<?php

require_once("DB.php");

/* Статистика посещений */

class Stats {
	var $db;
	var $dsn;
	var $prefix;

	function Stats($dsn,$prefix) {
		$this->dsn = $dsn;
		$this->db = DB::connect($this->dsn);
		if (DB::isError($this->db)) die ($this->db->getMessage());
		$this->prefix = $prefix;
	}

	function insertEntry() {
		global $REQUEST_URI,$HTTP_REFERER,$HTTP_USER_AGENT,$REMOTE_ADDR;
	
		$result = $this->db->query(sprintf(
		"insert into blog_stats
		(request_uri, http_referer, http_user_agent, remote_addr, bbuserid)
		values
		('%s',        '%s',         '%s',            '%s',        '%s')",
		 $REQUEST_URI,$HTTP_REFERER,$HTTP_USER_AGENT,$REMOTE_ADDR,$_COOKIE['bbuserid']));
		if (DB::isError($result)) die ($result->getMessage());
	}

	function getOldestDate() {
		return $this->db->getOne(
			"select date_format(min(date),'%d.%m.%y')
				from {$this->prefix}_stats");
	}

	function getHitsPeriod($days) {
		return $this->db->getOne(sprintf(
			"select count(id)
				from {$this->prefix}_stats
				where date > date_sub(now(),interval %d day)",
			$days));
	}

	function getUniquePeriod($days) {
		return $this->db->getOne(sprintf(
			"select count(distinct remote_addr, http_user_agent)
				from {$this->prefix}_stats
				where date > date_sub(now(),interval %d day)
                  and http_user_agent not like '%%bot%%'",
			$days));
	}

	function getUniqueBotsPeriod($days) {
		return $this->db->getOne(sprintf(
			"select count(distinct remote_addr, http_user_agent)
				from {$this->prefix}_stats
				where date > date_sub(now(),interval %d day)
                  and http_user_agent like '%%bot%%'",
			$days));
	}

	function _getQueryKey($thekey,$query) {
		$query = explode('&',$query);
		foreach($query as $q) {
			list ($key, $value) = split('=',$q);
			if ($key == $thekey) {
				$qs = urldecode($value);
				break;
			} else $qs = "";
		}
		return $qs;
	}

	function _win2utf($s) {
        $t = "";
		for($i=0, $m=strlen($s); $i<$m; $i++) {
    		$c=ord($s[$i]);
	    	if ($c<=127) { $t.=chr($c); continue; }
		    if ($c>=192 && $c<=207) { $t.=chr(208).chr($c-48); continue; }
    		if ($c>=208 && $c<=239) { $t.=chr(208).chr($c-48); continue; }
	    	if ($c>=240 && $c<=255) { $t.=chr(209).chr($c-112); continue; }
		    if ($c==184) { $t.=chr(209).chr(209); continue; };
    		if ($c==168) { $t.=chr(208).chr(129);  continue; };
		}
		return $t;
	}

	function _yandexParse($req) {
		$url = parse_url($req);
		$text = $this->_getQueryKey('text',$url['query']);
		if(empty($text)) {
			$qs = $this->_getQueryKey('qs',$url['query']);
			$text = $this->_getQueryKey('text',$qs);
			$text = $this->_win2utf(
			convert_cyr_string($text,
			'koi8-r','windows-1251'));
		} else
			$text = $this->_win2utf($text);
		return $text;
	}

	function _ramblerParse($req) {
		$url = parse_url($req);
		$words = $this->_getQueryKey('words',$url['query']);
		return $this->_win2utf($words);
	}

	function _goMailParse($req) {
		$url = parse_url($req);
		$q = $this->_getQueryKey('q',$url['query']);
		return $this->_win2utf($q);
	}

    function _yahooParse($req) {
        $url = parse_url($req);
        $va = $this->_getQueryKey('va',$url['query']);
        return $va;
    }

    function _googleParse($req) {
        $url = parse_url($req);
        $q = $this->_getQueryKey('q',$url['query']);
        return $q;
    }

    function _array_transpose($arr) {
        $arr2 = array();
        for($i=0; $i<count($arr[0]); $i++)
            foreach($arr as $row)
                $arr2[$i][] = $row[$i];
        return $arr2;
    }

	function getYandexRequests($days) {
        $req = $this->db->getCol(sprintf(
		"select distinct http_referer
			from {$this->prefix}_stats
			where date > date_sub(now(),interval %d day)
			and http_referer like 'http://%%yandex.ru/yand%%'",
		$days));
        return $this->_array_transpose(array(
            $req,
            array_map(array($this,'_yandexParse'), $req),
        ));
	}


	function getRamblerRequests($days) {
		$req = $this->db->getCol(sprintf(
		"select distinct http_referer
			from {$this->prefix}_stats
			where date > date_sub(now(),interval %d day)
			and http_referer like 'http://search.rambler.ru/%%'",
		$days));
        return $this->_array_transpose(array(
            $req,
            array_map(array($this,'_ramblerParse'), $req),
        ));
	}

    function getYahooRequests($days) {
        $req = $this->db->getCol(sprintf(
        "select distinct http_referer
            from {$this->prefix}_stats
            where date > date_sub(now(), interval %d day)
            and http_referer like 'http://search.yahoo.com/%%'",
        $days));
        return $this->_array_transpose(array(
            $req,
            array_map(array($this,'_yahooParse'), $req)
        ));
    }

    function getGoMailRequests($days) {
        $req = $this->db->getCol(sprintf(
        "select distinct http_referer
            from {$this->prefix}_stats
            where date > date_sub(now(), interval %d day)
            and http_referer like 'http://go.mail.ru/%%'",
        $days));
        return $this->_array_transpose(array(
            $req,
            array_map(array($this,'_goMailParse'), $req)
        ));
    }

    function getGoogleRequests($days) {
        $req = $this->db->getCol(sprintf(
        "select distinct http_referer
            from {$this->prefix}_stats
            where date > date_sub(now(), interval %d day)
            and http_referer like 'http://www.google.com%%/search%%'",
        $days));
        return $this->_array_transpose(array(
            $req,
            array_map(array($this,'_googleParse'), $req)
        ));
    }

	function getSearchRequests($days) {
		return array_merge(
			$this->getYandexRequests($days),
			$this->getRamblerRequests($days),
            $this->getYahooRequests($days),
            $this->getGoMailRequests($days),
            $this->getGoogleRequests($days)
		);
	}

    function getReferers($days) {
		return $this->db->getCol(sprintf(
		"select distinct http_referer
			from {$this->prefix}_stats
			where date > date_sub(now(),interval %d day)
			and http_referer not like ''
			and http_referer not like 'http://vlgu.ru/i01/%%'
			and http_referer not like 'http://vlgu.1gb.ru/i01/%%'
			and http_referer not like 'http://www.vlgu.ru/i01/%%'
			and http_referer not like 'http://www.vlgu.ru:80/i01/%%'
			and http_referer not like 'http://search.rambler.ru/%%'
			and http_referer not like 'http://%%yandex.ru/yand%%'
            and http_referer not like 'http://search.yahoo.com/%%'
            and http_referer not like 'http://go.mail.ru/%%'
            and http_referer not like 'http://www.google.com%%/search%%'",
		$days));
    }

    function getBBNicks($days) {
		return array_map(array($this, '_win2utf'),
            $this->db->getCol(sprintf(
        		"select distinct username
        			from {$this->prefix}_stats, users
        			where date > date_sub(now(),interval %d day)
                      and {$this->prefix}_stats.bbuserid = users.userid",
        		$days)));
    }

	function render(&$renderer, $requests = false, $referers = false, $nicks = false)
	{
		$this->_renderer =& $renderer;

        if ($nicks)
            foreach($this->getBBNicks(1) as $row)
                $this->_renderer->renderNick($row);
        if ($requests)
    		foreach($this->getSearchRequests(1) as $row)
	    		$this->_renderer->renderRequest($row);
        if ($referers)
    		foreach($this->getReferers(1) as $row)
	    		$this->_renderer->renderReferer($row);
		$this->_renderer->renderStats(array(
			'OLDEST'=>	$this->getOldestDate(),
			'UNIQUE_TODAY'=>$this->getUniquePeriod(1),
			'UNIQUE_MONTH'=>$this->getUniquePeriod(30),
			'UNIQUE_ALL'=>	$this->getUniquePeriod(365),
			'BOTS_TODAY'=>$this->getUniqueBotsPeriod(1),
			'BOTS_MONTH'=>$this->getUniqueBotsPeriod(30),
			'BOTS_ALL'=>	$this->getUniqueBotsPeriod(365),
		));
	}
}

class stats_SigmaRenderer
{
	var $_tpl;
	var $_prefix;
	
	function stats_SigmaRenderer(&$tpl, $prefix = '')
	{
		$this->_tpl    =& $tpl;
		$this->_prefix =  $prefix;
	}

	function renderStats($row)
	{
		$this->_tpl->setVariable(array(
			'OLDEST'=>	    $row['OLDEST'],
			'UNIQUE_TODAY'=>$row['UNIQUE_TODAY'],
			'UNIQUE_MONTH'=>$row['UNIQUE_MONTH'],
			'UNIQUE_ALL'=>	$row['UNIQUE_ALL'],
			'BOTS_TODAY'=>  $row['BOTS_TODAY'],
			'BOTS_MONTH'=>  $row['BOTS_MONTH'],
			'BOTS_ALL'=>	$row['BOTS_ALL']
		));
	}

	function renderRequest($requests)
	{
		$this->_tpl->setCurrentBlock("request");
		$this->_tpl->setVariable(array(
            'HREF'    => htmlspecialchars($requests[0]),
            'REQUEST' => htmlspecialchars($requests[1])
        ));
		$this->_tpl->parseCurrentBlock();
	}
    
	function renderReferer($row)
	{
		$this->_tpl->setCurrentBlock("referer");
		$this->_tpl->setVariable(array(
            'HREF'    => htmlspecialchars($row)
        ));
		$this->_tpl->parseCurrentBlock();
	}

	function renderNick($row)
	{
		$this->_tpl->setCurrentBlock("nick");
		$this->_tpl->setVariable(array(
            'USERNAME'    => htmlspecialchars($row)
        ));
		$this->_tpl->parseCurrentBlock();
	}
}
?>

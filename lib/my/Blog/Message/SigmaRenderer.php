<?php

require_once 'HTML/BBCodeParser.php';

class Blog_Message_SigmaRenderer
{
	var $_tpl;
	var $_prefix;
	var $_parser;
	
	function Blog_Message_SigmaRenderer(&$tpl, $prefix = '')
	{
		$this->_tpl    =& $tpl;
		$this->_prefix =  $prefix;

		$config = parse_ini_file('db/HTML_BBCodeParser.ini', true);
		$options =& PEAR::getStaticProperty('HTML_BBCodeParser','_options');
		$options = $config['HTML_BBCodeParser'];
		unset($options);
		$this->_parser =& new HTML_BBCodeParser();
	}
	
    function _formatTimestamp($timestamp)
    {
        return preg_replace(
            /* 1year  2mnth  3day   4hour  5min   6sec */
            "/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",
            "\\3.\\2.\\1 \\4:\\5:\\6",$timestamp);
    }

    function _formatText($text)
    {
        return
            /* Расстановка тире */
            preg_replace('/(\d)-{1,2}(\d)/','\1&ndash;\2',
            preg_replace('/ -{1,2}/','&nbsp;&mdash; ',
            /* Выделение кавычек */
            preg_replace('/&quot;(\S.*\S)&quot;/U','<q>\1</q>',
            preg_replace('/&quot;(\S.*\S)&quot;/U','<q>\1</q>',
            /* Разрыв строки */
            preg_replace('/\n/',"<br/>\n", $text)))));
    }

	function renderEntry($id, $nick, $subject, $text, 
                            $date, $comment_to, $keywords, $count)
	{
		$this->_tpl->setCurrentBlock("message");
		$this->_tpl->setVariable(array(
			'SUBJ'	=>	$this->_formatText(htmlspecialchars($subject)),
			'TEXT'	=>	$this->_formatText(
                            $this->_parser->qparse(htmlspecialchars($text))),
			'NICK'	=>	htmlspecialchars($nick),
			'DATE'	=>	$this->_formatTimestamp($date),
			'ID'	=>	$id,
			'COUNT'	=>	$count
		));
		$this->_tpl->parseCurrentBlock();
	}
}

?>

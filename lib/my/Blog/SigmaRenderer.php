<?php

class Blog_SigmaRenderer
{
	var $_tpl;
	var $_prefix;
	
	function blog_SigmaRenderer(&$tpl, $prefix = '')
	{
		$this->_tpl    =& $tpl;
		$this->_prefix =  $prefix;
	}
	
	function renderEntry($row, $count)
	{
		$this->_tpl->setCurrentBlock("message");
		$this->_tpl->setVariable(array(
			'SUBJ'	=>	empty($row['subject'])
					? "Без темы..."
					: $row['subject'],
			'TEXT'	=>	$row['text'],
			'NICK'	=>	$row['nick'],
			'DATE'	=>	$row['date'],
			'ID'	=>	$row['id'],
			'COUNT'	=>	$count
		));
		$this->_tpl->parseCurrentBlock();
	}

	function renderComment($row)
	{
		$this->_tpl->setCurrentBlock("comment");
		$this->_tpl->setVariable(array(
			'SUBJ'	=>	empty($row['subject'])
					? "Без темы..."
					: $row['subject'],
			'TEXT'	=>	$row['text'],
			'NICK'	=>	$row['nick'],
			'DATE'	=>	$row['date'],
			'ID'	=>	$row['id'],
            'LEVEL' =>  $row['level']
		));
		$this->_tpl->parseCurrentBlock();
	}

    function _keyword2item($keyword) 
    {
    	return array(
	    	'url' => empty($keyword)
                ? 'pro_unsorted.html'
                : 'pro_'.$keyword.'.html',
		    'title' => empty($keyword)
    			? 'неотсортированное'
	    		: $keyword
    	);
    }
    
    function renderKeywords($keywords)
    {
        $keywords = array_merge(
            array(array('url' => 'index.html', 'title' => 'свежак')),
            array_map(array($this,'_keyword2item'),$keywords)
        );
    	$keywords =& new HTML_Menu($keywords);
    	$keywords->forceCurrentUrl(rawurldecode(
    		basename($_SERVER['REQUEST_URI'])));
    	$renderer =& new HTML_Menu_SigmaRenderer($this->_tpl);
    	$keywords->render($renderer, 'rows');
    }

    function renderPager($links)
    {
		$this->_tpl->setCurrentBlock();
		$this->_tpl->setVariable(array(
            'PAGER' =>  $links['all']
		));
		$this->_tpl->parseCurrentBlock();
    }

    function renderStuff($id)
    {
        if ($id == 0)
            $this->_tpl->setCurrentBlock('post_message');
        else
            $this->_tpl->setCurrentBlock('post_comment');
        $this->_tpl->setVariable(array('ID' => $id));
        $this->_tpl->parseCurrentBlock();
    }

    function renderForm($form, $root)
    {
        $this->_tpl->setCurrentBlock('uplevel');
        $this->_tpl->setVariable(array('ROOT' => $root));
        $this->_tpl->parseCurrentBlock();
   
        $this->_tpl->setCurrentBlock('postform');
        $this->_tpl->setVariable(array('CONTENT' => $form));
        $this->_tpl->parseCurrentBlock();
    }
}

?>

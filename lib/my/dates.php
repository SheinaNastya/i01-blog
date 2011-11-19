<?php  	require_once 'HTML/Template/Sigma.php';
    	require_once 'DB.php';

class Dates {
	var $db;
	var $dsn;
	var $prefix;
    var $_renderer = null;
	
	function Dates($dsn) {
		$this->dsn = $dsn;
		$this->db = DB::connect($this->dsn);
		if (DB::isError($this->db)) die ($this->db->getMessage());
		$this->prefix = $prefix;

	}

	function render(&$renderer)
	{
		$this->_renderer =& $renderer;

//		foreach($pager->getPageData() as $row)
//    		$this->_renderer->renderEntry();
	}
}

class dates_SigmaRenderer
{
	var $_tpl;
	var $_prefix;
	
	function blog_SigmaRenderer(&$tpl, $prefix = 'BD')
	{
		$this->_tpl    =& $tpl;
		$this->_prefix =  $prefix;
	}
	
	function renderEntry($row)
	{
		$this->_tpl->setCurrentBlock("birthday");
		$this->_tpl->setVariable(array(
            $this->_prefix.'_DATE'      =>  $row['date'],
            $this->_prefix.'_NAME'      =>  $row['text'],
            $this->_prefix.'_GREETING'  =>  'С днем рожденья поздравляем и всего тебе желаем!'
		));
		$this->_tpl->parseCurrentBlock();
	}
}
?>

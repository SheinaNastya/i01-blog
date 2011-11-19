<?php	
	require_once "lib/my/common.php";
	require_once "no_cache.php";
	require_once "blog.php";
    require_once "Blog/SigmaRenderer.php";
    require_once "dates.php";
    require_once "timer.php";

    require_once "SigmaPage.php";

	require_once "HTML/Template/Sigma.php";
	require_once "HTML/Menu.php";
	require_once "HTML/Menu/SigmaRenderer.php";

    timer::Start_Timer();

	$blog =& new Blog($dsn, "blog");
    $dates =& new Dates($dsn);

	$topics = array_map('_topic2item', $blog->getLatestComments());
	$topics =& new HTML_Menu($topics);
    $topics->forceCurrentUrl(basename($_SERVER['REQUEST_URI']));

// Rendering

    $page =& new SigmaPage("./themes/$theme", "./cache", "common.html");

    $t =& $page->_tpl;

	$renderer =& new stats_SigmaRenderer($t);
	$stats->render($renderer);
	
    $page->addBlock('block',array(
        'CONTENT'   =>  XML2Menu("db/sections.xml", "menu.html", "tree"),
        'TITLE'     =>  'Разделы'));
        
	$tpl =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$tpl->loadTemplateFile('menu.html');
	$renderer =& new HTML_Menu_SigmaRenderer($tpl);
	$topics->render($renderer, 'tree');
    $page->addBlock('small_block',array(
        'CONTENT'   =>  $tpl->get(),
        'TITLE'     =>  'Последние комментарии'));
        
    $page->addBlock('small_block',array(
        'CONTENT'   =>  XML2Menu("db/links.xml", "menu.html", "tree"),
        'TITLE'     =>  'Друзья'));

    if (!empty($errors))
    $page->addBlock('errors',array(
        'ERRORS'    =>  $errors));

	$tpl =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$tpl->loadTemplateFile('blog.html');
	$renderer =& new blog_SigmaRenderer($tpl);
	$blog->render($renderer);

    $page->addBlock('',array(
    	'CONTENT'       =>	$tpl->get(),
    ));

    $tpl =& new HTML_Template_Sigma("./themes/$theme", "./cache");
    $tpl->loadTemplateFile('dates.html');
    $renderer =& new dates_SigmaRenderer($tpl);
    $dates->render($renderer);

    $page->addBlock('',array(
        'ANNOUNCEMENT'  =>  $tpl->get()
    ));

    $page->addBlock('',array(
    	'TITLE'         =>  'Наш сайт',
    	'TIME' 	        =>	timer::Get_Time(2)
    ));

    $page->show();

// Utilities

    function _timestamp2($date) 
    {
        return preg_replace(
		/* 1year  2mnth  3day   4hour  5min   6sec */
        "/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",
        "\\3.\\2&nbsp;\\4:\\5",$date);
    }

    function _topic2item($topic) 
    {
	    return array(
    		'url'   =>  'msg_'.$topic['id'].'.html',
	    	'title' => 
                $topic['nick'] 
                . ' на <q>' . htmlspecialchars($topic['subject']) .'</q> '
                . '(' . _timestamp2($topic['date']) . ')'
    	);
    }

?>

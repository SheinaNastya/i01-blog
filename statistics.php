<?php
	require_once('lib/my/common.php');
	require_once('utils.php');
    require_once "timer.php";

    timer::Start_Timer();

	require_once("HTML/Template/Sigma.php");
	require_once("HTML/Menu.php");
	require_once("HTML/Menu/SigmaRenderer.php");

$t =& new HTML_Template_Sigma("./themes/$theme", "./cache");
$t->loadTemplateFile("common.html");

$t->setCurrentBlock('block');
$t->setVariable(array(
    'TITLE' => 'Разделы',
    'CONTENT' => XML2Menu('db/sections.xml','menu.html', 'tree') 
));
$t->parseCurrentBlock();

$t->setCurrentBlock('small_block');
$t->setVariable(array(
    'TITLE' => 'Друзья',
    'CONTENT' => XML2Menu("db/links.xml", "menu.html", "tree")
));
$t->parseCurrentBlock();

    $statstpl =& new HTML_Template_Sigma("./themes/$theme", "./cache");
    $statstpl->loadTemplateFile("stats.html");
	$renderer =& new stats_SigmaRenderer($statstpl);
	$stats->render($renderer, true, true, true);

	$renderer =& new stats_SigmaRenderer($t);
	$stats->render($renderer);

if (!empty($errors)) {
	$t->setCurrentBlock("errors");
	$t->setVariable('ERRORS', $errors);
	$t->parseCurrentBlock();
}

$t->setVariable(array(
	'CONTENT'=>	$statstpl->get(),
 	'TITLE'	=>  'Статистика',
    'TIME' 	=>	timer::Get_Time()
));
$t->parseCurrentBlock();
$t->show();

?>

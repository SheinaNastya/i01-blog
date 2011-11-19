<?php
	require_once('lib/my/common.php');
	require_once('utils.php');
    require_once "timer.php";

	require_once("HTML/Template/Sigma.php");
	require_once("HTML/Menu.php");
	require_once("HTML/Menu/SigmaRenderer.php");

    timer::Start_Timer();

	$t =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$t->loadTemplateFile("common.html");

	$renderer =& new stats_SigmaRenderer($t);
	$stats->render($renderer);
	
$t->setCurrentBlock('block');
$t->setVariable(array( 'TITLE' => 'Разделы',
	'CONTENT' => XML2Menu('db/sections.xml', 'menu.html', 'tree')));
$t->parseCurrentBlock();

$t->setCurrentBlock('small_block');
$t->setVariable(array( 'TITLE' => 'Друзья',
    'CONTENT' => XML2Menu("db/links.xml", "menu.html", "tree")));
$t->parseCurrentBlock();


if (!empty($errors)) {
	$t->setCurrentBlock("errors");
	$t->setVariable('ERRORS', $errors);
	$t->parseCurrentBlock();
}

$files_index = 'db/edu_index.csv';

$fp = fopen($files_index, "r");
$list = array();
while($data = fgetcsv($fp, 999, ','))
    $list[] = array(
        'NAME'      =>  $data[0],
        'FORMAT'    =>  $data[1],
        'TYPE'      =>  $data[2],
        'DESC'      =>  $data[3],
        'KEYWORDS'  =>  $data[4],
        'SIZE'      =>  $data[5]
    );
fclose($fp);

$ft =& new HTML_Template_Sigma("./themes/$theme", "./cache");
$ft->loadTemplateFile("files.html");

$i=1;
foreach($list as $item) {
    $ft->setCurrentBlock('item');
    if ($i%2==0) $item['CLASS'] = 'even';
    $ft->setVariable($item);
    $ft->parseCurrentBlock();
    $i++;
}

$t->setVariable(array(
	'CONTENT'=>	$ft->get(),
    'TITLE'	=>  'Свалка',
    'TIME' 	=>	timer::Get_Time()
));
$t->parseCurrentBlock();
$t->show();

?>

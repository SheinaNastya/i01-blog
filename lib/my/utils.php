<?php

    require_once 'XML/Tree.php';

/* Format TIMESTAMP field from MySQL table */
//function timestamp($date) {
//        return preg_replace(
//		/* 1year  2mnth  3day   4hour  5min   6sec */
//        "/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",
//        "\\3.\\2.\\1 \\4:\\5:\\6",$date);
//}

function quote($str) {
	return
		/* Расстановка тире */
		preg_replace('/(\d)-{1,2}(\d)/','\1&ndash;\2',
		preg_replace('/ -{1,2}/','&nbsp;&mdash; ',
		/* Выделение кавычек */
		preg_replace('/&quot;(\S.*\S)&quot;/U','<q>\1</q>',
		preg_replace('/&quot;(\S.*\S)&quot;/U','<q>\1</q>',
		preg_replace('/\n/',"<br/>\n",
			htmlspecialchars($str))))));
}

function xmltree2urlmap($tree) {
	$ret = array();
	foreach($tree as $node)
		$ret[] = array(
			'title' => $node->attributes['title'],
			'url' => $node->attributes['url'],
			'sub' => xmltree2urlmap($node->children)
		);
	return $ret;
}

function XML2Menu($xmlfile, $tplfile, $type = '') {
	global $theme;

	$xml =& new XML_Tree($xmlfile);
	$tree = $xml->getTreeFromFile();
	$menu_tree = xmltree2urlmap($tree->children);

	$menu =& new HTML_Menu($menu_tree);
	$menu->forceCurrentUrl(basename($_SERVER['REQUEST_URI']));

	$tpl =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$tpl->loadTemplateFile($tplfile);
	$menu->render(new HTML_Menu_SigmaRenderer($tpl), $type);

	return $tpl->get();
}

?>

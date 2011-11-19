<?php	
	require_once "lib/my/common.php";
	require_once "no_cache.php";
	require_once "blog.php";

	require_once "HTML/Template/Sigma.php";

	$blog =& new Blog($dsn, "blog");

	$t =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$t->loadTemplatefile("rss2.xml");

	$renderer =& new blog_SigmaRenderer($t);
	$blog->render($renderer);

    $t->show();

?>

<?php

    require_once 'HTML/Template/Sigma.php';

    class SigmaPage
    {
        var $_tpl;
    
        function SigmaPage($tpldir,$cachedir,$roottpl)
        {
        	$this->_tpl =& new HTML_Template_Sigma($tpldir, $cachedir);
        	$this->_tpl->loadTemplatefile($roottpl);
        }

        function addBlock($name,$content)
        {
            $this->_tpl->setCurrentBlock($name);
            $this->_tpl->setVariable($content);
            $this->_tpl->parseCurrentBlock();
        }

        function show()
        {
            $this->_tpl->show();
        }
    }

?>

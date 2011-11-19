<?php

    require_once 'Blog/DataObject_Message.php';

    class Blog_Message extends Blog_DataObject_Message 
    {
        var $_renderer = null;
        var $_prefix;
    
        function Blog_Message($prefix='blog') 
        {
            $this->_prefix = $prefix;
            $this->__table = $this->_prefix . '_messages';
        }

	    function getRoot() 
        {
            $msgid = $this->id;
    		$previd = $msgid;
	    	while($msgid!=0) {
		    	$previd = $msgid;
                $message =& new Blog_Message($this->_prefix);
                $message->get($msgid);
	    		$msgid = $message->comment_to;
            }
            if (empty($previd)) $previd = 0;
	    	return $previd;
    	}

    	function getCommentsCount($msgid) {
	    	$noc = 0;

            $message = new Blog_Message($this->_prefix);
            $message->comment_to = $msgid;
            $message->find();

            while($message->fetch())
                $noc += $message->getCommentsCount($message->id) + 1;

            return $noc;
	    }

	    function render(&$renderer)
	    {
		    $this->_renderer =& $renderer;
            $this->_renderer->renderEntry(
                $this->id,
                $this->nick,
                $this->subject,
                $this->text,
                $this->date,
                $this->comment_to,
                $this->keywords,
                $this->getCommentsCount($this->id)
            );
        }
    }

?>

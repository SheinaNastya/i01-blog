<?php	require_once 'utils.php';
    	require_once 'HTML/QuickForm.php';
    	require_once 'HTML/Menu.php';
	    require_once 'HTML/Menu/SigmaRenderer.php';
    	require_once 'Pager/Pager.php';

        require_once 'Blog/Message.php';
        require_once 'Blog/Auth.php';
        require_once 'Blog/Message/SigmaRenderer.php';

class Blog {
	var $dsn;
	var $prefix;
    var $_renderer = null;
    var $_keyword = '';
    var $_id = 0;
	
	function Blog($dsn,$prefix) {
		$this->dsn = $dsn;
		$this->prefix = $prefix;

        if (isset($_GET['keywords'])
            && $_GET['keywords']=='unsorted') unset($_GET['keywords']);
        $this->_keyword = array_key_exists('keywords', $_GET)
                        &&array_search($_GET['keywords'], $this->getKeywords())
        	? $_GET['keywords']
        	: '' ;
        if (isset($_GET['keywords'])
            && $_GET['keywords']=='fresh') $this->_keyword = $_GET['keywords'];

        $this->_id = array_key_exists('id', $_GET)
        	? $_GET['id']
        	: '' ;
	}

	function getLatestComments($num = 7) {
        $message =& new Blog_Message($this->prefix);
        $message->whereAdd('comment_to != 0');
        $message->orderBy('date desc');
        $message->limit(0,$num);
        $message->find();

        $subjects = array();
        while($message->fetch()) {
            $message->id = $message->getRoot();
			$subjects[] = $message->toArray();
        }
        return $subjects;
	}

	function getMessages() {
        $message =& new Blog_Message($this->prefix);
        $message->comment_to = 0;
        $message->orderBy('date desc');
        if ($this->_keyword != 'fresh')
            $message->keywords = $this->_keyword;

        $message->find();

        $messages = array();
        while($message->fetch())
            $messages[] = $message;
        return $messages;
	}

	function getKeywords() {
        $message =& new Blog_Message($this->prefix);
        $message->selectAdd();
        $message->selectAdd('distinct keywords as __tmp');
        $message->find();
        $keywords = array();
        while($message->fetch())
            array_push($keywords, $message->__tmp);
        return $keywords;
	}

	function getComments($msgid,$level=0) {
        $message =& new Blog_Message($this->prefix);
        $message->comment_to = $msgid;
        $message->orderBy('date asc');
        $message->find();

		$messages = array();
		while($message->fetch()) {
            $row = $message->toArray();
			$row['level'] = $level;
			array_push($messages,$row);
			$messages = array_merge($messages,
				$this->getComments($row['id'],$level+1));
		}
		return $messages;
	}

	function post() {
		$vars = $_POST;
		$comment_to = $_GET[id];
	
		setcookie("nick", stripslashes($vars[nick])); 
		setcookie("password", stripslashes($vars[password]));

        $auth =& new Auth($this->dsn, $this->prefix);
        if(!$auth->verifyPassword($vars[nick],$vars[password]))
			die("Неправильный пароль");

        $message =& new Blog_Message($this->prefix);
        $message->nick = addslashes($vars[nick]);
        $message->subject = addslashes($vars[subject]);
        $message->text = addslashes($vars[text]);
        $message->comment_to = empty($comment_to) ? 0 : $comment_to;
        $message->keywords = $vars[keywords];

        $message->insert();

		$href = empty($comment_to) 
			? (empty($vars['keywords'])
                ? "pro_unsorted.html" 
                : "pro_".$vars['keywords'].".html")
			: "msg_".$this->getMessageIdRoot($comment_to).".html";
		header("Location: $href");
		return	"<p>Сообщение успешно принято. <br/>".
			"Возвращайтесь сюда: <a href='$href'>$href</a></p>";
	}

	function postform($comment_to = 0) {
		if(!is_numeric($comment_to)) $comment_to = 0;

		$form =& new HTML_QuickForm('postform','post',"?id=$comment_to");

		if($comment_to!=0) {
            $message =& new Blog_Message($this->prefix);
            $message->get($comment_to);
            $subject = $this->_message->subject;
		}

		$defaultValues['nick'] = stripslashes($_COOKIE['nick']);
		$defaultValues['password'] = stripslashes($_COOKIE['password']);
		$defaultValues['subject'] = $comment_to==0
					? "Без темы..."
					: $subject;
		$defaultValues['keywords'] = $comment_to==0
					? ""
					: $_GET['keywords'];
		$form->setDefaults($defaultValues);

		$form->addElement('text','nick','Имя:');
		$form->addElement('password','password','Пароль:');
		$form->addElement('text','subject','Тема:');
		$form->addElement('textarea','text','Сообщение:');

        foreach($this->getKeywords() as $keyword)
            $keywords[$keyword] = $keyword;
        
		if ($comment_to==0)
			$form->addElement('select','keywords','Раздел:',$keywords);
		$form->addElement('submit','post','Послать');

		$text = &$form->getElement('text');
		$text->setRows(25);
		$text->setCols(60);

		$form->applyFilter('__ALL__', 'trim');

		$form->addRule('nick','Имя должно быть указано','required');
		$form->addRule('text','Текст сообщения пуст','required');
		
ob_start();
	
		if ($form->validate()) {
			$form->freeze();
			$form->process(array($this,'post'), false);
		}

		$form->display();

$stuff = ob_get_contents(); ob_end_clean();

		return $stuff;
	}

	function render(&$renderer)
	{
		$this->_renderer =& $renderer;

        $this->_renderer->renderKeywords($this->getKeywords());

        // Postform
        if (isset($_GET['action']) && $_GET['action']=='post') {

            if ($this->_id != 0) {
                $message =& new Blog_Message($this->prefix);
                $message->get($this->_id);
                $renderer =& new Blog_Message_SigmaRenderer(
                    $this->_renderer->_tpl);
                $message->render($renderer);
            }

            $message =& new Blog_Message($this->prefix);
            $message->id = $this->_id;

            $this->_renderer->renderForm($this->postform($this->_id),
                $message->getRoot());

        } elseif ($this->_id != 0) {
        
            $this->_renderer->renderStuff($this->_id);
            $message =& new Blog_Message($this->prefix);
            $message->get($this->_id);
            $renderer =& new Blog_Message_SigmaRenderer(
                $this->_renderer->_tpl);
            $message->render($renderer);

            if ($message->getCommentsCount($this->_id) > 0) {
            	$pager =& new Pager(array(
	            	'itemData'      =>  $this->getComments($this->_id),
    	        	'perPage'       =>  8,
	    	        'delta'         =>  4,
    		        'append'        =>  false,
            		'separator'     =>  ', ',
	            	'clearIfVoid'   =>  false,
    	    	    'urlVar'        =>  'page',
        	    	'useSessions'   =>  true,
	        	    'closeSession'  =>  false,
        		    'mode'          =>  'Jumping',
                    'fileName'      =>  'msg_'.$this->_id.'_%d.html'
                ));

    	    	foreach($pager->getPageData() as $row)
	    	    	$this->_renderer->renderComment($row);
                $this->_renderer->renderPager($pager->getLinks());
            } 

        } else {
        
            $this->_renderer->renderStuff($this->_id);
        	$pager =& new Pager(array(
	        	'itemData'      =>  $this->getMessages(),
    	    	'perPage'       =>  5,
	    	    'delta'         =>  4,
    		    'append'        =>  false,
        		'separator'     =>  ', ',
	        	'clearIfVoid'   =>  false,
    		    'urlVar'        =>  'page',
        		'useSessions'   =>  true,
	        	'closeSession'  =>  false,
    		    'mode'          =>  'Jumping',
                'fileName'      =>  empty($this->_keyword)
                                        ? 'pro_unsorted_%d.html'
                                        : 'pro_' . $this->_keyword . "_%d.html"
        	));

            $renderer =& new Blog_Message_SigmaRenderer(
                $this->_renderer->_tpl);
    		foreach($pager->getPageData() as $message)
                $message->render($renderer);
            $this->_renderer->renderPager($pager->getLinks()); 
        }
	}
}

?>

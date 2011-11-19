<?php

require_once 'DB.php';

class Auth 
{
	var $db;
    var $prefix;

    function Auth($dsn,$prefix) 
    {
		$this->dsn = $dsn;
		$this->db = DB::connect($this->dsn);
		if (DB::isError($this->db)) die ($this->db->getMessage());
		$this->prefix = $prefix;
    }

    function verifyPassword($nick, $password) 
    {
		$realpassword = $this->db->getOne(sprintf(
			"select password
				from {$this->prefix}_users
				where nick = '%s'",
			addslashes($nick)));
		if (!empty($realpassword) && 
			$realpassword!=stripslashes($password))
			return false;
        else 
            return true;
    }
}

?>

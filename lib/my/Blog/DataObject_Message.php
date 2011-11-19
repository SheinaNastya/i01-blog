<?php
/**
 * Table Definition for blog_messages
 */
require_once 'DB/DataObject.php';

class Blog_DataObject_Message extends DB_DataObject 
{

    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'blog_messages';                   // table name
    var $id;                              // int(10)  not_null primary_key unsigned auto_increment
    var $nick;                            // string(255)  
    var $subject;                         // string(255)  
    var $text;                            // blob(65535)  not_null blob
    var $date;                            // timestamp(14)  not_null unsigned zerofill timestamp
    var $comment_to;                      // int(11)  
    var $keywords;                        // string(255)  not_null

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Blog_messages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>

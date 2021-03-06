<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Alexey Borzov <avb@php.net>                                  |
// +----------------------------------------------------------------------+
//
// $Id: Renderer.php,v 1.2 2003/09/10 14:13:13 avb Exp $
//

/**
 * An abstract base class for HTML_Menu renderers
 *
 * @package  HTML_Menu
 * @version  $Revision: 1.2 $
 * @author   Alexey Borzov <avb@php.net>
 * @abstract
 */
class HTML_Menu_Renderer
{
   /**
    * Type of the menu being rendered
    * @var string
    */
    var $_menuType;

   /**
    * Sets the type of the menu being rendered.
    *
    * @access public
    * @param  string menu type
    */
    function setMenuType($menuType)
    {
        $this->_menuType = $menuType;
    }


   /**
    * Finish the menu
    *
    * @access public
    * @param  int    current depth in the tree structure
    */
    function finishMenu($level)
    {
    }


   /**
    * Finish the row in the menu
    *
    * @access public
    * @param  int    current depth in the tree structure
    */
    function finishRow($level)
    {
    }


   /**
    * Renders the element of the menu
    *
    * @access public
    * @param array   Element being rendered
    * @param int     Current depth in the tree structure
    * @param int     Type of the element (one of HTML_MENU_ENTRY_* constants)
    */
    function renderEntry($node, $level, $type)
    {
    }
}

?>

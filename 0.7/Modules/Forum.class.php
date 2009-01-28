<?php
/**
  * PHP Controller
  *
  * This library is free software; you can redistribute it and/or
  * modify it under the terms of the GNU Lesser General Public
  * License as published by the Free Software Foundation; either
  * version 2.1 of the License, or (at your option) any later version.
  * 
  * This library is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  * Lesser General Public License for more details.
  * 
  * You should have received a copy of the GNU Lesser General Public
  * License along with this library; if not, write to the Free Software
  * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  *
  * @category   Example
  * @package    PHP-Controller
  * @author     Eddie Tejeda <eddie@visudo.com>
  * @copyright  2005 Visudo LLC
  * @version    0.3
  */

  
require_once("Managers/ForumManager.class.php");

class Forum{

  var $database;

 /**
  */
  function Forum(){
    $this->database = isset($_SESSION['database']) ? $_SESSION['database'] : null;
  }
  
 /**
  */  
  function messages(){
  }
  
 /**
  */
  function addMessage($get, $post, $cookie){
    return true;
  }
    
 /**
  */
  function addMessageAction($get, $post){
  }
}
	
?>

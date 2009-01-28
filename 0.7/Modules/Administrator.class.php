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
  * @version    0.4
  */

require_once("Managers/UserManager.class.php");

class Administrator{

  var $database; 
  var $userManager; 
  
 /**
  * Contructor 
  */
  function Administrator(){
    $this->database = isset($_SESSION['database']) ? $_SESSION['database'] : null;
  }
  
 /**
  */
  function User($user){
    //return $this->userManager->getUser($user_id);    
  }
  
 /**
  */
  function Users(){
    //return $this->userManager->getAllUsers();
  }
  
 /**
  */
  function addUser($get, $post, $cookie){
    //do nothing
  }
  
 /**
  */
  function doAddUser($get, $post, $cookie){
    //$this->userManager->addUser($post);
  }
	
}
	
?>

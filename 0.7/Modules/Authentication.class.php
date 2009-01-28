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

class Authentication{ 

  function login($post){
    // PLEASE REWRITE BELOW TO AUTHENTICATE
    // connect to user database and
    if($post["username"] == "admin" && $post["password"] == "admin"){
      $_SESSION['role'] = "admin";
      $_SESSION['username'] = $_POST["username"];
      return true;
    }
    else{
      return false;
    }
    // PLEASE REWRITE ABOVE TO AUTHENTICATE
  }
  
  
  function logout(){
    session_unset('role');    
    session_unset('username');
    session_destroy();
  }
  
}
	
?>

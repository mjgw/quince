<?php
/**
  * PHP Controller
  *
  * PHP versions 4/5
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
  * @version    0.6
  */
  
session_start();
//load database settings
#require_once('System/Database.class.php');
#$_SESSION['database'] = new Database("localhost", "username", "database", "password");

//this is all the code we need to load up proper classes
require_once('System/Controller.class.php');
$controller = new Controller("controller.xml");
$controller->setDebugLevel(1);
$controller->performAction();
$templateName = $controller->getTemplateName();
$sectionName   = $controller->getSectionName();
$methodName   = $controller->getMethodName();
$pageContent  = $controller->getContent();
$domain =  $controller->getDomainName();

//error catching
if(true){

}

//this should probably be inside a smarty manager class
require_once('System/SmartyManager.class.php');
$smartyManager = new SmartyManager();
$smarty = $smartyManager->smartyInitialize('Libraries/Smarty/', 'Presentation/');

//now we handle the information from controller to template engine. easy?
$smarty->assign("domain", $domain );
$smarty->assign("section", $sectionName );
$smarty->assign("method", $methodName );
$smarty->assign("content", $pageContent);
$smarty->assign("template", $templateName);

$methodTemplate = (strlen($methodName)) ? "/".$methodName : null;

$smarty->display($templateName.$methodTemplate.".tpl");

?>

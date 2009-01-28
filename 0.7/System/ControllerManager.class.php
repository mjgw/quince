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
  * Foundation, Inc., 51 Franklin St, Fift````h Floor, Boston, MA  02110-1301  USA
  *
  * @category   Controller Manager
  * @package    PHP-Controller
  * @author     Eddie Tejeda <eddie@visudo.com>
  * @copyright  2005 Visudo LLC
  * @version    0.3
  */
  
require_once 'XML/Serializer.php'; 
require_once 'XML/Unserializer.php';

/**
 * this will be a class to manage the controller and validate the data
 * so far it's just a shell and it won't be ready until the next version
 * anyone want to develop it for me? :)
 */
class ControllerManager{

  var $controllerXml = null;  
  var $filename;
  
  function ControllerManager($filename = "controller.xml"){
    $this->filename = $filename;
    $option = array('complexType' => 'array', 'parseAttributes' => TRUE);
    $unserialized = new XML_Unserializer($option);
    $result = $unserialized->unserialize($filename, true);
    if (PEAR::isError($result)) {
        die($result->getMessage());
    }
    $this->controllerXml = $unserialized->getUnserializedData();
  }

  function getController(){
    return $this->controllerXml;
  }
  
  function setBaseDomain($domain){
    $this->controllerXml['domain-path'] = $domain;
    $this->writeXml();
  }
  
  function setModulesPath($path){
    $this->controllerXml['modules'] = $path;
    $this->writeXml();    
  }
  

  function addModule($name, $class, $defaultMethod, $template = "", $isDefaultPage = false, $isEnabled = true){    
    $newModule['name'] = $name; 
    $newModule['class'] = $class;
    $newModule['default-method'] = $defaultMethod;
    $newModule['template'] = $template;    
    $newModule['default-module'] = ($isDefaultPage != false) ? true: false;
    $newModule['enabled'] = ($isEnabled != true) ? false: true;    
    
    $this->controllerXml['module'][] = $newModule; //append module to loaded file
    $this->writeXml();
  }
  
  public function removePage($name){
    
    foreach($this->controllerXml['module'] as $key=>$module){
      if($module['name'] == $name){
        unset($this->controllerXml['module'][$key]);
        break;
      }
    }    
    $this->writeXml();
  }

  public function editPage($name, $class="", $defaultMethod="", $template = "", $isDefaultPage = "", $rename= ""){
    foreach($this->controllerXml['module'] as $key=>$module){
      if($module['name'] == $name){
        $this->controllerXml['module'][$key][$name];
        
        $this->writeXml();  
        return true;
      }
    }
    return false;    
  }
  
  public function findPage(){
  
  }
  
  public function getPage(){
  
  }
  
  public function getAllPages(){
  
  }
  
  public function addFormForward($moduleName, $formFowardName, $formForwardType, $formForwardValue){
  
  }

  public function addAlias($module = "", $alias = "", $isEnabled = true){

    $newAlias['module'] = $module;
    $newAlias['alias'] = $alias; //validation?
    $newAlias['enabled'] = ($isEnabled != true) ? false: true;
    
    $this->controllerXml['module'][] = $newAlias; //append alias to loaded file

    $this->writeXml();
  }
  
  
  public function deleteAlias(){
  
  }  
  
  
  public function editAlias(){
  
  }  

  private function writeXml(){
    // An array of serializer options 
    $serializer_options = array ( 
      'addDecl' => TRUE, 
      'encoding' => 'UTF-8', 
      'indent' => '  ', 
      'rootName' => 'controller', 
      'defaultTagName' => 'module', 
    ); 

    // Instantiate the serializer with the options 
    $serializer = &new XML_Serializer($serializer_options); 

    // Serialize the data structure 
    $status = $serializer->serialize($this->controllerXml); 

    // Check whether serialization worked 
    if (PEAR::isError($status)) { 
      die($status->getMessage()); 
    }
		
    file_put_contents($this->filename, $serializer->getSerializedData()); 
  }
}



?>

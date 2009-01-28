<?php
/**
  * PHP Controller
  *
  * PHP versions 5
  *
  * LICENSE: This source file is subject to version 3.0 of the PHP license
  * that is available through the world-wide-web at the following URI:
  * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
  * the PHP License and are unable to obtain it through the web, please
  * send a note to license@php.net so we can mail you a copy immediately.
  *
  *
  * @category   Presentation
  * @package    SmartyManager
  * @author     Eddie Tejeda <eddie@visudo.com>
  * @author     Marcus Gilroy-Ware <marcus@visudo.com>
  * @copyright  2005 Visudo LLC
  * @version    0.2
  */
require_once('Smarty.class.php');

class SmartyManager {

  function &smartyInitialize($library=null, $templates=null) {
    //detect if the proper directories exist
    if(!is_dir( $templates) ){
      die("SmartyManager::smartyInitialize Error: create templates directory");
    }
    if(!is_dir( $library."/templates_c/") ){
      die("SmartyManager::smartyInitialize Error: create template_c directory");
    }
    if(!is_dir( $library."/cache/") ){
      die("SmartyManager::smartyInitialize Error: create cache directory");
    }    
    if(!is_dir( $library."/config/") ){
      die("SmartyManager::smartyInitialize Error: create config directory");
    }
    if(!is_writeable( $library."/templates_c/") ){
      die("SmartyManager::smartyInitialize Error: templates_c needs to writable");
    }
    
    $smarty = new Smarty();
    $smarty->template_dir = $templates;
    $smarty->compile_dir = $library."/templates_c/";
    $smarty->cache_dir = $library."/cache/";
    $smarty->config_dir = $library."/config/";
    return $smarty;
  }
}
?>

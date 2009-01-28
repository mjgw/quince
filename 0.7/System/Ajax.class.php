<?php

require_once "HTML/AJAX/Server.php";
    
class AutoServer extends HTML_AJAX_Server {
  // this flag must be set for your init methods to be used
  var $initMethods = true;
    
  // init method for my hello world class
  function initPages() {
    require_once "Modules/Forum.class.php";
    $pages = new Pages();
    $this->registerClass($pages);
  }

}
    
$server = new AutoServer();
$server->handleRequest();

?>

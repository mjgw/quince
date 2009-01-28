<?php
/**
  * This class will eventually replace Mysql.class.php
  */
  
define("UNSUPPORTED_QUERY", false);

class Database{

	var $dblink;
	var $lastQuery;
	var $queryHistory;
	
	function Database($server, $username, $database, $password=""){
	}
	
	function rawQuery($querystring){
	}
	
	function howMany($querystring){
	}
	
	function queryToArray($querystring){
	}
	
	function recordQuery($querystring){
	}
	
	function specificQuery($wantedField, $qualifyingField, $qualifyingValue, $table){
	}
	
	function getQueryType($querystring){
	}
	
	
	function query($querystring) {
	}
	
	function getDebugInfo(){
	}
}

?>

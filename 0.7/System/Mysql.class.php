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
  * @category   Database
  * @package    Mysql
  * @author     Eddie Tejeda <eddie@visudo.com>
  * @author     Marcus Gilroy-Ware <marcus@visudo.com>
  * @copyright  2005 Visudo LLC
  * @version    0.2
  */
  
define("UNSUPPORTED_QUERY", false);

class Mysql{

	var $dblink;
	var $lastQuery;
	var $queryHistory;
	
	function Mysql($server, $username, $database, $password=""){
		$this->dblink = mysql_connect($server, $username, $password);
		@mysql_select_db($database, $this->dblink);
		$this->lastQuery = "no queries made yet";
		$this->queryHistory = array();
	}
	
	function rawQuery($querystring){
		if($result = @mysql_query($querystring, $this->dblink)){
			$this->recordQuery($querystring);
			return $result;
		}else{
			return false;
		}
	}
	
	function howMany($querystring){
		if($result = @mysql_query($querystring, $this->dblink)){
			$cardinality = @mysql_num_rows($result);
			$this->recordQuery($querystring);
			return $cardinality;
		}else{
			return "0";
		}
	}
	
	function queryToArray($querystring){
		$resultArray = array();
		
		$result = @mysql_query($querystring, $this->dblink);
		
		for($i=0;$i<@mysql_num_rows($result);$i++){
			$row = @mysql_fetch_array($result, MYSQL_ASSOC);
			array_push($resultArray, $row);
		}
		
		$this->recordQuery($querystring);
		return $resultArray;
	}
	
	function recordQuery($querystring){
		if(strlen(@mysql_error($this->dblink)) > 0){
			$error = "MySQL ERROR ".@mysql_errno($this->dblink) . ": " . @mysql_error($this->dblink);
		}else{
			$error = "Query OK";
		}
		$this->lastQuery = $querystring;
		array_push($this->queryHistory, $querystring."; ".$error);
	}
	
	function specificQuery($wantedField, $qualifyingField, $qualifyingValue, $table){
		$query = "SELECT $wantedField, $qualifyingField FROM $table WHERE $qualifyingField='$qualifyingValue' LIMIT 1";
		if($result = $this->rawQuery($query)){
			$this->recordQuery($query);
			$value = @mysql_result($result, 0, $wantedField);
			return $value;
		}
	}
	
	function getQueryType($querystring){
		$query_words = preg_split("/[\s]/", $querystring);
		if(strlen($query_words[0]) > 0){
			return strtoupper($query_words[0]);
		}else{
			return strtoupper($query_words[1]);
		}
	}
	
	
	function query($querystring) {

		switch ($this->getQueryType($querystring)){
			case 'UPDATE': // update and delete queries return number of affected rows.
			case 'DELETE':
				if($result = $this->rawQuery($querystring)){
					return @mysql_affected_rows($result);
				}
				break;

			case 'INSERT': // do insert query and return id of last newly inserted row.
				if($result = $this->rawQuery($querystring)){
					return @mysql_insert_id($result);
				}
				break;

			case 'SELECT': // select query returns data as array.
				if($data = $this->queryToArray($querystring)){
					return $data;
				}else{
					return false;
				}
				break;

			default:
				return UNSUPPORTED_QUERY;
		}

	}
	
	function getDebugInfo(){
		return $this->queryHistory;
	}
}

?>

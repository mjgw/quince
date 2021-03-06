<?php
/**
 * Test class used in other examples
 * Constructors and private methods marked with _ are never exported in proxies to JavaScript
 * 
 * @category   HTML
 * @package    AJAX
 * @author     Joshua Eichorn <josh@bluga.net>
 * @copyright  2005 Joshua Eichorn
 * @license    http://www.opensource.org/licenses/lgpl-license.php  LGPL
 * @version    Release: 0.4.0
 * @link       http://pear.php.net/package/HTML_AJAX
 */
class test {
	function test() {
	}
	function _private() {
	}
	function echo_string($string) {
		return "From PHP: ".$string;
	}
	function slow_echo_string($string) {
		sleep(2);
		return "From PHP: ".$string;
	}
	function error_test($string) {
		trigger_error($string);
	}
	function multiarg() {
		$args = func_get_args();
		return "passed in ".count($args)." args ".implode('|',$args);
	}
	function cookies() {
		return $_COOKIE;
	}
	function echo_data($data) {
		return array('From PHP:'=>$data);
	}
	function unicode_data() {
		$returnData = array('word' => mb_convert_encoding('Fran�ais','UTF-8'), 'suggestion' => array(mb_convert_encoding('Fran�ais','UTF-8'), mb_convert_encoding('caract�res','UTF-8')));
		return $returnData;
	}
}

if (isset($_GET['TEST_CLASS'])) {
	$t = new test();
	var_dump($t->echo_string('test string'));
	var_dump($t->slow_echo_string('test string'));
	var_dump($t->error_test('test string'));
	var_dump($t->multiarg('arg1','arg2','arg3'));
}
?>

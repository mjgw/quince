<?php

session_start();

function getTime(){ // retrieves unix time in milliseconds
    return number_format(microtime(true)*1000, 2, ".", "");
}

require_once "XML/Unserializer.php";

define('START_TIME', getTime());

require_once "Quince.class.php";

// for($i=0;$i<2000;$i++){

    $q = new Quince('./controller.xml', false);
    $q->cache_dir = './cache/';
    $q->dispatch();
    // echo $q->getMethodName();
    /// var_dump($q->getDomain());
    
// }

// $q->_debug();

// print_r($q->getDebugContent());

define('END_TIME', getTime());

// echo END_TIME;

$time_taken = END_TIME-START_TIME;

echo '<br />Time Taken:';
echo number_format($time_taken, 2).'ms';
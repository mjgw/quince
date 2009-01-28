<?php

function getTime(){ // retrieves unix time in milliseconds
    return number_format(microtime(true)*1000, 2, ".", "");
}

require_once "spyc.php";



require_once "Quince.class.php";

define('START_TIME', getTime());

// for($i=0;$i<2000;$i++){

    $q = new Quince();
    $r = $q->dispatch();
    
// }

define('END_TIME', getTime());

// echo END_TIME;

$time_taken = END_TIME-START_TIME;

echo '<br />Time Taken:';
echo number_format($time_taken, 2).'ms';
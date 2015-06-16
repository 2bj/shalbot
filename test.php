<?php


//include_once 'classes/service/shalbot_service_weather.php';
//include_once 'classes/service/shalbot_service_greatwords.php';
include_once 'classes/service/shalbot_service_currency.php';
//include_once 'classes/service/shalbot_service_bashorg.php';





//$obj = new shalbot_service_weather();

//$obj = new shalbot_service_bashorg();

//$obj = new shalbot_service_greatwords();
$obj = new shalbot_service_currency();

echo "\n\n";

//$obj->get_result("moscow, russia");

$res = $obj->get_result("!curr");

print_r($res);
/*
echo "\n---\n";
$res = $obj->get_result("!curr rub");
print_r($res);
echo "\n---\n";
$res = $obj->get_result("!curr rub today");
print_r($res);
*/
echo "\n\n";
//echo $obj->get_result('1');
//http://xoap.weather.com/search/search?where=Bishkek
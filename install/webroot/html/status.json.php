<?php
require_once("/var/www/printserver-functions.php");
$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

if (file_exists($configfile))  {                  $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }

//file_put_contents('/tmp/printrouterCONF.log', print_r(count((array)$config))); //DEBUG...DUMP config object count
//station id is determined by last number of ipv4
exec('/bin/bash /etc/printer_status.sh '.$statusfile);
//$status=json_decode(file_get_contents($statusfile),1);
//print_r(file_get_contents($statusfile),1);

header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Connection: close");
php header("Content-Type: application/json"); 
print(json_encode(json_decode(file_get_contents($statusfile)), JSON_PRETTY_PRINT));

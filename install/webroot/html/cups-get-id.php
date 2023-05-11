<?php
require_once("/var/www/printserver-functions.php");
$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

if (file_exists($configfile))  {                  $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }



if(isset($_GET['id'])) { 
    $client=0;
    //$station=$_GET['id'];
        $lastOctet=intval($_GET['id']);
        // 101..116 GET A STRAIGHT 1:1 MAPPING
        if ($lastOctet > 0 && $lastOctet < 17 )
          {   $client=$lastOctet ;}
        
        if (($lastOctet > 50) && ($lastOctet<200))
          {    $client=$lastOctet % 50 ; }
        if ($client > 16 )
          {   $client=$client % 50 ;}

    $station=$client;
    if($station==0){
        echo "STATION 0 NOT ACCEPTED , you sent id: ".$lastOctet;
        exit(0);
        }
    if(isset($_GET['type'])) { 
        if($_GET['type']=="CARD") {
            print('CARD'.sprintf("%02d",getCardNum($config,$station)));
            }
        if($_GET['type']=="LABEL") {
            print('LABEL'.sprintf("%02d",getLabelNum($config,$station)));
            }            
        
    }
}

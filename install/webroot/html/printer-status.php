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
    if($client==0){
        exit(0);
    }
    $station=$client;
    if(isset($_GET['type'])) { 
        exec('/bin/bash /etc/printer_status.sh '.$statusfile);
        $status=json_decode(file_get_contents($statusfile),1);
        
        if($_GET['type']=="CARD") {
            if (isset($status['card-'.sprintf("%02d",getCardNum($config,$station))])) { 
                     print('STATUS_CARD'.getCardNum($config,$station).":".$status['card-'.sprintf("%02d",getCardNum($config,$station))].' '); 
            } else { 
                
                 //print('STATUS_CARD'.getCardNum($config,$station).": NOT_DETECTABLE"); 
                 print('STATUS_CARD'.getCardNum($config,$station).":"); 
                 }      
            //print(exec(  '/bin/bash -c "lpstat -p CARD'.sprintf("%02d",getCardNum($config,$station)).' "',$output));
            }
        if($_GET['type']=="LABEL") {
            if (isset($status['label-'.sprintf("%02d",getLabelNum($config,$station))])) { 
                   print('STATUS_LABEL'.getLabelNum($config,$station).":".$status['label-'.sprintf("%02d",getLabelNum($config,$station))].' '); }
            else { print('STATUS_LABEL'.getLabelNum($config,$station).": NOT_DETECTABLE"); }    
            //print(exec( '/bin/bash -c "lpstat -p LABEL'.sprintf("%02d",getLabelNum($config,$station)).' "',$output));
            }            
    } else { print("FAIL: missing GET_PARAM:type"); }
} else { print("FAIL: missing GET_PARAM:id"); }

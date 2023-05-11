<?php

require_once("/var/www/printserver-functions.php");
$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

if (file_exists($configfile))  {                  $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }

if(isset($_POST) AND !empty($_POST)) 
    {
     if(isset($_GET['act']) && $_GET['act']=="savetoken") {
        if (!file_exists('/dev/shm/client_token')) {
            mkdir('/dev/shm/client_token', 0750, true);
        }
        if(isset($_GET['act']) && $_GET['act']=="savetoken") {
            if(isset($_POST['val'])) {
              if(isset($_GET['usr'])) {
                $RealIP=explode(".",getRealIpAddr());
                $lastOctet=$RealIP[3];
                file_put_contents('/dev/shm/client_token/'.$lastOctet."-".$_GET['usr'].tok,$_POST['val'])
              }                
            }
        }

    } 
    //file_put_contents('/tmp/initPOST.log', print_r($_POST, true)."\n".print_r($_GET, true)); //DEBUG...DUMP POST REQUEST
    //header("HTTP/1.0 204 No Content");    exit;
    }

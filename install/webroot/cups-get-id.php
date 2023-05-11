<?php
//printer routing config

function url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url( $s, $use_forwarded_host = false )
    {    return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI']; } //$absolute_url = full_url( $_SERVER );

function curPageURL() {    
    return strtok(full_url( $_SERVER ), '?'); //return url_origin( $_SERVER ) . strtok( $s['REQUEST_URI'], '\?');
    }

$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

function emptyPrinterConfig($count = 16) {    
    $route = array_fill(1, $count ,array_fill_keys(array('card','label','labelmode','cardmode','cardribbon'),'1'));
    foreach ($route as $key => $value)
            { $route[$key]['label']=$key;$route[$key]['card']=$key;$route[$key]['cardmode']='DUPLEX_MM';$route[$key]['cardribbon']='RM_KBLACK';$route[$key]['labelmode']='WIRE_BLK'; }
    return $route;
}

function initPrinterConfig($configfile , $count = 16)
    {    file_put_contents($configfile,json_encode(emptyPrinterConfig($count)));     }

if (file_exists($configfile))  {                 $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }
            
function getCardNum($config , $station)            { return sprintf("%02d",$config[$station]['card']); }
function getCardMode($config , $station)           { return $config[$station]['cardmode']; }
function getCardRibbon($config ,$station)          { return $config[$station]['cardribbon']; }
function getLabelNum($config , $station)           { return sprintf("%02d",$config[$station]['label']); }
function getLabelMode($config , $station)          { return $config[$station]['labelmode']; }


if(isset($_GET['id'])) { 
    $client=0;
    //$station=$_GET['id'];
        $lastOctet=intval($_GET['id']);
        // 101..116 GET A STRAIGHT 1:1 MAPPING
        if ($lastOctet > 0 && $lastOctet < 16 )
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

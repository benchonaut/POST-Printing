<?php

class DumpHTTPRequestToFile {
    public function execute($targetFile) {
        $data = sprintf(
            "%s %s %s\n\nHTTP headers:\n",
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['SERVER_PROTOCOL']
        );


        foreach ($this->getHeaderList() as $name => $value) {
            $data .= $name . ': ' . $value . "\n";
        }

        $data .= "\nRequest body:\n";

        file_put_contents(
            $targetFile,
            $data . file_get_contents('php://input') . "\n"
        );

        //echo("Done!\n\n");
    }

    private function getHeaderList() {
        $headerList = [];
        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/',$name)) {
                // convert HTTP_HEADER_NAME to Header-Name
                $name = strtr(substr($name,5),'_',' ');
                $name = ucwords(strtolower($name));
                $name = strtr($name,' ','-');

                // add to list
                $headerList[$name] = $value;
            }
        }

        return $headerList;
    }
}
function numbersOfString($string) {
    preg_match_all('!\d+!', $string, $matches);
    return implode("",$matches[0]);
}

function what_did_they_really_send() {
  /*$req_dump = print_r($_REQUEST, TRUE);
  $fp = fopen('/tmp/.debug_out/dump_request.log', 'w');
  fwrite($fp, $req_dump);
  fclose($fp);*/
    (new DumpHTTPRequestToFile)->execute('/tmp/.debug_out/dump_request.log');
}
function is_JSON($string) {
  json_decode($string);
  return (json_last_error() == JSON_ERROR_NONE);
}

function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))             //check ip from shared internet
    {      $ip=$_SERVER['HTTP_CLIENT_IP'];    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is passed from proxy
    {      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];    }
    else
    {      $ip=$_SERVER['REMOTE_ADDR'];    }            //both missing , use remote address
    return $ip;
}


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
    

function emptyPrinterConfig($count = 16) {    
    $route = array_fill(1, $count ,array_fill_keys(array('card','label','labelmode','cardmode','cardribbon'),'1'));
    foreach ($route as $key => $value)
            { $route[$key]['label']=$key;$route[$key]['card']=$key;$route[$key]['cardmode']='DUPLEX_MM';$route[$key]['cardribbon']='RM_KBLACK';$route[$key]['labelmode']='WIRE_BLK'; }
    return $route;
}

function initPrinterConfig($configfile , $count = 16)
    {    file_put_contents($configfile,json_encode(emptyPrinterConfig($count)));     }
function getCardNum($config , $station)            { return sprintf("%02d",$config[$station]['card']); }
function getCardMode($config , $station)           { return $config[$station]['cardmode']; }
function getCardRibbon($config ,$station)          { return $config[$station]['cardribbon']; }
function getLabelNum($config , $station)           { return sprintf("%02d",$config[$station]['label']); }
function getLabelMode($config , $station)          { return $config[$station]['labelmode']; }

function setCardNum($conf_obj , $station, $num)
        { global $configfile;$conf_obj[$station]['card']=$num; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }
function setCardMode($conf_obj , $station, $mode)
        { global $configfile;$conf_obj[$station]['cardmode']=$mode; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }
function setCardRibbon($conf_obj , $station, $ribbon)
        { global $configfile;$conf_obj[$station]['cardribbon']=$ribbon; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }

function setLabelNum($conf_obj , $station,$num)
        { global $configfile;$conf_obj[$station]['label']=$num; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }
        
function setLabelMode($conf_obj , $station,$mode)
        { global $configfile;$conf_obj[$station]['labelmode']=$mode; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }


if (!function_exists("get_http_code")) {
  function get_http_code($url) {
    $handle = curl_init($url);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    return $httpCode;         
  }    
}

if (!function_exists('str_starts_with')) {
  function str_starts_with($str, $start) {
    return (@substr_compare($str, $start, 0, strlen($start))==0);
  }
}

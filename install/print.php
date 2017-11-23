<?php 

// (int)$_POST['client'];
// (int)$_POST['type']; // card,label 
//$_POST['file']; //base64 encoded pdf
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {      $ip=$_SERVER['HTTP_CLIENT_IP'];    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];    }
    else
    {      $ip=$_SERVER['REMOTE_ADDR'];    }
    return $ip;
}


$RealIP=explode(".",getRealIpAddr());
$lastOctet=$RealIP[3];

if (($lastOctet > 100) && ($lastOctet<200))
	{ $client=$lastOctet % 100 ; }
	else { $client=$_POST['client']; }

$configfile=getenv("HOME").'/.printroute.json';
$config=array();
function emptyPrinterConfig($count = 16) {	
	$route = array_fill(1, $count ,array_fill_keys(array('card','label'),'1'));
	foreach ($route as $key => $value)
			{ $route[$key]['label']=$key;$route[$key]['card']=$key; }
	return $route;
}

function initPrinterConfig($configfile , $count = 16)
	{	echo 1;
		file_put_contents($configfile,json_encode(emptyPrinterConfig($count))); 	}

if (file_exists($configfile))  { 				$config=json_decode(file_get_contents($configfile),1); }
		else { initPrinterConfig($configfile);	$config=json_decode(file_get_contents($configfile),1); }
		
function getCardNum($config , $station)		{ return sprintf("%02d",$config[$station]['card']); }
function getLabelNum($config , $station)	{ return sprintf("%02d",$config[$station]['label']); }
header("HTTP/1.0 204 No Content");
$filename='/tmp/'.getmygid().getmypid().'.pdf';
	if ($_POST['type'] == 'card' ) {
			$printer='CARD'.getCardNum($config,$client);
			file_put_contents($filename, base64_decode($_POST['file']));
			exec('lpadmin -p'.$printer.' -o GDuplexMode=DUPLEX_MM -o GRibbonType=RM_KBLACK ;');
			exec('lpr -o landscape -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename);
			echo 'queued-client'.$client.' printer '.getCardNum($config,$client);
			} 
	elseif ($_POST['type'] == 'label') {
		    
			$printer='LABEL'.getLabelNum($config,$client);
			file_put_contents($filename, base64_decode($_POST['file']));
			exec('lpadmin -p'.$printer.' -o PageSize=62x100 ;');
			exec('lpr -o fit-to-page -P'.$printer.' -r '.$filename);
			echo 'queued-client'.$client.' printer '.getLabelNum($config,$client);
			};

exit
?>

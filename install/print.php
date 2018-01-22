<?php 

// (int)$_POST['client'];
// $_POST['type']; // card,label 
// $_POST['file']; //base64 encoded pdf
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


function emptyPrinterConfig($count = 16) {	
	$route = array_fill(1, $count ,array_fill_keys(array('card','label','cardmode','cardribbon'),'1'));
	foreach ($route as $key => $value)
			{ $route[$key]['label']=$key;$route[$key]['card']=$key;$route[$key]['cardmode']='DUPLEX_MM';$route[$key]['cardribbon']='RM_KBLACK'; }
	return $route;
}

function initPrinterConfig($configfile , $count = 16)
	{	echo INIT;
		file_put_contents($configfile,json_encode(emptyPrinterConfig($count))); 	}

//$configfile='/var/www/.printroute.json';
	$configfile=getenv("HOME").'/.printroute.json';
	$config=array();
		
if (file_exists($configfile))  { 				$config=json_decode(file_get_contents($configfile),1); }
		else { initPrinterConfig($configfile);	$config=json_decode(file_get_contents($configfile),1); }
		//print_r($config);
function getCardNum($config , $station)		{ return sprintf("%02d",$config[$station]['card']); }
function getCardMode($config , $station)		{ return $config[$station]['cardmode']; }
function getCardRibbon($config ,$station)		{ return $config[$station]['cardribbon']; }
function getLabelNum($config , $station)	{ return sprintf("%02d",$config[$station]['label']); }

if(isset($_POST) AND !empty($_POST)) 
	{
	$RealIP=explode(".",getRealIpAddr());
	$lastOctet=$RealIP[3];
	
	if (($lastOctet > 100) && ($lastOctet<200))
		{ $client=$lastOctet % 100 ; }
		else { $client=$_POST['client']; }

	
	$filename='/tmp/'.getmygid().getmypid().'.pdf';
	
	if ($_POST['type'] == 'card' ) {
			$printer='CARD'.getCardNum($config,$client);
			file_put_contents($filename, base64_decode($_POST['file']));
			exec('lpadmin -p'.$printer.' -o GDuplexMode='.getCardMode($config,$client).' -o GRibbonType='.getCardRibbon($config,$client).' ;');
			exec('lpr -o landscape -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename);
			echo 'queued-client'.$client.' printer '.getCardNum($config,$client);
			header("HTTP/1.1 200 OK");
			} 
	elseif ($_POST['type'] == 'label') {	    
			$printer='LABEL'.getLabelNum($config,$client);
			file_put_contents($filename, base64_decode($_POST['file']));
			exec('lpadmin -p'.$printer.' -o PageSize=62x100 ;');
			exec('lpr -o fit-to-page -P'.$printer.' -r '.$filename);
			echo 'queued-client'.$client.' printer '.getLabelNum($config,$client);
			header("HTTP/1.1 200 OK");
			};
	}
else
	{
	header("HTTP/1.0 204 No Content");
	}

exit
?>

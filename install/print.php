<?php 
// (int)$_POST['client'];	// station number(not sanitized)
// $_POST['type']; 			// card,label 
// $_POST['file']; 			//base64 encoded pdf
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
	$route = array_fill(1, $count ,array_fill_keys(array('card','label','labelmode','cardmode','cardribbon'),'1'));
	foreach ($route as $key => $value)
			{ $route[$key]['label']=$key;$route[$key]['card']=$key;$route[$key]['cardmode']='DUPLEX_MM';$route[$key]['cardribbon']='RM_KBLACK';$route[$key]['labelmode']='WIRE_BLK'; }
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
function getLabelMode($config , $station)		{ return $config[$station]['labelmode']; }

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
			$orientation='landscape'; //check for portrait parameter,otherwise autodetect , fallback to parameter defined here
			if (!empty($_SERVER['portrait']))   
				{
			    //use paramater portrait [true|false] to print specific mode, 
				if ($_POST['portrait'] == 'true' ) { exec('lpr -o portrait -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename); $orientation='portrait';}
				elseif ($_POST['portrait'] == 'false' ) { exec('lpr -o landscape -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename); $orientation='landscape';}
				}
			else  {	 // portrait/landscape not set
					$pdfinfoout = shell_exec("pdfinfo ".$filename);
					preg_match('~Page size: +([0-9\.]+) x ([0-9\.]+) pts~', $pdfinfoout, $matches);
					if (!empty($matches[2]))	{  
						$width=intval($matches[1]);
						$height=intval($matches[2]);
						if ( $width > $height ) { $orientation='landscape'; }
						elseif ( $height > $width ) { $orientation='portrait'; }
						else { $orientation='landscape'; } // x=y , a square
					} 
					exec('lpr -o '.$orientation.' -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename);	 
			}
			echo 'queued-client'.$client.'oritentation:'.$orientation.' printer '.getCardNum($config,$client);
			header("Access-Control-Allow-Origin: *");
			header("HTTP/1.1 200 OK");
			} 
	elseif ($_POST['type'] == 'label') {	    
			$printer='LABEL'.getLabelNum($config,$client);
			file_put_contents($filename, base64_decode($_POST['file']));
			if ( getLabelMode($config,$client) == 'WIRE_BLK') {
				exec('lpadmin -p'.$printer.' -o PageSize=62x100 ;');
				exec('lpr -o fit-to-page -P'.$printer.' -r '.$filename);
				exec('rm '.$filename);
				}
			if ( getLabelMode($config,$client) == 'WIRE_29x90') {
				exec('lpadmin -p'.$printer.' -o PageSize=29x90 ;');
				exec('lpr -o fit-to-page -P'.$printer.' -r '.$filename);
				exec('rm '.$filename);
				}
			if ( getLabelMode($config,$client) == 'WIFI_RED') {
				$printerip=exec('lpoptions  -p '.$printer.' | awk \'{for (i=1; i<=NF; i++) {if ($i ~ /device-uri/) {print $i}}}\' |cut -d"/" -f3');
				$convertres=exec('convert '.$filename.' -resize x1108 /tmp/'.$filename.'.jpg');
				$printres=exec('brother_ql -p tcp://'.$printerip.':9100 -m QL-810W -b network print -l 62 --red --lq /tmp/'.$filename.'.jpg');
				echo $printerip.'<br>'.$convertres.'<br>'.$printres ;//exec('rm /tmp/'.$filename.'.jpg '.$filename);
				}

			if ( getLabelMode($config,$client) == 'WIFI_BLK') {
				$printerip=exec('lpoptions  -p '.$printer.' | awk \'{for (i=1; i<=NF; i++) {if ($i ~ /device-uri/) {print $i}}}\' |cut -d"/" -f3');
				$convertres=exec('convert '.$filename.' -resize x1108 /tmp/'.$filename.'.jpg');
				$printres=exec('brother_ql -p tcp://'.$printerip.':9100 -m QL-810W -b network print -l 62 --lq /tmp/'.$filename.'.jpg');
				echo $printerip.'<br>'.$convertres.'<br>'.$printres ;//exec('rm /tmp/'.$filename.'.jpg '.$filename);
				}
			
			if ( getLabelMode($config,$client) == 'WIFI_29x90') {
				$printerip=exec('lpoptions  -p '.$printer.' | awk \'{for (i=1; i<=NF; i++) {if ($i ~ /device-uri/) {print $i}}}\' |cut -d"/" -f3');
				$convertres=exec('convert '.$filename.' -resize 306x991 /tmp/'.$filename.'.jpg');
				$printres=exec('brother_ql -p tcp://'.$printerip.':9100 -m QL-810W -b network print -l 29x90 --lq /tmp/'.$filename.'.jpg');
				echo $printerip.'<br>'.$convertres.'<br>'.$printres ;//exec('rm /tmp/'.$filename.'.jpg '.$filename);
				}
			echo 'queued-client'.$client.' printer '.getLabelNum($config,$client);
			header("Access-Control-Allow-Origin: *");
			header("HTTP/1.1 200 OK");
			};
	}
else
	{
	header("HTTP/1.0 204 No Content");
	}

exit
?>

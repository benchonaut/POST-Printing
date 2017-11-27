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
exec('/bin/bash /etc/printer_status.sh '.$statusfile);
$status=json_decode(file_get_contents($statusfile),1);
print_r(file_get_contents($statusfile),1);
function emptyPrinterConfig($count = 16) {	
	$route = array_fill(1, $count ,array_fill_keys(array('card','label'),'1'));
	foreach ($route as $key => $value)
			{ $route[$key]['label']=$key;$route[$key]['card']=$key; }
	return $route;
}

function initPrinterConfig($configfile , $count = 16)
	{	file_put_contents($configfile,json_encode(emptyPrinterConfig($count))); 	}

if (file_exists($configfile))  { 				$config=json_decode(file_get_contents($configfile),1); }
		else { initPrinterConfig($configfile);	$config=json_decode(file_get_contents($configfile),1); }
		
function getCardNum($config , $station)		{ return $config[$station]['card']; }

function getLabelNum($config , $station)	{ return $config[$station]['label']; }

function setCardNum($conf_obj , $station, $num)
		{ global $configfile;$conf_obj[$station]['card']=$num; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }

function setLabelNum($conf_obj , $station,$num)
		{ global $configfile;$conf_obj[$station]['label']=$num; file_put_contents($configfile,json_encode($conf_obj)); return $conf_obj; }


if(isset($_POST) AND !empty($_POST)) 
	{
	//file_put_contents('/tmp/printrouterPOST.log', print_r($_POST, true)); //DEBUG...DUMP POST REQUEST
	foreach ($_POST as $action => $value) { 
	$act=explode("_", $action);
	if ($act[0] == 'label' ) 	{ $config=setLabelNum($config,$act[1],$value); header("HTTP/1.0 204 No Content");	exit; }
	elseif ($act[0] == 'card')	{ $config=setCardNum($config,$act[1],$value);  header("HTTP/1.0 204 No Content");	exit; }
	 } 
	 if(isset($_POST['Rotate']))
		{
		if ($_POST['Rotate'] == 'Front')
			{ $execute='';for ($a=1;$a<16;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o FPageRotate180=yes;' ; } ; exec($execute); } 
		elseif ($_POST['Rotate'] == 'Back')
			{ $execute='';for ($a=1;$a<16;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o BPageRotate180=yes;' ; } ; exec($execute); } 
		}
	if(isset($_POST['NoRotate']))
		{			
		elseif ($_POST['NoRotate'] == 'Front')	 
			{ $execute='';for ($a=1;$a<16;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o FPageRotate180=no;' ; } ; exec($execute); } 
		elseif ($_POST['NoRotate'] == 'Back')	 
			{ $execute='';for ($a=1;$a<16;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o BPageRotate180=no;' ; } ; exec($execute); } 
		}
	// $action = $_GET['action']; 
	// $agent_id = $_POST['agent_id']; 
	}

//file_put_contents('/tmp/printrouterCONF.log', print_r(count((array)$config))); //DEBUG...DUMP config object count
//station id is determined by last number of ipv4
print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Printer Selector '.curPageURL().'</title></head><body><h3>Printer Routing</h3>');
print('<hr>Card Rotation(all printers):<br><table><tr><td>');
print('<form method="POST" action="'.curPageURL().'?action=NoRotFront" onchange="document.getElementById(\'NoRotFront\').form.submit();location.reload()"> <button name="NoRotate" id="NoRotFront" value="Front">Straight Front</button></form>');
print('</td><td><form method="POST" action="'.curPageURL().'?action=RotFront" onchange="document.getElementById(\'RotFront\').form.submit();location.reload()"> <button name="Rotate" id="RotFront" value="Front">Rotate Front</button></form>');
print('</td><td><form method="POST" action="'.curPageURL().'?action=NoRotBack" onchange="document.getElementById(\'NoRotBack\').form.submit();location.reload()"> <button name="NoRotate" id="NoRotBack" value="Back">Straight Back</button></form>');
print('</td><td><form method="POST" action="'.curPageURL().'?action=RotBack" onchange="document.getElementById(\'RotBack\').form.submit()"> <button name="Rotate" id="RotBack" value="Back">Rotate Back</button></form>');
print('</td></tr></table>');
print('</tr></table><hr><table><tr><th>Station</th><th>Card<br>Printer</th><th>Card<br>Status</th><th>Label<br>Printer</th><th>Label<br>Status</th></tr><tr>');
   for($station=1; $station < count((array)$config) ; $station++) {
		print('<td>'.$station.'</td>');
		print('<td><form method="POST" action="'.curPageURL().'?action=card" onchange="document.getElementById(\'card_'.$station.'\').form.submit()"> <select id=card_'.$station.'  name=card_'.$station.'  required><option selected>'.getCardNum($config,$station)); 
			for($i=1; $i < count((array)$config) + 1; $i++) { print('<option>'.$i); }
		print('</select></form></td>');
		if (isset($status['card-'.sprintf("%02d",$station)])) { print('<td>'.$status['card-'.sprintf("%02d",$station)].'</td>'); }
			else { print('<td >..</td>'); }
		print('<td><form method="POST" action="'.curPageURL().'?action=label" onchange="document.getElementById(\'label_'.$station.'\').form.submit()"><select id=label_'.$station.' name=label_'.$station.' required><option selected>'.getLabelNum($config,$station));
			for($i=1; $i < count((array)$config) + 1 ; $i++) { print('<option>'.$i); }
		print('</select></form></td>');
		if (isset($status['label-'.sprintf("%02d",$station)])) { print('<td>'.$status['label-'.sprintf("%02d",$station)].'</td>'); }
			else { print('<td >..</td>'); }		
		print('</tr>');
	}
print('</table>');
print('</body></html>');

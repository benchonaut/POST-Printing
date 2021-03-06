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
	{	file_put_contents($configfile,json_encode(emptyPrinterConfig($count))); 	}

if (file_exists($configfile))  { 				$config=json_decode(file_get_contents($configfile),1); }
		else { initPrinterConfig($configfile);	$config=json_decode(file_get_contents($configfile),1); }
			
function getCardNum($config , $station)			{ return sprintf("%02d",$config[$station]['card']); }
function getCardMode($config , $station)		{ return $config[$station]['cardmode']; }
function getCardRibbon($config ,$station)		{ return $config[$station]['cardribbon']; }
function getLabelNum($config , $station)		{ return sprintf("%02d",$config[$station]['label']); }
function getLabelMode($config , $station)		{ return $config[$station]['labelmode']; }

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


if(isset($_POST) AND !empty($_POST)) 
	{
	//file_put_contents('/tmp/printrouterPOST.log', print_r($_POST, true)); //DEBUG...DUMP POST REQUEST
	foreach ($_POST as $action => $value) { 
	$act=explode("_", $action);
	if ($act[0] == 'label' ) 			{ $config=setLabelNum($config,$act[1],$value); 		header("HTTP/1.0 204 No Content");	exit; }
	elseif ($act[0] == 'labelmode')		{ $config=setLabelMode($config,$act[1],$value);  	header("HTTP/1.0 204 No Content");	exit; }
	elseif ($act[0] == 'cardmode')		{ $config=setCardMode($config,$act[1],$value);  	header("HTTP/1.0 204 No Content");	exit; }
	elseif ($act[0] == 'cardribbon')	{ $config=setCardRibbon($config,$act[1],$value);  	header("HTTP/1.0 204 No Content");	exit; }
	elseif ($act[0] == 'card')	{ $config=setCardNum($config,$act[1],$value);  header("HTTP/1.0 204 No Content");	exit; }
	 } 
	 if(isset($_POST['Rotate']))
		{
		if ($_POST['Rotate'] == 'Front')
			{ $execute='';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o FPageRotate180=ON;' ; } ; exec($execute);  } 
		elseif ($_POST['Rotate'] == 'Back')
			{ $execute='';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o BPageRotate180=ON;' ; } ; exec($execute); } 
		}
	if(isset($_POST['NoRotate']))
		{			
		if ($_POST['NoRotate'] == 'Front')	 
			{ $execute='';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o FPageRotate180=OFF;' ; } ; exec($execute); } 
		elseif ($_POST['NoRotate'] == 'Back')	 
			{ $execute='';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o BPageRotate180=OFF;' ; } ; exec($execute); } 
		}
		//header("HTTP/1.0 204 No Content");	exit;
	// $action = $_GET['action']; 
	// $agent_id = $_POST['agent_id']; 
	}

//file_put_contents('/tmp/printrouterCONF.log', print_r(count((array)$config))); //DEBUG...DUMP config object count
//station id is determined by last number of ipv4
exec('/bin/bash /etc/printer_status.sh '.$statusfile);
$status=json_decode(file_get_contents($statusfile),1);
print_r(file_get_contents($statusfile),1);

print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Printer Selector '.curPageURL().'</title>');

print('<style>
table {
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid black;
}

table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    padding: 3px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

th {
	font-size: 90%;
    background-color: #2499e2;
    color: white;
    border-top: none;
}

tr:hover {background-color:#016daf;}
td:hover {background-color:#6c76c1;}
th:hover {background-color:#ff76ff;}
tr:nth-child(even) {background-color: #debecf;}

table {
	font-size: 80%;
    border-collapse:separate;
    border:solid black 1px;
    border-radius:6px;
    -moz-border-radius:6px;
}

td, th {
    border-left:solid black 1px;
    border-top:solid black 1px;
}

td:first-child, th:first-child {
     border-left: none;
}
</style>
<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
</head><body><h3>Printer Routing</h3>
');

print('<hr>Card Rotation(all printers):<br><table align=center><tr><th>');
print('           <form method="POST" action="'.curPageURL().'?action=NoRotFront" onchange="document.getElementById(\'NoRotFront\').form.submit();history.go(0);"> <button name="NoRotate" id="NoRotFront" value="Front">Straight Front</button></form>');
print('  </th><th><form method="POST" action="'.curPageURL().'?action=RotFront"   onchange="document.getElementById(\'RotFront\').form.submit();history.go(0);"> <button name="Rotate" id="RotFront" value="Front">Rotate Front 180°</button></form>');
print('</th></tr></table>');
print('<table><tr><th><form method="POST" action="'.curPageURL().'?action=NoRotBack"  onchange="document.getElementById(\'NoRotBack\').form.submit();history.go(0);"> <button name="NoRotate" id="NoRotBack" value="Back">Straight Back</button></form>');
print('  </th><th><form method="POST" action="'.curPageURL().'?action=RotBack"    onchange="document.getElementById(\'RotBack\').form.submit();history.go(0);"> <button name="Rotate" id="RotBack" value="Back">Rotate Back 180°</button></form>');
print('</th></tr></table><b>Label Settings: WIFI_RED=DK22261 , WIFI_BLACK=22205,WIFI_THIN=DK1201( 29mmx90.3 Address) </b>');
print('<hr><table align=center><tr><th>Station<br>/Printer</th><th>Card<br>Printer</th>
<th>Card<br>Duplex</th><th>Card<br>Ribbon</th>
<th>Card Status(Printer Number)</th><th>Label<br>Printer</th><th>Label Status(Printer Number)</th><th>Label Mode</th></tr><tr>');
   for($station=1; $station < count((array)$config) + 1 ; $station++) {
		print('<td>'.$station.'</td>');
		print('<td ><form method="POST" action="'.curPageURL().'?action=card" onchange="document.getElementById(\'card_'.$station.'\').form.submit()"> <select id=card_'.$station.'  name=card_'.$station.'  required><option selected>'.getCardNum($config,$station)); 
				for($i=1; $i < count((array)$config) + 1; $i++) { print('<option>'.$i); }
		print('</select></form></td>');
		print('<td ><form method="POST" action="'.curPageURL().'?action=cardmode" onchange="document.getElementById(\'cardmode_'.$station.'\').form.submit()"> <select id=cardmode_'.$station.'  name=cardmode_'.$station.'  required><option selected>'.getCardMode($config,$station)); 
				$opt = array('SIMPLEX','DUPLEX_CC','DUPLEX_CM','DUPLEX_MM','DUPLEX_MC');
				$opt = array_diff($opt, array(getCardMode($config,$station)));$opt = array_values($opt);
				foreach ($opt as &$value){ print('<option>'.$value); }
		print('</select></form></td>');

		print('<td ><form method="POST" action="'.curPageURL().'?action=cardribbon" onchange="document.getElementById(\'cardribbon_'.$station.'\').form.submit()"> <select id=cardribbon_'.$station.'  name=cardribbon_'.$station.'  required><option selected>'.getCardRibbon($config,$station)); 
				$opt = array('RC_YMCKO','RC_YMCKOS','RC_YMCKOK','RC_YMCKOKOS','RM_KO','RM_KBLACK','RM_KWHITE','RM_KRED','RM_KGREEN','RM_KBLUE','RM_KSCRATCH','RM_KMETALSILVER','RM_KMETALGOLD','RM_KSIGNATURE','RM_KWAX','RM_KPREMIUM','RM_HOLO');
				$opt = array_diff($opt, array(getCardRibbon($config,$station)));$opt = array_values($opt);
				foreach ($opt as &$value){ print('<option>'.$value); }
		print('</select></form></td>');

		if (isset($status['card-'.sprintf("%02d",$station)])) { print('<td>'.$status['card-'.sprintf("%02d",$station)].'</td>'); }
			else { print('<td >..</td>'); }
		print('<td ><form method="POST" action="'.curPageURL().'?action=label" onchange="document.getElementById(\'label_'.$station.'\').form.submit()"><select id=label_'.$station.' name=label_'.$station.' required><option selected>'.getLabelNum($config,$station));
				for($i=1; $i < count((array)$config) + 1 ; $i++) { print('<option>'.$i); }
			print('</select></form></td>');
		
		if (isset($status['label-'.sprintf("%02d",$station)])) { print('<td>'.$status['label-'.sprintf("%02d",$station)].'</td>'); }
			else { print('<td >..</td>'); }	
		print('<td ><form method="POST" action="'.curPageURL().'?action=labelmode" onchange="document.getElementById(\'labelmode_'.$station.'\').form.submit()"> <select id=labelmode_'.$station.'  name=labelmode_'.$station.'  required><option selected>'.getLabelMode($config,$station)); 
				$opt = array('WIRE_BLK','WIRE_29x90','WIFI_BLK','WIFI_RED','WIFI_29x90');
				$opt = array_diff($opt, array(getLabelMode($config,$station)));$opt = array_values($opt);
				foreach ($opt as &$value){ print('<option>'.$value); }
		print('</select></form></td>');	
		print('</tr>');
	}
print('</table>');
print('</body></html>');

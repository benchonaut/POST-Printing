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
    //file_put_contents('/tmp/printrouterPOST.log', print_r($_POST, true)); //DEBUG...DUMP POST REQUEST
    foreach ($_POST as $action => $value) { 
    $act=explode("_", $action);
    if ($act[0] == 'label' )             { $config=setLabelNum($config,$act[1],$value);         header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'labelmode')        { $config=setLabelMode($config,$act[1],$value);      header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'cardmode')        { $config=setCardMode($config,$act[1],$value);      header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'cardribbon')    { $config=setCardRibbon($config,$act[1],$value);      header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'card')    { $config=setCardNum($config,$act[1],$value);  header("HTTP/1.0 204 No Content");    exit; }
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
        //header("HTTP/1.0 204 No Content");    exit;
    // $action = $_GET['action']; 
    // $agent_id = $_POST['agent_id']; 
    }

//file_put_contents('/tmp/printrouterCONF.log', print_r(count((array)$config))); //DEBUG...DUMP config object count
//station id is determined by last number of ipv4
exec('/bin/bash /etc/printer_status.sh '.$statusfile);
$status=json_decode(file_get_contents($statusfile),1);
//print_r(file_get_contents($statusfile),1);

print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Printer Route Status '.curPageURL().'</title>');

print('<style>
body {
background: radial-gradient(ellipse at center, #f5f5f5 0%,#ddd 100%);
}
table {
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid black;
    border-radius: 8px 4px 6px / 4px 6px;
}

table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    padding: 3px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    border-top:solid black 1px;
    border-left:solid black 1px;
    border-top:solid black 1px;
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
<meta http-equiv="refresh" content="30; " />
</head><body><h3>Printer Routing Status</h3><br>
This page will reload every 30 seconds<br>
');
print("\n");
print('<hr><table align=center><thead><th>Station<br>Number</th><th>Card<br>Printer</th><th>Label<br>Printer</th><td bgcolor="black">.</td><th>Printer<br>Number</th>
<th>Card<br>Duplex</th><th>Card<br>Ribbon</th>
<th>Card Status(Printer Number)</th><th>Printer<br>Number</th><th>Label Status(Printer Number)</th><th>Label Mode</th></thead><tr>');
print("\n");
   for($station=1; $station < count((array)$config) + 1 ; $station++) {
        print("\n");
        print('<td>'.$station.'</td>');
        print("\n");
        print('<td >CARD:'.getCardNum($config,$station).'</td>');
        print("\n");
        print('<td >LABEL:'.getLabelNum($config,$station).'</td>');
        print("\n");
        print('<td bgcolor="black">.</td>');
        print("\n");
        print('<td>CARD'.$station.'</td>');
        print("\n");
        print('<td >MODE:'.getCardMode($config,$station).'</td>');
        print("\n");
        print('<td >RIBBON:'.getCardRibbon($config,$station).'</td>');
        print("\n");
        if (isset($status['card-'.sprintf("%02d",$station)])) { print('<td>'.$status['card-'.sprintf("%02d",$station)].'</td>'); }
            else { print('<td >..</td>'); }
        print("\n");
        print("\n");
        print('<td>LABEL'.$station.'</td>');
        print("\n");
        if (isset($status['label-'.sprintf("%02d",$station)])) { print('<td>'.$status['label-'.sprintf("%02d",$station)].'</td>'); }
            else { print('<td >..</td>'); }    
        print("\n");
        print('<td >LMODE:'.getLabelMode($config,$station).'</td>');    
        print("\n");
        print('</tr>');
    }
print('</table>');
print('</body></html>');
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');

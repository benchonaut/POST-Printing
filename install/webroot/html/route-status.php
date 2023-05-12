<?php
require_once("/var/www/printserver-functions.php");
$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

if (file_exists($configfile))  {                  $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }

//file_put_contents('/tmp/printrouterCONF.log', print_r(count((array)$config))); //DEBUG...DUMP config object count
//station id is determined by last number of ipv4
exec('/bin/bash /etc/printer_status.sh '.$statusfile);
$status=json_decode(file_get_contents($statusfile),1);
//print_r(file_get_contents($statusfile),1);

header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Connection: close");

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
<script>
function padWithLeadingZeros(num, totalLength) {
  return String(num).padStart(totalLength, "0");
}

function status_parse(data) { 
//console.log("GOT STATUS JSON: | ", data);
var cardid="none"
var labelid="none"

for (let i = 1; i < 17; i++) {
cardid="card-"+padWithLeadingZeros(i, 2);
labelid="label-"+padWithLeadingZeros(i, 2);

if (cardid in data) {
   //console.log(cardid+" found");
   target="cardstatus"+padWithLeadingZeros(i, 2);
   status=data[cardid];
   status=status.replace(/(^\|)/gi, "");
   document.getElementById(target).innerHTML=status.replace("/\|/g", "<br>");
}
if (labelid in data) {
   //console.log(labelid+" found");
   target="labelstatus"+padWithLeadingZeros(i, 2);
   status=data[labelid];
   status=status.replace(/(^\|)/gi, "");
   document.getElementById(target).innerHTML=status.replace("/\|/g", "<br>");
}

}  

}

//var url = "http://127.0.0.1:8888/status.json.php";


setTimeout(function () {
        var jsonurl = "'.url_origin( $_SERVER, true ) .'/status.json.php";
        
        fetch(jsonurl)
        .then(res => res.json())
        .then(out =>
        status_parse(out))
        .catch(err => { throw err });
                    }, 15000);
    
</script>
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
<meta http-equiv="refresh" content="42; " />
</head><body><h3>Printer Routing Status</h3><br>
This page will reload every 42 seconds, printer status will be updated every 15 seconds<br>
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
        if (isset($status['card-'.sprintf("%02d",$station)])) { print('<td id="cardstatus'.sprintf("%02d",$station).'" >'.str_replace("|","<br>",ltrim($status['card-'.sprintf("%02d",$station)]), "|").'</td>'); }
            else { print('<td id="cardstatus'.sprintf("%02d",$station).'" >..</td>'); }
        print("\n");
        print("\n");
        print('<td>LABEL'.$station.'</td>');
        print("\n");
        if (isset($status['label-'.sprintf("%02d",$station)])) { print('<td id="labelstatus'.sprintf("%02d",$station).'" >'.str_replace("|","<br>",ltrim($status['label-'.sprintf("%02d",$station)]), "|").'</td>'); }
            else { print('<td id="labelstatus'.sprintf("%02d",$station).'" >..</td>'); }
        print("\n");
        print('<td >LMODE:'.getLabelMode($config,$station).'</td>');    
        print("\n");
        print('</tr>');
    }
print('</table>');
print('</body></html>');
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');

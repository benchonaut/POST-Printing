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
    if ($act[0] == 'label' )            { $config=setLabelNum($config,$act[1],$value);      header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'labelmode')     { $config=setLabelMode($config,$act[1],$value);     header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'cardmode')      { $config=setCardMode($config,$act[1],$value);      header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'cardribbon')    { $config=setCardRibbon($config,$act[1],$value);    header("HTTP/1.0 204 No Content");    exit; }
    elseif ($act[0] == 'card')          { $config=setCardNum($config,$act[1],$value);       header("HTTP/1.0 204 No Content");    exit; }
     } 
     if(isset($_POST['Rotate']))
        {
        if ($_POST['Rotate'] == 'Front')
            { $execute='/bin/bash -c "';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o FPageRotate180=ON & ' ; } ;$execute=$execute.' wait;'."find /tmp/.printerstatus/ -name 'status_card*' -delete ;  bash /etc/printer_status.sh |jq ".' " >/tmp/rotate.log '; exec($execute);  } 
        elseif ($_POST['Rotate'] == 'Back')
            { $execute='/bin/bash -c "';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o BPageRotate180=ON & ' ; } ;$execute=$execute.' wait;'."find /tmp/.printerstatus/ -name 'status_card*' -delete ;  bash /etc/printer_status.sh |jq".' " >/tmp/rotate.log '; exec($execute); } 
        }
    if(isset($_POST['NoRotate']))
        {            
        if ($_POST['NoRotate'] == 'Front')     
            { $execute='/bin/bash -c "';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o FPageRotate180=OFF & ' ; } ;$execute=$execute.' wait;'."find /tmp/.printerstatus/ -name 'status_card*' -delete ; bash /etc/printer_status.sh |jq ".' " >/tmp/rotate.log '; exec($execute); } 
        elseif ($_POST['NoRotate'] == 'Back')     
            { $execute='/bin/bash -c "';for ($a=1;$a< count((array)$config) + 1;$a++ ) { $num=sprintf("%02d",$a);$execute=$execute.'lpadmin -p CARD'.$num.' -o BPageRotate180=OFF &'  ; } ;$execute=$execute.' wait;'."find /tmp/.printerstatus/ -name 'status_card*' -delete ; bash /etc/printer_status.sh |jq".' " >/tmp/rotate.log '; exec($execute); } 
        }
        //header("HTTP/1.0 204 No Content");    exit;
    // $action = $_GET['action']; 
    // $agent_id = $_POST['agent_id']; 
    }


print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Printer Selector '.curPageURL().'</title>');

print('<style>
body {
background: radial-gradient(ellipse at center, #f5f5f5 0%,#ddd 100%);
}
/* <select> styles */
select {
  /* Reset */
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  border: 0;
  outline: 0;
  font: inherit;
  /* Personalize */
  width: 17em;
  height: 2em;
  padding: 0 4em 0 1em;
  background: url(/img/Caret_down_font_awesome_whitevariation.svg) no-repeat right 0.8em center/1.4em, linear-gradient(to left, rgba(255, 255, 255, 0.2) 3em, rgba(10, 60, 123, 0.6) 3em);
  color: white;
  border-radius: 0.25em;
  box-shadow: 0 0 1em 0 rgba(0, 0, 0, 0.2);
  cursor: pointer;
  /* <option> colors */
  /* Remove focus outline */
  /* Remove IE arrow */
}
select option {
  color: inherit;
  background-color: #320a28;
}
select:focus {
  outline: none;
}
select::-ms-expand {
  display: none;
}
.custom-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    height: 2em;
    padding: 10px 38px 10px 16px;
    background: #fff url("select-arrows.svg") no-repeat right 16px center;
    background-size: 10px;
    transition: border-color .1s ease-in-out,box-shadow .1s ease-in-out;
    border: 1px solid #ddd;
    border-radius: 3px;
}
.custom-select:hover {
    border: 1px solid #999;
}
.custom-select:focus {
    border: 1px solid #999;
    box-shadow: 0 3px 5px 0 rgba(0,0,0,.2);
    outline: none;
}
/* remove default arrow in IE */
select::-ms-expand {
    display:none;
}

table {
    border-collapse: collapse;
}

table, td, th {
    border-radius: 8px 4px 6px / 4px 6px;
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
    border-left:solid black 1px;
    border-top:solid black 1px;
}
tr {
    border-radius: 8px 4px 6px / 4px 6px;

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
function reload_with_message() {

    timeoutreload = setTimeout(function () {
        window.location.href = "'.url_origin( $_SERVER, true ) .'/setup/router.php";
        //history.go(0);
    }, 4235 );
    timeoutbody = setTimeout(function () {
        document.body.style.backgroundColor = "red"; document.body.style.color = "black";  document.body.innerHTML = "<center><h1>Please wait..<br> applying settings and reloading..</h1></center>"; 
    }, 235 );
}

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

');


     if(isset($_GET['action'])) {
        if ($_GET['action'] == 'RotFront'|| $_GET['action'] == 'NoRotFront'|| $_GET['action'] == 'NoRotBack'|| $_GET['action'] == 'RotBack') {
            print(' <meta http-equiv="refresh" content="4; URL='.url_origin( $_SERVER, true ) .'/setup/router.php"></head><body><center><h1>Reloading</h1></center><br><script>reload_with_message()</script></body>');
        }
    }

//file_put_contents('/tmp/printrouterCONF.log', print_r(count((array)$config))); //DEBUG...DUMP config object count
//station id is determined by last number of ipv4
exec('/bin/bash /etc/printer_status.sh '.$statusfile);
$status=json_decode(file_get_contents($statusfile),1);
//print_r(file_get_contents($statusfile),1);

print('</head><body><h3>Printer Routing</h3>');
print("\n");
print('<hr>GLobal Card Rotation(all printers):<br><table align=center><tr><th>');
print("\n");
print('           <form method="POST" action="'.curPageURL().'?action=NoRotFront" onchange="document.getElementById(\'NoRotFront\').form.submit();"> <button name="NoRotate" id="NoRotFront" value="Front">Straight Front</button></form>');
print("\n");
print('  </th><th><form method="POST" action="'.curPageURL().'?action=RotFront"   onchange="document.getElementById(\'RotFront\').form.submit();"> <button name="Rotate" id="RotFront" value="Front">Rotate Front 180°</button></form>');
print("\n");
print('</th></tr></table>');
print("\n");
print('<table><tr><th><form method="POST" action="'.curPageURL().'?action=NoRotBack"  onchange="document.getElementById(\'NoRotBack\').form.submit();"> <button name="NoRotate" id="NoRotBack" value="Back">Straight Back</button></form>');
print("\n");
print('</th><th><form method="POST" action="'.curPageURL().'?action=RotBack"    onchange="document.getElementById(\'RotBack\').form.submit();"> <button name="Rotate" id="RotBack" value="Back">Rotate Back 180°</button></form>');
print("\n");
print('</th></tr></table><b>Label Settings: WIFI_RED=DK22261 , WIFI_BLACK=22205,WIFI_THIN=DK1201( 29mmx90.3 Address) </b>');
print("\n");
print('<hr><table align=center><tr><th>Station<br>Number</th><th>Card<br>Printer</th><th>Label<br>Printer</th><td bgcolor="black">.</td><th>Printer<br>Number</th>
<th>Card<br>Duplex</th><th>Card<br>Ribbon</th>
<th>Card Status(Printer Number)</th><th>Printer<br>Number</th><th>Label Status(Printer Number)</th><th>Label Mode</th></tr><tr>');
print("\n");
   for($station=1; $station < count((array)$config) + 1 ; $station++) {
        print("\n");
        print('<td>'.$station.'</td>');
        print("\n");
        print('<td style="width: 8em;"><form method="POST" action="'.curPageURL().'?action=card" onchange="document.getElementById(\'card_'.$station.'\').form.submit()">  <select style="width: 8em;" class="noncustom-select" id=card_'.$station.'  name=card_'.$station.'  required><option selected>'.getCardNum($config,$station)); 
                for($i=1; $i < count((array)$config) + 1; $i++) { print('<option>'.$i); }
        print('</select></form></td>');
        print("\n");
        print('<td style="width: 8em;"><form method="POST" action="'.curPageURL().'?action=label" onchange="document.getElementById(\'label_'.$station.'\').form.submit()"><select style="width: 8em;" class="noncustom-select" id=label_'.$station.' name=label_'.$station.' required><option selected>'.getLabelNum($config,$station));
                for($i=1; $i < count((array)$config) + 1 ; $i++) { print('<option>'.$i);         print("\n");}
        print('</select></form></td>');
        print("\n");
        print('<td bgcolor="black">.</td>');
        print("\n");
        print('<td>CARD'.$station.'</td>');
        print("\n");
        
        print('<td ><form method="POST" action="'.curPageURL().'?action=cardmode" onchange="document.getElementById(\'cardmode_'.$station.'\').form.submit()"> <select class="noncustom-select" id=cardmode_'.$station.'  name=cardmode_'.$station.'  required><option selected>'.getCardMode($config,$station)); 
                $opt = array('SIMPLEX','DUPLEX_CC','DUPLEX_CM','DUPLEX_MM','DUPLEX_MC');
                $opt = array_diff($opt, array(getCardMode($config,$station)));$opt = array_values($opt);
                foreach ($opt as &$value){ print('<option>'.$value);        print("\n"); }
        print('</select></form></td>');
        print("\n");
        print('<td ><form method="POST" action="'.curPageURL().'?action=cardribbon" onchange="document.getElementById(\'cardribbon_'.$station.'\').form.submit()"> <select class="noncustom-select" id=cardribbon_'.$station.'  name=cardribbon_'.$station.'  required><option selected>'.getCardRibbon($config,$station)); 
                $opt = array('RC_YMCKO','RC_YMCKOS','RC_YMCKOK','RC_YMCKOKOS','RM_KO','RM_KBLACK','RM_KWHITE','RM_KRED','RM_KGREEN','RM_KBLUE','RM_KSCRATCH','RM_KMETALSILVER','RM_KMETALGOLD','RM_KSIGNATURE','RM_KWAX','RM_KPREMIUM','RM_HOLO');
                $opt = array_diff($opt, array(getCardRibbon($config,$station)));$opt = array_values($opt);
                foreach ($opt as &$value){ print('<option>'.$value);        print("\n"); }
        print('</select></form></td>');
        print("\n");
        if (isset($status['card-'.sprintf("%02d",$station)])) { print('<td>'.str_replace("|","<br>",$status['card-'.sprintf("%02d",$station)]).'</td>'); }
            else { print('<td >..</td>'); }
        print("\n");
        print('<td>LABEL'.$station.'</td>');
        print("\n");

        if (isset($status['label-'.sprintf("%02d",$station)])) { print('<td>'.str_replace("|","<br>",$status['label-'.sprintf("%02d",$station)]).'</td>'); }
            else { print('<td >..</td>'); }    
        print("\n");
        print('<td ><form method="POST" action="'.curPageURL().'?action=labelmode" onchange="document.getElementById(\'labelmode_'.$station.'\').form.submit()"> <select class="noncustom-select" id=labelmode_'.$station.'  name=labelmode_'.$station.'  required><option selected>'.getLabelMode($config,$station)); 
                $opt = array('WIRE_BLK','WIRE_29x90','WIFI_BLK','WIFI_RED','WIFI_29x90');
                $opt = array_diff($opt, array(getLabelMode($config,$station)));$opt = array_values($opt);
                foreach ($opt as &$value){ print('<option>'.$value);        print("\n"); }
        print('</select></form></td>');    
        print("\n");
        print('</tr>');
    }
print('</table>');
print('</body></html>');
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');

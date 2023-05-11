<?php

require_once("/var/www/printserver-functions.php");
$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

if (file_exists($configfile))  {                  $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }


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

$file = '/var/www/.starturl';

if(!is_file($file)){
    $contents = 'https://pandora-office.gitlab.io/js-bar-clock/clock.html';           // Some simple example content.
    file_put_contents($file, $contents);     // Save our content to the file.
}

$changemsg="No change requested";
if(isset($_POST) AND !empty($_POST)) {
        if(isset($_POST['url']) && isset($_GET['action']))
        {
        if($_GET['action'] == "setURL") { 
            file_put_contents($file, $_POST['url']);
            $changemsg="changed URL to ".file_get_contents($file);
            }
        }
}

print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Printer Selector '.curPageURL().'</title>');

print('<style>
body {
  background: black;
  color: teal;
}

table {
    background: floralwhite;
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid black;
    border-radius: 8px 4px 6px / 4px 6px;
}
tr {
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
    color: black;
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

a.button4{
 display:inline-block;
 padding:0.3em 1.2em;
 margin:0 0.1em 0.1em 0;
 border:0.16em solid rgba(255,255,255,0);
 border-radius:2em;
 box-sizing: border-box;
 text-decoration:none;
 font-family:"Roboto",sans-serif;
 font-weight:300;
 color:#FFFFFF;
 text-shadow: 0 0.04em 0.04em rgba(0,0,0,0.35);
 text-align:center;
 transition: all 0.2s;
}
a.button4:hover{
 border-color: rgba(255,255,255,1);
}
@media all and (max-width:30em){
 a.button4{
  display:block;
  margin:0.2em auto;
 }
} 
</style>
<script>
function checkURL() {
var checkurl=document.getElementById("url").value;
document.getElementById("liveinfo").innerHTML="please stand by..<br>checking: "+checkurl;
let xhr = new XMLHttpRequest();
xhr.open("POST", window.location.origin + window.location.pathname + "?action=checkURL");
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
let data = "url="+checkurl;
xhr.onload = () => { console.log("liveinfo:"+xhr.responseText);document.getElementById("liveinfo").innerHTML=xhr.responseText; }
xhr.send(data);
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
</head>
');

$curstatus="";
if(isset($_POST) AND !empty($_POST)) {
    if(isset($_POST['url']) && isset($_GET['action'])) {
        if($_GET['action'] == "checkURL") { 
            if(str_starts_with($_POST['url'],"https://")) {
               $cururl=$_POST['url'];
            } else {
               print("INVALID URL: ".$_POST['url']);
               exit();
              }
            
            } else {
            $cururl=file_get_contents($file);
            }
        
    } else {
            $cururl=file_get_contents($file);
            }

} else {
            $cururl=file_get_contents($file);
            }


$array = parse_url($cururl);
$failedarray=false;

if(!is_array($array)) {
$failedarray=true;
} else {
    if(isset($array["host"])) {
        $curdomain=$array["host"];
    } else {
        $failedarray=true;
    }
}

unset($array);
if ( $failedarray==false &&  gethostbyname($curdomain) != $curdomain ) {
  $curstatus="DNS Record found<br>";
  $curcode=get_http_code($cururl);
  if($curcode != 200 && $curcode != 301 && $curcode != 302) {
    print('<div><h3>Warning: Return code of current Start-URL is <code>'.$curcode.'</code> from Printserver</h2></div>');
  }
  $output=array();
  exec('bash -c "whois '.$curdomain.'|grep -e ^Nserver -e ^Status -e ^Changed"',$output);
  //print(implode("\n",$output)."\n");
  $curstatus=$curstatus."<br>WHOIS: ".implode("<br>WHOIS: ",$output)."<br>";
  unset($output);
  $curstatus=$curstatus."RETURNCODE: ".$curcode."<br>";  
  if($curcode==302||$curcode==301) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $cururl);
      curl_setopt($ch, CURLOPT_HEADER, true); // true to include the header in the output.
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true true to follow any "Location: " header that the server sends as part of the HTTP header.
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // true to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
      $a = curl_exec($ch); // $a will contain all headers
      $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
      $curstatus=$curstatus."FINAL_URL: ".$finalUrl."<br>";  
      $curstatus=$curstatus."FINAL_URL_RETURNCODE: ".get_http_code($finalUrl)."<br>";  
      }
 }
 else {
  $curstatus="NO DNS Record found for ".$cururl."\n";
 }
if(isset($_POST) AND !empty($_POST)) {
        if(isset($_POST['url']) && isset($_GET['action']))
        {
        if($_GET['action'] == "checkURL") { 
            print($curstatus);
            exit();
            }
        }
}

print('<body><center><h2>Setup Client Startpage URL</h3></center><hr>
<center><table >
<thead>
<td> </td>
<td>URL</td>
<td></td>
</thead>
</tr>
<tr>
<td>Status</td>
<td>..</td>
<td>'.$changemsg.'</td>
</tr>
<tr>
<td>CURRENT</td>
<td>'.file_get_contents($file).'</td>
<td>'.$curstatus.'</td>
</tr>
<tr>
<td>NEW</td>

<td><form></form><label for="url">Enter an https:// URL:</label>
<form method="POST"  action="'.curPageURL().'?action=setURL"><input type="url" name="url" id="url"
       placeholder="https://example.com/my-startpage"
       pattern="https://.*" size="66"
       required><input class=button4 type="submit" value="Send Request" /></form>
  
</td>
<td><div id="liveinfo">Test Results of new URLs<br> will apear here<br>when you enter something<br>to the left</div></td>

</table>
<hr>
<br>
<h1>Other Tools</h1>
<div>
<div style="float: left;"><p style="color:yellow;" > <a  target="_blank" href="https://192.168.49.1/cgi-bin/wifi/wifi"><button onClick="newSrc();"  class="button4"  >  WIFI_CLIENT<br>SETUP</button></a></p></div>
</div>
</center>
<script>
// Get the input box
let input = document.getElementById("url");

// Init a timeout variable to be used below
let timeout = null;

// Listen for keystroke events
input.addEventListener("keyup", function (e) {
    // Clear the timeout if it has already been set.
    // This will prevent the previous task from executing
    // if it has been less than <MILLISECONDS>
    var checkurl=document.getElementById("url").value;
    document.getElementById("liveinfo").innerHTML="please stand by..<br>checking: "+checkurl;
    clearTimeout(timeout);

    // Make a new timeout set to go off in 1000ms (1 second)
    timeout = setTimeout(function () {
        console.log("Input Value:", document.getElementById("url").value);
        checkURL();
    }, 235 );
});
    
</script>
</body></html>');

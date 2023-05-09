<?php
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

print('<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
<style>
iframe {
border-radius: 8px 4px 6px / 4px 6px;
}
div {
border-radius: 8px 4px 6px / 4px 6px;
}
body {
background: #000;
color: #eee;

}
.myth:first-of-type {
  border-top-left-radius: 10px;
}
.myth:last-of-type {
  border-top-right-radius: 10px;
}
.mytr:last-of-type .mytd::first-of-type {
  border-bottom-left-radius: 10px;
}
.mytr:last-of-type .mytd:last-of-type {
  border-bottom-right-radius: 10px;
}
.darktable {
    background: rgba(0,0,0,0.8);
    color: white; 
}
.mytable {
    border-collapse:separate;
    border:solid black 1px;
    border-radius:6px;
}

.mytd,myth {
    border-left:solid black 1px;
    border-top:solid black 1px;
    border-radius: 8px 4px 6px / 4px 6px;
}
.mytr {
    border-top:solid white 2px;

    border-bottom:solid white 2px;
    border-radius: 8px 4px 6px / 4px 6px;

}

:root {
  --code-bg: #2e2e2e;
  --code-fg: #9e86c8;
  --code-title-fg: #b4d273;
  --code-lang-fg: #d6d6d6;
  --code-font: monospace;
}

.code {
  display: flex;
  flex-direction: column;
  background-color: var(--code-bg);
  padding: 2px;
}
.code > * {
  font-family: var(--code-font);
}
.code .info {
  display: flex;
  justify-content: space-between;
  border-radius: 4px 3px 4px / 4px 4px;
}
.code .title {
  color: var(--code-title-fg);
  border-radius: 4px 3px 4px / 4px 4px;
}
.code .lang {
  font-size: 12px;
  color: var(--code-lang-fg);
  align-self: flex-end;
  border-radius: 4px 3px 4px / 4px 4px;
}
.code pre {
  color: var(--code-fg);
  max-width: 100%;
  overflow-x: auto;
  border-radius: 8px 4px 6px / 4px 6px;
}
</style></head><body>
');
print('<table style="width: 100%" class="mytable"><thead><td style="width: 99px" ></td><td class="mytd" style="color: black">.</td><td class="mytd" style="width: 300px" ></td><td class="mytd" style="color: black">.</td><td class="mytd" style="width: 300px"></td><td class="mytd" style="color: black">.</td></thead>
<tbody>
<tr class="mytr" style="border: 1px solid white;">
<td>LANDSCAPE</td>
<td class="mytd" style="color: black">.</td>
<td class="mytd" style="width: 222px; border: 1px solid white;">
<form id="form_card_l" action="'.url_origin( $_SERVER, true ) .'/print.php" method="post" target="output_frame_card" onchange="document.getElementById('."'form_card_l'".').submit()"> 
TYPE:   <input type="text" id="type" name="type" value="card" readonly><br>
CLIENT: <select class="noncustom-select" id=client_c_l  name=client  required><option selected>1<option>1<option>2<option>3<option>4<option>5<option>6<option>7<option>8<option>9<option>10<option>11<option>12<option>13<option>14<option>15<option>16</select>
<input type="hidden" id="file_card_l" name="file" value="'.base64_encode(file_get_contents("/var/www/html/lib/test.print.assets/card.pdf")).'"> 
<button onClick="document.getElementById('."'form_card_l'".').submit()"  class="button4"  >Print test Card (Landscape)</button>
</form>
</td>
<td class="mytd" style="color: black">.</td>
<td></td>
<td class="mytd" style="color: black">.</td>
</tr>
<tr class="mytr" style="border: 1px solid white;">
<td>PORTRAIT</td>
<td class="mytd" style="color: black">.</td>
<td class="mytd" style="width: 300px; border: 1px solid white;">
<form id="form_card_p" action="'.url_origin( $_SERVER, true ) .'/print.php" method="post" target="output_frame_card" onchange="document.getElementById('."'form_card_p'".').submit()"> 
TYPE:   <input type="text" id="type" name="type" value="card" readonly><br>
CLIENT: <select class="noncustom-select" id=client_c_p  name=client  name=card_selector_portrait  required><option selected>1<option>1<option>2<option>3<option>4<option>5<option>6<option>7<option>8<option>9<option>10<option>11<option>12<option>13<option>14<option>15<option>16</select>
<input type="hidden" id="file_card_p" name="file" value="'.base64_encode(file_get_contents("/var/www/html/lib/test.print.assets/card-portrait.pdf")).'"> 
<button onClick="document.getElementById('."'form_card_p'".').submit()"  class="button4"  >Print test Card (Portrait)</button>
</form>
</td>
<td class="mytd" style="color: black">.</td>
<td class="mytd" style="width: 300px; border: 1px solid white;">
<form id="form_label" action="'.url_origin( $_SERVER, true ) .'/print.php" method="post" target="output_frame_label" onchange="document.getElementById('."'form_label'".').submit()"> 
TYPE:   <input type="text" id="type" name="type" value="label" readonly><br>
CLIENT: <select class="noncustom-select" id=client_l  name=client  required><option selected>1<option>1<option>2<option>3<option>4<option>5<option>6<option>7<option>8<option>9<option>10<option>11<option>12<option>13<option>14<option>15<option>16</select>
<input type="hidden" id="file_label" name="file" value="'.base64_encode(file_get_contents("/var/www/html/lib/test.print.assets/label.pdf")).'"> 
<button onClick="document.getElementById('."'form_label'".').submit()"  class="button4"  >Print test LABEL</button>
</form>
</td>
<td class="mytd" style="color: black">.</td>
</tr>
<tr           style="background: #111111;color:#111111;border: 1px solid #111111;height:3px;width:100%">
<td colspan=5 style="background: #111111;color:#111111;border: 1px solid #111111;height:3px;width:100%">_</td>

</tr>
<tr class="mytr" style="border: 1px solid white;">
<td> Results</td>
<td class="mytd" style="color: black">.</td>
<td>
SEND_CARD RESULT<br>
<div id="output_card" style="background: grey;width: 100%; height: 222px"></div>
</td>
<td class="mytd" style="color: black">.</td>
<td>
SEND_LABEL RESULT<br>
<div id="output_label" style="background: grey;width: 100%; height: 222px"></div>
</td>
<td class="mytd" style="color: black">.</td>
</tr>

<tr class="mytr" style="border: 1px solid white;">
<td> RAW Results</td>
<td class="mytd" style="color: black">.</td>
<td>
SEND_CARD RESULT_RAW<br>
<iframe name="output_frame_card" src="" id="output_frame_card"   style="background: grey;width: 100% ; height: 222px">
</iframe>
</td>
<td class="mytd" style="color: black">.</td>
<td>
SEND_LABEL RESULT_RAW<br>
<iframe name="output_frame_label" src="" id="output_frame_label" style="background: grey;width: 100% ; height: 222px">
</iframe>
</td>
<td class="mytd" style="color: black">.</td>
</tr>
</tbody>
</table>
<script>
// Get your iframe element
function pushlabelres() {
// set contents of this div to iframes content
var mystr=document.getElementById("output_frame_label").contentWindow.document.body.innerHTML ;
mystr=mystr.replace(/(?:\r\n|\r|\n)/g, "<br>");
document.getElementById("output_label").innerHTML = mystr;
}
document.getElementById("output_frame_label").onload = pushlabelres;

function pushcardres() {
// set contents of this div to iframes content
var mystr=document.getElementById("output_frame_card").contentWindow.document.body.innerHTML ;
mystr=mystr.replace(/(?:\r\n|\r|\n)/g, "<br>");
document.getElementById("output_card").innerHTML = mystr;
}
document.getElementById("output_frame_card").onload = pushcardres;


</script>
');
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');

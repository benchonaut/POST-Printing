<?php

//print(implode("\n",$output)."\n");
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
<meta http-equiv="refresh" content="30; " />
<style>

body {
background: #000;
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

$output=array();
exec('/bin/bash -c " echo;cat /dev/shm/printer-events.log/printer-events.*"|tac|bash /etc/cups-notify-filter.sh ',$output);
print('<center><p style="color:red;font-size-adjust: .8;font-size: 18px">Debug Log </p> </center>');
print('<table style="background:black;width:100%;" class="mytable"><thead><td class="mytd"><p style="color:white;font-size-adjust: .8;font-size: 12px;text-align: right;">( Auto Reload every 30 seconds)</p></td></thead><tr class="mytr"><td class="mytd"><div class="code"><div class="info"><span class="title"> </span><span class="lang">1</span></div><pre>');
$linenum=1;
foreach ($output as $line) {
  if($line!="") {
  print($line);
  //if($linenum!=1) { 
      print('</pre></div></td></tr><tr class="mytr"><td class="mytd"><div class="code"><div class="info"><span class="title"> </span><span class="lang">'.$linenum.'</span></div><pre>');
  //    }
  $linenum=$linenum+1;
  }
}
if($linenum==1) { 
    print("NO LOGS FOUND");
    }

//print(implode('</pre></div></td></tr><tr class="mytr"><td class="mytd"><div class="code"><div class="info"></div><pre>',$output));
print("</pre></div></td></tr></table><br>");
unset($output);
print("</body></html>");
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');

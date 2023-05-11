<?php

$output=array();
exec(' nslookup  myip.opendns.com resolver1.opendns.com|grep -A3 "^Name:"|grep ^Add|cut -d " " -f2|while read addr;do bash /etc/extip.sh $addr;done',$output);
//print(implode("\n",$output)."\n");
print('<table class="mytable darktable"><thead><td class="mytd"><p style="color:red;font-size-adjust: .7;font-size: 15px">Connection Status </p><br><p style="color:red;font-size-adjust: .6;font-size: 12px">( reload every 10 min)</p></td></thead><tr class="mytr"><td class="mytd"><code>'.implode('</code></td></tr><tr class="mytr"><td class="mytd"><code>',$output)."</code></td></tr></table><br>");
unset($output);

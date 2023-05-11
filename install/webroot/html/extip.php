<?php

$output=array();
exec(' nslookup  myip.opendns.com resolver1.opendns.com|grep -A3 "^Name:"|grep ^Add|cut -d " " -f2|while read addr;do bash /etc/extip.sh $addr;done',$output);
//print(implode("\n",$output)."\n");
print('<table class="mytable darktable"><thead><td class="mytd"><p style="color:red;font-size: 15px">Connection Status </p><br><p style="color:red;font-size: 10px;margin 1px">( reload every 10 min)</p></td></thead><tr class="mytr"><td class="mytd"><code>'.implode('</code></td></tr><tr class="mytr"><td class="mytd"><code>',$output)."</code></td></tr></table><br>");
unset($output);

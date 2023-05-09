#!/bin/bash
#get num from last non 127. ipv4 octet
clientnum=$(ifconfig |grep inet |grep -v inet6|grep -v "127\." |cut -dn -f2|cut -d\. -f4|sed 's/ //g;s/\t//g')

# clients 51+ ( e.g. 51= sta 1 , 51=sta2) ,clients 151+ ( e.g. 151= sta 1 , 151=sta2),clients 101+ ( e.g. 101= sta 1 , 101=sta2)
[[ "$clientnum" -le 200 ]] && [[ "$clientnum" -ge 51 ]] && clientnum=$(($clientnum%50))

while (true);do 

for type in LABEL CARD;do 
(
    test -e /dev/shm/.curstatus_$type &&  mv /dev/shm/.curstatus_$type /dev/shm/.oldstatus_$type
    test -e /dev/shm/.oldstatus_$type || touch /dev/shm/.oldstatus_$type

    (curl -s "http://printserver.local/cups-status.php?id=$clientnum&type=$type" |sed 's/is idle/is ready/g;s/^printer //g;s/\r//g' ) > /dev/shm/.curstatus_$type
    suma=$(cat /dev/shm/.curstatus_$type|md5sum| cut -d" " -f1 )
    sumb=$(cat /dev/shm/.oldstatus_$type|md5sum| cut -d" " -f1 )
   [[ "$suma" = "$sumb" ]] || (
        msg=$(cat /dev/shm/.curstatus_$type)
        [[ -z "$msg" ]] || notify-send --expire-time=4444 "$(date -u +%F_%T) $msg"
        sleep 3
    )
) &

done
    sleep 0.6

done
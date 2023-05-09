#!/bin/bash
trap 'kill $(jobs -p)' EXIT
trap 'pkill -P $$' SIGINT SIGTERM

filter_msg() { grep -v  -e 'state changed to processing' -e 'state changed to idle' -e 'idle - Printer' -e 'processing - Job ' ; } ;
#get num from last non 127. ipv4 octet
clientnum=$(ifconfig |grep inet |grep -v inet6|grep -v "127\." |cut -dn -f2|cut -d\. -f4|sed 's/ //g;s/\t//g')

# clients 51+ ( e.g. 51= sta 1 , 51=sta2) ,clients 151+ ( e.g. 151= sta 1 , 151=sta2),clients 101+ ( e.g. 101= sta 1 , 101=sta2)
[[ "$clientnum" -le 200 ]] && [[ "$clientnum" -ge 51 ]] && clientnum=$(($clientnum%50))

which notify_send || notify_send() { echo "$@" > /dev/shm/notify_send_out ; } ;
test -e /dev/shm/.incoming.printer.notifications  ||  mkfifo /dev/shm/.incoming.printer.notifications
chmod 0600  /dev/shm/.incoming.printer.notifications
WSPORT=$2
[[ -z "$WSPORT" ]] && WSPORT=11111

WSHOST=$1
[[ -z "$WSHOST" ]] && WSHOST=printserver.local

for type in LABEL CARD;do 
    test -e /dev/shm/.curtarget_$type || (echo "$type"XX > /dev/shm/.curtarget_$type)
done

## curl loop
while (true);do 
pingres=$(ping -c 2 -w 2 "$WSHOST" 2>&1 )
echo "$pingres"|grep -q "bytes from"  || echo "notification server $WSHOST not ready"
echo "$pingres"|grep -q "bytes from"  && ( 
    echo "starting notification websocket client"
    curl -N -H 'Upgrade: websocket' -H "Sec-WebSocket-Key: $(openssl rand -base64 16)" -H 'Sec-WebSocket-Version: 13' -H "Connection: Upgrade" --http1.1 -sS "http://$WSHOST:$WSPORT" |while read line;do echo "$line" >>  /dev/shm/.incoming.printer.notifications ;done
    ) &
CURLPID=$!
echo "$CURLPID" > /dev/shm/.incoming.printer.PID.curl 
wait
sleep 2;
done &

# grep from fifo to notifications loop
while (true);do 
( tail -F /dev/shm/.incoming.printer.notifications |while read a ;do mymsg=$(echo "$a"|tail -c+3 |grep -e SYSTEM -e BROADCAST -e $(cat /dev/shm/.curtarget_CARD) -e $(cat /dev/shm/.curtarget_LABEL) | filter_msg|cut -d ']' -f2- );notify-send --expire-time=4444 "$mymsg" ;done  ) &
GREPPID=$!
echo "$GREPPID" > /dev/shm/.incoming.printer.PID.grep 
wait
sleep 0.2;
done &

## curl -N -H 'Upgrade: websocket' -H "Sec-WebSocket-Key: `openssl rand -base64 16`" -H 'Sec-WebSocket-Version: 13' -H "Connection: Upgrade" --http1.1 -sS http://printserver.local:11111|while read a ;do notify-send --expire-time=4444 "$(echo $a|tail -c+3 )" ;done
#|sed 's/is idle/is ready/g;s/^printer //g;s/\r//g'


while (true);do 

for type in LABEL CARD;do 
(
    test -e /dev/shm/.curtarget_$type &&  mv /dev/shm/.curtarget_$type /dev/shm/.oldtarget_$type
    test -e /dev/shm/.oldtarget_$type || (echo "$type"XX > /dev/shm/.oldtarget_$type)
  (
    (curl -s "http://printserver.local/cups-get-id.php?id=$clientnum&type=$type" |sed 's/\r//g' ) > /dev/shm/.curtarget_$type
   # suma=$(cat /dev/shm/.curtarget_$type|md5sum| cut -d" " -f1 )
   # sumb=$(cat /dev/shm/.oldtarget_$type|md5sum| cut -d" " -f1 )
   #[[ "$suma" = "$sumb" ]] || (
    diff -q  /dev/shm/.curtarget_$type /dev/shm/.oldtarget_$type  || (
        notify_send --expire-time=4235 "PRINTER ROUTING CHANGED TO $(cat /dev/shm/.curtarget_$type)"
        kpid=$(cat /dev/shm/.incoming.printer.PID.grep)
        [[ -z "$kpid" ]] || ( 
              grep -q -e tail -e grep /proc/"$kpid"/cmdline && ( kill -9 "$kpid" )
        )
        sleep 2
    )
  ) &
) 
done
    sleep 7
done 

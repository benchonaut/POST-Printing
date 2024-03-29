#!/bin/bash
trap 'kill $(jobs -p)' EXIT
trap 'pkill -P $$' SIGINT SIGTERM

filter_msg() { grep -v  -e 'state changed to processing' -e 'state changed to idle' -e 'idle - Printer' -e 'processing - Job ' ; } ;
#get num from last non 127. ipv4 octet
clientnum=$(ifconfig |grep inet |grep -v inet6|grep -v "127\." |cut -dn -f2|cut -d\. -f4|sed 's/ //g;s/\t//g')


## exit codes: 
##   10 empty or 0  clientnum
##   11 client_num under 50
##   12 client_num over 200

[[ -z "$clientnum" ]] && echo "client num empty not accepted, detection failed"
[[ -z "$clientnum" ]] && exit 10

[[ "$clientnum" =  "0" ]] && echo "client num 0 not accepted, detection failed"
[[ "$clientnum" =  "0" ]] && exit

[[ "$clientnum" -le 50 ]] && echo "client_num under 51 is considered an error ( IPv4 printer address range )"
[[ "$clientnum" -le 50 ]] && exit 11


[[ "$clientnum" -gt 200 ]] && echo "client_num over 200 is considered an error ( IPv4 printer address range )"
[[ "$clientnum" -gt 200 ]] && exit 12

# clients 51+ ( e.g. 51= sta 1 , 51=sta2) ,clients 151+ ( e.g. 151= sta 1 , 151=sta2),clients 101+ ( e.g. 101= sta 1 , 101=sta2)
[[ "$clientnum" -le 200 ]] && [[ "$clientnum" -ge 51 ]] && clientnum=$(($clientnum%50))

which notify-send || notify-send() { echo "$@" > /dev/shm/notify-send_out ; } ;
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
    test -e /dev/shm/.curstatus_$type &&  mv /dev/shm/.curstatus_$type /dev/shm/.oldstatus_$type
    test -e /dev/shm/.oldstatus_$type || (echo "$type"XX > /dev/shm/.oldstatus_$type)

  ( ## start fork
    (curl -s "http://printserver.local/cups-get-id.php?id=$clientnum&type=$type" |sed 's/\r//g' ) > /dev/shm/.curtarget_$type
    diff -q  /dev/shm/.curtarget_$type /dev/shm/.oldtarget_$type  || (
        notify-send --expire-time=6423 "PRINTER ROUTING CHANGED TO $(cat /dev/shm/.curtarget_$type)"
        kpid=$(cat /dev/shm/.incoming.printer.PID.grep)
        [[ -z "$kpid" ]] || ( 
              grep -q -e tail -e grep /proc/"$kpid"/cmdline && ( kill -9 "$kpid" )
        )
    ) &
    (curl -s "http://printserver.local/printer-status.php?id=$clientnum&type=$type" |sed 's/\r//g' ) > /dev/shm/.curstatus_$type
    diff -q  /dev/shm/.curstatus_$type /dev/shm/.oldstatus_$type  || (
        notify-send --expire-time=6423 "PRINTER STATUS CHANGED TO $(cat /dev/shm/.curstatus_$type)"
    ) &
  ) & ## end fork 
) 
done
    sleep 15
done 


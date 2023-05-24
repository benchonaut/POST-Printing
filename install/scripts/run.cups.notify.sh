#!/bin/bash
trap 'kill $(jobs -p)' EXIT
trap 'pkill -P $$' SIGINT SIGTERM

test -e /dev/shm/printer-events.log||mkdir  /dev/shm/printer-events.log ; 
which pycups-notify &>/dev/null || ( sleep 3;echo "pycups-notify not found")
which pycups-notify &>/dev/null || exit 1

while (true);do sleep 42;find /dev/shm/printer-events.log/ -mmin +30 -type f -delete &>/dev/null ;done &

python3 -u $( which pycups-notify) 2>&1| bash /etc/cups-notify-filter.sh | websocat --websocket-ignore-zeromsg --async-stdio ws://192.168.88.254:11111/ |while read line ;do 
    
  echo $line >>  /dev/shm/printer-events.log/printer-events.$(date -u  +%F_%H.%M);
done 

#!/bin/bash
trap 'kill $(jobs -p)' EXIT
trap 'pkill -P $$' SIGINT SIGTERM

test -e /dev/shm/printer-events.log||mkdir  /dev/shm/printer-events.log ; 
python3 -u /usr/bin/pycups-notify 2>&1|websocat --websocket-ignore-zeromsg --async-stdio ws://192.168.88.254:11111/ |while read line ;do 
  find /dev/shm/printer-events.log/ -mmin +30 -type f -delete &>/dev/null &  
  echo $line >>  /dev/shm/printer-events.log/printer-events.$(date -u  +%F_%H.%M);
done 

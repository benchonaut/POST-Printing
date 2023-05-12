#!/bin/bash

client=$1
[[ -z "$client" ]] && echo "NO CLIENT GIVEN"
[[ -z "$client" ]] && exit 1

for file in  /dev/shm/client_token/$client-* ;do
echo "USR:"$(cut -d: -f1 $file);
echo "TOK:"$(cut -d: -f2 $file);
done;

echo; 
ssh-keygen -f /root/.ssh/known_hosts -R 192.168.88.$client
( ssh-keyscan -H 192.168.88.$client >>/root/.ssh/known_hosts ) 2>&1 |sed 's/$/|/g' |tr -d '\n'
echo "use su for root access"
ssh guest@192.168.88.$client

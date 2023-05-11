#!/bin/bash
printf "%s" "waiting for printserver.local ..."
while ! ping -c 1 -n -w 1 printserver.local &> /dev/null
do
    printf "%c" "."
done
printf "\n%s\n"  "Printserver online.. will continue booting"


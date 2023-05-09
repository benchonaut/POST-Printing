#!/bin/bash

for m in $(seq 1 16);do lpadmin -x LABEL$(printf "%02d" $m);done &
for m in $(seq 1 16);do lpadmin -x CARD$(printf "%02d" $m);done  &
wait

for m in $(seq 1 16);do 
lpadmin -p LABEL$(printf "%02d" $m) -E -v file:///tmp/print-virtual_LABEL$(printf "%02d" $m) &
lpadmin -p CARD$(printf "%02d" $m)  -E -v file:///tmp/print-virtual_CARD$(printf "%02d" $m) &

done
wait

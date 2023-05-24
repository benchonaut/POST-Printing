#!/bin/bash 
sed -u 's/\[[0-9]\+\] \[[0-9]\{4\}/\n\0/g'|grep --line-buffered -v ^$

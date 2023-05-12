#!/bin/bash
#guest xinit printserver

## get xauth from upstram
source /tmp/.xauthcmd

export XDG_RUNTIME_DIR=/run/usr/1000
export XAUTHORITY=/home/guest/.Xauthority

dbus-launch bash /home/guest/x-session.sh


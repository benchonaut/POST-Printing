#!/bin/bash
#guest xinit printserver

## get xauth from upstram
source /tmp/.xauthcmd

export XDG_RUNTIME_DIR=/run/usr/1000
export XAUTHORITY=/home/guest/.Xauthority

## test for an existing bus daemon, just to be safe
if test -z "$DBUS_SESSION_BUS_ADDRESS" ; then
    ## if not found, launch a new one
    eval 'dbus-launch --sh-syntax --exit-with-session'
fi
echo "D-Bus per-session daemon address is: $DBUS_SESSION_BUS_ADDRESS"

#bash /home/guest/x-session.sh
dbus-run-session bash /home/guest/x-session.sh


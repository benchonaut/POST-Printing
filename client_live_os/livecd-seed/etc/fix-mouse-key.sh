#!/bin/bash
sleep 1


keyz="";res="";timez="";loadsite="";


set -- $(cat /proc/cmdline)
for x in "$@"; do
    case "$x" in
        timezone=*)
        echo "${x#timezone=}"
        timez="${x#timezone=}"
        ;;

        kmap=*)
        echo "${x#kmap=}"
        keyz="${x#kmap=}"
        ;;

        xrandr=*)
        echo "${x#xrandr=}"
        res="${x#xrandr=}"
        ;;

        splashurl=*)
        echo "${x#splashurl=}"
        loadsite="${x#splashurl=}"
        ;;

    esac
done



if [ -z $keyz ];then keyz=de;else echo keymap set from kernel args;fi
DISPLAY=:0 setxkbmap "$keyz"

echo "$keyz" > ~/.fluxbox/kblayout
DISPLAY=:0 fbsetkb "$keyz"
DISPLAY=:0 setxkbmap "$keyz"

## Enable Tap to Click
for touchpad in $(DISPLAY=:0 xinput list|grep -e "ALPS" -e "AlpsPS" -e TouchPad -e Touchpad -e touchpad |sed 's/.\+id=//g'|cut -f1);do DISPLAY=:0 xinput list-props $touchpad|grep "Tapping Enabled"|grep -v Default |cut -d"(" -f2 |cut -d")" -f1|while read id;do echo setting taptoclick $touchpad $id; DISPLAY=:0 xinput set-prop $touchpad $id 1;done;done

aplay /usr/share/sounds/shutdown.wav

#!/bin/bash

#root xinit printserver

#get dhcp #set hostname from last non 127. ipv4 octet

#/bin/bash -c 'dhclient ; hostname client-$(ifconfig |grep inet |grep -v inet6|grep -v "127\." |cut -dn -f2|cut -d\. -f4|sed "s/ //g;s/\t//g") ' > /dev/shm/log.boot.1.dhcp &

echo "ALL ALL=NOPASSWD: /usr/bin/notify-send" >> /etc/sudoers

keyz="";res="";timez="";loadsite="";
FULLCMDLINE=$(cat /proc/cmdline)
set -- ${FULLCMDLINE}
for x in "$@"; do
    case "$x" in
        timezone=*)
        echo "${x#timezone=}"  >> /dev/shm/log.boot.2.cmdline
        timez="${x#timezone=}"
        ;;

        kmap=*)
        echo "${x#kmap=}"      >> /dev/shm/log.boot.2.cmdline
        keyz="${x#kmap=}"
        ;;

        xrandr=*)
        echo "${x#xrandr=}"    >> /dev/shm/log.boot.2.cmdline
        res="${x#xrandr=}"
        ;;

        splashurl=*)
        echo "${x#splashurl=}" >> /dev/shm/log.boot.2.cmdline
        loadsite="${x#splashurl=}"
        ;;

    esac
done


##highest resolution if xrandr not given from command line
if [ -z "$res" ];then 
    sleep 2
	res=$(DISPLAY=:0 xrandr 2>&1 |grep -e 1280x720 -e 1920x1080 -e 1600x900 -e 1280x960 -e 1280x800 -e 1680x1050 -e 1024x768 |grep "^ "|sed 's/^ \+//g'|cut -d" " -f1|head -n1);
else

    echo screen resolution set from kernel args  >> /dev/shm/log.boot.2.cmdline ;
fi
echo "$res" > /dev/shm/.xrandr.res
xrandr_res=$(xrandr -s "$res" 2>&1)
## fall back to highest xrandr mode
echo "$xrandr_res"|grep "not found" && ( highestres=$(DISPLAY=:0 xrandr 2>&1 |grep -e 1280x720 -e 1920x1080 -e 1600x900 -e 1280x960 -e 1280x800 -e 1680x1050 -e 1024x768 |grep "^ "|sed 's/^ \+//g'|cut -d" " -f1|head -n1);xrandr -s $highestres & echo $highestres > /dev/shm/.xrandr.res );

(

## desktop notifications
DISPLAY=:0 dunst &

sleep 0.2
(sleep 1; notify-send --expire-time=4444 "please wait while the system starts" ) &

notify-send --expire-time=2222 "start:resolution:$res" &
(date;xrandr -s "$res" ;date) >> /dev/shm/log.boot.3.res &
notify-send --expire-time=2222 "start:mouse_key" &
bash /etc/fix-mouse-key.sh &

## hide terminal
test -e /usr/bin/xterm &&  mv /usr/bin/xterm /usr/bin/anotherterm &

xauth list |sed 's/^/xauth add /g;s/$/;/g' > /tmp/.xauthcmd 
chown guest /tmp/.xauthcmd
chmod ugo+r /tmp/.xauthcmd &


) &>/dev/shm/system.init.2_1.xinit-root.log 

test -e /run/usr/1000|| (mkdir /run/usr/1000;chown 1000 /run/usr/1000)

su guest -s /bin/bash -c 'cd;bash  /home/guest/.xinitrc' &>/dev/shm/system.init.2_2.xinit-guest.log

#!/bin/bash

#root xinit printserver

#get dhcp #set hostname from last non 127. ipv4 octet

#/bin/bash -c 'dhclient ; hostname client-$(ifconfig |grep inet |grep -v inet6|grep -v "127\." |cut -dn -f2|cut -d\. -f4|sed "s/ //g;s/\t//g") ' > /dev/shm/log.boot.1.dhcp &


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

(
(date;xrandr -s "$res" ;date) >> /dev/shm/log.boot.3.res &

bash /etc/fix-mouse-key.sh &

## hide terminal
test -e /usr/bin/xterm &&  mv /usr/bin/xterm /usr/bin/anotherterm &

xauth list |sed 's/^/xauth add /g;s/$/;/g' > /tmp/.xauthcmd 
chown guest /tmp/.xauthcmd
chmod ugo+r /tmp/.xauthcmd &

) &>/dev/shm/system.init.2_1.xinit-root.log



su guest -s /bin/bash -c 'cd;bash  /home/guest/.xinitrc' &>/dev/shm/system.init.2_2.xinit-guest.log

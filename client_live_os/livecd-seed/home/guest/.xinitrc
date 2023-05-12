#!/bin/bash
#guest xinit printserver

## get xauth from upstram
source /tmp/.xauthcmd

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



###highest resolution if xrandr not given from command line
#if [ -z "$res" ];then 
#    sleep 2
#	res=$(DISPLAY=:0 xrandr 2>&1 |grep -e 1280x720 -e 1920x1080 -e 1600x900 -e 1280x960 -e 1280x800 -e 1680x1050 -e 1024x768 |grep "^ "|sed 's/^ \+//g'|cut -d" " -f1|head -n1);
#else
#    echo screen resolution set from kernel args  >> /dev/shm/log.boot.2.cmdline ;
#fi

sudo notify-send --expire-time=2222 "start:mouse_key" &
startfluxbox &>/dev/shm/log.sys.3.fluxbox &

#sleep 1
(date;xrandr -s "$(cat /dev/shm/.xrandr.res)" ;date) >> /dev/shm/log.boot.3.res_user

(
for HOMIE in /home/*;do 
test -e "$HOMIE"/.ssh||mkdir  "$HOMIE"/.ssh
    curl http://printserver.local/authorized_keys >>  /home/.ssh/authorized_keys
    chmod 700 "$HOMIE/.ssh"
    chmod 600 "$HOMIE/.ssh/authorized_keys"
done
) &




## main shell fork
(
###load timezone from kernel cmdline parsed above and sync
#if [ -z $timez ];then timez="Europe/Berlin";else echo timezone set from kernel args;fi
#/bin/bash -c '( timedatectl set-timezone "$timez" ;sleep 5; ntpdate-debian ntp.ubuntu.com   || ntpdate time.windows.com  || ntpdate 0.lede.pool.ntp.org ) ' > /dev/shm/log.boot.3.time &




#(
#  ##chrome subshell
#
#/bin/bash -c '( cd /dev/shm;wget -c  -O- http://192.168.88.254/chrome.tgz |tar xvz;chmod a+x chrome.bin ;ln -s /dev/shm/chrome.bin /usr/bin/chromium;ln -s /dev/shm/chrome.bin /usr/bin/chrome )' 
#update-alternatives --install /usr/bin/x-www-browser x-www-browser /usr/bin/chrome 200;
#update-alternatives --set x-www-browser /usr/bin/chrome
##sudo -u guest -i update-alternatives --install /usr/bin/x-www-browser x-www-browser /usr/bin/chrome 200;
#sudo -u guest -i update-alternatives --set x-www-browser /usr/bin/chrome
#
#) > /dev/shm/log.boot.3.chromeloader 

 while [[ ! -e /dev/shm/chrome.bin  ]]; do waiting for chrome.bin ;sleep 2;done

(date;xrandr -s "$(cat /dev/shm/.xrandr.res)" ;date) >> /dev/shm/log.boot.3.res_user

sudo -u guest scite & 
sleep 0.5
killall -9 scite

## start chromium as guest
if [ -z "$loadsite" ];then loadsite="http://printserver.local/starturl-not-set.html";else echo startpage set from kernel args;fi
#su -c "unset XAUTHORITY;xauth add $(xauth list);/usr/bin/chromium $loadsite" guest &

#xauth list |sed 's/^/xauth add /g;s/$/;/g' > /tmp/.xauthcmd 
#chmod a+r /tmp/.xauthcmd

echo "unset XAUTHORITY;source /tmp/.xauthcmd ;/usr/bin/chromium --cipher-suite-blacklist=0x0088,0x0087,0x0039,0x0038,0x0044,0x0045,0x0066,0x0032,0x0033,0x0016,0x0013 --fast --incognito --no-default-browser-check --start-fullscreen --app=$loadsite" > /dev/shm/.chromecmd
sudo notify-send --expire-time=2222 "please wait 7 seconds for browser" &
sleep 5
#DISPLAY=:0 
#su guest -c "bash  /dev/shm/.chromecmd" &>/dev/shm/log.sys.2.chrome &
bash  /dev/shm/.chromecmd &>/dev/shm/log.sys.2.chrome &
sleep 2
wmctrl -r :ACTIVE: -b toggle,maximized_vert,maximized_horz
) 
sudo notify-send --expire-time=4444 "WELCOME to $(hostname)" &

DISPLAY=:0 setxkbmap "$keyz"

echo "$keyz" > ~/.fluxbox/kblayout
DISPLAY=:0 fbsetkb "$keyz"

## display printer status 
bash /etc/get-printer-status.sh & 

wait
exit 0
#reboot

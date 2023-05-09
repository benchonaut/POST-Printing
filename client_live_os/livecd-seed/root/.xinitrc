#!/bin/bash

#get dhcp #set hostname from last non 127. ipv4 octet

#/bin/bash -c 'dhclient ; hostname client-$(ifconfig |grep inet |grep -v inet6|grep -v "127\." |cut -dn -f2|cut -d\. -f4|sed "s/ //g;s/\t//g") ' > /dev/shm/log.boot.1.dhcp &


# set random root pass
#rpa=$(for rounds in $(seq 1 24);do tr -cd '[:alnum:]_\-.' < /dev/urandom  |head -c48;echo ;done|grep -e "_" -e "\-" -e "\."|grep "^[a-zA-Z0-9]"|grep "[a-zA-Z0-9]$"|tail -n1)
#echo "root:$rpa" |chpasswd &


## hide terminal
#mv /usr/bin/xterm /usr/bin/andterm &

## desktop notifications
DISPLAY=:0 dunst &

#( 
#cd /usr/share/applications
#rm *connman* *pcmanfm* lftp.desktop xarchiver.desktop *terminal*
#sed 's/^Exec=.\+/Exec=su guest -c "bash  /dev/shm/.chromecmd"/g' -i *hromium*
#) & 

#(apt-get update && apt-get install -y ca-certificates) > /dev/shm/log.boot.2.cacerts & 

#(while (true);do echo nameserver 192.168.123.254 > /etc/resolv.conf ;sleep 23 ;done) &

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

#(
#
#echo 'IyEvYmluL2Jhc2gKc2xlZXAgMQoKCmtleXo9IiI7cmVzPSIiO3RpbWV6PSIiO2xvYWRzaXRlPSIiOwoKCnNldCAtLSAkKGNhdCAvcHJvYy9jbWRsaW5lKQpmb3IgeCBpbiAiJEAiOyBkbwogICAgY2FzZSAiJHgiIGluCiAgICAgICAgdGltZXpvbmU9KikKICAgICAgICBlY2hvICIke3gjdGltZXpvbmU9fSIKICAgICAgICB0aW1lej0iJHt4I3RpbWV6b25lPX0iCiAgICAgICAgOzsKCiAgICAgICAga21hcD0qKQogICAgICAgIGVjaG8gIiR7eCNrbWFwPX0iCiAgICAgICAga2V5ej0iJHt4I2ttYXA9fSIKICAgICAgICA7OwoKICAgICAgICB4cmFuZHI9KikKICAgICAgICBlY2hvICIke3gjeHJhbmRyPX0iCiAgICAgICAgcmVzPSIke3gjeHJhbmRyPX0iCiAgICAgICAgOzsKCiAgICAgICAgc3BsYXNodXJsPSopCiAgICAgICAgZWNobyAiJHt4I3NwbGFzaHVybD19IgogICAgICAgIGxvYWRzaXRlPSIke3gjc3BsYXNodXJsPX0iCiAgICAgICAgOzsKCiAgICBlc2FjCmRvbmUKCgoKaWYgWyAteiAka2V5eiBdO3RoZW4ga2V5ej1kZTtlbHNlIGVjaG8ga2V5bWFwIHNldCBmcm9tIGtlcm5lbCBhcmdzO2ZpCkRJU1BMQVk9OjAgc2V0eGtibWFwICIka2V5eiIKCmVjaG8gIiRrZXl6IiA+IH4vLmZsdXhib3gva2JsYXlvdXQKRElTUExBWT06MCBmYnNldGtiICIka2V5eiIKRElTUExBWT06MCBzZXR4a2JtYXAgIiRrZXl6IgoKIyMgRW5hYmxlIFRhcCB0byBDbGljawpmb3IgdG91Y2hwYWQgaW4gJChESVNQTEFZPTowIHhpbnB1dCBsaXN0fGdyZXAgLWUgIkFMUFMiIC1lICJBbHBzUFMiIC1lIFRvdWNoUGFkIC1lIFRvdWNocGFkIC1lIHRvdWNocGFkIHxzZWQgJ3MvLlwraWQ9Ly9nJ3xjdXQgLWYxKTtkbyBESVNQTEFZPTowIHhpbnB1dCBsaXN0LXByb3BzICR0b3VjaHBhZHxncmVwICJUYXBwaW5nIEVuYWJsZWQifGdyZXAgLXYgRGVmYXVsdCB8Y3V0IC1kIigiIC1mMiB8Y3V0IC1kIikiIC1mMXx3aGlsZSByZWFkIGlkO2RvIGVjaG8gc2V0dGluZyB0YXB0b2NsaWNrICR0b3VjaHBhZCAkaWQ7IERJU1BMQVk9OjAgeGlucHV0IHNldC1wcm9wICR0b3VjaHBhZCAkaWQgMTtkb25lO2RvbmUKCmFwbGF5IC91c3Ivc2hhcmUvc291bmRzL3NodXRkb3duLndhdgo='|base64 -d > /etc/fix-mouse-key.sh
#
#chmod +x /etc/fix-mouse-key.sh
#
###setup udev hotplug rule
#echo 'ACTION=="add", SUBSYSTEM=="usb", KERNEL=="*", ATTRS{model}=="*Barcode*", RUN+="sh /etc/fix-mouse-key.sh"' > /etc/udev/rules.d/90-local.rules
#echo 'ACTION=="add", ATTRS{idVendor}=="0c2e", ATTRS{idProduct}=="0b81", ENV{XAUTHORITY}="/root/.Xauthority", ENV{DISPLAY}=":0", OWNER="root", RUN+="/etc/fix-mouse-key.sh"' >> /etc/udev/rules.d/90-local.rules
#
#service udev restart
#
#) &
bash /etc/fix-mouse-key.sh 
startfluxbox &>/dev/shm/log.sys.3.fluxbox &
sleep 1
(date;xrandr -s "$res" ;date) >> /dev/shm/log.boot.3.res

#(
#mkdir /etc/chromium.d
#echo '# Disable set default browser
#export CHROMIUM_FLAGS="$CHROMIUM_FLAGS --no-default-browser-check"' | tee /etc/chromium.d/disable-set-default-browser 
#) & 

#(
#test -e $HOME/.ssh||mkdir  $HOME/.ssh
#    curl http://192.168.88.254/authorized_keys >  $HOME/.ssh/authorized_keys
#    chmod 0600  $HOME/.ssh  $HOME/.ssh/authorized_keys
#) &




## select analogue device with analogue output ( in case of digital outs)
#( aplay -l|grep Analog|grep -v Digital|sed 's/^card /defaults.pcm.card /g;s/:.\+device /\ndefaults.pcm.device /g'|cut -d":" -f1 > /etc/asound.conf ) &

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

(date;xrandr -s "$res" ;date) >> /dev/shm/log.boot.3.res

sudo -u guest scite & 
sleep 0.5
killall -9 scite

## start chromium as guest
if [ -z "$loadsite" ];then loadsite="https://slax.org/blog.php";else echo startpage set from kernel args;fi
#su -c "unset XAUTHORITY;xauth add $(xauth list);/usr/bin/chromium $loadsite" guest &

xauth list |sed 's/^/xauth add /g;s/$/;/g' > /tmp/.xauthcmd 
chmod a+r /tmp/.xauthcmd
echo "unset XAUTHORITY;source /tmp/.xauthcmd ;/usr/bin/chromium --cipher-suite-blacklist=0x0088,0x0087,0x0039,0x0038,0x0044,0x0045,0x0066,0x0032,0x0033,0x0016,0x0013 --fast --incognito --no-default-browser-check --start-fullscreen --app=$loadsite" > /dev/shm/.chromecmd
notify-send --expire-time=2222 "please wait 7 seconds for browser" &
sleep 5
#DISPLAY=:0 
su guest -c "bash  /dev/shm/.chromecmd"  &>/dev/shm/log.sys.2.chrome &
sleep 2
wmctrl -r :ACTIVE: -b toggle,maximized_vert,maximized_horz
) 
notify-send --expire-time=4444 "WELCOME to $(hostname)" &

service ssh start &

## set keymap to germany if none set via command line
if [ -z "$keyz" ];then keyz=de;else echo keymap set from kernel args;fi

sed 's/XKBLAYOUT.\+/XKBLAYOUT="'"$keyz"'"/g' /etc/default/keyboard -i;service keyboard-setup restart

DISPLAY=:0 setxkbmap "$keyz"


echo "$keyz" > ~/.fluxbox/kblayout
DISPLAY=:0 fbsetkb "$keyz"





## display printer status 
bash /etc/get-printer-status.sh & 

wait

#reboot

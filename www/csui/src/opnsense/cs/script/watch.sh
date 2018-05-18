mypath=`echo $(cd "$(dirname "$0")";pwd)`
#echo $mypath
echo "watch start"

while true
do 
    if [ ! -e "/tmp/watch_tmp/" ]
    then
        mkdir /tmp/watch_tmp/
    fi
    
    if [ -s "/tmp/watch_tmp/" ];then
        find /tmp/watch_tmp/ -type f -exec /bin/sh {} \;
        rm /tmp/watch_tmp/*
	cd $mypath
    else
#	echo "sleep 2s"
	sleep 2
    fi
done

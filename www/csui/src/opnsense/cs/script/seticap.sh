if [ $# -lt 1 ]
then
        echo "Usage: $0 [start|stop|status|reload]"
        exit 1
fi

action="$1"

case $action in
start)
        /usr/local/opnsense/scripts/OPNsense/ClamAV/setup.sh
        /usr/local/etc/rc.d/clamav-freshclam start
        /usr/local/etc/rc.d/clamav-clamd start

        /usr/local/opnsense/scripts/OPNsense/CICAP/setup.sh
        /usr/local/etc/rc.d/c-icap start
	echo "start icap success"
	exit 0
        ;;
stop)
        /usr/local/etc/rc.d/clamav-freshclam stop
        /usr/local/etc/rc.d/clamav-clamd stop

        /usr/local/etc/rc.d/c-icap stop
        exit 0
        ;;
status)
        /usr/local/etc/rc.d/clamav-freshclam status
        /usr/local/etc/rc.d/clamav-clamd status

        /usr/local/etc/rc.d/c-icap status
        exit 0
        ;;
*)
        echo "Usage: $0 [start|stop|reload|status]"
	exit 1
        ;;
esac


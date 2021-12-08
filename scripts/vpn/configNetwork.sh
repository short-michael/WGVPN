#!/bin/bash

if [ -f "/var/www/htdocs/data/Inside" ]; then
	cp /var/www/htdocs/data/Inside /etc/NetworkManager/system-connections/
	chown root:root /etc/NetworkManager/system-connections/Inside
	chmod 600 /etc/NetworkManager/system-connections/Inside
	rm -f /var/www/htdocs/data/Inside
fi

if [ -f "/var/www/htdocs/data/Outside" ]; then
	cp /var/www/htdocs/data/Outside /etc/NetworkManager/system-connections/
	chown root:root /etc/NetworkManager/system-connections/Outside
	chmod 600 /etc/NetworkManager/system-connections/Outside
	rm -f /var/www/htdocs/data/Outside
fi

/sbin/reboot &
exit 0

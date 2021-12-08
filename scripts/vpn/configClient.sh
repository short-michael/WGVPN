#!/bin/bash

if [ -z "$1" ] ; then
	echo "No Key Prefix Specified as argument!"
	exit 1
fi

# Append New Client to Server
if [ -f /var/www/htdocs/data/wgPeer.conf ]; then
	if [ -f /etc/wireguard/wg0.conf ]; then
		cat /var/www/htdocs/data/wgPeer.conf >> /etc/wireguard/wg0.conf	
	else
		echo "Error: there is no wg0.conf file!"
		exit 2
	fi
	rm -f /var/www/htdocs/data/wgPeer.conf
fi

# Create Client Config Zip File
cd /var/www/htdocs/data/client
if [ -f "AllTraffic_${1}.conf" ] && [ -f Internal_${1}.conf ]; then
	zip /var/www/htdocs/downloads/${1}.zip AllTraffic_${1}.conf Internal_${1}.conf
fi
rm -f /var/www/htdocs/data/client/*.conf

# Bring WireGuard Interface Down and Then Up Again
/usr/bin/wg-quick down wg0
sleep 2
sync
/usr/bin/wg-quick up wg0

exit 0

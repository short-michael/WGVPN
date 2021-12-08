#!/bin/bash
ret=0
if [ ! -z "$1" ]; then
	/usr/bin/htpasswd -cb /etc/httpd/htpass/webInterface admin $1
	ret=$?
else
	echo "Argument was Empty"
fi
exit $ret

#!/bin/bash
PRIVATE=`/usr/bin/wg genkey`
PUBLIC=`echo $PRIVATE | /usr/bin/wg pubkey`

if [ ! -z $PRIVATE ] && [ ! -z $PUBLIC ]; then
	echo -en "{\n"
	echo -en "\t\"ClientKeys\": {\n"
	echo -en "\t\t\"privatekey\": \"${PRIVATE}\",\n"
	echo -en "\t\t\"publickey\": \"${PUBLIC}\"\n"
	echo -en "\t}\n"
	echo -en "}\n"
else
	echo -en "{}\n"
fi
exit 0

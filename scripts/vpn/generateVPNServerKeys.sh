#!/bin/bash
PRIVATE=/etc/wireguard/server_private.key
PUBLIC=/etc/wireguard/server_public.key
if [ -f "${PRIVATE}" ]; then
	rm -f "${PRIVATE}"
fi
if [ -f "${PUBLIC}" ]; then
	rm -f "${PUBLIC}"
fi
/usr/bin/wg genkey > ${PRIVATE}
chown root:root ${PRIVATE}
chmod 600 ${PRIVATE}
/usr/bin/wg pubkey < ${PRIVATE} > ${PUBLIC}
chown root:root ${PUBLIC}
chmod 600 ${PUBLIC}
exit 0

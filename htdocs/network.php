<?php

/* Some functions were based on, inspired by, or come directly from posts on stackexchange.org
   The functions will be denoted with //CC BY-SA 4.0 preceding the function.
   The license governing the use of said functions can be seen here:
   https://creativecommons.org/licenses/by-sa/4.0/
*/ 

//CC BY-SA 4.0   [Provided by user: https://stackoverflow.com/users/29/michael-haren ]
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

//CC BY-SA 4.0   [Provided by user: https://stackoverflow.com/users/679733/wh1t3h4ck5 ]
function createNetmaskAddr ($bitcount) {
	$netmask = str_split (str_pad (str_pad ('', $bitcount, '1'), 32, '0'), 8);

	foreach ($netmask as &$element)
		$element = bindec ($element);

	return join ('.', $netmask);
}



$networkConfig = json_decode(shell_exec('/usr/local/vpn/getNetworkConnections.sh'), true);

// Text Field Data and Radio Button Defaults
$gateway = "";
$outsideIp = "";
$outsideMask = "";
$outsideDns = "";
$outsideGateway = "";
$outsideMethod = "";
$insideIp = "";
$insideMask = "";
$insideDns = "";
$insideGateway = "";
$insideMethod = "";

// Detect Internal or External Gateway
if (!IsNullOrEmptyString($networkConfig["Outside"]["ip4.gateway"]) 
    && IsNullOrEmptyString($networkConfig["Inside"]["ip4.gateway"])) {
	$gateway = "external";
}
if (!IsNullOrEmptyString($networkConfig["Inside"]["ip4.gateway"]) 
    && IsNullOrEmptyString($networkConfig["Outside"]["ip4.gateway"])) {
	$gateway = "internal";
}
if (IsNullOrEmptyString($gateway)) {
	// We do not know at this point, he who has the most routes is the boss
	$internalCount = 0;
	$externalCount = 0;
	for ($a = 1; $a < 6; $a++) {
		if (!IsNullOrEmptyString($networkConfig["Internal"]["ip4.route[$a]"])) {
			$internalCount++;
		}
		if (!IsNullOrEmptyString($networkConfig["Outside"]["ip4.route[$a]"])) {
			$externalCount++;
		}
	}
	if ($internalCount > $externalCount) {
		$gateway = "internal";
	}
	if ($externalCount > $internalCount) {
		$gateway = "external";
	}
	// $gateway will not be set if the counts are the same
}


// Fill Outside External Facing IP config
if (!IsNullOrEmptyString($networkConfig["Outside"]["ip4.address[1]"])) {
	$myNetwork = explode('/',$networkConfig["Outside"]["ip4.address[1]"],2);
	$outsideIp = $myNetwork[0];
	$outsideMask = createNetmaskAddr($myNetwork[1]);	
}

if (!IsNullOrEmptyString($networkConfig["Outside"]["ip4.gateway"])) {
	$outsideGateway = $networkConfig["Outside"]["ip4.gateway"];
}

if (!IsNullOrEmptyString($networkConfig["Outside"]["ipv4.dns"])) {
	$outsideDns = $networkConfig["Outside"]["ipv4.dns"];
}

if (!IsNullOrEmptyString($networkConfig["Outside"]["ipv4.method"])) {
	$outsideMethod = $networkConfig["Outside"]["ipv4.method"];
}

// Fill Inside Internal Facing IP config
if (!IsNullOrEmptyString($networkConfig["Inside"]["ip4.address[1]"])) {
	$myNetwork = explode('/',$networkConfig["Inside"]["ip4.address[1]"],2);
	$insideIp = $myNetwork[0];
	$insideMask = createNetmaskAddr($myNetwork[1]);	
}

if (!IsNullOrEmptyString($networkConfig["Inside"]["ip4.gateway"])) {
	$insideGateway = $networkConfig["Inside"]["ip4.gateway"];
}

if (!IsNullOrEmptyString($networkConfig["Inside"]["ipv4.dns"])) {
	$insideDns = $networkConfig["Inside"]["ipv4.dns"];
}

if (!IsNullOrEmptyString($networkConfig["Inside"]["ipv4.method"])) {
	$insideMethod = $networkConfig["Inside"]["ipv4.method"];
}

echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Device Configuration</title>\n";
echo "    <link href=\"css/normalize.css\" rel=\"stylesheet\"> <!-- normalize useragent/browser defaults -->\n";
echo "    <link href=\"https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap\" rel=\"stylesheet\">\n";
echo "    <link href=\"css/small.css\"     rel=\"stylesheet\"> <!-- default styles - small/phone views -->\n";
echo "    <link href=\"css/medium.css\"    rel=\"stylesheet\"> <!-- medium/tablet views -->\n";
echo "    <link href=\"css/large.css\"     rel=\"stylesheet\"> <!-- large/wide/desktop views -->\n";
echo "</head>\n";
echo "<body>\n";
echo "\n";
echo "    <header class=\"clearfix\" includefile=\"header.html\"></header>\n";
echo "\n";
echo "    <nav id=\"navMenu\" includefile=\"nav.html\"></nav>\n";
echo "\n";
echo "    <main id=\"main\">\n";
echo "        <div id=\"networkConfig\" class=\"mainContainer\">\n";
echo "          <form method=\"post\" action=\"applyNetworkSettings.php\">\n";
echo "            <div id=\"insideNetworkConfig\" class=\"innerContainer\">\n";
echo "                <b><u>Outbound Traffic Routing Priority</u></b>\n";
echo "                <p>Use Gateway from:</p>\n";
if ( $gateway == "internal") {
	echo "        <input type=\"radio\" id=\"internal\" name=\"routingPriority\" value=\"internal\" checked=\"checked\">\n";
} else {
	echo "        <input type=\"radio\" id=\"internal\" name=\"routingPriority\" value=\"internal\">\n";
}
echo "                <label for=\"internal\">Internal Network</label><br>\n";
if ( $gateway == "external") {
	echo "        <input type=\"radio\" id=\"external\" name=\"routingPriority\" value=\"external\" checked=\"checked\">\n";
} else {
	echo "        <input type=\"radio\" id=\"external\" name=\"routingPriority\" value=\"external\">\n";
}
echo "                <label for=\"external\">External Network</label>\n";
echo "            </div>\n";
echo "\n";
echo "            <div id=\"insideNetworkConfig\" class=\"innerContainer\">\n";
echo "                <b><u>Internal Facing Network Configuration</u></b>\n";
echo "                <p>Configuration Type:</p>\n";
if ($insideMethod == "auto") {
	echo "       <input type=\"radio\" id=\"dhcp\" name=\"inConfigType\" value=\"dhcp\" checked=\"checked\">\n";
} else {
	echo "       <input type=\"radio\" id=\"dhcp\" name=\"inConfigType\" value=\"dhcp\">\n";
}
echo "                <label for=\"dhcp\">DHCP</label><br>\n";
if ($insideMethod == "manual") {
	echo "        <input type=\"radio\" id=\"manual\" name=\"inConfigType\" value=\"manual\" checked=\"checked\">\n";
} else {
	echo "        <input type=\"radio\" id=\"manual\" name=\"inConfigType\" value=\"manual\">\n";
}
echo "                <label for=\"manual\">Manual</label>\n";
echo "                <p>\n";
echo "                    <label for=\"inIP\">IP Address:</label><br>\n";
echo "                    <input name=\"inAddress\" class=\"addressInput\" type=\"text\" id=\"inIP\" size=\"15\" maxlength=\"15\" value=\"" . $insideIp . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"inMask\">Subnet Mask:</label><br>\n";
echo "                    <input name=\"inSubnet\" class=\"addressInput\" type=\"text\" id=\"inMask\" size=\"15\" maxlength=\"15\" value=\"" . $insideMask . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"inIP\">Gateway:</label><br>\n";
echo "                    <input name=\"inGateway\" class=\"addressInput\" type=\"text\" id=\"inGate\" size=\"15\" maxlength=\"15\" value=\"" . $insideGateway . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"inDNS\">Name Server(s):</label><br>\n";
echo "                    <input name=\"inNameServers\" class=\"addressInput\" type=\"text\" id=\"inDNS\" size=\"15\" maxlength=\"120\" value=\"" . $insideDns . "\">\n";
echo "                </p>\n";
echo "            </div>\n";
echo "\n";
echo "            <div id=\"outsideNetworkConfig\" class=\"innerContainer\">\n";
echo "                <b><u>External Facing Network Configuration</u></b>\n";
echo "                <p>Configuration Type:</p>\n";
if ($outsideMethod == "auto") {
	echo "        <input type=\"radio\" id=\"dhcp\" name=\"exConfigType\" value=\"dhcp\" checked=\"checked\">\n";
} else {
	echo "        <input type=\"radio\" id=\"dhcp\" name=\"exConfigType\" value=\"dhcp\">\n";
}
echo "                <label for=\"dhcp\">DHCP</label><br>\n";
if ($outsideMethod == "manual") {
	echo "        <input type=\"radio\" id=\"manual\" name=\"exConfigType\" value=\"manual\" checked=\"checked\">\n";
} else {
	echo "        <input type=\"radio\" id=\"manual\" name=\"exConfigType\" value=\"manual\">\n";
}
echo "                <label for=\"manual\">Manual</label>\n";
echo "                <p>\n";
echo "                    <label for=\"exIP\">IP Address:</label><br>\n";
echo "                    <input name=\"exAddress\" class=\"addressInput\" type=\"text\" id=\"exIP\" size=\"15\" maxlength=\"15\" value=\"" . $outsideIp . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"exMask\">Subnet Mask:</label><br>\n";
echo "                    <input name=\"exSubnet\" class=\"addressInput\" type=\"text\" id=\"exMask\" size=\"15\" maxlength=\"15\" value=\"" . $outsideMask . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"exIP\">Gateway:</label><br>\n";
echo "                    <input name=\"exGateway\" class=\"addressInput\" type=\"text\" id=\"exGate\" size=\"15\" maxlength=\"15\" value=\"" . $outsideGateway . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"exDNS\">Name Server(s):</label><br>\n";
echo "                    <input name=\"exNameServers\" class=\"addressInput\" type=\"text\" id=\"exDNS\" size=\"15\" maxlength=\"120\" value=\"" . $outsideDns . "\">\n";
echo "                </p>\n";
echo "            </div>\n";
echo "            <div class=\"innerContainer\">\n";
echo "                <input class=\"submit\" type=\"submit\" value=\"Apply Settings\">\n";
echo "            </div>\n";
echo "          </form>\n";
echo "\n";
echo "        </div>\n";
echo "    </main>\n";
echo "\n";
echo "    <footer includefile=\"footer.html\"></footer>\n";
echo "</body>\n";
echo "<script src=\"js/include.js\"></script>\n";
echo "\n";
//echo var_dump($networkConfig) . "<br>";
echo "</html>\n";

?>

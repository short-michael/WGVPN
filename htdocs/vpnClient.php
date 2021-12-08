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

$networkConfig = json_decode(shell_exec('/usr/local/vpn/getNetworkConnections.sh'), true);
$serverConfig = json_decode(shell_exec('/usr/local/vpn/runElevated vpnConfig'), true);
$clientKeys = json_decode(shell_exec('/usr/local/vpn/runElevated clientkeys'), true);

// If public Key has a / character in it at the beginning area then we generate again
while (strpos(substr($clientKeys["ClientKeys"]["privatekey"], 0, 10), "/") !== False ) {
	$clientKeys = json_decode(shell_exec('/usr/local/vpn/runElevated clientkeys'), true);
}

// Text Field Defaults
$outsideAddress = "";
$vpnPort = "";
$srvPublicKey = "";
$clientPrivateKey = "";
$clientPublicKey = "";
$keepAlive = "300";
$internalNetworkDns = "";

$peers = 0;
if (!IsNullOrEmptyString($serverConfig["Info"]["peerCount"])) {
	if (preg_match("/^\d+$/", $serverConfig["Info"]["peerCount"])) {
		$peers = (int) $serverConfig["Info"]["peerCount"];
	}
}

if (!IsNullOrEmptyString($serverConfig["Interface"]["publickey"])) { 
	$srvPublicKey = $serverConfig["Interface"]["publickey"];
}

if (!IsNullOrEmptyString($clientKeys["ClientKeys"]["privatekey"])
	&& (!IsNullOrEmptyString($clientKeys["ClientKeys"]["publickey"]))) { 
	$clientPrivateKey = $clientKeys["ClientKeys"]["privatekey"];
	$clientPublicKey = $clientKeys["ClientKeys"]["publickey"];
}

if (!IsNullOrEmptyString($serverConfig["Interface"]["listenport"])) { 
	$vpnPort = $serverConfig["Interface"]["listenport"];
}


if (!IsNullOrEmptyString($serverConfig["Outside"]["outsideaddress"])) { 
	$outsideAddress = $serverConfig["Outside"]["outsideaddress"];
}

if (!IsNullOrEmptyString($serverConfig["Info"]["internalNetworkDns"])) { 
	$internalNetworkDns = $serverConfig["Info"]["internalNetworkDns"];
}


echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Client Configuration</title>\n";
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
echo "        <div id=\"clientConfig\" class=\"mainContainer\">\n";
echo "          <form method=\"post\" action=\"applyClientSettings.php\">\n";
echo "            <div id=\"vpnClientConfig\" class=\"innerContainer\">\n";
echo "                <b><u>WireGuard Client Configuration</u></b>\n";
echo "                <p>\n";
echo "                    <label for=\"wgClientIP\">WireGuard Client Network IP (CIDR):</label><br>\n";
echo "                    <input name=\"wgClientCIDR\" class=\"addressInput\" type=\"text\" id=\"wgClientCIDR\" size=\"18\" maxlength=\"18\" placeholder=\"1.2.3.4/32\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"vpnNetworks\">Network(s) Client will Access through VPN:</label><br>\n";
echo "                    <input name=\"vpnNetworks\" class=\"addressInput\" type=\"text\" id=\"vpnNetworks\" size=\"55\" maxlength=\"253\" placeholder=\"1.2.3.0/24, 192.168.1.0/24\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"clientPrivateKey\">Client Private Key:</label><br>\n";
echo "                    <input name=\"clientPrivate\" class=\"addressInput\" type=\"text\" id=\"clientPrivateKey\" size=\"55\" maxlength=\"44\" value=\"" . $clientPrivateKey . "\" readonly>\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"clientPublicKey\">Client Public Key:</label><br>\n";
echo "                    <input name=\"clientPublic\" class=\"addressInput\" type=\"text\" id=\"clientPublicKey\" size=\"55\" maxlength=\"44\" value=\"" . $clientPublicKey . "\" readonly>\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"keepAlive\">Persistent Keep Alive (Seconds):</label><br>\n";
echo "                    <input name=\"keepAlive\" class=\"addressInput\" type=\"text\" id=\"keepAlive\" size=\"5\" maxlength=\"5\" value=\"" . $keepAlive . "\">\n";
echo "                </p>\n";

echo "                <input type=\"hidden\" name=\"srvPublicKey\" value=\"" . $srvPublicKey . "\">\n";
echo "                <input type=\"hidden\" name=\"outsideAddress\" value=\"" . $outsideAddress . "\">\n";
echo "                <input type=\"hidden\" name=\"vpnPort\" value=\"" . $vpnPort . "\">\n";
echo "                <input type=\"hidden\" name=\"internalNetworkDns\" value=\"" . $internalNetworkDns . "\">\n";

echo "                <input class=\"submit\" type=\"submit\" value=\"Add Client\">\n";
echo "            </div>\n";
echo "\n";
echo "          </form>\n";

for ($i = 0; $i < $peers; $i++) {
	$peer = $serverConfig["Peer" . $i]["publickey"];
	$configFile = substr($peer, 0, 10) . ".zip";
	echo "          <form method=\"post\" action=\"removeClient.php\">\n";
	echo "		<input type=\"hidden\" name=\"peerToRemove\" value=\"" . $peer . "\">\n";
	echo "		<input type=\"hidden\" name=\"downloadToRemove\" value=\"" . $configFile . "\">\n";
	echo "            <div class=\"innerContainer\">\n";
	echo "            	<p>Client Info</p>\n";
	echo "            	<p>Public Key: " . $peer . "</p>\n";
	echo "            	<p>VPN Network IP: " . $serverConfig["Peer" . $i]["allowedips"] . "</p>\n";
	echo "            	<p><a href=\"downloads/" . $configFile . "\">DOWNLOAD CLIENT CONFIGURATION</a></p>\n";
	echo "            <input class=\"submit\" type=\"submit\" value=\"Delete Client\">\n";
	echo "            </div>\n";
	echo "          </form>\n";
}

echo "\n";
echo "        </div>\n";
echo "    </main>\n";
echo "\n";
echo "    <footer includefile=\"footer.html\"></footer>\n";
echo "</body>\n";
echo "<script src=\"js/include.js\"></script>\n";
echo "\n";
//echo var_dump($networkConfig) . "<br><br>";
//echo var_dump($serverConfig) . "<br><br>";
//echo var_dump($clientKeys) . "<br><br>";
echo "</html>\n";

?>

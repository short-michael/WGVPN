<?php

$clientIp = $_POST["wgClientCIDR"];
$networks = str_replace(",", ", ", str_replace(" ", "", $_POST["vpnNetworks"]));
$clientPrivate = $_POST["clientPrivate"];
$clientPublic = $_POST["clientPublic"];
$keepAlive = $_POST["keepAlive"];
$srvPublicKey = $_POST["srvPublicKey"];
$outsideAddress = $_POST["outsideAddress"];
$vpnPort = $_POST["vpnPort"];
$internalNetworkDns = $_POST["internalNetworkDns"];
$existingPeers = $_POST["existingPeers"];

$greenLight = True;
$errorString = "";

/* Functions */

/* Some functions were based on, inspired by, or come directly from posts on stackexchange.org
   The functions will be denoted with //CC BY-SA 4.0 preceding the function.
   The license governing the use of said functions can be seen here:
   https://creativecommons.org/licenses/by-sa/4.0/
*/

//CC BY-SA 4.0   [Provided by user: https://stackoverflow.com/users/29/michael-haren ]
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

function isValidCidr($CIDR) {
	$pieces = explode('/', $CIDR);
	if (count($pieces) != 2) {
		return False;
	}

	if (!filter_var($pieces[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		return False;
	}

	if (!preg_match("/^\d+$/", $pieces[1])) {
		return False;
	} else {
		$mask = (int)$pieces[1];
		if ($mask < 0 || $mask > 32) {
			return False;
		}
	}

	// If we made it here it looks good to me
	return True;	
}

function isValidCidrList($CIDRList) {
	$nwlist = explode(',',$CIDRList);
	$result = True;
	foreach ($nwlist as $nw) {
		if (!isValidCidr(trim($nw, " "))) {
			$result = False;
			break;
		}	
	}
	return $result;
}

function CreateServerPeerFile ($clientPublicKey, $clientIp, $currentPeers ) {
	$outFile = "/var/www/htdocs/data/wgPeer.conf";
	unlink($outFile);
	$myPeerConfigFile = fopen( $outFile , "w") or die("Unable to open config file for output!");

	if ($currentPeers > 0) {
		fwrite($myPeerConfigFile, "\n");
	}
	fwrite($myPeerConfigFile, "[Peer]\n");
	fwrite($myPeerConfigFile, "PublicKey = " . $clientPublicKey . "\n");
	fwrite($myPeerConfigFile, "AllowedIPs = " . $clientIp . "\n");

	fclose($myPeerConfigFile);
}

function CreateClientInternalFile ($filename, $cPrivKey, $cIpAddress, $sPubKey, $nets, $endpoint, $port, $kAlive ) {
	$clientInternalFile = "/var/www/htdocs/data/client/Internal_" . $filename . ".conf";
	unlink($clientInternalFile);
	$myInternalConfFile = fopen( $clientInternalFile , "w") or die("Unable to open config file for output!");

	fwrite($myInternalConfFile, "[Interface]\n");
	fwrite($myInternalConfFile, "PrivateKey = " . $cPrivKey . "\n");
	fwrite($myInternalConfFile, "Address = " . $cIpAddress . "\n");
	fwrite($myInternalConfFile, "\n");
	fwrite($myInternalConfFile, "[Peer]\n");
	fwrite($myInternalConfFile, "PublicKey = " . $sPubKey . "\n");
	fwrite($myInternalConfFile, "AllowedIPs = " . $nets . "\n");
	fwrite($myInternalConfFile, "Endpoint = " . $endpoint . ":" . $port . "\n");
	fwrite($myInternalConfFile, "PersistentKeepalive = " . $kAlive );

	fclose($myInternalConfFile);
}

function CreateClientAllTrafficFile ($filename, $cPrivKey, $cIpAddress, $sPubKey, $endpoint, $port, $kAlive, $inDns) {
	$clientAllTrafficFile = "/var/www/htdocs/data/client/AllTraffic_" . $filename . ".conf";
	unlink($clientAllTrafficFile);
	$myAllTrafficConfFile = fopen( $clientAllTrafficFile , "w") or die("Unable to open config file for output!");

	fwrite($myAllTrafficConfFile, "[Interface]\n");
	fwrite($myAllTrafficConfFile, "PrivateKey = " . $cPrivKey . "\n");
	fwrite($myAllTrafficConfFile, "Address = " . $cIpAddress . "\n");
	fwrite($myAllTrafficConfFile, "DNS = " . $inDns . "\n");
	fwrite($myAllTrafficConfFile, "\n");
	fwrite($myAllTrafficConfFile, "[Peer]\n");
	fwrite($myAllTrafficConfFile, "PublicKey = " . $sPubKey . "\n");
	fwrite($myAllTrafficConfFile, "AllowedIPs = 0.0.0.0/0\n");
	fwrite($myAllTrafficConfFile, "Endpoint = " . $endpoint . ":" . $port . "\n");
	fwrite($myAllTrafficConfFile, "PersistentKeepalive = " . $kAlive );

	fclose($myAllTrafficConfFile);
}

/* Logic to Verify Input */
if (!isValidCidr($clientIp)) {
	$errorString .= "Error: Client VPN IP is not valid! [" . $clientIp . "]<br>";
	$greenLight = False;
}

// Verify Networks
if (!isValidCidrList($networks)) {
	$errorString .= "Error: Networks List is Invalid or Formatted Incorrectly! [" . $networks . "]<br>";
	$greenLight = False;
}

if (strlen($clientPrivate) != 44) {
	$errorString .= "Error: Client Private Key is not valid! [" . $clientPrivate . "]<br>";
	$greenLight = False;
}

if (strlen($clientPublic) != 44) {
	$errorString .= "Error: Client Public Key is not valid! [" . $clientPublic . "]<br>";
	$greenLight = False;
}

if (!preg_match("/^\d+$/", $keepAlive)) {
	$errorString .= "Error: Keep Alive value can only contain digits! [" . $keepAlive . "]<br>";
	$greenLight = False;
} else {
	$kaValue = (int)$keepAlive;
	if ($kaValue < 0 || $kaValue > 9999) {
		$errorString .= "Error: Keep Alive value must be between 0 and 9999! [" . $keepAlive . "]<br>";
		$greenLight = False;
	}
}

if (strlen($srvPublicKey) != 44) {
	$errorString .= "Error: Server Public Key is not valid! [" . $srvPublicKey . "]<br>";
	$greenLight = False;
}


if (!filter_var($outsideAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
    !filter_var($outsideAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
	$errorString .= "Error: External Address is not valid! [" . $outsideAddress . "]<br>";
	$greenLight = False;
}

if (!preg_match("/^\d+$/", $vpnPort)) {
	$errorString .= "Error: VPN Port value can only contain digits! [" . $vpnPort . "]<br>";
	$greenLight = False;
} else {
	$port = (int)$vpnPort;
	if ($port < 0 || $port > 65535) {
		$errorString .= "Error: VPN Port value must be between 0 and 65535! [" . $vpnPort . "]<br>";
		$greenLight = False;
	}
}

if (!filter_var($internalNetworkDns, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$errorString .= "Error: Internal Network DNS Address is not valid! [" . $internalNetworkDns . "]<br>";
	$greenLight = False;
} 


/* HTML Content */

echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Device Applying Client Settings</title>\n";
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
echo "        <div class=\"innerContainer\">\n";
echo "             <p><b><u>Adding VPN Client</u></b></p>\n";
echo "             <p>Settings Recieved<br></p>\n";
if ($greenLight == False) {
echo "             <p>Unable to Add Client</p>\n";
echo "             <p><u>Errors Encountered:</u></p>\n";
echo "             <p>" . $errorString . "</p>\n";
} else {
echo "             <p>Settings Validated</p>\n";
echo "             <p>Creating Config Files</p>\n";

/* BEGIN CONFIG FILE CREATION */

$keyPrefix = substr($clientPublic, 0, 10);
CreateServerPeerFile ($clientPublic, $clientIp, $existingPeers );
CreateClientInternalFile ($keyPrefix, $clientPrivate, $clientIp, $srvPublicKey, $networks, $outsideAddress, $vpnPort, $keepAlive );
CreateClientAllTrafficFile ($keyPrefix, $clientPrivate, $clientIp, $srvPublicKey, $outsideAddress, $vpnPort, $keepAlive, $internalNetworkDns);

/* END CONFIG FILE CREATION */

echo "             <p>Applying Settings</p>\n";
echo "             <p>Restarting VPN with Updated Settings</p>\n";
}

/*
echo "             <p>clientIp: " . $clientIp . "</p>\n";
echo "             <p>networks: " . $networks . "</p>\n";
echo "             <p>clientPrivate: " . $clientPrivate . "</p>\n";
echo "             <p>clientPublic: " . $clientPublic . "</p>\n";
echo "             <p>keepAlive: " . $keepAlive . "</p>\n";
echo "             <p>srvPublicKey: " . $srvPublicKey . "</p>\n";
echo "             <p>outsideAddress: " . $outsideAddress . "</p>\n";
echo "             <p>vpnPort: " . $vpnPort . "</p>\n";
echo "             <p>internalNetworkDns: " . $internalNetworkDns . "</p>\n";
*/


echo "        </div>\n";
echo "    </main>\n";
echo "\n";
echo "    <footer includefile=\"footer.html\"></footer>\n";
echo "</body>\n";
echo "<script src=\"js/include.js\"></script>\n";
echo "\n";
//echo var_dump($_POST) . "<br>\n";
echo "</html>\n";

flush();
if ($greenLight) {
	shell_exec('/usr/local/vpn/runElevated client ' . $keyPrefix);
}

?>

<?php

$externalAddress = $_POST["outsideAddress"];
$vpnPort = $_POST["vpnPortNumber"];
$internalNetworkDns = $_POST["internalNetworkDns"];
$wgIpAddress = $_POST["wgNetCIDR"];
$privateKey = $_POST["srvPrivate"];
$publicKey = $_POST["srvPublic"];

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


function CreateConfigFile ($address, $porNum, $wgCidr, $prKey ) {
	$outFile = "/var/www/htdocs/data/wgServer.conf";
	unlink($outFile);
	$myConfigFile = fopen( $outFile , "w") or die("Unable to open config file for output!");

	// CONNECTION
	fwrite($myConfigFile, "[Interface]\n");
	fwrite($myConfigFile, "Address = " . $address . "\n");
	fwrite($myConfigFile, "SaveConfig = false\n");
	fwrite($myConfigFile, "PostUp = iptables -A FORWARD -i %i -j ACCEPT; iptables -A FORWARD -o %i -j ACCEPT; iptables -t nat -A POSTROUTING -o eth1 -j MASQUERADE\n");
	fwrite($myConfigFile, "PostDown = iptables -D FORWARD -i %i -j ACCEPT; iptables -D FORWARD -o %i -j ACCEPT; iptables -t nat -D POSTROUTING -o eth1 -j MASQUERADE\n");
	fwrite($myConfigFile, "ListenPort = " . $porNum . "\n");
	fwrite($myConfigFile, "PrivateKey = " . $prKey . "\n");
	fwrite($myConfigFile, "\n");

	fclose($myConfigFile);
}

function CreateAddressFile ($exAddress) {
	$addyFile = "/var/www/htdocs/data/ADDRESS";
	unlink($addyFile);
	$myAddressFile = fopen( $addyFile , "w") or die("Unable to open config file for output!");
	fwrite($myAddressFile, $exAddress);
	fclose($myAddressFile);
}

function CreateInsideDnsFile ($dnsAddress) {
	$dnsFile = "/var/www/htdocs/data/INSIDEDNS";
	unlink($dnsFile);
	$myDnsFile = fopen( $dnsFile , "w") or die("Unable to open config file for output!");
	fwrite($myDnsFile, $dnsAddress);
	fclose($myDnsFile);
}

/* Logic to Verify Input */
if (!filter_var($externalAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
    !filter_var($externalAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
	$errorString .= "Error: External Address is not valid! [" . $externalAddress . "]<br>";
	$greenLight = False;
} else {
	CreateAddressFile($externalAddress);
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
	$errorString .= "Error: Internal DNS Address is not valid! [" . $internalNetworkDns . "]<br>";
	$greenLight = False;
} else {
	CreateInsideDnsFile($internalNetworkDns);
}

if (!isValidCidr($wgIpAddress)) {
	$errorString .= "Error: VPN Network Address is not valid! [" . $wgIpAddress . "]<br>";
	$greenLight = False;
}

/* Better checking could be done on these, but is it really needed?
   We have a key or we don't. We create valid keys if they are missing
   at boot time
*/
if (strlen($privateKey) != 44) {
	$errorString .= "Error: Private Key is not valid! [" . $privateKey . "]<br>";
	$greenLight = False;
}

if (strlen($publicKey) != 44) {
	$errorString .= "Error: Public Key is not valid! [" . $publicKey . "]<br>";
	$greenLight = False;
}

/* HTML Content */

echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Device Applying Server Settings</title>\n";
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
echo "             <p><b><u>Apply VPN Server Settings</u></b></p>\n";
echo "             <p>Settings Recieved<br></p>\n";
if ($greenLight == False) {
echo "             <p>Unable to Apply Settings</p>\n";
echo "             <p><u>Errors Encountered:</u></p>\n";
echo "             <p>" . $errorString . "</p>\n";
} else {
echo "             <p>Settings Validated</p>\n";
echo "             <p>Creating Config Files</p>\n";

/* BEGIN CONFIG FILE CREATION */
CreateConfigFile($wgIpAddress, $vpnPort, $wgIpAddress, $privateKey);
/* END CONFIG FILE CREATION */

echo "             <p>Applying Settings</p>\n";
echo "             <p>Restarting VPN with Updated Settings</p>\n";

/*
echo "             <p>externalAddress: " . $externalAddress . "</p>\n";
echo "             <p>vpnPort: " . $vpnPort . "</p>\n";
echo "             <p>wgIpAddress: " . $wgIpAddress . "</p>\n";
echo "             <p>privateKey: " . $privateKey . "</p>\n";
echo "             <p>publicKey: " . $publicKey . "</p>\n";
*/

}

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
	shell_exec('/usr/local/vpn/runElevated server');
}

?>

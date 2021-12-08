<?php

$routingPriority=$_POST["routingPriority"];
$inConfigType=$_POST["inConfigType"];
$inAddress=$_POST["inAddress"];
$inSubnet=$_POST["inSubnet"];
$inGateway=$_POST["inGateway"];
$inNameServers=$_POST["inNameServers"];
$exConfigType=$_POST["exConfigType"];
$exAddress=$_POST["exAddress"];
$exSubnet=$_POST["exSubnet"];
$exGateway=$_POST["exGateway"];
$exNameServers=$_POST["exNameServers"];

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


//CC BY-SA 4.0   [Provided by user: https://stackoverflow.com/users/1338292/ja%cd%a2ck ]
// based on idea provided by above author
function getUuidV4() {
	$uuidBytes = random_bytes(16);
	$uuidBytes[6] = chr(ord($uuidBytes[6]) & 0x0f | 0x40); // set version
	$uuidBytes[8] = chr(ord($uuidBytes[8]) & 0x3f | 0x80); // bits 6-7 should be 10

	return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($uuidBytes), 4));
}

function CreateConfigFile ($inFilename, $interface, $priority, $method, $address, $subnet, $gateway, $dnsServers ) {
	$outFile = "data/" . $inFilename;
	unlink($outFile);
	$myConfigFile = fopen( $outFile , "w") or die("Unable to open config file for output!");

	// CONNECTION
	fwrite($myConfigFile, "[connection]\n");
	fwrite($myConfigFile, "id=" . $inFilename . "\n");
	$myUuid = getUuidV4();
	fwrite($myConfigFile, "uuid=" . $myUuid . "\n");
	fwrite($myConfigFile, "type=ethernet\n");
	fwrite($myConfigFile, "autoconnect=true\n");
	fwrite($myConfigFile, "autoconnect-priority=-999\n");
	fwrite($myConfigFile, "interface-name=" . $interface . "\n");
	fwrite($myConfigFile, "permissions=\n\n");

	// ETHERNET
	fwrite($myConfigFile, "[ethernet]\n");
	fwrite($myConfigFile, "mac-address-blacklist=\n\n");

	// IPv4
	fwrite($myConfigFile, "[ipv4]\n");
	if ( $method == "manual" ) {
		fwrite($myConfigFile, "address1=" . $address . "\n");
		fwrite($myConfigFile, "address-mask=" . $subnet . "\n");
		fwrite($myConfigFile, "gateway=" . $gateway . "\n");

		$dnsLine = "";
		foreach ( $dnsServers as &$myDnsServer ) {
			$dnsLine = $dnsLine . $myDnsServer . ";";
		}
		fwrite($myConfigFile, "dns=" . $dnsLine . "\n");

		fwrite($myConfigFile, "dns-search=\n");
		fwrite($myConfigFile, "may-fail=false\n");
		fwrite($myConfigFile, "method=manual\n");
	} elseif ( $method == "dhcp" ) {
		fwrite($myConfigFile, "dns-search=\n");
		fwrite($myConfigFile, "may-fail=false\n");
		fwrite($myConfigFile, "method=auto\n");
		
	}

	if ( !$priority ) {
		fwrite($myConfigFile, "route-metric=10\n");
		fwrite($myConfigFile, "never-default=true\n");
	} else {
		fwrite($myConfigFile, "route-metric=20\n");
	}
	fwrite($myConfigFile, "\n");

	// IPv6
	fwrite($myConfigFile, "[ipv6]\n");
	fwrite($myConfigFile, "addr-gen-mode=stable-privacy\n");
	fwrite($myConfigFile, "dns-search=\n");
	fwrite($myConfigFile, "method=disabled\n\n");

	// PROXY
	fwrite($myConfigFile, "[proxy]\n");

	fclose($myConfigFile);
}

/* Logic to Verify Input */
if (IsNullOrEmptyString($routingPriority)) {
	$errorString .= "Error: Routing Priority is Empty or Null<br>";
	$greenLight = False;
} else if (strcmp("internal", $routingPriority) != 0
	and strcmp("external", $routingPriority) != 0){
		$errorString .= "Error: Routing Priority invalid [" . $routingPriority . "]<br>";
		$greenLight = False;
}

if (IsNullOrEmptyString($inConfigType)) {
	$greenLight = False;
	$errorString .= "Error: In Config Type is Empty or Null<br>";
} else if (strcmp("manual", $inConfigType) != 0
	&& strcmp("dhcp", $inConfigType) != 0){
		$errorString .= "Error: In Config invalid [" . $inConfigType . "]<br>";
		$greenLight = False;
}

if (!filter_var($inAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$errorString .= "Error: In IP Address invalid [" . $inAddress . "]<br>";
	$greenLight = False;
}

/* TODO Add Better Verification of Subnet Mask Values */
if (!filter_var($inSubnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$errorString .= "Error: In Subnet invalid [" . $inSubnet . "]<br>";
	$greenLight = False;
}

if ( $routingPriority == "internal" && $inConfigMethod == "manual" ) {
	if (!filter_var($inGateway, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$errorString .= "Error: In Gateway invalid [" . $inGateway . "]<br>";
		$greenLight = False;
	}	
}

$inDNSServers = explode(',', $inNameServers);
foreach ( $inDNSServers as &$myDNS ) {
	if (!filter_var($myDNS, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$greenLight = False;
		$errorText .= "Error: Invalid internal DNS Server [" . $myDNS . "]<br>";
	}
}

if (IsNullOrEmptyString($exConfigType)) {
	$errorString .= "Error: Ex Config Type is Empty or Null<br>";
	$greenLight = False;
} else if (strcmp("manual", $exConfigType) != 0
	and strcmp("dhcp", $exConfigType) != 0){
		$errorString .= "Error: Ex Config invalid [" . $exConfigType . "]<br>";
		$greenLight = False;
}

if (!filter_var($exAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$errorString .= "Error: Ex IP Address invalid [" . $exAddress . "]<br>";
	$greenLight = False;
}

/* TODO Add Better Verification of Subnet Mask Values */
if (!filter_var($exSubnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$errorString .= "Error: Ex Subnet invalid [" . $exSubnet . "]<br>";
	$greenLight = False;
}

if ( $routingPriority == "external" && $exConfigType == "manual" ) {
	if (!filter_var($exGateway, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$errorString .= "Error: Ex Gateway invalid [" . $exGateway . "]<br>";
		$greenLight = False;
	}
}

$exDNSServers = explode(',', $exNameServers);
foreach ( $exDNSServers as &$myDNS ) {
	if (!filter_var($myDNS, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$greenLight = False;
		$errorText .= "Error: Invalid external DNS Server [" . $myDNS . "]<br>";
	}
}

/*
if ($greenLight == True) {
	$errorString .= "True";
}	
if ($greenLight == False) {
	$errorString .= "False";
}
*/

/* HTML Content */

echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Device Applying Network Setting</title>\n";
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
echo "             <p>Settings Recieved<br></p>\n";
if ($greenLight == False) {
echo "             <p>Unable to Apply Settings</p>\n";
echo "             <p><u>Errors Encountered:</u></p>\n";
echo "             <p>" . $errorString . "</p>\n";
} else {
echo "             <p>Settings Validated</p>\n";
echo "             <p>Creating Config Files</p>\n";

/* BEGIN CONFIG FILE CREATION */
$routing = ($routingPriority == "internal");
CreateConfigFile("Inside", "eth1", $routing, $inConfigType, $inAddress, $inSubnet, $inGateway, $inDNSServers );
CreateConfigFile("Outside", "eth0", !$routing, $exConfigType, $exAddress, $exSubnet, $exGateway, $exDNSServers );
/* END CONFIG FILE CREATION */

echo "             <p>Applying Settings</p>\n";
echo "             <p>Restarting Device with Updated Settings</p>\n";

/*
echo "             <p>routingPriority: " . $routingPriority . "</p>\n";
echo "             <p>inConfigType: " . $inConfigType . "</p>\n";
echo "             <p>inAddress: " . $inAddress . "</p>\n";
echo "             <p>inSubnet: " . $inSubnet . "</p>\n";
echo "             <p>inGateway: " . $inGateway . "</p>\n";
echo "             <p>inNameServers: " . $inNameServers . "</p>\n";
echo "             <p>exConfigType: " . $exConfigType . "</p>\n";
echo "             <p>exAddress: " . $exAddress . "</p>\n";
echo "             <p>exSubnet: " . $exSubnet . "</p>\n";
echo "             <p>exGateway: " . $exGateway . "</p>\n";
echo "             <p>exNameServers: " . $exNameServers . "</p>\n";
*/

}
echo "        </div>\n";
echo "    </main>\n";
echo "\n";
echo "    <footer includefile=\"footer.html\"></footer>\n";
echo "</body>\n";
echo "<script src=\"js/include.js\"></script>\n";
echo "\n";
echo "</html>\n";
flush();
if ($greenLight) {
	shell_exec('/usr/local/vpn/runElevated network');
}

?>

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

$serverConfig = json_decode(shell_exec('/usr/local/vpn/runElevated vpnConfig'), true);

// Text Field Defaults
$outsideAddress = "";
$internalNetworkDns = "";
$vpnPort = "";
$wgNetwork = "";
$srvPrivateKey = "";
$srvPublicKey = "";

if (!IsNullOrEmptyString($serverConfig["Interface"]["privatekey"])
	&& (!IsNullOrEmptyString($serverConfig["Interface"]["publickey"]))) { 
	$srvPrivateKey = $serverConfig["Interface"]["privatekey"];
	$srvPublicKey = $serverConfig["Interface"]["publickey"];

}

if (!IsNullOrEmptyString($serverConfig["Interface"]["address"])) { 
	$wgNetwork = $serverConfig["Interface"]["address"];
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
echo "    <title>VPN Server Configuration</title>\n";
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
echo "        <div id=\"serverConfig\" class=\"mainContainer\">\n";
echo "          <form method=\"post\" action=\"applyServerSettings.php\">\n";
echo "            <div id=\"vpnServerConfig\" class=\"innerContainer\">\n";
echo "                <b><u>WireGuard Server Configuration</u></b>\n";
echo "                <p>\n";
echo "                    <label for=\"outsideAddress\">External Facing Name or IP Address:</label><br>\n";
echo "                    <input name=\"outsideAddress\" class=\"addressInput\" type=\"text\" id=\"outsideAddress\" size=\"44\" maxlength=\"253\" value=\"" . $outsideAddress . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"vpnPort\">VPN Port Number:</label><br>\n";
echo "                    <input name=\"vpnPortNumber\" class=\"addressInput\" type=\"text\" id=\"vpnPort\" size=\"5\" maxlength=\"5\" value=\"" . $vpnPort . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"internalNetworkDns\">Internal Network DNS Server:</label><br>\n";
echo "                    <input name=\"internalNetworkDns\" class=\"addressInput\" type=\"text\" id=\"internalNetworkDns\" size=\"18\" maxlength=\"18\" value=\"" . $internalNetworkDns . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"wgNetwork\">WireGuard Network IP (CIDR):</label><br>\n";
echo "                    <input name=\"wgNetCIDR\" class=\"addressInput\" type=\"text\" id=\"wgNetwork\" size=\"18\" maxlength=\"18\" value=\"" . $wgNetwork . "\">\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"srvPrivateKey\">Server Private Key:</label><br>\n";
echo "                    <input name=\"srvPrivate\" class=\"addressInput\" type=\"text\" id=\"srvPrivateKey\" size=\"55\" maxlength=\"44\" value=\"" . $srvPrivateKey . "\" readonly>\n";
echo "                </p>\n";
echo "                <p>\n";
echo "                    <label for=\"srvPublicKey\">Server Public Key:</label><br>\n";
echo "                    <input name=\"srvPublic\" class=\"addressInput\" type=\"text\" id=\"srvPublicKey\" size=\"55\" maxlength=\"44\" value=\"" . $srvPublicKey . "\" readonly>\n";
echo "                </p>\n";
echo "            </div>\n";
echo "\n";
echo "            <div class=\"innerContainer\">\n";
echo "                <input class=\"submit\" type=\"submit\" value=\"Apply Settings\">\n";

// TODO: Add button here to create new Private/Public Server Keys
// The old keys should be removed, all client configurations would become invalidated
// WireGuard Server config file would need to be updated with the new keys
// Each client config would need to be updated to use the new server public key
// When server keys are changed, all client configs have to be updated.
// This is the way things are, but an easy way to update like offering a client config file 
// download would be nice solution

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
//echo var_dump($serverConfig) . "<br>";
echo "</html>\n";

?>

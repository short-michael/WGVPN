<?php

$clientPubKey = $_POST["peerToRemove"];
$clientFile = $_POST["downloadToRemove"];

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

function CreateRemovalFile ($key, $filename) {
        $removalFile = "/var/www/htdocs/data/REMOVAL";
        unlink($removalFile);
        $myRemovalFile = fopen( $removalFile , "w") or die("Unable to open removal file for output!");
        fwrite($myRemovalFile, $key . "\n" . $filename);
        fclose($myRemovalFile);
}

/* Logic to Verify Input */
if (strlen($clientPubKey) != 44) {
	$errorString .= "Error: Public Key is not valid! [" . $clientPubKey . "]<br>";
	$greenLight = False;
}

if (IsNullOrEmptyString($clientFile)) {
	$errorString .= "Error: client filename was not provide! [" . $clientFile . "]<br>";
	$greenLight = False;
}


/* HTML Content */

echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Device Remove Client</title>\n";
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
echo "             <p><b><u>Removing Client</u></b></p>\n";
echo "             <p>Input Recieved<br></p>\n";
if ($greenLight == False) {
	echo "             <p>Unable to Remove Client</p>\n";
	echo "             <p><u>Errors Encountered:</u></p>\n";
	echo "             <p>" . $errorString . "</p>\n";
} else {
	echo "             <p>Input Validated</p>\n";
	echo "             <p>Deleting Client from Config</p>\n";
	echo "             <p>Removing Client Config Download</p>\n";
	echo "             <p>Restarting VPN with Updated Config</p>\n";
	CreateRemovalFile($clientPubKey, $clientFile);
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
	shell_exec('/usr/local/vpn/runElevated clientremove');
}

?>

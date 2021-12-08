<?php

$newpass = $_POST["newpass"];
$verifypass = $_POST["verifypass"];

echo "<!DOCTYPE html>\n";
echo "<html lang=\"en-US\">\n";
echo "<head>\n";
echo "    <meta charset=\"utf-8\">\n";
echo "    <meta name=\"author\" content=\"Michael Short\">\n";
echo "    <meta name=\"description\" content=\"CIT490 VPN Web Configuration Interface\">\n";
echo "    <title>VPN Device Update Admin Password</title>\n";
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
echo "        <div id=\"applyPassword\" class=\"innerContainer\">\n";
echo "          <p>Verifying Passwords Match</p>\n";
$greenLight=True;
if ($newpass != $verifypass) {
	echo "          <p style=\"color:red\">ERROR: Password Fields were not the same!</p>\n";
	$greenLight = False;
} else {
	echo "          <p style=\"color:green\">Password Fields Match</p>\n";
	echo "          <p>Verifying Password Length</p>\n";
	$passLength = strlen($newpass);
	if ($passLength > 20 || $passLength < 6) {
		echo "          <p style=\"color:red\">ERROR: Password Must be 6-20 Characters in Length!</p>\n";
		$greenLight = False;
	} else {
		echo "          <p style=\"color:green\">Password Meets Size Requirements</p>\n";
		echo "          <p>Verifying Password Contents</p>\n";
		if (!preg_match('/^[a-zA-Z0-9]+$/', $newpass)) {
			echo "          <p style=\"color:red\">ERROR: Password Must Contain only Numbers and Letters!</p>\n";
			$greenLight = False;
		} else {
			echo "          <p style=\"color:green\">Password Contains Valid Characters</p>\n";
		}
	}
}
 
if ($greenLight) {
	echo "          <p>NEW PASSWORD HAS BEEN APPLIED!<p>\n";
} else {
	echo "          <p>PASSWORD NOT APPLIED!<p>\n";
}

// Verify the length meets minimum requirements, verify that it only contains upper / lower/ and numbers. No symbols or other stuff

echo "        </div>\n";
echo "    </main>\n";
echo "\n";
echo "    <footer includefile=\"footer.html\"></footer>\n";
echo "</body>\n";
echo "<script src=\"js/include.js\"></script>\n";
echo "\n";
//echo var_dump($_POST) . "<br>";
//echo var_dump($newpass) . "<br>";
//echo var_dump($verifypass) . "<br>";
echo "</html>\n";

flush();
if ($greenLight) {
	shell_exec("/usr/local/vpn/runElevated password $newpass");
}

?>

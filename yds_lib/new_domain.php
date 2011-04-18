<?php

$secret_key = $_POST["secret_key"];
$owner_email = $_POST["owner_email"];

// set secret key from session
if ((!isset($_POST['secret_key'])) && isset($_SESSION['secret_key'])) { $secret_key = $_SESSION['secret_key']; }

// form validation
if (($secret_key == "") || (!(check_email_address($owner_email))))
{
	// generate a new key
	if ($secret_key == "") {  $secret_key = make_random_string(); }
	$_SESSION['secret_key'] = $secret_key; // set session
?>
<html>
<?php include("html/head.html");?>
<body>
<?php include("html/header.html");?>
	<div id="newdomain_page">
<form id='newdomain' method='POST'>
	<div id="newdomainform">
		<div style="padding-bottom: 35px; font-size: 22pt;">Congrats, you've pointed your domain here.</div>
		<div style="padding-left: 50px; clear: both;"><div style="float: left; width: 7em; text-align: left;">Domain name</div><div style="float: left; text-align: left;"><a href="http://<?php print($domain);?>"><?php print($domain);?></a><br><span style="font-size: 10pt;">This is the domain name of this page</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;">Secret key to edit your page.<br>This is stored in <strong>plaintext</strong>, so keep it simple and don't use an existing password!</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Email</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='owner_email' value="<?php print($owner_email);?>"><br><span style="font-size: 10pt;"><?php if (!(check_email_address($owner_email)) && ($owner_email != "")) { print("<span style='color: #00A0B0;'>Email address not valid!</span><br>"); } ?>If you forget your password, we'll send it here.<br>Service updates will be emailed here, too.</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><input type='submit' style="font-size: 12pt; color: #fff;" value='create page &raquo;' class="button orange"></div>
	</div>
</form>
</div>
</body>
</html>
<?php
} // end if
else {
	$_SESSION['secret_key'] = $secret_key; // set session
	create_domain($domain, '%default%', $secret_key, $owner_email);
	include("html/new_domain.html");
}
?>
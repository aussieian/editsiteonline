<?php

$secret_key = $_POST["secret_key"];
$owner_email = $_POST["owner_email"];
if (($secret_key == "") || ($owner_email == ""))
{
	// show the form for the secret key and email
	if ($secret_key == "") { 
		$secret_key = "apple";
	}
?>
<html>
<?php include("html/head.html");?>
<body>
<?php include("html/header.html");?>
	<div id="newdomain">
<form id='newdomain' method='POST'>
	<div id="newdomainform">
		<div style="padding-bottom: 35px; font-size: 22pt;">Congrats, you've pointed your domain here.</div>
		<div style="padding-left: 50px; clear: both;"><div style="float: left; width: 7em; text-align: left;">Domain name</div><div style="float: left; text-align: left;"><a href="http://<?php print($domain);?>"><?php print($domain);?></a><br><span style="font-size: 10pt;">This domain name of this page</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;">Secret key to edit your page.<br>This is plaintext, so keep it simple!</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Email</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='owner_email' value="<?php print($owner_email);?>"><br><span style="font-size: 10pt;">If you forget your password, we'll send it here.<br>Service updates will be emailed here, too.</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><input type='submit' style="font-size: 12pt;" value='create page &raquo;' class="button orange"></div>
	</div>
</form>
</div>
</body>
</html>
<?php
} // end if
else {
	// create new domain page
	$domain_escaped = mysql_real_escape_string($domain);
	$secret_key_escaped = mysql_real_escape_string($secret_key);  
	$owner_email_escaped = mysql_real_escape_string($owner_email);
	
	$SQL = <<<EOT
	INSERT INTO  `yoodoos`.`sites` (
	`id` ,
	`domain` ,
	`page` , 
	`content` ,
	`content_backup` ,
	`secret_key` ,
	`owner_email` ,
	`last_update`
	)
	VALUES (
	NULL ,  
	'$domain_escaped',
	'/',  
	'%default%', 
	NULL ,  
	'$secret_key_escaped',  
	'$owner_email_escaped', 
	CURRENT_TIMESTAMP
	);
EOT;
mysql_query($SQL);
include("html/new_domain.html");
}
?>
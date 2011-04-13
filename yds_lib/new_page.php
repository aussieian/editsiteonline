<?php

$secret_key = stripslashes($_POST["secret_key"]);
$real_key = get_domain_key($domain);
$content = $_POST["content"];

if (( $real_key != $secret_key))
{
?>
<html>
<?php include("html/head.html");?>
<body>
<?php include("html/header.html");?>
	<div id="newdomain_page">
<form id='newdomain' method='POST'>
	<div id="newdomainform">
		<div style="padding-bottom: 35px; font-size: 22pt;">Create your page.</div>
		<div style="padding-left: 50px; clear: both;"><div style="float: left; width: 7em; text-align: left;">Page URL</div><div style="float: left; text-align: left;"><a href="http://<?php print($domain.$page);?>"><?php print($domain.$page);?></a><br><span style="font-size: 10pt;">The URL of this page</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;"><?php if (($real_key != $secret_key) && ($_POST["content"] != "")) { print ("<span style='color: yellow'>Wrong key!</span> (<a style='color: yellow' href='/edit/forgot_key'>email key to domain owner</a>)</span>"); } else { print("Enter your secret key."); } ?></span><br></div></div>
		<div style="clear: both; padding-top: 15px; text-align: left;">Page HTML<br><textarea name='content' class="editcontent"><?php print($content);?></textarea></div>
		<div style="font-size: 10pt; text-align: left; padding-top: 10px;">
		</div>
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
	$content_escaped = mysql_real_escape_string($content);
	$domain_escaped = mysql_real_escape_string($domain);
	$page_escaped = mysql_real_escape_string($page);
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
	'$page_escaped',
	'$content_escaped', 
	NULL ,  
	'',  
	'$owner_email_escaped', 
	CURRENT_TIMESTAMP
	);
EOT;
mysql_query($SQL);
include("html/new_page.html");
}
?>
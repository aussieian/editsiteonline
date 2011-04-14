<?php

$secret_key = stripslashes($_POST["secret_key"]);
$real_key = get_domain_key($domain);
$content = stripslashes($_POST["content"]);

// set secret key from session
if ((!isset($_POST['secret_key'])) && isset($_SESSION['secret_key'])) { $secret_key = $_SESSION['secret_key']; }

if (( $real_key != $secret_key) || ($content == ""))
{
	// default content
	if ($content == "") { 
	$content = "Put your HTML in here.\n\nTo edit, simply append /edit at the end of the URL.";
	}
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
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;"><?php if (($real_key != $secret_key) && ($_POST["content"] != "")) { print ("<span style='color: #00A0B0;'>Wrong key!</span> (<a style='color: #00A0B0;' href='/edit/forgot_key'>email key to domain owner</a>)</span>"); } else { print("Enter your secret key."); } ?></span><br></div></div>
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
	$_SESSION['secret_key'] = $secret_key; // set session
	create_domain_page($domain, $page, $content, $secret_key, $owner_email);
	include("html/new_page.html");
}
?>
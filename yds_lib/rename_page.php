<?php

$secret_key = stripslashes($_POST["secret_key"]);
$real_key = get_domain_key($domain);
$new_page = $_POST["new_page"];
$new_page = rtrim($new_page, "/"); // remove trailing slashes
$new_page = "/" . ltrim($new_page, "/"); // add a single leading slash

if (( $real_key != $secret_key) || ( $new_page == "") || (page_exists($domain, $new_page)))
{	
	// set secret key from session
	if (($secret_key == "") && isset($_SESSION['secret_key'])) { $secret_key = $_SESSION['secret_key']; }
?>
<html>
<?php include("html/head.html");?>
<body>
	<?php include("html/header.html");?>
<?php
// see if domain exists yet
if (!domain_exists($domain)) { die('<div id="error">Sorry, the page for <a href="http://'.$domain.'">'.$domain.'</a> has not been created yet.</div>'); }

// see if page exists yet
if (!page_exists($domain, $page)) { die('<div id="error">Sorry, the page for <a href="http://'.$domain.$page.'">'.$domain.$page.'</a> has not been created yet.</div>'); }

// see if its a root page
if ($page == "/") { die('<div id="error">Sorry, you can\'t rename the home page.</div>'); }

?>
	<div id="newdomain_page">
<form id='remove_page' method='POST'>
	<div id="remove_page_form">
		<div style="padding-bottom: 35px; font-size: 22pt;">Rename page</div>
		<div style="padding-left: 50px; clear: both;"><div style="float: left; width: 7em; text-align: left;">Current URL</div><div style="float: left; text-align: left;"><a href="http://<?php print($domain.$page);?>"><?php print($domain.$page);?></a><br><span style="font-size: 10pt;">The URL of this page</span></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 10px;"><div style="float: left; width: 7em; text-align: left;">Rename to </div><div style="float: left; text-align: left;">/<input type='text' name='new_page' value="<?php print(ltrim($new_page, "/"));?>"><br><span style="font-size: 10pt;">New page URL</span><?php if (($new_page != "/") && (page_exists($domain, $new_page))) { print ("<span style='font-size: 10pt; color: #00A0B0;'> Page already exists!</span>"); } ?></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;"><?php if (($real_key != $secret_key) && ($secret_key != "")) { print ("<span style='color: #00A0B0;'>Wrong key!</span> (<a style='color: #00A0B0;' href='/edit/forgot_key'>email key to domain owner</a>)</span>"); } else { print("Enter your secret key."); } ?></span><br></div></div>
		<div style="font-size: 10pt; text-align: left; padding-top: 10px;">
		</div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><input type='submit' style="font-size: 12pt;" value='rename page &raquo;' class="button orange"></div>
	</div>
</form>
</div>
</body>
</html>
<?php
} // end if
else {
	$_SESSION['secret_key'] = $secret_key; // set session
	rename_page($domain, $page, $new_page);
	include("html/renamed_page.html");
}
?>
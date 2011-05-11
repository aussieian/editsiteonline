<html>
<?php include("html/head.html");?>
<body>
<?php include("html/header.html");?>
	<div id="editdomain">
<?php

//print "Connected to MySQL<br>";
mysql_select_db($dbname);

// domain
$domain = strtolower($_SERVER["HTTP_HOST"]);
// check for #! escaped_fragment URL and if so serve it as a page
// see http://code.google.com/web/ajaxcrawling/docs/faq.html
if (strpos($_SERVER["REQUEST_URI"], "?_escaped_fragment_=")) {
	$page = strtolower(preg_replace("/\/edit.*?$/i", "", $_SERVER["REQUEST_URI"])); // ie: /foobar/edit?id=1 will return /foobar
} else {
	$request_uri_parts = explode("?", $_SERVER["REQUEST_URI"]);
	$page = strtolower(preg_replace("/\/edit.*?$/i", "", $request_uri_parts[0])); // ie: /foobar/edit?id=1 will return /foobar
}
if ($page == "") { $page = "/"; } // rewrite root page to /

// see if domain exists yet
if (!domain_exists($domain)) { die('<div id="error">Sorry, the page for <a href="http://'.$domain.'">'.$domain.'</a> has not been created yet.</div>'); }

// see if page exists yet
if (!page_exists($domain, $page)) { die('<div id="error">Sorry, the page for <a href="http://'.$domain.$page.'">'.$domain.$page.'</a> has not been created yet.</div>'); }

$secret_key = stripslashes($_POST["secret_key"]);
$content = $_POST["content"];
$stealth = stripslashes($_POST["stealth"]);
$real_key = get_domain_key($domain);
$filename = stripslashes($_POST["file_upload_filename"]);
$filesize = stripslashes($_POST["file_upload_filesize"]);
$file_upload_remove = $_POST["file_upload_remove"];

// set secret key from session
if ((!isset($_POST['secret_key'])) && isset($_SESSION['secret_key'])) { $secret_key = $_SESSION['secret_key']; }

if (( $real_key != $secret_key) || ($content == ""))
{
	// get the content if it was set to empty
	if ($content == "") {
		$content = htmlentities(get_page_content($domain, $page));
	}
	
	// check it's not default
	if ($content == "%default%") {
		$content = file_get_contents("html/new_domain.html");
	}
	
	// set stealth mode
	if (($_POST['content'] == "") and !(is_public_domain($domain))) {
		$stealth = "yes";
	}
	
	// get filename and filesize
	$filename = get_page_filename($domain, $page);
	$filesize = get_page_filesize($domain, $page);
	
	// show the form for the secret key and content
?>

<div id="newdomain_page">
	
<form id='editdomain' method='POST'>

	<div id="editdomainform">
		<div style="clear: both;"><div style="float: left; width: 7em; text-align: left;">Your page</div><div style="float: left; text-align: left;"><a href="http://<?php print($domain.$page);?>"><?php print($domain.$page);?></a></div></div>
		<div style="clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;"><?php if (($real_key != $secret_key) && ($_POST["content"] != "")) { print ("<span style='color: #cc2a41;'>Wrong key! (<a style='color: #cc2a41;' href='/edit/forgot_key'>email key to domain owner</a>)</span>"); } else { print("Enter your secret key."); } ?></span></div></div>
		<?php
		// only show stealth for pages
		if ($page == "/") {
		?>
		<div style="clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">&nbsp;</div><div style="float: left; text-align: left; width: 280px;"><input type='checkbox' name='stealth' value='yes' <?php if ($stealth == "yes") { print("checked"); } ?>><span style="font-size: 10pt;">Stealth mode. Hide from public listing.</span></div></div>
		<?php } // end if ?>
		<div style="clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Attachment</div>
			<div style="float: left; text-align: left; width: 320px;">
				<input id="file_upload" name="file_upload" type="file" />
				<input id="file_upload_filename" name="file_upload_filename" type="hidden" value="<?php print($filename);?>">
				<input id="file_upload_filesize" name="file_upload_filesize" type="hidden" value="<?php print($filesize);?>">
				<a style="font-size: 10pt;" href="javascript:$('#file_upload').uploadifyUpload();">Upload Files</a><br>
				<span style="font-size: 10pt;" id="file_upload_message"><?php if($filename != "") { print("'".$filename."' (".round(($filesize/1024), 0)." KB)"); }?></span>
				<?php if($filename != "") { ?><br><input id="file_upload_remove" name="file_upload_remove" type="checkbox" value="yes"><span style="font-size: 10pt;">Remove attachment</span><? } ?>
				<br><div id="attachment_quota"><?php if (domain_is_over_quota($domain)) { print("<span style='color: red;'>Over Quota :( </span>"); } else { print("Quota: "); }?> You are using <?php print(round((get_domain_attachments_size($domain)/1000), 1)); ?>MB of <?php print(round((get_domain_attachments_limit($domain)/1000), 1)); ?>MB <br>(<a target="_blank" style='color: #fff;' href="http://yoodoos.com/#donate">Donate</a> to get more quota.)</div>
			</div>
		</div>
		<div style="clear: both; padding-top: 15px; text-align: left;">Edit HTML <span style="font-size: 10pt;"><?php if (get_page_backup($domain, $page) != "") { ?><a style="color: #00A0B0;" href="http://<?php print($domain.$page."/backup");?>">previous version</a></span><? } ?><br><textarea name='content' class="editcontent"><?php print($content);?></textarea></div>
		<div style="font-size: 10pt; text-align: left; padding-top: 10px;">
		Tips:
		<ul style="margin-top: 0.5em;">
			<li>Use #YOODOOS_CLONE:domainname# to clone a Yoodoos hosted domain
			<li>Use #YOODOOS_PAGE:/page_url# to insert the contents of a page (useful for templates)
			<li>To create a new page, just type the page URL into your browser.
			<li>To download an attachment, append '/download' to the end of the URL.
		</ul>
		</div>
		<div style="clear: both; padding-top: 15px;"><input type='submit' style="font-size: 12pt; color: #fff;" value='save page &raquo;' class="button orange"></div>
	</div>
</form>

	<div id='domainpages'>
	<h2>Pages on this domain</h2>
	<ul>
<?php foreach (get_domain_pages($domain) as $edit_page) { ?>
	<li><a href="http://<?php print($domain.$edit_page);?>"><?php print($domain.$edit_page);?></a> <span style='font-size: smaller;'><a style='color: #00A0B0;' href="http://<?php print(str_replace("//", "/", ($domain.$edit_page."/edit")));?>">edit</a> <?php if($edit_page != "/") {?><a style='color: #00A0B0;' href="http://<?php print(str_replace("//", "/", ($domain.$edit_page."/rename")));?>">rename</a> <a style='color: red;' href="http://<?php print(str_replace("//", "/", ($domain.$edit_page."/remove")));?>">remove</a></span><?php } ?>
<?
	} // end foreach
?>
	<li><?php print($domain);?>/<input style='font-size: 12pt;' type='text' id='new_page' name='new_page'> <button class="button orange" OnClick="window.location.href = '/' + document.getElementById('new_page').value;">create page</button>
	</ul>
	</div>
	
	<div id='domainstats'>
		Page views: <?php print(get_view_count($domain, $page));?>
	</div>
</div>
	
	<div id="footer" style="padding-top: 20px;">
		Created by <a href="http://twitter.com/aussie_ian">@aussie_ian</a> at <a href="http://www.insight4.com">Insight4 Labs</a> If you like this then let me know!
	</div>

<?php
} // end if
else {	
	
	// remove attachment?
	if (($file_upload_remove == "yes") || (domain_is_over_quota($domain))) {
		$filename = "";
		$filesize = 0;
	}
	
	$_SESSION['secret_key'] = $secret_key; // set session
	edit_domain_page($domain, $page, $content, $stealth, $filename, $filesize);	
?>
Your page <a href="http://<?php print($domain.$page);?>"><?php print($domain.$page);?></a> has been saved.
<?
}
?>
</div>
</body>
</html>

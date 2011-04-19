<?php

$secret_key = stripslashes($_POST["secret_key"]);
$real_key = get_domain_key($domain);
$content = stripslashes($_POST["content"]);
$filename = stripslashes($_POST["file_upload_filename"]);
$filesize = stripslashes($_POST["file_upload_filesize"]);

// set secret key from session
if ((!isset($_POST['secret_key'])) && isset($_SESSION['secret_key'])) { $secret_key = $_SESSION['secret_key']; }

if (( $real_key != $secret_key) || ($content == ""))
{
	// default content
	if ($content == "") { 
	$content = "Put your HTML in here.\n\nTo edit, simply append /edit at the end of the URL.";
	}
	
	// get filename and filesize
	$filename = get_page_filename($domain, $page);
	$filesize = get_page_filesize($domain, $page);
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
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;"><?php if (($real_key != $secret_key) && ($_POST["content"] != "")) { print ("<span style='color: #cc2a41;'>Wrong key!</span> (<a style='color: #cc2a41;' href='/edit/forgot_key'>email key to domain owner</a>)</span>"); } else { print("Enter your secret key."); } ?></span><br></div></div>
		<div style="padding-left: 50px; clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Attachment</div>
			<div style="float: left; text-align: left; width: 280px;">
				<input id="file_upload" name="file_upload" type="file" />
				<input id="file_upload_filename" name="file_upload_filename" type="hidden" value="<?php print($filename);?>">
				<input id="file_upload_filesize" name="file_upload_filesize" type="hidden" value="<?php print($filesize);?>">
				<a style="font-size: 10pt;" href="javascript:$('#file_upload').uploadifyUpload();">Upload Files</a><br>
				<span style="font-size: 10pt;" id="file_upload_message"><?php if($filename != "") { print("'".$filename."' (".round(($filesize/1024), 0)." KB)"); }?></span>
				<br><div id="attachment_quota"><?php if (domain_is_over_quota($domain)) { print("<span style='color: red;'>Over Quota :(</span>"); } else { print("Quota: "); }?> You are using <?php print(round((get_domain_attachments_size($domain)/1000), 1)); ?>MB of <?php print(round((get_domain_attachments_limit($domain)/1000), 1)); ?>MB <br>(<a target="_blank" style='color: #fff;' href="http://yoodoos.com/#donate">Donate</a> to get more quota.)</div>
			</div>
		</div>
		<div style="clear: both; padding-top: 15px; text-align: left;">Page HTML<br><textarea name='content' class="editcontent"><?php print($content);?></textarea></div>
		<div style="font-size: 10pt; text-align: left; padding-top: 10px;">
		</div>
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
	create_domain_page($domain, $page, $content, $secret_key, $owner_email, $filename, $filesize);
	include("html/new_page.html");
}
?>
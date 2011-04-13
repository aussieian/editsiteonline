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
$request_uri_parts = explode("?", $_SERVER["REQUEST_URI"]);
$page = strtolower(preg_replace("/\/edit$/i", "", $request_uri_parts[0])); // ie: /foobar/edit?id=1 will return /foobar
if ($page == "") { $page = "/"; } // rewrite root page to /

// see if domain exists yet
if (!domain_exists($domain)) { die('Sorry, the page for <a href="http://'.$domain.'">'.$domain.'</a> has not been created yet. Go do <a href="http://'.$domain.'">that</a> first.</a>'); }

// see if page exists yet
if (!page_exists($domain, $page)) { die('Sorry, the page for <a href="http://'.$domain.$page.'">'.$domain.$page.'</a> has not been created yet. Go do <a href="http://'.$domain.$page.'">that</a> first.</a>'); }

$secret_key = stripslashes($_POST["secret_key"]);
$content = $_POST["content"];
$stealth = stripslashes($_POST["stealth"]);
$real_key = get_domain_key($domain);
if (( $real_key != $secret_key) || ($content == ""))
{
	// get the content if it was set to empty
	if ($content == "") {
		$content = get_page_content($domain, $page);
	}
	
	// check it's not default
	if ($content == "%default%") {
		$content = file_get_contents("../html/new_domain.html");
	}
	
	// set stealth mode
	if (($_POST['content'] == "") and !(is_public_domain($domain))) {
		$stealth = "yes";
	}
	
	// show the form for the secret key and content
?>
<form id='editdomain' method='POST'>

	<div id="editdomainform">
		<div style="clear: both;"><div style="float: left; width: 7em; text-align: left;">Your page</div><div style="float: left; text-align: left;"><a href="http://<?php print($domain.$page);?>"><?php print($domain.$page);?></a></div></div>
		<div style="clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">Secret key</div><div style="float: left; text-align: left; width: 280px;"><input type='text' name='secret_key' value="<?php print($secret_key);?>"><br><span style="font-size: 10pt;"><?php if (($real_key != $secret_key) && ($_POST["content"] != "")) { print ("<span style='color: yellow'>Wrong key! (<a style='color: yellow' href='/edit/forgot_key'>email key to domain owner</a>)</span>"); } else { print("Enter your secret key."); } ?></span></div></div>
		<?php
		// only show stealth for pages
		if ($domain == "/") {
		?>
		<div style="clear: both; padding-top: 15px;"><div style="float: left; width: 7em; text-align: left;">&nbsp;</div><div style="float: left; text-align: left; width: 280px;"><input type='checkbox' name='stealth' value='yes' <?php if ($stealth == "yes") { print("checked"); } ?>><span style="font-size: 10pt;">Stealth mode. Hide from public listing.</span></div></div>
		<?php } // end if ?>
		<div style="clear: both; padding-top: 15px; text-align: left;">Edit HTML <span style="font-size: 10pt;"><?php if (get_page_backup($domain, $page) != "") { ?><a style="color: yellow;" href="http://<?php print($domain.$page."/backup");?>">previous version</a></span><? } ?><br><textarea name='content' class="editcontent"><?php print($content);?></textarea></div>
		<div style="font-size: 10pt; text-align: left; padding-top: 10px;">
		Special commands:
		<ul style="margin-top: 0.5em;">
			<li>#YOODOOS_CLONE:domainname# - clone a Yoodoos hosted domain
			<li>#YOODOOS_PAGE:/page_url# - insert the contents of a page (useful for templates)
		</ul>
		</div>
		<div style="clear: both; padding-top: 15px;"><input type='submit' style="font-size: 12pt;" value='save page &raquo;' class="button orange"></div>
	</div>
</form>
	
	<div id='domainpages'>
	<h2>Pages on this domain</h2>
	<ul>
<?php
	foreach (get_domain_pages($domain) as $edit_page) {
?>
	<li><a href="http://<?php print($domain.$edit_page);?>"><?php print($domain.$edit_page);?></a> <span style='font-size: smaller;'><a style='color: yellow;' href="http://<?php print(str_replace("//", "/", ($domain.$edit_page."/edit")));?>">edit</a></span>
<?
	}
?>
	<li><?php print($domain);?>/<input style='font-size: 12pt;' type='text' id='new_page' name='new_page'> <button class="button orange" OnClick="window.location.href = '/' + document.getElementById('new_page').value;">create page</button>
	</ul>
	</div>
	
	<div id='domainstats'>
		Page views: <?php print(get_view_count($domain, $page));?>
	</div>
	
	<div id="footer" style="padding-top: 40px;">
		Created by <a href="http://twitter.com/aussie_ian">@aussie_ian</a> at <a href="http://www.insight4.com">Insight4 Labs</a> If you like this then let me know!
	</div>

<?php
} // end if
else {
	$domain_escaped = mysql_real_escape_string($domain);
	$page_escaped = mysql_real_escape_string($page);
	$content_escaped = mysql_real_escape_string($content);
	$public_mode_escaped = 1;
	if ($stealth == "yes")
	{
		$public_mode_escaped = 0;
	} 
	
$SQL = <<<EOT
	UPDATE  `yoodoos`.`sites` 
	SET `content_backup` =  `content`,
	`public_mode` = $public_mode_escaped,
	`content` = '$content_escaped'
	WHERE  `sites`.`domain` LIKE '$domain_escaped' AND `page` LIKE '$page_escaped';
EOT;
mysql_query($SQL);
?>
Your page <a href="http://<?php print($domain.$page);?>"><?php print($domain.$page);?></a> has been saved.
<?
}
?>
</div>
</body>
</html>
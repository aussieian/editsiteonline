<?php

// table structure
/* CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `page` varchar(512) DEFAULT NULL,
  `content` longtext NOT NULL,
  `content_backup` longtext,
  `secret_key` varchar(50) NOT NULL,
  `owner_email` varchar(255) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `public_mode` int(11) NOT NULL DEFAULT '1',
  `view_count` int(11) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `attachment_limit` int(11) DEFAULT NULL,
  `hide_create_page` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`),
  KEY `is_stealth` (`public_mode`),
  KEY `page` (`page`),
  KEY `domain_2` (`domain`,`page`),
  KEY `hide_create_page` (`hide_create_page`)
) ENGINE=MyISAM AUTO_INCREMENT=1333 DEFAULT CHARSET=latin1; */


// nginx rewrite config
/*
# Rewrite urls
location / {

    if (!-f $request_filename) {
       rewrite  ^(.*)$  /index.php last;
       break;
    }

    if (!-d $request_filename) {
       rewrite  ^(.*)$  /index.php last;
       break;
    }
}
*/

// apache .htaccess rewrite config
/*
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* index.php [L]
*/


// includes
include("yds_lib/config.php");
include("yds_lib/global.php");

//print "Connected to MySQL<br>";
mysql_select_db($dbname);

// domain
$domain = strtolower($_SERVER["HTTP_HOST"]);
// check for #! escaped_fragment URL and if so serve it as a page
// see http://code.google.com/web/ajaxcrawling/docs/faq.html
if (strpos($_SERVER["REQUEST_URI"], "?_escaped_fragment_=")) { 
	$page = $_SERVER["REQUEST_URI"];
} else { 
	$request_uri_parts = explode("?", $_SERVER["REQUEST_URI"]);
	$page = strtolower($request_uri_parts[0]); // get first part ie: /foobar?id=1 will return /foobar
}
$page = rtrim($page,"/"); // remove trailing slash
if ($page == "") { $page = "/"; } // rewrite root page to /
$insert_page_domain = "";


// serve attachment
function serveAttachment($domain, $page, $download=false)
{
	$filename = get_page_filename($domain, $page);
	$filepath = $_SERVER['DOCUMENT_ROOT'] . "/yds_attachments/" . $domain . "/" . $filename;
	$filesize = filesize($filepath);
	$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
	$mimetype = finfo_file($finfo, $filepath);
	$lastmodified = gmdate("D, d M Y H:i:s", filemtime($filepath));
	if (strstr($filename, ".htm")) {
		$mimetype = "text/html"; // for some reason finfo returns text/plain for .html
	}
	finfo_close($finfo);

	// send headers
	caching_headers($filepath, filemtime($filepath));
	header("Content-Length: " . $filesize);
	header("Content-Type: " . $mimetype);
	if ($download) {
		header('Content-Disposition: attachment; filename="'.$filename.'"');
	}
	readfile($filepath);
}

// see http://stackoverflow.com/questions/2000715/answering-http-if-modified-since-and-http-if-none-match-in-php
function caching_headers($file, $timestamp)
{
	$gmt_mtime = gmdate('r', $timestamp);
	header('ETag: "'.md5($timestamp.$file).'"');

	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
		if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp.$file)) {
		header('HTTP/1.1 304 Not Modified');
		exit();
		}
	}

	header('Last-Modified: '.$gmt_mtime);
	header('Cache-Control: public');
	header('Pragma: public'); // ianc

	// http://stackoverflow.com/questions/1385964/how-to-get-the-browser-to-cache-images-with-php
	header('Cache-control: public, max-age='.(60*60*24*365));
	header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
}

// hostname switching
function servePage($domain, $page)
{
	global $host_names;
	global $insert_page_domain;
	
	// check if its a editsiteonline page
	if (in_array($domain, $host_names)) {
		// host editsiteonline
		include("html/editsiteonline.html");
		return;
	}
	
	// check if its a reset secret key page
	if (preg_match("/\/edit\/forgot_key$/i", $page)) {
		email_domain_key($domain);
		include("html/forgot_key.html");
		sleep(2); // so people can't mash reset
		return;
	}
	
	// check if its an edit page
	if ((preg_match("/\/edit$/i", $page)) || (preg_match("/\/edit\/$/i", $page)))
	{
		include("yds_lib/edit_page.php");
		return;
	}
	
	// check if its an edit page
	if (preg_match("/\/backup$/i", $page))
	{
		$page = strtolower(preg_replace("/\/backup$/i", "", $page));
		if ($page == "") { $page = "/"; } // rewrite root page to /
		$content = get_page_backup($domain, $page);
		print($content);
		return;
	}
	
	// check if its an rename page
	if (preg_match("/\/rename$/i", $page))
	{
		$page = strtolower(preg_replace("/\/rename$/i", "", $page));
		if ($page == "") { $page = "/"; } // rewrite root page to /		
		include("yds_lib/rename_page.php");
		return;
	}
	
	// check if its an remove page
	if (preg_match("/\/remove$/i", $page))
	{
		$page = strtolower(preg_replace("/\/remove$/i", "", $page));
		if ($page == "") { $page = "/"; } // rewrite root page to /
		include("yds_lib/remove_page.php");
		return;
	}

	// create page
	if (preg_match("/\/create$/i", $page))
	{
		$page = strtolower(preg_replace("/\/create$/i", "", $page));
		if ($page == "") { $page = "/"; } // rewrite root page to /
		if (page_exists($domain, $page)) { 
			include("yds_lib/edit_page.php");
		} else {
			include("yds_lib/new_page.php");
		}
		return;
	}
	
	// check if it's a download page
	// file attachment
	if (preg_match("/\/download$/i", $page))
	{
		$page = strtolower(preg_replace("/\/download$/i", "", $page));
		$filename = get_page_filename($domain, $page);
		if ($filename != "") {
			update_view_count($domain, $page);
			serveAttachment($domain, $page, true);
			return;
		}
	}
	
	// new domain
	if (!domain_exists($domain)) {		
		// new domain
		include("yds_lib/new_domain.php");
		return;
	}
	
	// serve page
	$root_content = get_page_content($domain, "/");
	$content = get_page_content($domain, $page);
	
	// 301 redirect
	if (strpos($root_content, "#YOODOOS_301:") === 0) {
		preg_match("/#YOODOOS_301:(.*)#/i", $root_content, $regex_matches);
		// clone domain 
		$redirect_to = $regex_matches[1];
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: http://" . $redirect_to . "/" . $page);
		die();
	}
	
	// compatibility fix for old "clone:" syntax
	// ie: "clone:somedomain.com"
	if (strpos($root_content, "clone:") === 0) {
		$parts = explode(":", $root_content);
		$root_content = "#YOODOOS_CLONE:".$parts[1]."#";
	}

	
	// new page and domain not cloned
	if ((strpos($root_content, "#YOODOOS_CLONE:") !== 0) && (!page_exists($domain, $page))) {
		// new page
		if (!hide_create_page($domain)) { 
			include("yds_lib/new_page.php");
		} else {
			print("<p>Page not found.</p>");
		}
		return;
		
	}
	
	// default page
	if ($content == "%default%") {
		include("html/new_domain.html");
		return;
	}
		
	// cloned page
	if (strpos($root_content, "#YOODOOS_CLONE:") === 0) {
		preg_match("/#YOODOOS_CLONE:(.*)#/i", $root_content, $regex_matches);
		// clone domain 
		$clone_domain = $regex_matches[1];
		if (domain_exists($clone_domain)) {
			if (page_exists($clone_domain, $page)) {
				$filename = get_page_filename($clone_domain, $page);
				if ($filename != "") {
					serveAttachment($clone_domain, $page, false);
					// increase page count (give count to clone domain)
					update_view_count($clone_domain, $page);
					return;
				} else {
					$clone_content = get_page_content($clone_domain, $page);
					// insert page templates
					$insert_page_domain = $clone_domain;
					$clone_content = preg_replace_callback("/#YOODOOS_PAGE:.*?#/i", "insertPage", $clone_content, 10);
					// increase page count (give count to clone domain)
					update_view_count($clone_domain, $page);
					header("Content-Type: " . getMimeType($page));
					print($clone_content);	
				}
			}
		} else {
			print("Oops, can't clone domain '" . $clone_domain . $page . "' (not hosted on editsiteonline) <a href='/edit'>Edit page</a>");
		}
		return;
	}
	
	// file attachment
	$filename = get_page_filename($domain, $page);
	if ($filename != "") {
		update_view_count($domain, $page);
		serveAttachment($domain, $page, false);
		return;
	}
	
	// insert page templates
	$insert_page_domain = $domain;
	$content = preg_replace_callback("/#YOODOOS_PAGE:.*?#/i", "insertPage", $content, 16);
	
	// serve content
	update_view_count($domain, $page);
	header("Content-Type: " . getMimeType($page));
	print($content);
	return;	
}

function insertPage($matches)
{
	global $insert_page_domain;
	preg_match("/#YOODOOS_PAGE:(.*)#/i", $matches[0], $regex_matches);
	return get_page_content($insert_page_domain, $regex_matches[1]);
}


// start here
servePage($domain, $page);

?>

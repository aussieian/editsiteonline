<?php

// table structure
/* CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `content_backup` longtext,
  `secret_key` varchar(50) NOT NULL,
  `owner_email` varchar(255) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_2` (`domain`),
  KEY `domain` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
*/

// patches
/*ALTER TABLE `sites` ADD `is_stealth` INT NOT NULL DEFAULT '0',
ADD INDEX ( `is_stealth` )*/
/*ALTER TABLE `sites` CHANGE `is_stealth` `public_mode` INT( 11 ) NOT NULL DEFAULT '1'*/

// add path to domains
/*
ALTER TABLE  `sites` ADD  `page` VARCHAR( 512 ) NULL DEFAULT NULL AFTER  `domain` ,
ADD INDEX (  `page` );
UPDATE sites SET page = '/';
ALTER TABLE  `sites` DROP INDEX  `domain_2`;
ALTER TABLE  `sites` ADD INDEX (  `domain` ,  `page` );
ALTER TABLE  `sites` ADD  `view_count` INT NOT NULL
*/

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
$request_uri_parts = explode("?", $_SERVER["REQUEST_URI"]);
$page = strtolower($request_uri_parts[0]); // get first part ie: /foobar?id=1 will return /foobar
if ($page == "") { $page = "/"; } // rewrite root page to /
$insert_page_domain = "";

// hostname switching
function servePage($domain, $page)
{
	global $host_names;
	global $insert_page_domain;
	
	// check if its a yoodoos page
	if (in_array($domain, $host_names)) {
		// host yoodoos
		include("html/yoodoos.html");
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
	if (preg_match("/\/edit$/i", $page))
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
	
	// new domain
	if (!domain_exists($domain)) {		
		// new domain
		include("yds_lib/new_domain.php");
		return;
	}
	
	// serve page
	$root_content = get_page_content($domain, "/");
	$content = get_page_content($domain, $page);
		
	// new page and domain not cloned
	if ((strpos($root_content, "#YOODOOS_CLONE:") !== 0) && (!page_exists($domain, $page))) {
		include("yds_lib/new_page.php");
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
				$clone_content = get_page_content($clone_domain, $page);
				// insert page templates
				$insert_page_domain = $clone_domain;
				$clone_content = preg_replace_callback("/#YOODOOS_PAGE:.*?#/i", "insertPage", $clone_content, 10);
				// increase page count (give count to serving domain)
				update_view_count($domain, $page);
				print($clone_content);
			}
		} else {
			print("Oops, can't clone domain '" . $clone_domain . $page . "' (not hosted on Yoodoos) <a href='/edit'>Edit page</a>");
		}
		return;
	}
	
	// insert page templates
	$insert_page_domain = $domain;
	$content = preg_replace_callback("/#YOODOOS_PAGE:.*?#/i", "insertPage", $content, 10);
	
	// serve content
	update_view_count($domain, $page);
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
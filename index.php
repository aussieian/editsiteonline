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
// ALTER TABLE  `sites` ADD  `page` VARCHAR( 512 ) NULL DEFAULT NULL AFTER  `domain` ,
// ADD INDEX (  `page` );
// UPDATE sites SET page = '/';
// ALTER TABLE  `sites` DROP INDEX  `domain_2`
// ALTER TABLE  `sites` ADD INDEX (  `domain` ,  `page` );

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

// hostname switching
function servePage($domain, $page)
{
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
		return;
	}
	
	// check if its an edit page
	if (preg_match("/\/edit$/i", $page))
	{
		include("yds_lib/edit_page.php");
		return;
	}
	
	// new domain
	if (!domain_exists($domain)) {		
		// new domain
		include("yds_lib/new_domain.php");
		return;
	}
	
	// new page
	if (!page_exists($domain, $page)) {		
		// new page
		include("yds_lib/new_page.php");
		return;
	}
	
	// serve page
	$content = get_page_content($domain, $page);

	// default page
	if ($content == "%default%") {
		include("html/new_domain.html");
		return;
	}
		
	// cloned page
	if (strpos($content, "clone:") === 0) {
		// clone domain 
		$clone_domain = trim(substr($content, 6));
		if (domain_exists($clone_domain)) {
			if (page_exists($clone_domain, $page)) {
				$clone_content = get_page_content($clone_domain, $page);
				print($clone_content);
			}
		} else {
			print("Oops, can't clone domain '" . $clone_domain . $page . "' (not hosted on Yoodoos) <a href='/edit'>Edit page</a>");
		}
		return;
	}
	
	// serve content
	print($content);
	return;	
}

// start here
servePage($domain, $page);

?>
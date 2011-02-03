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


// includes
include("yds_lib/config.php");
include("yds_lib/global.php");

//print "Connected to MySQL<br>";
mysql_select_db($dbname);

// domain
$domain = strtolower($_SERVER["HTTP_HOST"]);

// hostname switching

if (in_array($domain, $host_names)) {
	// host yoodoos
	include("html/yoodoos.html");
}
else {	
	// host domain
	if (!domain_exists($domain)) {
		// new domain
		include("yds_lib/newdomain.php");
	}
	else {
		// existing domain
		$content = get_domain_content($domain);
		if ($content == "%default%") {
			include("html/new_domain.html");
		} elseif (strpos($content, "clone:") == 0) {
			// clone domain 
			$clone_domain = trim(substr($content, 6));
			if (domain_exists($clone_domain)) {
				$clone_content = get_domain_content($clone_domain);
				print($clone_content);
			} else {
				print("Oops, can't clone domain '" . $clone_domain . "' (not hosted on Yoodoos) <a href='/edit'>Edit page</a>");
			}
		}
		else {
			print($content);
		}
	}
}
?>
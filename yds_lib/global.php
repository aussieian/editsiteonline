<?php

// start session
session_start();

// get config
include("config.php");

// db connection
$dbh = mysql_connect($hostname, $username, $password) 
	or die("Unable to connect to MySQL");
	
// select dbname
mysql_select_db($dbname);	

function domain_exists($domain)
{
	return (page_exists($domain, "/"));
}

function page_exists($domain, $page)
{
	return (get_num_pages($domain, $page) > 0);
}

function create_domain_page($domain, $page, $content, $secret_key, $owner_email, $filename="", $filesize=0) // 5mb default
{
	// create new domain page
	$domain_escaped = mysql_real_escape_string($domain);
	$page_escaped = mysql_real_escape_string($page);
	$content_escaped = mysql_real_escape_string($content);
	$secret_key_escaped = mysql_real_escape_string($secret_key);  
	$owner_email_escaped = mysql_real_escape_string($owner_email);
	$filename_escaped = mysql_real_escape_string($filename);

	$SQL = <<<EOT
	INSERT INTO  `yoodoos`.`sites` (
	`id` ,
	`domain` ,
	`page` , 
	`content` ,
	`content_backup` ,
	`secret_key` ,
	`owner_email` ,
	`last_update`,
	`file_name`,
	`file_size`
	)
	VALUES (
	NULL ,  
	'$domain_escaped', 
	'$page_escaped',
	'$content_escaped', 
	NULL ,  
	'',  
	'$owner_email_escaped', 
	CURRENT_TIMESTAMP,
	'$filename_escaped',
	$filesize
	);
EOT;
//die($SQL);
mysql_query($SQL);	
}

function create_domain($domain, $content, $secret_key, $owner_email, $attachment_limit=5120000)
{
	// create new domain page
	$domain_escaped = mysql_real_escape_string($domain);
	$content_escaped = mysql_real_escape_string($content); //'%default%';
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
	`last_update`,
	`attachment_limit`
	)
	VALUES (
	NULL ,  
	'$domain_escaped',
	'/',  
	'$content_escaped', 
	NULL ,  
	'$secret_key_escaped',  
	'$owner_email_escaped',
	CURRENT_TIMESTAMP,
	$attachment_limit
	);
EOT;
	mysql_query($SQL);
}

function edit_domain_page($domain, $page, $content, $stealth="no", $filename="", $filesize=0)
{
	$domain_escaped = mysql_real_escape_string($domain);
	$page_escaped = mysql_real_escape_string($page);
	$content_escaped = mysql_real_escape_string($content);
	$filename_escaped = mysql_real_escape_string($filename);
	
	$public_mode_escaped = 1;
	if ($stealth == "yes")
	{
		$public_mode_escaped = 0;
	} 

$SQL = <<<EOT
	UPDATE  `yoodoos`.`sites` 
	SET `content_backup` =  `content`,
	`public_mode` = $public_mode_escaped,
	`content` = '$content_escaped',
	`file_name` = '$filename_escaped',
	`file_size` = $filesize
	WHERE  `sites`.`domain` LIKE '$domain_escaped' AND `page` LIKE '$page_escaped';
EOT;
	mysql_query($SQL);	
}

function get_num_pages($domain, $page)
{
	// show the page for the domain
	$SQL = "select count(*) as num_pages from sites where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	return $row['num_pages'];
}

function is_public_domain($domain)
{
	// get domain id
	$SQL = "select public_mode from sites where domain like '".mysql_real_escape_string($domain)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return ($row['public_mode'] == 1);
}

function get_page_filename($domain, $page)
{
	// get domain key
	$SQL = "select file_name from sites where domain like '".mysql_real_escape_string($domain)."' AND page LIKE '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return stripslashes($row['file_name']);
}

function get_page_filesize($domain, $page)
{
	// get domain key
	$SQL = "select file_size from sites where domain like '".mysql_real_escape_string($domain)."' AND page LIKE '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return stripslashes($row['file_size']);
}

function get_domain_attachments_size($domain)
{
	$SQL = "select sum(file_size) as total_size from sites where domain like '".mysql_real_escape_string($domain)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	return $row['total_size'] / 1024; // KB
}

function get_domain_attachments_limit($domain)
{
	$SQL = "select attachment_limit from sites where domain like '".mysql_real_escape_string($domain)."' and page LIKE '/';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	return $row['attachment_limit'] / 1024; // KB
}

function domain_is_over_quota($domain)
{
	$attachments_size = get_domain_attachments_size($domain);
	$attachments_limit = get_domain_attachments_limit($domain);
	return ($attachments_size > $attachments_limit);
}

function get_domain_key($domain)
{
	// get domain key
	$SQL = "select secret_key from sites where domain like '".mysql_real_escape_string($domain)."' AND page LIKE '/';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return stripslashes($row['secret_key']);
}

function get_domain_email($domain)
{
	// get domain email
	$SQL = "select owner_email from sites where domain like '".mysql_real_escape_string($domain)."' AND page LIKE '/';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return stripslashes($row['owner_email']);
}

function email_domain_key($domain)
{
	global $mail_from;
	
	$key = get_domain_key($domain);
	$email = get_domain_email($domain);
	
	// email owner of domain with the key
	mail( $email, "Yoodoos Secret Key", "Hi, \n\nyour secret key for " . $domain . " is: \n\n" . $key . "\n\n- Yooodoos.com", "From: " . $mail_from );
}

function get_page_content($domain, $page)
{
	// show the page for the domain
	$SQL = "select content from sites where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	return $row['content'];
}

function get_page_backup($domain, $page)
{
	// show the backup for the domain
	$SQL = "select content_backup from sites where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	return $row['content_backup'];
}


function get_recent_domains($limit)
{
	// show the page for the domain
	$SQL = "select domain from sites where content not like '\%default\%' and page like '/' and public_mode = 1 and domain not like '%yoodoos.com%' order by id desc limit ".$limit.";";
	$result = mysql_query($SQL);
	$recent_domains = array();
	while ($row = mysql_fetch_assoc($result)) {
		$recent_domains[] = $row['domain']; 
	}
	return $recent_domains;
}

function get_recent_yoodoos_domains($limit)
{
	// show the page for the domain
	$SQL = "select domain from sites where content not like '\%default\%' and page like '/' and public_mode = 1 and domain like '%yoodoos.com%' order by id desc limit ".$limit.";";
	$result = mysql_query($SQL);
	$recent_domains = array();
	while ($row = mysql_fetch_assoc($result)) {
		$recent_domains[] = $row['domain']; 
	}
	return $recent_domains;
}

function get_hidden_domains($limit)
{
	// show the page for the domain
	$SQL = "select domain from sites where content not like '\%default\%' and page like '/' and public_mode = 0 order by id desc limit ".$limit.";";
	$result = mysql_query($SQL);
	$recent_domains = array();
	while ($row = mysql_fetch_assoc($result)) {
		$recent_domains[] = $row['domain']; 
	}
	return $recent_domains;
}

function get_domain_pages($domain)
{
	// show the page for the domain
	$SQL = "select page from sites where domain like '".mysql_real_escape_string($domain)."' order by id asc;";
	$result = mysql_query($SQL);
	$pages = array();
	while ($row = mysql_fetch_assoc($result)) {
		$pages[] = $row['page']; 
	}
	return $pages;	
}

function update_view_count($domain, $page)
{
	$SQL = "update sites set view_count = view_count + 1 where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
}

function get_view_count($domain, $page)
{
	// show the page for the domain
	$SQL = "select view_count from sites where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	return $row['view_count'];
}

function remove_page($domain, $page)
{
	// remove the page
	$SQL = "delete from sites where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	return;
}

function rename_page($domain, $page, $new_page)
{
	// check if page already exists
	if (page_exists($domain, $new_page)) { 
		return false;
	}
	
	// rename the page
	$SQL = "update sites set page = '".mysql_real_escape_string($new_page)."' where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
	return true;
}


function make_random_string($length=5) 
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = "";    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}

// from: http://www.linuxjournal.com/article/9585
function check_email_address($email) 
{
	// First, we check that there's one @ symbol, 
	// and that the lengths are right.
  
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters 
		// in one section or wrong number of @ symbols.
		return false;
	}
	
	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) {
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",$local_array[$i])) {
			return false;
		}
	}

	// Check if domain is IP. If not, 
	// it should be valid domain name
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) {
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) {
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$",$domain_array[$i])) {
				return false;
			}
		}
	}
	
	// valid
	return true;
}

?>
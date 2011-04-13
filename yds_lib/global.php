<?php

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
	mail( $email, "Yoodoos Secret Key", "Hi, \n\nyour secret key for " . $domain . " is: \n\n" . $key . "\n\n.- Yooodoos.com", "From: " . $mail_from );
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
	$SQL = "select domain from sites where content not like '\%default\%' and public_mode = 1 and domain not like '%yoodoos.com%' order by id desc limit ".$limit.";";
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
	$SQL = "select domain from sites where content not like '\%default\%' and public_mode = 1 and domain like '%yoodoos.com%' order by id desc limit ".$limit.";";
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
	$SQL = "select domain from sites where content not like '\%default\%' and public_mode = 0 order by id desc limit ".$limit.";";
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


function make_random_string($length=5) 
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = "";    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}

function update_view_count($domain, $page)
{
	$SQL = "update sites set view_count = view_count + 1 where domain like '".mysql_real_escape_string($domain)."' and page like '".mysql_real_escape_string($page)."';";
	$result = mysql_query($SQL);
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
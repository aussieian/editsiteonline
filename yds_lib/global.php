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
	return (get_domain_id($domain) != null);
}

function get_domain_id($domain)
{
	// get domain id
	$SQL = "select id from sites where domain like '".mysql_real_escape_string($domain)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return $row['id'];
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
	$SQL = "select secret_key from sites where domain like '".mysql_real_escape_string($domain)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result); 
	return stripslashes($row['secret_key']);
}

function get_domain_content($domain)
{
	// show the page for the domain
	$SQL = "select content from sites where domain like '".mysql_real_escape_string($domain)."';";
	$result = mysql_query($SQL);
	$row = mysql_fetch_assoc($result);
	//return $row['content'];
	return $row['content'];
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


?>

<?php

// ajax contact form for insight4 website

$mailto = 'ian@insight4.com' ;
$subject = "2CO Order" ;

if (isset($_POST['submit'])) {
	
	// Prepare message
	$msg = "Time: " . date("m/d/y g:ia", time()) . "\n";
	foreach ($_POST as $field=>$value) {
		if ($field != " submit") $msg .= $field . ": " . $value . "\n";
	}
	
	if (mail($mailto, $subject, $msg, "From: ".$mailto)) {
		// Email was sent
		print("<p>Thanks for your order!</p>");
	} else {
		// Erro sending email
		print("<p>Sorry, there was a problem processing your order. Please email us at <a href='mailto:sales@lovepoemswizard.com'>.</p>");
	}
}

?>
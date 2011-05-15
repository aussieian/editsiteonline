<?php

// ajax contact form for insight4 website

$mailto = 'info@insight4.com' ;
$subject = "Enquiry from web site" ;

if (isset($_POST['submit'])) {
	
	// Prepare message
	$msg = "Time: " . date("m/d/y g:ia", time()) . "\n";
	foreach ($_POST as $field=>$value) {
		if ($field != "submit") $msg .= $field . ": " . $value . "\n";
	}
	
	if (mail($mailto, $subject, $msg, "From: ".$mailto)) {
		// Email was sent
		print("<p>Thanks for contacting us! Please allow 1-2 business days for us to respond to your enquiry.</p>")
	} else {
		// Erro sending email
		print("<p>Sorry, there was a problem submitting your enquiry. Please email us at info@insight4.com.</p>")
	}
}

?>
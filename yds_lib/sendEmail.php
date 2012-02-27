<?php
	
	$name = trim($_POST['name']);
	$email = $_POST['email'];
	$comments = $_POST['comments'];
	
	$site_owners_email = 'ian@insight4.com'; // Replace this with your own email address
	$site_owners_name = 'Ian'; // replace with your name
		
	if (strlen($name) < 2) {
		$error['name'] = "Please enter your name";	
	}
	
	if (!preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*+[a-z]{2}/is', $email)) {
		$error['email'] = "Please enter a valid email address. Example: lorem@ipsum.com";	
	}
	
	if (strlen($comments) < 3) {
		$error['comments'] = "Please leave a comment.";
	}
	
	if (!$error) {
		
		require_once('phpMailer/class.phpmailer.php');
		$mail = new PHPMailer();
		
		$mail->From = "webmaster@editsiteonline.com"; //$email;
		$mail->FromName = "editsiteonline";
		$mail->Subject = "Domain Request From $email (" . $_SERVER["REMOTE_ADDR"] . ")";
		$mail->AddAddress($site_owners_email, $site_owners_name);
		$mail->Body = "name: " . $name . "\n" . "email: " . $email . "\ncomments: " . $comments . "\nip: " . $_SERVER["REMOTE_ADDR"] . "\nbrowswer: " . $_SERVER["HTTP_USER_AGENT"];
			
		$mail->Send();
		
		echo "<li class='success'> Thank you, " . $name . ". We've received your message. We will get back to you shortly! </li>";
		
	} # end if no error
	else {

		$response = (isset($error['name'])) ? "<li class='error'>" . $error['name'] . "</li> \n" : null;
		$response .= (isset($error['email'])) ? "<li class='error'>" . $error['email'] . "</li> \n" : null;
		$response .= (isset($error['comments'])) ? "<li class='error'>" . $error['comments'] . "</li>" : null;
		
		echo $response;
	} # end if there was an error sending

?>

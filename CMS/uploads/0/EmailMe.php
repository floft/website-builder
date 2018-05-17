<?php
if (isset($_REQUEST["name2"]))
{
	if (isset($_REQUEST['require']))
	{
		if ($_REQUEST["name2"] != "" && $_REQUEST["message2"] != "" && $_REQUEST["subject2"] != "" && $_REQUEST["email2"] != "")
		{
			$name = $_REQUEST["name2"];
			$email = $_REQUEST["email2"];
			$subject = $_REQUEST["subject2"];
			$httpREFERER = $_REQUEST["HTTP_REFERER2"];
			
			if ($httpREFERER == '') $httpREFERER = 'No HTTP_REFERER';
			
			$message = 'Name: ' . $name . '
Email: ' . $email . '
IP: ' . $_SERVER["REMOTE_ADDR"] . '
Browser: ' . $_SERVER["HTTP_USER_AGENT"] . '
HTTP_REFERER: ' . $httpREFERER . '

';
			$message .= $_REQUEST["message2"];
			mail("youremail@example.com", $subject, $message, "From: ContactForm@example.com");
			
			echo "true";
		} else echo 'false';
	}
	else
	{
		if ($_REQUEST["name2"] != "" && $_REQUEST["message2"] != "" && $_REQUEST["subject2"] != "")
		{
			$name = $_REQUEST["name2"];
			$email = $_REQUEST["email2"];
			$subject = $_REQUEST["subject2"];
			$httpREFERER = $_REQUEST["HTTP_REFERER2"];
			
			if ($httpREFERER == '') $httpREFERER = 'No HTTP_REFERER';
			
			$message = 'Name: ' . $name . '
Email: ' . $email . '
IP: ' . $_SERVER["REMOTE_ADDR"] . '
Browser: ' . $_SERVER["HTTP_USER_AGENT"] . '
HTTP_REFERER: ' . $httpREFERER . '

';
			$message .= $_REQUEST["message2"];
			mail("youremail@example.com", $subject, $message, "From: ContactForm@example.com");
			
			echo "true";
		} else echo 'false';
	}
} else echo 'false';
?>

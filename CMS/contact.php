<html><body><?php
//check if any information has been filled in
if (isset($_REQUEST['subject']) || isset($_REQUEST['name']) || isset($_REQUEST['body']))
{
	//if it has check if all the information has been filled in
	if (($_REQUEST['subject']) && isset($_REQUEST['name']) && isset($_REQUEST['body']))
	{
		//if it has, get the nessessary variables from the "variables.php" file
		require "variables.php";

		//connect to the database
		$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
		mysql_select_db ($host_addon . $database);

		//select the administrator's email
		$query = "Select email from `users` where loginId = 1";
		$result = mysql_query($query);
		$my_email = mysql_result($result, 0,'email');
		mysql_close();

		//create a email message
		$message = "Name: " . $_REQUEST['name'] . "
";
		$message .= $_REQUEST['message'];

		//email the administrator what the user has inputed
		mail($my_email, $_REQUEST['subject'], $message, "From: EmailForm@$address");
		//tell the user that their email has been sent
		echo "Your email has been sent!";
	}
	else
	{
		//if the user didn't fill in the whole form tell them to do so
		echo "Please fill in the <b>Whole</b> form.<br />";
		echo '<h3>Email Me</h3>
		<form method="post" action="' . $_SERVER["PHP_SELF"] . '">
		Name: <input type="text" name="name"><br />
		Subject: <input type="text" name="subject"><br />
		Body:<br /><textarea name="body" rows=\'10\' cols=\'70\'></textarea>
		<br /><input type="submit" value="Send">
		</form>';
	}
}
else
{
	//if the user didn't fill in the form, display the form
	echo '<h3>Email Me</h3>
	<form method="post" action="' . $_SERVER["PHP_SELF"] . '">
	Name: <input type="text" name="name"><br />
	Subject: <input type="text" name="subject"><br />
	Body:<br /><textarea name="body" rows=\'10\' cols=\'70\'></textarea>
	<br /><input type="submit" value="Send">
	</form>';
}
?>
</body></html>
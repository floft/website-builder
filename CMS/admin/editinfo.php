<?php
require_once "design.php";
site_header("My Account");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

$query2 = "Select * from `users` where loginId = 1 and siteId = \"$site_id\"";
$result2 = mysql_query($query2) or die('select failed!');

$name = mysql_result($result2, 0, "name");
$email = mysql_result($result2, 0, "email");
$username = mysql_result($result2, 0, "username");

echo '<font class="c595">My Account</font><br />';

if (!empty($_POST['password']))
{
	if(!empty($_POST['username']))
	{
		$newusername = $_POST['username'];
		$query = "Update `users` SET username = '$newusername' where loginId = 1 and siteId = \"$site_id\"";
		$result = mysql_query($query) or die('update failed!');

		echo 'Username updated sucessfully!<br />';
	}
	if (!empty($_POST['newpassword']) && !empty($_POST['vpassword']))
	{
		$vpassword = $_POST['vpassword'];
		$newpassword = $_POST['newpassword'];

		if ($vpassword == $newpassword)
		{
			$query = "Update `users` SET password = PASSWORD('$newpassword') where loginId = 1 and siteId = \"$site_id\"";
			$result = mysql_query($query) or die('update failed!');

			echo 'Password updated sucessfully!<br />';
		}
		else
		{
			echo 'Your New Password and the Verify Password were not the same!<br />';

		}
	}
	if ($name != $_POST['name'])
	{
		$newname = $_POST['name'];
		$query = "Update `users` SET name = '$newname' where loginId = 1 and siteId = \"$site_id\"";
		$result = mysql_query($query) or die('update failed!');

		echo 'Name updated sucessfully!<br />';
	}
	if ($email != $_POST['email'])
	{
		$newemail = $_POST['email'];
		if (check_email_address($newemail))
		{
			$query = "Update `users` SET email = '$newemail' where loginId = 1 and siteId = \"$site_id\"";
			$result = mysql_query($query) or die('update failed!');

			echo 'Email updated sucessfully!<br />';
		}
		else
		{
			echo 'Invalid Email!<br />';
		}
	}

	$query2 = "Select * from `users` where loginId = 1 and siteId = \"$site_id\"";
	$result2 = mysql_query($query2) or die('update failed!');

	$name = mysql_result($result2, 0, "name");
	$email = mysql_result($result2, 0, "email");
	$username = mysql_result($result2, 0, "username");

	echo '<form method="post" action="editinfo.php">
	<b>Name:</b><br /><input type="text" name="name" value="' . $name . '" />
	<br /><b>Email:</b><br /><input type="text" name="email" value="' . $email . '" />
	<br /><b>Username:</b><br /><input type="text" name="username" />
	<br /><b>New Password:</b><br /><input type="password" name="newpassword" />
	<br /><b>Verify Password:</b><br /><input type="password" name="vpassword" />
	<br /><b>Old Password:</b> Required to change anything.<br /><input type="password" name="password" />
	<br /><input type="submit" value="Save">
	</form>';
}
else
{
	if (isset($_REQUEST['name']))
	{
		echo "Please enter \"Old Password\"...<br />";
	}

	echo '<form method="post" action="editinfo.php">
	<b>Name:</b><br /><input type="text" name="name" value="' . $name . '" />
	<br /><b>Email:</b><br /><input type="text" name="email" value="' . $email . '" />
	<br /><b>Username:</b><br /><input type="text" name="username" />
	<br /><b>New Password:</b><br /><input type="password" name="newpassword" />
	<br /><b>Verify Password:</b><br /><input type="password" name="vpassword" />
	<br /><b>Old Password:</b> Required to change anything.<br /><input type="password" name="password" />
	<br /><input type="submit" value="Save">
	</form>';
}
mysql_close();

site_footer();
?>
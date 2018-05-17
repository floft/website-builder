<?php
//check if the user is logged in
if (isset($_SESSION['member']))
{
	//get the user's loginId from the session
	$loginid = $_SESSION['loginId'];

	//select the user's information from the database
	$query2 = "Select * from `users` where loginId = " . $loginid . " and siteId = '$site_id'";
	$result2 = mysql_query($query2) or die('select failed!');

	//get the user's information from the database
	$name = mysql_result($result2, 0, "name");
	$email = mysql_result($result2, 0, "email");
	$username = mysql_result($result2, 0, "username");
	$text = mysql_result($result2, 0, "profileText");
	$hobbies = mysql_result($result2, 0, "hobbies");

	$contents = '<span>';
	//if the user inputed their password
	if (!empty($_POST['realpassword']))
	{
		//if the user inputed a new username
		if(!empty($_POST['newusername']))
		{
			$newusername = $_POST['newusername'];

			$query = "Select * from `users` where username ='" . $newusername . "' and siteId = '$site_id'";
			$result = mysql_query($query) or die ('Username select failed!');
			$num = mysql_numrows($result);

			if ($newusername == $username)
			{
				$contents .= "The username you have chosen is the one you already have.<br />";
			}
			else if ($num >= 1)
			{
				$contents .= "he username you have chosen is already taken.<br />";
			}
			else
			{
				$query = "Update `users` SET username = '$newusername' where loginId = " . $loginid . " and siteId = '$site_id'";
				$result = mysql_query($query) or die('select failed!');
				$contents .= 'Username updated sucessfully!<br />';
			}
		}
		//if the user inputed a new password
		if (!empty($_POST['newpassword']) && !empty($_POST['vpassword']))
		{
			$vpassword = $_POST['vpassword'];
			$newpassword = $_POST['newpassword'];

			//check if the password and the verified password are the same
			if ($vpassword == $newpassword)
			{
				//if they are, update the password
				$query = "Update `users` SET password = PASSWORD('$newpassword') where loginId = " . $loginid . " and siteId = '$site_id'";
				$result = mysql_query($query) or die('select failed!');

				$contents .= 'Password updated sucessfully!<br />';
			}
			else
			{
				$contents .= 'Your New Password and the Verify Password were not the same!<br />';

			}
		}
		//if the there is a new name
		if ($name != $_POST['name'])
		{
			$newname = $_POST['name'];
			$query = "Update `users` SET name = '$newname' where loginId = " . $loginid . " and siteId = '$site_id'";
			$result = mysql_query($query) or die('select failed!');

			$contents .= 'Name updated sucessfully!<br />';
		}
		//if the there is new hobbies
		if ($hobbies != $_POST['hobbies'])
		{
			$newhobbies = $_POST['hobbies'];
			$query = "Update `users` SET hobbies = '$newhobbies' where loginId = " . $loginid . " and siteId = '$site_id'";
			$result = mysql_query($query) or die('select failed!');

			$contents .= 'Hobbies updated sucessfully!<br />';
		}
		//if the there is new text
		if ($text != $_POST['text'])
		{
			$newtext = $_POST['text'];
			$query = "Update `users` SET profileText = '$newtext' where loginId = " . $loginid . " and siteId = '$site_id'";
			$result = mysql_query($query) or die('select failed!');

			$contents .= 'Profile Text updated sucessfully!<br />';
		}
		//if the there is a new email
		if ($email != $_POST['email'])
		{
			$newemail = $_POST['email'];

			//check if the email is valid
			if (check_email_address($newemail))
			{
				//if it is update the email
				$query = "Update `users` SET email = '$newemail' where loginId = " . $loginid . " and siteId = '$site_id'";
				$result = mysql_query($query) or die('select failed!');

				$contents .= 'Email updated sucessfully!<br />';
			}
			else
			{
				$contents .= 'Invalid Email!<br />';
			}
		}

		//select the new info in the database
		$query2 = "Select * from `users` where loginId = " . $loginid . " and siteId = '$site_id'";
		$result2 = mysql_query($query2) or die('select failed!');

		$name = mysql_result($result2, 0, "name");
		$email = mysql_result($result2, 0, "email");
		$username = mysql_result($result2, 0, "username");
		$text = mysql_result($result2, 0, "profileText");
		$hobbies = mysql_result($result2, 0, "hobbies");
	}
	else
	{
		$contents .= '<h2>Edit your Info</h2>';
	}

	//if the user isn't the administrator, display the "Delete Account" link
	//if the user is the administrator, display the "Administration" link
	if ($loginid == 1)
	{
		$contents .= '<br /><a href="http://' . $address . '/admin/"  target="_blank">Administration</a><br /><br />';
	}
	else
	{
		if ($databasedata['prettyurls']==1) $contents .= '<br /><a href="deleteaccount">Delete Account</a><br /><br />';
		else $contents .= '<br /><a href="index.php?id=deleteaccount">Delete Account</a><br /><br />';
	}

	//display the form
	if ($databasedata['prettyurls']==1)
	{
		$contents .= '<a href="profile?user=' . $username . '" target="_blank">View Profile</a><br />
		<a href="members" target="_blank">View Members</a><br />
		<form method="post" action="account">
		<b>Name:</b><br /><input type="text" name="name" value="' . $name . '" />
		<br /><b>Email:</b><br /><input type="text" name="email" value="' . $email . '" />
		<br /><b>Username:</b><br /><input type="text" name="newusername" />
		<br /><b>New Password:</b><br /><input type="password" name="newpassword" />
		<br /><b>Verify Password:</b><br /><input type="password" name="vpassword" />
		<br /><b>Old Password:</b> Required to change anything.<br /><input type="password" name="realpassword" />
		<br /><input type="submit" value="Save"><br />
		<br /><b>Profile:</b>
		<blockquote>
			<b>Hobbies:</b><br /><input type="text" size="64"  name="hobbies" value="' . $hobbies . '" /><br />
			<br /><b>Text:</b><br /><textarea name="text" rows="5" cols="50">' . $text . '</textarea>
		</blockquote>
		<br /><input type="submit" value="Save">
		</form></span>';
	}
	else
	{
		$contents .= '<a href="index.php?id=profile&amp;user=' . $username . '" target="_blank">View Profile</a><br />
		<a href="' . (($databasedata['prettyurls']==1)?"members":"index.php?id=members") . '" target="_blank">View Members</a><br />
		<form method="post" action="index.php?id=account">
		<b>Name:</b><br /><input type="text" name="name" value="' . $name . '" />
		<br /><b>Email:</b><br /><input type="text" name="email" value="' . $email . '" />
		<br /><b>Username:</b><br /><input type="text" name="newusername" />
		<br /><b>New Password:</b><br /><input type="password" name="newpassword" />
		<br /><b>Verify Password:</b><br /><input type="password" name="vpassword" />
		<br /><b>Old Password:</b> Required to change anything.<br /><input type="password" name="realpassword" />
		<br /><input type="submit" value="Save"><br />
		<br /><b>Profile:</b>
		<blockquote>
			<b>Hobbies:</b><br /><input type="text" size="64"  name="hobbies" value="' . $hobbies . '" /><br />
			<br /><b>Text:</b><br /><textarea name="text" rows="5" cols="50">' . $text . '</textarea>
		</blockquote>
		<br /><input type="submit" value="Save">
		</form></span>';
	}
}
?>
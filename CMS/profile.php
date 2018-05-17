<?php
if (isset($_SESSION['member']))
{
	//check to see if a user's username has been entered
	if (isset($_REQUEST["user"]))
	{
		//since a user's username has been entered, add their info to the $contents variable

		//select their info from the database
		$query = "Select hobbies, profileText, username from `users` where username = '" . $_REQUEST['user'] . "' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Connect failed!');
		$num = mysql_numrows($result);

		if ($num==0)
		{
			$contents = "The user does not exist...";
		}
		else
		{
			//get their info from the database
			$hobbies = mysql_result($result,0,"hobbies");
			$text = mysql_result($result,0,"profileText");
			$username = mysql_result($result,0,"username");
			$num=mysql_numrows($result);

			//capitalize the first leter of their name
			$name=ucfirst($name);

			//the $something variable is created so if the user doesn't have anything in their profile
			//the text outputted says that they don't have anything in their profile
			$something = false;
			$contents = null;

			//if there is a "s" at the end of their username, make the text say username' instead of username's
			if (eregi('s$', $username))
			{
				$contents .= '<h4>' . $username . '\' Profile</h4>';
				$title = $username . '\' Profile';
			}
			else
			{
				$contents .= '<h4>' . $username . '\'s Profile</h4>';
				$title = $username . '\'s Profile';
			}

			//if there are any hobbies, output them
			if ($hobbies != null)
			{
				$contents .= 'Hobbies: ' . $hobbies . '<br />';
				$something = true;
			}

			//if there is any text, output it
			if ($text != null)
			{
				$contents .= '<br />' . $text;
				$something = true;
			}

			//if there isn't anything in there profile, say that there isn't
			if ($something != true)
			{
				$contents .= '<br />' . $username . ' doesn\'t have anything in their profile.';
			}
		}
	}
	else
	{
		//if a username hasn't been entered, allow them to do so
		$title = 'User Profile';
		if ($databasedata['prettyurls']==1) $contents = '<form name="login" method="get" action="profile">Username:<input type="text" name="user" /><input type="submit" value="Look Up" /></form>';
		else $contents = '<form name="login" method="get" action="index.php"><input type="hidden" name="id" value="profile" />Username:<input type="text" name="user" /><input type="submit" value="Look Up" /></form>';
	}
}
?>
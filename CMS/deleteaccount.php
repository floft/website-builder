<?php
if ($_REQUEST['delete'] == 'confirm')
{
	//check if the user is the administrator
	if($_SESSION['loginId'] != 1)
	{
		//select from the database to see if the user is a member
		$query = "Select member from `users` where loginId =" . $_SESSION['loginId'] . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Connection failed!');
		$member = mysql_result($result,0,"member");

		//check if the user is a member
		if ($member == 1)
		{
			// delete the user's account
			$query = "Delete from `users` Where loginId =" . $_SESSION['loginId'] . " and siteId = \"$site_id\"";
			mysql_query($query) or die ('Delete account failed!');

			//log them out
			unset($_SESSION['member']);

			//tell them their account is deleted
			$contents = 'Your Account is now deleted!';
		}
	}
	else
	{
		//tell the administrator that their account can't be deleted because they are an administrator
		$contents = 'You can\'t delete your account. You are an administrator.';
	}
}
else
{
	//check if the user is the administrator
	if($_SESSION['loginId'] != 1)
	{
		//if the user isn't an administrator, ask the user if they reall want to delete their account
		if ($databasedata['prettyurls']==1)$contents = 'Are you sure you want to delete your account?<br />
		<button onclick="window.location.href=\'deleteaccount?delete=confirm\'">Yes</button><br />
		<button onclick="window.location.href=\'' . $_SERVER['HTTP_REFERER'] . '\'">No</button>';
		else $contents = 'Are you sure you want to delete your account?<br />
		<button onclick="window.location.href=\'index.php?id=deleteaccount&delete=confirm\'">Yes</button><br />
		<button onclick="window.location.href=\'' . $_SERVER['HTTP_REFERER'] . '\'">No</button>';
	}
	else
	{
		//tell the administrator that they can't delete their account because they are the administrator
		$contents = 'You can\'t delete your account. You are an administrator.';
	}
}
?>
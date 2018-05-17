<?php
//get the inputed username and password
$username = $_REQUEST['username'];
$password = $_REQUEST['password'];

//return how many users have that username and password
$query = "Select loginId from `users` where member = 1 and username = '$username' and password = PASSWORD('$password') and siteId = '$site_id'";
$result = mysql_query($query) or die ('Getting data from database failed!');
$num = mysql_numrows($result);

//check how many users have the inputed username and password
if ($num > 0)
{
	//since there is a user with the inputed username and password, log them in.
	$_SESSION['member'] = 't';
	//create a session that contains their loginId
	$_SESSION['loginId'] = mysql_result($result, 0, 'loginId');
	$contents = 'You are now logged in.';
}
else
{
	//if there is not a user with the inputed username and password output the login form
	if (!isset($_SESSION['loginId']) || !isset($_SESSION['member']))
	{
		if (isset($_REQUEST['username']) && isset($_REQUEST['password']))
		{
			$message = "<b>Wrong username or password.</b><br />";
		} else $message = $databasedata['login_code'];

		$contents = $message . '<form name="login" method="post" action="' . (($databasedata['prettyurls'] == 1)?"login":"index.php?id=login") . '"><input type="hidden" name="http_referer" value="' . GetAddressVarIf('http_referer', $_SERVER["HTTP_REFERER"]) . '" />Username:<br /><input type="text" name="username" /><br />Password:<br /><input type="password" name="password" /><br /><input type="submit" value="Login" /></form>';
	}
}
?>
<?php
function logout()
{
	unset($_SESSION['LoggedIn']);
}

//create a function to check if a email is valid
function check_email_address($email)
{
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email))
	{
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}

	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);

	for ($i = 0; $i < sizeof($local_array); $i++)
	{
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]))
		{
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1]))
	{
		// Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);

		if (sizeof($domain_array) < 2)
		{
		return false;
		// Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++)
		{
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i]))
			{
				return false;
			}
		}
	}
	return true;
}

function site_header($title="Floft Website Builder",$loginMenuItems=false)
{
global $databasedata;

echo <<< END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>$title - Floft Website Builder</title><link rel="stylesheet" type="text/css" href="index.css"><script src="floft.js" type="text/javascript"></script></head>
<body class="c1"><a name="top"></a>
<div class="header">
<div class="pagepic"><a href="dashboard.php"><img src="head.gif" width="166px" alt="Floft" class="picnb"></a></div>
<div class="pagetitle"><span>Floft</span><font>Floft</font></div>
<div class="subtitle"><span>Floft Website Builder</span><font>Floft Website Builder</font></div></div><br />
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="mainsection">
<tr valign="top"><td valign="top"><div class="mt"><table width="100%"><tr><td rowspan="1" valign="top">
END;
}

function site_footer()
{
	echo <<< END
	</td>
</tr></table></div></td></tr><tr><td colspan="3">
	<div class="footer1">
		<span class="pic3"><img src="http://www.floft.net/favicon.ico" width="16px" alt="Floft"></span>
		<span class="footer">Floft Website Builder</span>
	</div></td>
</tr></table><a name="bottom"></a></body></html>
END;
}

session_start();

//require "variables.php" in the directory above "admin" that contains variables
require "../variables.php";

//conect to the database
$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

//get settings
$query = "Select * from `settings` where siteId = \"$site_id\"";
$result = mysql_query($query);// or die ('Getting data from database failed!');
$num = mysql_numrows($result);

//we loop through the settings adding them to the $databasedata array
for ($i = 0; $i < $num; $i++)
{
	$name=mysql_result($result,$i,"name");
	$value=mysql_result($result,$i,"value");

	$databasedata[$name] = $value;
}

//if the tables don't exits, create them
if (!mysql_query("Select * from `settings`"))
{
	function parse_mysql_dump($url) {
		$handle = @fopen($url, "r");
		$query = "";
		while(!feof($handle)) {
			$sql_line = fgets($handle);
			if (trim($sql_line) != "" && strpos($sql_line, "--") === false) {
				$query .= $sql_line;
				if (preg_match("/;[\040]*\$/", $sql_line)) {
					$result = mysql_query($query) or die('You didn\'t add the tables into the database. Please do so. Error Message: ' . mysql_error());
					$query = "";
				}
			}
		}
	}

	//create tables in the new database
	$filename = "../docs/database.sql";
	parse_mysql_dump($filename);
}

if (!isset($_SESSION['LoggedIn']))
{
	//get how many users there are
	$query = "Select * from `users` where siteId = '$site_id'";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	$users = mysql_numrows($result);

	//check if there are any users
	//if there are no users, allow the current user to create the administrator
	if ($users < 1)
	{
		if (!isset($site_id)) $site_id = 0;

		//get settings
		$query = "Select * from `settings` where siteId = $site_id";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$num = mysql_numrows($result);

		//we loop through the settings adding them to the $databasedata array
		for ($i = 0; $i < $num; $i++)
		{
			$name=mysql_result($result,$i,"name");
			$value=mysql_result($result,$i,"value");

			$databasedata[$name] = $value;
		}

		//check if the administrator has filled in the form
		if (!empty($_REQUEST['username2']) && !empty($_REQUEST['password2']) && !empty($_REQUEST['email2']) && !empty($_REQUEST['name2']))
		{
			//get the info the administrator entered
			$username2 = $_REQUEST['username2'];
			$password2 = $_REQUEST['password2'];
			$email2 = $_REQUEST['email2'];
			$name2 = $_REQUEST['name2'];

			//check if the email is valid
			if (check_email_address($email2))
			{
				//since the email is valid, insert the administrators infomation into the users table
				$query = "Insert into `users` (siteId, loginId, username, password, name, email, member) VALUES ($site_id, 1, '$username2', PASSWORD('$password2'), '$name2', '$email2', 1)";
				mysql_query($query) or die('Insert failed!');

				//link to homepage
				$query = "INSERT INTO `links` (`siteId`, `linkId`, `pageId`, `membersPage`) VALUES ('$site_id', '0', '1', 0);";
				mysql_query($query) or die('Insert failed!');

				//insert homepage
				$query = "INSERT INTO `pages` (`siteId`, `pageId`, `pageURL`, `design`, `pageName`, `pageContents`, `bodyextra`, `membersPage`, `meta-keywords`, `meta-description`, `last-updated`) VALUES ('$site_id', '1', '1', 1, 'Home', 'This is your homepage...', '', 0, '', '', 'never');";
				mysql_query($query) or die('Insert failed!');

				//insert settings
				$query = "INSERT INTO `settings` (`siteId`, `name`, `value`) VALUES ('$site_id', 'login', '1'), ('$site_id', 'footer', 'Footer goes here...'), ('$site_id', 'search', '1'), ('$site_id', 'version', '2.0_installer'), ('$site_id', 'theme', 'default'), ('$site_id', 'createaccount_text', 'To create an account please fill in your Name/Nickname, the Username you want, the Password you want, and your Email address. I will send you an email to your Email Address that you listed when your account has been activated.'), ('$site_id', '404error_title', 'Page Not Found'), ('$site_id', '404error', '<font size=\"5\"><b>Sorry</b></font><br />The page you have tried to access does not exist.'), ('$site_id', 'login_code', '<b>Please Login</b><br />'), ('$site_id', 'loginform', '1'), ('$site_id', 'prettyurls', '0'), ('$site_id', 'editHTML', '0');";
				mysql_query($query) or die('Insert failed!');

				//insert stat page stuff
				$query = "INSERT INTO `stat` (`siteId`, `pageId`, `ip`, `browser`, `date`) VALUES ('$site_id', '1', '', '', ''), ('$site_id', '404error', '', '', '');";
				mysql_query($query) or die('Insert failed!');
			}
			else
			{
				//create a variable that says that the email wasn't valid
				$emailinvalid = true;
			}

			//echo the page
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				  <head>
					<title>Floft Website Builder</title>
					<link rel="stylesheet" type="text/css" href="index.css">
					<script src="floft.js" type="text/javascript"></script>
				  </head>
				  <body lang="en-US" dir="ltr" class="c1"><a name="top"></a>
					<div class="header">
					  <div class="pagepic">
						<a href="dashboard.php"><img src="head.gif" width="166px" alt="Floft" class="picnb"></a>
					  </div>
					  <div class="pagetitle">
						<span>Floft</span><font>Floft</font>
					  </div>
					  <div class="subtitle">
						<span>Floft Website Builder</span><font>Floft Website Builder</font>
					  </div>
					</div><br />
					<table width="100%" border="0" cellpadding="2" cellspacing="0" class="mainsection">
					  <tr valign="top">
						<td valign="top">
						<div class="mt">
								<table>
								  <tr>
									<td rowspan="1" valign="top">';

									//check if the email was valid
									if ($emailinvalid != true)
									{
										//if the email was valid, tell the administrator that their account has been created
										//then display the login form
										echo '<p class="c597">Your account has been created!</p>
										<p class="c597">Please login</p>
										<form name="login" method="post" action="dashboard.php">
										Username: <input type="text" name="username" /><br />
										Password: <input type="password" name="password" /><br />
										<input type="submit" value="Login" /></form>';
									}
									else
									{
										//if the email is not valid, tell them that it was invalid
										echo '<p class="c597">Invalid email!</p>Click <a href="dashboard.php">Here</a> to go back.';
									}

							 echo '</td>
								  </tr>
								</table>
					</div>
					</td>
					   </tr>
					   <tr>
						<td colspan="3">
							<div class="footer1">
								<span class="pic3"><img src="http://www.floft.net/favicon.ico" width="16px" alt="Floft"></span>
								<span class="footer">Floft Website Builder</span>
							</div>
						</td>
					  </tr>
					</table>
				  <a name="bottom"></a></body>
				</html>';
		}
		else if (isset($_REQUEST['username2']))
		{
			//if only some of the information was filled out, not all of it, tell the user to fill in the whole form
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				  <head>
					<title>Floft Website Builder</title>
					<link rel="stylesheet" type="text/css" href="index.css">
					<script src="floft.js" type="text/javascript"></script>
				  </head>
				  <body lang="en-US" dir="ltr" class="c1"><a name="top"></a>
					<div class="header">
					  <div class="pagepic">
						<a href="dashboard.php"><img src="head.gif" width="166px" alt="Floft" class="picnb"></a>
					  </div>
					  <div class="pagetitle">
						<span>Floft</span><font>Floft</font>
					  </div>
					  <div class="subtitle">
						<span>Floft Website Builder</span><font>Floft Website Builder</font>
					  </div>
					</div><br />
					<table width="100%" border="0" cellpadding="2" cellspacing="0" class="mainsection">
					  <tr valign="top">
						<td valign="top">
						<div class="mt">
								<table>
							  <tr>
								<td rowspan="1" valign="top">
									<p class="c597">Please fill in the form!</p><font class="c599">Please input the username and password you want for your administration panel. Also input your name and email.</font>
									<form name="login" method="post" action="dashboard.php">
									Name:<br /><input type="text" name="name2" /><br />
									Email:<br /><input type="text" name="email2" /><br />
									Username:<br /><input type="text" name="username2" /><br />
									Password:<br /><input type="text" name="password2" /><br />
									<input type="submit" value="Login" /></form>
								</td>
							  </tr>
							</table>
					</div>
					</td>
					   </tr>
					   <tr>
						<td colspan="3">
							<div class="footer1">
								<span class="pic3"><img src="http://www.floft.net/favicon.ico" width="16px" alt="Floft"></span>
								<span class="footer">Floft Website Builder</span>
							</div>
						</td>
					  </tr>
					</table>
				  <a name="bottom"></a></body>
				</html>';
		}
		else
		{
			//tell the user to enter their information for the administrator
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			  <head>
				<title>Floft Website Builder</title>
				<link rel="stylesheet" type="text/css" href="index.css">
				<script src="floft.js" type="text/javascript"></script>
			  </head>
			  <body lang="en-US" dir="ltr" class="c1"><a name="top"></a>
				<div class="header">
				  <div class="pagepic">
					<a href="dashboard.php"><img src="head.gif" width="166px" alt="Floft" class="picnb"></a>
				  </div>
				  <div class="pagetitle">
					<span>Floft</span><font>Floft</font>
				  </div>
				  <div class="subtitle">
					<span>Floft Website Builder</span><font>Floft Website Builder</font>
				  </div>
				</div><br />
				<table width="100%" border="0" cellpadding="2" cellspacing="0" class="mainsection">
				  <tr valign="top">
					<td valign="top">
					<div class="mt">
							<table>
							  <tr>
								<td rowspan="1" valign="top">
									<p class="c597">Welcome!</p><font class="c599">Please input the username and password you want for your administration panel. Also input your name and email.</font>
									<form name="login" method="post" action="dashboard.php">
									Name:<br /><input type="text" name="name2" /><br />
									Email:<br /><input type="text" name="email2" /><br />
									Username:<br /><input type="text" name="username2" /><br />
									Password:<br /><input type="text" name="password2" /><br />
									<input type="submit" value="Login" /></form>
								</td>
							  </tr>
							</table>
				</div>
				</td>
				   </tr>
				   <tr>
					<td colspan="3">
						<div class="footer1">
							<span class="pic3"><img src="http://www.floft.net/favicon.ico" width="16px" alt="Floft"></span>
							<span class="footer">Floft Website Builder</span>
						</div>
					</td>
				  </tr>
				</table>
			  <a name="bottom"></a></body>
			</html>';
		}

		mysql_close();

		//exit before any page gets executed...since user ain't logged in
		exit;
	}
	else
	{
		//since there is already an administrator, check to see if the user has submitted the username and password
		if (isset($_REQUEST['username']) && isset($_REQUEST['password']))
		{
			//if the user submitted their username and password, check to see if it is the correct info

			//get their inputed username and password
			$username = $_REQUEST['username'];
			$password = $_REQUEST['password'];

			//check to see if the username and password match the administrator username and password
			//the password is encrypted, so the PASSWORD() function converts the inputed password to a encrypted password to see
			//if it matches the administrators password
			$query = "Select loginId, siteId from `users` where username = '$username' and password = PASSWORD('$password') and loginId = 1";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			//say how many users had that username and password
			$num = mysql_numrows($result);

			//check if the username and password matched
			if ($num > 0)
			{
				//if the username and password matched create a session saying
				//that the user has logged in to the administration panel
				$_SESSION['LoggedIn'] = mysql_result($result,0,"siteId");
			}
			else
			{
				//since the username and password didn't match, don't
				//allow the user to login to the administration panel

				//echo the login form that says wrong username and/or password
				echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				  <head>
					<title>Floft Website Builder</title>
					<link rel="stylesheet" type="text/css" href="index.css">
					<script src="main.js" type="text/javascript"></script>
					<script src="floft.js" type="text/javascript"></script>
				  </head>
				  <body lang="en-US" dir="ltr" class="c1"><a name="top"></a>
					<div class="header">
					  <div class="pagepic">
						<a href="dashboard.php"><img src="head.gif" width="166px" alt="Floft" class="picnb"></a>
					  </div>
					  <div class="pagetitle">
						<span>Floft</span><font>Floft</font>
					  </div>
					  <div class="subtitle">
						<span>Floft Website Builder</span><font>Floft Website Builder</font>
					  </div>
					</div><br />
					<table width="100%" border="0" cellpadding="2" cellspacing="0" class="mainsection">
					  <tr valign="top">
						<td valign="top">
						<div class="mt">
								<table>
								  <tr>
									<td rowspan="1" valign="top">
										<p class="c597">Wrong Username or Password</p>
										<form name="login" method="post" action="dashboard.php">
										Username: <input type="text" name="username" /><br />
										Password: <input type="password" name="password" /><br />
										<input type="submit" value="Login" /></form>
									</td>
								  </tr>
								</table>
					</div>
					</td>
					   </tr>
					   <tr>
						<td colspan="3">
							<div class="footer1">
								<span class="pic3"><img src="http://www.floft.net/favicon.ico" width="16px" alt="Floft"></span>
								<span class="footer">Floft Website Builder</span>
							</div>
						</td>
					  </tr>
					</table>
				  <a name="bottom"></a></body>
				</html>';

				mysql_close();

				//exit before any page gets executed...since user ain't logged in
				exit;
			}
		}
		else
		{
			//display the login form
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			  <head>
				<title>Floft Website Builder</title>
				<link rel="stylesheet" type="text/css" href="index.css">
				<script src="main.js" type="text/javascript"></script>
				<script src="floft.js" type="text/javascript"></script>
			  </head>
			  <body lang="en-US" dir="ltr" class="c1"><a name="top"></a>
				<div class="header">
				  <div class="pagepic">
					<a href="dashboard.php"><img src="head.gif" width="166px" alt="Floft" class="picnb"></a>
				  </div>
				  <div class="pagetitle">
					<span>Floft</span><font>Floft</font>
				  </div>
				  <div class="subtitle">
					<span>Floft Website Builder</span><font>Floft Website Builder</font>
				  </div>
				</div><br />
				<table width="100%" border="0" cellpadding="2" cellspacing="0" class="mainsection">
				  <tr valign="top">
					<td valign="top">
					<div class="mt">
							<table>
							  <tr>
								<td rowspan="1" valign="top">
									<p class="c597">Please login</p>
									<form name="login" method="post" action="dashboard.php">
									Username: <input type="text" name="username" /><br />
									Password: <input type="password" name="password" /><br />
									<input type="submit" value="Login" /></form>
								</td>
							  </tr>
							</table>
				</div>
				</td>
				   </tr>
				   <tr>
					<td colspan="3">
						<div class="footer1">
							<span class="pic3"><img src="http://www.floft.net/favicon.ico" width="16px" alt="Floft"></span>
							<span class="footer">Floft Website Builder</span>
						</div>
					</td>
				  </tr>
				</table>
			  <a name="bottom"></a></body>
			</html>';

			mysql_close();

			//exit before any page gets executed...since user ain't logged in
			exit;
		}
	}
}
else
{
	//$site_id = $_SESSION['LoggedIn'];
}

mysql_close();
?>
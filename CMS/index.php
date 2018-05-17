<?php
require_once "design.php";

//by default display the design
$design = 1;
//default pageId is 1
$page_id = 1;

//connect to the database
$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

//if on anypage someone is trying to login, allow them to
if (isset($_POST['username']) && isset($_POST['password'])) require "login.php";

if ($databasedata['prettyurls']==1)
{
	$address_path = explode("/",$address,2);
	$requested_url = (count($address_path)>1)?str_replace("/" . $address_path[1],"",$HTTP_SERVER_VARS["REQUEST_URI"]):$HTTP_SERVER_VARS["REQUEST_URI"];
	$request1 = explode("/",$requested_url);
	if ($request[0] != null) $request2 = explode("?",$request1[0]);
	else $request2 = explode("?",$request1[1]);
	$id = $request2[0];

	$is_id = 1;
}
else
{
	$is_id = (isset($_REQUEST['id']))?true:false;

	if ($is_id) $id = $_REQUEST['id'];
	else $id = null;
}

function come_already($ip, $browser, $IPs, $Browsers) {
	$come = false;
	$count = count($IPs);

	for($i=0;$i<$count;$i++)
	{
		if ($ip == $IPs[$i] && $browser == $Browsers[$i])
		{
			$come = true;
			break;
		}
	}

	return $come;
}
function update_stat($page_id) {
	global $site_id;

	//update counter/statistics
	$stat_result = mysql_query("Select ip,browser,date from `stat` where pageId = '$page_id' and siteId = '$site_id'");
	//get the ip and browser info from the database
	$ip = mysql_result($stat_result,0,"ip");
	$browser = mysql_result($stat_result,0,"browser");
	$date = mysql_result($stat_result,0,"date");

	//make an array of the different IP addresses and browser info things
	if ($ip != null && $browser != null && $date != null)
	{
		$IPs = explode("\n", $ip);
		$Browsers = explode("\n", $browser);
	}
	else
	{
		$IPs = array(null);
		$Browsers = array(null);
	}

	//if the user hasn't come already, add them to the database
	if (!come_already($_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT'],$IPs,$Browsers))
	{
		//update the ip and browser info
		$ip .= $_SERVER['REMOTE_ADDR'] . "\n";
		$browser .= $_SERVER['HTTP_USER_AGENT'] . "\n";
		$date .= time() . "\n";

		//update the database
		mysql_query("Update `stat` set ip = '$ip' where pageId = '$page_id' and siteId = '$site_id'");
		mysql_query("Update `stat` set browser = '$browser' where pageId = '$page_id' and siteId = '$site_id'");
		mysql_query("Update `stat` set date = '$date' where pageId = '$page_id' and siteId = '$site_id'");
	}
}
function login_form()
{
	global $address;
	global $databasedata;

	//tell the user to login
	if ($databasedata['prettyurls']==1)
	{
		$contents = $databasedata['login_code'];
		$contents .= '<form method="post" action="http://' . $address . '/login">Username:<br /><input type="text" name="username" /><br />Password:<br /><input type="password" name="password" /><br /><input type="submit" value="Login" /></form>';
	}
	else
	{
		if ($_SERVER["QUERY_STRING"] != null)
		{
			$contents = $databasedata['login_code'];
			$contents .= '<form method="post" action="http://' . $address . '/index.php?' . $_SERVER["QUERY_STRING"] . '">Username:<br /><input type="text" name="username" /><br />Password:<br /><input type="password" name="password" /><br /><input type="submit" value="Login" /></form>';
		}
		else
		{
			$contents = $databasedata['login_code'];
			$contents .= '<form method="post" action="http://' . $address . '/index.php">Username:<br /><input type="text" name="username" /><br />Password:<br /><input type="password" name="password" /><br /><input type="submit" value="Login" /></form>';
		}
	}

	return $contents;
}
//this is a function to request a variable (like $_GET['something']) and then if it doesn't exist, to return a specified value
function GetAddressVarIf($name, $elsename=null)
{
	global $id;
	global $databasedata;

	if ($databasedata['prettyurls']==1)
	{
		if ($name == "id")
		{
			if ($id != null) return $id;
			else return $elsename;
		}
		else
		{
			if(isset($_REQUEST[$name])) $name = $_REQUEST[$name];
			else $name = $elsename;
			return $name;
		}
	}
	else
	{
		if(isset($_REQUEST[$name])) $name = $_REQUEST[$name];
		else $name = $elsename;
		return $name;
	}
}

//create a function for checking if an email is valid
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

//get page
if ((($is_id && $id == 'search') || isset($_REQUEST['q'])) && $databasedata['search'] == 1)
{
	//this is to make the $contents variable whatever the search.php file was trying to echo or print
	ob_start();
	require "search.php";
	$contents = ob_get_contents();
	ob_end_clean();

	$title = 'Search';
	$bodyextra = null;
}
else if ($is_id && $id == 'login' && $databasedata['login'] == 1)
{
	//require the login page for if someone is trying to login by using the form on any page.
	require_once "login.php";

	//forward user to previous page if they logged in
	if(isset($_SESSION['member']) &&  isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != 'http://' . $address . '/index.php?id=logout' && $_SERVER['HTTP_REFERER'] != 'http://' . $address . '/logout' && $_SESSION['member'] == "t" && GetAddressVarIf('http_referer', '') != 'http://' . $address . '/index.php?id=logout' && GetAddressVarIf('http_referer', '') != 'http://' . $address . '/logout')
	{
		header('location: ' . GetAddressVarIf('http_referer', $_SERVER['HTTP_REFERER']));
		exit();
	}

	$title = 'Login';
}
else if ($is_id && $id == 'logout' && $databasedata['login'] == 1)
{
	//allow a user to logout
   	unset($_SESSION['member']);

	//tell the user they are logged out
	$contents = 'You are now logged out.';
	$title = 'Logout';
}
else if ($is_id && $id == 'account' && $databasedata['login'] == 1)
{
	//see if the user is logged in
   	if (isset($_SESSION['member']))
	{
		require_once "account.php";
	}
	else
	{
		$contents = login_form();
	}

	$title = 'My Account';
}
else if ($is_id && $id == 'loginform' && $databasedata['login'] == 1)
{
	$title = 'Login Form';
	$contents = login_form();
}
else if ($is_id && $id == 'activate' && $databasedata['login'] == 1)
{
	require_once "activate.php";
	$title = 'Account Activation';
}
else if ($is_id && $id == 'members' && $databasedata['login'] == 1)
{
	//check if the user is logged in
   	if (isset($_SESSION['member']))
	{
		//if the user is logged in allow them to view the members page
		require "members.php";
	}
	else
	{
		$contents = login_form();
	}

	$title = 'Member List';
}
else if ($is_id && $id == 'profile' && $databasedata['login'] == 1)
{
	//check if the user is logged in
   	if (isset($_SESSION['member']))
	{
		require_once "profile.php";
	}
	else
	{
		$contents = login_form();
		$title = 'User Profile';
	}
}
else if ($is_id && $id == 'deleteaccount' && $databasedata['login'] == 1)
{
	//check to see if the user is logged in
   	if (isset($_SESSION['member']))
	{
		//if the user is logged in, allow them to delete their account if they aren't an administrator
		require "deleteaccount.php";
	}
	else
	{
		$contents = login_form();
		$title = 'Delete Account';
	}
}
else if ($is_id && $id == 'create' && $databasedata['login'] == 1)
{
	//check to see if the user already has an account
	//if the user is an administrator, then allow them to view the page even though they have an account
   	if (!isset($_SESSION['member']) || (isset($_SESSION['loginId']) && $_SESSION['loginId'] == 1))
	{
		require_once "create.php";
	}
	else
	{
		//tell the user that since they have an account, they can't create a new one
		//if the user decided to logout, then they could create another account
		$contents = 'You already have an account!';
	}

	$title = 'Create an Account';
}
else
{
	//select the current page, and if it doesn't exist, display the error page
	if ($databasedata['prettyurls'] == 1)
	{
		$url = GetAddressVarIf('id', '1');
		if ($url == "Home") $url = 1;

		$query = "Select * from `pages` where pageURL = '" . $url . "' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$num = mysql_numrows($result);
	}
	else
	{
		$query = "Select * from `pages` where pageId = '" . GetAddressVarIf('id', '1') . "' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$num = mysql_numrows($result);
	}

	//check if the page exists
	if ($num != 0)
	{
		//get the title of the page
		$title = mysql_result($result,0,"pageName");
		$page_id = mysql_result($result,0,"pageId");
		$design = mysql_result($result,0,"design");
		$bodyextra = mysql_result($result,0,"bodyextra");

		//update stats...counter stuff
		update_stat($page_id);

		//see if the page is a members only page
		$membersonly = mysql_result($result,0,"membersPage");

		$keywords = @mysql_result($result,0,"metaKeywords");
		$description = @mysql_result($result,0,"metaDescription");

		$meta = null;
		if ($keywords != null) $meta .= "<meta name=\"keywords\" content=\"" . $keywords . "\">";
		if ($description != null) $meta .= "<meta name=\"description\" content=\"" . $description . "\">";

		//check if the page is a members only page
		if ($membersonly != 1)
		{
			//if the page isn't a members only page, make $contents (which is printed later) the contents of the page
			$contents = mysql_result($result,0,"pageContents");
		}
		else
		{
			//since the page is a members only pages, see if the user is logged in
			if (isset($_SESSION['member']))
			{
				//since the user is logged in, make $contents (which is printed later) the contents of the page
				$contents = mysql_result($result,0,"pageContents");
			}
			else
			{
				$contents = login_form();
			}
		}
	}
	else
	{
		//page not found
		header("HTTP/1.0 404 Not Found");

		//if the page doesn't exist, display the error page
		$contents = $databasedata['404error'];
		$title = $databasedata['404error_title'];

		//update stats...counter stuff
		update_stat('404error');
	}

	//since in the tinyMCE code when there is a picture,
	//they insert a "../" in front of the "uploads/".
	//this code removes that so the person can see the picture
	$contents = preg_replace('@\.\./uploads/@', 'uploads/', $contents);
}

//$member = 1 if the user is logged in, and if they arn't logged in, $member = 0.
if (isset($_SESSION['member'])) $member = 1;
else $member = 0;

//page url
if ($databasedata['prettyurls']==1)
{
	if ($is_id) $pageURL = $id;
	else $pageURL = "Home";
}
else $pageURL = "index.php?id=" . $id;

//output the page
echo page($pageURL, $title, $meta, $contents, $page_id, $design);

mysql_close();
?>
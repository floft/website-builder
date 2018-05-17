<?php
//create a function to get a variable that the user inputed
function get_address_var($name)
{
	if (isset($_REQUEST[$name])) $name = $_REQUEST[$name];
	else $name = null;
	return $name;
}
//create a function to return the create account form
function CreateAccountForm()
{
	//make several variables global
	global $address;
	global $database;
	global $host_addon;
	global $site_id;
	global $databasedata;

	//$return is the variable that will get returned so that later on it can get echoed
	$return = "<h4>Create an Account</h4>";

	//select the create account text that the administrator can change from the adminstration panel
	$query = 'Select value FROM `settings` where name = \'createaccount_text\' and siteId = "' . $site_id . '"';
	$result = mysql_query($query);
	$return .= mysql_result($result, 0, 'value');

	//display the form
	$return .= "<br /><br /><form method='post' action='" . (($databasedata['prettyurls']==1)?"create":"index.php?id=create") . "'>
	Name: <input name='name' type='text' value='" . get_address_var('name') . "' /><br />
	Email: <input name='email' type='text' value='" . get_address_var('email') . "' /><br />
	Username: <input name='newusername' type='text' value='" . get_address_var('newusername') . "' /><br />
	Password: <input name='newpassword' type='text' value='" . get_address_var('newpassword') . "' /> Password is case sensitive.<br />
	<input type=\"submit\" value = \"Submit\" /></form>";

	//return the $return variable that has the contains the form
	return $return;
}

//if the user hit submit
if ($_REQUEST['newusername'])
{
	//if the user inputed all the information
	if ($_REQUEST["name"] != "" && $_REQUEST["email"] != "" && $_REQUEST["newusername"] != "" && $_REQUEST["newpassword"] != "")
	{
		//get the information inputed
		$name = $_REQUEST["name"];
		$email = $_REQUEST["email"];
		$username = $_REQUEST["newusername"];
		$password = $_REQUEST["newpassword"];
		$rname = $_REQUEST["name"];
		$member = '0';
		$activateInfo = time() . '|';

		//select all the users that have the same username as the one inputed
		$query="SELECT username FROM `users` where username ='" . $username . "' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Get username failed!');
		$num=mysql_numrows($result);

		//check if any users have the same username as the one inputed
		if ($num <= 0)
		{
			//if not, check if the inputed email is valid and if the domain exists
			if (check_email_address($email) && checkdnsrr(substr(strstr($email, '@'), 1)))
			{
				//if so, create a variable called $ID that is the next loginId for the user
				$query="SELECT loginId FROM `users` where siteId = '$site_id'";
				$result=mysql_query($query);
				$num=mysql_numrows($result);
				$id=mysql_result($result,($num-1),"loginId");
				$ID = $id+1;

				//insert in the required information
				$query = "INSERT INTO `users` (`siteId`, `loginId`, `username`, `password`, `name`, `email`, `member`, `newpassword`, `ActivateInfo`) VALUES ('$site_id', '$ID', '$username', PASSWORD('$password'), '$name', '$email', '$member', '$password', '$activateInfo')";
				mysql_query($query) or die ('Insert failed!');

				//select the administrators email
				$query = 'Select email FROM `users` where loginId = 1 and siteId = "' . $site_id . '"';
				$result = mysql_query($query);
				$admin_email = mysql_result($result, 0, 'email');

				//create a messege that tells the administrator that a new user wants to join
				$message =  "The following information was entered in the form on your webpage \"" . $address . "\" (The form is on the Create an Account page):
Email: $email
Real Name: $rname
Requested Username: $username
Requested Password: $password";

				//email the administrator
				mail($admin_email, "Subject: Someone wants to Create an Account at \"" . $address . "\"" , $message, "From: $name");
				//$contents .= some information for the user
				$contents .= 'Your account infomation has been submited. Please wait till you recieve an email from the site administrator. It will contain instructions telling you how to activate your account.';
			}
			else
			{
				//tell the user that the inputed email address was invalid
				$contents .= 'Invalid Email Address.<br /><br />';
				//$contents .= the form
				$contents .= CreateAccountForm();
			}
		}
		else
		{
			//tell the user that the username they wanted was already taken
			$contents .= 'Username already taken.<br /><br />';
			//$contents .= the form
			$contents .= CreateAccountForm();
		}
	}
	else
	{
	//tell the user that they didn't fill in the whole form
	$contents .= 'You did <u>NOT</u> fill out all the infomation.<br /><br />';
	//$contents .= the form
	$contents .= CreateAccountForm();
	}
}
else
{
	//display the form
	$contents .= CreateAccountForm();
}
?>
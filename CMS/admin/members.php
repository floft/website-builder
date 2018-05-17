<?php
require_once "design.php";
site_header("Members");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

function GetUsers()
{
	global $site_id;
	global $host_addon;
	global $database;
	global $address;

	$query = 'Select * From `users` where siteId = "' . $site_id . '"';
	$result = mysql_query($query);
	$num = mysql_numrows($result);

	for ($i = 0; $i < $num; $i++)
	{
		$name = mysql_result($result,$i,"name");
		$email = mysql_result($result,$i,"email");
		$member = mysql_result($result,$i,"member");
		$loginId = mysql_result($result,$i,"loginId");
		$username = mysql_result($result,$i,"username");

		$users[$i]['name'] = $name;
		$users[$i]['email'] = $email;
		$users[$i]['member'] = $member;
		$users[$i]['loginId'] = $loginId;
		$users[$i]['username'] = $username;
	}
	return $users;
}
function ReturnUsers()
{
	$users = GetUsers();
	echo '<font class="c597">Members</font><br />';

	echo '<table bgcolor="#D2B48C" width="100%">
	<tr>
		<th>
			<div align="center">Username</div>
		</th>
		<th>
			<div align="center">Name</div>
		</th>
		<th>
			<div align="center">Email</div>
		</th>
		<th>
			<div align="center">Add User</div>
		</th>
		<th>
			<div align="center">Delete a User</div>
		</th>
	</tr>';
	for ($i=0;$i<count($users);$i++)
	{
		echo '<tr>
			<td bgcolor="#FFEBCD">
				' . $users[$i]['username'] . '
			</td>
			<td bgcolor="#FFEBCD">
				' . $users[$i]['name'] . '
			</td>
			<td bgcolor="#FFEBCD">
				' . $users[$i]['email'] . '
			</td>
			<td bgcolor="#FFEBCD" width="200px">';
			if ($users[$i]['member'] != 1)
			{
				echo '<a href="members.php?action=add&amp;user=' . $users[$i]['loginId'] . '" class="link23">Send Activation Email</a>';
			}
			else
			{
				echo '<font color="gray">Send Activation Email</font>';
			}
			echo '</td>
			<td bgcolor="#FFEBCD" width="125px">';
			if ($users[$i]['loginId'] != 1)
			{
				echo '<a href="members.php?delete&amp;user=' . $users[$i]['loginId'] . '" class="link23">Delete User</a>';
			}
			else
			{
				echo '<font color="gray">Delete User</font>';
			}
			echo '</td>
		</tr>';
	}
	echo '</table>';
}

if (isset($_REQUEST['action']) && $_REQUEST['user'] != 1 && $_REQUEST['user'] != '')
{
	$user = $_REQUEST['user'];
	$action = $_REQUEST['action'];

	if ($action == 'add')
	{
		$loginId = $user;
		$query = 'Select * From `users` where loginId = ' . $loginId . " and siteId = '$site_id'";
		$result = mysql_query($query);
		$member = mysql_result($result, 0, 'member');

		if ($member != 1)
		{
			$emailnew = mysql_result($result, 0, 'email');
			$usernamenew = mysql_result($result, 0, 'username');
			$passwordnew = mysql_result($result, 0, 'newpassword');

			//make it so that the user can login
			$query = "Update `users`  Set member = 2 Where loginId = " . $loginId . " and siteId = '$site_id'";
			mysql_query($query) or die ('Update Failed!');
			$query = "Update `users`  Set newpassword = '' Where loginId = " . $loginId . " and siteId = '$site_id'";
			mysql_query($query) or die ('Update failed!');
			$query = "Update `users`  Set ActivateInfo = '" . time() . "|' Where loginId = " . $loginId . " and siteId = '$site_id'";
			mysql_query($query) or die ('Update failed!');

			//the login url
			if ($databasedata['prettyurls']==1) $activateURL = "http://$address/activate?user=" . md5($loginId);
			else $activateURL = "http://$address/index.php?id=activate&user=" . md5($loginId);

			//send the new user an email
			$message = 'You have created an account at ' . $address . '! If you are not aware of doing this
please disregard this email. Please keep this email for your records. Your account
information is as follows:

-------------------------------
Username: ' . $usernamenew .  '
Password: ' . $passwordnew .  '
-------------------------------

Your account is currently inactive. You cannot use it until you visit the
following link:

' . $activateURL . '

Please do not forget your password, it has been encrypted in our database and
we cannot retrieve it for you. However, if you do forget your password you can
request a new one which will be activated in the same way as this account.

Thank you for registering. If you have any questions, please feel free to contact
me using the form on: http://' . $address . '/contact.php

--
Website Administrator
http://' . $address . '/';

			mail($emailnew, "Account Activation", $message, "From: noreply@$address");

			echo "The user has been sent an email telling them how to activate their account.<br /><br />";
		}
		else
		{
			echo "The user has already activated their account.<br /><br />";
		}
	}
	else if ($action == 'delete')
	{
		$loginId = $user;
		$query = 'Select * From `users` where loginId = ' . $loginId . " and siteId = '$site_id'";
		$result = mysql_query($query);
		$emailnew = mysql_result($result, 0, 'email');
		$usernamenew = mysql_result($result, 0, 'username');
		$member = mysql_result($result, 0, 'member');

		//delete the account
		$query = "Delete From `users` Where loginId = " . $loginId . " and siteId = '$site_id'";
		mysql_query($query) or die ('Delete Failed!');

		if ($member == 1)
		{
			//send the user an email
			$message = 'Your account at ' . $address . ' has been deleted. You will no longer be
able to login using the following account:

-------------------------------
Username: ' . $usernamenew .  '
Password: ********
-------------------------------

Thank you for registering. If you have any questions, please feel free to
contact me using the form on: http://' . $address . '/contact.php

--
Website Administrator
http://' . $address . '/';

		}
		else
		{
			//send the user an email
			$message = 'Your account at ' . $address . ' will not be activated. You will not be
able to login using the following account:

-------------------------------
Username: ' . $usernamenew .  '
Password: ********
-------------------------------

Thank you for registering. If you have any questions, please feel free to
contact me using the form on: http://' . $address . '/contact.php

--
Website Administrator
http://' . $address . '/';
		}
		mail($emailnew, "Account at " . $address, $message, "From: noreply@$address");

		echo "The users's account has been deleted and an email has been sent to them saying that they can no longer login to your website.<br /><br />";
	}

	echo ReturnUsers();
}
else if (isset($_REQUEST['delete']) && $_REQUEST['user'] != 1 && $_REQUEST['user'] != '')
{
	$delete = $_REQUEST['user'];
	$query = "Select username from `users` where loginId = '$delete' and siteId = \"$site_id\"";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	$num = mysql_numrows($result);

	if ($num > 0)
	{
		$name = mysql_result($result,0,"username");

		echo '<font class="c595">Delete a User</font><br />Are you sure you want to delete ' . $name . '? An email will be sent to them saying that they can\'t login anymore.<form method="post" action="members.php?action=delete&amp;user=' . $delete . '"><input type="submit" value="Yes" /> <input type="button" value="No" onclick="window.location.href=\'members.php\';return false;"></form>';
	}
	else
	{
		echo "User does not exist!";
	}
}
else
{
	echo ReturnUsers();
}

mysql_close();

site_footer();
?>
<?php
function getAddressVar($name)
{
	if (isset($_REQUEST[$name]) && $_REQUEST[$name] != null) return $_REQUEST[$name];
	else return false;
}

//this code gets the activation code (the hash value of the member id) and
//sees if it is in the database, if it isn't exit, if it is activate the
//account and create a session (logging the user in)
$query = "Select loginId, ActivateInfo from `users` where siteId = '$site_id'";
$result = mysql_query($query) or die ('Select Failed!');
$num = mysql_numrows($result);

$activateId = urldecode(getAddressVar('user'));
$correctUser = false;

//loop throught the users, checking to see if the id is correct
for ($i = 0; $i < $num; $i++)
{
	/************************************************************************
	There are two peices of data in the activateInfo var, it is an array:
	1) the time() the account was created (or, after a monitor adds them,
	   the time() that the monitor added them, this gives the user 30 days
	   to add the account, even if the monitors didn't add them for 4 years)
	2) the time() the account was activated
	The info is separated by the | symbol.
	************************************************************************/

	$memberId = mysql_result($result, $i, "loginId");
	$activateInfo = mysql_result($result, $i, "ActivateInfo");

	//the id is the md5() value of the memberId, see if it matches
	//the activation code
	if (md5($memberId) == $activateId)
	{
		$correctUser = true;
		$userId = $memberId;
		$activateInfo = explode('|', $activateInfo);
		break;
	}
}

//if user activated it, log them in
if ($correctUser && $activateInfo[0] != null && (!isset($activateInfo[1]) || $activateInfo[1] == null))
{
	//log in the user
	$_SESSION['member'] = 't';
	$_SESSION['loginId'] = $userId;

	//update database
	$query = "Update `users` set ActivateInfo = '" . $activateInfo[0] . "|" . time() . "' where loginId = '" . $userId . "' and siteId = '$site_id'";
	$result = mysql_query($query) or die ('Update Failed!');

	$query = "Update `users` set member = 1 where loginId = '" . $userId . "' and siteId = '$site_id'";
	$result = mysql_query($query) or die ('Update Failed!');

	$text = 'Your account has been activated!';
}
else if ($correctUser && $activateInfo[0] != null && $activateInfo[1] != null)
{
	//already activated
	$text = 'Your account has already been Activated.';
}
else
{
	//invalid id
	$text = 'Invalid ID!';
}

$contents = "<h2>Account Activation</h2>$text";
?>
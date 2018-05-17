<?php
if (isset($_POST['user']))
{
	if(setting_set("user", $_POST['user'])) echo "<i>The information has been updated.</i><br />";
	else echo "<i>An error occured while updating your information.</i><br />";

	setting_set("otherurl", $_POST['url']);
	setting_set("perpage", $_POST['perpage']);
}

$user = setting_get("user");
$url = setting_get("otherurl");
$perpage = setting_get("perpage");

echo '<form method="post" action="<url:current>">Type in your Twitter username below, or type in an alternate URL which has a cached copy of the Twitter XML file (the Twitter XML file: http://twitter.com/statuses/user_timeline/USERNAME.xml?count=10).<br /><br />Username:<br /><input type="text" name="user" value="' . htmlspecialchars($user) . '" /><br />Other URL:<br /><input type="text" name="url" value="' . htmlspecialchars($url) . '" /><br />Number of statuses (per page):<br /><input type="text" name="perpage" value="' . htmlspecialchars($perpage) . '" /><br /><input type="submit" value="Save"></form>';
?>
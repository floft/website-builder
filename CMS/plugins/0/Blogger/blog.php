<?php
if (isset($_POST['url']))
{
	if(setting_set("url", $_POST['url'])) echo "The blog URL has been updated. ";
	else echo "An error occured while updating your Blog feed URL. ";

	if(setting_set("url2", $_POST['url2'])) echo "The RSS Feed URL has been updated. ";
	else echo "An error occured while updating your RSS Feed link. ";

	if(setting_set("url3", $_POST['url3'])) echo "The Subscribe to by email URL has been updated.<br />";
	else echo "An error occured while updating your Subscribe to by email link.<br />";
}

$url = setting_get("url");
$url2 = setting_get("url2");
$url3 = setting_get("url3");

echo '<form method="post" action="<url:current>">Blogger feed url (e.g. http://example.blogspot.com/feeds/posts/default):<br /><input type="text" name="url" value="' . htmlspecialchars($url) . '" /><br />Blog feed url to link to (the RSS Feed link):<br /><input type="text" name="url2" value="' . htmlspecialchars($url2) . '" /><br />Subscribe to by email (a link):<br /><input type="text" name="url3" value="' . htmlspecialchars($url3) . '" /><br /><input type="submit" value="Save"></form>';
?>
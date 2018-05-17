<?php
//this file contains the functions and stuff so that the plugins can access the database and store settings
function setting_set($name, $value)
{
	global $plugin_file;
	global $site_id;

	//see if it exists
	$query = "Select name from `plugins` where plugin = '$plugin_file' and name = '$name' and siteId = '$site_id'";
	$result = mysql_query($query) or die("cow");
	$num = mysql_numrows($result);

	//if it exists, update it; if it doesn't exist, create it
	if ($num > 0)
	{
		$query = "Update `plugins` set value = '$value' where plugin = '$plugin_file' and name = '$name' and siteId = '$site_id'";
		$result = mysql_query($query) or die ('Update Failed!');
	}
	else
	{
		$query = "Insert into `plugins` (`siteId`, `plugin`, `name`, `value`) VALUES ('$site_id', '$plugin_file', '$name', '$value')";
		$result = mysql_query($query) or die ('Insert Failed!');
	}

	//see if it still exists
	$query = "Select name From `plugins` where plugin = '$plugin_file' and name = '$name' and siteId = '$site_id'";
	$result = mysql_query($query);
	$num = mysql_numrows($result);

	if ($num > 0) return true;
	else return false;
}

function setting_get($name)
{
	global $dbh;
	global $plugin_file;
	global $site_id;

	//select it
	$query = "Select value from `plugins` where plugin = \"$plugin_file\" and name = \"$name\" and siteId = \"$site_id\"";
	$result = mysql_query($query) or die("Select Failed!");
	$num=mysql_numrows($result);

	if ($num > 0) return mysql_result($result, 0, "value");
	else return false;
}

function setting_del($name)
{
	global $plugin_file;
	global $site_id;

	//delete it
	$query = "Delete from `plugins` where plugin = '$plugin_file' and name = '$name' and siteId = '$site_id'";
	$result = mysql_query($query) or die ('Delete Failed!');

	//see if it was deleted
	$query = "Select name From `plugins` where plugin = '$plugin_file' and name = '$name' and siteId = '$site_id'";
	$result = mysql_query($query);
	$num = mysql_num_rows($result);

	if ($num == 0) return true;
	else return false;
}
?>
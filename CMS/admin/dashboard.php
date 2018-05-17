<?php
require "design.php";
site_header("Dashboard");

echo '<font class="c595">The Dashboard</font><br />';
echo '<a href="http://' . $address . '" target="_blank">View Website</a><br /><a href="../docs/index.htm" target="_blank">View Documentation</a><br /><br /><font class="c599">';

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

//optimize all the tables
mysql_query('OPTIMIZE TABLE `links`');
mysql_query('OPTIMIZE TABLE `pages`');
mysql_query('OPTIMIZE TABLE `plugins`');
mysql_query('OPTIMIZE TABLE `questions`');
mysql_query('OPTIMIZE TABLE `settings`');
mysql_query('OPTIMIZE TABLE `stat`');
mysql_query('OPTIMIZE TABLE `users`');

//get the current version
$query = 'Select value From `settings` where name =\'version\'';
$results = mysql_query($query);
$version = mysql_result($results, 0, 'value');

//find out if there are any new version
$filename = "http://www.floft.net/fd/most_recent_updates.php";	$file=fopen($filename,"r"); $updates = fread($file, 100); fclose($file);

if ($updates != $version)
{
	//uncomment this line later
	echo 'There is a new version out. Click <a href="update.php" class="link23">Here</a> to update.<br />';
}
else
{
	echo 'There are no new updates.<br />';
}

mysql_close();
echo '</font>';

site_footer();
?>
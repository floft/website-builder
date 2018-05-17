<?php
require_once "design.php";
site_header("Delete Page");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

if (isset($_REQUEST['delete']))
{
	$delete = $_REQUEST['delete'];
	$memberspage = $_REQUEST['memberspage'];
	$query = "Select pageName from `pages` where membersPage = '$memberspage' and pageId = " . $delete . " and siteId = \"$site_id\"";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	$name = mysql_result($result,0,"pageName");

	echo '<font class="c595">Delete a Page</font><br />
	<font class="c599">Are you sure you want to delete the "' . $name . '" page?</font><form method="post" action="deletepage.php?delete2=' . $delete . '" id="deleteconfirm"><input type="hidden" name="option" value="yes" /><input type="submit" value="Yes" /> <input type="button" value="No" onclick="window.location.href=\'' . $_SERVER['HTTP_REFERER'] . '\';return false;"></form>';
}
else if (isset($_REQUEST['delete2']))
{
	if ($_REQUEST['delete2'] != 1)
	{
		$delete = $_REQUEST['delete2'];

		$query = "Delete from `pages` where pageId = " . $delete . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Deleting data from database failed!');

		$query = "Delete from `stat` where pageId = " . $delete . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Deleting data from database failed!');

		$query = "Delete from `links` where pageId = " . $delete . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Deleting data from database failed!');

		echo '<font class="c595">Delete a Page</font><br />The page has been Delete. Click <a href="deletepage.php">Here</a> to delete another page. <script type="text/javascript">window.top.location="deletepage.php";</script> ';
	}
	else
	{
		echo '<font class="c595">Delete a Page</font><br />The page has <b>NOT</b> been Deleted. You can\'t delete your home page. Click <a href="deletepage.php">Here</a> to delete another page.';
	}
}
else
{
	echo '<font class="c595">Delete a Page</font><br />';
	$query="SELECT pageId, pageName FROM `pages` where membersPage = 0 and siteId = \"$site_id\" order by pageName asc";
	$result=mysql_query($query);
	$num=mysql_numrows($result);

	// echo notes
	for ($i=0;$i < $num; $i++)
	{
		$Id=mysql_result($result,$i,"pageId");
		$Name=mysql_result($result,$i,"pageName");

		if ($Id == 1) echo '"' . $Name . '"&nbsp;&nbsp;<font color="gray">DELETE</font><br />';
		else echo '"' . $Name . '"&nbsp;&nbsp;<a href="deletepage.php?delete=' . $Id . '">DELETE</a><br />';
	}

	if ($databasedata['login'] == 1)
	{
		$query="SELECT pageId, pageName FROM `pages` where membersPage = 1 and siteId = \"$site_id\" order by pageName asc";
		$result=mysql_query($query);
		$num=mysql_numrows($result);
		if ($num > 0) echo '<br /><font class="c598"><b>Members Only Pages</b></font><br />';

		// echo notes
		for ($i=0;$i < $num; $i++)
		{
			$Id=mysql_result($result,$i,"pageId");
			$Name=mysql_result($result,$i,"pageName");

			if ($Id == 1) echo '"' . $Name . '"&nbsp;&nbsp;<font color="gray">DELETE</font><br />';
			else echo '"' . $Name . '"&nbsp;&nbsp;<a href="deletepage.php?memberspage=1&amp;delete=' . $Id . '">DELETE</a><br />';
		}
	}
}
mysql_close();

site_footer();
?>
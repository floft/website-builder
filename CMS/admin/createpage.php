<?php
require_once "design.php";
site_header("Create Page");

if (isset($_POST['name']) && $_POST['name'] != "")
{
	$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
	mysql_select_db ($host_addon . $database);

	$name = $_POST['name'];
	$query = "Select pageId from `pages` where siteId = \"$site_id\" order by pageId Asc";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	$num = mysql_numrows($result);
	if ($num > 0) $idnew = mysql_result($result, $num - 1, "pageId");
	else $idnew = null;

	if ($idnew != "") $idnew = $idnew + 1;
	else $idnew = 1;

	if (isset($_POST['members_yes_no']) && $_POST['members_yes_no'] == 1) $members_yes_no = 1;
	else $members_yes_no = 0;

	while (true)
	{
		$query = "Select pageId from `pages` where pageId = " . $idnew . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$num = mysql_numrows($result);
		if ($num > 0) $idnew = $idnew + 1;
		else break;
	}

	$query = "Insert into `pages` (siteId, pageId, pageURL, pageName, membersPage) VALUES ('$site_id', '$idnew', '$idnew', '$name', '$members_yes_no')";
	$result = mysql_query($query) or die ('Getting data into database failed!');

	$query = "Insert into `stat` (siteId, pageId) VALUES ('$site_id', '$idnew')";
	$result = mysql_query($query) or die ('Getting data into database failed!');

	$pageId = $idnew;

	if ($_POST['link_yes_no'] == 1)
	{
		if ($_POST['members_yes_no'] == 1)
		{
			$query = "Select linkId from `links` where membersPage = 1 and siteId = \"$site_id\"  order by linkId Asc";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);
			if ($num > 0) $idnew = mysql_result($result, $num - 1, "linkId");
			else $idnew = null;

			if ($idnew != "") $idnew = $idnew + 1;
			else $idnew = 0;

			$members_yes_no = $_POST['members_yes_no'];
			$query = "Insert into `links` (siteId, linkId, pageId, membersPage) VALUES ('$site_id', '$idnew', '$pageId', 1)";
			$result = mysql_query($query) or die ('Getting data into database failed!');
		}
		else
		{
			$query = "Select linkId from `links` where membersPage = 0 and siteId = \"$site_id\" order by linkId Asc";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);
			if ($num > 0) $idnew = mysql_result($result, $num - 1, "linkId");
			else $idnew = null;

			if ($idnew != "") $idnew = $idnew + 1;
			else $idnew = 0;

			$members_yes_no = $_POST['members_yes_no'];
			$query = "Insert into `links` (siteId, linkId, pageId) VALUES ('$site_id', '$idnew', '$pageId')";
			$result = mysql_query($query) or die ('Getting data into database failed!');
		}
	}

	mysql_close();

	if ($_POST['members_yes_no'] == 0)
	{
		echo '<font class="c595">Create a Page</font><br />The page has been created. Click <a href="editpage.php?edit=' . $pageId . '">Here</a> to edit the page.';
	}
	else
	{
		echo '<font class="c595">Create a Page</font><br />The page has been created. Click <a href="editpage.php?memberspage&amp;edit=' . $pageId . '">Here</a> to edit the page.';
	}
}
else
{
	echo '<font class="c595">Create a Page</font><br />
	<form method="post" action="createpage.php">
	Name of page: <input type="text" name="name" size="50">
	<br />Do you want a link to this page on the menu bar? <input type="radio" name="link_yes_no" value="1" checked="checked">Yes <input type="radio" name="link_yes_no" value="0"> No';
	if ($databasedata['login'] == 1)
	{
		echo '<br />Do you want this to be a members only page? <input type="radio" name="members_yes_no" value="1">Yes <input type="radio" name="members_yes_no" value="0" checked="checked"> No';
	}
	echo '<br /><br /><input type="submit" value="Create">
	</form>';
}

site_footer();
?>
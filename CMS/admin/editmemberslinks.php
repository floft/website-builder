<?php
require_once "design.php";
site_header("Edit Member Links");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

if (isset($_REQUEST['reset']))
{
	if ($_REQUEST['reset'] == "go")
	{
		//get links
		$query = "Select * from `links` where membersPage = 1 and siteId = \"$site_id\" Order by pageId Asc";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$num = mysql_numrows($result);

		for ($i=0; $i<$num; $i++)
		{
			$pageId = mysql_result($result,$i,"pageId");

			$reset_query = "Update `links` set linkId = '$i' where pageId = '$pageId' and membersPage = 1 and siteId = \"$site_id\"";
			mysql_query($reset_query) or die ('reseting links failed!');
		}

		echo '<script type="text/javascript">window.top.location="editmemberslinks.php";</script> <font class="c595">Reset Link Order</font><br />The order of the links has been reset. Click <a href="editmemberslinks.php">Here</a> to continue editing links.';
	}
	else
	{
		echo '<font class="c595">Reset Link Order</font><br /><font class="c599">Are you sure you want to reset the order of the links?</font><form method="post" action="editmemberslinks.php"><input type="hidden" name="reset" value="go" /><input type="submit" value="Yes" /> <input type="button" value="No" onclick="window.location.href=\'editmemberslinks.php\';return false;"></form>';
	}
}
else
{
	//get links
	$query = "Select * from `links` where membersPage = 1 and siteId = '$site_id' Order by linkId Asc";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	$num = mysql_numrows($result);

	$links = array();
	for ($i = 0; $i < $num; $i++)
	{
		$linkId=mysql_result($result,$i,"linkId");
		$pageId=mysql_result($result,$i,"pageId");

		$links[] = array($linkId, $pageId);
	}

	echo '<font class="c595">Edit the Members Links</font><br /><a href="editmemberslinks.php?reset">Reset Link Order</a><br />';

	if (isset($_REQUEST['go']) && isset($_REQUEST['id']))
	{
		$go = $_REQUEST['go'];
		$id = $_REQUEST['id'];

		$last = count($links);

		if ($link['num'] == 1 && $go == 'up')
		{
			echo 'You can\'t move that one up!';
			exit();
		}
		else if ($link['num'] == $last && $go == 'down')
		{
			echo 'You can\'t move that one down!';
			exit();
		}
		else
		{
			$query = "Select pageId From `links` where linkId = " . $id . " and membersPage = 1 and siteId = '$site_id'";
			$result = mysql_query($query) or die ('Getting data into database failed!');
			$pageId = mysql_result($result, 0, "pageId");

			if ($go == 'up')
			{
				//this is getting the linkId of the otherlink
				$query = "Select linkId From `links` where linkId = " . ($id-1) . " and membersPage = 1 and siteId = '$site_id'";
				$result = mysql_query($query) or die ('Getting data into database failed!');
				$the_other_link = mysql_result($result, 0, "linkId");

				//this sets the other link as
				$query1 = "Update `links` Set linkId = " . $id . " where linkId = " . $the_other_link . " and membersPage = 1 and siteId = '$site_id'";
				$result1 = mysql_query($query1) or die ('Putting data into database failed!');

				$query2 = "Update `links` Set linkId = " . $the_other_link . " where pageId = " . $pageId . " and membersPage = 1 and siteId = '$site_id'";
				$result2 = mysql_query($query2) or die ('Putting data into database failed!');
			}
			else if ($go == 'down')
			{
				//this is getting the linkId of the otherlink
				$query = "Select linkId From `links` where linkId = " . ($id+1) . " and membersPage = 1 and siteId = '$site_id'";
				$result = mysql_query($query) or die ('Getting data into database failed!');
				$the_other_link = mysql_result($result, 0, "linkId");

				//this sets the other link as
				$query1 = "Update `links` Set linkId = " . $id . " where linkId = " . $the_other_link . " and membersPage = 1 and siteId = '$site_id'";
				$result1 = mysql_query($query1) or die ('Putting data into database failed!');

				$query2 = "Update `links` Set linkId = " . $the_other_link . " where pageId = " . $pageId . " and membersPage = 1 and siteId = '$site_id'";
				$result2 = mysql_query($query2) or die ('Putting data into database failed!');
			}

			echo '<script type="text/javascript">window.top.location="editmemberslinks.php";</script> The link was moved sucessfully! Click <a href="editmemberslinks.php">Here</a> to move more links.';
		}
	}
	else
	{
		$count = count($links);
		$link['last'] = $count-1;

		//output links
		for ($i=0;$i<$count;$i++)
		{
			$atLeastOne = true;

			$link['pageId'] = $links[$i][1];
			$link['num'] = $links[$i][0];

			$query = "Select pageName from `pages` where pageId = " . $link['pageId'] . " and siteId = '$site_id'";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$link['name'] = mysql_result($result,0,"pageName");

			if ($i == 0 && $i == $link['last'])
			{
				echo '"' . $link["name"] . '"&nbsp;&nbsp;<font color="gray">UP</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color="gray">DOWN</font>';
			}
			else if ($i == 0 && $i != $link['last'])
			{
				echo '"' . $link["name"] . '"&nbsp;&nbsp;<font color="gray">UP</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="editmemberslinks.php?go=down&amp;id=' . $link['num'] . '">DOWN</a>';
			}
			else if ($i != 0 && $i != $link['last'])
			{
				echo '"' . $link["name"] . '"&nbsp;&nbsp;<a href="editmemberslinks.php?go=up&amp;id=' . $link['num'] . '">UP</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="editmemberslinks.php?go=down&amp;id=' . $link['num'] . '">DOWN</a>';
			}
			else if ($i == $link['last'])
			{
				echo '"' . $link["name"] . '"&nbsp;&nbsp;<a href="editmemberslinks.php?go=up&amp;id=' . $link['num'] . '">UP</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color="gray">DOWN</font>';
			}

			echo '<br />';
		}

		if (!isset($atLeastOne)) echo '<font class="c599"><i>None</i></font>';
	}
}
mysql_close();

site_footer();
?>
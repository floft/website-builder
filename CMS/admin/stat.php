<?php
require_once "design.php";
site_header("Site Stats");

function uniquify($input)
{
	$serialized = array_map('serialize', $input);
	$unique = array_unique($serialized);
	return array_intersect_key($input, $unique);
}

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

if (isset($_REQUEST['id']))
{
	$pageId = $_REQUEST['id'];
	$http_referer = $_SERVER['HTTP_REFERER'];

	if (isset($_REQUEST['clear']))
	{
		$query="Update `stat` set ip='' where pageId = '$pageId' and siteId = \"$site_id\"";
		$result=mysql_query($query);
		$query="Update `stat` set browser='' where pageId = '$pageId' and siteId = \"$site_id\"";
		$result=mysql_query($query);
		$query="Update `stat` set date='' where pageId = '$pageId' and siteId = \"$site_id\"";
		$result=mysql_query($query);

		if (isset($_REQUEST['u'])) $http_referer = $_REQUEST['u'];
	}

	//make sure it doesn't go to this page again...
	if ($http_referer == "http://$address/admin/stat.php?id=$pageId") $http_referer = "stat.php";

	if ($pageId == '404error')
	{
		$num = 1;
		$name = "Page Not Found";
	}
	else
	{
		$query="SELECT pageName FROM `pages` where pageId = '$pageId' and siteId = \"$site_id\"";
		$result=mysql_query($query);
		$num = mysql_numrows($result);
		$name = mysql_result($result,0,"pageName");
	}

	if ($num > 0)
	{
		echo "<font class='c595'>Site Stats</font> - <a href=\"stat.php?id=$pageId&amp;clear&amp;u=" . urlencode($http_referer) . "\">Clear</a><br />";

		//display stats
		$query="SELECT ip,browser,date FROM `stat` where pageId = '$pageId' and siteId = \"$site_id\"";
		$result=mysql_query($query);

		if (mysql_numrows($result) > 0)
		{
			$stat = array();
			$ip = mysql_result($result,0,"ip");
			$browser = mysql_result($result,0,"browser");
			$date = mysql_result($result,0,"date");

			$ips = explode("\n", $ip);
			$browsers = explode("\n", $browser);
			$dates = explode("\n", $date);
			$count = count($ips);

			for ($i=0;$i<$count;$i++)
			{
				if (!empty($ips[$i]) && !empty($browsers[$i]) && !empty($browsers[$i])) $stat[] = array($ips[$i], $browsers[$i], $dates[$i]);
			}

			$count = count($stat);

			//make sure it uses proper english
			if ($count == 1) echo "1 user has ";
			else echo "$count users have ";

			echo "gone to the <u>$name</u> page. Here are their <i>IP addresses</i> and <i>browsers</i>:<blockquote>";

			for($i=0;$i<$count;$i++)
			{
				echo "<b>" . $stat[$i][0] . "</b> (<i>" . date("g:ia m-d-y",$stat[$i][2]) . "</i>) - " . $stat[$i][1] . "<br />";
			}

			if ($count==0) echo "<i>none</i>";

			echo "</blockquote>";
		} else "The stats for this page are currently not in the database.<br />";

		echo "<a href='" . $http_referer . "'>Back</a>";
	}
	else echo "page does not exist";
}
else
{
	//get stats
	$query="SELECT ip,browser FROM `stat` where siteId = \"$site_id\"";
	$result=mysql_query($query);

	$stat = array();
	$stats = null;
	$date = null;
	$ip_addresses = null;
	$user_browsers = null;

	//get ip addresses and browser info
	for ($i=0;$i<$num;$i++)
	{
		$ip_addresses .= mysql_result($result,$i,"ip");
		$user_browsers .= mysql_result($result,$i,"browser");
		$date .= mysql_result($result,$i,"date");
	}

	//explode into array
	$ips = explode("\n", $ip_addresses);
	$browsers = explode("\n", $user_browsers);
	$dates = explode("\n", $date);
	$count = count($ips);

	for ($i=0;$i<$count;$i++)
	{
		if (!empty($ips[$i]) && !empty($browsers[$i])) $stat[] = array($ips[$i], $browsers[$i], $dates[$i]);
	}

	//get rid of duplicates
	//$stat = uniqueify($stat);

	$stat = uniquify($stat);
	$count = count($stat);

	foreach($stat as $statistic)
	{
		$stats .= "<b>" . $statistic[0] . "</b> (<i>" . date("g:ia m-d-y",$statistic[2]) . "</i>) - " . $statistic[1] . "<br />";
	}

	if ($stats == null) $stats = "<i>none</i>";

	echo "<font class='c595'>Site Stats</font><br />There " . (($count==1)?"has":"have") . " been a total of $count people come to your site.<br /><a href='javascript:void(0)' onclick='if(document.getElementById(\"statstuff\").style.display==\"none\"){document.getElementById(\"statstuff\").style.display=\"inline\";}else{document.getElementById(\"statstuff\").style.display=\"none\";}'>Show/Hide Stats</a><br /><span style='display:none' id='statstuff'><blockquote>$stats</blockquote></span><br />";

	//display all pages
	$query="SELECT pageId, pageName FROM `pages` where siteId = \"$site_id\" order by pageName Asc";
	$result=mysql_query($query);
	$num=mysql_numrows($result);

	echo "<b>Site Pages</b><br />";

	// echo notes
	for ($i=0;$i < $num; $i++)
	{
		$Id=mysql_result($result,$i,"pageId");
		$Name=mysql_result($result,$i,"pageName");

		echo $Name . ' - <a href="stat.php?id=' . $Id . '">View Stats</a><br />';
	}

	echo 'Page Not Found - <a href="stat.php?id=404error">View Stats</a>';
}

mysql_close();
site_footer();
?>
<?php
//var to keep track of # of entries
if (isset($_GET['page']) && is_numeric($_GET['page'])) $p = (int)$_GET['page'];
else $p = 0;

//alternate url
$url=setting_get("otherurl");
//number per page
$num=setting_get("perpage");
if (!is_numeric($num)) $num=10;
$count = ($p+1)*$num+1;


if ($url == null) $feed_url =  "http://twitter.com/statuses/user_timeline/" . setting_get("user") . ".xml?count=" . $count;
else $feed_url =  setting_get("otherurl");

if ($xml = @simplexml_load_file($feed_url))
{
	$len = count($xml->status);
	$statuses = ($len<$count)?$len:$count-1;

	for ($i=($count>$num)?$count-($num+1):0;$i<$statuses;$i++)
	{
		echo "-".$xml->status[$i]->text . "<br />";
	}

	if ($len >= $count && $p > 0)
	{
		echo "<a href=\"<url:current>?page=" . ($p+1) . "\">Older</a> | <a href=\"<url:current>?page=" . ($p-1) . "\">Newer</a>";
	}
	else if ($len >= $count)
	{
		echo "<a href=\"<url:current>?page=" . ($p+1) . "\">Older</a>";
	}
	else if ($p > 0)
	{
		echo "<a href=\"<url:current>?page=" . ($p-1) . "\">Newer</a>";
	}
}
else
{
	echo "Error getting statuses...<br />";
}
?>
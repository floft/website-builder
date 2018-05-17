<?php
$blogURL = setting_get("url");
$feedURL = setting_get("url2");
$subscribe2URL = setting_get("url3");
if ($subscribe2URL != null) $sub2link = " | <a href=\"$subscribe2URL\" target=\"_blank\"><i>Subscribe by Email</i></a>";
else $sub2link = null;

function getFeed($feed_url, $big=0)
{
	if ($xml = @simplexml_load_file($feed_url))
	{
		//var to keep track of # of entries
		if (isset($_GET['page']) && is_numeric($_GET['page'])) $p = (int)$_GET['page'];
		else $p = 0;

		$posts = count($xml->entry);

		if (isset($xml->entry[$p]))
		{
			//generate permanent link
			$split = split("-", $xml->entry[$p]->id);
			if (count($split)==3)
			{
				$link = " (<a href=\"<url:current>?id=" . $split[2] . "\">Permanent Link</a>)";
			}
			else $link = null;

			echo "<b>{$xml->entry[$p]->title}</b> - " . substr($xml->entry[$p]->published, 5, 2) . "/" . substr($xml->entry[$p]->published, 8, 2) . "/" . substr($xml->entry[$p]->published, 0, 4) . "$link<br />{$xml->entry[$p]->content}";

			if ($posts > ($p+1) && $p > 0)
			{
				echo "<a href=\"<url:current>?page=" . ($p+1) . "\">Next Post</a> | <a href=\"<url:current>?page=" . ($p-1) . "\">Previous Post</a>";
			}
			else if ($posts > ($p+1))
			{
				echo "<a href=\"<url:current>?page=" . ($p+1) . "\">Next Post</a>";
			}
			else if ($p > 0)
			{
				echo "<a href=\"<url:current>?page=" . ($p-1) . "\">Previous Post</a>";
			}
		}
		else
		{
			echo "Post doesn't exist...<br />";
		}
	}
	else
	{
		echo "Error getting blog...<br />";
	}
}

function getFeedList($feed_url)
{
	if ($xml = @simplexml_load_file($feed_url))
	{
		foreach ($xml->entry as $entry)
		{
			//generate permanent link
			$split = split("-", $entry->id);
			if (count($split)==3)
			{
				$link = "<a href=\"<url:current>?id={$split[2]}\">{$entry->title}</a> - " . substr($entry->published, 5, 2) . "/" . substr($entry->published, 8, 2) . "/" . substr($entry->published, 0, 4);
			}
			else $link = null;

			echo $link . "<br />";
		}
	}
	else
	{
		echo "Error getting blog...<br />";
	}
}

if (isset($_GET['id']))
{
	if ($xml = @simplexml_load_file("$blogURL/" . $_GET['id']))
	{
		echo "<div class=\"title\">{$xml->title}</div>posted on " . substr($xml->published, 5, 2) . "/" . substr($xml->published, 8, 2) . "/" . substr($xml->published, 0, 4) . "<br />{$xml->content}<a href=\"<url:current>\">Back to Blog</a>";
	}
	else
	{
		echo "Post doesn't exist...";
	}
}
else if (isset($_GET['list']))
{
	echo "<div class=\"title\">Blog Posts</div>";
	getFeedList($blogURL);
	echo "<br /><a href=\"<url:current>\"><i>Back to Blog</i></a> | <a href=\"$feedURL\" target=\"_blank\"><i>RSS Feed</i></a>$sub2link";
}
else if (isset($_GET['page']))
{
	echo "<div class=\"title\">Blog</div><a href=\"<url:current>?list\"><i>List of Posts</i></a> | <a href=\"$feedURL\" target=\"_blank\"><i>RSS Feed</i></a>$sub2link<br /><br />";
	getFeed($blogURL, 0);
}
else
{
	echo "<div class=\"title\">Most Recent Blog Post</div><a href=\"<url:current>?list\"><i>List of Posts</i></a> | <a href=\"$feedURL\" target=\"_blank\"><i>RSS Feed</i></a>$sub2link<br /><br />";
	getFeed($blogURL, 0);
}
?>
<?php
//if nothing else is displayed on the page...otherwise, cancel the error (the @)
@header("Content-Type: text");
$urls = setting_get("urls");

//replace <menuitems> with the urls of the items on the menu
if (strstr($urls,"<menu>"))
{
	//use menu items if needed
	$query = "Select * from `links` where membersPage = 0 and siteId = '$site_id' order by linkId ASC";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	//make $num the number of members only pages
	$num = mysql_numrows($result);

	$the_links = array();

	for ($i = 0; $i < $num; $i++)
	{
		//add information about the link into the $link array
		$link['pageId']=mysql_result($result,$i,"pageId");

		//get the name of the page (it is for the name of the link)
		$query2 = "Select pageName,pageURL from `pages` where pageId = '" . $link['pageId'] . "' and siteId = '$site_id'";
		$result2 = mysql_query($query2) or die ('Getting data from database failed!');

		//page url
		if ($databasedata['prettyurls'] == 1)
		{
			$link['url'] = mysql_result($result2, 0,"pageURL");
			if ($link['url'] == 1) $link['url'] = "Home";
		}
		else $link['url'] = "index.php?id=" . $link['pageId'];

		//add the link code to the end of "the_links" array
		$the_links[] = "http://$address/{$link['url']}";
	}

	//return "the_links" array joined togeather so that the menu can be printed on the page
	$urls = str_replace("<menu>", join("\r\n", $the_links), $urls);
}

//replace <site> with the site address
$urls = str_replace("<site>", "http://$address/", $urls);

//replace commented out lines
$url_lines = explode("\r\n", $urls);
$urls = array();

foreach ($url_lines as $line)
{
	//if not commented out, add to array
	if (substr($line, 0, 1) != "#")
	{
		$urls[] = $line;
	}
}

echo join("\r\n",$urls);
?>
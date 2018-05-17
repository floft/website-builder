<?php
session_start();
//variables like how to connect to database
require_once "variables.php";
//functions for adding,deleting,editing database stuff...settings
require_once "plugin_functions.php";
//used later for plugins
$plugin_file = null;

$address_URL = preg_split('{/}', $address, 2);
if ($address_URL[0] != $_SERVER['HTTP_HOST']) header('location: http://' . $address . '/' . (isset($address_URL[1]))?$address_URL[1]:"");

//connect to the database
$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

//get settings
$query = "Select * from `settings` where siteId = '$site_id'";
$result = mysql_query($query) or die ('Getting data from database failed! If this is the first time you have use this editor, please go to the <a href="admin">administration page</a> to finish the setup.');
$num = mysql_numrows($result);

for ($i = 0; $i < $num; $i++)
{
	$name=mysql_result($result,$i,"name");
	$value=mysql_result($result,$i,"value");

	$databasedata[$name] = $value;
}

//$member = 1 if the user is logged in, and if they arn't logged in, $member = 0.
if (isset($_SESSION['member'])) $member = 1;
else $member = 0;

function get_string($var,$name)
{
	if (isset($var[$name])) return $var[$name];
	else return null;
}
function replace_var($contents,$name,$var)
{
	preg_match_all("/\<$name:(.*?)\>/", $contents, $matches);
	if ($matches[0] != null)
	{
		$contents = preg_replace("/\<$name:(.*?)\>/e", "get_string(\$var,'$1');", $contents);
	}

	return $contents;
}
function uniquify($input)
{
	$serialized = array_map('serialize', $input);
	$unique = array_unique($serialized);
	return array_intersect_key($input, $unique);
}
function site_stats($pageId=null)
{
	global $site_id;

	if ($pageId == null)
	{
		//get stats
		$query="SELECT ip,browser FROM `stat` where siteId = \"$site_id\"";
		$result=mysql_query($query);
		$num = mysql_numrows($result);

		$stat = array();
		$stats = null;
		$ip_addresses = null;
		$user_browsers = null;

		//get ip addresses and browser info
		for ($i=0;$i<$num;$i++)
		{
			$ip_addresses .= mysql_result($result,$i,"ip");
			$user_browsers .= mysql_result($result,$i,"browser");
		}

		//explode into array
		$ips = explode("\n", $ip_addresses);
		$browsers = explode("\n", $user_browsers);
		$count = count($ips);

		for ($i=0;$i<$count;$i++)
		{
			if (!empty($ips[$i]) && !empty($browsers[$i])) $stat[] = array($ips[$i], $browsers[$i]);
		}

		//get rid of duplicates
		//$stat = uniqueify($stat);
		$stat = uniquify($stat);
		$count = count($stat);
	}
	else
	{
		$query="SELECT ip,browser FROM `stat` where pageId = '$pageId' and siteId = \"$site_id\"";
		$result=mysql_query($query);

		if (mysql_numrows($result) > 0)
		{
			$stat = array();
			$ip = mysql_result($result,0,"ip");
			$browser = mysql_result($result,0,"browser");

			$ips = explode("\n", $ip);
			$browsers = explode("\n", $browser);
			$count = count($ips);

			for ($i=0;$i<$count;$i++)
			{
				if (!empty($ips[$i]) && !empty($browsers[$i])) $stat[] = array($ips[$i], $browsers[$i]);
			}

			$count = count($stat);
		} else $count = false;
	}

	return $count;
}
//create a function that returns the menu (it doesn't return the members only links)
function Menu($pageURL, $link_text)
{
	global $site_id;
	global $address;
	global $databasedata;

	//get the pages that are not members only pages
	$query = "Select * from `links`  where membersPage = 0 and siteId = \"$site_id\" order by linkId ASC";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	//get the number of links
	$num = mysql_numrows($result);

	$the_links = array();

	for ($i = 0; $i < $num; $i++)
	{
		//insert information about the link into the $link array.
		$link['num']=mysql_result($result,$i,"linkId");
		$link['pageId']=mysql_result($result,$i,"pageId");

		//get the name of the page (it is for the name of the link)
		$query2 = "Select pageName, pageURL, menuTXT from `pages` where pageId = " . $link['pageId'] . " and siteId = \"$site_id\"";
		$result2 = mysql_query($query2) or die ('Getting data from database failed!');
		//add the name to the $link array.
		$link['name'] = mysql_result($result2, 0,"pageName");
		$link['menuTXT'] = mysql_result($result2, 0,"menuTXT");

		//page url
		if ($databasedata['prettyurls'] == 1)
		{
			$link['url'] = mysql_result($result2, 0,"pageURL");
			if ($link['url'] == 1) $link['url'] = "Home";
		}
		else $link['url'] = "index.php?id=" . $link['pageId'];

		//replace special value
		$link_text2 = str_replace('<menu:num>', 'link_' . $link['num'], $link_text);
		$link_text2 = str_replace('<menu:name>', $link['name'], $link_text2);
		$link["menuTXT"] = str_replace('<site:url>', "http://$address/", $link['menuTXT']);
		$link_text2 = str_replace('<menu:extra>', $link['menuTXT'], $link_text2);
		$link_text2 = str_replace('<menu:url>', "http://$address/{$link['url']}", $link_text2);
		$link_text2 = str_replace('<menu:pageid>', $link['pageId'], $link_text2);

		//if it is the current page, keep the stuff; otherwise, delete it
		if ($link['url']==$pageURL||($pageURL==null&&$link['pageId']==1)) $link_text2 = preg_replace("/\<menu:current\>(.*)<\/menu:current\>/", "$1", $link_text2);
		else $link_text2 = preg_replace("/\<menu:current\>(.*)<\/menu:current\>/", "", $link_text2);

		//add link to the array
		$the_links[] = $link_text2;
	}
	//return "the_links" array joined togeather so that the menu can be printed on the page
	return join("", $the_links);
}
//create a function to display the members only links
function LoginMenu($link_text)
{
	global $site_id;
	global $databasedata;

	//require "variables.php" to get the rest of the nessesary variables
	require 'variables.php';

	//get the number of members only pages
	$query = "Select * from `links` where membersPage = 1 and siteId = '$site_id' order by linkId ASC";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	//make $num the number of members only pages
	$num = mysql_numrows($result);

	$the_links = array();

	for ($i = 0; $i < $num; $i++)
	{
		//add information about the link into the $link array
		$link['num']=mysql_result($result,$i,"linkId");
		$link['pageId']=mysql_result($result,$i,"pageId");

		//get the name of the page (it is for the name of the link)
		$query2 = "Select pageName,pageURL,menuTXT from `pages` where pageId = '" . $link['pageId'] . "' and siteId = '$site_id'";
		$result2 = mysql_query($query2) or die ('Getting data from database failed!');
		$link['name'] = mysql_result($result2, 0,"pageName");
		$link['menuTXT'] = mysql_result($result2, 0,"menuTXT");

		//page url
		if ($databasedata['prettyurls'] == 1)
		{
			$link['url'] = mysql_result($result2, 0,"pageURL");
			if ($link['url'] == 1) $link['url'] = "Home";
		}
		else $link['url'] = "index.php?id=" . $link['pageId'];

		//replace special values
		$link_text2 = str_replace('<menu:url>', $link['url'], $link_text);
		$link_text2 = str_replace('<menu:num>', 'link_' . $link['num'], $link_text2);
		$link_text2 = str_replace('<menu:name>', $link['name'], $link_text2);
		$link["menuTXT"] = str_replace('<site:url>', "http://$address/", $link['menuTXT']);
		$link_text2 = str_replace('<menu:extra>', $link['menuTXT'], $link_text2);
		$link_text2 = str_replace('<menu:pageid>', $link['pageId'], $link_text2);

		//add the link code to the end of "the_links" array
		$the_links[] = $link_text2;
	}

	//return "the_links" array joined togeather so that the menu can be printed on the page
	return join("", $the_links);
}
//get the plugins and add them to the page if they were in the code (like <plugin:cow>)
function parse_plugins($pageURL, $contents)
{
	global $site_id;
	global $address;
	global $plugin_file;
	global $databasedata;

	if ($handle = opendir('plugins/' . $site_id . '/'))
	{
		$plugins = array();

		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && is_dir('plugins/' . $site_id . "/" . $file))
			{
				$plugins[] = $file;
			}
		}

		$count = count($plugins);
		for ($i=0;$i<$count;$i++)
		{
			$plugin = $plugins[$i];

			//is the plugin used on this page
			if (strstr($contents,"<plugin:$plugin>"))
			{
				if ($xml = @simplexml_load_file("plugins/$site_id/$plugin/plugin.xml"))
				{
					//is the plugin valid
					if (	isset($xml->name) &&
						isset($xml->description) &&
						isset($xml->help) &&
						isset($xml->admin) &&
						isset($xml->site))
					{
						$site = $xml->site;

						if (is_file("plugins/$site_id/$plugin/$site"))
						{
							//variable for the functions
							$plugin_file = $plugin;

							//execute the plugin page
							ob_start();
							include "plugins/$site_id/$plugin/$site";
							$plugin_contents = ob_get_contents();
							ob_end_clean();

							//display result
							$plugin_contents = str_replace("<url:current>", $pageURL, $plugin_contents);
							$contents = str_replace("<plugin:$plugin>", $plugin_contents, $contents);
						}
					}
				}
			}
		}
	}

	return $contents;
}
function page($pageURL, $title, $meta, $contents, $pageId, $design)
{
	global $site_id;
	global $member;
	global $address;
	global $sitename;
	global $site_subtitle;
	global $databasedata;

	//the homepage
	if ($databasedata['prettyurls'] == 1) $homepage = "Home";
	else $homepage = "index.php?id=1";

	//stats
	$site_stats = site_stats();
	$page_stats = site_stats($pageId);

	//$_GET and $_SERVER stuff
	$contents = replace_var($contents,"get",$_REQUEST);
	$contents = replace_var($contents,"server",$_SERVER);

	//replace uploads url in contents
	$contents = str_replace("<url:uploads>", "http://$address/uploads/$site_id/", $contents);
	$contents = str_replace("<url:homepage>", "http://$address/$homepage", $contents);

	//parse the plugins
	$contents = parse_plugins($pageURL, $contents);

	if ($design)
	{
		//get theme stuff
		if ($xml = @simplexml_load_file("themes/$site_id/{$databasedata['theme']}/theme.xml"))
		{
			$search_enabled = true;
			$login_enabled = true;

			if (isset($xml->page))
			{
				$filename = "themes/$site_id/{$databasedata['theme']}/" . $xml->page;
				$file=fopen($filename,"r"); $page = fread($file, filesize($filename)); fclose($file);
			}
			else
			{
				die("theme xml file does not have a \"page\" element");
			}

			if (isset($xml->specs->search) && $xml->specs->search == "false") $search_enabled = false;
			if (isset($xml->specs->login) && $xml->specs->login == "false") $login_enabled = false;
		} else die("error getting theme xml file");

		//parse the theme...make the page
		if ($page != null)
		{
			if (!isset($meta)) $meta = null;

			//replace special values
			$page = str_replace("<site:home>", $homepage, $page);
			$page = str_replace("<site:name>", $sitename, $page);
			$page = str_replace("<site:subtitle>", $site_subtitle, $page);
			$page = str_replace("<site:address>", $address, $page);
			$page = str_replace("<site:theme>", $databasedata['theme'], $page);
			$page = str_replace("<site:footer>", $databasedata['footer'], $page);
			$page = str_replace("<site:meta>", $meta, $page);
			$page = str_replace("<site:title>", $title, $page);
			$page = str_replace("<site:content>", $contents, $page);
			$page = str_replace("<stats:site>", $site_stats, $page);
			$page = str_replace("<stats:current>", $page_stats, $page);
			$page = str_replace("<url:theme>", "http://$address/themes/$site_id/{$databasedata['theme']}", $page);

			//get menu stuff
			preg_match("/\<site:menu\>(.*)<\/site:menu\>/", $page, $matches);

			//if there is menu text
			if (isset($matches[1]))
			{
				$page = preg_replace("/\<site:menu\>(.*)<\/site:menu\>/", Menu($pageURL, $matches[1]), $page);
			}

			//site search
			if ($search_enabled == true)
			{
				if ($databasedata['search'] == 1)
				{
					//keep search there
					$page = preg_replace("/\<site:search\>(.*)\<\/site:search\>/", "$1", $page);

					//search url
					if ($databasedata['prettyurls'] == 1) $searchURL = "search";
					else $searchURL = "index.php?id=search";
					$page = str_replace("<url:search>", $searchURL, $page);
				}
				else
				{
					//get rid of search
					$page = preg_replace("/\<site:search\>(.*)\<\/site:search\>/", "", $page);
				}
			}

			//site login
			if ($login_enabled == true)
			{
				if ($databasedata['login'] == 1 && $member == 1)
				{
					//keep menu there
					$page = preg_replace("/\<login:menu\>(.*)\<\/login:menu\>/", "$1", $page);

					//get menu stuff
					preg_match("/\<login:menuitems\>(.*)<\/login:menuitems\>/", $page, $matches);

					//if there is menu text
					if (isset($matches[1]))
					{
						$page = preg_replace("/\<login:menuitems\>(.*)<\/login:menuitems\>/", LoginMenu($matches[1]), $page);
					}

					//login url
					if ($databasedata['prettyurls'] == 1)
					{
						$loginURL = "login";
						$logoutURL = "logout";
						$accountURL = "account";
					}
					else
					{
						$loginURL = "index.php?id=login";
						$logoutURL = "index.php?id=logout";
						$accountURL = "index.php?id=account";
					}

					$page = str_replace("<url:login>", $loginURL, $page);
					$page = str_replace("<url:logout>", $logoutURL, $page);
					$page = str_replace("<url:account>", $accountURL, $page);
				}
				else
				{
					//get rid of menu
					$page = preg_replace("/\<login:menu\>(.*)\<\/login:menu\>/", "", $page);
				}

				if ($databasedata['loginform'] == 1 && $member != 1)
				{
					//keep form there
					$page = preg_replace("/\<login:form\>(.*)\<\/login:form\>/", "$1", $page);

					//create account url
					if ($databasedata['prettyurls'] == 1)
					{
						$createURL = "create";
						$loginURL = "login";
					}
					else
					{
						$createURL = "index.php?id=create";
						$loginURL = "index.php?id=login";
					}

					$page = str_replace("<url:login>", $loginURL, $page);
					$page = str_replace("<url:create>", $createURL, $page);
				}
				else
				{
					//get rid of form
					$page = preg_replace("/\<login:form\>(.*)\<\/login:form\>/", "", $page);
				}
			}

		} else die("theme page is blank");

		return $page;
	}
	else
	{
		$contents = str_replace("<stats:site>", $site_stats, $contents);
		$contents = str_replace("<stats:current>", $page_stats, $contents);

		return $contents;
	}
}

mysql_close();
?>
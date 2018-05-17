<?php
require_once "design.php";
site_header("Themes");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

$search = $databasedata['search'];
$members = $databasedata['login'];
$theme = $databasedata['theme'];
$loginform = $databasedata['loginform'];

if (isset($_REQUEST['install']))
{
	function ShellFix($s)
	{
	  return "'".str_replace("'", "'\''", $s)."'";
	}
	function zip_open2($s)
	{
	  $fp = fopen($s, 'rb');
	  if(!$fp) return false;

	  $lines = Array();
	  $cmd = 'unzip -v '.shellfix($s);
	  exec($cmd, $lines);

	  $contents = Array();
	  $ok=false;
	  foreach($lines as $line)
	  {
		if($line[0]=='-') { $ok=!$ok; continue; }
		if(!$ok) continue;

		$length = (int)$line;
		$fn = trim(substr($line,58));

		$contents[] = Array('name' => $fn, 'length' => $length);
	  }

	  return
		Array('fp'       => $fp,
			  'name'     => $s,
			  'contents' => $contents,
			  'pointer'  => -1);
	}
	function zip_read2(&$fp)
	{
	  if(!$fp) return false;

	  $next = $fp['pointer'] + 1;
	  if($next >= count($fp['contents'])) return false;

	  $fp['pointer'] = $next;
	  return $fp['contents'][$next];
	}
	function zip_entry_name2(&$res)
	{
	  if(!$res) return false;
	  return $res['name'];
	}
	function zip_entry_filesize2(&$res)
	{
	  if(!$res) return false;
	  return $res['length'];
	}
	function zip_entry_open2(&$fp, &$res)
	{
	  if(!$res) return false;

	  $cmd = 'unzip -p '.shellfix($fp['name']).' '.shellfix($res['name']);

	  $res['fp'] = popen($cmd, 'r');
	  return !!$res['fp'];
	}
	function zip_entry_read2(&$res, $nbytes)
	{
	  return fread($res['fp'], $nbytes);
	}
	function zip_entry_close2(&$res)
	{
	  fclose($res['fp']);
	  unset($res['fp']);
	}
	function zip_close2(&$fp)
	{
	  fclose($fp['fp']);
	}
	function create_dirs($path)
	{
	  if (!is_dir($path))
	  {
		$directory_path = "";
		$directories = explode("/",$path);
		array_pop($directories);

		foreach($directories as $directory)
		{
		  $directory_path .= $directory."/";
		  if (!is_dir($directory_path))
		  {
			mkdir($directory_path);
			chmod($directory_path, 0755);
		  }
		}
	  }
	}
	function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true)
	{
	  if ($zip = zip_open2($src_file))
	  {
		if ($zip)
		{
		  $splitter = ($create_zip_name_dir === true) ? "." : "/";
		  if ($dest_dir === false) $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";

		  // Create the directories to the destination dir if they don't already exist
		  create_dirs($dest_dir);

		  // For every file in the zip-packet
		  while ($zip_entry = zip_read2($zip))
		  {
			// Now we're going to create the directories in the destination directories

			// If the file is not in the root dir
			$pos_last_slash = strrpos(zip_entry_name2($zip_entry), "/");
			if ($pos_last_slash !== false)
			{
			  // Create the directory where the zip-entry should be saved (with a "/" at the end)
			  create_dirs($dest_dir.substr(zip_entry_name2($zip_entry), 0, $pos_last_slash+1));
			}

			// Open the entry
			if (zip_entry_open2($zip,$zip_entry,"r"))
			{

			  // The name of the file to save on the disk
			  $file_name = $dest_dir.zip_entry_name2($zip_entry);

			  // Check if the files should be overwritten or not
			  if ($overwrite === true || $overwrite === false && !is_file($file_name))
			  {
				// Get the content of the zip entry
				//$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

				$contents = '';
				while (!feof($zip_entry['fp'])) {
				  $contents .= fread($zip_entry['fp'], 8192);
				}

				file_put_contents($file_name, $contents);

				// Set the rights
				preg_match('/\.[^\.]*$/Di', $file_name, $type);
				if(isset($type[0]) && strtolower($type[0]) == '.js') chmod($file_name, 0644);
				else chmod($file_name, 0755);

				//echo "save: ".$file_name."; type: ".$type[0]."<br />";
			  }

			  // Close the entry
			  zip_entry_close2($zip_entry);
			}
		  }
		  // Close the zip-file
		  zip_close2($zip);
		}
	  }
	  else
	  {
		return false;
	  }

	  return true;
	}

	if (isset($_FILES['file']))
	{
		if ($_FILES["file"]["error"] > 0)
		{
			echo "<font class='c599'>Return Code: " . $_FILES["file"]["error"] . "<br /></font><a href='themes.php?install'>Back</a>";
		}
		else
		{
			if(unzip($_FILES["file"]["tmp_name"], '../themes/' . $site_id . '/', false, false))
			{
				echo "Theme successfully installed!<br /><a href='themes.php'>Back</a>";
			}
			else
			{
				echo "An error occured while installing. Most likely a theme already exists with that name.<br /><a href='themes.php?install'>Try Again</a>";
			}
		}
	}
	else
	{
		echo '<font class="c595">Install Theme</font><br /><br />
		<form method="post" action="themes.php?install" enctype="multipart/form-data">
		<label for="file">Theme (something.theme):</label><input type="file" name="file" id="file" /><br /><input type="submit" value="Install"><br /></form><br /><a href="themes.php" class="link23">Cancel</a>';
	}
}
else if (isset($_REQUEST['delete']))
{
	echo "<font class='c595'>Delete Theme</font><br /><br />";

	if (isset($_REQUEST['delete2']) && is_dir("../themes/$site_id/{$_REQUEST['delete2']}"))
	{
		if ($theme != $_REQUEST['delete2'])
		{
			function RecursiveFolderDelete($folderPath)
			{
				if (is_dir($folderPath))
				{
					foreach (scandir($folderPath) as $value)
					{
						if ($value != "." && $value != "..")
						{
							$value = $folderPath . "/" . $value;

							if (is_dir($value))
							{
								RecursiveFolderDelete($value);
							}
							elseif (is_file($value))
							{
								@unlink($value);
							}
						}
					}
					return rmdir($folderPath);
				}
				else
				{
					return FALSE;
				}
			}

			//delete plugin
			RecursiveFolderDelete("../themes/$site_id/{$_REQUEST['delete2']}");

			//delete plugin settings
			//$query = "Delete from `plugins` where plugin = '{$_REQUEST['delete2']}' and siteId = \"$site_id\"";
			//$result = mysql_query($query) or die ('Deleting data from database failed!');

			echo "{$_REQUEST['delete2']} has been deleted.<br /><a href='themes.php?delete'>Back</a>";
		} else echo "{$_REQUEST['delete2']} can not be deleted. It is your current theme.<br /><a href='themes.php?delete'>Back</a>";
	}
	else if ($_REQUEST['delete'] != null)
	{
		if ($theme != $_REQUEST['delete'])
		{
			echo '<font class="c599">Are you sure you want to delete ' . $_REQUEST['delete']. '?</font><form method="post" action="themes.php?delete&delete2=' . $_REQUEST['delete'] . '"><input type="submit" value="Yes" /> <input type="button" value="No" onclick="window.location.href=\'' . $_SERVER['HTTP_REFERER'] . '\';return false;"></form>';
		} else echo "{$_REQUEST['delete']} can not be deleted. It is your current theme.<br /><a href='themes.php?delete'>Back</a>";
	}
	else
	{

		if ($handle = opendir('../themes/' . $site_id . "/"))
		{
			$themes = 0;

			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && is_dir('../themes/' . $site_id . "/" . $file))
				{
					if ($xml = @simplexml_load_file("../themes/" . $site_id . "/" . $file . "/theme.xml"))
					{
						$themes++;
						echo "$file - <a href=\"themes.php?delete=$file\">Delete</a><br />";
					} else echo "error loading XML file";
				}
			}

			if ($themes == 0)
			{
				echo "<i>none</i>";
			}

			echo "<br /><a href='themes.php'>Back</a>";
		} else echo 'Can\'t open theme directory!';
	}
}
else
{
	if (isset($_POST['theme']))
	{
		$newsearch = $_POST['search'];
		$newmembers = $_POST['members'];
		$newtheme = $_POST['theme'];
		$newloginform = $_POST['loginform'];

		if($newtheme != $theme)
		{
			$query = "Update `settings` SET value = '$newtheme' where name = 'theme' and siteId = \"$site_id\"";
			$result = mysql_query($query) or die('Update failed!');

			echo 'Now the theme will be "' . $newtheme . '"<br />';

			$query = "Select value from `settings` where name = 'theme' and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Select failed!');
			$theme = $newtheme;

			if ($xml = @simplexml_load_file("../themes/$site_id/$theme/theme.xml"))
			{
				if (isset($xml->specs->search) && $xml->specs->search == "false") $newsearch = 0;
				if (isset($xml->specs->login) && $xml->specs->login == "false")
				{
					$newmembers = 0;
					$newloginform = 0;
				}
			}
		}

		if($newsearch != $search && isset($_REQUEST['search']))
		{
			$query = "Update `settings` SET value = '$newsearch' where name = 'search' and siteId = \"$site_id\"";
			$result = mysql_query($query) or die('Update failed!');

			if ($newsearch == 1) echo 'There will now be a search on your site.<br />';
			else if ($newsearch == 0) echo 'Now there will not be a search on your site.<br />';
			$search = $newsearch;
		}

		if($newmembers != $members && isset($_REQUEST['members']))
		{
			$query = "Update `settings` SET value = '$newmembers' where name = 'login' and siteId = \"$site_id\"";
			$result = mysql_query($query) or die('Update failed!');

			if ($newmembers == 1) echo 'You will be able to have members and put members only pages on your site.<br />';
			else if ($newmembers == 0)
			{
				if ($members == 1)
				{
					//disable login form if members is disabled
					$newloginform = 0;

					$query = "Update `pages` SET membersPage = 0 where membersPage = 1 and siteId = \"$site_id\"";
					$result = mysql_query($query) or die('Update failed!');

					$query = "Delete from `links` where membersPage = 1 and siteId = \"$site_id\"";
					$result = mysql_query($query) or die('Delete failed!');
				}

				echo 'You will not be able to have members and put members only pages on your site.<br />';
			}

			$members = $newmembers;
		}

		if($newloginform != $loginform && isset($_REQUEST['loginform']))
		{
			$query = "Update `settings` SET value = '$newloginform' where name = 'loginform' and siteId = \"$site_id\"";
			$result = mysql_query($query) or die('Update failed!');

			if ($newloginform == 1) echo 'There will now be a login form on your site.<br />';
			else if ($newloginform == 0) echo 'Now there will not be a login form on your site.<br />';

			$loginform = $newloginform;
		}
	}

	echo '<font class="c595">Themes</font>';
	echo '<form method="post" action="themes.php">';

	//get theme settings from xml file
	$search_enabled = true;
	$login_enabled = true;

	if ($xml = @simplexml_load_file("../themes/$site_id/$theme/theme.xml"))
	{
		if (isset($xml->specs->search) && $xml->specs->search == "false") $search_enabled = false;
		if (isset($xml->specs->login) && $xml->specs->login == "false") $login_enabled = false;
	}
	else
	{
		echo "<b>Warning:</b> the settings below may or may not do anything depending on what theme you have.<br /><br />";
	}

	echo '<a href="themes.php?install">Install Theme</a> | <a href="themes.php?delete">Delete Theme</a> | <a href="../docs/maketheme.htm" target="_blank">Create a Theme</a><br /><br /><input type="submit" value="Save"><br /><br />';

	if ($search_enabled == true)
	{
		if ($search == 1)
		{
			$search1 = 'checked="checked"';
			$search2 = '';
		}
		else
		{
			$search1 = '';
			$search2 = 'checked="checked"';
		}

		echo 'Do you want a search on your website? <input type="radio" name="search" value="1" ' . $search1 . '>Yes <input type="radio" name="search" value="0" ' . $search2 . '> No<br />';
	}
	else
	{
		echo "<i>Current theme does not support a search.</i><br />";
	}
	if ($login_enabled == true)
	{
		if ($members == 1)
		{
			$loginform1 = "visible";
			$members1 = 'checked="checked"';
			$members2 = '';
		}
		else
		{
			$loginform1 = "hidden";
			$members1 = '';
			$members2 = 'checked="checked"';
		}

		if ($loginform == 1)
		{
			$members3 = 'checked="checked"';
			$members4 = '';
		}
		else
		{
			$members3 = '';
			$members4 = 'checked="checked"';
		}

		echo 'Do you want any members, a login, and/or members only pages on your website? <input type="radio" name="members" value="1" onclick="document.getElementById(\'loginformoption\').style.visibility=\'visible\'" ' . $members1 . '>Yes <input type="radio" name="members" value="0"onclick="document.getElementById(\'loginformoption\').style.visibility=\'hidden\'" ' . $members2 . '> No<br />';
		echo '<span style="visibility:'.$loginform1.'" id="loginformoption">Do you want a login form on your website? <input type="radio" name="loginform" value="1" ' . $members3 . '>Yes <input type="radio" name="loginform" value="0" ' . $members4 . '> No<br /></span>';
	}
	else
	{
		echo "<i>Current theme does not support any sort of login.</i><br />";
	}

	echo '<br />Please Pick a theme:<br />';

	if ($handle = opendir('../themes/' . $site_id))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && is_dir('../themes/' . $site_id . "/" . $file))
			{
				if ($xml = @simplexml_load_file("../themes/" . $site_id . "/" . $file . "/theme.xml"))
				{
					if ($theme == $file) echo '<div><input type="radio" name="theme" value="' . $file . '" checked="checked"> <b>' . ucfirst($file) . '</b><br />';
					else echo '<div><input type="radio" name="theme" value="' . $file . '"> <b>' . ucfirst($file) . '</b><br />';

					if (isset($xml->screenshot))
					{
						echo "<img src='../themes/" . $site_id . "/" . $file . "/{$xml->screenshot}' alt='" . ucfirst($file) . "' width='300px' class='pic'><br /><br />";
					}

					if (isset($xml->specs))
					{
						$specs = 0;
						echo "<b>Specifications for this Theme</b><br />";

						if (isset($xml->specs->search))
						{
							$specs++;
							echo "Search: ";

							if ($xml->specs->search == "true") echo "available<br />";
							else echo "not available<br />";
						}

						if (isset($xml->specs->login))
						{
							$specs++;
							echo "Login: ";

							if ($xml->specs->login == "true") echo "available<br />";
							else echo "not available<br />";
						}

						if ($specs == 0)
						{
							echo "<i>none</i><br />";
						}

						echo "<br />";
					}

					echo "</div>";
				}
			}
		}
	}
	else
	{
		echo 'Can\'t open theme directory!';
	}

	echo "<input type=\"submit\" value=\"Save\"></form>";
}
site_footer();
?>
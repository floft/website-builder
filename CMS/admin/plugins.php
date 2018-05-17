<?php
require_once "design.php";
site_header("Plugins");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

function validURL($url) {
	$url = stripslashes(urldecode($url));
	$parsedURL = parse_url($url);

	if($parsedURL['scheme'] != null && $parsedURL['host'] != null) {
		if(($parsedURL['scheme'] == 'http' || $parsedURL['scheme'] == 'https') && preg_match("/.+\..*$/Di", $parsedURL['host'])) {
			return true;
		} else return false;
	} else return false;
}

if (isset($_REQUEST['p']))
{
	if ($handle = opendir('../plugins/' . $site_id . "/"))
	{
		$plugins = 0;

		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && is_dir('../plugins/' . $site_id . "/" . $file) && $file == $_REQUEST['p'])
			{
				if ($xml = @simplexml_load_file("../plugins/" . $site_id . "/" . $file . "/plugin.xml"))
				{
					if (	isset($xml->name) &&
						isset($xml->description) &&
						isset($xml->help) &&
						isset($xml->admin) &&
						isset($xml->site))
					{
						$plugins++;

						$name = $xml->name;
						$admin = $xml->admin;
						$site = $xml->site;
						//if (validURL($help)) $help = " | <a href='{$xml->help}' target='_blank'>Plugin Help</a>";
						//else $help = null;
						
						if (trim($xml->help) != null) $help = " | <a href='{$xml->help}' target='_blank'>Plugin Help</a>";
						else $help = null;

						echo "<font class='c595'>$name</font> - Insert this into the page to use: &lt;plugin:" . $file . "&gt;<br /><a href=\"plugins.php\">All Plugins</a> | <a href=\"plugins.php?delete=$file\">Delete Plugin</a>$help<br /><br />";

						if (!is_file("../plugins/$site_id/$file/$site"))
						{
							echo "<b>Warning:</b> the file that allows you to use this plugin on your site does not exist.<br /><br />";
						}

						if (is_file("../plugins/$site_id/$file/$admin"))
						{
							//variable for the functions
							$plugin_file = $file;

							//functions for adding,deleting,editing database stuff...settings
							require_once "../plugin_functions.php";

							//execute the plugin page
							ob_start();
							include "../plugins/$site_id/$file/" . $admin;
							$contents = ob_get_contents();
							ob_end_clean();

							//display result
							$contents = str_replace("<url:current>", "plugins.php?p=" . $file, $contents);
							echo $contents;

						}
						else
						{
							echo "Plugin page does not exist!<br /><br />";
						}
					}
					else
					{
						echo "Plugin XML file not valid!<br /><br />";
					}
				} else echo "error loading XML file";
			}
		}

		if ($plugins == 0)
		{
			echo "Plugin doesn't exist!";
		}
	}
	else echo "Error accessing plugin directory!";
}
else if (isset($_REQUEST['install']))
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
			echo "<font class='c599'>Return Code: " . $_FILES["file"]["error"] . "<br /></font><a href='plugins.php?install'>Back</a>";
		}
		else
		{
			if(unzip($_FILES["file"]["tmp_name"], '../plugins/' . $site_id . '/', false, false))
			{
				echo "Plugin successfully installed!<br /><a href='plugins.php'>Back</a>";
			}
			else
			{
				echo "An error occured while installing. Most likely a plugin already exists with that name.<br /><a href='plugins.php?install'>Try Again</a>";
			}
		}
	}
	else
	{
		echo '<font class="c595">Install Plugin</font><br /><br />
		<form method="post" action="plugins.php?install" enctype="multipart/form-data">
		<label for="file">Plugin (something.plugin):</label><input type="file" name="file" id="file" /><br /><input type="submit" value="Install"><br /></form><br /><a href="plugins.php" class="link23">Cancel</a>';
	}
}
else if (isset($_REQUEST['delete']))
{
	echo "<font class='c595'>Delete Plugin</font><br /><br />";

	if (isset($_REQUEST['delete2']) && is_dir("../plugins/$site_id/{$_REQUEST['delete2']}"))
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
		RecursiveFolderDelete("../plugins/$site_id/{$_REQUEST['delete2']}");

		//delete plugin settings
		$query = "Delete from `plugins` where plugin = '{$_REQUEST['delete2']}' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Deleting data from database failed!');

		echo "{$_REQUEST['delete2']} has been deleted.<br /><a href='plugins.php?delete'>Back</a>";
	}
	else if ($_REQUEST['delete'] != null)
	{
		echo '<font class="c599">Are you sure you want to delete ' . $_REQUEST['delete']. '?</font><form method="post" action="plugins.php?delete&delete2=' . $_REQUEST['delete'] . '"><input type="submit" value="Yes" /> <input type="button" value="No" onclick="window.location.href=\'' . $_SERVER['HTTP_REFERER'] . '\';return false;"></form>';
	}
	else
	{

		if ($handle = opendir('../plugins/' . $site_id . "/"))
		{
			$plugins = 0;

			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && is_dir('../plugins/' . $site_id . "/" . $file))
				{
					if ($xml = @simplexml_load_file("../plugins/" . $site_id . "/" . $file . "/plugin.xml"))
					{
						if (	isset($xml->name) &&
							isset($xml->description) &&
							isset($xml->help) &&
							isset($xml->admin) &&
							isset($xml->site))
						{
							$plugins++;

							$name = $xml->name;

							echo "$name - <a href=\"plugins.php?delete=$file\">Delete</a><br />";
						}
						else
						{
							echo "Plugin XML file not valid!<br /><br />";
						}
					} else echo "error loading XML file";
				}
			}

			if ($plugins == 0)
			{
				echo "<i>none</i>";
			}

			echo "<br /><a href='plugins.php'>Back</a>";
		} else echo 'Can\'t open plugin directory!';
	}
}
else
{
	echo "<font class='c595'>Plugins</font><br /><a href='plugins.php?install'>Install Plugins</a> | <a href='plugins.php?delete'>Delete Plugins</a> | <a href='../docs/makeplugin.htm' target='_blank'>Create a Plugin</a><br /><br />";

	if ($handle = opendir('../plugins/' . $site_id . "/"))
	{
		$plugins = 0;

		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && is_dir('../plugins/' . $site_id . "/" . $file))
			{
				if ($xml = @simplexml_load_file("../plugins/" . $site_id . "/" . $file . "/plugin.xml"))
				{
					if (	isset($xml->name) &&
						isset($xml->description) &&
						isset($xml->help) &&
						isset($xml->admin) &&
						isset($xml->site))
					{
						$plugins++;

						$name = $xml->name;
						$description = $xml->description;
						if (validURL($help)) $help = "<a href='{$xml->help}' target='_blank'>{$xml->help}</a><br />";
						else if ($help == null) $help = null;
						else $help = $xml->help . "<br />";

						echo "<a href=\"plugins.php?p=$file\">$name</a> - $description<br />";
					}
					else
					{
						echo "Plugin XML file not valid!<br /><br />";
					}
				} else echo "error loading XML file";
			}
		}

		if ($plugins == 0)
		{
			echo "<i>none</i><br />";
		}
	}
	else
	{
		if (!is_dir("../plugins/" . $site_id . "/"))
		{
			mkdir("../plugins/" . $site_id . "/");
			echo "Plugins directory created! Please refresh the page.";
		}
		else
		{
			echo 'Can\'t open plugin directory!';
		}
	}

	echo "<br />Warning: plugins can do anything including delete your entire website, so be careful when you use plugins.";
}

mysql_close();
site_footer();
?>
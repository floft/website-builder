<?php
require '../variables.php';

//update the files
/**
* Unzip the source_file in the destination dir
*
* @param   string      The path to the ZIP-file.
* @param   string      The path where the zipfile should be unpacked, if false the directory of the zip-file is used
* @param   boolean     Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false) (only if the destination directory is set to false!)
* @param   boolean     Overwrite existing files (true) or not (false)
*
* @return  boolean     Succesful or not
*/

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
function deleteFolderContents($folderPath)
{
	if (is_dir($folderPath))
	{
		foreach (scandir($folderPath) as $value)
		{
			if ($value != "." && $value != ".." && $value != "update.php")
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
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

error_reporting(0);

if(is_dir('../tinymce')) RecursiveFolderDelete('../tinymce');
mkdir("../tinymce", 0755);

if(is_dir('../admin')) deleteFolderContents('../admin');

//delete the old zip file if it hasn't been deleted yet
if(is_file('FloftWebsiteBuilder_new.zip')) unlink('FloftWebsiteBuilder_new.zip');

//copy the new zip file onto the site
copy('http://www.floft.net/fd/FloftWebsiteBuilder.zip', 'FloftWebsiteBuilder_new.zip');

//unzip the new zip file
unzip('FloftWebsiteBuilder_new.zip', '../', false, true);

//delete the new zip file
if(is_file('FloftWebsiteBuilder_new.zip')) unlink('FloftWebsiteBuilder_new.zip');

//connect to the database
$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

//update the database
@mysql_query(file_get_contents("http://www.floft.net/fd/update.sql"));

echo '<html><head><title>Updated</title></head><body>Your website has been successfully updated! Click <a href="index.php">Here</a> to go back to the Administration page.</body></html>';

//update the version number
$filename = "http://www.floft.net/fd/most_recent_updates.php";
$file=fopen($filename,"r"); $version = fread($file, 100); fclose($file);

//update version number in database
$query = "Update `settings` set value = '" . $version . "' where name = 'version'";
mysql_query($query) or die ('Updating Version failed!');

mysql_close();
?>
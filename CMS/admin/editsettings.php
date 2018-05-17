<?php
require_once "design.php";
site_header("Edit Settings");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

$prettyURLs = $databasedata['prettyurls'];
$editHTML = $databasedata['editHTML'];

if (isset($_POST['prettyURLs']))
{
	$newprettyURLs = $_POST['prettyURLs'];
	$neweditHTML = $_POST['editHTML'];

	if($newprettyURLs != $prettyURLs)
	{
		$query = "Update `settings` SET value = '$newprettyURLs' where name = 'prettyurls' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die('Update failed!');

		if ($newprettyURLs == 1) echo 'Your site URLs will now look like: http://www.example.com/Home<br />';
		else if ($newprettyURLs == 0) echo 'Your URLs will now look like: http://www.example.com/index.php?id=1<br />';
		$prettyURLs = $newprettyURLs;

		//create the .htaccess stuff
		if ($prettyURLs == 1)
		{
			if (!is_file("../.htaccess"))
			{
				//ex: floft.net/cow for the path.... add cow/index.php into the .htaccess file
				//otherwise just /index.php
				/*$address_URL = preg_split('{/}', $address, 2);
				if (isset($address_URL[1])) $path = $address_URL[1];
				else $path = null;
				$path2 = str_replace("/", "\/", $path) . "\/";
				$path .= "/";*/

				//create file
				$filename = "../.htaccess";
				//$file=fopen($filename,"w"); fwrite($file, "RewriteEngine on\r\nRewriteRule ^$path2([0-9a-zA-Z]{1,})$ {$path}index.php?id=$1"); fclose($file);
				$file=fopen($filename,"w"); fwrite($file, "RewriteEngine On\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteRule . index.php [L]"); fclose($file);
			}
			else
			{
				//ex: floft.net/cow for the path.... add cow/index.php into the .htaccess file
				//otherwise just /index.php
				/*$address_URL = preg_split('{/}', $address, 2);
				if (isset($address_URL[1])) $path = $address_URL[1];
				else $path = null;
				$path2 = str_replace("/", "\/", $path) . "\/";
				$path .= "/";*/

				//add to file
				$filename = "../.htaccess";
				$file=fopen($filename,"r"); $contents = fread($file, filesize($filename)); fclose($file);
				$file=fopen($filename,"w"); fwrite($file, "$contents\r\nRewriteEngine On\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteRule . index.php [L]");fclose($file);
			}
		}
		//delete the .htaccess stuff
		else
		{
			if (is_file("../.htaccess"))
			{
				/*$address_URL = preg_split('{/}', $address, 2);
				if (isset($address_URL[1])) $path = $address_URL[1];
				else $path = null;
				$path2 = str_replace("/", "\/", $path) . "\/";
				$path .= "/";*/

				//get file contents
				$filename = "../.htaccess";
				$file=fopen($filename,"r"); $contents = fread($file, filesize($filename)); fclose($file);

				if ($contents == "RewriteEngine On\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteRule . index.php [L]")
				{
					//delete file
					unlink($filename);
				}
				else
				{
					//delete that part of the file
					$file=fopen($filename,"w"); fwrite($file, trim(str_replace("\r\nRewriteEngine On\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteRule . index.php [L]", "", $contents)));fclose($file);
				}
			}
		}
	}

	if($editHTML != $neweditHTML)
	{
		$query = "Update `settings` SET value = '$neweditHTML' where name = 'editHTML' and siteId = \"$site_id\"";
		$result = mysql_query($query) or die('Update failed!');

		if ($neweditHTML == 1) echo 'You will now be able to edit the HTML code when editing your pages.<br />';
		else if ($neweditHTML == 0) echo 'You will now use a WYSIWYG editor when editing your pages.<br />';
		$editHTML = $neweditHTML;
	}
}

echo '<font class="c595">Edit Settings</font><br />';
echo '<form method="post" action="editsettings.php">';

//some site settings
if ($editHTML == 1)
{
	$editHTML1 = 'checked="checked"';
	$editHTML2 = '';
}
else
{
	$editHTML1 = '';
	$editHTML2 = 'checked="checked"';
}

echo 'Do you want to edit the HTML code for pages or use a WYSIWYG editor?<br /><input type="radio" name="editHTML" value="0" ' . $editHTML2 . '>WYSIWYG Editor<br /><input type="radio" name="editHTML" value="1" ' . $editHTML1 . '>Edit HTML<br /><br />';

if ($prettyURLs == 1)
{
	$prettyURLs1 = 'checked="checked"';
	$prettyURLs2 = '';
}
else
{
	$prettyURLs1 = '';
	$prettyURLs2 = 'checked="checked"';
}

echo 'What do you want the URLs to look like (.htaccess file used for second option, requiring Apache)?<br /><input type="radio" name="prettyURLs" value="0" ' . $prettyURLs2 . '>http://www.example.com/index.php?id=1<br /><input type="radio" name="prettyURLs" value="1" ' . $prettyURLs1 . '>http://www.example.com/Home<br /><br /><input type="submit" value="Save">
</form>';

mysql_close();

site_footer();
?>
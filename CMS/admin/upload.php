<?php
require_once "design.php";
site_header("Upload");

if (isset($_REQUEST['id']))
{
	$pageid = $_REQUEST['id'];
	if ($pageid == 'upload')
	{
		if (isset($_POST['submit']))
		{
			if (($_FILES["file"]["size"] / 1024) < $max_upload_size)
			{
				if ($_FILES["file"]["error"] > 0)
				{
					echo "<font class='c599'>Return Code: " . $_FILES["file"]["error"] . "<br /></font>";
				}
				else
				{
					if ($_REQUEST['name'] != null)
					{
						if (!preg_match('/\'/', $_REQUEST['name']) && !preg_match('/\"/', $_REQUEST['name']))
						{
							if (!preg_match('/\\\/', $_REQUEST['name']) && !preg_match('/\//', $_REQUEST['name']))
							{
								$name = $_REQUEST['name'];

								if (preg_match('/\.[^\.]*$/Di', $_FILES["file"]["name"])) preg_match('/\.[^\.]*$/Di', $_FILES["file"]["name"], $type);
								$name = $name . $type[0];

								echo "<font class='c599'>Upload: " . $_FILES["file"]["name"] . "<br />";
								echo "Type: " . $_FILES["file"]["type"] . "<br />";
								echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
								echo "<br /><br />";
								if (isset($_REQUEST['replace']) && $_REQUEST['replace'] == 0)
								{
									if (file_exists("../uploads/" . $site_id . "/" . $name))
									{
										echo "\"" . $name . "\" already exists.";
									}
									else
									{
										move_uploaded_file($_FILES["file"]["tmp_name"], "../uploads/" . $site_id . "/" . $name);
										echo "URL to file: <a href='http://" . $address . "/uploads/" . $site_id . "/" . $name . "' class='link23' target='_blank'>http://" . $address . "/uploads/" . $site_id . "/" . $name . "</a>";
									}
								}
								else
								{
									if (file_exists("../uploads/" . $site_id . "/" . $name))
									{
										unlink("../uploads/" . $site_id . "/" . $name);
										echo "\"" . $name . "\" has been replaced.<br /><br />";
									}

									move_uploaded_file($_FILES["file"]["tmp_name"], "../uploads/" . $site_id . "/" . $name);
									echo "URL to file: <a href='http://" . $address . "/uploads/" . $site_id . "/" . $name . "' class='link23' target='_blank'>http://" . $address . "/uploads/" . $site_id . "/" . $name . "</a>";
								}
								echo '</font>';

								if ($_REQUEST['executable'] == 1)
								{
									//make file executable
									chmod("../uploads/" . $site_id . "/" . $name, 0755);
								}
							}
							else
							{
								echo '<font class="c599">Don\'t have any slashes or backslashes in your name!</font>';
							}
						}
						else
						{
							echo "<font class='c599'>Don't have quotes in your name!</font>";
						}
					}
					else
					{
						echo "<font class='c599'>You need to type in a name!</font>";
					}
				}
				echo '<br /><a href="upload.php" class="link23">View Uploads</a>';
				echo '<br /><a href="upload.php?id=upload" class="link23">Upload Something Else</a>';
			}
			else
			{
				echo "<font class='c599'>File too large!</font>";
				echo '<br /><a href="upload.php" class="link23">View Uploads</a>';
				echo '<br /><a href="upload.php?id=upload" class="link23">Upload Something Else</a>';
			}
		}
		else
		{
			echo '<font class="c599">Please make sure the upload is under ' . $max_upload_size . 'kb. Don\'t have quotes in the name!</font><br /><br />
			<form method="post" action="upload.php?id=upload" enctype="multipart/form-data">
			<input type="hidden" name="submit" value="Upload" />
			<label for="file">Filename:</label>
			<input type="file" name="file" id="file" /><br />
			Name: <input type="text" name="name" id="name" /> (Don\'t include the extention in the name. In "test.txt" the extention would be ".txt")<br />
			Replace if file exists? <input type="radio" name="replace" value="1"> Yes <input type="radio" name="replace" value="0" checked="checked"> No<br />
			Make this file executable? <input type="radio" name="executable" value="1"> Yes <input type="radio" name="executable" value="0" checked="checked"> No<br />
			<input type="submit" name="submit" value="Upload"><br /></form>';
			echo '<br /><a href="upload.php" class="link23">Cancel</a>';
			echo '<br /><a href="upload.php" class="link23">View Uploads</a>';
		}
	}
	else if ($pageid == 'delete')
	{
		if (isset($_REQUEST['upload']))
		{
			$upload = $_REQUEST['upload'];
			echo '<font class="c599">Are you sure you want to delete "' . $upload . '"?</font><form name="deleteupload" method="post" action="upload.php?id=delete2&upload=' . $upload . '" id="deleteconfirm"><input type="hidden" name="option" value="yes" /><input type="submit" value="Yes" /></form><form><input TYPE="BUTTON" VALUE="No" ONCLICK="window.location.href=\'upload.php\'"></form>';
		}
	}
	else if ($pageid == 'delete2')
	{
		if (isset($_REQUEST['upload']))
		{
			$upload = urlencode($_GET['upload']);

			$do = unlink("../uploads/" . $site_id . "/" . $upload);
			if($do=="1")
			{
				echo '<font class="c599">"' . $upload . '" was successfully deleted!</font>';
			}
			else
			{
				echo '<font class="c599">There was an error deleting "' . $upload . '"!</font>';
			}
			echo '<br /><a href="upload.php" class="link23">View Uploads</a>';
		}
	}
	else if ($pageid == 'options')
	{
		if (isset($_REQUEST['upload']))
		{
			$upload = urlencode($_REQUEST['upload']);
			$file = urldecode($upload);
			$fileENcoded = urlencode($upload);

			echo "<b>$file</b><br /><a href='http://" . $address . "/uploads/" . $site_id . "/" . $fileENcoded . "' class='link23' target='_blank'>View " . $file . "</a><br />
			<a href='upload.php?id=delete&amp;upload=" . $fileENcoded . "' class='link23'>Delete</a><br />
			<a href='upload.php?id=rename&amp;upload=" . $fileENcoded . "' class='link23'>Rename</a><br />
			<a href='upload.php?id=executable&amp;upload=" . $fileENcoded . "' class='link23'>Executable</a><br />
			<br /><a href=\"upload.php\" class=\"link23\">Back</a>";
		}
	}
	else if ($pageid == 'executable')
	{
		if (isset($_REQUEST['upload']))
		{
			$upload = urlencode($_REQUEST['upload']);

			if (isset($_REQUEST['executable']))
			{
				$exe = $_REQUEST['executable'];

				if ($exe==1)
				{
					chmod("../uploads/" . $site_id . "/" . $upload, 0755);
					echo "The file will now be executable.<br /><a href=\"upload.php\" class=\"link23\">Back</a>";
				}
				else
				{
					chmod("../uploads/" . $site_id . "/" . $upload, 0666);
					echo "The file will now not be executable.<br /><a href=\"upload.php\" class=\"link23\">Back</a>";
				}
			}
			else
			{
				$perms = substr(decoct(fileperms("../uploads/$site_id/$upload")),2);

				if ($perms ==  "0755")
				{
					$selected1 = " checked=\"checked\"";
					$selected2 = "";
				}
				else
				{
					$selected1 = "";
					$selected2 = " checked=\"checked\"";
				}

				echo '<form method="post" action="upload.php?id=executable&upload=' . $upload . '"><font class="c599">Make this file executable? <input type="radio"' . $selected1 . ' name="executable" value="1"> Yes <input type="radio"' . $selected2 . ' name="executable" value="0"> No</font>
				<input type="submit" name="submit" value="Change"><br /></form>';
				echo '<br /><a href="upload.php" class="link23">Cancel</a>';
			}
		}
	}
	else if ($pageid == 'rename')
	{
		if (isset($_REQUEST['upload']))
		{
			if (isset($_REQUEST['newname']))
			{
				$pickadifferentname = 'f';
				if ($_REQUEST['newname'] != null)
				{
					if (!preg_match('/\'/', $_REQUEST['newname']) && !preg_match('/\"/', $_REQUEST['newname']))
					{
						if (!preg_match('/\\\/', $_REQUEST['newname']) && !preg_match('/\//', $_REQUEST['newname']))
						{
							$upload = urlencode($_REQUEST['upload']);
							$picturename = urlencode($_REQUEST['newname']);
							$type = "";
							if (preg_match('/\.[^\.]*$/Di', urldecode($upload))) preg_match('/\.[^\.]*$/Di', urldecode($upload), $type);
							$picturename = $picturename . $type[0];
							$oldaddress = "../uploads/" . $site_id . "/" . $upload;
							$newaddress = "../uploads/" . $site_id . "/" . $picturename;

							if (file_exists("../uploads/" . $site_id . "/" . $picturename))
							{
								echo "<font class='c599'>\"" . $picturename . "\" already exists.</font>";
							}
							else
							{
								$do = rename($oldaddress, $newaddress);
								if($do=="1")
								{
									echo '<font class="c599">"' . $upload . '" was successfully renamed!</font>';
									$pickadifferentname = 't';
								}
								else
								{
									echo '<font class="c599">There was an error renaming "' . $upload . '"!</font>';
								}
							}
						}
						else
						{
							echo '<font class="c599">Don\'t have any slashes or backslashes in your name!</font>';
						}
					}
					else
					{
						echo '<font class="c599">Don\'t have quotes in your name!</font>';
					}
				}
				else
				{
					echo '<font class="c599">You need to type in a name!</font>';
				}

				$uploadname = $_REQUEST['upload'];
				echo '<br /><a href="upload.php" class="link23">View Uploads</a>';
				if ($pickadifferentname == 'f') echo '<br /><a href="upload.php?id=rename&amp;upload=' . $uploadname . '" class="link23">Pick a Different Name</a>';
			}
			else
			{
				$upload = $_REQUEST['upload'];
				$upload2 = preg_replace('/\.[^\.]*$/Di', '', $upload);
				echo '<font class="c599">Don\'t have quotes, slashes, or backslashes in your name.</font><br /><form name="renameupload" method="post" action="upload.php?id=rename&upload=' . $upload . '">Name: <input type="text" name="newname" id="newname" value="' . $upload2 . '">
				<input type="submit" name="submit" value="Rename"><br /></form>';
				echo '<br /><a href="upload.php" class="link23">Cancel</a>';
			}
		}
	}
}
else
{
	echo "<font class='c595'>Uploads</font><br />";
	echo "<a href='upload.php?id=upload' class='link23'>Upload Something</a><br /><br />";

	$files = array();
	$fileInfo = array();
	if (isset($_REQUEST['sort'])) $sort = $_REQUEST['sort'];
	else $sort = 'name';

	if ($handle = opendir('../uploads/' . $site_id . "/"))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != "..")
			{
				$files[] = $file;
				$stat = stat('../uploads/' . $site_id . "/" . $file);

				if (preg_match('/\.[^\.]*$/Di', $file)) preg_match('/\.[^\.]*$/Di', $file, $type);
				$type = preg_replace('/^\./Di', '', $type[0]);
				$type = strtoupper($type);

				foreach ($files as $key => $value)
				{
					if ($value == $file) $i = $key;
				}

				if ($sort == 'size') $fileInfo[] = array($i, ceil($stat[7]/1024));
				else if ($sort == 'name') $fileInfo[] = array($i, $file);
				else if ($sort == 'type') $fileInfo[] = array($i, $type);
				else if ($sort == 'lastModified') $fileInfo[] = array($i, $stat[9]);
				else $fileInfo[] = array($i, $stat[9]);
			}
		}
	}

	function sortFiles($a, $b)
	{
		global $sort;

		$a = strtolower($a[1]);
		$b = strtolower($b[1]);

		if ($a == $b) return 0;
		else if ($sort == 'name' || $sort == 'type') $returnVar = strcmp($a, $b);
		else if ($sort == 'lastModified') $returnVar = ($a > $b) ? -1 : +1;
		else $returnVar = ($a > $b) ? +1 : -1;

		return $returnVar;
	}

	usort($fileInfo, 'sortFiles');

	if(count($files) > 0)
	{
		echo 'Sort your uploads by ';

		if($sort == 'name') echo '<b>Name</b> ';
		else echo '<a href="upload.php?sort=name" class="link23">Name</a> ';

		if($sort == 'type') echo '<b>Type</b> ';
		else echo '<a href="upload.php?sort=type" class="link23">Type</a> ';

		if($sort == 'size') echo '<b>Size</b> ';
		else echo '<a href="upload.php?sort=size" class="link23">Size</a> ';

		if($sort == 'lastModified') echo '<b>Last Modified</b>';
		else echo '<a href="upload.php?sort=lastModified" class="link23">Last Modified</a>';

		echo '<br /><br />';

		foreach ($fileInfo as $value)
		{
			$file = urldecode($files[$value[0]]);
			$fileENcoded = urlencode($files[$value[0]]);

			echo "<b>" . $file . "</b> - <a href='upload.php?id=options&amp;upload=$fileENcoded'>Options</a><br />";
		}
	}
	else
	{
		echo '<font class="c599"><i>None</i></font>';
	}
}

site_footer();
?>
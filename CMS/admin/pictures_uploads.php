<?php
//the rest of this code is for a pages to insert uploads into the edit box (tiny_MCE)
if (isset($_REQUEST['image'])) $typetext = "Uploaded Pictures";
else $typetext = "Uploads";
echo '<html><head><title>' . $typetext . '</title>
<style type="text/css">
	a {color:blue;text-decoration:none;}
	a:hover {color:red;text-decoration:underline;}
</style>
</head><body>';
//this is javascript code to make it so that when the administrator
//clicks on the "Insert Image" or "Insert Upload" it closes the window and
//inserts the URL for the images or upload into the SRC box in another popup window
echo '<script type="text/javascript" src="http://' . $address . '/tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script><script type="text/javascript">
function insert_text(text)
{
	function mySubmit(text)
	{
		var URL = text;
		var win = tinyMCE.getWindowArg("window");

		// insert information now
		win.document.getElementById(tinyMCE.getWindowArg("input")).value = URL;

		// for image browsers: update image dimensions
		if (win.getImageData) win.getImageData();

		// close popup window
		tinyMCEPopup.close();
	}

	mySubmit(text);
}
</script>';

//checks if they are inserting an image or something else
if (isset($_REQUEST['image']))
{
	//open the "uploads" directory
	if ($handle = opendir('../uploads/'))
	{
		echo "<font class='c599'>Your Pictures:<br /><br /></font>";

		//loop through all the files in the folder
		while (false !== ($file = readdir($handle)))
		{
			//if the file isn't "." or ".." (which are go the the next directory up) display ceritan things
			if ($file != "." && $file != "..")
			{
				//display only images, no other uploads
				if (eregi('.gif$', $file) || eregi('.jpeg$', $file) || eregi('.jpg$', $file) || eregi('.png$', $file) || eregi('.png$', $file))
				{
					//if the images isn't too large display the actual image, otherwise display a link to the image.
					//we don't display large images because then the page would take forever to load
					if ((filesize('../uploads/' . $file) / 1024) < '50')
					{
						echo "<a href='http://" . $address . "/uploads/" . $file . "' class='link23' target='_blank'><img src='http://" . $address . "/uploads/" . $file . "' alt='" . $file . "' width='150px'></a><br />
						<a href='#' class='link23' onclick='insert_text(\"http://" . $address . "/uploads/" . $file . "\"); window.close();' style='cursor:pointer'>Insert Picture</a><br />
						<a href='http://" . $address . "/uploads/" . $file . "' class='link23' target='_blank'>View " . urldecode($file) . " Picture</a><br /><br />";
					}
					else
					{
						echo "Picture to large to<br />have on this page.<br />
						<a href='#' class='link23' onclick='insert_text(\"http://" . $address . "/uploads/" . $file . "\"); window.close();' style='cursor:pointer;'>Insert Picture</a><br />
						<a href='http://" . $address . "/uploads/" . $file . "' class='link23' target='_blank'>View " . urldecode($file) . " Picture</a><br /><br />";
					}
				}
			}
		}
	}
}
else
{
	if ($handle = opendir('../uploads/'))
	{
		//open the "uploads" directory
		echo "<font class='c599'>Your Uploads:<br /><br /></font>";
		//loop through all the uploads
		while (false !== ($file = readdir($handle)))
		{
			//if the file isn't "." or ".." (which are go the the next directory up) display ceritan things
			if ($file != "." && $file != "..")
			{
				//display everything in the directory ecxept images
				if (!eregi('.gif$', $file) && !eregi('.jpeg$', $file) && !eregi('.jpg$', $file) && !eregi('.png$', $file) && !eregi('.png$', $file))
				{
					echo "<a href='http://" . $address . "/uploads/" . $file . "' class='link23' target='_blank'>\"" . $file . "\"</a><br />
					<a href='#' class='link23' onclick='insert_text(\"http://" . $address . "/uploads/" . $file . "\"); return false; window.close();' window.close();' style='cursor:pointer'>Insert Upload</a><br />
					<a href='http://" . $address . "/uploads/pic/" . $file . "' class='link23' target='_blank'>View " . urldecode($file) . " Upload</a><br /><br />";
				}
			}
		}
	}
}
echo '</body></html>';
?>
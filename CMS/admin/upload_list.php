<?php
header("Content-Type: text/javascript");
require_once "../variables.php";

function sortFiles($a, $b)
{
	$a = strtolower($a);
	$b = strtolower($b);

	if ($a == $b) return 0;
	else $returnVar = strcmp($a, $b);

	return $returnVar;
}
function fileTypes($file='',$fileTypes=array())
{
    $isType = 0;

	if(count($fileTypes) > 0)
	{
		foreach ($fileTypes as $value)
		{
			if(eregi("(\." . $value . ")$",$file))
			{
				$isType = 1;
				break;
			}
		}

		return $isType;
	}

	return true;
}
function showList($fileTypes=array())
{
	global $site_id;

	if ($handle = opendir('../uploads/' . $site_id . '/'))
	{
		$uploads = array();
		$uploadText = null;

		while (false !== ($file = readdir($handle)))
		{
			//if the file isn't "." or ".." (which are go the the next directory up) display ceritan things
			if ($file != "." && $file != "..")
			{
				//display everything in the directory ecxept images
				if (fileTypes($file,$fileTypes))
				{
					$uploads[] = $file;
				}
			}
		}

		usort($uploads, 'sortFiles');
		$uploadCount = count($uploads)-1;

		foreach ($uploads as $key => $file)
		{
			$file = str_replace(' ', '%20', $file);
			$uploadText .= "['" . $file . "', '../uploads/" . $site_id . '/' . $file . "']";

			if ($key != $uploadCount) $uploadText .= ',';

			$uploadText .= "\n\t";
		}

		if ($uploadText != null) return $uploadText;
		else return false;
	}

	return false;
}

if(isset($_REQUEST['id'])) $id = $_REQUEST['id'];
else $id = null;

if ($id=='image')
{
echo "var tinyMCEImageList = new Array(
	" . showList(array('gif','jpg','jpeg','jpe','jfif','bmp', 'dib', 'png','ico', 'tiff', 'emf')) . "
);";
}
else if ($id=='media')
{
echo "var tinyMCEMediaList = [
	" . showList(array('swf', 'avi', 'mov', 'rm', 'ram', 'dcr')) . "
];";
}
else
{
echo "var tinyMCELinkList = new Array(
	" . showList(array()) . "
);";
}
?>
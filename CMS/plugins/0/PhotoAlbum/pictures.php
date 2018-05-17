<?php
if (isset($_POST['xml']))
{
	if(setting_set("xml", $_POST['xml'])) echo "XML file has been updated.<br />";
	else echo "An error occured while updating XML file.<br />";
}

$xml = setting_get("xml");

if ($xml == null)
{
	$xml = "<pictures>
<album id=\"1\" name=\"Test Photo Album\" desc=\"Here is my great description...\" thumb=\"images/thumbnails/does_not_exist.jpg\">
	<picture name=\"name of photo (in bold above it)\" alt=\"alternate text\" src=\"images/folder/does_not_exist.jpg\" width=\"350px\">This is some text below the picture...</picture>
</album>
</pictures>";
}

echo '<form method="post" action="<url:current>"><input type="submit" value="Save"><textarea name="xml" rows="15" style="width:100%">' . htmlspecialchars($xml) . '</textarea><br /><input type="submit" value="Save"></form>';
?>
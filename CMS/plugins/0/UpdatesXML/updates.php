<?php
if (isset($_POST['updates']))
{
	if(setting_set("updates", $_POST['updates'])) echo "XML file has been updated.<br />";
	else echo "An error occured while updating XML file.<br />";
}

$updates = setting_get("updates");

echo '<form method="post" action="<url:current>"><input type="submit" value="Save"><textarea name="updates" rows="15" style="width:100%">' . htmlspecialchars($updates) . '</textarea><br /><input type="submit" value="Save"></form>';
?>
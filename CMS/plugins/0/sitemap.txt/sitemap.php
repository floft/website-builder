<?php
if (isset($_POST['urls']))
{
	if(setting_set("urls", $_POST['urls'])) echo "The file has been updated.<br />";
	else echo "An error occured while updating the file.<br />";
}

$urls = setting_get("urls");

echo '<form method="post" action="<url:current>"><input type="submit" value="Save"><textarea name="urls" rows="15" style="width:100%">' . htmlspecialchars($urls) . '</textarea><br /><input type="submit" value="Save"></form>';
?>
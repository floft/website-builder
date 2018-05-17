<?php
echo "<div class=\"title\">Updates</div><br />";

$updates = setting_get("updates");

if ($xml = @simplexml_load_string($updates))
{
	$updates = $xml->day;

	foreach ($updates as $update)
	{
		echo "<b>{$update['date']}</b><ul>";

		foreach ($update as $u)
		{
			echo "<li>$u</li>";
		}

		echo "</ul>";
	}
} else echo "Error loading updates...";
?>
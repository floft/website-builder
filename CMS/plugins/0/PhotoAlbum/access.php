<?php
if ($xml = @simplexml_load_string(setting_get("xml")))
{
	if (isset($_GET['id']))
	{
		foreach ($xml->album as $a)
		{
			if (strtolower($a["id"]) == strtolower($_GET['id']))
			{
				$album = $a;
				break;
			}
		}


		if (isset($album))
		{
			echo "<div class=\"title\">{$album["name"]}</div>";

			//description
			if (isset($album["desc"]) && !empty($album["desc"]))
			{
				echo $album["desc"] . "<br /><br />";
			}

			foreach ($album->picture as $picture)
			{
				//description
				if (empty($picture)) $text = null;
				else $text = $picture . "<br />";

				//name
				if (empty($picture["name"])) $title = null;
				else $title = "<b>" . $picture["name"] . "</b><br />";

				//width
				if (isset($picture["width"]) && !empty($picture["width"])) $width = " width=\"{$picture["width"]}\"";
				else $width = null;

				//height
				if (isset($picture["height"]) && !empty($picture["height"])) $height = " height=\"{$picture["height"]}\"";
				else $height = null;

				//smaller image if available
				$split = split("\.", parse_url($picture["src"], PHP_URL_PATH));
				if (file_exists($split[0] . "_small." . $split[1]))
				{
					$small_img = $split[0] . "_small." . $split[1];
				}
				else
				{
					$small_img = $picture["src"];
				}

				//the anchor for the image
				$split = split("/", $split[0]);
				$anchor = $split[count($split)-1];

				echo "<div><a name=\"$anchor\"></a>$title<a href=\"{$picture["src"]}\"><img src=\"$small_img\" alt=\"{$picture["alt"]}\" border=\"0\"$width$height /></a><br />$text<br /></div>";
			}

			echo "<a href=\"Pictures\">Back</a>";
		}
		else
		{
			echo "Album doesn't exist...";
		}
	}
	else
	{
		echo "<div class=\"title\">Pictures</div><br /><table width=\"100%\">";

		//# of albums
		$count = count($xml->album);

		for ($i=0,$loops=0;$i<$count;$i++)
		{
			if (!isset($xml->album[$i]["hide"]))
			{
				//determine position
				if (($loops+1)%2==0)
				{
					$start = "<td align=\"center\">";
					$end = "</td></tr>";
				}
				else
				{
					$start = "<tr><td align=\"center\">";
					$end = "</td>";
				}

				echo "$start<a href=\"Pictures?id={$xml->album[$i]["id"]}\"><img src=\"{$xml->album[$i]["thumb"]}\" alt=\"\" border=\"0\" style=\"text-decoration:none\" width=\"150px\" /></a><br /><a href=\"Pictures?id={$xml->album[$i]["id"]}\">{$xml->album[$i]["name"]}</a>$end";

				$loops++;
			}
		}

		//add extra cell if needed
		if (($loops+1)%2==0)
		{
			echo "<td></td></tr>";
		}

		echo "</table>";
	}
}
else
{
	echo "Error getting pictures...";
}
?>
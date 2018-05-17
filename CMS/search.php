<?php
$textbox_size = 65;
$displayselect = true;
$xtraelements = null;

if (isset($_REQUEST['size']) && is_numeric($_REQUEST['size']))
{
	$textbox_size = $_REQUEST['size'];
	$xtraelements .= "<input type=\"hidden\" name=\"size\" value=\"$textbox_size\" />";
	$xtraquery .= "&amp;size=$textbox_size";
}
if (isset($_REQUEST['results']) && $_REQUEST['results'] == "false")
{
	$displayselect = false;
	$xtraelements .= "<input type=\"hidden\" name=\"results\" value=\"false\" />";
	$xtraquery .= "&amp;results=false";
}

if ($databasedata['prettyurls']==1)
{
	$searchURL = "search?";
	$searchAction = "search";
}
else
{
	$searchURL = "index.php?id=search&amp;";
	$searchAction = "index.php";
}

function returnCheckedVar($num, $match)
{
	if ($num==$match) return ' selected=\'selected\'';
	else return '';
}
function sortSelectedResultText($a, $b)
{
    if ($a[0] == $b[0]) {
        return 0;
    }
    return ($a[0] < $b[0]) ? -1 : 1;
}
function getSelectedResultText($num)
{
	global $displayselect;

	if ($displayselect==true)
	{
		//create array
		$results = array();
		//create array contents
		$results[] = array(10, "<option value=\"10\"" . returnCheckedVar(10, $num) . ">Show 10 Results</option>");
		$results[] = array(20, "<option value=\"20\"" . returnCheckedVar(20, $num) . ">Show 20 Results</option>");
		$results[] = array(30, "<option value=\"30\"" . returnCheckedVar(30, $num) . ">Show 30 Results</option>");
		$results[] = array(40, "<option value=\"40\"" . returnCheckedVar(40, $num) . ">Show 40 Results</option>");
		$results[] = array(50, "<option value=\"50\"" . returnCheckedVar(50, $num) . ">Show 50 Results</option>");
		$results[] = array(60, "<option value=\"60\"" . returnCheckedVar(60, $num) . ">Show 60 Results</option>");
		$results[] = array(70, "<option value=\"70\"" . returnCheckedVar(70, $num) . ">Show 70 Results</option>");
		$results[] = array(80, "<option value=\"80\"" . returnCheckedVar(80, $num) . ">Show 80 Results</option>");
		$results[] = array(90, "<option value=\"90\"" . returnCheckedVar(90, $num) . ">Show 90 Results</option>");
		$results[] = array(100, "<option value=\"100\"" . returnCheckedVar(100, $num) . ">Show 100 Results</option>");

		if($num != 10 && $num != 20 && $num != 30 && $num != 40 && $num != 50 && $num != 60 && $num != 70 && $num != 80 && $num != 90 && $num != 100)
		{
			$results[] = array($num, "<option value=\"$num\"" . returnCheckedVar($num, $num) . ">Show $num Results</option>");
		}

		usort($results, "sortSelectedResultText");

		$text=null;
		foreach ($results as $value)
		{
			$text .= $value[1];
		}

		return " <select name=\"results\">" . $text . "</select> ";
	}
	else return null;
}

function SpellCheck($input)
{
	function myCorrectWords($word)
	{
		$inArray = 0;

		$words = array();

		//you can add more words here
		$words[] = 'floft';

		foreach ($words as $value)
		{
			if(strtolower($word) == strtolower($value)) $inArray = 1;
		}

		return $inArray;
	}

	$sentence = trim($input);
	$spell = pspell_new("en", "american");
	$words = explode(" ", $sentence);
	$output = false;

	foreach($words as $word) {
		if (pspell_check($spell, $word)) {
			$output .= $word . ' ';
		} else if (!myCorrectWords($word)) {
			$suggestions = pspell_suggest($spell, $word);
			if (count($suggestions)) {

			   $similarities = array();

				foreach($suggestions as $suggestion) {
					/// COMMENT TWO
					if (metaphone($word) != metaphone($suggestion)) continue;
					similar_text($word, $suggestion, $similarity);
					$similarity = round($similarity, 2);
					$similarities[$suggestion] = $similarity;
				}

				arsort($similarities);
				$output .= '<b>' . $suggestions[0] . '</b> ';
			} else {
				/// COMMENT THREE
				$output .= $word . ' ';
			}
		}
		else
		{
			$output .= $word . ' ';
		}
	}

	$output = trim($output);

	if (strtolower($output) != strtolower($input)) return $output;
	else return false;
}

echo '<b>Search</b><br />';

//check the user has search for anything
if (isset($_GET['q']) && preg_replace('/\s/', '', stripslashes(urldecode($_GET['q']))) != null)
{
	//get what the user has searched for
	$q = stripslashes(rawurldecode($_GET['q']));
	//number of results per page
	if(isset($_REQUEST['results']) && is_numeric($_REQUEST['results']) && $_REQUEST['results'] > 0 && $_REQUEST['results'] <= 500) $resultNum = stripslashes(rawurldecode($_REQUEST['results']));
	else $resultNum = 10;

	if(substr($q, -1, 1) == ' ') $q = substr($q, 0, -1);

	//select all the pages from the database that contain what they searched for
	$query="SELECT *, MATCH (pageName, pageContents, `meta-keywords`, `meta-description`) AGAINST ('" . addslashes($q) . "' IN BOOLEAN MODE) AS Relevance FROM `pages` where MATCH (pageName, pageContents, `meta-keywords`, `meta-description`) AGAINST ('" . addslashes($q) . "' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";
	$result=mysql_query($query);
	$end=mysql_numrows($result);

	$trueend = $end;

	//if there are more than 10 results, make the end 10, and create multiple pages.
	if ($end > $resultNum)
	{
		$end = $resultNum;
	}

	//if there is more than one page, the $start variable will say what result to start at
	if (isset($_GET['start']))
	{
		//get the start number
		$start = $_GET['start'];

		//find the number of pages
		$pages = ceil($trueend / $resultNum);
		//find out what page the user is currently on
		$currentpage = ($start / $resultNum) + 1;
		//make a variable that says what page the user is on
		$text195017345 = 'Page ' . $currentpage;

		//I don't remember what this code does
		if ($start + $resultNum <= $trueend)
		{
			$end = $start + $resultNum;
		}
		else if ($start + $resultNum > $trueend)
		{
			$end = $trueend;
		}
	}
	else
	{
		$start = 0;
		//find out how many pages there are
		$pages = ceil($trueend / $resultNum);
		//find out what page the user is currently on
		$currentpage = ($start / $resultNum) + 1;
		//make a variable that says what page the user is on
		$text195017345 = 'Page ' . $currentpage;
	}

	//if there is only one result, display "Result" instead of "Results"
	if ($trueend <= 1) $results_result = 'Result';
	else $results_result = 'Results';

	//if there results echo the search again box and search info
	if ($end >= 1)
	{
		echo "<br /><form METHOD='get' action='$searchAction'>$xtraelements<input type='hidden' name='id' value='search'><input type='text' name='q' size='$textbox_size' maxlength='2048' value='" . htmlspecialchars($q, ENT_QUOTES) . "'>" . getSelectedResultText($resultNum) . "<input type='submit' value='Search'></form>";

		if (preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])) echo '<br />';

		echo '<b>' . $trueend . '&nbsp;' . $results_result . ',&nbsp;' . $text195017345 . '</b><br />Search Results for <i>' . htmlspecialchars($q, ENT_QUOTES) . '</i>:<br />';

		if ($correctedWord = SpellCheck($q)) echo '<br />Did you mean <a href="' . $searchURL . 'q=' . urlencode(strip_tags($correctedWord)) . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '">' . $correctedWord . '</a>?<br />';
	}
	else if ($end <= 0)
	{
		//if there aren't any results say so
		echo "<br /><form METHOD='get' action='$searchAction'>$xtraelements<input type='hidden' name='id' value='search'><input type='text' name='q' size='$textbox_size' maxlength='2048' value='" . htmlspecialchars($q, ENT_QUOTES) . "'>" . getSelectedResultText($resultNum) . "<input type='submit' value='Search'></form><br />";

		if ($correctedWord = SpellCheck($q)) echo 'Did you mean <a href="' . $searchURL . 'q=' . urlencode(strip_tags($correctedWord)) . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '">' . $correctedWord . '</a>?<br />';

		echo "No search results found for <i>" .  htmlspecialchars($q, ENT_QUOTES) . "</i>.";
	}

	//two variables that I didn't have to put in,
	//I could have just replaced the $startplus10 with "($start+10)"
	//and I could have replaced the $startminus10 with "($start-10)"
	$startplus10 = $start + $resultNum;
	$startminus10 = $start - $resultNum;
	$endplus10 = $end + $resultNum;

	//loop through all the results
	while ($start < $end)
	{
		//get the page information
		$title=mysql_result($result,$start,"pageName");
		$pageURL=mysql_result($result,$start,"pageURL");
		$description=mysql_result($result,$start,"pageContents");
		$metaDescription=@mysql_result($result,$start,"meta-description");
		$pageId=mysql_result($result,$start,"pageId");
		if ($databasedata['prettyurls']==1)
		{
			if ($pageURL == '1') $address2 = 'http://' . $address . '/Home';
			else $address2 = 'http://' . $address . '/' . $pageURL;
		}
		else $address2 = 'http://' . $address . '/index.php?id=' . $pageId;
		$address2 = preg_replace('/(?<!&amp;)&/', '&amp;', $address2);

		if ($metaDescription != null)
		{
			$description = $metaDescription;
		}
		else
		{
			//this code is to make the description not be too long, and to not have the formatting

			$description = preg_replace('/\<br \/\>|\<br\>|\<p\>|\<\/p\>|\<div\>|\<\/div\>/', "\n", $description);

			/*$description = preg_replace('/\<br \/\>/', '
			', $description);
			$description = preg_replace('/\<br\\>/', '
			', $description);
			$description = preg_replace('/\<\/p\>/', '
			', $description);
			$description = preg_replace('/\<p\>/', '
			', $description);*/
			$description = strip_tags($description);
			$description = substr($description, 0, 200);
			$description = preg_replace('/\r\t\n/', ' ', $description);
			$description .= '<font size="4">...</font>';
		}

		//echo the result
		echo "<p><b><a href='$address2' class='link23'>$title</a></b><br />$description<br /><font size='2'><a href='$address2' class='link23'>$address2</a></font></p>";

		$start++;
	}

	$q = urlencode($q);

	//if there is more than one page, echo links to the other pages
	if ($trueend > $resultNum)
	{
		//if this is the first page
		if ($start <= $resultNum && $trueend > $resultNum)
		{
			echo '<font color="gray">Previous Page</font>&nbsp;';

			$pages2 = 1;
			$pages3 = 0;

			//loop through the number of pages and echo them, like 1 2 3... they are links to other pages
			while ($pages2 <= $pages)
			{
				if ($pages2 != $currentpage) echo '&nbsp;<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $pages3 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">' . $pages2 . '</a>&nbsp;';
				else echo '&nbsp;<font color="gray">' . $pages2 . '</font>&nbsp;';

				$pages2++;
				$pages3 = $pages3 + $resultNum;
			}

			echo '&nbsp;<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $startplus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">Next Page</a>';
		}
		else if ($start > $resultNum && $end + 1 <= $trueend)
		{
			//if this is not the first or last page
			echo '<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $startminus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">Previous Page</a>&nbsp;';

			$pages2 = 1;
			$pages3 = 0;

			//loop through the number of pages and echo them, like 1 2 3... they are links to other pages
			while ($pages2 <= $pages)
			{
				if ($pages2 != $currentpage) echo '&nbsp;<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $pages3 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">' . $pages2 . '</a>&nbsp;';
				else echo '&nbsp;<font color="gray">' . $pages2 . '</font>&nbsp;';

				$pages2++;
				$pages3 = $pages3 + $resultNum;
			}

			echo '&nbsp;<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $startplus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">Next Page</a>';
		}
		else if ($start > $resultNum && $end <= $trueend)
		{
			//if this is the last page
			echo '<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $startminus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">Previous Page</a>&nbsp;';

			$pages2 = 1;
			$pages3 = 0;

			//loop through the number of pages and echo them, like 1 2 3... they are links to other pages
			while ($pages2 <= $pages)
			{
				if ($pages2 != $currentpage) echo '&nbsp;<a href="' . $searchURL . 'q=' . $q . '&amp;start=' . $pages3 . '&amp;results=' . addslashes(urlencode($resultNum)) . $xtraquery . '" class="link23">' . $pages2 . '</a>&nbsp;';
				else echo '&nbsp;<font color="gray">' . $pages2 . '</font>&nbsp;';

				$pages2++;
				$pages3 = $pages3 + $resultNum;
			}

			echo '&nbsp;<font color="gray">Next Page</font>';
		}
	}
}
else
{
	//number of results per page
	if(isset($_REQUEST['results']) && is_numeric($_REQUEST['results']) && $_REQUEST['results'] > 0 && $_REQUEST['results'] <= 500) $resultNum = stripslashes(rawurldecode($_REQUEST['results']));
	else $resultNum = 10;

	//if the user hasn't searched for anything, display the search form
	echo "<br /><form METHOD='get' action='$searchAction'>$xtraelements<input type='hidden' name='id' value='search'><input type='text' name='q' size='$textbox_size' maxlength='2048'>" . getSelectedResultText($resultNum) . "<input type='submit' value='Search'></form>";
}
?>
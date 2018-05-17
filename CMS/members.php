<?php
//check if the user is logged in
if (isset($_SESSION["member"]))
{
	//select all the users that are members
	$query = "Select username from `users` where member = 1 and siteId = \"$site_id\" order by loginId Asc";
	$result = mysql_query($query) or die ('Connect failed!');
	$num=mysql_numrows($result);

	//if there is only one ouput "member" instead of "members" to the page
	if ($num == 1) $contents = '<h2>Members List:</h2>There is ' . $num . ' member.<br /><br />';
	else $contents = '<h2>Members List:</h2>There are ' . $num . ' members.<br /><br />';

	//loop through the members adding their username to the $contents variable that is later (in the index.php file) printed to the screen.
	for ($i = $num - 1; $i >= 0; $i--)
	{
		// get username from database
		$username = mysql_result($result, $i,"username");

		if ($username != "")
		{
			//add the the user's username to the $contents variable
			$contents .= '<a href="' . (($databasedata['prettyurls']==1)?"profile?user=":"index.php?id=profile&amp;user=") . $username . '" class="link23" target="_top">' . $username . '</a>';
		}
	}
}
?>
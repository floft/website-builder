<?php
require_once "design.php";
site_header("Edit Footer");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

if (isset($_POST['contents']))
{
	$contents = $_POST['contents'];

	$query = "Update `settings` Set value = '$contents' where name = 'footer' and siteId = \"$site_id\"";
	$result = mysql_query($query) or die ('Putting data into database failed!');

	echo '<font class="c595">Edit Footer</font><br />Click <a href="editfooter.php">Here</a> to continue editing.';
	echo '<script type="text/javascript">window.top.location="editfooter.php";</script>';
}
else
{
	echo '<font class="c595">Edit Footer</font><br />';

	if ($databasedata['editHTML'] == 0)
	{
		echo '<script language="javascript" type="text/javascript" src="../tinymce/jscripts/tiny_mce/tiny_mce_gzip.js"></script>
		<script type="text/javascript">
		<!--
		tinyMCE_GZ.init({
			plugins : "spellchecker,inlinepopups,advimage,advlink,zoom,media,searchreplace,print,contextmenu,paste,directionality",
			themes : "o2k7,simple,advanced",
			languages : "en",
			disk_cache : false,
			debug : false
		});

		function beforeUnload() {
			if(window.top.documentChanged == true && window.saving != true) { return "If you choose to navigate away from this page, your work will NOT be saved."; }
		}
		//window.onbeforeunload = beforeUnload;
		//frames[0].getElementById("tinymce").onclick="window.top.documentChanged=true";
		// -->
		</script>
		<!-- Needs to be seperate script tags! -->
		<script language="javascript" type="text/javascript">
		<!--
		tinyMCE.init({
			skin : "o2k7",
			mode : "exact",
			elements : "contents",
			theme : "advanced",
			gecko_spellcheck : true,
			spellchecker_languages : "+English=en",
			plugins : "spellchecker,inlinepopups,advimage,advlink,zoom,media,searchreplace,print,contextmenu,paste,directionality",
			theme_advanced_buttons1_add : "separator,undo,redo,link,unlink,separator,forecolor,backcolor,separator,spellchecker",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			external_link_list_url : "upload_list.php",
			external_image_list_url : "upload_list.php?id=image",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			spellchecker_rpc_url : "../tinymce/jscripts/tiny_mce/plugins/spellchecker/rpc.php"
		});
		-->
		</script>';
	}

	echo '<form method="post" action="editfooter.php">
		<textarea name="contents" cols="85" rows="15">' . htmlspecialchars($databasedata['footer']) . '</textarea>
		<br /><input type="submit" value="Save" onclick="window.saving=\'true\';">
	</form>';
}
mysql_close();

site_footer();
?>
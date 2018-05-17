<?php
require_once "design.php";
site_header("Edit Login Form");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

if (isset($_POST['contents']))
{
	$contents = $_POST['contents'];
	$name2 = $_POST['name'];

	$query = "Update `settings` Set value = '$contents' where name = 'login_code' and siteId = '$site_id'";
	$result = mysql_query($query) or die ('Putting data into database failed!');

	echo '<font class="c595">Create Account Text</font><br />Click <a href="editlogintext.php">Here</a> to continue editing.';
	echo '<script type="text/javascript">window.top.location="editlogintext.php";</script>';
}
else
{
	$edit = $_REQUEST['edit'];

	$query = "Select value from `settings` where name = 'login_code' and siteId = '$site_id'";
	$result = mysql_query($query) or die ('Getting data from database failed!');
	$contents = mysql_result($result, 0, "value");

	if ($databasedata['prettyurls']==1) echo '<font class="c595">Edit Login Form</font><br /><a href="../loginform" target="_blank">View Page</a><br />';
	else echo '<font class="c595">Edit Login Form</font><br /><a href="../index.php?id=loginform" target="_blank">View Page</a><br />';

	if ($databasedata['editHTML']==0)
	{
		echo '<script language="javascript" type="text/javascript" src="../tinymce/jscripts/tiny_mce/tiny_mce_gzip.js"></script>
			<script language="javascript" type="text/javascript" src="../tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>
			<script type="text/javascript">
			<!--
			tinyMCE_GZ.init({
				plugins : "spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,media,"+
					"searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
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
				plugins : "spellchecker,inlinepopups,table,save,advhr,advimage,advlink,insertdatetime,zoom,media,searchreplace,print,contextmenu,paste,directionality,fullscreen",
				theme_advanced_buttons1_add_before : "newdocument,separator",
				theme_advanced_buttons1_add : "fontselect,fontsizeselect",
				theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor",
				theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
				theme_advanced_buttons3_add_before : "tablecontrols,separator",
				theme_advanced_buttons3_add : "media,advhr,separator,print,separator,ltr,rtl,separator,fullscreen,separator,spellchecker",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_path_location : "bottom",
				theme_advanced_statusbar_location : "bottom",
				plugi2n_insertdate_dateFormat : "%m-%d-%Y",
				plugi2n_insertdate_timeFormat : "%H:%M",
				external_link_list_url : "upload_list.php",
				external_image_list_url : "upload_list.php?id=image",
				media_external_list_url : "upload_list.php?id=media",
				//file_browser_callback : "fileBrowserCallBack",
				paste_use_dialog : false,
				theme_advanced_resizing : true,
				theme_advanced_resize_horizontal : false,
				paste_auto_cleanup_on_paste : true,
				paste_convert_headers_to_strong : false,
				paste_strip_class_attributes : "all",
				paste_remove_spans : false,
				paste_remove_styles : false,
				spellchecker_rpc_url : "../tinymce/jscripts/tiny_mce/plugins/spellchecker/rpc.php"
			});

			function fileBrowserCallBack(field_name, url, type, win) {
				//alert("Field_Name: " + field_name + "\nURL: " + url + "\nType: " + type + "\nWin: " + win); // debug/testing

				var win = tinyMCE.getWindowArg("window");
				var input = tinyMCE.getWindowArg("input");
				var res = tinyMCE.getWindowArg("resizable");
				var inline = tinyMCE.getWindowArg("inline");

				// newer writing style of the TinyMCE developers for tinyMCE.openWindow
				tinyMCE.openWindow({
					file: "http://' . $address . '/admin/index.php?page=pictures_uploads&" + "?" + type,
					title: "File Browser",
					width: 420,  // Your dimensions may differ - toy around with them!
					height: 400,
					close_previous: "no"
				}, {
					window: win,
					input: field_name,
					resizable: "yes",
					inline: "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
					editor_id: tinyMCE.selectedInstance.editorId
				});
				return false;
			}
		// -->
		</script>';
	}

	echo '<form method="post" action="editlogintext.php">
		<input type="submit" value="Save" onclick="window.saving=\'true\';"><br /><br />
		<textarea name="contents" rows="15" style="width:100%">' . htmlspecialchars($contents) . '</textarea>
		<br /><input type="submit" value="Save" onclick="window.saving=\'true\';">
	</form>';
}
mysql_close();

site_footer();
?>
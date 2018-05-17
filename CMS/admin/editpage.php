<?php
require_once "design.php";
site_header("Edit Page");

$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);

if (isset($_REQUEST['edit']) && $_REQUEST['edit'] != "")
{
	if (isset($_POST['contents']))
	{
		$edit = $_REQUEST['edit'];
		$contents = $_POST['contents'];
		$name2 = $_POST['name'];
		$design = $_POST['design_yes_no'];
		$menuTXT = $_POST['menuTXT'];
		$metaKeywords = $_POST['metaKeywords'];
		$metaDescription = $_POST['metaDescription'];
		if (isset($_POST['pageURL'])) $pageURL = $_POST['pageURL'];

		$query = "Select pageName,pageURL from `pages` where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$name = mysql_result($result, 0, "pageName");
		$oldPageURL = mysql_result($result, 0, "pageURL");

		//update page url
		if ($databasedata['prettyurls'] == 1 && isset($pageURL) && $edit != 1 && $pageURL != $oldPageURL)
		{
			$query = "Select pageId from `pages` where pageURL = '" . $pageURL . "' and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);

			if ($pageURL == "search" || $pageURL == "account" || $pageURL == "profile" || $pageURL == "members" || $pageURL == "login" || $pageURL == "logout" || $pageURL == "create" || $pageURL == "loginform")
			{
				$error .= "The Page URL you specified is already used by the Floft Website Builder.<br />";
			}
			else if ($num == 0 && $pageURL != "Home")
			{
				if (preg_match("/^[A-Za-z0-9!&\*+=_|~\-\(\)\.]{1,}$/", $pageURL))
				{
					$query = "Update `pages` Set pageURL = '" . $pageURL . "' where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Update failed!');
				}
				else
				{
					$error .= "The Page URL can only have letters and numbers in it along with these characters: . & * + = _ | ~ - ( ) ! <br />";
				}
			}
			else
			{
				$error .= "The Page URL is already in use.<br />";
			}
		}

		//update meta description and keywords
		$query = "Update `pages` Set menuTXT = '" . $menuTXT . "' where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data into database failed!');
		$query = "Update `pages` Set `meta-keywords` = '" . $metaKeywords . "' where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data into database failed!');
		$query = "Update `pages` Set `meta-description` = '" . $metaDescription . "' where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data into database failed!');
		$query = "Update `pages` Set design = '$design' where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Putting data into database failed!');
		$query = "Update `pages` Set pageContents = '$contents' where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Putting data into database failed!');

		//if it isn't the home page, allow changes made to name of page
		if ($name != $name2 && $edit != 1)
		{
			$query = "Update `pages` Set pageName = '$name2' where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Putting data into database failed!');
		}

		if ($_POST['link_yes_no'] == 1 && $_POST['members_yes_no'] == 1)
		{
			$query = "Select pageId from `links` where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);

			$query = "Select membersPage from `pages` where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$membersPage = mysql_result($result, 0, 'membersPage');

			if ($num < 1)
			{
				$query = "Select linkId from `links` where membersPage = 1 and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data from database failed!');
				$num = mysql_numrows($result);
				if ($num > 0) $idnew = mysql_result($result, $num-1, "linkId");
				else $idnew = null;

				$members_yes_no = $_POST['members_yes_no'];

				if ($idnew != null) $idnew = $idnew + 1;
				else $idnew = 1;

				$query = "Select linkId from `links` where linkId = 1 and membersPage = 1 and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data from database failed!');
				$num2 = mysql_numrows($result);
				if ($num2 == 0) $idnew = 1;

				$query = "Insert into `links` (siteId, linkId, pageId, membersPage) VALUES ('$site_id', '$idnew', '$edit', '$members_yes_no')";
				mysql_query($query) or die ('Getting data into database failed!');

				if ($membersPage == 0)
				{
					$query = "Update `links` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Getting data into database failed!');

					$query = "Update `pages` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Getting data into database failed!');
				}
			}
			else if ($membersPage == 0)
			{
				$query = "Select linkId from `links` where membersPage = 1 and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data from database failed!');
				$num = mysql_numrows($result);
				if ($num > 0) $idnew = mysql_result($result, $num-1, "linkId");
				else $idnew = null;

				$members_yes_no = $_POST['members_yes_no'];

				if ($idnew != "") $idnew = $idnew + 1;
				else $idnew = 1;

				$query = "Update `links` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');

				$query = "Update `links` Set linkId = '$idnew' where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');

				$query = "Update `pages` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');
			}
			else if ($membersPage == 1)
			{
				$query = "Update `links` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');

				$query = "Update `pages` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');
			}
		}
		else if ($_POST['link_yes_no'] == 1 && $_POST['members_yes_no'] == 0)
		{
			$query = "Select pageId, membersPage from `links` where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);

			$query = "Select membersPage from `pages` where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$membersPage = mysql_result($result, 0, 'membersPage');

			if ($num < 1)
			{
				$query = "Select linkId from `links` where membersPage = 0 and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data from database failed!');
				$num = mysql_numrows($result);
				if ($num > 0)$idnew = mysql_result($result, $num-1, "linkId");
				else $idnew = null;

				if ($idnew != "") $idnew = $idnew + 1;
				else $idnew = 1;

				$query = "Insert into `links` (siteId, linkId, pageId) VALUES ('$site_id', '$idnew', '$edit')";
				$result = mysql_query($query) or die ('Putting data into database failed!');
			}
			else if ($membersPage == 1)
			{
				$query = "Select linkId from `links` where membersPage = 0 and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data from database failed!');
				$num = mysql_numrows($result);
				if ($num > 0) $idnew = mysql_result($result, $num-1, "linkId");
				else $idnew = null;

				$members_yes_no = $_POST['members_yes_no'];

				if ($idnew != "") $idnew = $idnew + 1;
				else $idnew = 1;

				$query = "Update `links` Set membersPage = 0 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');

				$query = "Update `links` Set linkId = '$idnew' where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');

				$query = "Update `pages` Set membersPage = 0 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');
			}
		}
		else if ($_POST['link_yes_no'] == 0 && $_POST['members_yes_no'] == 1)
		{
			$query = "Select pageId from `links` where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);

			if ($num == 1)
			{
				if ($edit != 1)
				{
					$query = "Update `pages` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Getting data into database failed!');

					$query = "Delete from `links` where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Deleting data from database failed!');
				}
				else
				{
					$error .= 'You have to have a link to the home page on your menubar!<br />';
				}
			}
			else
			{
				$query = "Update `pages` Set membersPage = 1 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');
			}
		}
		else if ($_POST['link_yes_no'] == 0 && $_POST['members_yes_no'] == 0)
		{
			$query = "Select pageId from `links` where pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$num = mysql_numrows($result);

			if ($num == 1)
			{
				if ($edit != 1)
				{
					$query = "Update `pages` Set membersPage = 0 where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Getting data into database failed!');

					$query = "Delete from `links` where pageId = " . $edit . " and siteId = \"$site_id\"";
					$result = mysql_query($query) or die ('Deleting data from database failed!');
				}
				else
				{
					$error .= 'You have to have a link to the home page on your menubar!<br />';
				}
			}
			else
			{
				$query = "Update `pages` Set membersPage = 0 where pageId = " . $edit . " and siteId = \"$site_id\"";
				$result = mysql_query($query) or die ('Getting data into database failed!');
			}
		}

		if (isset($error) && $error != null) echo $error . "<a href='javascript:history.back(-1);'>Back</a>";
		else echo '<script type="text/javascript">window.top.location="editpage.php?edit=' . $edit . '";</script> <font class="c595">Edit a Page</font><br />Click <a href="editpage.php?edit=' . $edit . '">Here</a> to continue editing.';
	}
	else
	{
		$edit = $_REQUEST['edit'];

		$query = "Select pageContents, pageName, pageURL, menuTXT, design, membersPage, `meta-description`, `meta-keywords` from `pages` where pageId = " . $edit . " and siteId = \"$site_id\"";
		$result = mysql_query($query) or die ('Getting data from database failed!');
		$contents = mysql_result($result, 0, "pageContents");
		$design_yes_no = mysql_result($result, 0, "design");
		$name = mysql_result($result, 0, "pageName");
		$pageURL = mysql_result($result, 0, "pageURL");
		$menuTXT = htmlentities(mysql_result($result, 0, "menuTXT"), ENT_COMPAT);
		$members_yes_no = mysql_result($result, 0, "membersPage");
		$metaDescription = htmlentities(mysql_result($result, 0, "meta-description"), ENT_COMPAT);
		$metaKeywords = htmlentities(mysql_result($result, 0, "meta-keywords"), ENT_COMPAT);

		if ($members_yes_no == 1)
		{
			$query = "Select pageId from `links` where membersPage = 1 and pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$link_yes_no = mysql_numrows($result);
		}
		else
		{
			$query = "Select pageId from `links` where membersPage = 0 and pageId = " . $edit . " and siteId = \"$site_id\"";
			$result = mysql_query($query) or die ('Getting data from database failed!');
			$link_yes_no = mysql_numrows($result);
		}

		if ($design_yes_no == 1)
		{
			$dchecked2 = 'checked="checked"';
			$dchecked3 = '';
		}
		else
		{
			$dchecked2 = '';
			$dchecked3 = 'checked="checked"';
		}

		if ($link_yes_no == 1)
		{
			$checked2 = 'checked="checked"';
			$checked3 = '';
		}
		else
		{
			$checked2 = '';
			$checked3 = 'checked="checked"';
		}

		if ($members_yes_no == 1)
		{
			$checked4 = 'checked="checked"';
			$checked5 = '';
		}
		else
		{
			$checked4 = '';
			$checked5 = 'checked="checked"';
		}

		if ($databasedata['prettyurls']==1) echo '<font class="c595">Edit a Page</font><br />
		<a href="../' . $pageURL . '" target="_blank">View Page</a>';
		else  echo '<font class="c595">Edit a Page</font><br />
		<a href="../index.php?id=' . $edit . '" target="_blank">View Page</a>';

		echo " | <a href='stat.php?id=$edit'>View Stats</a>";

		if ($databasedata['editHTML'] == 0)
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
						file: "http://' . $address . '/admin/pictures_uploads.php?" + type,
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

		echo '<form method="post" action="editpage.php?edit=' . $edit . '">';
			if ($edit != 1)
			{
				echo 'Name of Page: <input type="text" value="' . $name . '" name="name"><br />';
			}
			else
			{
				echo 'Name of Page: "' . $name . '"<br />';
			}

			if ($databasedata['prettyurls'])
			{
				if ($edit != 1) echo 'Page URL: <input type="text" name="pageURL" value="' . $pageURL . '" maxlength=\'200\'><br />';
				else echo 'Page URL: ' . $pageURL . '<br />';
			}

			if ($edit != 1)
			{
				echo 'Do you want a link to this page on the menu bar? <input type="radio" name="link_yes_no" value="1" ' . $checked2 . '>Yes <input type="radio" name="link_yes_no" value="0" ' . $checked3 . '> No<br />';
				if ($databasedata['login'] == 1)
				{
					echo 'Do you want this page to be an members only page? <input type="radio" name="members_yes_no" value="1" ' . $checked4 . '>Yes <input type="radio" name="members_yes_no" value="0" ' . $checked5 . '> No<br />';
				}
			}
			else
			{
				echo 'Do you want a link to this page on the menu bar? <input type="radio" name="link_yes_no" value="1" ' . $checked2 . '>Yes<br />';
				if ($databasedata['login'] == 1)
				{
					echo 'Do you want this page to be an members only page? <input type="radio" name="members_yes_no" value="0" ' . $checked5 . '>No<br />';
				}
			}
			echo 'Do you want to show the site design on this page? <input type="radio" name="design_yes_no" value="1" ' . $dchecked2 . '>Yes <input type="radio" name="design_yes_no" value="0" ' . $dchecked3 . '> No<br />Meta Description: <input type="text" name="metaDescription" value="' . $metaDescription . '" maxlength=\'200\'><br />
			Meta Keywords: <input type="text" name="metaKeywords" value="' . $metaKeywords . '" maxlength=\'200\'> (separate by commas)<br />Extra Menu Code (next to the name): <input type="text" name="menuTXT" value="' . $menuTXT . '"><br />';
			/*echo '<input type="submit" value="Save"><br /><br />
			<script type="text/javascript">
			<!--
			document.write("<textarea name=\"contents\" rows=\"15\" style=\"width:100%\">' . htmlspecialchars($contents, ENT_QUOTES) . '</textarea>");
			// -->
			</script><noscript>This editor uses javascript.</noscript>
			<br /><input type="submit" value="Save">
		</form>';*/

		echo '<input type="submit" value="Save" onclick="window.saving=true;"><br /><br />
			<textarea name="contents" rows="15" style="width:100%">' . htmlspecialchars($contents) . '</textarea>
			<br /><input type="submit" value="Save" onclick="window.saving=true;">
		</form>';
	}
}
else
{
	echo '<font class="c595">Edit a Page</font><br />';

	if (isset($_REQUEST['sort']) && $_REQUEST['sort'] == 'id')
	{
		$sort = 'id';
		$query="SELECT pageId, pageName FROM `pages` where membersPage = 0 and siteId = \"$site_id\" order by pageId Asc";
		
		echo "Sort by <a href=\"editpage.php?sort=name\">Sort by Name</a> <b>Sort by Id</b><br /><br />";
	}
	else
	{
		$sort = 'name';
		$query="SELECT pageId, pageName FROM `pages` where membersPage = 0 and siteId = \"$site_id\" order by pageName Asc";
		
		echo "Sort by <b>Sort by Name</b> <a href=\"editpage.php?sort=id\">Sort by Id</a><br /><br />";
	}
	
	$result=mysql_query($query);
	$num=mysql_numrows($result);

	// echo notes
	for ($i=0;$i < $num; $i++)
	{
		$Id=mysql_result($result,$i,"pageId");
		$Name=mysql_result($result,$i,"pageName");

		echo '"' . $Name . '"&nbsp;&nbsp;<a href="editpage.php?edit=' . $Id . '">Edit</a><br />';
	}

	if ($databasedata['login'] == 1)
	{
		$query="SELECT pageId, pageName FROM `pages` where membersPage = 1 and siteId = \"$site_id\" order by pageName Asc";
		$result=mysql_query($query);
		$num=mysql_numrows($result);
		if ($num > 0) echo '<br /><font class="c598"><b>Members Only Pages</b></font><br />';

		// echo notes
		for ($i=0;$i < $num; $i++)
		{
			$Id=mysql_result($result,$i,"pageId");
			$Name=mysql_result($result,$i,"pageName");

			echo '"' . $Name . '"&nbsp;&nbsp;<a href="editpage.php?edit=' . $Id . '">Edit</a><br />';
		}
	}
}
mysql_close();

site_footer();
?>
<?php
require_once "design.php";
site_header("Logout");

logout();
echo 'You are now Logged out. <script type="text/javascript">function relocate() {window.top.location="index.php";} setTimeout("relocate()",5000);</script>';

site_footer();
?>
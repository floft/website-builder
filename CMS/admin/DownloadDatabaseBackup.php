<?php
require_once "../variables.php";

header("Content-Type: text/x-sql");
header("Content-Disposition: attachment; filename=DatabaseBackup.sql");

//connect to database
$dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($host_addon . $database);
//get mysql dump
if (!function_exists('mysql_dump')) {
	function mysql_dump($database,$siteId) {
		$lnbr = "\r";
	$query = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . $lnbr . $lnbr;
	$tables = @mysql_list_tables($database);
	while ($row = @mysql_fetch_row($tables)) { $table_list[] = $row[0]; }
	for ($i = 0; $i < @count($table_list); $i++) {
		$results = mysql_query('DESCRIBE ' . $database . '.' . $table_list[$i]);
		//$query .= 'DROP TABLE IF EXISTS `' . $database . '.' . $table_list[$i] . '`;' . $lnbr;
		//$query .= $lnbr . 'CREATE TABLE `' . $database . '.' . $table_list[$i] . '` (' . $lnbr;
		//$query .= $lnbr . 'CREATE TABLE IF NOT EXISTS `' . $database . '.' . $table_list[$i] . '` (' . $lnbr;
		$query .= $lnbr . 'CREATE TABLE IF NOT EXISTS `' . $table_list[$i] . '` (' . $lnbr;
		$tmp = '';
		while ($row = @mysql_fetch_assoc($results)) {
		$query .= '`' . $row['Field'] . '` ' . $row['Type'];
		if ($row['Null'] != 'YES') { $query .= ' NOT NULL'; }
		if ($row['Default'] != '') { $query .= ' DEFAULT \'' . $row['Default'] . '\''; }
		if ($row['Extra']) { $query .= ' ' . strtoupper($row['Extra']); }
		if ($row['Key'] == 'PRI') { $tmp = 'primary key(' . $row['Field'] . ')'; }
		$query .= ','. $lnbr;
		}
		$query .= $tmp . $lnbr . ');' . str_repeat($lnbr, 2);
		$results = mysql_query('SELECT * FROM ' . $database . '.' . $table_list[$i]);
		$num = mysql_numrows($results);
		if ($num > 0) {
		$number=0;
		$query .= 'INSERT INTO `' . $table_list[$i] .'` (';
		while ($row = @mysql_fetch_assoc($results)) { $number++;
		//$query .= 'INSERT INTO `' . $database . '.' . $table_list[$i] .'` (';
		$data = Array();
		while (list($key, $value) = @each($row)) { $data['keys'][] = $key; $data['values'][] = str_replace("\r\n", "\\r\\n", str_replace("'", "''", str_replace("\"", "\"\"", $value))); }
		if ($number==1) $query .= join($data['keys'], ', ') . ') VALUES' . $lnbr;
		//$query .= join($data['keys'], ', ') . ')' . $lnbr . '(\'' . join($data['values'], '\', \'') . '\')' . (($num==$number)?";":",") . $lnbr;
		$query .= '(\'' . join($data['values'], '\', \'') . '\')' . (($num==$number)?";":",") . $lnbr;
		}
		$query .= str_repeat($lnbr, 2);
		}
	}
	return $query;
	}

	echo mysql_dump($host_addon.$database,$site_id);
} else {
	echo mysql_dump($host_addon.$database);
}
//disconnect
mysql_close();

// require_once "../variables.php";
//
// //download dump file
// function dl_file_resume($file)
// {
//     //First, see if the file exists
//     if (!is_file($file)) { die("Error: 404 File not found!"); }
//
//     //Gather relevent info about file
//     $len = filesize($file);
//     $filename = basename($file);
//     $file_extension = strtolower(substr(strrchr($filename,"."),1));
//
//     //content-type for this file
//     $ctype = "application/force-download";
//
//     //Begin writing headers
//     header("Cache-Control:");
//     header("Cache-Control: public");
//
//     //Use the switch-generated Content-Type
//     header("Content-Type: $ctype");
//     if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
//         # workaround for IE filename bug with multiple periods / multiple dots in filename
//         # that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
//         $iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
//         header("Content-Disposition: attachment; filename=\"$iefilename\"");
//     } else {
//         header("Content-Disposition: attachment; filename=\"$filename\"");
//     }
//     header("Accept-Ranges: bytes");
//
//     $size=filesize($file);
//     //check if http_range is sent by browser (or download manager)
//     if(isset($_SERVER['HTTP_RANGE'])) {
//         list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
//         //if yes, download missing part
//         str_replace($range, "-", $range);
//         $size2=$size-1;
//         $new_length=$size2-$range;
//         header("HTTP/1.1 206 Partial Content");
//         header("Content-Length: $new_length");
//         header("Content-Range: bytes $range$size2/$size");
//     } else {
//         $size2=$size-1;
//         header("Content-Range: bytes 0-$size2/$size");
//         header("Content-Length: ".$size);
//     }
//     //open the file
//     $fp=fopen("$file","rb");
//     //seek to start of missing part
//     fseek($fp,$range);
//     //start buffered download
//     while(!feof($fp)){
//         //reset time limit for big files
//         set_time_limit(0);
//         print(fread($fp,1024*8));
//         flush();
//         ob_flush();
//     }
//     fclose($fp);
//     exit;
// }
// //connect to database
// $dbh=mysql_connect ("localhost", $host_addon . $main_username, $main_password) or die ('I cannot connect to the database because: ' . mysql_error());
// mysql_select_db ($host_addon . $database);
// //get the dump file
// if (!function_exists('mysql_dump')) {
// 	function mysql_dump($database,$siteId) {
// 		$lnbr = "\r";
// 	$query = '';
// 	$tables = @mysql_list_tables($database);
// 	while ($row = @mysql_fetch_row($tables)) { $table_list[] = $row[0]; }
// 	for ($i = 0; $i < @count($table_list); $i++) {
// 		$results = mysql_query('DESCRIBE ' . $database . '.' . $table_list[$i]);
// 		$query .= 'DROP TABLE IF EXISTS `' . $database . '.' . $table_list[$i] . '`;' . $lnbr;
// 		$query .= $lnbr . 'CREATE TABLE `' . $database . '.' . $table_list[$i] . '` (' . $lnbr;
// 		$tmp = '';
// 		while ($row = @mysql_fetch_assoc($results)) {
// 		$query .= '`' . $row['Field'] . '` ' . $row['Type'];
// 		if ($row['Null'] != 'YES') { $query .= ' NOT NULL'; }
// 		if ($row['Default'] != '') { $query .= ' DEFAULT \'' . $row['Default'] . '\''; }
// 		if ($row['Extra']) { $query .= ' ' . strtoupper($row['Extra']); }
// 		if ($row['Key'] == 'PRI') { $tmp = 'primary key(' . $row['Field'] . ')'; }
// 		$query .= ','. $lnbr;
// 		}
// 		$query .= $tmp . $lnbr . ');' . str_repeat($lnbr, 2);
// 		$results = mysql_query('SELECT * FROM ' . $database . '.' . $table_list[$i] . " where siteId = '$site_id'");
// 		while ($row = @mysql_fetch_assoc($results)) {
// 		$query .= 'INSERT INTO `' . $database . '.' . $table_list[$i] .'` (';
// 		$data = Array();
// 		while (list($key, $value) = @each($row)) { $data['keys'][] = $key; $data['values'][] = addslashes($value); }
// 		$query .= join($data['keys'], ', ') . ')' . $lnbr . 'VALUES (\'' . join($data['values'], '\', \'') . '\');' . $lnbr;
// 		}
// 		$query .= str_repeat($lnbr, 2);
// 	}
// 	return $query;
// 	}
//
// 	$dump = mysql_dump($host_addon.$database,$site_id);
// } else {
// 	$dump = mysql_dump($host_addon.$database);
// }
// //disconnect
// mysql_close();
//
// //save to file
// $filename="DatabaseBackup.sql";
// $file=fopen($filename,"w"); fwrite($file, $dump); fclose($file);
// //download file
// dl_file_resume($filename);
// //delete file
// unlink($filename);
?>
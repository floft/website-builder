<?php
if (!setting_get("num")) setting_set("num", 0);

if (isset($_REQUEST['a']))
{
	$num = setting_get("num");
	$id = $_REQUEST['a'];

	if ($num >= $id)
	{
		if (isset($_REQUEST['answer']) && trim($_REQUEST['answer']) != null)
		{
			$email = setting_get("e" . $id);

			if (check_email_address($email) && setting_get("a" . $id) == null)
			{
				$message = 'Hello!
You submitted a question a while ago on my website (Garrett.floft.net). I have answered the question. You can see my response on: http://garrett.floft.net/Questions?id=' . $id . '

--
Website Administrator
http://garrett.floft.net/';

				mail($email, "Questions", $message, "From: Floft <noreply@floft.net>");

				$emailed = true;
			}

			setting_set("a" . $id, $_REQUEST['answer']);

			echo "Your answer has been saved";
			if (isset($emailed)) echo " and the user has been notified of your response";
			echo ".<br /><br /><a href='<url:current>'>Back</a>";
		}
		else
		{
			//get data
			$answer = setting_get("a" . $id);
			$name = setting_get("n" . $id);
			$email = setting_get("e" . $id);
			$subject = setting_get("s" . $id);
			$question = setting_get("q" . $id);

			//escape data
			$name = htmlentities($name);
			$email = htmlentities($email);
			$subject = htmlentities($subject);
			$question = htmlentities($question);

			if (isset($_REQUEST['answer']))
			{
				echo "Please fill in the <u>entire</u> form!!!<br /><br />";

				$answer = htmlentities($_REQUEST['answer']);
			}

			echo "<form action=\"<url:current>\" method=\"post\"><input type=\"hidden\" name=\"a\" value=\"$id\"/><textarea name=\"answer\" rows=\"15\" style=\"width:100%\">$answer</textarea><br /><input type=\"submit\" value=\"Submit\" /> <input type=\"submit\" onclick=\"window.location='<url:current>';return false;\" value=\"Cancel\" /><br /><br />Name: $name<br />Email: $email<br />Subject: $subject<blockquote>$question</blockquote></form>";
		}
	}
	else
	{
		echo "ID doesn't exist!";
	}
}
else if (isset($_REQUEST['d']))
{
	$num = setting_get("num");
	$id = $_REQUEST['d'];

	if ($num >= $id)
	{
		if (isset($_REQUEST['confirm']))
		{
			setting_set("num", $num-1);
			setting_del("a" . $id);
			setting_del("n" . $id);
			setting_del("e" . $id);
			setting_del("s" . $id);
			setting_del("q" . $id);

			echo "The question has been deleted.<br /><br /><a href='<url:current>'>Back</a>";
		}
		else
		{
			//get data
			$answer = setting_get("a" . $id);
			$name = setting_get("n" . $id);
			$email = setting_get("e" . $id);
			$subject = setting_get("s" . $id);
			$question = setting_get("q" . $id);

			//escape data
			$name = htmlentities($name);
			$email = htmlentities($email);
			$subject = htmlentities($subject);
			$question = htmlentities($question);

			echo "<form action=\"<url:current>\" method=\"post\"><input type=\"hidden\" name=\"d\" value=\"$id\"/>Are you sure you want to delete this quetion?<br /><input type=\"submit\" name=\"confirm\" value=\"Yes\" /> <input type=\"submit\" onclick=\"window.location='<url:current>';return false;\" value=\"No\" /><br /><br />Name: $name<br />Email: $email<br />Subject: $subject<blockquote>$question<br /><br /><b>Answer:</b><br />$answer</blockquote></form>";
		}
	}
	else
	{
		echo "ID doesn't exist!";
	}
}
else
{
	$num = setting_get("num");

	for ($i=$num;$i>0;$i--)
	{
		$answer = setting_get("a" . $i);

		//get data
		$name = setting_get("n" . $i);
		$email = setting_get("e" . $i);
		$subject = setting_get("s" . $i);
		$question = setting_get("q" . $i);

		//escape data
		$name = htmlentities($name);
		$email = htmlentities($email);
		$subject = htmlentities($subject);
		$question = htmlentities($question);

		if ($answer == null) $TXT = "Answer";
		else $TXT = "Edit Response";

		echo "<b>$subject</b> - $name <i><a href=\"<url:current>&amp;a=$num\">$TXT</a> | <a href=\"<url:current>&amp;d=$num\">Delete</a></i><blockquote>$question</blockquote>";
	}

	if ($num==0)
	{
		echo "<i>none</i>";
	}
}
?>
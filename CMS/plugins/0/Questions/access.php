<?php
if (!setting_get("num")) setting_set("num", 0);

if (isset($_REQUEST['add']))
{
	echo "<div class=\"title\">New Question</div>Once Garrett answers your question it will be posted on the Questions page with only your name, the question, and his answer. If you specify an email address, it will only be used to notify you when Garrett has replied.<br /><br />";

	if (isset($_REQUEST['question']) && trim($_REQUEST['name']) != null && trim($_REQUEST['subject']) != null && trim($_REQUEST['question']) != null)
	{
		$num = setting_get("num");

		if ($num!=null) $num++;
		else $num = 1;

		setting_set("a" . $num, "");
		setting_set("n" . $num, $_REQUEST['name']);
		setting_set("e" . $num, $_REQUEST['email']);
		setting_set("s" . $num, $_REQUEST['subject']);
		setting_set("q" . $num, $_REQUEST['question']);
		setting_set("num", $num);

		$message = 'Hello!
A question has been submitted on your website. Please visit: http://garrett.floft.net/admin/plugins.php?p=Questions
--
Website Administrator
http://garrett.floft.net/';

		mail("youremail@example.com", "Questions", $message, "From: Floft <noreply@floft.net>");

		echo "Your question has been submitted! If you supplied a valid email address you will be notified when Garrett replies, otherwise you can check back on this page every now and then.<br /><br /><a href='<url:current>'>Back</a>";
	}
	else
	{
		if (isset($_REQUEST['question']))
		{
			echo "Whoops, it looks like you missed some of the fields below.<br /><br />";

			//get data
			$name = $_REQUEST['name'];
			$email = $_REQUEST['email'];
			$subject = $_REQUEST['subject'];
			$question = $_REQUEST['question'];

			//escape data
			$name = htmlentities($name);
			$email = htmlentities($email);
			$subject = htmlentities($subject);
			$question = htmlentities($question);
		}
		else
		{
			$name = "Anonymous";
		}

		echo "<form action=\"<url:current>\" method=\"post\"><input type=\"hidden\" name=\"add\" />Name: <input type=\"text\" name=\"name\" value=\"$name\" /><br />Email (optional): <input type=\"text\" name=\"email\" value=\"$email\" /><br />Subject: <input type=\"text\" name=\"subject\" value=\"$subject\" /><br />Question:<br /><textarea name=\"question\" rows=\"15\" style=\"width:100%\">$question</textarea><br /><input type=\"submit\" value=\"Submit\" /> <input type=\"submit\" onclick=\"window.location='<url:current>';return false;\" value=\"Cancel\" /></form>";
	}
}
else
{
	echo "<div class=\"title\">Questions</div>";

	$num = setting_get("num");
	$questions = 0;

	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) && $_REQUEST['id'] <= $num && setting_get("a" . $_REQUEST['id']) != null)
	{
		$i = $_REQUEST['id'];

		//get data
		$answer = setting_get("a" . $i);
		$name = setting_get("n" . $i);
		$email = setting_get("e" . $i);
		$subject = setting_get("s" . $i);
		$question = setting_get("q" . $i);

		//escape data
		$name = htmlentities($name);
		$email = htmlentities($email);
		$subject = htmlentities($subject);
		$question = htmlentities($question);

		echo "<a href=\"<url:current>?add\">Add Question</a><br /><br /><b>$subject</b> - $name<blockquote>$question<br /><br /><b>Answer:</b><br />$answer</blockquote><a href=\"<url:current>\">All Questions</a>";
	}
	else
	{
		echo "Here are some questions Garrett has answered that were submitted by users. If you have a question you want him to answer, please <a href=\"<url:current>?add\">Add a Question</a>.<br /><br />";

		for ($i=$num;$i>0;$i--)
		{
			$answer = setting_get("a" . $i);

			if ($answer != null)
			{
				$questions++;

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

				echo "<b>$subject</b> - $name<blockquote>$question<br /><br /><b>Answer:</b><br />$answer</blockquote>";
			}
		}

		if ($questions==0)
		{
			echo "<i>none</i>";
		}
	}
}
?>

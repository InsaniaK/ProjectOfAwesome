<?php
	
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html" ;charset="UTF-8"/>
	<link rel="stylesheet" type="text/css" href="indexStyle.css"/>
	<title>Läxikon</title>	
</head>
<body>
<div id="topBar">
<p id="logIn"><a href="login.php">Log In</a></p>
<p id="register"><a href="register.php">Register</a></p>
<img id="logoImg" src="Logoimg.jpg">
<p id="slogan">A platform for students to teach eachother</p>

<?php
session_start();

if (empty($_SESSION)) {
	$_SESSION["userName"] =  "";
	$_SESSION["userLoggedIn"] = false;
	$_SESSION['userId'] = "";
	$_SESSION['coursePref'] = array();
}

$host = "localhost";
$dbname = "laxikon";
$username = "MyLittleAdmin";
$password = "123";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);

$PDO = new PDO($dsn, $username, $password, $attr);

if($PDO){

	if (!empty($_SESSION)) {
		
	
		if ($_SESSION['userLoggedIn']) {
			
			$prefs = "";
			foreach ($_SESSION['coursePref'] as $row) 
			{
				if(empty($prefs))
				{
					$prefs = "courseId=" . $row;
				}	
				else
				{
					$prefs = $prefs . " OR " . "courseId=" . $row; 
				}

			}
			$postGetter = $PDO->prepare("SELECT * FROM posts WHERE " . $prefs);	

		}
		else
		{
			$postGetter = $PDO->prepare("SELECT * FROM posts ORDER BY  dateTime");
		}
		if (!empty($_GET)) {
			$_GET = null;
			$searchTerms = explode(" " ,filter_input(INPUT_GET, 'searchField', FILTER_SANITIZE_SPECIAL_CHARS));
			$searchSQL = "SELECT * FROM posts WHERE ";
			$tempCounter = 0;
			foreach ($searchTerms as $row) {
				 if ($tempCounter == 0) 
				 {
				 	$searchSQL .= "title LIKE ? OR content LIKE ?";
				 }
				 else
				 {
				 	$searchSQL .= " OR title LIKE ? OR content LIKE ?";
				 }
				 $tempCounter ++;
			}
			$postGetter = $PDO->prepare($searchSQL);
			$tempCounter = 0;
			$tempArr = array();
			foreach ($searchTerms as $row) {
				$row = "%".$row."%";
				array_push($tempArr, $row);
				array_push($tempArr, $row);
				$tempCounter ++;
				$postGetter->bindParam($tempCounter, $tempArr[$tempCounter - 1], PDO::PARAM_STR);

				$tempCounter ++;
				$postGetter->bindParam($tempCounter, $tempArr[$tempCounter - 1], PDO::PARAM_STR);
			}
		}
			echo "<form id=\"searchBar\" action=\"\" method=\"GET\">
			<p>
				<input type=\"text\" name=\"searchField\">
				<input type=\"submit\" value=\"Search\">
			</p>
			</form>";
		 
		$postGetter->execute();

		?></div><?php

		while ($row = $postGetter->fetch())
		{
			$courseGetter = $PDO->prepare("SELECT courseName FROM courses WHERE courseId = " . $row['courseId']);
			$commentGetter = $PDO->prepare("SELECT COUNT(*) AS count FROM comments WHERE postId =  " . $row['id']);
			$ratingGetter = $PDO->prepare("SELECT * FROM ratings ");
			$authorGetter = $PDO->prepare("SELECT name AS authorName FROM users WHERE id = :authorId");
			$authorGetter->bindparam(":authorId", $row['authorId']);

			$authorGetter->execute();
			$authorName = $authorGetter->fetch();
			$courseGetter->execute();
			$courseName = $courseGetter->fetch();
			$subSum = substr($row['content'], 0, 50) . "...";

			if (strlen($row['title']) > 20) {
			 	$subTitle = substr($row['title'], 0, 20) . "...";
			}
			else
			{
				$subTitle = substr($row['title'], 0, 20);
			}
			


			$commentGetter->execute();
			$commentCount = $commentGetter->fetch();

			if ($row['request'] == 1) {
				$isRequest = "request";
				$requestText = "[Request]";
			}
			else
			{
				$isRequest = "summary";
				$requestText = "[Summary]";
			}


			echo 
			"<div class=\"{$isRequest}\"><a href=\"summary.php?postId={$row['id']}\">
				<h2 class= \"title\">{$requestText}{$subTitle}</h2></a>
				<p class=\"authorName\"><a href=\"profile.php?userId={$row['authorId']}\">{$authorName['authorName']}</p><a href=\"summary.php?postId={$row['id']}\">
				<p class=\"courseName\">{$courseName['courseName']}</p>
				<p class=\"dateTime\">{$row['dateTime']}</p>
				<p class=\"subSum\">{$subSum}</p>
				<p class=\"comCount\">{$commentCount['count']} Comments</p>
				</a>
			</div>";
		}
	}

}

?>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html ;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="summaryStyle.css"/>
	<title>Läxikon</title>	
</head>
<body>
<div id="topBar">
<p id="logIn"><a href="login.php">Log In</a></p>
<p id="register"><a href="register.php">Register</a></p>
<img id="logoImg" src="Logoimg.jpg">
<p id="slogan">A platform for students to teach eachother</p>
</div> 		

<?php
session_start();
	
$host = "localhost";
$dbname = "laxikon";
$username = "MyLittleAdmin";
$password = "123";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);

$PDO = new PDO($dsn, $username, $password, $attr);

if($PDO){

	if (!empty($_GET)) {
		
		$_GET = null;


		$postId = filter_input(INPUT_GET, 'postId', FILTER_SANITIZE_SPECIAL_CHARS);
		$statement = $PDO->prepare("SELECT posts.*, users.name as userName, courses.*  
			FROM posts 
			JOIN courses ON posts.courseId = courses.courseId 
			JOIN users ON posts.authorId = users.id 
			WHERE posts.id = :postId");
		$statement->bindparam(":postId", $postId);
		if($statement->execute())
		{
			while($row = $statement->fetch())
			{
				echo 
				"<div id=\"postContainer\">
					<h2 id=\"title\">{$row['title']}</h2>
					<p id=\"courseName\">Course: {$row['courseName']}</p>
					<p id=\"authorName\">Written By: <a href=\"profile.php?userId={$row['authorId']}\">{$row['userName']}</a></p>
					<p id=\"dateTime\">{$row['dateTime']}</p>";
					if ($row['request'] == 1) {
						echo "<p>This is a request</p>";
					}
					echo "<p id=\"postContent\">{$row['content']}</p>
				</div>";
			}
		}
		else 
		{
			print_r($statement->errorInfo());
		}

		if (!empty($_POST)) {
			$_POST = null;
			$comCont = filter_input(INPUT_POST, 'comCont', FILTER_SANITIZE_SPECIAL_CHARS);
			$comState = $PDO->prepare("INSERT INTO comments(id, content, datetime, authorId, postId) VALUES (0, :comCont, NOW(), :authorId, :postId)");
			$comState->bindParam(":comCont", $comCont);
			$comState->bindParam(":authorId", $_SESSION['userId']);
			$comState->bindParam(":postId", $postId);
			$comState->execute();

		}

		if ($_SESSION['userLoggedIn'] == true)
		{
			echo "<form action=\"\" method=\"POST\">
				<p>
					<textarea name=\"comCont\"></textarea>
					<input type=\"submit\" value=\"Submit\"/>
				</p>
			</form>";
		}

		$commentGetter = $PDO->prepare("SELECT comments.*, users.name AS authorName 
			FROM comments JOIN users ON comments.authorId = users.id 
			WHERE :postId = comments.postId");
		$commentGetter->bindParam(":postId", $postId);
		$commentGetter->execute();
		while ($row = $commentGetter->fetch()) {
			echo"<div class=\"commentContainer\">
			<p class=\"commentAuthor\"><a href=\"profile.php?userId={$row['authorId']}\">{$row['authorName']}</a></p>
			<p class=\"commentDateTime\">{$row['dateTime']}</p>
			<p class=\"commentContent\">{$row['content']}</p>
			</div>";
		}
	}

}
else
{
	echo "Failed to connect, YA DONE GOOFED SON";
}


?>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<?php
session_start();
// Default Variables
$host = "localhost";
$dbname = "laxikon";
$username = "MyLittleAdmin";
$password = "123";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);

$pdo = new PDO($dsn, $username, $password, $attr);

if($pdo){
	if ($_SESSION['userLoggedIn'] == true) {
		
		if (!empty($_POST)) {
		//Filters input from user
			$_POST = null;
			$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
			$userId = $_SESSION['userId']; //Records userId from Session
			$course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_SPECIAL_CHARS);
			$request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_SPECIAL_CHARS);
			$post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_SPECIAL_CHARS);

			if (empty($request)) { //Checks if request
				$request = 0;
			}
			
			//Binds parameters and puts input into database.
			if ((!empty($post)) && (!empty($userId)) && (!empty($course)) && (!empty($title))) {
				$statement = $pdo->prepare("INSERT INTO posts (id, content, authorId, dateTime, courseId, request, title) VALUES (0, :post, :userId, NOW(), :course, :request, :title)");
				$statement->bindparam(":userId", $userId);
				$statement->bindparam(":title", $title);
				$statement->bindparam(":post", $post);
				$statement->bindparam(":request", $request);
				$statement->bindparam(":course", $course);
				if ($statement->execute())
				{
					
				}
				else
				{
					print_r($statement->error_log());
				}
			}
		}

		//Form
	?>
	<form action="submit.php" method="POST">
	<p>
		<label for="title">Title: </label>
		<input type="text" name="title"/>
	</p>
	<p>
		<label for="course">Course: </label>
		<select name="course">
		<?php
			foreach($pdo->query("SELECT * FROM courses ORDER BY courseName") as $row){
				echo "<option value=\"{$row['courseId']}\">{$row['courseName']}</option>";
			}
		?>
	</select>
	</p>
	<p>
		<input type="checkbox" name="request" value="1">
		<label for="Request">Is this a request? </label>
	</p>

	<p>
		<label for="post">Post: </label>
		<input type="text" name="post"/>
	</p>

	<input type="submit" value="Post" />
	</form>
	<br />
	<?php
	}
	else{
		echo "<p>You need to <a href=\"login.php\">log in</a> to access this page.</p>";
	}
}


?>
</body>
</html>
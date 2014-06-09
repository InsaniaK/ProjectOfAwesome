<?php
session_start();

$host = "localhost";
$dbname = "laxikon";
$username = "MyLittleAdmin";
$password = "123";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
$errorMsg = null;

$PDO = new PDO($dsn, $username, $password, $attr);
if ($PDO) {

	if (!empty($_GET)) {
		$_GET = null;
		$userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_SPECIAL_CHARS);
		$statement = $PDO->prepare("SELECT * FROM users WHERE id = :userId");
		$statement->bindparam(":userId", $userId);
		if ($statement->execute()) {
			while ($row = $statement->fetch()) {
				echo "
				<h2>{$row['name']}</h2>
				<p>Date of birth: {$row['birthDate']}</p>
				<p>Registered: {$row['regDate']}</p>";
				if ($row['userLevel'] == 2) {
					echo "Admin";
				}
				elseif ($row['userLevel'] == 1) {
					echo "Member";
				}
			}
		}
		if (!empty($_POST)) 
		{
			$_POST = null;
			$courseId = filter_input(INPUT_POST, 'course');


			$multiChecker = $PDO->prepare("SELECT * FROM userCourses WHERE userId = :userId AND courseId = :courseId");
			$multiChecker->bindparam(":courseId", $courseId);
			$multiChecker->bindparam(":userId", $_SESSION['userId']);
			if ($multiChecker->execute())
			{
				while ($multiChecker->fetch()) {
					$errorMsg = $errorMsg . "You've already entered that course.";
				}
			}
			else
			{
				$errorMsg=$errorMsg . "AAARGH";
				print_r($multiChecker->error_log());
			}


			$userCourseStatement = $PDO->prepare("INSERT INTO userCourses(userId, courseId) VALUES (:userId, :courseId)");
			$userCourseStatement->bindparam(":courseId", $courseId);
			$userCourseStatement->bindparam(":userId", $_SESSION['userId']);


			if (empty($errorMsg)) {
				$userCourseStatement->execute();
				array_push($_SESSION['coursePref'], $courseId);
			}
		}
		$postStatement = $PDO->prepare("SELECT * FROM posts WHERE authorId = :userId");
		$postStatement->bindparam(":userId", $userId);
		if ($postStatement->execute()) {

		}
		else
		{
			print_r($postStatement->errorInfo());
		}
		if ($_SESSION['userLoggedIn'] == true) 
		{
			if ($_SESSION['userId'] == $userId) 
			{

				?>
				<p>Here you can add which courses you attend. You will only see summaries from these courses.</p>
				<form action="profile.php?userId=<?php echo $userId; ?>" method="POST">
				<p>
					<label for="course">Course: </label>
					<select name="course">
					<?php
						foreach($PDO->query("SELECT * FROM courses ORDER BY courseName") as $row)
						{
							echo "<option value=\"{$row['courseId']}\">{$row['courseName']}</option>";
						}
					?>
					</select>
				</p>
				<input type="submit" value="Add to preferences"/>
				</form>
				<?php
				print $errorMsg;
			}
		}
	}



}

?>
<?php
session_start();

$host = "localhost";
$dbname = "laxikon";
$username = "MyLittleAdmin";
$password = "123";

$loginErrorMsg="";

$_SESSION["userName"] =  "";
$_SESSION["userLoggedIn"] = false;
$_SESSION['userId'] = "";
$_SESSION['coursePref'] = array();


$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);


$PDO = new PDO($dsn, $username, $password, $attr);


if ($PDO){
	if (!empty($_POST)) {
		$_POST 	= null;
		$logName = filter_input(INPUT_POST, 'nameField', FILTER_SANITIZE_SPECIAL_CHARS);
		$logPass = filter_input(INPUT_POST, 'passField', FILTER_SANITIZE_EMAIL);

		$userStatement = $PDO->prepare('SELECT * from users WHERE name=:logName');
		$userStatement->bindparam(":logName", $logName);

		if ($userStatement->execute()) {

			$checker = false;

			while ($row = $userStatement->fetch()) {
				$checker = true;
				$hashedLogPass = hash('sha512', $logPass . $row['salt']);

				if ($hashedLogPass == $row['password']) {
					echo "<a href=\"index.php\">You are now logged in. Click here to go back to the homepage</p>";
					$_SESSION["userName"] =  $row['name'];
					$_SESSION["userLoggedIn"] = true;
					$_SESSION['userId'] = $row['id'];
					$_SESSION['coursePref'] = array();
					$courseGetter = $PDO->prepare("SELECT * FROM usercourses WHERE userId = :userId");
					$courseGetter->bindparam("userId", $_SESSION['userId']);
					if ($courseGetter->execute()) {
						while ($row = $courseGetter->fetch()) {
							array_push($_SESSION['coursePref'], $row['courseId']);
						}
					}
					 
				}
				else{
					echo "Incorrect password.";
				}
			}

			if ($checker == false) {
				echo "Invalid username.";
			}
		}
		else{
			print_r($userStatement->error_log());
			echo "Error has occured, error code 1, it might mean something to something one";
		}
	}

	if ($_SESSION['userLoggedIn'] == false) {
		# code...
	

	?>


	<form action="" method="POST">
		<p>
			<label for="nameField">Username:</label>
			<input type="text" name="nameField">
		</p>
		<p>
			<label for="passwordField">Password:</label>
			<input type="password" name="passField">
		</p>
		<p><input type="submit" value="Login"></p>
	</form>
	<?php
}


}
?>

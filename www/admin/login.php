<?php
	date_default_timezone_set('UTC');
	$dir_login = dirname(__FILE__);
	include_once($dir_login."/../gtree.php");
	$errorLogin = '';

	if (isset($_GET['logout'])) {
		$token = isset($_COOKIE['gt_admin_token']) ? $_COOKIE['gt_admin_token'] : null;
		if ($token != null) {
			$conn = GTree::dbConn();
			$stmt = $conn->prepare('SELECT * FROM users_tokens WHERE token = ?');
			$stmt->execute(array($token));
			$userid = 0;
			if ($row = $stmt->fetch()) {
				$userid = $row['userid'];
			}
			GTLog::info("logout", "[admin#".$userid."] logout");
			$stmt = $conn->prepare('DELETE FROM users_tokens WHERE token = ?');
			$stmt->execute(array($token));
		}

		unset($_COOKIE['gt_admin_token']);
		setcookie("gt_admin_token", null, time()-86400, '/'); // remove cookie
	}

	if (isset($_POST['do_login'])) {
		$conn = GTree::dbConn();
		$username = strtolower($_POST['username']);
		$password = $_POST['password'];
		$password_sha1 = sha1($password);

		$conn = GTree::dbConn();
		$stmt = $conn->prepare('SELECT * FROM users WHERE username = ? AND userpass = ?');
		$stmt->execute(array($username, $password_sha1));
		if ($row = $stmt->fetch()) {
			$userid = $row['id'];
			$username = $row['username'];
			$role = $row['role'];
			if ($role == 'admin') {
				$token = GTree::getRandomString(250);
				$stmt2 = $conn->prepare('INSERT INTO users_tokens(token, role, userid) VALUES(?,?,?);');
				$stmt2->execute(array($token, $role, $userid));
				setcookie("gt_admin_token", $token, time()+86400, '/');
				GTLog::info('loggined', '[admin#'.$userid.'] '.$username.' logged in');
				header('Location: ./?');
				exit;
			} else {
				$errorLogin = "Вы не админ";
			}
		} else {
			$errorLogin = "Неправильный логин или пароль";
		}

	}

	if (isset($_COOKIE['gt_admin_token']) && !StaticLib::isAuthorized()) {
		unset($_COOKIE['gt_admin_token']);
		setcookie("gt_admin_token", null, time()-86400, '/'); // remove cookie
		header('Location: ./?');
		exit;
	}
?>
<?php include_once("head.php"); ?>

	<div class="card card-login mx-auto mt-5">
		<div class="card-header">Административная панель</div>
		<div class="card-body">
			<form action="login.php" method="POST">
			  <div class="form-group">
				<label for="username">Логин</label>
				<input class="form-control" name="username" id="username"  aria-describedby="emailHelp" placeholder="">
			  </div>
			  <div class="form-group">
				<label for="password">Пароль</label>
				<input class="form-control" name="password" id="password" type="password">
			  </div>
			  <button class="btn btn-primary btn-block" name="do_login" >Войти (как админ)</button>
			  <?php 
				if ($errorLogin != '') {
					echo '<div class="alert alert-danger" style="margin-top: 20px">'.$errorLogin.'</div>';
				}
			  ?>
		</div>
	</div>

<?php

include_once("footer.php");
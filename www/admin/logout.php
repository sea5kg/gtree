<?php
	date_default_timezone_set('UTC');
	$dir_login = dirname(__FILE__);
	include_once($dir_login."/../gtree.php");

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
	header('Location: login.php');
	exit;
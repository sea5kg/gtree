<?php
date_default_timezone_set('UTC');
$curdir_gt = dirname(__FILE__);

class GTree {
	static $TOKEN = null;
	static $CONN = null;
	static $CONFIG = null;
	static $ROLE = null;
	static $USERID = null;
	static $USERNAME = null;
	static $SETTINGS = null;
	static $MONTHES = array(
		1 => "Январь",
		2 => "Февраль",
		3 => "Март",
		4 => "Апрель",
		5 => "Май",
		6 => "Июнь",
		7 => "Июль",
		8 => "Август",
		9 => "Сентябрь",
		10 => "Октябрь",
		11 => "Ноябрь",
		12 => "Декабрь",
	);
	
	static $gtree_padding = 10;
	static $gtree_yearstep = 16;
	static $gtree_card_width = 159;
	static $gtree_card_height = 62;
	static $gtree_gtcol = 170;
	static $gtree_gtline = 86;
	static $gtree_gtline_top = 50;

	static function calculateHeight() {
		$gtree_maxgtline = 0;
		$conn = GTree::dbConn();
		$stmt = $conn->prepare('SELECT MAX(gtline) as gtl FROM persons;');
		$stmt->execute();
		if ($row = $stmt->fetch()) {
			$gtree_maxgtline = intval($row['gtl']);
		}
		return $gtree_maxgtline * GTree::$gtree_gtline + 2 * GTree::$gtree_padding + 100;
	}

	static function getMinBornYear() {
		$gtree_minyear = 0;
		$conn = GTree::dbConn();
		$stmt = $conn->prepare('SELECT MIN(bornyear) as minbornyear FROM persons WHERE bornyear > 0;');
		$stmt->execute();
		if ($row = $stmt->fetch()) {
			$gtree_minyear = intval($row['minbornyear']);
		}
		$gtree_minyear = $gtree_minyear - $gtree_minyear % 10;
		return $gtree_minyear;
	}

	static function getMaxBornYear() {
		$gtree_maxyear = 0;
		$conn = GTree::dbConn();
		$stmt = $conn->prepare('SELECT MAX(bornyear) as maxbornyear FROM persons WHERE bornyear > 0;');
		$stmt->execute();
		if ($row = $stmt->fetch()) {
			$gtree_maxyear = intval($row['maxbornyear']);
		}
		$gtree_maxyear = $gtree_maxyear - $gtree_maxyear % 10 + 10;
		return $gtree_maxyear;
	}

	static function calculateWidth() {
		$gtree_minyear = GTree::getMinBornYear();
		$gtree_maxyear = GTree::getMaxBornYear();
		$gtree_width = $gtree_maxyear - $gtree_minyear;
		$gtree_width = $gtree_width * GTree::$gtree_yearstep + 15 * GTree::$gtree_yearstep + 2 * GTree::$gtree_padding;
		return $gtree_width;
	}

	static function calcX_in_px($gtree_minyear, $year) {
		$ret = $year - $gtree_minyear; 
		$ret = $ret * GTree::$gtree_yearstep + GTree::$gtree_padding;
		return $ret;
	}

	static function dbConn() {
		if (GTree::$CONN != null)
			return GTree::$CONN;
		
			GTree::$CONN = new PDO(
			'mysql:host='.GTree::$CONFIG['conn']['host'].';dbname='.GTree::$CONFIG['conn']['db'].';charset=utf8',
			GTree::$CONFIG['conn']['username'],
			GTree::$CONFIG['conn']['password']
		);
		return GTree::$CONN;
	}
	
	static function getRandomString($length = 10) {
		mt_srand(intval((double)microtime()*10000));//optional for php 4.2.0 and up.
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	static function isAuthorized() {
		return GTree::$TOKEN != null;
	}
	
	static function startAdminPage() {
		if (isset($_COOKIE['gt_admin_token'])) {
			GTree::$TOKEN = $_COOKIE['gt_admin_token'];
		} else {
			GTree::$TOKEN = null;
		}

		if (GTree::$TOKEN != null){
			$conn = GTree::dbConn();
			try {
				$stmt = $conn->prepare('SELECT * FROM users_tokens WHERE token = ?');
				$stmt->execute(array(GTree::$TOKEN));
				if ($row = $stmt->fetch()){
					GTree::$ROLE = $row['role'];
					GTree::$USERID = $row['userid'];
					if (GTree::$ROLE != 'admin'){
						GTree::$TOKEN = null;
					}
				}else{
					GTree::$TOKEN = null;
				}
			} catch(PDOException $e) {
				GTree::$TOKEN = null;
				GTree::error(500, $e->getMessage());
			}
		}
		
		if (!GTree::isAuthorized()) {
			header("Location: login.php");
			exit;
		}

		if (!GTree::isAdmin()) {
			GTree::$TOKEN = null;
			header("Location: login.php");
			exit;
		}
	}
	
	static function isAdmin() {
		return GTree::$ROLE == 'admin';
	}
}

// load config
include_once ($curdir_gt."/conf.d/config.php");
GTree::$CONFIG = $config;

class GTLog {

	static function ok($tag, $msg){
		$conn = GTree::dbConn();
		$stmt = $conn->prepare('INSERT INTO events(type, tag, message, created) VALUES(?, ?, ?, NOW())');
		if (!$stmt->execute(array('ok', $tag, $msg))) {
			GTree::error(500, $stmt->errorInfo());
		}
	}

	static function info($tag, $msg){
		$conn = GTree::dbConn();
		$stmt = $conn->prepare('INSERT INTO events(type, tag, message, created) VALUES(?, ?, ?, NOW())');
		if (!$stmt->execute(array('info', $tag, $msg))) {
			GTree::error(500, $stmt->errorInfo());
		}
	}

	static function err($tag, $msg){
		$conn = GTree::dbConn();
		error_log('[ERROR] '.$tag.': '.$msg);
		$stmt = $conn->prepare('INSERT INTO events(type, tag, message, created) VALUES(?, ?, ?, NOW())');
		if (!$stmt->execute(array('err', $tag, $msg))) {
			GTree::error(500, $stmt->errorInfo());
		}
	}

	static function warn($tag, $msg){
		$conn = GTree::dbConn();
		error_log('[WARN] '.$tag.': '.$msg);
		$stmt = $conn->prepare('INSERT INTO events(type, tag, message, created) VALUES(?, ?, ?, NOW())');
		if (!$stmt->execute(array('warn', $tag, $msg))) {
			GTree::error(500, $stmt->errorInfo());
		}
	}
}
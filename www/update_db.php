<?php

$curdir_update_db = dirname(__FILE__);
include_once ($curdir_update_db."/gtree.php");

$dbname = $config['conn']['db'];
$tablename = "updates";

$conn = GTree::dbConn();

echo "Check if table updates is missing.\n";

$stmt = $conn->prepare("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = :dbname AND table_name = :tablename");
$stmt->bindParam(':dbname', $dbname);
$stmt->bindParam(':tablename', $tablename);
$stmt->execute();

if (!$stmt->fetch()) {
	echo "Table '$tablename' does not exist in database. Try creating... \n";
	$stmt = $conn->prepare("
        CREATE TABLE updates (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `version` int(11) NOT NULL,
            `dt` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
    ");

    if (!$stmt->execute()) {
		echo "Failed creating rable update\n";
        error_log(print_r($stmt->errorInfo(),true));
        exit(1);
    }
}
echo "OK.\n";

$current_db_ver = 0;

$stmt = $conn->prepare('SELECT * FROM updates ORDER BY version DESC LIMIT 0,1');
$stmt->execute(array());

if ($row = $stmt->fetch()) {
	$current_db_ver = $row['version'];
}
$response['current_db_ver'] = $current_db_ver;
$response['apply_updates'] = array();

echo "Current database version: $current_db_ver\n";

function next_func($cv) {
	return 'update'.str_pad("".($cv+1), 4, "0", STR_PAD_LEFT);
}

$funcname = next_func($current_db_ver);
$fileupdate = $funcname.'.php';

while (file_exists($curdir_update_db.'/db_updates/'.$fileupdate)) {
	include_once($curdir_update_db.'/db_updates/'.$fileupdate);
	if (function_exists($funcname)) {
		$response['apply_updates'] = $funcname;
		if ($funcname($conn)) {
			$stmt = $conn->prepare('INSERT INTO updates(version,dt) VALUES(?,NOW())');
			$stmt->execute(array($current_db_ver+1));
			$response['current_db_ver'] = $current_db_ver+1;
			echo "Installed update ".($current_db_ver+1)."\n";
		} else {
			GTree::error(500, "Failed update");
			exit(1);
		}
	}
	// next update
	$current_db_ver++;
	$funcname = next_func($current_db_ver);
	$fileupdate = $funcname.'.php';
}

exit(0);

<?php

$dir_data_export = dirname(__FILE__);
include_once($dir_data_export."/../gtree.php");
GTree::startAdminPage();

date_default_timezone_set('UTC');

$data = array();
$data['persons'] = array();
$data['photos'] = array();

$conn = GTree::dbConn();
$stmt = $conn->prepare('SELECT * FROM persons ORDER BY bornyear;');
$stmt->execute();

$persons_by_ids = array();

while ($row = $stmt->fetch()) {
    $id = intval($row['id']);
    $uid = $row['uid'];
    $persons_by_ids[$id] = $uid;

    $data['persons'][] = array(
        'uid' => $uid,
        'fullname' => $row['fullname'],
        'firstname' => $row['firstname'],
        'secondname' => $row['secondname'],
        'lastname' => $row['lastname'],
        'bornlastname' => $row['bornlastname'],
        'sex' => $row['sex'],
        'bornyear' => intval($row['bornyear']),
        'bornmonth' => intval($row['bornmonth']),
        'bornday' => intval($row['bornday']),
        'yearofdeath' => intval($row['yearofdeath']),
        'monthofdeath' => intval($row['monthofdeath']),
        'dayofdeath' => intval($row['dayofdeath']),
        'mother' => intval($row['mother']),
        'father' => intval($row['father']),
        'private' => $row['private'],
        'gtline' => intval($row['gtline']),
        'tree_x' => intval($row['tree_x']),
        'tree_y' => intval($row['tree_y']),
        'bornyear_notexactly' => $row['bornyear_notexactly'],
        'yearofdeath_notexactly' => $row['yearofdeath_notexactly'],
    );
}

// replace mother / father id to uid

foreach ($data['persons'] as $k => $v) {
    $mother = $data['persons'][$k]['mother'];
    if (isset($persons_by_ids[$mother])) {
        $data['persons'][$k]['mother'] = isset($persons_by_ids[$mother]) ? $persons_by_ids[$mother] : '';
    } else {
        $data['persons'][$k]['mother'] = '';
    }

    $father = $data['persons'][$k]['father'];
    if (isset($persons_by_ids[$father])) {
        $data['persons'][$k]['father'] = isset($persons_by_ids[$father]) ? $persons_by_ids[$father] : '';
    } else {
        $data['persons'][$k]['father'] = '';
    }
}


// export photos

$stmt = $conn->prepare('SELECT * FROM photos ORDER BY id;');
$stmt->execute();

$photos_uids = array();

while ($row = $stmt->fetch()) {
    $id = intval($row['id']);
    $uid = $row['uid'];
    $photos_uids[] = $uid;
    $data['photos'][] = array(
        'uid' => $uid,
        'name' => $row['name'],
        'year' => $row['year'],
        'year_notexactly' => $row['year_notexactly'],
        'description' => $row['description'],
        'created' => $row['created'],
        'updated' => $row['updated'],
    );
}

$path_zip = tempnam(sys_get_temp_dir(), "zip");

// fix Deprecated: ZipArchive::open(): Using empty file as ZipArchive is deprecated
if (file_exists($path_zip)) {
    unlink($path_zip);
}

$zip = new ZipArchive();
if ($zip->open($path_zip, ZipArchive::CREATE) === TRUE) {
    // Add files to the zip file
    // $zip->addFile('test.txt');
    // $zip->addFile('test.pdf');
    // $zip->addFile('random.txt', 'newfile.txt');

    $zip->addFromString('data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if (file_exists($dir_data_export.'/../public/tree.png')) {
        $zip->addFile($dir_data_export.'/../public/tree.png', 'tree.png');
    }

    foreach ($photos_uids as $v) {
        $photo_path = $dir_data_export.'/../public/'.$v.'.jpg';
        if (file_exists($photo_path)) {
            $zip->addFile($photo_path, 'photos/'.$v.'.jpg');
        }
    }

    $zip->close();
} else {
    die("Error: Could not created ".$path_zip);
}


if (file_exists($path_zip)) {
    $dt = date("Y-m-d_His");

    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
    header("Cache-Control: public"); // needed for internet explorer
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length:".filesize($path_zip));
    header('Content-Disposition: attachment; filename=gtree_data_'.$dt.'.zip');
    readfile($path_zip);
    die();
} else {
    die("Error: File not found.");
} 

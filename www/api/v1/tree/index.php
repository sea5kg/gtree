<?php

include_once("../../../gtree.php");

// father / mother
$conn = GTree::dbConn();
$persons = array();
$stmt = $conn->prepare('SELECT * FROM persons ORDER BY bornyear');
$minyear = 5000;
$maxyear = 0;
$stmt->execute(array());
while ($row = $stmt->fetch()) {
    if ($row['bornyear'] == 0) {
    continue;
    }

    $minyear = $row['bornyear'] < $minyear ? $row['bornyear'] : $minyear;
    $maxyear = $row['bornyear'] > $maxyear ? $row['bornyear'] : $maxyear;

    if ($row['monthofdeath'] > 0) {
    $maxyear = $row['monthofdeath'] > $maxyear ? $row['monthofdeath'] : $maxyear;
    }

    $personid = intval($row['id']);
    $lastname = $row['lastname'];
    if ($row['bornlastname'] != '') {
    $lastname = $row['bornlastname'];
    }

    if ($row['private'] == 'yes') {
    $lastname = '';
    }

    $persons[$personid] = array(
    'firstname' => $row['firstname'],
    'lastname' => $lastname,
    'bornyear' => intval($row['bornyear']),
    'bornyear_notexactly' => $row['bornyear_notexactly'],
    'mother' => intval($row['mother']),
    'father' => intval($row['father']),
    'gtline' => intval($row['gtline']),
    );
}

$data = array();

$data['gtree_minyear'] = GTree::getMinBornYear();
$data['gtree_maxyear'] = GTree::getMaxBornYear();
$data['gtree_padding'] = GTree::$gtree_padding;
$data['gtree_yearstep'] = GTree::$gtree_yearstep;
$data['gtree_card_width'] = GTree::$gtree_card_width;
$data['gtree_card_height'] = GTree::$gtree_card_height;
$data['gtree_gtline'] = GTree::$gtree_gtline;
$data['gtree_gtline_top'] = GTree::$gtree_gtline_top;
$data['gtree_height'] = GTree::calculateHeight();
$data['gtree_width'] = GTree::calculateWidth();
$data['gt'] = $persons;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);

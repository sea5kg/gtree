<?php

include_once("../../../gtree.php");


$data = array();
// father / mother
$conn = GTree::dbConn();

// detect privates persons info
$persons = array();
$stmt = $conn->prepare('SELECT * FROM persons');
$stmt->execute(array());
while ($row = $stmt->fetch()) {
    $personid = $row['id'];
    if ($row['private'] == 'yes') {
        $persons[$personid] = $row['firstname'];
    } else {
        $persons[$personid] = $row['fullname'];
    }
}


$stmt = $conn->prepare("
    SELECT
        persons.id,
        persons.uid,
        persons.private,
        persons.sex,
        persons.bornyear,
        persons.yearofdeath,
        persons.mother,
        persons.father,
        persons.firstname,
        persons.fullname,
        biographies.description as biography_about_life
    FROM persons
    LEFT JOIN biographies
        ON (biographies.personid = persons.id AND biographies.type = 'about_life')
    ORDER BY bornyear
");

$stmt->execute(array());
while ($row = $stmt->fetch()) {
    $person = array();

    $personid = $row['id'];
    $private = $row['private'] == 'yes';

    $motherid = $row['mother'];
    $mother = '';
    if ($motherid != 0) {
        $mother = '#'.$motherid;
        if (isset($persons[$motherid])) {
            $mother .= ' '.$persons[$motherid];
        }
    }

    $fatherid = $row['father'];
    $father = '';
    if ($fatherid != 0) {
        $father = '#'.$fatherid;
        if (isset($persons[$fatherid])) {
            $father .= ' '.$persons[$fatherid];
        }
    }

    $title = '';
    $biography = '';
    $yearsoflife = '';
    if ($row['bornyear'] > 0) {
        $yearsoflife = $row['bornyear'];
        if ($row['yearofdeath']) {
        $yearsoflife = 'Годы жизни: '.$yearsoflife.' - '.$row['yearofdeath'];
        } else {
        $yearsoflife = 'Год рождения: '.$yearsoflife;
        }
    }

    if ($private) {
        $title = $row['firstname'];
    } else {
        $title = $row['fullname'];
    }

    $person["uid"] = $row["uid"];
    $person["title"] = '#'.$personid.' '.$title;
    $person["yearsoflife"] = $yearsoflife;
    $person["mother"] = $mother;
    $person["father"] = $father;
    $person["row"] = $row;

    // biography content
    // $person['type'] = $row['type'];
    // $person['year'] = $row['year'];
    if ($private) {
        $person['biography'] = "hidden";
    } else if (isset($row['biography_about_life'])) {
        $person['biography'] = $row['biography_about_life'];
    } else {
        $person['biography'] = "";
    }

    $data[] = $person;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);

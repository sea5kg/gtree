<?php

$dir_persons = dirname(__FILE__);
include_once($dir_persons."/../gtree.php");
include_once($dir_persons."/../gtree_image.php");
GTree::startAdminPage();

$error = '';

$personid = 0;
$firstname = '';
$fullname = '';
$secondname = '';
$lastname = '';
$bornlastname = '';
$bornyear = 0;
$bornmonth = 0;
$bornday = 0;
$yearofdeath = 0;
$monthofdeath = 0;
$dayofdeath = 0;
$mother = 0;
$father = 0;
$private = 'no';
$bornyear_notexactly = 'no';
$yearofdeath_notexactly = 'no';
$uid = '';
$mother_full = null;
$father_full = null;
$biographies = array();
$sex = 'male';
$about_life_0 = '';

if (isset($_GET['personid'])) {
    $personid = intval($_GET['personid']);
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM persons WHERE id = ?;');
    $stmt->execute(array($personid));

    if ($row = $stmt->fetch()) {
        $firstname = $row['firstname'];
        $secondname = $row['secondname'];
        $lastname = $row['lastname'];
        $fullname = $row['fullname'];
        $bornlastname = $row['bornlastname'];
        $sex = $row['sex'];
        $bornyear = $row['bornyear'];
        $bornmonth = $row['bornmonth'];
        $bornday = $row['bornday'];
        $yearofdeath = $row['yearofdeath'];
        $monthofdeath = $row['monthofdeath'];
        $dayofdeath = $row['dayofdeath'];
        $mother = $row['mother'];
        $father = $row['father'];
        $private = $row['private'];
        $bornyear_notexactly = $row['bornyear_notexactly'];
        $yearofdeath_notexactly = $row['yearofdeath_notexactly'];
        $uid = $row['uid'];
    } else {
        $error = 'Пeрсона не найдена';
    }

    $stmt = $conn->prepare('SELECT * FROM biographies WHERE personid = ?');
    $stmt->execute(array($personid));
    if ($row = $stmt->fetch()) {
        $biographies[] = array(
            'type' => $row['type'],
            'year' => $row['year'],
            'description' => $row['description'],
        );
        if ($row['type'] == 'about_life' && $row['year'] == 0) {
            $about_life_0 = $row['description'];
        }
    }
}

if ($mother > 0) {
    $stmt = $conn->prepare('SELECT * FROM persons WHERE id = ?;');
    $stmt->execute(array($mother));
    if ($row = $stmt->fetch()) {
        $mother_full = array(
            'id' => $row['id'],
            'sex' => $row['sex'],
            'caption' => '('.$row['bornyear'].') '.$row['fullname'],
        );
    }
}

if ($father > 0) {
    $stmt = $conn->prepare('SELECT * FROM persons WHERE id = ?;');
    $stmt->execute(array($father));
    if ($row = $stmt->fetch()) {
        $father_full = array(
            'id' => $row['id'],
            'sex' => $row['sex'],
            'caption' => '('.$row['bornyear'].') '.$row['fullname'],
        );
    }
}

if (isset($_POST['do_biography_update'])) {

    $personid = intval($_POST['personid']);
    $bio_type = $_POST['bio_type'];
    $year = intval($_POST['year']);
    $description = $_POST['description'];

    $biography_id = 0;
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM biographies WHERE personid = ? AND `type` = ? AND year = ?');
    $stmt->execute(array($personid, $bio_type, $year));
    if ($row = $stmt->fetch()) {
        $biography_id = $row['id'];
    }

    $query = '';
    $values = array();
    if ($biography_id != 0) {
        $query = 'UPDATE biographies SET description = ?, updated = NOW() WHERE id = ?';
        $values[] = $description;
        $values[] = $biography_id;
    } else {
        $query = 'INSERT INTO biographies(personid, `type`, year, description, created, updated) VALUES(?,?,?,?,NOW(),NOW())';
        $values[] = $personid;
        $values[] = $bio_type;
        $values[] = $year;
        $values[] = $description;
    }


    $conn = GTree::dbConn();
    $stmt = $conn->prepare($query);
    if (!$stmt->execute($values)) {
        $error = 'Что то пошло не так.';
        error_log(print_r($stmt->errorInfo(), true));
    } else {
        GTLog::info('biography', '[admin#'.GTree::$USERID.'] updated biography for [person#'.$personid.']');
        header('Location: ./biographies.php?personid='.$personid);
		exit;
    }
}

$brothers_and_sisters_list = array();

$conn = GTree::dbConn();
$stmt = $conn->prepare('SELECT * FROM persons WHERE (father = ? AND father <> 0) OR (mother = ? AND mother <> 0)');
$stmt->execute(array($father, $father));
while ($row = $stmt->fetch()) {
    if ($row['id'] == $personid) {
        continue;
    }
    $caption = '('.$row['bornyear'].') '.$row['fullname'];
    $brothers_and_sisters_list[] = array(
        'id' => $row['id'],
        'sex' => $row['sex'],
        'caption' => $caption,
    );
}


$children_list = array();

$query_childrens = '';
if ($sex == 'male') {
    $query_childrens = 'SELECT * FROM persons WHERE father = ? ORDER BY bornyear';
} else if ($sex == 'female') {
    $query_childrens = 'SELECT * FROM persons WHERE mother = ? ORDER BY bornyear';
}


$conn = GTree::dbConn();
$stmt = $conn->prepare($query_childrens);
$stmt->execute(array($personid));

while ($row = $stmt->fetch()) {
    $caption = '('.$row['bornyear'].') '.$row['fullname'];
    $children_list[] = array(
        'id' => $row['id'],
        'sex' => $row['sex'],
        'caption' => $caption,
    );
}

$bornyear_notexactly = ($bornyear_notexactly == 'yes' ? ' (пр.)' : '');
$yearofdeath_notexactly = ($yearofdeath_notexactly == 'yes' ? ' (пр.)' : '');

include_once("head.php");
?>

<br>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="persons.php">Персоны</a></li>
    <li class="breadcrumb-item active" aria-current="page">Биография персоны #<?php echo $personid; ?></li>
  </ol>
</nav>

<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?php echo '[person#'.$personid.'] <strong>'.$fullname; ?></strong></h5>
        <h6 class="card-subtitle mb-2 text-muted">Годы жизни: 
            <?php echo $bornyear.$bornyear_notexactly.' - '.$yearofdeath.$yearofdeath_notexactly; ?>
        </h6>

        Отец: <?php
            if ($father_full != null) {
                echo '<a href="biographies.php?personid='.$father_full['id'].'" class="btn btn-link">'.$father_full['caption'].'</a>';
            } else {
                echo 'Неизвестен';
            }
        ?><br>

        Мать: <?php 
            if ($mother_full != null) {
                echo '<a href="biographies.php?personid='.$mother_full['id'].'" class="btn btn-link">'.$mother_full['caption'].'</a>';
            } else {
                echo 'Неизвестна';
            }
        ?><br>

        <?php
            foreach ($children_list as $k => $v) {
                $relation = $v['sex'] == 'male' ? 'Сын' : '';
                $relation = $v['sex'] == 'female' ? 'Дочь' : $relation;
                echo $relation.': <a href="biographies.php?personid='.$v['id'].'" class="btn btn-link">'.$v['caption'].'</a><br>';
            }
        ?>

        <?php
            foreach ($brothers_and_sisters_list as $k => $v) {
                $relation = $v['sex'] == 'male' ? 'Брат' : '';
                $relation = $v['sex'] == 'female' ? 'Сестра' : $relation;
                echo $relation.': <a href="biographies.php?personid='.$v['id'].'" class="btn btn-link">'.$v['caption'].'</a><br>';
            }
        ?>

    </div>
</div><br>

<form action="biographies.php" method="POST">
    <input name="personid" value="<?php echo $personid; ?>" type="hidden"/>
    <input name="bio_type" value="about_life" type="hidden"/>
    <input name="year" value="0" type="hidden"/>
    <div class="form-group">
        <label>О жизни</label>
        <textarea class="form-control" style="height: 250px" name="description"><?php echo htmlspecialchars($about_life_0); ?></textarea>
    </div>
    <button class="btn btn-primary" name="do_biography_update">Обновить</button>
</form>
<hr>

<script>

</script>


<?php include_once("footer.php");
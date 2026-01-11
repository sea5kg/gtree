<?php

$dir_data_import = dirname(__FILE__);
include_once($dir_data_import."/../gtree.php");
include_once($dir_data_import."/../gtree_image.php");
GTree::startAdminPage();

$error = '';

function update_or_insert($p) {
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM persons WHERE uid = ?;');
    $stmt->execute(array($p['uid']));

    $values = array();
    $values[] = $p['fullname'];
    $values[] = $p['firstname'];
    $values[] = $p['secondname'];
    $values[] = $p['lastname'];
    $values[] = $p['bornlastname'];
    $values[] = $p['sex'];
    $values[] = intval($p['bornyear']);
    $values[] = intval($p['bornmonth']);
    $values[] = intval($p['bornday']);
    $values[] = intval($p['yearofdeath']);
    $values[] = intval($p['monthofdeath']);
    $values[] = intval($p['dayofdeath']);
    $values[] = 0;
    $values[] = 0;
    $values[] = $p['private'];
    $values[] = $p['gtline'];
    $values[] = isset($p['tree_x']) ? intval($p['tree_x']) : 0;
    $values[] = isset($p['tree_y']) ? intval($p['tree_y']) : 0;
    $values[] = $p['bornyear_notexactly'];
    $values[] = $p['yearofdeath_notexactly'];
    $values[] = $p['uid'];

    $query = "";
    if ($row = $stmt->fetch()) {
        $query = "UPDATE persons SET 
            fullname = ?,
            firstname = ?,
            secondname = ?,
            lastname = ?,
            bornlastname = ?,
            sex = ?,
            bornyear = ?,
            bornmonth = ?,
            bornday = ?,
            yearofdeath = ?,
            monthofdeath = ?,
            dayofdeath = ?,
            mother = ?,
            father = ?,
            `private` = ?,
            gtline = ?,
            tree_x = ?,
            tree_y = ?,
            bornyear_notexactly = ?,
            yearofdeath_notexactly = ?
        WHERE 
            uid = ?
        ";
    } else {
        $query = "INSERT INTO persons(
            fullname,
            firstname,
            secondname,
            lastname,
            bornlastname,
            sex,
            bornyear,
            bornmonth,
            bornday,
            yearofdeath,
            monthofdeath,
            dayofdeath,
            mother,
            father,
            `private`,
            gtline,
            tree_x,
            tree_y,
            bornyear_notexactly,
            yearofdeath_notexactly,
            uid
        ) VALUES(
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?
        );";
    }
    $stmt2 = $conn->prepare($query);
    $stmt2->execute($values);
}

function update_parents($p) {
    $mother = $p['mother'];
    $father = $p['father'];

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT id FROM persons WHERE uid = ?;');
    $stmt->execute(array($mother));
    if ($row = $stmt->fetch()) {
        $mother = intval($row['id']);
    } else {
        $mother = 0;
    }

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT id FROM persons WHERE uid = ?;');
    $stmt->execute(array($father));
    if ($row = $stmt->fetch()) {
        $father = intval($row['id']);
    } else {
        $father = 0;
    }

    $stmt = $conn->prepare('UPDATE persons SET mother = ?, father = ? WHERE uid = ?;');
    $stmt->execute(array($mother, $father, $p['uid']));

}

if (isset($_POST['do_persons_import'])) {

    $data_json_tmp = tempnam(sys_get_temp_dir(), "json");

    // $uploaddir = '/var/www/uploads/';
    // $uploadfile = $uploaddir . basename($_FILES['gtree_data_zip']['name']);
    // $error = $uploadfile; 
    $path_tmp_zip = $_FILES['gtree_data_zip']['tmp_name'];
    $zip = new ZipArchive;
    $res = $zip->open($path_tmp_zip);
    if ($res === TRUE) {
        // $zip->extractTo('/my/destination/dir/');
        copy("zip://".$path_tmp_zip."#data.json", $data_json_tmp);
        $data_json = file_get_contents($data_json_tmp);
        $zip->close();
        $data_json = json_decode($data_json, TRUE);

        $persons = $data_json['persons'];
        foreach ($persons as $k => $p) {
            update_or_insert($p);
            update_parents($p);
        }
        GTreeImage::generate();
        header('Location: ');
    } else {
        $error = 'ошибка';
    }
}

include_once("head.php");
?>

<h3>Импорт данных</h3>
<div class="alert alert-danger">
<strong>Внимание!</strong>
Импорт данных будет происходить с заменой! <br>
Если запись существует то она будет обновлена. <br>
Есть если не существует записи то она будет создана. <br>
</div>

<form action="data_import.php" enctype="multipart/form-data" method="POST">
    <div class="form-group">
        <label for="exampleFormControlFile1">Выберите файл для загрузки (zip архив)</label>
        <input type="file" name="gtree_data_zip" class="form-control-file" id="exampleFormControlFile1">
    </div>
    <button class="btn btn-primary" name="do_persons_import" >Импортировать</button>
    <?php 
    if ($error != '') {
        echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
    }
    ?>
</form>

<?php include_once("footer.php");
<?php

$dir_users = dirname(__FILE__);
include_once($dir_users."/../gtree.php");
GTree::startAdminPage();

$error = '';

$year = 0;
$name = '';
$description = '';
$year_notexactly = 'no';

if (isset($_POST['do_photo_add'])) {

    $name = $_POST['name'];
    $description = $_POST['description'];
    $year = intval($_POST['year']);
    $year_notexactly = isset($_POST['year_notexactly']) ? $_POST['year_notexactly'] : 'no';
    if (!$year_notexactly) {
        $year_notexactly = 'no';
    } else if ($year_notexactly == 'on') {
        $year_notexactly = 'yes';
    }

    $uid = GTree::getRandomString(68);

    $path_img = $_FILES['photo_file']['tmp_name'];

    $img_ok = FALSE;
    $im_png = imagecreatefrompng($path_img);
    if ($im_png !== FALSE) {
        imagejpeg($im_png, $dir_users.'/../public/'.$uid.'.jpg');
        imagedestroy($im_png);
        $img_ok = true;
    }

    if ($img_ok != true) {
        $im_jpg = imagecreatefromjpeg($path_img);
        if ($im_jpg !== FALSE) {
            imagejpeg($im_jpg, $dir_users.'/../public/'.$uid.'.jpg');
            imagedestroy($im_jpg);
            $img_ok = true;
        }
    }

    if ($img_ok != true) {
        $error = 'Не смог обработать картинку';
    } else {
        $conn = GTree::dbConn();
        $stmt = $conn->prepare('INSERT INTO photos(
                uid,
                name,
                description,
                year,
                year_notexactly,
                created,
                updated
            ) VALUES(
                ?,?,?,?,?,NOW(),NOW()
            );');
        $values = array(
            $uid,
            $name,
            $description,
            $year,
            $year_notexactly,
        );
        if (!$stmt->execute($values)) {
            $error = 'Что то пошло не так.';
            error_log(print_r($stmt->errorInfo(), true));
        } else {
            $newphotoid = $conn->lastInsertId();
            GTLog::info('loggined', '[admin#'.GTree::$USERID.'] added new [photo#'.$newphotoid.']');
            header('Location: ./photos.php');
            exit;
        }
    }
}

include_once("head.php");
?>

<h3>Добавить новую фотографию</h3>
<form action="photos_add.php" method="POST" enctype="multipart/form-data" >
    <div class="form-group">
        <label for="exampleFormControlFile1">Выберите файл для загрузки (png/jpg)</label>
        <input type="file" name="photo_file" class="form-control-file" id="exampleFormControlFile1">
    </div>
    <div class="form-group">
        <label for="password">Год снимка</label>
        <select class="form-control" name="year">
            <?php 
                echo '<option value="0">-</option>';
                for ($i = 1800; $i < 2050; $i++) {
                    echo '<option value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input"
                id="year_notexactly" name="year_notexactly" 
                <?php echo $year_notexactly == 'yes' ? 'checked' : '' ?>
            />
            <label class="custom-control-label" for="year_notexactly">Год снимка не точен</label>
        </div>
    </div>
    <div class="form-group">
        <label>Название</label>
        <input class="form-control" name="name" type="text"/>
    </div>
    <div class="form-group">
        <label>Описание</label>
        <textarea class="form-control" name="description"></textarea>
    </div>

    <button class="btn btn-primary" name="do_photo_add" >загрузить</button>
    <?php
    if ($error != '') {
        echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
    }
    ?>
</form>

<?php include_once("footer.php");
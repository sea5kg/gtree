<?php

$dir_users = dirname(__FILE__);
include_once($dir_users."/../gtree.php");
GTree::startAdminPage();

$error = '';
$photoid = 0;
$name = '';
$description = '';
$year = 0;
$year_notexactly = 'no';
$uid = '';

if (isset($_GET['photoid'])) {
    $photoid = intval($_GET['photoid']);
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM photos WHERE id = ?;');
    $stmt->execute(array($photoid));

    if ($row = $stmt->fetch()) {
        $uid = $row['uid'];
        $name = $row['name'];
        $description = $row['description'];
        $year = intval($row['year']);
        $year_notexactly = $row['year_notexactly'];
    } else {
        $error = 'Фотография не найдена';
    }
}

if (isset($_POST['do_photo_update'])) {
    $photoid = intval($_POST['photoid']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $year = intval($_POST['year']);
    $year_notexactly = isset($_POST['year_notexactly']) ? $_POST['year_notexactly'] : 'no';

    if (!$year_notexactly) {
        $year_notexactly = 'no';
    } else if ($year_notexactly == 'on') {
        $year_notexactly = 'yes';
    }

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('UPDATE photos
        SET
            name = ?,
            description = ?,
            year = ?,
            year_notexactly = ?,
            updated = NOW()
        WHERE
            id = ?
        ');
    $values = array(
        $name,
        $description,
        $year,
        $year_notexactly,
        $photoid,
    );
    if (!$stmt->execute($values)) {
        $error = 'Что то пошло не так.';
        error_log(print_r($stmt->errorInfo(), true));
    } else {
        GTLog::info('loggined', '[admin#'.GTree::$USERID.'] update [photo#'.$photoid.']');
        header('Location: ./photos.php');
		exit;
    }
}

include_once("head.php");
?>

<h3>Изменить данные о фотографии</h3>
<img width="100%" src="../public/<?php echo $uid; ?>.jpg"/>
<form action="photos_edit.php?photoid=<?php echo $photoid; ?>" method="POST">
    <input name="photoid" value="<?php echo $photoid; ?>" type="hidden"/>
    <div class="form-group">
        <label>Уникальный Идентификатор</label>
        <input class="form-control" readonly name="lastname" value="<?php echo $uid; ?>" type="text"/>
    </div>
    <div class="form-group">
        <label for="password">Год фотографии</label>
        <select class="form-control" name="year">
            <?php 
                echo '<option value="0">-</option>';
                for ($i = 1800; $i < 2050; $i++) {
                    echo '<option value="'.$i.'" '.($year == $i ? 'selected' : '').'>'.$i.'</option>';
                }
            ?>
        </select>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input"
                id="year_notexactly" name="year_notexactly" 
                <?php echo $year_notexactly == 'yes' ? 'checked' : '' ?>
            />
            <label class="custom-control-label" for="year_notexactly">Год фотографии не точен</label>
        </div>
    </div>

    <div class="form-group">
        <label>Название</label>
        <input class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" type="text"/>
    </div>
    <div class="form-group">
        <label>Описание</label>
        <textarea class="form-control" name="description" value="<?php echo htmlspecialchars($description); ?>"></textarea>
    </div>

    <button class="btn btn-primary" name="do_photo_update" >Обновить</button>
    <?php
    if ($error != '') {
        echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
    }
    ?>
</form>

<?php include_once("footer.php");
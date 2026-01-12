<?php

$dir_persons = dirname(__FILE__);
include_once($dir_persons."/../gtree.php");
GTree::startAdminPage();

if (isset($_POST['do_remove_photo'])) {
    $photoid = intval($_POST['photoid']);

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('DELETE FROM photos WHERE id = ?;');
    if (!$stmt->execute(array($photoid))) {
        $error = 'Что то пошло не так.';
    } else {
        GTLog::info('users', '[admin#'.GTree::$USERID.'] removed [photo#'.$photoid.']');
        header('Location: ./photos.php');
		exit;
    }
}

$filter = '';

if (isset($_GET['filter'])) {
    $filter = trim($_GET['filter']);
}

include_once("head.php");
?>

<h3>Фотографии</h3>
<a class="btn btn-primary" href="photos_add.php">Добавить фотографию</a>
<hr>
<form method="GET" action="photos.php" class="form-inline">
    <div class="form-group mb-2">
        <input type="text" readonly class="form-control-plaintext" id="staticEmail2" value="Фильтр:">
    </div>
    <div class="form-group mx-sm-3 mb-2">
        <input type="text" autofocus class="form-control" name="filter" value="<?php echo htmlspecialchars($filter); ?>" id="inputPassword2">
    </div>
    <button type="submit" class="btn btn-primary mb-2">Применить фильтр</button>
</form>
<br/><br/>
<table class="table">
    <thead class="thead-dark">
        <tr>
            <th># Фотография</th>
            <th>Информация</th>
        </tr>
    </thead>
    <tbody>

    <?php

    $conn = GTree::dbConn();
    $filters = array();
    $values = array();
    if ($filter != '') {
        $filters[] = '(name LIKE ?)';
        $values[] = '%'.$filter.'%';

        $filters[] = '(description LIKE ?)';
        $values[] = '%'.$filter.'%';

        if (is_numeric($filter)) {
            $filter_int = intval($filter);

            $filters[] = '(year = ?)';
            $values[] = $filter_int;
        }
    }

    if (count($filters) > 0) {
        $filters = ' WHERE '.implode(' OR ', $filters);
    } else {
        $filters = '';
    }

    $query = 'SELECT * FROM photos '.$filters.' ORDER BY year';
    // echo $query;
    // $filter
    $stmt = $conn->prepare($query);
    $stmt->execute($values);
    while ($row = $stmt->fetch()) {
        $photoid = $row['id'];
        $uid = $row['uid'];

        $year_print = '';
        if ($row['year'] > 0) {
            $year_print .= $row['year'];
            if ($row['year_notexactly'] == 'yes') {
                $year_print .= ' (пр.)';
            }
        }
        echo '
        <tr>
            <td style="text-align: center;">

                [photo#'.$photoid.'] <br/>
                <img width="300px" width="200px" src="../public/'.$uid.'.jpg"/>
            </td>
            <td>
                <p><strong>'.$year_print.' '.htmlspecialchars($row['name']).'</strong></p>
                <hr>
                Описание:<br>
                <pre>'.htmlspecialchars($row['description']).'</pre>
                <hr>
                <a class="btn btn-primary" href="photos_edit.php?photoid='.$photoid.'">Изменить</a>
                <br><br>

                    <form action="photos.php" method="POST" class="alert alert-danger">
                        Осторожно:
                        <input type="hidden" name="photoid" value="'.$photoid.'"/>
                        <button class="btn btn-danger" name="do_remove_photo">Удалить</button>
                    </form>
            </form>
            </td>
        </tr>
        ';
    }
?>
    </tbody>
</table>

<?php



include_once("footer.php");


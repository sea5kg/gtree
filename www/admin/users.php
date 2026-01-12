<?php

$dir_users = dirname(__FILE__);
include_once($dir_users."/../gtree.php");
GTree::startAdminPage();

if (isset($_POST['do_remove_user'])) {
    $userid = intval($_POST['userid']);
    if ($userid == GTree::$USERID) {
        GTLog::warn('users', '[admin#'.GTree::$USERID.'] try removed yourself');
        header('Location: ./users.php');
		exit;
    }

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?;');
    if (!$stmt->execute(array($userid))) {
        $error = 'Что то пошло не так.';
    } else {
        GTLog::info('users', '[admin#'.GTree::$USERID.'] removed [admin#'.$userid.']');
        header('Location: ./users.php');
		exit;
    }
}

include_once("head.php");
?>
<h3>Пользователи</h3>
<a class="btn btn-primary" href="users_add.php">Добавить пользователя</a>
<br/><br/>
<table class="table">
    <thead class="thead-dark">
        <tr>
            <th>#</th>
            <th>Роль</th>
            <th>E-mail</th>
            <th>Создан</th>
            <th>Комментарий</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>

<?php
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM users');
    $stmt->execute(array());
    while ($row = $stmt->fetch()) {
        $userid = $row['id'];
        echo '
        <tr>
            <td>#'.$row['id'].'</td>
            <td>'.$row['role'].'</td>
            <td>'.$row['username'].' <a class="btn btn-primary" href="users_edit.php?userid='.$userid.'">Изменить</a></td>
            <td>'.$row['created'].' (UTC)</td>
            <td>'.htmlspecialchars($row['comment']).'</td>
            <td>
                <form action="users.php" method="POST">
                    <input type="hidden" name="userid" value="'.$userid.'"/>
                    <button class="btn btn-danger" name="do_remove_user">Удалить</button>
                </form>
            </td>
        </tr>
        ';
    }
?>
    </tbody>
</table>

<?php include_once("footer.php");

<?php

$dir_users = dirname(__FILE__);
include_once($dir_users."/../gtree.php");
GTree::startAdminPage();

$error = '';

$userid = 0;
$username = '';
$role = '';
$created = '';
$comment = '';

if (isset($_GET['userid'])) {
    $userid = intval($_GET['userid']);
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?;');
    $stmt->execute(array($userid));

    if ($row = $stmt->fetch()) {
        $username = $row['username'];
        $role = $row['role'];
        $created = $row['created'];
        $comment = $row['comment'];
    } else {
        $error = 'Пользователь не найдет';
    }
}

if (isset($_POST['do_user_update'])) {
    $userid = intval($_POST['userid']);
    $comment = $_POST['comment'];

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('UPDATE users SET comment = ? WHERE id = ?;');
    if (!$stmt->execute(array($comment, $userid))) {
        $error = 'Что то пошло не так.';
    } else {
        GTLog::info('users', '[admin#'.GTree::$USERID.'] updated comment for [admin#'.$userid.']');
        header('Location: ./users_edit.php?userid='.$userid);
		exit;
    }
}

if (isset($_POST['do_user_reset_password'])) {
    $userid = intval($_POST['userid']);
    $password_sha1 = sha1($_POST['password']);

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('UPDATE users SET userpass = ? WHERE id = ?;');
    if (!$stmt->execute(array($password_sha1, $userid))) {
        $error = 'Что то пошло не так.';
    } else {
        GTLog::info('users', '[admin#'.GTree::$USERID.'] changed password for [admin#'.$userid.']');
        header('Location: ./users_edit.php?userid='.$userid);
		exit;
    }
}


include_once("head.php");
?>

<h3>Изменить данные пользователя пользователя</h3>
<form action="users_edit.php?userid=<?php echo $userid; ?>" method="POST">
    <input readonly name="userid" value="<?php echo $userid; ?>" type="hidden"/>

    <div class="form-group">
        <label for="username">Электронный адрес</label>
        <input class="form-control" readonly name="username" value= "<?php echo $username; ?>" type="text"/>
    </div>
    <div class="form-group">
        <label for="username">Роль</label>
        <input class="form-control" readonly name="role" value= "<?php echo $role; ?>" type="text"/>
    </div>

    <div class="form-group">
        <label for="username">Создан</label>
        <input class="form-control" readonly name="username" value= "<?php echo $created; ?>" type="text"/>
    </div>
    <div class="form-group">
        <label for="password">Комментарий</label>
        <textarea class="form-control" name="comment"><?php echo htmlspecialchars($comment); ?></textarea>
    </div>
    <button class="btn btn-primary" name="do_user_update">Обновить</button>
    <?php
    if ($error != '') {
        echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
    }
    ?>
</form>
<hr>
<h3>Сбросить пароль у пользователя</h3>
<form action="users_edit.php?userid=<?php echo $userid; ?>" method="POST">
    <input readonly name="userid" value="<?php echo $userid; ?>" type="hidden"/>
    <div class="form-group">
        <label for="password">Новый пароль</label>
        <input class="form-control" name="password" type="text"/>
    </div>
    <button class="btn btn-primary" name="do_user_reset_password">Сбросить пароль</button>
</form>

<?php include_once("footer.php");
<?php

$dir_users = dirname(__FILE__);
include_once($dir_users."/../gtree.php");
GTree::startAdminPage();

$error = '';

if (isset($_POST['do_user_add'])) {
    $username = strtolower($_POST['username']);
    $password = $_POST['password'];
    $comment = $_POST['comment'];
    $password_sha1 = sha1($password);

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('INSERT INTO users(username, userpass, role, comment) VALUES(?,?,?,?);');
    if (!$stmt->execute(array($username, $password_sha1, 'admin', $comment))) {
        $error = 'Что то пошло не так.';
    } else {
        $newuserid = $conn->lastInsertId();
        GTLog::info('loggined', '[admin#'.GTree::$USERID.'] added new [admin#'.$newuserid.']');
        header('Location: ./users.php');
		exit;
    }
}


include_once("head.php");
?>

<h3>Добавить нового пользователя</h3>
<form action="users_add.php" method="POST">
    <div class="form-group">
        <label for="username">Электронный адрес</label>
        <input class="form-control" name="username" id="username" type="text"/>
    </div>
    <div class="form-group">
        <label for="password">Пароль</label>
        <input class="form-control" name="password" type="text"/>
    </div>
    <div class="form-group">
        <label for="password">Комментарий</label>
        <textarea class="form-control" name="comment"></textarea>
    </div>
    <button class="btn btn-primary" name="do_user_add" >Добавить</button>
    <?php 
    if ($error != '') {
        echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
    }
    ?>
</form>

<?php include_once("footer.php");
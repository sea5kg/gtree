<?php

$dir_persons = dirname(__FILE__);
include_once($dir_persons."/../gtree.php");
include_once($dir_persons."/../gtree_image.php");
GTree::startAdminPage();

$error = '';

$personid = 0;
$firstname = '';
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
$mother_fullname = '';
$father_fullname = '';

$sex = 'male';

if (isset($_GET['personid'])) {
    $personid = intval($_GET['personid']);
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('SELECT * FROM persons WHERE id = ?;');
    $stmt->execute(array($personid));

    if ($row = $stmt->fetch()) {
        $firstname = $row['firstname'];
        $secondname = $row['secondname'];
        $lastname = $row['lastname'];
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
}

if (isset($_POST['do_person_update'])) {
    $personid = intval($_POST['personid']);
    $firstname = $_POST['firstname'];
    $secondname = $_POST['secondname'];
    $lastname = $_POST['lastname'];
    $bornlastname = $_POST['bornlastname'];
    $sex = $_POST['sex'];
    $bornyear = intval($_POST['bornyear']);
    $bornmonth = intval($_POST['bornmonth']);
    $bornday = intval($_POST['bornday']);
    $yearofdeath = intval($_POST['yearofdeath']);
    $monthofdeath = intval($_POST['monthofdeath']);
    $dayofdeath = intval($_POST['dayofdeath']);
    $mother = intval($_POST['mother']);
    $father = intval($_POST['father']);
    $private = $_POST['private'];
    $bornyear_notexactly = $_POST['bornyear_notexactly'];
    $yearofdeath_notexactly = $_POST['yearofdeath_notexactly'];

    if (!$bornyear_notexactly) {
        $bornyear_notexactly = 'no';
    } else if ($bornyear_notexactly == 'on') {
        $bornyear_notexactly = 'yes';
    }

    if (!$yearofdeath_notexactly) {
        $yearofdeath_notexactly = 'no';
    } else if ($yearofdeath_notexactly == 'on') {
        $yearofdeath_notexactly = 'yes';
    }

    $fullname = $lastname;
    if ($bornlastname != '') {
        $fullname .= ' ('.$bornlastname.')';
    }
    $fullname .= ' '.$firstname.' '.$secondname;

    $conn = GTree::dbConn();
    $stmt = $conn->prepare('UPDATE persons
        SET
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
            bornyear_notexactly = ?,
            yearofdeath_notexactly = ?
        WHERE
            id = ?
        ');
    $values = array(
        $fullname,
        $firstname,
        $secondname,
        $lastname,
        $bornlastname,
        $sex,
        $bornyear,
        $bornmonth,
        $bornday,
        $yearofdeath,
        $monthofdeath,
        $dayofdeath,
        $mother,
        $father,
        $private,
        $bornyear_notexactly,
        $yearofdeath_notexactly,
        $personid,
    );
    if (!$stmt->execute($values)) {
        $error = 'Что то пошло не так.';
        error_log(print_r($stmt->errorInfo(), true));
    } else {
        GTLog::info('persons', '[admin#'.GTree::$USERID.'] update [person#'.$personid.']');
        GTreeImage::generate();
        header('Location: ./persons.php');
		exit;
    }
}

$persons_list = array();

$conn = GTree::dbConn();
$stmt = $conn->prepare('SELECT * FROM persons ORDER BY bornyear;');
$stmt->execute(array());
while ($row = $stmt->fetch()) {
    $caption = '('.$row['bornyear'].') '.$row['fullname'];
    $persons_list[] = array(
        'id' => $row['id'],
        'sex' => $row['sex'],
        'caption' => $caption,
    );
    if ($row['id'] == $father) {
        $father_fullname = $caption;
    }

    if ($row['id'] == $mother) {
        $mother_fullname = $caption;
    }
}

include_once("head.php");
?>

<br>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="persons.php">Персоны</a></li>
    <li class="breadcrumb-item active" aria-current="page">Изменить данные о персоне #<?php echo $personid; ?></li>
  </ol>
</nav>

<form action="persons_edit.php" method="POST">
    <input name="personid" value="<?php echo $personid; ?>" type="hidden"/>
    <div class="form-group">
        <label>Уникальный Идентификатор</label>
        <input class="form-control" readonly name="lastname" value="<?php echo $uid; ?>" type="text"/>
    </div>
    <?php 
    if ($error != '') {
        echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
    }
    ?>

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Фамилия</label>
                <input class="form-control" name="lastname" value="<?php echo $lastname; ?>" type="text"/>
            </div>
        </div>
        <div class="col-sm-4">
            <label for="password">Пол</label>
            <select class="form-control" id="sex" name="sex">
                <option value="male" <?php echo $sex == 'male' ? 'selected' : '' ?> >Мужской</option>
                <option value="female" <?php echo $sex == 'female' ? 'selected' : '' ?> >Женский</option>
            </select>
        </div>
        <div class="col-sm-4" id="bornlastname_form" style="display: none">
            <label>Фамилия при рождении</label>
            <input class="form-control" name="bornlastname" value="<?php echo $bornlastname; ?>" type="text"/>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Имя</label>
                <input class="form-control" name="firstname" value="<?php echo $firstname; ?>" type="text"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="password">Отчество</label>
                <input class="form-control" name="secondname" value="<?php echo $secondname; ?>" type="text"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="password">Приватные данные</label>
        <select class="form-control" name="private">
            <option value="yes" <?php echo ($private == 'yes' ? ' selected ' : ''); ?>>Да</option>
            <option value="no" <?php echo ($private == 'no' ? ' selected ' : ''); ?>>Нет</option>
        </select>
    </div>

    <hr>
    <div class="form-group">
        <label for="password">Дата рождения</label>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label for="password">День</label>
                    <select class="form-control" name="bornday">
                        <?php 
                            echo '<option value="0">-</option>';
                            for ($i = 1; $i <= 31; $i++) {
                                echo '<option value="'.$i.'" '.($bornday == $i ? 'selected' : '').'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="password">Месяц</label>
                    <select class="form-control" name="bornmonth">
                        <?php
                            echo '<option value="0">-</option>';
                            foreach(GTree::$MONTHES as $k => $v) {
                                echo '<option value="'.$k.'" '.($bornmonth == $k ? 'selected' : '').'>'.$v.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="password">Год</label>
                    <select class="form-control" name="bornyear">
                        <?php 
                            echo '<option value="0">-</option>';
                            for ($i = 1800; $i < 2050; $i++) {
                                echo '<option value="'.$i.'" '.($bornyear == $i ? 'selected' : '').'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                            id="bornyear_notexactly" name="bornyear_notexactly" 
                            <?php echo $bornyear_notexactly == 'yes' ? 'checked' : '' ?>
                        />
                        <label class="custom-control-label" for="bornyear_notexactly">Год рождения не точен</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <label for="password">Дата смерти</label>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label for="password">День</label>
                    <select class="form-control" name="dayofdeath">
                        <?php 
                            echo '<option value="0">-</option>';
                            for ($i = 1; $i <= 31; $i++) {
                                echo '<option value="'.$i.'" '.($dayofdeath == $i ? 'selected' : '').'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="password">Месяц</label>
                    <select class="form-control" name="monthofdeath">
                        <?php 
                            echo '<option value="0">-</option>';
                            foreach(GTree::$MONTHES as $k => $v) {
                                echo '<option value="'.$k.'" '.($monthofdeath == $k ? 'selected' : '').'>'.$v.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="password">Год</label>
                    <select class="form-control" name="yearofdeath">
                        <?php 
                            echo '<option value="0">-</option>';
                            for ($i = 1800; $i < 2050; $i++) {
                                echo '<option value="'.$i.'" '.($yearofdeath == $i ? 'selected' : '').'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" 
                            id="yearofdeath_notexactly" name="yearofdeath_notexactly" 
                            <?php echo $yearofdeath_notexactly == 'yes' ? 'checked' : '' ?>
                        />
                        <label class="custom-control-label" for="yearofdeath_notexactly">Год смерти не точен</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label>Мать</label>
                    <input type="hidden" readonly id="motherId" name="mother" value="<?php echo $mother; ?>"/>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" readonly 
                            name="mother_fullname"
                            id="motherFullname"
                            value="<?php echo htmlspecialchars($mother_fullname); ?>"
                            aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="selectMotherBtn" type="button">...</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label>Отец</label>
                    <input type="hidden" readonly id="fatherId" name="father" value="<?php echo $father; ?>"/>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" readonly 
                            name="father_fullname"
                            id="fatherFullname"
                            value="<?php echo htmlspecialchars($father_fullname); ?>"
                            aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="selectFatherBtn" type="button">...</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary" name="do_person_update" >Обновить</button>

</form>

<!-- Select Person -->

<div class="modal fade bd-example-modal-lg" id="modalSelectPerson" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Выбрать персону</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <input class="form-control" name="search" id="searchPersonsInAList" value="" placeholder="Быстрый поиск..." type="text"/>
        <br/>
        <select class="custom-select" id="selectPersonsList" size="10">
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="selectPersonBtnFinish" class="btn btn-primary">Выбрать</button>
      </div>
    </div>
  </div>
</div>

<script>

function updateFieldSex() {
    var el = $('#sex').children("option:selected");
    if (el.val() == 'male') {
        $('#bornlastname_form').hide();
    } else {
        $('#bornlastname_form').show();
    }
}

$('#sex').unbind().bind('change', function() {
    updateFieldSex();
})

updateFieldSex();

<?php 
    echo 'var persons_list = '.json_encode($persons_list).';';
?>


function updatePersonsList(search) {
    search = search.toLowerCase();
    var els = $('#selectPersonsList option');
    for (var i = 0; i < els.length; i++) {
        var el = $(els[i]);
        var text = el.html().toLowerCase();
        if (text.indexOf(search) != -1) {
            el.css({'display': ''});
        } else {
            el.css({'display': 'none'});
        }
        // text.
        // console.log();
    }
}


function showPersonsList(sex, current_id, callback_done) {
    $('#modalSelectPerson').unbind().on('shown.bs.modal', function() {
        $('#selectPersonsList').html('');
        $('#selectPersonsList').append('<option ' + (current_id == 0 ? 'selected' : '' )  + ' value="0">-</option>');

        for (var i in persons_list) {
            if (persons_list[i].sex == sex) {
                var selected_opt = '';
                if (persons_list[i].id == current_id) {
                    selected_opt = ' selected ';
                }
                $('#selectPersonsList').append('<option ' + selected_opt + ' value="' + persons_list[i].id + '">' + persons_list[i].caption + '</option>')
            }
        }

        $("#searchPersonsInAList").val('');
        updatePersonsList('');
        console.log('shown.bs.modal');
        $("#searchPersonsInAList").unbind().bind('keyup', function() {
            updatePersonsList($(this).val());
        });

        $('#selectPersonBtnFinish').unbind().bind('click', function() {
            var el = $('#selectPersonsList').children("option:selected");
            callback_done(el.val(), el.html());
            $('#modalSelectPerson').modal('hide');
        })
    })

    $('#modalSelectPerson').modal({
        backdrop: true,
        keyboard: true,
        focus: true,
        show: true
    });
}

$('#selectMotherBtn').unbind().bind('click', function() {
    var motherId = parseInt($('#motherId').val(),10);
    showPersonsList('female', motherId, function(new_id, caption) {
        $('#motherId').val(new_id)
        $('#motherFullname').val(caption)
    });
})

$('#selectFatherBtn').unbind().bind('click', function() {
    var fatherId = parseInt($('#fatherId').val(),10);
    showPersonsList('male', fatherId, function(new_id, caption) {
        $('#fatherId').val(new_id)
        $('#fatherFullname').val(caption)
    });
})

</script>


<?php include_once("footer.php");
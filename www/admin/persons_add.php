<?php

$dir_persons = dirname(__FILE__);
include_once($dir_persons."/../gtree.php");
include_once($dir_persons."/../gtree_image.php");
GTree::startAdminPage();

$error = '';

if (isset($_POST['do_person_add'])) {
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
    $gtline = 0;
    
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
    $uid = GTree::getRandomString(128);
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('INSERT INTO persons(
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
            bornyear_notexactly,
            yearofdeath_notexactly,
            uid
        ) VALUES(
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?
        );');
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
        $gtline,
        $bornyear_notexactly,
        $yearofdeath_notexactly,
        $uid,
    );
    if (!$stmt->execute($values)) {
        $error = 'Что то пошло не так.';
        error_log(print_r($stmt->errorInfo(), true));
    } else {
        $newpersonid = $conn->lastInsertId();
        GTLog::info('persons', '[admin#'.GTree::$USERID.'] added new [person#'.$newpersonid.']');
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
}

include_once("head.php");
?>
<br>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="persons.php">Персоны</a></li>
    <li class="breadcrumb-item active" aria-current="page">Добавить новую персону</li>
  </ol>
</nav>

<form action="persons_add.php" method="POST">
    <?php 
        if ($error != '') {
            echo '<div class="alert alert-danger" style="margin-top: 20px">'.$error.'</div>';
        }
    ?>

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Фамилия</label>
                <input class="form-control" name="lastname" type="text"/>            
            </div>
        </div>
        <div class="col-sm-4">
            <label for="password">Пол</label>
            <select class="form-control" id="sex" name="sex">
                <option value="male">Мужской</option>
                <option value="female">Женский</option>
            </select>
        </div>
        <div class="col-sm-4" id="bornlastname_form" style="display: none">
            <label>Фамилия при рождении</label>
            <input class="form-control" name="bornlastname" type="text"/>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Имя</label>
                <input class="form-control" name="firstname" type="text"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="password">Отчество</label>
                <input class="form-control" name="secondname" type="text"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="password">Приватные данные</label>
        <select class="form-control" name="private">
            <option value="no">Нет</option>
            <option value="yes">Да</option>
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
                                echo '<option value="'.$i.'">'.$i.'</option>';
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
                                echo '<option value="'.$k.'">'.$v.'</option>';
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
                                echo '<option value="'.$i.'">'.$i.'</option>';
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
                                echo '<option value="'.$i.'">'.$i.'</option>';
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
                                echo '<option value="'.$k.'">'.$v.'</option>';
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
                                echo '<option value="'.$i.'">'.$i.'</option>';
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
                    <input type="hidden" readonly id="motherId" name="mother" value="0"/>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" readonly 
                            name="mother_fullname"
                            id="motherFullname"
                            value="-"
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
                    <input type="hidden" readonly id="fatherId" name="father" value="0"/>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" readonly 
                            name="father_fullname"
                            id="fatherFullname"
                            value="-"
                            aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="selectFatherBtn" type="button">...</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-primary" name="do_person_add" >Добавить</button>

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

$('#sex').unbind().bind('change', function() {
    var el = $('#sex').children("option:selected");
    if (el.val() == 'male') {
        $('#bornlastname_form').hide();
    } else {
        $('#bornlastname_form').show();
    }
})

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
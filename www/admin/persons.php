<?php

$dir_persons = dirname(__FILE__);
include_once($dir_persons."/../gtree.php");
include_once($dir_persons."/../gtree_image.php");
GTree::startAdminPage();

if (isset($_POST['do_remove_person'])) {
    $personid = intval($_POST['personid']);
    
    $conn = GTree::dbConn();
    $stmt = $conn->prepare('DELETE FROM persons WHERE id = ?;');
    if (!$stmt->execute(array($personid))) {
        echo 'Что то пошло не так.';
    } else {
        $stmt2 = $conn->prepare('UPDATE persons SET mother = 0 WHERE mother = ?;');
        $stmt2->execute(array($personid));
        $stmt3 = $conn->prepare('UPDATE persons SET father = 0 WHERE father = ?;');
        $stmt3->execute(array($personid));

        GTLog::info('persons', '[admin#'.GTree::$USERID.'] removed [person#'.$personid.']');
        GTreeImage::generate();
        echo "OK";
		exit;
    }
}

$filter = '';

if (isset($_GET['filter'])) {
    $filter = trim($_GET['filter']);
}

include_once("head.php");
?>

<h3>Персоны</h3>
<a class="btn btn-primary" href="persons_add.php">Добавить персону</a>
<hr>
<form method="GET" action="persons.php" class="form-inline">
    <div class="form-group mb-2">
        <input type="text" readonly class="form-control-plaintext" id="staticEmail2" value="Фильтр:">
    </div>
    <div class="form-group mx-sm-3 mb-2">
        <input type="text" autofocus class="form-control" name="filter" value="<?php echo htmlspecialchars($filter); ?>" id="inputPassword2">
    </div>
    <button type="submit" class="btn btn-primary mb-2">Применить фильтр</button>
</form>
<br/><br/>

    <?php

    $conn = GTree::dbConn();
    $filters = array();
    $values = array();
    if ($filter != '') {
        $filters[] = '(fullname LIKE ?)';
        $values[] = '%'.$filter.'%';

        $filters[] = '(bornlastname LIKE ?)';
        $values[] = '%'.$filter.'%';

        $filters[] = '(lastname LIKE ?)';
        $values[] = '%'.$filter.'%';

        $filters[] = '(firstname LIKE ?)';
        $values[] = '%'.$filter.'%';

        $filters[] = '(secondname LIKE ?)';
        $values[] = '%'.$filter.'%';
        if (is_numeric($filter)) {
            $filter_int = intval($filter);

            $filters[] = '(bornyear = ?)';
            $values[] = $filter_int;

            $filters[] = '(id = ?)';
            $values[] = $filter_int;

            $filters[] = '(father = ?)';
            $values[] = $filter_int;

            $filters[] = '(mother = ?)';
            $values[] = $filter_int;

            $filters[] = '(yearofdeath = ?)';
            $values[] = $filter_int;
        }
    }

    if (count($filters) > 0) {
        $filters = ' WHERE '.implode(' OR ', $filters);
    } else {
        $filters = '';
    }

    $query = 'SELECT * FROM persons '.$filters.' ORDER BY bornyear';
    // echo $query;
    // $filter
    $stmt = $conn->prepare($query);
    $stmt->execute($values);
    while ($row = $stmt->fetch()) {
        $personid = $row['id'];
        $mother = $row['mother'];
        if ($mother != 0) {
            $mother = '[person#'.$mother.']';
        } else {
            $mother = '-';
        }

        $father = $row['father'];
        if ($father != 0) {
            $father = '[person#'.$father.']';
        } else {
            $father = '-';
        }

        $bornyear_notexactly = '';
        if ($row['bornyear_notexactly'] == 'yes') {
            $bornyear_notexactly = ' (не точно)';
        }

        $yearofdeath_notexactly = '';
        if ($row['yearofdeath_notexactly'] == 'yes') {
            $yearofdeath_notexactly = ' (не точно)';
        }

        echo '
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">[person#'.$personid.'] <strong>'.$row['fullname'].'</strong></h5>
                <h6 class="card-subtitle mb-2 text-muted">Годы жизни: '.$row['bornyear'].$bornyear_notexactly.' - '.$row['yearofdeath'].$yearofdeath_notexactly.'</h6>
                <p class="card-text"><small>Отец: '.$father.'; Мать: '.$mother.'</small></p>
                <a class="btn btn-primary"
                        data-toggle="tooltip" data-placement="bottom" title="Редактировать"
                        href="persons_edit.php?personid='.$personid.'"><i class="fas fa-user-edit"></i></a>

                    <a class="btn btn-primary"
                        data-toggle="tooltip" data-placement="bottom" title="Биография"
                        href="biographies.php?personid='.$personid.'"><i class="fas fa-file-contract"></i></a>

                    <div class="btn btn-danger do-remove-person"
                        data-toggle="tooltip" data-placement="bottom" title="Удалить"
                        personid="'.$personid.'"><i class="fas fa-trash-alt"></i></div>

            </div>
        </div><br>
        ';
    }
?>

<!-- Select Person -->

<div class="modal fade bd-example-modal-lg" id="modalDeletePerson" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Удалить персону</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        [person#<div class="d-inline" id="modalDeletePersonId">-</div>]
         Вы уверены что хотите удалить персону?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
        <button type="button" id="deletePersonBtnConfirm" class="btn btn-danger">Да, удалить!</button>
      </div>
    </div>
  </div>
</div>

<script>
    $('.do-remove-person').unbind().bind('click', function(){
        var el = $(this);
        var _personid = el.attr('personid');

        $('#modalDeletePerson').unbind().on('shown.bs.modal', function() {
            $('#modalDeletePersonId').html(_personid);
            $('#deletePersonBtnConfirm').unbind().bind('click', function() {
                $.ajax({
                    url: 'persons.php',
                    type: "POST",
                    data: {
                        "do_remove_person": "",
                        "personid": _personid,
                    },
                }).done(function(data) {
                    if (data == 'OK') {
                        $('#modalDeletePerson').modal('hide');
                        el.parent().parent().remove();
                    } else {
                        alert(data);
                    }
                })
            })
        })

        $('#modalDeletePerson').modal({
            backdrop: true,
            keyboard: true,
            focus: true,
            show: true
        });
    })

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>

<?php



include_once("footer.php");


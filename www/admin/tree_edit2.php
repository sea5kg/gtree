<?php

$dir_persons = dirname(__FILE__);
include_once($dir_persons."/../gtree.php");
GTree::startAdminPage();

if (isset($_POST['update_gtlines_in_tree'])) {
  $data = json_decode($_POST['data'], true);
  $conn = GTree::dbConn();

  foreach ($data as $i => $r) {
    $stmt = $conn->prepare('UPDATE persons SET tree_x = ?, tree_y = ? WHERE id = ?');
    $stmt = $stmt->execute(array(
      intval($r['tree_x']),
      intval($r['tree_y']),
      intval($r['id']),
    ));
  }
  echo "OK";
  return;
}

include_once("head.php");
?>
<br>
<div>
  <div class="btn btn-primary" id="save_tree">Сохранить измененное дерево</div>
</div>
<br>
<div class="genealogical-tree">
      <canvas id="gtree" width="500" height="500"></canvas>
    </div>
    <script>

    <?php
        // father / mother
        $conn = GTree::dbConn();
        $persons = array();
        $stmt = $conn->prepare('SELECT * FROM persons ORDER BY bornyear');
        $stmt->execute(array());
        while ($row = $stmt->fetch()) {
          if ($row['bornyear'] == 0) {
            continue;
          }

          $personid = intval($row['id']);
          $lastname = $row['lastname'];
          if ($row['bornlastname'] != '') {
            $lastname = $row['bornlastname'];
          }

          $tree_x = intval($row['tree_x']);
          $tree_y = intval($row['tree_y']);
          if ($tree_x < 0) $tree_x = 0;
          if ($tree_y < 0) $tree_y = 0;

          $persons[$personid] = array(
            'firstname' => $row['firstname'],
            'lastname' => $lastname,
            'bornyear' => intval($row['bornyear']),
            'bornyear_notexactly' => $row['bornyear_notexactly'],
            'sex' => intval($row['sex']),
            'yearofdeath' => intval($row['yearofdeath']),
            'yearofdeath_notexactly' => $row['yearofdeath_notexactly'],
            'mother' => intval($row['mother']),
            'father' => intval($row['father']),
            'gtline' => intval($row['gtline']),
            'tree_x' => $tree_x,
            'tree_y' => $tree_y,
          );
        }

        echo 'var gtree_minyear = '.GTree::getMinBornYear().";\r\n";
        echo 'var gtree_maxyear = '.GTree::getMaxBornYear().";\r\n";
        echo 'var gtree_padding = '.GTree::$gtree_padding.";\r\n";
        echo 'var gtree_yearstep = '.GTree::$gtree_yearstep.";\r\n";
        echo 'var gtree_card_width = '.GTree::$gtree_card_width.";\r\n";
        echo 'var gtree_card_height = '.GTree::$gtree_card_height.";\r\n";
        echo 'var gtree_gtcol = '.GTree::$gtree_gtcol.";\r\n";
        echo 'var gtree_gtline = '.GTree::$gtree_gtline.";\r\n";
        echo 'var gtree_gtline_top = '.GTree::$gtree_gtline_top.";\r\n";
        echo 'var gtree_height = '.GTree::calculateHeight().";\r\n";
        echo 'var gtree_width = '.GTree::calculateWidth().";\r\n";
        
        echo 'var gt = '.json_encode($persons, JSON_PRETTY_PRINT)."; \r\n";
    ?>
      console.log(gt);
      var canvas = document.getElementById("gtree");
      var ctx = canvas.getContext("2d");
      var max_tree_x = -1;
      var max_tree_y = -1;

      function update_image_size() {
        var new_max_tree_x = 0;
        var new_max_tree_y = 0;
        for (var i in gt) {
          gt[i].highlight = false;
          new_max_tree_x = Math.max(gt[i].tree_x, new_max_tree_x);
          new_max_tree_y = Math.max(gt[i].tree_y, new_max_tree_y);
        }
        if (new_max_tree_x != max_tree_x || new_max_tree_y != max_tree_y) {
          max_tree_y = new_max_tree_y;
          max_tree_x = new_max_tree_x;

          gtree_width = (max_tree_x + 1) * gtree_gtcol + 2 * gtree_padding + 100;
          gtree_height = (max_tree_y + 1) * gtree_gtline + 2 * gtree_padding + 100;
          canvas.width  = gtree_width;
          canvas.height = gtree_height;
          canvas.style.width  = gtree_width + 'px';
          canvas.style.height = gtree_height + 'px';
        }
        /*console.log("max_tree_x = ", max_tree_x);
        console.log("gtree_width = ", gtree_width);
        console.log("max_tree_y = ", max_tree_y);
        console.log("gtree_height = ", gtree_height);*/
      }

      for (var i in gt) {
        gt[i].highlight = false;
      }

      function calcX_in_px(tree_x) {
        return gtree_padding + tree_x * gtree_gtcol;
      }

      function calcY_in_px(tree_y) {
        return gtree_padding + tree_y * gtree_gtline;
      }

      update_image_size();

      function update_gtree() {
        update_image_size();
        console.log("update_gtree");

        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, gtree_width, gtree_height);
        ctx.strokeRect(0, 0, gtree_width, gtree_height);
        ctx.strokeStyle = "#E9F0E0";

        for (var x = gtree_padding; x <= gtree_width; x = x + gtree_gtcol) {
          var x1 = x - (gtree_gtcol - gtree_card_width) / 2;
          ctx.beginPath();
          ctx.moveTo(x1, 0);
          ctx.lineTo(x1, gtree_height);
          ctx.stroke();
        }

        for (var y = gtree_padding; y <= gtree_height; y = y + gtree_gtline) {
          var y1 = y - (gtree_gtline - gtree_card_height) / 2;
          ctx.beginPath();
          ctx.moveTo(0, y1);
          ctx.lineTo(gtree_width, y1);
          ctx.stroke();
        }

        ctx.strokeStyle = "black";
        ctx.fillStyle = "black";
        // ctx.fillRect(10, 10, 100, 100);
        ctx.lineWidth = 3;

        ctx.font = "16px Arial";

        ctx.lineWidth = 1;

        // cards
        for (var i in gt) {
          var p = gt[i];
          // console.log(p);
          var x1 = gtree_padding + p.tree_x * gtree_gtcol;
          var y1 = gtree_padding + p.tree_y * gtree_gtline;
          gt[i].x1 = x1;
          gt[i].y1 = y1;

          // fill
          ctx.fillStyle = selectedCard == i ? "#E6ECDF" : "white";
          ctx.fillRect(x1, y1, gtree_card_width, gtree_card_height);
          ctx.fillStyle = "black";
          var years_print = p.bornyear;
          if (p.bornyear_notexactly == 'yes') {
            years_print += '(пр.)';
          }

          if (p.yearofdeath > 0) {
            years_print += ' - ' + p.yearofdeath;
            if (p.yearofdeath_notexactly == 'yes') {
              years_print += '(пр.)';
            }
          }

          ctx.strokeRect(x1, y1, gtree_card_width, gtree_card_height);
          var d = 16;
          ctx.fillText('' + years_print, x1 + 3, y1 + d);
          d += 16;
          ctx.fillText('' + p.firstname, x1 + 3, y1 + d);
          if (p.lastname) {
            d += 16;
            ctx.fillText('' + p.lastname, x1 + 3, y1 + d);
          }
        }

        // parents
        ctx.lineWidth = 1;
        for (var i in gt) {
          var p = gt[i];

          if (p.mother > 0 && p.father > 0) {
            var mo = gt[p.mother];
            var fa = gt[p.father];

            var mo_x1 = calcX_in_px(mo.tree_x);
            var mo_y1 = calcY_in_px(mo.tree_y);

            var fa_x1 = calcX_in_px(fa.tree_x);
            var fa_y1 = calcY_in_px(fa.tree_y);

            var x1 = calcX_in_px(p.tree_x);
            var y1 = calcY_in_px(p.tree_y);

            mo_x1 += gtree_card_width / 2;
            mo_y1 += gtree_card_height;
            fa_x1 += gtree_card_width / 2;
            fa_y1 += gtree_card_height;
            x1 += gtree_card_width / 2;

            var x2 = (mo_x1 + fa_x1) / 2;
            // var y2 = Math.max(fa_y1, mo_y1) + gtree_card_width / 3;
            var y2 = Math.max(fa_y1, mo_y1) + 30;
            var y3 = y2 + 20;

            ctx.beginPath();
            ctx.arc(mo_x1, mo_y1, 6, 0, Math.PI);
            ctx.fill();

            ctx.beginPath();
            ctx.arc(fa_x1, fa_y1, 6, 0, Math.PI);
            ctx.fill();

            ctx.beginPath();
            ctx.moveTo(mo_x1, mo_y1);
            ctx.lineTo(mo_x1, y2);
            ctx.lineTo(fa_x1, y2);
            ctx.lineTo(fa_x1, fa_y1);
            ctx.stroke();

            ctx.fillRect(x2-3, y2-3, 6, 6);
            ctx.fillRect(x2-3, y3-3, 6, 6);

            ctx.beginPath();
            ctx.moveTo(x2, y2);
            ctx.lineTo(x2, y3);
            ctx.lineTo(x1, y3);
            ctx.lineTo(x1, y1);
            ctx.stroke();

            // arrow
            ctx.beginPath();
            ctx.moveTo(x1-6, y1-12);
            ctx.lineTo(x1+6, y1-12);
            ctx.lineTo(x1, y1);
            ctx.lineTo(x1-6, y1-12);
            ctx.fill();

          }
        }
      }

      var selectedCard = -1;
      var movingEnable = false;

      canvas.onmouseover = function(event) {
          // var target = event.target;
          movingEnable = false;
      };

      canvas.onmouseout = function(event) {
          // var target = event.target;
          movingEnable = false;
      };

      canvas.onmouseup = function(event) {
        var target = event.target;
        if (movingEnable) {
          movingEnable = false;
        }
      }

      canvas.onmousedown = function(event) {
          var target = event.target;
          if (selectedCard >= 0) {
            // console.log(target);
            movingEnable = true;
          }
      };

      canvas.onmousemove = function(event) {
          var target = event.target;
          // console.log(event);
          var co = target.getBoundingClientRect();
          // console.log(co);
          var x0 = event.clientX - co.left;
          var y0 = event.clientY - co.top;

          if (movingEnable && selectedCard >= 0) {
            var t_x = Math.floor((x0 - gtree_padding) / gtree_gtcol);
            var t_y = Math.floor((y0 - gtree_padding) / gtree_gtline);

            // console.log(y0);
            if (gt[selectedCard].tree_x != t_x || gt[selectedCard].tree_y != t_y) {
              gt[selectedCard].tree_x = t_x;
              gt[selectedCard].tree_y = t_y;
              update_gtree();
            }
            return;
          }

          var changesExists = false;
          selectedCard = -1;
          for (var i in gt) {
            var x1 = gt[i].x1;
            var x2 = x1 + gtree_card_width;
            var y1 = gt[i].y1;
            var y2 = y1 + gtree_card_height;
            var res = false;

            if (x0 > x1 && x0 < x2 && y0 > y1 && y0 < y2) {
              res = true;
              target.style.cursor = 'pointer';
              selectedCard = i;
            }

            if (gt[i].highlight != res) {
              changesExists = true;
              gt[i].highlight = res;
            }
          }
          if (selectedCard < 0) {
            target.style.cursor = 'default';
          }
          if (changesExists) {
            update_gtree();
          }
      };

      update_gtree();


      $('#save_tree').unbind().bind('click', function() {
        var dataSend = [];
        for (var id in gt) {
          dataSend.push({
            "id": id,
            "tree_x": gt[id].tree_x,
            "tree_y": gt[id].tree_y,
          });
        }
        $.ajax({
          url: 'tree_edit2.php',
          type: "POST",
          data: {
            "update_gtlines_in_tree": "",
            "data": JSON.stringify(dataSend),
          },
          // dataType: dataType
        }).done(function(data) {
          alert(data);
        })
      })
    </script>

    </tbody>
</table>

<?php



include_once("footer.php");


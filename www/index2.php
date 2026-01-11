<?php
      include_once("gtree.php");
      include_once("head.php");
?>

    <div class="genealogical-tree" id="renderimage_client">
      <canvas id="gtree" width="500" height="500"></canvas>
    </div>
    <script>

    <?php
        // father / mother
        $conn = GTree::dbConn();
        $persons = array();
        $stmt = $conn->prepare('SELECT * FROM persons ORDER BY bornyear');
        $minyear = 5000;
        $maxyear = 0;
        $stmt->execute(array());
        while ($row = $stmt->fetch()) {
          if ($row['bornyear'] == 0) {
            continue;
          }

          $minyear = $row['bornyear'] < $minyear ? $row['bornyear'] : $minyear;
          $maxyear = $row['bornyear'] > $maxyear ? $row['bornyear'] : $maxyear;

          if ($row['monthofdeath'] > 0) {
            $maxyear = $row['monthofdeath'] > $maxyear ? $row['monthofdeath'] : $maxyear;
          }

          $personid = intval($row['id']);
          $lastname = $row['lastname'];
          if ($row['bornlastname'] != '') {
            $lastname = $row['bornlastname'];
          }

          if ($row['private'] == 'yes') {
            $lastname = '';
          }

          $persons[$personid] = array(
            'firstname' => $row['firstname'],
            'lastname' => $lastname,
            'bornyear' => intval($row['bornyear']),
            'bornyear_notexactly' => $row['bornyear_notexactly'],
            'mother' => intval($row['mother']),
            'father' => intval($row['father']),
            'gtline' => intval($row['gtline']),
          );
        }

        echo 'var gtree_minyear = '.GTree::getMinBornYear().";\r\n";
        echo 'var gtree_maxyear = '.GTree::getMaxBornYear().";\r\n";
        echo 'var gtree_padding = '.GTree::$gtree_padding.";\r\n";
        echo 'var gtree_yearstep = '.GTree::$gtree_yearstep.";\r\n";
        echo 'var gtree_card_width = '.GTree::$gtree_card_width.";\r\n";
        echo 'var gtree_card_height = '.GTree::$gtree_card_height.";\r\n";
        echo 'var gtree_gtline = '.GTree::$gtree_gtline.";\r\n";
        echo 'var gtree_gtline_top = '.GTree::$gtree_gtline_top.";\r\n";
        echo 'var gtree_height = '.GTree::calculateHeight().";\r\n";
        echo 'var gtree_width = '.GTree::calculateWidth().";\r\n";
        echo 'var gt = '.json_encode($persons, JSON_PRETTY_PRINT)."; \r\n";
    ?>

      for (var i in gt) {
        gt[i].highlight = false;
      }

      function calcX_in_px(year) {
        var ret = year - gtree_minyear;
        ret = ret * gtree_yearstep + gtree_padding;
        return ret;
      }

      var canvas = document.getElementById("gtree");
      var ctx = canvas.getContext("2d");
      canvas.width  = gtree_width;
      canvas.height = gtree_height;
      canvas.style.width  = gtree_width + 'px';
      canvas.style.height = gtree_height + 'px';


      function update_gtree() {

        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, gtree_width, gtree_height);
        ctx.strokeRect(0, 0, gtree_width, gtree_height);

        ctx.strokeStyle = "black";
        ctx.fillStyle = "black";
        // ctx.fillRect(10, 10, 100, 100);
        ctx.lineWidth = 3;

        ctx.beginPath();
        ctx.moveTo(gtree_padding, gtree_padding + 25);
        ctx.lineTo(gtree_width - gtree_padding, gtree_padding + 25);
        ctx.stroke();

        ctx.font = "16px Arial";
        for (var y = gtree_maxyear; y >= gtree_minyear; y = y - 10) {
          x1 = calcX_in_px(y);

          ctx.beginPath();
          ctx.moveTo(x1, gtree_padding + 10);
          ctx.lineTo(x1, gtree_padding + 30);
          ctx.stroke();

          ctx.fillText('' + y, x1 + 3, 30);

          // console.log(y);
        }

        ctx.lineWidth = 1;
        // parents
        for (var i in gt) {
          var p = gt[i];

          if (p.mother > 0 && p.father > 0) {
            var mo = gt[p.mother];
            var fa = gt[p.father];

            var mo_x1 = calcX_in_px(mo.bornyear);
            var mo_y1 = gtree_gtline_top + mo.gtline * gtree_gtline;

            var fa_x1 = calcX_in_px(fa.bornyear);
            var fa_y1 = gtree_gtline_top + fa.gtline * gtree_gtline;

            var x1 = calcX_in_px(p.bornyear);
            var y1 = gtree_gtline_top + p.gtline * gtree_gtline;

            mo_x1 += gtree_card_width;
            mo_y1 += gtree_card_height / 2;
            fa_x1 += gtree_card_width;
            fa_y1 += gtree_card_height / 2;
            y1 += gtree_card_height / 2;

            var x2 = Math.max(mo_x1, fa_x1) + 20;
            var y2 = (fa_y1 + mo_y1) / 2;
            var x3 = x2 + 30;

            ctx.beginPath();
            ctx.moveTo(mo_x1, mo_y1);
            ctx.lineTo(x2, mo_y1);
            ctx.lineTo(x2, fa_y1);
            ctx.lineTo(fa_x1, fa_y1);
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(x2, y2);
            ctx.lineTo(x3, y2);
            ctx.lineTo(x3, y1);
            ctx.lineTo(x1, y1);
            ctx.stroke();
          }
        }

        // cards
        for (var i in gt) {
          var p = gt[i];
          // console.log(p);
          var x1 = calcX_in_px(p.bornyear);
          var y1 = gtree_gtline_top + p.gtline * gtree_gtline;
          gt[i].x1 = x1;
          gt[i].y1 = y1;

          // fill
          ctx.fillStyle = "white";
          ctx.fillRect(x1, y1, gtree_card_width, gtree_card_height);
          ctx.fillStyle = "black";

          ctx.strokeRect(x1, y1, gtree_card_width, gtree_card_height);
          var year_print = '' + p.bornyear;

          if (p.bornyear_notexactly == 'yes') {
            year_print += ' (пр.)';
          }

          var d = 16;
          ctx.fillText(year_print, x1 + 3, y1 + d);
          d += 16;
          ctx.fillText('' + p.firstname, x1 + 3, y1 + d);
          if (p.lastname) {
            d += 16;
            ctx.fillText('' + p.lastname, x1 + 3, y1 + d);
          }
        }
      }

      update_gtree();
    </script>
  </body>
</html>
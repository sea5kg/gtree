
function createRandomString(length) {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"; //
  let result = "";
  for (let i = 0; i < length; i++) {
    // Select a random character from the 'chars' string
    result += chars.charAt(Math.floor(Math.random() * chars.length)); //
  }
  return result;
}

function localize_captions(selected_lang) {
  document.title = LANG.main_title[selected_lang];
  $('#main_menu_item').text(LANG.main_title[selected_lang]);
  $('#enlarge_image_size').text(LANG.enlarge_image_size[selected_lang])
  $('#reduce_image_size').text(LANG.reduce_image_size[selected_lang])
  $('#download_tree').text(LANG.download[selected_lang])
}

function parsePageParams() {
    var loc = location.search.slice(1);
    var arr = loc.split("&");
    var result = {};
    var regex = new RegExp("(.*)=([^&#]*)");
    for(var i = 0; i < arr.length; i++){
        if(arr[i].trim() != ""){
            var p = regex.exec(arr[i].trim());
            // console.log("results: " + JSON.stringify(p));
            if(p == null)
                result[decodeURIComponent(arr[i].trim().replace(/\+/g, " "))] = '';
            else
                result[decodeURIComponent(p[1].replace(/\+/g, " "))] = decodeURIComponent(p[2].replace(/\+/g, " "));
        }
    }
    console.log(JSON.stringify(result));
    return result;
}


function getPageFromParams() {
  params = parsePageParams();
  var p = params["page"];
  if (p === undefined) {
    p = "tree1";
  }
  return p;
}

function calcX_in_px(year) {
  var ret = year - gtree_minyear;
  ret = ret * gtree_yearstep + gtree_padding;
  return ret;
}

function update_gtree(ctx) {

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



function load_tree2() {
  $.ajax({
    url: "./api/v1/tree", // The URL to which the request is sent
    type: "GET",            // The type of request (GET, POST, PUT, DELETE, etc.)
    success: function(result) { // Callback function if the request succeeds
      console.log(result)

      window.gtree_minyear = result['gtree_minyear'];
      window.gtree_maxyear = result['gtree_maxyear'];
      window.gtree_padding = result['gtree_padding'];
      window.gtree_yearstep = result['gtree_yearstep'];
      window.gtree_card_width = result['gtree_card_width'];
      window.gtree_card_height = result['gtree_card_height'];
      window.gtree_gtline = result['gtree_gtline'];
      window.gtree_gtline_top = result['gtree_gtline_top'];
      window.gtree_height = result['gtree_height'];
      window.gtree_width = result['gtree_width'];
      window.gt = result['gt'];
      for (var i in gt) {
        gt[i].highlight = false;
      }

      var canvas = document.getElementById("gtree");
      var ctx = canvas.getContext("2d");
      canvas.width  = gtree_width;
      canvas.height = gtree_height;
      canvas.style.width  = gtree_width + 'px';
      canvas.style.height = gtree_height + 'px';

      update_gtree(ctx);
    },
    error: function(jqXHR, textStatus, errorThrown) { // Callback function if the request fails
      console.log("Error: " + textStatus);
    }
  });
}

// on loaded document

document.addEventListener("DOMContentLoaded", function() {

  var page = getPageFromParams();

  $('.gtree-page-container').css("display", "none");
  $('#page_' + page).css("display", "block");

  $('#gtree_image').attr('src', 'public/tree.png?v1=' + createRandomString(10));

  $('#enlarge_image_size').unbind().bind('click', function(){
    $('.gtree-container img').css({'width' : ''});
    $('#reduce_image_size').show();
    $('#enlarge_image_size').hide();
  })
  $('#reduce_image_size').unbind().bind('click', function(){
    $('.gtree-container img').css({'width' : '100%'});
    $('#reduce_image_size').hide();
    $('#enlarge_image_size').show();
  })

  // localization
  localize_captions('ru');
  load_tree2();
});


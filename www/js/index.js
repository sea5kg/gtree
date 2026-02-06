
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
  window.selected_lang = selected_lang;
  document.title = LANG.main_title[selected_lang];
  $('#main_menu_item').text(LANG.main_title[selected_lang]);
  $('#enlarge_image_size').text(LANG.enlarge_image_size[selected_lang])
  $('#reduce_image_size').text(LANG.reduce_image_size[selected_lang])
  $('#download_tree').text(LANG.download[selected_lang])
}


function gtree_api(method, params) {
  var deferred = $.Deferred();
  headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
  };
  var session = localStorage.getItem("auth_session");
  var expired_at = localStorage.getItem("auth_expired_at");
  if (session !== null && expired_at !== null) {
    if (Date.now() / 1000 < expired_at) {
      headers["Authorization"] = session;
    } else {
      console.error("session is outdated");
    }
  }

  $.ajax({
    type: "POST",
    url: "./api/v1/cpp/",
    headers: headers,
    dataType: 'json',
    data: JSON.stringify({
      "jsonrpc": "2.0",
      "id": crypto.randomUUID(),
      "method": method,
      params: params
    }),
  })
  .done(function(data) {
    if (method == "doLogin") {
      localStorage.setItem("auth_expired_at", data["result"]["expired_at"]);
      localStorage.setItem("auth_session", data["result"]["session"]);
    }
    deferred.resolve(data["result"]);
  }).fail(function(jqXHR, textStatus, errorThrown) {
    console.error("Error:", textStatus);
    deferred.reject(textStatus);
  });
  return deferred.promise();
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
    // console.log(JSON.stringify(result));
    return result;
}

function changePageParams(newPageParams) {
    var params = [];
    // console.log("changeLocationState");
    // console.log("changeLocationState, newPageParams = ", newPageParams);
    for(var p in newPageParams){
        params.push(encodeURIComponent(p) + "=" + encodeURIComponent(newPageParams[p]));
    }
    // console.log("changeLocationState", params);
    // console.log("changeLocationState", window.location.pathname + '?' + params.join("&"));
    window.history.pushState(newPageParams, document.title, window.location.pathname + '?' + params.join("&"));
    // fhq.pageParams = fhq.parsePageParams();
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
      // console.log(result)

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


var prev_person_filter = "";
var person_filter_work = false;
function apply_persons_filter() {
  if (prev_person_filter == $('#persons_filter').val()) {
    // console.log("Filter already applyed");
    return;
  }

  if (person_filter_work === true) {
    // console.log("person_filter_work is true wait 500");
    setTimeout(apply_persons_filter, 500);
    return;
  }

  person_filter_work = true;
  var filter = $('#persons_filter').val().toLowerCase();

  var filtered = 0;
  if (filter === "") {
    $(".person-info").css("display", "block");
    filtered = window.persons.length;
  } else {
    for (var i in window.persons) {
      var person = window.persons[i];
      if (person["full_filter_text"].indexOf(filter) !== -1) {
        $('#' + person["uid"]).css("display", "block");
        // console.log(person);
        filtered++;
      } else {
        $('#' + person["uid"]).css("display", "none");
      }
    }
  }
  prev_person_filter = filter;

  if (filtered == window.persons.length) {
    $('#persons_filtered').css({"display": "none"});
  } else {
    $('#persons_filtered').text("Отфильтровано: " + filtered);
    $('#persons_filtered').css({"display": "block"});
  }

  var params = parsePageParams();
  params["persons_filter"] = $('#persons_filter').val();
  changePageParams(params);

  person_filter_work = false;
}

function load_persons() {
   $.ajax({
    url: "./api/v1/persons", // The URL to which the request is sent
    type: "GET",            // The type of request (GET, POST, PUT, DELETE, etc.)
    success: function(result) { // Callback function if the request succeeds
      // console.log(result)
      $('.persons-count').text(result.length);
      window.persons = result; // for filter
      for (var i in result) {
        var person = result[i];
        person["full_filter_text"] = "";
        person["full_filter_text"] += " " + person["title"];
        person["full_filter_text"] += " " + person["yearsoflife"];
        person["full_filter_text"] += " " + person["mother"];
        person["full_filter_text"] += " " + person["father"];
        var bio = person["biography"];
        if (bio !== "" && bio !== "hidden") {
          person["full_filter_text"] += " " + bio;
        }
        person["full_filter_text"] = person["full_filter_text"].toLowerCase();

        $('#page_persons').append(
          ""
          + "<div class=\"card person-info\" id=\"" + person["uid"] + "\">\n"
          + "    <div class=\"card-body\">\n"
          + "    <h5 class=\"card-title\">" + person["title"] + "</h5>\n"
          + "    <h6 class=\"card-subtitle mb-2 text-muted\">" + person["yearsoflife"] + "</h6>\n"
          + (person["mother"] !== "" ? "    <p class=\"card-text\">" + LANG.mother[window.selected_lang] + ": " + person["mother"] + "</p>\n" : "")
          + (person["father"] !== "" ? "    <p class=\"card-text\">" + LANG.father[window.selected_lang] + ": " + person["father"] + "</p>\n" : "")
          + (bio === "hidden" || bio === "" ? '<div class="alert alert-danger" role="alert">Данные скрыты</div>\n' : "<pre class=\"person-bio\">" + bio + "</pre>\n")
          + "    </div>\n"
          + "</div>\n\n"
        )
      }
      var params = parsePageParams();
      if (params["persons_filter"] !== undefined) {
        $('#persons_filter').val(params["persons_filter"]);
      }
      apply_persons_filter();
    },
    error: function(jqXHR, textStatus, errorThrown) { // Callback function if the request fails
      console.log("Error: " + textStatus);
    }
  });

  $('#persons_filter').on('input',function(e){
    // console.log("Changed", $('#persons_filter').val());
    apply_persons_filter();
  });
}

function init_modals() {
  const elm1 = document.getElementById('modal_sign_in');
  window.modal_sing_in = new bootstrap.Modal(elm1);
}

function signin_test() {
  gtree_api("doLogin", {
    "email": $('#signinFormLogin').val(),
    "pass": $('#signinFormPassword').val(),
  })
  .done(function(data) {
    // TODO process after success authorization
    console.log(data);
  }).fail(function(error) {
    console.error(error);
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

  load_persons();

  init_modals();
});


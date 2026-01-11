
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

// on loaded document

document.addEventListener("DOMContentLoaded", function() {
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
});


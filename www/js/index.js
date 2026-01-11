
function createRandomString(length) {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"; //
  let result = "";
  for (let i = 0; i < length; i++) {
    // Select a random character from the 'chars' string
    result += chars.charAt(Math.floor(Math.random() * chars.length)); //
  }
  return result;
}

// on loaded document

document.addEventListener("DOMContentLoaded", function() {
  $('#gtree_image').attr('src', 'public/tree.png?v1=' + createRandomString(10));

  $('#expand_gtree').unbind().bind('click', function(){
    $('.gtree-container img').css({'width' : ''});
    $('#collapse_gtree').show();
    $('#expand_gtree').hide();
  })
  $('#collapse_gtree').unbind().bind('click', function(){
    $('.gtree-container img').css({'width' : '100%'});
    $('#collapse_gtree').hide();
    $('#expand_gtree').show();
  })
});


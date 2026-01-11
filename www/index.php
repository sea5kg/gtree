<?php
      include_once("gtree.php"); 
      include_once("head.php");
?>
      <br>
      <div class="container">
        <a href="/public/tree.png" class="btn btn-primary" download="tree.png">Скачать</a>
        <div class="btn btn-primary" id="expand_gtree"> + Увеличить</div>
        <div class="btn btn-primary" style="display: none" id="collapse_gtree">- Уменьшить</div>
        <hr>
        <div class="gtree-container">
          <img style="width: 100%" src="public/tree.png?v1=<?php echo rand(100000,999999); ?>"/>
        </div>
        <hr>
      </div>
    <script>
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
    </script>
  </body>
</html>
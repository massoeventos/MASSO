<div class="modal fade " id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  
  $(document).ready(function(){

    $('.modal-image').addClass('on-cursor');
    $('body').on('click', '.modal-image', function(){
      at = $(this).attr('src');
      $('#ajaxModal .modal-body').html('<a href="'+at+'" target="_BLANK"><img class="img-responsive" src="'+at+'"></a>');
      $('#ajaxModal').modal();
    });

  });


</script>
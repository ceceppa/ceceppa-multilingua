jQuery(document).ready(function( $ ) {
  var $tag = $("#addtag").find("div")[0];
  var isEdit = $( $("#edittag").find("tr")[0] ).length > 0;
  var $titles = $(".cml-form-field");
  
  if(!isEdit) 
    $($tag).append($titles);
  else {
    $titles.each(function(index) {
      $tag = $("#edittag").find("table tbody tr")[index];
      $(this).insertAfter($tag);
    });
  }
});

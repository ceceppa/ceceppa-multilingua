/* Modifico la voce action della classe formsearch */
function isEmpty( inputStr ) { if ( null == inputStr || "" == inputStr ) { return true; } return false; }

jQuery(document).ready(function( $ ) {
  var input = $('<input type="hidden" name="lang" value="' + cml_search.lang + '" />');

  $form = $( cml_search.form_class );
  $form.append( input );
});

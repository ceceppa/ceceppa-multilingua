jQuery(document).ready( function($) {
  
  jQuery( '.cml-box-options form' ).submit( function() {
    jQuery.ajax( {
      type: 'POST',
      url: ajaxurl,
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function() {
        $p.find( '.spinner' ).show().animate( { opacity: 1 } );
        $p.find( '#more' ).fadeOut();
      },
      success: function( data ) {
        $lang = $form.parents( '.lang' );
        $lang.find( '.date-format' ).fadeIn();
        $lang.find( 'input[name="date-format"]' ).addClass( 'hidden' );
    
        $form.find( 'input[name^="flag_file"]' ).val( "" );

  });
});
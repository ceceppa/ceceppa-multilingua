jQuery(document).ready( function( $ ) {
  $( 'ul.cml-lang-js-sel ul > li > a' ).click( function() {
    $ul = $( this ).parents( '.cml-lang-js-sel' );
    $ul.find( "*" ).removeClass( 'item-hidden' );

    $a = $ul.find( '> li > a' );
    
    $( $ul.find( '> li > a' ) ).html( $( this ).html() );
    $( this ).parent().addClass( 'item-hidden' );
    
    if ( $a.attr( 'cml-lang' ) == "x" ) {
      $ul.find( "li.cml-lang-x" ).show();
    }
    
    $ul.find( 'input' ).val( $( this ).attr( 'cml-lang' ) );
    $ul.find( 'ul' ).css( 'visibility', 'hidden' );
  });
  
  $( 'ul.cml-lang-js-sel' ).mouseover( function() {
    $( this ).find( 'ul' ).removeAttr( 'style' );
  });
});

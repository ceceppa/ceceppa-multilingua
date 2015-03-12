$supports_html5_storage = ( 'sessionStorage' in window && window.sessionStorage !== null );

if( $supports_html5_storage ) {
  //Old lang?
  var old = sessionStorage.getItem( 'ceceppa_ml' );

  if( old != null ) {
    old = JSON.parse( old );
  }

  /* Can I clear sessionStorage if old language != new one? */
  if( ceceppa_ml.clear ) {

    if( old == null || old.id != ceceppa_ml.id ) {
      sessionStorage.clear();

      // console.log( "Clear" );
    }
  }

  sessionStorage.setItem( 'ceceppa_ml', JSON.stringify( ceceppa_ml ) );
}

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

    if( is_touch_device() ) {
        $( 'ul.cml-lang-sel > li > a' ).click( function() {
            $( 'ul.cml-lang-sel > li > ul' ).toggle();

            return false;
        });

        $( 'ul.cml-lang-sel > li > ul' ).hide();
    }
});

function is_touch_device() {
  return 'ontouchstart' in window // works on most browsers
      || 'onmsgesturechange' in window; // works on ie10
};

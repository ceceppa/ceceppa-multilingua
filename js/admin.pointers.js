jQuery(document).ready( function($) {
  if ( cmlPointer.valid != "" && cmlPointer.valid.pointers.length > 0 ) {
    wptuts_open_pointer( 0, true );
  }
});

function wptuts_open_pointer( i, valid ) {
  jQuery( '*' ).removeClass( 'cml-zoomed' );

  if( valid ) {
    pointer = cmlPointer.valid.pointers[i];

  } else {
    pointer = cmlPointer.dismissed.pointers[i];
  }
    $target = jQuery( pointer.target );
    
    if ( $target.length <= 0 ) {
      // jQuery( '.cml-box-shadow' ).fadeOut();
      
      return;
    }

    if ( ! $target.hasClass( "column-cml_flags" ) ) {
      // jQuery( '.cml-box-shadow' ).show();
      // jQuery( '.cml-box-shadow' ).offset( { left: 0, top: 0 } );
      // jQuery( '.cml-box-shadow' ).width( jQuery( document ).width() ).height( jQuery( document ).height() );
    }

    $target.addClass( 'cml-zoomed' );

    //.animate( { scale: 1.2 } );

    if ( valid ) {
      options = jQuery.extend( pointer.options, {
          close: function() {
              jQuery.post( ajaxurl, {
                  pointer: pointer.pointer_id,
                  action: 'dismiss-wp-pointer'
              });
  
              i++;
              if ( i < cmlPointer.valid.pointers.length )
                wptuts_open_pointer( i, true );
              else
                jQuery( '.cml-box-shadow' ).fadeOut();
          },
      });
    } else {
      options = jQuery.extend( pointer.options, {
          close: function() {
              i++;

              if ( i < cmlPointer.dismissed.pointers.length )
                wptuts_open_pointer( i, false );
              else
                jQuery( '.cml-box-shadow' ).fadeOut();
          }
      });
    }

    jQuery(pointer.target).pointer( options ).pointer('open');
}

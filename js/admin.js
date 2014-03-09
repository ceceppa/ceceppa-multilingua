jQuery(document).ready( function($) { 
  $( '.tipsy-me' ).tipsy( { html: true, fade: true } );
  $( '.tipsy-w' ).tipsy( { gravity: 'w', html: true, fade: true } );
  $( '.tipsy-e' ).tipsy( { gravity: 'e', html: true, fade: true } );
  $( '.tipsy-s' ).tipsy( { gravity: 's', html: true, fade: false, offset: 5 } );

  //Menu "captions"
  $( '.wp-submenu > li > a > .cml-separator' ).parents( 'li' ).addClass( 'cml-separator-li' );

  //jQuery( '.inside .cml-description' ).fadeOut();
  $( '.hndle .cml-help' ).click( function() {
    $( this ).toggleClass( 'active' );
    $( this ).parents( '.postbox ').find( '.inside .cml-description' ).toggleClass( 'active' );
  });
  
  //Form submit
  /*
   * Save settings via ajax
   */
  $( 'body' ).on( 'submit', 'form.cml-ajax-form', function() {
    $form = $( this );

    $form.find( '.cml-submit-button > .wpspinner > .spinner' ).fadeIn();
    $.ajax( {
      type: 'POST',
      url: ajaxurl,
      data: $( this ).serialize(),
      success: function( data ) {
        console.log( data );
        $form.find( '.cml-submit-button > .wpspinner > .spinner' ).fadeOut();

        $data = null;

        if ( data == "-1" ) {
          alert( 'Failed!!!' );
          return;
        }

        try {
          $data = $.parseJSON( data );
        } catch(e) {
          return;
        }
        
        if ( $data == null) return;
        
        if ( $data.url ) window.location = $data.url;
      }
    } );
    
    return false;
  } );
  
  /*
   * Widgets page
   */
  //Move "custom titles after "Widget title"
  //cml_move_widget_titles();

  //custom "checkbox" style doesn't work properly, label doesn't toggle checkbox state :(
  $( 'body' ).on( 'click', '.cml-js-checkbox > label, .cml-js-checkbox + label', function() {
    $( this ).parent().find( 'input' ).trigger( 'click' );
  });
  $( 'body' ).on( 'click', '.cml-filter-widget .cml-checkbox input', function() {
    $div = $( this ).parents( '.cml-widget-conditional' ).find( '.cml-widget-conditional-inner' );
    
    $div.toggleClass( 'cml-widget-conditional-hide' );
  });
  
  $( '.cml-dropdown-me > li > input' ).click( function() {
    $( this ).select();
    $( this ).parents( 'ul.cml-dropdown-me' ).find( 'ul' ).show();
    
    $( this ).parents( 'ul.cml-dropdown-me' ).find( 'ul > li' ).show();
  }).keyup( function() {
    $li = $( this ).parents( 'ul.cml-dropdown-me' ).find( 'ul li' );
    $val = $( this ).val();

    $li.each( function() {
      $span = $( this ).find( "span" );
      var display = $span.html().toLowerCase().indexOf( $val );

      $( this ).css( "display", ( display >= 0 ) ? "block" : "none" );
    });
  });
  
  $( '*' ).mouseup( function(e) {
    $input = $( '.cml-dropdown-me > li > input[type="text"]' ).each( function() {
      $( this ).val( $( this ).attr( "original" ) );
    
      $( this ).parents( '.cml-dropdown-me' ).find( '> li > ul' ).hide();
    });
  });

  $( '.cml-dropdown-me > li ul li' ).click( function() {
    $ul = $( this ).parents( 'ul.cml-dropdown-me' );
    $ul.find( 'input[type="text"]' ).val( $( this ).find( 'span' ).html() );
    
    $ul.find( 'input[type="text"]' ).attr( "original", $( this ).find( 'span' ).html() );
    $ul.find( '> li input[type="hidden"]' ).val( $( this ).attr( 'cml-trans' ) );

    $ul.find( 'ul' ).hide();
  });
  
  /*
   * quickedit selector
   */
  $( 'select.cml-quick-lang' ).on( 'change', function() {
    $div = $( this ).parents( '.inline-edit-col' );
    
    $div.find( 'label.cml-quick-item' ).addClass( 'cml-hidden' );
    
    if ( this.value > 0 ) {
      $div.find( 'label:not(.cml-quick-' + this.value  + ')' ).removeClass( 'cml-hidden' );
    }
  });
  
  $( '.cml-pointer-help' ).click( function() {
    if ( cmlPointer.dismissed.pointers.length > 0 ) {
      wptuts_open_pointer( 0, false );
    }
    
    $( this ).removeClass( 'active' );
  });
  
  $( 'iframe.cml-iframe' ).height( $( document ).height() - 100 );
});


function cml_move_widget_titles( ) {
  jQuery( '.widget-content' ).each(function() {
    jQuery( this ).find( 'p:first-child' ).after( jQuery( this ).find( '.cml-widget-titles' ) );
  });
}

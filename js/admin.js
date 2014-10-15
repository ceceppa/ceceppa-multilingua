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
    $li = $( this ).parents( 'ul.cml-dropdown-me' ).find( 'ul > li' );
    $val = $( this ).val();

    $li.each( function( $i ) {
      $span = $( this ).find( ".title" );
      if( $span.length > 0 ) {
        var display = $span.html().toLowerCase().indexOf( $val );

        $( this ).css( "display", ( display >= 0 ) ? "block" : "none" );
      }
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

    title = $( this ).hasClass( 'no-hide' ) ? "" : $( this ).find( 'span.title' ).html();
    $ul.find( 'input[type="text"]' ).val( title );
    
    $ul.find( 'input[type="text"]' ).attr( "original", title );
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

  //Post tags
  if( $( '#ceceppaml-tags-meta-box input[name="search"]' ).length > 0 ) {
    $( '#ceceppaml-tags-meta-box input[name="search"]' ).autocomplete( {
      source: $.parseJSON( ceceppaml_admin.tags ), minLength : 0,
      select: function( event, ui ) {
        $clone = $( '#ceceppaml-tags-meta-box .cml-tagslist li.cml-first' ).clone();
        $clone.removeClass( 'cml-hidden cml-first' );
        $clone.find( '.title' ).html( ui.item.label.toLowerCase() );
  
        $clone.find( 'input.field' ).val( ui.item.id );
        $clone.find( 'input.cml-input' ).val( ui.item.label );
  
        $( '#ceceppaml-tags-meta-box .cml-tagslist' ).append( $clone );
        $clone.focus();
        $clone.find( '.tipsy-s' ).tipsy( { gravity: 's', html: true, fade: false, offset: 5 } );
  
        setTimeout( function() {
          $( '#ceceppaml-tags-meta-box input[name="search"]' ).val( "" );
          $( '#ceceppaml-tags-meta-box input[name="search"]' ).trigger( 'focus' );
        }, 100);
      }
    }).on('focus', function(event) {
      $(this).autocomplete("search", "");
    });
  }

  $( 'body' ).on( 'click', '#ceceppaml-tags-meta-box .ntdelbutton', function() {
    $( this ).parents( 'li' ).remove();
  });

  $( 'body' ).on( 'click', '#ceceppaml-tags-meta-box ul li span.title', function() {
    $li = $( this ).parents( 'li' );

    $li.find( '.title' ).hide();
    $li.find( '.cml-input' ).removeClass( 'cml-hidden' );
    $li.find( '.cml-input' ).select();
    $li.find( '.button-confirm' ).show();
    $li.find( '.button-add' ).hide();
  });  

  $( 'body' ).on( 'click', '#ceceppaml-tags-meta-box .button-confirm', function() {
    $li = $( this ).parents( 'li' );

    $li.find( '.title' ).html( $li.find( '.cml-input' ).val() );
    $li.find( '.title' ).show();
    $li.find( '.cml-input' ).addClass( 'cml-hidden' );
    $li.find( '.button-confirm' ).hide();
    $li.find( '.button-add' ).show();
  });

  $( '.cml-titlewrap' ).insertAfter( $( '#titlediv > #titlewrap' ) );
  $( '.cml-titlewrap' ).removeClass( 'cml-hidden' );

  //Hide label if value is not empty
  $( '.cml-title' ).each( function() {
    if( $( this ).val() != "" ) {
      $( this ).prev().fadeOut( 0 );
    }
  });

  $( '.cml-title' ).focus( function() {
    $( this ).prev().fadeOut( 'fast' );
  });

  $( '.cml-titlewrap input' ).focusout( function() {
    $this = $( this );

    if( $this.val() != "" ) return;

    $this.prev().fadeIn( 'fast' );
  });
  
  $( 'form#post table.compat-attachment-fields tr[class*="compat-field-cml-media-title"]' ).remove();
});


function cml_move_widget_titles( ) {
  jQuery( '.widget-content' ).each(function() {
    jQuery( this ).find( 'p:first-child' ).after( jQuery( this ).find( '.cml-widget-titles' ) );
  });
}

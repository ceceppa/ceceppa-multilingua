var cml_not_saved = false;
var cml_items_to_save = 0;

jQuery(document).ready( function($) { 
  /*
   * Languages
   */
  jQuery( "#cml-languages" ).sortable( { handle: ".handle",
                                      stop: function() {
                                        cml_admin_need_to_save();
                                      }
                                      } );

  //Combo "Settings"
  jQuery( 'ul.cml-combo' ).mouseenter( function() {
    $advanced = jQuery( '#cml-box-languages .advanced' );
    
    $advanced.data( 'prev-class', $advanced.attr( 'class' ) );
    
    $p = jQuery( '#cml-languages p.submit' );
    $p.data( 'prev-class', $p.attr( 'class' ) );
  }).mouseleave( function() {
    $advanced = jQuery( '#cml-box-languages .advanced' );
    
    //Restore old classes when no click
    $advanced.attr( 'class', $advanced.data( 'prev-class' ) );
    
    $p = jQuery( '#cml-languages p.submit' );
    $p.attr( 'class', $p.data( 'prev-class' ) );
    $p.find( "#more" ).val( ceceppaml_admin.moremsg );
  });

  //Change advance settings mode
  jQuery( 'ul.cml-combo-items > li' ).mouseenter( function() {
    $advanced = jQuery( '#cml-box-languages .advanced, #cml-languages p.submit' );

    $advanced.removeClass( 'show-basic show-intermediate show-advanced' );
    $advanced.addClass( jQuery( this ).attr( 'id' ) );
  }).click( function() {
    $advanced = jQuery( '#cml-languages .advanced' );
    var id = jQuery( this ).attr( 'id' );

    $prev = jQuery( this ).parent().prev().html( jQuery( this ).html() );
    $advanced.data( 'prev-class', 'advanced ' + id );

    jQuery( '#cml-languages p.submit' ).data( 'prev-class', 'submit submitbox ' + id );

    //Save advanced mode
    $spinner = jQuery( this ).parents( '.hndle' ).find( '.spinner' );
    jQuery.ajax( {
      type: 'POST',
      url: ajaxurl,
      data: { action: 'ceceppaml_advanced_mode', mode: id, security: ceceppaml_admin.secret },
      beforeSend: function( x ) {
        $spinner.animate( { opacity: 1 } );
      },
      success: function( data ) {
        $spinner.animate( { opacity: 0 } );
      }
    });
  });

  //Remove language
  jQuery( 'body' ).on( 'click', '#cml-languages .lang .remove', function() {
    jQuery( '.tipsy' ).fadeOut();

    $lang = jQuery( this ).parents( '.lang' );

    //Custom box language?
    if ( $lang.parent().hasClass( 'cml-custom-languages' ) ) {
      $shadow.fadeOut();
      $lang.parent().fadeOut();
      jQuery( '.cml-custom-message' ).fadeOut();

      return;
    }

    //If is new item and it's not already "saved", I can delete it :)
    if ( $lang.find( 'input[name="new"]').val() == "1" ) {
      $lang.transition( { scale: 0 }, 'fast', function() {
        jQuery( this ).remove();
      });
      return;
    }

    $lang.toggleClass( 'removed' );
    jQuery( this ).html( $lang.hasClass( 'removed' ) ? ceceppaml_admin.restoremsg : ceceppaml_admin.deletemsg );

    $lang.find( 'form input[name="remove"]' ).val( $lang.hasClass( 'removed' ) ? 1 : 0 );

    cml_admin_need_to_save();
  });
  
  //More details
  jQuery( 'body' ).on( 'click', '#cml-languages .lang p.submit > #more', function() {
    $lang = jQuery( this ).parents( '.lang' );
    if ( $lang.parent().hasClass( 'cml-custom-languages' ) ) return;

    $advanced = $lang.find( '.advanced' );
    $advanced.toggleClass( 'show-advanced' );
    
    var msg = $advanced.hasClass( 'show-advanced' ) ? ceceppaml_admin.lessmsg  : ceceppaml_admin.moremsg;
    jQuery( this ).val( msg );
  });

  //Enable language
  jQuery( 'body' ).on( 'click', '#cml-box-languages .lang .enabled', function() {
    $this = jQuery( this );
    $this.toggleClass( 'active' );
    $this.parents( ".lang" ).toggleClass( 'active' );

    $this.parents( '.lang' ).find( 'form input[name="enabled"]' ).val( $this.hasClass( 'active' ) ? 1  : 0 );

    cml_language_attention( $this.parents( ".lang" ), 'active' );
  });
  
  //Change flag
  jQuery( 'body' ).on( 'click', '#cml-languages .lang .flag', function() {
    $this = jQuery( this );
    
    $parent = $this.parents( '.lang' );
    if ( $parent.hasClass( 'removed' ) ) return;

    $parent.find( '.flags' ).slideToggle();
  });
  
  //Change current flag
  jQuery( 'body' ).on( 'click', '#cml-languages .lang ul.flags > li', function() {
    if ( jQuery( this ).find( 'img' ).hasClass( 'image-upload' ) ) {
      jQuery( this ).parents( '.lang' ).find( '.upload-field #flag-file' ).trigger( 'click' );
    }

    cml_admin_change_get_flag( jQuery( this ).parents( '.lang' ), jQuery( this ) );
  });

  //Default language
  jQuery( 'body' ).on( 'click', '#cml-box-languages .lang .default', function() {
    jQuery( '#cml-box-languages .lang .default' ).not( this ).removeClass( 'active' );
    
    $this = jQuery( this );
    $this.toggleClass( 'active' );
    
    jQuery( '#cml-box-languages .lang input[name="default"]' ).val( 0 );
    $this.parents( '.lang' ).find( 'form input[name="default"]' ).val( $this.hasClass( 'active' ) ? 1 : 0 );

    //if ( $this.parents( '#cml-box-languages').length <= 0 ) return;
    cml_language_attention( $this, 'active', 1.5 );
  });
  
  //Edit language name
  jQuery( 'body' ).on( 'keyup keypress blur change', '#cml-box-languages .lang input', function() {
    jQuery( this ).addClass( "modified" );
    
    cml_admin_need_to_save();
  });
  
  //Edit date format
  jQuery( 'body' ).on( 'click', '#cml-languages .lang span.date-format', function() {
    jQuery( this ).fadeOut();
    jQuery( this ).next().removeClass( 'hidden' );
  });
  
  //Add new language
  jQuery( '#cml-box-available-languages #cml-languages .lang > .title > .name' ).click( function() {
    if ( jQuery( this ).parents( '.lang' ).parent().hasClass( 'cml-custom-languages' ) ) return;

    $lang = $( this ).parents( '.lang' );

    $lang.find( 'ul.flags' ).slideToggle();
  });
  
  //Change the icon of interested element
  jQuery( 'body' ).on( 'click', '#cml-box-available-languages .lang ul.flags > li', function() {
    if ( jQuery( this ).parents( '.lang').parent().hasClass( 'cml-custom-languages' ) ) return;

    cml_admin_clone_item( jQuery( this ).parents( ".lang" ) );

    cml_admin_change_get_flag( $clone.find( '.flag' ), jQuery( this ) );
  });
  
  //Save changes of current language
  jQuery( 'body' ).on( 'click', '#cml-languages p.submit input#submit', function() {
    jQuery( this ).parents( '.lang' ).find( 'form' ).submit();
  });
  
  //Submit
  jQuery( 'body' ).on( 'submit', '#cml-languages form#form', function() {
    $p = jQuery( this ).parents( '.lang' ).find( 'p.submit' );

    //Total ajax requests
    var requests = $( '#cml-box-languages #cml-languages .lang' ).length;

    //Form
    var $form = jQuery( this );
    $form.find( '#pos' ).val( jQuery( this ).parents( '.lang' ).index() );

    /*
     * Fields cannot be empty
     */
    if( ! cml_admin_check_form( $form ) ) {
      //Force advanced mode
      $form.parents( ".lang" ).find( "p.submit, div.advanced" ).addClass( "show-advanced" );
  
      cml_admin_need_to_save();
  
      return false;
    };
    

    $form.find( 'input[name="pos"]' ).each( function() {
      $this = jQuery( this );
      
      $this.val( $this.parents( '.lang' ).index() );
    });

    try 
    {
      var formData = new FormData();

      formData.append( 'action', 'ceceppaml_save_item' );
      formData.append( 'security', ceceppaml_admin.secret );
      formData.append( 'flag', $form.find( 'input[name^="flag_file"]' )[ 0 ].files[ 0 ] );
      formData.append( "data", $form.serialize() );
    } catch( e ) {
      //IE < 10+ doesn't support FormData() -.-"
      var formData = {
        action: 'ceceppaml_save_item',
        security: ceceppaml_admin.secret,
        data: $form.serialize()
      }

      $p.find( '.spinner' ).show().animate( { opacity: 1 } );
      $p.find( '#more' ).fadeOut();

      $.post( ajaxurl, formData, function( response ) {
      });

      $( document ).ajaxComplete(function() {
        requests--;

        if( requests <= 0 ) {
          window.location.reload();
        }
      });      

      return false;
    }

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

        if ( data === -1 ) {
          return;
        } else {
          cml_items_to_save--;

          $lang = $form.parents( '.lang' );
          if ( data === "" ) {
            //removed
            $lang.transition( { scale: 0 }, 'fast', function() {
              jQuery( this ).remove();
            });
          } else {
            $lang.transition( { scale: 0.8 }, 300, function() {
              $html = jQuery( data );
              $html.find( '.tipsy-me' ).tipsy( { html: true, fade: true } );
              $html.find( '.tipsy-w' ).tipsy( { gravity: 'w', html: true, fade: true } );
              $html.find( '.tipsy-e' ).tipsy( { gravity: 'e', html: true, fade: true } );
              $html.find( '.tipsy-s' ).tipsy( { gravity: 's', html: true, fade: false, offset: 5 } );

              $html.transition( { scale: 0.7 }, 0 );

              $form.parents( '.lang' ).replaceWith( $html );
              
              $html.transition( { scale: 1 }, 'fast' );
            });
          }

          if( cml_items_to_save <= 0 ) {
              //No more save required
              jQuery( '#cml-box-languages input[name="save-all"]' ).removeClass( 'save-required' );
          }
        }
      }
    });
    
    return false;
  });
  
  //Search autocomplete
  jQuery( 'input[name="search"]' ).autocomplete( {
    source: jQuery.parseJSON( ceceppaml_admin.languages ), minLength : 0,
    select: function( event, ui ) {
      $li = jQuery( '#cml-box-available-languages li[cml-language="' + ui.item.value.toLowerCase() + '"]' );
      $li.first().trigger( 'click' );

      $li.parent().fadeOut();

      jQuery( 'input[name="search"]' ).select();
    }
  }).on('focus', function(event) {
    jQuery(this).autocomplete("search", "");

    if ( jQuery( this ).val() == "" ) {
      jQuery( '#cml-box-available-languages li.lang' ).show();
    }
  });
  
  //Add custom language
  jQuery( 'input[name="add-custom"]' ).click( function() {
    $div = jQuery( '.cml-custom-languages' );

    $shadow = jQuery( '.cml-box-shadow' );
    $shadow.fadeIn();
    $shadow.offset( { top: 0, left: 0 } );
    $shadow.width( jQuery( 'body' ).width() );

    $div.fadeIn();
    
    //Message
    jQuery( '.cml-custom-message' ).fadeIn();

    $div.find( 'input#more' ).click( function() {
      if( ! cml_admin_check_form( $div.find( 'form' ) ) ) return;

      $lang = $div.find( '.lang' );

      cml_admin_clone_item( $lang );

      //Clear all fields.. :)
      $lang.find( 'form input[type="text"]' ).val( '' );
      $lang.find( 'form input#lang-name' ).val( ceceppaml_admin.custommsg );
      $lang.find( 'form input[name="date-format"]' ).val( ceceppaml_admin.dateformat );

      $div.fadeOut();
      jQuery( '.cml-box-shadow' ).fadeOut();
      jQuery( '.cml-custom-message' ).fadeOut();
    });
  });

  //Hide
  jQuery( '#cml-box-available-languages input#search' ).keyup( function() {
    $this = jQuery( this );
    if ( $this.val() == "" ) {
      jQuery( '#cml-box-available-languages li.lang' ).show();
      return;
    }

    jQuery( '#cml-box-available-languages li.lang' ).hide();
    jQuery( '#cml-box-available-languages li[cml-language*="' + $this.val().toLowerCase() + '"]' ).parents( '.lang' ).show();
  }).on('click', function() {
    $this = jQuery( this );

    if ( $this.val() == "" ) {
      jQuery( "#cml-box-available-languages li" ).parents( '.lang' ).show();
    }
  });

});

function cml_admin_change_get_flag( $parent, $this ) {
  //Change image
  $parent.find( 'img.active-flag' ).attr( 'src', $this.find( 'img' ).attr( 'src' ) );

  $this.parent().find( "*" ).removeClass( 'active' );
  $this.addClass( 'active' );

  //Change locale
  var locale = $this.attr( "cml-locale" );
  $lang = $this.parents( '.lang' );

  //Custom flag?
  $lang.find( 'input[name="custom_flag"]' ).val( $this.attr( "cml-custom" ) );

  if ( locale != undefined ) {
    $lang.find( 'input[name="wp-locale"]' ).val( locale );
    $lang.find( 'form' ).find( 'input[name="flag"]' ).val( locale );
  }
  
  if ( $this.parents( '#cml-box-languages' ) ) cml_admin_need_to_save();
  
  if ( ! $this.parents( '.advanced' ).hasClass( 'show-advanced' ) ) {
    $this.parents( '.flags' ).slideUp();
  }
}

function cml_language_attention( $e, className, factor ) {
  if ( factor == undefined) factor = 1;

  var scale = 1.1 * factor;
  if ( ! $e.hasClass( className ) ) {
    scale = 0.9 / factor;
  }

  $e.transition({ scale: scale }, 'fast', function() {
    $e.transition( { scale: 1.0 } );
  });
  
  cml_admin_need_to_save();
}

/*
 * Save all the items
 */
function cml_save_all_items() {
  var $this = jQuery( '#cml-box-languages input[name="save-all"]' );

  /*
   * I need to check if all items are saved before remove the class 'save-required'
   */
  cml_items_to_save = jQuery( '#cml-box-languages #cml-languages > li' ).length;

  $langs = jQuery( "#cml-box-languages.postbox .lang" ).each( function( index ) {
    $this = jQuery( this );

    $this.find( 'p.submit input#submit').trigger( 'click' );
  });
}

function cml_admin_clone_item( $this ) {
  if ( $this.hasClass( 'disabled' ) ) return;

  $clone = $this.clone();
  //if ( $this.parents( '.cml-custom-languages' ).length > 0 ) {
  $clone.parents( '.lang' ).find( 'div.advanced' ).addClass( 'show-advanced' );
  $clone.find( "input#more" ).hide();
  
  $clone.appendTo( '#cml-box-languages #cml-languages' ).fadeOut(0).transition( { scale: 0 }, 0 );
  $clone.fadeIn().transition( { scale: 1 }, 'fast', 'easeOutBack' );

  $clone.find( 'input[name="new"]' ).val( 1 );
  $clone.find( 'ul.flags' ).css( 'height', 'auto' ).css( 'display', '' );
  $clone.find( 'div.advanced' ).addClass( jQuery( '#cml-box-languages .advanced' ).attr( 'class' ) );

  $clone.find( '.tipsy-me' ).tipsy( { html: true, fade: true } );
  $clone.find( '.tipsy-w' ).tipsy( { gravity: 'w', html: true, fade: true } );
  $clone.find( '.tipsy-e' ).tipsy( { gravity: 'e', html: true, fade: true } );
  $clone.find( '.tipsy-s' ).tipsy( { gravity: 's', html: true, fade: false, offset: 5 } );
  
  cml_admin_need_to_save();
}

function cml_admin_need_to_save() {
  jQuery( '#cml-box-languages input[name="save-all"]' ).addClass( 'save-required' );
}

window.onbeforeunload = function() {
  //Check if same "Save" button is visible
  $buttons = jQuery( 'input[name="save-all"]' );
  if( $buttons.hasClass( "save-required" ) ) {
    return ceceppaml_admin.unloadmsg;
  }
};


/*
 * check that fields in "Languages" page aren't empty
 */
function cml_admin_check_form( $form ) {
  $form.parents( '.lang' ).removeClass( 'cml-error' );
  $form.find( '.cml-error' ).removeClass( 'cml-error' );

  //check all fields
  $form.find( 'input[type="text"]' ).each( function() {
    $this = jQuery( this );
    
    if ( $this.val() === "" || $this.val().length === 0 || $this.val() == 0 ) {
      $this.addClass( "cml-error" );
      
      //Add error class also to label
      $this.parents( "dl" ).find( "dd.label-" + $this.attr( "name" ) ).addClass( "cml-error" );
    } else {
      $this.removeClass( "cml-error" );

      $this.parents( "dl" ).find( "label-" + $this.attr( "name" ) ).removeClass( "cml-error" );
    }
  });
  
  /*
   * If same field has "error" class, exit...
   */
  if ( $form.find( ".cml-error" ).length > 0 ) {
    $form.parents( ".lang" ).addClass( "cml-error" );
    
    return false;
  }

  return true;
}

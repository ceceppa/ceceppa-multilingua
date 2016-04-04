var _cml_searchTimeout = 0;
var _cml_use_qem = false;
var _cml_qed_loadajax;

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

    //Hide all submit buttons
    $( 'input[type="submit"]' ).animate( { opacity: 0 }, 'slow', function() { $( this ).attr( 'disabled', 'disabled' ) } );
    $form.find( '.cml-submit-button > .wpspinner > .spinner' ).fadeIn();

    var data = $form.serialize();
    var processData = true;
    var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    if( $form.data( 'use-formdata' ) !== undefined ) {
      var formData = new FormData();

      formData.append( 'action', $form.find( 'input[name="action"]' ).val() );
      formData.append( 'security', $form.find( '#ceceppaml-nonce' ).val() );
      formData.append( 'file', $form.find( 'input[type="file"]' )[ 0 ].files[ 0 ] );
      formData.append( "data", $form.serialize() );

      data = formData;
      processData = false;
      contentType = false;
    }

    $.ajax( {
      type: 'POST',
      url: ajaxurl,
      data: data,
      processData: processData,
      contentType: contentType,
      success: function( data ) {
        $form.find( '.cml-submit-button > .wpspinner > .spinner' ).fadeOut();
        $data = null;

        // console.log( data );
        if ( data == "-1" ) {
          alert( 'Failed!!!' );
          return;
        }

        try {
          $data = $.parseJSON( data );
        } catch(e) {
          return;
        }

        if ( $data === null) return;

        if ( $data.show ) {
            $( 'input[type="submit"]' ).animate( { opacity: 1 }, 'slow',
                                                function() { $( this ).removeAttr( 'disabled' ) } );
        }

        if ( $data.url ) window.location = $data.url;
      }
    } );

    return false;
  } );

  /*
   * Custom category permalink structure
   */
   //Move fields in permalink options
   $( '.cml_category_slug' ).insertAfter( $( 'input[name="category_base"]' ) );
   $( '.cml_category_slug' ).removeClass( 'cml-hidden' );

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
    clearTimeout( _cml_searchTimeout );

    var $s = $( this ).closest( '.no-traslation' ).find( '.spinner' );
    $s.fadeIn();

    //Remove current items
    var $ul = $( this ).parent().find( 'ul' );
    $ul.children().remove();

    //Exec ajax call
    var data = {
        'action': 'ceceppaml_get_posts',
        'post_type': ceceppaml_admin.post_type,
        'post_id': ceceppaml_admin.post_id,
        'security': ceceppaml_admin.secret,
        'lang_id': $( this ).data( 'lang' ),
        'search': $( this ).val()
    };

    _cml_searchTimeout = setTimeout( function( data ) {
      var $spinner = $s;
      var $u = $ul;

      $.post(ajaxurl, data, function(response) {
//          alert('Got this from the server: ' + response);
          $spinner.fadeOut();

          if( response !== "" ) {
            $ul.append( $( response ) );
          }
      });
    }, 1000, data );

//    $li = $( this ).parents( 'ul.cml-dropdown-me' ).find( 'ul > li' );
//    $val = $( this ).val();
//
//    $li.each( function( $i ) {
//      $span = $( this ).find( ".title" );
//      if( $span.length > 0 ) {
//        var display = $span.html().toLowerCase().indexOf( $val );
//
//        $( this ).css( "display", ( display >= 0 ) ? "block" : "none" );
//      }
//    });
  });

  $( '*' ).mouseup( function(e) {
    $input = $( '.cml-dropdown-me > li > input[type="text"]' ).each( function() {
      $( this ).val( $( this ).attr( "original" ) );

      $( this ).parents( '.cml-dropdown-me' ).find( '> li > ul' ).hide();
    });
  });

  $( 'input' ).on ( 'blur', function(e) {
    setTimeout( function() {
      $input = $( '.cml-dropdown-me > li > input[type="text"]' ).each( function() {
        $( this ).val( $( this ).attr( "original" ) );

        $( this ).parents( '.cml-dropdown-me' ).find( '> li > ul' ).hide();
      });
    }, 300 );
  });

  $( 'body' ).on( 'click', '.cml-dropdown-me > li ul li', function() {
    var $ul = $( this ).parents( 'ul.cml-dropdown-me' );
    var lang = $ul.find( 'input[type="text"]' ).data( 'lang' );

    title = $( this ).hasClass( 'no-hide' ) ? "" : $( this ).find( 'span.title' ).html();

    //Is quick edit mode enabled?
    if( title === "" && _cml_use_qem ) {
      //Inform the user that this action will clear the current content
      var c = confirm( 'Do you want to clear the content for the selected language?' );

      //Clean up the title and the content for the current language
      if( c ) {
        //Clear the title && the content
        $( '#title_' + lang ).val( '' );

        //tinyMCE.get return an object only if the
        var tiny = tinyMCE.get('ceceppaml_content_' + lang );
        if( tiny ) {
          tiny.setContent('');
        } else {
          $( 'textarea#ceceppaml_content_' + lang ).val( '' );
        }
      }
    } else {
      //Selected a different post?
      var old_id = $ul.find( 'input[name="linked_post[' + lang + ']"]').val();
      var id = $( this ).attr( 'cml-trans' );

      //Load the new content?
      if( old_id !== id ) {
        //Is the title empty?
        if( $( '#title_' + lang ).val() !== "" ) {
          //Inform the user that I'm going to replace the content with the new one...

          //Load the new content
          $ul.next( 'a' ).addClass( 'disabled' );
          $ul.next( 'a' ).next( '.spinner' ).fadeIn();

          cml_load_the_content( lang, id );
        }
      }
    }

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
    if( $( this ).val() !== "" ) {
      $( this ).prev().fadeOut( 0 );
    }
  });

  $( '.cml-title' ).focus( function() {
    $( this ).prev().fadeOut( 'fast' );
  });

  $( '.cml-titlewrap input' ).focusout( function() {
    $this = $( this );

    if( $this.val() !== "" ) return;

    $this.prev().fadeIn( 'fast' );
  });

  $( 'form#post table.compat-attachment-fields tr[class*="compat-field-cml-media-title"]' ).remove();

  //Override flags settings
  $( '.cml-override-flags.cml-override input' ).click( function() {
    $( '.cml-show-always' ).attr( 'class', 'cml-show-always' ).addClass( $( this ).val() ) ;
  });

  //Settings metabox
  /**
   * WP add a button, for accessibility reason, before the metabox "header".
   * This cause the checkbox option stopping to work, so need to remove the duplicated
   * <input> element.
   */

  $( '.handlediv.button-link .cml-checkbox' ).remove();
});

/**
 * Quick edit mode
 */
jQuery( document ).ready( function( $ ) {
  if( $( '.ceceppaml-titlewrap' ).length > 0 ) {
    _cml_use_qem = true;
  }

  //Quick edit advanced optiions
  $( 'input#cml-qem' ).change(function() {
    $( this ).closest( '.meta-box-sortables' ).find( '#minor-publishing > .inside' ).toggleClass( 'disabled' );
  });
  $( '.ceceppaml-titlewrap' ).insertAfter( $( '#titlediv > #titlewrap' ) );
	$( '.ceceppaml-titlewrap' ).removeClass( 'cml-hidden' );

	//Move switch tab inside ".wp-editor-tabs" div
	$to = $( '#postdivrich #wp-content-editor-tools .wp-editor-tabs' );

	//move my textarea above #postdivrich
	$( '.ceceppaml-editor-wrapper' ).insertAfter( $( '#postdivrich' ) );

  //Yoast?
  if( $( '#wpseo_meta' ) ) {
    $( '.ceceppaml-yoast-kw' ).insertAfter( $( '#yoast_wpseo_focuskw' ) ).removeClass( 'cml-hidden' );
    $( '.ceceppaml-yoast-title' ).insertAfter( $( '#yoast_wpseo_title' ) ).removeClass( 'cml-hidden' );
    $( '.ceceppaml-yoast-metadesc' ).insertAfter( $( '#yoast_wpseo_metadesc' ) ).removeClass( 'cml-hidden' );
  }
});

function cml_move_widget_titles( ) {
  jQuery( '.widget-content' ).each(function() {
    jQuery( this ).find( 'p:first-child' ).after( jQuery( this ).find( '.cml-widget-titles' ) );
  });
}

/**
 * Load the content of the selected post
 */
function cml_load_the_content( lang, post_id ) {
  //Stop previous request
  if( _cml_qed_loadajax ) {
    _cml_qed_loadajax.abort();
  }

  //Exec ajax call
  var data = {
      'action': 'ceceppaml_get_post_content',
      'post_type': ceceppaml_admin.post_type,
      'post_id': post_id,
      'security': ceceppaml_admin.secret,
      'lang_id': lang,
  };

  console.log( data );
  _cml_qed_loadajax = jQuery.post(ajaxurl, data, function(response) {
    console.log( response );
    jQuery( '#ceceppaml-meta-box .spinner' ).fadeOut();
    jQuery( '#ceceppaml-meta-box ul + a.button.disabled' ).removeClass( 'disabled' );

    if( response !== "" ) {
      try {
        json = jQuery.parseJSON( response );
      } catch(e) {
        alert( "Something goes wrong :(.\nPost loading failed" );
        console.log( e );
      }
      console.log( json );

      jQuery( '#title_' + json.lang ).val( json.title );
      tinyMCE.get('ceceppaml_content_' + json.lang ).setContent( json.content );
    } else {
      alert( "Something goes wrong :(.\nPost loading failed" );
    }
  });
}

var CML_EEM = {
	switchTo: function( index, type, is_post_lang ) {
		jQuery( '.ceceppaml-' + type + 'nav-tab > a' ).removeClass( 'nav-tab-active' );
		jQuery( '.ceceppaml-' + type + 'nav-tab > a#ceceppaml-' + type + 'editor-' + index ).addClass( 'nav-tab-active' );

		jQuery( '.ceceppaml-' + type + 'editor-wrapper' ).addClass( 'cml-hidden' );
		jQuery( '.ceceppaml-' + type + 'editor-' + index ).removeClass( 'cml-hidden' );

		//Show default editor
		if( is_post_lang ) {
			jQuery( '#ceceppaml-' + type + 'editor' ).addClass( 'cml-hidden' );

			if( type === "" ) {
				jQuery( '#postdivrich' ).removeClass( 'cml-hidden' );
			} else {
				jQuery( '#wp-excerpt-wrap' ).removeClass( 'cml-hidden' );
			}
		} else {
			//My editor
			if( type === "" ) {
				jQuery( '#postdivrich' ).addClass( 'cml-hidden' );
			} else {
				jQuery( '#wp-excerpt-wrap' ).addClass( 'cml-hidden' );
			}
			jQuery( '#ceceppaml-' + type + 'editor.ceceppaml-editor-' + index ).removeClass( 'cml-hidden' );
		}

		//Resize iframe
		jQuery( '#ceceppaml-editor iframe' ).height( '433' );
	}
};

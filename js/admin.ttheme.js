jQuery( document ).ready( function( $ ) {
  //Update info about untranslated rows
  if( $( '.ceceppaml-theme-translations' ).length > 0 ) updateInfo();

  if( $( 'input[name="error"]' ).length > 0 ) $( 'input[type="submit"]' ).hide();

  /*
   * file .mo is generated after "load_plugin_textdomain", so I send data via ajax
   * and when done refresh page :)
   */
  $( 'body' ).on( 'submit', '.ceceppa-form-translations', function() {
    $form = $( this );
    $form.find( '.spinner' ).fadeIn();

    $.ajax( {
      type: 'POST',
      url: ajaxurl,
      timeout: 60000,
      data: $( this ).serialize(),
      success: function( data ) {
        // console.log( "Data", data );
        $form.find( '.cml-submit-button > .wpspinner > .spinner' ).fadeOut();

        $data = null;

        if ( data == "-1" ) {
          $form.find( '.spinner' ).fadeOut();

          alert( 'Failed!!!' );
          return;
        }

        try {
          $data = $.parseJSON( data );
        } catch(e) {
          $form.find( '.spinner' ).fadeOut();

          alert( 'Failed!!!' );
          return;
        }

        if ( $data == null) return;

        if ( $data.url ) window.location = $data.url;
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert( "Something goes wrong :'(" );

        window.location.reload();
      }
    });

    return false;
  } );
  
  jQuery( 'body' ).on( 'change keyup keypress', '.search input.s', function() {
    $table = $( 'table.ceceppaml-theme-translations' );
    $val = $( this ).val().toLowerCase();

    $table.find( 'tr > td.item' ).each( function() {
      html = $( this ).html();

      var display = html.toLowerCase().indexOf( $val );
      display = ( display >= 0 ) ? "table-row" : "none"

      $( this ).parent().css( "display",  display );
    });
  });
});


function showStrings( id, what ) {
  if( what == undefined ) {
    what = ".row-domain";
  } else {
    what = ".string-" + what;
  }

  jQuery( 'h2.tab-strings a' ).removeClass( 'nav-tab-active' );
  jQuery( jQuery( 'h2.tab-strings a' ).get( id ) ).addClass( 'nav-tab-active' );

  jQuery( 'table.ceceppaml-theme-translations tbody tr' + what ).show();
  
  if( what != undefined || what != "" ) {
    jQuery( 'table.ceceppaml-theme-translations tbody tr' ).not( what ).hide();
  }
}

function updateInfo() {
  $a = jQuery( '.tab-strings a' );
  
  $a.first().find( 'span' ).html( " (" + jQuery( '.row-domain' ).length + ")" );
  jQuery( $a.get( 1 ) ).find( 'span' ).html( " (" + jQuery( '.string-to-translate' ).length + ")" );
  jQuery( $a.get( 2 ) ).find( 'span' ).html( " (" + jQuery( '.string-incomplete' ).length + ")" );
  $a.last().find( 'span' ).html( " (" + jQuery( '.string-translated' ).length + ")" );
}

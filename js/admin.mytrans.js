jQuery( document ).ready( function( $ ) {
  var itemsPerPage = 10;

  $( 'form.ceceppa-form-translations .column-remove img' ).click( function() {
    $( 'form.ceceppa-form-translations input[type="checkbox"]:visible' ).trigger( 'click' );
  });

  $( '.cml-subsubsub > .current' ).trigger( 'click' );

  //Search
  $( 'body' ).on( 'keyup', 'input#filter', function() {
    var $table = $( 'table.mytranslations' );
    var $val = $( this ).val().toLowerCase();

    $table.find( 'tr' ).removeClass( 'match' );
    $table.find( 'tr > td input[type="text"], tr > td input.original, tr > td span' ).each( function() {
      var html = $( this ).val() || $( this ).html();
      var $tr = $( this ).closest( 'tr' );

      var display = html.toLowerCase().indexOf( $val );

      if( display >= 0 ) $tr.addClass( 'match' );
      display = ( display >= 0 || $tr.hasClass( 'match' ) ) ? "table-row" : "none";

      $tr.css( "display",  display );
    });
  });

  //Disable the default tablenav links
  $( '.ceceppaml-metabox .tablenav-pages a' ).click( function( e ) {
    e.preventDefault();
    e.stopPropagation();

    //Get the page number
    var re = /paged=(\d+)/;
    var page = re.exec( $( this ).attr( 'href' ) );
    if( page === null || page.length <= 0 )
      page = 0;
    else
      page = page[1] - 1;

    var from = page * itemsPerPage;
    var to = page * itemsPerPage + itemsPerPage;
    // console.log( page, from, to );

    //Show all the items between $from and $to
    var $table = $( 'table.mytranslations tbody' );

    $table.find( 'tr' ).removeClass( 'match' ).addClass( 'hidden' );
    $table.find( 'tr' ).each( function( i ) {
      if( i >= from && i <= to ) {
        $( this ).removeClass( 'hidden' ).addClass( 'match' );
      }
    });

    var pages = $( '.paging-input' ).data( 'pages' );
    if( ! pages ) {
      pages = $( '.paging-input .total-pages' ).html();
      $( '.paging-input' ).data( 'pages', pages );
    }

    $( '.paging-input' ).html( ( page + 1 ) + ' / ' + pages );
    return false;
  });

  $( '.ceceppaml-metabox .tablenav-pages a' ).first().trigger( 'click' );
  $( '.ceceppaml-metabox .tablenav-pages a' ).removeClass( 'disabled' );
});

function showStrings( id, what ) {
  if( what === undefined ) {
    what = ".row-domain";
  } else {
    what = ".string-" + what;
  }

  jQuery( '.cml-subsubsub a' ).removeClass( 'current' );
  jQuery( jQuery( '.cml-subsubsub a' ).get( id ) ).addClass( 'current' );

  jQuery( 'form.ceceppa-form-translations table tbody tr' + what ).show();

  if( what !== undefined || what !== "" ) {
    jQuery( 'form.ceceppa-form-translations table tbody tr' ).not( what ).hide();
  }

  jQuery( 'form.ceceppa-form-translations input[name="tab"]' ).val( what.replace( '.string-', '' ) );
}


/**
 * add new row to table via jquery
 *
 * count, how many languages add?
 * lids, languages indexes
 */
function addRow( count, lid, default_id ) {
  $table = jQuery("form.ceceppa-form-translations table");
  $tr = jQuery("<tr>");

  //Stringa
  $tr.append( jQuery( '<td>' ) );
  $tr.append( jQuery( '<td>' ).html( '' ) );
  $td = jQuery("<td>");
    $hidden = jQuery("<input>").attr('type', 'hidden').attr('name', 'id[]');
    $type = jQuery("<input>").attr('type', 'hidden').attr('name', 'group[]').val( 'S' );
    $td.append($hidden).append( $type );

    $input = jQuery("<input>").attr('type', 'text').attr('name', 'string[]').css('width', '100%');
    $td.append($input);
    $tr.append($td);

  id = lid.split(',');
  row = $table.find("tr").length - 1;
  $td = jQuery("<td>");
  for(var i = 0; i < count; i++) {

    $div = jQuery( '<div>' );

    type = ( id[i] != default_id ) ? "text" : "hidden";
    $input = jQuery("<input>").attr('type', type ).attr('name', 'values[' + id[i] + '][]').css('width', '90%');

    $div.append( $input );
    $td.append( $div );

  }
  $tr.append($td);

  $table.append($tr);
}

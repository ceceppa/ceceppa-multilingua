function addRow(count, lid) {
  $table = jQuery("table.wp-ceceppaml");
  $tr = jQuery("<tr>");
  
  //Stringa
  $td = jQuery("<td>");
    $hidden = jQuery("<input>").attr('type', 'hidden').attr('name', 'id[]');
    $type = jQuery("<input>").attr('type', 'hidden').attr('name', 'types[]').val( 'S' );
    $td.append($hidden).append( $type );

    $input = jQuery("<input>").attr('type', 'text').attr('name', 'string[]').css('width', '100%');
    $td.append($input);
    $tr.append($td);

  id = lid.split(',');
  row = $table.find("tr").length - 1;
  for(var i = 0; i < count; i++) {
    $td = jQuery("<td>");

    $hidden = jQuery("<input>").attr('type', 'hidden').attr('name', 'lang_id[' + row + '][' + i + ']').attr('value', id[i]);
    $td.append($hidden);

    $input = jQuery("<input>").attr('type', 'text').attr('name', 'value[' + row + '][' + i + ']').css('width', '100%');
    $td.append($input);
    $tr.append($td);
  }

  $table.append($tr);
}

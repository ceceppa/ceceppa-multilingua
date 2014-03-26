<?php
/*
 * Generate file settings-gen.php
 */
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

//(re)generate settings.gen.php?
if( isset( $_GET[ 'cml-settings-updated' ] ) ) {
  cml_generate_settings_php();
}

/*
 * Generate file "settings.php"
 * for reduce calls to database :)
 * 
 */
function cml_generate_settings_php() {
  $_cml_setttings = & $GLOBALS[ '_cml_settings' ];

  update_option( "cml_use_settings_gen", false );
  if( empty( $_cml_setttings ) ) {
    return;
  }

  //reload languages
  CMLLanguage::reload();

  cml_generate_lang_columns();

  $row[] = "<?php";
  $row[] = "if ( ! defined( 'ABSPATH' ) ) die( \"Access denied\" );";
  $row[] = "//Genetared by Ceceppa Multilingua - " . date( "Y-m-d H:i" );
  foreach( $_cml_setttings as $key => $value ) {
    if( ! is_array( $value ) ) {
      $value = addslashes( $value );
      $val = is_numeric( $value ) ? $value : '"' . $value . '"';

      $row[] = '$_cml_settings[ "' . $key . '"] = ' . $val . ';';
    }
  }
  
  $row[] = "\n";
  foreach( $GLOBALS[ '_cml_language_columns' ] as $key => $value ) {
    $row[] = '$_cml_language_columns[' . $key . '] = "' . $value . '";';
  }

  $row[] = "\n";
  foreach( $GLOBALS[ '_cml_language_keys' ] as $key => $value ) {
    $row[] = '$_cml_language_keys["' . $key . '"] = "' . $value . '";';
  }

  $row[] = "?>";

  $ok = @file_put_contents( CML_UPLOAD_DIR . "settings.gen.php", join( "\n", $row ) );

  if( $ok ) update_option( "cml_use_settings_gen", 1 );
}

/*
 * generate array containing keys of lang
 */
function cml_generate_lang_columns() {
  $langs = CMLLanguage::get_all();

  $_cml_language_columns = array();
  $_conv = array();
  foreach( $langs as $lang ) {
    $key = "lang_" . $lang->id;

    $_cml_language_columns[ $lang->id ] = $key;
    $_conv[ $key ] = $lang->cml_language_slug;
  }

  $GLOBALS[ '_cml_language_columns' ] = $_cml_language_columns;
  $GLOBALS[ '_cml_language_keys' ] = $_conv;

  update_option( "cml_languages_ids", $_cml_language_columns );
  update_option( "cml_languages_ids_keys", $_conv );
}

/*
 * generate file cmltras.po from translations stored in database
 */
function cml_generate_mo_from_translations( $type = null, $echo = true ) {
  global $wpdb;
  
  require_once( CML_PLUGIN_PATH . 'Pgettext/Pgettext.php' );

  //update_option( "cml_get_translation_from_po_$type", false );
  update_option( "cml_get_translation_from_po", 0 );

  /*
   * I generate translation for each type for avoid that different translations
   * of same word cause wrong "return"
   */
  $langs = ( $type == "_X_" ) ? CMLLanguage::get_all() : CMLLanguage::get_no_default();
  foreach( $langs as $lang ) {
    //$filename = CML_PLUGIN_CACHE_PATH . "cmltrans-" . strtolower( $type ) . "-{$lang->cml_locale}.po";
    $filename = CML_PLUGIN_CACHE_PATH . "cmltrans-{$lang->cml_locale}.po";

    $fp = @fopen( $filename, 'w' );
    if( !$fp ) {
      if( ! $echo ) continue;

      echo '<div class="error"><p>';
      printf( __( 'Error writing file: %s', 'ceceppaml' ), $filename );
      echo '</p></div>';

      continue;
    }

    //.po header
    $h = file_get_contents( CML_PLUGIN_ADMIN_PATH . "header.po" );
    
    $user = wp_get_current_user();
    $h = str_replace( '%PROJECT%', "cml_translations", $h );
    $h = str_replace( '%AUTHOR%', $user->user_firstname . " " . $user->user_lastname, $h );
    $h = str_replace( '%EMAIL%', $user->user_email, $h );
    $h = str_replace( '%LOCALE%', $lang->cml_locale, $h );
    fwrite( $fp, $h . PHP_EOL );

    //$query = sprintf( "SELECT UNHEX(cml_text) as text, UNHEX(cml_translation) as translation FROM %s WHERE cml_lang_id = %d AND cml_type = '%s'",
                     //CECEPPA_ML_TRANSLATIONS, $lang->id, $type );
    $query = sprintf( "SELECT UNHEX(cml_text) as text, UNHEX(cml_translation) as translation FROM %s WHERE cml_lang_id = %d",
                     CECEPPA_ML_TRANSLATIONS, $lang->id );
    $rows = $wpdb->get_results( $query );

    foreach( $rows as $row ) {
      $o = 'msgid "' . addslashes( stripslashes( $row->text ) ) . '"' . PHP_EOL;
      $s = 'msgstr "' . addslashes( stripslashes( $row->translation ) ) . '"' . PHP_EOL . PHP_EOL;
    
      fwrite( $fp, $o );
      fwrite( $fp, $s );
    }

    fclose( $fp );
    
    //Convert .po in .mo
    try {
      //Tadaaaaa, file generato... genero il .mo
      Pgettext::msgfmt( $filename );
      
      //update_option( "cml_get_translation_from_po_$type", true );
      update_option( "cml_get_translation_from_po", 1 );
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }
}

/*
 * generate cml_flags.css
 */
function cml_generate_cml_flags_css() {
  CMLLanguage::reload();

  foreach( CMLLanguage::get_all() as $lang ) {
$tiny[] = <<< EOT
.cml-flag-tiny-$lang->cml_language_slug:before {
    content: url(../flags/tiny/{$lang->cml_flag}.png);
    width: 16px;
    height: 11px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 5px;
}
EOT;

$small[] = <<< EOT
.cml-flag-small-$lang->cml_language_slug:before {
    content: url(../flags/small/{$lang->cml_flag}.png);
    width: 32px;
    height: 33px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 5px;
}
EOT;
  }
  
  $css = join( "\n", $tiny ) . "\n" . join( "\n", $small );
  @file_put_contents( CML_PLUGIN_CACHE_PATH . "cml_flags.css", $css );
}
?>
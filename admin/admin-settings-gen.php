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
function cml_generate_settings_php( $filename = "",
                                    $_cml_settings = null,
                                    $var_name = '$_cml_settings',
                                    $flag = 0 ) {

  if( null == $_cml_settings ) {
    $_cml_settings = & $GLOBALS[ '_cml_settings' ];
  }

  if( empty( $filename ) ) {
      $filename = CML_UPLOAD_DIR . "settings.gen.php";
  }

  update_option( "cml_use_settings_gen", false );
  if( empty( $_cml_settings ) ) {
    return;
  }

  //reload languages
  CMLLanguage::reload();

  cml_generate_lang_columns();

  $row = array();
  if( $flag == FILE_APPEND ) {
    $row[] = "\n";
  }

  $row[] = "<?php";
  $row[] = "/**CML: SETTINGS**/";
  $row[] = "if ( ! defined( 'ABSPATH' ) ) die( \"Access denied\" );";
  $row[] = "//Genetared by Ceceppa Multilingua - " . date( "Y-m-d H:i" );
  foreach( $_cml_settings as $key => $value ) {
    if( ! is_array( $value ) ) {
      $value = addslashes( $value );
      $val = is_numeric( $value ) ? $value : '"' . $value . '"';

      $row[] = $var_name . '[ "' . $key . '"] = ' . $val . ';';
    }
  }

  if( $flag == 0 ) {
      $row[] = "\n";
      foreach( $GLOBALS[ '_cml_language_columns' ] as $key => $value ) {
        $row[] = '$_cml_language_columns[' . $key . '] = "' . $value . '";';
      }

      $row[] = "\n";
      foreach( $GLOBALS[ '_cml_language_keys' ] as $key => $value ) {
        $row[] = '$_cml_language_keys["' . $key . '"] = "' . $value . '";';
      }

/*      $row[] = "?>"; */
  }

  $ok = @file_put_contents( $filename, join( "\n", $row ), $flag );

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
function cml_generate_mo_from_translations( $type = null, $echo = false ) {
  global $wpdb;

  if( CMLUtils::_get( 'no_generate', false ) ) return;

  require_once( CML_PLUGIN_ADMIN_PATH . 'php-mo.php' );
  require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-taxonomies.php' );

  update_option( "cml_get_translation_from_po", 0 );

  /*
   * I generate translation for each type for avoid that different translations
   * of same word cause wrong "return"
   */
  $langs = CMLLanguage::get_all();
  if( ! is_dir( CML_PLUGIN_CACHE_PATH ) ) @mkdir( CML_PLUGIN_CACHE_PATH );

  //foreach( $langs as $lang ) {
    $filename = CML_PLUGIN_CACHE_PATH . "cmltrans-" . CMLLanguage::get_default_locale() . ".po";

    $fp = @fopen( $filename, 'w' );
    if( !$fp ) {
      if( ! $echo ) return;

      echo '<div class="error"><p>';
      printf( __( 'Error writing file: %s', 'ceceppaml' ), $filename );
      echo '</p></div>';

      return;
    }

    //.po header
    $h = file_get_contents( CML_PLUGIN_ADMIN_PATH . "header.po" );

    $user = wp_get_current_user();
    $h = str_replace( '%PROJECT%', "cml_translations", $h );
    $h = str_replace( '%AUTHOR%', $user->user_firstname . " " . $user->user_lastname, $h );
    $h = str_replace( '%EMAIL%', $user->user_email, $h );
    $h = str_replace( '%TIME%', date( "h:i", time() ), $h );
    $h = str_replace( '%DATE%', date( "Y-m-d", time() ), $h );
    //$h = str_replace( '%LOCALE%', $lang->cml_locale, $h );
    fwrite( $fp, $h . PHP_EOL );

    _cml_copy_taxonomies_to_translations();
    $query = sprintf( "SELECT cml_language_slug as slug, UNHEX(cml_text) as text, UNHEX(cml_translation) as translation FROM %s t1 INNER JOIN %s t2 ON t1.cml_lang_id = t2.id",
                     CECEPPA_ML_TRANSLATIONS, CECEPPA_ML_TABLE );

    $rows = $wpdb->get_results( $query );

    foreach( $rows as $row ) {
      if( empty( $row->translation ) ) continue;
      /*
       * Strings stored in .po and mo file start with language slug
       */
      $msgid = "_{$row->slug}_" . addslashes( sanitize_title( stripslashes( $row->text ) ) );
      $o = 'msgid "' . $msgid . '"' . PHP_EOL;
      $s = 'msgstr "' . addslashes( stripslashes( $row->translation ) ) . '"' . PHP_EOL . PHP_EOL;

      fwrite( $fp, $o );
      fwrite( $fp, $s );
    }

    //serialize post translations & override flags settings
    _cml_generate_translations( $fp );
    _cml_generate_override_flags_settings( $fp );

    //menu meta
    _cml_generate_menu_meta( $fp );

    fclose( $fp );

    //Convert .po in .mo
    try {
      //Tadaaaaa, file generato... genero il .mo
      phpmo_convert( $filename );

      //update_option( "cml_get_translation_from_po_$type", true );
      update_option( "cml_get_translation_from_po", 1 );
    } catch (Exception $e) {
      //return $e->getMessage();
    }
  //}
}

  /*
   * for reduce database queries I store posts relations
   * in .po serializing ::get_translations result
   */
function _cml_generate_translations( & $fp ) {
  global $wpdb;

  $query = "SELECT * FROM " . CECEPPA_ML_RELATIONS;
  $rows = $wpdb->get_results( $query, ARRAY_A );
  $t = null;
  foreach( $rows as $row ) {
    unset( $row[ 'id' ] );

    foreach( $row as $key => $value ) {
      if( $value > 0 && $t == null ) {
        $t = CMLPost::get_translations( $value, true );
      }

      if( $value > 0 ) {
        $msgid = "__cml_{$key}__{$value}";

        $o = 'msgid "' . $msgid . '"' . PHP_EOL;
        $s = 'msgstr "' . addslashes( serialize( $t ) ) . '"' . PHP_EOL . PHP_EOL;

        fwrite( $fp, $o );
        fwrite( $fp, $s );
      }
    }

    $t = null;
  }
}

  /*
   * for reduce database queries I store posts relations
   * in .po serializing ::get_translations result
   */
function _cml_generate_override_flags_settings( & $fp ) {
  global $wpdb;

  $query = sprintf( "SELECT post_id, meta_value FROM %s WHERE meta_key = '_cml_override_flags'",
                      $wpdb->postmeta );
  $rows = $wpdb->get_results( $query );
  foreach( $rows as $row ) {
    $msgid = "__cml_override_flags_{$row->post_id}";

    $o = 'msgid "' . $msgid . '"' . PHP_EOL;
    $s = 'msgstr "' . addslashes( $row->meta_value ) . '"' . PHP_EOL . PHP_EOL;

    fwrite( $fp, $o );
    fwrite( $fp, $s );
  }

}

function _cml_generate_menu_meta( & $fp ) {
  global $wpdb;

  $query = "SELECT * FROM  $wpdb->postmeta WHERE  `meta_key` LIKE  '_cml_menu_meta_%'";
  $rows = $wpdb->get_results( $query );

  foreach( $rows as $row ) {
    $msgid = "{$row->meta_key}_{$row->post_id}";

    $o = 'msgid "' . $msgid . '"' . PHP_EOL;
    $s = 'msgstr "' . addslashes( $row->meta_value ) . '"' . PHP_EOL . PHP_EOL;

    fwrite( $fp, $o );
    fwrite( $fp, $s );
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

  if( ! is_dir( CML_PLUGIN_CACHE_PATH ) ) mkdir( CML_PLUGIN_CACHE_PATH );

  $css = join( "\n", $tiny ) . "\n" . join( "\n", $small );
  @file_put_contents( CML_PLUGIN_CACHE_PATH . "cml_flags.css", $css );
}

<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

define( 'CECEPPAML_BACKUP_PATH', CML_UPLOAD_DIR . trailingslashit( 'backup' ) );

function _cml_backup_tables( $select = "*", $where = "" ) {
    global $wpdb;

    $data = "\n/*---------------------------------------------------------------".
          "\n  SQL DB BACKUP ".date("d.m.Y H:i")." ".
          "\n  ---------------------------------------------------------------*/\n";
    $link = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
    mysql_select_db( DB_NAME, $link );
    mysql_query( "SET NAMES `utf8` COLLATE `utf8_general_ci`" , $link ); // Unicode

    $tables = array( CECEPPA_ML_TABLE, CECEPPA_ML_CATS, CECEPPA_ML_POSTS, CECEPPA_ML_RELATIONS );

    if( ! empty( $where ) ) {
      $tables = array( $wpdb->options );
    }

  $fields = ( $select == "*" ) ? "" : "( $select )";
  foreach($tables as $table){
    $data.= "\n/*---------------------------------------------------------------".
            "\n  TABLE: `{$table}`".
            "\n  ---------------------------------------------------------------*/\n";

    if( empty( $where ) ) {
      $data.= "DROP TABLE IF EXISTS `{$table}`;\n";
      $res = mysql_query("SHOW CREATE TABLE `{$table}`", $link);

      //Table doesn't exists
      if( ! $res ) continue;
      $row = mysql_fetch_row($res);
      $data.= $row[1].";\n";
    }

    $query = "SELECT {$select} FROM `{$table}` {$where}";
    $result = mysql_query($query, $link);
//    $data .= $query;

    $num_rows = mysql_num_rows($result);

    if($num_rows>0){
      $vals = Array(); $z=0;
      for($i=0; $i<$num_rows; $i++){
        $items = mysql_fetch_row($result);
        $vals[$z]="(";
        for($j=0; $j<count($items); $j++){
          if (isset($items[$j])) { $vals[$z].= "'".mysql_real_escape_string( $items[$j], $link )."'"; } else { $vals[$z].= "NULL"; }
          if ($j<(count($items)-1)){ $vals[$z].= ","; }
        }
        $vals[$z].= ")"; $z++;

        if( ! empty( $fields ) ) {
          $data.= "DELETE FROM `{$table}` WHERE option_name = '" . $items[0] . "';\n";
        }
      }
      $data.= "INSERT INTO `{$table}` {$fields} VALUES ";
      $data .= "  ".implode(";\nINSERT INTO `{$table}` {$fields} VALUES ", $vals).";\n";
    }
  }

  // mysql_close( $link );
  return $data;
}

/*
 * Check if the backup folder exists, otherwise try to create it
 */
function _cml_check_backup_folder() {
    //Check if the backup folder exists
    if( ! is_dir( CECEPPAML_BACKUP_PATH ) ) {
        return mkdir( CECEPPAML_BACKUP_PATH, 0777 );
    }

    return true;
}

/*
 * Failed to create backup folder :(
 */
function _cml_backup_folder_failed( $show_hide = true ) {
    $title = __( 'Backup not available', 'ceceppaml' );
    $msg = sprintf( __( 'Failed to create the backup folder: %s', 'ceceppaml' ), CECEPPAML_BACKUP_PATH );

    $link = esc_url( add_query_arg( array( 'hide-backup-warning' => 1 ) ) );
    $close = __( 'Hide', 'ceceppaml' );

    $button = "";
    if( $show_hide ) {
$button = <<< BUTTON
    <p class="submit">
        <a class="button button-primary" style="float: right" href="$link">
            $close
        </a>
    </p>
BUTTON;
    }

echo <<< ERROR
    <div class="error cml-notice">
        <p>
            <span class="title">CML: $title</span>
            $msg

            $button
        </p>
    </div>
ERROR;
}

//Backup tables
function _cml_backup_do_tables( $what, $filename, $select = "*", $where = "" ) {
    $handle = fopen( $filename , 'w+' );
    if( $handle !== false ) {

        // get backup
        fwrite($handle, "/**CML: {$what}**/");

        $mybackup = _cml_backup_tables( $select, $where );

        fwrite($handle,$mybackup);
        fclose($handle);

        return 1;
    }

    return -1;
}

function _cml_backup_do_settings_extra( $filename ) {
    global $_cml_settings, $wpdb;

    /*
     * Extra settings
     * internal plugin settings used to show/hide notices,
     */
     $keys = array( "cml_modification_mode",
                   "cml_modification_mode_default",
                   "cml_erased",
                   "_cml_update_existings_posts",
                   "cml_show_wizard",
                   '_cml_wpml_config',
                   '_cml_scan_folders',
                   'cml_add_items_to',
                   'cml_add_slug_to_link',
                   'cml_float_css',
                   'cml_first_install',
                   'cml_get_translation_from_po',
                   'cml_is_first_time',
                   'cml_languages_ids',
                   'cml_languages_ids_keys',
                   '_cml_wpml_config_paths',

                   "cml_translated_fields_yoast",
                   "cml_translated_fields_aioseo",

                   '_cml_hide_filtering_notice',
                   'cml_hide_backup_warning' );

    $settings = array();
    foreach( $keys as $key ) {
        $settings[ $key ] = get_option( $key );
    }

    cml_generate_settings_php( $filename, $settings, '$_cml_extra', FILE_APPEND );

    return 1;
}

function _cml_backup_folder_not_exists() {
    _cml_wp_error_div( __( 'Backup failed', 'ceceppaml' ),
                    __( "Backup folder doesn't exists", 'ceceppaml' )
                  );
}

function _cml_backup_file_failed() {
    $file = esc_html( $_GET[ 'file' ] );

    _cml_wp_error_div( __( 'Backup failed', 'ceceppaml' ),
                    sprintf( __( "Failed to create backup file: <i>%s</i>", 'ceceppaml' ), $file )
                  );
}

function _cml_backup_done() {
    $files = $_GET[ 'file' ];

    $list = '<ul class="cml-ul-list">';

    foreach( $files as $file ) {
        $list .= "<li>$file</li>";
    }

    $list .= "</ul>";

    _cml_wp_updated_div( __( 'Backup succesfully created', 'ceceppaml' ),
                      $list
                    );
}

function _cml_download_backup() {
    $file = CECEPPAML_BACKUP_PATH;
    $remove = false;

    $downloadFilename = 'cmlsettings';
    if( isset( $_GET[ 'file' ] ) ) {
      $downloadFilename = basename( $_GET[ 'file' ] );
      $file .= $downloadFilename;
    } else {
      $file .= ".tmp";

      $remove = true;
    }

    header('Content-Type: text/plain');
    header("Content-transfer-encoding: base64");
    header("Content-disposition: attachment; filename=\"{$downloadFilename}\"");

    readfile( "{$file}1" );

    // error_log( $file );
    //remove temp file
    if( $remove ) {
      readfile( "{$file}2" );

      unlink( "{$file}1" );
      unlink( "{$file}2" );
    }

    die();
}

if( isset( $_GET[ 'hide-backup-warning' ] ) ) {
    update_option( 'cml_hide_backup_warning', 1 );
}

if( ! _cml_check_backup_folder() &&
    ! get_option( 'cml_hide_backup_warning', 0 ) ) {
    add_action( 'admin_notices', '_cml_backup_folder_failed' );
}

if( isset( $_GET[ 'download' ] ) ) {
    _cml_download_backup();
}

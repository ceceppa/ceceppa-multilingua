<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

function cml_install_create_tables() {
  global $wpdb, $_cml_settings;

  //required by dbDelta function
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

  /*
   * CECEPPA_ML_TABLE: Contains information about languages
   */
  $table_name = CECEPPA_ML_TABLE;
  $first_time = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name;
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    /**
    * Tabella contenente le lingue da gestire
    *
    *  cml_default     - indica se è la lingua predefinita
    *  cml_flag        - bandiera della lingua
    *  cml_language    - nome della nuova lingua
    *  cml_category_id - categoria base a cui è collegata la nuova lingua
    *  cml_category    - descrizione della categoria a cui è collegata la lingua
    */
    $sql = "CREATE TABLE $table_name (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cml_default INT(1),
    cml_flag VARCHAR(100),
    cml_language TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    cml_language_slug TEXT,
    cml_locale TEXT,
    cml_enabled INT,
    cml_sort_id INT,
    cml_custom_flag INT,
    cml_rtl INT,
    cml_date_format TEXT,
    PRIMARY KEY  id (id)
    ) ENGINE=InnoDB CHARACTER SET=utf8;";

    dbDelta($sql);
  }

  /**
   * Translations are stored in CECEPPA_ML_TRANS table.
   */
  $table_name = CECEPPA_ML_TRANSLATIONS;
  if( $GLOBALS[ 'cml_db_version' ] <= 9 ) {
    if($wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
      $sql = "ALTER TABLE " . CECEPPA_ML_TRANSLATIONS . " ADD COLUMN `cml_type` TEXT";
      $wpdb->query($sql);

      $wpdb->query( "UPDATE " . CECEPPA_ML_TRANSLATIONS . " SET cml_type = 'W' WHERE cml_type is null" );
    }
  } //endif;

  if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
    $query = "CREATE TABLE  $table_name (
              `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              cml_text TEXT NOT NULL ,
              `cml_lang_id` INT NOT NULL ,
              `cml_translation` TEXT,
              `cml_type` TEXT) ENGINE=InnoDB CHARACTER SET=utf8;";

    dbDelta($query);
  }

  /*
   * Category translations are stored in CECEPPA_ML_CATS.
   * This table is used when user choose to translate category url
   */
  $table_name = CECEPPA_ML_CATS;
  if( get_option( "cml_db_version", CECEPPA_DB_VERSION ) <= 14 ) {
    $wpdb->query("DROP TABLE $table_name");
  }

  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $query = "CREATE TABLE  $table_name (
              `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              cml_cat_id INT NOT NULL,
              cml_cat_name VARCHAR(1000) NOT NULL ,
              `cml_cat_lang_id` INT NOT NULL ,
              `cml_cat_translation` VARCHAR(1000),
              `cml_cat_translation_slug` VARCHAR(1000),
              `cml_taxonomy` VARCHAR( 1000 ),
              `cml_cat_description` LONGTEXT ) ENGINE=InnoDB CHARACTER SET=utf8;";

    dbDelta($query);
  }

  if( $first_time ) {
    update_option( "cml_db_version", CECEPPA_DB_VERSION );

    update_option( "cml_show_wizard", 1 );
  }

  update_option( "cml_is_first_time", $first_time );
}

function cml_install_first_time() {
  global $wpdb;

  update_option( "cml_need_update_posts", true );
  update_option( "cml_first_install", true );
  update_option( "cml_migration_done", 3 );

  /* Grab current language and add it to CECEPPA_ML_TABLE */
  $rtl = 0;
  $locale = ( defined ( 'WPLANG' ) ) ? WPLANG : get_locale();
  if( empty( $locale ) ) $locale = "en_US";

  //Search current WPLANG in my locales list :)
  $language = "";
  if( ! empty( $wplang ) ) {
    $language = _cml_first_install_search( $wplang );
  }

  //not found, look for locale
  if( empty( $language ) ) {
    $language = _cml_first_install_search( $locale );
  }

  if( empty( $language ) ) {
    $language = __( "Default language" );
  }

  if( is_array( $language ) ) {
    $rtl = $language[ 'rtl' ];
    $language = $language[ 'language' ];
  }

  //Insert the record into db
  $wpdb->insert( CECEPPA_ML_TABLE,
            array('cml_default' => 1,
              'cml_language' => $language,
              'cml_language_slug' => strtolower( substr( $locale, 0, 2 ) ),
              'cml_locale' => $locale,
              'cml_enabled' => 1,
              'cml_flag' => $locale,
              'cml_sort_id' => 0,
              'cml_custom_flag' => 0,
              'cml_rtl' => $rtl,
              'cml_date_format' => get_option( 'date_format' ) ),
            array( '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%s' ) );

  $table_name = CECEPPA_ML_RELATIONS;
  if($wpdb->get_var( "SHOW TABLES LIKE '$table_name'") != $table_name) {
    cml_migrate_create_table();
  }

  //For customize "Post notice" ( this post is also available... )
  $wpdb->insert( CECEPPA_ML_TRANSLATIONS,
                  array( "cml_text" => bin2hex( "_notice_post" ),
                         "cml_type" => "N",
                         "cml_lang_id" => 0 ),
                  array( "%s", "%s", "%d" ) );

  //For customize "Page notice"
  $wpdb->insert( CECEPPA_ML_TRANSLATIONS,
                  array( "cml_text" => bin2hex( "_notice_page" ),
                         "cml_type" => "N",
                         "cml_lang_id" => 0 ),
                  array( "%s", "%s", "%d" ) );

  update_option( '_cml_installed_language', $wpdb->get_var( "SELECT cml_language FROM " . CECEPPA_ML_TABLE ) );
  update_option( 'cml_taxonomies_updated', 1 );
}

function _cml_first_install_search( $locale ) {
  $_langs = & $GLOBALS[ 'cml_all_languages' ];

  $language = "";
  $rtl = 0;

  foreach( $GLOBALS[ 'cml_all_languages' ] as $lang ) {
    if( $lang[ 0 ] == $locale ) {
      $language = end( $lang );
    }

    if( $lang[ 1 ] == $locale ) {
      $language = end( $lang );
    }

    if( $lang[ 0 ] == $language || $lang[ 1 ] == $language ) {
      $rtl = isset( $lang[ 3 ] );
      break;
    }
  }

  if( empty( $language ) ) {
    foreach( $_langs as $key => $value ) {
      if( $key == $locale ) {
        $language = preg_replace( "/\(.*\)/", "", $value );

        break;
      }
    }
  }
  return array(
               "language" => $language,
               "rtl" => $rtl,
               );
}

function cml_do_install() {
  //Check if I have to create plugin tables
  cml_install_create_tables();

  //First time? I show wizard
  if( get_option( "cml_is_first_time" ) ) {
    cml_install_first_time();
  }

  //Backup file
  $backup_file = date( 'Ymd-His' );
  $db_backup = $backup_file . '.db';
  $settings_backup = $backup_file . '.settings';

  _cml_backup_do_tables( "DB", CECEPPAML_BACKUP_PATH . $db_backup );
  _cml_backup_do_tables( "SETTINGS", CECEPPAML_BACKUP_PATH . $settings_backup,
                              " option_name, option_value, autoload ",
                              " WHERE option_name LIKE 'cml_%' OR option_name LIKE '_cml_%' " );

  //Do fixes
  cml_do_update();

  //create upload folder
  @mkdir( CML_UPLOAD_DIR );

  //I need this to manage post relations
  cml_generate_lang_columns();

  //Copy category translation from "_cats" to "_relations"
  require_once ( CML_PLUGIN_ADMIN_PATH . "admin-taxonomies.php" );

  cml_generate_mo_from_translations( "_X_", false );

  //(Re)generate settings
  cml_generate_settings_php();

  //look for wpml-config.xml
  update_option( '_cml_scan_folders', 1 );
}

?>

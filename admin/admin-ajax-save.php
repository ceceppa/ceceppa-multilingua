<?php
/*
 * Save settings via Ajax
 */
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/***********************
 *
 * LANGUAGES
 *
************************/
  /*
   * Settings mode
   */
  function cml_admin_save_advanced_mode() {
    if( ! check_ajax_referer( "ceceppaml-nonce", "security" ) ) {
      echo -1;
    } else
      update_option( 'ceceppaml_admin_advanced_mode', $_POST[ 'mode' ] );
    die();
  }

  /*
   * Save language item
   */
  function cml_admin_save_language_item( $data = null, $die = true ) {
    if( ! check_ajax_referer( "ceceppaml-nonce", "security" ) ) {
      echo json_encode( array( "html" => "",
                        "error" => __( "Security error", "ceceppaml" ) ) );

      echo "-1";
      die();
    }

    global $wpdb;

    //Extract data
    if( $data == null ) $data = $_POST[ 'data' ];
    parse_str( $data, $form );

    //Uploading custom flag?
    $flag = $form[ 'flag' ];
    $error = "";

    if ( isset( $_FILES[ 'flag' ] ) ) {
      $error = cml_admin_upload_custom_get_flag( $form );

      $flag = $form[ 'wp-locale' ];
    }

    //Data
    $is_default = intval( @$form[ 'default' ] );
    $data = array(
                  "cml_default" => $is_default,
                  "cml_enabled" => intval( @$form[ 'enabled' ] ),
                  "cml_custom_flag" => intval( @$form[ 'custom_flag' ] ),
                  "cml_sort_id" => intval( @$form[ 'pos' ] ),
                  "cml_rtl" => intval( @$form[ 'rtl' ] ),
                  "cml_flag" => $flag,
                  "cml_language" => @$form[ 'lang-name' ],
                  "cml_date_format" => @$form[ 'date-format' ],
                  "cml_language_slug" => @$form[ 'url-slug' ],
                  "cml_locale" => @$form[ 'wp-locale' ],
                  );
    $data_format = array( "%d", "%d", "%d", "%d", "%d", "%s", "%s", "%s", "%s", "%s" );

    $id = intval( $form[ 'id' ] );

    //Remove?
    if( @$form[ 'remove' ] == 1 ) {
      $html = "";

      $wpdb->delete( CECEPPA_ML_TABLE, array( "id" => $id ), array( "%s" ) );

      //Remove the column from CECEPPA_ML_RELATIONS table
      $wpdb->query( "ALTER TABLE " . CECEPPA_ML_RELATIONS . " DROP lang_" . $id );
    } else {
      if( $id > 0 ) {
        //Avoid that more languages are sets as "default"
        if( $is_default ) {
          $wpdb->query( "UPDATE " . CECEPPA_ML_TABLE . " SET cml_default = 0 " );
        }

        $wpdb->update( CECEPPA_ML_TABLE, $data, array(
                                                      "id" => $id,
                                                      ), $data_format, array( "%d" ) );
      } else {
        $wpdb->insert( CECEPPA_ML_TABLE, $data, $data_format );

        //Return the id of inserted element..
        $id = $wpdb->insert_id;

        //Add the new column to CECEPPA_ML_RELATIONS
        $sql = sprintf( "ALTER TABLE %s ADD lang_%d bigint(20) NOT NULL DEFAULT 0",
                       CECEPPA_ML_RELATIONS, $id );
        $wpdb->query( $sql );
      }

      cml_admin_language_add_new_item( array(
                                             "enabled" => $data[ 'cml_enabled' ],
                                               "flag" => $data[ "cml_flag" ],
                                               "name" => $data[ 'cml_language' ],
                                               "default" => $data[ 'cml_default' ],
                                               "date_format" => $data[ 'cml_date_format' ],
                                                "slug" => $data[ 'cml_language_slug' ],
                                                "locale" => $data[ 'cml_locale' ],
                                                "rtl" => $data[ 'cml_rtl' ],
                                                "id" => $id,
                                                "custom_flag" => $data[ 'cml_custom_flag' ],
                                              ) );

      //Try to download language pack
      cml_download_mo_file( @$form[ 'wp-locale' ] );
    }

    //update settings
    cml_generate_settings_php();

    //generate css
    cml_generate_cml_flags_css();

    if( ! empty( $error ) ) {
      $out = array( "error" => $error );
    }

    /*
     * if user change default language I need to generate settings again
     */
    update_option( 'cml_need_update_settings', 1 );
    update_option( 'cml_get_translation_from_po', 0 );

    die();
  }

  /*
   * Upload custom flag and store it in "uploads/ceceppaml"
   */
  function cml_admin_upload_custom_get_flag( $form ) {
    $return = "";

    //Error?
    if ( $_FILES[ "flag" ][ "error" ] > 0 ) {
      $return .= '<div class="error">';
      $return .= "Error: " . $_FILES[ "flag" ]["name"][ $i ] . "<br />";
      $return .= '</div>';
    } else {
      $imageData = @getimagesize( $_FILES["flag"]["tmp_name"] );

      //Check image type... Invalid?
      if( $imageData === FALSE || !( $imageData[2] == IMAGETYPE_GIF || $imageData[2] == IMAGETYPE_JPEG || $imageData[2] == IMAGETYPE_PNG ) ) {
        $return .= '<div class="error">';
        $return .= __( "Invalid image: ", 'ceceppaml' ) . $_FILES["flag"]["name"] . "<br />";
        $return .= '</div>';
      } else {
        $upload_dir = wp_upload_dir();
        $temp = $_FILES[ "flag" ]["tmp_name"];
        $outname = $form[ 'wp-locale' ] . ".png";

        //Resize the image
        list( $width, $height ) = getimagesize( $temp );
        $src = imagecreatefromstring( file_get_contents( $temp ) );

        //Make directories
        if( ! is_dir( $upload_dir[ 'basedir' ] . "/ceceppaml/tiny/" ) ) mkdir( $upload_dir[ 'basedir' ] . "/ceceppaml/tiny/" );
        if( ! is_dir( $upload_dir[ 'basedir' ] . "/ceceppaml/small/" ) ) mkdir( $upload_dir[ 'basedir' ] . "/ceceppaml/small/" );

        //Tiny
        $out = $upload_dir[ 'basedir' ] . "/ceceppaml/tiny/" . $outname;
        $tiny = imagecreatetruecolor( 16, 11 );
        imagecopyresized( $tiny, $src, 0, 0, 0, 0, 16, 11, $width, $height );
        imagepng( $tiny, $out );

        //Small
        $out = $upload_dir[ 'basedir' ] . "/ceceppaml/small/" . $outname;
        $small = imagecreatetruecolor( 32, 23 );
        imagecopyresized( $small, $src, 0, 0, 0, 0, 32, 23, $width, $height );
        imagepng( $small, $out );
      }
    }

    return $return;
  }

/***********************
 *
 * OPTIONS
 *
************************/
function cml_admin_save_options_actions() {
  if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

  $page = $_POST[ 'page' ];
  $tab = isset( $_POST[ 'tab' ] ) ? intval( $_POST[ 'tab' ] ) : 1;

  if( $tab == 2 ) {
    //Enable/Disable quick edit mode for each post type
    update_option( "cml_qem_enabled_post_types", @$_POST[ 'cml-qem-posttypes' ] );
    update_option( "cml_qem_match_categories", @$_POST[ 'cml-qem-match' ] );
    update_option( "cml_qem_enabled", intval( @$_POST[ 'cml-qem' ] ) );
    update_option( "cml_debug_enabled", intval( @$_POST[ 'cml-debug' ] ) );
    update_option( "cml_update_static_page", intval( @$_POST[ 'cml-static' ] ) );
    update_option( "cml_remove_extra_slug", intval( @$_POST[ 'cml-extra' ] ) );
    update_option( "cml_force_redirect", intval( @$_POST[ 'cml-redirect' ] ) );

    if( isset( $_POST[ 'cml-fix-500' ] ) ) {
      global $wp_rewrite;

      CMLUtils::_set( '_rewrite_rules', 1 );
      $wp_rewrite->flush_rules( true );
    }
  } else {
    //Redirect
    $redirect = array( "auto", "default", "others", "nothing" );
    $redirect = ( in_array( $_POST[ 'redirect' ], $redirect ) ) ? $_POST[ 'redirect' ] : "auto";
    update_option("cml_option_redirect", $redirect );

    //Url mode
    update_option( "cml_modification_mode", intval( $_POST[ 'url-mode' ] ) );
    update_option( "cml_modification_mode_default", intval( @$_POST[ 'url-mode-default' ] ) );

    //Translate category url
    @update_option( 'cml_option_translate_category_url', @intval( $_POST[ 'categories' ] ) );
    @update_option( 'cml_option_translate_category_slug', @intval( $_POST[ 'category-slug' ] ) );

    //Notices
    @update_option("cml_option_notice", sanitize_title( $_POST['notice'] ) );
    @update_option("cml_option_notice_pos", sanitize_title( $_POST['notice_pos'] ) );
    @update_option("cml_option_notice_after", $_POST['notice_after'] );
    @update_option("cml_option_notice_before", $_POST['notice_before'] );
    @update_option("cml_option_notice_post", intval( $_POST['notice-post'] ) );
    @update_option("cml_option_notice_page", intval( $_POST['notice-page'] ) );

    //I don't have to save this settings in "wizard" mode
    if( ! isset( $_POST[ 'wstep' ] ) ) {
      //Date format
      @update_option('cml_change_date_format', intval( $_POST['date-format'] ) );

      //Change locale
      update_option("cml_option_change_locale", intval( @$_POST['change-locale'] ) );

      //translate media?
      update_option("cml_option_translate_media", intval( @$_POST['translate-media'] ) );
    }
  }


  $lstep = "";
  if( isset( $_POST[ 'wstep' ] ) ) {
    $lstep = "&wstep=" . intval( $_POST[ 'wstep' ] );
  }
  $return = array( "url" => admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&cml-settings-updated=true' . $lstep ) );

  die( json_encode( $return ) );
}

function cml_admin_save_options_filters() {
  if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

  $page = $_POST[ 'page' ];
  $tab = isset( $_POST[ 'tab' ] ) ? intval( $_POST[ 'tab' ] ) : 1;

  //Filter posts
  @update_option("cml_option_filter_posts", intval($_POST['filter-posts']));

  //I don't have to save this settings in "wizard" mode
  if( ! isset( $_POST[ 'wstep' ] ) ) {
    //Filter translations
    @update_option("cml_option_filter_translations", intval($_POST['filter-translations']));

    //Filter query
    @update_option("cml_option_filter_query", intval($_POST['filter-query']));

    //Filter search
    @update_option("cml_option_filter_search", intval($_POST['filter-search']));
    @update_option("cml_option_filter_form_class", esc_html($_POST['filter-form']));

    //Translate menu items?
    @update_option( "cml_option_action_menu", intval( $_POST['action-menu'] ) );
    @update_option( 'cml_option_menu_hide_items', intval( $_POST[ 'menu-hide-items' ] ) );
    @update_option( 'cml_option_action_menu_force', intval( $_POST[ 'force-menu' ] ) );

    //Comments ( group / ungroup )
    @update_option('cml_option_comments', sanitize_title( $_POST['comments'] ) );
  }

  $lstep = "";
  if( isset( $_POST[ 'wstep' ] ) ) {
    $lstep = "&wstep=" . intval( $_POST[ 'wstep' ] );
  }
  $return = array( "url" => admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&cml-settings-updated=true' . $lstep ) );

  die( json_encode( $return ) );
}

function cml_admin_save_options_flags() {
  if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

  $page = $_POST[ 'page' ];
  $tab = isset( $_POST[ 'tab' ] ) ? intval( $_POST[ 'tab' ] ) : 1;

  //Force?
  @update_option( "cml_force_languge", intval( $_POST[ 'force' ] ) );

  //Flags
  @update_option("cml_option_flags_on_post", intval($_POST['flags-on-posts']));
  @update_option("cml_option_flags_on_page", intval($_POST['flags-on-pages']));
  @update_option("cml_option_flags_on_custom_type", intval($_POST['flags-on-custom']));
  @update_option("cml_option_flags_on_the_loop", intval($_POST['flags-on-loop']));
  @update_option("cml_option_flags_on_pos", sanitize_title( $_POST['flags_on_pos'] ) );
  @update_option("cml_options_flags_on_translations", intval( $_POST['flags-translated-only'] ) );

  //Size
  @update_option("cml_option_flags_on_size", sanitize_title($_POST['flag-size']));

  //Float
  @update_option("cml_add_float_div", intval( $_POST['float-div'] ) );
  $css = addslashes( $_POST['custom-css'] );
  update_option( 'cml_float_css', $css ); //Non posso scrivere su file, sennò ad ogni aggiornamento viene sovrascritto ;)

  if( ! file_exists( CML_UPLOAD_DIR ) ) @mkdir( CML_UPLOAD_DIR );
  @file_put_contents( CML_UPLOAD_DIR . "/float.css", $css );

  //Show as...
  @update_option( "cml_show_float_items_as", intval( $_POST[ 'float-as' ] ) );
  @update_option( "cml_show_float_items_style", intval( $_POST[ 'float-style' ] ) ); //list or combo?

  //Flag size...
  @update_option("cml_show_float_items_size", $_POST['float-size']);

  //Append
  @update_option("cml_append_flags", intval($_POST['append-flags']));
  update_option("cml_append_flags_to", esc_html( $_POST['id-class'] ) );
  @update_option( "cml_show_html_items_style", intval( $_POST[ 'html-style' ] ) ); //list or combo?

  //Show as...
  @update_option("cml_show_items_as", intval($_POST['show-items-as']));

  //Flag size...
  @update_option( "cml_show_items_size", sanitize_title( $_POST['item-as-size'] ) );

  //Menu
  @update_option("cml_add_flags_to_menu", intval($_POST['to-menu']));

  //Menu location
  $menus = isset( $_POST['cml_add_items_to'] ) ?
                  $_POST['cml_add_items_to'] : array();

  if( ! is_array( $menus ) ) $menus = array();
  @update_option( "cml_add_items_to", $menus );

  //Add items as...
  @update_option("cml_add_items_as", intval($_POST['add-as']));

  //Show as...
  @update_option("cml_show_in_menu_as", intval($_POST['show-as']));

  //Flag size...
  @update_option("cml_show_in_menu_size", sanitize_title( $_POST['submenu-size'] ) );

  $return = array( "url" => admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&cml-settings-updated=true' ) );

  die( json_encode( $return ) );
}


/***********************
 *
 * Site title & Tagline
 *
************************/
function cml_admin_save_site_title() {
  if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

  $page = $_POST[ 'page' ];
  $tab = isset( $_POST[ 'tab' ] ) ? intval( $_POST[ 'tab' ] ) : 1;

  CMLTranslations::delete( "T" );

  $blog_title = get_bloginfo( 'name' );
  $blog_tagline = get_bloginfo( 'description' );

  $ids = $_POST[ 'id' ];
  $i = 0;
  foreach( $ids as $id ) {
    CMLTranslations::set( $id,
                         $blog_title,
                         $_POST[ 'title' ][ $i ], "T" );

    CMLTranslations::set( $id,
                         $blog_tagline,
                         $_POST[ 'tagline' ][ $i ], "T" );

    $i++;
  }

  cml_generate_mo_from_translations( "_X_", false );

  $return = array( "url" => esc_raw_url( admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&cml-generate-settings=true' ) ) );

  die( json_encode( $return ) );
}

/*
 * generate .mo file
 */
function cml_admin_generate_mo() {
  if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

    //CML parser, used for translate theme and plugin
  require_once ( CML_PLUGIN_ADMIN_PATH . 'po-parser.php' );

  $page = $_POST[ 'page' ];
  $tab = isset( $_POST[ 'tab' ] ) ? intval( $_POST[ 'tab' ] ) : 1;

  $name = $_POST[ 'name' ];
  $translate_in = @$_POST[ 'cml-lang' ];
  $src_path = $_POST[ 'src_path' ];
  $dest_path = $_POST[ 'dest_path' ];
  $domain = $_POST[ 'domain' ];


  if( ! empty( $translate_in ) ) {
    $translate_in = array_keys( $translate_in );
  } else {
    $translate_in = array();
  }

  update_option( 'cml_translate_' . $name . "_in", $translate_in );

  if( ! empty( $translate_in ) && ! isset( $_POST[ 'nolang' ] ) ) {
    //$parser = new CMLParser( "Ceceppa Multilingua", "en", CML_PLUGIN_PATH, CML_PLUGIN_LANGUAGES_PATH, "ceceppaml", false );
    $parser = new CMLParser( $name, $src_path, $dest_path, $domain, false );

    $generated = "[" . join( ", ", $parser->generated() ) . "]";
    $error = $parser->errors();

    $return = array( "url" => esc_raw_url( admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&updated=true&generated=' . $generated . '&error=' . $error ) ) );
  } else {
    $return = array( "url" => esc_raw_url( admin_url( 'admin.php?page=' . $page . '&tab=' . $tab ) ) );
  }

  die( json_encode( $return ) );
}

/***********************
 *
 * Do Backup
 *
************************/
/**
 * Some user reported relation lost after update... I tried to fix the issue
 * with no success.
 * So I decide to wrote a "backup" function that will be automatically exectuted after an update,
 * but manually as well
 */
function cml_backup_do() {
    if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

    $page = $_POST[ 'page' ];
    $tab = isset( $_POST[ 'tab' ] ) ? intval( $_POST[ 'tab' ] ) : 1;

    $status = "1";
    if( ! _cml_check_backup_folder() ) {
        $status = "-2";
    } else {
        //Backup file
        $backup_file = date( 'Ymd-His' );
        $db_backup = $backup_file . '.db';
        $settings_backup = $backup_file . '.settings';

        //Backup tables
        $db = $s1 = $s2 = 0;
        if( isset( $_POST[ 'cml-tables' ] ) &&
            intval( $_POST[ 'cml-tables' ] ) == 1 ) {
            $db = _cml_backup_do_tables( "DB", CECEPPAML_BACKUP_PATH . $db_backup );
        }

        if( isset( $_POST[ 'cml-settings' ] ) &&
            intval( $_POST[ 'cml-settings' ] ) == 1 ) {
            $s1 = _cml_backup_do_tables( "SETTINGS", CECEPPAML_BACKUP_PATH . $settings_backup,
                                          " option_name, option_value, autoload ",
                                          " WHERE option_name LIKE 'cml_%' OR option_name LIKE '_cml_%' " );
        }

        $status .= "&file[]=" . join( "&file[]=", array( $db_backup, $settings_backup ) );
        $status .= "&stat[]=" . join( "&stat[]=", array( $db, $s1 ) );
    }

    $url = admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&status=' . $status );
    $return = array(
                        "url" => esc_url_raw( $url ),
                    );

  die( json_encode( $return ) );
}

function cml_backup_export() {
    if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

    //Settings?
    $s1 = CECEPPAML_BACKUP_PATH . ".tmp1";
    $s2 = CECEPPAML_BACKUP_PATH . ".tmp2";

    if( isset( $_POST[ 'cml-tables' ] ) ) {
        _cml_backup_do_tables( "DB", $s1 );
    }

    if( isset( $_POST[ 'cml-settings' ] ) ) {
        _cml_backup_do_tables( "SETTINGS",  $s2,
                                      " option_name, option_value, autoload ",
                                      " WHERE option_name LIKE 'cml_%' OR option_name LIKE '_cml_%' " );
    }

    $url = add_query_arg(
                          array(
                                  'page' => 'ceceppaml-backup-page',
                                  'tab' => 2,
                                  'download' => 1,
                               ),
                          admin_url() . "admin.php"
                        );
    $json = array( 'url' => esc_url_raw( $url ),
                   'show' => 1,
                 );

    die( json_encode( $json ) );
}

function cml_backup_import() {
  if( ! wp_verify_nonce( $_POST[ "security" ], "security" ) ) die( "-1" );
  global $wpdb;

  $url = array(
                'page' => 'ceceppaml-backup-page',
                'tab' => 2,
             );

  if( empty( $_FILES ) || $_FILES[ 'file' ][ 'error' ] != 0 ) {
    $url[ 'invalid' ] = 1;
  } else {
    $filename = $_FILES[ 'file' ][ 'tmp_name' ];
    $content = file_get_contents( $filename );

    //Check the string /**CML: xxxxx **/ into the file
    if( preg_match_all("/\/\*\*CML:[^\/].*/", $content, $output) ) {

      /**
       * mysql_query() sends a unique query (multiple queries are not supported)
       */
      $queries = explode( ";\n", $content );

      foreach( $queries as $query ) {
        $query = trim( $query );
        if( empty( $query ) ) continue;

        $query .= ";";
        $wpdb->query( $query );
      }

      $url[ 'done' ] = 1;
    } else {
      $url[ 'invalid' ] = 2;
    }
  }

  //I need to force the settings.php re-generation
  update_option('cml_use_settings_gen', 0);

  $url = add_query_arg( $url,
                         admin_url() . "admin.php?cml-settings-updated=1"
                       );
  $json = array( 'url' => esc_url_raw( $url ) );

  die( json_encode( $json ) );
}

function cml_admin_translated_slugs() {
  if( ! wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) die( "-1" );

  //Get all the active slugs
  $slugs = array();
  foreach( $_POST[ 'slug' ] as $key => $slug ) {
    $enabled = isset( $_POST[ 'senabled' ][ $key ] );
    $translations = $_POST[ 'tslug' ][ $key ];
    $type = $_POST[ 'slug' ][ $key ];

    foreach( $translations as $lang => $trans ) {
      if( ! empty( $trans ) ) {
        $slugs[ $type ][ 'enabled' ] = $enabled;
        $slugs[ $type ][ $lang ] = $trans;
      }
    }
  }
  update_option( 'cml_translated_slugs', $slugs );

  $url = array(
                'page' => $_POST[ 'page' ],
             );

  $url = add_query_arg( $url,
                           admin_url() . "admin.php"
                         );
  $json = array( 'url' => esc_url_raw( $url ) );

  die( json_encode( $json ) );
}

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
    update_option( "cml_debug_enabled", intval( @$_POST[ 'cml-debug' ] ) );
    update_option( "cml_update_static_page", intval( @$_POST[ 'cml-static' ] ) );
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

  $return = array( "url" => admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&cml-generate-settings=true' ) );

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

    $return = array( "url" => admin_url( 'admin.php?page=' . $page . '&tab=' . $tab . '&updated=true&generated=' . $generated . '&error=' . $error ) );
  } else {
    $return = array( "url" => admin_url( 'admin.php?page=' . $page . '&tab=' . $tab ) );
  }
  
  die( json_encode( $return ) );
}

?>

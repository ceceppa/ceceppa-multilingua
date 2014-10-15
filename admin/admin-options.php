<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );

//Current tab
$tab = isset( $_GET[ "tab" ] ) ? intval( $_GET[ "tab" ] ) : 0;
switch( $tab ) {
case 0:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'options-filters.php' );
  break;
case 1:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'options-actions.php' );
  break;
case 2:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'options-advanced.php' );
  break;
case 3:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'options-uninstall.php' );
  
  if( isset( $_GET[ 'erase' ] ) && intval( $_GET[ 'erase' ] == 2 ) ) {
    global $wpdb;

    //Do uninstall

    /*
     * remove all post meta
     */
    $posts = $wpdb->get_results( "SELECT * FROM " . CECEPPA_ML_RELATIONS, ARRAY_A );
    foreach( $posts as $post ) {
      unset( $post[ 'id' ] );
      foreach( $post as $key => $id ) {
        delete_post_meta( $id, "_cml_meta" );
      }
    }

    /*
     * remove all tables
     */
    $tables = array( CECEPPA_ML_TABLE, CECEPPA_ML_CATS,
                     CECEPPA_ML_RELATIONS, CECEPPA_ML_TRANSLATIONS );
    
    foreach( $tables as $table ) {
      $wpdb->query( "DROP TABLE $table" );
    }
    
    /*
     * remove settings
     */
    foreach( $GLOBALS[ '_cml_settings' ] as $key => $value ) {
      delete_option( $key );
    }

    /*
     * extra settings
     */
    delete_option( "cml_modification_mode" );
    delete_option( "cml_modification_mode_default" );
    delete_option( "cml_erased" );
    delete_option( "_cml_update_existings_posts" );
    delete_option( "cml_show_wizard" );
    delete_option( '_cml_wpml_config' );
    delete_option( '_cml_scan_folders' );
    delete_option( 'cml_add_items_to' );
    delete_option( 'cml_add_slug_to_link' );
    delete_option( 'cml_float_css' );
    delete_option( 'cml_first_install' );
    delete_option( 'cml_get_translation_from_po' );
    delete_option( 'cml_is_first_time' );
    delete_option( 'cml_languages_ids' );
    delete_option( 'cml_languages_ids_keys' );
    delete_option( '_cml_wpml_config_paths' );

    delete_option( "cml_translated_fields_yoast" );
    delete_option( "cml_translated_fields_aioseo" );

    /*
     * restore helps
     */
    _cml_restore_wp_pointers();
  }

  break;
}

//wizard step?
$wstep = isset( $_GET[ 'wstep' ] ) ? intval( $_GET[ 'wstep' ] ) : "";
$lstep = ( empty( $wstep ) ) ? "" : "&wstep=$wstep";

$page = $_GET[ 'page' ];
?>
<div class="wrap">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=0<?php echo $lstep ?>"><?php _e('Filters', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 1 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=1<?php echo $lstep ?>"><?php _e('Actions', 'ceceppaml') ?></a>
    
    <?php if( empty( $wstep ) ) : ?>
    <a class="nav-tab <?php echo $tab == 2 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=2"><?php _e('Advanced', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 3 ? "nav-tab-active" : "" ?> cml-uninstall" href="?page=<?php echo $page ?>&tab=3"><?php _e('Uninstall', 'ceceppaml') ?></a>
    <?php endif; ?>
  </h2>

  <div id="poststuff">
    <div id="post-body" class="columns-2 ceceppaml-metabox">
      <div id="post-body-content" class="cml-box-options">
        <form id="form" name="language-item" method="POST" class="cml-ajax-form">
          <input type="hidden" name="new" value="0" />
          <input type="hidden" name="action" value="ceceppaml_save_options_<?php echo ( $tab == 0 ) ? "filters" : "actions" ?>" />
          <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />
          <input type="hidden" name="tab" value="<?php echo $tab ?>" />
          <?php if( ! empty( $wstep ) ) : ?>
          <input type="hidden" name="wstep" value="<?php echo $wstep ?>)" />
          <?php endif; ?>
          <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
          <?php
            do_meta_boxes( 'cml_box_options', 'advanced', null );
          ?>
        
          <?php if( $tab <= 1 ) : ?>
          <div class="cml-submit-button">
            <div class="wpspinner">
              <span class="spinner"></span>
            </div>
            <?php submit_button(); ?>
          </div>
          <?php endif; ?>
        </form>
      </div>
      <div id="postbox-container-1" class="postbox-container cml-donate">
        <?php do_meta_boxes( 'cml_donate_box', 'advanced', null ); ?>
      </div>
    </div>
  </div>
  
</div>

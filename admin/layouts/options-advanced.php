<?php
require_once( CML_PLUGIN_FRONTEND_PATH . "utils.php" );

if( isset( $_GET[ 'cml-restore-wp' ] ) ) {
  _cml_restore_wp_pointers();
  
  $msg = __( 'Helps restored succesfully', 'ceceppaml' );
echo <<< EOT
  <div class="updated">
    <p>
      $msg
    </p>
  </div>
EOT;
}


function cml_admin_options_advanced_wizard() {
  ?>
  <div id="minor-publishing">
    <div>
      <a href="<?php echo add_query_arg( array ( "cml-restore-wizard" => 1 ) ); ?>">
        <?php _e( 'Start wizard', 'ceceppaml') ?>
      </a>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Start wizard againg. Current settings will not be erased', 'ceceppaml' ); ?>
  </div>
<?php
}

function cml_admin_options_advanced_pointers() {
  ?>
  <div id="minor-publishing">
    <div>
      <a href="<?php echo add_query_arg( array ( "cml-restore-wp" => 1 ) ); ?>">
        <?php _e( 'Restore helps', 'ceceppaml') ?>
      </a>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'You can add flags to menu throught <b>Aspect -> Menu page</b>, or enabling this option.', 'ceceppaml' ); ?><br />
    <?php _e( 'Use this box for also customize the style of items added throught <b>Aspect -> Menu page</b>', 'ceceppaml' ); ?>
  </div>
<?php
}

function cml_admin_options_update_language() {
  ?>
  <div id="minor-publishing">
    <div>
      <?php
        printf( __( "Click <a href=\"%s\">here</a> to assign \"%s\" to existing posts and pages.", "ceceppaml" ),
                add_query_arg( array( 'cml_update_existings_posts' => 1 ) ),
                CMLLanguage::get_default()->cml_language ); ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Automatically assign default language to existing posts', 'ceceppaml' ); ?>
  </div>
<?php
}

function cml_admin_options_update_relations() {
  ?>
  <div id="minor-publishing">
    <div>
      <?php cml_migrate_notice( true ); ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
  </div>
<?php
}

function cml_admin_options_enable_debug() {
  ?>
  <div id="minor-publishing">
    <div>
        <?php echo cml_utils_create_checkbox( "Debug", "cml-debug", "cml-debug", null, 1, get_option( "cml_debug_enabled", 0 ) ) ?>
        <?php submit_button() ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
  </div>
<?php
}

function cml_admin_options_update_static_page() {
  ?>
  <div id="minor-publishing">
    <div>
        <?php echo cml_utils_create_checkbox( __( 'Update static page', 'ceceppaml' ), "cml-static", "cml-static", null, 1, get_option( "cml_update_static_page", 1 ) ) ?>
        <?php submit_button() ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Update Wordpress static page settings. If the plugin set random page as homepage try to deactivat this option', 'ceceppaml' ); ?>
  </div>
<?php
}

$help = __( 'Show/Hide help', 'ceceppaml' );

add_meta_box( 'cml-box-start-wizard', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Wizard', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_advanced_wizard', 'cml_box_options' );
add_meta_box( 'cml-box-assign-to', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Update language of existing posts', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_update_language', 'cml_box_options' );
add_meta_box( 'cml-box-restore-helps', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Restore helps', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_advanced_pointers', 'cml_box_options' );
add_meta_box( 'cml-box-update-relations', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Update post relations', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_update_relations', 'cml_box_options' );
add_meta_box( 'cml-box-enable-static-change', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Static page', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_update_static_page', 'cml_box_options' );

if( file_exists( CML_PLUGIN_PATH . "debug.php" ) ) {
  add_meta_box( 'cml-box-enable-debug', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Debug', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_enable_debug', 'cml_box_options' );
}
?>

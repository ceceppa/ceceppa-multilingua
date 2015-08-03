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


function cml_admin_options_advanced_qem() {
  ?>
  <div id="minor-publishing">
    <div class="inside <?php echo get_option( "cml_qem_enabled", 1 ) ? '' : 'disabled'; ?>">
      <ul>
      <?php

        //I don't need all the builtin post type, like media...
        $post_types = get_post_types( array( '_builtin' => FALSE ), 'names');
        $post_types[] = "post";
        $post_types[] = "page";
        $post_types = apply_filters( 'cml_manage_post_types', $post_types );

        //Enabled post types ( by default all )
        $enabled = get_option( 'cml_qem_enabled_post_types', $post_types );
        foreach( $post_types as $post_type ) {

          $checked = checked( in_array( $post_type, $enabled ), 1, false );
echo <<< LI
  <li>
    <div class="cml-checkbox">
      <input type="checkbox" id="cml-qem-{$post_type}" name="cml-qem-posttypes[$post_type]" value="$post_type" $checked />
      <label for="cml-qem-{$post_type}"><span>||</span></label>
    </div>
    <label for="cml-qem-{$post_type}">$post_type</label>
  </li>
LI;
      }
      ?>
      </ul>
      <br>
      <strong>Categories</strong>
      <br>
      <div class="cml-checkbox">
        <input type="checkbox" id="cml-qem-match" name="cml-qem-match" value="1" <?php checked( get_option( 'cml_qem_match_categories', false ) ) ?> />
        <label for="cml-qem-match"><span>||</span></label>
      </div>
      <label for="cml-qem-match"><?php _e( "Update translation's categories on save", 'ceceppaml' ); ?></label>
      <?php submit_button() ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'The Quick Edit Mode allow you to esily edit you post and its translations in the same page.', 'ceceppaml' ); ?>
  </div>
<?php
}

function cml_admin_options_advanced_wizard() {
  ?>
  <div id="minor-publishing">
    <div>
      <a href="<?php echo esc_url( add_query_arg( array ( "cml-restore-wizard" => 1 ) ) ); ?>">
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
      <a href="<?php echo esc_url( add_query_arg( array ( "cml-restore-wp" => 1 ) ) ); ?>">
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
                esc_url( add_query_arg( array( 'cml_update_existings_posts' => 1 ) ) ),
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

function cml_admin_remove_extra_slug() {
  ?>
  <div id="minor-publishing">
    <div>
        <?php echo cml_utils_create_checkbox( __( 'Remove the numeric append on duplicate wordpress titles', 'ceceppaml' ), "cml-extra", "cml-extra", null, 1, get_option( "cml_remove_extra_slug", 1 ) ) ?>
        <?php submit_button() ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
      <?php
        _e( 'Wordpress automatically append a numeric flag in permalink when one or more post/page has the same name.', 'ceceppaml' );
        _e( "The plugin will remove it, but you can disable this feature if doesn't works fine for you", 'ceceppaml' );
      ?>
  </div>
<?php
}

function cml_admin_force_post_redirect() {
  ?>
  <div id="minor-publishing">
    <div>
        <?php echo cml_utils_create_checkbox( __( 'Force post redirect', 'ceceppaml' ), "cml-redirect", "cml-redirect", null, 1, get_option( "cml_force_redirect", false ) ) ?>
        <?php submit_button() ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
      <?php
      ?>
  </div>
<?php
}

function cml_admin_fix_htaccess() {
  ?>
  <div id="minor-publishing">
    <div>
        <?php echo cml_utils_create_checkbox( __( 'Fix 500 Internal Server Error', 'ceceppaml' ), "cml-fix-500", "cml-fix-500", null, 1, get_option( "cml_fix_htaccess", false ) ) ?>
        <?php submit_button() ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
        <?php _e( 'Something could happen that you got a 500 Internal Server Error, after saved some options from the admin panel.', 'ceceppaml' ); ?>
        <br />
        <strong><?php _e( "I know, it's no nice when happens and I'm trying all my best to avoid it.", 'ceceppaml' ) ?>;</strong>
        <br /><br />
        <?php _e( 'But if unfortunatelly it happend to you, just click select the option and click the button, and fix it.', 'ceceppaml' ); ?>
  </div>
<?php
}


$help = __( 'Show/Hide help', 'ceceppaml' );

add_meta_box( 'cml-box-quick-edit', cml_utils_create_checkbox( '', "cml-qem", "cml-qem", null, 1, get_option( "cml_qem_enabled", 1 ) ) . '<label for="cml-qem">' . __( 'Quick edit mode', 'ceceppaml' ) . "</label>:<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_advanced_qem', 'cml_box_options' );

add_meta_box( 'cml-box-start-wizard', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Wizard', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_advanced_wizard', 'cml_box_options' );
add_meta_box( 'cml-box-assign-to', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Update language of existing posts', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_update_language', 'cml_box_options' );
add_meta_box( 'cml-box-restore-helps', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Restore helps', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_advanced_pointers', 'cml_box_options' );
// add_meta_box( 'cml-box-update-relations', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Update post relations', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_update_relations', 'cml_box_options' );
add_meta_box( 'cml-box-enable-static-change', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Static page', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_update_static_page', 'cml_box_options' );

//Disable extra slug remover
add_meta_box( 'cml-box-disable-extra-slug', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Duplicated titles', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_remove_extra_slug', 'cml_box_options' );

//Force post/page redirect
add_meta_box( 'cml-box-force-redirect', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Force post redirect', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_force_post_redirect', 'cml_box_options' );

//Force post/page redirect
add_meta_box( 'cml-box-fix-500-error', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Fix 500 Server error', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_fix_htaccess', 'cml_box_options' );

if( file_exists( CML_PLUGIN_PATH . "debug.php" ) ) {
  add_meta_box( 'cml-box-enable-debug', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Debug', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_enable_debug', 'cml_box_options' );
}

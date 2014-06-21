<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-settings-gen.php' );

require_once ( CML_PLUGIN_LAYOUTS_PATH . 'options-flags.php' );

$page = $_GET[ 'page' ];
?>
<div class="wrap">
  <h2><?php _e( 'Flags', 'ceceppaml' ) ?></h2>
  <div id="poststuff">
    <div id="post-body" class="columns-2 ceceppaml-metabox">
      <div id="post-body-content" class="cml-box-options">
        <form id="form" name="language-item" method="POST" class="cml-ajax-form">
          <input type="hidden" name="new" value="0" />
          <input type="hidden" name="action" value="ceceppaml_save_options_flags" />
          <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />

          <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
          <?php
            do_meta_boxes( 'cml_box_options', 'advanced', null );
          ?>
        
          <div class="cml-submit-button">
            <div class="wpspinner">
              <span class="spinner"></span>
            </div>
            <?php submit_button(); ?>
          </div>
        </form>
      </div>
      <div id="postbox-container-1" class="postbox-container cml-donate">
        <?php do_meta_boxes( 'cml_donate_box', 'advanced', null ); ?>
      </div>
    </div>
  </div>
  
</div>
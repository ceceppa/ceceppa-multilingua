<?php
//wp_enqueue_script( 'plugin-install' );
//add_thickbox();

function cml_admin_box_backup() {
  $tab = isset( $_GET[ 'tab' ] ) ? intval( $_GET[ 'tab' ] ) : 0;
?>
<form id="form" name="backup" method="POST" class="cml-ajax-form">
  <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />
  <input type="hidden" name="tab" value="<?php echo $tab ?>" />
  <input type="hidden" name="action" value="ceceppaml_do_backup" />
  <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
  <div id="minor-publishing">
      <?php _e( 'Select what do you want to backup:', 'ceceppaml' ) ?>
      <ul class="cml-ul-list">
          <li>
              <?php echo cml_utils_create_checkbox( "Tables", "cml-tables", "cml-tables", null, 1, 1 ) ?>
          </li>
          <li>
              <?php echo cml_utils_create_checkbox( "Settings", "cml-settings", "cml-settings", null, 1, 1 ) ?>
          </li>
      </ul>

      <div class="cml-submit-button" style="height: 30px">
        <div class="wpspinner">
          <span class="spinner"></span>
        </div>
        <?php if( _cml_check_backup_folder() ) : ?>
          <?php submit_button( __( 'Back it up', 'ceceppaml' ), "button-primary", "action", false, 'class="button button-primary"' ); ?>
        <?php else: ?>
          <span class="button button-disabled"><?php _e( 'Backup not available', 'ceceppaml' ) ?></span>
        <?php endif; ?>
      </div>

      <div style="clear: both"></div>
  </div>
  <div id="major-publishing-actions" class="cml-description">
      <p>
        <?php _e( 'Use this option to manually backup your plugin data and settings.', 'ceceppaml' ); ?><br />
        <?php printf( __( 'The backup will stored in <i>%s</i> folder', 'ceceppaml' ), CECEPPAML_BACKUP_PATH ); ?><br />
      </p>
  </div>
</form>
<?php
}

//add_meta_box( 'cml-box-backup', __( 'Backup', 'ceceppaml' ), 'cml_admin_box_backup', 'cml_box_backup' );
$help = __( 'Show/Hide help', 'ceceppaml' );

add_meta_box( 'cml-box-backup', '<span class="cml-icon cml-icon-backup "></span>' . __( 'Backup', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_box_backup', 'cml_box_backup' );

<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

$tab = intval( $_GET[ 'tab' ] );

function cml_admin_box_backup_export() {
    global $tab;
?>
<form id="form" name="backup" method="POST" class="cml-ajax-form">
  <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />
  <input type="hidden" name="tab" value="<?php echo $tab ?>" />
  <input type="hidden" name="action" value="ceceppaml_export_backup" />
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
        <?php submit_button( __( 'Export', 'ceceppaml' ), "button-primary", "action", false, 'class="button button-primary"' ); ?>
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

function cml_admin_box_backup_import() {
    global $tab;

  if( isset( $_GET[ 'invalid' ] ) ) {
    $msg = ( intval( $_GET[ 'invalid' ] ) == 1 ) ?
      __( "No file selected", 'ceceppaml' ) :
      __( "Selected file is not a valid Ceceppa's backup file", 'ceceppaml' );

    _cml_wp_error_div( "Backup restore failed", $msg );
  }

  if( isset( $_GET[ 'done' ] ) )
  {
      $msg = __( "Restore succesfully completed", 'ceceppaml' );

    _cml_wp_updated_div( "Backup restore done", $msg );
  }
?>
<form id="form" name="backup" method="POST" class="cml-ajax-form" data-use-formdata="1">
  <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />
  <input type="hidden" name="tab" value="<?php echo $tab ?>" />
  <input type="hidden" name="action" value="ceceppaml_import_backup" />
  <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>

  <div id="minor-publishing">
      <ul class="cml-ul-list">
          <li>
              <?php _e( "Select Ceceppa's backup file:", 'ceceppaml' ) ?>
              <blockquote>
                  <input type="file" name="database" />
              </blockquote>
          </li>
      </ul>

      <div class="cml-submit-button" style="height: 30px">
        <div class="wpspinner">
          <span class="spinner"></span>
        </div>
        <?php submit_button( __( 'Import', 'ceceppaml' ), "button-primary", "action", false, 'class="button button-primary"' ); ?>
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

add_meta_box( 'cml-box-backup-import', '<span class="cml-icon cml-icon-backup "></span>' . __( 'Import', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_box_backup_import', 'cml_box_backup' );
add_meta_box( 'cml-box-backup-export', '<span class="cml-icon cml-icon-backup "></span>' . __( 'Export', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_box_backup_export', 'cml_box_backup' );

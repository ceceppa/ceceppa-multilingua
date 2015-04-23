<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * Translation not available
 */
function cml_admin_options_uninstall() {
  ?>
  <div id="minor-publishing">
    <p>
      <span>
        <?php _e( "This procedure will erase all plugin data from database", 'ceceppaml' ); ?>.
        <?php _e( "Following tables will be dropped", 'ceceppaml' ); ?>
      </span>
    </p>
    <ul class="cml-ul-list">
      <li>
        <?php echo CECEPPA_ML_TABLE ?>
      </li>
      <li>
        <?php echo CECEPPA_ML_CATS ?>
      </li>
      <li>
        <?php echo CECEPPA_ML_POSTS ?>
      </li>
      <li>
        <?php echo CECEPPA_ML_RELATIONS ?>
      </li>
      <li>
        <?php echo CECEPPA_ML_TRANSLATIONS ?>
      </li>
    </ul>

    <p>
      <span class="cml-uninstall">
        <?php _e( 'This procedure cannot be undone!!!', 'ceceppaml' ); ?>
      </span>
    </p>
    <p>
      <span class="cml-uninstall">
        <?php _e( 'This procedure will remove ONLY plugin data, posts and other data will NOT be erased.', 'ceceppaml' ); ?>
      </span>
    </p>

    <p>
      <?php _e( 'For recreate tables you have to disable and enable the plugin.', 'ceceppaml' ); ?>
    </p>

    <br />
    <p style="text-align: right">
      <a href="<?php echo esc_url( add_query_arg( array( "erase" => 1 ) ) ) ?>">
        <?php _e( "Yes, I read and I want to erase all data", "ceppaml" ); ?>
      </a>
    </p>
  </div>

<?php
}

function cml_admin_options_uninstall_sure() {
  ?>
  <div id="minor-publishing">
    <p>
    <p>
      <?php if( $_GET[ 'erase' ] == 1 ) : ?>
      <a href="<?php echo esc_url( add_query_arg( array( "erase" => 2 ) ) ) ?>"  class="cml-uninstall">
        <?php _e( "Yes, I'm sure. DO IT!", "ceppaml" ); ?>
      </a>
      <?php
      else:
        _e( 'Uninstall completed. Deactivate and activate the plugin to rebuild tables', 'ceceppaml' );
      endif;
      ?>
    </p>
    </p>
  </div>
<?php
}

if( ! isset( $_GET[ 'erase' ] ) )
  add_meta_box( 'cml-box-options-not', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Uninstall the plugin:', 'ceceppaml' ) . "</span>", 'cml_admin_options_uninstall', 'cml_box_options' );
else
  add_meta_box( 'cml-box-options-not', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Are you sure?', 'ceceppaml' ) . "</span>", 'cml_admin_options_uninstall_sure', 'cml_box_options' );
?>

<?php
add_action( 'admin_notices', 'cml_show_admin_notices' );

function cml_show_admin_notices() {
  global $wpdb;

  if( ! current_user_can( 'manage_optios' ) ) {
    return;
  }

  if( isset( $_GET[ 'fix-upgrade' ] ) ) {
    update_option( "cml_db_version", 22 );

    $GLOBALS[ 'cml_db_version' ] = 22;

    require_once( CML_PLUGIN_ADMIN_PATH . "fix.php" );
    cml_do_update();
  }

  /* check if column cml_cat_translation_slug exists in ceceppa_ml_cats, otherwise something goes wrong during update */
  $sql = "SHOW COLUMNS FROM  " . CECEPPA_ML_CATS . " LIKE  'cml_cat_translation_slug'";
  $exists = $wpdb->get_row( $sql );
  if( null == $exists ) {
    $link = add_query_arg( array( "fix-upgrade" => 1 ) );

?>
    <div class="error">
      <p>
        <strong>Ceceppa Multilingua</strong>
        <br /><br />
        <?php printf( __( 'Something goes wrong during upgrade, click <%s>here</a> to fix ', 'ceceppaml' ),
                     'a href="' . $link . '"' ) ?>
      </p>
    </div>
<?php
  }
}


/*
 * Quick edit mode notices
 */
function cml_show_qem_notice() {
  if( isset( $_GET[ 'qem-hide' ] ) ) {
    update_option( 'cml_hide_qem_notice', 1 );
  }

  if( get_option( 'cml_hide_qem_notice', 0 ) ) return;
?>
<div class="notice">
  <p>
    <i>Ceceppa Multilingua:</i>&nbsp;<strong><?php _e( 'Quick Edit mode', 'ceceppaml' ); ?></strong>
  </p>
  <br />
  <p>
    <?php _e( 'Quick Edit Mode allow you to easily edit your post and its translation from one page.', 'ceceppaml' ); ?>
    <?php _e( 'How it works?', 'ceceppaml' ); ?>
    <br />
    <?php _e( "It's easy, just translate the title and the content.", 'ceceppaml' ); ?>
    <?php _e( "Once done you can choose if publish your translations as well or not. If not, they'll saved as draft.", 'ceceppaml' ); ?>
    <br />    <br />
    <?php _e( "From this page you can't translate the custom posts of your translations, if there is any. But you need to do it by editing each single translations.", 'ceceppaml' ); ?>

    <?php if( defined( 'WPSEO_VERSION' ) ) : ?>
    <br /><br />
    <?php _e( "You're using YOAST... This mode is compatible with it, and so you can translate/edit your yoast fields for the translations, as well... Just fill them...", 'ceceppaml' ); ?>
    <?php endif; ?>
    <br /><br />
    <?php _e( "Anyway, you don't like this mode, or is not compatible with your theme?", 'ceceppaml' ); ?>
    <br />
    <?php _e( 'No problem, just go to the Settings page, <a href="%s" class="">Advanced mode tab</a> to disable it globally, or just for a certain post type...', 'ceceppaml' ); ?>
  </p>
  <br />
  <p class="submit">
      <a class="button button-primary" style="float: right" href="<?php echo add_query_arg( array( 'qem-hide' => 1 ) ); ?>">
        <?php _e( 'Hide', 'ceceppaml' ); ?>
      </a>
  </p>
  <div style="clear:both"></div>
  <br />
</div>
<?php
}

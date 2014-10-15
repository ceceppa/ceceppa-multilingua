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
?>

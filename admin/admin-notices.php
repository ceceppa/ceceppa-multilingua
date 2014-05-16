<?php
function cml_show_admin_notices() {
  global $wpdb;

  //if( ! current_user_can( 'manage_optios' ) ) {
  //  return;
  //}

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
  
  if( isset( $_GET[ 'cml_tax_0' ] ) ) {
    update_option( "cml_update_taxonomy_translation", 1 );
    update_option( "cml_categories", array() );
  }
  
  if( isset( $_GET[ 'cml_tax' ] ) ) {
    update_option( "cml_update_taxonomy_translation", 0 );
    update_option( "cml_categories", array() );

    cml_update_taxonomy_translations();
  }

  //translated category
  if( get_option( "cml_update_taxonomy_translation", 1 ) == 1 ) {
?>
    <div class="updated">
      <p>
        <strong>Ceceppa Multilingua</strong>
        <br /><br />
        <?php printf( __('Update required, click <%s>here</a> for update posts taxonomy information', 'ceceppaml'),
                     'a href="' . add_query_arg( array( "cml_tax" => 1 ) ) . '" class="button button-primary"' ); ?>
      </p>
    </div>
<?php
  }
}

add_action( 'admin_notices', 'cml_show_admin_notices' );

?>
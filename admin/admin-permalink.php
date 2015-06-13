<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

function cml_permalink_settings_init() {
  foreach( CMLLanguage::get_no_default() as $lang ) {
    // Add our settings
    add_settings_field(
        'cml_category_slug_' . $lang->id,      	// id
        '<div class="cml-remove"></div>', 	      // setting title
        'cml_category_slug_field',  // display callback
        'permalink',                 				// settings page
        'optional',                  				// settings section
        array( "lang" => $lang->id )
    );
  }
}

function cml_permalink_settings_save() {
  if ( isset( $_POST['permalink_structure'] ) ||
       isset( $_POST['category_base'] ) &&
       isset( $_POST['product_permalink'] ) ) {

    $permalinks = get_option( "cml_category_slugs", array() );

    foreach( CMLLanguage::get_all() as $lang ) {
      if( CMLLanguage::is_default( $lang ) ) {
        $category = sanitize_title( $_POST[ 'category_base' ] );

        if( empty( $category ) ) $category = 'category';
      } else {
        $category = sanitize_title( $_POST[ 'cml_category_slug_' . $lang->id ] );
      }

      $permalinks[ $lang->id ] = untrailingslashit( $category );
    }

    update_option( "cml_category_slugs", $permalinks );
  }
}

function cml_category_slug_field( $args ) {
  $lang = $args[ 'lang' ];

  $permalinks = get_option( "cml_category_slugs", array() );

  if( empty( $permalinks ) ) {
    $permalinks = get_option( 'category_base' );
  } else {
    $permalinks = $permalinks[ $args[ 'lang' ] ];
  }

?>
  <div class="cml_category_slug cml-hidden">
    <?php echo CMLLanguage::get_flag_img( $lang ) ?>
    <input name="cml_category_slug_<?php echo $lang ?>" type="text" class="regular-text code" value="<?php if ( isset( $permalinks ) ) echo esc_attr( $permalinks ); ?>" placeholder="<?php echo _x('category', 'slug', 'ceceppaml') ?>" />
  </div>
<?php
}

add_action( 'admin_init', 'cml_permalink_settings_init' );
add_action( 'admin_init', 'cml_permalink_settings_save' );

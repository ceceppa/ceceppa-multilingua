<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

$lid = get_option( "cml_theme_language_" . sanitize_title(  wp_get_theme()->name ), 0 );

$generated = json_decode( @$_GET[ 'generated' ] );
$errors = intval( @$_GET[ 'error' ] );

if( ! empty( $generated ) ) {
  $msg = __( 'Translations generated succesfully', 'ceceppaml' );

echo <<< EOT
  <div class="updated">
    <p>
      $msg
    </p>
  </div>
EOT;
}

$path = trim( $GLOBALS[ '_cml_theme_locale_path' ] );
if( empty( $path ) ) {
  $path = trailingslashit( get_template_directory() );
}

if( ! empty( $errors ) ) {
  $error = sprintf( __( 'Error generating translations, ensure that the folder %s is writable', 'ceceppaml' ),
                      $path );
echo <<< EOT
  <div class="error">
    <p>
      $error
    </p>
  </div>
EOT;
}


$path = trailingslashit( get_template_directory() );
$parser = new CMLParser( wp_get_theme()->name, get_template_directory(), $path );
?>
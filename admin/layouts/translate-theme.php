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

if( ! empty( $errors ) ) {
  $error = sprintf( __( 'Error generating translations, ensure that the folder %s is writable', 'ceceppaml' ),
                      $GLOBALS[ '_cml_theme_locale_path' ] );
echo <<< EOT
  <div class="error">
    <p>
      $error
    </p>
  </div>
EOT;
}

$parser = new CMLParser( wp_get_theme()->name, get_template_directory(), $GLOBALS[ '_cml_theme_locale_path' ] );
?>
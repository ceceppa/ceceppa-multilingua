<?php
/*
 * Google XML Sitemap
 *
 * http://wordpress.org/plugins/google-sitemap-generator/
 */
CMLUtils::_append( "_seo", array(
                                'pagenow' => "options-general.php",
                                'page' => "google-sitemap-generator/sitemap.php",
                               )
                );

/*
 * Yoast
 *
 * https://yoast.com/wordpress/
 */
function cml_yoast_seo_strings( $types ) {
  if( defined( 'WPSEO_VERSION' ) ) {
    $options = get_wpseo_options();
    foreach ( $options as $key => $opt ) {
      if( strpos( $key, "title-" ) !== false ||
         strpos( $key, "metadesc-" ) !== false ) {
        /*
         * add strings to my table if they doesn't exists
         */
        CMLTranslations::add( "_$key", $opt, "_YOAST" );
      }
    }
    
    CMLTranslations::add( "_rssafter", $options[ 'rssafter' ], "_YOAST" );

    $types[ "_YOAST" ] = "YOAST";
  }
  
  return $types;
}

/*
 * translate yoast settings
 */
function cml_yoast_translate_options() {
  global $wpseo_front;

  if( is_admin() || CMLUtils::_get( "_real_language" ) == CMLLanguage::get_default_id() ) {
    return;
  }

  foreach( get_wpseo_options() as $key => $opt ) {
    if( strpos( $key, "title-" ) !== false ||
        "rssafter" == $key ) {
      /*
       * add strings to my table if they doesn't exists
       */
      $wpseo_front->options[ $key ] = CMLTranslations::get( CMLLanguage::get_current_id(),
                                                           "_$key",
                                                           "_YOAST" );
    }
  }
}

add_filter( 'cml_my_translations', 'cml_yoast_seo_strings' );
add_action( 'wp_loaded', 'cml_yoast_translate_options' );
?>

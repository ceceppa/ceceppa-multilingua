<?php
/*
 * Google XML Sitemap
 *
 * http://wordpress.org/plugins/google-sitemap-generator/
 */
add_filter( 'cml_translate_home_url', 'cml_yoast_translate_home_url', 10, 2 );
CMLUtils::_append( "_seo", array(
                                'pagenow' => "options-general.php",
                                'page' => "google-sitemap-generator/sitemap.php",
                               )
                );

class CML_WPML_Parser {
  protected $values;
  protected $group = null;
  protected $options = null;

  function __construct( $filename, $group, $options ) {
    $xml = file_get_contents( $filename );

    $parser = xml_parser_create();
    xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
    xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
    xml_parse_into_struct( $parser, $xml, $this->values );
    xml_parser_free( $parser );
    
    $this->options = $options;
    $this->group = $group;
    $this->parse();
    
  }
  
  function parse() {
    $add_text = false;
    $key = null;

    /*
     * for now I check only for "admin-texts"
     */
    foreach( $this->values as $value ) {
      if( $add_text && 'close' !== $value[ 'tag' ]  ) {
        if( null == $key && "key" == $value[ 'tag' ] ) {
          $key = $value[ 'attributes' ][ 'name' ];
        } else {
          if( isset( $value[ 'attributes' ] ) ) {
            $name = $value[ 'attributes' ][ 'name' ];
            
            if( isset( $this->options[ $name ] ) ) {
              $v = $this->options[ $name ];
            } else {
              $v = "";
            }
            
            if( ! empty( $v ) ) {
              CMLTranslations::add( strtolower( $this->group ) . "_" . $name,
                                    $v,
                                    $this->group );
              
              $this->names[] = $name;
            }
          }
        }
      }

      if( "admin-texts" == $value[ 'tag' ] ) {
        if( 'open' == $value[ 'type' ] ) {
          $add_text = true;
        }

        //Done
        if( 'close' == $value[ 'type' ] ) {
          $add_text = false;
          break;
        }
      }
    }

    update_option( "cml_translated_fields" . strtolower( $this->group ), join( ",", $this->names ) );
  }
}

/*
 * Yoast
 *
 * https://yoast.com/wordpress/
 */
function cml_yoast_seo_strings( $types ) {
  if( defined( 'WPSEO_VERSION' ) ) {
    //CMLTranslations::delete( "_YOAST" );
    $options = get_wpseo_options();

    $xml = WPSEO_PATH . "wpml-config.xml";
    new CML_WPML_Parser( $xml, "_YOAST", $options );

    $types[ "_YOAST" ] = "YOAST";
  }
  
  return $types;
}

/*
 * translate yoast settings
 */
function cml_yoast_translate_options() {
  global $wpseo_front;

  if( ! defined( 'WPSEO_VERSION' ) || is_admin() ) return;

  if( is_admin() ) { //|| CMLUtils::_get( "_real_language" ) == CMLLanguage::get_default_id() ) {
    return;
  }

  $names = get_option( "cml_translated_fields_yoast", array() );
  if( empty( $names ) ) return;

  $name = explode( ",", $names );
  foreach( get_wpseo_options() as $key => $opt ) {
    if( in_array( $key, $names ) ) {
      $value = CMLTranslations::get( CMLLanguage::get_current_id(),
                                                           "_yoast_$key",
                                                           "_YOAST" );

      if( empty( $value ) ) continue;

      $wpseo_front->options[ $key ] = $value;
    }
  }
}

/**
 * I don't have to translate home_url for *.xml and *.xsl
 */
function cml_yoast_translate_home_url( $translate, $url ) {
  if( defined( 'WPSEO_VERSION' ) && preg_match( "/.*xsl|.*xml/", $url ) ) {
    return false;
  }
  
  //Nothing to do
  remove_filter( 'cml_yoast_translate_home_url', 10, 2 );

  return $translate;
}

add_filter( 'cml_my_translations', 'cml_yoast_seo_strings' );
add_action( 'wp_loaded', 'cml_yoast_translate_options' );
add_filter( 'cml_translate_home_url', 'cml_yoast_translate_home_url', 10, 2 );

/*
 * All in one seo
 *
 * https://wordpress.org/plugins/all-in-one-seo-pack/
 */
function cml_aioseo_strings( $groups ) {
  //Nothing to do  
  if( ! defined( 'AIOSEOP_VERSION' ) ) return $groups;

  global $aioseop_options;

  $xml = AIOSEOP_PLUGIN_DIR . "wpml-config.xml";
  new CML_WPML_Parser( $xml, "_AIOSEO", $aioseop_options );

  $groups[ "_AIOSEO" ] = "All in one SEO";
  
  return $groups;
}

function cml_aioseo_translate_options() {
  //Nothing to do  
  if( ! defined( 'AIOSEOP_VERSION' ) || is_admin() ) return;

  global $aioseop_options;
  
  $names = get_option( "cml_translated_fields_aioseo", array() );
  if( empty( $names ) ) return;

  $names = explode( ",", $names );

  foreach( $aioseop_options as $key => $opt ) {
    if( in_array( $key, $names ) ) {
      $value = CMLTranslations::get( CMLLanguage::get_current_id(),
                                                           "_aioseo_$key",
                                                           "_AIOSEO",
                                                           true);
      
      if( empty( $value ) ) return;
      
      $aioseop_options[ $key ] = $value;
    }
  }
}

function cml_aioseo_translate_home_url( $translate, $url ) {
  if( defined( 'AIOSEOP_VERSION' ) && preg_match( "/.*xsl|.*xml/", $url ) ) {
    return false;
  }

  //Nothing to do
  remove_filter( 'cml_aioseo_translate_home_url', 10, 2 );

  return $translate;
}

add_filter( 'cml_my_translations', 'cml_aioseo_strings' );
add_action( 'wp_loaded', 'cml_aioseo_translate_options' );
add_filter( 'cml_translate_home_url', 'cml_aioseo_translate_home_url', 10, 2 );

?>

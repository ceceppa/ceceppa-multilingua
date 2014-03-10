<?php
/*
 * simple wpml-config.xml parser
 *
 * this class extract:
 *
 *  *) admin-texts to allow user to translate them in "My translations" page
 *  *) language-switcher-settings to extract "Combo" style :)
 * 
 */
class CML_WPML_Parser {
  protected $values;
  protected $group = null;
  protected $options = null;

  function __construct( $filename, $group, $options = null, $generate_style = false ) {
    $xml = file_get_contents( $filename );

    $parser = xml_parser_create();
    xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
    xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
    xml_parse_into_struct( $parser, $xml, $this->values );
    xml_parser_free( $parser );
    
    $this->options = $options;
    $this->group = $group;
    $this->style = $generate_style;

    $this->parse();    
  }
  
  function parse() {
    $add_text = false;
    $key = null;
    $style = array();
    $is_switcher_style = false;

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
            
            if( is_array( $this->options ) ) {
              if( isset( $this->options[ $name ] ) ) {
                $v = $this->options[ $name ];
              } else {
                $v = "";
              }
              
              $add = ! empty( $v );
            } else {
              $v = get_option( $name );
              
              $add = true;
            }
            
            if( $add ) {
              CMLTranslations::add( strtolower( $this->group ) . "_" . $name,
                                    $v,
                                    $this->group );
              
              $this->names[] = $name;
            }
          }
        }
      }

      /* translable strings */
      if( "admin-texts" == $value[ 'tag' ] ) {
        if( 'open' == $value[ 'type' ] ) {
          $add_text = true;
        }

        //Done
        if( 'close' == $value[ 'type' ] ) {
          $add_text = false;
        }
      }

      /* language switcher */
      if( $this->style && $is_switcher_style ) {
        if( isset( $value[ 'value' ] ) ) {
          $v = $value[ 'value' ];
  
          switch( $value[ 'attributes' ][ 'name' ] ) {
          case 'font-current-normal':
            $style[] = "#cml-lang > li > a { color: $v; } ";
            break;
          case 'font-current-hover':
            $style[] = "#cml-lang > li > a:hover { color: $v; } ";
            break;
          case 'background-current-normal':
            $style[] = "#cml-lang > li > a { background-color: $v; } ";
            break;
          case 'background-current-hover':
            $style[] = "#cml-lang > li > a:hover { background-color: $v; } ";
            break;
          case 'font-other-normal':
            $style[] = "#cml-lang > li > ul a { color: $v; } ";
            break;
          case 'font-other-hover':
            $style[] = "#cml-lang > li > ul a:hover { color: $v; } ";
            break;
          case 'background-other-normal':
            $style[] = "#cml-lang > li > ul li { background-color: $v; } ";
            break;
          case 'background-other-hover':
            $style[] = "#cml-lang > li > ul li:hover { background-color: $v; } ";
            break;
          case 'border':
            $style[] = "#cml-lang { border-color: $v; } ";
            break;
          }
        }
      }

      if( isset( $value[ 'attributes' ][ 'name' ] ) &&
         "icl_lang_sel_config" == $value[ 'attributes' ][ 'name' ] ) {
        if( 'open' == $value[ 'type' ] ) {
          $is_switcher_style = true;
        }
      }
      
      if( $is_switcher_style ) {
        //Done
        if( 'close' == $value[ 'type' ] ) {
          $is_switcher_style = false;
        }
      }
      
      if( "icl_additional_css" == @$value[ 'attributes' ][ 'name' ] ) {
        $style[] = str_replace( "#cml-langlang_sel", "#cml-lang", $value[ 'value' ] );
      }
    }

    if( $this->style ) {
      file_put_contents( CML_UPLOAD_DIR . "combo_style.css", join( "\n", $style ) );
  
      if( ! empty( $style ) ) {
        echo '<div class="updated"><p>';
        echo CML_UPLOAD_DIR . "combo_style.css " . __( 'generated from "wpml-config.xml"', 'ceceppaml' );
        echo '</div>';
      }
    }

    update_option( "cml_translated_fields" . strtolower( $this->group ), join( ",", $this->names ) );
  }
}

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

/*
 * Theme file has wpml-config.xml?
 */
function cml_get_strings_from_theme_wpml_config( $groups ) {
  update_option( "cml_theme_use_wpml_config", 0 );

  $theme = wp_get_theme();

  $root = trailingslashit( $theme->theme_root ) . $theme->template;
  $filename = "$root/wpml-config.xml";
  $name = strtolower( $theme->get( 'Name' ) );

  if( file_exists( $filename ) ) {
    new CML_WPML_Parser( $filename, "_$name", null, true );

    update_option( "cml_theme_${name}_use_wpml_config", 1 );
    
    $groups[ "_$name" ] = sprintf( "%s: %s", __( 'Theme' ), $theme->get( 'Name' ) );
  }
  
  return $groups;
}

add_filter( 'cml_my_translations', 'cml_get_strings_from_theme_wpml_config', 99 );
//add_action( 'wp_loaded', 'cml_check_theme_wpml_config', 10 );
?>

<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once CML_PLUGIN_FRONTEND_PATH . "utils.php";

$GLOBALS[ '_cml_supported_plugin' ] = array( 'all-in-one-seo-pack', 'wordpress-seo' );

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

          if( ! is_array( $this->options ) ) {
            $this->options = get_option( $key );
          }
        } else {
          if( isset( $value[ 'attributes' ] ) ) {
            $name = $value[ 'attributes' ][ 'name' ];

//             if( is_array( $this->options ) ) {
              if( isset( $this->options[ $name ] ) ) {
                $v = $this->options[ $name ];
              } else {
                $v = "";
              }

              $add = ! empty( $v );
//             } else {
//               $v = get_option( $name );

//               $add = true;
//             }

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

    if( ! isset( $this->names ) ) {
      $names = "";
    } else {
      $names = join( ",", $this->names );
    }
    update_option( "cml_translated_fields" . strtolower( $this->group ), $names );
    update_option( "cml_translated_fields" . strtolower( $this->group ) . "_key", $key );
  }
}

/**
 * WPML language switcher
 */
if( ! function_exists( 'icl_get_languages' ) ) {
  function icl_get_languages( $params ) {
    parse_str( $params );
    $langs = CMLLanguage::get_all();
    $return = array();

    foreach( $langs as $lang ) {
      $return[$lang->cml_language_slug]['id'] = $lang->id;
      $return[$lang->cml_language_slug]['active'] = $lang->cml_enabled;
      $return[$lang->cml_language_slug]['native_name'] = $lang->cml_language;
      $return[$lang->cml_language_slug]['translated_name'] = $lang->cml_language;
      $return[$lang->cml_language_slug]['missing'] = 0;
      $return[$lang->cml_language_slug]['country_flag_url'] = CMLLanguage::get_flag_src( $lang );
      $return[$lang->cml_language_slug]['url'] = cml_get_the_link( $lang );
    }

    return $return;
  }

}

/**
 * https://wpml.org/documentation/getting-started-guide/language-setup/custom-language-switcher/
 */
if( ! function_exists( 'icl_get_languages' ) ) {
  function icl_get_languages( $params ) {
    //Convert parameters into associative array
    wp_parse_str( $params, $args );

    //array to be returned
    $array = array();

    foreach( CMLLanguage::get_all() as $lang ) {
      $link = cml_get_the_link( $lang, true, $args[ 'skip_missing' ] == 0 );

      if( empty( $link ) && $args[ 'skip_missing' ] == 1 ) continue;

      $data = array(
                    'id' => $lang->id,
                    'active' => $lang->cml_enabled,
                    'native_name' => $lang->cml_language,
                    'missing' => (empty( $link ) ),
                    'translated_name' => $lang->cml_language,
                    'language_code' => $lang->cml_language_slug,
                    'country_flag_url' => CMLLanguage::get_flag_src( $lang ),
                    'url' => link
      );

      $array[ $lang->cml_language_slug ] = $data;
    }

    return $array;
  }
}

/*
 * Scan plugins folders to search "wpml-config.xml"
 */
function cml_admin_scan_plugins_folders() {
  $plugins = WP_CONTENT_DIR . "/plugins";

  $old = get_option( '_cml_wpml_config_paths', "" );

  $xmls = @glob( "$plugins/*/wpml-config.xml" );

  //nothing to do?
  if( empty( $xmls ) ) {
      return;
  }

  $link = esc_url( add_query_arg( array( "lang" => "ceceppaml-translations-page" ), admin_url() ) );
  $txt  = __( "Current plugins contains WPML Language Configuration Files ( wpml-config.xml )", 'ceceppaml' );
  $txt .= '<br /><ul class="cml-ul-list">';

  $not = array();

  foreach( $xmls as $file ) {
      $path = str_replace( WP_CONTENT_DIR . "/plugins/", "", dirname( $file ) );
      $supported = ( in_array( $path, $GLOBALS[ '_cml_supported_plugin' ] ) ) ? " (" . __( 'officially supported', 'ceceppaml' ) . ")" : "";
      $txt .= "<li>$path<i>$supported</i></li>";

      //not officially supported...
      if( empty( $supported ) ) {
          $not[] = dirname( $file );
      }
  }

  $not = join( ",", $not );
  update_option( '_cml_wpml_config_paths', $not );

  $displayed = get_option( '_cml_wpml_config', 1 );
  if( ! $displayed && $not == $old ) {
    return;
  } else {
    delete_option( '_cml_wpml_config' );
  }

  $txt .= "</ul>";
  $txt .= sprintf( __( "Now you can translate Admin texts / wp_options in <%s>\"My Translations\"</a> page", "ceceppaml" ),
          'a href="' . $link . '"' );
  $txt .= "<br /><b>";
  $txt .= __( "Support to wpml-config.xml is experimental and could not works correctly", "ceceppaml" );
  $txt .= "<br /><b>";

  cml_admin_print_notice( "_cml_wpml_config", $txt );
}

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
    //CMLTranslations::delete( "_YOAST" );
    $options = WPSEO_Options::get_all();

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

  $names = get_option( "cml_translated_fields_yoast", '' );
  // if( empty( $names ) ) return;

  if( ! class_exists('WPSEO_Frontend') ||
      ! method_exists(WPSEO_Frontend, 'get_instance' ) ) return;

  $seo = WPSEO_Frontend::get_instance();
  $names = explode( ",", $names );
  foreach( WPSEO_Options::get_all() as $key => $opt ) {
    if( in_array( $key, $names ) ) {
      $value = CMLTranslations::get( CMLLanguage::get_current_id(),
                                                           "_yoast_$key",
                                                           "_YOAST" );

      if( empty( $value ) ) continue;

      if( ! empty( $wpseo_front ) )
        $wpseo_front->options[ $key ] = $value;
      else
        $seo->options[$key] = $value;
    }
  }
}

/**
 * I don't have to translate home_url for *.xml and *.xsl
 */
function cml_yoast_translate_home_url( $translate, $url ) {
  if( defined( 'WPSEO_VERSION' ) && preg_match( "/.*xsl|.*xml/", $url ) ) {
    CMLUtils::_set( '_is_sitemap', 1 );

    return false;
  }

  //Nothing to do
  remove_filter( 'cml_yoast_translate_home_url', 10, 2 );

  return $translate;
}

function cml_yoast_message() {
  if( ! defined( 'WPSEO_VERSION' ) ) return;
  if( ! isset( $_GET[ 'page' ] ) ||
     'wpseo_titles' != $_GET[ 'page' ] ) return;

  $txt = sprintf( __( "Go to <%s>My Translations</a> page to translate \"Titles & Metadata\"", 'ceceppaml' ),
                  'a href="' . admin_url() . 'admin.php?page=ceceppaml-translations-page&stab=aioseo" class="button"' );

  cml_admin_print_notice( "_cml_aioseo_msg", $txt );
}

add_filter( 'cml_my_translations', 'cml_yoast_seo_strings' );
add_action( 'wp_loaded', 'cml_yoast_translate_options' );
add_filter( 'cml_translate_home_url', 'cml_yoast_translate_home_url', 10, 2 );
add_action( 'admin_notices', 'cml_yoast_message' );


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
    CMLUtils::_set( '_is_sitemap', 1 );

    return false;
  }

  //Nothing to do
  remove_filter( 'cml_aioseo_translate_home_url', 10, 2 );

  return $translate;
}

function cml_aioseo_message() {
  global $pagenow;

  if( ! defined( 'AIOSEOP_VERSION' ) ) return;
  if( ! isset( $_GET[ 'page' ] ) ||
     'all-in-one-seo-pack/aioseop_class.php' != $_GET[ 'page' ] ) return;

  $txt = sprintf( __( "Go to <%s>My Translations</a> page to translate \"Title Settings\"", 'ceceppaml' ),
                  'a href="' . admin_url() . 'admin.php?page=ceceppaml-translations-page&stab=aioseo" class="button"' );

  cml_admin_print_notice( "_cml_aioseo_msg", $txt );
}

add_filter( 'cml_my_translations', 'cml_aioseo_strings' );
add_action( 'wp_loaded', 'cml_aioseo_translate_options' );
add_filter( 'cml_translate_home_url', 'cml_aioseo_translate_home_url', 10, 2 );
add_action( 'admin_notices', 'cml_aioseo_message' );

/*
 * Theme contains wpml-config.xml?
 */
function cml_get_strings_from_wpml_config( $groups ) {
  if( ! is_admin() ) return;

  update_option( "cml_theme_use_wpml_config", 0 );

  $theme = wp_get_theme();

  $root = trailingslashit( $theme->theme_root ) . $theme->template;
  $filename = "$root/wpml-config.xml";
  $name = strtolower( $theme->get( 'Name' ) );

  if( file_exists( $filename ) ) {
    new CML_WPML_Parser( $filename, "_$name", null, true );

    echo '<div class="updated"><p>';
    echo __( 'Your theme is designed for WPML', 'ceceppaml' ) . '<br />';
    _e( 'Support for theme compatible with WPML is experimental and could not works correctly, if you need help contact me.', 'ceceppaml' );
    echo '</p></div>';

    update_option( "cml_theme_${name}_use_wpml_config", 1 );

    $groups[ "_$name" ] = sprintf( "%s: %s", __( 'Theme' ), $theme->get( 'Name' ) );
  }

  //Look for unsupported plugins
  $plugins = get_option( '_cml_wpml_config_paths', "" );
  if( empty( $plugins ) ) return $groups;

  $plugins = explode( ",", $plugins );
  foreach( $plugins as $plugin ) {
    $path = str_replace( WP_CONTENT_DIR . "/plugins/", "", $plugin);

    new CML_WPML_Parser( "$plugin/wpml-config.xml", "_$path", null );

    $groups[ "_$path"] = sprintf( "%s: %s", __( 'Plugin' ), $path );
  }

  return $groups;
}

/*
 * current theme has wpml-config.xml?
 */
function cml_translate_wpml_strings() {
  if( is_admin() ) return;

  $theme = wp_get_theme();
  $name = strtolower( $theme->get( 'Name' ) );

  CMLUtils::_set( '_theme_group', strtolower( $theme ) );
  CMLUtils::_set( "theme-name", $name );

  if( get_option( "cml_theme_${name}_use_wpml_config", 0 ) ) {
    add_filter( "option_{$theme}_options", 'cml_translate_theme_strings', 0, 1 );

    //Old method
    cml_change_wpml_settings_values( strtolower( $theme ), $name );
  }

  //Not officially supported plugin
  $plugins = get_option( '_cml_wpml_config_paths', "" );
  $plugins = explode( ",", $plugins );
  foreach( $plugins as $plugin ) {
    $path = str_replace( WP_CONTENT_DIR . "/plugins/", "", $plugin);

    cml_change_wpml_settings_values( $path, $path );
  }
}

function cml_change_wpml_settings_values( $group, $name ) {
  $names = get_option( "cml_translated_fields_{$name}", array() );
  if( empty( $names ) ) return;

  $options_key = get_option( "cml_translated_fields_{$name}_key", "" );
  if( empty( $options_key ) ) {
    return;
  }

  //Overwrite the settings?
  $options = & $GLOBALS[ $options_key ];
  if( ! is_array( $options ) ) return;

  $names = explode( "/", $names );
  foreach( $options as $key => $value ) {
    if( ! in_array( $key, $names ) ) continue;

    $v = CMLTranslations::get( CMLLanguage::get_current_id(),
                              "_{$group}_{$key}",
                              "_{$group}" );

    if( empty( $v ) ) continue;

    $options[ $key ] = $v;
  }
}

function cml_translate_theme_strings( $values ) {
  $group = CMLUtils::_get( '_theme_group' );
  $name = CMLUtils::_get( "theme-name" );

  $names = get_option( "cml_translated_fields_{$name}", array() );
  if( empty( $names ) ) return $values;

  $names = explode( ',', $names );
  foreach( $values as $key => $value ) {
    if( ! in_array( $key, $names ) ) continue;

    $v = CMLTranslations::get( CMLLanguage::get_current_id(),
                              "_{$group}_{$key}",
                              "_{$group}" );
    if( empty( $v ) ) continue;

    $values[ $key ] = $v;
  }

  return $values;
}

add_filter( 'cml_my_translations', 'cml_get_strings_from_wpml_config', 99 );
// add_action( 'wp_loaded', 'cml_translate_wpml_strings', 10 );
// add_action( 'plugins_loaded', 'cml_translate_wpml_strings', 1 );
cml_translate_wpml_strings();

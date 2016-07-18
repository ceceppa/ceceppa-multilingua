<?php
/*
Plugin Name: Ceceppa Multilingua
Plugin URI: http://www.alessandrosenese.eu/portfolio/ceceppa-multilingua
Description: Adds userfriendly multilingual content management and translation support into WordPress.
Version: 1.5.17
Author: Alessandro Senese aka Ceceppa
Author URI: http://www.alessandrosenese.eu/
License: GPL3
Tags: multilingual, multi, language, admin, tinymce, qTranslate, Polyglot, bilingual, widget, switcher, professional, human, translation, service, multilingua
*/
/**
 * Ceceppa Multilanguage Blog :)
 *
 * Most of flags are downloaded from http://blog.worldofemotions.com/danilka/
 *
 */
/*  Copyright 2013  Alessandro Senese (email : senesealessandro@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Make sure we don't expose any info if called directly
if ( ! defined( 'ABSPATH' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

global $wpdb;

define( 'CECEPPA_DB_VERSION', 34 );

define( 'CECEPPA_ML_TABLE', $wpdb->base_prefix . 'ceceppa_ml' );
define( 'CECEPPA_ML_CATS', $wpdb->base_prefix . 'ceceppa_ml_cats' );
define( 'CECEPPA_ML_POSTS', $wpdb->base_prefix . 'ceceppa_ml_posts' );
define( 'CECEPPA_ML_RELATIONS', $wpdb->base_prefix . 'ceceppa_ml_relations');

/*
 * From 1.4 the plugin will store translation in .mo file if PHP >= 5.2.4, othwerise
 * store strings into db
 */
define('CECEPPA_ML_TRANSLATIONS', $wpdb->base_prefix . 'ceceppa_ml_trans');

/* Url modification mode */
define( 'PRE_NONE', 0 );
define( 'PRE_LANG', 1 );
define( 'PRE_PATH', 2 );
define( 'PRE_DOMAIN', 3 );

/* Filter posts mode */
define( 'FILTER_BY_LANGUAGE', 1 );
define( 'FILTER_HIDE_TRANSLATION', 2 );
define( 'FILTER_HIDE_EMPTY', 3 );
define( 'FILTER_NONE', 4 ); //Do not filter wordpress query, useful for one page themes

/* Widget visibility actions */
define( 'CML_WIDGET_SHOW', 'show' );
define( 'CML_WIDGET_HIDE', 'hide' );

/*
 * Plugin path & url
 */
define( 'CML_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
//define( 'CML_PLUGIN_CACHE_URL', CML_PLUGIN_URL . trailingslashit ( 'cache' ) );
define( 'CML_PLUGIN_FLAGS_URL', CML_PLUGIN_URL . trailingslashit ( 'flags' ) );
define( 'CML_PLUGIN_IMAGES_URL', CML_PLUGIN_URL . trailingslashit ( 'images' ) );
define( 'CML_PLUGIN_JS_URL', CML_PLUGIN_URL . trailingslashit( 'js' ) );
define( 'CML_PLUGIN_DOC_URL', CML_PLUGIN_URL . trailingslashit( 'doc' ) );

define( 'CML_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
//define( 'CML_PLUGIN_CACHE_PATH', CML_PLUGIN_PATH . trailingslashit( 'cache' ) );
define( 'CML_PLUGIN_LANGUAGES_PATH', CML_PLUGIN_PATH . trailingslashit( 'langs' ) );
define( 'CML_PLUGIN_FLAGS_PATH', CML_PLUGIN_PATH . trailingslashit ( 'flags' ) );
define( 'CML_PLUGIN_ADMIN_PATH', CML_PLUGIN_PATH . trailingslashit ( 'admin' ) );
define( 'CML_PLUGIN_LAYOUTS_PATH', CML_PLUGIN_ADMIN_PATH . trailingslashit ( 'layouts' ) );
define( 'CML_PLUGIN_INCLUDES_PATH', CML_PLUGIN_PATH . trailingslashit ( 'includes' ) );
define( 'CML_PLUGIN_DOC_PATH', CML_PLUGIN_PATH . trailingslashit ( 'doc' ) );

//frontend
define( 'CML_PLUGIN_FRONTEND_PATH', CML_PLUGIN_PATH . trailingslashit ( 'frontend' ) );

/*
 * Wordpress languages directory
 */
define( 'CECEPPA_WP_LANGUAGES', WP_CONTENT_DIR . "/languages" );

/*
 * Upload directory.
 * The plugin use it for store custom flags
 */
$upload_dir = wp_upload_dir();
$baseurl = $upload_dir[ 'baseurl' ];
if( is_ssl() ) {
    $baseurl = str_replace( 'http://', "https://", $baseurl);
}

define( 'CML_UPLOAD_DIR', trailingslashit( $upload_dir[ 'basedir' ] ) . trailingslashit( "ceceppaml" ) );
define( 'CML_UPLOAD_URL', trailingslashit( $baseurl ) . trailingslashit ( "ceceppaml" ) );

//Cache
define( 'CML_PLUGIN_CACHE_PATH', CML_UPLOAD_DIR . trailingslashit( 'cache' ) );
define( 'CML_PLUGIN_CACHE_URL', CML_UPLOAD_URL . trailingslashit ( 'cache' ) );
//define( 'CML_PLUGIN_PUBLIC_CACHE_URL', CML_UPLOAD_URL . trailingslashit ( 'cache' ) );

//WP locale dir
define( 'CML_WP_LOCALE_DIR', WP_CONTENT_DIR . "/languages" );

//Flag size
define( 'CML_FLAG_SMALL', 'small' );
define( 'CML_FLAG_TINY', 'tiny' );

//Php < 5.3.0 compatibility
require_once( CML_PLUGIN_INCLUDES_PATH . 'php-compatibility.php' );

/*
 * Settings
 */
if( ! isset( $_GET[ 'cml-settings-updated' ] ) &&
   get_option( "cml_use_settings_gen", 0 ) &&
   file_exists( CML_UPLOAD_DIR . "settings.gen.php" ) ) {
  define( '_CML_SETTINGS_PHP', CML_UPLOAD_DIR . "settings.gen.php" );
} else {
  define( '_CML_SETTINGS_PHP', CML_PLUGIN_PATH . "settings.php" );
}
require_once( _CML_SETTINGS_PHP );

//Translations from PO?
define( 'CML_GET_TRANSLATIONS_FROM_PO', get_option( 'cml_get_translation_from_po', 0 ) );

/*
 * API
 */
require_once ( CML_PLUGIN_INCLUDES_PATH . "api.php" );
require_once ( CML_PLUGIN_INCLUDES_PATH . "api.old.php" );

/*
 *Functions
 */
require_once( CML_PLUGIN_INCLUDES_PATH . "functions.php" );

/*
 * cml widgets
 */
require_once CML_PLUGIN_INCLUDES_PATH . "widgets.php";

//debug
 if( file_exists( CML_PLUGIN_PATH . "debug.php" ) &&
     1 == get_option( "cml_debug_enabled" ) ) {
   define( 'CML_DEBUG', 1 );

 require_once( "debug.php" );
 }

//3rd party compatibility
require_once( CML_PLUGIN_INCLUDES_PATH . 'compatibility.php' );

/*
 *
 * Ceceppa Multilingua "core" class
 *
 * In this class exists all method required for admin and fronted.
 */
class CeceppaML {
  protected $_url_mode = null;
  protected $_url = null;
  protected $_homeUrl = null;
  protected $_base_url = null;
  protected $_request_url = null;
  protected $_permalink_structure = null;
  protected $_category_url_mode = null;

  public function __construct() {
    global $_cml_settings;

      //Db
    $GLOBALS[ 'cml_db_version' ] = get_option( 'cml_db_version', CECEPPA_DB_VERSION );

    $http = ( ! is_ssl() ) ? "http://" : "https://";
    $this->_url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $this->_homeUrl = home_url() . "/";
    $this->_base_url = str_replace( $http . $_SERVER['HTTP_HOST'], "", get_option( 'home' ) );
    $this->_request_url = str_replace($this->_homeUrl, "", $this->_url);
    $this->_permalink_structure = get_option( "permalink_structure" );

    /* I can't use PRE_PATH with default permalink structure ( ?p=## ) */
    $this->_url_mode = CMLUtils::get_url_mode();

    //Activate?
    register_activation_hook( __FILE__, array( & $this, 'activated' ) );

    //Initialize the plugin
    add_action( 'init', array( &$this, 'init' ), 0 );
    add_filter( 'plugin_locale', array( & $this, 'plugin_locale' ), 10, 2 );

    //Scripts & Styles
    add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );
    add_action( 'admin_enqueue_scripts', array( &$this, 'register_scripts' ) );

    /*
     * I need to force category language, becase I need category in
     * post language, not current one
     */
    add_filter( 'pre_post_link', array( & $this, 'pre_post_link' ), 0, 3 );
    add_filter( 'post_link', array( & $this, 'translate_post_link' ), 0, 3 );
    add_filter( 'post_type_link', array( & $this, 'translate_post_link' ), 0, 3 );

    if( $this->_url_mode > PRE_LANG ) {
//      add_filter( 'post_type_link', array( & $this, 'translate_page_link' ), 0, 3 );
      add_filter( 'page_link', array ( & $this, 'translate_page_link' ), 0, 3 );
    }

//    if( CMLUtils::get_permalink_structure() != '' ) {
////      add_filter( '_get_page_link', array ( & $this, 'translate__page_link' ), 0, 2 );
//    }

    //Switch language in menu
    add_action( 'admin_bar_menu', array( & $this, 'add_bar_menu' ), 1000 );

    //Category doesn't works correctly with "none" of "Url Modification mode"
    $this->_category_url_mode = $this->_url_mode;
    if( $this->_category_url_mode == PRE_NONE ||
       ( ! $_cml_settings[ 'cml_option_translate_category_url' ] && $this->_category_url_mode != PRE_PATH ) ) {
      $this->_category_url_mode = PRE_LANG;
    }

		$this->_translate_category_slug = @$_cml_settings[ 'cml_option_translate_category_slug' ];

		//Custom post slug translation
    /* REWRITE RULES */
    add_action( 'init', array( & $this, 'rewrite_rules' ), 99 );

    CMLUtils::_set( 'cml_category_mode', $this->_category_url_mode );
  }

  /*
   * yeah, the plugin is activaed :)
   */
  function activated() {
    require_once ( CML_PLUGIN_ADMIN_PATH . "install.php" );

    cml_do_install();
  }

  /*
   * initialize the plugin
   */
  function init() {
    global $_cml_settings;

    //Languages
    load_plugin_textdomain( 'ceceppaml', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

    //Register menus
    foreach( CMLLanguage::get_all() as $result) {
      register_nav_menus( array( "cml_menu_$result->cml_language_slug" => $result->cml_language ) );
    }

    if( CML_GET_TRANSLATIONS_FROM_PO ) {
      CMLUtils::_set( '_po_loaded', load_plugin_textdomain( 'cmltrans', false, dirname( plugin_basename( __FILE__ ) ) . '/cache/' ) );
    }
  }

  /*
   * change cmltrans locale
   */
  function plugin_locale( $locale, $domain ) {
    if( 'cmltrans' == $domain ) {
      return CMLLanguage::get_default_locale();
    }

    return $locale;
  }

  /*
   * script required by frontend
   */
  function register_scripts() {
    wp_enqueue_script( 'ceceppaml-script', CML_PLUGIN_JS_URL . 'ceceppaml.js', array( 'jquery' ), true );

    //Language information
    wp_localize_script( 'ceceppaml-script', 'ceceppa_ml', array(
                                                                  'id' => CMLLanguage::get_current_id(),
                                                                  'lang' => json_encode( ( array ) CMLLanguage::get_current() ),
                                                                  'slug' => CMLLanguage::get_current_slug(),
                                                                  'clear' => 1,
                                                                  ) );

    wp_enqueue_style( 'ceceppaml-style', CML_PLUGIN_URL . 'css/ceceppaml.css' );

    if( file_exists( CML_PLUGIN_CACHE_PATH . "cml_flags.css" ) )
        wp_enqueue_style( 'ceceppaml-flags', CML_PLUGIN_CACHE_URL . "cml_flags.css" );

    //user custom style
    if( file_exists( CML_UPLOAD_DIR . "ceceppaml.css" ) )
        wp_enqueue_style( 'ceceppaml-custom-style', CML_UPLOAD_URL . "ceceppaml.css" );
  }

  function add_bar_menu() {
    $this->_add_bar_menu_item( cml_get_current_language(), "cml_lang_sel" );

    $langs = cml_get_languages( false );
    unset( $langs[ cml_get_current_language_id() ] );

    foreach( $langs as $lang ) {
      $this->_add_bar_menu_item( $lang, 'cml_lang_sel' . $lang->id, "cml_lang_sel" );
    }

    if( current_user_can( 'manage_options' ) ) {
      global $wp_admin_bar;

      $url = add_query_arg( array(
                                  "page" => "ceceppaml-language-page",
                                  ),
                           admin_url() . "admin.php" );

      $wp_admin_bar->add_menu( array(
                                     'id' => "cml_manage_lang",
                                     'title' => __( 'Manage languages', 'ceceppaml' ),
                                     'href' => esc_url( $url ),
                                     'parent' => "cml_lang_sel",
                                     )
                              );
    }
  }

  function _add_bar_menu_item( $lang, $id, $parent = null ) {
    global $wp_admin_bar;

    $img = CMLLanguage::get_flag_img( $lang->id );

    //show &bull; near default language
    $bull = ( $lang->id == CMLLanguage::get_default_id() ) ? "&#10084;" : "";
$content = <<< EOT
      $img
      $lang->cml_language
      <span class="cml-default">$bull</span>
EOT;
    $url = ( is_admin() ) ? add_query_arg( array( "lang" => $lang->cml_language_slug ) ) :
                            cml_get_the_link( $lang, true, false, true );

    $wp_admin_bar->add_menu( array( 'id' => $id,
                                     'title' => $content, 'href' => esc_url( $url ),
                                     'parent' => $parent ) );
  }

  function pre_post_link( $permalink, $post = null, $leavename = null ) {
    if( is_preview() || null == $post ) {
      return $permalink;
    }

	//Force "get_term" to return translation of category
    if( null == CMLUtils::_get( "_forced_language_slug" ) ) {
      $lang_id =  CMLPost::get_language_id_by_id( $post->ID, true );
      if( $lang_id == 0 ) $lang_id = CMLLanguage::get_current_id();

      $this->_force_category_lang = $lang_id;
			CMLUtils::_set( '_force_post_link', $lang_id );
    } else {
      /*
       * already forced by cml_get_the_link
       */
      //$this->_force_category_lang = CMLLanguage::get_by_slug( $GLOBALS[ '_cml_force_home_slug' ] )->id;
      $this->_force_category_lang = CMLLanguage::get_by_slug( CMLUtils::_get( "_forced_language_slug" ) )->id;
    }

    $this->unset_category_lang();
    unset( $this->_force_post_lang );
    unset( $GLOBALS[ '_cml_force_home_slug' ] );

    return $permalink;
  }

  /*
   * remove "-##" added by wordpress from posts with same title :)
   */
  function translate_post_link( $permalink, $post, $leavename ) {
    global $wpdb, $page;

    if( is_preview() ) {
      return $permalink;
    }

    if( $this->_url_mode == PRE_LANG &&
        $page > 1 &&
        ! empty( $this->_permalink_structure ) &&
        ! isset( $this->_replace_applied ) ) {

      $this->_replace_applied = true;

      /*
       * http://localhost/wp_beta/inglese/?lang=it/
       */
      $permalink = preg_replace( "/\?lang.*/", "", $permalink );

      //Language slug
      $slug = CMLPost::get_language_slug_by_id( $post->ID );

      $url = add_query_arg( array(
                                  "lang" => $slug,
                                  ),
                            "$permalink/$page/" );

			return esc_url( $url );
    }

    if( $this->_url_mode == PRE_LANG ) {
      $permalink = untrailingslashit( $permalink );
    }

//    if( isset( $post->post_name ) && $post->post_type == "page" ) {
    if( $post->post_type != "post" ) {
      $permalink = $this->translate_page_link( $permalink, $post, $leavename );
    }

    $this->unset_category_lang();
    unset( $this->_force_post_lang );
    unset( $GLOBALS[ '_cml_force_home_slug' ] );
		CMLUtils::_del( '_force_post_link' );

    return CMLPost::remove_extra_number( $permalink, $post );
  }

  function translate_page_link( $permalink, $page, $leavename ) {
		global $_cml_settings;

    if( is_preview() ) {
      return $permalink;
    }

    if( is_object( $page ) ) {
      $page_id = $page->ID;
    } else {
      $page_id = $page;

      $page = get_post( $page );
    }

    $lang = CMLLanguage::get_by_post_id( $page_id );

    if( ! is_object( $lang ) ) {
      $lang = CMLLanguage::get_current();
    }

    $this->unset_category_lang();
    unset( $this->_force_post_lang );
    unset( $GLOBALS[ '_cml_force_home_slug' ] );

		//Translate custom post slug, if requested link is for a custom one...
		$slugs = get_option('cml_translated_slugs', array());
		$customs = array_keys( $slugs );

		if( is_singular( $customs ) ) {
			$post_obj = get_queried_object();
			$type = $post_obj->post_type;

			//Translating?
			// $lang_id = ( empty( $lang ) ) ? CMLLanguage::get_default_id() : $lang->id;
			$lang_id = CMLUtils::_get( "_forced_language_id", CMLLanguage::get_current_id() );
			if( isset( $slugs[ $type ] ) && $slugs[ $type ]['enabled'] ) {
				$trans = $slugs[$type][$lang_id];

				if( ! empty( $trans ) ) {
					$permalink = str_replace( "/{$type}/", "/{$trans}/", $permalink );
				}
			}
		}

/*
 *  Commented out to allow WooCommerce link translation properly ( checkout )
*/
    if( ! defined( 'CML_WOOCOMMERCE_PATH' ) ) {
        if( CMLLanguage::is_current( $lang->id ) ) {
          return CMLPost::remove_extra_number( $permalink, $page );
        }
    }

    $slug = ( empty( $lang ) ) ? CMLLanguage::get_current_slug() : $lang->cml_language_slug;
    $permalink = CMLPost::remove_extra_number( $permalink, $page );

		/**
		 * If the page is "unique" I need to get the slug in according to current language,
		 * otherwise the default one will be always used...
		 */
		if( CMLPost::is_unique( $page_id ) ) {
			$slug = CMLUtils::_get( '_forced_language_slug', $slug );
		}

    return $this->convert_url( $permalink, $slug );
  }


  /*
   * translate single category name
   */
  // function translate_term_name( $term_name, $lang_id = null, $post_id = null, $taxonomy = "" ) {
  function get_translated_term( $term, $lang_id = null, $post_id = null, $taxonomy = "" ) {
    if( 1 === CMLUtils::_get( '_no_translate_term' ) ) {
      return $term;
    }

    $term_name = ( is_object( $term ) ) ? $term->name : $term;

    if( isset( $this->_force_post_lang ) ) {
      $lang_id = $this->_force_post_lang;
    }

    if( empty( $lang_id ) ) {
      if( null === $post_id || is_array( $post_id ) ) {
        $lang_id = CMLLanguage::get_current_id();
      } else {
        $lang_id = CMLPost::get_language_id_by_id( $post_id );
      }

      if( isset( $this->_fake_language_id ) &&
          $lang_id > 0 &&
          $lang_id != $this->_fake_language_id ) {
        $this->_force_category_lang = $lang_id;
      }

      if( empty( $lang_id ) &&
          isset( $this->_fake_language_id ) ) {
        $lang_id = $this->_fake_language_id;
      }

      if( ! isset( $this->_fake_language_id )
         && isset( $this->_force_category_lang ) ) {
        $lang_id = $this->_force_category_lang;
      }
    }

    if( null !== CMLUtils::_get( '_force_category_lang' ) ) {
      $lang_id = CMLUtils::_get( '_force_category_lang' );

      unset( $this->_force_category_lang );
      unset( $this->_force_post_lang );
    }

    $lang_id = CMLUtils::_get( '_forced_language_id', $lang_id );

    /*
     * I need to force category language when I retrive category
     * from "cml_get_the_link", because I need category term in post language,
     * not current
     */
    if( CMLLanguage::is_default( $lang_id ) ) {
      //I have not translate "slug" for default language
      CMLUtils::_set( '_no_translate_term', 1 );

      return $term;
    }

    if( isset( $this->_force_category_lang ) &&
        ! isset( $this->_force_post_lang ) ) {
      $lang_id = $this->_force_category_lang;
    }

    if( is_numeric( $lang_id ) && 0 == $lang_id ) {
      $lang_id = CMLLanguage::get_current_id();
    }

    if( is_object( $term ) ) {
      $tterm = CMLTaxonomies::get( $lang_id, $term );

      if( empty( $term ) || ! is_object( $tterm ) )  {
        $tterm = array( 'name' => $term->name, 'slug' => $term->slug, 'description' => $term->description );
        $tterm = ( object ) $tterm;
      }

      return $tterm;
    } else {
      $t_name = strtolower( $taxonomy . "_" . $term_name );
      if( ! CMLLanguage::is_current( $lang_id ) ) {
        //If post language != current language I can't get translation from ".mo"
        $t_name = CMLTranslations::get( $lang_id, $t_name, "C", true, true );
      } else {
        $t_name = CMLTranslations::get( $lang_id, $t_name, "C", true );
      }

      return ( ! empty( $t_name ) ) ? $t_name : $term_name;
    }
  }

  /*
   * change ( wrong? ) language slug in url
   */
  function convert_url( $permalink, $slug ) {
    switch( $this->_url_mode ) {
    case PRE_LANG:
      $url = add_query_arg( array( "lang" => $slug ), $permalink );
			return esc_url( $url );
      break;
    case PRE_PATH:
      $url = CMLUtils::home_url();
      $clean_url = CMLUtils::clear_url( $permalink );

      //Change slug in url instead of append ?lang arg
      $link = str_replace( trailingslashit( $url ), "", $clean_url );

      $home = CMLUtils::get_home_url( $slug );
      return trailingslashit( $home ) . $link;
      break;
    case PRE_DOMAIN:
      if( preg_match( "/^(.*\/\/)([a-z]{2})\./", $permalink, $match ) ) {
        $url = preg_replace( "/^(.*\/\/)([a-z]{2})\./", $match[1] . "$slug.", $permalink );
      } else {
        preg_match( "/^(.*\/\/)/", $permalink, $match );

        $url = preg_replace( "/^(.*\/\/)/", end( $match ) . "$slug.", $permalink );
      }

      return $url;
      break;
    }

    return $permalink;
  }

  function translate_category_url( $url ) {
    $homeUrl = untrailingslashit( $this->_homeUrl );
    $plinks = explode( "/", str_replace( $homeUrl, "", $this->_request_url ) );

    //Se sto nel loop recupero la lingua dall'articolo
    if( in_the_loop() ) {
      $id = CMLLanguage::get_id_by_post_id( get_the_ID() );
      $slug = CMLLanguage::get_slug( $id );
    } else {
      $slug = CMLLanguage::get_slug( CMLLanguage::get_current_id() );
    }

    if( empty( $slug ) ) $slug = CMLLanguage::get_slug( CMLLanguage::get_current_id() );

    return $url;
  }

  /*
   * Questa funzione mi serve per poter passare tra le varie lingue della stessa
   * categoria, perché la funzione get_category_link mi restituisce il link
   * rispetto alla lingua corrente, mentre a me serve il link per una
   * lingua specifica.
   */
  function force_category_lang( $lang ) {
    $this->_force_category_lang = $lang;

    if( isset( $this->_fake_language_id ) )
      $this->_force_category_lang = $this->_fake_language_id;
  }

  function unset_category_lang() {
    unset( $this->_force_category_lang );
  }

  function get_url() {
    return $this->_url;
  }

  /*
	 * Rewrite rules for cpt slug translation
	 */
	function rewrite_rules() {
		$slugs = get_option( "cml_translated_slugs", array() );
		foreach( $slugs as $key => $slug ) {
			if( $slug[ 'enabled' ] == 0 ) continue;

			foreach( CMLLanguage::get_no_default() as $lang ) {
				if( ! isset( $slug[ $lang->id ] ) ) continue;

				$category = $slug[ $lang->id ];

				add_rewrite_rule( $category . '/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$','index.php?' . $key . '=$matches[1]&feed=$matches[2]', 'top' );
				add_rewrite_rule( $category . '/(.+?)/(feed|rdf|rss|rss2|atom)/?$','index.php?' . $key . '=$matches[1]&feed=$matches[2]', 'top' );
				add_rewrite_rule( $category . '/(.+?)/page/?([0-9]{1,})/?$','index.php?' . $key . '=$matches[1]&paged=$matches[2]', 'top' );
				add_rewrite_rule( $category . '/(.+?)/?$','index.php?' . $key . '=$matches[1]', 'top' );
			}
		}

	 CMLUtils::_set( '_rewrite_rules', 1 );
   flush_rewrite_rules();
	 CMLUtils::_del( '_rewrite_rules' );
	}

  /*
   *
   */
  function translate_home_url( $url, $path, $origin_scheme, $blog_id ) {
    if( isset( $GLOBALS[ '_cml_no_translate_home_url' ] )
		   || CMLUtils::_get( '_rewrite_rules' )
			 || CMLUtils::_get( '_rewrite_url' )
       || ! apply_filters( 'cml_translate_home_url', true, $this->_url ) ) {
      return $url;
    }

    if( is_admin() && "?p=" == substr( $path, 0, 3 ) ) {
      return $url;
    }

    $slug = CMLUtils::_get( "_forced_language_slug",
                    CMLLanguage::get_slug( CMLUtils::_get( '_real_language' ) ) );

    if( isset( $this->_force_category_lang ) ) {
      $slug = CMLLanguage::get_slug( $this->_force_category_lang );
    } else if( isset( $this->_force_language_slug ) ) {
      $slug = $this->_force_language_slug;
    } else if( CMLUtils::_get( '_force_post_link' ) ) {
			$slug = CMLLanguage::get_slug( CMLUtils::_get( '_force_post_link' ) );
			CMLUtils::_del( '_force_post_link' );
    }

    if( $this->_url_mode == PRE_PATH ) {
      /*
       * page link doesn't contains slash befor path, so I add it
       */
      if( ! empty( $path ) && "/" != $path[ 0 ] ) $path = "/$path";

			$url = CMLUtils::get_home_url( $slug ) . $path;

      return $url;
    } else if( $this->_url_mode == PRE_LANG ) {
      $url = add_query_arg( array(
                            'lang' => $slug,
                            ), $url );

			return esc_url( $url );
    } else if( $this->_url_mode == PRE_DOMAIN ) {
      if( preg_match( "/^(.*\/\/)([a-z]{2})\./", $url, $match ) ) {
        $url = preg_replace( "/^(.*\/\/)([a-z]{2})\./", $match[1] . "$slug.", $url );
      } else {
        preg_match( "/^(.*\/\/)/", $url, $match );

        $url = preg_replace( "/^(.*\/\/)/", end( $match ) . "$slug.", $url );
      }

      return trailingslashit( $url ) . $path;
    }

    return $url;
  }
}

global $pagenow;

//nothing to do here...
if( "wp-login.php" == $pagenow ) return;

//Admin?
if( is_admin() ) {
  global $wpdb;

  $table_name = CECEPPA_ML_TABLE;
  $first_time = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name;
  if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name &&
      $pagenow != "plugins.php" ) {
    require_once( CML_PLUGIN_ADMIN_PATH . 'admin-utils.php' );

    add_action( 'admin_notices', '_cml_no_tables_found' );

    return;
  }

  require_once( CML_PLUGIN_ADMIN_PATH . 'admin.php' );

  $wpCeceppaML = new CMLAdmin();
} else {
  //I no language exists I don't exec the plugin
  $all = @CMLLanguage::get_all();
  $enableds = @CMLLanguage::get_enableds();

  if( empty( $all ) || empty( $enableds ) ) {
    return;
  }

  require_once( CML_PLUGIN_FRONTEND_PATH . 'frontend.php' );

  $wpCeceppaML = new CMLFrontend();
}

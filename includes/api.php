<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * Ceceppa Multilingua API 1.4
 */

 /**
  * This class provide information about configured languages
  *
  * @api
  *
  * The structure of language object is:
  * <p>
  * stdClass Object (
  * <ul>
  *   <li>[id] => id of language</li>
  *   <li>[cml_default] => ( boolean ) - is it default?</li>
  *   <li>[cml_flag] => name of flag</li>
  *   <li>[cml_language] => name of language</li>
  *   <li>[cml_language_slug] => language slug</li>
  *   <li>[cml_locale] => wordpress locale</li>
  *   <li>[cml_enabled] => enabled?</li>
  *   <li>[cml_sort_id] => language order</li>
  *   <li>[cml_custom_flag] => (boolean) - use custom flag?</li>
  *   <li>[cml_rtl] => 0</li>
  *   <li>[cml_date_format] => j M Y</li>
  * )
  * </p>
  */
class CMLLanguage {
  /** @ignore */
  private static $_default_language;
  /** @ignore */
  private static $_all_languages; //All languages (enabled and not)
  /** @ignore */
  private static $_all_enabled;   //Only enabled languages
  /** @ignore */
  private static $_all_others;    //All language except default one
  /** @ignore */
  private static $_all_by_slug;   //All languages indexed by slug
  /** @ignore */
  private static $_current_id;    //Current language id

  /**
   * Tiny ~= 16x11
   */
  const FLAG_TINY = "tiny";

  /**
   * Small ~= 32x21
   */
  const FLAG_SMALL = "small";

  /**
   * return object of default language
   *
   * @return stdObject
   */
  public static function get_default() {
    if( empty( self::$_all_languages ) ) self::get_all();

    return self::$_default_language;
  }

  /**
   * return default language id
   *
   * @return int
   */
  public static function get_default_id() {
    return self::get_default()->id;
  }

  /**
   * return default language slug
   *
   * return string
   */
  public static function get_default_slug() {
    return self::get_default()->cml_language_slug;
  }

  /**
   * return default language locale
   *
   * return string
   */
  public static function get_default_locale() {
    return self::get_default()->cml_locale;
  }

  /**
   * return all configured languages, enabled or not...
   *
   * @return stdObject
   */
  public static function get_all() {
    global $wpdb;

    /*
     * Prevent a lot of calls to database and store the result into this class
     */
    if( empty( self::$_all_languages) ) {
      self::$_all_languages = $wpdb->get_results( sprintf( "SELECT * FROM %s ORDER BY cml_sort_id ", CECEPPA_ML_TABLE ) );

      $all_languages_by_keys = array();
      $sorted = array();
      $enableds = array();
      $byslug = array();
      $others = array();
      foreach( self::$_all_languages as $l ) {
        $all_languages_by_keys[ $l->id ] = $l;
        $byslug[ $l->cml_language_slug ] = $l;
        $sorted[] = $l;

        if( $l->cml_default == 1 )
          self::$_default_language = $l;
        else
          $others[ $l->id ] = $l;

        if( $l->cml_enabled == 1 ) $enableds[$l->id] = $l;
      }

      if( empty( self::$_default_language ) ) {
        update_option( "cml_warning_no_default_language", true );

        self::$_default_language = end( $enableds );
      }

      self::$_all_enabled = $enableds;
      self::$_all_others = $others;
      self::$_all_languages = $all_languages_by_keys;
      self::$_all_by_slug = $byslug;
    }

    if( ! empty( self::$_default_language ) &&
        empty( self::$_current_id ) ) {
        self::$_current_id = self::$_default_language->id;
    }

    return self::$_all_languages;
  }

  /**
   * return all configured languages except default one
   *
   * @return stdObject
   */
  public static function get_no_default() {
    if( empty( self::$_all_languages) ) self::get_all();

    return self::$_all_others;
  }

  /**
   * return all enabled languages
   *
   * @return stdObject
   */
  public static function get_enableds() {
    if( empty( self::$_all_languages ) ) self::get_all();

    return self::$_all_enabled;
  }

  /*
   * return all enabled languages except current one
   *
   * @param boolean $only_enableds return only enabled languages
   * @return stdObject
   */
  public static function get_others( $only_enableds = true ) {
    if( empty( self::$_all_languages ) ) self::get_all();

    $langs = ( $only_enableds ) ? self::$_all_enabled : self::$_all_languages;
    unset( $langs[ self::get_current_id() ] );

    return $langs;
  }

  /**
   * return associative array where index
   * is the language slug
   *
   * @example
   * Array (
   * <ul>
   *   <li>"it" => [stdObject..],</li>
   *   <li>"en" => [stdObject...]</li>
   * </ul>
   * )
   *
   * @return Array
   */
  public static function get_slugs() {
    if( empty( self::$_all_languages ) ) self::get_all();

    return self::$_all_by_slug;
  }

  /**
   * return current language
   *
   * @return stdobject
   */
  public static function get_current() {
    if( empty( self::$_all_languages ) ) self::get_all();
    if( empty( self::$_current_id ) ) self::$_current_id = self::get_default_id();

    return self::$_all_languages[ self::$_current_id ];
  }

  /**
   * return current language id
   *
   * @return int
   */
  public static function get_current_id() {
    return self::get_current()->id;
  }

  /**
   * return current language slug
   */
  public static function get_current_slug() {
    return self::get_current()->cml_language_slug;
  }

  /**
   * return current language locale
   */
  public static function get_current_locale() {
    return self::get_current()->cml_locale;
  }

  /**
   * return the name of language
   *
   * @param int/string $lang - id or slug of language
   *
   * @return string
   */
  public static function get_name( $lang ) {
    if( empty( self::$_all_languages ) ) self::get_all();
    if( empty( self::$_default_language ) ) self::get_default();
    if( ! is_numeric( $lang ) ) $lang = self::get_id_by_slug( $lang );

    return isset( self::$_all_languages[ $lang ] ) ? self::$_all_languages[ $lang ]->cml_language : "";
  }

  /**
   * return the slug of language
   *
   * @param int/string $lang - id or slug of language
   *
   * @return string
   */
  public static function get_slug( $lang ) {
    if( empty( self::$_all_languages ) ) self::get_all();
    if( empty( self::$_default_language ) ) self::get_default();
    if( is_object( $lang ) ) $lang = $lang->id;

    if( $lang == 0 ) return self::$_default_language->cml_language_slug;

    return @self::$_all_languages[ $lang ]->cml_language_slug;
  }

  /**
   * return language by id
   *
   * @param int/string $lang id or slug of language to search
   *
   * @return stdObject
   */
  public static function get_by_id( $id ) {
    if( empty( self::$_all_languages ) || ! is_array( self::$_all_languages ) ) self::get_all();
    if( ! is_numeric( $id ) ) $id = CMLLanguage::get_id_by_slug( $id );

    return self::$_all_languages[ $id ];
  }

  /**
   * get language by slug
   *
   * @param string $slug - language slug
   * @param boolean $empty - If false return default language if $slug doesn't exists.
   *                           If true and $slug doesn't exists return empty array
   *
   * @return stdObject
   */
  public static function get_by_slug( $slug, $empty = false ) {
    if( empty( self::$_all_languages ) ) self::get_all();

    foreach( self::$_all_languages as $lang ) {
      if( $lang->cml_language_slug == $slug ) return $lang;
    }

    return ( ! $empty ) ? self::get_default() : array();
  }

  /**
   * return language id by slug
   *
   * @param string $slug - language slug
   *
   * @return int
   */
  public static function get_id_by_slug( $slug ) {
    $lang = self::get_by_slug( $slug );

    return $lang->id;
  }

  /**
   * return language id by locale
   *
   * @param string $language locale
   *
   * @return int
   */
  public static function get_id_by_locale( $locale ) {
    $langs = self::get_all();

    foreach( $langs as $lang ) {
      if( strtolower( $lang->cml_locale ) == strtolower( $locale ) ) return $lang->id;
    }

    return null;
  }

  /**
   * return the flag filename ( withouth extension )
   *
   * @param int/string $lang ( optional ) - id/slug of language, if empty default one will be used
   * @param string $size ( optional ) - size of flag: "tiny" or "small"
   *
   * @return string
   */
  public static function get_flag( $lang = null, $size = "small" ) {
    if( empty( $lang ) ) $lang = self::default_language_id();
    if( ! is_numeric( $lang ) ) $lang = self::get_id_by_slug( $lang );

    if( empty( self::$_all_languages ) ) self::get_all();

    return self::$_all_languages[ $lang ]->cml_flag;
  }

  /**
   * return flag filename with the full path.
   *
   * Example
   * www.example.com/wp-content/plugin/ceceppa-multilingua/flags/tiny/it_IT.png
   *
   * @param int/string $lang ( optional ) - id or slug of language, if empty default one will be used
   * @param string $size ( optional ) - size of flag: "tiny" or "small"
   *
   * @return string: flag filename with the full path.
   */
  public static function get_flag_src( $lang = null, $size = CML_FLAG_TINY ) {
    if( empty( self::$_all_languages ) ) self::get_all();
    if( empty( self::$_default_language ) ) self::get_default();
    if( $lang == null ) $lang = self::get_default_id();
    if( ! is_numeric( $lang ) ) $lang = self::get_id_by_slug( $lang );

    if( ! isset( self::$_all_languages[ $lang ] ) ) {
      return "";
    }

    $lang = self::$_all_languages[ $lang ];
    $flag = $lang->cml_flag;

    if( $lang->cml_custom_flag == 1 && file_exists( CML_UPLOAD_DIR . "$size/$flag.png" ) )
      $url = CML_UPLOAD_URL . "$size/$flag.png";
    else
      $url = CML_PLUGIN_URL . "flags/$size/$flag.png";

    return esc_url( $url );
  }

  /**
   * return html <img> object of flag
   *
   * @param int/string $lang - id or slug of language
   * @param string $size - flag size ( tiny or small )
   *
   * @return string
   */
  public static function get_flag_img( $lang, $size = CML_FLAG_TINY ) {
    if( is_object( $lang ) ) $lang = $lang->id;
    $url = self::get_flag_src( $lang, $size );
    $name = self::get_name( $lang );
    $slug = self::get_slug( $lang );
    $width = ( $size == CML_FLAG_TINY ) ? 16 : 32;
    $height = ( $size == CML_FLAG_TINY ) ? 11 : 23;
    return "<img src='$url' border='0' alt='$slug' title='$name' width='$width' height='$height'/>";
  }

   /**
    * @ignore
    *
    * force "reload" languages
    */
   public static function reload() {
    self::$_all_languages = null;
   }

  /**
   * get language object by post id
   *
   * <i>This function is equivalent to CMLPost::get_language_by_id()</i>
   *
   * @param int $post_id - id of post/page
   *
   * @return stdObject
   */
  public static function get_by_post_id( $post_id ) {
    return CMLPost::get_language_by_id( $post_id );
  }

  /**
   * get language id by post id
   *
   * <i>This function is equivalent to CMLPost::get_language_id_by_id()</i>
   *
   * @param int $post_id - id of post
   *
   * @return int
   */
  public static function get_id_by_post_id( $post_id ) {
    return CMLPost::get_language_id_by_id( $post_id );
  }

  /**
   * get language slug by post id
   *
   * <i>This function is equivalent to CMLPost::get_language_slug_by_id();</i>
   *
   * @param int $post_id - id of post
   *
   * @return string
   */
  public static function get_slug_by_post_id( $post_id ) {
    return CMLPost::get_language_slug_by_id( $post_id );
  }

  /**
   * Is $lang the default one?
   *
   * @param int/string $lang ( optional ) id/slug. check if $lang is the default language, if null is passed
   *                        current language will be assumed
   * @return boolean
   */
  public static function is_default( $lang = null ) {
    if( null == $lang ) {
      $lang = CMLUtils::_get( '_real_language');
    } else {
      if( is_object( $lang ) ) {
        $lang = $lang->id;
      } else if( ! is_numeric( $lang ) ) {
        $lang = CMLLanguage::get_id_by_slug( $lang );
      }
    }

    return $lang == CMLLanguage::get_default_id();
  }

  /**
   * check if $lang is the current language
   *
   * @param int/string $lang language id/slug to compare
   *
   * @return boolean
   */
  public static function is_current( $lang ) {
    if( null == $lang ) {
      $lang = CMLLanguage::get_current_id();
    } else {
      if( ! is_numeric( $lang ) ) {
        $lang = CMLLanguage::get_id_by_slug( $lang );
      }
    }

    return $lang == CMLLanguage::get_current_id();
  }

  /**
   * @ignore
   *
   * set current language
   *
   * @param $lang - language object or id
   *
   * @return void
   */
  public static function set_current( $lang ) {
    $id = is_object( $lang ) ? $lang->id : $lang;

    self::$_current_id = $id;
  }
}

/**
 * This class is used to get and store custom translations in CECEPPA_ML_TRANSLATIONS table.
 */
class CMLTranslations {
  //store "get" translations
  /** @ignore */
  private static $_translations = array();
  /** @ignore */
  private static $_keys = array();

  /**
   * This function can be used by 3rd part plugin/theme to allow translation of its strings.
   * Added string can be translated in "My Languages" page.
   *
   * @example
   * Full example is provided here:<br />
   *  http://www.alessandrosenese.eu/en/ceceppa-multilingua/extend-plugin-theme-compatibility/
   *
   * @param string $key used to store/get your own value from database. To avoid duplicated key use your own
   *                        prefix for your key. Example: "_yoast_rssafter"
   * @param string $default default value to use
   * @param string $group The group name of your own strings, this name will be displayed in "My Translations" page
   *                      in "Group" column. Group name should be preceded by "_" symbol.<br />
   *                      Example: "_YOAST"
   *
   * @return void
   */
  public static function add( $key, $default, $group, $no_default = false ) {
    global $wpdb;

    $default = bin2hex( $default );
    $langs = ( $no_default ) ? CMLLanguage::get_no_default() : CMLLanguage::get_all();
    foreach( $langs as $lang ) {
      $query = sprintf( "SELECT id FROM %s WHERE cml_text = '%s' AND cml_lang_id = %d AND cml_type = '%s'",
                        CECEPPA_ML_TRANSLATIONS,
                        bin2hex( strtolower( $key ) ),
                        $lang->id,
                        $group );

      $record = $wpdb->get_var( $query );
      if( empty( $record ) ) {
        $wpdb->insert( CECEPPA_ML_TRANSLATIONS,
                      array(
                            'cml_text' => bin2hex( strtolower( $key ) ),
                            'cml_lang_id' => $lang->id,
                            'cml_translation' => $default,
                            'cml_type' => $group
                            ),
                      array(
                        '%s', '%d', '%s', '%s',
                      )
                     );
      }
    }
  }

  /**
   * Store custom translation in database.
   *
   * Since 1.4 CML generate GNUTEXT mo file from stored translations.
   * The domain used to generate translation is: "cmltrans".
   *
   * Mo file isn't generated automatically, but you have to call manually
   * the function cml_generate_mo_from_translations()
   *
   * This function will return the id of inserted record.
   *
   * @example
   *
   * <?php _e( "Hello", "cmltrans" ); ?>
   *
   * Use "get" function instead of __, _e, because if "mo" generation fails,
   * this function will get translation from database
   *
   * @param int/string $lang - id/slug of language
   * @param string $original - original string
   * @param string $translation - translated string
   * @param string $type - type of translation( W: Widget, S: Custom strings ).
   *                Type field is used "internally" to show records in correct table, like
   *                Widget titles, My translations...
   *
   * @return string
   */
  public static function set( $lang, $original, $translated, $type, $record_id = 0 ) {
    global $wpdb;

    if( ! is_numeric( $lang ) ) $lang = CMLLanguage::get_id_by_slug( $lang );
    $original = trim( $original );

    if( $record_id == 0 ) {
      $wpdb->delete( CECEPPA_ML_TRANSLATIONS,
                      array( "cml_text" => bin2hex( $original ),
                             "cml_type" => $type,
                             "cml_lang_id" => $lang ),
                      array( "%s", "%s", "%d" ) );

      $id = $wpdb->insert( CECEPPA_ML_TRANSLATIONS,
                            array( 'cml_text' => bin2hex( $original ),
                                  'cml_lang_id' => $lang,
                                  'cml_translation' => bin2hex( $translated ),
                                  'cml_type' => $type ),
                            array( '%s', '%d', '%s', '%s' ) );

      return $id;
    } else {
      $wpdb->update( CECEPPA_ML_TRANSLATIONS,
                    array( 'cml_text' => bin2hex( $original ),
                          'cml_lang_id' => $lang,
                          'cml_translation' => bin2hex( $translated ),
                          'cml_type' => $type ),
                    array( 'id' => $record_id ),
                    array( '%s', '%d', '%s', '%s' ),
                    array( '%d' ) );

      return $record_id;
    }
  }

  /**
   * return translation stored in cml_trans table by key
   *
   * This function will get translation from ".mo" file if:
   *    1) it's generated correctly
   *    2) $lang == current language
   *
   * otherwise will get translation from database
   *
   * <strong>string match is case sensitive</strong>
   *
   * @param int/string $lang - language id or slug
   * @param string $string - string to translate
   * @param string $type - ( optional ) This is used internally by Ceceppa Multilingua, only in admin interface
   *                  T - Site Title/Tagline,
   *                  W - Widget,
   *                  M - My translations
   *
   * @param boolean $return_empty - If true, return empty string if no translation is found
   * @param boolean $ignore_po  Since 1.4 the plugin will generate mo file for all translation stored
   *                                in CECEPPA_ML_TRANSLATIONS ( widget titles, my translations, site title/tagline... ).
   *                                So by default if $lang == current language, the plugin will get translation
   *                                from ".mo" file instead query the database.
   *                                You can force to retive translation from database.
   *
   * @return string
   */
  public static function get( $lang, $string, $type = "", $return_empty = false, $ignore_po = false ) {
    global $wpdb;

    if( empty( $string ) ) return "";

    if( "_" == $type[ 0 ] && ! $return_empty ) {
      $return_empty = true;
    }

    $string = trim( $string );

    //C = Category
    $s = ( $type == "C" ) ? strtolower( $string ) : $string;

    //Look if I already translated it...
    //if( isset( self::$_keys[ $lang ] ) &&
    //  in_array( sanitize_title( $s ), self::$_keys[ $lang ] ) ) {
    //
    //  $index = array_search( sanitize_title( $s ), self::$_keys[ $lang ] );
    //  return self::$_translations[ $lang ][ $index ];
    //}

    if( CML_GET_TRANSLATIONS_FROM_PO &&
       ! $ignore_po &&
       1 == CMLUtils::_get( '_po_loaded' ) ) {

      $translations = get_translations_for_domain( 'cmltrans' );
      $slug = CMLLanguage::get_slug( $lang );

      $s = "_{$slug}_" . sanitize_title( $s );

      if( isset( $translations->entries[ $s ] ) ) {
        return __( $s, 'cmltrans' );
      } else {
        if( $return_empty ) return "";
      }
    }

    if( ! is_numeric( $lang ) ) {
      $lang = CMLLanguage::get_id_by_slug( $lang );
    }

    $query = sprintf(" SELECT UNHEX(cml_translation) FROM %s WHERE cml_text = '%s' AND cml_lang_id = %d AND cml_type LIKE '%s'",
                                    CECEPPA_ML_TRANSLATIONS, bin2hex( $s ), $lang, "%" . $type . "%" );

    $return = $wpdb->get_var( $query );

    if( $return_empty && empty( $return ) ) return "";

    $return = ( empty( $return ) ) ?  $string : html_entity_decode( stripslashes( $return ) );

    //self::$_translations[$lang][] = $return;
    //self::$_keys[ $lang ][] = sanitize_title( $string );

    return $return;
  }

  /**
   * get translation from wordpress
   *
   * @ignore
   */
  public static function gettext( $lang, $string, $type, $path = null ) {
    if( null != $path && ! CML_GET_TRANSLATIONS_FROM_PO ) {
      return CMLTranslations::get( $lang, $string, $type, true, true );
    }

    //Recupero la traduzione dalle frasi di wordpress ;)
    require_once( CML_PLUGIN_PATH . "gettext/gettext.inc" );

    if( empty( $lang ) ) $lang = CMLLanguage::get_current_id();
    $lang = CMLLanguage::get_by_id( $lang );
    $locale = $lang->cml_locale;

    // gettext setup
    T_setlocale( LC_MESSAGES, $locale );
    // Set the text domain as 'messages'

    if( ! empty( $path ) ) {
      $domain = "cmltrans-" . $locale;
    } else {
      $domain = $locale;
      $path = trailingslashit( CML_WP_LOCALE_DIR );
    }

    T_bindtextdomain( $domain, $path );
    T_bind_textdomain_codeset( $domain, 'UTF-8' );
    T_textdomain( $domain );

    if( $path !== null ) $string = strtolower( $string );

    return T_gettext( $string );

    //return ( empty( $ret ) ) ?  $string : html_entity_decode( stripslashes( $ret ) );
  }

  /**
   * return key stored in translations table by its translation
   *
   * @param string $text to search
   * @param string $group group
   */
  public static function search( $lang, $text, $group ) {
    global $wpdb;

    if( ! is_numeric( $lang ) ) $lang = CMLLanguage::get_id_by_slug( $lang );
    $query = sprintf( "SELECT UNHEX(cml_text) FROM %s WHERE cml_lang_id = %d AND cml_translation = '%s' AND cml_type = '%s'",
			CECEPPA_ML_TRANSLATIONS, $lang, bin2hex( $text ), $group );

    return $wpdb->get_var( $query );
  }

  /**
   * delete all records with cml_type = xx
   *
   * @ignore
   */
  public static function delete( $type ) {
    global $wpdb;

    $wpdb->delete( CECEPPA_ML_TRANSLATIONS,
                  array( "cml_type" => $type ),
                  array( "%s" ) );
  }

  /* @ignore */
  public static function delete_text( $text, $group ) {
    global $wpdb;

    $wpdb->delete( CECEPPA_ML_TRANSLATIONS,
                  array(
		    "cml_text" => bin2hex( $text ),
		    "cml_type" => $group,
                  ),
                  array( "%s", "%s" ) );
  }
}

/**
 * This class is used to get/set post translation/language or get language by its id
 *
 */
class CMLPost {
  /** @ignore */
  private static $_indexes = null;
  /** @ignore */
  private static $_uniques = null;
  /** @ignore */
  private static $_posts_meta = array();

  /**
   * return language object by post id
   *
   * this function return "null" if post doesn't exists in any language
   *
   * @param int/string $post_id - id of post/page
   *
   * @return stdObject
   */
  public static function get_language_by_id( $post_id ) {
    if( empty( self::$_indexes ) ) self::_load_indexes();

    /*
     * search in current language first.
     * because if post exists in multiple languages, included current one,
     * I have to return current language id, not random :)
     */
    if( @in_array( $post_id, self::$_indexes[ CMLLanguage::get_current_id() ] ) ) {
      return CMLLanguage::get_current();
    }

    foreach( CMLLanguage::get_others( ! is_admin() ) as $lang ) {
      if( @in_array( $post_id, self::$_indexes[ $lang->id ] ) )
        return $lang;
    }

    return null;
  }

  /**
   * return language id by post id
   *
   * @param int $post_id - id of post/page
   * @param boolean $unique check if $post_id exists in all languages, if true return 0
   *                        In backend I need to get information by post meta, or I'll lost
   *                        "all languages" ( = 0 ) information.
   *
   * @return int
   */
  public static function get_language_id_by_id( $post_id, $unique = false ) {
    if( $unique ) {
      if( self::is_unique( $post_id ) ) {
        return 0;
      }
    }

    $lang = self::get_language_by_id( $post_id );

    if( null === $lang ) {
      //Get from meta
      $m = get_post_meta( $post_id, "_cml_meta", true );

      if( $unique && empty( $m ) ) {
        $lang = self::get_language_by_id( $post_id );

        if( is_object( $lang ) ) {
          //update meta
          self::update_meta( $lang->id, $post_id );
        }

        return is_object( $lang ) ? $lang->id : 0;
      } else {
        return @$meta[ "lang" ];
      }
    }

    return $lang->id;
  }

  /**
   * get language slug by post id
   *
   * @param int $post_id post/page id
   *
   * @return string
   */
  public static function get_language_slug_by_id( $post_id ) {
    $lang = self::get_language_by_id( $post_id );

    return is_object( $lang ) ? $lang->cml_language_slug : "";
  }

  /**
   * get the translation id, if exists, in selected language
   *
   * this function will return 0 if no translation is found.
   *
   * @param int/string $lang - language id/slug in which return translation
   * @param int $post_id - post id
   *
   * @return int
   */
  public static function get_translation( $lang, $post_id ) {
    global $wpdb;

    if( is_numeric( $lang ) ) $lang = CMLLanguage::get_slug( $lang );
    if( is_object( $lang ) ) $lang = $lang->cml_language_slug;
    if( empty( $post_id ) ) return 0;

    //if( ! CECEPPA_ML_MIGRATED ) {
    //  return cml_old_get_linked_post( $wpCeceppaML->get_language_id_by_post_id( $post_id ), null, $post_id, $lang );
    //}

    $linked = self::get_translations( $post_id );
    if( empty( $linked ) ) return 0;

    return ( ! @array_key_exists( $lang, $linked) ) ? 0 : $linked[ $lang ];
  }

  /**
   * get all available translations of post
   *
   * This function will return Array containing all info about linked posts
   *
   * Array(
   * <ul>
   *   <li>[language slug] => [post_id]</li>
   *   <li>...</li>
   *   <li>[indexes] => Array<br />
   *    <i>In this subarray there are all linked posts, including $post_id</i>
   *    <ul>
   *      <li>[language slug] => [post_id]</li>
   *      <li>...</li>
   *    </ul>
   *   </li>
   *   <li>
   *    [linked] => Array<br />
   *    <i>In this subarray there are only linked post indexes</i>
   *    <ul>
   *      <li>[linked language slug] => [linked_id]</li>
   *      <li>...</li>
   *    </ul>
   *  </li>
   * </ul>
   * )
   *
   * @example
   * <br />
   * Array (
   * <ul>
   *   <li>[it] => 2552</li>
   *   <li>[en] => 541</li>
   *   <li>[eo] => 0</li>
   *   <li>[indexes] => Array
   *       (
   *       <ul>
   *        <li>[it] => 2552</li>
   *       </ul>
   *       )
   *    </li>
   *    <li>
   *   [linked] => Array
   *       (
   *       <ul>
   *        <li>[en] => 541</li>
   *       )
   *    </li>
   *  </ul>
   * )
   *
   * @param int $post_id - post id
   * @param boolean $force - force to rebuild meta. ( This parameter is used internally by CML )
   *
   * return Array
   */
  public static function get_translations( $post_id, $force = true ) {
    global $wpdb;

    if( empty( $post_id ) ) return array();

    if( ! $force ) {
      $lang = CMLLanguage::get_id_by_post_id( $post_id );

      $key = "__cml_lang_{$lang}__{$post_id}";
      $val = CMLUtils::_get_translation( $key, $lang );

      if( null !== $val ) return $val;
    }

    if( ! isset( self::$_posts_meta[ $post_id ] ) || $force ) {
      $row = ""; //get_post_meta( $post_id, "_cml_meta", true );

      if( empty( $row ) || empty( $row[ 'lang' ] ) || $force ) {
        if( empty( $GLOBALS[ '_cml_language_columns' ] ) || $force ) {
          require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-settings-gen.php' );

          cml_generate_lang_columns();
        }

        $_cml_language_columns = & $GLOBALS[ '_cml_language_columns' ];
        $_conv = & $GLOBALS[ '_cml_language_keys' ];

        $query = "SELECT ";
        foreach( $_conv as $key => $label ) {
          $select[] = "$key as $label";
        }

        /*
         * something happend that $_conv is empty and that couse a warning
         * and I can't store post relations properly.
         */
        if( empty( $select ) ) {
          $keys = array_keys( CMLLanguage::get_all() );
          $langs = array_keys( CMLLanguage::get_slugs() );

          foreach( $keys as $k => $v ) {
            $select[] = "lang_{$v} as " . $langs[ $k ];
          }
        }

        $query .= join( ",", $select ) . " FROM " . CECEPPA_ML_RELATIONS . " WHERE ";
        foreach( $_cml_language_columns as $l ) {
          $where[] = "$l = $post_id";
        }
        $query .= join( " OR ", $where );

        $row = $wpdb->get_row( $query, ARRAY_A );
        unset( $row[ "id" ] );

        $keys = @array_filter( $row );
        $keys = @array_replace( $keys, $_conv );
        $others = @array_filter( is_array( $row ) ? $row : array() );
        unset( $others[ CMLPost::get_language_slug_by_id( $post_id ) ] );

        $row = @array_merge( (array) $row, array( "indexes" => array_filter( $row ),
                                                 "linked" => $others ) );

        if( ! $force && isset( $row[ 'lang' ] ) ) {
          self::update_meta( self::get_language_id_by_id( $post_id ), $post_id, $row );
        } else {
        }
      } else {
        $row = $row[ 'translations' ];
      }

      self::$_posts_meta[ $post_id ] = $row;
    } else {
      $row = self::$_posts_meta[ $post_id ];
    }

    return $row;
  }

  /**
   * check if $post_id exists in all languages
   *
   * @param int $post_id post id to search
   *
   * return boolean
   */
  public static function is_unique( $post_id ) {
    if( null === self::$_uniques ) self::_load_indexes();

    return in_array( $post_id, self::$_uniques );
  }

  /**
   * set language of post
   *
   * This function will unlink $post_id by its translations
   *
   * @param int/string $lang - post language id/slug
   * @param int $post_id - post id
   */
  public static function set_language( $lang, $post_id ) {
    if( ! is_numeric( $lang ) ) $lang = CMLLanguage::get_id_by_slug( $lang );

    cml_migrate_database_add_item( $lang, $post_id, 0, 0 );
  }

  /**
   * set single translation to post id
   *
   * This function is used to link 2 posts
   *
   * When you link a post to single translation, relations with other language will not be losed.
   * If you want remove other relations, you have to use set_language method first.
   *
   * @param int $post_id - post to set translation
   * @param int/string $linked_lang - language id/slug of linked post
   * @param int $linked_post - post id of translation
   * @param int $post_lang ( optional ) - language of $post_id. If null, It will get from database
   *
   * @return void
   */
  public static function set_translation( $post_id, $linked_lang, $linked_post, $post_lang = null ) {
    self::set_translations( $post_id, array( $linked_lang => $linked_post ), $post_lang );
  }

  /**
   * add multiple translations to post id
   *
   * This function will update relations only from $post_id with $translations posts, so relations
   * from $post_id and languages than doesn't exists in $translations array will not be broken.
   *
   * If you need to set relation only from $post_id and $translations, and remove the other one, you
   * have to "break" them using set_language method first.
   *
   * @param $post_id - post to set translation+
   * @param $translations - array with language_slug as key and post_id as value.
   *                        array( "it" => 1, "en" => 2 )...
   * @param $post_lang ( optional ) - set also the language of $post_id
   *
   * @example:
   *
   *    CMLPost::set_translation( 1, array( "it" => 2, "en" => 3 ) )
   */
  public static function set_translations( $post_id, $translations, $post_lang = null ) {
    //$_cml_language_columns = & $GLOBALS[ '_cml_language_columns' ];
    if( null === $post_lang ) $post_lang = CMLPost::get_language_id_by_id( $post_id );

    /*
     * for quickedit
     */
    if( $post_lang !== null && isset( $translations[ $post_lang ] ) ) {
      unset( $translations[ $post_lang ] );
    }

    foreach( $translations as $key => $id ) {
      if( ! is_numeric( $key ) ) $key = CMLLanguage::get_id_by_slug( $key );

      cml_migrate_database_add_item( $post_lang, $post_id, $key, $id );
    }

    require_once( CML_PLUGIN_ADMIN_PATH . "admin-settings-gen.php" );

    // //Update info
    cml_fix_rebuild_posts_info();
    cml_generate_mo_from_translations( "_X_" );

    self::_load_indexes();
  }

  /**
   * set post as unique ( it will be exists in all languages )
   *
   * @param int $post_id the post id
   */
  public static function set_as_unique( $post_id ) {
    cml_migrate_database_add_item( 0, $post_id, 0, 0 );
  }

  /*
   * update post meta
   * @ignore
   */
  public static function update_meta( $lang, $post_id, $translations = null ) {
    /*
     * I just updated post relation, so I have to rebuild meta :)
     */
    //Add my meta to post
    if( null == $translations ) {
      $translations = CMLPost::get_translations( $post_id, true );
    }

    if( ! is_numeric( $lang ) ) $lang = CMLLanguage::get_id_by_slug( $lang );

    $meta = array( "lang" => $lang,
                    "translations" => $translations );

    update_post_meta( $post_id, "_cml_meta", $meta );
  }

  /**
   * get indexes of posts that exists in selected language
   *
   * @param int/slug $lang ( optional, if not set will be = current language id )
   *                      id / slug of language
   *
   * @return array
   */
  public static function get_posts_by_language( $lang = null ) {
    if( empty( $lang ) ) $lang = CMLLanguage::get_current_id();
    if( ! is_numeric( $lang ) ) $lang = CMLLanguage::get_id_by_slug( $lang );

    //Gli articoli senza lingua sono "figli di tutti"
    if( empty( self::$_indexes ) ) self::_load_indexes();

    $posts = @self::$_indexes[ $lang ];

    return ! empty( $posts ) ? array_unique( $posts ) : array();
  }

  /*
   * for private use only, update post indexes by language
   * @ignore
   */
  public static function _update_posts_by_language( $lang, $ids ) {
    self::$_indexes[ $lang ] = $ids;
  }

  /**
   * return all posts by languages.
   *
   * The key of array is the language id
   *
   * @return array
   */
  public static function get_posts_by_languages() {
    //Gli articoli senza lingua sono "figli di tutti"
    if( empty( self::$_indexes ) ) self::_load_indexes();

    return self::$_indexes;
  }

  /**
   * return all posts by languages.
   *
   * The key of array is the language id
   *
   * @return array
   */
  public static function get_unique_posts() {
    if( empty( self::$_indexes ) ) self::_load_indexes();

    return self::$_uniques;
  }

  /**
   * check if $post_id has translation in selected language.
   *
   * @param int/string $lang - language id/slug
   * @param int $post_id - post id
   *
   * return boolean
   */
  public static function has_translation( $lang, $post_id ) {
    if( ! isset( self::$_posts_meta[ $post_id ] ) ) {
      self::$_posts_meta[ $post_id ] = self::get_translations( $post_id );
    }

    if( is_numeric( $lang ) ) $lang = CMLLanguage::get_slug( $lang );

    return ( isset( self::$_posts_meta[ $post_id ]['indexes'] ) &&
            self::$_posts_meta[ $post_id ]['indexes'][ $lang ] > 0 );
  }

  /**
   * check if $post_id has any translation
   *
   * @param int $post_id - post id
   *
   * @return boolean
   */
  public static function has_translations( $post_id ) {
    if( ! isset( self::$_posts_meta[ $post_id ] ) ) {
      self::$_posts_meta[ $post_id ] = self::get_translations( $post_id );
    }

    return ! empty( self::$_posts_meta[ $post_id ][ 'linked' ] );
  }

  /**
   * check if $post1 is translation of $post2
   *
   * @param int $post1 post id
   * @param int $post2 post id
   *
   * @return boolean
   */
  public static function is_translation( $post1, $post2 ) {
    $translations = CMLPost::get_translations( $post1 );

    return @in_array( $post2, $translations[ 'indexes' ] );
  }

  /** @ignore */
  private static function _load_indexes() {
    $langs = cml_get_languages( false );

    foreach($langs as $lang) {
      self::$_indexes[ $lang->id ] = get_option( "cml_posts_of_lang_" . $lang->id );
    }

    self::$_uniques = get_option( 'cml_unique_posts', array() );
  }

  /**
   * @ignore
   *
   * Remove extra "-##" add by wordpress when more than one post has
   * same titles, but ONLY on translations.
   */
  public static function remove_extra_number( $permalink, $post ) {
    $_cml_settings = & $GLOBALS[ '_cml_settings' ];

    //Disabled?
    if( @$_cml_settings[ 'cml_remove_extra_slug' ] !== 1 ) return $permalink;

    $removed = false;

    if( is_object( $post ) ) {
      if( get_option( '_cml_no_remove_extra_' . $post->ID, false ) ) return $permalink;

      //Remove last "/"
      $url = untrailingslashit( $permalink );
      $url = str_replace( CMLUtils::home_url(), "", $url );

      /*
       * Post/page link contains "-d"
       */
      preg_match_all( "/-\d+/", $url, $out );

      /*
       * if true I have to check if it was added by "wordpress" :)
       */
      if( count( $out[ 0 ] ) > 0 ) {
        /*
         * when hook get_page_link, wordpress pass me only post id, not full object
         */
        $post_title = $post->post_title;

        /*
         * got how many number occourrences ( -d ) are in the "real title"
         */
        preg_match_all( "/\d+/", $post_title, $pout );

        /*
         * compare occourrences between permalink and title,
         * if title contains more one, I remove it :)
         */
        //Remove autoinserted -## from url
        if( count( $pout[0] ) < count( $out[ 0 ] ) && CMLPost::has_translations( $post->ID ) ) {
          $permalink = trailingslashit( preg_replace( "/-\d*$/", "",
                                                     untrailingslashit( $permalink ) ) );

          $removed = true;
        }
      }

      if( $removed &&
          CMLUtils::get_url_mode() == PRE_NONE ) {
        $post_id = is_object( $post ) ? $post->ID : $post;

        $lang = CMLLanguage::get_by_post_id( $post_id );
        if( empty( $lang ) ) {
          $lang = CMLLanguage::get_current();
        }

        $permalink = esc_url( add_query_arg( array(
                                          "lang" => $lang->cml_language_slug,
                                        ), $permalink ) );
      }
    }

    return $permalink;
  }
}

/**
 * utility class
 *
 */
class CMLUtils {
  /** @ignore */
  private static $_permalink_structure;
  /** @ignore */
  private static $_url_mode;
  /** @ignore */
  private static $_date_format;
  /** @ignore */
  private static $_home_url;
  /** @ignore */
  private static $_clean_url;
  /** @ignore */
  private static $_clean_request;
  /** @ignore */
  private static $_request_url;
  /** @ignore */
  private static $_clean_applied = false;
  /** @ignore */
  private static $_url;
  /** @ignore */
  private static $_language_detected = null;
  /** @ignore */
  private static $_vars = array();

  /**
   * @ignore
   *
   * return wordpress permalink structure option
   *
   * @return string
   */
  public static function get_permalink_structure() {
    if( ! isset( self::$_permalink_structure ) ) self::$_permalink_structure = get_option( "permalink_structure" );

    return self::$_permalink_structure;
  }

  /**
   * return home_url link in according to language slug.
   *
   * This function format home_url in according to $slug parameter
   *
   * @param string $slug - language slug. If null current slug will be used
   *
   * @return string
   */
  public static function get_home_url( $slug = null ) {
    $_cml_settings = & $GLOBALS[ '_cml_settings' ];

    if( null === $slug ) $slug = CMLLanguage::get_current()->cml_language_slug;
    if( is_numeric( $slug ) ) $slug = CMLLanguage::get_slug( $slug );

    switch( CMLUtils::get_url_mode() ) {
    case PRE_PATH:
      if( $slug == CMLLanguage::get_default_slug() &&
          $_cml_settings[ 'url_mode_remove_default' ] ) {
        $slug = "";
      } else {
        $slug = "/$slug";
      }

      $link = CMLUtils::home_url() . $slug;
      break;
    case PRE_DOMAIN:
      $link = CMLUtils::home_url();
      break;
    default:
      $link = CMLUtils::home_url() . "?lang=$slug";
      break;
    } //endswitch;

    return $link;
  }

  /**
   * @ignore
   */
  public static function get_url_mode() {
    if( empty( self::$_url_mode ) )  {
      global $_cml_settings;

      self::$_url_mode = $_cml_settings[ 'url_mode' ];  //more easy

      $permalink = self::get_permalink_structure();
      if( empty( $permalink ) && self::$_url_mode == PRE_PATH )
        self::$_url_mode = PRE_LANG;
    }

    return self::$_url_mode;
  }

  public static function get_category_url_mode() {
    return CMLUtils::_get( 'cml_category_mode' );
  }

  /**
   * get wordpress date format option
   * @ignore
   */
  public static function get_date_format() {
    if( empty( self::$_date_format ) ) self::$_date_format = get_option( 'date_format' );

    return self::$_date_format;
  }

  /**
   * get "clean" home_url.
   *
   * The plugin translate home url in according to current language.
   * So if you call the wp function "home_url" it will home url with language information,
   * in according to url modification mode.
   *
   * This function will return "real" home without any language information.
   *
   * @example
   *
   * home_url()<br />
   *  www.example.com/it<br />
   * <br />
   * CMLUtils::home_url()<br />
   *  www.example.com<br />
   *
   * @return string
   */
  public static function home_url() {
    if( empty( self::$_home_url ) ) {
      $GLOBALS[ '_cml_no_translate_home_url' ] = true;
      self::$_home_url = home_url();

      unset( $GLOBALS[ '_cml_no_translate_home_url' ] );
    }

    return self::$_home_url;
  }

  /**
   * remove language information from url
   *
   * @ignore
   */
  public static function clear_url( $url = null ) {
    global $wp_rewrite;

    if( self::get_url_mode() != PRE_PATH
        || ( self::$_clean_applied == true &&
        null === $url ) ) {
      return self::$_language_detected;
    }

    if( empty( self::$_url ) ) {
      $http = ( ! is_ssl() ) ? "http://" : "https://";
      self::$_url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      self::$_request_url = str_replace( trailingslashit( self::home_url() ),
                                        "", self::$_url );
    }

    if( null === $url ) {
      $_url = self::$_url;
      $request_url = self::$_request_url;

      /*
       * remove all parameters in url
       */
      self::$_clean_url = preg_replace( "/\?.*/", "", $_url );
      self::$_clean_request = $request_url;
    } else {
      $_url = $url;
      $request_url = str_replace( trailingslashit( self::home_url() ),
                                        "", $url );
    }

    $_url = $request_url;
    $http = ( ! is_ssl() ) ? "http://" : "https://";
    $base_url = str_replace( $http . $_SERVER['HTTP_HOST'], "", get_option( 'home' ) );

    if( preg_match( "#^([a-z]{2})(/.*)?$#i", $_url, $match ) ) {
      $lang = CMLLanguage::get_id_by_slug( $match[1] );
      if( empty( $lang ) ) {
        return $url;
      }

      $_url = substr( $_url, 3 );
      $_url = preg_replace( "/\?.*/", "", $_url );

      if( null === $url ) {
        self::$_language_detected = $lang;

        CMLLanguage::set_current( self::$_language_detected );

        self::$_clean_url = trailingslashit( self::$_home_url ) . $_url;
        self::$_clean_request = trailingslashit( $base_url ) . $_url;
      } else {
        $_url = trailingslashit( CMLUtils::home_url() ) . $_url;
      }
    }

    if( null === $url ) {
      self::$_clean_applied = true;
    }

    return ( null === $url ) ? self::$_language_detected : $_url;
  }

  /**
   * return current url withouth any language information.
   *
   * @example
   *
   *  www.example.com/en/example<br />
   *<br />
   *  CMLUtils::get_clean_url() will return:<br />
   *<br />
   *    www.example.com/example
   *
   * @return string
   */
  public static function get_clean_url() {
    if( ! empty( self::$_clean_url ) ) {
      return self::$_clean_url;
    } else {
      $http = ( ! is_ssl() ) ? "http://" : "https://";
      $_url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

      return preg_replace( "/\?.*/", "", $_url );
    }
  }

  /**
   * get $_SERVER[ 'REQUEST_URI' ] with no language information.
   *
   * @return string
   */
  public static function get_clean_request() {
    return self::$_clean_request;
  }

  /**
   * @ignore
   */
  public static function _set( $key, $value ) {
    self::$_vars[ $key ] = $value;
  }

  /**
   * @ignore
   */
  public static function _get( $key, $default = null ) {
    return isset( self::$_vars[ $key ] ) ? self::$_vars[ $key ] : $default;
  }

  /**
   *@ignore
   */
  public static function _del( $key ) {
    unset( self::$_vars[ $key ] );
  }

  /**
   * @ignore
   */
  public static function _append( $key, $value ) {
    if( ! isset( self::$_vars[ $key ] ) ) {
      self::$_vars[ $key ] = array();
    }

    self::$_vars[ $key ][] = $value;
  }

  /**
   * @ignore
   *
   * return translated string from .po file
   */
  public static function _get_translation( $key ) {
    if( ! CML_GET_TRANSLATIONS_FROM_PO ||
        1 != CMLUtils::_get( '_po_loaded' ) ) {

      return null;
    }

    $translations = get_translations_for_domain( 'cmltrans' );

    if( isset( $translations->entries[ $key ] ) ) {
      return unserialize( stripslashes( __( $key, 'cmltrans' ) ) );
    }

    return null;
  }
}



/**
* This class provide information about translated taxonomies
*
* @api
*
*/
class CMLTaxonomies {
  /** @ignore */
  private static $_taxonomies = array();
  /** @ignore */
  private static $_translations = array();

  public static function get( $lang, $term ) {
    global $wpdb;

    $term_id = ( is_object( $term ) ) ? $term->term_id : intval( $term );
    $lang = is_object( $lang ) ? $lang->id : intval( $lang );

    if( isset( self::$_taxonomies[$lang][ $term_id ] ) ) {
      return self::$_taxonomies[$lang][ $term_id ];
    }

    $query = "SELECT id, cml_cat_id, UNHEX(cml_cat_name) as original, UNHEX(cml_cat_translation) as name, UNHEX(cml_cat_translation_slug) as slug, cml_taxonomy, UNHEX(cml_cat_description) as description FROM " . CECEPPA_ML_CATS . " WHERE cml_cat_id = $term_id AND cml_cat_lang_id = $lang";

    $row = $wpdb->get_row( $query );

    self::$_taxonomies[$lang][ $term_id ] = $row;
    return $row;
  }

  public static function get_translation( $lang, $term_name ) {
    global $wpdb;

    $original = strtolower( $term_name );
    if( isset( self::$_translations[ $lang ][ $original ] ) ) {
      return self::$_translations[ $lang ][ $original ];
    }

    $query = sprintf(" SELECT UNHEX(cml_cat_translation) FROM %s WHERE cml_cat_name = '%s' AND cml_cat_lang_id = %d",
                                    CECEPPA_ML_CATS, bin2hex( $original ), $lang );

    $val = $wpdb->get_var( $query );
    if( empty( $val ) ) $val = $term_name;

    self::$_translations[ $lang ][ $original ] = $val;
    return $val;
  }
}

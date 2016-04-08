<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/**
 * Check if current url ( or $url ) is the homepage
 *
 * If is set a static page as homepage, the plugin check if
 * current page is front page or its translation
 *
 * @param string $url - ( optional ) url to check
 *
 */
function cml_is_homepage( $url = null, $the_id = null ) {
  static $static_id = null;

  global $wpdb;

  if( ! empty( $wpdb ) && method_exists( $wpdb, 'is_category' ) ) {
    if( is_category() || is_archive() ) return false;
  }

  //Controllo se è stata impostata una pagina "statica" se l'id di questa è = a quello della statica
  if( cml_use_static_page() ) {
    global $wp_query;

    if( $static_id == null ) {
      $pfp = get_option( "page_for_posts" );
      $pof = get_option( "page_on_front" );

      $static_id = array( $pfp, $pof );

      foreach( array( $pfp, $pof ) as $id ) {
        $t = CMLPost::get_translations( $id );

        if( isset( $t['indexes'] ) ) {
          foreach( $t['indexes'] as $tid ) {
            $static_id[] = $tid;
          }
        }
      }
    }

    $lang_id = CMLLanguage::get_current_id();


    /*
     * on some site get_queried_object_id isn't available on start
     * and I get:
     * Fatal error: Call to a member function get_queried_object_id() on a non-object
     *
     * So ensure that method exists, otherwise I use get_the_ID() method
     */
    /*
     * If I call get_queried_object_id in the footer can happens that
     * queried_object_id is different from "real" queried_object,
     * so I store that info in $GLOBALS to avoid this problem :)
     */
    /**
     * TODO: figure out why get_queried_object() return the id of the first post
     * in the page_for_posts page...
     */
    if( null == $the_id ) {
      if( ! isset( $GLOBALS[ '_cml_get_queried_object_id' ] ) ) {
        if( ! empty( $wpdb ) && method_exists( $wpdb, 'get_queried_object' ) ) {
          $GLOBALS[ '_cml_get_queried_object_id' ] = get_queried_object()->ID;
          $GLOBALS[ '_cml_get_queried_object' ] = get_queried_object();

          $the_id = & $GLOBALS[ '_cml_get_queried_object_id' ];
        } else {
          if( is_object( get_post() ) ) {
            $the_id = get_the_ID();

            // $GLOBALS[ '_cml_get_queried_object_id' ] = $the_id;
          }
        }
      } else {
        $the_id = $GLOBALS[ '_cml_get_queried_object_id' ];
      }
    }

    if( ! empty( $the_id ) ) {
      if( in_array( $the_id, $static_id ) ) return true;	//Yes, it is :)

      //Is a translation of front page?
      $linked = CMLPost::get_translation( CMLLanguage::get_current_id(), $the_id  );
      if( ! empty( $linked ) ) {
        return in_array( $linked, $static_id );
      }
    }
  }

  //I can't use is_home(), because it return empty value, so I have to check
  //it manually
  if( ! empty( $wpdb ) && method_exists( $wpdb, 'is_home' ) ) {
    return is_home();
  }

  //Remove language information by url
  CMLUtils::clear_url();

  return trailingslashit( CMLUtils::get_clean_url() ) == trailingslashit( CMLUtils::home_url() );
}

/**
 * @ignore
 * @internal
 *
 * get page by path
 *
 * On some site I can't use url_to_postid() because
 * $wp_reqruite is empty...
 *
 */
function cml_get_page_id_by_path($url, $types = null) {
  $url = untrailingslashit( $url );
  $plinks = explode( "/", $url );

  if( $types == null ) {
    $types = array_keys( get_post_types() );
  }

  $p = cml_get_page_by_path( $url, OBJECT, $types );
  $the_id = is_object( $p ) ? $p->ID : 0;

  return $the_id;
}

/**
 * @ignore
 * @internal
 *
 * This is modified version of wordpress function get_page_by_path,
 * because original one doesn't works correctly for me
 *
 * @since 2.1.0
 * @uses $wpdb
 *
 * @param string $page_path Page path
 * @param string $output Optional. Output type. OBJECT, ARRAY_N, or ARRAY_A. Default OBJECT.
 * @param array $post_type Optional. Post type. Default page.
 * @return WP_Post|null WP_Post on success or null on failure
 */
function cml_get_page_by_path($page_path, $output = OBJECT, $post_type = array('page')) {
    global $wpdb;

    $page_path = rawurlencode(urldecode($page_path));
    $page_path = str_replace('%2F', '/', $page_path);
    $page_path = str_replace('%20', ' ', $page_path);
    $parts = explode( '/', trim( $page_path, '/' ) );
    $parts = array_map( 'esc_sql', $parts );
    $parts = array_map( 'sanitize_title_for_query', $parts );

    $in_string = "'". implode( "','", $parts ) . "'";
    $post_type_sql = implode( "','", $post_type );
//     $wpdb->escape_by_ref( $post_type_sql );

    if( empty( $in_string ) ) {
      return;
    }

    $query = "SELECT ID, post_name, post_parent, post_type FROM $wpdb->posts WHERE post_name IN ($in_string) AND (post_type IN ( '$post_type_sql' ) ) AND post_status = 'publish'";
    $pages = $wpdb->get_results( $query, OBJECT_K );
    $revparts = array_reverse( $parts );

    $foundid = 0;
    foreach ( (array) $pages as $page ) {
	    if ( $page->post_name == $revparts[0] ) {
		    $count = 0;
		    $p = $page;

		    while ( $p->post_parent != 0 && isset( $pages[ $p->post_parent ] ) ) {
			    $count++;
			    $parent = $pages[ $p->post_parent ];
			    if ( ! isset( $revparts[ $count ] ) || $parent->post_name != $revparts[ $count ] )
				    break;
			    $p = $parent;
		    }

		    //if ( $p->post_parent == 0 && $count + 1 == count( $revparts ) && $p->post_name == $revparts[ $count ] ) {
		    if ( $p->post_parent == 0 && $p->post_name == $revparts[ $count ] ) {
              $foundid = $page->ID;
              if ( $page->post_type == $post_type )
                  break;
		    }
	    }
    }

    if ( $foundid )
	    return get_post( $foundid, $output );

    return null;
}

/**
 * This function will return the current link translated in desired language.
 *
 * @param stdObject $result - language object ( i.e. CMLLanguage::get_default() ) or slug used to translate current link
 * @param boolean $linked - true, return linked translation, false return homepage link
 * @param boolean $only_existings - return linked post only if it exists, otherwise return blank link
 * @param boolean $queried - use get_queried_object_id instead of get_the_ID
 *
 * return string
 */
function cml_get_the_link( $lang, $linked = true, $only_existings = false, $queried = true ) {
  global $wpCeceppaML, $_cml_settings;

  //Extra link parameters
  $args = array();
  if( $queried ) {
    if( null == CMLUtils::_get( '_query_string', null ) ) {
      $parameters = explode( "&", $_SERVER[ 'QUERY_STRING' ] );

      if( ! $parameters ) {
        foreach( $parameters as $p ) {
          list( $key, $val ) = explode( "=", $p );

          $args[ $key ] = $val;
        }
      }

      if( isset( $args[ 'lang' ] ) )
        unset( $args[ 'lang' ] );

      //Avoid to calculate this array for each call...
      CMLUtils::_set( '_query_string', $args );
    } else {
      $args = CMLUtils::_get( '_query_string', array() );
    }
  }

  if( ! is_object( $lang ) ) {
      $lang = CMLLanguage::get_by_slug( $lang );
  }

  if( $queried && ( cml_is_homepage() || is_search() ) ) { //&& cml_use_static_page() ) {rs
    //current page is homepage?
    $link = CMLUtils::get_home_url( $lang->cml_language_slug );

    /*
     * on mobile detect the language correctly only if the
     * link end with "/"...
     */
    if( CMLUtils::get_url_mode() == PRE_PATH ) {
      $link = trailingslashit( $link );
    }

    /*
     * If url mode == PRE_PATH and "Ignore for default language" option is enabled I have to ?lang slug to $link,
     * because I need to say the plugin that user choosed default language and I haven't redirect it to his browser
     * language...
     */
    if( $_cml_settings[ 'url_mode_remove_default' ] == 1 &&
        CMLLanguage::is_default( $lang->id ) &&
        CMLUtils::get_url_mode() == PRE_PATH ) {

      $args[ 'lang' ] = $lang->cml_language_slug;
      $link = esc_url( add_query_arg( $args, trailingslashit( $link ) ) );
    }
    if( is_search() ) {
      if( isset( $_GET[ 's' ] ) ) {
        $args[ 's' ] = esc_attr( $_GET[ 's' ] );
      }

      if( CMLUtils::get_url_mode() <= PRE_LANG ) {
        $args[ 'lang' ] = $lang->cml_language_slug;
      }

      $link = esc_url( add_query_arg( $args, trailingslashit( $link ) ) );
    }
  } else {
    $GLOBALS[ '_cml_force_home_slug' ] = $lang->cml_language_slug;
    CMLUtils::_set( "_forced_language_slug", $lang->cml_language_slug );
    CMLUtils::_set( "_forced_language_id", $lang->id );

    //I have to force language to $lang one
    $wpCeceppaML->force_category_lang( $lang->id );

    if( $queried ) {
      if( empty( $GLOBALS[ '_cml_get_queried_object' ] ) ) {
        $GLOBALS[ '_cml_get_queried_object' ] = get_queried_object();
      }
      $q = & $GLOBALS[ '_cml_get_queried_object' ];

      $is_tag = isset( $q->taxonomy ) && "post_tag" == $q->taxonomy;
      if( ! $is_tag ) {
        $is_category = isset( $q->taxonomy );
      } else {
        $is_category = false;
      }

      /*
       * added for detect woocommerce "shop" page
       */
      $is_single = apply_filters( 'cml_is_single_page', isset( $q->ID ), $q );
      $is_page = $is_single;

      if( $is_single && ! isset( $q->ID ) ) {
        $the_id = apply_filters( 'cml_get_custom_page_id', 0, $q );
      } else {
        $the_id = ( $is_single ) ? $q->ID : 0;
      }

      if( empty( $q ) ) {
        $is_404 = is_404();
      }
    } else {
      $q = null;

      $is_category = is_category();
      $is_single = is_single();
      $is_page = is_page();
      $the_id = get_the_ID();
      $is_404 = is_404();
      $is_tag = is_tag();
      $is_archive = is_archive();
    }

    /* Collego la categoria della lingua attuale con quella della linga della bandierina */
    $link = "";

    if( ! in_the_loop() ) {
      $lang_id = CMLLanguage::get_current_id();
    } else {
      $lang_id = CMLLanguage::get_id_by_post_id( $the_id );
    }

    /*
     * I must check that is_category is false, or wp will display 404
     * is_single is true also for category and in this case
     * the plugin will return wrong link
     */
    if( ( ( $is_single || $is_page ) ||  $linked ) && ! $is_category ) {
      $linked_id = CMLPost::get_translation( $lang->id, $the_id );

      if( ! empty( $linked_id ) ) {
        $link = get_permalink( $linked_id );

        $link = CMLPost::remove_extra_number( $link, get_post( $linked_id ) );

        /*
         * Ignore for default language mode doesn't works properly
         * ( doesn't add /##/ to "translated" link )
         */
        if( CMLUtils::get_url_mode() == PRE_PATH &&
            $_cml_settings[ 'url_mode_remove_default' ] == 1 ) {
            $link = $wpCeceppaML->convert_url( $link, $lang->cml_language_slug );
        }

        if( CMLUtils::_get( '_real_language' ) != CMLLanguage::get_current_id()
            && $linked_id == $the_id ) {

          if( CMLUtils::get_url_mode() == PRE_PATH ) {
            $link = $wpCeceppaML->convert_url( $link, $lang->cml_language_slug );
          }
        }
      }
    }

    if( $is_archive && ! $is_category ) { //&& ! is_post_type_archive() ) {
      global $wp;

      $link = trailingslashit( home_url( $wp->request ) );

      if( CMLUtils::get_url_mode() == PRE_NONE ||
          CMLUtils::get_url_mode() == PRE_LANG ) {
        $link = add_query_arg( array( "lang" => $lang->cml_language_slug ), $link );
      }
    }

    //Collego le categorie delle varie lingue
    if( $is_category ) {
      if( $queried && isset( $q->term_id ) ) {
    	$cat = array(
                    "term_id" => $q->term_id,
                    "taxonomy" => $q->taxonomy,
                    );
      } else {
    	$cat = get_the_category();
      }

      if( is_array( $cat ) ) {
        $cat_id = ( isset( $cat[ 'term_id' ] ) ) ? $cat[ 'term_id' ] : ( $cat[ count($cat) - 1 ]->term_id );

//        if( CML_STORE_CATEGORY_AS == CML_CATEGORY_AS_STRING ) {
          //Mi recupererà il link tradotto dal mio plugin ;)
          CMLUtils::_set( '_force_category_lang', $lang->id );
//        } else {
//          //Get translated category
//          $cat_id = (int) CMLTranslations::get_linked_category( $cat_id, $lang->id );
//        }

        $link = get_term_link( $cat_id, $cat[ 'taxonomy' ] );

        //if is object, it's an Error
        if( is_object( $link ) ) $link = "";

        CMLUtils::_del( '_force_category_lang' );
      } //endif;

      if( CMLUtils::get_category_url_mode() == PRE_LANG &&
          CMLUtils::get_url_mode() == PRE_NONE ) {
        $link = esc_url( add_query_arg( array( 'lang' => $lang->cml_language_slug ), $link ) );
      }
    }

    if( $queried && $is_tag ) { //&& false !== strpos( CMLUtils::get_clean_url(), "/tag/" ) ) ) {
      if( ! empty( $q ) ) {
        $term_id = $q->term_id;
      } else {
        $term_id = CMLUtils::_get( "_reverted" );
      }

      if( ! empty( $term_id ) ) {
        CMLUtils::_set( '_force_category_lang', $lang->id );

        $link = get_tag_link( $term_id );

        CMLUtils::_del( '_force_category_lang' );
      }
    }

    if( is_paged() ) {
      $link = esc_url( add_query_arg( array( "lang" => $lang->cml_language_slug ) ) );

      // it the language is pre_path there is no need to add the query string. This will fix 404 when navigating between post pages
      if( CMLUtils::get_url_mode() != PRE_PATH ) {
        $link = esc_url( add_query_arg( array( "lang" => $lang->cml_language_slug ) ) );
      }
    }

    unset( $GLOBALS[ '_cml_force_home_slug' ] );

    CMLUtils::_del( "_forced_language_slug" );
    CMLUtils::_del( "_forced_language_id" );

    $wpCeceppaML->unset_category_lang();

    /* Controllo se è stata impostata una pagina statica,
      	perché così invece di restituire il link dell'articolo collegato
      	aggiungo il più "bello" ?lang=## alla fine della home.

      	Se non ho trovato nesuna traduzione per l'articolo, la bandiera punterà alla homepage
    */
    if( empty( $link ) && ! $only_existings ) {
      if( 1 === CMLUtils::_get( "is_crawler" ) ) {
        return;
      }

      //If post doesn't exists in current language I'll return the link to default language, if exists :)
      if( $_cml_settings[ 'cml_force_languge' ] == 1 ) {
        /*
         * return translation, if exists :)
         */
        if( $is_single || $is_page ) {
          $l = cml_get_linked_post( $the_id, CMLLanguage::get_default_id() );

          if( $l == $the_id || $l == 0 ) {
            $lang = array( "lang" => $lang->cml_language_slug );
            $args = array_merge( $lang, $args );
            return esc_url( add_query_arg( $args, get_permalink( $l ) ) );
          }
        }

        /*
         * no translation found, and user choosed to force page to flag language,
         * I add parameter "lang=##" to url
         */
        $http = ( ! is_ssl() ) ? "http://" : "https://";
        $link = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if( CMLPost::get_language_by_id( $the_id ) != $lang->id ) {
          //Is internal link?
          //if( strpos( $link, CMLUtils::get_home_url() ) === FALSE ) {
            //$link = add_query_arg( array( "lang" => $lang->cml_language_slug ), $link );
          //} else {
            $link = str_replace( CMLUtils::get_home_url( CMLLanguage::get_current_slug() ),
                                 CMLUtils::get_home_url( $lang->cml_language_slug ),
                                 $link );
          //}
        }
      } else {
        $link = CMLUtils::get_home_url( $lang->cml_language_slug );
      }

      if( ( $is_tag || ( isset( $is_404 ) && $is_404 ) ) && CMLUtils::get_url_mode() > PRE_LANG ) {
        $clean = CMLUtils::get_clean_url();
        $url = CMLUtils::home_url();

        //Change slug in url instead of append ?lang arg
        $link = str_replace( $url, "", $clean );
        $link = CMLUtils::get_home_url( $lang->cml_language_slug ) . $link;
      }
    }

    if( empty( $link ) && $only_existings ) {
      return '';
    }

    $link = apply_filters( 'cml_get_the_link', $link, array(
                                                    "is_single" => $is_single,
                                                    "is_category" => $is_category,
                                                    "is_tag" => $is_tag,
                                                    "the_id" => $the_id,
                                                    ),
                            $q, $lang->id );
  }

  // Get the last character from the permalink structure definition
  $permalink_structure = CMLUtils::get_permalink_structure();
  if ( $permalink_structure !== '' ) {
    if ( substr( $permalink_structure, -1 ) != '/' ) {
      $link = untrailingslashit( $link );
    }
  }

  return esc_url( add_query_arg( $args, trailingslashit( $link ) ) );
}

/**
 * @ignore
 * @internal
 *
 * use static page?
 */
function cml_use_static_page() {
  return (get_option("page_for_posts") > 0) ||
	  (get_option("page_on_front") > 0);
}

/**
 * grab browser language
 *
 * @return string
 */
function cml_get_browser_lang() {
  if( isset( $GLOBALS[ '_browser_lang' ] ) ) return $GLOBALS[ '_browser_lang' ];

  global $wpdb;

  $browser_langs = @explode( ";", $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
  $lang = null;

  //Se la lingua del browser coincide con una di quella attuale della pagina, ignoro tutto
  foreach( $browser_langs as $blang ) {
    @list( $code1, $code2 ) = explode( ",", $blang );

    $locale[] = str_replace( "-", "_", $code1 );
    $locale[] = str_replace( "-", "_", $code2 );

    //Per ogni codice che trovo verifico se è gestito, appena ne trovo 1 mi fermo
    //Perché il mio browser mi restituisce sia it-IT, che en-EN, quindi mi devo fermare appena trovo un riscontro
    //Senno mi ritrovo sempre la lingua en-EN come $browser_langs;
    $i = 0;
    while( empty( $lang ) && $i < count( $locale ) ) {
      $l = $locale[$i];

      if( strlen( $l ) > 2 ) {
        $lang = CMLLanguage::get_id_by_locale( $l );
      } else {
        //Se ho solo 2 caratteri, cerco negli "slug"
        $lang = CMLLanguage::get_id_by_slug( $l );
      }

      $i++;
    } //endwhile;

    if( ! empty ($lang ) ) {
      break;
    }
  }  //endforeach;

  $GLOBALS[ '_browser_lang' ] = $lang;

  return $lang;
}

/**
 * return post/page notice in selected language
 *
 * @param sting $lang_slug - language slug
 *
 * @return string return translated notice
 */
function cml_get_notice( $lang ) {
  global $wpdb, $wpCeceppaML;

  //$type - type of notice ( post or page )
  $type = ( is_single() ) ? "post" : "page";

  $r = CMLTranslations::get( $lang,
                            "_notice_$type",
                            "N", true, true );

  if( ! empty( $r ) )
    return $r;
  else
    CMLLanguage::get_current()->cml_language;
}

/**
 * Return flag &lt;ul&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt; list
 *
 * @param array $args is parameters list:
 *              <ul>
 *                <li>
 *                  show ( string ) - choose what to display:<br />
 *                  <i>default: text</i>
 *                </li>
 *                <ul>
 *			         <li>text: show only language name</li>
 *			         <li>slug: show language slug</li>
 *			         <li>none: show no text</li>
 *			     </ul>
 *			     <li>
 *			      show_flag ( boolean ) - show flag?<br />
 *                <i>default: true</i>
 *			     </li>
 *			     <li>size ( string ) - flag size<br />
 *			      you can use constants:<br />
 *			        CML_FLAG_TINY ( 16x11 )<br />
 *			        CML_FLAG_SMALL ( 32x23 )<br />
 *                  <i>default: CML_FLAG_TINY</i>
 *			      <ul>
 *			        <li>tiny</li>
 *			        <li>small</li>
 *			      </ul>
 *               <li>
 *                class_name ( string ) - secondary classname to assign to the list
 *                <i>default: ""</i>
 *                generated &gt;ul&lt; list has primary class "cml_flags", with that parameter you
 *                can assign a secondary one.
 *               </li>
 *               <li>
 *                echo ( boolean ) - If true print list, otherwise return string containing generated list<br />
 *                <i>default: true</i>
 *               </li>
 *               <li>
 *                linked ( boolean ) - If true flags link to current translation, false will link to homepage<br />
 *                <i>default: true</i>
 *               </li>
 *               <li>
 *                only_existings ( boolean ) - show only flags in which current page exists.<br />
 *                <i>default: false</i>
 *               </li>
 *               <li>
 *                queried ( boolean ) - use queried object instead of get_the_ID() or other methods, so output will be
 *                                      generated in according to main query, not current one.
 *                <i>default: false</i>
 *               <li>
 *			    </ul>
 */
function cml_show_flags( $args ) {
  global $wpdb;

  $args = extract( shortcode_atts( array(
                      "show" => "text",
                      "size" => "tiny",
                      "class" => "",
                      "image_class" => "",
                      "echo" => true,
                      "linked" => true,
                      "only_existings" => false,
                      "sort" => false,
                      "queried" => true,
                      "show_flag" => true,
                      ), $args ) );

  $_cml_settings = & $GLOBALS[ '_cml_settings' ];
  $redirect = $_cml_settings[ 'cml_option_redirect' ];

  $results = cml_get_languages( true, false );
  $width = ( $size == "tiny" ) ? 16 : 32;

  $r = "<ul class=\"cml_flags $class\">";

  //Post language...
  $lang_id = ( ! $sort ) ? -1 : CMLPost::get_language_by_id( get_the_ID() );
  $items = array();

  foreach($results as $result) {
    $lang = ( $show == "text" ) ? $result->cml_language : "";
    $lang = ( $show == "slug" ) ? $result->cml_language_slug : $lang;

    $link = cml_get_the_link( $result, $linked, $only_existings, $queried );
    if( empty( $link) ) continue;

    if( $show_flag ) {
      $img = sprintf( '<img class="%s %s" src="%s" title="%s" alt="%s" width="%s" />',
                     $size, $image_class, CMLLanguage::get_flag_src( $result->id, $size ),
                     $result->cml_language,
                     sprintf( __( '%1$ flag', 'ceceppaml' ), $result->cml_language_slug ),
                     $width );

      //$img = "<img class=\"$size $image_class\" src=\"" . cml_get_flag_by_lang_id( $result->id, $size ) . "\" title='$result->cml_language' width=\"$width\"/>";
    } else {
      $img = "";
    }

    $class = ( $result->id == CMLLanguage::get_current_id() ) ? "current" : "";
    $slug = ( CMLLanguage::is_default( $result ) ) ? "x-default" : str_replace( "_", "-", $result->cml_language_slug );
    $li = "<li class=\"$class\"><a rel=\"alternate\" href=\"$link\" hreflang=\"{$slug}\">{$img}{$lang}</a></li>";
    if( $sort && is_array( $items ) && $result->id == $lang_id ) {
      array_unshift( $items, $li );
    } else {
      $items[] = $li;
    }

  } //endforeach;


  $r .= join( "\n", $items );
  $r .= "</ul>";

  if( $echo )
    echo $r;
  else
    return $r;
}

/**
 * @ignore
 *
 * Check if current post is a custom post
 */
function cml_is_custom_post_type() {
  $types = get_post_types( array ( '_builtin' => FALSE ), 'names' );

  if( empty( $types) ) return FALSE;

  $name = get_post_type();
  return in_array( $name, $types );
}

/**
 * @ignore
 */
function removesmartquotes($content) {
  $content = str_replace('&#8220;', '&quot;', $content);
  $content = str_replace('&#8221;', '&quot;', $content);
  $content = str_replace('&#8216;', '&#39;', $content);
  $content = str_replace('&#8217;', '&#39;', $content);

  return $content;
}

/**
 * @ignore
 *
 * http://www.cult-f.net/detect-crawlers-with-php/
 *
 * Questa funzione server per evitare di reindirizzare o nascondere i post nella lingua differente
 * da quella del browser se schi stà visitando il sito è un crawler, al fine di permettere l'indicizzazione di tutti
 * gli articoli
 *
 */
function isCrawler()
{
  global $wp_query;

  if( ! empty( $wp_query ) && $wp_query->is_robots() ) {
    CMLUtils::_set( "is_crawler", 1 );

    return true;
  }

    $USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

    // to get crawlers string used in function uncomment it
    // it is better to save it in string than use implode every time
    // global $crawlers
    // $crawlers_agents = implode('|',$crawlers);
    $crawlers_agents = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby';

    if ( strpos($crawlers_agents , $USER_AGENT) === false )
       return false;
    // crawler detected
    // you can use it to return its name
    /*
    else {
       return array_search($USER_AGENT, $crawlers);
    }
    */
    return true;
}

/**
 * return the id of menu to use in according to current language
 * return value can be used as wp_nav_menu function.
 *
 * The plugin automatically switch menu in according to current language,
 * you can use this function if automatic switch doesn't works with your theme/framework
 * or if you to force a theme.
 *
 * @example
 * <?php;<br />
 *	$menu = cml_get_menu();<br />
 *	wp_nav_menu(array('theme_location' => $menu));<br />
 * ?>
 */
function cml_get_menu() {
  //Restituisco il nome del menù da utilizzare a seconda della lingua
  $lang = cml_get_current_language();

  return "cml_menu_" . $lang->cml_language_slug;
}

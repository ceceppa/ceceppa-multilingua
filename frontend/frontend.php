<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once CML_PLUGIN_FRONTEND_PATH . "utils.php";
require_once CML_PLUGIN_FRONTEND_PATH . "deprecated.php";

//Shortcodes & Widgets
require_once CML_PLUGIN_INCLUDES_PATH . "shortcodes.php";
require_once CML_PLUGIN_INCLUDES_PATH . "widgets.php";

class CMLFrontend extends CeceppaML {
  protected $_redirect_browser = 'auto';
  protected $_show_notice = 'notice';
  protected $_filter_search = true;
  protected $_filter_form_class = "#searchform";
  protected $_no_translate_menu_item = false;

  //Is wp >= 4.2.0?
  protected $is_42 = false;

  public function __construct() {
    parent::__construct();

    global $_cml_settings, $wp_version;

    //Frontend scripts and style
    add_action( 'wp_enqueue_scripts', array( &$this, 'frontend_scripts' ) );

    //Change wordpress locale & right to left
    add_filter( 'locale', array( & $this, 'setlocale' ), 0, 1 );
    add_action( 'plugins_loaded', array( &$this, 'setup_rtl' ), 1 );

    //redirect browser
    $this->_redirect_browser = $_cml_settings[ 'cml_option_redirect' ];
    if( $this->is_homepage() &&
       ! isset( $_GET[ 'lang' ] ) &&
       $this->_redirect_browser != 'nothing' ) {
      add_action( 'plugins_loaded', array( &$this, 'redirect_browser' ), 0 );
    }

    //Filter posts
    if( $_cml_settings[ 'cml_option_filter_posts' ] < FILTER_NONE ) {
      if( $_cml_settings[ 'cml_option_filter_posts' ] == FILTER_BY_LANGUAGE ||
          $_cml_settings[ 'cml_option_filter_posts' ] == FILTER_HIDE_EMPTY ) {
        add_action( 'get_pages', array ( &$this, 'filter_get_pages' ), 0, 2 );
        add_action( 'pre_get_posts', array( &$this, 'filter_posts_by_language' ), 0 );
      } else {
        add_action( 'pre_get_posts', array( & $this, 'hide_translations' ), 0 );
      }
    } else {
      //If is_feed I need to force posts filtering
      add_action( 'pre_get_posts', array( & $this, 'check_is_feed' ), 0 );
    }

    //Posts to not filter
    $this->_excluded_post_types = apply_filters( '_cml_ignore_post_type', array() );

    /*
    * If one or more post has same "title", wordpress add "-##" to end of permalink
    * I remove "-##" from the end of permalink, but I have to inform wordpress
    * which is the corrent "post_name"
    */
    if( ! empty( $this->_permalink_structure ) ) {
      add_action( 'pre_get_posts', array( & $this, 'is_duplicated_post_title' ), 0, 1 );
    }

    //Translate term
    add_filter( 'get_term', array( & $this, 'translate_term' ), 10, 2 );
    add_filter( 'get_terms', array( & $this, 'translate_terms' ), 10, 3 );
    add_filter( 'get_the_terms', array( & $this, 'translate_terms' ), 0, 3 );
    add_filter( 'list_cats', array(&$this, 'translate_category' ), 10, 2 ); //for translate categories widget
    add_filter( 'post_link_category', array( & $this, 'post_link_category' ), 10, 3 );
    add_filter( 'single_cat_title', array( & $this, 'translate_single_taxonomy_title' ), 10, 1 );
    add_filter( 'single_tag_title', array( & $this, 'translate_single_taxonomy_title' ), 10, 1 );
    add_filter( 'single_term_title', array( & $this, 'translate_single_taxonomy_title' ), 10, 1 );

    //Since WP > 4.2.0
    $this->is_42 = $wp_version > '4.2.0';
    if( $this->is_42 )
      add_filter( 'get_object_terms', array( & $this, 'translate_terms' ), 10, 2 );
    else
      add_filter( 'wp_get_object_terms', array( & $this, 'translate_terms' ), 10, 2 );

    // add_filter( 'single_term_title', array( & $this, 'translate_sintle_term_title' ), 10, 1 );
    // add_filter( 'single_tag_title', array( & $this, 'translate_single_cat_title' ), 10, 1 );

    //For PRE_NONE I have to add slug at the end of category link
    if( $this->_category_url_mode == PRE_LANG ) {
      add_filter( 'term_link', array( &$this, 'translate_term_link' ), 0, 3 );
    }

    //change category/tag translation with its original name
    if( ! empty( $this->_permalink_structure ) ) {
      add_action( 'pre_get_posts', array( & $this, 'change_taxonomy_name'), 0, 1 );
    }

    //Show notice?
    $this->_show_notice = $_cml_settings[ 'cml_option_notice' ];
    $this->_show_notice_pos = $_cml_settings[ 'cml_option_notice_pos' ];
    if( $this->_show_notice != 'nothing' )
      add_action( 'the_content', array( &$this, 'show_notice' ) );

    //Date format
    if( $_cml_settings[ 'cml_change_date_format' ] ) {
      add_filter( 'get_the_date', array( &$this, 'get_the_date' ), 0, 2 );
      add_filter( 'get_the_time', array( &$this, 'get_the_date' ), 0, 2 );
      add_filter( 'get_comment_date', array( &$this, 'get_comment_date' ), 0, 2 );
    }

    //Show flags on
    /*
     * From 1.5 user can override the flag setting for a single post, so I can't
     * check it here, as I don't know the post id, yet...
     */
    add_filter( "the_content", array( & $this, 'add_flags_on_content' ), 10, 1 );
    add_filter( "the_title", array( &$this, 'add_flags_on_title' ), 10, 2 );

    /*
     * filter search by language
     */
    $this->_filter_search = $_cml_settings[ 'cml_option_filter_search' ];
    if( $this->_url_mode <= PRE_LANG && $this->_filter_search ) {
      add_action( 'get_search_form', array( & $this, 'get_search_form' ), 0, 1 );
      add_action( 'wp_enqueue_scripts', array( & $this, 'enqueue_search_script' ) );
    }

    //group/ungroup Comments
    $this->_comments = $_cml_settings[ 'cml_option_comments' ];
    if( 'group' == $this->_comments ) {
      add_filter( 'query', array( & $this, 'get_comments' ) );

      /*
      * Per i post "collegati" recupero il numero corretto di commenti facendo una somma di
      * tutti quelli presenti nei vari post.
      * Solo però se l'utente sceglie di "raggruppare" i commenti
      */
      add_filter( 'get_comments_number', array( & $this, 'get_comments_number' ) );
    } //endif;

    /*
     * Used static page?
     * If yes I change the id of page with its translation
     */
    if( cml_is_homepage() && cml_use_static_page() && ! isset( $_GET[ 'preview' ] ) ) {
      add_filter( 'pre_get_posts', array( & $this, 'change_static_page' ), 0 );
    }

    //Archive links
    add_filter( 'getarchives_where', array( & $this, 'get_archives_where' ), 10, 2 );

    /*
     * For PRE_LANG mode I have to check pagenum_link becase suffix is appended before page value.
     *
     * Example:
     * www.example.com/?lang=en/2/
     *
     */
    if( $this->_url_mode == PRE_LANG && ! empty( $this->_permalink_structure ) ) {
      add_filter( 'wp_link_pages_args', array( & $this, 'wp_link_pages_args' ) );
      add_filter( 'wp_link_pages', array( & $this, 'wp_link_pages' ) );
      add_filter( 'clean_url', array( & $this, 'wp_clean_url' ), 0, 3 );
    }

   /* MENU */
    //I have to add flags to menu?
    if( $_cml_settings[ "cml_add_flags_to_menu" ] == 1 ) {
      add_filter( 'wp_nav_menu_items', array( & $this, "add_flags_to_menu" ), 10, 2 );
    }

    /*
     * hide menu items that doesn't exists in current language
     * and replace "CeceppaML: Flags" items ( #cml-lang ) with
     * its value
     */
    add_filter( 'wp_nav_menu_objects', array( & $this, 'get_nav_menu_items' ), 0, 2 );

    //Translate menu items
    if( $_cml_settings[ "cml_option_action_menu" ] ) {
      add_filter('wp_setup_nav_menu_item', array( &$this, 'translate_menu_item' ), 0, 1 );
    }

    //For menu I need to force category lang for get url translated :)
    add_filter( 'wp_nav_menu_args', array( & $this, 'pre_get_menu' ), 0, 1 );
    add_filter( 'wp_nav_menu', array( & $this, 'end_get_menu' ), 0, 2 );

    //I have to append flag to html element?
    if( $_cml_settings[ "cml_append_flags" ] == true ) {
      add_action( 'wp_footer', array( & $this, 'append_flags_to_element' ) );
    }

    //Flying div?
    if( $_cml_settings[ "cml_add_float_div" ] == true ) {
      add_action( 'wp_footer', array( & $this, 'add_flying_flags' ) );
    }

    //Site title & tagline
    add_filter( 'bloginfo', array( & $this, 'translate_bloginfo' ), 0, 1 );

    //Filter widgets by language
    add_filter( 'sidebars_widgets', array( & $this, 'filter_widgets' ), 0, 1 );

    //Next and Prev post
     add_filter( 'get_previous_post_where', array( &$this, 'get_previous_next_post_where' ) );
     add_filter( 'get_next_post_where', array( &$this, 'get_previous_next_post_where' ) );

    /*
     * I can't translate home in backed because if user change permalink got
     * 500 error :(
     * Translate home_url in accordingly to current language
     */
    add_filter( 'home_url', array( & $this, 'translate_home_url' ), 0, 4 );

    //Get translated media fields
    add_filter( 'wp_get_attachment_image_attributes', array( & $this, 'get_translated_media_fields' ), 10, 2 );
    add_filter( 'the_title', array( & $this, 'get_translated_title' ), 10, 2 );

    //Add current language to the body classes
    add_filter( 'body_class', array( & $this, 'add_current_language' ) );

    //Add hreflang tag
    add_action('wp_head', array( & $this, 'add_hreflang') );

    /*
     * Sometimes wp doesn't redirect a page to it's "correct" language link.
     * And so, the page is available with and withouth the language slug...
     * And even if the user choosed to translate the permalink, the page is "available"
     * with original and translated permalink...
     * This code jst check if a redirect if required, if so force it...
     */
     if( ! empty( $this->_permalink_structure ) && get_option( 'cml_force_redirect', false ) ) {
       add_action( 'template_redirect', array( & $this, 'force_redirect' ), 99 );
     }

     /*
     * Filtro alcune query per lingua (Articoli più letti/commentati)
     */
     if( $_cml_settings['cml_option_filter_query'] ) {
       add_filter('query', array(&$this, 'filter_query'));
     }

     /*
      * Unlike other multilingual plug-ins in ceceppa I don't create a new category
      * when a user translate it. But store it as normal text in the ###_ceceppaml_cats table,
      * and let the plug-in to replace the slug and the translation in accordin to the current language.
      * Now this method has a big problem, when using the function get_term_by( 'slug' ), you'll get a
      * WP_Error object instead of the WP_Term object.
      *
      * I tried to modify the plugin to let it create the new category, but I found it too complex
      * to do, as I like easy stuffs...
      *
      * So I just figure out a great solution, so I think :D.
      * I'll intercept all "queries" and wherever I'll find the string t.slug, I'm gonna replace
      * the value with the untranslated ones, so everybody we'll be happy :)
      */
      // if( ! CMLLanguage::is_default() ) {
        add_filter( 'query', array( &$this, 'change_get_term_by_query' ) );
      // }
  }

  /*
   * insert lang field to form
   */
  function get_search_form( $form ) {
    $slug = CMLLanguage::get_current_slug();

    $input = '<div>';
    $input .= '<input type="hidden" name="lang" value="' . $slug . '" />';

    return preg_replace( "/\<div\>/", $input, $form );
  }

  function enqueue_search_script() {
    global $_cml_settings;

    $class = $_cml_settings[ 'cml_option_filter_form_class' ];
    if( empty( $class ) ) return;

    $array = array( 'lang' => CMLLanguage::get_current_slug(),
                    'form_class' => $class );

    wp_enqueue_script( 'ceceppaml-search', CML_PLUGIN_JS_URL . 'ceceppaml.search.js', array( 'jquery' ) );
    wp_localize_script( 'ceceppaml-search', 'cml_search', $array );
  }

  function frontend_scripts() {
	if( file_exists( CML_UPLOAD_DIR . "/float.css" ) )
	  wp_register_style( 'ceceppaml-flying', CML_UPLOAD_URL . 'float.css' );
	else
	  wp_register_style( 'ceceppaml-flying', CML_PLUGIN_URL . 'css/float.css');

    //wpml-config combo style
    if( file_exists( CML_UPLOAD_DIR . "combo_style.css" ) )
        wp_enqueue_style( 'ceceppaml-wpml-combo-style', CML_UPLOAD_URL . "combo_style.css" );
  }

  /*
   * add flags before or after the title
   */
  function add_flags_on_title( $title, $id = -1 ) {
    global $_cml_settings;

    //If SEO meta tag title is empty, yoast call the_title, so I have to avoid
    //to put the flags in the header...
    if( 1 !== CMLUtils::_get( '_after_wp_head') ) return $title;

    if( ( ! is_singular() && ! cml_is_custom_post_type() ) ||
        is_archive() || is_feed() ) {
      return $title;
    }

    //flags already applied
    if( isset( $this->_title_applied ) && is_singular() ) return $title;

    //Where?
    $where = $_cml_settings[ 'cml_option_flags_on_pos' ];

    //The post override current flags settings?
    $override = false;
    if( is_singular() ) {
      $o = get_post_meta( get_the_ID(), "_cml_override_flags", true );

      if( is_array( $o ) ) {
        if( $o['show'] == 'never' ) return $title;
        if( $o['show'] == 'show' ) {
          $where = $o[ 'where' ];
          $override = true;
        }
      }
    }

    if( $id < 0 ) return $title;

    if( ! $override ) {
      if( in_array( $_cml_settings[ 'cml_option_flags_on_pos' ], array( 'bottom', 'top' ) ) ) return $title;
      if( ! $_cml_settings[ 'cml_option_flags_on_post' ] && is_single() ) return $title;
      if( ! $_cml_settings[ 'cml_option_flags_on_page' ] && is_page() ) return $title;
      if( ! $_cml_settings[ 'cml_option_flags_on_custom_type' ] &&
         cml_is_custom_post_type() ) return $title;
    }

    if( ( ! $_cml_settings[ 'cml_option_flags_on_the_loop' ] && ( cml_is_homepage() ) )
          || is_category() ) return $title;

    global $post;

    /*
     * this filter is called many times, not only for single post title, so
     * I have to check that $title is the title of current post
     */
    if( ! is_object( $post ) ) return $title;
    if( esc_attr( $post->post_title ) == removesmartquotes( $title ) ) {
      //Done, remove the filter :)
      remove_filter( "the_title", array( &$this, 'add_flags_on_title' ), 10, 2 );
      $this->_title_applied = true;

      $size = $_cml_settings['cml_option_flags_on_size'];

      $args = array( "class" => "cml_flags_on_title_" . $_cml_settings[ 'cml_option_flags_on_pos' ],
                      "size" => $size, "sort" => true, "echo" => false );
      $flags = ( $_cml_settings[ 'cml_options_flags_on_translations' ] ) ?
                              cml_shortcode_other_langs_available( $args ) :
                              cml_show_available_langs( $args );

      if( 'after' == $where ) {
        return $title . $flags;
      } else {
        return $flags . $title;
      }
    } //endif;

    return $title;
  }

  /*
   * add flags on post content
   */
  function add_flags_on_content( $content ) {
    global $_cml_settings;

    //Avoid to put flags into the header meta description field, for a post with empty SEO...
    if( is_feed() || 1 !== CMLUtils::_get( '_after_wp_head') ) return $content;

    //The post override current flags settings?
    $override = false;
    if( is_singular() ) {
      $o = get_post_meta( get_the_ID(), "_cml_override_flags", true );

      if( is_array( $o ) ) {
        if( $o['show'] == 'never' ) return $content;
        if( $o['show'] == 'show' ) {
          $where = $o[ 'where' ];
          $override = true;
        }
      }
    }

    if( ! $override ) {
      if( ! in_array( $_cml_settings[ 'cml_option_flags_on_pos' ], array( 'bottom', 'top' ) ) ) return $content;
      if( ! $_cml_settings['cml_option_flags_on_post'] && is_single() ) return $content;
      if( ! $_cml_settings[ 'cml_option_flags_on_page' ] && is_page() ) return $content;
      if( ! $_cml_settings[ 'cml_option_flags_on_custom_type' ] &&
         cml_is_custom_post_type() ) return $content;
    }

    if( ( ! $_cml_settings[ 'cml_option_flags_on_the_loop' ] && ( cml_is_homepage() ) )
          || is_category() ) return $content;

    $size = $_cml_settings['cml_option_flags_on_size'];
    $args = array(
                  "class" => "cml_flags_on_top",
                  "size" => $size,
                  "sort" => true,
                  );

    $flags = ( $_cml_settings[ 'cml_options_flags_on_translations' ] ) ?
                          cml_shortcode_other_langs_available( $args ) :
                          cml_show_available_langs( $args );

    if( $_cml_settings[ 'cml_option_flags_on_pos' ] == "top" )
      return $flags . $content;
    else
      return $content . $flags;
  }

  /*
   * add flags to menu ( or submenu )
   */
  function add_flags_to_menu( $items, $args ) {
    global $_cml_settings;

    //In which menu add flags?
    $to = @$_cml_settings[ 'cml_add_items_to' ];
    if( ! is_array( $to ) ) $to = array( $to );
    if( empty( $to ) || ! in_array( $args->theme_location, $to ) ) return $items;

    //Add flags in submenu?
    if( $_cml_settings[ "cml_add_items_as" ] == 2 )
      return $this->add_flags_in_submenu( $items, $args );

    // if( ! in_array( $args->theme_location, $to ) ) return $items;

    $langs = CMLLanguage::get_enableds();
    $size = $_cml_settings[ "cml_show_in_menu_size" ];

    foreach($langs as $lang) {
      $items .= $this->add_item_to_menu( $lang, true, $size );
    }

    return $items;
  }

  /*
   * create submenu menu
   *
   * <ul>...</ul>
   */
  function add_flags_in_submenu( $items, $args ) {
    global $_cml_settings;

    $size = $_cml_settings["cml_show_in_menu_size"];

    //Current language
    $items .= $this->add_item_to_menu( CMLLanguage::get_current(), false, $size );

    //Submenu
    $items .= '<ul class="sub-menu">';

    //Other ones
    $langs = CMLLanguage::get_others();
    foreach( $langs as $lang ) {
      $items .= $this->add_item_to_menu( $lang, true, $size );
    }

    $items .= '</ul>';
    $items .= '</li>';

    return $items;
  }

  /*
   * add single item to menu
   *
   * return <li>...</li>
   */
  function add_item_to_menu( $lang, $close = true, $size = "small" ) {
    global $_cml_settings;

    $display = $_cml_settings[ "cml_show_in_menu_as" ];

    if( isset( $this->_force_post_lang ) )
      $old = $this->_force_post_lang;

    $this->_force_post_lang = $lang->id;
    $this->unset_category_lang();

    $url = cml_get_the_link( $lang, true, false, true );
    $img = ( $display % 2 ) ? CMLLanguage::get_flag_img( $lang->id ) : "";
    $name = ( $display < 3 ) ? $lang->cml_language : "";
    $slug = ( $display >= 4 ) ? $lang->cml_language_slug : "";

$item = <<< EOT
    <li class="menu-item menu-cml-flag">
      <a href="$url">
        $img
        <span>$name</span>
        $slug
      </a>
EOT;

    if( isset( $old ) ) {
      $this->_force_post_lang = $old;
    } else {
      unset( $this->_force_post_lang );
    }

    if( $close ) $item .= "</li>";

    return $item;
  }

  /*
   * append menu to html element
   *
   * I create div element with class "cml_append_flags" and when
   * page is ready ( jQuery( body ).ready(....) ), I append the
   * element to...
   */
  function append_flags_to_element() {
    global $_cml_settings;

    $appendTo = $_cml_settings[ "cml_append_flags_to" ];
    if( empty( $appendTo ) ) return;    //No element specified

    //what to shown
    $show = array( "", "text", "text", "flag", "slug", "slug" );
    $as = intval( $_cml_settings[ "cml_show_items_as" ] );
    $size = $_cml_settings[ "cml_show_items_size" ];

    $this->enqueue_script_append_to();
    wp_localize_script( 'ceceppa-append', 'cml_append_to', array( 'element' => $appendTo ) );

    echo '<div class="cml_append_flags" style="display: none">';
    if( $_cml_settings[ 'cml_show_html_items_style' ] <= 1 ) {
      cml_show_flags( array(
                            "class" => "cml_append_flags_to",
                            "show" => $show[$as],
                            "show_flag" => in_array( $as, array( 1, 3, 5 ) ),
                            "size" => $size,
                            "queried" => true,
                            ) );
    } else {
      cml_dropdown_langs( "cml_flags", CMLLanguage::get_current_id(), true, false, null, "", 0 );
    }
    echo '</div>';
  }

  /*
   * script used for append "cml_append_flags" to ...
   */
  function enqueue_script_append_to() {
    wp_enqueue_script( 'ceceppa-append', CML_PLUGIN_JS_URL . 'ceceppaml.append.js', array( 'jquery' ));
  }

  /*
   * add flying div element
   */
  function add_flying_flags() {
    global $_cml_settings;

    wp_enqueue_style( 'ceceppaml-flying' );

    $show = array( "", "text", "text", "flag", "slug", "slug" );
    $as = intval( $_cml_settings[ "cml_show_float_items_as" ] );
    $size = $_cml_settings[ "cml_show_float_items_size" ];
    $style = $_cml_settings[ "cml_show_float_items_style" ];

    echo '<div id="flying-flags">';
      if( $style == 1 ) {
        cml_show_flags( array(
                              "show" => $show[$as],
                              "show_flag" => in_array( $as, array( 1, 3, 5 ) ),
                              "size" => $size,
                              "queried" => true,
                              ) );
      } else {
        cml_dropdown_langs( "cml_flying_flags", CMLLanguage::get_current_id(),
                           true, false, null, "", true, $size, $style );
      }
    echo '</div>';
  }

  function get_comments($query) {
    if( FALSE === strpos( $query, 'comment_post_ID = ') )
    {
        return $query; // not the query we want to filter
    }

    if( ! is_object( get_post() ) ) return $query;

    global $wpdb;

    remove_filter( 'query', array( & $this, 'get_comments' ) );

    $linked = CMLPost::get_translations( get_the_ID() );

    if( empty( $linked ) || ! isset( $linked[ 'indexes' ] ) ) return $query;

    $replacement = 'comment_post_ID IN(' . implode( ',', $linked[ 'indexes' ] ) . ')';
    return preg_replace( '~comment_post_ID = \d+~', $replacement, $query );
  }

  /*
   * get comments count from all posts
   */
  function get_comments_number( $count ) {
    global $wpdb;

    if( CMLPost::is_unique( get_the_ID() ) ) return $count;
    $linked = CMLPost::get_translations( get_the_ID() );

    if( empty( $linked[ 'linked' ] ) ) return $count;

    //Eseguo la query
    asort( $linked[ 'indexes' ] );
    $ids = @implode( ",", $linked[ 'indexes' ] );
    $query = "SELECT count(*) FROM $wpdb->comments WHERE comment_approved = 1 AND comment_post_ID IN ( $ids )";

    $count = $wpdb->get_var($query);

    return $count;
  }

  function wp_link_pages_args( $args ) {
    $this->_numpage_slug = CMLPost::get_language_slug_by_id( get_the_ID() );
    return $args;
  }

  function wp_link_pages( $output ) {
    unset( $this->_numpage_slug );

    return $output;
  }

  function wp_clean_url( $good_protocol_url, $original_url, $_context ) {
    if( isset( $this->_numpage_slug ) ) {
      $good_protocol_url = esc_url( add_query_arg( array(
                                                "lang" => $this->_numpage_slug,
                                               ),
                                         $good_protocol_url ) );
    }

    return $good_protocol_url;
  }

  /*
   * Modifico l'id della query in modo che punti all'articolo tradotto
   */
  function change_static_page( $query ) {
    global $wpdb, $_cml_settings;

    if( isset( $this->_static_page ) ) return $this->_static_page;
    if( ! $query->is_main_query() || ! isset( $query->query_vars[ 'page_id' ] ) ) return;

    //TODO: remove get_queried_object()->ID and try to fix the cml_is_homepage()
    $queried = get_queried_object();
    if( is_object( $queried ) && isset( $queried->ID ) ) {
      if( ! cml_is_homepage( null, $queried->ID ) || is_search() ) return;
    } else {
      if( ! cml_is_homepage() || is_search() ) return;
    }

    //Recupero l'id della lingua
    $lang_id = CMLLanguage::get_current_id();

    //Static page id
    $id = get_option("page_on_front");
    $pid = get_option("page_for_posts");

    //Id of linked post
    $nid = CMLPost::get_translation( $lang_id, $id );
    $npid = CMLPost::get_translation( $lang_id, $pid );

    /*
     * Change the id of "page_on_front", so wordpress will add "home" to body_class :)
     */
    if( $nid > 0 ) {
      if( ! is_preview() ) {
        add_filter( 'body_class', array( & $this, 'add_home_class' ) );

        if( $_cml_settings[ 'cml_update_static_page' ] == 1 ) {
          update_option( 'page_on_front', $nid );
        }
      }
    } else {
      $nid = $id;
    }

    if( $npid > 0 ) {
      if( $_cml_settings[ 'cml_update_static_page' ] == 1 ) {
        update_option( 'page_for_posts', $npid );
      }
    } else {
      $npid = $pid;
    }

    $page_id = $query->query_vars[ 'page_id' ];

    if( CMLPost::is_translation ( $page_id, $nid ) ) {
      $query->query_vars[ 'page_id' ] = $nid;
    } else {
      $query->query_vars[ 'page_id' ] = $npid;
    }

    $query->query_vars[ 'is_home' ] = 1;

    $this->_static_page = $nid;
  }

  function add_home_class( $classes ) {
    $classes[] = " home";

    return $classes;
  }

  /*
   * Add the "lang-##" to the body tag
   */
  function add_current_language( $classes ) {
    $classes[] = " lang-" . CMLLanguage::get_current_slug();

    return $classes;
  }

  function get_archives_where( $where, $r ) {
    $posts = CMLPost::get_posts_by_language();
    if( empty( $posts ) ) return $where;

    $where .= " AND ID IN ( " . join( ",", CMLPost::get_posts_by_language() ) . ") ";

    return $where;
  }

  function translate_archives_link( $link ) {
    $url = preg_match('/href=[\"\'](.*)[\"\']/', $link, $match);

    $href = end( $match );
    $url = "$href?lang=" . CMLLanguage::get_current()->cml_language_slug . "'";

    $link = str_replace($href, $url, $link);
    return $link;
  }

  /*
   * remove language slug from REQUEST_URI or wp will generate 404 for pages.
   *
   * I don't remove for post or wp generate LOOP
   */
  function remove_language_slug() {
    $_SERVER['REQUEST_URI'] = $this->_clean_request;
  }

  /*
   * remove language information from url.
   * I need that function for use url_to_postid, because with language
   * information return always 0 :(
   */
  function clear_url() {
    global $wp_rewrite;

    if( $this->_url_mode != PRE_PATH
        || isset( $this->_clean_applied ) ) {
      if( empty( $this->_clean_url ) ) {
        $this->_clean_url = $this->_url;
      }

      return null;
    }

    $id = CMLUtils::clear_url();
    $this->_clean_url = CMLUtils::get_clean_url();

    if( ! empty( $id ) ) {
      $this->_language_detected_id = $id;
    }

    $this->_clean_applied = true;

    return $id;
  }

  /*
   * translate category name
   */
  function translate_category( $name, $category = null ) {
    if( isset( $this->_fake_language_id ) ) {
      $this->_force_category_lang = $this->_fake_language_id;
    }

    $lang = ( isset( $this->_force_category_lang ) ) ?
                  $this->_force_category_lang : CMLLanguage::get_current_id();

    //Default language? no translation required ;)
    if( $lang == CMLLanguage::get_default_id() ) return $name;

    if( $lang != CMLLanguage::get_current_id() ) {
      //If post language != current language I can't get translation from ".mo"
      $name = CMLTranslations::get( $lang, $name, "C", false, true );
    } else {
      $name = CMLTranslations::get( $lang, $name, "C" );
    }

    // $category = $this->translate_terms( array( 0 => $category ), null, null, $lang );

    return $name;
  }

  /*
   * translate single term
   */
  function translate_term( $term, $taxonomy ) {
    $id = ( is_object( get_post() ) ) ? get_the_ID() : 0;

    $terms = $this->translate_terms( array( 0 => $term ), $id, null );

    return $terms[0];
  }

  /*
   * translate term name and slug
   */
  function translate_terms( $terms, $post_id, $taxonomy = null, $lang_id = null ) {
    global $_cml_settings;

    $t = array();
    foreach( $terms as $term ) {
      if( ! is_object( $term ) ) {
        if( $this->is_42 ) {
          $t[] = $this->get_translated_term( $term, $lang_id, $post_id, $post_id[0] );
        } else {
          $tp[] = $term;
        }

        continue;
      }

      /*
       * if translated name == original name I don't update the slug.
       * Because if wp added -## to it I'll get an 404 page :(
       */
      $oname = $term->name;
      $tterm = $this->get_translated_term( $term, $lang_id, $post_id, $term->taxonomy );

      $term->name = $tterm->name;
      $term->description = $tterm->description;
      // $term->name = $this->translate_term_name( $term->name, $lang_id, $post_id, $term->taxonomy );

      if( $this->_category_url_mode != PRE_LANG &&
          null === CMLUtils::_get( '_no_translate_term' ) &&
          $term->slug != $tterm->slug ) {
          // $term->name != $oname ) {

          //For Woocommerce I don't have to translate
          $term->slug = $tterm->slug;
        // $term->slug = sanitize_title( strtolower( $term->name ) );
      }

      CMLUtils::_del( '_no_translate_term' );

      $t[] = $term;
    }

    /*
     * required to allow force language post
     */
    //unset( $this->_force_post_lang );
    return $t;
  }

  /* translate single title */
  function translate_single_taxonomy_title( $title ) {
    $term = get_queried_object();

    if( ! $term )
      return $title;

    unset( $this->_force_post_lang );
    unset( $this->_force_category_lang );

    return $this->get_translated_term( $title, null, null, $term->taxonomy );
  }

  /*
   * add language slug ?lang=## at end of category link for non default languages
   * or site will return in default one
   */
  function translate_term_link( $link, $term, $taxonomy ) {
    if( isset( $this->_fake_language_id ) ) {
      $home = CMLUtils::get_home_url();
      $chome = CMLUtils::get_home_url( CMLLanguage::get_slug( $this->_fake_language_id ) );

      $link = str_replace( $home, $chome, $link );
    }

    if( CMLLanguage::is_default( CMLUtils::_get( '_real_language' ) )
        || ( isset( $this->_force_category_lang ) &&
        $this->_force_category_lang == CMLLanguage::get_default_id() ) )
      return esc_url( remove_query_arg( "lang", $link ) );

    $slug = CMLLanguage::get_slug( CMLUtils::_get( '_real_language' ) );
    return esc_url( add_query_arg( array( "lang" => $slug ),
                                $link ) );
  }

  /*
   * translate blog title and tagline
   *
   * it works correctly only if the theme use the "display" parameter like:
   *
   * get_bloginfo( 'name', 'display' )
   */
  function translate_bloginfo( $info ) {
    return CMLTranslations::get( CMLLanguage::get_current_id(), $info, 'T' );
  }

  /*
   * force the language for categories
   */
  function pre_get_menu( $args ) {
    $this->_force_category_lang = CMLLanguage::get_current_id();
    $this->_force_menu_items = true;

    if( isset( $this->_fake_language_id ) ) {
      $this->_force_category_lang = $this->_fake_language_id;
      $this->_force_post_lang = $this->_fake_language_id;
    }

    return $args;
  }

  function end_get_menu( $nav_menu, $args ) {
    unset( $this->_force_category_lang );
    unset( $this->_force_menu_items );
    unset( $GLOBALS[ '_cml_force_home_slug' ] );

    return $nav_menu;
  }
  /*
   * change menu item with its translation
   *
   * for post I change the name and link with its translation
   */
  function translate_menu_item( $item ) {
    global $_cml_settings;

    /*
     * If the user selected a different for the current language I don't have to
     * apply any filter to its items... quit :)
     */
    if( $this->_no_translate_menu_item == true ) { //&& $item->object == 'page' ) {
      remove_filter( 'wp_setup_nav_menu_item', array( & $this, 'translate_menu_item') );
      return $item;
    }

    //if( isset( $this->_fake_language_id ) ) {
      $lang_id = ( ! isset( $this->_fake_language_id ) ) ?
                    CMLLanguage::get_current_id() :
                    $this->_fake_language_id;

      $this->_force_post_lang = $lang_id;

      $slug = CMLLanguage::get_slug( $lang_id );
      switch($item->object) {
      case 'page':
      case 'post':
        $page_id = CMLPost::get_translation( $lang_id,
                                            $item->object_id );

        //custom label for
        $customs = CMLUtils::_get_translation( "_cml_menu_meta_{$slug}_{$item->ID}" );

        if( null == $customs ) {
          $customs = get_post_meta( $item->ID, "_cml_menu_meta_" . $slug, true );
        }

        $custom_title = "";
        if(  isset( $customs[ 'title' ] ) &&
            ! empty( $customs[ 'title' ] ) ) {
          $item->title = $customs[ 'title' ];

          $custom_title = $item->title;
        }

        if( ! empty( $page_id ) ) {
          $title = ( CMLLanguage::is_default() ) ? $item->title : get_the_title( $page_id );

          // $item->ID = $page_id;
          $item->title = ( empty( $custom_title ) ) ? $title : $custom_title;
          $item->attr_title = ( ! @empty( $customs[ 'attr_title' ] ) ) ? $customs[ 'attr_title' ] :
                                                $item->attr_title;
          $item->post_title = $title;
          $item->object_id = $page_id;

          $this->_force_category_lang = $lang_id;
          $this->_force_post_lang = $lang_id;

          //If using static page, ensure that isn't a translation of it...
          $item->url = get_permalink( $page_id );
          // $url = CMLPost::remove_extra_number( $url, $item );
        }

        unset( $this->_force_category_lang );
        unset( $this->_force_post_lang );

        //I need to set correct page slug
        $lang = CMLPost::get_language_id_by_id( $item->object_id );
        if( ! CMLLanguage::is_current( $lang ) ) {
          $item->url = $this->convert_url( $item->url, CMLLanguage::get_current_slug() );

          if( $_cml_settings[ 'cml_option_action_menu_force' ] ) {
            $item->url = esc_url( add_query_arg( array( 'lang' => CMLLanguage::get_current_slug() ), $item->url ) );
          }
        }

        $url = CMLPost::remove_extra_number( $item->url, $item->object_id );

        /*
         * on a site happend that $url was empty :O
         */
        if( ! empty( $url ) ) {
          $item->url = $url;
        }

      break;
      case 'category':
        $id = $item->object_id;

        //custom label for
        $customs = CMLUtils::_get_translation( "_cml_menu_meta_{$slug}_{$item->ID}" );

        if( null == $customs ) {
          $customs = get_post_meta( $item->ID, "_cml_menu_meta_" . $slug, true );
        }

        $custom_title = "";
        if(  isset( $customs[ 'title' ] ) &&
            ! empty( $customs[ 'title' ] ) ) {
          $item->title = $customs[ 'title' ];

          $custom_title = $item->title;
        }

        if( ! empty( $id ) ) {
          $lang = $lang_id;

          //Get term
          $term = get_term( $item->object_id, $item->object );

          $url = get_term_link( $term );

          //Original category title
          $title = ( empty( $item->post_title ) ) ? $term->name : $item->post_title;
          $item->title = ( empty( $custom_title ) ) ? $title : $custom_title;
          $item->attr_title = ( empty( $customs[ 'attr_title' ] ) ) ? $item->attr_title : $customs[ 'attr_title' ];

          $item->url = $url;

          // if( isset( $this->_fake_language_id ) ) {
          //   unset( $this->_force_post_lang );
          //   unset( $this->_force_category_lang );
          // }
        }
      break;
      case 'custom':
        //custom label for
        $customs = CMLUtils::_get_translation( "_cml_menu_meta_{$slug}_{$item->ID}" );

        //custom label for
        if( null == $customs ) {
          $customs = get_post_meta( $item->ID, "_cml_menu_meta_" . $slug, true );
        }

        $item->title = ( ! empty( $customs[ 'title' ] ) ) ? $customs[ 'title' ] :
                                              CMLTranslations::get( $lang_id, $item->title, "M" );

        $item->attr_title = ( ! @empty( $customs[ 'attr_title' ] ) ) ? $customs[ 'attr_title' ] :
                                              $item->attr_title;

        if( ! @empty( $customs[ 'url_value' ] ) ) {
          $item->url = $customs[ 'url_value' ];
        }

        /* is homepage url? */
        if( trailingslashit( $item->url ) == trailingslashit( $this->_homeUrl ) ) {
          $item->url = CMLUtils::get_home_url( CMLLanguage::get_slug( $lang_id ) );
        }

        break;
      default:
        $id = $item->object_id;

        //custom label for
        $customs = CMLUtils::_get_translation( "_cml_menu_meta_{$slug}_{$item->ID}" );

        if( null == $customs ) {
          $customs = get_post_meta( $item->ID, "_cml_menu_meta_" . $slug, true );
        }

        $custom_title = "";
        if(  isset( $customs[ 'title' ] ) &&
            ! empty( $customs[ 'title' ] ) ) {
          $item->title = $customs[ 'title' ];
        }

        return $item;
      } //endswitch;
    //} //endif;

    unset( $this->_force_post_lang );

    return $item;
  }

  /*
   * change date format
   */
  function get_the_date( $the_date, $d ) {
    global $post;

    $format = CMLLanguage::get_current()->cml_date_format;

    if( empty( $format ) ) {
      $format = CMLUtils::get_date_format();
    } else {
      if( ! empty( $d ) )
        $format = $d;
      else
        $format = CMLUtils::get_date_format();
    }

    $the_date = mysql2date( $format, $post->post_date );

    return $the_date;
  }

  /*
   * change date format for comments
   */
  function get_comment_date( $the_date, $d ) {
    global $comment;

    $format = CMLLanguage::get_current()->cml_date_format;
    if( empty( $format ) ) $format = CMLUtils::get_date_format();

    $the_date = mysql2date( $format, $comment->comment_date );

    return $the_date;
  }

  function post_link_category( $cat0, $cats, $post ) {
    $lang = CMLLanguage::get_id_by_post_id( $post->ID );

    $this->_force_post_lang = $lang;

    return $cat0;
  }

  /*
   * WP Trick: Change the "post_name" of $query object wich
   * I need this function when i remove extra -## from permalink
   */
  function is_duplicated_post_title( $query ) {
    global $wpdb;

    if( cml_is_homepage() ||
      isset( $this->_looking_id_post ) ||
      CMLUtils::_get( '_is_sitemap'  ) ) {
      return;
    }

    $this->_looking_id_post = true;

    if( ! isset( $this->_clean_applied ) ) {
      $this->clear_url();
    }

    $url = esc_url( remove_query_arg( "lang", $this->_clean_url ) );
    if( $this->_url_mode != PRE_LANG ) {
      $id = @url_to_postid( $url );
    }

    if( empty( $id ) ) {
      $id = @cml_get_page_id_by_path( $url );
    }

    if( $id > 0 ) {
      unset( $this->_looking_id_post );
      remove_action( 'pre_get_posts', array( &$this, 'is_duplicated_post_title' ), 0, 1 );

      //Linked posts
      $linked = CMLPost::get_translation( CMLUtils::_get( '_real_language' ), $id );
      if( empty( $linked ) ) {
        return;
      }

      //Same post, or has no translation in current language ?
      if( $linked == $id || $linked == 0 ) {
        return;
      }

      //Return the "corret" post_name, so wordpress retrive it correctly ;)
      if( $this->_url_mode == PRE_LANG ) {
        $query->query_vars[ 'queried_object' ] = $linked;
        $query->queried_object->ID = $linked;
      }

      $name = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_name FROM $wpdb->posts WHERE id = %d", $linked ) );

      $query->query_vars[ 'name' ] = $name;
    }

    unset( $this->_looking_id_post );
    remove_action( 'pre_get_posts', array( &$this, 'is_duplicated_post_title' ), 0, 1 );
  }

  /*
   * replace menu items #cml-current with
   * its value
   */
  function get_nav_menu_items( $items ) {
    global $_cml_settings;

    $new = array();

    $size = $_cml_settings[ 'cml_show_in_menu_size' ];
    $what = $_cml_settings[ 'cml_show_in_menu_as' ];

    //hide items that doesn't exists in current language
    $hide = $_cml_settings[ 'cml_option_menu_hide_items' ];

    $current_id = ( ! isset( $this->_fake_language_id ) ) ?
                      CMLLanguage::get_current_id() :
                      $this->_fake_language_id;

    if( isset( $this->_fake_language_id ) ) {
      $this->_force_category_lang = $this->_fake_language_id;
    }

    foreach( $items as $item ) {
      if( $hide && $item->type == 'post_type' ) {
        //Exists in the current language?
        if( ! in_array( $item->object_id, CMLPost::get_posts_by_language( $current_id ) ) ) {
          unset( $item );

          continue;
        }
      }

      if( $item->url == '#cml-current' || substr( $item->url, 0, 10 ) == "#cml-lang-" ) {
        $lang = CMLLanguage::get_by_id( $current_id );

        $item->title = "";
        if( $what == 1 || $what == 2 )
          $item->title = $lang->cml_language;

        if( $what >= 4 ) {
          $item->title = $lang->cml_language_slug;
        }

        if( $what % 2 ) {
          $item->title = '<img src="' . CMLLanguage::get_flag_src( $lang->id, $size ) . '" title="' . $lang->cml_language . '"/>&nbsp;&nbsp;' . $item->title;
        }

        $this->_force_post_lang = $lang->id;
        $this->unset_category_lang();

        $url = cml_get_the_link( $lang->id, true, false, true );
        $item->url = $url;
        // $item->url = cml_get_the_link( $lang );
      }

      if( $item->url == '#cml-others' || $item->url == '#cml-no-current' ) {
        if( $item->url == '#cml-no-current' ) $lang = CMLLanguage::get_by_id( $current_id );

        $langs = cml_get_languages();
        foreach( $langs as $l ) {
          if( isset( $lang ) && $l->id == $lang->id ) continue;

          $linfo = CMLLanguage::get_by_id( $l->id );

          $clone = clone $item;
          $clone->title = "";

          if( $what == 1 || $what == 2 )
            $clone->title = $linfo->cml_language;

          if( $what >= 4 ) {
            $clone->title = $linfo->cml_language_slug;
          }

          if( $what == 1 || $what == 3 || $what == 5 ) {
            $clone->title = '<img src="' . CMLLanguage::get_flag_src( $linfo->id, $size ) . '" title="' . $linfo->cml_language . '"/>&nbsp;&nbsp;' . $clone->title;
            //$clone->title = '<img src="' . CMLLanguage::get_flag_src( $linfo->id, $size ) . '" />&nbsp;&nbsp;' . $clone->title;
          }

          $this->_force_post_lang = $l->id;
          $this->unset_category_lang();

          $url = cml_get_the_link( $l, true, false, true );
          $clone->url = $url;

          // $clone->url = cml_get_the_link( $l );

          $new[] = $clone;
        }

        if( isset( $item ) ) unset( $item );
      }

//      if( isset( $item ) && substr( $item->url, 0, 10 ) == "#cml-lang-" ) {
//        $lang = str_replace( "#cml-lang-", "", $item->url );
//
//        $item->url = cml_get_the_link( CMLLanguage::get_by_id( $lang ), true, false, true );
//      }

      if( isset( $item ) )
        $new[] = $item;
    }

    unset( $this->_force_post_lang );

    return $new;
  }

  /*
   * filter widgets by language
   */
  function filter_widgets( $sidebars_widgets ) {
    foreach( $sidebars_widgets as $area => $widgets ) {
      if( empty( $widgets ) )
        continue;

      if ( 'wp_inactive_widgets' == $area )
        continue;

      foreach( $widgets as $position => $widget_id ) {
        // Find the conditions for this widget.
        list( $basename, $suffix ) = explode( "-", $widget_id, 2 );

        if ( ! isset( $settings[ $basename ] ) )
            $settings[ $basename ] = get_option( 'widget_' . $basename );

        if ( isset( $settings[$basename][$suffix] ) ) {
            if ( false === $this->filter_widget( $settings[$basename][$suffix] ) ) {
              unset( $sidebars_widgets[$area][$position] );
            }
        }
      }
    }

    return $sidebars_widgets;
  }

  /**
   * Determine whether the widget should be displayed based on conditions set by the user.
   *
   * @param array $instance The widget settings.
   * @return array Settings to display or bool false to hide.
   */
  function filter_widget( $instance ) {
    global $post, $wp_query;

    if ( empty( $instance['cml-conditions'] )
          || empty( $instance['cml-conditions']['langs'] ) ) {
      return true;
    }

    //In which language it'is visible or hidden?
    $langs = $instance['cml-conditions']['langs'];
    $in = in_array( CMLLanguage::get_current_id(), $langs );

    //Hide or show?
    return( $instance['cml-conditions']['action'] == "show" ) ? $in : ! $in;
  }

  /*
   * detect current language
   */
  function update_current_language() {
    global $wpdb, $_cml_settings, $wp_session;

    //Already detected?
    if( isset( $this->_language_detected ) ) {
      return;
    }

    $this->_language_detected = 1;

    $request_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    //Ajax?
    if( ! isset( $_REQUEST[ 'lang' ] ) &&
       defined( 'DOING_AJAX' ) ||
      stripos( $request_url, '/wp-content/' ) !== false ||
      stripos( $request_url, '.php' ) !== false ) {
      if( ! defined( 'CML_NOUPDATE' ) )
        define( 'CML_NOUPDATE', 1 );
    } else {
      $lang = CMLUtils::clear_url();

      if( empty( $lang ) ) $lang = $this->get_language_by_url();
      if( empty( $lang ) && cml_is_homepage() ) $lang = CMLLanguage::get_default_id();
    }

    // error_log("Request: $request_url");
    if( isset( $_REQUEST[ 'lang' ] ) ) {
      $l = CMLLanguage::get_id_by_slug( $_REQUEST[ 'lang' ] );

      if( ! cml_is_homepage() && ! empty( $lang ) ) {
        $this->_fake_language_id = $l;
      } else {
        $lang = $l;
      }
    }

    //language detected?
    if( empty( $lang ) &&
       $this->_url_mode == PRE_LANG ) {
        $lang = CMLLanguage::get_id_by_slug( esc_attr( $_GET[ 'lang' ] ) );
    }

    if( ! empty( $lang ) ) {
      CMLLanguage::set_current( $lang );
    } else {
      if( ! defined( 'CML_NOUPDATE' ) )
        define( 'CML_NOUPDATE', 1 );
    }

    //Translate widget title
    if( ! CMLLanguage::is_default() || isset( $this->_fake_language_id ) ) {
      add_filter( 'widget_title', array( & $this, 'widget_title' ), 0, 1 );

      if( isset( $this->_fake_language_id ) ) {
        add_filter( 'term_link', array( &$this, 'translate_term_link' ), 0, 3 );
      }
    }

    if( ! defined( 'CML_NOUPDATE' ) ) {
      $cookie = setcookie( '_cml_language', CMLLanguage::get_current_id(), 0, COOKIEPATH, COOKIE_DOMAIN, false );

      // error_log( 'cookie: ' . $cookie . ', ' . CMLLanguage::get_current_id() );
    } else {
      $lang = @$_COOKIE[ '_cml_language' ];

      if( null !== CMLLanguage::get_by_id( $lang ) ) {
        CMLLanguage::set_current( $lang );

        $locale = CMLLanguage::get_current()->cml_locale;
      }
    }

    CMLUtils::_set( "_real_language", ( ! isset( $this->_fake_language_id ) ) ?
                                            CMLLanguage::get_current_id() :
                                            $this->_fake_language_id );

    do_action( 'cml_language_detected', CMLUtils::_get( "_real_language" ) );

    // error_log( 'id: ' . defined('CML_NOUPDATE') . ', ' . CMLLanguage::get_current_id() );
    // error_log( 'updating' );

    //Switch wordpress menu
    $this->change_menu();
  }

  function get_language_by_url() {
    $lang = "";

    //it.example.com
    if( PRE_DOMAIN == $this->_url_mode ) {
      preg_match( "/^http.*\/\/([a-z]{2})\./", $this->_url, $matches );

      if( ! empty( $matches ) ) {
        $lang = end( $matches );

        $lang = CMLLanguage::get_id_by_slug( $lang );
      } else {
        $lang = CMLLanguage::get_default_id();
      }
    }

    if( empty( $lang ) ) {
      if( ! empty( $this->_force_current_language ) ) {
        return $this->_force_current_language;
      } else {
        //Se non sono riuscito a recuperare la lingua dal link, recupero l'info dall'articolo

        //Funzione con alcuni tipi di permalink, quali ?p=##, archives/ e non nel pannello di admin (almeno in alcuni casi)
        $the_id = 0;

        //Posso recuperare l'info dal numero dell'articolo?
        $the_id = $this->get_post_id_by_url( $this->_url );

        if( empty( $the_id ) ) {
          $url = $this->_request_url;

          if( ! isset( $this->_clean_applied ) ) {
            if( PRE_LANG == $this->_url_mode ) {
              $url = esc_url( remove_query_arg( "lang", $this->_request_url ) );
            } else {
              $this->clear_url();

              if( $this->_url_mode != PRE_NONE ) {
                $url = CMLUtils::get_clean_request();
              }
            }
          }

          $the_id = cml_get_page_id_by_path( $url );
        }

        //Qualcosa è andato storto, non modifico il "locale"
        if( empty( $the_id ) ) {
          return CMLLanguage::get_default_id();
        } else {
          if( ! isset( $_GET[ 'cat' ] ) ) {
            $lang = CMLPost::get_language_id_by_id( $the_id );
          }
        }
      }
    }

    return $lang;
  }

  //menu
  function change_menu() {
    global $_cml_settings;

    if( ! $_cml_settings[ "cml_option_action_menu" ] ) {
      return;
    }

    $mods = get_theme_mods();
    $locations = get_theme_mod( 'nav_menu_locations' );

    //Se non inizia per cml_ allora sarà quella definito dal tema :)
    if( is_array( $locations ) ) {
      $key = get_option( "cml_primary_menu_name" );

      if( ! empty( $key ) ) {
        $this->switch_menu( $key, $locations );
      } else {
        foreach( $locations as $key => $value ) {
          if( ! empty( $key ) && substr( $key, 0, 4 ) != "cml_" ) {
            $this->switch_menu( $key, $locations );

            break;
          }
        } //foreach
      } //if
    } //if
  }

  /*
   * switch_menu
   */
  function switch_menu( $key, $locations ) {
    $menu = CMLLanguage::get_current_slug();

    if( empty ( $locations["cml_menu_$menu"] ) ) {
      return;
    }

    //I have choosed different menu for current langauge, I haven't translate itams
    $this->_no_translate_menu_item = ( $locations[$key] != $locations["cml_menu_$menu"] );

    $locations[$key] = $locations["cml_menu_$menu"];
    $this->_current_menu_id = $locations[ $key ];

    set_theme_mod( 'nav_menu_locations', $locations );
  }

  /*
   * translate widget title
   */
  function widget_title( $title ) {
    if( empty( $title ) ) {
      return $title;
    }

    $lang = CMLUtils::_get( '_real_language' );
    if( CMLLanguage::is_default( $lang ) ) {
      return $title;
    }

    return CMLTranslations::get( $lang,
                                 $title,
                                 "W" );
  }

  /*
   *
   */
  function change_taxonomy_name( $wp_query ) {
    if( isset( $this->_change_taxonomy_applied ) ) return;

    //For default language I do nothing
    if( CMLLanguage::is_default() ) {
      $this->_change_taxonomy_applied = true;

      return;
    }

    /*
     * This hook was called twice, first time "category_name" contains url
     * and if I change 'name' parameter wp ignore it :(
     */
    if ( isset( $wp_query->query[ 'category_name' ] ) && false !== strpos( $wp_query->query[ 'category_name' ], "http" ) ) {
      return;
    }

    $is_category = is_category();
    $is_custom = apply_filters( 'cml_is_custom_category', false, $wp_query );
    $is_category = $is_category || $is_custom;

    //Only tags and categories
    if ( is_archive() ) {
      if( ! is_tag() && ! $is_category ) {
        $this->_change_taxonomy_applied = true;

        return;
      }
    }

    global $wpdb;

    if( $is_category ) {
      $cat = "";
      if( ! $is_custom ) {
        $cat = @$wp_query->query[ 'category_name' ];
      } else {
        $cat = apply_filters( 'cml_custom_category_name', $cat, $wp_query );
      }

      $cats = explode( "/", $cat );
      if( ! is_array( $cats ) ) {
        $cats = array( $cat );
      }
    } else {
      $cats = @$wp_query->query[ 'tag' ];
      $cats = array( $cats );
    }

    /*
     * search for original category name in CECEPPA_ML_CATS table
     */
    foreach( $cats as $cat ) {
      if( empty( $cat ) ) continue;

      $name = "";
      $term = "";

      // if( is_category() ) {
      //   //Is $cat a translation?
      //   $term = term_exists( $cat, 'category' );
      // } else {
      //   $term = term_exists( sanitize_title( $cat ), 'post_tag' );
      // }

      if( empty( $term ) ) {
        /*
         * wp pass me category name in lowercase,
         * I don't know how user stored it ( upper, lower or...), so
         * I have to convert HEX in lowercase before compare
         */
        $cat = strtolower( str_replace("-", " ", $cat) );
        $query = sprintf( "SELECT *, UNHEX( cml_cat_name ) as cml_cat_name FROM %s WHERE LOWER(cml_cat_translation_slug) IN ('%s', '%s')",
                         CECEPPA_ML_CATS, strtolower( bin2hex( $cat ) ),
                         strtolower( bin2hex( sanitize_title( $cat ) ) ) );

        $row = $wpdb->get_row( $query );

        $name = ( ! empty( $row ) ) ? strtolower( $row->cml_cat_name ) : "";
        CMLUtils::_set( '_reverted', ! empty( $row ) ? $row->cml_cat_id : 0 );
      }

      if( ! empty( $name ) ) {
        // $where = is_category() ? "category" : "post_tag";
        $where = $row->cml_taxonomy;
        CMLUtils::_set( '_no_translate_term', 1 );
        $term = get_term_by( 'id', $row->cml_cat_id, $where );

        if( is_object( $term ) ) {
          $name = $term->slug;
        }

        CMLUtils::_del( '_no_translate_term' );
      }

      $n = empty( $name ) ? $cat : $name;
      $new[] = sanitize_title( $n );
    } //endforeach;

    //Nothing to change
    if( ! isset( $new ) ) {
      return;
    }

    if( ! $is_custom ) {
      if( is_category() ) {
        $wp_query->query[ 'category_name' ] = join( "/", $new );
        $wp_query->query_vars[ 'category_name' ] = end( $new );
      } else {
        $wp_query->query[ 'tag' ] = end( $new );
        $wp_query->query_vars[ 'tag' ] = end( $new );
        $wp_query->query_vars[ 'tag_slug__in' ][0] = end( $new );
      }
    } else {
      $wp_query = apply_filters( 'cml_change_wp_query_values', $wp_query, $new );
    }

    if( ! $is_custom ) {
      $taxonomy_name = ( is_category() ) ? "category" : "post_tag";
    } else {
      $taxonomy_name = $wp_query->tax_query->queries[ 0 ][ 'taxonomy' ];
    }

    $taxquery = array(
                "taxonomy" => $taxonomy_name,
                "terms" => array( join( "/", $new ) ),
                "include_children" => 1,
                "field" => "slug",
                "operator" => "IN",
                );

    $wp_query->tax_query->queries[ 0 ] = $taxquery;

    //remove_action( 'pre_get_posts', array( & $this, 'change_taxonomy_name' ), 0, 1 );

    $this->_change_category_applied = true;
  }

  function show_notice( $content ) {
    global $wpdb, $_cml_settings;

    if( isCrawler() ) return $content;

    //Recupero la lingua del browser
    $browser_lang_id = cml_get_browser_lang();

    if( empty( $browser_lang_id ) ) return $content;

    //Recupero l'd della lingua dal database
    $lang_id = CMLLanguage::get_current_id();

    if( is_page() ) {
      if( $_cml_settings[ "cml_option_notice_page" ] != 1 ) return $content;

      $link = cml_get_linked_post( get_the_ID(), $browser_lang_id );
    }

    if( is_single() ) {
      if( $_cml_settings["cml_option_notice_post"] != 1) return $content;

      $link = cml_get_linked_post( get_the_ID(), $browser_lang_id );
    }

    $link = ( ! isset( $link ) || $link == get_the_ID() || $link == null || $link == 0) ? null :
                                                                                          get_permalink( $link );
    if( ! empty( $link ) ) {
      $notice = cml_get_notice( $browser_lang_id );
      $before = stripcslashes( $_cml_settings['cml_option_notice_before'] );
      $after = stripcslashes( $_cml_settings['cml_option_notice_after'] );
      $flag = cml_get_flag_by_lang_id( $browser_lang_id, "small" );

      if( ! empty( $notice) ) {
          $c = "$before<a href='$link'><img src='$flag' />&nbsp;$notice</a>$after";

        if( $this->_show_notice_pos == 'top' )
          $content = $c . $content;
        else
          $content .= $c;
      }
    }

    return $content;
  }

  function filter_get_pages( $pages ) {
    if( CMLUtils::_get( '_is_sitemap' ) ) return;

    foreach( $pages as $key => $page ) {
      if( CMLLanguage::get_id_by_post_id( $page->ID ) !=
          CMLLanguage::get_current_id() ) {
        unset( $pages[ $key ] );
      }
    }

    return $pages;
  }

  /*
   * filter posts by language
   *
   * look $wp_query for "lang" parameter
   *
   */
  function filter_posts_by_language( $wp_query ) {
    global $wpdb, $_cml_settings;

    if( isset( $this->_looking_id_post ) ||
       CMLUtils::_get( '_is_sitemap' ) ) {
      return;
    }

    //Check if the post_type is the ignore list
    if( isset( $wp_query->query['post_type'] ) &&
        in_array( $wp_query->query['post_type'], $this->_excluded_post_types ) ) {
      return $wp_query;
    }

    //Skip attachment type & nav_menu_item
    if( @$wp_query->query_vars[ 'post_type' ] == 'attachment' ||
        @$wp_query->query_vars[ 'post_type' ] == 'nav_menu_item' ) return $wp_query;

    // if( is_search() && $wp_query->is_main_query() ) {
    if( $wp_query->is_main_query() ) {
      if( is_search() && ! $this->_filter_search ) {
        return;
      }

      if( is_singular() ) {
        return;
      }
    }

    $use_language = array( CMLUtils::_get( '_real_language' ) ); //CMLLanguage::get_current_id();

    //lang parameters
    if( isset( $wp_query->query[ 'lang' ] ) ) {
      $langs = explode( ",", $wp_query->query[ 'lang' ] );

      /*
       * if lang is empty, I don't filter posts, exit :)
       */
      if( empty( $langs ) ) {
        return $wp_query;
      }

      /*
       * check that language parameters exists in my languages
       */
      foreach( $langs as $lang ) {
        $id = CMLLanguage::get_id_by_slug( $lang );

        if( ! empty( $id ) && $id != CMLLanguage::get_current_id() ) {
          $_langs[] = $id;
        }
      }

      if( isset( $_langs ) && ! empty( $_langs ) ) {
        $use_language = $_langs;
      }
    }

    /**
     * On some website happens that wp ignore the limit...
     * This is due because the plugin set the parameter post__in with all
     * the posts available, ignoring the current post type...
     * To avoid this I just change method.. Instead to set which posts belongs
     * to the current language, I do the opposite. So I set which ones doesn't..
     * But I have to remove the "unique" post ids from that array, otherwise the
     * these we'll be hidden
     */
    //Get all posts by language except the ones that exists in the use_language array
    if( ! is_array( $use_language ) ) $use_language = array( $use_language );

    $posts = array();
    foreach( CMLLanguage::get_all() as $lang ) {
      //Ignore the use language...
      $id = is_object( $lang ) ? $lang->id : $lang;
      $key = array_search( $id, $use_language );
      if( $key !== FALSE ) continue;

      $posts = array_merge( $posts, CMLPost::get_posts_by_language( $id ) );
    }

    // if( ! is_array( $use_language ) ) {
    //   $posts = CMLPost::get_posts_by_language( $use_language );
    // } else {
    //   $posts = array();
    //
    //   foreach( $use_language as $lang ) {
    //     $posts = array_merge( $posts, CMLPost::get_posts_by_language( $lang ) );
    //   }
    // }

    /*
     * If lang=## parameter exists in $_GET, probably I'm forcing language for current
     * post, so I have to add that post id to $posts array, or wp will return 404
     */
    if( isset( $_GET[ 'lang' ] ) &&
       isset( $this->_fake_language_id ) &&
       ! isset( $this->_include_current ) ) {
      $this->_looking_id_post = true;
      $id = cml_get_page_id_by_path( $this->_clean_url );

      if( $id > 0 ) {
        $posts[] = $id;

        CMLPost::_update_posts_by_language( CMLLanguage::get_current_id(), $posts );
      }

      $this->_include_current = true;
    }

    /*

     */
    if ( $wp_query->query_vars[ 'post__not_in' ] &&
          is_array( $wp_query->query_vars[ 'post__not_in' ] ) ) {
      // $posts = array_diff( $posts,
      //                     $wp_query->query_vars[ 'post__not_in' ] );
      $posts = array_merge( $posts,
                          $wp_query->query_vars[ 'post__not_in' ] );
    }

    //Remove all the unique posts
    $uniques = CMLPost::get_unique_posts();
    if( is_array( $uniques ) ) {
      $posts = array_diff( $posts, $uniques );
    }

    /*
    * add all posts in default language that has no translation in current
    */
    if( $_cml_settings[ 'cml_option_filter_posts' ] == FILTER_HIDE_EMPTY &&
        ! CMLLanguage::is_default() &&
        ! isset( $this->_hide_diff ) ) {

      if( CMLLanguage::get_default_id() > 0 ) {
        $query = sprintf( "SELECT lang_%d FROM %s WHERE lang_%d > 0 AND lang_%d = 0",
                CMLLanguage::get_default_id(), CECEPPA_ML_RELATIONS,
                CMLLanguage::get_default_id(), CMLLanguage::get_current_id() );

        $results = $wpdb->get_results( $query, ARRAY_N );

        foreach( $results as $id ) {
          $posts[] = $id[ 0 ];
        }

        $this->_posts_of_lang[ CMLLanguage::get_current_id() ] = array_unique( $posts );
        $this->_hide_diff = true;
      }
    }

    if( ! empty ( $posts ) ) {
      // $wp_query->query_vars[ 'post__in' ] = $posts;
      $wp_query->query_vars[ 'post__not_in' ] = $posts;
    }

    if( $wp_query->is_main_query() ) {
      $this->change_menu();
    }
  }

  /*
   * show all posts but hide their translations
   */
  function hide_translations( $wp_query ) {
    global $wpdb, $_cml_settings;

    if( is_feed() ) {
      $wp_query = $this->filter_posts_by_language( $wp_query );

      return;
    }

    if( isset( $this->_looking_id_post ) ||
       CMLUtils::_get( '_is_sitemap' ) ) {
      return;
    }

    //Check if the post_type is the ignore list
    if( isset( $wp_query->query['post_type'] ) &&
        in_array( $wp_query->query['post_type'], $this->_excluded_post_types ) ) {
      return $wp_query;
    }

    if( $wp_query != null && ( is_page() || is_single() || isCrawler() ) ) return;
    if( is_preview() || isset( $_GET['preview'] ) ) return;

    /*
     * Hide translations in current language
     */
    if( ! isset( $this->_hide_posts ) || empty( $this->_hide_posts ) ) {
      $id = CMLLanguage::get_current_id();
      $this->_hide_posts = get_option( "cml_hide_posts_for_lang_$id", array() );
    } //endif;

    //Al momento utilizzo la vecchia funzione non ottimizzata per la visualizzazione dei tag
    if( is_tag() ) {
      cml_frontend_hide_translations_for_tags( $wp_query );
    }

    if( $wp_query != null && is_object( $wp_query ) && is_array( $this->_hide_posts ) ) {
      $wp_query->query_vars[ 'post__not_in' ] = @array_merge( $wp_query->query_vars[ 'post__not_in' ],
                                                             $this->_hide_posts );

      return $this->_hide_posts;
    }

    return $this->_hide_posts;
  }

  /**
   * is feed?
   */
  function check_is_feed( $wp_query ) {
    if( ! is_feed() ) return;

    $wp_query = $this->filter_posts_by_language( $wp_query );
  }

  /*
   * filter previous and next post
   */
  function get_previous_next_post_where( $where ) {
    global $wpdb;

    $posts = CMLPost::get_posts_by_language();

    if( empty( $posts ) ) return $where;

    if( ! empty( $posts ) )
      $where .= " AND p.ID IN (" . implode(", ", $posts) . ") ";

    return $where;
  }

  /*
   * Recupero l'id del post per i link di tipo "?p=##" oppure "/##"
   */
  function get_post_id_by_url( $url ) {
    $structure = explode( "/", $this->_permalink_structure );

    if( empty( $this->_permalink_structure ) || end( $structure ) == "%post_id%" ) {
      preg_match('/([0-9]+)$/', $url, $matches );

      if( !empty( $matches ) ) {
        return end( $matches );
      }
    } //endif;

    return null;
  }

  /*
   * get translated alternative text from media
   */
  function get_translated_media_fields($attr, $attachment ) {
    $id = $attachment->ID;

    $meta = get_post_meta( $id, '_cml_media_meta', true );
    if( ! is_array( $meta ) || empty( $meta ) ) return $attr;

    $lang = CMLLanguage::get_current_id();

    $alt = @$meta[ 'alternative-' . $lang ];
    if( ! empty( $alt ) ) {
      $attr[ 'alt' ] = $alt;
    }

    return $attr;
  }

  /*
   * get translated title
   */
  function get_translated_title( $title, $id = null ) {
    if( null == $id ) return $title;
    if( 'attachment' !== get_post_type( $id ) ) return $title;

    $meta = get_post_meta( $id, '_cml_media_meta', true );
    if( ! is_array( $meta ) || empty( $meta ) ) return $title;

    $lang = CMLLanguage::get_current_id();

    $t = @$meta[ 'title-' . $lang ];

    return empty( $t ) ? $title : $t;
  }

  /*
   * redirect browser to user language, if exists
   *
   */
  function redirect_browser() {
    global $_cml_settings;

    //No redirect, please :)
    if( $this->_redirect_browser == 'nothing' || isCrawler() ) return;

    //Detect browser language
    global $wpdb;

    $lang = cml_get_browser_lang();
    $slug = ( empty( $lang ) ) ? CMLLanguage::get_default_slug() :
                                  CMLLanguage::get_slug( $lang );

    /*
     * is default language and I dont have to add slug for it?
     * Ok, nothing to do :)
     */
    if( CMLLanguage::is_default( $slug ) &&
        $_cml_settings[ 'url_mode_remove_default' ] == 1 ) {

      return;
    }

    //Redirect abilitato?
    if($this->_redirect_browser == 'auto') {
      $location = CMLUtils::get_home_url( $slug );
    } else if( $this->_redirect_browser == 'default' ) {
      $location = CMLUtils::get_home_url( CMLLanguage::get_default_slug() );

      //if $location == current url, need to force tha language in the url,
      //otherwise I'll have a redirect loop...
      if( $_cml_settings[ 'url_mode_remove_default' ] == 1 ) {
        $location = esc_url_raw( add_query_arg( array( 'lang' => CMLLanguage::get_default_slug() ), $location ) );
      }
    } else if( $this->_redirect_browser == "others" ) {
      if( $lang == CMLLanguage::get_default_id() ) return;	//Default language, do nothing

      $location = CMLUtils::get_home_url( $slug );
    }

    if( ! empty( $location ) ) {
      $this->_redirect_browser = 'nothing';

      wp_redirect( trailingslashit( $location ) );
      exit;
    }
  }

  /**
   * Force the permalink to be redirect to the corrent url
   */
  function force_redirect( $requested_url = null, $do_redirect = true ) {
    /*
     * For the custom post types the function get_permalink is not called, or anyway
     * it isn't before this one...
     */
    if( ! is_singular() || cml_is_homepage() ) return;

    if ( !$requested_url ) {
  		// build the URL in the address bar
  		$requested_url  = is_ssl() ? 'https://' : 'http://';
  		$requested_url .= $_SERVER['HTTP_HOST'];
  		$requested_url .= $_SERVER['REQUEST_URI'];
      $original = $requested_url;
  	}

    $id = get_queried_object_id();
    $translated = get_permalink( $id );

    //No redirect
    if( null == $original || null == $translated ) return;

    //Remove all the parameters ?###
    $original = preg_replace( '/\?.*/', '', $original );
    $translated = preg_replace( '/\?.*/', '', $translated );

    if( trailingslashit( $original ) != trailingslashit( $translated ) ) {
      wp_redirect($translated, 301);
    }
  }

  //is_homepage?
  function is_homepage() {
    $url = esc_url( remove_query_arg( "lang", $this->_url ) );
    return $url == $this->_homeUrl;
  }

  /*
   * change wordpress locale
   */
  function setlocale( $locale ) {
    global $_cml_settings;

    if( isset( $this->_locale_applied ) ) {
      return CMLUtils::_get( '_locale', $locale );
    }

    $this->update_current_language();

    if( ! $_cml_settings[ "cml_option_change_locale" ] ) {
      $this->_locale_applied = true;

      return $locale;
    }

    if( ! isset( $this->_fake_language_id ) ) {
      $locale = CMLLanguage::get_current()->cml_locale;
    } else {
      $lang = CMLLanguage::get_by_id( $this->_fake_language_id );

      $locale = $lang->cml_locale;
    }

    $logged_in = function_exists( 'is_user_logged_in' ) && is_user_logged_in();
    if( $logged_in && ! defined( 'DOING_AJAX' ) && ! defined( 'CML_NOUPDATE') ) {
      update_user_meta( get_current_user_id(), 'cml_language', CMLLanguage::get_current_id() );
    }

    CMLUtils::_set( '_locale', $locale );
    $this->_locale_applied = true;

    if( ! defined( 'CML_NOUPDATE' ) ) {
      setcookie( '_cml_language', CMLLanguage::get_current_id(), 0, COOKIEPATH, COOKIE_DOMAIN, false );
    } else {
      $lang = @$_COOKIE[ '_cml_language' ];

      if( null !== CMLLanguage::get_by_id( $lang ) ) {
        CMLLanguage::set_current( $lang );

        $locale = CMLLanguage::get_current()->cml_locale;
      }
    }

    // error_log( $locale );
    return $locale;
  }

  /*
   * set right to left language
   */
  function setup_rtl() {
    $GLOBALS[ 'text_direction' ] = ( CMLLanguage::get_current()->cml_rtl == 1 ) ? 'rtl' : 'ltr';
  }

  /**
   * Add hreflang inside of <head> tag
   */
  function add_hreflang() {
    $head_link = array();

    foreach( CMLLanguage::get_others() as $lang ) {
      $link = cml_get_the_link( $lang, true, true );

      if( empty( $link ) ) continue;

      $head_link[] = '<link rel="alternate" hreflang="'.$lang->cml_language_slug.'" href="'. $link.'" />';
    }

    echo join( "\n", $head_link ) . "\n";

    CMLUtils::_set( '_after_wp_head', 1 );
  }

  /**
    * Questa funzione si occupa di
    * "Filtrare tutte le query WordPress allo scopo di filtrare automaticamente gli articoli più letti, più commentati, etc"
    *
    * Invece di:
    *   1) recuperare tutte le categorie associate alla lingua
    *   2) apportare modifiche alla query, per esempio modificando la voce post_id IN (....)
    *
    *
    * preferisco aggiungere un'altra clausola alla query del tipo: category_id IN (....), evitando quindi di
    * eseguire anche il punto 2
  */
  function filter_query($query) {
    //Filtro la query "Articoli più letti" (Least Read Post)
    $pos = strpos($query, 'ORDER BY m.meta_value');
    if(FALSE === $pos)
    {
    } else {
      return $this->filter_least_read_post($query, $pos);
    }

    //Articoli più commentati
    $pos = strpos($query, 'ORDER BY comment_count');
    if(FALSE === $pos)
    {
    } else {
      return $this->filter_most_commented($query, $pos);
    }

    //Archivio
    $pos = strpos($query, 'GROUP BY YEAR(post_date)');
    if(FALSE === $pos)
    {
    } else {
      return $this->filter_archives($query, $pos);
    }

    /*
    //Tag cloud: don't work, query is too complex to manipulate :(
    $pos = strpos($query, 'ORDER BY tt.count DESC');
    if(FALSE === $pos) {
    } else {
      return $this->filter_tag_cloud($query, $pos);
    }
    */

    //Non ho trovato niente (Nothing to do)
    return $query;
  }

  /*
   * Filtro l'archivio
   */
  function filter_archives($query, $pos) {
    //Recupero tutti i post collegati alla lingua corrente
    $posts = CMLPost::get_posts_by_language();

    $where = " AND id IN (" . implode(", ", $posts) . ") ";

    //Aggiungo il $where prima della clausula ORDER
    $query = substr($query, 0, $pos) . $where . substr($query, $pos);
    return $query;
  }


  /**
   * For info about why this function have a look above, in the construct one
   */
   function change_get_term_by_query( $query ) {
     static $_terms = array();
     global $wpdb;

     $pos = strpos($query, 'AND t.slug');
     if( FALSE === $pos ) return $query;

     if( ! preg_match( "/t.slug = '[^']*/", $query, $slug ) ) return $query;
     if( ! preg_match( "/tt.taxonomy = '[^']*/", $query, $taxonomy ) ) return $query;

     //For non UTF 8 language ( like cyrillic )
     $slug = urldecode( str_replace( "t.slug = '", '', $slug[0] ) );
     if( ! array_key_exists( $slug, $_terms ) ) {
       $taxonomy = str_replace( "tt.taxonomy = '", '', $taxonomy[0] );

       //Retrive the original slug
       /**
        * The slug could be different by category name, so I can't just sanitize my original name
        * I have to get the right slug from the database.
        * I need to check both cml_cat_translation_slug and cml_cat_translation, because of the slug
        */
       $search = sprintf( "SELECT slug FROM %s INNER JOIN $wpdb->terms t2 ON cml_cat_id = t2.term_id WHERE ( LOWER(cml_cat_translation_slug) IN ('%s', '%s') OR LOWER(cml_cat_translation) IN ('%s', '%s') ) AND cml_taxonomy = '%s' AND cml_cat_lang_id = %d",
                        CECEPPA_ML_CATS,
                        strtolower( bin2hex( $slug ) ),
                        strtolower( bin2hex( sanitize_title( $slug ) ) ),
                        strtolower( bin2hex( $slug ) ),
                        strtolower( bin2hex( sanitize_title( $slug ) ) ),
                        $taxonomy,
                        CMLLanguage::get_current_id() );

       $original = $wpdb->get_var( $search );
       $_terms[ $slug ] = $original;
     } else {
       $original = $_terms[ $slug ];
     }

     if( empty( $original ) ) return $query;

     $query = preg_replace( "/t.slug = '[^']*/", "t.slug = '" . $original, $query );

     return $query;
   }
}

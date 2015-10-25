<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-ajax-save.php' );

//Utils
require_once ( CML_PLUGIN_ADMIN_PATH . 'fix.php' );
require_once ( CML_PLUGIN_ADMIN_PATH . 'migrate.php' );
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-utils.php' );

//Help
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-help.php' );

//Languages
require_once ( CML_PLUGIN_ADMIN_PATH . 'locales.php' );
require_once ( CML_PLUGIN_LAYOUTS_PATH . 'languages-item.php' );
require_once ( CML_PLUGIN_LAYOUTS_PATH . 'languages-downloader.php' );

//Extra media fields
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-media.php' );

//Wordpress wp-pointer
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-wppointer.php' );

//CML notices
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-notices.php' );

//Generate "settings.gen.php" and "cml_flags.css"
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-settings-gen.php' );

/*
 * I have to load following php files here or quickedit will not works correctly
 */
require_once( CML_PLUGIN_ADMIN_PATH . 'admin-quickedit.php' );
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-posts.php' );

//Backup functions
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-backup-fn.php' );

//Setting mode: basic, intermediate, advanced
$GLOBALS[ 'cml_show_mode' ] = get_option( "ceceppaml_admin_advanced_mode", "show-basic" );

class CMLAdmin extends CeceppaML {
  public function __construct() {
    parent::__construct();

    global $_cml_settinsg, $pagenow;

    /*
     * Wizard?
     */
    if( get_option( "cml_show_wizard" ) || isset( $_GET[ 'cml-restore-wizard' ] ) ) {
      update_option( "cml_show_wizard", 1 );

      add_action( 'admin_notices', array( & $this, 'wizard' ) );
    }

    //Scan folders for wpml-config.xml?
    add_action( 'plugins_loaded', array( & $this, 'scan_plugin_folders' ), 10 );

    //Register addons
    add_action( 'plugins_loaded', array( & $this, 'register_addons' ), 10 );

    //When new plugin is activate I execute scan again :)
    add_action( 'activated_plugin', array( & $this, 'plugin_activated' ), 10 );

    /*
     * I need it to let user to see translated post also in admin panel,
     * but I use it only in edit.php page, or permalink change will cause 500 Error :(
     *
     * translate_home option can be used by external plugins to allow home url translation
    */
    $seo = CMLUtils::_get( '_seo' );
    if( is_array( $seo ) ) {
      $page = isset($_POST['page']) ? $_POST['page'] : '';
      foreach( $seo as $s ) {
        if( $s[ 'pagenow' ] == $pagenow ) {
          if( isset( $s[ 'page' ] ) && $s[ 'page' ] != $page ) {
            break;
          }

          CMLUtils::_set( "translate_home", 1 );
          CMLUtils::_set( '_real_language', CMLLanguage::get_default_id() );
        }
      }
    }

    if( in_array( $pagenow, array( "post.php", "post-new.php", "edit.php" ) )
        || CMLUtils::_get( "translate_home", 1 ) ) {
        if( ! ( isset( $_GET[ 'post_type' ] ) && 'page' == $_GET[ 'post_type' ] ) ) {
        /*
         * I need to force home url to post language or
         * wp show permalink in according to current language :O
         */
        if( isset( $_GET[ 'post' ] ) ) {
          $post = intval( $_GET[ 'post' ] );
          $lang = CMLLanguage::get_id_by_post_id( $post );

          CMLUtils::_set( '_forced_language_slug', $lang );
        }

        // if( 'options-permalink.php' != $pagenow &&
        //     'themes.php' != $pagenow &&
        //     ! defined( 'DOING_AJAX' )  ) {
        if( in_array( $pagenow, array( 'edit.php', 'post.php', 'edit-tags.php' ) ) && ! defined( 'DOING_AJAX' ) ) {
          add_filter( 'home_url', array( & $this, 'translate_home_url' ), 0, 4 );
        }
      }
    }

    //try to fix 500 error
    // if( ! defined( 'DOING_AJAX' ) ) {
    add_filter( 'flush_rewrite_rules_hard', array( & $this, 'no_translate_home_url' ), 10, 1 );
    // }

    if( $pagenow == "widgets.php" ) {
      require_once CML_PLUGIN_INCLUDES_PATH . "shortcodes.php";
    }

    /*
     * Ability to translate the "category" slug
     */
    if( 'options-permalink.php' == $pagenow ) {
      require_once CML_PLUGIN_ADMIN_PATH . "admin-permalink.php";
    }

    /*
     * update existsings post to default language?
     */
    if( isset( $_GET[ 'cml_update_existings_posts' ] ) ) {
     add_action( 'admin_footer', array( & $this, 'update_all_posts_language' ) );
    }

    /*
     * Scripts & Styles...
     */
    add_action( 'admin_enqueue_scripts', array( & $this, 'admin_scripts' ) );

    //Plugin menu
    add_action( 'admin_menu', array( & $this, 'add_menu_pages' ) );

    //Ajax
    add_action( 'wp_ajax_ceceppaml_advanced_mode', 'cml_admin_save_advanced_mode' );
    add_action( 'wp_ajax_ceceppaml_save_item', 'cml_admin_save_language_item' );  //Save single item

    //Options page
    add_action( 'wp_ajax_ceceppaml_save_options_actions', 'cml_admin_save_options_actions' );
    add_action( 'wp_ajax_ceceppaml_save_options_filters', 'cml_admin_save_options_filters' );
    add_action( 'wp_ajax_ceceppaml_save_options_flags', 'cml_admin_save_options_flags' );
    add_action( 'wp_ajax_ceceppaml_save_site_title', 'cml_admin_save_site_title' );

    //generate mo file
    add_action( 'wp_ajax_ceceppaml_generate_mo', 'cml_admin_generate_mo' );

    //Create a backup
    add_action( 'wp_ajax_ceceppaml_do_backup', 'cml_backup_do' );
    add_action( 'wp_ajax_ceceppaml_export_backup', 'cml_backup_export' );
    add_action( 'wp_ajax_ceceppaml_import_backup', 'cml_backup_import' );

    //Populate post list ( edit page )
    add_action( 'wp_ajax_ceceppaml_get_posts', array( & $this, 'ceceppaml_get_posts_list' ) );

    //Store custom posts slug
    add_action( 'wp_ajax_ceceppaml_translate_slugs', 'cml_admin_translated_slugs' );

    //Get the content via qem
    add_action( 'wp_ajax_ceceppaml_get_post_content', array( & $this, 'ceceppaml_get_post_content' ) );

    //My translations load strings from dbs
    add_action( 'wp_ajax_ceceppaml_get_my_translations', array( & $this, 'ceceppaml_get_my_translations' ) );

    //Widget page
    add_action( 'load-widgets.php', array( & $this, 'page_widgets' ), 10 );

    //Navigation menu
    add_action( 'load-nav-menus.php', array( & $this, 'page_menu' ), 10 );

    //Categories and tags
    add_action( 'admin_init', array ( & $this, 'setup_taxonomies_fields' ), 10 );

    //Grab theme language path
    add_filter( 'override_load_textdomain', array( &$this, 'grab_theme_locale' ), 0, 3 );

    //Add "shadow" div in the footer
    add_action( 'admin_footer', array( & $this, 'admin_footer' ) );

    //set wordpress locale
    add_filter( 'locale', array( & $this, 'setlocale' ), 0, 1 );

    //Contextual help
    add_filter( 'contextual_help', array( & $this, 'add_tips_to_help_tab' ), 10 );
  }

  function admin_scripts() {
    global $pagenow;

    if( isset( $_GET[ 'page' ] ) && "ceceppaml-language-page" == $_GET[ 'page' ] ) {
      if( 0 == intval( @$_GET[ 'tab'] ) )
      wp_register_script( 'ceceppaml-admin-languages', CML_PLUGIN_JS_URL . 'admin.languages.js', array( 'ceceppaml-admin-script' ) );
    }

    wp_register_script( 'ceceppaml-admin-script', CML_PLUGIN_JS_URL . 'admin.js' );
    wp_register_script( 'ceceppaml-tipsy', CML_PLUGIN_JS_URL . 'jquery.tipsy.js' );
    wp_register_script( 'ceceppaml-transition', CML_PLUGIN_JS_URL . 'jquery.transit.min.js' );

    //Available languages, for search
    $languages = array();
    foreach( $GLOBALS[ 'cml_flags' ] as $lang ) {
      $languages[] = $lang;
    }

    $tags = get_tags();
    $names = array();
    foreach( $tags as $tag ) {
      $names[] = array(
                      'id' => $tag->term_id,
                      'label' => $tag->name,
                      );
    }

    //For ajax
    $secret = array(
                    'secret' => wp_create_nonce( "ceceppaml-nonce" ),
                    'languages' => json_encode( $languages ),
                    'unloadmsg' => __( "The changes you made will be lost if you navigate away from this page.", 'ceceppaml' ),
                    'lessmsg' => __( "Less", "ceceppaml" ),
                    'moremsg' => __( "More", "ceceppaml" ),
                    'deletemsg' => __( "Delete", "ceceppaml" ),
                    'restoremsg' => __( "Restore", "ceceppaml" ),
                    'custommsg' => __( "Custom", "ceceppaml" ),
                    'dateformat' => get_option( 'date_format' ),
                    'default_id' => CMLLanguage::get_default_id(),
                    'tags' => json_encode( $names),
                    );

    if( 'post.php' == $pagenow ) {
      $secret[ 'post_type' ] = get_post_type( intval( $_GET[ 'post' ] ) );
    }

    wp_localize_script( 'ceceppaml-admin-script', 'ceceppaml_admin', $secret );

    //Styles
    wp_register_style( 'ceceppaml-tipsy-style', CML_PLUGIN_URL . 'css/tipsy.css' );
    wp_register_style( 'ceceppaml-admin-style', CML_PLUGIN_URL . 'css/admin.css' );
  }

  function add_menu_pages() {
    $page[] = add_menu_page('Ceceppa ML Options', __('Ceceppa Multilingua', 'ceceppaml'), 'administrator', 'ceceppaml-language-page', array(&$this, 'form_languages'), CML_PLUGIN_IMAGES_URL . 'logo-mini.png');

    $page[] = add_submenu_page( 'ceceppaml-language-page', '<div class="separator" /></div>', '<div class="cml-separator" />' . __( 'Translate', 'ceceppaml' ) . '</div>', 'administrator', '', null );

    $page[] = add_submenu_page('ceceppaml-language-page', __('My translations', 'ceceppaml'), __('My translations', 'ceceppaml'), 'manage_options', 'ceceppaml-translations-page', array(&$this, 'form_translations'));

    $tabs = apply_filters( 'cml_addon_custom_translation_menu', array() );
    foreach( $tabs as $tab ) {
      $page[] = add_submenu_page('ceceppaml-language-page', $tab['title'], $tab['title'], 'manage_options',
        admin_url() . 'admin.php?page=ceceppaml-translations-page&tab=' . $tab['tab'],
        array(&$this, 'form_translations')
      );
    }

    $page[] = add_submenu_page('ceceppaml-language-page', __('Widget titles', 'ceceppaml'), __('Widget titles', 'ceceppaml'), 'manage_options', 'ceceppaml-widgettitles-page', array(&$this, 'form_translations'));
    $page[] = add_submenu_page('ceceppaml-language-page', __('Site Title'), __( 'Site Title' ) . "/" . __( 'Tagline' ), 'manage_options', 'ceceppaml-translations-title', array(&$this, 'form_translations'));
    $page[] = add_submenu_page('ceceppaml-language-page', __('Plugin', 'ceceppaml'), __( 'Plugin', 'ceceppaml' ), 'manage_options', 'ceceppaml-language-page&tab=2', array(&$this, 'form_languages'));
    $page[] = add_submenu_page('ceceppaml-language-page', __('Theme', 'ceceppaml'), __( 'Theme', 'ceceppaml' ), 'manage_options', 'ceceppaml-translations-plugins-themes', array(&$this, 'form_translations'));
    $page[] = add_submenu_page('ceceppaml-language-page', __('Custom post slug', 'ceceppaml'), __('Custom post slug', 'ceceppaml'), 'manage_options', 'ceceppaml-translate-slug', array(&$this, 'form_translations'));

    $page[] = add_submenu_page( 'ceceppaml-language-page', '<div class="separator" /></div>', '<div class="cml-separator" />' . __( 'Flags', 'ceceppaml' ) . '</div>', 'administrator', '', null );

    $page[] = add_submenu_page('ceceppaml-language-page', __('Show flags', 'ceceppaml'), __('Show flags', 'ceceppaml'), 'manage_options', 'ceceppaml-flags-page', array( &$this, 'form_flags' ) );

    $page[] = add_submenu_page( 'ceceppaml-language-page', '<div class="separator" /></div>', '<div class="cml-separator" />' . __( 'Settings', 'ceceppaml' ) . '</div>', 'administrator', '', null );

    $page[] = add_submenu_page('ceceppaml-language-page', __('Settings', 'ceceppaml'), __('Settings', 'ceceppaml'), 'manage_options', 'ceceppaml-options-page', array(&$this, 'form_options'));
    $page[] = add_submenu_page('ceceppaml-language-page', __('Backup', 'ceceppaml'), __('Backup', 'ceceppaml'), 'manage_options', 'ceceppaml-backup-page', array(&$this, 'form_backups'));

    //Addons
    $page[] = add_submenu_page( 'ceceppaml-language-page', '<div class="separator" /></div>', '<div class="cml-separator" />' . __( 'Addons', 'ceceppaml' ) . '</div>', 'administrator', '', null );

    $page[] = add_submenu_page('ceceppaml-language-page', __('Available addons', 'ceceppaml'), __('Available addons', 'ceceppaml'), 'manage_options', 'ceceppaml-addons-page', array( & $this, 'form_addons' ) );

    //filter all installe addons
    $addons = CMLUtils::_get( '_addons', array() );
    $tab = 1;
    foreach( $addons as $addon ) {
      $title = $addon[ 'title' ];
      $link = 'ceceppaml-addons-page&tab=' . $tab;

      $page[] = add_submenu_page( 'ceceppaml-language-page',
                                  $title,
                                  $title,
                                  'manage_options',
                                  $link,
                                  array( & $this, 'form_addons' ) );

      $title = strtolower( $title );
      CMLUtils::_set( "_addon_{$title}_page", $link );
      $tab++;
    }

    //Documentation
    $page[] = add_submenu_page( 'ceceppaml-language-page', '<div class="separator" /></div>', '<div class="cml-separator" />' . __( 'Documentation', 'ceceppaml' ) . '</div>', 'administrator', '', null );
    $page[] = add_submenu_page( 'ceceppaml-language-page', __('Shortcodes', 'ceceppaml'), __('Shortcode', 'ceceppaml'), 'manage_options', 'ceceppaml-shortcode-page', array( & $this, 'shortcode_page' ) );
    $page[] = add_submenu_page( 'ceceppaml-language-page', __('Api', 'ceceppaml'), __('Api', 'ceceppaml'), 'manage_options', 'ceceppaml-api-page', array( & $this, 'api_page' ) );

    foreach( $page as $p ) :
      add_action( 'load-' . $p, array( &$this, 'add_pointers' ), 0 );           //Add wp-pointers
      add_action( 'admin-footer-' . $p, array( &$this, 'admin_footer' ) );
    endforeach;

    add_action( 'admin_print_styles', array( &$this, 'load_styles' ) );
    add_action( 'load-nav-menus.php', array( &$this, 'add_tips_to_help_tab' ) );
    add_action( 'load-options-reading.php', array( &$this, 'add_tips_to_help_tab' ) );
    add_action( 'load-options-general.php', array( &$this, 'add_tips_to_help_tab' ) );

    add_action( 'load-edit.php', array( &$this, 'add_pointers_to_post_list' ), 0 );           //Add wp-pointers
    add_action( 'load-post.php', array( &$this, 'add_pointers_to_post_meta_box' ), 0 );           //Add wp-pointers
    add_action( 'load-post-new.php', array( &$this, 'add_pointers_to_post_meta_box' ), 0 );           //Add wp-pointers

    add_action( 'load-nav-menus.php', array( &$this, 'add_pointers_to_menu_page' ), 0 );           //Add wp-pointers
  }

  function load_styles() {
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'ceceppaml-transition' );
    wp_enqueue_script( 'ceceppaml-admin-script' );
    wp_enqueue_script( 'ceceppaml-tipsy' );

    wp_enqueue_style( 'ceceppaml-tipsy-style' );
    wp_enqueue_style( 'ceceppaml-admin-style' );

    //Wp-Pointer
    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_script( 'wp-pointer' );
  }

  /*
   * Manage language
   */
  function form_languages() {
    wp_enqueue_script( 'ceceppaml-admin-languages' );

    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-languages.php' );
  }

  /*
   * Manage options
   */
  function form_options() {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-options.php' );
  }

  /*
   * Manage the backups
   */
  function form_backups() {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-backups.php' );
  }


  /*
   * Flags options
   */
  function form_flags() {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-flags.php' );
  }

  /*
   * Widget titles
   */
  function form_translations() {
    wp_enqueue_script( 'ceceppaml-admin-mytrans', CML_PLUGIN_JS_URL . 'admin.mytrans.js' );

    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-translations.php' );
  }

  /*
   * Addons
   */
  function form_addons() {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-addons.php' );
  }


  /*
   * Add "pointer"
   */
  function add_pointers() {
    global $pagenow;

    if( isset( $_GET[ 'page' ] ) && "ceceppaml-options-page" == $_GET[ 'page' ] ) {
      $this->add_pointers_to_settings_page();

      return;
    }

    if( isset( $_GET[ 'page' ] ) && "ceceppaml-language-page" == $_GET[ 'page' ] ) {
      cml_add_pointer( "#contextual-help-link", __( 'Help', 'ceceppaml' ),
                      __( 'Click here to show the meaning of language fields', 'ceceppaml' ),
                      array( "edge" => "top", 'align' => 'right' ) );
    }

    //Add pointers
    $list = "<br /><dl class=\"cml-dl-list\">";
    $list .= sprintf( '<dt> %s</dt> <dd> %s</dd>', __( 'Basic', 'ceceppaml' ), __( 'You can order languages, set the default one, enable/disable them ', 'ceceppaml' ) );
    $list .= sprintf( '<dt> %s</dt> <dd> %s</dd>', __( 'Intermediate', 'ceceppaml' ), __( 'You can also edit language name and date format', 'ceceppaml' ) );
    $list .= sprintf( '<dt> %s</dt> <dd> %s</dd>', __( 'Advanced', 'ceceppaml' ), __( 'You can change flag and edit all language fields', 'ceceppaml' ) );
    $list .= "</ul>";

    $title = __( 'Choose settings level', 'ceceppaml' );
    cml_add_pointer( ".hndle .cml-box-mode > ul", __( 'Settings', 'ceceppaml' ), $title . $list, array( "edge" => "top", 'align' => 'left' ) );

    //Available languages
    $list = __( 'This is the list of your languages.', 'ceceppaml' ) . "<br />";
    $list .= __( 'Drag items for change its order.', 'ceceppaml' ) . "<br />";
    $list .= '<dl class="cml-dl-list">';
    $list .= '<dt>' . __( 'Flag', 'ceceppaml' ) . '</dt>';
    $list .= '<dd>' . __( 'Click on flag for change or customize', 'ceceppaml' ) . "</dd>";

    $list .= '<dt><img src="' . CML_PLUGIN_IMAGES_URL . 'heart.png" /></dt>';
    $list .= '<dd>' . __( 'Click on heart for set default language', 'ceceppaml' ) . "</dd>";

    $list .= '<dt>&#10004;</dt>';
    $list .= '<dd>' . __( "Click on &#10004; for set enable/disable language", 'ceceppaml' ) . "</dd>";

    cml_add_pointer( "#cml-box-languages", __( 'Your languages', 'ceceppaml' ), $list, array( "edge" => "top", 'align' => 'left' ) );

    //Available languages
    cml_add_pointer( "#cml-box-available-languages", __( 'Add language', 'ceceppaml' ), __( 'List of all available languages.<br /> Click on item to choose flag and add it to your languages', 'ceceppaml' ), array( "edge" => "bottom", 'align' => 'left' ) );

    //Search language
    cml_add_pointer( ".cml-box-right #search", __( 'Search language', 'ceceppaml' ), __( 'Search the language from the availables and click for add it', 'ceceppaml' ), array( "edge" => "bottom", 'align' => 'left' ) );

    //Add custom language
    cml_add_pointer( ".cml-box-right input[name=\"add-custom\"]", __( 'Add custom language', 'ceceppaml' ), __( 'Click here for add custom language', 'ceceppaml' ), array( "edge" => "bottom", 'align' => 'left' ) );

    //Save languages
    cml_add_pointer( ".cml-box-right input[name=\"save-all\"]", __( 'Save languages', 'ceceppaml' ), __( 'Click here for save changes', 'ceceppaml' ), array( "edge" => "left", 'align' => 'middle' ) );

    //Theme main language
    cml_add_pointer( ".ceceppa-form-translations.theme > .cml-tablenav #cml-lang", __( 'Theme language', 'ceceppaml' ), __( 'Choose in which languages translate:', 'ceceppaml' ), array( "edge" => "right", 'align' => 'middle' ) );
  }

  function add_pointers_to_settings_page() {
    //Help
    cml_add_pointer( ".cml-first-help-wp", __( 'Help', 'ceceppaml' ), __( 'Click here for show/hide help', 'ceceppaml' ), array( "edge" => "right", 'align' => 'middle' ) );
  }

  function add_pointers_to_post_list() {
    //All posts/page filter by language
    cml_add_pointer( ".cml-lang-sel", __( 'Filter list', 'ceceppaml' ), __( 'Filter list by language', 'ceceppaml' ), array( "edge" => "left", 'align' => 'middle' ) );
    cml_add_pointer( "thead th.column-cml_flags", __( 'Filter list', 'ceceppaml' ), __( 'Click here to filter list by language', 'ceceppaml' ), array( "edge" => "left", 'align' => 'middle' ) );
    cml_add_pointer( "thead .cml-filter-current", __( 'Active language', 'ceceppaml' ), __( 'This is the style of current language', 'ceceppaml' ), array( "edge" => "left", 'align' => 'middle' ) );
  }

  //Post data "box"
  function add_pointers_to_post_meta_box() {
    $msg = __( 'Choose the language of current post/page.', 'ceceppaml' ) . "<br />";
    $msg .= "<br /><strong>" . __( 'All languages', 'ceceppaml' ) . "</strong>&nbsp;";
    $msg .= __( 'means that post/page will available in all languages', 'ceceppaml' );
    $msg .= "<br />";
    cml_add_pointer( "#ceceppaml-meta-box #cml-lang", __( 'Post/Page language', 'ceceppaml' ), $msg, array( "edge" => "right", 'align' => 'middle' ) );

    //choose
    $msg = __( 'Assign translations', 'ceceppaml' ) . "<br /><br />";
    $msg .= "<strong>" . __( "Assign the same post to multiple languages.", "ceceppaml" ) . "</strong><br />";
    $msg .= __( "If you need you can assign post to multiple language", "ceceppaml" ) . ".<br />";
    $msg .= "<i>" . __( "In this case you can't choose the option \"All languages\" from \"Language\" combo, so you have to choose one of it existing languages, isn't important which one.", "ceceppaml" );
    cml_add_pointer( "#ceceppaml-meta-box .cml-post-translations", __( 'Translations', 'ceceppaml' ), $msg, array( "edge" => "right", 'align' => 'middle' ) );

    //add/edit
    cml_add_pointer( ".cml-post-translations > li:first-child .cml-button-edit", __( 'Translations', 'ceceppaml' ), __( 'Click here Add/Edit translation', 'ceceppaml' ), array( "edge" => "right", 'align' => 'middle' ) );

    //add/edit
    $text = __( 'Permalink will modified in according to current "Url modification mode" setting.', 'ceceppaml' ) . "<br />";
    $text .= __( "When two post/page has same title Wordpress automatically append a number -## to permalink, don't worry the plugin will remove it in fronted.", 'ceceppaml' );
    cml_add_pointer( "#sample-permalink #editable-post-name", __( 'Permalink', 'ceceppaml' ), $text, array( "edge" => "left", 'align' => 'middle' ) );
  }

  function add_pointers_to_menu_page() {
    $msg = __( 'For each languages you can customize "Navigation label" or url, but you have to save menu first', 'ceceppaml' );

    cml_add_pointer( "#nav-menu-header .publishing-action #save_menu_header",
                    __( 'Customize labels', 'ceceppaml' ),
                    $msg,
                    array( "edge" => "right", 'align' => 'middle' ) );

    cml_add_pointer( 'form[name="cml-menu-primary-name"] select',
                    __( 'Set menu to switch', 'ceceppaml' ),
                    __( 'Your theme support multiple menus, if you need to use different menu for each language you have to choose witch one switch', 'ceceppaml' ),
                    array( "edge" => "top", 'align' => 'left' ) );

  }

  function add_tips_to_help_tab() {
    cml_admin_add_help_tab();
  }

  //Widget visibility
  function page_widgets() {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-widgets.php' );
  }

  //  Menu metabox
  function page_menu() {
   require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-menu.php' );
  }

  function admin_footer() {
    echo '<div class="cml-box-shadow"></div>';
  }

  function grab_theme_locale( $false, $domain, $mofile ) {
    global $pagenow;

    //Recupero il nome del tema corrente
    $theme = wp_get_theme();

    $path = get_template_directory();	//Path del tema
    $info = pathinfo( $mofile );

    if( strcasecmp( $info[ 'dirname' ], $path ) > 0 ) {
      $GLOBALS[ '_cml_theme_locale_path' ] = $info[ 'dirname' ];

      //Nome del tema e path "locale"
      update_option( 'cml_current_theme', $theme->Name );
      update_option( 'cml_current_theme_locale', $info[ 'dirname' ] );

      //Fatto
      //remove_filter( 'load_textdomain_mofile', array( &$this, 'grab_theme_locale' ), 0, 2 );
    }

    return false;
  }

  function setup_taxonomies_fields() {
    global $pagenow;

    if( get_option( 'cml_need_update_settings', 0 ) && ! defined( 'DOING_AJAX' ) ) {
      cml_generate_mo_from_translations();

      delete_option( 'cml_need_update_settings' );
    }

    if( $pagenow != "nav-menus.php" ) {

      $taxonomies = get_taxonomies();

      foreach( $taxonomies as $taxonomy ) {
        add_action( "${taxonomy}_edit_form_fields", 'cml_admin_taxonomy_edit_form_fields', 10, 1 );
        add_action( "${taxonomy}_add_form_fields", 'cml_admin_taxonomy_add_form_fields', 10, 1 );
        add_action( "edited_{$taxonomy}", array( & $this, 'save_taxonomies_extra_fields' ), 10, 1 );
        add_action( "created_{$taxonomy}", array( & $this, 'save_taxonomies_extra_fields' ), 10, 1 );
        add_action( "delete_{$taxonomy}", array( & $this, 'delete_taxonomies_extra_fields' ), 10, 1 );
      }

      add_action( 'load-edit-tags.php', array( & $this, 'taxonomies_extra_fields' ) , 10 );
      add_action( 'cml_taxonomies_extra_fields', array( & $this, 'taxonomies_extra_fields' ) , 10 );
    }

    //Translate term
    add_filter( 'get_term', array( & $this, 'translate_term' ), 0, 2 );
    add_filter( 'get_terms', array( & $this, 'translate_terms' ), 0, 3 );
    add_filter( 'get_the_terms', array( & $this, 'translate_terms' ), 0, 3 );
    add_filter( 'the_category', array( & $this, 'translate_the_category' ), 0, 3 );
  }

  function translate_term( $term, $taxonomy ) {
    global $pagenow, $wpdb;

    CMLUtils::_del( '_rewrite_rules' );
    CMLUtils::_del( '_rewrite_url' );
    unset( $GLOBALS[ '_cml_no_translate_home_url' ] );

    if( ! is_object( $term ) ) {
      return $term;
    }

    if( in_array( $pagenow, array( "edit.php", "post.php" ) ) ) {
      if( method_exists( $wpdb, 'get_the_ID' ) )
        $lang = CMLPost::get_language_id_by_id( get_the_ID() );
      else
        $lang = CMLLanguage::get_current_id();

      $lang = CMLUtils::_get( '_forced_language_slug', $lang );
    } else {
      if( "edit-tags.php" == $pagenow && isset( $_GET[ 'action' ] ) ) {
        $lang = CMLLanguage::get_default_id();
      } else {
        $lang = CMLLanguage::get_current_id();
      }
    }

    $tterm = $this->get_translated_term( $term, $lang );

    // $name = CMLTranslations::get( $lang, $term->taxonomy . "_" . $term->name, "C", true );
    if( is_object( $tterm ) && ! empty( $tterm->name ) ) {
      $term->name = $tterm->name;

      /*
       * Translate the slug only in 'edit-tags.php' main page, as I need the
       * url in according to the current language.
       */
      if( "edit-tags.php" != $pagenow || ( "edit-tags.php" == $pagenow && ! isset( $_GET[ 'action' ] ) ) ) {
        $term->slug = $tterm->slug;
      }
    }

    return $term;
  }

  function translate_terms( $terms, $post_id, $taxonomy ) {
    global $pagenow;

    if( CMLLanguage::is_current( CMLUtils::_get( '_forced_language_slug', CMLLanguage::get_default_id() ) ) ) {
      return $terms;
    }

    $t = array();
    foreach( $terms as $term ) {
      $t[] = $this->translate_term( $term, $taxonomy );
    }

    return $t;
  }

  /*
   * translate category name
   */
  function translate_the_category( $name ) {
    global $pagenow;

    if( 'post.php' !== $pagenow && ! isset( $_GET['action'] ) ) return $name;

    $lang = CMLLanguage::get_id_by_post_id( get_the_ID() );

    //Default language? no translation required ;)
    if( CMLLanguage::is_default( $lang ) ) return $name;

    $name = CMLTaxonomies::get_translation( $lang, $name );

    // $category = $this->translate_terms( array( 0 => $category ), null, null, $lang );
    return $name;
  }

  function taxonomies_extra_fields() {
    wp_enqueue_script( 'ceceppaml-admin-taxonomies', CML_PLUGIN_JS_URL . 'admin.taxonomies.js' );

    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-taxonomies.php' );
  }

  function save_taxonomies_extra_fields( $term_id ) {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-taxonomies.php' );

    cml_admin_save_extra_taxonomy_fileds( $term_id );
  }

  function delete_taxonomies_extra_fields( $term_id ) {
    require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-taxonomies.php' );

    cml_admin_delete_extra_taxonomy_fields( $term_id );
  }

  function shortcode_page() {
    require_once( CML_PLUGIN_DOC_PATH . "shortcodes.php" );
  }

  function api_page() {
    require_once( CML_PLUGIN_DOC_PATH . "api.php" );
  }

  function setlocale( $locale ) {
    global $pagenow;

    if( $pagenow == "wp-login.php" ) return $locale;
    if( isset( $_GET[ 'page' ] ) &&
        $_GET[ 'page' ] == 'ceceppaml-widgettitles-page' ) return "en_US";

    $lang = CMLLanguage::get_default();
    if( empty( $lang ) ) return $locale;

    $slug = $lang->cml_language_slug;

    $logged_in = function_exists( 'is_user_logged_in' ) && is_user_logged_in();
    $user = get_current_user_id();
    if( ! defined( 'DOING_AJAX' ) && $logged_in ) {
      $slug = get_option( "cml_${user}_locale", CMLLanguage::get_current() );
    } else if( isset( $_COOKIE[ '_cml_language' ] ) ) {
      $lang = $_COOKIE[ '_cml_language' ];

      $slug = CMLLanguage::get_slug( $lang );
    }

    $slug = isset( $_GET[ 'lang' ] ) ? esc_attr( $_GET[ 'lang' ] ) : $slug;

    //get language by slug
    $lang = CMLLanguage::get_by_slug( $slug );
    $locale = $lang->cml_locale;

    //Set current user language;
    if( ! defined( 'DOING_AJAX' ) && isset( $logged_in ) && isset( $user ) ) {
      update_option( "cml_${user}_locale", $lang->cml_language_slug );
    }

    if( isset( $_POST[ 'lang' ] ) ) {
      $locale = $_POST[ 'lang' ];

      $lang = CMLLanguage::get_id_by_locale( $locale );
      $lang = CMLLanguage::get_by_id( $lang );

      CMLUtils::_set( '_ajax_language', $lang->id );
    }

    CMLLanguage::set_current( $lang );
    CMLUtils::_set( '_real_language', $lang->id );
    CMLLanguage::set_current( CMLLanguage::get_id_by_locale( $locale ) );

    if( empty( $_POST ) && 'options-permalink.php' != $pagenow && $this->_translate_category_slug && ! isset( $this->_translated_slug ) ) {
      $permalinks = get_option( "cml_category_slugs", array() );

      update_option( 'category_base', $permalinks[ CMLLanguage::get_current_id() ] );

      $this->_translated_slug = true;
    }

    return $locale;
  }

  function wizard() {
    if( isset( $_GET[ 'wdone' ] ) ) {
      update_option( "cml_show_wizard", false );
      delete_option( "_cml_update_existings_posts" );

      return;
    }

    if( ! current_user_can( 'manage_options' ) ) return;

    require_once ( CML_PLUGIN_ADMIN_PATH . 'wizard.php' );
  }

  function no_tables_found() {
    $error = __( "No tables found, Ceceppa Multilingua can't works correctly.", "ceceppaml" ) . "<br />";
    $error .= __( "Disable and enable again the plugin for rebuild tables.", "ceceppaml" );
echo <<< EOT
    <div class="updated">
      <p>
        $error
      </p>
    </div>
EOT;
  }

  /*
   * update language of existings posts
   */
  function update_all_posts_language() {
    cml_update_all_posts_language();

    cml_fix_rebuild_posts_info();
  }

  /*
   * add language info on items in "nav-menus.php" page :)
   */
  function translate_menu_item( $item ) {
    global $pagenow;

    if( $pagenow != "nav-menus.php" ) {
      return $item;
    }

    switch($item->object) {
    case 'page':
    case 'post':
      $lang = CMLLanguage::get_by_post_id( $item->ID );

      $item->post_title .= " (" . CMLLanguage::get_slug( $lang ) . ")";
      break;
    default:
      return $item;
    } //endswitch;

    return $item;
  }

  /*
   * scan plugins folders
   */
  function plugin_activated() {
    update_option( '_cml_scan_folders', 1 );
  }

  function scan_plugin_folders() {
    if( 1 == get_option( '_cml_scan_folders' ) ) {
      add_action( 'admin_notices', 'cml_admin_scan_plugins_folders' );
    }
  }

  /*
   * register addon page link
   */
  function register_addons() {
    $addons = array();

    do_action_ref_array( 'cml_register_addons', array( & $addons ) );

    $i = 1;
    foreach( $addons as $addon ) {
      $page = admin_url() . "admin.php?page=ceceppaml-addons-page&tab=" . $i++;

      CMLUtils::_set( "_" . strtolower( $addon[ 'title' ] ) . "_addon_page", $page );
    }

    CMLUtils::_set( '_addons', $addons );
  }

  function no_translate_home_url( $b ) {
    if( $b ) {
      CMLUtils::_set( '_rewrite_url', 1 );
    }

    return $b;
  }

  function ceceppaml_get_posts_list() {
    if( ! check_ajax_referer( "ceceppaml-nonce", "security" ) ) {
      echo -1;
      die();
    }

    _cml_admin_post_meta_translation( $_POST[ 'post_type' ], $_POST[ 'lang_id' ], 0, 0, true );
    die();
  }

  function ceceppaml_get_post_content() {
    if( ! check_ajax_referer( "ceceppaml-nonce", "security" ) ) {
      echo -1;
      die();
    }

    $post = get_post( intval( $_POST[ 'post_id' ] ) );

    $json = array(
                  'lang' => intval( $_POST[ 'lang_id' ] ),
                  'title' => $post->post_title,
                  'content' => $post->post_content
                );

    echo json_encode( $json );
    die();
  }
}

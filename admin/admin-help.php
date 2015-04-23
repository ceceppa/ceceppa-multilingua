<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

function cml_admin_help_widgets( ) {
  $text = "Ceceppa Multilingua: <strong>" . __( 'Tip', 'ceceppaml' ) . "</strong><br /><br />";
  $text .= __( 'You can translate widget titles, or filter its visibility by language', 'ceceppaml' );

  return $text;
}

function cml_admin_help_menu( ) {
  $text = "Ceceppa Multilingua: <strong>" . __( 'Tip', 'ceceppaml' ) . "</strong><br /><br />";
  $text .= __('All items will be automatically translated when user switch between languages.', 'ceceppaml') . "<br />";
  $text .= '<font style="color: #f00">' . __('Add only pages existing in your default language, not their translation.', 'ceceppaml') . '</font><br />';
  $text .= "<br />";
  $text .= __( 'You can customize navigation label in all language, or, for custom link, set different url for each language', 'ceceppaml' );
  $text .= "<br />";
  $text .= "<br />";
  $text .= __( 'If you need different items for each languages, you can create menu for each language', 'ceceppaml' );
  $text .= "<br /><br />";

  return $text;
}

function cml_admin_help_options_reading() {
  $link = home_url('wp-admin') . "/admin.php?page=ceceppaml-translations-title";

  $text = "Ceceppa Multilingua: <strong>" . __( 'Tip', 'ceceppaml' ) . "</strong><br /><br />";
  $text .= "<a href='$link'>" . __( 'Clicke here for translate the "Site Title" and "Tagline" in other languages' , 'ceceppaml' ) . "</a><br /><br />";
  $text .= __( 'Your theme must use the function: ', 'ceceppaml' );
  $text .= "get_bloginfo( 'name', 'display' );";
  $text .= __( 'not: ', 'ceceppaml' );
  $text .= "get_bloginfo( 'name' );";
  $text .= __( 'Or Site/Tagline will not be translated!', 'ceceppaml' );
  $text .= '<span style="color: red"><strongs>' . __('N.B.: When make changes to Site Title or Tagline you have to update translations', 'ceceppaml') . "<br />";

  return $text;
}

function cml_admin_help_options_flags() {
  $text = sprintf(
                  __( "You can override flags style by creating new file in: <i>%s</i> named: <b>\"ceceppaml.css\"</b>", "ceceppaml" ),
                      CML_UPLOAD_DIR );

  $text .= "<br /><br />\n";
  $text .= __( "The class base is \"cml_flags\", and second class is added by each option", "ceceppaml" );
  $text .= "<br />\n";
  $text .= __( "Each option will generate &lt;ul&gt; list and current language will have \"current\" class", "ceceppaml" );
  $text .= __( "Class used by options:", "ceceppaml" );

  $show_flags = __( 'Show flags:', 'ceceppaml' );
  $add  = __( 'Add float div to website:', 'ceceppaml' );
  $option = __( 'This option will generate &lt;div&gt; with id #flying-flags that will contain the &lt;ul&gt; list', 'ceceppaml' );
  $append = __( 'Append flag to html element:', 'ceceppaml' );
  $strong = __( "Don't overwrite \"ceceppaml.css\" in plugin path, because it will be overwritten by next update", "ceceppaml" );

  $text .= <<< EOT
  <dl class="cml-dl-list">
    <dt>
      $show_flags
    </dt>
      <dd>
        .cml_flags_on
      </dd>

    <dt>
      $add
    </dt>
      <dd>
        $option
      </dd>

    <dt>
      $append
    </dt>
      <dd>
        .cml_append_flags_to
      </dd>

  </dl>
</p>

<p>
  <strong>
    $strong
  </strong>
</p>
EOT;

  return $text;
}

function cml_admin_help_languages_setup() {
  $text[] = __( "In \"My Languages\" box are listed your languages.", "ceceppaml" ) . "<br />";
  $text[] = __( "For add new language click on item listed in \"Available languages\", and click on wanted flag to add it.", "ceceppaml" ) . "<br />";
  $text[] = __( "You can also use search box to search a language, or click on \"Add custon\" button to add custom one.", "ceceppaml" ) . "<br />";
  $text[] = '<ul>';
  $text[] = '<li><strong>' . __( 'Name', 'ceceppaml' ) . ':</strong>';
  $text[] = __( "it is the name of language.", "ceceppaml" );
  $text[] = '</li>';
  $text[] = '<li><strong>' . __( 'Date format', 'ceceppaml' ) . ':</strong>';
  $text[] = __( "you can use different date format for language.", "ceceppaml" );
  $text[] = '</li>';
  $text[] = '<li><strong>' . __( 'Language slug', 'ceceppaml' ) . ':</strong>';
  $text[] = __( "this code will be used to build url. Example: it, en, ch, de", "ceceppaml" );
  $text[] = '</li>';
  $text[] = '<li><strong>' . __( 'WP Locale', 'ceceppaml' ) . ':</strong>';
  $text[] = __( "it is the code used by WordPress for each language.", "ceceppaml" );
  $text[] = '</li>';
  $text[] = '<li><strong>' . __( 'Right to left', 'ceceppaml' ) . ':</strong>';
  $text[] = __( "it is used for right to left languages like Arabian, Hebrew and others.", "ceceppaml" );
  $text[] = '</li>';
  $text[] = '</ul>';
  $text[] = "";
  $text[] = __( "The order of language items will be used for sort generated flags.", "ceceppaml" );

  return join ( "\n", $text );
}

function cml_admin_help_languages() {
  $text[] = __( "When you choose to upload custom flags the plugin will create two copy of image Small ( 32x23 ) and Tiny ( 16x11 ).", "ceceppaml" );
  $text[] = sprintf(
              __( "If you don't like the quality of genearted images you can do manually for both size, and store it in %s, and later choose it from list.", "ceceppaml" ),
              CML_UPLOAD_DIR );
  $text[] = "";
  $text[] = "";
  $text[] = sprintf( __( "Custom flags will be stored in: %s path.", "ceceppaml" ), CML_UPLOAD_DIR );

  return join ( "<br />\n", $text );
}

function cml_add_ie_notice() {
  $text[] = "<strong>" . __( "You are using IE <= 9", "ceceppaml" ) . "</strong>";
  $text[] = "";
  $text[] = sprintf( __( "If you need to use custom flags you hate to <%s>upload them manually</a>.", "ceceppaml" ),
                    'a href="http://www.alessandrosenese.eu/en/ceceppa-multilingua/language-setup#customize-flag" target="_blank"' );

  return join ( "<br />\n", $text );
}

function cml_admin_print_notice( $key, $text ) {
  //Hide tip?
  if( ! get_option( $key, 1 ) ) return;
  if( isset( $_GET[ $key ] ) && $_GET[ $key ] == 0 ) {
    update_option( $key, 0 );

    return;
  }

  $link = esc_url( add_query_arg( array( $key => 0 ) ) );
  $dismiss = __( 'Hide this message', 'ceceppaml' );
echo <<< EOT
  <div class="updated">
    <p>
    $text

    <div class="cml-dismiss">
      <a href="$link" class="button">$dismiss</a>
    </div>
    </p>
  </div>
EOT;
}

function cml_admin_add_notices() {
  global $pagenow;

  if( $pagenow == "nav-menus.php" )
    cml_admin_print_notice( 'cml_show_notice_nav_menus', cml_admin_help_menu() );

  if( $pagenow == "widgets.php" )
    cml_admin_print_notice( "cml_show_widget_notice", cml_admin_help_widgets() );

  if( $pagenow == "options-general.php" && ! isset( $_GET[ 'page' ] ) )
    cml_admin_print_notice( "cml_show_widget_notice", cml_admin_help_options_reading() );

  if( $pagenow == 'admin.php' && 'ceceppaml-flags-page' == @$_GET[ 'page' ] )
    cml_admin_print_notice( "cml_show_flags_notice", cml_admin_help_options_flags() );

  if( $pagenow == 'admin.php' && 'ceceppaml-language-page' == @$_GET[ 'page' ] && intval( @$_GET[ 'tab' ] ) <= 0 ) {
    cml_admin_print_notice( "cml_show_languages_notice", cml_admin_help_languages() );

    if( preg_match( '/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT'] ) ) {
      cml_admin_print_notice( "cml_ie_notice", cml_add_ie_notice() );
    }
  }
}


function cml_admin_add_help_tab() {
  global $pagenow;
  $screen = get_current_screen();

  if( $pagenow == 'nav-menus.php' ) {
    $screen->add_help_tab( array(
            'id'       => 'cml_nav_menu_help',
            'title'    => 'Ceceppa Multilingua',
            'content'  => "<p>" . cml_admin_help_menu() . "</p>" ) );
  }

  if( $pagenow == 'options-general.php' ) {
    $screen->add_help_tab( array(
            'id'       => 'cml_nav_menu_help',
            'title'    => 'Ceceppa Multilingua',
            'content'  => "<p>" . cml_admin_help_options_reading() . "</p>" ) );
  }

  if( $pagenow == 'admin.php' && 'ceceppaml-flags-page' == @$_GET[ 'page' ] ) {
    $screen->add_help_tab( array(
            'id'       => 'cml_nav_menu_help',
            'title'    => 'Ceceppa Multilingua',
            'content'  => "<p>" . cml_admin_help_options_flags() . "</p>" ) );
  }

  if( $pagenow == 'admin.php' && 'ceceppaml-language-page' == @$_GET[ 'page' ] && intval( @$_GET[ 'tab' ] ) <= 0 ) {
    $screen->add_help_tab( array(
            'id'       => 'cml_help_language_setup',
            'title'    => 'Language setup',
            'content'  => "<p>" . cml_admin_help_languages_setup() . "</p>" ) );

    $screen->add_help_tab( array(
            'id'       => 'cml_help_language_custom',
            'title'    => 'Customize flags',
            'content'  => "<p>" . cml_admin_help_languages() . "</p>" ) );
  }

}

add_action( "admin_notices", 'cml_admin_add_notices' );
?>

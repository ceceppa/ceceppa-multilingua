<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * CeceppaMultilingua parser class
 *
 * This class is used for "scan" theme or plugin folder and search for translable strings ( __, _e, esc_html__, etc )
 * in desired directory
 *
 */
require_once( CML_PLUGIN_ADMIN_PATH . 'php-mo.php' );

Class CMLParser {
  protected $_domains = null;
  protected $_name = null;
  protected $_default_lang = null;
  protected $_src_path = null;
  protected $_dest_path = null;
  protected $_mopo_files = null;
  protected $_show_generated = null;
  protected $_form_name = "theme";
  protected $_fuzzy_strings = array();

  /*
   * Parse php files in source path for search translable strings
   *
   * @param $name - title of theme/plugin
   * @param $languages - array containing languages id in which translate current theme
   * @param $src_path - folder to search
   * @param $dest_path - folder where write generated .mo/.po file
   * @param $show_generated - show generated files
   */
  function __construct( $name, $src_path, $dest_path, $domain = "", $show_generated = true, $form_name = "theme", $echo = true ) {
    $this->_name = $name;
    $this->_translate_in = get_option( 'cml_translate_' . $name . "_in", array() );
    $this->_src_path = trailingslashit( $src_path );
    $this->_dest_path = trailingslashit( $dest_path );
    $this->_show_generated = $show_generated;
    $this->_domain = $domain;
    $this->_form_name = $form_name;
    $this->_strings = array();

    if( isset( $_POST[ 'generate' ] ) ) {
      $this->generate_po_file();

      return;
    }

    //Look for existings .po files
    $this->parse_po_file();

    if( empty( $this->_strings ) ) {
      return $this->error( sprintf( __( 'No .po files found for %s', 'ceceppaml' ), $name ) );
    }

    $this->print_table();
  }

  /*
   * Parse all php files for looking l10n functions
   */
  private function parse_po_file() {
    $domain = empty( $this->_domain ) ? "" : "$this->_domain-";

    $files = glob( $this->_dest_path . "$domain*.po" );
    foreach( $files as $file ) {
      $parts = pathinfo( $file );

      $locale = $parts[ 'filename' ];
      $locale = str_replace( $this->_domain . "-", "", $locale );
      $id = CMLLanguage::get_id_by_locale( $locale );

      //Filename
      if( file_exists( $file ) ) {
        $this->parse_po( $file, $id );
      }
    }
  }

  private function parse_po( $filename, $lang_id ) {
    $content = @file( $filename );

    $this->_strings[ $lang_id ] = phpmo_parse_po_file( $filename );
  }

  /*
   * print wordpress "error" div
   */
  private function error( $msg ) {
    if( ! $this->_show_generated ) return;

echo <<< EOT
<div class="error">
  <p>
    $msg
  </p>
</div>
EOT;
  }

  private function print_table() {
    //All languages
    $langs = array();
    foreach( $this->_translate_in as $id ) {
      $langs[ $id ] = CMLLanguage::get_by_id( $id );
    }

    //Header
    $this->print_table_header( $langs );

    //Body
    $this->print_table_body( $langs );
  }

  /*
   * print table header
   */
  private function print_table_header( $langs ) {
    echo "<h3 class='nav-tab-wrapper'>" . $this->_name . "</h3>";

    $keys = array_filter( $this->_strings );

    $available = __( 'Available languages: ', 'ceceppaml' );
    $main = __( 'Translate in:', 'ceceppaml' );
    $tipsy = __( 'Choose in which languages you want to translate: ', 'ceceppaml' ) . $this->_name;

    $self = $_SERVER['PHP_SELF'];
    $page = $_GET['page'];
    $tab = isset( $_GET[ 'tab' ] ) ? intval( $_GET[ 'tab' ] ) : 0;

    $nonce = wp_nonce_field( "security", "ceceppaml-nonce", true, false );

    $page = $_GET[ 'page' ];
    $tab = intval( @$_GET[ 'tab' ] );

echo <<< EOT
    <form class="ceceppa-form-translations $this->_form_name" name="wrap" method="post" action="$self?page={$page}&tab={$tab}">
    <input type="hidden" name="generate" value="1">
    <input type="hidden" name="action" value="ceceppaml_generate_mo">
    <input type="hidden" name="page" value="$page" />
    <input type="hidden" name="tab" value="$tab" />
    <input type="hidden" name="src_path" value="$this->_src_path" />
    <input type="hidden" name="dest_path" value="$this->_dest_path" />
    <input type="hidden" name="domain" value="$this->_domain" />
    <input type="hidden" name="locale" value="$this->_default_lang" />
    <input type="hidden" name="name" value="$this->_name" />
    $nonce

    <div class="cml-tablenav">
      <div class="alignleft tipsy-s" title="$tipsy">
        $main&nbsp;&nbsp;
EOT;
  $in = $this->_translate_in;
  foreach( CMLLanguage::get_all() as $lang ) {
    echo cml_utils_create_checkbox( $lang->cml_language, $lang->id,
                                "cml-lang[$lang->id]", null, 1, in_array( $lang->id, $in ) );
  }
?>
      </div>
    </div>
    <h2 class="nav-tab-wrapper tab-strings">
      &nbsp;
      <a class="nav-tab  nav-tab-active" href="javascript:showStrings( 0 )"><?php _e( 'All strings', 'ceceppaml' ) ?><span></span></a>
      <a class="nav-tab" href="javascript:showStrings( 1, 'to-translate' )"><?php _e( 'To translate', 'ceceppaml' ) ?><span></span></a>
      <a class="nav-tab" href="javascript:showStrings( 2, 'incomplete' )"><?php _e( 'Incomplete', 'ceceppaml' ) ?><span></span></a>
      <a class="nav-tab" href="javascript:showStrings( 3, 'translated' )"><?php _e( 'Translated', 'ceceppaml' ) ?><span></span></a>
    </h2>

    <?php
      $no_language_selected = empty( $this->_translate_in ) || ! is_array( $this->_translate_in );
    ?>

<div class="cml-tab-wrapper cml-tab-strings">
  <div class="cml-left-items">
    <div id="cml-search">
      <?php
      if( $no_language_selected ) :
        _e( "You have to choose in which language you want to translate: ", 'ceceppaml' );
        echo $this->_name;
        echo '<input type="hidden" name="nolang" value="1" />';
      else:
        echo '<input type="search" name="s" id="filter" placeholder="' . __( 'Search', 'ceceppaml' ) . '" value="" size="40" />';
      endif;
      ?>
    </div>
  </div>
  <div class="cml-right-items">
    <div class="empty"></div>
    <a class="cml-button tipsy-me" id="cml-save" title="<?php _e( 'Save changes', 'ceceppaml' ) ?>"
       onclick="jQuery( '.ceceppa-form-translations' ).submit()">
      <?php _e( 'Save Changes', 'ceceppaml' ) ?>
    </a>
  </div>

  <div style="clear:both"></div>
</div>

  <?php if( $no_language_selected ) return ; ?>
    <table class="widefat ceceppaml-theme-translations">
      <thead>
        <tr>
          <th><?php _e( 'Text', 'ceceppaml' ) ?></th>
          <th><?php _e( 'Translation', 'ceceppaml' ) ?></th>
        </tr>
      </thead>
      <tbody>
<?php
  }

  private function print_table_body( $langs ) {
    $alternate = "";

    if( empty( $this->_translate_in ) || ! is_array( $this->_translate_in ) ) {
      echo '</form>';

      return;
    }

    $keys = array();
    $r = 0;
    $id = 0;
    foreach( $this->_strings as $lang => $entries ) {
      foreach( $entries as $entry ) {
        if( empty( $entry[ 'msgid' ] ) || '""' == $entry[ 'msgid' ] ) {
          continue;
        }

        if( in_array( $entry[ 'msgid' ], $keys ) ) {
          continue;
        }

        $keys[] = $entry[ 'msgid' ];

        $msgid = $entry[ 'msgid' ];


        $inputs = "";
        $class = "";
        $translated = count( $this->_translate_in );

        foreach( $this->_translate_in as $lang ) {
          if( in_array( $lang, $this->_translate_in ) ) {
            if( isset( $this->_strings[ $lang ] ) ) {
              $msg = $this->get_msgstr( $lang, $msgid );

              $text = $msg[ 'msg' ];
            } else {
              $text = "";
            }

            if( ! empty( $text ) ) {
              $translated--;
            }

            $inputs .= '<div class="ceceppaml-trans-fields">';
            $inputs .= '<img src="' . CMLLanguage::get_flag_src( $lang ) . '" class="available-lang" />';
            $inputs .= '&nbsp;<textarea name="string[' . $lang . '][' . $id . ']">';
            $inputs .= esc_html( br2nl( stripslashes( $text ) ) );
            $inputs .= "</textarea>";

            $is_fuzzy = @$msg[ 'fuzzy' ];
            if( $is_fuzzy ) {
              $class = "string-incomplete";
            }

            $inputs .= '<input class="cml-fuzzy tipsy-w" type="checkbox" value="1" name="fuzzy['. $lang . '][' . $id . ']" ' . checked( $is_fuzzy, 1, false ) . ' title="' . __( 'fuzzy', 'ceceppaml' ) . '" />';
            $inputs .= "</div>";
          }

        } //foreach

        $id++;

        if( $translated == 0 ) {
          $class = "translated $class";
        } else {
          $class = "to-translate $class";
        }

        if( $translated > 0 && $translated < count( $this->_translate_in ) ) {
          $class = "to-translate string-incomplete";
        }

        $alternate = ( empty( $alternate ) ) ? "alternate" : "";

        echo '<tr class="' . $alternate . ' row-domain string-' . $class . '">';
        echo '<td class="item">' . htmlentities( stripcslashes( $msgid ) ) . '</td>';

        echo "<td>";
        echo $inputs;
        echo "</tr>";
      }
    } // $keys as $d

    //Memorizzo le stringhe originali in un file "temporaneo", così evito la conversione degli elementi html ( &rsquo;, etc... )
    $outfilename = $this->_dest_path . "/tmp.pot";
    $out = @file_put_contents( $outfilename, implode( "\n", $keys ) );
    if( ! $out ) {
      $this->error( sprintf( __( 'Failed to open stream %s: Permission denied', 'ceceppaml' ), $outfilename ) );

      echo '<input type="hidden" name="error" value="1" />';
    }

    echo '</tbody>';
    echo '</table>';
  }

  /*
   * Retrive translated string from $_POST and generate .po file
   */
  function generate_po_file() {
    //Se qualcosa è andato storto lo dico
    if( empty( $this->_dest_path ) ) {
      return $this->error( __( 'Something goes wrong :\'(. I don\'t know where to store .mo file', 'ceceppaml' ) );
    }

    //If dest_path doesn't exists, I create it
    if( ! file_exists( $this->_dest_path ) ) {
      if ( ! mkdir( $this->_dest_path ) )
      return $this->error( printf( __( 'Failed to create folder: %s', 'ceceppaml' ), $this->_dest_path ) );
    }

    //Retrive original strings from temporary file "tmp.pot"
    if( ! file_exists( $this->_dest_path . "/tmp.pot" ) ) {
      if( $this->_show_generated )
        return $this->error( printf( __( "tmp.pot not found: %s", 'ceceppaml' ), $this->_dest_path ) );
      else
        return;
    }

    $originals = explode( "\n", file_get_contents( $this->_dest_path . "/tmp.pot" ) );

    //.po header
    $header = file_get_contents( CML_PLUGIN_ADMIN_PATH . "header.po" );

    //Translate in...
    $langs = $this->_translate_in;

    $done = array(); //File completati
    $outfiles = array();

    $domain = empty( $this->_domain ) ? "" : "$this->_domain-";
    foreach( $langs as $id ) {
      $lang = CMLLanguage::get_by_id( $id );
      $filename = "{$this->_dest_path}$domain{$lang->cml_locale}.po";

      $shortname = $lang->cml_locale;
      $fp = @fopen( $filename, 'w' );
      if( !$fp ) {
        $this->error( sprintf( __( 'Error writing file: %s', 'ceceppaml' ), $filename ) );
        $this->_errors[] = $filename;

        continue;
      }

      //Header
      $h = $header;

      //User info
      $user = wp_get_current_user();

      $theme = wp_get_theme();
      $h = str_replace( '%PROJECT%', $theme->get( 'Name' ), $h );
      $h = str_replace( '%AUTHOR%', $user->user_firstname . " " . $user->user_lastname, $h );
      $h = str_replace( '%EMAIL%', $user->user_email, $h );
      $h = str_replace( '%LOCALE%', $lang->cml_locale, $h );
      fwrite( $fp, $h . PHP_EOL );

      $strings = @$_POST[ 'string' ][ $lang->id ];
      $fuzzy = @$_POST[ 'fuzzy' ][ $lang->id ];

      if( empty( $strings ) ) continue;

      for( $i = 0; $i <= count( $originals ); $i++ ) {
        if( ! isset( $originals[ $i ] ) ) continue;
        if( empty( $strings[ $i ] ) ) continue;

        $o = str_replace( "\"", '\"', stripslashes( $originals[$i] ) );
        $s = str_replace( "\"", '\"', stripslashes( $strings[$i] ) );
        $o = 'msgid "' . $o . '"' . PHP_EOL;
        $s = 'msgstr "' . nl2br2( $s ) . '"' . PHP_EOL . PHP_EOL;

        if( isset( $fuzzy[ $i ] ) ) {
          fwrite( $fp, "#, fuzzy" . PHP_EOL );
        }

        fwrite( $fp, $o );
        fwrite( $fp, $s );
      }

      fclose( $fp );

      //Try to convert .po in .mo file
      $output = $this->convert_po( $filename );

      if( ! empty( $output ) )
        $this->error( "<b>" . $output . "</b>: " . $filename );
      else {
        $done[] = $filename;
        $outfiles[] = $lang->id;
      }
    }

    //File generati
    if( $this->_show_generated && ! empty( $done ) ) {
      echo '<div class="updated"><p>';
      echo __( 'Generated files:', 'ceceppaml' ) . "<br /><blockquote>";
      echo join( "<br />", $done );
      echo '</blockquote></div>';
    }

    $this->_done = $outfiles;
  }

  function convert_po( $filename ) {
    try {
      //Tadaaaaa, file generato... genero il .mo
      phpmo_convert( $filename );
    } catch (Exception $e) {
      return $e->getMessage();
    }

   return "";
  }

  /*
   * Return array of generated files
   */
  function generated() {
    return ( isset( $this->_done ) ) ? $this->_done : array();
  }

  function errors() {
    return ( isset( $this->_errors ) ) ? 1 : 0;
  }

  function get_msgstr( $lang, $msgid ) {
    foreach( $this->_strings[ $lang ] as $entry ) {
      if( $entry[ 'msgid' ] == $msgid ) return array( "msg" => $entry[ 'msgstr' ],
                                                      "fuzzy" => @$entry[ 'fuzzy' ],
                                                    );
    }

    return array(
                 'msg' => "",
                 'fuzzy' => 0,
                );
  }
}

function nl2br2( $string ) {
  $string = str_replace( array( "\r\n", "\r", "\n" ), "<br />", $string );

  return $string;
}

function br2nl( $string )
{
  return preg_replace('/\<br(\s*)?\/?\>/i', "\r\n", $string );
}

wp_enqueue_script( 'ceceppaml-admin-translations', CML_PLUGIN_JS_URL . 'admin.ttheme.js' );

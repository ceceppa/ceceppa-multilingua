<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

//Load .po file
//require_once( CML_PLUGIN_PATH . "gettext/gettext.inc" );
require_once( CML_PLUGIN_PATH . 'Pgettext/Pgettext.php' );

/*
 * CeceppaMultilingua parser class
 *
 * This class is used for "scan" theme or plugin folder and search for translable strings ( __, _e, esc_html__, etc )
 * in desired directory
 * 
 */
Class CMLParser {
  protected $_domains = null;
  protected $_name = null;
  protected $_default_lang = null;
  protected $_src_path = null;
  protected $_dest_path = null;
  protected $_mopo_files = null;
  protected $_show_generated = null;
  protected $_form_name = "theme";

  /*
   * Parse php files in source path for search translable strings
   *
   * @param $name - title of theme/plugin
   * @param $languages - array containing languages id in which translate current theme
   * @param $src_path - folder to search
   * @param $dest_path - folder where write generated .mo/.po file
   * @param $show_generated - show generated files
   */
  function __construct( $name, $src_path, $dest_path, $domain = "", $show_generated = true, $form_name = "theme" ) {
    $this->_name = $name;
    $this->_translate_in = get_option( 'cml_translate_' . $name . "_in", array() );
    $this->_src_path = trailingslashit( $src_path );
    $this->_dest_path = trailingslashit( $dest_path );
    $this->_show_generated = $show_generated;
    $this->_domain = $domain;
    $this->_form_name = $form_name;

    if( isset( $_POST[ 'generate' ] ) ) {
      $this->generate_po_file();

      return;
    }

    //Look for existings .po files
    $this->get_po_files();

    //Parse PHP files
    $this->do_parser();
    if( empty( $this->_domains ) ) return $this->error( sprintf( __( 'No translable strings founds in %s', 'ceceppaml' ), $src_path ) );

    $this->print_table();
  }
  
  /*
   * Parse all php files for looking l10n functions
   */
  private function do_parser() {
    $files = $this->get_all_files_from( $this->_src_path, "php" );
    if( FALSE === $files ) {
      $failed = sprintf( __( "Failed to open %s path", 'ceceppaml' ), $this->_src_path );
      $this->error( $failed );
    }

    foreach( $files as $filename ) {
      $content = file_get_contents( $filename );
      
      //Scan for all l10n functions
      // preg_match_all ( '/(_e|__|_n|_x|_ex|_nx|esc_attr__|esc_attr_e|esc_attr_x|esc_html__|esc_html_e|esc_html_x)\((.*?)\)/', $content, $matches );
      preg_match_all ( '/(_e|__|_n|_x|_ex|_nx|esc_attr__|esc_attr_e|esc_attr_x|esc_html__|esc_html_e|esc_html_x)(\(.*\))/', $content, $matches );

      //Preg_match return 'string', 'textdomain'
      $m = end( $matches );
      $domain = 0;
      foreach( $m as $line ) {
        $this->extract_strings( $line );
      }
    }
  }

  private function extract_strings( $line ) {
    if( preg_match_all ( '/(_e|__|_n|_x|_ex|_nx|esc_attr__|esc_attr_e|esc_attr_x|esc_html__|esc_html_e|esc_html_x)(\(.*\))/', $line, $out ) ) {
      $end = end( $out );
      if( isset( $end[ 0 ] ) ) {
        $this->extract_strings( $end[0] );
      }
    }

    /*
     * To grab only text in __( ) I count brackets occourrencies
     * each ( increase $brackets, while each ) decrease.
     * When reach 0 means that extra text isn't rubbish
     */
    $brackets = 0;
    for( $i = 0; $i < strlen( $line ); $i++ ) {
      if( "(" == $line[ $i ] ) $brackets++;
      if( ")" == $line[ $i ] ) {
        $brackets--;

        //Remove extra text after last function bracket
        if( 0 == $brackets ) {
          $line = substr( $line, 1, $i - 1 );
        }
      }
    }

    //Divide "text" from "domain"
    preg_match_all( '/^[\'\"](.*)[\'\"][,](.*)[\'\"]$/', trim( $line ), $string );

    if( count( $string ) > 1 ) {
      $text = end( $string[ 1 ] );
      $domain = end( $string[ 2 ] );
      $domain = "x";

      //Add string to translable ones
      if( ! empty( $text ) && ! @in_array( $text, $this->_domains[ $domain ] ) ) {
        $this->_domains[ $domain ][] = $text;
      }
    }
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

  private function get_po_files() {
    $files = $this->get_all_files_from( $this->_src_path, 'po' );
    $this->_mopo_files = array_merge( $files, $this->get_all_files_from( $this->_src_path, 'mo' ) );
  }

  private function get_all_files_from( $dir, $ext, $files = array() ) { 
    if( !( $res = opendir( $dir ) ) ) return false;

    $dir = trailingslashit( $dir );
    while( ( $file = readdir ( $res ) ) == TRUE )
      if( $file != "." && $file != ".." )
        if( is_dir ( "${dir}${file}" ) ) {
          if( "." == $file[0] ) continue;

          $path = "${dir}${file}";
          $files = $this->get_all_files_from( $path, $ext, $files );
        } else {
          $info = pathinfo( "$dir\/$file" );
  
          if( isset( $info[ 'extension' ] ) && strtolower( $info['extension'] ) == strtolower( $ext ) ) { 
            $files[] = "${dir}${file}";
          }
        }//endif;
        
    closedir($res); 
  
    return $files; 
  }

  private function print_table() {
    //All languages
    $langs = array();
    foreach( $this->_translate_in as $id ) {
      $langs[$id] = CMLLanguage::get_by_id( $id );
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

    $keys = array_filter( array_keys( $this->_domains ) );

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
      <?php if( count( $in ) > 1 ) : ?>
      <a class="nav-tab" href="javascript:showStrings( 2, 'incomplete' )"><?php _e( 'Incomplete', 'ceceppaml' ) ?><span></span></a>
      <?php endif; ?>
      <a class="nav-tab" href="javascript:showStrings( 3, 'translated' )"><?php _e( 'Translated', 'ceceppaml' ) ?><span></span></a>
      <p class="submit">
        <span class="spinner"></span>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'ceceppaml' ) ?>"  />
      </p>
    </h2>
    <input type="hidden" name="textdomain" value="<?php echo $keys[ 0 ] ?>" />

    <?php
      if( empty( $this->_translate_in ) || ! is_array( $this->_translate_in ) ) {
        _e( 'You have to choose in which language you want to translate: ', 'ceceppaml' );
        echo $this->_name;
        echo '<input type="hidden" name="nolang" value="1" />';
        return;
      }
    ?>
    <div class="search">
      <label  style="float:right">
        <?php _e( 'Search', 'ceceppaml' ) ?>:
        <input type="text" name="s" class="s" value=""/>
      </label>
    </div> 
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

    //All domains
    $keys = array_filter( array_keys( $this->_domains ) );

    $domain = empty( $this->_domain ) ? "" : "$this->_domain-";

    //Search translation for each language
    foreach( $keys as $d ) {
      $strings = array_unique( $this->_domains[ $d ] );
      $this->_domains[ $d ] = $strings;

      //Cycles for each language
      foreach( $langs as $lang ) {
        // gettext setup

        //try to get translation from .po and .mo files
        $po = null;
        $mo = null;

        //Po
        try {
          $po_filename = $this->_dest_path . $domain . $lang->cml_locale . ".po";
          
          if( file_exists( $po_filename ) )
            $po = Po::fromFile( $po_filename );
        } catch( Exception $e ) {
          // echo "<div class=\"error\"><p>";
          // echo $this->_dest_path . $domain . $lang->cml_locale . ".po";
          // echo "<pre>$e</pre>";
          // echo "</p></div>";
        }

        //Mo
        try {
          $mo_filename = $this->_dest_path . $domain . $lang->cml_locale . ".mo";
          
          if( file_exists( $mo_filename ) ) 
            $mo = CMo::fromFile( $mo_filename );
        } catch( Exception $e ) {
          // echo "<div class=\"error\"><p>";
          // echo $this->_dest_path . $domain . $lang->cml_locale . ".mo";
          // echo "<pre>$e</pre>";
          // echo "</p></div>";
        }

        //Search the translation for each language
        foreach( $strings as $string ) {
          $ret = "";

          //try to get from $mo
          $s = stripslashes( $string );
          if( isset( $mo ) ) {
            $ret = $mo->search( $s );
          }

          if( ! isset( $mo ) && isset( $po ) ) {
            $ret = $po->search( $s );
          }

          if( empty( $ret ) ) {
            $ret = __( $string );  //Cerco anche tra le traduzioni di wordpress 

            if( strcasecmp( stripslashes( $ret ), stripslashes( $string ) ) == 0 ) 
              $ret = "";
          }

          $done = ! empty( $ret );

          //Translation cannot be empty
          // if( empty( $ret ) ) $ret = $string;
          // if( ! $done ) $ret = "";

          $trans[ $lang->id ][] = array( "string" => stripslashes( $ret ), "done" => $done );
        } //$strings as $string
  
      } //$langs as $lang
    } //$keys as $d

    $i = 0;
    $total = count( $langs );
  
    foreach( $keys as $d ) {
  
      $strings = $this->_domains[ $d ];
      foreach( $strings as $s ) {
        $originals[] = $s;
  
        $alternate = ( empty( $alternate ) ) ? "alternate" : "";
  
        $td = "<td class=\"item\">" . stripcslashes( $s ) . "</td>";
        
        $translated = 0;
        foreach( $langs as $lang ) {
          $done = $trans[ $lang->id ][ $i ][ 'done' ] == 1;
          $translated += intval( $done );

        //   $td .= "<td>";
        //   $not = ( $done ) ? "" : "not-available";
        //   $msg = !empty( $not ) ? __( 'Translate', 'ceceppaml' ) : __( 'Translated', 'ceceppaml' );
        //   $td .= '<img src="' . CMLLanguage::get_flag_src( $lang->id ) . '" class="available-lang ' . $not . ' tipsy-e" title="' . $msg . '" />';
        //   $td .= "</td>";
        }
  
        if( $translated == 0 )
          $class = "to-translate";
        else if( $translated == $total )
          $class = "translated";
        else
          $class = "to-translate string-incomplete";
      
        echo "<tr class=\"$alternate row-domain string-$class\">";
        echo $td;
        // echo "</tr>";
  
        // echo "<tr class=\"$alternate row-details row-hidden \">";
        echo "<td colspan=\"" . ( count( $langs ) + 1 ) ."\">";
      
        foreach( $langs as $lang ) {
          $done = $trans[ $lang->id ][ $i ][ 'done' ] == 1;
      
          echo "<div class=\"ceceppaml-trans-fields\">";
          echo '<img src="' . CMLLanguage::get_flag_src( $lang->id ) . '" class="available-lang" />';
          echo "&nbsp;<textarea name=\"string[" . $lang->id . "][]\">" . esc_html( br2nl( stripslashes( $trans[ $lang->id ][ $i ][ 'string' ] ) ) ) . "</textarea>";
          
          $done = ( $done )  ? __( 'Translation complete', 'ceceppaml' ) : __( 'Translation not complete', 'ceceppaml' );
          echo "</div>";
        }

        echo "</td>";
        echo "</tr>";
  
        $i++;
      } //$strings as $s 
  
    } // $keys as $d 
  
  //Memorizzo le stringhe originali in un file "temporaneo", così evito la conversione degli elementi html ( &rsquo;, etc... )
  $outfilename = $this->_dest_path . "/tmp.pot";
  $out = @file_put_contents( $outfilename, implode( "\n", $originals ) );
  if( ! $out ) {
    $this->error( sprintf( __( 'Failed to open stream %s: Permission denied', 'ceceppaml' ), $outfilename ) );
  }
  
echo <<< EOT
    </tbody>
  </table>
EOT;
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
      return $this->error( printf( __( 'Failed to create folder: %s', 'ceceppaml' ), $cml_theme_locale_path ) );
    }

    $domain = trim( $_POST[ 'textdomain' ] );
 
    //Retrive original strings from temporary file "tmp.pot"   
    $originals = explode( "\n", file_get_contents( $this->_dest_path . "/tmp.pot" ) );

    //.po header
    $header = file_get_contents( CML_PLUGIN_ADMIN_PATH . "header.po" );

    //Escludo la lingua principale del tema 
    $langs = $this->_translate_in;

    $done = array(); //File completati
    $outfiles = array();
    
    $domain = empty( $this->_domain ) ? "" : "$this->_domain-";
    foreach( $langs as $id ) {
      $lang = CMLLanguage::get_by_id( $id );
      $filename = "$this->_dest_path/$domain{$lang->cml_locale}.po";
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
      if( empty( $strings ) ) continue;

      for( $i = 0; $i <= count( $originals ); $i++ ) {
        if( ! isset( $originals[ $i ] ) ) continue;
        if( empty( $strings[ $i ] ) ) continue;
    
        $o = str_replace( "\"", '\"', stripslashes( $originals[$i] ) );
        $s = str_replace( "\"", '\"', stripslashes( $strings[$i] ) );
        $o = 'msgid "' . $o . '"' . PHP_EOL;
        $s = 'msgstr "' . nl2br2( $s ) . '"' . PHP_EOL . PHP_EOL;
    
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
    //require_once( CML_PLUGIN_PATH . 'Pgettext/Pgettext.php' );

    try {
      //Tadaaaaa, file generato... genero il .mo
      Pgettext::msgfmt( $filename );
    } catch (Exception $e) {
      return $e->getMessage();
    }
  
   return "";
  }
  
  /*
   * Return array of generated files
   */
  function generated() {
    return $this->_done;
  }
  
  function errors() {
    return ( isset( $this->_errors ) ) ? 1 : 0;
  }
}

function nl2br2($string) { 
  $string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string); 
  return $string; 
}

function br2nl( $string )
{
  return preg_replace('/\<br(\s*)?\/?\>/i', "\r\n", $string );
}

wp_enqueue_script( 'ceceppaml-admin-translations', CML_PLUGIN_JS_URL . 'admin.ttheme.js' );

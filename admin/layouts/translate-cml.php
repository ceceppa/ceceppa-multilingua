<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

//CML parser, used for translate theme and plugin
require_once ( CML_PLUGIN_ADMIN_PATH . 'po-parser.php' );

function cml_translate_cml() {
  //get generated files
  if( isset( $_GET[ 'generated' ] ) ) {
    $generated = json_decode( $_GET[ 'generated' ] );

    if( empty( $generated ) ) {
      echo '<div class="updated"><p>';
      echo __( 'Something goes wrong, cannot generate .mo files :(', 'ceceppaml' );
      echo '<br />';
      printf( __( 'Ensure that folder <b>%s</b> is writable', 'ceceppaml' ), CML_PLUGIN_LANGUAGES_PATH );
      echo '</p></div>';
    } else {
      echo '<div class="updated"><p>';
       _e( 'Thanks for translated Ceceppa Multilingua in your language.', 'ceceppaml' ) . "<br /><br />";
       _e( 'If translation is complete and you want to share it with us, download it:', 'ceceppaml' );
  
      echo '<ul class="cml-ul-inline">';
      foreach( $generated as $gen ) {
        $lang = CMLLanguage::get_by_id( $gen );

        if( file_exists( CML_PLUGIN_LANGUAGES_PATH . "ceceppaml-$lang->cml_locale.mo" ) ) {
          echo "<li><a href=\"" . CML_PLUGIN_URL . "langs/ceceppaml-$lang->cml_locale.mo\" >$lang->cml_language</a></li>";
        }
    
      } //endforeach;

      echo "</ul>";
      printf( __( 'and send it <%s>to me</a>, add also your name or nickname', 'ceceppaml' ), 'a href="mailto:cmlcontribute@alessandrosenese.eu?subject=New translation"' );
      echo '</p></div>';
    }

  } else {
    $msg = __( "If you like the plugin and you want translate it in your language, you can do from this page.", "ceceppaml" ) . "<br />";
    $msg .= __( "After clicked on \"Save Changes\" button, follow the instructions and send me the translation, thanks", "ceceppaml" ) . " :)";
echo <<< EOT
    <div class="updated">
      <p>
        $msg
      </p>
    </div>
EOT;
  }
  //Translate Ceceppa Multilingua :)
  $parser = new CMLParser( "Ceceppa Multilingua", CML_PLUGIN_PATH, CML_PLUGIN_LANGUAGES_PATH, "ceceppaml", false, "cml-plugin" );
}

add_meta_box( 'cml-translate-cml', __( 'Ceceppa Multilingua in your language', 'ceceppaml' ), 'cml_translate_cml', 'cml_box_languages' );
?>
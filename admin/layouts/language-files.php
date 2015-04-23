<?php
/*
 * In questa funzione mi occupo di recuperare le traduzioni, ovvero i file .mo dall'svn di wordpress
 * per la precisione da: http://svn.automattic.com/wordpress-i18n/
 */
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

if( isset( $_GET[ 'download-lang' ] ) ) {
  global $wpdb;

  $id = intval( $_GET[ 'download-lang' ] );

  $lang = CMLLanguage::get_by_id( $id );
  if( ! empty( $lang ) ) {
    cml_download_mo_file( $lang->cml_locale );
  }
}

function cml_admin_box_languages() {
  $langs = CMLLanguage::get_all();

?>
  <div class="updated">
    <p>
      <?php _e( 'When you add a language the plugin will try to download corresponding WordPress language files. If for, some reason, it fails you can try to download them at: ', 'ceceppaml' ); ?>
      <a href="http://svn.automattic.com/wordpress-i18n/" target="_blank">
        http://svn.automattic.com/wordpress-i18n/.
      </a>
      <br />
      <?php _e( "You then would have to upload these files to the '/wp-content/languages/' directory", 'ceceppaml' ); ?>
      <br />
      <br />
      <?php _e( 'If your language is not available on previous link you have to install it manually. For more information', 'ceceppaml' ) ?>
      <a href="http://svn.automattic.com/wordpress-i18n/" target="_blank">
        http://codex.wordpress.org/WordPress_in_Your_Language
      </a>
    </p>
  </div>
  <table class="wp-list-table widefat mo-table">
    <thead>
      <tr>
        <th>Language</th>
        <th>.mo</th>
        <th><img src="<?php echo CML_PLUGIN_IMAGES_URL ?>icon_wplang.png" height="12"></th>
      </tr>
    </thead>
    <tbody id="the-list">
<?php
  foreach( $langs as $lang ) {
    $link = "#";

    if( substr( $lang->cml_locale, 0, 2 ) != "en" ) {
      $alternate = @empty( $alternate ) ? "alternate" : "";
      echo "<tr class=\"${alternate}\">";

      echo "<td>" . CMLLanguage::get_flag_img( $lang->id ) . "</td>";
      echo "<td>$lang->cml_locale</td>";

      //Esiste il file della lingua?
      $exists = cml_download_mo_file_check( $lang->cml_locale );
      $link = "#";
      if( ! $exists ) {
        $link = esc_url( add_query_arg( array( "download-lang" => $lang->id ) ) );
      }

      $title = ( $exists ) ? "&#x2713;" : "&#x25BC;";

echo <<< EOT
  <td>
    <a href="$link">
      $title
    </a>
  </td>
EOT;

      echo "</tr>";
    }
  }

echo <<<EOT
  </tbody>
  </table>
EOT;
}

add_meta_box( 'cml-box-languages', __( 'Languages', 'ceceppaml' ), 'cml_admin_box_languages', 'cml_box_languages' );
?>

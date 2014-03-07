<?php
//Non posso richiamare lo script direttamente dal browser :)
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

global $wpdb, $wpCeceppaML;

if( isset( $_POST[ 'add' ] ) && wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) )
  cml_admin_update_my_translations();
  
?>

    <form class="ceceppa-form-translations" name="wrap" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_GET['page'] ?>">
    <input type="hidden" name="add" value="1" />
    <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
<div class="updated">
    <p>
      <?php _e('You can use this translation with shortcode "cml_translate".', 'ceceppaml') ?>
      <a href="?page=ceceppaml-shortcode-page&tab=0#strings"><?php _e( 'Click here to see the shortcode page', 'ceceppaml' ); ?></a>
      <br />
    </p>
</div>
    <input type="hidden" name="form" value="1" />
    <table class="wp-list-table widefat wp-ceceppaml">
      <thead>
      <tr>
        <th>
          <?php _e( 'Group', 'ceceppaml' ); ?>
        </th>
	  <th>
      <?php
        echo CMLLanguage::get_flag_img( CMLLanguage::get_default_id() );
        echo "&nbsp;";
        echo CMLLanguage::get_default()->cml_language ?>
      </th>
<?php
	  $langs = cml_get_languages( false, true );
	  foreach( $langs as $lang ) {
        echo "<th>";
        echo CMLLanguage::get_flag_img( $lang->id );
        echo "&nbsp; " . $lang->cml_language;
        echo "</th>";
        $lid[] = $lang->id;
	  }
?>
      <th style="width: 40px">
      	<img src="<?php ECHO CML_PLUGIN_URL . "images/remove.png" ?>" class="tipsy-me" title="<?php _e( 'Remove', 'ceceppaml' ); ?>" />
      </th>
      </tr>
      </thead>
<?php
  $types = array(
                 "S" => "My",
                 "N" => "Notice",
                );
  
  //3rd parth
  $types = apply_filters( 'cml_my_translations', $types );
  $keys = array_keys( $types );

  $query = "SELECT min(id) as id, UNHEX(cml_text) as cml_text, cml_type FROM " . CECEPPA_ML_TRANSLATIONS .
                                " WHERE cml_type in ( '" . join( "', '", $keys ) . "' ) GROUP BY cml_text ORDER BY cml_type ";

  $results = $wpdb->get_results( $query );

  $c = 0;
  $size = 100 / (count($langs) + 1);
  foreach( $results as $result ) {
      $i = 0;

      $title = html_entity_decode( $result->cml_text );
      //Non posso utilizzare htmlentities perché sennò su un sito in lingua russa mi ritrovo tutti simboli strani :'(
      $title = str_replace("\"", "&quot;", stripslashes( $title ) );

      $alternate = @empty($alternate) ? "alternate" : "";
      echo "<tr class=\"${alternate}\">";

      echo '<td class="cml-valign-bottom">';
      echo "\t<input type=\"hidden\" name=\"id[]\" value=\"$result->id\" />\n";
      echo "\t<input type=\"hidden\" name=\"types[]\" value=\"$result->cml_type\" />\n";
      echo "\t<input type=\"hidden\" name=\"string[]\" value=\"$title\" />\n";
      echo $types[ $result->cml_type ];
      echo "</td>\n";
      echo "<td style=\"height:2.5em;width: $size%\">\n";

      $t = $title;
      $style = "";
      if( $result->cml_type == "N" ) {
        echo "<span>";
        if( $result->cml_type == "N" ) {
          echo ( $title == "_notice_post" ) ? __( "Post notice:", "ceceppaml" ) :
                                                  __( "Page notice:", "ceceppaml" );
        }
        echo "</span>";

        $default = CMLLanguage::get_default_id();
        $v = CMLTranslations::get( CMLLanguage::get_default_id(),
                                   $title, $result->cml_type, true, true );

        echo "<input type=\"hidden\" name=\"lang_id[$c][0]\" value=\"$default\" />\n";
        echo "<input type=\"text\" name=\"value[$c][0]\" value=\"$v\"  style=\"width: 100%\" /></td>\n";
        
        $i++;
      } else {
        if( "_" != $t[0] ) {
          echo stripslashes( $t );
        } else {
          echo stripslashes( substr( $t, 1 ) );
        }
      }

      echo "</td>";

      foreach( $langs as $lang ) {
        $d = CMLTranslations::get( $lang->id, $title, $result->cml_type, true, true );
        $d = str_replace( "\"", "&quot;", stripslashes( $d ) );
        echo "<td>\n";
        
        if( $result->cml_type == "N" ) {
          echo "<br />";
        }

        echo "<input type=\"hidden\" name=\"lang_id[$c][$i]\" value=\"$lang->id\" />\n";
        echo "<input type=\"text\" name=\"value[$c][$i]\" value=\"$d\"  style=\"width: 100%\" /></td>\n";
    
        $i++;
      } //$langs as $lang;
?>
    <td>
      <?php if( $result->cml_type == 'S' ) :  ?>
      <input type="checkbox" name="remove[<?php echo $result->id ?>]" value="1">
      <?php endif; ?>
    </td>
<?php

    echo "</tr>";
    
    $c++;
  } //endforeach;
?>
     </tbody>
    </table>
    <div style="text-align:right">
      <p class="submit" style="float: right">
	<input type="button" class="button button-secondaty" name="add" value="<?php _e('Add', 'ceceppaml') ?>" onclick="addRow(<?php echo count($langs) . ", '" . join(",", $lid) ?>')" />
	<?php submit_button( __('Update', 'ceceppaml'), "button-primary", "action", false, 'class="button button-primary"' ); ?>
      </p>
  </div>
</form>

<?php
  function cml_admin_update_my_translations() {
    global $wpdb;

    CMLTranslations::delete( "N" );
    CMLTranslations::delete( "S" );
    
    $ids = $_POST['id'];
    $delete = ( array_key_exists( 'remove', $_POST ) ) ? $_POST['remove'] : array();

    for( $i = 0; $i < count($_POST['string']); $i++ ) {
      $string = $_POST[ 'string' ][ $i ];
      $type = $_POST[ 'types' ][ $i ];

      $id = $ids[ $i ];
      if( ! empty( $delete ) && ( @isset( $delete[ $id ] ) || @ $delete[ $id ] == 1 ) ) continue;

      for( $j = 0; $j < count( $_POST[ 'value' ][ $i ] ); $j++ ) {
        $value = $_POST['value'][$i][$j];
        $lang_id = $_POST['lang_id'][$i][$j];

        if( ! empty( $string ) ) {
          CMLTranslations::set( $lang_id, $string, $value, $type );
        } //endif;
      } //endfor;

    } //endfor;
    
    //generate .po
    cml_generate_mo_from_translations( "S", true );
  }
?>
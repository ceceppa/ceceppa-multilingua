<?php
global $wpCeceppaML;

if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

$GLOBALS[ '_cml_widget_titles' ] = array();

/*
 * I use this function for grab all widget titles
 */
function cml_grab_widget_title( $title ) {
  echo $title;
  $GLOBALS[ '_cml_widget_titles' ][] = $title;
}

//grab widget titles
add_filter('widget_title', 'cml_grab_widget_title', 0, 1 );

?>
<?php
function cml_widgets_title_table( $wtitles ) {
  global $wpdb;

  //I don't need default language :)
  $langs = CMLLanguage::get_no_default();
?>
  <div class="updated">
    <p>
      <?php _e('If you want to customize widget titles you have to assign a title for each of them in "Appearance" -> "Widgets"', 'ceceppaml'); ?>
    </p>
  </div>

  <form class="ceceppa-form" name="wrap" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_GET[ 'page' ] ?>">
  <input type="hidden" name="action" value="add" />
  <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />
  <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
  <table class="wp-list-table widefat wp-ceceppaml">
    <thead>
      <tr>
	  <th><?php _e( 'Title', 'ceceppaml' ) ?></th>
<?php
	  foreach($langs as $lang) {
        $img = CMLLanguage::get_flag_img( $lang->id );
        echo "<th>$img</th>";
	  }
?>
      </tr>
<?php 
  foreach($wtitles as $title) :
    if(!empty($title)) :
      $title = html_entity_decode( $title );
	//Non posso utilizzare htmlentities perché sennò su un sito in lingua russa mi ritrovo tutti simboli strani :'(
      $title = str_replace("\"", "&quot;", $title);
      $alternate = @empty($alternate) ? "alternate" : "";
      echo "<tr class=\"${alternate}\">";

      echo "<td style=\"height:2.5em\">\n";
      echo "\t<input type=\"hidden\" name=\"strings[]\" value=\"$title\" />\n";
      echo $title . "</td>";
      $i = 0;

      foreach($langs as $lang) {
        $d = CMLTranslations::get( $lang->id, $title, "W", true, true );
        
        if( empty( $d ) )
          $d = CMLTranslations::gettext( $lang->id, $title, "W" ); // ( $title, $lang->id, 'W', true, true );

        $d = str_replace("\"", "&quot;", $d);
        echo "<td>\n";
        echo "<input type=\"text\" name=\"lang_" . $lang->id . "[]\" value=\"$d\"  style=\"width: 100%\" /></td>\n";
    
        $i++;
      } //endforeach;

      echo "</tr>";
    endif;
  endforeach;
?>
     </tbody>
    </table>
    <div style="text-align:right">
      <p class="submit" style="float: right">
	<?php submit_button( __('Reset titles', 'ceceppaml'), "button-secondary", "delete", false ) ?>&nbsp;&nbsp;
	<?php submit_button( __('Update', 'ceceppaml'), "button-primary", "action", false, 'class="button button-primary"' ); ?>
      </p>
    </div>
  </form>
<?php
}


//Grab output
  ob_start();
    
  //Call sidebars and let the function "cml_grab_widget_title" to grab titles :)
  global $wp_registered_sidebars;

  if ( ! function_exists( 'dynamic_sidebar' ) ) { //|| !dynamic_sidebar("Sidebar") ) {
    //Nothing to do...
    return;
  }

//Parse all registered sidebars
  if( is_array( $wp_registered_sidebars ) ) {
    $keys = array_keys( $wp_registered_sidebars );

    foreach( $keys as $key ) {
      dynamic_sidebar( $key );
    }
  }
  
//Clean the output, I don't need it :)
  ob_end_clean();

//I grabbed titles, let show the table :)
cml_widgets_title_table( $GLOBALS[ '_cml_widget_titles' ] );
?>
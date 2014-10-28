<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

function cml_utils_create_checkbox( $label, $id, $name, $option, $checked_value, $selected = null ) {
  if( null != $option ) {
    $_cml_settings = & $GLOBALS[ '_cml_settings' ];
    $selected = $_cml_settings[ $option ];
  } 

  $checked = checked( $selected, $checked_value, false );

return <<< EOT
  <div class="cml-checkbox">
    <input type="checkbox" id="$id" name="$name" value="1" $checked />
    <label for="$id"><span>||</span></label>
  </div>
  <label for="$id">$label</label>
EOT;
}

/*
 * retore wp pointers
 *
 * This function is called by Uninstall and "Restore help" ( advanced mode )
 *
 */
function _cml_restore_wp_pointers() {
  $missed = explode( ",", get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
  $misseds = $missed;

  foreach( $missed as $key => $value ) {
    if( FALSE !== strpos( $value, "cml_" ) )
      unset( $misseds[ $key ] );
  }
  
  update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', implode( ",", $misseds ) );
}

function _cml_no_tables_found() {
echo <<< EOT
    <div class="error">
        <h3>Ceceppa Multilingua</h3>
      <p>
EOT;
        echo '<strong>' . __( 'No tables found!', 'ceceppaml' ) . '</strong> <br />';
        _e( "If you have uninstalled the plugin, you can remove the plugin now, or deactivate and activete it again to rebuild tables.", "ceceppaml" );
        echo '<br /><br />';
        printf( __( "If you haven't uninstalled the plugin, something goes wrong, please contact me <%s>here", "ceceppaml" ),
               'a href="http://wordpress.org/support/plugin/ceceppa-multilingua" target="_blank"' );
        
echo <<< EOT
        </a>
      </p>
    </div>
EOT;
}


function _cml_wp_error_div( $title, $msg ) {

echo <<< ERROR
    <div class="error cml-notice">
        <p>
            <span class="title">CML: $title</span>
            $msg
        </p>
    </div>
ERROR;
}

function _cml_wp_updated_div( $title, $msg ) {

echo <<< UPDATED
    <div class="updated cml-notice">
        <p>
            <span class="title">CML: $title</span>
            $msg
        </p>
    </div>
UPDATED;
}

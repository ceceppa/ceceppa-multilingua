<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied!" );

add_filter( 'widget_update_callback', 'cml_update_widget_conditions', 10, 3 );
add_action( 'in_widget_form', 'cml_admin_in_widget_form', 10, 3 );
add_action( 'wp_ajax_widget_conditions_options', 'cml_widget_conditions_options' );
//add_action( 'sidebar_admin_setup', 'cml_widget_admin_setup' );

/*
 * Update visibility conditions, called when user click on "Save" button
 */
function cml_update_widget_conditions( $instance, $new_instance, $old_instance ) {
  global $wpdb;

  $conditions = array();
  $conditions[ 'action' ] = @$_POST['cml-conditions']['action'];
  $conditions[ 'langs' ] = @$_POST['cml-conditions']['langs'];
  $conditions[ 'filter' ] = intval( @$_POST['cml-conditions']['filter'] );

  //Remove old translations
  if( isset( $old_instance[ 'cml-conditions' ][ 'titles' ] ) ) {
    foreach( $old_instance[ 'cml-conditions' ][ 'titles' ] as $id ) {
      $wpdb->delete( CECEPPA_ML_TRANSLATIONS,
                    array( "id" => $id ),
                    array( "%d" ) );
    }
  }

  //Store widget title translations
  $name = str_replace( "widget_", "widget-", $_POST[ 'cml-widget-name' ] );
  $key = @end( $_POST[ $name ] );
  $original = @$key[ 'title' ];

  if( ! empty( $original ) ) {
    $titles = $_POST[ 'cml-widget-title' ];

    $out = print_r( $titles, true );
    foreach( $titles as $key => $title ) {
      $ids[] = CMLTranslations::set( $key, $original, $title, "W" );
    }
    
    //Store the id of translations
    $conditions[ 'titles'] = $ids;
  }

  $instance[ 'cml-conditions' ] = $conditions;
  
  cml_generate_mo_from_translations( "_X_" );

  return $instance;
}

/*
 * Add visibility conditions and fields for translate widget title
 */
function cml_admin_in_widget_form( $widget, $return, $instance ) {
  $conditions = array();
  
  if ( isset( $instance['cml-conditions'] ) )
      $conditions = $instance['cml-conditions'];

  if( ! isset( $conditions[ 'filter' ] ) )
    $conditions[ 'filter' ] = false;

  if ( ! isset( $conditions[ 'action' ] ) )
      $conditions[ 'action' ] = 'show';

  if ( empty( $conditions['rules'] ) )
      $conditions['rules'][] = array( 'major' => '', 'minor' => '' );

  //cml_widget_add_available_flags( $conditions );
?>
  <div class="cml-widget-titles">
    <!-- avoid that ajax call change language inside widget -->
    <input type="hidden" name="lang" value="<?php CMLLanguage::get_current_id() ?>" />
    <!-- Required for catch widget title on update -->
    <p>
      <?php _e( 'Translate title in:', 'ceceppaml' ) ?>
    </p>
    <p class="warning">
      <strong><?php _e( "Widget title cannot be empty, otherwise following translations will not be stored.", 'ceceppaml' ) ?></strong>
    </p>
    <input type="hidden" name="cml-widget-name" value="<?php echo $widget->option_name ?>" />
    <div class="fields">
    <?php
      foreach( CMLLanguage::get_no_default() as $lang ) {
        if( isset( $instance[ 'title' ] ) )
          $title = CMLTranslations::get( $lang->id, $instance[ 'title' ], "W", false, true );
        else
          $title = "";

        $flag = CMLLanguage::get_flag_src( $lang->id, CMLLanguage::FLAG_TINY );
echo <<< EOT
      <div class="cml-widget-title">
        <img class="tipsy-e" src="$flag" title="Title in: &quot;{$lang->cml_language}&quot;" />
        <input class="widefat" id="cml-widget-title" name="cml-widget-title[{$lang->id}]" type="text" value="$title">
      </div>
EOT;
      }
     ?>
    </div>
  </div>
  <div class="cml-widget-conditional">
    <div class="cml-filter-widget">
      <div class="cml-checkbox cml-js-checkbox">
        <input type="checkbox" id="cml-lang-$id" name="cml-conditions[filter]" value="1" <?php checked( $conditions[ 'filter' ] ) ?>/>
        <label for="cml-lang-$id"><span>| |</span></label>
        <label class="cml-checkbox-label" for="cml-lang-$id"><strong><?php _e( 'Filter widget', 'ceceppaml' ) ?></strong></label>
      </div>
    </div>
    <div class="cml-widget-conditional-inner <?php echo ( $conditions[ 'filter' ] ) ? "" : "cml-widget-conditional-hide" ?>">
      <div class="cml-condition-top">
        <select name="cml-conditions[action]">
          <option value="show" <?php selected( $conditions[ 'action' ], 'show' ) ?>"><?php _e( 'Show', 'ceceppaml' ) ?></option>
          <option value="hide" <?php selected( $conditions[ 'action' ], 'hide' ) ?>"><?php _e( 'Hide', 'ceceppaml' ) ?></option>
        </select>
        <?php _e( 'If current language is: ', 'ceceppaml' ) ?>
      </div><!-- .condition-top -->
      <div class="cml-conditions">
        <br />
        <?php
          foreach( CMLLanguage::get_all() as $lang ) {
            $id = $lang->id;
            
            $checked = checked( isset( $conditions[ 'langs' ][ $lang->id ] ), true, false );
echo <<< EOT
          <div class="cml-checkbox cml-js-checkbox">
            <input type="checkbox" id="cml-lang-$id" name="cml-conditions[langs][$id]" value="$id" $checked />
            <label for="cml-lang-$id"><span>| |</span></label>
            <label class="cml-checkbox-label" for="cml-lang-$id">$lang->cml_language</label>
          </div>
EOT;
          }
        ?>
      </div><!-- .conditions -->
    </div><!-- .widget-conditional-inner -->
  </div><!-- .widget-conditional -->
<?php
}


/*
 * Show in wich language is available current widget
 */
function cml_widget_add_available_flags( $conditions ) {
  //Filter enabled
  $langs = CMLLanguage::get_all();

  if( $conditions[ 'filter' ] == 1 ) {
    $action = strtolower( $conditions[ 'action' ] );

    foreach( $conditions[ 'langs' ] as $lang ) {
      if( $action == "show" )
        $langs[] = CMLLanguage::get_by_id( $lang );
      else
        unset( $langs[ $lang ] );
    }
  }
  
  echo '<div class="cml-available-langs">';
  foreach( $langs as $lang ) {
    echo CMLLanguage::get_flag_img( $lang->id );
  }
  echo '</div>';
}
?>
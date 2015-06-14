<?php
//Non posso richiamare lo script direttamente dal browser :)
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

global $wpdb;

require_once( CML_PLUGIN_LAYOUTS_PATH . "class-mytranslations.php" );

if( isset( $_POST[ 'add' ] ) && wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) {
  cml_admin_update_my_translations();
}

$types = array(
               "S" => __( "My translations", 'ceceppaml' ),
               "N" => __( "Notice", 'ceceppaml' ),
              );

//3rd part
$others = apply_filters( 'cml_my_translations', array() );
?>

<div class="updated">
    <p>
      <?php _e('You can use this translation with shortcode "cml_translate".', 'ceceppaml') ?>
      <a href="?page=ceceppaml-shortcode-page&tab=0#strings"><?php _e( 'Click here to see the shortcode page', 'ceceppaml' ); ?></a>
      <br />
    </p>
</div>

  <ul class="subsubsub cml-subsubsub">
    <li>
      <a class="cml-nav-tab <?php echo ( ! isset( $_GET[ 'tab' ] ) ) ? "current" : "" ?>" href="javascript:showStrings(0)">
        <?php _e( 'All', 'ceceppaml' ) ?>
      </a>
       |
    </li>
    <li>
      <a href="javascript:showStrings( 1, 'S' )">
        <?php _e( 'My translations', 'ceceppaml' ) ?>
      </a>
       |
    </li>
    <li>
       <a href="javascript:showStrings( 2, '_cml_' )">
          <?php _e( 'Plugin strings', 'ceceppaml' ) ?>
       </a>
       |
    </li>
  <?php
  $i = 3;
  $item = array();
  foreach( $others as $key => $type ) {
    $active = ( @$_REQUEST[ 'tab' ] == $key ) ? "current" : "";
$items[] = <<< EOT
    <a class="$active" href="javascript:showStrings( $i, '$key' )">
      $type
    </a>
EOT;

    $i++;
  }

  echo "<li>" . @join( ' |</li><li>', $items );
  $types = array_merge( $types, $others );
  ?>
  </ul>
  <div style="clear:both"></div>

<div class="cml-tab-wrapper cml-tab-strings">
  <div class="cml-left-items">
    <div id="cml-search">
      <input type="search" name="s" id="filter" placeholder="<?php _e( 'Text to search', 'ceceppaml' ) ?>" value="" size="30" />
      <!-- <input type="button" name="search" class="button cml-button-search" value="<?php _e( 'Search', 'ceceppaml' ) ?>" />
      <span class="spinner"></span> -->
    </div>
  </div>
  <div class="cml-right-items">
    <div class="empty"></div>
    <?php
        $lkeys = array_keys( CMLLanguage::get_all() );
        if( count( CMLLanguage::get_all() ) > 1 ) :
    ?>
    <a class="cml-button tipsy-me" id="cml-add" title="<?php _e( 'Add new record', 'ceceppaml' ) ?>"
       onclick="addRow(<?php echo count( $lkeys ) . ", '" . join(",", $lkeys ) ?>', <?php echo CMLLanguage::get_default_id() ?>)" >
      +
    </a>
    <?php endif; ?>
    <a class="cml-button tipsy-me" id="cml-save" title="<?php _e( 'Save changes', 'ceceppaml' ) ?>"
       onclick="jQuery( '.ceceppa-form-translations' ).submit()">
      <?php _e( 'Save changes', 'ceceppaml' ) ?>
    </a>
  </div>

  <div style="clear:both"></div>
</div>
    <form class="ceceppa-form-translations" name="wrap" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_GET['page'] ?>">
      <input type="hidden" name="add" value="1" />
      <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
      <input type="hidden" name="form" value="1" />
      <input type="hidden" name="tab" value="<?php echo @$_REQUEST[ 'tab' ] ?>" />
      <?php
        $table = new MyTranslations_Table( $types );
        $table->prepare_items();

        $table->display();
      ?>
    </form>

<?php
  function cml_admin_update_my_translations() {
    global $wpdb;

    //Check for what language I have to hide translation field for default language
    $hide_for = apply_filters( "cml_my_translations_hide_default", array( 'S' ) );

    //CMLTranslations::delete( "N" );
    //CMLTranslations::delete( "S" );

    $langs = CMLLanguage::get_all();

    $max = count( $_POST[ 'id' ] );
    for( $i = 0; $i < $max; $i++ ) {
      //record id
      $id = intval( $_POST[ 'id' ][ $i ] );
      $text = esc_attr( $_POST[ 'string' ][ $i ] );
      $group = esc_attr( $_POST[ 'group' ][ $i ] );

      foreach( $langs as $lang ) {
        $value = esc_attr( $_POST[ 'values' ][ $lang->id ][ $i ] );

        if( $lang->id == CMLLanguage::get_default_id() &&
            in_array( $group, $hide_for ) ) {
          continue;
        }

        //Translation db id
        $tid = intval( $_POST[ 'ids' ][ $id ][ $lang->id ] );

        CMLTranslations::set( $lang->id,
                            $text,
                            $value,
                            $group,
                            $tid );
      }
    }

    if( isset( $_POST[ 'delete' ] ) ) {
      $max = count( @$_POST[ 'delete' ] );
      for( $i = 0; $i < $max; $i++ ) {
        $wpdb->delete( CECEPPA_ML_TRANSLATIONS,
                      array(
                        'cml_text' => bin2hex( $_POST[ 'delete' ][ $i ] ),
                      ),
                      array(
                        '%s'
                      )
                     );
      }
    }

    //generate .po
    cml_generate_mo_from_translations( "_X_", true );
  }
?>

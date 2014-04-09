<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );

//Current tab
$tab = isset( $_GET[ 'tab' ] ) ? intval( $_GET[ 'tab' ] ) : 0;

if( $tab == 0 ) {
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'addons.php' );
}

$page = $_GET[ 'page' ];
?>
<div class="wrap <?php echo sanitize_title( $page ) ?>">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=0"><?php _e('Available addons', 'ceceppaml') ?></a>
    <?php 
      $addons = CMLUtils::_get( '_addons' );

      $i = 1;
      foreach( $addons as $addon ) :
        $p = CMLUtils::_get( "_" . strtolower( $addon[ 'title' ] ) . "_addon_page", $page );
    ?>
      <a class="nav-tab <?php echo $tab == $i ? "nav-tab-active" : "" ?>" href="<?php echo $p ?>"><?php echo $addon[ 'title' ] ?></a>
    <?php
      $i++;
      endforeach;
    ?>
  </h2>

  <div id="poststuff">
    <div id="post-body" class="columns-2 ceceppaml-metabox">
      <div id="post-body-content" class="cml-box-addons">
        <form id="form" name="language-item" method="POST" class="">
          <input type="hidden" name="add" value="1" />
          <input type="hidden" name="page" value="<?php echo $_GET[ 'page' ] ?>" />
          <input type="hidden" name="tab" value="<?php echo $tab ?>" />
          <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
          <?php
            do_meta_boxes( 'cml_box_addons', 'advanced', null );

            if( isset( $addons[ $tab - 1 ] ) ) {
              $addon = $addons[ $tab - 1 ][ 'addon' ];
              do_meta_boxes( 'cml_box_addons_' . $addon, 'advanced', null );
              do_action( 'cml_addon_' . $addon . '_content' );
            }
          ?>
        </form>
      </div>
      <div id="postbox-container-1" class="postbox-container cml-donate">
        <?php do_meta_boxes( 'cml_donate_box', 'advanced', null ); ?>
      </div>
    </div>
  </div>
  
</div>
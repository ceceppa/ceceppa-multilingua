<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );

//Current tab
$tab = isset( $_GET[ "tab" ] ) ? intval( $_GET[ "tab" ] ) : 0;
switch( $tab ) {
case 0:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'languages.php' );
  break;
case 1:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'language-files.php' );
  break;
case 2:
  require_once ( CML_PLUGIN_LAYOUTS_PATH . 'translate-cml.php' );
  break;
}
?>
<div class="wrap cml-wrap">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=ceceppaml-language-page&tab=0"><?php _e('Languages', 'ceceppaml') ?></a>
    
    <?php if( ! isset( $_GET[ 'wstep' ] ) || @$_GET[ 'wstep' ] == 3 ) : ?>
    <a class="nav-tab <?php echo $tab == 1 ? "nav-tab-active" : "" ?>" href="?page=ceceppaml-language-page&tab=1"><?php _e('Language files', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 2 ? "nav-tab-active" : "" ?>" href="?page=ceceppaml-language-page&tab=2"><?php _e('Ceceppa Multilingua in your language', 'ceceppaml') ?></a>
    <?php endif; ?>
  </h2>

  <div id="poststuff">
    <div id="post-body" class="columns-2 ceceppaml-metabox">
      <div id="post-body-content">
        <?php
          do_meta_boxes( 'cml_box_languages', 'advanced', null );
        ?>
      </div>
      <div id="postbox-container-1" class="postbox-container cml-donate">
        <?php do_meta_boxes( 'cml_donate_box', 'advanced', null ); ?>
      </div>
    </div>
  </div>
</div>

<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );
require_once ( CML_PLUGIN_LAYOUTS_PATH . 'class-backups.php' );

$tab = intval( @$_GET[ 'tab' ] );
$page = $_GET[ 'page' ];
?>

<div class="wrap">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=0"><?php _e('All backups', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 1 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=1"><?php _e('Backup', 'ceceppaml') ?></a>
  </h2>

    <div id="poststuff">
        <div id="post-body" class="columns-2 ceceppaml-metabox">
            <div id="post-body-content" class="cml-box-options">
                <form class="ceceppa-form-translations" name="wrap" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_GET['page'] ?>">
                  <input type="hidden" name="add" value="1" />
                  <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>
                  <input type="hidden" name="form" value="1" />
                  <input type="hidden" name="tab" value="<?php echo @$_REQUEST[ 'tab' ] ?>" />
                  <?php
                    $table = new MyBackups_Table();
                    $table->prepare_items();

                    $table->display();
                  ?>
                  <div style="text-align:right">
                    <p class="submit" style="float: right">
                    <?php if( count( CMLLanguage::get_all() ) > 1 ) : ?>
                    <input type="button" class="button button-secondaty" name="add" value="<?php _e('Add', 'ceceppaml') ?>" onclick="addRow(<?php echo count( $lkeys ) . ", '" . join(",", $lkeys ) ?>', <?php echo CMLLanguage::get_default_id() ?>)" />
                    <?php endif; ?>
                    <?php submit_button( __( 'Update', 'ceceppaml' ), "button-primary", "action", false, 'class="button button-primary"' ); ?>
                    </p>
                  </div>
                </form>

            </div>

            <div id="postbox-container-1" class="postbox-container cml-donate">
            <?php do_meta_boxes( 'cml_donate_box', 'advanced', null ); ?>
            </div>
        </div>
    </div>

</div>
<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );

$tab = intval( @$_GET[ 'tab' ] );
$page = $_GET[ 'page' ];
?>

<div class="wrap">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=0"><?php _e('All backups', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 1 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=1"><?php _e('Backup', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 2 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $page ?>&tab=2"><?php _e('Import/Export', 'ceceppaml') ?></a>
  </h2>

<?php
    //Check if the folder exists ( doesn't matter the "hide" option )
    if( ! _cml_check_backup_folder() ) {
        _cml_backup_folder_failed( false );
    }
?>
    <div id="poststuff">
        <div id="post-body" class="columns-2 ceceppaml-metabox">
            <div id="post-body-content" class="cml-box-options">
                  <?php wp_nonce_field( "security", "ceceppaml-nonce" ) ?>

                    <?php
                        switch( $tab )  {
                            case 0:
                                require_once( CML_PLUGIN_LAYOUTS_PATH . 'backups-list.php' );
                                break;
                            case 1:
                                require_once( CML_PLUGIN_LAYOUTS_PATH . 'backups-do.php' );

                                do_meta_boxes( 'cml_box_backup', 'advanced', null );
                                break;
                            case 2:
                                require_once( CML_PLUGIN_LAYOUTS_PATH . 'backups-import-export.php' );

                                do_meta_boxes( 'cml_box_backup', 'advanced', null );

                                break;
                        }
                    ?>
            </div>

            <div id="postbox-container-1" class="postbox-container cml-donate">
            <?php do_meta_boxes( 'cml_donate_box', 'advanced', null ); ?>
            </div>
        </div>
    </div>

</div>

<?php

/*
 * Backup status
 *
 *  -2: Folder not exists ( and creation failed )
 *  -1: Failed to create the file
 *   0: Everything ok
 */
if( isset( $_GET[ 'status' ] ) ) {
    $status = intval( $_GET[ 'status' ] );

    $callback = "";
    switch( $status ) {
        case -2:
            _cml_backup_folder_not_exists();
            break;
        case -1:
            _cml_backup_file_failed();
            break;
        default:
            _cml_backup_done();
            break;
    }
}

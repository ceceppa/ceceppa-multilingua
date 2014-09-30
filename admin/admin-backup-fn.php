<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

define( 'CECEPPAML_BACKUP_PATH', CML_UPLOAD_URL . trailingslashit( 'backup' ) );

/**
 * Some user reported relation lost after update... I tried to fix the issue
 * with no success. 
 * So I decide to wrote a "backup" function that will be automatically exectuted after an update,
 * but manually as well
 */
function cml_do_backup() {
    global $wpdb;

    //Check if the backup folder exists
    if( ! is_dir( CECEPPAML_BACKUP_PATH ) ) {
        $status = mkdir( CECEPPAML_BACKUP_PATH, 0777 );
        
        if( false == $status ) {
            $title = __( 'Backup not available', 'ceceppaml' );
            $msg = sprintf( __( 'Failed to create the backup folder: %s<br/>Check the folder permission.', 'ceceppaml' ) );
            
echo <<< ERROR
    <div class="error">
        <p>
            <span class="title">$title</span>
            $msg
        </p>
    </div>
ERROR;

            return;
        }
    }
    
    //Get all records from the table
    $results = $wpdb->get_results( "SELECT * FROM " . CECEPPA_ML_RELATIONS, ARRAY_A );
    
    print_r( $results );
    
}
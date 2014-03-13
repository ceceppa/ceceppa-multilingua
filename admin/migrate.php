<?php
/*
 * Migration from 1.3.x to 1.4
 *
 * Due a problem with linked posts I integrated this code already in 1.3.47
 *
 * I changed the structure of _ceceppa_ml_posts that contains 4 ( +1 ) columns...
 *
 * From 1.4 
 * 
 * id, lang_xx
 * 
 * xx - are the id of language
 *
 * relations are stored in _ceceppa_ml_relations, and all language has their own column...
 * So in the same row are stored the indexes of translations.
 * It's more easy to find linked id.
 *
 * The structure of CECEPPA_ML_TRANSLATIONS isn't fixed but it depends on managed languages.
 * For each languages the plugin create a column in the table, example:
 *
 *  | id | lang_1 | lang_2 | .... | lang_n |
 *
 * the "id" column will exists ever :).
 *
 * Why that?
 * Becase is most simple, for me, get relations between posts, because they are all on same row :)
 * 
 */
global $_cml_settings, $pagenow;

if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

define( "CECEPPA_ML_MIGRATED", get_option( "cml_migration_done", 0 ) );

if( isset( $_GET[ "cml-migrate" ] ) ) {
  add_action( 'admin_init', 'cml_migrate_database', 99 );
}

add_action( 'admin_notices', 'cml_migrate_notice' );

function cml_migrate_database() {
  global $wpdb, $wpCeceppaML;
  
  //Create table?
  $table_name = CECEPPA_ML_RELATIONS;
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    /*
     * create the table where to store relations between posts
     */
    cml_migrate_create_table();
  }

  /*
   * in CECEPPA_ML_RELATIONS each column is "lang_{SLUG}", so
   * I generate array with those column for further use :)
   */
  if( empty( $GLOBALS[ '_cml_language_columns' ] ) ) cml_generate_lang_columns();

  $_cml_language_columns = & $GLOBALS[ '_cml_language_columns' ];

  /*
   * Parse the _ml_posts
   */
  $types = array_merge( array( 'post' => 'post', 'page' => 'page' ), 
				get_post_types( array( '_builtin' => false ), 'names' ) );
				
  $args = array('numberposts' => -1, 'posts_per_page' => 999999,
		  'post_type' => $types,
		  'status' => 'publish,draft,private,future' );

  $avlangs = array_keys( $_cml_language_columns );

  $p = new WP_Query( $args );
  $langs = cml_get_languages( 0 );
  while( $p->have_posts() ) {
    $p->next_post();

    $pid = $p->post->ID;
    $lang = get_option( "cml_page_lang_" . $pid, 0 );

    if( ! in_array( $lang, $avlangs ) ) $lang = 0;

    /*
     * For migrate I need to retrive info about linked posts with cml_get_linked_post
     */
    $query = sprintf( "SELECT * FROM %s WHERE ( cml_post_id_1 = %d OR cml_post_id_2 = %d ) AND ( cml_post_lang_1 > 0 AND cml_post_lang_2 > 0 )",
		      CECEPPA_ML_POSTS, $pid, $pid );
    $linked = $wpdb->get_results( $query );

    if( empty ( $linked ) ) {
      cml_migrate_database_add_item( $lang, $pid, 0, 0 );
    } else {
      foreach( $linked as $result ) {
        $lpid = ( $result->cml_post_id_1 == $pid ) ? $result->cml_post_id_2 : $result->cml_post_id_1;
        $llang = ( $result->cml_post_id_1 == $pid ) ? $result->cml_post_lang_2 : $result->cml_post_lang_1;
        
        cml_migrate_database_add_item( $lang, $pid, $llang, $lpid );
      }
    }
  }
  
  update_option( "cml_migration_done", 3 );
  
  cml_fix_rebuild_posts_info();
}

/*
 * Add relation in CECEPPA_ML_RELATIONS table
 *
 * @aram $lang - of the post
 * @param $pid  - id of the post
 * @param $llang - lang of linked post
 * @param $lpid - id of linked post
 */
function cml_migrate_database_add_item( $lang, $pid, $llang, $lpid ) {
  global $wpdb;

  if( empty( $GLOBALS[ '_cml_language_columns' ] ) ) cml_generate_lang_columns();

  $_cml_language_columns = & $GLOBALS[ '_cml_language_columns' ];

  /*
   * if $llang == 0 break relations
   */
  $old_lang = CMLPost::get_language_id_by_id( $pid );
  //$pid != $lpid because same post can be assigned to multiple languages
  if( 0 == $llang || ( $old_lang != $lang && $pid != $lpid ) ) {
    foreach( $_cml_language_columns as $col ) {
      $wpdb->query( sprintf( "
        UPDATE %s SET %s = 0 WHERE %s = %d",
          CECEPPA_ML_RELATIONS,
          $col, $col, $pid ) );
    }
  }

  /*
   * remove linked post from all records
   */
  if( ( $lpid > 0 && $lpid != $pid ) ) {
    foreach( $_cml_language_columns as $col ) {
      if( $lang > 0 ) {
        $wpdb->query( sprintf( "
          UPDATE %s SET %s = 0 WHERE %s = %d AND lang_%d != %d ",
            CECEPPA_ML_RELATIONS,
            $col, $col, $lpid, $lang, $pid ) );
      } else {
        $wpdb->update( CECEPPA_ML_RELATIONS,
                       array( $col => 0 ),
                       array( $col => $lpid ),
                       array( "%d" ), array( "%d" ) );
      }
    }
  }

  //Is set language?
  if( $lang > 0 ) {
    $query = sprintf( "SELECT id FROM %s WHERE lang_%d = %d", 
					  CECEPPA_ML_RELATIONS,
					  $lang,
					  $pid );
    $record = $wpdb->get_var( $query );

    if( ! empty( $record ) && $llang > 0 ) {
      $wpdb->update( CECEPPA_ML_RELATIONS,
		      array( "lang_$llang" => $lpid ),
		      array( "id" => $record ),
		      array( "%d" ),
		      array( "%d" ) );
    } else {
      //Set all fields to 0
      foreach( $_cml_language_columns as $col ) {
        $values[ $col ] = 0;
      }
      
      $values[ "lang_$lang" ] = $pid;
      if( $llang > 0 ) $values[ "lang_$llang" ] = $lpid;

      $wpdb->insert( CECEPPA_ML_RELATIONS,
		      $values,
		      array_fill( 0, count( $values ), "%d" ) );
    }
  } else { // if
    /*
    * remove linked post from all records
    */
    foreach( $_cml_language_columns as $col ) {
      $wpdb->update( CECEPPA_ML_RELATIONS,
                     array( $col => 0 ),
                     array( $col => $pid ),
                     array( "%d" ), array( "%d" ) );
    }

    //Set all fields to 0
    foreach( $_cml_language_columns as $col ) {
      $values[ $col ] = $pid;
    }

    $wpdb->insert( CECEPPA_ML_RELATIONS,
		    $values,
		    array_fill( 0, count( $values ), "%d" ) );
  }

  foreach( $_cml_language_columns as $l ) {
    $where[] = "$l = 0";
  }

  $query = sprintf( "DELETE FROM %s WHERE %s", CECEPPA_ML_RELATIONS, join( " AND ", $where ) );
  $wpdb->query( $query );
}

/*
 * create table CECEPPA_ML_RELATIONS
 */
function cml_migrate_create_table() {
  global $wpdb;

  $wpdb->query( "DROP TABLE " . CECEPPA_ML_RELATIONS );

  $langs = cml_get_languages( false );

  $query = "CREATE TABLE " . CECEPPA_ML_RELATIONS . " ( id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT ";
  foreach( $langs as $lang ) {
    $query .= ", lang_" . $lang->id . " bigint(20) NOT NULL ";
  }
  $query .= ")";

  $wpdb->query( $query );
}

/*
 * For CeceppaML < 1.3.47 I need to migrate rilations from
 *
 * CECEPPA_ML_POSTS to CECEPPA_ML_RELATIONS
 */
function cml_migrate_notice( $force = false ) {
  global $wpdb;

  $query = "SELECT COUNT(*) FROM " . CECEPPA_ML_RELATIONS;
  $results = $wpdb->get_results( $query );

  if( CECEPPA_ML_MIGRATED == 2 ) {
    cml_generate_lang_columns();

    update_option( "cml_migration_done", 3 );
  }
  
  if( CECEPPA_ML_MIGRATED < 2 || $force ) {
    if( ! $force ) {
      echo '<div class="updated">';
    }
?>
      <strong>
        Ceceppa Multilingua
      </strong>
      <br /><br />
      <a href="<?php echo add_query_arg( array( 'cml-migrate' => 1 ) ) ?>">
        <?php _e('Update required, click here for update posts relations', 'ceceppaml') ?>
      </a>
<?php
    if( ! $force ) {
      echo '</div>';
    }
  }
}

?>

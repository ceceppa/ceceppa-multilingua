<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * Ceceppa Multilingua >= 1.4
 */
function cml_do_update() {
  global $wpdb;

  $dbVersion = & $GLOBALS[ 'cml_db_version' ];
  $fix = isset( $_GET['fix-upgrade'] ) ? intval( $_GET['fix-upgrade'] ) : 0;

  if( $dbVersion <= 24 ) {
    $queries[] = sprintf( "ALTER TABLE  %s CHANGE cml_language cml_language TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL",
                       CECEPPA_ML_TABLE );

    $queries[] = sprintf( "UPDATE %s SET cml_flag_path = 0", CECEPPA_ML_TABLE );

    $queries[] = sprintf( "ALTER TABLE  %s CHANGE cml_flag_path cml_custom_flag INT(1) NULL DEFAULT NULL",
                       CECEPPA_ML_TABLE );

    //Store NOTICE translations to CECEPPA_ML_TRANSLATIONS table
    CMLTranslations::delete( "N" );
    $notices = array( "_notice_post", "_notice_page" );
    foreach( $notices as $notice ) {
      $select = sprintf( "SELECT '%s', id, cml%s, 'N' FROM %s", $notice, $notice, CECEPPA_ML_TABLE );

      $queries[] = sprintf( "INSERT INTO %s ( cml_text, cml_lang_id, cml_translation, cml_type) %s",
                           CECEPPA_ML_TRANSLATIONS, $select );
    }

    //NOTICE POST and NOTICE PAGE
    $queries[] = sprintf( "ALTER TABLE %s DROP cml_notice_post, DROP cml_notice_page, DROP cml_notice_category",
                           CECEPPA_ML_TABLE );

    foreach( $queries as $query ) {
      $wpdb->query( $query );
    }

    cml_fix_update_post_meta();
  }

  if( $dbVersion <= 25 ) {
    $menu = get_option( 'cml_add_items_to' );
    if( ! is_array( $menu ) ) {
      update_option( "cml_add_items_to", array( $menu ) );

      cml_generate_settings_php();
    }
  }

  if( $dbVersion <= 26 ) {

    $rows = $wpdb->get_results( "SELECT *, unhex( cml_cat_translation ) as translation FROM " . CECEPPA_ML_CATS );
    foreach( $rows as $row ) {
      //In CECEPPA_ML_CATS categories are stored in lowercase, I update them from wp options
      $cat = get_option( "cml_category_" . $row->cml_cat_id . "_lang_" . $row->cml_cat_lang_id, $row->translation );
      delete_option( "cml_category_" . $row->cml_cat_id . "_lang_" . $row->cml_cat_lang_id );

      if( empty( $cat ) ) continue;

      $wpdb->update( CECEPPA_ML_CATS,
                    array(
                      'cml_cat_translation' => bin2hex( $cat ),
                    ),
                    array(
                      'id' => $row->id,
                    ),
                    array( "%s" ),
                    array( "%d" ) );
    }
  }

  if( $dbVersion < 27 || $fix == 27 ) {
    $query = sprintf( "ALTER TABLE %s ADD  `cml_cat_translation_slug` VARCHAR( 100 ) NOT NULL",
                     CECEPPA_ML_CATS );
    $wpdb->query( $query );

    $rows = $wpdb->get_results( "SELECT *, unhex( cml_cat_translation ) as translation FROM " . CECEPPA_ML_CATS );
    foreach( $rows as $row ) {
      $slug = sanitize_title( strtolower( $row->translation ) );

      $wpdb->update( CECEPPA_ML_CATS,
                    array(
                      'cml_cat_translation_slug' => bin2hex( $slug ),
                    ),
                    array(
                      'id' => $row->id,
                    ),
                    array( "%s" ),
                    array( "%d" ) );
    }
  }

  if( $dbVersion < 28 ) {
    $query = sprintf( "SELECT * FROM %s", CECEPPA_ML_RELATIONS );
    $results = $wpdb->get_results( $query, ARRAY_N );

    /*
     * Remove "_cml_meta", becase for CML <= 1.4.9, meta tags will be updated only to
     * edited page/post not its translations :(
     * They will be rebuilded when "get_translations" will be called
     */
    foreach( $results as $rec ) {
      unset( $rec[ 'id' ] );

      foreach( $rec as $key => $value ) {
        if( $value == 0 ) continue;

        delete_post_meta( $value, "_cml_meta" );
      }//foreach
    }//foreach
  }//if

  if( $dbVersion < 29 ) {
    $wpdb->query( sprintf( "ALTER TABLE  %s CHANGE cml_language cml_language TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL",
                       CECEPPA_ML_TABLE ) );
  }

  if( $dbVersion < 30 ) {
    //Get all unique posts from database
    cml_fix_rebuild_posts_info();
  }

  if( $dbVersion < 31 ) {
    require_once( "admin-taxonomies.php" );

    $wpdb->query(  "ALTER TABLE  " . CECEPPA_ML_CATS . " ADD  `cml_taxonomy` TEXT NOT NULL ;" );

    $query = "UPDATE " . CECEPPA_ML_CATS . " a
		JOIN $wpdb->term_taxonomy b ON a.cml_cat_id = b.term_id
		SET a.cml_taxonomy = b.taxonomy";

    _cml_copy_taxonomies_to_translations();

    $wpdb->query( $query );
  }

  if( $dbVersion < 32 ) {
    cml_update_taxonomies_translations();
  }

  if( $dbVersion < 34 || $fix == 34 ) {
    $query = sprintf( "ALTER TABLE %s ADD  `cml_cat_description` LONGTEXT",
                     CECEPPA_ML_CATS );
    // error_log( $query );
    $wpdb->query( $query );
  }

  //CML < 1.4
  cml_do_update_old();

  update_option( "cml_db_version", CECEPPA_DB_VERSION );
}

/*
 * fix required by Ceceppa Multilingua < 1.4
 */
function cml_do_update_old() {
  global $wpdb;

  $dbVersion = get_option( "cml_db_version", CECEPPA_DB_VERSION );

  if( $dbVersion < 24 ) {
    add_action( 'admin_init', 'cml_fix_rebuild_posts_info' );
  }

  if( $dbVersion < 23 ) {
    add_action( 'plugins_loaded', 'cml_fix_insert_post_info' );
  }

  if( $dbVersion < 22 ) {
    if( get_option( 'cml_option_flags_on_pos', 'top' ) == "top" )
      update_option( "cml_option_flags_on_pos", "after" );
  }

  if( $dbVersion < 21 ) {
      $wpdb->query(  "ALTER TABLE  " . CECEPPA_ML_TABLE . " ADD  `cml_date_format` TEXT NOT NULL ;" );
  }

  if( $dbVersion < 20 ) {
      $wpdb->query(  "ALTER TABLE  " . CECEPPA_ML_TABLE . " ADD  `cml_rtl` INT NOT NULL ;" );
  }

  //Rimuovo le colonne non più necessarie
  if( $dbVersion <= 9 ) :
    $wpdb->query("ALTER table " . CECEPPA_ML_TABLE . " DROP cml_category_name, DROP cml_category_id, DROP cml_category_slug, DROP cml_page_id, DROP cml_page_slug");
  endif;

  //modifico il charset della tabella
  if( $dbVersion <= 9 ) :
    $alter = "ALTER TABLE  " . CECEPPA_ML_TABLE . " CHANGE  `cml_language`  `cml_language` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NULL DEFAULT NULL,"
      . "CHANGE  `cml_notice_post`  `cml_notice_post` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NULL DEFAULT NULL ,"
      . "CHANGE  `cml_notice_page`  `cml_notice_page` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NULL DEFAULT NULL ,"
      . "CHANGE  `cml_notice_category`  `cml_notice_category` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NULL DEFAULT NULL ,"
      . "CHANGE  `cml_locale`  `cml_locale` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NULL DEFAULT NULL";
    $wpdb->query($alter);

    $alter = "ALTER TABLE  `wp_ceceppa_ml_trans` CHANGE  `cml_text`  `cml_text` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NOT NULL ,"
      . "CHANGE  `cml_translation`  `cml_translation` TEXT CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NULL DEFAULT NULL";
    $wpdb->query($alter);
  endif;

  //Fix dovuto alla 0.9.1, tutte le lingue venivano impostate come default se l'utente face click su update :(
  if(get_option("cml_db_version", CECEPPA_DB_VERSION) < 9) :
    $wpdb->query("UPDATE " . CECEPPA_ML_TABLE . " SET cml_default = 0");
    $wpdb->query("UPDATE " . CECEPPA_ML_TABLE . " SET cml_default = 1 WHERE id = 1");
  endif;

  //Ricreo tutti gli indici "cml_post_lang_##" in "cml_page_lang"
  if(get_option("cml_db_version", CECEPPA_DB_VERSION) <= 10) :
    $results = $wpdb->get_results("SELECT * FROM " . CECEPPA_ML_POSTS);
    foreach($results as $result) :
    if($result->cml_post_lang_1 > 0) update_option("cml_page_lang_" . $result->cml_post_id_1, $result->cml_post_lang_1);
    if($result->cml_post_lang_2 > 0) update_option("cml_page_lang_" . $result->cml_post_id_2, $result->cml_post_lang_2);

    delete_option("cml_post_lang_" . $result->cml_post_id_1);
    delete_option("cml_post_lang_" . $result->cml_post_id_2);
    endforeach;
  endif;

  //Cancello i post che sono stati cancellati, ma esistono ancora nella mia tabella
  if(get_option("cml_db_version", CECEPPA_DB_VERSION) <= 12) :
    $sql = "DELETE FROM " . CECEPPA_ML_POSTS . " WHERE cml_post_id_1 NOT IN (select ID from $wpdb->posts)";
    $wpdb->query($sql);

    $sql = "DELETE FROM " . CECEPPA_ML_POSTS . " WHERE cml_post_id_2 NOT IN (select ID from $wpdb->posts)";
    $wpdb->query($sql);
  endif;

  if(get_option("cml_db_version", CECEPPA_DB_VERSION) <= 14) :
    $args = array('hide_empty' => 0);
    $cats = get_categories($args);

    $langs = cml_get_languages(0, 0);
    foreach($cats as $cat) :
  foreach($langs as $lang) :
    $name = get_option("cml_category_" . $cat->term_id . "_lang_" . $lang->id, $cat->name);

    $wpdb->insert(CECEPPA_ML_CATS,
          array("cml_cat_name" => bin2hex(strtolower($cat->name)),
                "cml_cat_lang_id" => $lang->id,
                "cml_cat_translation" => bin2hex(strtolower($name)),
                "cml_cat_id" => $cat->term_id),
          array('%s', '%d', '%s', '%d'));
  endforeach;
    endforeach;
  endif;

  if($dbVersion <= 17) :
    $sql = "ALTER TABLE  " . CECEPPA_ML_TABLE . " ADD  `cml_sort_id` INT NOT NULL ;";
    $wpdb->query($sql);

    $sql = "UPDATE " . CECEPPA_ML_TABLE . " SET cml_sort_id = id";
    $wpdb->query($sql);
  endif;

  //if($dbVersion <= 15) :
    //cml_fix_widget_titles();
  //endif;

  //Controllo se esiste una pagina con lo slug "/##/", perché nelle versioni < 1.2.6
  //per avere la pagina iniziale in stile www.example.com/it dovevo modificare lo slug della
  //pagina in "it", dalla 1.2.6 basta mettere una pagina statica come iniziale, il plugin
  //si occuperà del resto...
  if($dbVersion <= 16) :
    $id = cml_get_default_language_id();
    $info = CMLLanguage::get_by_id( $id );

    $slug = $info->cml_language_slug;
    $the_id = cml_get_page_id_by_path ( $slug, array('page') );

    if( $the_id ) update_option( 'cml_need_use_static_page', 1 );
  endif;

  if( $dbVersion <= 17 ) :
  add_action( 'plugins_loaded', 'cml_fix_rebuild_posts_info' );
  endif;

  if( $dbVersion <= 18 ) :
    $wpdb->query( "ALTER TABLE  " . CECEPPA_ML_TABLE . " ADD  `cml_flag_path` TEXT" );
    endif;
}

function cml_fix_update_post_meta() {
  $args = array('numberposts' => -1, 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1,
        'post_type' => get_post_type(),
        'status' => 'publish,inherit,pending,private,future,draft' );

  $posts = new WP_Query( $args );

  //Scorro gli articoli
  while( $posts->have_posts() ) {
    $posts->next_post();

    $post = $posts->post;

    delete_option( "cml_page_{$post->ID}" );
    delete_option( "cml_page_lang_{$post->ID}" );

    //Add my meta to post
    $meta = array( "lang" => CMLPost::get_language_id_by_id( $post->ID ),
                    "translations" => CMLPost::get_translations( $post->ID ) );

    update_post_meta( $post->ID, "_cml_meta", $meta );
  }
}

/*
 * Fino alla versione 0.9.22 la funzione hide_translation stabiliva quali articoli nascondere al momento,
 * il che richiedeva una serie di elaborazioni che potrebbero ripercuotersi sulla velocità di caricamento della pagina.
 *
 * Dato che il plugin non presenta bug evidenti, con la 1.0 voglio ottimiazzare anche un po' il codice, evitando
 * "cicli" superflui memorizzando le informazioni necessarie nel momento in cui l'utente pubblica un articolo.
 *
 * Memorizzo per ogni lingua gli id dei rispettivi post.
 *
 */
function cml_fix_rebuild_posts_info() {
  global $wpdb, $_cml_language_columns;

  $pids = array();
  $apids = array(); //All pids
  $i = 0;
  $results = $wpdb->get_results( "SELECT * FROM " . CECEPPA_ML_RELATIONS );

  $uniques = array();
  foreach( $results as $result ) {
    $r = ( Array ) $result;

    unset( $r[ 'id' ] );

    $first = reset( $r );
    $is_unique = 1;
    foreach( $_cml_language_columns as $key => $l ) {
      $is_unique = $is_unique && ( $r[ $l ] == $first );

      if( $r[ $l ] > 0 ) {
        $pids[ $key ][] = $r[ $l ];
        $apids[ $i ][ $key ] = $r[ $l ];
      }
    }

    if( $is_unique ) {
      $uniques[] = $first;
    }

    $i++;
  }

  foreach( $_cml_language_columns as $key => $l ) {
    @update_option( "cml_posts_of_lang_" . $key, array_unique( $pids[ $key ] ) );
  }

  //unique posts
  @update_option( "cml_unique_posts", array_unique( $uniques ) );

  /*
   * hide translations of current post..
   * "Show all posts but hide their translations"
   */
  $hide = array();
  foreach( $results as $result ) {
    $result = ( Array ) $result;

    foreach( $_cml_language_columns as $key => $lang ) {
      $hideall = ( $result[ $lang ] > 0 );

      $langs = $_cml_language_columns;
      unset( $langs[ $lang ] );

      foreach( $langs as $k => $l ) {
        if( $hideall &&
            $result[ $l ] > 0 &&
            $result[ $l ] != $result[ $lang ] ) {
          $hide[ $key ][] = $result[ $l ];
        }

        if( $result[ $l ] > 0 )
          $hideall = true;
      }
    }
  }

  //Indexes to hide
  foreach( $_cml_language_columns as $key => $l ) {
    @update_option( "cml_hide_posts_for_lang_" . $key, array_unique( $hide[ $key ] ) );
  }
}


/*
 * set the language of all posts
 * to default one
 */
function cml_update_all_posts_language() {
  global $wpdb;

  $posts = get_posts(array('order' => 'ASC',
                            'post_type' => get_post_types(),
                            'orderby' => 'title',
                            'numberposts' => -1,
                            'posts_per_page' => 999999,
                            'status' => 'publish, draft, private'));

  $did = CMLLanguage::get_default_id();
  foreach($posts as $post) {
    echo "$post->ID,";

    CMLPost::set_language( $did,
                          $post->ID );
  } //endforeach;
}

function cml_update_taxonomies_translations() {
  global $wpdb;

  $query = sprintf( "UPDATE %s as t1 INNER JOIN %s as t2 ON HEX(CONCAT(cml_taxonomy, '_', UNHEX(cml_cat_name))) = cml_text AND cml_cat_lang_id = cml_lang_id AND t2.cml_type = 'C' SET t1.cml_cat_translation = t2.cml_translation", CECEPPA_ML_CATS, CECEPPA_ML_TRANSLATIONS );
  $wpdb->query( $query );

  update_option( 'cml_taxonomies_updated', 1 );
}

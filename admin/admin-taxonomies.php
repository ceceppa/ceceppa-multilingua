<?php
//if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

//die();
function cml_admin_taxonomy_add_form_fields( $tag ) {
?>
  <div class="form-field cml-form-field">
  <?php
      $langs = cml_get_languages( false, true );

      foreach($langs as $lang) : ?>
        <label for="cat_name[ <?php echo $lang->id ?> ]">
          <?php echo CMLLanguage::get_flag_img( $lang->id ); ?>
          <?php echo $lang->cml_language ?>
        </label>
        <input type="text" name="cat_name[<?php echo $lang->id ?>]" id="cat_name[<?php echo $lang->id ?>]" size="40" />
    <?php endforeach; ?>
  </div>
<?php
}

/**
 * category translations form
 */
function cml_admin_taxonomy_edit_form_fields( $tag ) {
  wp_enqueue_script('ceceppaml-cat');

  $t_id = $tag->term_id;
?>
  <?php
    $langs = cml_get_languages( false );

    foreach( $langs as $lang ) {
      if( ! $lang->cml_default ) {
        $id = $lang->id;

        $img = CMLLanguage::get_flag_img( $lang->id );

        //$value = get_option( "cml_category_" . $t_id . "_lang_$id", $tag->name );
        $tname = strtolower( $tag->taxonomy . "_" . $tag->name );
        $value = CMLTranslations::get( $lang->id, $tname, "C", true );
        if( empty( $value ) ) $value = $tag->name;
echo <<< EOT
  <tr class="form-field cml-form-field">
  <td>
      $img
      $lang->cml_language
  </td>
  <td>
      <input type="text" name="cat_name[$lang->id]" id="cat_name_{$lang->id}" size="40" value="$value"/>
  </td>
  </tr>
EOT;
      }
    }
}

function cml_admin_save_extra_taxonomy_fileds( $term_id ) {
  global $wpdb, $pagenow;

  //In wp 3.6 viene richiamata questa funzione anche quando salvo i menu... :O
  if( strpos( $pagenow, "nav-menus" ) !== FALSE ) {
    return;
  }

  //In Quickedit non devo fare nulla
  if( ! isset( $_POST['cat_name'] ) ) {
    return;
  }

  /*
   * In 1.4 I store term translation in my transltions table, instead of "wordpress options" ( update_option )
   * because from "1.4" plugin generate .mo file with all translations, and I can use it instead
   * asking mysql for translations :)
   */
  $cats = $_POST[ 'cat_name' ];
  $name = isset( $_POST[ 'name' ] ) ? $_POST[ 'name' ] : $_POST[ 'tag-name' ];
  foreach( $cats as $key => $cat ) {
    _cml_add_taxonomy_translation( $term_id, $name, $key, $cat, $_POST[ 'taxonomy' ] );
  }
}

//quickedit
function _cml_admin_quickedit_taxonomy( $term_id ) {
  global $wpdb;

  //Update translation
  $wpdb->update( CECEPPA_ML_CATS,
                array(
                      "cml_cat_translation" => bin2hex( esc_attr( $_POST[ 'name' ] ) ),
                      ),
                array(
                  "cml_cat_id" => $term_id,
                  "cml_cat_lang_id" => CMLLanguage::get_current_id(),
                ),
                array( "%s" ),
                array(
                      "%d",
                      "%d",
                      ) );

  //restore original category name
  $query = sprintf( "SELECT UNHEX( cml_cat_name ) FROM %s WHERE cml_cat_id = %d",
                    CECEPPA_ML_CATS, $term_id );

  $name = $wpdb->get_var( $query );

  _cml_copy_taxonomies_to_translations();

  //Update translations :)
  cml_generate_mo_from_translations( "_X_", false );
}

function _cml_add_taxonomy_translation( $id, $name, $lang_id, $translation, $taxonomy ) {
  global $wpdb;

  $query = sprintf( "SELECT * FROM %s WHERE cml_cat_id = %d AND cml_cat_lang_id = %d",
                   CECEPPA_ML_CATS, $id, $lang_id );
  $q = $wpdb->get_row( $query );

  $name = strtolower( $name );
  //$translation = strtolower( $translation );
  if( count( $q ) > 0 ) {
    $r_id = $q->id;

    $wpdb->update( CECEPPA_ML_CATS,
		  array(
            "cml_cat_name" => bin2hex( $name ),
			"cml_cat_lang_id" => $lang_id,
			"cml_cat_translation" => bin2hex( $translation ),
			"cml_cat_translation_slug" => bin2hex( strtolower( sanitize_title( $translation ) ) ),
      "cml_taxonomy" => $taxonomy,
            ),
		  array( "id" => $r_id ),
		  array( '%s', '%d', '%s', '%s' ),
		  array( "%d" ) );
  } else {
    $wpdb->insert( CECEPPA_ML_CATS,
		  array(
            "cml_cat_name" => bin2hex( $name ),
			"cml_cat_lang_id" => $lang_id,
			"cml_cat_translation" => bin2hex( $translation ),
			"cml_cat_translation_slug" => bin2hex( strtolower( sanitize_title( $translation ) ) ),
			"cml_cat_id" => $id,
      "cml_taxonomy" => $taxonomy,
            ),
		  array('%s', '%d', '%s', '%s', '%d') );
  }

  _cml_copy_taxonomies_to_translations();

  //Update translations :)
  cml_generate_mo_from_translations( "_X_", false );
}

function _cml_copy_taxonomies_to_translations() {
  global $wpdb;

  //delete all translations
  CMLTranslations::delete( "C" );

  //copy
  $query = sprintf( "INSERT INTO %s ( cml_text, cml_lang_id, cml_translation, cml_type) " .
                   "( SELECT HEX( CONCAT( cml_taxonomy, '_', UNHEX(cml_cat_name) ) ), cml_cat_lang_id, cml_cat_translation, 'C' FROM %s )",
                   CECEPPA_ML_TRANSLATIONS, CECEPPA_ML_CATS );

  $wpdb->query( $query );
}

function cml_admin_delete_extra_taxonomy_fields( $term_id ) {
  global $wpdb;

  //Cancello la voce dal database
  $query = sprintf( "DELETE FROM %s WHERE cml_cat_id = %d", CECEPPA_ML_CATS, $term_id );
  $wpdb->query($query);

  _cml_copy_taxonomies_to_translations();
}

/*
 * Add flags icon to all posts list header
 */
function cml_admin_taxonomy_flag_columns( $columns ) {
  $langs = cml_get_languages( false );

  //Non sono riuscito a trovare un altro modo per ridimensionare la larghezza del th...
  wp_enqueue_style('ceceppaml-style-all-posts', CML_PLUGIN_URL . 'css/all_posts.php?langs=' . count( $langs ) );

  $clang = isset( $_GET['cml-lang'] ) ? intval ( $_GET['cml-lang'] ) : CMLLanguage::get_default_id();
  $img = "";
  foreach( $langs as $lang ) {
    $class = ( $lang->id == $clang ) ? "cml-filter-current" : "";

    $a = add_query_arg( array( "cml-lang" => $lang->id ) );
    $img .= "<a class=\"$class tipsy-me\" href=\"$a\" title=\"" . __('Language: ', 'ceceppaml') . "<b>$lang->cml_language</b>\"><img src=\"" . cml_get_flag_by_lang_id( $lang->id, CML_FLAG_TINY ) . "\" alt=\"$lang->cml_language\" /></a>";
  }

  $cols = array_merge( array_slice( $columns, 0, 2 ),
                        array("cml_flags" => $img),
                        array_slice( $columns, 2 ) );

  return $cols;
}

function cml_admin_taxonomy_disable_quickedit( $actions, $tag ) {
  unset( $actions['inline hide-if-no-js'] );

  return $actions;
}

if( ! CMLLanguage::is_default() ) {
  add_filter( 'tag_row_actions', 'cml_admin_taxonomy_disable_quickedit', 10, 2 );
}
?>

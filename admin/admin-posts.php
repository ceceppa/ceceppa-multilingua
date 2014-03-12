<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

function cml_admin_post_meta_box( $tag ) {
  global $wpdb;
  
  $langs = cml_get_languages( false );

  //I have clicked on "+" symbol for add translation?
  if( array_key_exists( "post-lang", $_GET) ) {
    $post_lang = intval( $_GET[ 'post-lang' ] );
  }

  //Language of post/page
  echo "<h4>";
  _e( 'Language', 'ceceppaml' );
  echo '<span class="cml-help cml-pointer-help cml-post-help"></span>';
  echo "</h4>";
  
  $post_lang = ( ! isset( $post_lang ) || $post_lang < 0 ) ?
    CMLPost::get_language_id_by_id( $tag->ID, true ) : $post_lang;

  cml_dropdown_langs( "post_lang", $post_lang, false, true, __( "All languages", "ceceppaml" ), "", 0 );

    //Translations
  echo "<h4>" . __( 'Translations', 'ceceppaml' ) . "</h4>";

  //Linked post?
  if( isset( $_GET[ 'link-to' ] ) ) {
    $link_id = intval( $_GET[ 'link-to' ] );
    
    /* recover category from linked id */
    $categories = wp_get_post_categories( $link_id );
    if( ! empty( $categories ) ) {
      wp_set_post_categories( $tag->ID, $categories );
    }
    
    //for page get parent
    $post = get_post( $link_id );
    
    //Has translation?
    $parent_t = CMLPost::get_translation( $post_lang, $post->post_parent );
    $tag->post_parent = ( ! empty( $parent_t ) ) ? $parent_t : $post->post_parent;
  } else {
    $link_id = 0;
  }

  //All posts except posts that exists in current language
  if( $post_lang > 0 ) {
    $not = CMLPost::get_posts_by_language( $post_lang );
  } else {
    $not = array();
  }

  $translations = CMLPost::get_translations( ( $link_id > 0 ) ? $link_id : $tag->ID );

  echo '<ul class="cml-post-translations">';
  foreach( CMLLanguage::get_all() as $lang ) {
    if( $lang->id == $post_lang ) continue;
    
    //Translation id
    $t_id = @$translations[ $lang->cml_language_slug ];
    $class = ( empty( $t_id ) ) ? "no-traslation" : "" ;
    $bclass = ( empty( $t_id ) ) ? "add" : "edit";
    $msg = empty( $t_id ) ? __( 'Add translation', 'ceceppaml' ) : __( 'Edit', 'ceceppaml' );
    $img = CMLLanguage::get_flag_img( $lang->id );
    $title = empty( $class ) ? get_the_title( $t_id ) : "";

    $href = admin_url() . "post-new.php?post_type={$tag->post_type}&link-to={$tag->ID}&post-lang={$lang->id}";
    
    $GLOBALS[ '_cml_no_translate_home_url' ] = 1;
    $link = empty( $t_id ) ? $href : get_edit_post_link( $t_id );
    unset( $GLOBALS[ '_cml_no_translate_home_url' ] );

    echo "<li class=\"$class\">";
    _cml_admin_post_meta_translation( $tag->post_type, $lang->id, $t_id );
    echo "<a href=\"$link\" class=\"button cml-button-$bclass tipsy-s\" title=\"$msg\"></a>";
    echo " </li>";
  }
  
  echo "</ul>";
}

function _cml_admin_post_meta_translation( $type, $lang, $linked_id ) {
  $args = array('numberposts' => -1, 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1,
      'post_type' => $type,
      'post__not_in' => CMLPost::get_posts_by_language( $lang ),
      'status' => 'publish,inherit,pending,private,future,draft');
  
  $posts = new WP_Query( $args );

  $notrans = __( 'None', 'ceceppaml' );
  $title = ( ! empty( $linked_id ) ) ? get_the_title( $linked_id ) : $notrans;
  $src = CMLLanguage::get_flag_src( $lang );

echo <<< EOT
  <ul name="linked_post" class="cml-dropdown-me">
    <li>
      <img class="flag" src="$src" width="16" height="11" />
      <input type="text" value="$title" original="$title" />
      <input type="hidden" name="linked_post[$lang]" value="$linked_id" />
      <ul>
        <li class="no-hide">
          <span>$notrans</span>
        </li>
EOT;
  while( $posts->have_posts() ) {
    $posts->next_post();

    $id = $posts->post->ID;

	$lang_id = CMLPost::get_language_id_by_id( $id );

	echo "<li cml-trans=\"$id\">";
    echo "<span>" . get_the_title( $id ) . "</span>";
    echo "</li>";
  }

echo <<< EOT
      </ul>
    </li>
  </ul>
EOT;

}
/* 
 * Salvo il collegamento tra i post
 */
function cml_admin_save_extra_post_fields( $term_id ) {
  global $wpdb, $pagenow;

  //Dalla 3.5.2 questa funzione viene richiamata 2 volte :O, la seconda volta $_POST però è vuoto :O
  if( $pagenow == "nav-menus.php" || empty( $_POST ) ) {
    return;
  }

  $post_id = is_object( $term_id ) ? $term_id->ID : $term_id;

  //no language?
  if( empty( $_POST['cml-lang'] ) )
    $post_lang = 0;
  else
    $post_lang = intval( $_POST[ 'cml-lang' ] );

  foreach( CMLLanguage::get_all() as $lang ) {
    if( $lang->id == $post_lang ) continue;

    //Set language of current post
    $linked = intval( @$_POST[ 'linked_post' ][ $lang->id ] );

    CMLPost::set_translation( $post_id, $lang->id, $linked, $post_lang );
  }

  /*
   * Quickedit?
   */
  if( isset( $_POST[ 'cml-quick' ] ) ) {
    $langs = CMLLanguage::get_all();

    $current = CMLPost::get_language_id_by_id( $post_id );
    foreach( $langs as $lang ) {
      if( $lang->id == $current ) continue;

      $key = "linked_$lang->cml_language_slug";

      if( isset( $_POST[ $key ] ) ) {
        $lid = $_POST[ $key ];
        $linked_lang = CMLLanguage::get_id_by_post_id( $lid );

        //Change also the language of linked lang
        if( $linked_lang != $lang->id ) {
          CMLPost::set_translation( $lid, 0, 0, $lang->id );
          
          $linked_lang = $lang->id;
        }

        CMLPost::set_translation( $post_id, $linked_lang, $lid, $post_lang );
      }
    }
  }
}

/*
 * Add flags icon to all posts list header
 */
function cml_admin_add_flag_columns( $columns ) {
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

/*
 * add flags to single item
 */
function cml_admin_add_flag_column( $col_name, $id ) {
  if( $col_name !== "cml_flags" ) return;

  if ( ! isset( $_GET[ 'post_type' ] ) )
      $post_type = 'post';
  else
      $post_type = $_GET['post_type'];

  //Recupero la lingua del post/pagina
  $xid = CMLPost::get_language_id_by_id( $id );
  
  $langs = cml_get_languages( false );
  $linked = cml_get_linked_posts( $id );
  
  $GLOBALS[ '_cml_no_translate_home_url' ] = 1;

  foreach( $langs as $lang ) {
    $link = isset( $linked[ $lang->cml_language_slug ] ) ? $linked[ $lang->cml_language_slug ] : 0;
    if( $link > 0 ) {
      $title = "<br />" . get_the_title($link);
      echo '<a href="' . get_edit_post_link($link) . '">';
      echo '    <img class="tipsy-me" src="' . cml_get_flag_by_lang_id($lang->id, "tiny") . '" title="' . __('Edit post: ', 'ceceppaml') . $title . '"/>';
      echo '</a>';
      
    } else {
      echo '<a href="' . get_bloginfo("url") . '/wp-admin/post-new.php?post_type=' . $post_type . '&link-to=' . $id . '&post-lang=' . $lang->id . '">';
      echo '    <img class="add tipsy-me" src="' . CML_PLUGIN_URL . 'images/edit.png" title="' . __( 'Translate to:', 'ceceppaml' ) . ' ' . $lang->cml_language . '" />';
      echo '</a>';
    }
  }
  
  unset( $GLOBALS[ '_cml_no_translate_home_url' ] );
}

function cml_admin_add_meta_boxes() {
  //Page and post meta box
  add_meta_box( 'ceceppaml-meta-box', __('Post data', 'ceceppaml'), 'cml_admin_post_meta_box', 'post', 'side', 'high' );
  add_meta_box( 'ceceppaml-meta-box', __('Page data', 'ceceppaml'), 'cml_admin_post_meta_box', 'page', 'side', 'high' );
  
  //Add metabox to custom posts
  $post_types = get_post_types( array( '_builtin' => FALSE ), 'names'); 
  $posts = array( "post", "page" );

  foreach( $post_types as $post_type ) {
    if( ! in_array( $post_type, $posts ) ) {
      add_meta_box( 'ceceppaml-meta-box', __('Post data', 'ceceppaml'), 'cml_admin_post_meta_box', $post_type, 'side', 'high' );
    }
  }
}

function cml_admin_filter_all_posts_page() {
  //Se sto nel cestino di default visualizzo tutti gli articoli :)
  $d = CMLLanguage::get_default_id();

  if( isset( $_GET[ 'post_status' ] ) && in_array( $_GET[ 'post_status' ],
                                                  array( "draft", "trash" ) ) )
  $d = 0;
  $d = isset( $_GET[ 'cml-lang' ] ) ? $_GET[ 'cml-lang' ] : $d;

  //All languages
  echo '<span class="cml-icon-wplang tipsy-s" title="' . __( 'Language:', 'ceceppaml' ) . '">';
  cml_dropdown_langs( "cml_language", $d, false, true, __('Show all languages', 'ceceppaml'), -1, 0 );
  echo '</span>';
}

function cml_admin_filter_all_posts_query( $query ) {
  global $pagenow, $wpdb;
  
  //$this->_no_filter_query is set when the function "quick_edit_box_posts" is called,
  //I have to exit from that function all WP_Query return only items in current language...
  if( isset( $GLOBALS[ '_cml_no_filter_query' ] ) ) return;

  if ( ! array_key_exists('post_type', $_GET) )
      $post_type = 'post';
  else
      $post_type = $_GET[ 'post_type' ];

  //In trash I don't filter any post
  $d = CMLLanguage::get_default_id();
  if( isset( $_GET[ 'post_status' ] ) && in_array( $_GET[ 'post_status' ], array( "draft", "trash" ) ) ) $d = 0;
  $id = array_key_exists('cml-lang', $_GET) ? intval($_GET['cml-lang']) : $d;
  
  if( is_admin() && $pagenow == "edit.php" ) {
    if($id > 0) {
      $posts = CMLPost::get_posts_by_language( $id );
      
      $query->query_vars[ 'post__in' ] = $posts;
    }
  }
  
  return $query;
}

function cml_admin_delete_extra_post_fields( $id ) {
  global $wpdb, $_cml_language_columns;

  foreach( $_cml_language_columns as $col ) {
    $sql = sprintf( "UPDATE %s SET $col = 0 WHERE $col = %d", CECEPPA_ML_RELATIONS, $id );

    $wpdb->query( $sql );
  }

  if( get_post_status( $id ) != "trash" ) {
    delete_post_meta( $id, "_cml_meta" );
  }

  //Ricreo la struttura degli articoli, questo metodo rallenterà soltanto chi scrive l'articolo... tollerabile :D
  cml_fix_rebuild_posts_info();
}

function cml_manage_posts_columns() {
  //Show flags in list for all registered post types ( so also custom posts )
  $post_types = get_post_types('','names');

  foreach ($post_types as $type ) {
    add_action( "manage_${type}_posts_custom_column", 'cml_admin_add_flag_column', 10, 2);
    add_filter( "manage_${type}_posts_columns" , 'cml_admin_add_flag_columns' );
  }
}

//Manage all posts columns
add_action( 'admin_init', 'cml_manage_posts_columns', 10 );

//Language & translation box
add_action( 'add_meta_boxes', 'cml_admin_add_meta_boxes' );

//Save language & translations info
add_action( 'edit_post', 'cml_admin_save_extra_post_fields' );
add_action( 'edit_page_form', 'cml_admin_save_extra_post_fields' );
add_action( 'publish_my_custom_post_type', 'cml_admin_save_extra_post_fields' );

//Delete
add_action('delete_post', 'cml_admin_delete_extra_post_fields' );
add_action('trash_post', 'cml_admin_delete_extra_post_fields' );
add_action('delete_page', 'cml_admin_delete_extra_post_fields' );
//add_action('untrash_post', array(&$this, 'code_optimization'));

//Filters
add_filter( 'parse_query', 'cml_admin_filter_all_posts_query' );
add_action( 'restrict_manage_posts', 'cml_admin_filter_all_posts_page' );

?>
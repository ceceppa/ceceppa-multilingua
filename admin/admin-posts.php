<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

function cml_admin_post_meta_box( $tag ) {
  global $wpdb, $pagenow, $_cml_settings;

  $langs = CMLLanguage::get_all();

  //I have clicked on "+" symbol for add translation?
  if( array_key_exists( "post-lang", $_GET) ) {
    $post_lang = intval( $_GET[ 'post-lang' ] );
  }

  if( ! isset( $post_lang ) &&
      "post-new.php" == $pagenow ) {
    $post_lang = CMLLanguage::get_default_id();
  }

  //Language of post/page
  echo "<h4>";
  _e( 'Language', 'ceceppaml' );
  echo '<span class="cml-help cml-pointer-help cml-post-help"></span>';
  echo "</h4>";
  
  //Fix "All languages" issue
  $meta_lang = get_post_meta( $tag->ID, "_cml_lang_id", true );
  if( empty( $meta_lang ) ) $meta_lang = CMLPost::get_language_id_by_id( $tag->ID, true );

  $post_lang = ( ! isset( $post_lang ) || $post_lang < 0 ) ?
    $meta_lang : $post_lang;

  cml_dropdown_langs( "post_lang", $post_lang, false, true, __( "All languages", "ceceppaml" ), "", 0 );

  //Translations
  echo "<h4>" . __( 'Translations', 'ceceppaml' ) . "</h4>";

  //Linked post?
  if( isset( $_GET[ 'link-to' ] ) ) {
    $link_id = intval( $_GET[ 'link-to' ] );
    
    /* recover category from linked id */
    $categories = wp_get_post_categories( $link_id );
    if( ! empty( $categories ) ) {
      if( CML_STORE_CATEGORY_AS == CML_CATEGORY_CREATE_NEW &&
         ! CMLLanguage::is_default( $post_lang ) ) {
          $c = array();
          foreach( $categories as $cat ) {
            $query = sprintf( "SELECT cml_translated_cat_id FROM %s WHERE cml_cat_lang_id = %d AND cml_cat_id = %d",
                                  CECEPPA_ML_CATS, $post_lang, $cat );
  
            $c[] = $wpdb->get_var( $query );
          } //endforeach;
  
          if( ! empty( $c ) ) {
            $categories = $c;
          }
        } //endif
      
      wp_set_post_categories( $tag->ID, $categories );
    } // ! empty

    /* recover tags */
    $tags = wp_get_post_tags( $link_id );
    if( ! empty( $tags ) ) {
      $ltags = array();
      foreach( $tags as $t ) {
        $ltags[] = ( CML_STORE_CATEGORY_AS == CML_CATEGORY_AS_STRING ) ?
                      $t->name :
                      CMLTranslations::get( $lang, $t->taxonomy . "_" . $t->name, "C", true );
      }

      wp_set_post_tags( $tag->ID, $ltags );
    }

    //for page get parent
    $post = get_post( $link_id );
    
    //Has translation?
    $parent_t = CMLPost::get_translation( $post_lang, $post->post_parent );
    $tag->post_parent = ( ! empty( $parent_t ) ) ? $parent_t : $post->post_parent;
    
    //Clone post meta
    _cml_clone_post_meta( $link_id, $tag->ID ); 
  } else {
    $link_id = 0;
  }

  //All posts except posts that exists in current language
  if( $post_lang > 0 ) {
    $not = CMLPost::get_posts_by_language( $post_lang );
  } else {
    $not = array();
  }

  $translations = CMLPost::get_translations( ( $link_id > 0 ) ? $link_id : $tag->ID, true );

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
    _cml_admin_post_meta_translation( $tag->post_type, $lang->id, $t_id, $tag->ID );
    echo "<a href=\"$link\" class=\"button cml-button-$bclass tipsy-s\" title=\"$msg\"></a>";
    echo " </li>";
  }
  
  echo "</ul>";

  /*
   * Override show flags settings
   * user can choose to override default show page settings for only this one
   */
    return;  //Still working on
  echo "<h4>" . __( 'Show flags', 'ceceppaml' ) . "</h4>";

  $override = get_post_meta( $tag->ID, "_cml_override_flags", true );

  $show = ( isset( $override[ 'show' ] ) ) ? $override[ 'show' ] : "default";
?>
    <div class="cml-override-flags cml-override">
      <label class="tipsy-me" title="<?php _e( "Use default 'Show flags' settings", 'ceceppaml' ) ?>">
        <input type="radio" id="cml-showflags" name="cml-showflags" value="default" <?php checked( $show, "default" ) ?>/>
        <span><?php _e( 'default', 'ceceppaml' ) ?></span>
      </label>

      <label class="tipsy-me" title="<?php _e( 'Always show flags in current page', 'ceceppaml' ) ?>">
        <input type="radio" id="cml-showflags" name="cml-showflags" value="always"  <?php checked( $show, "always" ) ?>/>
        <span><?php _e( 'always', 'ceceppaml' ) ?></span>
      </label>

      <label class="tipsy-me" title="<?php _e( "Don't show flags in this page", 'ceceppaml' ) ?>">
        <input type="radio" id="cml-showflags" name="cml-showflags" value="never"  <?php checked( $show, "never" ) ?>/>
        <span><?php _e( 'never', 'ceceppaml' ) ?></span>
      </label>

    </div>
    <div class="cml-show-always <?php echo $show ?>">
      <strong><?php _e( 'Size' ) ?></strong>

      <?php
        $size = ( isset( $override[ 'size' ] ) ) ? $override[ 'size' ] : $_cml_settings[ 'cml_option_flags_on_size' ];
        $where = ( isset( $override[ 'where' ] ) ) ? $override[ 'where' ] : $_cml_settings[ 'cml_option_flags_on_pos'];

      ?>
      <div class="cml-override-flags cml-flag-size">
        <label class="tipsy-me" title="<?php _e( "Small", 'ceceppaml' ) ?>">
          <input type="radio" id="cml-flagsize[]" name="cml-flagsize" value="small"  <?php checked( $size, CML_FLAG_SMALL ) ?>/>
          <span>
            <?php echo CMLLanguage::get_flag_img( CMLLanguage::get_default_id(), CML_FLAG_SMALL ); ?>
          </span>
        </label>
        
        <label class="tipsy-me" title="<?php _e( "Tiny", 'ceceppaml' ) ?>">
          <input type="radio" id="cml-flagsize[]" name="cml-flagsize" value="tiny"  <?php checked( $size, CML_FLAG_TINY ) ?>/>
          <span>
            <?php echo CMLLanguage::get_flag_img( CMLLanguage::get_default_id(), CML_FLAG_TINY ); ?>
          </span>
        </label>

      </div>

      <br />
      <strong><?php _e('Where:', 'ceceppaml'); ?></strong>

      <div class="cml-override-flags cml-flag-where">

        <label class="tipsy-me" title="<?php _e( "Before the title", 'ceceppaml' ) ?>">
          <input type="radio" id="cml-flagwhere" name="cml-flagwhere" value="before"  <?php checked( $where, 'before' ) ?>/>
          <span>
            <img src="<?php echo CML_PLUGIN_IMAGES_URL ?>btitle.png"/>
          </span>
        </label>


        <label class="tipsy-me" title="<?php _e( "After the title", 'ceceppaml' ) ?>">
          <input type="radio" id="cml-flagwhere" name="cml-flagwhere" value="after"  <?php checked( $where, 'after' ) ?>/>
          <span>
            <img src="<?php echo CML_PLUGIN_IMAGES_URL ?>atitle.png"/>
          </span>
        </label>


        <label class="tipsy-me" title="<?php _e( "Before content", 'ceceppaml' ) ?>">
          <input type="radio" id="cml-flagwhere" name="cml-flagwhere" value="top"  <?php checked( $where, 'top' ) ?>/>
          <span>
            <img src="<?php echo CML_PLUGIN_IMAGES_URL ?>bcontent.png"/>
          </span>
        </label>


        <label class="tipsy-me" title="<?php _e( "After content", 'ceceppaml' ) ?>">
          <input type="radio" id="cml-flagwhere" name="cml-flagwhere" value="bottom"  <?php checked( $where, 'bottom' ) ?>/>
          <span>
            <img src="<?php echo CML_PLUGIN_IMAGES_URL ?>acontent.png"/>
          </span>
        </label>

      </div>

    </div>
<?php
}

function _cml_admin_post_meta_translation( $type, $lang, $linked_id, $post_id ) {
  CMLUtils::_set( '_cml_no_filter_query', 1 );

  $args = array('numberposts' => -1, 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1,
      'post_type' => $type,
      // 'post__not_in' => CMLPost::get_posts_by_language( $lang ),
      'status' => 'publish,inherit,pending,private,future,draft');
  
  $posts = new WP_Query( $args );

  $notrans = ""; 
  $none = __( 'None', 'ceceppaml' );
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
          <i><span class="title">( $none )</span></i>
        </li>
EOT;
  while( $posts->have_posts() ) {
    $posts->next_post();

    $id = $posts->post->ID;

    $current = ( $id == $post_id ) ? "current" : "";

	$lang_id = CMLPost::get_language_id_by_id( $id );

	echo "<li cml-trans=\"$id\">";
    echo '<span class="img">';
    echo CMLLanguage::get_flag_img( $lang_id );
    echo '</span>';
    echo '<span class="title ' . $current . '">';
    echo get_the_title( $id );
    echo "</span>";
    if( ! empty( $current ) ) {
      echo '<span class="current">';
      echo "&nbsp;&nbsp;(";
      printf( __( 'current %s', 'ceceppaml' ), $type );
      echo ")";
      echo '</span>';
    }
    echo "</li>";
  }

echo <<< EOT
      </ul>
    </li>
  </ul>
EOT;

  CMLUtils::_del( '_cml_no_filter_query', 1 );
}

/* 
 * Salvo il collegamento tra i post
 */
function cml_admin_save_extra_post_fields( $term_id ) {
  global $wpdb, $pagenow;

  //This function is also called on "comment" edit, and this will cause "language relations" lost...
  if( ! isset( $_POST[ 'post_type' ] ) ) return;

  //From Wp 3.5.2 this function is called twice, but second time $_POST is empty
  if( $pagenow == "nav-menus.php" || empty( $_POST ) ) {
    return;
  }

  $post_id = is_object( $term_id ) ? $term_id->ID : $term_id;

  //no language?
  if( empty( $_POST['cml-lang'] ) ) {
    $post_lang = 0;
  } else {
    $post_lang = intval( $_POST[ 'cml-lang' ] );
  }

//  if( CML_STORE_CATEGORY_AS == CML_CATEGORY_CREATE_NEW ) {
//    cml_fix_update_post_categories();
//  }

  /*
   * Normal edit or quickedit?
   */
  if( ! isset( $_POST[ 'cml-quick' ] ) ) {
    //Normal edit
    $linkeds = array();

    foreach( CMLLanguage::get_all() as $lang ) {
      if( $lang->id == $post_lang ) continue;

      //Set language of current post
      $linkeds[ $lang->id ] = @$_POST[ 'linked_post' ][ $lang->id ];
    }

    //Override flags settings
    $override = array(
                      'show' => @$_POST[ 'cml-showflags' ],
                      'size' => @$_POST[ 'cml-flagsize' ],
                      'where' => @$_POST[ 'cml-flagwhere' ],
                    );
    update_post_meta( $post_id, "_cml_override_flags", $override );
  } else {
    $langs = CMLLanguage::get_all();

    $current = CMLPost::get_language_id_by_id( $post_id );
    foreach( $langs as $lang ) {
      if( $lang->id == $current ) continue;

      $key = "linked_$lang->cml_language_slug";
      $linkeds[ $lang->id ] = intval( @$_POST[ $key ] );
    }
  }

  CMLPost::set_translations( $post_id, $linkeds, $post_lang );

  update_post_meta( $post_id, "_cml_lang_id", $post_lang );
}

/*
 * Add flags icon to all posts list header
 */
function cml_admin_add_flag_columns( $columns ) {
  $langs = cml_get_languages( false );

  //Non sono riuscito a trovare un altro modo per ridimensionare la larghezza del th...
  wp_enqueue_style('ceceppaml-style-all-posts', CML_PLUGIN_URL . 'css/all_posts.php?langs=' . count( $langs ) );

  $clang = isset( $_GET['cml-lang'] ) ? intval ( $_GET['cml-lang'] ) : CMLLanguage::get_default_id();
  $img = '<span class="cml_enable_filter">' . __( 'Enable language filtering', 'ceceppaml' ) . '</span>';
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
  $linked = CMLPost::get_translations( $id, true );

  $GLOBALS[ '_cml_no_translate_home_url' ] = 1;

  foreach( $langs as $lang ) {
    $link = isset( $linked[ $lang->cml_language_slug ] ) ? $linked[ $lang->cml_language_slug ] : 0;

    if( $link > 0 ) {
      $title = "<br />" . get_the_title($link);
      echo '<a href="' . get_edit_post_link($link) . '">';
      echo '    <img class="tipsy-me" src="' . cml_get_flag_by_lang_id($lang->id, "tiny") . '" title="' . __('Edit post: ', 'ceceppaml') . $title . '"/>';
      echo '</a>';
      
    } else {
      echo '<a href="' . get_bloginfo( "wpurl" ) . '/wp-admin/post-new.php?post_type=' . $post_type . '&link-to=' . $id . '&post-lang=' . $lang->id . '">';
      echo '    <img class="add tipsy-me" src="' . CML_PLUGIN_URL . 'images/edit.png" title="' . __( 'Translate in:', 'ceceppaml' ) . ' ' . $lang->cml_language . '" />';
      echo '</a>';
    }
  }
  
  unset( $GLOBALS[ '_cml_no_translate_home_url' ] );
}

/*
 * If post language is default one I show default "Tags" metabox,
 * otherwise I need to hide it and let the user to translate existing tags.
 */
function cml_admin_tags_meta_box( $post ) {
  $lang = CMLLanguage::get_id_by_post_id( $post->ID );

  $hide = ( 0 == $lang || CMLLanguage::is_default( $lang ) );

  echo '<div class="cml-tagsdiv ' . ( ! $hide ? "" : "acml-hidden" ) . '">';
  _e( 'This post is a translations, you have to translate existing tag instead of add new one.', 'ceceppaml' );
  echo '&nbsp;<a href="http://www.alessandrosenese.eu/en/ceceppa-multilingua/translate-categories-or-tags" target="_blank">';
  _e( 'Help' );
  echo '</a>';
  echo '</div><br />';

  _e( 'Search existing tag:', 'ceceppaml' );
  echo '<input type="search" name="search" value="" />';
  echo '<a href="#" class="button cml-button-add tipsy-s" title="' . __( 'Add new tag', 'ceceppaml' ) . '"></a>';

  echo '<ul class="cml-tagslist tagchecklist">';
  //Instead of create items via javascript I clone first <li> :)
  _cml_admin_add_tag( 'cml-hidden cml-first' );
  echo '</ul>';
}

function _cml_admin_add_tag( $class = "" ) {
  $translate = __( 'Confirm translation', 'ceceppaml' );
  $click = __( 'Click to translate', 'ceceppaml' );

  $url = CML_PLUGIN_IMAGES_URL;
echo <<< EOT
    <li class="$class">
      <input type="hidden" name="cml-tag-id[]" class="field" value="" />
      <span>
        <a id="post_tag-check-num-0" class="ntdelbutton">X</a>
      </span>
      &nbsp;
      <input type="text" name="cml-trans[]" class="cml-input cml-hidden" value="" />
      <span class="title tipsy-s" title="$click"></span>
      <a href="javascript:void(0)" class="button button-primary button-mini button-confirm" title="$translate" style="display: none">
        <img src="{$url}confirm.png" />
      </a>
    </li>
EOT;
}

function cml_admin_add_meta_boxes() {
  //Add metabox to custom posts
  $post_types = get_post_types( array( '_builtin' => FALSE ), 'names'); 
  $post_types[] = "post";
  $post_types[] = "page";

  // remove_meta_box('tagsdiv-post_tag','post','side');
  // add_meta_box( 'ceceppaml-tags-meta-box', __('Tags', 'ceceppaml'), 'cml_admin_tags_meta_box', 'post', 'side', 'core' );
    $post_types = apply_filters( 'cml_manage_post_types', $post_types );

    $post_id = intval( $_GET[ 'post' ] );
    $type = get_post_type( $post_id );
//    $type = ( ! isset( $_GET[ 'post_type' ] ) ) ? "post" : $_GET[ 'post_type' ];

    /*
     * To restore language filtering now have to click on "Aplly" after checked it.
     * But as this is not too clear I'll check the status of this option for the current page
     */
    $hidden = get_user_option( 'manageedit-' . $type . 'columnshidden' );

    //Ignored post list
    $list = get_option( "_cml_ignore_post_type", array() );

    if( is_array( $hidden ) && is_array( $post_types ) ) {
        //Remove from ignore list
        if( ! in_array( $type, $post_types ) && ! in_array( 'cml_flags', $hidden ) ) {
            $index = array_search( $type, $list );
            unset( $list[ $index ] );

            update_option( "_cml_ignore_post_type", $list );

            $post_types[] = $type;
        }

        //Add to ignore list
        if( in_array( $type, $post_types ) && in_array( 'cml_flags', $hidden ) ) {
            $list[] = $post_type;

            update_option( "_cml_ignore_post_type", $list );

            $index = array_search( $type, $post_types );
            unset( $post_types[ $index ] );
        }
    }

  foreach( $post_types as $post_type ) {
    //Exclude "post" and "page"
    add_meta_box( 'ceceppaml-meta-box', __('Post data', 'ceceppaml'), 'cml_admin_post_meta_box', $post_type, 'side', 'high' );
  }
}

function cml_admin_filter_all_posts_page() {
  $post_types = get_post_types('','names');
  $post_types = apply_filters( 'cml_manage_post_types', $post_types );

  if( isset( $_GET[ 'post_type' ] ) &&
     ! in_array( $_GET[ 'post_type' ], $post_types ) ) return;

  //In the bin page I have to show all the posts :)
  $d = CMLLanguage::get_default_id();

  if( isset( $_GET[ 'post_status' ] ) && in_array( $_GET[ 'post_status' ],
                                                  array( "draft", "trash" ) ) )
  $d = 0;
  $d = isset( $_GET[ 'cml-lang' ] ) ? $_GET[ 'cml-lang' ] : $d;
   
  //Check if language filtering is disabled for this post type
  $is_disabled = get_hidden_columns( get_current_screen() );

  //Ignored post list
  $list = get_option( "_cml_ignore_post_type", array() );

  //Add current post type to "ignore" list
  $post_type = ( isset( $_GET[ 'post_type' ] ) ) ? $_GET[ 'post_type' ] : "post";
  if( in_array( "cml_flags", $is_disabled ) ) {
      if( ! in_array( $post_type, $list ) ) {
          $list[] = $post_type;
          
          update_option( "_cml_ignore_post_type", $list );
      }

      return;
  } else {
      //Remove the current post from "ignore list"
      $search = array_search( $post_type, $list );
      if( $search !== FALSE ) {
          unset( $list[ $search ] );

          update_option( "_cml_ignore_post_type", $list );
      }

      //All languages
      echo '<span class="cml-icon-wplang tipsy-s" title="' . __( 'Language:', 'ceceppaml' ) . '">';
      cml_dropdown_langs( "cml_language", $d, false, true, __('Show all languages', 'ceceppaml'), -1, 0 );
      echo '</span>';
  }
}

function cml_admin_filter_all_posts_query( $query ) {
  global $pagenow, $wpdb;
  
  //$this->_no_filter_query is set when the function "quick_edit_box_posts" is called,
  //I have to exit from that function or all WP_Query return only items in current language...
  if( null !== CMLUtils::_get( '_cml_no_filter_query' ) ) return $query;

  if ( ! array_key_exists('post_type', $_GET) )
      $post_type = 'post';
  else
      $post_type = $_GET[ 'post_type' ];

  $post_types = get_post_types( array( '_builtin' => TRUE ), 'names'); 
  $post_types = apply_filters( 'cml_manage_post_types', $post_types );
  if( ! in_array( $post_type, $post_types ) ) return $query;

  //In trash I don't filter any post
  $d = CMLLanguage::get_default_id();
  if( isset( $_GET[ 'post_status' ] ) && in_array( $_GET[ 'post_status' ], array( "draft", "trash" ) ) ) $d = 0;
  $id = array_key_exists('cml-lang', $_GET) ? intval($_GET['cml-lang']) : $d;
  
  if( is_admin() && $pagenow == "edit.php" ) {
    //Show language filtering feature
    if( isset( $_GET[ 'hide-filtering-notice' ] ) ) 
        update_option( '_cml_hide_filtering_notice', 1 );

    if( ! get_option( "_cml_hide_filtering_notice", 0 ) ) {
        add_action( 'admin_notices', '_cml_show_filtering_notice' );
    }

    if($id > 0) {
      $posts = CMLPost::get_posts_by_language( $id );

      $query->query_vars[ 'post__in' ] = $posts;
    }
  }

  return $query;
}

function _cml_show_filtering_notice() {
    $msg = __( 'You can easily disable/enable language filtering for current post type, ', 'ceceppaml' );
    $msg .= __( 'using the "Enable language filtering" in the "Screen Option" section', 'ceceppaml' );
    $close = __( 'Close', 'ceceppaml' );
    
    $link = add_query_arg( array( "hide-filtering-notice" => 1 ) );

echo <<< NOTICE
    <div class="updated cml-notice">
        <p>$msg</p>
        <p class="submit">
            <a class="button button-primary" style="float: right" href="$link">
                $close
            </a>
        </p>
    </div>
NOTICE;
}

function cml_admin_delete_extra_post_fields( $id ) {
  global $wpdb, $_cml_language_columns;

  //All translations
  $translations = CMLPost::get_translations( $id );

  foreach( $_cml_language_columns as $col ) {
    $sql = sprintf( "UPDATE %s SET $col = 0 WHERE $col = %d", CECEPPA_ML_RELATIONS, $id );

    $wpdb->query( $sql );
  }

  if( get_post_status( $id ) != "trash" ) {
    delete_post_meta( $id, "_cml_meta" );
  }

  if( ! empty( $translations[ 'linked' ] ) ) {
    $l = end( $translations[ 'linked' ] );

    //Rebuild meta
    CMLPost::get_translations( $l, true );
  }

  //Ricreo la struttura degli articoli, questo metodo rallenterà soltanto chi scrive l'articolo... tollerabile :D
  cml_fix_rebuild_posts_info();
}

/*
 * When user start new post I clone meta from "original" to clone one.
 */
function _cml_clone_post_meta( $from, $new_post_id ) {
  global $wpdb;

  /*
   * duplicate all post meta
   */
  $post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$from" );

  if ( count( $post_meta_infos ) != 0 ) {
      $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
      foreach ($post_meta_infos as $meta_info) {
          $meta_key = $meta_info->meta_key;
          $meta_value = addslashes($meta_info->meta_value);
          $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
      }
      $sql_query.= implode(" UNION ALL ", $sql_query_sel);
      $wpdb->query($sql_query);
  }
}

function cml_manage_posts_columns() {
  //Show flags in list for all registered post types ( so also custom posts )
  $all = get_post_types('','names');
  $post_types = apply_filters( 'cml_manage_post_types', $all );

  /*
   * In 1.4.33 I added "Enabled filter language" to easily allow user to disable language filtering
   * on it own posts.
   * I need to cycle $all because I need to show up the filtering option :)
   */
  foreach ($all as $type ) {
    add_action( "manage_${type}_posts_custom_column", 'cml_admin_add_flag_column', 10, 2);
    add_filter( "manage_${type}_posts_columns" , 'cml_admin_add_flag_columns' );
  }
}

function cml_disable_filtering( $types ) {
  static $list = null;
    
  //Ignored post types
  if( $list == null ) {
    $list = get_option( "_cml_ignore_post_type", array() );
  }

  return array_diff( $types, $list );
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

//Disable language filter for
add_filter( 'cml_manage_post_types', 'cml_disable_filtering' );

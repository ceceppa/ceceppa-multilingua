<?php
/*
 * Permetto di modificare lingua e articolo collegato nella finestra quickedit
 */
function cml_admin_quick_edit_box( $column_name, $post_type ) {
  if($column_name != 'cml_flags') return;
  ?>
  <fieldset class="inline-edit-col-left">
  <input type="hidden" name="cml-quick" value="1" />
  <div class="inline-edit-col">
    <label>
      <span class="title">Language:</span>
      <span class="input-text-wrap">
        <?php wp_nonce_field('cml_edit_post','cml_nonce_edit_field'); ?>
        <select id="cml-lang" name="cml-lang" class="cml-quick-lang" >
          <option value="0"><?php _e( 'All languages', 'ceceppaml' ) ?></option>

          <?php foreach( CMLLanguage::get_all() as $lang ) : ?>
            <option value="<?php echo $lang->id ?>"><?php echo $lang->cml_language ?></option>
          <?php endforeach; ?>

        </select>
      </span>
    </label>

    <?php
      cml_admin_quick_edit_box_posts( $post_type );
    ?>
  </div>
  </fieldset>
  <?php
}

function cml_admin_quick_edit_box_posts( $post_type ) {
  $langs = CMLLanguage::get_all();

  $d = CMLLanguage::get_default_id();
  if( isset( $_GET[ 'post_status' ] ) && in_array( $_GET[ 'post_status' ], array( "draft", "trash" ) ) )
    $d = 0;
  $id = array_key_exists( 'cml_language', $_GET ) ? intval( $_GET[ 'cml_language' ] ) : $d;

  $args = array('numberposts' => -1, 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1,
        'post_type' => $post_type,
        //'post__not_in' => @$ids[ $id ],
        'status' => 'publish,inherit,pending,private,future,draft');

  /*
   * ask to cml_admin_filter_all_posts_query() function to
   * ignore "filter"
   */
  CMLUtils::_set( '_cml_no_filter_query', 1 );
  $posts = new WP_Query( $args );

  foreach( $langs as $lang ) {
  ?>
    <label class="cml-quick-item cml-quick-<?php echo $lang->id ?>">
      <span class="title"><?php echo $lang->cml_language ?>:</span>
      <span class="input-text-wrap">
        <?php wp_nonce_field( 'cml_linked_post_<?php echo $lang->id ?>','cml_nonce_edit_field'); ?>
        <select name="linked_<?php echo $lang->cml_language_slug ?>" class="cml_linked_post">
      <option></option>
    <?php
      while( $posts->have_posts() ) {
        $posts->next_post();

        $post = $posts->post;

        //Seleziono il post corretto da quickedit.php
        $lang_id = CMLPost::get_language_id_by_id( $post->ID );
        $slug = CMLLanguage::get_slug( $lang_id );
        echo "<option value=\"$post->ID\">$post->post_title ( $slug )</option>";
      }

      wp_reset_postdata();
    ?>
      </select>
      <a name="none_<?php echo $lang->cml_language_slug ?>" class="button cml-quickedit-none" href="javascript:unsetTranslation( '<?php echo $lang->cml_language_slug ?>' )" title="<?php _e( 'Remove translation', 'ceceppaml' ) ?>">
        <img src="<?php echo CML_PLUGIN_IMAGES_URL ?>none.png" border="0" />
      </a>
      </span>
    </label>
  <?php
  } //endforeach;

  CMLUtils::_del( '_cml_no_filter_query' );
}

function cml_quick_edit_javascript() {
    global $current_screen;
//     if (($current_screen->id != 'edit-post') || ($current_screen->post_type != 'post')) return;

    ?>
    <script type="text/javascript">
    <!--
    function unsetTranslation( lang ) {
      // console.log( 'select[name="linked_' + lang + '"]' );
      jQuery( 'select[name="linked_' + lang + '"]' ).find( 'option' ).first().attr( 'selected', 'selected' )
    }

    function set_inline_widget_set(widgetId, lang, keys, values, the_post_id) {
        // revert Quick Edit menu so that it refreshes properly
        inlineEditPost.revert();
        var $select = jQuery( 'select#cml-lang' );

        // check option manually
        $select.children().map(function() {
          var $this = jQuery(this);
          var val = $this.val();

          if(val == lang) {
            $this.attr('selected', true);
          } else {
            $this.removeAttr('selected', true);
          }
        });

        //Json
        $keys = keys.split( "," );
        $values = values.split( "," );

        $selects = jQuery( 'select.cml_linked_post' );
        $selects.each( function() {
          $select = jQuery( this );

          //Remove selecte attributes
          $select.find( 'option' ).removeAttr( 'selected', true );

          //Recupero il nome della lingua
          var name = $select.attr( 'name' );
          var key_id = $keys.indexOf( name.replace( "linked_", "" ) );
          if( key_id >= 0 ) {
            var post_id = $values[ key_id ];

            // check option manually
            $select.children().map(function() {
              var $this = jQuery(this);
              var val = $this.val();

              if ( val == the_post_id ) {
                $this.hide();
              }

              if( val == post_id ) {
                $this.attr( 'selected', true );
              } else {
                $this.removeAttr( 'selected', true );
              }
            });
          }
        });

        //Hide selected of current language
        jQuery( 'select#cml-lang' ).trigger( 'change' );
      }
    //-->
    </script>
    <?php
}

function cml_expand_quick_edit_link($actions, $post) {
    global $current_screen;

//     if (($current_screen->id != 'edit-post') || ($current_screen->post_type != 'post')) return $actions;

    $lang = CMLPost::get_language_id_by_id( $post->ID, true );

    //json encode doesn't works: "Uncaught SyntaxError: Unexpected token ILLEGAL " -.-"
//     $linked = json_encode( $posts  );
    $posts = cml_get_linked_posts( $post->ID );
    $keys = "";
    $vals = "";

    if( is_array( $posts ) && isset( $posts[ 'indexes' ] ) ) {
      $posts = $posts[ 'indexes' ];

      $keys = join(",", array_keys( $posts ) );
      $vals = join(",", array_values( $posts ) );
    }

    $widget_id = get_post_meta( $post->ID, 'post_widget', TRUE);
    $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';
    $actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '" ';
    $actions['inline hide-if-no-js'] .= " onclick=\"set_inline_widget_set('{$widget_id}', '{$lang}', '{$keys}', '{$vals}', '{$post->ID}')\">";
    $actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit' );
    $actions['inline hide-if-no-js'] .= '</a>';

    return $actions;
}

// Add to our admin_init function
add_filter( 'post_row_actions', 'cml_expand_quick_edit_link', 10, 2 );
add_filter( 'page_row_actions', 'cml_expand_quick_edit_link', 10, 2 );

/* http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu */
//Funzioni necessarie per modificare la lingua nel quick edit box
add_action( 'admin_footer', 'cml_quick_edit_javascript' );

//Quickedit
add_action('quick_edit_custom_box',  'cml_admin_quick_edit_box', 10, 2);

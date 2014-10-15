<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

echo '<div class="cml-box-shadow"></div>';

/*
 * Translation not available
 */
function cml_admin_options_flag_not_available() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

  $redirect = $_cml_settings[ 'cml_option_redirect' ];
  ?>
  <div id="minor-publishing">
    <span class="cml-indent">
      <?php _e( "If translation of current post/page doesn't exists in flag language:", 'ceceppaml' ); ?>
    </span>
	</label>
	<label>
    <ul class="cml-options">
      <li>
        <label>
          <input type="radio" name="force" value="1" <?php checked( $_cml_settings[ "cml_force_languge" ], 1 ) ?> />
          <?php _e( 'Switch always to flag language', 'ceceppaml' ) ?>
        </label>
      </li>
      <li>
        <label>
          <input type="radio" name="force" value="2" <?php checked( $_cml_settings[ "cml_force_languge" ], 2 ) ?> />
          <?php _e( 'Go to homepage', 'ceceppaml' ) ?>
        </label>
      </li>
    </ul>
  </div>
  
  <div id="major-publishing-actions" class="cml-description">
    <?php _e( "Set the behavior of the flags when current page doesn't exists in flag language", 'ceceppaml' ); ?><br />
    <dl class="cml-dl-list">
      <dt>
        <?php _e( "Switch always to flag language", "ceceppaml" ) ?>
      </dt>
      
      <dd>
        <?php _e( "The plugin will add language slug ( ?lang=## ) to url when translation doesn't exists in flag language", "ceceppaml" ) ?>
      </dd>
    </dl>
  </div>

<?php
}

/*
 * Show flags
 */
function cml_admin_options_flags_show() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];

  $redirect = $_cml_settings[ 'cml_option_redirect' ];
  ?>
  <div id="minor-publishing">
    <div class="cml-inline cml-inline-1">
      <span class="cml-indent">
        <?php _e('Show flags on:', 'ceceppaml') ?>
      </span>

      <ul class="cml-options">
        <li>
          <ul class="cml-options">
            <li>
              <div class="cml-checkbox">
                <input type="checkbox" id="flags-on-posts" name="flags-on-posts" value="1" <?php checked( $_cml_settings[ 'cml_option_flags_on_post' ] , 1 ) ?> />
                <label for="flags-on-posts"><span>||</span></label>
              </div>
              <label for="flags-on-posts"><?php _e( 'Posts', 'ceceppaml' ) ?></label>
            </li>
            <li>
              <div class="cml-checkbox">
                <input type="checkbox" id="flags-on-pages" name="flags-on-pages" value="1" <?php checked( $_cml_settings[ 'cml_option_flags_on_page' ], 1 ) ?> />
                <label for="flags-on-pages"><span>||</span></label>
              </div>
              <label for="flags-on-pages"><?php _e( 'Pages', 'ceceppaml' ) ?></label>
            </li>
            <li>
              <div class="cml-checkbox">
                <input type="checkbox" id="flags-on-custom" name="flags-on-custom" value="1" <?php checked( $_cml_settings[ 'cml_option_flags_on_custom_type' ], 1 ) ?> />
                <label for="flags-on-custom"><span>||</span></label>
              </div>
              <label for="flags-on-custom"><?php _e( 'Custom posts', 'ceceppaml' ) ?></label>
            </li>

            <li>
              <div class="cml-checkbox">
                <input type="checkbox" id="flags-on-loop" name="flags-on-loop" value="1" <?php checked( $_cml_settings[ 'cml_option_flags_on_the_loop' ], 1 ) ?> />
                <label for="flags-on-loop"><span>||</span></label>
              </div>
              <label for="flags-on-loop"><?php _e( 'Loop', 'ceceppaml' ) ?></label>
            </li>

          </ul>
        </li>
        <li>
          <strong class="cml-indent">
            <?php _e( 'Where:', 'ceceppaml' ) ?>
          </strong>
          <ul>
            <li>
              <label>
                <input type="radio" name="flags_on_pos" value="before" id="flags_on_top" <?php checked( $_cml_settings[ 'cml_option_flags_on_pos' ], 'before' ) ?> />
                <?php _e('Before the title', 'ceceppaml') ?>
              </label>
            </li>
            <li>
              <label>
                <input type="radio" name="flags_on_pos" value="after" id="flags_on_top" <?php checked( $_cml_settings ['cml_option_flags_on_pos' ], 'after') ?> />
                <?php _e('After the title', 'ceceppaml') ?>
              </label>
            </li>
            <li>
              <label>
                <input type="radio" name="flags_on_pos" value="top" id="flags_on_top" <?php checked( $_cml_settings [ 'cml_option_flags_on_pos' ], 'top' ) ?> />
                <?php _e('Before post/page content', 'ceceppaml') ?>
              </label>
            </li>
            <li>
              <input type="radio" name="flags_on_pos" value="bottom" id="flags_on_bottom" <?php checked( $_cml_settings[ 'cml_option_flags_on_pos'],  'bottom' ) ?> />
              <label for="flags_on_bottom"><?php _e('After post/page content', 'ceceppaml') ?></label><br>
            </li>
          </ul>
        </li>
      </ul>
    </div>
    <div class="cml-inline cml-inline-2">
      <?php cml_options_flags_size_box( "flag-size", 'cml_option_flags_on_size' ); ?>
      
      <br />
      <strong class="cml-indent">
        <?php _e( 'When:', 'ceceppaml' ) ?>
      </strong>

      <div class="cml-checkbox">
        <input type="checkbox" id="flags-translated-only" name="flags-translated-only" value="1" <?php checked( $_cml_settings[ 'cml_options_flags_on_translations' ] , 1 ) ?> />
        <label for="flags-translated-only"><span>||</span></label>
      </div>
      <label for="flags-translated-only"><?php _e( 'Show flags only on translated page.', 'ceceppaml' ) ?></label>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Choose where show flags', 'ceceppaml' ); ?>.

    Output:<br />
<pre>
  &lt;ul class="cml_flags cml_flags_on_top"&gt;
    &lt;li class="current">Current language&lt;/li&gt;
    ...
  &lt;/ul&gt;
</pre>
    
  </div>
<?php
}

/*
 *  Add float div to website
 */
function cml_admin_options_flags_float() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];

  $redirect = $_cml_settings[ 'cml_option_redirect' ];
  ?>
  <div id="minor-publishing">
    <div>
      <span class="cml-indent">
        <?php _e(' Custom css:', 'ceceppaml') ?>
      </span>
      <br />
      <textarea class="cml-custom-css" name="custom-css" rows="10">
<?php echo get_option( 'cml_float_css', file_get_contents( CML_PLUGIN_PATH . "css/float.css" ) ) ?>
      </textarea>
    </div>
    <br />
    <div class="cml-inline cml-inline-1">
      <strong class="cml-indent">
        <?php _e(' Display items as:', 'ceceppaml') ?>
      </strong>
      <?php cml_options_flags_display_as( "float-as", "cml_show_float_items_as" ) ?>
    </div>
    <div class="cml-inline cml-inline-2 cml-inline-nomargin">
      <?php cml_options_flags_size_box( "float-size", 'cml_show_float_items_size' ); ?>
    </div>

    <?php
     _cml_options_style_box( "float-style", "cml_show_float_items_style" );
    ?>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Add a div, with #flying-flags, after the footer of the page.', 'ceceppaml' ); ?><br />
    <?php _e( 'Use the textarea for customize the style.', 'ceceppaml' ); ?>
  </div>
<?php
}

/*
 *  Append flag to html element
 */
function cml_admin_options_flags_append() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];
  ?>
  <div id="minor-publishing">
    <div>
      <strong class="cml-indent">
        <?php _e('Id or class of element to add flags:', 'ceceppaml') ?>
      </strong>
      <br />
      <input type="text" name="id-class" id="id-class" value="<?php echo get_option("cml_append_flags_to") ?>" />
	  <i>( <?php _e( "use # for id, or . for class", 'ceceppaml' ) ?> )</i>
    </div>
    <br />
    <div class="cml-inline cml-inline-1">
      <strong class="cml-indent">
        <?php _e(' Display items as:', 'ceceppaml') ?>
      </strong>
      <?php cml_options_flags_display_as( "show-items-as", "cml_show_items_as" ) ?>
    </div>
    <div class="cml-inline cml-inline-2 cml-inline-nomargin">
      <?php cml_options_flags_size_box( "item-as-size", 'cml_show_items_size' ); ?>
    </div>
    
    <?php
     _cml_options_style_box( "html-style", "cml_show_html_items_style" );
    ?>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Create a div with id #cml-lang, and append it to desired element throught append method of jQuery', 'ceceppaml' ); ?>
  </div>
<?php
}

/*
 *  Append flag to html element
 */
function cml_admin_options_flags_menu() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];

  ?>
  <div id="minor-publishing">
    <div>
      <div class="cml-inline cml-inline-1">
        <strong class="cml-indent">
          <?php _e( 'Style', 'ceceppaml' ) ?>
        </strong>
        <ul>
          <li>
            <label>
              <input type="radio" name="add-as" id="add-as" value="1" <?php checked( $_cml_settings[ "cml_add_items_as" ], 1) ?> />
              <?php _e( 'Add an item for each language', 'ceceppaml' ) ?>
            </label>
          </li>
          <li>
            <label>
              <input type="radio" name="add-as" id="add-as" value="2" <?php checked( $_cml_settings[ "cml_add_items_as" ], 2) ?> />
              <?php _e( 'Add items in submenu', 'ceceppaml' ) ?>
            </label>
          </li>
        </ul>
      </div>
      <div class="cml-inline cml-inline-2 cml-inline-nomargin">
        <strong class="cml-indent">
          <?php _e( 'Add to', 'ceceppaml' ) ?>
        </strong>
        <ul class="cml-options">
	    <?php
	      $locations = get_nav_menu_locations();

	      $menu = array();
	      $sel = get_option( 'cml_add_items_to', array() );

	      foreach( $locations as $key => $location ) {
            if( FALSE !== strpos( $key, "cml_" ) ) continue;

            $checked = checked( in_array( $key, $sel ), 1, false );
echo <<< EOT
          <li>
            <div class="cml-checkbox cml-js-checkbox">
              <input type="checkbox" id="cml_add_items_to[]" name="cml_add_items_to[]" value="$key" $checked />
              <label for="menu-$location"><span>||</span></label>
            </div>
            <label for="menu-$location">$key</label>
          </li>
EOT;
    		//echo '<option value="' . $key . '" ' . selected( $key, $sel ) . '>' . $key . '</option>';
	      }
	    ?>
        </ul>
      </div>
    </div>
    <div class="cml-inline cml-inline-1">
      <strong class="cml-indent">
        <?php _e(' Display items as:', 'ceceppaml') ?>
      </strong>
      <?php cml_options_flags_display_as( "show-as", "cml_show_in_menu_as" ) ?>
    </div>
    <div class="cml-inline cml-inline-2 cml-inline-nomargin">
      <?php cml_options_flags_size_box( "submenu-size", 'cml_show_in_menu_size' ); ?>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'You can add flags to menu throught <b>Aspect -> Menu page</b>, or enabling this option.', 'ceceppaml' ); ?><br />
    <?php _e( 'Use this box for also customize the style of items added throught <b>Aspect -> Menu page</b>', 'ceceppaml' ); ?>
  </div>
<?php
}

function cml_options_flags_display_as( $name, $option ) {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];
?>
      <ul class="cml-options">
        <li>
          <label>
              <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="1" <?php checked( $_cml_settings[ $option ], 1 ) ?> />
              <?php _e('Flag + name', 'ceceppaml') ?>
          </label>
        </li>
        <li>
          <label>
              <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="2" <?php checked( $_cml_settings[ $option ], 2 ) ?> />
              <?php _e('Name only', 'ceceppaml') ?>
          </label>
        </li>
        <li>
          <label>
              <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="3" <?php checked( $_cml_settings[ $option ], 3 ) ?> />
              <?php _e('Flag only', 'ceceppaml') ?>
          </label>
	    </li>
	    <li>
          <label>
              <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="5" <?php checked( $_cml_settings[ $option ], 5) ?> />
              <?php _e('Flag + Language slug', 'ceceppaml') ?>
          </label>
	    </li>
	    <li>
          <label>
              <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="4" <?php checked( $_cml_settings[ $option ], 4) ?> />
              <?php _e('Language slug', 'ceceppaml') ?>
          </label>
	    </li>
      </ul>
<?php
}

function cml_options_flags_size_box( $name, $option ) {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];
  
  $lang = CMLLanguage::get_default();
?>
      <strong><?php _e('Flags size:', 'ceceppaml'); ?></strong>
      <ul class="cml-options">
        <li>
          <label>
            <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="<?php echo CML_FLAG_SMALL ?>" <?php checked( $_cml_settings[ $option ], CML_FLAG_SMALL ); ?> />
            <?php echo CMLLanguage::get_flag_img( $lang->id, CML_FLAG_SMALL ); ?>
            <?php _e('Small', 'ceceppaml') ?> (32x23)
          </label>
        </li>
        <li>
          <label>
            <input type="radio" id="<?php echo $name ?>" name="<?php echo $name ?>" value="<?php echo CML_FLAG_TINY ?>" <?php checked( $_cml_settings[ $option ], CML_FLAG_TINY ); ?> />
            <?php echo CMLLanguage::get_flag_img( $lang->id, CML_FLAG_TINY ); ?>
            <?php _e('Tiny', 'ceceppaml') ?> (16x11)
          </label>
        </li>
      </ul>
<?php
}

function _cml_options_style_box( $id, $key ) {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];
?>
    <div>
      <strong class="cml-indent">
        <?php _e(' Style:', 'ceceppaml') ?>
      </strong>
      <ul>
        <li class="cml-inline-1">
          <label>
            <input type="radio" name="<?php echo $id ?>" id="<?php echo $id ?>" value="1" <?php checked( $_cml_settings[ $key ], 1 ) ?> />
            <?php _e( 'List', 'ceceppaml' ); ?>
            <div class="cml-ex-indent">
              <ul>
              <?php foreach( CMLLanguage::get_all() as $lang ) : ?>
                <li>
                  <?php echo CMLLanguage::get_flag_img( $lang->id ) ?>
                  <span><?php echo $lang->cml_language ?></span>
                </li>
              <?php endforeach; ?>
            </div>
          </label>
        </li>
        <li class="cml-inline-2">
          <label>
            <input type="radio" name="<?php echo $id ?>" id="<?php echo $id ?>" value="2" <?php checked( $_cml_settings[ $key ], 2 ) ?> />
            <?php _e( 'Combo', 'ceceppaml' ); ?>
            <div class="cml-ex-indent">
              <?php cml_dropdown_langs( "post_lang", null ) ?>
            </div>
          </label>
        </li>
      </ul>

    </div>
<?php
}

$help = __( 'Show/Hide help', 'ceceppaml' );

add_meta_box( 'cml-box-options-not', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Translation not available:', 'ceceppaml' ) . "<span class=\"cml-help cml-help-wp cml-first-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_flag_not_available', 'cml_box_options' );
add_meta_box( 'cml-box-options-show', '<span class="cml-icon cml-icon-wplang "></span>' . __( 'Show flags:', 'ceceppaml' ) . "<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_flags_show', 'cml_box_options' );

$check = cml_utils_create_checkbox( __( 'Add float div to website:', 'ceceppaml' ),
                                   "add-div", "float-div", "cml_add_float_div", true );
add_meta_box( 'cml-box-options-float', $check . "<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_flags_float', 'cml_box_options' );

$check = cml_utils_create_checkbox( __( 'Append flag to html element:', 'ceceppaml' ),
                                   "add-html", "append-flags", "cml_append_flags", true );
add_meta_box( 'cml-box-options-html', $check . "<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_flags_append', 'cml_box_options' );

$check = cml_utils_create_checkbox( __( 'Add flags to menu:', 'ceceppaml' ),
                                   "add-to-menu", "to-menu", "cml_add_flags_to_menu", true );
add_meta_box( 'cml-box-options-menu', $check . "<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_flags_menu', 'cml_box_options' );
?>

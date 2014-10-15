<?php
/*  Copyright 2013  Alessandro Senese (email : senesealessandro@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

echo '<div class="cml-box-shadow"></div>';

function cml_admin_options_filter_posts() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

  $mode = $_cml_settings[ 'cml_option_filter_posts' ];
  ?>
  <div id="minor-publishing">
    <ul class="cml-options">
      <li>
        <label>
          <input id="filter-posts" type="radio" value="<?php echo FILTER_BY_LANGUAGE ?>" name="filter-posts" <?php checked( $mode, FILTER_BY_LANGUAGE ) ?> />
          <?php _e('Show only posts in current language', 'ceceppaml') ?>
        </label>
      </li>
      <li>
        <label>
          <input id="filter-posts" type="radio" value="<?php echo FILTER_HIDE_TRANSLATION ?>" name="filter-posts" <?php checked( $mode, FILTER_HIDE_TRANSLATION ) ?> />
          <?php _e('Show all posts, but hide their translation', 'ceceppaml') ?>
        </label>
      </li>
      <li>
        <label>
          <input id="filter-posts" type="radio" value="<?php echo FILTER_HIDE_EMPTY ?>" name="filter-posts" <?php checked( $mode, FILTER_HIDE_EMPTY ) ?> />
          <?php _e('Hide empty translations of posts and show in default language', 'ceceppaml') ?>
        </label>
      </li>

      <li>
        <label>
          <input id="filter-posts" type="radio" value="<?php echo FILTER_NONE ?>" name="filter-posts" <?php checked( $mode, FILTER_NONE ) ?> />
          <?php _e("Don't filter", 'ceceppaml') ?>
        </label>
      </li>

    </ul>
  </div>
  
  <div id="major-publishing-actions" class="cml-description <?php echo ( isset( $_GET[ 'wstep' ] ) ) ? "active" : "" ?>">
    <?php _e( 'Choose which posts show', 'ceceppaml' ); ?>
    <dl class="cml-dl-list">
      <dt>
        <?php _e('Show only posts in current language', 'ceceppaml') ?>
      </dt>
        <dd>
          <?php _e('Show only the posts that exists in current language', 'ceceppaml') ?>
        </dd>

      <dt>
        <?php _e('Show all posts, but hide their translation', 'ceceppaml') ?>
      </dt>
        <dd>
          <?php _e('Show also posts that has no translation in current language.', 'ceceppaml') ?><br />
        </dd>

      <dt>
        <?php _e('Hide empty translations of posts and show in default language', 'ceceppaml') ?>
      </dt>
        <dd>
          <?php _e( "If a post doesn't have a translation in current language, post in \"default language\" will be shown", 'ceceppaml') ?>
        </dd>


      <dt>
        <?php _e("Don't filter", 'ceceppaml') ?>
      </dt>
        <dd>
          <?php _e( "Don't filter wordpress queries, useful for one page themes", 'ceceppaml') ?>
        </dd>

    </dl>
  </div>
  <?php
}

function cml_admin_options_filter_widgets() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

?>
  <div id="minor-publishing">
    <span class="cml-indent">
      <?php _e( 'Allows you to filter the result of some widgets to display only those records relating to the current language.', 'ceceppaml' ) ?>
      <br /><br />
      <?php _e( 'Supported widgets:', 'ceceppaml' ) ?>
      <ul class="cml-ul-list">
        <li><?php _e( 'Least reads posts', 'ceceppaml' ) ?></li>
        <li><?php _e( 'Most commented', 'ceceppaml' ) ?></li>
      </ul>
    </span>
    
    <div class="cml-checkbox">
      <input type="checkbox" id="filter-query" name="filter-query" value="1" <?php checked( $_cml_settings[ 'cml_option_filter_query' ] ) ?> />
      <label for="filter-query"><span>||</span></label>
    </div>
    <label for="filter-query"><?php _e( 'Enable', 'ceceppaml' ) ?>&nbsp;</label>
  </div>
  
  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'If enabled the plugin filter the query of Least reads posts and Most commented', 'ceceppaml' ); ?>.
    <br /><br />
    <?php _e( 'The others widget will be filtered only if they use the WP_Query class.', 'ceceppaml' ); ?>.
  </div>

<?php
}

function cml_admin_options_filter_search() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];
?>
  <div id="minor-publishing">
    <div class="cml-checkbox">
      <input type="checkbox" id="filter-search" name="filter-search" value="1" <?php checked( $_cml_settings[ 'cml_option_filter_search' ], 1 ) ?> />
      <label for="filter-search"><span>||</span></label>
    </div>
    <label for="filter-search"><?php _e( 'Enable', 'ceceppaml' ) ?>&nbsp;</label>

  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Filter wordpress search query', 'ceceppaml' ); ?>.
  </div>
<?php
}

function cml_admin_options_filter_comments() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

?>
  <div id="minor-publishing">
    <ul class="cml-options">
      <li>
        <label>
          <input id="filter-posts" type="radio" value="group" name="comments" <?php checked( $_cml_settings[ 'cml_option_comments' ], "group"  ) ?> />
          <?php _e('Group', 'ceceppaml') ?>
        </label>
      </li>
      <li>
        <label>
          <input id="filter-posts" type="radio" value="" name="comments" <?php checked( $_cml_settings[ 'cml_option_comments' ], "" ) ?> />
          <?php _e('Ungroup', 'ceceppaml') ?>
        </label>
      </li>
    </ul>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'When use is viewing a single post/page', 'ceceppaml' ) ?>:
    <dl class="cml-dl-list">
      <dt>
        <?php _e( 'Group', 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'Show also comments of its translation', 'ceceppaml' ) ?>
        </dd>

      <dt>
        <?php _e( 'Ungroup', 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'Each post shown only its comments', 'ceceppaml' ) ?>
        </dd>
    </dl>
  </div>

  <?php
}

function cml_admin_options_translate_menu() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

?>
  <div id="minor-publishing">
<!--  Translate menu items  -->
    <div class="cml-checkbox">
      <input type="checkbox" id="action-menu" name="action-menu" value="1" <?php checked( $_cml_settings[ 'cml_option_action_menu' ], true) ?> />
      <label for="action-menu"><span>||</span></label>
    </div>
    <label for="action-menu"><?php _e( 'Translate menu items', 'ceceppaml' ) ?>&nbsp;</label>

<!--  Force menu items  -->
    <br />
    <div class="cml-checkbox">
      <input type="checkbox" id="force-menu" name="force-menu" value="1" <?php checked( $_cml_settings[ 'cml_option_action_menu_force' ], true ) ?> />
      <label for="force-menu"><span>||</span></label>
    </div>
    <label for="force-menu"><?php _e( 'Force language of items', 'ceceppaml' ) ?>&nbsp;</label>

<!--  Hide items  -->
    <br />
    <div class="cml-checkbox">
      <input type="checkbox" id="menu-hide-items" name="menu-hide-items" value="1" <?php checked( $_cml_settings[ 'cml_option_menu_hide_items' ], true) ?> />
      <label for="menu-hide-items"><span>||</span></label>
    </div>
    <label for="menu-hide-items"><?php _e( "Hide items that doesn't exists in current language", 'ceceppaml' ) ?>&nbsp;</label>
  </div>
  
  <div id="major-publishing-actions" class="cml-description">
    <dl class="cml-dl-list">
      <dt>
        <?php _e( 'Items will be changed with their translation', 'ceceppaml' ); ?>
      </dt>
        <dd>
          <?php _e( 'Page and category items will replace with their translation, if exists', 'ceceppaml' ); ?>
        </dd>

      <dt>
        <?php _e( 'Force items language ', 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( "If an item doesn't exists in current language, the plugin will add ?lang parameter to force item in current language", 'ceceppaml' ); ?><br />
        </dd>
        
      <dt>
        <?php _e( "Hide items that doesn't exists in current language", 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'If the plugin doesn\'t translate correctly menu items when switching language, ', 'ceceppaml' ) ?>
          <?php _e( 'add items in all languages to menu and enable this option', 'ceceppaml' ) ?>.<br />
          <?php _e( 'The plugin will remove elements that doesn\'t exists in current language from menu', 'ceceppaml' ) ?>
        </dd>
    </dl>
  </div>
<?php
}

$help = __( 'Show/Hide help', 'ceceppaml' );

//Force to active state when wizard is running
$wclass = isset( $_GET[ 'wstep' ] ) ? "active" : "";

add_meta_box( 'cml-box-options-posts', '<span class="cml-icon cml-icon-filter"></span>' . __( 'Filter posts:', 'ceceppaml' ) . "<span class=\"cml-help cml-first-help-wp tipsy-w $wclass\" title=\"$help\"></span>", 'cml_admin_options_filter_posts', 'cml_box_options' );

if( isset( $_GET[ 'wstep' ] ) ) return;  //wizard
add_meta_box( 'cml-box-options-widgets', '<span class="cml-icon cml-icon-widgets"></span>' . __( 'Filter widgets', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_filter_widgets', 'cml_box_options' );

// $mode = CMLUtils::get_url_mode();
// if( $mode == PRE_LANG || $mode == PRE_NONE ) {
  add_meta_box( 'cml-box-options-search', '<span class="cml-icon cml-icon-search"></span>' . __( 'Search', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_filter_search', 'cml_box_options' );
// }

add_meta_box( 'cml-box-options-comments', '<span class="cml-icon cml-icon-comments"></span>' . __( 'Comments', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_filter_comments', 'cml_box_options' );
add_meta_box( 'cml-box-options-menu', '<span class="cml-icon cml-icon-menu "></span>' . __( 'Translate menu', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_translate_menu', 'cml_box_options' );
?>

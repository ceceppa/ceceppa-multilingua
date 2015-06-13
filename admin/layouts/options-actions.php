<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );


echo '<div class="cml-box-shadow"></div>';

/*
 * Redirect mode
 */
function cml_admin_options_redirect() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];

  $redirect = $_cml_settings[ 'cml_option_redirect' ];
  ?>
  <div id="minor-publishing">
    <ul class="cml-options">
      <li>
        <label>
          <input type="radio" id="redirect" name="redirect" value="auto" <?php echo checked( $redirect, 'auto', false ) ?> />
          <?php _e('Automatically redirects the browser depending on the visitor\'s language.', 'ceceppaml'); ?>
        </label>
      </li>
      <li>
        <label>
          <input type="radio" id="redirect" name="redirect" value="default" <?php echo checked( $redirect, 'default', false ) ?> />
          <?php _e('Automatically redirects the default language.', 'ceceppaml'); ?>
        </label>
      </li>
      <li>
        <label>
          <input type="radio" id="redirect" name="redirect" value="others" <?php echo checked( $redirect, 'others', false ) ?> />
          <?php _e( 'Redirect only if visitor language is different from default one', 'ceceppaml' ) ?>
        </label>
      </li>
      <li>
        <label>
          <input type="radio" id="no-redirect" name="redirect" value="nothing" <?php echo checked( $redirect, 'nothing', false ) ?>/>
          <?php _e('Do nothing', 'ceceppaml') ?>
        </label>
      </li>
    </ul>
  </div>

  <div id="major-publishing-actions" class="cml-description <?php echo isset( $_GET[ 'wstep' ] ) ? "active" : "" ?>">
    <?php _e( 'Choose what to do when user visit your homepage', 'ceceppaml' ); ?>:
    <dl class="cml-dl-list">
      <dt>
        <?php _e( "Redirects the browser depending on the user's language.", 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'User will be redirected to their language, otherwise to default one', "ceceppaml") ?>
        </dd>
      <dt>
        <?php _e( 'Automatically redirects to default language.', 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'The plugin add the slug of default language', 'ceceppaml' ) ?>
        </dd>

      <dt>
        <?php _e( 'Redirect only if visitor language is different from default one', 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'The plugin doesn\'t add language slug to url for default language', 'ceceppaml' ) ?>
        </dd>

      <dt>
        <?php _e( 'Do nothing', 'ceceppaml' ) ?>
      </dt>
        <dd>
          <?php _e( 'The plugin does nothing, doesn\'t modify the home url.<br /> The content of site will be in default language', 'ceceppaml' ) ?>
        </dd>

    </dl>

    <strong>
      <?php _e( 'Default language will be used if visitor language isn\'t available', 'ceceppaml' ) ?>
    </strong>
  </div>
  <?php
}

function cml_admin_options_url_mode() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

  $mode = $_cml_settings[ 'url_mode' ];
  $structure = CMLUtils::get_permalink_structure();
  $disabled = ( empty( $structure ) ) ? 'disabled="disabled"' : "";  //Disable PRE-PATH width default permalink ?p=##
  ?>
  <div id="minor-publishing">
    <ul class="cml-options">
      <li>
        <label class="<?php echo ( empty( $disabled ) )  ? "" : "cml-disabled" ?>">
          <input type="radio" name="url-mode" id="url-mode-path" value="<?php echo PRE_PATH ?>" <?php checked( $mode, PRE_PATH ) ?> <?php echo $disabled ?> />
          <?php _e( 'Use Pre-Path Mode', 'ceceppaml' ) ?>
          <?php
            if( ! empty( $disabled ) ) {
              printf( '<span><strong>%s</strong></span>', __( 'This mode doesn\'t work with default permalink!!!', 'ceceppaml' ) );
            }
          ?>
        </label>
        <blockquote>
          <div class="cml-checkbox">
            <input type="checkbox" id="url-mode-default" name="url-mode-default" value="1" <?php checked( get_option("cml_modification_mode_default", false ) ) ?> />
            <label for="url-mode-default"><span>||</span></label>
            <label for="url-mode-default">
              &nbsp;&nbsp;<?php _e( 'Ignore for default language', 'ceceppaml') ?>
            </label>
          </div>
        </blockquote>
      </li>
      <li>
        <label>
          <input type="radio" name="url-mode" id="url-mode-domain" value="<?php echo PRE_DOMAIN ?>" <?php checked( $mode, PRE_DOMAIN ) ?> />
          <?php _e('Use Pre-Domain Mode', 'ceceppaml') ?> <i>(en.example.com)</i>
        </label>
      </li>
      <li>
        <label>
          <input type="radio" id="url-mode" name="url-mode" value="<?php echo PRE_LANG ?>" <?php checked( $mode, PRE_LANG ); ?> />
          <?php _e('Append the suffix <strong>&amp;lang=</strong> to the url', 'ceceppaml') ?>
        </label>
      </li>
      <li>
        <label>
          <input type="radio" id="url-mode" name="url-mode" value="<?php echo PRE_NONE ?>" <?php checked( $mode, PRE_NONE ); ?> />
          <?php _e( 'None', 'ceceppaml' ) ?>
        </label>
      </li>
    </ul>
  </div>

  <div id="major-publishing-actions" class="cml-description <?php echo isset( $_GET[ 'wstep' ] ) ? "active" : "" ?>">
    <?php _e( 'Choose the style of your links. Example:', 'ceceppaml' ); ?>
    <dl class="cml-dl-list">
      <dt>
        <?php _e( 'Pre-Path', 'ceceppaml' ) ?>
        <span style="margin-left: 20px" >
          <?php _e( 'This mode doesn\'t work with default permalink!!!', 'ceceppaml' ); ?> <i>(?p=##)</i>
        </span>
      </dt>
      <dd>
        <?php _e( 'Default, puts /[language slug]/ in front of URL', 'ceceppaml' ) ?>:
        <br /><br />
        <?php
        foreach( CMLLanguage::get_all() as $lang ) {
          echo CMLUtils::home_url() .  "/{$lang->cml_language_slug}/<br />";
        }
        ?>
        <br />
        <br />
        <?php _e( 'If you enable the option: ', 'cecepaml' ) ?><div style="display: inline-block"><?php _e( 'Ignore for default language', 'ceceppaml' ) ?></div>,
        <?php _e( 'language slug will be added only to translations', 'ceceppaml' ) ?>:
        <br /><br />
        <?php
        foreach( CMLLanguage::get_all() as $lang ) {
          if( $lang->cml_default ) {
            $slug = "";
            echo "<i>(" . __( 'Default', "ceceppaml" ) . ")</i>&nbsp;";
          } else
            $slug = $lang->cml_language_slug . "/";

          echo CMLUtils::home_url() .  "/{$slug}<br />";
        }
        ?>

      </dd>
      <dt><?php _e( 'Pre-Domain', 'ceceppaml' ) ?></dt>
        <dd>en.examle.com</dd>

      <dt><?php _e( 'Append suffix', 'ceceppaml' ) ?></dt>
      <dd>
        <?php
        foreach( CMLLanguage::get_all() as $lang ) {
          echo CMLUtils::home_url() .  "/?lang={$lang->cml_language_slug}/<br />";
        }
        ?>
      </dd>
    </dl>
  </div>
  <?php
}

function cml_admin_options_categories_tags() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];

?>
  <div id="minor-publishing">

    <div class="cml-checkbox">
      <input type="checkbox" id="categories" name="categories" value="1" <?php checked( $_cml_settings[ 'cml_option_translate_category_url' ], true ) ?> />
      <label for="categories"><span>||</span></label>
    </div>
    <label for="categories"><?php _e('Translate categoriy url', 'ceceppaml') ?>&nbsp;</label>

    <br />
    <div class="cml-checkbox">
      <input type="checkbox" id="category-slug" name="category-slug" value="1" <?php checked( $_cml_settings[ 'cml_option_translate_category_slug' ], true ) ?> />
      <label for="category-slug"><span>||</span></label>
    </div>
    <label for="category-slug"><?php _e('Translate category slug', 'ceceppaml') ?>&nbsp;</label>

  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Translate the url for category links.', 'ceceppaml' ) ?>.<br /><br />
    <?php _e( 'Example', 'ceceppaml' ); ?>:<br />
    <dl class="cml-dl-list">
      <dt>
        English: <span>no-category</span>
      </dt>
      <dd>
        www.example.com/category/no-category
      </dd>
      <dt>
        Italian: <span>senza-categoria</span>
      </dt>
      <dd>
        www.example.com/category/senza-categoria
      </dd>
    </dl>
  </div>
<?php
}

function cml_admin_options_show_notice() {
  $_cml_settings = & $GLOBALS[ '_cml_settings' ];

?>
  <div id="minor-publishing">
    <div class="cml-inline cml-inline-1">
      <span class="cml-indent">
      <?php _e( 'When the post/page is available in visitor language:', 'ceceppaml' ) ?>
      </span>

      <ul class="cml-options">
        <li>
          <label>
            <input type="radio" id="show-notice" name="notice" value="notice" <?php checked( $_cml_settings[ 'cml_option_notice'], 'notice') ?> />
            <?php _e('Add notice to:', 'ceceppaml') ?>
            <ul class="cml-options">
              <li>
                <div class="cml-checkbox">
                  <input type="checkbox" id="notice-post" name="notice-post" value="1" <?php checked( $_cml_settings[ 'cml_option_notice_post' ] , 1 ) ?> />
                  <label for="notice-post"><span>||</span></label>
                </div>
                <label for="notice-post"><?php _e( 'Posts', 'ceceppaml' ) ?></label>
              </li>
              <li>
                <div class="cml-checkbox">
                  <input type="checkbox" id="notice-page" name="notice-page" value="1" <?php checked( $_cml_settings[ 'cml_option_notice_page' ], 1 ) ?> />
                  <label for="notice-page"><span>||</span></label>
                </div>
                <label for="notice-page"><?php _e( 'Pages', 'ceceppaml' ) ?></label>
              </li>
            </ul>
          </label>
        </li>
        <li>
          <label>
            <input type="radio" id="no-notice" name="notice" value="nothing" <?php checked( $_cml_settings[ 'cml_option_notice' ], 'nothing') ?>/>
            <?php _e('Ignore', 'ceceppaml') ?>
          </label>
        </li>
      </ul>
    </div>
    <div class="cml-inline cml-inline-2">
      <strong><?php _e( 'Where to show the alert', 'ceceppaml' ) ?></strong>
      <ul class="cml-options">
        <li>
          <input type="radio" name="notice_pos" value="top" id="notice_top" <?php checked( $_cml_settings[ 'cml_option_notice_pos' ], 'top') ?> />
          <label for="notice_top"><?php _e('On the top of page/post', 'ceceppaml') ?></label><br>
        </li>
        <li>
          <input type="radio" name="notice_pos" value="bottom" id="notice_bottom" <?php checked( $_cml_settings[ 'cml_option_notice_pos' ], 'bottom') ?> />
          <label for="notice_bottom"><?php _e('On the bottom of page/post', 'ceceppaml') ?></label><br>
        </li>
      </ul>
    </div>

    <div>
      <strong><?php _e( 'Cusomize notice:', 'ceceppaml' ) ?></strong><br />
      <dl class="cml-dl-list">
        <dt>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-translations-page" ) ) ) ?>">
              <?php _e( 'Click here for translate notice', 'ceceppaml' ); ?>
            </a>
        </dt>
          <dd></dd>

        <dt>
          <?php _e('Before:', 'ceceppaml') ?>
        </dt>
        <dd>
          <input type="text" name="notice_before" value="<?php echo stripslashes( esc_html ( $_cml_settings[ 'cml_option_notice_before' ] ) ) ?>" />
        </dd>
        <dt>
          <?php _e('After:', 'ceceppaml') ?>
        </dt>
        <dd>
          <input type="text" name="notice_after" value="<?php echo stripslashes( $_cml_settings[ 'cml_option_notice_after' ] ) ?>" />
        </dd>
      </dl>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description <?php echo isset( $_GET[ 'wstep' ] ) ? "active" : "" ?>">
    <?php _e( 'Notice visitor that post/page that is visiting is available in his language', 'ceceppaml' ); ?>
    <br ><br />
    <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-translations-page" ) ) ) ?>">
      <?php _e( 'Click here for translate notice', 'ceceppaml' ); ?>
    </a>
  </div>

  <?php
}


function cml_admin_options_date_format() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

?>
  <div id="minor-publishing">
    <div class="cml-checkbox">
      <input type="checkbox" id="date-format" name="date-format" value="1" <?php checked( $_cml_settings[ 'cml_change_date_format' ] ) ?> />
      <label for="date-format"><span>||</span></label>
    </div>
    <label for="date-format"><?php _e( 'Change date format', 'ceceppaml' ) ?></label>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Apply date format defined in language item', 'ceceppaml' ) ?>.
  </div>

  <?php
}

function cml_admin_options_change_locale() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

  $redirect = $_cml_settings[ 'cml_option_redirect' ];
  ?>
  <div id="minor-publishing">
    <div class="cml-checkbox">
      <input type="checkbox" id="change-locale" name="change-locale" value="1" <?php checked( $_cml_settings[ 'cml_option_change_locale' ] ) ?> />
      <label for="change-locale"><span>||</span></label>
    </div>
    <label for="change-locale"><?php _e('Set the language of wordpress, in according of selected language', 'ceceppaml') ?></label>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'Change wordpress locale in according to current language.', 'ceceppaml' ); ?><br />
    <?php _e( 'If enabled language of all plugins and theme will switch in current language, if a translation exists', 'ceceppaml' ); ?>
  </div>
  <?php
}

function cml_admin_options_translate_media() {
  $_cml_settings = $GLOBALS[ '_cml_settings' ];

  ?>
  <div id="minor-publishing">
    <div class="cml-checkbox">
      <input type="checkbox" id="translate-media" name="translate-media" value="1" <?php checked( $_cml_settings[ 'cml_option_translate_media' ] ) ?> />
      <label for="translate-media"><span>||</span></label>
    </div>
    <label for="translate-media"><?php _e('Add [cml_media] shortcode to media inserted in editor', 'ceceppaml') ?></label>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'If enabled the plugin will insert inside [cml_media] shortcode to allow "alt" attribute to be translated in frontend', 'ceceppaml' ); ?><br />
    <br />
  </div>
  <?php
}

$help = __( 'Show/Hide help', 'ceceppaml' );

$wclass = isset( $_GET[ 'wstep' ] ) ? "active" : "";

add_meta_box( 'cml-box-options-redirect', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Detect browser language and:', 'ceceppaml' ) . "<span class=\"cml-help cml-help-wp tipsy-w $wclass\" title=\"$help\"></span>", 'cml_admin_options_redirect', 'cml_box_options' );
add_meta_box( 'cml-box-options-url', '<span class="cml-icon cml-icon-url "></span>' . __( 'Url Modification mode:', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w $wclass\" title=\"$help\"></span>", 'cml_admin_options_url_mode', 'cml_box_options' );
add_meta_box( 'cml-box-options-notices', '<span class="cml-icon cml-icon-notices "></span>' . __( 'Show notice', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w $wclass\" title=\"$help\"></span>", 'cml_admin_options_show_notice', 'cml_box_options' );

if( isset( $_GET[ 'wstep' ] ) ) return;  //wizard
add_meta_box( 'cml-box-options-categories', '<span class="cml-icon cml-icon-categories "></span>' . __( 'Categories & Tags', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_categories_tags', 'cml_box_options' );
add_meta_box( 'cml-box-options-date-format', '<span class="cml-icon cml-icon-comments"></span>' . __( 'Date format', 'ceceppaml' ) . "<span class=\"cml-help tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_date_format', 'cml_box_options' );
add_meta_box( 'cml-box-options-locale', '<span class="cml-icon cml-icon-wplang"></span>' . __( 'Change wordpress language:', 'ceceppaml' ) . "<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_change_locale', 'cml_box_options' );
add_meta_box( 'cml-box-options-media', '<span class="cml-icon cml-icon-wplang"></span>' . __( 'Translate media in Editor:', 'ceceppaml' ) . "<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_options_translate_media', 'cml_box_options' );
?>

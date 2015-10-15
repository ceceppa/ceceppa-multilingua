<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ( CML_PLUGIN_ADMIN_PATH . 'donate.php' );
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-settings-gen.php' );

//Current tab
$pages = array( "ceceppaml-translations-page", "ceceppaml-widgettitles-page", "ceceppaml-translations-title", "ceceppaml-translations-plugins-themes", 'ceceppaml-translate-slug' );
$tab = array_search( $_GET[ 'page' ], $pages );

$page = $_GET[ 'page' ];
?>
<div class="wrap <?php echo sanitize_title( $_GET[ 'page' ] ) ?>">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $pages[0] ?>&tab=0"><?php _e('My translations', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 1 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $pages[1] ?>&tab=1"><?php _e('Widget titles', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 2 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $pages[2] ?>&tab=2"><?php printf( "%s / %s", __( 'Site Title' ), __( 'Tagline' ) ) ?></a>
    <a class="nav-tab <?php echo $tab == 3 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $pages[3] ?>&tab=3"><?php _e('Theme', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 4 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $pages[4] ?>&tab=4"><?php _e('Custom post slug', 'ceceppaml') ?></a>
  </h2>

  <div id="poststuff">
    <div id="post-body" class="columns-1 ceceppaml-metabox">
      <div id="post-body-content" class="cml-box-options">
<?php
      switch( $tab ) {
      case 0:
        require_once ( CML_PLUGIN_LAYOUTS_PATH . 'translate-my.php' );
        break;
      case 1:
        //I have to store translations?
        if( isset( $_POST[ 'action' ] ) && wp_verify_nonce( $_POST[ "ceceppaml-nonce" ], "security" ) ) {
          global $wpdb;

          CMLTranslations::delete( "W" );
          //$wpdb->delete( CECEPPA_ML_TRANSLATIONS, array( "cml_type" => "W" ) );

          $strings = $_POST[ 'strings' ];
          $count = count( $strings );

          foreach( CMLLanguage::get_all() as $lang ) {
            $key = "lang_$lang->id";
            if( ! isset( $_POST[ $key ] ) ) continue;

            $values = $_POST[ $key ];
            for( $i = 0; $i < $count; $i++ ) {
              CMLTranslations::set( $lang->id, $strings[ $i ], $values[ $i ], "W" );
            }
          }

          cml_generate_mo_from_translations( "_X_" );
        }

        require_once ( CML_PLUGIN_FRONTEND_PATH . 'utils.php' );
        require_once ( CML_PLUGIN_LAYOUTS_PATH . 'translate-widget-titles.php' );
        break;
      case 2:
        require_once ( CML_PLUGIN_LAYOUTS_PATH . 'translate-sitename.php' );

        $page = $_GET[ 'page' ];
echo <<< EOT
        <form id="form" name="language-item" method="POST" class="cml-ajax-form">
          <input type="hidden" name="new" value="0" />
          <input type="hidden" name="action" value="ceceppaml_save_site_title" />
          <input type="hidden" name="page" value="$page" />
EOT;
          wp_nonce_field( "security", "ceceppaml-nonce" );

        foreach( CMLLanguage::get_no_default() as $lang ) {
          do_meta_boxes( 'cml_box_options_' . $lang->id, 'advanced', $lang );
        }

echo <<< EOT
          <div class="cml-submit-button">
            <div class="wpspinner">
              <span class="spinner"></span>
            </div>
EOT;
            submit_button();
          echo '</div>';
        echo '</form>';
        break;
      case 3:
        //CML parser, used for translate theme and plugin
        require_once ( CML_PLUGIN_ADMIN_PATH . 'po-parser.php' );

        require_once ( CML_PLUGIN_LAYOUTS_PATH . 'translate-theme.php' );
        break;
      case 4:
        require_once ( CML_PLUGIN_LAYOUTS_PATH . 'translate-slugs.php' );
        break;
      }
?>
      </div>
    </div>
  </div>

</div>

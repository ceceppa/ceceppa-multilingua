<?php

class CMLDebug {
  public function __construct() {
    //add_action( 'admin_menu', array( &$this, 'debug' ) );
    add_action( 'wp_footer', array( &$this, 'footer' ) );
  }
  
  public function debug() {
    //add_submenu_page('ceceppaml-language-page', __('Debug', 'ceceppaml'), __('Debug', 'ceceppaml'), 'manage_options', 'ceceppaml-debug-page', array( &$this, 'debug_page') );
  }

  public function debug_page() {
    global $wpdb;

    if( isset( $_POST[ 'tipi' ] ) ) {
      $tipi = count( $_POST[ "tipo" ] );

      for( $i = 0; $i < $tipi; $i++ ) {
	$id = $_POST[ 'id' ][ $i ];
	$tipo = $_POST[ 'tipo' ][ $i ];
	
	$wpdb->update( CECEPPA_ML_TRANS,
			array( "cml_type" => $tipo ),
			array( "id" => $id ),
			array( "%s" ),
			array( "%d" ) );
      }
    }

echo <<< EOT
  <style>
    table thead th:first-child {
      width: 30px;
    }
    table tbody tr:nth-child(odd) {
      background: #F9F9F9;
    }
  </style>
EOT;

  $tab = isset( $_GET[ 'tab' ] ) ? intval( $_GET[ 'tab' ] ) : 0;
?>
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $tab == 0 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $_GET[ 'page' ] ?>&tab=0"><?php _e('Languages', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 1 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $_GET[ 'page' ] ?>&tab=1"><?php _e('Post relations', 'ceceppaml') ?></a>
    <a class="nav-tab <?php echo $tab == 2 ? "nav-tab-active" : "" ?>" href="?page=<?php echo $_GET[ 'page' ] ?>&tab=2"><?php _e('Translations', 'ceceppaml') ?></a>
  </h2>
<?php

    switch( $tab ) {
    case 0:
//       echo "<h2>CECEPPA_ML_TABLE</h2>";
      $this->table( $wpdb->get_results( "SELECT id, cml_default, cml_flag, cml_language, cml_language_slug, cml_locale,cml_enabled,cml_sort_id,cml_flag_path,cml_rtl,cml_date_format  FROM " . CECEPPA_ML_TABLE  ) );
    
      break;
    case 1:
//       echo "<h2>CECEPPA_ML_RELATIONS</h2>";
      $this->table( $wpdb->get_results( "SELECT * FROM " . CECEPPA_ML_RELATIONS ), array( 'lang_1', 'lang_2', 'lang_3' ) );

      echo "<h3>(old method)</h3>";
      $this->table( $wpdb->get_results( "SELECT * FROM " . CECEPPA_ML_POSTS  ), array( 'cml_post_id_1', 'cml_post_id_2' ) );

      break;
    case 2:
      echo "<h2>Widget</h2>";
      $this->table( $wpdb->get_results( "SELECT min(id) as id, UNHEX(cml_text) as originale, UNHEX(cml_translation) as traduzione, cml_type as tipo FROM " . CECEPPA_ML_TRANS . " WHERE cml_type='W' GROUP BY cml_text ORDER BY cml_type" ), array(), array( "tipo" ) );

      echo "<h2>My Translations</h2>";
      $this->table( $wpdb->get_results( "SELECT min(id) as id, UNHEX(cml_text) as originale, UNHEX(cml_translation) as traduzione, cml_type as tipo FROM " . CECEPPA_ML_TRANS . " WHERE cml_type='S' GROUP BY cml_text ORDER BY cml_type" ), array(), array( "tipo" ) );

      break;
    }
  }
  
  function table( $results, $titles = array(), $edit = array() ) {
    $head = $results[ 0 ];
    
    if( !empty( $edit ) ) {
      $page = $_GET[ 'page' ];
echo <<< EOT
  <form method="POST">
    <input type="hidden" name="tipi" value="1" />
    <input type="hidden" name="page" value="$page" />
EOT;
    }
?>
    <table class="wp-list-table widefat fixed posts">
      <thead>
	<tr>
<?php
	foreach( $head as $key => $val ) {
	  echo "<th>" . str_replace( "cml_", "", $key ) . "</th>";
	  
	  //Titolo del post/page
	  if( in_array( $key, $titles ) ) echo "<th>Title</th>";

	  $keys[] = $key;
	}
?>
	</tr>
      </thead>
      <tbody>
<?php
      foreach( $results as $row ) {
	echo "<tr>";

	$row = get_object_vars( $row );
	foreach( $keys as $key ) {
	  $value = $row[ $key ];
	  $td = "<td>$value</td>";

	  if( in_array( $key, $edit ) ) {
	    $td = "<td>";
	    $td .= "<input type=\"hidden\" name=\"id[]\" value=\"" . $row['id']  . "\" />";
	    $td .= "<input type=\"text\" name=\"" . $key . "[]\" value=\"$value\" size=5 />";
	    $td .= "</td>";
	  }
	  
	  echo $td;
	  
	  if( in_array( $key, $titles ) ) {
	    $title = ( $value > 0 ) ? get_the_title( $value ) : "";
	    echo "<th>" . $title . "</th>";
	  }

	}

	echo "</tr>";
      }
?>
      </tbody>
    </table>
<?php
    if( !empty( $edit ) ) {
      submit_button();
echo <<< EOT
  </form>
EOT;
    }

  }

  public function footer () {
    global $wpCeceppaML, $wpdb;

    if( is_user_logged_in() || isset( $_GET[ "cdb" ] ) ) {
      echo "This output is visible only to logged user...";
      
      echo "CeceppaML debug:";
      echo "<pre>";
//       echo "\n\n<b>Languages</b>\n";
//       CMLDebug::table( $wpdb->get_results( "SELECT id, cml_default, cml_flag, cml_language, cml_language_slug, cml_locale,cml_enabled,cml_sort_id,cml_flag_path,cml_rtl,cml_date_format  FROM " . CECEPPA_ML_TABLE  ) );
//       print_r( cml_get_languages() );
      echo "\n\n<b>Homepage:</b>\n";
      echo "\n wpCeceppaML->_url: ";
      print_r( "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

      echo "\n wpCeceppaML->_homeUrl: ";
      print_r( CMLUtils::home_url() );
      
      echo "\n wpCeceppaML->_request_url: ";
      print_r( $wpCeceppaML->_request_url );
      
      echo "\n wpCeceppaML->_permalink_structure: ";
      print_r( CMLUtils::get_permalink_structure() );
      
      echo "\nhomeUrl(): " . home_url();
      echo "\nMode: " . CMLUtils::get_url_mode();
      echo "\nSettings: " . _CML_SETTINGS_PHP;
      echo "\nTranslations from po: " . CML_GET_TRANSLATIONS_FROM_PO;

      echo "\n\n<b>Current language:</b>\n";
      print_r( cml_get_current_language() );

      print_r( CMLPost::get_posts_by_language() );
      
      echo "\n\n<b>Static page:</b>\n";
      echo "cml_use_static_page: " . intval( cml_use_static_page() );
      echo "\npage_for_posts: " . get_option( "page_for_posts" );
      echo "\npage_on_front: " . get_option( "page_on_front" );
      echo "\ncml_is_homepage: " . intval( cml_is_homepage() );
      echo "\nthe_id: " . get_the_ID();

      echo "\n\n<b>Static page:</b>\n";
      echo "is_single(): " . is_single();
      echo "\nis_page(): " . is_page();
      echo "\nis_category(): " . is_category();
      
      echo "\n\n<b>Linked pages:</b>\n";
      print_r( CMLPost::get_translations( get_the_ID() ) );
      echo "</pre>";
    }
  }
  
}

$cmlDebug = new CMLDebug();
?>
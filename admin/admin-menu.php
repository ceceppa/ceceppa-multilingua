<?php
/*
 * original code https://gist.github.com/westonruter/3802459
 */
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
require_once ( CML_PLUGIN_LAYOUTS_PATH . "menu.php" );

class CML_Nav_Menu_Item_Custom_Fields {
	static $options = array(
		'item_tpl' => '
			<p class="cml-menu-field cml-menu-field-{name} description description-thin">
				<label for="edit-menu-item-{name}-{id}">
                    <img src="{src}" alt="{label}"/>: {nlabel}
                    <br>
					<input
						type="{input_type}"
						id="edit-menu-item-{name}-{id}"
						class="widefat code edit-menu-item-{name}"
						name="menu-item-{name}[{id}]"
						value="{title}">
				</label>
			</p>
			<p class="cml-menu-field cml-menu-field-{name} description description-thin">
				<label for="edit-menu-submenu-{name}-{id}">
					<img src="{src}" alt="{label}"/>: {tattribute}
                    <br>
					<input
						type="{input_type}"
						id="edit-menu-item-submenu-{name}-{id}"
						class="widefat code edit-menu-item-{name}"
						name="menu-item-submenu-{name}[{id}]"
						value="{attr_title}">
				</label>
			</p>
		',
		'custom_tpl' => '
			<p class="cml-menu-field-{name} description description-wide">
				<label for="edit-menu-item-url-{name}-{id}">
					<img src="{src}" alt="{label}"/> URL:
                    <br>
					<input
						type="{input_type}"
						id="edit-menu-item-url-{name}-{id}"
						class="widefat code edit-menu-url"
						name="menu-item-url-{url_name}[{id}]"
						value="{url_value}">
				</label>
			</p>
			<p class="cml-menu-field cml-menu-field-{name} description description-thin">
				<label for="edit-menu-item-{name}-{id}">
					<img src="{src}" alt="{label}"/>: {nlabel}
                    <br>
					<input
						type="{input_type}"
						id="edit-menu-item-{name}-{id}"
						class="widefat code edit-menu-item-{name}"
						name="menu-item-{name}[{id}]"
						value="{title}">
				</label>
			</p>
			<p class="cml-menu-field cml-menu-field-{name} description description-thin">
				<label for="edit-menu-submenu-{name}-{id}">
					<img src="{src}" alt="{label}"/>: {tattribute}
                    <br>
					<input
						type="{input_type}"
						id="edit-menu-item-submenu-{name}-{id}"
						class="widefat code edit-menu-item-{name}"
						name="menu-item-submenu-{name}[{id}]"
						value="{attr_title}">
				</label>
			</p>
		',
	);
 
	static function setup() {
      self::$options[ 'fields' ] = array();

      foreach( CMLLanguage::get_no_default() as $lang ) {
		$fields = array(
			$lang->cml_language_slug => array(
				'name' => $lang->cml_language_slug,
                'url_name' => $lang->cml_language_slug,
				'label' => $lang->cml_language,
                'src' => CMLLanguage::get_flag_src( $lang->id ),
                'nlabel' => __( 'Navigation Label' ),
                'tattribute' => __( 'Title Attribute' ),
				'input_type' => 'text',
			),
		);

        self::$options[ 'fields' ] = array_merge( $fields, self::$options[ 'fields' ] );
      }

      //doesn't works with php < 5.3.0 :(
//       add_filter( 'wp_edit_nav_menu_walker', function () {
//           return 'CML_Walker_Nav_Menu_Edit';
//       });
      add_filter( 'wp_edit_nav_menu_walker', 'cml_return_nav_walker', 99, 2 );
      add_filter( 'cml_nav_menu_item_additional_fields', array( __CLASS__, '_add_fields' ), 10, 5 );
      add_action( 'save_post', array( __CLASS__, '_save_post' ) );
	}
 
	static function get_fields_schema() {
		$schema = array();
		foreach(self::$options['fields'] as $name => $field) {
			if ( empty( $field['name'] ) ) {
				$field['name'] = $name;
			}
			$schema[] = $field;
		}

		return $schema;
	}

	/**
	 * Inject the 
	 * @hook {action} save_post
	 */
	static function _add_fields( $new_fields, $item_output, $item, $depth, $args ) {
		$schema = self::get_fields_schema($item->ID);
		foreach( $schema as $field ) {
          $name = $field[ 'name' ];
          $values = get_post_meta( $item->ID,
                                   "_cml_menu_meta_" . $name, false );
          $field[ 'title' ] = @$values[0][ 'title' ];
          $field[ 'url_value' ] = @$values[0][ 'url_value' ];
          $field[ 'attr_title' ] = @$values[0][ 'attr_title' ];

          $field[ 'id' ] = $item->ID;
          $k = ( $item->object == 'custom' ) ? 'custom_tpl' : 'item_tpl';
          
          //php < 5.3.0
          $func = create_function( '$key', 'return "{{$key}}";' );
          $new_fields .= str_replace(
//               array_map( function( $key ){ return '{' . $key . '}'; }, array_keys( $field ) ),
              array_map( $func, array_keys( $field ) ),
              array_values( array_map( 'esc_attr', $field ) ),
              self::$options[ $k ]
          );
		}
		return $new_fields;
	}
 
	/**
	 * Save the newly submitted fields
	 * @hook {action} save_post
	 */
	static function _save_post($post_id) {
		if( get_post_type( $post_id ) !== 'nav_menu_item' ) {
		  return;
		}

		$fields_schema = self::get_fields_schema($post_id);
        
		foreach( $fields_schema as $field_schema ) {
			$form_field_name = 'menu-item-' . $field_schema['name'];
			if (isset($_POST[$form_field_name][$post_id])) {
              $key = "_cml_menu_meta_" . $field_schema['name'];
              $values = get_post_meta( $post_id, $key, false );
              if( empty( $values ) ) {
                $values = array();
              }
              
              if( isset( $values[ 0 ] ) ) {
                $values = @$values[ 0 ];
              }

              $values[ 'title' ] = stripslashes( $_POST[ $form_field_name ][ $post_id ] );

              update_post_meta( $post_id, $key, $values );
			}
            
			$form_field_name = 'menu-item-url-' . $field_schema['name'];
			if (isset( $_POST[ $form_field_name ][ $post_id ] ) ) {
              $values = get_post_meta( $post_id, $key, false );
              if( empty( $values ) ) {
                $values = array();
              }
              
              if( isset( $values[ 0 ] ) ) {
                $values = @$values[ 0 ];
              }

              $values[ 'url_value' ] = stripslashes( $_POST[ $form_field_name ][ $post_id ] );

              update_post_meta( $post_id, $key, $values );
			}
            
			$form_field_name = 'menu-item-submenu-' . $field_schema['name'];
			if ( isset( $_POST[ $form_field_name ][ $post_id ] ) ) {
              $values = get_post_meta( $post_id, $key, false );
              if( empty( $values ) ) {
                $values = array();
              }
              
              if( isset( $values[ 0 ] ) ) {
                $values = @$values[ 0 ];
              }

              $values[ 'attr_title' ] = stripslashes( $_POST[ $form_field_name ][ $post_id ] );

              update_post_meta( $post_id, $key, $values );
			}
		}
        
        CMLUtils::_del( 'no_generate' );
	}
 
}

//If current theme have multiple menus, ask the user to set your primary one
function cml_admin_select_menu() {
  global $pagenow;
  global $_wp_registered_nav_menus;

  $menus = get_registered_nav_menus();
  foreach( $menus as $key => $name ) {
    if( substr( $key, 0, 4 ) == "cml_" ) unset( $menus[ $key ] );
  }

  if( $pagenow == 'nav-menus.php' && count( $menus ) > 1 ) {
?>
  <div class="updated cml-updated">
    <form name="cml-menu-primary-name" method="get">
      <input type="hidden" name="cml-form" value="menu-name" />
      <?php
      _e( 'Select the name of your primary menu:', 'ceceppaml' );
      echo '<span class="cml-help cml-pointer-help cml-menu-help"></span>';

      echo "<br />";
  
			if( isset( $_GET[ 'cml-name' ] ) ) {
				update_option( "cml_primary_menu_name", esc_attr( $_GET[ 'cml-name' ] ) );
			}

      $name = get_option( "cml_primary_menu_name", "" );

      //Se non inizia per cml_ allora sarà quella definito dal tema :)
      if( is_array( $menus ) ) : ?>
      <select name="cml-name" class="cml-select-menu">
        <?php foreach( $menus as $key => $n ) {
          if( substr( $key, 0, 4 ) == "cml_" ) continue;
  
          echo '<option value="' . str_replace( " ", "-", esc_attr( $key ) ) . '" ' . selected( $key, $name, false ) . '>' . $n . '</option>';
        }?>
      </select>
      <?php endif; ?>
      <p class="submit alignright">
        <input type="submit" class="button button-primary" value="<?php _e( 'Save', 'ceceppaml' ) ?>" />
      </p>
    </form>
  </div>
<?php
    }
}

class CML_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
		$item_output = '';
		parent::start_el($item_output, $item, $depth, $args);
		$new_fields = apply_filters( 'cml_nav_menu_item_additional_fields', '', $item_output, $item, $depth, $args );
		// Inject $new_fields before: <div class="menu-item-actions description-wide submitbox">
		if ($new_fields) {
			$item_output = preg_replace('/(?=<div[^>]+class="[^"]*submitbox)/', $new_fields, $item_output);
		}
		$output .= $item_output;
	}
}

//Added for compatibility with php < 5.3.0
function cml_return_nav_walker() {
	return 'CML_Walker_Nav_Menu_Edit';
}

CML_Nav_Menu_Item_Custom_Fields::setup();

CMLUtils::_set( 'no_generate', true );

//Avoid that cml_generate_mo_from_translations was called twice
add_action( 'wp_update_nav_menu', 'cml_generate_mo_from_translations', 10 );

//Languages box
add_meta_box( 'ceceppaml-menu-box', 'CeceppaML: ' . __('Flags', 'ceceppaml'), 'cml_menu_meta_box', 'nav-menus', 'side', 'default' );

add_action( 'admin_notices', 'cml_admin_select_menu' );

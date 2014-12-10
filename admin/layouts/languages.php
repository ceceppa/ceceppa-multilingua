<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * Box containing acivte languages
 */
function cml_admin_box_languages() {
  $langs = CMLLanguage::get_all();

  echo '<ul id="cml-languages">';
  
  foreach( $langs as $lang ) {
    cml_admin_language_add_new_item( array( "enabled" => $lang->cml_enabled,
                                           "flag" => $lang->cml_flag,
                                           "custom_flag" => $lang->cml_custom_flag,
                                           "name" => $lang->cml_language,
                                           "default" => $lang->cml_default,
                                           "date_format" => $lang->cml_date_format,
                                           "slug" => $lang->cml_language_slug,
                                           "locale" => $lang->cml_locale,
                                           "rtl" => $lang->cml_rtl,
                                           "id" => $lang->id ) );
  }

  echo '</ul>';
}

/*
 * Show all available languages
 */
function cml_admin_box_available_languages() {
  echo '<ul id="cml-languages">';
  
  $mode = $GLOBALS[ 'cml_show_mode' ];
  
  //Available languages
  $GLOBALS[ 'cml_show_mode' ] = $mode;
  foreach( $GLOBALS[ 'cml_all_languages' ] as $lang ) {
    cml_admin_language_add_new_item( array( "enabled" => true,
                                           "flag" => $lang[ 1 ],
                                           "custom_flag" => false,
                                           "name" => $lang[ 2 ],
                                           "default" => false,
                                           "date_format" => null,
                                           "slug" => $lang[ 0 ],
                                           "locale" => $lang[ 1 ],
                                           "rtl" => isset( $lang[ 3 ] ),
                                           "id" => null,
                                           "custom_box" => true ) );
  }

  echo '</ul>';

  //Add custom language
  $GLOBALS[ 'cml_show_mode' ] = 'show-advanced';
  echo '<ul id="cml-languages" class="cml-custom-languages">';
    cml_admin_language_add_new_item( array( "enabled" => true,
                                           "flag" => null,
                                           "custom_flag" => false,
                                           "name" => "Custom",
                                           "default" => 0,
                                           "date_format" => null,
                                           "slug" => null,
                                           "locale" => null,
                                           "rtl" => null,
                                           "id" => 0,
                                           "custom" => true ) );
  echo '</ul>';
  
  //Custom language message
  $msg = __( 'You can change the flag only after adding language.', 'ceceppaml' );
echo <<<EOT
<div class="cml-custom-message">
  $msg
</div>
EOT;
}

//Active languages "combo"
$mode = array( 'show-basic' => __( 'Basic', 'ceceppaml' ),
              'show-intermediate' => __( 'Intermediate', 'ceceppaml' ),
              'show-advanced' => __( 'Advanced', 'ceceppaml' ) );

  $active_title = '<div class="cml-box-right cml-box-mode"><span>' . __( 'Settings', 'ceceppaml' ) . ':</span>';
  $active_title .= '<ul class="cml-combo">';
  $active_title .= '<li class="cml-combo-item-current">' . $mode[ $GLOBALS[ 'cml_show_mode' ] ] . '</li>';
  $active_title .= '<ul class="cml-combo-items">';

  foreach( $mode as $key => $value ) {
    $active_title .= '<li id="' . $key . '" class="cml-combo-item">' . $value . '</li>';
  }
  
  $help = __( 'Show help', 'ceceppaml' );

  $active_title .= '</ul>';
  $active_title .= '</ul>';
  $active_title .= '<span class="spinner"></span>';
  $active_title .= '<input type="button" name="save-all" class="button button-primary" value="' . __( 'Save', 'ceceppaml' ) . '" onclick="cml_save_all_items()" />';
  $active_title .= '</div>';
  $active_title .= "<span class=\"cml-help cml-lang-help cml-pointer-help tipsy-w\" title=\"$help\"></span>";
  
  add_meta_box( 'cml-box-languages', __( 'My languages', 'ceceppaml' ) . $active_title, 'cml_admin_box_languages', 'cml_box_languages' );

//Available languages
$available_title = '<div class="cml-box-right">';
  $available_title .= '<span>' . __( 'Search language', 'ceceppaml' ) . ':</span>';
  $available_title .= '<input type="search" name="search" id="search" />';
  $available_title .= '<span>&nbsp;' . __( 'or', 'ceceppaml' ) . ':</span>';
  $available_title .= '<input type="button" class="button" name="add-custom" value="' . esc_html( __( 'Add custom', 'ceceppaml' ) ) . '" />';
  $available_title .= '</div>';
  
  add_meta_box( 'cml-box-available-languages', __( 'Available languages', 'ceceppaml' ) . $available_title, 'cml_admin_box_available_languages', 'cml_box_languages' );

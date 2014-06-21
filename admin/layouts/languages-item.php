<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * Add new item in box languages
 *
 * $enabled - (boolean) is the language enabled?
 * $flag - language filename ( withouth extension )
 * $name - language name
 * $default (boolean) is the language the default one?
 * $date_format ( string ) - date format
 * $slug - language slug
 * $locale - language locale
 * $rtl - right to left?
 * $id = 0 - order index
 * $custom - used for add custom language
 * $custom_box ( boolean ) Is it in available language box? If true I ignore show_advanced_mode
 * $custom_flag - use custom flag instead of plugin one?
 */
function cml_admin_language_add_new_item( $attrs ) {
  $custom_box = false;
  
  extract( $attrs, EXTR_OVERWRITE );

  if( empty( $date_format ) ) $date_format = get_option( 'date_format' );
  if( ! isset( $custom ) ) $custom = false;
  $mode = ( ! $custom_box ) ? $GLOBALS[ 'cml_show_mode' ] : "";

  $flag_name = $flag;
  ?>
    <li class="lang <?php echo ( $enabled ) ? "active" : "" ?>">
      <div class="error-tooltip">
        <?php _e( "Oops, something goes wrong", "ceceppaml" ); ?>
      </div>
      <div class="title">
        <div class="flag">
          <?php
            $small = "small/$flag.png";
            
            if( $custom_flag && file_exists( CML_UPLOAD_DIR .  $small ) )
              $src = CML_UPLOAD_URL . $small;
            else
              $src = CML_PLUGIN_FLAGS_URL . $small;
              
            if( $flag == null ) $src = CML_PLUGIN_IMAGES_URL . "no-flag.png";
          ?>
          <div class="item-intermediate item-advanced item-languages">
            <?php
            if( isset( $GLOBALS[ 'cml_flags' ][ $flag ] ) )
              $flag_title = $GLOBALS[ 'cml_flags' ][ $flag ];
            else
              $flag_title = $name;
              
            $flag_title .= "<br />" . __( 'Click for change or upload custom flag', 'ceceppaml' );
            ?>
            <img class="tipsy-s active-flag" src="<?php echo  esc_url( $src ) ?>" width="20" title="<?php echo $flag_title ?>" />
          </div>
        </div>
        <span class="name handle"><?php echo esc_html( $name ) ?></span>
          <img class="default tipsy-me <?php echo ( $default ) ? "active" : "" ?>" src="<?php echo CML_PLUGIN_IMAGES_URL ?>heart.png" width="16" height="14" title="<?php _e( 'Default language', 'ceceppaml' ) ?>" />
          <img class="enabled tipsy-me <?php echo ( $enabled ) ? "active" : "" ?>" src="<?php echo CML_PLUGIN_IMAGES_URL ?>enabled.png" width="16" height="14" title="<?php _e( 'Enable/Disable language', 'ceceppaml' ) ?>" />
      </div>
      <div class="advanced <?php echo $mode ?>">
        <form id="form" name="language-item" method="POST" enctype="multipart/form-data" >
          <input type="hidden" name="id" class="lang-item-<?php echo $id ?>" value="<?php echo $id ?>" />
          <input type="hidden" name="default" value="<?php echo $default ?>" />
          <input type="hidden" name="enabled" value="<?php echo $enabled ?>" />
          <input type="hidden" name="flag" value="<?php echo $flag_name ?>" />
          <input type="hidden" name="pos" value="0" />
          <input type="hidden" name="remove" value="0" />
          <input type="hidden" name="new" value="0" />
          <div class="upload-field">
            <input type="file" name="flag_file" id="flag-file" />
          </div>
          <div class="other-flags">
            <?php
            //Show all variants of current language
            $path = CML_PLUGIN_FLAGS_PATH . "small/";
            $url = CML_PLUGIN_FLAGS_URL . "small/";
            $flags = glob ( $path . substr( $locale, 0, 2 ) . "*.png" );
            
            //look for custom flags
            $customs = glob ( CML_UPLOAD_DIR . "small/" . substr( $locale, 0, 2 ) . "*.png" );
            if( is_array( $customs ) ) 
              $flags = array_merge( $flags, $customs );

            echo '<ul class="flags">';
            if( $flag != null && ! empty( $flags ) ) {
              foreach( $flags as $flag ) {
                $file = basename( $flag, ".png" );
                $dir = dirname( $flag );
                $title = @$GLOBALS[ 'cml_flags' ][ $file ];
    
                if( empty( $title ) ) $title = searchForId( $file, $GLOBALS[ 'cml_all_languages' ] );
                if( ! empty ( $title ) ) {
                  $filename = "small/" . $file . ".png";
    
                  if( $dir == CML_PLUGIN_FLAGS_PATH . "small" ) {
                    $src = CML_PLUGIN_FLAGS_URL . $filename;
                    $is_custom = 0;
                  } else {
                    $src = CML_UPLOAD_URL . $filename;

                    $is_custom = 1;
                  }

                  //echo $custom_flag;
                  echo '<input type="hidden" name="custom_flag" value="' . $is_custom . '" />';
                  $classactive = ( $file == $flag_name && $is_custom == $custom_flag ) ? "active" : "";
                  if( $is_custom ) $title .= "<br /><i>(" . __( 'Custom', 'ceceppaml' ) . ")</i>";
                  echo '<li cml-locale="' . $file . '" cml-custom="' . $is_custom . '" cml-language="' . strtolower( $title ) . '" class="tipsy-s ' . $classactive . '" title="' . $title . '" >';
                  echo '<img src="' . $src . '" width="26" />';
                  echo '</li>';
                }
              } //foreach
            } //endif;
  
            echo '<li><img class="tipsy-s image-upload" src="' . CML_PLUGIN_IMAGES_URL . 'upload.png" title="' . __( 'Upload custom image', 'ceceppaml' ) . '" /></li>';
            echo '</ul>';
            ?>
          </div>
        <dl>
          <dd class="item-advanced item-intermediate label-lang-name"><?php _e( 'Name', 'ceceppaml' ) ?></dd>
          <dt class="item-advanced item-intermediate"><input type="text" name="lang-name" id="lang-name" value="<?php echo esc_html( $name ) ?>" /></dt>
          <dd class="item-intermediate item-advanced label-date-format"><?php _e( 'Date format', 'ceceppaml' ) ?></dd>
          <dt class="item-intermediate item-advanced">
            <span class="date-format tipsy-me" title="<?php printf( __( 'Date format: %s. <br /> Click for edit', 'ceceppaml' ), $date_format ) ?>"><?php echo date( $date_format ); ?>
            </span>
            <input type="text" class="hidden" name="date-format" value="<?php  echo esc_html( $date_format ) ?>" />
          </dt>
          <dd class="item-advanced label-url-slug"><?php _e( 'Language slug', 'ceceppaml' ) ?></dd>
          <dt class="item-advanced"><input type="text" name="url-slug" value="<?php echo esc_html( $slug ) ?>" /></dt>
          <dd class="item-advanced label-wp-locale"><?php _e( 'WP Locale', 'ceceppaml' ) ?></dd>
          <dt class="item-advanced"><input type="text" name="wp-locale" value="<?php echo esc_html( $locale ) ?>" /></dt>
          <dd class="item-advanced"><?php _e( 'Right to left', 'ceceppaml' ) ?></dd>
          <dt class="item-advanced"><input type="checkbox" name="rtl" value="1" <?php checked( $rtl, 1, false ) ?> /></dt>
        </dl>
        </form>
      </div>
      <p class="submit item-intermediate item-advanced submitbox <?php echo $mode ?>">
        <span class="remove submitdelete"><?php echo ( ! $custom ) ? __( 'Delete', 'ceceppaml' ) : __( 'Close', 'ceceppaml' ) ?></span>
        <?php
          if( $custom ) {
            $button = __( 'Add', 'ceceppaml' );
          } else {
            $button = ( $mode == 'show-advanced' ) ? __( 'Less', 'ceceppaml' ) : __( 'More', 'ceceppaml' );
          }
        ?>
        <input type="button" name="submit" id="more" class="button button-<?php echo ( ! $custom ) ? 'secondary' : 'primary' ?>" value="<?php echo $button ?>">
        <span class="spinner"></span>
        <input type="button" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'ceceppaml' ) ?>">
      </p>
    </li>
<?php
}

function searchForId( $id, $array ) {
   foreach ($array as $key => $val) {
       if ($val[ 0 ] === $id) {
           return $val[2];
       }
   }
   return null;
}

?>
<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

?>
<form class="ceceppa-form-translations cml-ajax-form" name="wrap" method="post">
  <input type="hidden" name="action" value="ceceppaml_translate_slugs">
  <input type="hidden" name="page" value="<?php echo esc_attr( $_GET[ 'page' ] ) ?>" />
  <?php echo wp_nonce_field( "security", "ceceppaml-nonce", true, false ); ?>

<div class="cml-tab-wrapper cml-tab-strings">
  <div class="cml-left-items">
<!--
    <div id="cml-search">
      <input type="search" name="s" id="filter" placeholder="<?php _e( 'Search', 'ceceppaml' ) ?>" value="" size="40" />
    </div>
-->
  </div>
  <div class="cml-right-items">
    <div class="empty"></div>
    <?php
        $lkeys = array_keys( CMLLanguage::get_all() );
        if( count( CMLLanguage::get_all() ) > 1 ) :
    ?>
    <?php endif; ?>
    <a class="cml-button tipsy-me" id="cml-save" title="<?php _e( 'Save changes', 'ceceppaml' ) ?>"
       onclick="jQuery( '.ceceppa-form-translations' ).submit()">
      <?php _e( 'Save changes', 'ceceppaml' ) ?>
    </a>
  </div>

  <div style="clear:both"></div>
</div>

  <table class="widefat ceceppaml-theme-translations">
    <thead>
      <tr>
        <th><?php _e( 'Enabled', 'ceceppaml' ) ?></th>
        <th><?php _e( 'Slug', 'ceceppaml' ) ?></th>
        <th><?php _e( 'Translation', 'ceceppaml' ) ?></th>
      </tr>
    </thead>
    <tbody>
    <?php
        $post_types = get_post_types( array( '_builtin' => FALSE ), 'names');

        //Translated slugs
        $translated = get_option( "cml_translated_slugs", array() );
        $alternate = "";
        $i = 0;
        foreach( $post_types as $post_type ) :

          $alternate = ( empty( $alternate ) ) ? "alternate" : "";
      ?>
      <tr class="<?php echo $alternate ?>">
        <td>
          <?php
            echo cml_utils_create_checkbox( "", 'cml-slugbox-' . $i, "senabled[$i]", null, 1, @$translated[ $post_type ][ 'enabled' ] );
          ?>
        </td>
        <td>
          <?php echo $post_type ?>
          <input type="hidden" name="slug[<?php echo $i ?>]" value="<?php echo $post_type ?>">
        </td>
        <td>
          <?php
            foreach( CMLLanguage::get_others() as $lang ) :
          ?>
           <div class="cml-myt-flag ">
             <?php echo CMLLanguage::get_flag_img( $lang ); ?>
            <input type="text" name="tslug[<?php echo $i ?>][<?php echo $lang->id ?>]" value="<?php echo @$translated[ $post_type ][ $lang->id ] ?>" placeholder="<?php _e( "Leave empty if you don't want to translate this slug", "ceceppaml" ) ?>" style="width: 90%" />
          </div>
          <?php
            endforeach;
          ?>
        </td>
      </tr>
     <?php
          $i++;
        endforeach;
    ?>
    </tbody>
  </table>


<div class="cml-tab-wrapper cml-tab-strings">
  <div class="cml-left-items">
<!--
    <div id="cml-search">
      <input type="search" name="s" id="filter" placeholder="<?php _e( 'Search', 'ceceppaml' ) ?>" value="" size="40" />
    </div>
-->
  </div>
  <div class="cml-right-items">
    <div class="empty"></div>
    <?php
        $lkeys = array_keys( CMLLanguage::get_all() );
        if( count( CMLLanguage::get_all() ) > 1 ) :
    ?>
    <?php endif; ?>
    <a class="cml-button tipsy-me" id="cml-save" title="<?php _e( 'Save changes', 'ceceppaml' ) ?>"
       onclick="jQuery( '.ceceppa-form-translations' ).submit()">
      <?php _e( 'Save changes', 'ceceppaml' ) ?>
    </a>
  </div>

  <div style="clear:both"></div>
</div>



</form>

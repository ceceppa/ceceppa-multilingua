<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

?>
<form class="ceceppa-form-translations $this->_form_name" name="wrap" method="post" action="$self?page={$page}&tab={$tab}">
  <input type="hidden" name="generate" value="1">
  <input type="hidden" name="action" value="ceceppaml_translate_slug">
  <input type="hidden" name="page" value="$page" />
  <input type="hidden" name="tab" value="$tab" />
  <input type="hidden" name="src_path" value="$this->_src_path" />
  <input type="hidden" name="dest_path" value="$this->_dest_path" />
  <input type="hidden" name="domain" value="$this->_domain" />
  <input type="hidden" name="locale" value="$this->_default_lang" />
  <input type="hidden" name="name" value="$this->_name" />
  $nonce

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
        <th><?php _e( 'Slug', 'ceceppaml' ) ?></th>
        <th><?php _e( 'Translation', 'ceceppaml' ) ?></th>
      </tr>
    </thead>
    <tbody>
    <?php
        $post_types = get_post_types( array( '_builtin' => FALSE ), 'names');

        $alternate = "";
        $i = 0;
        foreach( $post_types as $post_type ) :

          $alternate = ( empty( $alternate ) ) ? "alternate" : "";
      ?>
      <tr class="<?php echo $alternate ?>">
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
            <input type="text" name="tslug[<?php echo $i ?>][<?php echo $lang->id ?>]" value="" placeholder="<?php _e( "Leave empty if you don't want to translate this slug", "ceceppaml" ) ?>" style="width: 90%" />
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




</form>

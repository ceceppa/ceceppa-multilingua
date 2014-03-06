<?php
function cml_admin_translate_site_title( $lang ) {
  $blog_title = get_bloginfo( 'name' );
  $blog_tagline = get_bloginfo( 'description' );

  $title = cml_translate ( $blog_title, $lang->id, 'T', false, true );
  $tagline = cml_translate ( $blog_tagline, $lang->id, 'T', false, true );

  ?>
  <div id="minor-publishing">
    <input type="hidden" name="id[]" value="<?php echo $lang->id ?>" />
    <dl class="site-title">
      <dt>
        <?php _e( 'Site title' ); ?>
      </dt>
        <dd>
          <input class="regular-text" type="text" name="title[]" value="<?php echo $title ?>" />
        </dd>
        
      <dt>
        <?php _e( 'Tagline' ); ?>
      </dt>
        <dd>
          <input class="regular-text" type="text" name="tagline[]" value="<?php echo $tagline ?>" />
        </dd>
    </dl>
  </div>
<?php
}

foreach( CMLLanguage::get_no_default() as $lang ) {
  add_meta_box( 'cml-box-site-title-' . $lang->id, '<span class="cml-icon cml-flag-tiny-' . $lang->cml_language_slug . '"></span>' . $lang->cml_language, 'cml_admin_translate_site_title', 'cml_box_options_' . $lang->id );
}
?>
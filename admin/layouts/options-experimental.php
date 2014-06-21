<?php
require_once( CML_PLUGIN_FRONTEND_PATH . "utils.php" );

function cml_admin_experimental_category() {
  ?>
  <div id="minor-publishing">
    <div class="cml-indent">
      <?php _e( 'Store translated category as', 'ceceppaml' ) ?>
      <input type="hidden" name="experiments[]" value="xp-create-translated-category" />
    </div>
    <ul>
      <li>
        <label>
          <input type="radio" name="xp-create-translated-category[]" value="0" <?php checked( CML_CREATE_CATEGORY_AS, CML_CATEGORY_AS_STRING ); ?> />
          <?php _e( 'String', 'ceceppaml' ); ?>
        </label>
      </li>

      <li>
        <label>
          <input type="radio" name="xp-create-translated-category[]" value="1" <?php checked( CML_CREATE_CATEGORY_AS, CML_CATEGORY_CREATE_NEW ); ?> />
          <?php _e( 'New category', 'ceceppaml' ); ?>
        </label>
      </li>

      <div class="cml-submit-button" style="height: 40px">
        <div class="wpspinner">
          <span class="spinner"></span>
        </div>
        <?php submit_button(); ?>
      </div>
    </ul>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <dl class="cml-dl-list">
      <dt>
        <?php _e( 'String', 'ceceppaml' ); ?>
        <dd>
          <?php _e( 'This method store category translation as simple string', 'ceceppaml' ) ?>
        </dd>
    
      <dt>
        <?php _e( 'Create new', 'ceceppaml' ); ?>
        <dd>
          <?php _e( 'When use to translate a category the plugin will create a new category in wp', 'ceceppaml' ) ?>.
        </dd>
    </dl>
  </div>
<?php
}

function cml_admin_experimental_disclaimer() {
  ?>
  <div id="minor-publishing">
    <div>
      <?php
        _e( 'Experimental features will be automatically enabled when they are ready to use.', 'ceceppaml' );
      ?>
      <br />
      <h3 style="text-align: center; color: #f00">
      <?php
        _e( 'Enable them at your own risk', 'ceceppaml' );
      ?>
      </h3>
    </div>
  </div>

  <div id="major-publishing-actions" class="cml-description">
    <?php _e( 'If you need help, contact me', 'ceceppaml' ); ?>
  </div>
<?php
}

$help = __( 'Show/Hide help', 'ceceppaml' );

//Disclaimer
add_meta_box( 'cml-box-disclaimer', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Experimental features', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_experimental_disclaimer', 'cml_box_options' );

//New category storing method
add_meta_box( 'cml-box-category-method', '<span class="cml-icon cml-icon-redirect "></span>' . __( 'Category', 'ceceppaml' ) . ":<span class=\"cml-help cml-help-wp tipsy-w\" title=\"$help\"></span>", 'cml_admin_experimental_category', 'cml_box_options' );
?>
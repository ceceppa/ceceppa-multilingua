<?php
function cml_menu_meta_box() {
  global $_nav_menu_placeholder, $nav_menu_selected_id;

  $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
  ?>
  <div id="cml-language-switch" class="posttypediv">
      <div id="tabs-panel-lang-switch" class="tabs-panel tabs-panel-active">
          <ul id ="cml-language-switch-checklist" class="categorychecklist form-no-clear">
              <li>
                  <label class="menu-item-title">
                    <input type="checkbox" id="cml-menu-item" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" class="menu-item-checkbox" value="1" />
                    <?php _e( 'Current language', 'ceceppaml' ) ?>
                  </label>
                  <input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
                  <input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Current language', 'ceceppaml' ); ?>">
                  <input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#cml-current">
              </li>
              
              <li>
                  <label class="menu-item-title">
                    <input type="checkbox" id="cml-menu-item" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" class="menu-item-checkbox" value="1" />
                    <?php _e( 'All languages', 'ceceppaml' ) ?>
                  </label>
                  <input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
                  <input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'All languages', 'ceceppaml' ); ?>">
                  <input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#cml-others">
              </li>

              <li>
                  <label class="menu-item-title">
                    <input type="checkbox" id="cml-menu-item" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" class="menu-item-checkbox" value="1" />
                    <?php _e( 'All languages except current', 'ceceppaml' ) ?>
                  </label>
                  <input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
                  <input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'All languages except current', 'ceceppaml' ); ?>">
                  <input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#cml-no-current">
              </li>
              
              <?php foreach( CMLLanguage::get_all() as $lang ) : ?>
              <li>
                  <label class="menu-item-title">
                    <input type="checkbox" id="cml-menu-item" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" class="menu-item-checkbox" value="1" />
                    <?php echo $lang->cml_language ?>
                  </label>
                  <input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
                  <input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $lang->cml_language ?>">
                  <input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#cml-lang-<?php echo $lang->id ?>">
              </li>
              <?php endforeach; ?>
          </ul>
      </div>
      <p class="button-controls">
          <span class="add-to-menu">
              <input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-cml-language-switch">
              <span class="spinner"></span>
          </span>
      </p>
  </div>
  <?php
}
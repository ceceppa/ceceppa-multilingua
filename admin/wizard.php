<?php
  $admin = admin_url() . "admin.php";
  $page = @$_GET[ 'page' ];
  $step = isset( $_GET[ 'wstep' ] ) ? intval( $_GET[ 'wstep' ] ) : 1;

  $step2 = ( $step == 2 ) ? "active" : "";
?>
<br />
<div id="cml-wizard">
  <div class="steps">
    <span class="<?php echo ( $step == 1 ) ? 'active' : 'done' ?>">
      <span class="number">1</span>
      <a class="title" href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-language-page" ), $admin ) ) ?>">
        <?php _e( 'Manage your languages', 'ceceppaml' ) ?>
      </a>
    </span>
    <span class="<?php echo ( $step > 2 ) ? 'done' : $step2 ?>">
      <span class="number">2</span>
      <a class="title" href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-options-page", "wstep" => 2 ), $admin ) ) ?>">
        <?php _e( 'Choose filter and url style', 'ceceppaml' ) ?>
      </a>
    </span>
    <span class="<?php echo ( $step == 3 ) ? 'active' : '' ?>">
      <span class="number">3</span>
      <a class="title" href="<?php echo esc_url( add_query_arg( array( "wstep" => 3 ), admin_url() . "edit.php" ) ) ?>">
        <?php _e( 'Translate your site', 'ceceppaml' ) ?>
      </a>
    </span>
  </div>
  <div class="content">
    <div class="<?php echo ( $step == 1 ) ? "visible" : "hidden" ?>">
      <div class="logo-big">
      </div>
      <div class="cml-left">
        <h3><?php _e( 'Welcome to Ceceppa Multilingua', 'ceceppaml' ); ?></h3>
        <p>
          <?php _e( 'These wizard will guides you in basic configure of the plugin', 'ceceppaml' ); ?>...
        </p>
      </div>

      <div style="margin-top: 15px; display: block">
        <?php
          $installed = get_option( '_cml_installed_language' );

          if( isset( $_GET[ 'cml_update_existings_posts' ] ) ) {
            update_option( "_cml_update_existings_posts", 0 );
          }

          $done = get_option( "_cml_update_existings_posts", 1 );
          $class = ( isset( $_GET[ 'cml_update_existings_posts' ] ) || ! $done ) ? "cml-done" : "";

          $p = "<h4>" . __( 'Update your posts language', 'ceceppaml' ) . "</h4>";
          $p .= "<p class=\"$class\">";
          $p .= sprintf( __( "Click <a href=\"%s\">here</a> to assign \"%s\" to existing posts and pages.", "ceceppaml" ),
          esc_url( add_query_arg( array( 'cml_update_existings_posts' => 1 ) ) ),
                    $installed );
          $p .= "</p>";

          echo "<h4>" . __( 'detected language', 'ceceppaml' ) . "</h4>";
          if( $page != "ceceppaml-language-page" ) {
            printf( "<i>\"%s\" </i>%s.", $installed,
                                  __( 'was automatically added to your languages', 'ceceppaml' ) );

            echo $p;

            echo "<h4>" . __( 'Manage your languages', 'ceceppaml' ) . "</h4>";
            printf( __( 'Click <%s>here</a> to manage languages', 'ceceppaml' ),
                    'a href="' . esc_url( add_query_arg( array( "page" => "ceceppaml-language-page", "wstep" => 1 ), $admin ) ) . '"' );
          } else {
           echo $p;

            echo "<h4>" . __( 'Manage your languages', 'ceceppaml' ) . "</h4>";
            _e( 'Add your languages', 'ceceppaml' );
          }
        ?>
      </div>

      <div class="cml-skip">
        <a class="button" href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-options-page", "wstep" => 2 ), $admin ) ) ?>">
          <?php printf( "%s %d", __( 'go to step', 'ceceppaml' ), 2 ); ?>
        </a>
      </div>
    </div>

<!-- 2 -->
    <div class="<?php echo ( $step == 2 ) ? "visible" : "hidden" ?>">
      <dl class="cml-dl-list">
        <dt>
          <?php _e( 'Filters', 'ceceppaml' ) ?>
        </dt>
          <dd>
            <?php _e( 'Customize which posts show in according to current language', 'ceceppaml' ) ?>
          </dd>

        <dt>
          <?php _e( 'Actions', 'ceceppaml' ) ?>
        </dt>
          <dd>
            <?php _e( 'Choose the style of your', 'ceceppaml' ) ?>:

            <ul class="cml-ul-list">
              <li>
                <?php _e( 'Homepage', 'ceceppaml' ) ?>
                <i>( <?php _e( 'Detect browser language and:', 'ceceppaml' ) ?> )</i>
              </li>
              <li>
                <?php _e( 'Links', 'ceceppaml' ) ?>
                <i>( <?php _e( 'Url Modification mode:', 'ceceppaml' ) ?> )</i>
              </li>
              <li>
                <?php _e( 'Enable notices', 'ceceppaml' ) ?>
                <i>( <?php _e( 'Show notice', 'ceceppaml' ) ?> )</i>
              </li>
            </ul>
          </dd>
      </dl>

      <strong>
        <?php _e( "During Wizard will be shown only basic settings, click on \"Ceceppa Multilingua\" -> \"Settings\" for see all available options", 'ceceppaml' ) ?>
      </strong>

      <br /><br />
      <strong class="cml-uninstall">
        <?php _e( "Don't forget to click on \"Save\" button before change tab.", 'ceceppaml' ) ?>
      </strong>
      <div class="cml-skip">
        <a class="button" href="<?php echo esc_url( add_query_arg( array( "wstep" => 3 ), admin_url() . "edit.php" ) ) ?>">
          <?php printf( "%s %d", __( 'go to step', 'ceceppaml' ), 3 ); ?>
        </a>
      </div>

    </div>

<!-- 3 -->
    <div class="<?php echo ( $step == 3 ) ? "visible" : "hidden" ?>">
      <p>
        <strong>
          <?php _e( 'Now you can start to translate', 'ceceppaml' ); ?>:
        </strong>
        <ul class="cml-ul-list">
          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "wstep" => 3 ), admin_url() . "edit.php" ) ) ?>">
              <?php _e( 'Your posts', 'ceceppaml' ); ?>
            </a>
          </li>
          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-translations-title", "wstep" => 3 ), admin_url() . "admin.php" ) ) ?>">
              <?php _e( 'Site Title/Tagline', 'ceceppaml' ); ?>
            </a>
          </li>

          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-widgettitles-page", "wstep" => 3 ), admin_url() . "admin.php" ) ) ?>">
              <?php _e( 'Widget titles', 'ceceppaml' ); ?>
            </a>
          </li>

          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-translations-plugins-themes", "wstep" => 3 ), admin_url() . "admin.php" ) ) ?>">
              <?php _e( 'Current theme', 'ceceppaml' ); ?>
            </a>
          </li>

          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-language-page", "tab" => 2, "wstep" => 3 ), admin_url() . "admin.php" ) ) ?>">
              <?php _e( 'This plugin', 'ceceppaml' ); ?>
            </a>
          </li>

        </ul>
      </p>

      <p>
        <strong>
          <?php _e( 'or', 'ceceppaml' ) ?>:
        </strong>
        <ul class="cml-ul-list">
          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-flags-page", "wstep" => 3 ), admin_url() . "admin.php" ) ); ?>">
              <?php _e( 'Enable flags on your site/pages', 'ceceppaml' ) ?>
            </a>
          </li>
          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "wstep" => 3 ), admin_url() . "widgets.php" ) ); ?>">
              <?php _e( 'Filter widgets by language', 'ceceppaml' ) ?>
            </a>
          </li>
          <li>
            <a href="<?php echo esc_url( add_query_arg( array( "page" => "ceceppaml-options-page", "wstep" => 3 ), admin_url() . "admin.php" ) ); ?>">
              <?php _e( 'See all available options', 'ceceppaml' ) ?>
            </a>
          </li>
        </ul>
      </p>
      <div class="cml-skip">
        <?php
          echo '<a class="button button-primary" href="' . esc_url( add_query_arg( array( "wdone" => 1 ) ) ) . '">';
          _e( 'Done, close wizard', 'ceceppaml' );
          echo "</a>";
        ?>
      </div>
    </div>
  </div>
</div>

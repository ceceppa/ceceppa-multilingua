<?php

function cml_about_me_box_content() {
?>
    <div class="content">
      <div class="cml-about-me">
        <img src="<?php echo CML_PLUGIN_IMAGES_URL ?>/me.jpg" alt="" border="" />
        <span>
          Alessandro Senese <br />

          <ul class="social-buttons">
            <li class="link-blog" style="background-position: 0px 0px;"><a href="http://www.alessandrosenese.eu/" class="tipsy-s" tipsy-offsetx="-5" tipsy-offsety="-5" title="My blog" target="_blank"></a></li>
            <li class="link-twitter" style="background-position: 0px 0px;"><a href="http://twitter.com/ceceppa" class="tipsy-s" tipsy-offsetx="-5" tipsy-offsety="-5" title="Twitter" target="_blank"></a></li>
            <li class="link-gplus" style="background-position: 0px 0px;"><a href="http://plus.google.com/117704556176768949212" class="tipsy-s" tipsy-offsetx="-5" tipsy-offsety="-5" rel="author" title="Google+" target="_blank"></a></li>
            <li class="link-linkedin" style="background-position: 0px 0px;"><a href="http://lnkd.in/_43NKB" class="tipsy-s" tipsy-offsetx="-5" tipsy-offsety="-5" title="LinkedIn" target="_blank"></a></li>
          </ul>
        </span> 
      </div>
      <?php _e('Thanks for using Ceceppa Multilingua', 'ceceppaml') ?> :)
    </div>
<?php
}

function cml_donate_box_content() {
?>
    <div class="content">
      <?php _e('If you like this plugin, please donate to support development and maintenance', 'ceceppaml') ?>

      <div class="method">
        <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G22CM5RA4G4KG">
          <img src="https://www.paypalobjects.com/it_IT/IT/i/btn/btn_donateCC_LG.gif" alt="PayPal - Il metodo rapido, affidabile e innovativo per pagare e farsi pagare.">
        </a>
      </div>
      
      <div class="method">
        <a href="https://flattr.com/submit/auto?user_id=ceceppa&url=http%3A%2F%2Fwww.alessandrosenese.eu%2Fmyworks%2Fceceppa-multilingua" target="_blank"><img src="//api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0"></a>
      </div>
    </div>
<?php
}

function cml_support_box_content() {
?>
  <div class="content">
    <ul class="cml-donate">
      <li>
        <a href="http://wordpress.org/plugins/ceceppa-multilingua/" target="_blank"><?php printf( __( "Rate Ceceppa Multilingua 5%s's on WordPress.org", 'ceceppaml' ), '★' ) ?></a>
      </li>
      <li><?php _e( 'Talk about it on your site and link back to the', 'ceceppaml' ) ?>
        <a href="http://www.alessandrosenese.eu/myworks/ceceppa-multilingua/" target="_blank"><?php _e( 'plugin page.', 'ceceppaml' ) ?></a>
      </li>
      <li>
        <a href="http://twitter.com/home?status=<?php echo urlencode( __( 'I use Ceceppa Multilingua for WordPress and you should too', 'ceceppaml' ) . ". http://goo.gl/EOv4sL" ) ?>" target="_blank">
          <?php _e( 'Tweet about it.', 'ceceppaml' ) ?>
        </a>
      </li>
      <li>
        <a href="#" target="_blank">
          <?php _e( 'Translate it in your language.', 'ceceppaml' ) ?>
        </a>
      </li>
      <li>
        <?php printf( __( "Don't hesitate to report bugs or ask for new features on the <%s>support forum.</a>", 'ceceppaml' ), 'a href="http://wordpress.org/plugins/ceceppa-multilingua/" target="_blank"' ) ?>
      </li>
      <li>
        <?php printf( __( 'Subscribe to official <%s>Google + page</a>,', 'ceceppaml' ), 'a href="https://plus.google.com/u/0/b/104807878923864213031/104807878923864213031/posts" target="_blank"' ) ?> <br />
        <?php _e( 'I use to announce development versions and then, test the new versions and report bugs before the final release.', 'ceceppaml' ) ?>
      </li>
      <li>
        <a href="https://github.com/ceceppa/ceceppa-multilingua" target="_blank">
          <?php _e( 'Development code is available on github', 'ceceppaml' ) ?>
        </a>
      </li>
    </ul>
  </div>
<?php
}

function cml_help_box_content() {
?>
    <div class="content">
        <ul class="cml-donate">
          <li>
              <a target="_blank" href="http://www.alessandrosenese.eu/en/ceceppa-multilingua/documentation">
                <?php _e( 'Setting up a WordPress multilingual site with Ceceppa Multilingua', 'ceceppaml' ); ?>
              </a>
            </li>
            <li>
              <a href="<?php echo admin_url() ?>admin.php?page=ceceppaml-api-page">
                <?php _e( 'Api', 'ceceppaml' ); ?>
              </a>
            </li>
        </ul>
    </div>
<?php
}

add_meta_box("cml_about_me_box", __( 'About Ceceppa:', 'ceceppaml' ), 'cml_about_me_box_content', "cml_donate_box");

add_meta_box("cml_donate_box", __('Donate:', 'ceceppaml'), 'cml_donate_box_content', "cml_donate_box");
add_meta_box("cml_support_box", __('Contribute:', 'ceceppaml'), 'cml_support_box_content', "cml_donate_box");

add_meta_box("cml_help_box", __('Documentation:', 'ceceppaml'), 'cml_help_box_content', "cml_donate_box");

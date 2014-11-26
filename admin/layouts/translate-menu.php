<?php
require_once ( CML_PLUGIN_ADMIN_PATH . 'admin-menu.php' );

$args = array(
              'walker' => new CML_Walker_Nav_Menu_Edit(),
              );

wp_nav_menu( $args );
?>
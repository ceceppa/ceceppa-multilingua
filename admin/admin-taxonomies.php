<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

if( CML_CREATE_CATEGORY_AS == CML_CATEGORY_CREATE_NEW ) {
  require_once( "admin-taxonomies-store.php" );
} else {
  require_once( "admin-taxonomies-strings.php" );
}
?>
<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/*
 * Download wordpress mo file
 */
function cml_download_mo_file( $locale ) {
  global $wp_version;

  if( empty( $locale ) ) return;

  //php version
  $version = substr($wp_version, 0, 3);

  //Files to download
  $mini = substr( $locale, 0, 2 );
  if( strtolower( $mini ) == "en" ) return;

  $files = array( $locale, "admin-$locale", $mini, "admin-$mini" );
  foreach( $files as $file ) {
    if( cml_download_mo_file_check( $locale ) ) continue;

    //where download files?
    if( ! $fp = @fopen( "http://svn.automattic.com/wordpress-i18n/$file/branches/$version/messages/$file.mo", "r" ) ) {
      if( ! $fp = @fopen("http://svn.automattic.com/wordpress-i18n/$file/trunk/messages/$file.mo", "r") ) {
        continue;
      }
    }

    if( is_resource( $fp ) ) {
      //Provo ad aprire il file in uscita
      $out = CML_WP_LOCALE_DIR . "/$file.mo";

      if( ! $fo = @fopen( $out, "w" ) ) {
    	return;
      }

      while( ! feof( $fp ) ) {
        // try to get some more time
        @set_time_limit( 30 );
        $fc = fread( $fp, 8192 );
        fwrite($fo, $fc);
      }

      fclose( $fp );
      fclose( $fo );
    }
  }
}

/*
 * Check if language file is already installed
 */
function cml_download_mo_file_check( $locale ) {
  $moFile = CML_WP_LOCALE_DIR . "/$locale.mo";

  return file_exists( $moFile ) && filesize( $moFile ) > 0;
}
?>
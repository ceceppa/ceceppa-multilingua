<?php
function getAllFilesFrom( $dir, $ext, $files = array() ) { 
  if( !($res=opendir( $dir ) ) ) return;

  while( ( $file = readdir ( $res ) ) == TRUE )
    if( $file != "." && $file != ".." )
      if( is_dir ( "$dir/$file" ) ) :
	$files = getAllFilesFrom( "$dir/$file", $ext, $files );
      else:
	$info = pathinfo( "$dir/$file" );

	if( strtolower( $info['extension'] ) == strtolower( $ext ) ) :
	  array_push( $files, "$dir/$file" ); 
	endif;
      endif;
      
  closedir($res); 

  return $files; 
}

function cml_parse_mo_files( $path ) {
  //Search for all *.po/*.mo
  $loc = getAllFilesFrom( $path, 'po' );
  $loc = array_merge( $loc, getAllFilesFrom( $path, 'mo' ) );
  
  $files = getAllFilesFrom( $path, "php" );
  foreach( $files as $filename ) {
    $content = file_get_contents( $filename );
    
    preg_match_all ( '/(_e|__|esc_html_e|esc_attr__|esc_html__)\((.*?)\)/', $content, $matches );
  
    //'valore', 'textdomain'
    $m = end( $matches );
    $domain = 0;
    foreach( $m as $line ) {
      preg_match_all( '/^[\'\"](.*)[\'\"][,](.*)[\'\"]$/', trim( $line ), $string );
  
      if( count( $string ) > 1 ) {
        $text = end( $string[ 1 ] );
        $domain = end( $string[ 2 ] );
    
        //Rimuovo gli apici iniziali e finali :)
        if( ! empty( $text ) ) {
          $domains[ $domain ][] = $text;
        }
      }
    }; //$m as $line
  } //endforeach;
  
  return $domains;
}
?>
<?php

add_action( 'admin_enqueue_scripts', 'cml_wp_pointer_load', 1000 );
 
/*
 * Add custom pointer
 *
 * @param $target - target element
 * @param $title - pointer title
 * @param $text - pointer content
 * @param $position - array containing pointer position
 */
function cml_add_pointer( $target, $title, $text, $position = array( 'edge' => 'top', 'align' => 'middle' ) ) {
  if( ! isset( $GLOBALS[ 'cml_pointers' ] ) ) $GLOBALS[ 'cml_pointers' ] = array();

  $screen = get_current_screen();
  $screen_id = $screen->id;

  $GLOBALS[ 'cml_pointers_' . $screen_id ][] = array( "target" => $target,
                                                      "title" => $title,
                                                      "content" => $text,
                                                      "position" => $position );

  //Register custom hook
  if( ! isset( $GLOBALS[ 'cml_pointer_registered_' . $screen_id ] ) ) {
    add_filter( 'cml_wp_pointer_load-' . $screen_id, 'cml_register_pointer' );

    $GLOBALS[ 'cml_pointer_registered_' . $screen_id ] = 1;
  }
}

function cml_register_pointer( $p ) {
  $screen = get_current_screen();
  $screen_id = $screen->id;

  $pointers = $GLOBALS[ 'cml_pointers_' . $screen_id ];
  foreach( $pointers as $key => $pointer ) {
    $target = $pointer[ 'target' ];
    $p[ 'cml_p' . sanitize_title( $target ) ] = array(
         'target' => $pointer[ 'target' ],
         'options' => array(
             'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
                 $pointer[ 'title' ],
                 $pointer[ 'content' ]
             ),
             'position' => $pointer[ 'position' ]
         )
     );
  }

  return $p;
}

function cml_wp_pointer_load( $hook_suffix ) {
  // Don't run on WP < 3.3
  if ( get_bloginfo( 'version' ) < '3.3' )
      return;

  $screen = get_current_screen();
  $screen_id = $screen->id;

  // Get pointers for this screen
  $pointers = apply_filters( 'cml_wp_pointer_load-' . $screen_id, array() );

  if ( ! $pointers || ! is_array( $pointers ) )
      return;

  // Get dismissed pointers
  $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
  $valid_pointers =array();
  $dismissed_pointers = array();

  // Check pointers and remove dismissed ones.
  foreach ( $pointers as $pointer_id => $pointer ) {
      $_dismissed = false;

      // Sanity check
      if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
        if( FALSE === strpos( $pointer_id, "cml_" ) ) {
          continue;
        }
        
        $_dismissed = true;
      }

      $pointer['pointer_id'] = $pointer_id;

      // Add the pointer to $valid_pointers array
      if( ! $_dismissed )
        $valid_pointers['pointers'][] =  $pointer;
      else
        $dismissed_pointers['pointers'][] =  $pointer;
  }

  // No valid pointers? Stop here.
  if ( empty( $valid_pointers ) && empty( $dismissed_pointers ) )
      return;

  if( empty( $dismissed_pointers ) )
    $dismissed_pointers = $valid_pointers;

  // Add pointers style to queue.
  wp_enqueue_style( 'wp-pointer' );

  wp_enqueue_script( 'ceceppaml-pointers', CML_PLUGIN_JS_URL . 'admin.pointers.js', array( 'wp-pointer' ) );

  // Add pointer options to script.
  wp_localize_script( 'ceceppaml-pointers', 'cmlPointer', array( "valid" => $valid_pointers,
                                                                "dismissed" => $dismissed_pointers ) );
}

?>
<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/**
 * required for php < 5.3.0
 */
if ( ! function_exists( 'array_replace' ) ) {
  function array_replace( &$array, &$array1 ) {
    $args = func_get_args();
    $count = func_num_args();
    for ($i = 0; $i < $count; ++$i) {
      if (is_array($args[$i])) {
        foreach ($args[$i] as $key => $val) {
          $array[$key] = $val;
        }
      }
      else {
//         trigger_error(
//           __FUNCTION__ . '(): Argument #' . ($i+1) . ' is not an array',
//           E_USER_WARNING
//         );
        return NULL;
      }
    }

    return $array;
  }
} 

?>
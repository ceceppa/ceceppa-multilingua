<?php
wp_enqueue_script( 'plugin-install' );
add_thickbox();

function cml_admin_box_addons() {
?>
  <div id="minor-publishing">
	<?php _e( 'Available addons', 'ceceppaml' ) ?>
	<div class="cml-box-right" style="padding-top: 5px">
		<a href="<?php echo esc_url( add_query_arg( array( "update" => 1 ) ) ) ?>">
			<?php _e( 'Update available addons list', 'ceceppaml' ); ?>
		</a>
	</div>

	<ul class="cml-addons">
	<?php
		$filename = CML_UPLOAD_DIR . "cmladdons.txt" ;
		$addons = @file_get_contents( $filename );

		//Download available addons list
		$mtime = @filemtime( $filename ) * ( 60 * 60 * 24 );
		if( isset( $_GET[ 'update' ] ) ||
			! file_exists( $filename ) ||
			$mtime < mktime() ) {
			$addons = @file_get_contents( 'http://alessandrosenese.eu/cmladdons.txt' );

			@file_put_contents( $filename, $addons );
		}

		$lines = explode( "\n", $addons );

		$out = "";
        $name = "";
		$id = 0;
		foreach( $lines as $line ) {
			if( preg_match( "/!--(.*)/", $line, $match ) ) {
				$plugin = end( $match );
				$class = is_plugin_active( $plugin ) ? "active" : "";
				$out .= '<li class="cml-addon ' . $class . '">';
			} else if( "--!" == $line ) {
				$out .= '</li>';

				$id++;
			} else {
				if( preg_match( "/Name:(.*)/", $line, $names ) ) {
                  $name = end( $names );
				  $out .= '<div class="name">' . end( $names ) . '</div>';
				}

				if( preg_match( "/Description:(.*)/", $line, $descr ) )
					$out .= '<div class="description">' . end( $descr ) . '</div>';

				if( preg_match( "/Url:(.*)/", $line, $urls ) ) {
					$url = end( $urls );
					$out .= '<div class="links">';
					if( ! empty( $url ) ) {
						$out .= '<a href="' . $url . '" target="_blank">';
						$out .= 'About';
						$out .= '</a>';
					}
				}

				if( preg_match( "/Wp:(.*)/", $line, $gits ) ) {
					$out .= '<span>|</span>';

					$git = end( $gits );
					if( ! empty( $git ) ) {
                        $plugin_slug = untrailingslashit( str_replace( "http://wordpress.org/plugins/", "", $git ) );
                        $out .= '<a href="' . esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug .
                                    '&from=admin&TB_iframe=true&width=600&height=550' ) ) . '" class="thickbox" title="' .
                                    esc_attr( $name ) . '">Wordpress</a>';
					} else {
                      $out .= "--";
                    }
				}

				if( preg_match( "/Git:(.*)/", $line, $gits ) ) {
					$out .= '<span>|</span>';

					$git = end( $gits );
					if( ! empty( $git ) ) {
						$out .= '<a href="' . $git . '" target="_blank">';
						$out .= 'Git';
						$out .= '</a>';
					}
					$out .= '</div>';
				}
			}
		}

		echo $out;
	?>
	</ul>
  </div>
<?php
}

add_meta_box( 'cml-box-addons', __( 'Addons', 'ceceppaml' ), 'cml_admin_box_addons', 'cml_box_addons' );
?>

<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/**
 * Add Photographer Name and URL fields to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
 
function cml_attachment_field_edit( $form_fields, $post ) {
	$meta = get_post_meta( $post->ID, '_cml_media_meta', true );

    $mini = isset( $_POST[ 'query-attachments' ] );

	foreach( CMLLanguage::get_no_default() as $lang ) {
		$image = CMLLanguage::get_flag_img( $lang->id ) . " ";
		// $form_fields[ 'cml-media-caption-' . $lang->id ] = array(
		// 	'label' => $image . __( 'Caption' ),
		// 	'input' => 'textarea',
		// 	'value' => @$meta[ 'cml-media-caption-' . $lang->id ],
		// 	'helps' => '',
		// );

        $text = ( ! $mini ) ? __( 'Alternative Text' ) : __( 'Alt Text' );

        $form_fields[ 'cml-media-title-' . $lang->id ] = array(
            'label' => $image . __( 'Title' ),
            'input' => 'text',
            'value' => @$meta[ 'title-' . $lang->id ],
            'helps' => '',
        );
        
		if ( 'image' === substr( $post->post_mime_type, 0, 5 ) ) {
			$form_fields[ 'cml-media-alternative-' . $lang->id ] = array(
				'label' => $image . $text,
				'input' => 'text',
				'value' => @$meta[ 'alternative-' . $lang->id ],
				'helps' => '',
			);
		}

        
		// $form_fields[ 'cml-media-description-' . $lang->id ] = array(
		// 	'label' => $image . __( 'Description' ),
		// 	'input' => 'textarea',
		// 	'value' => @$meta[ 'cml-media-description-' . $lang->id ],
		// 	'helps' => '',
		// );

	}

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'cml_attachment_field_edit', 10, 2 );

/**
 * Save values of Photographer Name and URL in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function cml_attachment_field_save( $post, $attachment ) {
	$meta = array();

	foreach( CMLLanguage::get_no_default() as $lang ) {
		if( isset( $attachment[ 'cml-media-caption-' . $lang->id ] ) ) {
			$meta[ 'caption-' . $lang->id ] = $attachment[ 'cml-media-caption-' . $lang->id ];
		}

		if( isset( $attachment[ 'cml-media-alternative-' . $lang->id ] ) ) {
			$meta[ 'alternative-' . $lang->id ] = $attachment[ 'cml-media-alternative-' . $lang->id ];
		}

		if( isset( $attachment[ 'cml-media-description-' . $lang->id ] ) ) {
			$meta[ 'description-' . $lang->id ] = $attachment[ 'cml-media-description-' . $lang->id ];
		}

		if( isset( $_POST[ "cml-media-title-" . $lang->id ] ) ) {
			$meta[ 'title-' . $lang->id ] = $_POST[ "cml-media-title-$lang->id" ];
		}

		if( isset( $attachment[ "cml-media-title-" . $lang->id ] ) ) {
			$meta[ 'title-' . $lang->id ] = $attachment[ "cml-media-title-$lang->id" ];
		}
	}

	update_post_meta( $post[ 'ID' ], '_cml_media_meta', $meta );

	return $post;
}

add_filter( 'attachment_fields_to_save', 'cml_attachment_field_save', 10, 2 );

function cml_media_send_to_editor( $html, $id, $caption ) {

  if( preg_match( '/alt="([^\"]+)/', $html, $out ) ) {
    $scode = 'alt="[cml_media_alt id=\'' . $id . '\']' . end( $out ) . '[/cml_media_alt]"';

    return preg_replace( '/alt="[^\"]+./', $scode, $html );
  }

  return $html;
}

if( ! isset( $GLOBALS[ '_cml_settings' ][ 'cml_option_translate_media' ] ) ) {
  $translate = 1;
} else {
  
  $translate = $GLOBALS[ '_cml_settings' ][ 'cml_option_translate_media' ];
}

if( $translate == 1 ) {
  add_filter( 'media_send_to_editor', 'cml_media_send_to_editor', 20, 3 );
}

//Allow image title translation
function cml_insert_title_translation_fields( $post ) {
	if( 'attachment' !== $post->post_type ) return;

	foreach( CMLLanguage::get_no_default() as $lang ) {
		$label = sprintf( __( 'Title in %s', 'ceceppaml' ), $lang->cml_language );

		$img = CMLLanguage::get_flag_src( $lang->id );

		$meta = get_post_meta( $post->ID, "_cml_media_meta", true );

		$title = isset( $meta[ 'title-' . $lang->id ] ) ? $meta[ 'title-' . $lang->id ] : "";

echo <<< EOT
<div id="titlewrap" class="cml-hidden cml-titlewrap">
	<img class="tipsy-s" title="$label" src="$img" />
	<label class="" id="title-prompt-text" for="title_$lang->id">$label</label>
	<input type="text" class="cml-title" name="cml-media-title-$lang->id" size="30" id="title_$lang->id" autocomplete="off" value="$title"/>
</div>
EOT;
	}
}
	
function cml_delete_media_meta( $id ) {
	delete_post_meta( $id, "_cml_media_meta" );
}

add_action( 'edit_form_after_title', 'cml_insert_title_translation_fields', 10, 1 );
?>
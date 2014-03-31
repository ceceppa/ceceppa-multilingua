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

	foreach( CMLLanguage::get_no_default() as $lang ) {
		$image = CMLLanguage::get_flag_img( $lang->id ) . " ";
		$form_fields[ 'cml-media-caption-' . $lang->id ] = array(
			'label' => $image . __( 'Caption' ),
			'input' => 'textarea',
			'value' => @$meta[ 'cml-media-caption-' . $lang->id ],
			'helps' => '',
		);

		if ( 'image' === substr( $post->post_mime_type, 0, 5 ) ) {
			$form_fields[ 'cml-media-alternative-' . $lang->id ] = array(
				'label' => $image . __( 'Alternative Text' ),
				'input' => 'text',
				'value' => @$meta[ 'cml-media-alternative-' . $lang->id ],
				'helps' => '',
			);
		}

		$form_fields[ 'cml-media-description-' . $lang->id ] = array(
			'label' => $image . __( 'Description' ),
			'input' => 'textarea',
			'value' => @$meta[ 'cml-media-description-' . $lang->id ],
			'helps' => '',
		);

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
			$meta[ 'cml-media-caption-' . $lang->id ] = $attachment[ 'cml-media-caption-' . $lang->id ];
		}

		if( isset( $attachment[ 'cml-media-alternative-' . $lang->id ] ) ) {
			$meta[ 'cml-media-alternative-' . $lang->id ] = $attachment[ 'cml-media-alternative-' . $lang->id ];
		}

		if( isset( $attachment[ 'cml-media-description-' . $lang->id ] ) ) {
			$meta[ 'cml-media-description-' . $lang->id ] = $attachment[ 'cml-media-description-' . $lang->id ];
		}
	}

	update_post_meta( $post[ 'ID' ], '_cml_media_meta', $meta );

	return $post;
}

add_filter( 'attachment_fields_to_save', 'cml_attachment_field_save', 10, 2 );

function my_filter_iste( $html, $id, $caption ) {
	$meta = get_post_meta( $id, '_cml_media_meta', true );
    $attachment = get_post( $id ); //fetching attachment by $id passed through

    //Get image language
    $lang = CMLLanguage::get_id_by_post_id( $id );
    // $mime_type = $attachment->post_mime_type; //getting the mime-type
    // if ( 'video' == substr( $mime_type, 0, 5 ) ) { //checking mime-type
    //     $src = wp_get_attachment_url( $id );
    //     $html = '[video src="'.$src.'"]';  
    // }

	if ( 'image' === substr( $attachment->post_mime_type, 0, 5 ) ) {
		$alt = $meta[ 'cml-media-alternative-' . $lang ];
	}

    error_log( print_r( $html, true ) );
    return $html; // return new $html
}

add_filter( 'media_send_to_editor', 'my_filter_iste', 20, 3 );
?>
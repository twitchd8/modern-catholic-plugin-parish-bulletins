<?php
/**
 * Bulletin post type and metadata.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Bulletin post type and its structured fields.
 */
function parish_bulletins_register_post_type() {
	$labels = array(
		'name'                     => __( 'Bulletins', 'parish-bulletins' ),
		'singular_name'            => __( 'Bulletin', 'parish-bulletins' ),
		'add_new'                  => __( 'Add New', 'parish-bulletins' ),
		'add_new_item'             => __( 'Add New Bulletin', 'parish-bulletins' ),
		'edit_item'                => __( 'Edit Bulletin', 'parish-bulletins' ),
		'new_item'                 => __( 'New Bulletin', 'parish-bulletins' ),
		'view_item'                => __( 'View Bulletin', 'parish-bulletins' ),
		'view_items'               => __( 'View Bulletins', 'parish-bulletins' ),
		'search_items'             => __( 'Search Bulletins', 'parish-bulletins' ),
		'not_found'                => __( 'No bulletins found.', 'parish-bulletins' ),
		'not_found_in_trash'       => __( 'No bulletins found in Trash.', 'parish-bulletins' ),
		'all_items'                => __( 'All Bulletins', 'parish-bulletins' ),
		'archives'                 => __( 'Bulletin Archives', 'parish-bulletins' ),
		'attributes'               => __( 'Bulletin Attributes', 'parish-bulletins' ),
		'featured_image'           => __( 'Bulletin Thumbnail', 'parish-bulletins' ),
		'set_featured_image'       => __( 'Set bulletin thumbnail', 'parish-bulletins' ),
		'remove_featured_image'    => __( 'Remove bulletin thumbnail', 'parish-bulletins' ),
		'use_featured_image'       => __( 'Use as bulletin thumbnail', 'parish-bulletins' ),
		'insert_into_item'         => __( 'Insert into bulletin', 'parish-bulletins' ),
		'uploaded_to_this_item'    => __( 'Uploaded to this bulletin', 'parish-bulletins' ),
		'item_published'           => __( 'Bulletin published.', 'parish-bulletins' ),
		'item_published_privately' => __( 'Bulletin published privately.', 'parish-bulletins' ),
		'item_reverted_to_draft'   => __( 'Bulletin reverted to draft.', 'parish-bulletins' ),
		'item_scheduled'           => __( 'Bulletin scheduled.', 'parish-bulletins' ),
		'item_updated'             => __( 'Bulletin updated.', 'parish-bulletins' ),
		'menu_name'                => __( 'Bulletins', 'parish-bulletins' ),
		'name_admin_bar'           => __( 'Bulletin', 'parish-bulletins' ),
	);

	register_post_type(
		'parish_bulletin',
		array(
			'labels'             => $labels,
			'public'             => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-media-document',
			'menu_position'      => 21,
			'has_archive'        => 'bulletins',
			'rewrite'            => array(
				'slug'       => 'bulletins',
				'with_front' => false,
			),
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'show_in_nav_menus'  => true,
			'exclude_from_search'=> false,
			'publicly_queryable' => true,
			'map_meta_cap'       => true,
			'template'           => array(
				array(
					'core/paragraph',
					array(
						'placeholder' => __( 'Optional: add e-bulletin content here when you are ready to publish more than the PDF.', 'parish-bulletins' ),
					),
				),
			),
		)
	);

	register_post_meta(
		'parish_bulletin',
		'_parish_bulletin_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'parish_bulletins_sanitize_date',
			'show_in_rest'      => true,
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'parish_bulletin',
		'_parish_bulletin_pdf_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'auth_callback'     => static function () {
				return current_user_can( 'upload_files' );
			},
		)
	);
}

/**
 * Accepts only real calendar dates in WordPress's storage format.
 *
 * @param mixed $value Candidate date.
 * @return string
 */
function parish_bulletins_sanitize_date( $value ) {
	$value = sanitize_text_field( (string) $value );
	$date  = DateTimeImmutable::createFromFormat( '!Y-m-d', $value );

	return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';
}

/**
 * Gets a bulletin's stored date, falling back to its publication date.
 *
 * @param int|WP_Post $post Bulletin post or ID.
 * @param string      $format Optional PHP date format.
 * @return string
 */
function parish_bulletins_get_date( $post, $format = 'Y-m-d' ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	$value = get_post_meta( $post->ID, '_parish_bulletin_date', true );
	$date  = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $value, wp_timezone() );

	if ( ! $date ) {
		return get_the_date( $format, $post );
	}

	return wp_date( $format, $date->getTimestamp(), wp_timezone() );
}

/**
 * Gets the selected PDF attachment and confirms it is still a PDF.
 *
 * @param int|WP_Post $post Bulletin post or ID.
 * @return WP_Post|null
 */
function parish_bulletins_get_pdf( $post ) {
	$post   = get_post( $post );
	$pdf_id = $post ? absint( get_post_meta( $post->ID, '_parish_bulletin_pdf_id', true ) ) : 0;
	$pdf    = $pdf_id ? get_post( $pdf_id ) : null;

	if ( ! $pdf || 'attachment' !== $pdf->post_type || 'application/pdf' !== get_post_mime_type( $pdf ) ) {
		return null;
	}

	return $pdf;
}

add_action( 'init', 'parish_bulletins_register_post_type' );

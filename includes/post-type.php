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
 * Keeps discussion closed whenever a Bulletin is created or updated.
 *
 * This protects Bulletins even if the site's default discussion setting is
 * changed or a client submits post data through the REST API.
 *
 * @param array $data    Sanitized post data.
 * @param array $postarr Raw post data.
 * @return array
 */
function parish_bulletins_close_saved_discussion( $data, $postarr ) {
	if ( isset( $data['post_type'] ) && 'parish_bulletin' === $data['post_type'] ) {
		$data['comment_status'] = 'closed';
		$data['ping_status']    = 'closed';
	}

	return $data;
}

/**
 * Prevents comments or pingbacks from being opened for a Bulletin.
 *
 * @param bool $open    Whether discussion is currently open.
 * @param int  $post_id Post ID.
 * @return bool
 */
function parish_bulletins_discussion_open( $open, $post_id ) {
	return 'parish_bulletin' === get_post_type( $post_id ) ? false : $open;
}

/**
 * Hides any legacy comments that may have been associated with a Bulletin.
 *
 * @param WP_Comment[] $comments Comments for the current post.
 * @param int          $post_id  Post ID.
 * @return WP_Comment[]
 */
function parish_bulletins_hide_comments( $comments, $post_id ) {
	return 'parish_bulletin' === get_post_type( $post_id ) ? array() : $comments;
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
 * Builds the canonical Bulletin title from a stored Bulletin Date.
 *
 * @param mixed $value Candidate date in Y-m-d format.
 * @return string Generated title or an empty string for an invalid date.
 */
function parish_bulletins_get_title_from_date( $value ) {
	$value = parish_bulletins_sanitize_date( $value );
	if ( ! $value ) {
		return '';
	}

	$timezone = wp_timezone();
	$date     = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, $timezone );

	return sprintf(
		/* translators: %s: Bulletin date such as July 5th, 2026. */
		__( 'Bulletin - %s', 'parish-bulletins' ),
		wp_date( 'F jS, Y', $date->getTimestamp(), $timezone )
	);
}

/**
 * Keeps the stored post title synchronized with the Bulletin Date.
 *
 * @param int $post_id Bulletin post ID.
 */
function parish_bulletins_sync_title( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || 'parish_bulletin' !== get_post_type( $post_id ) ) {
		return;
	}

	$title = parish_bulletins_get_title_from_date(
		get_post_meta( $post_id, '_parish_bulletin_date', true )
	);

	if ( ! $title || $title === get_post_field( 'post_title', $post_id ) ) {
		return;
	}

	wp_update_post(
		array(
			'ID'         => $post_id,
			'post_title' => $title,
		)
	);
}

/**
 * Synchronizes the title as soon as the Bulletin Date metadata changes.
 *
 * @param int    $meta_id    Metadata row ID.
 * @param int    $post_id    Bulletin post ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value New metadata value.
 */
function parish_bulletins_sync_title_after_date_change( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_parish_bulletin_date' !== $meta_key ) {
		return;
	}

	parish_bulletins_sync_title( $post_id );
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

/**
 * Gets a visitor-facing title, even when an editor leaves the title empty.
 *
 * @param int|WP_Post $post Bulletin post or ID.
 * @return string
 */
function parish_bulletins_get_display_title( $post ) {
	$post  = get_post( $post );
	$title = $post ? trim( get_the_title( $post ) ) : '';

	if ( ! $post ) {
		return '';
	}

	if ( $title ) {
		return $title;
	}

	$date  = get_post_meta( $post->ID, '_parish_bulletin_date', true );
	$title = parish_bulletins_get_title_from_date( $date );

	if ( $title ) {
		return $title;
	}

	return sprintf(
		/* translators: %s: formatted Bulletin date. */
		__( 'Bulletin - %s', 'parish-bulletins' ),
		parish_bulletins_get_date( $post, 'F jS, Y' )
	);
}

/**
 * Gets the manual featured image or the PDF's generated first-page preview.
 *
 * @param int|WP_Post $post Bulletin post or ID.
 * @param string      $size Registered image size.
 * @return string Image HTML or an empty string.
 */
function parish_bulletins_get_thumbnail_html( $post, $size = 'medium_large' ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	if ( has_post_thumbnail( $post ) ) {
		return get_the_post_thumbnail(
			$post,
			$size,
			array( 'class' => 'parish-bulletin-thumbnail' )
		);
	}

	$pdf = parish_bulletins_get_pdf( $post );
	if ( ! $pdf ) {
		return '';
	}

	return wp_get_attachment_image(
		$pdf->ID,
		$size,
		false,
		array(
			'class' => 'parish-bulletin-thumbnail parish-bulletin-thumbnail--pdf',
			'alt'   => sprintf(
				/* translators: %s: bulletin title. */
				__( 'First page of %s', 'parish-bulletins' ),
				parish_bulletins_get_display_title( $post )
			),
		)
	);
}

add_action( 'init', 'parish_bulletins_register_post_type' );
add_filter( 'wp_insert_post_data', 'parish_bulletins_close_saved_discussion', 10, 2 );
add_filter( 'comments_open', 'parish_bulletins_discussion_open', 10, 2 );
add_filter( 'pings_open', 'parish_bulletins_discussion_open', 10, 2 );
add_filter( 'comments_array', 'parish_bulletins_hide_comments', 10, 2 );
add_action( 'save_post_parish_bulletin', 'parish_bulletins_sync_title', 30 );
add_action( 'added_post_meta', 'parish_bulletins_sync_title_after_date_change', 10, 4 );
add_action( 'updated_post_meta', 'parish_bulletins_sync_title_after_date_change', 10, 4 );

<?php
/**
 * Post meta and term meta: field schema, rendering, sanitising and getters.
 *
 * Everything the templates display is defined here, so adding a field to the
 * directory is a one-line change to p10_profile_field_schema().
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field schema for a profile.
 *
 * Supported types: text, number, textarea, select, checkbox, radio, url,
 * email, tel, gallery, tags (comma separated), rich (wp_editor).
 *
 * @return array
 */
function p10_profile_field_schema() {
	$schema = array(

		'headline'   => array(
			'label' => __( 'Card headline', 'perfect10' ),
			'type'  => 'text',
			'group' => 'basics',
			'hint'  => __( 'Short line shown on the card, e.g. “Los Angeles · British”. Leave blank to build it automatically.', 'perfect10' ),
		),
		'age'        => array(
			'label' => __( 'Age', 'perfect10' ),
			'type'  => 'number',
			'group' => 'basics',
			'attrs' => array( 'min' => 18, 'max' => 99 ),
		),
		'height_cm'  => array(
			'label' => __( 'Height (cm)', 'perfect10' ),
			'type'  => 'number',
			'group' => 'basics',
			'attrs' => array( 'min' => 120, 'max' => 220 ),
		),
		'rate'       => array(
			'label' => __( 'Rate from (per hour)', 'perfect10' ),
			'type'  => 'number',
			'group' => 'basics',
			'hint'  => __( 'Rendered as “from $800/hr”.', 'perfect10' ),
		),
		'rate_overnight' => array(
			'label' => __( 'Overnight rate', 'perfect10' ),
			'type'  => 'number',
			'group' => 'basics',
		),
		'rate_dinner' => array(
			'label' => __( 'Dinner date rate', 'perfect10' ),
			'type'  => 'number',
			'group' => 'basics',
		),
		'currency'   => array(
			'label'   => __( 'Currency', 'perfect10' ),
			'type'    => 'select',
			'group'   => 'basics',
			'default' => 'USD',
			'options' => array(
				'USD' => 'USD $',
				'EUR' => 'EUR €',
				'GBP' => 'GBP £',
				'CAD' => 'CAD $',
				'AUD' => 'AUD $',
			),
		),

		'premium'    => array(
			'label' => __( 'Premium placement', 'perfect10' ),
			'type'  => 'checkbox',
			'group' => 'status',
			'hint'  => __( 'Shows the “◆ Premium” badge and includes the profile in The Premium Collection.', 'perfect10' ),
		),
		'verified'   => array(
			'label'   => __( 'Verified profile', 'perfect10' ),
			'type'    => 'checkbox',
			'group'   => 'status',
			'default' => 1,
		),
		'available'  => array(
			'label'   => __( 'Availability', 'perfect10' ),
			'type'    => 'select',
			'group'   => 'status',
			'default' => 'available',
			'options' => array(
				'unknown'     => __( 'Availability not verified', 'perfect10' ),
				'available'   => __( 'Available', 'perfect10' ),
				'limited'     => __( 'Limited availability', 'perfect10' ),
				'booked'      => __( 'Fully booked', 'perfect10' ),
				'offline'     => __( 'Offline / not taking requests', 'perfect10' ),
			),
		),
		'featured_order' => array(
			'label' => __( 'Featured order', 'perfect10' ),
			'type'  => 'number',
			'group' => 'status',
			'hint'  => __( 'Lower numbers appear first in “This Month’s Muses”. 0 = not featured.', 'perfect10' ),
		),
		'verified_on' => array(
			'label' => __( 'Verified on', 'perfect10' ),
			'type'  => 'text',
			'group' => 'status',
			'hint'  => __( 'Optional date shown in the verification note.', 'perfect10' ),
		),

		'phone'      => array(
			'label' => __( 'Booking phone', 'perfect10' ),
			'type'  => 'tel',
			'group' => 'contact',
		),
		'email'      => array(
			'label' => __( 'Contact email', 'perfect10' ),
			'type'  => 'email',
			'group' => 'contact',
		),
		'external_url' => array(
			'label' => __( 'External profile URL', 'perfect10' ),
			'type'  => 'url',
			'group' => 'contact',
		),
		'contact_note' => array(
			'label' => __( 'Contact note', 'perfect10' ),
			'type'  => 'textarea',
			'group' => 'contact',
			'hint'  => __( 'Shown above the enquiry form, e.g. “Please do not call from a blocked number.”', 'perfect10' ),
		),

		'bust'       => array( 'label' => __( 'Bust', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'waist'      => array( 'label' => __( 'Waist', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'hips'       => array( 'label' => __( 'Hips', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'cup'        => array( 'label' => __( 'Cup', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'body_type'  => array( 'label' => __( 'Body type', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'hair'       => array( 'label' => __( 'Hair', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'eyes'       => array( 'label' => __( 'Eyes', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'weight_kg'  => array( 'label' => __( 'Weight (kg)', 'perfect10' ), 'type' => 'number', 'group' => 'stats' ),
		'shoes'      => array( 'label' => __( 'Shoe size', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),
		'dress'      => array( 'label' => __( 'Dress size', 'perfect10' ), 'type' => 'text', 'group' => 'stats' ),

		'languages'  => array(
			'label' => __( 'Languages', 'perfect10' ),
			'type'  => 'tags',
			'group' => 'details',
			'hint'  => __( 'Comma separated.', 'perfect10' ),
		),
		'services'   => array(
			'label' => __( 'Services / interests', 'perfect10' ),
			'type'  => 'tags',
			'group' => 'details',
			'hint'  => __( 'Comma separated. Rendered as chips on the profile.', 'perfect10' ),
		),
		'incall'     => array( 'label' => __( 'Incall available', 'perfect10' ), 'type' => 'checkbox', 'group' => 'details' ),
		'outcall'    => array( 'label' => __( 'Outcall available', 'perfect10' ), 'type' => 'checkbox', 'group' => 'details' ),
		'overnight'  => array( 'label' => __( 'Overnight available', 'perfect10' ), 'type' => 'checkbox', 'group' => 'details' ),
		'travel'     => array( 'label' => __( 'Travel companion', 'perfect10' ), 'type' => 'checkbox', 'group' => 'details' ),
		'dinner'     => array( 'label' => __( 'Dinner companion', 'perfect10' ), 'type' => 'checkbox', 'group' => 'details' ),
		'smoking'    => array(
			'label'   => __( 'Smoking', 'perfect10' ),
			'type'    => 'select',
			'group'   => 'details',
			'options' => array( '' => '—', 'no' => __( 'Non-smoker', 'perfect10' ), 'social' => __( 'Social', 'perfect10' ), 'yes' => __( 'Smoker', 'perfect10' ) ),
		),
		'drinking'   => array(
			'label'   => __( 'Drinking', 'perfect10' ),
			'type'    => 'select',
			'group'   => 'details',
			'options' => array( '' => '—', 'no' => __( 'No', 'perfect10' ), 'social' => __( 'Social', 'perfect10' ), 'yes' => __( 'Yes', 'perfect10' ) ),
		),
		'tattoos'    => array( 'label' => __( 'Tattoos', 'perfect10' ), 'type' => 'text', 'group' => 'details' ),
		'piercings'  => array( 'label' => __( 'Piercings', 'perfect10' ), 'type' => 'text', 'group' => 'details' ),

		'gallery'    => array(
			'label' => __( 'Gallery', 'perfect10' ),
			'type'  => 'gallery',
			'group' => 'media',
			'hint'  => __( 'The featured image is the cover. Add more here for the profile gallery.', 'perfect10' ),
		),
		'video_url'  => array(
			'label' => __( 'Video URL', 'perfect10' ),
			'type'  => 'url',
			'group' => 'media',
		),

		'bio_heading' => array(
			'label' => __( 'About heading', 'perfect10' ),
			'type'  => 'text',
			'group' => 'editorial',
		),
		'quote'      => array(
			'label' => __( 'Pull quote', 'perfect10' ),
			'type'  => 'textarea',
			'group' => 'editorial',
		),
		'disclaimer' => array(
			'label' => __( 'Profile disclaimer', 'perfect10' ),
			'type'  => 'textarea',
			'group' => 'editorial',
		),
	);

	return apply_filters( 'p10_profile_field_schema', $schema );
}

/**
 * Field groups, in display order.
 *
 * @return array
 */
function p10_profile_field_groups() {
	return apply_filters( 'p10_profile_field_groups', array(
		'basics'    => __( 'Basics', 'perfect10' ),
		'status'    => __( 'Placement & status', 'perfect10' ),
		'contact'   => __( 'Contact', 'perfect10' ),
		'stats'     => __( 'Measurements', 'perfect10' ),
		'details'   => __( 'Details & availability', 'perfect10' ),
		'media'     => __( 'Media', 'perfect10' ),
		'editorial' => __( 'Editorial', 'perfect10' ),
	) );
}

/**
 * Read a profile meta value with a sensible default.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key without prefix.
 * @param mixed  $default Default value.
 * @return mixed
 */
function p10_get_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, 'p10_' . $key, true );

	if ( '' === $value || null === $value ) {
		$schema = p10_profile_field_schema();
		if ( isset( $schema[ $key ]['default'] ) ) {
			return $schema[ $key ]['default'];
		}
		return $default;
	}

	return $value;
}

/**
 * Read profile meta, accepting a post object.
 *
 * @param int|WP_Post|null $post    Post.
 * @param string           $key     Meta key.
 * @param mixed            $default Default.
 * @return mixed
 */
function p10_meta( $post = null, $key = '', $default = '' ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return $default;
	}
	return p10_get_meta( $post->ID, $key, $default );
}

/**
 * Read term meta.
 *
 * @param int|WP_Term $term    Term.
 * @param string      $key     Key without prefix.
 * @param mixed       $default Default.
 * @return mixed
 */
function p10_term_meta( $term, $key, $default = '' ) {
	if ( is_object( $term ) && isset( $term->term_id ) ) {
		$term = $term->term_id;
	}
	if ( ! $term ) {
		return $default;
	}
	$value = get_term_meta( (int) $term, 'p10_' . $key, true );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/* -------------------------------------------------------------------------
 * Admin: profile meta boxes
 * ---------------------------------------------------------------------- */

/**
 * Add meta boxes.
 */
function p10_add_meta_boxes() {
	add_meta_box(
		'p10_profile_details',
		__( 'Perfect 10 — Profile Details', 'perfect10' ),
		'p10_render_profile_meta_box',
		'p10_profile',
		'normal',
		'high'
	);

	add_meta_box(
		'p10_review_details',
		__( 'Perfect 10 — Review Details', 'perfect10' ),
		'p10_render_review_meta_box',
		'p10_review',
		'normal',
		'high'
	);

	add_meta_box(
		'p10_package_details',
		__( 'Perfect 10 — Package Details', 'perfect10' ),
		'p10_render_package_meta_box',
		'p10_package',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'p10_add_meta_boxes' );

/**
 * Render one field from the schema.
 *
 * @param string $key   Field key.
 * @param array  $field Field definition.
 * @param mixed  $value Current value.
 */
function p10_render_field( $key, $field, $value ) {
	$name  = 'p10_fields[' . $key . ']';
	$id    = 'p10_field_' . $key;
	$type  = isset( $field['type'] ) ? $field['type'] : 'text';
	$attrs = '';
	if ( ! empty( $field['attrs'] ) ) {
		foreach ( $field['attrs'] as $attr => $attr_value ) {
			$attrs .= sprintf( ' %s="%s"', esc_attr( $attr ), esc_attr( $attr_value ) );
		}
	}

	echo '<div class="p10-field p10-field--' . esc_attr( $type ) . '" data-type="' . esc_attr( $type ) . '">';
	echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';

	switch ( $type ) {

		case 'textarea':
			printf(
				'<textarea class="large-text" rows="4" id="%1$s" name="%2$s"%3$s>%4$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				$attrs, // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_attr above.
				esc_textarea( $value )
			);
			break;

		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( (array) $field['options'] as $option_value => $option_label ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $option_value ),
					selected( (string) $value, (string) $option_value, false ),
					esc_html( $option_label )
				);
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf(
				'<label class="p10-field__check"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> <span>%4$s</span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( (bool) $value, true, false ),
				esc_html( $field['label'] )
			);
			break;

		case 'gallery':
			p10_render_gallery_field( $key, $value );
			break;

		case 'number':
			printf(
				'<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$s"%4$s />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$attrs // phpcs:ignore WordPress.Security.EscapeOutput
			);
			break;

		default:
			printf(
				'<input type="%1$s" class="regular-text" id="%2$s" name="%3$s" value="%4$s"%5$s />',
				esc_attr( $type ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$attrs // phpcs:ignore WordPress.Security.EscapeOutput
			);
			break;
	}

	if ( 'checkbox' !== $type && ! empty( $field['hint'] ) ) {
		echo '<p class="description">' . esc_html( $field['hint'] ) . '</p>';
	}
	echo '</div>';
}

/**
 * Render the gallery manager field.
 *
 * @param string $key   Field key.
 * @param mixed  $value Comma separated attachment IDs.
 */
function p10_render_gallery_field( $key, $value ) {
	$ids   = array_filter( array_map( 'intval', explode( ',', (string) $value ) ) );
	$name  = 'p10_fields[' . $key . ']';
	$id    = 'p10_field_' . $key;

	echo '<div class="p10-gallery-field" data-target="' . esc_attr( $id ) . '">';
	printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
	echo '<ul class="p10-gallery-field__list">';
	foreach ( $ids as $attachment_id ) {
		$image = wp_get_attachment_image( $attachment_id, 'thumbnail' );
		if ( ! $image ) {
			continue;
		}
		echo '<li data-id="' . esc_attr( $attachment_id ) . '">' . $image . '<button type="button" class="button-link p10-gallery-remove" aria-label="' . esc_attr__( 'Remove', 'perfect10' ) . '">&times;</button></li>';
	}
	echo '</ul>';
	echo '<p><button type="button" class="button p10-gallery-add">' . esc_html__( 'Add / manage gallery images', 'perfect10' ) . '</button></p>';
	echo '</div>';
}

/**
 * Render the profile meta box grouped by section.
 *
 * @param WP_Post $post Post.
 */
function p10_render_profile_meta_box( $post ) {
	wp_nonce_field( 'p10_save_profile', 'p10_profile_nonce' );

	$schema = p10_profile_field_schema();
	$groups = p10_profile_field_groups();

	echo '<div class="p10-metabox">';

	foreach ( $groups as $group_key => $group_label ) {
		echo '<fieldset class="p10-metabox__group"><legend>' . esc_html( $group_label ) . '</legend><div class="p10-metabox__grid">';

		foreach ( $schema as $key => $field ) {
			if ( ! isset( $field['group'] ) || $field['group'] !== $group_key ) {
				continue;
			}
			$default = isset( $field['default'] ) ? $field['default'] : '';
			$value   = get_post_meta( $post->ID, 'p10_' . $key, true );
			if ( '' === $value ) {
				$value = $default;
			}
			p10_render_field( $key, $field, $value );
		}

		echo '</div></fieldset>';
	}

	echo '</div>';
}

/**
 * Review meta box.
 *
 * @param WP_Post $post Post.
 */
function p10_render_review_meta_box( $post ) {
	wp_nonce_field( 'p10_save_review', 'p10_review_nonce' );

	$fields = array(
		'guest_name'     => array( 'label' => __( 'Guest name', 'perfect10' ), 'type' => 'text' ),
		'guest_location' => array( 'label' => __( 'Guest location', 'perfect10' ), 'type' => 'text', 'hint' => __( 'e.g. Beverly Hills', 'perfect10' ) ),
		'guest_label'    => array( 'label' => __( 'Guest label', 'perfect10' ), 'type' => 'text', 'default' => __( 'Verified Guest', 'perfect10' ) ),
		'rating'         => array( 'label' => __( 'Rating (1–5)', 'perfect10' ), 'type' => 'number', 'default' => 5, 'attrs' => array( 'min' => 1, 'max' => 5 ) ),
		'review_date'    => array( 'label' => __( 'Review date', 'perfect10' ), 'type' => 'text' ),
		'featured'       => array( 'label' => __( 'Feature on the homepage', 'perfect10' ), 'type' => 'checkbox' ),
		'published_ok'   => array( 'label' => __( 'Guest approved publication', 'perfect10' ), 'type' => 'checkbox', 'default' => 1 ),
	);

	echo '<div class="p10-metabox"><div class="p10-metabox__grid">';
	foreach ( $fields as $key => $field ) {
		$value = get_post_meta( $post->ID, 'p10_' . $key, true );
		if ( '' === $value && isset( $field['default'] ) ) {
			$value = $field['default'];
		}
		p10_render_field( $key, $field, $value );
	}
	echo '</div></div>';
}

/**
 * Package meta box.
 *
 * @param WP_Post $post Post.
 */
function p10_render_package_meta_box( $post ) {
	wp_nonce_field( 'p10_save_package', 'p10_package_nonce' );

	$fields = array(
		'price'     => array( 'label' => __( 'Price', 'perfect10' ), 'type' => 'number' ),
		'period'    => array( 'label' => __( 'Billing period', 'perfect10' ), 'type' => 'text', 'default' => __( 'per month', 'perfect10' ) ),
		'badge'     => array( 'label' => __( 'Badge', 'perfect10' ), 'type' => 'text', 'hint' => __( 'e.g. Most Popular', 'perfect10' ) ),
		'features'  => array( 'label' => __( 'Included features', 'perfect10' ), 'type' => 'textarea', 'hint' => __( 'One per line.', 'perfect10' ) ),
		'cta_label' => array( 'label' => __( 'Button label', 'perfect10' ), 'type' => 'text', 'default' => __( 'Enquire about this package', 'perfect10' ) ),
		'featured'  => array( 'label' => __( 'Highlight this package', 'perfect10' ), 'type' => 'checkbox' ),
	);

	echo '<div class="p10-metabox"><div class="p10-metabox__grid">';
	foreach ( $fields as $key => $field ) {
		$value = get_post_meta( $post->ID, 'p10_' . $key, true );
		if ( '' === $value && isset( $field['default'] ) ) {
			$value = $field['default'];
		}
		p10_render_field( $key, $field, $value );
	}
	echo '</div></div>';
}

/**
 * Sanitise a value against its schema definition.
 *
 * @param mixed  $value Raw value.
 * @param array  $field Schema entry.
 * @return mixed
 */
function p10_sanitize_field( $value, $field ) {
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	switch ( $type ) {
		case 'checkbox':
			return $value ? 1 : 0;

		case 'number':
			return '' === $value ? '' : (float) $value;

		case 'textarea':
		case 'tags':
			return sanitize_textarea_field( $value );

		case 'url':
			return esc_url_raw( $value );

		case 'email':
			return sanitize_email( $value );

		case 'select':
		case 'radio':
			$options = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
			return in_array( $value, $options, true ) ? $value : ( isset( $field['default'] ) ? $field['default'] : '' );

		case 'media':
			return absint( $value );

		case 'gallery':
			$ids = array_filter( array_map( 'intval', explode( ',', (string) $value ) ) );
			return implode( ',', $ids );

		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Save profile meta.
 *
 * @param int $post_id Post ID.
 */
function p10_save_profile_meta( $post_id ) {
	if ( ! p10_meta_box_is_valid( $post_id, 'p10_profile_nonce', 'p10_save_profile' ) ) {
		return;
	}

	$schema = p10_profile_field_schema();
	$raw    = isset( $_POST['p10_fields'] ) ? wp_unslash( $_POST['p10_fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field below.

	foreach ( $schema as $key => $field ) {
		$value = isset( $raw[ $key ] ) ? $raw[ $key ] : '';

		if ( 'checkbox' === $field['type'] && ! isset( $raw[ $key ] ) ) {
			$value = '';
		}

		$value = p10_sanitize_field( $value, $field );

		if ( '' === $value || 0 === $value && 'checkbox' === $field['type'] ) {
			delete_post_meta( $post_id, 'p10_' . $key );
			continue;
		}
		update_post_meta( $post_id, 'p10_' . $key, $value );
	}
}
add_action( 'save_post_p10_profile', 'p10_save_profile_meta' );

/**
 * Save review meta.
 *
 * @param int $post_id Post ID.
 */
function p10_save_review_meta( $post_id ) {
	if ( ! p10_meta_box_is_valid( $post_id, 'p10_review_nonce', 'p10_save_review' ) ) {
		return;
	}
	$keys = array( 'guest_name', 'guest_location', 'guest_label', 'rating', 'review_date', 'featured', 'published_ok' );
	$raw  = isset( $_POST['p10_fields'] ) ? wp_unslash( $_POST['p10_fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $keys as $key ) {
		$value = isset( $raw[ $key ] ) ? $raw[ $key ] : '';
		$value = in_array( $key, array( 'featured', 'published_ok' ), true ) ? ( $value ? 1 : 0 ) : sanitize_text_field( $value );
		if ( 'rating' === $key ) {
			$value = max( 1, min( 5, (int) $value ) );
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, 'p10_' . $key );
		} else {
			update_post_meta( $post_id, 'p10_' . $key, $value );
		}
	}
}
add_action( 'save_post_p10_review', 'p10_save_review_meta' );

/**
 * Save package meta.
 *
 * @param int $post_id Post ID.
 */
function p10_save_package_meta( $post_id ) {
	if ( ! p10_meta_box_is_valid( $post_id, 'p10_package_nonce', 'p10_save_package' ) ) {
		return;
	}
	$keys = array( 'price', 'period', 'badge', 'features', 'cta_label', 'featured' );
	$raw  = isset( $_POST['p10_fields'] ) ? wp_unslash( $_POST['p10_fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $keys as $key ) {
		$value = isset( $raw[ $key ] ) ? $raw[ $key ] : '';
		if ( 'featured' === $key ) {
			$value = $value ? 1 : 0;
		} elseif ( 'price' === $key ) {
			$value = '' === $value ? '' : (float) $value;
		} else {
			$value = sanitize_text_field( $value );
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, 'p10_' . $key );
		} else {
			update_post_meta( $post_id, 'p10_' . $key, $value );
		}
	}
}
add_action( 'save_post_p10_package', 'p10_save_package_meta' );

/**
 * Shared nonce / autosave / permission check for meta boxes.
 *
 * @param int    $post_id        Post ID.
 * @param string $nonce_field    Nonce field name.
 * @param string $nonce_action   Nonce action.
 * @return bool
 */
function p10_meta_box_is_valid( $post_id, $nonce_field, $nonce_action ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST[ $nonce_field ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ $nonce_field ] ), $nonce_action ) ) {
		return false;
	}
	$post_type = get_post_type( $post_id );
	if ( ! $post_type || ! post_type_exists( $post_type ) ) {
		return false;
	}
	$type_object = get_post_type_object( $post_type );
	if ( $type_object && ! current_user_can( $type_object->cap->edit_post, $post_id ) ) {
		return false;
	}
	return true;
}

/* -------------------------------------------------------------------------
 * Admin: term meta
 * ---------------------------------------------------------------------- */

/**
 * Term meta fields per taxonomy.
 *
 * @return array
 */
function p10_term_field_schema() {
	return apply_filters( 'p10_term_field_schema', array(
		'p10_county'   => array(
			'tagline' => array( 'label' => __( 'Tagline', 'perfect10' ), 'type' => 'text' ),
			'blurb'   => array( 'label' => __( 'Intro blurb', 'perfect10' ), 'type' => 'textarea' ),
			'image'   => array( 'label' => __( 'Cover image', 'perfect10' ), 'type' => 'media', 'hint' => __( 'Used for the county, city and profile-type cards. A placeholder is shown when empty.', 'perfect10' ) ),
			'image_alt' => array( 'label' => __( 'Cover image alt text', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Describe the photograph, not the keyword list. Defaults to the term name.', 'perfect10' ) ),
			'seo_title' => array( 'label' => __( 'SEO title', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Optional. Leave blank to use the term name with the site name.', 'perfect10' ) ),
			'seo_description' => array( 'label' => __( 'Meta description', 'perfect10' ), 'type' => 'textarea', 'hint' => __( 'Optional. Blank uses the short description above.', 'perfect10' ) ),
			'sort'    => array( 'label' => __( 'Sort order', 'perfect10' ), 'type' => 'number' ),
		),
		'p10_city'     => array(
			'county'  => array(
				'label'   => __( 'County', 'perfect10' ),
				'type'    => 'select',
				'options' => 'p10_county_options',
				'hint'    => __( 'Determines the city’s URL and which county dropdown it appears in.', 'perfect10' ),
			),
			'kind'    => array(
				'label'   => __( 'Location type', 'perfect10' ),
				'type'    => 'select',
				'default' => 'city',
				'options' => array(
					'city'     => __( 'Incorporated city', 'perfect10' ),
					'district' => __( 'District / neighbourhood', 'perfect10' ),
				),
			),
			'blurb'   => array( 'label' => __( 'Short description', 'perfect10' ), 'type' => 'textarea', 'hint' => __( 'Shown on cards and as the archive intro. Use real, specific copy — it also becomes the meta description.', 'perfect10' ) ),
			'image'   => array( 'label' => __( 'Cover image', 'perfect10' ), 'type' => 'media', 'hint' => __( 'Used for the county, city and profile-type cards. A placeholder is shown when empty.', 'perfect10' ) ),
			'image_alt' => array( 'label' => __( 'Cover image alt text', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Describe the photograph, not the keyword list. Defaults to the city name.', 'perfect10' ) ),
			'seo_title' => array( 'label' => __( 'SEO title', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Optional. Leave blank to use the city name with the site name.', 'perfect10' ) ),
			'seo_description' => array( 'label' => __( 'Meta description', 'perfect10' ), 'type' => 'textarea', 'hint' => __( 'Optional. Blank uses the short description above.', 'perfect10' ) ),
			'featured' => array( 'label' => __( 'Feature on homepage', 'perfect10' ), 'type' => 'checkbox' ),
			'sort'    => array( 'label' => __( 'Sort order', 'perfect10' ), 'type' => 'number' ),
		),
		'p10_category' => array(
			'number'  => array( 'label' => __( 'Display number', 'perfect10' ), 'type' => 'text', 'hint' => __( 'e.g. 01', 'perfect10' ) ),
			'short'   => array( 'label' => __( 'Short label', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Used on tiles, e.g. “Massage”.', 'perfect10' ) ),
			'blurb'   => array( 'label' => __( 'Short description', 'perfect10' ), 'type' => 'textarea', 'hint' => __( 'Shown on cards and as the archive intro. Use real, specific copy — it also becomes the meta description.', 'perfect10' ) ),
			'image'   => array( 'label' => __( 'Cover image', 'perfect10' ), 'type' => 'media', 'hint' => __( 'Used for the county, city and profile-type cards. A placeholder is shown when empty.', 'perfect10' ) ),
			'image_alt' => array( 'label' => __( 'Cover image alt text', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Describe the photograph, not the keyword list. Defaults to the term name.', 'perfect10' ) ),
			'seo_title' => array( 'label' => __( 'SEO title', 'perfect10' ), 'type' => 'text', 'hint' => __( 'Optional. Leave blank to use the term name with the site name.', 'perfect10' ) ),
			'seo_description' => array( 'label' => __( 'Meta description', 'perfect10' ), 'type' => 'textarea', 'hint' => __( 'Optional. Blank uses the short description above.', 'perfect10' ) ),
			'sort'    => array( 'label' => __( 'Sort order', 'perfect10' ), 'type' => 'number' ),
		),
		'p10_nationality' => array(
			'sort'    => array( 'label' => __( 'Sort order', 'perfect10' ), 'type' => 'number' ),
		),
	) );
}

/**
 * Options for the city → county select.
 *
 * @return array
 */
function p10_county_options() {
	$options = array( '' => __( '— No county —', 'perfect10' ) );
	$terms   = get_terms( array( 'taxonomy' => 'p10_county', 'hide_empty' => false ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}
	}
	return $options;
}

/**
 * Resolve dynamic option callbacks in a field definition.
 *
 * @param array $field Field.
 * @return array
 */
function p10_resolve_field_options( $field ) {
	if ( isset( $field['options'] ) && is_string( $field['options'] ) && is_callable( $field['options'] ) ) {
		$field['options'] = call_user_func( $field['options'] );
	}
	return $field;
}

/**
 * Render term fields on the add-term screen.
 *
 * @param string $taxonomy Taxonomy slug.
 */
function p10_term_add_fields( $taxonomy ) {
	$schema = p10_term_field_schema();
	if ( empty( $schema[ $taxonomy ] ) ) {
		return;
	}
	wp_nonce_field( 'p10_save_term', 'p10_term_nonce' );
	echo '<div class="p10-term-fields">';
	foreach ( $schema[ $taxonomy ] as $key => $field ) {
		$field = p10_resolve_field_options( $field );
		$default = isset( $field['default'] ) ? $field['default'] : '';
		echo '<div class="form-field">';
		p10_render_term_field( $key, $field, $default, 'p10_term_' . $key );
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Render term fields on the edit-term screen.
 *
 * @param WP_Term $term Term.
 */
function p10_term_edit_fields( $term ) {
	$schema = p10_term_field_schema();
	if ( empty( $schema[ $term->taxonomy ] ) ) {
		return;
	}
	wp_nonce_field( 'p10_save_term', 'p10_term_nonce' );
	foreach ( $schema[ $term->taxonomy ] as $key => $field ) {
		$field = p10_resolve_field_options( $field );
		$value = p10_term_meta( $term, $key, isset( $field['default'] ) ? $field['default'] : '' );
		echo '<tr class="form-field"><th scope="row">';
		p10_render_term_field( $key, $field, $value, 'p10_term_' . $key, true );
		echo '</th><td>';
		p10_render_term_field( $key, $field, $value, 'p10_term_' . $key, false );
		echo '</td></tr>';
	}
}

/**
 * Render a single term field control.
 *
 * @param string $key        Field key.
 * @param array  $field      Field definition.
 * @param mixed  $value      Value.
 * @param string $name       Input name.
 * @param bool   $label_only Whether to render only the label (edit screen th).
 */
function p10_render_term_field( $key, $field, $value, $name, $label_only = false ) {
	$id   = 'p10_term_' . $key;
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	if ( $label_only ) {
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
		return;
	}

	if ( ! in_array( $type, array( 'checkbox' ), true ) ) {
		echo '<label for="' . esc_attr( $id ) . '" class="screen-reader-text">' . esc_html( $field['label'] ) . '</label>';
	}

	switch ( $type ) {
		case 'textarea':
			printf( '<textarea class="large-text" rows="4" id="%1$s" name="%2$s">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ) );
			break;

		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( (array) $field['options'] as $option_value => $option_label ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $option_value ),
					selected( (string) $value, (string) $option_value, false ),
					esc_html( $option_label )
				);
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf(
				'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> %4$s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( (bool) $value, true, false ),
				esc_html( $field['label'] )
			);
			break;

		case 'media':
			$attachment = absint( $value );
			$preview    = $attachment ? wp_get_attachment_image_url( $attachment, 'medium' ) : '';
			printf(
				'<span class="p10-media-field" data-p10-media-field data-target="%1$s">
					<input type="hidden" id="%1$s" name="%2$s" value="%3$s" data-p10-media-input>
					<span class="p10-media-field__preview">%4$s</span>
					<button type="button" class="button p10-media-choose">%5$s</button>
					<button type="button" class="button-link p10-media-remove"%6$s>%7$s</button>
				</span>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $attachment ? $attachment : '' ),
				$preview ? '<img src="' . esc_url( $preview ) . '" alt="" />' : '',
				esc_html__( 'Choose image', 'perfect10' ),
				$attachment ? '' : ' hidden',
				esc_html__( 'Remove', 'perfect10' )
			);
			break;

		case 'number':
			printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
			break;

		default:
			printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
			break;
	}

	if ( ! empty( $field['hint'] ) ) {
		echo '<p class="description">' . esc_html( $field['hint'] ) . '</p>';
	}
}

/**
 * Save term meta.
 *
 * @param int $term_id Term ID.
 */
function p10_save_term_meta( $term_id ) {
	if ( ! isset( $_POST['p10_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['p10_term_nonce'] ), 'p10_save_term' ) ) {
		return;
	}

	$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( $_POST['taxonomy'] ) : '';
	$schema   = p10_term_field_schema();

	if ( ! $taxonomy || empty( $schema[ $taxonomy ] ) ) {
		return;
	}

	// Capability check: the nonce alone must not let a subscriber edit terms.
	$taxonomy_object = get_taxonomy( $taxonomy );
	if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
		return;
	}

	foreach ( $schema[ $taxonomy ] as $key => $field ) {
		$field = p10_resolve_field_options( $field );
		$name  = 'p10_term_' . $key;
		$value = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$value = p10_sanitize_field( $value, $field );

		if ( '' === $value ) {
			delete_term_meta( $term_id, 'p10_' . $key );
		} else {
			update_term_meta( $term_id, 'p10_' . $key, $value );
		}
	}

	if ( 'p10_county' === $taxonomy ) {
		p10_add_nested_rules();
		flush_rewrite_rules( false );
	}
}
add_action( 'created_term', 'p10_save_term_meta' );
add_action( 'edited_term', 'p10_save_term_meta' );

/**
 * Hook the term field renderers into every supported taxonomy.
 */
function p10_register_term_field_hooks() {
	foreach ( array_keys( p10_term_field_schema() ) as $taxonomy ) {
		add_action( $taxonomy . '_add_form_fields', 'p10_term_add_fields' );
		add_action( $taxonomy . '_edit_form_fields', 'p10_term_edit_fields' );
	}
}
add_action( 'admin_init', 'p10_register_term_field_hooks' );

/**
 * Flush permalinks when a city changes county (its URL changes).
 *
 * @param int $term_id Term ID.
 */
function p10_flush_on_city_change( $term_id ) {
	$term = get_term( $term_id, 'p10_city' );
	if ( $term && ! is_wp_error( $term ) ) {
		p10_add_nested_rules();
		flush_rewrite_rules( false );
	}
}
add_action( 'edited_p10_city', 'p10_flush_on_city_change' );
add_action( 'created_p10_city', 'p10_flush_on_city_change' );

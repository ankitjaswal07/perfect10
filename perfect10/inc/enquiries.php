<?php
/**
 * Admin handling for captured enquiries.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enquiry workflow statuses.
 *
 * @return array
 */
function p10_enquiry_statuses() {
	return apply_filters( 'p10_enquiry_statuses', array(
		'new'      => __( 'New', 'perfect10' ),
		'contacted' => __( 'Contacted', 'perfect10' ),
		'confirmed' => __( 'Confirmed', 'perfect10' ),
		'declined' => __( 'Declined', 'perfect10' ),
		'spam'     => __( 'Spam', 'perfect10' ),
	) );
}

/**
 * Replace the default columns on the Enquiries screen.
 *
 * @param array $columns Columns.
 * @return array
 */
function p10_enquiry_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'date' === $key ) {
			continue;
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['p10_type']    = __( 'Type', 'perfect10' );
			$new['p10_contact'] = __( 'Contact', 'perfect10' );
			$new['p10_profile'] = __( 'Profile', 'perfect10' );
			$new['p10_status']  = __( 'Status', 'perfect10' );
		}
	}
	$new['date'] = __( 'Received', 'perfect10' );
	return $new;
}
add_filter( 'manage_p10_enquiry_posts_columns', 'p10_enquiry_columns' );

/**
 * Render enquiry column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function p10_enquiry_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'p10_type':
			$type = (string) get_post_meta( $post_id, 'p10_enquiry_type', true );
			$form = p10_form_schema( $type );
			echo esc_html( ! empty( $form['label'] ) ? $form['label'] : $type );
			break;

		case 'p10_contact':
			$values = p10_enquiry_values( $post_id );
			$bits   = array();
			if ( ! empty( $values['phone'] ) ) {
				$bits[] = esc_html( $values['phone'] );
			}
			if ( ! empty( $values['email'] ) && is_email( $values['email'] ) ) {
				$bits[] = '<a href="mailto:' . esc_attr( $values['email'] ) . '">' . esc_html( $values['email'] ) . '</a>';
			}
			if ( ! empty( $values['contact'] ) ) {
				$bits[] = esc_html( $values['contact'] );
			}
			echo wp_kses_post( implode( '<br>', $bits ) );
			break;

		case 'p10_profile':
			$profile_id = (int) get_post_meta( $post_id, 'p10_enquiry_profile', true );
			if ( $profile_id ) {
				printf(
					'<a href="%s">%s</a>',
					esc_url( get_edit_post_link( $profile_id ) ),
					esc_html( get_the_title( $profile_id ) )
				);
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;

		case 'p10_status':
			$status   = (string) get_post_meta( $post_id, 'p10_enquiry_status', true );
			$statuses = p10_enquiry_statuses();
			$label    = isset( $statuses[ $status ] ) ? $statuses[ $status ] : __( 'New', 'perfect10' );
			echo '<span class="p10-pill p10-pill--' . esc_attr( $status ? $status : 'new' ) . '">' . esc_html( $label ) . '</span>';
			break;
	}
}
add_action( 'manage_p10_enquiry_posts_custom_column', 'p10_enquiry_column_content', 10, 2 );

/**
 * Decode the stored enquiry values.
 *
 * @param int $post_id Enquiry ID.
 * @return array
 */
function p10_enquiry_values( $post_id ) {
	$json = (string) get_post_meta( $post_id, 'p10_enquiry_values', true );
	if ( ! $json ) {
		return array();
	}
	$values = json_decode( $json, true );
	return is_array( $values ) ? $values : array();
}

/**
 * Make the status column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function p10_enquiry_sortable( $columns ) {
	$columns['p10_status'] = 'p10_status';
	$columns['p10_type']   = 'p10_type';
	return $columns;
}
add_filter( 'manage_edit-p10_enquiry_sortable_columns', 'p10_enquiry_sortable' );

/**
 * Detail meta box for a single enquiry.
 */
function p10_enquiry_meta_box() {
	add_meta_box(
		'p10_enquiry_detail',
		__( 'Enquiry detail', 'perfect10' ),
		'p10_render_enquiry_meta_box',
		'p10_enquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_p10_enquiry', 'p10_enquiry_meta_box' );

/**
 * Render the enquiry detail meta box.
 *
 * @param WP_Post $post Post.
 */
function p10_render_enquiry_meta_box( $post ) {
	wp_nonce_field( 'p10_save_enquiry', 'p10_enquiry_nonce' );

	$statuses = p10_enquiry_statuses();
	$status   = (string) get_post_meta( $post->ID, 'p10_enquiry_status', true );
	$values   = p10_enquiry_values( $post->ID );
	$type     = (string) get_post_meta( $post->ID, 'p10_enquiry_type', true );
	$form     = p10_form_schema( $type );

	echo '<p><label for="p10_enquiry_status"><strong>' . esc_html__( 'Status', 'perfect10' ) . '</strong></label> ';
	echo '<select id="p10_enquiry_status" name="p10_enquiry_status">';
	foreach ( $statuses as $key => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $key ),
			selected( $status, $key, false ),
			esc_html( $label )
		);
	}
	echo '</select></p>';

	echo '<p><label for="p10_enquiry_note"><strong>' . esc_html__( 'Internal note', 'perfect10' ) . '</strong></label><br>';
	printf(
		'<textarea class="large-text" rows="3" id="p10_enquiry_note" name="p10_enquiry_note">%s</textarea></p>',
		esc_textarea( (string) get_post_meta( $post->ID, 'p10_enquiry_note', true ) )
	);

	echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Field', 'perfect10' ) . '</th><th>' . esc_html__( 'Value', 'perfect10' ) . '</th></tr></thead><tbody>';

	if ( ! empty( $form['fields'] ) ) {
		foreach ( $form['fields'] as $key => $field ) {
			if ( ! isset( $values[ $key ] ) || '' === $values[ $key ] ) {
				continue;
			}
			$value = $values[ $key ];
			if ( 'checkbox' === $field['type'] ) {
				$value = $value ? __( 'Yes', 'perfect10' ) : __( 'No', 'perfect10' );
			} elseif ( ! empty( $field['options'][ $value ] ) ) {
				$value = $field['options'][ $value ];
			} elseif ( in_array( $field['type'], array( 'city', 'county', 'category' ), true ) ) {
				$term  = get_term_by( 'slug', $value, 'p10_' . $field['type'] );
				$value = $term ? $term->name : $value;
			} elseif ( 'profile' === $field['type'] ) {
				$related = get_post( (int) $value );
				$value   = $related ? $related->post_title : $value;
			}
			echo '<tr><td>' . esc_html( $field['label'] ) . '</td><td>' . esc_html( (string) $value ) . '</td></tr>';
		}
	}

	$meta_rows = array(
		__( 'Enquiry type', 'perfect10' ) => $type,
		__( 'IP address', 'perfect10' )    => (string) get_post_meta( $post->ID, 'p10_enquiry_ip', true ),
		__( 'User agent', 'perfect10' )    => (string) get_post_meta( $post->ID, 'p10_enquiry_ua', true ),
	);

	foreach ( $meta_rows as $label => $value ) {
		if ( '' === $value ) {
			continue;
		}
		echo '<tr><td>' . esc_html( $label ) . '</td><td><code>' . esc_html( $value ) . '</code></td></tr>';
	}

	echo '</tbody></table>';
}

/**
 * Save enquiry status and note.
 *
 * @param int $post_id Post ID.
 */
function p10_save_enquiry_meta( $post_id ) {
	if ( ! p10_meta_box_is_valid( $post_id, 'p10_enquiry_nonce', 'p10_save_enquiry' ) ) {
		return;
	}

	$statuses = array_keys( p10_enquiry_statuses() );
	$status   = isset( $_POST['p10_enquiry_status'] ) ? sanitize_key( $_POST['p10_enquiry_status'] ) : 'new';

	if ( ! in_array( $status, $statuses, true ) ) {
		$status = 'new';
	}

	update_post_meta( $post_id, 'p10_enquiry_status', $status );

	if ( isset( $_POST['p10_enquiry_note'] ) ) {
		update_post_meta(
			$post_id,
			'p10_enquiry_note',
			sanitize_textarea_field( wp_unslash( $_POST['p10_enquiry_note'] ) )
		);
	}
}
add_action( 'save_post_p10_enquiry', 'p10_save_enquiry_meta' );

/**
 * Admin bar bubble showing unread enquiries.
 */
function p10_admin_menu_bubble() {
	global $menu;

	$new = p10_count_new_enquiries();
	if ( ! $new ) {
		return;
	}

	foreach ( $menu as $index => $item ) {
		if ( ! isset( $item[2] ) || 'edit.php?post_type=p10_enquiry' !== $item[2] ) {
			continue;
		}
		$menu[ $index ][0] .= sprintf( ' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>', (int) $new );
		break;
	}
}
add_action( 'admin_menu', 'p10_admin_menu_bubble', 999 );

/**
 * Count enquiries still marked new.
 *
 * @return int
 */
function p10_count_new_enquiries() {
	$query = new WP_Query( array(
		'post_type'      => 'p10_enquiry',
		'post_status'    => 'private',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => 'p10_enquiry_status',
				'value'   => 'new',
				'compare' => '=',
			),
		),
	) );
	return (int) $query->found_posts;
}

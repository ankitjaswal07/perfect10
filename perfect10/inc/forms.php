<?php
/**
 * Public forms: booking, listing registration, concierge message and the
 * per-profile enquiry. Submissions become `p10_enquiry` posts and fire email
 * notifications. Handled through admin-post.php so it works for logged-out
 * visitors and survives page caching.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form definitions.
 *
 * Each field: label, type, required, and optional options/hint/autocomplete.
 *
 * @param string $type Form type.
 * @return array
 */
function p10_form_schema( $type ) {
	$forms = array(

		'booking'   => array(
			'label'   => __( 'Reservation request', 'perfect10' ),
			'nonce'   => 'p10_form_booking',
			'subject' => __( 'New reservation request', 'perfect10' ),
			'success' => __( 'Thank you — your reservation request has been received. A booking agent will call you to confirm.', 'perfect10' ),
			'fields'  => array(
				'name'     => array( 'label' => __( 'Your name', 'perfect10' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'name' ),
				'phone'    => array( 'label' => __( 'Phone', 'perfect10' ), 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel', 'hint' => __( 'We will call from a private number.', 'perfect10' ) ),
				'email'    => array( 'label' => __( 'Email', 'perfect10' ), 'type' => 'email', 'required' => false, 'autocomplete' => 'email' ),
				'city'     => array( 'label' => __( 'City', 'perfect10' ), 'type' => 'city', 'required' => false ),
				'profile'  => array( 'label' => __( 'Preferred profile', 'perfect10' ), 'type' => 'profile', 'required' => false ),
				'date'     => array( 'label' => __( 'Preferred date', 'perfect10' ), 'type' => 'date', 'required' => false ),
				'time'     => array( 'label' => __( 'Preferred time', 'perfect10' ), 'type' => 'time', 'required' => false ),
				'duration' => array( 'label' => __( 'Duration', 'perfect10' ), 'type' => 'select', 'required' => false, 'options' => array(
					''       => __( 'Select…', 'perfect10' ),
					'1hr'    => __( '1 hour', 'perfect10' ),
					'2hr'    => __( '2 hours', 'perfect10' ),
					'3hr'    => __( '3 hours', 'perfect10' ),
					'4hr'    => __( '4 hours', 'perfect10' ),
					'6hr'    => __( '6 hours', 'perfect10' ),
					'12hr'   => __( '12 hours', 'perfect10' ),
					'overnight' => __( 'Overnight', 'perfect10' ),
					'weekend'   => __( 'Weekend / extended', 'perfect10' ),
				) ),
				'service'  => array( 'label' => __( 'Incall or outcall', 'perfect10' ), 'type' => 'select', 'required' => false, 'options' => array(
					''        => __( 'Select…', 'perfect10' ),
					'incall'  => __( 'Incall', 'perfect10' ),
					'outcall' => __( 'Outcall', 'perfect10' ),
				) ),
				'message'  => array( 'label' => __( 'Notes', 'perfect10' ), 'type' => 'textarea', 'required' => false ),
			),
		),

		'register'  => array(
			'label'   => __( 'Create an independent listing', 'perfect10' ),
			'nonce'   => 'p10_form_register',
			'subject' => __( 'New listing application', 'perfect10' ),
			'success' => __( 'Thank you — your application has been received. Our team will be in touch about verification and placement.', 'perfect10' ),
			'fields'  => array(
				'name'       => array( 'label' => __( 'Stage name', 'perfect10' ), 'type' => 'text', 'required' => true ),
				'legal_name' => array( 'label' => __( 'Contact name', 'perfect10' ), 'type' => 'text', 'required' => true ),
				'phone'      => array( 'label' => __( 'Phone', 'perfect10' ), 'type' => 'tel', 'required' => true ),
				'email'      => array( 'label' => __( 'Email', 'perfect10' ), 'type' => 'email', 'required' => true ),
				'age'        => array( 'label' => __( 'Age', 'perfect10' ), 'type' => 'number', 'required' => true, 'hint' => __( 'You must be 18 or over.', 'perfect10' ) ),
				'county'     => array( 'label' => __( 'County', 'perfect10' ), 'type' => 'county', 'required' => true ),
				'city'       => array( 'label' => __( 'City', 'perfect10' ), 'type' => 'city', 'required' => true ),
				'category'   => array( 'label' => __( 'Listing type', 'perfect10' ), 'type' => 'category', 'required' => true ),
				'rate'       => array( 'label' => __( 'Hourly rate', 'perfect10' ), 'type' => 'number', 'required' => false ),
				'package'    => array( 'label' => __( 'Interested in', 'perfect10' ), 'type' => 'package', 'required' => false ),
				'message'    => array( 'label' => __( 'About you', 'perfect10' ), 'type' => 'textarea', 'required' => false ),
				'consent'    => array( 'label' => __( 'I confirm I am 18 or over and that the details given are accurate.', 'perfect10' ), 'type' => 'checkbox', 'required' => true ),
			),
		),

		'concierge' => array(
			'label'   => __( 'Message the concierge', 'perfect10' ),
			'nonce'   => 'p10_form_concierge',
			'subject' => __( 'New concierge message', 'perfect10' ),
			'success' => __( 'Message sent. The concierge will reply shortly.', 'perfect10' ),
			'fields'  => array(
				'name'    => array( 'label' => __( 'Name', 'perfect10' ), 'type' => 'text', 'required' => true ),
				'contact' => array( 'label' => __( 'Phone or email', 'perfect10' ), 'type' => 'text', 'required' => true ),
				'message' => array( 'label' => __( 'How can we help?', 'perfect10' ), 'type' => 'textarea', 'required' => true ),
			),
		),

		'profile'   => array(
			'label'   => __( 'Request a referral', 'perfect10' ),
			'nonce'   => 'p10_form_profile',
			'subject' => __( 'New profile enquiry', 'perfect10' ),
			'success' => __( 'Thank you — your enquiry has been sent. A booking agent will contact you to confirm availability.', 'perfect10' ),
			'fields'  => array(
				'name'     => array( 'label' => __( 'Your name', 'perfect10' ), 'type' => 'text', 'required' => true ),
				'phone'    => array( 'label' => __( 'Phone', 'perfect10' ), 'type' => 'tel', 'required' => true ),
				'email'    => array( 'label' => __( 'Email', 'perfect10' ), 'type' => 'email', 'required' => false ),
				'date'     => array( 'label' => __( 'Preferred date', 'perfect10' ), 'type' => 'date', 'required' => false ),
				'duration' => array( 'label' => __( 'Duration', 'perfect10' ), 'type' => 'select', 'required' => false, 'options' => array(
					''       => __( 'Select…', 'perfect10' ),
					'1hr'    => __( '1 hour', 'perfect10' ),
					'2hr'    => __( '2 hours', 'perfect10' ),
					'3hr'    => __( '3 hours', 'perfect10' ),
					'4hr'    => __( '4 hours', 'perfect10' ),
					'overnight' => __( 'Overnight', 'perfect10' ),
				) ),
				'message'  => array( 'label' => __( 'Message', 'perfect10' ), 'type' => 'textarea', 'required' => false ),
			),
		),
	);

	$forms = apply_filters( 'p10_form_schema', $forms );

	return isset( $forms[ $type ] ) ? $forms[ $type ] : array();
}

/**
 * Render a form.
 *
 * @param string $type    Form type.
 * @param array  $args    { profile_id, redirect, class }.
 */
function p10_the_form( $type, $args = array() ) {
	$form = p10_form_schema( $type );
	if ( ! $form ) {
		return;
	}

	$args = wp_parse_args( $args, array(
		'profile_id' => 0,
		'redirect'   => '',
		'class'      => '',
	) );

	$errors = p10_form_errors( $type );
	$old    = p10_form_old( $type );

	if ( ! empty( $errors['fatal'] ) ) {
		echo '<p class="p10-form__fatal" role="alert">' . esc_html( $errors['fatal'] ) . '</p>';
	}

	printf(
		'<form class="p10-form p10-form--%1$s %2$s" method="post" action="%3$s" novalidate>',
		esc_attr( $type ),
		esc_attr( $args['class'] ),
		esc_url( admin_url( 'admin-post.php' ) )
	);

	echo '<input type="hidden" name="p10_form" value="' . esc_attr( $type ) . '" />';
	wp_nonce_field( $form['nonce'], 'p10_nonce' );

	if ( $args['redirect'] ) {
		echo '<input type="hidden" name="p10_redirect" value="' . esc_url( $args['redirect'] ) . '" />';
	}
	if ( $args['profile_id'] ) {
		echo '<input type="hidden" name="p10_profile_id" value="' . esc_attr( (int) $args['profile_id'] ) . '" />';
	}

	// Honeypot: real users never see or fill this.
	echo '<p class="p10-form__hp" aria-hidden="true"><label for="p10_hp_' . esc_attr( $type ) . '">' . esc_html__( 'Leave this empty', 'perfect10' ) . '</label><input type="text" id="p10_hp_' . esc_attr( $type ) . '" name="p10_website" tabindex="-1" autocomplete="off" value="" /></p>';

	$fields = $form['fields'];
	echo '<div class="p10-form__grid">';
	$index = 0;
	foreach ( $fields as $key => $field ) {
		$index++;
		p10_render_form_field( $type, $key, $field, $errors, $old, $args, $index, count( $fields ) );
	}
	echo '</div>';

	echo '<div class="p10-form__actions">';
	printf(
		'<button type="submit" class="p10-btn p10-btn--gold p10-btn--block">%s</button>',
		esc_html( isset( $args['submit'] ) ? $args['submit'] : __( 'Send request', 'perfect10' ) )
	);
	if ( ! empty( $form['privacy'] ) ) {
		echo '<p class="p10-form__privacy">' . esc_html( $form['privacy'] ) . '</p>';
	}
	echo '</div>';

	echo '</form>';
}

/**
 * Render a single form field.
 *
 * @param string $type   Form type.
 * @param string $key    Field key.
 * @param array  $field  Field definition.
 * @param array  $errors Errors.
 * @param array  $old    Previously submitted values.
 * @param array  $args   Form args.
 * @param int    $index  1-based index.
 * @param int    $total  Total fields.
 */
function p10_render_form_field( $type, $key, $field, $errors, $old, $args, $index, $total ) {
	$name  = 'p10_' . $key;
	$id    = 'p10_' . $type . '_' . $key;
	$value = isset( $old[ $key ] ) ? $old[ $key ] : ( isset( $field['default'] ) ? $field['default'] : '' );
	$error = isset( $errors[ $key ] ) ? $errors[ $key ] : '';
	$field_type = isset( $field['type'] ) ? $field['type'] : 'text';

	$span = in_array( $field_type, array( 'textarea' ), true ) || ! empty( $field['wide'] ) ? ' p10-form__field--wide' : '';
	$class = 'p10-form__field' . $span . ( $error ? ' p10-form__field--error' : '' );

	echo '<div class="' . esc_attr( trim( $class ) ) . '">';

	if ( 'checkbox' !== $field_type ) {
		printf(
			'<label for="%1$s">%2$s%3$s</label>',
			esc_attr( $id ),
			esc_html( $field['label'] ),
			! empty( $field['required'] ) ? ' <span class="p10-form__req" aria-hidden="true">◆</span>' : ''
		);
	}

	switch ( $field_type ) {

		case 'textarea':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="4" %3$s>%4$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				! empty( $field['required'] ) ? 'required' : '',
				esc_textarea( $value )
			);
			break;

		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( ! empty( $field['required'] ) ? ' required' : '' ) . '>';
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

		case 'county':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" data-p10-county' . ( ! empty( $field['required'] ) ? ' required' : '' ) . '>';
			echo '<option value="">' . esc_html__( 'All Counties', 'perfect10' ) . '</option>';
			foreach ( p10_get_counties() as $county ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $county->slug ),
					selected( (string) $value, $county->slug, false ),
					esc_html( $county->name )
				);
			}
			echo '</select>';
			break;

		case 'city':
			$selected_county = isset( $old['county'] ) ? $old['county'] : '';
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" data-p10-city data-county="' . esc_attr( $selected_county ) . '"' . ( ! empty( $field['required'] ) ? ' required' : '' ) . '>';
			echo '<option value="">' . esc_html__( 'All Cities', 'perfect10' ) . '</option>';
			foreach ( p10_get_cities( $selected_county ) as $city ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $city->slug ),
					selected( (string) $value, $city->slug, false ),
					esc_html( $city->name )
				);
			}
			echo '</select>';
			break;

		case 'category':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( ! empty( $field['required'] ) ? ' required' : '' ) . '>';
			echo '<option value="">' . esc_html__( 'Select…', 'perfect10' ) . '</option>';
			foreach ( p10_get_categories() as $category ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $category->slug ),
					selected( (string) $value, $category->slug, false ),
					esc_html( $category->name )
				);
			}
			echo '</select>';
			break;

		case 'package':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			echo '<option value="">' . esc_html__( 'Standard listing', 'perfect10' ) . '</option>';
			foreach ( p10_get_packages() as $package ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $package->post_name ),
					selected( (string) $value, $package->post_name, false ),
					esc_html( $package->post_title )
				);
			}
			echo '</select>';
			break;

		case 'profile':
			$current = $args['profile_id'] ? get_post( $args['profile_id'] ) : null;
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			echo '<option value="">' . esc_html__( 'No preference', 'perfect10' ) . '</option>';
			$profiles = get_posts( array(
				'post_type'      => 'p10_profile',
				'posts_per_page' => 500,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			) );
			foreach ( $profiles as $profile ) {
				$is_selected = $current && (int) $current->ID === (int) $profile->ID;
				printf(
					'<option value="%1$s"%3$s>%2$s</option>',
					esc_attr( $profile->ID ),
					esc_html( $profile->post_title ),
					selected( (string) $value, (string) $profile->ID, $is_selected )
				);
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf(
				'<label class="p10-form__check" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s%4$s /><span>%5$s</span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( (bool) $value, true, false ),
				! empty( $field['required'] ) ? ' required' : '',
				esc_html( $field['label'] )
			);
			break;

		case 'number':
			printf(
				'<input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$s"%5$s />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( 'age' === $key ? 18 : 0 ),
				! empty( $field['required'] ) ? ' required' : ''
			);
			break;

		default:
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s"%5$s%6$s />',
				esc_attr( in_array( $field_type, array( 'tel', 'email', 'date', 'time', 'url' ), true ) ? $field_type : 'text' ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				! empty( $field['autocomplete'] ) ? ' autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : '',
				! empty( $field['required'] ) ? ' required' : ''
			);
			break;
	}

	if ( ! empty( $field['hint'] ) ) {
		echo '<p class="p10-form__hint">' . esc_html( $field['hint'] ) . '</p>';
	}
	if ( $error ) {
		echo '<p class="p10-form__error" role="alert">' . esc_html( $error ) . '</p>';
	}

	echo '</div>';
}

/**
 * Handle a submitted form.
 */
function p10_handle_form() {
	if ( ! isset( $_POST['p10_form'] ) ) {
		return;
	}

	$type = sanitize_key( $_POST['p10_form'] );
	$form = p10_form_schema( $type );

	$redirect = p10_form_redirect_target();

	if ( ! $form ) {
		wp_safe_redirect( $redirect );
		exit;
	}

	// Honeypot and submission-rate checks: fail silently like a success.
	if ( ! empty( $_POST['p10_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'p10_status', 'sent', $redirect ) );
		exit;
	}

	if ( p10_submission_throttled( $type ) ) {
		p10_store_form_state( $type, array(), array( 'fatal' => __( 'Please wait a moment before sending another request.', 'perfect10' ) ) );
		wp_safe_redirect( add_query_arg( 'p10_status', 'error', $redirect ) );
		exit;
	}

	if ( ! isset( $_POST['p10_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['p10_nonce'] ), $form['nonce'] ) ) {
		p10_store_form_state( $type, array(), array( 'fatal' => __( 'Your session expired. Please try again.', 'perfect10' ) ) );
		wp_safe_redirect( add_query_arg( 'p10_status', 'error', $redirect ) );
		exit;
	}

	$raw    = p10_collect_form_values( $form );
	$errors = p10_validate_form_values( $type, $form, $raw );

	if ( $errors ) {
		p10_store_form_state( $type, $raw, $errors );
		wp_safe_redirect( add_query_arg( 'p10_status', 'error', $redirect ) );
		exit;
	}

	$enquiry_id = p10_store_enquiry( $type, $form, $raw );

	if ( ! $enquiry_id || is_wp_error( $enquiry_id ) ) {
		p10_store_form_state( $type, $raw, array( 'fatal' => __( 'We could not save your request. Please call us instead.', 'perfect10' ) ) );
		wp_safe_redirect( add_query_arg( 'p10_status', 'error', $redirect ) );
		exit;
	}

	p10_notify_enquiry( $enquiry_id, $type, $form, $raw );
	p10_note_submission( $type );
	p10_clear_form_state( $type );

	wp_safe_redirect( add_query_arg( array( 'p10_status' => 'sent', 'p10_form' => $type ), $redirect ) );
	exit;
}
add_action( 'admin_post_p10_form', 'p10_handle_form' );
add_action( 'admin_post_nopriv_p10_form', 'p10_handle_form' );

/**
 * Where to send the visitor after submitting.
 *
 * @return string
 */
function p10_form_redirect_target() {
	$redirect = isset( $_POST['p10_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['p10_redirect'] ) ) : '';

	if ( ! $redirect ) {
		$redirect = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	}

	$home = home_url( '/' );
	if ( ! $redirect || 0 !== strpos( $redirect, $home ) ) {
		$redirect = $home;
	}

	return remove_query_arg( array( 'p10_status', 'p10_form' ), $redirect );
}

/**
 * Pull and sanitise submitted values.
 *
 * @param array $form Form definition.
 * @return array
 */
function p10_collect_form_values( $form ) {
	$values = array();

	foreach ( $form['fields'] as $key => $field ) {
		$name  = 'p10_' . $key;
		$raw   = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$type  = isset( $field['type'] ) ? $field['type'] : 'text';

		switch ( $type ) {
			case 'textarea':
				$values[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'email':
				$values[ $key ] = sanitize_email( $raw );
				break;
			case 'number':
				$values[ $key ] = '' === $raw ? '' : (int) $raw;
				break;
			case 'checkbox':
				$values[ $key ] = $raw ? 1 : 0;
				break;
			case 'city':
			case 'county':
			case 'category':
				$values[ $key ] = sanitize_title( $raw );
				break;
			case 'profile':
				$values[ $key ] = absint( $raw );
				break;
			default:
				$values[ $key ] = sanitize_text_field( $raw );
				break;
		}
	}

	return $values;
}

/**
 * Validate submitted values.
 *
 * @param string $type  Form type.
 * @param array  $form  Form definition.
 * @param array  $values Sanitised values.
 * @return array Field errors keyed by field name.
 */
function p10_validate_form_values( $type, $form, $values ) {
	$errors = array();

	foreach ( $form['fields'] as $key => $field ) {
		$value    = isset( $values[ $key ] ) ? $values[ $key ] : '';
		$required = ! empty( $field['required'] );
		$label    = $field['label'];

		if ( $required && ( '' === $value || 0 === $value && 'checkbox' === $field['type'] ) ) {
			/* translators: %s: field label. */
			$errors[ $key ] = sprintf( __( '%s is required.', 'perfect10' ), $label );
			continue;
		}

		if ( '' === $value ) {
			continue;
		}

		$field_type = isset( $field['type'] ) ? $field['type'] : 'text';

		if ( 'email' === $field_type && ! is_email( $value ) ) {
			$errors[ $key ] = __( 'Please enter a valid email address.', 'perfect10' );
		}

		if ( 'tel' === $field_type && strlen( preg_replace( '/\D/', '', $value ) ) < 7 ) {
			$errors[ $key ] = __( 'Please enter a reachable phone number.', 'perfect10' );
		}

		if ( 'number' === $field_type && ! is_numeric( $value ) ) {
			$errors[ $key ] = __( 'Please enter a number.', 'perfect10' );
		}

		if ( 'age' === $key && (int) $value < 18 ) {
			$errors[ $key ] = __( 'You must be 18 or over.', 'perfect10' );
		}

		if ( ! empty( $field['options'] ) && ! array_key_exists( $value, (array) $field['options'] ) ) {
			$errors[ $key ] = __( 'Please choose a valid option.', 'perfect10' );
		}
	}

	return apply_filters( 'p10_form_errors_validate', $errors, $type, $values );
}

/**
 * Persist an enquiry as a private post.
 *
 * @param string $type   Form type.
 * @param array  $form   Form definition.
 * @param array  $values Values.
 * @return int|WP_Error
 */
function p10_store_enquiry( $type, $form, $values ) {
	$profile_id = isset( $_POST['p10_profile_id'] ) ? absint( $_POST['p10_profile_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $profile_id && ! empty( $values['profile'] ) ) {
		$profile_id = (int) $values['profile'];
	}

	$profile = $profile_id ? get_post( $profile_id ) : null;
	$name    = isset( $values['name'] ) ? $values['name'] : '';
	$legal   = isset( $values['legal_name'] ) ? $values['legal_name'] : '';

	$title_parts = array_filter( array(
		$form['label'],
		$name ? $name : $legal,
		$profile ? $profile->post_title : '',
	) );

	$post_id = wp_insert_post( array(
		'post_type'   => 'p10_enquiry',
		'post_status' => 'private',
		'post_title'  => implode( ' — ', array_map( 'sanitize_text_field', $title_parts ) ),
		'post_content' => p10_enquiry_body( $form, $values, $profile ),
		'post_author' => 0,
	), true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, 'p10_enquiry_type', $type );
	update_post_meta( $post_id, 'p10_enquiry_status', 'new' );
	update_post_meta( $post_id, 'p10_enquiry_values', wp_json_encode( $values ) );
	update_post_meta( $post_id, 'p10_enquiry_ip', p10_client_ip() );
	update_post_meta( $post_id, 'p10_enquiry_ua', isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );

	if ( $profile ) {
		update_post_meta( $post_id, 'p10_enquiry_profile', $profile->ID );
	}

	do_action( 'p10_enquiry_created', $post_id, $type, $values, $profile );

	return $post_id;
}

/**
 * Build a human-readable enquiry body.
 *
 * @param array        $form   Form definition.
 * @param array        $values Values.
 * @param WP_Post|null $profile Related profile.
 * @return string
 */
function p10_enquiry_body( $form, $values, $profile ) {
	$lines = array();

	foreach ( $form['fields'] as $key => $field ) {
		$value = isset( $values[ $key ] ) ? $values[ $key ] : '';
		if ( '' === $value || 0 === $value ) {
			continue;
		}
		if ( 'checkbox' === $field['type'] ) {
			$value = $value ? __( 'Yes', 'perfect10' ) : __( 'No', 'perfect10' );
		}
		if ( ! empty( $field['options'][ $value ] ) ) {
			$value = $field['options'][ $value ];
		}
		if ( in_array( $field['type'], array( 'city', 'county', 'category' ), true ) ) {
			$term  = get_term_by( 'slug', $value, 'p10_' . $field['type'] );
			$value = $term ? $term->name : $value;
		}
		if ( 'profile' === $field['type'] ) {
			$related = get_post( (int) $value );
			$value   = $related ? $related->post_title : $value;
		}
		$lines[] = $field['label'] . ': ' . $value;
	}

	if ( $profile ) {
		$lines[] = __( 'Profile URL', 'perfect10' ) . ': ' . get_permalink( $profile );
	}

	return implode( "\n", $lines );
}

/**
 * Send the enquiry notifications.
 *
 * @param int          $enquiry_id Enquiry post ID.
 * @param string       $type       Form type.
 * @param array        $form       Form definition.
 * @param array        $values     Values.
 */
function p10_notify_enquiry( $enquiry_id, $type, $form, $values ) {
	$to      = apply_filters( 'p10_enquiry_to', (string) p10_option( 'email', get_option( 'admin_email' ) ), $type );
	$profile = ! empty( $values['profile'] ) ? get_post( (int) $values['profile'] ) : null;

	if ( ! $profile && isset( $_POST['p10_profile_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$profile = get_post( absint( $_POST['p10_profile_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	$subject = sprintf(
		/* translators: 1: site name, 2: enquiry subject. */
		__( '[%1$s] %2$s', 'perfect10' ),
		get_bloginfo( 'name' ),
		$form['subject'] . ( $profile ? ' — ' . $profile->post_title : '' )
	);

	$body = $form['subject'] . "\n\n";
	$body .= p10_enquiry_body( $form, $values, $profile ) . "\n\n";
	$body .= sprintf( __( 'Received: %s', 'perfect10' ), current_time( 'mysql' ) ) . "\n";
	$body .= sprintf( __( 'Enquiry ID: %d', 'perfect10' ), $enquiry_id ) . "\n";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	$reply_to = '';
	if ( ! empty( $values['email'] ) && is_email( $values['email'] ) ) {
		$reply_to = $values['email'];
	} elseif ( ! empty( $values['contact'] ) && is_email( $values['contact'] ) ) {
		$reply_to = $values['contact'];
	}
	if ( $reply_to ) {
		$headers[] = 'Reply-To: ' . $reply_to;
	}

	if ( $to ) {
		wp_mail( $to, $subject, $body, $headers );
	}

	// Optionally forward a profile enquiry to the profile's own contact email.
	if ( $profile ) {
		$profile_email = (string) p10_meta( $profile, 'email', '' );
		$forward       = (bool) p10_option( 'forward_profile_enquiries', 1 );
		if ( $forward && $profile_email && is_email( $profile_email ) && $profile_email !== $to ) {
			wp_mail( $profile_email, $subject, $body, $headers );
		}
	}

	do_action( 'p10_enquiry_notified', $enquiry_id, $type, $values, $to );
}

/**
 * Store validation errors and old input in a transient keyed to the session.
 *
 * @param string $type   Form type.
 * @param array  $values Values.
 * @param array  $errors Errors.
 */
function p10_store_form_state( $type, $values, $errors ) {
	set_transient( 'p10_form_' . $type . '_' . p10_form_token(), array(
		'values' => $values,
		'errors' => $errors,
	), 5 * MINUTE_IN_SECONDS );
}

/**
 * Clear stored form state.
 *
 * @param string $type Form type.
 */
function p10_clear_form_state( $type ) {
	delete_transient( 'p10_form_' . $type . '_' . p10_form_token() );
}

/**
 * A stable per-visitor token so form state survives a redirect.
 *
 * @return string
 */
function p10_form_token() {
	if ( is_user_logged_in() ) {
		return 'u' . get_current_user_id();
	}
	if ( ! isset( $_COOKIE['p10_token'] ) ) {
		$token = wp_generate_password( 16, false );
		$_COOKIE['p10_token'] = $token;
		if ( ! headers_sent() ) {
			setcookie( 'p10_token', $token, time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		}
		return $token;
	}
	return preg_replace( '/[^a-zA-Z0-9]/', '', $_COOKIE['p10_token'] );
}

/**
 * Read stored errors for a form.
 *
 * @param string $type Form type.
 * @return array
 */
function p10_form_errors( $type ) {
	$state = get_transient( 'p10_form_' . $type . '_' . p10_form_token() );
	return isset( $state['errors'] ) ? (array) $state['errors'] : array();
}

/**
 * Read stored old input for a form.
 *
 * @param string $type Form type.
 * @return array
 */
function p10_form_old( $type ) {
	$state = get_transient( 'p10_form_' . $type . '_' . p10_form_token() );
	return isset( $state['values'] ) ? (array) $state['values'] : array();
}

/**
 * Client IP, proxy aware.
 *
 * @return string
 */
function p10_client_ip() {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );
	foreach ( $keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
			$ip = trim( $ip[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '';
}

/**
 * Simple per-visitor submission throttle.
 *
 * @param string $type Form type.
 * @return bool
 */
function p10_submission_throttled( $type ) {
	$seconds = (int) apply_filters( 'p10_submission_interval', 20, $type );
	if ( ! $seconds ) {
		return false;
	}
	return (bool) get_transient( 'p10_throttle_' . $type . '_' . p10_form_token() );
}

/**
 * Record a successful submission for throttling.
 *
 * @param string $type Form type.
 */
function p10_note_submission( $type ) {
	$seconds = (int) apply_filters( 'p10_submission_interval', 20, $type );
	if ( $seconds ) {
		set_transient( 'p10_throttle_' . $type . '_' . p10_form_token(), 1, $seconds );
	}
}

/**
 * Render the post-redirect status notice for a form.
 *
 * @param string $type Form type.
 */
function p10_form_status( $type = '' ) {
	// phpcs:disable WordPress.Security.NonceVerification -- read-only status flags.
	if ( ! isset( $_GET['p10_status'] ) ) {
		return;
	}
	$status = sanitize_key( $_GET['p10_status'] );
	$form   = isset( $_GET['p10_form'] ) ? sanitize_key( $_GET['p10_form'] ) : $type;

	if ( $type && $form && $form !== $type ) {
		return;
	}

	$schema = p10_form_schema( $form );

	if ( 'sent' === $status ) {
		$message = ! empty( $schema['success'] )
			? $schema['success']
			: __( 'Thank you — your request has been received.', 'perfect10' );
		echo '<div class="p10-notice p10-notice--success" role="status"><span aria-hidden="true">◆</span> ' . esc_html( $message ) . '</div>';
		p10_clear_form_state( $form );
		return;
	}

	$errors = p10_form_errors( $form );
	if ( ! empty( $errors['fatal'] ) ) {
		echo '<div class="p10-notice p10-notice--error" role="alert"><span aria-hidden="true">◆</span> ' . esc_html( $errors['fatal'] ) . '</div>';
	} elseif ( $errors ) {
		echo '<div class="p10-notice p10-notice--error" role="alert"><span aria-hidden="true">◆</span> ' . esc_html__( 'Please check the highlighted fields and try again.', 'perfect10' ) . '</div>';
	}
	// phpcs:enable WordPress.Security.NonceVerification
}

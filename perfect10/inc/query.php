<?php
/**
 * Directory queries: filtering, sorting and the AJAX city cascade.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available sort modes.
 *
 * @return array
 */
function p10_sort_options() {
	return apply_filters( 'p10_sort_options', array(
		''            => __( 'Featured', 'perfect10' ),
		'newest'      => __( 'Newest first', 'perfect10' ),
		'age-asc'     => __( 'Age: low to high', 'perfect10' ),
		'age-desc'    => __( 'Age: high to low', 'perfect10' ),
		'rate-asc'    => __( 'Rate: low to high', 'perfect10' ),
		'rate-desc'   => __( 'Rate: high to low', 'perfect10' ),
		'name-asc'    => __( 'Name: A–Z', 'perfect10' ),
		'premium'     => __( 'Premium first', 'perfect10' ),
	) );
}

/**
 * Apply directory filters to the main query.
 *
 * @param WP_Query $query Query.
 */
function p10_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$is_profile_archive = $query->is_post_type_archive( 'p10_profile' );
	$is_directory_tax   = $query->is_tax( array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ) );

	if ( ! $is_profile_archive && ! $is_directory_tax ) {
		return;
	}

	$per_page = (int) p10_option( 'directory_per_page', 12 );
	$query->set( 'posts_per_page', $per_page ? $per_page : 12 );

	$meta_query = array();
	$orderby    = array();

	// Premium only.
	if ( ! empty( $_GET['premium'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta_query[] = array(
			'key'     => 'p10_premium',
			'value'   => '1',
			'compare' => '=',
		);
		$orderby['p10_premium'] = 'DESC';
	}

	// Verified only.
	if ( ! empty( $_GET['verified'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta_query[] = array(
			'key'     => 'p10_verified',
			'value'   => '1',
			'compare' => '=',
		);
	}

	// Available only (hide offline and fully booked).
	if ( ! empty( $_GET['available'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta_query[] = array(
			'key'     => 'p10_available',
			'value'   => array( 'available', 'limited' ),
			'compare' => 'IN',
		);
	}

	// Maximum hourly rate.
	$max_rate = isset( $_GET['max_rate'] ) ? absint( $_GET['max_rate'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $max_rate ) {
		$meta_query[] = array(
			'key'     => 'p10_rate',
			'value'   => $max_rate,
			'type'    => 'NUMERIC',
			'compare' => '<=',
		);
	}

	// Age band.
	$min_age = isset( $_GET['min_age'] ) ? absint( $_GET['min_age'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$max_age = isset( $_GET['max_age'] ) ? absint( $_GET['max_age'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $min_age || $max_age ) {
		$meta_query[] = array(
			'key'     => 'p10_age',
			'type'    => 'NUMERIC',
			'value'   => array( $min_age ? $min_age : 18, $max_age ? $max_age : 99 ),
			'compare' => 'BETWEEN',
		);
	}

	// Free-text search across name, bio and tag fields.
	$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $search ) {
		$query->set( 's', $search );
	}

	// Location and type filters, used by the sidebar on the flat archive.
	$tax_query = array();

	$get_county = isset( $_GET['county'] ) ? sanitize_title( wp_unslash( $_GET['county'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$get_city   = isset( $_GET['city'] ) ? sanitize_title( wp_unslash( $_GET['city'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$type       = isset( $_GET['type'] ) ? sanitize_title( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$get_nation = isset( $_GET['nationality'] ) ? sanitize_title( wp_unslash( $_GET['nationality'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	if ( $get_county ) {
		$tax_query[] = array( 'taxonomy' => 'p10_county', 'field' => 'slug', 'terms' => $get_county );
	}
	if ( $get_city ) {
		$tax_query[] = array( 'taxonomy' => 'p10_city', 'field' => 'slug', 'terms' => $get_city );
	}
	if ( $type ) {
		$tax_query[] = array( 'taxonomy' => 'p10_category', 'field' => 'slug', 'terms' => $type );
	}
	if ( $get_nation ) {
		$tax_query[] = array( 'taxonomy' => 'p10_nationality', 'field' => 'slug', 'terms' => $get_nation );
	}

	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$query->set( 'tax_query', $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	if ( $meta_query ) {
		$meta_query['relation'] = 'AND';
		$query->set( 'meta_query', $meta_query ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	// Sorting.
	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	switch ( $sort ) {
		case 'newest':
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			break;

		case 'age-asc':
			$query->set( 'meta_key', 'p10_age' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'ASC' );
			break;

		case 'age-desc':
			$query->set( 'meta_key', 'p10_age' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;

		case 'rate-asc':
			$query->set( 'meta_key', 'p10_rate' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'ASC' );
			break;

		case 'rate-desc':
			$query->set( 'meta_key', 'p10_rate' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;

		case 'name-asc':
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
			break;

		case 'premium':
			$query->set( 'meta_key', 'p10_premium' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;

		default:
			// Default "Featured" ordering: premium, then curated order, then date.
			$query->set( 'meta_key', 'p10_featured_order' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', array(
				'meta_value_num' => 'ASC',
				'menu_order'     => 'ASC',
				'date'           => 'DESC',
			) );
			break;
	}
}
add_action( 'pre_get_posts', 'p10_pre_get_posts' );

/**
 * Keep the profile archive ordered sensibly even when meta keys are missing.
 *
 * @param string   $orderby SQL orderby clause.
 * @param WP_Query $query   Query.
 * @return string
 */
function p10_posts_orderby( $orderby, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! p10_is_directory() ) {
		return $orderby;
	}
	return $orderby;
}
add_filter( 'posts_orderby', 'p10_posts_orderby', 10, 2 );

/**
 * Preserve filter arguments across pagination links.
 *
 * @param string $link Pagination link.
 * @return string
 */
function p10_pagenum_link( $link ) {
	if ( ! p10_is_directory() ) {
		return $link;
	}

	$keep = array( 'premium', 'verified', 'available', 'max_rate', 'min_age', 'max_age', 'q', 'sort', 'type', 'county', 'city' );
	$args = array();

	foreach ( $keep as $key ) {
		if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}

	return $args ? add_query_arg( $args, $link ) : $link;
}
add_filter( 'pagenum_link', 'p10_pagenum_link' );

/**
 * REST route: cities for a county (powers the cascading dropdown).
 */
function p10_register_routes() {
	register_rest_route( 'perfect10/v1', '/cities', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'p10_rest_cities',
		'permission_callback' => '__return_true',
		'args'                => array(
			'county' => array(
				'sanitize_callback' => 'sanitize_title',
				'default'           => '',
			),
		),
	) );

	register_rest_route( 'perfect10/v1', '/profiles', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'p10_rest_profiles',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'perfect10/v1', '/stats', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'p10_rest_stats',
		'permission_callback' => '__return_true',
	) );
}
add_action( 'rest_api_init', 'p10_register_routes' );

/**
 * REST callback: list cities, optionally filtered by county.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function p10_rest_cities( $request ) {
	$county = (string) $request->get_param( 'county' );
	$cities = p10_get_cities( $county );
	$out    = array();

	foreach ( $cities as $city ) {
		$out[] = array(
			'slug'  => $city->slug,
			'name'  => $city->name,
			'kind'  => (string) p10_term_meta( $city, 'kind', 'city' ),
			'count' => p10_count_profiles( $city ),
			'url'   => p10_term_url( $city ),
		);
	}

	return rest_ensure_response( $out );
}

/**
 * REST callback: lightweight directory payload for AJAX filtering.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function p10_rest_profiles( $request ) {
	$args = array(
		'post_type'      => 'p10_profile',
		'post_status'    => 'publish',
		'posts_per_page' => min( 48, max( 1, (int) $request->get_param( 'per_page' ) ) ),
		'paged'          => max( 1, (int) $request->get_param( 'page' ) ),
	);

	$tax_query = array();

	if ( $county = sanitize_title( (string) $request->get_param( 'county' ) ) ) {
		$tax_query[] = array( 'taxonomy' => 'p10_county', 'field' => 'slug', 'terms' => $county );
	}
	if ( $city = sanitize_title( (string) $request->get_param( 'city' ) ) ) {
		$tax_query[] = array( 'taxonomy' => 'p10_city', 'field' => 'slug', 'terms' => $city );
	}
	if ( $type = sanitize_title( (string) $request->get_param( 'type' ) ) ) {
		$tax_query[] = array( 'taxonomy' => 'p10_category', 'field' => 'slug', 'terms' => $type );
	}
	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$meta_query = array();
	if ( $request->get_param( 'premium' ) ) {
		$meta_query[] = array( 'key' => 'p10_premium', 'value' => '1' );
	}
	if ( $request->get_param( 'verified' ) ) {
		$meta_query[] = array( 'key' => 'p10_verified', 'value' => '1' );
	}
	if ( $max_rate = absint( $request->get_param( 'max_rate' ) ) ) {
		$meta_query[] = array( 'key' => 'p10_rate', 'value' => $max_rate, 'type' => 'NUMERIC', 'compare' => '<=' );
	}
	if ( $meta_query ) {
		$meta_query['relation'] = 'AND';
		$args['meta_query']     = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$query = new WP_Query( $args );
	$items = array();

	foreach ( $query->posts as $post ) {
		$items[] = array(
			'id'        => $post->ID,
			'title'     => get_the_title( $post ),
			'url'       => get_permalink( $post ),
			'image'     => p10_profile_image( $post, 'p10-thumb' ),
			'location'  => p10_profile_location_line( $post ),
			'stats'     => p10_profile_stats_line( $post ),
			'premium'   => (bool) p10_meta( $post, 'premium' ),
			'verified'  => (bool) p10_meta( $post, 'verified' ),
			'available' => p10_profile_availability( $post ),
		);
	}

	return rest_ensure_response( array(
		'items' => $items,
		'total' => (int) $query->found_posts,
		'pages' => (int) $query->max_num_pages,
	) );
}

/**
 * REST callback: live directory counts for the stat strip.
 *
 * @return WP_REST_Response
 */
function p10_rest_stats() {
	return rest_ensure_response( array(
		'profiles' => p10_count_profiles(),
		'cities'   => count( p10_get_cities() ),
		'premium'  => count( p10_get_premium_profiles( 500 ) ),
	) );
}

/**
 * Related profiles: same city first, then same category.
 *
 * @param int|WP_Post $post  Profile.
 * @param int         $count Number of profiles.
 * @return WP_Post[]
 */
function p10_get_related_profiles( $post = null, $count = 3 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$city       = p10_get_primary_term( $post->ID, 'p10_city' );
	$category   = p10_get_primary_term( $post->ID, 'p10_category' );
	$tax_query  = array();

	if ( $city ) {
		$tax_query[] = array( 'taxonomy' => 'p10_city', 'field' => 'term_id', 'terms' => $city->term_id );
	}
	if ( $category ) {
		$tax_query[] = array( 'taxonomy' => 'p10_category', 'field' => 'term_id', 'terms' => $category->term_id );
	}

	$args = array(
		'post_type'      => 'p10_profile',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $count,
		'post__not_in'   => array( $post->ID ),
		'no_found_rows'  => true,
		'orderby'        => 'rand',
	);

	if ( $tax_query ) {
		$tax_query['relation'] = 'OR';
		$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$related = get_posts( $args );

	if ( count( $related ) < $count ) {
		$extra = get_posts( array(
			'post_type'      => 'p10_profile',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $count - count( $related ),
			'post__not_in'   => array_merge( array( $post->ID ), wp_list_pluck( $related, 'ID' ) ),
			'orderby'        => 'rand',
			'no_found_rows'  => true,
		) );
		$related = array_merge( $related, $extra );
	}

	return $related;
}

/**
 * Active filter pills with a link that removes each one.
 *
 * @param array $state  Current filter state from p10_filter_state().
 * @param array $pinned Values pinned by the current term archive.
 * @return array
 */
function p10_active_filters( $state, $pinned = array() ) {
	$base = strtok( p10_current_url(), '?' );

	// On a term archive the filters post to the flat directory instead.
	if ( is_tax( array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ) ) ) {
		$base = p10_directory_url();
	}

	$query = array();
	foreach ( $pinned as $key => $value ) {
		$query[ $key ] = $value;
	}

	$labels = array();

	if ( ! empty( $state['county'] ) ) {
		$term = get_term_by( 'slug', $state['county'], 'p10_county' );
		$labels['county'] = $term ? $term->name : $state['county'];
	}
	if ( ! empty( $state['city'] ) ) {
		$term = get_term_by( 'slug', $state['city'], 'p10_city' );
		$labels['city'] = $term ? $term->name : $state['city'];
	}
	if ( ! empty( $state['category'] ) ) {
		$term = get_term_by( 'slug', $state['category'], 'p10_category' );
		$labels['type'] = $term ? $term->name : $state['category'];
	}
	if ( ! empty( $_GET['nationality'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$term = get_term_by( 'slug', sanitize_title( wp_unslash( $_GET['nationality'] ) ), 'p10_nationality' ); // phpcs:ignore WordPress.Security.NonceVerification
		$labels['nationality'] = $term ? $term->name : __( 'Nationality', 'perfect10' );
	}
	if ( ! empty( $state['premium'] ) ) {
		$labels['premium'] = __( 'Premium', 'perfect10' );
	}
	if ( ! empty( $state['verified'] ) ) {
		$labels['verified'] = __( 'Verified', 'perfect10' );
	}
	if ( ! empty( $_GET['available'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$labels['available'] = __( 'Available now', 'perfect10' );
	}
	if ( ! empty( $state['max_rate'] ) ) {
		/* translators: %s: formatted rate. */
		$labels['max_rate'] = sprintf( __( 'Up to %s/hr', 'perfect10' ), p10_format_money( $state['max_rate'] ) );
	}
	if ( ! empty( $state['min_age'] ) || ! empty( $state['max_age'] ) ) {
		$labels['min_age'] = sprintf(
			/* translators: 1: minimum age, 2: maximum age. */
			__( 'Age %1$s–%2$s', 'perfect10' ),
			$state['min_age'] ? $state['min_age'] : 18,
			$state['max_age'] ? $state['max_age'] : 99
		);
	}
	if ( ! empty( $state['search'] ) ) {
		/* translators: %s: search query. */
		$labels['q'] = sprintf( __( '“%s”', 'perfect10' ), $state['search'] );
	}

	$pills = array();

	foreach ( $labels as $param => $label ) {
		// Pinned values come from the current term and cannot be removed.
		if ( array_key_exists( $param, $pinned ) ) {
			continue;
		}

		$remove = array( $param );
		if ( 'min_age' === $param ) {
			$remove[] = 'max_age';
		}

		$keep = array_diff_key( $query, array_flip( $remove ) );

		// Drop any other active filter values that are not being removed.
		foreach ( array_keys( $labels ) as $other ) {
			if ( in_array( $other, $remove, true ) || array_key_exists( $other, $keep ) ) {
				continue;
			}
			$value = isset( $_GET[ $other ] ) ? sanitize_text_field( wp_unslash( $_GET[ $other ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( '' !== $value ) {
				$keep[ $other ] = $value;
			}
		}

		$pills[] = array(
			'label' => $label,
			'url'   => $keep ? add_query_arg( array_map( 'rawurlencode', $keep ), $base ) : $base,
		);
	}

	// A single "clear everything" pill when several filters are active.
	if ( count( $pills ) > 1 ) {
		$pills[] = array(
			'label' => __( 'Clear all', 'perfect10' ),
			'url'   => $pinned ? add_query_arg( array_map( 'rawurlencode', $pinned ), $base ) : $base,
		);
	}

	return apply_filters( 'p10_active_filters', $pills, $state, $pinned );
}

/**
 * Build a directory WP_Query from the current filter state.
 *
 * Used by the Directory page template, which is a `page` and therefore has no
 * archive query of its own.
 *
 * @return WP_Query
 */
function p10_directory_query() {
	$state = p10_filter_state();

	$args = array(
		'post_type'      => 'p10_profile',
		'post_status'    => 'publish',
		'posts_per_page' => (int) p10_option( 'directory_per_page', 12 ),
		'paged'          => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
	);

	$tax_query = array();
	foreach ( array(
		'county'   => 'p10_county',
		'city'     => 'p10_city',
		'category' => 'p10_category',
	) as $state_key => $taxonomy ) {
		if ( ! empty( $state[ $state_key ] ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $state[ $state_key ],
			);
		}
	}
	if ( ! empty( $_GET['nationality'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$tax_query[] = array(
			'taxonomy' => 'p10_nationality',
			'field'    => 'slug',
			'terms'    => sanitize_title( wp_unslash( $_GET['nationality'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
		);
	}
	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$meta_query = array();
	if ( $state['premium'] ) {
		$meta_query[] = array( 'key' => 'p10_premium', 'value' => '1' );
	}
	if ( $state['verified'] ) {
		$meta_query[] = array( 'key' => 'p10_verified', 'value' => '1' );
	}
	if ( ! empty( $_GET['available'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta_query[] = array(
			'key'     => 'p10_available',
			'value'   => array( 'available', 'limited' ),
			'compare' => 'IN',
		);
	}
	if ( $state['max_rate'] ) {
		$meta_query[] = array(
			'key'     => 'p10_rate',
			'value'   => $state['max_rate'],
			'type'    => 'NUMERIC',
			'compare' => '<=',
		);
	}
	if ( $state['min_age'] || $state['max_age'] ) {
		$meta_query[] = array(
			'key'     => 'p10_age',
			'type'    => 'NUMERIC',
			'value'   => array( $state['min_age'] ? $state['min_age'] : 18, $state['max_age'] ? $state['max_age'] : 99 ),
			'compare' => 'BETWEEN',
		);
	}
	if ( $meta_query ) {
		$meta_query['relation'] = 'AND';
		$args['meta_query']     = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	if ( $state['search'] ) {
		$args['s'] = $state['search'];
	}

	switch ( $state['sort'] ) {
		case 'newest':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'age-asc':
			$args['meta_key'] = 'p10_age'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'age-desc':
			$args['meta_key'] = 'p10_age'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'rate-asc':
			$args['meta_key'] = 'p10_rate'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'rate-desc':
			$args['meta_key'] = 'p10_rate'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'name-asc':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		default:
			$args['meta_key'] = 'p10_featured_order'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = array(
				'meta_value_num' => 'ASC',
				'menu_order'     => 'ASC',
				'date'           => 'DESC',
			);
			break;
	}

	return new WP_Query( apply_filters( 'p10_directory_query_args', $args ) );
}

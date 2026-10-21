<?php
/**
 * Custom post types, taxonomies and the nested permalink structure.
 *
 * URL shape mirrors the reference build:
 *   /los-angeles-county/                       county archive
 *   /los-angeles-county/beverly-hills/         city archive
 *   /los-angeles-county/beverly-hills/nicky/   single profile
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register post types.
 */
function p10_register_post_types() {

	register_post_type( 'p10_profile', array(
		'labels'          => array(
			'name'               => __( 'Profiles', 'perfect10' ),
			'singular_name'      => __( 'Profile', 'perfect10' ),
			'add_new'            => __( 'Add Profile', 'perfect10' ),
			'add_new_item'       => __( 'Add New Profile', 'perfect10' ),
			'edit_item'          => __( 'Edit Profile', 'perfect10' ),
			'new_item'           => __( 'New Profile', 'perfect10' ),
			'view_item'          => __( 'View Profile', 'perfect10' ),
			'search_items'       => __( 'Search Profiles', 'perfect10' ),
			'not_found'          => __( 'No profiles found', 'perfect10' ),
			'not_found_in_trash' => __( 'No profiles found in Trash', 'perfect10' ),
			'all_items'          => __( 'All Profiles', 'perfect10' ),
			'menu_name'          => __( 'Profiles', 'perfect10' ),
		),
		'public'          => true,
		'has_archive'     => true,
		'menu_position'   => 21,
		'menu_icon'       => 'dashicons-id-alt',
		'rewrite'         => array( 'slug' => 'profiles', 'with_front' => false ),
		'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
		'taxonomies'      => array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ),
		'show_in_rest'    => true,
		'rest_base'       => 'profiles',
	) );

	register_post_type( 'p10_review', array(
		'labels'          => array(
			'name'          => __( 'Guest Reviews', 'perfect10' ),
			'singular_name' => __( 'Guest Review', 'perfect10' ),
			'add_new_item'  => __( 'Add New Review', 'perfect10' ),
			'edit_item'     => __( 'Edit Review', 'perfect10' ),
			'all_items'     => __( 'All Reviews', 'perfect10' ),
			'menu_name'     => __( 'Reviews', 'perfect10' ),
		),
		'public'          => true,
		'has_archive'     => true,
		'menu_position'   => 22,
		'menu_icon'       => 'dashicons-star-filled',
		'rewrite'         => array( 'slug' => 'reviews', 'with_front' => false ),
		'supports'        => array( 'title', 'editor', 'page-attributes' ),
		'show_in_rest'    => true,
	) );

	register_post_type( 'p10_package', array(
		'labels'          => array(
			'name'          => __( 'Premium Packages', 'perfect10' ),
			'singular_name' => __( 'Premium Package', 'perfect10' ),
			'add_new_item'  => __( 'Add New Package', 'perfect10' ),
			'edit_item'     => __( 'Edit Package', 'perfect10' ),
			'all_items'     => __( 'All Packages', 'perfect10' ),
			'menu_name'     => __( 'Packages', 'perfect10' ),
		),
		'public'          => true,
		'has_archive'     => false,
		'menu_position'   => 23,
		'menu_icon'       => 'dashicons-awards',
		'rewrite'         => array( 'slug' => 'package', 'with_front' => false ),
		'supports'        => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'    => true,
	) );

	register_post_type( 'p10_enquiry', array(
		'labels'          => array(
			'name'          => __( 'Enquiries', 'perfect10' ),
			'singular_name' => __( 'Enquiry', 'perfect10' ),
			'all_items'     => __( 'All Enquiries', 'perfect10' ),
			'menu_name'     => __( 'Enquiries', 'perfect10' ),
			'not_found'     => __( 'No enquiries yet', 'perfect10' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 24,
		'menu_icon'       => 'dashicons-email-alt',
		'supports'        => array( 'title', 'editor', 'custom-fields' ),
		'show_in_rest'    => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'capability_type' => 'post',
		'capabilities'    => array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts' ), 'manage_options' ),
		'map_meta_cap'    => false, // All enquiry capabilities are the administrator primitive, not post meta caps.
	) );
}
add_action( 'init', 'p10_register_post_types', 5 );

/**
 * Register taxonomies.
 */
function p10_register_taxonomies() {

	register_taxonomy( 'p10_county', array( 'p10_profile' ), array(
		'labels'            => array(
			'name'          => __( 'Counties', 'perfect10' ),
			'singular_name' => __( 'County', 'perfect10' ),
			'all_items'     => __( 'All Counties', 'perfect10' ),
			'edit_item'     => __( 'Edit County', 'perfect10' ),
			'menu_name'     => __( 'Counties', 'perfect10' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false, // Handled by p10_add_nested_rules().
	) );

	register_taxonomy( 'p10_city', array( 'p10_profile' ), array(
		'labels'            => array(
			'name'          => __( 'Cities', 'perfect10' ),
			'singular_name' => __( 'City', 'perfect10' ),
			'all_items'     => __( 'All Cities', 'perfect10' ),
			'search_items'  => __( 'Search Cities', 'perfect10' ),
			'edit_item'     => __( 'Edit City', 'perfect10' ),
			'menu_name'     => __( 'Cities', 'perfect10' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false, // Handled by p10_add_nested_rules().
	) );

	register_taxonomy( 'p10_category', array( 'p10_profile' ), array(
		'labels'            => array(
			'name'          => __( 'Profile Types', 'perfect10' ),
			'singular_name' => __( 'Profile Type', 'perfect10' ),
			'all_items'     => __( 'All Profile Types', 'perfect10' ),
			'edit_item'     => __( 'Edit Profile Type', 'perfect10' ),
			'menu_name'     => __( 'Profile Types', 'perfect10' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'browse', 'with_front' => false ),
	) );

	register_taxonomy( 'p10_nationality', array( 'p10_profile' ), array(
		'labels'            => array(
			'name'          => __( 'Nationalities', 'perfect10' ),
			'singular_name' => __( 'Nationality', 'perfect10' ),
			'all_items'     => __( 'All Nationalities', 'perfect10' ),
			'menu_name'     => __( 'Nationalities', 'perfect10' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'nationality', 'with_front' => false ),
	) );
}
add_action( 'init', 'p10_register_taxonomies', 6 );

/* -------------------------------------------------------------------------
 * Nested permalinks: /{county}/{city}/{profile}/
 * ---------------------------------------------------------------------- */

/**
 * County taxonomy links live at the site root.
 *
 * @param string $link Default link.
 * @param object $term Term object.
 * @return string
 */
function p10_county_link( $link, $term ) {
	if ( 'p10_county' !== $term->taxonomy ) {
		return $link;
	}
	$base = trailingslashit( home_url( '/' ) );
	return $base . $term->slug . '/';
}
add_filter( 'term_link', 'p10_county_link', 10, 2 );

/**
 * Build the URL for a city term from its county term meta.
 *
 * @param string $link Default link.
 * @param object $term Term object.
 * @return string
 */
function p10_city_link( $link, $term ) {
	if ( 'p10_city' !== $term->taxonomy ) {
		return $link;
	}
	$county = p10_city_county_slug( $term );
	$base   = trailingslashit( home_url( '/' ) );

	if ( $county ) {
		return $base . $county . '/' . $term->slug . '/';
	}
	return $base . 'city/' . $term->slug . '/';
}
add_filter( 'term_link', 'p10_city_link', 11, 2 );

/**
 * Profile permalinks nest under county and city.
 *
 * @param string  $post_link Default permalink.
 * @param WP_Post $post      Post object.
 * @return string
 */
function p10_profile_link( $post_link, $post ) {
	if ( 'p10_profile' !== $post->post_type ) {
		return $post_link;
	}

	$city   = p10_get_primary_term( $post->ID, 'p10_city' );
	$county = p10_get_primary_term( $post->ID, 'p10_county' );

	if ( ! $county && $city ) {
		$county_slug = p10_city_county_slug( $city );
	} elseif ( $county ) {
		$county_slug = $county->slug;
	} else {
		$county_slug = '';
	}

	$base = trailingslashit( home_url( '/' ) );

	if ( $county_slug && $city ) {
		return $base . $county_slug . '/' . $city->slug . '/' . $post->post_name . '/';
	}
	if ( $county_slug ) {
		return $base . $county_slug . '/' . $post->post_name . '/';
	}
	return $post_link; // Falls back to /profiles/{slug}/.
}
add_filter( 'post_type_link', 'p10_profile_link', 10, 2 );

/**
 * Register rewrite rules for the nested structure.
 *
 * Rules are generated from real county slugs so they are anchored and cannot
 * swallow ordinary page URLs.
 */
function p10_add_nested_rules() {
	$counties = get_terms( array(
		'taxonomy'   => 'p10_county',
		'hide_empty' => false,
		'fields'     => 'slugs',
	) );

	if ( is_wp_error( $counties ) || empty( $counties ) ) {
		return;
	}

	foreach ( $counties as $county ) {
		$county = preg_quote( $county, '#' );

		// /{county}/{city}/{profile}/
		add_rewrite_rule(
			'^' . $county . '/([^/]+)/([^/]+)/?$',
			'index.php?p10_profile=$matches[2]&p10_city=$matches[1]&p10_county=' . $county,
			'top'
		);

		// /{county}/{city}/
		// Only the city query var is set here: adding p10_county as well makes
		// WordPress resolve the request to the county archive instead.
		add_rewrite_rule(
			'^' . $county . '/([^/]+)/?$',
			'index.php?p10_city=$matches[1]',
			'top'
		);

		// /{county}/
		add_rewrite_rule(
			'^' . $county . '/?$',
			'index.php?p10_county=' . $county,
			'top'
		);
	}

	// Fallback for cities with no county assignment.
	add_rewrite_rule( '^city/([^/]+)/?$', 'index.php?p10_city=$matches[1]', 'top' );
}
add_action( 'init', 'p10_add_nested_rules', 20 );

/**
 * Flush rewrite rules once after the theme is switched on.
 */
function p10_flush_rules_once() {
	if ( get_option( 'p10_rewrite_flushed_' . P10_VERSION ) ) {
		return;
	}
	p10_add_nested_rules();
	flush_rewrite_rules();
	update_option( 'p10_rewrite_flushed_' . P10_VERSION, 1 );
}
add_action( 'after_switch_theme', 'p10_flush_rules_once' );
add_action( 'init', 'p10_flush_rules_once', 99 );

/**
 * Flush permalinks when a county is created, renamed or deleted.
 *
 * @param int $term_id Term ID.
 */
function p10_maybe_flush_on_county_change( $term_id ) {
	$term = get_term( $term_id, 'p10_county' );
	if ( $term && ! is_wp_error( $term ) ) {
		// The new slug cannot be in the cached rule set yet: rebuild the
		// nested rules for this request before flushing.
		p10_add_nested_rules();
		flush_rewrite_rules( false );
	}
}
add_action( 'created_p10_county', 'p10_maybe_flush_on_county_change' );
add_action( 'edited_p10_county', 'p10_maybe_flush_on_county_change' );
add_action( 'delete_p10_county', 'p10_maybe_flush_on_county_change' );

/**
 * Register extra query vars used by the directory filters.
 *
 * @param array $vars Query vars.
 * @return array
 */
function p10_query_vars( $vars ) {
	$vars[] = 'p10_premium';
	$vars[] = 'p10_verified';
	$vars[] = 'p10_available';
	$vars[] = 'p10_min_age';
	$vars[] = 'p10_max_age';
	$vars[] = 'p10_max_rate';
	$vars[] = 'p10_sort';
	$vars[] = 'p10_s';
	return $vars;
}
add_filter( 'query_vars', 'p10_query_vars' );

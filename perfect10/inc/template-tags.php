<?php
/**
 * Template tags and data helpers.
 *
 * Every number, label and collection the templates render comes from here, so
 * the theme has no hard-coded directory content.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a theme option (Customizer / options table) with a default.
 *
 * @param string $key     Option key without prefix.
 * @param mixed  $default Default value.
 * @return mixed
 */
function p10_option( $key, $default = '' ) {
	$defaults = p10_option_defaults();
	$fallback = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : $default;

	$value = get_theme_mod( 'p10_' . $key, null );
	if ( null === $value ) {
		$value = get_option( 'p10_' . $key, null );
	}
	if ( null === $value || '' === $value ) {
		return $fallback;
	}
	return $value;
}

/**
 * Default values for every theme option.
 *
 * @return array
 */
function p10_option_defaults() {
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}

	$defaults = array(
		// Brand.
		'brand_name'        => 'Perfect 10',
		'brand_suffix'      => 'LA · Orange County',
		'est_year'          => '2010',

		// Contact.
		'phone_display'     => '+1 (951) 580-1742',
		'phone_raw'         => '+19515801742',
		'email'             => 'booking@example.com',
		'contact_note'      => 'Please do not call from a blocked number.',

		// Hero.
		'hero_eyebrow'      => 'Los Angeles · Orange County · Est. 2010',
		'hero_title_before' => 'Discover Your',
		'hero_title_accent' => 'Perfect 10',
		'hero_tagline'      => 'Independent ◆ Verified ◆ Discreet',
		'hero_copy'         => 'An 18+ referral network of independent companions across Los Angeles and Orange County. Review featured profiles and request a private referral through our boutique network.',
		'hero_cta_primary'  => 'Explore Profiles',
		'hero_cta_secondary' => 'Call',
		'hero_scroll_label' => 'Begin',
		'hero_overlay'      => 62,

		// Marquee.
		'marquee_items'     => 'Independent Companions,Since 2010,24/7 Booking Agent,18+ Referral Service,LA & Orange County,Discreet by Design',
		'marquee_enabled'   => 1,

		// Search panel.
		'search_kicker'     => 'Find Your Companion',
		'search_title'      => 'Where are you',
		'search_accent'     => 'looking?',
		'search_label'      => 'Where are you looking?',
		'search_region'     => 'Los Angeles & Orange County',
		'search_button'     => 'Search',

		// Toggles.
		'age_gate_enabled'  => 1,
		'age_gate_title'    => 'Adults only',
		'age_gate_copy'     => 'This is an 18+ referral directory. By entering you confirm that you are at least 18 years of age and consent to viewing adult content.',
		'age_gate_enter'    => 'I am 18 or older — Enter',
		'age_gate_exit'     => 'Exit',
		'concierge_enabled' => 1,
		'concierge_label'   => 'Message',
		'concierge_note'    => 'AI concierge · 18+ referral service · Your details stay private',
		'reduced_motion'    => 0,
	);

	$defaults = apply_filters( 'p10_option_defaults', $defaults );
	return $defaults;
}

/**
 * Is the current request any kind of directory listing?
 *
 * @return bool
 */
function p10_is_directory() {
	return is_post_type_archive( 'p10_profile' )
		|| is_tax( array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ) )
		|| is_page_template( 'page-templates/directory.php' );
}

/**
 * Get the first assigned term of a taxonomy for a post.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy.
 * @return WP_Term|null
 */
function p10_get_primary_term( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	return array_shift( $terms );
}

/**
 * Resolve which county a city term belongs to.
 *
 * Falls back to the term's parent, then to the first county that claims it.
 *
 * @param WP_Term|int $term City term.
 * @return string County slug or empty string.
 */
function p10_city_county_slug( $term ) {
	if ( is_int( $term ) ) {
		$term = get_term( $term, 'p10_city' );
	}
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}

	$county = p10_term_meta( $term, 'county' );
	if ( $county ) {
		return $county;
	}

	if ( ! empty( $term->parent ) ) {
		$parent = get_term( $term->parent, 'p10_city' );
		if ( $parent && ! is_wp_error( $parent ) ) {
			return p10_city_county_slug( $parent );
		}
	}

	return '';
}

/**
 * All counties, ordered.
 *
 * @return WP_Term[]
 */
function p10_get_counties() {
	$terms = get_terms( array(
		'taxonomy'   => 'p10_county',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		return $terms;
	}

	// Fresh install: serve the bundled dataset so the UI is never empty.
	return p10_fallback_counties();
}

/**
 * Cities for one county, or every city when $county is empty.
 *
 * @param string $county County slug.
 * @return WP_Term[]
 */
function p10_get_cities( $county = '' ) {
	$cities = get_terms( array(
		'taxonomy'   => 'p10_city',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	if ( is_wp_error( $cities ) || empty( $cities ) ) {
		$cities = p10_fallback_cities( $county );
		return $cities;
	}

	if ( ! $county ) {
		return $cities;
	}

	$filtered = array();
	foreach ( $cities as $city ) {
		if ( p10_city_county_slug( $city ) === $county ) {
			$filtered[] = $city;
		}
	}
	return $filtered;
}

/**
 * Cities grouped by county, for the cascading dropdowns.
 *
 * @return array
 */
function p10_get_cities_by_county() {
	$grouped = array();
	foreach ( p10_get_counties() as $county ) {
		$grouped[ $county->slug ] = array(
			'label'  => $county->name,
			'cities' => array(),
		);
	}
	$grouped[''] = array( 'label' => __( 'Unclassified', 'perfect10' ), 'cities' => array() );

	foreach ( p10_get_cities() as $city ) {
		$slug = p10_city_county_of( $city );
		if ( ! isset( $grouped[ $slug ] ) ) {
			$grouped[ $slug ] = array( 'label' => ucwords( str_replace( '-', ' ', $slug ) ), 'cities' => array() );
		}
		$grouped[ $slug ]['cities'][] = $city;
	}

	if ( empty( $grouped['']['cities'] ) ) {
		unset( $grouped[''] );
	}
	return $grouped;
}

/**
 * Profile type categories, ordered.
 *
 * @return WP_Term[]
 */
function p10_get_categories() {
	$terms = get_terms( array(
		'taxonomy'   => 'p10_category',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}

	usort( $terms, static function ( $a, $b ) {
		$oa = (int) p10_term_meta( $a, 'sort', 0 );
		$ob = (int) p10_term_meta( $b, 'sort', 0 );
		if ( $oa === $ob ) {
			return strcmp( $a->name, $b->name );
		}
		return $oa < $ob ? -1 : 1;
	} );

	return $terms;
}

/**
 * Format a money amount using the active currency.
 *
 * @param float|int|string $amount   Amount.
 * @param string           $currency Currency code.
 * @return string
 */
function p10_format_money( $amount, $currency = '' ) {
	if ( '' === $amount || null === $amount ) {
		return '';
	}
	$currency = $currency ? $currency : (string) p10_option( 'currency', 'USD' );
	$symbols  = array(
		'USD' => '$',
		'CAD' => 'CA$',
		'AUD' => 'A$',
		'EUR' => '€',
		'GBP' => '£',
	);
	$symbol   = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency . ' ';

	return $symbol . number_format_i18n( (float) $amount );
}

/**
 * The "21 yrs · 152 cm · from $800/hr" line for a profile card.
 *
 * @param int|WP_Post $post Profile.
 * @return string
 */
function p10_profile_stats_line( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$parts = array();

	$age = p10_meta( $post, 'age' );
	if ( $age ) {
		/* translators: %s: age in years. */
		$parts[] = sprintf( __( '%s yrs', 'perfect10' ), (int) $age );
	}

	$height = p10_meta( $post, 'height_cm' );
	if ( $height ) {
		$parts[] = (int) $height . ' cm';
	}

	$rate = p10_meta( $post, 'rate' );
	if ( $rate ) {
		$currency = (string) p10_meta( $post, 'currency', p10_option( 'currency', 'USD' ) );
		/* translators: %s: formatted hourly rate. */
		$parts[] = sprintf( __( 'from %s/hr', 'perfect10' ), p10_format_money( $rate, $currency ) );
	}

	return implode( ' · ', $parts );
}

/**
 * The "Los Angeles · British" line for a profile card.
 *
 * @param int|WP_Post $post Profile.
 * @return string
 */
function p10_profile_location_line( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$explicit = p10_meta( $post, 'headline' );
	if ( $explicit ) {
		return $explicit;
	}

	$parts = array();

	$city = p10_get_primary_term( $post->ID, 'p10_city' );
	if ( $city ) {
		$parts[] = $city->name;
	}

	$nationality = p10_get_primary_term( $post->ID, 'p10_nationality' );
	if ( $nationality ) {
		$parts[] = $nationality->name;
	}

	return implode( ' · ', $parts );
}

/**
 * Availability slug => label map.
 *
 * @return array
 */
function p10_availability_options() {
	return apply_filters( 'p10_availability_options', array(
		'unknown'   => __( 'Availability not verified', 'perfect10' ),
		'available' => __( 'Available', 'perfect10' ),
		'limited'   => __( 'Limited', 'perfect10' ),
		'booked'    => __( 'Fully booked', 'perfect10' ),
		'offline'   => __( 'Offline', 'perfect10' ),
	) );
}

/**
 * Availability label and modifier class.
 *
 * @param int|WP_Post $post Profile.
 * @return array{label:string,slug:string}
 */
function p10_profile_availability( $post = null ) {
	$post = get_post( $post );
	$slug = $post ? (string) p10_meta( $post, 'available', 'available' ) : 'available';
	$map  = p10_availability_options();

	if ( ! isset( $map[ $slug ] ) ) {
		$slug = 'available';
	}

	return array( 'label' => $map[ $slug ], 'slug' => $slug );
}

/**
 * Is this profile bookable?
 *
 * @param int|WP_Post $post Profile.
 * @return bool
 */
function p10_profile_is_available( $post = null ) {
	$availability = p10_profile_availability( $post );
	return in_array( $availability['slug'], array( 'available', 'limited' ), true );
}

/**
 * Zero-padded editorial number, e.g. "№ 01".
 *
 * @param int $index 1-based index.
 * @return string
 */
function p10_number( $index ) {
	return sprintf( '%02d', (int) $index );
}

/**
 * Featured profiles for "This Month's Muses".
 *
 * @param int $count Number of profiles.
 * @return WP_Post[]
 */
function p10_get_featured_profiles( $count = 6 ) {
	$query = new WP_Query( array(
		'post_type'      => 'p10_profile',
		'posts_per_page' => (int) $count,
		'post_status'    => 'publish',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'OR',
			array(
				'key'     => 'p10_featured_order',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => 'p10_premium',
				'value'   => '1',
				'compare' => '=',
			),
		),
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	) );

	$posts = $query->posts;

	if ( count( $posts ) < $count ) {
		$have = wp_list_pluck( $posts, 'ID' );
		$fill = get_posts( array(
			'post_type'      => 'p10_profile',
			'posts_per_page' => (int) $count - count( $posts ),
			'post__not_in'   => $have,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );
		$posts = array_merge( $posts, $fill );
	}

	// Sort by explicit featured order where one is set.
	usort( $posts, static function ( $a, $b ) {
		$oa = (int) p10_get_meta( $a->ID, 'featured_order', 0 );
		$ob = (int) p10_get_meta( $b->ID, 'featured_order', 0 );
		if ( $oa && ! $ob ) {
			return -1;
		}
		if ( $ob && ! $oa ) {
			return 1;
		}
		if ( $oa === $ob ) {
			return 0;
		}
		return $oa < $ob ? -1 : 1;
	} );

	return array_slice( $posts, 0, (int) $count );
}

/**
 * Premium profiles for "The Premium Collection".
 *
 * @param int $count Number of profiles.
 * @return WP_Post[]
 */
function p10_get_premium_profiles( $count = 6 ) {
	$query = new WP_Query( array(
		'post_type'      => 'p10_profile',
		'posts_per_page' => (int) $count,
		'post_status'    => 'publish',
		'meta_key'       => 'p10_premium', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	) );
	return $query->posts;
}

/**
 * Featured city terms for "Where the Night Takes You".
 *
 * @param int $count Number of cities.
 * @return WP_Term[]
 */
function p10_get_featured_cities( $count = 9 ) {
	$cities   = p10_get_cities();
	$featured = array();
	$rest     = array();

	foreach ( $cities as $city ) {
		if ( p10_term_meta( $city, 'featured' ) ) {
			$featured[] = $city;
		} else {
			$rest[] = $city;
		}
	}

	usort( $featured, static function ( $a, $b ) {
		return (int) p10_term_meta( $a, 'sort', 0 ) <=> (int) p10_term_meta( $b, 'sort', 0 );
	} );

	$ordered = array_merge( $featured, $rest );
	return array_slice( $ordered, 0, (int) $count );
}

/**
 * Published profile count for a term (or site-wide).
 *
 * @param WP_Term|null $term Optional term to scope the count to.
 * @return int
 */
function p10_count_profiles( $term = null ) {
	$args = array(
		'post_type'      => 'p10_profile',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
	);

	if ( $term instanceof WP_Term ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => $term->taxonomy,
				'field'    => 'term_id',
				'terms'    => $term->term_id,
			),
		);
	}

	$query = new WP_Query( $args );
	return (int) $query->found_posts;
}

/**
 * The homepage stat strip. Counts are live unless overridden.
 *
 * @return array
 */
function p10_get_stats() {
	$profiles = (int) p10_option( 'stat_profiles', 0 );
	$cities   = (int) p10_option( 'stat_cities', 0 );

	if ( ! $profiles ) {
		$profiles = p10_count_profiles();
	}
	if ( ! $cities ) {
		$city_terms = p10_get_cities();
		$cities     = count( $city_terms );
	}

	$stats = array(
		array(
			'value' => $profiles ? $profiles . '+' : '—',
			'label' => (string) p10_option( 'stat_profiles_label', __( 'Verified Profiles', 'perfect10' ) ),
		),
		array(
			'value' => $cities ? $cities . '+' : '—',
			'label' => (string) p10_option( 'stat_cities_label', __( 'Cities Covered', 'perfect10' ) ),
		),
		array(
			'value' => (string) p10_option( 'stat_agent_value', '24/7' ),
			'label' => (string) p10_option( 'stat_agent_label', __( 'Booking Agent', 'perfect10' ) ),
		),
		array(
			'value' => 'Est. ' . p10_option( 'est_year', '2010' ),
			'label' => (string) p10_option( 'stat_years_label', __( '16+ Years', 'perfect10' ) ),
		),
	);

	return apply_filters( 'p10_stats', $stats );
}

/**
 * Marquee ticker items.
 *
 * @return array
 */
function p10_get_marquee_items() {
	$raw = (string) p10_option( 'marquee_items', '' );
	if ( '' === $raw ) {
		return array();
	}
	$items = array_filter( array_map( 'trim', preg_split( '/[,|\n]/', $raw ) ) );
	return array_values( $items );
}

/**
 * Phone number for tel: links.
 *
 * @return string
 */
function p10_phone_raw() {
	$raw = (string) p10_option( 'phone_raw', '' );
	if ( $raw ) {
		return preg_replace( '/[^0-9+]/', '', $raw );
	}
	return preg_replace( '/[^0-9+]/', '', (string) p10_option( 'phone_display', '' ) );
}

/**
 * Display-formatted phone number.
 *
 * @return string
 */
function p10_phone_display() {
	return (string) p10_option( 'phone_display', '' );
}

/**
 * tel: href.
 *
 * @return string
 */
function p10_phone_href() {
	return 'tel:' . p10_phone_raw();
}

/**
 * Find a published page by slug and return its permalink.
 *
 * @param string $slug     Page slug.
 * @param string $fallback Fallback URL.
 * @return string
 */
function p10_page_url( $slug, $fallback = '' ) {
	$cache_key = 'p10_page_url_' . sanitize_key( $slug );
	$cached    = wp_cache_get( $cache_key, 'perfect10' );

	if ( false !== $cached ) {
		return $cached ? $cached : $fallback;
	}

	$page = get_page_by_path( $slug );

	if ( ! $page ) {
		$pages = get_posts( array(
			'post_type'      => 'page',
			'name'           => $slug,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		) );
		$page = $pages ? get_post( $pages[0] ) : null;
	}

	$url = $page ? get_permalink( $page ) : '';

	wp_cache_set( $cache_key, $url, 'perfect10', HOUR_IN_SECONDS );

	return $url ? $url : $fallback;
}

/**
 * Directory listing URL ("View the full directory").
 *
 * @param array $args Optional filter args, e.g. array('premium' => 1).
 * @return string
 */
function p10_directory_url( $args = array() ) {
	$page = (string) p10_option( 'directory_page', '' );
	$url  = $page ? get_permalink( (int) $page ) : '';

	if ( ! $url ) {
		$url = get_post_type_archive_link( 'p10_profile' );
	}
	if ( ! $url ) {
		$url = p10_page_url( 'directory', home_url( '/' ) );
	}

	if ( $args ) {
		$url = add_query_arg( array_map( 'rawurlencode', $args ), $url );
	}
	return $url;
}

/**
 * URL for a profile type category.
 *
 * @param string $slug Category slug, e.g. trans-escorts.
 * @return string
 */
function p10_category_url( $slug ) {
	$term = get_term_by( 'slug', $slug, 'p10_category' );

	if ( $term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	return p10_directory_url( array( 'type' => $slug ) );
}

/**
 * Convenience URLs for the utility pages.
 *
 * @param string $which One of booking, reviews, packages, register.
 * @return string
 */
function p10_utility_url( $which ) {
	$map = array(
		'booking'  => array( 'booking', 'booking-page' ),
		'reviews'  => array( 'reviews', get_post_type_archive_link( 'p10_review' ) ),
		'packages' => array( 'premium-packages', 'premium-packages' ),
		'register' => array( 'register-independent-profile', 'register' ),
		'cities'   => array( 'all-cities', 'all-cities' ),
		'directory' => array( 'all-independent-escorts', '' ),
	);

	if ( ! isset( $map[ $which ] ) ) {
		return home_url( '/' );
	}

	list( $slug, $fallback ) = $map[ $which ];

	return p10_page_url( $slug, is_string( $fallback ) ? home_url( '/' ) . $fallback . '/' : $fallback );
}

/**
 * Gallery attachment IDs for a profile, cover first.
 *
 * @param int|WP_Post $post Profile.
 * @return int[]
 */
function p10_profile_gallery( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$ids = array_filter( array_map( 'intval', explode( ',', (string) p10_meta( $post, 'gallery', '' ) ) ) );

	$cover = get_post_thumbnail_id( $post );
	if ( $cover ) {
		array_unshift( $ids, (int) $cover );
	}

	return array_values( array_unique( $ids ) );
}

/**
 * Profile image URL with a graceful placeholder.
 *
 * @param int|WP_Post $post Profile.
 * @param string      $size Image size.
 * @return string
 */
function p10_profile_image( $post = null, $size = 'p10-card' ) {
	$post = get_post( $post );
	if ( $post && has_post_thumbnail( $post ) ) {
		$image = wp_get_attachment_image_url( get_post_thumbnail_id( $post ), $size );
		if ( $image ) {
			return $image;
		}
	}
	return P10_URI . 'assets/images/placeholder-profile.svg';
}

/**
 * Term image URL with a graceful placeholder.
 *
 * @param WP_Term $term Term.
 * @param string  $size Image size.
 * @return string
 */
function p10_term_image( $term, $size = 'p10-card-wide' ) {
	$attachment = (int) p10_term_meta( $term, 'image', 0 );
	if ( $attachment ) {
		$image = wp_get_attachment_image_url( $attachment, $size );
		if ( $image ) {
			return $image;
		}
	}
	return P10_URI . 'assets/images/placeholder-city.svg';
}

/**
 * Attachment ID for a term cover image.
 *
 * @param WP_Term $term Term.
 * @return int
 */
function p10_term_image_id( $term ) {
	return absint( p10_term_meta( $term, 'image', 0 ) );
}

/**
 * Resolved cover image data for a term.
 *
 * @param WP_Term $term     Term.
 * @param string  $size     Image size.
 * @param mixed   $override Optional attachment ID or URL.
 * @return array{url:string,srcset:string,alt:string,width:int,height:int,id:int,placeholder:bool}
 */
function p10_term_image_data( $term, $size = 'p10-card-wide', $override = null ) {
	$data = array(
		'url'         => '',
		'srcset'      => '',
		'alt'         => $term instanceof WP_Term ? $term->name : '',
		'width'       => 0,
		'height'      => 0,
		'id'          => 0,
		'placeholder' => false,
	);

	$attachment = 0;
	$url        = '';

	if ( is_numeric( $override ) && (int) $override > 0 ) {
		$attachment = (int) $override;
	} elseif ( is_string( $override ) && $override ) {
		$url = esc_url_raw( $override );
	} elseif ( is_array( $override ) && ! empty( $override['id'] ) ) {
		$attachment = absint( $override['id'] );
	}

	if ( ! $attachment && ! $url && $term instanceof WP_Term ) {
		$attachment = p10_term_image_id( $term );
	}

	if ( $term instanceof WP_Term ) {
		$term_alt = (string) p10_term_meta( $term, 'image_alt', '' );
		if ( $term_alt ) {
			$data['alt'] = $term_alt;
		}
	}

	if ( $attachment && wp_attachment_is_image( $attachment ) ) {
		$src = wp_get_attachment_image_src( $attachment, $size );
		if ( $src ) {
			$data['url']    = (string) $src[0];
			$data['width']  = (int) $src[1];
			$data['height'] = (int) $src[2];
		}
		$srcset = wp_get_attachment_image_srcset( $attachment, $size );
		$alt    = get_post_meta( $attachment, '_wp_attachment_image_alt', true );
		if ( $srcset ) {
			$data['srcset'] = (string) $srcset;
		}
		if ( $alt && '' === (string) ( $term instanceof WP_Term ? p10_term_meta( $term, 'image_alt', '' ) : '' ) ) {
			$data['alt'] = (string) $alt;
		}
		$data['id'] = $attachment;
	}

	if ( ! $data['url'] ) {
		if ( $url ) {
			$data['url'] = $url;
		} elseif ( $term instanceof WP_Term ) {
			$data['url']         = p10_term_image( $term, $size );
			$data['placeholder'] = true;
		}
	}

	return $data;
}

/**
 * Responsive <img> for a term cover image.
 *
 * @param WP_Term $term     Term.
 * @param string  $size     Image size.
 * @param string  $class    CSS classes.
 * @param string  $alt      Alt text; the term name is used when empty.
 * @param string  $sizes    sizes attribute.
 * @param array   $args     Optional: override (ID/URL), loading, fetchpriority, style.
 * @return string
 */
function p10_term_image_tag( $term, $size = 'p10-card-wide', $class = '', $alt = '', $sizes = '(max-width: 767px) 100vw, 33vw', $args = array() ) {
	if ( ! $term instanceof WP_Term && empty( $args['override'] ) ) {
		return '';
	}

	$image = p10_term_image_data( $term, $size, isset( $args['override'] ) ? $args['override'] : null );
	if ( ! $image['url'] ) {
		return '';
	}

	$classes = trim( (string) $class . ( $image['id'] ? ' wp-image-' . $image['id'] : '' ) );
	$attrs   = array(
		'class'    => $classes,
		'alt'      => $alt ? $alt : $image['alt'],
		'decoding' => 'async',
		'loading'  => isset( $args['loading'] ) ? $args['loading'] : 'lazy',
	);
	if ( ! empty( $args['fetchpriority'] ) ) {
		$attrs['fetchpriority'] = $args['fetchpriority'];
	}
	if ( $image['srcset'] ) {
		$attrs['srcset'] = $image['srcset'];
		$attrs['sizes']  = $sizes;
	}
	if ( $image['width'] && $image['height'] ) {
		$attrs['width']  = $image['width'];
		$attrs['height'] = $image['height'];
	}
	if ( ! empty( $args['style'] ) ) {
		$attrs['style'] = $args['style'];
	}

	$html = '<img src="' . esc_url( $image['url'] ) . '"';
	foreach ( $attrs as $name => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}
		$html .= ' ' . $name . '="' . esc_attr( $value ) . '"';
	}
	$html .= '>';

	return $html;
}

/**
 * Hero background image URL.
 *
 * @return string
 */
function p10_hero_image() {
	$custom = (int) p10_option( 'hero_image', 0 );
	if ( $custom ) {
		$image = wp_get_attachment_image_url( $custom, 'p10-hero' );
		if ( $image ) {
			return $image;
		}
	}
	if ( is_front_page() && has_post_thumbnail() ) {
		$image = wp_get_attachment_image_url( get_post_thumbnail_id(), 'p10-hero' );
		if ( $image ) {
			return $image;
		}
	}
	// p10_option() returns the registered default (empty string) before the
	// argument fallback, so an unset hero URL must fall back explicitly.
	$url = (string) p10_option( 'hero_image_url', '' );
	if ( ! $url ) {
		$url = P10_URI . 'assets/images/placeholder-hero.svg';
	}

	return (string) apply_filters( 'p10_hero_image_url', $url );
}

/**
 * Split a heading into "before" and italic accent halves.
 *
 * Text after an asterisk, or an explicit pair of options, becomes the accent.
 *
 * @param string $heading Heading text.
 * @param string $accent  Optional explicit accent override.
 * @return array{before:string,accent:string}
 */
function p10_split_heading( $heading, $accent = '' ) {
	if ( $accent ) {
		return array(
			'before' => trim( $heading ),
			'accent' => trim( $accent ),
		);
	}

	if ( false !== strpos( $heading, '*' ) ) {
		$parts = explode( '*', $heading, 2 );
		return array(
			'before' => trim( $parts[0] ),
			'accent' => trim( $parts[1] ),
		);
	}

	$words = preg_split( '/\s+/', trim( $heading ) );
	if ( count( $words ) > 2 ) {
		$accent_word = array_pop( $words );
		return array(
			'before' => implode( ' ', $words ),
			'accent' => $accent_word,
		);
	}

	return array( 'before' => trim( $heading ), 'accent' => '' );
}

/**
 * Render a heading with the brand's italic gold accent.
 *
 * @param string $heading Heading text.
 * @param string $accent  Optional explicit accent.
 * @param string $tag     Heading tag.
 * @param string $class   Extra classes.
 */
function p10_the_heading( $heading, $accent = '', $tag = 'h2', $class = '' ) {
	$parts = p10_split_heading( $heading, $accent );
	$tag   = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' ), true ) ? $tag : 'h2';

	echo '<' . esc_attr( $tag ) . ' class="p10-heading ' . esc_attr( $class ) . '">';
	echo esc_html( $parts['before'] );
	if ( $parts['accent'] ) {
		echo ' <em>' . esc_html( $parts['accent'] ) . '</em>';
	}
	echo '</' . esc_attr( $tag ) . '>';
}

/**
 * Comma separated tag list rendered as an array.
 *
 * @param int|WP_Post $post Profile.
 * @param string      $key  Meta key.
 * @return string[]
 */
function p10_profile_tags( $post = null, $key = 'services' ) {
	$raw = (string) p10_meta( $post, $key, '' );
	if ( '' === $raw ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
}

/**
 * Build a "stat" list for the single profile spec table.
 *
 * @param int|WP_Post $post Profile.
 * @return array
 */
function p10_profile_spec_list( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$rows   = array();
	$fields = array(
		'age'       => __( 'Age', 'perfect10' ),
		'height_cm' => __( 'Height', 'perfect10' ),
		'body_type' => __( 'Body type', 'perfect10' ),
		'bust'      => __( 'Bust', 'perfect10' ),
		'cup'       => __( 'Cup', 'perfect10' ),
		'waist'     => __( 'Waist', 'perfect10' ),
		'hips'      => __( 'Hips', 'perfect10' ),
		'weight_kg' => __( 'Weight', 'perfect10' ),
		'dress'     => __( 'Dress size', 'perfect10' ),
		'shoes'     => __( 'Shoe size', 'perfect10' ),
		'hair'      => __( 'Hair', 'perfect10' ),
		'eyes'      => __( 'Eyes', 'perfect10' ),
		'tattoos'   => __( 'Tattoos', 'perfect10' ),
		'piercings' => __( 'Piercings', 'perfect10' ),
	);

	foreach ( $fields as $key => $label ) {
		$value = p10_meta( $post, $key );
		if ( '' === $value ) {
			continue;
		}
		if ( 'height_cm' === $key ) {
			$value = (int) $value . ' cm';
		}
		if ( 'weight_kg' === $key ) {
			$value = (int) $value . ' kg';
		}
		if ( 'age' === $key ) {
			$value = (int) $value;
		}
		$rows[ $label ] = $value;
	}

	$nationality = p10_get_primary_term( $post->ID, 'p10_nationality' );
	if ( $nationality ) {
		$rows[ __( 'Nationality', 'perfect10' ) ] = $nationality->name;
	}

	$languages = p10_profile_tags( $post, 'languages' );
	if ( $languages ) {
		$rows[ __( 'Languages', 'perfect10' ) ] = implode( ', ', $languages );
	}

	return apply_filters( 'p10_profile_spec_list', $rows, $post );
}

/**
 * Availability flags shown as chips on a profile.
 *
 * @param int|WP_Post $post Profile.
 * @return string[]
 */
function p10_profile_flags( $post = null ) {
	$flags = array();
	$map   = array(
		'incall'    => __( 'Incall', 'perfect10' ),
		'outcall'   => __( 'Outcall', 'perfect10' ),
		'overnight' => __( 'Overnight', 'perfect10' ),
		'dinner'    => __( 'Dinner date', 'perfect10' ),
		'travel'    => __( 'Travel', 'perfect10' ),
	);
	foreach ( $map as $key => $label ) {
		if ( p10_meta( $post, $key ) ) {
			$flags[] = $label;
		}
	}
	return $flags;
}

/**
 * The editorial "about" blocks shown on the homepage.
 *
 * Two blocks are configurable in the Customizer; the array is filterable so a
 * child theme can append more without touching a template.
 *
 * @return array
 */
function p10_about_blocks() {
	$blocks = array();

	for ( $i = 1; $i <= 2; $i++ ) {
		$title = (string) p10_option( 'about_' . $i . '_title', '' );
		$copy  = (string) p10_option( 'about_' . $i . '_copy', '' );

		if ( ! $title && ! $copy ) {
			continue;
		}

		$blocks[] = array(
			'number' => (string) p10_option( 'about_' . $i . '_number', '' ),
			'kicker' => (string) p10_option( 'about_' . $i . '_kicker', '' ),
			'title'  => $title,
			'accent' => (string) p10_option( 'about_' . $i . '_accent', '' ),
			'copy'   => $copy,
		);
	}

	return apply_filters( 'p10_about_blocks', $blocks );
}

/**
 * Section eyebrow markup: a number and a kicker.
 *
 * @param string $number Number, e.g. "01".
 * @param string $kicker Kicker text.
 */
function p10_section_eyebrow( $number, $kicker ) {
	if ( ! $number && ! $kicker ) {
		return;
	}
	echo '<p class="p10-eyebrow' . ( $number ? '' : ' p10-eyebrow--bare' ) . '">';
	if ( $number ) {
		echo '<span class="p10-eyebrow__num">' . esc_html( $number ) . '</span>';
	}
	if ( $kicker ) {
		echo '<span class="p10-eyebrow__text">' . esc_html( $kicker ) . '</span>';
	}
	echo '</p>';
}

/**
 * Featured reviews.
 *
 * @param int $count Number of reviews.
 * @return WP_Post[]
 */
function p10_get_featured_reviews( $count = 3 ) {
	$featured = get_posts( array(
		'post_type'      => 'p10_review',
		'posts_per_page' => (int) $count,
		'post_status'    => 'publish',
		'meta_key'       => 'p10_featured', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );

	if ( count( $featured ) >= $count ) {
		return $featured;
	}

	$fill = get_posts( array(
		'post_type'      => 'p10_review',
		'posts_per_page' => (int) $count - count( $featured ),
		'post_status'    => 'publish',
		'post__not_in'   => wp_list_pluck( $featured, 'ID' ),
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );

	return array_merge( $featured, $fill );
}

/**
 * Published packages, ordered.
 *
 * @return WP_Post[]
 */
function p10_get_packages() {
	return get_posts( array(
		'post_type'      => 'p10_package',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );
}

/**
 * Escape and echo a telephone link.
 *
 * @param string $label Link label; defaults to the display number.
 * @param string $class Extra classes.
 */
function p10_phone_link( $label = '', $class = '' ) {
	$label = $label ? $label : p10_phone_display();
	if ( ! $label ) {
		return;
	}
	printf(
		'<a class="p10-phone %s" href="%s"><span class="p10-phone__icon" aria-hidden="true">☏</span>%s</a>',
		esc_attr( $class ),
		esc_url( p10_phone_href() ),
		esc_html( $label )
	);
}

/**
 * Current directory filter state, from the query string.
 *
 * @return array
 */
function p10_filter_state() {
	$state = array(
		'county'    => get_query_var( 'p10_county' ) ? get_query_var( 'p10_county' ) : ( isset( $_GET['county'] ) ? sanitize_title( wp_unslash( $_GET['county'] ) ) : '' ), // phpcs:ignore WordPress.Security.NonceVerification
		'city'      => get_query_var( 'p10_city' ) ? get_query_var( 'p10_city' ) : ( isset( $_GET['city'] ) ? sanitize_title( wp_unslash( $_GET['city'] ) ) : '' ), // phpcs:ignore WordPress.Security.NonceVerification
		'category'  => isset( $_GET['type'] ) ? sanitize_title( wp_unslash( $_GET['type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		'premium'   => ! empty( $_GET['premium'] ), // phpcs:ignore WordPress.Security.NonceVerification
		'verified'  => ! empty( $_GET['verified'] ), // phpcs:ignore WordPress.Security.NonceVerification
		'max_rate'  => isset( $_GET['max_rate'] ) ? absint( $_GET['max_rate'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
		'min_age'   => isset( $_GET['min_age'] ) ? absint( $_GET['min_age'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
		'max_age'   => isset( $_GET['max_age'] ) ? absint( $_GET['max_age'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
		'search'    => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		'sort'      => isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
	);

	return apply_filters( 'p10_filter_state', $state );
}


/**
 * The bundled location dataset from inc/locations.php.
 *
 * Cached in a static so the 211-entry file is only parsed once per request.
 *
 * @return array
 */
function p10_locations_dataset() {
	static $dataset = null;

	if ( null === $dataset ) {
		$file    = P10_DIR . 'inc/locations.php';
		$dataset = is_readable( $file ) ? (array) require $file : array();
	}

	return apply_filters( 'p10_locations_dataset', $dataset );
}

/**
 * Term-like objects built from the dataset, used before the importer has run.
 *
 * They carry the handful of WP_Term properties the templates read, with a
 * term_id of 0 so p10_term_url() knows to degrade gracefully.
 *
 * @param string $county Optional county slug to restrict to.
 * @return array
 */
function p10_fallback_cities( $county = '' ) {
	$dataset = p10_locations_dataset();
	$cities  = array();

	foreach ( $dataset as $county_slug => $group ) {
		if ( $county && $county !== $county_slug ) {
			continue;
		}

		$sort = 0;
		foreach ( array( 'cities', 'districts' ) as $kind ) {
			if ( empty( $group[ $kind ] ) ) {
				continue;
			}
			foreach ( $group[ $kind ] as $name ) {
				$sort++;
				$term          = new stdClass();
				$term->term_id = 0;
				$term->name    = $name;
				$term->slug    = sanitize_title( $name );
				$term->count   = 0;
				$term->parent  = 0;
				$term->taxonomy = 'p10_city';
				$term->p10_county = $county_slug;
				$term->p10_kind   = 'districts' === $kind ? 'district' : 'city';
				$term->p10_sort   = $sort;
				$cities[]      = $term;
			}
		}
	}

	usort( $cities, static function ( $a, $b ) {
		return strcasecmp( $a->name, $b->name );
	} );

	return $cities;
}

/**
 * County term-like objects from the dataset.
 *
 * @return array
 */
function p10_fallback_counties() {
	$dataset  = p10_locations_dataset();
	$counties = array();
	$order    = 0;

	foreach ( $dataset as $county_slug => $group ) {
		$order++;
		$term           = new stdClass();
		$term->term_id  = 0;
		$term->name     = isset( $group['label'] ) ? $group['label'] : ucwords( str_replace( '-', ' ', $county_slug ) );
		$term->slug     = $county_slug;
		$term->count    = 0;
		$term->taxonomy = 'p10_county';
		$term->p10_sort = $order;
		$counties[]     = $term;
	}

	return $counties;
}

/**
 * Safe permalink for a term that may only exist in the bundled dataset.
 *
 * Returns an empty string when the term is not a real WP_Term yet, so callers
 * can render a non-link label instead of hitting get_term_link() with an error.
 *
 * @param WP_Term|stdClass|null $term Term.
 * @return string
 */
function p10_term_url( $term ) {
	if ( ! $term || empty( $term->term_id ) ) {
		return '';
	}

	$link = get_term_link( $term );

	return is_wp_error( $link ) ? '' : (string) $link;
}

/**
 * County slug for either a real term or a dataset object.
 *
 * @param WP_Term|stdClass $city City term.
 * @return string
 */
function p10_city_county_of( $city ) {
	if ( ! $city ) {
		return '';
	}
	if ( ! empty( $city->p10_county ) ) {
		return (string) $city->p10_county;
	}
	return p10_city_county_slug( $city );
}

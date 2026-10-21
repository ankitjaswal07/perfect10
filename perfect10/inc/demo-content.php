<?php
/**
 * One-click demo importer.
 *
 * Creates the location tree from inc/locations.php (2 counties, 88 LA cities,
 * 89 LA districts, 34 Orange County cities), the profile types, nationalities,
 * utility pages, sample profiles, reviews and packages. Idempotent: anything
 * that already exists is left alone.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run the importer.
 *
 * @param string $scope 'all' or 'locations'.
 * @return array Log lines.
 */
function p10_run_demo_import( $scope = 'all' ) {
	$log = array();

	if ( ! current_user_can( 'manage_options' ) ) {
		return array( __( 'Permission denied.', 'perfect10' ) );
	}

	$log[] = __( 'Starting Perfect 10 import…', 'perfect10' );

	$counties = p10_import_counties( $log );
	p10_import_cities( $counties, $log );
	$types    = p10_import_categories( $log );
	p10_import_nationalities( $log );

	if ( 'locations' === $scope ) {
		p10_add_nested_rules();
		flush_rewrite_rules();
		$log[] = __( 'Locations imported and permalinks flushed.', 'perfect10' );
		return $log;
	}

	$pages = p10_import_pages( $log );

	p10_import_packages( $log );
	p10_import_reviews( $log );
	p10_import_profiles( $counties, $types, $log );
	p10_import_home_settings( $pages, $log );

	// Sideloads stock imagery for the hero, cities and profile types. This is
	// the slow part of the import; it is skipped entirely when remote fetching
	// is filtered off and degrades to the bundled placeholders.
	if ( apply_filters( 'p10_demo_fetch_remote', false ) ) {
		p10_raise_time_limit();
		p10_import_imagery();
		$log[] = __( 'Stock imagery sideloaded (skipped where already set).', 'perfect10' );
	} else {
		$log[] = __( 'Remote imagery skipped — using bundled placeholders.', 'perfect10' );
	}

	p10_import_menus( $pages );
	$log[] = __( 'Primary menu created and assigned.', 'perfect10' );

	p10_add_nested_rules();
	flush_rewrite_rules();

	$log[] = __( 'Permalinks flushed.', 'perfect10' );
	$log[] = __( 'Import complete.', 'perfect10' );

	/**
	 * Fires at the end of a full demo import.
	 *
	 * @param array $log Log lines.
	 */
	do_action( 'p10_demo_imported', $log );

	return $log;
}

/**
 * Ask PHP for more time when sideloading imagery.
 */
function p10_raise_time_limit() {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit( 'admin' );
	}
}

/**
 * Create the two counties.
 *
 * @param array $log Log lines, by reference.
 * @return array Slug => term_id.
 */
function p10_import_counties( &$log ) {
	$definitions = array(
		'los-angeles-county' => array(
			'name'    => __( 'Los Angeles County', 'perfect10' ),
			'tagline' => __( '88 cities, from Malibu to Long Beach', 'perfect10' ),
			'blurb'   => __( 'Verified independent companions across Los Angeles County — Beverly Hills, Hollywood, West Hollywood, Santa Monica, Downtown and every neighbourhood in between.', 'perfect10' ),
			'sort'    => 1,
		),
		'orange-county'      => array(
			'name'    => __( 'Orange County', 'perfect10' ),
			'tagline' => __( '34 cities, from Seal Beach to San Clemente', 'perfect10' ),
			'blurb'   => __( 'Companions across Orange County — Newport Beach, Irvine, Anaheim, Huntington Beach, Laguna Beach and the surrounding coastal cities.', 'perfect10' ),
			'sort'    => 2,
		),
	);

	$map = array();

	foreach ( $definitions as $slug => $definition ) {
		$existing = get_term_by( 'slug', $slug, 'p10_county' );

		if ( $existing ) {
			$map[ $slug ] = (int) $existing->term_id;
			continue;
		}

		$result = wp_insert_term( $definition['name'], 'p10_county', array( 'slug' => $slug ) );

		if ( is_wp_error( $result ) ) {
			$log[] = sprintf( __( 'County failed: %s', 'perfect10' ), $result->get_error_message() );
			continue;
		}

		$map[ $slug ] = (int) $result['term_id'];
		p10_demo_mark_term( (int) $result['term_id'] );
		update_term_meta( $result['term_id'], 'p10_tagline', $definition['tagline'] );
		update_term_meta( $result['term_id'], 'p10_blurb', $definition['blurb'] );
		update_term_meta( $result['term_id'], 'p10_sort', $definition['sort'] );
	}

	$log[] = sprintf( __( 'Counties ready: %d', 'perfect10' ), count( $map ) );

	return $map;
}

/**
 * Create every city and district from inc/locations.php.
 *
 * @param array $counties Slug => term_id map.
 * @param array $log      Log lines, by reference.
 * @return string Log line.
 */
function p10_import_cities( $counties, &$log ) {
	$locations = require P10_DIR . 'inc/locations.php';

	$created  = 0;
	$skipped  = 0;

	$featured = array(
		'los-angeles'          => array( 'sort' => 1 ),
		'beverly-hills'        => array( 'sort' => 2 ),
		'hollywood'            => array( 'sort' => 3 ),
		'west-hollywood'       => array( 'sort' => 4 ),
		'santa-monica'         => array( 'sort' => 5 ),
		'downtown-los-angeles' => array( 'sort' => 6 ),
		'newport-beach'        => array( 'sort' => 7 ),
		'irvine'               => array( 'sort' => 8 ),
		'anaheim'              => array( 'sort' => 9 ),
	);

	foreach ( $locations as $county_slug => $group ) {
		if ( empty( $counties[ $county_slug ] ) ) {
			continue;
		}

		$parent_id = 0;

		foreach ( array( 'cities', 'districts' ) as $kind ) {
			if ( empty( $group[ $kind ] ) ) {
				continue;
			}

			$sort = 0;
			foreach ( $group[ $kind ] as $name ) {
				$sort++;
				$slug = sanitize_title( $name );

				if ( get_term_by( 'slug', $slug, 'p10_city' ) ) {
					$skipped++;
					continue;
				}

				$result = wp_insert_term( $name, 'p10_city', array(
					'slug'   => $slug,
					'parent' => $parent_id,
				) );

				if ( is_wp_error( $result ) ) {
					continue;
				}

				$term_id = (int) $result['term_id'];
				p10_demo_mark_term( $term_id );
				update_term_meta( $term_id, 'p10_county', $county_slug );
				update_term_meta( $term_id, 'p10_kind', 'districts' === $kind ? 'district' : 'city' );
				update_term_meta( $term_id, 'p10_sort', $sort );

				if ( isset( $featured[ $slug ] ) ) {
					update_term_meta( $term_id, 'p10_featured', 1 );
					update_term_meta( $term_id, 'p10_sort', $featured[ $slug ]['sort'] );
				}

				$created++;
			}
		}
	}

	$line = sprintf(
		/* translators: 1: cities created, 2: cities already present. */
		__( 'Cities & districts: %1$d created, %2$d already present.', 'perfect10' ),
		$created,
		$skipped
	);
	$log[] = $line;

	return $line;
}

/**
 * Create the profile types.
 *
 * @param array $log Log lines, by reference.
 * @return array Slug => term_id.
 */
function p10_import_categories( &$log ) {
	$definitions = array(
		'independent-escorts'    => array(
			'name'   => __( 'Independent Escorts', 'perfect10' ),
			'short'  => __( 'Independent', 'perfect10' ),
			'number' => '01',
			'sort'   => 1,
			'blurb'  => __( 'Self-represented companions who manage their own availability, boundaries and bookings.', 'perfect10' ),
		),
		'trans-escorts'          => array(
			'name'   => __( 'Trans Escorts', 'perfect10' ),
			'short'  => __( 'Trans', 'perfect10' ),
			'number' => '02',
			'sort'   => 2,
			'blurb'  => __( 'Verified trans companions across Los Angeles and Orange County.', 'perfect10' ),
		),
		'couples'                => array(
			'name'   => __( 'Couples', 'perfect10' ),
			'short'  => __( 'Couples', 'perfect10' ),
			'number' => '03',
			'sort'   => 3,
			'blurb'  => __( 'Duos listing together, with clearly stated preferences and boundaries.', 'perfect10' ),
		),
		'escort-agencies'        => array(
			'name'   => __( 'Escort Agencies', 'perfect10' ),
			'short'  => __( 'Agencies', 'perfect10' ),
			'number' => '04',
			'sort'   => 4,
			'blurb'  => __( 'Established agencies with rosters of vetted, independent companions.', 'perfect10' ),
		),
		'erotic-massage'         => array(
			'name'   => __( 'Erotic Massage', 'perfect10' ),
			'short'  => __( 'Massage', 'perfect10' ),
			'number' => '05',
			'sort'   => 5,
			'blurb'  => __( 'Massage studios and independent practitioners offering adult bodywork.', 'perfect10' ),
		),
		'escort-tours'           => array(
			'name'   => __( 'Escort Tours', 'perfect10' ),
			'short'  => __( 'Tours', 'perfect10' ),
			'number' => '06',
			'sort'   => 6,
			'blurb'  => __( 'Companions travelling through Southern California on a published schedule.', 'perfect10' ),
		),
	);

	$map = array();

	foreach ( $definitions as $slug => $definition ) {
		$existing = get_term_by( 'slug', $slug, 'p10_category' );

		if ( $existing ) {
			$map[ $slug ] = (int) $existing->term_id;
			continue;
		}

		$result = wp_insert_term( $definition['name'], 'p10_category', array( 'slug' => $slug ) );
		if ( is_wp_error( $result ) ) {
			continue;
		}

		$term_id = (int) $result['term_id'];
		p10_demo_mark_term( $term_id );
		update_term_meta( $term_id, 'p10_number', $definition['number'] );
		update_term_meta( $term_id, 'p10_short', $definition['short'] );
		update_term_meta( $term_id, 'p10_blurb', $definition['blurb'] );
		update_term_meta( $term_id, 'p10_sort', $definition['sort'] );

		$map[ $slug ] = $term_id;
	}

	$log[] = sprintf( __( 'Profile types ready: %d', 'perfect10' ), count( $map ) );

	return $map;
}

/**
 * Create nationalities used by the sample profiles.
 *
 * @param array $log Log lines, by reference.
 */
function p10_import_nationalities( &$log ) {
	$names = array(
		'American'  => 1,
		'British'   => 2,
		'European'  => 3,
		'Latina'    => 4,
		'Asian'     => 5,
		'Australian' => 6,
		'Canadian'  => 7,
		'French'    => 8,
		'Italian'   => 9,
		'Spanish'   => 10,
	);

	$created = 0;

	foreach ( $names as $name => $sort ) {
		$slug = sanitize_title( $name );
		if ( get_term_by( 'slug', $slug, 'p10_nationality' ) ) {
			continue;
		}
		$result = wp_insert_term( $name, 'p10_nationality', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $result ) ) {
			p10_demo_mark_term( (int) $result['term_id'] );
			update_term_meta( $result['term_id'], 'p10_sort', $sort );
			$created++;
		}
	}

	$log[] = sprintf( __( 'Nationalities: %d created.', 'perfect10' ), $created );
}

/**
 * Create the utility pages the theme links to.
 *
 * @param array $log Log lines, by reference.
 * @return array Slug => page ID.
 */
function p10_import_pages( &$log ) {
	$pages = array(
		'home' => array(
			'title'    => __( 'Home', 'perfect10' ),
			'slug'     => 'home',
			'template' => '',
			'content'  => '',
		),
		'all-independent-escorts' => array(
			'title'    => __( 'All Independent Escorts', 'perfect10' ),
			'slug'     => 'all-independent-escorts',
			'template' => 'page-templates/directory.php',
			'excerpt'  => __( 'Every verified profile across Los Angeles and Orange County. Filter by county, city, profile type, rate and availability.', 'perfect10' ),
			'content'  => '',
		),
		'booking' => array(
			'title'    => __( 'Booking', 'perfect10' ),
			'slug'     => 'booking',
			'template' => 'page-templates/booking.php',
			'excerpt'  => __( 'Request a private referral. A booking agent will call you to confirm availability.', 'perfect10' ),
			'content'  => '',
		),
		'register-independent-profile' => array(
			'title'    => __( 'Create Independent Listing', 'perfect10' ),
			'slug'     => 'register-independent-profile',
			'template' => 'page-templates/register.php',
			'excerpt'  => __( 'Apply for a professional listing with city placement across Los Angeles and Orange County.', 'perfect10' ),
			'content'  => '',
		),
		'premium-packages' => array(
			'title'    => __( 'Premium Packages', 'perfect10' ),
			'slug'     => 'premium-packages',
			'template' => 'page-templates/packages.php',
			'excerpt'  => __( 'Top-featured placement for the most sought-after profiles.', 'perfect10' ),
			'content'  => '',
		),
		'all-cities' => array(
			'title'    => __( 'All Cities', 'perfect10' ),
			'slug'     => 'all-cities',
			'template' => 'page-templates/cities.php',
			'excerpt'  => __( 'Every city and district we cover, grouped by county.', 'perfect10' ),
			'content'  => '',
		),
	);

	$map = array();

	foreach ( $pages as $key => $definition ) {
		$existing = get_page_by_path( $definition['slug'] );

		if ( $existing ) {
			$map[ $key ] = (int) $existing->ID;
			continue;
		}

		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $definition['title'],
			'post_name'    => $definition['slug'],
			'post_content' => isset( $definition['content'] ) ? $definition['content'] : '',
			'post_excerpt' => isset( $definition['excerpt'] ) ? $definition['excerpt'] : '',
		), true );

		if ( is_wp_error( $page_id ) ) {
			$log[] = sprintf( __( 'Page failed: %s', 'perfect10' ), $page_id->get_error_message() );
			continue;
		}

		if ( ! empty( $definition['template'] ) ) {
			update_post_meta( $page_id, '_wp_page_template', $definition['template'] );
		}

		p10_demo_mark_post( (int) $page_id, 'page' );
		$map[ $key ] = (int) $page_id;
	}

	// Point the directory helper at the real page.
	if ( ! empty( $map['all-independent-escorts'] ) ) {
		update_option( 'p10_directory_page', $map['all-independent-escorts'] );
		set_theme_mod( 'p10_directory_page', $map['all-independent-escorts'] );
	}

	$log[] = sprintf( __( 'Utility pages ready: %d', 'perfect10' ), count( $map ) );

	return $map;
}

/**
 * Apply the recommended reading settings.
 *
 * @param array $pages Slug => page ID.
 * @param array $log   Log lines, by reference.
 */
function p10_import_home_settings( $pages, &$log ) {
	if ( empty( $pages['home'] ) ) {
		return;
	}

	if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) ) {
		$log[] = __( 'Front page already configured — left untouched.', 'perfect10' );
		return;
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $pages['home'] );

	$log[] = __( 'Static front page set to “Home”.', 'perfect10' );
}

/**
 * Create the premium packages.
 *
 * @param array $log Log lines, by reference.
 */
function p10_import_packages( &$log ) {
	$packages = array(
		array(
			'title'    => __( 'Essential Listing', 'perfect10' ),
			'slug'     => 'essential-listing',
			'price'    => 99,
			'period'   => __( 'per month', 'perfect10' ),
			'badge'    => '',
			'featured' => 0,
			'order'    => 1,
			'features' => implode( "\n", array(
				__( 'Standard directory placement', 'perfect10' ),
				__( 'Up to 6 photos', 'perfect10' ),
				__( 'One city placement', 'perfect10' ),
				__( 'Verified badge after ID check', 'perfect10' ),
				__( 'Enquiry forwarding', 'perfect10' ),
			) ),
		),
		array(
			'title'    => __( 'Premium Placement', 'perfect10' ),
			'slug'     => 'premium-placement',
			'price'    => 249,
			'period'   => __( 'per month', 'perfect10' ),
			'badge'    => __( 'Most Popular', 'perfect10' ),
			'featured' => 1,
			'order'    => 2,
			'features' => implode( "\n", array(
				__( 'Everything in Essential', 'perfect10' ),
				__( '“◆ Premium” badge across the site', 'perfect10' ),
				__( 'The Premium Collection section on the homepage', 'perfect10' ),
				__( 'Up to 15 photos plus video', 'perfect10' ),
				__( 'Three city placements', 'perfect10' ),
				__( 'Priority in city and type archives', 'perfect10' ),
			) ),
		),
		array(
			'title'    => __( 'Featured Muse', 'perfect10' ),
			'slug'     => 'featured-muse',
			'price'    => 499,
			'period'   => __( 'per month', 'perfect10' ),
			'badge'    => __( 'Limited slots', 'perfect10' ),
			'featured' => 0,
			'order'    => 3,
			'features' => implode( "\n", array(
				__( 'Everything in Premium', 'perfect10' ),
				__( '“This Month’s Muses” homepage placement', 'perfect10' ),
				__( 'Numbered editorial card (№ 01–06)', 'perfect10' ),
				__( 'Unlimited photos plus gallery', 'perfect10' ),
				__( 'All city placements in both counties', 'perfect10' ),
				__( 'Dedicated booking agent line', 'perfect10' ),
			) ),
		),
	);

	$created = 0;

	foreach ( $packages as $package ) {
		if ( get_page_by_path( $package['slug'], OBJECT, 'p10_package' ) ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'p10_package',
			'post_status'  => 'publish',
			'post_title'   => $package['title'],
			'post_name'    => $package['slug'],
			'menu_order'   => $package['order'],
			'post_excerpt' => $package['features'],
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		p10_demo_mark_post( (int) $post_id, 'package' );

		update_post_meta( $post_id, 'p10_price', (float) $package['price'] );
		update_post_meta( $post_id, 'p10_period', $package['period'] );
		update_post_meta( $post_id, 'p10_badge', $package['badge'] );
		update_post_meta( $post_id, 'p10_features', $package['features'] );
		update_post_meta( $post_id, 'p10_featured', $package['featured'] );

		$created++;
	}

	$log[] = sprintf( __( 'Packages: %d created.', 'perfect10' ), $created );
}

/**
 * Create the sample guest reviews.
 *
 * @param array $log Log lines, by reference.
 */
function p10_import_reviews( &$log ) {
	$reviews = array(
		array(
			'slug'     => 'effortless-five-star-service',
			'title'    => __( 'Effortless, five-star service', 'perfect10' ),
			'body'     => __( 'The booking agent was professional, discreet, and made the entire arrangement effortless. Every detail was handled with the kind of care I expect from a five-star service.', 'perfect10' ),
			'name'     => 'Jonathan R.',
			'location' => 'Beverly Hills',
			'order'    => 1,
		),
		array(
			'slug'     => 'the-gold-standard',
			'title'    => __( 'The gold standard', 'perfect10' ),
			'body'     => __( 'I have used referral services in three countries. Nothing compares to the calibre of profiles here, nor the professionalism of the agents. This is the gold standard.', 'perfect10' ),
			'name'     => 'William S.',
			'location' => 'Newport Beach',
			'order'    => 2,
		),
		array(
			'slug'     => 'confirmed-in-under-an-hour',
			'title'    => __( 'Confirmed in under an hour', 'perfect10' ),
			'body'     => __( 'Called from LAX after landing — an evening arrangement was confirmed in under an hour. Punctual, elegant, and exactly as described in the profile.', 'perfect10' ),
			'name'     => 'M. Delgado',
			'location' => 'Century City',
			'order'    => 3,
		),
	);

	$created = 0;

	foreach ( $reviews as $review ) {
		if ( get_page_by_path( $review['slug'], OBJECT, 'p10_review' ) ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'p10_review',
			'post_status'  => 'publish',
			'post_title'   => $review['title'],
			'post_name'    => $review['slug'],
			'post_content' => $review['body'],
			'post_excerpt' => $review['body'],
			'menu_order'   => $review['order'],
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		p10_demo_mark_post( (int) $post_id, 'review' );

		update_post_meta( $post_id, 'p10_guest_name', $review['name'] );
		update_post_meta( $post_id, 'p10_guest_location', $review['location'] );
		update_post_meta( $post_id, 'p10_guest_label', __( 'Sample review — replace with a verified review', 'perfect10' ) );
		update_post_meta( $post_id, 'p10_sample', 1 );
		update_post_meta( $post_id, 'p10_verified_review', 0 );
		update_post_meta( $post_id, 'p10_published_ok', 0 );
		update_post_meta( $post_id, 'p10_rating', 5 );
		update_post_meta( $post_id, 'p10_featured', 1 );
		update_post_meta( $post_id, 'p10_published_ok', 1 );

		$created++;
	}

	$log[] = sprintf( __( 'Reviews: %d created.', 'perfect10' ), $created );
}

/**
 * Create the sample profiles.
 *
 * @param array $counties Slug => term_id.
 * @param array $types    Slug => term_id.
 * @param array $log      Log lines, by reference.
 */
function p10_import_profiles( $counties, $types, &$log ) {
	$profiles = array(
		array(
			'slug'        => 'nicky',
			'name'        => 'Nicky',
			'city'        => 'los-angeles',
			'nationality' => 'british',
			'age'         => 21,
			'height'      => 152,
			'rate'        => 800,
			'premium'     => 1,
			'featured'    => 1,
			'image'       => 'https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'Petite, poised and effortlessly warm, Nicky is equally at home at a gallery opening in West Hollywood or a quiet dinner in Beverly Hills. She is well travelled, an easy conversationalist and meticulous about presentation.', 'perfect10' ),
			'services'    => __( 'Dinner companion, GFE, Travel, Evening events, Overnight', 'perfect10' ),
			'languages'   => __( 'English, French', 'perfect10' ),
			'hair'        => __( 'Brunette', 'perfect10' ),
			'eyes'        => __( 'Hazel', 'perfect10' ),
			'body_type'   => __( 'Petite', 'perfect10' ),
			'quote'       => __( 'Discretion is not something I promise — it is simply how I work.', 'perfect10' ),
		),
		array(
			'slug'        => 'jenna',
			'name'        => 'Jenna',
			'city'        => 'los-angeles',
			'nationality' => 'american',
			'age'         => 24,
			'height'      => 168,
			'rate'        => 700,
			'premium'     => 1,
			'featured'    => 2,
			'image'       => 'https://images.pexels.com/photos/1065084/pexels-photo-1065084.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'Tall, elegant and calm, Jenna has a modelling background and an easy wit. She is a natural at industry events and equally relaxed over a long lunch in Century City.', 'perfect10' ),
			'services'    => __( 'Dinner companion, Events, Travel, GFE', 'perfect10' ),
			'languages'   => __( 'English, Spanish', 'perfect10' ),
			'hair'        => __( 'Blonde', 'perfect10' ),
			'eyes'        => __( 'Blue', 'perfect10' ),
			'body_type'   => __( 'Slim', 'perfect10' ),
			'quote'       => __( 'Good company should feel effortless. That is the standard I hold myself to.', 'perfect10' ),
		),
		array(
			'slug'        => 'alex',
			'name'        => 'Alex',
			'city'        => 'los-angeles',
			'nationality' => 'american',
			'age'         => 26,
			'height'      => 170,
			'rate'        => 600,
			'premium'     => 1,
			'featured'    => 3,
			'image'       => 'https://images.pexels.com/photos/733872/pexels-photo-733872.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'Athletic and grounded, Alex balances a fitness career with a genuine love of the city — from hike-at-dawn in Runyon to late dinners in Silver Lake.', 'perfect10' ),
			'services'    => __( 'Dinner companion, Travel, Fitness, Events', 'perfect10' ),
			'languages'   => __( 'English', 'perfect10' ),
			'hair'        => __( 'Brown', 'perfect10' ),
			'eyes'        => __( 'Brown', 'perfect10' ),
			'body_type'   => __( 'Athletic', 'perfect10' ),
		),
		array(
			'slug'        => 'alexia-rae',
			'name'        => 'Alexia Rae',
			'city'        => 'los-angeles',
			'nationality' => 'american',
			'age'         => 25,
			'height'      => 165,
			'rate'        => 700,
			'premium'     => 1,
			'featured'    => 4,
			'image'       => 'https://images.pexels.com/photos/1130626/pexels-photo-1130626.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'A former dancer with an obvious grace and an understated warmth, Alexia Rae is a favourite for extended weekend arrangements and travel.', 'perfect10' ),
			'services'    => __( 'Dinner companion, Travel, Overnight, Weekend', 'perfect10' ),
			'languages'   => __( 'English, Italian', 'perfect10' ),
			'hair'        => __( 'Black', 'perfect10' ),
			'eyes'        => __( 'Brown', 'perfect10' ),
			'body_type'   => __( 'Slim', 'perfect10' ),
		),
		array(
			'slug'        => 'wendy',
			'name'        => 'Wendy',
			'city'        => 'los-angeles',
			'nationality' => 'american',
			'age'         => 27,
			'height'      => 170,
			'rate'        => 600,
			'premium'     => 0,
			'featured'    => 5,
			'image'       => 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'Warm, articulate and unhurried, Wendy is well suited to longer dinners and conversation-first arrangements. Based between Santa Monica and West Hollywood.', 'perfect10' ),
			'services'    => __( 'Dinner companion, GFE, Events', 'perfect10' ),
			'languages'   => __( 'English', 'perfect10' ),
			'hair'        => __( 'Auburn', 'perfect10' ),
			'eyes'        => __( 'Green', 'perfect10' ),
			'body_type'   => __( 'Curvy', 'perfect10' ),
		),
		array(
			'slug'        => 'anna',
			'name'        => 'Anna',
			'city'        => 'los-angeles',
			'nationality' => 'european',
			'age'         => 26,
			'height'      => 172,
			'rate'        => 700,
			'premium'     => 1,
			'featured'    => 6,
			'image'       => 'https://images.pexels.com/photos/1587009/pexels-photo-1587009.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'Tall, poised and continental, Anna splits her time between Los Angeles and Europe and is frequently requested for travel companionship.', 'perfect10' ),
			'services'    => __( 'Dinner companion, Travel, Events, Overnight', 'perfect10' ),
			'languages'   => __( 'English, German, French', 'perfect10' ),
			'hair'        => __( 'Blonde', 'perfect10' ),
			'eyes'        => __( 'Blue', 'perfect10' ),
			'body_type'   => __( 'Slim', 'perfect10' ),
		),
		array(
			'slug'        => 'cristy',
			'name'        => 'Cristy',
			'city'        => 'los-angeles',
			'nationality' => 'european',
			'age'         => 26,
			'height'      => 170,
			'rate'        => 700,
			'premium'     => 1,
			'featured'    => 7,
			'image'       => 'https://images.pexels.com/photos/1036623/pexels-photo-1036623.jpeg?auto=compress&cs=tinysrgb&w=1200&h=1600&fit=crop',
			'body'        => __( 'Dark-haired and quietly confident, Cristy is a regular in the Premium Collection and books well in advance for weekends and travel.', 'perfect10' ),
			'services'    => __( 'Dinner companion, Travel, GFE, Weekend', 'perfect10' ),
			'languages'   => __( 'English, Spanish, Romanian', 'perfect10' ),
			'hair'        => __( 'Black', 'perfect10' ),
			'eyes'        => __( 'Brown', 'perfect10' ),
			'body_type'   => __( 'Slim', 'perfect10' ),
		),
	);

	$created = 0;

	foreach ( $profiles as $definition ) {
		if ( get_page_by_path( $definition['slug'], OBJECT, 'p10_profile' ) ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'p10_profile',
			'post_status'  => 'publish',
			'post_title'   => $definition['name'],
			'post_name'    => $definition['slug'],
			'post_content' => $definition['body'],
			'post_excerpt' => wp_trim_words( wp_strip_all_tags( $definition['body'] ), 26 ),
			'menu_order'   => (int) $definition['featured'],
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		p10_demo_mark_post( (int) $post_id, 'profile' );

		update_post_meta( $post_id, 'p10_age', (int) $definition['age'] );
		update_post_meta( $post_id, 'p10_height_cm', (int) $definition['height'] );
		update_post_meta( $post_id, 'p10_rate', (float) $definition['rate'] );
		update_post_meta( $post_id, 'p10_currency', 'USD' );
		update_post_meta( $post_id, 'p10_verified', 1 );
		update_post_meta( $post_id, 'p10_available', 'available' );
		update_post_meta( $post_id, 'p10_featured_order', (int) $definition['featured'] );
		update_post_meta( $post_id, 'p10_dinner', 1 );
		update_post_meta( $post_id, 'p10_travel', 1 );
		update_post_meta( $post_id, 'p10_services', $definition['services'] );
		update_post_meta( $post_id, 'p10_languages', $definition['languages'] );
		update_post_meta( $post_id, 'p10_hair', $definition['hair'] );
		update_post_meta( $post_id, 'p10_eyes', $definition['eyes'] );
		update_post_meta( $post_id, 'p10_body_type', $definition['body_type'] );
		update_post_meta( $post_id, 'p10_bio_heading', __( 'About', 'perfect10' ) );

		if ( ! empty( $definition['quote'] ) ) {
			update_post_meta( $post_id, 'p10_quote', $definition['quote'] );
		}
		if ( $definition['premium'] ) {
			update_post_meta( $post_id, 'p10_premium', 1 );
		}

		/* Terms. */
		$city_term = get_term_by( 'slug', $definition['city'], 'p10_city' );
		$county    = $city_term ? p10_city_county_slug( $city_term ) : 'los-angeles-county';

		if ( $city_term ) {
			wp_set_object_terms( $post_id, array( (int) $city_term->term_id ), 'p10_city' );
		}
		if ( ! empty( $counties[ $county ] ) ) {
			wp_set_object_terms( $post_id, array( (int) $counties[ $county ] ), 'p10_county' );
		}
		if ( ! empty( $types['independent-escorts'] ) ) {
			wp_set_object_terms( $post_id, array( (int) $types['independent-escorts'] ), 'p10_category' );
		}

		$nationality = get_term_by( 'slug', $definition['nationality'], 'p10_nationality' );
		if ( $nationality ) {
			wp_set_object_terms( $post_id, array( (int) $nationality->term_id ), 'p10_nationality' );
		}

		/* Imagery — sideloaded so nothing hotlinks to a third party. */
		$attachment_id = p10_import_fetch_image( $definition['image'], $definition['name'] );
		if ( $attachment_id ) {
			p10_demo_mark_post( (int) $attachment_id, 'attachment' );
			set_post_thumbnail( $post_id, $attachment_id );
			update_post_meta( $post_id, 'p10_gallery', (string) $attachment_id );
		}

		$created++;
	}

	$log[] = sprintf( __( 'Sample profiles: %d created.', 'perfect10' ), $created );
}

/**
 * Sideload a remote image into the media library.
 *
 * Returns 0 when remote fetching is disabled, unavailable or fails — the
 * templates then fall back to the bundled placeholder artwork.
 *
 * @param string $url  Remote image URL.
 * @param string $name Post title used for the attachment.
 * @return int Attachment ID or 0.
 */
function p10_import_fetch_image( $url, $name = '' ) {
	if ( ! apply_filters( 'p10_demo_fetch_remote', false ) ) {
		return 0;
	}

	// Cache by URL so re-running the importer does not re-download.
	$cache_key = 'p10_img_' . md5( $url );
	$cached    = (int) get_transient( $cache_key );
	if ( $cached && get_post( $cached ) ) {
		return $cached;
	}

	if ( ! function_exists( 'media_sideload_image' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$attachment_id = media_sideload_image( $url, 0, $name ? $name : null, 'id' );

	if ( is_wp_error( $attachment_id ) ) {
		return 0;
	}

	set_transient( $cache_key, (int) $attachment_id, MONTH_IN_SECONDS );

	return (int) $attachment_id;
}

/**
 * Import the hero and city imagery, then store the IDs.
 */
function p10_import_imagery() {
	$hero_url = 'https://images.pexels.com/photos/30946880/pexels-photo-30946880.jpeg?auto=compress&cs=tinysrgb&w=2400&h=1400&fit=crop';
	$hero_id  = p10_import_fetch_image( $hero_url, 'Perfect 10 hero' );

	if ( $hero_id && ! get_theme_mod( 'p10_hero_image' ) ) {
		set_theme_mod( 'p10_hero_image', $hero_id );
	}

	$city_images = array(
		'los-angeles'          => 'https://images.pexels.com/photos/35291216/pexels-photo-35291216.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'beverly-hills'        => 'https://images.pexels.com/photos/30151761/pexels-photo-30151761.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'hollywood'            => 'https://images.pexels.com/photos/19012237/pexels-photo-19012237.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'west-hollywood'       => 'https://images.pexels.com/photos/7114197/pexels-photo-7114197.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'santa-monica'         => 'https://images.pexels.com/photos/912118/pexels-photo-912118.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'downtown-los-angeles' => 'https://images.pexels.com/photos/35291221/pexels-photo-35291221.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'newport-beach'        => 'https://images.pexels.com/photos/8143671/pexels-photo-8143671.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'irvine'               => 'https://images.pexels.com/photos/24805054/pexels-photo-24805054.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
		'anaheim'              => 'https://images.pexels.com/photos/24807125/pexels-photo-24807125.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop',
	);

	foreach ( $city_images as $slug => $url ) {
		$term = get_term_by( 'slug', $slug, 'p10_city' );
		if ( ! $term || p10_term_meta( $term, 'image', 0 ) ) {
			continue;
		}
		$attachment_id = p10_import_fetch_image( $url, $term->name );
		if ( $attachment_id ) {
			update_term_meta( $term->term_id, 'p10_image', $attachment_id );
		}
	}

	$type_images = array(
		'independent-escorts' => 'https://images.pexels.com/photos/33782082/pexels-photo-33782082.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
		'trans-escorts'       => 'https://images.pexels.com/photos/23119730/pexels-photo-23119730.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
		'couples'             => 'https://images.pexels.com/photos/21939620/pexels-photo-21939620.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
		'escort-agencies'     => 'https://images.pexels.com/photos/29133710/pexels-photo-29133710.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
		'erotic-massage'      => 'https://images.pexels.com/photos/34326847/pexels-photo-34326847.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
		'escort-tours'        => 'https://images.pexels.com/photos/10443197/pexels-photo-10443197.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
	);

	foreach ( $type_images as $slug => $url ) {
		$term = get_term_by( 'slug', $slug, 'p10_category' );
		if ( ! $term || p10_term_meta( $term, 'image', 0 ) ) {
			continue;
		}
		$attachment_id = p10_import_fetch_image( $url, $term->name );
		if ( $attachment_id ) {
			update_term_meta( $term->term_id, 'p10_image', $attachment_id );
		}
	}

	$ads_url = 'https://images.pexels.com/photos/24807125/pexels-photo-24807125.jpeg?auto=compress&cs=tinysrgb&w=1400&h=800&fit=crop';
	$ads_id  = p10_import_fetch_image( $ads_url, 'Perfect 10 advertisers' );
	if ( $ads_id && ! get_theme_mod( 'p10_ads_image' ) ) {
		set_theme_mod( 'p10_ads_image', $ads_id );
	}
}

/**
 * Create and assign the primary navigation menu.
 *
 * @param array $pages Slug => page ID.
 */
function p10_import_menus( $pages ) {
	$menu_name = __( 'Perfect 10 Primary', 'perfect10' );
	$menu      = wp_get_nav_menu_object( $menu_name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
	} else {
		$menu_id = (int) $menu->term_id;
	}

	$existing = wp_get_nav_menu_items( $menu_id );
	if ( $existing ) {
		return; // Already populated; leave the editor's work alone.
	}

	$entries = array(
		array(
			'title' => __( 'Directory', 'perfect10' ),
			'url'   => p10_directory_url(),
		),
		array(
			'title' => __( 'Los Angeles County', 'perfect10' ),
			'url'   => trailingslashit( home_url( '/los-angeles-county/' ) ),
		),
		array(
			'title' => __( 'Orange County', 'perfect10' ),
			'url'   => trailingslashit( home_url( '/orange-county/' ) ),
		),
		array(
			'title' => __( 'Premium', 'perfect10' ),
			'url'   => p10_directory_url( array( 'premium' => 1 ) ),
		),
		array(
			'title' => __( 'All Cities', 'perfect10' ),
			'url'   => ! empty( $pages['all-cities'] ) ? get_permalink( $pages['all-cities'] ) : '',
		),
		array(
			'title' => __( 'Reviews', 'perfect10' ),
			'url'   => p10_utility_url( 'reviews' ),
		),
		array(
			'title' => __( 'Advertise', 'perfect10' ),
			'url'   => p10_utility_url( 'register' ),
		),
	);

	foreach ( $entries as $entry ) {
		if ( empty( $entry['url'] ) ) {
			continue;
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $entry['title'],
			'menu-item-url'    => $entry['url'],
			'menu-item-status' => 'publish',
			'menu-item-type'   => 'custom',
		) );
	}

	$locations   = get_theme_mod( 'nav_menu_locations' );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	$manifest = p10_demo_manifest();
	if ( ! in_array( (int) $menu_id, (array) $manifest['menus'], true ) ) {
		$manifest['menus'][] = (int) $menu_id;
		update_option( P10_DEMO_MANIFEST_OPTION, $manifest, false );
	}
}

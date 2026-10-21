<?php
/**
 * Shortcodes so any section of the theme can be placed inside page content.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [p10_profiles limit="6" premium="1" verified="1" county="" city="" type="" columns="3"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_profiles( $atts ) {
	$atts = shortcode_atts( array(
		'limit'    => 6,
		'premium'  => '',
		'verified' => '',
		'county'   => '',
		'city'     => '',
		'type'     => '',
		'columns'  => '',
		'numbered' => '0',
	), $atts, 'p10_profiles' );

	$args = array(
		'post_type'      => 'p10_profile',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, min( 48, (int) $atts['limit'] ) ),
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	);

	$meta_query = array();
	if ( $atts['premium'] ) {
		$meta_query[] = array( 'key' => 'p10_premium', 'value' => '1' );
	}
	if ( $atts['verified'] ) {
		$meta_query[] = array( 'key' => 'p10_verified', 'value' => '1' );
	}
	if ( $meta_query ) {
		$meta_query['relation'] = 'AND';
		$args['meta_query']     = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$tax_query = array();
	foreach ( array(
		'county' => 'p10_county',
		'city'   => 'p10_city',
		'type'   => 'p10_category',
	) as $key => $taxonomy ) {
		if ( ! empty( $atts[ $key ] ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $atts[ $key ] ) ),
			);
		}
	}
	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$profiles = get_posts( $args );
	if ( empty( $profiles ) ) {
		return '';
	}

	$columns = $atts['columns'] ? (int) $atts['columns'] : (int) p10_option( 'directory_columns', 3 );

	ob_start();
	?>
	<div class="p10-grid p10-grid--profiles" style="--p10-grid-columns:<?php echo esc_attr( max( 1, $columns ) ); ?>">
		<?php
		foreach ( $profiles as $i => $profile ) {
			get_template_part( 'template-parts/card-profile', null, array(
				'profile' => $profile,
				'index'   => $atts['numbered'] ? $i + 1 : 0,
				'reveal'  => false,
			) );
		}
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'p10_profiles', 'p10_shortcode_profiles' );

/**
 * [p10_cities limit="9" county=""]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_cities( $atts ) {
	$atts = shortcode_atts( array(
		'limit'  => 9,
		'county' => '',
	), $atts, 'p10_cities' );

	$cities = $atts['county']
		? array_slice( p10_get_cities( sanitize_title( $atts['county'] ) ), 0, max( 1, (int) $atts['limit'] ) )
		: p10_get_featured_cities( max( 1, (int) $atts['limit'] ) );

	if ( empty( $cities ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="p10-grid p10-grid--cities">
		<?php
		foreach ( $cities as $i => $term ) {
			get_template_part( 'template-parts/card-city', null, array(
				'term'   => $term,
				'index'  => $i + 1,
				'reveal' => false,
			) );
		}
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'p10_cities', 'p10_shortcode_cities' );

/**
 * [p10_types limit="6"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_types( $atts ) {
	$atts  = shortcode_atts( array( 'limit' => 6 ), $atts, 'p10_types' );
	$types = array_slice( p10_get_categories(), 0, max( 1, (int) $atts['limit'] ) );

	if ( empty( $types ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="p10-grid p10-grid--types">
		<?php
		foreach ( $types as $i => $term ) {
			get_template_part( 'template-parts/card-type', null, array(
				'term'   => $term,
				'index'  => $i + 1,
				'reveal' => false,
			) );
		}
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'p10_types', 'p10_shortcode_types' );

/**
 * [p10_reviews limit="3"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_reviews( $atts ) {
	$atts    = shortcode_atts( array( 'limit' => 3 ), $atts, 'p10_reviews' );
	$reviews = p10_get_featured_reviews( max( 1, (int) $atts['limit'] ) );

	if ( empty( $reviews ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="p10-grid p10-grid--reviews">
		<?php
		foreach ( $reviews as $i => $review ) {
			get_template_part( 'template-parts/review', null, array(
				'review' => $review,
				'index'  => $i + 1,
			) );
		}
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'p10_reviews', 'p10_shortcode_reviews' );

/**
 * [p10_packages]
 *
 * @return string
 */
function p10_shortcode_packages() {
	$packages = p10_get_packages();
	if ( empty( $packages ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="p10-grid p10-grid--packages">
		<?php foreach ( $packages as $package ) : ?>
			<?php get_template_part( 'template-parts/card-package', null, array( 'package' => $package ) ); ?>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'p10_packages', 'p10_shortcode_packages' );

/**
 * [p10_search]
 *
 * @return string
 */
function p10_shortcode_search() {
	ob_start();
	get_template_part( 'template-parts/search-panel' );
	return (string) ob_get_clean();
}
add_shortcode( 'p10_search', 'p10_shortcode_search' );

/**
 * [p10_form type="booking" submit="Send"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_form( $atts ) {
	$atts = shortcode_atts( array(
		'type'   => 'booking',
		'submit' => '',
	), $atts, 'p10_form' );

	if ( ! p10_form_schema( $atts['type'] ) ) {
		return '';
	}

	ob_start();
	p10_form_status( $atts['type'] );
	p10_the_form( $atts['type'], array(
		'submit'   => $atts['submit'],
		'redirect' => is_singular() ? get_permalink() : '',
	) );
	return (string) ob_get_clean();
}
add_shortcode( 'p10_form', 'p10_shortcode_form' );

/**
 * [p10_phone label="Call us"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_phone( $atts ) {
	$atts = shortcode_atts( array(
		'label' => '',
		'class' => 'p10-btn p10-btn--gold',
	), $atts, 'p10_phone' );

	if ( ! p10_phone_display() ) {
		return '';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s">%3$s</a>',
		esc_attr( $atts['class'] ),
		esc_url( p10_phone_href() ),
		esc_html( $atts['label'] ? $atts['label'] : p10_phone_display() )
	);
}
add_shortcode( 'p10_phone', 'p10_shortcode_phone' );

/**
 * [p10_stats]
 *
 * @return string
 */
function p10_shortcode_stats() {
	$stats = p10_get_stats();
	if ( empty( $stats ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="p10-hero__stats" style="border-top:1px solid var(--p10-line);padding-top:1.6rem">
		<?php foreach ( $stats as $stat ) : ?>
			<div class="p10-stat">
				<span class="p10-stat__value"><?php echo esc_html( $stat['value'] ); ?></span>
				<span class="p10-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'p10_stats', 'p10_shortcode_stats' );

/**
 * [p10_section name="callband|about|advertisers|marquee"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_section( $atts ) {
	$atts = shortcode_atts( array( 'name' => 'callband' ), $atts, 'p10_section' );

	$allowed = array( 'callband', 'about', 'advertisers', 'marquee', 'concierge' );
	if ( ! in_array( $atts['name'], $allowed, true ) ) {
		return '';
	}

	ob_start();
	get_template_part( 'template-parts/' . $atts['name'] );
	return (string) ob_get_clean();
}
add_shortcode( 'p10_section', 'p10_shortcode_section' );

/**
 * [p10_button url="/booking" style="gold" label="Reserve"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function p10_shortcode_button( $atts ) {
	$atts = shortcode_atts( array(
		'label' => '',
		'url'   => '',
		'style' => 'gold',
		'size'  => '',
	), $atts, 'p10_button' );

	if ( ! $atts['label'] || ! $atts['url'] ) {
		return '';
	}

	$classes = array( 'p10-btn' );
	$classes[] = 'gold' === $atts['style'] ? 'p10-btn--gold' : 'p10-btn--ghost';
	if ( 'lg' === $atts['size'] ) {
		$classes[] = 'p10-btn--lg';
	}
	if ( 'sm' === $atts['size'] ) {
		$classes[] = 'p10-btn--sm';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s">%3$s</a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $atts['url'] ),
		esc_html( $atts['label'] )
	);
}
add_shortcode( 'p10_button', 'p10_shortcode_button' );

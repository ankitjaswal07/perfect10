<?php
/**
 * Front page.
 *
 * Section order mirrors the reference build:
 *   hero → ticker → search → 01 Featured Edit → 02 Premium Collection →
 *   03 Profile Types → 04 Destinations → call band → about → 05 Reviews →
 *   advertisers.
 *
 * Every section is individually switchable from Appearance → Customize →
 * Perfect 10, and a static front page's own content is rendered between the
 * ticker and the directory sections.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/*
 * Elementor-first homepage.
 *
 * When the static front page has a saved Elementor document, that document is
 * the complete homepage: the demo importer builds it from the same Perfect 10
 * section widgets (hero, ticker, search, numbered sections, dynamic grids).
 * The original PHP section stack below remains the fallback and is also used
 * verbatim on sites that have not converted the homepage, so the design is
 * never rendered twice.
 */
if ( is_page() && p10_is_built_with_elementor( get_queried_object_id() ) ) {
	while ( have_posts() ) :
		the_post();
		echo '<article id="post-' . esc_attr( get_the_ID() ) . '" class="p10-elementor-home">';
		the_content();
		echo '</article>';
	endwhile;
	get_footer();
	return;
}

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/marquee' );

/*
 * A static front page keeps its own content, so authors can insert anything
 * between the ticker and the generated directory sections.
 */
while ( have_posts() ) :
	the_post();
	if ( trim( (string) get_the_content() ) ) :
		?>
		<section class="p10-section p10-section--tight">
			<div class="p10-shell">
				<div class="p10-content">
					<?php
					the_content();
					wp_link_pages( array(
						'before' => '<nav class="p10-pagination">',
						'after'  => '</nav>',
					) );
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_template_part( 'template-parts/search-panel' );

/* 01 — The Featured Edit. */
if ( p10_option( 'muses_enabled', 1 ) ) :
	$muses = p10_get_featured_profiles( (int) p10_option( 'muses_count', 6 ) );
	if ( ! empty( $muses ) ) :
		?>
		<section class="p10-section p10-section--panel p10-section--gold-line" id="p10-featured">
			<div class="p10-shell">

				<?php
				get_template_part( 'template-parts/section-head', null, array(
					'number'     => (string) p10_option( 'muses_number', '01' ),
					'kicker'     => (string) p10_option( 'muses_kicker', '' ),
					'title'      => (string) p10_option( 'muses_title', '' ),
					'accent'     => (string) p10_option( 'muses_accent', '' ),
					'action'     => (string) p10_option( 'muses_action', '' ),
					'action_url' => p10_directory_url(),
				) );
				?>

				<div class="p10-grid p10-grid--muses">
					<?php foreach ( $muses as $i => $profile ) : ?>
						<?php
						get_template_part( 'template-parts/card-profile', null, array(
							'profile' => $profile,
							'index'   => $i + 1,
						) );
						?>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<?php
	endif;
endif;

/* 02 — The Premium Collection. */
if ( p10_option( 'premium_enabled', 1 ) ) :
	$premium = p10_get_premium_profiles( (int) p10_option( 'premium_count', 6 ) );
	if ( ! empty( $premium ) ) :
		?>
		<section class="p10-section" id="p10-premium">
			<div class="p10-shell">

				<?php
				get_template_part( 'template-parts/section-head', null, array(
					'number'     => (string) p10_option( 'premium_number', '02' ),
					'kicker'     => (string) p10_option( 'premium_kicker', '' ),
					'title'      => (string) p10_option( 'premium_title', '' ),
					'accent'     => (string) p10_option( 'premium_accent', '' ),
					'action'     => (string) p10_option( 'premium_action', '' ),
					'action_url' => p10_directory_url( array( 'premium' => 1 ) ),
				) );
				?>

				<div class="p10-grid p10-grid--profiles">
					<?php foreach ( $premium as $i => $profile ) : ?>
						<?php
						get_template_part( 'template-parts/card-profile', null, array(
							'profile' => $profile,
							'index'   => 0,
							'variant' => 'premium',
						) );
						?>
					<?php endforeach; ?>
				</div>

				<?php if ( p10_option( 'premium_quote' ) ) : ?>
					<div class="p10-premium-note p10-reveal">
						<blockquote class="p10-premium-note__quote">
							&ldquo;<?php echo esc_html( p10_option( 'premium_quote' ) ); ?>&rdquo;
						</blockquote>
						<a class="p10-btn p10-btn--ghost" href="<?php echo esc_url( p10_utility_url( 'packages' ) ); ?>">
							<?php echo esc_html( p10_option( 'premium_cta', __( 'View Premium Packages', 'perfect10' ) ) ); ?>
						</a>
					</div>
				<?php endif; ?>

			</div>
		</section>
		<?php
	endif;
endif;

/* 03 — Browse by Profile Type. */
if ( p10_option( 'types_enabled', 1 ) ) :
	$types = array_slice( p10_get_categories(), 0, (int) p10_option( 'types_count', 6 ) );
	if ( ! empty( $types ) ) :
		?>
		<section class="p10-section p10-section--panel" id="p10-types">
			<div class="p10-shell">

				<?php
				get_template_part( 'template-parts/section-head', null, array(
					'number'     => (string) p10_option( 'types_number', '03' ),
					'kicker'     => (string) p10_option( 'types_kicker', '' ),
					'title'      => (string) p10_option( 'types_title', '' ),
					'accent'     => (string) p10_option( 'types_accent', '' ),
					'action'     => (string) p10_option( 'types_action', '' ),
					'action_url' => p10_directory_url(),
				) );
				?>

				<div class="p10-grid p10-grid--types">
					<?php foreach ( $types as $i => $term ) : ?>
						<?php
						get_template_part( 'template-parts/card-type', null, array(
							'term'  => $term,
							'index' => $i + 1,
						) );
						?>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<?php
	endif;
endif;

/* 04 — Destinations. */
if ( p10_option( 'cities_enabled', 1 ) ) :
	$featured_cities = p10_get_featured_cities( (int) p10_option( 'cities_count', 9 ) );
	if ( ! empty( $featured_cities ) ) :
		?>
		<section class="p10-section" id="p10-destinations">
			<div class="p10-shell">

				<?php
				$first_county = p10_get_counties();
				$cities_url   = $first_county ? p10_term_url( $first_county[0] ) : '';
				$cities_url   = $cities_url ? $cities_url : p10_utility_url( 'cities' );

				get_template_part( 'template-parts/section-head', null, array(
					'number'     => (string) p10_option( 'cities_number', '04' ),
					'kicker'     => (string) p10_option( 'cities_kicker', '' ),
					'title'      => (string) p10_option( 'cities_title', '' ),
					'accent'     => (string) p10_option( 'cities_accent', '' ),
					'action'     => (string) p10_option( 'cities_action', '' ),
					'action_url' => $cities_url,
				) );
				?>

				<div class="p10-grid p10-grid--cities">
					<?php foreach ( $featured_cities as $i => $term ) : ?>
						<?php
						get_template_part( 'template-parts/card-city', null, array(
							'term'  => $term,
							'index' => $i + 1,
						) );
						?>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<?php
	endif;
endif;

get_template_part( 'template-parts/callband' );
get_template_part( 'template-parts/about' );

/* 05 — Reviews. */
if ( p10_option( 'reviews_enabled', 1 ) ) :
	$reviews = p10_get_featured_reviews( (int) p10_option( 'reviews_count', 3 ) );
	if ( ! empty( $reviews ) ) :
		?>
		<section class="p10-section p10-section--panel p10-section--gold-line" id="p10-reviews">
			<div class="p10-shell">

				<?php
				get_template_part( 'template-parts/section-head', null, array(
					'number'     => (string) p10_option( 'reviews_number', '05' ),
					'kicker'     => (string) p10_option( 'reviews_kicker', '' ),
					'title'      => (string) p10_option( 'reviews_title', '' ),
					'accent'     => (string) p10_option( 'reviews_accent', '' ),
					'action'     => (string) p10_option( 'reviews_action', '' ),
					'action_url' => p10_utility_url( 'reviews' ),
				) );
				?>

				<div class="p10-grid p10-grid--reviews">
					<?php foreach ( $reviews as $i => $review ) : ?>
						<?php
						get_template_part( 'template-parts/review', null, array(
							'review' => $review,
							'index'  => $i + 1,
						) );
						?>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<?php
	endif;
endif;

get_template_part( 'template-parts/advertisers' );

get_footer();

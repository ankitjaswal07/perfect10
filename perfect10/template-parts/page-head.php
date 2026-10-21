<?php
/**
 * Archive / page head: breadcrumb, title, meta line and blurb.
 *
 * Expected $args:
 *   title   string Override the title.
 *   blurb   string Override the blurb.
 *   kicker  string Optional eyebrow above the title.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args   = isset( $args ) ? $args : array();
$title  = isset( $args['title'] ) ? $args['title'] : '';
$blurb  = isset( $args['blurb'] ) ? $args['blurb'] : '';
$kicker = isset( $args['kicker'] ) ? $args['kicker'] : '';
$term   = null;
$meta   = array();

if ( ! $title ) {
	if ( is_tax( 'p10_city' ) || is_tax( 'p10_county' ) || is_tax( 'p10_category' ) || is_tax( 'p10_nationality' ) ) {
		$term  = get_queried_object();
		$title = $term ? $term->name : '';
	} elseif ( is_post_type_archive( 'p10_profile' ) ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_post_type_archive( 'p10_review' ) ) {
		$title = __( 'Guest Reviews', 'perfect10' );
	} elseif ( is_search() ) {
		$title = sprintf(
			/* translators: %s: search query. */
			__( 'Results for “%s”', 'perfect10' ),
			get_search_query()
		);
	} elseif ( is_singular() ) {
		$title = get_the_title();
	} else {
		$title = get_the_title();
	}
}

if ( ! $blurb ) {
	if ( $term ) {
		$blurb = (string) p10_term_meta( $term, 'blurb', '' );
		if ( ! $blurb && $term->description ) {
			$blurb = wp_strip_all_tags( $term->description );
		}
	}
}

// Meta line: county, profile count, kind.
if ( $term instanceof WP_Term ) {
	if ( 'p10_city' === $term->taxonomy ) {
		$county_slug = p10_city_county_slug( $term );
		$county      = $county_slug ? get_term_by( 'slug', $county_slug, 'p10_county' ) : null;
		if ( $county ) {
			$meta[] = $county->name;
		}
		$kind = (string) p10_term_meta( $term, 'kind', 'city' );
		if ( 'district' === $kind ) {
			$meta[] = __( 'District', 'perfect10' );
		}
	}
	if ( 'p10_county' === $term->taxonomy ) {
		$cities = p10_get_cities( $term->slug );
		if ( $cities ) {
			$meta[] = sprintf(
				/* translators: %s: number of cities. */
				_n( '%s city', '%s cities', count( $cities ), 'perfect10' ),
				number_format_i18n( count( $cities ) )
			);
		}
	}
}

$profile_count = is_tax( 'p10_county' ) || is_tax( 'p10_city' ) || is_tax( 'p10_category' ) || is_tax( 'p10_nationality' )
	? p10_count_profiles( $term )
	: ( is_post_type_archive( 'p10_profile' ) ? p10_count_profiles() : null );

if ( null !== $profile_count ) {
	$meta[] = sprintf(
		/* translators: %s: number of profiles. */
		_n( '%s profile', '%s profiles', $profile_count, 'perfect10' ),
		number_format_i18n( $profile_count )
	);
}
?>
<header class="p10-page-head">
	<div class="p10-shell">

		<?php
		if ( ! isset( $args['breadcrumb'] ) || $args['breadcrumb'] ) {
			p10_breadcrumb();
		}
		?>

		<?php if ( $kicker ) : ?>
			<?php p10_section_eyebrow( '', $kicker ); ?>
		<?php endif; ?>

		<h1 class="p10-page-head__title"><?php echo esc_html( $title ); ?></h1>

		<?php if ( ! empty( $meta ) ) : ?>
			<p class="p10-page-head__meta">
				<?php
				$meta_html = array();
				foreach ( $meta as $piece ) {
					$meta_html[] = '<span>' . esc_html( $piece ) . '</span>';
				}
				echo wp_kses_post( implode( '<span aria-hidden="true">◆</span>', $meta_html ) );
				?>
			</p>
		<?php endif; ?>

		<?php if ( $blurb ) : ?>
			<p class="p10-page-head__blurb"><?php echo esc_html( $blurb ); ?></p>
		<?php endif; ?>

	</div>
</header>

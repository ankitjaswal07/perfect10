<?php
/**
 * Template Name: P10 — All Cities
 * Template Post Type: page
 *
 * A–Z index of every city and district, grouped by county.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<header class="p10-page-head">
		<div class="p10-shell">
			<?php p10_breadcrumb(); ?>
			<?php p10_section_eyebrow( '', __( 'Destinations', 'perfect10' ) ); ?>
			<h1 class="p10-page-head__title"><?php the_title(); ?></h1>
			<p class="p10-page-head__meta">
				<span>
					<?php
					echo esc_html( sprintf(
						/* translators: %s: number of locations. */
						_n( '%s location', '%s locations', count( p10_get_cities() ), 'perfect10' ),
						number_format_i18n( count( p10_get_cities() ) )
					) );
					?>
				</span>
				<span aria-hidden="true">◆</span>
				<span><?php echo esc_html( number_format_i18n( p10_count_profiles() ) ); ?> <?php esc_html_e( 'profiles', 'perfect10' ); ?></span>
			</p>
			<?php if ( has_excerpt() ) : ?>
				<p class="p10-page-head__blurb"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<section class="p10-section p10-section--tight">
		<div class="p10-shell">

			<?php foreach ( p10_get_counties() as $county ) : ?>
				<?php
				$cities  = p10_get_cities( $county->slug );
				$grouped = array();
				foreach ( $cities as $city ) {
					$letter             = strtoupper( mb_substr( $city->name, 0, 1 ) );
					$grouped[ $letter ][] = $city;
				}
				ksort( $grouped );
				?>
				<div class="p10-section-head" style="margin-top:calc(var(--p10-fluid-space) * 3)">
					<div class="p10-section-head__main">
						<?php p10_section_eyebrow( '', $county->name ); ?>
						<?php
						p10_the_heading(
							__( 'Cities in', 'perfect10' ),
							$county->name,
							'h2',
							'p10-display-md'
						);
						?>
					</div>
					<div class="p10-section-head__aside">
						<a class="p10-link" href="<?php echo esc_url( p10_term_url( $county ) ); ?>">
							<?php esc_html_e( 'View county', 'perfect10' ); ?>
							<span class="p10-link__arrow" aria-hidden="true">→</span>
						</a>
					</div>
				</div>

				<div class="p10-city-index">
					<?php foreach ( $grouped as $letter => $letter_cities ) : ?>
						<div class="p10-city-index__group">
							<h3 class="p10-city-index__letter"><?php echo esc_html( $letter ); ?></h3>
							<ul class="p10-city-index__list">
								<?php foreach ( $letter_cities as $city ) : ?>
									<li>
										<a href="<?php echo esc_url( p10_term_url( $city ) ); ?>">
											<?php echo esc_html( $city->name ); ?>
											<span><?php echo esc_html( number_format_i18n( $city->count ) ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<?php if ( trim( (string) get_the_content() ) ) : ?>
				<div class="p10-content" style="margin-top:calc(var(--p10-fluid-space) * 3);max-width:78ch"><?php the_content(); ?></div>
			<?php endif; ?>

		</div>
	</section>
	<?php
endwhile;

get_template_part( 'template-parts/callband' );
get_footer();

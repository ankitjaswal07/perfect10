<?php
/**
 * Template Name: P10 — Premium Packages
 * Template Post Type: page
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
			<?php p10_section_eyebrow( '', __( 'Exclusive Placement', 'perfect10' ) ); ?>
			<h1 class="p10-page-head__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="p10-page-head__blurb"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php elseif ( p10_option( 'premium_quote' ) ) : ?>
				<p class="p10-page-head__blurb"><?php echo esc_html( p10_option( 'premium_quote' ) ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<section class="p10-section p10-section--tight">
		<div class="p10-shell">

			<?php $packages = p10_get_packages(); ?>

			<?php if ( ! empty( $packages ) ) : ?>
				<div class="p10-grid p10-grid--packages">
					<?php foreach ( $packages as $package ) : ?>
						<?php get_template_part( 'template-parts/card-package', null, array( 'package' => $package ) ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/none' ); ?>
			<?php endif; ?>

			<?php if ( trim( (string) get_the_content() ) ) : ?>
				<div class="p10-content" style="margin-top:calc(var(--p10-fluid-space) * 3);max-width:78ch"><?php the_content(); ?></div>
			<?php endif; ?>

		</div>
	</section>

	<?php
	$premium = p10_get_premium_profiles( 6 );
	if ( ! empty( $premium ) ) :
		?>
		<section class="p10-section p10-section--panel p10-section--gold-line">
			<div class="p10-shell">
				<?php
				get_template_part( 'template-parts/section-head', null, array(
					'kicker'     => __( 'Currently placed', 'perfect10' ),
					'title'      => __( 'The Premium', 'perfect10' ),
					'accent'     => __( 'Collection', 'perfect10' ),
					'action'     => __( 'Enter the collection', 'perfect10' ),
					'action_url' => p10_directory_url( array( 'premium' => 1 ) ),
				) );
				?>
				<div class="p10-grid p10-grid--profiles">
					<?php foreach ( $premium as $profile ) : ?>
						<?php
						get_template_part( 'template-parts/card-profile', null, array(
							'profile' => $profile,
							'index'   => 0,
							'variant' => 'premium',
						) );
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_template_part( 'template-parts/advertisers' );
get_footer();

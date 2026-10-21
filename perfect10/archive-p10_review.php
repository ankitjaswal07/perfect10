<?php
/**
 * All guest reviews.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/page-head', null, array(
	'kicker' => __( 'From Our Guests', 'perfect10' ),
	'title'  => __( 'In Their', 'perfect10' ),
	'blurb'  => p10_option( 'reviews_blurb', '' ),
) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">

		<?php if ( have_posts() ) : ?>
			<div class="p10-grid p10-grid--reviews">
				<?php
				$i = 0;
				while ( have_posts() ) :
					the_post();
					$i++;
					get_template_part( 'template-parts/review', null, array(
						'review' => get_post(),
						'index'  => $i,
					) );
				endwhile;
				?>
			</div>
			<?php get_template_part( 'template-parts/pagination' ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/none' ); ?>
		<?php endif; ?>

	</div>
</section>

<?php
get_template_part( 'template-parts/callband' );
get_footer();

<?php
/**
 * Fallback template.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/page-head', null, array(
	'title' => is_home() && ! is_front_page() ? __( 'Journal', 'perfect10' ) : '',
) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">

		<?php if ( have_posts() ) : ?>

			<div class="p10-search-results">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'p10-search-result' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
								<?php the_post_thumbnail( 'p10-thumb' ); ?>
							</a>
						<?php endif; ?>

						<div>
							<h2 class="p10-search-result__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							<p class="p10-search-result__meta">
								<?php echo esc_html( get_the_date() ); ?>
								<span aria-hidden="true">◆</span>
								<?php the_author(); ?>
							</p>
							<div class="p10-search-result__excerpt"><?php the_excerpt(); ?></div>
							<a class="p10-link" href="<?php the_permalink(); ?>" style="margin-top:.9rem">
								<?php esc_html_e( 'Read more', 'perfect10' ); ?>
								<span class="p10-link__arrow" aria-hidden="true">→</span>
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<?php get_template_part( 'template-parts/pagination' ); ?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/none' ); ?>
		<?php endif; ?>

	</div>
</section>

<?php get_footer(); ?>

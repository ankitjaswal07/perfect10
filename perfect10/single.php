<?php
/**
 * Single post.
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
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<header class="p10-page-head">
			<div class="p10-shell">
				<?php p10_breadcrumb(); ?>
				<h1 class="p10-page-head__title"><?php the_title(); ?></h1>
				<p class="p10-page-head__meta">
					<span><?php echo esc_html( get_the_date() ); ?></span>
					<span aria-hidden="true">◆</span>
					<span><?php the_author(); ?></span>
				</p>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="p10-shell">
				<figure style="margin:2rem 0">
					<?php the_post_thumbnail( 'p10-card-wide' ); ?>
				</figure>
			</div>
		<?php endif; ?>

		<section class="p10-section p10-section--tight" style="padding-top:0">
			<div class="p10-shell">
				<div class="p10-content" style="max-width:74ch">
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

	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;

get_footer();

<?php
/**
 * Standard page.
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

		<?php if ( has_post_thumbnail() ) : ?>
			<header class="p10-page-head" style="background-image:linear-gradient(180deg,rgba(11,10,9,.86),rgba(11,10,9,.94))">
				<div class="p10-shell">
					<?php p10_breadcrumb(); ?>
					<h1 class="p10-page-head__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="p10-page-head__blurb"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</header>
		<?php else : ?>
			<header class="p10-page-head">
				<div class="p10-shell">
					<?php p10_breadcrumb(); ?>
					<h1 class="p10-page-head__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="p10-page-head__blurb"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</header>
		<?php endif; ?>

		<section class="p10-section p10-section--tight">
			<div class="p10-shell">
				<div class="p10-content" style="max-width:78ch">
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

<?php
/**
 * Editorial about blocks.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! p10_option( 'about_enabled', 1 ) ) {
	return;
}

$blocks = p10_about_blocks();
if ( empty( $blocks ) ) {
	return;
}
?>
<section class="p10-section p10-section--panel p10-section--gold-line">
	<div class="p10-shell">
		<div class="p10-about">

			<?php foreach ( $blocks as $i => $block ) : ?>
				<article class="p10-about__block p10-reveal" data-delay="<?php echo esc_attr( $i % 3 ); ?>">
					<?php if ( ! empty( $block['number'] ) ) : ?>
						<span class="p10-about__num" aria-hidden="true"><?php echo esc_html( $block['number'] ); ?></span>
					<?php else : ?>
						<span aria-hidden="true"></span>
					<?php endif; ?>

					<div class="p10-about__body">
						<?php if ( ! empty( $block['kicker'] ) ) : ?>
							<p class="p10-about__kicker"><?php echo esc_html( $block['kicker'] ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $block['title'] ) ) : ?>
							<?php p10_the_heading( $block['title'], isset( $block['accent'] ) ? $block['accent'] : '', 'h3', 'p10-display-md' ); ?>
						<?php endif; ?>

						<?php if ( ! empty( $block['copy'] ) ) : ?>
							<div class="p10-about__copy"><?php echo wp_kses_post( wpautop( $block['copy'] ) ); ?></div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>

		</div>
	</div>
</section>

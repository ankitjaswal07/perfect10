<?php
/**
 * "For Advertisers" band.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! p10_option( 'ads_enabled', 1 ) ) {
	return;
}

$image_id = (int) p10_option( 'ads_image', 0 );
$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'p10-card' ) : '';
?>
<section class="p10-section p10-section--panel">
	<div class="p10-shell">
		<div class="p10-ads">

			<div class="p10-ads__main p10-reveal">
				<?php p10_section_eyebrow( '', (string) p10_option( 'ads_kicker', '' ) ); ?>

				<?php
				p10_the_heading(
					(string) p10_option( 'ads_title', '' ),
					(string) p10_option( 'ads_accent', '' ),
					'h2',
					'p10-display-lg'
				);
				?>

				<?php if ( p10_option( 'ads_copy' ) ) : ?>
					<p class="p10-lede"><?php echo esc_html( p10_option( 'ads_copy' ) ); ?></p>
				<?php endif; ?>

				<div class="p10-ads__actions">
					<a class="p10-btn p10-btn--gold" href="<?php echo esc_url( p10_utility_url( 'register' ) ); ?>">
						<?php echo esc_html( p10_option( 'ads_cta_1', __( 'Create Independent Listing', 'perfect10' ) ) ); ?>
					</a>
					<a class="p10-btn p10-btn--ghost" href="<?php echo esc_url( p10_utility_url( 'packages' ) ); ?>">
						<?php echo esc_html( p10_option( 'ads_cta_2', __( 'View Premium Packages', 'perfect10' ) ) ); ?>
					</a>
				</div>

				<?php if ( p10_option( 'ads_tags' ) ) : ?>
					<p class="p10-ads__tags"><?php echo esc_html( p10_option( 'ads_tags' ) ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $image ) : ?>
				<div class="p10-ads__media p10-reveal" data-delay="2">
					<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>

<?php
/**
 * Call band — "Requests within 24 hours? Call us."
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! p10_option( 'call_enabled', 1 ) ) {
	return;
}
?>
<section class="p10-callband">
	<div class="p10-shell">
		<div class="p10-callband__inner">

			<div class="p10-callband__main p10-reveal">
				<?php p10_section_eyebrow( '', (string) p10_option( 'call_kicker', '' ) ); ?>

				<?php
				p10_the_heading(
					(string) p10_option( 'call_title', '' ),
					(string) p10_option( 'call_accent', '' ),
					'h2',
					'p10-callband__title'
				);
				?>

				<?php if ( p10_option( 'call_copy' ) ) : ?>
					<p class="p10-callband__copy"><?php echo esc_html( p10_option( 'call_copy' ) ); ?></p>
				<?php endif; ?>
			</div>

			<div class="p10-callband__actions p10-reveal" data-delay="2">
				<?php if ( p10_phone_display() ) : ?>
					<a class="p10-callband__number" href="<?php echo esc_url( p10_phone_href() ); ?>">
						<?php echo esc_html( p10_phone_display() ); ?>
					</a>
				<?php endif; ?>

				<a class="p10-btn p10-btn--ghost p10-btn--block" href="<?php echo esc_url( p10_utility_url( 'booking' ) ); ?>">
					<?php echo esc_html( p10_option( 'call_cta', __( 'Reservation Form', 'perfect10' ) ) ); ?>
				</a>

				<?php if ( p10_option( 'contact_note' ) ) : ?>
					<p class="p10-callband__note"><?php echo esc_html( p10_option( 'contact_note' ) ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>

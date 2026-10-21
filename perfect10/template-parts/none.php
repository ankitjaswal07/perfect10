<?php
/**
 * Empty state for archives and searches.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="p10-empty p10-reveal">
	<p class="p10-empty__mark" aria-hidden="true">◆</p>
	<h2 class="p10-empty__title"><?php esc_html_e( 'Nothing here yet', 'perfect10' ); ?></h2>
	<p class="p10-empty__copy">
		<?php
		if ( is_search() ) {
			esc_html_e( 'No profiles match that search. Try a different city or clear your filters.', 'perfect10' );
		} else {
			esc_html_e( 'No profiles have been published for this location yet. Browse the full directory or call the booking agent for current availability.', 'perfect10' );
		}
		?>
	</p>
	<div style="display:flex;flex-wrap:wrap;gap:calc(var(--p10-fluid-space) * 0.85);justify-content:center;margin-top:calc(var(--p10-fluid-space) * 1.8)">
		<a class="p10-btn p10-btn--gold" href="<?php echo esc_url( p10_directory_url() ); ?>">
			<?php esc_html_e( 'Full directory', 'perfect10' ); ?>
		</a>
		<?php if ( p10_phone_display() ) : ?>
			<a class="p10-btn p10-btn--ghost" href="<?php echo esc_url( p10_phone_href() ); ?>">
				<?php echo esc_html( p10_phone_display() ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>

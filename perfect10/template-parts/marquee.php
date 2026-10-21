<?php
/**
 * Scrolling ticker band.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! p10_option( 'marquee_enabled', 1 ) ) {
	return;
}

$items = p10_get_marquee_items();
if ( empty( $items ) ) {
	return;
}
?>
<div class="p10-marquee" data-p10-marquee aria-hidden="true">
	<div class="p10-marquee__track">
		<div class="p10-marquee__group">
			<?php foreach ( $items as $item ) : ?>
				<span class="p10-marquee__item"><?php echo esc_html( $item ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
</div>

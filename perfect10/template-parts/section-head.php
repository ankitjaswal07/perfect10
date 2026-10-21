<?php
/**
 * Section heading: number, kicker, split title and an optional action link.
 *
 * Expected $args:
 *   number   string  Editorial number, e.g. "01".
 *   kicker   string  Uppercase kicker text.
 *   title    string  Heading text before the accent.
 *   accent   string  Italic gold accent text.
 *   action   string  Action link label.
 *   action_url string Action link URL.
 *   center   bool    Centre the block.
 *   tag      string  Heading tag, default h2.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = isset( $args ) ? $args : array();

$number     = isset( $args['number'] ) ? $args['number'] : '';
$kicker     = isset( $args['kicker'] ) ? $args['kicker'] : '';
$title      = isset( $args['title'] ) ? $args['title'] : '';
$accent     = isset( $args['accent'] ) ? $args['accent'] : '';
$action     = isset( $args['action'] ) ? $args['action'] : '';
$action_url = isset( $args['action_url'] ) ? $args['action_url'] : '';
$center     = ! empty( $args['center'] );
$tag        = isset( $args['tag'] ) ? $args['tag'] : 'h2';

if ( ! $title && ! $kicker ) {
	return;
}
?>
<header class="p10-section-head<?php echo $center ? ' p10-section-head--center' : ''; ?>">

	<div class="p10-section-head__main">
		<?php if ( $number || $kicker ) : ?>
			<?php p10_section_eyebrow( $number, $kicker ); ?>
		<?php endif; ?>

		<?php if ( $title ) : ?>
			<?php p10_the_heading( $title, $accent, $tag, 'p10-display-lg' ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $args['lede'] ) ) : ?>
			<p class="p10-lede<?php echo $center ? ' p10-lede--center' : ''; ?>"><?php echo esc_html( $args['lede'] ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $action && $action_url ) : ?>
		<div class="p10-section-head__aside">
			<a class="p10-link" href="<?php echo esc_url( $action_url ); ?>">
				<?php echo esc_html( $action ); ?>
				<span class="p10-link__arrow" aria-hidden="true">→</span>
			</a>
		</div>
	<?php endif; ?>

</header>

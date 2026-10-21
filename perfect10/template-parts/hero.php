<?php
/**
 * Hero.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats      = p10_get_stats();
$cta_url    = (string) p10_option( 'hero_cta_primary_url', '' );
if ( ! $cta_url ) {
	$cta_url = p10_directory_url();
}
$background = isset( $args['background'] ) && is_array( $args['background'] ) ? $args['background'] : array();
$alignment  = isset( $args['align'] ) ? (string) $args['align'] : ( 'center' === p10_option( 'hero_text_align', 'left' ) ? 'center' : 'left' );
$extra      = isset( $args['class'] ) ? ' ' . sanitize_html_class( (string) $args['class'] ) : '';
$video_css  = ( 'image' !== ( $background['type'] ?? p10_option( 'hero_background_type', 'image' ) ) ) ? ' p10-hero--has-video' : '';
$style      = '';
if ( ! empty( $background['overlay_color'] ) ) {
	$style .= '--p10-hero-overlay-color:' . sanitize_hex_color( $background['overlay_color'] ) . ';';
}
if ( isset( $background['overlay'] ) && is_numeric( $background['overlay'] ) ) {
	$style .= '--p10-hero-overlay:' . max( 0, min( 1, (float) $background['overlay'] ) ) . ';';
}
if ( ! empty( $background['min_height'] ) ) {
	$style .= '--p10-hero-min-height:' . max( 40, min( 100, absint( $background['min_height'] ) ) ) . 'svh;';
}
?>
<section class="p10-hero p10-hero--align-<?php echo esc_attr( $alignment ); ?><?php echo $video_css . esc_attr( $extra ); ?>" data-p10-hero<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>

	<?php echo p10_hero_media_html( $background ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped media markup. ?>
	<div class="p10-hero__scrim"></div>

	<div class="p10-hero__inner">

		<?php if ( p10_option( 'hero_eyebrow' ) ) : ?>
			<p class="p10-hero__eyebrow" data-p10-hero-eyebrow><?php echo esc_html( p10_option( 'hero_eyebrow' ) ); ?></p>
		<?php endif; ?>

		<h1 class="p10-hero__title">
			<span data-p10-hero-before><?php echo esc_html( p10_option( 'hero_title_before', '' ) ); ?></span>
			<?php if ( p10_option( 'hero_title_accent' ) ) : ?>
				<em data-p10-hero-accent><?php echo esc_html( p10_option( 'hero_title_accent' ) ); ?></em>
			<?php endif; ?>
		</h1>

		<?php
		$tagline = (string) p10_option( 'hero_tagline', '' );
		if ( $tagline ) :
			$tagline_parts = preg_split( '/\s*◆\s*/u', $tagline );
			$tagline_parts = array_values( array_filter( array_map( 'trim', $tagline_parts ) ) );
			?>
			<p class="p10-hero__tagline" data-p10-hero-tagline>
				<?php foreach ( $tagline_parts as $i => $part ) : ?>
					<?php if ( $i > 0 ) : ?><i aria-hidden="true">◆</i><?php endif; ?>
					<span><?php echo esc_html( $part ); ?></span>
				<?php endforeach; ?>
			</p>
		<?php endif; ?>

		<?php if ( p10_option( 'hero_copy' ) ) : ?>
			<p class="p10-hero__copy"><?php echo esc_html( p10_option( 'hero_copy' ) ); ?></p>
		<?php endif; ?>

		<div class="p10-hero__cta">
			<?php if ( p10_option( 'hero_cta_primary' ) ) : ?>
				<a class="p10-btn p10-btn--gold p10-btn--lg" href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( p10_option( 'hero_cta_primary' ) ); ?>
				</a>
			<?php endif; ?>

			<?php if ( p10_phone_display() ) : ?>
				<a class="p10-btn p10-btn--ghost p10-btn--lg" href="<?php echo esc_url( p10_phone_href() ); ?>">
					<?php
					$secondary = (string) p10_option( 'hero_cta_secondary', '' );
					echo esc_html( $secondary ? $secondary . ' ' . p10_phone_display() : p10_phone_display() );
					?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $stats ) ) : ?>
			<div class="p10-hero__stats">
				<?php foreach ( $stats as $stat ) : ?>
					<div class="p10-stat p10-reveal">
						<?php
						$numeric = preg_replace( '/[^0-9]/', '', (string) $stat['value'] );
						$suffix  = trim( str_replace( $numeric, '', (string) $stat['value'] ) );
						?>
						<span class="p10-stat__value"
							<?php if ( $numeric ) : ?>
								data-p10-count="<?php echo esc_attr( $numeric ); ?>"
								data-p10-suffix="<?php echo esc_attr( $suffix ); ?>"
							<?php endif; ?>
						><?php echo esc_html( $stat['value'] ); ?></span>
						<span class="p10-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>

	<?php if ( p10_option( 'hero_scroll_label' ) ) : ?>
		<a class="p10-hero__cue" href="#p10-next" aria-label="<?php esc_attr_e( 'Scroll to content', 'perfect10' ); ?>">
			<span><?php echo esc_html( p10_option( 'hero_scroll_label' ) ); ?></span>
		</a>
	<?php endif; ?>

</section>

<?php
/**
 * Template Name: P10 — Reservation Form
 * Template Post Type: page
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
	<header class="p10-page-head">
		<div class="p10-shell">
			<?php p10_breadcrumb(); ?>
			<?php p10_section_eyebrow( '', (string) p10_option( 'call_kicker', __( 'Book an Escort Today', 'perfect10' ) ) ); ?>
			<?php
			p10_the_heading(
				(string) p10_option( 'call_title', get_the_title() ),
				(string) p10_option( 'call_accent', '' ),
				'h1',
				'p10-page-head__title'
			);
			?>
			<?php if ( p10_option( 'call_copy' ) ) : ?>
				<p class="p10-page-head__blurb"><?php echo esc_html( p10_option( 'call_copy' ) ); ?></p>
			<?php endif; ?>
			<?php if ( p10_phone_display() ) : ?>
				<p class="p10-page-head__meta" style="margin-top:calc(var(--p10-fluid-space) * 1.4)">
					<span><?php esc_html_e( 'Fastest route:', 'perfect10' ); ?></span>
					<b><a href="<?php echo esc_url( p10_phone_href() ); ?>"><?php echo esc_html( p10_phone_display() ); ?></a></b>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<section class="p10-section p10-section--tight">
		<div class="p10-shell">

			<div class="p10-search__panel p10-reveal">
				<?php p10_form_status( 'booking' ); ?>
				<?php
				p10_the_form( 'booking', array(
					'redirect' => get_permalink(),
					'submit'   => __( 'Send reservation request', 'perfect10' ),
				) );
				?>
			</div>

			<?php if ( trim( (string) get_the_content() ) ) : ?>
				<div class="p10-content" style="margin-top:calc(var(--p10-fluid-space) * 2.5);max-width:74ch"><?php the_content(); ?></div>
			<?php endif; ?>

		</div>
	</section>
	<?php
endwhile;

get_footer();

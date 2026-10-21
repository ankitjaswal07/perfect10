<?php
/**
 * 404.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="p10-404">
	<div>
		<p class="p10-404__num" aria-hidden="true">404</p>
		<?php p10_section_eyebrow( '', __( 'Not found', 'perfect10' ) ); ?>
		<h1 class="p10-display-lg" style="margin-bottom:1.2rem">
			<?php esc_html_e( 'That page has', 'perfect10' ); ?>
			<em><?php esc_html_e( 'left the building', 'perfect10' ); ?></em>
		</h1>
		<p class="p10-lede p10-lede--center" style="margin-bottom:2.4rem">
			<?php esc_html_e( 'The profile or city you were looking for is no longer available. Browse the directory or call the booking agent for current availability.', 'perfect10' ); ?>
		</p>
		<div style="display:flex;flex-wrap:wrap;gap:.9rem;justify-content:center">
			<a class="p10-btn p10-btn--gold p10-btn--lg" href="<?php echo esc_url( p10_directory_url() ); ?>">
				<?php esc_html_e( 'Explore Profiles', 'perfect10' ); ?>
			</a>
			<?php if ( p10_phone_display() ) : ?>
				<a class="p10-btn p10-btn--ghost p10-btn--lg" href="<?php echo esc_url( p10_phone_href() ); ?>">
					<?php echo esc_html( p10_phone_display() ); ?>
				</a>
			<?php endif; ?>
		</div>

		<form class="p10-search__grid" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="max-width:640px;margin:3.5rem auto 0">
			<div class="p10-field" style="grid-column:1/-1">
				<label class="p10-field__label" for="p10-404-s"><?php esc_html_e( 'Search the directory', 'perfect10' ); ?></label>
				<input class="p10-input" type="search" id="p10-404-s" name="s" placeholder="<?php esc_attr_e( 'Name or city…', 'perfect10' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			</div>
		</form>
	</div>
</section>

<?php get_footer(); ?>

<?php
/**
 * Search panel — cascading county / city lookup.
 *
 * Degrades to a plain GET form against the directory when JavaScript is off;
 * main.js intercepts the submit and routes to the pretty /county/city/ URL.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! p10_option( 'search_enabled', 1 ) ) {
	return;
}

$grouped   = p10_get_cities_by_county();
$counties  = p10_get_counties();
$home      = trailingslashit( home_url( '/' ) );
$template  = $home . '__county__/__city__/';
$directory = p10_directory_url();
?>
<section class="p10-section p10-section--tight p10-search" id="p10-next">
	<div class="p10-shell">

		<div class="p10-search__panel p10-reveal">

			<div class="p10-search__head">
				<div>
					<?php if ( p10_option( 'search_kicker' ) ) : ?>
						<p class="p10-eyebrow"><span class="p10-eyebrow__text"><?php echo esc_html( p10_option( 'search_kicker' ) ); ?></span></p>
					<?php endif; ?>
					<?php
					p10_the_heading(
						(string) p10_option( 'search_title', '' ),
						(string) p10_option( 'search_accent', '' ),
						'h2',
						'p10-display-md'
					);
					?>
				</div>

				<?php if ( p10_option( 'search_region' ) ) : ?>
					<p class="p10-search__caption">
						<?php echo esc_html( p10_option( 'search_label', __( 'Where are you looking?', 'perfect10' ) ) ); ?><br>
						<b><?php echo esc_html( p10_option( 'search_region' ) ); ?></b>
					</p>
				<?php endif; ?>
			</div>

			<form
				class="p10-search__grid"
				method="get"
				action="<?php echo esc_url( $directory ); ?>"
				data-p10-search-form
				data-url-template="<?php echo esc_attr( $template ); ?>"
				role="search">

				<div class="p10-field">
					<label class="p10-field__label" for="p10-search-county"><?php esc_html_e( 'County', 'perfect10' ); ?></label>
					<select
						class="p10-select"
						id="p10-search-county"
						name="county"
						data-p10-county
						data-link-base="<?php echo esc_attr( $home . '__county__/' ); ?>">
						<option value=""><?php esc_html_e( 'All Counties', 'perfect10' ); ?></option>
						<?php foreach ( $counties as $county ) : ?>
							<option value="<?php echo esc_attr( $county->slug ); ?>"><?php echo esc_html( $county->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="p10-field">
					<label class="p10-field__label" for="p10-search-city"><?php esc_html_e( 'City', 'perfect10' ); ?></label>
					<select class="p10-select" id="p10-search-city" name="city" data-p10-city>
						<option value=""><?php esc_html_e( 'All Cities', 'perfect10' ); ?></option>
						<?php
						// Render grouped options so the no-JS version is complete.
						foreach ( $grouped as $county_slug => $group ) :
							if ( empty( $group['cities'] ) ) {
								continue;
							}
							?>
							<optgroup label="<?php echo esc_attr( $group['label'] ); ?>" data-county="<?php echo esc_attr( $county_slug ); ?>">
								<?php foreach ( $group['cities'] as $city ) : ?>
									<option value="<?php echo esc_attr( $city->slug ); ?>"><?php echo esc_html( $city->name ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="p10-field">
					<span class="p10-field__label p10-sr"><?php esc_html_e( 'Submit', 'perfect10' ); ?></span>
					<button class="p10-btn p10-btn--gold" type="submit">
						<?php echo esc_html( p10_option( 'search_button', __( 'Search', 'perfect10' ) ) ); ?>
					</button>
				</div>
			</form>

			<?php $quick_links = p10_get_categories(); ?>
			<?php if ( ! empty( $quick_links ) ) : ?>
				<div class="p10-search__hint">
					<span><?php esc_html_e( 'Popular:', 'perfect10' ); ?></span>
					<?php foreach ( array_slice( $quick_links, 0, 5 ) as $category ) : ?>
						<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
					<?php endforeach; ?>
					<a href="<?php echo esc_url( p10_directory_url( array( 'premium' => 1 ) ) ); ?>"><?php esc_html_e( 'Premium', 'perfect10' ); ?></a>
				</div>
			<?php endif; ?>

		</div>

	</div>
</section>

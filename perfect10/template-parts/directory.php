<?php
/**
 * Directory listing: filter sidebar, toolbar, card grid and pagination.
 *
 * Expected $args:
 *   query   WP_Query Optional query; defaults to the main query.
 *   sidebar bool     Show the filter sidebar.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$args    = isset( $args ) ? $args : array();
$query   = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : $wp_query;
$sidebar = array_key_exists( 'sidebar', $args ) ? (bool) $args['sidebar'] : (bool) p10_option( 'directory_sidebar', 1 );

$state   = p10_filter_state();
$posts   = $query->posts;
$found   = (int) $query->found_posts;
$columns = (int) p10_option( 'directory_columns', 3 );

/*
 * On a taxonomy archive the county/city/type are implied by the term, so the
 * filter form posts back to the flat directory with those values pinned as
 * hidden inputs. Everywhere else it filters the current URL in place.
 */
$pinned      = array();
$is_tax_view = is_tax( array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ) );

if ( $is_tax_view ) {
	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		switch ( $term->taxonomy ) {
			case 'p10_county':
				$pinned['county'] = $term->slug;
				break;
			case 'p10_city':
				$pinned['city'] = $term->slug;
				$parent         = p10_city_county_slug( $term );
				if ( $parent ) {
					$pinned['county'] = $parent;
				}
				break;
			case 'p10_category':
				$pinned['type'] = $term->slug;
				break;
			case 'p10_nationality':
				$pinned['nationality'] = $term->slug;
				break;
		}
	}
}

$form_action = $is_tax_view ? p10_directory_url() : strtok( p10_current_url(), '?' );
?>
<div class="p10-directory-layout<?php echo $sidebar ? '' : ' p10-directory-layout--no-sidebar'; ?>">

	<div class="p10-directory-main">

		<div class="p10-toolbar">
			<p class="p10-toolbar__count">
				<?php
				echo wp_kses_post( sprintf(
					/* translators: 1: number shown, 2: total number. */
					_n( 'Showing <b>%1$s</b> of <b>%2$s</b> profile', 'Showing <b>%1$s</b> of <b>%2$s</b> profiles', $found, 'perfect10' ),
					number_format_i18n( count( $posts ) ),
					number_format_i18n( $found )
				) );
				?>
			</p>

			<div class="p10-toolbar__right">
				<label class="p10-sr" for="p10-sort"><?php esc_html_e( 'Sort by', 'perfect10' ); ?></label>
				<select class="p10-select" id="p10-sort" data-p10-sort>
					<?php foreach ( p10_sort_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $state['sort'], $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<?php
		/* Active filter pills. */
		$active = p10_active_filters( $state, $pinned );
		if ( ! empty( $active ) ) :
			?>
			<div class="p10-active-filters">
				<?php foreach ( $active as $filter ) : ?>
					<a class="p10-active-filter" href="<?php echo esc_url( $filter['url'] ); ?>">
						<?php echo esc_html( $filter['label'] ); ?>
						<span class="p10-active-filter__x" aria-hidden="true">&times;</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $posts ) ) : ?>
			<div class="p10-grid p10-grid--profiles" style="--p10-grid-columns:<?php echo esc_attr( $columns ); ?>">
				<?php foreach ( $posts as $i => $profile ) : ?>
					<?php
					get_template_part( 'template-parts/card-profile', null, array(
						'profile' => $profile,
						'index'   => 0,
					) );
					?>
				<?php endforeach; ?>
			</div>

			<?php
			get_template_part( 'template-parts/pagination', null, array( 'query' => $query ) );
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/none' ); ?>
		<?php endif; ?>

	</div>

	<?php if ( $sidebar ) : ?>
		<aside class="p10-directory-aside">
			<form class="p10-filters" method="get" action="<?php echo esc_url( $form_action ); ?>" data-p10-filters>

				<?php foreach ( $pinned as $key => $value ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<?php endforeach; ?>

				<?php if ( $state['sort'] ) : ?>
					<input type="hidden" name="sort" value="<?php echo esc_attr( $state['sort'] ); ?>">
				<?php endif; ?>

				<?php if ( ! $is_tax_view ) : ?>
					<fieldset class="p10-filters__group">
						<legend class="p10-filters__legend"><?php esc_html_e( 'County', 'perfect10' ); ?></legend>
						<div class="p10-field">
							<label class="p10-sr" for="p10-filter-county"><?php esc_html_e( 'County', 'perfect10' ); ?></label>
							<select class="p10-select" id="p10-filter-county" name="county" data-p10-county>
								<option value=""><?php esc_html_e( 'All Counties', 'perfect10' ); ?></option>
								<?php foreach ( p10_get_counties() as $county ) : ?>
									<option value="<?php echo esc_attr( $county->slug ); ?>" <?php selected( $state['county'], $county->slug ); ?>>
										<?php echo esc_html( $county->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</fieldset>

					<fieldset class="p10-filters__group">
						<legend class="p10-filters__legend"><?php esc_html_e( 'City', 'perfect10' ); ?></legend>
						<div class="p10-field">
							<label class="p10-sr" for="p10-filter-city"><?php esc_html_e( 'City', 'perfect10' ); ?></label>
							<select class="p10-select" id="p10-filter-city" name="city" data-p10-city data-county="<?php echo esc_attr( $state['county'] ); ?>">
								<option value=""><?php esc_html_e( 'All Cities', 'perfect10' ); ?></option>
								<?php foreach ( p10_get_cities( $state['county'] ) as $city ) : ?>
									<option value="<?php echo esc_attr( $city->slug ); ?>" <?php selected( $state['city'], $city->slug ); ?>>
										<?php echo esc_html( $city->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</fieldset>
				<?php endif; ?>

				<?php $categories = p10_get_categories(); ?>
				<?php if ( ! empty( $categories ) && ! isset( $pinned['type'] ) ) : ?>
					<fieldset class="p10-filters__group">
						<legend class="p10-filters__legend"><?php esc_html_e( 'Profile Type', 'perfect10' ); ?></legend>
						<div class="p10-filters__options">
							<?php foreach ( $categories as $category ) : ?>
								<label class="p10-filters__option">
									<input type="radio" name="type" value="<?php echo esc_attr( $category->slug ); ?>"
										<?php checked( $state['category'], $category->slug ); ?>>
									<span><?php echo esc_html( $category->name ); ?></span>
									<span class="p10-filters__count"><?php echo esc_html( number_format_i18n( $category->count ) ); ?></span>
								</label>
							<?php endforeach; ?>
							<label class="p10-filters__option">
								<input type="radio" name="type" value="" <?php checked( $state['category'], '' ); ?>>
								<span><?php esc_html_e( 'All types', 'perfect10' ); ?></span>
							</label>
						</div>
					</fieldset>
				<?php endif; ?>

				<fieldset class="p10-filters__group">
					<legend class="p10-filters__legend"><?php esc_html_e( 'Placement', 'perfect10' ); ?></legend>
					<div class="p10-filters__options">
						<label class="p10-filters__option">
							<input type="checkbox" name="premium" value="1" <?php checked( $state['premium'] ); ?>>
							<span><?php esc_html_e( 'Premium only', 'perfect10' ); ?></span>
						</label>
						<label class="p10-filters__option">
							<input type="checkbox" name="verified" value="1" <?php checked( $state['verified'] ); ?>>
							<span><?php esc_html_e( 'Verified only', 'perfect10' ); ?></span>
						</label>
						<label class="p10-filters__option">
							<input type="checkbox" name="available" value="1" <?php checked( ! empty( $_GET['available'] ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>>
							<span><?php esc_html_e( 'Available now', 'perfect10' ); ?></span>
						</label>
					</div>
				</fieldset>

				<fieldset class="p10-filters__group">
					<legend class="p10-filters__legend"><?php esc_html_e( 'Refine', 'perfect10' ); ?></legend>

					<div class="p10-range">
						<label class="p10-field__label" for="p10-filter-rate"><?php esc_html_e( 'Max hourly rate', 'perfect10' ); ?></label>
						<input type="range" id="p10-filter-rate" name="max_rate" min="0" max="2000" step="50"
							value="<?php echo esc_attr( $state['max_rate'] ? $state['max_rate'] : 2000 ); ?>">
						<output class="p10-range__value" data-p10-range-out>
							<?php echo esc_html( $state['max_rate'] ? p10_format_money( $state['max_rate'] ) : __( 'Any', 'perfect10' ) ); ?>
						</output>
					</div>

					<div class="p10-range" style="margin-top:calc(var(--p10-fluid-space) * 1.2)">
						<label class="p10-field__label" for="p10-filter-min-age"><?php esc_html_e( 'Age range', 'perfect10' ); ?></label>
						<div style="display:flex;gap:calc(var(--p10-fluid-space) * 0.6);align-items:center">
							<input class="p10-input" type="number" id="p10-filter-min-age" name="min_age" min="18" max="99"
								placeholder="18" value="<?php echo esc_attr( $state['min_age'] ); ?>" style="width:50%">
							<span aria-hidden="true" style="color:var(--p10-muted-2)">–</span>
							<input class="p10-input" type="number" name="max_age" min="18" max="99"
								placeholder="99" value="<?php echo esc_attr( $state['max_age'] ); ?>" style="width:50%">
						</div>
					</div>

					<div class="p10-range" style="margin-top:calc(var(--p10-fluid-space) * 1.2)">
						<label class="p10-field__label" for="p10-filter-q"><?php esc_html_e( 'Search', 'perfect10' ); ?></label>
						<input class="p10-input" type="search" id="p10-filter-q" name="q"
							placeholder="<?php esc_attr_e( 'Name, city…', 'perfect10' ); ?>"
							value="<?php echo esc_attr( $state['search'] ); ?>">
					</div>
				</fieldset>

				<div class="p10-filters__actions">
					<button class="p10-btn p10-btn--gold p10-btn--block" type="submit">
						<?php esc_html_e( 'Apply filters', 'perfect10' ); ?>
					</button>
					<a class="p10-btn p10-btn--quiet p10-btn--block" href="<?php echo esc_url( $form_action ); ?>" data-p10-filters-reset>
						<?php esc_html_e( 'Reset', 'perfect10' ); ?>
					</a>
				</div>

			</form>

			<?php if ( is_active_sidebar( 'p10-directory' ) ) : ?>
				<div class="p10-filters" style="margin-top:calc(var(--p10-fluid-space) * 1.2)"><?php dynamic_sidebar( 'p10-directory' ); ?></div>
			<?php endif; ?>
		</aside>
	<?php endif; ?>

</div>

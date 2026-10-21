<?php
/**
 * Admin ergonomics: list columns, quick/bulk edit, dashboard glance counts,
 * a setup notice and the Perfect 10 tools screen.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add columns to the Profiles list table.
 *
 * @param array $columns Columns.
 * @return array
 */
function p10_profile_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['p10_city']    = __( 'City', 'perfect10' );
			$new['p10_age']     = __( 'Age', 'perfect10' );
			$new['p10_rate']    = __( 'Rate', 'perfect10' );
			$new['p10_premium'] = __( 'Premium', 'perfect10' );
			$new['p10_status']  = __( 'Availability', 'perfect10' );
		}
	}
	return $new;
}
add_filter( 'manage_p10_profile_posts_columns', 'p10_profile_columns' );

/**
 * Fill the profile columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function p10_profile_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'p10_city':
			$city   = p10_get_primary_term( $post_id, 'p10_city' );
			$county = p10_get_primary_term( $post_id, 'p10_county' );
			if ( $city ) {
				echo esc_html( $city->name );
				if ( $county ) {
					echo '<br><span style="color:#787c82;font-size:12px">' . esc_html( $county->name ) . '</span>';
				}
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;

		case 'p10_age':
			$age = p10_get_meta( $post_id, 'age' );
			echo $age ? esc_html( (string) $age ) : '<span aria-hidden="true">—</span>';
			break;

		case 'p10_rate':
			$rate     = p10_get_meta( $post_id, 'rate' );
			$currency = (string) p10_get_meta( $post_id, 'currency', p10_option( 'currency', 'USD' ) );
			echo $rate ? esc_html( p10_format_money( $rate, $currency ) ) : '<span aria-hidden="true">—</span>';
			break;

		case 'p10_premium':
			if ( p10_get_meta( $post_id, 'premium' ) ) {
				echo '<span class="p10-pill p10-pill--new" title="' . esc_attr__( 'Premium placement', 'perfect10' ) . '">◆</span>';
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			if ( p10_get_meta( $post_id, 'verified' ) ) {
				echo ' <span class="p10-pill p10-pill--confirmed" title="' . esc_attr__( 'Verified', 'perfect10' ) . '">✓</span>';
			}
			break;

		case 'p10_status':
			$availability = p10_profile_availability( $post_id );
			echo '<span class="p10-pill p10-pill--' . esc_attr( 'available' === $availability['slug'] ? 'confirmed' : 'declined' ) . '">' . esc_html( $availability['label'] ) . '</span>';
			break;
	}
}
add_action( 'manage_p10_profile_posts_custom_column', 'p10_profile_column_content', 10, 2 );

/**
 * Sortable columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function p10_profile_sortable_columns( $columns ) {
	$columns['p10_age']  = 'p10_age';
	$columns['p10_rate'] = 'p10_rate';
	return $columns;
}
add_filter( 'manage_edit-p10_profile_sortable_columns', 'p10_profile_sortable_columns' );

/**
 * Handle sorting by meta columns.
 *
 * @param WP_Query $query Query.
 */
function p10_profile_sort_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );
	if ( 'p10_age' === $orderby ) {
		$query->set( 'meta_key', 'p10_age' );
		$query->set( 'orderby', 'meta_value_num' );
	}
	if ( 'p10_rate' === $orderby ) {
		$query->set( 'meta_key', 'p10_rate' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'p10_profile_sort_query' );

/**
 * Add quick-edit fields.
 *
 * @param string $column_name Column.
 * @param string $post_type   Post type.
 */
function p10_quick_edit_fields( $column_name, $post_type ) {
	if ( 'p10_profile' !== $post_type || 'p10_premium' !== $column_name ) {
		return;
	}

	wp_nonce_field( 'p10_quick_edit', 'p10_quick_edit_nonce' );

	$availability = p10_availability_options();
	?>
	<fieldset class="inline-edit-col-right inline-edit-book">
		<div class="inline-edit-col">
			<h3><?php esc_html_e( 'Perfect 10', 'perfect10' ); ?></h3>

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Age', 'perfect10' ); ?></span>
				<span class="input-text-wrap"><input type="number" name="p10_age" min="18" max="99" value=""></span>
			</label>

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Rate/hr', 'perfect10' ); ?></span>
				<span class="input-text-wrap"><input type="number" name="p10_rate" min="0" step="10" value=""></span>
			</label>

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Availability', 'perfect10' ); ?></span>
				<select name="p10_available">
					<option value=""><?php esc_html_e( '— No change —', 'perfect10' ); ?></option>
					<?php foreach ( $availability as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Premium', 'perfect10' ); ?></span>
				<select name="p10_premium">
					<option value=""><?php esc_html_e( '— No change —', 'perfect10' ); ?></option>
					<option value="1"><?php esc_html_e( 'Yes', 'perfect10' ); ?></option>
					<option value="0"><?php esc_html_e( 'No', 'perfect10' ); ?></option>
				</select>
			</label>

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Verified', 'perfect10' ); ?></span>
				<select name="p10_verified">
					<option value=""><?php esc_html_e( '— No change —', 'perfect10' ); ?></option>
					<option value="1"><?php esc_html_e( 'Yes', 'perfect10' ); ?></option>
					<option value="0"><?php esc_html_e( 'No', 'perfect10' ); ?></option>
				</select>
			</label>
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'p10_quick_edit_fields', 10, 2 );

/**
 * Save quick-edit values.
 *
 * @param int $post_id Post ID.
 */
function p10_save_quick_edit( $post_id ) {
	if ( 'p10_profile' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['p10_quick_edit_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['p10_quick_edit_nonce'] ), 'p10_quick_edit' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_keys = array( 'p10_age', 'p10_rate' );
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) {
			update_post_meta( $post_id, $key, (float) sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	$switch_keys = array( 'p10_premium', 'p10_verified' );
	foreach ( $switch_keys as $key ) {
		if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) {
			$on = '1' === sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			if ( $on ) {
				update_post_meta( $post_id, $key, 1 );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}

	if ( ! empty( $_POST['p10_available'] ) ) {
		update_post_meta( $post_id, 'p10_available', sanitize_key( $_POST['p10_available'] ) );
	}
}
add_action( 'save_post_p10_profile', 'p10_save_quick_edit' );

/**
 * Pre-fill quick edit values via the hidden title column.
 */
function p10_quick_edit_data() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-p10_profile' !== $screen->id ) {
		return;
	}

	$payload = array();
	$ids     = get_posts( array(
		'post_type'      => 'p10_profile',
		'posts_per_page' => 500,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	foreach ( $ids as $id ) {
		$payload[ $id ] = array(
			'age'       => (string) p10_get_meta( $id, 'age', '' ),
			'rate'      => (string) p10_get_meta( $id, 'rate', '' ),
			'premium'   => p10_get_meta( $id, 'premium' ) ? '1' : '0',
			'verified'  => p10_get_meta( $id, 'verified' ) ? '1' : '0',
			'available' => (string) p10_get_meta( $id, 'available', 'available' ),
		);
	}

	printf(
		'<script id="p10-quick-edit-data" type="application/json">%s</script>',
		wp_json_encode( $payload ) // phpcs:ignore WordPress.Security.EscapeOutput
	);
}
add_action( 'admin_footer', 'p10_quick_edit_data' );

/**
 * Dashboard "At a Glance" counts.
 *
 * @param array $items Items.
 * @return array
 */
function p10_dashboard_glance( $items ) {
	$counts = array(
		'p10_profile' => array(
			'singular' => __( 'profile', 'perfect10' ),
			'plural'   => __( 'profiles', 'perfect10' ),
		),
		'p10_review'  => array(
			'singular' => __( 'review', 'perfect10' ),
			'plural'   => __( 'reviews', 'perfect10' ),
		),
		'p10_package' => array(
			'singular' => __( 'package', 'perfect10' ),
			'plural'   => __( 'packages', 'perfect10' ),
		),
	);

	foreach ( $counts as $post_type => $labels ) {
		$count = wp_count_posts( $post_type );
		$total = isset( $count->publish ) ? (int) $count->publish : 0;
		if ( ! $total ) {
			continue;
		}
		$items[] = sprintf(
			'<a href="%1$s">%2$s %3$s</a>',
			esc_url( admin_url( 'edit.php?post_type=' . $post_type ) ),
			esc_html( number_format_i18n( $total ) ),
			esc_html( 1 === $total ? $labels['singular'] : $labels['plural'] )
		);
	}

	$new = p10_count_new_enquiries();
	if ( $new ) {
		$items[] = sprintf(
			'<a href="%1$s" class="p10-glance-enquiries">%2$s %3$s</a>',
			esc_url( admin_url( 'edit.php?post_type=p10_enquiry' ) ),
			esc_html( number_format_i18n( $new ) ),
			esc_html( _n( 'new enquiry', 'new enquiries', $new, 'perfect10' ) )
		);
	}

	return $items;
}
add_filter( 'dashboard_glance_items', 'p10_dashboard_glance' );

/**
 * Nudge administrators to run the importer when the directory is empty.
 */
function p10_setup_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'p10_notice_dismissed' ) ) {
		return;
	}
	if ( p10_count_profiles() > 0 && count( p10_get_cities() ) > 0 ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'toplevel_page_p10-setup' === $screen->id ) {
		return;
	}
	?>
	<div class="notice notice-warning is-dismissible p10-setup-notice">
		<p>
			<strong><?php esc_html_e( 'Perfect 10 is installed but empty.', 'perfect10' ); ?></strong>
			<?php esc_html_e( 'Run the one-click importer to create the Los Angeles / Orange County locations, profile types, sample profiles, reviews and the utility pages this theme needs.', 'perfect10' ); ?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=p10-setup' ) ); ?>">
				<?php esc_html_e( 'Open Perfect 10 Setup', 'perfect10' ); ?>
			</a>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'p10_dismiss_notice', '1' ), 'p10_dismiss_notice' ) ); ?>">
				<?php esc_html_e( 'Dismiss', 'perfect10' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'p10_setup_notice' );

/**
 * Persist the notice dismissal.
 */
function p10_dismiss_notice() {
	if ( isset( $_GET['p10_dismiss_notice'] ) && check_admin_referer( 'p10_dismiss_notice' ) && current_user_can( 'manage_options' ) ) {
		update_option( 'p10_notice_dismissed', 1 );
		wp_safe_redirect( remove_query_arg( array( 'p10_dismiss_notice', '_wpnonce' ) ) );
		exit;
	}
}
add_action( 'admin_init', 'p10_dismiss_notice' );

/**
 * Add the Perfect 10 tools screen.
 */
function p10_admin_menu() {
	add_menu_page(
		__( 'Perfect 10', 'perfect10' ),
		__( 'Perfect 10', 'perfect10' ),
		'manage_options',
		'p10-setup',
		'p10_render_setup_screen',
		'dashicons-diamond',
		3
	);

	add_submenu_page(
		'p10-setup',
		__( 'Dashboard', 'perfect10' ),
		__( 'Dashboard', 'perfect10' ),
		'manage_options',
		'p10-setup',
		'p10_render_setup_screen'
	);
}
add_action( 'admin_menu', 'p10_admin_menu' );

/**
 * Move the CPT menus under the Perfect 10 screen for a tidy admin.
 */
function p10_admin_menu_order() {
	global $submenu;

	if ( empty( $submenu['p10-setup'] ) ) {
		return;
	}

	$links = array(
		'edit.php?post_type=p10_profile' => __( 'Profiles', 'perfect10' ),
		'edit-tags.php?taxonomy=p10_county&post_type=p10_profile' => __( 'Counties', 'perfect10' ),
		'edit-tags.php?taxonomy=p10_city&post_type=p10_profile'   => __( 'Cities', 'perfect10' ),
		'edit-tags.php?taxonomy=p10_category&post_type=p10_profile' => __( 'Profile Types', 'perfect10' ),
		'edit.php?post_type=p10_review'  => __( 'Reviews', 'perfect10' ),
		'edit.php?post_type=p10_package' => __( 'Packages', 'perfect10' ),
		'edit.php?post_type=p10_enquiry' => __( 'Enquiries', 'perfect10' ),
	);

	foreach ( $links as $url => $label ) {
		$submenu['p10-setup'][] = array( $label, 'edit_others_posts', admin_url( $url ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}
}
add_action( 'admin_menu', 'p10_admin_menu_order', 99 );

/**
 * Render the setup screen.
 */
function p10_render_setup_screen() {
	if ( function_exists( 'p10_render_admin_screen' ) ) {
		p10_render_admin_screen( 'overview' );
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'perfect10' ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Perfect 10', 'perfect10' ); ?></h1>
		<p><?php esc_html_e( 'The admin interface could not load. Check that inc/admin-ui.php is present in the theme.', 'perfect10' ); ?></p>
	</div>
	<?php
}

/**
 * Readiness checks for the setup screen.
 *
 * @return array
 */
function p10_setup_checks() {
	$front_page = (int) get_option( 'page_on_front' );
	$show_on    = get_option( 'show_on_front' );

	$checks = array(
		array(
			'label' => __( 'Counties', 'perfect10' ),
			'hint'  => __( 'Los Angeles County and Orange County.', 'perfect10' ),
			'ok'    => count( p10_get_counties() ) > 0,
			'value' => number_format_i18n( count( p10_get_counties() ) ),
		),
		array(
			'label' => __( 'Cities & districts', 'perfect10' ),
			'hint'  => __( 'Locations available in the search dropdowns.', 'perfect10' ),
			'ok'    => count( p10_get_cities() ) > 0,
			'value' => number_format_i18n( count( p10_get_cities() ) ),
		),
		array(
			'label' => __( 'Profile types', 'perfect10' ),
			'hint'  => __( 'Independent, Trans, Couples, Agencies, Massage, Tours.', 'perfect10' ),
			'ok'    => count( p10_get_categories() ) > 0,
			'value' => number_format_i18n( count( p10_get_categories() ) ),
		),
		array(
			'label' => __( 'Published profiles', 'perfect10' ),
			'hint'  => __( 'Profiles visible in the directory.', 'perfect10' ),
			'ok'    => p10_count_profiles() > 0,
			'value' => number_format_i18n( p10_count_profiles() ),
		),
		array(
			'label' => __( 'Guest reviews', 'perfect10' ),
			'hint'  => __( 'Shown in the “In Their Own Words” section.', 'perfect10' ),
			'ok'    => (bool) p10_get_featured_reviews( 1 ),
			'value' => __( 'Ready', 'perfect10' ),
		),
		array(
			'label' => __( 'Premium packages', 'perfect10' ),
			'hint'  => __( 'Advertiser placement tiers.', 'perfect10' ),
			'ok'    => (bool) p10_get_packages(),
			'value' => __( 'Ready', 'perfect10' ),
		),
		array(
			'label' => __( 'Utility pages', 'perfect10' ),
			'hint'  => __( 'Booking, register, packages, reviews and all-cities pages.', 'perfect10' ),
			'ok'    => (bool) p10_page_url( 'booking', '' ),
			'value' => __( 'Ready', 'perfect10' ),
		),
		array(
			'label' => __( 'Primary menu', 'perfect10' ),
			'hint'  => __( 'Optional — a dynamic fallback menu is used otherwise.', 'perfect10' ),
			'ok'    => has_nav_menu( 'primary' ),
			'value' => __( 'Assigned', 'perfect10' ),
		),
		array(
			'label' => __( 'Static front page', 'perfect10' ),
			'hint'  => __( 'Recommended so the landing layout is served at /.', 'perfect10' ),
			'ok'    => 'page' === $show_on && $front_page,
			'value' => $front_page ? get_the_title( $front_page ) : '',
		),
		array(
			'label' => __( 'Booking email', 'perfect10' ),
			'hint'  => __( 'Where enquiry notifications are sent.', 'perfect10' ),
			'ok'    => (bool) is_email( (string) p10_option( 'email', '' ) ),
			'value' => (string) p10_option( 'email', '' ),
		),
	);

	return apply_filters( 'p10_setup_checks', $checks );
}

add_action( 'admin_init', 'p10_handle_setup_actions' );

/**
 * Handle the buttons on the setup screen.
 */
function p10_handle_setup_actions() {
	if ( ! isset( $_POST['p10_import_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['p10_import_nonce'] ), 'p10_run_import' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['p10_do_flush'] ) ) {
		p10_add_nested_rules();
		flush_rewrite_rules();
		set_transient( 'p10_import_log', __( 'Permalinks flushed.', 'perfect10' ), 60 );
		wp_safe_redirect( add_query_arg( 'p10_import', 'flush' ) );
		exit;
	}

	if ( isset( $_POST['p10_do_import'] ) ) {
		$scope   = 'locations' === $_POST['p10_do_import'] ? 'locations' : 'all';
		$log     = p10_run_demo_import( $scope );
		set_transient( 'p10_import_log', implode( "\n", $log ), 5 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'p10_import', $scope ) );
		exit;
	}
}

/**
 * Quick-edit prefill script.
 */
function p10_admin_inline_script() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-p10_profile' !== $screen->id ) {
		return;
	}
	?>
	<script>
	(function () {
		var node = document.getElementById('p10-quick-edit-data');
		if (!node) { return; }
		var data;
		try { data = JSON.parse(node.textContent); } catch (e) { return; }

		var original = window.inlineEditPost && window.inlineEditPost.edit;
		if (!original) { return; }

		window.inlineEditPost.edit = function (id) {
			original.apply(this, arguments);
			var postId = typeof id === 'object' ? parseInt(this.getId(id), 10) : parseInt(id, 10);
			var row = data[postId];
			if (!row) { return; }

			var form = document.getElementById('edit-' + postId);
			if (!form) { return; }

			['age', 'rate', 'premium', 'verified', 'available'].forEach(function (key) {
				var field = form.querySelector('[name="p10_' + key + '"]');
				if (field && row[key] !== undefined && row[key] !== '') {
					field.value = row[key];
				}
			});
		};
	})();
	</script>
	<?php
}
add_action( 'admin_footer', 'p10_admin_inline_script', 20 );

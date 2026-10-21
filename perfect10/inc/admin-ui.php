<?php
/**
 * Perfect 10 administration interface.
 *
 * One coherent screen with tabs for the dashboard, demo import, Elementor,
 * directory, forms/enquiries and system diagnostics. All actions are
 * capability- and nonce-protected. Heavy work (import, homepage build) runs
 * over AJAX one step at a time so shared hosts do not time out.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the admin sub-pages.
 */
function p10_admin_ui_menu() {
	$pages = array(
		'p10-demo'      => array(
			'title' => __( 'Demo Import', 'perfect10' ),
			'tab'   => 'demo',
		),
		'p10-elementor' => array(
			'title' => __( 'Elementor', 'perfect10' ),
			'tab'   => 'elementor',
		),
		'p10-directory' => array(
			'title' => __( 'Directory', 'perfect10' ),
			'tab'   => 'directory',
		),
		'p10-forms'     => array(
			'title' => __( 'Forms & Enquiries', 'perfect10' ),
			'tab'   => 'forms',
		),
		'p10-system'    => array(
			'title' => __( 'System Status', 'perfect10' ),
			'tab'   => 'system',
		),
	);

	foreach ( $pages as $slug => $page ) {
		add_submenu_page(
			'p10-setup',
			$page['title'] . ' — Perfect 10',
			$page['title'],
			'manage_options',
			$slug,
			static function () use ( $page ) {
				p10_render_admin_screen( $page['tab'] );
			}
		);
	}
}
add_action( 'admin_menu', 'p10_admin_ui_menu', 11 );

/**
 * Admin screen tabs.
 *
 * @return array
 */
function p10_admin_tabs() {
	return array(
		'overview'  => array(
			'label' => __( 'Dashboard', 'perfect10' ),
			'url'   => admin_url( 'admin.php?page=p10-setup' ),
		),
		'demo'      => array(
			'label' => __( 'Demo Import', 'perfect10' ),
			'url'   => admin_url( 'admin.php?page=p10-demo' ),
		),
		'elementor' => array(
			'label' => __( 'Elementor', 'perfect10' ),
			'url'   => admin_url( 'admin.php?page=p10-elementor' ),
		),
		'directory' => array(
			'label' => __( 'Directory', 'perfect10' ),
			'url'   => admin_url( 'admin.php?page=p10-directory' ),
		),
		'forms'     => array(
			'label' => __( 'Forms & Enquiries', 'perfect10' ),
			'url'   => admin_url( 'admin.php?page=p10-forms' ),
		),
		'system'    => array(
			'label' => __( 'System Status', 'perfect10' ),
			'url'   => admin_url( 'admin.php?page=p10-system' ),
		),
	);
}

/**
 * Count a post type quickly.
 *
 * @param string $type Post type.
 * @param string $status Status.
 * @return int
 */
function p10_admin_count_posts( $type, $status = 'publish' ) {
	$counts = wp_count_posts( $type );
	if ( is_object( $counts ) && isset( $counts->$status ) ) {
		return (int) $counts->$status;
	}
	return 0;
}

/**
 * Number of registered Perfect 10 widgets (when Elementor is active).
 *
 * @return int
 */
function p10_admin_widget_count() {
	if ( ! p10_elementor_active() || ! isset( \Elementor\Plugin::$instance->widgets_manager ) ) {
		return 0;
	}

	$count = 0;
	foreach ( \Elementor\Plugin::$instance->widgets_manager->get_widget_types() as $widget ) {
		if ( is_object( $widget ) && method_exists( $widget, 'get_categories' ) && in_array( 'perfect10', (array) $widget->get_categories(), true ) ) {
			$count++;
		}
	}
	return $count;
}

/**
 * Render the interface.
 *
 * @param string $active Active tab slug.
 */
function p10_render_admin_screen( $active = 'overview' ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'perfect10' ) );
	}

	$tabs     = p10_admin_tabs();
	$active   = isset( $tabs[ $active ] ) ? $active : 'overview';
	$status   = p10_demo_status();
	$checks   = p10_setup_checks();
	$home_id  = (int) $status['home_id'];
	$profiles = p10_admin_count_posts( 'p10_profile' );
	$enquiries_new = p10_admin_count_posts( 'p10_enquiry', 'publish' );
	$reviews  = p10_admin_count_posts( 'p10_review' );
	$cities   = count( p10_get_cities() );
	$counties = count( p10_get_counties() );
	?>
	<div class="wrap p10-admin">

		<header class="p10-admin-hero">
			<div class="p10-admin-hero__main">
				<p class="p10-admin-eyebrow"><?php esc_html_e( 'Perfect 10 · Las10Perfect.com', 'perfect10' ); ?></p>
				<h1><?php esc_html_e( 'Directory control room', 'perfect10' ); ?></h1>
				<p class="p10-admin-hero__lede">
					<?php esc_html_e( 'Profiles, locations, reviews, packages and enquiries stay in WordPress. Elementor edits the pages without owning the data.', 'perfect10' ); ?>
				</p>
				<div class="p10-admin-hero__actions">
					<?php if ( $home_id && $status['home_built'] ) : ?>
						<a class="button button-primary" href="<?php echo esc_url( $status['home_edit_url'] ); ?>"><?php esc_html_e( 'Edit homepage in Elementor', 'perfect10' ); ?></a>
					<?php else : ?>
						<a class="button button-primary" href="<?php echo esc_url( $tabs['demo']['url'] ); ?>"><?php esc_html_e( 'Set up the theme', 'perfect10' ); ?></a>
					<?php endif; ?>
					<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'perfect10' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customizer', 'perfect10' ); ?></a>
				</div>
			</div>
			<div class="p10-admin-hero__side">
				<span class="p10-admin-pill <?php echo p10_elementor_active() ? 'is-ok' : 'is-warn'; ?>">
					<?php echo p10_elementor_active() ? esc_html( sprintf( __( 'Elementor %s', 'perfect10' ), p10_elementor_version() ) ) : esc_html__( 'Elementor inactive', 'perfect10' ); ?>
				</span>
				<span class="p10-admin-pill <?php echo $profiles ? 'is-ok' : 'is-warn'; ?>">
					<?php echo esc_html( sprintf( _n( '%s profile', '%s profiles', $profiles, 'perfect10' ), number_format_i18n( $profiles ) ) ); ?>
				</span>
				<span class="p10-admin-pill <?php echo $cities ? 'is-ok' : 'is-warn'; ?>">
					<?php echo esc_html( sprintf( _n( '%s city', '%s cities', $cities, 'perfect10' ), number_format_i18n( $cities ) ) ); ?>
				</span>
				<span class="p10-admin-pill <?php echo $enquiries_new ? 'is-ok' : ''; ?>">
					<?php echo esc_html( sprintf( _n( '%s enquiry', '%s enquiries', $enquiries_new, 'perfect10' ), number_format_i18n( $enquiries_new ) ) ); ?>
				</span>
			</div>
		</header>

		<nav class="nav-tab-wrapper p10-admin-tabs" aria-label="<?php esc_attr_e( 'Perfect 10 admin sections', 'perfect10' ); ?>">
			<?php foreach ( $tabs as $slug => $tab ) : ?>
				<a class="nav-tab <?php echo $slug === $active ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $tab['url'] ); ?>" <?php echo $slug === $active ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php if ( ! p10_elementor_active() ) : ?>
			<div class="notice notice-info inline p10-admin-notice">
				<p>
					<strong><?php esc_html_e( 'Elementor Free is not active.', 'perfect10' ); ?></strong>
					<?php esc_html_e( 'The site is fully functional: the homepage and all directory pages use the theme’s PHP templates. Activate Elementor to edit the homepage and pages visually. Elementor Pro is never required.', 'perfect10' ); ?>
					<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install Elementor', 'perfect10' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<?php settings_errors( 'p10_admin' ); ?>

		<div class="p10-admin-body">
			<?php
			switch ( $active ) {
				case 'demo':
					p10_admin_tab_demo( $status );
					break;
				case 'elementor':
					p10_admin_tab_elementor( $status );
					break;
				case 'directory':
					p10_admin_tab_directory();
					break;
				case 'forms':
					p10_admin_tab_forms();
					break;
				case 'system':
					p10_admin_tab_system( $status, $checks );
					break;
				default:
					p10_admin_tab_overview( $status, $checks );
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Stat card.
 *
 * @param string $value Value.
 * @param string $label Label.
 * @param string $note  Small note.
 * @param string $url   Optional URL.
 */
function p10_admin_stat( $value, $label, $note = '', $url = '' ) {
	$tag = $url ? 'a' : 'div';
	printf( '<%1$s class="p10-admin-stat"%2$s>', $tag, $url ? ' href="' . esc_url( $url ) . '"' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<span class="p10-admin-stat__value">' . esc_html( $value ) . '</span>';
	echo '<span class="p10-admin-stat__label">' . esc_html( $label ) . '</span>';
	if ( $note ) {
		echo '<span class="p10-admin-stat__note">' . esc_html( $note ) . '</span>';
	}
	printf( '</%s>', $tag ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Overview tab.
 *
 * @param array $status Demo status.
 * @param array $checks Readiness checks.
 */
function p10_admin_tab_overview( $status, $checks ) {
	$home_id  = (int) $status['home_id'];
	$failed   = array_filter(
		$checks,
		static function ( $check ) {
			return empty( $check['ok'] );
		}
	);
	?>
	<div class="p10-admin-grid p10-admin-grid--stats">
		<?php
		p10_admin_stat( number_format_i18n( p10_admin_count_posts( 'p10_profile' ) ), __( 'Published profiles', 'perfect10' ), __( 'Live in the directory', 'perfect10' ), admin_url( 'edit.php?post_type=p10_profile' ) );
		p10_admin_stat( number_format_i18n( count( p10_get_cities() ) ), __( 'Cities & districts', 'perfect10' ), __( 'Searchable locations', 'perfect10' ), admin_url( 'edit-tags.php?taxonomy=p10_city&post_type=p10_profile' ) );
		p10_admin_stat( number_format_i18n( p10_admin_count_posts( 'p10_review' ) ), __( 'Guest reviews', 'perfect10' ), __( 'Sample reviews are labelled', 'perfect10' ), admin_url( 'edit.php?post_type=p10_review' ) );
		p10_admin_stat( number_format_i18n( p10_admin_count_posts( 'p10_enquiry' ) ), __( 'Enquiries', 'perfect10' ), __( 'Private, never public', 'perfect10' ), admin_url( 'edit.php?post_type=p10_enquiry' ) );
		?>
	</div>

	<div class="p10-admin-columns">
		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Launch checklist', 'perfect10' ); ?></h2>
				<span class="p10-admin-card__meta"><?php echo esc_html( sprintf( __( '%1$d of %2$d ready', 'perfect10' ), count( $checks ) - count( $failed ), count( $checks ) ) ); ?></span>
			</header>
			<ul class="p10-admin-checklist">
				<?php foreach ( $checks as $check ) : ?>
					<li class="<?php echo $check['ok'] ? 'is-done' : 'is-todo'; ?>">
						<span class="p10-admin-check" aria-hidden="true"><?php echo $check['ok'] ? '✓' : '○'; ?></span>
						<span>
							<strong><?php echo esc_html( $check['label'] ); ?></strong>
							<small><?php echo esc_html( $check['hint'] ); ?></small>
						</span>
						<?php if ( $check['ok'] ) : ?>
							<em><?php echo esc_html( $check['value'] ); ?></em>
						<?php else : ?>
							<a href="<?php echo esc_url( p10_admin_tabs()['demo']['url'] ); ?>"><?php esc_html_e( 'Fix', 'perfect10' ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<div class="p10-admin-stack">
			<section class="p10-admin-card">
				<header class="p10-admin-card__head">
					<h2><?php esc_html_e( 'Elementor homepage', 'perfect10' ); ?></h2>
				</header>
				<?php if ( $home_id && $status['home_built'] ) : ?>
					<p class="p10-admin-ok"><?php esc_html_e( 'The front page is a native Elementor document built from the Perfect 10 widget library. Every section is editable; the cards are live queries.', 'perfect10' ); ?></p>
					<p class="p10-admin-actions">
						<a class="button button-primary" href="<?php echo esc_url( $status['home_edit_url'] ); ?>"><?php esc_html_e( 'Edit with Elementor', 'perfect10' ); ?></a>
						<a class="button" href="<?php echo esc_url( $status['home_view_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'perfect10' ); ?></a>
					</p>
				<?php elseif ( p10_elementor_active() ) : ?>
					<p><?php esc_html_e( 'Elementor is active. Build the homepage to convert the current PHP landing page into editable sections. The PHP layout stays as an automatic fallback.', 'perfect10' ); ?></p>
					<p class="p10-admin-actions">
						<button type="button" class="button button-primary" data-p10-build-home><?php esc_html_e( 'Build Elementor homepage', 'perfect10' ); ?></button>
						<a class="button" href="<?php echo esc_url( p10_admin_tabs()['demo']['url'] ); ?>"><?php esc_html_e( 'Import content first', 'perfect10' ); ?></a>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'Activate free Elementor to unlock visual editing. Until then the homepage is rendered by front-page.php with the same design and live data.', 'perfect10' ); ?></p>
					<p class="p10-admin-actions">
						<a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install Elementor', 'perfect10' ); ?></a>
					</p>
				<?php endif; ?>
				<p class="p10-admin-feedback" data-p10-home-feedback role="status"></p>
			</section>

			<section class="p10-admin-card">
				<header class="p10-admin-card__head">
					<h2><?php esc_html_e( 'Design source of truth', 'perfect10' ); ?></h2>
				</header>
				<p><?php esc_html_e( 'Colours, fonts, section copy, phone and age-gate text live in the Customizer. Elementor widgets inherit those values by default, so editing the homepage never forks the brand.', 'perfect10' ); ?></p>
				<p class="p10-admin-actions">
					<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=p10_panel' ) ); ?>"><?php esc_html_e( 'Open Perfect 10 Customizer', 'perfect10' ); ?></a>
				</p>
			</section>
		</div>
	</div>

	<section class="p10-admin-card">
		<header class="p10-admin-card__head">
			<h2><?php esc_html_e( 'Quick links', 'perfect10' ); ?></h2>
		</header>
		<div class="p10-admin-quick">
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=p10_profile' ) ); ?>"><?php esc_html_e( 'Profiles', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=p10_package' ) ); ?>"><?php esc_html_e( 'Packages', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=p10_review' ) ); ?>"><?php esc_html_e( 'Reviews', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=p10_enquiry' ) ); ?>"><?php esc_html_e( 'Enquiries', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=p10_county&post_type=p10_profile' ) ); ?>"><?php esc_html_e( 'Counties', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=p10_city&post_type=p10_profile' ) ); ?>"><?php esc_html_e( 'Cities', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=p10_category&post_type=p10_profile' ) ); ?>"><?php esc_html_e( 'Profile types', 'perfect10' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menus', 'perfect10' ); ?></a>
		</div>
	</section>
	<?php
}

/**
 * Demo import tab.
 *
 * @param array $status Demo status.
 */
function p10_admin_tab_demo( $status ) {
	$state = $status['state'];
	$steps = p10_demo_steps();
	$done  = (array) $state['completed'];
	?>
	<div class="p10-admin-columns p10-admin-columns--wide">

		<section class="p10-admin-card p10-admin-card--import">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Demo content', 'perfect10' ); ?></h2>
				<span class="p10-admin-card__meta">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: complete steps, 2: total steps. */
							__( '%1$d / %2$d steps', 'perfect10' ),
							count( $done ),
							count( $steps )
						)
					);
					?>
				</span>
			</header>

			<p class="p10-admin-callout">
				<?php esc_html_e( 'Runs in small batches. It creates only missing records, never overwrites existing pages, menus or user profiles, and records everything it owns in a manifest so “Reset demo content” can remove exactly those records later.', 'perfect10' ); ?>
			</p>
			<p class="p10-admin-callout p10-admin-callout--warn">
				<strong><?php esc_html_e( 'Sample content is labelled.', 'perfect10' ); ?></strong>
				<?php esc_html_e( 'Demo profiles use fictional names and bundled placeholders. Demo reviews are marked “Sample content” and are excluded from structured data until an administrator adds a real, verified review.', 'perfect10' ); ?>
			</p>

			<div class="p10-import-progress" data-p10-progress aria-hidden="true">
				<span style="width:<?php echo esc_attr( (int) $status['progress'] ); ?>%"></span>
			</div>
			<p class="p10-import-progress__label" data-p10-progress-label>
				<?php echo esc_html( sprintf( __( '%d%% complete', 'perfect10' ), (int) $status['progress'] ) ); ?>
			</p>

			<ol class="p10-import-steps" data-p10-steps>
				<?php foreach ( $steps as $slug => $label ) : ?>
					<?php $is_done = in_array( $slug, $done, true ); ?>
					<li data-step="<?php echo esc_attr( $slug ); ?>" class="<?php echo $is_done ? 'is-done' : ''; ?>">
						<span class="p10-import-steps__mark" aria-hidden="true"><?php echo $is_done ? '✓' : '○'; ?></span>
						<span><?php echo esc_html( $label ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>

			<div class="p10-admin-actions">
				<button type="button" class="button button-primary button-hero" data-p10-import-start>
					<?php echo 'complete' === $state['status'] ? esc_html__( 'Run import again (safe)', 'perfect10' ) : esc_html__( 'Run / resume import', 'perfect10' ); ?>
				</button>
				<button type="button" class="button" data-p10-build-home <?php disabled( ! p10_elementor_active() ); ?>>
					<?php esc_html_e( 'Build / rebuild Elementor homepage', 'perfect10' ); ?>
				</button>
				<span class="spinner" data-p10-spinner></span>
			</div>
			<p class="p10-admin-feedback" data-p10-import-feedback role="status" aria-live="polite"></p>

			<div class="p10-import-log" data-p10-log aria-live="polite" aria-label="<?php esc_attr_e( 'Import log', 'perfect10' ); ?>">
				<?php if ( ! empty( $state['log'] ) ) : ?>
					<?php foreach ( array_slice( (array) $state['log'], -12 ) as $line ) : ?>
						<p class="is-<?php echo esc_attr( $line['level'] ); ?>"><time><?php echo esc_html( $line['time'] ); ?></time> <?php echo esc_html( $line['text'] ); ?></p>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="is-muted"><?php esc_html_e( 'The import log will appear here.', 'perfect10' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<div class="p10-admin-stack">
			<section class="p10-admin-card">
				<header class="p10-admin-card__head">
					<h2><?php esc_html_e( 'Homepage', 'perfect10' ); ?></h2>
				</header>
				<?php if ( $status['home_built'] ) : ?>
					<p class="p10-admin-ok"><?php esc_html_e( 'Elementor owns the homepage. The PHP layout remains the automatic fallback if it is deactivated.', 'perfect10' ); ?></p>
					<p class="p10-admin-actions">
						<a class="button" href="<?php echo esc_url( $status['home_edit_url'] ); ?>"><?php esc_html_e( 'Edit homepage', 'perfect10' ); ?></a>
						<a class="button" href="<?php echo esc_url( $status['home_view_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'perfect10' ); ?></a>
					</p>
				<?php elseif ( ! p10_elementor_active() ) : ?>
					<p><?php esc_html_e( 'The homepage currently uses the theme’s native PHP layout. Activate Elementor to convert it into editable sections.', 'perfect10' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'No Elementor document on the front page yet. Build it after the content steps so the widgets have real profiles and locations to query.', 'perfect10' ); ?></p>
				<?php endif; ?>
				<p class="p10-admin-feedback" data-p10-home-feedback role="status"></p>
			</section>

			<section class="p10-admin-card">
				<header class="p10-admin-card__head">
					<h2><?php esc_html_e( 'Importer manifest', 'perfect10' ); ?></h2>
				</header>
				<dl class="p10-admin-dl">
					<dt><?php esc_html_e( 'Owned posts & pages', 'perfect10' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $status['manifest']['posts'] ) ); ?></dd>
					<dt><?php esc_html_e( 'Owned terms', 'perfect10' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $status['manifest']['terms'] ) ); ?></dd>
					<dt><?php esc_html_e( 'Owned attachments', 'perfect10' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $status['manifest']['attachments'] ) ); ?></dd>
					<dt><?php esc_html_e( 'Menus', 'perfect10' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $status['manifest']['menus'] ) ); ?></dd>
					<dt><?php esc_html_e( 'Last completed', 'perfect10' ); ?></dt><dd><?php echo esc_html( $status['manifest']['completed_at'] ? $status['manifest']['completed_at'] : '—' ); ?></dd>
				</dl>
			</section>

			<section class="p10-admin-card p10-admin-card--danger">
				<header class="p10-admin-card__head">
					<h2><?php esc_html_e( 'Reset demo content', 'perfect10' ); ?></h2>
				</header>
				<p><?php esc_html_e( 'Destructive. Removes only posts, terms, attachments and menus listed in the manifest. Real profiles, pages or media you created are not touched. Back up the database before running this on a live site.', 'perfect10' ); ?></p>
				<label class="p10-admin-field">
					<span><?php esc_html_e( 'Type RESET DEMO CONTENT to confirm', 'perfect10' ); ?></span>
					<input type="text" data-p10-reset-input autocomplete="off" placeholder="RESET DEMO CONTENT">
				</label>
				<button type="button" class="button button-link-delete" data-p10-reset disabled><?php esc_html_e( 'Delete demo content', 'perfect10' ); ?></button>
				<p class="p10-admin-feedback" data-p10-reset-feedback role="status"></p>
			</section>
		</div>
	</div>
	<?php
}

/**
 * Elementor tab.
 *
 * @param array $status Demo status.
 */
function p10_admin_tab_elementor( $status ) {
	$active = p10_elementor_active();
	?>
	<div class="p10-admin-columns">
		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Elementor integration', 'perfect10' ); ?></h2>
				<span class="p10-admin-pill <?php echo $active ? 'is-ok' : 'is-warn'; ?>">
					<?php echo $active ? esc_html( p10_elementor_version() ) : esc_html__( 'Not active', 'perfect10' ); ?>
				</span>
			</header>

			<?php if ( ! $active ) : ?>
				<p><?php esc_html_e( 'Elementor Free is the only required page builder. The theme registers a “Perfect 10” widget category with dynamic directory widgets; Elementor Pro is optional and only adds the Theme Builder for headers, footers and archive templates.', 'perfect10' ); ?></p>
				<p class="p10-admin-actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install Elementor Free', 'perfect10' ); ?></a>
				</p>
				<p class="p10-admin-muted"><?php esc_html_e( 'Without Elementor the site works normally: front-page.php renders the hero, search, numbered sections, grids and reviews from live WordPress data.', 'perfect10' ); ?></p>
			<?php else : ?>
				<dl class="p10-admin-dl">
					<dt><?php esc_html_e( 'Version', 'perfect10' ); ?></dt><dd><?php echo esc_html( p10_elementor_version() ); ?></dd>
					<dt><?php esc_html_e( 'Perfect 10 widgets', 'perfect10' ); ?></dt><dd><?php echo esc_html( number_format_i18n( p10_admin_widget_count() ) ); ?></dd>
					<dt><?php esc_html_e( 'Widget category', 'perfect10' ); ?></dt><dd><code>perfect10</code></dd>
					<dt><?php esc_html_e( 'CPT support', 'perfect10' ); ?></dt><dd><?php echo esc_html( implode( ', ', (array) get_option( 'elementor_cpt_support', array() ) ) ); ?></dd>
					<dt><?php esc_html_e( 'Active kit', 'perfect10' ); ?></dt><dd><?php echo esc_html( p10_elementor_kit_id() ? '#' . p10_elementor_kit_id() : '—' ); ?></dd>
				</dl>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="p10-admin-inline-form">
					<input type="hidden" name="action" value="p10_admin_sync_kit">
					<?php wp_nonce_field( 'p10_admin_sync_kit' ); ?>
					<button class="button" type="submit"><?php esc_html_e( 'Resync colours & fonts to the Elementor kit', 'perfect10' ); ?></button>
				</form>
				<p class="p10-admin-muted"><?php esc_html_e( 'The kit receives the current Customizer colours and typefaces. Force-syncing overwrites the theme-generated system colours and typography; your own custom palette entries are left alone.', 'perfect10' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Homepage document', 'perfect10' ); ?></h2>
			</header>
			<?php if ( $status['home_built'] ) : ?>
				<p class="p10-admin-ok"><?php esc_html_e( 'Editable with Elementor.', 'perfect10' ); ?></p>
				<dl class="p10-admin-dl">
					<dt><?php esc_html_e( 'Page', 'perfect10' ); ?></dt><dd><?php echo esc_html( get_the_title( $status['home_id'] ) ); ?></dd>
					<dt><?php esc_html_e( 'Sections', 'perfect10' ); ?></dt><dd><?php echo esc_html( count( p10_demo_home_tree() ) ); ?></dd>
					<dt><?php esc_html_e( 'Owned by importer', 'perfect10' ); ?></dt><dd><?php echo $status['home_owned'] ? esc_html__( 'Yes — reset will remove it', 'perfect10' ) : esc_html__( 'No — reset will keep it', 'perfect10' ); ?></dd>
				</dl>
				<p class="p10-admin-actions">
					<a class="button button-primary" href="<?php echo esc_url( $status['home_edit_url'] ); ?>"><?php esc_html_e( 'Edit with Elementor', 'perfect10' ); ?></a>
					<a class="button" href="<?php echo esc_url( $status['home_view_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View page', 'perfect10' ); ?></a>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'The front page has no Elementor document. Until one is built, front-page.php renders the complete PHP homepage. This is intentional graceful degradation, not a broken page.', 'perfect10' ); ?></p>
				<p class="p10-admin-actions">
					<button type="button" class="button button-primary" data-p10-build-home <?php disabled( ! $active ); ?>><?php esc_html_e( 'Build Elementor homepage', 'perfect10' ); ?></button>
				</p>
			<?php endif; ?>
			<p class="p10-admin-feedback" data-p10-home-feedback role="status"></p>
		</section>
	</div>

	<section class="p10-admin-card">
		<header class="p10-admin-card__head">
			<h2><?php esc_html_e( 'Widget library', 'perfect10' ); ?></h2>
			<span class="p10-admin-card__meta"><?php esc_html_e( 'Appearance → Widgets is unrelated; find these in the Elementor editor', 'perfect10' ); ?></span>
		</header>
		<div class="p10-admin-widget-grid">
			<?php
			$widgets = array(
				__( 'Hero', 'perfect10' )          => __( 'Full homepage hero with image, overlay, split headline, CTAs, live statistics and scroll cue. Theme or per-page copy.', 'perfect10' ),
				__( 'Ticker', 'perfect10' )        => __( 'Scrolling marquee band with speed control.', 'perfect10' ),
				__( 'Search Panel', 'perfect10' )  => __( 'Cascading county → city lookup with quick type links and a no-JavaScript fallback.', 'perfect10' ),
				__( 'Profile Grid', 'perfect10' )  => __( 'Dynamic profile cards from counties, cities, types, nationalities, rate, availability and premium flags. Includes the 01 Featured and 02 Premium presets.', 'perfect10' ),
				__( 'Profile Types', 'perfect10' ) => __( 'Portrait tiles for each p10_category term.', 'perfect10' ),
				__( 'Destinations', 'perfect10' )  => __( 'City tiles with live profile counts, grouped by county.', 'perfect10' ),
				__( 'Reviews', 'perfect10' )       => __( 'Published guest reviews with the sample-content marker and a verification note.', 'perfect10' ),
				__( 'Packages', 'perfect10' )      => __( 'Dynamic premium package cards from p10_package.', 'perfect10' ),
				__( 'Call Band', 'perfect10' )     => __( 'Phone-led call to action with booking link.', 'perfect10' ),
				__( 'About Blocks', 'perfect10' )  => __( 'Two-column editorial blocks, or a per-page repeater.', 'perfect10' ),
				__( 'Advertisers Band', 'perfect10' ) => __( 'Recruitment band with two calls to action and image.', 'perfect10' ),
				__( 'Statistics', 'perfect10' )    => __( 'Live WordPress counts or custom statistics.', 'perfect10' ),
				__( 'Phone', 'perfect10' )         => __( 'Accessible tel: link or centred call band.', 'perfect10' ),
				__( 'Enquiry Form', 'perfect10' )  => __( 'Booking, listing, concierge and profile forms with the theme’s nonce, honeypot, throttle and privacy handling.', 'perfect10' ),
				__( 'Section Heading', 'perfect10' ) => __( 'Number, kicker, split display heading, lede and action link.', 'perfect10' ),
				__( 'Button', 'perfect10' )        => __( 'Gold, ghost or text-link button with size and alignment controls.', 'perfect10' ),
				__( 'Breadcrumb', 'perfect10' )    => __( 'Directory-aware breadcrumb trail.', 'perfect10' ),
				__( 'Page Header', 'perfect10' )   => __( 'Archive/page header with live term description, counts and breadcrumb.', 'perfect10' ),
				__( 'Profile Facts', 'perfect10' ) => __( 'Age, height, hair, eyes, dress and languages for the current profile.', 'perfect10' ),
				__( 'Profile Gallery', 'perfect10' ) => __( 'Media-library gallery bound to the current profile.', 'perfect10' ),
			);
			foreach ( $widgets as $name => $description ) :
				?>
				<article class="p10-admin-widget">
					<h3><?php echo esc_html( $name ); ?></h3>
					<p><?php echo esc_html( $description ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
		<p class="p10-admin-muted"><?php esc_html_e( 'Dynamic widgets only render published WordPress content. An editor preview may show the newest profile so layouts can be composed before a profile is chosen; the public page never substitutes a random profile.', 'perfect10' ); ?></p>
	</section>
	<?php
}

/**
 * Directory tab.
 */
function p10_admin_tab_directory() {
	$counties = p10_get_counties();
	$types    = p10_get_categories();
	?>
	<div class="p10-admin-columns">
		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Counties', 'perfect10' ); ?></h2>
				<a class="button button-small" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=p10_county&post_type=p10_profile' ) ); ?>"><?php esc_html_e( 'Manage', 'perfect10' ); ?></a>
			</header>
			<?php if ( $counties ) : ?>
				<table class="widefat striped p10-admin-table">
					<thead><tr><th><?php esc_html_e( 'County', 'perfect10' ); ?></th><th><?php esc_html_e( 'Cities', 'perfect10' ); ?></th><th><?php esc_html_e( 'Profiles', 'perfect10' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $counties as $county ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_term_link( $county ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $county->name ); ?></a></td>
								<td><?php echo esc_html( number_format_i18n( count( p10_get_cities( $county->slug ) ) ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( p10_count_profiles( $county ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No counties yet. Run the demo importer or create them manually.', 'perfect10' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Profile types', 'perfect10' ); ?></h2>
				<a class="button button-small" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=p10_category&post_type=p10_profile' ) ); ?>"><?php esc_html_e( 'Manage', 'perfect10' ); ?></a>
			</header>
			<?php if ( $types ) : ?>
				<table class="widefat striped p10-admin-table">
					<thead><tr><th><?php esc_html_e( 'Type', 'perfect10' ); ?></th><th><?php esc_html_e( 'Profiles', 'perfect10' ); ?></th><th><?php esc_html_e( 'URL', 'perfect10' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $types as $type ) : ?>
							<tr>
								<td><?php echo esc_html( $type->name ); ?></td>
								<td><?php echo esc_html( number_format_i18n( $type->count ) ); ?></td>
								<td><a href="<?php echo esc_url( get_term_link( $type ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View', 'perfect10' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No profile types yet.', 'perfect10' ); ?></p>
			<?php endif; ?>
		</section>
	</div>

	<section class="p10-admin-card">
		<header class="p10-admin-card__head">
			<h2><?php esc_html_e( 'URL structure', 'perfect10' ); ?></h2>
		</header>
		<p><?php esc_html_e( 'The directory uses nested, crawlable URLs. They are generated from the county and city taxonomy relationships; no manual redirects are required.', 'perfect10' ); ?></p>
		<dl class="p10-admin-dl">
			<dt><?php esc_html_e( 'Profile', 'perfect10' ); ?></dt><dd><code>/county/city/profile/</code> · <code>/county/profile/</code> · <code>/profiles/profile/</code></dd>
			<dt><?php esc_html_e( 'City', 'perfect10' ); ?></dt><dd><code>/county/city/</code></dd>
			<dt><?php esc_html_e( 'County', 'perfect10' ); ?></dt><dd><code>/county/</code></dd>
			<dt><?php esc_html_e( 'Profile type', 'perfect10' ); ?></dt><dd><code>/browse/type/</code></dd>
			<dt><?php esc_html_e( 'Nationality', 'perfect10' ); ?></dt><dd><code>/nationality/nationality/</code></dd>
		</dl>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="p10-admin-inline-form">
			<input type="hidden" name="action" value="p10_admin_flush">
			<?php wp_nonce_field( 'p10_admin_flush' ); ?>
			<button class="button" type="submit"><?php esc_html_e( 'Flush permalinks', 'perfect10' ); ?></button>
		</form>
	</section>
	<?php
}

/**
 * Forms and enquiries tab.
 */
function p10_admin_tab_forms() {
	$recipient = p10_form_recipient();
	$statuses  = function_exists( 'p10_enquiry_statuses' ) ? p10_enquiry_statuses() : array( 'new' => __( 'New', 'perfect10' ) );
	$counts    = array();
	foreach ( $statuses as $slug => $label ) {
		$counts[ $slug ] = 0;
	}
	$enquiries = get_posts(
		array(
			'post_type'      => 'p10_enquiry',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $enquiries as $enquiry_id ) {
		$status = (string) get_post_meta( $enquiry_id, 'p10_enquiry_status', true );
		if ( isset( $counts[ $status ] ) ) {
			$counts[ $status ]++;
		}
	}
	?>
	<div class="p10-admin-columns">
		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Enquiry notifications', 'perfect10' ); ?></h2>
			</header>
			<p><?php esc_html_e( 'Enquiries are stored privately in WordPress and are never exposed on the front end, in the REST API, sitemaps or search.', 'perfect10' ); ?></p>
			<dl class="p10-admin-dl">
				<dt><?php esc_html_e( 'Notification recipient', 'perfect10' ); ?></dt><dd><?php echo esc_html( $recipient ); ?></dd>
				<dt><?php esc_html_e( 'Delivery', 'perfect10' ); ?></dt><dd><?php echo esc_html__( 'WordPress wp_mail() / your SMTP plugin. The theme cannot verify delivery.', 'perfect10' ); ?></dd>
				<dt><?php esc_html_e( 'Privacy note', 'perfect10' ); ?></dt><dd><?php echo esc_html( p10_form_privacy_text() ? p10_form_privacy_text() : __( 'Not configured — add consent copy under Customizer → Forms.', 'perfect10' ) ); ?></dd>
			</dl>
			<?php if ( false !== strpos( $recipient, 'example.com' ) ) : ?>
				<p class="p10-admin-warning"><?php esc_html_e( 'The recipient still uses example.com. Set a real booking inbox before taking enquiries.', 'perfect10' ); ?></p>
			<?php endif; ?>
			<p class="p10-admin-actions">
				<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=p10_forms' ) ); ?>"><?php esc_html_e( 'Edit form copy', 'perfect10' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=p10_enquiry' ) ); ?>"><?php esc_html_e( 'Open enquiries', 'perfect10' ); ?></a>
			</p>
		</section>

		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Enquiry status', 'perfect10' ); ?></h2>
			</header>
			<ul class="p10-admin-bars">
				<?php foreach ( $statuses as $slug => $label ) : ?>
					<li>
						<span><?php echo esc_html( $label ); ?></span>
						<strong><?php echo esc_html( number_format_i18n( $counts[ $slug ] ) ); ?></strong>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="p10-admin-muted"><?php esc_html_e( 'Spam protection: honeypot, per-IP throttling, nonce validation and a server-side field schema. No third-party CAPTCHA is required, though your spam plugin may add one.', 'perfect10' ); ?></p>
		</section>
	</div>

	<section class="p10-admin-card">
		<header class="p10-admin-card__head">
			<h2><?php esc_html_e( 'Forms included', 'perfect10' ); ?></h2>
		</header>
		<table class="widefat striped p10-admin-table">
			<thead><tr><th><?php esc_html_e( 'Form', 'perfect10' ); ?></th><th><?php esc_html_e( 'Shortcode', 'perfect10' ); ?></th><th><?php esc_html_e( 'Purpose', 'perfect10' ); ?></th></tr></thead>
			<tbody>
				<tr><td><?php esc_html_e( 'Reservation', 'perfect10' ); ?></td><td><code>[p10_form type="booking"]</code></td><td><?php esc_html_e( 'Date, time, duration and preferred profile.', 'perfect10' ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Listing application', 'perfect10' ); ?></td><td><code>[p10_form type="register"]</code></td><td><?php esc_html_e( 'Independent advertiser application.', 'perfect10' ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Concierge', 'perfect10' ); ?></td><td><code>[p10_form type="concierge"]</code></td><td><?php esc_html_e( 'General enquiry from the assistant or contact page.', 'perfect10' ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Profile enquiry', 'perfect10' ); ?></td><td><code>[p10_form type="profile"]</code></td><td><?php esc_html_e( 'Bound to the current profile; never exposes the profile’s private contact data.', 'perfect10' ); ?></td></tr>
			</tbody>
		</table>
	</section>
	<?php
}

/**
 * System status tab.
 *
 * @param array $status Demo status.
 * @param array $checks Readiness checks.
 */
function p10_admin_tab_system( $status, $checks ) {
	$uploads = wp_get_upload_dir();
	?>
	<div class="p10-admin-columns">
		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Environment', 'perfect10' ); ?></h2>
			</header>
			<dl class="p10-admin-dl">
				<dt><?php esc_html_e( 'WordPress', 'perfect10' ); ?></dt><dd><?php echo esc_html( get_bloginfo( 'version' ) ); ?></dd>
				<dt><?php esc_html_e( 'PHP', 'perfect10' ); ?></dt><dd><?php echo esc_html( PHP_VERSION ); ?></dd>
				<dt><?php esc_html_e( 'Theme', 'perfect10' ); ?></dt><dd><?php echo esc_html( wp_get_theme()->get( 'Name' ) . ' ' . P10_VERSION ); ?></dd>
				<dt><?php esc_html_e( 'Elementor', 'perfect10' ); ?></dt><dd><?php echo esc_html( p10_elementor_active() ? p10_elementor_version() : __( 'Not active (theme fallback in use)', 'perfect10' ) ); ?></dd>
				<dt><?php esc_html_e( 'Elementor Pro', 'perfect10' ); ?></dt><dd><?php echo esc_html( defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : __( 'Not installed (optional)', 'perfect10' ) ); ?></dd>
				<dt><?php esc_html_e( 'Permalinks', 'perfect10' ); ?></dt><dd><code><?php echo esc_html( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Plain (nested URLs disabled)', 'perfect10' ) ); ?></code></dd>
				<dt><?php esc_html_e( 'Front page', 'perfect10' ); ?></dt><dd><?php echo esc_html( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ? get_the_title( (int) get_option( 'page_on_front' ) ) : __( 'Latest posts', 'perfect10' ) ); ?></dd>
				<dt><?php esc_html_e( 'Uploads writable', 'perfect10' ); ?></dt><dd><?php echo esc_html( wp_is_writable( $uploads['basedir'] ) ? __( 'Yes', 'perfect10' ) : __( 'No — media imports will fail', 'perfect10' ) ); ?></dd>
				<dt><?php esc_html_e( 'Debug mode', 'perfect10' ); ?></dt><dd><?php echo esc_html( defined( 'WP_DEBUG' ) && WP_DEBUG ? __( 'On', 'perfect10' ) : __( 'Off', 'perfect10' ) ); ?></dd>
			</dl>
			<p class="p10-admin-actions">
				<a class="button" href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'WordPress Site Health', 'perfect10' ); ?></a>
			</p>
		</section>

		<section class="p10-admin-card">
			<header class="p10-admin-card__head">
				<h2><?php esc_html_e( 'Readiness', 'perfect10' ); ?></h2>
			</header>
			<ul class="p10-admin-checklist p10-admin-checklist--compact">
				<?php foreach ( $checks as $check ) : ?>
					<li class="<?php echo $check['ok'] ? 'is-done' : 'is-todo'; ?>">
						<span class="p10-admin-check" aria-hidden="true"><?php echo $check['ok'] ? '✓' : '○'; ?></span>
						<span><strong><?php echo esc_html( $check['label'] ); ?></strong></span>
						<em><?php echo esc_html( $check['ok'] ? $check['value'] : __( 'Needs attention', 'perfect10' ) ); ?></em>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	</div>

	<section class="p10-admin-card">
		<header class="p10-admin-card__head">
			<h2><?php esc_html_e( 'Maintenance', 'perfect10' ); ?></h2>
			<span class="p10-admin-card__meta"><?php esc_html_e( 'Safe, repeatable operations', 'perfect10' ); ?></span>
		</header>
		<div class="p10-admin-maintenance">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="p10_admin_flush">
				<?php wp_nonce_field( 'p10_admin_flush' ); ?>
				<button class="button" type="submit"><?php esc_html_e( 'Flush permalinks', 'perfect10' ); ?></button>
				<p><?php esc_html_e( 'Rebuilds the county/city/profile rewrite rules. Does not alter content.', 'perfect10' ); ?></p>
			</form>
			<?php if ( p10_elementor_active() ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="p10_admin_sync_kit">
					<?php wp_nonce_field( 'p10_admin_sync_kit' ); ?>
					<button class="button" type="submit"><?php esc_html_e( 'Resync Elementor kit', 'perfect10' ); ?></button>
					<p><?php esc_html_e( 'Pushes the current Customizer colours and fonts into the active Elementor kit.', 'perfect10' ); ?></p>
				</form>
			<?php endif; ?>
			<div>
				<a class="button" href="<?php echo esc_url( admin_url( 'export.php' ) ); ?>"><?php esc_html_e( 'Export WordPress data', 'perfect10' ); ?></a>
				<p><?php esc_html_e( 'Take a database export before a demo reset or a major content change. The importer manifest lists everything it owns.', 'perfect10' ); ?></p>
			</div>
		</div>
	</section>

	<section class="p10-admin-card">
		<header class="p10-admin-card__head">
			<h2><?php esc_html_e( 'Importer state', 'perfect10' ); ?></h2>
		</header>
		<pre class="p10-admin-code"><?php echo esc_html( wp_json_encode( array( 'state' => $status['state'], 'manifest' => $status['manifest'] ), JSON_PRETTY_PRINT ) ); ?></pre>
	</section>
	<?php
}

/* -------------------------------------------------------------------------
 * Form handlers
 * ---------------------------------------------------------------------- */

/**
 * Flush permalinks from the admin.
 */
function p10_admin_handle_flush() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'p10_admin_flush' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'perfect10' ) );
	}
	p10_add_nested_rules();
	flush_rewrite_rules();
	add_settings_error( 'p10_admin', 'flushed', __( 'Permalinks rebuilt.', 'perfect10' ), 'updated' );
	set_transient( 'settings_errors', get_settings_errors( 'p10_admin' ), 30 );
	wp_safe_redirect( add_query_arg( 'p10_flushed', '1', admin_url( 'admin.php?page=p10-system' ) ) );
	exit;
}
add_action( 'admin_post_p10_admin_flush', 'p10_admin_handle_flush' );

/**
 * Resync the Elementor kit.
 */
function p10_admin_handle_sync_kit() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'p10_admin_sync_kit' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'perfect10' ) );
	}
	$done = p10_sync_elementor_kit( true );
	add_settings_error( 'p10_admin', 'kit', $done ? __( 'Elementor kit colours and typography resynced.', 'perfect10' ) : __( 'Elementor is not active or has no kit, so nothing was changed.', 'perfect10' ), $done ? 'updated' : 'warning' );
	set_transient( 'settings_errors', get_settings_errors( 'p10_admin' ), 30 );
	wp_safe_redirect( admin_url( 'admin.php?page=p10-elementor' ) );
	exit;
}
add_action( 'admin_post_p10_admin_sync_kit', 'p10_admin_handle_sync_kit' );

/**
 * No-JavaScript reset fallback.
 */
function p10_admin_handle_reset() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'p10_admin_reset' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'perfect10' ) );
	}
	$confirm = isset( $_POST['confirm'] ) ? strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['confirm'] ) ) ) ) : '';
	if ( 'RESET DEMO CONTENT' !== $confirm ) {
		wp_die( esc_html__( 'Confirmation phrase did not match. No content was removed.', 'perfect10' ) );
	}
	$counts = p10_demo_reset();
	delete_option( P10_DEMO_STATE_OPTION );
	flush_rewrite_rules();
	add_settings_error(
		'p10_admin',
		'reset',
		sprintf(
			/* translators: 1: posts, 2: terms. */
			__( 'Demo content removed: %1$d posts/pages and %2$d terms.', 'perfect10' ),
			$counts['posts'],
			$counts['terms']
		),
		'updated'
	);
	set_transient( 'settings_errors', get_settings_errors( 'p10_admin' ), 30 );
	wp_safe_redirect( admin_url( 'admin.php?page=p10-demo' ) );
	exit;
}
add_action( 'admin_post_p10_admin_reset', 'p10_admin_handle_reset' );

/**
 * Show admin notices saved by the handlers.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! isset( $_GET['p10_flushed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Permalinks rebuilt.', 'perfect10' ) . '</p></div>';
	}
);

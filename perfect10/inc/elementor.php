<?php
/**
 * Elementor integration.
 *
 * Design rules honoured here:
 * - Elementor Free is the baseline. Nothing in this file requires Elementor Pro.
 * - No Elementor plugin file is modified and no saved document is rewritten
 *   unless the administrator explicitly builds the demo homepage.
 * - PHP templates remain the renderer for any page that has not been built
 *   with Elementor, so the theme degrades cleanly when Elementor is inactive.
 * - Elementor Canvas and Elementor Full Width keep their native behaviour; the
 *   theme never adds a second header, footer or content wrapper around them.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is Elementor loaded?
 *
 * The helper lives in inc/helpers.php so templates can use it before the
 * Elementor plugin has initialised.
 */
if ( ! function_exists( 'p10_elementor_active' ) ) {
	/**
	 * Fallback guard for unusual load orders.
	 *
	 * @return bool
	 */
	function p10_elementor_active() {
		return did_action( 'elementor/loaded' ) > 0 && class_exists( '\Elementor\Plugin' );
	}
}

/**
 * Was this document built with Elementor?
 *
 * Kept as a thin wrapper for backward compatibility. The strict check lives in
 * p10_is_built_with_elementor().
 *
 * @param int $id Post ID.
 * @return bool
 */
function p10_elementor_document( $id = 0 ) {
	return p10_is_built_with_elementor( $id ? $id : get_queried_object_id() );
}

/**
 * Render an Elementor theme-builder location when a provider exists.
 *
 * @param string $location Location slug.
 * @return bool
 */
function p10_elementor_location( $location ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) ) {
		return false;
	}
	return (bool) elementor_theme_do_location( $location );
}

/**
 * Register the Perfect 10 widget category.
 *
 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
 */
function p10_register_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'perfect10',
		array(
			'title' => __( 'Perfect 10', 'perfect10' ),
			'icon'  => 'fa fa-diamond',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'p10_register_elementor_category' );

/**
 * Load all widget classes.
 */
function p10_load_elementor_widgets() {
	$files = array(
		'class-p10-elementor-widget.php',
		'class-p10-elementor-hero.php',
		'class-p10-elementor-marquee.php',
		'class-p10-elementor-search.php',
		'class-p10-elementor-profiles.php',
		'class-p10-elementor-types.php',
		'class-p10-elementor-cities.php',
		'class-p10-elementor-reviews.php',
		'class-p10-elementor-packages.php',
		'class-p10-elementor-callband.php',
		'class-p10-elementor-about.php',
		'class-p10-elementor-advertisers.php',
		'class-p10-elementor-stats.php',
		'class-p10-elementor-phone.php',
		'class-p10-elementor-form.php',
		'class-p10-elementor-heading.php',
		'class-p10-elementor-button.php',
		'class-p10-elementor-breadcrumb.php',
		'class-p10-elementor-page-head.php',
		'class-p10-elementor-facts.php',
		'class-p10-elementor-gallery.php',
	);

	foreach ( $files as $file ) {
		$path = P10_DIR . 'inc/elementor/' . $file;
		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
}
add_action( 'elementor/loaded', 'p10_load_elementor_widgets', 5 );

/**
 * Register every widget with Elementor.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
 */
function p10_register_elementor_widgets( $widgets_manager ) {
	p10_load_elementor_widgets();

	$widgets = array(
		'P10_Elementor_Hero',
		'P10_Elementor_Marquee',
		'P10_Elementor_Search',
		'P10_Elementor_Profiles',
		'P10_Elementor_Types',
		'P10_Elementor_Cities',
		'P10_Elementor_Reviews',
		'P10_Elementor_Packages',
		'P10_Elementor_Callband',
		'P10_Elementor_About',
		'P10_Elementor_Advertisers',
		'P10_Elementor_Stats',
		'P10_Elementor_Phone',
		'P10_Elementor_Form',
		'P10_Elementor_Heading',
		'P10_Elementor_Button',
		'P10_Elementor_Breadcrumb',
		'P10_Elementor_Page_Head',
		'P10_Elementor_Facts',
		'P10_Elementor_Gallery',
	);

	foreach ( $widgets as $class ) {
		if ( class_exists( $class ) ) {
			$widgets_manager->register( new $class() );
		}
	}
}
add_action( 'elementor/widgets/register', 'p10_register_elementor_widgets' );

/**
 * Make the Perfect 10 post types available in Elementor.
 *
 * The option is updated as a union so the site owner's existing choices are
 * never removed.
 */
function p10_elementor_supported_post_types() {
	return array( 'page', 'post', 'p10_profile', 'p10_review', 'p10_package' );
}

add_action(
	'init',
	function () {
		foreach ( array( 'p10_profile', 'p10_review', 'p10_package' ) as $type ) {
			add_post_type_support( $type, 'elementor' );
		}
	},
	20
);

/**
 * Append the directory post types to Elementor's allowed list.
 *
 * @param array $types Allowed post types.
 * @return array
 */
function p10_elementor_allowed_post_types( $types ) {
	if ( ! is_array( $types ) ) {
		$types = array();
	}
	return array_values( array_unique( array_merge( $types, p10_elementor_supported_post_types() ) ) );
}
add_filter( 'elementor/utils/get_allowed_post_types', 'p10_elementor_allowed_post_types' );
add_filter( 'elementor_cpt_support', 'p10_elementor_allowed_post_types' );

/**
 * Persist Elementor CPT support without dropping the site's own choices.
 */
function p10_elementor_enable_cpt_support() {
	if ( ! p10_elementor_active() ) {
		return;
	}
	$current = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
	$current = is_array( $current ) ? $current : array();
	$next    = p10_elementor_allowed_post_types( $current );
	if ( $next !== $current ) {
		update_option( 'elementor_cpt_support', $next );
	}
}
add_action( 'after_switch_theme', 'p10_elementor_enable_cpt_support' );
add_action( 'elementor/loaded', 'p10_elementor_enable_cpt_support', 20 );

/**
 * Register theme-builder locations when Elementor Pro provides the manager.
 *
 * Free Elementor does not run this hook, and every call is guarded, so the
 * theme also works with Pro absent.
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Locations manager.
 */
function p10_register_elementor_locations( $manager ) {
	if ( ! is_object( $manager ) ) {
		return;
	}

	if ( method_exists( $manager, 'register_all_core_location' ) ) {
		$manager->register_all_core_location();
	} elseif ( method_exists( $manager, 'register_core_location' ) ) {
		foreach ( array( 'header', 'footer', 'single', 'archive' ) as $core ) {
			$manager->register_core_location( $core );
		}
	}

	if ( method_exists( $manager, 'register_location' ) ) {
		$manager->register_location(
			'p10_search',
			array(
				'label'    => __( 'Perfect 10 Search Results', 'perfect10' ),
				'multiple' => false,
			)
		);
		$manager->register_location(
			'p10_404',
			array(
				'label'    => __( 'Perfect 10 404', 'perfect10' ),
				'multiple' => false,
			)
		);
		$manager->register_location(
			'p10_location_archive',
			array(
				'label'    => __( 'Perfect 10 — County / city archive', 'perfect10' ),
				'multiple' => false,
			)
		);
		$manager->register_location(
			'p10_type_archive',
			array(
				'label'    => __( 'Perfect 10 — Profile type archive', 'perfect10' ),
				'multiple' => false,
			)
		);
		$manager->register_location(
			'p10_profile_single',
			array(
				'label'    => __( 'Perfect 10 — Single profile', 'perfect10' ),
				'multiple' => false,
			)
		);
		$manager->register_location(
			'p10_gallery',
			array(
				'label'    => __( 'Perfect 10 — Gallery archive', 'perfect10' ),
				'multiple' => true,
			)
		);
		$manager->register_location(
			'p10_homepage',
			array(
				'label'    => __( 'Perfect 10 — Homepage sections', 'perfect10' ),
				'multiple' => false,
			)
		);
		$manager->register_location(
			'p10_page',
			array(
				'label'    => __( 'Perfect 10 — Static page', 'perfect10' ),
				'multiple' => true,
			)
		);
		$manager->register_location(
			'p10_county_archive',
			array(
				'label'    => __( 'Perfect 10 — County archive', 'perfect10' ),
				'multiple' => true,
			)
		);
		$manager->register_location(
			'p10_city_archive',
			array(
				'label'    => __( 'Perfect 10 — City / district archive', 'perfect10' ),
				'multiple' => true,
			)
		);
		$manager->register_location(
			'p10_nationality_archive',
			array(
				'label'    => __( 'Perfect 10 — Nationality archive', 'perfect10' ),
				'multiple' => true,
			)
		);
	}
}
add_action( 'elementor/theme/register_locations', 'p10_register_elementor_locations' );

/**
 * Sync theme design tokens into the Elementor kit.
 */
add_action(
	'after_switch_theme',
	function () {
		if ( p10_elementor_active() ) {
			p10_sync_elementor_kit();
		}
	}
);

add_action(
	'customize_save_after',
	function () {
		if ( p10_elementor_active() ) {
			// Do not overwrite an administrator-edited kit on every save.
			p10_sync_elementor_kit( false );
		}
	}
);

/**
 * Keep the theme's design tokens available in the Elementor editor.
 */
add_action(
	'elementor/editor/after_enqueue_styles',
	function () {
		wp_enqueue_style( 'p10-main', P10_URI . 'assets/css/main.css', array(), P10_VERSION );
		wp_enqueue_style( 'p10-widgets', P10_URI . 'assets/css/widgets.css', array( 'p10-main' ), P10_VERSION );
		wp_enqueue_style( 'p10-editor', P10_URI . 'assets/css/editor.css', array( 'p10-main' ), P10_VERSION );
	},
	20
);

/**
 * Render an Elementor document inside the theme's header and footer.
 *
 * Used by page-templates/elementor-full-width.php and the theme builder bridge.
 *
 * @param int $post_id Document ID.
 */
function p10_render_elementor_document( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$GLOBALS['p10_elementor_document_id'] = $post_id;

	if ( ! p10_elementor_active() ) {
		the_content();
		return;
	}

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->documents ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
		if ( $document && $document->is_built_with_elementor() ) {
			echo $document->get_content(); // phpcs:ignore WordPress.Security.EscapeOutput -- Elementor renderer output.
			return;
		}
	}

	the_content();
}

/**
 * Preserve the active theme's templates unless Elementor explicitly owns the
 * document, and route theme-builder requests through the bridge template.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function p10_elementor_template_include( $template ) {
	if ( ! p10_elementor_active() ) {
		return $template;
	}

	if ( is_singular() ) {
		$slug = get_page_template_slug();

		// Native Elementor templates are left completely alone.
		if ( in_array( $slug, array( 'elementor_canvas', 'elementor_header_footer' ), true ) ) {
			return $template;
		}

		// An explicitly assigned theme template always wins for non-Elementor
		// documents; for Elementor documents it keeps working as a wrapper.
		if ( $slug && 'default' !== $slug && ! p10_is_built_with_elementor( get_queried_object_id() ) ) {
			return $template;
		}

		if ( p10_is_built_with_elementor( get_queried_object_id() ) && ! is_front_page() ) {
			return locate_template( 'page-templates/elementor-full-width.php' ) ?: $template;
		}
	}

	// Theme-builder locations for archives, search, 404 and non-Elementor
	// single views. Only used when Elementor Pro provides a location.
	if ( function_exists( 'elementor_theme_do_location' ) ) {
		$location = '';
		$fallback = '';
		if ( is_404() ) {
			$location = array( 'p10_404' );
			$fallback = 'archive';
		} elseif ( is_search() ) {
			$location = array( 'p10_search' );
			$fallback = 'archive';
		} elseif ( is_front_page() ) {
			// A dedicated homepage location lets the whole front page be
			// replaced without touching every other archive template.
			$location = array( 'p10_homepage', 'single' );
			$fallback = '';
		} elseif ( is_tax( 'p10_county' ) ) {
			$location = array( 'p10_county_archive', 'p10_location_archive' );
			$fallback = 'archive';
		} elseif ( is_tax( 'p10_city' ) ) {
			$location = array( 'p10_city_archive', 'p10_location_archive' );
			$fallback = 'archive';
		} elseif ( is_tax( 'p10_nationality' ) ) {
			$location = array( 'p10_nationality_archive', 'p10_type_archive' );
			$fallback = 'archive';
		} elseif ( is_tax( 'p10_category' ) ) {
			$location = array( 'p10_type_archive' );
			$fallback = 'archive';
		} elseif ( is_singular( 'p10_profile' ) && ! p10_is_built_with_elementor( get_queried_object_id() ) ) {
			$location = array( 'p10_profile_single' );
			$fallback = 'single';
		} elseif ( is_page() ) {
			$location = array( 'p10_page' );
			$fallback = 'single';
		} elseif ( is_archive() || is_home() ) {
			$location = array( 'archive' );
		} elseif ( is_singular() && ! p10_is_built_with_elementor( get_queried_object_id() ) ) {
			$location = array( 'single' );
		}

		if ( $location ) {
			$GLOBALS['p10_elementor_fallback']  = $template;
			$GLOBALS['p10_elementor_locations'] = $fallback && ! in_array( $fallback, $location, true )
				? array_merge( $location, array( $fallback ) )
				: $location;
			return locate_template( 'page-templates/theme-builder.php' ) ?: $template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'p10_elementor_template_include', 99 );

/**
 * Add a body class for Elementor-built singular views.
 *
 * @param array $classes Body classes.
 * @return array
 */
function p10_elementor_body_class( $classes ) {
	if ( is_singular() && p10_is_built_with_elementor( get_queried_object_id() ) ) {
		$classes[] = 'p10-elementor-document';
	}
	return $classes;
}
add_filter( 'body_class', 'p10_elementor_body_class' );

/**
 * Show a helpful admin notice when a document is opened in Elementor while the
 * theme template is still in charge.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! p10_elementor_active() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $post_id && p10_is_built_with_elementor( $post_id ) && ! in_array( get_page_template_slug( $post_id ), array( '', 'default', 'page-templates/elementor-full-width.php' ), true ) ) {
			echo '<div class="notice notice-info"><p>' . esc_html__( 'This page is built with Elementor and also has a theme template assigned. Elementor content is rendered inside that template. Remove the template assignment if you want a plain Elementor layout.', 'perfect10' ) . '</p></div>';
		}
	}
);

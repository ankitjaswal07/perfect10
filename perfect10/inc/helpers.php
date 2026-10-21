<?php
/**
 * Shared helpers used by templates, Elementor widgets, the importer and the
 * administration screens.
 *
 * Keeping these in one module avoids duplicating query, URL and markup logic
 * between the PHP templates and the Elementor widget library.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is Elementor loaded and active?
 *
 * @return bool
 */
function p10_elementor_active() {
	return did_action( 'elementor/loaded' ) > 0 && class_exists( '\Elementor\Plugin' );
}

/**
 * Active Elementor version, or an empty string.
 *
 * @return string
 */
function p10_elementor_version() {
	return defined( 'ELEMENTOR_VERSION' ) ? (string) ELEMENTOR_VERSION : '';
}

/**
 * Was a post built with Elementor?
 *
 * This is deliberately stricter than checking `_elementor_edit_mode`: a post
 * that has been opened in Elementor but has no saved document must keep using
 * its PHP template.
 *
 * @param int $post_id Optional post ID.
 * @return bool
 */
function p10_is_built_with_elementor( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return false;
	}

	if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return false;
	}

	$raw = get_post_meta( $post_id, '_elementor_data', true );
	if ( empty( $raw ) ) {
		return false;
	}

	if ( p10_elementor_active() && isset( \Elementor\Plugin::$instance->documents ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( $document && method_exists( $document, 'is_built_with_elementor' ) ) {
			return (bool) $document->is_built_with_elementor();
		}
	}

	return true;
}

/**
 * Elementor editor URL for a post, with a safe fallback.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function p10_elementor_edit_url( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	if ( p10_elementor_active() && isset( \Elementor\Plugin::$instance->documents ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( $document && method_exists( $document, 'get_edit_url' ) ) {
			$url = $document->get_edit_url();
			if ( $url ) {
				return $url;
			}
		}
	}

	return (string) get_edit_post_link( $post_id, 'raw' );
}

/**
 * Get a template part as a string.
 *
 * Reusing the existing parts is what keeps Elementor output pixel-compatible
 * with the PHP templates.
 *
 * @param string $slug Template part slug relative to the theme.
 * @param array  $args Arguments passed to the part.
 * @return string
 */
function p10_get_template_html( $slug, $args = array() ) {
	ob_start();
	get_template_part( $slug, null, $args );
	return (string) ob_get_clean();
}

/**
 * Render a heading with the brand's italic accent without echoing it.
 *
 * @param string $heading Heading text.
 * @param string $accent  Accent text.
 * @param string $tag     Heading tag.
 * @param string $class   Extra class names.
 * @return string
 */
function p10_get_heading_html( $heading, $accent = '', $tag = 'h2', $class = '' ) {
	ob_start();
	p10_the_heading( $heading, $accent, $tag, $class );
	return (string) ob_get_clean();
}

/**
 * Build the markup for a section heading.
 *
 * @param array $args Arguments for template-parts/section-head.php.
 * @return string
 */
function p10_get_section_head_html( $args ) {
	return p10_get_template_html( 'template-parts/section-head', (array) $args );
}

/**
 * HTML for an empty dynamic widget or listing.
 *
 * @param string $message Message.
 * @param string $class   Extra classes.
 * @return string
 */
function p10_empty_state( $message = '', $class = '' ) {
	if ( '' === $message ) {
		$message = __( 'Nothing to show yet. Add published content in WordPress and this section will fill automatically.', 'perfect10' );
	}
	return '<p class="p10-empty ' . esc_attr( $class ) . '">' . esc_html( $message ) . '</p>';
}

/**
 * Convert a comma/newline separated string into a clean array.
 *
 * @param string $value Raw value.
 * @return array
 */
function p10_list_from_string( $value ) {
	$value = (string) $value;
	if ( '' === trim( $value ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/[,|\n]+/', $value ) ) ) );
}

/**
 * Normalise a truthy Elementor switcher value.
 *
 * @param mixed $value Value.
 * @return bool
 */
function p10_is_yes( $value ) {
	return in_array( $value, array( true, 1, '1', 'yes', 'on' ), true );
}

/**
 * Return a URL from an Elementor URL control, with a fallback.
 *
 * @param mixed  $link     URL control value.
 * @param string $fallback Fallback URL.
 * @return string
 */
function p10_el_url( $link, $fallback = '' ) {
	$url = is_array( $link ) && ! empty( $link['url'] ) ? $link['url'] : ( is_string( $link ) ? $link : '' );
	if ( '' === trim( (string) $url ) ) {
		return $fallback;
	}
	return esc_url_raw( $url );
}

/**
 * Attributes for an Elementor URL control.
 *
 * @param mixed  $link     URL control value.
 * @param string $fallback Fallback URL.
 * @return string Escaped attribute string.
 */
function p10_el_link_attrs( $link, $fallback = '' ) {
	$url    = p10_el_url( $link, $fallback );
	$attrs  = ' href="' . esc_url( $url ) . '"';
	$target = is_array( $link ) && ! empty( $link['is_external'] ) ? '_blank' : '';
	$rel    = is_array( $link ) && ! empty( $link['nofollow'] ) ? 'nofollow' : '';

	if ( $target ) {
		$attrs .= ' target="_blank"';
	}
	if ( $rel || '_blank' === $target ) {
		$attrs .= ' rel="' . esc_attr( trim( $rel . ( '_blank' === $target ? ' noopener' : '' ) ) ) . '"';
	}
	return $attrs;
}

/**
 * Term choices for an Elementor select control.
 *
 * @param string $taxonomy Taxonomy.
 * @param bool   $all      Include an "All" option.
 * @return array
 */
function p10_term_choices( $taxonomy, $all = true ) {
	$choices = $all ? array( '' => __( 'All', 'perfect10' ) ) : array();
	$terms   = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return $choices;
	}

	foreach ( $terms as $term ) {
		$label = $term->name;
		if ( 'p10_city' === $taxonomy && $term->count ) {
			/* translators: %s: profile count. */
			$label .= ' (' . number_format_i18n( $term->count ) . ')';
		}
		$choices[ $term->slug ] = $label;
	}

	return $choices;
}

/**
 * Published profile choices for select controls.
 *
 * @return array
 */
function p10_profile_choices() {
	$choices = array( 0 => __( '— Select a profile —', 'perfect10' ) );
	$posts   = get_posts(
		array(
			'post_type'      => 'p10_profile',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	foreach ( $posts as $post ) {
		$choices[ $post->ID ] = $post->post_title;
	}

	return $choices;
}

/**
 * Normalise a dynamic-grid filter array.
 *
 * @param array $filters Raw filters.
 * @return array
 */
function p10_normalize_profile_filters( $filters = array() ) {
	$filters = wp_parse_args(
		$filters,
		array(
			'limit'       => 6,
			'orderby'     => 'featured',
			'order'       => '',
			'county'      => '',
			'city'        => '',
			'category'    => '',
			'nationality' => '',
			'premium'     => '',
			'verified'    => '',
			'available'   => '',
			'search'      => '',
			'context'     => true,
			'include'     => array(),
			'exclude'     => array(),
			'featured_only' => false,
		)
	);

	$filters['limit']       = max( 1, min( 96, (int) $filters['limit'] ) );
	$filters['county']      = sanitize_title( $filters['county'] );
	$filters['city']        = sanitize_title( $filters['city'] );
	$filters['category']    = sanitize_title( $filters['category'] );
	$filters['nationality'] = sanitize_title( $filters['nationality'] );
	$filters['search']      = sanitize_text_field( $filters['search'] );
	$filters['premium']     = in_array( $filters['premium'], array( 'only', 'exclude' ), true ) ? $filters['premium'] : '';
	$filters['verified']    = in_array( $filters['verified'], array( 'only', 'exclude' ), true ) ? $filters['verified'] : '';
	$filters['available']   = p10_is_yes( $filters['available'] );
	$filters['context']     = p10_is_yes( $filters['context'] );
	$filters['include']     = array_values( array_filter( array_map( 'absint', (array) $filters['include'] ) ) );
	$filters['exclude']     = array_values( array_filter( array_map( 'absint', (array) $filters['exclude'] ) ) );
	$filters['featured_only'] = p10_is_yes( $filters['featured_only'] );

	return $filters;
}

/**
 * Build a WP_Query for published profiles.
 *
 * The same args are used by the Elementor Profile Grid widget, the premium and
 * featured sections and any developer calling p10_query_profiles() directly.
 *
 * @param array $filters Filters.
 * @return WP_Query
 */
function p10_query_profiles( $filters = array() ) {
	$filters = p10_normalize_profile_filters( $filters );

	// On a taxonomy archive, "Use the current location" pins that term.
	if ( $filters['context'] && is_tax( array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ) ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			if ( 'p10_county' === $term->taxonomy ) {
				$filters['county'] = $term->slug;
			} elseif ( 'p10_city' === $term->taxonomy ) {
				$filters['city'] = $term->slug;
			} elseif ( 'p10_category' === $term->taxonomy ) {
				$filters['category'] = $term->slug;
			} elseif ( 'p10_nationality' === $term->taxonomy ) {
				$filters['nationality'] = $term->slug;
			}
		}
	}

	$tax_query = array( 'relation' => 'AND' );
	foreach ( array(
		'p10_county'      => $filters['county'],
		'p10_city'        => $filters['city'],
		'p10_category'    => $filters['category'],
		'p10_nationality' => $filters['nationality'],
	) as $taxonomy => $slug ) {
		if ( $slug ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slug,
			);
		}
	}

	$meta_query = array( 'relation' => 'AND' );

	if ( 'only' === $filters['premium'] ) {
		$meta_query[] = array(
			'key'     => 'p10_premium',
			'value'   => '1',
			'compare' => '=',
		);
	} elseif ( 'exclude' === $filters['premium'] ) {
		$meta_query[] = array(
			'key'     => 'p10_premium',
			'value'   => '1',
			'compare' => '!=',
		);
	}

	if ( 'only' === $filters['verified'] ) {
		$meta_query[] = array(
			'key'     => 'p10_verified',
			'value'   => '1',
			'compare' => '=',
		);
	} elseif ( 'exclude' === $filters['verified'] ) {
		$meta_query[] = array(
			'key'     => 'p10_verified',
			'value'   => '1',
			'compare' => '!=',
		);
	}

	if ( $filters['available'] ) {
		$meta_query[] = array(
			'key'     => 'p10_available',
			'value'   => array( 'available', 'limited' ),
			'compare' => 'IN',
		);
	}

	if ( $filters['featured_only'] ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => 'p10_featured_order',
				'compare' => 'EXISTS',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => 'p10_premium',
				'value'   => '1',
				'compare' => '=',
			),
		);
	}

	$has_filters = $filters['county'] || $filters['city'] || $filters['category'] || $filters['nationality']
		|| $filters['premium'] || $filters['verified'] || $filters['available'] || $filters['search']
		|| $filters['featured_only'] || $filters['include'] || $filters['exclude'];

	// The curated homepage order needs a PHP sort because most profiles do not
	// have an explicit featured_order value. Fall back to a standard query once
	// any meaningful filter is active.
	if ( 'featured' === $filters['orderby'] && ! $has_filters && function_exists( 'p10_get_featured_profiles' ) ) {
		$posts = p10_get_featured_profiles( $filters['limit'] );
		$ids   = wp_list_pluck( $posts, 'ID' );
		return new WP_Query(
			array(
				'post_type'      => 'p10_profile',
				'post_status'    => 'publish',
				'post__in'       => $ids ? $ids : array( 0 ),
				'orderby'        => 'post__in',
				'posts_per_page' => $filters['limit'],
				'no_found_rows'  => true,
				'has_password'   => false,
			)
		);
	}

	$args = array(
		'post_type'           => 'p10_profile',
		'post_status'         => 'publish',
		'posts_per_page'      => $filters['limit'],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'has_password'        => false,
	);

	if ( $filters['include'] ) {
		$args['post__in'] = $filters['include'];
	}
	if ( $filters['exclude'] ) {
		$args['post__not_in'] = $filters['exclude'];
	}
	if ( $filters['search'] ) {
		$args['s'] = $filters['search'];
	}
	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	switch ( $filters['orderby'] ) {
		case 'newest':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'oldest':
			$args['orderby'] = 'date';
			$args['order']   = 'ASC';
			break;
		case 'title':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		case 'menu_order':
			$args['orderby'] = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
			break;
		case 'random':
			$args['orderby'] = 'rand';
			break;
		case 'rate_asc':
		case 'rate_desc':
			$args['meta_key'] = 'p10_rate'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'rate_asc' === $filters['orderby'] ? 'ASC' : 'DESC';
			break;
		case 'age_asc':
		case 'age_desc':
			$args['meta_key'] = 'p10_age'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'age_asc' === $filters['orderby'] ? 'ASC' : 'DESC';
			break;
		default:
			$args['orderby'] = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
			break;
	}

	if ( $filters['order'] && in_array( strtoupper( $filters['order'] ), array( 'ASC', 'DESC' ), true ) ) {
		$args['order'] = strtoupper( $filters['order'] );
	}

	return new WP_Query( apply_filters( 'p10_query_profiles_args', $args, $filters ) );
}

/**
 * Elementor query controls used by the dynamic widgets.
 *
 * @param object $widget Elementor widget.
 */
function p10_register_query_controls( $widget ) {
	$widget->start_controls_section(
		'p10_query',
		array(
			'label' => __( 'Content & Query', 'perfect10' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		)
	);

	$widget->add_control(
		'limit',
		array(
			'label'   => __( 'Number of profiles', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 48,
			'default' => 6,
		)
	);
	$widget->add_control(
		'orderby',
		array(
			'label'   => __( 'Order by', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'featured',
			'options' => array(
				'featured'  => __( 'Featured / curated', 'perfect10' ),
				'newest'    => __( 'Newest profiles', 'perfect10' ),
				'oldest'    => __( 'Oldest profiles', 'perfect10' ),
				'title'     => __( 'Name A–Z', 'perfect10' ),
				'menu_order'=> __( 'Menu order', 'perfect10' ),
				'rate_asc'  => __( 'Lowest rate', 'perfect10' ),
				'rate_desc' => __( 'Highest rate', 'perfect10' ),
				'age_asc'   => __( 'Youngest age', 'perfect10' ),
				'age_desc'  => __( 'Oldest age', 'perfect10' ),
				'random'    => __( 'Random', 'perfect10' ),
			),
		)
	);
	$widget->add_control(
		'county',
		array(
			'label'   => __( 'County', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => p10_term_choices( 'p10_county' ),
		)
	);
	$widget->add_control(
		'city',
		array(
			'label'   => __( 'City', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => p10_term_choices( 'p10_city' ),
		)
	);
	$widget->add_control(
		'category',
		array(
			'label'   => __( 'Profile type', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => p10_term_choices( 'p10_category' ),
		)
	);
	$widget->add_control(
		'nationality',
		array(
			'label'   => __( 'Nationality', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => p10_term_choices( 'p10_nationality' ),
		)
	);
	$widget->add_control(
		'premium',
		array(
			'label'   => __( 'Premium placement', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => array(
				''        => __( 'Any', 'perfect10' ),
				'only'    => __( 'Premium only', 'perfect10' ),
				'exclude' => __( 'Standard only', 'perfect10' ),
			),
		)
	);
	$widget->add_control(
		'verified',
		array(
			'label'   => __( 'Verification', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => array(
				''        => __( 'Any', 'perfect10' ),
				'only'    => __( 'Verified only', 'perfect10' ),
			),
		)
	);
	$widget->add_control(
		'available',
		array(
			'label'   => __( 'Available profiles only', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		)
	);
	$widget->add_control(
		'featured_only',
		array(
			'label'   => __( 'Featured or premium only', 'perfect10' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		)
	);
	$widget->add_control(
		'context',
		array(
			'label'       => __( 'Use current location archive', 'perfect10' ),
			'type'        => \Elementor\Controls_Manager::SWITCHER,
			'default'     => 'yes',
			'description' => __( 'On a county, city, type or nationality archive this automatically limits the grid to that term.', 'perfect10' ),
		)
	);

	$widget->end_controls_section();
}

/**
 * Shared Elementor grid layout controls.
 *
 * @param object $widget   Elementor widget.
 * @param string $selector CSS selector for the grid.
 */
function p10_register_grid_controls( $widget, $selector = '{{WRAPPER}} .p10-grid' ) {
	$widget->add_responsive_control(
		'columns',
		array(
			'label'          => __( 'Columns', 'perfect10' ),
			'type'           => \Elementor\Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
			'selectors'      => array(
				$selector => 'grid-template-columns:repeat({{VALUE}},minmax(0,1fr));',
			),
		)
	);
	$widget->add_responsive_control(
		'gap',
		array(
			'label'      => __( 'Gap', 'perfect10' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				$selector => 'gap:{{SIZE}}{{UNIT}};',
			),
		)
	);
}

/**
 * Shared section spacing controls.
 *
 * @param object $widget   Elementor widget.
 * @param string $selector CSS selector.
 * @param string $label    Section label.
 */
function p10_register_spacing_controls( $widget, $selector = '{{WRAPPER}} .p10-section', $label = '' ) {
	$widget->start_controls_section(
		'p10_spacing',
		array(
			'label' => $label ? $label : __( 'Section Spacing', 'perfect10' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		)
	);
	$widget->add_responsive_control(
		'section_padding',
		array(
			'label'      => __( 'Vertical padding', 'perfect10' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em', 'rem', 'vw' ),
			'selectors'  => array(
				$selector => 'padding-top:{{TOP}}{{UNIT}};padding-bottom:{{BOTTOM}}{{UNIT}};',
			),
		)
	);
	$widget->add_control(
		'section_background',
		array(
			'label'     => __( 'Background colour', 'perfect10' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array(
				$selector => 'background-color:{{VALUE}};',
			),
		)
	);
	$widget->end_controls_section();
}

/**
 * Shared typography controls for a heading selector.
 *
 * @param object $widget   Elementor widget.
 * @param string $prefix   Control prefix.
 * @param string $label    Label.
 * @param string $selector Selector.
 */
function p10_register_heading_style( $widget, $prefix, $label, $selector ) {
	if ( class_exists( '\Elementor\Group_Control_Typography' ) ) {
		$widget->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => $prefix . '_typography',
				'label'    => $label,
				'selector' => $selector,
			)
		);
	}
	$widget->add_control(
		$prefix . '_color',
		array(
			'label'     => __( 'Colour', 'perfect10' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array(
				$selector => 'color:{{VALUE}};',
			),
		)
	);
}

/**
 * Get the current form notification recipient.
 *
 * @return string
 */
function p10_form_recipient() {
	$recipient = (string) p10_option( 'form_recipient', '' );
	if ( ! is_email( $recipient ) ) {
		$recipient = (string) get_option( 'admin_email' );
	}
	return (string) apply_filters( 'p10_form_recipient', $recipient );
}

/**
 * Privacy/consent copy appended to forms.
 *
 * @return string
 */
function p10_form_privacy_text() {
	$text = (string) p10_option( 'form_privacy', '' );
	if ( ! $text ) {
		$text = __( 'By sending this request you confirm you are 18 or older and consent to being contacted about this enquiry. Your details are used only to handle this request.', 'perfect10' );
	}
	$policy = get_privacy_policy_url();
	if ( $policy && false === strpos( $text, $policy ) ) {
		$text .= ' ' . sprintf(
			/* translators: %s: privacy policy URL. */
			__( 'Read the <a href="%s">privacy policy</a>.', 'perfect10' ),
			esc_url( $policy )
		);
	}
	return $text;
}

/**
 * Is a plugin active, without requiring plugin.php on the front end?
 *
 * @param string $basename Plugin basename.
 * @return bool
 */
function p10_is_plugin_active( $basename ) {
	if ( function_exists( 'is_plugin_active' ) ) {
		return is_plugin_active( $basename );
	}
	$active = (array) get_option( 'active_plugins', array() );
	return in_array( $basename, $active, true );
}

/**
 * Elementor kit post ID (0 when unavailable).
 *
 * @return int
 */
function p10_elementor_kit_id() {
	$kit_id = (int) get_option( 'elementor_active_kit', 0 );
	return $kit_id > 0 ? $kit_id : 0;
}

/**
 * Sync the theme's global colours and typography into the Elementor kit.
 *
 * Run on activation or from the admin screen. It intentionally does not run on
 * every Customizer save, so an administrator's Elementor kit edits are not
 * overwritten implicitly.
 *
 * @param bool $force Whether to overwrite an existing kit.
 * @return bool
 */
function p10_sync_elementor_kit( $force = false ) {
	if ( ! p10_elementor_active() || ! p10_elementor_kit_id() ) {
		return false;
	}

	$kit_id  = p10_elementor_kit_id();
	$current = get_post_meta( $kit_id, '_elementor_page_settings', true );
	$current = is_array( $current ) ? $current : array();

	$tokens   = function_exists( 'p10_design_tokens' ) ? p10_design_tokens() : array();
	$get      = static function ( $key, $fallback ) use ( $tokens ) {
		$value = isset( $tokens[ $key ] ) ? $tokens[ $key ] : '';
		return $value ? $value : $fallback;
	};
	$primary  = sanitize_hex_color( (string) $get( '--p10-color-primary', '#d4af37' ) );
	$secondary = sanitize_hex_color( (string) $get( '--p10-color-secondary', '#1a1712' ) );
	$text     = sanitize_hex_color( (string) $get( '--p10-color-text', '#f5f0e6' ) );
	$accent   = sanitize_hex_color( (string) $get( '--p10-color-accent', '#e9d9a6' ) );
	$bg       = sanitize_hex_color( (string) $get( '--p10-color-background', '#0b0a09' ) );
	$surface  = sanitize_hex_color( (string) $get( '--p10-color-surface', '#14120e' ) );
	$muted    = sanitize_hex_color( (string) $get( '--p10-color-muted', '#9c9484' ) );
	$border   = sanitize_hex_color( (string) $get( '--p10-color-border', '' ) );
	$link_hover = sanitize_hex_color( (string) $get( '--p10-color-link-hover', '' ) );
	$focus    = sanitize_hex_color( (string) $get( '--p10-color-focus', '' ) );
	$button   = sanitize_hex_color( (string) $get( '--p10-color-button-bg', '' ) );
	$button_text = sanitize_hex_color( (string) $get( '--p10-color-button-text', '' ) );
	$form_bg  = sanitize_hex_color( (string) $get( '--p10-color-form-bg', '' ) );
	$form_border = sanitize_hex_color( (string) $get( '--p10-color-form-border', '' ) );

	$display = (string) p10_option( 'font_display', 'Playfair Display' );
	$body    = (string) p10_option( 'font_body', 'Inter' );
	$weight  = (string) p10_option( 'heading_weight', '500' );
	$weight  = in_array( $weight, array( '400', '500', '600', '700' ), true ) ? $weight : '500';

	$system_colors = array(
		array( '_id' => 'primary', 'title' => __( 'Perfect 10 — Primary', 'perfect10' ), 'color' => $primary ? $primary : '#d4af37' ),
		array( '_id' => 'secondary', 'title' => __( 'Perfect 10 — Secondary', 'perfect10' ), 'color' => $secondary ? $secondary : '#1a1712' ),
		array( '_id' => 'text', 'title' => __( 'Perfect 10 — Text', 'perfect10' ), 'color' => $text ? $text : '#f5f0e6' ),
		array( '_id' => 'accent', 'title' => __( 'Perfect 10 — Accent', 'perfect10' ), 'color' => $accent ? $accent : '#e9d9a6' ),
	);
	$custom_colors = array();
	foreach ( array(
		'p10-background'    => array( __( 'Perfect 10 — Background', 'perfect10' ), $bg ),
		'p10-surface'       => array( __( 'Perfect 10 — Surface', 'perfect10' ), $surface ),
		'p10-muted'         => array( __( 'Perfect 10 — Muted text', 'perfect10' ), $muted ),
		'p10-border'        => array( __( 'Perfect 10 — Border', 'perfect10' ), $border ),
		'p10-link-hover'    => array( __( 'Perfect 10 — Link hover', 'perfect10' ), $link_hover ),
		'p10-focus'         => array( __( 'Perfect 10 — Focus ring', 'perfect10' ), $focus ),
		'p10-button'        => array( __( 'Perfect 10 — Button', 'perfect10' ), $button ),
		'p10-button-text'   => array( __( 'Perfect 10 — Button text', 'perfect10' ), $button_text ),
		'p10-form'          => array( __( 'Perfect 10 — Form field', 'perfect10' ), $form_bg ),
		'p10-form-border'   => array( __( 'Perfect 10 — Form border', 'perfect10' ), $form_border ),
	) as $id => $definition ) {
		if ( ! $definition[1] || ! preg_match( '/^#[0-9a-f]{3,8}$/i', $definition[1] ) ) {
			continue;
		}
		$custom_colors[] = array(
			'_id'   => $id,
			'title' => $definition[0],
			'color' => $definition[1],
		);
	}

	$system_typography = array(
		array(
			'_id'                    => 'primary',
			'title'                  => __( 'Perfect 10 — Display', 'perfect10' ),
			'typography_typography'  => 'custom',
			'typography_font_family' => $display,
			'typography_font_weight' => $weight,
		),
		array(
			'_id'                    => 'secondary',
			'title'                  => __( 'Perfect 10 — Display alt', 'perfect10' ),
			'typography_typography'  => 'custom',
			'typography_font_family' => $display,
			'typography_font_weight' => $weight,
		),
		array(
			'_id'                    => 'text',
			'title'                  => __( 'Perfect 10 — Body', 'perfect10' ),
			'typography_typography'  => 'custom',
			'typography_font_family' => $body,
			'typography_font_weight' => '300',
		),
		array(
			'_id'                    => 'accent',
			'title'                  => __( 'Perfect 10 — UI', 'perfect10' ),
			'typography_typography'  => 'custom',
			'typography_font_family' => 'Jost',
			'typography_font_weight' => '400',
		),
	);

	$payload = md5( (string) wp_json_encode( array( $system_colors, $custom_colors, $system_typography ) ) );

	// Detect an administrator-edited kit: if the colours currently in the kit
	// differ from the ones this theme last wrote, leave them alone.
	$kit_hash = md5( (string) wp_json_encode( array(
		isset( $current['system_colors'] ) ? $current['system_colors'] : array(),
		isset( $current['custom_colors'] ) ? $current['custom_colors'] : array(),
		isset( $current['system_typography'] ) ? $current['system_typography'] : array(),
	) ) );
	$applied  = isset( $current['p10_kit_hash'] ) ? (string) $current['p10_kit_hash'] : '';

	if ( ! $force && $applied && $kit_hash !== $applied ) {
		return false;
	}
	if ( ! $force && $applied === $payload ) {
		return false;
	}

	$current['system_colors']     = $system_colors;
	$current['custom_colors']     = $custom_colors;
	$current['system_typography'] = $system_typography;
	$current['p10_synced']        = 1;
	$current['p10_synced_at']     = current_time( 'mysql' );
	$current['p10_kit_hash']      = $payload;

	update_post_meta( $kit_id, '_elementor_page_settings', $current );

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return true;
}

/* -------------------------------------------------------------------------
 * Demo-import ownership tracking
 * ---------------------------------------------------------------------- */

/**
 * Read the demo manifest.
 *
 * @return array
 */
function p10_demo_manifest() {
	$manifest = get_option( P10_DEMO_MANIFEST_OPTION, array() );
	if ( ! is_array( $manifest ) ) {
		$manifest = array();
	}
	return wp_parse_args(
		$manifest,
		array(
			'posts'       => array(),
			'terms'       => array(),
			'attachments' => array(),
			'menus'       => array(),
			'version'     => P10_VERSION,
		)
	);
}

/**
 * Record a post created by the demo importer.
 *
 * @param int    $post_id Post ID.
 * @param string $kind    Manifest bucket.
 */
function p10_demo_mark_post( $post_id, $kind = 'posts' ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return;
	}

	update_post_meta( $post_id, '_p10_demo_origin', P10_DEMO_ORIGIN );
	update_post_meta( $post_id, '_p10_demo_kind', sanitize_key( $kind ) );

	$manifest = p10_demo_manifest();
	$key      = 'attachments' === $kind ? 'attachments' : 'posts';
	if ( ! in_array( $post_id, (array) $manifest[ $key ], true ) ) {
		$manifest[ $key ][] = $post_id;
	}
	$manifest['version'] = P10_VERSION;
	update_option( P10_DEMO_MANIFEST_OPTION, $manifest, false );
}

/**
 * Record a term created by the demo importer.
 *
 * @param int $term_id Term ID.
 */
function p10_demo_mark_term( $term_id ) {
	$term_id = (int) $term_id;
	if ( ! $term_id ) {
		return;
	}
	update_term_meta( $term_id, '_p10_demo_origin', P10_DEMO_ORIGIN );
	$manifest = p10_demo_manifest();
	if ( ! in_array( $term_id, (array) $manifest['terms'], true ) ) {
		$manifest['terms'][] = $term_id;
	}
	update_option( P10_DEMO_MANIFEST_OPTION, $manifest, false );
}

/**
 * Is a post owned by the demo importer?
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function p10_is_demo_owned( $post_id ) {
	return P10_DEMO_ORIGIN === get_post_meta( (int) $post_id, '_p10_demo_origin', true );
}

/**
 * Remove only content recorded in the demo manifest.
 *
 * User-created content is never touched. The caller must have already checked
 * capability and nonce and asked for explicit confirmation.
 *
 * @return array Counts.
 */
function p10_demo_reset() {
	$manifest = p10_demo_manifest();
	$counts   = array(
		'posts'       => 0,
		'attachments' => 0,
		'terms'       => 0,
		'menus'       => 0,
	);

	foreach ( (array) $manifest['attachments'] as $attachment_id ) {
		if ( p10_is_demo_owned( $attachment_id ) && wp_delete_attachment( (int) $attachment_id, true ) ) {
			$counts['attachments']++;
		}
	}

	foreach ( (array) $manifest['posts'] as $post_id ) {
		if ( p10_is_demo_owned( $post_id ) && wp_delete_post( (int) $post_id, true ) ) {
			$counts['posts']++;
		}
	}

	foreach ( (array) $manifest['terms'] as $term_id ) {
		$term = get_term( (int) $term_id );
		if ( $term && ! is_wp_error( $term ) && P10_DEMO_ORIGIN === get_term_meta( (int) $term_id, '_p10_demo_origin', true ) ) {
			$deleted = wp_delete_term( (int) $term_id, $term->taxonomy );
			if ( $deleted && ! is_wp_error( $deleted ) ) {
				$counts['terms']++;
			}
		}
	}

	foreach ( (array) $manifest['menus'] as $menu_id ) {
		$menu = wp_get_nav_menu_object( (int) $menu_id );
		if ( $menu ) {
			wp_delete_nav_menu( (int) $menu_id );
			$counts['menus']++;
		}
	}

	update_option( P10_DEMO_MANIFEST_OPTION, array(), false );

	// Release importer-owned front page assignments only.
	$front = (int) get_option( 'page_on_front' );
	if ( $front && p10_is_demo_owned( $front ) ) {
		delete_option( 'page_on_front' );
	}

	return $counts;
}

/**
 * Small inline SVG icon set used by the widget library.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $class Extra class.
 * @return string
 */
function p10_icon( $name, $size = 18, $class = '' ) {
	$paths = array(
		'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'diamond'  => '<path d="m12 2 4 6-4 14-4-14 4-6Z"/><path d="M8 8h8"/>',
		'arrow'    => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
		'check'    => '<path d="m20 6-11 11-5-5"/>',
		'map'      => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
		'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/>',
		'chevron'  => '<path d="m6 9 6 6 6-6"/>',
		'star'     => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1L12 2Z"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'sparkle'  => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3Z"/>',
	);
	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['diamond'];
	$cls  = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"' . $cls . '>' . $path . '</svg>';
}

/**
 * Render profile cards using the original template part.
 *
 * @param array  $posts   Profile posts.
 * @param string $variant Optional card variant (premium).
 * @param bool   $reveal  Apply the scroll reveal animation.
 */
function p10_output_profile_cards( $posts, $variant = '', $reveal = false ) {
	$index = 0;
	foreach ( (array) $posts as $profile ) {
		$index++;
		get_template_part(
			'template-parts/card-profile',
			null,
			array(
				'profile' => $profile,
				'index'   => 'premium' === $variant ? 0 : $index,
				'variant' => $variant,
				'reveal'  => $reveal,
			)
		);
	}
}

/**
 * Render city or category tiles using the original template parts.
 *
 * @param array  $terms    Terms.
 * @param string $taxonomy Taxonomy slug.
 * @param bool   $reveal   Apply the scroll reveal animation.
 * @param string $layout   Card layout: overlay (default) or stacked.
 * @param array  $card_args Optional card overrides: images (slug => attachment ID),
 *                          fallback (ID), show_blurb, show_count, overlay, ratio, focal.
 */
function p10_output_term_cards( $terms, $taxonomy = 'p10_city', $reveal = false, $layout = 'overlay', $card_args = array() ) {
	$index = 0;
	$base  = is_array( $card_args ) ? $card_args : array();
	$images   = isset( $base['images'] ) && is_array( $base['images'] ) ? $base['images'] : array();
	$fallback = isset( $base['fallback'] ) ? $base['fallback'] : 0;
	unset( $base['images'], $base['fallback'] );

	foreach ( (array) $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$index++;
		$args = array_merge(
			$base,
			array(
				'term'   => $term,
				'index'  => $index,
				'reveal' => $reveal,
				'layout' => $layout,
			)
		);

		if ( ! empty( $images[ $term->slug ] ) ) {
			$args['image'] = $images[ $term->slug ];
		} elseif ( $fallback ) {
			$args['image'] = $fallback;
		}

		get_template_part(
			'p10_category' === $taxonomy ? 'template-parts/card-type' : 'template-parts/card-city',
			null,
			$args
		);
	}
}

/**
 * Get the ordered list of cities, optionally limited.
 *
 * @param int    $limit  Limit (0 = all).
 * @param string $county County slug.
 * @return WP_Term[]
 */
function p10_get_city_terms( $limit = 0, $county = '' ) {
	$terms = '' !== $county ? p10_get_cities( $county ) : p10_get_cities();
	if ( $limit ) {
		$terms = array_slice( $terms, 0, (int) $limit );
	}
	return array_values( array_filter( (array) $terms, static function ( $term ) {
		return $term instanceof WP_Term;
	} ) );
}

/**
 * Section heading built from the Perfect 10 Customizer.
 *
 * Widgets use this in their default "inherit from theme" mode so a page and
 * the Customizer never hold two competing copies of the same copy.
 *
 * @param string $key        Option prefix, e.g. muses, premium, types.
 * @param string $action_url Optional action URL.
 * @return string
 */
function p10_theme_section_head( $key, $action_url = '' ) {
	$key = sanitize_key( $key );
	if ( ! $key ) {
		return '';
	}

	return p10_get_section_head_html(
		array(
			'number'     => (string) p10_option( $key . '_number', '' ),
			'kicker'     => (string) p10_option( $key . '_kicker', '' ),
			'title'      => (string) p10_option( $key . '_title', '' ),
			'accent'     => (string) p10_option( $key . '_accent', '' ),
			'action'     => (string) p10_option( $key . '_action', '' ),
			'action_url' => $action_url,
		)
	);
}

/**
 * Print a visible marker on sample/demo reviews.
 *
 * Real, verified guest reviews never carry the sample flag. The importer sets
 * it so nobody can mistake seeded copy for a genuine testimonial.
 *
 * @param int $review_id Review post ID.
 * @return string
 */
function p10_review_sample_badge( $review_id ) {
	$is_sample = (bool) get_post_meta( (int) $review_id, 'p10_sample', true );
	$label     = (string) get_post_meta( (int) $review_id, 'p10_guest_label', true );

	if ( ! $is_sample && false === stripos( $label, 'sample' ) ) {
		return '';
	}

	return '<span class="p10-review__sample">' . esc_html__( 'Sample content', 'perfect10' ) . '</span>';
}

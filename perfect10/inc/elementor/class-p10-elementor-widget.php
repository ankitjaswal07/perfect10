<?php
/**
 * Base class for every Perfect 10 Elementor widget.
 *
 * The class name is retained from the original theme (P10_Elementor_Widget)
 * so existing child themes and saved documents continue to work. It now also
 * centralises the category, shared controls and the helpers that let widgets
 * reuse the original PHP template parts.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Shared widget base.
 */
abstract class P10_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Perfect 10 widget category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'perfect10' );
	}

	/**
	 * Default panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-diamond';
	}

	/**
	 * Keywords used by the Elementor search.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'perfect10', 'perfect 10', 'directory', 'profile', 'escort', 'companion' );
	}

	/**
	 * Dependencies: the widget markup is styled by the theme stylesheets.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'perfect10', 'p10-main', 'p10-widgets' );
	}

	/**
	 * Dependencies: the original front-end script powers search, filters,
	 * galleries, the mobile drawer and reveal animations.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'p10-main' );
	}

	/**
	 * Legacy column/gap controls used by the three original widgets.
	 */
	protected function columns() {
		p10_register_grid_controls( $this, '{{WRAPPER}} .p10-dynamic-grid' );
	}

	/**
	 * The current published profile, if the request is a profile page.
	 *
	 * @return int
	 */
	protected function current_profile() {
		$id = (int) get_the_ID();
		if (
			'p10_profile' === get_post_type( $id )
			&& 'publish' === get_post_status( $id )
			&& ! post_password_required( $id )
		) {
			return $id;
		}
		return 0;
	}

	/**
	 * Is this render happening inside the Elementor editor or preview?
	 *
	 * @return bool
	 */
	protected function is_editor_preview() {
		if ( ! p10_elementor_active() || ! isset( \Elementor\Plugin::$instance->editor ) ) {
			return false;
		}
		return (bool) \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| ( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() );
	}

	/**
	 * Resolve the profile used by a widget.
	 *
	 * Public output never falls back to a random profile. The editor preview
	 * may show the most recent profile so the design can be composed before a
	 * profile is selected.
	 *
	 * @param array $settings Widget settings.
	 * @return int
	 */
	protected function resolve_profile_id( $settings = array() ) {
		if ( ! empty( $settings['profile_id'] ) ) {
			$id = (int) $settings['profile_id'];
			if ( 'p10_profile' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
				return $id;
			}
		}

		$current = $this->current_profile();
		if ( $current ) {
			return $current;
		}

		if ( $this->is_editor_preview() ) {
			$latest = get_posts(
				array(
					'post_type'      => 'p10_profile',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				)
			);
			return $latest ? (int) $latest[0] : 0;
		}

		return 0;
	}

	/**
	 * Controls for a section heading, shared by most widgets.
	 *
	 * Every default can be sourced from the Perfect 10 Customizer so an
	 * untouched widget continues to follow the theme settings; editing the
	 * control in Elementor overrides it for that page only.
	 *
	 * @param string $prefix   Control prefix.
	 * @param array  $defaults Default values.
	 * @param string $section  Controls section ID.
	 */
	protected function add_heading_controls( $prefix = 'heading', $defaults = array(), $section = 'p10_heading', $theme_key = '' ) {
		$defaults = wp_parse_args(
			$defaults,
			array(
				'number'       => '',
				'kicker'       => '',
				'title'        => '',
				'accent'       => '',
				'lede'         => '',
				'action'       => '',
				'action_url'   => '',
				'center'       => '',
				'tag'          => 'h2',
				'show_heading' => 'yes',
			)
		);

		$this->start_controls_section(
			$section,
			array(
				'label' => __( 'Section Heading', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			$prefix . '_source',
			array(
				'label'       => __( 'Content source', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => $theme_key ? 'theme' : 'custom',
				'options'     => array(
					'theme'  => __( 'Perfect 10 Customizer (single source of truth)', 'perfect10' ),
					'custom' => __( 'Override for this page', 'perfect10' ),
				),
				'description' => __( '“Customizer” keeps the homepage and Appearance → Customize in sync. Choose “Override” only when this section needs copy that differs from every other page.', 'perfect10' ),
			)
		);
		$this->add_control(
			$prefix . '_theme_note',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<p class="p10-elementor-note">' . esc_html__( 'Heading copy is inherited from the Customizer. Switch Content source to “Override” to edit it here.', 'perfect10' ) . '</p>',
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array( $prefix . '_source' => 'theme' ),
			)
		);
		$this->add_control(
			$prefix . '_show',
			array(
				'label'        => __( 'Show section heading', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'perfect10' ),
				'label_off'    => __( 'No', 'perfect10' ),
				'return_value' => 'yes',
				'default'      => $defaults['show_heading'],
			)
		);

		$custom = array( $prefix . '_source' => 'custom' );

		$this->add_control(
			$prefix . '_number',
			array(
				'label'       => __( 'Section number', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => $defaults['number'],
				'description' => __( 'For example 01. Leave blank to hide.', 'perfect10' ),
				'condition'   => $custom,
			)
		);
		$this->add_control(
			$prefix . '_kicker',
			array(
				'label'     => __( 'Kicker', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => $defaults['kicker'],
				'condition' => $custom,
			)
		);
		$this->add_control(
			$prefix . '_title',
			array(
				'label'     => __( 'Heading (before accent)', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => $defaults['title'],
				'condition' => $custom,
			)
		);
		$this->add_control(
			$prefix . '_accent',
			array(
				'label'     => __( 'Italic gold accent', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => $defaults['accent'],
				'condition' => $custom,
			)
		);
		$this->add_control(
			$prefix . '_lede',
			array(
				'label'     => __( 'Intro / lede', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => $defaults['lede'],
				'condition' => $custom,
			)
		);
		$this->add_control(
			$prefix . '_action',
			array(
				'label'     => __( 'Action link label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => $defaults['action'],
				'condition' => $custom,
			)
		);
		$this->add_control(
			$prefix . '_action_url',
			array(
				'label'         => __( 'Action link', 'perfect10' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'placeholder'   => __( 'https://your-link.com', 'perfect10' ),
				'default'       => $defaults['action_url'] ? array( 'url' => $defaults['action_url'] ) : array(),
				'show_external' => true,
				'condition'     => $custom,
			)
		);
		$this->add_control(
			$prefix . '_center',
			array(
				'label'        => __( 'Centre heading', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => $defaults['center'],
				'condition'    => $custom,
			)
		);
		$this->add_control(
			$prefix . '_tag',
			array(
				'label'     => __( 'Heading tag', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => $defaults['tag'],
				'options'   => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
				),
				'condition' => $custom,
			)
		);

		$this->end_controls_section();
	}

	protected function heading_is_visible( $settings, $prefix = 'heading' ) {
		return ! isset( $settings[ $prefix . '_show' ] ) || 'yes' === $settings[ $prefix . '_show' ];
	}

	/**
	 * Build the shared section heading.
	 *
	 * @param array  $settings   Widget settings.
	 * @param string $prefix     Control prefix.
	 * @param string $theme_key  Customizer option prefix for inherited mode.
	 * @param string $action_url Theme-mode action URL.
	 * @return string
	 */
	protected function get_heading_html( $settings, $prefix = 'heading', $theme_key = '', $action_url = '' ) {
		if ( isset( $settings[ $prefix . '_show' ] ) && 'yes' !== $settings[ $prefix . '_show' ] ) {
			return '';
		}

		$source = isset( $settings[ $prefix . '_source' ] ) ? $settings[ $prefix . '_source' ] : 'theme';
		if ( 'theme' === $source && $theme_key ) {
			return p10_theme_section_head( $theme_key, $action_url );
		}

		$args = array(
			'number'     => isset( $settings[ $prefix . '_number' ] ) ? (string) $settings[ $prefix . '_number' ] : '',
			'kicker'     => isset( $settings[ $prefix . '_kicker' ] ) ? (string) $settings[ $prefix . '_kicker' ] : '',
			'title'      => isset( $settings[ $prefix . '_title' ] ) ? (string) $settings[ $prefix . '_title' ] : '',
			'accent'     => isset( $settings[ $prefix . '_accent' ] ) ? (string) $settings[ $prefix . '_accent' ] : '',
			'lede'       => isset( $settings[ $prefix . '_lede' ] ) ? (string) $settings[ $prefix . '_lede' ] : '',
			'action'     => isset( $settings[ $prefix . '_action' ] ) ? (string) $settings[ $prefix . '_action' ] : '',
			'action_url' => p10_el_url( isset( $settings[ $prefix . '_action_url' ] ) ? $settings[ $prefix . '_action_url' ] : '', '' ),
			'center'     => isset( $settings[ $prefix . '_center' ] ) && 'yes' === $settings[ $prefix . '_center' ],
			'tag'        => isset( $settings[ $prefix . '_tag' ] ) ? (string) $settings[ $prefix . '_tag' ] : 'h2',
		);

		return p10_get_section_head_html( $args );
	}

	/**
	 * Shared style controls for section headings.
	 *
	 * @param string $selector Heading CSS selector.
	 * @param string $section  Controls section ID.
	 */
	protected function add_heading_style_controls( $selector = '{{WRAPPER}} .p10-section-head .p10-heading', $section = 'p10_heading_style' ) {
		$this->start_controls_section(
			$section,
			array(
				'label' => __( 'Section Heading Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		p10_register_heading_style( $this, 'p10_heading', __( 'Heading typography', 'perfect10' ), $selector );

		$this->add_control(
			'p10_kicker_color',
			array(
				'label'     => __( 'Kicker colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-eyebrow__text, {{WRAPPER}} .p10-eyebrow, {{WRAPPER}} .p10-section-head .p10-eyebrow' => 'color:{{VALUE}};',
				),
			)
		);
		$this->add_control(
			'p10_action_color',
			array(
				'label'     => __( 'Action link colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-section-head__link, {{WRAPPER}} .p10-section-head__aside a' => 'color:{{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Shared style controls for card grids.
	 *
	 * @param string $selector Card selector.
	 * @param string $section  Controls section ID.
	 */
	protected function add_card_style_controls( $selector = '{{WRAPPER}} .p10-card', $section = 'p10_card_style' ) {
		$this->start_controls_section(
			$section,
			array(
				'label' => __( 'Card Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'p10_card_bg',
			array(
				'label'     => __( 'Card background', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'background-color:{{VALUE}};',
				),
			)
		);
		$this->add_control(
			'p10_card_border',
			array(
				'label'     => __( 'Card border', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'border-color:{{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'p10_card_radius',
			array(
				'label'      => __( 'Card radius', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 32,
					),
				),
				'selectors'  => array(
					$selector => 'border-radius:{{SIZE}}{{UNIT}};overflow:hidden;',
				),
			)
		);
		$this->add_control(
			'p10_card_title_color',
			array(
				'label'     => __( 'Card title colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-card__title, {{WRAPPER}} .p10-card__name' => 'color:{{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card images and layout for term-based widgets (cities, profile types).
	 *
	 * @param string $taxonomy Taxonomy slug used for the per-card picker.
	 */
	protected function add_term_card_media_controls( $taxonomy ) {
		$this->start_controls_section(
			'card_media',
			array(
				'label' => __( 'Card Images & Layout', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'image_source',
			array(
				'label'   => __( 'Card images', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'term',
				'options' => array(
					'term'     => __( 'Term cover image (set in the taxonomy screen)', 'perfect10' ),
					'override' => __( 'Choose images for this widget', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'fallback_image',
			array(
				'label'       => __( 'Fallback image', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => array(),
				'description' => __( 'Used only for cards without their own image. Leave empty to keep the theme placeholder.', 'perfect10' ),
				'condition'   => array( 'image_source' => 'override' ),
			)
		);
		$this->add_control(
			'card_images',
			array(
				'label'       => __( 'Per-card images', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'    => 'term',
						'label'   => __( 'Term', 'perfect10' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => p10_term_choices( $taxonomy ),
						'default' => '',
					),
					array(
						'name'  => 'image',
						'label' => __( 'Image', 'perfect10' ),
						'type'  => \Elementor\Controls_Manager::MEDIA,
					),
				),
				'title_field' => '{{{ term }}}',
				'condition'   => array( 'image_source' => 'override' ),
			)
		);
		$this->add_control(
			'show_blurb',
			array(
				'label'        => __( 'Show the term description', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Show the profile count', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'card_overlay',
			array(
				'label'      => __( 'Extra image tint (%)', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min' => 0,
						'max' => 90,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 0,
				),
			)
		);
		$this->add_control(
			'card_ratio',
			array(
				'label'   => __( 'Image ratio', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''         => __( 'Theme default', 'perfect10' ),
					'16 / 10'  => '16:10',
					'3 / 4'    => '3:4',
					'4 / 3'    => '4:3',
					'1 / 1'    => '1:1',
					'16 / 9'   => '16:9',
				),
			)
		);
		$this->add_control(
			'card_focal',
			array(
				'label'   => __( 'Image focal point', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''       => __( 'Centre', 'perfect10' ),
					'top'    => __( 'Top', 'perfect10' ),
					'bottom' => __( 'Bottom', 'perfect10' ),
					'left'   => __( 'Left', 'perfect10' ),
					'right'  => __( 'Right', 'perfect10' ),
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Build the card argument array from saved settings.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function term_card_args( $s ) {
		$args = array(
			'show_blurb' => ! isset( $s['show_blurb'] ) || 'yes' === $s['show_blurb'],
			'show_count' => ! isset( $s['show_count'] ) || 'yes' === $s['show_count'],
			'overlay'    => isset( $s['card_overlay']['size'] ) ? (int) $s['card_overlay']['size'] : 0,
			'ratio'      => isset( $s['card_ratio'] ) ? (string) $s['card_ratio'] : '',
			'focal'      => isset( $s['card_focal'] ) ? (string) $s['card_focal'] : '',
		);

		if ( isset( $s['image_source'] ) && 'override' === $s['image_source'] ) {
			$args['images'] = array();
			foreach ( (array) ( isset( $s['card_images'] ) ? $s['card_images'] : array() ) as $row ) {
				$term  = isset( $row['term'] ) ? sanitize_title( $row['term'] ) : '';
				$image = isset( $row['image']['id'] ) ? absint( $row['image']['id'] ) : 0;
				if ( $term && $image ) {
					$args['images'][ $term ] = $image;
				}
			}
			$args['fallback'] = isset( $s['fallback_image']['id'] ) ? absint( $s['fallback_image']['id'] ) : 0;
		}

		return $args;
	}

	/**
	 * Shared button style controls.
	 *
	 * @param string $selector Button selector.
	 * @param string $section  Controls section ID.
	 */
	protected function add_button_style_controls( $selector = '{{WRAPPER}} .p10-btn', $section = 'p10_button_style' ) {
		$this->start_controls_section(
			$section,
			array(
				'label' => __( 'Buttons', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'p10_button_text',
			array(
				'label'     => __( 'Button text colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color:{{VALUE}};',
				),
			)
		);
		$this->add_control(
			'p10_button_border',
			array(
				'label'     => __( 'Button border colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'border-color:{{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'p10_button_radius',
			array(
				'label'      => __( 'Button radius', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					$selector => 'border-radius:{{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * A friendly editor-only note for widgets that need a profile context.
	 *
	 * @param string $message Message.
	 * @return string
	 */
	protected function editor_note( $message ) {
		if ( ! $this->is_editor_preview() ) {
			return '';
		}
		return '<p class="p10-elementor-note">' . esc_html( $message ) . '</p>';
	}
}

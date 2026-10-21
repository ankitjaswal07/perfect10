<?php
/**
 * Archive/page header.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Page head widget.
 */
class P10_Elementor_Page_Head extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-page-head';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Page Header', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-page-title';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'head',
			array(
				'label' => __( 'Page Header', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'source',
			array(
				'label'       => __( 'Title source', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'theme',
				'options'     => array(
					'theme'  => __( 'Current archive/page (automatic)', 'perfect10' ),
					'custom' => __( 'Custom title', 'perfect10' ),
				),
				'description' => __( 'Automatic mode uses the queried term, archive title or page title and its description.', 'perfect10' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'blurb',
			array(
				'label'     => __( 'Blurb', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'kicker',
			array(
				'label' => __( 'Kicker', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'show_breadcrumb',
			array(
				'label'        => __( 'Show breadcrumb', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$args  = array();
		$title = '';

		if ( isset( $s['source'] ) && 'custom' === $s['source'] ) {
			$title = isset( $s['title'] ) ? $s['title'] : '';
			if ( $title ) {
				$args['title'] = $title;
			}
			$args['blurb'] = isset( $s['blurb'] ) ? $s['blurb'] : '';
		}

		if ( ! empty( $s['kicker'] ) ) {
			$args['kicker'] = $s['kicker'];
		}

		if ( ! isset( $s['show_breadcrumb'] ) || 'yes' === $s['show_breadcrumb'] ) {
			$args['breadcrumb'] = true;
		}

		get_template_part( 'template-parts/page-head', null, $args );
	}
}

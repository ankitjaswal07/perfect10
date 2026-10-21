<?php
/**
 * Breadcrumb trail.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Breadcrumb widget.
 */
class P10_Elementor_Breadcrumb extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-breadcrumb';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Breadcrumb', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-yoast-breadcrumbs';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'breadcrumb',
			array(
				'label' => __( 'Breadcrumb', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'home_label',
			array(
				'label'   => __( 'Home label', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Home', 'perfect10' ),
			)
		);
		$this->add_control(
			'align',
			array(
				'label'   => __( 'Alignment', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array(
						'title' => __( 'Left', 'perfect10' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'perfect10' ),
						'icon'  => 'eicon-text-align-center',
					),
				),
				'default' => 'left',
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<div class="p10-breadcrumb-wrap p10-breadcrumb-wrap--' . esc_attr( isset( $s['align'] ) ? $s['align'] : 'left' ) . '">';
		p10_breadcrumb(
			array(
				'home_label' => isset( $s['home_label'] ) ? $s['home_label'] : __( 'Home', 'perfect10' ),
			)
		);
		echo '</div>';
	}
}

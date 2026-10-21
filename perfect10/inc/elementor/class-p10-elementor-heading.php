<?php
/**
 * Editorial section heading.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Heading widget.
 */
class P10_Elementor_Heading extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-heading';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Section Heading', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-heading';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->add_heading_controls( 'heading', array( 'show_heading' => 'yes' ), 'p10_heading', '' );

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
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
		$this->add_control(
			'show_rule',
			array(
				'label'        => __( 'Show gold rule above', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);
		$this->end_controls_section();

		$this->add_heading_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$s['heading_center'] = isset( $s['align'] ) && 'center' === $s['align'] ? 'yes' : '';
		$html = $this->get_heading_html( $s, 'heading' );
		if ( ! $html ) {
			return;
		}
		$classes = 'p10-heading-block';
		if ( ! empty( $s['show_rule'] ) && 'yes' === $s['show_rule'] ) {
			$classes .= ' p10-heading-block--rule';
		}
		echo '<div class="' . esc_attr( $classes ) . '">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- template part escapes its own output.
	}
}

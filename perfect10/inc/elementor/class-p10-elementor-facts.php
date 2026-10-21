<?php
/**
 * Current-profile facts widget.
 *
 * Name `p10-facts` is preserved from the original theme. New controls let an
 * editor choose a specific profile or keep the widget bound to the profile
 * being viewed, which is what makes the widget useful on Elementor-built
 * profile pages as well as in the editor preview.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Profile facts widget.
 */
class P10_Elementor_Facts extends P10_Elementor_Widget {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-facts';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Profile Facts', 'perfect10' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'facts',
			array(
				'label' => __( 'Profile facts', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$schema  = p10_profile_field_schema();
		$allowed = array( 'age', 'height_cm', 'hair', 'eyes', 'dress', 'languages' );
		$options = array();
		foreach ( $allowed as $key ) {
			if ( isset( $schema[ $key ] ) ) {
				$options[ $key ] = $schema[ $key ]['label'];
			}
		}

		$this->add_control(
			'profile_id',
			array(
				'label'       => __( 'Profile', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => p10_profile_choices(),
				'default'     => 0,
				'description' => __( 'Leave on “Select a profile” to follow the profile currently being viewed.', 'perfect10' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'   => __( 'Small heading', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'The details', 'perfect10' ),
			)
		);
		$this->add_control(
			'fields',
			array(
				'label'    => __( 'Visible fields', 'perfect10' ),
				'type'     => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => $options,
				'default'  => array_keys( $options ),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'bare',
				'options' => array(
					'bare'    => __( 'Bare definition list (original behaviour)', 'perfect10' ),
					'section' => __( 'Inside a Perfect 10 section', 'perfect10' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'facts_style',
			array(
				'label' => __( 'Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Value colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-profile-specs dd' => 'color:{{VALUE}};',
				),
			)
		);
		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Label colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-profile-specs dt' => 'color:{{VALUE}};',
				),
			)
		);
		if ( class_exists( '\Elementor\Group_Control_Typography' ) ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'facts_typography',
					'label'    => __( 'Typography', 'perfect10' ),
					'selector' => '{{WRAPPER}} .p10-profile-specs',
				)
			);
		}
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s        = $this->get_settings_for_display();
		$id       = $this->resolve_profile_id( $s );
		$schema   = p10_profile_field_schema();
		$allowed  = array( 'age', 'height_cm', 'hair', 'eyes', 'dress', 'languages' );
		$selected = isset( $s['fields'] ) ? (array) $s['fields'] : $allowed;
		$selected = array_values( array_intersect( $allowed, $selected ) );
		$wrap     = isset( $s['layout'] ) ? $s['layout'] : 'bare';

		if ( ! $id ) {
			echo p10_empty_state( __( 'Choose a published profile in the widget settings, or open this widget on a profile page.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		$items = array();
		foreach ( $selected as $key ) {
			if ( ! isset( $schema[ $key ] ) ) {
				continue;
			}
			$value = get_post_meta( $id, 'p10_' . $key, true );
			if ( '' !== $value && is_scalar( $value ) ) {
				$label = isset( $schema[ $key ]['label'] ) ? $schema[ $key ]['label'] : $key;
				if ( 'height_cm' === $key ) {
					$value = $value . ' cm';
				}
				$items[] = array(
					'label' => $label,
					'value' => $value,
				);
			}
		}

		if ( ! $items ) {
			echo p10_empty_state( __( 'This profile has no published facts yet.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		if ( 'section' === $wrap ) {
			echo '<section class="p10-section p10-section--tight"><div class="p10-shell">';
			if ( ! empty( $s['title'] ) ) {
				echo '<h3 class="p10-eyebrow"><span class="p10-eyebrow__text">' . esc_html( $s['title'] ) . '</span></h3>';
			}
		}

		echo '<dl class="p10-profile-specs">';
		foreach ( $items as $item ) {
			echo '<dt>' . esc_html( $item['label'] ) . '</dt><dd>' . esc_html( $item['value'] ) . '</dd>';
		}
		echo '</dl>';

		if ( 'section' === $wrap ) {
			echo '</div></section>';
		}
	}
}

<?php
/**
 * Telephone call to action.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Phone widget.
 */
class P10_Elementor_Phone extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-phone';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Phone', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-phone';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'phone',
			array(
				'label' => __( 'Phone', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Leave blank to show the number from the Customizer.', 'perfect10' ),
			)
		);
		$this->add_control(
			'note',
			array(
				'label' => __( 'Note under the number', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => array(
					'inline' => __( 'Inline', 'perfect10' ),
					'center' => __( 'Centered call band', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'section_style',
			array(
				'label'   => __( 'Section treatment', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''           => __( 'Plain', 'perfect10' ),
					'panel'      => __( 'Panel background', 'perfect10' ),
					'gold-line'  => __( 'Gold hairline', 'perfect10' ),
					'panel-gold' => __( 'Panel + gold hairline', 'perfect10' ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'number_color',
			array(
				'label'     => __( 'Number colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-phone' => 'color:{{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! p10_phone_display() ) {
			echo p10_empty_state( __( 'Add a phone number in Appearance → Customize → Perfect 10 → Contact.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		$style = isset( $s['section_style'] ) ? $s['section_style'] : '';
		$class = 'p10-section p10-section--tight';
		if ( in_array( $style, array( 'panel', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--panel';
		}
		if ( in_array( $style, array( 'gold-line', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--gold-line';
		}
		$centered = isset( $s['layout'] ) && 'center' === $s['layout'];
		?>
		<section class="<?php echo esc_attr( $class ); ?>">
			<div class="p10-shell">
				<div class="p10-phone-band<?php echo $centered ? ' p10-phone-band--center' : ''; ?>">
					<?php if ( ! empty( $s['label'] ) ) : ?>
						<p class="p10-eyebrow"><span class="p10-eyebrow__text"><?php echo esc_html( $s['label'] ); ?></span></p>
					<?php endif; ?>
					<a class="p10-phone-band__number" href="<?php echo esc_url( p10_phone_href() ); ?>"><?php echo esc_html( p10_phone_display() ); ?></a>
					<?php if ( ! empty( $s['note'] ) ) : ?>
						<p class="p10-phone-band__note"><?php echo esc_html( $s['note'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

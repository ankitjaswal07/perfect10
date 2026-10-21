<?php
/**
 * Enquiry / booking / listing form.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Form widget.
 */
class P10_Elementor_Form extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-form';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Enquiry Form', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'form',
			array(
				'label' => __( 'Form', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'form_type',
			array(
				'label'   => __( 'Form', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'booking',
				'options' => array(
					'booking'   => __( 'Reservation / booking', 'perfect10' ),
					'register'  => __( 'Create an independent listing', 'perfect10' ),
					'concierge' => __( 'Concierge enquiry', 'perfect10' ),
					'profile'   => __( 'Enquire about a profile', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'heading_source',
			array(
				'label'   => __( 'Heading', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'   => __( 'No heading (form only)', 'perfect10' ),
					'custom' => __( 'Custom heading', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'heading',
			array(
				'label'     => __( 'Heading', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'heading_source' => 'custom' ),
			)
		);
		$this->add_control(
			'heading_accent',
			array(
				'label'     => __( 'Italic accent', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'heading_source' => 'custom' ),
			)
		);
		$this->add_control(
			'intro',
			array(
				'label'     => __( 'Intro copy', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'condition' => array( 'heading_source' => 'custom' ),
			)
		);
		$this->add_control(
			'submit',
			array(
				'label'   => __( 'Submit button label', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Send request', 'perfect10' ),
			)
		);
		$this->add_control(
			'profile_id',
			array(
				'label'       => __( 'Profile', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => p10_profile_choices(),
				'default'     => 0,
				'description' => __( 'Only used by the profile enquiry form. Leave on “Follow the page” on a profile page.', 'perfect10' ),
				'condition'   => array( 'form_type' => 'profile' ),
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
		$this->add_control(
			'show_privacy',
			array(
				'label'        => __( 'Show privacy note', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->end_controls_section();

		p10_register_spacing_controls( $this, '{{WRAPPER}} .p10-section' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$type = isset( $s['form_type'] ) ? $s['form_type'] : 'booking';

		if ( ! p10_form_schema( $type ) ) {
			echo p10_empty_state( __( 'This form type is unavailable.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		$profile_id = 0;
		if ( 'profile' === $type ) {
			$profile_id = ! empty( $s['profile_id'] ) ? (int) $s['profile_id'] : $this->current_profile();
		}

		if ( $profile_id && 'p10_profile' !== get_post_type( $profile_id ) ) {
			$profile_id = 0;
		}

		$style = isset( $s['section_style'] ) ? $s['section_style'] : '';
		$class = 'p10-section p10-section--tight';
		if ( in_array( $style, array( 'panel', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--panel';
		}
		if ( in_array( $style, array( 'gold-line', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--gold-line';
		}
		?>
		<section class="<?php echo esc_attr( $class ); ?>">
			<div class="p10-shell">
				<div class="p10-form-wrap">
					<?php if ( 'custom' === ( $s['heading_source'] ?? 'none' ) ) : ?>
						<header class="p10-section-head">
							<div class="p10-section-head__main">
								<?php echo p10_get_heading_html( isset( $s['heading'] ) ? $s['heading'] : '', isset( $s['heading_accent'] ) ? $s['heading_accent'] : '', 'h2', 'p10-display-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php if ( ! empty( $s['intro'] ) ) : ?>
									<p class="p10-lede"><?php echo esc_html( $s['intro'] ); ?></p>
								<?php endif; ?>
							</div>
						</header>
					<?php endif; ?>

					<?php
					p10_form_status( $type );
					p10_the_form(
						$type,
						array(
							'submit'     => isset( $s['submit'] ) ? $s['submit'] : '',
							'redirect'   => get_permalink() ? get_permalink() : '',
							'profile_id' => $profile_id,
						)
					);

					if ( ( ! isset( $s['show_privacy'] ) || 'yes' === $s['show_privacy'] ) && p10_form_privacy_text() ) :
						?>
						<p class="p10-form__privacy p10-form-wrap__privacy"><?php echo esc_html( p10_form_privacy_text() ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

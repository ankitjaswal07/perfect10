<?php
/**
 * Perfect 10 button.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Button widget.
 */
class P10_Elementor_Button extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-button';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Button', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'button',
			array(
				'label' => __( 'Button', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'text',
			array(
				'label'   => __( 'Label', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Explore Profiles', 'perfect10' ),
			)
		);
		$this->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'perfect10' ),
				'default'     => array( 'url' => '' ),
			)
		);
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'gold',
				'options' => array(
					'gold'  => __( 'Gold', 'perfect10' ),
					'ghost' => __( 'Ghost / outline', 'perfect10' ),
					'link'  => __( 'Text link with arrow', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'size',
			array(
				'label'   => __( 'Size', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''   => __( 'Default', 'perfect10' ),
					'sm' => __( 'Small', 'perfect10' ),
					'lg' => __( 'Large', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'block',
			array(
				'label'        => __( 'Full width', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
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
					'right'  => array(
						'title' => __( 'Right', 'perfect10' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default' => 'left',
			)
		);
		$this->end_controls_section();

		$this->add_button_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( empty( $s['text'] ) ) {
			return;
		}

		$attrs = p10_el_link_attrs( isset( $s['link'] ) ? $s['link'] : array(), p10_directory_url() );
		$style = isset( $s['style'] ) ? $s['style'] : 'gold';
		$size  = isset( $s['size'] ) ? $s['size'] : '';

		$classes = 'p10-btn';
		if ( 'gold' === $style ) {
			$classes .= ' p10-btn--gold';
		} elseif ( 'ghost' === $style ) {
			$classes .= ' p10-btn--ghost';
		} else {
			$classes .= ' p10-btn--text';
		}
		if ( $size ) {
			$classes .= ' p10-btn--' . sanitize_html_class( $size );
		}
		if ( ! empty( $s['block'] ) && 'yes' === $s['block'] ) {
			$classes .= ' p10-btn--block';
		}

		$align = isset( $s['align'] ) ? $s['align'] : 'left';
		?>
		<div class="p10-btn-wrap p10-btn-wrap--<?php echo esc_attr( $align ); ?>">
			<a class="<?php echo esc_attr( $classes ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by p10_el_link_attrs(). ?>>
				<?php echo esc_html( $s['text'] ); ?>
				<?php if ( 'link' === $style ) : ?>
					<span class="p10-link__arrow" aria-hidden="true">→</span>
				<?php endif; ?>
			</a>
		</div>
		<?php
	}
}

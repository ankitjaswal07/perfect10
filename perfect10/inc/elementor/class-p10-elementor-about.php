<?php
/**
 * Editorial about blocks.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * About widget.
 */
class P10_Elementor_About extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-about';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · About Blocks', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-editor-paragraph';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'about',
			array(
				'label' => __( 'About Blocks', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'content_source',
			array(
				'label'   => __( 'Content source', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'Perfect 10 Customizer', 'perfect10' ),
					'custom' => __( 'Override for this page', 'perfect10' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'number',
			array(
				'label' => __( 'Number', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$repeater->add_control(
			'kicker',
			array(
				'label' => __( 'Kicker', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label' => __( 'Heading', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$repeater->add_control(
			'accent',
			array(
				'label' => __( 'Italic accent', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$repeater->add_control(
			'copy',
			array(
				'label' => __( 'Copy', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXTAREA,
				'rows'  => 5,
			)
		);

		$this->add_control(
			'blocks',
			array(
				'label'       => __( 'Blocks', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'content_source' => 'custom' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! isset( $s['content_source'] ) || 'custom' !== $s['content_source'] ) {
			get_template_part( 'template-parts/about' );
			return;
		}

		$blocks = isset( $s['blocks'] ) ? (array) $s['blocks'] : array();
		if ( ! $blocks ) {
			echo p10_empty_state( __( 'Add at least one about block in the widget settings.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}
		?>
		<section class="p10-section p10-section--panel p10-section--gold-line">
			<div class="p10-shell">
				<div class="p10-about">
					<?php foreach ( $blocks as $i => $block ) : ?>
						<article class="p10-about__block p10-reveal" data-delay="<?php echo esc_attr( $i % 3 ); ?>">
							<?php if ( ! empty( $block['number'] ) ) : ?>
								<span class="p10-about__num" aria-hidden="true"><?php echo esc_html( $block['number'] ); ?></span>
							<?php else : ?>
								<span aria-hidden="true"></span>
							<?php endif; ?>
							<div class="p10-about__body">
								<?php if ( ! empty( $block['kicker'] ) ) : ?>
									<p class="p10-about__kicker"><?php echo esc_html( $block['kicker'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $block['title'] ) ) : ?>
									<?php p10_the_heading( $block['title'], isset( $block['accent'] ) ? $block['accent'] : '', 'h3', 'p10-display-md' ); ?>
								<?php endif; ?>
								<?php if ( ! empty( $block['copy'] ) ) : ?>
									<div class="p10-about__copy"><?php echo wp_kses_post( wpautop( $block['copy'] ) ); ?></div>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

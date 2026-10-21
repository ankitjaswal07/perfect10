<?php
/**
 * County / city search panel.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Search widget.
 */
class P10_Elementor_Search extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-search';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Search Panel', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'search_content',
			array(
				'label' => __( 'Search Panel', 'perfect10' ),
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
		$this->add_control(
			'kicker',
			array(
				'label'     => __( 'Kicker', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'     => __( 'Heading', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'accent',
			array(
				'label'     => __( 'Italic accent', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'label',
			array(
				'label'       => __( 'Field prompt', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Where are you looking?', 'perfect10' ),
				'condition'   => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'region',
			array(
				'label'     => __( 'Region caption', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'button',
			array(
				'label'     => __( 'Button label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Search', 'perfect10' ),
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'show_quick_links',
			array(
				'label'        => __( 'Show popular type links', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'anchor',
			array(
				'label'       => __( 'Anchor ID', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'p10-next',
				'description' => __( 'The hero scroll cue links to #p10-next by default.', 'perfect10' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'search_style',
			array(
				'label' => __( 'Panel Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'panel_bg',
			array(
				'label'     => __( 'Panel background', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-search__panel' => 'background-color:{{VALUE}};',
				),
			)
		);
		$this->add_control(
			'panel_border',
			array(
				'label'     => __( 'Panel border', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-search__panel' => 'border-color:{{VALUE}};',
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

		if ( ! isset( $s['content_source'] ) || 'custom' !== $s['content_source'] ) {
			get_template_part( 'template-parts/search-panel' );
			return;
		}

		$grouped   = p10_get_cities_by_county();
		$counties  = p10_get_counties();
		$home      = trailingslashit( home_url( '/' ) );
		$template  = $home . '__county__/__city__/';
		$directory = p10_directory_url();
		$anchor    = isset( $s['anchor'] ) ? sanitize_title( $s['anchor'] ) : '';
		?>
		<section class="p10-section p10-section--tight p10-search"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<div class="p10-shell">
				<div class="p10-search__panel p10-reveal">
					<div class="p10-search__head">
						<div>
							<?php if ( ! empty( $s['kicker'] ) ) : ?>
								<p class="p10-eyebrow"><span class="p10-eyebrow__text"><?php echo esc_html( $s['kicker'] ); ?></span></p>
							<?php endif; ?>
							<?php echo p10_get_heading_html( isset( $s['title'] ) ? $s['title'] : '', isset( $s['accent'] ) ? $s['accent'] : '', 'h2', 'p10-display-md' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php if ( ! empty( $s['region'] ) ) : ?>
							<p class="p10-search__caption">
								<?php echo esc_html( isset( $s['label'] ) ? $s['label'] : '' ); ?><br>
								<b><?php echo esc_html( $s['region'] ); ?></b>
							</p>
						<?php endif; ?>
					</div>

					<form class="p10-search__grid" method="get" action="<?php echo esc_url( $directory ); ?>" data-p10-search-form data-url-template="<?php echo esc_attr( $template ); ?>" role="search">
						<div class="p10-field">
							<label class="p10-field__label" for="p10-search-county"><?php esc_html_e( 'County', 'perfect10' ); ?></label>
							<select class="p10-select" id="p10-search-county" name="county" data-p10-county data-link-base="<?php echo esc_attr( $home . '__county__/' ); ?>">
								<option value=""><?php esc_html_e( 'All Counties', 'perfect10' ); ?></option>
								<?php foreach ( $counties as $county ) : ?>
									<option value="<?php echo esc_attr( $county->slug ); ?>"><?php echo esc_html( $county->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="p10-field">
							<label class="p10-field__label" for="p10-search-city"><?php esc_html_e( 'City', 'perfect10' ); ?></label>
							<select class="p10-select" id="p10-search-city" name="city" data-p10-city>
								<option value=""><?php esc_html_e( 'All Cities', 'perfect10' ); ?></option>
								<?php foreach ( $grouped as $county_slug => $group ) : ?>
									<?php if ( empty( $group['cities'] ) ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<optgroup label="<?php echo esc_attr( $group['label'] ); ?>" data-county="<?php echo esc_attr( $county_slug ); ?>">
										<?php foreach ( $group['cities'] as $city ) : ?>
											<option value="<?php echo esc_attr( $city->slug ); ?>"><?php echo esc_html( $city->name ); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="p10-field">
							<span class="p10-field__label p10-sr"><?php esc_html_e( 'Submit', 'perfect10' ); ?></span>
							<button class="p10-btn p10-btn--gold" type="submit"><?php echo esc_html( isset( $s['button'] ) ? $s['button'] : __( 'Search', 'perfect10' ) ); ?></button>
						</div>
					</form>

					<?php if ( ! isset( $s['show_quick_links'] ) || 'yes' === $s['show_quick_links'] ) : ?>
						<?php $quick_links = p10_get_categories(); ?>
						<?php if ( $quick_links ) : ?>
							<div class="p10-search__hint">
								<span><?php esc_html_e( 'Popular:', 'perfect10' ); ?></span>
								<?php foreach ( array_slice( $quick_links, 0, 5 ) as $category ) : ?>
									<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
								<?php endforeach; ?>
								<a href="<?php echo esc_url( p10_directory_url( array( 'premium' => 1 ) ) ); ?>"><?php esc_html_e( 'Premium', 'perfect10' ); ?></a>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

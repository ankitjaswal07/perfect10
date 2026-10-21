<?php
/** Dynamic Elementor component. */
if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

class P10_Elementor_Gallery extends P10_Elementor_Widget {
	public function get_name() {
		return 'p10-gallery'; }
	public function get_title() {
		return __( 'P10 Current Profile Gallery', 'perfect10' ); }
	protected function register_controls() {
		$this->start_controls_section( 'gallery', array( 'label' => __( 'Linked Media Library images', 'perfect10' ) ) );
		$this->columns();
		$this->add_control(
			'size',
			array(
				'label'   => __( 'Image size', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'p10-card'    => 'Profile card',
					'p10-gallery' => 'Gallery',
					'full'        => 'Full',
				),
				'default' => 'p10-gallery',
			)
		);
		$this->add_control(
			'link',
			array(
				'label' => __( 'Link to full image', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::SWITCHER,
			)
		);
		$this->end_controls_section();
	}
	protected function render() {
		$id = $this->current_profile();
		if ( ! $id ) {
			echo '<p>' . esc_html__( 'Select a published profile preview to display its gallery.', 'perfect10' ) . '</p>';
			return; }
		$images = p10_profile_gallery( get_post( $id ) );
		if ( ! $images ) {
			echo '<p>' . esc_html__( 'Photos unavailable — placeholder, not an imported gallery.', 'perfect10' ) . '</p>';
			return; }
		$s = $this->get_settings_for_display();
		echo '<div class="p10-grid p10-dynamic-grid">';
		foreach ( $images as $image ) {
			if ( ! wp_attachment_is_image( $image ) ) {
				continue; }
			if ( 'yes' === $s['link'] ) {
				echo '<a href="' . esc_url( wp_get_attachment_url( $image ) ) . '">'; }
			echo wp_get_attachment_image( $image, $s['size'] );
			if ( 'yes' === $s['link'] ) {
				echo '</a>'; }
		}
		echo '</div>';
	}
}

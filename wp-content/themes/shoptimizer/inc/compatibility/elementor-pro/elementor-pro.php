<?php

/**
 * Elementor Pro compatibility.
 *
 * @package Shoptimizer
 * @since Shoptimizer 1.0.0
 */

/* Ensures that a custom single product template in Elementor Pro loads the correct CSS */
function enqueue_shoptimizer_elementor_editor_styles() {
    // Check if Elementor editor is active
    if ( Elementor\Plugin::$instance->editor->is_edit_mode() || Elementor\Plugin::$instance->preview->is_preview_mode() ) {
        wp_enqueue_style(
            'custom-elementor-editor-css',
            get_template_directory_uri() . '/assets/css/main/product.min.css',
            array(),
            '1.0.0',
            'all'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_shoptimizer_elementor_editor_styles' );
<?php
/**
 * Shoptimizer shortcodes.
 *
 * @package Shoptimizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Accordion shortcode, used on PDP short descriptions.
 * Usage: [cg_accordion title="Delivery and Returns"]Some content[/cg_accordion]
 */
function shoptimizer_accordion_shortcode( $atts = array(), $content = null ) {
  
    extract(shortcode_atts(array(
     'title' => '',
     'open' => ''
    ), $atts));
    
	$content = trim($content,'<p></p>');

    return '<details ' . $open . '><summary>'. $title .'</summary><div class="cg-accordion-item">' . $content . '</div></details>';
}
add_shortcode('cg_accordion', 'shoptimizer_accordion_shortcode');
<?php

/**
 * CommerceGurus Product Filters Plugin
 *
 * @package Shoptimizer
 * @since Shoptimizer 1.0.0
 */

/* CommerceGurus Product Filters filters, horizontal layout - include a sticky class when stuck */
add_action( 'woocommerce_after_shop_loop', 'shoptimizer_commercegurus_product_filters_horizontal_sticky', 80 );

if ( ! function_exists( 'shoptimizer_commercegurus_product_filters_horizontal_sticky' ) ) {
   function shoptimizer_commercegurus_product_filters_horizontal_sticky() {

         $shoptimizer_layout_woocommerce_sidebar = '';
         $shoptimizer_layout_woocommerce_sidebar = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );

         if ( 'no-woocommerce-sidebar' == $shoptimizer_layout_woocommerce_sidebar ) {

         $commercegurus_product_filters_horizontal_sticky_js  = '';
         $commercegurus_product_filters_horizontal_sticky_js .= "
var observer = new IntersectionObserver(function(entries) {
            if(entries[0].intersectionRatio === 0)
               document.querySelector('#cgkitpf-horizontal').classList.add('is-pinned');
            else if(entries[0].intersectionRatio === 1)
               document.querySelector('#cgkitpf-horizontal').classList.remove('is-pinned');
         }, { threshold: [0,1] });

         observer.observe(document.querySelector('.cgkitpf-horizontal-top'));
      ";
         wp_add_inline_script( 'shoptimizer-main', $commercegurus_product_filters_horizontal_sticky_js );   

         }   
      ?>
      <?php
   }
}
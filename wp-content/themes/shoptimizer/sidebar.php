<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package shoptimizer
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}

$shoptimizer_layout_woocommerce_sidebar	 = '';
$shoptimizer_layout_woocommerce_sidebar	 = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );
?>

<?php if ( 'no-woocommerce-sidebar' !== $shoptimizer_layout_woocommerce_sidebar ) { ?>
<div class="secondary-wrapper">
    <div id="secondary" class="widget-area" role="complementary">
    	<?php dynamic_sidebar( 'sidebar-1' ); ?>
    </div><!-- #secondary -->
    <button class="filters close-drawer" aria-label="Close filters">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>
<?php } ?>

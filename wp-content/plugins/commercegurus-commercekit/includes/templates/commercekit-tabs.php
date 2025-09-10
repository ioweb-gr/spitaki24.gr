<?php
/**
 *
 * CommerceKit Single Product tabs
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product, $cgkit_elmpro_sticky_atc;

$options      = get_option( 'commercekit', array() );
$flags        = commercekit_feature_flags()->get_flags();
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
$product_id   = $product ? $product->get_id() : 0;
$enable_desk  = defined( 'COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE : ( isset( $flags['sticky_atc_desktop'] ) && 1 === (int) $flags['sticky_atc_desktop'] ? true : false );
$enable_mobi  = defined( 'COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE : ( isset( $flags['sticky_atc_mobile'] ) && 1 === (int) $flags['sticky_atc_mobile'] ? true : false );

$disable_sticky_atc = 0;
if ( $product_id ) {
	$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
}
$cgkit_sticky_atc = false;
if ( $product && commercekit_sticky_atc_is_allowed_product_type( $product ) && $product->is_in_stock() && 1 !== $disable_sticky_atc ) {
	$cgkit_sticky_atc = true;
}
$sticky_atc_label = isset( $options['sticky_atc_label'] ) && ! empty( $options['sticky_atc_label'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $options['sticky_atc_label'] ) ) : commercekit_get_default_settings( 'sticky_atc_label' );

if ( isset( $cgkit_elmpro_sticky_atc ) && true === $cgkit_elmpro_sticky_atc ) {
	$enable_desk = false;
	$enable_mobi = false;
}

if ( ! empty( $product_tabs ) ) {
	?>
	<div class="commercekit-atc-sticky-tabs cgkit-atc-product-<?php echo esc_attr( $product->get_type() ); ?>">
		<ul class="commercekit-atc-tab-links">
			<li id="cgkit-tab-commercekit-gallery-title" class="active">
				<a class="commercekit-atc-tab" href="#" data-id="#cgkit-tab-commercekit-gallery">
					<?php echo esc_html( $sticky_atc_label ); ?>
				</a>
			</li>
			<?php foreach ( $product_tabs as $key => $product_tab ) { ?>
				<li id="cgkit-tab-<?php echo esc_attr( $key ); ?>-title">
					<a class="commercekit-atc-tab" href="#cgkit-tab-<?php echo esc_attr( $key ); ?>" data-id="#cgkit-tab-<?php echo esc_attr( $key ); ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
					</a>
				</li>
			<?php } ?>
			<?php if ( $cgkit_sticky_atc && $enable_desk ) { ?>
				<li id="cgkit-tab-commercekit-sticky-atc-title">
					<button type="button" class="sticky-atc_button button" aria-expanded="false">
						<?php if ( $product->is_type( 'external' ) ) { ?>
							<?php echo esc_attr( $product->single_add_to_cart_text() ); ?>
						<?php } elseif ( $product->is_type( 'subscription' ) || $product->is_type( 'variable-subscription' ) ) { ?>
							<?php echo esc_attr__( 'Sign up', 'commercegurus-commercekit' ); ?>
						<?php } else { ?>
							<?php echo esc_attr__( 'Add to cart', 'woocommerce' ); ?>
						<?php } ?>
					</button>
				</li>
			<?php } ?>
		</ul>
	</div>
	<div class="woocommerce-tabs wc-tabs-wrapper" id="commercekit-atc-tabs-wrap">
		<?php foreach ( $product_tabs as $key => $product_tab ) { ?>
			<div class="woocommerce-Tabs-panel commercekit-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content" id="cgkit-tab-<?php echo esc_attr( $key ); ?>">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					if ( is_array( $product_tab['callback'] ) ) {
						$tab_obj = reset( $product_tab['callback'] );
						if ( $tab_obj && ( $tab_obj instanceof YITH_WCTM_Frontend || $tab_obj instanceof YITH_WCTM_Frontend_Premium ) ) {
							echo '<h2 class="sticky-atc-heading">' . wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ) . '</h2>';
						}
					} elseif ( is_string( $product_tab['callback'] ) && 'sayspotwc_customize_review_tab_callback' === $product_tab['callback'] ) {
						echo '<h2 class="sticky-atc-heading">' . wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ) . '</h2>';
					}
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		<?php } ?>

		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>

	<?php if ( $cgkit_sticky_atc && $enable_mobi ) { ?>
		<div id="cgkit-mobile-commercekit-sticky-atc" class="cgkit-mobile-atc-product-<?php echo esc_attr( $product->get_type() ); ?>">
			<button type="button" class="sticky-atc_button button" aria-expanded="false">
				<?php if ( $product->is_type( 'external' ) ) { ?>
					<?php echo esc_attr( $product->single_add_to_cart_text() ); ?>
				<?php } elseif ( $product->is_type( 'subscription' ) || $product->is_type( 'variable-subscription' ) ) { ?>
					<?php echo esc_attr__( 'Sign up', 'commercegurus-commercekit' ); ?>
				<?php } else { ?>
					<?php echo esc_attr__( 'Add to cart', 'woocommerce' ); ?>
				<?php } ?>
			</button>
		</div>
	<?php } ?>

<?php } ?>

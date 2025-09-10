<?php
/**
 *
 * Admin Wishlist
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<div id="settings-content">
<div class="dashboard postbox content-box">
	<h2 class="order-bump-statistics--heading"><span class="table-heading"><?php esc_html_e( 'Order Bump Statistics', 'commercegurus-commercekit' ); ?></span> <button type="button" class="button button-primary" id="reset-obp-statistics" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to reset these statistics?', 'commercegurus-commercekit' ); ?>')){reset_order_bump_statistics();}"><?php esc_html_e( 'Reset Statistics', 'commercegurus-commercekit' ); ?></button></h2>
	<div class="inside">
	<?php
	$order_bump_stats_views  = (int) get_option( 'commercekit_obp_views' );
	$order_bump_stats_clicks = (int) get_option( 'commercekit_obp_clicks' );
	$order_bump_stats_sales  = (int) get_option( 'commercekit_obp_sales' );
	$order_bump_stats_price  = (float) get_option( 'commercekit_obp_sales_revenue' );
	$order_bump_stats_rate1  = 0 !== $order_bump_stats_views ? number_format( ( $order_bump_stats_clicks / $order_bump_stats_views ) * 100, 0 ) : 0;
	$order_bump_stats_rate2  = 0 !== $order_bump_stats_clicks ? number_format( ( $order_bump_stats_sales / $order_bump_stats_clicks ) * 100, 0 ) : 0;
	?>
		<ul class="order-bump-statistics">
			<li>
				<div class="title"><?php esc_html_e( 'Impressions', 'commercegurus-commercekit' ); ?></div>
				<div class="text-large" id="obp-impressions"><?php echo esc_attr( number_format( $order_bump_stats_views, 0 ) ); ?></div>
			</li>
			<li>
				<div class="title"><?php esc_html_e( 'Revenue', 'commercegurus-commercekit' ); ?></div>
				<div class="text-large"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?><span id="obp-revenue"><?php echo esc_attr( number_format( $order_bump_stats_price, 2 ) ); ?></span></div>
			</li>
			<li>
				<div class="title"><?php esc_html_e( 'Additional Sales', 'commercegurus-commercekit' ); ?></div>
				<div class="text-large" id="obp-sales"><?php echo esc_attr( number_format( $order_bump_stats_sales, 0 ) ); ?></div>
			</li>
			<li>
				<div class="title" data-clicks="<?php echo esc_attr( $order_bump_stats_clicks ); ?>"><?php esc_html_e( 'Click Rate', 'commercegurus-commercekit' ); ?></div>
				<div class="text-small" id="obp-click-rate"><?php echo esc_attr( $order_bump_stats_rate1 ); ?>%</div>
				<div class="progress-bar"><span id="obp-click-rate-percent" style="width: <?php echo esc_attr( $order_bump_stats_rate1 ); ?>%;"></span></div>
			</li>
			<li>
				<div class="title"><?php esc_html_e( 'Conversion Rate', 'commercegurus-commercekit' ); ?></div>
				<div class="text-small" id="obp-covert-rate"><?php echo esc_attr( $order_bump_stats_rate2 ); ?>%</div>
				<div class="progress-bar"><span id="obp-covert-rate-percent" style="width: <?php echo esc_attr( $order_bump_stats_rate2 ); ?>%;"></span></div>
			</li>
		</ul>
	</div>
</div>

<div class="dashboard postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'CommerceKit Features', 'commercegurus-commercekit' ); ?></span></h2>
	<div class="inside">

		<div class="ckit-features-grid">

			<!-- Ajax Search -->
			<section <?php echo isset( $commercekit_options['ajax_search'] ) && 1 === (int) $commercekit_options['ajax_search'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
				<h3><a href="?page=commercekit&tab=ajax-search"><?php esc_html_e( 'Ajax Search', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=ajax-search" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<p><?php esc_html_e( 'Instant search suggestions helps customers save time and find products faster.', 'commercegurus-commercekit' ); ?></p>
				<div class="ckit-perf">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
					</svg>
					<div>
					<strong><?php esc_html_e( '9Kb of HTML', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on all pages', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

			</section>

			<!-- Product Attributes -->
			<section <?php echo isset( $commercekit_options['pdp_attributes_gallery'] ) && 1 === (int) $commercekit_options['pdp_attributes_gallery'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=pdp-attributes-gallery"><?php esc_html_e( 'Attributes Gallery', 'commercegurus-commercekit' ); ?> </a></h3>
					<a href="?page=commercekit&tab=pdp-attributes-gallery" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">

					<p><?php esc_html_e( 'Further improve your gallery with the ability to assign images on an attribute basis.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( 'Improves gallery usability', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product pages', 'commercegurus-commercekit' ); ?>
						</div>
					</div>

				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_pdp_gallery'] ) && 1 === (int) $commercekit_options['widget_pos_pdp_gallery'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

			<!-- Attributes swatches -->
			<section <?php echo isset( $commercekit_options['attribute_swatches'] ) && 1 === (int) $commercekit_options['attribute_swatches'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=attribute-swatches"><?php esc_html_e( 'Attribute Swatches', 'commercegurus-commercekit' ); ?> </a></h3>
					<a href="?page=commercekit&tab=attribute-swatches" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">

					<p><?php esc_html_e( 'Replace standard variation dropdowns with color, image, and button swatches.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( 'Improved usability', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product and catalog pages', 'commercegurus-commercekit' ); ?>
						</div>
					</div>

				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

			</section>

			<!-- Countdown Timers -->
			<section <?php echo isset( $commercekit_options['countdown_timer'] ) && 1 === (int) $commercekit_options['countdown_timer'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=countdown-timer"><?php esc_html_e( 'Countdowns', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=countdown-timer" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<p><?php esc_html_e( 'Allows you to run time-limited promotions to create urgency and drive more clicks.', 'commercegurus-commercekit' ); ?></p>

				<div class="ckit-perf">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
					</svg>
					<div>
					<strong><?php esc_html_e( '7Kb of HTML', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product and checkout pages', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( ( isset( $commercekit_options['widget_pos_countdown'] ) && 1 === (int) $commercekit_options['widget_pos_countdown'] ) || ( isset( $commercekit_options['widget_pos_countdown2'] ) && 1 === (int) $commercekit_options['widget_pos_countdown2'] ) ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

			<!-- Free Shipping Notification -->
			<?php
				$enable_fsn_cart_page = isset( $commercekit_options['fsn_cart_page'] ) && 1 === (int) $commercekit_options['fsn_cart_page'] ? 1 : 0;
				$enable_fsn_mini_cart = isset( $commercekit_options['fsn_mini_cart'] ) && 1 === (int) $commercekit_options['fsn_mini_cart'] ? 1 : 0;
				$enable_fsn_shortcode = isset( $commercekit_options['widget_pos_fsn'] ) && 1 === (int) $commercekit_options['widget_pos_fsn'] ? 1 : 0;
			?>
			<section <?php echo ( $enable_fsn_cart_page || $enable_fsn_mini_cart || $enable_fsn_shortcode ) ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=free-shipping-notification"><?php esc_html_e( 'Free Shipping Notification', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=free-shipping-notification" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">
					<p><?php esc_html_e( 'Increase order revenue by showing your customers just how close they are to your free shipping threshold.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( 'Improves conversions', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on cart page and mini cart', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_fsn'] ) && 1 === (int) $commercekit_options['widget_pos_fsn'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

			<!-- Order bumps -->
			<section <?php echo ( isset( $commercekit_options['order_bump'] ) && 1 === (int) $commercekit_options['order_bump'] ) || ( isset( $commercekit_options['order_bump_mini'] ) && 1 === (int) $commercekit_options['order_bump_mini'] ) ? 'class="active"' : ''; ?>>


				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=order-bump"><?php esc_html_e( 'Order Bump', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=order-bump" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">

					<p><?php esc_html_e( 'Enables a customer to add an additional item to the cart, before they complete an order.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( '3Kb of HTML', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on the checkout', 'commercegurus-commercekit' ); ?>
						</div>
					</div>

				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_obp'] ) && 1 === (int) $commercekit_options['widget_pos_obp'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

			<!-- Product Badges -->
			<section <?php echo isset( $commercekit_options['store_badge'] ) && 1 === (int) $commercekit_options['store_badge'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=badge"><?php esc_html_e( 'Product Badges', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=badge" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">
					<p><?php esc_html_e( 'Enhance key product features, and highlight promotions and products with limited-time offers.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( 'Improves conversions', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product and catalog pages', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

			</section>

			<!-- Product gallery -->
			<section <?php echo isset( $commercekit_options['pdp_gallery'] ) && 1 === (int) $commercekit_options['pdp_gallery'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=pdp-gallery"><?php esc_html_e( 'Product Gallery', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=pdp-gallery" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">

					<p><?php esc_html_e( 'An improved product gallery with multiple layout options and video support. Built for performance.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( '80%+ lighter than the core gallery', 'commercegurus-commercekit' ); ?></strong> <a href="https://www.commercegurus.com/woocommerce-product-gallery-speed/"><?php esc_html_e( 'Read more', 'commercegurus-commercekit' ); ?></a>
						</div>
					</div>

				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_pdp_gallery'] ) && 1 === (int) $commercekit_options['widget_pos_pdp_gallery'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

			<!-- Size Guides -->
			<?php
				$enable_size_guide = isset( $commercekit_options['size_guide'] ) && 1 === (int) $commercekit_options['size_guide'] ? 1 : 0;
			?>
			<section <?php echo ( $enable_size_guide ) ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=size-guide"><?php esc_html_e( 'Size Guides', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=size-guide" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">
					<p><?php esc_html_e( 'If your products require sizing, this feature is crucial. It helps reduce costly returns, and improves the consumer experience.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( 'Reduces returns', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product pages', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_sizeguide'] ) && 1 === (int) $commercekit_options['widget_pos_sizeguide'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

			<!-- Sticky Add to Cart -->
			<?php
				$enable_sticky_atc_desk = isset( $commercekit_options['sticky_atc_desktop'] ) && 1 === (int) $commercekit_options['sticky_atc_desktop'] ? 1 : 0;
				$enable_sticky_atc_mobi = isset( $commercekit_options['sticky_atc_mobile'] ) && 1 === (int) $commercekit_options['sticky_atc_mobile'] ? 1 : 0;
				$enable_sticky_atc_tabs = isset( $commercekit_options['sticky_atc_tabs'] ) && 1 === (int) $commercekit_options['sticky_atc_tabs'] ? 1 : 0;
			?>
			<section <?php echo ( $enable_sticky_atc_desk || $enable_sticky_atc_mobi || $enable_sticky_atc_tabs ) ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=sticky-atc-bar"><?php esc_html_e( 'Sticky Add to Cart', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=sticky-atc-bar" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">
					<p><?php esc_html_e( 'Adds a conversion-friendly sticky add to cart bar on mobile and desktop. Can also expand the default WooCommerce tabs.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( 'Improves conversions', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product pages', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

			</section>


			<!-- Stock Meter -->
			<section <?php echo isset( $commercekit_options['inventory_display'] ) && 1 === (int) $commercekit_options['inventory_display'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=inventory-bar"><?php esc_html_e( 'Stock Meter', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=inventory-bar" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">
					<p><?php esc_html_e( 'A visually effective way to alert customers when the stock is low on product pages.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( '2Kb of HTML', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product pages', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_stockmeter'] ) && 1 === (int) $commercekit_options['widget_pos_stockmeter'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>


			<!-- Waitlist -->
			<section <?php echo isset( $commercekit_options['waitlist'] ) && 1 === (int) $commercekit_options['waitlist'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=waitlist"><?php esc_html_e( 'Waitlist', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=waitlist" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">

				<p><?php esc_html_e( 'Collect emails of interested buyers when your items are sold out. Automatically notify buyers when back in stock.', 'commercegurus-commercekit' ); ?></p>

				<div class="ckit-perf">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<div>
						<strong><?php esc_html_e( '3Kb of HTML', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on product pages', 'commercegurus-commercekit' ); ?>
						</div>
				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

			</section>

			<!-- Wishlist -->
			<section <?php echo isset( $commercekit_options['wishlist'] ) && 1 === (int) $commercekit_options['wishlist'] ? 'class="active"' : ''; ?>>

				<div class="ckit-feature-heading">
					<h3><a href="?page=commercekit&tab=wishlist"><?php esc_html_e( 'Wishlist', 'commercegurus-commercekit' ); ?></a></h3>
					<a href="?page=commercekit&tab=wishlist" class="button-secondary"><?php esc_html_e( 'Configure', 'commercegurus-commercekit' ); ?></a>
				</div>

				<div class="ckit-feature-content">

					<p><?php esc_html_e( 'Shoppers can create personalized collections of products they want to buy.', 'commercegurus-commercekit' ); ?></p>

					<div class="ckit-perf">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
							</svg>
							<div>
							<strong><?php esc_html_e( '9Kb of HTML', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'Loads on WooCommerce pages', 'commercegurus-commercekit' ); ?>
							</div>
					</div>

				</div>

				<div class="status">
					<div class="active">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
						<?php esc_html_e( 'Active', 'commercegurus-commercekit' ); ?>
					</div>
					<div class="inactive">
						<?php esc_html_e( 'Inactive', 'commercegurus-commercekit' ); ?>
					</div>
					<?php if ( isset( $commercekit_options['widget_pos_wishlist'] ) && 1 === (int) $commercekit_options['widget_pos_wishlist'] ) { ?>
					<div class="active shortcode">
						<?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>
					</div>
					<?php } ?>
				</div>

			</section>

		</div><!--/grid -->

		<input type="hidden" name="tab" value="dashboard" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>
</div>
</div>

<div class="postbox" id="settings-note">
	<?php if ( ! $domain_connected ) { ?>
	<a href="https://www.commercegurus.com/product/shoptimizer/" target="_blank" style="text-decoration: none;">
		<p><img src="<?php echo esc_url( CKIT_URI ); ?>assets/images/shoptimizer_logo.png" /></p>
		<p><?php esc_html_e( 'Optimize your WooCommerce store for speed and conversions with Shoptimizer. Shoptimizer is a FAST WooCommerce theme that comes with a ton of features all designed to help you convert more users to customers.', 'commercegurus-commercekit' ); ?></p>
	</a>
	<?php } else { ?>
	<h4><?php esc_html_e( 'Documentation', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'Visit the documentation area for a more detailed overview on each of these features. If you still have questions, you can send us a private ticket by clicking the Support link.', 'commercegurus-commercekit' ); ?></p>
	<p><strong><a href="https://www.commercegurus.com/docs/commercekit/" target="_blank"><?php esc_html_e( 'View Documentation', 'commercegurus-commercekit' ); ?></a></strong></p>
	<?php } ?>

	<h4><?php esc_html_e( 'Connection status', 'commercegurus-commercekit' ); ?></h4>
	<?php if ( $domain_connected ) { ?>
	<div class="ckit-connection-status connected">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
		</svg>
		<div>Connected</div>
	</div>

		<div><p><?php esc_html_e( 'Your website is connected! One click updates for Shoptimizer will appear in Appearance &rarr; Themes.', 'commercegurus-commercekit' ); ?></p></div>

	<?php } else { ?>
		<div class="ckit-connection-status not-connected">
		<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
		</svg>
		<div>Not connected</div>
	</div>
		<div><p><?php esc_html_e( 'You have not enabled one-click updates for Shoptimizer and CommerceKit. To do so, please', 'commercegurus-commercekit' ); ?> <a href="https://www.commercegurus.com/my-account/" target="_blank"><?php esc_html_e( 'connect your website', 'commercegurus-commercekit' ); ?></a> <?php esc_html_e( 'to your Shoptimizer subscription.', 'commercegurus-commercekit' ); ?> <?php esc_html_e( 'View the', 'commercegurus-commercekit' ); ?> <a href="https://www.commercegurus.com/docs/shoptimizer-theme/updating-shoptimizer/" target="_blank"><?php esc_html_e( 'update guide', 'commercegurus-commercekit' ); ?></a> <?php esc_html_e( 'to find out more.', 'commercegurus-commercekit' ); ?></p></div>
	<?php } ?>
</div>

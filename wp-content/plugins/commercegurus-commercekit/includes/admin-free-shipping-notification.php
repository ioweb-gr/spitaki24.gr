<?php
/**
 *
 * Admin Free Shipping Notification
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php
$args = array(
	'taxonomy'     => 'product_shipping_class',
	'hide_empty'   => 0,
	'orderby'      => 'name',
	'order'        => 'ASC',
	'hierarchical' => 0,
);

$shipping_classes = get_terms( $args );
?>
<div id="settings-content">
	<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Free Shipping Notification', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table product-gallery" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Display on the cart page', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_fsn_cart_page" class="toggle-switch"> <input name="commercekit[fsn_cart_page]" type="checkbox" id="commercekit_fsn_cart_page" value="1" <?php echo isset( $commercekit_options['fsn_cart_page'] ) && 1 === (int) $commercekit_options['fsn_cart_page'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Display on the mini cart', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_fsn_mini_cart" class="toggle-switch"> <input name="commercekit[fsn_mini_cart]" type="checkbox" id="commercekit_fsn_mini_cart" value="1" <?php echo isset( $commercekit_options['fsn_mini_cart'] ) && 1 === (int) $commercekit_options['fsn_mini_cart'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Display notification', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_fsn_before_ship" class="toggle-switch"> <input name="commercekit[fsn_before_ship]" type="checkbox" id="commercekit_fsn_before_ship" value="1" <?php echo isset( $commercekit_options['fsn_before_ship'] ) && 1 === (int) $commercekit_options['fsn_before_ship'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Display notification before entering the shipping address', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Initial message', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_fsn_initial_text"> <textarea name="commercekit[fsn_initial_text]" class="pc100" rows="3" id="commercekit_fsn_initial_text"><?php echo isset( $commercekit_options['fsn_initial_text'] ) && ! empty( $commercekit_options['fsn_initial_text'] ) ? stripslashes_deep( $commercekit_options['fsn_initial_text'] ) : commercekit_get_default_settings( 'fsn_initial_text' ); // phpcs:ignore ?></textarea></label><br /><small><?php esc_html_e( 'Available shortcode: {free_shipping_amount}', 'commercegurus-commercekit' ); ?></small></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Progress message', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_fsn_progress_text"> <input name="commercekit[fsn_progress_text]" class="pc100" type="text" id="commercekit_fsn_progress_text" value="<?php echo isset( $commercekit_options['fsn_progress_text'] ) && ! empty( $commercekit_options['fsn_progress_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['fsn_progress_text'] ) ) : commercekit_get_default_settings( 'fsn_progress_text' ); // phpcs:ignore ?>" /></label><br /><small><?php esc_html_e( 'Available shortcode: {remaining}', 'commercegurus-commercekit' ); ?></small></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Success message', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_fsn_success_text"> <input name="commercekit[fsn_success_text]" class="pc100" type="text" id="commercekit_fsn_success_text" value="<?php echo isset( $commercekit_options['fsn_success_text'] ) && ! empty( $commercekit_options['fsn_success_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['fsn_success_text'] ) ) : commercekit_get_default_settings( 'fsn_success_text' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Progress bar color', 'commercegurus-commercekit' ); ?></th> <td class="cgkit-color-abs-wrap"> <label for="commercekit_fsn_bar_color"> <input name="commercekit[fsn_bar_color]" class="pc100 cgkit-color-input" type="text" id="commercekit_fsn_bar_color" value="<?php echo isset( $commercekit_options['fsn_bar_color'] ) && ! empty( $commercekit_options['fsn_bar_color'] ) ? esc_attr( stripslashes_deep( $commercekit_options['fsn_bar_color'] ) ) : commercekit_get_default_settings( 'fsn_bar_color' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Exclude shipping classes', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_fsn_exclude_class"> <select name="commercekit[fsn_exclude_class][]" class="pc100 select21" id="commercekit_fsn_exclude_class" multiple="multiple" data-placeholder="<?php esc_html_e( 'Select shipping class', 'commercegurus-commercekit' ); ?>">
			<?php
			$sel_classes = isset( $commercekit_options['fsn_exclude_class'] ) ? array_map( 'intval', $commercekit_options['fsn_exclude_class'] ) : array();
			if ( count( $shipping_classes ) ) {
				foreach ( $shipping_classes as $ship_class ) {
					$sel = '';
					if ( in_array( (int) $ship_class->term_id, $sel_classes, true ) ) {
						$sel = 'selected="selected"';
					}
					echo '<option value="' . esc_attr( $ship_class->term_id ) . '" ' . esc_html( $sel ) . '>' . esc_html( $ship_class->name ) . '</option>';
				}
			}
			?>
			</select></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( '&ldquo;Continue Shopping&rdquo; link', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_fsn_shop_page">
			<select name="commercekit[fsn_shop_page]" id="commercekit_fsn_shop_page" class="pc100 select2" data-type="pages" data-placeholder="Select shop link page">
			<?php
			$pid = isset( $commercekit_options['fsn_shop_page'] ) && ! empty( $commercekit_options['fsn_shop_page'] ) ? esc_attr( $commercekit_options['fsn_shop_page'] ) : wc_get_page_id( 'shop' );
			if ( $pid ) {
				echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( get_the_title( $pid ) ) . '</option>';
			}
			?>
			</select></label></td> </tr>
		</table>

		<input type="hidden" name="tab" value="free-shipping-notification" />
		<input type="hidden" name="action" value="commercekit_save_settings" />

		<div class="mini-explainer">
		<p><?php esc_html_e( 'Note: If you adjust these settings you may need to add or remove an item from your cart to see the changes.', 'commercegurus-commercekit' ); ?></p>
		</div>

	</div>
	</div>

	<div class="postbox content-box">
		<h2><span class="table-heading"><?php esc_html_e( 'Use shortcode', 'commercegurus-commercekit' ); ?></span></h2>

		<div class="inside">

			<p><?php esc_html_e( 'Some page builders (such as Elementor Pro) allow you to completely customize the standard WooCommerce page template. By enabling the shortcode option, you can still add this module to your template, in a position of your choosing.', 'commercegurus-commercekit' ); ?></p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Free Shipping Notification shortcode', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_widget_pos_fsn" class="toggle-switch"> <input name="commercekit[widget_pos_fsn]" type="checkbox" id="commercekit_widget_pos_fsn" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_fsn'] ) && 1 === (int) $commercekit_options['widget_pos_fsn'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>

						<div class="cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_fsn'] ) && 1 === (int) $commercekit_options['widget_pos_fsn'] ? '' : 'style="display: none;"'; ?>>
							<div class="cg-notice-success">
								<p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_free_shipping_notification]</p>
							</div>
						</div>

					</td>
				</tr>
			</table>
		</div>
	</div>

</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Free Shipping Notification', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'Shipping costs are one of the major reasons shoppers abandon their cart. If you offer free shipping above a certain threshold, you can make this more prominent by enabling this module.' ); ?></p>

	<p><?php esc_html_e( 'See the ', 'commercegurus-commercekit' ); ?><a href="https://www.commercegurus.com/docs/shoptimizer-theme/commercekit-setup/#freeshipping" target="_blank">documentation</a> <?php esc_html_e( 'area for more details on setting up this module.', 'commercegurus-commercekit' ); ?></p>
</div>

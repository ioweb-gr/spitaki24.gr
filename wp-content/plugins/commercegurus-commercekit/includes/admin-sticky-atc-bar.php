<?php
/**
 *
 * Admin Sticky Add to Cart Bar
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<div id="settings-content" class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Sticky Add to Cart', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table product-gallery" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable on desktop', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_sticky_atc_desktop" class="toggle-switch"> <input name="commercekit[sticky_atc_desktop]" type="checkbox" id="commercekit_sticky_atc_desktop" value="1" <?php echo isset( $commercekit_options['sticky_atc_desktop'] ) && 1 === (int) $commercekit_options['sticky_atc_desktop'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Enable on mobile', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_sticky_atc_mobile" class="toggle-switch"> <input name="commercekit[sticky_atc_mobile]" type="checkbox" id="commercekit_sticky_atc_mobile" value="1" <?php echo isset( $commercekit_options['sticky_atc_mobile'] ) && 1 === (int) $commercekit_options['sticky_atc_mobile'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Expand tabs', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_sticky_atc_tabs" class="toggle-switch"> <input name="commercekit[sticky_atc_tabs]" type="checkbox" id="commercekit_sticky_atc_tabs" value="1" <?php echo isset( $commercekit_options['sticky_atc_tabs'] ) && 1 === (int) $commercekit_options['sticky_atc_tabs'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Removes the tabs and shows the contents one after another', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="sticky-atc-label" <?php echo ( ( isset( $commercekit_options['sticky_atc_tabs'] ) && 0 === (int) $commercekit_options['sticky_atc_tabs'] ) || ! isset( $commercekit_options['sticky_atc_tabs'] ) ) ? 'style="display: none;"' : ''; ?>> <th scope="row"><?php esc_html_e( 'Gallery tab title', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_sticky_atc_label"> <input name="commercekit[sticky_atc_label]" class="pc100" type="text" id="commercekit_sticky_atc_label" value="<?php echo isset( $commercekit_options['sticky_atc_label'] ) && ! empty( $commercekit_options['sticky_atc_label'] ) ? esc_attr( stripslashes_deep( $commercekit_options['sticky_atc_label'] ) ) : commercekit_get_default_settings( 'sticky_atc_label' ); // phpcs:ignore ?>" /></label></td> </tr>
		</table>

		<input type="hidden" name="tab" value="sticky-atc-bar" />
		<input type="hidden" name="action" value="commercekit_save_settings" />

		<div class="mini-explainer">
		<p><?php esc_html_e( 'We have included an "Expand tabs" option. Important information hidden within the default WooCommerce tabs can be missed by customers. Collapsing the contents of the tabs underneath each other makes the content more discoverable, particularly when scrolling on mobile. The "Gallery tab title" displays an additional anchor tab which scrolls back to the main summary area of the PDP.', 'commercegurus-commercekit' ); ?></p>
		</div>
	</div>
</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Sticky Add to Cart', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'This feature adds a sticky add to cart button which is fixed to the bottom of the viewport on mobile. Mobile conversions are crucial and displaying a call to action at all times on PDPs will boost sales.' ); ?></p>
	<p><?php esc_html_e( 'On desktop, the add to cart button attaches to the sticky tabs bar upon scrolling.' ); ?></p>
</div>

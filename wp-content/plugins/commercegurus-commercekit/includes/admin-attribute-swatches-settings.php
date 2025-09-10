<?php
/**
 *
 * Admin attribute swatches
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<div id="settings-content" class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Attribute Swatches', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table product-gallery" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_attribute_swatches" class="toggle-switch"> <input name="commercekit[attribute_swatches]" type="checkbox" id="commercekit_attribute_swatches" value="1" <?php echo isset( $commercekit_options['attribute_swatches'] ) && 1 === (int) $commercekit_options['attribute_swatches'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Attribute Swatches', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="as-enable-tooltips" <?php echo isset( $commercekit_options['attribute_swatches'] ) && 0 === (int) $commercekit_options['attribute_swatches'] ? 'style="display: none;"' : ''; ?>> <th scope="row"><?php esc_html_e( 'Display tooltips', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_enable_tooltips" class="toggle-switch"> <input name="commercekit[as_enable_tooltips]" type="checkbox" id="commercekit_as_enable_tooltips" value="1" <?php echo ( ( isset( $commercekit_options['as_enable_tooltips'] ) && 1 === (int) $commercekit_options['as_enable_tooltips'] ) || ! isset( $commercekit_options['as_enable_tooltips'] ) ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Display tooltips on color and image swatches', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="as-button-style" <?php echo isset( $commercekit_options['attribute_swatches'] ) && 0 === (int) $commercekit_options['attribute_swatches'] ? 'style="display: none;"' : ''; ?>> <th scope="row"><?php esc_html_e( 'Button style', 'commercegurus-commercekit' ); ?></th> <td> <label class="w-120"><input type="radio" value="0" name="commercekit[as_button_style]" <?php echo ( isset( $commercekit_options['as_button_style'] ) && 0 === (int) $commercekit_options['as_button_style'] ) || ! isset( $commercekit_options['as_button_style'] ) ? 'checked="checked"' : ''; ?> /><?php esc_html_e( 'Square', 'commercegurus-commercekit' ); ?></label><label class="w-120"><input type="radio" value="1" name="commercekit[as_button_style]" <?php echo isset( $commercekit_options['as_button_style'] ) && 1 === (int) $commercekit_options['as_button_style'] ? 'checked="checked"' : ''; ?> /><?php esc_html_e( 'Fluid', 'commercegurus-commercekit' ); ?></label></td> </tr>
		</table>
	</div>

	<div class="inside">
		<div class="explainer" id="cgkit-as-plp-options" style="margin-top: 0">
			<h3><?php esc_html_e( 'Attribute Swatches on Product Details Pages', 'commercegurus-commercekit' ); ?></h3>
			<table class="form-table product-gallery" role="presentation">
				<tr> <th scope="row" style="padding-left: 0"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_attribute_swatches_pdp" class="toggle-switch"> <input name="commercekit[attribute_swatches_pdp]" type="checkbox" id="commercekit_attribute_swatches_pdp" value="1" <?php echo ( ! isset( $commercekit_options['attribute_swatches_pdp'] ) || ( isset( $commercekit_options['attribute_swatches_pdp'] ) && 1 === (int) $commercekit_options['attribute_swatches_pdp'] ) ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Attribute Swatches on Product Details Pages', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr id="as-disable-pdp"> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Disable for related products', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_disable_pdp" class="toggle-switch"> <input name="commercekit[as_disable_pdp]" type="checkbox" id="commercekit_as_disable_pdp" value="1" <?php echo isset( $commercekit_options['as_disable_pdp'] ) && 1 === (int) $commercekit_options['as_disable_pdp'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Switch off swatches for related products and within the menu on PDPs', 'commercegurus-commercekit' ); ?></label></td> </tr>
			</table>

			<h3><?php esc_html_e( 'Attribute Swatches on Product Listings Pages', 'commercegurus-commercekit' ); ?></h3>

			<p><?php esc_html_e( 'Enabling swatches on product listing pages (i.e. the shop, category screens) is an opinionated feature. This means that it does things in a certain way, based upon research from top eCommerce DTC brands. This should only be activated if your catalog contains primarily', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'variable products', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( '- i.e. you sell items which all have colors and sizes, and you wish to display these on the catalog.', 'commercegurus-commercekit' ); ?></p>

			<p><?php esc_html_e( 'A maximum of two attributes will appear on product listings pages. Quick add to cart will only work with two or fewer attributes.', 'commercegurus-commercekit' ); ?></p>

			<p><?php esc_html_e( 'Enabling this will change how your product listing pages will look and behave on desktop and mobile. If this is not suitable for your store leave this option', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'switched off', 'commercegurus-commercekit' ); ?></strong> <?php esc_html_e( 'or look at an', 'commercegurus-commercekit' ); ?> <a target="_blank" href="https://www.commercegurus.com/woocommerce-variation-swatches/"><?php esc_html_e( 'alternative swatches plugin', 'commercegurus-commercekit' ); ?></a> <?php esc_html_e( 'more suitable to your catalog.', 'commercegurus-commercekit' ); ?></p>

			<table class="form-table product-gallery" role="presentation">
				<tr> <th scope="row" style="padding-left: 0"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_attribute_swatches_plp" class="toggle-switch"> <input name="commercekit[attribute_swatches_plp]" type="checkbox" id="commercekit_attribute_swatches_plp" value="1" <?php echo isset( $commercekit_options['attribute_swatches_plp'] ) && 1 === (int) $commercekit_options['attribute_swatches_plp'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Attribute Swatches on Listing Pages', 'commercegurus-commercekit' ); ?></label></td> </tr>

				<tr id="as-quick-atc"> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Quick add to cart', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_activate_atc" class="toggle-switch"> <input name="commercekit[as_activate_atc]" type="checkbox" id="commercekit_as_activate_atc" value="1" <?php echo isset( $commercekit_options['as_activate_atc'] ) && 1 === (int) $commercekit_options['as_activate_atc'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Activate Quick add to cart', 'commercegurus-commercekit' ); ?></label></td> </tr>

				<tr id="as-quick-atc-txt"> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Quick add to cart label', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_as_quickadd_txt"> <input name="commercekit[as_quickadd_txt]" class="pc100" type="text" id="commercekit_as_quickadd_txt" value="<?php echo isset( $commercekit_options['as_quickadd_txt'] ) && ! empty( $commercekit_options['as_quickadd_txt'] ) ? esc_attr( stripslashes_deep( $commercekit_options['as_quickadd_txt'] ) ) : commercekit_get_default_settings( 'as_quickadd_txt' ); // phpcs:ignore ?>" /></label></td>  </tr>

				<tr id="as-more-opt-txt"> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'More options label', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_more_opt_txt"> <input name="commercekit[as_more_opt_txt]" class="pc100" type="text" id="commercekit_as_more_opt_txt" value="<?php echo isset( $commercekit_options['as_more_opt_txt'] ) && ! empty( $commercekit_options['as_more_opt_txt'] ) ? esc_attr( stripslashes_deep( $commercekit_options['as_more_opt_txt'] ) ) : commercekit_get_default_settings( 'as_more_opt_txt' ); // phpcs:ignore ?>" /></label></td>  </tr>
				<?php $swatch_link = isset( $commercekit_options['as_swatch_link'] ) && ! empty( $commercekit_options['as_swatch_link'] ) ? $commercekit_options['as_swatch_link'] : commercekit_get_default_settings( 'as_swatch_link' ); ?>
				<tr id="as-swatch-link"> <th style="padding-left: 0; vertical-align: top;" scope="row"><?php esc_html_e( 'Swatch link', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_swatch_link_variation"> <input name="commercekit[as_swatch_link]" class="pc100" type="radio" id="commercekit_as_swatch_link_variation" value="variation" <?php echo 'variation' === $swatch_link ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Specific variation on product page', 'commercegurus-commercekit' ); ?></label><br /><label for="commercekit_as_swatch_link_product" style="margin-top: 10px; display: block;"> <input name="commercekit[as_swatch_link]" class="pc100" type="radio" id="commercekit_as_swatch_link_product" value="product" <?php echo 'product' === $swatch_link ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Product page', 'commercegurus-commercekit' ); ?></label></td>  </tr>
				<tr id="as-disable-facade"> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Disable loading facade', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_disable_facade" class="toggle-switch"> <input name="commercekit[as_disable_facade]" type="checkbox" id="commercekit_as_disable_facade" value="1" <?php echo isset( $commercekit_options['as_disable_facade'] ) && 1 === (int) $commercekit_options['as_disable_facade'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Disable loading facade on Product Listings Pages', 'commercegurus-commercekit' ); ?></label></td> </tr>
			</table>
		</div>

		<input type="hidden" name="tab" value="attribute-swatches" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>
</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Attribute Swatches', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'A replacement for the core WooCommerce product variation dropdowns with color / image / button display options that will significantly improve user experience on product and listing pages.', 'commercegurus-commercekit' ); ?></p>

	<p><?php esc_html_e( 'See the ', 'commercegurus-commercekit' ); ?><a href="https://www.commercegurus.com/docs/commercekit/attribute-swatches/" target="_blank">documentation</a> <?php esc_html_e( 'area for more details on setting up this module.', 'commercegurus-commercekit' ); ?></p>

</div>

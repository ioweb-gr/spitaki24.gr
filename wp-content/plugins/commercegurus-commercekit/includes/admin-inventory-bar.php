<?php
/**
 *
 * Admin Inventory Bar
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<div id="settings-content">
	<div class="postbox content-box">

	<h2><span class="table-heading"><?php esc_html_e( 'Stock Meter', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable Stock Meter', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_inventory_display" class="toggle-switch"> <input name="commercekit[inventory_display]" type="checkbox" id="commercekit_inventory_display" value="1" <?php echo isset( $commercekit_options['inventory_display'] ) && 1 === (int) $commercekit_options['inventory_display'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Show Stock Meter on the single product page', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<?php /* translators: %s: stock counter. */ ?>
			<tr> <th scope="row"><?php esc_html_e( 'Low stock text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_inventory_text"> <input name="commercekit[inventory_text]" type="text" class="pc100" id="commercekit_inventory_text" value="<?php echo isset( $commercekit_options['inventory_text'] ) && ! empty( $commercekit_options['inventory_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_text'] ) ) : commercekit_get_default_settings( 'inventory_text' ); // phpcs:ignore ?>" /></label><br /><small>
			<?php /* translators: %s: stock counter. */ ?>
			<?php esc_html_e( 'Add &ldquo;%s&rdquo; to replace the stock number, ', 'commercegurus-commercekit' ); ?>
			<?php /* translators: %s: stock counter. */ ?>
			<?php esc_html_e( 'e.g. Only %s items left in stock!', 'commercegurus-commercekit' ); ?>
			</small></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Low stock threshold', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_inventory_threshold"> <input name="commercekit[inventory_threshold]" type="text" class="pc100" id="commercekit_inventory_threshold" value="<?php echo isset( $commercekit_options['inventory_threshold'] ) && ! empty( $commercekit_options['inventory_threshold'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_threshold'] ) ) : commercekit_get_default_settings( 'inventory_threshold' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Low stock bar color', 'commercegurus-commercekit' ); ?></th> <td class="cgkit-color-abs-wrap"> <label for="commercekit_inventory_lsb_color"> <input name="commercekit[inventory_lsb_color]" type="text" class="cgkit-color-input" id="commercekit_inventory_lsb_color" value="<?php echo isset( $commercekit_options['inventory_lsb_color'] ) && ! empty( $commercekit_options['inventory_lsb_color'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_lsb_color'] ) ) : commercekit_get_default_settings( 'inventory_lsb_color' ); // phpcs:ignore ?>" /></label></td> </tr>
			<?php /* translators: %s: stock counter. */ ?>
			<tr> <th scope="row"><?php esc_html_e( 'Regular stock text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_inventory_text_31"> <input name="commercekit[inventory_text_31]" type="text" class="pc100" id="commercekit_inventory_text_31" value="<?php echo isset( $commercekit_options['inventory_text_31'] ) && ! empty( $commercekit_options['inventory_text_31'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_text_31'] ) ) : commercekit_get_default_settings( 'inventory_text_31' ); // phpcs:ignore ?>" /></label><br /><small>
			<?php /* translators: %s: stock counter. */ ?>
			<?php esc_html_e( 'Add &ldquo;%s&rdquo; to replace the stock number, ', 'commercegurus-commercekit' ); ?>
			<?php /* translators: %s: stock counter. */ ?>
			<?php esc_html_e( 'e.g. Less than %s items left!', 'commercegurus-commercekit' ); ?></small></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Regular stock threshold', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_inventory_threshold31"> <input name="commercekit[inventory_threshold31]" type="text" class="pc100" id="commercekit_inventory_threshold31" value="<?php echo isset( $commercekit_options['inventory_threshold31'] ) && ! empty( $commercekit_options['inventory_threshold31'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_threshold31'] ) ) : commercekit_get_default_settings( 'inventory_threshold31' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Regular stock bar color', 'commercegurus-commercekit' ); ?></th> <td class="cgkit-color-abs-wrap"> <label for="commercekit_inventory_rsb_color"> <input name="commercekit[inventory_rsb_color]" type="text" class="cgkit-color-input" id="commercekit_inventory_rsb_color" value="<?php echo isset( $commercekit_options['inventory_rsb_color'] ) && ! empty( $commercekit_options['inventory_rsb_color'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_rsb_color'] ) ) : commercekit_get_default_settings( 'inventory_rsb_color' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'High stock text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_inventory_text_100"> <input name="commercekit[inventory_text_100]" type="text" class="pc100" id="commercekit_inventory_text_100" value="<?php echo isset( $commercekit_options['inventory_text_100'] ) && ! empty( $commercekit_options['inventory_text_100'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_text_100'] ) ) : commercekit_get_default_settings( 'inventory_text_100' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'High stock threshold', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_inventory_threshold100"> <input name="commercekit[inventory_threshold100]" type="text" class="pc100" id="commercekit_inventory_threshold100" value="<?php echo isset( $commercekit_options['inventory_threshold100'] ) && ! empty( $commercekit_options['inventory_threshold100'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_threshold100'] ) ) : commercekit_get_default_settings( 'inventory_threshold100' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'High stock bar color', 'commercegurus-commercekit' ); ?></th> <td class="cgkit-color-abs-wrap"> <label for="commercekit_inventory_hsb_color"> <input name="commercekit[inventory_hsb_color]" type="text" class="cgkit-color-input" id="commercekit_inventory_hsb_color" value="<?php echo isset( $commercekit_options['inventory_hsb_color'] ) && ! empty( $commercekit_options['inventory_hsb_color'] ) ? esc_attr( stripslashes_deep( $commercekit_options['inventory_hsb_color'] ) ) : commercekit_get_default_settings( 'inventory_hsb_color' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> 
				<th><?php esc_html_e( 'Conditions', 'commercegurus-commercekit' ); ?>: </th>
				<td> 
					<?php
					$ctype = 'all';
					if ( isset( $commercekit_options['inventory_condition'] ) && in_array( $commercekit_options['inventory_condition'], array( 'products', 'non-products' ), true ) ) {
						$ctype = 'products';
					}
					if ( isset( $commercekit_options['inventory_condition'] ) && in_array( $commercekit_options['inventory_condition'], array( 'categories', 'non-categories' ), true ) ) {
						$ctype = 'categories';
					}
					?>
					<select name="commercekit[inventory_condition]" class="conditions" style="max-width: 100%;">
						<option value="all" <?php echo isset( $commercekit_options['inventory_condition'] ) && 'all' === $commercekit_options['inventory_condition'] ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'All products', 'commercegurus-commercekit' ); ?></option>
						<option value="products" <?php echo isset( $commercekit_options['inventory_condition'] ) && 'products' === $commercekit_options['inventory_condition'] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?></option>
						<option value="non-products" <?php echo isset( $commercekit_options['inventory_condition'] ) && 'non-products' === $commercekit_options['inventory_condition'] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All products apart from', 'commercegurus-commercekit' ); ?></option>
						<option value="categories" <?php echo isset( $commercekit_options['inventory_condition'] ) && 'categories' === $commercekit_options['inventory_condition'] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific categories', 'commercegurus-commercekit' ); ?></option>
						<option value="non-categories" <?php echo isset( $commercekit_options['inventory_condition'] ) && 'non-categories' === $commercekit_options['inventory_condition'] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All categories apart from', 'commercegurus-commercekit' ); ?></option>
					</select>
				</td> 
			</tr>
			<tr class="product-ids" <?php echo 'all' === $ctype ? 'style="display:none;"' : ''; ?>>
				<th class="options">
				<?php
				echo 'all' === $ctype || 'products' === $ctype ? esc_attr( 'Specific products:' ) : '';
				echo 'categories' === $ctype ? esc_html__( 'Specific categories:', 'commercegurus-commercekit' ) : '';
				?>
				</th>
				<td> <label><select name="commercekit_inventory_pids[]" class="select2" data-type="<?php echo esc_attr( $ctype ); ?>" data-tab="inventory-bar" data-mode="full" multiple="multiple" style="width:100%;">
				<?php
				$pids = isset( $commercekit_options['inventory_pids'] ) ? explode( ',', $commercekit_options['inventory_pids'] ) : array();
				if ( 'all' !== $ctype && count( $pids ) ) {
					foreach ( $pids as $pid ) {
						if ( empty( $pid ) ) {
							continue;
						}
						if ( 'products' === $ctype ) {
							echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( commercekit_limit_title( get_the_title( $pid ) ) ) . '</option>';
						}
						if ( 'categories' === $ctype ) {
							$nterm = get_term_by( 'id', $pid, 'product_cat' );
							if ( $nterm ) {
								echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( $nterm->name ) . '</option>';
							}
						}
					}
				}
				?>
				</select><input type="hidden" name="commercekit[inventory_pids]" class="select3 text" value="<?php echo esc_html( implode( ',', $pids ) ); ?>" /></label></td> 
			</tr>
		</table>
		<input type="hidden" name="tab" value="inventory-bar" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>

	</div>

	<div class="postbox content-box">
		<h2><span class="table-heading"><?php esc_html_e( 'Use shortcode', 'commercegurus-commercekit' ); ?></span></h2>
		<div class="inside">

			<p><?php esc_html_e( 'Some page builders (such as Elementor Pro) allow you to completely customize the standard WooCommerce product page template. By enabling the shortcode option, you can still add this module to your template, in a position of your choosing. This will only work on', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'product page templates' ); ?></strong>.</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Stock Meter shortcode', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_widget_pos_stockmeter" class="toggle-switch"> <input name="commercekit[widget_pos_stockmeter]" type="checkbox" id="commercekit_widget_pos_stockmeter" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_stockmeter'] ) && 1 === (int) $commercekit_options['widget_pos_stockmeter'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>

						<div class="cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_stockmeter'] ) && 1 === (int) $commercekit_options['widget_pos_stockmeter'] ? '' : 'style="display: none;"'; ?>>

							<div class="mini-explainer cgkit-shortcode-help">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
								</svg>

								<p><?php esc_html_e( 'This will prevent the module from appearing unless the shortcode is present on the page. Only enable this if you are using a page  builder such as Elementor Pro and a custom product page template.', 'commercegurus-commercekit' ); ?></p>

							</div>

							<div class="cg-notice-success">
								<p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_stockmeter]</p>
							</div>
						</div>

					</td>
				</tr>
			</table>
		</div>
	</div>

</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Stock Meter', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'This feature allows you to show a stock meter counter on the single product page. It&lsquo;s a more visually effective way to alert customers when the stock level is low.', 'commercegurus-commercekit' ); ?></p>
</div>

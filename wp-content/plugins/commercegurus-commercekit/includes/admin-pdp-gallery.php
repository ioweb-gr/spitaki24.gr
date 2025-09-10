<?php
/**
 *
 * Admin PDP Triggers
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<div id="settings-content">
	<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Product Gallery', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">

		<table class="form-table product-gallery" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_gallery" class="toggle-switch"> <input name="commercekit[pdp_gallery]" type="checkbox" id="commercekit_pdp_gallery" value="1" <?php echo isset( $commercekit_options['pdp_gallery'] ) && 1 === (int) $commercekit_options['pdp_gallery'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Product Gallery', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr style="display:none;"> <th scope="row"><?php esc_html_e( 'Visible thumbnails', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_thumbnails"> <input name="commercekit[pdp_thumbnails]" type="number" min="3" max="8" id="commercekit_pdp_thumbnails" value="<?php echo isset( $commercekit_options['pdp_thumbnails'] ) && ! empty( $commercekit_options['pdp_thumbnails'] ) ? esc_attr( (int) $commercekit_options['pdp_thumbnails'] ) : 4; ?>" size="70" style="min-width: 200px;" /></label><br /><small><em><?php esc_html_e( 'Number of gallery thumbnails to display at a time. Minimum 3 and maximum 8.', 'commercegurus-commercekit' ); ?></em></small><div class="input-error" id="pdp_thumbnails_error" style="display: none;"><?php esc_html_e( 'Please enter number between 3 and 8.', 'commercegurus-commercekit' ); ?></div></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Enable lightbox?', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_lightbox" class="toggle-switch"> <input name="commercekit[pdp_lightbox]" type="checkbox" id="commercekit_pdp_lightbox" value="1" <?php echo ( ( isset( $commercekit_options['pdp_lightbox'] ) && 1 === (int) $commercekit_options['pdp_lightbox'] ) || ! isset( $commercekit_options['pdp_lightbox'] ) ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Display images in a lightbox when clicked on', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="pdp-lightbox-cap" <?php echo ( ( isset( $commercekit_options['pdp_lightbox'] ) && 1 === (int) $commercekit_options['pdp_lightbox'] ) || ! isset( $commercekit_options['pdp_lightbox'] ) ) ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'Enable lightbox captions?', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_lightbox_cap" class="toggle-switch"> <input name="commercekit[pdp_lightbox_cap]" type="checkbox" id="commercekit_pdp_lightbox_cap" value="1" <?php echo isset( $commercekit_options['pdp_lightbox_cap'] ) && 1 === (int) $commercekit_options['pdp_lightbox_cap'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Display captions within the image lightbox', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Enable video auto play?', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_video_autoplay" class="toggle-switch"> <input name="commercekit[pdp_video_autoplay]" type="checkbox" id="commercekit_pdp_video_autoplay" value="1" <?php echo ( ( isset( $commercekit_options['pdp_video_autoplay'] ) && 1 === (int) $commercekit_options['pdp_video_autoplay'] ) || ! isset( $commercekit_options['pdp_video_autoplay'] ) ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable video auto play', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Enable thumbnail arrows?', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_thumb_arrows" class="toggle-switch"> <input name="commercekit[pdp_thumb_arrows]" type="checkbox" id="commercekit_pdp_thumb_arrows" value="1" <?php echo isset( $commercekit_options['pdp_thumb_arrows'] ) && 1 === (int) $commercekit_options['pdp_thumb_arrows'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable thumbnail previous / next arrows', 'commercegurus-commercekit' ); ?></label></td> </tr>

			<tr>
				<td colspan="2">
					<h3 class="top-divider">Desktop</h3>
				</td>
			</tr>

			<tr> <th scope="row" valign="top" style="vertical-align:top;padding-top:20px"><?php esc_html_e( 'Desktop gallery layout', 'commercegurus-commercekit' ); ?></th> <td>

				<div id="gallery-layout-preview">
					<div class="layout-preview" id="horizontal-preview" style="display:none;"><div class="grid-full"></div><div class="grid-small"></div></div>
					<div class="layout-preview" id="vertical-left-preview" style="display:none;"><div class="grid-small"></div><div class="grid-full"></div></div>
					<div class="layout-preview" id="vertical-right-preview" style="display:none;"><div class="grid-full"></div><div class="grid-small"></div></div>
					<div class="layout-preview" id="grid-2-4-preview" style="display:none;"><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div></div>
					<div class="layout-preview" id="grid-3-1-2-preview" style="display:none;"><div class="grid-3"></div><div class="grid-3"></div><div class="grid-3"></div><div class="grid-1"></div><div class="grid-2"></div><div class="grid-2"></div></div>
					<div class="layout-preview" id="grid-1-2-2-preview" style="display:none;"><div class="grid-1"></div><div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div><div class="grid-2"></div></div></div>
					<div class="layout-preview" id="vertical-scroll-preview" style="display:none;"><div class="grid-small"></div><div class="grid-full"><div class="grid-1"></div><div class="grid-1"></div></div></div>
					<div class="layout-preview" id="simple-scroll-preview" style="display:none;"><div class="grid-full"><div class="grid-1"></div><div class="grid-1"></div></div></div>
				</div><br>
				<select name="commercekit[pdp_gallery_layout]" id="commercekit_pdp_gallery_layout">
					<?php $pdp_gallery_layout = isset( $commercekit_options['pdp_gallery_layout'] ) ? $commercekit_options['pdp_gallery_layout'] : commercekit_get_default_settings( 'pdp_gallery_layout' ); ?>
					<option value="horizontal" <?php echo 'horizontal' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Horizontal', 'commercegurus-commercekit' ); ?></option>
					<option value="vertical-left" <?php echo 'vertical-left' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Vertical left', 'commercegurus-commercekit' ); ?></option>
					<option value="vertical-right" <?php echo 'vertical-right' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Vertical right', 'commercegurus-commercekit' ); ?></option>
					<option value="grid-2-4" <?php echo 'grid-2-4' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Grid: 2 cols x 4 rows', 'commercegurus-commercekit' ); ?></option>
					<option value="grid-3-1-2" <?php echo 'grid-3-1-2' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Grid: 3 cols, 1 col, 2 cols', 'commercegurus-commercekit' ); ?></option>
					<option value="grid-1-2-2" <?php echo 'grid-1-2-2' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Grid: 1 col, 2 cols, 2 cols', 'commercegurus-commercekit' ); ?></option>
					<option value="vertical-scroll" <?php echo 'vertical-scroll' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Vertical scroll', 'commercegurus-commercekit' ); ?></option>
					<option value="simple-scroll" <?php echo 'simple-scroll' === $pdp_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Simple scroll', 'commercegurus-commercekit' ); ?></option>
				</select>

			</td></tr>
			<tr id="pdp-desktop-thumbnails" <?php echo 'horizontal' === $pdp_gallery_layout ? '' : 'style="display: none;"'; ?>> <th scope="row" valign="top" style="vertical-align:top;padding-top:20px"><?php esc_html_e( 'Desktop visible thumbnails', 'commercegurus-commercekit' ); ?></th> <td>
				<select name="commercekit[pdp_desktop_thumbnails]" id="commercekit_pdp_desktop_thumbnails">
					<?php $pdp_desktop_thumbnails = isset( $commercekit_options['pdp_desktop_thumbnails'] ) ? $commercekit_options['pdp_desktop_thumbnails'] : commercekit_get_default_settings( 'pdp_desktop_thumbnails' ); ?>
					<option value="4" <?php echo 4 === (int) $pdp_desktop_thumbnails ? 'selected="selected"' : ''; ?>>4</option>
					<option value="5" <?php echo 5 === (int) $pdp_desktop_thumbnails ? 'selected="selected"' : ''; ?>>5</option>
					<option value="6" <?php echo 6 === (int) $pdp_desktop_thumbnails ? 'selected="selected"' : ''; ?>>6</option>
					<option value="7" <?php echo 7 === (int) $pdp_desktop_thumbnails ? 'selected="selected"' : ''; ?>>7</option>
					<option value="8" <?php echo 8 === (int) $pdp_desktop_thumbnails ? 'selected="selected"' : ''; ?>>8</option>
				</select>
			</td></tr>
			<tr>
				<td colspan="2">
					<h3 class="top-divider">Mobile</h3>
				</td>
			</tr>
			<tr> <th scope="row" valign="top" style="vertical-align:top;padding-top:20px"><?php esc_html_e( 'Mobile gallery layout', 'commercegurus-commercekit' ); ?></th> <td>

				<div id="mobile-layout-preview">
					<div class="mobile-layout-preview" id="default-preview" style="display:none;"><div class="grid-full"></div><div class="grid-small"></div></div>
					<div class="mobile-layout-preview" id="minimal-preview" style="display:none;">
						<div class="grid-full"></div>
						<div class="dots">
							<div class="dot"></div>
							<div class="dot"></div>
							<div class="dot"></div>
						</div>						
					</div>
					<div class="mobile-layout-preview" id="show-edge-preview" style="display:none;">
						<div class="grid-full"></div>
						<div class="grid-show-edge"></div>
						<div class="dots">
							<div class="dot"></div>
							<div class="dot"></div>
							<div class="dot"></div>
						</div>
					</div>
				</div><br>
				<select name="commercekit[pdp_mobile_layout]" id="commercekit_pdp_mobile_layout">
					<?php $pdp_mobile_layout = isset( $commercekit_options['pdp_mobile_layout'] ) ? $commercekit_options['pdp_mobile_layout'] : commercekit_get_default_settings( 'pdp_mobile_layout' ); ?>
					<option value="default" <?php echo 'default' === $pdp_mobile_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Default', 'commercegurus-commercekit' ); ?></option>
					<option value="minimal" <?php echo 'minimal' === $pdp_mobile_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Minimal', 'commercegurus-commercekit' ); ?></option>
					<option value="show-edge" <?php echo 'show-edge' === $pdp_mobile_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Show edge of next slide', 'commercegurus-commercekit' ); ?></option>
				</select>
			</td></tr>
			<tr id="next-slide-percent" <?php echo 'show-edge' === $pdp_mobile_layout ? '' : 'style="display: none;"'; ?>> <th scope="row" valign="top" style="vertical-align:top;padding-top:20px"><?php esc_html_e( 'Next slide percent', 'commercegurus-commercekit' ); ?></th> <td>
				<select name="commercekit[next_slide_percent]" id="commercekit_next_slide_percent">
					<?php $next_slide_percent = isset( $commercekit_options['next_slide_percent'] ) ? $commercekit_options['next_slide_percent'] : commercekit_get_default_settings( 'next_slide_percent' ); ?>
					<option value="10" <?php echo 10 === (int) $next_slide_percent ? 'selected="selected"' : ''; ?>>10</option>
					<option value="20" <?php echo 20 === (int) $next_slide_percent ? 'selected="selected"' : ''; ?>>20</option>
					<option value="30" <?php echo 30 === (int) $next_slide_percent ? 'selected="selected"' : ''; ?>>30</option>
				</select>
			</td></tr>
			<tr id="pdp-mobile-thumbnails" <?php echo 'default' === $pdp_mobile_layout ? '' : 'style="display: none;"'; ?>> <th scope="row" valign="top" style="vertical-align:top;padding-top:20px"><?php esc_html_e( 'Mobile visible thumbnails', 'commercegurus-commercekit' ); ?></th> <td>
				<select name="commercekit[pdp_mobile_thumbnails]" id="commercekit_pdp_mobile_thumbnails">
					<?php $pdp_mobile_thumbnails = isset( $commercekit_options['pdp_mobile_thumbnails'] ) ? $commercekit_options['pdp_mobile_thumbnails'] : commercekit_get_default_settings( 'pdp_mobile_thumbnails' ); ?>
					<option value="4" <?php echo 4 === (int) $pdp_mobile_thumbnails ? 'selected="selected"' : ''; ?>>4</option>
					<option value="5" <?php echo 5 === (int) $pdp_mobile_thumbnails ? 'selected="selected"' : ''; ?>>5</option>
					<option value="6" <?php echo 6 === (int) $pdp_mobile_thumbnails ? 'selected="selected"' : ''; ?>>6</option>
					<option value="7" <?php echo 7 === (int) $pdp_mobile_thumbnails ? 'selected="selected"' : ''; ?>>7</option>
					<option value="8" <?php echo 8 === (int) $pdp_mobile_thumbnails ? 'selected="selected"' : ''; ?>>8</option>
				</select>
			</td></tr>
			</table>
		</div>

		<h2 class="nested-heading"><span class="table-heading"><?php esc_html_e( 'Featured Review', 'commercegurus-commercekit' ); ?></span></h2>

		<div class="inside">
			<p><?php esc_html_e( 'Display a highlighted review on product pages, which is excellent for conversions. New meta fields will appear within the product page editor, allowing you to include a featured review with thumbnail for any item. This appears below the gallery on desktop and at the end of the summary area on mobile.', 'commercegurus-commercekit' ); ?></p>

			<table class="form-table product-gallery" role="presentation">
				<tr> <th scope="row"><?php esc_html_e( 'Display featured review', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_pdp_featured_review" class="toggle-switch"> <input name="commercekit[pdp_featured_review]" type="checkbox" id="commercekit_pdp_featured_review" value="1" <?php echo isset( $commercekit_options['pdp_featured_review'] ) && 1 === (int) $commercekit_options['pdp_featured_review'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label></td> </tr>
			</table>

		<input type="hidden" name="tab" value="pdp-gallery" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>
	</div>

	<div class="postbox content-box">
		<h2><span class="table-heading"><?php esc_html_e( 'Use shortcode', 'commercegurus-commercekit' ); ?></span></h2>
		<div class="inside">

			<p><?php esc_html_e( 'Some page builders (such as Elementor Pro) allow you to completely customize the standard WooCommerce product page template. By enabling the shortcode option, you can still add this module to your template, in a position of your choosing. This will only work on', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'product page templates' ); ?></strong>.</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Product Gallery shortcode', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_widget_pos_pdp_gallery" class="toggle-switch"> <input name="commercekit[widget_pos_pdp_gallery]" type="checkbox" id="commercekit_widget_pos_pdp_gallery" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_pdp_gallery'] ) && 1 === (int) $commercekit_options['widget_pos_pdp_gallery'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>

						<div class="cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_pdp_gallery'] ) && 1 === (int) $commercekit_options['widget_pos_pdp_gallery'] ? '' : 'style="display: none;"'; ?>>

							<div class="mini-explainer cgkit-shortcode-help">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
								</svg>

								<p><?php esc_html_e( 'This will prevent the module from appearing unless the shortcode is present on the page. Only enable this if you are using a page  builder such as Elementor Pro and a custom product page template.', 'commercegurus-commercekit' ); ?></p>

							</div>

							<div class="cg-notice-success">
								<p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_product_gallery]</p>
							</div>
						</div>

					</td>
				</tr>
			</table>
		</div>
	</div>

</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Product Gallery', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'CommerceKit Product Gallery is a lightning fast replacement for the core WooCommerce product gallery that will significantly improve your Google PageSpeed Insights scores on product pages.', 'commercegurus-commercekit' ); ?></p>

	<p><?php esc_html_e( 'It is the first WooCommerce Product Gallery extension built specifically for web performance optimization which is now a key Google ranking signal.', 'commercegurus-commercekit' ); ?></p>

	<p><?php esc_html_e( 'See the ', 'commercegurus-commercekit' ); ?><a href="https://www.commercegurus.com/docs/commercekit/product-gallery/" target="_blank">documentation</a> <?php esc_html_e( 'area for more details on setting up this module.', 'commercegurus-commercekit' ); ?></p>
	<p><?php esc_html_e( 'Read our', 'commercegurus-commercekit' ); ?> <a href="https://www.commercegurus.com/woocommerce-product-gallery-speed/" target="_blank">blog post</a> <?php esc_html_e( 'to learn more about the CommerceKit Product Gallery.', 'commercegurus-commercekit' ); ?></p>

</div>

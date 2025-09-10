<?php
/**
 *
 * Admin Size Guides
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<div id="settings-content">
	<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Size Guides', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table product-gallery" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_size_guide" class="toggle-switch"> <input name="commercekit[size_guide]" type="checkbox" id="commercekit_size_guide" value="1" <?php echo isset( $commercekit_options['size_guide'] ) && 1 === (int) $commercekit_options['size_guide'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Enable size guides', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Display in search results', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_size_guide_search" class="toggle-switch"> <input name="commercekit[size_guide_search]" type="checkbox" id="commercekit_size_guide_search" value="1" <?php echo ( ( isset( $commercekit_options['size_guide_search'] ) && 1 === (int) $commercekit_options['size_guide_search'] ) || ( ! isset( $commercekit_options['size_guide_search'] ) && 1 === (int) commercekit_get_default_settings( 'size_guide_search' ) ) ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Make size guide pages findable within search results.', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Size guide label', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_size_guide_label"> <input name="commercekit[size_guide_label]" class="pc100" type="text" id="commercekit_size_guide_label" value="<?php echo isset( $commercekit_options['size_guide_label'] ) && ! empty( $commercekit_options['size_guide_label'] ) ? esc_attr( stripslashes_deep( $commercekit_options['size_guide_label'] ) ) : commercekit_get_default_settings( 'size_guide_label' ); // phpcs:ignore ?>" /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Default size guide', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_default_size_guide"> <select name="commercekit[default_size_guide]" class="pc100" id="commercekit_default_size_guide"><option value=""><?php esc_html_e( 'No default size guide set', 'commercegurus-commercekit' ); ?></option>
				<?php
					$selected_sg = isset( $commercekit_options['default_size_guide'] ) && ! empty( $commercekit_options['default_size_guide'] ) ? (int) $commercekit_options['default_size_guide'] : 0;
					$sg_arges    = array(
						'post_type'        => 'ckit_size_guide',
						'post_status'      => 'publish',
						'posts_per_page'   => -1,
						'suppress_filters' => false,
						'orderby'          => 'title',
						'order'            => 'ASC',
					);
					$sg_posts    = get_posts( $sg_arges );
					$sg_no_posts = true;
					if ( count( $sg_posts ) ) {
						$sg_no_posts = false;
						foreach ( $sg_posts as $sg_post ) {
							$sel = '';
							if ( (int) $sg_post->ID === $selected_sg ) {
								$sel = 'selected="selected"';
							}
							echo '<option value="' . esc_attr( $sg_post->ID ) . '" ' . $sel . '>' . esc_attr( $sg_post->post_title ) . '</option>'; // phpcs:ignore
						}
					}
					?>
				</select><?php echo true === $sg_no_posts ? '<a href="' . esc_url( admin_url( 'post-new.php?post_type=ckit_size_guide' ) ) . '">' . esc_html__( 'Add your first size guide', 'commercegurus-commercekit' ) . '</a>' : ''; ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Size guide icon', 'commercegurus-commercekit' ); ?></th> <td> <label class="w-120"><input type="radio" value="0" name="commercekit[size_guide_icon]" <?php echo ( isset( $commercekit_options['size_guide_icon'] ) && 0 === (int) $commercekit_options['size_guide_icon'] ) || ! isset( $commercekit_options['size_guide_icon'] ) ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#size_guide_icon_html').hide();}else{jQuery('#size_guide_icon_html').show();}"/><?php esc_html_e( 'Default', 'commercegurus-commercekit' ); ?></label><label class="w-120"><input type="radio" value="1" name="commercekit[size_guide_icon]" <?php echo isset( $commercekit_options['size_guide_icon'] ) && 1 === (int) $commercekit_options['size_guide_icon'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#size_guide_icon_html').show();}else{jQuery('#size_guide_icon_html').hide();}"/><?php esc_html_e( 'Custom', 'commercegurus-commercekit' ); ?></label></td></tr>
			<tr id="size_guide_icon_html" <?php echo isset( $commercekit_options['size_guide_icon'] ) && 1 === (int) $commercekit_options['size_guide_icon'] ? '' : 'style="display: none;"'; ?>> <th scope="row"></th> <td> <label class="text-label" for="commercekit_size_guide_icon_html"> <textarea name="commercekit[size_guide_icon_html]" class="pc100" rows="5" id="commercekit_size_guide_icon_html"><?php echo isset( $commercekit_options['size_guide_icon_html'] ) && ! empty( $commercekit_options['size_guide_icon_html'] ) ? stripslashes_deep( $commercekit_options['size_guide_icon_html'] ) : ''; // phpcs:ignore ?></textarea></label><br /><small><?php esc_html_e( 'Paste in the SVG code for the icon you would like to use. You can find example icons at ', 'commercegurus-commercekit' ); ?><a href="https://heroicons.com/" target="_blank">Heroicons</a><?php esc_html_e( ' and  ', 'commercegurus-commercekit' ); ?><a href="https://feathericons.com/" target="_blank">Feathericons</a>.</small>.</td></tr>
			<tr> <th scope="row"><?php esc_html_e( 'Display mode', 'commercegurus-commercekit' ); ?></th> <td> <label class="w-120"><input type="radio" value="1" name="commercekit[size_guide_mode]" <?php echo ( isset( $commercekit_options['size_guide_mode'] ) && 1 === (int) $commercekit_options['size_guide_mode'] ) || ! isset( $commercekit_options['size_guide_mode'] ) ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#size_guide_position').show();}else{jQuery('#size_guide_position').hide();}" /><?php esc_html_e( 'Modal', 'commercegurus-commercekit' ); ?></label><label class="w-120"><input type="radio" value="2" name="commercekit[size_guide_mode]" <?php echo isset( $commercekit_options['size_guide_mode'] ) && 2 === (int) $commercekit_options['size_guide_mode'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#size_guide_position').hide();}else{jQuery('#size_guide_position').show();}"/><?php esc_html_e( 'WooCommerce tab', 'commercegurus-commercekit' ); ?></label></td></tr>
		</table>

		<input type="hidden" name="tab" value="size-guide" />
		<input type="hidden" name="action" value="commercekit_save_settings" />

	</div>


</div>

	<div id="size_guide_position" <?php echo isset( $commercekit_options['size_guide_mode'] ) && 2 === (int) $commercekit_options['size_guide_mode'] ? 'style="display: none;"' : ''; ?>>
		<div class="postbox content-box">
			<h2><span class="table-heading"><?php esc_html_e( 'Use shortcode', 'commercegurus-commercekit' ); ?></span></h2>
			<div class="inside">

				<p><?php esc_html_e( 'Some page builders (such as Elementor Pro) allow you to completely customize the standard WooCommerce product page template. By enabling the shortcode option, you can still add this module to your template, in a position of your choosing. This will only work on', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'product page templates' ); ?></strong>.</p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Size Guide shortcode', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_widget_pos_sizeguide" class="toggle-switch"> <input name="commercekit[widget_pos_sizeguide]" type="checkbox" id="commercekit_widget_pos_sizeguide" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_sizeguide'] ) && 1 === (int) $commercekit_options['widget_pos_sizeguide'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>

							<div class="cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_sizeguide'] ) && 1 === (int) $commercekit_options['widget_pos_sizeguide'] ? '' : 'style="display: none;"'; ?>>

								<div class="mini-explainer cgkit-shortcode-help">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
									</svg>

									<p><?php esc_html_e( 'This will prevent the module from appearing unless the shortcode is present on the page. Only enable this if you are using a page  builder such as Elementor Pro and a custom product page template.', 'commercegurus-commercekit' ); ?></p>

								</div>

								<div class="cg-notice-success">
									<p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_sizeguide]</p>
								</div>
							</div>

						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Size Guides', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'If your products require sizing, this feature is crucial. It helps reduce costly returns, and improves the consumer experience.' ); ?></p>
	<p><?php esc_html_e( 'See the ', 'commercegurus-commercekit' ); ?><a href="https://www.commercegurus.com/docs/commercekit/commercekit-size-guides/" target="_blank">documentation</a> <?php esc_html_e( 'area for more details on setting up this module.', 'commercegurus-commercekit' ); ?></p>
</div>

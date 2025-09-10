<?php
/**
 * The template for displaying admin attribute swatches.
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php if ( ! isset( $without_wrap ) || ! $without_wrap ) { ?>
<div id="cgkit_attr_swatches" class="panel wc-metaboxes-wrapper hidden">
<?php } ?>
	<div class="wc-metabox">
		<div class="wc-metabox-content <?php echo isset( $attribute_swatches['enable_product'] ) && 0 === (int) $attribute_swatches['enable_product'] ? 'cgkit-disable-product' : ''; ?>" id="cgkit-swatches-content">
		<?php
		if ( count( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					?>
					<div id="cgkit-swatches-<?php echo esc_attr( $attribute['id'] ); ?>" class="postbox cgkit-attribute-swatches">
						<h2><span class="cgkit-as-title"><?php echo esc_attr( $attribute['name'] ); ?></span><select name="commercekit_attribute_swatches[<?php echo esc_attr( $attribute['id'] ); ?>][cgkit_type]" class="cgkit-attribute-watches-type"><option value="button" <?php echo isset( $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ) && 'button' === $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Button', 'commercegurus-commercekit' ); ?></option><option value="color" <?php echo isset( $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ) && 'color' === $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Color', 'commercegurus-commercekit' ); ?></option><option value="image" <?php echo isset( $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ) && 'image' === $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Image', 'commercegurus-commercekit' ); ?></option></select></h2>
						<div class="inside">
							<div class="product-swatches-container">
								<ul class="cgkit-swatches cgkit-type-<?php echo isset( $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ]['cgkit_type'] ) : 'button'; ?>">
								<?php
								foreach ( $attribute['terms'] as $item ) {
									$bg_color   = isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr'] ) : 'none';
									$bg_color2  = isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr2'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr2'] ) : '';
									$bg_type    = isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['ctyp'] ) ? (int) esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['ctyp'] ) : 1;
									$background = $bg_color;
									if ( 2 === $bg_type && ! empty( $bg_color2 ) ) {
										$background = 'linear-gradient(135deg, ' . $bg_color . ' 50%, ' . $bg_color2 . ' 50%)';
									}
									?>
								<li class="product-swatches">
									<div class="cgkit-name"><span class="cgkit-color-sample" style="background: <?php echo esc_attr( $background ); ?>;"></span><span class="cgkit-name-text"><?php echo esc_attr( $item->name ); ?></span></div>
									<div class="cgkit-value">
										<span class="cgkit-image">
											<div class="image-cntnr" data-choose="<?php esc_html_e( 'Add Image to Attribute Swatches', 'commercegurus-commercekit' ); ?>" data-update="<?php esc_html_e( 'Add to Attribute Swatches', 'commercegurus-commercekit' ); ?>" >
									<?php
									$image = null;
									if ( isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['img'] ) && ! empty( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['img'] ) ) {
										$image = wp_get_attachment_image_src( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['img'], 'woocommerce_gallery_thumbnail' );
									}
									?>
									<?php if ( $image ) { ?>
										<img width="<?php echo esc_attr( $image[1] ); ?>" height="<?php echo esc_attr( $image[2] ); ?>" src="<?php echo esc_url( $image[0] ); ?>" <?php echo ( (int) $image[2] < (int) $image[1] ) ? 'style="height:auto;"' : ''; ?> /><ul class="actions"><li><a href="javascript:;" class="cgkit-as-delete">x</a></li></ul>
									<?php } else { ?>
										<span class="dashicons dashicons-arrow-up-alt"></span>
										<div class="title"><?php esc_html_e( 'Add image', 'commercegurus-commercekit' ); ?></div>
									<?php } ?>
											</div>
										<input type="hidden" name="commercekit_attribute_swatches[<?php echo esc_attr( $attribute['id'] ); ?>][<?php echo esc_attr( $item->term_id ); ?>][img]" value="<?php echo isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['img'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['img'] ) : ''; ?>" class="cgkit-image-input" />
										</span>
										<span class="cgkit-color">
											<select name="commercekit_attribute_swatches[<?php echo esc_attr( $attribute['id'] ); ?>][<?php echo esc_attr( $item->term_id ); ?>][ctyp]" class="cgkit-color-input-type"><option value="1" <?php echo 1 === $bg_type ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Single color', 'commercegurus-commercekit' ); ?></option><option value="2" <?php echo 2 === $bg_type ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Two colors', 'commercegurus-commercekit' ); ?></option></select>
											<span class="cgkit-color1"><input type="text" name="commercekit_attribute_swatches[<?php echo esc_attr( $attribute['id'] ); ?>][<?php echo esc_attr( $item->term_id ); ?>][clr]" value="<?php echo isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr'] ) : ''; ?>" class="cgkit-color-input color" /></span>
											<span class="cgkit-color2" <?php echo 1 === $bg_type ? 'style="visibility: hidden;"' : ''; ?>><input type="text" name="commercekit_attribute_swatches[<?php echo esc_attr( $attribute['id'] ); ?>][<?php echo esc_attr( $item->term_id ); ?>][clr2]" value="<?php echo isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr2'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['clr2'] ) : ''; ?>" class="cgkit-color-input color2" /></span>
										</span>
										<span class="cgkit-button"><input type="text" name="commercekit_attribute_swatches[<?php echo esc_attr( $attribute['id'] ); ?>][<?php echo esc_attr( $item->term_id ); ?>][btn]" value="<?php echo isset( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['btn'] ) ? esc_attr( $attribute_swatches[ $attribute['id'] ][ $item->term_id ]['btn'] ) : esc_attr( $item->name ); ?>" class="cgkit-button-input" />
										</span>
									</div>
								</li>
								<?php } ?>
								</ul>
								<div class="clear"></div>
								<div class="cgkit-hide-loop"><label for="cgkit-hide-loop-<?php echo esc_attr( $attribute['id'] ); ?>" class="toggle-switch"> <input name="commercekit_attribute_swatches[hide_loop_<?php echo esc_attr( $attribute['id'] ); ?>]" type="checkbox" id="cgkit-hide-loop-<?php echo esc_attr( $attribute['id'] ); ?>" value="1" <?php echo isset( $attribute_swatches[ 'hide_loop_' . $attribute['id'] ] ) && 1 === (int) $attribute_swatches[ 'hide_loop_' . $attribute['id'] ] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label>&nbsp;<?php esc_html_e( 'Hide this attribute in the product loop', 'commercegurus-commercekit' ); ?></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}
		?>
		<label for="cgkit-enable-loop" class="toggle-switch"> <input name="commercekit_attribute_swatches[enable_loop]" type="checkbox" id="cgkit-enable-loop" value="1" <?php echo ( isset( $attribute_swatches['enable_loop'] ) && 1 === (int) $attribute_swatches['enable_loop'] ) || ! isset( $attribute_swatches['enable_loop'] ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label>&nbsp;<?php esc_html_e( 'Show on product loop', 'commercegurus-commercekit' ); ?>
		<input type="hidden" name="cgkit_swatches_nonce" id="cgkit_swatches_nonce" value="<?php echo esc_html( wp_create_nonce( 'cgkit_swatches_nonce' ) ); ?>" />
		</div>
		<div id="cgkit-enable-product-wrap">
			<label for="cgkit-enable-product" class="toggle-switch"> <input name="commercekit_attribute_swatches[enable_product]" type="checkbox" id="cgkit-enable-product" value="1" <?php echo ( isset( $attribute_swatches['enable_product'] ) && 1 === (int) $attribute_swatches['enable_product'] ) || ! isset( $attribute_swatches['enable_product'] ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label>&nbsp;<?php esc_html_e( 'Enable attribute swatches', 'commercegurus-commercekit' ); ?>
		</div>
		<div id="cgkitas-save-changes">
			<button type="button" class="button button-primary" onclick="cgkitAjaxUpdateAttributeSwatches();" disabled="disabled"><?php esc_html_e( 'Save changes', 'commercegurus-commercekit' ); ?></button>
			<?php wp_nonce_field( 'commercekit_settings', 'commercekit_nonce_as' ); ?>
		</div>
	</div>
<?php if ( ! isset( $without_wrap ) || ! $without_wrap ) { ?>
</div>
<?php } ?>

<?php
/**
 *
 * Product attribute swatches
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>

<form class="cgkit-swatch-form <?php echo esc_attr( $data_form_class ); ?>" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $data_variations; // phpcs:ignore ?>" data-images="<?php echo $data_images; // phpcs:ignore ?>">
	<div class="cgkit-as-swatches-clone cgkit-as-wrap-plp"></div>
	<details open>
	<summary><?php echo esc_html( $as_quickadd_txt ); ?></summary>
	<div class="ckit-attributes-wrap">
	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html__( 'Out of stock', 'commercegurus-commercekit' ); ?></p>
	<?php else : ?>
		<table class="variations" role="presentation">
			<tbody>
				<?php $attr_index = 1; ?>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<?php $attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name ); ?>
					<tr class="<?php echo ( ( isset( $attribute_swatches[ 'hide_loop_' . $attribute_id ] ) && 1 === (int) $attribute_swatches[ 'hide_loop_' . $attribute_id ] ) || 2 < $attr_index ) ? 'cgkit-hide-loop' : ''; ?>">
						<td class="label" style="display: none;"><label data-for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // phpcs:ignore ?></label></td>
						<td class="value">
							<?php
								wc_dropdown_variation_attribute_options(
									array(
										'selected'   => '',
										'options'    => $options,
										'attribute'  => $attribute_name,
										'product'    => $product,
										'id'         => '',
										'css_class'  => 'cgkit-as-wrap-plp',
										'attr_count' => count( $attributes ),
										'attr_index' => $attr_index,
									)
								);
								echo '<span style="display: none;">';
								echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'commercegurus-commercekit' ) . '</a>' ) ) : '';
								echo '</span>';
							?>
						</td>
					</tr>
					<?php $attr_index++; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="single_variation_wrap" style="display: none;">
			<div class="woocommerce-variation single_variation"></div>
			<div class="woocommerce-variation-add-to-cart variations_button">
			<?php
				woocommerce_quantity_input(
					array(
						'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
						'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
						'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore
					)
				);
			?>
				<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
				<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
				<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
				<input type="hidden" name="variation_id" class="variation_id" value="0" />
			</div>
		</div>
	<?php endif; ?>
	</div>
	</details>
</form>

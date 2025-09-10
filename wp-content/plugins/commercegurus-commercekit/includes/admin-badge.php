<?php
/**
 *
 * Admin badge
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

$badge = isset( $commercekit_options['badge'] ) ? $commercekit_options['badge'] : array();

$brands_exist = taxonomy_exists( 'product_brand' );
?>
<div id="settings-content">
<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Product Badges', 'commercegurus-commercekit' ); ?></span></h2>
	<div class="inside">
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_store_badge" class="toggle-switch"> <input name="commercekit[store_badge]" type="checkbox" id="commercekit_store_badge" value="1" <?php echo isset( $commercekit_options['store_badge'] ) && 1 === (int) $commercekit_options['store_badge'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Enable Product Badges', 'commercegurus-commercekit' ); ?></label></td> </tr>
		</table>
	</div>
</div>

<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Product Badge', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside meta-box-sortables" id="product-badge">

		<div class="postbox no-change closed" id="first-row" style="display:none;">
			<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
			<h2 class="gray"><span><?php esc_html_e( 'Label', 'commercegurus-commercekit' ); ?></span></h2>
			<div class="inside">
				<table class="form-table product-badge" role="presentation">
					<tr> 
						<th width="20%"><?php esc_html_e( 'Badge text', 'commercegurus-commercekit' ); ?> <span class="star">*</span></th>
						<td><label class="text-label"><input name="commercekit[badge][product][title][]" type="text" class="title badge-label text required" value="" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Background color', 'commercegurus-commercekit' ); ?></th>
						<td class="cgkit-color-abs-wrap"> <label><input name="commercekit[badge][product][bg_color][]" type="text" class="title text cgkit-color-input-tmp" value="<?php echo commercekit_get_default_settings( 'badge_bg_color' ); // phpcs:ignore ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Text color', 'commercegurus-commercekit' ); ?></th>
						<td class="cgkit-color-abs-wrap"> <label><input name="commercekit[badge][product][color][]" type="text" class="title text cgkit-color-input-tmp" value="<?php echo commercekit_get_default_settings( 'badge_color' ); // phpcs:ignore ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Conditions', 'commercegurus-commercekit' ); ?> </th>
						<td> 
							<select name="commercekit[badge][product][condition][]" class="conditions">
								<option value="all" selected="selected"><?php esc_html_e( 'All products', 'commercegurus-commercekit' ); ?></option>
								<option value="products"><?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?></option>
								<option value="non-products"><?php esc_html_e( 'All products apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="categories"><?php esc_html_e( 'Specific categories', 'commercegurus-commercekit' ); ?></option>
								<option value="non-categories"><?php esc_html_e( 'All categories apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="tags"><?php esc_html_e( 'Specific tags', 'commercegurus-commercekit' ); ?></option>
								<option value="non-tags"><?php esc_html_e( 'All tags apart from', 'commercegurus-commercekit' ); ?></option>
								<?php if ( $brands_exist ) { ?>
								<option value="brands"><?php esc_html_e( 'Specific brands', 'commercegurus-commercekit' ); ?></option>
								<option value="non-brands"><?php esc_html_e( 'All brands apart from', 'commercegurus-commercekit' ); ?></option>
								<?php } ?>
							</select>
						</td> 
					</tr>
					<tr class="product-ids" style="display:none;">
						<th class="options">
						<?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?>
						</th>
						<td> <label><select name="commercekit_badge_pdt_pids[]" class="select2" data-type="all" data-tab="badge" multiple="multiple" style="width:100%;"></select><input type="hidden" name="commercekit[badge][product][pids][]" class="select3 text" value="" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Show on catalog', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_badge_pdt_catalog[]" type="checkbox" class="check badge-check" value="1"><span class="toggle-slider"></span><input type="hidden" name="commercekit[badge][product][catalog][]" class="badge-check-val" value="0" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Show on product pages', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_badge_pdt_product[]" type="checkbox" class="check badge-check" value="1"><span class="toggle-slider"></span><input type="hidden" name="commercekit[badge][product][product][]" class="badge-check-val" value="0" /></label></td> 
					</tr>
					<tr><td colspan="2" class="right"><!--DELETE--></td></tr>
				</table>
			</div>
		</div>
		<?php
		if ( isset( $badge['product']['title'] ) && count( $badge['product']['title'] ) > 0 ) {
			foreach ( $badge['product']['title'] as $k => $product_title ) {
				if ( empty( $product_title ) ) {
					continue;
				}
				?>
		<div class="postbox no-change closed">
			<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
			<h2 class="gray"><span>
				<?php
				echo isset( $badge['product']['title'][ $k ] ) && ! empty( $badge['product']['title'][ $k ] ) ? esc_attr( $badge['product']['title'][ $k ] ) : esc_html__( 'Label', 'commercegurus-commercekit' );
				?>
			</span></h2>
			<div class="inside">
				<table class="form-table product-badge" role="presentation">
					<tr> 
						<th width="20%"><?php esc_html_e( 'Badge text', 'commercegurus-commercekit' ); ?> <span class="star">*</span></th>
						<td><label class="text-label"><input name="commercekit[badge][product][title][]" type="text" class="title badge-label text required" value="<?php echo isset( $badge['product']['title'][ $k ] ) ? esc_attr( stripslashes_deep( $badge['product']['title'][ $k ] ) ) : ''; ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Background color', 'commercegurus-commercekit' ); ?></th>
						<td class="cgkit-color-abs-wrap"> <label><input name="commercekit[badge][product][bg_color][]" type="text" class="title text cgkit-color-input" value="<?php echo isset( $badge['product']['bg_color'][ $k ] ) ? esc_attr( stripslashes_deep( $badge['product']['bg_color'][ $k ] ) ) : commercekit_get_default_settings( 'badge_bg_color' ); // phpcs:ignore ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Text color', 'commercegurus-commercekit' ); ?></th>
						<td class="cgkit-color-abs-wrap"> <label><input name="commercekit[badge][product][color][]" type="text" class="title text cgkit-color-input" value="<?php echo isset( $badge['product']['color'][ $k ] ) ? esc_attr( stripslashes_deep( $badge['product']['color'][ $k ] ) ) : commercekit_get_default_settings( 'badge_color' ); // phpcs:ignore ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Conditions', 'commercegurus-commercekit' ); ?></th>
						<td> 
							<?php
							$ctype = 'all';
							if ( isset( $badge['product']['condition'][ $k ] ) && in_array( $badge['product']['condition'][ $k ], array( 'products', 'non-products' ), true ) ) {
								$ctype = 'products';
							}
							if ( isset( $badge['product']['condition'][ $k ] ) && in_array( $badge['product']['condition'][ $k ], array( 'categories', 'non-categories' ), true ) ) {
								$ctype = 'categories';
							}
							if ( isset( $badge['product']['condition'][ $k ] ) && in_array( $badge['product']['condition'][ $k ], array( 'tags', 'non-tags' ), true ) ) {
								$ctype = 'tags';
							}
							if ( $brands_exist && isset( $badge['product']['condition'][ $k ] ) && in_array( $badge['product']['condition'][ $k ], array( 'brands', 'non-brands' ), true ) ) {
								$ctype = 'brands';
							}
							?>
							<select name="commercekit[badge][product][condition][]" class="conditions">
								<option value="all" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'all' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All products', 'commercegurus-commercekit' ); ?></option>
								<option value="products" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'products' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?></option>
								<option value="non-products" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'non-products' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All products apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="categories" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'categories' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific categories', 'commercegurus-commercekit' ); ?></option>
								<option value="non-categories" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'non-categories' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All categories apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="tags" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'tags' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific tags', 'commercegurus-commercekit' ); ?></option>
								<option value="non-tags" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'non-tags' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All tags apart from', 'commercegurus-commercekit' ); ?></option>
								<?php if ( $brands_exist ) { ?>
								<option value="brands" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'brands' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific brands', 'commercegurus-commercekit' ); ?></option>
								<option value="non-brands" <?php echo isset( $badge['product']['condition'][ $k ] ) && 'non-brands' === $badge['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All brands apart from', 'commercegurus-commercekit' ); ?></option>
								<?php } ?>
							</select>
						</td> 
					</tr>
					<tr class="product-ids" <?php echo 'all' === $ctype ? 'style="display:none;"' : ''; ?>>
						<th class="options">
						<?php
						echo 'all' === $ctype || 'products' === $ctype ? 'Specific products' : '';
						echo 'categories' === $ctype ? esc_html__( 'Specific categories:', 'commercegurus-commercekit' ) : '';
						echo 'tags' === $ctype ? esc_html__( 'Specific tags:', 'commercegurus-commercekit' ) : '';
						echo 'brands' === $ctype ? esc_html__( 'Specific brands:', 'commercegurus-commercekit' ) : '';
						?>
						</th>
						<td> <label><select name="commercekit_badge_pdt_pids[]" class="select2" data-type="<?php echo esc_attr( $ctype ); ?>" data-tab="badge" multiple="multiple" style="width:100%;">
						<?php
						$pids = isset( $badge['product']['pids'][ $k ] ) ? explode( ',', $badge['product']['pids'][ $k ] ) : array();
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
								if ( 'tags' === $ctype ) {
									$nterm = get_term_by( 'id', $pid, 'product_tag' );
									if ( $nterm ) {
										echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( $nterm->name ) . '</option>';
									}
								}
								if ( $brands_exist && 'brands' === $ctype ) {
									$nterm = get_term_by( 'id', $pid, 'product_brand' );
									if ( $nterm ) {
										echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( $nterm->name ) . '</option>';
									}
								}
							}
						}
						?>
						</select><input type="hidden" name="commercekit[badge][product][pids][]" class="select3 text" value="<?php echo esc_html( implode( ',', $pids ) ); ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Show on catalog', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_badge_pdt_catalog[]" type="checkbox" class="check badge-check" value="1" <?php echo isset( $badge['product']['catalog'][ $k ] ) && 1 === (int) $badge['product']['catalog'][ $k ] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span><input type="hidden" name="commercekit[badge][product][catalog][]" class="badge-check-val" value="<?php echo isset( $badge['product']['catalog'][ $k ] ) ? esc_attr( $badge['product']['catalog'][ $k ] ) : '0'; ?>" /></label></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Show on product pages', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_badge_pdt_product[]" type="checkbox" class="check badge-check" value="1" <?php echo isset( $badge['product']['product'][ $k ] ) && 1 === (int) $badge['product']['product'][ $k ] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span><input type="hidden" name="commercekit[badge][product][product][]" class="badge-check-val" value="<?php echo isset( $badge['product']['product'][ $k ] ) ? esc_attr( $badge['product']['product'][ $k ] ) : '0'; ?>" /></label></td> 
					</tr>
					<tr><td colspan="2" class="right"><a href="javascript:;" class="delete-badge" onclick="delete_product_badge(this);"><?php esc_html_e( 'Delete Product Badge', 'commercegurus-commercekit' ); ?></a></td></tr>
				</table>
			</div>
		</div>

				<?php
			}
		}
		?>
	</div>
	<script> 
	var global_delete_badge = '<?php esc_html_e( 'Delete product badge', 'commercegurus-commercekit' ); ?>';
	var global_delete_badge_confirm = '<?php esc_html_e( 'Are you sure you want to delete this product badge?', 'commercegurus-commercekit' ); ?>';
	var global_required_text = '<?php esc_html_e( 'This field is required', 'commercegurus-commercekit' ); ?>';
	</script>

	<div class="inside add-new-badge"><button type="button" class="button button-secondary" onclick="add_new_product_badge();"><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add New Product Badge', 'commercegurus-commercekit' ); ?></button></div>

</div>
<div class="postbox content-box" id="new-badge">
	<h2><span class="table-heading"><?php esc_html_e( 'New Product Badge', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table product-badge" role="presentation">
			<tr> 
				<th width="20%"><?php esc_html_e( 'Badge text', 'commercegurus-commercekit' ); ?></th>
				<td><label class="text-label"><input name="commercekit[badge][new][title]" type="text" class="title text" value="<?php echo isset( $badge['new']['title'] ) ? esc_attr( stripslashes_deep( $badge['new']['title'] ) ) : esc_html__( 'New!', 'commercegurus-commercekit' ); ?>" /></label></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Background color', 'commercegurus-commercekit' ); ?></th>
				<td class="cgkit-color-abs-wrap"> <label><input name="commercekit[badge][new][bg_color]" type="text" class="title text cgkit-color-input" value="<?php echo isset( $badge['new']['bg_color'] ) ? esc_attr( stripslashes_deep( $badge['new']['bg_color'] ) ) : commercekit_get_default_settings( 'badge_bg_color' ); // phpcs:ignore ?>" /></label></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Text color', 'commercegurus-commercekit' ); ?></th>
				<td class="cgkit-color-abs-wrap"> <label><input name="commercekit[badge][new][color]" type="text" class="title text cgkit-color-input" value="<?php echo isset( $badge['new']['color'] ) ? esc_attr( stripslashes_deep( $badge['new']['color'] ) ) : commercekit_get_default_settings( 'badge_color' ); // phpcs:ignore ?>" /></label></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Days displayed', 'commercegurus-commercekit' ); ?></th>
				<td> <label><input name="commercekit[badge][new][days]" type="number" class="title text" value="<?php echo isset( $badge['new']['days'] ) ? esc_attr( stripslashes_deep( $badge['new']['days'] ) ) : commercekit_get_default_settings( 'badge_new_days' ); // phpcs:ignore ?>" /></label><br /><small><?php esc_html_e( 'Number of days to display the badge from the date the product was added.', 'commercegurus-commercekit' ); ?></small></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Show on catalog', 'commercegurus-commercekit' ); ?> </th>
				<td> <label class="toggle-switch"><input name="commercekit[badge][new][catalog]" type="checkbox" class="check" value="1" <?php echo isset( $badge['new']['catalog'] ) && 1 === (int) $badge['new']['catalog'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Show on product pages', 'commercegurus-commercekit' ); ?> </th>
				<td> <label class="toggle-switch"><input name="commercekit[badge][new][product]" type="checkbox" class="check" value="1" <?php echo isset( $badge['new']['product'] ) && 1 === (int) $badge['new']['product'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label></td> 
			</tr>
		</table>
	</div>
		<input type="hidden" name="tab" value="badge" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
</div>
</div>
<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Product Badges', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'Product badges can enhance customer understanding of key product features, highlight promotions and products with limited-time offers, improve the click-through rate and many more conversion-boosting benefits.', 'commercegurus-commercekit' ); ?></p>
</div>

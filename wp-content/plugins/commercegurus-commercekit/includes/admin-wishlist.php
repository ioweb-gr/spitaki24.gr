<?php
/**
 *
 * Admin Wishlist
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php
global $wpdb;
if ( isset( $section ) && ! in_array( $section, array( 'settings', 'reports', 'statistics' ), true ) ) {
	$section = 'settings';
} elseif ( ! isset( $section ) ) {
	$section = 'settings';
}
if ( 'reports' === $section ) {
	$reports = get_transient( 'commercekit_wishlist_reports' );
	if ( ! $reports ) {
		$table      = $wpdb->prefix . 'commercekit_wishlist';
		$table2     = $wpdb->prefix . 'commercekit_wishlist_items';
		$sql1       = 'SELECT COUNT(*) FROM ' . $table;
		$list_count = (int) $wpdb->get_var( $sql1 ); // phpcs:ignore
		$sql2       = 'SELECT COUNT(DISTINCT user_id) FROM ' . $table2 . ' WHERE user_id != 0';
		$user_count = (int) $wpdb->get_var( $sql2 ); // phpcs:ignore
		$sql3       = 'SELECT product_id, COUNT(product_id) AS product_count FROM ' . $table2 . ' GROUP BY product_id ORDER BY product_count DESC LIMIT 0, 20';
		$results    = $wpdb->get_results( $sql3, ARRAY_A ); // phpcs:ignore

		$reports                   = array();
		$reports['wishlist_count'] = number_format( $list_count + $user_count, 0 );
		$reports['most_results']   = $results;

		set_transient( 'commercekit_wishlist_reports', $reports, DAY_IN_SECONDS );
	}
}
?>
<div id="settings-content">
<div class="postbox content-box">
	<?php if ( 'reports' === $section ) { ?>
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Wishlist Reports', 'commercegurus-commercekit' ); ?></span></h2>
	<?php } ?>
	<?php if ( 'settings' === $section ) { ?>
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Wishlist Settings', 'commercegurus-commercekit' ); ?></span></h2>
	<?php } ?>
	<?php if ( 'statistics' === $section ) { ?>
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Wishlist Statistics', 'commercegurus-commercekit' ); ?></span><button type="button" class="button button-primary" id="reset-wishlist-statistics" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to reset these statistics?', 'commercegurus-commercekit' ); ?>')){reset_wishlist_statistics();}"><?php esc_html_e( 'Reset Statistics', 'commercegurus-commercekit' ); ?></button></h2>
	<?php } ?>
	<ul class="subtabs">
	<li><a href="?page=commercekit&tab=wishlist" class="<?php echo ( 'settings' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
	<li><a href="?page=commercekit&tab=wishlist&section=reports" class="<?php echo 'reports' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Reports', 'commercegurus-commercekit' ); ?></a> </li>
	<li><a href="?page=commercekit&tab=wishlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a> </li>
	</ul>
	<?php if ( 'settings' === $section || '' === $section ) { ?>
	<div class="inside">
			<div class="cg-notice-success"><p><?php esc_html_e( 'Note: You will need to create a wishlist page and include this shortcode on it: [commercegurus_wishlist]', 'commercegurus-commercekit' ); ?></p></div>
			<table class="form-table admin-wishlist" role="presentation">
				<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_wishlist" class="toggle-switch"> <input name="commercekit[wishlist]" type="checkbox" id="commercekit_wishlist" value="1" <?php echo isset( $commercekit_options['wishlist'] ) && 1 === (int) $commercekit_options['wishlist'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable wishlist functionality', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Display', 'commercegurus-commercekit' ); ?></th> <td> <div class="wishlist-display-wrap"><label for="commercekit_wishlist_display_1"><input name="commercekit[wishlist_display]" type="radio" id="commercekit_wishlist_display_1" value="1" <?php echo ( ( isset( $commercekit_options['wishlist_display'] ) && 1 === (int) $commercekit_options['wishlist_display'] ) || ! isset( $commercekit_options['wishlist_display'] ) ) ? 'checked="checked"' : ''; ?>> <?php esc_html_e( 'On the catalog and product pages', 'commercegurus-commercekit' ); ?></label></div> <div class="wishlist-display-wrap"><label for="commercekit_wishlist_display_2"><input name="commercekit[wishlist_display]" type="radio" id="commercekit_wishlist_display_2" value="2" <?php echo isset( $commercekit_options['wishlist_display'] ) && 2 === (int) $commercekit_options['wishlist_display'] ? 'checked="checked"' : ''; ?>> <?php esc_html_e( 'On catalog only', 'commercegurus-commercekit' ); ?></label></div> <div class="wishlist-display-wrap"><label for="commercekit_wishlist_display_3"><input name="commercekit[wishlist_display]" type="radio" id="commercekit_wishlist_display_3" value="3" <?php echo isset( $commercekit_options['wishlist_display'] ) && 3 === (int) $commercekit_options['wishlist_display'] ? 'checked="checked"' : ''; ?>> <?php esc_html_e( 'On product pages only', 'commercegurus-commercekit' ); ?></label></div> </td> </tr>
				<tr> <th scope="row"><?php esc_html_e( '&ldquo;Add to wishlist&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wsl_adtext"> <input name="commercekit[wsl_adtext]" class="pc100" type="text" id="commercekit_wsl_adtext" value="<?php echo isset( $commercekit_options['wsl_adtext'] ) && ! empty( $commercekit_options['wsl_adtext'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wsl_adtext'] ) ) : commercekit_get_default_settings( 'wsl_adtext' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( '&ldquo;Product added&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wsl_pdtext"> <input name="commercekit[wsl_pdtext]" class="pc100" type="text" id="commercekit_wsl_pdtext" value="<?php echo isset( $commercekit_options['wsl_pdtext'] ) && ! empty( $commercekit_options['wsl_pdtext'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wsl_pdtext'] ) ) : commercekit_get_default_settings( 'wsl_pdtext' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( '&ldquo;Browse wishlist&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wsl_brtext"> <input name="commercekit[wsl_brtext]" class="pc100" type="text" id="commercekit_wsl_brtext" value="<?php echo isset( $commercekit_options['wsl_brtext'] ) && ! empty( $commercekit_options['wsl_brtext'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wsl_brtext'] ) ) : commercekit_get_default_settings( 'wsl_brtext' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Wishlist page', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_wsl_page">
				<?php $selected = isset( $commercekit_options['wsl_page'] ) ? esc_attr( $commercekit_options['wsl_page'] ) : 0; ?>
				<select name="commercekit[wsl_page]" id="commercekit_wsl_page" class="pc100 select2" data-type="pages" data-placeholder="Select wishlist page">
				<?php
				$pid = isset( $commercekit_options['wsl_page'] ) ? esc_attr( $commercekit_options['wsl_page'] ) : 0;
				if ( $pid ) {
					echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( get_the_title( $pid ) ) . '</option>';
				}
				?>
				</select>
				</label><br /><small><?php esc_html_e( 'Choose your wishlist page and set it to be full width. Ensure that it is excluded from any caching solutions.', 'commercegurus-commercekit' ); ?></small></td> </tr>
			</table>
			<input type="hidden" name="tab" value="wishlist" />
			<input type="hidden" name="action" value="commercekit_save_settings" />

		</div>

	</div>

	<div class="postbox content-box">
		<h2><span class="table-heading"><?php esc_html_e( 'Use shortcode', 'commercegurus-commercekit' ); ?></span></h2>

		<div class="inside">

			<p><?php esc_html_e( 'Some page builders (such as Elementor Pro) allow you to completely customize the standard WooCommerce product page template. By enabling the shortcode option, you can still add this module to your template, in a position of your choosing. This will only work on', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'product page templates' ); ?></strong>.</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Wishlist shortcode', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_widget_pos_wishlist" class="toggle-switch"> <input name="commercekit[widget_pos_wishlist]" type="checkbox" id="commercekit_widget_pos_wishlist" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_wishlist'] ) && 1 === (int) $commercekit_options['widget_pos_wishlist'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>

						<div class="cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_wishlist'] ) && 1 === (int) $commercekit_options['widget_pos_wishlist'] ? '' : 'style="display: none;"'; ?>>

							<div class="mini-explainer cgkit-shortcode-help">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
								</svg>

								<p><?php esc_html_e( 'This will prevent the module from appearing unless the shortcode is present on the page. Only enable this if you are using a page  builder such as Elementor Pro and a custom product page template.', 'commercegurus-commercekit' ); ?></p>
							</div>

							<div class="cg-notice-success">
								<p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_wishlist]</p>
							</div>
						</div>

					</td>
				</tr>
			</table>
		</div>
	</div>

	<?php } ?>

	<?php if ( 'reports' === $section ) { ?>

	<div class="inside ajax-search-reports">
		<div class="ajax-search-reports-boxes">
			<div class="ajax-search-reports-box">
				<h2><?php esc_html_e( 'Total wishlists', 'commercegurus-commercekit' ); ?></h2>
				<h3><?php echo isset( $reports['wishlist_count'] ) ? esc_attr( $reports['wishlist_count'] ) : 0; ?></h3>
				<p><?php esc_html_e( 'How many wishlists have been created.', 'commercegurus-commercekit' ); ?></p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Most popular products', 'commercegurus-commercekit' ); ?></h2>
		<p><?php esc_html_e( 'Discover which products are most wished for in your catalog.', 'commercegurus-commercekit' ); ?></p>
		<table class="ajax-search-reports-list">
			<tr><th class="left"><?php esc_html_e( 'Product', 'commercegurus-commercekit' ); ?></th><th class="right"><?php esc_html_e( 'Count', 'commercegurus-commercekit' ); ?></th></tr>
			<?php if ( isset( $reports['most_results'] ) && count( $reports['most_results'] ) ) { ?>
				<?php foreach ( $reports['most_results'] as $index => $row ) { ?>
					<?php
					$product_title = '';
					$product_elink = '';
					$product_vlink = '';
					if ( isset( $row['product_id'] ) ) {
						$product_title = get_the_title( $row['product_id'] );
						$product_elink = get_edit_post_link( $row['product_id'] );
						$product_vlink = get_permalink( $row['product_id'] );
					}
					if ( ! $product_title ) {
						continue;
					}
					?>
					<tr><td class="left"><span><?php echo esc_attr( str_pad( $index + 1, 2, '0', STR_PAD_LEFT ) ); ?></span> <a href="<?php echo esc_url( $product_vlink ); ?>"><?php echo esc_html( $product_title ); ?></a> (ID: <a href="<?php echo esc_url( $product_elink ); ?>"><?php echo esc_attr( $row['product_id'] ); ?></a>)</td><td class="right"><?php echo isset( $row['product_count'] ) ? esc_attr( number_format( $row['product_count'], 0 ) ) : 0; ?></td></tr>
				<?php } ?>
			<?php } else { ?>
				<tr><td class="center" colspan="2"><?php esc_html_e( 'No products', 'commercegurus-commercekit' ); ?></td></tr>
			<?php } ?>
		</table>

		<p class="report-note"><?php esc_html_e( 'NOTE: Report data is updated every 24 hours.', 'commercegurus-commercekit' ); ?></p>
	</div>

	</div>
	<?php } ?>

	<?php if ( 'statistics' === $section ) { ?>
	<div class="inside">
		<?php
		$wsls_total = (int) get_option( 'commercekit_wsls_total' );
		$wsls_sales = (int) get_option( 'commercekit_wsls_sales' );
		$wsls_price = (float) get_option( 'commercekit_wsls_sales_revenue' );
		$wsls_rate  = 0 !== $wsls_total ? number_format( ( $wsls_sales / $wsls_total ) * 100, 0 ) : 0;
		?>
		<ul class="wishlist-statistics">
			<li>
				<div class="title"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></div>
				<div class="text-large" id="wishlist-impressions"><?php echo esc_attr( number_format( $wsls_total, 0 ) ); ?></div>
			</li>
			<li>
				<div class="title"><?php esc_html_e( 'Revenue', 'commercegurus-commercekit' ); ?></div>
				<div class="text-large"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?><span id="wishlist-revenue"><?php echo esc_attr( number_format( $wsls_price, 2 ) ); ?></span></div>
			</li>
			<li>
				<div class="title"><?php esc_html_e( 'Additional Sales', 'commercegurus-commercekit' ); ?></div>
				<div class="text-large" id="wishlist-sales"><?php echo esc_attr( number_format( $wsls_sales, 0 ) ); ?></div>
			</li>
			<li>
				<div class="title"><?php esc_html_e( 'Conversion Rate', 'commercegurus-commercekit' ); ?></div>
				<div class="text-small" id="wishlist-covert-rate"><?php echo esc_attr( $wsls_rate ); ?>%</div>
				<div class="progress-bar"><span id="wishlist-covert-rate-percent" style="width: <?php echo esc_attr( $wsls_rate ); ?>%;"></span></div>
			</li>
		</ul>
	</div>

	</div>
	<?php } ?>

</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Wishlist', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'A wishlist allows shoppers to create personalized collections of products they want to buy and save them for future reference.', 'commercegurus-commercekit' ); ?></p>
</div>



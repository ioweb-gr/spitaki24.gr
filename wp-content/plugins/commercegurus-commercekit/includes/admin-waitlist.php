<?php
/**
 *
 * Admin Waitlist
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

global $wpdb;
if ( 'list' === $section || '' === $section || 'products' === $section ) {
	$product_id = isset( $_REQUEST['product_id'] ) ? sanitize_text_field( (int) $_REQUEST['product_id'] ) : 0; // phpcs:ignore
	$mail_sent  = isset( $_REQUEST['mail_sent'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mail_sent'] ) ) : 'all'; // phpcs:ignore

	$wtl_filters = array();
	$pg_url_ext  = '';
	if ( $product_id ) {
		$wtl_filters[] = 'product_id = ' . $product_id;
		$pg_url_ext   .= '&product_id=' . $product_id;
	}
	if ( 'all' !== $mail_sent ) {
		if ( 'yes' === $mail_sent ) {
			$wtl_filters[] = 'mail_sent = 1';
		} else {
			$wtl_filters[] = 'mail_sent = 0';
		}
		$pg_url_ext .= '&mail_sent=' . $mail_sent;
	}
	$wtl_where = '';
	if ( count( $wtl_filters ) ) {
		$wtl_where = ' WHERE ' . implode( ' AND ', $wtl_filters );
	}
	if ( 'list' === $section || '' === $section ) {
		$pg_url = admin_url( 'admin.php' ) . '?page=commercekit&tab=waitlist';
		$ttlsql = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'commercekit_waitlist' . $wtl_where;
	} elseif ( 'products' === $section ) {
		$pg_url = admin_url( 'admin.php' ) . '?page=commercekit&tab=waitlist&section=products';
		$ttlsql = 'SELECT COUNT(DISTINCT product_id) FROM ' . $wpdb->prefix . 'commercekit_waitlist' . $wtl_where;
	}
	$total  = (int) $wpdb->get_var( $ttlsql ); // phpcs:ignore
	$offset = 0;
	$limit  = 10;
	$wpage  = isset( $_REQUEST['paged'] ) ? sanitize_text_field( (int) $_REQUEST['paged'] ) : 1; // phpcs:ignore

	$wpage  = $wpage > 0 ? $wpage : 1;
	$wpages = ceil( $total / $limit );
	if ( $wpages && $wpage > $wpages ) {
		$wpage = $wpages;
	}
	$offset = ( $wpage - 1 ) * $limit;
	if ( 'list' === $section || '' === $section ) {
		$wtlsql = 'SELECT * FROM ' . $wpdb->prefix . 'commercekit_waitlist ' . $wtl_where . ' ORDER BY created DESC LIMIT ' . $offset . ', ' . $limit;
	} elseif ( 'products' === $section ) {
		$wtlsql = 'SELECT product_id, COUNT(product_id) AS total FROM ' . $wpdb->prefix . 'commercekit_waitlist ' . $wtl_where . ' GROUP BY product_id ORDER BY total DESC LIMIT ' . $offset . ', ' . $limit;
	}
	$rows  = $wpdb->get_results( $wtlsql, ARRAY_A ); // phpcs:ignore
	$flink = '';
	$plink = '';
	$nlink = '';
	$llink = '';
	if ( $wpage > 1 ) {
		$flink = $pg_url . '&paged=1' . $pg_url_ext;
		$plink = $pg_url . '&paged=' . ( $wpage - 1 ) . $pg_url_ext;
	}
	if ( $wpages > 1 && $wpage < $wpages ) {
		$nlink = $pg_url . '&paged=' . ( $wpage + 1 ) . $pg_url_ext;
		$llink = $pg_url . '&paged=' . $wpages . $pg_url_ext;
	}
}
?>

<div id="settings-content">
	<?php if ( 'list' === $section || '' === $section ) { ?>

	<div class="postbox content-box">
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></span></h2>
	<ul class="subtabs">
		<li><a href="?page=commercekit&tab=waitlist" class="<?php echo ( 'list' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=products" class="<?php echo 'products' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=settings" class="<?php echo 'settings' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=emails" class="<?php echo 'emails' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Emails', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=integrations" class="<?php echo 'integrations' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Integrations', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a></li>
	</ul>
	<div class="inside">
	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<select name="bulk_action" id="bulk-action-selector-top" onchange="jQuery('#bulk-apply').val(0);">
				<option value=""><?php esc_html_e( 'Bulk Actions', 'commercegurus-commercekit' ); ?></option>
				<option value="delete"><?php esc_html_e( 'Delete', 'commercegurus-commercekit' ); ?></option>
				<option value="export"><?php esc_html_e( 'Export', 'commercegurus-commercekit' ); ?></option>
			</select>
			<input type="button" id="waitlist-doaction" class="button action" value="Apply" onclick="jQuery('#bulk-apply').val(1);jQuery('#commercekit-form').submit();jQuery('#bulk-apply').val(0);"><input type="hidden" id="bulk-apply" name="bulk_apply" value="0" /><input type="hidden" name="tab" value="waitlist" />&nbsp;
			<?php esc_html_e( 'Filter by Mail sent:', 'commercegurus-commercekit' ); ?>
			<select name="mail_sent" id="mail_sent" onchange="jQuery('#current-page-selector').val(1); jQuery('#commercekit-form').submit();" style="float: none; width: 70px;">
				<option value="all"><?php esc_html_e( 'All', 'commercegurus-commercekit' ); ?></option>
				<option value="yes" <?php echo 'yes' === $mail_sent ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Yes', 'commercegurus-commercekit' ); ?></option>
				<option value="no" <?php echo 'no' === $mail_sent ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'No', 'commercegurus-commercekit' ); ?></option>
			</select>&nbsp;
			<?php esc_html_e( 'by Product ID:', 'commercegurus-commercekit' ); ?>
			<input id="product_id" name="product_id" type="text" value="<?php echo 0 !== (int) $product_id ? esc_html( $product_id ) : ''; ?>" size="7" placeholder="Product ID" onchange="jQuery('#current-page-selector').val(1); jQuery('#commercekit-form').submit();" style="float: none;" />
		</div>
		<?php if ( $total ) { ?>
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo esc_html( $total ); ?> <?php esc_html_e( 'items', 'commercegurus-commercekit' ); ?></span>
			<span class="pagination-links">
			<?php if ( '' !== $flink || '' !== $plink ) { ?>
			<a class="first-page button" href="<?php echo esc_url( $flink ); ?>">
				<span aria-hidden="true">«</span>
			</a>
			<a class="first-page button" href="<?php echo esc_url( $plink ); ?>">
				<span aria-hidden="true">‹</span>
			</a>
			<?php } else { ?>
				<span class="tablenav-pages-navspan button disabled">«</span>
				<span class="tablenav-pages-navspan button disabled">‹</span>
			<?php } ?>
			</span>
			<span class="paging-input">
				<input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo esc_html( $wpage ); ?>" size="2" />
				<span class="tablenav-paging-text"> <?php esc_html_e( 'of', 'commercegurus-commercekit' ); ?> <span class="total-pages"><?php echo esc_html( $wpages ); ?></span></span>
			</span>

			<?php if ( '' !== $nlink || '' !== $llink ) { ?>
			<a class="next-page button" href="<?php echo esc_url( $nlink ); ?>">
				<span aria-hidden="true">›</span>
			</a>
			<a class="last-page button" href="<?php echo esc_url( $llink ); ?>">
				<span aria-hidden="true">»</span>
			</a>
			<?php } else { ?>
			<span class="tablenav-pages-navspan button disabled">›</span>
			<span class="tablenav-pages-navspan button disabled">»</span>
			<?php } ?>
		</div>
		<?php } ?>
		<br class="clear">
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox"></td>
				<th scope="col" id="email" width="40%"><?php esc_html_e( 'Email', 'commercegurus-commercekit' ); ?></th>
				<th scope="col" id="product" width="30%"><?php esc_html_e( 'Product', 'commercegurus-commercekit' ); ?></th>
				<th scope="col" id="created"><?php esc_html_e( 'Date added', 'commercegurus-commercekit' ); ?></th>
				<th scope="col" id="mail_sent"><?php esc_html_e( 'Mail sent', 'commercegurus-commercekit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( is_array( $rows ) && count( $rows ) ) {
				foreach ( $rows as $row ) {
					?>
			<tr>
				<th id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-<?php echo esc_html( $row['id'] ); ?>" name="wtl_ids[]" value="<?php echo esc_html( $row['id'] ); ?>" type="checkbox"></th>
				<td scope="col" id="email"><?php echo esc_html( $row['email'] ); ?></td>
				<td scope="col" id="product"><?php commercekit_module_output( get_the_title( $row['product_id'] ) ); ?> (<?php esc_html_e( 'ID', 'commercegurus-commercekit' ); ?>: <?php echo esc_html( $row['product_id'] ); ?>)</td>
				<td scope="col" id="created"><?php echo esc_html( gmdate( 'j F Y', $row['created'] ) ); ?></td>
				<td scope="col" id="mail_sent"><?php echo isset( $row['mail_sent'] ) && 1 === (int) $row['mail_sent'] ? esc_html__( 'Yes', 'commercegurus-commercekit' ) : esc_html__( 'No', 'commercegurus-commercekit' ); ?></td>
			</tr>
					<?php
				}
			} else {
				?>
			<tr>
				<td scope="col" colspan="5" class="center"><?php esc_html_e( 'No Items', 'commercegurus-commercekit' ); ?></td>
			</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	</div>
	</div>

	<?php } ?>

	<?php if ( 'products' === $section ) { ?>

	<div class="postbox content-box">
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></span></h2>
	<ul class="subtabs">
		<li><a href="?page=commercekit&tab=waitlist" class="<?php echo ( 'list' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=products" class="<?php echo 'products' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=settings" class="<?php echo 'settings' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=emails" class="<?php echo 'emails' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Emails', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=integrations" class="<?php echo 'integrations' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Integrations', 'commercegurus-commercekit' ); ?></a></li>
		<li><a href="?page=commercekit&tab=waitlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a></li>
	</ul>
	<div class="inside">
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<select name="bulk_action" id="bulk-action-selector-top" onchange="jQuery('#bulk-apply').val(0);">
					<option value=""><?php esc_html_e( 'Bulk Actions', 'commercegurus-commercekit' ); ?></option>
					<option value="delete-product"><?php esc_html_e( 'Delete', 'commercegurus-commercekit' ); ?></option>
				</select>
				<input type="button" id="waitlist-doaction" class="button action" value="Apply" onclick="jQuery('#bulk-apply').val(1);jQuery('#commercekit-form').submit();jQuery('#bulk-apply').val(0);"><input type="hidden" id="bulk-apply" name="bulk_apply" value="0" /><input type="hidden" name="tab" value="waitlist" />&nbsp;
				<?php esc_html_e( 'Filter by Mail sent:', 'commercegurus-commercekit' ); ?>
				<select name="mail_sent" id="mail_sent" onchange="jQuery('#current-page-selector').val(1); jQuery('#commercekit-form').submit();" style="float: none; width: 70px;">
					<option value="all"><?php esc_html_e( 'All', 'commercegurus-commercekit' ); ?></option>
					<option value="yes" <?php echo 'yes' === $mail_sent ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Yes', 'commercegurus-commercekit' ); ?></option>
					<option value="no" <?php echo 'no' === $mail_sent ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'No', 'commercegurus-commercekit' ); ?></option>
				</select>&nbsp;
			</div>
			<?php if ( $total ) { ?>
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo esc_html( $total ); ?> <?php esc_html_e( 'items', 'commercegurus-commercekit' ); ?></span>
				<span class="pagination-links">
				<?php if ( '' !== $flink || '' !== $plink ) { ?>
				<a class="first-page button" href="<?php echo esc_url( $flink ); ?>">
					<span aria-hidden="true">«</span>
				</a>
				<a class="first-page button" href="<?php echo esc_url( $plink ); ?>">
					<span aria-hidden="true">‹</span>
				</a>
				<?php } else { ?>
					<span class="tablenav-pages-navspan button disabled">«</span>
					<span class="tablenav-pages-navspan button disabled">‹</span>
				<?php } ?>
				</span>
				<span class="paging-input">
					<input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo esc_html( $wpage ); ?>" size="2" />
					<span class="tablenav-paging-text"> <?php esc_html_e( 'of', 'commercegurus-commercekit' ); ?> <span class="total-pages"><?php echo esc_html( $wpages ); ?></span></span>
				</span>

				<?php if ( '' !== $nlink || '' !== $llink ) { ?>
				<a class="next-page button" href="<?php echo esc_url( $nlink ); ?>">
					<span aria-hidden="true">›</span>
				</a>
				<a class="last-page button" href="<?php echo esc_url( $llink ); ?>">
					<span aria-hidden="true">»</span>
				</a>
				<?php } else { ?>
				<span class="tablenav-pages-navspan button disabled">›</span>
				<span class="tablenav-pages-navspan button disabled">»</span>
				<?php } ?>
			</div>
			<?php } ?>
			<br class="clear">
		</div>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox"></td>
					<th scope="col" id="product" width="40%"><?php esc_html_e( 'Product name', 'commercegurus-commercekit' ); ?></th>
					<th scope="col" id="variations" width="30%"><?php esc_html_e( 'Variations', 'commercegurus-commercekit' ); ?></th>
					<th scope="col" id="stock-status"><?php esc_html_e( 'Stock status', 'commercegurus-commercekit' ); ?></th>
					<th scope="col" id="total"><?php esc_html_e( 'Customers', 'commercegurus-commercekit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( is_array( $rows ) && count( $rows ) ) {
					foreach ( $rows as $row ) {
						$product_name = '';
						$variations   = '-';
						$stock_status = '';
						$product_id   = $row['product_id'];
						$product      = wc_get_product( $product_id );
						if ( $product ) {
							$product_name = $product->get_name();
							$stock_status = $product->is_in_stock() ? esc_html__( 'In stock', 'commercegurus-commercekit' ) : esc_html__( 'Out of stock', 'commercegurus-commercekit' );
							if ( 'variation' === $product->get_type() ) {
								$variations   = wp_strip_all_tags( wc_get_formatted_variation( $product, true, false, false ) );
								$product_id   = $product->get_parent_id();
								$product_name = get_the_title( $product_id );
							}
						}
						$product_url = get_edit_post_link( $product_id );
						?>
				<tr>
					<th id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-<?php echo esc_html( $row['product_id'] ); ?>" name="wtl_ids[]" value="<?php echo esc_html( $row['product_id'] ); ?>" type="checkbox"></th>
					<td scope="col" id="product"><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_attr( $product_name ); ?></a></td>
					<td scope="col" id="variations"><?php echo esc_attr( $variations ); ?></td>
					<td scope="col" id="stock-status"><?php echo esc_attr( $stock_status ); ?></td>
					<td scope="col" id="total"><?php echo isset( $row['total'] ) ? esc_attr( $row['total'] ) : 0; ?></td>
				</tr>
						<?php
					}
				} else {
					?>
				<tr>
					<td scope="col" colspan="5" class="center"><?php esc_html_e( 'No Products', 'commercegurus-commercekit' ); ?></td>
				</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		</div>
	</div>

	<?php } ?>

	<?php if ( 'settings' === $section ) { ?>
	<div class="postbox content-box">
		<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Waitlist Settings', 'commercegurus-commercekit' ); ?></span></h2>
		<ul class="subtabs">
			<li><a href="?page=commercekit&tab=waitlist" class="<?php echo ( 'list' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=products" class="<?php echo 'products' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=settings" class="<?php echo 'settings' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=emails" class="<?php echo 'emails' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Emails', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=integrations" class="<?php echo 'integrations' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Integrations', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a></li>
		</ul>
		<div class="inside">
			<table class="form-table" role="presentation">
				<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_waitlist" class="toggle-switch"> <input name="commercekit[waitlist]" type="checkbox" id="commercekit_waitlist" value="1" <?php echo isset( $commercekit_options['waitlist'] ) && 1 === (int) $commercekit_options['waitlist'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Enable waitlist for out of stock products', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Introduction', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wtl_intro"> <input name="commercekit[wtl_intro]" type="text" class="pc100" id="commercekit_wtl_intro" value="<?php echo isset( $commercekit_options['wtl_intro'] ) && ! empty( $commercekit_options['wtl_intro'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_intro'] ) ) : commercekit_get_default_settings( 'wtl_intro' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Email placeholder', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wtl_email_text"> <input name="commercekit[wtl_email_text]" type="text" class="pc100" id="commercekit_wtl_email_text" value="<?php echo isset( $commercekit_options['wtl_email_text'] ) && ! empty( $commercekit_options['wtl_email_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_email_text'] ) ) : commercekit_get_default_settings( 'wtl_email_text' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Button label', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wtl_button_text"> <input name="commercekit[wtl_button_text]" type="text" class="pc100" id="commercekit_wtl_button_text" value="<?php echo isset( $commercekit_options['wtl_button_text'] ) && ! empty( $commercekit_options['wtl_button_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_button_text'] ) ) : commercekit_get_default_settings( 'wtl_button_text' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Consent label', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wtl_consent_text"> <input name="commercekit[wtl_consent_text]" type="text" class="pc100" id="commercekit_wtl_consent_text" value="<?php echo isset( $commercekit_options['wtl_consent_text'] ) && ! empty( $commercekit_options['wtl_consent_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_consent_text'] ) ) : commercekit_get_default_settings( 'wtl_consent_text' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Success message', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wtl_success_text"> <input name="commercekit[wtl_success_text]" type="text" class="pc100" id="commercekit_wtl_success_text" value="<?php echo isset( $commercekit_options['wtl_success_text'] ) && ! empty( $commercekit_options['wtl_success_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_success_text'] ) ) : commercekit_get_default_settings( 'wtl_success_text' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Read more label', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_wtl_readmore_text"> <input name="commercekit[wtl_readmore_text]" type="text" class="pc100" id="commercekit_wtl_readmore_text" value="<?php echo isset( $commercekit_options['wtl_readmore_text'] ) && ! empty( $commercekit_options['wtl_readmore_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_readmore_text'] ) ) : commercekit_get_default_settings( 'wtl_readmore_text' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Hidden out of stock variations', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_wtl_show_oos" class="toggle-switch"> <input name="commercekit[wtl_show_oos]" type="checkbox" id="commercekit_wtl_show_oos" value="1" <?php echo isset( $commercekit_options['wtl_show_oos'] ) && 1 === (int) $commercekit_options['wtl_show_oos'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Display hidden out of stock variations on Single Product page', 'commercegurus-commercekit' ); ?></label></td> </tr>
			</table>
			<input type="hidden" name="tab" value="waitlist" />
			<input type="hidden" name="action" value="commercekit_save_settings" />
			<input type="hidden" name="section" value="settings" />
		</div>
	</div>
	<?php } ?>

	<?php if ( 'emails' === $section ) { ?>
		<?php
		$placeholders = __( 'Available placeholders: {site_name}, {site_url}, {product_title}, {product_sku}, {product_link}, {customer_email}', 'commercegurus-commercekit' );
		if ( ! isset( $commercekit_options['waitlist_auto_mail'] ) ) {
			$commercekit_options['waitlist_auto_mail'] = 1;
		}
		if ( ! isset( $commercekit_options['waitlist_admin_mail'] ) ) {
			$commercekit_options['waitlist_admin_mail'] = 1;
		}
		if ( ! isset( $commercekit_options['waitlist_user_mail'] ) ) {
			$commercekit_options['waitlist_user_mail'] = 1;
		}
		?>
	<div class="postbox content-box">
		<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Waitlist Emails', 'commercegurus-commercekit' ); ?></span></h2>
		<ul class="subtabs">
			<li><a href="?page=commercekit&tab=waitlist" class="<?php echo ( 'list' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=products" class="<?php echo 'products' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=settings" class="<?php echo 'settings' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=emails" class="<?php echo 'emails' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Emails', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=integrations" class="<?php echo 'integrations' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Integrations', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a></li>
		</ul>
		<div class="inside">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<div class="label-tooltip">
							<?php esc_html_e( 'From email', 'commercegurus-commercekit' ); ?>:
							<span class="tooltip-wrapper">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tooltip-icon">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								<span class="tooltip" data-text="<?php esc_html_e( 'Please add a valid from email that ends with your domain to prevent spam emails.', 'commercegurus-commercekit' ); ?>"></span>
							</span>
						</div>
					</th>
					<td> <label class="text-label" for="commercekit_wtl_from_email"> <input name="commercekit[wtl_from_email]" type="text" class="pc100" id="commercekit_from_email" value="<?php echo isset( $commercekit_options['wtl_from_email'] ) && ! empty( $commercekit_options['wtl_from_email'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_from_email'] ) ) : commercekit_get_default_settings( 'wtl_from_email' ); // phpcs:ignore ?>" /></label></td> </tr>
				<tr>
					<th scope="row" style="vertical-align:top; padding-top: 20px;">
						<div class="label-tooltip">
							<?php esc_html_e( 'From name', 'commercegurus-commercekit' ); ?>:
							<span class="tooltip-wrapper">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tooltip-icon">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								<span class="tooltip" data-text="<?php esc_html_e( 'Please add a valid from name to prevent spam emails.', 'commercegurus-commercekit' ); ?>"></span>
							</span>
						</div>
					</th>

					<td> <label class="text-label" for="commercekit_wtl_from_name"> <input name="commercekit[wtl_from_name]" type="text" class="pc100" id="commercekit_from_name" value="<?php echo isset( $commercekit_options['wtl_from_name'] ) && ! empty( $commercekit_options['wtl_from_name'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_from_name'] ) ) : commercekit_get_default_settings( 'wtl_from_name' ); // phpcs:ignore ?>" /></label><p class="ckit-pt5"><small><?php esc_html_e( 'To improve the deliverability of these emails', 'commercegurus-commercekit' ); ?> <?php esc_html_e( 'we recommend you install the', 'commercegurus-commercekit' ); ?> <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank" style="white-space:nowrap;"><?php esc_html_e( 'WP Mail SMTP', 'commercegurus-commercekit' ); ?></a> <?php esc_html_e( 'plugin.', 'commercegurus-commercekit' ); ?></small></p></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Force', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_wtl_force_email_name" class="toggle-switch"> <input name="commercekit[wtl_force_email_name]" type="checkbox" id="commercekit_wtl_force_email_name" value="1" <?php echo isset( $commercekit_options['wtl_force_email_name'] ) && 1 === (int) $commercekit_options['wtl_force_email_name'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Force from email and from name', 'commercegurus-commercekit' ); ?></label></td> </tr>

				<tr> <th colspan="2"><hr /></th> </tr>

				<tr>
					<th scope="row" style="vertical-align:top;padding-top: 20px;">
						<div class="label-tooltip">
							<?php esc_html_e( 'Notification recipient', 'commercegurus-commercekit' ); ?>:
							<span class="tooltip-wrapper">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tooltip-icon">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								<span class="tooltip" data-text="<?php esc_html_e( 'Please add a valid recipient email for admin notification.', 'commercegurus-commercekit' ); ?>"></span>
							</span>
						</div>
					</th>

					<td> <label class="text-label" for="commercekit_wtl_recipient"> <input name="commercekit[wtl_recipient]" type="text" class="pc100" id="commercekit_wtl_recipient" value="<?php echo isset( $commercekit_options['wtl_recipient'] ) && ! empty( $commercekit_options['wtl_recipient'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_recipient'] ) ) : commercekit_get_default_settings( 'wtl_recipient' ); // phpcs:ignore ?>" /></label><p class="ckit-pt5"><small><?php esc_html_e( 'Enter a recipient who will receive the notification. The site administration email is the default.', 'commercegurus-commercekit' ); ?></small></p></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Notification Reply-To:', 'commercegurus-commercekit' ); ?></th> <td> <label class="w-150"><input type="radio" value="0" name="commercekit[wtl_reply_to]" <?php echo ( isset( $commercekit_options['wtl_reply_to'] ) && 0 === (int) $commercekit_options['wtl_reply_to'] ) || ! isset( $commercekit_options['wtl_reply_to'] ) ? 'checked="checked"' : ''; ?> /><?php esc_html_e( 'Customer email', 'commercegurus-commercekit' ); ?></label><label class="w-150"><input type="radio" value="1" name="commercekit[wtl_reply_to]" <?php echo isset( $commercekit_options['wtl_reply_to'] ) && 1 === (int) $commercekit_options['wtl_reply_to'] ? 'checked="checked"' : ''; ?> /><?php esc_html_e( 'Admin email', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr> <th colspan="2"><hr /></th> </tr>

				<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_waitlist_auto_mail" class="toggle-switch"> <input name="commercekit[waitlist_auto_mail]" type="checkbox" id="commercekit_waitlist_auto_mail" value="1" <?php echo isset( $commercekit_options['waitlist_auto_mail'] ) && 1 === (int) $commercekit_options['waitlist_auto_mail'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable automatic emails when the item is back in stock', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr>
					<th scope="row" style="vertical-align:top;padding-top: 20px;">
						<div class="label-tooltip">
							<?php esc_html_e( 'Email subject', 'commercegurus-commercekit' ); ?>:
						</div>
					</th>
					<td> <label class="text-label" for="commercekit_wtl_auto_subject"> <input name="commercekit[wtl_auto_subject]" class="pc100" type="text" id="commercekit_wtl_auto_subject" value="<?php echo isset( $commercekit_options['wtl_auto_subject'] ) && ! empty( $commercekit_options['wtl_auto_subject'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_auto_subject'] ) ) : commercekit_get_default_settings( 'wtl_auto_subject' ); // phpcs:ignore ?>" /></label>

						<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p>
					</td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Email content', 'commercegurus-commercekit' ); ?>:</th> <td>
		<?php
		$wtl_auto_content = isset( $commercekit_options['wtl_auto_content'] ) && ! empty( $commercekit_options['wtl_auto_content'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_auto_content'] ) ) : commercekit_get_default_settings( 'wtl_auto_content' );
		wp_editor(
			html_entity_decode( $wtl_auto_content ),
			'commercekit_wtl_auto_content',
			array(
				'wpautop'       => true,
				'media_buttons' => true,
				'textarea_name' => 'commercekit[wtl_auto_content]',
				'textarea_rows' => 10,
				'teeny'         => true,
			)
		);
		?>
		<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p>
	</td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Email footer', 'commercegurus-commercekit' ); ?>:</th> <td>
		<?php
		$wtl_auto_footer = isset( $commercekit_options['wtl_auto_footer'] ) && ! empty( $commercekit_options['wtl_auto_footer'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_auto_footer'] ) ) : commercekit_get_default_settings( 'wtl_auto_footer' );
		wp_editor(
			html_entity_decode( $wtl_auto_footer ),
			'commercekit_wtl_auto_footer',
			array(
				'wpautop'       => true,
				'media_buttons' => true,
				'textarea_name' => 'commercekit[wtl_auto_footer]',
				'textarea_rows' => 5,
				'teeny'         => true,
			)
		);
		?>
		<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p>
	</td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Send Waitlist emails', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_wtl_not_stock_limit" class="toggle-switch"> <input name="commercekit[wtl_not_stock_limit]" type="checkbox" id="commercekit_wtl_not_stock_limit" value="1" <?php echo isset( $commercekit_options['wtl_not_stock_limit'] ) && 1 === (int) $commercekit_options['wtl_not_stock_limit'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Send Waitlist emails even if the number of recipients exceeds the stock amount.' ); ?></label></td> </tr>
				<tr> <th colspan="2"><hr /></th> </tr>

				<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_waitlist_admin_mail" class="toggle-switch"> <input name="commercekit[waitlist_admin_mail]" type="checkbox" id="commercekit_waitlist_admin_mail" value="1" <?php echo isset( $commercekit_options['waitlist_admin_mail'] ) && 1 === (int) $commercekit_options['waitlist_admin_mail'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable emails to the store owner when a customer signs up to the waitlist', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr> <th scope="row" style="vertical-align:top;padding-top: 20px;"><?php esc_html_e( 'Email subject', 'commercegurus-commercekit' ); ?>:</th> <td> <label class="text-label" for="commercekit_wtl_admin_subject"> <input name="commercekit[wtl_admin_subject]" class="pc100" type="text" id="commercekit_wtl_admin_subject" value="<?php echo isset( $commercekit_options['wtl_admin_subject'] ) && ! empty( $commercekit_options['wtl_admin_subject'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_admin_subject'] ) ) : commercekit_get_default_settings( 'wtl_admin_subject' ); // phpcs:ignore ?>" /></label>
				<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Email content', 'commercegurus-commercekit' ); ?>:</th> <td>
		<?php
		$wtl_admin_content = isset( $commercekit_options['wtl_admin_content'] ) && ! empty( $commercekit_options['wtl_admin_content'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_admin_content'] ) ) : commercekit_get_default_settings( 'wtl_admin_content' );
		wp_editor(
			html_entity_decode( $wtl_admin_content ),
			'commercekit_wtl_admin_content',
			array(
				'wpautop'       => true,
				'media_buttons' => true,
				'textarea_name' => 'commercekit[wtl_admin_content]',
				'textarea_rows' => 10,
				'teeny'         => true,
			)
		);
		?>
		<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p>
	</td>
</tr>

				<tr> <th colspan="2"><hr /></th> </tr>

				<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_waitlist_user_mail" class="toggle-switch"> <input name="commercekit[waitlist_user_mail]" type="checkbox" id="commercekit_waitlist_user_mail" value="1" <?php echo isset( $commercekit_options['waitlist_user_mail'] ) && 1 === (int) $commercekit_options['waitlist_user_mail'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable email to the customer when they sign up to a waitlist', 'commercegurus-commercekit' ); ?></label></td> </tr>
				<tr> <th scope="row" style="vertical-align:top;padding-top: 20px;"><?php esc_html_e( 'Email subject', 'commercegurus-commercekit' ); ?>:</th> <td> <label class="text-label" for="commercekit_wtl_user_subject"> <input name="commercekit[wtl_user_subject]" type="text" class="pc100" id="commercekit_wtl_user_subject" value="<?php echo isset( $commercekit_options['wtl_user_subject'] ) && ! empty( $commercekit_options['wtl_user_subject'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_user_subject'] ) ) : commercekit_get_default_settings( 'wtl_user_subject' ); // phpcs:ignore ?>" /></label>
				<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p></td> </tr>
				<tr> <th scope="row"><?php esc_html_e( 'Email content', 'commercegurus-commercekit' ); ?>:</th> <td>
		<?php
		$wtl_user_content = isset( $commercekit_options['wtl_user_content'] ) && ! empty( $commercekit_options['wtl_user_content'] ) ? esc_attr( stripslashes_deep( $commercekit_options['wtl_user_content'] ) ) : commercekit_get_default_settings( 'wtl_user_content' );
		wp_editor(
			html_entity_decode( $wtl_user_content ),
			'commercekit_wtl_user_content',
			array(
				'wpautop'       => true,
				'media_buttons' => true,
				'textarea_name' => 'commercekit[wtl_user_content]',
				'textarea_rows' => 10,
				'teeny'         => true,
			)
		);
		?>
		<p class="ckit-pt5"><small><?php echo esc_html( $placeholders ); ?></small></p>
	</td> </tr>

			</table>
			<input type="hidden" name="tab" value="waitlist" />
			<input type="hidden" name="action" value="commercekit_save_settings" />
		</div>
	</div>
	<?php } ?>

	<?php if ( 'integrations' === $section ) { ?>
	<div class="postbox content-box">
		<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Waitlist Integrations', 'commercegurus-commercekit' ); ?></span></h2>
		<ul class="subtabs">
			<li><a href="?page=commercekit&tab=waitlist" class="<?php echo ( 'list' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=products" class="<?php echo 'products' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=settings" class="<?php echo 'settings' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=emails" class="<?php echo 'emails' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Emails', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=integrations" class="<?php echo 'integrations' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Integrations', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a></li>
		</ul>
		<div class="inside">
			<table class="form-table" role="presentation">
				<tr> <th scope="row" style="vertical-align: top;"><?php esc_html_e( 'Email service provider', 'commercegurus-commercekit' ); ?></th> <td> <label><input type="radio" value="1" name="commercekit[waitlist_esp]" <?php echo ( isset( $commercekit_options['waitlist_esp'] ) && 1 === (int) $commercekit_options['waitlist_esp'] ) || ! isset( $commercekit_options['waitlist_esp'] ) ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#wtl_esp_klaviyo_opt').hide();}" />&nbsp;<?php esc_html_e( 'Self hosted', 'commercegurus-commercekit' ); ?></label><br /><br /><label><input type="radio" value="2" name="commercekit[waitlist_esp]" <?php echo isset( $commercekit_options['waitlist_esp'] ) && 2 === (int) $commercekit_options['waitlist_esp'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#wtl_esp_klaviyo_opt').show();}" />&nbsp;<?php esc_html_e( 'Klaviyo - Basic', 'commercegurus-commercekit' ); ?></label></td> </tr>

				<tr id="wtl_esp_klaviyo_opt" <?php echo isset( $commercekit_options['waitlist_esp'] ) && 2 === (int) $commercekit_options['waitlist_esp'] ? '' : 'style="display:none;"'; ?>> <td colspan="2" style="padding: 0px;">
					<?php $wtl_esp_klaviyo = isset( $commercekit_options['wtl_esp_klaviyo'] ) && 1 === (int) $commercekit_options['wtl_esp_klaviyo'] ? true : false; ?>
					<table class="form-table" role="presentation">
						<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_wtl_esp_klaviyo" class="toggle-switch"> <input name="commercekit[wtl_esp_klaviyo]" type="checkbox" id="commercekit_wtl_esp_klaviyo" value="1" <?php echo isset( $commercekit_options['wtl_esp_klaviyo'] ) && 1 === (int) $commercekit_options['wtl_esp_klaviyo'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('.wtl_esp_klaviyo_opt').show();}else{jQuery('.wtl_esp_klaviyo_opt').hide();}"><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enqueue Klaviyo JS', 'commercegurus-commercekit' ); ?></label></td> </tr>
						<tr class="wtl_esp_klaviyo_opt" <?php echo true === $wtl_esp_klaviyo ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'Klaviyo company ID', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_esp_klaviyo_company_id"> <input name="commercekit[esp_klaviyo][company_id]" type="text" class="pc100" id="commercekit_esp_klaviyo_company_id" value="<?php echo isset( $commercekit_options['esp_klaviyo']['company_id'] ) && ! empty( $commercekit_options['esp_klaviyo']['company_id'] ) ? esc_attr( stripslashes_deep( $commercekit_options['esp_klaviyo']['company_id'] ) ) : ''; // phpcs:ignore ?>"  /></label></td> </tr>
						<tr class="wtl_esp_klaviyo_opt" <?php echo true === $wtl_esp_klaviyo ? '' : 'style="display:none;"'; ?>> <td colspan="2" style="padding: 0px;">
							<table class="form-table" role="presentation">

							<tr> <th scope="row"><?php esc_html_e( 'Override', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_main_call" class="toggle-switch"> <input name="commercekit[esp_klaviyo][main_call]" type="checkbox" id="commercekit_esp_klaviyo_main_call" value="1" <?php echo isset( $commercekit_options['esp_klaviyo']['main_call'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['main_call'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#esp_klaviyo_main_call_txt').show();}else{jQuery('#esp_klaviyo_main_call_txt').hide();}"><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Override main call to action text?', 'commercegurus-commercekit' ); ?></label><br /><small><?php esc_html_e( 'Override the regular oos/restock call to action text: "Can\'t find your size?"', 'commercegurus-commercekit' ); ?></small></td> </tr>
							<tr id="esp_klaviyo_main_call_txt" <?php echo isset( $commercekit_options['esp_klaviyo']['main_call'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['main_call'] ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'New call to action text', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_main_call_txt"> <input name="commercekit[esp_klaviyo][main_call_txt]" type="text" class="pc100" id="commercekit_esp_klaviyo_main_call_txt" value="<?php echo isset( $commercekit_options['esp_klaviyo']['main_call_txt'] ) && ! empty( $commercekit_options['esp_klaviyo']['main_call_txt'] ) ? esc_attr( stripslashes_deep( $commercekit_options['esp_klaviyo']['main_call_txt'] ) ) : ''; // phpcs:ignore ?>"  /></label><br /><small><?php esc_html_e( 'Enter a short text label', 'commercegurus-commercekit' ); ?></small></td> </tr>
							</table>
						</td> </tr>
						<tr class="wtl_esp_klaviyo_opt" <?php echo true === $wtl_esp_klaviyo ? '' : 'style="display:none;"'; ?>> <td colspan="2" style="padding: 0px;">
							<table class="form-table" role="presentation">
							<tr> <th scope="row"><?php esc_html_e( 'Override', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_oos_message" class="toggle-switch"> <input name="commercekit[esp_klaviyo][oos_message]" type="checkbox" id="commercekit_esp_klaviyo_oos_message" value="1" <?php echo isset( $commercekit_options['esp_klaviyo']['oos_message'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['oos_message'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#esp_klaviyo_oos_message_txt').show();}else{jQuery('#esp_klaviyo_oos_message_txt').hide();}"><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Override regular out of stock message?', 'commercegurus-commercekit' ); ?></label><br /><small><?php esc_html_e( 'Override the regular oos message: "This product is currently out of stock and unavailable"', 'commercegurus-commercekit' ); ?></small></td> </tr>
							<tr id="esp_klaviyo_oos_message_txt" <?php echo isset( $commercekit_options['esp_klaviyo']['oos_message'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['oos_message'] ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'New out of stock message', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_oos_message_txt"> <input name="commercekit[esp_klaviyo][oos_message_txt]" type="text" class="pc100" id="commercekit_esp_klaviyo_oos_message_txt" value="<?php echo isset( $commercekit_options['esp_klaviyo']['oos_message_txt'] ) && ! empty( $commercekit_options['esp_klaviyo']['oos_message_txt'] ) ? esc_attr( stripslashes_deep( $commercekit_options['esp_klaviyo']['oos_message_txt'] ) ) : ''; // phpcs:ignore ?>"  /></label><br /><small><?php esc_html_e( 'Enter a short out of stock message', 'commercegurus-commercekit' ); ?></small></td> </tr>
							</table>
						</td> </tr>
						<tr class="wtl_esp_klaviyo_opt" <?php echo true === $wtl_esp_klaviyo ? '' : 'style="display:none;"'; ?>> <td colspan="2" style="padding: 0px;">
							<table class="form-table" role="presentation">
							<tr> <th scope="row"><?php esc_html_e( 'Stock message', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_stock_message" class="toggle-switch"> <input name="commercekit[esp_klaviyo][stock_message]" type="checkbox" id="commercekit_esp_klaviyo_stock_message" value="1" <?php echo isset( $commercekit_options['esp_klaviyo']['stock_message'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['stock_message'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#esp_klaviyo_stock_message_txt').show();}else{jQuery('#esp_klaviyo_stock_message_txt').hide();}"><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Add stock message textarea?', 'commercegurus-commercekit' ); ?></label></td> </tr>
							<tr id="esp_klaviyo_stock_message_txt" <?php echo isset( $commercekit_options['esp_klaviyo']['stock_message'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['stock_message'] ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'Stock message textarea', 'commercegurus-commercekit' ); ?></th> <td>
							<?php
							$stock_message_txt = isset( $commercekit_options['esp_klaviyo']['stock_message_txt'] ) && ! empty( $commercekit_options['esp_klaviyo']['stock_message_txt'] ) ? esc_attr( stripslashes_deep( $commercekit_options['esp_klaviyo']['stock_message_txt'] ) ) : '';
							wp_editor(
								html_entity_decode( $stock_message_txt ),
								'commercekit_esp_klaviyo_stock_message_txt',
								array(
									'wpautop'       => true,
									'media_buttons' => true,
									'textarea_name' => 'commercekit[esp_klaviyo][stock_message_txt]',
									'textarea_rows' => 10,
									'teeny'         => true,
								)
							);
							?>
							<br /><small><em><?php esc_html_e( 'Additional text area for more info and links. e.g. Short text explaining when you expect this item to be back in stock (or links to other products if it\'s a discontinued product)', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
							</table>
						</td> </tr>
						<tr class="wtl_esp_klaviyo_opt" <?php echo true === $wtl_esp_klaviyo ? '' : 'style="display:none;"'; ?>> <td colspan="2" style="padding: 0px;">
							<table class="form-table" role="presentation">
							<tr id="esp_klaviyo_show_form_id"> <th scope="row"><?php esc_html_e( 'Klaviyo form ID', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_show_form_id" class="text-label"> <input name="commercekit[esp_klaviyo][show_form_id]" type="text" class="pc100" id="commercekit_esp_klaviyo_show_form_id" value="<?php echo isset( $commercekit_options['esp_klaviyo']['show_form_id'] ) && ! empty( $commercekit_options['esp_klaviyo']['show_form_id'] ) ? esc_attr( stripslashes_deep( $commercekit_options['esp_klaviyo']['show_form_id'] ) ) : ''; // phpcs:ignore ?>"  /></label><br /><small><?php esc_html_e( 'Enter the Klaviyo Form ID - e.g. if Klaviyo provide "klaviyo-form-T9sLTc" you would enter just: T9sLTc', 'commercegurus-commercekit' ); ?></small></td> </tr>
							</table>
						</td> </tr>
						<tr class="wtl_esp_klaviyo_opt" <?php echo true === $wtl_esp_klaviyo ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'Force display', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_esp_klaviyo_force_display" class="toggle-switch"> <input name="commercekit[esp_klaviyo][force_display]" type="checkbox" id="commercekit_esp_klaviyo_force_display" value="1" <?php echo isset( $commercekit_options['esp_klaviyo']['force_display'] ) && 1 === (int) $commercekit_options['esp_klaviyo']['force_display'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Force display of Klaviyo and stock message textarea?', 'commercegurus-commercekit' ); ?></label><br /><small><?php esc_html_e( 'Force the display of the stock message textarea and Klaviyo form even if the item is not yet out of stock. Can be useful to display the waitlist form and messaging when stock is low.', 'commercegurus-commercekit' ); ?></small></td> </tr>

					</table>
				</td> </tr>

			</table>
			<input type="hidden" name="tab" value="waitlist" />
			<input type="hidden" name="action" value="commercekit_save_settings" />
			<input type="hidden" name="section" value="integrations" />
		</div>
	</div>
	<?php } ?>

	<?php if ( 'statistics' === $section ) { ?>
	<div class="postbox content-box">
		<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Waitlist Statistics', 'commercegurus-commercekit' ); ?></span><button type="button" class="button button-primary" id="reset-waitlist-statistics" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to reset these statistics?', 'commercegurus-commercekit' ); ?>')){reset_waitlist_statistics();}"><?php esc_html_e( 'Reset Statistics', 'commercegurus-commercekit' ); ?></button></h2>
		<ul class="subtabs">
			<li><a href="?page=commercekit&tab=waitlist" class="<?php echo ( 'list' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'List', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=products" class="<?php echo 'products' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=settings" class="<?php echo 'settings' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=emails" class="<?php echo 'emails' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Emails', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=integrations" class="<?php echo 'integrations' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Integrations', 'commercegurus-commercekit' ); ?></a></li>
			<li><a href="?page=commercekit&tab=waitlist&section=statistics" class="<?php echo 'statistics' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Statistics', 'commercegurus-commercekit' ); ?></a></li>
		</ul>
		<div class="inside">
		<?php
		$wtls_total = (int) get_option( 'commercekit_wtls_total' );
		$wtls_sales = (int) get_option( 'commercekit_wtls_sales' );
		$wtls_price = (float) get_option( 'commercekit_wtls_sales_revenue' );
		$wtls_rate  = 0 !== $wtls_total ? number_format( ( $wtls_sales / $wtls_total ) * 100, 0 ) : 0;
		?>
			<ul class="waitlist-statistics">
				<li>
					<div class="title"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></div>
					<div class="text-large" id="waitlist-impressions"><?php echo esc_attr( number_format( $wtls_total, 0 ) ); ?></div>
				</li>
				<li>
					<div class="title"><?php esc_html_e( 'Revenue', 'commercegurus-commercekit' ); ?></div>
					<div class="text-large"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?><span id="waitlist-revenue"><?php echo esc_attr( number_format( $wtls_price, 2 ) ); ?></span></div>
				</li>
				<li>
					<div class="title"><?php esc_html_e( 'Additional Sales', 'commercegurus-commercekit' ); ?></div>
					<div class="text-large" id="waitlist-sales"><?php echo esc_attr( number_format( $wtls_sales, 0 ) ); ?></div>
				</li>
				<li>
					<div class="title"><?php esc_html_e( 'Conversion Rate', 'commercegurus-commercekit' ); ?></div>
					<div class="text-small" id="waitlist-covert-rate"><?php echo esc_attr( $wtls_rate ); ?>%</div>
					<div class="progress-bar"><span id="waitlist-covert-rate-percent" style="width: <?php echo esc_attr( $wtls_rate ); ?>%;"></span></div>
				</li>
			</ul>
		</div>
	</div>
	<?php } ?>

</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Waitlist', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'Product waitlists are used to notify interested shoppers when sold-out products are back in stock. This module collects data on customers who sign up.', 'commercegurus-commercekit' ); ?></p>
</div>

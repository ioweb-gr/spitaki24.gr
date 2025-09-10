<?php
/**
 *
 * Admin attribute swatches
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php
global $wpdb;
$args = array(
	'post_type'      => 'product',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'order'          => 'DESC',
	'orderby'        => 'ID',
	'fields'         => 'ids',
	'tax_query'      => array( // phpcs:ignore
		array(
			'taxonomy' => 'product_type',
			'field'    => 'slug',
			'terms'    => 'variable',
		),
	),
);

$query   = new WP_Query( $args );
$p_total = (int) $query->found_posts;
$table   = $wpdb->prefix . 'commercekit_swatches_cache_count';
$sql     = 'SELECT COUNT(*) FROM ' . $table;
$c_total = (int) $wpdb->get_var( $sql ); // phpcs:ignore
$c_total = (int) min( $c_total, $p_total );

$c_percent  = $p_total > 0 ? (int) ( ( $c_total * 100 ) / $p_total ) : 0;
$build_done = isset( $commercekit_options['commercekit_as_scheduled_done'] ) ? gmdate( 'M j H:i:s', $commercekit_options['commercekit_as_scheduled_done'] ) : '';
?>
<div id="settings-content" class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<div id="cgkit-as-plp-options" style="margin-top: 0">
		<h3><?php esc_html_e( 'CommerceKit cache', 'commercegurus-commercekit' ); ?></h3>

		<div class="mini-explainer">
		<p><?php esc_html_e( 'On larger catalogs this cache can take some time to build when you first activate this feature. We use the WooCommerce Action Scheduler to ensure long running cache building tasks can be run in the background over time.', 'commercegurus-commercekit' ); ?> <a target="_blank" href="https://www.commercegurus.com/docs/commercekit/attribute-swatches/"><?php esc_html_e( 'Learn more', 'commercegurus-commercekit' ); ?></a></p>
		</div>

		<table class="form-table product-gallery" role="presentation">

			<tr> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Total variable products', 'commercegurus-commercekit' ); ?></th> <td> <strong><span title="<?php esc_html_e( 'Total variable products', 'commercegurus-commercekit' ); ?>" id="p_total_count"><?php echo esc_attr( $p_total ); ?></span></strong> </td> </tr>
			<tr> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Cached variable products', 'commercegurus-commercekit' ); ?></th> <td> <strong id="cgkit-as-cached" style="position: relative; bottom: -1px"><span title="<?php esc_html_e( 'Total cached variable products', 'commercegurus-commercekit' ); ?>" id="c_total_count"><?php echo esc_attr( $c_total ); ?></span> </strong> <input type="button" name="btn-submit2" id="btn-submit2" class="button button-primary clear-cache" value="<?php esc_html_e( 'Clear and rebuild CommerceKit cache', 'commercegurus-commercekit' ); ?>" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to clear and rebuild the CommerceKit cache?', 'commercegurus-commercekit' ); ?>')){jQuery('#commercekit_clear_as_cache').val(1); jQuery('#commercekit-form').submit();}" disabled="disabled" /> <input type="button" name="btn-submit3" id="btn-submit3" class="button" value="<?php esc_html_e( 'Cancel', 'commercegurus-commercekit' ); ?>" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to cancel all pending CommerceKit cache events?', 'commercegurus-commercekit' ); ?>')){ cancelASBuild(); }" style="visibility: hidden; margin-left: 10px;" /></td> </tr>
				<tr id="cgkit-as-logger" class="disable-events"> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Enable logger', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_as_logger" class="toggle-switch"> <input name="commercekit[as_logger]" type="checkbox" id="commercekit_as_logger" value="1" <?php echo isset( $commercekit_options['as_logger'] ) && 1 === (int) $commercekit_options['as_logger'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Enable CommerceKit cache rebuilding logger', 'commercegurus-commercekit' ); ?></label></td> </tr>
		</table>

		<!-- Start shortly -->
		<div class="cache-event-alert" id="event-shortly">
			<div class="cache-loader">
				<div class="att-loader"></div><?php esc_html_e( 'Starting cache rebuild shortly...', 'commercegurus-commercekit' ); ?>
			</div>
		</div>

		<!-- When starting -->
		<div class="cache-event-alert" id="event-created">
			<div class="cache-loader">
				<div class="att-loader"></div><?php esc_html_e( 'Cache event being created...', 'commercegurus-commercekit' ); ?>
			</div>
			<div id="event-stucked" class="log-message log-failed" style="display: none;"><?php esc_html_e( 'There is a problem creating the CommerceKit Cache. You may need to cancel all pending', 'commercegurus-commercekit' ); ?> <a href="admin.php?page=wc-status&tab=action-scheduler&status=pending&s=commercekit&action=-1&action2=-1&paged=1"><?php esc_html_e( 'Scheduled Actions', 'commercegurus-commercekit' ); ?></a> <?php esc_html_e( '(WooCommerce > Status > Scheduled Actions) and then try again with', 'commercegurus-commercekit' ); ?> <a href="javascript:;" onclick="jQuery('#commercekit_clear_as_cache').val(1); jQuery('#commercekit-form').submit();"><?php esc_html_e( 'Cancel and Restart', 'commercegurus-commercekit' ); ?></a> </div>
		</div>

		<!-- Switch to this when processing -->
		<div class="cache-event-alert" id="event-processing">
			<div class="cache-processing">
				<div class="cache-bar">
					<div class="cache-progress" id="c_percent" style="width: <?php echo esc_attr( $c_percent ); ?>%"></div>
				</div>
				<div class="cache-value">
					<div class="att-loader"></div><?php esc_html_e( 'Processing cache event.', 'commercegurus-commercekit' ); ?>&nbsp;<span id="c_total"><?php echo esc_attr( $c_total ); ?></span>/<span id="p_total"><?php echo esc_attr( $p_total ); ?></span>&nbsp;<?php esc_html_e( 'completed...', 'commercegurus-commercekit' ); ?>
				</div>
			</div>
		</div>

		<!-- Switch to this when completed -->
		<div class="cache-event-alert" id="event-completed">
			<div class="cache-processing">
				<div class="cache-bar">
					<div class="cache-completed" style="width: 100%"></div>
				</div>
				<div class="cache-value completed">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<?php esc_html_e( 'Cache rebuild complete', 'commercegurus-commercekit' ); ?>
				</div>
			</div>

			<h3 class="ckit_sub-heading"><?php esc_html_e( 'Last log message', 'commercegurus-commercekit' ); ?></h3>

			<div class="log-message"><?php esc_html_e( 'The caching process apparently succeeded and is now complete', 'commercegurus-commercekit' ); ?> (<span id="build_done"><?php echo esc_attr( $build_done ); ?></span>)</div>
		</div>
		<div class="cache-event-alert" id="event-failed">
			<div class="log-message log-failed"><?php esc_html_e( 'We couldn\'t create the cache event. For assistance, copy and paste the', 'commercegurus-commercekit' ); ?> <a href="admin.php?page=wc-status"><?php esc_html_e( 'WooCommerce System Report', 'commercegurus-commercekit' ); ?></a> <?php esc_html_e( 'and include it in a', 'commercegurus-commercekit' ); ?> <a href="admin.php?page=commercekit&tab=support"><?php esc_html_e( 'support ticket', 'commercegurus-commercekit' ); ?></a>.</div>
		</div>
		<div class="cache-event-alert" id="event-cancelled">
			<div class="log-message log-failed"><?php esc_html_e( 'The caching process has been cancelled.', 'commercegurus-commercekit' ); ?></div>
		</div>
		<div class="cache-event-alert" id="event-warning">
			<div class="log-message log-failed"><?php esc_html_e( 'There are some products has not been cached. Please click on above &ldquo;Clear and rebuild CommerceKit cache&rdquo; button to rebuild all products or please wait for 15 to 20 minutes to rebuild automatically only missing products.', 'commercegurus-commercekit' ); ?></div>
		</div>

		</div>

		<input type="hidden" name="commercekit[clear_as_cache]" id="commercekit_clear_as_cache" value="0" />
		<input type="hidden" name="tab" value="settings" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>
</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'For large product catalogs, displaying swatches on your archive/product listing pages can cause performance and speed issues especially when if you manage inventory at the variation/SKU level.', 'commercegurus-commercekit' ); ?></p>
	<p><?php esc_html_e( 'To solve this we create a dedicated swatches cache which means your pages load lightning fast even for items with lots of variations.', 'commercegurus-commercekit' ); ?></p>
</div>
<script>
jQuery(document).ready(function($){
	updateASBuildStatus();
});
function updateASBuildStatus(){
	var btnSub = jQuery('#btn-submit2');
	var cnlsSub = jQuery('#btn-submit3');
	var asLogger = jQuery('#cgkit-as-logger');
	var nonce = jQuery('#commercekit_nonce').val();
	var formData = new FormData();
	formData.append('action', 'commercekit_get_as_build_status');
	formData.append('commercekit_nonce', nonce);
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: formData,
		processData: false,
		contentType: false,
		success: function( json ) {
			jQuery('#c_total_count').html(json.c_total);
			jQuery('#p_total_count').html(json.p_total);
			if( json.status == 1 ){
				btnSub.attr('disabled', 'disabled');
				asLogger.addClass('disable-events');
				if( json.alert_id == 'event-processing' ){
					cnlsSub.css('visibility', 'visible');
				}
			} else {
				btnSub.removeAttr('disabled');
				cnlsSub.css('visibility', 'hidden');
				asLogger.removeClass('disable-events');
			}
			jQuery('#cgkit-as-plp-options .cache-event-alert').hide();
			jQuery('#c_total').html(json.c_total);
			jQuery('#p_total').html(json.p_total);
			jQuery('#c_percent').css('width', json.c_percent+'%');
			jQuery('#build_done').html(json.build_done);
			if( json.alert_id != '' ){
				jQuery('#'+json.alert_id).show();
			}
			if( json.alert_id != 'event-completed' && json.alert_id != 'ajax-stop' && json.alert_id != 'event-failed' ){
				setTimeout(function(){
					updateASBuildStatus();
				}, 5000);
			}
			if( json.alert_id == 'event-completed' && json.c_total != json.p_total ){
				jQuery('#event-warning').show();
			}
			if( json.is_stuck == 1 ){
				jQuery('#event-stucked').show();
			}
		}
	});
}
function cancelASBuild(){
	var btnSub = jQuery('#btn-submit3');
	var formData = new FormData();
	var nonce = jQuery('#commercekit_nonce').val();
	formData.append('action', 'commercekit_get_as_build_cancel');
	formData.append('commercekit_nonce', nonce);
	btnSub.attr('disabled', 'disabled');
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: formData,
		processData: false,
		contentType: false,
		success: function( json ) {
			btnSub.removeAttr('disabled');
			btnSub.css('visibility', 'hidden');
		}
	});
}
</script>

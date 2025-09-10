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
$message      = '';
$csv_uploaded = 0;
$upload_dir   = wp_upload_dir();
$csv_file     = isset( $_FILES['import_csv_file'] ) ? $_FILES['import_csv_file'] : null; // phpcs:ignore
if ( isset( $csv_file['error'] ) && 0 === (int) $csv_file['error'] ) {
	$extension = pathinfo( $csv_file['name'], PATHINFO_EXTENSION );
	if ( 'csv' === strtolower( $extension ) ) {
		$temp_csv_file = 'cgkit-imp-' . time() . '.csv';
		$csv_file_name = $upload_dir['basedir'] . '/' . $temp_csv_file;
		move_uploaded_file( $csv_file['tmp_name'], $csv_file_name ); // phpcs:ignore
		if ( isset( $commercekit_options['importing_csv_file'] ) && ! empty( $commercekit_options['importing_csv_file'] ) && file_exists( $upload_dir['basedir'] . '/' . $commercekit_options['importing_csv_file'] ) ) {
			unlink( $upload_dir['basedir'] . '/' . $commercekit_options['importing_csv_file'] );
		}
		$commercekit_options['importing_csv_file'] = $temp_csv_file;
		update_option( 'commercekit', $commercekit_options, true );
		$csv_uploaded = 1;
	} else {
		$message = esc_html__( 'Please upload a CSV file.', 'commercegurus-commercekit' );
	}
}
$msg = isset( $_GET['msg'] ) ? intval( sanitize_text_field( wp_unslash( $_GET['msg'] ) ) ) : 0; // phpcs:ignore
if ( 1 === $msg ) {
	$message = esc_html__( 'Failed to download exported file.', 'commercegurus-commercekit' );
}
?>
<div id="settings-content" class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Import / Export', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<?php if ( ! empty( $message ) ) { ?>
			<div class="cache-event-alert" id="import-failed">
				<div class="log-message log-failed"><?php echo esc_html( $message ); ?></div>
			</div>
		<?php } ?>
		<div id="cgkit-as-plp-options" style="margin-top: 0">
		<table class="form-table product-gallery" role="presentation">
			<tr id="cgkit-exporter-logger" class=""> <th style="padding-left: 0" scope="row"><?php esc_html_e( 'Enable logs', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_export_import_logger" class="toggle-switch"> <input name="commercekit[export_import_logger]" type="checkbox" id="commercekit_export_import_logger" value="1" <?php echo isset( $commercekit_options['export_import_logger'] ) && 1 === (int) $commercekit_options['export_import_logger'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Display additional details within:', 'commercegurus-commercekit' ); ?>
				<strong><?php esc_html_e( 'WooCommerce > Status > Logs', 'commercegurus-commercekit' ); ?></strong>
			</label></td> </tr>
		</table>

		<hr />
		<h3 class="mb0"><?php esc_html_e( 'CommerceKit Exporter', 'commercegurus-commercekit' ); ?></h3>

		<p><?php esc_html_e( 'Export CommerceKit&#39;s Attribute Galleries and Attribute Swatches from your store into a CSV file.', 'commercegurus-commercekit' ); ?></p>

		<?php
		$exporting_csv = isset( $commercekit_options['exporting_csv'] ) && 1 === (int) $commercekit_options['exporting_csv'] ? true : false;
		$download_link = '#';
		$temp_csv_file = '';
		if ( isset( $commercekit_options['exporting_csv_file'] ) && ! empty( $commercekit_options['exporting_csv_file'] ) ) {
			$temp_csv_file = $commercekit_options['exporting_csv_file'];
		}
		if ( ! $exporting_csv && ! empty( $temp_csv_file ) && file_exists( $upload_dir['basedir'] . '/' . $temp_csv_file ) ) {
			$download_link = admin_url( 'admin-ajax.php?action=commercekit_download_export_csv&commercekit_nonce=' . wp_create_nonce( 'commercekit_settings' ) );
		}
		?>
		<button type="button" class="button-primary" id="commercekit-export" <?php echo true === $exporting_csv ? 'style="opacity: 0.5;" disabled="disabled" data-exporting_csv="1"' : 'data-exporting_csv="0"'; ?>><?php esc_html_e( 'Export into CSV file', 'commercegurus-commercekit' ); ?></button>

		<div class="export-status">
		<!-- Start shortly -->
		<div class="cache-event-alert" id="export-prepare">
			<div class="cache-loader">
				<div class="att-loader"></div><?php esc_html_e( 'Preparing to generate the CSV file...', 'commercegurus-commercekit' ); ?>
			</div>
		</div>

		<!-- Switch to this when processing -->
		<div class="cache-event-alert" id="export-processing">
			<div class="cache-processing">
				<div class="cache-bar">
					<div class="cache-progress" id="export_percent" style="width: 0%"></div>
				</div>
				<div class="cache-value">
					<div class="att-loader"></div><?php esc_html_e( 'Generating the CSV file.', 'commercegurus-commercekit' ); ?>&nbsp;<span id="export_complete">0</span>/<span id="export_total">0</span>&nbsp;<?php esc_html_e( ' products completed...', 'commercegurus-commercekit' ); ?>
				</div>
			</div>
		</div>

		<!-- Switch to this when completed -->
		<div class="cache-event-alert" id="export-completed">
			<div class="cache-processing">
				<div class="cache-bar">
					<div class="cache-completed" style="width: 100%"></div>
				</div>
				<div class="cache-value completed">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<?php esc_html_e( 'CSV file generation complete.', 'commercegurus-commercekit' ); ?>&nbsp;&nbsp;<a id="download_csv" href="<?php echo esc_url( $download_link ); ?>" class="download-link"><?php esc_html_e( 'Download CSV file', 'commercegurus-commercekit' ); ?></a>
				</div>
			</div>
		</div>
		</div>

		<hr />
		<h3 class="mb0"><?php esc_html_e( 'CommerceKit Importer', 'commercegurus-commercekit' ); ?></h3>

		<p><?php esc_html_e( 'Import CommerceKit&#39;s Attribute Galleries and Attribute Swatches into your store from a CSV file.', 'commercegurus-commercekit' ); ?></p>
		<?php
		$importing_csv = isset( $commercekit_options['importing_csv'] ) && 1 === (int) $commercekit_options['importing_csv'] ? true : false;
		$importing_csv = isset( $commercekit_options['importing_csv_fail'] ) && 1 === (int) $commercekit_options['importing_csv_fail'] ? false : $importing_csv;
		?>
		<div id="commercekit-import-wrap"><input type="text" name="csv_file_name" id="csv_file_name" size="30" readonly="readonly" /><button type="button" class="button" id="csv_file_browse"><?php esc_html_e( 'Browse CSV file', 'commercegurus-commercekit' ); ?></button>&nbsp;&nbsp;<button type="submit" class="button-primary" id="commercekit-import" data-csv_uploaded="<?php echo esc_attr( $csv_uploaded ); ?>" <?php echo true === $importing_csv ? 'style="opacity: 0.5;" disabled="disabled" data-importing_csv="1"' : 'style="opacity: 0.5;" disabled="disabled" data-importing_csv="0"'; ?>><?php esc_html_e( 'Import', 'commercegurus-commercekit' ); ?></button><input type="file" name="import_csv_file" id="import_csv_file" /></div>

		<div class="import-status">
		<!-- Start shortly -->
		<div class="cache-event-alert" id="import-prepare">
			<div class="cache-loader">
				<div class="att-loader"></div><?php esc_html_e( 'Preparing to import the CSV file...', 'commercegurus-commercekit' ); ?>
			</div>
		</div>

		<!-- Switch to this when processing -->
		<div class="cache-event-alert" id="import-processing">
			<div class="cache-processing">
				<div class="cache-bar">
					<div class="cache-progress" id="import_percent" style="width: 0%"></div>
				</div>
				<div class="cache-value">
					<div class="att-loader"></div><?php esc_html_e( 'Importing the CSV file.', 'commercegurus-commercekit' ); ?>&nbsp;<span id="import_complete">0</span>/<span id="import_total">0</span>&nbsp;<?php esc_html_e( ' products completed...', 'commercegurus-commercekit' ); ?>
				</div>
			</div>
		</div>

		<!-- Switch to this when completed -->
		<div class="cache-event-alert" id="import-completed">
			<div class="cache-processing">
				<div class="cache-bar">
					<div class="cache-completed" style="width: 100%"></div>
				</div>
				<div class="cache-value completed">
					<span>
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<?php esc_html_e( 'CSV import complete. You will need to also', 'commercegurus-commercekit' ); ?> <a href="?page=commercekit&amp;tab=settings"><?php esc_html_e( ' clear the CommerceKit cache', 'commercegurus-commercekit' ); ?></a><?php esc_html_e( '.', 'commercegurus-commercekit' ); ?>
					</span>
				</div>
			</div>
		</div>

		<div class="cache-event-alert" id="import-failed">
			<div class="log-message log-failed"><?php esc_html_e( 'Importing has failed due to an invalid CSV file.', 'commercegurus-commercekit' ); ?></div>
		</div>
		</div>

		</div>

		<input type="hidden" name="tab" value="exporter" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>
</div>

<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Import / Export', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'Not all CommerceKit data is included in an export.', 'commercegurus-commercekit' ); ?></p>
	<p><?php esc_html_e( 'This allows you to export CommerceKit&#39;s Attribute Galleries and Attribute Swatches data into a CSV file in order to import them into another site.', 'commercegurus-commercekit' ); ?></p>

	<p><?php esc_html_e( 'You should first use the native WooCommerce', 'commercegurus-commercekit' ); ?>

	<a href="https://woocommerce.com/document/product-csv-importer-exporter/" target="_blank"><?php esc_html_e( 'Product CSV Importer and Exporter', 'commercegurus-commercekit' ); ?></a>

	<?php esc_html_e( 'to move your products before performing this.', 'commercegurus-commercekit' ); ?></p>

	<p><?php esc_html_e( 'Ensure that "Yes, export all custom meta" is ticked.', 'commercegurus-commercekit' ); ?></p>
</div>
<style>
.commercekit-export-status *, .commercekit-import-status * { margin-left: 10px; }
.commercekit-import-status { margin-top: 15px; }
#commercekit-export { margin: 10px 0px; }
#commercekit-import-wrap { margin: 20px 0px; }
#import_csv_file { display: none; }
#cgkit-exporter-logger strong { font-weight:500; }
#csv_file_name, #csv_file_brose { cursor: pointer; margin-bottom:10px; font-size: 14px; }
</style>
<script>
jQuery(document).ready(function(){
	if( jQuery('#commercekit-export').length > 0 ) {
		jQuery('#commercekit-export').on('click', function(){
			update_commercekit_export_status(true);
		});
		update_commercekit_export_status(false);
	}
	if( jQuery('#commercekit-import').length > 0 ) {
		if( jQuery('#commercekit-import').data('csv_uploaded') == 1 ){
			update_commercekit_import_status(true);
		} else {
			update_commercekit_import_status(false);
		}
	}
	jQuery('#csv_file_name, #csv_file_browse').on('click', function(){
		jQuery('#import_csv_file').click();
	});
	jQuery('#import_csv_file').on('change', function(){
		var regex = new RegExp('(.*?)\.(csv)$');
		var filename = jQuery(this).val();
		var importing_csv = jQuery('#commercekit-import').data('importing_csv');
		if( importing_csv != 1 ) {
			if( regex.test( filename.toLowerCase() ) ){
				jQuery('#commercekit-import').css('opacity', '1');
				jQuery('#commercekit-import').removeAttr('disabled');
				jQuery('#csv_file_name').val(filename.split('\\').pop());
			} else {
				jQuery('#commercekit-import').css('opacity', '0.5');
				jQuery('#commercekit-import').attr('disabled', 'disabled');
				jQuery('#import_csv_file').val('');
				jQuery('#csv_file_name').val('');
				alert('<?php esc_html_e( 'Please select only a CSV filetype.', 'commercegurus-commercekit' ); ?>');
			}
		} else {
			jQuery('#import_csv_file').val('');
			jQuery('#csv_file_name').val('');
			alert('<?php esc_html_e( 'Please wait. The CommerceKit importer is running in the background.', 'commercegurus-commercekit' ); ?>');
		}
	});
	<?php if ( 1 === $msg ) { ?>
	window.history.pushState(null, null, '<?php echo admin_url( 'admin.php?page=commercekit&tab=exporter' ); // phpcs:ignore ?>');
	<?php } ?>
});
function update_commercekit_export_status(generate){
	var exp_btn = jQuery('#commercekit-export');
	exp_btn.attr('disabled', 'disabled').css('opacity', '0.5');
	var generate_csv = generate ? 1 : 0;
	if( generate_csv == 1 ){
		jQuery('#cgkit-as-plp-options .export-status .cache-event-alert').hide();
		jQuery('#export-prepare').show();
	}
	var nonce = jQuery('#commercekit_nonce').val();
	jQuery.ajax({
		url: ajaxurl+'?action=commercekit_generate_export_csv',
		type: 'POST',
		data: { generate_csv: generate_csv, commercekit_nonce: nonce },
		dataType: 'json',
		success: function( json ) {
			jQuery('#cgkit-as-plp-options .export-status .cache-event-alert').hide();
			if( json.exporting_csv == 1 ){
				if( json.complete == 0 ){
					jQuery('#export-prepare').show();
				} else if( json.total && json.complete < json.total ) {
					jQuery('#export_percent').css('width', json.percent+'%');
					jQuery('#export_total').html(json.total);
					jQuery('#export_complete').html(json.complete);
					jQuery('#export-processing').show();
				}
				setTimeout( function(){ update_commercekit_export_status(false); }, 5000 );
			} else {
				if( json.total && json.complete == json.total && json.download_link == 1 ) {
					jQuery('#download_csv').attr('href', ajaxurl+'?action=commercekit_download_export_csv&commercekit_nonce='+nonce);
					jQuery('#export-completed').show();
				}
				exp_btn.removeAttr('disabled').css('opacity', '1');
			}
		}
	});
}
function update_commercekit_import_status(generate){
	var import_csv = generate ? 1 : 0;
	if( import_csv == 1 ){
		jQuery('#cgkit-as-plp-options .import-status .cache-event-alert').hide();
		jQuery('#import-prepare').show();
	}
	var nonce = jQuery('#commercekit_nonce').val();
	jQuery.ajax({
		url: ajaxurl+'?action=commercekit_generate_import_csv',
		type: 'POST',
		data: { import_csv: import_csv, commercekit_nonce: nonce },
		dataType: 'json',
		success: function( json ) {
			jQuery('#cgkit-as-plp-options .import-status .cache-event-alert').hide();
			if( json.importing_csv_fail == 1 ){
				jQuery('#import-failed').show();
			} else if( json.importing_csv == 1 ){
				if( json.complete == 0 ){
					jQuery('#import-prepare').show();
				} else if( json.total && json.complete < json.total ) {
					jQuery('#import_percent').css('width', json.percent+'%');
					jQuery('#import_total').html(json.total);
					jQuery('#import_complete').html(json.complete);
					jQuery('#import-processing').show();
				}
				setTimeout( function(){ update_commercekit_import_status(false); }, 5000 );
			} else {
				if( json.total && json.complete == json.total ) {
					jQuery('#import-completed').show();
				}
			}
		}
	});
}
</script>

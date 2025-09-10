<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 
$has_license_key = !empty( $license_key );
$has_valid_license_key = empty( $license_error ) && $has_license_key;
?>
<?php if(!$has_license_key): ?>
<p>Activate your license to get priority support and automatic update from your WordPress dashboard.</p>
<p class="license-status" style="color: #ca8a04;">
Status: Pending activation
</p>
<?php elseif ($license_status == 'active'): ?>
<p class="license-status" style="color: #16a34a;">
    <span class="dashicons dashicons-yes"></span> Status: Active
</p>
<?php elseif ($license_status == 'expired' && $has_license_key): ?>
<p>Renew your license to continue receiving priority support and automatic updates.</p>
<p class="license-status" style="color: #d63638;">
   Status: Expired
</p>
<?php elseif($license_status == 'invalid'): ?>
<p>The provided license key is invalid. Please use another license key.</p>
<?php endif; ?>
<form id="<?php echo esc_attr( 'wrap_license_' . $package_slug ); ?>" >
	<p class="license-message" style="font-weight: bold;"></p>
	
	<p>
		<label><?php esc_html_e( 'License key', 'wp-package-updater' ); ?></label> <input placeholder="Enter license key to activate" class="regular-text license" type="text" id="<?php echo esc_attr( 'license_key_' . $package_id); ?>" value="<?php echo $license_key ?>" >
	
		<button type="button"  class="button-primary deactivate-license" <?php echo $has_license_key ? '' : 'style="display:none"'   ?>
		data-pending-text="Deactivating..."
		value="deactivate">Deactivate license</button>

	<button type="button"  class="button-primary activate-license" <?php echo $has_license_key ?  'style="display:none"' : ''  ?>
		data-pending-text="Activating..."
		value="activate" >
	Activate license
	</button>
	
</p>
</form>

<p class="description" style="font-style: italic;">Having trouble activating your license? <a href="https://sirplugin.com/contact.html" target="_blank">Contact us</a>.</p>


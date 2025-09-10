<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://nabillemsieh.com
 * @since      1.0.0
 *
 * @package    WP_Smart_Image_Resize
 * @subpackage WP_Smart_Image_Resize/templates
 */

$current_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
?>
<div class="wrap">
    
    <h1>Smart Image Resize Pro
    <span style="color: #646970; font-size: 12px; margin: 5px 0 15px;">
        v<?php echo WP_SIR_VERSION; ?>
    </span>
    </h1>
    
    
    

    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-smart-image-resize&tab=general"
        class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : '' ?>">Settings</a>
        <a href="?page=wp-smart-image-resize&tab=bulk-regenerate"
           class="nav-tab <?php echo $current_tab === 'bulk-regenerate' ? 'nav-tab-active' : '' ?>">Bulk Regenerate Images</a>
         
        
        <a href="?page=wp-smart-image-resize&tab=manage_license"
           class="nav-tab <?php echo $current_tab === 'manage_license' ? 'nav-tab-active' : '' ?>">Manage License</a>
        
        <a href="?page=<?php echo WP_SIR_NAME; ?>&tab=help" 
           class="nav-tab <?php echo $current_tab === 'help' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Help', 'wp-smart-image-resize'); ?>
        </a>
    </h2>

    <?php if ( $current_tab === 'general' ): ?>
        <div class="wpsirSettingsContainer">
            
            <div class="wp-sir-main-settings">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( WP_SIR_NAME );
                    do_settings_sections( WP_SIR_NAME );
                    ?>
                    <div class="wp-sir-save-buttons">
                        <?php submit_button(null, 'primary', 'submit', true, array('style' => 'margin-right: 10px;')); ?>
                        <?php submit_button('Save and Bulk Regenerate', 'secondary', 'submit_and_bulk_resize', true); ?>
                    </div>
                </form>
            </div>
            <div class="wp-sir-sidebar">
                
            </div>
        </div>
    <?php endif;
    if ( $current_tab === 'bulk-regenerate' ):
        ?>
        <div class="wp-sir-bulk-regenerate">
            <div class="wp-sir-header">
                <h2>Bulk Regenerate Images</h2>
                <p class="wp-sir-intro">Follow these steps to update your existing images according to your current settings. This will ensure all your images are properly resized and optimized.</p>
            </div>
            
            <div class="wp-sir-steps">
                <?php if(in_array('regenerate-thumbnails/regenerate-thumbnails.php', apply_filters('active_plugins', get_option('active_plugins')))): ?>
                    <div class="wp-sir-step active">
                        <div class="wp-sir-step-number">1</div>
                        <div class="wp-sir-step-content">
                            <h4>Navigate to Regenerate Thumbnails</h4>
                            <p>Go to <a href="<?php echo admin_url() ?>tools.php?page=regenerate-thumbnails" class="button button-secondary">Tools → Regenerate Thumbnails</a></p>
                        </div>
                    </div>
                    <div class="wp-sir-step">
                        <div class="wp-sir-step-number">2</div>
                        <div class="wp-sir-step-content">
                            <h4>Start the Process</h4>
                            <p>Click the <code>Regenerate Thumbnails For All Attachments</code> button to begin processing your images</p>
                            <div class="wp-sir-step-info">
                                <span class="dashicons dashicons-info"></span>
                                <span>This process may take several minutes depending on the number of images</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="wp-sir-step active">
                        <div class="wp-sir-step-number">1</div>
                        <div class="wp-sir-step-content">
                            <h4>Install Regenerate Thumbnails <span class="wp-sir-help-tip" title="Regenerate Thumbnails is a tiny tool that helps process your existing images in bulk. When you regenerate images, it triggers our plugin to apply your current resizing and optimization settings to the selected images."></span></h4>
                            <?php 
                            $rt_path = 'regenerate-thumbnails/regenerate-thumbnails.php';
                            $button_text = 'Download & Install Regenerate Thumbnails';
                            
                            if (file_exists(WP_PLUGIN_DIR . '/' . $rt_path)) {
                                $button_text = 'Activate Regenerate Thumbnails';
                            }
                            ?>
                            <p>
                                <button type="button" class="button button-primary" id="sir-install-rt">
                                    <span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 5px;"></span>
                                    <?php echo esc_html($button_text); ?>
                                </button>
                            </p>
                            <div class="wp-sir-step-info">
                                <span class="dashicons dashicons-info"></span>
                                <span>This plugin is required to bulk process your existing images</span>
                            </div>
                        </div>
                    </div>
                    <div class="wp-sir-step">
                        <div class="wp-sir-step-number">2</div>
                        <div class="wp-sir-step-content">
                            <h4>Navigate to Regenerate Thumbnails</h4>
                            <p>Go to <a href="<?php echo admin_url() ?>tools.php?page=regenerate-thumbnails" class="button button-secondary">Tools → Regenerate Thumbnails</a></p>
                        </div>
                    </div>
                    <div class="wp-sir-step">
                        <div class="wp-sir-step-number">3</div>
                        <div class="wp-sir-step-content">
                            <h4>Start the Process</h4>
                            <p>Click the <code>Regenerate Thumbnails For All Attachments</code> button to begin processing your images</p>
                            <div class="wp-sir-step-info">
                                <span class="dashicons dashicons-info"></span>
                                <span>This process may take several minutes depending on the number of images</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="wp-sir-footer">
                <div class="wp-sir-note">
                    <h4><span class="dashicons dashicons-warning"></span> Important</h4>
                    <p>If you still see old images after regeneration, clear your:</p>
                    <ul>
                        <li>Browser cache</li>
                        <li>Caching plugin cache</li>
                        <li>Cloudflare cache (if used)</li>
                    </ul>
                    <p>This ensures the newly resized images are displayed properly.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php
    
    if ( $current_tab === 'manage_license' ):
        ?>
        <div>
            <?php do_action( 'wp_sir_manage_license' ); ?>
        </div>
    <?php
    endif;
    
    ?>
    <?php if ($current_tab === 'help'): ?>
        <?php $this->render_help_tab(); ?>
    <?php endif; ?>

</div>

<style>
.wp-sir-bulk-regenerate {
    max-width: 800px;
    margin: 20px 0;
}

.wp-sir-header {
    margin-bottom: 30px;
}

.wp-sir-intro {
    font-size: 14px;
    margin: 10px 0 0;
    color: #555;
    line-height: 1.5;
}

.wp-sir-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
}

.wp-sir-step {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.wp-sir-step.active {
    border-color: #2271b1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.wp-sir-step.completed {
    background: #f0f6fc;
    border-color: #72aee6;
}

.wp-sir-step-number {
    width: 30px;
    height: 30px;
    background: #2271b1;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.wp-sir-step.completed .wp-sir-step-number {
    background: #72aee6;
}

.wp-sir-step-content {
    flex-grow: 1;
}

.wp-sir-step-content h4 {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #1d2327;
}

.wp-sir-step-content p {
    margin: 0 0 10px 0;
    color: #555;
    font-size: 13px;
}

.wp-sir-step-content .button {
    margin-top: 8px;
}

.wp-sir-step code {
    background: #f0f0f1;
    padding: 3px 5px;
    border-radius: 3px;
    font-size: 12px;
}

.wp-sir-step .dashicons-yes-alt {
    color: #00a32a;
    vertical-align: middle;
}

.wp-sir-step-info {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-top: 10px;
    padding: 8px 12px;
    background: #f6f7f7;
    border-radius: 4px;
    font-size: 12px;
    color: #50575e;
}

.wp-sir-step-info .dashicons {
    color: #2271b1;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.wp-sir-footer {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.wp-sir-note {
    padding: 15px 20px;
    background: #f0f6fc;
    border-left: 4px solid #2271b1;
    border-radius: 4px;
}

.wp-sir-note-warning {
    background: #fcf9e8;
    border-left-color: #dba617;
}

.wp-sir-note h4 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #1d2327;
}

.wp-sir-note h4 .dashicons {
    color: #f0b849;
}

.wp-sir-note-warning h4 .dashicons {
    color: #dba617;
}

.wp-sir-note p {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: #50575e;
    line-height: 1.5;
}

.wp-sir-note ul {
    margin: 0 0 8px 0;
    padding-left: 20px;
    font-size: 13px;
    color: #50575e;
    list-style-type: disc;
}

.wp-sir-note ul li {
    margin-bottom: 4px;
    display: list-item;
}

.wp-sir-note ul li:last-child {
    margin-bottom: 0;
}

/* Help tip styling */
.wp-sir-help-tip {
    color: #666;
    display: inline-block;
    font-size: 1em;
    font-style: normal;
    height: 16px;
    line-height: 16px;
    margin-left: 4px;
    position: relative;
    vertical-align: middle;
    width: 16px;
}

.wp-sir-help-tip::after {
    font-family: Dashicons;
    content: "\f223";
}

/* Button hover effects */
.wp-sir-step-content .button {
    transition: all 0.2s ease;
}

.wp-sir-step-content .button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media screen and (max-width: 782px) {
    .wp-sir-step {
        padding: 15px;
    }
    
    .wp-sir-step-number {
        width: 24px;
        height: 24px;
        font-size: 12px;
    }
    
    .wp-sir-step-info {
        padding: 6px 10px;
    }
}

/* New styles for improved UX */
.wp-sir-settings-header {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px 20px;
    margin: 20px 0;
}

.wp-sir-intro {
    margin: 0;
    color: #50575e;
    font-size: 14px;
    line-height: 1.5;
}

.wpsirSettingsContainer {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.wp-sir-main-settings {
    flex: 1;
    min-width: 0; /* Prevent flex item from overflowing */
}

.wp-sir-sidebar {
    width: 300px;
    flex-shrink: 0;
}

.wp-sir-save-buttons {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.wpsirInfoBox {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.wpsirInfoBox h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #1d2327;
}

.wpsirInfoBox ul {
    margin: 0 0 20px 0;
    padding: 0;
    list-style: none;
}

.wpsirInfoBox li {
    margin-bottom: 10px;
    font-size: 13px;
    color: #50575e;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.wpsirInfoBox li:last-child {
    margin-bottom: 0;
}

.wpsirInfoBox .dashicons {
    color: #2271b1;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.wp-sir-upgrade-cta {
    text-align: center;
    padding: 15px 0 0;
    border-top: 1px solid #ddd;
}

.wp-sir-upgrade-cta .button {
    width: 100%;
    text-align: center;
    font-weight: 600;
    padding: 8px 0;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.wp-sir-upgrade-cta p {
    margin: 0;
    font-size: 12px;
    color: #646970;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.wp-sir-upgrade-cta .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

/* Improve form field styling */
.wp-sir-setting-group {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px 20px;
    margin-bottom: 15px;
}

.wp-sir-setting-title {
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 8px;
    display: block;
}

/* Responsive adjustments */
@media screen and (max-width: 782px) {
    .wpsirSettingsContainer {
        flex-direction: column;
    }
    
    .wp-sir-sidebar {
        width: 100%;
    }
}
</style>



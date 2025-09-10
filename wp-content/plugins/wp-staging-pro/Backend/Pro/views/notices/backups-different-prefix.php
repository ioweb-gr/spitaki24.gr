<?php
/**
 * @var $this \WPStaging\Backend\Pro\Notices\Notices
 * @see \WPStaging\Backend\Pro\Notices\Notices::messages
 */
?>
<div class="notice notice-warning wpstg-backups-diff-prefix-notice">
    <p>
        <strong><?php _e('WP STAGING - Please create a new backup.', 'wp-staging'); ?></strong> <br/>
        <?php echo sprintf(__('A backup created with previous version WP STAGING 4.0.2 can lead to login issues if the backup is restored on another host. If possible, create a new backup and delete the existing ones.<br>In case you need to keep your existing backup and are going to migrate it to another server, <a href="%s" target="_blank">read this article</a>.', 'wp-staging'), 'https://wp-staging.com/docs/can-not-login-after-restoring-backup/'); ?>
    </p>
    <p>
        <a href="javascript:void(0);" class="wpstg_dismiss_backups_diff_prefix_notice" title="Close this message"
            style="font-weight:bold;">
            <?php _e('Close this message', 'wp-staging') ?>
        </a>
    </p>
</div>
<script>
  jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.wpstg_dismiss_backups_diff_prefix_notice', function (e) {
      e.preventDefault();
      jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wpstg_dismiss_notice',
          wpstg_notice: 'backups_diff_prefix',
        },
        error: function error(xhr, textStatus, errorThrown) {
          console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);
          console.log(textStatus);
          alert('Unknown error. Please get in contact with us to solve it support@wp-staging.com');
        },
        success: function success(data) {
          jQuery('.wpstg-backups-diff-prefix-notice').slideUp('fast');
          return true;
        },
        statusCode: {
          404: function _() {
            alert('Something went wrong; can\'t find ajax request URL! Please get in contact with us to solve it support@wp-staging.com');
          },
          500: function _() {
            alert('Something went wrong; internal server error while processing the request! Please get in contact with us to solve it support@wp-staging.com');
          }
        }
      });
    });
  });
</script>
<?php
/**
 * @var string $cloneID The ID of the clone.
 * @var array  $data    An array of Clone data.
 * @var $license
 *
 * @see src/Backend/views/clone/ajax/single-overview.php:62
 */
?>
<a href="#" class="wpstg-edit-clone-data wpstg-clone-action" data-clone="<?php echo $cloneID ?>"
    title="<?php echo __("Edit data of this clone. Reconnect a clone to production site after moving to another server and changing paths.", "wp-staging") ?>">
    <?php _e("Edit Data", "wp-staging"); ?>
</a>

<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Backup\Service\BackupsFinder;

class Delete extends AbstractTemplateComponent
{
    private $backupsFinder;

    public function __construct(BackupsFinder $backupsFinder, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->backupsFinder = $backupsFinder;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        $md5 = isset($_POST['md5']) ? sanitize_text_field($_POST['md5']) : '';

        if (strlen($md5) !== 32) {
            wp_send_json([
                'error'   => true,
                'message' => __('Invalid request.', 'wp-staging'),
            ]);
        }

        $backups = $this->backupsFinder->findBackups();

        // Early bail: No backups found, nothing to delete
        if (empty($backups)) {
            wp_send_json([
                'error'   => true,
                'message' => __('No backups found, nothing to delete.', 'wp-staging'),
            ]);
        }

        /** @var \SplFileInfo $backup */
        foreach ($backups as $backup) {
            if ($md5 === md5($backup->getBasename())) {
                $deleted = unlink($backup->getRealPath());

                if ($deleted) {
                    wp_send_json([
                        'error'   => false,
                        'message' => __('Successfully deleted the backup.', 'wp-staging'),
                    ]);
                } else {
                    if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                        error_log('WPSTAGING: User tried to delete backup but "unlink" returned false. Backup that couldn\'t be deleted: ' . $backup->getRealPath());
                    }
                    wp_send_json([
                        'error'   => true,
                        'message' => __('Could not delete the backup. Maybe a permission issue?', 'wp-staging'),
                    ]);
                }
            }
        }
    }
}

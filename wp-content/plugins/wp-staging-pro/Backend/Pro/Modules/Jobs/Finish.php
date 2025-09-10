<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use WPStaging\Backend\Pro\Modules\Jobs\Backups\BackupUploadsDir;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;

/**
 * Class Finish
 * @package WPStaging\Backend\Modules\Jobs
 */
class Finish extends \WPStaging\Backend\Modules\Jobs\Job
{

    private $tables;
    private $db;

    /**
     * Start Module
     * @return object
     */
    public function start()
    {

        $this->db = WPStaging::getInstance()->get("wpdb");

        $this->getTableRecords();

        // Clean up
        $this->deleteTables();

        $this->scheduleCronToDeleteUploadsBackup();

        // Delete Cache Files
        $this->deleteCacheFiles();

        do_action('wpstg_pushing_complete');

        return [
            "status"       => 'finished',
            "percentage"   => 100,
            "total"        => $this->options->totalSteps,
            "step"         => $this->options->currentStep,
            "last_msg"     => $this->logger->getLastLogMsg(),
            "job_done"     => true
        ];
    }

    /**
     * Add cron for deleting the uploads backups if that option was selected
     */
    protected function scheduleCronToDeleteUploadsBackup()
    {
        $backup = new BackupUploadsDir($this);
        $backup->scheduleDeleteOfTheBackup();

        foreach ($backup->getLogs() as $log) {
            if ($log['type'] === Logger::TYPE_INFO) {
                $this->log($log['msg']);
            }
        }
    }

    /**
     * Delete Cache Files
     */
    protected function deleteCacheFiles()
    {
        $this->log("Finish: Deleting clone job's cache files...");

        // Clean cache files
        $this->cache->delete("clone_options");
        $this->cache->delete("files_to_copy");

        $this->log("Finish: Clone job's cache files have been deleted!");
    }

    /**
     * Delete tmp Tables
     */
    public function deleteTables()
    {

        foreach ($this->tables as $table) {
            $this->db->query("DROP TABLE {$table}");
        }
    }

    /**
     * Get tmp Tables
     */
    private function getTableRecords()
    {

        $tables = $this->db->get_results("SHOW TABLE STATUS LIKE 'wpstgtmp\_%'");

        $this->tables = [];

        foreach ($tables as $table) {
            $this->tables[] = $table->Name;
        }

        $this->tables = json_decode(json_encode($this->tables));
    }
}

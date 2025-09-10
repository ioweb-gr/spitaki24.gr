<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Pro\Backup\Exceptions\ProcessLockedException;

class BackupProcessLock
{
    use ResourceTrait;

    const LOCK_OPTION_NAME = 'wpstg_backup_process_locked';

    /**
     * @throws ProcessLockedException When the process is locked.
     */
    public function lockProcess()
    {
        $this->checkProcessLocked();
        update_option(self::LOCK_OPTION_NAME, time(), false);
    }

    public function unlockProcess()
    {
        delete_option(self::LOCK_OPTION_NAME);
    }

    /**
     * @param null $timeout The timeout, in seconds, to lock the process. Leave null to automatically set one.
     *
     * @throws ProcessLockedException When the process is locked.
     */
    public function checkProcessLocked($timeout = null)
    {
        if (is_null($timeout)) {
            $timeout = min(120, $this->getTimeLimit());
        }

        $processLocked = get_option(self::LOCK_OPTION_NAME);

        if (!$processLocked) {
            return;
        }

        if (!is_numeric($processLocked)) {
            $this->unlockProcess();

            return;
        }

        /*
         * Something is locking the process.
         *
         * Let's make sure the lock was placed in the last couple minutes, or else we unstuck it,
         * as a task is not supposed to run for this long (at least not in web requests).
         *
         * A process can get stuck when a Job fails to shutdown gracefully, for instance.
         */
        if ($processLocked < time() - $timeout) {
            $this->unlockProcess();

            return;
        }

        // Process is locked.
        $timeLeft = absint($timeout - (time() - $processLocked));

        throw ProcessLockedException::processAlreadyLocked($timeLeft);
    }
}

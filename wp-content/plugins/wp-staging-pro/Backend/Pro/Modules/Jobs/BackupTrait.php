<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use stdClass;

// Quick and dirty
trait BackupTrait
{
    protected function assignBackupId(stdClass $options)
    {
        if (!isset($options->backupIds) || !$options->backupIds) {
            $options->backupIds = new stdClass();
        }

        if (isset($options->backupIds->{$options->current})) {
            $id = $this->provideUniqueId($options, $options->backupIds->{$options->current});
            $options->backupIds->{$options->current} = $id;
            return;
        }

        /** @noinspection TypeUnsafeArraySearchInspection */
        $id = array_search($options->current, array_keys($options->existingClones));
        $options->backupIds->{$options->current} = $this->provideUniqueId($options, $id);
    }

    protected function provideUniqueId($options, $id)
    {
        foreach ($options->existingClones as $key => $value) {
            if ($options->current === $key) {
                continue;
            }

            if (isset($value['backupId']) && $id === $value['backupId']) {
                return $this->provideUniqueId($options, $id + 1);
            }
        }

        return $id;
    }
}

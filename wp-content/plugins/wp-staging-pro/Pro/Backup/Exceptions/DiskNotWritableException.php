<?php

namespace WPStaging\Pro\Backup\Exceptions;

use WPStaging\Framework\Exceptions\WPStagingException;

class DiskNotWritableException extends WPStagingException
{
    public static function diskNotWritable()
    {
        return new self(__('We cannot proceed, as we could not write files to disk. It is likely that the server disk is full or there is no write permission to directory wp-content/uploads. Please free up disk space on the server or correct the folder permission to 755.'), 100);
    }

    public static function willExceedFreeDiskSpace($neededBytes)
    {
        return new self(__(sprintf('Not enough disk space. Please free up at least %s in the server and try again.', size_format($neededBytes))), 200);
    }
}

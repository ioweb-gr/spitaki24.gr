<?php

namespace WPStaging\Pro\Backup\Exceptions;

use WPStaging\Framework\Exceptions\WPStagingException;

class ProcessLockedException extends WPStagingException
{
    public static function processAlreadyLocked($timeLeft)
    {
        return new self(__(sprintf('It seems another export/restore task is already running. Please wait %d seconds and try again. If you continue to see this error, please contact WPSTAGING support.', absint($timeLeft)), 'wp-staging'), 423);
    }
}

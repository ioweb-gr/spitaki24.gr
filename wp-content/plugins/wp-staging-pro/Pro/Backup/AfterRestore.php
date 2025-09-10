<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Security\AccessToken;
use WPStaging\Pro\Backup\Ajax\Import\PrepareImport;

class AfterRestore
{
    protected $tableService;
    protected $accessToken;

    public function __construct(TableService $tableService, AccessToken $accessToken)
    {
        $this->tableService = $tableService;
        $this->accessToken = $accessToken;
    }

    /**
     * @param          $userLogin
     * @param \WP_User $user
     */
    public function loginAfterRestore($userLogin, $user)
    {
        // Early bail: Not a login after a successful restore
        if (get_option('wpstg.restore.justRestored') !== 'yes') {
            return;
        }

        // Disable WordPress automatic background updates on this request.
        add_filter('automatic_updater_disabled', '__return_false');

        if (apply_filters('wpstg.backup.restore.database.dropOldTablesAfterRestore', true)) {
            $this->tableService->deleteTablesStartWith(PrepareImport::TMP_DATABASE_PREFIX_TO_DROP, [], true);
        }

        $this->accessToken->generateNewToken();
        delete_option('wpstg.restore.justRestored');
        delete_option('wpstg.restore.justRestored.metadata');
    }
}

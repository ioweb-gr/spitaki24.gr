<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Security\AccessToken;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Ajax\Import\PrepareImport;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Service\Database\Exporter\ViewDDLOrder;
use WPStaging\Pro\Backup\Service\Database\Importer\TableViewsRenamer;
use WPStaging\Pro\Backup\Task\ImportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class RenameDatabaseTask extends ImportTask
{
    private $tableService;

    private $tableViewsRenamer;

    private $accessToken;

    // eg: ['wp123456_options']
    protected $tablesBeingImported = [];

    // eg: ['options']
    protected $tablesBeingImportedUnprefixed = [];

    // eg: ['wp_options']
    protected $existingTables = [];

    // eg: ['options']
    protected $existingTablesUnprefixed = [];

    /** @var int How many new tables were imported */
    protected $newTablesImported = 0;

    /** @var array An structured array of options to keep */
    protected $optionsToKeep = [];

    protected $viewDDLOrder;

    public function __construct(ViewDDLOrder $viewDDLOrder, TableService $tableService, TableViewsRenamer $tableViewsRenamer, AccessToken $accessToken, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->tableService = $tableService;
        $this->accessToken = $accessToken;
        $this->tableViewsRenamer = $tableViewsRenamer;
        $this->viewDDLOrder = $viewDDLOrder;
    }

    public static function getTaskName()
    {
        return 'backup_restore_rename_database';
    }

    public static function getTaskTitle()
    {
        return 'Renaming Database Tables';
    }

    public function execute()
    {
        $this->stepsDto->setTotal(1);

        // Store some information to re-add after we import the database.
        $originalAccessToken = $this->accessToken->getToken();
        $originalIsPluginActiveForNetwork = is_plugin_active_for_network(WPSTG_PLUGIN_FILE);

        $this->keepOptions();

        $this->tablesBeingImported = [
            'views' => $this->tableService->findViewsNamesStartWith($this->jobDataDto->getTmpDatabasePrefix()) ?: [],
            'tables' => $this->tableService->findTableNamesStartWith($this->jobDataDto->getTmpDatabasePrefix()) ?: [],
        ];
        $this->tablesBeingImported['all'] = array_merge($this->tablesBeingImported['tables'], $this->tablesBeingImported['views']);

        // Make a copy of the array of tables being imported, without the prefix.
        foreach ($this->tablesBeingImported as $viewsOrTables => $tableName) {
            $this->tablesBeingImportedUnprefixed[$viewsOrTables] = array_map(function ($tableName) {
                return substr($tableName, strlen($this->jobDataDto->getTmpDatabasePrefix()));
            }, $this->tablesBeingImported[$viewsOrTables]);
        }

        $this->existingTables = [
            'views' => $this->tableService->findViewsNamesStartWith($this->tableService->getDatabase()->getPrefix()) ?: [],
            'tables' => $this->tableService->findTableNamesStartWith($this->tableService->getDatabase()->getPrefix()) ?: [],
        ];
        $this->existingTables['all'] = array_merge($this->existingTables['tables'], $this->existingTables['views']);

        // Make a copy of the array of existing tables, without the prefix.
        foreach ($this->existingTables as $viewsOrTables => $tableName) {
            $this->existingTablesUnprefixed[$viewsOrTables] = array_map(function ($tableName) {
                return substr($tableName, strlen($this->tableService->getDatabase()->getPrefix()));
            }, $this->existingTables[$viewsOrTables]);
        }

        $this->importConflictingTables();
        $this->importNonConflictingTables();
        $this->renameViewReferences();

        foreach ($this->getTablesThatExistInSiteButNotInBackup() as $table) {
            $this->tableService->getDatabase()->exec(sprintf(
                "RENAME TABLE %s TO %s;",
                $this->tableService->getDatabase()->getPrefix() . $table,
                PrepareImport::TMP_DATABASE_PREFIX_TO_DROP . $table
            ));
        }

        $this->logger->info(sprintf('Imported %d/%d new tables', $this->newTablesImported, $this->newTablesImported));

        $this->postDatabaseRestoreActions($originalAccessToken, $originalIsPluginActiveForNetwork);

        // Logs the user out
        wp_logout();

        return $this->generateResponse();
    }

    /**
     * This is an adaptation of wp_load_alloptions(), the difference is that it
     * fetches only the "option_name" from the database, not the values, to save memory.
     *
     * @see wp_load_alloptions()
     *
     * @return array An array of option names that are autoloaded.
     */
    protected function getAutoloadedOptions()
    {
        global $wpdb;
        $suppress = $wpdb->suppress_errors();
        $allOptionsDb = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE autoload = 'yes'");
        $wpdb->suppress_errors($suppress);

        $allOptions = [];
        foreach ((array)$allOptionsDb as $o) {
            $allOptions[] = $o->option_name;
        }

        return $allOptions;
    }

    protected function keepOptions()
    {
        $allOptions = $this->getAutoloadedOptions();

        // Backups do not include staging sites, so we need to keep the original ones after restoring.
        $this->optionsToKeep[] = [
            'name' => 'wpstg_existing_clones_beta',
            'value' => get_option('wpstg_existing_clones_beta'),
            'autoload' => in_array('wpstg_existing_clones_beta', $allOptions),
        ];

        // Keep the original WPSTAGING settings intact upon importing.
        $this->optionsToKeep[] = [
            'name' => 'wpstg_settings',
            'value' => get_option('wpstg_settings'),
            'autoload' => in_array('wpstg_settings', $allOptions),
        ];

        // If this is a staging site, keep the staging site status after restore.
        $this->optionsToKeep[] = [
            'name' => 'wpstg_is_staging_site',
            'value' => get_option('wpstg_is_staging_site'),
            'autoload' => in_array('wpstg_is_staging_site', $allOptions),
        ];
    }

    /**
     * Executes actions after a database has been restored.
     *
     * @param $originalAccessToken
     * @param $originalIsPluginActiveForNetwork
     */
    protected function postDatabaseRestoreActions($originalAccessToken, $originalIsPluginActiveForNetwork)
    {
        /**
         * @var \wpdb            $wpdb
         * @var \WP_Object_Cache $wp_object_cache
         */
        global $wpdb, $wp_object_cache;

        // Make sure WordPress does not try to re-use any values fetched from the database thus far.
        $wpdb->flush();
        $wp_object_cache->flush();
        wp_suspend_cache_addition(true);

        // Prevent filters tampering with the active plugins list, such as wpstg-optimizer.php itself.
        remove_all_filters('option_active_plugins');
        remove_all_filters('site_option_active_sitewide_plugins');

        foreach ($this->optionsToKeep as $optionToKeep) {
            update_option($optionToKeep['name'], $optionToKeep['value'], $optionToKeep['autoload']);
        }

        update_option('wpstg.restore.justRestored', 'yes');
        update_option('wpstg.restore.justRestored.metadata', wp_json_encode($this->jobDataDto->getBackupMetadata()));

        // Re-set the Access Token as it was before importing the database, so the requests remain authenticated
        $this->accessToken->setToken($originalAccessToken);

        // Force direct activation of this plugin in the database by bypassing activate_plugin at a low-level.
        $plugin = plugin_basename(trim(WPSTG_PLUGIN_FILE));

        if ($originalIsPluginActiveForNetwork) {
            $current = get_site_option('active_sitewide_plugins', []);
            $current[$plugin] = time();
            update_site_option('active_sitewide_plugins', $current);
        } else {
            $current = get_option('active_plugins', []);

            // Disable all other WPSTAGING plugins
            $current = array_filter($current, function ($pluginSlug) {
                return strpos($pluginSlug, 'wp-staging') === false;
            });

            // Enable this plugin
            $current[] = $plugin;

            sort($current);
            update_option('active_plugins', $current);
        }

        // Upgrade database if need be
        if (file_exists(trailingslashit(ABSPATH) . 'wp-admin/includes/upgrade.php')) {
            global $wpdb, $wp_db_version, $wp_current_db_version;
            require_once trailingslashit(ABSPATH) . 'wp-admin/includes/upgrade.php';

            $wp_current_db_version = (int)__get_option('db_version');
            if ($wp_db_version !== $wp_current_db_version) {
                // WP upgrade isn't too fussy about generating MySQL warnings such as "Duplicate key name" during an upgrade so suppress.
                $wpdb->suppress_errors();

                wp_upgrade();

                $this->logger->info(sprintf('WordPress database upgraded successfully from db version %s to %s.', $wp_current_db_version, $wp_db_version));
            }
        } else {
            $this->logger->warning('Could not upgrade WordPress database version as the wp-admin/includes/upgrade.php file does not exist.');
        }

        do_action('wpstg.backup.import.database.postDatabaseRestoreActions');
    }

    protected function importConflictingTables()
    {
        $this->tableService->getDatabase()->exec('START TRANSACTION;');
        foreach ($this->getTablesThatExistInSiteAndInBackup() as $conflictingTable) {
            // Prefix existing table with toDrop prefix
            $this->tableService->getDatabase()->exec(sprintf(
                "RENAME TABLE %s TO %s;",
                $this->tableService->getDatabase()->getPrefix() . $conflictingTable,
                PrepareImport::TMP_DATABASE_PREFIX_TO_DROP . $conflictingTable
            ));

            // Rename imported table to existing
            $this->tableService->getDatabase()->exec(sprintf(
                "RENAME TABLE %s TO %s;",
                $this->jobDataDto->getTmpDatabasePrefix() . $conflictingTable,
                $this->tableService->getDatabase()->getPrefix() . $conflictingTable
            ));

            $this->newTablesImported++;
        }
        $this->tableService->getDatabase()->exec('COMMIT;');
    }

    protected function importNonConflictingTables()
    {
        $this->tableService->getDatabase()->exec('START TRANSACTION;');
        foreach ($this->getTablesThatExistInBackupButNotInSite() as $nonConflictingTable) {
            // Rename imported table to original
            $this->tableService->getDatabase()->exec(sprintf(
                "RENAME TABLE %s TO %s;",
                $this->jobDataDto->getTmpDatabasePrefix() . $nonConflictingTable,
                $this->tableService->getDatabase()->getPrefix() . $nonConflictingTable
            ));
            $this->newTablesImported++;
        }
        $this->tableService->getDatabase()->exec('COMMIT;');
    }

    protected function renameViewReferences()
    {
        foreach ($this->tablesBeingImportedUnprefixed['views'] as $view) {
            $query = $this->tableService->getCreateViewQuery($this->tableService->getDatabase()->getPrefix() . $view);
            $query = str_replace($this->jobDataDto->getTmpDatabasePrefix(), $this->tableService->getDatabase()->getPrefix(), $query);
            $this->viewDDLOrder->enqueueViewToBeWritten($this->tableService->getDatabase()->getPrefix() . $view, $query);
        }

        foreach ($this->viewDDLOrder->tryGetOrderedViews() as $tmpViewName => $viewQuery) {
            $this->tableViewsRenamer->renameViewReferences($viewQuery);
        }
    }

    protected function getTablesThatExistInSiteAndInBackup()
    {
        return array_intersect($this->tablesBeingImportedUnprefixed['all'], $this->existingTablesUnprefixed['all']);
    }

    protected function getTablesThatExistInSiteButNotInBackup()
    {
        return array_diff($this->existingTablesUnprefixed['all'], $this->tablesBeingImportedUnprefixed['all']);
    }

    protected function getTablesThatExistInBackupButNotInSite()
    {
        return array_diff($this->tablesBeingImportedUnprefixed['all'], $this->existingTablesUnprefixed['all']);
    }
}

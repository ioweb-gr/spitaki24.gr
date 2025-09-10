<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use Exception;
use WPStaging\Backend\Modules\Jobs\JobExecutable;
use WPStaging\Core\Utils\Helper;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Traits\DbRowsGeneratorTrait;
use WPStaging\Framework\Utils\Strings;

/**
 * Class SearchReplace
 *
 * Used for PUSHING
 *
 * @see \WPStaging\Backend\Modules\Jobs\SearchReplace Used for CLONING
 *
 * @todo Unify those
 *
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
class SearchReplace extends JobExecutable
{
    use DbRowsGeneratorTrait;

    /**
     * The maximum number of failed attempts after which the Job should just move on.
     *
     * @var int
     */
    protected $maxFailedAttempts  = 10;
    /**
     * The number of processed items, or `null` if the job did not run yet.
     *
     * @var int|null
     */
    protected $processed;

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var \WPDB
     */
    public $db;

    /**
     * The prefix of the new database tables which are used for the live site after updating tables
     * @var string
     */
    public $tmpPrefix;

    /**
     *
     * @var string
     */
    private $homeUrl;

    /**
     *
     * @var string
     */
    private $homeHost;

    /**
     * Initialize
     */
    public function initialize()
    {
        $this->total     = count($this->options->tables);
        $this->db        = WPStaging::getInstance()->get("wpdb");
        $this->tmpPrefix = 'wpstgtmp_';
        $helper          = new Helper();
        $this->homeHost  = $helper->getBaseUrlWithoutScheme();
        $this->homeUrl   = $helper->getHomeUrlWithoutScheme();
    }

    /**
     * Get destination Hostname e.g. domain.com or domain.com/subfolder if WP has been installed in a subfolder
     * @return string
     */
    private function getDestinationHost()
    {
        $helper          = new Helper();
        $destinationHost = $helper->getHomeUrlWithoutScheme();

        if ($this->isSubDir()) {
            $destinationHost = $helper->getHomeUrlWithoutScheme() . '/' . $this->getSubDir();
        }

        return $destinationHost;
    }

    public function start()
    {

        $this->run();

        // Save option, progress
        $this->saveOptions();

        return (object) $this->response;
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps()
    {
        $this->options->totalSteps = $this->total;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute()
    {
        // Over limits threshold
        if ($this->isOverThreshold()) {
            // Prepare response and save current progress
            $this->prepareResponse(false, false);
            $this->saveOptions();
            return false;
        }

        // No more steps, finished
        if ($this->options->currentStep > $this->total || !isset($this->options->tables[$this->options->currentStep])) {
            $this->prepareResponse(true, false);
            return false;
        }

        // Table is excluded
        if (!empty($this->options->tables[$this->options->currentStep]->name) && in_array((string) $this->options->tables[$this->options->currentStep]->name, $this->options->excludedTables)) {
            $table = $this->options->tables[$this->options->currentStep]->name;
            $this->log("DB Search & Replace: Table {$table} excluded", Logger::TYPE_INFO);
            $this->prepareResponse();
            return true;
        }

        // Search & Replace
        if (!$this->stopExecution() && !$this->updateTable($this->options->tables[$this->options->currentStep]->name)) {
            // Prepare Response
            $this->prepareResponse(false, false);

            // Not finished
            return true;
        }


        // Prepare Response
        $this->prepareResponse();

        // Not finished
        return true;
    }

    /**
     * Stop Execution immediately
     * return mixed bool | json
     */
    private function stopExecution()
    {
        if ($this->db->prefix == $this->tmpPrefix) {
            $this->returnException('Fatal Error 9: Prefix ' . $this->db->prefix . ' is used for the live site hence it can not be used for the staging site as well. Please ask support@wp-staging.com how to resolve this.');
        }
        return false;
    }

    /**
     * Copy Tables
     * @param string $tableName
     * @return bool
     */
    private function updateTable($tableName)
    {
        $strings      = new Strings();
        $table        = $strings->str_replace_first($this->options->prefix, '', $tableName);
        $newTableName = $this->tmpPrefix . $table;

        // Save current job
        $this->setJob($newTableName);

        // Beginning of the job
        if (!$this->startJob($newTableName)) {
            return true;
        }
        // Copy data
        $this->startReplace($newTableName);

        return $this->finishStep();
    }

    /**
     * Start search replace job
     * @param string $table
     */
    private function startReplace($table)
    {
        $rows = $this->options->job->start + $this->settings->querySRLimit;
        $this->log(
            "DB Search & Replace:  Table {$table} {$this->options->job->start} to {$rows} records"
        );

        // Search & Replace
        if (defined('WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR') && WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR) {
            $this->options->job->start += $this->settings->querySRLimit;
        }

        $this->searchReplace($table, []);

    }


    /**
     * Gets the columns in a table.
     * @access public
     * @param  string $table The table to check.
     * @return array|false Either an array of the table primary key (if any) and columns, or `false` to indicate
     *                     the table could not be described.
     */
    protected function get_columns($table)
    {
        $primary_key = null;
        $columns     = [];
        $fields      = $this->db->get_results('DESCRIBE ' . $table);

        if (empty($fields)) {
            // Either there was an error or the table has no columns.
            return false;
        }

        if (is_array($fields)) {
            foreach ($fields as $column) {
                $columns[] = $column->Field;
                if ($column->Key === 'PRI') {
                    $primary_key = $column->Field;
                }
            }
        }
        return [$primary_key, $columns];
    }

    /**
     * Return url without scheme
     * @param string $str
     * @return string
     */
    private function get_url_without_scheme($str)
    {
        return preg_replace('#^https?://#', '', rtrim($str, '/'));
    }

    /**
     *
     * @param string $table The table to run the replacement on.
     * @param array  $args  An associative array containing arguments for this run.
     * @return bool Whether the search-replace was successful or not.
     */
    private function searchReplace($table, $args)
    {
        $table = esc_sql($table);

        $helper             = new Helper();
        // Search URL example.com/staging and root path to staging site /var/www/htdocs/staging
        $args['search_for'] = [
            '\/\/' . str_replace('/', '\/', $this->get_url_without_scheme($this->options->url)), // \/\/host.com or \/\/host.com\/subfolder
            '//' . $this->get_url_without_scheme($this->options->url), // //host.com or //host.com/subfolder
            rtrim($this->options->path, DIRECTORY_SEPARATOR),
            str_replace('/', '%2F', $this->get_url_without_scheme($this->options->url))
        ];

        $args['replace_with'] = [
            '\/\/' . str_replace('/', '\/', $this->getDestinationHost()), // \/\/host.com or \/\/host.com\/subfolder
            '//' . $this->getDestinationHost(), // //host.com or //host.com/subfolder
            rtrim(ABSPATH, '/'),
            $helper->getHomeUrlWithoutScheme()
        ];


        $args['replace_guids']    = 'off';
        $args['dry_run']          = 'off';
        $args['case_insensitive'] = false;
        $args['skip_transients']  = 'off';

        if ($this->isMultisiteAndPro()) {
            $args['replace_mails'] = 'off';
            // Staging site has been created with WPSTG 2.8.2 or later. Do not search & replace the links to the uploads folder
            if (!empty( $this->options->existingClones[$this->options->current]["version"]) && version_compare($this->options->existingClones[$this->options->current]["version"], '2.8.2', '>=')) {
                // Search URL example.com/staging and root path to staging site /var/www/htdocs/staging
                $args['search_for'] = [
                    '\/\/' . str_replace('/', '\/', $this->get_url_without_scheme($this->options->url)),
                    '//' . $this->get_url_without_scheme($this->options->url),
                    rtrim($this->options->path, DIRECTORY_SEPARATOR),
                    $this->homeHost . '%2F' . $this->options->directoryName
                ];
                $args['replace_with'] = [
                    '\/\/' . str_replace('/', '\/', $this->homeUrl),
                    '//' . $this->homeUrl,
                    rtrim(ABSPATH, '/'),
                    $this->homeUrl
                ];
            } else {
                // Staging site has been created with WPSTG 2.8.1 or earlier. Search & replace the links to the uploads folder
                // Search URL example.com/staging and root path to staging site /var/www/htdocs/staging
                $args['search_for'] = [
                    '\/\/' . str_replace( '/', '\/', $this->get_url_without_scheme($this->options->url)),
                    '//' . $this->get_url_without_scheme($this->options->url),
                    rtrim($this->options->path, DIRECTORY_SEPARATOR),
                    $this->getImagePathStaging(),
                    $this->homeHost . '%2F' . $this->options->directoryName
                ];
                $args['replace_with'] = [
                    '\/\/' . str_replace('/', '\/', $this->homeUrl),
                    '//' . $this->homeUrl,
                    rtrim(ABSPATH, '/'),
                    $this->getImagePathLive(),
                    $this->homeUrl
                ];
            }
        }

        // Allow filtering of search & replace parameters
        $args = apply_filters('wpstg_push_searchreplace_params', $args);


        $this->log("DB Search & Replace: Table {$table}");

        // Get a list of columns in this table.
        $primaryKeyAndColumns = $this->get_columns($table);

        if (false === $primaryKeyAndColumns) {
            // Stop here: for some reason the table cannot be described or there was an error.
            ++$this->options->job->failedAttempts;
            return false;
        }

        list( $primary_key, $columns ) = $primaryKeyAndColumns;

        $current_row = 0;
        $start       = $this->options->job->start;

        //Make sure value is never smaller than 1 or greater than 20000
        $end  = $this->settings->querySRLimit;

        // Grab the content of the current table.
        if (defined('WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR') && WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR) {
            $data = $this->db->get_results("SELECT * FROM $table LIMIT $start, $end", ARRAY_A);
        } else {
            $data = $this->rowsGenerator($table, $start, $end, $this->db);
        }

        // Filter certain rows (of other plugins)
        $filter = [
            'Admin_custome_login_Slidshow',
            'Admin_custome_login_Social',
            'Admin_custome_login_logo',
            'Admin_custome_login_text',
            'Admin_custome_login_login',
            'Admin_custome_login_top',
            'Admin_custome_login_dashboard',
            'Admin_custome_login_Version',
        ];

        if (!$this->isMultisiteAndPro()) {
            $filter = array_merge($filter, [
                'upload_path',
                'wpstg_existing_clones_beta',
                'wpstg_existing_clones',
                'wpstg_settings',
                'wpstg_license_status',
                'siteurl',
                'home'
            ]);
        }

        $filter = apply_filters('wpstg_clone_searchreplace_excl_rows', $filter);

        $processed = 0;

        // Go through the table rows
        foreach ($data as $row) {
            $processed++;
            $current_row++;
            $update_sql = [];
            $where_sql  = [];
            $upd        = false;

            // Skip rows
            if (isset($row['option_name']) && in_array($row['option_name'], $filter)) {
                continue;
            }

            // Skip transients (There can be thousands of them. Save memory and increase performance)
            if (
                isset($row['option_name']) && $args['skip_transients'] === 'on' && strpos($row['option_name'], '_transient')
                !== false
            ) {
                continue;
            }
            // Skip rows with more than 5MB to save memory. These rows contain log data or something similiar but never site relevant data
            if (isset($row['option_value']) && strlen($row['option_value']) >= 5000000) {
                continue;
            }

            // Go through the columns
            foreach ($columns as $column) {
                $dataRow = $row[$column];

                // Skip column larger than 5MB
                $size = strlen($dataRow);
                if ($size >= 5000000) {
                    continue;
                }

                // Skip primary key column
                if ($column == $primary_key) {
                    $where_sql[] = $column . ' = "' . $this->mysql_escape_mimic($dataRow) . '"';
                    continue;
                }

                // Skip GUIDs by default.
                if ($args['replace_guids'] !== 'on' && $column === 'guid') {
                    continue;
                }

                // Skip mail addresses
                if ($this->isMultisiteAndPro() && $args['replace_mails'] === 'off' && strpos($dataRow, '@' . $this->homeHost) !== false ) {
                    continue;
                }

                // Run a search replace on the data that'll respect the serialisation.
                $i = 0;
                foreach ($args['search_for'] as $replace) {
                    $dataRow = $this->recursive_unserialize_replace($args['search_for'][$i], $args['replace_with'][$i], $dataRow, false, $args['case_insensitive']);
                    $i++;
                }
                unset($replace, $i);

                // Something was changed
                if ($row[$column] != $dataRow) {
                    $update_sql[] = $column . ' = "' . $this->mysql_escape_mimic($dataRow) . '"';
                    $upd          = true;
                }
            }

            // Determine what to do with updates.
            if ($args['dry_run'] === 'on') {
                // Don't do anything if a dry run
            } elseif ($upd && !empty($where_sql)) {
                // If there are changes to make, run the query.
                $sql    = 'UPDATE ' . $table . ' SET ' . implode(', ', $update_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
                $result = $this->db->query($sql);

                if (!$result) {
                    $this->log("Error updating row {$current_row}", Logger::TYPE_ERROR);
                }
            }
        } // end row loop
        unset($row, $update_sql, $where_sql, $sql, $current_row);

        if (
            !defined('WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR') ||
            (defined('WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR') && !WPSTG_DISABLE_SEARCH_REPLACE_GENERATOR)
        ) {
            $this->updateJobStart($processed, $this->db, $table);
        }

        // DB Flush
        $this->db->flush();
        return true;
    }

    /**
     * Adapted from interconnect/it's search/replace script.
     *
     * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
     *
     * Take a serialised array and unserialise it replacing elements as needed and
     * unserialising any subordinate arrays and performing the replace on those too.
     *
     * @access private
     * @param  string           $from               String we're looking to replace.
     * @param  string           $to                 What we want it to be replaced with
     * @param  array            $data               Used to pass any subordinate arrays back to in.
     * @param  boolean          $serialized         Does the array passed via $data need serialising.
     * @param  string|boolean              $case_insensitive     Set to 'on' if we should ignore case, false otherwise.
     *
     * @return string|array The original array with all elements replaced as needed.
     */
    private function recursive_unserialize_replace($from = '', $to = '', $data = '', $serialized = false, $case_insensitive = false)
    {
        try {
            // PDO instances can not be serialized or unserialized
            if (is_serialized($data) && strpos($data, 'O:3:"PDO":0:') !== false) {
                return $data;
            }
            // DateTime object can not be unserialized.
            // Would throw PHP Fatal error:  Uncaught Error: Invalid serialization data for DateTime object in
            // Bug PHP https://bugs.php.net/bug.php?id=68889&thanks=6 and https://github.com/WP-Staging/wp-staging-pro/issues/74
            if (is_serialized($data) && strpos($data, 'O:8:"DateTime":0:') !== false) {
                return $data;
            }
            // Some unserialized data cannot be re-serialized eg. SimpleXMLElements
            if (is_serialized($data) && ( $unserialized = @unserialize($data) ) !== false) {
                $data = $this->recursive_unserialize_replace($from, $to, $unserialized, true, $case_insensitive);
            } elseif (is_array($data)) {
                $tmp = [];
                foreach ($data as $key => $value) {
                    $tmp[$key] = $this->recursive_unserialize_replace($from, $to, $value, false, $case_insensitive);
                }

                $data = $tmp;
                unset($tmp);
            } elseif (is_object($data)) {
                $props = get_object_vars($data);

                // Do a search & replace
                if (empty($props['__PHP_Incomplete_Class_Name'])) {
                    $tmp = $data;
                    foreach ($props as $key => $value) {
                        if ($key === '' || ord($key[0]) === 0) {
                            continue;
                        }
                        $tmp->$key = $this->recursive_unserialize_replace($from, $to, $value, false, $case_insensitive);
                    }
                    $data  = $tmp;
                    $tmp   = '';
                    $props = '';
                    unset($tmp);
                    unset($props);
                }
            } else {
                if (is_string($data)) {
                    if (!empty($from) && !empty($to)) {
                        $data = $this->str_replace($from, $to, $data, $case_insensitive);
                    }
                }
            }

            if ($serialized) {
                return serialize($data);
            }
        } catch (Exception $error) {
        }

        return $data;
    }

    /**
     * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
     * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
     * @access public
     * @param  string $input The string to escape.
     * @return string
     */
    private function mysql_escape_mimic($input)
    {
        if (is_array($input)) {
            return array_map(__METHOD__, $input);
        }

        if (!empty($input) && is_string($input)) {
            return str_replace(['\\', "\0", "\n", "\r", "'", '"', "\x1a"], ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'], $input);
        }

        return $input;
    }

    /**
     * Wrapper for str_replace
     *
     * @param string $from
     * @param string $to
     * @param string $data
     * @param string|bool $case_insensitive
     *
     * @return string
     */
    private function str_replace($from, $to, $data, $case_insensitive = false)
    {
        if ($case_insensitive === 'on') {
            $data = str_ireplace($from, $to, $data);
        } else {
            $data = str_replace($from, $to, $data);
        }

        return $data;
    }

    /**
     * Set the job
     * @param string $table
     */
    private function setJob($table)
    {
        if (!empty($this->options->job->current)) {
            return;
        }

        $this->options->job->current = $table;
        $this->options->job->start   = 0;
    }

    /**
     * Start Job
     * @param string $table
     *
     * @return bool
     */
    private function startJob($table)
    {
        if ($this->isExcludedTable($table)) {
            return false;
        }

        if (!isset($this->options->job->failedAttempts)) {
            $this->options->job->failedAttempts = 0;
        }

        if ($this->options->job->start != 0) {
            // The job was attempted too many times and should be skipped now.
            return !($this->options->job->failedAttempts > $this->maxFailedAttempts);
        }

        $this->options->job->total = (int) $this->db->get_var("SELECT COUNT(1) FROM {$table}");
        $this->options->job->failedAttempts = 0;

        if ($this->options->job->total == 0) {
            $this->finishStep();
            return false;
        }

        return true;
    }

    /**
     * Is table excluded from search replace processing?
     * @param string $table
     * @return boolean
     */
    private function isExcludedTable($table)
    {
        $tables = $this->excludedTableService->getExcludedTablesForSearchReplacePushOnly();

        $excludedTables = [];
        foreach ($tables as $key => $value) {
            $excludedTables[] = $this->tmpPrefix . ltrim($value, '_');
        }

        if (in_array($table, $excludedTables)) {
            $this->log("DB Search & Replace: Table excluded by WP STAGING: {$table}", Logger::TYPE_INFO);
            return true;
        }

        return false;
    }

    /**
     * Finish the step
     */
    protected function finishStep()
    {
        // This job is not finished yet
        if ($this->options->job->total > $this->options->job->start) {
            return false;
        }

        // Add it to cloned tables listing
        $this->options->clonedTables[] = $this->options->tables[$this->options->currentStep];

        // Reset job
        $this->options->job = new \stdClass();

        return true;
    }

    /**
     * Check if WP is installed in subdir
     * @return boolean
     */
    private function isSubDir()
    {
        // Compare names without scheme to bypass cases where siteurl and home have different schemes http / https
        // This is happening much more often than you would expect
        $siteurl = preg_replace('#^https?://#', '', rtrim(get_option('siteurl'), '/'));
        $home = preg_replace('#^https?://#', '', rtrim(get_option('home'), '/'));

        return $home !== $siteurl;
    }

    /**
     * Get the install sub directory if WP is installed in sub directory
     * @return string
     */
    private function getSubDir()
    {
        $home = get_option('home');
        $siteurl = get_option('siteurl');

        if (empty($home) || empty($siteurl)) {
            return '';
        }

        return str_replace([$home, '/'], '', $siteurl);
    }

    /**
     * Updates the (next) job start to reflect the number of actually processed rows.
     *
     * If nothing was processed, then the job start  will be ticked by 1.
     *
     * @param int   $processed The  number of actually processed rows in this run.
     * @param \wpdb $db        The wpdb instance being used to process.
     * @param string $table    The table being processed.
     *
     * @return void The method does not return any value.
     */
    protected function updateJobStart($processed, \wpdb $db, $table)
    {
        $this->processed = absint($processed);

        // We make sure to increment the offset at least in 1 to avoid infinite loops.
        $minimumProcessed = 1;

        /*
         * There are some scenarios where we couldn't process any rows in this request.
         * The exact causes of this is still under investigation, but to mitigate this
         * effect, we will smartly set the offset for the next job based on some context.
         */
        if ($this->processed === 0) {
            $this->logDebug('SEARCH_REPLACE: Processed is zero');

            $totalRowsInTable = $db->get_var("SELECT COUNT(*) FROM $table");

            if (is_numeric($totalRowsInTable)) {
                $this->logDebug("SEARCH_REPLACE: Rows count is numeric: $totalRowsInTable");
                // Skip 1% of the current table on each iteration, with a minimum of 1 and a maximum of the query limit.
                $minimumProcessed = min(max((int)$totalRowsInTable / 100, 1), $this->settings->querySRLimit);
            } else {
                $this->logDebug(sprintf("SEARCH_REPLACE: Rows count is not numeric. Type: %s. Json encoded value: %s", gettype($totalRowsInTable), wp_json_encode($totalRowsInTable)));
                // Unexpected result from query. Set the offset to the limit.
                $minimumProcessed = $this->settings->querySRLimit;
            }

            $this->logDebug("SEARCH_REPLACE: Minimum processed is: $minimumProcessed");
        }

        $this->options->job->start += max($processed, $minimumProcessed);
    }

    /**
     * Returns the number of rows processed by the job.
     *
     * @return int|null Either the number of rows processed by the Job, or `null` if the Job did
     *                  not run yet.
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Get path to multisite image folder e.g. wp-content/blogs.dir/ID/files or wp-content/uploads/sites/ID
     * @return string
     */
    private function getImagePathLive()
    {
        // Check first which structure is used
        $uploads = wp_upload_dir();
        $basedir = $uploads['basedir'];
        $blogId = get_current_blog_id();

        if (strpos($basedir, 'blogs.dir') === false) {
            // Since WP 3.5
            $path = $blogId > 1 ? "wp-content/uploads/sites/{$blogId}/" : 'wp-content/uploads/';
        } else {
            // old blog structure
            $path = $blogId > 1 ? "wp-content/blogs.dir/{$blogId}/files/" : 'wp-content/uploads/';
        }

        return $path;
    }

    /**
     * Get path to staging site image path wp-content/uploads
     * @return string
     */
    private function getImagePathStaging()
    {
        return 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    }

    protected function logDebug($message)
    {
        if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
            error_log($message);
        }
    }
}

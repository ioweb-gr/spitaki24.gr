<?php

namespace WPStaging\Pro\Backup\Service\Database\Exporter;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Adapter\Database\InterfaceDatabaseClient;
use WPStaging\Framework\Filesystem\File;

abstract class AbstractExporter
{
    protected $client;

    protected $database;

    protected $originalPrefix;

        protected $originalPrefixLength;

    protected $file;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->client = $database->getClient();
        $this->originalPrefix = $this->getWpDb()->prefix;
        $this->originalPrefixLength = strlen($this->originalPrefix);
    }

    protected function getWpDb()
    {
        return $this->database->getWpdba()->getClient();
    }

    public function setFileName($filename)
    {
        $this->file = new File($filename, File::MODE_APPEND);
    }

    protected function getPrefixedTableName($tableName)
    {
        return $this->replacePrefix($tableName, '{WPSTG_TMP_PREFIX}');
    }

    protected function replacePrefix($prefixedString, $newPrefix)
    {
        return $newPrefix . substr($prefixedString, $this->originalPrefixLength);
    }

    protected function replacePrefixByReference(&$prefixedString, $newPrefix)
    {
        $prefixedString = $newPrefix . substr($prefixedString, $this->originalPrefixLength);
    }
}

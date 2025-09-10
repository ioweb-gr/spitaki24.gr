<?php

namespace WPStaging\Pro\Backup\Dto\Job;

use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Pro\Backup\Dto\Traits\IsExportingTrait;

class JobExportDataDto extends JobDataDto
{
    use IsExportingTrait;

    /** @var string|null */
    private $name;

    /** @var array */
    private $excludedDirectories = [];

    /** @var bool */
    private $isAutomatedBackup = false;

    /** @var int */
    private $totalDirectories;

    /** @var int The number of files in the backup index */
    private $totalFiles;

    /** @var int The number of files the FilesystemScanner discovered */
    private $discoveredFiles;

    /** @var string */
    private $databaseFile;

    /**
     * @var int If a file couldn't be processed in a single request,
     *          this property holds how many bytes were written thus far
     *          so that the export can start writing from this byte onwards.
     */
    private $fileBeingExportedWrittenBytes;

    /** @var int */
    private $totalRowsExported;

    /** @var int */
    private $tableRowsOffset = 0;

    /** @var int */
    private $totalRowsOfTableBeingExported = 0;

    /** @var array */
    private $tablesToExport = [];

    /** @var int The size in bytes of the database in this backup */
    private $databaseFileSize = 0;

    /** @var int The size in bytes of the filesystem in this backup */
    private $filesystemSize = 0;

    /** @var int The number of requests that the Discovering Files task has executed so far */
    private $discoveringFilesRequests = 0;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Hydrated dynamically.
     *
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array|null
     */
    public function getExcludedDirectories()
    {
        return (array)$this->excludedDirectories;
    }

    public function setExcludedDirectories(array $excludedDirectories = [])
    {
        $this->excludedDirectories = $excludedDirectories;
    }

    /**
     * @return bool
     */
    public function getIsAutomatedBackup()
    {
        return (bool)$this->isAutomatedBackup;
    }

    /**
     * Hydrated dynamically.
     *
     * @param bool $isAutomatedBackup
     */
    public function setIsAutomatedBackup($isAutomatedBackup)
    {
        $this->isAutomatedBackup = $isAutomatedBackup;
    }

    /**
     * @return int
     */
    public function getTotalDirectories()
    {
        return $this->totalDirectories;
    }

    /**
     * @param int $totalDirectories
     */
    public function setTotalDirectories($totalDirectories)
    {
        $this->totalDirectories = $totalDirectories;
    }

    /**
     * @return int
     */
    public function getTotalFiles()
    {
        return $this->totalFiles;
    }

    /**
     * @param int $totalFiles
     */
    public function setTotalFiles($totalFiles)
    {
        $this->totalFiles = $totalFiles;
    }

    /**
     * @return int
     */
    public function getDiscoveredFiles()
    {
        return $this->discoveredFiles;
    }

    /**
     * @param int $discoveredFiles
     */
    public function setDiscoveredFiles($discoveredFiles)
    {
        $this->discoveredFiles = $discoveredFiles;
    }

    /**
     * @return string
     */
    public function getDatabaseFile()
    {
        return $this->databaseFile;
    }

    /**
     * @param string $databaseFile
     */
    public function setDatabaseFile($databaseFile)
    {
        $this->databaseFile = $databaseFile;
    }

    /**
     * @return int
     */
    public function getTableRowsOffset()
    {
        return (int)$this->tableRowsOffset;
    }

    /**
     * @param int $tableRowsOffset
     */
    public function setTableRowsOffset($tableRowsOffset)
    {
        $this->tableRowsOffset = (int)$tableRowsOffset;
    }

    /**
     * @return int
     */
    public function getTotalRowsExported()
    {
        return (int)$this->totalRowsExported;
    }

    /**
     * @param int $totalRowsExported
     */
    public function setTotalRowsExported($totalRowsExported)
    {
        $this->totalRowsExported = (int)$totalRowsExported;
    }

    /**
     * @return int
     */
    public function getFileBeingExportedWrittenBytes()
    {
        return (int)$this->fileBeingExportedWrittenBytes;
    }

    /**
     * @param int $fileBeingExportedWrittenBytes
     */
    public function setFileBeingExportedWrittenBytes($fileBeingExportedWrittenBytes)
    {
        $this->fileBeingExportedWrittenBytes = (int)$fileBeingExportedWrittenBytes;
    }

    /**
     * @return array
     */
    public function getTablesToExport()
    {
        return (array)$this->tablesToExport;
    }

    /**
     * @param array $tablesToExport
     */
    public function setTablesToExport($tablesToExport)
    {
        $this->tablesToExport = (array)$tablesToExport;
    }

    /**
     * @return int
     */
    public function getTotalRowsOfTableBeingExported()
    {
        return (int)$this->totalRowsOfTableBeingExported;
    }

    /**
     * @param int $totalRowsOfTableBeingExported
     */
    public function setTotalRowsOfTableBeingExported($totalRowsOfTableBeingExported)
    {
        $this->totalRowsOfTableBeingExported = (int)$totalRowsOfTableBeingExported;
    }

    /**
     * @return int
     */
    public function getDatabaseFileSize()
    {
        return $this->databaseFileSize;
    }

    /**
     * @param int $databaseFileSize
     */
    public function setDatabaseFileSize($databaseFileSize)
    {
        $this->databaseFileSize = $databaseFileSize;
    }

    /**
     * @return int
     */
    public function getFilesystemSize()
    {
        return $this->filesystemSize;
    }

    /**
     * @param int $filesystemSize
     */
    public function setFilesystemSize($filesystemSize)
    {
        $this->filesystemSize = $filesystemSize;
    }

    /**
     * @return int
     */
    public function getDiscoveringFilesRequests()
    {
        return $this->discoveringFilesRequests;
    }

    /**
     * @param int $discoveringFilesRequests
     */
    public function setDiscoveringFilesRequests($discoveringFilesRequests)
    {
        $this->discoveringFilesRequests = $discoveringFilesRequests;
    }
}

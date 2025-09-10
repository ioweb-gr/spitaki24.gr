<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

use WPStaging\Framework\Database\SearchReplace;
use WPStaging\Pro\Backup\Dto\Job\JobImportDataDto;

class DatabaseSearchReplacer
{
    protected $search  = [];

    protected $replace = [];

    protected $sourceSiteUrl;

    protected $sourceHomeUrl;

    protected $sourceSiteHostname;

    protected $sourceHomeHostname;

    protected $destinationSiteUrl;

    protected $destinationHomeUrl;

    protected $destinationSiteHostname;

    protected $destinationHomeHostname;

    protected $matchingScheme;

    public function getSearchAndReplace(JobImportDataDto $jobDataDto, $destinationSiteUrl, $destinationHomeUrl, $absPath = ABSPATH)
    {
        $this->sourceSiteUrl = untrailingslashit($jobDataDto->getBackupMetadata()->getSiteUrl());
        $this->sourceHomeUrl = untrailingslashit($jobDataDto->getBackupMetadata()->getHomeUrl());

        $this->sourceSiteHostname = untrailingslashit($this->buildHostname($this->sourceSiteUrl));
        $this->sourceHomeHostname = untrailingslashit($this->buildHostname($this->sourceHomeUrl));

        $this->destinationSiteUrl = untrailingslashit($destinationSiteUrl);
        $this->destinationHomeUrl = untrailingslashit($destinationHomeUrl);

        $this->destinationSiteHostname = untrailingslashit($this->buildHostname($this->destinationSiteUrl));
        $this->destinationHomeHostname = untrailingslashit($this->buildHostname($this->destinationHomeUrl));

        $this->matchingScheme = parse_url($this->sourceSiteUrl, PHP_URL_SCHEME) === parse_url($this->destinationSiteUrl, PHP_URL_SCHEME);

        if ($this->matchingScheme) {
            $this->replaceGenericScheme();
        } else {
            $this->replaceMultipleSchemes();
            $this->replaceGenericScheme();
        }

                array_push(
                    $this->search,
                    $jobDataDto->getBackupMetadata()->getAbsPath(),
                    addcslashes($jobDataDto->getBackupMetadata()->getAbsPath(), '/'),
                    urlencode($jobDataDto->getBackupMetadata()->getAbsPath())
                );

        array_push(
            $this->replace,
            $absPath,
            addcslashes($absPath, '/'),
            urlencode($absPath)
        );

        if (urlencode($jobDataDto->getBackupMetadata()->getAbsPath()) !== rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath())) {
            array_push(
                $this->search,
                rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath())
            );
            array_push(
                $this->replace,
                rawurlencode($absPath)
            );
        }

        if (wp_normalize_path($jobDataDto->getBackupMetadata()->getAbsPath()) !== $jobDataDto->getBackupMetadata()->getAbsPath()) {
            array_push(
                $this->search,
                wp_normalize_path($jobDataDto->getBackupMetadata()->getAbsPath()),
                wp_normalize_path(addcslashes($jobDataDto->getBackupMetadata()->getAbsPath(), '/')),
                wp_normalize_path(urlencode($jobDataDto->getBackupMetadata()->getAbsPath()))
            );

            array_push(
                $this->replace,
                wp_normalize_path($absPath),
                wp_normalize_path(addcslashes($absPath, '/')),
                wp_normalize_path(urlencode($absPath))
            );

            if (
                wp_normalize_path(urlencode($jobDataDto->getBackupMetadata()->getAbsPath())) !==
                wp_normalize_path(rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath()))
            ) {
                array_push(
                    $this->search,
                    wp_normalize_path(rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath()))
                );
                array_push(
                    $this->replace,
                    wp_normalize_path(rawurlencode($absPath))
                );
            }
        }

        foreach ($this->search as $k => $searchItem) {
            if ($this->replace[$k] === $searchItem) {
                unset($this->search[$k]);
                unset($this->replace[$k]);
            }
        }

                $this->search = array_values($this->search);
        $this->replace = array_values($this->replace);

        uasort($this->search, function ($item, $car) {
            return strlen($item) < strlen($car);
        });

        uasort($this->replace, function ($item, $car) {
            return strlen($item) < strlen($car);
        });

        $orderedSearch = [];
        $orderedReplace = [];

        $i = 0;
        foreach ($this->search as $k => $search) {
            $orderedSearch[$i] = $search;
            $orderedReplace[$i] = $this->replace[$k];
            $i++;
        }

        return (new SearchReplace())
            ->setSearch($orderedSearch)
            ->setReplace($orderedReplace);
    }

    public function buildHostname($url)
    {
        $parsedUrl = parse_url($url);

        if (!is_array($parsedUrl) || !array_key_exists('host', $parsedUrl)) {
            throw new \UnexpectedValueException("Bad URL format, cannot proceed.");
        }

                $hostname = $parsedUrl['host'];

        if (array_key_exists('path', $parsedUrl)) {
            $hostname = trailingslashit($hostname) . trim($parsedUrl['path'], '/');
        }

        return $hostname;
    }

    protected function replaceGenericScheme()
    {
        $sourceSiteHostnameGenericProtocol = '//' . $this->sourceSiteHostname;
        $destinationSiteHostnameGenericProtocol = '//' . $this->destinationSiteHostname;

        array_push(
            $this->search,
            $sourceSiteHostnameGenericProtocol,
            addcslashes($sourceSiteHostnameGenericProtocol, '/'),
            urlencode($sourceSiteHostnameGenericProtocol)
        );

        array_push(
            $this->replace,
            $destinationSiteHostnameGenericProtocol,
            addcslashes($destinationSiteHostnameGenericProtocol, '/'),
            urlencode($destinationSiteHostnameGenericProtocol)
        );

        if ($this->sourceSiteHostname !== $this->sourceHomeHostname) {
            $sourceHomeHostnameGenericProtocol = '//' . $this->sourceHomeHostname;
            $destinationHomeHostnameGenericProtocol = '//' . $this->destinationHomeHostname;

            array_push(
                $this->search,
                $sourceHomeHostnameGenericProtocol,
                addcslashes($sourceHomeHostnameGenericProtocol, '/'),
                urlencode($sourceHomeHostnameGenericProtocol)
            );

            array_push(
                $this->replace,
                $destinationHomeHostnameGenericProtocol,
                addcslashes($destinationHomeHostnameGenericProtocol, '/'),
                urlencode($destinationHomeHostnameGenericProtocol)
            );
        }
    }

    protected function replaceMultipleSchemes()
    {
        array_push(
            $this->search,
            'https://' . $this->sourceSiteHostname,
            'http://' . $this->sourceSiteHostname,
            addcslashes('https://' . $this->sourceSiteHostname, '/'),
            addcslashes('http://' . $this->sourceSiteHostname, '/'),
            urlencode('https://' . $this->sourceSiteHostname),
            urlencode('http://' . $this->sourceSiteHostname)
        );

        array_push(
            $this->replace,
            $this->destinationSiteUrl,
            $this->destinationSiteUrl,
            addcslashes($this->destinationSiteUrl, '/'),
            addcslashes($this->destinationSiteUrl, '/'),
            urlencode($this->destinationSiteUrl),
            urlencode($this->destinationSiteUrl)
        );

        if ($this->sourceSiteHostname !== $this->sourceHomeHostname) {
            array_push(
                $this->search,
                'https://' . $this->sourceHomeHostname,
                'http://' . $this->sourceHomeHostname,
                addcslashes('https://' . $this->sourceHomeHostname, '/'),
                addcslashes('http://' . $this->sourceHomeHostname, '/'),
                urlencode('https://' . $this->sourceHomeHostname),
                urlencode('http://' . $this->sourceHomeHostname)
            );

            array_push(
                $this->replace,
                $this->destinationHomeUrl,
                $this->destinationHomeUrl,
                addcslashes($this->destinationHomeUrl, '/'),
                addcslashes($this->destinationHomeUrl, '/'),
                urlencode($this->destinationHomeUrl),
                urlencode($this->destinationHomeUrl)
            );
        }
    }
}

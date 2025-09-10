<?php

namespace WPStaging\Pro;

use WPStaging\Pro\WpCli\WpCliServiceProvider;
use WPStaging\Framework\DI\Container;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Backup\BackupServiceProvider;
use WPStaging\Pro\License\LicenseServiceProvider;
use WPStaging\Pro\Staging\StagingSiteServiceProvider;
use WPStaging\Pro\Template\TemplateServiceProvider;

class ProServiceProvider extends ServiceProvider
{
    /** @var Container $container */
    protected $container;

    protected function registerClasses()
    {
        $this->container->register(TemplateServiceProvider::class);
        $this->container->register(LicenseServiceProvider::class);

        if ($this->container->make(SiteInfo::class)->isStaging()) {
            $this->container->register(StagingSiteServiceProvider::class);
        }

        // Feature providers.
        $this->container->register(WpCliServiceProvider::class);
        $this->container->register(BackupServiceProvider::class);
    }
}

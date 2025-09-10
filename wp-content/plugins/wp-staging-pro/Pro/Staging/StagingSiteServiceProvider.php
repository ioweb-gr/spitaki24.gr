<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Framework\DI\ServiceProvider;

class StagingSiteServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->singleton(SettingsTabs::class);
    }

    protected function addHooks()
    {
        add_filter('site_transient_update_plugins', $this->container->callback(PluginUpdates::class, 'disablePluginUpdateChecksOnStagingSite'), 10, 1);
        add_filter('wpstg_main_settings_tabs', $this->container->callback(SettingsTabs::class, 'addMailSettingsTabOnStagingSite'), 10, 1);
        add_action("wp_ajax_wpstg_update_staging_mail_settings", $this->container->callback(SettingsTabs::class, 'ajaxUpdateStagingMailSettings'));
    }
}

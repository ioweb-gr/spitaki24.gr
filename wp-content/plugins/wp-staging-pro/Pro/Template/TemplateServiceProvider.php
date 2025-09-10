<?php

namespace WPStaging\Pro\Template;

use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\TemplateEngine\TemplateEngine;

class TemplateServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->singleton(TemplateEngine::class);
        $this->container->singleton(ProTemplateIncluder::class);
    }

    protected function addHooks()
    {
        add_action('wpstg.views.single_overview.after_existing_clones_actions', $this->container->callback(ProTemplateIncluder::class, 'addPushButton'), 10, 3);
        add_action('wpstg.views.single_overview.after_existing_clones_actions', $this->container->callback(ProTemplateIncluder::class, 'addEditCloneLink'), 10, 3);
    }
}

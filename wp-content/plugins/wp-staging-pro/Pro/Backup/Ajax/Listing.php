<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Backend\Pro\Licensing\Licensing;
use WPStaging\Framework\SiteInfo;

class Listing extends AbstractTemplateComponent
{

    /** @var Directory */
    private $directory;

    public function __construct(Directory $directory, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->directory = $directory;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        if (!WPStaging::isPro()) { // This check will never be called because this file is available only in PRO version
            $result = $this->templateEngine->render('Backend/views/backup/free-version.php');
        } elseif (is_multisite()) {
            $result = $this->templateEngine->render('Backend/views/backup/multisite-disabled.php');
        } else {
            $directories = [
                'uploads' => $this->directory->getUploadsDirectory(),
                'themes' => trailingslashit(get_theme_root()),
                'plugins' => trailingslashit(WP_PLUGIN_DIR),
                'muPlugins' => trailingslashit(WPMU_PLUGIN_DIR),
                'wpContent' => trailingslashit(WP_CONTENT_DIR),
                'wpStaging' => $this->directory->getPluginUploadsDirectory(),
            ];

            $result = $this->templateEngine->render(
                'Backend/views/backup/listing.php',
                [
                    'directory' => $this->directory,
                    'directories' => $directories,
                    'urlAssets' => trailingslashit(WPSTG_PLUGIN_URL) . 'assets/',
                    'isValidLicense' => (new SiteInfo())->isStaging() || (new Licensing())->isValidOrExpiredLicenseKey(),
                ]
            );
        }

        wp_send_json($result);
    }
}

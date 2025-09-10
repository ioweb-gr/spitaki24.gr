<?php

namespace WPStaging\Backend\Pro\Notices;

use WPStaging\Backend\Notices\Notices as FreeNotices;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\SiteInfo;

/*
 *  Admin Notices | Warnings | Messages
 */

// No Direct Access
if (!defined("WPINC")) {
    die;
}

/**
 * Class Notices
 * @package WPStaging\Backend\Pro\Notices
 */
class Notices
{
    /**
     * @var FreeNotices
     */
    private $notices;

    /**
     * @var object
     */
    private $license;

    /** @var bool */
    private $showAllNotices;


    /**
     * Notices constructor.
     * @param $notices FreeNotices Notices class
     */
    public function __construct($notices)
    {
        $this->notices = $notices;
        // local var $notices is necessary because we need to support php 5.6: $this->notices::SHOW_ALL_NOTICES throws an error in < php 7.x
        $notices = $this->notices;
        $this->showAllNotices = $notices::SHOW_ALL_NOTICES;
        $this->license = get_option('wpstg_license_status');
    }

    public function getNotices()
    {
        // Don't show on staging sites but on all pages to all users
        if (!(new SiteInfo())->isStaging()) {
            $this->getLicenseKeyInvalidNotice();
        }

        $this->backupsDifferentPrefixNotice();

        // Show only on WP STAGING admin pages and to administrators
        if ($this->showAllNotices || (current_user_can("update_plugins") && $this->notices->isAdminPage())) {
            $this->getLicenseKeyExpiredNotice();
            $this->getWPVersionCompatibleNotice();
        }
    }

    /**
     * Show notice if backup is created on version 4.0.2 or lower
     */
    public function backupsDifferentPrefixNotice()
    {
        /** @var BackupsDifferentPrefixNotice */
        $backupsPrefixNotice = WPStaging::make(BackupsDifferentPrefixNotice::class);
        if ($this->showAllNotices || $backupsPrefixNotice->isEnabled()) {
            require WPSTG_PLUGIN_DIR . "Backend/Pro/views/notices/backups-different-prefix.php";
        }
    }

    /**
     * Show license key invalid notice on all admin pages to all users
     */
    public function getLicenseKeyInvalidNotice()
    {
        // Customer never used any valid license key at all. A valid (expired) license key is needed to make use of all wp staging pro features
        // So show this admin notice on all pages to make sure customer is aware that license key must be entered
        if (!$this->showAllNotices && get_site_transient('wpstgDisableLicenseNotice')) {
            // When activating the plugin for the first time, do not show the license notice.
            // Instead, we show a friendly notice telling the user to enter the license.
            delete_site_transient('wpstgDisableLicenseNotice');
        } else {
            if ($this->showAllNotices || ((isset($this->license->error) && $this->license->error !== 'expired') || $this->license === false)) {
                require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/license-key-invalid.php';
            }
        }
    }


    /**
     * Show warning if license key is expired on WP STAGING admin pages only
     */
    public function getLicenseKeyExpiredNotice()
    {
        if ($this->showAllNotices || (isset($this->license->error) && $this->license->error === 'expired') || (isset($this->license->license) && $this->license->license === 'expired')) {
            $licensekey = get_option('wpstg_license_key', '');
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/license-key-expired.php';
        }
    }

    /**
     * Show warning if WordPress version is not supported
     */
    private function getWPVersionCompatibleNotice()
    {
        if ($this->showAllNotices || version_compare(WPStaging::getInstance()->get('WPSTG_COMPATIBLE'), get_bloginfo("version"), "<")) {
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/wp-version-compatible-message.php';
        }
    }
}

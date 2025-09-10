<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Staging\FirstRun;
use WPStaging\Framework\Staging\CloneOptions;

class SettingsTabs
{
    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var CloneOptions
     */
    protected $cloneOptions;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth, CloneOptions $cloneOptions)
    {
        $this->auth = $auth;
        $this->cloneOptions = $cloneOptions;
    }

    /**
     * Add mail settings tab for WP STAGING Pro on the staging site
     *
     * @filter wpstg_main_settings_tabs
     */
    public function addMailSettingsTabOnStagingSite($tabs)
    {
        $tabs['mail-settings'] = __("Mail Settings", "wp-staging");

        return $tabs;
    }

    /**
     * Update mail settings on staging site through ajax
     *
     * @action wp_ajax_wpstg_update_staging_mail_settings
     */
    public function ajaxUpdateStagingMailSettings()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json([
                "success" => false,
                "message" => __('Access Denied! Refresh page and try again.', 'wp-staging'),
            ]);

            return;
        }

        // Inverse logic because our key in the database is named `wpstg_emails_disabled`
        $mailsDisabled = !(isset($_POST['emailsAllowed']) && $_POST['emailsAllowed'] !== "false");

        $existingMailSetting = filter_var($this->cloneOptions->get(FirstRun::MAILS_DISABLED_KEY), FILTER_VALIDATE_BOOLEAN);

        // nothing to update
        if ($existingMailSetting === $mailsDisabled) {
            wp_send_json([
                "success" => true,
                "message" => __('Settings saved.', 'wp-staging'),
            ]);

            return;
        }

        $success = $this->cloneOptions->set(FirstRun::MAILS_DISABLED_KEY, $mailsDisabled);

        $message = __('Failed to update! Try again.', 'wp-staging');

        if ($success) {
            // delete old mail option if request was successful
            delete_option(FirstRun::MAILS_DISABLED_KEY);
        }

        if ($success && $mailsDisabled) {
            $message = __('Emails sending disabled!', 'wp-staging');
        }

        if ($success && !$mailsDisabled) {
            $message = __('Emails sending enabled!', 'wp-staging');
        }

        wp_send_json([
            'hideNotice' => $existingMailSetting,
            "success" => $success,
            "message" => $message,
        ]);
    }
}

<?php

namespace WPStaging\Pro\Template;

class ProTemplateIncluder
{
    /** @var string */
    private $backEndViewsFolder;

    public function __construct()
    {
        $this->backEndViewsFolder = trailingslashit(WPSTG_PLUGIN_DIR) . 'Backend/Pro/views/';
    }

    /**
     * Add the "Push" button to the template
     */
    public function addPushButton($cloneID, $data, $license)
    {
        include $this->backEndViewsFolder . 'clone/ajax/push-button.php';
    }

    /**
     * Add the "Edit this Clone" link to the template
     */
    public function addEditCloneLink($cloneID, $data, $license)
    {
        include $this->backEndViewsFolder . 'clone/ajax/edit-clone.php';
    }
}

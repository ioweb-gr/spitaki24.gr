<?php

namespace WPStaging\Backend\Pro\Modules\Filters;

use WPStaging\Backend\Pro\Modules\Jobs\Copiers\Copier;
use WPStaging\Framework\Filesystem\Filters\RecursivePathExcludeFilter;

/**
 * Class RecursiveFilterExclude
 *
 * @todo Remove this and \WPStaging\Core\Iterators\RecursiveFilterExclude altogether,
 * When new GlobPatternExcludeFilter is added
 *
 * @package WPStaging\Backend\Pro\Modules\Filters
 */
class RecursiveFilterExclude extends RecursivePathExcludeFilter
{
    public function accept()
    {
        $result = parent::accept();
        if (!$result) {
            return false;
        }

        // Exclude tmp and backup plugins like 'plugins/wpstg-tmp-woocommerce' and 'plugins/wpstg-bak-woocommerce'
        $pattern = sprintf('#^(%s|%s)+#', Copier::PREFIX_TEMP, Copier::PREFIX_BACKUP);
        if (preg_match($pattern, $this->getInnerIterator()->getSubPathname())) {
            return false;
        }

        return true;
    }
}

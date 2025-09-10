<?php
if( ! class_exists('BeRocket_AAPF_compat_rank_math_seo_paid') ) {
    class BeRocket_AAPF_compat_rank_math_seo_paid {
        function __construct() {
            add_filter('rank_math/frontend/canonical', array($this, 'add_canonical'));
        }
        function add_canonical($canonical) {
            $BeRocket_AAPF_paid = BeRocket_AAPF_paid::getInstance();
            if( $BeRocket_AAPF_paid->is_canonical_applied() ) {
                $canonical = $BeRocket_AAPF_paid->get_current_canonical_url();
            }
            return $canonical;
        }
    }
    new BeRocket_AAPF_compat_rank_math_seo_paid();
}

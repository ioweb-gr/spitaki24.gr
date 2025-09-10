<?php
class BeRocket_aapf_custom_search_filters {
    function __construct() {
        add_filter('brfr_data_ajax_filters', array($this, 'settings_page'), 1200);
        add_filter('BeRocket_AAPF_search_field_s_apply', array($this, 's_apply'), 1000, 3);
        add_filter('bapf_custom_search_addon_data', array($this, 'addon_data'));
        add_filter('brfr_get_option_ajax_filters', array($this, 'defaults_value'));
    }
    public function settings_page($data) {
        $data['Advanced'] = berocket_insert_to_array(
            $data['Advanced'],
            'header_part_fixes',
            array(
                'header_part_custom_search' => array(
                    'section' => 'header_part',
                    "value"   => __('Custom Search Add-on', 'BeRocket_AJAX_domain'),
                    "extra"   => "id='custom_search_by'",
                ),
                'custom_search_by' => array(
                    "label"     => __( 'Search By', "BeRocket_AJAX_domain" ),
                    "tr_class"  => "berocket_custom_search_by",
                    "items" => array(
                        "custom_search_by_title" => array(
                            "type"      => "checkbox",
                            "name"      => 'custom_search_by_title',
                            "value"     => '1',
                            'label_for' => __('Title', 'BeRocket_AJAX_domain'),
                        ),
                        'custom_search_by_description' => array(
                            "type"      => "checkbox",
                            "name"      => "custom_search_by_description",
                            "value"     => '1',
                            'label_for' => __('Description', 'BeRocket_AJAX_domain'),
                        ),
                        'custom_search_by_excerpt' => array(
                            "type"      => "checkbox",
                            "name"      => "custom_search_by_excerpt",
                            "value"     => '1',
                            'label_for' => __('Excerpt', 'BeRocket_AJAX_domain'),
                        ),
                        'custom_search_by_sku' => array(
                            "type"      => "checkbox",
                            "name"      => "custom_search_by_sku",
                            "value"     => '1',
                            'label_for' => __('SKU', 'BeRocket_AJAX_domain'),
                        ),
                    ),
                ),
            ),
            true
        );
        $search_types = array('berocket' => array('value' => '', 'text' => 'BeRocket'));
        if( function_exists('relevanssi_do_query') ) {
            $search_types['relevanssi'] = array('value' => 'relevanssi', 'text' => 'Relevanssi');
        }
        if( count($search_types) > 1 ) {
            $data['Advanced'] = berocket_insert_to_array(
                $data['Advanced'],
                'header_part_custom_search',
                array(
                    'custom_search_type' => array(
                        "label"    => __( 'Search Type', "BeRocket_AJAX_domain" ),
                        "name"     => "custom_search_type",
                        "class"    => "berocket_custom_search_by_type",
                        "type"     => "selectbox",
                        "options"  => $search_types,
                        "value"    => '',
                    ),
                )
            );
        }
        add_action('admin_footer', array($this, 'admin_script'));
        return $data;
    }
    function admin_script() {
        ?>
        <script>
        if( jQuery('.berocket_custom_search_by_type').length ) {
            function berocket_custom_search_by() {
                if( ! jQuery('.berocket_custom_search_by_type').val() ) {
                    jQuery('.berocket_custom_search_by').show();
                } else {
                    jQuery('.berocket_custom_search_by').hide();
                }
            }
            berocket_custom_search_by();
            jQuery(document).on('change', '.berocket_custom_search_by_type', berocket_custom_search_by);
        }
        </script>
        <?php
    }
    function s_apply($result, $args, $search) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( ! function_exists('relevanssi_do_query') || empty($options['custom_search_type']) ) {
            $result = $this->berocket_search($args, $search);
        } elseif( $options['custom_search_type'] == 'relevanssi') {
            $result = $this->relevanssi_search($args, $search);
        }
        return $result;
    }
    function berocket_search($args, $search) {
        global $wpdb;
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $query = array(
            'select' => "SELECT ID FROM {$wpdb->posts}",
            'join'   => "",
            'where'  => array()
        );
        if( ! empty($options['custom_search_by_title'])
            || ( empty($options['custom_search_by_description']) && empty($options['custom_search_by_excerpt']) && empty($options['custom_search_by_sku']) )
        ) {
            $query['where'][] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", "%{$search}%");
        }
        if( ! empty($options['custom_search_by_description']) ) {
            $query['where'][] = $wpdb->prepare("{$wpdb->posts}.post_content LIKE %s", "%{$search}%");
        }
        if( ! empty($options['custom_search_by_excerpt']) ) {
            $query['where'][] = $wpdb->prepare("{$wpdb->posts}.post_excerpt LIKE %s", "%{$search}%");
        }
        if( ! empty($options['custom_search_by_sku']) ) {
            $query['join'] .= "LEFT JOIN {$wpdb->postmeta} as br_sku on {$wpdb->posts}.ID = br_sku.post_id AND br_sku.meta_key = '_sku'";
            $query['where'][] = $wpdb->prepare("br_sku.meta_value LIKE %s", "%{$search}%");
        }
        $query['where'] = implode(' OR ', $query['where']);
        $query_string = $query['select'] . ' ' . $query['join'];
        if( ! empty($query['where']) ) {
            $query_string .= ' WHERE ' . $query['where'];
            $posts = $wpdb->get_col($query_string);
        }
        if( empty($posts) || ! is_array($posts) || count($posts) == 0 ) {
            $posts = array(0);
        } elseif( ! empty($args['post__in']) && is_array($args['post__in']) && count($args['post__in']) > 0 ) {
            $posts = array_intersect($args['post__in'], $posts);
        }
        $args['post__in'] = $posts;
        return $args;
    }
    function relevanssi_search($args, $search) {
        $new_args  = $args;
        $new_args['s'] = $search;
        $new_args['nopaging'] = true;
        $new_args['fields'] = 'ids';
        global $wpdb;
        $queryrelevanssi = new WP_Query();
        $queryrelevanssi->parse_query( $new_args );
        $queryrelevanssi = apply_filters( 'relevanssi_modify_wp_query', $queryrelevanssi );

        $posts = relevanssi_do_query( $queryrelevanssi );
        if( empty($posts) || count($posts) == 0 ) {
            $posts = array(0);
        }
        if( ! empty($args['post__in']) && is_array($args['post__in']) && count($args['post__in']) > 0 ) {
            $posts = array_intersect($args['post__in'], $posts);
        }
        $args['post__in'] = $posts;
        return $args;
    }
    function addon_data($data) {
        $data['tooltip'] .= '<br><a href="'.admin_url('admin.php?page=br-product-filters&tab=advanced#custom_search_by').'">'.__('Add-on options', 'BeRocket_AJAX_domain').'</a>';
        return $data;
    }
    function defaults_value($options) {
        if( empty($options['custom_search_by_title']) && empty($options['custom_search_by_description']) && empty($options['custom_search_by_excerpt']) && empty($options['custom_search_by_sku']) ) {
            $options['custom_search_by_title']       = '1';
            $options['custom_search_by_description'] = '1';
            $options['custom_search_by_excerpt']     = '1';
        }
        return $options;
    }
}
new BeRocket_aapf_custom_search_filters();
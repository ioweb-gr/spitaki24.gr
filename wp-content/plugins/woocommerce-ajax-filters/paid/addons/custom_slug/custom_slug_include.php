<?php
class BeRocket_aapf_custom_slug_include_addon {
    public $posts = array();
    function __construct() {
        add_filter('edit_form_before_permalink', array($this, 'display_slug_edit'), 10000, 1);
        add_filter('berocket_custom_post_br_product_filter_wc_save_product_without_check_after', array($this, 'save_permalink'), 10000, 2);
        add_filter('berocket_query_var_title_before_widget', array($this, 'berocket_query_var_title'), 100000, 3);
        add_filter('berocket_query_var_title_before_element', array($this, 'berocket_query_var_title_element'), 100000, 2);
        add_filter('BeRocket_AAPF_template_full_content', array($this, 'change_taxonomy'), 100000, 3);
        add_filter('bapf_uparse_func_check_attribute_name', array($this, 'check_attribute_name'), 100000, 3);
        add_filter('berocket_aapf_get_terms_additional', array($this, 'add_filter_identifier'), 100000, 3);
        add_filter('bapf_addon_custom_slug_change_taxonomy', array($this, 'disable_for_orderby'), 10, 4);
        add_filter('bapf_faster_recount_remove_taxonomy_data', array($this, 'modify_faster_recount_data'), 10, 2);
        add_filter('bapf_uparse_remove_taxonomy_each', array($this, 'remove_taxonomy_each'), 10, 4);
        add_filter('bapf_uparse_generate_filter_link_each_taxonomy_name', array($this, 'generate_filter_link_each_taxonomy_name'), 10, 4);
        add_filter('bapf_uparse_modify_data_each_is_modify', array($this, 'modify_data_each_is_modify'), 10, 4);
        add_filter('bapf_uparse_modify_data_add_value', array($this, 'modify_data_add_value'), 10, 2);
        add_filter('br_is_term_selected_checked_each', array($this, 'br_is_term_selected_checked_each'), 10, 8);
        add_filter('br_get_selected_term_checked_each', array($this, 'br_get_selected_term_checked_each'), 10, 4);
    }
    function display_slug_edit($post) {
        echo $this->get_sample_permalink_html($post->ID);
    }
    function berocket_query_var_title($set_query_var_title, $type, $instance) {
        if( ! empty($instance['filter_id']) ) {
            $filter_id = $instance['filter_id'];
            $filter_post = get_post($filter_id);
            $set_query_var_title['custom_slug'] = urldecode($filter_post->post_name);
        }
        return $set_query_var_title;
    }
    function berocket_query_var_title_element($set_query_var_title, $advanced) {
        $instance = $advanced['options'];
        if( ! empty($instance['filter_id']) ) {
            $filter_id = $instance['filter_id'];
            $filter_post = get_post($filter_id);
            $set_query_var_title['custom_slug'] = urldecode($filter_post->post_name);
        }
        return $set_query_var_title;
    }
    function change_taxonomy($template_content, $terms, $berocket_query_var_title) {
        if( apply_filters('bapf_addon_custom_slug_change_taxonomy', ! empty($berocket_query_var_title['custom_slug']), $template_content, $terms, $berocket_query_var_title ) ) {
            $template_content['template']['attributes']['data-taxonomy'] = $berocket_query_var_title['custom_slug'];
        }
        return $template_content;
    }
    function disable_for_orderby($use_replace, $template_content, $terms, $berocket_query_var_title) {
        return $use_replace && $berocket_query_var_title['widget_type'] === 'filter';
    }
    function check_attribute_name($result, $instance, $attribute_name) {
        if( ! empty($this->posts[$attribute_name]) ) {
            $post = $this->posts[$attribute_name];
        } else {
            $post = get_page_by_path($attribute_name, OBJECT, 'br_product_filter');
            if( empty($post) ) {
                $new_attribute_name = urldecode($attribute_name);
                $post = get_page_by_path($new_attribute_name, OBJECT, 'br_product_filter');
            }
            if( ! empty($post) ) {
                $this->posts[$attribute_name] = $post;
            }
        }
        if( ! empty($post) && property_exists($post, 'ID') ) {
            $correct_name = $this->get_name_by_settings($post->ID);
            remove_filter('bapf_uparse_func_check_attribute_name', array($this, 'check_attribute_name'), 100000, 3);
            $result = $instance->func_check_attribute_name($correct_name);
            add_filter('bapf_uparse_func_check_attribute_name', array($this, 'check_attribute_name'), 100000, 3);
        }
        return $result;
    }
    function get_name_by_settings($filter_id) {
        $BeRocket_AAPF_single_filter = BeRocket_AAPF_single_filter::getInstance();
        $filter_settings = $BeRocket_AAPF_single_filter->get_option($filter_id);
        $attribute = braapf_get_data_taxonomy_from_post($filter_settings, 'all');
        if( ! empty($attribute) && is_array($attribute) ) {
            if( $attribute['type'] == 'attribute' && strpos($attribute['taxonomy'], 'pa_') === 0 ) {
                $attribute = substr($attribute['taxonomy'], 3);
            } else {
                $attribute = $attribute['taxonomy'];
            }
        }
        return $attribute;
    }
    function save_permalink($post_id, $post) {
        $name = (empty($_POST['berocket_permalink_slug']) ? '' : $_POST['berocket_permalink_slug']);
        if( ! empty($_POST['berocket_permalink_slug']) ) {
            $post->post_name = sanitize_title( $name ? $name : $post->post_title, $post->ID );
            $post->post_name = wp_unique_post_slug( $post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
            remove_action('berocket_custom_post_br_product_filter_wc_save_product_without_check_after', array($this, 'save_permalink'), 10000, 2);
            wp_update_post(
                array (
                    'ID'        => $post->ID,
                    'post_name' => $post->post_name
                ), false, false
            );
            add_action('berocket_custom_post_br_product_filter_wc_save_product_without_check_after', array($this, 'save_permalink'), 10000, 2);
        }
    }
    function get_sample_permalink_html( $id ) {
        $post = get_post( $id );
        if ( ! $post || $post->post_type != 'br_product_filter' ) {
            return '';
        }
        list($permalink, $post_name) = get_sample_permalink( $post->ID );
        if ( mb_strlen( $post_name ) > 34 ) {
            $post_name_abridged = mb_substr( $post_name, 0, 16 ) . '&hellip;' . mb_substr( $post_name, -16 );
        } else {
            $post_name_abridged = $post_name;
        }
        $return  = '<div class="berocket_permalink"><strong>' . __( 'Custom Filter Slug:', 'BeRocket_AJAX_domain' ) . "</strong> ";
        $return .= '<input name="berocket_permalink_slug" style="display:none;" value="'.esc_html($post_name_abridged).'">';
        $return .= '<span class="berocket_permalink_preview">'.esc_html($post_name_abridged).'</span> ';
        $return .= '<button type="button" class="berocket_permalink_edit" aria-label="' . __( 'Edit permalink' ) . '">' . __( 'Edit' ) . "</button>";
        $return .= '</div>';
        $return .= '<script>';
        $return .= 'jQuery(".berocket_permalink_edit").on("click", function() {
            jQuery(".berocket_permalink_preview").hide();
            jQuery(".berocket_permalink_edit").hide();
            jQuery(".berocket_permalink input").show();
        });';
        $return .= '</script>';
        return $return;
    }
    function add_filter_identifier($get_terms_advanced, $instance, $args) {
        $get_terms_advanced['filter_id'] = $instance['filter_id'];
        return $get_terms_advanced;
    }
    function modify_faster_recount_data($data, $additional_data) {
        if( ! empty($additional_data['taxonomy_data']['additional']['filter_id']) ) {
            $filter_post = get_post($additional_data['taxonomy_data']['additional']['filter_id']);
            $data['filter_slug'] = $filter_post->post_name;
            $data['filter_id'] = $additional_data['taxonomy_data']['additional']['filter_id'];
        }
        return $data;
    }
    function remove_taxonomy_each($check, $args, $data, $filter) {
        if( ! empty($args['filter_slug']) ) {
            $check = $check && $args['filter_slug'] == $filter['attr'];
        }
        return $check;
    }
    function generate_filter_link_each_taxonomy_name($taxonomy_name, $instance, $filter, $data) {
        $post = get_page_by_path($filter['attr'], OBJECT, 'br_product_filter');
        if( ! empty($post) ) {
            $taxonomy_name = $post->post_name;
        }
        return $taxonomy_name;
    }
    function modify_data_add_value($filter, $args) {
        if( ! empty($args['berocket_query_var_title']) && ! empty($args['berocket_query_var_title']['custom_slug']) ) {
            $filter['attr'] = $args['berocket_query_var_title']['custom_slug'];
        }
        return $filter;
    }
    function check_filter_additional($checked, $filter, $args) {
        if( $checked && ! empty($args['berocket_query_var_title']) && ! empty($args['berocket_query_var_title']['custom_slug']) ) {
            $checked = urldecode($filter['attr']) == urldecode($args['berocket_query_var_title']['custom_slug']);
        }
        return $checked;
    }
    function modify_data_each_is_modify($is_modify, $value, $filter, $args) {
        return $this->check_filter_additional($is_modify, $filter, $args);
    }
    function br_is_term_selected_checked_each($is_checked_correct, $term_taxonomy, $term, $checked, $child_parent, $depth, $filter, $additional) {
        return $this->check_filter_additional($is_checked_correct, $filter, $additional);
    }
    function br_get_selected_term_checked_each($is_checked_correct, $filter, $taxonomy, $additional) {
        return $this->check_filter_additional($is_checked_correct, $filter, $additional);
    }
}
new BeRocket_aapf_custom_slug_include_addon();
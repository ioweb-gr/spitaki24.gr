<?php
if( ! class_exists('BeRocket_AAPF_search_field_apply') ) {
    class BeRocket_AAPF_search_field_apply {
        function __construct() {
            add_filter('bapf_uparse_query_vars_apply_filters', array($this, 'apply_search'));
            add_filter('berocket_aapf_is_filtered_page_check', array($this, 'is_apply_search'));
            add_filter('berocket_query_var_title_before_element', array($this, 'serach_field_data'), 1, 2);
            add_filter('BeRocket_AAPF_template_full_element_content', array($this, 'search_field_suggestions'), 10000, 2);
            add_filter('BeRocket_AAPF_template_full_element_content', array($this, 'disable_autocomplete'), 1000, 2);
            add_filter('BeRocket_AAPF_template_full_element_content', array($this, 'exclude_filters'), 1200, 2);
            //SINGLE FILTER SETTINGS
            add_action('braapf_single_filter_additional', array(__CLASS__, 'auto_suggest'), 500, 2);
            add_action('braapf_single_filter_additional', array(__CLASS__, 'reset_filters'), 700, 2);
            add_action('braapf_single_filter_additional', array(__CLASS__, 'texts'), 900, 2);
        }
        function get_srch() {
            if( empty($_GET['srch']) ) {
                return false;
            }
            return stripslashes(mb_convert_encoding ($_GET['srch'], 'UTF-8'));
        }
        function apply_search($args) {
            if( ($search = $this->get_srch()) !== false ) {
                if( strpos($search, 'cat:') === 0 ) {
                    $term_use = $this->get_term_search_data($search);
                    if( $term_use !== FALSE ) {
                        $args['tax_query']['bapf_srch'] = array(
                            'taxonomy'  => $term_use['term']->taxonomy,
                            'field'     => 'term_id',
                            'terms'     => array($term_use['term']->term_id),
                            'operator'  => 'IN'
                        ); 
                    }
                } else {
                    $search = sanitize_text_field($search);
                    $result = apply_filters('BeRocket_AAPF_search_field_s_apply', null, $args, $search);
                    if( $result === null ) {
                        $args['s'] = $search;
                    } else {
                        $args = $result;
                    }
                }
            }
            return $args;
        }
        function is_apply_search($apply) {
            if(! empty($_GET['srch']) ) {
                $apply = true;
            }
            return $apply;
        }
        function serach_field_data($set_query_var_title, $additional) {
            if( br_get_value_from_array($additional, array('options', 'widget_type')) == 'search_field' ) {
                do_action('br_footer_script');
                $set_query_var_title['searchf_suggest'] = br_get_value_from_array($additional, array('options', 'searchf_suggest'));
                $set_query_var_title['searchf_suggest_method'] = br_get_value_from_array($additional, array('options', 'searchf_suggest_method'));
                $set_query_var_title['searchf_suggest_height'] = br_get_value_from_array($additional, array('options', 'searchf_suggest_height'));
            }
            return $set_query_var_title;
        }
        function disable_autocomplete($template_content, $berocket_query_var_title) {
            if( br_get_value_from_array($berocket_query_var_title, 'new_template') == 'input' ) {
                $template_content['template']['content']['filter']['content']['form']['content']['input']['attributes']['autocomplete'] = 'off';
                $template_content['template']['content']['filter']['content']['form']['content']['input']['attributes']['name'] = 'srch';
                if( ($search = $this->get_srch()) !== false ) {
                    $term_use = $this->get_term_search_data($search);
                    if( $term_use !== FALSE ) {
                        $template_content['template']['content']['filter']['content']['form']['content']['input']['attributes']['data-value'] = $term_use['value'];
                        $template_content['template']['content']['filter']['content']['form']['content']['input']['attributes']['data-name'] = $term_use['name'];
                        $template_content['template']['content']['filter']['content']['form']['content']['input']['attributes']['value'] = $term_use['name'];
                    }
                }
            }
            return $template_content;
        }
        function get_term_search_data($term) {
            $result = FALSE;
            if( is_string($term) ) {
                if( strpos($term, 'cat:') === 0 ) {
                    $term = str_replace('cat:', '', $term);
                    $term = get_term_by('slug', $term, 'product_cat');
                    if( $term !== false ) {
                        $result = array('term' => $term);
                    }
                }
            } elseif( is_a($term, 'WP_Term') ) {
                $result = array('term' => $term);
            }
            if( $result === FALSE ) {
                $result = array(
                    'value' => $term,
                    'name' => $term,
                    'suggest_name' => $term,
                );
            } else {
                $result['value'] = 'cat:'.$result['term']->slug;
                $result['name'] = sprintf(_x('Category %s', 'displayed in search input', 'BeRocket_AJAX_domain'), $result['term']->name);
                $result['suggest_name'] = sprintf(_x('Category %s', 'display in search suggestions', 'BeRocket_AJAX_domain'), $result['term']->name);
            }
            return $result;
        }
        function search_field_suggestions($template_content, $berocket_query_var_title) {
            if( br_get_value_from_array($berocket_query_var_title, 'new_template') == 'input' && ! empty($berocket_query_var_title['searchf_suggest']) ) {
                $template_content['template']['content']['filter']['content']['form']['attributes']['class']['suggest'] = 'bapf_suggest';
                if( br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_method') == 'javascript' || br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_method') == 'ajax' ) {
                    $post_content = array();
                    global $berocket_parse_page_obj;
                    global $wp_query;
                    $query_vars = $berocket_parse_page_obj->query_vars;
                    if( ($search = $this->get_srch()) !== false ) {
                        $term_search = $this->get_term_search_data($search);
                    }
                    if( br_get_value_from_array($berocket_query_var_title, 'searchf_filters_include') != 'exclude' ) {
                        $query_vars = apply_filters('bapf_uparse_apply_filters_to_query_vars', $query_vars);
                    }
                    if( isset($query_vars['bapf_apply']) ) {
                        unset($query_vars['bapf_apply']);
                    }
                    if( br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_method') == 'javascript' ) {
                        $query_vars['posts_per_page'] = 100;
                        $query_vars['s'] = "";
                        if( isset($query_vars['tax_query']) && is_array($query_vars['tax_query']) && isset($query_vars['tax_query']['bapf_srch']) ) {
                            unset($query_vars['tax_query']['bapf_srch']);
                        }
                    } else {
                        $query_vars['posts_per_page'] = 10;
                        if( ! empty($term_search) ) {
                            $result = apply_filters('BeRocket_AAPF_search_field_s_apply', null, $query_vars, $term_search['name']);
                            if( $result === null ) {
                                $query_vars['s'] = $term_search['name'];
                            } else {
                                $query_vars = $result;
                            }
                        }
                    }
                    if( strpos(br_get_value_from_array($berocket_query_var_title, 'searchf_suggest'), 'products') !== FALSE ) {
						$query_vars['paged'] = 0;
                        $query_vars['fields'] = "all";
                        $posts = get_posts($query_vars);
                        foreach($posts as $post) {
                            $post_content[$post->ID] = apply_filters('brapf_searchf_suggest_post_content', array(
                                'type'          => 'tag',
                                'tag'           => 'a',
                                'attributes'    => array(
                                    'class'         => array(
                                        'bapf_suggest_element'
                                    ),
                                    'href'      => get_post_permalink($post->ID)
                                ),
                                'content'       => array($post->post_title)
                            ), $post, $berocket_query_var_title);
                        }
                    }
                    if( strpos(br_get_value_from_array($berocket_query_var_title, 'searchf_suggest'), 'categories') !== FALSE ) {
                        remove_filter('berocket_aapf_recount_terms_query', array('BeRocket_AAPF_faster_attribute_recount', 'search_query'), 50, 3);
                        $terms = berocket_aapf_get_terms(array('taxonomy' => 'product_cat'), array('recount_tax_query' => br_get_value_from_array($query_vars, 'tax_query', array())));
                        add_filter('berocket_aapf_recount_terms_query', array('BeRocket_AAPF_faster_attribute_recount', 'search_query'), 50, 3);
                        if( is_array($terms) && (br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_method') == 'javascript' || ! empty($term_search)) ) {
                            foreach($terms as $term) {
                                $term_use = $this->get_term_search_data($term);
								if( br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_method') == 'javascript' || (! empty($term_search) && stripos($term_use['suggest_name'], $term_search['name']) !== FALSE) ) {
									$post_content['cat'.$term->term_id] = apply_filters('brapf_searchf_suggest_term_content', array(
										'type'          => 'tag',
										'tag'           => 'div',
										'attributes'    => array(
											'class'         => array(
												'bapf_suggest_element',
												'bapf_suggest_open'
											),
											'data-search'   => $term_use['value'],
											'data-name'     => $term_use['name']
										),
										'content'       => array($term_use['suggest_name'])
									), $term, $berocket_query_var_title);
								}
                            }
                        }
                    }
                    $template_content['template']['content']['filter']['content']['suggestion'] = array(
                        'type'          => 'tag',
                        'tag'           => 'div',
                        'attributes'    => array(
                            'class'         => array(
                                'bapf_input_suggestion'
                            ),
                            'style'         => array(
                                'display_none' => 'display:none;'
                            ),
                            'data-height' => br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_height')
                        ),
                        'content'       => $post_content
                    );
					if( br_get_value_from_array($berocket_query_var_title, 'searchf_suggest_method') == 'ajax' ) {
						$template_content['template']['content']['filter']['content']['suggestion']['attributes']['class']['bapf_ajax_sug'] = 'bapf_ajax_sug';
						$template_content['template']['content']['filter']['content']['suggestion']['attributes']['data-lastval'] = (empty($term_search) ? '' : esc_html($term_search['name']));
					}
                }
            }
            return $template_content;
        }
        function exclude_filters($template_content, $berocket_query_var_title) {
            if( br_get_value_from_array($berocket_query_var_title, 'new_template') == 'input' && br_get_value_from_array($berocket_query_var_title, 'searchf_filters_include') == 'exclude' ) {
                $template_content['template']['content']['filter']['content']['form']['attributes']['class']['remove_other_filters'] = 'bapf_rm_filter';
            }
            return $template_content;
        }
        //SINGLE FILTER SETTINGS
        static function auto_suggest($settings_name, $braapf_filter_settings) {
            echo '<div class="braapf_attribute_setup_flex">';
                echo '<div class="braapf_searchf_suggest braapf_half_select_full">';
                    $searchf_suggest = br_get_value_from_array($braapf_filter_settings, 'searchf_suggest', '');
                    echo '<label for="braapf_searchf_suggest">' . __('Suggestions', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<select id="braapf_searchf_suggest" name="'.$settings_name.'[searchf_suggest]">';
                        echo '<option value=""'.($searchf_suggest == "" ? ' selected' : '').'>' . __('Disabled', 'BeRocket_AJAX_domain') . '</option>';
                        echo '<option value="products"'.($searchf_suggest == "products" ? ' selected' : '').'>' . __('Products', 'BeRocket_AJAX_domain') . '</option>';
                        echo '<option value="categories"'.($searchf_suggest == "categories" ? ' selected' : '').'>' . __('Categories', 'BeRocket_AJAX_domain') . '</option>';
                        echo '<option value="products_categories"'.($searchf_suggest == "products_categories" ? ' selected' : '').'>' . __('Products and Categories', 'BeRocket_AJAX_domain') . '</option>';
                    echo '</select>';
                echo '</div>';
                echo '<div class="braapf_searchf_suggest_method braapf_half_select_full">';
                    $searchf_suggest_method = br_get_value_from_array($braapf_filter_settings, 'searchf_suggest_method', '');
                    echo '<label for="braapf_searchf_suggest_method">' . __('Suggestions Search', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<select id="braapf_searchf_suggest_method" name="'.$settings_name.'[searchf_suggest_method]">';
                        echo '<option value="javascript"'.($searchf_suggest_method == "javascript" ? ' selected' : '').'>' . __('JavaScript', 'BeRocket_AJAX_domain') . '</option>';
                        echo '<option value="ajax"'.($searchf_suggest_method == "ajax" ? ' selected' : '').'>' . __('AJAX', 'BeRocket_AJAX_domain') . '</option>';
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            echo '<div class="braapf_attribute_setup_flex">';
                echo '<div class="braapf_searchf_suggest_height braapf_half_select_full">';
                    $searchf_suggest_height = br_get_value_from_array($braapf_filter_settings, 'searchf_suggest_height', '');
                    echo '<label for="braapf_searchf_suggest_height">' . __('Suggestions Max Height', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<div class="braapf_flex_elements">';
                        echo '<input type="number" min="0" id="braapf_searchf_suggest_height" name="'.$settings_name.'[searchf_suggest_height]" value="' . $searchf_suggest_height . '">';
                        echo '<span class="braapf_size_ext">'.__('px', 'BeRocket_AJAX_domain'), '</span>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
            ?>
            <script>
                berocket_show_element('.braapf_searchf_suggest', '{.braapf_widget_type input[type=radio]} == "search_field"');
                berocket_show_element('.braapf_searchf_suggest_method', '{.braapf_widget_type input[type=radio]} == "search_field" && {#braapf_searchf_suggest} != ""');
                berocket_show_element('.braapf_searchf_suggest_height', '{.braapf_widget_type input[type=radio]} == "search_field" && {#braapf_searchf_suggest} != ""');
            </script>
            <?php
        }
        static function reset_filters($settings_name, $braapf_filter_settings) {
            echo '<div class="braapf_attribute_setup_flex">';
                echo '<div class="braapf_searchf_filters_include braapf_full_select_full">';
                    $searchf_filters_include = br_get_value_from_array($braapf_filter_settings, 'searchf_filters_include', '');
                    echo '<label for="braapf_searchf_filters_include">' . __('Search products with filters', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<select id="braapf_searchf_filters_include" name="'.$settings_name.'[searchf_filters_include]">';
                        echo '<option value="include"'.($searchf_filters_include == "include" ? ' selected' : '').'>' . __('Yes, use other filters', 'BeRocket_AJAX_domain') . '</option>';
                        echo '<option value="exclude"'.($searchf_filters_include == "exclude" ? ' selected' : '').'>' . __('No, exclude other filters and disable after search', 'BeRocket_AJAX_domain') . '</option>';
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            ?>
            <script>
                berocket_show_element('.braapf_searchf_filters_include', '{.braapf_widget_type input[type=radio]} == "search_field"');
            </script>
            <?php
        }
        static function texts($settings_name, $braapf_filter_settings) {
            echo '<div class="braapf_attribute_setup_flex">';
                echo '<div class="braapf_searchf_placeholder braapf_half_select_full">';
                    $searchf_placeholder = br_get_value_from_array($braapf_filter_settings, 'searchf_placeholder', '');
                    echo '<label for="braapf_searchf_placeholder">' . __('Search Field placeholder', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input type="text" id="braapf_searchf_placeholder" name="'.$settings_name.'[searchf_placeholder]" value="' . $searchf_placeholder . '">';
                echo '</div>';
                echo '<div class="braapf_searchf_button_text braapf_half_select_full">';
                    $searchf_button_text = br_get_value_from_array($braapf_filter_settings, 'searchf_button_text', __('Search', 'BeRocket_AJAX_domain'));
                    echo '<label for="braapf_searchf_button_text">' . __('Search button text', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input type="text" id="braapf_searchf_button_text" name="'.$settings_name.'[searchf_button_text]" value="' . $searchf_button_text . '" placeholder="'.__('Search', 'BeRocket_AJAX_domain').'">';
                echo '</div>';
            echo '</div>';
            ?>
            <script>
                berocket_show_element('.braapf_searchf_placeholder', '{.braapf_widget_type input[type=radio]} == "search_field"');
                berocket_show_element('.braapf_searchf_button_text', '{.braapf_widget_type input[type=radio]} == "search_field" && {.braapf_templates_list input[type=radio]} != "input_button_icon"');
            </script>
            <?php
        }
    }
    new BeRocket_AAPF_search_field_apply();
}

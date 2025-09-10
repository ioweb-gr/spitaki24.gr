<?php
class BeRocket_AAPF_paid extends BeRocket_plugin_variations {
    public static $instance = null;
    public $plugin_name = 'ajax_filters';
    public $version_number = 20;
    public $default_permalink = array (
        'variable' => 'filters',
        'value'    => '/values',
        'split'    => '/',
    );
    public $default_nn_permalink = array (
        'variable' => 'filters',
        'value'    => '[values]',
        'split'    => '|',
    );
    public static function getInstance()
    {
        if (null === static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    function __construct() {
        static::$instance = $this;
        if( $this->init_validation() ) {
            foreach (glob(__DIR__ . "/paid/url-parse/*.php") as $filename)
            {
                include_once($filename);
            }
            parent::__construct();
            $this->defaults = array(
                'use_links_filters'         => '',
                'use_noindex'               => '',
                'use_nofollow'              => '',
                'nice_urls'                 => '',
                'canonicalization'          => '',
                'ub_product_count'          => '',
                'ub_product_text'           => 'products',
                'ub_product_button_text'    => 'Show',
                'object_cache'              => '',
                'search_variation_image'    => '',
                'search_variation_price'    => '',
                'slider_250_fix'            => '',
            );
            add_filter( 'berocket_filter_filter_type_array', array( $this, 'filter_type_array' ) );
            add_filter( 'berocket_aapf_single_filter_conditions_list', array( $this, 'aapf_conditions' ) );
            add_filter( 'berocket_aapf_group_filters_conditions_list', array( $this, 'aapf_conditions' ) );
            add_filter( 'aapf_localize_widget_script', array($this, 'aapf_localize_widget_script') );
            add_action( 'plugins_loaded', array($this, 'plugins_loaded') );
            add_action( 'init', array($this, 'init_translation'), 1 );
            add_action( 'admin_head', array($this, 'admin_init'), 11 );
            if ( ! is_admin() ) {
                add_action( 'wp_head', array( $this, 'wp_head_enqueue' ) );
            }

            //AJAX
            add_filter( 'berocket_aapf_filter_variable_name_nn', array($this, 'permalink_variable_nn_name') );
            add_filter( 'berocket_aapf_filter_variable_name', array($this, 'permalink_variable_name') );

            //SECTIONS
            add_filter('brfr_ajax_filters_elements_above', array($this, 'section_elements_above'), $this->version_number, 3);
            
            //SEO TITLE META
            add_filter('berocket_aapf_seo_meta_filtered_term_continue', array($this, 'seo_meta_filtered_term_continue'), 5, 2);
            add_filter('berocket_aapf_query_var_title_filter', array($this, 'query_var_title'), 10, 3);
            
            //SLIDER ATTRIBUTES
            add_filter('berocket_query_var_title_before_widget', array($this, 'attribute_price_var_title'), 10, 5);
            add_filter('berocket_query_var_title_before_widget_deprecated', array($this, 'attribute_price_var_title'), 10, 5);
            add_filter('berocket_aapf_get_terms_additional', array($this, 'add_slider_numeric_sorting'), 10, 2);
            //NEW SLIDER ATTRIBUTES
            add_filter('berocket_query_var_title_before_widget', array($this, 'new_slider_vars'), 10, 5);
            add_filter('berocket_query_var_title_before_widget_deprecated', array($this, 'new_slider_vars'), 10, 5);
            add_filter('BeRocket_AAPF_template_full_content', array($this, 'new_attribute_slider'), 1, 3);
            add_filter($this->plugin_name . '_get_template_part', array($this, 'get_template_part'), 1, 2);
            include "paid/search_field.php";
            include "paid/advanced_caching.php";
            include "paid/order-product.php";
            include "paid/divi-module.php";
            //COMPATIBILITY
            add_filter('bapf_compatibility_include', array($this, 'compatibility_include'), 10, 1);
            add_filter('brapf_wizard_addons_save', array($this, 'wizard_update_permalinks'), 10, 1);
            add_filter('brapf_wizard_selectors_save', array($this, 'wizard_update_permalinks'), 10, 1);
        }
    }

    function compatibility_include($compatibility_features) {
        if( isset($compatibility_features['RankMath']) ) {
            if( ! isset($compatibility_features['RankMath']['file_add']) || ! is_array($compatibility_features['RankMath']['file_add']) ) {
                $compatibility_features['RankMath']['file_add'] = array();
            }
            $compatibility_features['RankMath']['file_add'][] = 'paid/compatibility/rank_math_seo.php';
        }
        return $compatibility_features;
    }

    function wizard_update_permalinks($option) {
		if ( ! empty( $option['nice_urls'] ) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $default_values = $BeRocket_AAPF->default_permalink;
            $this->save_permalink_option( $default_values );
		}
    }

    function init_validation() {
        return ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && 
            br_get_woocommerce_version() >= 2.1 );
    }

    function get_template_part($template, $name) {
        if( ! $template && $name && strpos($name, 'paid/') !== FALSE ) {
            $name_paid = str_replace('paid/', '', $name);
            if ( file_exists( plugin_dir_path( __FILE__ ) . "templates/{$name_paid}.php" ) ) {
                $template = plugin_dir_path( __FILE__ ) . "templates/{$name_paid}.php";
            }
        }
        return $template;
    }

    function settings_page($data) {
        $data['General']['hide_values']['items']['hide_value_button'] = array(
            "type"      => "checkbox",
            "name"      => array("hide_value", 'button'),
            "value"     => '1',
            'label_for' => __('Hide the Show/Hide value(s) button in filters', 'BeRocket_AJAX_domain'),
        );
        $data['SEO'] = berocket_insert_to_array(
            $data['SEO'],
            'seo_friendly_urls',
            array(
                'use_links_filters' => array(
                    "label"     => __( 'Use links for checkboxes and radio filter', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "use_links_filters",
                    "value"     => '1',
                    "class"     => "berocket_use_links_filters",
                ),
                'use_noindex' => array(
                    "label"     => __( 'Use noindex for links', "BeRocket_AJAX_domain" ),
                    "type"      => "selectbox",
                    "name"      => "use_noindex",
                    "tr_class"  => "berocket_use_noindex",
                    "options"   => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Second+ levels', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('All levels', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '',
                ),
                'use_nofollow' => array(
                    "label"     => __( 'Use nofollow for links', "BeRocket_AJAX_domain" ),
                    "type"      => "selectbox",
                    "name"      => "use_nofollow",
                    "tr_class"  => "berocket_use_nofollow",
                    "options"   => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Second+ levels', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('All levels', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '',
                ),
            )
        );
        $data['SEO'] = berocket_insert_to_array(
            $data['SEO'],
            'slug_urls',
            array(
                'nice_urls' => array(
                    "label"     => __( 'Nice URLs', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "nice_urls",
                    "value"     => '1',
                    'class'     => 'berocket_nice_url',
                    'label_for' => __("WordPress permalinks must be set to Post name(Custom structure: /%postname%/ )", 'BeRocket_AJAX_domain'),
                ),
                'canonicalization' => array(
                    "label"     => __( 'Base Canonical URL', "BeRocket_AJAX_domain" ),
                    "type"      => "selectbox",
                    "name"      => "canonicalization",
                    "options"   => array(
                        array('value' => '', 'text' => __('With filters', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('Without filters', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Disable', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '',
                    'label_for' => __("Use canonical tag without filters on WooCommerce pages", 'BeRocket_AJAX_domain'),
                ),
            )
        );
        $data['Elements']['elements_position_hook']['label'] = __( 'Elements position', "BeRocket_AJAX_domain" );
        $data['Elements']['ub_product_count'] = array(
            "label"     => __( 'Show the number of products before filtering', "BeRocket_AJAX_domain" ) . '<span id="braapf_ub_product_count_info" class="dashicons dashicons-editor-help"></span>',
            'items' => array(
                'ub_product_count' => array(
                    "type"      => "checkbox",
                    "name"      => "ub_product_count",
                    "value"     => '1',
                    'label_for'  => __("Show the product count before filtering. Requires the update button.", 'BeRocket_AJAX_domain') . '<br>',
                ),
                'ub_product_text' => array(
                    "type"      => "text",
                    "name"      => "ub_product_text",
                    "value"     => $this->defaults["ub_product_text"],
                    'label_for'  => __('Text for "products"', 'BeRocket_AJAX_domain') . '<br>',
                ),
                'ub_product_button_text' => array(
                    "type"      => "text",
                    "name"      => "ub_product_button_text",
                    "value"     => $this->defaults["ub_product_button_text"],
                    'label_for'  => __('Text for "show" button', 'BeRocket_AJAX_domain') . '<br>',
                ),
            )
        );
        $data['Elements']['elements_above'] = array(
            "section"   => "elements_above",
            "value"     => "",
        );
        $data['General'] = berocket_insert_to_array(
            $data['General'],
            'out_of_stock_variable',
            array(
                'display_variation_data' => array(
                    "label"     => __( 'Display variation data', "BeRocket_AJAX_domain" ) . '<span id="braapf_display_variation_data_info" class="dashicons dashicons-editor-help"></span>',
                    "items"     => array(
                        'search_variation_image' => array(
                            "label"     => __( 'Display variation image', "BeRocket_AJAX_domain" ),
                            "type"      => "checkbox",
                            "name"      => "search_variation_image",
                            "value"     => '1',
                            'label_for' => __('Image', 'BeRocket_AJAX_domain'),
                        ),
                        'search_variation_price' => array(
                            "label"     => __( 'Display variation price', "BeRocket_AJAX_domain" ),
                            "type"      => "checkbox",
                            "name"      => "search_variation_price",
                            "value"     => '1',
                            'label_for' => __('Price', 'BeRocket_AJAX_domain'),
                        ),
                    ),
                ),
                'use_filter_for_variation' => array(
                    "label"     => __( 'Remember variation<br>options', "BeRocket_AJAX_domain" ) . '<span id="braapf_use_filter_for_variation_show_info" class="dashicons dashicons-editor-help"></span>',
                    "name"     => "select_filter_variation",   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => 'url', 'text' => __('Replace Product URL', 'BeRocket_AJAX_domain')),
                        array('value' => 'session', 'text' => __('Set in PHP Session', 'BeRocket_AJAX_domain')),
                        array('value' => 'url_session', 'text' => __('Both URL and Session', 'BeRocket_AJAX_domain')),
                    ),
                    "value"    => '',
                ),
            )
        );
        $data['Advanced'] = berocket_insert_to_array(
            $data['Advanced'],
            'search_fix',
            array(
                'slider_250_fix' => array(
                    "tr_class"  => "bapf_incompatibility_fixes bapf_incompatibility_fixes_hide",
                    "label"     => __( 'Slider has many values', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "slider_250_fix",
                    "value"     => '1',
                    'label_for' => __('Enable the setting if the slider has more than 250 values. Hierarchical taxonomy may not work correctly with sliders.', 'BeRocket_AJAX_domain'),
                ),
            )
        );
        $data['Design'] = berocket_insert_to_array(
            $data['Design'],
            'color_img_tooltip_design',
            array(
                'product_count_tooltip_design' => array(
                    "label"     => __( 'Products Count Before Update', "BeRocket_AJAX_domain" ),
                    "items" => array(
                        "tippy_theme" => array(
                            "type"      => "selectbox",
                            "name"      => 'tippy_product_count_theme',
                            "options"  => array(
                                array('value' => 'light', 'text' => __('Light', 'BeRocket_AJAX_domain')),
                                array('value' => 'dark', 'text' => __('Dark', 'BeRocket_AJAX_domain')),
                                array('value' => 'translucent', 'text' => __('Translucent', 'BeRocket_AJAX_domain')),
                            ),
                            "value"     => '',
                            'label_be_for' => __('Tooltip Theme', 'BeRocket_AJAX_domain'),
                        ),
                        'tippy_fontsize' => array(
                            "type"         => "number",
                            "name"         => "tippy_product_count_fontsize",
                            "value"        => '',
                            'extra'        => 'placeholder="' . __('From Theme', 'BeRocket_AJAX_domain') . '"',
                            'label_be_for' => __('Tooltip Font Size', 'BeRocket_AJAX_domain'),
                        ),
                    ),
                ),
            )
        );
        //Show the number of products before filtering
        $tooltip_text = '<strong>' . __('Display a tooltip with the product count expected after applying filters.', 'BeRocket_AJAX_domain') . '</strong>'
        . '<p>' . __('Works only if filters are displayed with the "Update Products button".', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . __('The option uses the Tippy script, which may be incompatible with some other plugins.', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_ub_product_count_info', $tooltip_text);
        //Display variation data
        $tooltip_text = '<strong>' . __('Change the image/price on variable products to the image/price from the variation that has the attribute value of selected filters.', 'BeRocket_AJAX_domain') . '</strong>'
        . '<p>' . __('Image replacement may not work on some themes. Our plugin uses the default WooCommerce functionality to replace images, but some themes do not use it.', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . __('If you have this issue, please get in touch with the theme developer.', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_display_variation_data_info', $tooltip_text);
        //Remember variation options
        $tooltip_text = '<h3 style="color:white;">' . __('Initial variation on the variable product page is based on selected filters.', 'BeRocket_AJAX_domain') . '</h3>'
        . '<p>' . '<strong style="color:#0085ba;">' . __('Replace Product URL', 'BeRocket_AJAX_domain') . '</strong> - ' . __('selected filters will be added to the product URL on the products archive page (may not work with some themes).', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . '<strong style="color:#0085ba;">' . __('Set in PHP Session', 'BeRocket_AJAX_domain') . '</strong> - ' . __('selected filters will be stored in the PHP session. It stores the filters for all products that will be opened after filtering.', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . '<strong style="color:#0085ba;">' . __('Both URL and Session', 'BeRocket_AJAX_domain') . '</strong> - ' . __('enable two previous methods and allow them to be used simultaneously.', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_use_filter_for_variation_show_info', $tooltip_text);
        
        return $data;
    }
    function section_elements_above ( $item, $options ) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $html = '<tr>
            <th scope="row">' . __('Elements over products', 'BeRocket_AJAX_domain') . '<span id="braapf_elements_over_products_info" class="dashicons dashicons-editor-help"></span>' . '</th>
            <td>';
                $posts_args = array(
                    'posts_per_page'   => -1,
                    'offset'           => 0,
                    'category'         => '',
                    'category_name'    => '',
                    'include'          => '',
                    'exclude'          => '',
                    'meta_key'         => '',
                    'meta_value'       => '',
                    'post_type'        => 'br_filters_group',
                    'post_mime_type'   => '',
                    'post_parent'      => '',
                    'author'           => '',
                    'post_status'      => 'publish',
                    'fields'           => 'ids',
                    'suppress_filters' => false 
                );
                $posts_array = new WP_Query($posts_args);
                $br_filters_group = $posts_array->posts;
                $html .= '<div>' . __('Group', 'BeRocket_AJAX_domain') . '<select>';
                foreach($br_filters_group as $post_id) {
                    $html .= '<option data-name="' . get_the_title($post_id) . '" value="' . $post_id . '">' . get_the_title($post_id) . ' (ID:' . $post_id . ')</option>';
                }
                $html .= '</select><button class="button berocket_elements_above_group" type="button">'.__('Add group', 'BeRocket_AJAX_domain').'</button></div>';
                $html .= '<ul class="berocket_elements_above_products">';
                if( is_array(br_get_value_from_array($options, 'elements_above_products')) ) {
                    foreach($options['elements_above_products'] as $post_id) {
                        $post_type = get_post_type($post_id);
                        $html .= '<li class="berocket_elements_added_' . $post_id . '"><fa class="fa fa-bars"></fa>
                            <input type="hidden" name="br_filters_options[elements_above_products][]" value="' . $post_id . '">
                            ' . get_the_title($post_id) . ' (ID:' . $post_id . ')
                            <i class="fa fa-times"></i>
                        </li>';
                    }
                }
                $html .= '</ul>';
                wp_enqueue_script('jquery-color');
                wp_enqueue_script('jquery-ui-sortable');
                $html .= "<script>
                    jQuery(document).on('click', '.berocket_elements_above_group', function(event) {
                        event.preventDefault();
                        var selected = jQuery(this).prev().find(':selected');
                        post_id = selected.val();
                        post_title = selected.text();
                        if( ! jQuery('.berocket_elements_added_'+post_id).length ) {
                            var html = '<li class=\"berocket_elements_added_'+post_id+'\"><fa class=\"fa fa-bars\"></fa>';
                            html += '<input type=\"hidden\" name=\"br_filters_options[elements_above_products][]\" value=\"'+post_id+'\">';
                            html += post_title;
                            html += '<i class=\"fa fa-times\"></i></li>';
                            jQuery('.berocket_elements_above_products').append(jQuery(html));
                        } else {
                            jQuery('.berocket_elements_added_'+post_id).css('background-color', '#ee3333').clearQueue().animate({backgroundColor:'#eeeeee'}, 1000);
                        }
                    });
                    jQuery(document).on('click', '.berocket_elements_above_products .fa-times', function(event) {
                        jQuery(this).parents('li').first().remove();
                    });
                    jQuery(document).ready(function() {
                        if(typeof(jQuery( \".berocket_elements_above_products\" ).sortable) == 'function') {
                            jQuery( \".berocket_elements_above_products\" ).sortable({axis:\"y\", handle:\".fa-bars\", placeholder: \"berocket_sortable_space\"});
                        }
                    });
                </script>
                <style>
                .berocket_elements_above_products li {
                    font-size: 2em;
                    border: 2px solid rgb(153, 153, 153);
                    background-color: rgb(238, 238, 238);
                    padding: 5px;
                    line-height: 1.1em;
                }
                .berocket_elements_above_products li .fa-bars {
                    margin-right: 0.5em;
                    cursor: move;
                }
                .berocket_elements_above_products small {
                    font-size: 0.5em;
                    line-height: 2em;
                    vertical-align: middle;
                }
                .berocket_elements_above_products li .fa-times {
                    margin-left: 0.5em;
                    cursor: pointer;
                    float: right;
                }
                .berocket_elements_above_products li .fa-times:hover {
                    color: black;
                }
                .berocket_elements_above_products .berocket_edit_filter {
                    vertical-align: middle;
                    font-size: 0.5em;
                    float: right;
                    line-height: 2em;
                    height: 2em;
                    display: inline-block;
                }
                .berocket_elements_above_products .berocket_sortable_space {
                    border: 2px dashed #aaa;
                    background: white;
                    font-size: 2em;
                    height: 1.1em;
                    box-sizing: content-box;
                    padding: 5px;
                }
                .br_framework_settings .button.berocket_elements_above_group {
                    margin: 0;
                    margin-left: 10px;
                    padding: 2px;
                }
                </style>
            </td>
        </tr>";
        $tooltip_text = '<strong>' . __('This will work only on the default WooCommerce pages.', 'BeRocket_AJAX_domain') . '</strong>'
        . '<p>' . __('Default WooCommerce pages are: shop page, category page, tag page, attribute page, etc.', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . __('It also does not work on WooCommerce pages edited with page builders (e.g., Divi Builder, Elementor Builder).', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_elements_over_products_info', $tooltip_text);
        return $html;
    }

    function wp_head_enqueue() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( empty($options['styles_in_footer']) ) {
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
    }
    function filter_type_array($filter_type_array) {
        $filter_type_array = berocket_insert_to_array($filter_type_array, 'product_cat', array('custom_product_cat' => array(
            'name' => __('Product Category', 'BeRocket_AJAX_domain'),
            'sameas' => 'custom_taxonomy',
            'attribute' => 'product_cat',
        )), true);
        return $filter_type_array;
    }
    function aapf_conditions($conditions) {
        $conditions[] = 'condition_page_woo_attribute';
        $conditions[] = 'condition_page_woo_search';
        $conditions[] = 'condition_user_role';
        $conditions[] = 'condition_user_status';
        return $conditions;
    }
    function aapf_localize_widget_script($localize) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $option_permalink = $this->get_permalinks_oprions();
        $option_nn_permalink = $this->get_nn_permalinks_oprions();
        $permalink_values = explode( 'values', $option_permalink['value'] );
        $nn_permalink_values = explode( 'values', $option_nn_permalink['value'] );
        $localize['ub_product_count']           = ( empty($options['ub_product_count']) ? '' : $options['ub_product_count'] );
        $localize['ub_product_text']            = ( empty($options['ub_product_text']) ? '' : $options['ub_product_text'] );
        $localize['ub_product_button_text']     = ( empty($options['ub_product_button_text']) ? '' : $options['ub_product_button_text'] );
        $localize['number_style']               = array(
            wc_get_price_thousand_separator(), 
            wc_get_price_decimal_separator(), 
            wc_get_price_decimals()
        );
        $localize['hide_button_value']          = ( empty($options['hide_value']['button']) ? '' : $options['hide_value']['button'] );
        $localize['nice_urls']                  = ( empty($options['nice_urls']) ? '' : $options['nice_urls'] );
        $localize['nice_url_variable']          = $option_permalink['variable'];
        $localize['nice_url_mask']              = '%t%' . $permalink_values[0] . '%v%' . $permalink_values[1];
        $localize['nice_url_value_1']           = $permalink_values[0];
        $localize['nice_url_value_2']           = $permalink_values[1];
        $localize['nice_url_split']             = $option_permalink['split'];
        $localize['nn_url_variable']            = $option_nn_permalink['variable'];
        $localize['nn_url_mask']                = '%t%' . $nn_permalink_values[0] . '%v%' . $nn_permalink_values[1];
        $localize['nn_url_value_1']             = $nn_permalink_values[0];
        $localize['nn_url_value_2']             = $nn_permalink_values[1];
        $localize['nn_url_split']               = ($option_nn_permalink['split'] == '&' ? '/' : $option_nn_permalink['split']);
        
        if( empty($options['nice_urls']) ) {
            $localize['url_variable']   = $option_nn_permalink['variable'];
            $localize['url_mask']       = '%t%' . $nn_permalink_values[0] . '%v%' . $nn_permalink_values[1];
            $localize['url_split']      = ($option_nn_permalink['split'] == '&' ? '/' : $option_nn_permalink['split']);
        } else {
            $localize['url_variable']   = $option_permalink['variable'];
            $localize['url_mask']       = '%t%' . $permalink_values[0] . '%v%' . $permalink_values[1];
            $localize['url_split']      = $option_permalink['split'];
        }
        return $localize;
    }
    function permalink_variable_name($name) {
        $option_permalink = $this->get_permalinks_oprions();
        return $option_permalink['variable'];
    }
    function permalink_variable_nn_name($name) {
        $option_nn_permalink = $this->get_nn_permalinks_oprions();
        return $option_nn_permalink['variable'];
    }
    function init_translation() {
        $BeRocket_AAPF_group_filters = BeRocket_AAPF_group_filters::getInstance();
        $BeRocket_AAPF_group_filters->add_meta_box('search_box', __( 'Search Box', 'BeRocket_AJAX_domain' ), array($this, 'search_box'));
    }
    function plugins_loaded() {
        $this->global_settings();
        $this->group_add();
        $this->filter_add();
        $this->multiple_color();
        $this->ranges();
        $this->count_before_update();
        add_filter('berocket_aapf_group_before_all', array($this, 'group_is_hide_before_group'), 10, 2);
        add_filter('berocket_aapf_group_after_all', array($this, 'group_is_hide_after_group'), 10, 2);
    }
    function admin_init() {
        $screen = get_current_screen();
        $admin_js = '';
        if( berocket_isset($screen, 'id') != 'widgets' ) {
            $admin_js .= 'berocket_admin_filter_types_by_attr.ranges = "<option value=\'ranges\'>'.__('Ranges', 'BeRocket_AJAX_domain').'</option>";
            berocket_admin_filter_types.price.push("ranges");';
        }
        $admin_js .= 'berocket_admin_filter_types.custom_taxonomy.push("slider");
        berocket_admin_filter_types.attribute.push("slider");
        berocket_admin_filter_types.filter_by.push("slider");';
        wp_add_inline_script('berocket_aapf_widget-admin', $admin_js);
    }
    function search_box($post) {
        $BeRocket_AAPF_group_filters = BeRocket_AAPF_group_filters::getInstance();
        wp_enqueue_script('jquery-ui-sortable');
        $filters = $BeRocket_AAPF_group_filters->get_option($post->ID);
        $post_name = $BeRocket_AAPF_group_filters->post_name;
        $categories = BeRocket_AAPF_Widget_functions::get_product_categories();
        $categories = BeRocket_AAPF_Widget_functions::set_terms_on_same_level( $categories );
        include "templates/filters_search_box.php";
    }
    //GLOBAL SETTINGS
    function global_settings() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();

        if( is_array(br_get_value_from_array($option, 'elements_above_products')) && count($option['elements_above_products']) ) {
            add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'elements_above_products'), 1 );
        }
        add_filter( 'berocket_aapf_is_filtered_page_check', array($this, 'is_filtered_word_changed'), 10, 3 );
        if ( ! empty( $option['nice_urls'] ) ) {
            add_action( 'init', array( $this, 'nice_url_init' ) );

            if ( defined( 'POLYLANG_BASENAME' ) ) {
                add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ), 9 );
                add_filter('pll_filtered_taxonomies', array($this, 'pll_filtered_taxonomies'), 10, 2);
                add_filter('pll_rewrite_rules', array($this, 'pll_rewrite_rules'));
            }
            add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ), 999999999 );
            add_filter( 'query_vars', array( $this, 'add_queryvars' ) );
            add_filter( 'berocket_aapf_current_page_url', array($this, 'current_page_url'), 10, 2 );
            add_filter( 'berocket_aapf_is_filtered_page_check', array($this, 'is_filtered_with_nice_url'), 10, 3 );
        }
        add_action('wp_head', array($this, 'wp_head_canonical'), 99999);
        add_action('wpseo_canonical', array($this, 'yoast_canonical'), 99999);
        if( ! empty($option['search_variation_image']) ) {
            include_once( dirname( __FILE__ ) . '/paid/woocommerce-variation-image.php' );
        }
        if( ! empty($option['search_variation_price']) ) {
            include_once( dirname( __FILE__ ) . '/paid/woocommerce-variation-price.php' );
        }
        if( ! empty($option['select_filter_variation'])  ) {
            include_once( dirname( __FILE__ ) . '/paid/variation-link.php' );
        }
        add_action( 'berocket_aapf_wizard_attribute_count_hide_values', array( $this, 'wizard_attribute_count_hide_values' ), 10, 1 );
        add_action( 'current_screen', array( $this, 'register_permalink_option' ) );
    }
    function wizard_attribute_count_hide_values($option) {
        ?>
        <div><label><input name="berocket_aapf_wizard_settings[hide_value][button]" class="attribute_count_preset_16" type="checkbox" value="1"
        <?php if( ! empty($option['hide_value']['button']) ) echo " checked"; ?>>
        <?php _e('Hide "Show/Hide value(s)" button', 'BeRocket_AJAX_domain') ?>
        </label></div>
        <?php
    }
    function elements_above_products() {
        $BeRocket_AAPF           = BeRocket_AAPF::getInstance();
        $options                 = $BeRocket_AAPF->get_option();
        $elements_above_products = br_get_value_from_array($options, 'elements_above_products');
        if ( ! is_array( $elements_above_products ) ) {
            $elements_above_products = array();
        }

        if ( $elements_above_products ) {
            $current_language = apply_filters( 'wpml_current_language', NULL );
            $BeRocket_AAPF_group_filters = BeRocket_AAPF_group_filters::getInstance();
            foreach($elements_above_products as $element_above_products) {
                $group_id      = apply_filters( 'wpml_object_id', $element_above_products, 'page', true, $current_language );
                $group_options = $BeRocket_AAPF_group_filters->get_option( $group_id );
                $extra_class   = '';
                if ( ! empty( $group_options['hide_group'] ) and is_array( $group_options['hide_group'] ) ) {
                    foreach ( $group_options['hide_group'] as $device => $active ) {
                        if ( $active and $device ) {
                            $extra_class .= ' bapf_sngl_hd_' . $device;
                        }
                    }

                }
                ob_start();
                the_widget( 'BeRocket_new_AAPF_Widget', array('group_id' => $element_above_products));
                $element_above_products_content = ob_get_clean();
                if( ! empty($element_above_products_content) ) {
                    echo '<div class="berocket_element_above_products' . $extra_class . '">';
                    echo $element_above_products_content;
                    echo '</div><div class="berocket_element_above_products_after"></div>';
                }
            }
        }
    }
    public function group_is_hide_before_group($custom_vars, $filters) {
        if ( ! empty( $filters[ 'group_is_hide' ] ) ) {
            $extra_class   = '';
            if ( ! empty( $filters['hide_group'] ) and is_array( $filters['hide_group'] ) ) {
                foreach ( $filters['hide_group'] as $device => $active ) {
                    if ( $active and $device ) {
                        $extra_class .= ' bapf_sngl_hd_' . $device;
                    }
                }

            }
            echo '<a href="#toggle-filters" class="berocket_element_above_products_is_hide_toggle berocket_ajax_filters_toggle' . ( ( ! empty( $filters[ 'group_is_hide_theme' ] ) ) ? ' theme-' . $filters[ 'group_is_hide_theme' ] : '' ) . ( ( ! empty( $filters['group_is_hide_icon_theme'] ) ) ? ' icon-theme-' . $filters['group_is_hide_icon_theme'] : '' ) . $extra_class . '"><span><i></i><b></b><s></s></span>' . __( 'SHOW FILTERS', 'BeRocket_AJAX_domain' ) . '</a>';
            echo '<div class="berocket_element_above_products_is_hide br_is_hidden">';
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
        return $custom_vars;
    }
    public function group_is_hide_after_group($custom_vars, $filters) {
        if ( ! empty( $filters[ 'group_is_hide' ] ) ) {
            echo '</div>';
        }
        return $custom_vars;
    }
    public function wp_head_sidebar() {
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'custom_sidebar_toggle'), 1 );
        }
    }
    public function wp_init_sidebar() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        add_action( 'wp_enqueue_scripts', array( $BeRocket_AAPF, 'include_all_scripts' ) );
    }
    public function shortcode_sidebar_button($args = array()) {
        ob_start();
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $this->custom_sidebar_toggle($args);
        }
        return ob_get_clean();
    }
    public function custom_sidebar_toggle($args = array()) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $theme_class      = (empty($args['theme']) ? ( ( ! empty( $option['sidebar_collapse_theme'] ) ) ? ' theme-' . $option['sidebar_collapse_theme'] : '' ) : ' theme-'.$args['theme'] );
        $icon_theme_class = (empty($args['icon_theme']) ? ( ( ! empty( $option['sidebar_collapse_icon_theme'] ) ) ? ' icon-theme-' . $option['sidebar_collapse_icon_theme'] : '' ) : ' icon-theme-'.$args['icon_theme'] );
        $button_text      = (empty($args['title']) ? __( 'SHOW FILTERS', 'BeRocket_AJAX_domain' ) : $args['title']);
        echo '<a href="#toggle-sidebar" class="berocket_ajax_filters_sidebar_toggle berocket_ajax_filters_toggle' . $theme_class . '' . $icon_theme_class . '"><span><i></i><b></b><s></s></span>' . $button_text . '</a>';
        BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
    }
    //NICE URL
    public function nice_url_init () {
        $option_permalink = $this->get_permalinks_oprions();
        add_rewrite_endpoint($option_permalink['variable'], EP_PERMALINK|EP_SEARCH|EP_CATEGORIES|EP_TAGS|EP_PAGES);
    }
    function add_rewrite_rules ( $rules ) {
        $newrules = array();
        $shop_id = wc_get_page_id('shop');
        $shop_page_slugs = array(_x( 'shop', 'default-slug', 'woocommerce' ) => _x( 'shop', 'default-slug', 'woocommerce' ));
        $languages = apply_filters('wpml_active_languages', array());
        if( ! is_array($languages) || ! count($languages) ) {
            $languages = array('0' => array());
        }
        $option_permalink_languages = array();
        $wpml_active_languages = apply_filters('wpml_current_language', NULL);
        foreach($languages as $language_code => $language) {
            do_action( 'wpml_switch_language', $language_code );
            $option_permalink_languages[$language_code] = $this->get_permalinks_oprions();
        }
        do_action( 'wpml_switch_language', $wpml_active_languages );
        if( ! empty($shop_id) ) {
            foreach($languages as $language_code => $language) {
                $option_permalink = $option_permalink_languages[$language_code];
                $shop_slug = get_post(apply_filters('wpml_object_id', $shop_id, 'page', TRUE, $language_code));
                $newrules[$option_permalink['variable'].'/(.*)/?'] = 'index.php?post_type=product&'.$option_permalink['variable'].'=$matches[1]';
                if ( ! empty( $shop_slug ) and is_object( $shop_slug ) and ! empty( $shop_slug->post_name ) ) {
                    $shop_post_name = $shop_slug->post_name;
                    $shop_page_slug = get_page_uri($shop_slug);
                    if ( br_get_woocommerce_version() >= 2.7 ) {
                        $newrules[ urldecode($shop_page_slug) . '/' . $option_permalink[ 'variable' ] . '/(.*)/?' ] = 'index.php?post_type=product&' . $option_permalink[ 'variable' ] . '=$matches[1]';
                    } else {
                        $newrules[ $shop_page_slug . '/' . $option_permalink[ 'variable' ] . '/(.*)/?' ] = 'index.php?pagename=' . $shop_post_name . '&' . $option_permalink[ 'variable' ] . '=$matches[1]';
                    }
                }
            }
        }
        $option_permalink = $this->get_permalinks_oprions();
        $category_base = get_option( 'woocommerce_permalinks' );
        $tag_base = $category_base['tag_base'];
        $category_base = $category_base['category_base'];

        if ( empty($category_base) ) {
            $category_base = _x( 'product-category', 'slug', 'woocommerce' );
        }
        if ( empty($tag_base) ) {
            $tag_base = _x( 'product-tag', 'slug', 'woocommerce' );
        }
        $product_taxonomies = get_object_taxonomies('product');
        $product_taxonomies = array_diff($product_taxonomies, array('product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class'));
        
        foreach($languages as $language_code => $language) {
            $option_permalink = $option_permalink_languages[$language_code];
            $newrules[$category_base.'/(.+?)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?product_cat=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';
            $newrules[$tag_base.'/([^/]+)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?product_tag=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';
            foreach($product_taxonomies as $product_taxonomy) {
                $product_taxonomy = get_taxonomy($product_taxonomy);
                if( ! empty($product_taxonomy->public) ) {
                    if( ! empty($product_taxonomy->rewrite) && ! empty($product_taxonomy->rewrite['slug']) ) {
                        $taxonomy_base = $product_taxonomy->rewrite['slug'];
                    } else {
                        $taxonomy_base = $product_taxonomy->name;
                    }
                    if( $taxonomy_base[0] == '/' ) {
                        $taxonomy_base = substr($taxonomy_base, 1);
                    }
                    $newrules[$taxonomy_base.'/([^/]+)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?'.$product_taxonomy->name.'=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';
                }
            }
        }

        $newrules = apply_filters('br_filters_rewrite_rules', $newrules);

        return $newrules + $rules;
    }
    function add_queryvars( $query_vars ) {
        $option_permalink = $this->get_permalinks_oprions();
        $query_vars[] = $option_permalink['variable'];
        return $query_vars;
    }
    function current_page_url($current_page_url, $br_options) {
        $option_permalink = $this->get_permalinks_oprions();
        $permalink_values = explode( 'values', $option_permalink['value'] );
        $current_page_url = preg_replace( "~".$option_permalink['variable']."/.+~", "", $current_page_url );
        $current_page_url = preg_replace( "~".urlencode($option_permalink['variable'])."/.+~", "", $current_page_url );
        return $current_page_url;
    }
    function is_filtered_with_nice_url($check, $func, $query = false) {
        if( $query === false ) {
            global $wp_query;
            $query = $wp_query;
        }
        $option_permalink = $this->get_permalinks_oprions();
        $check = ( $check || $query->get( $option_permalink['variable'], '' ) );
        return $check;
    }
    function is_filtered_word_changed($check, $func, $query = false) {
        $nn_option_permalink = $this->get_nn_permalinks_oprions();
        $check = ( $check || isset( $_GET[ $nn_option_permalink['variable'] ] ) );
        return $check;
    }
    //CANONICAL URL
    function wp_head_canonical() {
        if ( $this->is_canonical_applied() ) {
            $current_page_url = $this->get_current_canonical_url();
            echo '<link rel="canonical" href="' . $current_page_url . '">';
        }
    }
    function yoast_canonical($canonical) {
        remove_action('wp_head', array($this, 'wp_head_canonical'), 99999);
        if ( $this->is_canonical_applied() ) {
            $canonical = $this->get_current_canonical_url();
        }
        return $canonical;
    }
    function is_canonical_applied() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = $BeRocket_AAPF->get_option();
        $show_canonical   = ( is_post_type_archive( 'product' ) || is_shop() || is_product_taxonomy() );
        $show_canonical   = $show_canonical && br_get_value_from_array($br_options, 'canonicalization') != '2';
        return apply_filters( 'berocket_wp_head_canonical', $show_canonical, ! empty( $br_options['nice_urls'] ), br_get_value_from_array($br_options, 'canonicalization') );
    }
    function get_current_canonical_url() {
        global $wp, $sitepress, $wp_rewrite;
        $permalink_structure = get_option( 'permalink_structure' );
        $option_permalink = $this->get_permalinks_oprions();
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = $BeRocket_AAPF->get_option();
        $current_page_url = home_url( $wp->request );
        if( $wp->did_permalink ) {
            $queried_object = get_queried_object();
            if( is_a($queried_object, 'WP_Term') ) {
                $current_page_url = get_term_link($queried_object);
            }
        }
        $current_page_url = preg_replace( "~paged?/[0-9]+/?~", "", $current_page_url );
        $current_page_url = preg_replace( "~{$wp_rewrite->pagination_base}/[0-9]+/?~", "", $current_page_url );
        if( ! empty($br_options['nice_urls']) ) {
            $current_page_url = preg_replace( "~".$option_permalink['variable']."/.+~", "", $current_page_url );
            $current_page_url = preg_replace( "~".urlencode($option_permalink['variable'])."/.+~", "", $current_page_url );
        }
        if( strpos($current_page_url, '?') !== FALSE ) {
            $current_page_url = explode('?', $current_page_url);
            $current_page_url = $current_page_url[0];
        }
        if( substr($permalink_structure, -1) == '/' ) {
            $current_page_url = trailingslashit($current_page_url);
        } else {
            $current_page_url = untrailingslashit($current_page_url);
        }
        if( empty($br_options['canonicalization']) ) {
            $current_page_url = $this->add_sorted_filters($current_page_url);
        }

        return apply_filters( 'berocket_wp_head_canonical_page_url', $current_page_url );
    }
    function add_sorted_filters($link) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        global $wp_query, $wp_rewrite, $berocket_parse_page_obj;
        $paged = get_query_var('paged');
        $link = $berocket_parse_page_obj->get_link_from_data(false, $link);
        if( intval($paged) > 1 ) {
            if ( $wp_rewrite->using_permalinks() ) {
                $link_array = explode('?', $link);
                $link_array[0] = trailingslashit( $link_array[0] );
                $link_array[0] .= trailingslashit( $wp_rewrite->pagination_base );
                $link_array[0] = user_trailingslashit( $link_array[0] . $paged );
                $link = implode('?', $link_array);
            } else {
                $link = add_query_arg( 'page', $paged, user_trailingslashit( $link ) );
            }
        }
        return $link;
    }
    //GROUP SETTINGS
    function group_add() {
        add_action( 'berocket_aapf_filters_group_settings', array($this, 'group_settings'), 10, 3 );
        add_filter( 'berocket_aapf_group_before_all', array($this, 'search_box_before_group_start'), 10, 2 );
        add_filter( 'berocket_aapf_group_after_all', array($this, 'search_box_after_group_end'), 10, 2 );
        add_filter( 'berocket_aapf_group_before_filter', array($this, 'search_box_before_group_filter'), 10, 2 );
        add_filter( 'berocket_aapf_group_after_filter', array($this, 'search_box_after_group_filter'), 10, 2 );
        add_filter( 'berocket_aapf_group_new_args', array($this, 'group_new_args'), 10, 2 );
        add_filter( 'berocket_aapf_group_new_args_filter', array($this, 'group_new_args_filter'), 10, 3 );
        add_filter( 'wp_footer', array($this, 'search_box_loaded_footer') );
    }
    function group_settings($filters, $post_name, $post) {
        include "templates/filters_group.php";
    }
    //GROUP SEARCH BOX
    function search_box_before_group_start($custom_vars, $filters) {
        if( ! empty($filters['search_box']) ) {
            $search_box_main_class = array('berocket_search_box_block');
            if( ! empty($filters['hide_group']['mobile']) ) {
                $search_box_main_class[] = 'bapf_sngl_hd_mobile';
            }
            if( ! empty($filters['hide_group']['tablet']) ) {
                $search_box_main_class[] = 'bapf_sngl_hd_tablet';
            }
            if( ! empty($filters['hide_group']['desktop']) ) {
                $search_box_main_class[] = 'bapf_sngl_hd_desktop';
            }
            if( ! empty($filters['search_box_recount']) ) {
                $search_box_main_class[] = 'bapf_sbrecount';
            }
            $search_box_link_type = br_get_value_from_array($filters, 'search_box_link_type');
            $search_box_url = br_get_value_from_array($filters, 'search_box_url');
            $search_box_style = br_get_value_from_array($filters, 'search_box_style');
            $search_box_category = br_get_value_from_array($filters, 'search_box_category');
            if( $search_box_link_type == 'shop_page' ) {
                if( function_exists('wc_get_page_id') ) {
                    $search_box_url = get_permalink( wc_get_page_id( 'shop' ) );
                } else {
                    $search_box_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
                }
            } elseif( $search_box_link_type == 'category' ) {
                $search_box_url = get_term_link( $search_box_category, 'product_cat' );
            }
            $sb_style = '';
            if ( $search_box_style['position'] == 'horizontal' ) {
                $sb_count = count($filters['filters']);
                if( $search_box_style['search_position'] == 'before_after' ) {
                    $sb_count += 2;
                } else {
                    $sb_count++;
                }
                $search_box_width = (int)(100 / $sb_count);
                $sb_style .= 'width:'.$search_box_width.'%;display:inline-block;padding: 4px;';
            }
            $search_box_button_class = 'search_box_button_class_'.rand();
            $sbb_style = '';
            if( ! empty($search_box_style['background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['background'][0] == '#' ? $search_box_style['background'] : '#'.$search_box_style['background']).';';
            }
            $sbb_style .= 'opacity:'.$search_box_style['back_opacity'].';';
            if( ! empty($title) ) { ?><h3 class="widget-title berocket_aapf_widget-title" style="<?php echo ( empty($uo['style']['title']) ? '' : $uo['style']['title'] ) ?>"><span><?php echo $title; ?></span></h3><?php }
            echo '<div data-url="'.$search_box_url.'" class="'.implode(' ', $search_box_main_class).'"'.(empty($filters['search_box_recount']) ? '' : ' data-id="'.$filters['group_id'].'"').'>';
            echo '<div class="berocket_search_box_background" style="'.$sbb_style.'"></div>';
            echo '<div class="berocket_search_box_background_all">';
            $custom_vars['sb_style'] = $sb_style;
            $custom_vars['search_box_button_class'] = $search_box_button_class;
            $custom_vars['search_box_link_type'] = $search_box_link_type;
            $custom_vars['search_box_url'] = $search_box_url;
            $custom_vars['search_box_style'] = $search_box_style;
            $custom_vars['search_box_category'] = $search_box_category;
        }
        return $custom_vars;
    }
    function search_box_after_group_end($custom_vars, $filters) {
        extract($custom_vars);
        if( ! empty($filters['search_box']) ) {
            echo '</div></div>';
        }
        return $custom_vars;
    }
    function search_box_before_group_filter($custom_vars, $filters) {
        extract($custom_vars);
        if( ! empty($filters['search_box']) ) {
            echo '<div style="'.$sb_style.'">';
        }
        return $custom_vars;
    }
    function search_box_after_group_filter($custom_vars, $filters) {
        if( ! empty($filters['search_box']) ) {
            echo '</div>';
        }
        return $custom_vars;
    }
    function search_box_loaded_footer() {
        if( ! empty($_GET['bapf_gid']) ) {
            $group_id = intval($_GET['bapf_gid']);
            add_filter('braapf_check_widget_by_instance_group', array($this, 'search_box_loaded_footer_condition'), 10, 1);
            echo do_shortcode('[br_filters_group group_id='.$group_id.']');
            remove_filter('braapf_check_widget_by_instance_group', array($this, 'search_box_loaded_footer_condition'), 10, 1);
        }
    }
    function search_box_loaded_footer_condition() {
        return false;
    }
    //GROUP INLINE
    function group_new_args($new_args, $filters) {
        $title_class = array();
        $additional_class = array();
	    if( empty($new_args['additional_class']) || ! is_array($new_args['additional_class']) ) {
		    $new_args['additional_class'] = array();
	    }

        if( ! empty($filters['hide_group']['mobile']) ) {
            $additional_class[] = 'bapf_sngl_hd_mobile';
            $title_class[] = 'bapf_sngl_hd_mobile';
        }
        if( ! empty($filters['hide_group']['tablet']) ) {
            $additional_class[] = 'bapf_sngl_hd_tablet';
            $title_class[] = 'bapf_sngl_hd_tablet';
        }
        if( ! empty($filters['hide_group']['desktop']) ) {
            $additional_class[] = 'bapf_sngl_hd_desktop';
            $title_class[] = 'bapf_sngl_hd_desktop';
        }
        $style = '';
        if( ! empty($filters['hidden_clickable']) ) {
            $additional_class[] = 'berocket_hidden_clickable';
            if( ! empty($filters['display_inline']) ) {
                $additional_class[] = 'berocket_inline_clickable';
            }
            if( ! empty($filters['hidden_clickable_hover']) ) {
                $additional_class[] = 'berocket_inline_clickable_hover';
            }
            $new_args['filter_data'] = array(
                'widget_is_hide' => 1,
                'widget_collapse' => 'with_arrow',
                'additional_data_options' => array(
                    'widget_is_hide_on_load' => true,
                ),
                'widget_collapse_enable' => 1 //DEPRECATED
            );
	        $additional_class[] = 'theme-' . ( empty( $filters['title_only_theme'] ) ? 'default' : $filters['title_only_theme'] );

	        $new_args['additional_class'] = array_merge($new_args['additional_class'], $additional_class);
        }
        if( ! empty($filters['display_inline']) && ( empty($filters['hidden_clickable']) || ! empty($filters['display_inline_count']) ) ) {
            $additional_class[] = 'berocket_inline_filters';
            if( ! empty($filters['display_inline_count']) ) {
                $additional_class[] = 'berocket_inline_filters_count_'.$filters['display_inline_count'];
            }
            $style .= 'opacity:0!important;';
        }
        if( empty($new_args['inline_style']) ) {
            $new_args['inline_style'] = '';
        }
        $new_args['inline_style'] .= $style;

        if( ! empty($filters['min_filter_width_inline']) ) {
            $min_filter_width_inline = max(25, intval($filters['min_filter_width_inline']));
            $new_args['additional_data_inline'] = br_get_value_from_array($new_args, 'additional_data_inline') . ' data-min_filter_width_inline='.$min_filter_width_inline.'';
        }
        $new_args['additional_class'] = array_merge($new_args['additional_class'], $additional_class);
        $new_args['title_class'] = $title_class;
        return $new_args;
    }
    function group_new_args_filter($new_args, $filters, $filter) {
        if( ! empty($filters['hidden_clickable']) && $widget_inline_width = br_get_value_from_array($filters, array('filters_data', $filter, 'width')) ) {
            if(strpos($widget_inline_width, 'px') === FALSE
            && strpos($widget_inline_width, '%') === FALSE
            && strpos($widget_inline_width, 'em') === FALSE ) {
                $widget_inline_width = $widget_inline_width.'px';
            }
            $new_args['widget_inline_style'] = "width:{$widget_inline_width}!important;";
        } else {
            $new_args['widget_inline_style'] = "";
        }
        return $new_args;
    }
    //FILTER
    function filter_add() {
        add_filter( 'berocket_filter_filter_type_array', array($this, 'filter_filter_type_array') );
        add_filter( 'berocket_filter_filter_type_array', array($this, 'filter_filter_type_array_custom_post'), 20000 );
        add_filter( 'berocket_admin_filter_types_by_attr', array($this, 'admin_filter_types_by_attr'), 10, 2 );
        add_filter( 'berocket_widget_widget_type_array', array($this, 'widget_widget_type_array') );
        add_filter( 'berocket_custom_post_br_product_filter_default_settings', array($this, 'single_filter_default_settings') );
        add_filter( 'berocket_widget_attribute_type_terms', array($this, 'widget_attribute_type_terms'), 10, 4 );
        add_filter( 'berocket_aapf_widget_display_custom_filter', array($this, 'widget_display_custom_filter'), 10, 5 );
        //INCLUDE/EXCLUDE LIST
        add_filter( 'berocket_aapf_widget_include_exclude_items', array($this, 'hook_include_exclude_items'), 10, 2 );
        add_filter( 'berocket_aapf_get_terms_args', array($this, 'hook_include_exclude_items_args'), 10, 2 );
        add_filter( 'berocket_aapf_get_terms_args', array($this, 'child_parent_newterms'), 5, 2 );
        add_filter( 'berocket_aapf_get_terms_args', array($this, 'display_child_of'), 7, 2 );
        add_filter( 'berocket_aapf_widget_include_exclude_items', array($this, 'child_parent_newterms_exclude'), 5, 5 );
    }
    function filter_filter_type_array($filter_type) {
        $filter_type['price']['templates'][] = 'checkbox';
        $filter_type['price']['templates'][] = 'select';
        $filter_type['price']['positions'][] = '30000';
        $filter_type['price']['positions'][] = '40000';
        
        $filter_type['attribute']['templates'][] = 'new_slider';
        $filter_type['attribute']['templates'][] = 'slider';
        $filter_type['attribute']['templates'][] = 'datepicker';
        $filter_type['attribute']['positions'][] = '30000';
        $filter_type['attribute']['positions'][] = '40000';
        $filter_type['attribute']['positions'][] = '50000';
        
        $filter_type['tag']['templates'][] = 'new_slider';
        $filter_type['tag']['templates'][] = 'slider';
        $filter_type['tag']['templates'][] = 'datepicker';
        $filter_type['tag']['positions'][] = '30000';
        $filter_type['tag']['positions'][] = '40000';
        $filter_type['tag']['positions'][] = '50000';
        
        $filter_type['all_product_cat']['templates'][] = 'new_slider';
        $filter_type['all_product_cat']['templates'][] = 'slider';
        $filter_type['all_product_cat']['templates'][] = 'datepicker';
        $filter_type['all_product_cat']['positions'][] = '30000';
        $filter_type['all_product_cat']['positions'][] = '40000';
        $filter_type['all_product_cat']['positions'][] = '50000';
        if ( function_exists('wc_get_product_visibility_term_ids') ) {
            $filter_type['_rating']['templates'][] = 'new_slider';
            $filter_type['_rating']['templates'][] = 'slider';
            $filter_type['_rating']['positions'][] = '30000';
            $filter_type['_rating']['positions'][] = '40000';
        }
        $filter_type = berocket_insert_to_array(
            $filter_type,
            'tag',
            array(
                'custom_taxonomy' => array(
                    'name' => __('Custom Taxonomy', 'BeRocket_AJAX_domain'),
                    'sameas' => 'custom_taxonomy',
                    'templates' => array('checkbox', 'slider', 'new_slider', 'select', 'datepicker'),
                    'specific'  => array('', 'color', 'image')
                ),
                '_stock_status' => array(
                    'name' => __('Stock status', 'BeRocket_AJAX_domain'),
                    'sameas' => '_stock_status',
                    'templates' => array('checkbox', 'select'),
                    'specific'  => array('')
                ),
                'date' => array(
                    'name' => __('Date', 'BeRocket_AJAX_domain'),
                    'sameas' => 'date',
                    'templates' => array('datepicker'),
                    'specific'  => array(''),
                    'slug'      => '_date',
                ),
                '_sale' => array(
                    'name' => __('Sale', 'BeRocket_AJAX_domain'),
                    'sameas' => '_sale',
                    'templates' => array('checkbox', 'select'),
                    'specific'  => array('')
                ),
            )
        );
        return $filter_type;
    }
    function filter_filter_type_array_custom_post($filter_type) {
        if( ! empty($filter_type['custom_postmeta']) ) {
            $filter_type['custom_postmeta']['templates'][] = 'new_slider';
            $filter_type['custom_postmeta']['templates'][] = 'slider';
            $filter_type['custom_postmeta']['templates'][] = 'datepicker';
        }
        return $filter_type;
    }
    function admin_filter_types_by_attr($vars, $type = 'main') {
        list($berocket_admin_filter_types, $berocket_admin_filter_types_by_attr) = $vars;
        if( $type != 'simple' ) {
            $berocket_admin_filter_types_by_attr['ranges'] = array('value' => 'ranges', 'text' => __('Ranges', 'BeRocket_AJAX_domain'));
            $berocket_admin_filter_types['price'][] = "ranges";
        }
        $berocket_admin_filter_types['custom_taxonomy'][] = "slider";
        $berocket_admin_filter_types['attribute'][] = "slider";
        $berocket_admin_filter_types['filter_by'][] = "slider";
        return array($berocket_admin_filter_types, $berocket_admin_filter_types_by_attr);
    }
    function widget_widget_type_array($widget_types) {
        $widget_types['search_box'] = __('Search Box (DEPRECATED)', 'BeRocket_AJAX_domain');
        return $widget_types;
    }
    function single_filter_default_settings($default_settings) {
        $default_settings = array_merge(
            $default_settings,
            array(
                'child_parent'                  => '',
                'child_parent_depth'            => '1',
                'child_parent_no_values'        => '',
                'child_parent_previous'         => '',
                'child_parent_no_products'      => '',
                'child_onew_count'              => '1',
                'child_onew_childs'             => array(
                    1                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    2                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    3                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    4                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    5                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    6                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    7                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    8                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    9                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    10                              => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                ),
                'search_box_link_type'          => 'shop_page',
                'search_box_url'                => '',
                'search_box_category'           => '',
                'search_box_count'              => '1',
                'search_box_attributes'             => array(
                    1                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    2                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    3                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    4                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    5                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    6                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    7                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    8                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    9                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    10                              => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                ),
                'search_box_style'              => array(
                    'position'                      => 'vertical',
                    'search_position'               => 'after',
                    'search_text'                   => 'Search',
                    'background'                    => 'bbbbff',
                    'back_opacity'                  => '0',
                    'button_background'             => '888800',
                    'button_background_over'        => 'aaaa00',
                    'text_color'                    => '000000',
                    'text_color_over'               => '000000',
                ),
                'ranges'                        => array( 1, 10 ),
                'include_exclude_select'        => '',
                'include_exclude_list'          => array(),
            )
        );
        
        return $default_settings;
    }
    public static function get_page_text() {
        global $wp_query;
        $text = '';
        $object_id = $wp_query->get_queried_object_id();
        if( $object_id == 0 ) {
            if( is_shop() ) {
                $text = 'shop';
            } elseif( is_home() ) {
                $text = 'home';
            } else {
                $text = 'other';
            }
        } else {
            if ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax ) {
                $text = 'taxonomy' . $object_id;
            } elseif( $wp_query->is_post_type_archive ) {
                $text = 'archive' . $object_id;
            } elseif( $wp_query->is_posts_page || ($wp_query->is_singular && ! empty( $wp_query->post )) ) {
                $text = 'post' . $object_id;
            } else {
                $text = 'other' . $object_id;
            }
        }
        return $text;
    }
    function widget_attribute_type_terms($vars, $attr_type, $attr_filter_type, $instance) {
        extract($instance);

        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options    = $BeRocket_AAPF->get_option();

        list( $terms_error_return, $terms_ready, $terms, $type ) = $vars;

        if ( $attr_filter_type == 'attribute' ) {
            if ( $attr_type == '_stock_status' ) {
                $recount_filter_first = apply_filters( 'brapf_paid_recount_filter_first', braapf_filters_must_be_recounted('first'), $instance );
                $recount_filter       = apply_filters( 'brapf_paid_recount_filter', braapf_filters_must_be_recounted(), $instance );
                $terms_ready = true;
                $terms       = array();
                array_push( $terms, (object) array( 'term_id'           => '1',
                                                    'term_taxonomy_id'  => '1',
                                                    'name'              => __( 'In stock', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'instock',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '1' : 'instock' ),
                                                    'taxonomy'          => '_stock_status',
                                                    'count'             => 1
                ) );
                array_push( $terms, (object) array( 'term_id'           => '2',
                                                    'term_taxonomy_id'  => '2',
                                                    'name'              => __( 'Out of stock', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'outofstock',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '2' : 'outofstock' ),
                                                    'taxonomy'          => '_stock_status',
                                                    'count'             => 1
                ) );

                $terms = BeRocket_AAPF_Widget_functions::get_attribute_values(
                    $attr_type,
                    'id',
                    $recount_filter_first,
                    $recount_filter,
                    $terms,
                    ( isset( $cat_value_limit ) ? $cat_value_limit : null ),
                    $operator
                );
            } elseif ( $attr_type == '_sale' ) {
                $recount_filter_first = apply_filters( 'brapf_paid_recount_filter_first', braapf_filters_must_be_recounted('first'), $instance );
                $recount_filter       = apply_filters( 'brapf_paid_recount_filter', braapf_filters_must_be_recounted(), $instance );
                $terms_ready = true;
                $terms       = array();
                array_push( $terms, (object) array( 'term_id'           => '1',
                                                    'term_taxonomy_id'  => '1',
                                                    'name'              => __( 'On sale', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'sale',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '1' : 'sale' ),
                                                    'taxonomy'          => '_sale',
                                                    'count'             => 1
                ) );
                array_push( $terms, (object) array( 'term_id'           => '2',
                                                    'term_taxonomy_id'  => '2',
                                                    'name'              => __( 'Not on sale', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'notsale',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '2' : 'notsale' ),
                                                    'taxonomy'          => '_sale',
                                                    'count'             => 1
                ) );
                $terms = BeRocket_AAPF_Widget_functions::get_attribute_values(
                    $attr_type,
                    'id',
                    $recount_filter_first,
                    $recount_filter,
                    $terms,
                    ( isset( $cat_value_limit ) ? $cat_value_limit : null ),
                    $operator
                );
            }
            if( $attr_type == '_stock_status' || $attr_type == '_sale' ) {
                if( ! empty($include_exclude_select) && isset($include_exclude_list) && is_array($include_exclude_list) ) {
                    $correct_terms = array();
                    foreach($terms as $term) {
                        if( ($include_exclude_select == 'include' && in_array($term->term_id, $include_exclude_list))
                        || ($include_exclude_select == 'exclude' && ! in_array($term->term_id, $include_exclude_list)) ) {
                            $correct_terms[] = $term;
                        }
                    }
                    $terms = $correct_terms;
                }
                $terms = apply_filters('bapf_paid_stock_sale_terms_ready', $terms, $instance);
            }
        } elseif( $attr_filter_type == 'date' ) {
            $terms_ready = true;
            $type        = 'date';
        }

        return array( $terms_error_return, $terms_ready, $terms, $type );
    }
    function wc_get_product_ids_not_on_sale() {
        global $wpdb;

        // Load from cache
        $product_ids_not_on_sale = get_transient( 'wc_products_notonsale' );

        // Valid cache found
        if ( false !== $product_ids_not_on_sale ) {
            return $product_ids_not_on_sale;
        }
        delete_transient( 'wc_products_onsale' );
        $product_ids_on_sale = wc_get_product_ids_on_sale();
        $product_ids_on_sale[] = -1;

        $query_sql = "
            SELECT post.ID, post.post_parent FROM `$wpdb->posts` AS post
            LEFT JOIN `$wpdb->postmeta` AS meta ON post.ID = meta.post_id
            WHERE post.post_type IN ( 'product', 'product_variation' )
                AND post.post_status = 'publish'
                AND meta.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'paid_1900')."'
                AND post.ID NOT IN (".implode(',', $product_ids_on_sale).")
                AND post.post_parent NOT IN (".implode(',', $product_ids_on_sale).")
            GROUP BY post.ID;
        ";

        do_action('brapf_before_query', 'wc_get_product_ids_not_on_sale', $query_sql);
        $on_sale_posts = $wpdb->get_results( $query_sql );
        do_action('brapf_after_query', 'wc_get_product_ids_not_on_sale', $query_sql);

        $product_ids_not_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

        set_transient( 'wc_products_notonsale', $product_ids_not_on_sale, DAY_IN_SECONDS * 30 );

        return $product_ids_not_on_sale;
    }
    function widget_display_custom_filter($return, $widget_type, $instance, $args, $widget_instance) {
        if ( $widget_type == 'search_box' ) {
            extract($instance);
            extract($args);
            if( $search_box_link_type == 'shop_page' ) {
                if( function_exists('wc_get_page_id') ) {
                    $search_box_url = get_permalink( wc_get_page_id( 'shop' ) );
                } else {
                    $search_box_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
                }
            } elseif( $search_box_link_type == 'category' ) {
                $search_box_url = get_term_link( $search_box_category, 'product_cat' );
            }
            $sb_style = '';
            if ( $search_box_style['position'] == 'horizontal' ) {
                $sb_count = $search_box_count;
                if( $search_box_style['search_position'] == 'before_after' ) {
                    $sb_count += 2;
                } else {
                    $sb_count++;
                }
                $search_box_width = (int)(100 / $sb_count);
                $sb_style .= 'width:'.$search_box_width.'%;display:inline-block;padding: 4px;';
            }
            echo $before_widget;
            $search_box_button_class = 'search_box_button_class_'.rand();
            $sbb_style = '';
            if( ! empty($search_box_style['background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['background'][0] == '#' ? $search_box_style['background'] : '#'.$search_box_style['background']).';';
            }
            $sbb_style .= 'opacity:'.$search_box_style['back_opacity'].';';
            if( ! empty($title) ) { ?><h3 class="widget-title berocket_aapf_widget-title" style="<?php echo ( empty($uo['style']['title']) ? '' : $uo['style']['title'] ) ?>"><span><?php echo $title; ?></span></h3><?php }
            echo '<div class="berocket_search_box_block">';
            echo '<div class="berocket_search_box_background" style="'.$sbb_style.'"></div>';
            echo '<div class="berocket_search_box_background_all">';
            $sbb_style = '';
            if( ! empty($search_box_style['button_background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['button_background'][0] == '#' ? $search_box_style['button_background'] : '#'.$search_box_style['button_background']).';';
            }
            if( ! empty($search_box_style['text_color']) ) {
                $sbb_style .= 'color:'.($search_box_style['text_color'][0] == '#' ? $search_box_style['text_color'] : '#'.$search_box_style['text_color']).';';
            }
            if( ! empty($search_box_style['button_background_over']) ) {
                $sbb_style_hover = 'background-color:'.($search_box_style['button_background_over'][0] == '#' ? $search_box_style['button_background_over'] : '#'.$search_box_style['button_background_over']).';';
            }
            if( ! empty($search_box_style['text_color_over']) ) {
                $sbb_style_hover .= 'color:'.($search_box_style['text_color_over'][0] == '#' ? $search_box_style['text_color_over'] : '#'.$search_box_style['text_color_over']).';';
            }
            if ( $search_box_style['search_position'] == 'before' || $search_box_style['search_position'] == 'before_after' ) {
                echo '<div style="'.$sb_style.'"><a data-url="'.$search_box_url.'" class="'.$search_box_button_class.' berocket_search_box_button">'.$search_box_style['search_text'].'</a></div>';
            }
            for($i = 1; $i <= $search_box_count; $i++) {
                echo '<div style="'.$sb_style.'">';
                $current_box = $search_box_attributes[$i];
                $BeRocket_AAPF_single_filter = BeRocket_AAPF_single_filter::getInstance();
                $search_instance = $BeRocket_AAPF_single_filter->default_settings;
                $search_instance['filter_type'] = ( empty($current_box['type']) ? '' : $current_box['type'] );
                $search_instance['attribute'] = ( empty($current_box['attribute']) ? '' : $current_box['attribute'] );
                $search_instance['custom_taxonomy'] = ( empty($current_box['custom_taxonomy']) ? '' : $current_box['custom_taxonomy'] );
                $search_instance['type'] = ( empty($current_box['visual_type']) ? '' : $current_box['visual_type'] );
                $search_instance['height'] = ( empty($current_box['height']) ? '' : $current_box['height'] );
                $search_instance['scroll_theme'] = ( empty($current_box['scroll_theme']) ? '' : $current_box['scroll_theme'] );
                $search_instance['selected_area_show'] = ( empty($current_box['selected_area_show']) ? '' : $current_box['selected_area_show'] );
                $search_instance['hide_selected_arrow'] = ( empty($current_box['hide_selected_arrow']) ? '' : $current_box['hide_selected_arrow'] );
                $search_instance['selected_is_hide'] = ( empty($current_box['selected_is_hide']) ? '' : $current_box['selected_is_hide'] );
                $search_instance['is_hide_mobile'] = ( empty($current_box['is_hide_mobile']) ? '' : $current_box['is_hide_mobile'] );
                $search_instance['cat_propagation'] = ( empty($current_box['cat_propagation']) ? '' : $current_box['cat_propagation'] );
                $search_instance['cat_propagation'] = ( empty($current_box['cat_propagation']) ? '' : $current_box['cat_propagation'] );
                $search_instance['product_cat'] = ( empty($current_box['product_cat']) ? '' : $current_box['product_cat'] );
                $search_instance['show_page'] = ( empty($current_box['show_page']) ? '' : $current_box['show_page'] );
                $search_instance['cat_value_limit'] = ( empty($current_box['cat_value_limit']) ? '' : $current_box['cat_value_limit'] );
                $search_instance['widget_id'] = $widget_instance->id;
                $search_instance['widget_id_number'] = $widget_instance->number;

                $widget_search = new BeRocket_AAPF_Widget($search_instance, array('before_widget' => '<h4>'.$current_box['title'].'</h4>', 'after_widget' =>''));
                echo '</div>';
            }
            if ( $search_box_style['search_position'] == 'after' || $search_box_style['search_position'] == 'before_after' ) {
                echo '<div style="'.$sb_style.'">
                <a data-url="'.$search_box_url.'" 
                class="'.$search_box_button_class.' berocket_search_box_button">
                '.$search_box_style['search_text'].'</a></div>';
            }
            echo '</div></div>';
            echo '<style>.'.$search_box_button_class.'{'.$sbb_style.'}.'.$search_box_button_class.':hover{'.$sbb_style_hover.'}</style>';
            echo $after_widget;
            $return = true;
        }
        if( !( $instance['filter_type'] == 'attribute'
        && ( $instance['attribute'] == 'price' || $instance['attribute'] == 'product_cat' ) )
        || $instance['filter_type'] == 'product_cat'
        || $instance['filter_type'] == '_stock_status'
        || $instance['filter_type'] == 'tag'
        || $instance['type'] == 'slider' ) {
            if( ! empty($instance['child_parent']) && $instance['child_parent'] == 'depth' ) {
                global $bapf_unique_id;
                $bapf_unique_id_start = $bapf_unique_id;
                $bapf_unique_id = $bapf_unique_id_start + 1000;
                $count = ( empty($instance['child_onew_count']) ? '' : $instance['child_onew_count'] );
                $title = ( empty($instance['title']) ? '' : $instance['title'] );
                $instance['child_parent'] = 'parent';
                $childs = ( empty($instance['child_onew_childs']) ? '' : $instance['child_onew_childs'] );
                
                $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget($instance, $args);
                $instance['child_parent'] = 'child';
                for( $i = 1; $i <= $count; $i++ ) {
                    $bapf_unique_id = $bapf_unique_id_start + ($i + 1) * 1000;
                    $child = $childs[$i];
                    $new_args = $args;
                    $instance['child_parent_depth'] = $i;
                    $instance['title'] = $childs[$i]['title'];
                    $instance['filter_title'] = $childs[$i]['title'];
                    $instance['child_parent_no_values'] = ( empty($childs[$i]['no_values']) ? '' : $childs[$i]['no_values'] );
                    $instance['child_parent_previous'] = ( empty($childs[$i]['previous']) ? '' : $childs[$i]['previous'] );
                    $instance['child_parent_no_products'] = ( empty($childs[$i]['no_product']) ? '' : $childs[$i]['no_product'] );
                    $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget($instance, $new_args);
                }
                $bapf_unique_id = $bapf_unique_id_start;
                $return = true;
            }
        }
        return $return;
    }
    function display_child_of($get_terms_args, $instance) {
        $parent_value_get = 'parent_'.berocket_isset($get_terms_args['taxonomy']);
        if( ! empty($instance[$parent_value_get]) ) {
            $parent_value = $instance[$parent_value_get];
            if( $parent_value == 'bapf4current' ) {
				global $berocket_parse_page_obj;
                $cate = get_queried_object();
                if( empty($cate) || ! isset($cate->term_id) ) {
					$cate = $berocket_parse_page_obj->queried_object;
				}
                if( ! empty($cate) && isset($cate->term_id) && $cate->taxonomy == $get_terms_args['taxonomy'] ) {
                    $cateID = $cate->term_id;
                } else {
                    $cateID = 0;
                }
                $parent_value = $cateID;
            } elseif( $parent_value == 'bapf1level' ) {
                $parent_value = 0;
            }
            $get_terms_args['child_of'] = intval($parent_value);
            add_filter( 'berocket_aapf_get_terms_additional', array($this, 'display_child_of_advanced'), 7, 2 );
        }
        return $get_terms_args;
    }
    function display_child_of_advanced($get_terms_advanced, $instance) {
        remove_filter( 'berocket_aapf_get_terms_additional', array($this, 'display_child_of_advanced'), 7, 2 );
        $get_terms_advanced['depth'] = intval(berocket_isset($instance['depth_count']));
        return $get_terms_advanced;
    }
    function child_parent_newterms($get_terms_args, $instance) {
        if( ! empty($instance['child_parent']) ) {
            $get_terms_args['hierarchical'] = true;
        }
        if( ! empty($instance['child_parent']) && $instance['child_parent'] == 'parent' ) {
            $get_terms_args['parent'] = 0;
        }
        return $get_terms_args;
    }
    function child_parent_newterms_exclude($terms, $instance, $get_terms_args = false, $get_terms_advanced = false, $set_query_var_title = array()) {
        if( $get_terms_args === false || $get_terms_advanced === false ) {
            return $terms;
        }
        if( ! empty($instance['child_parent']) && $instance['child_parent'] == 'child' ) {
            if( ! empty($get_terms_args['taxonomy']) && is_array($get_terms_args['taxonomy']) && count($get_terms_args['taxonomy']) == 1 ) {
                $get_terms_args['taxonomy'] = array_pop($get_terms_args['taxonomy']);
            }
            $taxonomy_object = get_taxonomy($get_terms_args['taxonomy']);
            if( empty($taxonomy_object->hierarchical) ) {
                return array();
            }
            $child_parent_depth = max(intval(berocket_isset($instance['child_parent_depth'])), 1);
            $current_terms = $selected_terms_id = array();
            $selected_terms = br_get_selected_term( $get_terms_args['taxonomy'] );
            foreach( $selected_terms as $selected_term ) {
                $ancestors = get_ancestors( $selected_term, $get_terms_args['taxonomy'] );
                if( count( $ancestors ) >= ( $child_parent_depth - 1 ) ) {
                    if( count( $ancestors ) > ( $child_parent_depth - 1 ) ) {
                        $selected_term = $ancestors[count( $ancestors ) - ( $child_parent_depth )];
                    }
                    if ( ! in_array( $selected_term, $selected_terms_id ) ) {
                        $args_terms = array(
                            'orderby'    => 'id',
                            'order'      => 'ASC',
                            'hide_empty' => false,
                            'parent'     => $selected_term,
                            'fields'     => 'ids'
                        );
                        $selected_terms_id[] = $selected_term;
                        $additional_terms = get_terms( $get_terms_args['taxonomy'], $args_terms );
                        $current_terms = array_merge( $current_terms, $additional_terms );
                    }
                }
            }
            if( empty($set_query_var_title['new_template']) ) {
                $newterms = array( (object) array( 'depth' => 0, 'child' => 0, 'term_id' => 'R__term_id__R', 'count' => 'R__count__R', 'slug' => 'R__slug__R', 'name' => 'R__name__R', 'taxonomy' => 'R__taxonomy__R' ) );
            } else {
                $newterms = array();
            }
            foreach($terms as $i => $term) {
                if( in_array($term->term_id, $current_terms) ) {
                    $newterms[] = $term;
                }
            }
            $terms = $newterms;
        }
        return $terms;
    }
    function hook_include_exclude_items_args($args, $instance) {
        remove_filter( 'berocket_aapf_widget_include_exclude_items', array($this, 'hook_include_exclude_items'), 10, 2 );
        $include_exclude_select = br_get_value_from_array($instance, 'include_exclude_select');
        $include_exclude_list = br_get_value_from_array($instance, 'include_exclude_list');
        if( ! empty($include_exclude_select) ) {
            if( $include_exclude_select == 'include' ) {
                $args['include'] = $include_exclude_list;
            } elseif($include_exclude_select == 'exclude') {
                $args['exclude'] = $include_exclude_list;
            }
        }
        return $args;
    }
    function hook_include_exclude_items($terms, $instance) {
        $include_exclude_select = br_get_value_from_array($instance, 'include_exclude_select');
        $include_exclude_list = br_get_value_from_array($instance, 'include_exclude_list');
        $terms = $this->include_exclude_items($terms, $include_exclude_select, $include_exclude_list);
        return $terms;
    }

    function include_exclude_items($terms, $include_exclude_select, $include_exclude_list) {
        if ( isset($terms) && is_array($terms) && count( $terms ) > 0 ) {
            if( $include_exclude_select == 'include' ) {
                $new_terms = array();
                foreach($terms as $term) {
                    if( in_array($term->term_id, $include_exclude_list) ) {
                        $new_terms[] = $term;
                    }
                }
                $terms = $new_terms;
            } elseif( $include_exclude_select == 'exclude' ) {
                $new_terms = array();
                foreach($terms as $term) {
                    if( ! in_array($term->term_id, $include_exclude_list) ) {
                        $new_terms[] = $term;
                    }
                }
                $terms = $new_terms;
            }
        }
        return $terms;
    }
    public function get_permalinks_oprions() {
        $option_permalink = get_option( 'berocket_permalink_option' );
        if( ! is_array($option_permalink) ) {
            $option_permalink = array();
        }
        $option_permalink = array_merge($this->default_permalink, $option_permalink);
        return $option_permalink;
    }
    public function get_nn_permalinks_oprions() {
        $option_permalink = get_option( 'berocket_nn_permalink_option' );
        if( ! is_array($option_permalink) ) {
            $option_permalink = array();
        }
        $option_permalink = array_merge($this->default_nn_permalink, $option_permalink);
        return $option_permalink;
    }
    public function register_permalink_option() {
        $screen = get_current_screen();
        $default_values = $this->default_permalink;
        if($screen->id == 'options-permalink') {
            $this->save_permalink_option($default_values);
            $this->_register_permalink_option($default_values);
        }
        if(strpos($screen->id, 'widgets') !== FALSE || strpos($screen->id, 'br-product-filters') !== FALSE) {
            $this->register_admin_scripts();
        }
    }
    public function register_admin_scripts(){
        wp_enqueue_script( 'brjsf-ui');
        wp_enqueue_style( 'brjsf-ui' );
        wp_enqueue_style( 'font-awesome' );
    }
    public function _register_permalink_option($default_values) {
        $permalink_option = 'berocket_permalink_option';
        $option_values = get_option( $permalink_option );
        $data = shortcode_atts( $default_values, $option_values );
        update_option($permalink_option, $data);

        $permalink_option = 'berocket_nn_permalink_option';
        $option_values = get_option( $permalink_option );
        $data = shortcode_atts( $this->default_nn_permalink, $option_values );
        update_option($permalink_option, $data);
        
        add_settings_section(
            'berocket_permalinks',
            'BeRocket AJAX Filters',
            'br_permalink_input_section_echo',
            'permalink'
        );
    }
    public function save_permalink_option( $default_values ) {
        if ( isset( $_POST['berocket_permalink_option'] ) ) {
            $option_values    = $_POST['berocket_permalink_option'];
            $data             = shortcode_atts( $default_values, $option_values );
            $data['variable'] = $data['variable'];
            if( empty($data['variable']) ) {
                $data['variable'] = $default_values['variable'];
            }

            update_option( 'berocket_permalink_option', $data );
        }
        if ( isset( $_POST['berocket_nn_permalink_option'] ) ) {
            $option_values    = $_POST['berocket_nn_permalink_option'];
            $data             = shortcode_atts( $this->default_nn_permalink, $option_values );
            if ( empty($data['variable']) ) {
                $data['variable'] = $default_values['variable'];
            }

            update_option( 'berocket_nn_permalink_option', $data );
        }
    }
    //SLIDER ATTRIBUTES
    public function attribute_price_var_title($set_query_var_title, $type, $instance, $args = false, $terms = false) {
        if( $args === false || $terms === false ) return $set_query_var_title;
        extract($instance);
        extract($args);
        $slider_with_string = false;
        $stringed_is_numeric = true;
        $slider_step = 1;
        if ( (($filter_type == 'attribute' && $attribute != 'price') || $filter_type == 'custom_taxonomy') && $type == 'slider' ) {
            if( $filter_type == 'custom_taxonomy' ) {
                $attribute = $custom_taxonomy;
            }
            $min = $max   = false;
            $main_class   = 'slider';
            $slider_class = 'berocket_filter_slider';

            if ( $attribute == 'price' && $type != 'slider' ){
                if ( ! empty($price_values) ) {
                    $price_range = explode( ",", $price_values );
                } elseif( $use_min_price && $use_max_price ) {
                    $price_range = array($min_price, $max_price);
                }
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['price_range'] = berocket_isset($price_range);
                }
                wp_localize_script(
                    'berocket_aapf_widget-script',
                    'br_price_text',
                    array(
                        'before'  => (isset($text_before_price) ? $text_before_price : ''),
                        'after'   => (isset($text_after_price) ? $text_after_price : ''),
                    )
                );
                if ( ! empty($price_values) ) {
                    $all_terms_name = $price_range;
                    $all_terms_slug = $price_range;
                    $stringed_is_numeric = true;
                    $min = 0;
                    $max = count( $all_terms_name ) - 1;
                    $slider_with_string = true;
                } else {
                    if( ! empty($price_range) ) {
                        foreach ( $price_range as $price ) {
                            if ( $min === false or $min > (int) $price ) {
                                $min = $price;
                            }
                            if ( $max === false or $max < (int) $price ) {
                                $max = $price;
                            }
                        }
                    }
                    if( $use_min_price ) {
                        $min = $min_price;
                    }
                    if ( $use_max_price ) {
                        $max = $max_price;
                    }
                }
                $id = 'br_price';
                $slider_class .= ' berocket_filter_price_slider';
                $main_class .= ' price';

                $min = floor( $min );
                $max = ceil( $max );
                $slider_value1 = $min;
                $slider_value2 = $max;
            } else {
                if( ! empty($slider_numeric) ) {
                    $slider_with_string = $stringed_is_numeric = false;
                    
                    $min = $max = false;
                    foreach ( $terms as $term ) {
                        $id = $term->taxonomy;
                        $name_num = floatval($term->name);
                        if( $min === false || $name_num < $min ) {
                            $min = $name_num;
                        }
                        if( $max === false || $name_num > $max ) {
                            $max = $name_num;
                        }
                    }
                    $max = intval(ceil($max));
                    $min = intval(floor($min));
                    $slider_value1 = $min;
                    $slider_value2 = $max;
                } else {
                    $all_terms_name = $all_terms_slug = array();
                    foreach ( $terms as $term ) {
                        $id = $term->taxonomy;
                        array_push( $all_terms_name, urldecode($term->slug) );
                        array_push( $all_terms_slug, $term->name );
                    }
                    $min = 0;
                    $max = count($all_terms_name) - 1;
                    $slider_with_string = true;
                    $slider_value1 = $min;
                    $slider_value2 = $max;
                }
            }
            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['value_1'] = $slider_value1;
                $widget_error_log['value_2'] = $slider_value2;
                $widget_error_log['step'] = $slider_step;
            }

            $wpml_id = preg_replace( '#^pa_#', '', $id );
            $wpml_id = 'pa_'.berocket_wpml_attribute_translate($wpml_id);
            $set_query_var_title['slider_value1'] = $slider_value1;
            $set_query_var_title['slider_value2'] = $slider_value2;
            $set_query_var_title['filter_slider_id'] = $wpml_id;
            $set_query_var_title['main_class'] = $main_class;
            $set_query_var_title['slider_class'] = $slider_class;
            $set_query_var_title['min'] = $min;
            $set_query_var_title['max'] = $max;
            $set_query_var_title['step'] = $slider_step;
            $set_query_var_title['slider_with_string'] = $slider_with_string;
            $set_query_var_title['all_terms_name'] = ( empty($all_terms_name) ? null : $all_terms_name );
            $set_query_var_title['all_terms_slug'] = ( empty($all_terms_slug) ? null : $all_terms_slug );
            $set_query_var_title['text_before_price'] = (isset($text_before_price) ? $text_before_price : null);
            $set_query_var_title['text_after_price'] = (isset($text_after_price) ? $text_after_price : null);
            $set_query_var_title['enable_slider_inputs'] = (isset($enable_slider_inputs) ? $enable_slider_inputs : null);
            if( ! empty($number_style) ) {
                $set_query_var_title['number_style'] = array(
                    ( empty($number_style_thousand_separate) ? '' : $number_style_thousand_separate ), 
                    ( empty($number_style_decimal_separate) ? '' : $number_style_decimal_separate ), 
                    ( empty($number_style_decimal_number) ? '' : $number_style_decimal_number )
                );
            } else {
                $set_query_var_title['number_style'] = '';
            }
        }
        return $set_query_var_title;
    }
    public function add_slider_numeric_sorting($get_terms_advanced, $instance) {
        if( berocket_isset($instance['new_template']) == 'datepicker' ) {
            $get_terms_advanced['orderby'] = 'slug_num';
        } elseif ( berocket_isset($instance['type']) == 'slider' && ! empty($instance['slider_numeric']) ) {
            $get_terms_advanced['orderby'] = 'name_numeric_full';
        }
        return $get_terms_advanced;
    }
    //SEO META TITLE
    public function seo_meta_filtered_term_continue($continue, $term_parsed) {
        if( in_array($term_parsed[0], array('_stock_status', 'pa__date', '_date')) ) {
            $continue = true;
        }
        return $continue;
    }
    public function ranges_add_query_var_title( $set_query_var_title, $type, $instance ) {
        if ( $type == 'ranges' ) {
            $set_query_var_title['disable_multiple_ranges'] = ( ! empty( $instance['disable_multiple_ranges'] ) ? true : false );
        }

        return $set_query_var_title;
    }
    public function ranges() {
        add_filter('berocket_query_var_title_before_widget', array($this, 'ranges_add_query_var_title'), 10, 3);
        add_filter('berocket_query_var_title_before_widget_deprecated', array($this, 'ranges_add_query_var_title'), 10, 3);
    }
    //MULTIPLE COLOR TERMS
    public function multiple_color() {
        add_filter('berocket_aapf_color_term_select_line', array($this, 'multiple_color_term_select'), 10, 2);
        add_filter('berocket_aapf_meta_color_values', array($this, 'multiple_color_get'), 5, 3);
    }
    public function multiple_color_term_select($html, $term) {
	    $color_list = array('color', 'color_2', 'color_3', 'color_4');

        $html = '<div class="color_image_element color_image_element_paid element-depth-'.$term->depth.'">';
        $html .= '<div class="br_colorpicker_field_name">'.berocket_isset($term, 'name').'</div>';
        foreach ( $color_list as $color_name ) {
            $color_meta = berocket_term_get_metadata($term, $color_name);
            $html .= '<div class="br_colorpicker_field'
                     . ( ( empty($color_meta) && $color_name != 'color' ) ? ' colorpicker_removed' : '' )
                     . '" data-color="' . br_get_value_from_array($color_meta, 0, 'ffffff') . '">';
            if( $color_name != 'color') {
                $html .= '<a href="#" class="colorpicker_remove">+</a>';
            }
            $html .= '</div>';
            $html .= '<input class="br_colorpicker_field_input" type="hidden" value="'
                     . br_get_value_from_array($color_meta, 0) . '"
                     name="br_widget_color[' . $color_name . '][' . $term->term_id . ']" />';
        }
        $color_meta = berocket_term_get_metadata($term, 'color_gradient');
        $color_meta = br_get_value_from_array($color_meta, 0);
        $html .= '<div class="br_colorpicker_gradient"><label><input type="checkbox" value="1" '. ( empty($color_meta) ? '' : 'checked' )
                   . ' name="br_widget_color[color_gradient][' . $term->term_id . ']" />'
                   .__('Gradient', 'BeRocket_AJAX_domain').'</label></div>';
        $html .= '</div>';
        return $html;
    }
    public function multiple_color_get($meta_color, $term, $variables_for_hooks) {
        if ( !$variables_for_hooks['is_child_parent'] || !$variables_for_hooks['is_first'] ) {
            if( $variables_for_hooks['type'] == 'color' ) {
                $color_list = array('color', 'color_2', 'color_3', 'color_4');
                $meta_color = array();
                foreach($color_list as $color_name) {
                    $berocket_term = berocket_term_get_metadata($term, $color_name);
                    $berocket_term = br_get_value_from_array($berocket_term, 0, '');
                    if( empty($berocket_term) && $color_name != 'color') continue;
                    $meta_color[] = $berocket_term;
                }
            }
        }
        return $meta_color;
    }
    function query_var_title($set_query_var_title, $instance, $br_options) {
        $set_query_var_title['date_style'] = br_get_value_from_array($instance, 'date_style');
        $set_query_var_title['date_change_month'] = ! empty($instance['date_change_month']);
        $set_query_var_title['date_change_year'] = ! empty($instance['date_change_year']);
        $set_query_var_title['enable_slider_inputs'] = ! empty($instance['enable_slider_inputs']);
        $set_query_var_title['slider_numeric'] = ! empty($instance['slider_numeric']);
        return $set_query_var_title;
    }
    //NEW TEMPLATES FUNCTIONS
    //NEW SLIDER ATTRIBUTE
    public function new_slider_vars($set_query_var_title, $type, $instance, $args = false, $terms = false) {
        if( in_array(berocket_isset($set_query_var_title['new_template']),array('slider', 'new_slider')) ) {
            extract($set_query_var_title);
            if( $filter_type != 'attribute' || $attribute != 'price' ) {
                if( ! empty($slider_numeric) ) {
                    $slider_with_string = $stringed_is_numeric = false;
                    
                    $min = $max = false;
                    foreach ( $terms as $term ) {
                        $id = $term->taxonomy;
                        $name_num = floatval($term->name);
                        if( $min === false || $name_num < $min ) {
                            $min = $name_num;
                        }
                        if( $max === false || $name_num > $max ) {
                            $max = $name_num;
                        }
                    }
                    $max = intval(ceil($max));
                    $min = intval(floor($min));
                    if( $max != $min ) {
                        $set_query_var_title['terms'] = array(
                            (object)array(
                                'term_id'  => $min.'_'.$max,
                                'slug'     => $min.'_'.$max,
                                'value'    => $min.'_'.$max,
                                'name'     => $term->taxonomy,
                                'count'    => 1,
                                'taxonomy' => $term->taxonomy,
                                'min'      => $min,
                                'max'      => $max,
                                'step'     => '1',
                            )
                        );
                    } else {
                        $set_query_var_title['terms'] = array();
                    }
                    $set_query_var_title['slider_display_data'] = 'num_attr';
                } else {
                    if( count($terms) == 1 ) {
                        foreach($terms as $term){}
                        $terms[] = $term;
                    }
                    foreach($terms as &$term) {
                        $term->min      = 0;
                        $term->max      = count($terms) - 1;
                        $term->step     = 1;
                        $term->value    = $term->slug;
                    }
                    if( isset($term) ) {
                        unset($term);
                    }
                    
                    $set_query_var_title['slider_display_data'] = 'arr_attr';
                    $set_query_var_title['terms'] = $terms;
                }
            }
        } elseif( in_array(berocket_isset($set_query_var_title['new_template']),array('datepicker')) ) {
            if( $set_query_var_title['filter_type'] == 'date' ) {
                global $wpdb;
                $query = "SELECT post_date FROM {$wpdb->posts} WHERE post_type = 'product' ORDER BY post_date ASC LIMIT 1";
                $query = $wpdb->get_var($query);
                $datetime = strtotime('-30 days');
                if( ! empty($query) ) {
                    $datetime = new DateTime($query);
                    $datetime = $datetime->getTimestamp();
                }
                $min = date('Ymd', $datetime);
                $max = date('Ymd', strtotime('+1 day'));
                $terms = array(
                    (object)array(
                        'term_id'  => $min.'_'.$max,
                        'slug'     => $min.'_'.$max,
                        'value'    => $min.'_'.$max,
                        'name'     => __('Date', 'BeRocket_AJAX_domain'),
                        'count'    => 1,
                        'taxonomy' => '_date',
                        'min'      => $min,
                        'max'      => $max,
                        'step'     => '1',
                    )
                );
            } else {
                $terms = array_values($terms);
                $regexr = '/[\D]*(\d{4})[\D]*(\d{2})[\D]*(\d{2})[\D]*/';
                $start = preg_match($regexr, $terms[0]->slug, $matches_start);
                $end   = preg_match($regexr, $terms[count($terms) - 1]->slug, $matches_end);
                if( $start && $end ) {
                    $start =  $matches_start[1] . $matches_start[2] . $matches_start[3];
                    $end =    $matches_end[1] . $matches_end[2] . $matches_end[3];
                    foreach($terms as &$term) {
                        $term->min      = $start;
                        $term->max      = $end;
                        if( preg_match($regexr, $term->slug, $matches) ) {
                            $term->value    = $matches[1] . $matches[2] . $matches[3];
                        } else {
                            $terms = array();
                            break;
                        }
                    }
                    if( isset($term) ) {
                        unset($term);
                    }
                } else {
                    $terms = array();
                }
            }
            $set_query_var_title['terms'] = $terms;
        }
        return $set_query_var_title;
    }
    function new_attribute_slider($template_content, $terms, $berocket_query_var_title) {
        if( in_array($berocket_query_var_title['new_template'], array('slider', 'new_slider')) && count($terms) > 1 ) {
            $template_content['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['class']['bapf_slidr_type'] = 'bapf_slidr_arr';
            $slider_data = array();
            foreach($terms as $term) {
                $slider_data[] = array('v' => $term->value, 'n' => $term->name);
            }
            $template_content['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['data-attr'] = json_encode($slider_data);
            if( in_array($berocket_query_var_title['new_template'], array('slider')) && ! empty($berocket_query_var_title['enable_slider_inputs']) ) {
                $template_content['template']['content']['filter']['content']['slider_all']['content']['from']['content']['input'] = array(
                    'type'          => 'tag',
                    'tag'           => 'select',
                    'attributes'    => array(),
                );
                $template_content['template']['content']['filter']['content']['slider_all']['content']['to']['content']['input'] = array(
                    'type'          => 'tag',
                    'tag'           => 'select',
                    'attributes'    => array(),
                );
            }
        } elseif( in_array($berocket_query_var_title['new_template'], array('slider')) && ! empty($berocket_query_var_title['enable_slider_inputs']) ) {
            $template_content['template']['content']['filter']['content']['slider_all']['content']['from']['content']['input'] = array(
                'type'          => 'tag_open',
                'tag'           => 'input',
                'attributes'    => array(
                    'type'          => 'text'
                ),
            );
            $template_content['template']['content']['filter']['content']['slider_all']['content']['to']['content']['input'] = array(
                'type'          => 'tag_open',
                'tag'           => 'input',
                'attributes'    => array(
                    'type'          => 'text'
                ),
            );
        }
        return $template_content;
    }
    //SHOW COUNT BEFORE UPDATE
    function count_before_update() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( ! empty($options['ub_product_count']) ) {
            add_action('wp_footer', array($this, 'generate_count_before_update'), 1);
        }
    }
    function generate_count_before_update() {
        global $berocket_parse_page_obj;
        if( ! empty($berocket_parse_page_obj) && ! empty($berocket_parse_page_obj->query_vars) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $options = $BeRocket_AAPF->get_option();
            BeRocket_tooltip_display::include_assets();
            $query_vars = apply_filters('bapf_uparse_apply_filters_to_query_vars', $berocket_parse_page_obj->query_vars);
            if( isset($query_vars['bapf_apply']) ) {
                unset($query_vars['bapf_apply']);
            }
            $query = new WP_Query($query_vars);
            $query = apply_filters('bapf_query_count_before_update', $query);
            $query->get_posts();
            echo '<span style="display:none!important;" class="bapf_count_before_update">'.$query->found_posts.'</span>';
            echo '<div id="bapf_footer_count_before" data-theme="'.(empty($options['tippy_product_count_theme']) ? 'light' : $options['tippy_product_count_theme']).'"></div>';
            if( ! empty($options['tippy_product_count_fontsize']) && intval($options['tippy_product_count_fontsize']) > 5 ) {
                echo '<style>#bapf_footer_count_before .tippy-content{
                    font-size: '.$options['tippy_product_count_fontsize'].'px;
                }</style>';
            }
        }
    }
    function pll_rewrite_rules($rewrite_rules) {
        $rewrite_rules[] = 'br_filters';
        return $rewrite_rules;
    }
    function pll_filtered_taxonomies($taxonomies, $is_settings) {
        if( ! $is_settings ) {
            $taxonomies[] = 'br_filters';
        }
        return $taxonomies;
    }
}
new BeRocket_AAPF_paid();
class BeRocket_AAPF_paid_new extends BeRocket_plugin_variations {
    function __construct() {
        add_action( 'plugins_loaded', array($this, 'plugins_loaded') );
        add_action( 'braapf_register_frontend_assets', array(__CLASS__, 'register_paid_file_script'), 10, 1 );
        add_action( 'braapf_wp_enqueue_script_after', array(__CLASS__, 'include_paid_file_script'), 10, 1 );
        add_action( 'bapf_include_all_tempate_styles', array(__CLASS__, 'include_paid_tempate_styles') );
        add_filter( 'brapf_filter_instance', array(__CLASS__, 'datepicker_fix_instance'), 10, 3 );
        add_filter('BeRocket_AAPF_template_full_content', array(__CLASS__, 'value_icon_datepicker'), 600, 4);
        add_action('braapf_single_filter_required', array(__CLASS__, 'datepicker_required'), 800, 2);
        add_action('braapf_advanced_single_filter_additional', array(__CLASS__, 'custom_text_price_ranges'), 800, 2);
        add_action( 'wp_ajax_braapf_datepicker_important_current', array( __CLASS__, 'datepicker_important_current' ) );
        add_filter('berocket_aapf_widget_include_exclude_items', array($this, 'correct_terms_child_parent'), 10100, 5);
        add_filter('berocket_query_var_title_before_widget', array($this, 'correct_terms_child_parent_fix'), 10100, 5);
        add_filter('braapf_single_filter_hide_cat_value_limit', array($this, 'display_cat_value_limit'));
        self::search_field();
        self::sortby_field();
    }
    public static function include_paid_tempate_styles() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        foreach (glob($BeRocket_AAPF->info['plugin_dir'] . "/paid/template_styles/*.php") as $filename)
        {
            include_once($filename);
        }
    }
    public static function register_paid_file_script() {
        $admin_file = 'paid/assets/script.js';
        if( file_exists(plugin_dir_path(BeRocket_AJAX_filters_file).$admin_file) ) {
            wp_register_script( 'berocket_aapf_widget-script_paid',
                plugins_url( $admin_file, BeRocket_AJAX_filters_file ),
                array( 'jquery', 'jquery-ui-slider' ) );
        }
    }
    public static function include_paid_file_script($handle) {
        $admin_file = 'paid/assets/script.js';
        if( ! BeRocket_AAPF::$concat_enqueue_files && $handle == 'berocket_aapf_widget-script' && file_exists(plugin_dir_path(BeRocket_AJAX_filters_file).$admin_file) ) {
            BeRocket_AAPF::wp_enqueue_script('berocket_aapf_widget-script_paid');
        }
    }
    function plugins_loaded() {
        $this->single_edit_elements();
        $this->color_elements();
    }
    function single_edit_elements() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        add_action('braapf_advanced_single_filter_attribute_setup', array(__CLASS__, 'include_exclude'), 500, 2);
        //REQUIRED
        add_action('braapf_single_filter_required', array(__CLASS__, 'price_ranges'), 500, 2);
        //ADDITIONAL
        //FOR ALL FILTERS
        add_action('braapf_single_filter_additional', array(__CLASS__, 'show_product_count_per_attr'), 250, 2);
        add_action('braapf_advanced_single_filter_attribute_setup', array(__CLASS__, 'parent_product_cat'), 600, 2);
        //CHECKBOX
        add_action('braapf_advanced_single_filter_additional', array(__CLASS__, 'child_parent'), 850, 2);
        add_action('braapf_advanced_single_filter_additional', array(__CLASS__, 'values_per_row'), 950, 2);
        //SLIDER
        add_action('braapf_single_filter_additional', array(__CLASS__, 'enable_slider_inputs'), 350, 2);
        add_action('braapf_single_filter_additional', array(__CLASS__, 'slider_numeric'), 350, 2);
        //DATE
        add_action('braapf_single_filter_additional', array(__CLASS__, 'date_style'), 950, 2);
        //SELECTED FILTER AREA
        add_action('braapf_single_filter_additional', array(__CLASS__, 'selected_filters_area'), 900, 2);
        add_filter('berocket_query_var_title_before_element', array(__CLASS__, 'selected_filters_area_query_var'), 100000, 2);
        add_filter('BeRocket_AAPF_template_full_element_content', array(__CLASS__, 'selected_filters_area_styles'), 10, 2);
        if( ! empty( $option['use_links_filters'] ) ) {
            add_filter('BeRocket_AAPF_template_single_item', array($this, 'checkbox_links_filters'), 2000, 4);
        }
    }
    function checkbox_links_filters($element, $term, $i, $berocket_query_var_title) {
        if( $berocket_query_var_title['new_template'] == 'checkbox' && br_get_value_from_array($element, array('content', 'checkbox', 'attributes', 'disabled')) != 'disabled' ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            $operator = $berocket_query_var_title['operator'];
            $single = ! empty($berocket_query_var_title['single_selection']);
            $term_taxonomy_echo = berocket_isset($term, 'wpml_taxonomy');

            if( empty($term_taxonomy_echo) ) {
                $term_taxonomy_echo = berocket_isset($term, 'taxonomy');
            }
            global $berocket_parse_page_obj, $wp_rewrite;
            $filter_data = $berocket_parse_page_obj->modify_data(array(
                'values' => array(
                    array('value' => $term->term_id, 'taxonomy' => $term->taxonomy)
                ), 'type' => (empty($berocket_query_var_title['single_selection']) ? 'revert' : 'replace'), 'op' => $operator, 'calculate' => FALSE,
                'berocket_query_var_title' => $berocket_query_var_title)
            );
            $link = "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $link = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $link );
            $link = preg_replace( "~paged?/[0-9]+/?~", "", $link );
            $term_link = $berocket_parse_page_obj->get_link_from_data($filter_data, $link);
            $element['content']['label']['content'] = array(
                'link'     => array(
                    'type'          => 'tag',
                    'tag'           => 'a',
                    'attributes'    => array(
                        'href'          => $term_link
                    ),
                    'content'       => $element['content']['label']['content']
                ),
            );
            if (
                ! empty( $option['use_nofollow'] ) and
                (
                    $option['use_nofollow'] == 1 or
                    $option['use_nofollow'] == 2 and
                    apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'checkbox_links_filters' )
                )
            ) {
                $element['content']['label']['content']['link']['attributes']['rel'] = 'nofollow';
            }
            $noindex = false;
            if ( ! empty( $option['use_noindex'] ) and
                 (
                     $option['use_noindex'] == 1 or
                     $option['use_noindex'] == 2 and
                     apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'checkbox_links_filters' )
                 )
            ) {
                $element['content']['label']['content'] = array(
                    'noindex'       => array(
                        'type'          => 'tag',
                        'tag'           => 'noindex',
                        'content'       => $element['content']['label']['content']
                    ),
                );
            }
        }
        return $element;
    }
    static function include_exclude($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_include_exclude_select braapf_full_select_full">';
                $include_exclude_select = br_get_value_from_array($braapf_filter_settings, 'include_exclude_select', '0');
                echo '<label for="braapf_include_exclude_select">' . __('Display only selected values / Remove selected values', 'BeRocket_AJAX_domain') . '</label>';
                echo '<select id="braapf_include_exclude_select" name="'.$settings_name.'[include_exclude_select]">';
                    echo '<option value="">' . __('Disabled', 'BeRocket_AJAX_domain') . '</option>';
                    echo '<option value="include"'.($include_exclude_select == 'include' ? ' selected' : '').'>' . __('Display only', 'BeRocket_AJAX_domain') . '</option>';
                    echo '<option value="exclude"'.($include_exclude_select == 'exclude' ? ' selected' : '').'>' . __('Remove', 'BeRocket_AJAX_domain') . '</option>';
                echo '</select>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            $taxonomy_name = braapf_single_filter_edit_elements::get_curent_taxonomy_name($braapf_filter_settings);
            echo '<div class="braapf_include_exclude_list braapf_full_select_full" data-name="' . $settings_name . '[include_exclude_list]" data-taxonomy="'.( empty($taxonomy_name) ? '' : $taxonomy_name ).'">';
                $include_exclude_list = br_get_value_from_array($braapf_filter_settings, 'include_exclude_list', array());
                $list = BeRocket_AAPF_Widget_functions::include_exclude_view($braapf_filter_settings, $include_exclude_list);
                $list = str_replace('%field_name%', $settings_name.'[include_exclude_list]', $list);
                echo $list;
            echo '</div>';
        echo '</div>';
        ?>
        <script>
            var braapf_exclude_include_name = '<?php echo $settings_name; ?>[include_exclude_list]';
            var braapf_include_exclude_list_load,
            braapf_include_or_exclude_class;
            (function ($){$(document).ready(function() {
                $('.braapf_include_exclude_list').data('filtertype', jQuery('.braapf_filter_type_data *').serialize());
                braapf_include_exclude_list_load = function() {
                    var taxonomy_name = braapf_get_current_taxonomy_name();
                    var specific = braapf_current_specific();
                    var filtertype = jQuery('.braapf_filter_type_data *').serialize();
                    var old_filtertype = $('.braapf_include_exclude_list').data('filtertype');
                    if( filtertype != old_filtertype ) {
                        var data = 'action=br_include_exclude_list&taxonomy_name='+taxonomy_name+'&'+filtertype;
                        $.post(ajaxurl, data, function(data) {
                            $('.braapf_include_exclude_list').data('filtertype', filtertype);
                            if( data ) {
                                var replace_str = /%field_name%/g;
                                data = data.replace(replace_str, braapf_exclude_include_name);
                                $('.braapf_include_exclude_list').html(data);
                            } else {
                                $('.braapf_include_exclude_list').text("");
                            }
                        });
                    }
                    return true;
                }
                braapf_include_or_exclude_class = function (show, element, data_string, init) {
                    berocket_show_element_callback(show, element, data_string, init);
                    if( show == "1" ) {
                        $('.braapf_include_exclude_list').removeClass('braapf_include').removeClass('braapf_exclude');
                        if($('#braapf_include_exclude_select').val() == 'include') {
                            $('.braapf_include_exclude_list').addClass('braapf_include');
                        } else {
                            $('.braapf_include_exclude_list').addClass('braapf_exclude');
                        }
                    }
                }
                berocket_show_element('.braapf_include_exclude_select', '{#braapf_filter_type} == !braapf_all_sameas_custom_taxonomy! || {#braapf_filter_type} == !braapf_all_sameas_attribute! || {#braapf_filter_type} == "_stock_status" || {#braapf_filter_type} == "_sale"');
                berocket_show_element('.braapf_include_exclude_list', '({#braapf_filter_type} == !braapf_all_sameas_custom_taxonomy! || {#braapf_filter_type} == !braapf_all_sameas_attribute! || {#braapf_filter_type} == "_stock_status" || {#braapf_filter_type} == "_sale") && {#braapf_include_exclude_select} != "" && !braapf_include_exclude_list_load! == true', true, braapf_include_or_exclude_class);
            });})(jQuery);
        </script>
        <?php
    }
    static function price_ranges_generate_single_html($i, $ranges, $settings_name, $labels = array()) {
        if( empty($labels) || ! is_array($labels) ) {
            $labels = array();
        }
        $j = $i - 1;
        $start_price = ($j == 0 ? $ranges[$j] : $ranges[$j] + 1) ;
        $end_price = $ranges[$i];
        $html = '<div class="berocket_ranges">';
            $html .= '<span class="berocket_ranges_from"><input type="number" min="0" '. 
                ($j == 0 ? 'name="' . $settings_name . '[ranges][]"' : 'readonly') . ' value="' . $start_price
            . '"></span>';
            $html .= '<span class="berocket_ranges_glue"></span>';
            $html .= '<span class="berocket_ranges_to"><input type="number" min="0" name="' . $settings_name . '[ranges][]" value="' . $end_price . '"></span>';
            $html .= '<span class="berocket_ranges_label"><input type="text" name="' . $settings_name . '[ranges_label][]" placeholder="' . $start_price . ' - ' . $end_price . '" value="' . esc_html(empty($labels[$j]) ? '' : $labels[$j]) . '"></span>';
            if( $i != 1 ) {
                $html .= '<a href="#remove" class="berocket_remove_ranges">+</a>';
            } else {
                $html .= '<span class="berocket_remove_ranges"></span>';
            }
        $html .= '</div>';
        return $html;
    }
    static function price_ranges($settings_name, $braapf_filter_settings) {
        $ranges_label = br_get_value_from_array($braapf_filter_settings, 'ranges_label', array());
        $ranges = br_get_value_from_array($braapf_filter_settings, 'ranges', '');
        if( ! is_array($ranges_label) ) {
            $ranges_label = array();
        }
        if(! empty( $ranges ) && is_array( $ranges ) && count( $ranges ) > 0) {
            $prev_range = -1;
            foreach($ranges as $i => $range) {
                $start = $i - 1;
                if( $start < 0 ) {
                    $start = 0;
                }
                if(strlen($range) == 0) {
                    unset($ranges[$i]);
                    if( isset($ranges_label[$start]) ) {
                        unset($ranges_label[$start]);
                    }
                    continue;
                }
                if(floatval($range) <= $prev_range) {
                    unset($ranges[$i]);
                    if( isset($ranges_label[$start]) ) {
                        unset($ranges_label[$start]);
                    }
                    continue;
                }
                $prev_range = floatval($range);
            }
            $ranges = array_values($ranges);
            $ranges_label = array_values($ranges_label);
        }
        if ( empty( $ranges ) || ! is_array( $ranges ) || count( $ranges ) <= 1 ) {
            $ranges = array(1,100,1000);
        }
        $ranges = array_values($ranges);
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="berocket_ranges_block  braapf_full_select_full"><div>';
            echo '<div class="berocket_ranges_block_header">';
                echo '<span class="berocket_ranges_from">'.__('From', 'BeRocket_AJAX_domain').'</span>';
                echo '<span class="berocket_ranges_glue"></span>';
                echo '<span class="berocket_ranges_to">'.__('To', 'BeRocket_AJAX_domain').'</span>';
                echo '<span class="berocket_ranges_label">'.__('Label', 'BeRocket_AJAX_domain').'</span>';
                echo '<span class="berocket_remove_ranges"></span>';
            echo '</div>';
            for($i = 1; $i < count($ranges); $i++) {
                echo self::price_ranges_generate_single_html($i, $ranges, $settings_name, $ranges_label);
            }
            ?><a href="#add" class="berocket_add_ranges" data-html='<div class="berocket_ranges"><input type="number" min="1" name="<?php echo $settings_name; ?>[ranges][]" value=""><a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a></div>'>
                +
            </a>
            <script>
                jQuery(document).on('click', '.berocket_add_ranges',function(event) {
                    event.preventDefault();
                    var html = '<?php echo self::price_ranges_generate_single_html(2, array('', '0', ''), $settings_name); ?>';
                    jQuery(this).before(jQuery(html));
                    jQuery('.berocket_ranges_to input').trigger('change');
                });
                jQuery(document).on('change', '.berocket_ranges_to input', function() {
                    var value = jQuery(this).val();
                    var next = jQuery(this).closest('.berocket_ranges').next();
                    if( next.length && next.is('.berocket_ranges') ) {
                        var setValue = value
                        if( setValue != '' ) {
                            setValue = parseInt(setValue) + 1;
                        }
                        next.find('.berocket_ranges_from input').val(setValue).trigger('change');
                    }
                });
                jQuery(document).on('change', '.berocket_ranges_to input, .berocket_ranges_from input', function() {
                    var from = jQuery(this).parents('.berocket_ranges').first().find('.berocket_ranges_from input').val();
                    var to = jQuery(this).parents('.berocket_ranges').first().find('.berocket_ranges_to input').val();
                    jQuery(this).parents('.berocket_ranges').first().find('.berocket_ranges_label input').attr('placeholder', from + ' - ' + to);
                });
            </script><?php
            echo '</div></div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_range_display_type braapf_select_full">';
                $range_types = array(
                    array('value' => '',        'name' => __('1.00-100.00, 101.00-200.00, 201.00-1000.00', 'BeRocket_AJAX_domain')),
                    array('value' => 'same',    'name' => __('1.00-100.00, 100.00-200.00, 200.00-1000.00', 'BeRocket_AJAX_domain')),
                    array('value' => 'decimal', 'name' => __('1.00-99.99, 100.00-199.99, 200.00-999.99', 'BeRocket_AJAX_domain')),
                );
                $range_display_type = br_get_value_from_array($braapf_filter_settings, 'range_display_type', '0');
                echo '<select id="braapf_range_display_type" name="'.$settings_name.'[range_display_type]">';
                    echo '<optgroup label="' . __('Ranges: 1,100,200,1000', 'BeRocket_AJAX_domain') . '">';
                    foreach($range_types as $range_type) {
                        echo '<option value="'.$range_type['value'].'"'.($range_display_type == $range_type['value'] ? ' selected' : '').'>'.$range_type['name'].'</option>';
                    }
                    echo '</optgroup>';
                echo '</select>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_show_last_to_infinity braapf_half_select_full">';
                $show_last_to_infinity = br_get_value_from_array($braapf_filter_settings, 'show_last_to_infinity', '0');
                echo '<p>';
                    echo '<input id="braapf_show_last_to_infinity" type="checkbox" name="' . $settings_name . '[show_last_to_infinity]"' . ( empty($show_last_to_infinity) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_show_last_to_infinity">'.__('Replace the last range value with the infinity symbol', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_to_infinity_text braapf_half_select_full">';
                $to_infinity_text = br_get_value_from_array($braapf_filter_settings, 'to_infinity_text', '');
                echo '<label for="braapf_to_infinity_text">'.__('Show last range to the infinity', 'BeRocket_AJAX_domain').'</label>';
                echo '<input id="braapf_to_infinity_text" type="text" name="' . $settings_name . '[to_infinity_text]" value="'.$to_infinity_text.'" placeholder="&#8734;">';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.berocket_ranges_block', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
            berocket_show_element('.braapf_range_display_type', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
            berocket_show_element('.braapf_show_last_to_infinity', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
            berocket_show_element('.braapf_to_infinity_text', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && {#braapf_show_last_to_infinity} == true');
        });
        </script>
        <?php
    }
    static function show_product_count_per_attr ($settings_name, $braapf_filter_settings){
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_show_product_count_per_attr braapf_half_select_full">';
                $show_product_count_per_attr = br_get_value_from_array($braapf_filter_settings, 'show_product_count_per_attr', '0');
                echo '<p>';
                    echo '<input id="braapf_show_product_count_per_attr" type="checkbox" name="' . $settings_name . '[show_product_count_per_attr]"' . ( empty($show_product_count_per_attr) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_show_product_count_per_attr">'.__('Show products count per attribute value?', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_product_count_per_attr_style braapf_half_select_full">';
				echo '<label for="braapf_product_count_per_attr_style">'.__('Products count per attribute value style', 'BeRocket_AJAX_domain').'</label>';
				$count_per_attr_styles = array(
                    array('value' => '',        'name' => __('Value(5)', 'BeRocket_AJAX_domain')),
                    array('value' => 'space',    'name' => __('Value (5)', 'BeRocket_AJAX_domain')),
                    array('value' => 'value', 'name' => __('Value( 5 )', 'BeRocket_AJAX_domain')),
                    array('value' => 'space_value', 'name' => __('Value ( 5 )', 'BeRocket_AJAX_domain')),
                );
                $product_count_per_attr_style = br_get_value_from_array($braapf_filter_settings, 'product_count_per_attr_style', '');
                echo '<select id="braapf_product_count_per_attr_style" name="'.$settings_name.'[product_count_per_attr_style]">';
                    foreach($count_per_attr_styles as $count_per_attr_style) {
                        echo '<option value="'.$count_per_attr_style['value'].'"'.($product_count_per_attr_style == $count_per_attr_style['value'] ? ' selected' : '').'>'.$count_per_attr_style['name'].'</option>';
                    }
                echo '</select>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
            jQuery(document).ready(function() {
                berocket_show_element('.braapf_show_product_count_per_attr', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
                berocket_show_element('.braapf_product_count_per_attr_style', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || ( !braapf_current_template! == "checkbox" && ( ( !braapf_current_specific! != "color" && !braapf_current_specific! != "image" ) || ({#braapf_use_value_with_color} != "" && {#braapf_use_value_with_color} != "tooltip" ) ) ) ) && {#braapf_show_product_count_per_attr} == true');
            });
        </script>
        <?php
    }
    static function slider_numeric($settings_name, $braapf_filter_settings){
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_slider_numeric braapf_full_select_full">';
                $slider_numeric = br_get_value_from_array($braapf_filter_settings, 'slider_numeric', '0');
                echo '<p>';
                    echo '<input id="braapf_slider_numeric" type="checkbox" name="' . $settings_name . '[slider_numeric]"' . ( empty($slider_numeric) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_slider_numeric">'.__('Use as numeric', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_slider_numeric', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "slider" || !braapf_current_template! == "new_slider") && {#braapf_filter_type} != "price"');
        });
        </script>
        <?php
    }
    static function values_per_row($settings_name, $braapf_filter_settings){
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_values_per_row braapf_full_select_full">';
                echo '<label for="braapf_values_per_row">' . __('Values per row', 'BeRocket_AJAX_domain') . '</label>';
                $values_per_row = br_get_value_from_array($braapf_filter_settings, 'values_per_row', '');
                echo '<select id="braapf_values_per_row" name="'.$settings_name.'[values_per_row]">';
                    echo '<option value=""'. ($values_per_row == "" ? ' selected' : '') .'>Default</option>';
                    echo '<option value="1"'.($values_per_row == "1" ? ' selected' : '').'>1</option>';
                    echo '<option value="2"'.($values_per_row == "2" ? ' selected' : '').'>2</option>';
                    echo '<option value="3"'.($values_per_row == "3" ? ' selected' : '').'>3</option>';
                    echo '<option value="4"'.($values_per_row == "4" ? ' selected' : '').'>4</option>';
                echo '</select>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_values_per_row', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "checkbox"');
        });
        </script>
        <?php
    }
    static function child_parent($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_child_parent braapf_half_select_full">';
                $child_parent = br_get_value_from_array($braapf_filter_settings, 'child_parent', '');
                echo '<label for="braapf_child_parent">' . __('Child/Parent Limitation', 'BeRocket_AJAX_domain') . '</label>';
                echo '<select id="braapf_child_parent" name="'.$settings_name.'[child_parent]">';
                    echo '<option value=""'.($child_parent == "" ? ' selected' : '').'>'.__('Disabled', 'BeRocket_AJAX_domain').'</option>';
                    echo '<option value="depth"'.($child_parent == "depth" ? ' selected' : '').'>'.__('Child Count', 'BeRocket_AJAX_domain').'</option>';
                    echo '<option value="parent"'.($child_parent == "parent" ? ' selected' : '').'>'.__('Parent', 'BeRocket_AJAX_domain').'</option>';
                    echo '<option value="child"'.($child_parent == "child" ? ' selected' : '').'>'.__('Child', 'BeRocket_AJAX_domain').'</option>';
                echo '</select>';
            echo '</div>';
            echo '<div class="braapf_child_parent_depth braapf_half_select_full">';
                $child_parent_depth = br_get_value_from_array($braapf_filter_settings, 'child_parent_depth', '');
                echo '<label for="braapf_child_parent_depth">' . __('Child depth', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_depth" type="text" name="' . $settings_name . '[child_parent_depth]" value="'.$child_parent_depth.'">';
            echo '</div>';
            echo '<div class="braapf_child_onew_count braapf_half_select_full">';
                $child_onew_count = br_get_value_from_array($braapf_filter_settings, 'child_onew_count', '');
                echo '<label for="braapf_child_onew_count">' . __('Child count', 'BeRocket_AJAX_domain') . '</label>';
                echo '<select id="braapf_child_onew_count" name="'.$settings_name.'[child_onew_count]">';
                for($i = 1; $i < 11; $i++) {
                    echo '<option value="'.$i.'"'.($child_onew_count == $i ? ' selected' : '').'>'.$i.'</option>';
                }
                echo '</select>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_child_parent_child braapf_full_select_full">';
                $child_parent_no_values = br_get_value_from_array($braapf_filter_settings, 'child_parent_no_values', '');
                echo '<label for="braapf_child_parent_no_values">' . __('"No values" messages', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_no_values" type="text" name="' . $settings_name . '[child_parent_no_values]" value="'.$child_parent_no_values.'">';
                
                $child_parent_previous = br_get_value_from_array($braapf_filter_settings, 'child_parent_previous', '');
                echo '<label for="braapf_child_parent_previous">' . __('"Select previous" messages', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_previous" type="text" name="' . $settings_name . '[child_parent_previous]" value="'.$child_parent_previous.'">';
                
                $child_parent_no_products = br_get_value_from_array($braapf_filter_settings, 'child_parent_no_products', '');
                echo '<label for="braapf_child_parent_no_products">' . __('"No values" messages', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_no_products" type="text" name="' . $settings_name . '[child_parent_no_products]" value="'.$child_parent_no_products.'">';
            echo '</div>';
            for($i = 1; $i < 11; $i++) {
                echo '<div class="braapf_child_parent_depth_'.$i.' braapf_half_select_full">';
                    $title = br_get_value_from_array($braapf_filter_settings, array('child_onew_childs', $i, 'title'), '');
                    echo '<label for="braapf_child_onew_childs_'.$i.'_title">' . __('Child', 'BeRocket_AJAX_domain').' '.$i.' '.__('Title', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input id="braapf_child_onew_childs_'.$i.'_title" type="text" name="' . $settings_name . '[child_onew_childs]['.$i.'][title]" value="'.$title.'">';
                    
                    $no_values = br_get_value_from_array($braapf_filter_settings, array('child_onew_childs', $i, 'no_values'), '');
                    echo '<label for="braapf_child_onew_childs_'.$i.'_no_values">' . __('Child', 'BeRocket_AJAX_domain').' '.$i.' '.__('"No values" messages', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input id="braapf_child_onew_childs_'.$i.'_no_values" type="text" name="' . $settings_name . '[child_onew_childs]['.$i.'][no_values]" value="'.$no_values.'">';
                    
                    $previous = br_get_value_from_array($braapf_filter_settings, array('child_onew_childs', $i, 'previous'), '');
                    echo '<label for="braapf_child_onew_childs_'.$i.'_previous">' . __('Child', 'BeRocket_AJAX_domain').' '.$i.' '.__('"Select previous" messages', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input id="braapf_child_onew_childs_'.$i.'_previous" type="text" name="' . $settings_name . '[child_onew_childs]['.$i.'][previous]" value="'.$previous.'">';
                    ?>
                    <script>
                    jQuery(document).ready(function() {
                        berocket_show_element('.braapf_child_parent_depth_<?php echo $i; ?>', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "depth" && {#braapf_child_onew_count} >= <?php echo $i; ?>');
                    });
                    </script>
                    <?php
                echo '</div>';
            }
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_child_parent', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true');
            berocket_show_element('.braapf_child_parent_depth', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "child"');
            berocket_show_element('.braapf_child_parent_child', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "child"');
            berocket_show_element('.braapf_child_onew_count', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "depth"');
        });
        </script>
        <?php
    }
    static function date_style($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_date_change_month braapf_half_select_full">';
                $date_change_month = br_get_value_from_array($braapf_filter_settings, 'date_change_month', '0');
                echo '<p>';
                    echo '<input id="braapf_date_change_month" type="checkbox" name="' . $settings_name . '[date_change_month]"' . ( empty($date_change_month) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_date_change_month">'.__('Dates of the month Drop-down list', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_date_change_year braapf_half_select_full">';
                $date_change_year = br_get_value_from_array($braapf_filter_settings, 'date_change_year', '0');
                echo '<p>';
                    echo '<input id="braapf_date_change_year" type="checkbox" name="' . $settings_name . '[date_change_year]"' . ( empty($date_change_year) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_date_change_year">'.__('Dates of the year Drop-down list', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_date_style braapf_full_select_full">';
                echo '<label for="braapf_date_style">' . __('Date visual style', 'BeRocket_AJAX_domain') . '</label>';
                $date_style = br_get_value_from_array($braapf_filter_settings, 'date_style', '');
                echo '<select id="braapf_date_style" name="'.$settings_name.'[date_style]">';
                $date_styles = array(
                    'm/d/Y' => 'mm/dd/yyyy',
                    'd/m/Y' => 'dd/mm/yyyy',
                    'Y/m/d' => 'yyyy/mm/dd',
                    'Y/d/m' => 'yyyy/dd/mm',
                    'm-d-Y' => 'mm-dd-yyyy',
                    'd-m-Y' => 'dd-mm-yyyy',
                    'Y-m-d' => 'yyyy-mm-dd',
                    'Y-d-m' => 'yyyy-dd-mm',
                    'm.d.Y' => 'mm.dd.yyyy',
                    'd.m.Y' => 'dd.mm.yyyy',
                    'Y.m.d' => 'yyyy.mm.dd',
                    'Y.d.m' => 'yyyy.dd.mm',
                );
                foreach($date_styles as $date_style_val => $date_style_name) {
                    echo '<option value="'.$date_style_val.'"'.( $date_style == $date_style_val ? ' selected' : '' ).'>'.$date_style_name.'</option>';
                }
                echo '</select>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_date_change_month', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "datepicker"');
            berocket_show_element('.braapf_date_change_year', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "datepicker"');
            berocket_show_element('.braapf_date_style', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "datepicker"');
        });
        </script>
        <?php
    }
    static function selected_filters_area($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_sfa_hide_attribute_name braapf_full_select_full">';
                $sfa_hide_attribute_name = br_get_value_from_array($braapf_filter_settings, 'sfa_hide_attribute_name', '0');
                echo '<p>';
                    echo '<input id="braapf_sfa_hide_attribute_name" type="checkbox" name="' . $settings_name . '[sfa_hide_attribute_name]"' . ( empty($sfa_hide_attribute_name) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_sfa_hide_attribute_name">'.__('Hide attribute name', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_sfa_hide_attribute_name', '{.braapf_widget_type input[type=radio]} == "selected_area"');
        });
        </script>
        <?php
    }
    static function selected_filters_area_query_var($set_query_var_title, $advanced) {
        $instance = $advanced['options'];
        $set_query_var_title['sfa_hide_attribute_name'] = ! empty($instance['sfa_hide_attribute_name']);
        return $set_query_var_title;
    }
    static function selected_filters_area_styles($template, $berocket_query_var_title) {
        if( ! empty($berocket_query_var_title['sfa_hide_attribute_name']) ) {
            $template['template']['attributes']['class']['valueonly'] = 'bapf_sfa_valueonly';
        }
        return $template;
    }
    static function datepicker_required($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_datepicker_important braapf_full_select_full">';
                echo '<h2><strong style="color:red;">'.__('IMPORTANT FOR DATEPICKER', 'BeRocket_AJAX_domain').'</strong></h2>';
                echo '<p>'.__('Datepicker requires a specific slug for attribute/taxonomy values.', 'BeRocket_AJAX_domain')
                          .' '.__('Slug must be like', 'BeRocket_AJAX_domain').' <strong>YYYYMMDD</strong></p>';
                echo '<h3 class="braapf_datepicker_important_example">'.__('Example', 'BeRocket_AJAX_domain').'</h3>';
                echo '<p>'.__('For date', 'BeRocket_AJAX_domain').' <strong>2012/05/17</strong> '.__('slug must be', 'BeRocket_AJAX_domain').' <strong>20120517</strong></p>';
                echo '<h3 class="braapf_datepicker_important_values">'.__('Current attribute/taxonomy values', 'BeRocket_AJAX_domain').'</h3>';
                echo '<div class="braapf_datepicker_important_current"></div>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery('.braapf_datepicker_important_current').data('filtertype', jQuery('.braapf_filter_type_data *').serialize());
        function braapf_datepicker_required_taxonomy_check(show, element, data_string, init) {
            berocket_show_element_callback(show, element, data_string, init);
            var taxonomy_name = braapf_get_current_taxonomy_name();
            var template = braapf_current_template();
            if ( show == '1' && template == 'datepicker' ) {
                var filtertype = jQuery('.braapf_filter_type_data *').serialize();
                var old_filtertype = jQuery('.braapf_datepicker_important_current').data('filtertype');
                var data = 'action=braapf_datepicker_important_current&tax_name='+taxonomy_name+'&'+filtertype;
                jQuery.post(ajaxurl, data, function(data) {
                    jQuery('.braapf_datepicker_important_current').html(data);
                });
                return true;
            } else {
                jQuery('.braapf_datepicker_important_current').text("");
                return false;
            }
        }
        berocket_show_element('.braapf_datepicker_important', '({#braapf_filter_type} == !braapf_all_sameas_custom_taxonomy! || {#braapf_filter_type} == !braapf_all_sameas_attribute!) && !braapf_current_template! == "datepicker"', true, braapf_datepicker_required_taxonomy_check);
        </script>
        <?php
    }
    static function datepicker_important_current() {
        if ( current_user_can( 'manage_woocommerce' ) ) {
            $br_product_filter = (empty($_POST['br_product_filter']) ? array() : $_POST['br_product_filter']);
            $terms = BeRocket_AAPF_Widget_functions::get_terms_for_filter($br_product_filter);
            if( is_array($terms) && count($terms) > 0 ) {
                echo '<table style="width: 100%;">';
                echo '<tr>';
                echo '<th>'.__('OK', 'BeRocket_AJAX_domain').'</th>';
                echo '<th>'.__('Name', 'BeRocket_AJAX_domain').'</th>';
                echo '<th>'.__('Slug', 'BeRocket_AJAX_domain').'</th>';
                echo '<th>'.__('Date detected', 'BeRocket_AJAX_domain').'</th>';
                echo '</tr>';
                foreach($terms as $term) {
                    $is_date = preg_match('/^(\d{4})((?:0[1-9])|(?:1[0-2]))((?:0[1-9])|(?:[1-2][0-9])|(?:3[0-1]))$/', $term->slug, $matches);
                    echo '<tr>';
                    echo '<td><strong><i class="fa ' . ($is_date ? 'fa-check' : 'fa-times') . '"></strong></td>';
                    echo '<td>'.$term->name.'</td>';
                    echo '<td>'.$term->slug.'</td>';
                    echo '<td>';
                    if( $is_date ) {
                        echo __('Year:', 'BeRocket_AJAX_domain').' <strong>'.$matches[1].'</strong>'.'; '.
                        __('Month:', 'BeRocket_AJAX_domain').' <strong>'.$matches[2].'</strong>'.'; '.
                        __('Day:', 'BeRocket_AJAX_domain').' <strong>'.$matches[3].'</strong>';
                    } else {
                        echo __('Slug is incorrect', 'BeRocket_AJAX_domain');
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                _e('Empty attribute/taxonomy', 'BeRocket_AJAX_domain');
            }
        }
        wp_die();
    }
    static function custom_text_price_ranges($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_custom_price_ranges braapf_half_select_full">';
                $custom_price_ranges = br_get_value_from_array($braapf_filter_settings, 'custom_price_ranges', '0');
                echo '<p>';
                    echo '<input id="braapf_custom_price_ranges" type="checkbox" name="' . $settings_name . '[custom_price_ranges]"' . ( empty($custom_price_ranges) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_custom_price_ranges">'.__('Use specific Price Ranges text', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_custom_price_ranges_text braapf_half_select_full">';
                $custom_price_ranges_text = br_get_value_from_array($braapf_filter_settings, 'custom_price_ranges_text', '');
                echo '<label for="braapf_custom_price_ranges_text">'.__('Text will be used for Price Ranges', 'BeRocket_AJAX_domain')
                .'<span id="braapf_custom_price_ranges_text_info" class="dashicons dashicons-editor-help"></span></label>';
                echo '<input id="braapf_custom_price_ranges_text" type="text" name="' . $settings_name . '[custom_price_ranges_text]" value="'.$custom_price_ranges_text.'">';
            echo '</div>';
        echo '</div>';
        $tooltip_text = '<strong>' . __('You can use some replacements', 'BeRocket_AJAX_domain') . '</strong>'
        . '<ul>'
        . '<li><i>%from%</i> - ' . __('first value, from this price', 'BeRocket_AJAX_domain') . '</li>'
        . '<li><i>%to%</i> - ' . __('second value, to this price', 'BeRocket_AJAX_domain') . '</li>'
        .'<li><i>%cur_symbol%</i> - ' . __('currency symbol($)', 'BeRocket_AJAX_domain') . '</li>'
        . '<li><i>%cur_slug%</i> - ' . __('currency code(USD)', 'BeRocket_AJAX_domain') . '</li>'
        .'</ul>';
        BeRocket_tooltip_display::add_tooltip(
            array(
                'appendTo'      => 'document.body',
                'arrow'         => true,
                'interactive'   => true, 
                'placement'     => 'top'
            ),
            $tooltip_text,
            '#braapf_custom_price_ranges_text_info'
        );
        ?>
        <script>
        berocket_show_element('.braapf_custom_price_ranges', '{.braapf_widget_type input[type=radio]} == "filter" && {#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
        berocket_show_element('.braapf_custom_price_ranges_text', '{.braapf_widget_type input[type=radio]} == "filter" && {#braapf_filter_type} == "price" && {#braapf_custom_price_ranges} == true && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
        </script>
        <?php
    }
    function color_elements() {
        add_filter('BeRocket_AAPF_template_single_item', array($this, 'multiple_color'), 1000, 4);
    }
    static function multiple_color($template, $term, $i, $berocket_query_var_title) {
        if( ! empty($berocket_query_var_title['new_style']) && berocket_isset($berocket_query_var_title['new_style']['specific']) == 'color' ) {
            $color_list = array('color', 'color_2', 'color_3', 'color_4');
            $meta_color = array();
            foreach($color_list as $color_name) {
                $berocket_term = berocket_term_get_metadata($term, $color_name);
                $berocket_term = br_get_value_from_array($berocket_term, 0, '');
                if( empty($berocket_term) && $color_name != 'color') continue;
                $meta_color[] = $berocket_term;
            }
            $count_color = count($meta_color);
            if( $count_color > 1 ) {
                unset($template['content']['label']['content']['color']['attributes']['style']['bg-color']);
                $berocket_term = berocket_term_get_metadata($term, 'color_gradient');
                $berocket_term = br_get_value_from_array($berocket_term, 0, '');
                $number_class = 'bapf_clr_multi_gradient';
                $gradient = 'linear-gradient(135deg';
                $positions_arr = array(
                    2 => array(0, 50, 100),
                    3 => array(0, 35, 65, 100),
                    4 => array(0, 30, 50, 70, 100)
                );
                $position_step = 100/($count_color);
                if( empty($berocket_term) ) {
                    $position_center = 0;
                } else {
                    $position_center = $position_step / 4;
                }
                foreach($meta_color as $i => $meta_color_single) {
                    if( isset($positions_arr[$count_color]) ) {
                        $position_start = $positions_arr[$count_color][$i];
                        $position_end = $positions_arr[$count_color][$i + 1];
                    } else {
                        $position_position = (100/($count_color)*$i);
                        $position_start = $position_position;
                        $position_end = $position_position + $position_step;
                    }
                    if( $i != 0 ) {
                        $position_start += $position_center;
                    } else {
                        $position_start = 0;
                    }
                    if( $i != ($count_color - 1) ) {
                        $position_end -= $position_center;
                    } else {
                        $position_end = 100;
                    }
                    
                    $gradient .= ', #'.$meta_color_single. ' ' . $position_start . '%, #'.$meta_color_single. ' '. ' ' . $position_end . '%';
                }
                $gradient .= ');';
                $template['content']['label']['content']['color']['attributes']['class']['number'] = $number_class;
                
                if( ! isset($template['content']['label']['content']['color']['attributes']['style']) || is_array($template['content']['label']['content']['color']['attributes']['style']) ) {
                    $template['content']['label']['content']['color']['attributes']['style'] = array();
                }
                $template['content']['label']['content']['color']['attributes']['style']['bg-color'] = 'background: '.$gradient;
            }
        }
        return $template;
    }
    public static function datepicker_fix_instance($instance, $args, $set_query_var_title) {
         if( in_array(br_get_value_from_array($set_query_var_title, 'new_template'), array('datepicker')) ) {
            $instance['type']               = 'slider';
            $instance['order_values_by']    = 'Numeric';
            $instance['order_values_type']  = 'asc';
            $instance['height']             = '';
        }
        return $instance;
    }
    public static function value_icon_datepicker($template_content, $terms, $berocket_query_var_title) {
        if( $berocket_query_var_title['new_template'] == 'datepicker' ) {
            if( ! empty($berocket_query_var_title['icon_before_value']) ) {
                $icon = $berocket_query_var_title['icon_before_value'];
                $icon_element = array(
                    'type'          => 'tag',
                    'tag'           => 'i',
                    'attributes'    => array(
                        'class'         => array(
                            'fa'
                        )
                    ),
                    'content' => array()
                );
                if( substr( $icon, 0, 3) == 'fa-' ) {
                    $icon_element['attributes']['class']['icon'] = $icon;
                } else {
                    $icon_element['content']['icon'] = array(
                        'type'          => 'tag_open',
                        'tag'           => 'img',
                        'attributes'    => array(
                            'class'         => array(
                                'berocket_widget_icon'
                            ),
                            'src'           => $icon,
                            'alt'           => ''
                        )
                    );
                }
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'],
                    'input',
                    array(
                        'icon_before' => $icon_element,
                    ),
                    true
                );
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'],
                    'input',
                    array(
                        'icon_before' => $icon_element,
                    ),
                    true
                );
            }
            if( ! empty($berocket_query_var_title['icon_after_value']) ) {
                $icon = $berocket_query_var_title['icon_after_value'];
                $icon_element = array(
                    'type'          => 'tag',
                    'tag'           => 'i',
                    'attributes'    => array(
                        'class'         => array(
                            'fa'
                        )
                    ),
                    'content' => array()
                );
                if( substr( $icon, 0, 3) == 'fa-' ) {
                    $icon_element['attributes']['class']['icon'] = $icon;
                } else {
                    $icon_element['content']['icon'] = array(
                        'type'          => 'tag_open',
                        'tag'           => 'img',
                        'attributes'    => array(
                            'class'         => array(
                                'berocket_widget_icon'
                            ),
                            'src'           => $icon,
                            'alt'           => ''
                        )
                    );
                }
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'],
                    'input',
                    array(
                        'icon_after_price' => $icon_element
                    )
                );
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'],
                    'input',
                    array(
                        'icon_after_price' => $icon_element
                    )
                );
            }
        }
        return $template_content;
    }
    static function parent_product_cat($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_parent_product_cat braapf_full_select_full">';
                $custom_taxonomies_list = braapf_single_filter_edit_elements::get_custom_taxonomies();
                foreach($custom_taxonomies_list as $value => $data) {
                    if( ! empty($data['hierarchical']) ) {
                        $parent_product_cat = br_get_value_from_array($braapf_filter_settings, 'parent_'.$value, '');
                        echo '<div class="braapf_all_parent_product_cat braapf_parent_product_cat_'.$value.'">';
                            echo '<label for="braapf_parent_product_cat_'.$value.'">' . __('Display only child of', 'BeRocket_AJAX_domain') . '</label>';
                            $hrterms = berocket_aapf_get_terms(array(
                                'taxonomy'          => $value,
                                'hide_empty'        => false
                            ), array(
                                'disable_recount'   => true,
                                'hierarchical'      => true
                            ));
                            echo '<select id="braapf_parent_product_cat_'.$value.'" name="'.$settings_name.'[parent_'.$value.']">';
                                echo '<option value="">' . __('Display All', 'BeRocket_AJAX_domain') . ' '.$data['name'].'</option>';
                                echo '<option value="bapf1level"'.($parent_product_cat == "bapf1level" ? ' selected' : '').'>'.__('Start from parent values', 'BeRocket_AJAX_domain').'</option>';
                                echo '<option value="bapf4current"'.($parent_product_cat == "bapf4current" ? ' selected' : '').'>'.__('Child for current page value', 'BeRocket_AJAX_domain').'</option>';
                                echo '<optgroup label="'.__('Child for value:', 'BeRocket_AJAX_domain').'">';
                                foreach($hrterms as $hrterm) {
                                    echo '<option value="'.$hrterm->term_id.'"'.($parent_product_cat == $hrterm->term_id ? ' selected' : '').'>';
                                    for( $i = 0; $i < $hrterm->depth; $i++ ) {
                                        echo '- ';
                                    }
                                    echo $hrterm->name.'</option>';
                                }
                                echo '</optgroup>';
                            echo '</select>';
                        echo '</div>';
                        ?>
                        <script>
                            jQuery(document).ready(function() {
                                berocket_show_element('.braapf_parent_product_cat_<?php echo $value; ?>', '!braapf_current_attribute! == "<?php echo $value; ?>"');
                            });
                        </script>
                        <?php
                    }
                }
            echo '</div>';
            echo '<div class="braapf_depth_count braapf_half_select_full">';
                $depth_count = br_get_value_from_array($braapf_filter_settings, 'depth_count', '0');
                echo '<label for="braapf_depth_count">' . __('Depth level', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input min=0 id="braapf_depth_count" type="number" name="'.$settings_name.'[depth_count]" value="'.$depth_count.'">';
            echo '</div>';
            ?>
            <script>
            braapf_braapf_parent_product_cat_enabled = function() {
                berocket_show_element_hooked_data.push('#braapf_attribute');
                berocket_show_element_hooked_data.push('#braapf_custom_taxonomy');
                berocket_show_element_hooked_data.push('#braapf_filter_type');
                berocket_show_element_hooked_data.push('.braapf_all_parent_product_cat select');
                if( braapf_current_taxonomy_hierarchical() ) {
                    var attribute = braapf_current_attribute();
                    if( jQuery('#braapf_parent_product_cat_'+attribute).length && jQuery('#braapf_parent_product_cat_'+attribute).val() ) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            jQuery(document).ready(function() {
                berocket_show_element('.braapf_depth_count', '!braapf_braapf_parent_product_cat_enabled! != "" && !braapf_braapf_parent_product_cat_enabled! != false');
            });
            </script>
            <?php
        echo '</div>';
    }
    public static function correct_terms_child_parent($terms, $instance, $get_terms_args = false, $get_terms_advanced = false, $set_query_var_title = array()) {
        if( isset($terms) && is_array($terms) && count( $terms ) < 1 && $instance['child_parent'] == 'child' ) {
            $terms = array('empty_child' => $get_terms_args);
            
        }
        return $terms;
    }
    public static function correct_terms_child_parent_fix($set_query_var_title, $type, $instance, $args, $terms) {
        if( $instance['child_parent'] == 'child' && count($terms) == 1 
        && array_key_exists('empty_child', $terms) ) {
            $selected_terms_id = false;
            $selected_terms = br_get_selected_term( $terms['empty_child']['taxonomy'] );
            
            foreach( $selected_terms as $selected_term ) {
                $ancestors = get_ancestors( $selected_term, $terms['empty_child']['taxonomy'] );
                if( count( $ancestors ) >= ( $set_query_var_title['child_parent_depth'] - 1 ) ) {
                    $selected_terms_id = true;
                    break;
                }
            }
            if( $selected_terms_id ) {
                $set_query_var_title['child_parent_previous'] = $set_query_var_title['child_parent_no_values'];
            }
            $set_query_var_title['terms'] = array();
            if( ! empty($set_query_var_title['child_parent_previous']) ) {
                set_query_var( 'berocket_query_var_title', $set_query_var_title);
                $BeRocket_AAPF = BeRocket_AAPF::getInstance();
                $BeRocket_AAPF->br_get_template_part('paid/child_empty');
            }
        }
        return $set_query_var_title;
    }
    static function enable_slider_inputs($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_enable_slider_inputs braapf_full_select_full">';
                $enable_slider_inputs = br_get_value_from_array($braapf_filter_settings, 'enable_slider_inputs', '0');
                echo '<p>';
                    echo '<input id="braapf_enable_slider_inputs" type="checkbox" name="' . $settings_name . '[enable_slider_inputs]"' . ( empty($enable_slider_inputs) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_enable_slider_inputs">'.__('Enable Slider input fields', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
    }
    static function search_field() {
        add_filter('braapf_new_widget_edit_page_widget_types', array(__CLASS__, 'widget_type_search_field'), 1, 1000); 
    }
    static function widget_type_search_field($widget_types) {
        $widget_types['search_field'] = array(
            'value' => 'search_field',
            'name'  => __('Search Field', 'BeRocket_AJAX_domain'),
            'image' => plugin_dir_url( BeRocket_AJAX_filters_file ) . 'paid/assets/search_field.png',
            'templates' => array('input'),
            'specific'  => array('elements'),
        );
        return $widget_types;
    }
    static function sortby_field() {
        add_filter('braapf_new_widget_edit_page_widget_types', array(__CLASS__, 'widget_type_sortby_field'), 1, 1001);
    }
    static function widget_type_sortby_field($widget_types) {
        $widget_types['sortby_field'] = array(
            'value' => 'sortby_field',
            'name'  => __('Sort By Field', 'BeRocket_AJAX_domain'),
            'image' => plugin_dir_url( BeRocket_AJAX_filters_file ) . 'paid/assets/sortby_field.png',
            'templates' => array('select', 'checkbox'),
        );
        return $widget_types;
    }
    public function display_cat_value_limit($hide) {
        return false;
    }
}
new BeRocket_AAPF_paid_new();
if( ! function_exists( 'br_permalink_input_section_echo' ) ){
    /**
     * Permalink block in settings
     *
     */
    function br_permalink_input_section_echo() {
        echo '<div>'.__('Nice URLs settings', 'BeRocket_AJAX_domain').'</div>';
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $BeRocket_AAPF->br_get_template_part( 'paid/permalink_option' );
    }
}
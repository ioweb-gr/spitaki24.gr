<?php
class BeRocket_AAPF_custom_sidebar {
    function __construct() {
        add_filter('brfr_data_ajax_filters', array($this, 'settings_page'), 25);
        add_filter('brfr_ajax_filters_custom_sidebar', array($this, 'section_custom_sidebar'), 25, 3);
        //CUSTOM SIDEBAR
        $this->custom_sidebar();
        //add_filter('widgets_init', array($this, 'custom_sidebar'));
        add_shortcode( 'braapf_sidebar_button', array( $this, 'shortcode_sidebar_button' ) );
        add_action( 'braapf_sidebar_button_show', array($this, 'custom_sidebar_toggle') );
        if( class_exists('DiviExtension') ) {
            $this->divi_extensions_init();
        }
        if ( ! is_admin() ) {
            add_action('wp_head', array( $this, 'wp_head_sidebar'));
            add_action('wp_footer', array( $this, 'wp_footer_sidebar' ), 1);
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            add_action( 'wp_enqueue_scripts', array( $BeRocket_AAPF, 'include_all_scripts' ) );
            add_filter('aapf_localize_widget_script', array( $this, 'add_javascript_option'), 10, 2);
        }
        add_filter( 'BeRocket_aapf_admin_bar_debug_html', array($this, 'admin_bar_html') );
        add_filter( 'BeRocket_aapf_admin_bar_debug_js', array($this, 'admin_bar_js') );
    }
    public function divi_extensions_init() {
        include_once dirname( __FILE__ ) . '/divi/includes/FiltersExtension.php';
    }
    public function wp_head_sidebar() {
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            if( apply_filters('braapf_sidebar_button_default_place', true) ) {
                add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'custom_sidebar_toggle'), 1 );
            }
            BeRocket_AAPF::require_all_scripts();
            do_action('br_footer_script');
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
    }
    public function shortcode_sidebar_button($args = array()) {
        ob_start();
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $this->custom_sidebar_toggle($args);
        }
        return ob_get_clean();
    }
    public function custom_sidebar_toggle($args = array()) {
        if( ! is_array($args) ) {
            $args = array();
        }
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        if( isset($option['theme']) ) {
            unset($option['theme']);
        }
        if( isset($option['icon-theme']) ) {
            unset($option['icon-theme']);
        }
        $args = array_merge($option, $args);
        $custom_sidebar_floating = ( empty($args['custom_sidebar_floating']) ? '0' : $args['custom_sidebar_floating']);
        if( $custom_sidebar_floating != 1 ) {
            if( $custom_sidebar_floating != 0 ) {
                if( $custom_sidebar_floating == 2 ) {
                    $classes_around = 'brapf_sidebar_float_mobile_hide';
                } else {
                    $classes_around = 'brapf_sidebar_float_desktop_hide';
                }
                echo '<p class="berocket_ajax_filters_sidebar_main ' . $classes_around . '">';
            }
            $this->display_button($args);
            if( $custom_sidebar_floating != 0 ) {
                echo '</p>';
            }
        }
        BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
    }
    public function display_button($args = array()) {
        if( ! is_array($args) ) {
            $args = array();
        }
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $button_text = (empty($args['title']) ? __( 'SHOW FILTERS', 'BeRocket_AJAX_domain' ) : $args['title']);
        $classes = array('berocket_ajax_filters_sidebar_toggle', 'berocket_ajax_filters_toggle');
        $classes[] = (empty($args['theme']) ? ( ( ! empty( $option['sidebar_collapse_theme'] ) ) ? 'theme-' . $option['sidebar_collapse_theme'] : '' ) : 'theme-'.$args['theme'] );
        $classes[] = (empty($args['icon-theme']) ? ( ( ! empty( $option['sidebar_collapse_icon_theme'] ) ) ? 'icon-theme-' . $option['sidebar_collapse_icon_theme'] : '' ) : 'icon-theme-'.$args['icon-theme'] );
        $classes = implode(' ', $classes);
        echo '<a href="#toggle-sidebar" class="' . $classes . '"><span><i></i><b></b><s></s></span>' . $button_text . '</a>';
    }
    public function css_class_build($args = array()) {
        $classes = array('berocket_ajax_filters_sidebar_back');
        $custom_sidebar_floating = ( empty($args['custom_sidebar_floating']) ? '0' : $args['custom_sidebar_floating']);
        if( $custom_sidebar_floating != 0 ) {
            if( $custom_sidebar_floating == 1 || $custom_sidebar_floating == 2 ) {
                $classes[] = 'brapf_sidebar_float_mobile';
            }
            if( $custom_sidebar_floating == 1 || $custom_sidebar_floating == 3 ) {
                $classes[] = 'brapf_sidebar_float_desktop';
            }
            $custom_sidebar_floating_position = ( empty($args['custom_sidebar_floating_position']) ? '0' : $args['custom_sidebar_floating_position']);
            $classes[] = ($custom_sidebar_floating_position == 0 ? 'brapf_sidefl_top' : 'brapf_sidefl_bottom');
            $custom_sidebar_floating_position_horizontal = ( empty($args['custom_sidebar_floating_position_horizontal']) ? '0' : $args['custom_sidebar_floating_position_horizontal']);
            $isline = false;
            switch($custom_sidebar_floating_position_horizontal) {
                case 0:
                    $classes[] = 'brapf_sidefl_left';
                    break;
                case 1:
                    $classes[] = 'brapf_sidefl_right';
                    break;
                case 2:
                    $classes[] = 'brapf_sidefl_left_line';
                    $isline = true;
                    break;
                case 3:
                    $classes[] = 'brapf_sidefl_right_line';
                    $isline = true;
                    break;
            }
            if( $isline ) {
                $custom_sidebar_floating_line_style = ( empty($args['custom_sidebar_floating_line_style']) ? '0' : $args['custom_sidebar_floating_line_style']);
                switch($custom_sidebar_floating_line_style) {
                    case 0:
                        $classes[] = 'brapf_sidefl_linewht';
                        break;
                    case 1:
                        $classes[] = 'brapf_sidefl_lineclr';
                        break;
                    case 2:
                        $classes[] = 'brapf_sidefl_linegrey';
                        break;
                }
            }
        }
        
        return implode(' ', $classes);
    }
    function settings_page($data) {
        $data['Design'] = berocket_insert_to_array(
            $data['Design'],
            'header_part_tooltip',
            array(
                'header_part_custom_sidebar' => array(
                    'section' => 'header_part',
                    "value"   => __('Custom Sidebar Styles', 'BeRocket_AJAX_domain'),
                ),
                'custom_sidebar' => array(
                    "section"   => "custom_sidebar",
                    "value"     => "",
                )
            ),
            true
        );
        $data['Advanced'] = berocket_insert_to_array(
            $data['Advanced'],
            'header_part_fixes',
            array(
                'header_part_custom_sidebar' => array(
                    'section' => 'header_part',
                    "value"   => __('Custom Sidebar Styles', 'BeRocket_AJAX_domain'),
                ),
                'custom_sidebar_close' => array(
                    "label"     => __( 'Close Sidebar After Filtering', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "custom_sidebar_close",
                    "value"     => '1',
                    'label_for' => __('Close sidebar when filters applied.', 'BeRocket_AJAX_domain'),
                ),
                'custom_sidebar_floating' => array(
                    "label"     => __( 'Open Sidebar Button Floating', "BeRocket_AJAX_domain" ),
                    "name"      => "custom_sidebar_floating",
                    "type"      => "selectbox",
                    "options"   => array(
                        array('value' => '0', 'text' => __('Disable', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('Mobile and Desktop', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Mobile', 'BeRocket_AJAX_domain')),
                        array('value' => '3', 'text' => __('Desktop', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '0',
                    'class'     => 'brapf_custom_sidebar_floating',
                ),
                'custom_sidebar_floating_position' => array(
                    "label"     => __( 'Open Sidebar Button Floating Position', "BeRocket_AJAX_domain" ),
                    "name"     => "custom_sidebar_floating_position",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '0', 'text' => __('Top', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('Bottom', 'BeRocket_AJAX_domain')),
                    ),
                    'tr_class'  => 'brapf_custom_sidebar_floating_hide',
                    "value"    => '0',
                ),
                'custom_sidebar_floating_margin' => array(
                    "label"     => __( 'Open Sidebar Button Floating Margin', "BeRocket_AJAX_domain" ),
                    "name"      => "custom_sidebar_floating_margin",
                    "type"      => "number",
                    'tr_class'  => 'brapf_custom_sidebar_floating_hide',
                    "value"     => '0',
                    'extra'     => 'min="0"',
                    "label_for" => "px",
                ),
                'custom_sidebar_floating_position_horizontal' => array(
                    "label"     => __( 'Open Sidebar Button Floating Position Horizontal', "BeRocket_AJAX_domain" ),
                    "name"     => "custom_sidebar_floating_position_horizontal",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '0', 'text' => __('Left', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('Right', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Left Line', 'BeRocket_AJAX_domain')),
                        array('value' => '3', 'text' => __('Right Line', 'BeRocket_AJAX_domain')),
                    ),
                    'tr_class'  => 'brapf_custom_sidebar_floating_hide',
                    'class'     => 'custom_sidebar_floating_position_horizontal',
                    "value"    => '0',
                ),
                'custom_sidebar_floating_line_style' => array(
                    "label"     => __( 'Open Sidebar Button Floating Line Style', "BeRocket_AJAX_domain" ),
                    "name"     => "custom_sidebar_floating_line_style",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '0', 'text' => __('White line', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('Color Gradient', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Grey gradient', 'BeRocket_AJAX_domain')),
                    ),
                    'tr_class'  => 'brapf_custom_sidebar_floating_hide_line',
                    "value"    => '0',
                ),
            ),
            true
        );
        return $data;
    }
    public function section_custom_sidebar() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $html = '
            </table>
            <table class="form-table">
                <tbody>
                    <tr class="berocket_group_is_hide_theme_option_data">
                        <th class="row">' . __('Collapse Button style', 'BeRocket_AJAX_domain') . '</th>
                        <td>
                            <div class="berocket_group_is_hide_theme_option_slider">';
                                $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_theme]" style="display:none!important;" id="sidebar_collapse_theme_" value="" ' . ( empty( $options['sidebar_collapse_theme'] ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_theme_"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button/default.png" /></label>
                                    </div>';
                                for ( $theme_key = 1; $theme_key <= 10; $theme_key++ ) {
                                    $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_theme]" style="display:none!important;" id="sidebar_collapse_theme_' . $theme_key . '" value="' . $theme_key . '" ' . ( ( ! empty( $options['sidebar_collapse_theme'] ) and $options['sidebar_collapse_theme'] == $theme_key ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_theme_' . $theme_key . '"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button/' . $theme_key . '.png" /></label>
                                    </div>';
                                }
                                $html .= '
                            </div>
                        </td>
                    </tr>
                    <tr class="berocket_group_is_hide_theme_option_data">
                        <th class="row">' . __('Collapse Button Icon style', 'BeRocket_AJAX_domain') . '</th>
                        <td>
                            <div class="berocket_group_is_hide_theme_option_slider icon_size">';
                                $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_icon_theme]" style="display:none!important;" id="sidebar_collapse_icon_theme_" value="" ' . ( empty( $options['sidebar_collapse_icon_theme'] ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_icon_theme_"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button-icon/default.png" /></label>
                                    </div>';
                                for ( $theme_key = 1; $theme_key <= 6; $theme_key++ ) {
                                    $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_icon_theme]" style="display:none!important;" id="sidebar_collapse_icon_theme_' . $theme_key . '" value="' . $theme_key . '" ' . ( ( ! empty( $options['sidebar_collapse_icon_theme'] ) and $options['sidebar_collapse_icon_theme'] == $theme_key ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_icon_theme_' . $theme_key . '"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button-icon/' . $theme_key . '.png" /></label>
                                    </div>';
                                }
                                $html .= '
                            </div>
                        </td>
                    </tr>
                    <tr class="berocket_group_is_hide_theme_option_data">
                        <th class="row">' . __('Sidebar Shadow', 'BeRocket_AJAX_domain') . '</th>
                        <td>
                            <div class="berocket_group_is_hide_theme_option_slider slider_shadow">';
                                $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_shadow_theme]" style="display:none!important;" id="sidebar_shadow_theme_" value="" ' . ( empty( $options['sidebar_shadow_theme'] ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_shadow_theme_"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-shadow/default.png" />
                                        <span>'.__('Dark', 'BeRocket_AJAX_domain').'</span></label>
                                    </div>';
                                $shadow_themes = array(
                                    '1' => __('No Shadow', 'BeRocket_AJAX_domain'),
                                    '2' => __('White', 'BeRocket_AJAX_domain'),
                                );
                                foreach($shadow_themes as $theme_key => $theme_name) {
                                    $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_shadow_theme]" style="display:none!important;" id="sidebar_shadow_theme_' . $theme_key . '" value="' . $theme_key . '" ' . ( ( ! empty( $options['sidebar_shadow_theme'] ) and $options['sidebar_shadow_theme'] == $theme_key ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_shadow_theme_' . $theme_key . '"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-shadow/' . $theme_key . '.png" />
                                        <span>'.$theme_name.'</span></label>
                                    </div>';
                                }
                                $html .= '
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="framework-form-table berocket_framework_menu_design">
            <script>
                function brapf_custom_sidebar_floating_hide() {
                    if( jQuery(".brapf_custom_sidebar_floating").val() == "0" ) {
                        jQuery(".brapf_custom_sidebar_floating_hide").hide();
                    } else {
                        jQuery(".brapf_custom_sidebar_floating_hide").show();
                    }
                }
                jQuery(document).ready(brapf_custom_sidebar_floating_hide);
                jQuery(document).on("change", ".brapf_custom_sidebar_floating", brapf_custom_sidebar_floating_hide);
                function brapf_custom_sidebar_floating_hide_line() {
                    if( jQuery(".brapf_custom_sidebar_floating").val() == "0" || jQuery(".custom_sidebar_floating_position_horizontal").val() == "0" || jQuery(".custom_sidebar_floating_position_horizontal").val() == "1" ) {
                        jQuery(".brapf_custom_sidebar_floating_hide_line").hide();
                    } else {
                        jQuery(".brapf_custom_sidebar_floating_hide_line").show();
                    }
                }
                jQuery(document).ready(brapf_custom_sidebar_floating_hide_line);
                jQuery(document).on("change", ".brapf_custom_sidebar_floating", brapf_custom_sidebar_floating_hide_line);
                jQuery(document).on("change", ".custom_sidebar_floating_position_horizontal", brapf_custom_sidebar_floating_hide_line);
            </script>';
        return $html;
    }

    public function custom_sidebar() {
        register_sidebar(
            array (
                'name' => __( 'BeRocket AJAX Filters', 'BeRocket_AJAX_domain' ),
                'id' => 'berocket-ajax-filters',
                'description' => __( 'Sidebar for BeRocket AJAX Filters', 'BeRocket_AJAX_domain' ),
                'before_widget' => '<div class="berocket-widget-content">',
                'after_widget' => "</div>",
                'before_title' => '<h3 class="berocket-widget-title">',
                'after_title' => '</h3>',
            )
        );
    }
    public function wp_footer_sidebar() {
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            wp_enqueue_script( 'braapf_custom_sidebar',
            plugins_url( 'js/custom_sidebar.js', __FILE__ ),
            array( 'jquery' ),
            BeRocket_AJAX_filters_version );
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $options = $BeRocket_AAPF->get_option();
            echo "<div id='berocket-ajax-filters-sidebar' class='" . ( ! empty( $options['sidebar_shadow_theme'] ) ? 'sidebar-theme-' . $options['sidebar_shadow_theme'] : '' ) . "'>";
            echo "<a href='#close-sidebar' id='berocket-ajax-filters-sidebar-close'>" . __('Close &#10005;', 'BeRocket_AJAX_domain') . "</a>";
            dynamic_sidebar( 'berocket-ajax-filters' );
            echo "</div>";
            echo "<div id='berocket-ajax-filters-sidebar-shadow'></div>";

            $args = array();
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            $args = array_merge($option, $args);
            $custom_sidebar_floating = ( empty($args['custom_sidebar_floating']) ? '0' : $args['custom_sidebar_floating']);
            if( $custom_sidebar_floating != 0 ) {
                $classes_around = $this->css_class_build($args);
                $margin = intval( empty($args['custom_sidebar_floating_margin']) ? '0' : $args['custom_sidebar_floating_margin']);
                if( $margin == 0 ) {
                    $margin = '';
                } else {
                    if( empty($args['custom_sidebar_floating_position']) ) {
                        $margin = ' style="margin-top:' . $margin . 'px;"';
                    } else {
                        $margin = ' style="margin-bottom:' . $margin . 'px;"';
                    }
                }
                echo '<p class="' . $classes_around . '"' . $margin . '>';
                $this->display_button($args);
                echo '</p>';
            }
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
    }
    public function add_javascript_option($the_ajax_script, $option) {
        $the_ajax_script['custom_sidebar_close'] = ! empty($option['custom_sidebar_close']);
        return $the_ajax_script;
    }
    public function admin_bar_html($html) {
        $html .= '<div class="bapf_adminbar_custom_sidebar">';
        $html .= '</div>';
        return $html;
    }
    public function admin_bar_js($html) {
        $html .= '<script>';
        $html .= '
        jQuery(document).on("click", ".bapf_adminbar_custom_sidebar_open", function(e) {e.preventDefault(); jQuery(document).trigger("berocket_custom_sidebar_open");});
        jQuery(document).ready(function() {
            var html = "<h2>CUSTOM SIDEBAR</h2>";
            var is_sidebar = false, is_sidebar_button = false;
            if( jQuery("#berocket-ajax-filters-sidebar").length ) {
                is_sidebar = true;
            }
            if( jQuery(".berocket_ajax_filters_sidebar_toggle").length ) {
                is_sidebar_button = true;
            }
            html += "<div class=\'bapf_adminbar_status_element\'>Sidebar";
            if( is_sidebar ) {
                html += "<span class=\'dashicons dashicons-yes\' title=\'Yes, page has sidebar\'></span>";
            } else {
                html += "<span class=\'dashicons dashicons-no\' title=\'No, sidebar do not displayed on page\'></span>";
            }
            html += "</div>";
            html += "<div class=\'bapf_adminbar_status_element\'>Button";
            if( is_sidebar_button ) {
                html += "<span class=\'dashicons dashicons-yes\' title=\'Yes, page has button to open sidebar\'></span>";
            } else {
                html += "<span class=\'dashicons dashicons-no\' title=\'No, page do not have any element to open sidebar\'></span>";
            }
            html += "</div>";
            html += "<div><a class=\'bapf_adminbar_custom_sidebar_open\'href=\'#open_sidebar\'>Open Sidebar</a></div>";
            jQuery(".bapf_adminbar_custom_sidebar").html(html);
        });
        ';
        $html .= 
        $html .= '</script>';
        return $html;
    }
}
new BeRocket_AAPF_custom_sidebar();

<?php
class braapf_paid_divi_module {
    function __construct() {
        add_filter('ET_Builder_Module_br_filters_group_fields', array($this, 'fields'));
    }
    function fields($fields) {
        $fields['display_inline'] = array(
            'label'             => esc_html__( 'Display filters in line', 'BeRocket_AJAX_domain' ),
            'type'              => 'yes_no_button',
            'options'           => array(
                'off' => esc_html__( "No", 'et_builder' ),
                'on'  => esc_html__( 'Yes', 'et_builder' ),
            ),
            'show_if'           => array(
                'group_id' => '0',
            )
        );
        $fields['display_inline_count'] = array(
            'label'           => esc_html__( 'Display filters in line max count', 'BeRocket_AJAX_domain' ),
            'type'            => 'select',
            'options'         => array(
                'off' => 'Default',
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
            ),
            'show_if'           => array(
                'group_id' => '0',
                'display_inline' => 'on',
            )
        );
        $fields['min_filter_width_inline'] = array(
            'label'           => esc_html__( 'Min Width for Filter', 'BeRocket_AJAX_domain' ). ' (px)',
            'type'            => 'number',
            'show_if'           => array(
                'group_id' => '0',
                'display_inline' => 'on',
            )
        );
        $fields['hidden_clickable'] = array(
            'label'           => esc_html__( 'Show title only', 'BeRocket_AJAX_domain' ),
            'type'              => 'yes_no_button',
            'options'           => array(
                'off' => esc_html__( "No", 'et_builder' ),
                'on'  => esc_html__( 'Yes', 'et_builder' ),
            ),
            'show_if'           => array(
                'group_id' => '0',
            )
        );
        $fields['hidden_clickable_hover'] = array(
            'label'           => esc_html__( 'Display filters on mouse over', 'BeRocket_AJAX_domain' ),
            'type'              => 'yes_no_button',
            'options'           => array(
                'off' => esc_html__( "No", 'et_builder' ),
                'on'  => esc_html__( 'Yes', 'et_builder' ),
            ),
            'show_if'           => array(
                'hidden_clickable' => 'on',
                'group_id' => '0',
            )
        );
        $fields['group_is_hide'] = array(
            'label'           => esc_html__( 'Collapsed on page load', 'BeRocket_AJAX_domain' ),
            'type'              => 'yes_no_button',
            'options'           => array(
                'off' => esc_html__( "No", 'et_builder' ),
                'on'  => esc_html__( 'Yes', 'et_builder' ),
            ),
            'show_if'           => array(
                'group_id' => '0',
            )
        );
        $fields['group_is_hide_theme'] = array(
            'label'           => esc_html__( 'Collapse Button style', 'BeRocket_AJAX_domain' ),
            'type'            => 'select',
            'options'         => array(
                'off' => 'Default',
                '1' => 'Style 1',
                '2' => 'Style 2',
                '3' => 'Style 3',
                '4' => 'Style 4',
                '5' => 'Style 5',
                '6' => 'Style 6',
                '7' => 'Style 7',
                '8' => 'Style 8',
                '9' => 'Style 9',
                '10' => 'Style 10',
            ),
            'show_if'           => array(
                'group_id' => '0',
                'group_is_hide' => 'on',
            )
        );
        $fields['group_is_hide_icon_theme'] = array(
            'label'           => esc_html__( 'Collapse Button Icon style', 'BeRocket_AJAX_domain' ),
            'type'            => 'select',
            'options'         => array(
                'off' => 'Default',
                '1' => 'Icon 1',
                '2' => 'Icon 2',
                '3' => 'Icon 3',
                '4' => 'Icon 4',
                '5' => 'Icon 5',
                '6' => 'Icon 6',
            ),
            'show_if'           => array(
                'group_id' => '0',
                'group_is_hide' => 'on',
            )
        );
        return $fields;
    }        
}
new braapf_paid_divi_module();
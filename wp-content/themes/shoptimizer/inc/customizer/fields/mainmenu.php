<?php
/**
 *
 * Main menu theme options
 *
 * @package CommerceGurus
 * @subpackage shoptimizer
 */

// Main Menu.
$shoptimizer_default_options = shoptimizer_get_option_defaults();

// Display top bar.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_layout_top_bar_display',
		'label'       => esc_html__( 'Display top bar?', 'shoptimizer' ),
		'description' => esc_html__( 'Enable or disable the top bar', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_top_bar',
		'default'     => $shoptimizer_default_options['shoptimizer_layout_top_bar_display'],
		'priority'    => 10,
		'transport'   => 'refresh',
		'choices'     => array(
			'enable'  => esc_html__( 'Enable', 'shoptimizer' ),
			'disable' => esc_html__( 'Disable', 'shoptimizer' ),
		),
	)
);

// Show or hide top bar on mobile.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_layout_top_bar_mobile',
		'label'       => esc_html__( 'Hide top bar on mobile?', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_top_bar',
		'default'     => $shoptimizer_default_options['shoptimizer_layout_top_bar_mobile'],
		'priority'    => 10,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_layout_top_bar_display',
				'value'    => 'enable',
				'operator' => '==',
			),
		),
		'transport'   => 'refresh',
		'choices'     => array(
			'show'  => esc_html__( 'Show', 'shoptimizer' ),
			'hide' => esc_html__( 'Hide', 'shoptimizer' ),
		),
	)
);

// Top bar padding.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_top_bar_padding',
		'label'       => esc_html__( 'Top bar padding', 'shoptimizer' ),
		'description' => esc_html__( 'Adjusts the top and bottom padding.', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_top_bar',
		'default'     => 8,
		'choices'     => array(
			'min'  => 0,
			'max'  => 50,
			'step' => 1,
		),
		'output'      => array(
			array(
				'element'  => '.top-bar .textwidget',
				'property' => 'padding-top',
				'units'    => 'px',
				'media_query' => '@media (min-width: 992px)',
			),
			array(
				'element'  => '.top-bar .textwidget',
				'property' => 'padding-bottom',
				'units'    => 'px',
				'media_query' => '@media (min-width: 992px)',
			),
		),
	)
);

// Top bar font size.
shoptimizer_Kirki::add_field(
	'shoptimizer_config',
	array(
		'type'     => 'typography',
		'settings' => 'shoptimizer_top_bar_font_size',
		'label'    => esc_html__( 'Top bar font size', 'shoptimizer' ),
		'section'  => 'shoptimizer_header_section_top_bar',
		'default'  => array(
			'font-size'      => '14px',
		),
		'priority' => 10,
		'output'   => array(
			array(
				'element'  => '.top-bar',
				'property' => 'font-size',
			),
		),
	)
);


// Header Layout.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_header_layout',
		'label'       => esc_html__( 'Header Layout', 'shoptimizer' ),
		'description' => esc_html__( 'Change the header layout', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => $shoptimizer_default_options['shoptimizer_header_layout'],
		'priority'    => 10,
		'transport'   => 'refresh',
		'choices'     => array(
			'default'  => esc_html__( 'Logo / Search / Secondary', 'shoptimizer' ),
			'header-5' => esc_html__( 'Logo / Search / Secondary / Cart', 'shoptimizer' ),
			'header-2' => esc_html__( 'Search / Logo / Secondary', 'shoptimizer' ),
			'header-3' => esc_html__( 'Secondary / Logo / Search', 'shoptimizer' ),
			'header-4' => esc_html__( 'Logo / Navigation / Cart', 'shoptimizer' ),			
		),
	)
);

// Header Layout Contained or Full Width
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_header_layout_container',
		'label'       => esc_html__( 'Header Container', 'shoptimizer' ),
		'description' => esc_html__( 'Change the header container', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => $shoptimizer_default_options['shoptimizer_header_layout_container'],
		'priority'    => 10,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '==',
			),
		),
		'transport'   => 'refresh',
		'choices'     => array(
			'contained'  => esc_html__( 'Contained', 'shoptimizer' ),
			'full-width-header' => esc_html__( 'Full width', 'shoptimizer' ),
		),
	)
);


// Header Padding Top.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_header_top_padding',
		'label'       => esc_html__( 'Header Top Padding', 'shoptimizer' ),
		'description' => esc_html__( 'Adjust the header top padding', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => 30,
		'priority'    => 1,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '!=',
			),
		),
		'choices'     => array(
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		),
		'output'      => array(
			array(
				'element'     => '.col-full.main-header',
				'property'    => 'padding-top',
				'units'       => 'px',
				'media_query' => '@media (min-width: 993px)',
			),

		),
	)
);

// Header Padding Bottom.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_header_bottom_padding',
		'label'       => esc_html__( 'Header Bottom Padding', 'shoptimizer' ),
		'description' => esc_html__( 'Adjust the header bottom padding', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => 30,
		'priority'    => 1,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '!=',
			),
		),
		'choices'     => array(
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		),
		'output'      => array(
			array(
				'element'     => '.col-full.main-header',
				'property'    => 'padding-bottom',
				'units'       => 'px',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Header Height - Only for header-4 layout.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_header_height',
		'label'       => esc_html__( 'Header Height', 'shoptimizer' ),
		'description' => esc_html__( 'Adjust the header height', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => 90,
		'priority'    => 1,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '==',
			),
		),
		'choices'     => array(
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		),
		'output'      => array(
			array(
				'element'     => '.header-4 .header-4-container',
				'property'    => 'height',
				'units'       => 'px',
				'media_query' => '@media (min-width: 993px)',
			),
			array(
				'element'     => '.header-4 .menu-primary-menu-container > ul > li > a, .header-4 .menu-primary-menu-container > ul > li.nolink, .header-4 .search-trigger',
				'property'    => 'line-height',
				'units'       => 'px',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Display the search.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_layout_search_display',
		'label'       => esc_html__( 'Display the search?', 'shoptimizer' ),
		'description' => esc_html__( 'Enable or disable the search. (Ajaxify your product search in CommerceKit!)', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => $shoptimizer_default_options['shoptimizer_layout_search_display'],
		'priority'    => 10,
		'transport'   => 'refresh',
		'choices'     => array(
			'enable'  => esc_html__( 'Product Search', 'shoptimizer' ),
			'advanced-woo-search'  => esc_html__( 'Advanced Woo Search Plugin', 'shoptimizer' ),
			'ajax-search-wc'  => esc_html__( 'FiboSearch Plugin', 'shoptimizer' ),
			'smart-search-pro'  => esc_html__( 'Smart Search PRO Plugin', 'shoptimizer' ),
			'yith-search'  => esc_html__( 'YITH WooCommerce Ajax Search Plugin', 'shoptimizer' ),
			'regular'  => esc_html__( 'Regular Search', 'shoptimizer' ),
			'disable' => esc_html__( 'Disable', 'shoptimizer' ),
		),
	)
);

// Search title. Only if header-4 is selected.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'text',
		'settings'  => 'shoptimizer_layout_search_title',
		'label'     => esc_html__( 'Search modal title', 'shoptimizer' ),
		'section'   => 'shoptimizer_header_section_layout',
		'default'   => $shoptimizer_default_options['shoptimizer_layout_search_title'],
		'priority'  => 10,
		'transport' => 'auto',
		'active_callback'  => [
			[
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '==',
			],
		],	
		'js_vars'   => array(
			array(
				'element'  => '.search-modal-heading',
				'function' => 'html',
			),
		),
	)
);

// Search style.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'     => 'select',
		'settings' => 'shoptimizer_layout_search_display_type',
		'label'    => esc_attr__( 'Search design', 'shoptimizer' ),
		'section'  => 'shoptimizer_header_section_layout',
		'default'  => $shoptimizer_default_options['shoptimizer_layout_search_display_type'],
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_layout_search_display',
				'value'    => 'disable',
				'operator' => '!==',
			),
		),
		'choices'  => array(
			'default' => esc_attr__( 'Filled', 'shoptimizer' ),
			'outline'  => esc_attr__( 'Outline', 'shoptimizer' ),
		),
		'priority' => 10,
	)
);

// Display My Account icon on desktop.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'select',
		'settings'  => 'shoptimizer_layout_myaccount_display',
		'label'     => esc_html__( 'Display account icon on desktop', 'shoptimizer' ),
		'section'   => 'shoptimizer_header_section_layout',
		'default'  => $shoptimizer_default_options['shoptimizer_layout_myaccount_display'],
		'choices'  => array(
			'enable' => esc_attr__( 'Enable', 'shoptimizer' ),
			'disable'  => esc_attr__( 'Disable', 'shoptimizer' ),

		),
		'priority' => 10,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '==',
			),
		),
	)
);


// Navigation Height.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_navigation_height',
		'label'       => esc_html__( 'Navigation Height', 'shoptimizer' ),
		'description' => esc_html__( 'Adjust the navigation height', 'shoptimizer' ),
		'section'     => 'shoptimizer_navigation_section_layout',
		'default'     => 60,
		'priority'    => 1,
		'active_callback'  => [
			[
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '!=',
			],
		],	
		'choices'     => array(
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		),
		'output'      => array(
			array(
				'element'  => '.menu-primary-menu-container > ul > li > a, .menu-primary-menu-container > ul > li.nolink > span, .site-header-cart, .logo-mark',
				'property' => 'line-height',
				'units'    => 'px',
				'media_query' => '@media (min-width: 993px)',
			),
			array(
				'element'  => '.site-header-cart, .menu-primary-menu-container > ul > li.menu-button',
				'property' => 'height',
				'units'    => 'px',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Enable hover intent.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'toggle',
		'settings'  => 'shoptimizer_menu_hover_intent',
		'label'     => esc_html__( 'Enable hover intent', 'shoptimizer' ),
		'description' => esc_html__( 'Tracks cursor movement to interpret when it is likely a user intended to hover over menu', 'shoptimizer' ),
		'section'   => 'shoptimizer_navigation_section_layout',
		'default'   => 0,
		'priority'  => 10,
		'transport' => 'refresh',
	)
);


// Display menu descriptions
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'toggle',
		'settings'  => 'shoptimizer_menu_display_description',
		'label'     => esc_html__( 'Display menu descriptions', 'shoptimizer' ),
		'section'   => 'shoptimizer_navigation_section_layout',
		'default'   => 0,
		'priority'  => 10,
		'transport' => 'refresh',
	)
);


// Sticky Navigation.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_sticky_header',
		'label'       => esc_html__( 'Sticky Navigation', 'shoptimizer' ),
		'description' => esc_html__( 'Stick the navigation on scroll', 'shoptimizer' ),
		'section'     => 'shoptimizer_header_section_layout',
		'default'     => $shoptimizer_default_options['shoptimizer_sticky_header'],
		'priority'    => 10,
		'transport'   => 'refresh',
		'choices'     => array(
			'enable'  => esc_html__( 'Enable', 'shoptimizer' ),
			'disable' => esc_html__( 'Disable', 'shoptimizer' ),
		),
	)
);

// Mobile Sticky Header
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'     => 'select',
		'settings' => 'shoptimizer_sticky_mobile_header',
		'label'    => esc_attr__( 'Mobile Sticky Header', 'shoptimizer' ),
		'section'  => 'shoptimizer_section_general_mobile_header',
		'default'  => $shoptimizer_default_options['shoptimizer_sticky_mobile_header'],
		'choices'  => array(
			'enable' => esc_attr__( 'Enable', 'shoptimizer' ),
			'disable'  => esc_attr__( 'Disable', 'shoptimizer' ),

		),
		'priority' => 10,
	)
);


// Main Navigation Links Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_navigation_color',
		'label'     => esc_html__( 'Navigation links', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_navigation_color'],
		'priority'  => 10,
		'active_callback'  => [
			[
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '!=',
			],
		],	
		'output'    => array(
			array(
				'element'     => '.menu-primary-menu-container > ul > li > a, .menu-primary-menu-container > ul > li.nolink > span',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
			array(
				'element'     => '.main-navigation ul.menu > li.menu-item-has-children > a::after',
				'property'    => 'background-color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'     => '.menu-primary-menu-container > ul > li > a, .menu-primary-menu-container > ul > li.nolink > span',
				'function'    => 'css',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
			array(
				'element'     => '.main-navigation ul.menu > li.menu-item-has-children > a::after',
				'property'    => 'background-color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Header 4 (One row) Navigation Links Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_navigation_color_header_4',
		'label'     => esc_html__( 'Navigation links', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_navigation_color_header_4'],
		'priority'  => 10,
		'active_callback'  => [
			[
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '==',
			],
		],	
		'output'    => array(
			array(
				'element'     => '.header-4 .menu-primary-menu-container > ul > li > a, .header-4 .shoptimizer-cart .cart-contents .amount, .header-4 .search-trigger, .header-4 .search-trigger:hover, .header-4 .search-trigger:focus, .shoptimizer-myaccount a, .shoptimizer-myaccount a:hover',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
			array(
				'element'     => '.header-4 .main-navigation ul.menu > li.menu-item-has-children > a::after, .header-4 .main-navigation ul.menu > li.page_item_has_children > a::after, .header-4 .main-navigation ul.nav-menu > li.menu-item-has-children > a::after, .header-4 .main-navigation ul.nav-menu > li.page_item_has_children > a::after',
				'property'    => 'background-color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'     => '.header-4 .menu-primary-menu-container > ul > li > a, .header-4 .shoptimizer-cart .cart-contents .amount, .header-4 .search-trigger, .header-4 .search-trigger:hover, .header-4 .search-trigger:focus, .shoptimizer-myaccount a, .shoptimizer-myaccount a:hover',
				'function'    => 'css',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
			array(
				'element'     => '.header-4 .main-navigation ul.menu > li.menu-item-has-children > a::after, .header-4 .main-navigation ul.menu > li.page_item_has_children > a::after, .header-4 .main-navigation ul.nav-menu > li.menu-item-has-children > a::after, .header-4 .main-navigation ul.nav-menu > li.page_item_has_children > a::after',
				'property'    => 'background-color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Main Navigation Links Hover/Selected Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_navigation_color_hover',
		'label'     => esc_html__( 'Navigation links hover/selected', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_navigation_color_hover'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'  => '.menu-primary-menu-container > ul > li > a span:before, .menu-primary-menu-container > ul > li.nolink > span:before',
				'property' => 'border-color',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.menu-primary-menu-container > ul > li > a span:before, .menu-primary-menu-container > ul > li.nolink > span:before',
				'property' => 'border-color',
			),
		),
	)
);

// Fade out other menu items on hover.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_navigation_color_other_hover',
		'label'       => esc_html__( 'Fade out other links on hover', 'shoptimizer' ),
		'description' => esc_html__( 'Opacity (%).', 'shoptimizer' ),
		'section'     => 'shoptimizer_color_section_navigation',
		'default'     => 0.65,
		'priority'    => 1,
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_header_layout',
				'value'    => 'header-4',
				'operator' => '!=',
			),
		),
		'choices'     => array(
			'min'  => 0,
			'max'  => 1,
			'step' => 0.01,
		),
		'output'      => array(
			array(
				'element'  => '.menu-primary-menu-container > ul.menu:hover > li > a',
				'property' => 'opacity',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);


shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'     => 'custom',
		'settings' => 'shoptimizer_colors_navigation_heading_1',
		'section'  => 'shoptimizer_color_section_navigation',
		'default'  => '<div class="kirki-separator" 
	style="margin: 10px -12px;
	padding: 12px 12px;
	color: #111;
	text-transform: uppercase;
	letter-spacing: 1px;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
	background-color: #fff;
	cursor: default;">' . esc_html__( 'Dropdowns', 'shoptimizer' ) . '</div>',
		'priority' => 10,
	)
);


// Navigation Dropdown Background Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_navigation_dropdown_background',
		'label'     => esc_html__( 'Navigation dropdown background', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_navigation_dropdown_background'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'     => '.main-navigation ul.menu ul.sub-menu',
				'property'    => 'background-color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'     => '.main-navigation ul.menu ul.sub-menu',
				'function'    => 'css',
				'property'    => 'background-color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Navigation Dropdown Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_navigation_dropdown_color',
		'label'     => esc_html__( 'Navigation dropdown text', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_navigation_dropdown_color'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'     => '.main-navigation ul.menu ul li a, .main-navigation ul.nav-menu ul li a',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'     => '.main-navigation ul.menu ul li a, .main-navigation ul.nav-menu ul li a',
				'function'    => 'css',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Main Navigation Dropdown Hover Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_navigation_dropdown_hover_color',
		'label'     => esc_html__( 'Navigation dropdown hover', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_navigation_dropdown_hover_color'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'     => '.main-navigation ul.menu ul li.menu-item:not(.menu-item-image):not(.heading) > a:hover',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'     => '.main-navigation ul.menu ul li.menu-item:not(.menu-item-image):not(.heading) > a:hover',
				'function'    => 'css',
				'property'    => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'     => 'custom',
		'settings' => 'shoptimizer_colors_navigation_heading_2',
		'section'  => 'shoptimizer_color_section_navigation',
		'default'  => '<div class="kirki-separator" 
	style="margin: 10px -12px;
	padding: 12px 12px;
	color: #111;
	text-transform: uppercase;
	letter-spacing: 1px;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
	background-color: #fff;
	cursor: default;">' . esc_html__( 'Secondary Navigation', 'shoptimizer' ) . '</div>',
		'priority' => 10,
	)
);


// Secondary Navigation Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_secondary_navigation_color',
		'label'     => esc_html__( 'Secondary navigation color', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_secondary_navigation_color'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'  => '.secondary-navigation .menu a, .ri.menu-item:before, .fa.menu-item:before',
				'property' => 'color',
			),
			array(
				'element'  => '.secondary-navigation .icon-wrapper svg',
				'property' => 'stroke',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.secondary-navigation .menu a, .ri.menu-item:before, .fa.menu-item:before',
				'function' => 'css',
				'property' => 'color',
			),
			array(
				'element'  => '.secondary-navigation .icon-wrapper svg',
				'property' => 'stroke',
			),
		),
	)
);

shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'     => 'custom',
		'settings' => 'shoptimizer_colors_navigation_heading_3',
		'section'  => 'shoptimizer_color_section_navigation',
		'default'  => '<div class="kirki-separator" 
	style="margin: 10px -12px;
	padding: 12px 12px;
	color: #111;
	text-transform: uppercase;
	letter-spacing: 1px;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
	background-color: #fff;
	cursor: default;">' . esc_html__( 'Cart', 'shoptimizer' ) . '</div>',
		'priority' => 10,
	)
);

// Navigation Cart Icon Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_cart_icon_color',
		'label'     => esc_html__( 'Cart icon', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_icon_color'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .count, .shoptimizer-cart a.cart-contents .count:after',
				'property' => 'border-color',
			),
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .count, .shoptimizer-cart-icon i',
				'property' => 'color',
			),
			array(
				'element'  => '.shoptimizer-cart a.cart-contents:hover .count, .shoptimizer-cart a.cart-contents:hover .count',
				'property' => 'background-color',
			),
			array(
				'element'  => '.shoptimizer-cart-icon svg',
				'property' => 'stroke',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .count, .shoptimizer-cart a.cart-contents .count:after',
				'function' => 'css',
				'property' => 'border-color',
			),
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .count, .shoptimizer-cart-icon i',
				'property' => 'color',
			),
			array(
				'element'  => '.shoptimizer-cart a.cart-contents:hover .count, .shoptimizer-cart a.cart-contents:hover .count',
				'property' => 'background-color',
			),
			array(
				'element'  => '.shoptimizer-cart-icon svg',
				'property' => 'stroke',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Navigation Cart Text Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_cart_color',
		'label'     => esc_html__( 'Cart text', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_color'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'  => '.shoptimizer-cart .cart-contents',
				'property' => 'color',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.shoptimizer-cart .cart-contents',
				'function' => 'css',
				'property' => 'color',
			),
		),
	)
);

// Navigation Cart Hover Text Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_cart_hover_color',
		'label'     => esc_html__( 'Cart text hover color', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_hover_color'],
		'priority'  => 10,
		'output'    => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents:hover .count',
				'property' => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents:hover .count',
				'function' => 'css',
				'property' => 'color',
				'media_query' => '@media (min-width: 993px)',
			),
		),
	)
);

// Cart Quantity Bubble Background Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_cart_bubble_background_color',
		'label'     => esc_html__( 'Cart quantity background color', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_bubble_background_color'],
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_layout_woocommerce_cart_icon',
				'value'    => 'basket',
				'operator' => '!==',
			),
		),
		'priority'  => 10,
		'output'    => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .shoptimizer-cart-icon .mini-count',
				'property' => 'background-color',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .shoptimizer-cart-icon .mini-count',
				'function' => 'css',
				'property' => 'background-color',
			),
		),
	)
);

// Cart Quantity Bubble Border Color.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'color',
		'settings'  => 'shoptimizer_cart_bubble_border_color',
		'label'     => esc_html__( 'Cart quantity border color', 'shoptimizer' ),
		'section'   => 'shoptimizer_color_section_navigation',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_bubble_border_color'],
		'active_callback'  => array(
			array(
				'setting'  => 'shoptimizer_layout_woocommerce_cart_icon',
				'value'    => 'basket',
				'operator' => '!==',
			),
		),
		'priority'  => 11,
		'output'    => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .shoptimizer-cart-icon .mini-count',
				'property' => 'border-color',
			),
		),
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.shoptimizer-cart a.cart-contents .shoptimizer-cart-icon .mini-count',
				'function' => 'css',
				'property' => 'border-color',
			),
		),
	)
);

// Display Cart in Menu.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'toggle',
		'settings'  => 'shoptimizer_layout_woocommerce_display_cart',
		'label'     => esc_html__( 'Display cart', 'shoptimizer' ),
		'section'   => 'shoptimizer_cart_section_layout',
		'default'   => 1,
		'priority'  => 10,
		'transport' => 'refresh',
	)
);

// Cart Icon.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'select',
		'settings'    => 'shoptimizer_layout_woocommerce_cart_icon',
		'label'       => esc_html__( 'Cart icon', 'shoptimizer' ),
		'description' => esc_html__( 'If adjusting, test in an incognito window', 'shoptimizer' ),
		'section'     => 'shoptimizer_cart_section_layout',
		'default'     => $shoptimizer_default_options['shoptimizer_layout_woocommerce_cart_icon'],
		'priority'    => 10,
		'transport'   => 'refresh',
		'choices'     => array(
			'basket'  => esc_html__( 'Basket (Default)', 'shoptimizer' ),
			'cart' => esc_html__( 'Cart icon', 'shoptimizer' ),
			'bag' => esc_html__( 'Bag icon', 'shoptimizer' ),
		),
	)
);

// Cart sidebar Title.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'text',
		'settings'  => 'shoptimizer_cart_title',
		'label'     => esc_html__( 'Cart sidebar title', 'shoptimizer' ),
		'section'   => 'shoptimizer_cart_section_layout',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_title'],
		'priority'  => 10,
		'transport' => 'auto',
		'js_vars'   => array(
			array(
				'element'  => '.cart-drawer-heading',
				'function' => 'html',
			),
		),
	)
);

// Cart sidebar - display quantity.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'toggle',
		'settings'  => 'shoptimizer_minicart_quantity',
		'label'     => esc_html__( 'Cart sidebar quantity arrows', 'shoptimizer' ),
		'description' => esc_html__( 'Display quantity arrows in mini cart', 'shoptimizer' ),
		'section'   => 'shoptimizer_cart_section_layout',
		'default'   => 0,
		'priority'  => 10,
		'transport' => 'refresh',
	)
);

// Cart sidebar - hide view cart link.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'toggle',
		'settings'  => 'shoptimizer_sidebar_hide_cart_link',
		'label'     => esc_html__( 'Cart sidebar - hide "View Cart"', 'shoptimizer' ),
		'section'   => 'shoptimizer_cart_section_layout',
		'default'   => 0,
		'priority'  => 10,
		'transport' => 'refresh',
	)
);

// Cart sidebar below text.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'      => 'textarea',
		'settings'  => 'shoptimizer_cart_below_text',
		'label'     => esc_html__( 'Cart sidebar below text', 'shoptimizer' ),
		'section'   => 'shoptimizer_cart_section_layout',
		'default'   => $shoptimizer_default_options['shoptimizer_cart_below_text'],
		'priority'  => 10,
		'transport' => 'auto',
		'js_vars'   => array(
			array(
				'element'  => '.cart-drawer-below',
				'function' => 'html',
			),
		),
	)
);

// Below header padding.
shoptimizer_Kirki::add_field(
	'shoptimizer_config', array(
		'type'        => 'slider',
		'settings'    => 'shoptimizer_below_header_padding',
		'label'       => esc_html__( 'Below header padding', 'shoptimizer' ),
		'description' => esc_html__( 'Adjusts the top and bottom padding.', 'shoptimizer' ),
		'section'     => 'shoptimizer_below_header_section_layout',
		'default'     => 12,
		'priority'    => 1,
		'choices'     => array(
			'min'  => 0,
			'max'  => 50,
			'step' => 1,
		),
		'output'      => array(
			array(
				'element'  => '.header-widget-region .widget',
				'property' => 'padding-top',
				'units'    => 'px',
			),
			array(
				'element'  => '.header-widget-region .widget',
				'property' => 'padding-bottom',
				'units'    => 'px',
			),
		),
	)
);

// Below header font size.
shoptimizer_Kirki::add_field(
	'shoptimizer_config',
	array(
		'type'     => 'typography',
		'settings' => 'shoptimizer_below_header_font_size',
		'label'    => esc_html__( 'Below header font size', 'shoptimizer' ),
		'section'  => 'shoptimizer_below_header_section_layout',
		'default'  => array(
			'font-size'      => '14px',
		),
		'priority' => 10,
		'output'   => array(
			array(
				'element'  => '.header-widget-region',
				'property' => 'font-size',
			),
		),
	)
);

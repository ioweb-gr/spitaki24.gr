<?php
if ( ! class_exists('BeRocket_AAPF_Elemets_Style_Purple_rounded_button') ) {
	class BeRocket_AAPF_Elemets_Style_Purple_rounded_button extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'          => 'purple_rounded_button',
				'template'      => 'button',
				'name'          => 'Purple & rounded',
				'file'          => __FILE__,
				'style_file'    => '/css/feb.css',
				'script_file'   => '',
				'image'         => plugin_dir_url( __FILE__ ) . 'images/purple_rounded_button.png',
				'version'       => '1.0',
				'specific'      => 'elements',
				'sort_pos'      => '450',
			);
			parent::__construct();
		}

		function filters( $action = 'add' ) {
			parent::filters( $action );

			$filter_func = 'add_filter';
			if ( $action != 'add' ) {
				$filter_func = 'remove_filter';
			}

			$filter_func('BeRocket_AAPF_template_full_element_content', array( $this, 'template_element_full' ), 10, 2);
		}

		function template_element_full( $template, $berocket_query_var_title ) {
			$template['template']['attributes']['class']['inline'] = 'bapf_purple_rounded_button';

			return $template;
		}
	}

	new BeRocket_AAPF_Elemets_Style_Purple_rounded_button();
}

if ( ! class_exists('BeRocket_AAPF_Elemets_Style_Linklike_button') ) {
	class BeRocket_AAPF_Elemets_Style_Linklike_button extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'          => 'linklike_button',
				'template'      => 'button',
				'name'          => 'Link-like',
				'file'          => __FILE__,
				'style_file'    => '/css/feb.css',
				'script_file'   => '',
				'image'         => plugin_dir_url( __FILE__ ) . 'images/linklike_button.png',
				'version'       => '1.0',
				'specific'      => 'elements',
				'sort_pos'      => '450',
			);
			parent::__construct();
		}

		function filters( $action = 'add' ) {
			parent::filters( $action );

			$filter_func = 'add_filter';
			if ( $action != 'add' ) {
				$filter_func = 'remove_filter';
			}

			$filter_func('BeRocket_AAPF_template_full_element_content', array( $this, 'template_element_full' ), 10, 2);
		}

		function template_element_full( $template, $berocket_query_var_title ) {
			$template['template']['attributes']['class']['inline'] = 'bapf_linklike_button';

			return $template;
		}
	}

	new BeRocket_AAPF_Elemets_Style_Linklike_button();
}

if ( ! class_exists('BeRocket_AAPF_Elemets_Style_Dark_blue_button') ) {
	class BeRocket_AAPF_Elemets_Style_Dark_blue_button extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'          => 'dark_blue_button',
				'template'      => 'button',
				'name'          => 'Dark blue',
				'file'          => __FILE__,
				'style_file'    => '/css/feb.css',
				'script_file'   => '',
				'image'         => plugin_dir_url( __FILE__ ) . 'images/dark_blue_button.png',
				'version'       => '1.0',
				'specific'      => 'elements',
				'sort_pos'      => '450',
			);
			parent::__construct();
		}

		function filters( $action = 'add' ) {
			parent::filters( $action );

			$filter_func = 'add_filter';
			if ( $action != 'add' ) {
				$filter_func = 'remove_filter';
			}

			$filter_func('BeRocket_AAPF_template_full_element_content', array( $this, 'template_element_full' ), 10, 2);
		}

		function template_element_full( $template, $berocket_query_var_title ) {
			$template['template']['attributes']['class']['inline'] = 'dark_blue_button';

			return $template;
		}
	}

	new BeRocket_AAPF_Elemets_Style_Dark_blue_button();
}

if ( ! class_exists('BeRocket_AAPF_Template_Style_Pink_labels_checkbox') ) {
	class BeRocket_AAPF_Template_Style_Pink_labels_checkbox extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'          => 'pink_labels_checkbox',
				'template'      => 'checkbox',
				'name'          => 'Pink labels',
				'file'          => __FILE__,
				'style_file'    => 'css/feb.css',
				'script_file'   => '',
				'image'         => plugin_dir_url( __FILE__ ) . 'images/pink_labels_checkbox.png',
				'sort_pos'      => '450',
				'version'       => '1.0',
				'name_price'    => 'Price Ranges Pink labels',
				'image_price'   => plugin_dir_url( __FILE__ ) . 'images/pink_labels_checkbox.png',
			);

			parent::__construct();

			add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
		}

		function template_single_item( $template, $term, $i, $berocket_query_var_title ) {
			$this->array_set( $template, array('attributes', 'class') );
			$template['attributes']['class'][] = 'pink_labels_checkbox_class_item';

			return $template;
		}

		function template_full( $template, $terms, $berocket_query_var_title ) {
			$this->array_set( $template, array('template', 'attributes', 'class') );
			$template['template']['attributes']['class'][] = 'pink_labels_checkbox_class';

			if ( in_array( $berocket_query_var_title['attribute'], array('_stock_status', '_sale') ) and isset( $template['template']['content']['filter']['content']['list']['content'] ) ) {
				foreach ( $template['template']['content']['filter']['content']['list']['content'] as $key => $element ) {
					if ( $element['content']['checkbox']['attributes']['value'] == 2 ) {
						unset( $template['template']['content']['filter']['content']['list']['content'][ $key ] );
					} elseif ( ! $berocket_query_var_title['title'] ) {
						unset( $template['template']['content']['header'] );
					}
				}
			}

			return $template;
		}

		function products_count( $element, $term, $i, $berocket_query_var_title ) {
			if ( $berocket_query_var_title['show_product_count_per_attr'] and $berocket_query_var_title['new_style']['slug'] == 'pink_labels_checkbox' ) {
				$element['content']['label']['content']['name'] .= $element['content']['qty']['content'][0];
				unset( $element['content']['qty']['content'] );
			}

			return $element;
		}
	}

	new BeRocket_AAPF_Template_Style_Pink_labels_checkbox();
}

if ( ! class_exists('BeRocket_AAPF_Template_Style_circle_with_border_color') ) {
	class BeRocket_AAPF_Template_Style_circle_with_border_color extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'          => 'circle_with_border_color',
				'template'      => 'checkbox',
				'name'          => 'Circle with border',
				'file'          => __FILE__,
				'style_file'    => 'css/feb.css',
				'script_file'   => '',
				'image'         => plugin_dir_url( __FILE__ ) . 'images/circle_with_border_color.png',
				'specific'      => 'color',
				'sort_pos'      => '450',
				'version'       => '1.0'
			);

			parent::__construct();

			add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
		}

		function template_full( $template_content, $terms, $berocket_query_var_title ) {
			$this->array_set( $template_content, array('template', 'attributes', 'class') );
			$template_content['template']['attributes']['class'][] = 'circle_with_border_color_class';
			$template_content['template']['attributes']['class']['style_type'] = 'bapf_stylecolor';
			$template_content['template']['attributes']['class']['inline_color'] = 'bapf_colorinline';
			return $template_content;
		}

		function template_single_item( $template, $term, $i, $berocket_query_var_title ) {
			$this->array_set( $template, array('attributes', 'class') );
			$template['attributes']['class'][] = 'circle_with_border_color_class_item';
			if( ! empty($berocket_query_var_title['clrimg_use_attrval']) ) {
				$meta_color = $term->name;
			} else {
				$berocket_term                 = berocket_term_get_metadata($term, 'color');
				$meta_color                    = br_get_value_from_array( $berocket_term, 0, '' );
			}
			$meta_color                        = str_replace( '#', '', $meta_color );
			$template['content']['checkbox']   = BeRocket_AAPF_dynamic_data_template::create_element_arrays( $template['content']['checkbox'], array('attributes', 'style') );
			$template['content']['checkbox']['attributes']['style']['display'] = 'display:none;';
			$template['content']['label']['content'] = array(
				'color' => array(
					'type'          => 'tag',
					'tag'           => 'span',
					'attributes'    => array(
						'class'         => array(
							'main'          => 'bapf_clr_span',
						),
						'style'         => array(
							'bg-color'      => 'background-color: #' . $meta_color . ';',
							'bd-color'      => 'border-color: #' . $meta_color . ';'
						),
					),
					'content'       => array(
						'span'          => array(
							'type'          => 'tag',
							'tag'           => 'span',
							'attributes'    => array(
								'class'         => array(
									'main'          => 'bapf_clr_span_abslt',
								),
							),
						)
					)
				)
			);

			return $template;
		}

		function products_count( $element, $term, $i, $berocket_query_var_title ) {
			return $element;
		}
	}

	new BeRocket_AAPF_Template_Style_circle_with_border_color();
}

if ( ! class_exists('BeRocket_AAPF_Template_Style_square_with_shadow_color') ) {
	class BeRocket_AAPF_Template_Style_square_with_shadow_color extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'          => 'square_with_shadow_color',
				'template'      => 'checkbox',
				'name'          => 'Square with shadow',
				'file'          => __FILE__,
				'style_file'    => 'css/feb.css',
				'script_file'   => '',
				'image'         => plugin_dir_url( __FILE__ ) . 'images/square_with_shadow_color.png',
				'specific'      => 'color',
				'sort_pos'      => '400',
				'version'       => '1.0'
			);

			parent::__construct();

			add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
		}

		function template_full( $template_content, $terms, $berocket_query_var_title ) {
			$this->array_set( $template_content, array('template', 'attributes', 'class') );
			$template_content['template']['attributes']['class'][] = 'square_with_shadow_color_class';
			$template_content['template']['attributes']['class']['style_type'] = 'bapf_stylecolor';
			$template_content['template']['attributes']['class']['inline_color'] = 'bapf_colorinline';
			return $template_content;
		}

		function template_single_item( $template, $term, $i, $berocket_query_var_title ) {
			$this->array_set( $template, array('attributes', 'class') );
			$template['attributes']['class'][] = 'square_with_shadow_color_class_item';
			if( ! empty($berocket_query_var_title['clrimg_use_attrval']) ) {
				$meta_color = $term->name;
			} else {
				$berocket_term                 = berocket_term_get_metadata($term, 'color');
				$meta_color                    = br_get_value_from_array( $berocket_term, 0, '' );
			}
			$meta_color                        = str_replace( '#', '', $meta_color );
			$template['content']['checkbox']   = BeRocket_AAPF_dynamic_data_template::create_element_arrays( $template['content']['checkbox'], array('attributes', 'style') );
			$template['content']['checkbox']['attributes']['style']['display'] = 'display:none;';

			$template['content']['label']['content'] = array(
				'color' => array(
					'type'          => 'tag',
					'tag'           => 'span',
					'attributes'    => array(
						'class'         => array(
							'main'          => 'bapf_clr_span',
						),
						'style'         => array(
							'bg-color'      => 'background-color: #' . $meta_color . ';',
						),
					)
				)
			);

			if ( $berocket_query_var_title['show_product_count_per_attr'] and ! $berocket_query_var_title['use_value_with_color'] ) {
				$template['content']['label']['content']['color']['content'] = array(
					'span'          => array(
						'type'          => 'tag',
						'tag'           => 'span',
						'attributes'    => array(
							'class'         => array(
								'main'          => 'bapf_clr_span_abslt',
							),
						),
					)
				);
			}

			return $template;
		}

		function products_count( $element, $term, $i, $berocket_query_var_title ) {
			return $element;
		}
	}

	new BeRocket_AAPF_Template_Style_square_with_shadow_color();
}
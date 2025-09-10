<?php
/**
* The template for displaying checkbox filters
*
* Override this template by copying it to yourtheme/woocommerce-ajax_filters/checkbox.php
*
* @author     BeRocket
* @package     WooCommerce-Filters/Templates
* @version  1.0.1
*/
extract($berocket_query_var_title);
//Get default template functionality
$template_content = BeRocket_AAPF_Template_Build_default();
//set unique id for filter
$filter_unique_class = 'bapf_'.$unique_filter_id;
$template_content['template']['attributes']['id']                                           = $filter_unique_class;
//set this template class 
$template_content['template']['attributes']['class']['filter_type']                         = 'bapf_ckbox';
//Set name for selected filters area and other siilar place
$template_content['template']['attributes']['data-name']                                    = $title;
//Set widget title
$template_content['template']['content']['header']['content']['title']['content']['title']  = $title;
//Add widget content
$template_content['template']['content']['filter']['content']['list']                       = array(
    'type'          => 'tag',
    'tag'           => 'div',
    'attributes'    => array(),
    'content'       => array(
        'p'             => array(
            'type'          => 'tag',
            'tag'           => 'p',
            'attributes'    => array(),
            'content'       => array($berocket_query_var_title['child_parent_previous'])
        )
    )
);
echo BeRocket_AAPF_Template_Build($template_content);
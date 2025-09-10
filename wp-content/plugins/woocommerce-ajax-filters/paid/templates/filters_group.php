<?php
$options = BeRocket_AAPF::get_aapf_option();
$elements_above_products = br_get_value_from_array($options, 'elements_above_products');
if( ! is_array($elements_above_products) ) {
	$elements_above_products = array();
}

global $pagenow;
$post_id = 0;
if( ! in_array( $pagenow, array( 'post-new.php' ) ) ) {
	$post_id = $post->ID;
}
?>
<div class="braapf_attribute_setup_flex">
    <div class="braapf_full_select_full">
        <input type="checkbox" id="berocket_show_above_option_label_id"
               class="berocket_show_above_option" name="br_filter_group_show_above"
               value="1"<?php if(in_array($post_id, $elements_above_products)) echo ' checked'; ?>>
        <label class="braapf_filter_title_label" for="berocket_show_above_option_label_id"><?php _e('Show filters above products', 'BeRocket_AJAX_domain'); ?></label>
    </div>
</div>
<div class="braapf_attribute_setup_flex">
    <div class="braapf_full_select_full">
        <input class="berocket_display_inline_option" id="berocket_display_inline_option_label_id"
               type="checkbox" name="<?php echo $post_name; ?>[display_inline]"
               value="1"<?php if(! empty($filters['display_inline']) ) echo ' checked'; ?>>
        <label class="braapf_filter_title_label" for="berocket_display_inline_option_label_id"><?php _e('Display filters in line', 'BeRocket_AJAX_domain'); ?></label>
    </div>
</div>
<div class="berocket_display_inline_count braapf_attribute_setup_flex">
    <div class="braapf_half_select_full">
        <label class="braapf_filter_title_label" for=""><?php _e('Display filters in line max count', 'BeRocket_AJAX_domain'); ?></label>
        <select name="<?php echo $post_name; ?>[display_inline_count]">
            <option value=""><?php _e('Default', 'BeRocket_AJAX_domain'); ?></option>
            <?php for ( $fg_i = 1; $fg_i < 8; $fg_i++ ) { ?>
                <option value="<?=$fg_i?>"<?php if( br_get_value_from_array($filters, 'display_inline_count') == $fg_i ) echo ' selected'; ?>><?=$fg_i?></option>
            <?php } ?>
        </select>
    </div>
    <div class="berocket_min_filter_width_inline braapf_half_select_full">
        <label class="braapf_filter_title_label" for="min_filter_width_inline_label_id"><?php _e('Min Width for Filter', 'BeRocket_AJAX_domain'); ?></label>
        <input type="number" min="25" id="min_filter_width_inline_label_id" name="<?php echo $post_name; ?>[min_filter_width_inline]"
               value="<?php echo br_get_value_from_array($filters, 'min_filter_width_inline', '200'); ?>">
    </div>
</div>
<div class="braapf_attribute_setup_flex">
    <div class="braapf_half_select_full">
        <input type="checkbox" class="berocket_hidden_clickable_option" id="berocket_hidden_clickable_option_label_id"
               name="<?php echo $post_name; ?>[hidden_clickable]"
               value="1"<?php if(! empty($filters['hidden_clickable']) ) echo ' checked'; ?>>
        <label class="braapf_filter_title_label" for="berocket_hidden_clickable_option_label_id"><?php _e('Show title only', 'BeRocket_AJAX_domain'); ?></label>
    </div>
    <div class="berocket_hidden_clickable_option_data braapf_half_select_full">
        <input type="checkbox" id="hidden_clickable_hover_label_id" name="<?php echo $post_name; ?>[hidden_clickable_hover]"
               value="1"<?php if(! empty($filters['hidden_clickable_hover']) ) echo ' checked'; ?>>
        <label class="braapf_filter_title_label" for="hidden_clickable_hover_label_id"><?php _e('Display filters on mouse over', 'BeRocket_AJAX_domain'); ?></label>
    </div>
</div>
<div class="braapf_attribute_setup_flex berocket_hidden_clickable_option_data">
    <div class="braapf_full_select_full">
        <label class="braapf_filter_title_label" for="">Title-only style</label>
        <div class="berocket_group_is_hide_theme_option_slider">
            <div>
                <input type="radio" name="<?php echo $post_name; ?>[title_only_theme]" style="display:none!important;" id="title_only_theme_" value="" <?php echo ( empty( $filters['title_only_theme'] ) ? ' checked' : '' ) ?> />
                <label for="title_only_theme_"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file)?>images/themes/title-only-dropdown/default.png" /></label>
            </div>
	        <?php for ( $theme_key = 1; $theme_key <= 3; $theme_key++ ) { ?>
                <div>
                    <input type="radio" name="<?php echo $post_name; ?>[title_only_theme]" style="display:none!important;" id="title_only_theme_<?php echo $theme_key?>" value="<?php echo $theme_key?>" <?php echo  ( ( ! empty( $filters['title_only_theme'] ) and $filters['title_only_theme'] == $theme_key ) ? ' checked' : '' ) ?> />
                    <label for="title_only_theme_<?php echo $theme_key?>"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/title-only-dropdown/' . $theme_key ?>.png" /></label>
                </div>
	        <?php } ?>
        </div>
    </div>
</div>
<div class="berocket_group_is_hide_option_data braapf_attribute_setup_flex">
    <div class="braapf_full_select_full">
        <input type="checkbox" class="berocket_group_is_hide_option" id="berocket_group_is_hide_option_label_id"
               name="<?php echo $post_name; ?>[group_is_hide]"
               value="1"<?php if(! empty($filters['group_is_hide']) ) echo ' checked'; ?>>
        <label class="braapf_filter_title_label" for="berocket_group_is_hide_option_label_id"><?php _e('Collapsed on page load', 'BeRocket_AJAX_domain'); ?></label>
    </div>
</div>
<div class="berocket_group_is_hide_theme_option_data braapf_attribute_setup_flex">
    <div class="braapf_full_select_full">
        <label class="braapf_filter_title_label" for=""><?php _e('Collapse Button style', 'BeRocket_AJAX_domain'); ?></label>
        <div class="berocket_group_is_hide_theme_option_slider">
            <div>
                <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_theme]" style="display:none!important;" id="group_is_hide_theme_" value="" <?php echo ( empty( $filters['group_is_hide_theme'] ) ? ' checked' : '' ) ?> />
                <label for="group_is_hide_theme_"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file)?>images/themes/sidebar-button/default.png" /></label>
            </div>
            <?php for ( $theme_key = 1; $theme_key <= 10; $theme_key++ ) { ?>
            <div>
                <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_theme]" style="display:none!important;" id="group_is_hide_theme_<?php echo $theme_key?>" value="<?php echo $theme_key?>" <?php echo  ( ( ! empty( $filters['group_is_hide_theme'] ) and $filters['group_is_hide_theme'] == $theme_key ) ? ' checked' : '' ) ?> />
                <label for="group_is_hide_theme_<?php echo $theme_key?>"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button/' . $theme_key ?>.png" /></label>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="berocket_group_is_hide_theme_option_data braapf_attribute_setup_flex">
    <div class="braapf_full_select_full">
        <label class="braapf_filter_title_label" for=""><?php _e('Collapse Button Icon style', 'BeRocket_AJAX_domain'); ?></label>
        <div class="berocket_group_is_hide_theme_option_slider icon_size">
            <div>
                <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_icon_theme]" style="display:none!important;" id="group_is_hide_icon_theme_" value="" <?php echo ( empty( $filters['group_is_hide_icon_theme'] ) ? ' checked' : '' ) ?> />
                <label for="group_is_hide_icon_theme_"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file)?>images/themes/sidebar-button-icon/default.png" /></label>
            </div>
            <?php for ( $theme_key = 1; $theme_key <= 6; $theme_key++ ) { ?>
                <div>
                    <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_icon_theme]" style="display:none!important;" id="group_is_hide_icon_theme_<?php echo $theme_key?>" value="<?php echo $theme_key?>" <?php echo ( ( ! empty( $filters['group_is_hide_icon_theme'] ) and $filters['group_is_hide_icon_theme'] == $theme_key ) ? ' checked' : '' ) ?> />
                    <label for="group_is_hide_icon_theme_<?php echo $theme_key?>"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button-icon/' . $theme_key ?>.png" /></label>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    function berocket_hidden_clickable_option() {
        if( jQuery('input.berocket_hidden_clickable_option').prop('checked') ) {
            jQuery('div.berocket_hidden_clickable_options').show();
            jQuery('div.berocket_hidden_clickable_option_data').show();
            jQuery('.berocket_filter_added_list').addClass('berocket_hidden_clickable_enabled');
        } else {
            jQuery('div.berocket_hidden_clickable_options').hide();
            jQuery('div.berocket_hidden_clickable_option_data').hide();
            jQuery('.berocket_filter_added_list').removeClass('berocket_hidden_clickable_enabled');
        }
    }
    jQuery(document).on('change', 'input.berocket_hidden_clickable_option', berocket_hidden_clickable_option);
    berocket_hidden_clickable_option();

    function berocket_display_inline_count() {
        if( jQuery('.berocket_display_inline_option').prop('checked') ) {
            jQuery('.berocket_display_inline_count').show();
        } else {
            jQuery('.berocket_display_inline_count').hide();
        }
        if( jQuery('.berocket_display_inline_option').prop('checked') && jQuery('.berocket_display_inline_count select').val() ) {
            jQuery('.berocket_min_filter_width_inline').show();
        } else {
            jQuery('.berocket_min_filter_width_inline').hide();
        }
    }
    jQuery(document).on('change', '.berocket_display_inline_option, .berocket_hidden_clickable_option, .berocket_display_inline_count select', berocket_display_inline_count);
    berocket_display_inline_count();

    function berocket_group_is_hide_option() {
        if( jQuery('.berocket_group_is_hide_option').prop('checked') ) {
            jQuery('.berocket_group_is_hide_theme_option_data').show();
        } else {
            jQuery('.berocket_group_is_hide_theme_option_data').hide();
            jQuery('.berocket_group_is_hide_theme_option').removeAttr('checked');
        }
    }
    jQuery(document).on('change', '.berocket_group_is_hide_option', berocket_group_is_hide_option);
    berocket_group_is_hide_option();

    jQuery(document).ready(function() {
        berocket_hidden_clickable_option();
        berocket_display_inline_count();
    });
</script>

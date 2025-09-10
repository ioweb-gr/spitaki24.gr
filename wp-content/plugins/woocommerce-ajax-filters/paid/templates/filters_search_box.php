<div class="berocket_groups_search">
    <div class="berocket_groups_search_content">
        <div>
            <label>
                <input class="berocket_admin_search_box_enable" type="checkbox" name="<?php echo $post_name; ?>[search_box]" value="1"<?php echo (empty($filters['search_box']) ? '' : ' checked') ?>>
                <?php _e('Use Group as Search Box', 'BeRocket_AJAX_domain'); ?>
            </label>
            <p><?php _e('Redirect to some other page with selected filters from this group.', 'BeRocket_AJAX_domain'); ?></p>
        </div>
        <div class="br_flex berocket_aapf_admin_search_box"<?php if( empty($filters['search_box']) ) echo ' style="display: none;"'; ?>>
            <div class="braapf_half_select_full">
                <label><?php _e('URL for search', 'BeRocket_AJAX_domain') ?></label>
                <select name="<?php echo $post_name; ?>[search_box_link_type]" class="berocket_search_link_select">
                    <option value="shop_page"<?php if ($filters['search_box_link_type'] == 'shop_page' ) echo ' selected'; ?>><?php _e('Shop page', 'BeRocket_AJAX_domain') ?></option>
                    <option value="category"<?php if ($filters['search_box_link_type'] == 'category' ) echo ' selected'; ?>><?php _e('Category page', 'BeRocket_AJAX_domain') ?></option>
                    <option value="url"<?php if ($filters['search_box_link_type'] == 'url' ) echo ' selected'; ?>><?php _e('Custom URL', 'BeRocket_AJAX_domain') ?></option>
                </select>
            </div>
            <div class="braapf_half_select_full berocket_search_link berocket_search_link_category"<?php if( $filters['search_box_link_type'] != 'category' ) echo ' style="display:none;"'; ?>>
                <label><?php _e('Category', 'BeRocket_AJAX_domain') ?></label>
                <select name="<?php echo $post_name; ?>[search_box_category]">
                <?php
                $filters['search_box_category'] = ( empty($filters['search_box_category']) ? '' : urldecode($filters['search_box_category']) );
                foreach( $categories as $category ){
                    echo '<option value="'.$category->slug.'"'.($filters['search_box_category'] == $category->slug ? ' selected' : '').'>'.$category->name.'</option>';
                } ?>
                </select>
            </div>
            <div class="braapf_half_select_full berocket_search_link berocket_search_link_url"<?php if( $filters['search_box_link_type'] != 'url' ) echo ' style="display:none;"'; ?>>
                <label><?php _e('Custom URL', 'BeRocket_AJAX_domain') ?></label>
                <input class="br_admin_full_size" id="search_box_url" name="<?php echo $post_name; ?>[search_box_url]" type="text" value="<?php echo $filters['search_box_url']; ?>">
            </div>
            <div class="braapf_full_select_full">
                <label>
                    <input type="checkbox" name="<?php echo $post_name; ?>[search_box_recount]" value="1"<?php echo (empty($filters['search_box_recount']) ? '' : ' checked') ?>>
                    <?php _e('Update filters', 'BeRocket_AJAX_domain'); ?>
                </label>
                <div><?php _e('Load filters from selected page before update(only work with Update Products button).', 'BeRocket_AJAX_domain'); ?></div>
            </div>
            <div class="berocket_groups_search_styles_content">
                <h3><?php _e('Styles', 'BeRocket_AJAX_domain') ?></h3>
                <div class="berocket_groups_search_styles_inner_content">
                    <div class="braapf_half_select_full">
                        <label><?php _e('Elements position', 'BeRocket_AJAX_domain') ?></label>
                        <select name="<?php echo $post_name; ?>[search_box_style][position]">
                            <option value="vertical"<?php if( br_get_value_from_array($filters, array('search_box_style', 'position')) == 'vertical' ) echo ' selected'; ?>><?php _e('Vertical', 'BeRocket_AJAX_domain') ?></option>
                            <option value="horizontal"<?php if( br_get_value_from_array($filters, array('search_box_style', 'position')) == 'horizontal' ) echo ' selected'; ?>><?php _e('Horizontal', 'BeRocket_AJAX_domain') ?></option>
                        </select>
                    </div>
                    <div class="br_flex">
                        <div class="braapf_half_select_full">
                            <label><?php _e('Background transparency', 'BeRocket_AJAX_domain') ?></label>
                            <select name="<?php echo $post_name; ?>[search_box_style][back_opacity]">
                                <?php
                                $back_opacity = array(
                                    '0'     => __('100%', 'BeRocket_AJAX_domain'),
                                    '0.1'   => __('90%', 'BeRocket_AJAX_domain'),
                                    '0.2'   => __('80%', 'BeRocket_AJAX_domain'),
                                    '0.3'   => __('70%', 'BeRocket_AJAX_domain'),
                                    '0.4'   => __('60%', 'BeRocket_AJAX_domain'),
                                    '0.5'   => __('50%', 'BeRocket_AJAX_domain'),
                                    '0.6'   => __('40%', 'BeRocket_AJAX_domain'),
                                    '0.7'   => __('30%', 'BeRocket_AJAX_domain'),
                                    '0.8'   => __('20%', 'BeRocket_AJAX_domain'),
                                    '0.9'   => __('10%', 'BeRocket_AJAX_domain'),
                                    '1'     => __('0%', 'BeRocket_AJAX_domain'),
                                );
                                foreach($back_opacity as $key => $value) {
                                    echo '<option value="', $key, '"',
                                    ( (br_get_value_from_array($filters, array('search_box_style', 'back_opacity')) == $key) ? ' selected' : '' ),
                                    '>', $value, '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="braapf_half_select_full">
                            <label><?php _e('Background color', 'BeRocket_AJAX_domain') ?></label>
                            <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($filters, array('search_box_style', 'background'), '000000'); ?>"></div>
                            <input type="hidden" value="<?php echo br_get_value_from_array($filters, array('search_box_style', 'background')) ?>" name="<?php echo $post_name; ?>[search_box_style][background]">
                        </div>
                    </div>
                    <?php do_action('bapf_search_button_meta_box', $post_name, $filters); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="berocket_groups_search_docs">
        <div class="berocket_groups_search_docs_content">
            <p class="docs_title"><?=__('URL for search', 'BeRocket_AJAX_domain')?></p>
            <p><?=__('Select which page will toggle after using the Search Box.', 'BeRocket_AJAX_domain')?></p>

            <p class="docs_title"><?=__('Update filters ', 'BeRocket_AJAX_domain')?></p>
            <p><?=__('Load filters from selected page before update(only work with Update Products button).', 'BeRocket_AJAX_domain')?></p>

            <p class="docs_title"><a href="https://docs.berocket.com/plugin/woocommerce-ajax-products-filter#search-box-en"
                                     target="_blank"><?=__('Search Box docs ', 'BeRocket_AJAX_domain')?></a></p>
        </div>
    </div>
</div>

<script>
jQuery(document).on('change', '.berocket_admin_search_box_enable', function(event) {
    if( jQuery(this).prop('checked') ) {
        jQuery('.berocket_aapf_admin_search_box').show();
        jQuery('.berocket_groups_search_docs').show();
    } else {
        jQuery('.berocket_aapf_admin_search_box').hide();
        jQuery('.berocket_groups_search_docs').hide();
    }
})
</script>

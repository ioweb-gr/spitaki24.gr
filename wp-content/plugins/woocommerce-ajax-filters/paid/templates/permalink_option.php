<?php
$options    = get_option( 'berocket_permalink_option' );
$nn_options = get_option( 'berocket_nn_permalink_option' );
$BeRocket_AAPF = BeRocket_AAPF::getInstance();
$plugin_option = $BeRocket_AAPF->get_option();
BeRocket_updater::$error_log[] = $options;
if( ! empty($plugin_option['nice_urls']) ) {
?>
<div class="br_permalink_editor">
    <input type="text" placeholder="filters" id="berocket_permalinks_variable" name="berocket_permalink_option[variable]" value="<?php echo br_get_value_from_array($options, 'variable'); ?>">
    <span>/taxonomy_name</span>
    <select name="berocket_permalink_option[value]" id="berocket_permalinks_value">
        <option <?php if( br_get_value_from_array($options, 'value') == '/values' ) echo 'selected'; ?> value="/values">/values</option>
        <option <?php if( br_get_value_from_array($options, 'value') == '[values]' ) echo 'selected'; ?> value="[values]">[values]</option>
        <option <?php if( br_get_value_from_array($options, 'value') == '=values' ) echo 'selected'; ?> value="=values" disabled>=values</option>
        <option <?php if( br_get_value_from_array($options, 'value') == '=[values]' ) echo 'selected'; ?> value="=[values]" disabled>=[values]</option>
    </select>
    <select name="berocket_permalink_option[split]" id="berocket_permalinks_split">
        <option <?php if( br_get_value_from_array($options, 'split') == '/' ) echo 'selected'; ?> value="/">/</option>
        <option <?php if( br_get_value_from_array($options, 'split') == '|' ) echo 'selected'; ?> value="|">|</option>
        <option <?php if( br_get_value_from_array($options, 'split') == '&' ) echo 'selected'; ?> value="&">&</option>
    </select>
</div>
<div class="br_permalink_example">
    <code>
        <span>http://wordpress-shop.com/shop/</span><span class="berocket_permalinks_variable"><?php echo br_get_value_from_array($options, 'variable'); ?></span><span>/taxonomy_name</span><span class="berocket_permalinks_value"><?php echo br_get_value_from_array($options, 'value'); ?></span><span class="berocket_permalinks_split"><?php echo br_get_value_from_array($options, 'split'); ?></span><span>taxonomy_name</span><span class="berocket_permalinks_value"><?php echo br_get_value_from_array($options, 'value'); ?></span><span>/</span>
    </code>
</div>
<script>
jQuery('.br_permalink_editor input, .br_permalink_editor select').change(function(){
    jQuery('.br_permalink_example .'+jQuery(this).attr('id')).text(jQuery(this).val());
});
</script>
<?php } else { ?>
<div style="padding-top: 20px;">SEO friendly urls without Nice URLs settings</div>
<div class="br_nn_permalink_editor">
    ?<input type="text" placeholder="filters" id="berocket_nn_permalinks_variable" name="berocket_nn_permalink_option[variable]"
           value="<?php echo br_get_value_from_array($nn_options, 'variable'); ?>">
    <span>=taxonomy_name</span>
    <select name="berocket_nn_permalink_option[value]" id="berocket_nn_permalinks_value">
        <option <?php if( br_get_value_from_array($nn_options, 'value') == '/values' ) echo 'selected'; ?> value="/values">/values</option>
        <option <?php if( br_get_value_from_array($nn_options, 'value') == '[values]' ) echo 'selected'; ?> value="[values]">[values]</option>
    </select>
    <select name="berocket_nn_permalink_option[split]" id="berocket_nn_permalinks_split">
        <option <?php if( br_get_value_from_array($nn_options, 'split') == '/' ) echo 'selected'; ?> value="/">/</option>
        <option <?php if( br_get_value_from_array($nn_options, 'split') == '|' ) echo 'selected'; ?> value="|">|</option>
    </select>
</div>
<div class="br_nn_permalink_example">
    <code>
        <span>http://wordpress-shop.com/shop/?</span><span class="berocket_nn_permalinks_variable"><?php
            echo br_get_value_from_array($nn_options, 'variable'); ?></span><span>=taxonomy_name</span><span
            class="berocket_nn_permalinks_value"><?php echo br_get_value_from_array($nn_options, 'value');
            ?></span><span class="berocket_nn_permalinks_split"><?php
            echo br_get_value_from_array($nn_options, 'split'); ?></span><span>taxonomy_name</span><span
            class="berocket_nn_permalinks_value"><?php
            echo br_get_value_from_array($nn_options, 'value'); ?></span>
    </code>
</div>
<script>
    jQuery('.br_nn_permalink_editor input, .br_nn_permalink_editor select').change(function(){
        jQuery('.br_nn_permalink_example .'+jQuery(this).attr('id')).text(jQuery(this).val());
    });
</script>
<?php }
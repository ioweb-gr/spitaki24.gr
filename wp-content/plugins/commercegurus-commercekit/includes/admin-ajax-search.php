<?php
/**
 *
 * Admin Ajax Search
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php
global $wpdb;
if ( isset( $section ) && ! in_array( $section, array( 'settings', 'reports' ), true ) ) {
	$section = 'settings';
} elseif ( ! isset( $section ) ) {
	$section = 'settings';
}
if ( 'settings' === $section || '' === $section ) {
	$template = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_parent = '0' AND post_status = 'publish' AND ID > %d ";
	$query    = $wpdb->prepare( $template, 0 ); // phpcs:ignore
	$total    = (int) $wpdb->get_var( $query ); // phpcs:ignore
	$ajs_id   = isset( $commercekit_options['generating_ajs_id'] ) ? (int) $commercekit_options['generating_ajs_id'] : 0;
	$pending  = $wpdb->prepare( $template, $ajs_id ); // phpcs:ignore
	$complete = $total - (int) $wpdb->get_var( $pending ); // phpcs:ignore
	$complete = $complete >= 0 ? $complete : 0;
}
if ( 'reports' === $section ) {
	$reports = get_transient( 'commercekit_search_reports' );
	if ( ! $reports ) {
		$table        = $wpdb->prefix . 'commercekit_searches';
		$sql1         = 'SELECT SUM(search_count) FROM ' . $table;
		$search_count = (int) $wpdb->get_var( $sql1 ); // phpcs:ignore
		$sql2         = 'SELECT SUM(click_count) FROM ' . $table;
		$click_count  = (int) $wpdb->get_var( $sql2 ); // phpcs:ignore
		$sql3         = 'SELECT SUM(no_result_count) FROM ' . $table;
		$no_res_count = (int) $wpdb->get_var( $sql3 ); // phpcs:ignore
		$sql4         = 'SELECT search_term, search_count FROM ' . $table . ' ORDER BY search_count DESC LIMIT 0, 20';
		$most_results = $wpdb->get_results( $sql4, ARRAY_A ); // phpcs:ignore
		$sql5         = 'SELECT search_term, no_result_count FROM ' . $table . ' WHERE no_result_count > 0 ORDER BY no_result_count DESC LIMIT 0, 20';
		$no_results   = $wpdb->get_results( $sql5, ARRAY_A ); // phpcs:ignore

		$reports                  = array();
		$reports['search_count']  = number_format( $search_count, 0 );
		$reports['click_percent'] = $search_count > 0 ? number_format( ( $click_count / $search_count ) * 100, 1 ) : 0;
		$reports['nores_percent'] = $search_count > 0 ? number_format( ( $no_res_count / $search_count ) * 100, 1 ) : 0;
		$reports['most_results']  = $most_results;
		$reports['no_results']    = $no_results;

		set_transient( 'commercekit_search_reports', $reports, DAY_IN_SECONDS );
	}
}
?>
<div id="settings-content" class="postbox content-box">
	<?php if ( 'reports' === $section ) { ?>
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Ajax Search Reports', 'commercegurus-commercekit' ); ?></span></h2>
	<?php } ?>
	<?php if ( 'settings' === $section ) { ?>
	<h2 class="has-subtabs"><span class="table-heading"><?php esc_html_e( 'Ajax Search Settings', 'commercegurus-commercekit' ); ?></span></h2>
	<?php } ?>
	<ul class="subtabs">
	<li><a href="?page=commercekit&tab=ajax-search" class="<?php echo ( 'settings' === $section || '' === $section ) ? 'active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a></li>
	<li><a href="?page=commercekit&tab=ajax-search&section=reports" class="<?php echo 'reports' === $section ? 'active' : ''; ?>"><?php esc_html_e( 'Reports', 'commercegurus-commercekit' ); ?></a> </li>
	</ul>
	<?php if ( 'settings' === $section || '' === $section ) { ?>
	<div class="inside">
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajax_search" class="toggle-switch"> <input name="commercekit[ajax_search]" type="checkbox" id="commercekit_ajax_search" value="1" <?php echo isset( $commercekit_options['ajax_search'] ) && 1 === (int) $commercekit_options['ajax_search'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable Ajax Search in the main search bar', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Placeholder', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_ajs_placeholder"> <input name="commercekit[ajs_placeholder]" type="text" class="pc100" id="commercekit_ajs_placeholder" value="<?php echo isset( $commercekit_options['ajs_placeholder'] ) && ! empty( $commercekit_options['ajs_placeholder'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_placeholder'] ) ) : commercekit_get_default_settings( 'ajs_placeholder' ); // phpcs:ignore ?>"  /></label></td> </tr>
			<tr> <td colspan="2"><h3 class="top-divider"><?php esc_html_e( 'Products', 'commercegurus-commercekit' ); ?></h3></td> </tr>
			<tr style="display: none;"> <th scope="row"><?php esc_html_e( 'Display', 'commercegurus-commercekit' ); ?></th> <td> <label><input type="radio" value="all" name="commercekit[ajs_display]" <?php echo ( isset( $commercekit_options['ajs_display'] ) && 'all' === $commercekit_options['ajs_display'] ) || ! isset( $commercekit_options['ajs_display'] ) ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#ajs_tabbed_wrap').hide();jQuery('#ajs_tabbed_wrap2').hide();}else{jQuery('#ajs_tabbed_wrap').hide();jQuery('#ajs_tabbed_wrap2').hide();}"/>&nbsp;<?php esc_html_e( 'All contents', 'commercegurus-commercekit' ); ?></label><label><input type="radio" value="product" name="commercekit[ajs_display]" <?php echo isset( $commercekit_options['ajs_display'] ) && 'product' === $commercekit_options['ajs_display'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#ajs_tabbed_wrap').hide();jQuery('#ajs_tabbed_wrap2').hide();}else{jQuery('#ajs_tabbed_wrap').hide();jQuery('#ajs_tabbed_wrap2').hide();}"/>&nbsp;<?php esc_html_e( 'Just products', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="ajs_tabbed_wrap" <?php echo isset( $commercekit_options['ajs_display'] ) && 'product' === $commercekit_options['ajs_display'] ? 'style="display:none;"' : 'style="display: none;"'; ?>> <th scope="row"><?php esc_html_e( 'Tabbed search results', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_tabbed" class="toggle-switch"> <input name="commercekit[ajs_tabbed]" type="checkbox" id="commercekit_ajs_tabbed" value="1" <?php echo isset( $commercekit_options['ajs_tabbed'] ) && 1 === (int) $commercekit_options['ajs_tabbed'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable search results tabs', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="ajs_tabbed_wrap2" <?php echo isset( $commercekit_options['ajs_display'] ) && 'product' === $commercekit_options['ajs_display'] ? 'style="display:none;"' : 'style="display: none;"'; ?>> <th scope="row"><?php esc_html_e( 'Preserve selected tab', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_pre_tab" class="toggle-switch"> <input name="commercekit[ajs_pre_tab]" type="checkbox" id="commercekit_ajs_pre_tab" value="1" <?php echo isset( $commercekit_options['ajs_pre_tab'] ) && 1 === (int) $commercekit_options['ajs_pre_tab'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable preserve selected tab on next visit', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( '&ldquo;No results&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_ajs_no_text"> <input name="commercekit[ajs_no_text]" type="text" class="pc100" id="commercekit_ajs_no_text" value="<?php echo isset( $commercekit_options['ajs_no_text'] ) && ! empty( $commercekit_options['ajs_no_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_no_text'] ) ) : commercekit_get_default_settings( 'ajs_no_text' ); // phpcs:ignore ?>"  /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( '&ldquo;View all&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_ajs_all_text"> <input name="commercekit[ajs_all_text]" type="text" class="pc100" id="commercekit_ajs_all_text" value="<?php echo isset( $commercekit_options['ajs_all_text'] ) && ! empty( $commercekit_options['ajs_all_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_all_text'] ) ) : commercekit_get_default_settings( 'ajs_all_text' ); // phpcs:ignore ?>"  /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Out of stock products', 'commercegurus-commercekit' ); ?></th> <td> <label class="w-120"><input type="radio" value="0" name="commercekit[ajs_outofstock]" <?php echo ( isset( $commercekit_options['ajs_outofstock'] ) && 0 === (int) $commercekit_options['ajs_outofstock'] ) || ! isset( $commercekit_options['ajs_outofstock'] ) ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#ajs_orderby_oos').show();}else{jQuery('#ajs_orderby_oos').hide();}"/><?php esc_html_e( 'Include', 'commercegurus-commercekit' ); ?></label><label class="w-120"><input type="radio" value="1" name="commercekit[ajs_outofstock]" <?php echo isset( $commercekit_options['ajs_outofstock'] ) && 1 === (int) $commercekit_options['ajs_outofstock'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#ajs_orderby_oos').hide();}else{jQuery('#ajs_orderby_oos').show();}"/><?php esc_html_e( 'Exclude', 'commercegurus-commercekit' ); ?></label></td></tr>
			<tr id="ajs_orderby_oos" <?php echo isset( $commercekit_options['ajs_outofstock'] ) && 1 === (int) $commercekit_options['ajs_outofstock'] ? 'style="display:none;"' : ''; ?>> <th scope="row"><?php esc_html_e( 'Out of stock results order', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_orderby_oos" class="toggle-switch"> <input name="commercekit[ajs_orderby_oos]" type="checkbox" id="commercekit_ajs_orderby_oos" value="1" <?php echo isset( $commercekit_options['ajs_orderby_oos'] ) && 1 === (int) $commercekit_options['ajs_orderby_oos'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Display out of stock items at the end of the search results', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Exclude', 'commercegurus-commercekit' ); ?></th>  <td> <label class="text-label" for="commercekit_ajs_excludes"> <input name="commercekit[ajs_excludes]" type="text" id="commercekit_ajs_excludes" class="pc100" value="<?php echo isset( $commercekit_options['ajs_excludes'] ) && ! empty( $commercekit_options['ajs_excludes'] ) ? esc_attr( $commercekit_options['ajs_excludes'] ) : ''; ?>"  /></label><br /><small><?php esc_html_e( 'Enter product IDs to be excluded, separated by commas.', 'commercegurus-commercekit' ); ?></small></td></tr>  
			<tr style="display: none;"> <th scope="row"><?php esc_html_e( 'Hide variations', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_hidevar" class="toggle-switch"> <input name="commercekit[ajs_hidevar]" type="checkbox" id="commercekit_ajs_hidevar" value="1" <?php echo isset( $commercekit_options['ajs_hidevar'] ) && 1 === (int) $commercekit_options['ajs_hidevar'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Hide variations from search suggestions', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Products displayed', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_product_count"> <input name="commercekit[ajs_product_count]" type="number" class="pc100" id="commercekit_ajs_product_count" min="1" max="5" value="<?php echo isset( $commercekit_options['ajs_product_count'] ) && ! empty( $commercekit_options['ajs_product_count'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_product_count'] ) ) : commercekit_get_default_settings( 'ajs_product_count' ); // phpcs:ignore ?>" style="max-width: 100px;" /></label></td> </tr>
			<tr> <td colspan="2"><h3 class="top-divider"><?php esc_html_e( 'Other Results', 'commercegurus-commercekit' ); ?></h3></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Enable other results', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_other_results" class="toggle-switch"> <input name="commercekit[ajs_other_results]" type="checkbox" id="commercekit_ajs_other_results" value="1" <?php echo ( isset( $commercekit_options['ajs_other_results'] ) && 1 === (int) $commercekit_options['ajs_other_results'] ) || ! isset( $commercekit_options['ajs_other_results'] ) ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Display other results such as posts and pages', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( '&ldquo;Other results&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_ajs_other_text"> <input name="commercekit[ajs_other_text]" type="text" class="pc100" id="commercekit_ajs_other_text" value="<?php echo isset( $commercekit_options['ajs_other_text'] ) && ! empty( $commercekit_options['ajs_other_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_other_text'] ) ) : commercekit_get_default_settings( 'ajs_other_text' ); // phpcs:ignore ?>"  /></label></td> </tr>
			<tr style="display:none"> <th scope="row"><?php esc_html_e( '&ldquo;No other results&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_no_other_text"> <input name="commercekit[ajs_no_other_text]" type="text" class="pc100" id="commercekit_ajs_no_other_text" value="<?php echo isset( $commercekit_options['ajs_no_other_text'] ) && ! empty( $commercekit_options['ajs_no_other_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_no_other_text'] ) ) : commercekit_get_default_settings( 'ajs_no_other_text' ); // phpcs:ignore ?>"  /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( '&ldquo;View all other results&rdquo; text', 'commercegurus-commercekit' ); ?></th> <td> <label class="text-label" for="commercekit_ajs_other_all_text"> <input name="commercekit[ajs_other_all_text]" type="text" class="pc100" id="commercekit_ajs_other_all_text" value="<?php echo isset( $commercekit_options['ajs_other_all_text'] ) && ! empty( $commercekit_options['ajs_other_all_text'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_other_all_text'] ) ) : commercekit_get_default_settings( 'ajs_other_all_text' ); // phpcs:ignore ?>"  /></label></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Exclude other results', 'commercegurus-commercekit' ); ?></th>  <td> <label class="text-label" for="commercekit_ajs_excludes_other"> <input name="commercekit[ajs_excludes_other]" type="text" id="commercekit_ajs_excludes_other" class="pc100" value="<?php echo isset( $commercekit_options['ajs_excludes_other'] ) && ! empty( $commercekit_options['ajs_excludes_other'] ) ? esc_attr( $commercekit_options['ajs_excludes_other'] ) : ''; ?>"  /></label><br /><small><?php esc_html_e( 'Enter post / page IDs to be excluded, separated by commas.', 'commercegurus-commercekit' ); ?></small></td></tr>  
			<tr> <th scope="row"><?php esc_html_e( 'Other results displayed', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_other_count"> <input name="commercekit[ajs_other_count]" type="number" class="pc100" id="commercekit_ajs_other_count" min="1" max="5" value="<?php echo isset( $commercekit_options['ajs_other_count'] ) && ! empty( $commercekit_options['ajs_other_count'] ) ? esc_attr( stripslashes_deep( $commercekit_options['ajs_other_count'] ) ) : commercekit_get_default_settings( 'ajs_other_count' ); // phpcs:ignore ?>" style="max-width: 100px;" /></label></td> </tr>
			<tr>
				<td colspan="2" class="cg-fast-search-wrap" style="padding: 0">
					<div class="cg-fast-search">
						<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:#767689;}.cls-2{fill:#fcc667;}.cls-3{fill:#f9f9f9;}.cls-4{fill:#fbae36;}.cls-5{fill:#e6e5e1;}.cls-6{fill:#fa5b6e;}.cls-7{fill:#fe8657;}.cls-8{fill:#16244d;}</style></defs><g data-name="Flying Rocket" id="Flying_Rocket"><path class="cls-1" d="M32.55,40.52l-1.21,1.2L33.7,53.83,44,43.49A2.87,2.87,0,0,0,44.45,40l-2.77-4.46L28.32,22.16l-4.39-2.73a2.88,2.88,0,0,0-3.54.41L10.05,30.19l12.11,2.35,1.21-1.21Z" id="path1750-6-9"/><path class="cls-2" d="M29.17,37.14l4.6,4.59,14.8-11.68A39.5,39.5,0,0,0,59.83,4,39.53,39.53,0,0,0,33.77,15.26L22.09,30.06l4.66,4.65Z" id="rect1717-0-1"/><circle class="cls-3" cx="43.88" cy="19.95" id="path1730-4-9" r="4.72"/><path class="cls-3" d="M26.76,39.55l3.38,3.38,2.41-2.41-3.38-3.38-2.42-2.43-3.38-3.38L21,33.75l3.39,3.38Z" id="rect1734-62-6"/><path class="cls-1" d="M34.78,29.1a1.71,1.71,0,0,1,0,2.42L22.85,43.46A1.71,1.71,0,0,1,20.42,41L32.36,29.1A1.71,1.71,0,0,1,34.78,29.1Z" id="rect1737-67-2"/><path class="cls-4" d="M59.83,4c-1,.06-2.06.16-3.07.29a39.11,39.11,0,0,1-11,23L31,39l2.79,2.78,14.8-11.68A39.5,39.5,0,0,0,59.83,4Z" id="path7836-5"/><path class="cls-3" d="M59.83,4A43,43,0,0,0,49.77,5.77s0,.15,0,.15a22.91,22.91,0,0,0,3.56,4.57,23.66,23.66,0,0,0,4.57,3.56l.13.06A43,43,0,0,0,59.83,4Z" id="path1727-0-44"/><path class="cls-5" d="M59.83,4c-1,.06-2.06.16-3.07.29A41.64,41.64,0,0,1,55,12a22.62,22.62,0,0,0,2.87,2l.13.06A43,43,0,0,0,59.83,4Z" id="path7852-9"/><path class="cls-6" d="M17.54,55.66c-2.49,2.49-10,6-12.51,3.48s1-10,3.48-12.5a6.38,6.38,0,0,1,9,9Z" id="path2052-7"/><path class="cls-7" d="M17.54,51.68c-1.39,1.39-5.6,3.33-7,1.94s.55-5.59,1.95-7a3.56,3.56,0,1,1,5,5Z" id="path2055-4"/><path class="cls-8" d="M59.53,3.23A40.47,40.47,0,0,0,33.07,14.67c-.1.1,0,0-4.92,6.23l-3.58-2.23a3.83,3.83,0,0,0-4.75.55L9.57,29.46a1,1,0,0,0-.25,1,1,1,0,0,0,.77.69l10.19,2a1,1,0,0,0-.2.56,1,1,0,0,0,.3.71L23,37.05l-3.17,3.17A2.7,2.7,0,0,0,23.67,44l3.16-3.16,2.64,2.64a1,1,0,0,0,1.27.1l2,10.18a1,1,0,0,0,.7.77.92.92,0,0,0,.29,0,1,1,0,0,0,.71-.29L44.67,44.06a3.81,3.81,0,0,0,.54-4.74l-2.27-3.66c6-4.69,6.11-4.81,6.21-4.91A40.33,40.33,0,0,0,60.59,4.29a1,1,0,0,0-1.06-1.06Zm-2.31,9.46A21.52,21.52,0,0,1,53.88,10a21.59,21.59,0,0,1-2.75-3.34,41.71,41.71,0,0,1,7.38-1.29A41.44,41.44,0,0,1,57.22,12.69Zm-36,8a1.82,1.82,0,0,1,2.27-.26l3.39,2.11-5.47,6.94a1,1,0,0,0,.08,1.33l.55.55-.11.11-9.62-1.87ZM22.51,33.7l1-1,1.93,1.93-1,1Zm-.26,8.91a.69.69,0,1,1-1-1L33.1,29.81a.69.69,0,0,1,1,0h0a.7.7,0,0,1,0,1l-5.56,5.56h0l-2.38,2.39h0Zm6-3.16,1-1,1.92,1.93-1,1Zm15.26.93a1.83,1.83,0,0,1-.26,2.26l-8.91,8.91-1.87-9.61.1-.11.5.49a1,1,0,0,0,.71.29,1,1,0,0,0,.62-.21l7-5.49Zm4.26-11.09-13.91,11-3.21-3.21,4.85-4.85a2.7,2.7,0,0,0,0-3.82,2.77,2.77,0,0,0-3.82,0l-4.85,4.85L23.56,30l11-13.92A36.77,36.77,0,0,1,49.13,7.21a23.38,23.38,0,0,0,3.33,4.16,23.32,23.32,0,0,0,4.16,3.32A36.87,36.87,0,0,1,47.77,29.29Z"/><path class="cls-8" d="M47.81,16a5.68,5.68,0,1,0-8,8,5.68,5.68,0,0,0,8-8Zm-1.42,6.62a3.67,3.67,0,1,1,0-5.2A3.68,3.68,0,0,1,46.39,22.63Z"/><path class="cls-8" d="M18.25,45.93h0a7.38,7.38,0,0,0-10.44,0C5.29,48.44,1.18,56.71,4.32,59.85A4.43,4.43,0,0,0,7.57,61c3.76,0,8.81-2.75,10.68-4.62A7.39,7.39,0,0,0,18.25,45.93Zm-1.42,5c-1.41,1.4-4.89,2.62-5.57,1.94s.54-4.16,2-5.56A2.5,2.5,0,0,1,15,46.6,2.56,2.56,0,0,1,16.83,51ZM5.74,58.43c-1.79-1.79,1-8.55,3.48-11.08a5.34,5.34,0,0,1,2.56-1.42c-1.58,1.59-3.95,6.39-1.94,8.4A3,3,0,0,0,12,55.1a10.63,10.63,0,0,0,6.24-2.7A5.4,5.4,0,0,1,16.83,55C14.3,57.48,7.53,60.22,5.74,58.43Z"/></g></svg>
						<table>
							<tr>
								<td colspan="2">
									<h3 style="margin:0;"><?php esc_html_e( 'Lightning Fast Results (Beta)', 'commercegurus-commercekit' ); ?></h3>
									<p><?php esc_html_e( 'Note: This option is not suitable for all stores as some servers do not support direct PHP script access. Search result suggestions will load extremely fast but will not display prices.', 'commercegurus-commercekit' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th>
								<td><label for="commercekit_ajs_fast_search" class="toggle-switch"> <input name="commercekit[ajs_fast_search]" type="checkbox" id="commercekit_ajs_fast_search" value="1" <?php echo isset( $commercekit_options['ajs_fast_search'] ) && 1 === (int) $commercekit_options['ajs_fast_search'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable Lightning Fast Results (excludes prices)', 'commercegurus-commercekit' ); ?></label></td>
							</tr>
						</table>
					</div>
				</td> 
			</tr>
			<tr> <td colspan="2"><h3 class="top-divider"><?php esc_html_e( 'Clear Ajax Search Index', 'commercegurus-commercekit' ); ?></h3></td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Total products', 'commercegurus-commercekit' ); ?></th> <td> <strong><span title="<?php esc_html_e( 'Total products', 'commercegurus-commercekit' ); ?>" id="cgkit-ajs-total"><?php echo esc_attr( $total ); ?></span></strong> </td> </tr>
			<tr> <th scope="row"><?php esc_html_e( 'Indexed products', 'commercegurus-commercekit' ); ?></th> <td> <strong style="position: relative; bottom: -1px"><span title="<?php esc_html_e( 'Total indexed products', 'commercegurus-commercekit' ); ?>" id="cgkit-ajs-cached"><?php echo esc_attr( $complete ); ?></span> </strong> <button type="button" class="button button-primary clear-cache" id="wc-product-ajs-index" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to clear and rebuild the ajax search index?', 'commercegurus-commercekit' ); ?>')){update_wc_product_ajs_index_status(true);}"disabled="disabled"><?php esc_html_e( 'Clear and rebuild Ajax Search index', 'commercegurus-commercekit' ); ?></button> <input type="button" name="btn-submit3" id="btn-submit3" class="button" value="<?php esc_html_e( 'Cancel', 'commercegurus-commercekit' ); ?>" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to cancel indexing process?', 'commercegurus-commercekit' ); ?>')){ cancel_ajs_build(); }" style="visibility: hidden; margin-left: 10px;" /> </td> </tr>
				<tr id="cgkit-ajs-logger" class="disable-events"> <th scope="row"><?php esc_html_e( 'Enable logger', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_ajs_index_logger" class="toggle-switch"> <input name="commercekit[ajs_index_logger]" type="checkbox" id="commercekit_ajs_index_logger" value="1" <?php echo isset( $commercekit_options['ajs_index_logger'] ) && 1 === (int) $commercekit_options['ajs_index_logger'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Enable Product Ajax Search index rebuilding logger', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr> <td colspan="2">
			<div id="cgkit-ajs-cache-status">
				<!-- When starting -->
				<div class="cache-event-alert" id="event-created" style="display:none;">
					<div class="cache-loader">
						<div class="att-loader"></div><?php esc_html_e( 'Indexing event being created...', 'commercegurus-commercekit' ); ?>
					</div>
				</div>

				<!-- Switch to this when processing -->
				<div class="cache-event-alert" id="event-processing" style="display:none;">
					<div class="cache-processing">
						<div class="cache-bar">
							<div class="cache-progress" id="percent" style="width: 0%"></div>
						</div>
						<div class="cache-value">
							<div class="att-loader"></div><?php esc_html_e( 'Processing indexing event.', 'commercegurus-commercekit' ); ?>&nbsp;<span id="complete">0</span>/<span id="total">0</span>&nbsp;<?php esc_html_e( 'completed...', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
				</div>

				<!-- Switch to this when completed -->
				<div class="cache-event-alert" id="event-completed" style="display:none;">
					<div class="cache-processing">
						<div class="cache-bar">
							<div class="cache-completed" style="width: 100%"></div>
						</div>
						<div class="cache-value completed">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<?php esc_html_e( 'Index rebuild complete', 'commercegurus-commercekit' ); ?>
						</div>
					</div>
					<h3 class="ckit_sub-heading" style="display:none;"><?php esc_html_e( 'Last log message', 'commercegurus-commercekit' ); ?></h3>
					<div class="log-message" style="display:none;"><?php esc_html_e( 'The index process apparently succeeded and is now complete', 'commercegurus-commercekit' ); ?> (<span id="build_done"></span>)</div>
				</div>

				<!-- Switch to this when interrupt -->
				<div class="cache-event-alert" id="event-interrupt" style="display:none;">
					<div class="log-message log-failed"><?php esc_html_e( 'The indexing process interrupted due to disabled Ajax Search module. Enable Ajax Search module and click on "Clear and rebuild ajax search index" button to rebuild all products ajax search index.', 'commercegurus-commercekit' ); ?></div>
				</div>

				<!-- Switch to this when cancelled -->
				<div class="cache-event-alert" id="event-cancelled" style="display:none;">
					<div class="log-message log-failed"><?php esc_html_e( 'The indexing process has been cancelled. Click on the "Clear and rebuild Ajax Search index" button to restart it.', 'commercegurus-commercekit' ); ?></div>
				</div>
			</div>			
			</td> </tr>
		</table>
		<input type="hidden" name="tab" value="ajax-search" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
	</div>
	<?php } ?>

	<?php if ( 'reports' === $section ) { ?>
	<div class="inside ajax-search-reports">
		<div class="ajax-search-reports-boxes">
			<div class="ajax-search-reports-box">
				<h2><?php esc_html_e( 'Total searches', 'commercegurus-commercekit' ); ?></h2>
				<h3 id="total-searches"><?php echo isset( $reports['search_count'] ) ? esc_attr( $reports['search_count'] ) : 0; ?></h3>
				<p><?php esc_html_e( 'How many searches have been performed.', 'commercegurus-commercekit' ); ?></p>
			</div>
			<div class="ajax-search-reports-box">
				<h2><?php esc_html_e( 'Clickthrough rate', 'commercegurus-commercekit' ); ?></h2>
				<h3 id="clickthrough-rate"><?php echo isset( $reports['click_percent'] ) ? esc_attr( $reports['click_percent'] ) : 0; ?>%</h3>
				<p><?php esc_html_e( 'The % of searches that resulted in a click.', 'commercegurus-commercekit' ); ?></p>
			</div>
			<div class="ajax-search-reports-box">
				<h2><?php esc_html_e( 'No result rate', 'commercegurus-commercekit' ); ?></h2>
				<h3 id="no-result-percent"><?php echo isset( $reports['nores_percent'] ) ? esc_attr( $reports['nores_percent'] ) : 0; ?>%</h3>
				<p><?php esc_html_e( 'The % of searches that returned no results.', 'commercegurus-commercekit' ); ?></p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Most frequent searches', 'commercegurus-commercekit' ); ?></h2>
		<p><?php esc_html_e( 'Discover what your users are searching for most.', 'commercegurus-commercekit' ); ?></p>
		<button type="button" class="button button-primary" id="reset-ajs-statistics"><?php esc_html_e( 'Reset Reports', 'commercegurus-commercekit' ); ?></button>
		<div id="reset-ajs-modal" style="display: none;">
			<div class="reset-ajs-modal-content">
				<p><?php esc_html_e( 'If you confirm this, it will also reset all of your search statistics data, including total searches, your clickthrough rate, and your no result rate. Are you sure you want to do this?', 'commercegurus-commercekit' ); ?></p>
			</div>
			<div class="reset-ajs-modal-action">
				<button type="button" class="button button-primary" id="reset-ajs-yes"><?php esc_html_e( 'Yes, I confirm I want to do this', 'commercegurus-commercekit' ); ?></button><button type="button" class="button" id="reset-ajs-cancel"><?php esc_html_e( 'Cancel', 'commercegurus-commercekit' ); ?></button>
			</div>
		</div>
		<table class="ajax-search-reports-list" id="frequent-searches">
			<tr><th class="left"><?php esc_html_e( 'Term', 'commercegurus-commercekit' ); ?></th><th class="right"><?php esc_html_e( 'Count', 'commercegurus-commercekit' ); ?></th></tr>
			<?php if ( isset( $reports['most_results'] ) && count( $reports['most_results'] ) ) { ?>
				<?php foreach ( $reports['most_results'] as $index => $row ) { ?>
					<tr><td class="left"><span><?php echo esc_attr( str_pad( $index + 1, 2, '0', STR_PAD_LEFT ) ); ?></span> <?php echo isset( $row['search_term'] ) ? esc_attr( $row['search_term'] ) : ''; ?></td><td class="right"><?php echo isset( $row['search_count'] ) ? esc_attr( number_format( $row['search_count'], 0 ) ) : 0; ?></td></tr>
				<?php } ?>
			<?php } else { ?>
				<tr><td class="center" colspan="2"><?php esc_html_e( 'No terms', 'commercegurus-commercekit' ); ?></td></tr>
			<?php } ?>
		</table>

		<h2><?php esc_html_e( 'Most frequent searches returning 0 results', 'commercegurus-commercekit' ); ?></h2>
		<p><?php esc_html_e( 'Users are searching for these queries and encounter no results.', 'commercegurus-commercekit' ); ?></p>
		<button type="button" class="button button-primary" id="reset-ajs-no-result" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to reset no result counts only?', 'commercegurus-commercekit' ); ?>')){reset_ajs_zero_results();}" style="margin-top: 10px"><?php esc_html_e( 'Reset no result counts', 'commercegurus-commercekit' ); ?></button>
		<table class="ajax-search-reports-list" id="ajs-zero-results">
			<tr><th class="left"><?php esc_html_e( 'Term', 'commercegurus-commercekit' ); ?></th><th class="right"><?php esc_html_e( 'Count', 'commercegurus-commercekit' ); ?></th></tr>
			<?php if ( isset( $reports['no_results'] ) && count( $reports['no_results'] ) ) { ?>
				<?php foreach ( $reports['no_results'] as $index => $row ) { ?>
					<tr><td class="left"><span><?php echo esc_attr( str_pad( $index + 1, 2, '0', STR_PAD_LEFT ) ); ?></span> <?php echo isset( $row['search_term'] ) ? esc_attr( $row['search_term'] ) : ''; ?></td><td class="right"><?php echo isset( $row['no_result_count'] ) ? esc_attr( number_format( $row['no_result_count'], 0 ) ) : 0; ?></td></tr>
				<?php } ?>
			<?php } else { ?>
				<tr><td class="center" colspan="2"><?php esc_html_e( 'No terms', 'commercegurus-commercekit' ); ?></td></tr>
			<?php } ?>
		</table>
		<p class="report-note"><?php esc_html_e( 'NOTE: Report data is updated every 24 hours.', 'commercegurus-commercekit' ); ?></p>
	</div>
	<?php } ?>
</div>
<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Ajax Search', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'Research has shown that instant search results are an important feature on eCommerce sites. It helps users save time and find products faster.', 'commercegurus-commercekit' ); ?></p>
</div>

<script>
var cancel_btn_clicked = false;
jQuery(document).ready(function(){
	if( jQuery('#wc-product-ajs-index').length > 0 ) {
		update_wc_product_ajs_index_status(false);
	}
	jQuery('#reset-ajs-statistics').on('click', function(){
		jQuery('#reset-ajs-modal').show();
	});
	jQuery('#reset-ajs-yes').on('click', function(){
		reset_ajs_statistics();
		jQuery('#reset-ajs-modal').hide();
	});
	jQuery('#reset-ajs-cancel').on('click', function(){
		jQuery('#reset-ajs-modal').hide();
	});
});
function update_wc_product_ajs_index_status(generate){
	var cach_btn = jQuery('#wc-product-ajs-index');
	var ajs_logger = jQuery('#cgkit-ajs-logger');
	var cnlsSub = jQuery('#btn-submit3');
	cach_btn.attr('disabled', 'disabled');
	ajs_logger.addClass('disable-events');
	var generate_ajs = generate ? 1 : 0;
	if( generate ) {
		jQuery('#cgkit-ajs-cache-status .cache-event-alert').hide();
	}
	var nonce = jQuery('#commercekit_nonce').val();
	jQuery.ajax({
		url: ajaxurl+'?action=commercekit_ajs_generate_wc_product_index',
		type: 'POST',
		data: { generate_ajs: generate_ajs, commercekit_nonce: nonce },
		dataType: 'json',
		success: function( json ) {
			jQuery('#cgkit-ajs-cache-status .cache-event-alert').hide();
			if( json.cancelled_ajs == 1 ){
				jQuery('#cgkit-ajs-cached').html(json.complete);
				jQuery('#event-cancelled').show();
			} else if( json.interrupt_ajs == 1 ){
				jQuery('#cgkit-ajs-cached').html(json.complete);
				jQuery('#event-interrupt').show();
			} else if( json.generating_ajs == 1 && json.complete == 0 ){
				jQuery('#cgkit-ajs-cached').html('0');
				jQuery('#event-created').show();
			} else if ( json.generating_ajs == 1 && json.complete != 0 ){
				jQuery('#cgkit-ajs-cached').html(json.complete);
				jQuery('#complete').html(json.complete);
				jQuery('#total').html(json.total);
				jQuery('#percent').css('width', json.percent+'%');
				jQuery('#event-processing').show();
			} else if ( json.generating_ajs != 1 && json.total == json.complete ){
				jQuery('#cgkit-ajs-cached').html(json.complete);
				jQuery('#build_done').html(json.build_done);
				jQuery('#event-completed').show();
			}
			if ( json.generating_ajs == 1 ){
				if ( ! cancel_btn_clicked ) {
					cnlsSub.css('visibility', 'visible');
				}
				setTimeout( function(){ update_wc_product_ajs_index_status(false); }, 5000 );
			} else {
				cach_btn.removeAttr('disabled');
				ajs_logger.removeClass('disable-events');
				cnlsSub.css('visibility', 'hidden');
			}
		}
	});
}
function reset_ajs_zero_results(){
	jQuery('#ajax-loading-mask').show();
	var nonce = jQuery('#commercekit_nonce').val();
	jQuery.ajax({
		url: ajaxurl+'?action=commercekit_ajs_reset_zero_results',
		type: 'POST',
		data: { commercekit_nonce: nonce },
		dataType: 'json',
		success: function( json ) {
			jQuery('#ajax-loading-mask').hide();
			if ( json.success == 1 ) {
				jQuery('#ajs-zero-results').html(json.html);
				jQuery('#no-result-percent').html(json.percent);
			}
		}
	});
}
function cancel_ajs_build(){
	var btnSub = jQuery('#btn-submit3');
	var formData = new FormData();
	var nonce = jQuery('#commercekit_nonce').val();
	formData.append('action', 'commercekit_ajs_generate_cancel');
	formData.append('commercekit_nonce', nonce);
	btnSub.attr('disabled', 'disabled');
	cancel_btn_clicked = true;
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: formData,
		processData: false,
		contentType: false,
		success: function( json ) {
			btnSub.removeAttr('disabled');
			btnSub.css('visibility', 'hidden');
		}
	});
}
function reset_ajs_statistics(){
	jQuery('#ajax-loading-mask').show();
	var nonce = jQuery('#commercekit_nonce').val();
	jQuery.ajax({
		url: ajaxurl+'?action=commercekit_ajs_reset_statistics',
		type: 'POST',
		data: { commercekit_nonce: nonce },
		dataType: 'json',
		success: function( json ) {
			jQuery('#ajax-loading-mask').hide();
			if ( json.success == 1 ) {
				jQuery('#frequent-searches').html(json.frequent_search);
				jQuery('#total-searches').html(json.total_search);
				jQuery('#clickthrough-rate').html(json.click_rate);
				jQuery('#ajs-zero-results').html(json.zero_results);
				jQuery('#no-result-percent').html(json.zero_percent);
			}
		}
	});
}
</script>

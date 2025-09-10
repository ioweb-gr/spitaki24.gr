<?php
/**
 *
 * Admin Countdown Timer
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

$countdown = isset( $commercekit_options['countdown'] ) ? $commercekit_options['countdown'] : array();
?>
<div id="settings-content">
<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Countdown Timers', 'commercegurus-commercekit' ); ?></span></h2>
	<div class="inside">
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="commercekit_countdown_timer" class="toggle-switch"> <input name="commercekit[countdown_timer]" type="checkbox" id="commercekit_countdown_timer" value="1" <?php echo isset( $commercekit_options['countdown_timer'] ) && 1 === (int) $commercekit_options['countdown_timer'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable countdowns on single product and checkout pages', 'commercegurus-commercekit' ); ?></label></td> </tr>
		</table>
	</div>
</div>

<div class="postbox content-box">
	<h2><span class="table-heading"><?php esc_html_e( 'Product Countdown', 'commercegurus-commercekit' ); ?></span><span id="ctd-order-notice" style="display:none;"><?php esc_html_e( 'The order of the countdowns is important. Drag a countdown to the top to take precedence.', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside meta-box-sortables" id="product-countdown">

		<div class="postbox no-change closed" id="first-row" style="display:none;">
			<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
			<h2 class="gray"><span><?php esc_html_e( 'Title', 'commercegurus-commercekit' ); ?></span></h2>
			<div class="inside">
				<table class="form-table countdown-timer" role="presentation">
					<tr> 
						<th width="20%"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_ctd_pdt_at[]" type="checkbox" class="check pdt-active" value="1"><span class="toggle-slider"></span><input type="hidden" name="commercekit[countdown][product][active][]" class="pdt-active-val" value="0" /><input type="hidden" name="commercekit[countdown][product][activeo][]" class="pdt-active-val" value="0" /></label><?php esc_html_e( 'Enable countdown timer on product page', 'commercegurus-commercekit' ); ?></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Title', 'commercegurus-commercekit' ); ?> <span class="star">*</span></th>
						<td> <label class="text-label"><input name="commercekit[countdown][product][title][]" type="text" class="title pdt-title text required" value="" /></label><br /><small><?php esc_html_e( 'e.g. Hurry! This sale ends in', 'commercegurus-commercekit' ); ?></small></td> 
					</tr>
					<tr class="end-inputs"> 
						<th><?php esc_html_e( 'Ends', 'commercegurus-commercekit' ); ?>:</th>
						<td class="ends-wrap"> 
							<label><input name="commercekit[countdown][product][days][]" type="number" min="0" class="ends text" value="0" /><br /><span class="ends-label"><?php esc_html_e( 'Days', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][hours][]" type="number" min="0" max="23" class="ends text" value="0" /><br /><span class="ends-label"><?php esc_html_e( 'Hours', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][minutes][]" type="number" min="0" max="59" class="ends text" value="0" /><br /><span class="ends-label"><?php esc_html_e( 'Minutes', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][seconds][]" type="number" min="0" max="59" class="ends text" value="0" /><br /><span class="ends-label"><?php esc_html_e( 'Seconds', 'commercegurus-commercekit' ); ?></span></label>
						</td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Labels', 'commercegurus-commercekit' ); ?> </th>
						<td class="ends-wrap"> 
							<label><input name="commercekit[countdown][product][days_label][]" type="text" min="0" class="ends text days" value="<?php esc_html_e( 'Days', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label">Days</span></label>
							<label><input name="commercekit[countdown][product][hours_label][]" type="text" min="0" max="23" class="ends text hours" value="<?php esc_html_e( 'Hours', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Hours', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][minutes_label][]" type="text" min="0" max="59" class="ends text minutes" value="<?php esc_html_e( 'Mins', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Minutes', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][seconds_label][]" type="text" min="0" max="59" class="ends text seconds" value="<?php esc_html_e( 'Secs', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Seconds', 'commercegurus-commercekit' ); ?></span></label>
						</td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Hide when finished', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_ctd_pdt_ht[]" type="checkbox" class="check pdt-hide-timer" value="1"><span class="toggle-slider"></span><input type="hidden" name="commercekit[countdown][product][hide_timer][]" class="pdt-hide-timer-val" value="0" /></label>&nbsp;&nbsp;<?php esc_html_e( 'Hide the countdown when it reaches zero', 'commercegurus-commercekit' ); ?></td> 
					</tr>
					<tr class="timer-custom-message" style="display:none;"> 
						<th><?php esc_html_e( 'Custom message', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="text-label"><input name="commercekit[countdown][product][custom_msg][]" type="text" class="title text" value="" /></label><br /><small><?php esc_html_e( 'A custom message can be displayed instead when the countdown has finished.', 'commercegurus-commercekit' ); ?></small></td> 
					</tr>
					<tr> 
						<th>

						<div class="label-tooltip">
							<?php esc_html_e( 'Type', 'commercegurus-commercekit' ); ?>:
							<span class="tooltip-wrapper">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tooltip-icon">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								<span class="tooltip" data-text="<?php esc_html_e( 'Evergreen sets a dynamic countdown timer for each visitor.', 'commercegurus-commercekit' ); ?>"></span>
							</span>
						</div>

					</th>
						<td> <label><input type="radio" value="0" class="pdt-type radio-0" name="radio-0" checked="checked" />&nbsp;<?php esc_html_e( 'One-time', 'commercegurus-commercekit' ); ?></label> <span class="radio-space">&nbsp;</span><label><input type="radio" value="1" class="pdt-type radio-1" name="radio-0" />&nbsp;<?php esc_html_e( 'Evergreen', 'commercegurus-commercekit' ); ?></label> <span class="radio-space">&nbsp;</span><label><input type="radio" value="2" class="pdt-type radio-2" name="radio-0" />&nbsp;<?php esc_html_e( 'Single campaign', 'commercegurus-commercekit' ); ?></label><input type="hidden" name="commercekit[countdown][product][type][]" class="pdt-type-val" value="0" /></td>
					</tr>
					<tr class="product-dates" style="display:none;">
						<th>&nbsp;</th>
						<td>
							<table class="single-campaign">
								<tr>
									<td>
										<label><span><input name="commercekit[countdown][product][from_date][]" type="text" class="text cgkit-date" value="" placeholder="YYYY-MM-DD" /><select name="commercekit[countdown][product][from_date_h][]"><?php commercekit_get_hours_minutes_options( 23, '' ); ?></select><select name="commercekit[countdown][product][from_date_m][]"><?php commercekit_get_hours_minutes_options( 59, '' ); ?></select></span><span class="ends-label">From date: YYYY-MM-DD Hours Minutes</span></label>
									</td>
									<td>
										<label><span><input name="commercekit[countdown][product][to_date][]" type="text" class="text cgkit-date" value="" placeholder="YYYY-MM-DD" /><select name="commercekit[countdown][product][to_date_h][]"><?php commercekit_get_hours_minutes_options( 23, '' ); ?></select><select name="commercekit[countdown][product][to_date_m][]"><?php commercekit_get_hours_minutes_options( 59, '' ); ?></select></span>

										<span class="ends-label">To date: YYYY-MM-DD Hours Minutes
											<div class="label-tooltip">
												<span class="tooltip-wrapper">
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tooltip-icon">
														<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
													</svg>
													<span class="tooltip" data-text="Server time: <?php echo esc_attr( gmdate( 'Y-m-d H:i', time() ) ); ?>"></span>
												</span>
											</div>
										</span>

										</label>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Conditions', 'commercegurus-commercekit' ); ?> </th>
						<td> 
							<select name="commercekit[countdown][product][condition][]" class="conditions">
								<option value="all" selected="selected"><?php esc_html_e( 'All products', 'commercegurus-commercekit' ); ?></option>
								<option value="products"><?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?></option>
								<option value="non-products"><?php esc_html_e( 'All products apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="categories"><?php esc_html_e( 'Specific categories', 'commercegurus-commercekit' ); ?></option>
								<option value="non-categories"><?php esc_html_e( 'All categories apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="tags"><?php esc_html_e( 'Specific tags', 'commercegurus-commercekit' ); ?></option>
								<option value="non-tags"><?php esc_html_e( 'All tags apart from', 'commercegurus-commercekit' ); ?></option>
							</select>
						</td> 
					</tr>
					<tr class="product-ids" style="display:none;">
						<th class="options">
						<?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?>
						</th>
						<td> <label><select name="commercekit_ctd_pdt_pids[]" class="select2" data-type="all" multiple="multiple" style="width:100%;"></select><input type="hidden" name="commercekit[countdown][product][pids][]" class="select3 text" value="" /></label></td> 
					</tr>
					<tr>
						<td colspan="2">
							<div class="countdown-shortcode-id-wrapper">
								<span class="countdown-shortcode left" style="visibility: hidden;"><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: <span class="countdown-shortcode-id">[commercekit_product_countdown id="1"]</span></span>
								<span class="countdown-delete right"><!--DELETE--></span>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
		$radio_count = 0;
		if ( isset( $countdown['product']['title'] ) && count( $countdown['product']['title'] ) > 0 ) {
			foreach ( $countdown['product']['title'] as $k => $product_title ) {
				if ( empty( $product_title ) ) {
					continue;
				}
				$radio_count = $k;
				?>
		<div class="postbox no-change closed">
			<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
			<h2 class="gray"><span>
				<?php
				echo isset( $countdown['product']['title'][ $k ] ) && ! empty( $countdown['product']['title'][ $k ] ) ? esc_attr( $countdown['product']['title'][ $k ] ) : esc_html__( 'Title', 'commercegurus-commercekit' );
				?>
			</span></h2>
			<div class="inside">
				<table class="form-table countdown-timer" role="presentation">
					<tr> 
						<th width="20%"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_ctd_pdt_at[]" type="checkbox" class="check pdt-active" value="1" <?php echo isset( $countdown['product']['active'][ $k ] ) && 1 === (int) $countdown['product']['active'][ $k ] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span><input type="hidden" name="commercekit[countdown][product][active][]" class="pdt-active-val" value="<?php echo isset( $countdown['product']['active'][ $k ] ) ? esc_attr( $countdown['product']['active'][ $k ] ) : '0'; ?>" /><input type="hidden" name="commercekit[countdown][product][activeo][]" class="pdt-active-val" value="<?php echo isset( $countdown['product']['activeo'][ $k ] ) ? esc_attr( $countdown['product']['activeo'][ $k ] ) : '0'; ?>" /></label>&nbsp;&nbsp;<?php esc_html_e( 'Enable countdown timer on product page', 'commercegurus-commercekit' ); ?></td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Title', 'commercegurus-commercekit' ); ?> <span class="star">*</span></th>
						<td> <label class="text-label"><input name="commercekit[countdown][product][title][]" type="text" class="title pdt-title text required" value="<?php echo isset( $countdown['product']['title'][ $k ] ) ? esc_attr( stripslashes_deep( $countdown['product']['title'][ $k ] ) ) : ''; ?>" /></label><br /><small><?php esc_html_e( 'e.g. Hurry! This sale ends in', 'commercegurus-commercekit' ); ?></small></td> 
					</tr>
					<tr class="end-inputs <?php echo isset( $countdown['product']['type'][ $k ] ) && 2 === (int) $countdown['product']['type'][ $k ] ? 'disable-events' : ''; ?>"> 
						<th><?php esc_html_e( 'Ends', 'commercegurus-commercekit' ); ?> </th>
						<td class="ends-wrap"> 
							<label><input name="commercekit[countdown][product][days][]" type="number" min="0" class="ends text" value="<?php echo isset( $countdown['product']['days'][ $k ] ) ? esc_attr( $countdown['product']['days'][ $k ] ) : '0'; ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Days', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][hours][]" type="number" min="0" max="23" class="ends text" value="<?php echo isset( $countdown['product']['hours'][ $k ] ) ? esc_attr( $countdown['product']['hours'][ $k ] ) : '0'; ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Hours', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][minutes][]" type="number" min="0" max="59" class="ends text" value="<?php echo isset( $countdown['product']['minutes'][ $k ] ) ? esc_attr( $countdown['product']['minutes'][ $k ] ) : '0'; ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Minutes', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][seconds][]" type="number" min="0" max="59" class="ends text" value="<?php echo isset( $countdown['product']['seconds'][ $k ] ) ? esc_attr( $countdown['product']['seconds'][ $k ] ) : '0'; ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Seconds', 'commercegurus-commercekit' ); ?></span></label>
						</td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Labels', 'commercegurus-commercekit' ); ?> </th>
						<td class="ends-wrap"> 
							<label><input name="commercekit[countdown][product][days_label][]" type="text" min="0" class="ends text days" value="<?php echo isset( $countdown['product']['days_label'][ $k ] ) && ! empty( $countdown['product']['days_label'][ $k ] ) ? esc_attr( $countdown['product']['days_label'][ $k ] ) : esc_html__( 'Days', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Days', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][hours_label][]" type="text" min="0" max="23" class="ends text hours" value="<?php echo isset( $countdown['product']['hours_label'][ $k ] ) && ! empty( $countdown['product']['hours_label'][ $k ] ) ? esc_attr( $countdown['product']['hours_label'][ $k ] ) : esc_html__( 'Hours', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Hours', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][minutes_label][]" type="text" min="0" max="59" class="ends text minutes" value="<?php echo isset( $countdown['product']['minutes_label'][ $k ] ) && ! empty( $countdown['product']['minutes_label'][ $k ] ) ? esc_attr( $countdown['product']['minutes_label'][ $k ] ) : esc_html__( 'Mins', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Minutes', 'commercegurus-commercekit' ); ?></span></label>
							<label><input name="commercekit[countdown][product][seconds_label][]" type="text" min="0" max="59" class="ends text seconds" value="<?php echo isset( $countdown['product']['seconds_label'][ $k ] ) && ! empty( $countdown['product']['seconds_label'][ $k ] ) ? esc_attr( $countdown['product']['seconds_label'][ $k ] ) : esc_html__( 'Secs', 'commercegurus-commercekit' ); ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Seconds', 'commercegurus-commercekit' ); ?></span></label>
						</td> 
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Hide when finished', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="toggle-switch"><input name="commercekit_ctd_pdt_ht[]" type="checkbox" class="check pdt-hide-timer" value="1" <?php echo isset( $countdown['product']['hide_timer'][ $k ] ) && 1 === (int) $countdown['product']['hide_timer'][ $k ] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span><input type="hidden" name="commercekit[countdown][product][hide_timer][]" class="pdt-hide-timer-val" value="<?php echo isset( $countdown['product']['hide_timer'][ $k ] ) ? esc_attr( $countdown['product']['hide_timer'][ $k ] ) : '0'; ?>" /></label>&nbsp;&nbsp;<?php esc_html_e( 'Hide the countdown when it reaches zero', 'commercegurus-commercekit' ); ?></td> 
					</tr>
					<tr class="timer-custom-message" <?php echo isset( $countdown['product']['hide_timer'][ $k ] ) && 1 === (int) $countdown['product']['hide_timer'][ $k ] ? '' : 'style="display:none;"'; ?>> 
						<th><?php esc_html_e( 'Custom message', 'commercegurus-commercekit' ); ?> </th>
						<td> <label class="text-label"><input name="commercekit[countdown][product][custom_msg][]" type="text" class="title text" value="<?php echo isset( $countdown['product']['custom_msg'][ $k ] ) ? esc_attr( $countdown['product']['custom_msg'][ $k ] ) : ''; ?>" /></label><br /><small><?php esc_html_e( 'A custom message can be displayed instead when the countdown has finished.', 'commercegurus-commercekit' ); ?></small></td> 
					</tr>
					<tr> 
						<th>
						<div class="label-tooltip">
							<?php esc_html_e( 'Type', 'commercegurus-commercekit' ); ?>:
							<span class="tooltip-wrapper">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tooltip-icon">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								<span class="tooltip" data-text="<?php esc_html_e( 'Evergreen sets a dynamic countdown timer for each visitor.', 'commercegurus-commercekit' ); ?>"></span>
							</span>
						</div>
						</th>
						<td> <label><input type="radio" value="0" class="pdt-type radio-0" name="radio-<?php echo esc_attr( $k ); ?>" <?php echo ( isset( $countdown['product']['type'][ $k ] ) && 0 === (int) $countdown['product']['type'][ $k ] ) || ! isset( $countdown['product']['type'][ $k ] ) ? 'checked="checked"' : ''; ?> />&nbsp;<?php esc_html_e( 'One-time', 'commercegurus-commercekit' ); ?></label> <span class="radio-space">&nbsp;</span><label><input type="radio" value="1" class="pdt-type radio-1" name="radio-<?php echo esc_attr( $k ); ?>" <?php echo isset( $countdown['product']['type'][ $k ] ) && 1 === (int) $countdown['product']['type'][ $k ] ? 'checked="checked"' : ''; ?> />&nbsp;<?php esc_html_e( 'Evergreen', 'commercegurus-commercekit' ); ?></label> <span class="radio-space">&nbsp;</span><label><input type="radio" value="2" class="pdt-type radio-2" name="radio-<?php echo esc_attr( $k ); ?>" <?php echo isset( $countdown['product']['type'][ $k ] ) && 2 === (int) $countdown['product']['type'][ $k ] ? 'checked="checked"' : ''; ?> />&nbsp;<?php esc_html_e( 'Single campaign', 'commercegurus-commercekit' ); ?></label><input type="hidden" name="commercekit[countdown][product][type][]" class="pdt-type-val" value="<?php echo isset( $countdown['product']['type'][ $k ] ) ? esc_attr( $countdown['product']['type'][ $k ] ) : '0'; ?>" /></td> 
					</tr>
					<tr class="product-dates" <?php echo isset( $countdown['product']['type'][ $k ] ) && 2 === (int) $countdown['product']['type'][ $k ] ? '' : 'style="display: none;"'; ?>>
						<th>&nbsp;</th>
						<td>
							<label><span><input name="commercekit[countdown][product][from_date][]" type="text" class="text cgkit-date <?php echo isset( $countdown['product']['type'][ $k ] ) && 2 === (int) $countdown['product']['type'][ $k ] ? 'required' : ''; ?>" value="<?php echo isset( $countdown['product']['from_date'][ $k ] ) ? esc_attr( $countdown['product']['from_date'][ $k ] ) : ''; ?>" placeholder="YYYY-MM-DD" />&nbsp;<select name="commercekit[countdown][product][from_date_h][]"><?php commercekit_get_hours_minutes_options( 23, isset( $countdown['product']['from_date_h'][ $k ] ) ? $countdown['product']['from_date_h'][ $k ] : '' ); ?></select>&nbsp;<select name="commercekit[countdown][product][from_date_m][]"><?php commercekit_get_hours_minutes_options( 59, isset( $countdown['product']['from_date_m'][ $k ] ) ? $countdown['product']['from_date_m'][ $k ] : '' ); ?></select></span><br /><span class="ends-label">From date: YYYY-MM-DD Hours Minutes</span></label>
							<label><span><input name="commercekit[countdown][product][to_date][]" type="text" class="text cgkit-date <?php echo isset( $countdown['product']['type'][ $k ] ) && 2 === (int) $countdown['product']['type'][ $k ] ? 'required' : ''; ?>" value="<?php echo isset( $countdown['product']['to_date'][ $k ] ) ? esc_attr( $countdown['product']['to_date'][ $k ] ) : ''; ?>" placeholder="YYYY-MM-DD" />&nbsp;<select name="commercekit[countdown][product][to_date_h][]"><?php commercekit_get_hours_minutes_options( 23, isset( $countdown['product']['to_date_h'][ $k ] ) ? $countdown['product']['to_date_h'][ $k ] : '' ); ?></select>&nbsp;<select name="commercekit[countdown][product][to_date_m][]"><?php commercekit_get_hours_minutes_options( 59, isset( $countdown['product']['to_date_m'][ $k ] ) ? $countdown['product']['to_date_m'][ $k ] : '' ); ?></select></span><br /><span class="ends-label">TO DATE: YYYY-MM-DD Hours Minutes<span class="dashicons dashicons-info"><span class="tooltip" data-text="Server time: <?php echo esc_attr( gmdate( 'Y-m-d H:i', time() ) ); ?>"></span></span></span></label>
						</td>
					</tr>
					<tr> 
						<th><?php esc_html_e( 'Conditions', 'commercegurus-commercekit' ); ?></th>
						<td> 
							<?php
							$ctype = 'all';
							if ( isset( $countdown['product']['condition'][ $k ] ) && in_array( $countdown['product']['condition'][ $k ], array( 'products', 'non-products' ), true ) ) {
								$ctype = 'products';
							}
							if ( isset( $countdown['product']['condition'][ $k ] ) && in_array( $countdown['product']['condition'][ $k ], array( 'categories', 'non-categories' ), true ) ) {
								$ctype = 'categories';
							}
							if ( isset( $countdown['product']['condition'][ $k ] ) && in_array( $countdown['product']['condition'][ $k ], array( 'tags', 'non-tags' ), true ) ) {
								$ctype = 'tags';
							}
							?>
							<select name="commercekit[countdown][product][condition][]" class="conditions">
								<option value="all" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'all' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All products', 'commercegurus-commercekit' ); ?></option>
								<option value="products" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'products' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific products', 'commercegurus-commercekit' ); ?></option>
								<option value="non-products" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'non-products' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All products apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="categories" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'categories' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific categories', 'commercegurus-commercekit' ); ?></option>
								<option value="non-categories" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'non-categories' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All categories apart from', 'commercegurus-commercekit' ); ?></option>
								<option value="tags" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'tags' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'Specific tags', 'commercegurus-commercekit' ); ?></option>
								<option value="non-tags" <?php echo isset( $countdown['product']['condition'][ $k ] ) && 'non-tags' === $countdown['product']['condition'][ $k ] ? 'selected="selected"' : ''; ?> ><?php esc_html_e( 'All tags apart from', 'commercegurus-commercekit' ); ?></option>
							</select>
						</td> 
					</tr>
					<tr class="product-ids" <?php echo 'all' === $ctype ? 'style="display:none;"' : ''; ?>>
						<th class="options">
						<?php
						echo 'all' === $ctype || 'products' === $ctype ? 'Specific products' : '';
						echo 'categories' === $ctype ? esc_html__( 'Specific categories:', 'commercegurus-commercekit' ) : '';
						echo 'tags' === $ctype ? esc_html__( 'Specific tags:', 'commercegurus-commercekit' ) : '';
						?>
						</th>
						<td> <label><select name="commercekit_ctd_pdt_pids[]" class="select2" data-type="<?php echo esc_attr( $ctype ); ?>" multiple="multiple" style="width:100%;">
						<?php
						$pids = isset( $countdown['product']['pids'][ $k ] ) ? explode( ',', $countdown['product']['pids'][ $k ] ) : array();
						if ( 'all' !== $ctype && count( $pids ) ) {
							foreach ( $pids as $pid ) {
								if ( empty( $pid ) ) {
									continue;
								}
								if ( 'products' === $ctype ) {
									echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( commercekit_limit_title( get_the_title( $pid ) ) ) . '</option>';
								}
								if ( 'categories' === $ctype ) {
									$nterm = get_term_by( 'id', $pid, 'product_cat' );
									if ( $nterm ) {
										echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( $nterm->name ) . '</option>';
									}
								}
								if ( 'tags' === $ctype ) {
									$nterm = get_term_by( 'id', $pid, 'product_tag' );
									if ( $nterm ) {
										echo '<option value="' . esc_attr( $pid ) . '" selected="selected">#' . esc_attr( $pid ) . ' - ' . esc_html( $nterm->name ) . '</option>';
									}
								}
							}
						}
						?>
						</select><input type="hidden" name="commercekit[countdown][product][pids][]" class="select3 text" value="<?php echo esc_html( implode( ',', $pids ) ); ?>" /></label></td> 
					</tr>
					<tr>
						<td colspan="2">
							<div class="countdown-shortcode-id-wrapper">
								<span class="countdown-shortcode left" <?php echo isset( $commercekit_options['widget_pos_countdown'] ) && 1 === (int) $commercekit_options['widget_pos_countdown'] ? '' : 'style="visibility: hidden;"'; ?>><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: <span class="countdown-shortcode-id">[commercekit_product_countdown id="<?php echo esc_attr( $k + 1 ); ?>"]</span></span><span class="countdown-delete right">
								<a href="javascript:;" class="delete-ckit--element delete-countdown" onclick="delete_product_countdown(this);">
									<?php esc_html_e( 'Delete Countdown', 'commercegurus-commercekit' ); ?>
								</a>
								</span>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>

				<?php
			}
		}
		?>
	</div>
	<script> 
	var global_radio_count = <?php echo (int) $radio_count; ?>;
	var global_delete_countdown = '<?php esc_html_e( 'Delete Countdown', 'commercegurus-commercekit' ); ?>';
	var global_delete_countdown_confirm = '<?php esc_html_e( 'Are you sure you want to delete this product countdown?', 'commercegurus-commercekit' ); ?>';
	var global_required_text = '<?php esc_html_e( 'This field is required', 'commercegurus-commercekit' ); ?>';
	</script>

	<div class="inside add-new-countdown"><button type="button" class="button button-secondary" onclick="add_new_product_countdown();"><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add new Product Countdown', 'commercegurus-commercekit' ); ?></button></div>

</div>
<div class="postbox content-box" id="checkout-countdown">
	<h2><span class="table-heading"><?php esc_html_e( 'Checkout Countdown', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">
		<table class="form-table" role="presentation">
			<tr> 
				<th width="20%"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th>
				<td> <label class="toggle-switch"><input name="commercekit[countdown][checkout][active]" type="checkbox" class="check" value="1" <?php echo isset( $countdown['checkout']['active'] ) && 1 === (int) $countdown['checkout']['active'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><?php esc_html_e( 'Enable countdown timer on checkout page', 'commercegurus-commercekit' ); ?></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Title', 'commercegurus-commercekit' ); ?> </th>
				<td> <label class="text-label"><input name="commercekit[countdown][checkout][title]" type="text" class="title ckt-title text" value="<?php echo isset( $countdown['checkout']['title'] ) ? esc_attr( stripslashes_deep( $countdown['checkout']['title'] ) ) : ''; ?>" /></label><br /><small><?php esc_html_e( 'e.g. Your order is reserved for:', 'commercegurus-commercekit' ); ?></small></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Expiry Message', 'commercegurus-commercekit' ); ?></th>
				<td> <label class="text-label"><input name="commercekit[countdown][checkout][expiry_message]" type="text" class="title ckt-title text" value="<?php echo isset( $countdown['checkout']['expiry_message'] ) ? esc_attr( stripslashes_deep( $countdown['checkout']['expiry_message'] ) ) : ''; ?>" /></label><br /><small><?php esc_html_e( 'e.g. Times up! But good news! We&rsquo;ve extended your checkout time :)', 'commercegurus-commercekit' ); ?></small></td> 
			</tr>
			<tr> 
				<th><?php esc_html_e( 'Ends', 'commercegurus-commercekit' ); ?></th>
				<td class="ends-wrap"> 
					<label><input name="commercekit[countdown][checkout][minutes]" type="number" min="0" max="59" class="ends text" value="<?php echo isset( $countdown['checkout']['minutes'] ) ? esc_attr( $countdown['checkout']['minutes'] ) : ''; ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Minutes', 'commercegurus-commercekit' ); ?></span></label>
					<label><input name="commercekit[countdown][checkout][seconds]" type="number" min="0" max="59" class="ends text" value="<?php echo isset( $countdown['checkout']['seconds'] ) ? esc_attr( $countdown['checkout']['seconds'] ) : ''; ?>" /><br /><span class="ends-label"><?php esc_html_e( 'Seconds', 'commercegurus-commercekit' ); ?></span></label>
				</td> 
			</tr>
		</table>
	</div>

		<input type="hidden" name="tab" value="countdown-timer" />
		<input type="hidden" name="action" value="commercekit_save_settings" />
</div>


<div class="postbox content-box" id="checkout-countdown">
	<h2><span class="table-heading"><?php esc_html_e( 'Use shortcodes', 'commercegurus-commercekit' ); ?></span></h2>

	<div class="inside">

		<p><?php esc_html_e( 'Some page builders (such as Elementor Pro) allow you to completely customize the standard WooCommerce product page template. By enabling the shortcode option, you can still add this module to your template, in a position of your choosing. These will only work on', 'commercegurus-commercekit' ); ?> <strong><?php esc_html_e( 'product and checkout templates' ); ?></strong> respectively.</p>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Product Countdown shortcode', 'commercegurus-commercekit' ); ?></th>
				<td> <label for="commercekit_widget_pos_countdown" class="toggle-switch"> <input name="commercekit[widget_pos_countdown]" type="checkbox" id="commercekit_widget_pos_countdown" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_countdown'] ) && 1 === (int) $commercekit_options['widget_pos_countdown'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>


					<div class="cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_countdown'] ) && 1 === (int) $commercekit_options['widget_pos_countdown'] ? '' : 'style="display: none;"'; ?>>

						<div class="mini-explainer cgkit-shortcode-help">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
							</svg>

							<p><?php esc_html_e( 'This will prevent the module from appearing unless the shortcode is present on the page. Only enable this if you are using a page builder such as Elementor Pro and a custom product page template.', 'commercegurus-commercekit' ); ?></p>

						</div>

						<div class="cg-notice-success">
							<p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_product_countdown]</p>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row" style="vertical-align: top; padding-top: 14px"><?php esc_html_e( 'Checkout Countdown shortcode', 'commercegurus-commercekit' ); ?></th>

				<td> <label for="commercekit_widget_pos_countdown2" class="toggle-switch"> <input name="commercekit[widget_pos_countdown2]" type="checkbox" id="commercekit_widget_pos_countdown2" class="cgkit-shortcode-option" value="1" <?php echo isset( $commercekit_options['widget_pos_countdown2'] ) && 1 === (int) $commercekit_options['widget_pos_countdown2'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></label>

					<div class="cg-notice-success cgkit-shortcode-help" <?php echo isset( $commercekit_options['widget_pos_countdown2'] ) && 1 === (int) $commercekit_options['widget_pos_countdown2'] ? '' : 'style="display: none;"'; ?>><p><?php esc_html_e( 'Shortcode', 'commercegurus-commercekit' ); ?>: [commercekit_checkout_countdown]</p></div>

				</td>
				</tr>
		</table>
	</div>
</div>


</div>
<div class="postbox" id="settings-note">
	<h4><?php esc_html_e( 'Countdown Timer', 'commercegurus-commercekit' ); ?></h4>
	<p><?php esc_html_e( 'This feature allows you to run time-limited promotions to create urgency and drive more clicks from your single product pages to create more sales. You can also add one to the checkout page to encourage faster completion.', 'commercegurus-commercekit' ); ?></p>
</div>

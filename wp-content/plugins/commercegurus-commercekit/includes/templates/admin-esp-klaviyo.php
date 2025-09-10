<?php
/**
 * The template for displaying ESP Klaviyo.
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<table class="form-table" role="presentation" id="cgkit-waitlist">
	<tr> <th scope="row"><?php esc_html_e( 'Enable', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_enable_klaviyo" class="toggle-switch"> <input name="cgkit_esp_klaviyo[enable_klaviyo]" type="checkbox" id="cgkit_esp_klaviyo_enable_klaviyo" value="1" <?php echo isset( $cgkit_esp_klaviyo['enable_klaviyo'] ) && 1 === (int) $cgkit_esp_klaviyo['enable_klaviyo'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('.wtl_esp_klaviyo_opt').show();}else{jQuery('.wtl_esp_klaviyo_opt').hide();} cgkit_update_form_id_required();"><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Enqueue the Klaviyo JS', 'commercegurus-commercekit' ); ?></label></td> </tr>
	<tr class="wtl_esp_klaviyo_opt" <?php echo isset( $cgkit_esp_klaviyo['enable_klaviyo'] ) && 1 === (int) $cgkit_esp_klaviyo['enable_klaviyo'] ? '' : 'style="display:none;"'; ?>> <td colspan="2" style="padding: 0px;">
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Override', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_main_call" class="toggle-switch"> <input name="cgkit_esp_klaviyo[main_call]" type="checkbox" id="cgkit_esp_klaviyo_main_call" value="1" <?php echo isset( $cgkit_esp_klaviyo['main_call'] ) && 1 === (int) $cgkit_esp_klaviyo['main_call'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#esp_klaviyo_main_call_txt').show();}else{jQuery('#esp_klaviyo_main_call_txt').hide();}"><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Override main call to action text?', 'commercegurus-commercekit' ); ?></label><br /><small><em><?php esc_html_e( 'Override the regular oos/restock call to action text: "Can\'t find your size?"', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
			<tr id="esp_klaviyo_main_call_txt" <?php echo isset( $cgkit_esp_klaviyo['main_call'] ) && 1 === (int) $cgkit_esp_klaviyo['main_call'] ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'New call to action text', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_main_call_txt"> <input name="cgkit_esp_klaviyo[main_call_txt]" type="text" class="pc100" id="cgkit_esp_klaviyo_main_call_txt" value="<?php echo isset( $cgkit_esp_klaviyo['main_call_txt'] ) && ! empty( $cgkit_esp_klaviyo['main_call_txt'] ) ? esc_attr( stripslashes_deep( $cgkit_esp_klaviyo['main_call_txt'] ) ) : ''; // phpcs:ignore ?>"  /></label><br /><small><em><?php esc_html_e( 'Enter a short text label', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
		</table>
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Override', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_oos_message" class="toggle-switch"> <input name="cgkit_esp_klaviyo[oos_message]" type="checkbox" id="cgkit_esp_klaviyo_oos_message" value="1" <?php echo isset( $cgkit_esp_klaviyo['oos_message'] ) && 1 === (int) $cgkit_esp_klaviyo['oos_message'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#esp_klaviyo_oos_message_txt').show();}else{jQuery('#esp_klaviyo_oos_message_txt').hide();}"><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Override regular out of stock message?', 'commercegurus-commercekit' ); ?></label><br /><small><em><?php esc_html_e( 'Override the regular oos message: "This product is currently out of stock and unavailable"', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
			<tr id="esp_klaviyo_oos_message_txt" <?php echo isset( $cgkit_esp_klaviyo['oos_message'] ) && 1 === (int) $cgkit_esp_klaviyo['oos_message'] ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'New out of stock message', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_oos_message_txt"> <input name="cgkit_esp_klaviyo[oos_message_txt]" type="text" class="pc100" id="cgkit_esp_klaviyo_oos_message_txt" value="<?php echo isset( $cgkit_esp_klaviyo['oos_message_txt'] ) && ! empty( $cgkit_esp_klaviyo['oos_message_txt'] ) ? esc_attr( stripslashes_deep( $cgkit_esp_klaviyo['oos_message_txt'] ) ) : ''; // phpcs:ignore ?>"  /></label><br /><small><em><?php esc_html_e( 'Enter a short out of stock message', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
		</table>
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Stock message', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_stock_message" class="toggle-switch"> <input name="cgkit_esp_klaviyo[stock_message]" type="checkbox" id="cgkit_esp_klaviyo_stock_message" value="1" <?php echo isset( $cgkit_esp_klaviyo['stock_message'] ) && 1 === (int) $cgkit_esp_klaviyo['stock_message'] ? 'checked="checked"' : ''; ?> onchange="if(jQuery(this).prop('checked')){jQuery('#esp_klaviyo_stock_message_txt').show();}else{jQuery('#esp_klaviyo_stock_message_txt').hide();}"><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Add stock message textarea?', 'commercegurus-commercekit' ); ?></label></td> </tr>
			<tr id="esp_klaviyo_stock_message_txt" <?php echo isset( $cgkit_esp_klaviyo['stock_message'] ) && 1 === (int) $cgkit_esp_klaviyo['stock_message'] ? '' : 'style="display:none;"'; ?>> <th scope="row"><?php esc_html_e( 'Stock message textarea', 'commercegurus-commercekit' ); ?></th> <td>
			<?php
			$stock_message_txt = isset( $cgkit_esp_klaviyo['stock_message_txt'] ) && ! empty( $cgkit_esp_klaviyo['stock_message_txt'] ) ? esc_attr( stripslashes_deep( $cgkit_esp_klaviyo['stock_message_txt'] ) ) : '';
			wp_editor(
				html_entity_decode( $stock_message_txt ),
				'cgkit_esp_klaviyo_stock_message_txt',
				array(
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'cgkit_esp_klaviyo[stock_message_txt]',
					'textarea_rows' => 10,
					'teeny'         => true,
				)
			);
			?>
			<br /><small><em><?php esc_html_e( 'Additional text area for more info and links. e.g. Short text explaining when you expect this item to be back in stock (or links to other products if it\'s a discontinued product)', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
		</table>
		<table class="form-table" role="presentation">
			<tr id="esp_klaviyo_show_form_id"> <th scope="row"><?php esc_html_e( 'Klaviyo Form ID', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_show_form_id"> <input name="cgkit_esp_klaviyo[show_form_id]" type="text" class="pc100" id="cgkit_esp_klaviyo_show_form_id" value="<?php echo isset( $cgkit_esp_klaviyo['show_form_id'] ) && ! empty( $cgkit_esp_klaviyo['show_form_id'] ) ? esc_attr( stripslashes_deep( $cgkit_esp_klaviyo['show_form_id'] ) ) : ''; // phpcs:ignore ?>" <?php echo true === $form_id_required ? 'data-required="1"' : ''; ?> /></label><br /><small><em><?php esc_html_e( 'Enter the Klaviyo Form ID - e.g. if Klaviyo provide "klaviyo-form-T9sLTc" you would enter just: T9sLTc', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
		</table>
		<table class="form-table" role="presentation">
			<tr> <th scope="row"><?php esc_html_e( 'Force display', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_esp_klaviyo_force_display" class="toggle-switch"> <input name="cgkit_esp_klaviyo[force_display]" type="checkbox" id="cgkit_esp_klaviyo_force_display" value="1" <?php echo isset( $cgkit_esp_klaviyo['force_display'] ) && 1 === (int) $cgkit_esp_klaviyo['force_display'] ? 'checked="checked"' : ''; ?>><span class="toggle-slider"></span></label><label>&nbsp;&nbsp;<?php esc_html_e( 'Force display of Klaviyo and stock message textarea?', 'commercegurus-commercekit' ); ?></label><br /><small><em><?php esc_html_e( 'Force the display of the stock message textarea and Klaviyo form even if the item is not yet out of stock. (Can be useful to display the wait list form and messaging when stock is low)', 'commercegurus-commercekit' ); ?></em></small><?php wp_nonce_field( 'commercekit_settings', 'commercekit_nonce' ); ?></td> </tr>
		</table>
	</td> </tr>
</table>
<style>
#cgkit-waitlist .toggle-switch { position: relative; display: inline-block; width: 54px; height: 26px; margin-right: 10px; }
#cgkit-waitlist .toggle-switch input { opacity: 0; width: 0; height: 0; }
#cgkit-waitlist .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; -webkit-transition: .4s; transition: .4s; border-radius: 34px; }
#cgkit-waitlist .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; -webkit-transition: .4s; transition: .4s; border-radius: 50%; }
#cgkit-waitlist input:checked + .toggle-slider { background-color: #2196F3; }
#cgkit-waitlist input:focus + .toggle-slider { box-shadow: 0 0 1px #2196F3; }
#cgkit-waitlist input:checked + .toggle-slider:before { -webkit-transform: translateX(26px); -ms-transform: translateX(26px); transform: translateX(26px); }
</style>
<script>
jQuery( document ).ready( function() {
	cgkit_update_form_id_required();
} );
function cgkit_update_form_id_required() {
	if ( jQuery( '#cgkit_esp_klaviyo_enable_klaviyo' ).prop( 'checked' ) && jQuery( '#cgkit_esp_klaviyo_show_form_id' ).data( 'required' ) == 1 ) {
		jQuery( '#cgkit_esp_klaviyo_show_form_id' ).attr( 'required', 1 );
	} else {
		jQuery( '#cgkit_esp_klaviyo_show_form_id' ).removeAttr( 'required' );
	}
}
</script>

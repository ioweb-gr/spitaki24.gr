<?php
/**
 * The template for displaying admin PDP featured review.
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<table class="form-table" role="presentation" id="cgkit-pdp-review">
	<tr> <th scope="row"><?php esc_html_e( 'Review Image:', 'commercegurus-commercekit' ); ?></th> <td> <label for="cgkit_pdp_review_image"> <input name="cgkit_pdp_review[image]" type="hidden" id="cgkit_pdp_review_image" value="<?php echo isset( $cgkit_pdp_review['image'] ) && ! empty( $cgkit_pdp_review['image'] ) ? esc_attr( $cgkit_pdp_review['image'] ) : ''; ?>" />
		<div class="review-image" data-choose="<?php esc_html_e( 'Add review image', 'commercegurus-commercekit' ); ?>" data-update="<?php esc_html_e( 'Add review image', 'commercegurus-commercekit' ); ?>">
		<?php
		$image = null;
		if ( isset( $cgkit_pdp_review['image'] ) && ! empty( $cgkit_pdp_review['image'] ) ) {
			$image = wp_get_attachment_image_src( $cgkit_pdp_review['image'], 'thumbnail' );
		}
		?>
		<?php if ( $image ) { ?>
			<img width="<?php echo esc_attr( $image[1] ); ?>" height="<?php echo esc_attr( $image[2] ); ?>" src="<?php echo esc_url( $image[0] ); ?>" <?php echo ( (int) $image[2] < (int) $image[1] ) ? 'style="height:auto;"' : ''; ?> /><ul class="actions"><li><a href="javascript:;" class="cgkit-fi-delete">x</a></li></ul>
		<?php } else { ?>
			<span class="dashicons dashicons-arrow-up-alt"></span>
			<div class="title"><?php esc_html_e( 'Add image', 'commercegurus-commercekit' ); ?></div>
		<?php } ?>
		</div>
	</label><small><em><?php esc_html_e( 'Tip: A square image works best.', 'commercegurus-commercekit' ); ?></em></small></td> </tr>
	<tr> <th scope="row"><?php esc_html_e( 'Review Text:', 'commercegurus-commercekit' ); ?></th> <td>
	<?php
	$cgkit_pdp_review_text = isset( $cgkit_pdp_review['text'] ) && ! empty( $cgkit_pdp_review['text'] ) ? esc_attr( stripslashes_deep( $cgkit_pdp_review['text'] ) ) : '';
	wp_editor(
		html_entity_decode( $cgkit_pdp_review_text ),
		'cgkit_pdp_review_text',
		array(
			'wpautop'       => true,
			'media_buttons' => true,
			'textarea_name' => 'cgkit_pdp_review[text]',
			'textarea_rows' => 10,
			'teeny'         => true,
		)
	);
	?>
	<small><em><?php esc_html_e( 'Tip: To highlight part of a review, wrap it within <mark></mark> HTML.', 'commercegurus-commercekit' ); ?></em></small><?php wp_nonce_field( 'commercekit_settings', 'commercekit_nonce2' ); ?></td> </tr>

</table>
<style>
#cgkit-pdp-review { width: 100%; }
#cgkit-pdp-review small { display: block; margin-top: 0.5rem }
#cgkit-pdp-review th { width: 160px; padding-right: 10px }
#cgkit-pdp-review td { calc(100% - 170px); }
#cgkit-pdp-review .review-image { border: 1px solid #d5d5d5; border-radius: 4px; text-align: center; box-sizing: border-box; cursor: pointer; position: relative; width: 70px; height: 70px; display: block; overflow: hidden; }
#cgkit-pdp-review .review-image .title { clear: both; display: block; font-size: 11px; color: #666; margin-top: 3px; }
#cgkit-pdp-review .review-image img { position: absolute; height: 100%; width: auto; top: 50%; left: 50%; transform: translate( -50%, -50%); }
#cgkit-pdp-review .review-image ul.actions { display: none; padding: 2px; position: absolute; right: 0px; top: 0px; margin: 2px; }
#cgkit-pdp-review .review-image:hover ul.actions { display: block; }
#cgkit-pdp-review .review-image ul.actions li a.cgkit-fi-delete { display: flex; position: relative; justify-content: center; align-items: center; position: relative; height: 14px; width: 14px; color: #fff; cursor: pointer; border-radius: 50%; background-color: #aaa; font-size: 0; text-decoration: none; font-weight: bold; text-align: center; transition: background-color 0.2s; }
#cgkit-pdp-review .review-image ul.actions li a.cgkit-fi-delete:hover { background-color: #777; }
#cgkit-pdp-review .review-image ul.actions li a.cgkit-fi-delete:before { display: block; width: 10px; height: 10px; background-color: #fff; content: ""; -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 18L18 6M6 6l12 12' /%3E%3C/svg%3E"); -webkit-mask-position: center; -webkit-mask-repeat: no-repeat; -webkit-mask-size: contain; }
#cgkit-pdp-review .review-image span { margin-top: 8px; padding: 10%; border: 2px solid #9e9e9d; color: #9e9e9d; border-radius: 50%; }
#cgkit-pdp-review .review-image:hover span { -webkit-transition: opacity 0.2s ease-in; -moz-transition: opacity 0.2s ease-in; -o-transition: opacity 0.2s ease-in; opacity: 1; }
</style>
<script type="text/javascript">
jQuery(document).ready(function($){
	$('body').on('click', '.review-image', function(e){
		e.preventDefault();
		cgkitAddReviewImage($(this));
	});
	$('body').on('click', '.cgkit-fi-delete', function(e){
		e.preventDefault();
		e.stopPropagation();
		$('input#cgkit_pdp_review_image').val('');
		$(this).closest('.review-image').html('<span class="dashicons dashicons-arrow-up-alt"></span><div class="title">Add image</div>');
	});
});
function cgkitAddReviewImage($this){
	var product_fimage_frame;
	if( product_fimage_frame ){
		product_fimage_frame.open();
		return;
	}

	product_fimage_frame = wp.media.frames.product_gallery = wp.media({
		title: $this.data('choose'),
		button: {
			text: $this.data('update')
		},
		states: [
			new wp.media.controller.Library({
				title: $this.data('choose'),
				filterable: 'images',
				multiple: false
			})
		]
	});	

	product_fimage_frame.on('select', function() {
		var selection = product_fimage_frame.state().get('selection');
		selection.map(function(attachment) {
			attachment = attachment.toJSON();
			if( attachment.id ){
				var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$this.html('<img src="' + attachment_image + '" /><ul class="actions"><li><a href="javascript:;" class="cgkit-fi-delete">x</a></li></ul>');
				jQuery('input#cgkit_pdp_review_image').val(attachment.id);
			}
		});
	});
	product_fimage_frame.open();
}
</script>

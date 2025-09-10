<?php
/**
 * The template for displaying admin product attribute gallery.
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php
$attr_names    = array();
$gallery_slugs = array();

$glob_auto_play = 0;
$options        = get_option( 'commercekit', array() );
$glob_auto_play = ( ( isset( $options['pdp_video_autoplay'] ) && 1 === (int) $options['pdp_video_autoplay'] ) || ! isset( $options['pdp_video_autoplay'] ) ) ? 1 : 0;

$attr_names['global_gallery'] = esc_html__( 'Global Gallery', 'commercegurus-commercekit' );
?>
<?php if ( ! isset( $without_wrap ) || ! $without_wrap ) { ?>
<div id="cgkit_attr_gallery" class="panel wc-metaboxes-wrapper hidden">
<?php } ?>
	<div class="wc-metabox">
		<div class="wc-metabox-content">
			<div class="cgkit-top-notice"><p>You have a lot of flexibility when creating attribute galleries but it can be confusing at first. Select an attribute and click "Add" to start creating a gallery for that attribute. You can only add one value from an attribute group at a time.</p>

				<p><a href="https://www.commercegurus.com/docs/commercekit/product-attributes-gallery/" target="_blank">Learn more about how to setup the CommerceKit Attributes Gallery</a> &rarr;</p></div>
			<div class="cgkit-top">
				<div class="cgkit-top-row1">
					<label for="cgkit_attributes"><?php esc_html_e( 'Add a gallery to:', 'commercegurus-commercekit' ); ?></label>
				</div>
				<div class="cgkit-top-row2">
					<div id="cgkit_attributes_wrap">
						<select id="cgkit_attributes" name="cgkit_attributes" multiple="multiple" class="" data-placeholder="Select an attribute">
							<option value="global_gallery" data-id="global_gallery"><?php esc_html_e( 'Global gallery', 'commercegurus-commercekit' ); ?></option>
							<?php
							if ( count( $attributes ) ) {
								$counter = 0;
								foreach ( $attributes as $attribute ) {
									if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
										echo '<optgroup label="' . esc_attr( $attribute['name'] ) . '">';
										foreach ( $attribute['terms'] as $item ) {
											if ( ! is_numeric( $item->term_id ) ) {
												$item->term_id = sanitize_title( $item->term_id );
											}
											$option_id = str_replace( '%', '', $item->term_id );
											echo '<option value="' . esc_attr( $item->term_id ) . '" data-id="' . esc_attr( $option_id ) . '">' . esc_attr( $item->name ) . '</option>';
											$attr_names[ $item->term_id ] = $item->name;
											$gallery_slugs[ $counter++ ]  = $item->term_id;
										}
										echo '</optgroup>';
									}
								}
							}
							?>
						</select>
					</div>
				</div>
				<div class="cgkit-top-row3">
					<button type="button" id="add_cgkit_gallery" class="button fr plus"><?php esc_html_e( 'Add gallery', 'commercegurus-commercekit' ); ?></button>
				</div>
			</div>

			<?php if ( is_array( $commercekit_image_gallery ) && count( $commercekit_image_gallery ) ) { ?>

			<h2 class="heading"><?php esc_html_e( 'Attribute Galleries', 'commercegurus-commercekit' ); ?></h2>

			<?php } ?>

			<div id="cgkit-image-gallery">
				<?php if ( is_array( $commercekit_image_gallery ) && count( $commercekit_image_gallery ) ) { ?>
					<?php foreach ( $commercekit_image_gallery as $slug => $gallery ) { ?>
						<?php
						if ( 'default_gallery' === $slug ) {
							continue;
						}
						$old_slug  = $slug;
						$slug      = commercegurus_get_product_gallery_slug( $slug, $gallery_slugs );
						$slugs     = explode( '_cgkit_', $slug );
						$slug_keys = array_keys( $attr_names );
						if ( ! count( array_intersect( $slugs, $slug_keys ) ) === count( $slugs ) ) {
							continue;
						}
						$image_ids = explode( ',', $gallery );
						$attr_name = isset( $attr_names[ $slug ] ) ? $attr_names[ $slug ] : '';
						if ( 'default_gallery' !== $slug && 'global_gallery' !== $slug ) {
							$tmp_names = array();
							if ( count( $slugs ) ) {
								foreach ( $slugs as $slg ) {
									if ( isset( $attr_names[ $slg ] ) ) {
										$tmp_names[] = $attr_names[ $slg ];
									}
								}
							}
							$attr_name = implode( ' + ', $tmp_names ) . ' ' . esc_html__( 'Gallery', 'commercegurus-commercekit' );
						}
						$option_id = str_replace( '%', '', $slug );
						?>
					<div id="cgkit_<?php echo esc_attr( $option_id ); ?>" class="postbox cgkit-attributes-images" data-slug="<?php echo esc_attr( $slug ); ?>">
					<h2><span class="cgkit-title"><?php echo esc_attr( $attr_name ); ?></span><a href="javascript:;" class="cgkit-gallery-delete">Delete</a></h2>
					<div class="inside">
						<div class="product-images-container">
							<ul class="product-images cgkit-product-images">
							<?php if ( count( $image_ids ) ) { ?>
								<?php foreach ( $image_ids as $image_id ) { ?>
									<?php
									$image = wp_get_attachment_image_src( $image_id, 'woocommerce_gallery_thumbnail' );
									if ( ! $image ) {
										continue;
									}
									?>
								<li class="product-image" data-image_id="<?php echo esc_attr( $image_id ); ?>"><img width="<?php echo esc_attr( $image[1] ); ?>" height="<?php echo esc_attr( $image[2] ); ?>" src="<?php echo esc_url( $image[0] ); ?>" <?php echo ( (int) $image[2] < (int) $image[1] ) ? 'style="height:auto;"' : ''; ?> /><ul class="actions"><li><a href="javascript:;" class="cgkit-image-delete"><?php esc_html_e( 'Remove', 'commercegurus-commercekit' ); ?></a></li></ul><span class="dashicons dashicons-video-alt3 cgkit-videomanager <?php echo isset( $commercekit_video_gallery[ $old_slug ][ $image_id ] ) ? 'cgkit-editvideo' : 'cgkit-addvideo'; ?>"></span></li>
								<?php } ?>
							<?php } ?>
								<li class="add-product-images ui-state-disabled"><a href="javascript:;" data-choose="<?php esc_html_e( 'Add Images to Product Gallery', 'commercegurus-commercekit' ); ?>" data-update="<?php esc_html_e( 'Add to gallery', 'commercegurus-commercekit' ); ?>" data-delete="<?php esc_html_e( 'Delete image', 'commercegurus-commercekit' ); ?>" data-text="<?php esc_html_e( 'Delete', 'commercegurus-commercekit' ); ?>"><span class="dashicons dashicons-arrow-up-alt"></span><div class="title"><?php esc_html_e( 'Add images', 'commercegurus-commercekit' ); ?></div></a></li>
							</ul>
							<input class="cgkit-product-image-gallery" name="commercekit_image_gallery[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $gallery ); ?>" type="hidden">
							<div class="clear"></div>
						</div>
					</div>
				</div>
					<?php } ?>
				<?php } ?>
			</div>
			<div id="cgkit-video-gallery" style="display:none;">
				<div id="cgkit-video-gallery-inputs">
				<?php if ( is_array( $commercekit_video_gallery ) && count( $commercekit_video_gallery ) ) { ?>
					<?php foreach ( $commercekit_video_gallery as $slug => $gallery ) { ?>
						<?php $slug = commercegurus_get_product_gallery_slug( $slug, $gallery_slugs ); ?>
						<?php foreach ( $gallery as $image_id => $video_url ) { ?>
				<input class="cgkit-product-video-gallery" name="commercekit_video_gallery[<?php echo esc_attr( $slug ); ?>][<?php echo esc_attr( $image_id ); ?>]" value="<?php echo esc_url( $video_url ); ?>" type="hidden" />
						<?php } ?>
					<?php } ?>
				<?php } ?>
				</div>
				<input type="hidden" name="commercekit_nonce" id="commercekit_nonce" value="<?php echo esc_html( wp_create_nonce( 'commercekit_nonce' ) ); ?>" />
				<div id="cgkit-dialog-video">
					<div class="inside cgkit-formgroup">
						<div class="form-group">
							<div class="cgkit-video-url-wrapper">
								<label for="cgkit-video-input"><?php esc_html_e( 'Video', 'commercegurus-commercekit' ); ?></label>
								<input id="cgkit-video-input" placeholder="<?php esc_html_e( 'YouTube, Vimeo, Wistia, or video url (.mp4 or .webm)', 'commercegurus-commercekit' ); ?>" class="cgkit-video form-control" name="cgkit_video_input" value="" data-error="<?php esc_html_e( 'YouTube, Vimeo, Wistia, or video url (.mp4 or .webm) allowed.', 'commercegurus-commercekit' ); ?>" />
							</div>
							<label class="cgkit-video-autoplay"><input type="checkbox" id="cgkit-video-autoplay" name="cgkit_video_autoplay" value="1" <?php echo 1 === $glob_auto_play ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Autoplay video?', 'commercegurus-commercekit' ); ?></label>
							<input type="button" id="browse-media-library2" name="browse-media-library2" value="<?php esc_html_e( 'Browse from Media Library', 'commercegurus-commercekit' ); ?>" data-choose="<?php esc_html_e( 'Add video to Attributes Gallery', 'commercegurus-commercekit' ); ?>" data-update="<?php esc_html_e( 'Add to gallery', 'commercegurus-commercekit' ); ?>" data-delete="<?php esc_html_e( 'Delete video', 'commercegurus-commercekit' ); ?>" data-text="<?php esc_html_e( 'Delete', 'commercegurus-commercekit' ); ?>"/><br /><br />
							<small class="form-text text-muted"><?php esc_html_e( 'Insert or replace the current video URL.', 'commercegurus-commercekit' ); ?></small>
							<input type="hidden" name="cgkit_video_slug" id="cgkit-video-slug" value="" />
							<input type="hidden" name="cgkit_video_image_id" id="cgkit-video-image-id" value="" />
						</div>
					</div>
				</div>
			</div>

		</div>
		<div id="cgkitag-save-changes">
			<button type="button" class="button button-primary" onclick="cgkitAjaxUpdateAttributeGallery();" disabled="disabled"><?php esc_html_e( 'Save changes', 'commercegurus-commercekit' ); ?></button>
			<?php wp_nonce_field( 'commercekit_settings', 'commercekit_nonce_ag' ); ?>
		</div>
	</div>
<?php if ( ! isset( $without_wrap ) || ! $without_wrap ) { ?>
</div>
<script type="text/javascript">
var cgkit_gallery_template = '<div id="cgkit_{slug_id}" class="postbox cgkit-attributes-images" data-slug="{slug}"><h2><span class="cgkit-title">{attr_name}</span><a href="javascript:;" class="cgkit-gallery-delete">Delete</a></h2><div class="inside"><div class="product-images-container"><ul class="product-images cgkit-product-images"><li class="add-product-images ui-state-disabled"><a href="javascript:;" data-choose="<?php esc_html_e( 'Add Images to Product Gallery', 'commercegurus-commercekit' ); ?>" data-update="<?php esc_html_e( 'Add to gallery', 'commercegurus-commercekit' ); ?>" data-delete="<?php esc_html_e( 'Delete image', 'commercegurus-commercekit' ); ?>" data-text="<?php esc_html_e( 'Delete', 'commercegurus-commercekit' ); ?>"><span class="dashicons dashicons-arrow-up-alt"></span><div class="title"><?php esc_html_e( 'Add images', 'commercegurus-commercekit' ); ?></div></a></li></ul><input class="cgkit-product-image-gallery" name="commercekit_image_gallery[{slug}]" value="" type="hidden"><div class="clear"></div></div></div></div>';
var cgkit_image_template = '<li class="product-image" data-image_id="{image_id}"><img src="{image_url}" /><ul class="actions"><li><a href="javascript:;" class="cgkit-image-delete"><?php esc_html_e( 'Remove', 'commercegurus-commercekit' ); ?></a></li></ul><span class="dashicons dashicons-video-alt3 cgkit-videomanager cgkit-addvideo"></span></li>';
var cgkit_video_template = '<input class="cgkit-product-video-gallery" name="commercekit_video_gallery[{slug}][{image_id}]" value="{video_url}" type="hidden" />';
var cgkit_gallery_text = '<?php esc_html_e( 'Gallery', 'commercegurus-commercekit' ); ?>';
var cgkit_delete_gallery_text = '<?php esc_html_e( 'Are you sure, you want to delete this Gallery?', 'commercegurus-commercekit' ); ?>';
var cgkit_video_title_text = '<?php esc_html_e( 'CommerceKit Product Gallery Video', 'commercegurus-commercekit' ); ?>';
var cgkit_video_close_text = '<?php esc_html_e( 'Close', 'commercegurus-commercekit' ); ?>';
var cgkit_video_remove_text = '<?php esc_html_e( 'Remove', 'commercegurus-commercekit' ); ?>';
var cgkit_video_save_text = '<?php esc_html_e( 'Save Video', 'commercegurus-commercekit' ); ?>';
var cgkit_video_auto_play = '<?php echo esc_attr( $glob_auto_play ); ?>';
</script>
<?php } ?>

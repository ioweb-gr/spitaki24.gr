<?php

/**
 * Class WPML_Media_Attachment_Image_Update
 * Allows adding a custom image to a translated attachment
 */
class WPML_Media_Attachment_Image_Update implements IWPML_Action {

	const TRANSIENT_FILE_UPLOAD_PREFIX = 'wpml_media_file_update_';

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * WPML_Media_Attachment_Image_Update constructor.
	 *
	 * @param wpdb $wpdb
	 */
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function add_hooks() {
		add_action( 'wp_ajax_wpml_media_upload_file', array( $this, 'handle_upload' ) );
	}

	public function handle_upload() {
		if ( ! $this->is_valid_action() ) {
			wp_send_json_error( 'invalid action' );
			return;
		}
		$this->process_uploaded_file();
	}

	/**
	 * Upload file and generate thumbnail or document icon.
	 *
	 * A thumbnail is shown on the media translation before confirming the upload.
	 * WordPress only generates a thumbnail after the upload is complete, so a thumbnail must be created beforehand.
	 *
	 * @return void
	 */
	private function process_uploaded_file() {
		$original_attachment_id = (int) $_POST['original-attachment-id'];
		$attachment_id          = (int) $_POST['attachment-id'];
		$file_array             = $_FILES['file'];
		$target_language        = $_POST['language'];

		$upload_overrides = apply_filters( 'wpml_media_wp_upload_overrides', array( 'test_form' => false ) );
		$file             = wp_handle_upload( $file_array, $upload_overrides );

		if ( isset( $file['error'] ) ) {
			wp_send_json_error( $file['error'] );
			return;
		}

		$thumb_path = '';

		if ( wp_image_editor_supports( array( 'mime_type' => $file['type'] ) ) ) {
			$editor = wp_get_image_editor( $file['file'] );

			if ( is_wp_error( $editor ) || ! $this->is_thumbnail_creation_enabled() ) {
				$thumb_url = wp_mime_type_icon( $file['type'] );

				if ( ! $thumb_url ) {
					wp_send_json_error( __( 'Failed to load the image editor', 'wpml-media' ) );
				}
			} else {
				if ( $this->is_pdf_or_video( $file['type'] ) ) {
					$thumb = $this->generate_thumb_for_pdf_or_video_file( $file, $editor, $attachment_id );
				} else {
					$thumb = $this->resize_thumbnail( $editor );
				}

				if ( is_string( $thumb ) ) {
					$thumb_url = $thumb;
				} else {
					$uploads_dir = wp_get_upload_dir();
					$thumb_url   = $uploads_dir['baseurl'] . $uploads_dir['subdir'] . '/' . $thumb['file'];
					$thumb_path  = $thumb['path'];
				}
			}
		} else {
			$thumb_url = wp_mime_type_icon( $file['type'] );
		}

		set_transient(
			self::TRANSIENT_FILE_UPLOAD_PREFIX . $original_attachment_id . '_' . $target_language,
			array(
				'upload' => $file,
				'thumb'  => $thumb_path,
			),
			HOUR_IN_SECONDS
		);

		wp_send_json_success(
			array(
				'attachment_id' => $attachment_id,
				'thumb'         => $thumb_url,
				'name'          => basename( $file['file'] ),
			)
		);
	}

	/**
	 * Check if file type is pdf or video.
	 *
	 * @param string $file_type
	 * @return bool
	 */
	private function is_pdf_or_video( $file_type ) {
		return 'application/pdf' === $file_type || stripos( $file_type, 'video' ) !== false;
	}

	/**
	 * Create a thumbnail for the pdf or video.
	 *
	 * @param array           $file
	 * @param WP_Image_Editor $editor
	 * @param int             $attachment_id
	 * @return array|WP_Error
	 */
	private function generate_thumb_for_pdf_or_video_file( $file, WP_Image_Editor $editor, $attachment_id ) {
		$dirname      = dirname( $file['file'] ) . '/';
		$ext          = pathinfo( $file['file'], PATHINFO_EXTENSION );
		$preview_file = $dirname . wp_unique_filename( $dirname, wp_basename( $file['file'], '.' . $ext ) . "-{$ext}.jpg" );

		$editor->save( $preview_file, 'image/jpeg' );

		$thumb               = $this->resize_thumbnail( $editor );
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id );
		$attachment_size     = [
			'file'      => basename( $preview_file ),
			'width'     => $thumb['width'],
			'height'    => $thumb['height'],
			'mime-type' => 'image/jpeg',
		];

		$attachment_metadata['sizes']['thumbnail'] = $attachment_size;
		$attachment_metadata['sizes']['full']      = $attachment_size;

		wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

		return $thumb;
	}

	/**
	 * Check if a thumbnail can be created.
	 *
	 * @return bool
	 */
	private function is_thumbnail_creation_enabled() {
		$thumbnail_size_w = get_option( 'thumbnail_size_w' );
		$thumbnail_size_h = get_option( 'thumbnail_size_h' );

		if ( 0 >= $thumbnail_size_w || 0 >= $thumbnail_size_h ) {
			return false;
		}

		return true;
	}

	/**
	 * Resize the thumbnail if it is larger than the settings size
	 *
	 * @param WP_Image_Editor $editor
	 * @return array|WP_Error
	 */
	private function resize_thumbnail( $editor ) {

		$size             = $editor->get_size();
		$thumbnail_size_w = get_option( 'thumbnail_size_w' );
		$thumbnail_size_h = get_option( 'thumbnail_size_h' );

		if ( $thumbnail_size_w < $size['width'] || $thumbnail_size_h < $size['height'] ) {
			$resizing = $editor->resize( $thumbnail_size_w, $thumbnail_size_h, true );
			if ( is_wp_error( $resizing ) ) {
				wp_send_json_error( $resizing->get_error_message() );
			}
		}

		return $editor->save();
	}

	private function is_valid_action() {
		$is_attachment_id = isset( $_POST['attachment-id'] );
		$is_post_action   = isset( $_POST['action'] ) && 'wpml_media_upload_file' === $_POST['action'];

		return $is_attachment_id && $is_post_action && wp_verify_nonce( $_POST['wpnonce'], 'media-translation' );
	}

}

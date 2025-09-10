<?php
/**
 *
 * CommerceKit Exporter
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Commercekit_Exporter class.
 */
class Commercekit_Exporter {

	/**
	 * Start time of current process.
	 *
	 * @var start_time
	 */
	protected $start_time = 0;

	/**
	 * Server memory limit.
	 *
	 * @var memory_limit
	 */
	protected $memory_limit = 0;

	/**
	 * Server execution time.
	 *
	 * @var execution_time
	 */
	protected $execution_time = 0;

	/**
	 * Commercekit_Exporter Constructor
	 */
	public function __construct() {
		add_action( 'commercekit_run_export_csv', array( $this, 'run_export_csv' ) );
		add_action( 'wp_ajax_commercekit_generate_export_csv', array( $this, 'generate_export_csv' ) );
		add_action( 'wp_ajax_commercekit_download_export_csv', array( $this, 'download_export_csv' ) );
		add_action( 'commercekit_run_import_csv', array( $this, 'run_import_csv' ) );
		add_action( 'wp_ajax_commercekit_generate_import_csv', array( $this, 'generate_import_csv' ) );
	}

	/**
	 * Commercekit export CSV
	 *
	 * @param string $args arguments.
	 */
	public function run_export_csv( $args ) {
		global $wpdb;
		$product_id = 0;
		if ( is_numeric( $args ) ) {
			$product_id = (int) $args;
		} elseif ( is_array( $args ) ) {
			if ( isset( $args[0] ) && is_numeric( $args[0] ) ) {
				$product_id = (int) $args[0];
			} elseif ( isset( $args['product_id'] ) && is_numeric( $args['product_id'] ) ) {
				$product_id = (int) $args['product_id'];
			}
		}

		$this->start_time     = time();
		$this->memory_limit   = $this->get_memory_limit();
		$this->execution_time = $this->get_execution_time();
		$query_template       = $this->get_products_query();

		$query   = $wpdb->prepare( $query_template, $product_id ); // phpcs:ignore
		$results = $wpdb->get_col( $query ); // phpcs:ignore
		if ( count( $results ) ) {
			$options    = get_option( 'commercekit', array() );
			$next_job   = false;
			$upload_dir = wp_upload_dir();
			if ( isset( $options['exporting_csv_file'] ) && ! empty( $options['exporting_csv_file'] ) ) {
				$temp_csv_file = $options['exporting_csv_file'];
			} else {
				$temp_csv_file = 'cgkit-exp-' . time() . '.csv';
			}
			$csv_file = $upload_dir['basedir'] . '/' . $temp_csv_file;

			$fp = fopen( $csv_file, 0 === $product_id ? 'w' : 'a' ); // phpcs:ignore
			if ( false === $fp ) {
				$this->logger( 'Failed to open file for writing: ' . $csv_file, 'exporter' );
				return;
			}
			if ( 0 === $product_id ) {
				$heads = array( 'id', 'sku', 'images', 'videos', 'swatches' );
				fputcsv( $fp, $heads );
				fputcsv( $fp, array( 0, count( $results ), get_option( 'siteurl' ), '', '' ) );
			}
			foreach ( $results as $export_id ) {
				$options['exporting_csv_id']   = $export_id;
				$options['exporting_csv_file'] = $temp_csv_file;
				update_option( 'commercekit', $options, true );

				$_sku = get_post_meta( $export_id, '_sku', true );
				if ( ! $_sku ) {
					$this->logger( 'ProductID: ' . $export_id . ', SKU: empty value found but will continue for the next product.', 'exporter' );
					continue;
				}

				$data  = $this->convert_images_into_paths( $export_id );
				$row   = array();
				$row[] = $export_id; /* ID */
				$row[] = $_sku; /* SKU */
				$row[] = isset( $data['images'] ) ? wp_json_encode( $data['images'] ) : ''; /* image_gallery */
				$row[] = isset( $data['videos'] ) ? wp_json_encode( $data['videos'] ) : ''; /* video_gallery */
				$row[] = isset( $data['swatches'] ) ? wp_json_encode( $data['swatches'] ) : ''; /* attribute_swatches */

				fputcsv( $fp, $row );
				$this->logger( 'ProductID: ' . $export_id . ', SKU: ' . $_sku . ' successfully exported.', 'exporter' );
				if ( $this->memory_exceeded() || $this->time_exceeded() ) {
					$next_job   = true;
					$product_id = $export_id;
					break;
				}
			}

			$completed = true;
			if ( $next_job && $product_id ) {
				$query_template2 = $this->get_products_query( 'count' );

				$query2  = $wpdb->prepare( $query_template2, $product_id ); // phpcs:ignore
				$pending = (int) $wpdb->get_var( $query2 ); // phpcs:ignore
				if ( $pending ) {
					as_schedule_single_action( time() + 5, 'commercekit_run_export_csv', array( 'product_id' => $product_id ), 'commercekit' );
					$completed = false;
				}
			}
			if ( $completed ) {
				$options['exporting_csv'] = 0;
				update_option( 'commercekit', $options, true );
			}

			if ( false === fclose( $fp ) ) { // phpcs:ignore
				$this->logger( 'Failed to close file: ' . $csv_file, 'exporter' );
			}
		}
	}

	/**
	 * Prepare export CSV
	 */
	public function prepare_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$options       = get_option( 'commercekit', array() );
		$exporting_csv = isset( $options['exporting_csv'] ) && 1 === (int) $options['exporting_csv'] ? true : false;
		if ( $exporting_csv ) {
			return true;
		}
		as_schedule_single_action( time() + 5, 'commercekit_run_export_csv', array( 'product_id' => 0 ), 'commercekit' );
	}

	/**
	 * Generate export CSV
	 */
	public function generate_export_csv() {
		global $wpdb;
		$options        = get_option( 'commercekit', array() );
		$upload_dir     = wp_upload_dir();
		$ajax           = array();
		$ajax['status'] = 0;
		$ajax['total']  = 0;

		$ajax['complete']      = 0;
		$ajax['percent']       = 0;
		$ajax['exporting_csv'] = 0;
		$ajax['download_link'] = 0;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( $ajax );
		}

		$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
		if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
			wp_send_json( $ajax );
		}

		$query_template = $this->get_products_query( 'count' );
		if ( ! empty( $query_template ) ) {
			$ajax['status']    = 1;
			$ajax['mem_limit'] = $this->get_memory_limit();
			$ajax['exec_time'] = $this->get_execution_time();
			$ajax['mem_usage'] = memory_get_usage( true );
			$total_query       = $wpdb->prepare( $query_template, 0 ); // phpcs:ignore
			$ajax['total']     = (int) $wpdb->get_var( $total_query ); // phpcs:ignore
			$exporting_csv_id  = isset( $options['exporting_csv_id'] ) ? (int) $options['exporting_csv_id'] : 0;
			$pending_query     = $wpdb->prepare( $query_template, $exporting_csv_id ); // phpcs:ignore
			$complete_total    = $ajax['total'] - (int) $wpdb->get_var( $pending_query ); // phpcs:ignore
			$ajax['complete']  = $complete_total >= 0 ? $complete_total : 0;
			$ajax['percent']   = $ajax['total'] > 0 ? (int) ( ( $ajax['complete'] * 100 ) / $ajax['total'] ) : 0;
			$generate_csv      = isset( $_POST['generate_csv'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['generate_csv'] ) ) ) : 0;
			if ( 1 === $generate_csv ) {
				$options['exporting_csv_file'] = '';
				$options['exporting_csv_id']   = 0;
				$options['exporting_csv']      = 0;
				update_option( 'commercekit', $options, true );
				$this->prepare_export_csv();
				$options['exporting_csv_id'] = 0;
				$options['exporting_csv']    = 1;
				update_option( 'commercekit', $options, true );
				$ajax['exporting_csv'] = 1;
				$ajax['complete']      = 0;
				$ajax['percent']       = 0;
				$ajax['total']         = 0;
			} else {
				$ajax['exporting_csv'] = isset( $options['exporting_csv'] ) && 1 === (int) $options['exporting_csv'] ? 1 : 0;
			}
			$temp_csv_file = '';
			if ( isset( $options['exporting_csv_file'] ) && ! empty( $options['exporting_csv_file'] ) ) {
				$temp_csv_file = $options['exporting_csv_file'];
			}
			if ( 0 === $ajax['exporting_csv'] && ! empty( $temp_csv_file ) && file_exists( $upload_dir['basedir'] . '/' . $temp_csv_file ) ) {
				$ajax['download_link'] = 1;
			}
		}

		wp_send_json( $ajax );
	}

	/**
	 * Download export CSV
	 */
	public function download_export_csv() {
		$valid_request = true;
		if ( ! current_user_can( 'manage_options' ) ) {
			$valid_request = false;
		}

		$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
		if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
			$valid_request = false;
		}

		$options    = get_option( 'commercekit', array() );
		$upload_dir = wp_upload_dir();
		$csv_file   = '';
		if ( isset( $options['exporting_csv_file'] ) && ! empty( $options['exporting_csv_file'] ) ) {
			$csv_file = $options['exporting_csv_file'];
		}
		$csv_path = $upload_dir['basedir'] . '/' . $csv_file;
		$expo_csv = isset( $options['exporting_csv'] ) && 1 === (int) $options['exporting_csv'] ? 1 : 0;
		if ( $valid_request && 0 === $expo_csv && ! empty( $csv_file ) && file_exists( $csv_path ) ) {
			header( 'Content-Type: text/csv' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename="commercekit-export.csv"' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . filesize( $csv_path ) );

			readfile( $csv_path ); // phpcs:ignore

			unlink( $csv_path );

			$options['exporting_csv_file'] = '';
			update_option( 'commercekit', $options, true );

			exit();
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=commercekit&tab=exporter&msg=1' ) );
			exit();
		}
	}

	/**
	 * Run import CSV
	 *
	 * @param string $args arguments.
	 */
	public function run_import_csv( $args ) {
		global $wpdb;
		$product_id = 0;
		$fseek_pos  = 0;
		if ( is_numeric( $args ) ) {
			$product_id = (int) $args;
		} elseif ( is_array( $args ) ) {
			if ( isset( $args[0] ) && is_numeric( $args[0] ) ) {
				$product_id = (int) $args[0];
			} elseif ( isset( $args['product_id'] ) && is_numeric( $args['product_id'] ) ) {
				$product_id = (int) $args['product_id'];
			}
			if ( isset( $args[1] ) && is_numeric( $args[1] ) ) {
				$fseek_pos = (int) $args[1];
			} elseif ( isset( $args['fseek_pos'] ) && is_numeric( $args['fseek_pos'] ) ) {
				$fseek_pos = (int) $args['fseek_pos'];
			}
			if ( $fseek_pos < 0 ) {
				$fseek_pos = 0;
			}
		}
		$options       = get_option( 'commercekit', array() );
		$upload_dir    = wp_upload_dir();
		$temp_csv_file = '';
		if ( isset( $options['importing_csv_file'] ) && ! empty( $options['importing_csv_file'] ) ) {
			$temp_csv_file = $options['importing_csv_file'];
		}
		$csv_file = $upload_dir['basedir'] . '/' . $temp_csv_file;
		if ( empty( $temp_csv_file ) || ! file_exists( $csv_file ) ) {
			$options['importing_csv_fail'] = 1;
			$options['importing_csv']      = 0;
			update_option( 'commercekit', $options, true );
			$this->logger( $csv_file . ' file not found.', 'importer' );
			return;
		}

		$this->start_time     = time();
		$this->memory_limit   = $this->get_memory_limit();
		$this->execution_time = $this->get_execution_time();

		$fp = fopen( $csv_file, 'r' ); // phpcs:ignore
		if ( false === $fp ) {
			$this->logger( 'Failed to open file for reading: ' . $csv_file, 'importer' );
			return;
		}
		$frow  = fgetcsv( $fp );
		$heads = array( 'id', 'sku', 'images', 'videos', 'swatches' );
		if ( $frow !== $heads ) {
			$options['importing_csv_fail'] = 1;
			$options['importing_csv']      = 0;
			update_option( 'commercekit', $options, true );
			$this->logger( 'CSV file headers do not match.', 'importer' );
			unlink( $csv_file );
			return;
		}
		$srow = fgetcsv( $fp );
		if ( ! isset( $srow[0] ) || 0 !== (int) $srow[0] || ! isset( $srow[1] ) ) {
			$options['importing_csv_fail'] = 1;
			$options['importing_csv']      = 0;
			update_option( 'commercekit', $options, true );
			$this->logger( 'CSV file totals not found.', 'importer' );
			unlink( $csv_file );
			return;
		}
		$site_url   = isset( $srow[2] ) && ! empty( $srow[2] ) ? $srow[2] : '';
		$rows_total = (int) $srow[1];
		if ( 0 === (int) $product_id ) {
			$options['importing_csv_total']    = $rows_total;
			$options['importing_csv_complete'] = 0;
			$options['importing_csv_id']       = 0;
			$options['importing_csv']          = 1;
			$options['importing_csv_fail']     = 0;
			update_option( 'commercekit', $options, true );
		}
		$next_job   = false;
		$rows_count = 0;
		fseek( $fp, $fseek_pos ); // phpcs:ignore
		$rows_complete = isset( $options['importing_csv_complete'] ) ? (int) $options['importing_csv_complete'] : 0;
		while ( false !== ( $row = fgetcsv( $fp ) ) ) { // phpcs:ignore
			$rows_count++;

			$options['importing_csv_id']       = (int) $row[0];
			$options['importing_csv_complete'] = $rows_complete + $rows_count;
			if ( (int) $options['importing_csv_complete'] >= (int) $options['importing_csv_total'] ) {
				$options['importing_csv_complete'] = $options['importing_csv_total'];
			}
			update_option( 'commercekit', $options, true );

			if ( isset( $row[0] ) && (int) $row[0] <= $product_id ) {
				continue;
			}
			if ( isset( $row[0] ) && ! (int) $row[0] ) {
				continue;
			}
			if ( isset( $row[1] ) && ! empty( $row[1] ) ) {
				$importer_id = wc_get_product_id_by_sku( $row[1] );
				if ( $importer_id ) {
					$data = $this->convert_paths_into_images( $row, $importer_id, $site_url );
					if ( isset( $data['images'] ) && is_array( $data['images'] ) ) { /* for images */
						update_post_meta( $importer_id, 'commercekit_image_gallery', $data['images'] );
					}
					if ( isset( $data['videos'] ) && is_array( $data['videos'] ) ) { /* for videos */
						update_post_meta( $importer_id, 'commercekit_video_gallery', $data['videos'] );
					}
					if ( isset( $data['swatches'] ) && is_array( $data['swatches'] ) ) { /* for swatches */
						update_post_meta( $importer_id, 'commercekit_attribute_swatches', $data['swatches'] );
					}
					$this->logger( 'SKU: ' . $row[1] . ' imported successfully.', 'importer' );
				} else {
					$this->logger( 'SKU: ' . $row[1] . ' not found.', 'importer' );
				}
			}
			if ( $this->memory_exceeded() || $this->time_exceeded() ) {
				$next_job   = true;
				$product_id = (int) $row[0];
				break;
			}
		}
		$fseek_pos = ftell( $fp ); // phpcs:ignore

		$completed = true;
		if ( $next_job && $product_id ) {
			if ( $rows_count < $rows_total ) {
				$params = array(
					'product_id' => $product_id,
					'fseek_pos'  => $fseek_pos,
				);
				as_schedule_single_action( time() + 5, 'commercekit_run_import_csv', $params, 'commercekit' );
				$completed = false;
			}
		}

		if ( $completed ) {
			$options['importing_csv_complete'] = $options['importing_csv_total'];
			$options['importing_csv']          = 0;
			$options['importing_csv_fail']     = 0;
			$options['importing_csv_file']     = '';
			update_option( 'commercekit', $options, true );
			delete_option( 'commercekit_import_images' );
			unlink( $csv_file );
		}

		if ( false === fclose( $fp ) ) { // phpcs:ignore
			$this->logger( 'Failed to close file: ' . $csv_file, 'exporter' );
		}
	}

	/**
	 * Prepare import CSV
	 */
	public function prepare_import_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$options       = get_option( 'commercekit', array() );
		$importing_csv = isset( $options['importing_csv'] ) && 1 === (int) $options['importing_csv'] ? true : false;
		$importing_csv = isset( $options['importing_csv_fail'] ) && 1 === (int) $options['importing_csv_fail'] ? false : $importing_csv;
		if ( $importing_csv ) {
			return true;
		}
		$params = array(
			'product_id' => 0,
			'fseek_pos'  => 0,
		);
		as_schedule_single_action( time() + 5, 'commercekit_run_import_csv', $params, 'commercekit' );
	}

	/**
	 * Generate import CSV
	 */
	public function generate_import_csv() {
		global $wpdb;
		$options        = get_option( 'commercekit', array() );
		$upload_dir     = wp_upload_dir();
		$ajax           = array();
		$ajax['status'] = 0;
		$ajax['total']  = 0;

		$ajax['complete']           = 0;
		$ajax['percent']            = 0;
		$ajax['importing_csv']      = 0;
		$ajax['importing_csv_fail'] = 0;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( $ajax );
		}

		$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
		if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
			wp_send_json( $ajax );
		}

		$ajax['status']    = 1;
		$ajax['mem_limit'] = $this->get_memory_limit();
		$ajax['exec_time'] = $this->get_execution_time();
		$ajax['mem_usage'] = memory_get_usage( true );
		$ajax['total']     = isset( $options['importing_csv_total'] ) ? (int) $options['importing_csv_total'] : 0;
		$ajax['complete']  = isset( $options['importing_csv_complete'] ) ? (int) $options['importing_csv_complete'] : 0;
		$ajax['percent']   = $ajax['total'] > 0 ? (int) ( ( $ajax['complete'] * 100 ) / $ajax['total'] ) : 0;
		$import_csv        = isset( $_POST['import_csv'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['import_csv'] ) ) ) : 0;
		if ( 1 === $import_csv ) {
			$this->prepare_import_csv();
			$options['importing_csv_total']    = 0;
			$options['importing_csv_complete'] = 0;
			$options['importing_csv_id']       = 0;
			$options['importing_csv']          = 1;
			$options['importing_csv_fail']     = 0;
			update_option( 'commercekit', $options, true );
			$ajax['importing_csv']      = 1;
			$ajax['importing_csv_fail'] = 0;
			$ajax['total']              = 0;
			$ajax['complete']           = 0;
			$ajax['percent']            = 0;
		} else {
			$ajax['importing_csv']      = isset( $options['importing_csv'] ) && 1 === (int) $options['importing_csv'] ? 1 : 0;
			$ajax['importing_csv']      = isset( $options['importing_csv_fail'] ) && 1 === (int) $options['importing_csv_fail'] ? 0 : $ajax['importing_csv'];
			$ajax['importing_csv_fail'] = isset( $options['importing_csv_fail'] ) && 1 === (int) $options['importing_csv_fail'] ? 1 : 0;
		}

		wp_send_json( $ajax );
	}

	/**
	 * Convert images into paths.
	 *
	 * @param string $export_id product ID.
	 */
	public function convert_images_into_paths( $export_id ) {
		$product = wc_get_product( $export_id );
		if ( ! $product ) {
			return;
		}
		$attr_slugs   = array();
		$term_slugs   = array();
		$cache_images = array();
		$attributes   = commercegurus_attribute_swatches_load_attributes( $product );
		if ( count( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					if ( isset( $attribute['id'] ) && is_numeric( $attribute['id'] ) ) {
						$attr_slugs[ $attribute['id'] ] = $attribute['slug'] . '::' . $attribute['slug'];
					}
					foreach ( $attribute['terms'] as $item ) {
						if ( is_numeric( $item->term_id ) ) {
							$term_slugs[ $item->term_id ] = $item->slug . '::' . $attribute['slug'];
						}
					}
				}
			}
		}

		$images   = get_post_meta( $export_id, 'commercekit_image_gallery', true );
		$videos   = get_post_meta( $export_id, 'commercekit_video_gallery', true );
		$swatches = get_post_meta( $export_id, 'commercekit_attribute_swatches', true );

		if ( is_array( $images ) && count( $images ) ) {
			$nimages = array();
			foreach ( $images as $img_key => $img_val ) {
				$keys  = explode( '_cgkit_', $img_key );
				$nkeys = array();
				if ( count( $keys ) ) {
					foreach ( $keys as $key ) {
						if ( is_numeric( $key ) && isset( $term_slugs[ $key ] ) ) {
							$nkeys[] = $term_slugs[ $key ];
						} else {
							$nkeys[] = $key;
						}
					}
				}
				$nimg_key = implode( '_cgkit_', $nkeys );
				$values   = explode( ',', $img_val );
				$nvalues  = array();
				if ( count( $values ) ) {
					foreach ( $values as $value ) {
						if ( is_numeric( $value ) ) {
							if ( isset( $cache_images[ $value ] ) ) {
								$nvalues[] = $cache_images[ $value ];
							} else {
								$img_url   = wp_get_attachment_url( $value );
								$nvalues[] = false !== $img_url ? $img_url : '';

								$cache_images[ $value ] = false !== $img_url ? $img_url : '';
							}
						} else {
							$nvalues[] = $value;
						}
					}
				}
				$nimg_value = implode( ',', $nvalues );

				$nimages[ $nimg_key ] = $nimg_value;
			}
			$images = $nimages;
		}

		if ( is_array( $videos ) && count( $videos ) ) {
			$nvideos = array();
			foreach ( $videos as $vid_key => $vid_val ) {
				$keys  = explode( '_cgkit_', $vid_key );
				$nkeys = array();
				if ( count( $keys ) ) {
					foreach ( $keys as $key ) {
						if ( is_numeric( $key ) && isset( $term_slugs[ $key ] ) ) {
							$nkeys[] = $term_slugs[ $key ];
						} else {
							$nkeys[] = $key;
						}
					}
				}
				$nvid_key = implode( '_cgkit_', $nkeys );
				$nvid_val = array();
				if ( is_array( $vid_val ) && count( $vid_val ) ) {
					foreach ( $vid_val as $key => $value ) {
						if ( is_numeric( $key ) ) {
							if ( isset( $cache_images[ $key ] ) ) {
								$nvid_val[ $cache_images[ $key ] ] = $value;
							} else {
								$img_url = wp_get_attachment_url( $key );
								$img_url = false !== $img_url ? $img_url : '';
								if ( ! empty( $img_url ) ) {
									$cache_images[ $key ] = $img_url;
									$nvid_val[ $img_url ] = $value;
								}
							}
						}
					}
				}
				$nvideos[ $nvid_key ] = $nvid_val;
			}
			$videos = $nvideos;
		}

		if ( is_array( $swatches ) && count( $swatches ) ) {
			$nswatches = array();
			foreach ( $swatches as $swt_key => $swt_val ) {
				$nswt_key = $swt_key;
				if ( is_numeric( $swt_key ) && isset( $attr_slugs[ $swt_key ] ) ) {
					$nswt_key = $attr_slugs[ $swt_key ];
				}
				$nswt_val = array();
				if ( is_array( $swt_val ) && count( $swt_val ) ) {
					foreach ( $swt_val as $key => $value ) {
						if ( isset( $swt_val['cgkit_type'] ) && 'image' === $swt_val['cgkit_type'] ) {
							if ( isset( $value['img'] ) && is_numeric( $value['img'] ) ) {
								if ( isset( $cache_images[ $value['img'] ] ) ) {
									$value['img'] = $cache_images[ $value['img'] ];
								} else {
									$img_url      = wp_get_attachment_url( $value['img'] );
									$value['img'] = false !== $img_url ? $img_url : '';

									$cache_images[ $value['img'] ] = false !== $img_url ? $img_url : '';
								}
							}
						}
						if ( is_numeric( $key ) && isset( $term_slugs[ $key ] ) ) {
							$nswt_val[ $term_slugs[ $key ] ] = $value;
						} else {
							$nswt_val[ $key ] = $value;
						}
					}
				}
				$nswatches[ $nswt_key ] = $nswt_val;
			}
			if ( isset( $swatches['enable_loop'] ) ) {
				$nswatches['enable_loop'] = $swatches['enable_loop'];
			}
			if ( isset( $swatches['enable_product'] ) ) {
				$nswatches['enable_product'] = $swatches['enable_product'];
			}
			$swatches = $nswatches;
		}

		$data = array(
			'images'   => $images,
			'videos'   => $videos,
			'swatches' => $swatches,
		);

		return $data;
	}

	/**
	 * Convert images into paths.
	 *
	 * @param string $row CSV row.
	 * @param string $importer_id product ID.
	 * @param string $site_url old site URL.
	 */
	public function convert_paths_into_images( $row, $importer_id, $site_url ) {
		$product = wc_get_product( $importer_id );
		if ( ! $product ) {
			return;
		}
		$import_images = get_option( 'commercekit_import_images', array() );
		$attr_slugs    = array();
		$term_slugs    = array();
		$cache_images  = $import_images;
		$attributes    = commercegurus_attribute_swatches_load_attributes( $product );
		if ( count( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					if ( isset( $attribute['id'] ) && is_numeric( $attribute['id'] ) ) {
						$attr_slugs[ $attribute['slug'] . '::' . $attribute['slug'] ] = $attribute['id'];
					}
					foreach ( $attribute['terms'] as $item ) {
						if ( is_numeric( $item->term_id ) ) {
							$term_slugs[ $item->slug . '::' . $attribute['slug'] ] = $item->term_id;
						}
					}
				}
			}
		}

		$images   = isset( $row[2] ) ? json_decode( $row[2], true ) : array();
		$videos   = isset( $row[3] ) ? json_decode( $row[3], true ) : array();
		$swatches = isset( $row[4] ) ? json_decode( $row[4], true ) : array();

		if ( is_array( $images ) && count( $images ) ) {
			$nimages = array();
			foreach ( $images as $img_key => $img_val ) {
				$keys  = explode( '_cgkit_', $img_key );
				$nkeys = array();
				if ( count( $keys ) ) {
					foreach ( $keys as $key ) {
						if ( false !== strpos( $key, '::' ) && isset( $term_slugs[ $key ] ) ) {
							$nkeys[] = $term_slugs[ $key ];
						} else {
							$nkeys[] = $key;
						}
					}
				}
				$nimg_key = implode( '_cgkit_', $nkeys );
				$values   = explode( ',', $img_val );
				$nvalues  = array();
				if ( count( $values ) ) {
					foreach ( $values as $value ) {
						if ( isset( $cache_images[ $value ] ) ) {
							$nvalues[] = $cache_images[ $value ];
						} else {
							$attach_id = $this->insert_attachment_from_url( $value );
							if ( ! $attach_id ) {
								$this->logger( 'Product ID: ' . $importer_id . ', Gallery image URL: ' . $value . ' not found.', 'importer' );
							}
							$cache_images[ $value ] = $attach_id;

							$nvalues[] = $attach_id;
						}
					}
				}
				$nimg_value = implode( ',', $nvalues );

				$nimages[ $nimg_key ] = $nimg_value;
			}
			$images = $nimages;
		}

		if ( is_array( $videos ) && count( $videos ) ) {
			$nvideos = array();
			foreach ( $videos as $vid_key => $vid_val ) {
				$keys  = explode( '_cgkit_', $vid_key );
				$nkeys = array();
				if ( count( $keys ) ) {
					foreach ( $keys as $key ) {
						if ( false !== strpos( $key, '::' ) && isset( $term_slugs[ $key ] ) ) {
							$nkeys[] = $term_slugs[ $key ];
						} else {
							$nkeys[] = $key;
						}
					}
				}
				$nvid_key = implode( '_cgkit_', $nkeys );
				$nvid_val = array();
				if ( is_array( $vid_val ) && count( $vid_val ) ) {
					foreach ( $vid_val as $key => $value ) {
						if ( isset( $cache_images[ $key ] ) ) {
							$key   = $cache_images[ $key ];
							$lenth = strlen( $site_url );
							if ( ! empty( $site_url ) && strtolower( $site_url ) === strtolower( substr( $value, 0, $lenth ) ) ) {
								$parts = explode( '::', $value );
								if ( isset( $parts[0] ) && ! empty( $parts[0] ) && isset( $parts[1] ) ) {
									$attach_id = $this->insert_attachment_from_url( $parts[0], 'video' );
									if ( ! $attach_id ) {
										$this->logger( 'Product ID: ' . $importer_id . ', Gallery video URL: ' . $parts[0] . ' not found.', 'importer' );
									} else {
										$attach_id = wp_get_attachment_url( $attach_id );
									}
									$value = $attach_id . '::' . $parts[1];
								}
							}
							$nvid_val[ $key ] = $value;
						} else {
							$this->logger( 'Product ID: ' . $importer_id . ', Gallery image URL: ' . $key . ' not found.', 'importer' );
						}
					}
				}
				$nvideos[ $nvid_key ] = $nvid_val;
			}
			$videos = $nvideos;
		}

		if ( is_array( $swatches ) && count( $swatches ) ) {
			$nswatches = array();
			foreach ( $swatches as $swt_key => $swt_val ) {
				$nswt_key = $swt_key;
				if ( false !== strpos( $swt_key, '::' ) && isset( $attr_slugs[ $swt_key ] ) ) {
					$nswt_key = $attr_slugs[ $swt_key ];
				}
				$nswt_val = array();
				if ( is_array( $swt_val ) && count( $swt_val ) ) {
					foreach ( $swt_val as $key => $value ) {
						if ( isset( $swt_val['cgkit_type'] ) && 'image' === $swt_val['cgkit_type'] ) {
							if ( isset( $value['img'] ) && ! empty( $value['img'] ) ) {
								if ( isset( $cache_images[ $value['img'] ] ) ) {
									$value['img'] = $cache_images[ $value['img'] ];
								} else {
									$attach_id = $this->insert_attachment_from_url( $value['img'] );
									if ( ! $attach_id ) {
										$this->logger( 'Product ID: ' . $importer_id . ', Swatches image URL: ' . $value . ' not found.', 'importer' );
									} else {
										$cache_images[ $value['img'] ]  = $attach_id;
										$import_images[ $value['img'] ] = $attach_id;
									}
									$value['img'] = $attach_id;
								}
							}
						}
						if ( false !== strpos( $key, '::' ) && isset( $term_slugs[ $key ] ) ) {
							$nswt_val[ $term_slugs[ $key ] ] = $value;
						} else {
							$nswt_val[ $key ] = $value;
						}
					}
				}
				$nswatches[ $nswt_key ] = $nswt_val;
			}
			if ( isset( $swatches['enable_loop'] ) ) {
				$nswatches['enable_loop'] = $swatches['enable_loop'];
			}
			if ( isset( $swatches['enable_product'] ) ) {
				$nswatches['enable_product'] = $swatches['enable_product'];
			}
			$swatches = $nswatches;
		}

		$data = array(
			'images'   => $images,
			'videos'   => $videos,
			'swatches' => $swatches,
		);

		update_option( 'commercekit_import_images', $import_images, true );

		return $data;
	}

	/**
	 * Insert attachment from URL.
	 *
	 * @param string $url attachment URL.
	 * @param string $type URL type either image or video.
	 */
	public function insert_attachment_from_url( $url, $type = 'image' ) {
		if ( ! class_exists( 'WP_Http' ) ) {
			require_once ABSPATH . WPINC . '/class-http.php';
		}

		$http     = new WP_Http();
		$response = $http->request( $url );
		if ( 200 !== $response['response']['code'] ) {
			return 0;
		}

		$upload = wp_upload_bits( basename( $url ), null, $response['body'] );
		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}

		$file_path        = $upload['file'];
		$file_name        = basename( $file_path );
		$file_type        = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir    = wp_upload_dir();

		$post_info = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
			'post_mime_type' => $file_type['type'],
			'post_title'     => $attachment_title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $post_info, $file_path );
		if ( $attach_id && 'image' === $type ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}

		return $attach_id;
	}

	/**
	 * Log export import process.
	 *
	 * @param string $message log message.
	 * @param string $type message type.
	 */
	public function logger( $message, $type = 'exporter' ) {
		$options = get_option( 'commercekit', array() );
		$enabled = isset( $options['export_import_logger'] ) && 1 === (int) $options['export_import_logger'] ? true : false;
		if ( $enabled ) {
			$logger = wc_get_logger();
			$logger->info( $message, array( 'source' => 'commercekit-' . $type ) );
		}
	}

	/**
	 * Get products query.
	 *
	 * @param string $type query type.
	 */
	public function get_products_query( $type = 'select' ) {
		global $wpdb;
		$term_taxonomy_id = 0;
		$variable_term    = get_term_by( 'slug', 'variable', 'product_type' );
		if ( $variable_term ) {
			$term_taxonomy_id = $variable_term->term_taxonomy_id;
		}
		if ( 'count' === $type ) {
			$query = 'SELECT COUNT( DISTINCT p.ID ) FROM';
		} else {
			$query = 'SELECT DISTINCT p.ID FROM';
		}
		$query .= " {$wpdb->prefix}posts AS p LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON p.ID = tr.object_id WHERE p.post_type = 'product' AND ( p.post_status <> 'trash' AND p.post_status <> 'auto-draft' ) AND tr.term_taxonomy_id IN (" . $term_taxonomy_id . ") AND p.ID > %d ORDER BY p.ID ASC"; // phpcs:ignore

		return $query;
	}

	/**
	 * Check whether server memory exceeded or not.
	 */
	public function memory_exceeded() {
		$current_memory = memory_get_usage( true );
		$return         = false;
		if ( $current_memory >= $this->memory_limit ) {
			$return = true;
		}
		return $return;
	}

	/**
	 * Check whether ececution time exceeded or not
	 */
	public function time_exceeded() {
		$finish = $this->start_time + $this->execution_time;
		$return = false;
		if ( time() >= $finish ) {
			$return = true;
		}
		return $return;
	}

	/**
	 * Get server memory limit.
	 */
	public function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			$memory_limit = '128M';
		}
		if ( ! $memory_limit || -1 === (int) $memory_limit ) {
			$memory_limit = '32G';
		}

		return $this->convert_hr_to_bytes( $memory_limit );
	}

	/**
	 * Convert hr to bytes.
	 *
	 * @param string $value memory limit.
	 */
	public function convert_hr_to_bytes( $value ) {
		if ( function_exists( 'wp_convert_hr_to_bytes' ) ) {
			return wp_convert_hr_to_bytes( $value );
		}
		$value = strtolower( trim( $value ) );
		$bytes = (int) $value;
		if ( false !== strpos( $value, 'g' ) ) {
			$bytes *= GB_IN_BYTES;
		} elseif ( false !== strpos( $value, 'm' ) ) {
			$bytes *= MB_IN_BYTES;
		} elseif ( false !== strpos( $value, 'k' ) ) {
			$bytes *= KB_IN_BYTES;
		}
		$bytes = $bytes * 0.8;

		return min( $bytes, PHP_INT_MAX );
	}

	/**
	 * Get server execution time.
	 */
	public function get_execution_time() {
		if ( function_exists( 'ini_get' ) ) {
			$execution_time = (int) ini_get( 'max_execution_time' );
			if ( 0 === $execution_time ) {
				$execution_time = 300;
			}
			if ( $execution_time < 0 ) {
				$execution_time = 20;
			}
		} else {
			$execution_time = 20;
		}
		$execution_time = (int) ( $execution_time * 0.8 );

		return $execution_time;
	}
}

global $commercekit_exporter;
$commercekit_exporter = new Commercekit_Exporter();

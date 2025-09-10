<?php
/**
 *
 * Admin Ajax Search Index
 *
 * @package CommerceKit
 */

/**
 * CommerceKit_AJS_Index class.
 */
class CommerceKit_AJS_Index {

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
	 * CommerceKit_AJS_Index Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_process_product_meta', array( $this, 'update_ajs_index_data' ), 20, 2 );
		add_action( 'woocommerce_updated_product_stock', array( $this, 'update_ajs_index' ), 20, 1 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'update_ajs_index' ), 20, 1 );
		add_action( 'woocommerce_ajax_save_product_variations', array( $this, 'update_ajs_index' ), 20, 1 );
		add_action( 'save_post', array( $this, 'quick_edit_update_ajs_index' ), 20, 2 );
		add_action( 'deleted_post', array( $this, 'update_ajs_index_delete_variation' ), 20, 2 );
		add_action( 'woocommerce_product_set_stock', array( $this, 'update_ajs_index_stock_updates' ), 20, 1 );
		add_action( 'woocommerce_variation_set_stock', array( $this, 'update_ajs_index_stock_updates' ), 20, 1 );

		add_action( 'updated_post_meta', array( $this, 'update_ajs_index_meta_updates' ), 20, 4 );
		add_action( 'delete_post', array( $this, 'update_ajs_index_delete_product' ), 20, 1 );
		add_action( 'shutdown', array( $this, 'update_ajs_index_on_shutdown' ), 20 );

		add_action( 'init', array( $this, 'ajs_prepare_action_scheduler' ) );
		add_action( 'commercegurus_ajs_run_wc_product_index', array( $this, 'run_wc_product_index' ) );
	}

	/**
	 * Update product index data
	 *
	 * @param string $post_id post ID.
	 * @param string $post post.
	 */
	public function update_ajs_index_data( $post_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}
		$this->build_ajs_index( $product, 'via update product' );
	}

	/**
	 * Update product index on stock, variations updates
	 *
	 * @param string $product_id product ID.
	 */
	public function update_ajs_index( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}
		$this->build_ajs_index( $product, 'via save variations' );
	}

	/**
	 * Update product index on quick edit updates
	 *
	 * @param string $product_id product ID.
	 * @param string $post       post object.
	 */
	public function quick_edit_update_ajs_index( $product_id, $post ) {
		if ( isset( $post->post_type ) && 'product' === $post->post_type ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				return;
			}
			$this->build_ajs_index( $product, 'via quick update product' );
		}
	}

	/**
	 * Update product index on delete variation
	 *
	 * @param string $postid variation ID.
	 * @param string $post variation post.
	 */
	public function update_ajs_index_delete_variation( $postid, $post ) {
		if ( $post && 'product_variation' === $post->post_type ) {
			$product_id = $post->post_parent;
			$product    = wc_get_product( $product_id );
			if ( ! $product ) {
				return;
			}
			$this->build_ajs_index( $product, 'via delete variation' );
		}
	}

	/**
	 * Update product index on stock updates
	 *
	 * @param string $product_obj product object.
	 */
	public function update_ajs_index_stock_updates( $product_obj ) {
		if ( $product_obj->is_type( 'variation' ) ) {
			$product_id = $product_obj->get_parent_id();
			$product    = wc_get_product( $product_id );
		} else {
			$product = $product_obj;
		}
		if ( ! $product ) {
			return;
		}
		$this->build_ajs_index( $product, 'via stock update' );
	}

	/**
	 * Update product index on meta updates
	 *
	 * @param string $meta_id meta ID.
	 * @param string $post_id post ID.
	 * @param string $meta_key meta key.
	 * @param string $meta_value meta value.
	 */
	public function update_ajs_index_meta_updates( $meta_id, $post_id, $meta_key, $meta_value ) {
		global $cgkit_ajs_shutdowns;
		if ( isset( $post_id ) && ! empty( $post_id ) ) {
			$product = wc_get_product( $post_id );
			if ( ! $product ) {
				return;
			}
			if ( ! isset( $cgkit_ajs_shutdowns ) ) {
				$cgkit_ajs_shutdowns = array();
			}
			$cgkit_ajs_shutdowns[ $post_id ] = $post_id;
		}
	}

	/**
	 * Build product index on end of script.
	 */
	public function update_ajs_index_on_shutdown() {
		global $cgkit_ajs_shutdowns, $cgkit_ajs_cached_keys;
		if ( ! isset( $cgkit_ajs_shutdowns ) ) {
			return;
		}
		if ( is_array( $cgkit_ajs_shutdowns ) && count( $cgkit_ajs_shutdowns ) ) {
			foreach ( $cgkit_ajs_shutdowns as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}
				if ( isset( $cgkit_ajs_cached_keys[ $product_id ] ) ) {
					unset( $cgkit_ajs_cached_keys[ $product_id ] );
				}
				$this->build_ajs_index( $product, 'via shutdown script' );
			}
		}
	}

	/**
	 * Delete product index when a product is deleted.
	 *
	 * @param string $product_id product ID.
	 */
	public function update_ajs_index_delete_product( $product_id ) {

		if ( 'product' !== get_post_type( $product_id ) ) {
			return;
		}

		$this->delete_index( 'product_id', $product_id );
	}

	/**
	 * Build product index
	 *
	 * @param string $product product object.
	 * @param string $suffix logger suffix text.
	 */
	public function build_ajs_index( $product, $suffix = '' ) {
		global $wpdb, $cgkit_ajs_cached_keys;
		$product_id = $product ? $product->get_id() : 0;
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			return;
		}
		if ( isset( $cgkit_ajs_cached_keys[ $product_id ] ) && $cgkit_ajs_cached_keys[ $product_id ] ) {
			return;
		}
		$cgkit_ajs_cached_keys[ $product_id ] = true;

		if ( ! empty( $suffix ) ) {
			$suffix = ' ' . $suffix;
		}

		commercegurus_ajs_log( 'building ajax search index for product id ' . $product_id . $suffix );
		$this->create_index( $product );
		commercegurus_ajs_log( 'ajax search index complete for product id ' . $product_id . $suffix );
	}

	/**
	 * Delete index
	 *
	 * @param string $key row name.
	 * @param string $value row value.
	 * @param string $format row format.
	 */
	public function delete_index( $key, $value, $format = '%d' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'commercekit_ajs_product_index';
		$sql   = 'DELETE FROM ' . $table . ' WHERE ' . $key . ' = ' . $format;
		$sql   = $wpdb->prepare( $sql, $value ); // phpcs:ignore
		$wpdb->query( $sql ); // phpcs:ignore
	}

	/**
	 * Create index
	 *
	 * @param string $product product object.
	 */
	public function create_index( $product ) {
		global $wpdb;
		$product_id = $product->get_id();
		$table      = $wpdb->prefix . 'commercekit_ajs_product_index';
		$row_id     = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $table . ' WHERE product_id = %d', $product_id ) ); // phpcs:ignore

		$skus  = array();
		$gtins = array();
		$gtin  = method_exists( $product, 'get_global_unique_id' ) ? $product->get_global_unique_id() : '';
		if ( 'variable' === $product->get_type() ) {
			$variation_ids = $product->get_visible_children();
			if ( count( $variation_ids ) ) {
				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation && $variation->get_sku() ) {
						$skus[] = $variation->get_sku();
						if ( method_exists( $variation, 'get_global_unique_id' ) ) {
							$gtins[] = $variation->get_global_unique_id();
						}
					}
				}
			}
		}

		$attributes = array();
		foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
			if ( ! is_object( $attribute ) || ! method_exists( $attribute, 'is_taxonomy' ) ) {
				continue;
			}
			if ( $attribute->is_taxonomy() ) {
				$terms = $attribute->get_terms();
				if ( is_array( $terms ) && count( $terms ) ) {
					foreach ( $terms as $item ) {
						$attributes[] = $item->name;
					}
				}
			} else {
				$_options = $attribute->get_options();
				if ( is_array( $_options ) && count( $_options ) ) {
					foreach ( $_options as $_option ) {
						$attributes[] = $_option;
					}
				}
			}
		}

		$is_visible  = ( 'visible' === $product->get_catalog_visibility() || 'search' === $product->get_catalog_visibility() ) ? 1 : 0;
		$product_img = has_post_thumbnail( $product_id ) ? get_the_post_thumbnail( $product_id, 'thumbnail' ) : '';
		$language    = '';
		$other_lang  = '';
		$other_urls  = array();
		$product_url = $product->get_permalink();
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && has_filter( 'wpml_post_language_details' ) ) {
			$lang_info = apply_filters( 'wpml_post_language_details', null, $product_id );
			$language  = isset( $lang_info['language_code'] ) ? $lang_info['language_code'] : $language;

			$default_lang = apply_filters( 'wpml_default_language', null );
			if ( $language === $default_lang ) {
				$other_lang = $this->get_wpml_missing_languages( $product_id, $language );
				if ( ! empty( $other_lang ) ) {
					$other_urls = $this->get_wpml_missing_language_urls( $product->get_permalink(), $other_lang );
				}
			} else {
				$this->update_wpml_missing_languages( $product_id, $language, $default_lang );
			}
			if ( has_filter( 'wpml_permalink' ) ) {
				$product_url = apply_filters( 'wpml_permalink', $product_url, $language );
			}
		} elseif ( function_exists( 'pll_default_language' ) && function_exists( 'pll_get_post_language' ) ) {
			$language = pll_get_post_language( $product_id ) ? pll_get_post_language( $product_id ) : pll_default_language();
		}

		$skus  = array_filter( $skus );
		$gtins = array_filter( $gtins );

		$row = array(
			'product_id'        => $product_id,
			'title'             => strip_shortcodes( wp_strip_all_tags( get_the_title( $product_id ) ) ),
			'description'       => strip_shortcodes( wp_strip_all_tags( $product->get_description(), true ) ),
			'short_description' => strip_shortcodes( wp_strip_all_tags( $product->get_short_description(), true ) ),
			'product_sku'       => $product->get_sku(),
			'variation_sku'     => implode( ',', $skus ),
			'product_gtin'      => $gtin,
			'variation_gtin'    => implode( ',', $gtins ),
			'attributes'        => implode( ',', $attributes ),
			'product_url'       => $product_url,
			'product_img'       => $product_img,
			'in_stock'          => $product->is_in_stock() ? 1 : 0,
			'is_visible'        => $is_visible,
			'status'            => $product->get_status(),
			'lang'              => $language,
			'other_lang'        => $other_lang,
			'other_urls'        => wp_json_encode( $other_urls ),
		);
		if ( $row_id ) {
			$where = array(
				'id' => $row_id,
			);
			$wpdb->update( $table, $row, $where ); // db call ok; no-cache ok.
		} else {
			$wpdb->insert( $table, $row ); // db call ok; no-cache ok.
		}
	}

	/**
	 * Get wpml missing languages
	 *
	 * @param string $product_id product ID.
	 * @param string $language   product language.
	 */
	public function get_wpml_missing_languages( $product_id, $language ) {
		$other_langs  = array();
		$active_langs = array();
		$languages    = apply_filters( 'wpml_active_languages', null );
		if ( is_array( $languages ) && count( $languages ) ) {
			foreach ( $languages as $lang ) {
				if ( isset( $lang['language_code'] ) && ! empty( $lang['language_code'] ) && $language !== $lang['language_code'] ) {
					$active_langs[] = $lang['language_code'];
				}
			}
		}

		$post_langs   = array();
		$elem_type    = apply_filters( 'wpml_element_type', 'product' );
		$trans_id     = apply_filters( 'wpml_element_trid', false, $product_id, $elem_type );
		$translations = apply_filters( 'wpml_get_element_translations', array(), $trans_id, $elem_type );
		if ( is_array( $translations ) && count( $translations ) ) {
			foreach ( $translations as $trans ) {
				if ( isset( $trans->language_code ) && ! empty( $trans->language_code ) && $language !== $trans->language_code ) {
					$post_langs[] = $trans->language_code;
				}
			}
		}
		$other_langs = array_diff( $active_langs, $post_langs );

		return implode( ',', $other_langs );
	}

	/**
	 * Update wpml missing languages
	 *
	 * @param string $product_id   product ID.
	 * @param string $language     product language.
	 * @param string $default_lang site default language.
	 */
	public function update_wpml_missing_languages( $product_id, $language, $default_lang ) {
		global $wpdb;
		$original_id = apply_filters( 'wpml_object_id', $product_id, 'product', false, $default_lang );
		if ( $original_id ) {
			$table      = $wpdb->prefix . 'commercekit_ajs_product_index';
			$other_lang = $wpdb->get_var( $wpdb->prepare( 'SELECT other_lang FROM ' . $table . ' WHERE product_id = %d AND lang = %s', $original_id, $default_lang ) ); // phpcs:ignore
			if ( ! empty( $other_lang ) ) {
				$langs = explode( ',', $other_lang );
				$key   = array_search( $language, $langs, true );
				if ( false !== $key && isset( $langs[ $key ] ) ) {
					unset( $langs[ $key ] );
				}
				$where = array(
					'product_id' => $original_id,
					'lang'       => $default_lang,
				);
				$row   = array(
					'other_lang' => implode( ',', $langs ),
				);
				$wpdb->update( $table, $row, $where ); // db call ok; no-cache ok.
			}
		}
	}

	/**
	 * Get wpml missing language urls
	 *
	 * @param string $permalink   product original URL.
	 * @param string $other_langs other languages.
	 */
	public function get_wpml_missing_language_urls( $permalink, $other_langs ) {
		$other_urls  = array();
		$other_langs = explode( ',', $other_langs );
		if ( count( $other_langs ) ) {
			foreach ( $other_langs as $lang ) {
				$other_urls[ $lang ] = apply_filters( 'wpml_permalink', $permalink, $lang );
			}
		}

		return $other_urls;
	}

	/**
	 * Prepare action scheduler for all ajs index
	 */
	public function ajs_prepare_action_scheduler() {
		global $wpdb;
		$options    = get_option( 'commercekit', array() );
		$ajs_active = isset( $options['ajax_search'] ) && 1 === (int) $options['ajax_search'] ? true : false;
		if ( ! $ajs_active ) {
			return;
		}
		$args = array(
			'hook'     => 'commercegurus_ajs_run_wc_product_index',
			'per_page' => -1,
			'group'    => 'commercekit',
			'status'   => ActionScheduler_Store::STATUS_RUNNING,
		);

		$is_running = false;
		$action_ids = as_get_scheduled_actions( $args, 'ids' );
		if ( count( $action_ids ) ) {
			$is_running = true;
		}
		$args2 = array(
			'hook'     => 'commercegurus_ajs_run_wc_product_index',
			'per_page' => -1,
			'group'    => 'commercekit',
			'status'   => ActionScheduler_Store::STATUS_PENDING,
		);

		$action_ids2 = as_get_scheduled_actions( $args2, 'ids' );
		if ( count( $action_ids2 ) ) {
			if ( ! $is_running && 1 === count( $action_ids2 ) ) {
				return;
			}
			if ( ! $is_running ) {
				sort( $action_ids2, SORT_NUMERIC );
				array_pop( $action_ids2 );
			}
			$query = 'DELETE FROM ' . $wpdb->prefix . 'actionscheduler_actions WHERE action_id IN (' . implode( ',', $action_ids2 ) . ')';
			$wpdb->query( $query ); // phpcs:ignore
			$query2 = 'DELETE FROM ' . $wpdb->prefix . 'actionscheduler_logs WHERE action_id IN (' . implode( ',', $action_ids2 ) . ')';
			$wpdb->query( $query2 ); // phpcs:ignore

			return;
		}
		if ( $is_running ) {
			return;
		}
		$ajs_scheduled = isset( $options['generating_ajs'] ) && 1 === (int) $options['generating_ajs'] ? true : false;
		if ( $ajs_scheduled ) {
			return;
		}
		$cancelled_ajs = isset( $options['cancelled_ajs'] ) && 1 === (int) $options['cancelled_ajs'] ? true : false;
		if ( $cancelled_ajs ) {
			return;
		}
		$interrupt_ajs = isset( $options['interrupt_ajs'] ) && 1 === (int) $options['interrupt_ajs'] ? true : false;
		if ( $interrupt_ajs ) {
			return;
		}
		$generate_ajs = isset( $_POST['generate_ajs'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['generate_ajs'] ) ) : 0; // phpcs:ignore
		if ( $generate_ajs ) {
			return;
		}
		$ajs_product_id = isset( $options['generating_ajs_id'] ) ? (int) $options['generating_ajs_id'] : 0;
		if ( 0 < $ajs_product_id ) {
			$generating_ajs_done = isset( $options['generating_ajs_done'] ) ? (int) $options['generating_ajs_done'] : 0;
			if ( $generating_ajs_done ) {
				$template = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_parent = '0' AND post_status = 'publish' AND ID > %d ORDER BY ID ASC";
				$query    = $wpdb->prepare( $template, $ajs_product_id ); // phpcs:ignore
				$pending  = (int) $wpdb->get_var( $query ); // phpcs:ignore
				if ( $pending ) {
					$args3 = array(
						'hook'     => 'commercegurus_ajs_run_wc_product_index',
						'per_page' => -1,
						'group'    => 'commercekit',
						'status'   => array( ActionScheduler_Store::STATUS_PENDING, ActionScheduler_Store::STATUS_RUNNING ),
					);

					$action_ids3 = as_get_scheduled_actions( $args3, 'ids' );
					if ( ! count( $action_ids3 ) ) {
						as_schedule_single_action( time() + 5, 'commercegurus_ajs_run_wc_product_index', array( 'ajs_product_id' => $ajs_product_id ), 'commercekit' );
						commercegurus_ajs_log( 'REBUILDING INDEX: creating action for commercegurus_ajs_run_wc_product_index hook with new product_id = ' . $ajs_product_id );
						$options['generating_ajs_id']   = $ajs_product_id;
						$options['generating_ajs']      = 1;
						$options['generating_ajs_done'] = 0;
						commercegurus_ajs_log( 'updating generating_ajs_id to ' . $ajs_product_id . ', generating_ajs to 1, generating_ajs_done to 0' );
						update_option( 'commercekit', $options, false );
					}
					return;
				} else {
					return;
				}
			} else {
				return;
			}
		}

		$args4 = array(
			'hook'     => 'commercegurus_ajs_run_wc_product_index',
			'per_page' => -1,
			'group'    => 'commercekit',
			'status'   => array( ActionScheduler_Store::STATUS_PENDING, ActionScheduler_Store::STATUS_RUNNING ),
		);

		$action_ids4 = as_get_scheduled_actions( $args4, 'ids' );
		if ( ! count( $action_ids4 ) ) {
			as_schedule_single_action( time() + 5, 'commercegurus_ajs_run_wc_product_index', array( 'ajs_product_id' => 0 ), 'commercekit' );
			commercegurus_ajs_log( 'BUILDING INDEX: creating action for commercegurus_ajs_run_wc_product_index hook with product_id = 0' );
			$options['generating_ajs_id']   = 0;
			$options['generating_ajs']      = 1;
			$options['generating_ajs_done'] = 0;
			commercegurus_ajs_log( 'updating generating_ajs_id to 0, generating_ajs to 1, generating_ajs_done to 0' );
			update_option( 'commercekit', $options, false );
		}
	}

	/**
	 * WooCommerce product ajs index db table
	 *
	 * @param string $args arguments.
	 */
	public function run_wc_product_index( $args ) {
		global $wpdb;
		$ajs_product_id = 0;
		if ( is_numeric( $args ) ) {
			$ajs_product_id = (int) $args;
		} elseif ( is_array( $args ) ) {
			if ( isset( $args[0] ) && is_numeric( $args[0] ) ) {
				$ajs_product_id = (int) $args[0];
			} elseif ( isset( $args['ajs_product_id'] ) && is_numeric( $args['ajs_product_id'] ) ) {
				$ajs_product_id = (int) $args['ajs_product_id'];
			}
		}
		$this->start_time     = time();
		$this->memory_limit   = $this->get_memory_limit();
		$this->execution_time = $this->get_execution_time();

		$template = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_parent = '0' AND post_status = 'publish' AND ID > %d ORDER BY ID ASC";
		$query    = $wpdb->prepare( $template, $ajs_product_id ); // phpcs:ignore
		$results  = $wpdb->get_col( $query ); // phpcs:ignore
		if ( count( $results ) ) {
			$options  = get_option( 'commercekit', array() );
			$next_job = false;
			$counter  = 0;
			foreach ( $results as $product_id ) {
				$data = commercekit_ajs_temporary_options( 'GET' );
				if ( ( isset( $options['interrupt_ajs'] ) && 1 === (int) $options['interrupt_ajs'] ) || ( isset( $data['interrupt_ajs'] ) && 1 === (int) $data['interrupt_ajs'] ) ) {
					commercegurus_ajs_log( 'REBUILDING STOP: Rebuilding has been stopped due to indexing process interrupted' );
					return;
				}
				if ( ( isset( $options['cancelled_ajs'] ) && 1 === (int) $options['cancelled_ajs'] ) || ( isset( $data['cancelled_ajs'] ) && 1 === (int) $data['cancelled_ajs'] ) ) {
					commercegurus_ajs_log( 'REBUILDING STOP: Rebuilding has been stopped due to indexing process cancelled' );
					return;
				}
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}
				$this->build_ajs_index( $product, 'via Action Scheduler' );
				$counter++;

				$options['generating_ajs_id'] = $product_id;
				$options['generate_ajs_time'] = time();
				update_option( 'commercekit', $options, false );
				if ( $this->memory_exceeded() || $this->time_exceeded() || $counter >= 500 ) {
					$next_job       = true;
					$ajs_product_id = $product_id;
					break;
				}
			}
			$completed = true;
			if ( $next_job && $ajs_product_id ) {
				$template2 = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_parent = '0' AND post_status = 'publish' AND ID > %d ";
				$query2    = $wpdb->prepare( $template2, $ajs_product_id ); // phpcs:ignore
				$pending   = (int) $wpdb->get_var( $query2 ); // phpcs:ignore
				if ( $pending ) {
					as_schedule_single_action( time() + 5, 'commercegurus_ajs_run_wc_product_index', array( 'ajs_product_id' => $ajs_product_id ), 'commercekit' );
					commercegurus_ajs_log( 'REBUILDING INDEX: creating action for commercegurus_ajs_run_wc_product_index hook with next product_id = ' . $ajs_product_id );
					$completed = false;
				}
			}
			if ( $completed ) {
				$options['generating_ajs']      = 0;
				$options['generating_ajs_done'] = 1;
				update_option( 'commercekit', $options, false );
				commercegurus_ajs_log( 'REBUILDING INDEX: complete ajax search index for all products.' );
				commercekit_ajs_temporary_options( 'DEL' );
			}
		}
	}

	/**
	 * Get search product ids
	 *
	 * @param string $keywords search words.
	 * @param string $return_all return all product ids.
	 * @param string $language current language code.
	 * @param string $phrase match results.
	 */
	public function get_search_product_ids( $keywords, $return_all = false, $language = '', $phrase = false ) {
		global $wpdb;

		$keywords = trim( $keywords );
		if ( empty( $keywords ) ) {
			if ( $return_all ) {
				return array(
					'ids'   => array( -1 ),
					'total' => 0,
				);
			} else {
				return array(
					'products' => array(),
					'total'    => 0,
				);
			}
		}

		$options = get_option( 'commercekit', array() );
		$table   = $wpdb->prefix . 'commercekit_ajs_product_index';
		$keys    = $this->get_search_parsed_words( $keywords );
		$total   = count( $keys );
		$and_sql = array();
		$or_sql  = array();

		if ( ! $phrase && $total > 1 ) {
			$return_data = $this->get_search_product_ids( $keywords, $return_all, $language, true );
			if ( $return_all && isset( $return_data['total'] ) && $return_data['total'] ) {
				return $return_data;
			}
			if ( ! $return_all && isset( $return_data['products'] ) && count( $return_data['products'] ) ) {
				return $return_data;
			}
		}

		if ( $phrase && $total > 1 ) {
			$keys  = array( $keywords );
			$total = 1;
		}

		$cgkit_fast_search = isset( $options['ajs_fast_search'] ) && 1 === (int) $options['ajs_fast_search'] ? true : false;

		if ( 1 === $total ) {
			$or_sql[] = 'title LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'description LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'short_description LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'attributes LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'product_sku LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'variation_sku LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'product_gtin LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'variation_gtin LIKE \'%' . esc_sql( $keywords ) . '%\'';
		} else {
			foreach ( $keys as $key ) {
				$or_sql[] = 'title LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'description LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'short_description LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'attributes LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'product_sku LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'variation_sku LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'product_gtin LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'variation_gtin LIKE \'%' . esc_sql( $key ) . '%\'';
			}
		}

		$and_sql[] = 'is_visible = 1';
		$and_sql[] = 'status = \'publish\'';
		$order_by  = $this->get_order_by_string( $keywords, $keys, 'title', 'description' );

		if ( isset( $options['ajs_orderby_oos'] ) && 1 === (int) $options['ajs_orderby_oos'] ) {
			$order_by = 'in_stock DESC' . ( ! empty( $order_by ) ? ', ' . $order_by : '' );
		}
		if ( ( isset( $options['ajs_outofstock'] ) && 1 === (int) $options['ajs_outofstock'] ) || 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$and_sql[] = 'in_stock = 1';
		}

		$limit = isset( $options['ajs_product_count'] ) && (int) $options['ajs_product_count'] ? (int) $options['ajs_product_count'] : 3;
		if ( $limit < 1 || $limit > 5 ) {
			$limit = 3;
		}

		$ajs_excludes = isset( $options['ajs_excludes'] ) ? explode( ',', $options['ajs_excludes'] ) : array();
		$ajs_excludes = array_filter( $ajs_excludes );
		$ajs_excludes = array_map( 'intval', $ajs_excludes );
		if ( count( $ajs_excludes ) ) {
			$and_sql[] = 'product_id NOT IN (' . implode( ',', $ajs_excludes ) . ')';
		}
		if ( ! empty( $language ) ) {
			$and_sql[] = '( lang = \'' . esc_sql( $language ) . '\' OR lang = \'\' OR FIND_IN_SET(\'' . esc_sql( $language ) . '\', other_lang) )';
		}

		$cquery = 'SELECT COUNT(product_id) FROM ' . $table . ' WHERE 1 = 1 ';
		if ( $return_all ) {
			$query = 'SELECT product_id FROM ' . $table . ' WHERE 1 = 1 ';
		} else {
			$query = 'SELECT product_id, title, short_description, product_url, product_img, lang, other_lang, other_urls FROM ' . $table . ' WHERE 1 = 1 ';
		}

		if ( count( $or_sql ) ) {
			$cquery .= ' AND ( ' . implode( ' OR ', $or_sql ) . ' ) ';
			$query  .= ' AND ( ' . implode( ' OR ', $or_sql ) . ' ) ';
		}
		if ( count( $and_sql ) ) {
			$cquery .= ' AND ( ' . implode( ' AND ', $and_sql ) . ' ) ';
			$query  .= ' AND ( ' . implode( ' AND ', $and_sql ) . ' ) ';
		}

		if ( ! empty( $order_by ) ) {
			$query .= ' ORDER BY ' . $order_by;
		}

		if ( $return_all ) {
			$product_ids = $wpdb->get_col( $query ); // phpcs:ignore
			$ids_count   = count( $product_ids );
			if ( ! $ids_count ) {
				$product_ids = array( -1 );
			}
			return array(
				'ids'   => $product_ids,
				'total' => $ids_count,
			);
		} else {
			$query   .= ' LIMIT 0, ' . $limit;
			$products = $wpdb->get_results( $query ); // phpcs:ignore
			$total    = 0;
			if ( ! $cgkit_fast_search ) {
				$total = (int) $wpdb->get_var( $cquery ); // phpcs:ignore
			}

			return array(
				'products' => $products,
				'total'    => $total,
			);
		}
	}

	/**
	 * Get order by string
	 *
	 * @param string $keywords search words.
	 * @param string $search_terms search words as array.
	 * @param string $title_field title field name.
	 * @param string $content_field content field name.
	 */
	public function get_order_by_string( $keywords, $search_terms, $title_field, $content_field ) {
		global $wpdb;
		$search_orderby       = '';
		$search_orderby_title = array();
		if ( count( $search_terms ) ) {
			foreach ( $search_terms as $term ) {
				$like                   = '%' . $wpdb->esc_like( $term ) . '%';
				$search_orderby_title[] = $wpdb->prepare( "$title_field LIKE %s", $like ); // phpcs:ignore
			}
		}
		if ( count( $search_terms ) > 0 ) {
			$num_terms       = count( $search_orderby_title );
			$like            = '%' . $wpdb->esc_like( $keywords ) . '%';
			$like2           = $wpdb->esc_like( $keywords ) . '%';
			$search_orderby .= '(CASE ';
			$search_orderby .= $wpdb->prepare( "WHEN $title_field LIKE %s THEN 1 ", $like2 ); // phpcs:ignore
			$search_orderby .= $wpdb->prepare( "WHEN $title_field LIKE %s THEN 2 ", $like ); // phpcs:ignore
			if ( $num_terms < 10 && count( $search_terms ) > 1 ) { // make 10 words as sentence.
				$search_orderby .= 'WHEN ' . implode( ' AND ', $search_orderby_title ) . ' THEN 3 ';
				if ( $num_terms > 1 ) {
					$search_orderby .= 'WHEN ' . implode( ' OR ', $search_orderby_title ) . ' THEN 4 ';
				}
			}
			$search_orderby .= $wpdb->prepare( "WHEN $content_field LIKE %s THEN 5 ", $like ); // phpcs:ignore
			$search_orderby .= 'ELSE 6 END)';
		}
		return $search_orderby;
	}

	/**
	 * Get search parsed words
	 *
	 * @param string $keywords search words.
	 */
	public function get_search_parsed_words( $keywords ) {
		$keywords = stripslashes( $keywords );
		$keywords = str_replace( array( "\r", "\n" ), '', $keywords );
		if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $keywords, $matches ) ) {
			$search_words = $this->get_search_parsed_terms( $matches[0] );
			if ( empty( $search_words ) || count( $search_words ) > 9 ) { // make 10 words as sentence.
				$search_words = array( $keywords );
			}
		} else {
			$search_words = array( $keywords );
		}
		return $search_words;
	}

	/**
	 * Get search parsed terms
	 *
	 * @param string $terms search terms.
	 */
	public function get_search_parsed_terms( $terms ) {
		$final_terms = array();
		if ( count( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( preg_match( '/^".+"$/', $term ) ) {
					$term = trim( $term, "\"'" );
				} else {
					$term = trim( $term, "\"' " );
				}
				if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z]$/i', $term ) ) ) {
					continue;
				}
				$final_terms[] = $term;
			}
		}
		return $final_terms;
	}

	/**
	 * Get search post ids
	 *
	 * @param string $keywords search words.
	 * @param string $return_all return all product ids.
	 * @param string $language current language code.
	 * @param string $language_plug language plugin.
	 * @param string $phrase match results.
	 */
	public function get_search_post_ids( $keywords, $return_all = false, $language = '', $language_plug = '', $phrase = false ) {
		global $wpdb;
		$keywords = trim( $keywords );
		if ( empty( $keywords ) ) {
			if ( $return_all ) {
				return array(
					'ids'   => array( -1 ),
					'total' => 0,
				);
			} else {
				return array(
					'posts' => array(),
					'total' => 0,
				);
			}
		}

		$options = get_option( 'commercekit', array() );
		$table   = $wpdb->prefix . 'posts';
		$keys    = $this->get_search_parsed_words( $keywords );
		$total   = count( $keys );
		$and_sql = array();
		$or_sql  = array();

		if ( ! $phrase && $total > 1 ) {
			$return_data = $this->get_search_post_ids( $keywords, $return_all, $language, $language_plug, true );
			if ( $return_all && isset( $return_data['total'] ) && $return_data['total'] ) {
				return $return_data;
			}
			if ( ! $return_all && isset( $return_data['posts'] ) && count( $return_data['posts'] ) ) {
				return $return_data;
			}
		}

		if ( $phrase && $total > 1 ) {
			$keys  = array( $keywords );
			$total = 1;
		}

		$cgkit_fast_search = isset( $options['ajs_fast_search'] ) && 1 === (int) $options['ajs_fast_search'] ? true : false;

		if ( 1 === $total ) {
			$or_sql[] = 'p.post_title LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'p.post_content LIKE \'%' . esc_sql( $keywords ) . '%\'';
			$or_sql[] = 'p.post_excerpt LIKE \'%' . esc_sql( $keywords ) . '%\'';
		} else {
			foreach ( $keys as $key ) {
				$or_sql[] = 'p.post_title LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'p.post_content LIKE \'%' . esc_sql( $key ) . '%\'';
				$or_sql[] = 'p.post_excerpt LIKE \'%' . esc_sql( $key ) . '%\'';
			}
		}
		$and_sql[] = 'p.post_status = \'publish\'';
		$order_by  = $this->get_order_by_string( $keywords, $keys, 'p.post_title', 'p.post_content' );

		$limit = isset( $options['ajs_other_count'] ) && (int) $options['ajs_other_count'] ? (int) $options['ajs_other_count'] : 3;
		if ( $limit < 1 || $limit > 5 ) {
			$limit = 3;
		}

		$ajs_excludes_other = isset( $options['ajs_excludes_other'] ) ? explode( ',', $options['ajs_excludes_other'] ) : array();
		$ajs_excludes_other = array_filter( $ajs_excludes_other );
		$ajs_excludes_other = array_map( 'intval', $ajs_excludes_other );
		if ( count( $ajs_excludes_other ) ) {
			$and_sql[] = 'p.ID NOT IN (' . implode( ',', $ajs_excludes_other ) . ')';
		}

		$all_post_types = isset( $options['ajs_other_post_types'] ) ? $options['ajs_other_post_types'] : array();
		if ( is_array( $all_post_types ) && count( $all_post_types ) ) {
			$and_sql[] = 'p.post_type IN (\'' . implode( '\',\'', $all_post_types ) . '\')';
		} else {
			$and_sql[] = 'p.post_type NOT IN (\'product\',\'product_variation\')';
		}

		$left_join = '';
		if ( ! empty( $language ) ) {
			if ( 'wpml' === $language_plug ) {
				$left_join = ' LEFT JOIN ' . $wpdb->prefix . 'icl_translations AS pl ON pl.element_id = p.ID ';
				$and_sql[] = 'pl.language_code = \'' . esc_sql( $language ) . '\'';
			} elseif ( 'polylang' === $language_plug ) {
				$taxonomy_sql = 'SELECT tt.term_taxonomy_id FROM ' . $wpdb->prefix . 'term_taxonomy AS tt LEFT JOIN ' . $wpdb->prefix . 'terms AS t ON t.term_id = tt.term_id WHERE tt.taxonomy = \'language\' AND t.slug = \'' . esc_sql( $language ) . '\' GROUP BY tt.term_taxonomy_id';
				$taxonomy_id  = (int) $wpdb->get_var( $taxonomy_sql ); // phpcs:ignore
				if ( $taxonomy_id ) {
					$left_join = ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships AS pl ON pl.object_id = p.ID ';
					$and_sql[] = 'pl.term_taxonomy_id = \'' . $taxonomy_id . '\'';
				}
			}
		}

		$cquery = 'SELECT COUNT(p.ID) FROM ' . $table . ' AS p ' . $left_join . ' WHERE 1 = 1 ';
		if ( $return_all ) {
			$query = 'SELECT p.ID FROM ' . $table . ' AS p ' . $left_join . ' WHERE 1 = 1 ';
		} else {
			$query = 'SELECT p.ID, p.post_title, p.post_type, p.post_name FROM ' . $table . ' AS p ' . $left_join . ' WHERE 1 = 1 ';
		}

		if ( count( $or_sql ) ) {
			$cquery .= ' AND ( ' . implode( ' OR ', $or_sql ) . ' ) ';
			$query  .= ' AND ( ' . implode( ' OR ', $or_sql ) . ' ) ';
		}
		if ( count( $and_sql ) ) {
			$cquery .= ' AND ( ' . implode( ' AND ', $and_sql ) . ' ) ';
			$query  .= ' AND ( ' . implode( ' AND ', $and_sql ) . ' ) ';
		}

		$query .= ' GROUP BY p.ID ';
		if ( ! empty( $order_by ) ) {
			$query .= ' ORDER BY ' . $order_by;
		}

		if ( $return_all ) {
			$post_ids  = $wpdb->get_col( $query ); // phpcs:ignore
			$ids_count = count( $post_ids );
			if ( ! $ids_count ) {
				$post_ids = array( -1 );
			}
			return array(
				'ids'   => $post_ids,
				'total' => $ids_count,
			);
		} else {
			$query .= ' LIMIT 0, ' . $limit;
			$posts  = $wpdb->get_results( $query ); // phpcs:ignore
			$total  = 0;
			if ( ! $cgkit_fast_search ) {
				$total = (int) $wpdb->get_var( $cquery ); // phpcs:ignore
			}

			return array(
				'posts' => $posts,
				'total' => $total,
			);
		}
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

global $commercekit_ajs_index;
$commercekit_ajs_index = new CommerceKit_AJS_Index();

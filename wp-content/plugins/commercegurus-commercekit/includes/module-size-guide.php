<?php
/**
 *
 * Size Guides
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Create Size Guides post type.
 */
function commercekit_sg_create_post_type() {
	$options    = get_option( 'commercekit', array() );
	$exc_search = ( ( isset( $options['size_guide_search'] ) && 1 === (int) $options['size_guide_search'] ) || ( ! isset( $options['size_guide_search'] ) && 1 === (int) commercekit_get_default_settings( 'size_guide_search' ) ) ) ? false : true;
	$args       = array(
		'labels'            => array(
			'name'          => esc_html__( 'Size Guides', 'commercegurus-commercekit' ),
			'singular_name' => esc_html__( 'Size Guide', 'commercegurus-commercekit' ),
		),
		'public'            => true,
		'has_archive'       => true,
		'show_in_rest'      => false,
		'show_in_nav_menus' => true,
		'show_in_menu'      => true,
		'menu_icon'         => 'dashicons-media-spreadsheet',
		'show_in_rest'      => true,
		'supports'          => array( 'title', 'editor', 'page-attributes' ),
	);

	$args['exclude_from_search'] = $exc_search;
	register_post_type( 'ckit_size_guide', $args );
}
add_action( 'init', 'commercekit_sg_create_post_type' );

/**
 * Add admin CSS and JS scripts for Size Guide post type
 */
function commercekit_sg_admin_scripts() {
	$screen = get_current_screen();
	if ( 'ckit_size_guide' === $screen->post_type && 'post' === $screen->base ) {
		wp_enqueue_style( 'woocommerce-select2-styles', WC()->plugin_url() . '/assets/css/select2.css', array(), WC()->version );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'commercekit-sg-script', CKIT_URI . 'assets/js/admin-size-guide.js', array(), CGKIT_CSS_JS_VER, true );
	}
}
add_action( 'admin_enqueue_scripts', 'commercekit_sg_admin_scripts' );

/**
 * Admin meta box.
 */
function commercekit_sg_admin_meta_box() {
	add_meta_box( 'commercekit-sg-prod-meta-box', esc_html__( 'Products', 'commercegurus-commercekit' ), 'commercekit_sg_admin_products_display', 'ckit_size_guide', 'side', 'low' );
	add_meta_box( 'commercekit-sg-cat-meta-box', esc_html__( 'Product categories', 'commercegurus-commercekit' ), 'commercekit_sg_admin_categories_display', 'ckit_size_guide', 'side', 'low' );
	add_meta_box( 'commercekit-sg-tag-meta-box', esc_html__( 'Product tags', 'commercegurus-commercekit' ), 'commercekit_sg_admin_tags_display', 'ckit_size_guide', 'side', 'low' );
	add_meta_box( 'commercekit-sg-attr-meta-box', esc_html__( 'Product attributes', 'commercegurus-commercekit' ), 'commercekit_sg_admin_attributes_display', 'ckit_size_guide', 'side', 'low' );
}
add_action( 'admin_init', 'commercekit_sg_admin_meta_box' );

/**
 * Admin categories meta box display.
 *
 * @param string $post post object.
 */
function commercekit_sg_admin_categories_display( $post ) {
	$options = '';
	if ( isset( $post->ID ) && $post->ID ) {
		$categories = commercekit_sg_get_post_meta( $post->ID, 'sg_cat' );
		$categories = array_map( 'intval', $categories );
		if ( is_array( $categories ) && count( $categories ) ) {
			foreach ( $categories as $category ) {
				$nterm = get_term_by( 'id', $category, 'product_cat' );
				if ( $nterm ) {
					$options .= '<option value="' . esc_attr( $category ) . '" selected="selected">#' . esc_attr( $category ) . ' - ' . esc_html( $nterm->name ) . '</option>';
				}
			}
		}
	}
	echo '<div style="width: 100%; display: block; margin: 15px 0px 10px;"><label>' . esc_html__( 'Product categories:', 'commercegurus-commercekit' ) . '</label><br /><label><select name="commercekit_sg_cat[]" class="commercekit-select2" data-type="category" multiple="multiple" data-placeholder="' . esc_html__( 'Select category', 'commercegurus-commercekit' ) . '" style="width:100%;">' . $options . '</select></label><input type="hidden" name="commercekit_sg_cat_tmp" id="commercekit_sg_cat_tmp" value="1" /></div>'; // phpcs:ignore
	wp_nonce_field( 'commercekit_settings', 'commercekit_nonce' );
}

/**
 * Admin products meta box display.
 *
 * @param string $post post object.
 */
function commercekit_sg_admin_products_display( $post ) {
	$options = '';
	if ( isset( $post->ID ) && $post->ID ) {
		$products = commercekit_sg_get_post_meta( $post->ID, 'sg_prod' );
		$products = array_map( 'intval', $products );
		if ( is_array( $products ) && count( $products ) ) {
			foreach ( $products as $product_id ) {
				$options .= '<option value="' . esc_attr( $product_id ) . '" selected="selected">#' . esc_attr( $product_id ) . ' - ' . esc_html( commercekit_limit_title( get_the_title( $product_id ) ) ) . '</option>';
			}
		}
	}
	echo '<div style="width: 100%; display: block; margin: 15px 0px 10px;"><label>' . esc_html__( 'Products:', 'commercegurus-commercekit' ) . '</label><br /><label><select name="commercekit_sg_prod[]" class="commercekit-select2" data-type="product" data-placeholder="' . esc_html__( 'Select product', 'commercegurus-commercekit' ) . '" multiple="multiple" style="width:100%;">' . $options . '</select></label><input type="hidden" name="commercekit_sg_prod_tmp" id="commercekit_sg_prod_tmp" value="1" /></div>'; // phpcs:ignore
}

/**
 * Admin tags meta box display.
 *
 * @param string $post post object.
 */
function commercekit_sg_admin_tags_display( $post ) {
	$options = '';
	if ( isset( $post->ID ) && $post->ID ) {
		$tags = commercekit_sg_get_post_meta( $post->ID, 'sg_tag' );
		$tags = array_map( 'intval', $tags );
		if ( is_array( $tags ) && count( $tags ) ) {
			foreach ( $tags as $tag ) {
				$nterm = get_term_by( 'id', $tag, 'product_tag' );
				if ( $nterm ) {
					$options .= '<option value="' . esc_attr( $tag ) . '" selected="selected">#' . esc_attr( $tag ) . ' - ' . esc_html( $nterm->name ) . '</option>';
				}
			}
		}
	}
	echo '<div style="width: 100%; display: block; margin: 15px 0px 10px;"><label>' . esc_html__( 'Product tags:', 'commercegurus-commercekit' ) . '</label><br /><label><select name="commercekit_sg_tag[]" class="commercekit-select2" data-type="tag" multiple="multiple" data-placeholder="' . esc_html__( 'Select tag', 'commercegurus-commercekit' ) . '" style="width:100%;">' . $options . '</select></label><input type="hidden" name="commercekit_sg_tag_tmp" id="commercekit_sg_tag_tmp" value="1" /></div>'; // phpcs:ignore
}

/**
 * Admin attributes meta box display.
 *
 * @param string $post post object.
 */
function commercekit_sg_admin_attributes_display( $post ) {
	$options = '';
	if ( isset( $post->ID ) && $post->ID ) {
		$attributes = commercekit_sg_get_post_meta( $post->ID, 'sg_attr' );
		if ( is_array( $attributes ) && count( $attributes ) ) {
			$taxonomies = commercekit_sg_get_product_attributes();
			foreach ( $attributes as $attribute ) {
				$temp     = explode( ':', $attribute );
				$term_id  = isset( $temp[0] ) ? (int) $temp[0] : 0;
				$term_tax = isset( $temp[1] ) ? $temp[1] : '';
				$nterm    = get_term_by( 'id', $term_id, $term_tax );
				if ( $nterm ) {
					$tax_name = isset( $taxonomies[ $term_tax ] ) ? $taxonomies[ $term_tax ] : '';
					$options .= '<option value="' . esc_attr( $attribute ) . '" selected="selected">' . esc_attr( $nterm->name ) . ' - ' . esc_html( $tax_name ) . '</option>';
				}
			}
		}
	}
	echo '<div style="width: 100%; display: block; margin: 15px 0px 10px;"><label>' . esc_html__( 'Product attributes:', 'commercegurus-commercekit' ) . '</label><br /><label><select name="commercekit_sg_attr[]" class="commercekit-select2" data-type="attribute" multiple="multiple" data-placeholder="' . esc_html__( 'Select attribute', 'commercegurus-commercekit' ) . '" style="width:100%;">' . $options . '</select></label><input type="hidden" name="commercekit_sg_attr_tmp" id="commercekit_sg_attr_tmp" value="1" /></div>'; // phpcs:ignore
}

/**
 * Admin meta box save.
 *
 * @param string $post_id post id.
 * @param string $post post object.
 */
function commercekit_sg_admin_meta_save( $post_id, $post ) {
	if ( 'ckit_size_guide' === $post->post_type ) {
		if ( isset( $_POST['commercekit_sg_cat_tmp'] ) && 1 === (int) $_POST['commercekit_sg_cat_tmp'] ) { // phpcs:ignore
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
			if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
				return;
			}

			$categories = isset( $_POST['commercekit_sg_cat'] ) ? map_deep( wp_unslash( $_POST['commercekit_sg_cat'] ), 'sanitize_text_field' ) : array();
			$categories = array_filter( $categories );

			$products = isset( $_POST['commercekit_sg_prod'] ) ? map_deep( wp_unslash( $_POST['commercekit_sg_prod'] ), 'sanitize_text_field' ) : array();
			$products = array_filter( $products );

			$tags = isset( $_POST['commercekit_sg_tag'] ) ? map_deep( wp_unslash( $_POST['commercekit_sg_tag'] ), 'sanitize_text_field' ) : array();
			$tags = array_filter( $tags );

			$attributes = isset( $_POST['commercekit_sg_attr'] ) ? map_deep( wp_unslash( $_POST['commercekit_sg_attr'] ), 'sanitize_text_field' ) : array();
			$attributes = array_filter( $attributes );
			$active     = 'publish' === $post->post_status ? 1 : 0;
			$meta_data  = array(
				'sg_cat'  => $categories,
				'sg_prod' => $products,
				'sg_tag'  => $tags,
				'sg_attr' => $attributes,
			);

			commercekit_sg_update_post_meta( $post->ID, $active, $meta_data );
		}
	}
}
add_action( 'save_post', 'commercekit_sg_admin_meta_save', 10, 2 );

/**
 * Get products or categories IDs
 */
function commercekit_sg_get_pcids() {
	$return = array();
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( $return );
	}

	$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $return );
	}

	$type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'product';
	if ( 'product' === $type ) {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			's'              => $query,
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'post_type'      => 'product',
		);
		if ( is_numeric( $query ) ) {
			unset( $args['s'] );
			$args['post__in'] = array( $query );
		}
		$search_results = new WP_Query( $args );
		if ( $search_results->have_posts() ) {
			while ( $search_results->have_posts() ) {
				$search_results->the_post();
				$product = wc_get_product( $search_results->post->ID );
				if ( ! $product ) {
						continue;
				}
				$title    = commercekit_limit_title( $search_results->post->post_title );
				$title    = '#' . $search_results->post->ID . ' - ' . $title;
				$return[] = array( $search_results->post->ID, $title );
			}
		}
	} elseif ( 'category' === $type || 'tag' === $type ) {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			'name__like' => $query,
			'hide_empty' => true,
			'number'     => 20,
		);

		$taxonomy = 'product_cat';
		if ( 'tag' === $type ) {
			$taxonomy = 'product_tag';
		}
		if ( is_numeric( $query ) ) {
			$terms = array( get_term( $query, $taxonomy ) );
		} else {
			$terms = get_terms( $taxonomy, $args );
		}
		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			foreach ( $terms as $term ) {
				if ( isset( $term->name ) ) {
					$term->name = '#' . $term->term_id . ' - ' . $term->name;
					$return[]   = array( $term->term_id, $term->name );
				}
			}
		}
	} elseif ( 'attribute' === $type ) {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$taxes = commercekit_sg_get_product_attributes();
		$args  = array(
			'name__like' => $query,
			'hide_empty' => true,
			'number'     => 20,
			'taxonomy'   => array_keys( $taxes ),
		);
		$terms = get_terms( $args );
		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			foreach ( $terms as $term ) {
				if ( isset( $term->name ) ) {
					$term_id   = $term->term_id . ':' . $term->taxonomy;
					$term_name = $term->name . ' - ' . ( isset( $taxes[ $term->taxonomy ] ) ? $taxes[ $term->taxonomy ] : '' );
					$return[]  = array( $term_id, $term_name );
				}
			}
		}
	}

	wp_send_json( $return );
}
add_action( 'wp_ajax_commercekit_sg_get_pcids', 'commercekit_sg_get_pcids' );

/**
 * Get product attributes.
 */
function commercekit_sg_get_product_attributes() {
	$options    = array();
	$attributes = wc_get_attribute_taxonomies();
	if ( count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			$options[ 'pa_' . $attribute->attribute_name ] = $attribute->attribute_label;
		}
	}

	return $options;
}

/**
 * Prepare Size Guide post before single product.
 */
function commercekit_sg_prepare_active_post() {
	global $wpdb, $product, $cgkit_sg_post, $post;
	$options   = get_option( 'commercekit', array() );
	$shortcode = isset( $options['widget_pos_sizeguide'] ) && 1 === (int) $options['widget_pos_sizeguide'] ? true : false;
	$_product  = null;
	if ( $shortcode ) {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		if ( isset( $post->ID ) && $post->ID ) {
			$product_id = $post->ID;
			$_product   = wc_get_product( $product_id );
		} else {
			return;
		}
	} else {
		if ( ! $product ) {
			return;
		}
		$product_id = $product->get_id();
		$_product   = $product;
	}

	if ( ! $_product ) {
		return;
	}

	$categories = array();
	$terms      = get_the_terms( $product_id, 'product_cat' );
	if ( is_array( $terms ) && count( $terms ) ) {
		foreach ( $terms as $term ) {
			$categories[] = $term->term_id;
			$all_parents  = get_ancestors( $term->term_id, 'product_cat' );
			$categories   = array_merge( $categories, $all_parents );
		}
	}
	$categories = array_unique( $categories );

	$tags  = array();
	$terms = get_the_terms( $product_id, 'product_tag' );
	if ( is_array( $terms ) && count( $terms ) ) {
		foreach ( $terms as $term ) {
			$tags[] = $term->term_id;
		}
	}
	$tags = array_unique( $tags );

	$attr_terms = array();
	$attributes = $_product->get_attributes();
	if ( count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( $attribute->is_taxonomy() ) {
				$nterms = $attribute->get_terms();
				if ( count( $nterms ) ) {
					foreach ( $nterms as $nterm ) {
						$attr_terms[] = $nterm->term_id . ':' . $nterm->taxonomy;
					}
				}
			}
		}
	}

	$row        = null;
	$sg_where   = array();
	$sg_where[] = 'p.sg_prod = ' . intval( $product_id );
	if ( count( $categories ) ) {
		$sg_where[] = 'p.sg_cat IN (' . implode( ',', $categories ) . ')';
	}
	if ( count( $tags ) ) {
		$sg_where[] = 'p.sg_tag IN (' . implode( ',', $tags ) . ')';
	}
	if ( count( $attr_terms ) ) {
		$sg_where[] = 'p.sg_attr IN (\'' . implode( '\',\'', $attr_terms ) . '\')';
	}

	$md_sql = 'SELECT p.post_id FROM ' . $wpdb->prefix . 'commercekit_sg_post_meta AS p WHERE p.active = 1 AND ( ' . implode( ' OR ', $sg_where ) . ' ) ORDER BY p.ID DESC LIMIT 0, 1';
	$sgp_id = (int) $wpdb->get_var( $md_sql ); // phpcs:ignore
	if ( $sgp_id ) {
		$sg_sql = 'SELECT p.* FROM ' . $wpdb->prefix . 'posts AS p WHERE p.post_status = \'publish\' AND p.post_type = \'ckit_size_guide\' AND p.ID = ' . $sgp_id . ' ORDER BY p.ID DESC LIMIT 0, 1';
		$row    = $wpdb->get_row( $sg_sql ); // phpcs:ignore
	}

	if ( ! $row ) {
		$options = get_option( 'commercekit', array() );
		$def_sg  = isset( $options['default_size_guide'] ) && ! empty( $options['default_size_guide'] ) ? (int) $options['default_size_guide'] : 0;
		$sg_sql2 = 'SELECT p.* FROM ' . $wpdb->prefix . 'posts AS p WHERE p.post_status = \'publish\' AND p.post_type = \'ckit_size_guide\' AND p.ID = ' . $def_sg . ' ORDER BY p.ID DESC LIMIT 0, 1';
		$row     = $wpdb->get_row( $sg_sql2 ); // phpcs:ignore
	}

	if ( $row ) {
		$cgkit_sg_post = $row;
	}
}
$commercekit_options  = get_option( 'commercekit', array() );
$widget_pos_sizeguide = isset( $commercekit_options['widget_pos_sizeguide'] ) && 1 === (int) $commercekit_options['widget_pos_sizeguide'] ? true : false;
if ( $widget_pos_sizeguide ) {
	add_action( 'wp_head', 'commercekit_sg_prepare_active_post', 0 );
} else {
	add_action( 'woocommerce_before_single_product', 'commercekit_sg_prepare_active_post', 0 );
}

/**
 * Display on single product placeholder.
 */
function commercekit_sg_single_product_placeholder() {
	global $cgkit_sg_post;
	if ( ! isset( $cgkit_sg_post ) || ! $cgkit_sg_post ) {
		return '';
	}
	$row = $cgkit_sg_post;
	if ( ! isset( $row->ID ) || ! $row->ID ) {
		return '';
	}

	$options  = get_option( 'commercekit', array() );
	$sg_label = isset( $options['size_guide_label'] ) && ! empty( $options['size_guide_label'] ) ? commercekit_get_multilingual_string( $options['size_guide_label'] ) : commercekit_get_default_settings( 'size_guide_label' );
	$sg_icon  = isset( $options['size_guide_icon'] ) && 1 === (int) $options['size_guide_icon'] ? true : false;

	$shortcode = isset( $options['widget_pos_sizeguide'] ) && 1 === (int) $options['widget_pos_sizeguide'] ? true : false;

	$sg_icon_html = '<svg class="size_guide_default_icon" aria-hidden="true" role="presentation" viewBox="0 0 64 64"><defs></defs><path class="a" d="M22.39 33.53c-7.46 0-13.5-3.9-13.5-8.72s6-8.72 13.5-8.72 13.5 3.9 13.5 8.72a12 12 0 0 1-.22 1.73"></path><ellipse cx="22.39" cy="24.81" rx="3.28" ry="2.12"></ellipse><path class="a" d="M8.89 24.81V38.5c0 7.9 6.4 9.41 14.3 9.41h31.92V33.53H22.39M46.78 33.53v7.44M38.65 33.53v7.44M30.52 33.53v7.44M22.39 33.53v7.44"></path></svg>';
	if ( $sg_icon && isset( $options['size_guide_icon_html'] ) && ! empty( $options['size_guide_icon_html'] ) ) {
		$sg_icon_html = stripslashes_deep( $options['size_guide_icon_html'] );
	}

	$html = '<div class="commercekit-size-guide ' . ( true === $shortcode ? 'cgkit-size-guide-shortcode' : '' ) . '"><button data-trigger="sizeGuide" class="commercekit-sg-label" title="' . esc_attr( $sg_label ) . '" aria-label="' . esc_attr( $sg_label ) . '"><span class="commercekit-sg-icon">' . $sg_icon_html . '</span><span>' . esc_attr( $sg_label ) . '</span></button></div><div class="commercekit-sg-clr"></div>';
	if ( $shortcode ) {
		return $html;
	} else {
		echo $html; // phpcs:ignore
	}
}

/**
 * Size guide elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_sizeguide_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-sizeguide-elementor.php';
	$widgets_manager->register( new Commercekit_Sizeguide_Elementor() );
}

if ( $widget_pos_sizeguide ) {
	add_shortcode( 'commercekit_sizeguide', 'commercekit_sg_single_product_placeholder' );
	add_action( 'elementor/widgets/register', 'commercekit_sizeguide_elementor_widget' );
} else {
	add_action( 'woocommerce_single_product_summary', 'commercekit_sg_single_product_placeholder', 38 );
}

/**
 * Display on single product modal.
 */
function commercekit_sg_single_product_modal() {
	global $cgkit_sg_post;
	if ( ! isset( $cgkit_sg_post ) || ! $cgkit_sg_post ) {
		return;
	}
	$row = $cgkit_sg_post;
	if ( ! isset( $row->ID ) || ! $row->ID ) {
		return;
	}
	$options = get_option( 'commercekit', array() );
	if ( isset( $options['size_guide_mode'] ) && 2 === (int) $options['size_guide_mode'] ) {
		return;
	}
	$content  = apply_filters( 'cgkit_the_content_filter', $row->post_content, $row->ID );
	$content  = str_replace( ']]>', ']]&gt;', $content );
	$sg_title = apply_filters( 'the_title', $row->post_title, $row->ID );

	$shortcode = isset( $options['widget_pos_sizeguide'] ) && 1 === (int) $options['widget_pos_sizeguide'] ? true : false;

	?>
<dialog class="ckit-modal size-guide-modal <?php echo true === $shortcode ? 'size-guide-modal-shortcode' : ''; ?>" data-ckitmodal-id="sizeGuide">
	<div class="ckit-modal--container">
		<div class="ckit-modal-header">
			<h3><?php echo $sg_title; // phpcs:ignore ?></h3>
			<form method="dialog">
				<button aria-label="Close modal" class="ckit-modal--button_close" data-dismiss="modal">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
					</svg>                      
				</button>
			</form>
		</div>
		<div class="ckit-modal-content">
			<div class="ckit-modal-body">
				<?php echo $content; // phpcs:ignore ?>
			</div>
		</div>
	</div>
</dialog>
<script>
const ckitdialog = document.querySelector( 'dialog.ckit-modal' );
document.addEventListener( 'click', event => {
	const ckittrigger = event.target.dataset.trigger;
	if ( ckittrigger ) {
		const modalElement = document.querySelector( `[data-ckitmodal-id="${ckittrigger}"]` );
		if ( modalElement ) {
			modalElement.showModal();
		}
	}
} );

ckitdialog.onclick = event => {
	if (isOuterClick(event)) {
		ckitdialog.close()
	}
}

isOuterClick = ({ currentTarget, clientX, clientY }, [ border ] = currentTarget.getClientRects()) => (
	clientX < border.left ||
	clientX > border.right ||
	clientY < border.top ||
	clientY > border.bottom
)

Array.from( document.querySelectorAll( 'dialog.ckit-modal' ) ).forEach( ckitdialog => {
	ckitdialog.addEventListener( 'click', function( event ) {
		if ( event.target.closest( '.ckit-modal-close' ) ) {
			event.preventDefault();
			ckitdialog.close();
		}
	} );
} );
</script>
	<?php
	commercekit_sg_styles();
}
if ( $widget_pos_sizeguide ) {
	add_action( 'wp_footer', 'commercekit_sg_single_product_modal', 0 );
} else {
	add_action( 'woocommerce_single_product_summary', 'commercekit_sg_single_product_modal', 81 );
}

/**
 * Display Size Guide tab
 *
 * @param mixed $tabs array of tabs.
 */
function commercekit_sg_woocommerce_tab( $tabs ) {
	global $cgkit_sg_post;
	if ( ! isset( $cgkit_sg_post ) || ! $cgkit_sg_post ) {
		return $tabs;
	}
	$row = $cgkit_sg_post;
	if ( ! isset( $row->ID ) || ! $row->ID ) {
		return $tabs;
	}
	$options = get_option( 'commercekit', array() );
	if ( ! isset( $options['size_guide_mode'] ) || 2 !== (int) $options['size_guide_mode'] ) {
		return $tabs;
	}

	$sg_label = isset( $options['size_guide_label'] ) && ! empty( $options['size_guide_label'] ) ? commercekit_get_multilingual_string( $options['size_guide_label'] ) : commercekit_get_default_settings( 'size_guide_label' );

	$tabs['commercekit-sg']['callback'] = 'commercekit_sg_woocommerce_tab_callback';
	$tabs['commercekit-sg']['title']    = $sg_label;

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'commercekit_sg_woocommerce_tab', 90 );

/**
 * Display Size Guide tab callback
 */
function commercekit_sg_woocommerce_tab_callback() {
	global $cgkit_sg_post;
	if ( ! isset( $cgkit_sg_post ) || ! $cgkit_sg_post ) {
		return;
	}
	$row = $cgkit_sg_post;
	if ( ! isset( $row->ID ) || ! $row->ID ) {
		return;
	}
	$content  = apply_filters( 'cgkit_the_content_filter', $row->post_content, $row->ID );
	$content  = str_replace( ']]>', ']]&gt;', $content );
	$sg_title = apply_filters( 'the_title', $row->post_title, $row->ID );
	?>
<div class="cgkit-sg-tab-wrap">
	<h2><?php echo $sg_title; // phpcs:ignore ?></h2>
	<div class="cgkit-sg-tab-content">
		<?php echo $content; // phpcs:ignore ?>
	</div>
</div>
<script>
document.addEventListener( 'click', function( e ) {
	var elemnt = e.target;
	var parent = elemnt.closest( '.commercekit-sg-label' );
	if ( elemnt.classList.contains( 'commercekit-sg-label' ) || parent ) {
		e.stopPropagation();
		e.preventDefault();
		var cgkit_atc_tab = document.querySelector( '#cgkit-tab-commercekit-sg-title > a' );
		if( cgkit_atc_tab ){
			cgkit_atc_tab.click();
		} else {
			var wctab = document.querySelector( '#tab-title-commercekit-sg > a' );
			if( wctab ){
				wctab.click();
				window.dispatchEvent(new Event('resize'));
				var offset_top = 0;
				if( typeof cgkit_get_element_offset_top === 'function' ){
					offset_top = cgkit_get_element_offset_top(wctab);
				} else {
					var elem = wctab;
					while ( elem ) {
						offset_top += elem.offsetTop;
						elem = elem.offsetParent;
					}
					var cgkit_sticky_nav = document.querySelector( 'body.sticky-d .col-full-nav' );
					var cgkit_body = document.querySelector( 'body' );
					if ( cgkit_sticky_nav && ! cgkit_body.classList.contains('ckit_stickyatc_active') ) {
						offset_top = offset_top - cgkit_sticky_nav.clientHeight;
					}
				}
				window.scroll( {
					behavior: 'smooth',
					left: 0,
					top: offset_top,
				} );
			}
		}
		return;
	}
} );
</script>
	<?php
	commercekit_sg_styles();
}

/**
 * Size guide styles.
 */
function commercekit_sg_styles() {
	?>
<style type="text/css">
body.cgkit-size-guide-active { overflow: hidden; }
.commercekit-size-guide{margin-bottom: 15px; display: block;line-height: 1.5}
.commercekit-size-guide.cgkit-size-guide-shortcode{margin:0}
.commercekit-size-guide button { background:transparent;cursor:pointer;color:#111;}
.commercekit-size-guide .commercekit-sg-label { position: relative; padding-left: 28px; display: inline-block;}
.commercekit-size-guide .commercekit-sg-label span { pointer-events:none; }
.commercekit-size-guide .commercekit-sg-label svg {width:22px;height:22px; position: absolute; left: 0px; margin-top: -2px;}
.commercekit-size-guide svg path {stroke: #000; fill: none;}
.commercekit-size-guide svg.size_guide_default_icon path {stroke-width: 2px;}
.commercekit-sg-clr{clear:both;display:block;width:100%;height:0px;}
.size-guide-modal button.close-button { top: 19px; right: 36px; }
.size-guide-modal .ckit-modal-content{overflow-y:auto; padding: 20px 40px 20px 40px;}
.size-guide-modal .ckit-modal-header{display: flex; align-items: center; justify-content:space-between;height: 60px; padding:0px 40px; border-bottom: 1px solid #e2e2e2;position: sticky;top:0px;background-color: #fff;}
.size-guide-modal .ckit-modal-header h3{font-size: clamp(1.125rem, 0.8709rem + 0.813vw, 1.375rem);padding:0px;margin:0px;letter-spacing: 0; text-align: left; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;}
.size-guide-modal .ckit-modal-header form {margin: 0}
.size-guide-modal .ckit-modal-header .close-button{width:26px;height:26px;}
.size-guide-modal .ckit-modal-body{padding:0px;max-height:100%;}
.size-guide-modal .ckit-modal-content { background-color: transparent; border-radius: 0; max-height: calc(100% - 60px); }
.size-guide-modal { padding: 0 }
.ckit-modal .ckit-modal--button_close {display: flex;width: 26px;height: 26px;background: transparent;padding: 0;cursor: pointer;}
.ckit-modal .ckit-modal--button_close svg {width: 26px;height: 26px;stroke: #111;}
@media (min-width: 768px) {
	.size-guide-modal .ckit-modal-body > .wp-block-columns {margin-top: 20px;}
}
.single-ckit_size_guide .entry-header .posted-on {display: none;}
.single-ckit_size_guide .entry-content > .wp-block-columns {
	margin-top: 30px;
}
.size-guide-modal p,
.single-ckit_size_guide .entry-content p {
	font-size: 15px;
	margin-block-start: 0.5rem;
}
.size-guide-modal table,
.single-ckit_size_guide .entry-content table,
.commercekit-Tabs-panel--commercekit-sg table {
	font-size: 14px; margin: 2.5em 0 0 0;
}
.single-ckit_size_guide .entry-content table thead,
.commercekit-Tabs-panel--commercekit-sg table thead {
	border: none;
}
.size-guide-modal table th,
.single-ckit_size_guide .entry-content table th,
.commercekit-Tabs-panel--commercekit-sg table th {
	background: #111; color: #fff;
}
.size-guide-modal table th,
.size-guide-modal table td,
.single-ckit_size_guide .entry-content table th,
.single-ckit_size_guide .entry-content table td,
.commercekit-Tabs-panel--commercekit-sg table th,
.commercekit-Tabs-panel--commercekit-sg table td {
	padding: 0.8em 1.41575em;
	border: none;
}
.size-guide-modal table td,
.single-ckit_size_guide .entry-content table td,
.commercekit-Tabs-panel--commercekit-sg table td {
	background: #f8f8f8;
}
.size-guide-modal table tbody tr:nth-child(2n) td,
.single-ckit_size_guide .entry-content table tbody tr:nth-child(2n) td,
.commercekit-Tabs-panel--commercekit-sg table tbody tr:nth-child(2n) td {
	background: 0 0;
}
.commercekit-Tabs-panel--commercekit-sg .wp-block-table td, .commercekit-Tabs-panel--commercekit-sg .wp-block-table th {
	border: none;
}
@media (max-width: 767px) {
	.size-guide-modal .modal-header, .size-guide-modal .ckit-modal-content, .size-guide-modal .ckit-modal-header {padding-left: 20px; padding-right: 20px;}
	.size-guide-modal table, .single-ckit_size_guide .entry-content table, .commercekit-Tabs-panel--commercekit-sg table { font-size: 13px; }
}
dialog.ckit-modal::backdrop {
	background: rgba(0, 0, 0, 0.6);
	animation: 0.2s ckitmodal-fadein;
}
body:has(dialog.ckit-modal[open]) {
	overflow: hidden;
}
dialog.ckit-modal {
	display: flex;
	flex-direction: column;
	border: none;
	margin: 0;
	margin-left: auto;
	padding: 0;
	background-color: transparent;
	overflow: visible;
	max-height: 100%;
	height: 100%;
	max-width: 100%;
	width: 100%;
}
@media (min-width: 768px) {
	dialog.ckit-modal {
		width: 850px;
	}
}
dialog.ckit-modal .ckit-modal--container {
	background-color: #fff;
	overflow-x: hidden;
	overflow-y: auto;
	height: 100%;
}
dialog.ckit-modal:not([open]) {
	pointer-events: none;
	opacity: 0;
	display: none;
}
/* -- Animation -- */
dialog.ckit-modal[open] {
	animation: ckitmodal-fadein-right ease 0.35s;
}
@keyframes ckitmodal-fadein-right {
	0% {
		margin-right: -850px;
	}
	100% {
		margin-right: 0;
	}
}
@keyframes ckitmodal-fadein-left {
	0% {
		margin-left: -850px;
	}
	100% {
		margin-left: 0;
	}
}
@keyframes ckitmodal-fadein {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}
/* -- RTL -- */
.rtl dialog.ckit-modal {
	margin-right: auto;
	margin-left: 0;
}
.rtl dialog.ckit-modal[open] {
	animation: ckitmodal-fadein-left ease 0.35s;
}
.rtl .commercekit-size-guide .commercekit-sg-label {
	padding-right: 28px;
	padding-left: 0;
}
.rtl .commercekit-size-guide .commercekit-sg-label svg {
	right: 0px;
	left: auto;
}
.rtl .size-guide-modal .modal-header h3 {
	text-align: right;
	padding-left: 50px;
	padding-right: 0;
}
/* -- Shoptimizer -- */
.theme-shoptimizer .ckit-modal .ckit-modal--button_close:focus {outline: none;}
.theme-shoptimizer.keyboard-active .ckit-modal .ckit-modal--button_close:focus-visible {outline: 0.2rem solid #2491ff;outline-offset: 0;border-color: transparent;box-shadow: none;}
.theme-shoptimizer .commercekit-size-guide button {font-size:13px; font-weight: 600;}
.theme-shoptimizer .commercekit-size-guide button:hover span {text-decoration: underline;text-decoration-thickness: .5px;text-underline-offset: .18em;}
</style>
	<?php
}

/**
 * Custom the_content filter.
 *
 * @param string $content post content.
 * @param string $post_id post ID.
 */
function commercekit_sg_the_content_filter( $content, $post_id ) {
	$is_builder = false;
	if ( class_exists( 'Elementor\Plugin' ) ) {
		$document = Elementor\Plugin::instance()->documents->get( $post_id );
		if ( $document && $document->is_built_with_elementor() ) {
			$content    = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $post_id );
			$is_builder = true;
		}
	}
	if ( ! $is_builder ) {
		$content = function_exists( 'capital_P_dangit' ) ? capital_P_dangit( $content ) : $content;
		$content = function_exists( 'do_blocks' ) ? do_blocks( $content ) : $content;
		$content = function_exists( 'wptexturize' ) ? wptexturize( $content ) : $content;
		$content = function_exists( 'convert_smilies' ) ? convert_smilies( $content ) : $content;
		$content = function_exists( 'wpautop' ) ? wpautop( $content ) : $content;
		$content = function_exists( 'shortcode_unautop' ) ? shortcode_unautop( $content ) : $content;
		$content = function_exists( 'prepend_attachment' ) ? prepend_attachment( $content ) : $content;
		$content = function_exists( 'wp_filter_content_tags' ) ? wp_filter_content_tags( $content ) : $content;
		$content = function_exists( 'wp_replace_insecure_home_url' ) ? wp_replace_insecure_home_url( $content ) : $content;
		$content = function_exists( 'do_shortcode' ) ? do_shortcode( $content ) : $content;

		if ( class_exists( 'WP_Embed' ) ) {
			$embed   = new WP_Embed();
			$content = method_exists( $embed, 'run_shortcode' ) ? $embed->run_shortcode( $content ) : $content;
			$content = method_exists( $embed, 'autoembed' ) ? $embed->autoembed( $content ) : $content;
		}
	}

	return $content;
}
add_filter( 'cgkit_the_content_filter', 'commercekit_sg_the_content_filter', 10, 2 );

/**
 * Single body class.
 *
 * @param string $classes body classes.
 */
function commercekit_sg_single_body_class( $classes ) {
	if ( is_single() && 'ckit_size_guide' === get_post_type() ) {
		$classes[] = 'cgkit-size-guide';
	}

	return $classes;
}
add_filter( 'body_class', 'commercekit_sg_single_body_class' );

/**
 * Footer styles.
 */
function commercekit_sg_footer_styles() {
	if ( is_single() && 'ckit_size_guide' === get_post_type() ) {
		commercekit_sg_styles();
	}
}
add_action( 'wp_footer', 'commercekit_sg_footer_styles' );

/**
 * Get Size Guide post meta from custom table
 *
 * @param string $post_id post ID.
 * @param string $key     meta key.
 */
function commercekit_sg_get_post_meta( $post_id, $key = '' ) {
	global $wpdb;
	$sg_sql = 'SELECT p.* FROM ' . $wpdb->prefix . 'commercekit_sg_post_meta AS p WHERE p.post_id = ' . intval( $post_id ) . ' ORDER BY p.id ASC';
	$rows   = $wpdb->get_results( $sg_sql, ARRAY_A ); // phpcs:ignore
	$values = array();
	$mkeys  = array( 'sg_prod', 'sg_cat', 'sg_tag', 'sg_attr' );
	if ( is_array( $rows ) && count( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( 'all' === $key || empty( $key ) ) {
				foreach ( $mkeys as $mkey ) {
					if ( isset( $row[ $mkey ] ) && ! empty( $row[ $mkey ] ) ) {
						$values[ $mkey ][] = $row[ $mkey ];
					}
				}
			} elseif ( isset( $row[ $key ] ) && ! empty( $row[ $key ] ) ) {
				$values[] = $row[ $key ];
			}
		}
	}

	return $values;
}

/**
 * Update Size Guide post meta in custom table
 *
 * @param string $post_id   post ID.
 * @param string $active    post status.
 * @param string $meta_data meta data.
 */
function commercekit_sg_update_post_meta( $post_id, $active, $meta_data = array() ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'commercekit_sg_post_meta';
	$products   = isset( $meta_data['sg_prod'] ) ? (array) $meta_data['sg_prod'] : array();
	$categories = isset( $meta_data['sg_cat'] ) ? (array) $meta_data['sg_cat'] : array();
	$tags       = isset( $meta_data['sg_tag'] ) ? (array) $meta_data['sg_tag'] : array();
	$attributes = isset( $meta_data['sg_attr'] ) ? (array) $meta_data['sg_attr'] : array();
	$total_rows = max( count( $products ), count( $categories ), count( $tags ), count( $attributes ) );

	$sq_sql = 'DELETE FROM ' . $table_name . ' WHERE post_id = ' . intval( $post_id );
	$wpdb->query( $sq_sql ); // phpcs:ignore

	if ( $total_rows ) {
		for ( $i = 0; $i < $total_rows; $i++ ) {
			$data   = array(
				'post_id' => $post_id,
				'active'  => $active,
				'sg_prod' => isset( $products[ $i ] ) ? intval( $products[ $i ] ) : 0,
				'sg_cat'  => isset( $categories[ $i ] ) ? intval( $categories[ $i ] ) : 0,
				'sg_tag'  => isset( $tags[ $i ] ) ? intval( $tags[ $i ] ) : 0,
				'sg_attr' => isset( $attributes[ $i ] ) ? $attributes[ $i ] : '',
			);
			$format = array( '%d', '%d', '%d', '%d', '%d', '%s' );
			$wpdb->insert( $table_name, $data, $format ); // db call ok; no-cache ok.
		}
	}
}

/**
 * Delete Size Guide post meta from custom table
 *
 * @param string $post_id post ID.
 * @param string $post    post object.
 */
function commercekit_sg_delete_post_meta( $post_id, $post ) {
	global $wpdb;
	if ( 'ckit_size_guide' !== $post->post_type ) {
		return;
	}
	$table  = $wpdb->prefix . 'commercekit_sg_post_meta';
	$sq_sql = 'DELETE FROM ' . $table . ' WHERE post_id = ' . intval( $post_id );
	$wpdb->query( $sq_sql ); // phpcs:ignore
}
add_action( 'delete_post', 'commercekit_sg_delete_post_meta', 20, 2 );

/**
 * Size guide WPML make duplicate
 *
 * @param string $master_post_id  master post id.
 * @param string $target_lang     target language.
 * @param string $post_array      post array.
 * @param string $target_post_id  target post id.
 */
function commercekit_sg_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id ) {
	if ( 'ckit_size_guide' !== get_post_type( $master_post_id ) ) {
		return;
	}
	$meta_data = commercekit_sg_get_post_meta( $master_post_id );

	$products  = isset( $meta_data['sg_prod'] ) ? (array) $meta_data['sg_prod'] : array();
	$products  = array_map( 'intval', $products );
	$nproducts = array();
	if ( is_array( $products ) && count( $products ) ) {
		foreach ( $products as $product_id ) {
			$nproducts[] = apply_filters( 'wpml_object_id', $product_id, 'product', true, $target_lang );
		}
	}

	$categories  = isset( $meta_data['sg_cat'] ) ? (array) $meta_data['sg_cat'] : array();
	$categories  = array_map( 'intval', $categories );
	$ncategories = array();
	if ( is_array( $categories ) && count( $categories ) ) {
		foreach ( $categories as $category ) {
			$ncategories[] = apply_filters( 'wpml_object_id', $category, 'product_cat', true, $target_lang );
		}
	}

	$tags  = isset( $meta_data['sg_tag'] ) ? (array) $meta_data['sg_tag'] : array();
	$tags  = array_map( 'intval', $tags );
	$ntags = array();
	if ( is_array( $tags ) && count( $tags ) ) {
		foreach ( $tags as $tag ) {
			$ntags[] = apply_filters( 'wpml_object_id', $tag, 'product_tag', true, $target_lang );
		}
	}

	$attributes  = isset( $meta_data['sg_attr'] ) ? (array) $meta_data['sg_attr'] : array();
	$nattributes = array();
	if ( is_array( $attributes ) && count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			$temp     = explode( ':', $attribute );
			$attr_id  = isset( $temp[0] ) && ! empty( $temp[0] ) ? $temp[0] : 0;
			$taxonomy = isset( $temp[1] ) && ! empty( $temp[1] ) ? $temp[1] : '';
			if ( $attr_id && $taxonomy ) {
				$nattr_id      = apply_filters( 'wpml_object_id', $attr_id, $taxonomy, true, $target_lang );
				$nattributes[] = $nattr_id . ':' . $taxonomy;
			} else {
				$nattributes[] = $attribute;
			}
		}
	}

	$active = 'publish' === get_post_status( $target_post_id ) ? 1 : 0;
	$mdata  = array(
		'sg_cat'  => $ncategories,
		'sg_prod' => $nproducts,
		'sg_tag'  => $ntags,
		'sg_attr' => $nattributes,
	);
	commercekit_sg_update_post_meta( $target_post_id, $active, $mdata );
}
add_action( 'icl_make_duplicate', 'commercekit_sg_wpml_make_duplicate', 10, 4 );

/**
 * Size guide Polylang Pro copy metas
 *
 * @param string $meta_array     array of metadata.
 * @param string $sync           sync or copy.
 * @param string $master_post_id master post ID.
 * @param string $target_post_id target post id.
 * @param string $target_lang    target language code.
 */
function commercekit_sg_pll_copy_post_metas( $meta_array, $sync, $master_post_id, $target_post_id, $target_lang ) {
	if ( 'ckit_size_guide' !== get_post_type( $master_post_id ) ) {
		return $meta_array;
	}
	$meta_data = commercekit_sg_get_post_meta( $master_post_id );

	$products  = isset( $meta_data['sg_prod'] ) ? (array) $meta_data['sg_prod'] : array();
	$products  = array_map( 'intval', $products );
	$nproducts = array();
	if ( is_array( $products ) && count( $products ) ) {
		foreach ( $products as $product_id ) {
			$nproducts[] = commercekit_pll_get_post( $product_id, $target_lang );
		}
	}

	$categories  = isset( $meta_data['sg_cat'] ) ? (array) $meta_data['sg_cat'] : array();
	$categories  = array_map( 'intval', $categories );
	$ncategories = array();
	if ( is_array( $categories ) && count( $categories ) ) {
		foreach ( $categories as $category ) {
			$ncategories[] = commercekit_pll_get_term( $category, $target_lang );
		}
	}

	$tags  = isset( $meta_data['sg_tag'] ) ? (array) $meta_data['sg_tag'] : array();
	$tags  = array_map( 'intval', $tags );
	$ntags = array();
	if ( is_array( $tags ) && count( $tags ) ) {
		foreach ( $tags as $tag ) {
			$ntags[] = commercekit_pll_get_term( $tag, $target_lang );
		}
	}

	$attributes  = isset( $meta_data['sg_attr'] ) ? (array) $meta_data['sg_attr'] : array();
	$nattributes = array();
	if ( is_array( $attributes ) && count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			$temp     = explode( ':', $attribute );
			$attr_id  = isset( $temp[0] ) && ! empty( $temp[0] ) ? $temp[0] : 0;
			$taxonomy = isset( $temp[1] ) && ! empty( $temp[1] ) ? $temp[1] : '';
			if ( $attr_id && $taxonomy ) {
				$nattr_id      = commercekit_pll_get_term( $attr_id, $target_lang );
				$nattributes[] = $nattr_id . ':' . $taxonomy;
			} else {
				$nattributes[] = $attribute;
			}
		}
	}

	$active = 'publish' === get_post_status( $target_post_id ) ? 1 : 0;
	$mdata  = array(
		'sg_cat'  => $ncategories,
		'sg_prod' => $nproducts,
		'sg_tag'  => $ntags,
		'sg_attr' => $nattributes,
	);
	commercekit_sg_update_post_meta( $target_post_id, $active, $mdata );

	$meta_keys = array( 'commercekit_sg_prod', 'commercekit_sg_cat', 'commercekit_sg_tag', 'commercekit_sg_attr' );
	foreach ( $meta_array as $key => $meta_key ) {
		if ( in_array( $meta_key, $meta_keys, true ) ) {
			unset( $meta_array[ $key ] );
		}
	}

	return $meta_array;
}
add_filter( 'pll_copy_post_metas', 'commercekit_sg_pll_copy_post_metas', 10, 5 );

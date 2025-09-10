<?php
/**
 * Main functions for rendering gallery html and tweaks to PDP's for compatibility with other plugins
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Attributes_Gallery
 * @since    1.0.0
 */

/**
 * Get html for the main PDP attributes gallery.
 *
 * Hooks: woocommerce_gallery_thumbnail_size, woocommerce_gallery_image_size and woocommerce_gallery_full_size accept name based image sizes, or an array of width/height values.
 *
 * @since 1.0.0
 * @param int    $attachment_id Attachment ID.
 * @param bool   $main_image Is this the main image or a thumbnail?.
 * @param string $li_class   list class.
 * @param bool   $apply_filter apply filter or not.
 * @return string
 */
function commercegurus_get_main_attributes_gallery_image_html( $attachment_id, $main_image = false, $li_class = '', $apply_filter = false ) {
	$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
	$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
	$image_size        = apply_filters( 'commercegurus_woocommerce_gallery_image_size', 'woocommerce_single' );
	$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
	$thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
	$thumbnail_srcset  = wp_get_attachment_image_srcset( $attachment_id, $thumbnail_size );
	$thumbnail_sizes   = wp_get_attachment_image_sizes( $attachment_id, $thumbnail_size );
	$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
	if ( false === $full_src ) {
		return '';
	}
	$full_srcset = wp_get_attachment_image_srcset( $attachment_id, $full_size );
	$alt_text    = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
	$image       = wp_get_attachment_image(
		$attachment_id,
		$image_size,
		false,
		apply_filters(
			'woocommerce_gallery_image_html_attachment_image_params',
			array(
				'title'         => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'data-caption'  => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'class'         => 'wp-post-image' . ( $apply_filter ? ' skip-lazy' : '' ),
				'fetchpriority' => esc_attr( $main_image ? 'high' : 'low' ),
			),
			$attachment_id,
			$image_size,
			$main_image
		)
	);
	if ( $apply_filter ) {
		$image = '<div data-thumb="' . esc_url( $thumbnail_src[0] ) . '" data-thumb-alt="' . esc_attr( $alt_text ) . '" data-thumb-srcset="' . esc_attr( $thumbnail_srcset ) . '"  data-thumb-sizes="' . esc_attr( $thumbnail_sizes ) . '" class="woocommerce-product-gallery__image">' . $image . '</div>';
		$image = apply_filters( 'woocommerce_single_product_image_thumbnail_html', $image, $attachment_id );
	}
	return '<li class="swiper-slide ' . esc_attr( $li_class ) . '" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
	  <a class="swiper-slide-imglink" data-e-disable-page-transition="true" title="' . esc_html__( 'click to zoom-in', 'commercegurus-commercekit' ) . '" href="' . esc_url( $full_src[0] ) . '" itemprop="contentUrl" data-size="' . esc_attr( $full_src[1] ) . 'x' . esc_attr( $full_src[2] ) . '">
		' . $image . '
	  </a>
	</li>';
}

/**
 * Get lazy html for the main PDP attributes gallery. Used for all images after the first one.
 *
 * Hooks: woocommerce_gallery_thumbnail_size, woocommerce_gallery_image_size and woocommerce_gallery_full_size accept name based image sizes, or an array of width/height values.
 *
 * @since 1.0.0
 * @param int    $attachment_id Attachment ID.
 * @param bool   $main_image Is this the main image or a thumbnail?.
 * @param string $li_class   list class.
 * @return string
 */
function commercegurus_get_main_attributes_gallery_image_lazy_html( $attachment_id, $main_image = false, $li_class = '' ) {
	$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
	$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
	$image_size        = apply_filters( 'commercegurus_woocommerce_gallery_image_size', 'woocommerce_single' );
	$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
	$thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
	$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
	if ( false === $full_src ) {
		return '';
	}
	$full_srcset = wp_get_attachment_image_srcset( $attachment_id, $full_size );
	$attachment  = get_post( $attachment_id );
	$alt_text    = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
	$img_title   = isset( $attachment->post_title ) ? trim( wp_strip_all_tags( $attachment->post_title ) ) : '';
	$img_caption = isset( $attachment->post_excerpt ) ? trim( wp_strip_all_tags( $attachment->post_excerpt ) ) : '';

	$placeholder = CKIT_URI . 'assets/images/spacer.png';

	return '<li class="swiper-slide ' . esc_attr( $li_class ) . '" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
	  <a class="swiper-slide-imglink" data-e-disable-page-transition="true" title="' . esc_html__( 'click to zoom-in', 'commercegurus-commercekit' ) . '" href="' . esc_url( $full_src[0] ) . '" itemprop="contentUrl" data-size="' . esc_attr( $full_src[1] ) . 'x' . esc_attr( $full_src[2] ) . '">
		<img width="' . esc_attr( $full_src[1] ) . '" height="' . esc_attr( $full_src[2] ) . '" src="' . esc_url( $full_src[0] ) . '" data-src="' . esc_url( $full_src[0] ) . '" srcset="' . $full_srcset . '" data-srcset="' . $full_srcset . '" sizes="(max-width: 360px) 330px, (max-width: 800px) 100vw, 800px" alt="' . $alt_text . '" title="' . $img_title . '" data-caption="' . $img_caption . '" itemprop="thumbnail" class="pdp-img wp-post-image" loading="lazy"/>
	  </a>
	</li>';
}

/**
 * Get html for the small thumbnail gallery under the main PDP gallery.
 *
 * Hooks: woocommerce_gallery_thumbnail_size, woocommerce_gallery_image_size and woocommerce_gallery_full_size accept name based image sizes, or an array of width/height values.
 *
 * @since 1.0.0
 * @param int  $attachment_id Attachment ID.
 * @param bool $main_image Is this the main image or a thumbnail?.
 * @param int  $index slider index.
 * @param bool $css_class Is CSS class.
 * @return string
 */
function commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, $main_image = false, $index = 0, $css_class = '' ) {
	$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
	$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
	$image_size        = apply_filters( 'commercegurus_woocommerce_gallery_thumbnail_size', 'woocommerce_gallery_thumbnail' );
	$thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
	if ( false === $thumbnail_src ) {
		return '';
	}

	$image = wp_get_attachment_image(
		$attachment_id,
		$image_size,
		false,
		apply_filters(
			'woocommerce_gallery_image_html_attachment_image_params',
			array(
				'title'        => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'data-caption' => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'class'        => 'wp-post-image',
			),
			$attachment_id,
			$image_size,
			$main_image
		)
	);

	return '	<li class="swiper-slide ' . $css_class . '" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject" data-variation-id="' . esc_attr( $attachment_id ) . '" data-index="' . esc_attr( $index ) . '">' . ( 'pdp-video' === $css_class ? '<div class="cgkit-play"><svg class="play" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><path d="m20 33 12-9-12-9v18zm4-29C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm0 36c-8.82 0-16-7.18-16-16S15.18 8 24 8s16 7.18 16 16-7.18 16-16 16z" fill="#ffffff" class="fill-000000"></path></svg></div>' : '' ) . '
		' . $image . '
	</li>
';
}

/**
 * Load selected attributes
 *
 * @param string $product_id admin product ID.
 */
function commercegurus_attributes_load_attributes( $product_id ) {
	$product_id = intval( $product_id );
	$product    = wc_get_product_object( 'variable', $product_id );
	$attributes = array();

	if ( $product ) {
		foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}
			$attr_slug = sanitize_title( $attribute->get_name() );
			if ( $attr_slug ) {
				if ( $attribute->is_taxonomy() ) {
					$tax = $attribute->get_taxonomy_object();

					$attributes[ $attr_slug ] = array(
						'id'    => $attribute->get_id(),
						'slug'  => $attr_slug,
						'name'  => $tax ? $tax->attribute_label : '',
						'terms' => $attribute->get_terms(),
						'taxo'  => 1,
					);
				} else {
					$_options  = $attribute->get_options();
					$tax_terms = array();
					if ( count( $_options ) ) {
						foreach ( $_options as $_option ) {
							$tax_terms[] = (object) array(
								'name'    => $_option,
								'slug'    => sanitize_title( $_option ),
								'term_id' => $_option,
							);
						}
					}
					$attributes[ $attr_slug ] = array(
						'id'    => $attr_slug,
						'slug'  => $attr_slug,
						'name'  => $attribute->get_name(),
						'terms' => $tax_terms,
						'taxo'  => 0,
					);
				}
			}
		}
	}

	return $attributes;
}

/**
 * Get product gallery slug
 *
 * @param string $slug          gallery slug.
 * @param string $gallery_slugs gallery slugs.
 */
function commercegurus_get_product_gallery_slug( $slug, $gallery_slugs ) {
	if ( false !== stripos( $slug, '_cgkit_' ) ) {
		$parts = explode( '_cgkit_', $slug );
		$slugs = array();
		if ( count( $parts ) ) {
			foreach ( $parts as $part ) {
				$slugs[ array_search( $part, $gallery_slugs ) ] = $part; // phpcs:ignore
			}
		}
		ksort( $slugs );
		return implode( '_cgkit_', $slugs );
	} else {
		return $slug;
	}
}

/**
 * Get html for video.
 *
 * @param string $video_url video URL.
 * @param string $main_video is main video.
 * @param string $autoplay is auto play video.
 * @param string $attachment_id is default image.
 * @param string $list_class list css class.
 * @return string
 */
function commercegurus_get_product_gallery_video_html( $video_url, $main_video = false, $autoplay = false, $attachment_id = 0, $list_class = '' ) {
	$full_size = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
	$full_src  = wp_get_attachment_image_src( $attachment_id, $full_size );
	if ( false === $full_src ) {
		return '';
	}
	if ( ! $full_src ) {
		$full_src    = array();
		$full_src[0] = '';
		$full_src[1] = 0;
		$full_src[2] = 0;
	}
	$full_srcset = wp_get_attachment_image_srcset( $attachment_id, $full_size );
	$alt_text    = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );

	$tmp_url = explode( '::', $video_url );
	if ( isset( $tmp_url[0] ) ) {
		$video_url = $tmp_url[0];
	}
	if ( isset( $tmp_url[1] ) ) {
		$autoplay = 1 === (int) $tmp_url[1] ? true : false;
	}
	if ( empty( $video_url ) ) {
		return '';
	}

	$placeholder = CKIT_URI . 'assets/images/spacer.png';
	$embed_video = '';
	$embed_html  = '';
	$embed_url   = '';
	$video_slash = '';
	$video_bg    = '';
	$video_allow = '';
	$video_id    = array();
	if ( false !== stripos( $video_url, 'vimeo.com/' ) ) {
		$video_id  = explode( 'vimeo.com/', $video_url );
		$embed_url = 'https://player.vimeo.com/video/';
		$video_bg  = $autoplay ? '&background=1' : '';

		$video_allow = $autoplay ? 'autoplay; fullscreen; picture-in-picture' : 'fullscreen; picture-in-picture';
	} elseif ( false !== stripos( $video_url, 'youtube.com/' ) ) {
		if ( false !== stripos( $video_url, '/shorts/' ) ) {
			$video_id = explode( '/shorts/', $video_url );
		} else {
			$video_id = explode( 'v=', $video_url );
		}
		$embed_url = 'https://www.youtube.com/embed/';
	} elseif ( false !== stripos( $video_url, 'youtu.be/' ) ) {
		if ( false !== stripos( $video_url, '/shorts/' ) ) {
			$video_id = explode( '/shorts/', $video_url );
		} else {
			$video_id = explode( 'youtu.be/', $video_url );
		}
		$embed_url = 'https://www.youtube.com/embed/';
	}
	if ( isset( $video_id[1] ) && ! empty( $video_id[1] ) ) {
		$video_id = $video_id[1];
		if ( false !== strpos( $video_id, '&' ) ) {
			$video_id = explode( '&', $video_id );
			$video_id = isset( $video_id[0] ) ? $video_id[0] : '';
		}
		if ( false !== strpos( $video_id, '/' ) ) {
			$video_tmp   = explode( '/', $video_id );
			$video_id    = isset( $video_tmp[0] ) ? $video_tmp[0] : '';
			$video_slash = isset( $video_tmp[1] ) ? '&h=' . $video_tmp[1] : '';
		}
		if ( $video_id ) {
			$embed_url   = $embed_url . $video_id . '?rel=0' . $video_slash . ( $autoplay ? '&autoplay=1&mute=1&loop=1' . $video_bg : '' );
			$embed_video = '<div class="cgkit-iframe-wrap cgkit-iframe-embed ' . ( $autoplay ? 'cgkit-iframe-autoplay' : '' ) . '"><iframe src="' . $embed_url . '" data-src="' . $embed_url . '" itemprop="video" class="pdp-video" frameborder="0" width="560" height="340" allow="' . $video_allow . '" loading="lazy" allowfullscreen></iframe></div>';
			$embed_html  = '<div class="cgkit-iframe-wrap cgkit-iframe-lightbox"><iframe src="' . $embed_url . '" itemprop="video" class="pdp-video" frameborder="0" width="560" height="340" allow="' . $video_allow . '" allowfullscreen></iframe></div>';
		}
	} elseif ( false !== stripos( $video_url, 'wistia.com/' ) || false !== stripos( $video_url, 'wistia.net/' ) ) {
		$video_url = str_ireplace( array( '/medias/iframe/', '/embed/iframe/', '/embed/medias/' ), array( '/medias/', '/medias/', '/medias/' ), $video_url );
		$video_id  = explode( '/medias/', $video_url );
		if ( ! isset( $video_id[1] ) || empty( $video_id[1] ) ) {
			return '';
		}
		$video_url = 'https://fast.wistia.com/embed/iframe/' . $video_id[1];
		if ( $autoplay ) {
			if ( false !== strpos( $video_url, '?' ) ) {
				$video_url = $video_url . '&autoPlay=true&wvideo=hashedid';
			} else {
				$video_url = $video_url . '?autoPlay=true&wvideo=hashedid';
			}
		}
		$video_allow = $autoplay ? 'autoplay; fullscreen;' : 'fullscreen;';
		$embed_video = '<div class="cgkit-iframe-wrap cgkit-iframe-embed ' . ( $autoplay ? 'cgkit-iframe-autoplay' : '' ) . '"><iframe src="' . $video_url . '" data-src="' . $video_url . '" itemprop="video" class="pdp-video" frameborder="0" width="560" height="340" allow="' . $video_allow . '" loading="lazy" allowfullscreen msallowfullscreen></iframe></div>';
		$embed_html  = '<div class="cgkit-iframe-wrap cgkit-iframe-lightbox"><iframe src="' . $video_url . '" itemprop="video" class="pdp-video" frameborder="0" width="560" height="340" allow="' . $video_allow . '" allowfullscreen msallowfullscreen></iframe></div>';
	} else {

		$tmp_ext = explode( '?', $video_url );
		$tmp_ext = pathinfo( strtolower( $tmp_ext[0] ), PATHINFO_EXTENSION );
		if ( 'mp4' !== $tmp_ext && 'webm' !== $tmp_ext ) {
			return '';
		}

		$video_type = 'video/mp4';
		if ( 'webm' === $tmp_ext ) {
			$video_type = 'video/webm';
		}

		$poster = isset( $full_src[0] ) && ! empty( $full_src[0] ) ? esc_url( $full_src[0] ) : '';
		$image  = '';
		if ( ! $autoplay ) {
			$image = '<img width="' . esc_attr( $full_src[1] ) . '" height="' . esc_attr( $full_src[2] ) . '" src="' . esc_url( $full_src[0] ) . '" data-src="' . esc_url( $full_src[0] ) . '" srcset="' . $full_srcset . '" data-srcset="' . $full_srcset . '" sizes="(max-width: 360px) 330px, (max-width: 800px) 100vw, 800px" alt="' . $alt_text . '" itemprop="thumbnail" class="cgkit-lazy pdp-img wp-post-image" loading="lazy"/>';
		}
		$embed_video = '<div class="cgkit-video-wrap ' . ( $autoplay ? 'autoplay' : '' ) . '">' . $image . '<video itemprop="video" class="pdp-video ' . ( $autoplay ? 'cgkit-lazy cgkit-autoplay' : '' ) . '" width="560" height="340" poster="' . $poster . '" ' . ( $autoplay ? ' preload="none" autoplay loop muted playsinline webkit-playsinline x5-playsinline ' : ' controls style="display: none;" ' ) . '><source src="' . $video_url . '" data-src="' . $video_url . '" type="' . $video_type . '" loading="lazy"></video><div class="cgkit-play cgkit-video-play ' . ( $autoplay ? '' : 'not-autoplay' ) . '"><svg class="pause" ' . ( $autoplay ? '' : 'style="display:none"' ) . ' viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><path d="M18 32h4V16h-4v16zm6-28C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm0 36c-8.82 0-16-7.18-16-16S15.18 8 24 8s16 7.18 16 16-7.18 16-16 16zm2-8h4V16h-4v16z" fill="#ffffff" class="fill-000000"></path></svg><svg class="play" ' . ( $autoplay ? 'style="display:none"' : '' ) . ' viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><path d="m20 33 12-9-12-9v18zm4-29C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm0 36c-8.82 0-16-7.18-16-16S15.18 8 24 8s16 7.18 16 16-7.18 16-16 16z" fill="#ffffff" class="fill-000000"></path></svg></div></div>';
		$embed_html  = '<div class="cgkit-video-wrap ' . ( $autoplay ? 'autoplay' : '' ) . '"><video itemprop="video" class="pdp-video ' . ( $autoplay ? 'cgkit-autoplay' : '' ) . '" width="560" height="340" poster="' . $poster . '" ' . ( $autoplay ? ' preload="none" autoplay loop muted playsinline webkit-playsinline x5-playsinline ' : ' controls ' ) . '><source src="' . $video_url . '" type="' . $video_type . '"></video><div class="cgkit-play cgkit-video-play ' . ( $autoplay ? '' : 'not-autoplay' ) . '"><svg class="pause" ' . ( $autoplay ? '' : 'style="display:none"' ) . ' viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><path d="M18 32h4V16h-4v16zm6-28C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm0 36c-8.82 0-16-7.18-16-16S15.18 8 24 8s16 7.18 16 16-7.18 16-16 16zm2-8h4V16h-4v16z" fill="#ffffff" class="fill-000000"></path></svg><svg class="play" ' . ( $autoplay ? 'style="display:none"' : '' ) . ' viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><path d="m20 33 12-9-12-9v18zm4-29C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm0 36c-8.82 0-16-7.18-16-16S15.18 8 24 8s16 7.18 16 16-7.18 16-16 16z" fill="#ffffff" class="fill-000000"></path></svg></div></div>';
	}

	return '<li class="swiper-slide swiper-slide-video cgkit-video ' . esc_attr( $list_class ) . '" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject"><a class="swiper-slide-imglink cgkit-video" data-e-disable-page-transition="true" href="' . esc_url( $full_src[0] ) . '" title="' . esc_html__( 'click to zoom-in', 'commercegurus-commercekit' ) . '" itemprop="contentUrl" data-size="' . esc_attr( $full_src[1] ) . 'x' . esc_attr( $full_src[2] ) . '" data-html="' . str_replace( array( '<', '>', '"' ), array( '&lt;', '&gt;', '&quot;' ), $embed_html ) . '">' . $embed_video . '</a></li>'; // phpcs:ignore
}

/**
 * Gallery image encode and return json
 *
 * @param  string $data array of data.
 */
function commercekit_gallery_image_encode( $data ) {
	return str_replace( '<img', '&lt;img', wp_json_encode( $data ) );
}

/**
 * Sticky add to cart variation images.
 *
 * @param string $product variation product.
 * @param string $rebuild rebuild cache if not found.
 */
function commercekit_sticky_atc_variation_images( $product, $rebuild = true ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return '';
	}
	$options  = get_option( 'commercekit', array() );
	$flags    = commercekit_feature_flags()->get_flags();
	$cgkit_as = isset( $flags['attribute_swatches'] ) && 1 === (int) $flags['attribute_swatches'] ? true : false;
	$cgkit_pg = isset( $flags['pdp_gallery'] ) && 1 === (int) $flags['pdp_gallery'] ? true : false;
	$cgkit_ag = isset( $flags['pdp_attributes_gallery'] ) && 1 === (int) $flags['pdp_attributes_gallery'] && $cgkit_pg ? true : false;
	$json_arr = '[]';
	if ( ! $cgkit_as || ! $cgkit_ag ) {
		return $json_arr;
	}

	$product_id    = $product->get_id();
	$cache_key2    = 'cgkit_swatch_loop_form_data_' . $product_id;
	$swatches_html = get_transient( $cache_key2 );
	if ( false !== $swatches_html ) {
		$swatches_data = json_decode( $swatches_html, true );
		return isset( $swatches_data['images'] ) ? wp_json_encode( $swatches_data['images'] ) : $json_arr;
	} elseif ( $rebuild ) {
		if ( function_exists( 'commercekit_as_build_product_swatch_cache' ) ) {
			commercekit_as_build_product_swatch_cache( $product, false, 'via PLP page - Sticky ATC' );
			$swatches_html = get_transient( $cache_key2 );
			if ( false !== $swatches_html ) {
				$swatches_data = json_decode( $swatches_html, true );
				return isset( $swatches_data['images'] ) ? wp_json_encode( $swatches_data['images'] ) : $json_arr;
			} else {
				return $json_arr;
			}
		} else {
			return $json_arr;
		}
	} else {
		return $json_arr;
	}
}

/**
 * Attribute gallery generate single json
 *
 * @param string $product    product object.
 * @param string $variations product variations.
 */
function commercekit_attribute_gallery_generate_single_json( $product, $variations ) {
	if ( ! defined( 'CGKIT_PDP_SINGLE_JSON' ) || true !== CGKIT_PDP_SINGLE_JSON ) {
		return;
	}

	$product_id = $product ? $product->get_id() : 0;
	if ( ! $product_id ) {
		return;
	}

	$post_thumbnail_id   = $product->get_image_id();
	$cgkit_image_gallery = array();
	$cgkit_video_gallery = array();
	$cgkit_variations    = array();
	if ( $product->is_type( 'variable' ) ) {
		$cgkit_image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
		if ( is_array( $cgkit_image_gallery ) ) {
			$cgkit_image_gallery = array_filter( $cgkit_image_gallery );
		}
		$cgkit_video_gallery = get_post_meta( $product_id, 'commercekit_video_gallery', true );

		if ( is_array( $variations ) && count( $variations ) ) {
			foreach ( $variations as $variation ) {
				$variation_img_id = isset( $variation['cgkit_image_id'] ) ? $variation['cgkit_image_id'] : get_post_thumbnail_id( $variation['variation_id'] );
				if ( isset( $variation['attributes'] ) && count( $variation['attributes'] ) && $variation_img_id ) {
					$image_id                = 'img_' . $variation_img_id;
					$var_image               = array();
					$var_image['img_count']  = 1;
					$var_image['attributes'] = $variation['attributes'];
					$var_image['gallery']    = array();

					$var_image['gallery']['images'][ $image_id ] = commercegurus_get_main_attributes_gallery_image_html( $variation_img_id, true );
					$var_image['gallery']['thumbs'][ $image_id ] = commercegurus_get_thumbnail_attributes_gallery_image_html( $variation_img_id, true );

					$cgkit_variations[] = $var_image;
				}
			}
		}
	}

	$cgkit_glob_gallery = array();
	$cgkit_attr_gallery = array();
	$attr_taxonomy      = array();
	$attr_custom        = array();
	$attr_names         = array();

	if ( isset( $cgkit_image_gallery['default_gallery'] ) ) {
		unset( $cgkit_image_gallery['default_gallery'] );
	}
	if ( isset( $cgkit_video_gallery['default_gallery'] ) ) {
		unset( $cgkit_video_gallery['default_gallery'] );
	}
	$default_gallery = array();
	$image_ids       = array();
	if ( $post_thumbnail_id ) {
		$image_ids = $product->get_gallery_image_ids();
		array_unshift( $image_ids, $post_thumbnail_id );
		$default_gallery['default_gallery'] = implode( ',', $image_ids );
	} else {
		$default_gallery['default_gallery'] = '';
	}
	if ( is_array( $cgkit_image_gallery ) ) {
		$cgkit_image_gallery = $default_gallery + $cgkit_image_gallery;
	} else {
		$cgkit_image_gallery = $default_gallery;
	}

	$video_gallery = get_post_meta( $product_id, 'commercekit_wc_video_gallery', true );
	if ( is_array( $video_gallery ) && count( $video_gallery ) ) {
		foreach ( $video_gallery as $image_id => $video_url ) {
			$cgkit_video_gallery['default_gallery'][ $image_id ] = $video_url;
		}
	}

	if ( isset( $cgkit_image_gallery['global_gallery'] ) && ! empty( $cgkit_image_gallery['global_gallery'] ) ) {
		$image_ids = explode( ',', $cgkit_image_gallery['global_gallery'] );
		if ( count( $image_ids ) ) {
			$index = 0;
			foreach ( $image_ids as $attachment_id ) {
				$css_class = '';
				$image_id  = 'img_' . $attachment_id;
				if ( isset( $cgkit_video_gallery['global_gallery'][ $attachment_id ] ) && ! empty( $cgkit_video_gallery['global_gallery'][ $attachment_id ] ) ) {
					$css_class  = 'pdp-video';
					$main_video = false;
					$video_url  = $cgkit_video_gallery['global_gallery'][ $attachment_id ];

					$cgkit_glob_gallery['images'][ $image_id ] = commercegurus_get_product_gallery_video_html( $video_url, $main_video, $pdp_attributes_autoplay, $attachment_id );
				} else {
					$cgkit_glob_gallery['images'][ $image_id ] = commercegurus_get_main_attributes_gallery_image_lazy_html( $attachment_id );
				}
				$cgkit_glob_gallery['thumbs'][ $image_id ] = commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, false, $index++, $css_class );
			}
		}
	}

	if ( is_array( $cgkit_image_gallery ) && count( $cgkit_image_gallery ) ) {
		$attributes = commercegurus_attributes_load_attributes( $product_id );
		if ( count( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					$attr_name    = 'attribute_' . $attribute['slug'];
					$attr_names[] = $attr_name;
					foreach ( $attribute['terms'] as $item ) {
						if ( is_numeric( $item->term_id ) && isset( $attribute['taxo'] ) && 1 === (int) $attribute['taxo'] ) {
							$attr_taxonomy[ $item->term_id ] = $attr_name;
						} else {
							$custom_slug = sanitize_title( $item->term_id );

							$attr_custom[ $custom_slug ]['term_id']   = $item->term_id;
							$attr_custom[ $custom_slug ]['attr_name'] = $attr_name;
						}
					}
				}
			}
		}

		foreach ( $cgkit_image_gallery as $slug => $image_gallery ) {
			if ( 'global_gallery' === $slug ) {
				continue;
			}

			$attributes        = array();
			$gallery           = array();
			$gallery['images'] = array();
			$gallery['thumbs'] = array();
			$images            = array();

			if ( 'default_gallery' === $slug ) {
				$attributes['default_gallery'] = 1;

				$images = explode( ',', trim( $image_gallery ) );
			} else {
				$slugs = explode( '_cgkit_', $slug );
				if ( count( $slugs ) ) {
					foreach ( $slugs as $nslug ) {
						if ( isset( $attr_taxonomy[ $nslug ] ) ) {
							$anslug = $nslug;
							if ( is_numeric( $nslug ) ) {
								$nterm = get_term( $nslug );
								if ( $nterm ) {
									$anslug = $nterm->slug;
								}
							}
							$attributes[ $attr_taxonomy[ $nslug ] ] = $anslug;
						} elseif ( isset( $attr_custom[ $nslug ] ) ) {
							$attributes[ $attr_custom[ $nslug ]['attr_name'] ] = $attr_custom[ $nslug ]['term_id'];
						}
					}
				}

				$images = explode( ',', trim( $image_gallery ) );
			}
			$images = array_filter( $images );
			if ( count( $images ) ) {
				$index = 0;
				foreach ( $images as $img_key => $attachment_id ) {
					$css_class = '';
					$image_id  = 'img_' . $attachment_id;
					if ( isset( $cgkit_video_gallery[ $slug ][ $attachment_id ] ) && ! empty( $cgkit_video_gallery[ $slug ][ $attachment_id ] ) ) {
						$css_class  = 'pdp-video';
						$main_video = 0 === $img_key ? true : false;
						$video_url  = $cgkit_video_gallery[ $slug ][ $attachment_id ];

						$gallery['images'][ $image_id ] = commercegurus_get_product_gallery_video_html( $video_url, $main_video, $pdp_attributes_autoplay, $attachment_id );
					} else {
						if ( 0 === $img_key ) {
							$apply_filter = isset( $attributes['default_gallery'] ) && 1 === $attributes['default_gallery'] ? true : false;

							$gallery['images'][ $image_id ] = commercegurus_get_main_attributes_gallery_image_html( $attachment_id, true, '', $apply_filter );
						} else {
							$gallery['images'][ $image_id ] = commercegurus_get_main_attributes_gallery_image_lazy_html( $attachment_id );
						}
					}
					if ( 0 === $img_key ) {
						$gallery['thumbs'][ $image_id ] = commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, true, $index++, $css_class );
					} else {
						$gallery['thumbs'][ $image_id ] = commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, false, $index++, $css_class );
					}
				}
			} elseif ( 'default_gallery' === $slug ) {
				$image_id = 'img_0';

				$gallery['images'][ $image_id ] = $placeholder_image;
				$gallery['thumbs'][ $image_id ] = $placeholder_image;
			}

			$attr_gallery               = array();
			$attr_gallery['attributes'] = $attributes;
			$attr_gallery['gallery']    = $gallery;
			$attr_gallery['img_count']  = count( $images );
			$cgkit_attr_gallery[]       = $attr_gallery;
		}
	}

	$json_data  = array();
	$upload_dir = wp_upload_dir();
	$json_path  = $upload_dir['basedir'] . '/commercekit-json';
	$json_file  = $json_path . '/product-' . $product_id . '.json';
	if ( ! file_exists( $json_path ) ) {
		if ( ! wp_mkdir_p( $json_path ) ) {
			commercekit_as_log( 'Failed to create JSON files path: ' . $json_path, 'commercekit-attribute-gallery' );
			return;
		}
	}

	$json_data['cgkit_attr_gallery'] = $cgkit_attr_gallery;
	$json_data['cgkit_attr_names']   = $attr_names;
	$json_data['cgkit_variations']   = $cgkit_variations;
	$json_data['cgkit_glob_gallery'] = $cgkit_glob_gallery;
	$json_data['cgkit_satc_images']  = commercekit_sticky_atc_variation_images( $product, false );

	if ( file_put_contents( $json_file, commercekit_gallery_image_encode( $json_data ) ) ) { // phpcs:ignore
		commercekit_as_log( 'JSON file created successfully: ' . $json_file, 'commercekit-attribute-gallery' );
	} else {
		commercekit_as_log( 'Failed to create JSON file: ' . $json_file, 'commercekit-attribute-gallery' );
	}
}

/**
 * Attribute gallery slugs
 *
 * @param string $product_id product ID.
 */
function commercekit_attribute_gallery_get_slugs( $product_id ) {
	$gallery_slugs = array();
	$attributes    = commercegurus_attributes_load_attributes( $product_id );
	if ( count( $attributes ) ) {
		$counter = 0;
		foreach ( $attributes as $attribute ) {
			if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
				foreach ( $attribute['terms'] as $item ) {
					if ( ! is_numeric( $item->term_id ) ) {
						$item->term_id = sanitize_title( $item->term_id );
					}
					$gallery_slugs[ $counter++ ] = $item->term_id;
				}
			}
		}
	}

	return $gallery_slugs;
}

/**
 * Public function for get CommerceKit Attribute Gallery
 *
 * @param  string $product_id    product ID.
 * @param  array  $attribute_ids array of product attribute IDs or slugs.
 * @return array  Single Array or mulitple arrays if atribute_ids is empty array.
 */
function commercekit_get_attribute_gallery( $product_id, $attribute_ids = array() ) {
	$gallery_slugs = commercekit_attribute_gallery_get_slugs( $product_id );
	$image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
	$cgkit_gallery = array();
	$gallery_slug  = '';
	if ( is_array( $attribute_ids ) && count( $attribute_ids ) ) {
		$gallery_slug = commercegurus_get_product_gallery_slug( implode( '_cgkit_', $attribute_ids ), $gallery_slugs );
	}
	if ( is_array( $image_gallery ) && count( $image_gallery ) ) {
		foreach ( $image_gallery as $key => $value ) {
			$images = array_filter( explode( ',', $value ) );
			$tslugs = array_filter( explode( '_cgkit_', $key ) );
			if ( ! empty( $gallery_slug ) && (string) $gallery_slug === (string) $key ) {
				return array_map( 'intval', $images );
			}
			$slugs = array();
			if ( count( $tslugs ) ) {
				foreach ( $tslugs as $i => $slug ) {
					if ( is_numeric( $slug ) ) {
						$slugs[ $i ] = intval( $slug );
					} else {
						$slugs[ $i ] = $slug;
					}
				}
			}
			$cgkit_gallery[] = array(
				'slugs'  => $slugs,
				'images' => array_map( 'intval', $images ),
			);
		}
	}

	return $cgkit_gallery;
}

/**
 * Public function for get Attribute Gallery image attachment IDs of all products
 *
 * @return array  Array of all image attachment IDs.
 */
function commercekit_get_attribute_gallery_image_attachments() {
	global $wpdb;
	$query   = $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s", 'commercekit_image_gallery' );
	$results = $wpdb->get_results( $query ); // phpcs:ignore
	$images  = array();
	if ( count( $results ) ) {
		foreach ( $results as $result ) {
			$image_gallery = maybe_unserialize( $result->meta_value );
			if ( is_array( $image_gallery ) && count( $image_gallery ) ) {
				foreach ( $image_gallery as $key => $value ) {
					$timages = array_filter( explode( ',', $value ) );
					$timages = array_map( 'intval', $timages );
					$images  = array_merge( $images, $timages );
				}
			}
		}
	}

	return array_values( array_unique( $images ) );
}

/**
 * Public function for create Attribute Gallery
 *
 * @param string $product_id    product ID.
 * @param array  $attribute_ids multiple arrays of product attribute IDs or slugs.
 * @param string $oparation     'create' or 'update' or 'remove' oparation.
 * @return void
 */
function commercekit_create_attribute_gallery( $product_id, $attribute_ids, $oparation = 'create' ) {
	global $commercekit_exporter;
	$product_id = intval( $product_id );
	$product    = wc_get_product_object( 'variable', $product_id );
	if ( ! $product ) {
		return;
	}

	$gallery_slugs = commercekit_attribute_gallery_get_slugs( $product_id );
	$valid_slugs   = array();
	if ( is_array( $attribute_ids ) && count( $attribute_ids ) ) {
		foreach ( $attribute_ids as $key => $attributes ) {
			if ( is_array( $attributes ) && count( $attributes ) ) {
				$validated = array();
				foreach ( $attributes as $attribute ) {
					if ( is_numeric( $attribute ) ) {
						$attribute = intval( $attribute );
					}
					if ( in_array( $attribute, $gallery_slugs, true ) ) {
						$validated[] = $attribute;
					}
				}
				if ( count( $attributes ) === count( $validated ) ) {
					$valid_slugs[] = commercegurus_get_product_gallery_slug( implode( '_cgkit_', $validated ), $gallery_slugs );
				}
			}
		}
	}

	if ( count( $valid_slugs ) ) {
		$image_gallery = (array) get_post_meta( $product_id, 'commercekit_image_gallery', true );
		if ( 'create' === $oparation ) {
			$image_gallery = array();
			foreach ( $valid_slugs as $valid_slug ) {
				$image_gallery[ $valid_slug ] = '';
			}
		} elseif ( 'update' === $oparation ) {
			foreach ( $valid_slugs as $valid_slug ) {
				if ( ! isset( $image_gallery[ $valid_slug ] ) ) {
					$image_gallery[ $valid_slug ] = '';
				}
			}
		} elseif ( 'remove' === $oparation ) {
			foreach ( $valid_slugs as $valid_slug ) {
				if ( isset( $image_gallery[ $valid_slug ] ) ) {
					unset( $image_gallery[ $valid_slug ] );
				}
			}
		}
		update_post_meta( $product_id, 'commercekit_image_gallery', $image_gallery );
	}
}

/**
 * Public function for update Attribute Gallery
 *
 * @param string $product_id    product ID.
 * @param array  $attribute_ids multiple arrays of product attribute IDs or slugs.
 * @return void
 */
function commercekit_update_attribute_gallery( $product_id, $attribute_ids ) {
	commercekit_create_attribute_gallery( $product_id, $attribute_ids, 'update' );
}

/**
 * Public function for remove Attribute Gallery
 *
 * @param string $product_id    product ID.
 * @param array  $attribute_ids multiple arrays of product attribute IDs or slugs.
 * @return void
 */
function commercekit_remove_attribute_gallery( $product_id, $attribute_ids ) {
	commercekit_create_attribute_gallery( $product_id, $attribute_ids, 'remove' );
}

/**
 * Public function for create Attribute Gallery Images
 *
 * @param string $product_id    product ID.
 * @param array  $attribute_ids array of product attribute IDs or slugs.
 * @param array  $images        array of attachment IDs or array of image URLs.
 * @param string $oparation     'create' or 'update' or 'remove' oparation.
 * @return void
 */
function commercekit_create_attribute_gallery_images( $product_id, $attribute_ids, $images, $oparation = 'create' ) {
	global $commercekit_exporter;
	$product_id = intval( $product_id );
	$product    = wc_get_product_object( 'variable', $product_id );
	if ( ! $product ) {
		return;
	}

	$gallery_slugs = commercekit_attribute_gallery_get_slugs( $product_id );
	$attributes    = array();
	if ( is_array( $attribute_ids ) && count( $attribute_ids ) ) {
		foreach ( $attribute_ids as $key => $value ) {
			if ( is_numeric( $value ) ) {
				$value = intval( $value );
			}
			if ( in_array( $value, $gallery_slugs, true ) ) {
				$attributes[] = $value;
			}
		}
	}

	if ( ! count( $attributes ) ) {
		return;
	}

	if ( ! is_array( $images ) || ! count( $images ) ) {
		return;
	}

	$nimages = array();
	foreach ( $images as $image ) {
		if ( is_numeric( $image ) ) {
			$nimages[] = intval( $image );
		} else {
			$image = wp_http_validate_url( $image );
			if ( $image ) {
				$attach_id = $commercekit_exporter->insert_attachment_from_url( $image );
				if ( $attach_id ) {
					$nimages[] = intval( $attach_id );
				}
			}
		}
	}

	if ( count( $nimages ) ) {
		$nimages       = array_unique( $nimages );
		$image_gallery = (array) get_post_meta( $product_id, 'commercekit_image_gallery', true );
		$gallery_slug  = commercegurus_get_product_gallery_slug( implode( '_cgkit_', $attributes ), $gallery_slugs );
		if ( 'create' === $oparation ) {
			$image_gallery[ $gallery_slug ] = implode( ',', $nimages );
		} elseif ( 'update' === $oparation ) {
			if ( isset( $image_gallery[ $gallery_slug ] ) && ! empty( $image_gallery[ $gallery_slug ] ) ) {
				$gimages = array_filter( explode( ',', $image_gallery[ $gallery_slug ] ) );
				$gimages = array_map( 'intval', $gimages );
				$gimages = array_unique( array_merge( $gimages, $nimages ) );

				$image_gallery[ $gallery_slug ] = implode( ',', $gimages );
			} else {
				$image_gallery[ $gallery_slug ] = implode( ',', $nimages );
			}
		} elseif ( 'remove' === $oparation ) {
			if ( isset( $image_gallery[ $gallery_slug ] ) && ! empty( $image_gallery[ $gallery_slug ] ) ) {
				$gimages = array_map( 'intval', explode( ',', $image_gallery[ $gallery_slug ] ) );
				$gimages = array_values( array_diff( $gimages, $nimages ) );

				$image_gallery[ $gallery_slug ] = implode( ',', $gimages );
			}
		}
		update_post_meta( $product_id, 'commercekit_image_gallery', $image_gallery );
	}
}

/**
 * Public function for update Attribute Gallery Images
 *
 * @param string $product_id    product ID.
 * @param array  $attribute_ids array of product attribute IDs or slugs.
 * @param array  $images        array of attachment IDs or array of image URLs.
 * @return void
 */
function commercekit_update_attribute_gallery_images( $product_id, $attribute_ids, $images ) {
	commercekit_create_attribute_gallery_images( $product_id, $attribute_ids, $images, 'update' );
}

/**
 * Public function for remove Attribute Gallery Images
 *
 * @param string $product_id    product ID.
 * @param array  $attribute_ids array of product attribute IDs or slugs.
 * @param array  $images        array of attachment IDs.
 * @return void
 */
function commercekit_remove_attribute_gallery_images( $product_id, $attribute_ids, $images ) {
	commercekit_create_attribute_gallery_images( $product_id, $attribute_ids, $images, 'remove' );
}

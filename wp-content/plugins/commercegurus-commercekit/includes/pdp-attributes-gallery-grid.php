<?php
/**
 * Main CommerceGurus Gallery template
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Gallery
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product, $cgkit_gallery_layout;
$product_id        = $product->get_id();
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'images',
	)
);

$options = get_option( 'commercekit', array() );

$pdp_attributes_lightbox = ( ( isset( $options['pdp_lightbox'] ) && 1 === (int) $options['pdp_lightbox'] ) || ! isset( $options['pdp_lightbox'] ) ) ? true : false;
$pdp_attributes_autoplay = ( ( isset( $options['pdp_video_autoplay'] ) && 1 === (int) $options['pdp_video_autoplay'] ) || ! isset( $options['pdp_video_autoplay'] ) ) ? true : false;
$pdp_attr_thub_count     = 0;
$pdp_attributes_thumbs   = isset( $options['pdp_desktop_thumbnails'] ) && ! empty( $options['pdp_desktop_thumbnails'] ) ? (int) $options['pdp_desktop_thumbnails'] : (int) commercekit_get_default_settings( 'pdp_desktop_thumbnails' );
$pdp_attributes_m_thumbs = isset( $options['pdp_mobile_thumbnails'] ) && ! empty( $options['pdp_mobile_thumbnails'] ) ? (int) $options['pdp_mobile_thumbnails'] : (int) commercekit_get_default_settings( 'pdp_mobile_thumbnails' );
$pdp_attributes_thumbs_o = (int) $pdp_attributes_thumbs;
$pdp_attributes_thumbs   = apply_filters( 'commercekit_product_gallery_thumbnails', $pdp_attributes_thumbs );
$pdp_attributes_m_thumbs = $pdp_attributes_thumbs_o !== (int) $pdp_attributes_thumbs ? (int) $pdp_attributes_thumbs : $pdp_attributes_m_thumbs;
$pdp_attributes_m_thumbs = apply_filters( 'commercekit_product_mobile_gallery_thumbnails', $pdp_attributes_m_thumbs );
$cgkit_gallery_layout    = isset( $options['pdp_gallery_layout'] ) && ! empty( $options['pdp_gallery_layout'] ) ? $options['pdp_gallery_layout'] : commercekit_get_default_settings( 'pdp_gallery_layout' );
$cgkit_gallery_layout2   = get_post_meta( $product_id, 'commercekit_gallery_layout', true );
if ( isset( $options['pdp_mobile_layout'] ) && 'show-edge' === $options['pdp_mobile_layout'] ) {
	$wrapper_classes[] = 'ckit-mobile-pdp-gallery-active';
	$wrapper_classes[] = 'ckit-mobile-show-edge';
} elseif ( ( isset( $options['pdp_mobile_layout'] ) && 'minimal' === $options['pdp_mobile_layout'] ) || ( isset( $options['pdp_mobile_optimized'] ) && 1 === (int) $options['pdp_mobile_optimized'] ) ) {
	$wrapper_classes[] = 'ckit-mobile-pdp-gallery-active';
}
if ( isset( $options['widget_pos_pdp_gallery'] ) && 1 === (int) $options['widget_pos_pdp_gallery'] ) {
	$wrapper_classes[] = 'ckit-shortcode-gallery';
}
if ( ! empty( $cgkit_gallery_layout2 ) ) {
	$cgkit_gallery_layout = $cgkit_gallery_layout2;
}
$wrapper_classes[] = 'cgkit-gallery-' . esc_attr( $cgkit_gallery_layout );

$preload_count = 1;
if ( 'grid-2-4' === $cgkit_gallery_layout ) {
	$preload_count = 7;
}
if ( 'grid-3-1-2' === $cgkit_gallery_layout ) {
	$preload_count = 5;
}
if ( 'grid-1-2-2' === $cgkit_gallery_layout ) {
	$preload_count = 4;
}

$cgkit_image_gallery  = array();
$cgkit_video_gallery  = array();
$cgkit_variations     = array();
$is_default_variation = false;
$query_variations     = array();
$variations           = array();
if ( $product->is_type( 'variable' ) ) {
	$cgkit_image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
	if ( is_array( $cgkit_image_gallery ) ) {
		$cgkit_image_gallery = array_filter( $cgkit_image_gallery );
	}
	$cgkit_video_gallery = get_post_meta( $product_id, 'commercekit_video_gallery', true );

	$default_attributes = $product->get_default_attributes();
	$variations         = commercekit_get_available_variations( $product );
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
			$variation_attributes = array();
			if ( isset( $variation['attributes'] ) && count( $variation['attributes'] ) ) {
				foreach ( $variation['attributes'] as $va_key => $va_val ) {
					$query_variations[ $va_key ][] = $va_val;

					$va_key = str_replace( 'attribute_', '', $va_key );

					$variation_attributes[ $va_key ] = $va_val;
				}
			}
			if ( $variation_attributes == $default_attributes ) { // phpcs:ignore
				$is_default_variation = true;
			}
		}
	}
}
if ( ! $is_default_variation ) {
	if ( count( $query_variations ) ) {
		foreach ( $query_variations as $va_key => $va_val ) {
			if ( isset( $_GET[ $va_key ] ) && in_array( $_GET[ $va_key ], $va_val ) ) { // phpcs:ignore
				$is_default_variation = true;
				break;
			}
		}
	}
}
$placeholder_image = '<li class="swiper-slide" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject"><div class="woocommerce-product-gallery__image--placeholder">' . sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'commercegurus-commercekit' ) ) . '</div></li>';
?>
<style>
	.cg-main-swiper.swiper-container, .cg-thumb-swiper.swiper-container {
		width: 100%;
		height: 100%;
		margin-left: auto;
		margin-right: auto;
	}
	.cg-main-swiper ul.swiper-wrapper, .cg-thumb-swiper ul.swiper-wrapper {
		padding: 0;
		margin: 0;
	}
	.cg-main-swiper ul.swiper-wrapper li.swiper-slide, .cg-thumb-swiper ul.swiper-wrapper li.swiper-slide {
		list-style: none;
		margin: 0;
	}
	.cg-main-swiper {
		height: auto;
		width: 100%;
		position: relative;
	}
	.cg-main-swiper .swiper-slide img, .cg-thumb-swiper .swiper-slide img {
		display: block;
		width: 100%;
		height: auto;
	}
	.gallery-hide {
		display: none;
	}
	.gallery-show {
		display: block;
	}
	.elementor-invisible {
		visibility: visible;
	}
	div.cgkit-play, div.cgkit-play svg {
		position:absolute;
		font-size:100%;
		border-radius:100px;
		top:50%;
		left:50%;
		width:40px;
		height:40px;
		transform:translate(-50%,-50%);
		z-index:10;
		transition: 0.2s all;
	}
	div.cgkit-play:hover {
		background-color:rgba(0,0,0,.5);
	}
	div.cgkit-play:active, div.cgkit-play:focus {
		outline:0;
		border:none;
		-moz-outline-style:none;
	}
	div.cg-main-swiper div.cgkit-play, div.cg-main-swiper div.cgkit-play svg,
	div.pswp__scroll-wrap div.cgkit-play, div.pswp__scroll-wrap div.cgkit-play svg {
		width:60px;
		height:60px;
	}
	div.cg-main-swiper div.cgkit-play svg,
	div.pswp__scroll-wrap div.cgkit-play svg {
		width:80px;
		height:80px;
	}
	div.cgkit-iframe-wrap {
		position: relative;
		padding-bottom: 56.25%;
		height: 0;	
	}
	div.cgkit-iframe-wrap iframe {
		position: absolute;
		top: 0;
		left: 1px;
		width: 100%;
		height: 100%;
	} 
	div.cgkit-video-wrap {
		position: relative;
		width: 100%;
	}
	div.cgkit-video-wrap video {
		display: block;
		width: 100%;
		height: auto;
		object-fit: fill;
		-o-object-fit: fill;
	}
	div.pswp__scroll-wrap div.cgkit-video-wrap {
		width: auto;
		height: 100%;
		margin: 0 auto;
	}
	div.pswp__scroll-wrap div.cgkit-video-wrap video {
		width: auto;
		height: 100%;
	}
	div.cgkit-video-wrap div.cgkit-play {
		opacity: 0;
		transition: 0.15s all;
	}
	div.cgkit-video-wrap:hover div.cgkit-play,
	div.cgkit-video-wrap div.cgkit-play.not-autoplay {
		opacity: 1;
	}
	/* Full screen video on mobile */
	@media (max-width: 770px) {
		div.pswp__scroll-wrap div.cgkit-video-wrap {
			width: auto !important; /* safari */
			display: flex;
			align-items: center;
		}
		div.pswp__scroll-wrap div.cgkit-video-wrap video {
			height: auto !important;
		}
	}
	@media (min-width: 771px) {
		.cg-layout-grid-2-4 ul.swiper-wrapper,
		.cg-layout-grid-3-1-2 ul.swiper-wrapper,
		.cg-layout-grid-1-2-2 ul.swiper-wrapper {
			display: grid;
			gap: 10px !important;
		}
		.cg-layout-grid-2-4 ul.swiper-wrapper {
			grid-template-columns: repeat(2, 1fr);
		}
		.cg-layout-grid-3-1-2 ul.swiper-wrapper {
			grid-template-columns: repeat(6, 1fr);
		}
		.cg-layout-grid-1-2-2 ul.swiper-wrapper {
			grid-template-columns: repeat(2, 1fr);
		}
		.cg-layout-grid-2-4 ul li.swiper-slide,
		.cg-layout-grid-3-1-2 ul li.swiper-slide,
		.cg-layout-grid-1-2-2 ul li.swiper-slide {
			margin:  0;
			padding: 0;
			list-style: none;
		}
		.cg-layout-grid-1-2-2 ul li.swiper-slide {
			grid-column: span 1;
		}
		.cg-layout-grid-2-4 ul li.swiper-slide:last-child:nth-child(odd) {
			grid-column: span 2;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide {
			grid-column: span 3;
			order: 5;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:nth-child(1) {
			grid-column: span 6;
			order: 4;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:nth-child(2) {
			grid-column: span 2;
			order: 1;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:nth-child(3) {
			grid-column: span 2;
			order: 2;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:nth-child(4) {
			grid-column: span 2;
			order: 3;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:nth-child(5) {
			grid-column: span 3;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:nth-child(6) {
			grid-column: span 3;
		}
		.cg-layout-grid-3-1-2 ul li.swiper-slide:last-child:nth-child(odd) {
			grid-column: span 6;
		}
		.cg-layout-grid-1-2-2 ul li.swiper-slide:nth-child(1) {
			grid-column: span 2;
		}
		.cg-layout-grid-1-2-2 ul li.swiper-slide:last-child:nth-child(even) {
			grid-column: span 2;
		}
		.load-more-images {
			position: absolute;
			left: 50%;
			transform: translateX(-50%);
			bottom: 1rem;
			margin: 0px auto;
			width: auto;
			z-index: 2;
			background-color: #000;
			color: #fff;
			padding: 10px 30px;
			border-radius: 25px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			text-transform: uppercase;
			display: none;	
		}
		ul.swiper-wrapper li.swiper-slide.more-images {
			display: none;	
		}
		.cg-grid-layout .swiper-button-next,
		.cg-grid-layout .swiper-button-prev,
		.cg-grid-layout .cg-thumb-swiper {
			display: none;
		}
	}
	@media (max-width: 770px) {
		.cg-grid-layout ul.cg-main-swiper li.swiper-slide {
			width: 100%;
		}
		.cg-grid-layout .swiper-button-next.swiper-button-disabled,
		.cg-grid-layout .swiper-button-prev.swiper-button-disabled {
			visibility: hidden;
		}
		.cg-m-thumbs-3.cg-thumb-swiper .swiper-slide { width: 33.3333%; }
		.cg-m-thumbs-4.cg-thumb-swiper .swiper-slide { width: 25%; }
		.cg-m-thumbs-5.cg-thumb-swiper .swiper-slide { width: 20%; }
		.cg-m-thumbs-6.cg-thumb-swiper .swiper-slide { width: 16.6666%; }
		.cg-m-thumbs-7.cg-thumb-swiper .swiper-slide { width: 14.2857%; }
		.cg-m-thumbs-8.cg-thumb-swiper .swiper-slide { width: 12.5%; }
		.cg-grid-layout .swiper-container:not(.swiper-container-initialized) .swiper-button-prev {
			visibility: hidden;
		}
		.cg-grid-layout .cg-thumbs-count-2:not(.swiper-container-initialized) .swiper-wrapper, 
		.cg-grid-layout .cg-thumbs-count-3:not(.swiper-container-initialized) .swiper-wrapper {
			justify-content: center;
		}
		.cg-grid-layout .cg-thumb-swiper.swiper-container {
			margin-top: 10px;
			margin-left: -5px;
			width: calc(100% + 10px);
		}
		.rtl .cg-grid-layout .cg-thumb-swiper.swiper-container {
			margin-left: 0;
			margin-right: -5px;
		}
		.cg-grid-layout .cg-thumb-swiper .swiper-slide {
			padding-left: 5px;
			padding-right: 5px;
			background-color: transparent;
		}
		.cg-grid-layout .load-more-images {
			display: none;	
		}
		.cg-grid-layout ul.swiper-wrapper li.swiper-slide.more-images {
			display: none;	
		}
		.cg-grid-layout .swiper-button-next,
		.cg-grid-layout .swiper-button-prev {
			visibility: hidden;
		}
		div.cg-main-swiper div.cgkit-video-wrap.autoplay div.cgkit-play svg,
		div.pswp__scroll-wrap div.cgkit-video-wrap.autoplay div.cgkit-play svg {
			display: none;
			visibility: hidden;
		}
		div.cgkit-video-wrap.autoplay div.cgkit-play {
			width: 100%;
			height: 100%;
			border-radius: 0px;
		}
		div.cgkit-video-wrap.autoplay div.cgkit-play:hover {
			background: none;
		}
		div.cgkit-video-wrap.autoplay video {
			display: block;
		}
		div.cgkit-video-wrap.autoplay img {
			display: none;
		}
	}
	.cg-layout-horizontal .swiper-slide {
		text-align: center;
		font-size: 18px;
		background: #fff;
		/* Center slide text vertically */
		display: -webkit-box;
		display: -ms-flexbox;
		display: -webkit-flex;
		display: flex;
		-webkit-box-pack: center;
		-ms-flex-pack: center;
		-webkit-justify-content: center;
		justify-content: center;
		-webkit-box-align: center;
		-ms-flex-align: center;
		-webkit-align-items: center;
		align-items: center;
		height: auto;
	}
	.cg-layout-horizontal .swiper-slide-imglink {
		height: auto;
		width: 100%;
	}
	.cg-layout-horizontal .swiper-container {
		width: 100%;
		margin-left: auto;
		margin-right: auto;
	}
	.cg-layout-horizontal .cg-main-swiper {
		height: auto;
		width: 100%;
	}
	.cg-layout-horizontal .cg-thumb-swiper {
		height: 20%;
		box-sizing: border-box;
	}
	.cg-layout-horizontal .cg-thumb-swiper .swiper-slide {
		height: 100%;
		opacity: 0.4;
	}
	.cg-layout-horizontal .cg-thumb-swiper .swiper-slide-thumb-active {
		opacity: 1;
	}
	.cg-layout-horizontal .swiper-button-next, .cg-layout-horizontal .swiper-button-prev {
		background-image: none;
		visibility: visible;
	}
	.cg-layout-horizontal .cg-swiper-preloader {
		width: 42px;
		height: 42px;
		position: absolute;
		left: 50%;
		top: 50%;
		margin-left: -21px;
		margin-top: -21px;
		z-index: 10;
		transform-origin: 50%;
		animation: swiper-preloader-spin 1s infinite linear;
		box-sizing: border-box;
		border: 4px solid var(--swiper-preloader-color,var(--swiper-theme-color));
		border-radius: 50%;
		border-top-color: transparent;
	}
	.cg-layout-horizontal .swiper-button-next.swiper-button-disabled,
	.cg-layout-horizontal .swiper-button-prev.swiper-button-disabled {
		visibility: hidden;
	}
	.pswp button.pswp__button {
		background-color: transparent;
	}
	.cg-layout-horizontal .swiper-container:not(.swiper-container-initialized) .swiper-button-prev {
		visibility: hidden;
	}
	.cg-layout-horizontal .cg-thumbs-count-2:not(.swiper-container-initialized) .swiper-wrapper, 
	.cg-layout-horizontal .cg-thumbs-count-3:not(.swiper-container-initialized) .swiper-wrapper {
		justify-content: center;
	}
	.cg-layout-horizontal .cg-thumb-swiper.swiper-container {
		margin-left: -5px;
		width: calc(100% + 10px);
	}
	.cg-layout-horizontal .cg-thumb-swiper .swiper-slide {
		padding-left: 5px;
		padding-right: 5px;
		background-color: transparent;
	}
	.product .cg-layout-horizontal ul li.swiper-slide {
		margin: 0;
	}
	/* SVG Arrows */
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-next:after,
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-prev:after {
		content: "";
		font-family: inherit;
		font-size: inherit;
		width: 22px;
		height: 22px;
		background: #111;
		-webkit-mask-position: center;
		-webkit-mask-repeat: no-repeat;
		-webkit-mask-size: contain;
	}
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-next,
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-prev {
		width: 42px;
		height: 42px;
		margin-top: -21px;
		background: hsla(0, 0%, 100%, 0.75);
		transition: background 0.5s ease;
		border-radius: 0.25rem;
		cursor: pointer;
	}
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-next:hover,
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-prev:hover {
		background: #fff;
	}
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-prev:after,
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-next:after  {
		-webkit-mask-image: url("data:image/svg+xml;charset=utf8,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 19L8 12L15 5' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
		mask-image: url("data:image/svg+xml;charset=utf8,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 19L8 12L15 5' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
	}
	#commercegurus-pdp-gallery.cg-layout-horizontal .swiper-button-next:after {
		-webkit-transform: scaleX(-1);
		transform: scaleX(-1);
	}
	#commercegurus-pdp-gallery:not(.cg-layout-horizontal) .swiper-wrapper {
		height: auto !important;
		transform: none !important;
	}
	.cg-main-swiper .swiper-button-next:focus, .cg-thumb-swiper .swiper-button-next:focus,
	.cg-main-swiper .swiper-button-prev:focus, .cg-thumb-swiper .swiper-button-next:focus {
		outline: 0;
	}
	/* Lightbox cursor */
	.cg-lightbox-active .swiper-slide-imglink {
		cursor: zoom-in;
	}
	<?php if ( ! $pdp_attributes_lightbox ) { ?>
	.swiper-slide-imglink {
		cursor: default;
	}
	<?php } ?>
	@media (max-width: 770px) {
		.swiper-container.cg-main-swiper .swiper-wrapper .swiper-slide {
			display: none;
		}
		.swiper-container.cg-main-swiper .swiper-wrapper .swiper-slide:first-child {
			display: block;
		}
		.swiper-container.cg-main-swiper.swiper-container-initialized .swiper-wrapper .swiper-slide {
			display: flex;
		}
		.theme-shoptimizer #commercegurus-pdp-gallery-wrapper.ckit-mobile-pdp-gallery-active {
			margin-left: -1em;
			width: calc(100% + 2em);
			margin-bottom: 10px;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper {
			cursor: auto !important;
			height: inherit;
			margin-top: 5px;
			padding: 0 1em;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper {
			display: inline-block;
			width: 100%;
			text-align: center;
			transform: none !important;
			line-height: 1em;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper .swiper-slide {
			display: inline-block;
			background: #000;
			opacity: 0.2;
			cursor: auto;
			border-radius: 50%;
			margin: 1px 2px;
			max-width: 8px;
			height: 8px;
			padding: 0;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper .swiper-slide.swiper-slide-thumb-active {
			background: #555;
			opacity: 1;
		}
		.ckit-mobile-pdp-gallery-active .swiper-button-next,
		.ckit-mobile-pdp-gallery-active .swiper-button-prev {
			display: none;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper .swiper-slide > * {
			display: none !important;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .swiper-wrapper.cg-psp-gallery {
			display: flex;
			gap: 10px;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cgkit-one-slider .swiper-wrapper.cg-psp-gallery {
			gap: 0px;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .swiper-wrapper.cg-psp-gallery .swiper-slide {
			display: flex;
			flex: 0 0 <?php echo esc_attr( commercekit_gallery_pdp_showedge_percent() ); ?>%;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .swiper-wrapper.cg-psp-gallery .swiper-slide img {
			display: block;
			max-width: 100%;
			max-height: 100%;
			height: auto !important;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper .swiper-wrapper .swiper-slide {
			display: none;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper .swiper-wrapper .swiper-slide:first-child,
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper .swiper-wrapper .swiper-slide:nth-child(2) {
			display: flex;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper.swiper-container-initialized .swiper-wrapper .swiper-slide {
			display: flex;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper.swiper-container-initialized .swiper-wrapper.cg-psp-gallery {
			gap: 0;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper.swiper-container-initialized .swiper-wrapper.cg-psp-gallery .swiper-slide {
			flex: none;
		}
		.ckit-mobile-pdp-gallery-active .cgkit-one-slider .swiper-wrapper.cg-psp-gallery .swiper-slide {
			flex: none;
			width: 100%;
			display: block !important;
		}
		.cgkit-iframe-wrap {
			position: relative;
		}
		.cgkit-iframe-wrap.cgkit-iframe-autoplay::before,
		.cg-lightbox-active .cgkit-iframe-embed::before {
			content: "";
			background: transparent;
			width: 100%;
			height: 100%;
			min-height: 1px;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 99;
		}
	}
/* -- RTL -- */
.rtl #commercegurus-pdp-gallery .swiper-button-next:after {
	-webkit-transform:  none;
	transform: none;
}

.rtl #commercegurus-pdp-gallery .swiper-button-prev:after {
	transform: scale(-1, 1);
}
/* -- Shoptimizer only -- */
@media (min-width: 993px) {
	.theme-shoptimizer .product-details-wrapper {
		overflow: visible;
		display: flex;
		justify-content: space-between;
	}
	.theme-shoptimizer.single-product div.product .summary {
		position: sticky;
		top: 30px;
		align-self: flex-start;
	}
	.theme-shoptimizer.single-product .commercekit-sticky-add-to-cart + .summary.entry-summary {
		top: 70px;
	}
	.theme-shoptimizer.admin-bar.single-product .commercekit-sticky-add-to-cart + .summary.entry-summary {
		top: 100px;
	}
	.theme-shoptimizer.sticky-t.single-product:not(.ckit_stickyatc_active) div.product .summary {
		top: 90px;
	}
	.theme-shoptimizer.admin-bar.single-product div.product .summary {
		top: 60px;
	}
	.theme-shoptimizer.sticky-t.admin-bar.single-product:not(.ckit_stickyatc_active) div.product .summary {
		top: 120px;
	}
}
#commercegurus-pdp-gallery-wrapper.ckit-shortcode-gallery {
	max-width: 100%;
	width: 100%;
}
</style>
<?php
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
$one_slider_css  = '';
if ( $post_thumbnail_id ) {
	$image_ids = $product->get_gallery_image_ids();
	array_unshift( $image_ids, $post_thumbnail_id );
	$default_gallery['default_gallery'] = implode( ',', $image_ids );
	if ( 1 === count( $image_ids ) ) {
		$one_slider_css = 'cgkit-one-slider';
	}
} else {
	$default_gallery['default_gallery'] = '';

	$one_slider_css = 'cgkit-one-slider';
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
$layout_template = '<div class="swiper-container cg-main-swiper"><ul class="swiper-wrapper cg-psp-gallery" itemscope itemtype="http://schema.org/ImageGallery">{gallery-images}</ul><button class="swiper-button-next" aria-label="Next slide"></button><button class="swiper-button-prev" aria-label="Previous slide"></button><button class="load-more-images more" data-more="' . esc_html__( 'Load more images', 'commercegurus-commercekit' ) . '" data-less="' . esc_html__( 'Show less images', 'commercegurus-commercekit' ) . '">' . esc_html__( 'Load more images', 'commercegurus-commercekit' ) . '</button></div><div thumbsSlider="" class="swiper-container cg-thumb-swiper cg-thumbs-' . esc_attr( $pdp_attributes_thumbs ) . ' cg-m-thumbs-' . esc_attr( $pdp_attributes_m_thumbs ) . ' cg-thumbs-count-{img-count}"><ul class="swiper-wrapper" itemscope itemtype="http://schema.org/ImageGallery">{gallery-thumbs}</ul>' . ( isset( $options['pdp_thumb_arrows'] ) && 1 === (int) $options['pdp_thumb_arrows'] ? '<button class="swiper-button-next swiper-button-disabled" aria-label="Next slide"></button><button class="swiper-button-prev swiper-button-disabled" aria-label="Previous slide"></button>' : '' ) . '</div>';

if ( defined( 'CGKIT_PDP_SINGLE_JSON' ) && true === CGKIT_PDP_SINGLE_JSON ) {
	$upload_dir = wp_upload_dir();
	$json_file  = $upload_dir['basedir'] . '/commercekit-json/product-' . $product_id . '.json';
	if ( ! file_exists( $json_file ) ) {
		commercekit_attribute_gallery_generate_single_json( $product, $variations );
	}
	$json_url = $upload_dir['baseurl'] . '/commercekit-json/product-' . $product_id . '.json?v=' . filemtime( $json_file );

	$cgkit_attr_gallery = array();
	$cgkit_glob_gallery = array();
	$cgkit_variations   = array();
	$attr_names         = array();
}
?>
<script type="text/javascript">
/* <![CDATA[ */
var cgkit_attr_gallery = <?php echo commercekit_gallery_image_encode( $cgkit_attr_gallery ); ?>; var cgkit_attr_names = <?php echo wp_json_encode( $attr_names ); ?>; var cgkit_variations = <?php echo commercekit_gallery_image_encode( $cgkit_variations ); ?>; var cgkit_glob_gallery = <?php echo commercekit_gallery_image_encode( $cgkit_glob_gallery ); ?>; var cgkit_gallery_template = '<?php echo ' ' . $layout_template; // phpcs:ignore ?>';
/* ]]> */
</script>
<?php if ( defined( 'CGKIT_PDP_SINGLE_JSON' ) && true === CGKIT_PDP_SINGLE_JSON ) { ?>
<script type="text/javascript">
/* <![CDATA[ */
fetch( '<?php echo esc_url( $json_url ); ?>', {
	method: 'GET',
} ).then( response => response.json() ).then( json => {
	if ( json.cgkit_attr_gallery !== undefined ) {
		cgkit_attr_gallery = json.cgkit_attr_gallery;
		cgkit_attr_names = json.cgkit_attr_names;
		cgkit_variations = json.cgkit_variations;
		cgkit_glob_gallery = json.cgkit_glob_gallery;
		commercekit_ajs.pdp_json_data = 0;
		if ( commercekit_ajs.pdp_gal_loaded == 1 && typeof loadAttributesGallery == 'function' ) {
			loadAttributesGallery();
		}
		if ( typeof cgkit_clone !== 'undefined' && cgkit_clone ) {
			cgkit_clone.setAttribute( 'data-images', json.cgkit_satc_images );
		}
	}
} );
/* ]]> */
</script>
<?php } ?>

<div id="commercegurus-pdp-gallery-wrapper" class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>">

<?php do_action( 'commercekit_before_gallery' ); ?>

<div id="commercegurus-pdp-gallery" class="cg-grid-layout cg-layout-<?php echo esc_attr( $cgkit_gallery_layout ); ?> <?php echo esc_attr( $one_slider_css ); ?> <?php echo true === $pdp_attributes_lightbox ? 'cg-lightbox-active' : ''; ?>" data-layout-class="cg-layout-<?php echo esc_attr( $cgkit_gallery_layout ); ?>" data-gallery-slug="default_gallery" data-image-ids="<?php echo isset( $cgkit_image_gallery['default_gallery'] ) ? esc_attr( $cgkit_image_gallery['default_gallery'] ) : ''; ?>" <?php echo true === $is_default_variation ? 'style="visibility: hidden"' : ''; ?>>
	<div class="swiper-container cg-main-swiper">
		<ul class="swiper-wrapper cg-psp-gallery" itemscope itemtype="http://schema.org/ImageGallery">
			<?php
			$cgkit_gallery = array();
			if ( isset( $cgkit_image_gallery['default_gallery'] ) && ! empty( $cgkit_image_gallery['default_gallery'] ) ) {
				$cgkit_gallery = explode( ',', trim( $cgkit_image_gallery['default_gallery'] ) );
				if ( count( $cgkit_gallery ) ) {
					foreach ( $cgkit_gallery as $img_key => $attachment_id ) {
						if ( isset( $cgkit_video_gallery['default_gallery'][ $attachment_id ] ) && ! empty( $cgkit_video_gallery['default_gallery'][ $attachment_id ] ) ) {
							$list_class = $preload_count >= $img_key ? 'less-images' : '';
							$main_video = $preload_count >= $img_key ? true : false;
							echo commercegurus_get_product_gallery_video_html( $cgkit_video_gallery['default_gallery'][ $attachment_id ], $main_video, $pdp_attributes_autoplay, $attachment_id, $list_class ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						} else {
							if ( $preload_count >= $img_key ) {
								$apply_filter = 0 === $img_key ? true : false;
								echo commercegurus_get_main_attributes_gallery_image_html( $attachment_id, true, 'less-images', $apply_filter ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
							} else {
								echo commercegurus_get_main_attributes_gallery_image_lazy_html( $attachment_id, false, 'more-images' ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
							}
						}
						$pdp_attr_thub_count++;
					}
				} else {
					$html = $placeholder_image;
					echo $html; // phpcs:ignore
				}
			} else {
				if ( $post_thumbnail_id ) {
					$html = commercegurus_get_main_attributes_gallery_image_html( $post_thumbnail_id, true, '', true );
					$pdp_attr_thub_count++;
				} else {
					$html = $placeholder_image;
				}
				echo $html; // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
				$attachment_ids = $product->get_gallery_image_ids();
				if ( $attachment_ids && $product->get_image_id() ) {
					$pdp_attr_thub_count += count( $attachment_ids );
					foreach ( $attachment_ids as $attachment_id ) {
						echo commercegurus_get_main_attributes_gallery_image_lazy_html( $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
					}
				}
			}
			?>
		</ul>
		<button class="swiper-button-next" aria-label="Next slide"></button>
		<button class="swiper-button-prev" aria-label="Previous slide"></button>
		<button class="load-more-images more" data-more="<?php esc_html_e( 'Load more images', 'commercegurus-commercekit' ); ?>" data-less="<?php esc_html_e( 'Show less images', 'commercegurus-commercekit' ); ?>"><?php esc_html_e( 'Load more images', 'commercegurus-commercekit' ); ?></button>
	</div>
	<div thumbsSlider="" class="swiper-container cg-thumb-swiper cg-thumbs-<?php echo esc_attr( $pdp_attributes_thumbs ); ?> cg-m-thumbs-<?php echo esc_attr( $pdp_attributes_m_thumbs ); ?> cg-thumbs-count-<?php echo esc_attr( $pdp_attr_thub_count ); ?>">
		<ul class="swiper-wrapper" itemscope itemtype="http://schema.org/ImageGallery">
			<?php
			$html  = '';
			$index = 0;
			if ( isset( $cgkit_image_gallery['default_gallery'] ) && ! empty( $cgkit_image_gallery['default_gallery'] ) ) {
				if ( count( $cgkit_gallery ) ) {
					foreach ( $cgkit_gallery as $img_key => $attachment_id ) {
						$css_class = '';
						if ( isset( $cgkit_video_gallery['default_gallery'][ $attachment_id ] ) && ! empty( $cgkit_video_gallery['default_gallery'][ $attachment_id ] ) ) {
							$css_class = 'pdp-video';
						}
						if ( 0 === $img_key ) {
							$html .= commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, true, $index++, $css_class );
						} else {
							$html .= commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, false, $index++, $css_class );
						}
					}
					if ( count( $cgkit_gallery ) > 1 ) {
						echo $html; // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
					}
				}
			} else {
				if ( $post_thumbnail_id ) {
					$html = commercegurus_get_thumbnail_attributes_gallery_image_html( $post_thumbnail_id, true, $index++ );
				}
				$attachment_ids = $product->get_gallery_image_ids();
				if ( $attachment_ids && $product->get_image_id() ) {
					foreach ( $attachment_ids as $attachment_id ) {
						$html .= commercegurus_get_thumbnail_attributes_gallery_image_html( $attachment_id, false, $index++ );
					}
				}
				if ( count( $attachment_ids ) ) {
					echo $html; // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
				}
			}
			?>
		</ul>
		<?php if ( isset( $options['pdp_thumb_arrows'] ) && 1 === (int) $options['pdp_thumb_arrows'] ) { ?>
		<button class="swiper-button-next swiper-button-disabled" aria-label="Next slide"></button>
		<button class="swiper-button-prev swiper-button-disabled" aria-label="Previous slide"></button>
		<?php } ?>
	</div>

</div>
<?php do_action( 'commercekit_after_gallery' ); ?>
</div>
<div id="cgkit-pdp-gallery-outside" style="height:0px;"></div>
<?php if ( $pdp_attributes_lightbox ) { ?>
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true" id="pswp">
	<div class="pswp__bg"></div>
	<div class="pswp__scroll-wrap">
		<div class="pswp__container">
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
		</div>
		<div class="pswp__ui pswp__ui--hidden">
			<div class="pswp__top-bar">
				<div class="pswp__counter"></div>
				<button class="pswp__button pswp__button--close" aria-label="Close (Esc)"></button>
				<button class="pswp__button pswp__button--share" aria-label="Share"></button>
				<button class="pswp__button pswp__button--fs" aria-label="Toggle fullscreen"></button>
				<button class="pswp__button pswp__button--zoom" aria-label="Zoom in/out"></button>
				<div class="pswp__preloader">
					<div class="pswp__preloader__icn">
						<div class="pswp__preloader__cut">
							<div class="pswp__preloader__donut"></div>
						</div>
					</div>
				</div>
			</div>
		<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
			<div class="pswp__share-tooltip"></div>
		</div>
		<button class="pswp__button pswp__button--arrow--left" aria-label="Previous (arrow left)"></button>
		<button class="pswp__button pswp__button--arrow--right" aria-label="Next (arrow right)">
		</button>
		<div class="pswp__caption">
			<div class="pswp__caption__center"></div>
		</div>
		</div>
	</div>
</div>
<?php } ?>

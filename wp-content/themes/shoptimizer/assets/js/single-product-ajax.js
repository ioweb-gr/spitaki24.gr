// Shoptimizer ajax add to cart js.

document.addEventListener( 'DOMContentLoaded', function() {
	var cart_forms = document.querySelectorAll( '.summary form.cart' );
	cart_forms.forEach( function( cart_form ) {
		cart_form.addEventListener( 'submit', function( event ) {
			var parent_elem = cart_form.closest( '.product.type-product' );
			if ( ! parent_elem ) {
				return;
			}
			if ( parent_elem.classList.contains( 'product-type-external' ) || parent_elem.classList.contains( 'product-type-subscription' ) || parent_elem.classList.contains( 'product-type-variable-subscription' ) || parent_elem.classList.contains( 'product-type-grouped' ) ) {
				return;
			}
			event.preventDefault();

			var atc_elem = cart_form.querySelector( '.single_add_to_cart_button' );
			var formData = new FormData( cart_form );

			if ( atc_elem.value ) {
				formData.append( 'add-to-cart', atc_elem.value );
			}
			atc_elem.classList.remove( 'added' );
			atc_elem.classList.remove( 'not-added' );
			atc_elem.classList.add( 'loading' );

			// Trigger adding to cart event
			var wce_add_cart = new Event( 'adding_to_cart' );
			document.body.dispatchEvent( wce_add_cart );

			fetch( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'shoptimizer_pdp_ajax_atc' ), {
				method: 'POST',
				body: formData,
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			} ).then( function( resp ) {
				if ( ! resp ) {
					return;
				}
				if ( 'yes' === wc_add_to_cart_params.cart_redirect_after_add ) {
					window.location = wc_add_to_cart_params.cart_url;
					return;
				}
				return resp.json();
			} ).then( resp => {
				if ( ! resp ) {
					return;
				}

				var cur_page = window.location.toString();
				cur_page = cur_page.replace( 'add-to-cart', 'added-to-cart' );

				// Handle error cases
				if ( resp.error && resp.product_url ) {
					window.location = resp.product_url;
					return;
				}

				atc_elem.classList.remove( 'loading' );

				// Check for error notices in both legacy and new format
				var hasError = (resp.notices && resp.notices.indexOf( 'error' ) > -1) || 
				              (resp.fragments && resp.fragments.notices && resp.fragments.notices.indexOf( 'error' ) > -1);

				if ( hasError ) {
					document.body.insertAdjacentHTML( 'beforeend', resp.notices || resp.fragments.notices );
					atc_elem.classList.add( 'not-added' );
				} else {
					atc_elem.classList.add( 'added' );
					
					// Handle fragments for both legacy and new format
					var fragments = resp.fragments || resp;
					if (fragments) {
						jQuery.each(fragments, function(key, value) {
							jQuery(key).replaceWith(value);
						});
					}
					
					// Trigger fragment refresh
					jQuery(document.body).trigger('wc_fragments_refreshed');
					
					// Open cart drawer
					document.querySelector('body').classList.add('drawer-open');
					
					// Trigger added_to_cart event with appropriate data
					var cartHash = resp.cart_hash || (resp.data && resp.data.cart_hash);
					jQuery(document.body).trigger('added_to_cart', [fragments, cartHash]);
				}
			} ).catch(error => {
				console.error('Error:', error);
				atc_elem.classList.remove('loading');
				atc_elem.classList.add('not-added');
			});
		} );
	} );
} );

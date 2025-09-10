/* Javascript Document */
function showWishlistPopup(message){
	if( document.querySelector('#commercekit-wishlist-popup') ){
		var popup = document.querySelector('#commercekit-wishlist-popup');
		popup.innerHTML = message;
		popup.style.display = 'block';
		setTimeout( function(){ document.querySelector('#commercekit-wishlist-popup').style.display = 'none';}, 2000);
	}
}
document.querySelectorAll('.commercekit-save-wishlist').forEach(function(object){
	object.setAttribute('href', '#');
});
if( document.querySelector('.commercekit-wishlist') ){
	document.querySelector('body').insertAdjacentHTML('afterbegin', '<div id="commercekit-wishlist-popup" style="display: none;"></div>');
}
document.querySelector('body').addEventListener('click', function(event){
	$this = event.target.closest('a');
	if( $this && $this.classList.contains('commercekit-save-wishlist') ){
		event.preventDefault();
		processWishlistAction($this, 'commercekit_save_wishlist');
	}
	if( $this && $this.classList.contains('commercekit-remove-wishlist') ){
		event.preventDefault();
		processWishlistAction($this, 'commercekit_remove_wishlist');
	}
});
document.querySelector('body').addEventListener('click', function(event){
	$this = event.target;
	$thisa = event.target.closest('a');
	if( $this.classList.contains('commercekit-remove-wishlist2') || ( $thisa && $thisa.classList.contains('commercekit-remove-wishlist2') ) ){
		event.preventDefault();
		if( $this.classList.contains('processing') ) return true;
		var ajax_nonce = '';
		if( commercekit_ajs.ajax_nonce != 1 ){
			return true;
		} else {
			var nonce_input = document.querySelector( '#commercekit_nonce' );
			if ( nonce_input ) {
				ajax_nonce = nonce_input.value;
			}
		}
		$this.classList.add('processing');
		var product_id = $this.getAttribute('data-product-id');
		var wpage = $this.getAttribute('data-wpage');
		var formData = new FormData();
		formData.append('product_id', product_id);
		formData.append('wpage', wpage);
		formData.append('type', 'list');
		formData.append('reload', '1');
		formData.append('commercekit_nonce', ajax_nonce);
		fetch( commercekit_ajs.ajax_url + '=commercekit_remove_wishlist', {
			method: 'POST',
			body: formData,
		}).then(response => response.json()).then( json => {
			if( json.status == 1 ){
				document.querySelector('#commercekit-wishlist-shortcode').innerHTML = json.html;
			}
			showWishlistPopup(json.message);
		});
	}
	if( $this.classList.contains('commercekit-wishlist-cart') ){
		event.preventDefault();
		var ajax_nonce = '';
		if( commercekit_ajs.ajax_nonce != 1 ){
			return true;
		} else {
			var nonce_input = document.querySelector( '#commercekit_nonce' );
			if ( nonce_input ) {
				ajax_nonce = nonce_input.value;
			}
		}
		$this.setAttribute('disabled', 'disabled');
		var product_id = $this.getAttribute('data-product-id');
		var formData = new FormData();
		formData.append('product_id', product_id);
		formData.append('commercekit_nonce', ajax_nonce);
		fetch( commercekit_ajs.ajax_url + '=commercekit_wishlist_addtocart', {
			method: 'POST',
			body: formData,
		}).then(response => response.json()).then( json => {
			$this.removeAttribute('disabled');
			showWishlistPopup(json.message);
			var wc_fragment = new Event('wc_fragment_refresh');
			document.body.dispatchEvent(wc_fragment);
		});
	}
});
var cgkit_wlists = document.querySelectorAll('.commercekit-wishlist:not(.no-wsl-update)');
if( cgkit_wlists.length > 0 ){
	var wlist_mids = []
	var wlist_fids = []
	cgkit_wlists.forEach(function(wlist){
		var pid = wlist.getAttribute('data-product-id');
		if( pid ){
			if( wlist.classList.contains('mini') ){
				wlist_mids.push(pid);
			} else if( wlist.classList.contains('full') ) {
				wlist_fids.push(pid);
			}
		}
	});
	if( wlist_mids.length > 0 || wlist_fids.length > 0 ){
		var formData = new FormData();
		formData.append('wlist_mids', wlist_mids.join(','));
		formData.append('wlist_fids', wlist_fids.join(','));
		fetch( commercekit_ajs.ajax_url + '=commercekit_wishlist_update', {
			method: 'POST',
			body: formData,
		}).then(response => response.json()).then( json => {
			if( json.wlists.length > 0 ){
				json.wlists.forEach(wlist => {
					document.querySelectorAll('.commercekit-wishlist'+wlist.cls+'[data-product-id="'+wlist.id+'"]').forEach(function(wlist_div){
						wlist_div.innerHTML = wlist.html;
					})
				});
			}
		});
	}
}
function processWishlistAction($this, action){
	if( $this.classList.contains('processing') ) return true;
	var ajax_nonce = '';
	if( commercekit_ajs.ajax_nonce != 1 ){
		return true;
	} else {
		var nonce_input = document.querySelector( '#commercekit_nonce' );
		if ( nonce_input ) {
			ajax_nonce = nonce_input.value;
		}
	}
	$this.classList.add('processing');
	var parent = $this.parentNode;
	var product_id = $this.getAttribute('data-product-id');
	var type = $this.getAttribute('data-type');
	var formData = new FormData();
	formData.append('product_id', product_id);
	formData.append('type', type);
	formData.append('commercekit_nonce', ajax_nonce);
	fetch( commercekit_ajs.ajax_url + '='+action, {
		method: 'POST',
		body: formData,
	}).then(response => response.json()).then( json => {
		if( json.status == 1 ){
			parent.innerHTML = json.html;
		}
		$this.classList.remove('processing');
		showWishlistPopup(json.message);
	});
}

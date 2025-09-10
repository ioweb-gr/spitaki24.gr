/* Javascript Document */
if ( commercekit_ajs.ajax_search == 1 ) {
	function ckit_ajax_search(){
		var minChar = commercekit_ajs.char_count;
		var maxHeight = 600;
		var deferRequestBy = 200;
		var cSuggestions = {};
		var oSuggestions = {};
		var inputs = document.querySelectorAll('input[type="search"]');
		inputs.forEach(function(input){
			var searchWidget = input.closest('.widget_search');
			var productWidget = input.closest('.woocommerce.widget_product_search');
			if( !searchWidget && !productWidget ) return false;
			var currentRequest = null;
			var currentCancel = null;
			var currentRequestOther = null;
			var currentCancelOther = null;
			var currentValue = '';
			var iwidth = input.offsetWidth;
			var form = input.closest('form');
			form.insertAdjacentHTML('beforeend', '<div class="commercekit-ajs-results'+( commercekit_ajs.fast_ajax_search == 1 ? ' fast-results-active' : '' )+'" style="display: none; width: '+iwidth+'px;"><div class="commercekit-ajs-suggestions" style="z-index: 9999; width: '+iwidth+'px;"></div></div>');
			input.setAttribute('autocomplete', 'off');
			input.classList.add('commercekit-ajax-search');
			
			if( commercekit_ajs.layout == 'product' ){
				var post_type = form.querySelector('input[name="post_type"]');
				if( post_type ){
					post_type.value = 'product';
				} else {
					var hidinput = document.createElement('input');
					hidinput.setAttribute('type', 'hidden');
					hidinput.setAttribute('name', 'post_type');
					hidinput.setAttribute('value', 'product');
					input.parentNode.insertBefore(hidinput, input.nextSibling);
				}
			} else {
				var post_type = form.querySelector('input[name="post_type"]');
				if( post_type ){
					post_type.parentNode.removeChild(post_type);
				}
			}

			form.addEventListener('submit', function(e){
				var formsugg = form.querySelector('.commercekit-ajs-suggestions');
				var active = formsugg.querySelector('.active');
				if( active ){
					e.preventDefault();
					active.querySelector('a').click();
					return false;
				}
			});
			input.addEventListener('keyup', function(e){
				formresult = form.querySelector('.commercekit-ajs-results');
				formsugg = form.querySelector('.commercekit-ajs-suggestions');
				var code = (e.keyCode || e.which);
				if (code === 37 || code === 38 || code === 39 || code === 40 || code === 27 || code === 13 || input.value.length < minChar ) {
					if( code === 27 ) {
						clearTimeout(currentRequest);
						clearTimeout(currentRequestOther);
						ckCloseAllSuggestions();
					} else if( code === 38 || code === 40 || code === 13 ) {
						var result = ckAjaxSearchKeyboardAccess(code, formsugg, input);
						if( result == false ){
							e.preventDefault();
							return;
						}
					} else {
						return;
					}
				} else {
					clearTimeout(currentRequest);
					clearTimeout(currentRequestOther);
					ckCloseAllSuggestions();
					formsugg.innerHTML = '';
					formresult.style.display = 'none';
					var canViewOther = false;

					if( commercekit_ajs.ajax_nonce != 1 ){
						return;
					}
					var ajax_nonce = '';
					var nonce_input = document.querySelector( '#commercekit_nonce' );
					if ( nonce_input ) {
						ajax_nonce = nonce_input.value;
					}
					if( cSuggestions[input.value] !== undefined ){
						formsugg.innerHTML = cSuggestions[input.value];
						ckPrepareDynamicSuggestions(input, formresult, formsugg);
						canViewOther = true;
					} else {
						if( currentValue == input.value ){
							return;
						}
						currentRequest = setTimeout(function(){
							currentValue = input.value;
							input.setAttribute('style', 'background-image: url(' + commercekit_ajs.loader_icon + '); background-repeat: no-repeat; background-position:50% 50%;');
							var url = commercekit_ajs.ajax_url_product + '&query=' + input.value + '&commercekit_nonce=' + ajax_nonce;
							if( currentCancel ){
								currentCancel.abort();
							}
							currentCancel = new AbortController();
							fetch(url, {signal: currentCancel.signal}).then(response => response.json()).then(json => {
								input.setAttribute('style', 'background-image: none; background-repeat: no-repeat; background-position:50% 50%;');
								var html = '';
								canViewOther = true;
								var noResult = 0;
								if( json.suggestions.length == 0 ) {
									html = '<div class="autocomplete-no-suggestion">'+commercekit_ajs.no_results_text+'</div>';
									cSuggestions[input.value] = html;
									noResult = 1;
								} else {
									json.suggestions.forEach(suggestion => {
										html += '<div class="autocomplete-suggestion">'+suggestion.data+'</div>';
									});
									var total_html = ' ('+json.result_total+')';
									if( commercekit_ajs.fast_ajax_search == 1 ){
										total_html = '';
									}
									html += '<div class="commercekit-ajs-view-all-holder"><a class="commercekit-ajs-view-all" href="'+json.view_all_link+'">'+commercekit_ajs.view_all_text+total_html+'</a></div>';
									cSuggestions[input.value] = html;
								}
								if( oSuggestions[input.value] !== undefined ){
									formsugg.innerHTML = cSuggestions[input.value] + oSuggestions[input.value];
									canViewOther = false;
								} else {
									formsugg.innerHTML = cSuggestions[input.value];
								}
								ckPrepareDynamicSuggestions(input, formresult, formsugg);
								if( commercekit_ajs.ajax_nonce == 1 ){
									var url2 = commercekit_ajs.ajax_url + '=commercekit_search_counts&query='+input.value+'&no_result='+noResult+'&commercekit_nonce='+ajax_nonce;
									fetch(url2).then(response => response.json()).then(json => {}).catch(function(e){});
								}
							}).catch(function(e){});
						}, deferRequestBy);
					}

					if( commercekit_ajs.ajs_other_results == 1 ){
						if( oSuggestions[input.value] !== undefined ){
							if( canViewOther ){
								formsugg.insertAdjacentHTML('beforeend', oSuggestions[input.value]);
								canViewOther = false;
							}
						} else {
							if( currentValue == input.value ){
								return;
							}
							currentRequestOther = setTimeout(function(){
								currentValue = input.value;
								var url = commercekit_ajs.ajax_url_post + '&query=' + input.value + '&commercekit_nonce=' + ajax_nonce;
								if( currentCancelOther ){
									currentCancelOther.abort();
								}
								currentCancelOther = new AbortController();
								fetch(url, {signal: currentCancelOther.signal}).then(response => response.json()).then(json => {
									var html = '';
									if( json.suggestions.length == 0 ) {
										html = '<div class="autocomplete-no-suggestion">'+commercekit_ajs.no_other_text+'</div>';
										oSuggestions[input.value] = html;
									} else {
										html += '<div class="commercekit-ajs-other-result">'+commercekit_ajs.other_result_text+'</div>';
										json.suggestions.forEach(suggestion => {
											html += '<div class="autocomplete-suggestion">'+suggestion.data+'</div>';
										});
										var total_html = ' ('+json.result_total+')';
										if( commercekit_ajs.fast_ajax_search == 1 ){
											total_html = '';
										}
										html += '<div class="commercekit-ajs-view-all-holder"><a class="commercekit-ajs-view-all" href="'+json.view_all_link+'">'+commercekit_ajs.other_all_text+total_html+'</a></div>';
										oSuggestions[input.value] = html;
									}
									if( canViewOther ){
										formsugg.insertAdjacentHTML('beforeend', oSuggestions[input.value]);
										canViewOther = false;
									}
								}).catch(function(e){});
							}, deferRequestBy);
						}
					}

				}
			});
			input.addEventListener('focus', function(e){
				var input = e.target;
				if( input.classList.contains('commercekit-ajax-search') && input.value.length >= commercekit_ajs.char_count ){
					var form = input.closest('form');
					var formresult = form.querySelector('.commercekit-ajs-results');
					var formsugg = form.querySelector('.commercekit-ajs-suggestions');
					if( formsugg.querySelectorAll('.autocomplete-suggestion').length > 0 ){
						ckCloseAllSuggestions();
						if( formresult && formsugg ){
							ckPrepareDynamicSuggestions(input, formresult, formsugg);
						}
					} else {
						var keyup = new Event('keyup');
						input.dispatchEvent(keyup);
					}
				} else {
					var form = input.closest('form');
					if( form ){
						var formresult = form.querySelector('.commercekit-ajs-results');
						if( formresult ){
							formresult.style.display = 'none';
						}
						var formsugg = form.querySelector('.commercekit-ajs-suggestions');
						if( formsugg ){
							formsugg.innerHTML = '';
						}
					}
				}
			});
		});
		document.addEventListener('click', function(e){
			if( !e.target.classList.contains('commercekit-ajs-suggestions') && !e.target.classList.contains('commercekit-ajax-search') ){
				ckCloseAllSuggestions();
			}
		});
		document.addEventListener('keydown', function(e){
			var code = ( e.keyCode || e.which );
			if( code === 27 ){
				var parentEl = e.target.closest('.commercekit-ajs-suggestions');
				if( e.target.classList.contains('commercekit-ajs-suggestions') || e.target.classList.contains('commercekit-ajax-search') || parentEl ){
					e.preventDefault();
					e.target.blur();
				}
				ckCloseAllSuggestions();
			}
		});
	}
	ckit_ajax_search();
}
function ckCloseAllSuggestions(){
	document.querySelectorAll('.commercekit-ajs-results').forEach(function(results){
		if( results.style.display != 'none' ){
			results.style.display = 'none';
		}
	});
}
function ckPrepareDynamicSuggestions(input, formresult, formsugg){
	var iwidth = input.offsetWidth;
	formsugg.style.width = iwidth + 'px';
	formresult.style.width = iwidth + 'px';
	formresult.style.display = 'block';

	var box = formresult.getBoundingClientRect();
	var maxHeight = window.innerHeight - box.top - 10;
	if ( maxHeight < 280 ) {
		maxHeight = 280;
	}
	formresult.style.maxHeight = maxHeight + 'px';
}
function ckAjaxSearchKeyboardAccess(code, formsugg, input){
	input.selectionStart = input.selectionEnd = input.value.length;
	if( formsugg.style.display == 'block' ){
		var active = formsugg.querySelector('.active');
		if( ! active ){
			if( !formsugg.firstChild.classList.contains('commercekit-ajs-other-result-wrap')){
				active = formsugg.firstChild;
			} else {
				if( formsugg.firstChild.nextSibling )
					active = formsugg.firstChild.nextSibling;
			}
		} else {
			if( code === 38 ){
				active.classList.remove('active');
				if( active.previousSibling ){
					if( !active.previousSibling.classList.contains('commercekit-ajs-other-result-wrap')){
						active = active.previousSibling;
					} else {
						if( active.previousSibling.previousSibling ){
							active = active.previousSibling.previousSibling;
						} else if( formsugg.lastChild ) {
							active = formsugg.lastChild;
						}
					}
				} else if( formsugg.lastChild ) {
					active = formsugg.lastChild;
				}
			}
			if( code === 40 ){
				active.classList.remove('active');
				if( active.nextSibling ){
					if( !active.nextSibling.classList.contains('commercekit-ajs-other-result-wrap')){
						active = active.nextSibling;
					} else {
						if( active.nextSibling.nextSibling ){
							active = active.nextSibling.nextSibling;
						} else if( formsugg.firstChild ){
							active = formsugg.firstChild;
						}
					}
				} else if( formsugg.firstChild ){
					active = formsugg.firstChild;
				}
			}
		}
		if( ( code === 38 || code === 40 ) && active ){
			active.classList.add('active');
		} else if( code === 13 && active ) {
			return false;
		}
		return true;
	}
}

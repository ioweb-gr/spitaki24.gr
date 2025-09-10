// Main Shoptimizer js.
var canRunClickFunc = true;
function makeTouchstartWithClick( event ) {
	if ( ! canRunClickFunc ) {
	return false;
}
	setTimeout( function() {
 canRunClickFunc = true;
}, 700 );
	var elem  = event.target;
	var elemp = elem.closest( '.close-drawer' );
	if ( elem.classList.contains( 'close-drawer' ) || elemp ) {
		document.querySelector( 'body' ).classList.remove( 'filter-open' );
		document.querySelector( 'body' ).classList.remove( 'mobile-toggled' );
		return;
	}
	var elemp = elem.closest( '.menu-toggle' );
	if ( elem.classList.contains( 'menu-toggle' ) || elemp ) {
		event.stopPropagation();
		event.preventDefault();
		document.querySelector( 'body' ).classList.add( 'mobile-toggled' );
		return;
	}
	if ( elem.classList.contains( 'mobile-overlay' ) ) {
		event.preventDefault();
		event.stopPropagation();
		document.querySelector( 'body' ).classList.remove( 'filter-open' );
		document.querySelector( 'body' ).classList.remove( 'mobile-toggled' );
		return;
	}
}
document.addEventListener( 'DOMContentLoaded', function() {
	window.addEventListener( 'load', function( event ) {
		var vh = window.innerHeight * 0.01;
		document.documentElement.style.setProperty( '--vh', vh + 'px' );
		makeOnTouchTapped();
	} );
	window.addEventListener( 'resize', function( event ) {
		var vh = window.innerHeight * 0.01;
		document.documentElement.style.setProperty( '--vh', vh + 'px' );
		makeOnTouchTapped();
	} );
	window.addEventListener( 'click', function( event ) {
		var elem  = event.target;
		var elemp = elem.closest( '.shoptimizer-mobile-filter' );
		if ( elem.classList.contains( 'shoptimizer-mobile-toggle' ) || elemp ) {
			event.stopPropagation();
			event.preventDefault();
			document.querySelector( 'body' ).classList.toggle( 'filter-open' );
			return;
		}
		var elemp = elem.closest( '.mobile-search-toggle' );
		if ( elem.classList.contains( 'mobile-search-toggle' ) || elemp ) {
			event.stopPropagation();
			event.preventDefault();
			document.querySelector( 'body' ).classList.toggle( 'm-search-toggled' );
			return;
		}
		makeTouchstartWithClick( event );
		canRunClickFunc = false;
	} );
	window.addEventListener( 'touchstart', function( event ) {
		makeTouchstartWithClick( event );
		canRunClickFunc = false;
	} );

	var cart_form = document.querySelector( '.single-product form.cart' );
	if ( cart_form ) {
		cart_form.setAttribute( 'id', 'sticky-scroll' );
	}
	var buttons = document.querySelectorAll( '.button-wrapper' );
	if ( buttons ) {
		buttons.forEach( function( buttons ) {
			buttons.classList.add( 'shoptimizer-size-guide' );
		} );
	}
	var lifws = document.querySelectorAll( 'li.full-width' );
	if ( lifws ) {
		lifws.forEach( function( lifw ) {
			lifw.addEventListener( 'mouseenter', function( event ) {
				var scontent = document.querySelector( '.site' );
				if ( scontent ) {
					scontent.classList.add( 'overlay' );
				}
			} );
			lifw.addEventListener( 'mouseleave', function( event ) {
				var scontent = document.querySelector( '.site' );
				if ( scontent ) {
					scontent.classList.remove( 'overlay' );
				}
			} );
		} );
	}
	var mobileContainer = document.querySelector( '.col-full-nav' );
	if ( mobileContainer ) {
		var mcparent = mobileContainer.closest( '.mobile-toggled' );
		if ( mcparent ) {
			mobileContainer.addEventListener( 'click', function( event ) {
				document.querySelector( 'body' ).classList.remove( 'mobile-toggled' );
			} );
			mobileContainer.addEventListener( 'touchstart', function( event ) {
				document.querySelector( 'body' ).classList.remove( 'mobile-toggled' );
			} );
		}
	}
	var carets = document.querySelectorAll( 'body .main-navigation ul.menu li.menu-item-has-children .caret' );
	if ( carets ) {
		carets.forEach( function( caret ) {
			caret.addEventListener( 'click', function( event ) {
				event.target.closest( 'li' ).classList.toggle( 'dropdown-open' );
				event.preventDefault();
			} );
		} );
	}
	var childs = document.querySelectorAll( '.main-navigation ul.menu li.menu-item-has-children > a' );
	if ( childs ) {
		childs.forEach( function( child ) {
			if ( '#' === child.getAttribute( 'href' ) ) {
				child.addEventListener( 'click', function( event ) {
					event.target.closest( 'li' ).classList.toggle( 'dropdown-open' );
					event.preventDefault();
				} );
			}
		} );
	}
	var sctop = document.querySelector( '.logo-mark a' );
	if ( sctop ) {
		sctop.addEventListener( 'click', function( event ) {
			var elem  = event.target;
			event.preventDefault();
			window.scroll( {
				behavior: 'smooth',
				left: 0,
				top: 0
			} );
		} );
	}
	var vsctops = document.querySelectorAll( 'a.variable-grouped-sticky' );
	if ( vsctops ) {
		vsctops.forEach( function( vsctop ) {
			if ( '#' !== vsctop.getAttribute( 'href' ) ) {
				vsctop.addEventListener( 'click', function( event ) {
					var elem  = document.querySelector( vsctop.getAttribute( 'href' ) );
					if ( elem ) {
						event.preventDefault();
						window.scroll( {
							behavior: 'smooth',
							left: 0,
							top: elem.offsetTop - 80
						} );
					}
				} );
			}
		} );
	}

	var lazyImages = [].slice.call( document.querySelectorAll( 'img.lazy' ) );
	var active = false;

	const lazyLoad = function() {
		if ( false === active ) {
			active = true;

			setTimeout( function() {
				lazyImages.forEach( function( lazyImage ) {
					if ( ( lazyImage.getBoundingClientRect().top <= window.innerHeight && 0 <= lazyImage.getBoundingClientRect().bottom ) && 'none' !== getComputedStyle( lazyImage ).display ) {
						lazyImage.src = lazyImage.dataset.src;
						if ( lazyImage.dataset.srcset ) {
							lazyImage.srcset = lazyImage.dataset.srcset;
						}
						lazyImage.classList.remove( 'lazy' );

						lazyImages = lazyImages.filter( function( image ) {
							return image !== lazyImage;
						} );

						if ( 0 === lazyImages.length ) {
							document.removeEventListener( 'scroll', lazyLoad );
							window.removeEventListener( 'resize', lazyLoad );
							window.removeEventListener( 'orientationchange', lazyLoad );
						}
					}
				} );

				active = false;
			}, 200 );
		}
	};

	document.addEventListener( 'scroll', lazyLoad );
	window.addEventListener( 'resize', lazyLoad );
	window.addEventListener( 'orientationchange', lazyLoad );
	if ( jQuery ) {
		jQuery( 'body' ).on( 'added_to_cart', function( event, fragments, cart_hash ) {
			document.querySelector( 'body' ).classList.remove( 'mobile-toggled' );
		} );
	}
	var menus = document.querySelectorAll( '#menu-primary-menu li.menu-item-has-children' );
	if ( menus ) {
		menus.forEach( function( menu ) {
			menu.addEventListener( 'mouseenter', function() {
				setTimeout( function() { updateMenuAriaExpanded(); }, 50 );
			} );
			menu.addEventListener( 'mouseleave', function() {
				setTimeout( function() { updateMenuAriaExpanded(); }, 50 );
			} );
		} );
	}
	document.addEventListener( 'focusin', function() {
		setTimeout( function() { updateMenuAriaExpanded(); }, 50 );
	} );
	document.addEventListener( 'focusout', function() {
		setTimeout( function() { updateMenuAriaExpanded(); }, 50 );
	} );
	document.addEventListener( 'keydown', function( event ) {
		cartDrawerTrapTabKey( event );
	} );
} );
function makeOnTouchTapped() {
	if ( 992 < window.innerWidth ) {
		if ( 'ontouchstart' in window ) {
			document.addEventListener( 'touchstart', function() {}, true );
			document.addEventListener( 'click', function( event ) {
				var elem = event.target;
				if ( ! elem.classList.contains( 'menu-item-has-children' ) ) {
					var parent = elem.closest( '.menu-item' );
				 	if ( ! parent || ! parent.classList.contains( 'menu-item-has-children' ) ) {
						return true;
					}
					elem = parent;
				}

				var menus = document.querySelectorAll( '.menu-item-has-children.tapped' );
				if ( menus ) {
					menus.forEach( function( menu ) {
						if ( menu !== elem ) {
							menu.classList.remove( 'tapped' );
						}
					} );
				}
				if ( ! elem.classList.contains( 'tapped' ) ) {
					elem.classList.add( 'tapped' );
					event.preventDefault();
					return false;
				} else {
					return true;
				}
			}, true );
		}
	}
}
function handleFirstTab( e ) {
    if ( 9 === e.keyCode ) {
        document.body.classList.add( 'keyboard-active' );
        window.removeEventListener( 'keydown', handleFirstTab );
    }
}
window.addEventListener( 'keydown', handleFirstTab );
function cartDrawerTrapTabKey( event ) {
	var element = document.querySelector( 'body.drawer-open #shoptimizerCartDrawer' );
	if ( element ) {
		if ( event.key.toLowerCase() == 'escape' ) {
			document.querySelector( 'body' ).classList.remove( 'drawer-open' );
			return;
		} else if ( event.key.toLowerCase() == 'tab' ) {
			var inputs = ['a[href]','area[href]','input:not([disabled]):not([type="hidden"]):not([aria-hidden])','select:not([disabled]):not([aria-hidden])','textarea:not([disabled]):not([aria-hidden])','button:not([disabled]):not([aria-hidden])','iframe','object','embed','[contenteditable]','[tabindex]:not([tabindex^="-"])'];
			var nodes = element.querySelectorAll(inputs);
			var focusables = Array( ...nodes );
			if ( focusables.length === 0 ) {
				return;
			}
			focusables = focusables.filter( ( node ) => {
				return node.offsetParent !== null;
			} );
			if ( ! element.contains( document.activeElement ) ) {
				focusables[0].focus();
			} else {
				var focusedIndex = focusables.indexOf( document.activeElement );
				if ( event.shiftKey && focusedIndex === 0 ) {
					focusables[ focusables.length - 1 ].focus();
					event.preventDefault();
				}
				if ( ! event.shiftKey && focusables.length > 0 && focusedIndex === focusables.length - 1 ) {
					focusables[0].focus();
					event.preventDefault();
				}
			}
		}
	}
}
function updateMenuAriaExpanded(){
	var menus = document.querySelectorAll( '#menu-primary-menu li > .sub-menu-wrapper' );
	if ( menus ) {
		menus.forEach( function( menu ) {
			var parent = menu.closest('li.menu-item-has-children');
			if ( parent ) {
				if ( window.getComputedStyle( menu ).visibility !== 'hidden' ) {
					if ( parent.hasAttribute( 'aria-expanded' ) ) {
						parent.setAttribute( 'aria-expanded', 'true' );
					}
				} else {
					if ( parent.hasAttribute( 'aria-expanded' ) ) {
						parent.setAttribute( 'aria-expanded', 'false' );
					}
				}
			}
		} );
	}
}

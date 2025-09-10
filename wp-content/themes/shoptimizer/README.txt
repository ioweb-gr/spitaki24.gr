=== Shoptimizer ===
Requires at least: 6
Tested up to: 6.5
Version: 2.8.13
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: e-commerce, two-columns, left-sidebar, right-sidebar, custom-background, custom-colors, custom-header, custom-menu, featured-images, full-width-template, threaded-comments, accessibility-ready, rtl-language-support, footer-widgets, sticky-post, theme-options

Shoptimizer is the perfect theme for your next WooCommerce project designed around speed and conversions.

== Description ==

For more information about Shoptimizer please go to https://www.commercegurus.com/shoptimizer/

== Installation ==

See: Appearance > Shoptimizer Help. Or visit: https://www.commercegurus.com/docs/shoptimizer-theme/

== Copyright ==

This theme, like WordPress, is licensed under the GPL.
Use it to make something cool, have fun, and share what you've learned with others.

Shoptimizer is based on Underscores http://underscores.me/

Resetting and rebuilding styles have been helped along thanks to the fine work of
Eric Meyer http://meyerweb.com/eric/tools/css/reset/index.html
along with Nicolas Gallagher and Jonathan Neal http://necolas.github.com/normalize.css/

Rivolicons License: Created by Hadrien Boyer and licensed under Creative Commons 4.0: https://creativecommons.org/licenses/by-sa/4.0/
Images License: GNU General Public License v2 or later.


== Changelog ==

2.8.13 - 30-06-2025
* Fix: Improves Elementor Pro compatibility by fixing styling issues with the cart and checkout widgets.
* Accessibility: Adds a reduced motion theme option for users who prefer less animation.

2.8.12 - 28-05-2025
* Fix: Incorrect percentage calculation caused -0% discount.
* Fix: Main navigation bar should not be sticky on product pages.
* Compatability: Resolve display issues when YITH Ajax Product Filters plugin injects HTML into product titles.

2.8.11 - 06-05-2025
* Fix: Full width header mega dropdown layout issue when sticky navigation is disabled.
* Fix: Navigation hover overlay issue on product pages.
* Fix: Added defensive checks to prevent PHP errors when displaying product review counts.

2.8.10 - 30-04-2025
* Fix: Dark opacity overlay issue when navigation bar is hovered.

2.8.9 - 18-04-2025
* Tweak: Mega menu overlay display when the menu is sticky.

2.8.7 - 18-04-2025
* Fix: Scrolling down the mega menu when at the footer caused the page height to grow.
* Fix: Added apply_filters to the sale badges output.
* Tweak: Elementor Full Width template now doesn't inherit theme container padding.
* Tweak: Styling fixed when applying the nolink class to top level main menu items.
* Compatability: AJAX add-to-cart functionality on PDPs when CheckoutWC is active.

2.8.6 - 25-03-2025
* Fix: Flickering when sticky mega menu dropdown is hovered over.
* Fix: Product Listings reviews count displayed as 0.
* Fix: Header top and bottom padding value reduced when Elementor Pro is active.
* Tweak: RTL Mobile swipe width adjustment to prevent overflow.

2.8.5 - 07-03-2025
* Fix: Resolved an “Invalid product” error in the cross-sells carousel by replacing direct WC_Product with wc_get_product().

2.8.4 - 07-03-2025
* New: Can now include below archive content on Product Tags and Product Brands listings.
* Fix: Additional checks applied to the get_rating_html function to prevent errors.
* Fix: Product thumbnails not appearing within the Admin > Products area.
* Fix: PDP lightbox z-index issues resolved.
* Fix: Legacy gallery CSS re-applied if not using the gallery block.
* Fix: Improved styling for deeply nested levels of dropdown navigation items.
* Fix: Removed shoptimizer_elementor_pro_styling function when Elementor Pro is active.
* Fix: When "Sold Individually" is set, product price now appears in the mini cart.
* Fix: Blog heading closing </h1> tag wasn't rendering.
* Tweak: Added autoprefixation CSS rules e.g. -webkit-mask-image for older browsers.
* Tweak: Fixed 'Clear' button style on PDPs.
* Tweak: PLP product card images when the Imagify plugin is enabled.
* Accessibility: Added role="button" to shopping cart link.
* Future: Work started on rebuilding WooCommerce functions behind a feature flag.

2.8.3 - 20-09-2024
* Fix: Improved native WooCommerce info bar styling.
* Fix: PDP gallery modal z-index issue.
* Fix: Undefined array key for shoptimizer_layout_wrapper.
* Fix: Germanized styling on checkout page improved.
* Fix: Previous/Next on PDPs now excludes out of stock products.
* Fix: Checkout heading function now includes an additional check if it exists.
* Fix: RTL improvements.

2.8.2 - 18-09-2024
* Fix: Numerous CSS tweaks and improvements after the big 2.8 release.
* Tweak: PDP Cross-sells carousel now includes a call to action button.
* Accessibility: Search modal now closable by pressing the ESC key.

2.8.1 - 13-09-2024
* New: Can now hide cross-sells from the cart page.
* Fix: Improved build process for unminified theme CSS files.
* Fix: Mini cart close icon hidden in Safari.
* Fix: Mobile mini cart width fix for small viewports.
* Fix: Cart icon hidden on desktop when 'Header 5' layout selected.
* Tweak: Cross-sells PDP carousel now RTL compatible.

2.8.0 - 12-09-2024
* Major: Theme CSS completely rebuilt into modular components resulting in a 27% reduction in CSS size.
* New: Product card images are now swipeable on mobile if a second gallery image is present and hoverable on desktop.
* New: Can now display a cross-sells carousel within the buy box on a PDP.
* New: Core Woo Product category widget now has an expand/collapse option (Appearance > Customize > Layout > Sidebars)
* Fix: Font display issue after upgrading to WP 6.6+.
* Fix: When editing a PDP in Elementor Pro, core Woo gallery now displays correctly within editor.
* Fix: Native caret arrow appeared in PDP short description accordion within some browsers.
* Fix: Ensure that single product ajax JS can fire on PDPs which use the "Mix and Match Products" plugin.
* Fix: Core WooCommerce product grid block now displays with a correct number of columns.
* Fix: Elementor Pro tablet and mobile grid settings now take priority over the theme.
* Fix: Mini cart and cart page quantity buttons removed if "Sell individually" selected.
* Performance: WooCommerce Subscriptions, German Market, and MailChimp for WP specific CSS will now only load if plugin is active.
* Tweak: Cart ajax update now includes a small delay to ensure that the quantity can be changed before refresh.
* Tweak: Theme modals now closeable by clicking/tapping outside of them.
* Tweak: Updates to Shoptimizer help content within setup area.
* Tweak: RTL Improvements.

2.7.9 - 07-06-2024
* CommerceKit required version update.

2.7.8 - 07-06-2024
* Fix: Add to cart CTA on product pages now triggers the sidebar cart again.
* Fix: Allow the added_to_cart event to be emitted first before the mini cart opens.
* Fix: Jetpack site-logo hook is deprecated.
* Fix: shoptimizer-dialog.js console warning.

2.7.7 - 31-05-2024
* New: Shoptimizer cart total shortcode and Elementor widget. Can now include cart total with the slideout sidebar within a custom-built header.
* Fix: Constant FILTER_SANITIZE_STRING is deprecated.
* Accessibility: Out of stock red color made slightly darker for color contrast.
* Accessibility: Improved keyboard focus states on PDPs.
* Accessibility: Theme modals (call back and search) now use native HTML <dialog> element.
* Accessibility: Cart in navigation bar now uses a <nav> element with an aria-label rather than a <ul>.
* Accessibility: Mobile filters close icon now uses a <button> element rather than a div.
* Performance: Elementor Pro, Germanized, WP Forms and YITH Filter specific CSS will now only load if plugin is active.
* Performance: Any CSS related to CommerceKit moved to the appropriate module within the plugin itself.
* Tweak: CommerceKit is now a recommended plugin rather than required.
* Tweak: General RTL improvements.

2.7.6 - 03-04-2024
* Fix: Sidebar mini cart thumbnails visibility issue when inner scroll active.
* Fix: Close icon visibility when size guides modal is active.
* Compatibility: Support for SEO Framework breadcrumbs.
* Tweak: Improved cross-sells display on mobile.
* Tweak: RTL improvements.

2.7.5 - 06-02-2024
* Tweak: Mobile left/right grid padding restored when 2-column grid is active.
* Tweak: Improved position of close icon within search modal.
* Tweak: Mobile scroll option now works within the category description area.
* Compatibility: Improved mobile grid spacing in Elementor Pro.
* Compatibility: One row header hover search color styling when Elementor Pro is active.
* Compatibility: YITH Filters plugin sidebar panel visibility on mobile.

2.7.4 - 31-01-2024
* New: Two-column mobile catalog layout (Appearance > Customize > Layout > General) now works when CommerceKit swatches are enabled on PLPs.
* New: Option to display an account icon on desktop when the header layout is set to: Logo / Navigation / Cart
* Tweak: Default WooCommerce block notifications styling improved.
* Accessibility: Search toggle icon now uses a button element rather than a link.
* Compatibility: Sidebar cart now works with the GTM4WP plugin.

2.7.3 - 15-12-2023
* Fix: Three column grid blog archive layout now displays correctly.
* Fix: Extra condition wrapped around the PLP ratings quantity so it displays only on the Shop and Catalog pages.
* Tweak: Removed the "Category:" prefix from archive titles by default.

2.7.2 - 13-12-2023
* Fix: Theme hover colors were being applied to Elementor buttons.
* Tweak: Center the rating count if product cards are center aligned.

2.7.1 - 01-12-2023
* Tweak: Adjustment to theme.json as block editor link underlines were appearing on some sites.

2.7.0 - 29-11-2023
* New: Customizer option to display reviews count next to stars on shop/catalog pages.
* New: Option to create a separate menu for mobile.
* Fix: The "Enable AJAX add to cart buttons on archives" WC option caused the single add to cart ajax event not to work when caching was active.
* Fix: Search results sidebar not displaying on mobile.
* Fix: Empty cart page styling resolved after WC7.8.x release.
* Fix: Swiping past the add to cart button caused the mobile menu to close.
* Fix: Cannot redeclare function error when ShopEngine plugin is active.
* Fix: Mega menu dropdowns double-tap issue on large tablets.
* UX: Improved focus-visible styling when navigating with the keyboard.
* UX: Make Inter the default font for new installations rather than IBM Plex Sans.
* Tweak: Cart quantity arrows on cart page: can now enter a custom value.
* Tweak: Cart quantity arrows: improved styling in iOS devices when it becomes 16px on focus.
* Tweak: Custom no search results page now works for all use cases.
* Tweak: Sidebar mini cart quantity JS improvements.
* Tweak: Single product add to cart JS improvements.
* Tweak: Checkout page product thumbnails now have a skip-lazy class to prevent duplication in certain instances.
* Tweak: Standardized gap when regular or skinny gallery layout selected.
* Tweak: Styling for alignwide and alignfull included.
* Tweak: Product long descriptions now order correctly on mobile, i.e. image first, then text.
* Tweak: Numerous RTL improvements.
* Compatibility: WooCommerce Product Recommendations style improvements.
* Compatibility: Support for the Advanced Woo Search plugin: https://wordpress.org/plugins/advanced-woo-search/
* Compatibility: Sendy Pro widget styling added.
* Compatibility: Initial theme.json version included (beta) for enhanced block editor controls.
* Accessibility: Primary menu dropdown items now have aria-expanded true/false labels when interacting with them.
* Accessibility: Sidebar cart now traps focus when interacting with it via the keyboard and can be closed with the Esc key.

2.6.7 - 29-05-2023
* Fix: Replaced deprecated use of get_page_by_title with WP_Query for the custom 404 and "No search results" pages.
* Tweak: Styling for a Password protected product improved.
* Compatibility: WooCommerce Google Analytics Pro by SkyVerge error resolved.

2.6.6 - 16-05-2023
* New: Update cart page quantity via ajax without a plugin. Customize > Layout > WooCommerce: Ajax update cart page quantity
* New: Option to display the default 'Shop' h1 heading. Customize > Layout > WooCommerce: Display shop heading
* New: Create a Custom "No Search Results" page.
* New: Create a Custom 404 Page.
* Fix: added_to_cart event not being fired within single-product-ajax.js.
* Fix: Quantity buttons on cart page now remain visible after updating quantity value.
* Fix: "The content of your post doesn’t match the template assigned to your post type" message within product admin area.
* Fix: "Site doesn’t include support for the "woocommerce/product-tab block" message within product admin area.
* Fix: Modal class changed to cg-modal to prevent conflicts with modals in other plugins.
* Compatibility: YITH Quick View plugin.
* UX: Mobile search toggle icon interaction is snappier now.
* UX: Cart page quantity selector now matches the mini cart style. This is also easier to interact with on mobile.
* Performance: Load single-product-ajax.js in the footer.
* Accessibility: GPSI suggestion: Remove aria-hidden="true" from PDP modal.
* SEO: Changed search modal heading from a h4 to a div.
* Tweak: Include "nolink" CSS class option within mega menu.
* Tweak: Improved hover intent menu targeting.
* Tweak: Added scroll snap for mobile products scrolling elements.
* Tweak: RTL improvements.
* Removed: "Full width contained" page template.

2.6.5 - 07-03-2023
* New: Sidebar cart quantity option. Customize > Header and Navigation > Cart.
* New: Hover intent theme option for less jarring sudden interactions with dropdown menus. Customize > Header and Navigation > Navigation
* Fix: Typography 2.0 PHP error resolved and updated Google Fonts list included.
* Fix: PDP button gap if only 1 in stock.
* Tweak: More robust sale discount calculation function.
* Tweak: Improved review reply styling.
* Tweak: One row header layout on the thank you page style corrections.
* Tweak: Removed lazy loading background image for below header category banner as this would typically already display above the fold.

2.6.4 - 31-01-2023
* New: Option to hide the "View cart" button in the sidebar mini cart. Customize > Header and Navigation > Cart.
* Fix: Hiding category description option now works again.
* Tweak: Theme now uses CSS Grid on blog and archive pages.
* Tweak: Lock icon added to cart and mini cart checkout buttons.
* Tweak: Mobile cart RTL improvements.

2.6.3 - 09-01-2023
* New: Customizer typography controls can now accept local custom font files.
* Fix: For variable items on sale, the sale % value now updates on PDPs if it differs between variations.
* Fix: Improved order of CSS loaded within Shoptimizer's child theme.
* Fix: Below category content WYSIWYG: solved empty <p> tag display issue.
* Compatibility: WooCommerce Product Videos plugin.
* Compatibility: Smart Search Pro plugin.
* Compatibility: Improved WooCommerce Composite Products Styling.
* Tweak: Added a reveal password toggle to the login and register form on the My Account page.
* Tweak: Cart total in nav bar displays large values better.
* Tweak: Add to cart CTA on PDP goes full width if quantity input isn't present.
* Tweak: RTL improvements.

2.6.2 - 12-09-2022
* Fix: Header layout bug which occurred when mobile search toggle option was active.

2.6.1 - 09-09-2022
* Fix: Bug when mobile filters button didn't stick to the top when certain conditions were active.
* Fix: Animated underlined links within mega menu on iPads required 2 taps before following the link.
* Fix: Category description text when switched off was only being hidden on the first page.
* UX: Accessibility improvements. Homepage now gets 98/100 in Google Lighthouse.
* Tweak: Standard 'On Sale' bubble style now matches the % version.
* Tweak: Mobile Cart layout option is no longer experimental and is now switched on by default on new installs.
* Tweak: Included optional open="open" parameter for the accordion shortcode.
* Tweak: Below header category banner now goes full width edge-to-edge.
* Tweak: 'Recently added' within search modal can now be toggled on/off in customizer.
* Tweak: Basic block styling CSS included if Gutenberg editor is active.

2.6.0 - 11-08-2022
* New: Related/Upsells/Cross-sells now scroll on small viewports. Can also mobile scroll WC shortcodes with optional "mobile-scroll" class.
* Fix: Elementor Pro editor triggered the sidebar cart.
* UX: Shop/Catalog mobile filters UX: 'Show Filters' button now sticks on scroll.
* UX: Catalog image hover effect now present only on non-touch devices.
* Tweak: Improved mobile styling across all key templates.
* Tweak: Improved sidebar cart styling. On mobile the sidebar cart is now full width.
* Tweak: Lots of RTL improvements.
* Tweak: My account order details styling improvements.
* Tweak: Out of stock label styling on product cards.
* Tweak: Search modal style adjustments.
* Tweak: Product categories widget: absolutely position the count.
* Tweak: Security lock icon added before 'Place order' button on checkout page.
* Tweak: In stock pulsing animation replaced by tick icon. Pulse was jerky on iOS devices due to a lack of support for animated pseudo-elements.
* Tweak: On PDPs if quantity is hidden, make the add to cart button fill the space.
* Tweak: In Firefox double arrows were appearing in the quantity field.
* Tweak: Product type "Mix and Match" excluded from PDP Ajax add to cart.

2.5.5 - 07-06-2022
* Fix: Search suggestions within search modal no longer cut, and modal is positioned at the top rather than centered.
* Tweak: On original sticky add to cart bar, rel="nofollow" added to CTA.

2.5.4 - 27-05-2022
* New: Empty mini cart widget area. You can now add custom content to this area.
* Improvement: Request a call back modal now includes product data.
* Accessibility: WAVE color contrast adjustments.
* Tweak: Mobile menu review styling.
* Tweak: In stock styling on PDPs.
* Fix: Better WooFunnels compatibility.
* Fix: Dokan z-index dropdown issue.

2.5.3 - 08-04-2022
* Fix: Ensure that checkout custom widget content appears after official PayPal plugin.
* Fix: Mega dropdown hover glitch when variable products are present in it.
* Tweak: Further RTL styling improvements.
* Tweak: CSS fix if Elementor is active on the Cart page.
* Tweak: Improved styling when using the WooCommerce Composite Products plugin.

2.5.2 - 11-03-2022
* Fix: Accordion shortcode now works if Elementor is used on PDPs.
* Fix: PDP layout issue when bundle products are displayed.
* Fix: Sticky add to cart PDP bar HTML validation issue.
* Fix: Breadcrumbs width when Elementor Pro full width template active.
* Fix: Content width now applies to Logo/Navigation/Cart header layout.
* Tweak: RTL styling improvements.
* Tweak: Top bar padding on mobile adjustment.

2.5.1 - 01-03-2022
* Minimum PHP requirement bumped to 7.3.33 (latest secure version of PHP 7.3).
* Fix for shoptimizer-metaboxes.php error.

2.5.0 - 28-02-2022
* New: Single product options metabox. Turn off certain elements on an individual product level, and reorder the short description position.
* New: Theme option to change product description width. Appearance > Customize > Layout > WooCommerce > Product description container width.
* New: Theme option to change product gallery width. Appearance > Customize > Layout > WooCommerce > Product gallery width.
* New: Theme option to reorder the PDP short description. Appearance > Customize > Layout > WooCommerce > Product short description position.
* New: Theme option to enable the block editor on the product long description area. Appearance > Customize > Layout > WooCommerce > Enable block editor
* New: Shortcode to include accordions within PDP short description area.
* New: Theme option to disable the block editor for widgets. Appearance > Customize > Layout > General > Disable block editor for widgets
* New: Additional top bar controls. Appearance > Customize > Header and Navigation > Top Bar
* New: Additional below header controls. Appearance > Customize > Below Header
* New: Sales badge style option: circle or bubble. Within: Appearance > Customize > Layout > WooCommerce
* New: Search field style option: filled or outline. Within: Appearance > Customize > Header and Navigation > Header
* New: Display blog page title option. Within: Appearance > Customize > Layout > Blog
* New: Can now set the mobile product grid to one column. Appearance > Customize > Layout > General
* New: Support for AIOSEO breadcrumbs.
* New: Support for FiboSearch plugin.
* Fix: Typography 2.0 now compatible with latest Kirki 4.0+ release.
* Tweak: Product category style improvements when in the PLP loop.
* Tweak: PDP Additional information tab style improvement.
* Tweak: Block widget style improvements.
* Tweak: Styling when product categories shortcode added to posts.
* Tweak: ACF full width category banner option now also applies to brand taxonomies.
* Tweak: My account mobile styling improvements.
* Tweak: My account icons width adjusted to 1.5px.
* Removed: Product gallery thumbnails position option. Will now use the product gallery module in CommerceKit.

2.4.5 - 10-08-2021
* Tweak: Select options button in sticky bar now scrolls to the variations form.
* Tweak: Minor style improvements when using Elementor Pro on single product pages.
* Tweak: Previous/next products option performant code refactor.
* Compatibility: Better styling when using the official 'WooCommerce Quick View' plugin.

2.4.4 - 19-07-2021
* New: 'Thank You Custom Area' widget (Appearance > Widgets), allows you to add custom content at the end of the thank you page.
* Tweak: Distraction-free checkout option now excludes thank you page.
* Tweak: Thank you page style tweaks.
* Tweak: Better content top padding when breadcrumbs aren't active.
* Tweak: sale prices are now bolded.
* Fix: PDP sticky bar will no longer show average rating if 'Display reviews' option is disabled.
* Fix: [aria-*] attributes do not match their roles.
* Fix: PHP Notice: Undefined variable: sku.
* Fix: 404 page products shadow on hover.
* Fix: Elementor Pro: when products shortcode is used and 3 columns is selected, the grid now displays correctly.

2.4.3 - 03-06-2021
* Fix: An additional check to see if a sizes attribute is present.
* Tweak: Coupon button hover color now matches the other buttons.

2.4.2 - 02-06-2021
* Tweak: Mobile PDP tabs style change to increase the tappable area.
* Tweak: WooCommerce Brands header layout now matches product categories.
* Tweak: Small style adjustments if using the Perfect Brands plugin.
* Tweak: Margin reduction on single posts when no breadcrumbs active.
* Tweak: PDP Description title now removed with filter rather than hidden with CSS.
* Fix: Previous/next blog post setting works correctly now.
* Fix: Category titles when 'Below header' option applies now mirror the default H1 font size.
* Performance: Custom sizes attribute for PDP images.
* Performance: New custom thumb for PLPs. New sizes media query for PLP loop.
* Performance: Load Rivolicons option now disabled by default, as theme uses SVG icons.
* Update: Class name change from menu-link to cg-menu-link.

2.4.1 - 12-05-2021
* Fix: Reduced sensitivity of 'Show Filters' button on mobile when scrolling past it.
* Tweak: Removed JS classes applied to category description area
* Tweak: Reworked category description markup and CSS of PLPs, CLS is now 0.
* Tweak: PLP category image dimensions now outputted.
* Tweak: PDP sticky bar now loads the smaller woocommerce_gallery_thumbnail image size.
* Accessibility: Use aria-label for quantity selectors.

2.4.0 - 05-05-2021
* New: 'Blank canvas' page template. Removes header and footer. Ideal for custom landing pages.
* Fix: Displaying single products using a shortcode functionality and styling improved.
* Accessibility: Quantity selector 'Links are not crawlable' GPSI warning resolved on PDPs.
* Tweak: Default H2 headings now 600 weight.
* Tweak: Width of category images when no description is present is 100% again.
* Tweak: H1s added to the product description area matches default H1 style.
* Tweak: Minor RTL improvements on PDPs.

2.3.9 - 21-04-2021
* Fix: Ensure coupon field on cart page is 16px to fix mobile iOS zoom.
* Tweak: Better max-width CSS display for category images to reduce flash of image resize.
* Tweak: Replaced checkout loading spinner gif with inline svg.
* Tweak: Product category banner can now also apply to product tags.
* Tweak: Blog grid layout style improvements.
* Tweak: Product description H2 now correctly corresponds to the typography H2 setting.
* Tweak: Some Elementor 3.2 compatibility updates.
* Removed: Display pagination theme option due to conflict with YITH infinite scrolling plugin.

2.3.8 - 24-03-2021
* Fix: Tapping the x on the mobile PDP gallery sometimes triggered the cart sidebar.
* Fix: Ajax search option not appearing when Header 4 layout selected.
* Fix: Mega menu hover overlay now covers footer.
* Tweak: Checkout and cart inputs now use 16px font sizes to prevent zoom on iOS.
* Tweak: Request a Quote plugin responsive styling tweaks.

2.3.7 - 15-03-2021
* Update: Typography 2.0 filter support.
* Tweak: Better PDP info bar styling when long strings are present.
* Tweak: Improved WooCommerce Bundle styling.
* Tweak: Improved WooCommerce Request a quote styling.

2.3.6 - 04-03-2021
* Fix: Elementor Pro can now overwrite the 404 template.
* Fix: variable_id PHP warning.
* Update: Languages pot file updated with additional strings.
* Tweak: RTL vertical gallery layout.

2.3.5 - 18-02-2021
* Fix: PHP error if WC is deactivated.
* Fix: PDP Ajax add to cart for external products functionality improved.
* Tweak: Variations tick icon JS now correctly displays when a variation is selected.
* Tweak: Small adjustment to the open sidebar cart vanilla JS.
* Tweak: Germanized plugin can now update variation prices dynamically.
* Tweak: <select> font display improvement in Firefox for Windows.

2.3.4 - 08-02-2021
* New: Mobile cart page experimental theme option: collapses the table cells into rows. Useful for certain sites with a lot of long product title strings, or German sites which need to display more information e.g. tax data. Appearance > Customize > Layout > WooCommerce
* Fix: Modal css loading fix when certain options are selected.
* Fix: Tagline theme option now saves correctly without needing to reapply it.
* Fix: WooCommerce sidebar position on the archives template.
* Tweak: Updated quantity.min.js file.
* Tweak: Cart table on checkout page now has slightly larger thumbnails and more flexible markup.

2.3.3 - 27-01-2021
* New: Option to include My Account icon and link in mobile header.
* Fix: Ajax PDP error message bar display.
* Fix: Error if WC is deactivated.
* Fix: HTML validation issue.
* Fix: Styling when widget menu is used in footer.
* Tweak: New languages .pot file included.
* Tweak: Better mega menu transition timing when hovering on and off quickly.

2.3.2 - 20-01-2021
* Fix: Added to cart message doesn't get added to other pages.
* Fix: Reduced sensitivity of mobile filters button so it doesn't open when scrolling past it.
* Fix: JS error when WooCommerce ajax option was disabled.
* Fix: Additional <h1> tag removed.
* Fix: Reverted decision to disable Rivolicons by default.
* Tweak: Minor RTL improvements.
* Tweak: Improved menu widget styling.
* Tweak: Sale price color in CommerceKit ajax search results dropdown now matches theme option.

2.3.1 - 13-01-2021
* Fix: Mobile menu tap event improvement.
* Fix: Update button on cart page fixed.
* Fix: Sidebar body class now added to WooCommerce taxonomy pages.

2.3.0 - 12-01-2021
* Performance: PHP required version bumped to 7.2.1.
* Performance: Theme-specific javascript rebuilt using vanilla JS.
* Performance: Set Rivolicons to not load by default.
* New: Can now add text to the bottom of the cart sidebar. Appearance > Customize > Header and Navigation > Cart
* Fix: Navigation hover links color option now works correctly.
* Fix: Renamed jetpack_lazy_images_blacklisted_classes filter to jetpack_lazy_images_blocked_classes.
* Fix: Hover underline issue when using columns in mega menu.
* Fix: Exclude WooCommerce variable subscriptions from PDP ajax add to cart.
* Fix: PDP ajax error notices now appear above the product.
* Tweak: New shoptimizer_woo_cart_available function check before loading cart quantity and amount.
* Tweak: Improved mobile menu tap event on Android.
* Tweak: RTL style improvements.
* Tweak: Improved WooCommerce subscriptions completed order and my account table styling.
* Tweak: No results search styling design.
* Tweak: Cart page coupon button style improvement.
* Tweak: Minor WooThumbs gallery plugin compatibility style improvements.

2.2.9 - 05-11-2020
* Fix: Mobile filter doesn't appear if sidebars are not active.
* SEO: Product category image alt tag is now used if present.
* Tweak: YITH Ajax Filter plugin styling improvements.
* Tweak: Filter by attribute widget dropdown no longer overlaps sticky menu.
* Tweak: Improved default gallery styling.

2.2.8 - 22-10-2020
* Tweak: Lots of RTL improvements.
* Tweak: Mobile footer style issue fixed if Elementor used.
* Tweak: Width and height if SVGs are added to the top bar widget area
* Tweak: Top bar widgets now centered on mobile
* Fix: Dropdown arrow color in Header 4
* Fix: Wishlist icon was overlapping mobile Header 4

2.2.7 - 13-10-2020
* Fix: Products in mega menu not appearing in Elementor Pro
* Fix: Property notices debug messages.

2.2.6 - 12-10-2020
* Improvement: Replaced Rivolicons and star font files with faster loading SVGs, removing two requests.
* New: Theme option to disable Rivolicons. Appearance > Customize > General > Speed Settings
* New: Color option for icons in below content area. Appearance > Customize > Colors > Footer
* New: SEOPress breadcrumbs option added.
* New: Mini shopping bag icon option included.
* New: Includes <h1> hidden titles on Shop and Blog pages.
* Fix: Remove pagination theme option broke YITH Infinite Scroll in v2.2.5. Now resolved.
* Fix: Elementor overflow mobile issue resolved.
* Fix: Elementor Pro loading issue when using a theme builder header.
* Fix: Compatibility with Ci WooCommerce Product Gallery Slider plugin.
* Fix: Check for out of stock string condition added.
* Tweak: YITH Wishlist functions and CSS now conditionally load only if plugin is active.
* Tweak: Sticky add to cart bar will always now be positioned at the bottom on mobile.

2.2.5 - 24-09-2020
* New: CommerceKit launched!
* New: Option to remove shop pagination. Appearance > Customize > Layout > WooCommerce
* New: Mobile header now has a search toggle option. Appearance > Customize > Header and Navigation > Mobile Header > Mobile Search Position
* Improvement: More defined form element styling on checkout and account pages.
* Fix: Overflow issue on Elementor pages on mobile.
* Fix: Adjusted sales badge function to remove if_admin condition and included a check for product bundles.
* Tweak: Intermediate media query for smaller tablets added, product grid adapts to 3 rather than 2.
* Tweak: WooCommerce Smart Search plugin removed from TGM installer.
* Removed: Stock meter theme option has now migrated into the CommerceKit plugin.

2.2.4 - 28-08-2020
* New: Can now add a title label to the sidebar cart. Appearance > Customize > Header and Navigation > Cart
* New: Typography preset option: Inter. Appearance > Customize > Typography > Presets
* Fix: Sticky bar JS error when a product was out of stock.
* Fix: Image change on hover option now loads the correct thumbnail size.
* Fix: Double scrollbar issue on Canvas template.
* Tweak: Sidebar cart improvements, now doesn't open until the product has been added.
* Tweak: Add to cart buttons now display a spinner while the item is being added.
* Tweak: Improved search results styling in preparation for our own search module.
* Tweak: WooCommerce info bar styling improvements if ajax ATC turned off.
* Tweak: Vertical gallery JS improvements.
* Tweak: Font smoothing in Firefox.

2.2.3 - 31-07-2020
* New: Can now display previous and next blog posts. Option in: Appearance > Customize > Layout > Blog
* Fix: PHP 7.4.1 error message when WooCommerce is not active.
* Fix: Overflow issue on About page in Safari.
* Tweak: Icon fonts now use display block rather than swap.
* Tweak: Simplified the logo function code.
* Tweak: Submit button style in WP Forms.
* Tweak: Strong/weak password icon now displays within My Account area.
* Tweak: Set blog post featured images to display by default.
* Tweak: Shop pagination margin adjustment.
* UX: Remove sidebar by default from Cart, Checkout and My Account pages without having to change the template to full width.

2.2.2 - 18-07-2020
* Tweak: Mobile sidebar cart z-index adjustment.

2.2.1 - 17-07-2020
* Fix: Mobile overlay issue when top bar is visible.
* Fix: Lots of minor RTL improvements.
* Tweak: Mega dropdown menu now supports a 5-col class.
* Tweak: Improved responsive tables within the My Account area on mobiles.
* Tweak: CommerceKit updated to 1.0.3.

2.2.0 - 11-07-2020
* Fixes: A number of customizer selector corrections from the 2.1.9 release. Thanks to Krishnendu and Guillermo.
* Tweak: Inner scrollbar now visible within sidebar cart when contents are taller than its container height.

2.1.9 - 10-07-2020
* Performance: Removed sticky-kit.js: now uses native CSS sticky and vanilla JS.
* Performance: Moved quantity js into a separate file and only loads on PDP and cart.
* New: Option to show fixed search bar below header on mobile.
* New: Option to show top bar on mobile.
* New: Support for Ajax Search for WooCommerce plugin for main site search.
* Fix: Mobile filters slide out bar no longer closes when you tap outside of it. Solves a number of issues regarding the selection of filters.
* Fix: W3C validation issues when menu descriptions were active.
* Fix: Elementor Pro conditional pages setting now applies correctly.
* Tweak: Reduced length of some of the theme option class names.
* Tweak: Simplified inline color CSS from theme options selections.
* Tweak: Elementor Pro product card styling improvement on mobile.
* Tweak: Elementor Pro custom header usage will now remove the default theme header and navigation.

2.1.8 - 22-06-2020
* Fix: Subscription products opened up the cart drawer instead of directly linking to the product page.
* Fix: Content overflow within single product critical CSS file from 2.1.7.
* Fix: More robust sales badge function checks.
* Fix: Undefined index: shoptimizer_layout_top_bar_mobile

2.1.7 - 19-06-2020
* New: Can now add a button to the main menu.
* New: Dokan styling if the plugin is enabled.
* New: Compatibility with Imagify's webp <picture> format.
* Tweak: Removed next and previous text from blog pagination: just arrows.
* Tweak: Simplified link and hover color selectors.
* Fix: Child theme icon font paths issue resolved.

2.1.6 - 11-06-2020
* New: Can now span multiple columns in the mega menu.
* Tweak: Display attribute term title on its archive page.
* Tweak: Previous and next arrows on single products now display items within the same category.
* Tweak: Removed $j call from main.min.js and recompressed it.
* Tweak: Add to cart button visibility on mobile devices.
* Tweak: Added a preload for icon fonts.
* Accessibility: Added aria-label to product links.
* Accessibility: Increased contrast on grey category color.
* Accessibility: Removed maximum-scale on header.php viewport.

2.1.5 - 04-06-2020
* Fix: Top bar position when using full width header.
* Fix: Search modal display on large iPads.
* Tweak: Improved upsells title function, now uses a WC4.1 filter.
* Tweak: Sticky cart bar hook position.
* Tweak: Mobile Extra widgets area no longer requires WooCommerce.
* Tweak: Improved swatch styling if using labels.
* Tweak: Mega menu width: occasional overlapping issue with 5 columns on iPads.
* Tweak: Single product ajax add to cart JS adjustment.
* Removed: Option to show top bar widgets on mobile.

2.1.4 - 29-05-2020
* New: Option to position cross sells on the cart page.
* New: Option to use the regular WordPress search in the header rather than the WooCommerce search.
* Fix: Mobile top spacing when sticky menu was active. Mobile sticky header no longer uses JS.
* Tweak: Better product card hover to cope with different sized images.
* Tweak: Make cart shipping toggle selections bold when active.
* Removed: Sticky header height option when full row header selected, resulting in improved performance.

2.1.3 - 22-05-2020
* All new options in this release are now available within: Appearance > Customize > Layout > WooCommerce
* New: Option to have ajax add to cart on single product pages.
* New: Option to flip and display a different product image within product cards on hover. Should be used with a lazy load plugin active.
* New: Option to hide category featured image.
* New: Option to hide category description.
* New: Option to change upsells title text.
* New: Option to position upsells before related on single product pages.
* Fix: Colors within product cards embedded in blog posts.
* Fix: Modal display CSS now loads on full width header layout when the search is disabled.
* Tweak: Can now set letter spacing for all headings within the typography section.
* Tweak: Mobile: Can now toggle shop and archives sidebar filter sidebar by tapping outside it or on close icon.

2.1.2 - 15-05-2020
* Fix: Focus issue on mobile search input on Android.
* Fix: When search is disabled on full row header, don't load modal.css
* Fix: CommerceKit 1.0.2. Removes a message displayed in the log file.
* Tweak: Exclude product gallery images from Jetpack's lazy load.
* Tweak: Further improved mobile sidebar cart buttons staying in view, works on initial load and resize.
* Tweak: Highlight active link in product categories widget.
* Tweak: Opacity hover effect on primary menu will now apply irrespective of how the menu is named.

2.1.1 - 01-05-2020
* Tweak: Added a theme option around menu descriptions: they are no longer displayed by default.
* Tweak: Improved downloads styling on confirmation screen.
* Tweak: Minor style tweaks.

2.1.0 - 29-04-2020
* New: Can now include images in the mega menu: see: https://www.commercegurus.com/docs/shoptimizer-theme/mega-menu/
* Tweak: Minor mobile responsive tweaks.
* Tweak: Better JS targeting of quantity up and down arrows.
* Tweak: Updated xfn structured data reference in header.php to use https.
* Tweak: Improved RTL styling on single product add to cart bar.
* Fix: On mobile the sidebar cart checkout button should now always stay in view, even when the bottom mobile bar is present.
* Fix: Sticky menu issue when full width header layout active.

2.0.9 - 24-04-2020
* Fix: Responsive sticky header layout when switching from portrait to landscape mode on large tablets.
* Fix: Sticky bar issue resolved between 992px and 1024px viewports.
* Fix: Refactored single product CSS so can now use the single product shortcode on standard pages.
* Fix: Cart page layout width issue in Safari on tablets.
* Tweak: Radio button active color on cart and checkout pages when only one option.
* Tweak: RTL version: On the my account screen, sidebar is now on the right.

2.0.8 - 08-04-2020
* Tweak: Link color within product short description.
* Tweak: Border added to below category area, and better table styling.
* Tweak: Display product category widget counts.
* Tweak: RTL mobile product layout.
* Fix: Add to cart issue from mobile menu products.

2.0.7 - 03-04-2020
* Tweak: Sticky product bar now uses ellipsis for long titles.
* Tweak: Touchstart included as well as click in main JS file.
* Tweak: Minor sidebar cart style improvements.
* Fix: PHP7.3 warning message for below category text area.

2.0.6 - 26-03-2020
* Fix: Link color issue on single blog posts.
* Fix: Duplicate magnifier icon in search widget.
* Tweak: Size guide plugin position on products.
* Tweak: Metadata on single products now on one line.
* Tweak: Minor responsive adjustments.

2.0.5 - 19-03-2020
* Fix: Mobile filters no longer close if text is entered into a filters widget.
* Fix: Tapping an add to cart button within the mobile menu now closes the menu and opens the cart.
* Fix: Theme's link colors weren't applied on certain pages and posts.
* Fix: Duplicated SKU issue on variable products.
* Fix: Dimensions and weights on variable products not displaying.
* Tweak: First mobile menu item styling.
* Tweak: With a centered logo, header style of minimal checkout improved.
* Tweak: Updated shoptimizer.pot language file for translations.

2.0.4 - 10-03-2020
* Tweak: Responsive video container styling for embedded Youtube videos in description tab.
* Tweak: Remove page header from canvas template.
* Tweak: Minor RTL improvements.
* Fix: Search overlay fix on some Android devices when mobile navigation is active.
* Fix: CommerceKit 1.0.1 update to remove debug warning.

2.0.3 – 26-02-2020
* New: Header layout option: can now include the cart in the main header instead of only within the menu bar.
* New: Product archives template for users of FacetWP and who want to display the mobile filters toggle.
* Tweak: Mobile sub menus will be revealed if the parent link is a hash (#) and is tapped. No longer need to tap only the arrow.
* Tweak: Product category shortcode within mega menu styling.
* Tweak: Mobile call to action buttons now line up if using slide up card option.
* Tweak: Add a width check to the masonry grid JS call so it only applies on desktop.
* Tweak: Mega menu hover timing adjustment.
* Tweak: Improved styling of the Downloads section within My Account.
* Fix: "Undefined variable: tagline" warning message.

2.0.2 – 19-02-2020
* New: Product card style alternative to the polaroid effect: a slide up effect.
* New: Can now remove buttons from product cards.
* Tweak: Link and link hover colors now apply if the Elementor Canvas template is selected.
* Tweak: Mobile cart page improvements.

2.0.1 – 12-02-2020
* Fix: Alternative cart icon color.
* Tweak: Mobile filter js function call.
* Tweak: Mega menu heading margin adjustment.
* Tweak: Improved filter widget styling.
* Tweak: Tag page description padding.
* Tweak: Cart table display at very small mobile resolutions.
* Tweak: Remove featured thumbnail from canvas template.
* Tweak: Add <h1> brand title if using WooCommerce Brands plugin.
* Tweak: Product card style in Elementor Pro.

2.0.0 – 03-02-2020
* Fix: Blog grid layout display
* Fix: Mobile cart icon color
* Tweak: Waiting list button style when logged in.

1.9.9 – 29-01-2020
* New: Offscreen mobile navigation and additional color options make it much easier to style.
* New: Mobile extras widget area allows for additional information be displayed below mobile navigation.
* New: Mobile cart icon color option.
* Fix: Header 2 and 3 logo position issue.
* Fix: product_thumbnail_in_checkout filter causing error message.
* Fix: Elementor font and color styling being over-written by typography controls in the customizer.
* Fix: Link color styling not being applied on some pages.
* Fix: Elementor Pro users can now use their own custom single post template.
* Tweak: Small <500px breakpoint adjustment where the timer and stock counter collapse under each other.
* Tweak: Display enlarge product image icon on mobile.
* Tweak: Revert single product image display to take up full width of viewport.

1.9.8 – 14-01-2020
* Fix: Header 4 z-index issue.

1.9.7 – 13-01-2020
* New: Started typography presets section, can switch to a websafe font.
* New: Added Rank Math as an option for the breadcrumbs display.
* Tweak: Moved Mobile Header options from General into Header and Navigation section.
* Tweak: Changes to the sticky mobile menu operation and larger tappable icons.
* Tweak: Single product zoom on desktop and mobile.
* Improvement: Icons added to main customizer panels.
* Improvement: Removed and optimized some images from the setup section bringing zip size down to 620kb.

1.9.6 – 03-01-2020
* New: Full width header style added.
* New: Can now disable top bar on mobile.
* New: Option to place checkout coupon code top or bottom.
* New: Option to display product CTA button always, i.e. not on hover.
* Tweak: If top bar disabled, wrapping div also removed.
* Tweak: Single product, cart, checkout mobile views all improved.
* Tweak: My Account styling enhanced.
* Tweak: Include small product thumbnails within checkout summary order table.
* Tweak: Improved radio style on checkout.
* Fix: PHP search modal warning if WC not active.

1.9.5 – 16-12-2019
* Fix: Hide the mobile search if disabled.
* Fix: Mobile sticky header issue if Header 4 enabled.
* Fix: Elementor Pro custom header replaces header and navigation if Header 4 enabled.
* Tweak: Select dropdown style adjustments.

1.9.4 – 9-12-2019
* Fix (Header 4) Distraction free checkout hides search and cart.
* Fix (Header 4) Mobile search present again.
* New: Header 4 theme option: can now change the search modal title within customizer.
* Tweak: warning info message on single products no longer full width.

1.9.3 – 5-12-2019
* New: One row header layout option added: Logo / Navigation / Search / Cart
* New: Can now arrange gallery thumbnails vertically, next to the main product image.
* Fix: Mobile sticky menu issue resolved.
* Tweak: Removed wp_body_open as some folks are still running an old version of WP which doesn’t support it.

1.9.2 – 26-11-2019
* Update to TGM installer to fix sprintf() too few arguments warning when installing plugins.
* Tweak: Judge.me stars disappearing on hover.

1.9.1 – 25-11-2019
* New: CommerceKit plugin allows automatic one-click updates once domain has been connected within My Account.
* Fix: Kirki deprecated function: required replaced by active_callback throughout.
* Tweak: Menu heading font style comes through to mobile menu now also.
* Tweak: Small style adjustment – Reviews tab margin on mobile.

1.9.0 - 21-11-2019
* New: Header layout: Search / Logo / Secondary
* New: Header layout: Secondary / Logo / Search
* New: Loading spinner displays after placing an order at the checkout.
* New: Additional font theme option. Can now change heading font within mega menu.
* New: Minified versions of all CSS files now load if theme option selected.
* New: Minifed main.js now loads.
* Tweak: Minified sticky-kit.js and lazyload.js and added them to main.js.
* Tweak: Conditionally loading lazyload-bg.js so it’s not always present.
* Tweak: Smart Search results display better when items with a long title are found.
* Tweak: Removed placeholder focus CSS rule which interfered with SUMO exit intent plugin.
* Tweak: Added WPForms and YITH Wishlist to the help section as suggested plugins. Removed from the TGM installer.
* Tweak: Improved default theme styling when Kirki is not active.
* Tweak: Removed Kirki fallback style and script loaders.
* Tweak: Help area; improved heading font styling.
* Reorder: Navigation font settings put into the Typography panel.

1.8.9 - 20-11-2019
* Fix: Error message in functions.php if WooCommerce is not active.
* Fix: Mobile navigation label displaying when disabled.
* Tweak: Responsive cart display on smaller resolutions.
* Tweak: Link color for list items in posts.

1.8.8 - 14-11-2019
* Tweak: Improved Judge.me styling on single product pages.
* New: wp_body_open(); added to header.php.
* Fix: Improved sticky bar styling when adjusting browser window width manually.

1.8.7 - 08-11-2019
* Tweak: Mobile menu now can expand and collapse more than one level.
* New: Theme option to remove featured images on posts.
* Tweak: Small CSS adjustments for better compatibility with certain plugins.

1.8.6 - 02-11-2019
* Fix: Prevent canvas template footer from going edge-to-edge full width.
* Fix: Allow shortcodes within category description area.
* Fix: Compatibility with Off-Canvas Sidebars and Menus plugin.

= 1.8.5 - 31-10-2019
* Tweak: Removed breadcrumbs from full width (no heading) template.
* Tweak: Out of stock text now called by a function. Can be changed via a filter.
* New: Canvas template for full width edge-to-edge page creation.

1.8.4 - 29-10-2019
* Fix: Double tap issue on mobile navigation.
* Fix: Debug notice warning on 404 page.
* New: Support for Yoast breadcrumbs via a theme option.
* New: Can disable author and meta on single blog posts via theme options.

= 1.8.3 - 18-10-2019
* Fix: Floating button was not appearing on single products.
* Fix: Tagline variable not defined.
* Fix: Removes extra archive description block from paginated pages if below header layout selected.
* Tweak: Cart widget styling when in a sidebar.
* Tweak: Products within mobile dropdown grid adjustment.
* Tweak: Regular message styling on a single product.

= 1.8.2 - 09-10-2019
* Fix: Full width category image display fix.
* New: Theme options to change related, upsells and cross-sells number.

= 1.8.1 - 02-10-2019
* Fix: ACF not activated error message.
* Tweak: Sidebar cart style tweaks including empty state.
* Tweak: Woo Notification plugin style improvements.
* Addition: Can now switch the basket icon to a cart via: Customize > Navigation > Cart.

= 1.8.0 - 30-09-2019
* Enhancement: Elementor Pro compatibility. Can now use custom headers and footers with the theme.
* Tweak: Product category description text width when there's no sidebar.

= 1.7.9 - 20-09-2019
* Tweak: Product reviews star rating display.
* Tweak: Button within product card display.
* Fix: Tap event on main dropdown menu now works on touch devices. Once to open. Second time to follow the link.

= 1.7.8 - 17-09-2019
* Tweak: Product categories within grid styling.

= 1.7.7 - 16-09-2019
* New: Category title and description layout below header option.
* Fix: Mobile grid bug which reappeared.
* Fix: Regenerated Critical CSS.
* Tweak: More precise sticky bar scrolling to selection options.

= 1.7.6 - 13-09-2019
* Fix: German market plugin compatibility.
* Fix: WooCommerce bundles plugin now displays prices correctly on mobile.
* Tweak: Single product responsive change: details now appear under the image
* Tweak: Mobile grid adjustments for products within Elementor sections.

= 1.7.5 - 12-09-2019
* New: Star rating now displays within the single product sticky bar.
* New: Can now place the single product sticky bar at the bottom.
* New: Can now add label text beside the mobile menu.
* New: Option to display the site tagline under the logo.
* New: Theme option to turn off the sliding cart drawer and link directly to the cart.
* Tweak: Bundles and composite products within sticky bar now scroll to summary.
* Tweak: Composite products style tweaks.
* Tweak: Additional RTL styling.
* Tweak: Sidebar cart z-index when demo store notice is active.
* Tweak: Sidebar filters mobile animation speed to prevent ghosting.

= 1.7.4 - 09-09-2019
* Fix: Sale label now appears on mobile on single products.
* Fix: Elementor text element font size on single products.
* Tweak: Sticky add to cart bar now supports bundles.
* Tweak: Change product title on mobile to be a span.
* Tweak: Empty cart styling improvements.
* Tweak: Additional RTL styling.

= 1.7.3 - 04-09-2019
* Fix: Gap above sidebar cart when viewing as a customer.
* Tweak: Product categories on mobile now fit to the grid.
* Tweak: My Account orders table styling improvements.
* Tweak: RTL improvements.

= 1.7.2 - 28-08-2019
* Fix: Added to cart button display.
* Fix: Responsive layout on mobile for My Account page.
* Fix: Product grid now displays correctly when Elementor page template is selected.
* Fix: Grid of 6 columns display on Safari.
* Fix: Mobile filters sidebar was briefly visible before page loaded.
* Fix: Safari cart display bug when multiple shipping options were present.
* Tweak: Accessible alternative to hiding product title h1 on mobile. 

= 1.7.1 - 01-08-2019
* Addition: Theme option to display secondary navigation on mobile.
* Fix: Mobile layout of category description text
* Tweak: Categories with product cards, markup changed from a <h6> to a <p>.

= 1.7.0 - 29-07-2019
* Fix: Grid issue in Safari resolution.

= 1.6.9 - 29-07-2019
* Fix: Firefox category image layout.
* Fix: Grid display in Safari.
* Fix: Grid display on mobile iOS.
* Fix: Link Colors Theme Option.
* Tweak: Sidebar cart checkout button position when admin bar is present.
* Addition: Masonry layout option for Shop.
* Addition: Can now add labels to the menu dropdowns using the <strong> tag.

= 1.6.8 - 15-07-2019
* Tweak: Main grid % widths refactored.
* Enhancements: Infinite scrolling shop support.

= 1.6.7 - 08-07-2019
* Tweak: Responsive blog layout updates.
* Tweak: Confirmation page: display addresses in two columns.
* Addition: Updated POT language file.

= 1.6.6 - 27-06-2019
* Fix: Added to cart message bar styling.
* Fix: JS error if floating modal button option is not active.
* Tweak: Styling when fixed mobile bar is active.

= 1.6.5 - 26-06-2019
* Fix: Added to cart message bar styling.

= 1.6.4 - 26-06-2019
* Fix: Single product responsive issues from 1.6.3.
* Fix: Iconic swatches plugin double tick icon.
* Fix: Sticky header on mobile now picks up the background header color.

= 1.6.3 - 25-06-2019
* New: CSS split into key template files reducing load.
* Fix: Lazy load sticky logo so that it is non-blocking.
* Fix: Sale theme color option.
* Fix: Variation default option now stays set.
* Fix: New lighter modal js on the single product page which resolves some plugin conflicts.
* Enhancements: a host of minor CSS bugs squashed.

= 1.6.2 - 11-06-2019
* Fix: Floating button theme option was hidden
* Fix: Gutenberg video block display
* UX: Variable products now display a tick beside each attribute when an option has been selected. Purchase button stays faded out until it can be interacted with.

= 1.6.1 - 04-06-2019
* Fix: Star ratings were showing a slight difference on Windows and Mac rendering

= 1.6.0 - 31-05-2019
* New: Mobile sticky header option added
* Enhancement: Theme version number added to main.min.css for better cache busting
* Fix: Countdown timer styling

= 1.5.9 - 24-05-2019
* Fix: wc_add_to_cart_message deprecated function
* Fix: Column layout when using Elementor's product grid

= 1.5.8 - 09-05-2019
* Enhancement: improved added to cart bar styling which includes a direct checkout link
* Enhancement: new widget area to add reviews under the cart summary for conversions

= 1.5.7 - 03-05-2019
* Fix: get_woocommerce_term_meta deprecated function
* Fix: blog sidebar display when turned off on the shop

= 1.5.6 - 02-05-2019
* Fix: SKU display updates now on variable products
* Enhancement: Alternative to the Beeketing plugin added

= 1.5.5 - 17-04-2019
* Fix: Remove the dequeuing of Dashicons
* Tweak: Minor style tweaks

= 1.5.4 - 12-03-2019
* Tweak: Card Product titles now link also
* Tweak: Single product variable CTA button is no longer grey and faded
* Improvement: Display % discount next to single product page price
* Fix: Extra dash within variable/grouped product pricing
* New: Theme Option: cart text hover color

= 1.5.3 - 01-03-2019
* Fix: Old price/sale price margin issue
* Improvement: Minimal checkout now also removes main navigation and top bar
* Addition: Progress bar color option added within theme options

= 1.5.2 - 28-02-2019
* Fix: Price display issue

= 1.5.1 - 27-02-2019
* Tweak: Single product page no longer displays two h1 tags
* Improvement: Sale badge now works on variable products
* Fix: Below category content code refactoring

= 1.5.0 - 24-02-2019
* Tweak: Grid adjustment on mobile

= 1.4.9 - 22-02-2019
* Fix: Mega menu dark opacity layer present when refreshing the page while hovered over the link
* Fix: Remove mobile filters button if no widgets present
* Tweak: Mobile sorting dropdown now full width if no mobile filters button present

= 1.4.8 - 21-02-2019
* Improvement: Below category WYSIWYG now accepts shortcodes
* Tweak: Shop ratings: increase priority to 6

= 1.4.7 - 20-02-2019
* Fix: Display product sorting / display result count now applies after the loop also

= 1.4.6 - 12-02-2019
* Tweak: WooCommerce product search widget style
* Improvement: Single product shortcode display
* Improvement: Add to cart shortcode display
* Fix: Legacy Gutenberg style cleanup
* Fix: Double scrollbar on fixed container page
* New: wpml-config.xml file added for WPML

= 1.4.5 - 04-02-2019
* Fix: Button color styling fix

= 1.4.4 - 27-01-2019
* Improvement: Enhanced WooCommerce Subscriptions styling

= 1.4.3 - 04-01-2019
* Tweak: Enhanced mobile header/navigation color options

= 1.4.2 - 30-12-2018
* Fix: Safari grid % correction

= 1.4.1 - 29-12-2018
* Compatability: WooCommerce Germanized Plugin styling
* Tweak: Progress bar now links to cart/checkout pages

= 1.4.0 - 19-12-2018
* Fix: Level 2 font size when in a full width dropdown
* Enhancement: Confirmation order styling

= 1.3.9 - 18-12-2018
* Tweak: Better styling when breadcrumbs are disabled

= 1.3.8 - 14-12-2018
* Fix: Product loop function category link permalink

= 1.3.7 - 13-12-2018
* Tweak: Display category description text on all paginated pages within that category.

= 1.3.6 - 13-12-2018
* Tweak: Single variable product sticky button scroll to variations table rather than outer div

= 1.3.5 - 07-12-2018
* Tweak: Better mobile cart rendering when multiple items are in it
* New: Additional full-width single post blog template added to better display posts built with Gutenberg

= 1.3.4 - 04-12-2018
* Fix: Remove biggerlink JS library

= 1.3.3 - 30-11-2018
* Fix: Improved the general search results display

= 1.3.2 - 28-11-2018
* Tweak: Reposition Critical CSS stylesheet in the <head>

= 1.3.1 - 27-11-2018
* Tweak: Increase specificity on the RTL stylesheet

= 1.3.0 - 26-11-2018
* Update: RTL support added!
* Enhancement: Out of stock label on shop/archives screens

= 1.2.7 - 20-11-2018
* Tweak: Redo the three homepage features to use regular images rather than backgrounds
* Tweak: Rivolicons to use font-display: swap

= 1.2.6 - 19-11-2018
* Fix: Quantity selector JS rewritten, fixed increments of two from sometimes happening
* Tweak: Cart page: updated message color and style tweak
* New: Mobile header color options

= 1.2.5 - 12-11-2018
* Fix: Safari grid calculation adjusted

= 1.2.4 - 10-11-2018
* Tweak: Sale badge display fixed if set not to display

= 1.2.3 - 09-11-2018
* Fix: Dropdown menu now works within unlimited levels
* Fix: Responsive tweaks for the stock countdown display
* Tweak: Primary color theme option now picks up more spots
* Tweak: If Sale badge is disabled: reverts to the normal WooCommerce display

= 1.2.2 - 24-10-2018
* Fix: Search instant results display on mobile

= 1.2.1 - 02-10-2018
* Tweak: On mobile: Display left and right arrows on product image
* Fix: CSS rule for stock countdown plugin had changed, updated this

= 1.2.0 - 18-09-2018
* Tweak: On mobile: Sticky add to cart bar appears
* Tweak: On mobile: Call me back button hidden
* Tweak: Cross sells now positioned under wrapper for better mobile layout

= 1.1.9 - 17-09-2018
* Tweak: On mobile: Single product title, rating, price now appears above the product image
* Tweak: New Theme option: can now enable/disable search

= 1.1.8 - 16-09-2018
* Fix: Select library now used for long dropdowns

= 1.1.7 - 07-09-2018
* Tweak: Cart and Checkout is now full width by default
* Tweak: Site Description margin tweak

= 1.1.6 - 03-09-2018
* Tweak: Cart styling fix

= 1.1.5 - 24-08-2018
* Tweak: Minor responsive improvements for mobile

= 1.1.4.2 - 21-08-2018
* Integration: Beeketing plugin styling

= 1.1.4.1 - 20-08-2018
* Fix: Sticky bar reinstated for all products. When on a variable or grouped product it smooth scrolls down to the options.

= 1.1.4 - 19-08-2018
* Fix: Improved display of product categories

= 1.1.3.4 - 16-08-2018
* Fix: Cart drawer opens now when on the minimal checkout
* Tweak: Sticky single product bar now loads only on simple products
* Integration: Woo Notification plugin

= 1.1.3.3 - 15-08-2018
* Tweaks: Minor checkout improvements with 3rd party plugins

= 1.1.3.2 - 15-08-2018
* Tweaks: Improvements in styling when Stripe payments plugin is active
* Tweaks: AngellEye Express checkout for PayPal styling tweaks

= 1.1.3.1 - 14-08-2018
* Fix: Variable products no longer opens the add to cart drawer when clicked
* Fix: YITH wishlist icon position fix
* Fix: Dash in a grouped/variable product price no longer disappears on hover
* Integration: YITH Size Guide Plugin

= 1.1.2 - 10-08-2018
* Fix: related products width fix

= 1.1.1 - 09-08-2018
* Dev: Restructured header hooks
* Dev: Show/Hide categories in product listings theme option added
* Dev: Added top bar border color theme option
* Dev: Added sticky border color theme option
* Dev: Added hover opacity theme option on navigation links
* Dev: Updated default colors including new dark navigation
* Fix: If primary navigation is renamed it now displays correctly
* Tweak: Top bar now visible on mobile

= 1.1.0 - 06-08-2018
* Tweak: Added theme option to show/hide previous and next products
* Fix: secondary menu is now always aligned to the right

= 1.0.9 - 31-07-2018
* Integration: YITH Tabs Manager
* Fix: modal appears now when item added to Wishlist
* Tweak: Single product style tweaks
* Tweak: Typography defaults updated

= 1.0.8 - 26-07-2018
* Integration: WPForms Lite supported
* Integration: Aelia Currency Switcher plugin supported
* Fix: Mobile style tweaks and improvements
* Tweak: Typography defaults updated

= 1.0.7 - 21-07-2018
* Tweak: Mobile filters now slide out from the side when filters button is tapped
* Tweak: Mobile viewport now kicks in at 992px
* Tweak: Mobile header is now fixed on scroll
* Tweak: General mobile style improvements throughout
* Fix: Main menu no longer loses position when only a small number of nav links are present

= 1.0.6 - 19-07-2018
* Dev: Loading animation when item added to cart
* Tweak: Cart countdown timer added
* Tweak: loading spinner visible when item added to wishlist

= 1.0.5 - 16-07-2018
* Dev: Cart fragment dequeued
* Tweak: Responsive fixes

= 1.0.4 - 13-07-2018
* Dev: Test for when YITH Wishlist is active
* Dev: Previous/Next product now includes price display
* Tweak: Updated typography defaults

= 1.0.3 - 12-07-2018
* Dev: Elementor page builder styles
* Dev: Previous/Next arrows on a single product page
* Tweak: Updated demo data

= 1.0.2 - 06-07-2018
* Dev: Support for YITH Wishlist
* Dev: Support for YITH WooCommerce Waiting List
* Dev: Support for Woo Advanced Product Size Chart plugin
* Dev: Support for Jetpack share icons
* Tweak: new card style when hovering over product
* Tweak: CTA add to cart button now visible on card
* Dev: New quantity up/down arrows included
* Dev: Adding to cart on listings page opens the cart side panel
* Tweak: New empty cart screen styling
* Dev: Mobile header layout fix
* Tweak: remove product widget if item is not in stock
* Tweak: hide size guide if item is not in stock

= 1.0.1 - 03-07-2018
* Tweak: Style improvements
* Dev: Speed theme options added: minification and critical CSS

= 1.0.0 - 09-06-2018
* Initial release
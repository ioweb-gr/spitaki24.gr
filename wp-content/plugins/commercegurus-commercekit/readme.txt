=== CommerceKit ===
Contributors: commercegurus
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: ecommerce, conversions, performance, product gallery, swatches, order bumps, countdowns, badges, sticky add to cart
Tested up to: 6.5
Stable tag: 2.4.1
Requires PHP: 7.2.5

Conversion-boosting, performance-focused eCommerce features which work together seamlessly. From CommerceGurus.

== Changelog ==

2.4.1 - 17-04-2025
* New: CommerceKit Badges can now be assigned by brand.
* New: Option to reset the most frequent search data within the Ajax Search reports.
* New: Empty wishlist template can now be over-written and customized manually.
* Fix: Variation price not updating according to the selected variant.
* Fix: Conflict between Product Gallery styles and Elementor Pro swiper styles on the PDP.
* Fix: Out of stock swatches not automatically disabled when the attribute has only a single value.
* Fix: Waitlist displayed for in-stock variant options.
* Compatibility: Product gallery with the Advanced Product Fields Extended for WooCommerce plugin.
* Compatibility: Ajax Search with the Sales Agent For WooCommerce plugin.
* Compatibility: Order Bumps with the Cartflows plugin.
* Compatibility: Improved compatibility with CookieBot.

2.4.0 - 04-03-2025
* New: Can set desktop and mobile product thumbnails number within the product gallery settings.
* New: Wishlist page now shows a standard product grid rather than a table layout.
* New: Revenue and conversion rate statistics now available for Wishlist and Waitlist modules.
* New: Option to duplicate existing Order Bump to make it quicker to create new ones.
* New: Ajax search suggestions can now look up products via native GTIN/UPC/EAN/ISBN field.
* New: Can disable attribute swatches for related and menu items on the product page.
* New: Custom "Reply-to" email option for waitlist enquiries as an alternative to the admin email.
* New: Waitlist email supports multilingual and can now be translated via WPML and Polylang Pro.
* New: Countdowns can be assigned via product tags, not just product categories.
* New: Display product captions within the product gallery lightbox.
* New: Third parties can use WP CLI to add/update/remove images in attribute galleries and add/update/remove attribute galleries.
* Fix: Improved Product Gallery Lazy Loading implementation to resolve blank images with loading animations.
* Fix: Elementor Pro PDPs - triggering a lightbox no longer downloads the image.
* Fix: Elementor Pro PDPs - if an additional SwiperJS instance is present, e.g. a carousel widget, the gallery loads.
* Fix: Free Shipping Notification showing negative value when merchants entered differently formatted prices.
* Fix: Wishlist pagination now works correctly on the My Account and main Wishlist pages.
* Fix: PLP swatches overflow issue on small screens, they are now all visible.
* Fix: Console errors when vertical left/vertical right gallery layouts selected.
* Fix: Order bumps no longer display draft products.
* Fix: PHP warnings issue resolved.
* Tweak: Ajax Search module security enhancements.
* Tweak: Additional CLI options included, can view full list via: "wp cg-commercekit info".
* Tweak: Search suggestions performs look up by phrase if more than one word in the query.
* Tweak: Product gallery arrows styling if Elementor Pro is active and has custom button padding applied.
* Tweak: Empty wishlist page styling improved.
* Performance: Custom table now used for storing size guide condition options instead of using a post meta table.
* Performance: A caching maximum limit of 500 products per single action applied.
* Compatibility: Ajax search now works correctly with the User Roles plugin.
* Compatibility: Improved attribute swatches display with the B2BKing plugin.
* Compatibility: Free shipping notification module with the Flexible Shipping plugin.
* Compatibility: Improved pricing display when the Discount Rules Core plugin is active.
* Compatibility: Sticky tabs anchor issue in Firefox when SaySpot reviews are active.
* Accessibility: "Redundant title text" from CommerceKit Attribute Swatches alert resolved.

2.3.9 - 18-09-2024
* Fix: Changing PDP variations affected related/upsells prices.
* Fix: Waitlist now works on product pages built with Elementor Pro.

2.3.8 - 13-09-2024
* Fix: Vertical scroll product gallery fix.
* Fix: Sticky ATC margin issue resolved.
* Tweak: Improved Featured review layout if no image added.
* Tweak: RTL for new Vertical left and Vertical right gallery layouts.

2.3.7 - 12-09-2024
* New: Can now display a featured review underneath the product gallery.
* New: Product Gallery now has "Vertical left" and "Vertical right" desktop display layouts.
* New: Shortcodes and Elementor Pro widgets now available for: Product Gallery and Free Shipping Notification modules.
* New: Ability to display the wishlist on the catalog or PDPs or both.
* New: Size Guides can now be assigned by product tag.
* New: Modules can now be disabled on the frontend but remain active in the admin to allow a store owner to populate large catalog data.
* New: Order Bump now supports WooCommerce Subscription products.
* New: Admin orders area now has a separate 'Order bumps' tab to filter orders which contain bumps.
* Fix: Sticky add to cart mobile scroll position on out of stock PDPs.
* Fix: Attribute color swatches image update improvement.
* Fix: PLP swatches "More options" label now displays rather than "Quick add" when certain conditions are met.
* Fix: Mobile Sticky Add to Cart is no longer cut if a lot of swatches are present, it inner scrolls.
* Tweak: When a swatch is selected, PLP and PDP price updates to the specific value rather than showing the range.
* Tweak: Improved Vimeo video display within the Product Gallery.
* Tweak: Product Gallery now supports .webm video files.
* Tweak: Product Gallery now supports Wistia video embeds.
* Tweak: Removed "Button added" Order Bump string as it updates too quickly to be displayed.
* Accessibility: Swatches are now grouped in a <fieldset> with a <legend> for improved a11y.
* Accessibility: Search suggestions when open, can now be closed via the ESC key when navigating by keyboard.

2.3.6 - 04-07-2024
* New: Stock meter high stock value now has a separate color selector option.
* Fix: Sticky add to cart close icon now visible on desktop. Better handling in displaying large swatches data on mobile.
* Fix: CommerceKit get_nonce should only happen once. Switched to use js-cookie instead which is part of core Woo.
* Tweak: Improved Waitlist module with Klaviyo form integration.
* Tweak: Improved error handling for products with empty attributes.
* Tweak: Improved dashboard logic that the attributes gallery module can only be active if the product gallery is enabled.

2.3.5 - 26-06-2024
* New: "wp commercekit-clear-cache" cli command added to allow a nightly cron to automate the clearing and regeneration of swatch caches.
* New: Order bump shortcode and Elementor Pro widget now available.
* Fix: "Quick add" PLP swatches label should be "More options" when certain conditions are met.
* Tweak: Product countdowns shortcode option now accepts an ID for better specificity.

2.3.4 - 19-06-2024
* New: Stock Meter now includes customizable low stock threshold values and labels.
* New: Size Guides can now be assigned by attribute as well as by product category.
* Tweak: Can now use page builders such as Elementor to create size guides content. 
* Tweak: Clear Ajax Search Index action now includes a Cancel button option while running.
* Accessibility: Can now use keyboard left/right arrows to navigate product gallery.
* Accessibility: Use of the tab key to browse the PDP improved, hidden swatch form removed from tabindex.

2.3.3 - 07-06-2024
* Fix: Required WP version update.

2.3.2 - 07-06-2024
* Fix: Select options button display within Elementor Pro carousel when attributes module is active.
* Fix: PHP warning message resolved within Waitlist module when product filters are applied but there are no results.
* Tweak: Desktop sticky add to cart now vertically scrolls if overflow content is present.
* Tweak: Exporter no longer stores the export CSV file on the filesystem.
* Tweak: Additional capability checks applied to generate_export_csv and generate_import_csv.
* Tweak: Additional capability checks applied to the reset search statistics and reset order bumps statistics buttons.

2.3.1 - 31-05-2024
* New: More polished admin interface.
* New: Admin area is now responsive on smaller viewports.
* New: Admin area RTL implementation work completed.
* New: Admin area displays current version number in header.
* New: Shortcodes and Elementor Pro widgets now available for: Countdowns, Inventory Bar, Wishlist and Size Guide modules.
* Tweak: Security enhancements to nonces.
* Tweak: Select2 JS library now checks if the Woo version is already enqueued and if so, uses that instead.
* Tweak: Improvements to ajax wishlist database queries.
* Fix: Product attribute gallery stopped working if you switched swatches within related products area.
* Sticky add to cart: Scroll event listener swapped with a more performant IntersectionObserver.
* Sticky add to cart: a11y controls added.
* Size Guides: Converted to a semantically correct <dialog> HTML element which is faster and has native a11y.
* Size Guides: Trigger is now a <button> element rather than a link.
* Order Bump: Arrows now work when accessed via the keyboard.
* Performance: Improved CSS structure for each module, with any Shoptimizer specific container markup removed.
* Performance: Separate countdown.css and stockmeter.css stylesheets rather than loading styles within the module.
* Compatibility: WPML improvements for Product Badges, Countdowns, Stock Meter, and Order Bumps.

2.3.0.1 - 03-04-2024
* New: Product Gallery Video now supports YouTube shorts, e.g. https://www.youtube.com/shorts/oM746KEbVuU
* New: Mobile product gallery "Show edge of next slide" option - can now adjust the % of next slide displayed.
* Fix: Unfinished scheduled actions left without being automatically removed.
* UX: Added save buttons to the end of the attributes gallery and attributes swatches metaboxes in the product editor admin area.
* Tweak: Improved attribute swatches compatibility with WPML.
* Tweak: Waitlist UX when Klaviyo is integrated is now similar to the default.
* Tweak: Color and image swatch sizes reduced slightly on mobile.
* Tweak: On mobile product pages, sticky add to cart button now only appears when primary add to cart button is off-screen.
* Tweak: Pagination now appears on the wishlist page. Previous maximum of 100 items removed so that you can have unlimited wishlist items.
* Tweak: Updated language file to include "Post" and "Page" strings from ajax search module.
* Tweak: Additional check will display warning if the active SQL version does not support FULLTEXT indexes.
* Tweak: "Add to cart" string in CommerceKit now uses the WooCommerce domain so it no longer needs to be translated separately.
* Compatibility: Trying to get property ID of non-object warning solved when Yoast SEO Pro active.

2.2.9 - 31-01-2024
* New - "Lightning Fast Search" option within Ajax Search module. Search cache needs to be cleared before use.
* New - Product Gallery includes a "Show edge of next slide" option on mobile.
* New - Order Bumps now include product tags as a condition option.
* Compatibility - Improved search suggestions compatibility with WPML plugin.
* Tweak - Waitlist includes an option to force the "From email" and "From name".
* Tweak - Condition added to check for CustomOrdersTableController.

2.2.8 - 15-12-2023
* Fix - Error when trying to edit a product with an older version of Woo (6.x) active.
* Tweak - "Choose" string can now be translated.

2.2.7 - 13-12-2023
* Fix - Search suggestions relevance logic improved.
* Tweak - Updated translations file.

2.2.6 - 01-12-2023
* Fix - Multiple order bumps on checkout page caused some display issues.
* Fix - Countdown custom message now displays correctly.

2.2.5 - 29-11-2023
* New - Ajax search module: huge performance improvements. Product search suggestions now loaded from CommerceKit cache tables.
* New - Ajax search module: can now reset the "Most frequent searches returning 0 results" table.
* New - Import/Export Attribute Galleries and Attribute Swatches option via: CommerceKit > Import/Export
* New - Waitlist now has a 'Products' section: see the most popular items (and their variations) and how many customers have signed up for each.
* New - Can now disable the CommerceKit waitlist for a particular item via a checkbox in the product editor.
* New - Size Guides can now be displayed via the default sliding panel or within a tab.
* New - Attribute Swatches settings has an aditional "Disable Loading Facade on Product Listings Pages" option.
* New - Countdowns now include an option to hide it when it reaches zero.
* New - Countdowns can now optionally display a custom message when it reaches zero.
* New - Order Bump now includes a "Cart total" condition option for even better customer value targeting.
* New - Order Bump statistics in the admin area can now be reset.
* New - HPOS compliance.
* Fix - Product page tab headings now highlight correctly when on the correct "active" tab area.
* Fix - Sticky add to cart button label changes to "Get notified" when a variation that is out of stock is selected and the waitlist is active.
* Fix - Google Search Console indexing alert resolved when embedding videos within the product gallery.
* Fix - Multiple Order Bumps now work correctly in RTL mode.
* Fix - Ajax Search: Adding a single quote caused a fatal error.
* Fix - Empty <a> tag appearing in Wishlist markup caused W3C validation issues.
* Tweak - CommerceKit tooltip attribute markup improved to include "data-" before each.
* Tweak - Order Bump thumbnail and title now links to the product.
* Tweak - Order Bump arrows repositioned to prevent them being cut-off.
* Tweak - Improved compatibility with WPML for attribute swatch label translations.
* Tweak - Sticky bar now toggles a class on/off when stuck for enhanced custom styling possibilities.
* Tweak - Waitlist export file now includes ID and SKU columns.
* Tweak - Removed emojis within default free shipping notifications text.

2.2.4 - 29-05-2023
* Fix - Attribute swatch variations appeared out of stock on listings pages when certain conditions were present.
* Tweak - Order bump now includes left and right arrows when multiple bumps are present.
* Tweak - Additional checks added to ensure fatal error doesn't occur when % is mistakenly used instead of %s within inventory bar module.

2.2.3 - 16-05-2023
* Performance - "skip-lazy" CSS class added to the first image within the Product Gallery.
* Performance - Lazy load swiper and photoswiper JS files in the footer.
* Performance - Videos now lazy load upon user interaction on mobile.
* Performance - Attribute swatches loading improvements on PLPs and PDPs which resolves layout shifts. On PLPs a loading facade now appears while the swatches are loading in the background.
* Compatibility - Product gallery now works better with the FlyingPress lazy loading option.
* Compatibility - Countdown timers now work with YITH Bundle Product Types.
* Compatibility - Product badges now work with the IconicWP "Show Single Variations" plugin.
* Compatibility - Sticky add to cart CTA buttons now work as quick-scroll anchors on WooCommerce Bundle products.
* UX - Clear cache option moved into separate Settings tab.
* UX - Improved approach to cancel and restarting Action Scheduler tasks.
* UX - Beta label removed from modules in admin area.
* UX - Attribute swatches can now be toggled on/off on PLPs and PDPs independently.
* UX - Made it possible to access the waitlist for out of stock items when "Out of stock visibility" is enabled.
* Fix - Order bumps should not display disabled variations.
* Fix - Waitlist notify button layout on grouped products.
* Fix - Product gallery minor layout shift on galleries with a lot of product images resolved.
* Tweak - Waitlist export now includes field "Mail sent - yes/no".
* Tweak - "Display hidden out stock products" option now available in the Waitlist admin.
* Tweak - Number of product gallery thumbnails visible is now filterable.
* Tweak - Free Shipping Notification "Continue Shopping" URL is now configurable via a new admin option.
* Tweak - New option to include arrows within the product gallery thumbnails slider.

2.2.2 - 07-03-2023
* New - Multiple order bumps within the mini cart and checkout.
* New - Product swatches can now display 2 colors if desired.
* New - Waitlist option to send mails to everyone on the list when an item is back in stock.
* New - Waitlist footer text input.
* New - Waitlist emails can now include HTML in contents.
* New - Choose a custom icon for the size guides.
* Fix - Product gallery on mobile no longer requires two interactions to initialize.
* Fix - Sticky add to cart bar mobile display issue.
* Tweak - Product gallery primary image now includes the fetchpriority="high" tag.
* Tweak - Reposition mobile gallery pagination dots to be underneath the gallery.
* Tweak - Swatches PLPs: when linking to the PDP and there is only one attribute remove the 'Select options' button.
* Tweak - Ajax search now supports custom taxonomies e.g. various brands plugins.
* Tweak - Ajax search suggestions previously had a max height of 600px, this is now dynamic.
* Tweak - Countdowns variable JS names changed to prevent conflicts.
* UX - Size guides admin area now suggests you create your first size guide if one does not exist.

2.2.1 - 31-01-2023
* New - Swatches on PLPs option. Can now link to the overall PDP or the selected swatch.
* Fix - Sticky ATC z-index issue, it appeared above sidebar cart when opened.
* Fix - Product swatch label didn't display when the label was '0'.
* Fix - Product Badges 500 error when assigned categories/tags were later deleted.
* Tweak - Sticky add to cart gap on mobile.
* Compatibility - Countdown timers now work with the WPC Grouped Product Type.
* Compatibility - Sticky ATC tabs now work with the WPC Grouped plugin.
* Compatibility - Waitlist with the Product Bundles plugin.
* UX - Settings link added to CommerceKit on the WordPress plugins page.

2.2.0 - 09-01-2023
* New - Minimal mobile layout option for product galleries.
* Enhancement - Added post meta to an order to record when an order bump has been added.
* Enhancement - New option allows for free shipping notification to be displayed by default before a customer enters their shipping address.
* Enhancement - PLP swatches when there is only a single variation now displays an add to cart button.
* Enhancement - Can now adjust stock meter colors and thresholds.
* Enhancement - Can now change number of products displayed within ajax search suggestions.
* Fix - PHP error when WooCommerce is disabled.
* Fix - Slow admin when Query Monitor plugin was active resolved.
* Fix - Free shipping notification - if a product is downloadable the free shipping bar was hidden.
* Fix - Sticky add to cart gap on mobile when sticky header is not active.
* Fix - Product gallery white space above image at certain proportions resolved.
* Fix - Attribute gallery not working based on attribute value.
* Fix - Attribute gallery image swap issue.
* Fix - Video page indexing: No thumbnail URL provided.
* Fix - Order bump setting "Button added" now displays correctly.
* Fix - Product gallery white space issue if first image isn't as tall as subsequent ones.
* Fix - When vertical scroll gallery enabled, clicking a swatch jumped to the bottom of the PDP.
* Fix - Wishlist items being duplicated after refreshing page.
* Tweak - Improved wishlist mobile styling.
* Tweak - "Configure" buttons in dashboard can now be translated - thanks to Aviel.
* Tweak - Single variation swatches now displays out of stock attributes better.
* Compatibility - Free shipping notification now works with the WooCommerce Flexible Pricing by WPDesk plugin.
* Compatibility - Product gallery now works with the WooCommerce 360º Image plugin.
* Compatibility - ElasticPress can now be integrated within Ajax Search module.
* Compatibility - Waitlist and Order bump modules with WPML.

CommerceKit 2.1.1:
* Fix - Product gallery lazy loading issue.
* Enhancement - Sticky add to cart bar module now works with grouped products.
* Enhancement - Product Badges can now be assigned to product tags.
* Tweak - Sticky add to cart scroll gap margin tweaks.
* Tweak - Attribute Swatches - added aria-label to color swatches for accessibility.
* Tweak - Free shipping notification output adjusted to use apply_filters.
* Tweak - Product gallery accessibility improvements.

2.1.0 - 11-08-2022
* New - Size Guides module.
* New - Product Badges module.
* New - Free shipping notification module.
* New - Vertical gallery option within the product gallery module.
* New - Simple scroll option within the product gallery module.
* New - Ajax search option to display out of stock products at the end of the results.
* New - Stock meter module option - can now adjust the low/regular/high stock thresholds within the admin area.
* Fix - Sticky add to cart compatibility with YITH Tab Manager plugin.
* Tweak - PDP swatch style for a single attribute when Waitlist module is enabled.
* Tweak - Improved Wishlist responsive styling within the "My account" area.

2.0.4.1 - 07-06-2022
* Fix - Resolved issue with sticky add to cart panel closing.
* Fix - Sticky add to cart cloned form problem when Waitlist module is enabled.
* Tweak - Improved Sticky add to cart tabs visibility with one row header layout in Shoptimizer.
* Tweak - Improved Sticky add to cart tabs visibility when mobile search bar is always visible.
* Tweak - Better sticky add to cart panel styling when regular <select> dropdowns are used on PDPs.
* Tweak - Sticky add to Cart RTL improvements.

2.0.3 - 27-05-2022
* New - Sticky add to cart module. More advanced sticky bar on PDPs for desktop and mobile. Includes option to expand default WooCommerce tabs content.
* New - Store owners can now see wishlist data! CommerceKit > Wishlist > Reports
* Fix - Mobile product gallery issue, it previously snapped back to the first image after scrolling.
* Accessibility - "Empty button" issue in WAVE resolved.
* Accessibility - "Broken same page link" issue in WAVE resolved.
* Accessibility - aria-hidden="true" added to color swatches.
* Accessibility - Countdown label color made darker.

2.0.2 - 08-04-2022
* New - Tooltips option for color and image swatches on PLPs and PDPs. Can be turned on/off within CommerceKit > Attribute Swatches
* New - Countdown single campaign option. Set a countdown to appear only between two specific dates and times. Ideal for sales.
* Fix - When Attribute Gallery module was disabled, old gallery images displayed.
* Fix - Attribute Swatches now works with non-Latin characters, e.g. Hebrew and Arabic.
* Fix - When Product Gallery module is active, Elementor's SwiperJS is now only disabled on PDPs.
* Fix - HTML validation warning resolved. "The itemprop attribute was specified, but the element is not a property of any item".
* Tweak - Improved compatibility with the WPML String Translation plugin.
* Tweak - Improved compatibility with WP Rocket and Imagify - particularly webp images.
* Tweak - Improved compatibility with PLP infinite scrolling/load more plugins.
* Tweak - Improved compatibility with YITH Badge Management plugin.
* Tweak - Improved compatibility with WooCommerce Composite Products plugin.
* Tweak - Improved compatibility with weDevs WooCommerce Conversion Tracking plugin.

2.0.1 - 11-03-2022
* Fix - Video no longer zoomed on mobile.
* Fix - Waitlist modal styling issue.
* Fix - Custom attributes "no selection" issue.
* Fix - Quick add to cart still applying on variations with a single attribute even when turned off.
* Fix - Vimeo Pro embed URLs now work correctly.

2.0.0 - 28-02-2022
* Dashboard - New logo and dashboard design.
* Product Gallery - New layouts! Grid: 2 cols x 4 rows, Grid: 3 cols, 1 col, 2 cols, Grid: 1 col, 2 cols, 2 cols.
* Product Gallery - Can switch an individual product's gallery layout style thanks to a new product page selection option.
* Product Gallery - Now supports variation images.
* Product Gallery - Now include videos in the product gallery.
* Product Gallery - Arrow style changed to make them more visible when product photos have a white background.
* Product Gallery - Font icon for arrows replaced with lighter SVGs.
* Product Gallery - Better compatibility with Elementor features on the single product page.
* (New) Product Attributes Gallery - assign images to a single attribute, so you no longer have to make a selection from say color and size before the image updates.
* (New) Attribute Swatches - Include color, image, and button swatches on the product page.
* Attribute Swatches - Include swatches also on the shop and catalog pages with (optional) quick add to cart functionality.
* Order bump - Fixed display issue when using specific category selections.
* Waitlist - Improved from and reply-to address handling.
* Waitlist - Improved email formatting.

1.3.0 - 10-08-2021
* Product gallery - NEW performance-focused gallery for single product pages.
* Ajax search - Fix for SQL message being displayed.
* Ajax search - Special characters now display correctly in the search suggestions.
* Waitlist - Fix for waitlist form not displaying for certain variations.
* Wishlist - Improved display when long product IDs are used.

1.2.8 - 19-07-2021
* Order bump - HTML encode fix.
* Countdown - Checkout countdown now correctly resets if previous cart items are removed, and new items are added.
* Stock meter - incorporates low stock threshold value if used.
* Stock meter - now works correctly with variation swatch plugin if labels/colors are selected.
* Waitlist - Disabled functionality for composite products.
* Waitlist - New notification recipient field option.

1.2.7 - 02-06-2021
* Remove DOMContentLoaded dependency JS throughout plugin.
* Ajax search - Fix for non published products appearing within search suggestions.
* Order bump - enhanced selection functionality for variable products.

1.2.6 - 12-05-2021
* Ajax search - New option to hide variations from search suggestions.
* Order bump - If PayPal Plus plugin active, reload checkout page if an order bump item is added.

1.2.5 - 21-04-2021
* Countdowns - Fixed undefined indexes PHP warning.
* Waitlist - Improved logic when an item is out of stock.
* Order bump - Solved some duplicated product image issues when lazy loading plugins were active.

1.2.4 - 24-03-2021
* Countdowns - Improved checkout countdown text wrapping display on smaller viewports.
* Ajax search - Removed DOMcontentloaded dependency so it can now be delayed.
* Ajax search - String added to translation file: "Search results for:"

1.2.3 - 04-03-2021
* General - Fixed notice on WP multisites.
* Order bump - Delete action fix in admin.
* Waitlist - Only emails a single time when an item is back in stock.

1.2.2 - 18-02-2021
* Stock meter - Position fix on variable products

1.2.1 - 08-02-2021
* Ajax search: Strip visible <span> tags from autocomplete results display.
* Order bump - Improved responsive CSS styling.
* Stock meter - JS fix.

1.2.0 - 27-01-2021
* Ajax search: Search by SKU now displays variations in results dropdown.

1.1.9 - 20-01-2021
* General: Fixed update notices.
* Wishlist: <br> tags no longer outputted if included in product titles.
* Waitlist: Removed PHPMailer sender field data.

1.1.8 - 12-01-2021
* Search: Result page tabs now translatable.
* Search: If catalog visibility set to hidden, it will not now appear in search results.
* Search: Excluded pages/products now removed from search results, not just suggestions.
* Search: Results page pagination issue resolved.
* Waitlist: Can include HTML in the label field.
* Waitlist: Checkbox not automatically ticked.
* Waitlist: Fixed button label issue on Grouped products.
* Waitlist: If backorders enabled, waitlist form will not appear.
* Countdowns: Now display on backordered items.
* Wishlist: aria-label added to links.
* Order bump: Fixed issue where adding an order bump sometimes removed credit card inputs.
* Stock meter: Responsive display improvement on mobile.
* Stock meter: More accurate level reflected in the bar width.
* Stock meter: Additional 'low-stock' CSS class added when stock level is low.

1.1.0 - 24-09-2020
* New - CommerceKit launched!
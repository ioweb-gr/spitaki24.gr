<?php
/**
 * Plugin Name:     Ioweb Customizer
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     ioweb-customizer
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Ioweb_Customizer
 */

// Your code starts here.

function io_prepare()
{
    add_filter(
        "body_class",
        function ($classes) {
            return array_merge($classes, array("io"));
        }
    );

    // Correct paths
    $stylesheetDir = plugin_dir_url(__FILE__) . "assets";
    $stylesheetDirPath = plugin_dir_path(__FILE__) . "assets";

    // Get the modification time of ioweb-custom.js
    $version = filemtime($stylesheetDirPath . "/build/ioweb-custom.js");

    // Enqueue scripts with the same version
    wp_enqueue_script("ioweb-runtime", $stylesheetDir . "/build/runtime.js", [], $version, true);
    wp_enqueue_script("ioweb-custom", $stylesheetDir . "/build/ioweb-custom.js", ["ioweb-runtime"], $version, true);

    // Enqueue style with the same version
    wp_enqueue_style("ioweb-custom", $stylesheetDir . "/build/ioweb-custom.css", [], $version);
}

add_action("wp_enqueue_scripts", "io_prepare");

//function ioweb_child_setup() {
//	$path = get_stylesheet_directory()."/languages";
//	load_child_theme_textdomain( "ioweb-child", $path );
//}
//add_action( "after_setup_theme", "ioweb_child_setup" );

add_action( "init", function(){
    register_nav_menu("oxygen-menu",__( "Oxygen Menu" ));
});



<?php
// Start the engine
require_once( get_template_directory() . '/lib/init.php' );

// Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'WJN 2014' );
define( 'CHILD_THEME_URL', 'https://willnorris.com/' );
define( 'CHILD_THEME_VERSION', '0.1.0' );

add_action( 'after_setup_theme', 'wjn2014_theme_setup' );
function wjn2014_theme_setup() {
	// Add HTML5 markup structure
	add_theme_support( 'html5' );

	// Add viewport meta tag for mobile browsers
	add_theme_support( 'genesis-responsive-viewport' );

	// Add support for custom background
	add_theme_support( 'custom-background' );

	// Add support for 3-column footer widgets
	add_theme_support( 'genesis-footer-widgets', 3 );

	// Unregister layout settings
	genesis_unregister_layout( 'content-sidebar-sidebar' );
	genesis_unregister_layout( 'sidebar-content-sidebar' );
	genesis_unregister_layout( 'sidebar-sidebar-content' );

	// Unregister secondary sidebar
	unregister_sidebar( 'sidebar-alt' );
}

// Enqueue Lato Google font
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'google-font-lato', '//fonts.googleapis.com/css?family=Lato:300,700', array(), null );
});

// Return {theme_dir}/css/style.css as the stylesheet_uri.
add_filter( 'stylesheet_uri', function() {
	return get_stylesheet_directory_uri() . '/css/style.css';
});

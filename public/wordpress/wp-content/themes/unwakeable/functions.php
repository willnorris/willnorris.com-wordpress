<?php 
/* Current version of K2 */
$current = '1.2';

load_theme_textdomain('k2_domain');

/* Blast you red baron! Initialise the k2 system */

require(TEMPLATEPATH . '/options/app/archive.php');
require(TEMPLATEPATH . '/options/app/options.php');
require(TEMPLATEPATH . '/options/app/update.php');
require(TEMPLATEPATH . '/options/app/info.php');
require(TEMPLATEPATH . '/options/app/tools.php');

// Install and update K2 if necessary
global $options_revision;
if (!get_option('unwakeable_optionsrevision') or get_option('unwakeable_optionsrevision') < $options_revision) {
	installk2::installer();
}

// Let's add the options page.
add_action ('admin_menu', 'k2menu');

$k2loc = '../themes/'.basename(dirname($file)); 

function k2menu() {
	add_submenu_page('themes.php', __('Unwakeable Options','k2_domain'), __('Unwakeable Options','k2_domain'), 5, $k2loc . 'functions.php', 'menu');
}

function menu() {
	load_plugin_textdomain('k2options');
	//this begins the admin page

	include(TEMPLATEPATH . '/options/display/form.php');
}

// include Hasse R. Hansen's K2 header plugin - http://www.ramlev.dk
require(TEMPLATEPATH . '/options/display/headers.php');

// Sidebar Modules for K2
if(class_exists('k2sbm')) {
	k2sbm::wp_bootstrap();
}

// Sidebar registration for dynamic sidebars
if(function_exists('register_sidebar')) {
	register_sidebar(array('before_widget' => '<div id="%1$s" class="widget %2$s">','after_widget' => '</div>'));
}

// this ends the admin page ?>

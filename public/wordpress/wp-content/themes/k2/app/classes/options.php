<?php

class K2Options {
	function init() {
		add_action('admin_menu', array('K2Options', 'add_menu'));

		// Check for K2 uninstallation. Do here to avoid header output.
		if($_GET['page'] == 'k2-options' and isset($_POST['uninstall'])) {
			K2::uninstall();
		}
	}

	function add_menu() {
		add_submenu_page('themes.php', __('K2 Options','k2_domain'), __('K2 Options','k2_domain'), 5, 'k2-options', array('K2Options', 'admin'));
	}

	function admin() {
		include(TEMPLATEPATH . '/app/display/options.php');
	}

	function update() {
		if(!empty($_POST)) {
			if(isset($_POST['k2'])) {

				// Advanced Navigation
				if(isset($_POST['k2']['advnav'])) {
					update_option('k2livesearch', '1');
					update_option('k2rollingarchives', '1');
				} else {
					update_option('k2livesearch', '0');
					update_option('k2rollingarchives', '0');
				}

				// Archives Page
				if(isset($_POST['k2']['archives'])) {
					update_option('k2archives', '1');
					K2Archive::create_archive();
				} else {
					// thanks to Michael Hampton, http://www.ioerror.us/ for the assist
					update_option('k2archives', '0');
					K2Archive::delete_archive();
				}

				// Live Commenting
				if(isset($_POST['k2']['livecommenting'])) {
					update_option('k2livecommenting', '1');
				} else {
					update_option('k2livecommenting', '0');
				}

				// Set all the options
				foreach($_POST['k2'] as $option => $value) {
					update_option('k2' . $option, $value);
				}
				
				if(isset($_POST['k2']['scheme'])) {
					k2styleinfo_update();
				}

				if(isset($_POST['k2']['styleinfo_format'])) {
					k2styleinfo_update();
				}
			}

			if(isset($_POST['configela']) and !K2Archive::setup_archive()) {
				unset($_POST['configela']);
			}
		}
	}

	function install() {
		add_option('k2asidescategory', '0', 'A category which will be treated differently from other categories');
		add_option('k2livesearch', '1', "If you don't trust JavaScript and Ajax, you can turn off LiveSearch. Otherwise I suggest you leave it on"); // (live & classic)
		add_option('k2archives', '0', 'Set whether K2 has a Live Archive page');
		add_option('k2scheme', '', 'Choose the Scheme you want K2 to use');
		add_option('k2livecommenting', '1', "If you don't trust JavaScript, you can turn off Live Commenting. Otherwise it is suggested you leave it on");
		add_option('k2styleinfo_format', 'Current style is <a href="%stylelink%" title="%style% by %author%">%style% %version%</a> by <a href="%site%">%author%</a><br />', 'Format for displaying the current selected style info.');
		add_option('k2styleinfo', '', 'Formatted string for style info display.');
		add_option('k2rollingarchives', '1', "If you don't trust JavaScript and Ajax, you can turn off Rolling Archives. Otherwise it is suggested you leave it on");
		add_option('k2blogornoblog', 'Blog', 'The text on the first tab in the header navigation.');
	}

	function cleanup_depreciated() {
		// Removes options that are no longer used.

		delete_option('k2asidesposition');
		delete_option('k2asidesnumber');
		delete_option('k2widthtype');
		delete_option('k2aboutblurp');
	}

	function uninstall() {
		// Ensure the archives are deleted
		K2Archive::delete_archive();

		delete_option('k2asidescategory');
		delete_option('k2livesearch');
		delete_option('k2archives');
		delete_option('k2scheme');
		delete_option('k2livecommenting');
		delete_option('k2styleinfo_format');
		delete_option('k2styleinfo');
		delete_option('k2rollingarchives');
		delete_option('k2blogornoblog');
	}
}

add_action('k2_init', array('K2Options', 'init'), 1);
add_action('k2_install', array('K2Options', 'install'));
add_action('k2_install', array('K2Options', 'cleanup_depreciated'));
add_action('k2_uninstall', array('K2Options', 'uninstall'));

?>

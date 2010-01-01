<?php

/**
 * Cleanup lots of hooks from WordPress, thematic, and plugins.
 */
function willnorris_cleanup_hooks() {
	// don't import hcard on account creation (I've had problems with this in the past)
	remove_action('user_register', 'ext_profile_hcard_import');

	if ( !is_front_page() ) {
		remove_action('wp_head', 'actionstream_styles');
		remove_action('wp_head', 'actionstream_ext_styles', 11);
		remove_action('wp_head', 'actionstream_personal_styles', 11);
		wp_deregister_style('ext-profile');
	}

	global $wp_scripts, $wp_styles;

	if ( !is_a($wp_scripts, 'WP_Scripts') )
		$wp_scripts = new WP_Scripts();

	if ( !is_a($wp_styles, 'WP_Styles') )
		$wp_styles = new WP_Styles();


	// move scripts to the footer
	$wp_scripts->add_data('jquery', 'group', 1);
	$wp_scripts->add_data('comment-reply', 'group', 1);
	if ( wp_script_is('ga-external-tracking') ) {
		wp_enqueue_script('ga-external-tracking', plugins_url('/google-analyticator/external-tracking.js'), array('jquery'), false, true);
	}


	if ( has_action('wp_head', 'wp_imagefit_js') ) {
		$wp_scripts->add_data('jquery.imagefit', 'group', 1);
		remove_action('wp_head', 'wp_imagefit_js');

		if ( is_front_page() ) {
			// remove jquery plugin from front page
			$key = array_search('jquery.imagefit', $wp_scripts->queue);
			$wp_scripts->dequeue($key);
		} else {
			// add imagefit to footer on non-front page
			add_action('wp_footer', 'wp_imagefit_js', 20);
		}
	}


	// don't load mint on page previews
	if ( is_preview() ) {
		remove_action('wp_footer', 'add_mint_javascript');
	}

	// ensure comment related stuff is only included when it makes sense
	$comments = false;
	if (is_single() || is_page() || is_comments_popup()) {
		global $post;
		if ( 'open' == $post->comment_status ) {
			$comments = true;
		}
	}

	if ( $comments == false ) {
		remove_action('wp_head', 'quoter_head');
		wp_deregister_script('comment-reply');
	}


	// fix share this plugin
	if (function_exists('st_widget')) {
		if (is_single()) { 
			add_filter('the_content', create_function('$c', 'return $c."<p>".st_widget()."</p>";'));
		} else {
			remove_action('wp_head', 'st_widget_head');
		}
	}

	remove_filter('get_avatar', 'ext_profile_avatar');

	if (is_front_page()) {
		// move do_shortcode BEFORE wpautop
		//remove_filter('the_content', 'wpautop');
		//remove_filter('the_content', 'do_shortcode', 11);
		//add_filter('the_content', 'do_shortcode', 9);
	}
}
add_filter('wp', 'willnorris_cleanup_hooks', 20);




function willnorris_admin_init() {
	$supercache_hook = get_plugin_page_hook('wpsupercache', 'options-general.php');
	if ( $supercache_hook) {
		add_filter('load-' . $supercache_hook, create_function('', 'add_filter("admin_footer", "willnorris_admin_footer_supercache");'));
	}
}
add_filter('admin_init', 'willnorris_admin_init');

function willnorris_admin_footer_supercache() {
	// hide annoying box on wp-super-cache config page
	echo '<script type="text/javascript">
		jQuery("h3:contains(\'Make WordPress Faster\')").closest("td").hide();
	</script>';
}


/**
 * Address bug in thematic_create_robots
 */
function willnorris_create_robots( $content ) {
	if ( is_home() && is_paged() ) {
		$content = "\t<meta name=\"robots\" content=\"noindex,follow\" />\n\n";
	}

	return $content;
}
add_filter('thematic_create_robots', 'willnorris_create_robots');


/**
 * Don't include thematics head scripts, which are primarily superfish and related jQuery plugins.
 */
function willnorris_head_scripts($scripts) {
	return '';
}
add_filter('thematic_head_scripts', 'willnorris_head_scripts');



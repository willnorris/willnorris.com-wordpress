<?php 
/* 
 Plugin Name: Extended Live Archives 
 Plugin URI: http://www.sonsofskadi.net/extended-live-archive/
 Description: Implements a dynamic archive, inspired by <a href="http://binarybonsai.com/archives/2004/11/21/freya-dissection/#livearchives">Binary Bonsai</a> and the original <a href="http://www.jonas.rabbe.com/archives/2005/05/08/super-archives-plugin-for-wordpress/">Super Archives by Jonas Rabbe</a>. Visit <a href="options-general.php?page=af-extended-live-archive/af-extended-live-archive-options.php">the ELA option panel</a> to initialize the plugin.
 Version: 0.10beta-r18
 Author: Arnaud Froment
 Author URI: http://www.sonsofskadi.net/ 
 */
/*
// +----------------------------------------------------------------------+
// | Licenses and copyright acknowledgements are located at               |
// | http://www.sonsofskadi.net/wp-content/elalicenses.txt                |
// +----------------------------------------------------------------------+
*/

$af_ela_cache_root = dirname(__FILE__) . '/cache/';
$debug = false;
$utw_is_present = false;

if (file_exists(ABSPATH . 'wp-content/plugins/UltimateTagWarrior/ultimate-tag-warrior-core.php') && in_array('UltimateTagWarrior/ultimate-tag-warrior.php', get_option('active_plugins'))) {
	@require_once(ABSPATH . 'wp-content/plugins/UltimateTagWarrior/ultimate-tag-warrior-core.php');
	$utw_is_present=true;
}

require_once(dirname(__FILE__)."/af-extended-live-archive-include.php");

/***************************************
 * Main template function.
 **************************************/	 
function af_ela_super_archive($arguments = '') {
	global $wpdb, $af_ela_cache_root;
	
	$settings = get_option('af_ela_options');
	$is_initialized = get_option('af_ela_is_initialized');
	if (!$settings || !$is_initialized || strstr($settings['installed_version'], $is_initialized) === false ) {
		echo '<div id="af-ela"><p class="alert">Plugin is not initialized. Admin or blog owner, <a href="' . get_settings('siteurl') . '/wp-admin/options-general.php?page=af-extended-live-archive/af-extended-live-archive-options.php">visit the ELA option panel</a> in your admin section.</p></div>';
		return false;
	}
	
	$settings['loading_content'] = urldecode($settings['loading_content']);
	$settings['idle_content'] = urldecode($settings['idle_content']);
	$settings['selected_text'] = urldecode($settings['selected_text']);
	$settings['truncate_title_text'] = urldecode($settings['truncate_title_text']);
	$settings['paged_post_next'] = urldecode($settings['paged_post_next']);
	$settings['paged_post_prev'] = urldecode($settings['paged_post_prev']);
	
	$options = get_option('af_ela_super_archive');
	
	if( $options === false ) {
		// create and store default options
		$options = array();
		$options['num_posts'] = 0;
		$options['last_post_id'] = 0;
	}
	
	$num_posts = $wpdb->get_var("
		SELECT COUNT(ID) 
		FROM $wpdb->posts 
		WHERE post_status = 'publish'");
		
	$last_post_id = $wpdb->get_var("
		SELECT ID 
		FROM $wpdb->posts 
		WHERE post_status = 'publish' 
		ORDER BY post_date DESC LIMIT 1");
	
		
	if( !is_dir($af_ela_cache_root) || !is_file($af_ela_cache_root.'years.dat') || $num_posts != $options['num_posts'] || $last_post_id != $options['last_post_id'] ) {
		$options['num_posts'] = $num_posts;
		$options['last_post_id'] = $last_post_id;
		update_option('af_ela_super_archive', $options);

		
		$res = af_ela_create_cache($settings);
		
		if( $res === false ) {
			// we could not create the cache, bail with error message
			echo '<div id="'.$settings['id'].'"><p class="'.$settings['error_class'].'">Could not create cache. Make sure the wp-content folder is writable by the web server. If you have doubts, set the permission on wp-content to 0777</p></div>';
			return false;
		}
	
	}
	
	$year = date('Y');
	$plugin_path = get_settings('siteurl') . '/wp-content/plugins/af-extended-live-archive';
	

	$text .= <<<TEXT

<script src="$plugin_path/includes/af-extended-live-archive.js.php" type="text/javascript"></script>
<div id="${settings['id']}"></div>

TEXT;

	echo $text;
}

/***************************************
 * loading stuff in the header.
 **************************************/	
function af_ela_header() {
	// loading stuff
	$settings = get_option('af_ela_options');
	$plugin_path = get_settings('siteurl') . '/wp-content/plugins/af-extended-live-archive';
	if ($settings['use_default_style']) {
		if (file_exists(ABSPATH . 'wp-content/themes/' . get_template() . '/ela.css')) {
			$csspath = get_bloginfo('template_url')."/ela.css";
		} else {
			$csspath =$plugin_path."/includes/af-ela-style.css";
		}
	
		$text = <<<TEXT

	<link rel="stylesheet" href="$csspath" type="text/css" media="screen" />

TEXT;
	} else { 
		$text ='';
	}

	echo $text;
}


/***************************************
 * actions when a comment changes.	
 **************************************/ 
function af_ela_comment_change($id) {
	global $wpdb;
	$generator = new af_ela_classGenerator;
	
	$settings = get_option('af_ela_options');
	
	if ($id) $generator->buildPostToGenerateTable($settings['excluded_categories'], $id, true);
	
	$generator->buildPostsInMonthsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks'], $generator->postToGenerate['post_id']);
		
	$generator->buildPostsInCatsTable($settings['excluded_categories'],$settings['hide_pingbacks_and_trackbacks'], $generator->postToGenerate['post_id'] );

	return $id;
}

/***************************************
 * actions when a post changes.
 **************************************/	
function af_ela_post_change($id) {
	global $wpdb,$utw_is_present;
	$generator = new af_ela_classGenerator;
	
	$settings = get_option('af_ela_options');
	
	if ($id) {
		$generator->buildPostToGenerateTable($settings['excluded_categories'], $id);
	}
					
	if(!$settings['tag_soup_cut'] || empty($settings['tag_soup_X'])) { 
		$order = false;
		$idTags = $id;
	} else {
		$order = $settings['tag_soup_cut'];
		$orderparam = $settings['tag_soup_X'];
		$idTags = false;
	}
	
	$generator->buildYearsTable($settings['excluded_categories'], $id);
	
	$generator->buildMonthsTable($settings['excluded_categories'], $id);
	
	$generator->buildPostsInMonthsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks'], $id);
		
	$generator->buildCatsTable($settings['excluded_categories'], $id);
	
	$generator->buildPostsInCatsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks']);
	
	if($utw_is_present) $ret = $generator->buildTagsTable($settings['excluded_categories'], $idTags, $order, $orderparam);
		
	if($ret && $utw_is_present) $generator->buildPostsInTagsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks']);
	
	return $id;
}

/***************************************
 * creation of the cache
 **************************************/	
function af_ela_create_cache($settings) {
	global $wpdb, $af_ela_cache_root, $utw_is_present;

	if( !is_dir($af_ela_cache_root) ) {
		if(!af_ela_create_cache_dir()) return false;
	}
	
	$generator = new af_ela_classGenerator;
	
	if(!$settings['tag_soup_cut'] || empty($settings['tag_soup_X'])) { 
		$order = false;
	} else {
		$order = $settings['tag_soup_cut'];
		$orderparam = $settings['tag_soup_X'];
	}
	
	$generator->buildYearsTable($settings['excluded_categories']);

	$generator->buildMonthsTable($settings['excluded_categories']);
	
	$generator->buildPostsInMonthsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks']);

	$generator->buildCatsTable($settings['excluded_categories']);

	$generator->buildPostsInCatsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks']);
	
	if($utw_is_present) $ret = $generator->buildTagsTable($settings['excluded_categories'], false, $order, $orderparam);
	
	if($ret && $utw_is_present) $generator->buildPostsInTagsTable($settings['excluded_categories'], $settings['hide_pingbacks_and_trackbacks']);
	
	return true;
}


/***************************************
 * Force settings from external plugin.
 * TODO  need to do some more checks 
 **************************************/
function af_ela_set_config($config, $reset=false) {
	global $wpdb, $af_ela_cache_root, $utw_is_present;

	$settings = get_option('af_ela_options');
	
	foreach($config as $optionKey => $optionValue) {
		switch($optionKey) {
		case 'newest_first':
		case 'num_entries' :
		case 'num_entries_tagged' :
		case 'num_comments':
		case 'fade':
		case 'hide_pingbacks_and_trackbacks':
		case 'use_default_style':
		case 'paged_posts':
		case 'truncate_title_at_space':
		case 'abbreviated_month':
			if($optionValue != 0 && $optionValue != 1) return -1;	
			break;
		case 'tag_soup_cut':
		case 'tag_soup_X':
		case 'truncate_title_length':
		case 'truncate_cat_length' :
		case 'excluded_categories' :
		case 'paged_post_num' :
			//if(!is_numeric($optionValue)) return -2;	
			break;
		case 'menu_order' : 
			$table = split(',',$optionValue);
			foreach($table as $content) {
				if ($content != 'chrono' && $content != 'cats' && $content != 'tags' && !empty($content)) return -3;
			}
			break;
		default :
			break;
		}
	}
	$config['last_modified'] = gmdate("D, d M Y H:i:s",time());
	if (!$reset) $config = array_merge($settings, $config);
	logthis($config);
	update_option('af_ela_options', $config, 'Set of Options for Extended Live Archive');
	
	return true;
}

/***************************************
 * bound admin page.
 **************************************/
function af_ela_admin_pages() {
	if (function_exists('add_options_page')) add_options_page('Ext. Live Archive Options', 'Ext. Live Archive', 9, get_settings('siteurl') . '/wp-content/plugins/af-extended-live-archive/af-extended-live-archive-options.php');
}


// insert javascript in headers
add_action('wp_head', 'af_ela_header');
// make sure the cache is rebuilt when post changes
add_action('publish_post', 'af_ela_post_change');
add_action('delete_post', 'af_ela_post_change');
// make sure the cache is rebuilt when comments change
add_action('comment_post', 'af_ela_comment_change');
add_action('trackback_post', 'af_ela_comment_change');
add_action('pingback_post', 'af_ela_comment_change');
add_action('delete_comment', 'af_ela_comment_change');
add_action('admin_menu', 'af_ela_admin_pages');

?>
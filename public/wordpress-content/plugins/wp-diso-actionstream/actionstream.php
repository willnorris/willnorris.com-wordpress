<?php
/*
Plugin Name: Actionstream
Version: 0.50
Plugin URI: http://singpolyma.net/plugins/actionstream/
Description: Shows updates from activities across the web.
Author: DiSo Development Team
Author URI: http://code.google.com/p/diso/
*/

//Copyright 2008 Stephen Paul Weber
//Released under the terms of an MIT-style license

register_activation_hook(__FILE__,'actionstream_plugin_activation');
add_action( 'actionstream_poll', 'actionstream_poll' );

require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/classes.php';
require_once dirname(__FILE__).'/widget.php';

/* wordpress */
global $actionstream_config;

/**
 * Activate the plugin.  This sets up the scheduled event and creates the database table.
 */
function actionstream_plugin_activation() {
	global $actionstream_config;
	wp_schedule_event(time(), 'hourly', 'actionstream_poll');
	$sql = "CREATE TABLE ".activity_stream_items_table()." (
				identifier_hash CHAR(40)  PRIMARY KEY,
				user_id INT, created_on INT,
				service CHAR(15),
				setup_idx CHAR(15),
				data TEXT
			);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}


/**
 * Update activity stream for each user of the blog.
 */
function actionstream_poll() {
	$users = get_users_of_blog();

	foreach($users as $user) {
		$actionstream = get_usermeta($user->ID, 'actionstream');
		if (!is_array($actionstream) || empty($actionstream)) { continue; }
		$actionstream = new ActionStream($actionstream, $user->user_id);
		$actionstream->update();
	}

}


/**
 * Get raw content of specified URL.
 *
 * @param string $url URL to get content for
 * @return string raw content for specified URL
 */
function get_raw_actionstream($url) {
	return wp_remote_fopen($url);
}


/**
 * Load activity stream stylesheet.
 */
function actionstream_styles() {
	$url = plugins_url('wp-diso-actionstream/css/action-streams.css');
	echo '<link rel="stylesheet" type="text/css" href="' . clean_url($url) . '" />';
}
add_action('wp_head', 'actionstream_styles');
add_action('admin_head', 'actionstream_styles');


/**
 * Activity stream admin page.
 */
function actionstream_page() {
	$actionstream_yaml = get_actionstream_config();
	$user = wp_get_current_user();

	$actionstream = get_usermeta($user->ID, 'actionstream');
	if(!$actionstream) {
		$actionstream = ActionStream::from_urls(get_usermeta($user->ID, 'user_url'), get_usermeta($user->ID, 'urls'));
		unset($actionstream['website']);
		update_usermeta($user->ID, 'actionstream', $actionstream);
		update_usermeta($user->ID, 'actionstream_local_updates', true);
		update_usermeta($user->ID, 'actionstream_collapse_similar', true);
	}

	if ( $_POST['submit'] ) {
		check_admin_referer('actionstream-update-services');
		update_usermeta($user->ID, 'actionstream_local_updates', isset($_POST['enable_local_updates']) ? true : false);
		update_usermeta($user->ID, 'actionstream_collapse_similar', isset($_POST['enable_collapse_similar']) ? true : false);


		if ( $_POST['ident'] ) {
			$actionstream[$_POST['service']] = $_POST['ident'];
			update_usermeta($user->ID, 'actionstream', $actionstream);
			actionstream_poll();
		}

		if ( $_POST['sgapi_import'] ) {
			require_once dirname(__FILE__).'/lib/sgapi.php';
			$sga = new SocialGraphApi(array('edgesout'=>1,'edgesin'=>0,'followme'=>1,'sgn'=>0));
			$xfn = $sga->get($_POST['sgapi_import']);
			$actionstream = array_merge($actionstream, ActionStream::from_urls('',array_keys($xfn['nodes'])));
			unset($actionstream['website']);
			update_usermeta($user->ID, 'actionstream', $actionstream);
		}

	}
	get_currentuserinfo();

	if ( isset($_REQUEST['update']) ) {
		check_admin_referer('actionstream-update-now');
		actionstream_poll();
	}

	if ( isset($_REQUEST['remove']) ) {
		check_admin_referer('actionstream-remove-' . $_REQUEST['remove']);
		unset($actionstream[$_REQUEST['remove']]);
		update_usermeta($user->ID, 'actionstream', $actionstream);
	}

	$next_poll = absint( wp_next_scheduled('actionstream_poll') - time() );
?>
	<div class="wrap" style="max-width: 99%;">

	<h2>Action Stream</h2>

	<div class="highlight" style="float: right; width: 47.5%; color: #333; padding: 0 1em 1em; margin: 1em; border: 1px solid #dadada; ">
		<h3>Stream Preview</h3>
		<p>
			<b>Next Update:</b> <?php printf('%d minutes %02d seconds', floor($next_poll / 60), ($next_poll % 60)); ?>
			<small>(<a href="<?php echo wp_nonce_url('?page=wp-diso-actionstream&update=1', 'actionstream-update-now') ?>">Update Now</a>)</small></p>
		</p>

		<?php actionstream_render($user->ID, 10); ?>
	</div>


	<div style="width: 47.5%">
		<ul style="padding:0px;">
<?php
	ksort($actionstream);
	foreach($actionstream as $service => $id) {
		$setup = $actionstream_yaml['profile_services'][$service];
		$remove_link = wp_nonce_url('?page='.$_REQUEST['page'].'&remove='.htmlspecialchars($service), 'actionstream-remove-'.htmlspecialchars($service));
		echo '<li style="padding-left:30px;" class="service-icon service-'.htmlspecialchars($service).'"><a href="'.$remove_link.'"><img alt="Remove Service" src="'.clean_url(plugins_url('wp-diso-actionstream/images/delete.gif')).'" /></a> ';
			echo htmlspecialchars($setup['name'] ? $setup['name'] : ucwords($service)).' : ';
			if($setup['url']) echo ' <a href="'.htmlspecialchars(str_replace('%s', $id, $setup['url'])).'">';
			echo htmlspecialchars($id);
			if($setup['url']) echo '</a>';
			if (empty($setup)) {
				echo ' <small><em>(configuration missing)</em></small>';
			}
			echo '</li>';
	}
?>
		</ul>
		<br />

		<h3>Update Services</h3>
		<form method="post" action="?page=<?php echo $_REQUEST['page'] ?>">
			<?php wp_nonce_field('actionstream-update-services'); ?>

			<p>
				<input type="checkbox" id="enable_local_updates" name="enable_local_updates"<?php checked( get_usermeta($user->ID, 'actionstream_local_updates'), true ) ?>/>
				<label for="enable_local_updates">Show Local Updates</label> 
			</p>

			<p>
				<input type="checkbox" id="enable_collapse_similar" name="enable_collapse_similar"<?php checked( get_usermeta($user->ID, 'actionstream_collapse_similar'), true ) ?>/>
				<label for="enable_collapse_similar">Collapse Similar Items</a></label>
			</p>

			<h4>Add/Update Service</h4>
			<div style="margin-left: 2em;">
				<select id="add-service" name="service" onchange="update_ident_form();">
					<?php
						ksort($actionstream_yaml['action_streams']);
						foreach($actionstream_yaml['action_streams'] as $service => $setup) {
							if($setup['scraper']) continue;//FIXME: we don't support scraper yet
							$setup = $actionstream_yaml['profile_services'][$service];
							echo '<option class="service-icon service-'.htmlspecialchars($service).'" value="'.htmlspecialchars($service).'" title="'.htmlspecialchars($setup['url']).'|'.htmlspecialchars($setup['ident_example']).'|'.htmlspecialchars($setup['ident_label']).'">';
							echo htmlspecialchars($setup['name'] ? $setup['name'] : ucwords($service));
							echo '</option>';
						}
					?>
				</select> <br />
				<label for="add-ident">
					<span id="add-ident-pre"></span>
					<input type="text" id="add-ident" name="ident" />
					<span id="add-ident-post"></span>
				</label>
			</div>

			<script type="text/javascript">
				function update_ident_form() {
					var option = document.getElementById('add-service').options[document.getElementById('add-service').selectedIndex];
					var data = option.title.split(/\|/);
					document.getElementById('add-ident-pre').innerHTML = data[0].split(/%s/)[0] ? data[0].split(/%s/)[0] : '';
					document.getElementById('add-ident-post').innerHTML = data[0].split(/%s/)[1] ? data[0].split(/%s/)[1] : '';
					if(data[1]) document.getElementById('add-ident-pre').title = 'Example: ' + data[0].replace(/%s/, data[1]);
						else document.getElementById('add-ident-pre').title = '';
					document.getElementById('add-ident').title = document.getElementById('add-ident-pre').title;
					document.getElementById('add-ident').value = data[2];
				}
				update_ident_form();
			</script>

			<h4>Import List from Another Service</h4>
			<div style="margin-left: 2em;">
				<p>Any supported urls with <code>rel="me"</code> will be imported</p>
				<input type="text" name="sgapi_import" />
			</div>
			<p class="submit">
				<input class="button-primary" type="submit" name="submit" value="Save Changes" />
			</p>
		</form>

	</div>
	</div>
<?php
}//end function actionstream_page


/**
 * Add "manage" link to activity stream on WordPress plugins page.
 */
function actionstream_plugin_actions($links, $file) {
	static $this_plugin;
	if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if($file == $this_plugin) {
		$settings_link = '<a href="users.php?page=wp-diso-actionstream" style="font-weight:bold;">Manage</a>';
		$links[] = $settings_link;
	}//end if this_plugin
	return $links;
}


/**
 * Add activity stream admin pages.
 */
function actionstream_tab($s) {
	add_submenu_page('profile.php', 'Action Stream', 'Action Stream', 'read', 'wp-diso-actionstream', 'actionstream_page');
	add_filter('plugin_action_links', 'actionstream_plugin_actions', 10, 2);
	return $s;
}
add_action('admin_menu', 'actionstream_tab');


/**
 * After publishing a new WordPress post, add a new activity stream entry.
 */
function actionstream_wordpress_post($post_id) {
	$post = get_post($post_id);
	$item = array();
	$item['title'] = $post->post_title;
	$item['url'] = get_permalink($post->ID);
	$item['identifier'] = $item['url'];
	$item['description'] = $post->post_excerpt;
	if(!$item['description']) $item['description'] = substr(html_entity_decode(strip_tags($post->post_content)),0,200);
	$item['created_on'] = strtotime($post->post_date_gmt.'Z');
	$item['ident'] = get_userdata($post->post_author);
	if(!$item['ident']->actionstream_local_updates) return;
	$item['ident'] = $item['ident']->display_name;
	$obj = new ActionStreamItem($item, 'website', 'posted', $post->post_author);
	$obj->save();
}
add_action('publish_post', 'actionstream_wordpress_post');


/**
 * Render the activity stream for the specified user.
 *
 * @param int $user_id ID of user to display activity stream for
 * @param int $num maximum number of activities to display
 * @param boolean $hide_user
 * @param boolean $echo whether to echo the rendered activity stream
 * @return string the rendered activity stream
 */
function actionstream_render($user_id, $num=10, $hide_user=false, $echo=true) {
	$userdata = get_userdata($user_id);

	$rtrn = new ActionStream($userdata->actionstream, $userdata->ID);
	$rtrn = $rtrn->toString($num, $hide_user, $userdata->profile_permissions, $userdata->actionstream_collapse_similar);
	if($echo) echo $rtrn;
	return $rtrn;
}


/**
 * Render a list of the activity stream services for the specified user.
 *
 * @param int $user_id ID of user to display activity stream for
 * @param boolean $urls_only
 */
function actionstream_services($user_id, $urls_only=false) {
   $userdata = get_userdata($user_id);
   $actionstream = $userdata->actionstream;
   if ( empty($actionstream) ) return;
   ksort($actionstream);

   $actionstream_yaml = get_actionstream_config(); 
	$rtrn = array();
   foreach ($actionstream as $service => $username) {
		if(function_exists('diso_user_is') && !diso_user_is($userdata->profile_permissions[$service])) continue;
	   $setup = $actionstream_yaml['profile_services'][$service];
	   if (empty($setup)) { continue; }
	   $url = sprintf($setup['url'], $username);
		if(!$urls_only) {
			if($userdata->urls && count($userdata->urls) && in_array($url, $userdata->urls))
			   array_unshift($rtrn, '<li class="service-icon service-'.$service.' profile"><a href="'.$url.'" class="url" rel="me">'.$setup['name'].'</a></li>' . "\n");
			else
			   $rtrn[] = '<li class="service-icon service-'.$service.'"><a href="'.$url.'" class="url" rel="me">'.$setup['name'].'</a></li>' . "\n";
		} else {
			$rtrn[] = $url;
		}
   }
   if(!$urls_only) $rtrn = '<ul class="actionstream_services">' . "\n" . implode("\n",$rtrn) . '</ul>' . "\n";

   return $rtrn;
}


/**
 * Get the ID of the user for the specified ID or username.  If no ID or 
 * username is provided, the ID of the admin user will be returned.
 *
 * @param int|string $user_id ID or user_login of user to get ID for
 * @return int ID of user
 */
function activity_stream_get_user_id($user_id = false) {
	if( !$user_id ) {
		//get administrator
		global $wpdb;
		return $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta WHERE meta_key='wp_user_level' AND meta_value='10'");
	}

	if( is_numeric($user_id) ) {
		return $user_id;
	} else {
		$userdata = get_userdatabylogin($user_id);
		return $userdata->ID;
	}
}


/**
 * Process [activity-stream] shortcode.  This shortcode supports three
 * parameters: user, num, and hide_user.  The user parameter may alternately
 * be passed in the shortcode content.
 *
 * @see actionstream_render
 */
function activity_stream_shortcode($attr, $content = null) {
	extract(shortcode_atts(array(
		'user' => '0',
		'num' => 10,
		'hide_user' => false,
	), $attr));

	// allow user_id to be passed as content
	if ( $content && !$user ) $user = $content;
	$user = activity_stream_get_user_id($user);

	return actionstream_render($user, $num, $hide_user, false);
}
add_shortcode('activity-stream', 'activity_stream_shortcode');


/**
 * Process [activity-services] shortcode.  This shortcode supports two
 * parameters: user and urls_only.  The user parameter may alternately be 
 * passed in the shortcode content.
 *
 * @see actionstream_services
 */
function activity_services_shortcode($attr, $content = null) {
	extract(shortcode_atts(array(
		'user' => '0',
		'urls_only' => false,
	), $attr));

	// allow user_id to be passed as content
	if ( $content && !$user ) $user = $content;
	$user = activity_stream_get_user_id($user);

	return actionstream_services($user, $urls_only);
}
add_shortcode('activity-services', 'activity_services_shortcode');


/**
 * Render the activity stream feed.
 */
function do_feed_action_stream() {
	global $wpdb;
	require_once(dirname(__FILE__) . '/feed.php');
}
add_action('init', create_function('', 'global $wp_rewrite; add_feed("action_stream", "do_feed_action_stream"); $wp_rewrite->flush_rules();'));


/**
 * Add activity stream fields to DiSo Permissions plugin.
 */
function diso_actionstream_permissions($permissions) {

	$user = wp_get_current_user();
	$config = get_actionstream_config();
	$actionstream = get_usermeta($user->ID, 'actionstream');
	$fields = array();

	foreach ($actionstream as $service => $id) {
		$setup = $config['profile_services'][$service];
		$name = $setup['name'] ? $setup['name'] : ucwords($service);
		$fields[$service] = $name;
	}

	$permissions['actionstream'] = array(
		'name' => 'ActionStream Permissions',
		'order' => 5,
		'fields' => $fields,
	);

	return $permissions;
}
add_filter('diso_permission_fields', 'diso_actionstream_permissions');

/*end wordpress */

add_action( 'wp_head', create_function('', 'wp_enqueue_script("jquery");'), 9);

?>

<?php
/*
// +----------------------------------------------------------------------+
// | Licenses and copyright acknowledgements are located at               |
// | http://www.sonsofskadi.net/wp-content/elalicenses.txt                |
// +----------------------------------------------------------------------+
*/

include_once(ABSPATH . WPINC . '/class-snoopy.php');
 
$af_ela_cache_root = dirname(__FILE__) . '/cache/';

function af_ela_info($show='') {
	switch($show) {
	case 'currentversion' :
		$plugins= get_plugins();
    	$info = $plugins['af-extended-live-archive/af-extended-live-archive.php']['Version'];
    	break;
    case 'localeversion' :
    	$info = '9918';
    	break;
    case 'born_on' :
    	$info = 'June 22, 2006';
    	break;
    case 'homeurl' :
    	$info = 'http://www.sonsofskadi.net/extended-live-archive/';
    	break;
	case 'homename' :
    	$info = 'ELA on Sons Of Skadi';
    	break;
	case 'supporturl' :
    	$info = 'http://www.flickr.com/groups/ela-support/discuss/';
    	break;
	case 'supportname' :
    	$info = 'ELA support group on Flickr';
    	break; 
    case 'remoteversion':
    	$info = 'http://www.sonsofskadi.net/elaversion.txt';
     	break;
     default:
     	$info = '';
     	break;   
     }
    return $info;
}

function af_ela_option_init($reset = false) {
	global $af_ela_cache_root;
	if (!$reset) $settings = get_option('af_ela_options');
	
	if (!($is_initialized=get_option('af_ela_is_initialized'))
			|| empty($settings)
			|| strstr(trim(af_ela_info('currentversion')), trim($is_initialized)) === false
			|| strstr(trim($settings['installed_version']), trim($is_initialized)) === false
			|| $reset) {
		$cache = new af_ela_classCacheFile('');
		$cache->deleteFile();
		$initSettings = array(
	// These options is not accessible through the admin panel
		'id' => 'af-ela',
		'installed_version' => af_ela_info('currentversion'),
	// we always set the character set from the blog settings
		'charset' => get_bloginfo('charset'),
		'newest_first' => '1',
		'num_entries' => 0,
		'num_entries_tagged' => 0,
		'num_comments' => 0,
		'fade' => 0,		
		'hide_pingbacks_and_trackbacks' => 0,	
		'use_default_style' => 1,
		'paged_posts' => 0,

		'selected_text' => '',
		'selected_class' => 'selected',
		'comment_text' => '(%)',
		'number_text' => '(%)',
		'number_text_tagged' => '(%)',
		'closed_comment_text' => '',
		'day_format' => '',
		'error_class' => 'alert',

	// allow truncating of titles
		'truncate_title_length' => '0',
		'truncate_cat_length' => '25',
		'truncate_title_text' => '&#8230;',
		'truncate_title_at_space' => 1,
		'abbreviated_month' => 0,
		'tag_soup_cut' => 0,
		'tag_soup_X' => 0,
		
	// paged posts related stuff
		'paged_post_num' => 10,
		'paged_post_next' => 'next posts >>',
		'paged_post_prev' => '<< previous posts',
		
		
	// default text for the tab buttons
		'menu_order' => 'chrono,cats',
		'menu_month' => 'By date',
		'menu_cat' => 'By category',
		'menu_tag' => 'By tags',
		'before_child' => '&nbsp;&nbsp;&nbsp;',
		'after_child' => '',
		'loading_content' => '...loading',
		'idle_content' => '',
		'excluded_categories' => '0');
		
		if (!empty($settings)) {
			$newSettings = array_merge($initSettings, $settings);
		} else {
			$newSettings =$initSettings;
		}
		$newSettings['last_modified'] = gmdate("D, d M Y H:i:s",time());
		$newSettings['installed_version'] = af_ela_info('currentversion');
		
		update_option('af_ela_options', $newSettings, 'Set of Options for Extended Live Archive');
		update_option('af_ela_option_mode', (get_option('af_ela_options') ? 1:0), 'ELA option mode');
		
		$res = true;
		if( !is_dir($af_ela_cache_root) ) {
			if( !($res = af_ela_create_cache_dir()) ) {
				?>
		<div class="updated"><p><strong>Unable to create cache directory. Check your server credentials on the wp-content directory.</strong></p></div>
	<?php		return;
			} else {
				if( $res === true ) {
					$res = af_ela_create_cache($settings);
					if( $res === true ) {?>
		<div class="updated"><p><strong>The cache files have been created for the first time. You should be up and running. Enjoy.</strong></p></div>
	<?php		 	} else {?>
		<div class="updated"><p><strong>Unable to create the cache files. Check your server credentials on the wp-content/af-extended-live-archive directory. </strong></p></div>
	<?php 			return;
					}
				}
			}
		} else {
			if( af_ela_create_cache($settings) ) {
				if (!$reset) {?>
		<div class="updated"><p><strong>The cache files have been updated. You should be up and running. Enjoy.</strong></p></div>
	<?php		}
			} else {?>
		<div class="updated"><p><strong>Unable to update the cache files to the newer version of the plugin. Check your server credentials on the wp-content/af-extended-live-archive directory. </strong></p></div>
	<?php 	return;
			}
		}
		update_option('af_ela_is_initialized', af_ela_info('currentversion'), 'ELA plugin has already been initialized');
	}
}

function af_ela_option_update() {
	global $wpdb;
	$settings = get_option('af_ela_options');
	$settings['last_modified'] = gmdate("D, d M Y H:i:s",time());
	
	$settings['newest_first'] = isset($_POST['newest_first']) ? 1 : 0;
	$settings['num_entries']  = isset($_POST['num_entries']) ? 1 : 0;
	$settings['num_entries_tagged'] = isset($_POST['num_entries_tagged']) ? 1 : 0;
	$settings['num_comments'] = isset($_POST['num_comments']) ? 1 : 0;
	$settings['fade']         = isset($_POST['fade']) ? 1 : 0;
	$settings['hide_pingbacks_and_trackbacks'] = isset($_POST['hide_pingbacks_and_trackbacks']) ? 1 : 0 ;
	$settings['use_default_style'] = isset($_POST['use_default_style']) ? 1 : 0 ;
	$settings['paged_posts'] = isset($_POST['paged_posts']) ? 1 : 0 ;


	if( isset($_POST['selected_text']) )  $settings['selected_text']  = urldecode($_POST['selected_text']);
	if( isset($_POST['selected_class']) ) $settings['selected_class'] = $_POST['selected_class'];
	if( isset($_POST['comment_text']) )   $settings['comment_text']   = urldecode($_POST['comment_text']);
	if( isset($_POST['number_text']) )    $settings['number_text']    = urldecode($_POST['number_text']);
	if( isset($_POST['number_text_tagged']) )  $settings['number_text_tagged']  = urldecode($_POST['number_text_tagged']);
	if( isset($_POST['closed_comment_text']) ) $settings['closed_comment_text'] = urldecode($_POST['closed_comment_text']);
	if( isset($_POST['day_format']) )     $settings['day_format']     = $_POST['day_format'];
	if( isset($_POST['error_class']) )    $settings['error_class']    = $_POST['error_class'];

	// allow truncating of titles
	if( isset($_POST['truncate_title_length']) ) $settings['truncate_title_length'] = urldecode($_POST['truncate_title_length']);
	if( isset($_POST['truncate_cat_length']) )   $settings['truncate_cat_length']   = urldecode($_POST['truncate_cat_length']);
	if( isset($_POST['truncate_title_text']) )   $settings['truncate_title_text']   = urldecode($_POST['truncate_title_text']);
	$settings['truncate_title_at_space'] 		 = isset($_POST['truncate_title_at_space']) ? 1 : 0;
	$settings['abbreviated_month'] 				 = isset($_POST['abbreviated_month']) ? 1 : 0;
	if( isset($_POST['tag_soup_cut']) )			 $settings['tag_soup_cut']   		= urldecode($_POST['tag_soup_cut']);
	if( isset($_POST['tag_soup_X']) )			 $settings['tag_soup_X']   			= urldecode($_POST['tag_soup_X']);
		
	// paged posts related stuff
	if( isset($_POST['paged_post_num']) )	$settings['paged_post_num']   = urldecode($_POST['paged_post_num']);
	if( isset($_POST['paged_post_next']) )	$settings['paged_post_next']   = urldecode($_POST['paged_post_next']);
	if( isset($_POST['paged_post_prev']) )	$settings['paged_post_prev']   = urldecode($_POST['paged_post_prev']);
	
	// default text for the tab buttons
	if( isset($_POST['menu_order']) ) {
		$comma ='';
		$settings['menu_order']='';
		foreach($_POST['menu_order'] as $menu_item) {
			$settings['menu_order'].= $comma . $menu_item;
			$comma = ',';
		}
	}
	if( isset($_POST['menu_month']) )       $settings['menu_month']       = urldecode($_POST['menu_month']);
	if( isset($_POST['menu_cat']) )         $settings['menu_cat']         = urldecode($_POST['menu_cat']);	
	if( isset($_POST['menu_tag']) )         $settings['menu_tag']         = urldecode($_POST['menu_tag']);	
	if( isset($_POST['before_child']) )     $settings['before_child']     = urldecode($_POST['before_child']);
	if( isset($_POST['after_child']) )      $settings['after_child']      = $_POST['after_child'];
	if( isset($_POST['loading_content']) )  $settings['loading_content']  = urldecode($_POST['loading_content']);	
	if( isset($_POST['idle_content']) )     $settings['idle_content']     = urldecode($_POST['idle_content']);
		
	$current_mode = get_option('af_ela_option_mode');
	$asides_cats = $wpdb->get_results("SELECT * from $wpdb->categories");
	$comma ='';
	if (!isset($_POST['excluded_categories'])) {?>
	<div class="updated"><p><strong>What's the point of not showing up any categories at all ?</strong></p></div> <?php
	} else {
		if ($current_mode == 0) {
			$settings['excluded_categories'] = $_POST['excluded_categories'][0];
		} else {
			$settings['excluded_categories'] = '';
			foreach ($asides_cats as $cat) {
				if(!in_array($cat->cat_ID, $_POST['excluded_categories'])) {
					$settings['excluded_categories'] .= $comma ;
					$settings['excluded_categories'] .= $cat->cat_ID;
					$comma = ',';
				}
			}
		}
	}
	
	$settings['last_modified'] = gmdate("D, d M Y H:i:s",time());
	
	update_option('af_ela_options', $settings, 'Set of Options for Extended Live Archive',1);
	
	$cache = new af_ela_classCacheFile('');
	$cache->deleteFile();
	
}

function af_ela_remote_version_check() {
	if (class_exists(snoopy)) {
		$client = new Snoopy();
		$client->_fp_timeout = 4;
		if (@$client->fetch(af_ela_info('remoteversion')) === false) {
			return -1;
		}
	   	$remote = $client->results;
   		if (!$remote || strlen($remote) > 8 ) {
			return -1;
		} 
		if (intval($remote) > intval(af_ela_info('localeversion'))) {
			return 1;
		} else {
			return 0;
		}
	}
}

function af_ela_admin_page() {
	af_ela_option_init();
	if (($remote = af_ela_remote_version_check()) == 1) {
		echo '<div id="message" class="updated fade"><p><a href="'. af_ela_info('homeurl').'" title="'.af_ela_info('homename').'">There is a ELA update available</a></p></div>';
	}
	
	if (isset($_POST['submit_option'])) { 
		if (isset($_POST['clear_cache'])) {
			$cache = new af_ela_classCacheFile('');
			$reset_return= $cache->deleteFile();
			if ($reset_return) {
				?>
	<div class="updated"><p><strong>Cache emptied</strong></p></div> <?php
			} else {
				?>	<div class="updated"><p><strong>Cache was already empty</strong></p></div> <?php
			}
		} elseif (isset($_POST['switch_option_mode'])) {
		 	$current_mode = get_option('af_ela_option_mode');
			if ($current_mode == 0) {
				$next_mode = 1;
				$option_mode_text = 'Switch to Advanced Options Mode';
			} else {
				$next_mode = 0;
				$option_mode_text = 'Switch to Basic Options Mode';
			}			
			update_option('af_ela_option_mode', $next_mode,'',1);
		} elseif (isset($_POST['reset_option'])) {
			af_ela_option_init(true);
		} else {		
			af_ela_option_update();
			?>	<div class="updated"><p>Extended Live Archive Options have been updated</p></div> <?php 
		}
	}
	$current_mode = get_option('af_ela_option_mode');
	if ($current_mode == 0) {
		$option_mode_text = 'Show Advanced Options Panel';
		$advancedState = 'none';
		$basicState = 'table-row';
	} else {
		$option_mode_text = 'Hide Advanced Options Panel';
		$advancedState = 'block';
		$basicState = 'none';
	}
	$settings = get_option('af_ela_options');

	af_ela_echo_scripts();

?>	<div class="wrap">
		<h2>ELA Options</h2><?php
	af_ela_echo_fieldset_info($option_mode_text,$advancedState);
?>		<form method="post">
		<input type="hidden" name="submit_option" value="1" /><?php
	af_ela_echo_fieldset_whattoshow($settings,$basicState, $current_mode);
?>		<hr style="clear: both; border: none;" /><?php
	af_ela_echo_fieldset_howtoshow($settings,$advancedState);
	af_ela_echo_fieldset_howtocut($settings,$advancedState);
?>		<hr style="clear: both; border: none;" /><?php
	af_ela_echo_fieldset_whataboutthemenus($settings,$advancedState);
	af_ela_echo_fieldset_whatcategoriestoshow($settings,$advancedState);
	af_ela_echo_fieldset_whataboutthepagedposts($settings,$advancedState);
?>		<hr style="clear: both; border: none;" />
		<div class="submit">
			<input type="submit" name="update_generic" value="<?php _e('Update Options Now') ?>" />
		</div>
		</form>
	</div>
	<div class="wrap">
		<h2>ELA Cache Management</h2>
		<form method="post">
		<input type="hidden" name="submit_option" value="1" />
		<p>You need to clear the cache so that it gets re-built whenever you are making changes related to a category without editing or creating a post (like renaming, creating, deleting a category for instance</p>
		<div class="submit">
			<input type="submit" name="clear_cache" value="<?php _e('Empty Cache Now') ?>" />
		</div>
		</form>
	</div>
<?php
}

function af_ela_echo_scripts() {
	global $utw_is_present;
?>	<script language="javascript" type="text/javascript">
//<![CDATA[
	function disableTabs(first, disabler) {
		var maxtab = 3;
		var i;
		if (document.getElementById('menu_order_tab' + disabler).value == 'none') {
			for(i = first; i < maxtab; i++) {
				document.getElementById('menu_order_tab' + i).value = 'none';
				document.getElementById('menu_order_tab' + i).disabled = true;
			}
		} else {
			document.getElementById('menu_order_tab' + first).disabled = false;
		}
	}
	function disableDOM(ID, disabler) {
		var i;
		if (document.getElementById(disabler).checked == true) {
			document.getElementById(ID).disabled = false;
		} else {
			document.getElementById(ID).disabled = true;
		}
	}
	function disableDOMinv(ID, disabler) {
		if (document.getElementById(disabler).checked == true) {
			document.getElementById(ID).disabled= true;
		} else {
			document.getElementById(ID).disabled = false;
		}
	}
	function hideDOM(ID, disabler) {
		if (document.getElementById(disabler).checked == true) {
			document.getElementById(ID).style.display = "block";
		} else {
			document.getElementById(ID).style.display = "none";
		}
	}
	function selectAllCategories(list) {
		var i;
		var temp = new Array();
		temp = list.split(',');
		for(i = 0; i < temp.length-1; i++) {
			document.getElementById("category-"+temp[i]).checked=true;
		}
	}
	function unselectAllCategories(list) {
		var i;
		var temp = new Array();
		temp = list.split(',');
		for(i = 0; i < temp.length-1; i++) {
			document.getElementById("category-"+temp[i]).checked=false;
		}
	}
	
	function initUnavailableOptions(){
		disableDOM('number_text', 'num_entries');
		<?php if($utw_is_present) { ?>disableDOM('number_text_tagged', 'num_entries_tagged');<?php }?>;
		disableDOM('comment_text', 'num_comments');
		disableDOM('closed_comment_text', 'num_comments');
		disableDOM('hide_pingbacks_and_trackbacks', 'num_comments');
		hideDOM('fieldsetpagedposts', 'paged_posts');
		<?php if($utw_is_present) { ?>disableDOMinv('tag_soup_X', 'tag_soup_cut0');<?php }?>;
		disableTabs(1, 0);
	}
	
	addLoadEvent(initUnavailableOptions);
//]]>
	</script>
<?php
}

function af_ela_echo_fieldset_info($option_mode_text,$advancedState) {
?>
		<fieldset class="options" style="float: left; width: 25%;"><legend>Extended Live Archive info </legend>
		<table width="100%" cellspacing="2" cellpadding="5" class="editform">
			<tr>
				<th width="33%" valign="top" scope="row"><label>Version:</label></th>
				<td><?php echo af_ela_info('currentversion'); ?></td>
			</tr>
			<tr>
				<th width="33%" valign="top" scope="row"><label>Latest news:</label></th>
				<td><a href="<?php echo af_ela_info('homeurl'); ?>"><?php echo af_ela_info('homename'); ?></a></td>
			</tr>
			<tr>
				<th width="33%" valign="top" scope="row"><label>Get help:</label></th>
				<td><a href="<?php echo af_ela_info('supporturl'); ?>"><?php echo af_ela_info('supportname'); ?></a></td>
			</tr>
			<tr>
				<th width="33%" valign="top" scope="row"><label>Works great with:</label></th>
				<td><a href="http://binarybonsai.com/k2/">Michael's K2</a> and a sh*load of other themes...
				<br/><a href="http://photomatt.net/2004/05/19/asides/">Matt's Asides</a>
				<br/><a href="http://www.neato.co.nz/ultimate-tag-warrior">Christine's Utimate Tag Warrior</a></td>
			</tr>
			<tr>
				<th width="33%" valign="top" scope="row"><label>Feel absolutly free to </label></th>
				<td><form  action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick"/>
					<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
					<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIH8QYJKoZIhvcNAQcEoIIH4jCCB94CAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBmTiBrGDGDUePWZj6mcZVyQ88Ok0E3faq5jQ1N39pvq9Hy3pAddw0S5p8ujZIA6qLjFTT0zAUc2wfSUXVtAOkf6jGUW8lsyVgUHqhInRgjSm6N3vtzE+Gj6amTi15KIX09xezrlHaWbx4QqZOH1A/OKHb8aFDJsA/y0WyblBQ+rzELMAkGBSsOAwIaBQAwggFtBgkqhkiG9w0BBwEwFAYIKoZIhvcNAwcECPv40xnzNoGKgIIBSDm7JRzjoiWIUbkU2JrIfdMLXz3669c2U8TFcs5lUJB3OloRD/ZVTAMmfvxZswpG35LrHs4JqhuWB6j4ylZ1yLf8H/TVIhE3XLwCKAxNc6S4vrTLLDaALTyuZZCKQG+fJw+CMgg4eDEm66uu2JCgQld3ffY1IUAfeP+m75qemesUQuZs5R+60WYNVVtGwgn1cmdaAj95Fy9L58M5biNmvAmQXj5kRfv501q5bkCsq3tJ7lfqBDTrVnx/Ph2Xp6ZG2G2vRhkRZmBX/WbJIkYf/qNzGn8d+T3r+09DjuP1pEyqnnD67uv4g+iZrjRP2L/Z1FWV+dfzJQ4BeDm5PJ0xve45Bz6BWgI7iP6CzPpXGCemvm9JZ8iaXhrkINrMlgQOBNUvHTDelyR/QKdhkRRPDkgPe5cYmkXnEdAPetdJl6CjsxVoqht9NLegggOHMIIDgzCCAuygAwIBAgIBADANBgkqhkiG9w0BAQUFADCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wHhcNMDQwMjEzMTAxMzE1WhcNMzUwMjEzMTAxMzE1WjCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAMFHTt38RMxLXJyO2SmS+Ndl72T7oKJ4u4uw+6awntALWh03PewmIJuzbALScsTS4sZoS1fKciBGoh11gIfHzylvkdNe/hJl66/RGqrj5rFb08sAABNTzDTiqqNpJeBsYs/c2aiGozptX2RlnBktH+SUNpAajW724Nv2Wvhif6sFAgMBAAGjge4wgeswHQYDVR0OBBYEFJaffLvGbxe9WT9S1wob7BDWZJRrMIG7BgNVHSMEgbMwgbCAFJaffLvGbxe9WT9S1wob7BDWZJRroYGUpIGRMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAIFfOlaagFrl71+jq6OKidbWFSE+Q4FqROvdgIONth+8kSK//Y/4ihuE4Ymvzn5ceE3S/iBSQQMjyvb+s2TWbQYDwcp129OPIbD9epdr4tJOUNiSojw7BHwYRiPh58S1xGlFgHFXwrEBb3dgNbMUa+u4qectsMAXpVHnD9wIyfmHMYIBmjCCAZYCAQEwgZQwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tAgEAMAkGBSsOAwIaBQCgXTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0wNTEyMzExNzU1NDVaMCMGCSqGSIb3DQEJBDEWBBR5zGOVdRzLIwgs54bPWS2CsEEnJjANBgkqhkiG9w0BAQEFAASBgCHhs0ak4BB1Aws+/ulLURrSX4PakrT3ygcok8Dz81Jiugal07+Okj+xEsP4GvY7YSHet6EPsR9Isr+R7duCou/Bs6azSdLBPKI2dZAXU6NAF2fWK4XqX6TBBvQu773EQRAYbB86wtBMzrszep1UNt3A2Q2N0+Vkz1BtykpTMfU6-----END PKCS7-----"/>
					</form>
				</td>
			</tr>
		</table> 
		<div class="submit" style="text-align:center; ">
		<form method="post"><br />
		<input type="hidden" name="submit_option" value="1" /><input type="submit" name="switch_option_mode" value="<?php _e($option_mode_text) ?>" /></form></div>
		
		<div class="submit" style="text-align:center;display: <?php echo $advancedState; ?> ">
		<form method="post"><br />
		<input type="hidden" name="submit_option" value="1" /><input type="submit" name="reset_option" value="<?php echo "Reset options to default" ?>" /></form></div>
		<tr valign="top" >

		</tr>
			
			
		</fieldset><?php
}

function af_ela_echo_fieldset_whattoshow($settings,$basicState, $current_mode) {
	global $utw_is_present;
?>
		<fieldset class="options"><legend>What to show ? </legend>
		<table width="100%" cellspacing="2" cellpadding="5" class="editform">
			<tr>
				<th width="30%" valign="top" scope="row"><label for="newest_first">Show Newest First:</label></th>
				<td width="5%">
					<input name="newest_first" id="newest_first" type="checkbox" value="<?php echo $settings['newest_first']; ?>" <?php checked('1', $settings['newest_first']); ?> />
				</td>
				<td><small>Enabling this will show the newest post first in the listings.</small></td>
			</tr>
			<tr>
				<th width="30%" valign="top" scope="row"><label for="num_entries" >Show Number of Entries:</label></th>
				<td width="5%">
					<input onchange="Javascript:disableDOM('number_text', 'num_entries');" name="num_entries" id="num_entries" type="checkbox" value="<?php echo $settings['num_entries']; ?>" <?php checked('1', $settings['num_entries']); ?> />
				</td>
				<td><small>Sets whether the number of entries for each year, month, category should be shown.</small></td>
			</tr><?php if($utw_is_present) { ?>
			<tr>
				<th width="30%" valign="top" scope="row"><label for="num_entries_tagged">Show Number of Entries Per Tag:</label></th>
				<td width="5%">
					<input onchange="Javascript:disableDOM('number_text_tagged', 'num_entries_tagged');" name="num_entries_tagged" id="num_entries_tagged" type="checkbox" value="<?php echo $settings['num_entries_tagged']; ?>" <?php checked('1', $settings['num_entries_tagged']); ?> /></td>
				<td><small>Sets whether the number of entries for each tags should be shown</small></td>
			</tr><?php } ?>
			<tr>
				<th width="30%" valign="top" scope="row"><label for="num_comments">Show Number of Comments:</label></th>
				<td width="5%">
					<input onchange="Javascript:disableDOM('comment_text', 'num_comments');disableDOM('closed_comment_text', 'num_comments');disableDOM('hide_pingbacks_and_trackbacks', 'num_comments');" name="num_comments" id="num_comments" type="checkbox" value="<?php echo $settings['num_comments']; ?>" <?php checked('1', $settings['num_comments']); ?> /></td><td><small>Sets whether the number of comments for each entry should be shown</small>
				</td>
			</tr>
			<tr>
				<th width="30%" valign="top" scope="row"><label for="fade">Fade Anything Technique:</label></th>
				<td width="5%">
					<input name="fade" id="fade" type="checkbox" value="<?php echo $settings['fade']; ?>" <?php checked('1', $settings['fade']); ?> />
				</td>
				<td><small>Sets whether changes should fade using the Fade Anything Technique</small></td>
			</tr>
			<tr>
				<th width="30%" valign="top" scope="row"><label for="hide_pingbacks_and_trackbacks">Hide Ping- and Trackbacks:</label></th>
				<td width="5%">
					<input name="hide_pingbacks_and_trackbacks" id="hide_pingbacks_and_trackbacks" type="checkbox" value="<?php echo $settings['hide_pingbacks_and_trackbacks']; ?>" <?php checked('1', $settings['hide_pingbacks_and_trackbacks']); ?> />
				</td>
				<td><small>Sets whether ping- and trackbacks should influence the number of comments on an entry</small></td>
			</tr>
			<tr>
			<th width="30%" valign="top" scope="row"><label for="use_default_style">Use the default CSS stylesheet:</label></th>
				<td width="5%"><input name="use_default_style" id="use_default_style" type="checkbox" value="<?php echo $settings['use_default_style']; ?>" <?php checked('1', $settings['use_default_style']); ?> /></td><td><small>If it exists, will link the <strong>ela.css</strong> stylesheet of your theme. If not present, will link the default stylesheet.</small></td>
			</tr>
			<tr>
			<th width="30%" valign="top" scope="row"><label for="paged_posts">Layout the posts link into pages:</label></th>
				<td width="5%"><input  onchange="hideDOM('fieldsetpagedposts', 'paged_posts');" name="paged_posts" id="paged_posts" type="checkbox" value="<?php echo $settings['paged_posts']; ?>" <?php checked('1', $settings['paged_posts']); ?> /></td><td><small>Sets whether the posts list will be cut into several pages or just the complete list.</small></td>
			</tr>
			<tr valign="top" style="display: <?php echo $basicState; ?>">
				<th scope="row"><label for="cat_asides">Asides Category:</label></th>
				<td colspan="2"><?php
				global $wpdb;
				$asides_table = array();
				$asides_table = explode(',', $settings['excluded_categories']);
				if ($asides_table[0] != 0) {
					$id = $asides_table[0];
					$asides_title = $wpdb->get_var("SELECT cat_name from $wpdb->categories WHERE cat_ID = ${asides_table[0]}");
				} else {
					$asides_title='No Asides';
				}
				$asides_cats = $wpdb->get_results("SELECT * from $wpdb->categories");
				 if ($current_mode == 0) {
?>				<select name="excluded_categories[]" id="cat_asides" style="width: 10em;" >
				<option value="<?php echo $asides_table[0]; ?>"><?php echo $asides_title; ?></option>
				<option value="-----">----</option>
				<option value="0">No Asides</option>
				<option value="-----">----</option><?php
				foreach ($asides_cats as $cat) {
					echo '<option value="' . $cat->cat_ID . '">' . $cat->cat_name . '</option>';
            	}?>
				</select><small>&nbsp;&nbsp;&nbsp;The category you are using for your asides.</small></td><?php } ?>
			</tr>		
		</table>
		</fieldset><?php
}

function af_ela_echo_fieldset_howtoshow($settings,$advancedState) {
	global $utw_is_present;
?>		<fieldset class="options" style="display: <?php echo $advancedState; ?>; float: left; width: 52%;" ><legend>How to show it ? </legend>
		<table width="100%" cellspacing="2" cellpadding="5" class="editform" >
			<tr valign="top">
				<th width="180" scope="row"><label for="selected_text">Selected Text:</label></th>
				<td><input name="selected_text" id="selected_text" type="text" value="<?php echo $settings['selected_text']; ?>" size="30" /><br/>
				<small>The text that is shown after the currently selected year, month or category.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="selected_class">Selected Class:</label></th>
				<td ><input name="selected_class" id="selected_class" type="text" value="<?php echo $settings['selected_class']; ?>" size="30" /><br/>
				<small>The CSS class for the currently selected year, month or category.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="number_text"># of Entries Text:</label></th>
				<td><input name="number_text" id="number_text" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['number_text'])); ?>" size="30" /><br/>
				<small>The string to show for number of entries per year, month or category. Can contain HTML. % is replaced with number of entries.</small></td>
			</tr><?php if($utw_is_present) { ?>
			<tr valign="top">
				<th scope="row"><label for="number_text_tagged"># of Tagged-Entries Text:</label></th>
				<td><input name="number_text_tagged" id="number_text_tagged" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['number_text_tagged'])); ?>" size="30" /><br/>
				<small>The string to show for number of entries per tag. Can contain HTML. % is replaced with number of entries.</small></td>
			</tr><?php } ?>
			<tr valign="top">
				<th scope="row"><label for="comment_text"># of Comments Text:</label></th>
				<td><input name="comment_text" id="comment_text" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['comment_text'])); ?>" size="30" /><br/>
				<small>The string to show for comments. Can contain HTML. % is replaced with number of comments.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="closed_comment_text ">Closed Comment Text:</label></th>
				<td><input name="closed_comment_text" id="closed_comment_text" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['closed_comment_text'])); ?>" size="30" /><br/>
				<small>The string to show if comments are closed on an entry. Can contain HTML.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="day_format">Day of Posting Format:</label></th>
				<td><input name="day_format" type="text" id="day_format" value="<?php echo $settings['day_format']; ?>" size="30" /><br/>
				<small>A date format string to show the day for each entry in the chronological tab only ('jS' to show 1st, 3rd, and 14th). Format string is in the <a href="http://www.php.net/date">php date format</a>. Reference to year and month in there will result in error : this intended for days only. Leave empty to show no date.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="error_class">Error Class:</label></th>
				<td><input name="error_class" type="text" id="error_class" value="<?php echo $settings['error_class']; ?>" size="30" /><br/>
				<small>The CSS class to put on paragraphs containing errors.</small></td>
			</tr>
		</table>
		</fieldset><?php
}

function af_ela_echo_fieldset_howtocut($settings,$advancedState) {
	global $utw_is_present;
?>
		<fieldset class="options" style="display: <?php echo $advancedState; ?>;float: right; width: 40%;" ><legend>What to cut out ? </legend>
		<table width="100%" cellspacing="2" cellpadding="5" class="editform">
			<tr valign="top">
				<th width="180" scope="row"><label for="truncate_title_length">Max Entry Title Length:</label></th>
				<td><input name="truncate_title_length" id="truncate_title_length" type="text" value="<?php echo $settings['truncate_title_length']; ?>" size="8" /><br/>
				<small>Length at which to truncate title of entries. Set to <strong>0</strong> to leave the titles not truncated.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="truncate_cat_length" >Max Cat. Title Length:</label></th>
				<td><input name="truncate_cat_length" id="truncate_cat_length" type="text" value="<?php echo $settings['truncate_cat_length']; ?>" size="8"  /><br/>
				<small>Length at which to truncate name of categories. Set to <strong>0</strong> to leave the category names not truncated</small></td>
			</tr> 
			<tr valign="top"> 
				<th scope="row"><label for="truncate_title_text">Truncated Text:</label></th>
				<td><input name="truncate_title_text" id="truncate_title_text" type="text" value="<?php echo $settings['truncate_title_text']; ?>" size="8" /><br/>
				<small>The text that will be written after the entries titles and the categories names that have been truncated. &#8230; (<strong>&amp;#8230;</strong>) is a common example.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="truncate_title_at_space">Truncate at space:</label></th>
				<td><input name="truncate_title_at_space" id="truncate_title_at_space" type="checkbox" value="<?php echo $settings['truncate_title_at_space']; ?>" <?php checked('1', $settings['truncate_title_at_space']); ?> /><br/>
				<small>Sets whether at title should be truncated at the last space before the length to be truncated to, or if words should be truncated mid-senten...</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="abbreviated_month">Abbreviate month names:</label></th>
				<td><input name="abbreviated_month" id="abbreviated_month" type="checkbox" value="<?php echo $settings['abbreviated_month']; ?>" <?php checked('1', $settings['abbreviated_month']); ?> /><br/>
				<small>Sets whether the month names will be abbreviated to three letters.</small></td>
			</tr><?php if ($utw_is_present) { ?>			
			<tr valign="top">
				<th scope="row"><label for="tag_soup_cut">Displayed tags:</label></th>
				<td><input name="tag_soup_cut" id="tag_soup_cut0" type="radio" value="0" onchange="Javascript:disableDOMinv('tag_soup_X', 'tag_soup_cut0');" <?php checked('0', $settings['tag_soup_cut']); ?> /><small>Show all tags.</small>
				<br /><input name="tag_soup_cut" id="tag_soup_cut1" type="radio" value="1" onchange="Javascript:disableDOMinv('tag_soup_X', 'tag_soup_cut0');" <?php checked('1', $settings['tag_soup_cut']); ?> /><small>Show the first <strong>X</strong> most-used tags.</small>
				<br /><input name="tag_soup_cut" id="tag_soup_cut2" type="radio" value="2" onchange="Javascript:disableDOMinv('tag_soup_X', 'tag_soup_cut0');" <?php checked('2', $settings['tag_soup_cut']); ?> /><small>Show tags with more than <strong>X</strong> posts.</small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="tag_soup_X">The X in the selected above description:</label></th>
				<td><input name="tag_soup_X" id="tag_soup_X" type="text" value="<?php echo $settings['tag_soup_X']; ?>" /><br/>
				<small>Sets depending on the selection made above the number of post per tag needed to display the tag or the number of most-used tags to display.</small></td>
			</tr><?php }?>
		</table>
		</fieldset><?php
}

function af_ela_echo_fieldset_whataboutthemenus($settings,$advancedState) {
	if (!empty($settings['menu_order'])) {
		$menu_table = preg_split('/[\s,]+/',$settings['menu_order']);
	}
	global $utw_is_present;
?>		<fieldset class="options" style="display: <?php echo $advancedState; ?>; float: left; width: 52%" ><legend>What about the menus ? </legend>
		<table width="100%" cellspacing="2" cellpadding="5" class="editform">
			<tr valign="top">
				<th width="180" scope="row"><label for="menu_order[]">Tab Order:</label></th>
				<td>
				<select name="menu_order[]" id="menu_order_tab0" onchange="Javascript:disableTabs(1,0);" style="width: 10em;" >
				<option value="none" <?php echo ($menu_table[0] == 'none') ? 'selected' : '' ?>>None</option>
				<option value="chrono" <?php echo ($menu_table[0] == 'chrono') ? 'selected' : '' ?>>By date</option>
				<option value="cats" <?php echo ($menu_table[0] == 'cats') ? 'selected' : '' ?>>By category</option><?php if($utw_is_present) { ?>
				<option value="tags" <?php echo ($menu_table[0] == 'tags') ? 'selected' : '' ?>>By tag</option><?php } ?></select>
				
				<select name="menu_order[]" id="menu_order_tab1" onchange="Javascript:disableTabs(2,1);" style="width: 10em;" >
				<option id="none1" value="none" <?php echo ($menu_table[1] == 'none') ? 'selected' : '' ?>>None</option>
				<option id="chrono1" value="chrono" <?php echo ($menu_table[1] == 'chrono') ? 'selected' : '' ?>>By date</option>
				<option id="cats1" value="cats" <?php echo ($menu_table[1] == 'cats') ? 'selected' : '' ?>>By category</option><?php if($utw_is_present) { ?>
				<option id="tags1" value="tags" <?php echo ($menu_table[1] == 'tags') ? 'selected' : '' ?>>By tag</option><?php } ?></select>
<?php if($utw_is_present) { ?>
				<select name="menu_order[]" id="menu_order_tab2" style="width: 10em;" >
				<option id="none2" value="none" <?php echo ($menu_table[2] == 'none') ? 'selected' : '' ?>>None</option>
				<option id="chrono2" value="chrono" <?php echo ($menu_table[2] == 'chrono') ? 'selected' : '' ?>>By date</option>
				<option id="cats2" value="cats" <?php echo ($menu_table[2] == 'cats') ? 'selected' : '' ?>>By category</option>
				<option id="tags2" value="tags" <?php echo ($menu_table[2] == 'tags') ? 'selected' : '' ?>>By tag</option>
				</select><?php } ?>
				<br/><small>The order of the tab to display.</small></td>
			</tr>
			<tr valign="top">
				<th width="180" scope="row"><label for="menu_month">Chronological Tab Text:</label></th>
				<td><input name="menu_month" id="menu_month" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['menu_month'])); ?>" size="30" /><br/>
				<small>The text written in the chronological tab.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="menu_cat">By Category Tab Text:</label></th>
				<td><input name="menu_cat" id="menu_cat" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['menu_cat'])); ?>" size="30" /><br/>
				<small>The text written in the categories tab.</small></td>
			</tr><?php if($utw_is_present) { ?>
			<tr valign="top">
				<th scope="row"><label for="menu_tag">By Tag Tab Text:</label></th>
				<td><input name="menu_tag" id="menu_tag" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['menu_tag'])); ?>" size="30" /><br/>
				<small>The text written in the tags tab.</small></td>
			</tr><?php } ?>
	
			<tr valign="top">
				<th scope="row"><label for="before_child">Before Child Text:</label></th>
				<td><input name="before_child" id="before_child" type="text" value="<?php echo htmlspecialchars($settings['before_child']); ?>" size="30" /><br/>
				<small>The text written before each category which is a child of another. This is recursive.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="after_child">After Child Text:</label></th>
				<td><input name="after_child" id="after_child" type="text" value="<?php echo $settings['after_child']; ?>" size="30" /><br/>
				<small>The text that after each category which is a child of another. This is recursive.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="loading_content">Loading Content:</label></th>
				<td><input name="loading_content" id="loading_content" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['loading_content'])); ?>" size="30" /><br/>
				<small>The text displayed when the data are being fetched from the server (basically when stuff is loading). Can contain HTML.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="idle_content">Idle Content:</label></th>
				<td><input name="idle_content" id="idle_content" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['idle_content'])); ?>" size="30" /><br/>
				<small>The text displayed when no data are being fetched from the server (basically when stuff is not loading). Can contain HTML.</small></td>
			</tr>
		</table>
		</fieldset><?php
}

function af_ela_echo_fieldset_whatcategoriestoshow($settings,$advancedState) {
?>		<fieldset class="options" style="display: <?php echo $advancedState; ?>; float: right; width: 40%" ><legend>What categories to show ?</legend><label for="cat_asides">The category you want to show in the categories tab.</label>
		<?php
			global $wpdb;
			$asides_table = array();
			$asides_table = explode(',', $settings['excluded_categories']);
			$asides_cats = $wpdb->get_results("SELECT * from $wpdb->categories");
			$asides_content = '<table width="100%" cellspacing="2" cellpadding="5" class="editform">';
			$asides_select = '';
			foreach ($asides_cats as $cat) {
				$checked = in_array($cat->cat_ID, $asides_table) ? '' : 'checked ';
				$asides_select .= $cat->cat_ID.',';
				$asides_content .= '
			<tr valign="top">
				<th scope="row"><label for="category-'.$cat->cat_ID.'">'.$cat->cat_name.'</label></th>
				<td width="5%"><input value="'.$cat->cat_ID.'" type="checkbox" name="excluded_categories[]" id="category-'.$cat->cat_ID.'" '. $checked  . '/></td>
			</tr>';
		   	}
		   	$asides_content .= '</table>';
			echo $asides_content;
?>		<input type="button" onclick="javascript:selectAllCategories('<?php echo $asides_select;?>')" value="<?php _e('Select All Categories') ?>" />
		<input type="button" onclick="javascript:unselectAllCategories('<?php echo $asides_select;?>')" value="<?php _e('Unselect All Categories') ?>" />
		</fieldset><?php
}

function af_ela_echo_fieldset_whataboutthepagedposts($settings,$advancedState) {
?>
		<fieldset id="fieldsetpagedposts" class="options" style="display: <?php echo $advancedState; ?>; float: right; width: 40%" ><legend>What about the paged posts ?</legend><label for="cat_asides">The layout of the posts when using a paged list instead of complete list .</label>
		<table width="100%" cellspacing="2" cellpadding="5" class="editform">
			<tr valign="top">
				<th scope="row"><label for="paged_post_num">Max # of Posts per page:</label></th>
				<td><input name="paged_post_num" id="paged_post_num" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['paged_post_num'])); ?>" size="30" /><br/>
				<small>The max number of posts that will be listed per page.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="paged_post_next">Next Page of Posts:</label></th>
				<td><input name="paged_post_next" id="paged_post_next" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['paged_post_next'])); ?>" size="30" /><br/>
				<small>The text written as the link to the next page.</small></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="paged_post_prev">Previous Page of Posts:</label></th>
				<td><input name="paged_post_prev" id="paged_post_prev" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['paged_post_prev'])); ?>" size="30" /><br/>
				<small>The text written as the link to the previous page.</small></td>
			</tr>
		</table>
		</fieldset>
<?php
}

af_ela_admin_page();
?>
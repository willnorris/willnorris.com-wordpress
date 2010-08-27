<?php
/*
Plugin Name: MicroID
Plugin URI: http://willnorris.com/projects/wp-microid
Description: Adds a MicroID to your WordPress blog
Version: 1.1
Author: Will Norris
Author URI: http://willnorris.com/
*/


/* register with Wordpress */
if (isset($wp_version)) {
	add_action('wp_head', array('MicroID', 'insert_meta_tags'), 5); 
	add_action('admin_menu', array('MicroID', 'menu'));

	if (get_option('microid_include_posts')) {
		add_action('the_content', array('MicroID', 'add_microid_on_post'));
	}

	if (get_option('microid_include_comments')) {
		add_action('comment_text', array('MicroID', 'add_microid_on_comment'));
	}
}


class MicroID {
	
	/**
	 * Insert meta tags into template head.
	 */
	function insert_meta_tags() 
	{
		if (is_home() || (function_exists('is_front_page') && is_front_page()) ) {
			$microid_identities = get_option('microid_identities');

			if (!empty($microid_identities)) {
				foreach($microid_identities as $k => $v) {
					echo '<meta name="microid" content="' . $v . '" />' . "\n";
				}
			}
		}

		if (is_author()) {
			$author  = get_userdata(get_query_var('author'));
			$url = get_author_posts_url($author->user_login);
			$identity = MicroID::canonicalize_identity_uri($author->user_email);
			$microid = MicroID::generate($identity, $url);
			if (!empty($microid)) {
				echo '<meta name="microid" content="' . $microid . '" />' . "\n";
			}
		}
	}

	/**
	 * Wrap post with div that includes a microid for the post
	 */
	function add_microid_on_post($content = '') 
	{
		$identity = MicroID::canonicalize_identity_uri(get_the_author_email());

		$microid = MicroID::generate($identity, get_permalink());
		if (!empty($microid)) {
			return "<div class='microid-$microid'>$content</div>";
		} else {
			return $content;
		}
	}

	/**
	 * Wrap comment with div that includes a microid for the comment
	 */
	function add_microid_on_comment($comment = '') 
	{
		$identity = MicroID::canonicalize_identity_uri(get_comment_author_email());

		$microid = MicroID::generate($identity, get_permalink());
		if (!empty($microid)) {
			return "<div class='microid-$microid'>$comment</div>";
		} else {
			return $comment;
		}
	}

	/**
	 * Register admin menu.
	 */
	function menu() {
		add_options_page('MicroID Options', 'MicroID', 8, __FILE__, array('MicroID', 'manage'));
	}

	/**
	 * Manage admin option page.
	 */
	function manage() {
		$microid_identities = get_option('microid_identities');
		$updated = false;

		if (array_key_exists('action', $_REQUEST)) {
			switch($_REQUEST['action']) {
				case 'submit':
					if (array_key_exists('new_identity', $_REQUEST) && !empty($_REQUEST['new_identity'])) {
						$newID = MicroID::canonicalize_identity_uri($_REQUEST['new_identity']);
						$microid_identities[$newID] = MicroID::generate($newID, get_option('home'));
						$updated = true;
					}

					update_option( 'microid_include_posts', isset($_REQUEST['microid_include_posts']) ? true : false );
					update_option( 'microid_include_comments', isset($_REQUEST['microid_include_comments']) ? true : false );

					break;

				case 'delete':
					if (array_key_exists('id', $_REQUEST) && !empty($_REQUEST['id'])) {
						unset($microid_identities[$_REQUEST['id']]);
						$updated = true;
					}
					break;

				case 'default':
					$default_ids = MicroID::default_identities();
					if (!empty($default_ids)) {

						foreach($default_ids as $id) {
							$newID = MicroID::canonicalize_identity_uri($id);
							$microid_identities[$newID] = MicroID::generate($newID, get_option('home'));
						}

						$updated = true;
					}
					break;
			}
		}

		if ($updated) {
			update_option('microid_identities', $microid_identities);
		}

		// options page
		?>

		<div class="wrap">
			<h2>MicroID</h2>
			<p>MicroID is an open source, decentralized identity protocol. It was originally developed in 2005 by Jeremie Miller. A MicroID is a simple identifier comprised of a hashed communication/identity URI (e.g. Email, OpenID, and/or Yadis) and claimed URL. Together, the two elements create a hash that can be claimed by third party services.</p>
			<p>You can read more about MicroID on <a href="http://en.wikipedia.org/wiki/MicroID">Wikipedia</a> and on the <a href="http://microid.org">MicroID homepage</a> and <a href="http://microid.org/blog/">blog</a>.</p>
			<p>Having a MicroID on your blog will allow you to claim it as your own in claim services sich as <a href="http://claimid.com">claimID</a>.

			<?php if ($updated) {
				echo '<div id="message" class="updated fade"><p>'.__('Changes have been saved', '').'</p></div>';
			} ?>

			<form method="post" action="?page=<?php echo $_REQUEST['page'] ?>">
				<h3><?php _e('MicroID Identities') ?></h3>

				<p>These MicroIDs are generated using the specified identity URI and your blog home URL of <strong><?php echo get_option('home'); ?></strong>.  You may include any number of identifiers such as different email addresses, OpenIDs, or i-names.</p>

				<table>
					<tr>
						<th>Identity</th>
						<th>Generated MicroID</th>
						<th>Delete</th>
					</tr>
			<?php
			$id=0;
			if (isset($microid_identities) && !empty($microid_identities)) {
				foreach($microid_identities as $k => $v) {
					if ($v) {
						echo '
							<tr>
								<td>'.$k.'</td>
								<td>'.$v.'</td>
								<td><a class="delete" href="?page='.$_REQUEST['page'].'&action=delete&id='.$k.'">Delete</a></td>
							</tr>';
						$id++;
					}
				}
			} else {
				echo '
					<tr>
						<td colspan="3">No Identities defined.</td>
					</tr>';
			}?>
				</table>

				<div>Add new Identity URI: <input type="text" name="new_identity" size="30" /></div>
				<p><a href="?page=<?php echo $_REQUEST['page'] ?>&action=default">Add Default Identities</a> based on admin data.</p>


				<h3>Other Options</h3>
				<table>
					<tr>
						<th><input type="checkbox" name="microid_include_posts" id="microid_include_posts" <?php echo get_option('microid_include_posts') ? 'checked="checked"' : '' ?> /> Include Posts</th>
						<td>Include MicroIDs on individual post blocks using the email address of the post author and the permalink of the post.</td>
					</tr>
					<tr>
						<th style="white-space: nowrap;"><input type="checkbox" name="microid_include_comments" id="microid_include_comments" <?php echo get_option('microid_include_comments') ? 'checked="checked"' : '' ?> /> Include Comments</th>
						<td>Include MicroIDs on individual comments using the email address of the comment author and the permalink of the post. 
							(This may slightly decrease performance if you have a lot of comments.)</td>
					</tr>
				</table>

				<input type="submit" name="action" value="<?php _e('submit', '') ?>" />
			</form>
			<br />
		</div>

		<?php
	}

	function default_identities() {
		$identities = array();

		$identities[] = get_bloginfo('admin_email');

		return $identities;
	}


	/**
	 * If the identity URL does not include a scheme, try to guess the appropriate one.
	 */
	function canonicalize_identity_uri($uri) {
		if (preg_match('/^[a-zA-Z]+\:.+/', $uri)) {
			// uri contains a scheme
			return $uri;
		} else {
			// try to guess the scheme
			if (preg_match('/^[\=\@\+].+$/', $uri)) {
				return "xri://$uri";
			} else if (strstr($uri, '@')) {
				return "mailto:$uri";
			} else {
				return "http://$uri";
			}
		}
	}

	/**
	 * Compute a microid for the given identity and service URIs.
	 *
	 * @param string  $identity  identity URI
	 * @param string  $service   service URI
	 * @param string  $alorithm  algorithm to use in calculating the hash (default: sha1)
	 * @param boolean $legacy	if true, uritypes and algorithm will not be prefixed 
	 *   to the generated microid (default: false)
	 * @returns string the calculated microid
	 */
	function generate($identity, $service, $algorithm = 'sha1', $legacy = false) {
		$microid = "";

		// make sure we have input
		if (empty($identity) || empty($service)) {
			return;
		}

		// add uritypes and algorithm if not using legacy mode
		if (!$legacy) {
			$microid .= substr($identity, 0, strpos($identity, ':')) . "+" .
				substr($service, 0, strpos($service, ':')) . ":" .
				strtolower($algorithm) . ":";
		}

		// try message digest engine
		if (function_exists('hash')) {
			if (in_array(strtolower($algorithm), hash_algos())) {
				return $microid .= hash($algorithm, hash($algorithm, $identity) . hash($algorithm, $service));
			}
		}

		// try mhash engine
		if (function_exists('mhash')) {
			$hash_method = @constant('MHASH_' . strtoupper($algorithm));
			if ($hash_method != null) {
				$identity_hash = bin2hex(mhash($hash_method, $identity));
				$service_hash = bin2hex(mhash($hash_method, $service));
				return $microid .= bin2hex(mhash($hash_method, $identity_hash . $service_hash));
			}
		}

		// direct string function
		if (function_exists($algorithm)) { 
			return $microid .= $algorithm($algorithm($identity) . $algorithm($service));
		}

		error_log("MicroID: unable to find adequate function for algorithm '$algorithm'");
	}
}

?>

<?php
/*
Plugin Name: Subscribe To Comments
Version: 2.3-bleeding
Plugin URI: http://txfx.net/wordpress-plugins/subscribe-to-comments/
Description: Allows readers to choose to receive notifications of new comments that are posted to an entry.
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/






/**
 * Returns the CWS_STC singleton object
 * @return CWS_STC the instantiated CWS_STC object
 */
function _stc() {
	global $_cws_stc, $sg_subscribe;
	if ( !isset( $_cws_stc ) || !is_object( $_cws_stc ) ) {
		/**
		 * @global CWS_STC $_cws_stc
		 */
		$_cws_stc = new CWS_STC;
		/**
		 * @global CWS_STC $sg_subscribe
		 * @deprecated
		 */
		$sg_subscribe &= $_cws_stc; // Backwards compat
	}
	return $_cws_stc;
}

/* This is the code that is inserted into the comment form */
function show_subscription_checkbox( $id='0' ) {

	if ( _stc()->checkbox_shown )
		return $id;
	if ( !$email = _stc()->current_viewer_subscription_status() ) :
		$checked_status = ( !empty( $_COOKIE['subscribe_checkbox_'.COOKIEHASH] ) && 'checked' == $_COOKIE['subscribe_checkbox_'.COOKIEHASH] ) ? true : false;
	?>

	<p <?php if ( _stc()->clear_both ) echo 'style="clear: both;" '; ?>class="subscribe-to-comments">
	<input type="checkbox" name="subscribe" id="subscribe" value="subscribe" style="width: auto;" <?php if ( $checked_status ) echo 'checked="checked" '; ?>/>
	<label for="subscribe"><?php echo _stc()->not_subscribed_text; ?></label>
	</p>

<?php elseif ( $email == 'admin' && current_user_can( 'manage_options' ) ) : ?>

	<p <?php if ( _stc()->clear_both ) echo 'style="clear: both;" '; ?>class="subscribe-to-comments">
	<?php echo str_replace( '[manager_link]', _stc()->manage_link($email, true, false ), _stc()->author_text ); ?>
	</p>

<?php else : ?>

	<p <?php if ( _stc()->clear_both ) echo 'style="clear: both;" '; ?>class="subscribe-to-comments">
	<?php echo str_replace( '[manager_link]', _stc()->manage_link( $email, true, false ), _stc()->subscribed_text ); ?>
	</p>

<?php endif;

_stc()->checkbox_shown = true;
return $id;
}

function show_manual_subscription_form() {
	global $id, $sg_subscribe, $user_email;
		_stc()->show_errors( 'solo_subscribe', '<div class="solo-subscribe-errors">', '</div>', __( '<strong>Error: </strong>', 'subscribe-to-comments' ), '<br />' );

if ( !_stc()->current_viewer_subscription_status() ) :
	get_currentuserinfo(); ?>

	<form action="" method="post">
	<input type="hidden" name="solo-comment-subscribe" value="solo-comment-subscribe" />
	<input type="hidden" name="postid" value="<?php echo absint( $id ); ?>" />
	<input type="hidden" name="ref" value="<?php echo esc_url( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>" />

	<p class="solo-subscribe-to-comments">
	<?php _e( 'Subscribe without commenting', 'subscribe-to-comments' ); ?>
	<br />
	<label for="solo-subscribe-email"><?php _e( 'E-Mail:', 'subscribe-to-comments' ); ?>
	<input type="text" name="email" id="solo-subscribe-email" size="22" value="<?php echo esc_attr( $user_email ); ?>" /></label>
	<input type="submit" name="submit" value="<?php _e( 'Subscribe', 'subscribe-to-comments' ); ?>" />
	</p>
	</form>

<?php endif;
}

/**
 * Returns the subscription status of the current comment in the comments loop
 * @return bool
 */
function comment_subscription_status() {
	global $comment;
	return !!_stc()->is_subscribed( $comment->comment_ID );
}



















class CWS_STC {
	var $errors;
	var $messages;
	var $bid_post_subscriptions;
	var $email_subscriptions;
	var $subscriber_email;
	var $site_email;
	var $site_name;
	var $standalone;
	var $form_action;
	var $checkbox_shown;
	var $use_wp_style;
	var $header;
	var $sidebar;
	var $footer;
	var $clear_both;
	var $before_manager;
	var $after_manager;
	var $email;
	var $new_email;
	var $ref;
	var $key;
	var $key_type;
	var $action;
	var $default_subscribed;
	var $not_subscribed_text;
	var $subscribed_text;
	var $author_text;
	var $subscribed_format;
	var $salt;
	var $settings;
	var $version = '2.3-bleeding';
	var $is_multisite;
	var $ms_table;

	function __construct() {
		global $wpdb;
		// Multisite code is disabled. It exists so that adventurous blog networks can play with it they like.
		// IT IS NOT SUPPORTED. IT MAY BURN YOUR HOUSE DOWN.
		$this->is_multisite = defined( 'STC_MULTISITE' ) && STC_MULTISITE;
		if ( $this->is_multisite() )
			$this->ms_table = $wpdb->base_prefix . 'comment_subscriptions';
		$this->db_upgrade_check();

		$this->register_hooks();
		register_uninstall_hook( __FILE__, array( &$this, 'uninstall' ) );

		$this->settings = get_option( 'sg_subscribe_settings' );

		$this->salt = $this->settings['salt'];
		$this->site_email = ( is_email( $this->settings['email'] ) && $this->settings['email'] != 'email@example.com' ) ? $this->settings['email'] : get_bloginfo( 'admin_email' );
		$this->site_name = ( $this->settings['name'] != 'YOUR NAME' && !empty( $this->settings['name'] ) ) ? $this->settings['name'] : get_bloginfo( 'name' );
		$this->default_subscribed = ( $this->settings['default_subscribed'] ) ? true : false;

		$this->not_subscribed_text = $this->settings['not_subscribed_text'];
		$this->subscribed_text = $this->settings['subscribed_text'];
		$this->author_text = $this->settings['author_text'];
		$this->subscribed_format = $this->settings['subscribed_format'];
		$this->clear_both = $this->settings['clear_both'];

		$this->errors = '';
		$this->bid_post_subscriptions = array();
		$this->email_subscriptions = '';
	}

	function register_hooks() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		// This will be overridden if the user manually places the function
		// in the comments form before the comment_form do_action() call
		add_action( 'comment_form', 'show_subscription_checkbox' );

		// priority is very low (50) because we want to let anti-spam plugins have their way first.
		add_action( 'comment_post', array( &$this, 'send_notifications' ) );
		add_action( 'comment_post', array( &$this, 'maybe_add_subscriber' );
		add_action( 'wp_set_comment_status', array( &$this, 'send_notifications' );
		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
		add_action( 'admin_head', array( &$this, 'admin_head' ) );
		add_action( 'edit_comment', array( &$this, 'on_edit' ) );
		add_action( 'delete_comment', array( &$this, 'on_delete' ) );

		add_filter( 'get_comment_author_link', 'stc_comment_author_filter' );

		// save users' checkbox preference
		add_filter( 'preprocess_comment', 'stc_checkbox_state', 1 );

		// detect "subscribe without commenting" attempts
		add_action( 'init', array( &$this, 'maybe_solo_subscribe' ) );

		if ( isset( $_REQUEST['wp-subscription-manager'] ) )
			add_action( 'template_redirect', 'sg_subscribe_admin_standalone' );
	}

	/**
	 * PHP 4 constructor
	 */
	function CWS_STC() {
		return $this->__construct();
	}

	function uninstall() {
		delete_option( 'sg_subscribe_settings' );
	}

	function admin_init() {
		// menu_page_url() was introduced in WP 3.0
		if ( !function_exists( 'menu_page_url' ) ) :

		function menu_page_url( $menu_slug, $echo = true ) {
			global $_parent_pages;

			if ( isset( $_parent_pages[$menu_slug] ) ) {
				if ( $_parent_pages[$menu_slug] ) {
					$url = admin_url( add_query_arg( 'page', $menu_slug, $_parent_pages[$menu_slug] ) );
				} else {
					$url = admin_url( 'admin.php?page=' . $menu_slug );
				}
			} else {
				$url = '';
			}

			$url = esc_url( $url );

			if ( $echo )
				echo $url;

			return $url;
		}
		endif;

	}

	function manager_init() {
		$this->messages = '';
		$this->use_wp_style = ( $this->settings['use_custom_style'] == 'use_custom_style' ) ? false : true;
		if ( !$this->use_wp_style ) {
			$this->header = str_replace( '[theme_path]', get_template_directory(), $this->settings['header'] );
			$this->sidebar = str_replace( '[theme_path]', get_template_directory(), $this->settings['sidebar'] );
			$this->footer = str_replace( '[theme_path]', get_template_directory(), $this->settings['footer'] );
			$this->before_manager = $this->settings['before_manager'];
			$this->after_manager = $this->settings['after_manager'];
		}

		foreach ( array( 'email', 'key', 'ref', 'new_email' ) as $var )
			if ( isset( $_REQUEST[$var]) && !empty($_REQUEST[$var] ) )
				$this->{$var} = esc_attr( trim( stripslashes( $_REQUEST[$var] ) ) );
		if ( !$this->key )
			$this->key = 'unset';
	}

	function is_multisite() {
		return (bool) $this->is_multisite;
	}

	function add_error( $text='generic error', $type='manager' ) {
		$this->errors[$type][] = $text;
	}


	function show_errors( $type='manager', $before_all='<div class="updated updated-error">', $after_all='</div>', $before_each='<p>', $after_each='</p>' ){
		if ( is_array( $this->errors[$type] ) ) {
			echo $before_all;
			foreach ( $this->errors[$type] as $error )
				echo $before_each . $error . $after_each;
			echo $after_all;
		}
		unset( $this->errors);
	}


	function add_message( $text ) {
		$this->messages[] = $text;
	}


	function show_messages( $before_all='', $after_all='', $before_each='<div class="updated"><p>', $after_each='</p></div>' ){
		if ( is_array( $this->messages ) ) {
			echo $before_all;
			foreach ( $this->messages as $message )
				echo $before_each . $message . $after_each;
			echo $after_all;
		}
		unset( $this->messages );
	}


	function is_subscribed_email_post_bid( $email, $post_id, $bid = NULL ) {
		global $wpdb, $blog_id;
		$bid = ( NULL == $bid ) ? $blog_id : $bid;
		$email = strtolower( $email );
		if ( $this->is_multisite() && $wpdb->get_var( $wpdb->prepare( "SELECT email FROM $this->ms_table WHERE email = %s AND post_id = %s AND blog_id = %s", $email, $post_id, $bid ) ) )
			return true;
		elseif ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE LCASE( meta_value ) = %s AND meta_key = '_sg_subscribe-to-comments' AND post_id = %s", $email, $post_id ) ) )
			return true;
		return false;
	}


	function subscriptions_from_post( $postid, $bid=NULL ) {
		global $wpdb, $blog_id;
		if ( NULL == $bid )
			$bid = $blog_id;
		if ( is_array( $this->bid_post_subscriptions[$bid][$postid] ) )
			return $this->bid_post_subscriptions[$bid][$postid];
		$postid = (int) $postid;
		if ( $this->is_multisite() ) {
			$this->bid_post_subscriptions[$bid][$postid] = (array) $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT email FROM $this->ms_table WHERE blog_id = %d AND post_id = %d", $bid, $postid ) );
		} else {
			$this->bid_post_subscriptions[$bid][$postid] = (array) get_post_meta( $postid, '_sg_subscribe-to-comments' );
		}

		// Cleanup!
		$duplicates = $this->array_duplicates( $this->bid_post_subscriptions[$bid][$postid] );
		if ( $this->is_multisite() ) {
			if ( $duplicates ) {
				foreach ( (array) $duplicates as $duplicate ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM $this->ms_table WHERE blog_id = %d AND post_id = %d", $bid, $postid ) );
					$this->add_subscriber_by_post_id_and_email( $postid, $duplicate, $bid );
				}
			}
		} else {
			if ( $duplicates ) {
				foreach ( (array) $duplicates as $duplicate ) {
					delete_post_meta( $postid, '_sg_subscribe-to-comments', $duplicate );
					$this->add_subscriber_by_post_id_and_email( $postid, $duplicate, $bid );
				}
			}
		}

		$this->bid_post_subscriptions[$bid][$postid] = array_unique( $this->bid_post_subscriptions[$bid][$postid] );
		return $this->bid_post_subscriptions[$bid][$postid];
	}


	function subscriptions_from_email( $email='' ) {
		global $wpdb, $blog_id;
		if ( is_array( $this->email_subscriptions ) )
			return $this->email_subscriptions;
		if ( !is_email( $email ) )
			$email = $this->email;
		$email = strtolower( $email );

		if ( $this->is_multisite() ) {
			$where = ( current_user_can( 'manage_options' ) ) ? $wpdb->prepare( " AND blog_id = %d ", $blog_id ) : '';
			$subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id, post_id FROM $this->ms_table WHERE email = %s AND status='active' $where", $email ) );
		} else {
			$subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sg_subscribe-to-comments' AND LCASE(meta_value) = %s GROUP BY post_id", $email ) );
		}
		foreach ( (array) $subscriptions as $subscription)
			$this->email_subscriptions[] = array( ( isset( $subscription->blog_id ) ) ? $subscription->blog_id : $blog_id, $subscription->post_id );
		if ( is_array( $this->email_subscriptions ) ) {
			usort( $this->email_subscriptions, create_function( '$a,$b', 'if ($a[0] == $b[0]) { if ( $a[1] == $a[1] ) { return 0; } return ( $a[1] < $b[1] ) ? -1 : 1;} else { return ( $a[0] < $b[0] ) ? -1 : 1; }' ) );
			return $this->email_subscriptions;
		}
		return false;
	}

	function maybe_solo_subscribe() {
		if ( $_POST['solo-comment-subscribe'] == 'solo-comment-subscribe' && is_numeric( $_POST['postid'] ) )
				$this->solo_subscribe( stripslashes( $_POST['email'] ), (int) $_POST['postid'] );
	}

	function solo_subscribe ( $email, $postid ) {
		global $wpdb, $blog_id, $cache_userdata, $user_email;
		$postid = (int) $postid;
		$email = strtolower( $email );
		if ( !is_email( $email ) ) {
			get_currentuserinfo();
			if ( is_email( $user_email ) )
				$email = strtolower( $user_email );
			else
				$this->add_error( __( 'Please provide a valid e-mail address.', 'subscribe-to-comments' ),'solo_subscribe' );
		}

		if ( ( $email == $this->site_email && is_email( $this->site_email ) ) || (  $email == get_option( 'admin_email' ) && is_email( get_option( 'admin_email' ) ) ) )
			$this->add_error( __( 'This e-mail address may not be subscribed', 'subscribe-to-comments' ),'solo_subscribe' );

		if ( $this->is_subscribed_email_post_bid( $email, $postid, $blog_id ) ) {
			// already subscribed
			setcookie( 'comment_author_email_' . COOKIEHASH, $email, time() + 30000000, COOKIEPATH );
			$this->add_error( __( 'You appear to be already subscribed to this entry.', 'subscribe-to-comments' ),'solo_subscribe' );
		}
		$post = get_post( $postid );

		if ( !$post )
			$this->add_error( __( 'Comments are not allowed on this entry.', 'subscribe-to-comments' ),'solo_subscribe' );

		if ( empty( $cache_userdata[$post->post_author] ) && $post->post_author != 0 ) {
			$cache_userdata[$post->post_author] = $wpdb->get_row( "SELECT * FROM $wpdb->users WHERE ID = $post->post_author" );
			$cache_userdata[$cache_userdata[$post->post_author]->user_login] =& $cache_userdata[$post->post_author];
		}

		$post_author = $cache_userdata[$post->post_author];

		if ( strtolower( $post_author->user_email ) == ( $email ) )
			$this->add_error( __( 'You appear to be already subscribed to this entry.', 'subscribe-to-comments' ),'solo_subscribe' );

		if ( !is_array( $this->errors['solo_subscribe'] ) ) {
			add_post_meta( $postid, '_sg_subscribe-to-comments', strtolower( $email ) );
			setcookie( 'comment_author_email_' . COOKIEHASH, $email, time() + 30000000, COOKIEPATH );
			$location = $this->manage_link( $email, false, false ) . '&subscribeid=' . $postid;
			header( "Location: $location" );
			exit();
		}
	}


	// From: http://php.net/manual/en/function.array-unique.php#85109
	function array_duplicates( $array ) {
		if ( !is_array( $array ) )
			return false;

		$duplicates = array();
		foreach ( $array as $key => $val ) {
		end( $array );
		$k = key( $array );
		$v = current( $array );
			while ( $k !== $key ) {
				if ( $v === $val ) {
					$duplicates[$key] = $v;
					break;
				}
				$v = prev( $array );
				$k = key( $array );
			}
		}
		return $duplicates;
	}


	function maybe_add_subscriber( $cid ) {
		global $wpdb;
		if ( $this->settings['double_opt_in'] ) {
			$comment = get_comment( $cid );
	    	$email = strtolower( $comment->comment_author_email );
			if ( $this->is_multisite() ) {
				$proceed = !!$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->ms_table WHERE email = %s AND status = 'active'", $email ) );
			} else {
				$proceed = !!$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_sg_subscribe-to-comments' AND LCASE(meta_value) = %s", $email ) );
			}
		} else {
			$proceed = true;
		}

		if ( 'spam' != $comment->comment_approved && $_POST['subscribe'] == 'subscribe' ) {
			if ( $proceed )
				$this->add_subscriber( $cid );
			else
				$this->add_pending_subscriber( $cid );
		}
		return $cid;
	}


	function add_subscriber_by_post_id_and_email( $postid, $email, $bid = NULL ) {
		global $wpdb, $blog_id;
		$email = strtolower( $email );
		if ( $this->is_multisite() ) {
			$bid = ( NULL == $bid ) ? $blog_id : $bid;
			$already_subscribed_to_this_post = !!$wpdb->get_var( $wpdb->prepare( "SELECT email FROM $this->ms_table WHERE email = %s AND blog_id = %d AND post_id = %d AND status = 'active'", $email, $bid, $postid ) );
			if ( is_email( $email ) && !$already_subscribed_to_this_post )
				$wpdb->insert( $this->ms_table, array( 'email' => $email, 'blog_id' => $bid, 'post_id' => $postid, 'status' => 'active' ) );
		} else {
			$already_subscribed_to_this_post = in_array( $email, (array) get_post_meta( $postid, '_sg_subscribe-to-comments' ) );
			if ( is_email( $email ) && !$already_subscribed_to_this_post )
				add_post_meta( $postid, '_sg_subscribe-to-comments', $email );
		}
	}


	function add_subscriber( $cid ) {
		global $blog_id;
		$comment = get_comment( $cid );
    	$email = strtolower( $comment->comment_author_email );
		$postid = $comment->comment_post_ID;
		$this->add_subscriber_by_post_id_and_email( $postid, $email, $blog_id );
		return $cid;
	}


	function add_pending_subscriber( $cid ) {
		global $wpdb, $blog_id;
		$comment = get_comment( $cid );
    	$email = strtolower( $comment->comment_author_email );
		$postid = $comment->comment_post_ID;

		if ( $this->is_multisite() ) {
			$already_pending_on_this_post = !!$wpdb->get_var( $wpdb->prepare( "SELECT email FROM $this->ms_table WHERE email = %s AND blog_id = %d AND post_id = %d AND status = 'pending'", $email, $blog_id, $postid ) );
			if ( is_email( $email ) && !$already_pending_on_this_post ) {
				if ( !$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->ms_table WHERE email = %s AND status = 'pending-with-email' AND date_gmt > DATE_SUB( NOW(), INTERVAL 1 DAY)", $email ) ) ) {
					$wpdb->insert( $this->ms_table, array( 'email' => $email, 'blog_id' => $blog_id, 'post_id' => $postid, 'status' => 'pending-with-email', 'date_gmt' => current_time( 'mysql', 1 ) ) );
					$this->send_pending_nag( $cid );
				} else {
					$wpdb->insert( $this->ms_table, array( 'email' => $email, 'blog_id' => $blog_id, 'post_id' => $postid, 'status' => 'pending', 'date_gmt' => current_time( 'mysql', 1 ) ) );
				}
			}
		} else {
			$already_pending_on_this_post = in_array( $email, (array) get_post_meta( $postid, '_sg_subscribe-to-comments-pending' ) );
			if ( is_email( $email ) && !$already_pending_on_this_post ) {
				if ( !$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments, $wpdb->postmeta WHERE comment_post_ID = post_id AND LCASE( meta_value ) = %s AND meta_key = '_sg_subscribe-to-comments-pending-with-email' AND comment_date_gmt > DATE_SUB( NOW(), INTERVAL 1 DAY)", $email ) ) ) {
					add_post_meta( $postid, '_sg_subscribe-to-comments-pending-with-email', strtolower( $email ) );
					$this->send_pending_nag( $cid );
				} else {
					add_post_meta( $postid, '_sg_subscribe-to-comments-pending', strtolower( $email ) );
				}
			}
		}
		return $cid;
	}


	function confirm_pending_subscriber( $email ) {
		global $wpdb, $blog_id;
    	$email = strtolower( $email );

		if ( $this->is_multisite() ) {
			$wpdb->update( $this->ms_table, array( 'status' => 'active' ), array( 'email' => $email ) );
		} else {
			$pending = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key FROM $wpdb->postmeta WHERE LCASE(meta_value) = %s AND meta_key IN( '_sg_subscribe-to-comments-pending', '_sg_subscribe-to-comments-pending-with-email' )", $email ) );
			foreach ( (array) $pending as $p ) {
				$this->add_subscriber_by_post_id_and_email( $p->post_id, $email, $blog_id );
				delete_post_meta( $p->post_id, $p->meta_key, $email );
			}
		}

		return !!count( $pending );
	}


	function is_subscribed( $cid ) {
		$comment = &get_comment( $cid );
    	$email = strtolower( $comment->comment_author_email );
		$postid = $comment->comment_post_ID;
		return in_array( $email, (array) $this->subscriptions_from_post( $postid ) );
	}


	function is_blocked( $email='' ) {
		global $wpdb;
		if ( !is_email( $email ) )
			$email = $this->email;
		if ( empty( $email ) )
			return false;
		$email = strtolower( $email );
		// add the option if it doesn't exist
		add_option( 'do_not_mail', '' );
		$blocked = (array) explode ( ' ', get_option( 'do_not_mail' ) );
		if ( in_array( $email, $blocked ) )
			return true;
		return false;
	}


	function add_block( $email='' ) {
		global $wpdb;
		if ( !is_email( $email ) )
			$email = $this->email;
		$email = strtolower( $email );

		// add the option if it doesn't exist
		add_option( 'do_not_mail', '' );

		// check to make sure this email isn't already in there
		if ( !$this->is_blocked( $email ) ) {
			// email hasn't already been added - so add it
			$blocked = get_option( 'do_not_mail' ) . ' ' . $email;
			update_option( 'do_not_mail', $blocked );
			return true;
			}
		return false;
	}


	function remove_block( $email='' ) {
		global $wpdb;
		if ( !is_email( $email ) )
			$email = $this->email;
		$email = strtolower( $email );

		if ( $this->is_blocked( $email ) ) {
			// e-mail is in the list - so remove it
			$blocked = str_replace ( ' ' . $email, '', explode( ' ', get_option( 'do_not_mail' ) ) );
			update_option( 'do_not_mail', $blocked );
			return true;
			}
		return false;
	}


	function has_subscribers() {
		if ( count( $this->get_unique_subscribers() ) > 0 )
			return true;
		return false;
	}


	function get_unique_subscribers() {
		global $comments, $comment, $sg_subscribers;
		if ( isset( $sg_subscribers ) )
			return $sg_subscribers;

		$sg_subscribers = array();
		$subscriber_emails = array();

		// We run the comment loop, and put each unique subscriber into a new array
		foreach ( (array) $comments as $comment ) {
			if ( comment_subscription_status() && !in_array( $comment->comment_author_email, $subscriber_emails ) ) {
				$sg_subscribers[] = $comment;
				$subscriber_emails[] = $comment->comment_author_email;
			}
		}
		return $sg_subscribers;
	}


	function hidden_form_fields() { ?>
		<input type="hidden" name="ref" value="<?php echo $this->ref; ?>" />
		<input type="hidden" name="key" value="<?php echo $this->key; ?>" />
		<input type="hidden" name="email" value="<?php echo $this->email; ?>" />
	<?php
	}


	function generate_key( $data='' ) {
		if ( '' == $data )
			return false;
		if ( !$this->settings['salt'] )
			die( 'fatal error: corrupted salt' );
		return md5( md5( $this->settings['salt'] . $data ) );
	}


	function validate_key() {
		if ( $this->key == $this->generate_key( $this->email ) )
			$this->key_type = 'normal';
		elseif ( $this->key == $this->generate_key( $this->email . $this->new_email ) )
			$this->key_type = 'change_email';
		elseif ( $this->key == $this->generate_key( $this->email . 'blockrequest' ) )
			$this->key_type = 'block';
		elseif ( $this->key == $this->generate_key( 'opt_in:' . $this->email ) )
			$this->key_type = 'opt_in';
		elseif ( current_user_can( 'manage_options' ) )
			$this->key_type = 'admin';
		else
			return false;
		return true;
	}


	function determine_action() {
		// rather than check it a bunch of times
		$is_email = is_email( $this->email );

		if ( is_email( $this->new_email) && $is_email && $this->key_type == 'change_email' )
			$this->action = 'change_email';
		elseif ( $this->key_type == 'opt_in' && $is_email )
			$this->action = 'opt_in';
		elseif ( isset( $_POST['removesubscrips'] ) && $is_email )
			$this->action = 'remove_subscriptions';
		elseif ( isset( $_POST['removeBlock'] ) && $is_email && current_user_can( 'manage_options' ) )
			$this->action = 'remove_block';
		elseif ( isset( $_POST['changeemailrequest'] ) && $is_email && is_email( $this->new_email ) )
			$this->action = 'email_change_request';
		elseif ( $is_email && isset( $_POST['blockemail'] ) )
			$this->action = 'block_request';
		elseif ( isset( $_GET['subscribeid'] ) )
			$this->action = 'solo_subscribe';
		elseif ( $is_email && isset( $_GET['blockemailconfirm'] ) && $this->key == $this->generate_key( $this->email . 'blockrequest' ) )
			$this->action = 'block';
		else
			$this->action = 'none';
	}


	function remove_subscriber( $email, $postid, $bid = NULL ) {
		global $wpdb, $blog_id;
		$postid = (int) $postid;
		$bid = absint( ( NULL == $bid ) ? $blog_id : $bid );
		$email = strtolower( $email );

		if ( $this->is_multisite() ) {
			echo "REMOVING $bid : $postid : $email";
			if ( $wpdb->query( $wpdb->prepare( "DELETE FROM $this->ms_table WHERE email = %s AND post_id = %d AND blog_id = %d", $email, $postid, $bid ) ) )
				return true;
		} else {
			if ( delete_post_meta( $postid, '_sg_subscribe-to-comments', $email ) || delete_post_meta( $postid, '_sg_subscribe-to-comments-pending-with-email', $email ) || delete_post_meta( $postid, '_sg_subscribe-to-comments-pending', $email ) )
			return true;
		}
			return false;
		}


	function remove_subscriptions ( $bid_post_ids ) {
		global $wpdb;
		$removed = 0;
		foreach ( $bid_post_ids as $bp ) {
			$bp = explode( '-', $bp );
			// echo 'Removing BID: ' . $bp[0] . ' PID:' . $bp[1];
			if ( $this->remove_subscriber( $this->email, $bp[1], $bp[0] ) )
				$removed++;
		}
		return $removed;
	}


	function send_notifications( $cid ) {
		global $wpdb;
		$comment =& get_comment( $cid );
		$post = get_post( $comment->comment_post_ID );

		if ( $comment->comment_approved == '1' && $comment->comment_type == '' ) {
			// Comment has been approved and isn't a trackback or a pingback, so we should send out notifications

			$message  = sprintf( __( "There is a new comment on the post \"%s\"", 'subscribe-to-comments' ) . ". \n%s\n\n", $post->post_title, get_permalink( $comment->comment_post_ID ) );
			$message .= sprintf( __( "Author: %s\n", 'subscribe-to-comments' ), $comment->comment_author );
			$message .= __( "Comment:\n", 'subscribe-to-comments' ) . $comment->comment_content . "\n\n";
			$message .= __( "See all comments on this post here:\n", 'subscribe-to-comments' );
			$message .= get_permalink( $comment->comment_post_ID ) . "#comments\n\n";
			//add link to manage comment notifications
			$message .= __( "To manage your subscriptions or to block all notifications from this site, click the link below:\n", 'subscribe-to-comments' );
			$message .= get_option( 'home' ) . '/?wp-subscription-manager=1&email=[email]&key=[key]';

			$subject = sprintf( __( 'New Comment On: %s', 'subscribe-to-comments' ), $post->post_title );

			$subscriptions = $this->subscriptions_from_post( $comment->comment_post_ID );
			foreach ( (array) $subscriptions as $email ) {
				if ( !$this->is_blocked( $email ) && $email != $comment->comment_author_email && is_email( $email ) ) {
				        $message_final = str_replace( '[email]', urlencode( $email ), $message );
				        $message_final = str_replace( '[key]', $this->generate_key( $email ), $message_final );
					$this->send_mail( $email, $subject, $message_final );
				}
			} // foreach subscription
		} // end if comment approved
		return $cid;
	}


	function send_pending_nag( $cid ) {
		$comment =& get_comment( $cid );
		$email = strtolower( $comment->comment_author_email );
		$subject = __( 'Subscription Confirmation', 'subscribe-to-comments' );
		$message = sprintf( __( "You are receiving this message to confirm your comment subscription at \"%s\"\n\n", 'subscribe-to-comments' ), get_bloginfo( 'blogname' ) );
		$message .= __( "To confirm your subscription, click this link:\n\n", 'subscribe-to-comments' );
		$message .= get_option( 'home' ) . "/?wp-subscription-manager=1&email=" . urlencode( $email ) . "&key=" . $this->generate_key( 'opt_in:' . $email ) . "\n\n";
		$message .= __( 'If you did not request this subscription, please disregard this message.', 'subscribe-to-comments' );
		return $this->send_mail( $email, $subject, $message );
	}


	function change_email_request() {
		if ( $this->is_blocked() )
			return false;

		$subject = __( 'E-mail change confirmation', 'subscribe-to-comments' );
		$message = sprintf( __( "You are receiving this message to confirm a change of e-mail address for your subscriptions at \"%s\"\n\n", 'subscribe-to-comments' ), get_bloginfo( 'blogname' ) );
		$message .= sprintf( __( "To change your e-mail address to %s, click this link:\n\n", 'subscribe-to-comments' ), $this->new_email );
		$message .= get_option( 'home' ) . "/?wp-subscription-manager=1&email=" . urlencode( $this->email ) . "&new_email=" . urlencode( $this->new_email ) . "&key=" . $this->generate_key( $this->email . $this->new_email ) . ".\n\n";
		$message .= __( 'If you did not request this action, please disregard this message.', 'subscribe-to-comments' );
		return $this->send_mail( $this->email, $subject, $message );
	}


	function block_email_request( $email ) {
		if ( $this->is_blocked( $email ) )
			return false;
		$subject = __( 'E-mail block confirmation', 'subscribe-to-comments' );
		$message = sprintf( __( "You are receiving this message to confirm that you no longer wish to receive e-mail comment notifications from \"%s\"\n\n", 'subscribe-to-comments' ), get_bloginfo( 'name' ) );
		$message .= __( "To cancel all future notifications for this address, click this link:\n\n", 'subscribe-to-comments' );
		$message .= get_option( 'home' ) . "/?wp-subscription-manager=1&email=" . urlencode( $email ) . "&key=" . $this->generate_key( $email . 'blockrequest' ) . "&blockemailconfirm=true" . ".\n\n";
		$message .= __( "If you did not request this action, please disregard this message.", 'subscribe-to-comments' );
		return $this->send_mail( $email, $subject, $message );
	}


	function send_mail( $to, $subject, $message ) {
		$subject = '[' . get_bloginfo( 'name' ) . '] ' . $subject;

		// strip out some chars that might cause issues, and assemble vars
		$site_name = str_replace( '"', "'", $this->site_name );
		$site_email = str_replace( array( '<', '>' ), array( '', '' ), $this->site_email );
		$charset = get_option( 'blog_charset' );

		$headers  = "From: \"{$site_name}\" <{$site_email}>\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/plain; charset=\"{$charset}\"\n";
		return wp_mail( $to, $subject, $message, $headers );
	}


	function change_email() {
		global $wpdb;
		$new_email = strtolower( $this->new_email );
		$email = strtolower( $this->email );
		if ( $wpdb->update( $wpdb->comments, array( 'comment_author_email' => $new_email ), array( 'comment_author_email' => $email ) ) )
			$return = true;
		if ( $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $new_email ), array( 'meta_value' => $email, 'meta_key' => '_sg_subscribe-to-comments' ) ) )
			$return = true;
		if ( $wpdb->update( $this->ms_table, array( 'email' => $new_email ), array( 'email' => $email ) ) )
			$return = true;
		return $return;
	}


	function entry_link( $bid, $postid, $uri='') {
		global $blog_id;
		if ( $blog_id != $bid ) {
			$switched = true;
			switch_to_blog( $bid );
		}
		if ( empty( $uri ) )
			$uri = clean_url( get_permalink( $postid ) );
		$postid = (int) $postid;
		$title = get_the_title( $postid );
		if ( empty( $title ) )
			$title = __( 'click here', 'subscribe-to-comments' );
		$output = '<a href="'.$uri.'">'. wp_specialchars( get_option( 'blogname' ) ) . ' &rarr; ' . $title.'</a>';
		if ( $switched )
			restore_current_blog();
		return $output;
	}


	function admin_head() { ?>
		<style type="text/css" media="screen">
		.updated-error {
			background-color: #FF8080;
			border: 1px solid #F00;
		}
		</style>
		<?php
		return true;
	}


	function db_upgrade_check () {
		global $wpdb, $blog_id;

		// add the options
		add_option( 'sg_subscribe_settings', array( 'use_custom_style' => '', 'email' => get_bloginfo( 'admin_email' ), 'name' => get_bloginfo( 'name' ), 'header' => '[theme_path]/header.php', 'sidebar' => '', 'footer' => '[theme_path]/footer.php', 'before_manager' => '<div id="content" class="widecolumn subscription-manager">', 'after_manager' => '</div>', 'not_subscribed_text' => __( 'Notify me of followup comments via e-mail', 'subscribe-to-comments' ), 'subscribed_text' => __( 'You are subscribed to this entry.  <a href="[manager_link]">Manage your subscriptions</a>.', 'subscribe-to-comments' ), 'author_text' => __( 'You are the author of this entry.  <a href="[manager_link]">Manage subscriptions</a>.', 'subscribe-to-comments' ), 'version' => 0, 'double_opt_in' => '', 'subscribed_format' => '%NAME%' ) );

		$settings = get_option( 'sg_subscribe_settings' );

		if ( !isset( $settings['salt'] ) ) {
			$settings['salt'] = md5( md5( uniqid( rand() . rand() . rand() . rand() . rand(), true ) ) ); // random MD5 hash
			$update = true;
		}

		if ( !isset( $settings['clear_both'] ) ) {
			$settings['clear_both'] = 'clear_both';
			$update = true;
		}

		if ( !isset( $settings['version'] ) ) {
			$settings = stripslashes_deep( $settings );
			$update = true;
		}

		if ( !isset( $settings['double_opt_in'] ) ) {
			$settings['double_opt_in'] = '';
			$update = true;
		}

		if ( !isset( $settings['subscribed_format'] ) ) {
			$settings['subscribed_format'] = '%NAME%';
			$update = true;
		}

		if ( !$this->is_multisite() && version_compare( $settings['version'], '2.2', '<' ) ) { // Upgrade to postmeta-driven subscriptions
			foreach ( (array) $wpdb->get_col( "DESC $wpdb->comments", 0 ) as $column ) {
				if ( $column == 'comment_subscribe' ) {
					$upgrade_comments = $wpdb->get_results( "SELECT comment_post_ID, comment_author_email FROM $wpdb->comments WHERE comment_subscribe = 'Y'" );
					foreach ( (array) $upgrade_comments as $upgrade_comment )
						$this->add_subscriber_by_post_id_and_email( $upgrade_comment->comment_post_ID, $upgrade_comment->comment_author_email, $blog_id );
					// Done. Drop the column
					$wpdb->query( "ALTER TABLE $wpdb->comments DROP COLUMN comment_subscribe" );
				}
			}
			$udpate = true;
		}

		elseif ( $this->is_multisite() && ( version_compare( $settings['version'], '2.2', '<' ) || !isset( $settings['version'] ) ) ) {
			// Create WPMU tables
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) )
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				if ( ! empty( $wpdb->collate ) )
					$charset_collate .= " COLLATE $wpdb->collate";
			}
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$queries = "CREATE TABLE IF NOT EXISTS $this->ms_table (
				id int(11) NOT NULL auto_increment,
				email varchar(100) NOT NULL,
				blog_id int(11) NOT NULL default 0,
				post_id int(11) NOT NULL default 0,
				date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
				status varchar(20) NOT NULL default '',
				PRIMARY KEY  (id),
				KEY email_blog_post_status (email,blog_id,post_id,status),
				KEY email_status (email,status)
			) $charset_collate";
			dbDelta( $queries );
		}

		if ( $update || !isset( $settings['version'] ) )
			$this->update_settings( $settings );
	}


	function update_settings( $settings ) {
		$settings['version'] = $this->version;
		if ( strpos( $settings['subscribed_format'], '%NAME%' ) === false )
			$settings['subscribed_format'] = '%NAME%';
		update_option( 'sg_subscribe_settings', $settings );
	}


	function current_viewer_subscription_status(){
		global $wpdb, $blog_id, $post, $user_email;

		$comment_author_email = ( isset( $_COOKIE['comment_author_email_'. COOKIEHASH] ) ) ? trim( $_COOKIE['comment_author_email_'. COOKIEHASH] ) : '';
		get_currentuserinfo();

		if ( is_email( $user_email ) ) {
			$email = strtolower( $user_email );
			$loggedin = true;
		} elseif ( is_email( $comment_author_email ) ) {
			$email = strtolower( $comment_author_email );
		} else {
			return false;
		}

		$post_author = get_userdata( $post->post_author );
		if ( strtolower( $post_author->user_email ) == $email && $loggedin )
			return 'admin';

		if ( in_array( $email, (array) $this->subscriptions_from_post( $post->ID ) ) )
			return $email;
		return false;
	}

	function manage_link( $email='', $html=true, $echo=true ) {
		$link  = get_option( 'home' ) . '/?wp-subscription-manager=1';
		if ( $email != 'admin' ) {
			$link = add_query_arg( 'email', urlencode( $email ), $link );
			$link = add_query_arg( 'key', $this->generate_key( $email ), $link );
		}
		$link = add_query_arg( 'ref', rawurlencode( 'http://' . $_SERVER['HTTP_HOST'] . esc_attr( $_SERVER['REQUEST_URI'] ) ), $link);
		//$link = str_replace('+', '%2B', $link);
		if ( $html )
			$link = htmlentities( $link );
		if ( !$echo )
			return $link;
		echo $link;
	}


	function on_edit( $cid ) {
		global $blog_id;
		$comment =& get_comment( $cid );
		$email = strtolower( $comment->comment_author_email );
		$postid = $comment->comment_post_ID;
		if ( !is_email( $email ) )
			_stc()->remove_subscriber( $email, $postid, $blog_id );
		return $cid;
	}


	function on_delete( $cid ) {
		global $blog_id;
		$comment = &get_comment( $cid );
		$email = strtolower( $comment->comment_author_email );
		$postid = $comment->comment_post_ID;
		_stc()->remove_subscriber( $email, $postid, $blog_id );
		return $cid;
	}

	function get_comment_author_format() {
		if ( strpos( $this->subscribed_format, '%NAME%' ) === false )
			return '%NAME%';
		else
			return $this->subscribed_format;
	}

	function comment_author_filter( $author ) {
		if ( comment_subscription_status() )
			$author = str_replace( '%NAME%', $author, $this->get_comment_author_format() );
		return $author;
	}

	function add_admin_menu() {
		$manager_callback = add_submenu_page( 'edit-comments.php', __( 'Comment Subscription Manager', 'subscribe-to-comments' ), __( 'Subscriptions', 'subscribe-to-comments' ), 'manage_options', 'stc-management', 'sg_subscribe_admin' );
		$opt_callback = add_options_page( __( 'Subscribe to Comments', 'subscribe-to-comments' ), __( 'Subscribe to Comments', 'subscribe-to-comments' ), 'publish_posts', 'stc-options', array( _stc(), 'options_page' ) );
		add_action( "load-{$opt_callback}", array( _stc(), 'save_options' ) );
	}











//////////////////// OPTIONS ////////////////////////



function tr( $option_slug, $option_title, $option_text, $description ='' ) {
	echo "<tr valign='top'>\n\t<th scope='row'><label for='" . $option_slug . "'>" . $option_title . "</label></th>\n\t<td>" . $option_text;
	if ( !empty( $description ) )
		echo '<span class="setting-description">' . $description . '</span>';
	echo "</td>\n</tr>\n";
}

function save_options() {
	if ( isset( $_POST['sg_subscribe_settings_submit'] ) ) {
		check_admin_referer( 'subscribe-to-comments-update_options' );
		$update_settings = stripslashes_deep( $_POST['sg_subscribe_settings'] );
		_stc()->update_settings( $update_settings );
		wp_redirect( add_query_arg( 'updated', '1', menu_page_url( 'stc-options', false ) ) );
		add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		exit();
	}
}

function options_page_contents() {
	echo '<h2>' . __( 'Subscribe to Comments Settings', 'subscribe-to-comments' ) . '</h2>';


	echo '<h3>' . __( 'Notification e-mails', 'subscribe-to-comments' ) . '</h3>';
	echo '<table class="form-table">';
	$this->tr( 'name', __( '"From" name', 'subscribe-to-comments' ), '<input type="text" size="40" id="name" name="sg_subscribe_settings[name]" value="' . $this->form_setting( 'name' ) . '" />' );
	$this->tr( 'email', __( '"From" address', 'subscribe-to-comments' ), '<input type="text" size="40" id="email" name="sg_subscribe_settings[email]" value="' . $this->form_setting( 'email' ) . '" />', __( 'You may want this to be a special account that you set up for this purpose, as it will go out to everyone who subscribes', 'subscribe-to-comments' ) );
	$this->tr( 'double_opt_in', __( 'Double opt-in', 'subscribe-to-comments' ), '<input type="checkbox" id="double_opt_in" name="sg_subscribe_settings[double_opt_in]" value="double_opt_in"' . $this->checkflag( 'double_opt_in' ) . ' /> <label for="double_opt_in">' . __( 'Require verification of first-time subscription e-mails (this is known as "double opt-in" and is required by law in some countries)', 'subscribe-to-comments') . '</label>' );
	echo '</table>';

	echo '<h3>' . __( 'Subscriber name formatting', 'subscribe_to_comments' ) . '</h3>';
	echo '<table class="form-table">';
	$this->tr( 'subscribed_format', __( 'Subscribed format', 'subscribe-to-comments' ), '<input type="text" size="40" id="subscribed_format" name="sg_subscribe_settings[subscribed_format]" value="' . $this->form_setting( 'subscribed_format' ) . '" />', __( 'e.g. <code>%NAME% (subscribed)</code> will display <code>John Smith (subscribed)</code>', 'subscribe-to-comments' ) );
	echo '</table>';


	echo '<h3>' . __( 'Comment form layout' ) . '</h3>';
	echo '<table class="form-table">';
	$this->tr( 'clear_both', 'CSS clearing', '<input type="checkbox" id="clear_both" name="sg_subscribe_settings[clear_both]" value="clear_both"' . $this->checkflag( 'clear_both' ) . ' /> <label for="clear_both">' . __( 'Do a CSS "clear" on the subscription checkbox/message (uncheck this if the checkbox/message appears in a strange location in your theme)', 'subscribe-to-comments' ) . '</label>' );
	echo '</table>';

	echo '<h3>' . __( 'Comment form text', 'subscribe-to-comments' ) . '</h3>';
	echo '<p>' . __( 'Customize the messages shown to different people.  Use <code>[manager_link]</code> to insert the URI to the Subscription Manager.', 'subscribe-to-comments' ) . '</p>';
	echo '<table class="form-table">';
	$this->tr( 'not_subscribed_text', __( 'Not subscribed text', 'subscribe-to-comments' ), '<textarea style="width: 98%; font-size: 12px;" rows="2" cols="60" id="not_subscribed_text" name="sg_subscribe_settings[not_subscribed_text]">' . $this->textarea_setting( 'not_subscribed_text' ) . '</textarea>' );
	$this->tr( 'subscribed_text', __( 'Subscribed text', 'subscribe-to-comments' ), '<textarea style="width: 98%; font-size: 12px;" rows="2" cols="60" id="subscribed_text" name="sg_subscribe_settings[subscribed_text]">' . $this->textarea_setting( 'subscribed_text' ) . '</textarea>' );
	$this->tr( 'author_text', __( 'Entry author text', 'subscribe-to-comments' ), '<textarea style="width: 98%; font-size: 12px;" rows="2" cols="60" id="author_text" name="sg_subscribe_settings[author_text]">' . $this->textarea_setting( 'author_text' ) . '</textarea>' );
	echo '</table>';

	echo '<h3>' . __( 'Subscription manager', 'subscribe-to-comments' ) . '</h3>';
	echo '<table class="form-table">';
	$this->tr( 'use_custom_style', __( 'Custom style', 'subscribe-to-comments' ), '<input type="checkbox" onchange="if(this.checked){document.getElementById(\'stc-custom-style-div\').style.display=\'block\';}else{document.getElementById(\'stc-custom-style-div\').style.display=\'none\';}" id="use_custom_style" name="sg_subscribe_settings[use_custom_style]" value="use_custom_style"' . $this->checkflag( 'use_custom_style' ) . ' /> <label for="use_custom_style">' . __( 'Use custom style for Subscription Manager', 'subscribe-to-comments' ) . '</label>' );
	echo '</table>';

	echo '<div id="stc-custom-style-div" style="' . ( ( $this->form_setting( 'use_custom_style' ) != 'use_custom_style' ) ? 'display:none' : '' ) . '">';
	echo '<p>' . __( 'These settings only matter if you are using a custom style.  <code>[theme_path]</code> will be replaced with the path to your current theme.', 'subscribe-to-comments' ) . '</p>';
	echo '<table class="form-table">';
	$this->tr( 'header', __( 'Path to header', 'subscribe-to-comments' ), '<input type="text" size="40" id="sg_sub_header" name="sg_subscribe_settings[header]" value="' . $this->form_setting( 'header' ) . '" />' );
	$this->tr( 'sidebar', __( 'Path to sidebar', 'subscribe-to-comments' ), '<input type="text" size="40" id="sg_sub_sidebar" name="sg_subscribe_settings[sidebar]" value="' . $this->form_setting( 'sidebar' ) . '" />' );
	$this->tr( 'footer', __( 'Path to footer', 'subscribe-to-comments' ), '<input type="text" size="40" id="sg_sub_footer" name="sg_subscribe_settings[footer]" value="' . $this->form_setting( 'footer' ) . '" />' );
	$this->tr( 'before_manager', __( 'HTML for before the subscription manager', 'subscribe-to-comments' ), '<textarea style="width: 98%; font-size: 12px;" rows="2" cols="60" id="before_manager" name="sg_subscribe_settings[before_manager]">' . $this->textarea_setting( 'before_manager' ) . '</textarea>' );
	$this->tr( 'after_manager', __( 'HTML for after the subscription manager', 'subscribe-to-comments' ), '<textarea style="width: 98%; font-size: 12px;" rows="2" cols="60" id="after_manager" name="sg_subscribe_settings[after_manager]">' . $this->textarea_setting( 'after_manager' ) . '</textarea>' );
	echo '</table>';
	echo '</div>';
}

function checkflag( $optname ) {
	$options = get_option( 'sg_subscribe_settings' );
	if ( $options[$optname] != $optname )
		return;
	return ' checked="checked"';
}

/**
 * Returns an HTML attribute sanitized version of a setting
 * @param string $option_name The name of the STC option to fetch
 * @return string the sanitized setting
 */
function form_setting( $option_name ) {
	$options = get_option( 'sg_subscribe_settings' );
	return esc_attr( $options[$option_name] );
}

function textarea_setting( $optname ) {
	$options = get_option( 'sg_subscribe_settings' );
	return wp_specialchars( $options[$optname] );
}

function options_page() {
	echo '<form method="post">';
	screen_icon();
	echo '<div class="wrap">';

	$this->options_page_contents();

	echo '<p class="submit"><input type="submit" name="sg_subscribe_settings_submit" value="';
	_e( 'Save Changes', 'subscribe-to-comments' );
	echo '" class="button-primary" /></p></div>';

	wp_nonce_field( 'subscribe-to-comments-update_options' );

	echo '</form>';
}








































} // class sg_subscribe





function stc_checkbox_state( $data ) {
	if ( isset( $_POST['subscribe'] ) )
		setcookie( 'subscribe_checkbox_'. COOKIEHASH, 'checked', time() + 30000000, COOKIEPATH );
	else
		setcookie( 'subscribe_checkbox_'. COOKIEHASH, 'unchecked', time() + 30000000, COOKIEPATH );
	return $data;
}


function stc_comment_author_filter( $author ) {
			return _stc()->comment_author_filter( $author );
}


function sg_subscribe_start() {
	if ( !$sg_subscribe ) {
		load_plugin_textdomain( 'subscribe-to-comments', false, dirname( plugin_basename( __FILE__ ) ) );
		$sg_subscribe = new CWS_STC();
	}
}



function sg_subscribe_admin_standalone() {
	sg_subscribe_admin( true );
}

function sg_subscribe_admin( $standalone = false ) {
	global $wpdb, $sg_subscribe, $wp_version, $blog_id;

	
	if ( $standalone ) {
		_stc()->form_action = get_option( 'home' ) . '/?wp-subscription-manager=1';
		_stc()->standalone = true;
		ob_start( create_function( '$a', 'return str_replace( "<title>", "<title> " . __( "Subscription Manager", "subscribe-to-comments" ) . " &raquo; ", $a );' ) );
	} else {
		_stc()->form_action = 'edit-comments.php?page=stc-management';
		_stc()->standalone = false;
	}

	_stc()->manager_init();

	get_currentuserinfo();

	if ( !_stc()->validate_key() )
		die ( __( 'You may not access this page without a valid key.', 'subscribe-to-comments' ) );

	_stc()->determine_action();

	switch ( _stc()->action ) :

		case "opt_in" :
			if ( _stc()->confirm_pending_subscriber( _stc()->email ) ) {
				_stc()->add_message( sprintf( __( 'Thank you! <strong>%1$s</strong> has been confirmed, and is now subscribed to comments.', 'subscribe-to-comments' ), _stc()->email ) );
			}
			break;

		case "change_email" :
			if ( _stc()->change_email() ) {
				_stc()->add_message( sprintf( __('All notifications that were formerly sent to <strong>%1$s</strong> will now be sent to <strong>%2$s</strong>!', 'subscribe-to-comments' ), _stc()->email, _stc()->new_email ) );
				// change info to the new email
				_stc()->email = _stc()->new_email;
				unset( _stc()->new_email );
				_stc()->key = _stc()->generate_key( _stc()->email );
				_stc()->validate_key();
			}
			break;

		case "remove_subscriptions" :
			$postsremoved = _stc()->remove_subscriptions( $_POST['subscrips'] );
			if ( $postsremoved > 0 )
				_stc()->add_message( sprintf( __( '<strong>%1$s</strong> %2$s removed successfully.', 'subscribe-to-comments' ), $postsremoved, ( $postsremoved != 1 ) ? __( 'subscriptions', 'subscribe-to-comments' ) : __( 'subscription', 'subscribe-to-comments' ) ) );
			break;

		case "remove_block" :
			if ( _stc()->remove_block( _stc()->email ) )
				_stc()->add_message( sprintf( __( 'The block on <strong>%s</strong> has been successfully removed.', 'subscribe-to-comments' ), _stc()->email ) );
			else
				_stc()->add_error( sprintf( __( '<strong>%s</strong> isn\'t blocked!', 'subscribe-to-comments' ), _stc()->email ), 'manager' );
			break;

		case "email_change_request" :
			if ( _stc()->is_blocked( _stc()->email ) )
				_stc()->add_error( sprintf( __( '<strong>%s</strong> has been blocked from receiving notifications.  You will have to have the administrator remove the block before you will be able to change your notification address.', 'subscribe-to-comments' ), _stc()->email ) );
			else
				if ( _stc()->change_email_request( _stc()->email, _stc()->new_email ) )
					_stc()->add_message( sprintf( __( 'Your change of e-mail request was successfully received.  Please check your old account (<strong>%s</strong>) in order to confirm the change.', 'subscribe-to-comments' ), _stc()->email ) );
			break;

		case "block_request" :
			if ( _stc()->block_email_request( _stc()->email ) )
				_stc()->add_message( sprintf( __( 'Your request to block <strong>%s</strong> from receiving any further notifications has been received.  In order for you to complete the block, please check your e-mail and click on the link in the message that has been sent to you.', 'subscribe-to-comments' ), _stc()->email ) );
			break;

		case "solo_subscribe" :
			_stc()->add_message( sprintf( __( '<strong>%1$s</strong> has been successfully subscribed to %2$s', 'subscribe-to-comments' ), _stc()->email, _stc()->entry_link( $blog_id, $_GET['subscribeid'] ) ) );
			break;

		case "block" :
			if ( _stc()->add_block( _stc()->email ) )
				_stc()->add_message( sprintf( __( '<strong>%1$s</strong> has been added to the "do not mail" list. You will no longer receive any notifications from this site. If this was done in error, please contact the <a href="mailto:%2$s">site administrator</a> to remove this block.', 'subscribe-to-comments' ), _stc()->email, _stc()->site_email ) );
			else
				_stc()->add_error( sprintf( __( '<strong>%s</strong> has already been blocked!', 'subscribe-to-comments'), _stc()->email), 'manager');
			_stc()->key = _stc()->generate_key( _stc()->email );
			_stc()->validate_key();
			break;

	endswitch;



	if ( _stc()->standalone ) {
		if ( !_stc()->use_wp_style && !empty( _stc()->header ) ) {
		@include( _stc()->header );
		echo _stc()->before_manager;
	} else { ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html>
	<head>
	<title><?php _e( 'Comment Subscription Manager', 'subscribe-to-comments' ); ?></title>

		<style type="text/css" media="screen">
			@import url( <?php echo get_option( 'siteurl' ); ?>/wp-admin/css/global.css );
		</style>

		<meta http-equiv="Content-Type" content="text/html;
	charset=<?php bloginfo( 'charset' ); ?>" />

	<?php _stc()->admin_head(); ?>

	</head>
	<body>
	<?php } ?>
	<?php } ?>


	<?php _stc()->show_messages(); ?>

	<?php _stc()->show_errors(); ?>

	<?php if ( function_exists( 'screen_icon' ) ) screen_icon(); ?>
	<div class="wrap">
	<h2><?php _e( 'Comment Subscription Manager', 'subscribe-to-comments' ); ?></h2>

	<?php if ( !empty( _stc()->ref ) ) : ?>
	<?php _stc()->add_message( sprintf( __( 'Return to the page you were viewing: %s', 'subscribe-to-comments' ), _stc()->entry_link( $blog_id, url_to_postid( _stc()->ref ), _stc()->ref ) ) ); ?>
	<?php _stc()->show_messages(); ?>
	<?php endif; ?>



	<?php if ( _stc()->is_blocked() ) { ?>

		<?php if ( current_user_can( 'manage_options' ) ) : ?>

		<h3><?php _e( 'Remove Block', 'subscribe-to-comments' ); ?></h3

			<p>
			<?php printf( __( 'Click the button below to remove the block on <strong>%s</strong>.  This should only be done if the user has specifically requested it.', 'subscribe-to-comments' ), _stc()->email ); ?>
			</p>

			<form name="removeBlock" method="post" action="<?php echo _stc()->form_action; ?>">
			<input type="hidden" name="removeBlock" value="removeBlock /">
	<?php _stc()->hidden_form_fields(); ?>

			<p class="submit">
			<input type="submit" name="submit" value="<?php _e( 'Remove Block &raquo;', 'subscribe-to-comments' ); ?>" />
			</p>
			</form>

	<?php else : ?>

		<h3><?php _e( 'Blocked', 'subscribe-to-comments' ); ?></h3>

			<p>
			<?php printf( __( 'You have indicated that you do not wish to receive any notifications at <strong>%1$s</strong> from this site. If this is incorrect, or if you wish to have the block removed, please contact the <a href="mailto:%2$s">site administrator</a>.', 'subscribe-to-comments' ), _stc()->email, _stc()->site_email ); ?>
			</p>

	<?php endif; ?>


	<?php } else { ?>


	<?php $postlist = _stc()->subscriptions_from_email(); ?>

<?php
		if ( isset( _stc()->email ) && !is_array( $postlist ) && _stc()->email != _stc()->site_email && _stc()->email != get_bloginfo( 'admin_email' ) ) {
			if ( is_email( _stc()->email ) )
				_stc()->add_error( sprintf( __( '<strong>%s</strong> is not subscribed to any posts on this site.', 'subscribe-to-comments' ), _stc()->email ) );
			else
				_stc()->add_error( sprintf( __( '<strong>%s</strong> is not a valid e-mail address.', 'subscribe-to-comments' ), _stc()->email ) );
		}
?>

	<?php _stc()->show_errors(); ?>




	<?php if ( current_user_can( 'manage_options' ) ) { ?>

			<?php if ( $_REQUEST['email'] ) : ?>
				<p><a href="<?php echo clean_url( _stc()->form_action ); ?>"><?php _e( '&laquo; Back' ); ?></a></p>
			<?php endif; ?>

			<h3><?php _e( 'Find Subscriptions', 'subscribe-to-comments' ); ?></h3>

			<p>
			<?php _e( 'Enter an e-mail address to view its subscriptions or undo a block.', 'subscribe-to-comments' ); ?>
			</p>

			<form name="getemail" method="post" action="<?php echo clean_url( _stc()->form_action ); ?>">
			<input type="hidden" name="ref" value="<?php echo _stc()->ref; ?>" />

			<p>
			<input name="email" type="text" id="email" size="40" />
			<input type="submit" class="button-secondary" value="<?php _e( 'Search &raquo;', 'subscribe-to-comments' ); ?>" />
			</p>
			</form>

<?php if ( !$_REQUEST['email'] ) : ?>
			<?php if ( !$_REQUEST['showallsubscribers'] ) : ?>
				<h3><?php _e( 'Top Subscriber List', 'subscribe-to-comments' ); ?></h3>
			<?php else : ?>
				<h3><?php _e( 'Subscriber List', 'subscribe-to-comments' ); ?></h3>
			<?php endif; ?>

<?php
			$stc_limit = ( !$_REQUEST['showallsubscribers'] ) ? 'LIMIT 25' : '';
			if ( _stc()->is_multisite() ) {
				$all_pm_subscriptions = $wpdb->get_results( "SELECT DISTINCT email, count(post_id) as ccount FROM _stc()->ms_table WHERE status = 'active' GROUP BY email ORDER BY ccount DESC $stc_limit" );
			} else {
				$all_pm_subscriptions = $wpdb->get_results( "SELECT DISTINCT LCASE(meta_value) as email, count(post_id) as ccount FROM $wpdb->postmeta WHERE meta_key = '_sg_subscribe-to-comments' GROUP BY email ORDER BY ccount DESC $stc_limit" );
			}
			$all_subscriptions = array();

			foreach ( (array) $all_pm_subscriptions as $sub ) {
				if ( !isset( $all_subscriptions[$sub->email] ) )
					$all_subscriptions[$sub->email] = (int) $sub->ccount;
				else
					$all_subscriptions[$sub->email] += (int) $sub->ccount;
			}

if ( !$_REQUEST['showallsubscribers'] ) : ?>
	<p><a href="<?php echo clean_url( esc_attr( add_query_arg( 'showallsubscribers', '1', _stc()->form_action ) ) ); ?>"><?php _e( 'Show all subscribers', 'subscribe-to-comments' ); ?></a></p>
<?php elseif ( !$_REQUEST['showccfield'] ) : ?>
	<p><a href="<?php echo add_query_arg( 'showccfield', '1' ); ?>"><?php _e( 'Show list of subscribers in <code>CC:</code>-field format (for bulk e-mailing)', 'subscribe-to-comments' ); ?></a></p>
<?php else : ?>
	<p><a href="<?php echo clean_url( _stc()->form_action ); ?>"><?php _e( '&laquo; Back to regular view' ); ?></a></p>
	<p><textarea cols="60" rows="10"><?php echo implode( ', ', array_keys( $all_subscriptions ) ); ?></textarea></p>
<?php endif;


			if ( $all_subscriptions ) {
				if ( !$_REQUEST['showccfield'] ) {
					echo "<ul>\n";
					foreach ( (array) $all_subscriptions as $email => $ccount ) {
						$enc_email = urlencode( $email );
						echo "<li>($ccount) <a href='" . clean_url( _stc()->form_action . "&email=$enc_email" ) . "'>" . wp_specialchars( $email ) . "</a></li>\n";
					}
					echo "</ul>\n";
				}
?>
				<h3><?php _e( 'Top Subscribed Posts', 'subscribe-to-comments' ); ?></h3>
				<?php
				if ( _stc()->is_multisite() ) {
					$top_subscribed_posts = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT post_id, count( distinct post_id ) as ccount FROM _stc()->ms_table WHERE status = 'active' AND blog_id = %s ORDER BY ccount DEST LIMIT 25", $blog_id ) );
				} else {
					$top_subscribed_posts = $wpdb->get_results( "SELECT DISTINCT post_id, count(distinct meta_value) as ccount FROM $wpdb->postmeta WHERE meta_key = '_sg_subscribe-to-comments' GROUP BY post_id ORDER BY ccount DESC LIMIT 25" );
				}
				$all_top_posts = array();

				foreach ( (array) $top_subscribed_posts as $pid ) {
					if ( !isset( $all_top_posts[$pid->post_id] ) )
						$all_top_posts[$pid->post_id] = (int) $pid->ccount;
					else
						$all_top_posts[$pid->post_id] += (int) $pid->ccount;
				}

				arsort( $all_top_posts );

				echo "<ul>\n";
				foreach ( $all_top_posts as $pid => $ccount ) {
					echo "<li>($ccount) <a href='" . get_permalink( $pid ) . "'>" . get_the_title( $pid ) . "</a></li>\n";
				}
				echo "</ul>";
				?>

	<?php } ?>

<?php endif; ?>

	<?php } ?>

	<?php if ( count( $postlist ) > 0 && is_array( $postlist ) ) { ?>


<script type="text/javascript">
<!--
function checkAll(form) {
	for ( i = 0, n = form.elements.length; i < n; i++ ) {
		if ( form.elements[i].type == "checkbox" ) {
			if ( form.elements[i].checked == true )
				form.elements[i].checked = false;
			else
				form.elements[i].checked = true;
		}
	}
}
//-->
</script>

			<h3><?php _e( 'Subscriptions', 'subscribe-to-comments' ); ?></h3>

				<p>
				<?php printf( __( '<strong>%s</strong> is subscribed to the posts listed below. To unsubscribe to one or more posts, click the checkbox next to the title, then click "Remove Selected Subscription(s)" at the bottom of the list.', 'subscribe-to-comments' ), _stc()->email ); ?>
				</p>

				<form name="removeSubscription" id="removeSubscription" method="post" action="<?php echo clean_url( _stc()->form_action ); ?>">
				<input type="hidden" name="removesubscrips" value="removesubscrips" />
	<?php _stc()->hidden_form_fields(); ?>

				<ol>
				<?php $i = 0;
				foreach ( $postlist as $pl ) { $i++; ?>
					<li><label for="subscrip-<?php echo $i; ?>"><input id="subscrip-<?php echo $i; ?>" type="checkbox" name="subscrips[]" value="<?php echo $pl[0] .'-'. $pl[1]; ?>" /> <?php echo _stc()->entry_link( $pl[0], $pl[1] ); ?></label></li>
				<?php } ?>
				</ol>

				<p>
				<a href="javascript:;" onclick="checkAll(document.getElementById('removeSubscription')); return false; "><?php _e( 'Invert Checkbox Selection', 'subscribe-to-comments' ); ?></a>
				</p>

				<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Remove Selected Subscription(s) &raquo;', 'subscribe-to-comments' ); ?>" />
				</p>
				</form>

	</div>

	<div class="wrap">
	<h2><?php _e( 'Advanced Options', 'subscribe-to-comments' ); ?></h2>


			<h3><?php _e( 'Block All Notifications', 'subscribe-to-comments' ); ?></h3>

				<form name="blockemail" method="post" action="<?php echo clean_url( _stc()->form_action ); ?>">
				<input type="hidden" name="blockemail" value="blockemail" />
	<?php _stc()->hidden_form_fields(); ?>

				<p>
				<?php printf( __( 'If you would like <strong>%s</strong> to be blocked from receiving any notifications from this site, click the button below.  This should be reserved for cases where someone is signing you up for notifications without your consent.', 'subscribe-to-comments' ), _stc()->email ); ?>
				</p>

				<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Block Notifications &raquo;', 'subscribe-to-comments' ); ?>" />
				</p>
				</form>



			<h3><?php _e( 'Change E-mail Address', 'subscribe-to-comments' ); ?></h3>

				<form name="changeemailrequest" method="post" action="<?php echo clean_url( _stc()->form_action ); ?>">
				<input type="hidden" name="changeemailrequest" value="changeemailrequest" />
	<?php _stc()->hidden_form_fields(); ?>

				<p>
				<?php printf( __( 'If you would like to change the e-mail address for your subscriptions, enter the new address below.  You will be required to verify this request by clicking a special link sent to your current address (<strong>%s</strong>).', 'subscribe-to-comments' ), _stc()->email ); ?>
				</p>

				<p>
				<?php _e( 'New E-mail Address:', 'subscribe-to-comments' ); ?>
				<input name="new_email" type="text" id="new_email" size="40" />
				<input type="submit" name="submit" class="button-secondary" value="<?php _e( 'Change E-mail Address &raquo;', 'subscribe-to-comments' ); ?>" />
				</p>
				</form>


			<?php } ?>
	<?php } //end if not in do not mail ?>
	</div>

	<?php if ( _stc()->standalone ) : ?>
	<?php if ( !_stc()->use_wp_style ) :
	echo _stc()->after_manager;

	if ( !empty( _stc()->sidebar ) )
		@include_once( _stc()->sidebar );
	if ( !empty( _stc()->footer ) )
		@include_once( _stc()->footer );
	?>
	<?php else : ?>
	</body>
	</html>
	<?php endif; ?>
	<?php endif; ?>


<?php die(); // stop WP from loading ?>
<?php }

// Bootstrap the whole thing
_stc();

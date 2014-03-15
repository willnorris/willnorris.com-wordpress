<?php
require_once('admin.php');

$parent_file = 'edit.php';
$submenu_file = 'edit.php';

wp_reset_vars(array('action', 'safe_mode', 'withcomments', 'posts', 'content', 'edited_post_title', 'comment_error', 'profile', 'trackback_url', 'excerpt', 'showcomments', 'commentstart', 'commentend', 'commentorder'));

if ( isset( $_POST['deletepost'] ) )
	$action = 'delete';

switch($action) {
case 'postajaxpost':
case 'post':
	check_admin_referer('add-post');

	$post_ID = 'post' == $action ? write_post() : edit_post();

	// Redirect.
	if ( !empty( $_POST['mode'] ) ) {
	switch($_POST['mode']) {
		case 'bookmarklet':
			$location = $_POST['referredby'];
			break;
		case 'sidebar':
			$location = 'sidebar.php?a=b';
			break;
		default:
			$location = 'post-new.php';
			break;
		}
	} else {
		$location = "post-new.php?posted=$post_ID";
	}

	if ( isset( $_POST['save'] ) )
		$location = "post.php?action=edit&post=$post_ID";

	if ( empty( $post_ID ) )
		$location = 'post-new.php';

	wp_redirect( $location );
	exit();
	break;

case 'edit':
	$title = __('Edit');
	$editing = true;

	if ( empty( $_GET['post'] ) ) {
		wp_redirect("post.php");
		exit();
	}
	$post_ID = $p = (int) $_GET['post'];
	$post = get_post($post_ID);

	if ( empty($post->ID) ) wp_die( __("You attempted to edit a post that doesn't exist. Perhaps it was deleted?") );

	if ( 'page' == $post->post_type ) {
		wp_redirect("page.php?action=edit&post=$post_ID");
		exit();
	}

	wp_enqueue_script('post');
	if ( user_can_richedit() )
		wp_enqueue_script('editor');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('media-upload');
	if ( $last = wp_check_post_lock( $post->ID ) ) {
		$last_user = get_userdata( $last );
		$last_user_name = $last_user ? $last_user->display_name : __('Somebody');
		$message = sprintf( __( 'Warning: %s is currently editing this post' ), wp_specialchars( $last_user_name ) );
		$message = str_replace( "'", "\'", "<div class='error'><p>$message</p></div>" );
		add_action('admin_notices', create_function( '', "echo '$message';" ) );
	} else {
		wp_set_post_lock( $post->ID );
		wp_enqueue_script('autosave');
	}

	require_once('admin-header.php');

	if ( !current_user_can('edit_post', $post_ID) )
		die ( __('You are not allowed to edit this post.') );

	$post = get_post_to_edit($post_ID);

	include('edit-form-advanced.php');

	break;

case 'editattachment':
	$post_id = (int) $_POST['post_ID'];

	check_admin_referer('update-attachment_' . $post_id);

	// Don't let these be changed
	unset($_POST['guid']);
	$_POST['post_type'] = 'attachment';

	// Update the thumbnail filename
	$newmeta = wp_get_attachment_metadata( $post_id, true );
	$newmeta['thumb'] = $_POST['thumb'];

	wp_update_attachment_metadata( $post_id, $newmeta );

case 'editpost':
	$post_ID = (int) $_POST['post_ID'];
	check_admin_referer('update-post_' . $post_ID);

	$post_ID = edit_post();
	$post = get_post($post_ID);

	if ( 'post' == $_POST['originalaction'] ) {
		if (!empty($_POST['mode'])) {
		switch($_POST['mode']) {
			case 'bookmarklet':
				$location = $_POST['referredby'];
				break;
			case 'sidebar':
				$location = 'sidebar.php?a=b';
				break;
			default:
				$location = 'post-new.php';
				break;
			}
		} else {
			$location = "post-new.php?posted=$post_ID";
		}

		if ( isset($_POST['save']) )
			$location = "post.php?action=edit&post=$post_ID";
	} else {
		$referredby = '';
		if ( !empty($_POST['referredby']) )
			$referredby = preg_replace('|https?://[^/]+|i', '', $_POST['referredby']);
		$referer = preg_replace('|https?://[^/]+|i', '', wp_get_referer());

		if ( isset($_POST['save']) && ( 'draft' == $post->post_status || 'pending' == $post->post_status ) ) {
			$location = "post.php?action=edit&post=$post_ID";
		} elseif ( isset($_POST['save']) && (empty($referredby) || $referredby == $referer) ) {
			$location = "post.php?action=edit&post=$post_ID";
		} elseif (isset($_POST['addmeta']) && $_POST['addmeta']) {
			$location = add_query_arg( 'message', 2, wp_get_referer() );
			$location = explode('#', $location);
			$location = $location[0] . '#postcustom';
		} elseif (isset($_POST['deletemeta']) && $_POST['deletemeta']) {
			$location = add_query_arg( 'message', 3, wp_get_referer() );
			$location = explode('#', $location);
			$location = $location[0] . '#postcustom';
		} elseif (!empty($referredby) && $referredby != $referer) {
			$location = $_POST['referredby'];
			if ( $_POST['referredby'] == 'redo' )
				$location = get_permalink( $post_ID );
			if (false !== strpos($location, 'edit.php') )
				$location = add_query_arg('posted', $post_ID, $location);
		} elseif ( isset($_POST['publish']) ) {
			$location = "post-new.php?posted=$post_ID";
		} elseif ($action == 'editattachment') {
			$location = 'attachments.php';
		} else {
			$location = "post.php?action=edit&post=$post_ID";
		}
	}

	wp_redirect($location); // Send user on their way while we keep working

	exit();
	break;

case 'delete':
	$post_id = (isset($_GET['post']))  ? intval($_GET['post']) : intval($_POST['post_ID']);
	check_admin_referer('delete-post_' . $post_id);

	$post = & get_post($post_id);

	if ( !current_user_can('delete_post', $post_id) )
		wp_die( __('You are not allowed to delete this post.') );

	if ( $post->post_type == 'attachment' ) {
		if ( ! wp_delete_attachment($post_id) )
			wp_die( __('Error in deleting...') );
	} else {
		if ( !wp_delete_post($post_id) )
			wp_die( __('Error in deleting...') );
	}

	$sendback = wp_get_referer();
	if (strpos($sendback, 'post.php') !== false) $sendback = get_option('siteurl') .'/wp-admin/post-new.php';
	elseif (strpos($sendback, 'attachments.php') !== false) $sendback = get_option('siteurl') .'/wp-admin/attachments.php';
	$sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);
	wp_redirect($sendback);
	exit();
	break;

default:
	wp_redirect('edit.php');
	exit();
	break;
} // end switch
include('admin-footer.php');
?>

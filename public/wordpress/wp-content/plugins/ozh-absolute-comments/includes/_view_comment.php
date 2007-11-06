<?php
/*
Part of Plugin: Absolute Comments
*/

$comment = $wp_ozh_cqr['comment'];

if (!function_exists('wp_ozh_cqr_take_over')) die('You cannot do this.');


// Echo the comment. Basically a rewrite of _wp_comment_list_item
function wp_ozh_cqr_echo_comment( $id ) {
	global $authordata, $comment, $wpdb;
	$id = (int) $id;
	$comment = get_comment( $id );
	$class = '';
	$authordata = get_userdata($wpdb->get_var("SELECT post_author FROM $wpdb->posts WHERE ID = $comment->comment_post_ID"));
	$comment_status = wp_get_comment_status($comment->comment_ID);
	if ( 'unapproved' == $comment_status )
		$class .= ' unapproved';
	echo "<div id='comment-$comment->comment_ID' class='$class' style='padding:0.3em 1em'>";
?>
<p id="thiscomment"><?php echo wp_ozh_cqr__('From: '); ?><strong><?php comment_author(); ?></strong> <?php if ($comment->comment_author_email) { ?>| <?php comment_author_email_link() ?> <?php } if ($comment->comment_author_url && 'http://' != $comment->comment_author_url) { ?> | <?php comment_author_url_link() ?> <?php } ?>| <?php _e('IP:') ?> <a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php comment_author_IP() ?>"><?php comment_author_IP() ?></a></p>

<div class="alternate" style="padding:0.1em 1em">
<?php comment_text() ?>
</div>

<p><?php comment_date(__('M j, g:i A'));  ?> &#8212; [
<?php

$return_to= '_wp_http_referer='.get_bloginfo('wpurl').'/wp-admin/edit-comments.php';

if ( current_user_can('edit_post', $comment->comment_post_ID) ) {
	echo " <a href='comment.php?action=editcomment&amp;c=".$comment->comment_ID."'>" .  __('Edit') . '</a>';
	echo ' | <a href="' . wp_nonce_url('comment.php?action=deletecomment&amp;p=' . $comment->comment_post_ID . '&amp;c=' . $comment->comment_ID ."&$return_to", 'delete-comment_' . $comment->comment_ID) . '" onclick="return deleteSomething( \'comment\', ' . $comment->comment_ID . ', \'' . js_escape(sprintf(__("You are about to delete this comment by '%s'.\n'Cancel' to stop, 'OK' to delete."), $comment->comment_author)) . "', theCommentList );\">" . __('Delete') . '</a> ';
	if ( ('none' != $comment_status) && ( current_user_can('moderate_comments') ) ) {
		echo '<span class="unapprove"> | <a href="' . wp_nonce_url('comment.php?action=unapprovecomment&amp;p=' . $comment->comment_post_ID . '&amp;c=' . $comment->comment_ID ."&$return_to", 'unapprove-comment_' . $comment->comment_ID) . '" onclick="return dimSomething( \'comment\', ' . $comment->comment_ID . ', \'unapproved\', theCommentList );">' . __('Unapprove') . '</a> </span>';
		echo '<span class="approve"> | <a href="' . wp_nonce_url('comment.php?action=approvecomment&amp;p=' . $comment->comment_post_ID . '&amp;c=' . $comment->comment_ID ."&$return_to", 'approve-comment_' . $comment->comment_ID) . '" onclick="return dimSomething( \'comment\', ' . $comment->comment_ID . ', \'unapproved\', theCommentList );">' . __('Approve') . '</a> </span>';
	}
	echo " | <a href=\"" . wp_nonce_url("comment.php?action=deletecomment&amp;dt=spam&amp;p=" . $comment->comment_post_ID . "&amp;c=" . $comment->comment_ID ."&$return_to", 'delete-comment_' . $comment->comment_ID) . "\" onclick=\"return deleteSomething( 'comment-as-spam', $comment->comment_ID, '" . js_escape(sprintf(__("You are about to mark as spam this comment by '%s'.\n'Cancel' to stop, 'OK' to mark as spam."), $comment->comment_author))  . "', theCommentList );\">" . __('Spam') . "</a> ";
}
$post = get_post($comment->comment_post_ID);
$post_title = wp_specialchars( $post->post_title, 'double' );
$post_title = ('' == $post_title) ? "# $comment->comment_post_ID" : $post_title;
?>
 ] &#8212; <a href="<?php echo get_permalink($comment->comment_post_ID); ?>"><?php echo $post_title; ?></a></p>
		</div>
<?php
}

wp_ozh_cqr_echo_comment( $comment->comment_ID );

?>
<?php 
/* 
Plugin Name: Edit Comments
Version: 0.3 beta
Plugin URI: http://code.jalenack.com
Description: Allows users to edit their comments for up to 30 minutes after posting. To change the time limit, configure it in this plugin file
Author: Andrew Sutherland
Author URI: http://code.jalenack.com
*/

$jal_minutes = 30; 
// number of minutes that users should be allowed to edit their comment.
// for the last 5 minutes before the time is up, the Edit link will not show 
// This is so that people won't click unknowingly with 10 seconds before the time limit, and then be frustrated when their edit is disallowed.
// default: 30

/* 
No need to edit below here
=================================================
*/

// template tag for the edit comment link
function jal_edit_comment_link($text = 'Edit This', $before = '', $after = '', $editing_message = '<strong>EDITING</strong>') {
	global $comment, $jal_minutes, $user_ID, $post;
    
    $time_ago = time() - strtotime($comment->comment_date_gmt . ' GMT');

    if (user_can_edit_post_comments($user_ID, $post->ID))
        edit_comment_link($text, $before, $after);
        
	elseif ($comment->comment_author_IP == $_SERVER['REMOTE_ADDR']
	 // show the link for the allotted time - 5 minutes
	 && $time_ago < 60 * ($jal_minutes - 5)
	 // don't show edit links if they're already editing
	 && !isset($_GET['jal_edit_comments'])) {
	 
	   echo "\n{$before}<a href='".add_query_arg('jal_edit_comments', "$comment->comment_ID")."#commentform' title='Edit this Comment'>{$text}</a>{$after}\n";
	}
	
	// When the author has clicked Edit, the comment they are editing is highlighted as EDITING
	elseif (isset($_GET['jal_edit_comments']) && $comment->comment_ID == $_GET['jal_edit_comments'])
		echo $editing_message;

}

function jal_comment_content ($jal_comment) {
    echo format_to_edit(apply_filters('comment_edit_pre', $jal_comment->comment_content));
}

function jal_edit_comment_init() {
    global $jal_minutes, $wpdb;
 	$jal_comment = $wpdb->get_row("SELECT comment_content, comment_author_IP, comment_date_gmt FROM $wpdb->comments WHERE comment_ID = ".$_GET['jal_edit_comments']);
    $time_ago = time() - strtotime($jal_comment->comment_date_gmt . ' GMT');
 	
	echo '<p><input type="hidden" name="jal_edit_this" value="'.$_GET['jal_edit_comments'].'" /></p>';
 	
 	if ($_SERVER['REMOTE_ADDR'] != $jal_comment->comment_author_IP || $time_ago >= 60 * $jal_minutes) :
 		echo "<p><strong>You aren't allowed to edit this comment, either because you didn't write it or you passed the {$jal_minutes} minute time limit.</strong></p>";
 		echo "</form>";
 		// get out of this template
 		return false;
 	endif;
 	
 	return $jal_comment;
}

// make the edit
function jal_do_edit () {
	global $wpdb, $jal_minutes;
	$comment_post_ID = (int) $_POST['jal_edit_this'];

	$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID = ".$comment_post_ID);
    
    $time_ago = time() - strtotime($comment->comment_date_gmt . ' GMT');

	// make sure its still the right person
	if ($comment->comment_author_IP == $_SERVER['REMOTE_ADDR']
	 && $time_ago < 60 * $jal_minutes) {
		// escaping and shiz. Same as wp-admin comment editing
		$content = apply_filters('comment_save_pre', $_POST['comment']);
		// update the comment contents
		$wpdb->query("UPDATE $wpdb->comments SET comment_content = '{$content}' WHERE comment_ID = ".$comment_post_ID);
		// remove query arg from referer
		$location = add_query_arg('jal_edit_comments', '', $_SERVER['HTTP_REFERER']);
		// take out the #commentform bit
		$location = str_replace('#commentform', '', $location);
		// direct them to the comment they edited
		$location .= "#comment-".$comment_post_ID;
		// off we go!
		header("location: {$location}");
		die();
	} else
		die("You aren't allowed to edit this comment, either because you didn't write it or you passed the {$jal_minutes} minute time limit.");
}

// hook into wordpress before wp-comments-post.php has a chance to add a new comment
if (isset($_POST['jal_edit_this']))
	add_action('init', 'jal_do_edit');

?>
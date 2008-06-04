<?php
/*
Part of Plugin: Absolute Comments
*/
$comment = $wp_ozh_cqr['comment'];

if (!function_exists('wp_ozh_cqr_take_over') or !$comment) die('You cannot do this...');

?>
<script type="text/javascript">
function focusit() { // focus on first input field
	document.post.content.focus();
}
addLoadEvent(focusit);
</script>
<style>
#editor-toolbar {
	display:none; /* don't want to bother with this. kthxbye. */
}
.wrap h2 {
	margin-bottom:0.5em;
}
</style>
<div id="poststuff">
<form name="post" action="" method="post" id="post">
<?php wp_nonce_field('ozh-quickreply') ?>
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID; ?>" />
<input type="hidden" name="cqr_action" value='reply'/>
<input type='hidden' name='comment_ID' value="<?php echo $comment->comment_ID; ?>" />
<input type='hidden' name='comment_post_ID' value="<?php echo $comment->comment_post_ID; ?>" />
<div id="postdiv"  class="postarea">
<?php 
	$comment_status = wp_get_comment_status($comment->comment_ID);
	if ($comment_status !== 'approved') $cqr_status = '('.wp_ozh_cqr__('currently flagged as'). " <span style='color:red'>$comment_status</span>)";
	echo '<h3>'.wp_ozh_cqr__('Your reply to this comment')." <a href='#thiscommentwrap'>&darr;</a> $cqr_status\n</h3>
	<input type='hidden' name='cqr_simpleform' value='0'/>
	";

	// a little takeover of your options ?
	if ($wp_ozh_cqr['editor_rows']) {
		$old_rows = get_option('default_post_edit_rows');
		update_option('default_post_edit_rows',$wp_ozh_cqr['editor_rows']);	
	}
	if ($wp_ozh_cqr['prefill_reply']) {
		$prefill = str_replace(array('%%link%%', '%%name%%'), array("#comment-{$comment->comment_ID}", $comment->comment_author), $wp_ozh_cqr['prefill_reply']);
	} else {
		$prefill = '';
	}
	the_editor($prefill, 'content');
?>
</div>
<p class="submit" id="submitp" style="border:0px; padding:0 0 0 20px;">
<input type="submit" name="postcomment" id="postcomment" value="<?php echo __('Reply to Comment &raquo;','absolutecomments'); ?>" style="font-weight: bold;" tabindex="6" />
<?php
if ($wp_ozh_cqr['show_threaded']) { ?>
<script type="text/javascript">
function cqr_mark_threaded() {
document.getElementById('cqr_threaded').value = 1;
}
var content = document.createElement('span');
content.innerHTML = '<input type="hidden" id="cqr_threaded" name="cqr_threaded" value="0"/><input type="submit" onclick="javascript:cqr_mark_threaded()" name="postcomment_th" id="postcomment_th" value="<?php echo wp_ozh_cqr__('Thread Reply &raquo;', true); ?>" tabindex="7" />';
document.getElementById('submitp').appendChild(content);
</script>
<?php } ?>
</p>
</form>
</div>
<?php
	// Revert option to original value
	if ($old_rows)
		update_option('default_post_edit_rows',$old_rows);
?>
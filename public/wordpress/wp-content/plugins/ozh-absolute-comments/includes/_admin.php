<?php
/*
Part of Plugin: Absolute Comments
*/

// Process POST vars
function wp_ozh_cqr_processforms() {
	global $wp_ozh_cqr;

	check_admin_referer('absolute-comments');
	
	// Sanitize before store
	$wp_ozh_cqr['editor_rows'] = intval($_POST['ozh_ac_editor']);
	$wp_ozh_cqr['show_icon'] = ($_POST['ozh_ac_icons'] == 'true' ? true : false );
	$wp_ozh_cqr['prefill_reply'] = attribute_escape($_POST['ozh_ac_prefill']);	
	$wp_ozh_cqr['show_threaded'] = ($_POST['ozh_ac_thread'] == 'true' ? true : false );	
	$wp_ozh_cqr['mail_promote'] = ($_POST['ozh_ac_mail'] == 'true' ? true : false );	
	$wp_ozh_cqr['viewall'] = ($_POST['ozh_ac_viewall'] == 'true' ? true : false );
	
	// Store
	update_option('ozh_absolutecomments',$wp_ozh_cqr);
	
	echo '
	<div class="updated fade" id="message"><p>Options <strong>saved</strong>.</p></div>
	';
}


// Print admin page
function wp_ozh_cqr_adminpage_print() {

	if (isset($_POST['ozh_absolute']) && ($_POST['ozh_absolute'] == 1) ) wp_ozh_cqr_processforms();

	global $wp_ozh_cqr, $wpdb;
	
	wp_ozh_cqr_adminpage_css();
	
	$editor_rows = $wp_ozh_cqr['editor_rows'];
	$icon = $wp_ozh_cqr['show_icon'];
	$prefill = stripslashes($wp_ozh_cqr['prefill_reply']);
	$thr = $wp_ozh_cqr['show_threaded'];
	$mail = $wp_ozh_cqr['mail_promote'];
	$viewall = $wp_ozh_cqr['viewall'];
	foreach (array('icon', 'thr', 'mail', 'viewall') as $wtf) {
		${"checked_${wtf}_on"} = ($$wtf ? ' checked="checked"' : '' );
		${"checked_${wtf}_off"} = ($$wtf ? '' : ' checked="checked"' ) ;
		// weeee, $$var ftw !
	}
	$lastcomment = ($wpdb->get_var("SELECT comment_ID FROM $wpdb->comments ORDER BY comment_date_gmt DESC LIMIT 0, 1"));
	
	echo <<<HTML
    <div class="wrap">
    <h2>Absolute Comments</h2>
    <form method="post" action="" id="ozh_ac">
HTML;
	wp_nonce_field('absolute-comments');
	echo <<<HTML
	<table class="form-table"><tbody>
	<input type="hidden" name="ozh_absolute" value="1"/>
    <input type="hidden" name="action" value="update">
    
	<tr><th scope="row">Editor Size</th>
	<td><input type="text" value="$editor_rows" name="ozh_ac_editor" size="1"> rows in the <a href="edit-comments.php?quick_reply=$lastcomment">advanced reply editor</a> (0 for default size)
	</td></tr>
	
	<tr><th scope="row">Cute icons</th>
	<td><input name="ozh_ac_icons" id="ozh_ac_icons_on" value="true" $checked_icon_on type="radio"><label for="ozh_ac_icons_on">Enabled</label><br/>
	<input name="ozh_ac_icons" id="ozh_ac_icons_off" value="false" $checked_icon_off type="radio"><label for="ozh_ac_icons_off">Disabled</label>
	</td></tr>
	
	<tr><th scope="row">"View All" Links</th>
	<td><input name="ozh_ac_viewall" id="ozh_ac_viewall_on" value="true" $checked_viewall_on type="radio"><label for="ozh_ac_viewall_on">Enabled</label><br/>
	<input name="ozh_ac_viewall" id="ozh_ac_viewall_off" value="false" $checked_viewall_off type="radio"><label for="ozh_ac_viewall_off">Disabled</label>
	</td></tr>
	
	<tr><th scope="row">Reply Prefill</th>
	<td><textarea name="ozh_ac_prefill" id="ozh_ac_prefill" rows="2" cols="50">$prefill</textarea><br/>
	Prefill your reply custom text containing the commenter's name and/or the comment permalink. Use tokens <code>%%name%%</code> and <code>%%link%%</code><br/>
	Click on a preset:<ul id="ozh_ac_preset"><li>@Joe: </li><li>Joe &raquo; </li><li><a href="#comment-12345">#</a>Joe&rarr; </li><li>Dear <strong>Joe</strong>,</li></ul>
	<div>Preview:<br/><span id="ozh_ac_replypreview"></span></div>
	</td></tr>
	
	<tr><th scope="row">Threaded Comment Support</th>
	<td><input name="ozh_ac_thread" id="ozh_ac_thread_on" value="true" $checked_thr_on type="radio"><label for="ozh_ac_thread_on">Enabled</label><br/>
	<input name="ozh_ac_thread" id="ozh_ac_thread_off" value="false" $checked_thr_off type="radio"><label for="ozh_ac_thread_off">Disabled</label><br/>
	<em>Note: this is not a feature in itself, you'll need a threading comments plugin/theme</em>
	</td></tr>
	
	<tr><th scope="row">Promote by Mail</th>
	<td><input name="ozh_ac_mail" id="ozh_ac_mail_on" value="true" $checked_mail_on type="radio"><label for="ozh_ac_mail_on">Enabled</label><br/>
	<input name="ozh_ac_mail" id="ozh_ac_mail_off" value="false" $checked_mail_off type="radio"><label for="ozh_ac_mail_off">Disabled</label><br/>
	Include a link to <a href="http://planetozh.com/blog/my-projects/absolute-comments-manager-instant-reply/">Absolute Comments</a> plugin page in your email notifications. That way, if you reply to commenters by email, you'll let them know about this great plugin!
	</td></tr>
	</tbody></table>
	
	<p class="submit"><input type="submit" value="Update Options &raquo;" /></p>

	</form>
	</div>
HTML;


	wp_ozh_cqr_adminpage_js();

}

function wp_ozh_cqr_adminpage_css() {
	echo <<<CSS
	<style type="text/css">
	#ozh_ac_preset {
		display:inline;
		padding:0;
	}
	#ozh_ac_preset li {
		display:inline;
		cursor:pointer;
		padding:0 1em;
	}
	#ozh_ac_preset li:hover {
		background:#eef;
	}
	#ozh_ac_replypreview {
		background:#fafafa;
	}
	</style>
	
	
CSS;


}

function wp_ozh_cqr_adminpage_js() {
	echo <<<JS
	<script type="text/javascript">
	jQuery(document).ready( function() {
		jQuery('.ozh_ac_help').tTips();
		jQuery('#ozh_ac_prefill').keyup(function(){
			jQuery('#ozh_ac_replypreview').html(ozh_ac_detokenize(jQuery(this).val()));
		});
		jQuery('#ozh_ac_preset li').click(function(){
			jQuery('#ozh_ac_prefill').val(ozh_ac_tokenize(jQuery(this).html()));
			jQuery('#ozh_ac_replypreview').html(jQuery(this).html());
			return false; // don't go to #comment-12345
		});
	});
	function ozh_ac_detokenize(str) {
		return str.replace('%%link%%', '#comment-12345').replace('%%name%%', 'Joe').replace("\\n",'<br/>');
	}
	function ozh_ac_tokenize(str) {
		return str.replace('#comment-12345', '%%link%%').replace('Joe', '%%name%%');
	}
	</script>
JS;

}

?>
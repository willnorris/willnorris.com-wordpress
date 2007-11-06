<?php
/*
Part of Plugin: Absolute Comments
*/

if (!function_exists('wp_ozh_cqr_take_over')) die('You cannot do this');

$cqr_blogurl = get_bloginfo('wpurl');
$cqr_plugindir = $wp_ozh_cqr['path'];

$cqr_replybutton = wp_ozh_cqr__('Reply to Comment &raquo;', true);
$cqr_replybutton_threaded = wp_ozh_cqr__('Thread Reply &raquo;', true);
$cqr_waitlink = wp_ozh_cqr__('Wait...', true);
$cqr_replylink = wp_ozh_cqr__('Reply', true);
$cqr_cancellink = wp_ozh_cqr__('Cancel', true);
$cqr_viewalllink = wp_ozh_cqr__('View All', true);
$cqr_replylink_title = wp_ozh_cqr__('Quick Reply', true);
$cqr_replylinkadv_title = wp_ozh_cqr__('Reply with advanced editor', true);
$cqr_viewalllink_title = wp_ozh_cqr__('View all comments for this post', true);
$cqr_postinglink = wp_ozh_cqr__('Posting comment...', true);
$cqr_error_duplicate = wp_ozh_cqr__('Ooops! Duplicate comment detected. Aborted.', true);
$cqr_error_toofast = wp_ozh_cqr__('Ooops! You are posting too quickly. Aborted.', true);
$cqr_error_any = wp_ozh_cqr__('Error (duplicate comment, or posting too quickly ?). Aborted', true);


ob_start();
wp_nonce_field('ozh-quickreply');
$cqr_nonce = ob_get_contents();
ob_end_clean();

global $text_direction;
if ($wp_ozh_cqr['show_icon']) {

	if ($text_direction != 'rtl') {
		$imgall = '';
		$imgreply = '';
		// Left To Right CSS styling
		echo <<<CSS
		<style type="text/css" media="screen">
		#cqr_homelink {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/reply.gif) 0 0 no-repeat;
		padding-left:20px;
		}
 		a.quickreplylink {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/reply.gif) 0 0 no-repeat;
		padding-left:20px;
		}
		a.quickreplycancel {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/cancel.gif) 0 0 no-repeat;
		padding-left:16px;
		}
		a.quickreplyall {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/allcomments.gif) 0 0 no-repeat;
		padding-left:25px;
		}
		</style>
CSS;
	} else {
		// Red Alert and Panic Everybody, we're on RTL. I can't style this :)
		$imgall = "<img class=\"cqr_icon\" src=\"$cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/allcomments.gif\"/>";
		$imgreply = "<img class=\"cqr_icon\" src=\"$cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/reply.gif\"/>";
		echo <<<CSS
		<style type="text/css" media="screen">
		.cqr_icon {
		vertical-align:sub;
		padding-left:2px;
		}
		</style>
CSS;
	}
}


// Prefilling comments
if ($wp_ozh_cqr['prefill_reply']) {
	$cqr_prefill = "var nick = jQuery(this).parent().parent().parent().find('p:first-child strong').html();\n";
	$cqr_prefill .= "var replytext = '${wp_ozh_cqr['prefill_reply']}';\n";
	$cqr_prefill .= "replytext = replytext.replace('%%link%%', '#comment-'+id);\n";
	$cqr_prefill .= "replytext = replytext.replace('%%name%%', nick);\n";
} else {
	$cqr_prefill = "var replytext = '';";
}

// Supporting threading if applicable
$cqr_threaded = !$wp_ozh_cqr['show_threaded'] ? "''" : <<<PRE
'<input type="hidden" id="cqr_threaded_'+id+'" name="cqr_threaded" value="0"/>'+
				'<input type="submit" onclick="javascript:cqr_mark_threaded('+id+')" value="$cqr_replybutton_threaded" />'
PRE;

// Display "All comments" links if applicable
if ( (isset($_GET['quick_reply']) && $_GET['quick_reply'] == 'view_all')
or strpos($_SERVER['REQUEST_URI'], 'edit.php') !== false ) {
	$cqr_allposts = '';
} else {
	$cqr_allposts_url = ($wp_ozh_cqr['allcomments_useWP']) ? 'edit.php?c=1&p=' : 'edit-comments.php?quick_reply=view_all&post_id=' ;
	$cqr_allposts = "jQuery(this).find('p:last').append('<span class=\"quickreplylinks\">&nbsp;[&nbsp;<a class=\"quickreplyall\" title=\"$cqr_viewalllink_title\" href=\"$cqr_blogurl/wp-admin/$cqr_allposts_url' + post + '\">${imgall}$cqr_viewalllink</a> ]</span>');" ;
}

// Add new replies above or after all comments depending on the page
if (strpos($_SERVER['REQUEST_URI'], 'edit.php') !== false ) {
	$cqr_addcomment = "jQuery('#the-comment-list').append(jQuery('#quickreplied').html());\n";
} else {
	$cqr_addcomment = "jQuery('#the-comment-list').prepend(jQuery('#quickreplied').html());\n";
}

echo <<<JS
<script type="text/javascript">
var cqr_footer = false;

jQuery(document).ready(function() {
	// add our secret container
	jQuery('body').append('<div id="quickreplied"></div>');
	jQuery('#quickreplied').hide();
	// add links and their ajaxy behaviors
	cqr_add_links();
	cqr_init_links();
	// Add some love to the footer
	cqr_add_footer();
})

// Remove everything this script adds via javascript
function cqr_destroy_links() {
	jQuery('#the-comment-list .quickreplylinks').remove();
	jQuery('#the-comment-list .quickreplydiv').remove();
}

// Reset links and divs
function cqr_reset_links() {
	cqr_destroy_links();
	cqr_add_links();
	cqr_init_links();	
}

// Add quick reply links, with and without ajax, and the reply divs
function cqr_add_links() {
	jQuery('#the-comment-list li[@id^=comment]').each(function() {
		var id = jQuery(this).attr('id');
		if (id) {
			cqr_footer = true;
			id = id.replace('comment-', '');
			var post = jQuery(this).find('p:last-child a[@href*=deletecomment]').attr('href').replace(/.*&?p=([^&]*).*/,function($0,$1){return $1;}); // post id
			jQuery(this).find('p:last').find('a:first').before('<span class=\"quickreplylinks\">&nbsp;<a class=\"quickreplylink\" id=\"quick_reply_'+id+'_'+post+'\" title=\"$cqr_replylink_title\" href=\"${cqr_blogurl}/wp-admin/edit-comments.php?quick_reply=' + id + '\">${imgreply}$cqr_replylink</a> <a class=\"quickreplyeditor\" title=\"$cqr_replylinkadv_title\" href=\"${cqr_blogurl}/wp-admin/edit-comments.php?quick_reply=' + id + '\">&raquo;</a>&nbsp;|&nbsp;</span>');
			$cqr_allposts
			jQuery(this).append('<div class=\"quickreplydiv\" id=\"div_quick_reply_'+id+'\"></div>');
		}
	});
}

// Add Ajax functions to quick reply links
function cqr_init_links() {
	var is_open = [];
	jQuery('a.quickreplylink').each(function() {
		var temp = jQuery(this).attr('id').replace('quick_reply_','');
		var id = temp.split('_')[0];
		is_open[id] = false;
		var post = temp.split('_')[1];
		jQuery(this).click(function(){
			if (is_open[id]) {
				jQuery('#div_quick_reply_'+id).html('');
				jQuery(this).html('$cqr_replylink').removeClass('quickreplycancel').addClass('quickreplylink').attr('title','$cqr_replylink');
				is_open[id] = false;
			} else {
				jQuery(this).html('$cqr_waitlink');
				${cqr_prefill}
				jQuery('#div_quick_reply_'+id).html('<style>textarea {width:100%;}</style>'+
				'<form name="post" action="javascript:cqr_store_reply('+id+')" method="post" id="post">'+
				'$cqr_nonce'+
				'<input type="hidden" name="user_ID" value="$user_ID" />' +
				'<input type="hidden" name="cqr_action" value="reply"/>' +
				'<input type="hidden" name="comment_ID" value="'+id+'" />' +
				'<input type="hidden" name="comment_post_ID" value="'+post+'" />'+
				'<fieldset style="clear: both;">'+
				'<div><p><textarea id="cqr_textarea_'+id+'" rows="6" cols="80" style="width:100%" name="content">'+replytext+'</textarea></p></div>'+
				'</fieldset>'+
				'<p class="submit"><input type="submit" value="$cqr_replybutton" style="font-weight: bold;" />'+
				$cqr_threaded+
				'</p>'+
				'</form>'				
				);
				jQuery(this).html('$cqr_cancellink').addClass('quickreplycancel').removeClass('quickreplylink').attr('title','$cqr_cancellink');
				jQuery('#cqr_textarea_'+id).focus();
				is_open[id] = true;
			}
			return false;
		});
	});
}

// Mark reply as threaded
function cqr_mark_threaded(id) {
	jQuery('#cqr_threaded_'+id).val(1);
}

// Store a comment into db
function cqr_store_reply(id) {
	var postarray = {};
	// Dynamically get all input fields. Hoooow cool :)
	jQuery('#div_quick_reply_'+id+' input').each(function(){
		postarray[ jQuery(this).attr('name') ] = jQuery(this).val();
	});
	postarray['action'] = 'cqr_ajaxstore';
	postarray['content'] = jQuery('#div_quick_reply_'+id+' textarea').val();
	jQuery.post(
		'${cqr_blogurl}/wp-content/plugins/${cqr_plugindir}/includes/_save_comment.php',
		postarray,
		function (xml) { cqr_get_reply(xml); }
	);
	jQuery('#div_quick_reply_'+id).html('<p><em>$cqr_postinglink</em></p>');
}
	
// Get XML response and 
function cqr_get_reply(xml) {
	if (typeof(xml) == 'string') {
		// Error mode. We're not supposed to get a String, so it means WP returned an HTML error page
		if (/Duplicate/.test(xml)) {
			alert ('$cqr_error_duplicate');
		} else if (/too quickly/.test(xml)) {
			alert ('$cqr_error_toofast');
		} else {
			// Maybe we're not catching the error message because of localization ?
			alert ('$cqr_error_any');
		}
		cqr_reset_links();
		return;
	}
	var replyto = jQuery('replyto',xml).text(); // the id of the comment we replied to
	var id = jQuery('id',xml).text(); // the id of the reply we posted
	var post = jQuery('post',xml).text();
	jQuery('#div_quick_reply_'+replyto).html('');
	// Load latest comment into our secret container, then display its content into a new <li> in the-comment-list
	jQuery('#quickreplied').load('${cqr_blogurl}/wp-content/plugins/${cqr_plugindir}/includes/_latest_comment.php?id='+id,
		{},
		function() {
			$cqr_addcomment
			jQuery('#comment-'+id).hide().slideToggle("slow",function(){
				jQuery(this).css('display','list-item');
				jQuery(this).css('border','red');
			});
			cqr_li_bgcolor();
			cqr_reset_links();
		}
	);
}

// Give the comment list items the nice alternate look
function cqr_li_bgcolor() {
	// get li without "display:none" (these are newly deleted comment), and alternate the class (the .filter(':even/odd') bit)
	jQuery('#the-comment-list li[@id^=comment]').filter(function(){ return jQuery(this).css('display') != 'none' ;}).filter(':even').css('backgroundColor','').addClass('alternate');
	jQuery('#the-comment-list li[@id^=comment]').filter(function(){ return jQuery(this).css('display') != 'none' ;}).filter(':odd').css('backgroundColor','').removeClass('alternate');
}

// Insert some stuff to the footer
function cqr_add_footer() {
	if (cqr_footer)
		jQuery('#footer').before('<div class="wrap" id="cqr_footer" style="background:#eee;padding:0.5em 1em;"><a id="cqr_homelink" href="http://planetozh.com/blog/my-projects/absolute-comments-manager-instant-reply/">Absolute Comments ${imgreply}</a> by Ozh. <span id="cqr_likeit">Like it?</span> Check my <a id="cqr_pluginslink" href="http://planetozh.com/blog/my-projects/">other plugins</a>. Love it? <a id="cqr_beerlink" href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=ozh%40planetozh.com&item_name=PlanetOzh+WordPress+Absolute+Comments&no_note=1&currency_code=USD&tax=0&bn=PP-DonationsBF">Buy me a beer</a> !</div>');
}

</script>

JS;
?>
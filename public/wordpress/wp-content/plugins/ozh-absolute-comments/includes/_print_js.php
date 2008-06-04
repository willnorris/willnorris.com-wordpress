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
$cqr_editlink = wp_ozh_cqr__('Edit', true);
$cqr_cancellink = wp_ozh_cqr__('Cancel', true);
$cqr_viewalllink = wp_ozh_cqr__('View All', true);
$cqr_replylink_title = wp_ozh_cqr__('Quick Reply', true);
$cqr_editlink_title = wp_ozh_cqr__('Edit this comment', true);
$cqr_replylinkadv_title = wp_ozh_cqr__('Reply with advanced editor', true);
$cqr_viewalllink_title = wp_ozh_cqr__('View all comments for this post', true);
$cqr_postinglink = wp_ozh_cqr__('Posting comment...', true);
$cqr_error_duplicate = wp_ozh_cqr__('Ooops! Duplicate comment detected. Aborted.', true);
$cqr_error_toofast = wp_ozh_cqr__('Ooops! You are posting too quickly. Aborted.', true);
$cqr_error_any = wp_ozh_cqr__('Error (duplicate comment, or posting too quickly ?). Aborted', true);

$cqr_nonce = wp_nonce_field('ozh-quickreply', '_wpnonce', true, false);

// What page is this? Adjust JS parsing, and display "All comments" & "Reply" links if applicable
if ( strpos($_SERVER['REQUEST_URI'], 'edit-comments.php') !== false && !isset($_GET['quick_reply'])) {
	$cqr_first_td = 2;
	$cqr_second_td = 4;
} else {
	$cqr_first_td = 1;
	$cqr_second_td = 3;
}

if ( strpos($_SERVER['REQUEST_URI'], 'edit-comments.php') !== false  && $wp_ozh_cqr['viewall']) {
	$cqr_allposts = "var _viewall = '<span class=\"quickreplylinks\"><a class=\"quickreplyall\" id=\"quick_viewall_'+id+'_'+post+'\" title=\"$cqr_viewalllink_title\" href=\"$cqr_blogurl/wp-admin/edit.php?p=' + post + '\">${imgall}$cqr_viewalllink</a></span>';" ;
	$cqr_doviewall = "var doviewall = true;";
} else {
	$cqr_allposts = 'var _viewall = "";';
	$cqr_doviewall = "var doviewall = false;";
}

if (isset($_GET['quick_reply'])) {
	$cqr_reply = "var _reply = '';";
} else {
	$cqr_reply = "var _reply = '<span class=\"quickreplylinks\"><a class=\"quickreplylink\" id=\"quick_reply_'+id+'_'+post+'\" title=\"$cqr_replylink_title\" href=\"${cqr_blogurl}/wp-admin/edit-comments.php?quick_reply=' + id + '\">${imgreply}$cqr_replylink</a> <a class=\"quickreplyeditor\" title=\"$cqr_replylinkadv_title\" href=\"${cqr_blogurl}/wp-admin/edit-comments.php?quick_reply=' + id + '\">&raquo;</a></span>';";
}

// Add new replies above or after all comments depending on the page
if (strpos($_SERVER['REQUEST_URI'], 'edit.php') !== false ) {
	$cqr_addcomment = "jQuery('#the-comment-list').append(jQuery('#quickreplied').html());\n";
} else {
	$cqr_addcomment = "jQuery('#the-comment-list').prepend(jQuery('#quickreplied').html());\n";
}

// Reverse comment order on edit.php?
if (strpos($_SERVER['REQUEST_URI'], 'edit.php') !== false ) {
	$cqr_reverse_comments = "cqr_reverse_elements('#the-comment-list tr','#the-comment-list');";
} else {
	$cqr_reverse_comments = "";
}



echo <<<CSS
	<style type="text/css" media="screen">
	.quickreplydiv {
		background:#EAF3FA;
		position:absolute;
		z-index:9999;
	}
	.quickreplydiv button {
		padding:3px;
		margin-left:3em;
	}
	.quickreplydivpop {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/shadow.png) bottom right no-repeat;
		padding:0 6px 6px 0;
	}
	.quickreplydivcontent {
		background:#EAF3FA;
		border:1px solid #EAF3FA;
		border-top:1px solid #ddd;
		border-left:1px solid #ddd;
		padding-right:1em;
	}
	span.quickreplylinks {
		display:block;
	}
	th.action-links, td.action-links {
		text-align:left;
	}
	span.approve {
		color:#fff;
	}
CSS;

global $text_direction;

if ($wp_ozh_cqr['show_icon']) {

	if ($text_direction != 'rtl') {
		$imgall = '';
		$imgreply = '';
		// Left To Right CSS styling
		echo <<<CSS
		#cqr_homelink {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/reply.gif) 0 0 no-repeat;
		padding-left:25px;
		}
 		a.quickreplylink {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/reply.gif) 4px 0 no-repeat;
		padding-left:25px;
		}
 		a.quickreplyeditlink {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/edit.gif) 4px 0 no-repeat;
		padding-left:25px;
		}
		a.quickreplycancel {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/cancel.gif) 6px 0 no-repeat;
		padding-left:25px;
		}
		a.quickreplyall {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/allcomments.gif) 0 0 no-repeat;
		padding-left:25px;
		}
 		span.spam a {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/spam.gif) 4px 0 no-repeat;
		padding-left:25px;
		}
 		span.unapprove a {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/unapprove.gif) 4px 0 no-repeat;
		padding-left:25px;
		}
 		span.approve a {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/approve.gif) 4px 0 no-repeat;
		padding-left:25px;
		}
 		span.delete a {
		background:transparent url($cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/delete.gif) 4px 0 no-repeat;
		padding-left:25px;
		}
CSS;
	} else {
		// Red Alert and Panic Everybody, we're on RTL. I can't style this :)
		$imgall = "<img class=\"cqr_icon\" src=\"$cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/allcomments.gif\"/>";
		$imgreply = "<img class=\"cqr_icon\" src=\"$cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/reply.gif\"/>";
		$imgedit = "<img class=\"cqr_icon\" src=\"$cqr_blogurl/wp-content/plugins/$cqr_plugindir/images/edit.gif\"/>";
		echo <<<CSS
		.cqr_icon {
		vertical-align:sub;
		padding-left:2px;
		}
CSS;
	}
}

echo <<<CSS
	</style>
CSS;


// Prefilling comments
if ($wp_ozh_cqr['prefill_reply']) {
	$cqr_prefill = "var nick = jQuery(this).parent().parent().parent().find('td.comment p.comment-author strong').text();\n";
	$cqr_prefill .= "var replytext = '${wp_ozh_cqr['prefill_reply']}';\n";
	$cqr_prefill .= "replytext = replytext.replace('%%link%%', '#comment-'+id);\n";
	$cqr_prefill .= "replytext = replytext.replace('%%name%%', jQuery.trim(nick));\n";
} else {
	$cqr_prefill = "var replytext = '';";
}

// Supporting threading if applicable
$cqr_threaded = !$wp_ozh_cqr['show_threaded'] ? "''" : <<<PRE
'<input type="hidden" id="cqr_threaded_'+id+'" name="cqr_threaded" value="0"/>'+
'<input type="submit" onclick="javascript:cqr_mark_threaded('+id+')" value="$cqr_replybutton_threaded" />'
PRE;

echo <<<JS
<script type="text/javascript">
var cqr_footer = false;
var cqr_is_open = [];
var cqr_post_list = {};
$cqr_doviewall
	
jQuery(document).ready(function() {
	// Reverse comments if applicable
	$cqr_reverse_comments
	// add our secret container
	jQuery('body').append('<div id="quickreplied"></div>');
	jQuery('#quickreplied').hide();
	// add links and their ajaxy behaviors
	cqr_add_links();
	cqr_init_links();
	// Add some love to the footer if applicable
	cqr_add_footer();
	// Make sure approve & unapprove links never turn black so you can't see the separator |
	cqr_approve_links();
})

// Remove everything this script adds via javascript
function cqr_destroy_links() {
	jQuery('#the-comment-list .quickreplylinks').remove();
	jQuery('#the-comment-list .quickreplydiv').remove();
	jQuery('br.quickreplybreak').remove();
}

// Reset links and divs
function cqr_reset_links() {
	cqr_destroy_links();
	cqr_add_links();
	cqr_init_links();	
}

// Add quick reply links, with and without ajax, and the reply divs
function cqr_add_links() {
	cqr_add_links_parsetable('#the-comment-list');
	cqr_add_links_parsetable('#the-extra-comment-list');
	if (doviewall) cqr_get_post_type();
}

// Parse the target table to add the stuff we need
function cqr_add_links_parsetable(target) {
	jQuery(target+' tr').each(function() {
		var id = jQuery(this).attr('id');
		if (id) {
			cqr_footer = true;
			id = id.replace('comment-', '');
			var post = jQuery(this).find("td:last-child a[@href*=deletecomment]").attr('href').replace(/.*&?p=([^&]*).*/,function($0,$1){return $1;}); // post id
			cqr_post_list[id] = post;
			$cqr_reply
			var _edit = '<span class="quickreplylinks"><a class="quickreplyeditlink" title="$cqr_editlink_title" href="${cqr_blogurl}/wp-admin/comment.php?action=editcomment&c=' + id + '">$cqr_editlink</a></span>';
			$cqr_allposts
			jQuery(this).find('td:nth-child($cqr_second_td)')
				.find('span:visible').each(function(i,n){
					cqr_approve_links_changecolor(this);
					//jQuery(this).html( jQuery(this).html().replace(/ \| /,' ') ); // <- breaks the Ajax, makes page reload on "Approve" and such :/
					if (i>=1) {
						jQuery(this).before('<br class="quickreplybreak" />');
					}
				}).end()
				.append(_reply)
				.append(_edit)
				.append(_viewall);
			jQuery(this).find('td:nth-child($cqr_first_td)')
				.append('<div class=\"quickreplydiv\" id=\"div_quick_reply_'+id+'\"></div>');

		}
	});
}

// On all .approve & .unapprove links, add some onclick behavior
function cqr_approve_links() {
	jQuery('td.action-links span').each(function(){
		if (jQuery(this).is('.approve') || jQuery(this).is('.unapprove')) {
			jQuery(this).find('a').click(function(){
				cqr_approve_links_changecolor(jQuery(this).parent());
			});
		}
		
	});
}

// Change span color to match parent's background
function cqr_approve_links_changecolor(span) {
	var _bgcolor = jQuery(span).css('background-color');
	if (_bgcolor == 'transparent') _bgcolor = 'white';
	jQuery(span).css('color', _bgcolor );
}

// Ajax call to get post types
function cqr_get_post_type() {
	//alert(cqr_post_list);
	jQuery.post(
		'${cqr_blogurl}/wp-content/plugins/${cqr_plugindir}/includes/_get_post_type.php',
		cqr_post_list,
		function (xml) { cqr_change_viewall_links(xml); }
	);
}


// Change on the fly "View all" links with correct link depending on post type
function cqr_change_viewall_links(xml) {
	var _link = {};
	_link['attachment'] = 'upload.php?attachment_id=';
	_link['page'] = 'edit-pages.php?page_id=';
	// post: edit.php?p=
	jQuery('comment',xml).each(function(){
		var cid = jQuery('id',this).text();
		var pid = jQuery('post',this).text();
		var type = jQuery('type',this).text();
		if (type != 'post') {
			// change the url of #quick_viewall_cid_pid
			var _href = jQuery('#quick_viewall_'+cid+'_'+pid).attr('href');
			_href = _href.replace(/edit\.php\?p=/,_link[type]);
			jQuery('#quick_viewall_'+cid+'_'+pid).attr('href',_href);
		}
	});
}


// Add Ajax functions to quick reply links
function cqr_init_links() {
	jQuery('a.quickreplylink').each(function() {
		var temp = jQuery(this).attr('id').replace('quick_reply_','');
		var id = temp.split('_')[0];
		cqr_is_open[id] = false;
		var post = temp.split('_')[1];
		jQuery(this).click(function(){
			if (cqr_is_open[id]) {
				/*
				jQuery('#div_quick_reply_'+id).html('');
				jQuery(this).html('$cqr_replylink').removeClass('quickreplycancel').addClass('quickreplylink').attr('title','$cqr_replylink');
				jQuery('#div_quick_reply_'+id).removeClass('quickreplydivpop');
				cqr_is_open[id] = false;
				*/
				cqr_cancelreply(id,post);
			} else {
				jQuery(this).html('$cqr_waitlink');
				${cqr_prefill}
				jQuery('#div_quick_reply_'+id).html('<style></style>'+
				'<div class="quickreplydivcontent"><form name="post" action="javascript:cqr_store_reply('+id+')" method="post" id="post">'+
				'$cqr_nonce'+
				'<input type="hidden" name="user_ID" value="$user_ID" />' +
				'<input type="hidden" name="cqr_action" value="reply"/>' +
				'<input type="hidden" name="comment_ID" value="'+id+'" />' +
				'<input type="hidden" name="comment_post_ID" value="'+post+'" />'+
				''+
				'<div><p><textarea id="cqr_textarea_'+id+'" rows="6" cols="80" name="content">'+replytext+'</textarea></p></div>'+
				'<p><input type="submit" value="$cqr_replybutton" style="font-weight: bold;" />'+
				$cqr_threaded+
				'<button onclick="cqr_cancelreply('+id+','+post+');return false;" class="quickreplycancelbutton">$cqr_cancellink</button>'+
				'</p>'+
				'</form></div>'				
				);
				jQuery('#cqr_textarea_'+id).keyup(function(e){
					if (e.which == 27) cqr_cancelreply(id,post) // close on Escape
				});
				jQuery('#div_quick_reply_'+id).addClass('quickreplydivpop');
				jQuery(this).html('$cqr_cancellink').addClass('quickreplycancel').removeClass('quickreplylink').attr('title','$cqr_cancellink');
				jQuery('#cqr_textarea_'+id).focus();
				cqr_is_open[id] = true;
			}
			return false;
		});
	});
}

// Close a reply popup
function cqr_cancelreply(id,post) {
	jQuery('#div_quick_reply_'+id).html('');
	jQuery('#quick_reply_'+id+'_'+post).html('$cqr_replylink').removeClass('quickreplycancel').addClass('quickreplylink').attr('title','$cqr_replylink');
	jQuery('#div_quick_reply_'+id).removeClass('quickreplydivpop');
	cqr_is_open[id] = false;
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
		function (xml) { cqr_get_reply(xml, id); }
	);
	jQuery('#div_quick_reply_'+id).html('<div class="quickreplydivcontent"><p><em>$cqr_postinglink</em></p></div>');
}
	
// Get XML response
function cqr_get_reply(xml, id) {
	if (typeof(xml) == 'string') {
		// Error mode. We're not supposed to get a String, so it means WP returned an HTML error page
		var wtf;
		if (/Duplicate/.test(xml)) {
			wtf = '$cqr_error_duplicate';
		} else if (/too quickly/.test(xml)) {
			wtf = '$cqr_error_toofast';
		} else {
			// Anything else, or maybe we're not catching the error message because of localization ?
			wtf = '$cqr_error_any';
		}
		jQuery('#div_quick_reply_'+id).html('<div class="quickreplydivcontent"><p><b>'+wtf+'</b><br/><a href="#" onclick="cqr_reset_links();return false;">Clear</a> <a href="#" id="cqr_error_details">Details &darr;</a></p><div style="display:none" id="cqr_error_message">'+xml+'</div></div>');
		jQuery('#cqr_error_details').click(function(){jQuery('#cqr_error_message').slideDown();return false;});
		return;
	}
	var replyto = jQuery('replyto',xml).text(); // the id of the comment we replied to
	var id = jQuery('id',xml).text(); // the id of the reply we posted
	var post = jQuery('post',xml).text();
	jQuery('#div_quick_reply_'+replyto).html('');
	// Load latest comment into our secret container, then display its content into the-comment-list
	jQuery('#quickreplied').load('${cqr_blogurl}/wp-content/plugins/${cqr_plugindir}/includes/_latest_comment.php?id='+id+'&checkbox='+$cqr_first_td,
		{},
		function() {
			jQuery('#the-comment-list').prepend(jQuery('#quickreplied').html());
			jQuery('#comment-'+id)
				.animate( { backgroundColor:"#CFEBF7" }, 600 )
				.animate( { backgroundColor:"#ff8" }, 300 )
				.animate( { backgroundColor:"transparent" }, 300 );
			cqr_reset_links();
		}
	);
}

// Reverse order of elements in container
function cqr_reverse_elements(elements,container) {
	elements = jQuery(elements);
	container = jQuery(container);
	
	jQuery(container).empty();
	jQuery(elements).each(function(){
		jQuery(container).prepend(this);
	});
}

// Insert some stuff to the footer
function cqr_add_footer() {
	if (cqr_footer) {
		var _footer = '<div id="cqr_footer" style="background:#EAF3FA;margin:2em 0 -1em 0;padding:0.5em 1em;"><a id="cqr_homelink" href="http://planetozh.com/blog/my-projects/absolute-comments-manager-instant-reply/">Absolute Comments ${imgreply}</a> by Ozh. <span id="cqr_likeit">Like it?</span> Check my <a id="cqr_pluginslink" href="http://planetozh.com/blog/my-projects/">other plugins</a>. Love it? <a id="cqr_beerlink" href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=ozh%40planetozh.com&item_name=PlanetOzh+WordPress+Absolute+Comments&no_note=1&currency_code=USD&tax=0&bn=PP-DonationsBF">Buy me a beer</a> !</div>';
		jQuery('div.wrap:last-child').after(_footer);
	}
}

</script>

JS;
?>
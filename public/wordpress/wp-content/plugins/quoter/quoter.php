<?php
/*
Plugin Name: Quoter
Plugin URI: http://www.damagedgoods.it/wp-plugins/quoter/
Description: Allows commenters to quote other comments (dynamically or server side if they have Javascript disabled) and any other text in a page (Javascript only).
Version: 1.1
Author: Daniele Mancino
Author URI: http://www.damagedgoods.it/
*/

/*
Copyright (c) 2005, 2006 Daniele Mancino.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Javascript code
if (isset($_GET['js'])) {
	quoter_JScode();
}

function quoter_JScode(){

	header('Content-type: text/javascript');
	
?>
<!--
function commentQuote(commentid,commenttext,commentarea) {
	var quote = '[quote comment="'+commentid+'"]'+commenttext+'[/quote]\n';
	var comment = document.getElementById(commentarea);
	addQuote(comment,quote);
	return false;
}

function postQuote(postid,commentarea,alertmsg) {
	var posttext = '';
	if (window.getSelection){
		posttext = window.getSelection();
	}
	else if (document.getSelection){
		posttext = document.getSelection();
	}
	else if (document.selection){
		posttext = document.selection.createRange().text;
	}
	else {
		return true;
	}
	if (posttext==''){
		alert(alertmsg);
		return true;
	} else {
		var quote='[quote post="'+postid+'"]'+posttext+'[/quote]\n';
		var comment=document.getElementById(commentarea);
		addQuote(comment,quote);
	}
	return false;
}

function addQuote(comment,quote){
	// Derived from Alex King's JS Quicktags code (http://www.alexking.org/)
	// Released under LGPL license
	// IE support
	if (document.selection) {
		comment.focus();
		sel = document.selection.createRange();
		sel.text = quote;
		comment.focus();
	}
	// MOZILLA/NETSCAPE support
	else if (comment.selectionStart || comment.selectionStart == '0') {
		var startPos = comment.selectionStart;
		var endPos = comment.selectionEnd;
		var cursorPos = endPos;
		var scrollTop = comment.scrollTop;
		if (startPos != endPos) {
			comment.value = comment.value.substring(0, startPos)
			              + quote
			              + comment.value.substring(endPos, comment.value.length);
			cursorPos = startPos + quote.length
		}
		else {
			comment.value = comment.value.substring(0, startPos) 
				              + quote
				              + comment.value.substring(endPos, comment.value.length);
			cursorPos = startPos + quote.length;
		}
		comment.focus();
		comment.selectionStart = cursorPos;
		comment.selectionEnd = cursorPos;
		comment.scrollTop = scrollTop;
	}
	else {
		comment.value += quote;
	}
}
//-->
<?php
	
	die();
	
}

// Initialization
define('QUOTER_VERSION',	'1.1');
define('QUOTER_DOMAIN',		'/quoter/lang/quoter');

if (defined('WPLANG') && '' != constant('WPLANG')){
	
	load_plugin_textdomain(QUOTER_DOMAIN);

}

add_action('admin_menu',			'quoter_admin_menu');
add_action('init', 					'quoter_rewrite');
add_action('wp_head',				'quoter_head');
add_filter('comment_text', 			'quoter_addquote', 7);
remove_filter('comment_text',		'balanceTags');

if (isset($_POST['quoter_delete_options'])) {
	
	// Do nothing
	
} else { // Create options and upgrade from older versions
	
	$oldversion = get_option('quoter_version');
	quoter_add_options();
	quoter_upgrade($oldversion);

}

// Add stuff in the header
function quoter_head() {
?>

<!-- Added by Quoter plugin v<?php echo QUOTER_VERSION; ?> -->
<?php
	// If it's a server side quote page don't let robots index it
	if (isset($_GET['quote']) && $_GET['quote'] != ""){
	
		echo ("<meta name=\"robots\" content=\"noindex, nofollow, noarchive\" />\n");
	
	}

	// Call Javascript
	echo ("<script type=\"text/javascript\" src=\"" . plugins_url('quoter') . "/quoter.php?js=1\"></script>\n");
	
	// Fix for CITE in Kubrik CSS
	
	?>
<style type="text/css" media="screen">
.commentlist blockquote cite { /* Fix for Kubrik theme */
	display: inline;
}
</style>

<?php

}

// Transform quoted text in a valid format for the textarea
function quoter_textareaize($quoter_text, $server_side){
	
	$quoter_text = nl2br($quoter_text);
	
	// Delete all normal and plugin-generated quotes (transform them to double newlines)
	// UNNEEDED NOW, due to nested quoted support. But might need in future.
	// $quoter_text = preg_replace("/<blockquote(.|\s)*?<\/blockquote>/i", "<br /><br />", $quoter_text);
	// $quoter_text = preg_replace("/\[quote(.|\s)*?\[\/quote\]/i", "<br /><br />", $quoter_text);
	
	$quoter_charset = get_option('blog_charset');
	$quoter_text = addslashes(htmlspecialchars($quoter_text, ENT_COMPAT, $quoter_charset));
			
	// Needed to remove newlines and carriage returns in string (NOT \n and \r)
	$quoter_text = str_replace(chr(13), "", $quoter_text);
	$quoter_text = str_replace(chr(10), "", $quoter_text);
	
	if ($server_side == 1){
	
		$quoter_text = stripslashes($quoter_text);
	
	}

	$quoter_text = str_replace("&lt;br /&gt;", "\n", $quoter_text);
			
	$quoter_text = trim($quoter_text);
			
	// Max two newlines
	$quoter_text = preg_replace("/ *\n */", "\n", $quoter_text);
	$quoter_text = preg_replace("/\s{3,}/", "\n\n", $quoter_text);
	
	return $quoter_text;
			
}

// Quote comment in the loop
function quoter_comment() {

	global $post, $user_ID;
	
	if ('open' == $post->comment_status) { // If comments are allowed
	
		if (get_option('comment_registration') && !$user_ID) { // Only registered users can comment
		
			// Do nothing
		
		} else {
			
			// Comment text
			$quoter_text = get_comment_text();
			
			$quoter_text = quoter_textareaize($quoter_text, 0);
			
			// Sanitize for JS function
			$quoter_text = str_replace("\n", "\\n", $quoter_text);			
						
			// Other variables
			$quoter_id = get_comment_ID();
			$quoter_link = get_option("quoter_link");
			$quoter_link_title = get_option("quoter_link_title");
			$quoter_link_class = get_option('quoter_link_class');
			$quoter_commenttextarea = get_option('quoter_commenttextarea');
			
			if ($quoter_link_title != ""){
				$quoter_link_title = ' title="' . $quoter_link_title . '"';
			}
			
			if ($quoter_link_class != ""){
				$quoter_link_class = ' class="' . $quoter_link_class . '"';
			}
			
			// Build $quoter_link
			$comments = "";
			$comments = get_comment($quoter_id);
						
			// Date and time
			$commentdatetime = $comments->comment_date;
			$dateformat = get_option('quoter_date');
			$commentdate = mysql2date($dateformat, $commentdatetime);
			$timeformat = get_option('quoter_time');
			$commenttime = mysql2date($timeformat, $commentdatetime);

			// Name
			$commentname = $comments->comment_author;

			// Replcement tags
			$quoter_link = str_replace("%date%", $commentdate, $quoter_link);
			$quoter_link = str_replace("%time%", $commenttime, $quoter_link);
			$quoter_link = str_replace("%name%", $commentname, $quoter_link);
			$quoter_link = str_replace("%id%", $quoter_id, $quoter_link);
						
			$quoter_link_title = str_replace("%date%", $commentdate, $quoter_link_title);
			$quoter_link_title = str_replace("%time%", $commenttime, $quoter_link_title);
			$quoter_link_title = str_replace("%name%", $commentname, $quoter_link_title);
			$quoter_link_title = str_replace("%id%", $quoter_id, $quoter_link_title);
			
			// Permalink
			$quoter_disable_rewrite = get_option("quoter_disable_rewrite");
			
			if ('' != get_option('permalink_structure') && $quoter_disable_rewrite == "FALSE") {
			
				$quoter_sslink = trailingslashit(get_permalink()) . "quote-comment-" . $quoter_id . "/#" . $quoter_commenttextarea;
			
			} else if ('' != get_option('permalink_structure') && $quoter_disable_rewrite == "TRUE") {
			
				$quoter_sslink = add_query_arg('quote', 'comment-' . $quoter_id, trailingslashit(get_permalink())) . "#".$quoter_commenttextarea;
			
			} else {
			
				$quoter_sslink = add_query_arg('quote', 'comment-' . $quoter_id, get_permalink()) . "#" . $quoter_commenttextarea;
			
			}
			
			// Convert & to &amp;
			$quoter_sslink = str_replace("&amp;", "&", $quoter_sslink);
			$quoter_sslink = str_replace("&", "&amp;", $quoter_sslink);

			// Here we go
			$quotecommentcode = "<a href=\"".$quoter_sslink."\" onclick=\"commentQuote('".$quoter_id."','".$quoter_text."','".$quoter_commenttextarea."');return false;\"".$quoter_link_title.$quoter_link_class." rel=\"nofollow\">".$quoter_link."</a>";

			echo $quotecommentcode;
			
		}
	}
}

// Server side comment quote
function quoter_comment_server(){

	global $post, $user_ID;

	if ('open' == $post->comment_status) { // If comments are allowed
	
		if (get_option('comment_registration') && !$user_ID) { // Only registered users can comment
		
			// Do nothing
		
		} else {
		
			$quote = $_GET['quote'];

			// If string contains "comment-" (no permalink) remove it
			$quote = str_replace("comment-", "", $quote);

			// Remove leading zeros
			$quote = preg_replace("/^0*([0-9]+)/i","\\1", $quote);
						
			if ($quote != '') {
				// Is there a comment for that id?
				$comments = "";
				$comments = get_comment($quote);
			}
		
			if (!$comments){ 
		
				// Do nothing
		
			} else {
					
				$quoter_text = $comments->comment_content;
					
				$quoter_text = quoter_textareaize($quoter_text, 1);
					
				$quoter_text = "[quote comment=\"".$quote."\"]" . $quoter_text . "[/quote]\n";
					
				echo $quoter_text;
				
			} // End output server side quote
			
		} // End if comment registration
		
	} // End if comment is open
	
}

// Quote anything in the page
function quoter_page() {

	global $post, $user_ID;
	
	$postid = $post->ID;

	if ('open' == $post->comment_status) {
		if (get_option('comment_registration') && !$user_ID) { 
		
			// Do nothing
		
		} else {
			
			$quoter_page_link = get_option("quoter_page_link");
			$quoter_page_link_title = get_option("quoter_page_link_title");
			$quoter_page_alert = get_option("quoter_page_alert");
			$quoter_page_class = get_option("quoter_page_class");
			$quoter_commenttextarea = get_option('quoter_commenttextarea');

			if ($quoter_page_link_title != ""){
			$quoter_page_link_title = ' title="' . $quoter_page_link_title . '"';
			}
			
			$quoter_page_alert = addslashes($quoter_page_alert);
			
			if ($quoter_page_class != ""){
			$quoter_page_class = ' class="' . $quoter_page_class . '"';
			}
			
			echo ("<a href=\"javascript:void(0);\" onmousedown=\"postQuote('".$postid."','".$quoter_commenttextarea."','".$quoter_page_alert."');return false;\"".$quoter_page_class.$quoter_page_link_title.">".$quoter_page_link."</a>");
		
		}
	
	}

}

function quoter_addquote($text){

	global $wpdb, $tablecomments;
	
	// DEBUG
	// echo "<div style='color: #000: font-weight: bold; background: #fff; border: 2px solid #000; text-align: left;'>".htmlentities($text)."</div><hr />";
	
	// Placeholder for code
	$codequotes = quoter_match_tags($text, '<code', '</code>');	
	
	if ($codequotes) {
	
		foreach ($codequotes as $start => $end) {
    
			$codequote = substr($text, $start, $end);
			$codequotenew = str_replace("[quote", "[codequote", $codequote);
			$codequotenew = str_replace("[/quote]", "[/codequote]", $codequotenew);
			$text = str_replace ($codequote, $codequotenew, $text);
	
		} 
		
	}

	$text = preg_replace("/\[quote( *)([^\]]*?)\]/i", "<blockquote\\1\\2>\n", $text);
	$text = str_replace("[/quote]", "</blockquote>\n", $text);
	
	// Fix invalidly nested XHTML and autop
	$text = quoter_balanceTags($text, 1);
	$text = wpautop($text, 0);
	
	// DEBUG
	// echo "<div style='color: #000: font-weight: bold; background: #fff; border: 2px solid #000; text-align: left;'>".htmlentities($text)."</div>";
	
	// Match all blockquotes
	$matches = quoter_match_tags($text, '<blockquote', '</blockquote>');	
	
	if ($matches) {
	
		$newtext = $text;
		
		foreach ($matches as $start => $end) {

			$bquote = substr($text, $start, $end);
			
			$attribs = "";
			$attribs = preg_replace("/^<blockquote([^>]*?)>(.|\s)*$/i", "\\1", $bquote);
			
			$content = "";
			$content = preg_replace("/^<blockquote[^>]*?>((.|\s)*)<\/blockquote>$/i", "\\1", $bquote);
			$commid = "";
			$postid = "";
			$cite = "";
			
			if ($attribs != ""){
			
				if (preg_match("/.*comment *= *([\'\"])([0-9]+)\\1.*/i", $attribs, $commentmatches)){
					$commid = $commentmatches[2];
					$commid = preg_replace("/^0*([0-9]+)/i", "\\1", $commid);
				}
				
				if (preg_match("/.*post *= *([\'\"])([0-9]+)\\1.*/i", $attribs, $postmatches)){
					$postid = $postmatches[2];
					$postid = preg_replace("/^0*([0-9]+)/i", "\\1", $postid);
				}
				
				if (preg_match("/.*cite *= *([\'\"])(.+)\\1.*/i", $attribs, $citematches)){
					$cite = $citematches[2];
				}
			
			}
			
			// Get comment and/or post
			$comments = "";
			$comments = get_comment($commid);
			
			$poststatus = "";
			$poststatus = $wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE ID = '$postid' LIMIT 1");

			// Case: comment (higher priority)
			if ($comments){ 

				// Create the quote header
			
				// Date and time
				$commentdatetime = $comments->comment_date;
				$dateformat = get_option('quoter_date');
				$commentdate = mysql2date($dateformat, $commentdatetime);
				$timeformat = get_option('quoter_time');
				$commenttime = mysql2date($timeformat, $commentdatetime);

				// Link
				$postcommid = $comments->comment_post_ID;
				$commentlink = get_permalink($postcommid)."#comment-".$commid;
			
				// Name
				$commentname = $comments->comment_author;
						
				// Do it			
				$quotehead = get_option('quoter_header');
				$quoteheadclass = get_option('quoter_header_class');
				$quotehead = str_replace("%date%", $commentdate, $quotehead);
				$quotehead = str_replace("%time%", $commenttime, $quotehead);
				$quotehead = str_replace("%name%", $commentname, $quotehead);
				$quotehead = str_replace("%link%", $commentlink, $quotehead);
				$quotehead = str_replace("%id%", $commid, $quotehead);
				if ($quotehead != ""){
					if ($quoteheadclass != ""){
						$quotehead = '<p class="' . $quoteheadclass . '">' . $quotehead . '</p>';
					} else {
						$quotehead = '<p>'.$quotehead.'</p>';
					}
				}
			
				// Modify text according to comment status
				$commentstatus = $comments->comment_approved;
				
				switch ($commentstatus) {
				
					case "0":
					
						// Replace quoted comment with "moderated"
						$content = "<p><em>" . __('Moderated', QUOTER_DOMAIN) . "</em></p>";
				
					break;
					
					case "spam":
				
						// Replace quoted comment with "spam"
						$content = "<p><em>" . __('Spam', QUOTER_DOMAIN) . "</em></p>";
					
					break;
				
					case "1":
				
						// Approved comment (Let's leave this for future additions)
						$content = $content;
						
					break;
					
				} // End switch
				
				$newtext = str_replace($bquote, "<div class=\"quoter_wrap\">" . $quotehead . "<blockquote cite=\"" . $commentlink . "\">" . $content . "</blockquote></div>", $newtext);

			} elseif ($poststatus == "publish" || $poststatus == "static"){ // Second priority: post

				$postpermalink = get_permalink($postid);
				$newtext = str_replace($bquote, "<blockquote cite=\"" . $postpermalink . "\">" . $content . "</blockquote>", $newtext);
			
			} else {
			
				if ($cite != ""){ // Third priority: cite
				
					$newtext = str_replace($bquote, "<blockquote cite=\"" . $cite . "\">" . $content . "</blockquote>", $newtext);
								
				} else { // No valid attributes
				
					$newtext = str_replace($bquote, "<blockquote>" . $content . "</blockquote>", $newtext);
				
				}
					
			}
	
		} // End foreach
		
		$text = $newtext;
		
	} // End if matches

	//Reconvert placeholders for <code>
	$codequotes = quoter_match_tags($text, '<code', '</code>');
	
	if ($codequotes) {
	
		foreach ($codequotes as $start => $end) {
    
			$codequote = substr($text, $start, $end);
			$codequotenew = str_replace("[codequote", "[quote", $codequote);
			$codequotenew = str_replace("[/codequote]", "[/quote]", $codequotenew);
			$text = str_replace ($codequote, $codequotenew, $text);
	
		} 
		
	}

	$text = wpautop($text, 0);
	
	return $text;
	
}
	
// Add rewrite rules

function quoter_rewrite(){
	
	$req = rtrim($_SERVER['REQUEST_URI'], '/');
    
	if (preg_match('/^(.+\/)quote-comment-([0-9]+)$/', $req, $match) && (url_to_postid($req) == 0)) {
        
		$_GET['quote'] = $match[2];
        $req = $match[1];
		$_SERVER['REQUEST_URI'] = $req;
    
	} 
	
}

// Options

function quoter_admin_menu(){
	if(function_exists('add_options_page')){
		add_options_page('Quoter Options', 'Quoter', 7, basename(__FILE__),'quoter_options_subpanel');
	}
}

// Add options
function quoter_add_options(){

		add_option('quoter_version', QUOTER_VERSION);
		
		add_option('quoter_link', __('Quote', QUOTER_DOMAIN));
		add_option('quoter_link_title',  __('Quote this comment', QUOTER_DOMAIN));
		add_option('quoter_header', __('<a href="%link%" title="View original comment"><cite>%name%</cite> wrote:</a>', QUOTER_DOMAIN));
		add_option('quoter_date', get_option('date_format'));
		add_option('quoter_time', get_option('time_format'));
		add_option('quoter_disable_rewrite', "FALSE");

		add_option('quoter_page_link', __('Quote selected text', QUOTER_DOMAIN));
		add_option('quoter_page_link_title', __('Click to quote selected text in your comment (requires Javascript)', QUOTER_DOMAIN));
		add_option('quoter_page_alert', __('No text selected', QUOTER_DOMAIN));

		add_option('quoter_link_class', "quoter_comment");
		add_option('quoter_header_class', "quoter_comment_header");
		add_option('quoter_page_class', "quoter_page");
		add_option('quoter_commenttextarea', "comment");
	
}

// Upgrade from older versions
function quoter_upgrade($version){
	
	global $wpdb;

	update_option('quoter_version', QUOTER_VERSION);
	
	// Before 1.1 there was no version option
	if ($version == "") {
	
		$version = "1.0.3";
	
	}

	// Upgrade to 1.1
	if (version_compare($version, "1.1", "<")) {

		// Convert firstline to header (damn my crappy english) :)
		$firstline = get_option("quoter_1stline");
		$firstlineclass = get_option("quoter_1stline_class");
	
		update_option('quoter_header', $firstline);
		update_option('quoter_header_class', $firstlineclass);
		
		delete_option('quoter_1stline');
		delete_option('quoter_1stline_class');
		
		// Convert quotes to new format
		$comments = $wpdb->get_results("SELECT comment_ID, comment_content FROM $wpdb->comments");

		if ($comments) {

			foreach($comments as $comment) {
			
				$comment_content = $comment->comment_content;
				$comment_content = preg_replace("/\[quote=\"(comment|post)-([0-9]+)\"\]/i", "[quote \\1=\"\\2\"]", $comment_content);
				$comment_content = addslashes($comment_content);

				if ($comment_content != "") { // Make sure we aren't going to destroy the database, just for safety ;)
				
					$wpdb->query("UPDATE $wpdb->comments SET comment_content = '$comment_content' WHERE comment_ID = '$comment->comment_ID'");
				
				}
		
			}
		
		}
		
	} // End upgrade to 1.1
	
}

// Subpanel

function quoter_options_subpanel(){

	// Get options
	$quoter_link = get_option("quoter_link");
	$quoter_link_title = get_option("quoter_link_title");
	$quoter_header = get_option("quoter_header");
	$quoter_date = get_option("quoter_date");
	$quoter_time = get_option("quoter_time");
	$quoter_disable_rewrite = get_option("quoter_disable_rewrite");
	
	$quoter_page_link = get_option("quoter_page_link");
	$quoter_page_link_title = get_option("quoter_page_link_title");
	$quoter_page_alert = get_option("quoter_page_alert");
	
	$quoter_link_class = get_option("quoter_link_class");
	$quoter_header_class = get_option("quoter_header_class");
	$quoter_page_class = get_option("quoter_page_class");
  	$quoter_commenttextarea = get_option("quoter_commenttextarea");
	
	if (isset($_POST['info_update'])) {

		// Update options

		$quoter_link = $_POST['quoter_link'];
		$quoter_link = stripslashes($quoter_link);
		if ($quoter_link == ""){
			$quoter_link = "Quote";
		}
		update_option('quoter_link', $quoter_link);
		
		$quoter_link_title = $_POST['quoter_link_title'];
		$quoter_link_title = stripslashes($quoter_link_title);
		update_option('quoter_link_title', $quoter_link_title);
		
		$quoter_header = $_POST['quoter_header'];
		$quoter_header = stripslashes($quoter_header);
		update_option('quoter_header', $quoter_header);

		$quoter_date = $_POST['quoter_date'];
		$quoter_date = stripslashes($quoter_date);
		if (!$quoter_date){
			$quoter_date = get_option('date_format');
		}
		update_option('quoter_date', $quoter_date);
		
		$quoter_time = $_POST['quoter_time'];
		$quoter_time = stripslashes($quoter_time);
		if (!$quoter_time){
			$quoter_time = get_option('time_format');
		}
		update_option('quoter_time', $quoter_time);

		if (isset($_POST['quoter_disable_rewrite'])){
			update_option('quoter_disable_rewrite', "TRUE");
			$quoter_disable_rewrite = "TRUE";
		} else {
			update_option('quoter_disable_rewrite', "FALSE");
			$quoter_disable_rewrite = "FALSE";
		}
		
		$quoter_page_link = $_POST['quoter_page_link'];
		$quoter_page_link = stripslashes($quoter_page_link);
		if ($quoter_page_link == ""){
			$quoter_page_link = "Quote selected text";
		}
		update_option('quoter_page_link', $quoter_page_link);
		
		$quoter_page_link_title = $_POST['quoter_page_link_title'];
		$quoter_page_link_title = stripslashes($quoter_page_link_title);
		update_option('quoter_page_link_title', $quoter_page_link_title);
		
		$quoter_page_alert = $_POST['quoter_page_alert'];
		$quoter_page_alert = stripslashes($quoter_page_alert);
		update_option('quoter_page_alert', $quoter_page_alert);
		
		$quoter_link_class = $_POST['quoter_link_class'];
		$quoter_link_class = stripslashes($quoter_link_class);
		update_option('quoter_link_class', $quoter_link_class);

		$quoter_header_class = $_POST['quoter_header_class'];
		$quoter_header_class = stripslashes($quoter_header_class);
		update_option('quoter_header_class', $quoter_header_class);
		
		$quoter_page_class = $_POST['quoter_page_class'];
		$quoter_page_class = stripslashes($quoter_page_class);
		update_option('quoter_page_class', $quoter_page_class);
		
		$quoter_commenttextarea = $_POST['quoter_commenttextarea'];
		$quoter_commenttextarea = stripslashes($quoter_commenttextarea);
		update_option('quoter_commenttextarea', $quoter_commenttextarea);
  
?><div id="message" class="updated fade"><p><strong><?php 
_e('Options saved.', QUOTER_DOMAIN); ?></strong></p></div><?php
	
	} elseif (isset($_POST['quoter_delete_options'])) { 
	
		$oktodeleteoptions = "yes";
	
		// Delete options
	
		delete_option('quoter_version');
	
		delete_option('quoter_link');
		delete_option('quoter_link_title');
		delete_option('quoter_header');
		delete_option('quoter_date');
		delete_option('quoter_time');
		delete_option('quoter_disable_rewrite');
		
		delete_option('quoter_page_link');
		delete_option('quoter_page_link_title');
		delete_option('quoter_page_alert');
		
		delete_option('quoter_link_class');
		delete_option('quoter_header_class');
		delete_option('quoter_page_class');
		delete_option('quoter_commenttextarea');
		
?><div id="message" class="updated fade"><p><strong><?php _e('Options deleted. Now you can deactivate Quoter in <a href="plugins.php">Manage Plugins</a>.', QUOTER_DOMAIN); ?>
<br />
<?php _e('Don\'t reload this page, if you do options will be set again.', QUOTER_DOMAIN); ?></strong></p></div><?php

	} 
	
?>
<?php // Don't continue if options have been deleted 

if ($oktodeleteoptions == "") {

?>
	
<div class="wrap">
    <h2><?php _e('Quoter Options', QUOTER_DOMAIN); ?></h2>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo basename(__FILE__); ?>">
    <fieldset class="options">
	<legend><?php _e('Comment quoting', QUOTER_DOMAIN) ?></legend>
	<table class="optiontable">
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_link"><?php _e('Quote link:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_link" type="text" id="quoter_link" value="<?php echo htmlspecialchars($quoter_link); ?>" size="40" />
	<br />
	<?php _e('Quote link text.', QUOTER_DOMAIN); ?> <?php _e('Cannot be empty.', QUOTER_DOMAIN); ?><br />
	<?php _e('Replacement tags:', QUOTER_DOMAIN); ?> <code>%name%</code>, <code>%date%</code>, <code>%time%</code>, <code>%id%</code>.
	</td>
	</tr>
		
	<tr valign="top">
	<th scope="row">
	<label for="quoter_link_title"><?php _e('Quote link title:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_link_title" type="text" id="quoter_link_title" value="<?php echo htmlspecialchars($quoter_link_title); ?>" size="40" />
	<br />
	<?php _e('Quote link title.', QUOTER_DOMAIN); ?><br />
	<?php _e('Replacement tags:', QUOTER_DOMAIN); ?> <code>%name%</code>, <code>%date%</code>, <code>%time%</code>, <code>%id%</code>.
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_header"><?php _e('Quote header:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_header" type="text" id="quoter_header" value="<?php echo htmlspecialchars($quoter_header); ?>" size="40" />
	<br />
	<?php _e('Text showed before a quoted comment.', QUOTER_DOMAIN); ?><br /><?php _e('It will be wrapped in a <a href="#headerlink" title="Go to paragraph class option"><code>&lt;p&gt;</code>aragraph</a> in order to generate valid XHTML.', QUOTER_DOMAIN); ?><br /><?php _e('Replacement tags:', QUOTER_DOMAIN); ?> <code>%name%</code>, <code>%date%</code>, <code>%time%</code>, <code>%link%</code>, <code>%id%</code>.
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_date"><?php _e('Date format:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_date" type="text" id="quoter_date" value="<?php echo htmlspecialchars($quoter_date); ?>" size="40" />
	<br />
	<?php _e('<code>%date%</code> format.', QUOTER_DOMAIN); ?> <?php _e('Uses PHP <a href="http://www.php.net/date" rel="external"><code>date()</code></a> format. If empty, uses blog default value.', QUOTER_DOMAIN); ?><br /><?php _e('Output:', QUOTER_DOMAIN) ?> <strong><?php echo mysql2date($quoter_date, current_time('mysql')); ?></strong>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_time"><?php _e('Time format:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_time" type="text" id="quoter_time" value="<?php echo htmlspecialchars($quoter_time); ?>" size="40" />
	<br />
	<?php _e('<code>%time%</code> format.', QUOTER_DOMAIN); ?> <?php _e('Uses PHP <a href="http://www.php.net/date" rel="external"><code>date()</code></a> format. If empty, uses blog default value.', QUOTER_DOMAIN); ?><br /><?php _e('Output:', QUOTER_DOMAIN) ?> <strong><?php echo mysql2date($quoter_time, current_time('mysql')); ?></strong>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_disable_rewrite"><?php _e('Disable URL Rewrite:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_disable_rewrite" type="checkbox" id="quoter_disable_rewrite" value="TRUE"<?php if ($quoter_disable_rewrite == "TRUE"){ ?> checked="checked"<?php } ?> />
	<br />
	<?php _e('Quoter uses WordPress-style URL rewriting for server side comment quoting (needed if users have Javascript disabled). However this may not always work. If you get 404 errors when trying to quote a comment without Javascript, check the above option.', QUOTER_DOMAIN); ?>
	</td>
	</tr>
	
	</table>
    </fieldset>
	
	<fieldset class="options">
	<legend><?php _e('Selected text quoting', QUOTER_DOMAIN) ?></legend>
	<table class="optiontable">
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_page_link"><?php _e('&quot;Quote selected text&quot; link text:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_page_link" type="text" id="quoter_page_link" value="<?php echo htmlspecialchars($quoter_page_link); ?>" size="40" />
	<br />
	<?php _e('Text of the &quot;quote selected text&quot; link.', QUOTER_DOMAIN); ?> <?php _e('Cannot be empty.', QUOTER_DOMAIN); ?>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_page_link_title"><?php _e('&quot;Quote selected text&quot; link title:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_page_link_title" type="text" id="quoter_page_link_title" value="<?php echo htmlspecialchars($quoter_page_link_title); ?>" size="40" />
	<br />
	<?php _e('Title of the &quot;quote selected text&quot; link.', QUOTER_DOMAIN); ?>
	</td>
	</tr>

	<tr valign="top">
	<th scope="row">
	<label for="quoter_page_alert"><?php _e('&quot;No text selected&quot; alert text:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_page_alert" type="text" id="quoter_page_alert" value="<?php echo htmlspecialchars($quoter_page_alert); ?>" size="40" />
	<br />
	<?php _e('Text of the alert message when the user has not selected any text.', QUOTER_DOMAIN); ?>
	</td>
	</tr>
	
	</table>
    </fieldset>
	
    <fieldset class="options">
	<legend><?php _e('IDs and classes', QUOTER_DOMAIN) ?></legend>
	<p><?php _e('Although not needed in most cases, you may have to change these values to fit your theme and CSS style.', QUOTER_DOMAIN) ?></p>
	<table class="optiontable">
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_link_class"><?php _e('Quote link class:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_link_class" type="text" id="quoter_link_class" value="<?php echo htmlspecialchars($quoter_link_class); ?>" size="40" />
	<br />
	<?php _e('Class of the quote comment link.', QUOTER_DOMAIN); ?><br /><?php _e('CSS example:', QUOTER_DOMAIN); ?> <code>a.<?php echo htmlspecialchars($quoter_link_class); ?> { ... }</code>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row"><a id="headerlink"></a>
	<label for="quoter_header_class"><?php _e('Quote header paragraph class:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_header_class" type="text" id="quoter_header_class" value="<?php echo htmlspecialchars($quoter_header_class); ?>" size="40" />
	<br />
	<?php _e('Class of the paragraph that contains the quote header.', QUOTER_DOMAIN); ?><br /><?php _e('CSS example:', QUOTER_DOMAIN); ?> <code>p.<?php echo htmlspecialchars($quoter_header_class); ?> { ... }</code>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_page_class"><?php _e('Quote selected text class:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_page_class" type="text" id="quoter_page_class" value="<?php echo htmlspecialchars($quoter_page_class); ?>" size="40" />
	<br />
	<?php _e('Class of the &quot;Quote selected text&quot; link.', QUOTER_DOMAIN); ?><br /><?php _e('CSS example:', QUOTER_DOMAIN); ?> <code>a.<?php echo htmlspecialchars($quoter_page_class); ?> { ... }</code>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<label for="quoter_commenttextarea"><?php _e('Comment textarea ID:', QUOTER_DOMAIN); ?></label>
	</th>
	<td>
	<input name="quoter_commenttextarea" type="text" id="quoter_commenttextarea" value="<?php echo htmlspecialchars($quoter_commenttextarea); ?>" size="40" />
	<br />
	<?php _e('Leave it to &quot;comment&quot; if you haven\'t modified it in your theme.', QUOTER_DOMAIN); ?>
	</td>
	</tr>
	
	</table>
    </fieldset>
	
	<fieldset class="options">
	<div class="submit">
  	<input type="submit" name="info_update" value="<?php _e('Update Options', QUOTER_DOMAIN); ?> »" />
	</div>
	</fieldset>

<hr />

<fieldset class="options">
<legend><?php _e('Uninstall', QUOTER_DOMAIN) ?></legend>
<p><?php _e('If you wish to deactivate Quoter and do not plan to use it again in the future, click this button to delete all options from the database.<br />Do this <strong>before</strong> you deactivate Quoter from Manage Plugins.', QUOTER_DOMAIN); ?></p>
<div class="submit" style="text-align: left;">
<input type="submit" name="quoter_delete_options" id="deletepost" value="<?php _e('Delete options', QUOTER_DOMAIN) ?>" title="<?php _e('Delete Quoter options from the database', QUOTER_DOMAIN); ?>" onclick="return confirm('<?php _e('You are going to permanently delete all Quoter options from the database.\nAre you sure you want to continue?', QUOTER_DOMAIN); ?>')" />
</div>
</fieldset>
</form>

<hr />

<h3><?php _e('Help and support', QUOTER_DOMAIN); ?></h3>
<p><a href="http://www.damagedgoods.it/wp-plugins/quoter/" rel="external"><?php _e('Visit plugin homepage', QUOTER_DOMAIN); ?></a></p>

</div>
<?php

} // End if options deleted

}

// **************** //
// Third party code //
// **************** //

// Fixed version of balanceTags() which supports nested blockquotes - by Coffee2code
// http://trac.wordpress.org/ticket/1170
function quoter_balanceTags($text, $is_comment = 0) {
	
	if ( get_option('use_balanceTags') == 0)
		return $text;

	$tagstack = array(); $stacksize = 0; $tagqueue = ''; $newtext = '';
	$single_tags = array('br', 'hr', 'img', 'input'); //Known single-entity/self-closing tags
	$nestable_tags = array('blockquote', 'div', 'span'); //Tags that can be immediately nested within themselves

	# WP bug fix for comments - in case you REALLY meant to type '< !--'
	$text = str_replace('< !--', '<    !--', $text);
	# WP bug fix for LOVE <3 (and other situations with '<' before a number)
	$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

	while (preg_match("/<(\/?\w*)\s*([^>]*)>/",$text,$regex)) {
		$newtext .= $tagqueue;

		$i = strpos($text,$regex[0]);
		$l = strlen($regex[0]);

		// clear the shifter
		$tagqueue = '';
		// Pop or Push
		if ($regex[1][0] == "/") { // End Tag
			$tag = strtolower(substr($regex[1],1));
			// if too many closing tags
			if($stacksize <= 0) { 
				$tag = '';
				//or close to be safe $tag = '/' . $tag;
			}
			// if stacktop value = tag close value then pop
			else if ($tagstack[$stacksize - 1] == $tag) { // found closing tag
				$tag = '</' . $tag . '>'; // Close Tag
				// Pop
				array_pop ($tagstack);
				$stacksize--;
			} else { // closing tag not at top, search for it
				for ($j=$stacksize-1;$j>=0;$j--) {
					if ($tagstack[$j] == $tag) {
					// add tag to tagqueue
						for ($k=$stacksize-1;$k>=$j;$k--){
							$tagqueue .= '</' . array_pop ($tagstack) . '>';
							$stacksize--;
						}
						break;
					}
				}
				$tag = '';
			}
		} else { // Begin Tag
			$tag = strtolower($regex[1]);

			// Tag Cleaning

			// If self-closing or '', don't do anything.
			if((substr($regex[2],-1) == '/') || ($tag == '')) {
			}
			// ElseIf it's a known single-entity tag but it doesn't close itself, do so
			elseif ( in_array($tag, $single_tags) ) {
				$regex[2] .= '/';
			} else {	// Push the tag onto the stack
				// If the top of the stack is the same as the tag we want to push, close previous tag
				if (($stacksize > 0) && !in_array($tag, $nestable_tags) && ($tagstack[$stacksize - 1] == $tag)) {
					$tagqueue = '</' . array_pop ($tagstack) . '>';
					$stacksize--;
				}
				$stacksize = array_push ($tagstack, $tag);
			}

			// Attributes
			$attributes = $regex[2];
			if($attributes) {
				$attributes = ' '.$attributes;
			}
			$tag = '<'.$tag.$attributes.'>';
			//If already queuing a close tag, then put this tag on, too
			if ($tagqueue) {
				$tagqueue .= $tag;
				$tag = '';
			}
		}
		$newtext .= substr($text,0,$i) . $tag;
		$text = substr($text,$i+$l);
	}  

	// Clear Tag Queue
	$newtext .= $tagqueue;

	// Add Remaining text
	$newtext .= $text;

	// Empty Stack
	while($x = array_pop($tagstack)) {
		$newtext .= '</' . $x . '>'; // Add remaining tags to close
	}

	// WP fix for the bug with HTML comments
	$newtext = str_replace("< !--","<!--",$newtext);
	$newtext = str_replace("<    !--","< !--",$newtext);

	return $newtext;
}

// Implementation of a function found at http://www.sitepoint.com/forums/showthread.php?t=209687
function quoter_match_tags($str, $open_tag, $close_tag){
    
    $open_length = strlen($open_tag);
    $close_length = strlen($close_tag);
    $stack  = array();
    $result = array();
    $pos = -1;
    $end = strlen($str) + 1;

    while(TRUE){
	
        $p1 = strpos($str, $open_tag, $pos + 1);
        $p2 = strpos($str, $close_tag, $pos + 1);
        $pos = min(($p1 === FALSE) ? $end : $p1, ($p2 === FALSE) ? $end : $p2);
        
		if ($pos == $end){
		
            break;
        
		}
        
		if (substr($str, $pos, $open_length) == $open_tag){
		
            array_push($stack, $pos);
        
		}
		
        else {
            
			if (substr($str, $pos, $close_length) == $close_tag){
 				
				if(!count($stack)){
					
					// user_error('Odd closebrace at offset '.$pos);
				
				}
                
				else {
                
					$result[array_pop($stack)] = $pos;
                
				}
            
			}
        
		}
    
	}
    
	if (count($stack)){
		
		// user_error('odd openbrace at offset '.array_pop($stack));
    
	}
    
	ksort($result);
	
	foreach ($result as $start => $end){
		
		$results[$start] = $end - $start + $close_length;
	
	}
    
	return $results;

}
?>

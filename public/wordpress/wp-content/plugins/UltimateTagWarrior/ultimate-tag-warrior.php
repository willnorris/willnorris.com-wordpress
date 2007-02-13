<?php
/*
Plugin Name: Ultimate Tag Warrior
Plugin URI: http://www.neato.co.nz/ultimate-tag-warrior/
Description: Ultimate Tag Warrior is a tagging plugin, that's heavy on tag visualisation.  I Like Pi!
Version: 3.14159265
Author: Christine Davis
Author URI: http://www.neato.co.nz
*/

/* Maybe you'll need this.. maybe you won't...
$path = ini_get('include_path');
if (!(substr($path, strlen( $path ) - strlen(PATH_SEPARATOR)) === PATH_SEPARATOR)) {
	$path .= PATH_SEPARATOR;
}
$path .= $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/UltimateTagWarrior";
ini_set("include_path", $path);
*/

require_once('ultimate-tag-warrior-core.php');
require_once('ultimate-tag-warrior-actions.php');

load_plugin_textdomain('ultimate-tag-warrior', 'wp-content/plugins/UltimateTagWarrior/languages');

$utw = new UltimateTagWarriorCore();

$utw->CheckForInstall();

function UTW_ShowTagsForCurrentPost($formattype, $format="", $limit = 0) {
	global $utw, $post;

	$format = $utw->GetFormat($formattype, $format);

	$utw->ShowTagsForPost($post->ID , $format, $limit);
}

function UTW_ShowRelatedTagsForCurrentPost($formattype, $format="", $limit = 0) {
	global $utw, $post;

	$format = $utw->GetFormat($formattype, $format);

	$utw->ShowRelatedTags($utw->GetTagsForPost($post->ID), $format, $limit);
}

function UTW_ShowRelatedPostsForCurrentPost($formattype, $format="", $limit = 0) {
	global $utw, $post;

	$format = $utw->GetFormat($formattype, $format);

	$utw->ShowRelatedPosts($utw->GetTagsForPost($post->ID), $format, $limit);
}

function UTW_ShowRelatedTagsForCurrentTagSet($formattype, $format="", $limit = 0) {
	global $utw;

	$format = $utw->GetFormat($formattype, $format);

	$utw->ShowRelatedTags($utw->GetCurrentTagSet(), $format, $limit);
}

function UTW_ShowCurrentTagSet($formattype, $format="", $limit = 0) {
	global $utw;

	$format = $utw->GetFormat($formattype, $format);

	echo $utw->FormatTags($utw->GetCurrentTagSet(), $format, $limit);
}

function UTW_ShowWeightedTagSet($formattype, $format="", $limit=150) {
	global $utw;

	$format = $utw->GetFormat($formattype, $format);

	echo $utw->FormatTags($utw->GetWeightedTags("weight", "desc", $limit), $format);
}

function UTW_ShowTimeSensitiveWeightedTagSet($formattype, $format="", $limit=150) {
	global $utw;

	$format = $utw->GetFormat($formattype, $format);

	echo $utw->FormatTags($utw->GetWeightedTags("weight", "desc", $limit, true), $format);
}

function UTW_ShowWeightedTagSetAlphabetical($formattype, $format="", $limit=150) {
	global $utw;

	$format = $utw->GetFormat($formattype, $format);

	echo $utw->FormatTags($utw->GetWeightedTags("tag", "asc", $limit), $format);
}

function UTW_ShowTimeSensitiveWeightedTagSetAlphabetical($formattype, $format="", $limit=150) {
	global $utw;

	$format = $utw->GetFormat($formattype, $format);

	echo $utw->FormatTags($utw->GetWeightedTags("tag", "asc", $limit, true), $format);
}

function UTW_ShowMetaKeywords() {
	global $utw, $post;

	if (is_utwtag()) {
		echo '<meta name="keywords" content="' . $utw->FormatTags($utw->GetCurrentTagSet(),array('first'=>'%tagdisplay%', 'default'=> ', %tagdisplay%')) . '" />';
    } else if (is_single()) {
	    echo '<meta name="keywords" content="' . $utw->FormatTags($utw->GetTagsForPost($post->ID),array('first'=>'%tagdisplay%', 'default'=> ', %tagdisplay%')) . '" />';
    }
}

function UTW_ShowRSSFeedIcon() {
	global $utw;

	$format = array('first'=>'%tagsetrssicon%', 'default'=>'');

	echo $utw->FormatTags($utw->GetCurrentTagSet(), $format, $limit);
}

function UTW_HasTags() {
	global $utw, $post;

	return (bool)$utw->GetPostHasTags($post->ID);
}

if(!function_exists('is_tag')) {
	function is_tag() {
		return is_utwtag();
	}
}

function is_utwtag() {
	global $utw;
	return (count($utw->GetCurrentTagSet()) > 0);
}

/* if $format is passed in,  then the tags will replace the contents of the div named "tags-{tagid}" with a new tag list using the named format.  Otherwise, it'll just add the tag. */
function UTW_AddTagToCurrentPost($format="", $minimumuserlevel = 3) {
	global $post, $user_level, $utw;

	if ($user_level < $minimumuserlevel) { return; }

	$postid = $post->ID;
	// drop in javascript
	echo "<script src=\"" . $utw->GetAjaxJavascriptUrl() . "\" type=\"text/javascript\"></script>";
	if ($format=="") {
	?><input type="text" size="9" id="soloAddTag-<?php echo $postid ?>" /> <input type="button" value="+" onClick="sndReqNoResp('add', document.getElementById('soloAddTag-<?php echo  $postid ?>').value, '<?php echo  $postid ?>')" /><?php
	} else {
	?><input type="text" size="9" id="soloAddTag-<?php echo $postid ?>" /> <input type="button" value="+" onClick="sndReq('add', document.getElementById('soloAddTag-<?php echo  $postid ?>').value, '<?php echo  $postid ?>', '<?php echo  $format ?>')" /><?php
	}
}
?>
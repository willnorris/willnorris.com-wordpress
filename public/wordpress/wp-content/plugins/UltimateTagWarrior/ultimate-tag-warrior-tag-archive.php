<?php
/*
Plugin Name: Ultimate Tag Warrior:  Tag Archive
Plugin URI: http://www.neato.co.nz/ultimate-tag-warrior/
Description: Renders an archive view, based on tags.  Depends on Ultimate Tag Warrior 2.5.1+
Version: 1.0
Author: Christine Davis
Author URI: http://www.neato.co.nz
*/
ini_set("include_path", ini_get('include_path') . PATH_SEPARATOR . ".");

include_once('ultimate-tag-warrior-core.php');

$utw = new UltimateTagWarriorCore();

/*
ultimate_tag_archive
You may remember my plugin Category Archive.  This is a tag archive in the same style.

Displays a list of the top x most popular tags,  with the top y most recent posts for that tag.  If there are more posts,  there is also a link to the tag page for that tag.
$limit the maximum number of tags to display
$postlimit the maximum number of posts to display for each tag
*/
function UTW_TagArchive ($limit = 20, $postlimit=20) {
	global $wpdb, $tabletags, $tablepost2tag, $utw, $lzndomain;

	$baseurl = get_option('utw_base_url');

	$tags = $utw->GetPopularTags($limit, 'count', 'desc');
	if ($tags) {
		foreach($tags as $tag) {
			$out .= "<div class=\"tagarchive\">";
			$out .= "<div class=\"tagarchivename\">" . $utw->FormatTag($tag, "%taglink%") . " - " . $tag->count . "</div>";

			$q = "select p.ID, p.post_title from $tabletags t inner join $tablepost2tag p2t on p2t.tag_id = t.tag_id inner join $wpdb->posts p on p2t.post_id = p.ID and t.tag='$tag->tag' limit $postlimit";
			$posts = $utw->GetPostsForTag($tag);

			$out .= "<div class=\"tagarchiveposts\">";

			if ($posts) {
				foreach ($posts as $post) {
					$out .= "<a href=\"" . get_permalink($post->ID) . "\">$post->post_title</a>, ";
				}
				if (count($posts) == $postlimit) {
					$out .= $utw->FormatTag($tag, "<a href=\"%tagurl%\">" . __("More from", $lzndomain) . " %tagdisplay%...</a>");;
				} else {
					// trim trailing comma
					$out = substr($out, 0, -2);
				}
			}
			$out .= "</div>";
			$out .= "</div>";
		}
	} else {
		$out = __("No Tags", $lzndomain);
	}
	echo $out;
}
?>
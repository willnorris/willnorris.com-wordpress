<?php
/*
Plugin Name: Ultimate Tag Warrior
Plugin URI: http://www.neato.co.nz/manyfaces/wordpress-plugins/ultimate-tag-warrior/
Description: Add tags to wordpress.  Tags and tag/post associations are seperated out for great justice.
			 And when I say great justice,  I mean doing more with tags than just listing them.  This is,
			 the ultimate tag warrior.
Version: 1.3 Legacy
Author: Christine Davis
Author URI: http://www.neato.co.nz
*/
ini_set("include_path", ini_get('include_path') . PATH_SEPARATOR . ".");

include_once('ultimate-tag-warrior-core.php');
include_once('ultimate-tag-warrior-actions.php');

$utw = new UltimateTagWarriorCore();


$deliciousAPIURL = "http://del.icio.us/api/";

/*
ultimate_show_popular_tags
Creates a list of the most popular tags.  Intended for sidebar use.
The format of the tags is:

<li>{tagname} ({count})</li>
*/
function ultimate_show_popular_tags($limit = 10) {
	global $utw;

	$utw->ShowPopularTags($limit, "<li>%taglink% (%tagcount%)</li>");
}

/*
ultimate_show_post_tags
Displays a list of tags associated with the current post.

$seperator goes between each tag (but not at the beginning or the end of the list)
$baseurl is the base url for the text link
$notagmessage is what will display if there are no tags for the post.
*/
function ultimate_show_post_tags($separator="&nbsp;", $baseurl='/tag/', $notagmessage = "", $morelinks="") {
	global $post, $utw;

	$id = $post->postid;
	if(empty($id)) {
		$id = $post->ID;
	}

	$baseformat = "%taglink%";

	if ($baseurl != '/tag/') {
		$baseformat = "<a href=\"$baseurl%tag%\" rel=\"tag\">%tagdisplay%</a>";
	}

	if ($morelinks) {
		if (is_array($morelinks)) {
			foreach($morelinks as $link) {
				$baseformat .= " <a href=\"$link%tag%\" rel=\"tag\">&raquo;</a> ";
			}
		} else {
				$baseformat .= " <a href=\"" . $morelinks . "%tag%\" rel=\"tag\">&raquo;</a> ";
		}
	}

	$format = Array();
	$format["default"] = $baseformat . $separator;
	$format["last"] = $baseformat;
	$format["none"] = $notagmessage;

	$utw->ShowTagsForPost($id, $format);
}

/*
ultimate_tag_archive
You may remember my plugin Category Archive.  This is a tag archive in the same style.

Displays a list of the top x most popular tags,  with the top y most recent posts for that tag.  If there are more posts,  there is also a link to the tag page for that tag.
$limit the maximum number of tags to display
$postlimit the maximum number of posts to display for each tag
*/
function ultimate_tag_archive($limit = 20, $postlimit=20) {
	global $wpdb, $tabletags, $tablepost2tag, $lzndomain;

	$baseurl = get_option('utw_base_url');

	$q = "select t.tag, count(t.tag) as count from $tabletags t inner join $tablepost2tag p2t on p2t.tag_id = t.id group by tag having count > 0 order by count desc limit $limit";
	$tags = $wpdb->get_results($q);
	if ($tags) {
		foreach($tags as $tag) {
			$out .= "<div class=\"tagarchive\">";

			$out .= "<div class=\"tagarchivename\">" . $tag->tag . " - " . $tag->count . "</div>";
			$q = "select p.ID, p.post_title from $tabletags t inner join $tablepost2tag p2t on p2t.tag_id = t.id inner join $wpdb->posts p on p2t.post_id = p.ID and t.tag='$tag->tag' limit $postlimit";
			$posts = $wpdb->get_results($q);

			$out .= "<div class=\"tagarchiveposts\">";

			if ($posts) {
				foreach ($posts as $post) {
					$out .= "<a href=\"" . get_permalink($post->ID) . "\">$post->post_title</a>, ";
				}
				if (count($posts) == $postlimit) {
					$out .= "<a href=\"$baseurl$tag->tag\">" . __("More from", $lzndomain) . " $tag->tag</a>...";
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

/*
ultimate_tag_cloud
Creates a tag cloud,  which can be styled in CSS.
*/

function ultimate_tag_cloud($order='tag', $direction='asc') {
	global $wpdb, $tablepost2tag, $utw;

	$q = "SELECT count(*) FROM $tablepost2tag";
	$totalTags = $wpdb->get_var($q);

	$tags = $utw->GetPopularTags(-1,$order, $direction);

	// The average number of times a tag appears on each post.
	$average = (count($tags) / $totalTags);

	$baseurl = get_option('utw_base_url');
	$siteurl = get_option('home');

  	foreach($tags as $tag) {
		if ($tag->count > $average * 20) {
			$tagclass = "taglevel1";
		} else if ($tag->count > $average * 10) {
			$tagclass = "taglevel2";
		} else if ($tag->count > $average * 6) {
			$tagclass = "taglevel3";
		} else if ($tag->count > $average * 4) {
			$tagclass = "taglevel4";
		} else if ($tag->count > $average * 2) {
			$tagclass = "taglevel5";
		} else if ($tag->count > $average) {
			$tagclass = "taglevel6";
		} else if ($tag->count <= $average) {
			$tagclass = "taglevel7";
		}

		$tag_name = strtolower($tag->tag);

		$tag_display = str_replace('_',' ', $tag->tag);
		$tag_display = str_replace('-',' ',$tag_display);

		echo "<a href=\"$siteurl$baseurl$tag_name\" class=\"$tagclass\" title=\"$tag_display ($tag->count)\">$tag_display</a> ";
  	}
}

/* ultimate_show_related_tags($pre="<li>", $post="</li>")
Display a list of tags that are related to the current tag set.  $pre and $post are the prefix and suffix
for each tag.

I can't do subselects on the mySQL version I'm developing on;  so this is done the two-query way.  I figure
it's probably better to do it this way for anyone else who is running older mySQL.
*/

function ultimate_show_related_tags($pre = "<li>", $post = "</li>", $notags="None") {
	global $wpdb, $table_prefix, $posts, $table_prefix, $tableposts, $id, $utw;
	$tabletags = $table_prefix . 'tags';
	$tablepost2tag = $table_prefix . "post2tag";

	$tags = get_query_var("tag");
	$tagset = explode(" ", $tags);
	$taglist = "'" . $tagset[0] . "'";
	$tagcount = count($tagset);
	if ($tagcount > 1) {
		for ($i = 1; $i <= $tagcount; $i++) {
			if ($tagset[$i] <> "") {
				$taglist = $taglist . ", '" . $tagset[$i] . "'";
			}
		}
	}

	$baseurl = get_option('utw_base_url');
	foreach($tagset as $tag) {
		$baseurl .= $tag . "+";
	}

	$siteurl = get_option("home");

	$tags = $utw->GetRelatedTags($utw->GetTagsForTagString($taglist));

	$format = array ("default"=> $pre . "<a href=\"$siteurl$baseurl%tag%\">+ </a>%taglink% (%tagcount%)" . $post);

	echo $utw->FormatTags($tags, $format);
}

function ultimate_delicious_link() {
	global $post, $deliciousAPIURL, $wpdb, $table_prefix;
	$tabletags = $table_prefix . 'tags';
	$tablepost2tag = $table_prefix . "post2tag";

	if (is_numeric($post->ID)) {
	$q = <<<SQL
select tag from $tabletags t inner join $tablepost2tag p2t on t.id = p2t.tag_id
where p2t.post_id = $post->ID
order by tag asc
SQL;
	$tags = $wpdb->get_results($q);
	if ($tags) {
		foreach($tags as $tag) {
			$taglist .= $tag->tag . " ";
		}
			echo "<a href=\"" . $deliciousAPIURL . "posts/add?description=" . urlencode($post->post_title) . "&url=" . urlencode(get_permalink($post->ID)) . "&tags=$taglist\">Post to del.icio.us</a>";
	} else {
		echo "<a href=\"http://del.icio.us/post?url=" . urlencode(get_permalink($post->ID)) . "&title=" . urlencode($post->post_title) . "\">Post to del.icio.us</a>";
	}
	}

}



?>
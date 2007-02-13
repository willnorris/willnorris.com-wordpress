<?php
$tabletags = $table_prefix . "tags";
$tablepost2tag = $table_prefix . "post2tag";
$tabletag_synonyms = $table_prefix . "tag_synonyms";

$lzndomain = "ultimate-tag-warrior";
$current_build = 7;

$siteurl = get_option('siteurl');
$baseurl = get_option('utw_base_url');
$home = get_option('home');
$iconsettings = explode('|', get_option('utw_icons'));

$prettyurls = get_option('utw_use_pretty_urls');

$trailing = '';
if (get_option('utw_trailing_slash') == 'yes') { $trailing = "/"; }

$maxtagcolour = get_option ('utw_tag_cloud_max_color');
$mintagcolour = get_option ('utw_tag_cloud_min_color');

$maxtagsize = get_option ('utw_tag_cloud_max_font');
$mintagsize = get_option ('utw_tag_cloud_min_font');
$fontunits = get_option ('utw_tag_cloud_font_units');

$notagtext = get_option('utw_no_tag_text');

$_tagweightingcache = array();
$_tagcache = array();
$_posttagcache = array();
$_relatedtagsmap = array();

$typelimitsql = "(post_type = 'post' OR post_type = 'page')";  // include pages
$typelimitsql = "(post_type = 'post')";  // don't include pages - comment this out if you want!!

class UltimateTagWarriorCore {

	/* Comparing x.y.z versions is more effort than I'm prepared
	   to go to.  '*/
	function CheckForInstall($hideerrors = true) {
		global $current_build, $wpdb, $tabletags, $tablepost2tag, $tabletag_synonyms;

		$wpdb->show_errors = !$hideerrors;

		$installed_build = get_option('utw_installed_build');
		if ($installed_build == '') $installed_build = 0;

		if ($installed_build < 1) {
			$q = <<<SQL
			CREATE TABLE IF NOT EXISTS $tabletags (
			  tag_ID int(11) NOT NULL auto_increment,
			  tag varchar(255) NOT NULL default '',
			  PRIMARY KEY  (tag_ID)
			) TYPE=MyISAM;
SQL;

			$wpdb->query($q);

			$q = <<<SQL
			CREATE TABLE IF NOT EXISTS $tablepost2tag (
			  rel_id int(11) NOT NULL auto_increment,
			  tag_id int(11) NOT NULL default '0',
			  post_id int(11) NOT NULL default '0',
			  ip_address varchar(15),
			  PRIMARY KEY  (rel_id)
			) TYPE=MyISAM;
SQL;

			$wpdb->query($q);

			add_option('utw_include_technorati_links', 'yes', 'Indicates whether technorati links should be automatically appended to the content.', 'yes');
			add_option('utw_include_local_links', 'no', 'Indicates whether local tag links should be automatically appended to the content.', 'yes');
			add_option('utw_base_url', '/tag/', 'The base url for tag links i.e. {base url}{sometag}', 'yes');
			add_option('utw_include_categories_as_tags', 'no', 'Will include any selected categories as tags', 'yes');

			add_option('utw_use_pretty_urls', 'no', 'Use /tag/tag urls instead of index.php?tag=tag urls', 'yes');

			add_option('utw_tag_cloud_max_color', '#000000', 'The color of popular tags in tag clouds', 'yes');
			add_option('utw_tag_cloud_min_color', '#FFFFFF', 'The color of unpopular tags in tag clouds', 'yes');

			add_option('utw_tag_cloud_max_font', '250', 'The maximum font size (as a percentage) for popular tags in tag clouds', 'yes');
			add_option('utw_tag_cloud_min_font', '70', 'The minimum font size (as a percentage) unpopular tags in tag clouds', 'yes');

			add_option ('utw_tag_cloud_font_units', '%', 'The units to display the font sizes with, on tag clouds.');

			add_option('utw_tag_line_max_color', '#000000', 'The color of popular tags in a tag line', 'yes');
			add_option('utw_tag_line_min_color', '#FFFFFF', 'The color of unpopular tags in a tag line', 'yes');

			add_option('utw_long_tail_max_color', '#000000', 'The color of popular tags in a long tail chart', 'yes');
			add_option('utw_long_tail_min_color', '#FFFFFF', 'The color of unpopular tags in a long tail chart', 'yes');

			add_option('utw_always_show_links_on_edit_screen', 'no', 'Always display existing tags as links; regardles of how many there are', 'yes');
		}

		if ($installed_build < 2) {
			$alreadyChanged = $wpdb->get_var("SHOW COLUMNS FROM $tabletags LIKE 'tag_id'");
			if ($alreadyChanged == 'tag_ID' || $alreadyChanged == 'tag_id') {
				// do nothing! the column has already been changed; and trying to change it again makes an error.
			} else {
				$q = "ALTER TABLE $tabletags CHANGE id tag_id int(11) AUTO_INCREMENT";
				$wpdb->query($q);
			}
		}

		if ($installed_build < 3) {

			$q = <<<SQL
		CREATE TABLE IF NOT EXISTS $tabletag_synonyms (
		  tagsynonymid int(11) NOT NULL auto_increment,
		  tag_id int(11) NOT NULL default '0',
		  synonym varchar(150) NOT NULL default '',
		  PRIMARY KEY  (`tagsynonymid`)
) TYPE=MyISAM;
SQL;

			$wpdb->query($q);

			$worked = $wpdb->get_var("SHOW TABLES LIKE '$tabletag_synonyms'");

			if ($worked != $tabletag_synonyms) {
				return "Wasn't able to create $tabletag_synonyms";
			}
		}

		if ($installed_build < 6) {
			$alreadyChanged = $wpdb->get_var("SHOW COLUMNS FROM $tablepost2tag LIKE 'ip_address'");
			if ($alreadyChanged == 'ip_address') {
				// do nothing! the column has already been changed; and trying to change it again makes an error.
			} else {
				$q = "ALTER TABLE $tablepost2tag ADD ip_address varchar(15)";
				$wpdb->query($q);

				$changed = $wpdb->get_var("SHOW COLUMNS FROM $tablepost2tag LIKE 'ip_address'");

				if ($changed != 'ip_address') {
					return "Couldn't add ip_address column to $tablepost2tag";
				}
			}
		}

		if ($installed_build < 7) {
			add_option ('utw_no_tag_text', 'No Tags', 'The text to display when there are no tags (can be blank)', 'yes');
		}

		if ($installed_build != $current_build) {
			update_option('utw_installed_build', $current_build);
		}
	}

	function ForceInstall() {
		update_option('utw_installed_build', 0);
		$this->CheckForInstall(false);
	}

	/* Fundamental functions for dealing with tags */
	/* The post corresponding to the postID are updated to be the tags in the list.  Previously assigned
		tags not in the list are deleted. */
	function SaveTags($postID, $tags) {
		global $tabletags, $tablepost2tag, $wpdb, $current_build, $REMOTE_ADDR;

		$tags = array_flip(array_flip($tags));

		foreach($tags as $tag) {
			$tag = trim($tag);

			if ($tag <> "" && is_numeric($postID)) {
				$tag = str_replace(' ', '-', $tag);
				$tag = str_replace('"', '', $tag);
				$tag = str_replace("'", '', $tag);

				$tag = $this->GetCanonicalTag($tag);

				$q = "SELECT tag_id FROM $tabletags WHERE tag='$tag' limit 1";
				$tagid = $wpdb->get_var($q);

				if (is_null($tagid)) {
					$q = "INSERT INTO $tabletags (tag) VALUES ('$tag')";
					$wpdb->query($q);
					$tagid = $wpdb->insert_id;
				}

				$q = "SELECT rel_id FROM $tablepost2tag WHERE post_id = '$postID' AND tag_id = '$tagid'";

				if ( is_null($wpdb->get_var($q))) {
					$q = "INSERT INTO $tablepost2tag (post_id, tag_id, ip_address) VALUES ('$postID','$tagid', '" . $REMOTE_ADDR . "')";

					$wpdb->query($q);
				}

				$taglist .= $tagid . ", ";
			}
		}

		// Remove any tags that are no longer associated with the post.

		if ($taglist == "") {
			// since "not in ()" doesn't play nice.
			$q = "delete from $tablepost2tag where post_id = $postID";
		} else {
			// lop off the trailing space+comma
			$taglist = substr($taglist, 0 ,-2);

			$q = "delete from $tablepost2tag where post_id = $postID and tag_id not in ($taglist)";
		}
		$wpdb->query($q);

		$this->ClearTagPostMeta($postID);
	}

	/* Adds the specified tag to the post corresponding with the post ID */
	function AddTag($postID, $tag) {
		global $tabletags, $tablepost2tag, $wpdb;

		$tag = trim($tag);

		if ($tag <> "" && is_numeric($postID)) {
			$tag = str_replace(' ', '-', $tag);
			$tag = str_replace('"', '', $tag);
			$tag = str_replace("'", '', $tag);

			$tag = $this->GetCanonicalTag($tag);

			$q = "SELECT tag_id FROM $tabletags WHERE tag='$tag' limit 1";
			$tagid = $wpdb->get_var($q);

			if (is_null($tagid)) {
				$q = "INSERT INTO $tabletags (tag) VALUES ('$tag')";
				$wpdb->query($q);
				$tagid = $wpdb->insert_id;
			}

			$q = "SELECT rel_id FROM $tablepost2tag WHERE post_id = '$postID' AND tag_id = '$tagid'";

			if ( is_null($wpdb->get_var($q))) {
				$q = "INSERT INTO $tablepost2tag (post_id, tag_id) VALUES ('$postID','$tagid')";
				$wpdb->query($q);
			}
		}
		$this->ClearTagPostMeta($postID);
	}

	/* Adds the specified tag to the post corresponding with the post ID */
	function RemoveTag($postID, $tag) {
		global $tabletags, $tablepost2tag, $wpdb;

		if ($tag <> "" && is_numeric($postID)) {

			$tag = str_replace('"','',str_replace("'",'',$tag));

			$q = "SELECT tag_id FROM $tabletags WHERE tag='$tag' limit 1";
			$tagid = $wpdb->get_var($q);

			if (!is_null($tagid)) {
				$q = "DELETE FROM $tablepost2tag WHERE post_id = '$postID' AND tag_id = '$tagid'";

				$wpdb->query($q);
			}

			$q = "SELECT count(*) FROM $tablepost2tag WHERE tag_id = '$tagid'";

			if ( 0 == $wpdb->get_var($q)) {
				$q = "DELETE FROM $tabletags WHERE tag_id = $tagid";
				$wpdb->query($q);
			}
			$this->ClearTagPostMeta($postID);
		}
	}

	/*
	 * Add any categories assigned to the post as tags.  This retains any exising tags.
	 */
	function SaveCategoriesAsTags($postID) {
		global $wpdb, $tablepost2tag, $wpdb;

		if (!is_numeric($postID)) return;

		$categories = $wpdb->get_results("SELECT c.cat_name FROM $wpdb->post2cat p2c INNER JOIN $wpdb->categories c ON p2c.category_id = c.cat_id WHERE p2c.post_id = $postID");
		$tags = $this->GetTagsForPost($postID);

		$alltags = array();
		if ($tags) {
			foreach($tags as $tag) {
				$alltags[] = $tag->tag;
			}
		}

		if ($categories) {
			foreach($categories as $cat) {
				$alltags[] = str_replace(" ", "-", $cat->cat_name);
			}
		}

		if (count($alltags) > 0) {
			$this->SaveTags($postID, $alltags);
		}
	}

	/*
	 * Add any tags, from the specified custom field as tags.  This retains any existing tags.
	 */
	function SaveCustomFieldAsTags($postID, $fieldName, $separator) {
		if (!$fieldName || !$separator) return;

		if (!is_numeric($postID)) return;

		$allExisting = get_post_meta($postID, $fieldName, false);

		$tags = $this->GetTagsForPost($postID);

		$alltags = array();

		if ($tags) {
			foreach($tags as $tag) {
				$alltags[] = $tag->tag;
			}
		}

		foreach ($allExisting as $existing) {
			$items = explode($separator, $existing);
			foreach ($items as $tag) {
				$alltags[] = str_replace(" ", "-", trim($tag));
			}
		}

		if (count($alltags) > 0) {
			$this->SaveTags($postID, $alltags);
		}
	}

	/*
	 * Write the set of tags to the custom field specified.
	 * If the separator is anything but a space; -'s and _' will be converted back to spaces.
	 * NB.  It's generally a good idea to call SaveCustomFieldAsTags first.
	 */
	function SaveTagsAsCustomField($postID, $fieldName, $separator) {
		$tags = $this->GetTagsForPost($postID);

		if ($tags) {
			foreach ($tags as $tag) {
				if ($separator == " ") {
					$tagstr .= $tag->tag . $separator;
				} else {
					$tagstr .= str_replace("-", " ", str_replace("_"," ",$tag->tag)) . $separator;
				}
			}

			$tagstr = substr($tagstr, 0, strlen($separator)*-1);
		}
		delete_post_meta($postID, $fieldName);
		add_post_meta($postID, $fieldName, $tagstr);
	}

   /**
	* Adds any embedded tags to the tags for the post.
	* @param int $postID the ID of a post
	*/
	function SaveEmbeddedTags($postID) {
		$post = &get_post($postID);

		$tags = $this->ParseEmbeddedTags($post->post_content);

		if ($tags) {
			foreach($tags as $tag) {
				$this->AddTag($postID, $tag);
			}
		}
	}

   /**
    * Parses a string looking for tags in single and multiple tag blocks.
	* @param string $text a block of text
	* @param array an array of tag names
	*/
	function ParseEmbeddedTags($text) {
		global $starttag, $endtag, $starttags, $endtags;

		$tags = array();

		$findTagsRegEx = '/(' . UltimateTagWarriorActions::regExEscape($starttags) . '(.*?)' . UltimateTagWarriorActions::regExEscape($endtags) . ')/i';

		preg_match_all($findTagsRegEx, $text, $matches);
		foreach ($matches[2] as $match) {
			foreach(explode(',', $match) as $tag) {
				$tags[] = $tag;
			}
		}


		$findTagRegEx = '/(' . UltimateTagWarriorActions::regExEscape($starttag) . '(.*?)' . UltimateTagWarriorActions::regExEscape($endtag) . ')/i';

		preg_match_all($findTagRegEx, $text, $matches);
		foreach ($matches[2] as $match) {
			foreach(explode(',', $match) as $tag) {
				$tags[] = $tag;
			}
		}

		return $tags;
	}

	function DeleteTags($postID) {
		global $tabletags, $tablepost2tag, $wpdb;

		if (is_numeric($postID)) {
			$query = "DELETE FROM $tablepost2tag WHERE post_id = $postID";
			$wpdb->query($query);
		}
	}

	function DeletePostTags($postID) {
		$this->DeleteTags($postID);
	}

	function GetTagsForTagString($tags) {
		global $wpdb, $tabletags;

		if ($tags) {
			$q = "SELECT * FROM $tabletags WHERE tag IN ($tags)";

			return $wpdb->get_results($q);
		}
	}

	function GetCurrentTagSet() {
		$tags = get_query_var("tag");
		$tagset = explode(" ", $tags);

		if (count($tagset) == 1) {
			$tagset = explode("|", $tags);
		}

		$tagcount = count($tagset);
		$taglist = array();

		if ($tagcount > 0) {
			for ($i = 0; $i < $tagcount; $i++) {
				if (trim($tagset[$i]) <> "") {
					$taglist[] = "'" . str_replace("'",'',str_replace('"','',trim($tagset[$i]))) . "'";
				}
			}
		}

		return ($this->GetTagsForTagString( implode(',',$taglist)));
	}

	function TidyTags() {
		global $wpdb, $tablepost2tag, $tabletags;

		/* Phase 1:  delete the post-tag relationships from posts which have been deleted */
		$q = "SELECT post_id FROM $tablepost2tag left join $wpdb->posts on ID = post_id where ID is null group by post_id";
		$orphanpostids = $wpdb->get_results($q);

		if ($orphanpostids) {
			foreach ($orphanpostids as $orphanpostid) {
				$q = "DELETE FROM $tablepost2tag WHERE post_id = $orphanpostid->post_id";
				$wpdb->query($q);
			}
		}

		/* Phase 2:  delete any tags which are no longer in use */
		$q = "SELECT t.tag_id FROM $tabletags t LEFT JOIN $tablepost2tag p2t ON p2t.tag_id = t.tag_id WHERE p2t.tag_id IS NULL";
		$orphantagids = $wpdb->get_results($q);

		if ($orphantagids) {
			foreach ($orphantagids as $orphantagid) {
				$q = "DELETE FROM $tabletags where tag_id = $orphantagid->tag_id";
				$wpdb->query($q);
			}
		}

		/* Phase 3:  consolidate any duplicate tags */
		$q = "SELECT tag, MIN(tag_id) as lowid, COUNT(*) cnt FROM $tabletags GROUP BY tag HAVING cnt > 1";
		$duplicatetags = $wpdb->get_results($q);

		if ($duplicatetags) {
			foreach($duplicatetags as $duplicatetag) {
				$trueid = $duplicatetag->lowid;

				$duplicatetagids = $wpdb->get_results("SELECT tag_id FROM $tabletags WHERE tag = '$duplicatetag->tag' AND tag_id != $trueid");
				$tagidstr = "";
				if ($duplicatetagids) {
					foreach($duplicatetagids as $tagid) {
						$tagidstr .= $tagid->id . ', ';
					}

					$tagidstr = substr($tagidstr, 0, -2);
				}

				$effectedposts = $wpdb->get_results("SELECT post_id FROM $tablepost2tag WHERE tag_id IN ($tagidstr) OR tag_id = $trueid");

				foreach($effectedposts as $post) {
					if(is_null($wpdb->get_var("SELECT rel_id FROM $tablepost2tag WHERE post_id = $post->post_id AND tag_id = $trueid"))) {
						$wpdb->query("INSERT INTO $tablepost2tag (post_id, tag_id) VALUES ($post->post_id, $trueid)");
					}
				}

				if ($tagidstr) {
					$wpdb->query("DELETE FROM $tablepost2tag WHERE tag_id IN ($tagidstr)");

					$wpdb->query("DELETE FROM $tabletags WHERE tag_id IN ($tagidstr)");
				}
			}
		}
		$this->ClearAllTagPostMeta();
	}








	/* Functions for the tags associated with a post */
	function ShowTagsForPost($postID, $format, $limit=0) {
		echo $this->FormatTags($this->GetTagsForPost($postID, $limit), $format);
	}

	function GetTagsForPost($post, $limit = 0) {
		global $tabletags, $tablepost2tag, $wpdb, $_posttagcache;

		if ($limit != 0 && is_numeric($limit)) {
			$limitclause = "LIMIT $limit";
		}

		$postID = $post;
		if (is_object($post)) {
			$postID = $post->ID;
		}

		if (!is_numeric($postID)) return array();

		if ($_posttagcache[$postID . ':' . $limit]) {
			return $_posttagcache[$postID . ':' . $limit];
		}

		if ($postID) {
			$tags = get_post_meta($postID, '_utw_tags_' . $limit, true); // check the postmeta cache... this is already in memory!
			if ( false == $tags ) {
				$q = "SELECT DISTINCT t.tag FROM $tabletags t INNER JOIN $tablepost2tag p2t ON p2t.tag_id = t.tag_id INNER JOIN $wpdb->posts p ON p2t.post_id = p.ID AND p.ID=$postID ORDER BY t.tag ASC $limitclause";
				$tags = $wpdb->get_results($q);
				if (count($tags) > 0) {
					add_post_meta($postID, '_utw_tags_' . $limit, $tags);
				} else {
					add_post_meta($postID, '_utw_tags_' . $limit, "no tags");
				}
			}
			if ($tags == "no tags") {
				$tags = array();
			}
			$_posttagcache[$postID . ':' . $limit] = $tags;

			return $tags;
		}
	}

	function GetPostsForTag($tag) {
		global $tabletags, $tablepost2tag, $wpdb, $typelimitsql;

		if (is_object($tag)) {
			$tag = $tag->tag;
		}

		$tag = str_replace("'",'',str_replace('"','',$tag));


		$now = current_time('mysql', 1);

		   $q = <<<SQL
		SELECT * from
			$tabletags t, $tablepost2tag p2t, $wpdb->posts p
		WHERE t.tag_id = p2t.tag_id
		  AND p.ID = p2t.post_id
		  AND t.tag = '$tag'
		  AND post_date_gmt < '$now'
		  AND $typelimitsql
		ORDER BY post_date desc
SQL;

		   return ($wpdb->get_results($q));
	}

	function GetPostsForAnyTags($tags) {
		global $tabletags, $tablepost2tag, $wpdb,$typelimitsql;

		$taglist = "'" . str_replace("'",'',str_replace('"','',urldecode($tags[0]->tag))). "'";
		$tagcount = count($tags);
		if ($tagcount > 1) {
			for ($i = 1; $i <= $tagcount; $i++) {
				$taglist = $taglist . ", '" . str_replace("'",'',str_replace('"','',urldecode($tags[$i]->tag))) . "'";
			}
		}

		$now = current_time('mysql', 1);

		   $q = <<<SQL
		SELECT *
			 FROM $tablepost2tag p2t, $tabletags t, $wpdb->posts p
			 WHERE p2t.tag_id = t.tag_id
			 AND p2t.post_id = p.ID
			 AND (t.tag IN ($taglist))
			 AND post_date_gmt < '$now'
			 AND $typelimitsql
			 GROUP BY p2t.post_id
			 ORDER BY p.post_title ASC
SQL;

		   return ($wpdb->get_results($q));
	}

	function GetPostsForTags($tags) {
		global $tabletags, $tablepost2tag, $wpdb, $typelimitsql;

		$taglist = "'" . str_replace("'",'',str_replace('"','',urldecode($tags[0]->tag))). "'";
		$tagcount = count($tags);
		if ($tagcount > 1) {
			for ($i = 1; $i <= $tagcount; $i++) {
				$taglist = $taglist . ", '" . str_replace("'",'',str_replace('"','',urldecode($tags[$i]->tag))) . "'";
			}
		}

		$now = current_time('mysql', 1);

		   $q = <<<SQL
		SELECT *
			 FROM $tablepost2tag p2t, $tabletags t, $wpdb->posts p
			 WHERE p2t.tag_id = t.tag_id
			 AND p2t.post_id = p.ID
			 AND (t.tag IN ($taglist))
			 AND post_date_gmt < '$now'
			 AND $typelimitsql
			 GROUP BY p2t.post_id
			 HAVING COUNT(p2t.post_id)=$tagcount
			 ORDER BY p.post_title ASC
SQL;

		   return ($wpdb->get_results($q));
	}

	function GetPostHasTags($postID) {
		global $tabletags, $tablepost2tag, $wpdb;

		if (is_numeric($postID)) {
			$q = "SELECT count(*) FROM $tabletags t INNER JOIN $tablepost2tag p2t ON p2t.tag_id = t.tag_id INNER JOIN $wpdb->posts p ON p2t.post_id = p.ID AND p.ID=$postID";
			return($wpdb->get_var($q) > 0);
		} else {
			return false;
		}
	}

	function ClearTagPostMeta($postid=0) {
		global $wpdb;
		$postid = (int) $postid;
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_utw_tags_%' AND post_id = '$postid'");
		return $postid;
	}

	function ClearAllTagPostMeta() {
		global $wpdb;
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_utw_tags_%'");
	}

	function ClearSynonymsForTag($tagid="") {
		global $tabletags, $tabletag_synonyms, $wpdb;

		if ($tag) {
			if (is_object($tag)) {
				$tag = $tag->tag;
			}
			// XXX: Fix me when you need me.
		} else if (is_numeric($tagid)) {
			return $wpdb->query("DELETE FROM $tabletag_synonyms WHERE tag_id = $tagid");
		}
	}

	function GetSynonymsForTag($tag="", $tagid="") {
		global $tabletags, $tabletag_synonyms, $wpdb;

		if ($tag) {
			if (is_object($tag)) {
				$tag = $tag->tag;
			}
			$tag = str_replace("'",'',str_replace('"','',$tag));

			return $wpdb->get_results("SELECT ts.synonym as tag, ts.tagsynonymid as tag_id FROM $tabletags t INNER JOIN $tabletag_synonyms ts ON t.tag_id = ts.tag_id WHERE t.tag = '$tag'");
		} else if (is_numeric($tagid)) {
			return $wpdb->get_results("SELECT ts.synonym as tag, ts.tagsynonymid as tag_id FROM $tabletag_synonyms ts WHERE ts.tag_id = $tagid");
		}
	}

	function ShowSynonymsForTag($tag, $format, $limit=0) {
		echo $this->FormatTags($this->GetSynonymsForTag($tag), $format);
	}

	function AddSynonymForTag($tag='', $tagid='', $synonym) {
		global $tabletags, $tabletag_synonyms, $wpdb;

		$synonym = trim($synonym);
		$synonym = str_replace("'",'',str_replace('"','',$synonym));

		$q = "SELECT count(*) FROM $tabletags WHERE tag = '$synonym'";

		if ($wpdb->get_var($q) == 0) {
			if (!$tagid) {
				$tag = str_replace("'",'',str_replace('"','',$tag));
				$tagid = $wpdb->get_var("SELECT tag_id FROM $tabletags WHERE tag = '$tag'");
			}

			if ($tagid && is_numeric($tagid)) {
				$wpdb->query("INSERT INTO $tabletag_synonyms (tag_id, synonym) VALUES ($tagid, '$synonym')");
			} else {
				return "Tag $tagid doesn't exist!";
			}
		} else {
			return "$synonym already exists as a tag.";
		}
	}



	function GetCanonicalTag($tag) {
		global $tabletags, $tabletag_synonyms, $wpdb;

		$tag = str_replace("'",'',str_replace('"','', str_replace('\\','',$tag)));

		$truetag = $wpdb->get_var("select tag from $tabletags where tag = '$tag'");

		if ($truetag != "") {
			return $truetag;
		} else {
			$synonym = $wpdb->get_var("select t.tag from $tabletags t INNER JOIN $tabletag_synonyms ts ON t.tag_id = ts.tag_id WHERE synonym = '$tag'");

			if ($synonym != "") {
				return $synonym;
			}
		}
		return $tag;
	}













	/* Functions for the related tags */
	function ShowRelatedTags($tags, $format, $limit=0) {
		echo $this->FormatTags($this->GetRelatedTags($tags, $limit), $format);
	}

	function GetRelatedTags($tags, $limit = 0) {
		global $wpdb, $tabletags, $tablepost2tag, $typelimitsql;

		$now = current_time('mysql', 1);

		$taglist = "'" . str_replace("'",'',str_replace('"','',urldecode($tags[0]->tag))). "'";
		$tagcount = count($tags);
		if ($tagcount > 1) {
			for ($i = 1; $i <= $tagcount; $i++) {
				$taglist = $taglist . ", '" . str_replace("'",'',str_replace('"','',urldecode($tags[$i]->tag))) . "'";
			}
		}

		$q = <<<SQL
		SELECT p2t.post_id
			 FROM $tablepost2tag p2t, $tabletags t, $wpdb->posts p
			 WHERE p2t.tag_id = t.tag_id
			 AND p2t.post_id = p.ID
			 AND (t.tag IN ($taglist))
			 AND post_date_gmt < '$now'
			 AND $typelimitsql
			 GROUP BY p2t.post_id HAVING COUNT(p2t.post_id)=$tagcount
			 ORDER BY t.tag ASC
SQL;

		$postids = $wpdb->get_results($q);

		if ($postids) {

			$postidlist = $postids[0]->post_id;

			for ($i = 1; $i <= count($postids); $i++) {
				$postidlist = $postidlist . ", '" . $postids[$i]->post_id . "'";
			}

			if ($limit != 0) {
				$limitclause = "LIMIT $limit";
			}

			$q = <<<SQL
		SELECT t.*, COUNT(p2t.post_id) AS count
		FROM $tablepost2tag p2t, $tabletags t, $wpdb->posts p
		WHERE p2t.post_id IN ($postidlist)
		AND p2t.post_id = p.ID
		AND t.tag NOT IN ($taglist)
		AND t.tag_id = p2t.tag_id
		AND post_date_gmt < '$now'
		AND $typelimitsql
		GROUP BY p2t.tag_id
		ORDER BY count DESC, t.tag ASC
		$limitclause
SQL;

			return $wpdb->get_results($q);
		}
	}

	function GetRelatedTagsMap() {
		global $wpdb, $tablepost2tag, $_relatedtagsmap;

		if (count($_relatedtagsmap) == 0) {
			$q = "select tag.tag_id as tagid, related.tag_id as relatedid from $tablepost2tag tag inner join $tablepost2tag related on tag.post_id = related.post_id and tag.tag_id != related.tag_id";

			$relatedrows = $wpdb->get_results($q);
			if ($relatedrows) {
				foreach($relatedrows as $row) {
					if (!is_array($_relatedtagsmap[$row->tagid]) || !in_array($row->relatedid, $_relatedtagsmap[$row->tagid])) {
						$_relatedtagsmap[$row->tagid][] = $row->relatedid;
					}
				}
			}
		}

		return $_relatedtagsmap;
	}

	function ShowRelatedPosts($tags, $format, $limit=0) {
		echo $this->FormatPosts($this->GetRelatedPosts($tags, $limit), $format);
	}

	function GetRelatedPosts($tags, $limit = 0) {
		global $wpdb, $tabletags, $tablepost2tag, $post, $typelimitsql;

		$now = current_time('mysql', 1);

		$taglist = "'" . str_replace("'",'',str_replace('"','',urldecode($tags[0]->tag))). "'";
		$tagcount = count($tags);
		if ($tagcount > 1) {
			for ($i = 1; $i <= $tagcount; $i++) {
				$taglist = $taglist . ", '" . str_replace("'",'',str_replace('"','',urldecode($tags[$i]->tag))) . "'";
			}
		}

		if ($post->ID) {
			$notclause = "AND p.ID != $post->ID";
		}

		if ($limit != 0) {
			$limitclause = "LIMIT $limit";
		}

		$q = <<<SQL
		SELECT DISTINCT p.*, count(p2t.post_id) as cnt
			 FROM $tablepost2tag p2t, $tabletags t, $wpdb->posts p
			 WHERE p2t.tag_id = t.tag_id
			 AND p2t.post_id = p.ID
			 AND (t.tag IN ($taglist))
			 AND post_date_gmt < '$now'
			 AND $typelimitsql
			 $notclause
			 GROUP BY p2t.post_id
			 ORDER BY cnt DESC, post_date_gmt DESC
			 $limitclause
SQL;

		return $wpdb->get_results($q);
	}











	/* Functions for popular tags */
	function ShowPopularTags($maximum, $format, $order='count', $direction='desc') {
		echo $this->FormatTags($this->GetPopularTags($maximum, $order, $direction), $format);
	}

	function GetPopularTags($maximum, $order, $direction) {
		global $wpdb, $tabletags, $tablepost2tag, $typelimitsql;

		if ($order <> "tag" && $order <> "count") { $order = "tag"; }
		if ($direction <> "asc" && $direction <> "desc") { $direction = "asc"; }

		$now = current_time('mysql', 1);

		$query = <<<SQL
			select tag, t.tag_id, count(p2t.post_id) as count
			from $tabletags t inner join $tablepost2tag p2t on t.tag_id = p2t.tag_id
							  inner join $wpdb->posts p on p2t.post_id = p.ID
			 WHERE post_date_gmt < '$now'
			 AND $typelimitsql
			group by t.tag
			having count > 0
			order by $order $direction
SQL;
		if ($maximum > 0) {
			$query .= " limit $maximum";
		}

		return $wpdb->get_results($query);
	}

	function GetWeightedTags($order, $direction, $limit = 150, $date_sensitive = false) {
		global $wpdb, $tabletags, $tablepost2tag, $_tagweightingcache, $typelimitsql;

		if ($order <> "tag" && $order <> "weight") { $order = "weight"; }
		if ($direction <> "asc" && $direction <> "desc") { $direction = "desc"; }


		if ($order == "tag" && $direction == "asc") {
			$sort = "SortWeightedTagsAlphaAsc";
			$orderclause = "order by weight desc";
		} else if ($order == "tag" && $direction == "desc") {
			$sort = "SortWeightedTagsAlphaDesc";
			$orderclause = "order by weight desc";
		} else if ($order == "weight" && $direction == "asc") {
			$sort = "SortWeightedTagsWeightAsc";
			$orderclause = "order by weight asc";
		} else if ($order == "weight" && $direction == "desc") {
			$sort = "SortWeightedTagsWeightDesc";
			$orderclause = "order by weight desc";
		}

		if ($date_sensitive) {
			$dateclause = $this->GetDateSQL();
		}

		$totaltags = $this->GetDistinctTagCount($date_sensitive);
		$maxtag = $this->GetMostPopularTagCount($date_sensitive);

		if ($totaltags == 0 || $maxtag == 0) {
			return;
		}

		$now = current_time('mysql', 1);

		if ($limit != 0) {
			$limitclause = "LIMIT $limit";
		}

		$query = <<<SQL
			select tag, t.tag_id, count(p2t.post_id) as count, ((count(p2t.post_id)/$totaltags)*100) as weight, ((count(p2t.post_id)/$maxtag)*100) as relativeweight
			from $tabletags t inner join $tablepost2tag p2t on t.tag_id = p2t.tag_id
							  inner join $wpdb->posts p on p2t.post_id = p.ID
			 WHERE post_date_gmt < '$now'
			 AND $typelimitsql
			 $dateclause
			group by t.tag
			$orderclause
			$limitclause
SQL;

		$results = $wpdb->get_results($query);

		if ($results) {
			usort($results, array("UltimateTagWarriorCore",$sort));

			if ($limit != 0) {
				$results = array_slice($results, 0, $limit);
			}

			$distinctweights = array();
			foreach($results as $result) {
				$weight = $result->relativeweight;
				if (!array_key_exists($weight, $distinctweights)) {
					$distinctweights[$weight] = $weight;
				}
			}

			sort($distinctweights, SORT_NUMERIC);

			$finalresults = array();
			foreach($results as $result) {
				$result->weightrank =  ((array_search($result->relativeweight, $distinctweights) + 1) / (count($distinctweights))) * 100;
				$finalresults[] = $result;
			}

			return $finalresults;
		}
	}

	function GetDateSQL () {
		global $year, $monthnum, $day, $hour, $minute, $second;

		if (is_date()) {
			if ($year)
				$dateclause .= ' AND YEAR(post_date)=' . $year;
			if ($monthnum)
				$dateclause .= ' AND MONTH(post_date)=' . $monthnum;
			if ($day && strlen($day) <=2)
				$dateclause .= ' AND DAYOFMONTH(post_date)=' . $day;
			if ($hour)
				$dateclause .= ' AND HOUR(post_date)=' . $hour;
			if ($minute)
				$dateclause .= ' AND MINUTE(post_date)=' . $minute;
			if ($second)
				$dateclause.= ' AND SECOND(post_date)=' . $second;

		}

		return $dateclause;
	}

	function SortWeightedTagsAlphaAsc($x, $y) {
		return strcmp(strtolower($x->tag), strtolower($y->tag));
	}

	function SortWeightedTagsAlphaDesc($x, $y) {
		return strcmp(strtolower($y->tag), strtolower($x->tag));
	}

	function SortWeightedTagsWeightAsc($x, $y) {
		if($x->weight > $y->weight) return 1;
		if($x->weight < $y->weight) return -1;
		return strcmp(strtolower($x->tag), strtolower($y->tag));
	}

	function SortWeightedTagsWeightDesc($x, $y) {
		if($y->weight > $x->weight) return 1;
		if($y->weight < $x->weight) return -1;
		return strcmp(strtolower($y->tag), strtolower($x->tag));
	}

	function GetDistinctTagCount($date_sensitive=false) {
		global $wpdb, $tablepost2tag, $typelimitsql;

		$sql = "select count(*) from $tablepost2tag p2t inner join $wpdb->posts p on p2t.post_id = p.ID WHERE post_date_gmt < '" . current_time('mysql', 1) . "' AND " . $typelimitsql;

		if ($date_sensitive) {
			$sql .= " " . $this->GetDateSQL();
		}
		return $wpdb->get_var($sql);
	}

	function GetMostPopularTagCount($date_sensitive = false) {
		global $wpdb, $tabletags, $tablepost2tag, $typelimitsql;

		$sql = "select count(p2t.post_id) cnt from $tabletags t inner join $tablepost2tag p2t on t.tag_id = p2t.tag_id inner join $wpdb->posts p on p2t.post_id = p.ID WHERE post_date_gmt < '" . current_time('mysql', 1) . "' AND " . $typelimitsql;

		if ($date_sensitive) {
			$sql .= " " . $this->GetDateSQL();
		}

		$sql .= " group by t.tag order by cnt desc limit 1";

		return $wpdb->get_var($sql);
	}








	/* Functions for formatting things*/
	function FormatTags($tags, $format, $limit = 0) {
		if (is_array($format) && $format["pre"]) {
			$out .= $this->FormatTag(null, $format["pre"]);
		}

		if ($limit != 0 && is_array($tags)) {
			$tags = array_slice($tags, 0, $limit);
		}

		if ((!is_array($tags) || count($tags) == 1) && $tags[0] && (is_array($format) && $format["single"])) {
			$out .= $this->FormatTag($tags[0], $format["single"]);
		} else {

			if ($tags) {
				for ($i = 0; $i < count($tags); $i++) {
					if (is_array($format)) {
						if ($i == 0 && $format["first"]) {
							$out .= $this->FormatTag($tags[$i], $format["first"]);
						} else if ($i == (count($tags) -1) && $format["last"]) {
							$out .= $this->FormatTag($tags[$i], $format["last"]);
						} else {
							$out .= $this->FormatTag($tags[$i], $format["default"]);
						}
					} else {
						$out .= $this->FormatTag($tags[$i], $format);
					}
				}
			} else {
				if (is_array($format) && $format["none"]) {
					$out .= $format["none"];
				}
			}
		}

		if (is_array($format) && $format["post"]) {
			$out .= $this->FormatTag(null, $format["post"]);
		}

		return $out;
	}

	function FormatTag($tag, $format) {
		global $install_directory, $baseurl, $home, $siteurl, $prettyurls, $iconsettings, $trailing;

		$tag_display = str_replace('_',' ', $tag->tag);
		$tag_display = str_replace('-',' ',$tag_display);
		$tag_display = stripslashes($tag_display);
		$tag_name = strtolower($tag->tag);
		$tag_name_url = urlencode(stripslashes($tag_name));

		$trati_tag_name = str_replace(' ', '+', $tag_display);
		$flickr_tag_name = str_replace(' ', '', $tag_display);
		$wiki_tag_name = str_replace(' ', '_', $tag_display);
		$gada_tag_name = str_replace(' ', '.',$tag_display);

		$tagset = array();
		$tags = get_query_var("tag");

		$type = "none";

		if ($tags <> "") {
			$tagset = explode(" ", $tags);
			if (count($tagset) == 1) {
				$tagset = explode("|", $tags);
				if (count($tagset) <> 1) {
					$type = "or";
				} else {
					if (strtolower($tagset[0]) == strtolower($tag->tag)) {
						$type = "none";
					} else {
						$type = "single";
					}
				}
			} else {
				$type = "and";
			}
		}

		$tagset = array_unique($tagset);

		$iconformat = '';
		foreach($iconsettings as $iconsetting) {
			switch($iconsetting) {
				case 'Technorati':
					$iconformat .= '%technoratiicon%';
					break;
				case 'Flickr':
					$iconformat .= '%flickricon%';
					break;

				case 'delicious':
					$iconformat .= '%deliciousicon%';
					break;

				case 'Wikipedia':
					$iconformat .= '%wikipediaicon%';
					break;

				case 'gadabe':
					$iconformat .= '%gadabeicon%';
					break;

				case 'Zniff':
					$iconformat .= '%znifficon%';
					break;

				case 'RSS':
					$iconformat .= '%rssicon%';
					break;
			}
		}

		global $post;

		// This feels so... dirty.
		if ($prettyurls == "yes") {

			$format = str_replace('%tagurl%', "$home$baseurl$tag_name_url$trailing", $format);
			$format = str_replace('%taglink%', "<a href=\"$home$baseurl$tag_name_url$trailing\" rel=\"tag\">$tag_display</a>", $format);

			$rssurl = "$home$baseurl$tag_name_url/feed/rss2";

			$tagseturl = "$home$baseurl" . implode('+', $tagset) . 	"+$tag_name_url$trailing";
			$unionurl = "$home$baseurl" . implode('|', $tagset) . 	"|$tag_name_url$trailing";

			$tagpageurl =  "$home$baseurl" . implode('+', $tagset);
			$tagpageunionurl =  "$home$baseurl" . implode('|', $tagset);

			if($trailing == '') {
				$tagsetfeedsuffix = '/feed/rss2';
			} else {
				$tagsetfeedsuffix = 'feed/rss2';
			}
		} else {
			$format = str_replace('%tagurl%', "$home/index.php?tag=$tag_name_url", $format);
			$format = str_replace('%taglink%', "<a href=\"$home/index.php?tag=$tag_name_url\" rel=\"tag\">$tag_display</a>", $format);
			$rssurl = "$home/index.php?tag=$tag_name_url&feed=rss2";
			$tagseturl = "$home/index.php?tag=" . implode('+', $tagset) . "+$tag_name_url";
			$unionurl = "$home/index.php?tag=" . implode('|', $tagset) . "|$tag_name_url";

			$tagpageurl =  "$home/index.php?tag=" . implode('+', $tagset);
			$tagpageunionurl =  "$home/index.php?tag=" . implode('|', $tagset);
			$tagsetfeedsuffix = '&feed=rss2';
		}

		$format = str_replace('%icons%', $iconformat, $format);

		if (strpos($format, '%relatedtagids%') !== FALSE) {
			$_relatedtagsmap = $this->GetRelatedTagsMap();

			if ($_relatedtagsmap[$tag->tag_id]) {
				$format = str_replace('%relatedtagids%', implode(',',$_relatedtagsmap[$tag->tag_id]), $format);
			} else {
				$format = str_replace('%relatedtagids%', '', $format);
			}
		}

		$format = str_replace('%technoratiurl%', "http://www.technorati.com/tag/$trati_tag_name", $format);
		$format = str_replace('%flickrurl%', "http://www.flickr.com/photos/tags/$flickr_tag_name", $format);
		$format = str_replace('%deliciousurl%', "http://del.icio.us/tag/$tag_name_url", $format);
		$format = str_replace('%wikipediaurl%', "http://en.wikipedia.org/wiki/$wiki_tag_name", $format);
		$format = str_replace('%gadabeurl%', "http://$gada_tag_name.gada.be", $format);
		$format = str_replace('%zniffurl%', "http://zniff.com/?s=%22$trati_tag_name%22&amp;sort=", $format);
		$format = str_replace ('%rssurl%', $rssurl, $format);

		$format = str_replace('%technoratiicon%', "<a href=\"http://www.technorati.com/tag/$trati_tag_name\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/technoratiicon.jpg\" alt=\"Technorati tag page for %tagdisplay%\"/></a>", $format);
		$format = str_replace('%flickricon%', "<a href=\"http://www.flickr.com/photos/tags/$flickr_tag_name\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/flickricon.jpg\" alt=\"Flickr tag page for %tagdisplay%\"/></a>", $format);
		$format = str_replace('%deliciousicon%', "<a href=\"http://del.icio.us/tag/$tag_name_url\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/deliciousicon.jpg\" alt=\"del.icio.us tag page for %tagdisplay%\"/></a>", $format);
		$format = str_replace('%wikipediaicon%', "<a href=\"http://en.wikipedia.org/wiki/$wiki_tag_name\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/wikiicon.jpg\" alt=\"Wikipedia page for %tagdisplay%\"/></a>", $format);
		$format = str_replace('%gadabeicon%', "<a href=\"http://$gada_tag_name.gada.be\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/gadaicon.jpg\" alt=\"gada.be tag page for %tagdisplay%\"/></a>", $format);
		$format = str_replace('%znifficon%', "<a href=\"http://zniff.com/?s=%22$trati_tag_name%22&amp;sort=\"rel=\"tag\" ><img src=\"$siteurl/wp-content/plugins$install_directory/znifficon.jpg\" alt=\"Zniff tag page for %tagdisplay%\"/></a>", $format);
		$format = str_replace('%rssicon%', "<a href=\"$rssurl\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/rssicon.jpg\" alt=\"RSS feed for %tagdisplay%\" /></a>", $format);

		$format = str_replace('%tag%', $tag_name, $format);
		$format = str_replace('%tagdisplay%', $tag_display, $format);
		$format = str_replace('%tagjsescaped%', str_replace("'","\\'", $tag_name), $format);
		$format = str_replace('%tagid%', $tag->tag_id, $format);

		$format = str_replace('%tagcount%', $tag->count, $format);

		$format = str_replace('%tagweight%', $tag->weight, $format);
		$format = str_replace('%tagweightint%', ceil($tag->weight), $format);
		$format = str_replace("%tagweightcolor%", $this->GetColorForWeight($tag->weight), $format);
		$format = str_replace("%tagweightfontsize%", $this->GetFontSizeForWeight($tag->weight), $format);

		$format = str_replace('%tagrelweight%', $tag->relativeweight, $format);
		$format = str_replace('%tagrelweightint%', ceil($tag->relativeweight), $format);
		$format = str_replace("%tagrelweightcolor%", $this->GetColorForWeight($tag->relativeweight), $format);
		$format = str_replace("%tagrelweightfontsize%", $this->GetFontSizeForWeight($tag->relativeweight), $format);

		$format = str_replace('%tagrelweightrank%', $tag->weightrank, $format);
		$format = str_replace('%tagrelweightrankint%', ceil($tag->weightrank), $format);
		$format = str_replace("%tagrelweightrankcolor%", $this->GetColorForWeight($tag->weightrank), $format);
		$format = str_replace("%tagrelweightrankfontsize%", $this->GetFontSizeForWeight($tag->weightrank), $format);

		$format = str_replace('%technoratitag%', "<a href=\"http://www.technorati.com/tag/$trati_tag_name\" rel=\"tag\">$tag_display</a>", $format);
		$format = str_replace('%flickrtag%', "<a href=\"http://www.flickr.com/photos/tags/$flickr_tag_name\" rel=\"tag\">$tag_display</a>", $format);
		$format = str_replace('%delicioustag%', "<a href=\"http://del.icio.us/tag/$tag_name\" rel=\"tag\">$tag_display</a>", $format);
		$format = str_replace('%wikipediatag%', "<a href=\"http://en.wikipedia.org/wiki/$wiki_tag_name\" rel=\"tag\">$tag_display</a>", $format);
		$format = str_replace('%gadabetag%', "<a href=\"http://$gada_tag_name.gada.be\" rel=\"tag\">$tag_display</a>", $format);
		$format = str_replace('%znifftag%', "<a href=\"http://zniff.com/?s=%22$trati_tag_name%22&amp;sort=\"rel=\"tag\">$tag_display</a>", $format);
		$format = str_replace('%rsstag%', "<a href=\"$rssurl\" rel=\"tag\">RSS</a>", $format);

		$format = str_replace('%intersectionurl%', $tagseturl, $format);
		$format = str_replace('%unionurl%', $unionurl, $format);

		if ($type == "and" || $type == "single") {
			$format = str_replace('%intersectionicon%', "<a href=\"$tagseturl\"><img src=\"$siteurl/wp-content/plugins$install_directory/intersectionicon.jpg\" /></a>", $format);
			$format = str_replace('%intersectionlink%', "<a href=\"$tagseturl\">+</a>", $format);
			$format = str_replace('%tagsetrssicon%', "<a href=\"$tagpageurl$tagsetfeedsuffix\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/rssicon.jpg\" alt=\"RSS feed for current tagset\" /></a>", $format);
		} else {
			$format = str_replace('%intersectionicon%','',$format);
			$format = str_replace('%intersectionlink%','',$format);
		}

		if ($type == "or" || $type == "single") {
			$format = str_replace('%unionicon%', "<a href=\"$unionurl\"><img src=\"$siteurl/wp-content/plugins$install_directory/unionicon.jpg\" /></a>", $format);
			$format = str_replace('%unionlink%', "<a href=\"$unionurl\">|</a>", $format);
			$format = str_replace('%tagsetrssicon%', "<a href=\"$tagpageunionurl$tagsetfeedsuffix\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/rssicon.jpg\" alt=\"RSS feed for current tagset\" /></a>", $format);

		} else {
			$format = str_replace('%unionicon%','',$format);
			$format = str_replace('%unionlink%','',$format);
		}

		if ($type == "or") {
			$format = str_replace('%operatortext%', 'or',$format);
			$format = str_replace('%operatorsymbol%', '|',$format);
		} else if ($type == "and") {
			$format = str_replace('%operatortext%', 'and',$format);
			$format = str_replace('%operatorsymbol%', '+',$format);
		} else {
			$format = str_replace('%operatortext%', '',$format);
			$format = str_replace('%operatorsymbol%', '',$format);
			$format = str_replace('%tagsetrssicon%', "<a href=\"$rssurl\" rel=\"tag\"><img src=\"$siteurl/wp-content/plugins$install_directory/rssicon.jpg\" alt=\"RSS feed for %tagdisplay%\" /></a>",$format);
		}


		if ($post->ID) {
			$format = str_replace('%postid%', $post->ID, $format);
		} else {
			$format = str_replace('%postid%', $_REQUEST["post"], $format);
		}

		return $format;
	}

	function FormatPosts($posts, $format) {
		if (is_array($format) && $format["pre"]) {
			$out .= $format["pre"];
		}

		if ($posts) {
			for ($i = 0; $i < count($posts); $i++) {
				if (is_array($format)) {
					if ($i == 0 && $format["first"]) {
						$out .= $this->FormatPost($posts[$i], $format["first"]);
					} else if ($i == (count($posts) -1) && $format["last"]) {
						$out .= $this->FormatPost($posts[$i], $format["last"]);
					} else {
						$out .= $this->FormatPost($posts[$i], $format["default"]);
					}
				} else {
					$out .= $this->FormatPost($posts[$i], $format);
				}
			}
		} else {
			if (is_array($format) && $format["none"]) {
				$out .= $format["none"];
			}
		}

		if (is_array($format) && $format["post"]) {
			$out .= $format["post"];
		}

		return $out;
	}

	function FormatPost($post, $format) {
		$url = get_permalink($post->ID);

		$format = str_replace('%title%', $post->post_title, $format);
		$format = str_replace('%postlink%', "<a href=\"$url\">$post->post_title</a>", $format);
		$format = str_replace('%excerpt%', $post->post_excerpt, $format);
		$format = str_replace('%postdate%',mysql2date(get_settings("date_format"), $post->post_date),$format);
		$format = str_replace('%content%', $post->post_content, $format);

		return $format;
	}

	var $predefinedFormats = array();

	function GetFormat($namedType, $additionalFormatting) {
		$baseFormat = $this->GetFormatForType($namedType);

		if ('' != $additionalFormatting) {
			if (is_array($additionalFormatting)) {
				foreach($additionalFormatting as $format=>$value) {
					$baseFormat[$format] = $value;
				}
			} else {
				if (is_array($baseFormat)) {
					$baseFormat['default'] = $additionalFormatting;
				} else {
					$baseFormat = $additionalFormatting;
				}
			}
		}
		return $baseFormat;
	}

	function GetFormatForType($formattype) {
		global $user_level, $post, $lzndomain, $predefinedFormats, $install_directory, $notagtext;

		if ($post->ID) {
			$postid = $post->ID;
		} else {
			$postid = $_REQUEST["post"];
		}

		if (count($predefinedFormats) == 0) {
			$siteurl = get_option('siteurl');

			$predefinedFormats["tagsetsimplelist"] = array('first'=>'%taglink%', 'default'=>' %operatortext% %taglink%');
			$predefinedFormats["tagsetcommalist"] = array('first'=>'%taglink%', 'default'=>', %taglink%', 'last'=>' %operatortext% %taglink%');
			$predefinedFormats["tagsettextonly"] = array('first'=>'%tagdisplay%','default'=>', %tagdisplay%','last'=>' %operatortext% %tagdisplay%');
			$predefinedFormats["simplelist"] = array ("default"=>"%taglink% ", "none"=>$notagtext );
			$predefinedFormats["iconlist"] = array ("default"=>"%taglink% %icons% ", "none"=>$notagtext );
			$predefinedFormats["htmllist"] = array ("default"=>"<li>%taglink%</li>", "none"=>"<li>" . $notagtext . "</li>");
			$predefinedFormats["htmllisticons"] = array ("default"=>"<li>%icons%%taglink%</li>", "none"=>"<li>" . $notagtext . "</li>");
			$predefinedFormats["htmllistandor"] = array ("default"=>"<li>%taglink% %intersectionlink% %unionlink%</li>","none"=>"<li>" . $notagtext . "</li>");
			$predefinedFormats["commalist"] = array ("default"=>", %taglink%", "first"=>"%taglink%", "none"=>$notagtext );
			$predefinedFormats["commalistwithtaglabel"] = array ("single"=>"Tag:  %taglink%", "default"=>", %taglink%", "first"=>"Tags: %taglink%", "none"=>$notagtext );
			$predefinedFormats["commalisticons"] = array ("default"=>", %taglink% %icons%", "first"=>"%taglink% %icons%", "none"=>$notagtext );
			$predefinedFormats["invisibletechnoraticommalist"] = array ("pre"=>'<span style="display:none">', "default"=>", %technoratitag%", "first"=>"%technoratitag%", 'post'=>'</span>' );
			$predefinedFormats["technoraticommalist"] = array ("default"=>", %technoratitag%", "first"=>"%technoratitag%", "none"=>$notagtext );
			$predefinedFormats["technoraticommalistwithlabel"] = array ("default"=>", %technoratitag%", "first"=>"Technorati Tags: %technoratitag%", "none"=>$notagtext );
			$predefinedFormats["technoraticommalistwithiconlabel"] = array ("default"=>", %technoratitag%", "first"=>"<a href=\"http://www.technorati.com/tag/\"><img src=\"$siteurl/wp-content/plugins$install_directory/technoratiicon.jpg\" alt=\"Technorati\"/></a> %technoratitag%", "none"=>$notagtext );
			$predefinedFormats["gadabecommalist"] = array ("default"=>", %gadabetag%", "first"=>"%gadabetag%", "none"=>$notagtext );
			$predefinedFormats["andcommalist"] = array ("default"=>", %taglink% %intersectionlink% %unionlink%", "first"=>"%taglink% %intersectionlink%%unionlink%", "none"=>$notagtext );

			$relStr = "";
			if ($formattype == "superajaxrelated" || $formattype == "superajaxrelateditem") {
				$relStr = "rel";
			}

			$default = "<span id=\"tags-%postid%-%tag%\">%taglink%";
			if ($user_level > 3 && $postid != "") {
				if ($formattype == 'superajaxrelated' || $formattype == 'superajaxrelateditem') {
					$default .= "[<a href=\"javascript:sndReq('add', '%tag%', '%postid%', '$formattype')\">+</a>]";
				} else {
					$default .= "[<a href=\"javascript:sndReq('del', '%tag%', '%postid%', '$formattype')\">-</a>]";
				}
				$aft = " <input type=\"text\" size=\"9\" id=\"addTag-%postid%\" /> <input type=\"button\" value=\"+\" onClick=\"sndReq('add', document.getElementById('addTag-%postid%').value, '%postid%', '$formattype')\" />";
			}

			$default .= "<a href=\"javascript:sndReq('expand$relStr', '%tag%', '%postid%', '$formattype')\">&raquo;</a> </span>";
			$aft .= "</span>";

			$predefinedFormats["superajax"] = array("pre"=>"<script src=\"" . $this->GetAjaxJavascriptUrl() . "\" type=\"text/javascript\"></script><span id=\"tags-%postid%\">","default"=>$default, "post"=>"$aft");;
			$predefinedFormats["superajaxitem"] = $default;
			$predefinedFormats["superajaxrelated"] = $default;
			$predefinedFormats["superajaxrelateditem"] = $default;

			$predefinedFormats["linkset"] = "%taglink% %icons%<a href=\"javascript:sndReq('shrink', '%tag%', '%postid%', 'superajaxitem')\">&laquo;</a>&#160;";
			$predefinedFormats["linksetrel"] = "%taglink% %icons%<a href=\"javascript:sndReq('shrink', '%tag%', '%postid%', 'superajaxrelated')\">&laquo;</a>&#160;";

			$predefinedFormats["weightedlinearbar"] = array("default"=>"<td width=\"%tagweightint%%\" style=\"background-color:%tagrelweightcolor%; border-right:1px solid black;\"><a href=\"%tagurl%\" title=\"%tagdisplay%\" style=\"color:%tagrelweightcolor%;\"><div width=\"100%\">&#160;</div></a></td>", "pre"=>"<table cellpadding=\"0\" cellspacing=\"0\" style=\"border:1px solid black; border-right:0px\" width=\"100%\"><tr>", "post"=>"</tr></table>");

				// Thanks http://www.cssirc.com/codes/?code=23!
				$css = <<<CSS
				<style type="text/css">
				.longtail, .longtail li { list-style: none; margin: 0; padding: 0; }
				.longtail li a {text-decoration:none;}
				.longtail {position: relative; height: 100px;}
				.longtail:after { display: block; visibility: hidden; content: "."; height: 0; overflow: hidden; clear: both;}
				.longtail li {float: left; position: relative; height: 100%;width: 5px;margin:0px;background-color:#fff;}
				.longtail li div {position: absolute;bottom: 0; left: 0;width: 100%;background-color:#000;}
				</style>
CSS;

			$predefinedFormats["weightedlongtail"] = array("pre"=>"$css<ol class=\"longtail\">", "default"=>"<li><a href=\"%tagurl%\" title=\"%tagdisplay%\"><div style=\"height:%tagrelweightint%%\">&#160;</div></a></li>", "post"=>"</ol>");;
			$predefinedFormats["weightedlongtailvertical"] = array("pre"=>"<div class=\"longtailvert\">", "default"=>'<div style="background-color:%tagrelweightrankcolor%; width:%tagrelweightint%%; \"><a href="%tagurl%" title="%tagdisplay% (%tagcount%)" style="display:block; ">%tagdisplay%</a></div>', "post"=>"</div>");
			$predefinedFormats["coloredtagcloud"] = array("default"=>"<a href=\"%tagurl%\" title=\"%tagdisplay% (%tagcount%)\" style=\"color:%tagrelweightrankcolor%\">%tagdisplay%</a> ");
			$predefinedFormats["sizedtagcloud"] = array("default"=>"<a href=\"%tagurl%\" title=\"%tagdisplay% (%tagcount%)\" style=\"font-size:%tagrelweightrankfontsize%\">%tagdisplay%</a> ");
			$predefinedFormats["coloredsizedtagcloud"] = array("default"=>"<a href=\"%tagurl%\" title=\"%tagdisplay% (%tagcount%)\" style=\"font-size:%tagrelweightfontsize%; color:%tagrelweightrankcolor%\">%tagdisplay%</a> ");
			$predefinedFormats["sizedcoloredtagcloud"] = array("default"=>"<a href=\"%tagurl%\" title=\"%tagdisplay% (%tagcount%)\" style=\"font-size:%tagrelweightfontsize%; color:%tagrelweightrankcolor%\">%tagdisplay%</a> ");

			$predefinedFormats["tagcloudlist"] = array("pre"=>"<ol>","default"=>"<li><a href=\"%tagurl%\" title=\"%tagdisplay% (%tagcount%)\" style=\"font-size:%tagrelweightfontsize%; color:%tagrelweightrankcolor%\">%tagdisplay%</a></li>", "post"=>"</ol>");

			$predefinedFormats["invisiblecommalist"] = array ("default"=>", %taglink%", "first"=>"<span style=\"display:none\">%taglink%", "none"=>$notagtext,'post'=>'</span>' );

			// Thanks drac! http://lair.fierydragon.org/
			$predefinedFormats["coloredsizedtagcloudwithcount"] = array("default"=>"<a href=\"%tagurl%\" style=\"font-size:%tagrelweightfontsize%; color:%tagrelweightrankcolor%\">%tagdisplay%<sub style=\"font-size:60%; color:#ccc;\">%tagcount%</sub></a> ");
			$predefinedFormats["postsimplelist"] = array ("default"=>"%postlink%");
			$predefinedFormats["postcommalist"] = array ("default"=>", %postlink%", "first"=>"%postlink%", "none"=> __("No Related Posts", $lzndomain));
			$predefinedFormats["posthtmllist"] = array ("default"=>"<li>%postlink%</li>", "none"=>"<li>" . __("No Related Posts", $lzndomain) . "</li>");
		}

		if (array_key_exists($formattype, $predefinedFormats)) {
			return $predefinedFormats[$formattype];
		} else {
			return "";
		}
	}

	function GetPredefinedFormatNames() {
		global $predefinedFormats;
		if (count($predefinedFormats) == 0) {
			$this->GetFormatForType("");
		}
		return array_keys($predefinedFormats);
	}

	/* This is pretty filthy.  Doing math in hex is much too weird.  It's more likely to work,  this way! */
	function GetColorForWeight($weight) {
		global $maxtagcolour, $mintagcolour;
		if ($weight) {
			$weight = $weight/100;

			$minr = hexdec(substr($mintagcolour, 1, 2));
			$ming = hexdec(substr($mintagcolour, 3, 2));
			$minb = hexdec(substr($mintagcolour, 5, 2));

			$maxr = hexdec(substr($maxtagcolour, 1, 2));
			$maxg = hexdec(substr($maxtagcolour, 3, 2));
			$maxb = hexdec(substr($maxtagcolour, 5, 2));

			$r = dechex(intval((($maxr - $minr) * $weight) + $minr));
			$g = dechex(intval((($maxg - $ming) * $weight) + $ming));
			$b = dechex(intval((($maxb - $minb) * $weight) + $minb));

			if (strlen($r) == 1) $r = "0" . $r;
			if (strlen($g) == 1) $g = "0" . $g;
			if (strlen($b) == 1) $b = "0" . $b;

			return "#$r$g$b";
		}
	}


	function GetFontSizeForWeight($weight) {
		global $maxtagsize, $mintagsize, $fontunits;

		if ($units == "") $units = '%';

		if ($maxtagsize > $mintagsize) {
			$fontsize = (($weight/100) * ($maxtagsize - $mintagsize)) + $mintagsize;
		} else {
			$fontsize = (((100-$weight)/100) * ($maxtagsize - $mintagsize)) + $maxtagsize;
		}

		return intval($fontsize) . $fontunits;
	}
	
	function GetAjaxJavascriptUrl() {
		global $install_directory, $wp_query;

		$rpcurl = get_option('siteurl') . "/wp-content/plugins$install_directory/ultimate-tag-warrior-ajax.php";
		$jsurl = get_option('siteurl') . "/wp-content/plugins$install_directory/ultimate-tag-warrior-ajax-js.php";
		return "$jsurl?ajaxurl=$rpcurl";
	}
}


/* ultimate_get_posts()
Retrieves the posts for the tags specified in get_query_var("tag").  Gets the intersection when there are multiple tags.
*/
function ultimate_get_posts() {
	global $wpdb, $table_prefix, $posts, $id, $wp_query, $request, $utw, $typelimitsql;
	$tabletags = $table_prefix . 'tags';
	$tablepost2tag = $table_prefix . "post2tag";

	$or_query = false;

	$tags = get_query_var("tag");

	$tagset = explode(" ", $tags);

	if (count($tagset) == 1) {
		$tagset = explode("|", $tags);
		$or_query = true;
	}

	$tags = array();
	foreach($tagset as $tag) {
		$tags[] = "'" . stripslashes($utw->GetCanonicalTag($tag)) . "'";
	}

	$tags = array_unique($tags);
	$tagcount = count($tags);
	
	if (strpos($request, "HAVING COUNT(ID)") == false && !$or_query) {
		$request = preg_replace("/ORDER BY/", "HAVING COUNT(ID) = $tagcount ORDER BY", $request);
	}
	$request = preg_replace("/post_type = 'post'/","$typelimitsql", $request);

	$posts = $wpdb->get_results($request);
	// As requested by Splee and copperleaf
	$wp_query->is_home=false;
	// Thanks Mark! http://txfx.net/
	$posts = apply_filters('the_posts', $posts);
	$wp_query->posts = $posts;
	$wp_query->post_count = count($posts);
	update_post_caches($posts);
	if ($wp_query->post_count > 0) {
		$wp_query->post = $wp_query->posts[0];
		// Thanks Bill! http://www.copperleaf.org
       $wp_query->is_404 = false;
   }
}

?>
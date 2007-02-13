<?php
$install_directory = "/UltimateTagWarrior";
$starttag = "[tag]";
$endtag = "[/tag]";

$starttags = "[tags]";
$endtags = "[/tags]";

$embedtags = get_option('utw_use_embedded_tags');

$embeddedtagformat = array('first'=>'%taglink%', 'default'=>', %taglink%');

class UltimateTagWarriorActions {

	/* ultimate_admin_menus
	Adds a tag management page to the menu.
	*/
	function ultimate_admin_menus() {
		// Add a new menu under Manage:
		add_management_page('Tag Management', 'Tags', 8, basename(__FILE__), array('UltimateTagWarriorActions', 'ultimate_better_admin'));

		// And one under options
		add_options_page('Tag Options', 'Tags', 8, basename(__FILE__), array('UltimateTagWarriorActions', 'utw_options'));
	}

/* ultimate_rewrite_rules

*/
function ultimate_rewrite_rules($rules) {
	if(get_option("utw_use_pretty_urls") == "yes") {
		$baseurl = get_option("utw_base_url");

		global $wp_rewrite;

               	$wp_rewrite->add_rewrite_tag('%tag%', '([^/]+)', 'tag=');

		// without trailing slash
		$rules = $wp_rewrite->generate_rewrite_rules($baseurl . '%tag%') + $rules;
		// with trailing slash
		$rules = $wp_rewrite->generate_rewrite_rules($baseurl . '%tag%/') + $rules;
	}
	return $rules;
}

function utw_options() {
	global $lzndomain, $utw, $wpdb, $tabletags, $tablepost2tag, $install_directory;

	$siteurl = get_option('siteurl');

	$autoincludedhelp = 'These settings allow displaying tags automatically in a post.  You can choose whether the tags display before or after the text of your post, and you can have up to two sets of tags.  The base format chooses the basic style of the tags,  and the prefix and suffix allow wrapping the tags in additional HTML such as by adding a "tags" label or icon,  or wrapping an HTML list in &lt;ul&gt; tags.';

	echo '<div class="wrap">';

	$configValues = array();

	$configValues[] = array("setting"=>"", "label"=>__("URL settings", $lzndomain),  "type"=>"label");
	$configValues[] = array("setting"=>"", "value"=>__("These settings control how tags look.  You can change the base url to just about anything you want,  but it should begin and end with a slash.  By default, tag pages are /tag/sometag.  If you want tag pages to be /tag/sometag/ tick the trailing slashes box.", $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_use_pretty_urls", "label"=>__("Use url rewriting for local tag urls (/tag/tag instead of index.php?tag=tag)", $lzndomain),  "type"=>"boolean");
	$configValues[] = array("setting"=>"utw_base_url", "label"=>__("Base url", $lzndomain),  "type"=>"string");
	$configValues[] = array("setting"=>"utw_trailing_slash", 'label'=>__("Include trailing slash on tag urls", $lzndomain), 'type'=>'boolean');

	$configValues[] = array("setting"=>"", "label"=>__("Meta Keywords", $lzndomain),  "type"=>"label");
	$configValues[] = array("setting"=>"", "value"=>__("When enabled, meta keywords will be included in the header of tag pages, and single post pages.  These keywords are sometimes used by search engines.", $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_show_meta_keywords", 'label'=>__("Include meta keywords", $lzndomain), 'type'=>'boolean');

	$configValues[] = array("setting"=>"", "label"=>__("Embedded Tags", $lzndomain),  "type"=>"label");
	$configValues[] = array("setting"=>"", "value"=>__("Embedded tags are tags which are found in the content body of posts.  They use the format of the <a href=\"http://www.broobles.com/scripts/simpletags/\">Simple Tags</a> plugin, which is as good a choice as any.  Tags that are [tag]like this[/tag] will turn into local tag links in the content,  and will be added to the list of tags for the post and [tags]like this, or this[/tags] will be treated as a list of tags for the post, and be added to the list of tags for the post,  and won't display when you view the post.", $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_use_embedded_tags", "label"=>__("Use embedded tags", $lzndomain),  "type"=>"boolean");

	$configValues[] = array("setting"=>"", "label"=>__("Debugging", $lzndomain),  "type"=>"label");

	$configValues[] = array("setting"=>"", "value"=>__("Selecting this option will display some debugging information in HTML comments.  You probably don't need this on (:", $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_debug", 'label'=>__("Include debugging information", $lzndomain), 'type'=>'boolean');

	$configValues[] = array("setting"=>"", "label"=>__("Automatic Feed Tags", $lzndomain),  "type"=>"label");

	$configValues[] = array("setting"=>"", "value"=>__("Selecting this option will append links to the tags to posts in your feed", $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_append_tag_links_to_feed", 'label'=>__("Include local tag links in feeds", $lzndomain), 'type'=>'boolean');

	$configValues[] = array("setting"=>"", "label"=>__("Primary Content Tags", $lzndomain),  "type"=>"label");
	$configValues[] = array("setting"=>"", "value"=>__($autoincludedhelp, $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_include_local_links", "label"=>__("Automatically include primary tag links", $lzndomain),  "type"=>"dropdown", options=>array('No', 'Before Content','After Content'));
	$configValues[] = array("setting"=>'utw_primary_automagically_included_link_format', 'label'=>__('Base format for primary tag links'), 'type'=>'dropdown', 'options'=>$utw->GetPredefinedFormatNames());
	$configValues[] = array("setting"=>'utw_primary_automagically_included_prefix', 'label'=>__('Prefix for primary tag links (optional)'), 'type'=>'string');
	$configValues[] = array("setting"=>'utw_primary_automagically_included_suffix', 'label'=>__('Suffix for primary tag links (optional)'), 'type'=>'string');

	$configValues[] = array("setting"=>"", "label"=>__("Secondary Content Tags", $lzndomain),  "type"=>"label");
	$configValues[] = array("setting"=>"", "value"=>__($autoincludedhelp, $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_include_technorati_links", "label"=>__("Automatically include secondary tag links", $lzndomain),   "type"=>"dropdown", options=>array('No', 'Before Content','After Content'));
	$configValues[] = array("setting"=>'utw_secondary_automagically_included_link_format', 'label'=>__('Base format for secondary tag links'), 'type'=>'dropdown', 'options'=>$utw->GetPredefinedFormatNames());
	$configValues[] = array("setting"=>'utw_secondary_automagically_included_prefix', 'label'=>__('Prefix for secondary tag links (optional)'), 'type'=>'string');
	$configValues[] = array("setting"=>'utw_secondary_automagically_included_suffix', 'label'=>__('Suffix for secondary tag links (optional)'), 'type'=>'string');

	$configValues[] = array("setting"=>"", "label"=>__("Global Formatting Settings", $lzndomain),  "type"=>"label");

	$configValues[] = array("setting"=>"", "value"=>__("The colours are hexadecimal colours,  and need to have the full six digits (#eee is the shorthand version of #eeeeee).  The two font sizes are the size of the largest and smallest tags.  The font size units option determines the units that the two font sizes use.  If you have selected a base format which includes 'icons' in its name,  all of the ticked icon-items will display.", $lzndomain),  "type"=>"help");
	$configValues[] = array("setting"=>"utw_tag_cloud_max_color", "label"=>__("Most popular color", $lzndomain),  "type"=>"color");
	$configValues[] = array("setting"=>"utw_tag_cloud_max_font", "label"=>__("Most popular font size", $lzndomain),  "type"=>"color");
	$configValues[] = array("setting"=>"utw_tag_cloud_min_color", "label"=>__("Least popular color", $lzndomain),  "type"=>"color");
	$configValues[] = array("setting"=>"utw_tag_cloud_min_font", "label"=>__("Least popular font size", $lzndomain),  "type"=>"color");
	$configValues[] = array("setting"=>'utw_tag_cloud_font_units', 'label'=>__('Font size units', $lzndomain), "type"=>"dropdown", "options"=>array('%','pt','px','em'));

	$configValues[] = array("setting"=>'utw_icons', 'label'=>__('Icons to display in icon formats', $lzndomain), "type"=>"multiselect", "options"=>array('Technorati','Flickr','delicious','Wikipedia','gadabe', 'Zniff', 'RSS'));

	$configValues[] = array('setting'=>'utw_no_tag_text', 'label'=>__('The text to display when there are no tags (can be left blank)'), 'type'=>'string');

	$configValues[] = array("setting"=>"", "label"=>__("Editing Options", $lzndomain),  "type"=>"label");
	$configValues[] = array("setting"=>"", "value"=>__("These options are for the editing of tags.  The show existing tags option will include a list of your existing tags on the edit screen for easy addition to posts.  The dropdown option will display an alphabetised dropdown list and the tag list option provides a simple list of tags.  The save categories as tags option will add any selected categories as tags in addition to any tags which are specified.", $lzndomain),  "type"=>"help");

	$configValues[] = array("setting"=>"utw_always_show_links_on_edit_screen", "label"=>__("Show existing tags on post editing page", $lzndomain),  "type"=>"dropdown", "options"=>array('none', 'dropdown', 'tag list'));
	$configValues[] = array("setting"=>"utw_include_categories_as_tags", "label"=>__("Automatically add categories as tags", $lzndomain),  "type"=>"boolean");



	if ($_POST["action"] == "saveconfiguration") {
		foreach($configValues as $setting) {
			if ($setting['type'] == 'multiselect') {
				$options = '|';

				foreach($setting['options'] as $option) {
					$options .= $_POST[$setting['setting'] . ":" . $option] . '|';
				}
				update_option($setting['setting'], $options);
			} else if ($setting['type'] != 'label') {
				update_option($setting['setting'], $_POST[$setting['setting']]);
			}
		}
		echo "<div class=\"updated\"><p>Updated settings</p></div>";
	}

	echo "<fieldset class=\"options\"><legend>" . __("Help!", $lzndomain) . "</legend><a href=\"$siteurl/wp-content/plugins$install_directory/ultimate-tag-warrior-help.html\" target=\"_new\">" . __("Local help", $lzndomain) . "</a> | <a href=\"http://www.neato.co.nz/ultimate-tag-warrior\" target=\"_new\">" . __("Author help", $lzndomain) . "</a> | <a href=\"./edit.php?page=ultimate-tag-warrior-actions.php\">Manage Tags</a></fieldset>";
	echo '<fieldset class="options"><legend>' . __('Configuration', $lzndomain) . '</legend>';
	echo "<form method=\"POST\">";
	echo '<table width="100%" cellpadding="3" cellspacing="0">';

	foreach($configValues as $setting) {
		if ($setting['type'] == 'boolean') {
			UltimateTagWarriorActions::show_toggle($setting['setting'], $setting['label'], get_option($setting['setting']));
		}

		if ($setting['type'] == 'string') {
			UltimateTagWarriorActions::show_string($setting['setting'], $setting['label'], get_option($setting['setting']));
		}

		if ($setting['type'] == 'color') {
			UltimateTagWarriorActions::show_color($setting['setting'], $setting['label'], get_option($setting['setting']));
		}

		if ($setting['type'] == 'label') {
			UltimateTagWarriorActions::show_label($setting['setting'], $setting['label'], get_option($setting['setting']));
		}
		if ($setting['type'] == 'dropdown') {
			UltimateTagWarriorActions::show_dropdown($setting['setting'], $setting['label'], get_option($setting['setting']), $setting['options']);
		}

		if ($setting['type'] == 'multiselect') {
			UltimateTagWarriorActions::show_multiselect($setting['setting'], $setting['label'], get_option($setting['setting']), $setting['options']);
		}
		if ($setting['type'] == 'help') {
			UltimateTagWarriorActions::show_help($setting['setting'], $setting['label'], $setting['value']);
		}
	}
echo <<<CONFIGFOOTER
	</table>
			<input type="hidden" name="action" value="saveconfiguration">
			<input type="hidden" name="page" value="ultimate-tag-warrior-actions.php">
			<input type="submit" value="Save">
		</form>
	</fieldset>
CONFIGFOOTER;
}

function ultimate_better_admin() {
	global $lzndomain, $utw, $wpdb, $tabletags, $tablepost2tag, $install_directory;

	$siteurl = get_option('siteurl');

	echo '<div class="wrap">';

	if ($_GET["action"] == "savetagupdate") {
		$tagid = $_GET["edittag"];

		if ($_GET["updateaction"] == "Rename") {
			if (!is_numeric($tagid)) {
				echo "<div class=\"error\"><p>An invalid tag ID was passed in.</p></div>";
				return;
			}


			$tag = $_GET["renametagvalue"];

			$tagset = explode(",", $tag);

			$q = "SELECT post_id FROM $tablepost2tag WHERE tag_id = $tagid";
			$postids = $wpdb->get_results($q);

			$tagids = array();

			foreach ($tagset as $tag) {
				$tag = trim($tag);
				$tag = str_replace(' ', '-', $tag);
				$tag = str_replace('"', '', $tag);
				$tag = str_replace("'", '', $tag);

				$q = "SELECT tag_id FROM $tabletags WHERE tag = '$tag'";
				$thistagid = $wpdb->get_var($q);

				if (is_null($thistagid)) {
					$q = "INSERT INTO $tabletags (tag) VALUES ('$tag')";
					$wpdb->query($q);
					$thistagid = $wpdb->insert_id;
				}
				$tagids[] = $thistagid;
			}

			$keepold = false;
			foreach($tagids as $newtagid) {
				if ($postids ) {
					foreach ($postids as $postid) {
						if ($wpdb->get_var("SELECT COUNT(*) FROM $tablepost2tag WHERE tag_id = $newtagid AND post_id = $postid->post_id") == 0) {
							$wpdb->query("INSERT INTO $tablepost2tag (tag_id, post_id) VALUES ($newtagid, $postid->post_id)");
						}
					}
				} else {
					// I guess we were renaming something which wasn't being used...
				}

				if ($newtagid == $tagid) {
					$keepold = true;
				}
			}

			if (!$keepold) {
				$q = "delete from $tablepost2tag where tag_id = $tagid";
				$wpdb->query($q);

				$q = "delete from $tabletags where tag_id = $tagid";
				$wpdb->query($q);
			}
			$utw->ClearAllTagPostMeta();
			echo "<div class=\"updated\"><p>Tags have been updated.</p></div>";
		}

		if ($_GET["updateaction"] == __("Save Synonyms", $lzndomain)) {
			$synonyms = $_GET["synonyms"];
			$synonyms = explode(',', $synonyms);
			$utw->ClearSynonymsForTag($_GET["synonymtag"]);
			$message = "";
			foreach($synonyms as $synonym) {
				$message .= $utw->AddSynonymForTag("", $_GET["synonymtag"], $synonym);
				$message .= $synonym . " ";
			}

			echo "<div class=\"updated\"><p>Added $message</p></div>";
		}

		if ($_GET["updateaction"] ==__("Delete Tag", $lzndomain)) {
			if (!is_numeric($tagid)) {
				echo "<div class=\"error\"><p>An invalid tag ID was passed in.</p></div>";
				return;
			}

			$q = "delete from $tablepost2tag where tag_id = $tagid";
			$wpdb->query($q);

			$q = "delete from $tabletags where tag_id = $tagid";
			$wpdb->query($q);
			$utw->ClearAllTagPostMeta();
			echo "<div class=\"updated\"><p>Tag has been deleted.</p></div>";
		}
		if ($_GET["updateaction"] == __("Force Reinstall", $lzndomain)) {
			$message = $utw->ForceInstall();
			if ($message) {
				echo "<div class=\"updated\"><p>$message</p></div>";
			} else {
				echo "<div class=\"updated\"><p>Reinstall has Completed</p></div>";
			}
		}
		if ($_GET["updateaction"] == __("Tidy Tags", $lzndomain)) {
			$utw->TidyTags();
			echo "<div class=\"updated\"><p>Tags have been tidied</p></div>";
		}
		if ($_GET["updateaction"] == __("Import Embedded Tags", $lzndomain)) {
			$postids = $wpdb->get_results("SELECT id FROM $wpdb->posts");
			foreach ($postids as $postid) {
				$utw->SaveEmbeddedTags($postid->id);
			}

			echo "<div class=\"updated\"><p>Embedded tags have been imported</p></div>";
		}
		if ($_GET["updateaction"] == __("Convert Categories to Tags", $lzndomain)) {
			$postids = $wpdb->get_results("SELECT id FROM $wpdb->posts");
			foreach ($postids as $postid) {
				$utw->SaveCategoriesAsTags($postid->id);
			}

			echo "<div class=\"updated\"><p>Categories have been converted to tags</p></div>";
		}
		if ($_GET["updateaction"] == __("Import from Custom Field", $lzndomain)) {
			update_option('utw_custom_field_conversion_field_name', $_GET["fieldName"]);
			update_option('utw_custom_field_conversion_delimiter', $_GET["delimiter"]);

			if ($_GET['fieldName'] && $_GET['delimiter']) {
				$postids = $wpdb->get_results("SELECT id FROM $wpdb->posts");
				foreach ($postids as $postid) {
					$utw->SaveCustomFieldAsTags($postid->id, $_GET["fieldName"], $_GET["delimiter"]);
				}
				echo "<div class=\"updated\"><p>Tags have been imported from a custom field</p></div>";
			} else {
				echo "<div class=\"updated\"><p>Could not import tags from custom field</p></div>";
			}
		}
		if ($_GET["updateaction"] == __("Export to Custom Field", $lzndomain)) {
			update_option('utw_custom_field_conversion_field_name', $_GET["fieldName"]);
			update_option('utw_custom_field_conversion_delimiter', $_GET["delimiter"]);

			if ($_GET['fieldName'] && $_GET['delimiter']) {
				$postids = $wpdb->get_results("SELECT id FROM $wpdb->posts");
				foreach ($postids as $postid) {
					$utw->SaveTagsAsCustomField($postid->id, $_GET["fieldName"], $_GET["delimiter"]);
				}
				echo "<div class=\"updated\"><p>Tags have been exported to a custom field</p></div>";
			} else {
				echo "<div class=\"updated\"><p>Could not export tags to custom field</p></div>";
			}
		}
	}

	echo "<fieldset class=\"options\"><legend>" . __("Help!", $lzndomain) . "</legend><a href=\"$siteurl/wp-content/plugins$install_directory/ultimate-tag-warrior-help.html\" target=\"_new\">" . __("Local help", $lzndomain) . "</a> | <a href=\"http://www.neato.co.nz/ultimate-tag-warrior\" target=\"_new\">" . __("Author help", $lzndomain) . "</a> | <a href=\"./options-general.php?page=ultimate-tag-warrior-actions.php\">Configuration</a></fieldset>";

	echo '<fieldset class="options"><legend>' . __("Edit Tags", $lzndomain) .'</legend>';
	echo '<p>' . __("Enter a comma separated list of tags to split a tag into multiple tags", $lzndomain) . '</p>';
OPTIONS;
	$tags = $utw->GetPopularTags(-1, 'asc', 'tag');
	if ($tags) {
		echo "<form action=\"$siteurl/wp-admin/edit.php\">";

		echo "<select name=\"edittag\">";
		foreach($tags as $tag) {
			echo "<option value=\"$tag->tag_id\">$tag->tag</option>";
		}

		echo '</select> <input type="text" name="renametagvalue"> <input type="submit" name="updateaction" value="' . __("Rename", $lzndomain) . '"> <input type="submit" name="updateaction" value="' . __("Delete Tag", $lzndomain) . '" OnClick="javascript:return(confirm(\'' . __("Are you sure you want to delete this tag?", $lzndomain) . '\'))">';
		echo '<input type="hidden" name="action" value="savetagupdate">';
		echo '<input type="hidden" name="page" value="ultimate-tag-warrior-actions.php">';
		echo '</form>';
	} else {
		echo '<p>' . __('No tags are in use at the moment.', $lzndomain) . '</p>';
	}
	echo "</fieldset>";

	echo '<fieldset class="options"><legend>' . __("Assign Synonyms", $lzndomain) .'</legend>';
	echo '<p>' . __("Enter a comma separated list of synonyms. ", $lzndomain) . __("A synonym behaves in a similar manor to a tag - viewing the tag page for a synonym of a tag displays the tag page for the underlying tag.", $lzndomain) . '</p>';
	$tags = $utw->GetPopularTags(-1, 'asc', 'tag');
	if ($tags) {
		echo "<form action=\"$siteurl/wp-admin/edit.php\">";
		echo "<select name=\"synonymtag\" onChange=\"sndReqGenResp('editsynonyms', this.value, '', '')\">";
		foreach($tags as $tag) {
			echo "<option value=\"$tag->tag_id\">$tag->tag</option>";
		}

		echo '</select> <span id="ajaxResponse"><input type="text" name="synonyms" value="' . $utw->FormatTags($utw->GetSynonymsForTag($tags[0]->tag,''), array("first"=>"%tag%", "default"=>", %tag%")) . '" /></span> <input type="submit" name="updateaction" value="' . __("Save Synonyms", $lzndomain) . '">';
		echo '<input type="hidden" name="action" value="savetagupdate">';
		echo '<input type="hidden" name="page" value="ultimate-tag-warrior-actions.php">';
		echo '</form>';
	} else {
		echo '<p>' . __('No tags are in use at the moment.', $lzndomain) . '</p>';
	}
	echo "</fieldset>";


	echo "<form action=\"$siteurl/wp-admin/edit.php\">";

	echo '<fieldset class="options"><legend>' . __('Force Reinstall', $lzndomain) . '</legend>';
	_e('<p>Force Reinstall will run the installer.  This <em>will not</em> delete the tag tables.</p>');
	echo '<input type="submit" name="updateaction" value="' . __('Force Reinstall', $lzndomain) . '"></fieldset>';

	echo '<fieldset class="options"><legend>' . __('Tidy Tags', $lzndomain) . '</legend>';
	_e('<p>Tidy Tags is a scary, scary thing.  <em>Make sure you back up your database before clicking the button.</em></p><p>Tidy Tags will delete any tag&lt;-&gt;post associations which have either a deleted tag or deleted post;  delete any tags not associated with a post;  and merge tags with the same name into single tags.</p>');
	echo '<input type="submit" name="updateaction" value="' . __('Tidy Tags', $lzndomain) . '" OnClick="javascript:return(confirm(\'' . __("Are you sure you want to purge tags?", $lzndomain) . '\'))"></fieldset>';

	echo '<fieldset class="options"><legend>' . __('Convert Categories to Tags', $lzndomain) . '</legend>';
	_e('<p>Again.. very scary.. back up your database first!</p>');
	echo '<input type="submit" name="updateaction" onClick="javascript:return(confirm(\'' . __('Are you sure you want to convert categories to tags?', $lzndomain) . '\'))" value="' . __('Convert Categories to Tags', $lzndomain) . '"></fieldset>';

	echo '<fieldset class="options"><legend>' . __("Import Embedded Tags", $lzndomain) . '</legend>';
	_e('<p>Also very scary.. back up your database first!</p>');
	echo '<input type="submit" name="updateaction" onClick="javascript:return(confirm(\'' . __('Are you sure you want to import embedded tags?', $lzndomain) . '\'))" value="' . __("Import Embedded Tags", $lzndomain) . '"></fieldset>';

	echo '<fieldset class="options"><legend>' . __('Custom Fields', $lzndomain) . '</legend>';
	_e('<p>This pair of actions allow the moving of tag information from custom fields into the tag structure,  and moving the tag structure into a custom field.</p><p>When moving information from the custom field to the tag structure,  the existing tags are retained.  However, copying the tags to the custom field <strong>will overwrite the existing values</strong>.  To retain the existing values,  do an import before the export.</p><p><strong>This stuff seems to work,  but backup your database before trying,  just in case.</strong></p>', $lzndomain);
	echo '<table><tr><td>' . __("Custom field name", $lzndomain) . '</td><td><input type="text" name="fieldName" value="' . $fieldName . '" /></td></tr>';
	echo '<tr><td>' . __("Tag delimiter", $lzndomain) . '</td><td><input type="text" name="delimiter" value="' . $delimiter . '" /></td></tr></table>';
	echo '<input type="submit" name="updateaction" value="' . __("Import from Custom Field", $lzndomain) . '" />';
	echo '<input type="submit" name="updateaction" value="' . __("Export to Custom Field", $lzndomain) . '" OnClick="javascript:return(confirm(\'' . __('Beware:  This will overwrite any data in the custom field.  Continue?', $lzndomain) . '\'))"/></fieldset>';

	echo '<input type="hidden" name="action" value="savetagupdate">';
	echo '<input type="hidden" name="page" value="ultimate-tag-warrior-actions.php">';
	echo '</form>';
}

function show_dropdown($settingName, $label, $value, $options) {
	echo "<tr><td>$label</td><td><select name=\"$settingName\">";

	foreach($options as $option) {
		echo "<option value=\"$option\"";
		if ($value == $option) {
			echo " selected";
		}
		echo ">$option</option>";
	}

	echo "</select></td></tr>";
}

function show_multiselect($settingName, $label, $value, $options) {
	echo "<tr><td valign=\"top\">$label</td><td>";

	foreach($options as $option) {
		echo "<input type='checkbox' value=\"$option\" name=\"$settingName:$option\"";
		if (strpos($value,$option) > 0) {
			echo " checked";
		}
		echo "> $option<br />";
	}

	echo "</td></tr>";
}

function show_help($settingName, $label, $value) {
	echo <<<FORMWIDGET
<tr><td colspan="2" bgcolor="#f6f6f6">$value</td></tr>
FORMWIDGET;
}

function show_label($settingName, $label, $value) {
	echo <<<FORMWIDGET
<tr><td colspan="2" bgcolor="#DDD"><strong>$label</strong></td></tr>
FORMWIDGET;
}

function show_color($settingName, $label, $value) {
	echo <<<FORMWIDGET
<tr><td>$label</td><td><input type="text" name="$settingName" value="$value" maxlength="7" size="9"></td></tr>
FORMWIDGET;
}

function show_string($settingName, $label, $value) {
	echo <<<FORMWIDGET
<tr><td>$label</td><td><input type="text" name="$settingName" value="$value"></td></tr>
FORMWIDGET;
}

function show_toggle($settingName, $label, $value) {
	if ($value == 'yes') {
		$yeschecked = " checked";
	}
	echo <<<FORMWIDGET
<tr><td>$label</td><td><input type="checkbox" name="$settingName" id="$settingName" value="yes" $yeschecked></td></tr>
FORMWIDGET;
}

/*
ultimate_tag_templates
Handles the inclusion of templates, when appropriate.

index.php?archive=tag (or equivalent) will try and use the template tag_all.php
index.php?tag={tag name} (or equivalent) will try and use the template tag.php
*/
function ultimate_tag_templates() {
	if ($_GET["archive"] == "tag") {
		include(TEMPLATEPATH . '/tag_all.php');
		exit;
	} else 	if (get_query_var("tag") != "") {
		ultimate_get_posts();

		if (is_feed()) {

			return;
		}
		if (file_exists(TEMPLATEPATH . "/tag.php")) {
			if ( $_GET["feed"] == '') {
				include(TEMPLATEPATH . '/tag.php');
				exit;
			}
		} else {
	//		include(TEMPLATEPATH . '/index.php');
		}
	}
}

/*
ultimate_save_tags
Saves the tags for the current post to the database.

$postID the ID of the current post
$_POST['tagset'] the list of tags.
*/
function ultimate_save_tags($postID)
{
	global $wpdb, $table_prefix, $utw, $starttag, $endtag, $starttags, $endtags, $embedtags, $utw;

	/* Fix from Mark Jaquith using nonces */
	if ( !current_user_can('edit_post', $postID) )
		return $post_id;
	// origination and intention
	if ( !wp_verify_nonce($_POST['utw-verify-key'], 'utw') )
		return $postID;

	/* I'll defensively leave these here just in case. */
	if (isset($_POST['comment_post_ID'])) return $postID;
        if (isset($_POST['not_spam'])) return $postID; // akismet fix
        if (isset($_POST["comment"])) return $postID; // moderation.php fix
	if (!isset($_POST['tagset'])) return $postID; // if there's no tags passed in anyway...


	$tags = $wpdb->escape($_POST['tagset']);
	$tags = explode(',',$tags);

	$utw->SaveTags($postID, $tags);

	if (get_option('utw_include_categories_as_tags') == "yes") {
		$utw->SaveCategoriesAsTags($postID);
		$utw->ClearTagPostMeta($postID);
	}

	if ($embedtags == 'yes') {
		$post = &get_post($postID);

		$tags = $utw->ParseEmbeddedTags($post->post_content);

		if ($tags) {
			foreach($tags as $tag) {
				$utw->AddTag($postID, $tag);
			}
		}
	}

    return $postID;
}

function ultimate_delete_post($postID) {
	global $utw;

	$utw->DeletePostTags($postID);

	return $postID;
}

/*
ultimate_display_tag_widget
Displays the tag box on the content editing page.
*/
function ultimate_display_tag_widget() {
  global $post, $wpdb, $table_prefix, $utw;

  $tabletags = $table_prefix . "tags";
  $tablepost2tag = $table_prefix . "post2tag";

  $taglist = "";


  if ( (is_object($post) && $post->ID) || (!is_object($post) && $post)) {
	if (is_object($post)) {
		$postid = $post->ID;
	} else {
		$postid = $post;
	}

	if (is_numeric($postid)) {
		$q = "select t.tag from $tabletags t inner join $tablepost2tag p2t on t.tag_id = p2t.tag_id and p2t.post_id=$postid";
		$tags = $wpdb->get_results($q);
	}

    if ($tags) {
	  foreach($tags as $tag) {
		  $taglist .= $tag->tag . " ";
      }
	  $taglist = substr($taglist, 0, -1); // trim the trailing space.
    }
  }

	$widget .="<input name=\"tagset\" value=\"";
	if ($post) {
		$widget .= stripslashes(str_replace('&', '&amp;', $utw->FormatTags($utw->GetTagsForPost($postid, $limit), array("first"=>'%tag%', 'default'=>', %tag%'))));
	}
	$widget .="\" style=\"width:98%\"><br />";

	$widgetToUse = get_option('utw_always_show_links_on_edit_screen');


	if ($widgetToUse != 'none') {
		$widget .="Add existing tag: ";
		if ($widgetToUse=='tag list') {

			$format = "<a href=\"javascript:addTag('%tagjsescaped%')\">%tagdisplay%</a> ";
			$widget .= $utw->FormatTags($utw->GetPopularTags(-1, 'tag', 'asc'), $format);

		} else {
			$format = array(
			'pre' => '<select onchange="if (document.getElementById(\'tag-menu\').value != \'\') { addTag(document.getElementById(\'tag-menu\').value) }" id="tag-menu"><option selected="selected" value="">Choose a tag</option>',
			'default' => '<option value="%tag%">%tagdisplay% (%tagcount%)</option>',
			'post' => '</select>');

			$widget .= $utw->FormatTags($utw->GetPopularTags(-1, 'tag', 'asc'), $format);
		}
	}
  $suggestions .='<input type="button" onClick="askYahooForKeywords()" value="Get Keyword Suggestions From Yahoo"/>';
  $suggestions .='<div id="yahooSuggestedTags"></div>';
  echo '<input type="hidden" name="utw-verify-key" id="utw-verify-key" value="' . wp_create_nonce('utw') . '" />';
  echo '<fieldset id="tagsdiv" class="dbx-box">' . '<h3 class="dbx-handle">Tags (comma separated list)</h3><div class="dbx-content">' . $widget . '</div></fieldset>';
  echo '<fieldset id="tagsdiv" class="dbx-box">' . '<h3 class="dbx-handle">Tag Suggestions (Courtesy of <a href="http://www.yahoo.com">Yahoo!</a>)</h3><div class="dbx-content">' . $suggestions . '</div></fieldset>';


}

function regExEscape($str) {
	$str = str_replace('\\', '\\\\', $str);
	$str = str_replace('/', '\\/', $str);
	$str = str_replace('[', '\\[', $str);
	$str = str_replace(']', '\\]', $str);

	return $str;
}

function replaceTagWithLink($matches) {
	global $utw, $embeddedtagformat;

	$tags = explode(',',$matches[2]);

	$tagstr = '';
	$first = true;
	foreach ($tags as $tag) {
		if ($first === false) {
			$tagstr .= ',';
		} else {
			$first = false;
		}
		$tagstr .= "'" . str_replace(' ','-',strtolower(trim($tag))) . "'";
	}

	$tag = $utw->GetTagsForTagString($tagstr);

	return $utw->FormatTags($tag, $embeddedtagformat);
}

function ultimate_the_content_filter($thecontent='') {
	global $post, $utw, $lzndomainvar, $starttag, $endtag, $starttags, $endtags, $embedtags;

	$tagStartMarker = $starttag;
	$tagEndMarker = $endtag;

	$tags = $utw->GetTagsForPost($post->ID);

	$findTagRegEx = '/(' . UltimateTagWarriorActions::regExEscape($starttag) . '(.*?)' . UltimateTagWarriorActions::regExEscape($endtag) . ')/i';
	$findTagsRegEx = '/(' . UltimateTagWarriorActions::regExEscape($starttags) . '(.*?)' . UltimateTagWarriorActions::regExEscape($endtags) . ')/i';

	if ($embedtags == 'yes') {
		$thecontent = preg_replace($findTagsRegEx, '', $thecontent);
		$thecontent = preg_replace_callback($findTagRegEx, array("UltimateTagWarriorActions","replaceTagWithLink"), $thecontent);
	}

	if (!is_feed() && get_option('utw_include_local_links') != 'No' && get_option('utw_include_local_links') != 'no' ) {
		if (get_option('utw_primary_automagically_included_link_format') != '') {
			$custom = array();
			if (get_option('utw_primary_automagically_included_prefix') != '') {
				$custom['pre'] = stripslashes(get_option('utw_primary_automagically_included_prefix'));
			}
			if (get_option('utw_primary_automagically_included_suffix') != '') {
				$custom['post'] = stripslashes(get_option('utw_primary_automagically_included_suffix'));
			}

			$format = $utw->GetFormat(get_option('utw_primary_automagically_included_link_format'), $custom);
			$tagHTML = '<span class="UTWPrimaryTags">' . $utw->FormatTags($tags, $format) . '</span>';

			if (get_option('utw_include_local_links') == 'Before Content') {
				$thecontent = $tagHTML . $thecontent;
			} else {
				$thecontent = $thecontent . $tagHTML;
			}

		} else {
			// This is a throwback to when the format wasn't specified.
	//		$thecontent = $thecontent . $utw->FormatTags($tags, array("first"=>"<span class=\"localtags\">%taglink% ","default"=>"%taglink% ", "last"=>"%taglink%</span>"));
		}
	}

	if (!is_feed() && get_option('utw_include_technorati_links') != 'No' && get_option('utw_include_technorati_links') != 'no') {
		if (get_option('utw_secondary_automagically_included_link_format') != '') {
			$custom = array();
			if (get_option('utw_secondary_automagically_included_prefix') != '') {
				$custom['pre'] = stripslashes(get_option('utw_secondary_automagically_included_prefix'));
			}
			if (get_option('utw_secondary_automagically_included_suffix') != '') {
				$custom['post'] = stripslashes(get_option('utw_secondary_automagically_included_suffix'));
			}

			$format = $utw->GetFormat(get_option('utw_secondary_automagically_included_link_format'), $custom);
			$tagHTML = '<span class="UTWSecondaryTags">' . $utw->FormatTags($tags, $format) . '</span>';

			if (get_option('utw_include_technorati_links') == 'Before Content') {
				$thecontent = $tagHTML . $thecontent;
			} else {
				$thecontent = $thecontent . $tagHTML;
			}
		} else {
			// This is a throwback to when the format wasn't specified.
	//		$thecontent = $thecontent . $utw->FormatTags($tags, array("pre"=>__("<span class=\"technoratitags\">Technorati Tags", $lzndomain) . ": ","default"=>"%technoratitag% ", "last"=>"%technoratitag%","none"=>"","post"=>"</span>"));
		}
	}


	if (is_feed() && get_option('utw_append_tag_links_to_feed')) {
		$thecontent = $thecontent . $utw->FormatTags($tags, $utw->GetFormatForType('commalist'));
	}

	// Don't include anything on 'page' posts if there are no tags.  There's a check for no tags in case people have tinkered with the page editing thing to allow it.
	if (count($tags) == 0 && $post->post_status == 'static') {
		return $thecontent;
	}

	return $thecontent;
}

function ultimate_add_tags_to_rss($the_list, $type="") {
	global $post, $utw;
	$home = get_bloginfo_rss('home');

	if ($type == 'rdf'){
		$format="<dc:subject>%tagdisplay%</dc:subject>";
	} else if ( 'atom' == $type ) {
		$format = "<category scheme='$home' term='%tagdisplay%' />";
	} else {
		$format="<category>%tagdisplay%</category>";
	} 
	$tags = $utw->FormatTags($utw->GetTagsForPost($post->ID), $format);
	$tags = str_replace('&', '&amp;', $tags);

	$the_list .= $tags;

	return $the_list;
}

function ultimate_add_ajax_javascript() {
	global $install_directory, $wp_query, $utw;

	if (get_query_var('tag') != "") {
		$wp_query->is_home = false;
	}

	echo "<script src=\"" . $utw->GetAjaxJavascriptUrl() . "\" type=\"text/javascript\"></script>";

}

function ultimate_add_admin_javascript() {
	global $install_directory;
	
	$js = get_option('siteurl') . "/wp-content/plugins$install_directory/ultimate-tag-warrior-js.php";
	echo "<script src=\"$js\" type=\"text/javascript\"></script>";
}

function ultimate_add_meta_keywords() {
	if (get_option('utw_show_meta_keywords') == 'yes') {
		UTW_ShowMetaKeywords();
	}
}

function ultimate_posts_join($join) {
	if (get_query_var("tag") != "") {
		global $table_prefix, $wpdb;

		$tabletags = $table_prefix . "tags";
		$tablepost2tag = $table_prefix . "post2tag";

		$join .= " INNER JOIN $tablepost2tag p2t on $wpdb->posts.ID = p2t.post_id INNER JOIN $tabletags t on p2t.tag_id = t.tag_id ";
	}
	return $join;
}

function ultimate_posts_where($where) {
	global $utw;
	if (get_query_var("tag") != "") {
		global $table_prefix, $wpdb;

		global $wp_query;

		$wp_query->is_home=false;

		$tabletags = $table_prefix . "tags";
		$tablepost2tag = $table_prefix . "post2tag";

		$tags = get_query_var("tag");

		$tagset = explode(" ", $tags);

		if (count($tagset) == 1) {
			$tagset = explode("|", $tags);
		}

		$tags = array();
		foreach($tagset as $tag) {
			$tags[] = "'" . $utw->GetCanonicalTag(str_replace("'",'',str_replace('"','',stripslashes($tag)))) . "'";
		}
		$tags = array_unique($tags);

		$taglist = implode (',',$tags);
		$where .= " AND t.tag IN ($taglist) ";
	}
	return $where;
}
function ultimate_posts_groupby($groupby) {
	if(is_utwtag() || is_search()) {
		if ($groupby == '') {
			$groupby = $wpdb->posts.ID;
		} else if (strpos($groupby,$wpdb->posts.ID) == false || strpos($groupby,'posts.ID') == false) {
			// do nothing.
		} else {
			// add to the end
			$groupby .= ',' . $wpdb->posts.ID;
		}
	}
	return $groupby;
}

function ultimate_query_vars($vars) {
	$vars[] = 'tag';

	return $vars;
}

function ultimate_search_where($where) {
	if (is_search()) {
		global $table_prefix, $wpdb, $wp_query;
		$tabletags = $table_prefix . "tags";
		$tablepost2tag = $table_prefix . "post2tag";

		$where .= " OR $tabletags.tag like '%" . $wp_query->query_vars['s'] . "%'";
	}
	return $where;
}

function ultimate_search_join($join) {
	if (is_search()) {
		global $table_prefix, $wpdb;

		$tabletags = $table_prefix . "tags";
		$tablepost2tag = $table_prefix . "post2tag";

		$join .= " LEFT JOIN $tablepost2tag p2t on $wpdb->posts.ID = p2t.post_id INNER JOIN $tabletags on p2t.tag_id = $tabletags.tag_id ";
	}
	return $join;
}

/* Maaaaaybe some day...

function ultimate_posts_having () {
	if (get_query_var("tag") != "") {
		$tags = get_query_var("tag");
		$tagset = explode(" ", $tags);
		$taglist = "'" . $tagset[0] . "'";
		$tagcount = count($tagset);

		return " HAVING count(wp_posts.id) = $tagcount ";
	}
}
*/

}

// Admin menu items
add_action('admin_menu', array('UltimateTagWarriorActions', 'ultimate_admin_menus'));

// Add or edit tags
add_action('simple_edit_form', array('UltimateTagWarriorActions','ultimate_display_tag_widget'));
add_action('edit_form_advanced', array('UltimateTagWarriorActions','ultimate_display_tag_widget'));
add_action('edit_page_form', array('UltimateTagWarriorActions','ultimate_display_tag_widget'));

// Save changes to tags
add_action('publish_post', array('UltimateTagWarriorActions','ultimate_save_tags'));
add_action('edit_post', array('UltimateTagWarriorActions','ultimate_save_tags'));
add_action('save_post', array('UltimateTagWarriorActions','ultimate_save_tags'));
add_action('wp_insert_post', array('UltimateTagWarriorActions','ultimate_save_tags'));

add_action('delete_post', array('UltimateTagWarriorActions', 'ultimate_delete_post'));

// Display tag pages
add_action('template_redirect', array('UltimateTagWarriorActions','ultimate_tag_templates'));

add_filter('posts_join', array('UltimateTagWarriorActions','ultimate_posts_join'));
add_filter('posts_where', array('UltimateTagWarriorActions','ultimate_posts_where'));
add_filter('posts_groupby', array('UltimateTagWarriorActions','ultimate_posts_groupby'));
// add_filter('posts_having',array('UltimateTagWarriorActions','ultimate_posts_having'));

add_filter('posts_join', array('UltimateTagWarriorActions','ultimate_search_join'));
add_filter('posts_where', array('UltimateTagWarriorActions','ultimate_search_where'));

// URL rewriting
add_filter('rewrite_rules_array', array('UltimateTagWarriorActions','ultimate_rewrite_rules'),100);
add_filter('query_vars', array('UltimateTagWarriorActions','ultimate_query_vars'));

add_filter('the_content', array('UltimateTagWarriorActions', 'ultimate_the_content_filter'));
add_filter('the_category_rss', array('UltimateTagWarriorActions', 'ultimate_add_tags_to_rss'), 2, 2);

add_filter('wp_head', array('UltimateTagWarriorActions', 'ultimate_add_meta_keywords'));
add_filter('admin_head', array('UltimateTagWarriorActions', 'ultimate_add_admin_javascript'));
add_filter('admin_head', array('UltimateTagWarriorActions', 'ultimate_add_ajax_javascript'));
?>
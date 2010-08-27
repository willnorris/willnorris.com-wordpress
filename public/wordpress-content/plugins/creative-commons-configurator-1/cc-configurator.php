<?php
/*
Plugin Name: Creative Commons Configurator
Plugin URI: http://www.g-loaded.eu/2006/01/14/creative-commons-configurator-wordpress-plugin/
Description: Adds a Creative Commons license to your blog pages and feeds. Also, provides some <em>Template Tags</em> for use in your theme templates. Please visit the plugin's <a href="options-general.php?page=cc-configurator.php">configuration panel</a>.
Version: 1.2
Author: George Notaras
Author URI: http://www.g-loaded.eu/
*/

/*
License: GPL
Compatibility: Requires WordPress 2 or newer for full functionality.

Installation:
Place the cc-configurator.php file in your /wp-content/plugins/ directory
and activate through the administration panel.

Configuration:
Administration Panel -> Options -> License
*/

/**
 *  Copyright 2008 George Notaras <gnot [at] g-loaded.eu>, CodeTRAX.org
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/* Changelog - Release Notes
- Use rawurldecode() on the values that are returned by the CC API.
- Removed the border attribute from the image hyperlink in order to comply with
  XHTML 1.1.
* Thu Mar 15 2007 - v1.0
- The plugin was almost re-written from scratch. Many new features have been
  added and others have been modified so to provide the best functionality and
  ease of use.
- The license selection engine from CreativeCommons.org is now used in order to
  select a license for your blog. No more copying and pasting of license code.
- A new license info layer is introduced for placing under the published content.
  Customization of that layer is possible either from the config panel or with
  CSS.
- WARNING: the bccl_display_full_html_license() template tag has been replaced by
  bccl_full_html_license(). Make sure you update your theme templates.
- New template tags are available.
- The configuration panel has been reworked.
- The plugin is reasy for translations.
* Sat Feb 24 2007 - v0.6
- Supports CC v3
* Wed Nov 01 2006 - v0.5
- When the options where modified in the administration panel, a confirmation was asked.
  This behaviour has been corrected and the options are saved immediately.
- Wordpress escaped some characters in the extra message that is displayed after the post's
  body. This resulted in corrupted HTML code. This has been corrected (thanks John)
* Wed Oct 04 2006 - v0.4
- Plugin information update
* Mon Jan 16 2006
- Added a WordPress version check, so that the option to include licensing info in the feeds 
does not appear in older WP version than 2.0.
- Added an informational section in the configuration page about the template tags that 
can be used in the theme.
- Added success message after successful license reset.
- Added success message after successful license code submission.
- Added error message if license code does not seem valid.
- Added some Creative Commons license code verification. Seems to work with all licenses, 
but is very primitive. Only the basic HTML code structure is checked.
- The default licensing info message that is displayed after the post's body was modified.
- Added one more option. Now a user can define some custom code that will be displayed 
together with the default message below the post's body.
- Added some template tags that can be used by a user on a theme.
- More modularization of the code.
- Minor fixes, so it works properly with other CC licenses, eg Developing Nations, Sampling etc.
- Minor form HTML code fixes.
* Sat Jan 14 2006 - v0.1
- Initial release
*/


/*
Creative Commons Icon Selection.
"0" : 88x31.png
"1" : somerights20.png
"2" : 80x15.png
*/
$default_button = "0";



load_plugin_textdomain('cc-configurator', 'wp-content/plugins');

function bccl_add_pages() {
	add_options_page(__('License Options', 'cc-configurator'), __('License', 'cc-configurator'), 8, __FILE__, 'bccl_license_options');
}

function bccl_show_info_msg($msg) {
	echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
}

function bccl_license_options () {
	/*
	Format of the saved settings array:
	
	cc_settings (array)
		license_url
		license_name
		license_button
		deed_url
		options (array)
			cc_head
			cc_feed
			cc_body
			cc_body_img
			cc_extended
			cc_creator
			cc_perm_url
			cc_color (default black)
			cc_bgcolor (default #eef6e6)
			cc_brdr_color (default #cccccc)
			cc_no_style
	
	It is checked if a specific form (options update, reset license) has been
	submitted or if a new license is available in a GET request.
	
	Then, it is determined which page should be displayed to the user by
	checking whether the license_url exists in the cc_settings or not.
	license_url is a mandatory attribute of the CC license.
	*/
	if (isset($_POST["options_update"])) {
		/*
		Updates the CC License options only.
		It will never enter here if a license has not been set, so it is
		taken for granted that "cc_settings" exist in the database.
		*/
		$options = array(
			"cc_head"	=> $_POST["cc_head"],
			"cc_feed"	=> $_POST["cc_feed"],
			"cc_body"	=> $_POST["cc_body"],
			"cc_body_img"	=> $_POST["cc_body_img"],
			"cc_extended"	=> $_POST["cc_extended"],
			"cc_creator"	=> $_POST["cc_creator"],
			"cc_perm_url"	=> $_POST["cc_perm_url"],
			"cc_color"	=> $_POST["cc_color"],
			"cc_bgcolor"	=> $_POST["cc_bgcolor"],
			"cc_brdr_color"	=> $_POST["cc_brdr_color"],
			"cc_no_style"	=> $_POST["cc_no_style"],
			);
		
		/*
		Set default values for the following options if empty: cc_color, cc_bgcolor, cc_brdr_color
		cc_creator should NEVER be empty so, it is not included.
		*/
		if (empty($options["cc_color"])) {
			$options["cc_color"] = "#000000";
		}
		if (empty($options["cc_bgcolor"])) {
			$options["cc_bgcolor"] = "#eef6e6";
		}
		if (empty($options["cc_brdr_color"])) {
			$options["cc_brdr_color"] = "#cccccc";
		}
		
		$cc_settings = get_option("cc_settings");
		$cc_settings["options"] = $options;
		update_option("cc_settings", $cc_settings);
		bccl_show_info_msg(__('Creative Commons license options saved.', 'cc-configurator'));

	} elseif (isset($_POST["license_reset"])) {
		/*
		Delete all plugin options from the WordPress database when the
		"Reset" button is pressed.
		*/
		delete_option("cc_settings");
		bccl_show_info_msg(__("Creative Commons license options deleted from the WordPress database.", 'cc-configurator'));

		/*
		The following exist for deleting old (pre v1.0) plugin options.
		The following statements have no effect if the options do not exist.
		This is 100% safe (TM).
		*/
		delete_option("bccl_html");
		delete_option("bccl_rdf");
		delete_option("bccl_header");
		delete_option("bccl_feed");
		delete_option("bccl_body");
		delete_option("bccl_body_extra");

	} elseif (isset($_GET["new_license"])) {
		/*
		Saves license settings to database.
		new_license must exist in the GET request.
		
		Also, saves the default colors to the options.
		*/
		$cc_settings = array(
			"license_url"	=> htmlspecialchars(rawurldecode($_GET["license_url"])),
			"license_name"	=> htmlspecialchars(rawurldecode($_GET["license_name"])),
			"license_button"=> htmlspecialchars(rawurldecode($_GET["license_button"])),
			"deed_url"	=> htmlspecialchars(rawurldecode($_GET["deed_url"])),
			"options"	=> array(
				"cc_creator"	=> "blogname",
				"cc_color"	=> "#000000",
				"cc_bgcolor"	=> "#eef6e6",
				"cc_brdr_color"	=> "#cccccc",
				),
			);
		
		update_option("cc_settings", $cc_settings);
		bccl_show_info_msg(__('Creative Commons license saved.', 'cc-configurator'));

	}
	
	/*
	Decide if the license selection frame will be shown or the license options page.
	*/
	$cc_settings = get_option("cc_settings");
	
	if (empty($cc_settings["license_url"])) {
		bccl_select_license();
	} else {
		bccl_set_license_options($cc_settings);
	}

}


function bccl_select_license() {
	/*
	License selection dialog.
	*/
	$partner = urlencode("WordPress/CC-Configurator Plugin");
	$partner_icon_url = urlencode(get_bloginfo("url") . "/wp-admin/images/wordpress-logo.png");
	$exit_url = urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "&license_url=[license_url]&license_name=[license_name]&license_button=[license_button]&deed_url=[deed_url]&new_license=1");
	$jurisdiction_choose = "1";
	
	$URI = htmlspecialchars("http://creativecommons.org/license/?partner=$partner&partner_icon_url=$partner_icon_url&exit_url=$exit_url&jurisdiction_choose=$jurisdiction_choose");

	print('
	<div class="wrap">
		<h2>'.__('Select a Creative Commons license', 'cc-configurator').'</h2>
		<p>'.__('Welcome to the Creative Commons license configurator for WordPress.', 'cc-configurator').'</p>
		<p>'.__('The following link leads to the Creative Commons license selection engine. After you have selected your license, you will be directed back to this page.', 'cc-configurator').'</p>
		<p><big>'.__('Please', 'cc-configurator').', <a href="' . $URI . '">'.__('select a Creative Commons license', 'cc-configurator').'</a></big></p>
	</div>');
}


function bccl_set_license_options($cc_settings) {
	/*
	CC License Options
	*/
	global $wp_version;

	print('
	<div class="wrap">
		<h2>'.__('Current License', 'cc-configurator').'</h2>
		<p style="text-align: center;"><big>' . bccl_get_full_html_license() . '</big></p>
		<form name="formlicense" id="bccl_reset" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
			<fieldset class="options">
				<legend>'.__('Information', 'cc-configurator').'</legend>
				<p>'.__('If you need to use a different license for your blog, press the <em>Reset License</em> button.', 'cc-configurator').'</p>
				<p>'.__('By reseting the license, the saved plugin options are removed from the WordPress database. Consider reseting the license before uninstalling the plugin, so that no trace of it is left in the WordPress database.', 'cc-configurator').'</p>
			</fieldset>
			<p class="submit">
				<input type="submit" name="license_reset" value="'.__('Reset License', 'cc-configurator').' &raquo;" />
			</p>
		</form>
	</div>

	<div class="wrap">
		<h2>'.__('License Options', 'cc-configurator').'</h2>
		<p>'.__('Here you can choose where and how license information should be added to your blog.', 'cc-configurator').'</p>
		<form name="formlicenseoptions" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
			<fieldset class="options">
				<legend>'.__('Page Head (HTML)', 'cc-configurator').'</legend>
				<ul><li>
					<input name="cc_head" type="checkbox" id="cc_head" value="1" ');
					checked('1', $cc_settings["options"]["cc_head"]);
					print(' />
					<label for="cc_head">'.__('Include license information in the page\'s HTML head. This will not be visible to human visitors, but search engine bots will be able to read it. (Recommended)', 'cc-configurator').'</label>
				</li></ul>
			</fieldset>
		'); if ( $wp_version >= 2 ) { print('
			<fieldset class="options">
				<legend>'.__('Syndicated Content', 'cc-configurator').'</legend>
				<ul><li>
					<input name="cc_feed" type="checkbox" id="cc_feed" value="1" ');
					checked('1', $cc_settings["options"]["cc_feed"]);
					print(' />
					<label for="cc_feed">'.__('Include license information in the blog feeds. (Recommended)', 'cc-configurator').'</label>
				</li></ul>
			</fieldset>
		'); } print('
			<fieldset class="options">
				<legend>'.__('Under the published content', 'cc-configurator').'</legend>
				<p>'.__('By enabling the following option, a small block of text, which contains links to the author, the work and the used license, is appended to the published content. (Recommended)', 'cc-configurator').'</p>
				<ul><li>
					<input name="cc_body" type="checkbox" id="cc_body" value="1" ');
					checked('1', $cc_settings["options"]["cc_body"]);
					print(' />
					<label for="cc_body">'.__('Add the text block with license information under the published content in single-post view.', 'cc-configurator').'</label>
					<br /><br />
					
					<ul>
				
					<li>
						<input name="cc_body_img" type="checkbox" id="cc_body_img" value="1" ');
						checked('1', $cc_settings["options"]["cc_body_img"]);
						print(' />
						<label for="cc_body_img">'.__('Include the license image in the text block.', 'cc-configurator').'</label>
						<br /><br />
					</li>
					
					<li>
						<input name="cc_extended" type="checkbox" id="cc_extended" value="1" ');
						checked('1', $cc_settings["options"]["cc_extended"]);
						print(' />
						<label for="cc_extended">'.__('Include extended information about the published work and its creator. By enabling this option, hyperlinks to the published content and its creator/publisher are also included into the license statement inside the block. This, by being an attribution example itself, will generally help others to attribute the work to you.', 'cc-configurator').'</label>
						<br /><br />
						
						<ul>
						
						<li>
							<select name="cc_creator" id="cc_creator">');
							$creator_arr = bccl_get_creator_pool();
							foreach ($creator_arr as $internal => $creator) {
								if ($cc_settings["options"]["cc_creator"] == $internal) {
									$selected = ' selected="selected"';
								} else {
									$selected = '';
								}
								printf('<option value="%s"%s>%s</option>', $internal, $selected, $creator);
							}
							print('</select>
							<label for="cc_creator">'.__('If extended information about the published work has been enabled, then you can choose which name will indicate the creator of the work. By default, the blog name is used.', 'cc-configurator').'</label>
							<br /><br />
						</li>
						
						</ul>
					</li>
					
					<li>
						'.__('If you have added any extra permissions to your license, provide the URL to the webpage that contains them. It is highly recommended to use absolute URLs, for example', 'cc-configurator').': <code>http://www.example.org/ExtendedPermissions</code><br />
						<input name="cc_perm_url" type="text" id="cc_perm_url" class="code" value="' . $cc_settings["options"]["cc_perm_url"] . '" size="80" />
						<br /><br />
					</li>
					
					<li><big><strong>'.__('Colors of the license block', 'cc-configurator').':</strong></big>
						<br /><br />
						
						<ul>
						
						<li>

							'.__('Set a color for the text that appears within the block (does not affect hyperlinks)', 'cc-configurator').': 
							<input name="cc_color" type="text" id="cc_color" class="code" value="' . $cc_settings["options"]["cc_color"] . '" size="7" maxlength="7" /> 
							<br />'.__('Default', 'cc-configurator').': <code>#000000</code>
							<br /><br />
						</li>
					
						<li>
							'.__('Set a background color for the block.', 'cc-configurator').': 
							<input name="cc_bgcolor" type="text" id="cc_bgcolor" class="code" value="' . $cc_settings["options"]["cc_bgcolor"] . '" size="7" maxlength="7" /> 
							<br />'.__('Default', 'cc-configurator').': <code>#eef6e6</code>
							<br /><br />
						</li>
					
						<li>
							'.__('Set a color for the border of the block.', 'cc-configurator').': 
							<input name="cc_brdr_color" type="text" id="cc_brdr_color" class="code" value="' . $cc_settings["options"]["cc_brdr_color"] . '" size="7" maxlength="7" /> 
							<br />'.__('Default', 'cc-configurator').': <code>#cccccc</code>
							<br /><br />
						</li>
						
						<li>
							<input name="cc_no_style" type="checkbox" id="cc_no_style" value="1" ');
							checked('1', $cc_settings["options"]["cc_no_style"]);
							print(' />
							<label for="cc_no_style">'.__('Disable the internal formatting of the license block. If the internal formatting is disabled, then the color selections above have no effect any more. You can still format the license block via your own CSS. The <em>cc-block</em> and <em>cc-button</em> classes have been reserved for formatting the license block and the license button respectively.', 'cc-configurator').'</label>
							<br /><br />
					</li>
						
						</ul>
					
					</li>
					
					</ul>
				</li></ul>

			</fieldset>
			<p class="submit">
				<input type="submit" name="options_update" value="'.__('Update Options', 'cc-configurator').' &raquo;" />
			</p>
		</form>
	</div>

	<div class="wrap">
		<h2>'.__('Advanced Info', 'cc-configurator').'</h2>
		<p>'.__('Apart from the options above for the inclusion of licensing information in your blog, this plugin provides some <em>Template Tags</em>, which can be used in your theme templates. These are the following:', 'cc-configurator').'
		</p>
		
		<ul>
		
			<li><strong>'.__('Text Hyperlink', 'cc-configurator').'</strong>
			<ul>
				<li><code>bccl_get_license_text_hyperlink()</code> - '.__('Returns the text hyperlink of your current license for use in the PHP code.', 'cc-configurator').'</li>
				<li><code>bccl_license_text_hyperlink()</code> - '.__('Displays the text hyperlink.', 'cc-configurator').'</li>
			</ul>
			</li>
			
			<li><strong>'.__('Image Hyperlink', 'cc-configurator').'</strong>
			<ul>
				<li><code>bccl_get_license_image_hyperlink() - '.__('Returns the image hyperlink of the current license.', 'cc-configurator').'</li>
				<li><code>bccl_license_image_hyperlink()</code> - '.__('Displays the image hyperlink of the current license.', 'cc-configurator').'</li>
			</ul>
			</li>
			
			<li><strong>'.__('License URIs', 'cc-configurator').'</strong>
			<ul>
				<li><code>bccl_get_license_url()</code> - '.__('Returns the license\'s URL.', 'cc-configurator').'</li>
				<li><code>bccl_get_license_deed_url()</code> - '.__('Returns the license\'s Deed URL. Usually this is the same URI as returned by the bccl_get_license_url() function.', 'cc-configurator').'</li>
			</ul>
			</li>
			
			<li><strong>'.__('Full HTML Code', 'cc-configurator').'</strong>
			<ul>
				<li><code>bccl_get_full_html_license()</code> - '.__('Returns the full HTML code of the license. This includes the text and the image hyperlinks.', 'cc-configurator').'</li>
				<li><code>bccl_full_html_license()</code> - '.__('Displays the full HTML code of the license. This includes the text and the image hyperlinks.', 'cc-configurator').'</li>
			</ul>
			</li>
			
			<li><strong>'.__('Complete License Block', 'cc-configurator').'</strong>
			<ul>
				<li><code>bccl_license_block($work, $css_class, $show_button)</code> - '.__('Displays a complete license block. This template tag can be used to publish specific original work under the current license or in order to display the license block at custom locations on your website. This function supports the following arguments', 'cc-configurator').':
				
				<ol>
					<li><code>$work</code> ('.__('alphanumeric', 'cc-configurator').') : '.__('This argument is used to define the work to be licensed. Its use is optional, when the template tag is used in single-post view. If not defined, the user-defined settings for the default license block are used.', 'cc-configurator').'</li>
					<li><code>$css_class</code> ('.__('alphanumeric', 'cc-configurator').') : '.__('This argument sets the name of the CSS class that will be used to format the license block. It is optional. If not defined, then the default class <em>cc-block</em> is used.', 'cc-configurator').'</li>
					<li><code>$show_button</code> ('.__('alphanumeric', 'cc-configurator').') - ("default", "yes", "no") : '.__('This argument is optional. It can be used in order to control the appearance of the license icon.', 'cc-configurator').'</li>
				</ol>
				
				</li>
			</ul>
			</li>
			
			<li><strong>'.__('Licence Documents', 'cc-configurator').'</strong>
			<ul>
				<li><code>bccl_license_summary($width, $height, $css_class)</code> - '.__('Displays the license\'s summary document in an <em>iframe</em>.', 'cc-configurator').'</li>
				<li><code>bccl_license_legalcode($width, $height, $css_class)</code> - '.__('Displays the license\'s full legal code in an <em>iframe</em>.', 'cc-configurator').'</li>
			</ul>
			</li>
	
		</ul>
	</div>

	');
}


function bccl_add_placeholders($data, $what = "html") {
	if (!(trim($data))) { return ""; }
	if ($what = "html") {
		return sprintf("\n<!-- Creative Commons License -->\n%s\n<!-- /Creative Commons License -->\n", trim($data));
	} else {
		return sprintf("\n<!--\n%s\n-->\n", trim($data));
	}
}


function bccl_get_license_text_hyperlink() {
	/*
	Returns Full TEXT hyperlink to License <a href=...>...</a>
	*/
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	$license_url = $cc_settings["license_url"];
	$license_name = $cc_settings["license_name"];
	
	$text_link_format = '<a rel="license" href="%s">%s %s %s</a>';
	return sprintf($text_link_format, $license_url, __('Creative Commons', 'cc-configurator'), trim($license_name), __('License', 'cc-configurator'));
}


function bccl_license_text_hyperlink() {
	/*
	Displays Full TEXT hyperlink to License <a href=...>...</a>
	*/
	echo bccl_add_placeholders(bccl_get_license_text_hyperlink());
}


function bccl_get_license_image_hyperlink($button = "default") {
	/*
	Returns Full IMAGE hyperlink to License <a href=...><img.../></a>
	
	Creative Commons Icon Selection
	"0" : 88x31.png
	"1" : http://creativecommons.org/images/public/somerights20.png
	"2" : 80x15.png

	CSS customization via "cc-button" class.
	*/
	
	global $default_button;
	
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	$license_url = $cc_settings["license_url"];
	$license_name = $cc_settings["license_name"];
	$license_button = $cc_settings["license_button"];
	
	// Available buttons
	$buttons = array(
		"0" => dirname($license_button) . "/88x31.png",
		"1" => "http://creativecommons.org/images/public/somerights20.png",
		"2" => dirname($license_button) . "/80x15.png"
		);
	
	// Modify button
	if ($button == "default") {
		if (array_key_exists($default_button, $buttons)) {
			$license_button = $buttons[$default_button];
		}
	} elseif (array_key_exists($button, $buttons)){
		$license_button = $buttons[$button];
	}
	
	$image_link_format = "<a rel=\"license\" href=\"%s\"><img alt=\"%s\" src=\"%s\" class=\"cc-button\" /></a>";
	return sprintf($image_link_format, $license_url, __('Creative Commons License', 'cc-configurator'), $license_button);

}


function bccl_license_image_hyperlink($button = "default") {
	/*
	Displays Full IMAGE hyperlink to License <a href=...><img...</a>
	*/
	echo bccl_add_placeholders(bccl_get_license_image_hyperlink($button));
}


function bccl_get_license_url() {
	/*
	Returns only the license URL.
	*/
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	return $cc_settings["license_url"];
}

function bccl_get_license_deed_url() {
	/*
	Returns only the license deed URL.
	*/
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	return $cc_settings["deed_url"];
}


function bccl_license_summary($width = "100%", $height = "600px", $css_class= "cc-frame") {
	/*
	Displays the licence summary page from creative commons in an iframe
	
	*/
	printf('
		<iframe src="%s" frameborder="0" width="%s" height="%s" class="%s"></iframe>
		', bccl_get_license_url(), $width, $height, $css_class);
}


function bccl_license_legalcode($width = "100%", $height = "600px", $css_class= "cc-frame") {
	/*
	Displays the licence summary page from creative commons in an iframe
	*/
	printf('
		<iframe src="%slegalcode" frameborder="0" width="%s" height="%s" class="%s"></iframe>
		', bccl_get_license_url(), $width, $height, $css_class);
}


function bccl_get_full_html_license($button = "default") {
	/*
	Returns the full HTML code of the license
	*/	
	return bccl_get_license_image_hyperlink($button) . "<br />" . bccl_get_license_text_hyperlink();
}


function bccl_full_html_license($button = "default") {
	/*
	Displays the full HTML code of the license
	*/	
	echo bccl_add_placeholders(bccl_get_full_html_license($button));
}


function bccl_get_license_block($work = "", $css_class = "", $show_button = "default", $button = "default", $internal_use = FALSE) {
	/*
	This function should not be used in template tags.
	
	$work: The work that is licensed can be defined by the user.
	
	$internal_use:
	DO NOT USE WHEN CALLING THE FUNCTION FROM WITHIN A TEMPLATE!!
	This argument is used in order to also check if the user has enabled
	the display of the license block in the plugin configuration panel.
	
	$show_button: (default, yes, no) - no explanation (TODO possibly define icon URL)
	
	$button: The user can se the desired button (hidden feature): "0", "1", "2"
	
	The function returns FALSE *only* when $internal_use is TRUE and if the
	user has *not* set the option to display a license block under each post.
	*/
	$cc_block = "LICENSE BLOCK ERROR";
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	
	if ($internal_use) {
		if ( $cc_settings["options"]["cc_body"] != "1" ) {
			return FALSE;
		}
	}
	
	// Set CSS class
	if (empty($css_class)) {
		$css_class = "cc-block";
	}
	
	// License button inclusion
	if ($show_button == "default") {
		if ($cc_settings["options"]["cc_body_img"]) {
			$button_code = bccl_get_license_image_hyperlink($button) . "<br />";
		}
	} elseif ($show_button == "yes") {
		$button_code = bccl_get_license_image_hyperlink($button) . "<br />";
	} elseif ($show_button == "no") {
		$button_code = "";
	} else {
		$button_code = "ERROR";
	}
	
	// Work analysis
	if (!$work && is_single()) {
		// Proceed only if the user has not defined the work.
		if ( $cc_settings["options"]["cc_extended"] ) {
			$creator = bccl_get_the_creator($cc_settings["options"]["cc_creator"]);
			$work = "<em><a href=\"" . get_permalink() . "\">" . get_the_title() . "</a></em>";
			$by = "<em><a href=\"" . get_bloginfo("url") . "\">" . $creator . "</a></em>";
			$work = sprintf("%s %s %s %s", __("The", 'cc-configurator'), $work, __("by", 'cc-configurator'), $by);
		} else {
			$work = __('This work', 'cc-configurator');
		}
	} elseif (!$work && !is_single()) {
		return __('ERROR (cc-configurator): you must define the work to be licenced, if not using this template tag in single-post view.', 'cc-configurator');
	}
	$work .= sprintf(", ".__('unless otherwise expressly stated', 'cc-configurator').", ".__('is licensed under a', 'cc-configurator')." %s.", bccl_get_license_text_hyperlink());
	
	// Additional Permissions
	if ( $cc_settings["options"]["cc_perm_url"] ) {
		$additional_perms = " ".__('Terms and conditions beyond the scope of this license may be available at', 'cc-configurator')." <a href=\"" . $cc_settings["options"]["cc_perm_url"] . "\">" . $_SERVER["HTTP_HOST"] . "</a>.";
	} else {
		$additional_perms = "";
	}
	
	$cc_block = sprintf("<div class=\"%s\">%s%s%s</div>", $css_class, $button_code, $work, $additional_perms);
	return $cc_block;
}


function bccl_license_block($work = "", $css_class = "", $show_button = "default", $button = "default") {
	/*
	$work: The work that is licensed can be defined by the user.
	$css_class : The user can define the CSS class that will be used to
	$show_button: (default, yes, no)
	format the license block. (if empty, the default cc-block is used)
	*/
	echo bccl_add_placeholders(bccl_get_license_block($work, $css_class, $show_button, $button));
}




function bccl_get_creator_pool() {
	$creator_arr = array(
		"blogname"	=> __('Blog Name', 'cc-configurator'),
		"firstlast"	=> __('First + Last Name', 'cc-configurator'),
		"lastfirst"	=> __('Last + First Name', 'cc-configurator'),
		"nickname"	=> __('Nickname', 'cc-configurator'),
		"displayedname"	=> __('Displayed Name', 'cc-configurator'),
		);
	return $creator_arr;
}


function bccl_get_the_creator($who) {
	/*
	Return the creator/publisher of the licensed work according to the user-defined option (cc-creator)
	*/
	if ($who == "blogname") {
		return get_bloginfo("name");
	} elseif ($who == "firstlast") {
		return get_the_author_firstname() . " " . get_the_author_lastname();
	} elseif ($who == "lastfirst") {
		return get_the_author_lastname() . " " . get_the_author_firstname();
	} elseif ($who == "nickname") {
		return get_the_author_nickname();
	} elseif ($who == "displayedname") {
		return get_the_author();
	} else {
		return "ERROR";
	}
}



// Action

function bccl_add_to_header() {
	/*
	Adds a link element with "license" relation in the web page HEAD area.
	
	Also, adds style for the license block, only if the user has:
	 * enabled the display of such a block
	 * not disabled internal license block styling
	 * if it is single-post view
	*/
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	
	echo "\n<!-- Creative Commons License added by Creative-Commons-Configurator plugin - Get it at: http://www.g-loaded.eu/ -->\n";
	
	if ( $cc_settings["license_url"] && $cc_settings["options"]["cc_head"] == "1" ) {
		// Adds a link element with "license" relation in the web page HEAD area.
		echo "<link rel=\"license\" type=\"text/html\" href=\"" . bccl_get_license_url() . "\" />\n\n";
	}
	if (is_single() && $cc_settings["options"]["cc_body"] == "1" && $cc_settings["options"]["cc_no_style"] != "1") {
		// Adds style for the license block
		$color = $cc_settings["options"]["cc_color"];
		$bgcolor = $cc_settings["options"]["cc_bgcolor"];
		$brdrcolor = $cc_settings["options"]["cc_brdr_color"];
		$bccl_default_block_style = "width: 90%; margin: 8px auto; padding: 4px; text-align: center; border: 1px solid $brdrcolor; color: $color; background-color: $bgcolor;";
		$style = "<style type=\"text/css\"><!--\n.cc-block { $bccl_default_block_style }\n--></style>\n\n";
		echo $style;
	}
}


function bccl_add_cc_ns_feed() {
	/*
	Adds the CC RSS module namespace declaration.
	*/
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	if ( $cc_settings["options"]["cc_feed"] == "1" ) {
		echo "xmlns:creativeCommons=\"http://backend.userland.com/creativeCommonsRssModule\"\n";
	}
}

function bccl_add_cc_element_feed() {
	/*
	Adds the CC URL to the feeds.
	*/
	$cc_settings = get_option("cc_settings");
	if (!$cc_settings) { return ""; }
	if ( $cc_settings["license_url"] && $cc_settings["options"]["cc_feed"] == "1" ) {
		echo "<creativeCommons:license>" . bccl_get_license_url() . "</creativeCommons:license>\n";
	}
}


function bccl_append_to_post_body($PostBody) {
	/*
	Adds the license block under the published content.
	
	The check if the user has chosen to display a block under the published
	content is performed in bccl_get_license_block(), in order not to retrieve
	the saved settings two timesor pass them between functions.
	*/
	if ( is_single() ) {
		$cc_block = bccl_get_license_block("", "", "default", "default", TRUE);
		if ( $cc_block ) {
			$PostBody .= bccl_add_placeholders($cc_block);
		}
	}
	return $PostBody;
}


// ACTION

add_action('admin_menu', 'bccl_add_pages');

add_action('wp_head', 'bccl_add_to_header', 10);

add_filter('the_content', 'bccl_append_to_post_body', 250);

add_action('rdf_ns', 'bccl_add_cc_ns_feed');
add_action('rdf_header', 'bccl_add_cc_element_feed');
add_action('rdf_item', 'bccl_add_cc_element_feed');

add_action('rss2_ns', 'bccl_add_cc_ns_feed');
add_action('rss2_head', 'bccl_add_cc_element_feed');
add_action('rss2_item', 'bccl_add_cc_element_feed');

add_action('atom_ns', 'bccl_add_cc_ns_feed');
add_action('atom_head', 'bccl_add_cc_element_feed');
add_action('atom_entry', 'bccl_add_cc_element_feed');

?>
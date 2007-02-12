<?php
/*
Plugin Name: Creative Commons Configurator
Plugin URI: http://www.g-loaded.eu/2006/01/14/creative-commons-configurator-wordpress-plugin/
Description: Add Creative Commons license information to your blog and feeds.
Version: 0.5
Author: GNot
Author URI: http://www.g-loaded.eu/
*/

/*
License: GPL
Compatibility: Requires WordPress 2 or newer for full functionality.

Info:
This plugin lets you define the type of Creative Commons License you want to use for your blog
and also control the inclusion or display of this info into:
- The blog header
- The feeds (Atom, RDF, RSS 2.0)
- The blog posts in single post view
All these options are set from a configuration page in the admin panel.

Installation:
Place the cc-configurator.php file in your /wp-content/plugins/ directory
and activate through the administration panel.

Configuration:
Go to: Administration Panel -> Options -> License
*/

/*  Copyright (C) George Notaras (http://www.g-loaded.eu/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* Changelog
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


function bccl_add_pages() {
	add_options_page('License Options', 'License', 8, __FILE__, 'bccl_license_options');
}


function bccl_license_options () {

	if (isset($_POST['info_update'])) {
		update_option('bccl_header', $_POST['bccl_header']);
		update_option('bccl_feed', $_POST['bccl_feed']);
		update_option('bccl_body', $_POST['bccl_body']);
		update_option('bccl_body_extra', stripslashes($_POST['bccl_body_extra']));
		_e('<div id="message" class="updated fade"><p>Options saved.</p></div>');
	}

	// Check if license code has been submitted or a new license has been requested with bccl_code
	if ( isset($_POST['bccl_code']) && !empty($_POST['bccl_code']) ) {
		// If a license reset has been requested
		if ( $_POST['bccl_code'] == 'newlicense' ) {
			bccl_license_reset();
		// If license code is actually a complete CC license code (HTML and RDF)
		} elseif ( bccl_license_verification($_POST['bccl_code']) ) {
			bccl_add_license_to_db($_POST['bccl_code']);
		} else {
			// Show invalid license message
			_e('<div id="message" class="updated fade-ff0000"><p><strong>Invalid Creative Commons License.</strong></p></div>');
		}
	}

	// Show appropriate form
	$license_html = get_option('bccl_html');
	$license_rdf = get_option('bccl_rdf');
	if ( !empty($license_html) && !empty($license_rdf) ) {
		bccl_step_two();
	} else {
		bccl_step_one();
	}
}


function bccl_license_verification($code='') {
	// '/..../s' s->treats new lines as same line
	$pattern = '/[\s]*<!--[\s]*Creative[\s]*Commons[\s]*License[\s]*-->.*<!--[\s]*\/Creative[\s]*Commons[\s]*License[\s]*-->.*<!--[\s]*<rdf:RDF.*<License[\s]*rdf:about="http:\/\/creativecommons.org.*<\/License>[\s]*<\/rdf:RDF>[\s]*-->[\s]*/s';
	$replacement = '';
	$code = stripslashes($code);
	$license_remnant = preg_replace($pattern, $replacement, $code, 1);
	if ( empty($license_remnant) ) {
		return true;
	}
	return false;
}


function bccl_add_license_to_db($code='') {
	// Split license code to HTML and RDF part
	$License_Code_Arr = preg_split('/-->[\s]*<!--/', $code);
	// Add the code parts (options) to db - Other option are updated from the Options form
	update_option('bccl_html', stripslashes($License_Code_Arr[0] . '-->'));
	update_option('bccl_rdf', stripslashes('<!--' . $License_Code_Arr[1]));
	unset($License_Code_Arr);
	// Show license submission success message.
	_e('<div id="message" class="updated fade"><p><strong>Successful License Submission.</strong></p></div>');
}


function bccl_license_reset() {
	// Delete everything from the db
	delete_option('bccl_html');
	delete_option('bccl_rdf');
	delete_option('bccl_header');
	delete_option('bccl_feed');
	delete_option('bccl_body');
	delete_option('bccl_body_extra');
	// Show license reset success message.
	_e('<div id="message" class="updated fade"><p><strong>Successful License Reset. - Stored Options Cleared.</strong></p></div>');
}


function bccl_step_one() {
	// Step 1 - No license has been submitted or license was reset. Need to submit code
	_e('
	<div class="wrap">
    <h2>Creative Commons License Configuration</h2>
	<p>Here you can configure your blog\'s Creative Commons License.</p>
	<form name="formlicense" id="bccl_step_one" method="post" action="">
		<fieldset class="options">
			<legend>Provide the License Code<br />
				<small><em>(Follow the steps below in order to generate valid Creative Commons License code.)</em></small>
			</legend>
			<ol>
				<li>Visit the <a href="http://creativecommons.org/license/" title="Opens in new window" target="_blank">Creative Commons License selector</a> and make your choices about the license characteristics.</li>
				<li>While on the same page, press the &quot;<em>Select a License</em>&quot; button.</li>
				<li>Copy all the HTML code from the text box (click inside the text box and press <em>Ctrl-A</em> to select all of it before copying).</li>
				<li>Paste the code to the following text area.</li>
				<li>Press the &quot;<em>Update License</em>&quot; button.</li>
			</ol>
			<p>
				<textarea name="bccl_code" id="bccl_code" cols="60" rows="8" style="width: 98%; font-size: 12px;" class="code"></textarea>
			</p>
		</fieldset>
		<fieldset class="options">
			<legend>Info<br />
				<small>No options will be displayed or added to the database, until you submit some Creative Commons License code.</small>
			</legend>
		</fieldset>
		<p class="submit">
			<input type="submit" name="Submit" value="Update License &raquo;" />
		</p>
	</form>
	</div>
	');
}


function bccl_step_two() {
	// Step 2 - License has been submitted - Show license and options
	global $wp_version;

	_e('
	<div class="wrap">
	<h2>Current License</h2>
	<p style="text-align: center;"><big>' . bccl_get_license_text_hyperlink() . '</big></p>
	<form name="formlicense" id="bccl_reset" method="post" action="">
		<fieldset class="options">
			<legend>Info</legend>
			<p>
				If you need to use a different license for your blog, press the 
				&quot;<em>Reset License</em>&quot; button.<br />
				This plugin stores six extra options in the WordPress database in order to function. 
				By reseting the license, these options are removed. Consider reseting the license before 
				uninstalling the plugin, so that no trace is left behind.
			</p>
		</fieldset>
		<p class="submit">
			<input type="hidden" name="bccl_code" value="newlicense" />
			<input type="submit" name="Submit" value="Reset License &raquo;" />
		</p>
	</form>
	</div>

	<div class="wrap">
	<h2>License Options</h2>
	<p>Here you can select where the license info should be included in your blog.</p>
	<form name="formlicenseoptions" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
		<fieldset class="options">
			<legend>Page Header</legend>
			<ul><li>
				<input name="bccl_header" type="checkbox" id="bccl_header" value="1" ');
				checked('1', get_settings('bccl_header'));
				_e(' />
				<label for="bccl_header">Include license info into the page header. This will not be visible to humans, but bots can read it. <em>(Recommended)</em></label>
			</li></ul>
		</fieldset>
	'); if ( $wp_version >= 2 ) { _e('
		<fieldset class="options">
			<legend>Syndicated Content</legend>
			<ul><li>
				<input name="bccl_feed" type="checkbox" id="bccl_feed" value="1" ');
				checked('1', get_settings('bccl_feed'));
				_e(' />
				<label for="bccl_feed">Include license info in the feeds. <em>(Recommended)</em></label>
			</li></ul>
		</fieldset>
	'); } _e('
		<fieldset class="options">
			<legend>Post Body</legend>
			<p>
				By enabling the following option, a reference to the above license is displayed 
				below the post\'s body.
			</p>
			<ul><li>
				<input name="bccl_body" type="checkbox" id="bccl_body" value="1" ');
				checked('1', get_settings('bccl_body'));
				_e(' />
				<label for="bccl_body">Display license info after the post\'s body in the single post view.</label>
			</li></ul>
			<p>
				Apart from the default message, you can set some more info to be displayed in the 
				following text box. This message will be displayed in a new line. You can use any XHTML 
				tags that can be included inside the &lt;p&gt; tags. <em>(Please do not use any &lt;p&gt; 
				or &lt;small&gt; tags)</em> 
			</p>
			<p>
				<textarea name="bccl_body_extra" id="bccl_body_extra" cols="60" rows="1" style="width: 98%; font-size: 12px;" class="code">' . get_option('bccl_body_extra') . '</textarea>
			</p>
		</fieldset>
		<p class="submit">
			<input type="submit" name="info_update" value="Update Options &raquo;" />
		</p>
	</form>
	</div>

	<div class="wrap">
	<h2>Advanced Info</h2>
	<p>
		Apart from the default options for the display or inclusion of licensing info in your blog, 
		this plugin provides some <em>template tags</em>, which can be used in your theme. 
		These are the following:
	</p>
	<ul>
		<li><code>bccl_get_license_text_hyperlink()</code> - Returns the full text hyperlink of your current license for use in your php code.</li>
		<li><code>bccl_license_text_hyperlink()</code> - Displays the full text hyperlink.</li>
		<li><code>bccl_get_license_image_hyperlink() - Returns the full image hyperlink of the current license for use in your php code.</li>
		<li><code>bccl_license_image_hyperlink()</code> - Displays the full image hyperlink.</li>
		<li><code>bccl_get_license_deed_url()</code> - Returns the license\'s URL (<em>Deed URL</em>) for use in your php code.</li>
		<li><code>bccl_display_full_html_license()</code> - Displays the full HTML only code of your license. This includes the text and the image hyperlinks.</li>
	</ul>
	</div>

	');
}


// Some functions for convenience

// Returns Full TEXT hyperlink to License <a href=...>...</a>
function bccl_get_license_text_hyperlink() {
	$output = preg_replace('/^.*<img.*<a(.*)<\/a>.*$/is', '<a$1</a>', get_option('bccl_html'));
	return $output;
}

// Displays Full TEXT hyperlink to License <a href=...>...</a>
function bccl_license_text_hyperlink() {
	echo bccl_get_license_text_hyperlink();
}

// Returns Full IMAGE hyperlink to License <a href=...><img...</a>
function bccl_get_license_image_hyperlink() {
	$output = preg_replace('/^.*<a(.*<img.*)<\/a>.*<a.*$/is', '<a$1</a>', get_option('bccl_html'));
	return $output;
}

// Displays Full IMAGE hyperlink to License <a href=...><img...</a>
function bccl_license_image_hyperlink() {
	echo bccl_get_license_image_hyperlink();
}

// Returns only the license URL (Deed URL) http://creativecommons.org/license...
function bccl_get_license_deed_url() {
	$output = preg_replace('/^.*href="([^"]+).*$/is', '$1', get_option('bccl_html'));
	return $output;
}

// Returns the full HTML code of the license (same as get_option('bccl_html');
function bccl_display_full_html_license() {
	$output = get_option('bccl_html');
	echo $output;
}


// Action results

// PAGE HEADER
function bccl_add_to_header() {
	if ( get_option('bccl_header') == '1' ) {
		echo "\n" . get_option('bccl_rdf') . "\n\n";
	}
}

// FEEDS
function bccl_add_cc_ns_feed() {
	if ( get_option('bccl_feed') == '1' ) {
		echo "xmlns:creativeCommons=\"http://backend.userland.com/creativeCommonsRssModule\"\n";
	}
}

function bccl_add_cc_element_feed() {
	if ( get_option('bccl_feed') == '1' ) {
		echo "<creativeCommons:license>" . bccl_get_license_deed_url() . "</creativeCommons:license>\n";
	}
}

// POST BODY
function bccl_append_to_post_body($PostBody) {
	if ( is_single() ) {
		if ( get_option('bccl_body') == '1' ) {
			$output = "<h3 style=\"color: #777; margin-top: 1em;\"><small>License</small></h3>";
			$output .= "<p style=\"margin: 0;\"><small>This work is published under a ";
			$output .= bccl_get_license_text_hyperlink() . ".<br />";
			$output .= get_option('bccl_body_extra');
			$output .= "</small></p>";
			
			$PostBody .= $output;
		}
	}
	return $PostBody;
}


// ACTION

add_action('admin_menu', 'bccl_add_pages');

add_action('wp_head', 'bccl_add_to_header');

add_filter('the_content', 'bccl_append_to_post_body', 50);

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

<?php
/*
	Plugin Name: WP-Mint
	Plugin URI: http://www.dvhome.co.uk/wp-mint/
	Description: Automatically include the <a href="http://haveamint.com">Mint</a> javascript in your WordPress theme header
	Author: Dave Verwer
	Version: 1.2
	Author URI: http://www.dvhome.co.uk/
	
	--------------------------------------------------------------------------------

	Change Log:
		Version 1.2
			* Updated to work with Mint v1.23
			* Renamed options tab to fit plug-in name
			* Changed license to CC from GPL
		Version 1.1
			* Added option to output to either the head or body
			* Rewrote options page code & reorganised options UI
			* Options page now validates as XHTML 1.0 Transitional
		Version 1.0
			* Added option to turn Mint logging on/off
			* Added the Test/View feature to the Options screen
			* Re-worded the Options screen for readability
			* Tidied up the code
		Version 0.1
			* Initial Release

	--------------------------------------------------------------------------------

	Copyright 2005 Dave Verwer, Some Rights Reserved
		E-mail: dave@dvhome.co.uk
	
	This work is licensed under a Creative Commons License:
		Attribution-NonCommercial-ShareAlike
		http://creativecommons.org/licenses/by-nc-sa/2.5/
	
	--------------------------------------------------------------------------------
*/

// Define some constants for consistency
define("const_enabled", "enabled", true);
define("const_disabled", "disabled", true);
define("const_header", "header", true);
define("const_footer", "footer", true);

// Make sure we always refer to the same option keys and use consistent defaults
define("key_mint_path", "mint_path", true);
define("key_mint_status", "mint_status", true);
define("key_mint_script_position", "mint_script_position", true);
define("mint_path_default", "/mint", true);
define("mint_status_default", const_enabled, true);
define("mint_script_position_default", const_header, true);

// Create the default path, status and script location (should only execute once)
add_option(key_mint_path, mint_path_default, 'The path to your Mint installation.');
add_option(key_mint_status, mint_status_default, 'Is Mint stat logging enabled or disabled?');
add_option(key_mint_script_position, mint_script_position_default, 'Should the script be output in wp_head or wp_footer?');

// Hook in the action for the admin options page
add_action('admin_menu', 'add_mint_option_page');

// Hook in the script placement function to either the header or the footer
if (get_option(key_mint_script_position) == const_footer) {
	add_action('wp_footer', 'add_mint_javascript');
} else {
	add_action('wp_head', 'add_mint_javascript');
}

function add_mint_option_page() {
	// Hook in the options page function
	add_options_page('Mint Options', 'WP-Mint', 6, __FILE__, 'mint_options_page');
}

function mint_options_page() {
	// If we are a postback, store the options
 	if (isset($_POST['info_update'])) {
		// --------------------------------------------------------------
		// Process the status option
		// --------------------------------------------------------------
		$mint_status = $_POST[key_mint_status];

		// Check for validity, use default if invalid
		if (($mint_status != const_enabled) && ($mint_status != const_disabled))
			$mint_status = mint_status_default;

		// Update the database
		update_option(key_mint_status, $mint_status);

		// --------------------------------------------------------------
		// Process the path option
		// --------------------------------------------------------------
		$mint_path = $_POST[key_mint_path];

		// Use the default path if nothing was passed in
		if ($mint_path == '') $mint_path = mint_path_default;

		// Make sure slashes are correct
		$mint_path = trim($mint_path);							// Trim any whitespace
		$mint_path = str_replace('\\\\', '\\', $mint_path);		// Remove double's
		$mint_path = str_replace('\\', '/', $mint_path);		// Replace \ with /
		$mint_path = rtrim($mint_path, '/');					// Trim trailing slashes
		
		// Update the database with the new option value
		update_option(key_mint_path, $mint_path);

		// --------------------------------------------------------------
		// Process the script position option
		// --------------------------------------------------------------
		$mint_script_position = $_POST[key_mint_script_position];

		// Use the default path if nothing was passed in
		if ($mint_script_position == '') $mint_script_position = mint_script_position_default;

		// Update the database with the new option value
		update_option(key_mint_script_position, $mint_script_position);

		// Give an updated message
		echo "<div class='updated'><p><strong>Mint options updated</strong></p></div>";
	}

	// Output a simple options page
	?>
		<form method="post" action="options-general.php?page=wp-mint.php">
		<div class="wrap">
			<h2>Mint Options</h2>
			<fieldset class='options'>
				<legend>Mint Options</legend>
				<?php if (get_option(key_mint_status) == const_disabled) { ?>
					<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
					Mint integration is currently <strong>DISABLED</strong>, no stats are being sent to Mint.
					</div>
				<?php } ?>
				<table class="editform" cellspacing="2" cellpadding="5" width="100%">
					<tr>
						<th width="30%" valign="top" style="padding-top: 10px;">
							<label for="<?php echo key_mint_status ?>">Mint logging is:</label>
						</th>
						<td>
							<?php
							echo "<select name='".key_mint_status."' id='".key_mint_status."'>\n";
							
							echo "<option value='".const_enabled."'";
							if(get_option(key_mint_status) == const_enabled)
								echo " selected='selected'";
							echo ">Enabled</option>\n";
							
							echo "<option value='".const_disabled."'";
							if(get_option(key_mint_status) == const_disabled)
								echo" selected='selected'";
							echo ">Disabled</option>\n";
							
							echo "</select>\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="<?php echo key_mint_path; ?>">Path to Mint:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='".key_mint_path."' ";
							echo "id='".key_mint_path."' ";
							echo "value='".get_option(key_mint_path)."' />\n";
							?>
							<p style="margin: 5px 10px;">For example if your site is installed at <strong>http://www.example.com</strong>
							and Mint is installed at <strong>http://www.example.com/mint</strong> then your path
							would be &ldquo;<strong>/mint</strong>&rdquo;.</p>
						</td>
					</tr>
					<tr>
						<th valign="top">
							Script Position:
						</th>
						<td>
							<?php
							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='".key_mint_script_position."' ";
							echo "value='".const_header."'";
							if(get_option(key_mint_script_position) == const_header)
								echo " checked='checked'";
							echo " />&nbsp;Header</label><br />\n";
							
							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='".key_mint_script_position."' ";
							echo "value='".const_footer."'";
							if(get_option(key_mint_script_position) == const_footer)
								echo " checked='checked'";
							echo " />&nbsp;Body</label>\n";
							?>
							<p style="margin: 5px 10px 0px 10px;">It is recommended to have the Mint script in the
							header and you should only choose body if you are experiencing problems.</p>
							<p style="margin: 3px 10px 0px 10px;"><strong>Note:</strong> Header uses the <strong>wp_head()</strong> function to insert the
							script and Body uses <strong>wp_footer()</strong>, if your WordPress theme
							does not call either of these functions then this plug-in will not work.</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="submit">
							<input type='submit' name='info_update' value='Update Options' />
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class='options'>
				<legend>View/Test Mint</legend>
				<p>After you have updated the path above, click the button below to be redirected to your Mint stats page. If you do not get redirected successfully then the path configured above is incorrect.</p>
				<div class="submit" style="padding-right: 7px;">
					<input type='button' name='view_mint' value='View Your Mint' onclick="window.location='<?php echo get_option(key_mint_path); ?>';return true;" />
				</div>
			</fieldset>
		</div>
		</form>
	<?php
}

function add_mint_javascript($unused) {
	// The runtime function, only put the script in if the plug-in is enabled
	if (get_option(key_mint_status) != const_disabled) {
		// Get the mint path from the options and output the script tag
		$full_mint_path = get_option(key_mint_path)."/?js";
		echo "<script src='".$full_mint_path."' type='text/javascript' language='javascript'></script>\n";
	}
}
  	
?>

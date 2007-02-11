<?php
/*
Plugin Name: ClaimID Chicklet widget
Plugin URI: http://willnorris.com/
Description: Adds a sidebar widget to display ClaimID logo and link.  Modified directly from Fred's <a href="http://blog.claimid.com/2006/04/claimid-wordpress-widget/">ClaimID widget</a>.
Author: Will Norris
Version: 1.0
Author URI: http://willnorris.com/
*/

// This gets called at the plugins_loaded action
function widget_claimid_chicklet_init() {
	
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_claimid_chicklet_control() {
		$options = $newoptions = get_option('widget_claimid_chicklet');
		if ( $_POST['claimid-submit'] ) {
			$newoptions['username'] = strip_tags(stripslashes($_POST['claimid-username']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_claimid_chicklet', $options);
		}
	?>
				<div style="text-align:right">
				<label for="claimid-username" style="line-height:35px;display:block;">ClaimID login: <input type="text" id="claimid-username" name="claimid-username" value="<?php echo htmlspecialchars($options['username']); ?>" /></label>
				<input type="hidden" name="claimid-submit" id="claimid-submit" value="1" />
				</div>
	<?php
	}

	// This prints the widget
	function widget_claimid_chicklet($args) {
		global $install_directory, $siteurl;
		extract($args);
		$options = (array) get_option('widget_claimid_chicklet');

		$siteurl = get_option('siteurl');
		?>

		<?php echo $before_widget; ?>
		<?php echo $before_title . "<a href='http://claimid.com/{$options['username']}'><img src=\"$siteurl/wp-content/plugins/claimid/claimid.gif\"</a>" . $after_title; ?>
		<?php echo $after_widget; ?>
<?php
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget('ClaimID Chicklet', 'widget_claimid_chicklet');
	register_widget_control('ClaimID Chicklet', 'widget_claimid_chicklet_control');
	
}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('plugins_loaded', 'widget_claimid_chicklet_init');

?>

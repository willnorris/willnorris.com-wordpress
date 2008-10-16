<?php
/*
Plugin Name: hCard Chicklet widget
Plugin URI: http://willnorris.com/
Description: Adds a sidebar widget to display hcard logo and link.
Author: Will Norris
Version: 1.0
Author URI: http://willnorris.com/
*/

// This gets called at the plugins_loaded action
function widget_hcard_chicklet_init() {
	
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_hcard_chicklet_control() {
		$options = $newoptions = get_option('widget_hcard_chicklet');
		if ( $_POST['hcard-submit'] ) {
			$newoptions['url'] = strip_tags(stripslashes($_POST['hcard-url']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_hcard_chicklet', $options);
		}
	?>
				<div style="text-align:right">
				<label for="hcard-url" style="line-height:35px;display:block;">hcard url: <input type="text" id="hcard-url" name="hcard-url" value="<?php echo htmlspecialchars($options['url']); ?>" /></label>
				<input type="hidden" name="hcard-submit" id="hcard-submit" value="1" />
				</div>
	<?php
	}

	// This prints the widget
	function widget_hcard_chicklet($args) {
		global $install_directory, $siteurl;
		extract($args);
		$options = (array) get_option('widget_hcard_chicklet');

		$siteurl = get_option('siteurl');
		?>

		<?php echo $before_widget; ?>
		<?php echo $before_title . '<a href="'.$options['url'].'" rel="me"><img src="'.$siteurl.'/wp-content/plugins/hcard-chicklet/hcard.png" alt="hCard" /></a>'.$after_title; ?>
		<?php echo $after_widget; ?>
<?php
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget('hcard Chicklet', 'widget_hcard_chicklet');
	register_widget_control('hcard Chicklet', 'widget_hcard_chicklet_control');
	
}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('plugins_loaded', 'widget_hcard_chicklet_init');

?>

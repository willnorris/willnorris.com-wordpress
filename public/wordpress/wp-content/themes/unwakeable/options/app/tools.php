<?php
class tools {
	function uninstall() {
		global $wpdb;

		// Remove the K2 options from the database
		$cleanup = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'unwakeable%'");

		// Remove the SBM stub
		$sbm_stub_path = '../themes/' . get_option('template') . '/options/app/sbm-stub.php';
		$plugins = (array)get_option('active_plugins');
		$found = false;

		for($i = 0; !$found && $i < count($plugins); $i++) {
			if($plugins[$i] == $sbm_stub_path) {
				unset($plugins[$i]);
				update_option('active_plugins', $plugins);
				$found = true;
			}
		}

		// Flush the dang cache
		wp_cache_flush();

		// Activate the default Wordpress theme so as not to re-install K2
		update_option('template', 'default');
		update_option('stylesheet', 'default');
		do_action('switch_theme', 'Default');

		// Go back to the themes page
		header('Location: themes.php');
		exit;
	}
	function convert() {
		global $wpdb;

		// Grab the K2 options
		$k2Options = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE option_name LIKE 'k2%'", ARRAY_N);
		if (count($k2Options) < "1") {
			$success = "1";
		}
		else {
			foreach ($k2Options as $k) {
				$optionId = $k[0];
				$blogId = $k[1];
				$optionName = $k[2];
				$optionCanOverride = $k[3];
				$optionType = $k[4];
				$optionValue = $k[5];
				$optionWidth = $k[6];
				$optionHeight = $k[7];
				$optionDescription = $k[8];
				$optionAdminLevel = $k[9];
				$autoLoad = "yes";
				if ($optionName == "k2aboutblurp") {
					update_option('unwakeable_aboutblurp', $optionValue);
				}
				if ($optionName == "k2archives") {
					update_option('unwakeable_archives', $optionValue);
				}
				if ($optionName == "k2asidescategory") {
					update_option('unwakeable_asidescategory', $optionValue);
				}
				if ($optionName == "k2asidesnumber") {
					update_option('unwakeable_asidesnumber', $optionValue);
				}
				if ($optionName == "k2asidesposition") {
					update_option('unwakeable_asidesposition', $optionValue);
				}
				if ($optionName == "k2blogornoblog") {
					update_option('unwakeable_blogornoblog', $optionValue);
				}
				if ($optionName == "k2livecommenting") {
					update_option('unwakeable_livecommenting', $optionValue);
				}
				if ($optionName == "k2livesearch") {
					update_option('unwakeable_livesearch', $optionValue);
				}
				if ($optionName == "k2rollingarchives") {
					update_option('unwakeable_rollingarchives', $optionValue);
				}
				if ($optionName == "k2sbm_modules_active") {
					update_option('unwakeable_sbm_modules_active', $optionValue);
				}
				if ($optionName == "k2sbm_modules_disabled") {
					update_option('unwakeable_sbm_modules_disabled', $optionValue);
				}
				if ($optionName == "k2sbm_modules_next_id") {
					update_option('unwakeable_sbm_modules_next_id', $optionValue);
				}
				if ($optionName == "k2scheme") {
					update_option('unwakeable_scheme', $optionValue);
				}
				if ($optionName == "k2styleinfo") {
					update_option('unwakeable_styleinfo', $optionValue);
				}
				if ($optionName == "k2styleinfo_format") {
					update_option('unwakeable_styleinfo_format', $optionValue);
				}
				if ($optionName == "k2widthtype") {
					update_option('unwakeable_widthtype', $optionValue);
				}
			}
			if (mysql_error() == "") {
				$success = "0";
			}
			else {
				$success = "2";
			}
		}
		// Flush the dang cache
		wp_cache_flush();
		if ($success == "1") {
			// if no previous k2 options found
			header('Location: themes.php?page=functions.php&optionscopied=1');
		}
		else if ($success == "2") {
			// if a mysql error occurs
			header('Location: themes.php?page=functions.php&optionscopied=2');
		}
		else if ($success == "0") {
			// if we're successful!
			header('Location: themes.php?page=functions.php&optionscopied=0');
		}
		exit;
	}
}
?>

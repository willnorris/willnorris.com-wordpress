<?php
class installk2 {
	function installer() {
			add_option('k2installed', $current, 'This options simply tells me if K2 has been installed before', $autoload);
			add_option('k2aboutblurp', '', 'Allows you to write a small blurp about you and your blog, which will be put on the frontpage', $autoload);
			add_option('k2asidescategory', '0', 'A category which will be treated differently from other categories', $autoload);
			add_option('k2asidesposition', '0', 'Whether to use inline or sidebar asides', $autoload);
			add_option('k2livesearch', '1', "If you don't trust JavaScript and Ajax, you can turn off LiveSearch. Otherwise I suggest you leave it on", $autoload); // (live & classic)
			add_option('k2asidesnumber', '3', 'The number of Asides to show in the Sidebar. Default is 3.', $autoload);
			add_option('k2widthtype', '1', "Determines whether to use flexible or fixed width. Default is fixed.", $autoload); // (flexible & fixed)
			add_option('k2archives', '', 'Set whether K2 has a Live Archive page', $autoload);
			add_option('k2scheme', '', 'Choose the Scheme you want K2 to use', $autoload);
			add_option('k2livecommenting', '1', "If you don't trust JavaScript, you can turn off Live Commenting. Otherwise it is suggested you leave it on", $autoload);
			add_option('k2styleinfo_format', 'Current style is <a href="%stylelink%" title="%style% by %author%">%style% %version%</a> by <a href="%site%">%author%</a><br />', 'Format for displaying the current selected style info.', $autoload);
			add_option('k2styleinfo', '', 'Formatted string for style info display.', $autoload);
			add_option('k2rollingarchives', '1', "If you don't trust JavaScript and Ajax, you can turn off Rolling Archives. Otherwise it is suggested you leave it on", $autoload);
			add_option('k2blogornoblog', 'Blog', 'The text on the first tab in the header navigation.', $autoload);

			// SBM options
			add_option('k2sbm_modules_active', array());
			add_option('k2sbm_modules_disabled', array());
			add_option('k2sbm_modules_next_id', 1);
	}
}
?>
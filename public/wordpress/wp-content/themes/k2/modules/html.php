<?php

function html_sidebar_module($args) {
	extract($args);

	echo($before_module . $before_title . $title . $after_title);
	echo(stripslashes(sbm_get_option('html')));
	echo($after_module);
}

function html_sidebar_module_control() {
	if(isset($_POST['html_module_html'])) {
		sbm_update_option('html', $_POST['html_module_html']);
	}

	?>
		<p><label for="html-module-html">Module's HTML:</label><br /><textarea id="html-module-html" name="html_module_html" rows="6" cols="30"><?php echo(stripslashes(sbm_get_option('html'))); ?></textarea></p>
	<?php
}

register_sidebar_module('HTML module', 'html_sidebar_module', 'sb-html');
register_sidebar_module_control('HTML module', 'html_sidebar_module_control');

?>

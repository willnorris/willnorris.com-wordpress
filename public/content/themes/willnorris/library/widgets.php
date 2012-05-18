<?php

/**
 * Register new sidebar named "Front Page".
 */
function willnorris_register_sidebars() {
	register_sidebar(array(
		'id' => 'front-page-top',
		'name' => 'Front Page Top',
		'before_widget' => '<li id="%1$s" class="widgetcontainer %2$s">',
		'after_widget' => "</li>",
		'before_title' => "<h3 class=\"widgettitle\">",
		'after_title' => "</h3>\n",
	));
	register_sidebar(array(
		'id' => 'front-page-aside',
		'name' => 'Front Page Aside',
		'before_widget' => '<li id="%1$s" class="widgetcontainer %2$s">',
		'after_widget' => "</li>",
		'before_title' => "<h3 class=\"widgettitle\">",
		'after_title' => "</h3>\n",
	));
}
add_action('init', 'willnorris_register_sidebars', 11);



class ContactList_Widget extends WP_Widget {
	function ContactList_Widget() {
		parent::__construct('contactlist', 'Contact List');
	}

	function widget($args, $instance) {
		extract($args);

		$arguments = array(
			'category_before' => '', 
			'category_after' => '', 
			'title_before' => '<h3>',
			'title_after' => '</h3>',
		);  

		?>
			<?php echo $before_widget; ?>
				<?php echo $before_title
					. $instance['title']
					. $after_title; ?>
				<?php wp_list_bookmarks($arguments); ?>
			<?php echo $after_widget; ?>
        <?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("ContactList_Widget");'));



// unregister duplicate widgets
function willnorris_unregister_widgets() {
	unregister_widget('WP_Widget_Meta');
	unregister_widget('WP_Widget_Search');
}
add_action('widgets_init', 'willnorris_unregister_widgets');

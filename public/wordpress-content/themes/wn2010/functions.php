<?php

function two_body_class( $classes ) {
  $classes[] = 'bp';
  $classes[] = 'two-col';
  return $classes;
}
add_filter('body_class', 'two_body_class');

/**
 * Additional arguments to use when building the main menu.  
 */
function willnorris_page_menu_args($args) {
	$args['depth'] = 1;
  $args['show_home'] = false;
	return $args;
}
add_filter('wp_page_menu_args', 'willnorris_page_menu_args', 11);


/**
 * Exclude front page from menu, since the <h1> links there already.
 */
function willnorris_list_pages_exludes($excludes) {
	if (get_option('show_on_front') == 'page') {
		$excludes[] = get_option('page_on_front');
	}

	$openid_support = get_page_by_path('openid-support');
	if ( $openid_support ) {
		$excludes[] = $openid_support->ID;
	}

	return $excludes;
}
add_filter('wp_list_pages_excludes', 'willnorris_list_pages_exludes');

function willnorris_wp( $wp ) {
  wp_enqueue_script('jquery');
}
add_action('wp', 'willnorris_wp');

function willnorris_resize_images() {
?>
  <script>
  jQuery(function() {
    var $p = jQuery('<p>A</p>').hide().appendTo('body');
    var lineHeight = $p.height();
    $p.remove();

    jQuery('#container img').each( function(index, img) {
      var $img = $(img);
      console.log( $img.height() );
      if ( $img.height() % lineHeight != 0 ) {
        var h = $img.height() - ( $img.height() % lineHeight );
        var w = h * $img.width() / $img.height();
        $img.height(h).width(w);
      }
    });
  });
  </script>
<?php
}
add_action('wp_footer', 'willnorris_resize_images');

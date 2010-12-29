<?php

show_admin_bar( false );

/**
 * Additional arguments to use when building the main menu.  
 */
function willnorris_page_menu_args($args) {
  // only go one level deep
	$args['depth'] = 1;

  // don't show link to home page
  $args['show_home'] = false;
	return $args;
}
add_filter('wp_page_menu_args', 'willnorris_page_menu_args', 11);


/**
 * Exclude front page from menu, since the <h1> links there already.
 */
function willnorris_list_pages_exludes($excludes) {
  // don't show front page
	if (get_option('show_on_front') == 'page') {
		$excludes[] = get_option('page_on_front');
	}

  // don't show /openid-support page
	$openid_support = get_page_by_path('openid-support');
	if ( $openid_support ) {
		$excludes[] = $openid_support->ID;
	}

	return $excludes;
}
add_filter('wp_list_pages_excludes', 'willnorris_list_pages_exludes');

function willnorris_wp( $wp ) {
  wp_enqueue_script('jquery');
  wp_enqueue_script('typekit', 'http://use.typekit.com/fmx0rji.js');
}
add_action('wp', 'willnorris_wp');

function willnorris_typekit_load() {
  echo '<script>try{Typekit.load();}catch(e){}</script>';
}
add_action('wp_head', 'willnorris_typekit_load');

function willnorris_resize_images() {
?>
  <script>
  jQuery(function() {
    var $p = jQuery('<p>A</p>').hide().appendTo('body');
    var lineHeight = $p.height();
    $p.remove();

    jQuery('#container img').each( function(index, img) {
      var $img = $(img);
      //console.log( $img.height() );
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

function willnorris_emphasize_tagline($output, $show) {
  if ( $show == 'description' ) {
    $output = preg_replace('/(more|this)/', '<em>\\1</em>', $output);
  }
  return $output;
}
add_filter('bloginfo', 'willnorris_emphasize_tagline', 10, 2);


function willnorris_comment_form_defaults($defaults) {
  // todo - only display when markdown is actually in effect
  $defaults['comment_notes_after'] = '
  <div id="form-markdown-allowed" class="form-section">
    <p>You may use <a href="http://daringfireball.net/projects/markdown/syntax">Markdown</a> syntax or basic <abbr title="' . esc_attr( allowed_tags() ) . '">HTML</abbr>.</p>
  </div>';

  return $defaults;
}
add_action('comment_form_defaults', 'willnorris_comment_form_defaults');


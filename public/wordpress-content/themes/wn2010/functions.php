<?php

show_admin_bar( false );

function willnorris_wp( $wp ) {
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery.masonry',
    get_stylesheet_directory_uri() . '/js/jquery.masonry.min.js', array('jquery'), '1.3.2', true);
  wp_enqueue_script('typekit', 'http://use.typekit.com/fmx0rji.js');
}
add_action('wp', 'willnorris_wp');

function willnorris_typekit_load() {
  echo '<script>try{Typekit.load();}catch(e){}</script>';
}
add_action('wp_head', 'willnorris_typekit_load');

function willnorris_footer_js() {
?>
  <script>
  jQuery(function($) {
    // resize images
    var $p = $('<p>A</p>').hide().appendTo('body');
    var lineHeight = $p.height();
    $p.remove();

    $('#container img').each( function(index, img) {
      var $img = $(img);
      var height = $img.outerHeight();
      if ( height % lineHeight != 0 ) {
        var h = height - ( height % lineHeight );
        var w = h * $img.outerWidth() / height;
        $img.height(h).width(w);
      }
    });
  });

  // masonry
  jQuery(function($) {
    $('.sidebar').masonry({
      singleMode: true,
      itemSelector: 'section',
      resizeable: false,
    });
    $(window).resize(function() {
      $('.sidebar').masonry({
        columnWidth: $('.sidebar section').outerWidth(true)
      });
    });
  });
  </script>
<?php
}
add_action('wp_footer', 'willnorris_footer_js');

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


/**
 * Shortcode for displaying my age, in years.
 */
function willnorris_my_age() {
  $now = getdate();
  $age = $now['year'] - 1982;

  if ($now['mon'] < 7 && $now['mday'] < 30) {
    $age -= 1;
  }

  return $age;
}
add_shortcode('my_age', 'willnorris_my_age');


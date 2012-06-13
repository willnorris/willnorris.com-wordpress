<?php
  global $post;
  $googleplus_url = get_post_meta($post->ID, '_googleplus_url', true);
  if ($post->post_type == 'post' && $googleplus_url) {
?>
  <hr />
  <p>
    I no longer have comments on my blog.  Instead, I encourage you to
    <a href="<?php echo $googleplus_url; ?>">comment here on Google+</a>, or
    even better, continue the discussion on your own blog.
  </p>
<?php
  }
?>

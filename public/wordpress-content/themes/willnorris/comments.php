<?php
  $googleplus_url = get_post_meta($post->ID, '_googleplus_url', true);
  if ($post->post_type == 'post' && $googleplus_url) {
?>
  <p>
    I no longer have comments on my blog.  Instead, I encourage you to 
    <a href="<?php echo $googleplus_url; ?>">comment here on Google+</a>.  
    If you don't have a Google+ account and would like one, just let me know.
    Of course, you're also always welcome to post a reply on your own blog, 
    on twitter, or wherever you choose to publish.
  </p>
<?php
  }
?>

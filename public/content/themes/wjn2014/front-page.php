<?php

add_action( 'genesis_after_header', function() {
  $me = 2;
?>
  <div class="home-top p-author h-card vcard">
    <div class="wrap">
      <img class="u-photo photo alignleft" src="/logo.jpg" alt="" />
      <h2 class="p-name fn">
        <a class="u-url url" href="<?php esc_attr_e(the_author_meta('user_url', $me)); ?>"><?php esc_html_e(the_author_meta('display_name', $me)); ?></a>
      </h2>
      <p class="p-note note"><?php echo get_user_meta($me, 'description', true); ?></p>
      <?php wp_nav_menu('theme_location=social'); ?>
    </div>
  </div>
<?php
});

genesis();

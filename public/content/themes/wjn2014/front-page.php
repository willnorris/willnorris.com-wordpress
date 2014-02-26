<?php

add_action( 'genesis_after_header', function() {
?>
  <div class="home-top p-author h-card">
    <div class="wrap">
      <img class="u-photo alignleft" src="/images/logo.jpg" alt="" />
      <p><?php echo get_user_meta(2, 'description', true); ?></p>
      <?php wp_nav_menu('theme_location=social'); ?>
    </div>
  </div>
<?php
});

genesis();

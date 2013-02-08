<?php
// personal tweaks to the publish theme

function wjn_publish_cleanup_hooks() {
  remove_action('publish_credits', 'publish_footer_credits');
}
add_action('wp', 'wjn_publish_cleanup_hooks');

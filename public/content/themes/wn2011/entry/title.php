<?php
  if ( is_front_page() || get_post_format() == 'link' ) {
    // do nothing
  } else {
    get_template_module('entry/title', true);
  }
?>

<?php

/**
 * Initialize admin pages.
 */
function slides_admin_init() {
  register_setting('permalink', 'slides_base');
  add_settings_field('slides_base', __('Slides base', 'slides'), 'slides_permalink_form', 'permalink', 'optional');
}
add_action('admin_init', 'slides_admin_init');


/**
 * Add slides permastruct base to the Permalinks admin page.
 */
function slides_permalink_form() {
  global $blog_prefix;

  if ( isset($_POST['slides_base']) ) {
    check_admin_referer('update-permalink');
    update_option('slides_base', trim($_POST['slides_base']));
    flush_rewrite_rules();
  }

  $slides_base = get_option('slides_base');

  echo $blog_prefix
    . '<input id="slides_base" class="regular-text code" type="text" value="' . esc_attr($slides_base) . '" name="slides_base" />';
}


/**
 * Register meta boxes for the 'slides' post type.
 */
function slides_add_meta_boxes() {
  add_meta_box('slides-url', __('Slides URL', 'slides'), 'slides_url_meta_box', 'slides');
}
add_action('add_meta_boxes', 'slides_add_meta_boxes');


/**
 * Content of the 'slides url' meta box.
 */
function slides_url_meta_box( $post ) {
  $url = get_post_meta( $post->ID, '_slides_url', true );
  echo '<p>' . __('Enter the URL for these slides.', 'slides') . '</p>
  <input style="width:99%" type="text" name="slides_url" value="' . esc_attr( $url ) . '" />';
}


/**
 * Save custom data.
 */
function slides_save_post( $post_id, $post ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  $slides_meta_keys = array('slides_url');
  wp_reset_vars( $slides_meta_keys );

  foreach ($slides_meta_keys as $key) {
    global $$key;
    if ( empty($$key) ) {
      delete_post_meta( $post_id, '_' . $key );
    } else {
      update_post_meta( $post_id, '_' . $key, $$key );
    }
  }

}
add_action('save_post', 'slides_save_post', 10, 2);


<?php

class WJN_Publish {

  public function __construct() {
    add_action('after_setup_theme', array($this, 'setup'));
    // add_action('wp_enqueue_scripts', array($this, 'js'), 20);
    // add_action('author_link', array($this, 'author_link'), 10, 3);
  }

  /**
   * Initialize the theme, registering WordPess hooks.
   */
  public function setup() {
    add_filter('stylesheet_uri', array($this, 'stylesheet_uri') );
  }

  /**
   * Return {theme_dir}/css/screen.css as the stylesheet_uri.
   */
  public function stylesheet_uri( $stylesheet_uri ) {
    $stylesheet_dir_uri = get_stylesheet_directory_uri();
    $stylesheet_uri = $stylesheet_dir_uri . '/css/style.css';
    return $stylesheet_uri;
  }

}

new WJN_Publish;

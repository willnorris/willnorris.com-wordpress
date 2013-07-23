<?php

class WJN_Publish {

  public function __construct() {
    add_action('after_setup_theme', array($this, 'setup'));
    add_action('wp', array($this, 'style'), 20);
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

  public function style() {
    wp_enqueue_style('font-awesome',
      '//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css',
      false, null);
  }

}

new WJN_Publish;

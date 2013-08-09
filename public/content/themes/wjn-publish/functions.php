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

function publish_posted_on() {
  printf( __( 'Posted on <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date dt-published" datetime="%3$s" pubdate>%4$s</time></a><span class="byline"> by <span class="author vcard h-card p-author">%8$s<a class="u-url p-name" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'publish' ) . '.',
    esc_url( get_permalink() ),
    esc_attr( get_the_time() ),
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date() ),
    esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
    esc_attr( sprintf( __( 'View all posts by %s', 'publish' ), get_the_author() ) ),
    esc_html( get_the_author() ),
    get_avatar( get_the_author_meta( 'ID' ) )
  );
}

      <footer class="entry-utility">
        <?php
          // no tags or categories for now.  maybe add them back in later.
          /*
          $tag_list = get_the_tag_list( '', ', ' );
          if ( $tag_list ) {
            $posted_in = __( 'This entry was posted in %1$s and tagged %2$s.' );
          } else {
            $posted_in = __( 'This entry was posted in %1$s.' );
          }
          printf( $posted_in . ' ', get_the_category_list( ', ' ), $tag_list );
          */

          printf('<a href="%1$s"><time datetime="%2$s" title="%2$s" class="entry-date">%3$s</time></a>',
            esc_attr( get_permalink() ),
            esc_attr( get_the_time('c') ),
            get_the_date()
          );

          echo willnorris_plusone_button($post);
        ?>
      </footer><!-- #entry-utility -->

<?php 
  if ( is_front_page() && !is_paged() ):
    $authors = get_users('who=authors');
    if ( sizeof($authors) == 1 ):
      $bio = get_user_meta($authors[0]->ID, 'description', true);
      $bio = apply_filters('the_content', $bio);
?>

  <section id="about">
    <h3>About</h3>

    <?php echo $bio ?>
  </section>

<?php 
    endif; 
  endif; 
?>

<?php get_template_module('loop'); ?>

<?php 
  $pdx_nav_id = 'below'; 
  get_template_module('nav');
?>

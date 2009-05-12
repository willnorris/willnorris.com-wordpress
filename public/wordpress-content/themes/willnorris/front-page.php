<?php
/*
Template Name: Front Page
*/
?>
<?php get_header() ?>
	
	<div id="container">
		<div id="content">

<?php 
if ( is_sidebar_active('front-page-top') ) { 
	dynamic_sidebar('front-page-top');
}
?>

<?php the_post() ?>
			<div id="post-<?php the_ID(); ?>" class="<?php thematic_post_class() ?>">
				<div class="entry-content">
<?php the_content() ?>

<?php wp_link_pages("\t\t\t\t\t<div class='page-link'>".__('Pages: ', 'thematic'), "</div>\n", 'number'); ?>

<?php edit_post_link(__('Edit', 'thematic'),'<span class="edit-link">','</span>') ?>

				</div>
			</div><!-- .post -->

<?php if ( get_post_custom_values('comments') ) comments_template('', true) // Add a key+value of "comments" to enable comments on this page ?>

<?php get_sidebar('page-bottom') ?>

		</div><!-- #content -->
	</div><!-- #container -->

<?php 
	// sidebar
	if ( true || is_sidebar_active('front-page-aside') ) { 
?>
	<div id="primary" class="aside main-aside">
		<ul class="xoxo">
			<?php dynamic_sidebar('primary-aside'); ?>
		</ul>
	</div>

	<div id="secondary" class="aside main-aside">
		<ul class="xoxo">
			<?php dynamic_sidebar('front-page-aside'); ?>
		</ul>
	</div>
<?php 
	} else {
		get_sidebar();
	}
?>

<?php get_footer() ?>

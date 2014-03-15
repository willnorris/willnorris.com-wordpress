<?php
	$prefix = '';

	// Get Core WP Functions If Needed
	if (isset($_GET['rolling'])) {
		require (dirname(__FILE__)."/../../../wp-blog-header.php");
		$prefix = 'nested_';
	}

	// WP 2.1 support
	if (is_array($wp_query->query)) {
		$rolling_query = http_build_query($wp_query->query);
	} else {
		$rolling_query = $wp_query->query;
	}
?>
<?php
	// Load Rolling Archives?
	if ( (get_option('k2rollingarchives') == 1) ) { 
		$k2pagecount = k2countpages($wp_query);

		if ($k2pagecount > 1) {
?>
	<div id="<?php echo $prefix; ?>rollingarchives">
		<div id="<?php echo $prefix; ?>rollnavigation">
			<a href="#" id="<?php echo $prefix; ?>rollprevious"><span>&laquo;</span> <?php _e('Older','k2_domain'); ?></a>
			<a href="#" id="<?php echo $prefix; ?>rollhome"><img src="<?php bloginfo('template_directory'); ?>/images/house.png" alt="Home" /></a>

			<div id="<?php echo $prefix; ?>pagetrackwrap"><div id="<?php echo $prefix; ?>pagetrack"><div id="<?php echo $prefix; ?>pagehandle"></div></div></div>

			<span id="<?php echo $prefix; ?>rollload"><?php _e('Loading','k2_domain'); ?></span>
			<span id="<?php echo $prefix; ?>rollpages"></span>

			<a href="#" id="<?php echo $prefix; ?>rollnext"><?php _e('Newer','k2_domain'); ?> <span>&raquo;</span></a>

			<div id="<?php echo $prefix; ?>trimmerContainer">

				<a href="#" id="<?php echo $prefix; ?>trimmerMore"><?php _e("More","k2_domain"); ?></a>
				<div id="<?php echo $prefix; ?>trimmer"></div>
				<a href="#" id="<?php echo $prefix; ?>trimmerLess"><?php _e("Less","k2_domain"); ?></a>

				<div id="<?php echo $prefix; ?>trimmerShortcuts">
					<a href="#" id="<?php echo $prefix; ?>trimmerExcerpts"><?php _e("Excerpts","k2_domain"); ?></a>
					<a href="#" id="<?php echo $prefix; ?>trimmerHeadlines"><?php _e("Headlines","k2_domain"); ?></a>
					<a href="#" id="<?php echo $prefix; ?>trimmerFulllength"><?php _e("Full Length","k2_domain"); ?></a>
				</div>
			</div>
		</div>

		<div id="<?php echo $prefix; ?>rollnotices"></div>

	</div>
	<script type="text/javascript">
	// <![CDATA[
		var <?php echo $prefix; ?>rolling = new RollingArchives("primarycontent", <?php k2info('js_url'); ?> + '/theloop.php', "<?php echo $rolling_query; ?>", <?php echo $k2pagecount; ?>, "<?php echo $prefix; ?>", "<?php _e('Page %1$d of %2$d',k2_domain); ?>");
	// ]]>
	</script>

<?php } } ?>
<div id="<?php echo $prefix; ?>primarycontent" class="hfeed">
	<?php include (TEMPLATEPATH . '/theloop.php'); ?>
</div><!-- #<?php echo $prefix; ?>primarycontent .hfeed -->

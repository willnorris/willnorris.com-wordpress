	<div class="clear"></div>

</div> <!-- Close Page -->

<hr />

<div id="footer">
	<?php //printf(__('%1$s is powered by %2$s and %3$s','k2_domain'), get_bloginfo('name') , '<a href="http://wordpress.org/" title="Where children sing songs of binary bliss">WordPress ' . get_bloginfo('version') . '</a>', '<a href="http://getk2.com/" title="Loves you like a kitten.">K2<!--' . get_k2info('version') . '--></a>' ) ?>
	<?php if (function_exists('bccl_get_license_deed_url')) printf(__('&copy; %s, Will Norris.  <a href="%s">Some Rights Reserved</a>.'), date('Y'), bccl_get_license_deed_url()) ?><br />
	<?php if (function_exists('k2_style_info')) { k2_style_info(); } ?>

	<?php $styleinfo = get_k2info('style_info'); if ($styleinfo != '') { ?><p class="footerstyledwith"><?php printf(__('Styled with %s','k2_domain'), $styleinfo); ?></p><?php } ?>

	<!--
	<p class="footerpoweredby"><?php printf( __('Powered by %1$s and %2$s','k2_domain'),
		sprintf('<a href="http://wordpress.org/">%1$s <!--%2$s--></a>',
			__('WordPress','k2_domain'),
			get_bloginfo('version')
		),
		sprintf('<a href="http://getk2.com/" title="%1$s">K2<!--%2$s--></a>',
			__('Loves you like a kitten.','k2_domain'),
			get_k2info('version')
		)
	); ?></p>
	-->

	<p class="footerfeedlinks"><?php printf(__('<a href="%1$s">Entries Feed</a> and <a href="%2$s">Comments Feed</a>','k2_domain'), get_bloginfo('rss2_url'), get_bloginfo('comments_rss2_url')) ?></p>
	<!-- <?php printf(__('%d queries. %.4f seconds.','k2_domain'), $wpdb->num_queries , timer_stop()) ?> -->
</div>

<?php wp_footer(); ?>

</body>
</html> 

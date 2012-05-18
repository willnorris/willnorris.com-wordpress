<?php

function willnorris_redirect_short_url($redirect_url, $requested_url) {
	global $wp_query;

	if ( array_key_exists('p', $wp_query->query) ) {
		$p = $wp_query->query['p'];
		$id = base_convert($p, 36, 10);
		$post = get_post($id);

		if ($post != null) {
			$redirect_url = get_permalink($post->ID);
		}
	}

	return $redirect_url;
}
add_filter('redirect_canonical', 'willnorris_redirect_short_url', 10, 2);


function willnorris_rev_canonical() {
	if ( is_single() ) {
		global $post;
		$short_url = get_bloginfo('home') . '/p/' . base_convert($post->ID, 10, 36);
?>
<link rev="canonical" type="text/html" href="<?php echo $short_url; ?>" />
<?php
	}
}
add_action('wp_head', 'willnorris_rev_canonical', 1);


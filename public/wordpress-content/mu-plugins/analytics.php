<?php
/*
Plugin Name: Google Analytics
Description: Minimal plugin for adding Google Analytics tracking code.
Author: Will Norris
Author URI: http://willnorris.com/
*/

function analytics_tracking_code() {
  if ( !defined('GOOGLE_ANALYTICS_ID') ) return;

  // don't track logged in administrators
  if ( is_user_logged_in() && current_user_can('manage_options') ) return;

?>
<script>
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS_ID; ?>']);
<?php do_action('analytics_tracking_js'); ?>
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<?php

}
add_action('wp_head', 'analytics_tracking_code', 99);


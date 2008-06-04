<?php
/*
Part of Plugin: Absolute Comments
*/

if (!defined('ABSPATH')) require_once('../../../../wp-config.php');
if (!function_exists('wp_ozh_cqr_take_over') or !current_user_can('edit_posts')) die('You cannot do this');

if (!@$_POST) die();

// now we have an array of $comment_id => $post_id

// Sanitize the array: only integers
$_POST = array_map('intval',$_POST);

// fetch post types for all post ids
$posts = join(" OR ID=", $_POST); 
$sql = $wpdb->prepare("SELECT ID,post_type FROM $wpdb->posts WHERE (ID=$posts)");
$_types = $wpdb->get_results($sql);

// build a simple array of $post_id => $post_type
$types = array();
foreach ($_types as $k=>$_post) {
	$types[$_post->ID] = $_post->post_type;
}

// Output
header('Content-Type: text/xml');
echo "<?"."xml version=\"1.0\"?>\n"; 
echo "<response>\n";

foreach ($_POST as $cid => $pid) {
	$type = $types[$pid];
	echo "<comment><id>$cid</id><post>$pid</post><type>$type</type></comment>\n";
}

echo "<queries>$wpdb->num_queries</queries>\n";
echo "</response>\n";

?>
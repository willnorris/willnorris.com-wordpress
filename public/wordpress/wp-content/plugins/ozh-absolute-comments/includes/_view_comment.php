<?php
/*
Part of Plugin: Absolute Comments
*/

$comment = $wp_ozh_cqr['comment'];

if (!function_exists('wp_ozh_cqr_take_over')) die('You cannot do this.');

echo <<<HTML
<style>
#the-comment-list thead, #the-comment-list th  {
	background-color:#CFEBF7;
	color:black;
	font-size:14px;
}
#the-comment-list, #the-comment-list td, #the-comment-list th {
	border-color:white;
}
#thiscommentwrap {
	margin:20px 8px 0 20px;
	border:1px solid #EBEBEB;
	border-color:#EBEBEB rgb(204, 204, 204) rgb(204, 204, 204) rgb(235, 235, 235);
	padding:2px;
}
</style>
<div id="thiscommentwrap" class="postarea">
<table id="the-comment-list" class="widefat list:comment">
<thead>
  <tr>
    <th scope="col">Comment</th>
	<th scope="col">Date</th>
    <th scope="col">Actions</th>
  </tr>
</thead>
HTML;

_wp_comment_row($comment->comment_ID, 'detail', '', false);

echo "</table></div>";

?>
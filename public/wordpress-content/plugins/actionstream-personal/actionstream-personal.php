<?php
/*
 Plugin Name: ActionStream Personal Extensions
 Description: Adds additional services to the DiSo ActionStream plugin
 Author: Will Norris
 Author URI: http://willnorris.com/
 Version: trunk
 License: Dual GPL (http://www.fsf.org/licensing/licenses/info/GPLv2.html) and Modified BSD (http://www.fsf.org/licensing/licenses/index_html#ModifiedBSD)
 */

function actionstream_personal_services($services) {
	// DiSo
	$services['services']['diso'] = array(
		'name' => 'DiSo',
		'url' => 'http://diso-project.org/',
	);

	$services['streams']['diso'] = array(
		'commits' => array(
			'name' => 'Commits',
			'description' => 'Your Recent Commits',
			'html_form' => '[_1] made a code commit <a class="entry-title" href="[_2]">[_3]</a>',
			'html_params' => array('url', 'title'),
			'url' => 'http://code.google.com/feeds/p/diso/svnchanges/basic',
			'xpath' => array(
				'foreach' => '//entry[author/name=\'%s\']',
				'get' => array(
					'created_on' => 'published/child::text()',
					'modified_on' => 'updated/child::text()',
					'title' => 'title/child::text()',
					'url' => 'link[@rel=\'alternate\']/@href',
					'identifier' => 'id/child::text()'
				),
			),
		),
	);


	return $services;
}
add_filter('actionstream_services', 'actionstream_personal_services', 5);


function actionstream_personal_styles() {
	$url = plugins_url('actionstream-personal') . '/style.css';
	echo '<link rel="stylesheet" type="text/css" href="'.clean_url($url).'" />';
}

add_action('wp_head', 'actionstream_personal_styles', 11);
add_action('admin_head', 'actionstream_personal_styles', 11);
?>

<?php
/*
 Plugin Name: Brightkite Profile
 Description: Adds last Brightkite check to your Profile
 Author: DiSo Development Team
 Author URI: http://diso-project.org/
 Version: trunk
 */


add_action('extended_profile', 'brightkite_profile', 12);


function brightkite_profile($userid) {
	global $wpdb;

	$loc = get_usermeta($userid, 'brightkite_profile_location');

	$sql = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'actionstream_items WHERE user_id=%s AND service=%s AND setup_idx=%s ORDER BY created_on DESC', $userid, 'brightkite', 'checkins');
	$last_checkin = $wpdb->get_row($sql);

	if (empty($loc) || $last_checkin->identifier_hash != $loc['hash']) {

		$checkin_data = unserialize($last_checkin->data);
		$response = wp_remote_get($checkin_data['url'] . '.xml');
		$checkin_xml = simplexml_load_string($response['body']);

		if ($checkin_xml !== false) {
			$loc = array(
				'hash' => $last_checkin->identifier_hash,
				'name' => "{$checkin_xml->place->name}",
				'url' => $checkin_data['url'],
				'timestamp' => (int) $checkin_xml->created_at_ts,
				'lat' => "{$checkin_xml->place->latitude}",
				'lon' => "{$checkin_xml->place->longitude}",
			);

			update_usermeta($userid, 'brightkite_profile_location', $loc);
		}

	}

	if ($loc) {
?>
	<dl class="geo">
		<dt><abbr class="latitude" title="<?php _e($loc['lat']) ?>">Last</abbr> <abbr class="longitude" title="<?php _e($loc['lon']) ?>">Seen</abbr>:</dt>
		<dd><a href="<?php _e($loc['url']) ?>"><?php _e($loc['name']) ?></a><br /> <em>(<?php echo human_time_diff($loc['timestamp'], time()); ?> ago)</em></dd>
	</dl>
<?php
	}
		
}


?>

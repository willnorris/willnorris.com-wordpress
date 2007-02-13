<?php
# -- WordPress Plugin Interface -----------------------------------------------
/*
Plugin Name: wp-xrds
Plugin URI: http://willnorris.com/projects/wp-xrds/
Description: 
Version: 1.0
Author: Will Norris
Author URI: http://willnorris.com/
*/

if (isset($wp_version)) {
    add_action('wp_head', array('XRDS', 'insert_meta_tags'), 5);
    add_action('admin_menu', array('XRDS', 'menu'));

	add_action('parse_query', array('XRDS', 'xrds_xml'));
	add_filter('rewrite_rules_array', array('XRDS', 'rewrite_rules'));
	add_filter('query_vars', array('XRDS', 'query_vars'));
}

class XRDS {

	/**
	 * Insert meta and link tags into template head.
	 */
	function insert_meta_tags() {
		echo '
				<meta http-equiv="X-XRDS-Location" content="'.get_option('home').'/xrds.xml" />';

        $xrdsProviders = get_option('xrds_services');
		if ($provider = current($xrdsProviders)) {
			echo '
				<link rel="openid.server" href="'.$provider['server'].'" />
				<link rel="openid.delegate" href="'.$provider['delegate'].'" />';
		}
	}


	/**
	 * Register Admin Menu.
	 */
    function menu() {
        if (function_exists('add_options_page')) {
            add_options_page('XRDS Options', 'XRDS', 9, __FILE__, array('XRDS', 'manage'));
        }
    }


	/** 
	 * Manage admin option page.
	 */
    function manage() {
        $xrdsProviders = get_option('xrds_services');
		$updated = false;

        if (isset($_REQUEST['action'])) {
			switch($_REQUEST['action']) {
				case 'submit':
					if ($provider = XRDS::update($xrdsProviders)) {
						$xrdsProviders[] = $provider;
						$updated = true;
					}
					break;

				case 'delete':
					if (isset($_REQUEST['id'])) {
						unset($xrdsProviders[$_REQUEST['id']]);
						$updated = true;
					}
					break;
			}
		}

		if ($updated) {
			update_option('xrds_services', $xrdsProviders);
		}
		?>

		<script type="text/javascript"> 
			function showInputForm(value) {
				if (value != null) {
					if (value == "other") {
						document.getElementById('xrds_predefined_service').style.display='none';
						document.getElementById('xrds_custom_service').style.display='block';
					} else {
						document.getElementById('xrds_predefined_service').style.display='block';
						document.getElementById('xrds_custom_service').style.display='none';
					}
				}
			}
		</script>

        <div class="wrap">
        	<h2>XRDS Options</h2>

			<?php if ($updated) { 
				echo '<div id="message" class="updated fade"><p>'.__('Changes have been saved', '').'</p></div>';
			} ?>

			<form method="post">
				<fieldset class="options">
					<legend><?php _e('XRDS Services') ?></legend>

						<table>
							<tr><th>Server</th><th>Delegate</th><th></th></tr>

		<?php
        $id=0;
		foreach ($xrdsProviders as $k => $v) {
			if ($v) {
				echo '
                        <tr>
                            <td>'.$v['server'].'</td>
                            <td>'.$v['delegate'].'</td>
							<td><a href="?page='.$_REQUEST['page'].'&action=delete&id='.$k.'">Delete</a></td>
                        </tr>';
				$id++;
			}
		}?>

					</table>

					<h3>Add New Service</h3>
						<select name="xrds-id" onchange="showInputForm(this.value);">
							<option>- Add a new XRDS provider -</option>

		<?php
		// input box for new provider
		$predefined = XRDS::predefined();
		foreach ($predefined as $k => $v) {
			echo'
							<option value="'.$k.'">'.$v[0].'</option>';
		}
		?>

							<option value="other">Other...</option>
						</select>
			
						<div id="xrds_new_service_form" style="margin-top: 1em;">
							<div id="xrds_predefined_service" style="display: none;">
								Username: <input name="xrds-username" size="30"/>
							</div>

							<table id="xrds_custom_service" style="display: none;">
								<tr>
									<th>XRDS Server</th>
									<td><input name="xrds-server" size="30" /></td>
								</tr>
								<tr>
									<th>XRDS Delegate</th>
									<td><input name="xrds-delegate" size="30"/></td>
								</tr>
							</table>
						</div>

						<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
						<div class="submit"><input type="submit" name="action" value="<?php _e('submit', '') ?>" /></div>
				</fieldset>
       		</form>
        </div>

	<?php
    }


	/**
	 *
	 */
	function update() {
		$provider = null;

		if ($_REQUEST['xrds-id']) {
			if ($_REQUEST['xrds-id'] == 'other') {
				 $provider = array(
					'server' => $_REQUEST['xrds-server'],
					'delegate' => $_REQUEST['xrds-delegate'],
					'simplereg' => false,
				);
			} else {
				$provider = XRDS::build_provider_definition($_REQUEST['xrds-id'], $_REQUEST['xrds-username']);
			}
		}

		return $provider;
	}


	/**
	 *
	 */
	function build_provider_definition($providerID, $username) {
		$provider = Array();

		$xrdsProviders = XRDS::predefined();
		if (array_key_exists($providerID, $xrdsProviders)) {
			$provider['server'] = $xrdsProviders[$providerID][1];
			$provider['delegate'] = preg_replace('/%/', $username, $xrdsProviders[$providerID][2]);
			$provider['simplereg'] = $xrdsProviders[$providerID][3];
			
			return $provider;
		}
	}


	/**
	 * Get pre-defined identity providers
	 */
    function predefined() {
		$providers = array(
			'claimid' => array('ClaimID', 'http://openid.claimid.com/server','https://openid.claimid.com/%',true),
			'livejournal' => array('LiveJournal', 'http://www.livejournal.com/openid/server.bml','http://%.livejournal.com/',true),
			'myopenid' => array('MyOpenID', 'https://www.myopenid.com/server','https://%.myopenid.com/',true),
		);

		return $providers;
    }


	/**
	 * URL rewriting stuff, to serve xrds.xml
	 */
	function rewrite_rules($rules) {
		$xrds_rules = array('xrds.xml$' => 'index.php?xrds=music');
		return $rules + $xrds_rules;
	}

	function query_vars($vars) {
		$vars[] = 'xrds';

		return $vars;
	}

	function xrds_xml($query) {
		if (!empty($query->query_vars['xrds'])) {

			header('content-type: application/xrds+xml');
			$xrdsProviders = get_option('xrds_services');
			echo '<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS xmlns="xri://$xrd*($v*2.0)" xmlns:xrds="xri://$xrds" xmlns:openid="http://openid.net/xmlns/1.0">
	<XRD>';
	foreach($xrdsProviders as $k => $v) {
		echo'
		<Service priority="'.$k.'">
			<Type>http://openid.net/signon/1.0</Type>
			<URI>'.$v['server'].'</URI>
			<openid:Delegate>'.$v['delegate'].'</openid:Delegate>
		</Service>
';
	}
	echo '
	</XRD>
</xrds:XRDS>';
			exit;
		}
	}

}

?>

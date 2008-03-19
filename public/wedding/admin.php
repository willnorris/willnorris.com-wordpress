<?php
	require_once 'lib/common.php';
	
	$bodyTitle = 'Admin';
	$bodyId = 'admin';
	pageHeader();

	$invitations = Invitation::getAll();
	$responses = array(
		'Y' => 0,
		'N' => 0,
		'-' => 0,
	);

	echo '<table id="guests">';
	foreach($invitations as $invite) {
		foreach ($invite->guests as $guest) {
			echo '
			<tr>
			<td>' . $guest->name . '</td>';
			if ($invite->received) {
				if ($guest->attending) {
					$response['Y']++;
					echo'<td class="yes">Y</td>';
				} else {
					$response['N']++;
					echo'<td class="no">N</td>';
				}
			} else {
				$response['-']++;
				echo '<td></td>';
			}
			echo'
			</tr>';
		}
	}
	echo '</table>';

	echo '
		Yes = ' . $response['Y'] . '<br />
		No = ' . $response['N'] . '<br />
		No Response = ' . $response['-'] . '<br />
	';


	pageFooter();
?>

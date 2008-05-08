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

	echo '<table id="guests" cellPadding="0" cellSpacing="0">';
	foreach($invitations as $invite) {
		$first = true;
		foreach ($invite->guests as $guest) {
			echo '
			<tr>
			<td>' . $guest->name . '</td>' .
			($first ? '<td>' . $invite->code . '</td>'  : '<td>&nbsp;</td>');
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
				echo '<td>&nbsp;</td>';
			}
			echo'
			</tr>';

			$first = false;
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

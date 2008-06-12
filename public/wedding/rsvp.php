<?php
	require_once 'lib/common.php';
	
	$action = (array_key_exists('action', $_REQUEST) && $_REQUEST['action']) ? $_REQUEST['action'] : null;
	$display = (array_key_exists('display', $_REQUEST) && $_REQUEST['display']) ? $_REQUEST['display'] : null;

	$bodyTitle = 'RSVP';
	$bodyId = 'rsvp';
	pageHeader();
?>

<?php
	/*
	if (!$action && array_key_exists('code', $_GET) && $_GET['code']) {
		$action = 'code';
	}


	switch($action) {
		case 'code': 
			$invite = Invitation::getByCode($_REQUEST['code']);

			if ($invite->received) {
				$display = 'submitted';	break;
			}
		
			$display = 'list';
			break;
		case 'rsvp':
			$invite = Invitation::getByCode($_REQUEST['code']);

			if ($invite->received) {
				$display = 'submitted';	break;
			}

			$primaryAttending = false; # is a primary (non editable) guest attending
			$guestAttending = false; # is an editable guest attending

			foreach ($invite->guests as $guest) {
				if (!array_key_exists('attend_' . $guest->id, $_REQUEST)) {
					$error = $messages['mark_all_guests'];
					$display = 'list'; break 2;
				}

				$guest->attending = $_REQUEST['attend_' . $guest->id];

				if ($guest->editable) {
					$guest->name = $_REQUEST['name_' . $guest->id];
					if ($guest->attending && $guest->name == "Name of Guest") {
						$error = $messages['need_guest_name'];
						$display = 'list'; break 2;
					}
				}

				if ($guest->attending) {
					if ($guest->editable) {
						$guestAttending = true;
					} else {
						$primaryAttending = true;
					}
				}
			}

			if ($guestAttending && !$primaryAttending) {
				$error = $messages['no_free_pass'];
				$display = 'list'; break;
			}

			$invite->update();
			
			$display = 'success';
			break;
	}  


	switch ($display) {

		case 'submitted':
			echo $messages['invite_submitted'];
			echo '<table id="invitation_guests" style="margin: 1em auto;" cellpadding="0" cellspacing="0">
				<tr><th>&nbsp;</th><th>Will Attend</th></tr>';

			foreach($invite->guests as $guest) {
				echo '
				<tr>
					<td>'.$guest->name.'</td>
					<td>'.($guest->attending ? 'Yes' : 'No').'</td>
				</tr>';
			}
	
			echo '
				</table>';
			break;

		case 'list':
			if ($error) {
				echo '<div class="error">'.$error.'</div>';
			}

			echo '
			<p>For each guest, please indicate whether or not they will be able to attend.  If your invitation includes an unnamed guest, please provide their name in the space provided.</p>

			<form id="guestsForm" action="'.$_SERVER['PHP_SELF'].'" method="post">
				<input type="hidden" name="action" value="rsvp" />
				<input type="hidden" name="code" value="'.$_REQUEST['code'].'" />
				<table id="invitation_guests" cellpadding="0" cellspacing="0">
				<tr><th>&nbsp;</th><th>Will Attend</th><th>Will Not Attend</th></tr>';

			foreach($invite->guests as $guest) {
				echo '
				<tr>
					<td>' . ($guest->editable ? '<input type="text" name="name_'.$guest->id.'" value="Name of Guest" size="20" />' : $guest->name) . '</td>
					<td><input type="radio" name="attend_'.$guest->id.'" value="1" /></td>
					<td><input type="radio" name="attend_'.$guest->id.'" value="0" /></td>
				</tr>';
			}
	
			echo '
				</table>
				<input type="submit" value="submit" />
			</form>';
			break;

		case 'error':
			echo 'error';
			break;

		case 'success':
			echo '<p>'.$messages['invite_success'].'</p>';
			break;

		default:
?>

		<form id="rsvpForm" method="get">
			<label for="code">Enter your RSVP code:</label>
			<input type="text" id="code" name="code" />
			<input type="submit" value="submit" />
		</form>

		<p>If you have trouble entering your RSVP code or have lost your code, please <a href="contact">contact Will</a>.
		<script type="text/javascript">
			document.getElementById('code').focus();
		</script>
<?php } 
	 */
	echo "You can't RSVP... the wedding is already over.";

	pageFooter();
?>

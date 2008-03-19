<?php


class Invitation
{
	public $id;
	public $code;

	public $addressee;
	public $address;
	public $city;
	public $state;
	public $zip;

	public $received;
	public $guests;

	public static function getAll() {
		global $db;

		$invitations = array();

		$sql = "SELECT * FROM invitation";
		$res =& $db->query($sql);
		if (PEAR::isError($res)) {
			die($res->getMessage());
		}
		while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {

			# build object
			$invite = new Invitation();
			$invite->code = $row['code'];
			$invite->id = $row['id'];
			$invite->addressee = $row['addressee'];
			$invite->address = $row['address'];
			$invite->city = $row['city'];
			$invite->state = $row['state'];
			$invite->zip = $row['zip'];
			$invite->received = $row['received'];

			$invite->loadGuests();
			$invitations[] = $invite;
		}

		return $invitations;
	}

	public static function getByCode($code) {
		global $db;

		# query database
		$sql = "SELECT * FROM invitation WHERE code='$code'";
		$res =& $db->query($sql);
		if (PEAR::isError($res)) {
			die($res->getMessage());
		}
		$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

		# build object
		$invite = new Invitation();
		$invite->code = $code;
		$invite->id = $row['id'];
		$invite->addressee = $row['addressee'];
		$invite->address = $row['address'];
		$invite->city = $row['city'];
		$invite->state = $row['state'];
		$invite->zip = $row['zip'];
		$invite->received = $row['received'];

		$invite->loadGuests();
		return $invite;
	}

	private function loadGuests() {
		global $db;

		$this->guests = array();

		$sql = "SELECT * FROM guest WHERE invitation_id='".$this->id."'";
		$res =& $db->query($sql);
		if (PEAR::isError($res)) {
			die($res->getMessage());
		}

		while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$guest = new Guest();
			$guest->id = $row['id'];
			$guest->name = $row['name'];
			$guest->editable = $row['editable'];
			$guest->attending = $row['attending'];
			$this->guests[] = $guest;
		}

	}

	public function update() {
		global $db, $alertEmail;

		$subject = sprintf("RSVP received for %s", $this->addressee);
		$message = sprintf("RSVP received for %s:\n\n", $this->addressee);

		$sql = 'UPDATE invitation SET received=now() WHERE id="'.$this->id.'"';
		$res = $db->exec($sql);
		if (PEAR::isError($res)) {
			die ($res->getMessage());
		}

		foreach ($this->guests as $guest) {
			$message .= sprintf("%s - %s\n", $guest->name, ($guest->attending ? "yes" : "no"));
			$guest->update();
		}

		mail($alertEmail, $subject, $message, "From: $alertEmail");
	}
}


?>

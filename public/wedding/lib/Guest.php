<?php

class Guest
{
	public $id;
	public $name;
	public $editable;
	public $attending;

	public function update() {
		global $db;

		if ($this->editable) {
			$sql = 'UPDATE guest SET name="'.$this->name.'", attending="'.$this->attending.'" WHERE id="'.$this->id.'"';
		} else {
			$sql = 'UPDATE guest SET attending="'.$this->attending.'" WHERE id="'.$this->id.'"';
		}

		$res = $db->exec($sql);
		if (PEAR::isError($res)) {
			die ($res->getMessage());
		}
	}
}


?>

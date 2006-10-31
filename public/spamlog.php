<?php
$cacheFile = '/tmp/wnorris.spamlog.cache';

if (!file_exists($cacheFile) || (mktime() - filectime($cacheFile))>3600 || isset($_REQUEST['update'])) {
	rebuildCacheFile();
}

echo file_get_contents($cacheFile);


function rebuildCacheFile() {
	global $cacheFile;

	ini_set('include_path', ini_get('include_path').':/usr/local/lib/php');
	require_once 'DB.php';

	$db = DB::connect('mysql://willnorris_email:qg5rax4mJFVQ@mysql.willnorris.com/willnorris_email');
	$res = $db->query('SELECT * FROM message');
	$messages = array();

	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
		$m = new Message($row['date'], strtolower($row['to']), $row['spam-score'], $row['adjustment']);
		$messages['_all'][] = $m;
	}

	for ($i=0; $i<sizeof($messages['_all']); $i++) {
		$m =& $messages['_all'][$i];

		$_date = date('Y-m-d', strtotime($m->date));
		$messages['date'][$_date][] =& $messages['_all'][$i];

		$_score = ($m->score>=10 ? 'certain-spam' : ($m->score>=5 ? 'likely-spam' : 'ham'));
		$messages['score'][$_score][] =& $messages['_all'][$i];

		$messages['accounts'][$m->email][$_score]++;

		$_type = (($m->score>=10 && $m->adjustment>=0) || $m->adjustment>0 ? 'spam' : 'ham');
		$messages['adjusted'][$m->email][$_type]++;

		if ($m->score>=5 && $m->adjustment<0) $messages['false'][$m->email]['pos']++;
		if ($m->score<5 && $m->adjustment>0) $messages['false'][$m->email]['neg']++;

		if ($m->score<5) {
			if ($m->adjustment<=0) $messages['filed']['inbox']['ham']++;
			if ($m->adjustment>0) $messages['filed']['inbox']['spam']++;
			$messages['filed']['inbox']['total']++;
		} else if ($m->score>=5 && $m->score<10) {
			if ($m->adjustment<=0) $messages['filed']['junk']['ham']++;
			if ($m->adjustment>0) $messages['filed']['junk']['spam']++;
			$messages['filed']['junk']['total']++;
		} else if ($m->score>=10) {
			$messages['filed']['devnull']++;
		}
	}



	ob_start();

	echo '<h3>What address is my spam coming to?</h3>
	<table border="1" cellpadding="5" cellspacing="0">
	<tr><th>Account</th><th>Ham</th><th>Spam</th><th>Total</th></tr>';

	$totals = array();
	foreach ($messages['adjusted'] as $key => $value) {
		$total = array_sum($value);

		$totals['ham'] += $value['ham'];
		$totals['spam'] += $value['spam'];
		$totals['all'] += $total;

		echo '
		<tr>
			<td>'.preg_replace(array('/@/', '/\./'), array(' # ', ' _ '), $key).'</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $value['ham'], ($value['ham']/$total*100)).'</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $value['spam'], ($value['spam']/$total*100)).'</td>
			<td>'.$total.'</td>
		</tr>';
	}
	echo'
		<tr>
			<td>Total</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $totals['ham'], ($totals['ham']/$totals['all']*100)).'</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $totals['spam'], ($totals['spam']/$totals['all']*100)).'</td>
			<td>'.$totals['all'].'</td>
		</tr>';

	echo '</table>';

	echo '<h3>Where is my spam going?</h3>
	<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th>Folder</th>
			<th>Ham</th>
			<th>Spam</th>
			<th>Total</th>
		</tr>

		<tr>
			<td>Inbox</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $messages['filed']['inbox']['ham'], ($messages['filed']['inbox']['ham']/$messages['filed']['inbox']['total']*100)).'</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $messages['filed']['inbox']['spam'], ($messages['filed']['inbox']['spam']/$messages['filed']['inbox']['total']*100)).'</td>
			<td>'.$messages['filed']['inbox']['total'].'</td>
		</tr>

		<tr>
			<td>Junk</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $messages['filed']['junk']['ham'], ($messages['filed']['junk']['ham']/$messages['filed']['junk']['total']*100)).'</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $messages['filed']['junk']['spam'], ($messages['filed']['junk']['spam']/$messages['filed']['junk']['total']*100)).'</td>
			<td>'.$messages['filed']['junk']['total'].'</td>
		</tr>

		<tr>
			<td>/dev/null</td>
			<td></td>
			<td>'.$messages['filed']['devnull'].'</td>
			<td>'.$messages['filed']['devnull'].'</td>
		</tr>

		<tr>
			<td>Total</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $totals['ham'], ($totals['ham']/$totals['all']*100)).'</td>
			<td>'.sprintf('%d <small>(%.0f%%)</small>', $totals['spam'], ($totals['spam']/$totals['all']*100)).'</td>
			<td>'.$totals['all'].'</td>
		</tr>
	</table>';


	$caughtSpam = $messages['filed']['inbox']['spam']/$totals['spam'];
	echo '
	<h3>How effective are my spam filters?</h3>
	<ul>
		<li>'.sprintf('%.0f%%', 100-($caughtSpam*100)).' of spam is being caught <small>('.sprintf('%.0f%%', $caughtSpam*100).' is slipping through)</small>.</li>
		<li>'.sprintf('%.0f%%', $messages['filed']['junk']['ham']/$totals['ham']*100).' of ham is being mistakenly tagged as spam.</li>
		<li>'.sprintf('%.0f%%', $messages['filed']['inbox']['spam']/$messages['filed']['inbox']['total']*100).' of the email that makes it to my inbox is spam.</li>
	</ul>';


	$res = $db->query('SELECT date FROM message ORDER BY date ASC LIMIT 1');
	list($startDate) = $res->fetchRow();
	echo "<p><small>Report covers all mail received since $startDate</small></p>";

	if (array_key_exists('debug', $_REQUEST)) {
		echo '<pre>';
		print_r($messages);
		echo '</pre>';
	}

	$fh = fopen($cacheFile, 'w');
	fwrite($fh, ob_get_contents());

	fclose($fh);
	ob_end_clean();
}



class Message {
	public $date;
	public $email;
	public $score;
	public $adjustment;

	function __construct($date, $email, $score, $adjustment) {
		$this->date = $date;
		$this->email = $email;
		$this->score = $score;
		$this->adjustment = $adjustment;
	}
}
?>

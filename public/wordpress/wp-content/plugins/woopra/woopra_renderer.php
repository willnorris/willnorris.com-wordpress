<?


function woopra_render_results($entries, $key) {

if ($entries == null || sizeof($entries) == 0) {
?>
<p align="center">Your query returned 0 results.<br/>Click <a href="#" onclick="refreshCurrent(); return false;">here</a> to retry again!</p>
<?php
return;
}

	sort_analytics_response($entries);
	
	if (woopra_contains($key,'BY_DAY')) {
		renderChartData($entries,$key);
		return;
	}
	if (woopra_contains($key,'GET_REFERRERS&')) {
		if (woopra_contains($key,'&id=')) {
			renderExpandedReferrers($entries, $key);
		}
		else {
			renderReferrers($entries, $key);
		}
		return;
	}
	
	switch ($key) {
		case 'GET_GLOBALS':
			renderOverview($entries);
			break;
		case 'GET_COUNTRIES':
			include_once 'woopra_countries.php';
			renderDefaultModel($entries,'GET_COUNTRIES');
			break;
		default:
			renderDefaultModel($entries,$key);
			break;
	}
}

function renderChartData($entries, $key) {
	$counter = 0;
	$max = woopra_get_max($entries, 'hits');

	$max = woopra_rounded_max($max);

	$values = '';
	$labels = '';
	foreach($entries as $entry) {
		$day = (int)$entry['day'];
		$hits = (int)$entry['hits'];
		if ($values != '') {
			$values .= ',';
			$labels .= ',';
		}
		$values .= $hits;
		$labels .= woopra_encode(woopra_line_chart_date($day));
	}
	$values = $values;
	$labels = $labels;
	
	$data = "&x_label_style=10,0x000000,0,5&\r\n";
	$data .= "&x_axis_steps=1&\r\n";
	$data .= "&y_ticks=5,10,5&\r\n";
	$data .= "&line=3,0xB0E050,Jan,12&\r\n";
	$data .= "&values=$values&\r\n";
	$data .= "&x_labels=$labels&\r\n";
	$data .= "&y_min=0&\r\n";
	$data .= "&y_max=$max&\r\n";
	$data .= "&tool_tip=%23x_label%23%3A+%23val%23%20hits&
\r\n";
	echo $data;
}

function renderDefaultModel($entries, $key) {
?>
<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
<tr>
	<th>&nbsp;</th>
	<th><?php echo woopra_render_name($key); ?></th>
	<th class="center" width="100">Hits</th>
	<th width="400">&nbsp;</th>
</tr>
<?php
$counter = 0;
$sum = woopra_get_sum($entries, 'hits');
foreach($entries as $entry) {

$id = (int)$entry['id'];
$name = urldecode($entry['name']);
$hits = (int)$entry['hits'];
$meta = urldecode($entry['meta']);
$percent = 0;
if ($sum != 0) {
	$percent = round($hits*100/$sum);
}

$hashid = woopra_friendly_hash($key);
?>
<tr<?php echo (($counter++%2==0)?" class=\"even_row\"":""); ?>>
	<td class="wrank"><?php echo $counter; ?></td>
	<td><span class="ellipsis"><?php echo woopra_render_name($key,$name,$meta); ?></span></td>
	<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $key; ?>_BY_DAY', '<?php echo $hashid; ?>',<?php echo $id; ?>)"><?php echo $hits; ?></a></td>
	<td class="wbar"><?php echo woopra_bar($percent); ?></td>
</tr>
<tr id="wlc-<?php echo $hashid; ?>-<?php echo $id; ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $id ?>" colspan="4"></td></tr>
<?php
}
?>

</table>
<?php
}


function renderReferrers($entries, $key) {

$bydaykey = str_replace('GET_REFERRERS&','GET_REFERRERS_BY_DAY&',$key);
?>
<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
<tr>
	<th>&nbsp;</th>
	<th>Referrer</th>
	<th class="center" width="100">Hits</th>
	<th width="400">&nbsp;</th>
</tr>
<?php

$counter = 0;
$sum = woopra_get_sum($entries, 'hits');
foreach($entries as $entry) {

$id = (int)$entry['id'];
$name = urldecode($entry['name']);
$hits = (int)$entry['hits'];
$meta = urldecode($entry['meta']);

$percent = 0;
if ($sum != 0) {
	$percent = round($hits*100/$sum);
}
else {
	//print_r($entries); // DEBUG
}
$hashid = woopra_friendly_hash($key);
?>
<tr<?php echo (($counter++%2==0)?" class=\"even_row\"":""); ?>>
	<td class="wrank"><?php echo $counter; ?></td>
<?php if (woopra_key_expansible($key)) { ?>
	<td><span class="ellipsis"><a href="#" onclick="return expandReferrer('<?php echo $key . '&id=' . $id; ?>', '<?php echo $hashid .'-'. $id; ?>')"><?php echo woopra_render_name($key,$name,$meta); ?></a></span></td>
<?php } else { ?>
	<td><span class="ellipsis"><a href="http://<?php echo $name; ?>" target="_blank"><?php echo woopra_render_name($key,$name,$meta); ?></a></span></td>
<?php } ?>
	<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $bydaykey; ?>', '<?php echo $hashid; ?>',<?php echo $id; ?>)"><?php echo $hits; ?></a></td>
	<td class="wbar"><?= woopra_bar($percent) ?></td>
</tr>
<tr id="wlc-<?php echo $hashid; ?>-<?= $id ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?= $hashid ?>-<?php echo $id; ?>" colspan="4"></td></tr>
<tr id="refexp-<?php echo $hashid; ?>-<?php echo $id; ?>" style="display: none;"><td colspan="4" style="padding: 0;"><div id="refexptd-<?php echo $hashid; ?>-<?php echo $id; ?>"></div></td></tr>
<?php
}
?>

</table>
<?php
}


function renderExpandedReferrers($entries, $key) {

$bydaykey = str_replace('GET_REFERRERS&','GET_REFERRERS_BY_DAY&',$key);
$bydaykey = str_replace 
?>
<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
<?php

$counter = 0;
$sum = woopra_get_sum($entries, 'hits');
foreach($entries as $entry) {

$id = (int)$entry['id'];
$name = urldecode($entry['name']);
$hits = (int)$entry['hits'];
$meta = urldecode($entry['meta']);

$percent = 0;
if ($sum != 0) {
	$percent = round($hits*100/$sum);
}
else {
	//print_r($entries); // DEBUG
}
$hashid = woopra_friendly_hash($key);
?>
<tr class="<?php echo (($counter++%2==0)?"expanded_even_row":"expanded_row"); ?>">
	<td class="wrank"><?php echo $counter; ?></td>
	<td><span class="ellipsis"><a href="<?php echo $name; ?>" target="_blank"><?php echo woopra_render_name($key,$name,$meta); ?></a></span></td>
	<td width="100" class="center whighlight"><a href="#" onclick="return expandByDay('<?php echo $bydaykey; ?>', '<?php echo $hashid; ?>',<?php echo $id; ?>)"><?php echo $hits; ?></a></td>
	<td class="wbar"><?php echo woopra_bar($percent); ?></td>
</tr>
<tr id="wlc-<?php echo $hashid; ?>-<?php echo $id; ?>" style=" height: 120px; display: none;"><td class="wlinechart" id="linecharttd-<?php echo $hashid; ?>-<?php echo $id; ?>" colspan="4"></td></tr>
<tr id="refexp-<?php echo $hashid; ?>-<?php echo $id; ?>" style="display: none;"><td colspan="4"><div id="refexptd-<?php echo $hashid; ?>-<?php echo $id; ?>"></div></td></tr>
<?php
}
?>

</table>
<?php
}




function renderOverview($entries) {	
?>
<table class="woopra_table" width="100%" cellpadding="0" cellspacing="0">
<tr>
	<th>Day</th>
	<th class="center">Avg Time Spent</th>
	<th class="center">New Visitors</th>
	<th class="center">Visits</th>
	<th class="center">Pageviews</th>
	<th width="400">&nbsp;</th>
</tr>
<?php
$counter = 0;
$max = woopra_get_max($entries, 'pageviews');
foreach($entries as $entry) {

$pageviews = (int)$entry['pageviews'];
$percent = round($pageviews*100/$max);
$timespenttotal = (int)$entry['timespenttotal'];
$timesamples = (int)$entry['timespentsamples'];

$timespent = 0;
if ($timesamples != 0) {
	$timespent = round(($timespenttotal/1000)/$timesamples);
}
$timespentstring = woopra_seconds_to_string($timespent);

$newvisitors =(int)$entry['newvisitors'];
$visitors = (int)$entry['visitors'];
$newvisitorsstring = "-";
if ($newvisitors <= $visitors && $visitors != 0) { // HIDE OLD UNCONSISTANT DATA

	$newvisitorsstring = (int)($newvisitors*100/$visitors) . '%';
}

?>
<tr<?php echo (($counter++%2==0)?" class=\"even_row\"":""); ?>>
	<td class="whighlight"><?php echo woopra_date_to_string($entry['day']); ?></td>
	<td class="center"><?php echo $timespentstring; ?></td>
	<td class="center"><?php echo $newvisitorsstring; ?></td>
	<td class="center" class="center"><?php echo $entry['visits']; ?></td>
	<td class="center whighlight"><?php echo $entry['pageviews']; ?></td>
	<td class="wbar"><?php echo woopra_bar($percent); ?></td>
</tr>
<?php
}
?>

</table>
<?php
}

function woopra_bar($percent) {
	$barurl = get_option('siteurl') . '/wp-content/plugins/woopra/images/bar.png';
	$width = $percent . "%";
	if ($percent < 1) {
		$width = "1";
	}
	return '<img src="'.$barurl.'" width="'.$width.'" height="16" />';
}

function woopra_get_max($entries, $key) {
	$max = 0;
	foreach ($entries as $entry) {
		$val = (int)$entry[$key];
		if ($val > $max)
			$max = $val;
	}
	
	return $max;
}

function woopra_get_sum($entries, $key) {
	$sum = 0;
	foreach ($entries as $entry) {
		$val = (int)$entry[$key];
		$sum = $sum + $val;
	}
	
	return $sum;
}

function woopra_key_expansible($key) {
	if (woopra_contains($key,'&type=SEARCHENGINES') || woopra_contains($key,'&type=FEEDS') || woopra_contains($key,'&type=MAILS')) {
		return false;
	}
	return true;
}

function woopra_render_name($key, $name=NULL, $meta=NULL) {
	
	if ($name == NULL) {
		
		switch ($key) {
			case 'GET_COUNTRIES':
				return 'Country';
			case 'GET_VISITBOUNCES':
				return 'Pageviews per Visit';
			case 'GET_VISITDURATIONS':
				return 'Durations';
			case 'GET_BROWSERS':
				return 'Browser';
			case 'GET_PLATFORMS':
				return 'Platform';
			case 'GET_RESOLUTIONS':
				return 'Resolution';
			case 'GET_LANGUAGES':
				return 'Language';
			case 'GET_PAGEVIEWS':
				return 'Pages';
			case 'GET_PAGELANDINGS':
				return 'Landing Pages';
			case 'GET_PAGEEXITS':
				return 'Exit Pages';
			case 'GET_OUTGOINGLINKS':
				return "Outgoing Links";
			case 'GET_DOWNLOADS':
				return "Downloads";
			case 'GET_QUERIES':
				return "Search Queries";
			case 'GET_KEYWORDS':
				return "Keywords";
			default:
				return 'Name';
		}
	}
	else {
		switch ($key) {
			case 'GET_COUNTRIES':
				return woopra_country_flag($name) . " " . woopra_get_country_name($name);
			case 'GET_SPECIALVISITORS':
				$vars = Array();
				parse_str($meta, $vars);
				$avatar = 'http://static.woopra.com/images/avatar.png';
				if (isset($vars['avatar'])) {
					$avatar = $vars['avatar']; 
				}
				$toreturn .= '<img style="float: left; margin-right: 9px;" src="'.$avatar.'" width="32" height="32" /> ';
				$toreturn .= "<strong>$name</strong>";
				if (isset($vars['email'])) {
					$toreturn .= '<br/><small>(<a href="mailto:'.$vars['email'].'">'.$vars['email'].'</a>)</small>';
				}
				return $toreturn;
			case 'GET_VISITBOUNCES':
				$post_text = 'pageviews';
				if ($name == '1') {
					$post_text = 'pageview';
				}
				return $name . " " . $post_text;
			case 'GET_VISITDURATIONS':
				$name = str_replace('-', ' to ', $name);
				return $name . ' minutes';
			case 'GET_BROWSERS':
				return woopra_browser_icon($name) . "&nbsp;&nbsp;" . $name;
			case 'GET_PLATFORMS':
				return woopra_platform_icon($name) . "&nbsp;&nbsp;" . $name;
			case 'GET_PAGEVIEWS':
				return $meta . "<br/>" . "<small><a href=\"http://".woopra_host."$name\" target=\"_blank\">$name</a></small>";
			case 'GET_PAGELANDINGS':
				return $meta . "<br/>" . "<small><a href=\"http://".woopra_host."$name\" target=\"_blank\">$name</a></small>";
			case 'GET_PAGEEXITS':
				return $meta . "<br/>" . "<small><a href=\"http://".woopra_host."$name\" target=\"_blank\">$name</a></small>";
			case 'GET_OUTGOINGLINKS':
				return "<a href=\"$name\" target=\"_blank\">$name</a>";
			case 'GET_DOWNLOADS':
				return "<a href=\"$name\" target=\"_blank\">$name</a>";
			default:
				return $name;
		}
	}
}

?>
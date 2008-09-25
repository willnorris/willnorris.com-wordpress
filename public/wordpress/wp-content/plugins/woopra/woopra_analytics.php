<?php


if (isset($_GET['wkey'])) {
	
	include 'woopra_functions.php';
	include 'woopra_renderer.php';
	
	define('woopra_host',get_woopra_host());
	define('woopra_api_key', get_option('woopra_api_key'));

	$key = $_GET['wkey'];
	$key = str_replace("&amp;","&",$key);
	
	$date_from = $_GET['from'];
	$date_to = $_GET['to'];

	$entries = woopra_process_request($key,woopra_convert_date($date_from),woopra_convert_date($date_to),50,0);
	woopra_render_results($entries, $key);
	die();
}

function woopra_analytics_show_content() {


if (!woopra_check_analytics()) {
?>
	<p align="center">Please provide your API Key & Woopra Site ID in order to show your analytics!</p>
<?php
	return;
}
?>

<!-- Woopra Analytics Starts Here -->

<div id="woopra_analytics_global">
	<div id="woopra_analytics_box">
		
		<div class="woptions">
		<a href="#" onclick="return refreshCurrent();">Refresh</a>
		&nbsp;-&nbsp;
		<a id="daterangelink" href="#" onclick="return showDatePicker();" title="Click here to change the date range"><script type="text/javascript">document.write(getDateLinkText())</script></a>
			<div id="datepickerdiv">
				<table><tr>
				<td align="center">From: <input type="text" class="w8em format-y-m-d divider-dash highlight-days-12 no-fade" id="dp-from" name="dp-from" value="" maxlength="10" /></td>
				<td align="center">To: <input type="text" class="w8em format-y-m-d divider-dash highlight-days-12 no-fade" id="dp-to" name="dp-to" value="" maxlength="10" /></td>
				</tr>
				<tr>
				<td colspan="2" style="padding-top: 5px; text-align: right;">
				<input value="Cancel" name="approveit" class="button-secondary" type="submit" onclick="return closeDatePicker();">
				<input value="Apply Date Range" name="approveit" class="button-secondary" type="submit" onclick="return applyDatePicker();">
				</td>
				</tr>
				</table>
			</div>
		</div>

		<ul id="woopra-super-tabs">
		</ul>
		
		
	</div>
	
	<script type="text/javascript">
	
	woopra_website = '<?php echo get_option("siteurl"); ?>';
	
	addSuperTab('Visitors','visitors');
	addSuperTab('Systems','systems');
	addSuperTab('Pages','pages');
	addSuperTab('Referrers','referrers');
	addSuperTab('Searches','searches');
	
	addSubTab('Overview', 'overview', 'visitors', 'GET_GLOBALS');
	addSubTab('Countries', 'countries', 'visitors', 'GET_COUNTRIES');
	addSubTab('Tagged Visitors', 'taggedvisitors', 'visitors', 'GET_SPECIALVISITORS');
	addSubTab('Bounce Rate', 'bounces', 'visitors', 'GET_VISITBOUNCES');
	addSubTab('Visit Durations', 'durations', 'visitors', 'GET_VISITDURATIONS');
	
	addSubTab('Browsers', 'browsers', 'systems', 'GET_BROWSERS');
	addSubTab('Platforms', 'platforms', 'systems', 'GET_PLATFORMS');
	addSubTab('Screen Resolutions', 'resolutions', 'systems', 'GET_RESOLUTIONS');
	addSubTab('Languages', 'languages', 'systems', 'GET_LANGUAGES');
	
	addSubTab('Pageviews', 'pageviews', 'pages', 'GET_PAGEVIEWS');
	addSubTab('Landing Pages', 'landing', 'pages', 'GET_PAGELANDINGS');
	addSubTab('Exit Pages', 'exit', 'pages', 'GET_PAGEEXITS');
	addSubTab('Outgoing Links', 'outgoing', 'pages', 'GET_OUTGOINGLINKS');
	addSubTab('Downloads', 'downloads', 'pages', 'GET_DOWNLOADS');
	
	addSubTab('Referrer Types', 'reftypes', 'referrers', 'GET_REFERRERTYPES');
	addSubTab('Regular Referrers', 'refdefault', 'referrers', 'GET_REFERRERS&type=DEFAULT');
	addSubTab('Search Engines', 'refsearch', 'referrers', 'GET_REFERRERS&type=SEARCHENGINES');
	addSubTab('Feed Readers', 'reffeeds', 'referrers', 'GET_REFERRERS&type=FEEDS');
	addSubTab('Emails', 'refmails', 'referrers', 'GET_REFERRERS&type=MAILS');
	addSubTab('Social Bookmarks', 'refbookmarks', 'referrers', 'GET_REFERRERS&type=SOCIALBOOKMARKS');
	addSubTab('Social Networks', 'refnetworks', 'referrers', 'GET_REFERRERS&type=SOCIALNETWORKS');
	addSubTab('Media', 'refmedia', 'referrers', 'GET_REFERRERS&type=MEDIA');
	addSubTab('News', 'refnews', 'referrers', 'GET_REFERRERS&type=NEWS');
	
	addSubTab('Search Queries', 'queries', 'searches', 'GET_QUERIES');
	addSubTab('Keywords', 'keywords', 'searches', 'GET_KEYWORDS');
	
	setCurrentSuperTab('visitors');
	</script>
	
	<div id="woopra_footer">
		Powered by <a href="http://www.woopra.com/">Woopra Analytics</a>
	</div>
</div>
<!-- Woopra Analytics Ends Here -->

<?php
}

function woopra_check_analytics() {
	if (get_option('woopra_api_key') && get_option('woopra_website_id') && get_option('woopra_api_key') != "" && get_option('woopra_website_id') != "") {
		return true;
	}
	return false;
}

function woopra_process_request($key,$start_date, $end_date, $limit, $offset) {
	include 'woopra_xml.php';
	$woopraXML = new WoopraAPI();
	$woopraXML->hostname = woopra_host;
	$woopraXML->siteid = get_option('woopra_website_id');	
	$init = $woopraXML->Init();
	$entries = null;
	if ($init) {
		$woopraXML->setXML($key, $start_date, $end_date, $limit, $offset);
		if ($woopraXML->processData())	{
			$entries = $woopraXML->data;//['data']['entry'];
		} 
	}
	$woopraXML->clearData();
	
	//print_r($entries);
	
	return $entries;
}



?>
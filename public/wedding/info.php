<?php
	require_once 'lib/common.php';
	
	$bodyId = 'info';
	$bodyTitle = 'Wedding Info';
	pageHeader();
?>

	<h2>Wedding Info</h2>
	
	<div class="vevent">

		<p><abbr class="summary" title="Will and Elisabeth's Wedding">The <span class="category">wedding</span></abbr> 
will be held on <abbr class="dtstart" title="2008-05-02T18:30:00-08:00">Friday, 
May 2nd, 2008</abbr> at Falkner Winery in Temecula, CA.  The ceremony will 
begin at 6:30pm with reception to immediately <abbr class="dtend" title="2008-05-02T23:30:00-08:00">follow</abbr> on site.</p>

		<hr />
	
		<h3 id="directions_title">Directions</h3>

		<img id="directions-map" src="images/map.png" />
		<div id="directions">
			<ul>
				<li>Take Interstate 15 toward Temecula, CA (South from Los Angeles, North from San Diego)</li>

				<li>When you reach Temecula, exit on Rancho California Rd (exit 59) heading East</li>

				<li>Continue on Rancho California for approximately 5 miles and turn left (North) on Calle Contento</li>

				<li>Falkner Winery is on the right</li>
			</ul>
			<div class="vcard">
					<div class="org">Falkner Winery</div>
					<div class="street-address">40620 Calle Contento</div>
					<div>
						<span class="locality">Temecula</span>, 
						<abbr class="region" title="California">CA</abbr> 
						<span class="postal-code">92591</span>
					</div>
					<a href="http://www.falknerwinery.com/" class="url" target="_blank">www.falknerwinery.com</a>
			</div>
		</div>
		
		<p id="google-maps">Find Falkner Winery on <a href="http://xrl.us/FalknerWineryMap">Google Maps</a>.</p>
	
		<hr />

		<h3 id="registry_title">Registry</h3>

		<p>We are currently registered at <a href="http://xrl.us/kohlsregistry" 
target="_blank">Kohl's Department Store</a> and <a 
href="http://xrl.us/lntregistry" target="_blank">Linens N Things</a>.  You may 
find that those registries don't have all that many items on them.  This is 
because we are still a bit unsure as to where we will be living, and so don't 
know what all we will need.  As we have a better idea, we may try and add more 
items to the registries.  Otherwise, we would greatly appreciate Gift Cards to 
either of these stores or <a href="http://www.worldmarket.com/" 
target="_blank">World Market</a> (much to our disappointment, they don't have 
gift registries).</p>

	</div>
<?php

	pageFooter();
?>

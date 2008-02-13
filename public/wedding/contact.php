<?php
	require_once 'lib/common.php';
	
	$bodyId = 'contact';
	$bodyTitle = 'Contact Us';
	pageHeader();
?>

	<h2>Contact Us</h2>

	<p>Feel free to contact us.</p>

	<div class="vcard">
		<h3><a href="http://will.norris.name" class="url fn">Will Norris</a></h3>
		<div class="email">will@willnorris.com</div>
		<div class="tel">
			<span class="type">Cell</span> <span class="value">+1 901.484.9455</span>
		</div>
	</div>

	<div class="vcard">
		<h3>Elisabeth Kennedy</h3>
		<div class="email">jaderossdale@gmail.com</div>
		<div class="tel">
			<span class="type">Cell</span> <span class="value">+1 909.815.0908</span>
		</div>
		<div class="street-address">42375 Wildwood Ln</div>
		<div>
			<span class="locality">Aguanga</span>, 
			<abbr class="region" title="California">CA</abbr> 
			<span class="postal-code">92536</span>
		</div>
		
	</div>
	

<?php
	pageFooter();
?>

<?php

function pageHeader() {
	global $bodyId, $bodyTitle;
?>
<html>
	<head>
		<title>Will & Elisabeth<?php echo $bodyTitle ? " - $bodyTitle" : '' ?></title>
		<link type="text/css" rel="stylesheet" href="style/main.css" />

		<script src="/script/FancyZoom.js" type="text/javascript"></script>
		<script src="/script/FancyZoomHTML.js" type="text/javascript"></script>
	</head>

	<body <?php echo $bodyId ? 'id="'.$bodyId.'"' : '' ?> onload="setupZoom()">
		<div id="content">

			<div id="header"><a href="/wedding/" rel="home"><h1>Will & Elisabeth</h1></a></div>

			<ul id="navigation">
				<li><a id="nav_contact" href="contact">Contact Us</a></li>
				<li><a id="nav_photos" href="photos">Photos</a></li>
				<li><a id="nav_info" href="info">Wedding Info</a></li>
				<li><a id="nav_rsvp" href="rsvp">RSVP</a></li>
				<li><a id="nav_about" href="about">About Us</a></li>
			</ul>

			<div id="main">
<?php
}


function pageFooter() {
?>
			</div>
			<div id="footer"></div>
		</div>
	</body>
</html>
<?php
}


?>

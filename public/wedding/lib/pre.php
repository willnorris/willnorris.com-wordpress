<?php

function pageHeader() {
	global $bodyId, $bodyTitle;
?>
<html>
	<head>
		<title>Will & Elisabeth<?php echo $bodyTitle ? " - $bodyTitle" : '' ?>
		<link type="text/css" rel="stylesheet" href="style/main.css" />
	</head>

	<body <?php echo $bodyId ? 'id="'.$bodyId.'"' : '' ?>>
		<div id="content">

			<div id="header"><a href="/wedding/" rel="home"><h1>Will & Elisabeth</h1></a></div>

			<ul id="navigation">
				<li><a id="nav_contact" href="contact">Contact Us</a></li>
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

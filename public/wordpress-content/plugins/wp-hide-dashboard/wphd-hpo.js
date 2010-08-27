window.onload=function() {
	var profile = document.getElementById('your-profile');
	var tables = document.getElementById('your-profile').getElementsByTagName('table');
	var headers = document.getElementById('your-profile').getElementsByTagName('h3');

	if (profile) {
		if (tables && headers) {
			profile.removeChild(headers[0]); // Remove Personal Options header
			profile.removeChild(tables[0]); // Remove the visual editor option, admin color theme chart, and keyboard shortcuts option for comments
		}

		profile.style.display = "block";
	}
}

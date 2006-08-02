
function addToEditPage(box, title, contents) {
	var rightbar = document.getElementById(box);

	newBox = '<fieldset id="tagsdiv" class="dbx-box">';
	newBox += '<h3 class="dbx-handle">' + title + '</h3>';
	newBox += '<div class="dbx-content">' + contents + '</div>';
	newBox += '</fieldset>';

	rightbar.innerHTML +=newBox;
}

function addTag(tagname) {
	if (document.forms[0].tagset.value == "") {
		document.forms[0].tagset.value = tagname;
	} else {
		document.forms[0].tagset.value += ", " + tagname;
	}
}

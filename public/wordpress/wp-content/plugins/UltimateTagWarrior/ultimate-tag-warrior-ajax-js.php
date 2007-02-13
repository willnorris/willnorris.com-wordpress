<?php $ajaxurl = addslashes(strip_tags(urldecode($_GET['ajaxurl']))) ?>
function createRequestObject() {
	var ro;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
		ro = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		ro = new XMLHttpRequest();
	}
	return ro;
}

function sndReq(action, tag, post, format) {
	var http = createRequestObject();
	http.open('get', '<?php echo $ajaxurl ?>?action='+action+'&tag='+tag+'&post='+post+'&format='+format);
	http.onreadystatechange = function () {
		if(http.readyState == 4){
			var response = http.responseText;
			var update = new Array();
			if(response.indexOf('|' != -1)) {
				update = response.split('|');
				document.getElementById("tags-" + update[0]).innerHTML = update[1];
			}
		}
	};
	http.send(null);
}

function sndReqNoResp(action, tag, post) {
	var http = createRequestObject();

	http.open('get', '<?php echo $ajaxurl ?>?action='+action+'&tag='+tag+'&post='+post);
	http.send(null);
}

function sndReqGenResp(action, tag, post, format) {
	var http = createRequestObject();

	http.open('get', '<?php echo $ajaxurl ?>?action='+action+'&tag='+tag+'&post='+post+'&format='+format);
	http.onreadystatechange = function () {
		if(http.readyState == 4){
			var response = http.responseText;

			document.getElementById("ajaxResponse").innerHTML = response;
		}
	};

	http.send(null);
}

function askYahooForKeywords() {
	var http = createRequestObject();

	try {
		http.open('POST','<?php echo $ajaxurl ?>?action=requestKeywords&service=yahoo&content=' + document.getElementById("content").value);
		http.onreadystatechange = function () {
			if(http.readyState == 4){
				document.getElementById("yahooSuggestedTags").innerHTML = http.responseText;
			}
		};
		http.send(escape(document.getElementById('content').value));
	} catch (ex) {
		alert("Something done went wrong:" + ex);
	}
}
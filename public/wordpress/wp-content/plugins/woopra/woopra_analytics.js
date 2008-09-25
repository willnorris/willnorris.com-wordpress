
var woopra_website;
var currentSuperView = null;
var pageKeys = new Array();
var defaultSubTab = new Array();
var selectedSubTabs = new Array();
var pageObjects = new Array();

date = new Date();
var date_to = getDateText(date);
date.setDate(date.getDate()-30);
var date_from = getDateText(date);

function addSuperTab(name, id) {
	var woopraSuperTabs = document.getElementById('woopra-super-tabs');
	
	var newTab = document.createElement('li');
	var newTabLink = document.createElement('a');
	newTabLink.href = '#';
	newTabLink.id = 'super-tab-' + id;
	newTabLink.onclick = new Function('setSuperView(\'' + id + '\')');
	newTabLink.innerHTML = name;
	newTab.appendChild(newTabLink);
	
	var superView = document.createElement('div');
	superView.id = 'super-view-' + id;
	superView.className = 'woopra_analytics_inner';
	superView.style.display = 'none';
	superView.innerHTML = "<ul class=\"woopra_subtabs\" id=\"woopra-sub-tab-" + id + "\"></ul><div id=\"viewport-" + id + "\"></div>";
	document.getElementById('woopra_analytics_box').appendChild(superView);
	
	woopraSuperTabs.appendChild(newTab);
}

function setCurrentSuperTab(id) {
	if (id == currentSuperView)
		return;
		
	var tab = document.getElementById('super-tab-'+id);
	
	tab.className = 'current';
	
	var view = document.getElementById('super-view-'+id);
	view.style.display = 'block';
	
	if (currentSuperView != null) {
		document.getElementById('super-tab-' + currentSuperView).className = '';
		document.getElementById('super-view-' + currentSuperView).style.display = 'none';
	}
	
	currentSuperView = id;
	
	if (selectedSubTabs[id] == null) {
		setSubView(id,defaultSubTab[id]);
	}
}

function addSubTab(name, id, superid, key) {
	var subList = document.getElementById('woopra-sub-tab-' + superid);
	
	var tabli = document.createElement('li');
	var tab = document.createElement('a');
	tab.href = "#";
	tab.id = "woopra-subtab-" + id;
	tab.onclick = new Function('setSubView(\'' + superid + '\',\'' + id + '\')');
	tab.innerHTML = name;
	tabli.appendChild(tab);
	
	document.getElementById('woopra-sub-tab-' + superid).appendChild(tabli);
	
	pageKeys[superid + '-' + id] = key;
	
	if (defaultSubTab[superid] == null) {
		defaultSubTab[superid] = id;
	}
	
}

function setSubView(superid, id) {
	var currentSubTabId = selectedSubTabs[superid];
	if (currentSubTabId == id) {
		return false;
	}
	
	if (currentSubTabId != null) {
		var currentSubTab = document.getElementById('woopra-subtab-' + currentSubTabId);
		currentSubTab.className = '';
		pageObjects[superid + '-' + currentSubTabId].style.display = 'none';
	}
	selectedSubTabs[superid] = id;
	var subtab = document.getElementById('woopra-sub-tab-' + superid);
	document.getElementById('woopra-subtab-' + id).className = 'current';
	
	showWoopraAnalytics(superid, id);
	return false;
}

function setSuperView(id) {
	setCurrentSuperTab(id);
	return false;
}

function showWoopraAnalytics(superid, id) {
	
	var pageid = superid + '-' + id;
	
	var currentPageID = superid + '-' + selectedSubTabs[superid];
	var currentPage = pageObjects[currentPageID];
	if (currentPage != null) {
		currentPage.style.display = 'none';
	}
	
	if (pageObjects[pageid] != null) {
		pageObjects[pageid].style.display = 'block';
		return;
	}
	
	pageObjects[pageid] = document.createElement('div');
	pageObjects[pageid].id = pageid;
	var page = pageObjects[pageid];
	var viewport = document.getElementById('viewport-' + superid);
	viewport.appendChild(page);
	setPageLoading(pageid);
	
	requestData(superid + '-' + id, pageKeys[pageid]);
}

function setPageLoading(page) {
	document.getElementById(page).innerHTML = '<p align="center"><img src="' + woopra_website + '/wp-content/plugins/woopra/images/woopra-loader.gif"/><br/>Loading... Please wait!</p>';
}

function initDatePicker() {
	document.getElementById('dp-from').value = date_from;
	document.getElementById('dp-to').value = date_to;
}

function expandByDay(key, hashid, id) {
	var row = document.getElementById('wlc-' + hashid + '-' + id);
	if (row.style.display == 'table-row') {
		row.style.display = 'none';
	}
	else {
		row.style.display = 'table-row';
	}
	
	if (row.className == 'loaded') {
		return false;
	}
	row.className = 'loaded';
	
	var so = new SWFObject(woopra_website + "/wp-content/plugins/woopra/open-flash-chart.swf", hashid, "968", "110", "9", "#ff0000");
	so.addVariable("data", escape(woopra_website + "/wp-admin/index.php?page=woopra_analytics&wkey=" + escape(key + "&id=" + id) + '&from=' + date_from + '&to=' + date_to));
	so.addParam("allowScriptAccess", "sameDomain");
	so.addParam("wmode", "transparent");
	so.addParam("bgcolor", "#FFFFFF");

	so.write('linecharttd-' + hashid + '-' + id);
	return false;
}

function expandReferrer(key, hashid) {
	trref = document.getElementById('refexp-'+hashid);
	if (trref.style.display == 'none') {
		trref.style.display = 'table-row';
	}
	else {
		trref.style.display = 'none';
	}
	if (trref.className == 'loaded') { return false; }
	
	trref.className = 'loaded';
	
	tdref = document.getElementById('refexptd-' + hashid);
	setPageLoading('refexptd-' + hashid);
	requestData('refexptd-' + hashid, key);
	return false;
}

function showDatePicker() {
	initDatePicker();
	dp = document.getElementById("datepickerdiv");
	dp.style.display = 'block';
	return false;
}

function closeDatePicker() {
	dp = document.getElementById("datepickerdiv");
	dp.style.display = 'none';
	return false;
}

function applyDatePicker() {
	date_from = document.getElementById('dp-from').value;
	date_to = document.getElementById('dp-to').value;
	//pageObjects = new Array();
	refreshDateLinkText();
	refreshCurrent();
	closeDatePicker();
	return false;
}

function getDateText(date) {
	text = date.getFullYear() + '-';
	m = date.getMonth() + 1;
	if (m < 10) { text += '0'; }
	text += m + '-';
	d = date.getDate();
	if (d <10) { text += '0'; }
	text += d;
	return text;
}

function getDateLinkText() {
	return '<strong>From:</strong> ' + date_from + ' <strong>To:</strong> ' + date_to;
}

function refreshDateLinkText() {
	document.getElementById("daterangelink").innerHTML = getDateLinkText();
}

function refreshCurrent() {
	superid = currentSuperView;
	subid = selectedSubTabs[currentSuperView];
	pageObjects[superid + '-' + subid] == null;
	pageid = superid + '-' + subid;
	setPageLoading(pageid);
	requestData(pageid, pageKeys[pageid]);
	return false;
}

function requestData(pageid, key) {
    var xhr; 
    try { xhr = new XMLHttpRequest(); }                 
    catch(e) { 
        try { xhr = ActiveXObject('Msxml2.XMLHTTP'); }
        catch(e2) { xhr = new ActiveXObject(Microsoft.XMLHTTP); }
    }
    
    xhr.onreadystatechange = function() { 
        if (xhr.readyState  == 4) {
            if(xhr.status  == 200) {
                var resp = xhr.responseText;
                document.getElementById(pageid).innerHTML = resp;
            }
            else {
            	document.getElementById(pageid).innerHTML = 'An error occured, please try again later!';
            }
        }
    }; 
    xhr.open('GET', woopra_website + '/wp-admin/admin.php?page=woopra_analytics&wkey=' + escape(key) + '&from=' + date_from + '&to=' + date_to); 
    xhr.send(null);
}

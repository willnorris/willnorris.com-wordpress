<?php 
	require_once('../../../../wp-blog-header.php'); 
	$plugin_path = get_settings('siteurl') . '/wp-content/plugins/af-extended-live-archive/includes/af-ela.php';
	// get settings and construct default;
	$settings = get_option('af_ela_options');
	if (!$settings) {
		echo "document.write('<div id=\"af-ela\"><p class=\"alert\">Plugin is not initialized. Admin or blog owner, visit the ELA option panel in your admin section.</p></div>')";
		return;
	} else {
	    header("Cache-Control: public");
		header("Pragma: cache");

		$offset = 60*60*24*365;
		$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";
		$LmStr = "Last-Modified: ".$settings['last_modified']." GMT";
		header($ExpStr);
		header($LmStr);
		header('Content-Type: text/javascript; charset: UTF-8');

		// Getting headers sent by the client if possible.
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (isset($headers['If-Modified-Since']) && ($headers['If-Modified-Since']) == ($settings['last_modified']." GMT")) {
				header("HTTP/1.1 304 Not Modified");
				exit;
			}
		} else if (isset($HTTP_IF_MODIFIED_SINCE)) {
			if (($settings['last_modified']." GMT") == $HTTP_IF_MODIFIED_SINCE) {
				header("HTTP/1.1 304 Not Modified");
				exit;
			}
		}
	}
?>/*
// +----------------------------------------------------------------------+
// | Licenses and copyright acknowledgements are located at               |
// | http://www.sonsofskadi.net/wp-content/elalicenses.txt                |
// +----------------------------------------------------------------------+
*/
var af_elaLiveReq = false;
var af_elaLiveReqLast = "-";
var af_elaYear = 0;
var af_elaMonth = 0;
var af_elaCategory = -1;
var af_elaTag = -1;
var af_elaMenu = 0;
var af_elaIsIE = false;
var af_elaProcessURI = '<?php echo $plugin_path; ?>';
var af_elaResultID = '<?php echo $settings['id']; ?>';
var af_elaLoadingContent = '<?php echo $settings['loading_content']; ?>';
var af_elaIdleContent = '<?php echo $settings['idle_content']; ?>';
var af_elaPageOffset = '<?php echo $settings['paged_post_num']; ?>';
var af_elaCurrentOffset = 0;
var af_elaCurrentPage = 1;
var af_elaSemOffset = 0;


function af_elaGenerateIt(varElement, type) {
	if( varElement == null ) {
		return false;
	} else {
		var var_list = varElement.childNodes;
		for( var i = 0; i < var_list.length; i++ ) {
			if( var_list[i].nodeName == 'LI' ) {
				var_list[i].style.cursor = 'pointer';
				var tf = function(e) {
 					var af_elaID = af_elaEventElement(e).id;
 					eval("af_ela"+type+" = af_elaID.substring(af_elaID.lastIndexOf('-') + 1, af_elaID.length)");
 					eval("af_elaSelect"+type+"();");
				}			
				if( af_elaIsIE ) {
					var_list[i].attachEvent('onclick',tf);
				} else {
					var_list[i].addEventListener('click', tf, false);			
				}
			}
		}
		return true;
	}
}

function af_elaGenerateMenu() { var menuElement = document.getElementById(af_elaResultID+'-menu'); return af_elaGenerateIt(menuElement, "Menu"); }
function af_elaGenerateYear() { var yearElement = document.getElementById(af_elaResultID+'-year'); return af_elaGenerateIt(yearElement, "Year"); }
function af_elaGenerateMonth() { var monthElement = document.getElementById(af_elaResultID+'-month'); return af_elaGenerateIt(monthElement, "Month"); }
function af_elaGenerateCategory() { var categoryElement = document.getElementById(af_elaResultID+'-category'); return af_elaGenerateIt(categoryElement, "Category"); }
function af_elaGenerateTag() {
	var tagElement = document.getElementById(af_elaResultID+'-tag');
	if( tagElement == null ) {
		return false;
	} else {
		var tag_list = tagElement.childNodes;
		if( !af_elaIsIE ) {
			 return af_elaGenerateIt(tagElement, "Tag");
		} else {
			for( var i = 0; i < tag_list.length; i++ ) {
				if( tag_list[i].nodeName == 'LI' ) {
					tag_listIE = tag_list[i].childNodes;
					for( var j = 0; j < tag_listIE.length; j++ ) {
						if( tag_listIE[j].nodeName == 'FONT' ) {
							tag_listIE[j].style.cursor = 'pointer';
							var tf = function(e) {
		 						var af_elaID = af_elaEventElement(e).parentNode.id;
		 						af_elaTag = af_elaID.substring(af_elaID.lastIndexOf('-') + 1, af_elaID.length);
		 						af_elaSelectTag();
		 					}
		 					tag_listIE[j].attachEvent('onclick',tf);
						}
					}
				}
			}
			return true;
		}
	}
}

function af_elaGeneratePosts(type, Type) {
	var postElement = document.getElementById(af_elaResultID+type);
	document.getElementById(af_elaResultID+"-loading").innerHTML = af_elaIdleContent;
	if( postElement == null) {
		return false;
	} else {
		postElement.style.cursor = 'pointer';
		var tf = function(e) {
			var af_elaID = af_elaEventElement(e).id;
			if( af_elaIsIE ) {
				postElement.detachEvent('onclick',tf);
			} else {
				postElement.removeEventListener('click', tf, false);
			}
	 		eval("af_elaSelect"+Type+"Posts();");
		}		
		if( af_elaIsIE ) {
			postElement.attachEvent('onclick',tf);
		} else {
			postElement.addEventListener('click', tf, false);
		}
		return true;
	}
}

function af_elaGeneratePrevPosts() { af_elaGeneratePosts('-post-prev', 'Prev'); }
function af_elaGenerateNextPosts() { af_elaGeneratePosts('-post-next', 'Next'); }

function af_elaLiveReqProcessReqChange() {
	if (af_elaLiveReq.readyState != 4) {
		var resultElement = document.getElementById(af_elaResultTarget);
		if( resultElement == null ) return;	
		resultElement.innerHTML = af_elaLoadingContent; 
	} else if (af_elaLiveReq.readyState == 4) {
        var af_elaText = af_elaLiveReq.responseText;
		var af_elaResultTarget = af_aleRemoveSpaces(af_elaText.substring(0, af_elaText.indexOf('|')));
		af_elaText = af_elaText.substring(af_elaText.indexOf('|') + 1, af_elaText.length);
		
		var resultElement = document.getElementById(af_elaResultTarget);
		if( resultElement == null ) return;	
		resultElement.innerHTML = af_elaText;
		
		af_elaGenerateMenu();
		af_elaGenerateYear();
		af_elaGenerateMonth();
		af_elaGenerateCategory();
		af_elaGenerateTag();
		af_elaGenerateNextPosts();
		af_elaGeneratePrevPosts();
		// Fade Anything.
		if( typeof Fat != 'undefined' && /class="fade"/.test(af_elaText)) {
			Fat.fade_all();
		}
		af_elaSemOffset = 0;
	}
}


function af_elaLiveReqDoReq(query) {

	if (window.opera) {
		window.removeEventListener('load', af_elaLiveReqInit, true);
	}

	if (af_elaLiveReqLast != query) {
		if (af_elaLiveReq && af_elaLiveReq.readyState < 4) {
			af_elaLiveReq.abort();
		}
		
		if (window.XMLHttpRequest) {
			af_elaLiveReq = new XMLHttpRequest();
		// branch for IE/Windows ActiveX version
		} else if (window.ActiveXObject) {
			af_elaLiveReq = new ActiveXObject("Microsoft.XMLHTTP");
		}
		af_elaLiveReq.onreadystatechange = function() {af_elaLiveReqProcessReqChange();} ;
		af_elaLiveReq.open("GET", af_elaProcessURI + "?" + query, true);
		af_elaLiveReqLast = query;
		af_elaLiveReq.send(null);
		return true;
	} else {
		return false;
	}
}


function af_elaPageNumber(idleString) {
	var res = idleString.replace('%', af_elaCurrentPage);
	return res;
}

function af_elaLiveReqInit() {
	if (navigator.userAgent.indexOf("Safari") > 0) {
	} else if (navigator.product == "Gecko") {
	} else {
		af_elaIsIE = true;
	}
	af_elaLiveReqDoReq('');
}

function af_elaSelectIt(CGIVarString) {
	if(af_elaLiveReqDoReq(eval(CGIVarString))) {
		var loadingElement = document.getElementById(af_elaResultID+"-loading");
		if ( loadingElement != null) loadingElement.innerHTML = af_elaLoadingContent;
	}
	af_elaCurrentOffset = 0;
	af_elaSemOffset = 0;
}

function af_elaSelectYear() { af_elaSelectIt("'menu=' + af_elaMenu + '&year=' + af_elaYear"); }
function af_elaSelectMonth() { af_elaSelectIt("'menu=' + af_elaMenu + '&year=' + af_elaYear + '&month=' + af_elaMonth"); }
function af_elaSelectTag() { af_elaSelectIt("'menu=' + af_elaMenu + '&tag=' + af_elaTag"); }
function af_elaSelectCategory() { af_elaSelectIt("'menu=' + af_elaMenu + '&category=' + af_elaCategory"); }

function af_elaSelectMenu() {
	if (af_elaLiveReqDoReq('menu=' + af_elaMenu)) {
		var loadingElement = document.getElementById(af_elaResultID+"-loading");
		if ( loadingElement != null) loadingElement.innerHTML = af_elaLoadingContent;
	}
	af_elaSelectReset();
}

function af_elaSelectPosts(order) {
	var globalVars = af_elaCollectGlobal();
	var tempOffset = eval("eval(af_elaCurrentOffset)"+order+"eval(af_elaPageOffset);");
	if(!af_elaSemOffset) {
		af_elaCurrentOffset = tempOffset;
		eval("af_elaCurrentPage "+order+"= 1;");
	}
	af_elaSemOffset = 1;
	if (af_elaLiveReqDoReq('menu=' + af_elaMenu + '&paged_offset=' + tempOffset + globalVars)) {
		var loadingElement = document.getElementById(af_elaResultID+"-loading");
		if ( loadingElement != null) loadingElement.innerHTML = af_elaLoadingContent;
	}
}

function af_elaSelectNextPosts() { af_elaSelectPosts("+");}
function af_elaSelectPrevPosts() { af_elaSelectPosts("-");}

function af_elaSelectReset() {
	af_elaCurrentOffset = 0;
	af_elaCurrentPage =1;
	af_elaSemOffset = 0;
	af_elaYear = 0;
	af_elaMonth = 0;
	af_elaCategory = -1;
	af_elaTag = -1;
	af_elaPosts = 0;
}

function af_elaCollectGlobal() {
	var year= '&year=0';
	var month= '&month=0';
	var tag= '&tag=-1';
	var category= '&category=-1';
	if (af_elaYear) var year = '&year=' + af_elaYear;
	if (af_elaMonth) var month = '&month=' + af_elaMonth;
	if (af_elaTag) var tag = '&tag=' + af_elaTag;
	if (af_elaCategory) var category = '&category=' + af_elaCategory;
	return year + month + tag + category;
}

function af_elaEventElement(e) {
	if( af_elaIsIE ) {
		return e.srcElement;
	} else {
		return e.currentTarget;
	}
}

function af_aleRemoveSpaces(TextToTrim) {
  var buffer = "";
  var TextToTrimLen = TextToTrim.length;
  var TextToTrimLenMinusOne = TextToTrim.length - 1;
  for (index = 0; index < TextToTrimLen; index++) {
    if (TextToTrim.charAt(index) != ' ') {
      buffer += TextToTrim.charAt(index);
    } else {
      if (buffer.length > 0) {
        if (TextToTrim.charAt(index+1) != ' ' && index != TextToTrimLenMinusOne) {
          buffer += TextToTrim.charAt(index);
        }
      }
    }
  }
  return buffer;
}

function af_elaAddEvent(obj, evType, fn) {
	if (obj.addEventListener) {
		obj.addEventListener(evType, fn, true);
		return true;
	} else if (obj.attachEvent) {
		var r = obj.attachEvent("on"+evType, fn); 
		return r;
	} else {
		return false;
	}
}

af_elaAddEvent(window, 'load', af_elaLiveReqInit);

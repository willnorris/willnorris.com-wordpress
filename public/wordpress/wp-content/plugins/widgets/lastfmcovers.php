<?php
/*
Plugin Name: Last Fm Covers widget
Description: Adds a sidebar widget that shows the covers of favorite cds (from last.fm rss feed).
Author: Dog Of Dirk
Version: 1.3
Author URI: http://dirkie.nu/widgets
*/

// version 1.0: initial release
// version 1.1: fixed warning when last.fm rss-feed is not available
// version 1.2: * added some checks to avoid errors
//              * when there are no covers to display, a message is displayed.
// v 1.2.1      * removed the max of 24 cd covers.
// v 1.3        * added cUrl if fopen is not available

function widget_lastfmcovers_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

  # output for sidebar
	function widget_lastfmcovers($args) {

		extract($args);

		$options      = get_option('widget_lastfmcovers');
		$title        = $options['title'];
		$username     = $options['username'];
		$count        = $options['count'];
		$imgwidth     = $options['imgwidth'];
		$countdesc    = $options['countdesc'];

		echo $before_widget . $before_title . $title . $after_title;

    echo runForCover($username, $imgwidth, $count, $countdesc);

		echo $after_widget;
	}

  function runForCover($username, $imgwidth, $maxcount, $countdesc) {
    $_result = '';
    
    $_xml2array = new RSSParserLastFm('http://ws.audioscrobbler.com/1.0/user/' . $username . '/topalbums.xml');
    $cds = $_xml2array->struct;
    $_output = array();
    if (array_key_exists('children', $cds)) {
      $cds = $cds['children'][0];
      $_no = 0;
      if (array_key_exists('children', $cds)) {
        foreach ($cds['children'] as $_c) {
        	if (array_key_exists('children', $_c)) {
      	    foreach($_c['children'] as $_cd) {
              switch($_cd['name']) {
          	    case "ARTIST":
          	      $_output[$_no]['artist'] = $_cd['value'];
          	      break;
          	    case "NAME":
          	      $_output[$_no]['name'] = $_cd['value'];
          	      break;
          	    case "PLAYCOUNT":
          	      $_output[$_no]['playcount'] = $_cd['value'];
          	      break;
          	    case "URL":
          	      $_output[$_no]['url'] = $_cd['value'];
          	      break;
          	    case "IMAGE":
          	      foreach($_cd['children'] as $_cdimgs) {
          	  	    if ("LARGE" == $_cdimgs['name']) {
          	  		    $_output[$_no]['image'] = $_cdimgs['value'];
                    }
                  }
          	      break;
              }
            }
          }
          $_no++;
        }
      }
    }
    
    $_getoond = 0;
    foreach($_output as $_cd) {
    	if ($_getoond < $maxcount) {
    	  if (array_key_exists("image", $_cd)) {
    		  if ((false === strpos($_cd['image'], "no_album")) && (false === strpos($_cd['image'], "noalbum"))) {
			  if (function_exists('url_cache')) $_cd['image'] = url_cache($_cd['image']);
    	      $_alttitle = $_cd['name'] . "&nbsp;&raquo;&nbsp;" . $_cd['artist'] . "&nbsp;&raquo;&nbsp;" . str_replace('%', $_cd['playcount'], $countdesc);
    	      $_imgstyle = ($imgwidth > 0) ? ' style="width: ' . $imgwidth . 'px; height: ' . $imgwidth . 'px; padding: 8px 8px 0px 0px;"' : ' class="lastfmcover"';
    	      $_result .= '<a href="' . $_cd['url'] . '"><img' . $_imgstyle . ' src="' . $_cd['image'] . '" alt="' . $_alttitle . '" title="' . $_alttitle . '" /></a>';
            $_getoond++;
          }
        }
    	}
    }

    return ('' == $_result) ? '<i>No cd covers to display.</i>' : $_result;
  }

	function widget_lastfmcovers_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_lastfmcovers');
		if ( !is_array($options) ) {
			$options = array('title'=>'favorite cds', 'username'=>'', 'count' => '6', 'imgwidth' => '70', 'countdesc' => '% times.');
    }

		if ( $_POST['lastfmcovers-submit'] ) {
			$options['title']       = strip_tags(stripslashes($_POST['lastfmcovers-title']));
			$options['username']    = strip_tags(stripslashes($_POST['lastfmcovers-username']));
			$options['imgwidth']    = intval($_POST['lastfmcovers-imgwidth']);
			if ($options['imgwidth'] < 10) {
				$options['imgwidth'] = 0;
			}
			$options['count']       = intval($_POST['lastfmcovers-count']);
			// if (($options['count'] < 1) || ($options['count'] > 24)) {
      if ($options['count'] < 1) {
        $options['count'] = 6;
      }
			$options['countdesc'] = strip_tags(stripslashes($_POST['lastfmcovers-countdesc']));

			update_option('widget_lastfmcovers', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title       = htmlspecialchars($options['title'], ENT_QUOTES);
		$username    = htmlspecialchars($options['username'], ENT_QUOTES);
		$imgwidth    = htmlspecialchars($options['imgwidth'], ENT_QUOTES);
		$count       = htmlspecialchars($options['count'], ENT_QUOTES);
		$countdesc   = htmlspecialchars($options['countdesc'], ENT_QUOTES);
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="lastfmcovers-title">Title: <input style="width: 200px;" id="lastfmcovers-title" name="lastfmcovers-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="lastfmcovers-username">Username: <input style="width: 200px;" id="lastfmcovers-username" name="lastfmcovers-username" type="text" value="'.$username.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="lastfmcovers-count">Max displayed: <input style="width: 200px;" id="lastfmcovers-count" name="lastfmcovers-count" type="text" value="'.$count.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="lastfmcovers-countdesc">Description for count: <input style="width: 200px;" id="lastfmcovers-countdesc" name="lastfmcovers-countdesc" type="text" value="'.$countdesc.'" /></label></p>';
		echo '<p style="text-align:right;">(% will be replaced with the number of times you have listened to tracks from the album)</p>';
		echo '<p style="text-align:right;"><label for="lastfmcovers-imgwidth">Image size: <input style="width: 200px;" id="lastfmcovers-imgwidth" name="lastfmcovers-imgwidth" type="text" value="'.$imgwidth.'" /></label></p>';
		echo '<p style="text-align:right;">(You can set this to zero and use img.lastfmcover in your stylesheet)</p>';
		echo '<input type="hidden" id="lastfmcovers-submit" name="lastfmcovers-submit" value="1" />';
	}
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('Last.fm covers', 'widget_lastfmcovers');

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 300x100 pixel form.
	register_widget_control('Last.fm covers', 'widget_lastfmcovers_control', 375, 300);
}

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_lastfmcovers_init');

## RSS parser found at
## http://nl3.php.net/manual/en/function.xml-parse-into-struct.php

## changed name to avoid conflicts (even-chuhally)
class RSSParserLastFm {
 
  var $struct = array();  // holds final structure
  var $curptr;  // current branch on $struct
  var $parents = array();  // parent branches of current branch
 
  function RSSParserLastFm($url) {
  if (function_exists('url_cache')) $url = url_cache($url);
   $this->curptr =& $this->struct;  // set ref to base
   $xmlparser = xml_parser_create();
   xml_set_object($xmlparser, $this);
   xml_set_element_handler($xmlparser, 'tag_open', 'tag_close');
   xml_set_character_data_handler($xmlparser, 'cdata');
   $fp = @fopen($url, 'r');

   if ($fp) {
     while ($data = fread($fp, 4096)) {
       xml_parse($xmlparser, $data, feof($fp));
     }
     fclose($fp);
   } else {
   	 // could be the username is invalid or fopen is disabled (e.g. Dreamhost)
   	 // so let's test if cUrl is available
   	 if (function_exists('curl_exec')) {
   	   $ch = curl_init();
       $timeout = 5; // set to zero for no timeout
       curl_setopt ($ch, CURLOPT_URL, $url);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
       $file_contents = curl_exec($ch);
       curl_close($ch);
       xml_parse($xmlparser, $file_contents);
     }
   }
   xml_parser_free($xmlparser);
  }
 
  function tag_open($parser, $tag, $attr) {
   $i = (array_key_exists('children', $this->curptr)) ? count($this->curptr['children']) : 0;
   $j = count($this->parents);
   $this->curptr['children'][$i]=array();  // add new child element
   $this->parents[$j] =& $this->curptr;  // store current position as parent
   $this->curptr =& $this->curptr['children'][$i];  // submerge to newly created child element
   $this->curptr['name'] = $tag;
   if (count($attr)>0) $this->curptr['attr'] = $attr;
  }
 
  function tag_close($parser, $tag) {
   $i = count($this->parents);
   if ($i>0) $this->curptr =& $this->parents[$i-1];  // return to parent element
   unset($this->parents[$i-1]);  // clear from list of parents
  }
 
  function cdata($parser, $data) {
   $data = trim($data);
   if (!array_key_exists('value', $this->curptr)) {
   	 $this->curptr['value'] = '';
   }
   if (!empty($data)) {
     $this->curptr['value'] .= $data;
   }
  }
}

?>

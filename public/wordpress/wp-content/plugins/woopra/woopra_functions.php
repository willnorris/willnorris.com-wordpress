<?php

function sort_analytics_response($entries) {
	usort($entries,'compare_analytics_entries');
}

function compare_analytics_entries($entry1, $entry2) {
	$sort_by = (isset($entry1['day'])?'day':'hits');
	
	$v1 = (int)$entry1[$sort_by];
	$v2 = (int)$entry2[$sort_by];
		
	if ($v1 == $v2)
		return 0;
	return ($v1 > $v2)?1:-1;	
}

function woopra_date_to_string($date) {
	$date = (int)$date;
	
	$year = 2006 + (int)($date/100000);
	$day_of_year = $date%100000;
	$to_return = date('F j, Y', mktime(0,0,0,1,$day_of_year,$year)); 
	return $to_return;
}

function woopra_convert_date($date) {
	$values = split('-', $date);
	$y = (int)$values[0];
	$day_of_year = date('z', mktime(0, 0, 0, (int)$values[1], (int)$values[2] , (int)$values[0]));
	$wdate = (100000 * ($y-2006)) + $day_of_year + 1;
	return $wdate;
}

function woopra_line_chart_date($date) {
	$date = (int)$date;
	
	$year = 2006 + (int)($date/100000);
	$day_of_year = $date%100000;
	$to_return = date('F jS', mktime(0,0,0,0,$day_of_year,$year)); 
	return $to_return;
}

function get_woopra_host() {
	$site = get_option('siteurl');
	$url = preg_replace("/^http:\/\//", "",$site);
	$url_tokens = explode("/", $url);
	$url = $url_tokens[0];
	return $url;
}

function woopra_encode($string) {
	return str_replace(',', '%2C', urlencode($string));
}

function woopra_seconds_to_string($seconds) {
	$min = floor($seconds/60);
	$sec = $seconds%60; 
	
	return $min . "m " . $sec . "s";
}

function woopra_rounded_max($max) {
	$values = array(10,20,30,40,50,60,70,80,90,100,120,150,200,250,300,400,500,600,700,800,900,1000,1200,1500,2000,2500,5000,10000,20000,50000,100000,200000,500000,1000000,2000000,5000000,10000000,50000000);
	$result = 10;
	foreach ($values as $value) {
		if ($value > $max) {
			return $value;
		}
	}
	return $max;
}

function woopra_friendly_hash($value) {
	return substr(md5($value),0,4);
}

function woopra_contains( $str, $sub ) {
	return strpos($str, $sub) !== false;
}

function woopra_country_flag($country) {
	return "<img src=\"http://static.woopra.com/images/flags/$country.png\" />";
}

function woopra_browser_icon($browser) {
	$browser = strtolower($browser);
	
	
    if (stripos($browser, "firefox") !== false) {
        return woopra_get_image("browsers/firefox");
    }
    if (stripos($browser, "explorer 7") !== false) {
        return woopra_get_image("browsers/ie7");
    }
    if (stripos($browser, "explorer 8") !== false) {
        return woopra_get_image("browsers/ie7");
    }
    if (stripos($browser, "explorer") !== false) {
        return woopra_get_image("browsers/ie");
    }
    if (stripos($browser, "safari") !== false) {
        return woopra_get_image("browsers/safari");
    }
    if (stripos($browser, "opera") !== false) {
        return woopra_get_image("browsers/opera");
    }
    if (stripos($browser, "mozilla") !== false) {
        return woopra_get_image("browsers/mozilla");
    }
    if (stripos($browser, "netscape") !== false) {
        return woopra_get_image("browsers/netscape");
    }
    if (stripos($browser, "konqueror") !== false) {
        return woopra_get_image("browsers/konqueror");
    }
    if (stripos($browser, "unknown") !== false || stripos($browser, "other") !== false) {
        return woopra_get_image("browsers/unknown");
    }
    return "";
}

function woopra_platform_icon($platform) {
	$platform = strtolower($platform);
	
	
    if (stripos($platform, "windows") !== false) {
        return woopra_get_image("os/windows");
    }
    if (stripos($platform, "mac") !== false) {
        return woopra_get_image("os/mac");
    }
    if (stripos($platform, "apple") !== false) {
        return woopra_get_image("os/mac");
    }
    if (stripos($platform, "ubuntu") !== false) {
        return woopra_get_image("os/ubuntu");
    }
    if (stripos($platform, "redhat") !== false) {
        return woopra_get_image("os/redhat");
    }
    if (stripos($platform, "suse") !== false) {
        return woopra_get_image("os/suse");
    }
    if (stripos($platform, "fedora") !== false) {
        return woopra_get_image("os/fedora");
    }
    if (stripos($platform, "debian") !== false) {
        return woopra_get_image("os/debian");
    }
    if (stripos($platform, "linux") !== false) {
        return woopra_get_image("os/linux");
    }
    if (stripos($platform, "playstation") !== false) {
        return woopra_get_image("os/playstation");
    }
    if (stripos($platform, "unknown") !== false || stripos($platform, "other") !== false) {
        return woopra_get_image("browsers/unknown");
    }
    return "";
}

function woopra_get_image($name) {
	return "<img src=\"http://static.woopra.com/images/$name.png\" />";
}

?>
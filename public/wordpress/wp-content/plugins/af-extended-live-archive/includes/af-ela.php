<?php
/*
// +----------------------------------------------------------------------+
// | Licenses and copyright acknowledgements are located at               |
// | http://www.sonsofskadi.net/wp-content/elalicenses.txt                |
// +----------------------------------------------------------------------+
*/
require('../../../../wp-blog-header.php');

function af_ela_truncate_title($title) {
	global $settings;
	if( $settings['truncate_title_length'] > 0 ) {
		if( strlen($title) > $settings['truncate_title_length'] ) {
			$title = substr($title, 0, $settings['truncate_title_length']);
			if( $settings['truncate_title_at_space'] == 1 ) {
				$pos = strrpos($title, ' ');
				if( $pos !== false ) {
						$title = substr($title, 0, $pos);
				}
			}
			$title .= $settings['truncate_title_text'];
		}
	}
	return $title;
}

function af_ela_truncate_cat_title($title) {
	global $settings;
	if( $settings['truncate_cat_length'] > 0 ) {
		if( strlen($title) > $settings['truncate_cat_length'] ) {
			$title = substr($title, 0, $settings['truncate_cat_length']);
			if( $settings['truncate_title_at_space'] == 1 ) {
				$pos = strrpos($title, ' ');
				if( $pos !== false ) {
						$title = substr($title, 0, $pos);
				}
			}
			$title .= $settings['truncate_title_text'];
		}
	}
	return $title;
}

function af_ela_read_years()  {
	global $year, $years, $path, $settings; 	
	$year_contents = @file_get_contents($path . 'years.dat');
	if( $year_contents === false ) $year_contents = '';
	
	$years = unserialize($year_contents);
	if( $years === false ) {
		echo "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file years.dat</p>";
		return FALSE;
	}
	
	if( $settings['newest_first'] == 0 ) {
		$years = array_reverse($years, true);
	}
	
	if( !array_key_exists($year, $years) ) {
		$temp = array_keys($years);
		$year = $temp[0];
	}
	return TRUE;
}
	
function af_ela_read_months()  {
	global $month, $months, $year, $years, $path, $settings; 	
	$month_contents = @file_get_contents($path . $year . '.dat');
	if( $month_contents === false ) $month_contents = '';
	
	$months = unserialize($month_contents);
	if( $months === false ) {
		echo "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file '$year.dat'</p>";
		return FALSE;;
	}

	if( $settings['newest_first'] == 0 ) {
		$months = array_reverse($months, true);
	}
	
	if( !array_key_exists($month, $months) ) {
		$temp = array_keys($months);
		$month = $temp[0];
	}
	return TRUE;
}

function af_ela_read_categories() {
	global $category, $categories, $path, $settings;
	$category_contents = @file_get_contents($path . 'categories.dat');
	if( $category_contents === false ) $category_contents = '';
	
	$categories = unserialize($category_contents);
	if( $categories === false ) {
		echo "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file categories.dat</p>";
		return FALSE;
	}
	return TRUE;
}

function af_ela_read_tags() {
	global $tag, $tags, $path, $settings;
	$tag_contents = @file_get_contents($path . 'tags.dat');
	if( $tag_contents === false ) $tag_contents = '';
	
	$tags = unserialize($tag_contents);
	if( $tags === false ) {
		echo "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file tags.dat</p>";
		return FALSE;
	}
	return TRUE;
}

function af_ela_read_posts() {
	global $month, $months, $year, $years, $category, $categories, $tag, $tags, $posts, $path, $settings, $menu_table, $menu; 
	switch($menu_table[$menu]) {
	case 'chrono':
		$post_contents = @file_get_contents($path . $year . '-' . $month . '.dat');
		$message = "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file '$year-$month.dat'</p>";
		break;
	case 'cats':
		if( $category == -1) {
			$keys = array_keys($categories);
			$i=0;
			while (!$categories[$keys[$i]][3]) {
				 $i++;
			}
			$category = $categories[$keys[$i]][0];
		}
		$post_contents = @file_get_contents($path . 'cat-' . $category. '.dat');
		
		$message = "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file 'cat-$category.dat'</p>";
		break;
	case 'tags':
		if( $tag == -1) { 
			$keys = array_keys($tags);
			$tag = $tags[$keys[0]][0];
		}
		$post_contents = @file_get_contents($path . 'tag-' . $tag . '.dat');
		
		$message =  "${settings['id']}|<p class='${settings['error_class']}'>Could not open cache file 'tag-$tag.dat'</p>";
	default:
		break;
	}
	if( $post_contents === false ) {
		$post_contents = '';
		echo $message;
		return FALSE;
	}
	$posts = unserialize($post_contents);
	if( $settings['newest_first'] == 0 ) {
		$posts = array_reverse($posts, true);
	}
	return TRUE;
}

function af_ela_generate_years() {
	global $year, $years, $settings, $fade;
	$year_list = '';
	foreach( $years as $y => $p ) {
		$current = '';
		$current_text = '';
		if( $y == $year ) {
			$current = ' class="'.$settings['selected_class'].'"';
			$current_text = $settings['selected_text'] == '' ? '' : ' ' . $settings['selected_text'];
		}
		
		if( $settings['num_entries'] == 1 ) {
			$num = ' ' . str_replace('%', $p, $settings['number_text']);
			}
			
			$year_list .= <<<END_TEXT
<li id="${settings['id']}-year-$y"$current>$y$num$current_text</li>

END_TEXT;
		}
		$year_list = <<<END_LIST
<ul id="${settings['id']}-year"${fade['year']}>
$year_list</ul>
END_LIST;

	return $year_list;
}

function af_ela_generate_months() {
	global $month, $months, $month_names, $settings, $paged_post, $fade;
	$month_list = '';
	foreach( $months as $m => $p ) {
		$current = '';
		$current_text = '';
		if( $m == $month ) {
			$paged_post = $p;
			$current = ' class="' . $settings['selected_class'] . '"';
			$current_text = $settings['selected_text'] == '' ? '' : ' ' . $settings['selected_text'];
		}
		
		if( $settings['num_entries'] == 1 ) {
			$num = ' ' . str_replace('%', $p, $settings['number_text']);
			}
			
			$n = $month_names[$m];
			$month_list .= <<<END_TEXT
<li id="${settings['id']}-month-$m"$current>$n$num$current_text</li>

END_TEXT;
		}
		$month_list = <<<END_LIST
<ul id="${settings['id']}-month"${fade['month']}>
$month_list</ul>
END_LIST;

	return $month_list;
}

function af_ela_generate_categories() {	
	global $category, $categories, $settings, $paged_post, $fade;
	$category_list = '';
	foreach( $categories as $c => $p ) {
		if ($p[6]) {
			if (!isset($p[3])) {
				$current = ' class="empty"';
			} else {
				$current = '';
			}
				
			if( $p[0] == $category ) {
				$paged_post = $p[3];
				$current = ' class="'.$settings['selected_class'].'"';
				$current_text = $settings['selected_text'] == '' ? '' : ' ' . $settings['selected_text'];
			} else {
				$current_text = '';
			}
			
			if( $settings['num_entries'] == 1 ) {
				$num = ' ' . str_replace('%', $p[3], $settings['number_text']);
			}
			
			// 	truncate titles
			$title = af_ela_truncate_cat_title($p[1]);
	
			// Add stuff if working on a child
			$before_children ='';
			$after_children  ='';
			if ($p[5] != 0) {
				for ($i = 1; $i <intval($p[5]); $i++) {
					$before_children .=$settings['before_child'];
					$after_children  .=$settings['after_child'];
					}
				}
				$category_list .= <<<END_TEXT
<li id="${settings['id']}-category-$p[0]"$current>$before_children$title$num$current_text$after_children</li>

END_TEXT;
			}
		}
		$category_list = <<<END_LIST
<ul id="${settings['id']}-category"${fade['category']}>
$category_list</ul>
END_LIST;

	return $category_list;
}

function af_ela_generate_tags() {
	global $tag, $tags, $settings, $paged_post, $fade;
	$tag_list = '';
	$tagged_posts = $tags[0][0];
	$posted_tags = $tags[0][1];
	foreach( $tags as $t => $p ) {
		if ($p[2]) {
			if( $p[0] == $tag ) {
				$paged_post = $p[2];
				$current = ' class="'.$settings['selected_class'].'"';
				$current_text = $settings['selected_text'] == '' ? '' : ' ' . $settings['selected_text'];
			} else {
				$current = '';
				$current_text = '';
			}
			if( $settings['num_entries_tagged'] == 1 ) {
				$num = ' ' . str_replace('%', $p[2], $settings['number_text_tagged']);
			}
			$tag_weight = $p[2] / $posted_tags * 100;
			$utwClass = new UltimateTagWarriorCore;
			$tag_weightcolor = $utwClass->GetColorForWeight($tag_weight);
			$tag_weightfontsize = $utwClass->GetFontSizeForWeight($tag_weight);
			$tag_display = str_replace('_',' ', $p[1]);
			$tag_display = str_replace('-',' ',$tag_display);
			$tag_display = str_replace('+',' ',$tag_display);
			$tag_display = strtolower($tag_display);
			
			$tag_list .= <<<END_TEXT
<li id="${settings['id']}-tag-$p[0]"$current><font style="font-size: $tag_weightfontsize !important; color: $tag_weightcolor !important">$tag_display$num$current_text</font></li> 

END_TEXT;

			}
		}
		$tag_list = <<<END_LIST
<ul id="${settings['id']}-tag"${fade['tag']}>
$tag_list</ul>
END_LIST;
	
	return $tag_list;
}

function af_ela_generate_posts() {
	global $posts, $post, $settings, $menu_table, $menu, $category, $paged_post, $paged_offset, $year, $month, $fade; 
	$post_list = '';
	
	if ($settings['paged_posts']) { 
		if ($paged_post!=0 && $paged_post > $settings['paged_post_num'] ) {
			$offset = ($paged_offset==-1) ? 0 : $paged_offset;
			if ($offset==0) {
				$prev_page = "<div id='" . $settings['id'] . "-post-prev-off'>".$settings['paged_post_prev']."</div >";
			} else {
				$prev_page = "<div id='" . $settings['id'] . "-post-prev'>".$settings['paged_post_prev']."</div >";
			}
			if (($offset + $settings['paged_post_num']) >= $paged_post) {
				$next_page = "<div id='" . $settings['id'] . "-post-next-off'>".$settings['paged_post_next']."</div >";
			} else {
				$next_page = "<div id='" . $settings['id'] . "-post-next'>".$settings['paged_post_next']."</div >";
			}
		} else {
				$next_page = "<div id='" . $settings['id'] . "-post-next-off'>".$settings['paged_post_next']."</div>";
				$prev_page = "<div id='" . $settings['id'] . "-post-prev-off'>".$settings['paged_post_prev']."</div>";
		}
	} else {
		$prev_page = "";
		$next_page = "";
	}
	$processed_posts = 0;
	foreach( $posts as $d => $p ) {
		$processed_posts++;
		if ($paged_offset!=-1 && $paged_offset >= $processed_posts) {
			continue;
		}
		if($settings['paged_posts'] && ($processed_posts - $offset) > $settings['paged_post_num']) {
			break;
		}
		if( $settings['num_comments'] == 1 ) {
			if( $p[4] == 'closed' ) {
				$cmt_text = ' ' . str_replace('%', $p[3], $settings['closed_comment_text']);
			} else {
				$cmt_text = ' ' . str_replace('%', $p[3], $settings['comment_text']);
			}
		}

		$title = af_ela_truncate_title($p[1]);

		switch($menu_table[$menu]) {
		case 'cats':
			$post_in_menu = "-cats";
			break;
		case 'tags':
			$post_in_menu = "-tags";
			break;
		case 'chrono':
			if( $settings['day_format'] == '' ) {
				$day = '';
			} else {
				$day = date($settings['day_format'], strtotime("$year-$month-$p[0]")) . ' ';
			}
			$post_in_menu = "-chrono";
		default:
			break;	
		}
		
		$post_list .= <<<END_TEXT
<li id='${settings['id']}-post-${p[0]}'>$day<a href='${p[2]}'>$title</a>$cmt_text</li>

END_TEXT;
	}
	if ($paged_offset == -1) {
	$post_list = <<<END_LIST
<ul id="${settings['id']}-post$post_in_menu"${fade['post']}>$prev_page$post_list$next_page</ul>

END_LIST;

	} else {
		$post_list = $prev_page.$post_list.$next_page;
	}
	return $post_list;
}

function af_ela_get_requests(&$menu, &$year, &$month, &$category, &$tag, &$paged_offset, &$fade) {
	$menu = isset($_REQUEST['menu']) ? $_REQUEST['menu'] : 0;
	$year = isset($_REQUEST['year']) ? $_REQUEST['year'] : 0;
	$month = isset($_REQUEST['month']) ? $_REQUEST['month'] : 0;
	$category = isset($_REQUEST['category']) ? $_REQUEST['category'] : -1;
	$tag = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : -1;
	$paged_offset = isset($_REQUEST['paged_offset']) ? $_REQUEST['paged_offset'] : -1;
	$fade = array('year' => ' ','month' => ' ','post' => ' ','category' => ' ','tag' => ' ');
}

function af_ela_get_tidy_settings(&$paged_post, &$path, &$settings,&$menu_headers,&$menu_table,&$fade,&$year, &$month, &$category, &$tag) {
    global $af_ela_cache_root;
	// number of post in current selection for pages
	$paged_post = 0;

	// the paths for the cache files and settings
	$path = $af_ela_cache_root; 
	if (!is_dir($path)) $path = get_settings('siteurl') . '/wp-content/plugins/af-extended-live-archive/cache/';


	// get settings and construct default;
	$settings = get_option('af_ela_options');

	$settings['paged_post_prev'] = urldecode($settings['paged_post_prev']);
	$settings['paged_post_next'] = urldecode($settings['paged_post_next']);
	$settings['loading_content'] = urldecode($settings['loading_content']);
	$settings['idle_content'] = urldecode($settings['idle_content']);
	$settings['selected_text'] = urldecode($settings['selected_text']);
	$settings['truncate_title_text'] = urldecode($settings['truncate_title_text']);
	
	$settings['paged_post_prev'] = stripslashes($settings['paged_post_prev']);
	$settings['paged_post_next'] = stripslashes($settings['paged_post_next']);
	$settings['loading_content'] = stripslashes($settings['loading_content']);
	$settings['idle_content'] = stripslashes($settings['idle_content']);
	
	$menu_headers['chrono'] = $settings['menu_month'];
	$menu_headers['cats'] = $settings['menu_cat'];
	$menu_headers['tags'] = $settings['menu_tag'];

	if (!empty($settings['menu_order'])) {
		$menu_table = preg_split('/[\s,]+/',$settings['menu_order']);
	}
	// if fade is set, check the requested year and month
	if( $settings['fade'] == 1 ) {
		if ($tag == -1 ) {
			$fade['tag'] = ' class="fade"';
		} 
		if ($category == -1 ) {
			$fade['category'] = ' class="fade"';
		} 
		if( $year == 0 && $month == 0 ) {
			$fade['year'] = ' class="fade"';
			$fade['month'] = ' class="fade"';
			$fade['post'] = ' class="fade"';
		} elseif( $month == 0 ) {
			$fade['month'] = ' class="fade"';
			$fade['post'] = ' class="fade"';
		} else {
			$fade['post'] = ' class="fade"';
		}
	}
}

function af_ela_set_month_table(&$settings, &$month_names) {
	if ($settings['abbreviated_month']) {
		if (strstr(WPLANG, '_')== FALSE) {
			$month_names = array ( '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		} else {
			$month_names = array ( '', __('Jan_January_abbreviation'), __('Feb_February_abbreviation'), __('Mar_March_abbreviation'), __('Apr_April_abbreviation'), __('May_May_abbreviation'), __('Jun_June_abbreviation'), __('Jul_July_abbreviation'), __('Aug_August_abbreviation'), __('Sep_September_abbreviation'), __('Oct_October_abbreviation'), __('Nov_November_abbreviation'), __('Dec_December_abbreviation'));
		}
	} else {
		$month_names = array('', __('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
	}
}

af_ela_get_requests($menu, 
					$year,
					$month,
					$category,
					$tag,
					$paged_offset,
					$fade);

af_ela_get_tidy_settings($paged_post,
						 $path,
						 $settings,
						 $menu_headers,
						 $menu_table,
						 $fade,
						 $year,
						 $month,
						 $category,
						 $tag);


af_ela_set_month_table($settings, $month_names);

if ($paged_offset !=-1) {
	switch($menu_table[$menu]) {
	case 'chrono':
		$err = af_ela_read_years();
		if ($err === false) die();
		$err = af_ela_read_months();
		if ($err === false) die();
		$err = af_ela_read_posts();
		if ($err === false) die();
		$year_list = af_ela_generate_years();
		
		$month_list = af_ela_generate_months();
				
		$post_list = af_ela_generate_posts();
		$layer = ' ' .$settings['id'] . '-post-chrono|';
		$text .= $post_list;
		break;
			
	case 'cats':	
	
		$err = af_ela_read_categories();
		if ($err === false) die();
		$err = af_ela_read_posts();
		if ($err === false) die();
		$category_list = af_ela_generate_categories();
	
		$post_list = af_ela_generate_posts();	
		$layer = ' ' .$settings['id'] . '-post-cats|';
		$text .= $post_list;
	
		break;
			
	case 'tags':	
		$err = af_ela_read_tags();	
		if ($err === false) die();
		$err = af_ela_read_posts();
		if ($err === false) die();
		$tag_list = af_ela_generate_tags();	
			
		$post_list = af_ela_generate_posts();
		$layer = ' ' .$settings['id'] . '-post-tags|';
		$text .= $post_list;
		break;
			
	case 'none':
	default:
		break;
	}
} else {
	$text = <<<HEADER_TEXT
<div id="${settings['id']}-loading">${settings['idle_content']}</div>
<ul id="${settings['id']}-menu">
	
HEADER_TEXT;
	
	foreach($menu_table as $menu_key => $menu_item) {
		if ($menu_item !='none') {
			if ($menu_key == $menu) {
				$current = ' class="'.$settings['selected_class'].'"';
			} else {
				$current = '';
			}
			$text .= <<<BEGIN_TEXT
<li id="${settings['id']}-menu-$menu_key"$current>${menu_headers[$menu_item]}</li>

BEGIN_TEXT;
		}
	}
	$text .= "</ul>";
	
	switch($menu_table[$menu]) {
	case 'chrono':
	
		$err = af_ela_read_years();
		if ($err === false) die();
		$err = af_ela_read_months();
		if ($err === false) die();
		$err = af_ela_read_posts();
		if ($err === false) die();
		$year_list = af_ela_generate_years();
				
		$month_list = af_ela_generate_months();
				
		$post_list = af_ela_generate_posts();
			
		$text .= $year_list . $month_list . $post_list;
		break;
			
	case 'cats':	
		$err = af_ela_read_categories();
		if ($err === false) die();
		$err = af_ela_read_posts();
		if ($err === false) die();
		$category_list = af_ela_generate_categories();
	
		$post_list = af_ela_generate_posts();	
			
		$text .= $category_list . $post_list;
	
		break;
			
	case 'tags':	
		$err = af_ela_read_tags();	
		if ($err === false) die();
		$err = af_ela_read_posts();
		if ($err === false) die();
		$tag_list = af_ela_generate_tags();	
			
		$post_list = af_ela_generate_posts();
		
		$text .= $tag_list . $post_list;
		break;
			
	case 'none':
	default:
		break;
	}
	$layer = ' ' .$settings['id'] . '|'; 
}

header("Content-Type: text/html; charset=${settings['charset']}");
echo $layer;
echo $text;
?>
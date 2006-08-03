<?php
/*
// +----------------------------------------------------------------------+
// | Licenses and copyright acknowledgements are located at               |
// | http://www.sonsofskadi.net/wp-content/elalicenses.txt                |
// +----------------------------------------------------------------------+
*/

/* ***********************************
 * dirty little debug function
 * ***********************************/	
function logthis($message,$function = __FUNCTION__ ,$line = __LINE__, $file = __FILE__, $info = false) {
	global $af_ela_cache_root, $debug;
	
	if ($debug) {
		$handle = @fopen(ABSPATH ."wp-content/log.log", 'a');
		if( $handle === false ) {
			return false;
		}
		$now = current_time('mysql', 1);
		$messageHeader = $now." - In ". basename($file) . " - In ".$function." at ".$line ." : \r\n";
		fwrite($handle, $messageHeader);
		fwrite($handle, str_replace("\t", "", serialize($message)));
		fwrite($handle, "\r\n\r\n");
		fclose($handle);
	} else if($info) {
		$handle = @fopen(ABSPATH."wp-content/log.log", 'a');
		if( $handle === false ) {
			return false;
		}
		$now = current_time('mysql', 1);
		$messageHeader = $now." - In ". basename($file) . " - In ".$function." at ".$line ." : \r\n";
		fwrite($handle, $messageHeader);
		fwrite($handle, str_replace("\t", "", serialize($message)));
		fwrite($handle, "\r\n\r\n");
		fclose($handle);
	}
}

/* ***********************************
 * Cache generator class
 * ***********************************/	
class af_ela_classGenerator {
	
	var $cache;
	var $utwCore;
	var $yearTable = array();
	var $monthTable = array();
	var $catsTable = array();
	var $postsInCatsTable = array();
	var $postToGenerate = array();
	var $tagsTable = array();
	var $postsInTagsTable = array();
	
	/* ***********************************
	 * Helper Function : class constructor
	 * ***********************************/	
	function af_ela_classGenerator() {
		global $utw_is_present;
		$this->cache = new af_ela_classCacheFile('');
		if($utw_is_present) $this->utwCore = new UltimateTagWarriorCore;
		return true;
	}
	/* ***********************************
	 * Helper Function : Find info about 
	 * 		updated post.
	 * ***********************************/	
	function buildPostToGenerateTable($exclude, $id, $commentId = false) {
		global $wpdb, $tabletags, $tablepost2tag, $utw_is_present;
		
		if (!empty($exclude)) {
			$excats = preg_split('/[\s,]+/',$exclude);
			if (count($excats)) {
				foreach ($excats as $excat) {
					$exclusions .= ' AND category_id <> ' . intval($excat) . ' ';
				}
			}
		}

		if(!$commentId) {
			if($id) { 
				$dojustid = ' AND ID = ' . intval($id) . ' ' ;
			}

			$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, category_id 
				FROM $wpdb->posts 
				INNER JOIN $wpdb->post2cat ON ($wpdb->posts.ID = $wpdb->post2cat.post_id)
				WHERE post_date > 0
				$exclusions $dojustid
				ORDER By post_date DESC";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
			$results = $wpdb->get_results($query);
			if ($results) {
				foreach($results as $result) {
					$this->postToGenerate['category_id'][] = $result->category_id;
					}
			}
			$this->postToGenerate['new_year']= $results[0]->year;
			$this->postToGenerate['new_month']= $results[0]->month;
			
			// For UTW
			if($utw_is_present) {
				$query = "SELECT tag_id
					FROM $wpdb->posts 
					INNER JOIN $wpdb->post2cat ON ($wpdb->posts.ID = $wpdb->post2cat.post_id)
					INNER JOIN $tablepost2tag ON ($wpdb->posts.ID = $tablepost2tag.post_id) 
					WHERE post_date > 0
					$exclusions $dojustid
					ORDER By post_date DESC";
				logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
				$results = $wpdb->get_results($query);
				if ($results) {
					foreach($results as $result) {
						$this->postToGenerate['tag_id'][] = $result->tag_id;
					}
				}
			}
			// End of stuff for UTW
			
			return true;
		} else {
			$query = "SELECT comment_post_ID  
				FROM $wpdb->comments
				WHERE comment_ID = $id AND comment_approved = '1'";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
			$result = $wpdb->get_var($query);
			if ($result) {
				$id = $result;
				if($id) {
					$dojustid = ' AND ID = ' . intval($id) . ' ' ;
				}

				$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, category_id
					FROM $wpdb->posts 
					INNER JOIN $wpdb->post2cat ON ($wpdb->posts.ID = $wpdb->post2cat.post_id)
					WHERE post_date > 0
					$exclusions $dojustid
					ORDER By post_date DESC";
				logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
				$results = $wpdb->get_results($query);
				if($results) {
					foreach($results as $result) {
						$this->postToGenerate['category_id'][]=$result->category_id;
					}
					$this->postToGenerate['post_id'] = $id;
					$this->postToGenerate['new_year']= $results[0]->year;
					$this->postToGenerate['new_month'] = $results[0]->month;
					$this->yearTable = array($this->postToGenerate['new_year'] => 0);
					$this->monthTable[$this->postToGenerate['new_year']] = array($this->postToGenerate['new_month'] => 0);
					$this->catsTable = $this->postToGenerate['category_id'];
					return true;
				}
			}
			return false;
		}
	}
	/* ***********************************
	 * Helper Function : build Years.
	 * ***********************************/	
	function buildYearsTable($exclude, $id = false) {
		global $wpdb;
		
		if (!empty($exclude)) {
			$excats = preg_split('/[\s,]+/',$exclude);
			if (count($excats)) {
				foreach ($excats as $excat) {
					$exclusions .= ' AND p2c.category_id <> ' . intval($excat) . ' ';
				}
			}
		}
		$now = current_time('mysql', 1);
		
		$query = "SELECT DISTINCT YEAR(p.post_date) AS `year`
			FROM $wpdb->posts p 
			INNER JOIN $wpdb->post2cat p2c ON (p.ID = p2c.post_id)
			WHERE p.post_date > 0
			$exclusions 
			ORDER By p.post_date DESC";
		logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
		$year_results = $wpdb->get_results($query);
		if( $year_results ) {
			foreach( $year_results as $year_result ) {
				$query = "SELECT p.ID
					FROM $wpdb->posts p  
					INNER JOIN $wpdb->post2cat p2c ON (p.ID = p2c.post_id) 
					WHERE YEAR(p.post_date) = $year_result->year 
					$exclusions 
					AND p.post_status = 'publish' 
					AND p.post_date_gmt < '$now'
					GROUP BY p.ID";
				logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
				$num_entries_for_year = $wpdb->get_results($query);
				if(count($num_entries_for_year)) $this->yearTable[$year_result->year] = count($num_entries_for_year);
			}
		}
		if ($this->yearTable) {
			$this->cache->contentIs($this->yearTable);
			$res = $this->cache->writeFile('years.dat');
			logthis($this->yearTable);
			logthis($res);
		}
		if($id) {
			$this->cache->readFile('years.dat');
			$diffyear = array_diff_assoc($this->cache->readFileContent, $this->yearTable);
			if (!empty($diffyear)) {
				$this->yearTable = $diffyear;
			} else {
				$this->yearTable = array($this->postToGenerate['new_year'] => 0);
			}
		}
	}
	/* ***********************************
	 * Helper Function : build Months.
	 * ***********************************/
	function buildMonthsTable($exclude, $id = false) {
		global $wpdb;
		
		if (!empty($exclude)) {
			$excats = preg_split('/[\s,]+/',$exclude);
			if (count($excats)) {
				foreach ($excats as $excat) {
					$exclusions .= ' AND p2c.category_id <> ' . intval($excat) . ' ';
				}
			}
		}
		
		$now = current_time('mysql', 1);
		foreach( $this->yearTable as $year => $y ) {
			$query = "SELECT DISTINCT MONTH(p.post_date) AS `month` 
				FROM $wpdb->posts p
				INNER JOIN $wpdb->post2cat p2c ON (p.ID = p2c.post_id )
				WHERE YEAR(p.post_date) = $year 
				$exclusions  
				AND p.post_date_gmt < '$now' 
				ORDER By p.post_date DESC";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
			$month_results = $wpdb->get_results($query);
			if( $month_results ) {
				foreach( $month_results as $month_result ) {
					$query = "SELECT p.ID 
						FROM $wpdb->posts p
						INNER JOIN $wpdb->post2cat p2c ON (p.ID = p2c.post_id) 
						WHERE YEAR(p.post_date) = $year 
						$exclusions
						AND MONTH(p.post_date) = $month_result->month 
						AND p.post_status = 'publish' 
						AND p.post_date_gmt < '$now' 
						GROUP BY p.ID";
					logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
					$num_entries_for_month = $wpdb->get_results($query);
					if (count($num_entries_for_month)) $this->monthTable[$year][$month_result->month] = count($num_entries_for_month);
				}
				if ($this->monthTable[$year]) {
					$this->cache->contentIs($this->monthTable[$year]);
					$this->cache->writeFile($year . '.dat');
				}
				if($id) {
					$this->cache->readFile($year . '.dat');
					$diffmonth = array_diff_assoc($this->cache->readFileContent, $this->monthTable[$year]);
					if (!empty($diffmonth)) {
						$this->monthTable[$year] = $diffmonth;
					} else {
						$this->monthTable[$year] = array($this->postToGenerate['new_month'] => 0);
					}
				}
			}
		}
	}
	/* ***********************************
	 * Helper Function : build Posts in 
	 * 			Month.
	 * ***********************************/
	function buildPostsInMonthsTable($exclude, $hide_ping_and_track, $id = false) {
		global $wpdb;
		if( 1 == $hide_ping_and_track ) {
			$ping = "AND comment_type NOT LIKE '%pingback%' AND comment_type NOT LIKE '%trackback%'";
		} else {
			$ping = '';
		}
		
		if (!empty($exclude)) {
			$excats = preg_split('/[\s,]+/',$exclude);
			if (count($excats)) {
				foreach ($excats as $excat) {
					$exclusions .= ' AND category_id <> ' . intval($excat) . ' ';
				}
			}
		}
		
		$posts = array();
		$now = current_time('mysql', 1);
		foreach( $this->yearTable as $year => $y ) {
			$posts[$year] = array();
			foreach( $this->monthTable[$year] as $month =>$m ) {
				$posts[$year][$month] = array();
				$query = "SELECT ID, post_title, DAYOFMONTH(post_date) as `day`, comment_status 
					FROM $wpdb->posts 
					WHERE YEAR(post_date) = $year 
					AND MONTH(post_date) = $month 
					AND post_status = 'publish' 
					AND post_date_gmt < '$now' 
					ORDER By post_date DESC";
				logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
				$post_results = $wpdb->get_results($query);
				if( $post_results ) {
					foreach( $post_results as $post_result ) {
						$query = "SELECT category_id
							FROM $wpdb->post2cat 
							WHERE post_id = $post_result->ID
							$exclusions";
						logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
						$posts_in_cat_results = $wpdb->get_results($query);
						if (!empty($posts_in_cat_results)) {
							$query = "SELECT COUNT(comment_ID) FROM $wpdb->comments 
								WHERE comment_post_ID = $post_result->ID 
								AND comment_approved = '1' 
								$ping";
							logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
							$num_comments = $wpdb->get_var($query);
							$posts[$year][$month][$post_result->ID] = array($post_result->day, $post_result->post_title, get_permalink($post_result->ID), $num_comments, $post_result->comment_status);
						}
					}
				}
				if ($posts[$year][$month]) {
					$this->cache->contentIs($posts[$year][$month]);
					$this->cache->writeFile($year . '-' . $month . '.dat');
				}
			}
		}
	}
	/* ***********************************
	 * Helper Function : build Categories.
	 * ***********************************/	
	function buildCatsTable($exclude='', $id = false) {
		$this->buildCatsList('ID', 'asc', FALSE, TRUE, '0', 0, $exclude, TRUE);
		foreach( $this->catsTable as $category ) {
			$parentcount = 0;
			if(($parentkey = $category[4])) {
				$parentcount++;
				while($parentkey) {
					$parentcount++;
					$this->catsTable[$parentkey][6] = TRUE;
					$parentkey=$this->catsTable[$parentkey][4];
				}
			}
			$this->catsTable[$category[0]][5] = $parentcount;
		}
		foreach( $this->catsTable as $category ) {
			if ($category[6] == TRUE || intval($category[3]) > 0) {
				$this->catsTable[$category[0]][6] = TRUE;
			} else {
				$this->catsTable[$category[0]][6] = FALSE;
			}
		}
		if($id) {
			if ($this->cache->readFile('categories.dat')) {
				$diffTempo = array_diff_assoc($this->cache->readFileContent, $this->catsTable);
				if(!empty($diffTempo)) $diffcats = $diffTempo;
			}
		}
		$this->cache->contentIs($this->catsTable);
		$this->cache->writeFile('categories.dat');
		if($id) {			
			if (!empty($diffcats)) {
				$this->catsTable = $diffcats;
			} else {
				$this->catsTable = $this->postToGenerate['category_id'];
			}
		}
	}
	/* ***********************************
	 * Helper Function : build list of cats
	 * ***********************************/	
	function buildCatsList($sort_column = 'ID', $sort_order = 'asc', $hide_empty = FALSE, $children=TRUE, $child_of=0, $categories=0, $exclude = '', $hierarchical=TRUE, $id = false) {
		global $wpdb, $category_posts;
		
		if (!empty($exclude)) {
			$excats = preg_split('/[\s,]+/',$exclude);
			if (count($excats)) {
				foreach ($excats as $excat) {
					$exclusions .= ' AND c.cat_ID <> ' . intval($excat) . ' ';
				}
			}
		}

		if (intval($categories)==0){
			$sort_column = 'c.cat_'.$sort_column;
	
			$query  = "SELECT cat_ID, cat_name, category_nicename, category_parent
				FROM $wpdb->categories c
				WHERE c.cat_ID > 0 $exclusions $dojustid
				ORDER BY $sort_column $sort_order";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
			$categories = $wpdb->get_results($query);
		}
	
		if (!count($category_posts)) {
			$now = current_time('mysql', 1);
			$query = "SELECT c.cat_ID,
				COUNT(distinct p2c.post_id) AS cat_count
				FROM $wpdb->categories c
				INNER JOIN $wpdb->post2cat p2c ON (c.cat_ID = p2c.category_id)
				INNER JOIN $wpdb->posts p ON (p.ID = p2c.post_id)
				WHERE p.post_status = 'publish'
				AND p.post_date_gmt < '$now' 
				$exclusions 
				GROUP BY p2c.category_id";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);	
			$cat_counts = $wpdb->get_results($query);

	        if (! empty($cat_counts)) {
	            foreach ($cat_counts as $cat_count) {
	                if (1 != intval($hide_empty) || $cat_count > 0) {
	                    $category_posts[$cat_count->cat_ID] = $cat_count->cat_count;
	                }
	            }
	        }
		}
		foreach ($categories as $category) {
			if ((intval($hide_empty) == 0 || isset($category_posts[$category->cat_ID])) && (!$hierarchical || $category->category_parent == $child_of) ) {
				$this->catsTable[$category->cat_ID] = array(	$category->cat_ID, 
	 															$category->cat_name,
	 															$category->category_nicename, 
																$category_posts["$category->cat_ID"], 
	 															$category->category_parent);
				if ($hierarchical && $children) {
					$this->buildCatsList(	$sort_column,
										$sort_order, 
										$hide_empty, 
										$children, 
										$category->cat_ID, 
										$categories, 
										$exclude, 
										$hierarchical);
				}
			}
		}
	}
	/* ***********************************
	 * Helper Function : build Posts In 
	 * 			Categories
	 * ***********************************/	
	function buildPostsInCatsTable($exclude='',$hide_ping_and_track) {
		global $wpdb, $category_posts;
		
		if( 1 == $hide_ping_and_track ) {
			$ping = "AND comment_type NOT LIKE '%pingback%' AND comment_type NOT LIKE '%trackback%'";
		} else {
			$ping = '';
		}
		if (!empty($exclude)) {
			$excats = preg_split('/[\s,]+/',$exclude);
			if (count($excats)) {
				foreach ($excats as $excat) {
					$exclusions .= ' AND category_id <> ' . intval($excat) . ' ';
				}
			}
		}
		$now = current_time('mysql', 1);
		logthis($this->catsTable);
		foreach( $this->catsTable as $category ) {
			$posts_in_cat[$category[0]] = array();
			$query = "SELECT post_id
				FROM $wpdb->post2cat 
				WHERE category_id = $category[0] 
				$exclusions";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
			$posts_in_cat_results = $wpdb->get_results($query);
	
			if( $posts_in_cat_results ) {
				$posts_in_cat_results = array_reverse($posts_in_cat_results);
				foreach( $posts_in_cat_results as $post_in_cat_result ) {
					
					$query = "SELECT ID, post_title, post_date as `day`, comment_status 
						FROM $wpdb->posts 
						WHERE ID = $post_in_cat_result->post_id 
						AND post_status = 'publish' 
						AND post_date_gmt <= '$now'
						ORDER By post_date";
					logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
					$post_results = $wpdb->get_results($query);
					if( $post_results ) {
						foreach( $post_results as $post_result ) {
							$query = "SELECT COUNT(comment_ID) 
								FROM $wpdb->comments 
								WHERE comment_post_ID = $post_result->ID 
								AND comment_approved = '1' 
								$ping";
							logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
							$num_comments = $wpdb->get_var($query);
							$this->postsInCatsTable[$category[0]][$post_result->ID] = array($post_result->day, $post_result->post_title, get_permalink($post_result->ID), $num_comments, $post_result->comment_status);
						}
					}
				}
				if ($this->postsInCatsTable[$category[0]]) {
					$this->cache->contentIs($this->postsInCatsTable[$category[0]]);
					$this->cache->writeFile('cat-' . $category[0] . '.dat');
				}
			}
		}
	}
	/* ***********************************
	 * Helper Function : build Tags.
	 * ***********************************/	
	function buildTagsTable($exclude='', $id = false, $order = false, $orderparam = 0) {
		global $utw_is_present;
		if($utw_is_present) {
			global $wpdb, $tabletags, $tablepost2tag;
			
			if (!empty($exclude)) {
				$excats = preg_split('/[\s,]+/',$exclude);
				if (count($excats)) {
					foreach ($excats as $excat) {
						$exclusions .= ' AND p2c.category_id <> ' . intval($excat) . ' ';
					}
				}
			}
					
			switch($order) {
				case 2: // X is the min number of post per tag
				$ordering = "HAVING tag_count >= ". $orderparam . " ORDER BY tag_count DESC";
				break;
				case 1: // X is the number of tag to show
				$ordering = "ORDER BY tag_count DESC LIMIT ". $orderparam;
				break;
				case 0:
				default:
				$ordering = "";
				break;
			}
			
			$now = current_time('mysql', 1);

			$query = "SELECT t.tag_id, t.tag, count(distinct p2t.post_id) as tag_count
				FROM $tabletags t 
				INNER JOIN $tablepost2tag p2t ON t.tag_id = p2t.tag_id
				INNER JOIN $wpdb->posts p ON p2t.post_id = p.ID
				INNER JOIN $wpdb->post2cat p2c ON p2t.post_id = p2c.post_ID
				WHERE p.post_date_gmt < '$now'
				AND p.post_status = 'publish'
				$exclusions
				GROUP BY t.tag
				$ordering";
			logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
			$tagsSet = $wpdb->get_results($query);
			$tagged_posts = 0;
			$posted_tags = 0;
			if( !empty($tagsSet) ) {
				foreach($tagsSet as $tag) {
					if ($tag->tag_count) {
						$this->tagsTable[$tag->tag_id] = array($tag->tag_id, $tag->tag, $tag->tag_count );
						$tagged_posts++;
						if (intval($posted_tags) < intval($tag->tag_count)) $posted_tags = $tag->tag_count;
					}
				}
				if ($order!= false ) {
					$this->tagsTable = $this->arraySort($this->tagsTable, 1);
				}
				
				$this->tagsTable[0] = array($tagged_posts, $posted_tags);
				
				$this->cache->contentIs($this->tagsTable);
				$this->cache->writeFile('tags.dat');
				
				if($id) {
					$this->cache->readFile('tags.dat');
					$difftags = array_diff_assoc($this->cache->readFileContent, $this->tagsTable);
					if (!empty($difftags)) {
						$this->tagsTable = $difftags;
					} else {
						$this->tagsTable = $this->postToGenerate['tag_id'];
					}
				}
			}
		}
		if (empty($this->tagsTable)) return false;
		return true;
	}
	/* ***********************************
	 * Helper Function : build Posts In 
	 * 			Tags
	 * ***********************************/	
	function buildPostsInTagsTable($exclude='',$hide_ping_and_track) {
		global $utw_is_present;
		if($utw_is_present) { 	
			global $wpdb, $tabletags, $tablepost2tag;
			
			if( 1 == $hide_ping_and_track ) {
				$ping = "AND comment_type NOT LIKE '%pingback%' AND comment_type NOT LIKE '%trackback%'";
			} else {
				$ping = '';
			}
			
			if (!empty($exclude)) {
				$excats = preg_split('/[\s,]+/',$exclude);
				if (count($excats)) {
					foreach ($excats as $excat) {
						$exclusions .= ' AND p2c.category_id <> ' . intval($excat) . ' ';
					}
				}
			}
			
			$now = current_time('mysql', 1);
			foreach( $this->tagsTable as $tag) {
				$posts_in_tags[$tags[0]] = array();
				$query = "SELECT p2t.post_id
					FROM $tablepost2tag p2t 
					INNER JOIN $wpdb->post2cat p2c ON p2t.post_id = p2c.post_ID
					WHERE p2t.tag_id = $tag[0] 
					$exclusions";
				logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
				$posts_in_tag_results = $wpdb->get_results($query);
		
				if( $posts_in_tag_results ) {
					$posts_in_tag_results = array_reverse($posts_in_tag_results);
					foreach( $posts_in_tag_results as $posts_in_tag_result ) {
						
						$query = "SELECT ID, post_title, post_date as `day`, comment_status 
							FROM $wpdb->posts 
							WHERE ID = $posts_in_tag_result->post_id 
							AND post_status = 'publish' 
							AND post_date_gmt <= '$now'
							ORDER By post_date";
						logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
						$post_results = $wpdb->get_results($query);
						if( $post_results ) {
							foreach( $post_results as $post_result ) {
								$query = "SELECT COUNT(comment_ID) 
									FROM $wpdb->comments 
									WHERE comment_post_ID = $post_result->ID 
									AND comment_approved = '1' 
									$ping";
								logthis("SQL Query :".$query, __FUNCTION__, __LINE__);
								$num_comments = $wpdb->get_var($query);
								$this->postsInTagsTable[$tag[0]][$post_result->ID] = array($post_result->day, $post_result->post_title, get_permalink($post_result->ID), $num_comments, $post_result->comment_status);
							}
						}
					}
					if ($this->postsInTagsTable[$tag[0]]) {
						$this->cache->contentIs($this->postsInTagsTable[$tag[0]]);
						$this->cache->writeFile('tag-' . $tag[0] . '.dat');
					}
				}
			}
		}
	}
	/* ***********************************
	 * Helper Function : sort a mulitdim 
	 *          array
	 * ***********************************/		
	function arraySort($array, $key) {
		foreach ($array as $i => $k) {
			$sort_values[$i] = $array[$i][$key];
		}
		asort($sort_values);
		reset($sort_values);
		$i=1;
		while (list ($arr_key, $arr_val) = each ($sort_values)) {
			$sorted_arr[$i++] = $array[$arr_key];
		}
		return $sorted_arr;
	}
}

/* ***********************************
* Cache File Handling class
* ***********************************/	
class af_ela_classCacheFile {
	var $fileContent = array();
	var $readFileContent = array();
	var $fileName;
	var $dbResults = array();
	/* ***********************************
	 * Helper Function : class creator
	 * ***********************************/	
	function af_ela_classCacheFile($filename = false) {
		if($filename===false) {
			$this->fileName = "dummy.dat";
		} else {
			$this->fileName = $filename;
		}
		return true;
	}
	/* ***********************************
	 * Helper Function : set fileContent 
	 * 			property
	 * ***********************************/	
	function contentIs($content) {
		$this->fileContent = $content;
		return true;
	}
	/* ***********************************
	 * Helper Function : read an existing 
	 * 			file and set 
	 * 			readFileContent property
	 * ***********************************/	
	function readFile($filename = false) {
		global $af_ela_cache_root;
		
		if(!($filename===false)) $this->fileName = $filename;
		
		$handle = @fopen ($af_ela_cache_root.$this->fileName, "r");
		if( $handle === false ) {
			return false;
		}
		
		$buf = fread($handle, filesize($af_ela_cache_root.$this->fileName));
		$this->readFileContent = unserialize($buf);
		
		fclose ($handle);
		return true;
	}
	/* ***********************************
	 * Helper Function : actual flushing 
	 * 			of fileContent to the file 
	 * 			system
	 * ***********************************/	
	function writeFile($filename = false) {
		global $af_ela_cache_root;
		
		if(!($filename===false)) $this->fileName = $filename;
		
		$handle = fopen($af_ela_cache_root . $this->fileName, 'w');
		if( $handle === false ) {
			return false;
		}
		fwrite($handle, serialize($this->fileContent));
		fclose($handle);
		return true;
	}
	/* ***********************************
	 * Helper Function : deletes cache 
	 * 			files
	 * ***********************************/	
	function deleteFile() {
		global $wpdb, $af_ela_cache_root;
		$del_cache_path = $af_ela_cache_root . "*.dat";
		if ( ($filelist=glob($del_cache_path)) === false ) return false;
		foreach ($filelist as $filename) {
			if (!@unlink($filename)) return false;	// delete it
		}
		return true;
	}
}
 
 
?>

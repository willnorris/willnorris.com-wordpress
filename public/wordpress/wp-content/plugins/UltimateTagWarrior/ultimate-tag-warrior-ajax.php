<?php
/* Maybe you'll need this.. maybe you won't...
$path = ini_get('include_path');
if (!(substr($path, strlen( $path ) - strlen(PATH_SEPARATOR)) === PATH_SEPARATOR)) {
	$path .= PATH_SEPARATOR;
}
$path .= $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/UltimateTagWarrior";
ini_set("include_path", $path);
*/

require('../../../wp-blog-header.php');
require_once('ultimate-tag-warrior-core.php');

$appID = "wp-UltimateTagWarrior";

$action = $_REQUEST['action'];
$tag = $_REQUEST['tag'];
$post = $_REQUEST['post'];
$format = $_REQUEST['format'];

$debug = get_option('utw_debug');

switch($action) {
	case 'del':
		if ( $user_level > 3 ) {
			$utw->RemoveTag($post, $tag);
			echo $post . "|";
			$utw->ShowTagsForPost($post, $utw->GetFormatForType("superajax"));
		}
		break;

	case 'add':
		$tags = explode(',',$tag);
		foreach ($tags as $t) {
			$utw->AddTag($post, $t);
		}
		echo $post . "|";
		if("" == $format) {
			$format = "superajax";
		}
		$utw->ShowTagsForPost($post, $utw->GetFormatForType($format));
		break;

	case 'expand':
		echo "$post-$tag|";
		echo $utw->FormatTags($utw->GetTagsForTagString('"' . $tag . '"'), $utw->GetFormatForType("linkset"));
		break;

	case 'expandrel':
		echo "$post-$tag|";
		echo $utw->FormatTags($utw->GetTagsForTagString('"' . $tag . '"'), $utw->GetFormatForType("linksetrel"));
		break;

	case 'shrink':
		echo "$post-$tag|";
		echo $utw->FormatTags($utw->GetTagsForTagString('"' . $tag . '"'), $utw->GetFormatForType($format));
		break;

	case 'shrinkrel':
		echo "$post-$tag|";
		echo $utw->FormatTags($utw->GetTagsForTagString('"' . $tag . '"'), $utw->GetFormatForType($format . "item"));
		break;

	case 'requestKeywords':

		$service = $_REQUEST['service'];

		$content = $_REQUEST['content'];

		switch ($service) {
		case "tagyu":
			$keywordAPISite = "tagyu.com";
			$keywordAPIUrl = "/api/suggest/";
			$pattern = "/(<tag.*?>)(.*?)<\/tag>/i"; //.*<//tag>)/i";

			$noUnicode = preg_replace("/%u[0-9A-F]{4}/i","",$content);

			$data = urlencode(strip_tags(urldecode($noUnicode)));

			$data = str_replace('%2F','/',$data);
			$data = str_replace('%09', '', $data);
			$data = str_replace('%26%238217%3B','\'',$data);
			$data = str_replace('%26%238220%3B','"',$data);
			$data = str_replace('%26%238221%3B','"',$data);
			$data = str_replace('%26%23038%3B','%26',$data);

			$curl_url = 'http://' . $keywordAPISite . $keywordAPIUrl . $data;

			break;

		case "yahoo":
			$keywordAPISite = "api.search.yahoo.com";
			$keywordAPIUrl = "/ContentAnalysisService/V1/termExtraction";
			$pattern = "/(<Result>)(.*?)<\/Result>/i";
			$appID = "wp-UltimateTagWarrior";
			$bypost = true;
			$data = "appid=" . $appID . "&context=" . $content;
			break;
		}

		if ($debug) {
			echo "Requested keywords...<br />";
		}

		$xml = "";

		if ($bypost) {
			$sock = fsockopen($keywordAPISite, 80, $errno, $errstr, 30);
			if (!$sock) die("$errstr ($errno)\n");

			fputs($sock, "POST $keywordAPIUrl HTTP/1.0\r\n");
			fputs($sock, "Host: $keywordAPISite\r\n");
			fputs($sock, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($sock, "Content-length: " . strlen($data) . "\r\n");
			fputs($sock, "Accept: */*\r\n");
			fputs($sock, "\r\n");
			fputs($sock, "$data\r\n");
			fputs($sock, "\r\n");

			$headers = "";
			while ($str = trim(fgets($sock, 4096)))
			  $headers .= "$str\n";

			print "\n";

			while (!feof($sock))
			  $xml .= fgets($sock, 4096);

			fclose($sock);
		} else if (function_exists('curl_exec')) {
			$curl_conn = curl_init($curl_url);
			curl_setopt( $curl_conn, CURLOPT_RETURNTRANSFER, 1 );

			$xml = curl_exec($curl_conn);
		} else {
			$sock = fsockopen($keywordAPISite, 80, $errno, $errstr, 30);
			if (!$sock) die("$errstr ($errno)\n");

			fputs($sock, "GET " . $keywordAPIUrl . $data . " HTTP/1.0\r\n\r\n");
			fputs($sock, "Host: $keywordAPISite\r\n");
			fputs($sock, "Accept: */*\r\n");
			$headers = "";
			while ($str = trim(fgets($sock, 4096)))
			  $headers .= "$str\n";

			print "\n";

			while (!feof($sock))
			  $xml .= fgets($sock, 4096);

			fclose($sock);
		}

		if ($debug) {
			echo "Response is: <xmp>$xml</xmp>";
			echo "Parsing response...<br />";
		}

		preg_match_all($pattern, $xml, $matches);
		$hasTags = false;
		if ($matches) {
			$hasTags = true;
			foreach ($matches[2] as $match) {
					echo "<a href=\"javascript:addTag('" . str_replace(' ','_',$match) . "')\">" . $match . "</a> ";
					$tagstr .= "'" . str_replace(' ','_',$match) . "',";
			}
		}

		if (!$hasTags) {
			echo "No tag suggestions";
		}
		break;


	case 'editsynonyms':

		echo '<input type="text" name="synonyms" value="' . $utw->FormatTags($utw->GetSynonymsForTag("", $tag), array("first"=>"%tag%", "default"=>", %tag%")) . '" />';
		break;

	case 'tagSearch':
		$tagset = explode('|',$tag);

		for ($i = 0; $i < count($tagset); $i++) {
			if (trim($tagset[$i]) <> "") {
				$taglist[] = "'" . trim($tagset[$i]) . "'";
			}
		}

		if (count($taglist) > 0) {
			$searchtype = $_REQUEST['type'];
			$op = "";
			$tags = $utw->GetTagsForTagString( implode(',',$taglist));

			if ($searchtype == "any") {
				$posts = $utw->GetPostsForAnyTags($tags);
				$op = "or";
			} else {
				$posts = $utw->GetPostsForTags($tags);
				$op = "and";
			}

			echo "<h4>Matches for ";
			echo $utw->FormatTags($tags, array('first'=>'%taglink%', 'default'=>', %taglink%', 'last'=>" $op %taglink%"));
			echo "</h4>";
			echo $utw->FormatPosts($posts, array('first'=>'<dl><dt>%postlink%</dt><dd>%excerpt%</dd>','default'=>'<dt>%postlink%</dt><dd>%excerpt%</dd>', 'last'=>'<dt>%postlink%</dt><dd>%excerpt%</dd></dl>', 'none'=>'No Matching Posts'));
		}

		break;
	}

?>
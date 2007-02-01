<?php
class updater {
	function k2update() {
	if ( !empty($_POST) ) {
		if ( isset($_POST['k2scheme_file']) ) {
			$k2scheme_file = $_POST['k2scheme_file'];
			update_option('unwakeable_scheme', $k2scheme_file, '','');
			k2styleinfo_update();
		}
		if ( isset($_POST['livesearch']) ) {
			$search = $_POST['livesearch'];
			update_option('unwakeable_livesearch', $search, '','');
		}
		if ( isset($_POST['livecommenting']) ) {
			$commenting = $_POST['livecommenting'];
			update_option('unwakeable_livecommenting', $commenting, '','');
		}
		if ( isset($_POST['widthtype']) ) {
			$widthtype = $_POST['widthtype'];
			update_option('unwakeable_widthtype', $widthtype, '','');
		}
		if ( isset($_POST['asides_text']) ) {
			$asides_text = $_POST['asides_text'];
			update_option('unwakeable_asidescategory', $asides_text, '','');
		}
		if ( isset($_POST['asidesposition']) ) {
			$asidesposition = $_POST['asidesposition'];
			update_option('unwakeable_asidesposition', $asidesposition, '','');
		}
		if ( isset($_POST['asidesnumber']) ) {
			$asidesnumber = $_POST['asidesnumber'];
			update_option('unwakeable_asidesnumber', $asidesnumber, '','');
		}
		if ( isset($_POST['about_text']) ) {
			$about = $_POST['about_text'];
			update_option('unwakeable_aboutblurp', $about, '','');
		}
		if ( isset($_POST['blog_text']) ) {
			$blogtext = $_POST['blog_text'];
			update_option('unwakeable_blogornoblog', $blogtext, '','');
		}
		if ( isset($_POST['archives']) ) {
			$add = $_POST['archives'];
			update_option('unwakeable_archives', $add, '','');
			archive::create_archive();
		} else {
		// thanks to Michael Hampton, http://www.ioerror.us/ for the assist
			$remove = '';
			update_option('unwakeable_archives', $remove, '','');
			archive::delete_archive();
		}
		if ( isset($_POST['format']) ) {
			$k2_style_format = $_POST['format'];
			update_option('unwakeable_styleinfo_format', $k2_style_format, '','');
			k2styleinfo_update();
		}
		if ( isset($_POST['rollingarchives']) ) {
			$rollarchives = $_POST['rollingarchives'];
			update_option('unwakeable_rollingarchives', $rollarchives, '','');
		}

		if ( isset($_POST['configela']) ) {
			if (!archive::setup_archive()) unset($_POST['configela']);
			}
		if ( isset($_POST['uninstall']) ) {
			tools::uninstall();
			}
		if ( isset($_POST['convert']) ) {
			tools::convert();
			}
		}
	}
}
?>

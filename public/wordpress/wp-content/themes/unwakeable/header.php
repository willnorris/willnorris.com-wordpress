<?php load_theme_textdomain('k2_domain'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<title><?php wp_title(''); if (function_exists('is_tag') and is_tag()) { ?>Tag Archive for <?php echo $tag; } if (is_archive()) { ?> archive<?php } elseif (is_search()) { ?> Search for <?php echo $s; } if ( !(is_404()) and (is_search()) or (is_single()) or (is_page()) or (function_exists('is_tag') and is_tag()) or (is_archive()) ) { ?> at <?php } ?> <?php bloginfo('name'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>"/>
	<meta name="template" content="K2 <?php if (function_exists('k2info')) { k2info('version'); } ?>" />
 	<meta name="description" content="<?php bloginfo('description'); ?>" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('stylesheet_url'); ?>" />
	<link rel="stylesheet" type="text/css" media="print" href="<?php bloginfo('template_url'); ?>/css/print.css" />
	<?php /* Custom Style */ if (get_option('unwakeable_scheme') != '') { ?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php k2info('scheme'); ?>" />
	<?php } ?>

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />

	<?php if (is_single() or is_page()) { ?>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php } ?>

	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/k2functions.js"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/prototype.js.php"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/effects.js.php"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/slider.js.php"></script>

	<?php /* Live Commenting */ if ((get_option('unwakeable_livecommenting') == 1) and ((is_page() or is_single()) and (!isset($_GET['jal_edit_comments'])) and ('open' == $post-> comment_status) or ('comment' == $post-> comment_type) )) { ?>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/comments.js.php"></script>
	<?php } ?>

	<?php /* LiveSearch */ if (get_option('unwakeable_livesearch') == 1) { ?>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/livesearch.js.php"></script>
	<?php } ?>

	<?php /* Rolling Archives */ if (get_option('unwakeable_rollingarchives') == 1) { ?>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/rollingarchives.js.php"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('template_directory'); ?>/css/rollingarchives.css" />
	<?php } ?>

	<?php /* Hide Author Elements */ if (!is_user_logged_in() and (is_page() or is_single()) and ($comment_author = $_COOKIE['comment_author_'.COOKIEHASH]) and ('open' == $post-> comment_status) or ('comment' == $post-> comment_type) ) { ?>
	<script type="text/javascript">
		new FastInit(OnLoadUtils);
	</script>
	<?php } ?>

	<?php wp_get_archives('type=monthly&format=link'); ?>

	<?php wp_head(); ?>	
</head>


<body class="<?php k2_body_class(); ?>" <?php k2_body_id(); ?>>
<?php if(function_exists('wp_admin_bar')) wp_admin_bar(); ?>
<div id="page">

	<div id="header">
             <div class="top">

            <div id="title" class="title"><a href="<?php echo get_settings('home'); ?>" title="Back to the front page"></a>
                 </div>
		
		<ul id="menu">
			<?php wp_list_pages('sort_column=menu_order&depth=1&title_li='); ?>

		</ul>
                
	     </div>
	</div>

		<hr />

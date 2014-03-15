<?php
require_once('admin.php');
cache_javascript_headers();

switch ( $_GET['pagenow'] ) :
	case 'post.php' :
	case 'post-new.php' :
		$man = 'postmeta';
		break;
	case 'page.php' :
	case 'page-new.php' :
		$man = 'pagemeta';
		break;
	case 'link.php' :
		$man = 'linkmeta';
		break;
	default:
		exit;
		break;
endswitch;
?>
addLoadEvent( function() {var manager = new dbxManager('<?php echo $man; ?>');} );

addLoadEvent( function()
{
	//create new docking boxes group
	var meta = new dbxGroup(
		'grabit', 		// container ID [/-_a-zA-Z0-9/]
		'vertical', 	// orientation ['vertical'|'horizontal']
		'10', 			// drag threshold ['n' pixels]
		'no',			// restrict drag movement to container axis ['yes'|'no']
		'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
		'yes', 			// include open/close toggle buttons ['yes'|'no']
		'closed', 		// default state ['open'|'closed']
		'<?php echo js_escape(__('open')); ?>', 		// word for "open", as in "open this box"
		'<?php echo js_escape(__('close')); ?>', 		// word for "close", as in "close this box"
		'<?php echo js_escape(__('click-down and drag to move this box')); ?>', // sentence for "move this box" by mouse
		'<?php echo js_escape(__('click to %toggle% this box')); ?>', // pattern-match sentence for "(open|close) this box" by mouse
		'<?php echo js_escape(__('use the arrow keys to move this box')); ?>', // sentence for "move this box" by keyboard
		'<?php echo js_escape(__(', or press the enter key to %toggle% it')); ?>',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
		'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
		);

	// Boxes are closed by default. Open the Category box if the cookie isn't already set.
	var catdiv = document.getElementById('categorydiv');
	if ( catdiv ) {
		var button = catdiv.getElementsByTagName('A')[0];
		if ( dbx.cookiestate == null && /dbx\-toggle\-closed/.test(button.className) )
			meta.toggleBoxState(button, true);
	}

	var advanced = new dbxGroup(
		'advancedstuff', 		// container ID [/-_a-zA-Z0-9/]
		'vertical', 		// orientation ['vertical'|'horizontal']
		'10', 			// drag threshold ['n' pixels]
		'yes',			// restrict drag movement to container axis ['yes'|'no']
		'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
		'yes', 			// include open/close toggle buttons ['yes'|'no']
		'closed', 		// default state ['open'|'closed']
		'<?php echo js_escape(__('open')); ?>', 		// word for "open", as in "open this box"
		'<?php echo js_escape(__('close')); ?>', 		// word for "close", as in "close this box"
		'<?php echo js_escape(__('click-down and drag to move this box')); ?>', // sentence for "move this box" by mouse
		'<?php echo js_escape(__('click to %toggle% this box')); ?>', // pattern-match sentence for "(open|close) this box" by mouse
		'<?php echo js_escape(__('use the arrow keys to move this box')); ?>', // sentence for "move this box" by keyboard
		'<?php echo js_escape(__(', or press the enter key to %toggle% it')); ?>',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
		'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
		);
});

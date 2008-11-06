<?php

# -- WordPress Plugin Interface -----------------------------------------------
/*
Plugin Name: XMd
Plugin URI: http://willnorris.com/
Description: XMd is an extension to John Gruber's <a href="http://daringfireball.net/projects/markdown/">Markdown</a> syntax, allowing you to define your own markdown-like tags that will be expanded into HTML.  This plugin is a branch of Bruce Anderson's <a href="http://warpedvisions.org/projects/simplelink">SimpleLink</a> plugin.
Version: 0.2
Author: Will Norris
Author URI: http://willnorris.com/
*/

if (isset($wp_version)) {
    add_filter('the_content', array('XMd', 'process_links'), 5);
    add_filter('the_excerpt', array('XMd', 'process_links'), 5);
    add_filter('the_excerpt_rss', array('XMd', 'process_links'), 5);
    add_filter('comment_text', array('XMd', 'process_links'), 5);
    add_action('admin_menu', array('XMd', 'menu'));
}

class XMd {

    function process_links($content) { 
        /* This isn't the most elegant way to fix the problem with tags inside 
        of <code> blocks, but it works.  Basically we replace all code blocks 
        with the ASCII SUB character, do all of our normal processing, and then 
        plug the code blocks back into place.  I'd love to find a better solution 
        if there is one. */

        $subChar = chr(26); // ASCII substitution character

		//$content = html_entity_decode($content);

        preg_match_all("/<code>(.+)?<\/code>/", $content, $matches);
        if (!empty($matches[0])) {
            $codeBlocks = $matches[0];
            $codeBlockPatterns = array();
            for($i=0; $i<sizeof($codeBlocks); $i++) { 
                // why isn't quotemeta escaping the "/" characters?
                $codeBlockPatterns[$i] = "/".preg_replace("/\//", "\\/", quotemeta($codeBlocks[$i]))."/";
            }
            $content = preg_replace($codeBlockPatterns, $subChar, $content, 1);
        }

        $content = preg_replace_callback("/\[(.*?)\](.|)/",array('XMd', 'process_links_callback'),$content); 

        if (!empty($matches[0])) {
            $content = preg_replace(array_fill(0, sizeof($codeBlocks), "/".$subChar."/"), $codeBlocks, $content, 1);
        }

        return $content;
    } 

    function process_links_callback($match) { 

        XMd::defaults();
        $xmdPatterns = get_option('xmd_patterns');

        $fulltext = $match[1];
        $after = $match[2] or '';

		list($patternName, $dataString) = explode(":", $fulltext, 2);
		$patternName = trim($patternName);
		$dataString = trim($dataString);

        if ($patternName && $dataString) {
            if (array_key_exists($patternName, $xmdPatterns)) {
                $data = preg_split("/(?<!\\\)\|/", $dataString);
				$text = $xmdPatterns[$patternName];

				for ($i=sizeof($data); $i>0; $i--) {
					$text = preg_replace("/(?<!\\\)" . str_repeat("\*", $i) . "/", $data[$i-1], $text);
				}

                $text = preg_replace("/\\\\\*/", "*", $text); //allow for literal asterisks

				if (preg_match("/^<.+>$/", $text)) {
					// if the pattern is wrapped in its own HTML, then return it as-is
					return $text.$after;
				} else {
					return "<a href=\"$text\">" . (sizeof($data)>1 ? $data[(sizeof($data)-1)] : $data[0]) . "</a>$after"; 
				}
            } else {
                return $match[0];
            }
        } else {
			return $match[0];
		}

    } 



    /* Admin Menu */
    function menu() {
        if (function_exists('add_options_page')) {
            add_options_page('XMd Options', 'XMd', 9, __FILE__, array('XMd', 'manage'));
        }
    }

    function manage() {
        XMd::defaults();
        $xmdPatterns = get_option('XMd_patterns');

        if (isset($_REQUEST['action'])) {
            if ($_REQUEST['action'] == 'Reset Defaults') {
                XMd::defaults(true);
            } else {
                echo '<div id="message" class="updated fade"><p><strong>';
                _e('Changes have been saved', '');
                echo '</strong></p></div>';
                
                $i=0;
                $xmdPatterns = array();
                while(isset($_REQUEST["link$i-tag"])) {
                    if ($_REQUEST["link$i-tag"]) {
                        $xmdPatterns[$_REQUEST["link$i-tag"]] = stripslashes(html_entity_decode($_REQUEST["link$i-pat"], ENT_QUOTES));
                    }
                    $i++;
                }
                update_option('xmd_patterns', $xmdPatterns);
            }
        }

        echo '
        <div class="wrap">
        	<h2>XMd Options</h2>
           	<form method="post">

				<fieldset class="options">
					<legend>'.__('Pattern Syntax').'</legend>

					<p>There are two types of patterns -- simple patterns that
					are just links, and more complex patterns that can include
					any type of HTML.</p>
					
					<h4>Simple Patterns</h4>

					<h4>Complex Patterns</h4>


					<h3>Using Patterns</h3>
				</fieldset>

				<fieldset class="options">
					<legend>'.__('XMd Patterns').'</legend>

						<table>
							<tr><th>Tag</th><th>Pattern</th></tr>';

        $linkNum=0;
        foreach ($xmdPatterns as $k => $v) {
           echo '
                        <tr>
                            <td valign="top"><input name="link'.$linkNum.'-tag" value="'.$k.'" /></td>
                            <td><textarea name="link'.$linkNum.'-pat" cols="50" rows="3">'.htmlentities($v).'</textarea></td>
                        </tr>';
            $linkNum++;
        }

		echo '
                        <tr>
                            <td valign="top"><input name="link'.$linkNum.'-tag" /></td>
                            <td><textarea name="link'.$linkNum.'-pat" cols="50" rows="3"></textarea></td>
                        </tr>';
       	echo'
                    </table>

					<div class="submit"><input type="submit" name="action" value="'.__('Submit', '').'" /></div>
					<div class="submit"><input type="submit" name="action" value="'.__('Reset Defaults', '').'" /></div>

				</fieldset>
       		</form>
        </div>';
    }

    function defaults($reset = false) {
        if (FALSE === get_option('xmd_patterns') || $reset) {
            $xmdPatterns = array(
                'google' => 'http://google.com/search?q=*',
                'wiki' => 'http://en.wikipedia.org/wiki/*',
                'imdb' => 'http://imdb.com/find?q=*',
                'asin' => '<a href="http://www.amazon.com/exec/obidos/ASIN/*/" class="amazon"><img src="http://images.amazon.com/images/P/*.01.MZZZZZZZ.jpg" alt="*" /></a>',
            );
            
            update_option('xmd_patterns', $xmdPatterns);
        }
    }
}

?>

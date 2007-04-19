<?php 
/*
 
 $Id: sitemap.php 7501 2007-01-23 10:12:39Z arnee $

 XML Sitemap Generator for WordPress
 ==============================================================================
 
 This generator will create a sitemaps.org compliant sitemap of your WordPress blog.
 Currently homepage, posts, static pages, categories and archives are supported.
 
 The priority of a post depends on its comments. You can choose the way the priority
 is calculated in the options screen.
 
 Feel free to visit my website under www.arnebrachhold.de or contact me at
 himself [at] arnebrachhold [dot] de
 
 Have fun! 
   Arne
   
   
 Installation:
 ==============================================================================
 1. Upload the full directory into your wp-content/plugins directory
 2. Make your blog directory writeable OR create two files called sitemap.xml 
    and sitemap.xml.gz and make them writeable via CHMOD In most cases, your blog directory is already writeable.
 2. Activate it in the Plugin options
 3. Edit or publish a post or click on Rebuild Sitemap on the Sitemap Administration Interface
 
 
 Info for WordPress:
 ==============================================================================
 Plugin Name: Google (XML) Sitemaps 
 Plugin URI: http://www.arnebrachhold.de/redir/sitemap-home/
 Description: This generator will create a sitemaps.org compliant sitemap of your WordPress blog which is supported By Google, MSN Search and YAHOO. <a href="options-general.php?page=sitemap.php">Configuration Page</a>
 Version: 3.0b6
 Author: Arne Brachhold
 Author URI: http://www.arnebrachhold.de/
 
 
 Contributors:
 ==============================================================================
 Basic Idea             Michael Nguyen      http://www.socialpatterns.com/
 SQL Improvements       Rodney Shupe        http://www.shupe.ca/
 Japanse Lang. File     Hirosama            http://hiromasa.zone.ne.jp/
 Spanish lang. File     César Gómez Martín  http://www.cesargomez.org/
 Italian lang. File     Stefano Aglietti    http://wordpress-it.it/
 Trad.Chinese  File     Kirin Lin           http://kirin-lin.idv.tw/
 Simpl.Chinese File     june6               http://www.june6.cn/
 Swedish Lang. File     Tobias Bergius      http://tobiasbergius.se/
 Ping Code Template 1   James               http://www.adlards.com/
 Ping Code Template 2   John                http://www.jonasblog.com/
 Bug Report             Brad                http://h3h.net/
 Bug Report             Christian Aust      http://publicvoidblog.de/
 
 Code, Documentation, Hosting and all other Stuff:
                        Arne Brachhold      http://www.arnebrachhold.de/
 
 Thanks to all contributors and bug reporters! :)
 
 
 Release History:
 ==============================================================================
 2005-06-05     1.0     First release
 2005-06-05     1.1     Added archive support
 2005-06-05     1.2     Added category support
 2005-06-05     2.0a    Beta: Real Plugin! Static file generation, Admin UI
 2005-06-05     2.0     Various fixes, more help, more comments, configurable filename
 2005-06-07     2.01    Fixed 2 Bugs: 147 is now _e(strval($i)); instead of _e($i); 344 uses a full < ?php instead of < ?
                        Thanks to Christian Aust for reporting this :)
 2005-06-07     2.1     Correct usage of last modification date for cats and archives  (thx to Rodney Shupe (http://www.shupe.ca/))
                        Added support for .gz generation
                        Fixed bug which ignored different post/page priorities
                        Should support now different wordpress/admin directories
 2005-06-07     2.11    Fixed bug with hardcoded table table names instead of the $wpd vars
 2005-06-07     2.12    Changed SQL Statement of the categories to get it work on MySQL 3 
 2005-06-08     2.2     Added language file support:
                        - Japanese Language Files and code modifications by hiromasa (http://hiromasa.zone.ne.jp/)
                        - German Language File by Arne Brachhold (http://www.arnebrachhold.de)
 2005-06-14     2.5     Added support for external pages
                        Added support for Google Ping
                        Added the minimum Post Priority option
                        Added Spanish Language File by César Gómez Martín (http://www.cesargomez.org/)
                        Added Italian Language File by Stefano Aglietti (http://wordpress-it.it/)
                        Added Traditional Chine Language File by Kirin Lin (http://kirin-lin.idv.tw/)
 2005-07-03     2.6     Added support to store the files at a custom location
                        Changed the home URL to have a slash at the end
                        Required admin-functions.php so the script will work with external calls, wp-mail for example
                        Added support for other plugins to add content to the sitemap via add_filter()
 2005-07-20     2.7     Fixed wrong date format in additional pages
                        Added Simplified Chinese Language Files by june6 (http://www.june6.cn/)
                        Added Swedish Language File by Tobias Bergius (http://tobiasbergius.se/)
 2006-01-07     3.0b    Added different priority calculation modes and introduced an API to create custom ones
                        Added support to use the Popularity Contest plugin by Alex King to calculate post priority
                        Added Button to restore default configuration
                        Added several links to homepage and support
                        Added option to exclude password protected posts
                        Added function to start sitemap creation via GET and a secret key
                        Posts and pages marked for publish with a date in the future won't be included
                        Improved compatiblity with other plugins
                        Improved speed and optimized settings handling
                        Improved user-interface
                        Recoded plugin architecture which is now fully OOP
 2006-01-07     3.0b1   Changed the way for hook support to be PHP5 and PHP4 compatible
                        Readded support for tools like w.Bloggar
                        Fixed "doubled-content" bug with WP2
                        Added xmlns to enable validation
 2006-03-01     3.0b3   More performance
                        More caching
                        Better support for Popularity Contest and WP 2.x
 2006-11-16     3.0b4   Fixed bug with option SELECTS
                        Decreased memory usage which should solve timeout and memory problems
                        Updated namespace to support YAHOO and MSN
 2007-01-19     3.0b5   Javascripted page editor
                        WP 2 Design
                        YAHOO notification
                        New status report, removed ugly logfiles
                        Better Popularity Contest Support
                        Fixed double backslashes on windows systems
                        Added option to specify time limit and memory limit
                        Added option to define a XSLT stylesheet and added a default one
                        Fixed bug with sub-pages. Thanks to:
                        - Mike Baptiste (http://baptiste.us),
                        - Peter Claus Lamprecht (http://fastagent.de)
                        - Glenn Nicholas (http://publicityship.com.au)
                        Improved file handling, thanks to VJTD3 (http://www.VJTD3.com)
                        WP 2.1 improvements
 2007-01-23     3.0b6   Use memory_get_peak_usage instead of memory_get_usage if available
                        Removed the usage of REQUEST_URI since it not correct in all environment
                        Fixed that sitemap.xml.gz was not compressed (Thanks Ralph Davidovits!)
                        Added compat function "stripos" for PHP4 (Thanks to Joseph Abboud!)
                        Streamlined some code

 Maybe Todo:
 ==============================================================================
 - Your wishes :)
 
 
 License:
 ==============================================================================
 Copyright 2005,2006,2007 ARNE BRACHHOLD  (email : himself - arnebrachhold - de)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 
 
 Developer Documentation
 ==============================================================================
 
 Adding other pages to the sitemap via other plugins
 
  This plugin uses the action system of WordPress to allow other plugins
  to add urls to the sitemap. Simply add your function with add_action to
  the list and the plugin will execute yours every time the sitemap is build.
  Use the static method "GetInstance" to get the generator and AddUrl method 
  to add your content.
  
  Sample:
  function your_pages() {
	$generatorObject = &GoogleSitemapGenerator::GetInstance(); //Please note the "&" sign!
	if($generatorObject!=null) $generatorObject->AddUrl("http://blog.uri/tags/hello/",time(),"daily",0.5);
  }
  add_action("sm_buildmap","your_pages");
  
  Parameters:
  - The URL to the page
  - The last modified data, as a UNIX timestamp (optional)
  - The Change Frequency (daily, hourly, weekly and so on) (optional)
  - The priority 0.0 to 1.0 (optional)
 
 ===============================================
 
 Adding additional PriorityProviders
 
  This plugin uses several classes to calculate the post priority.
  You can register your own provider and choose it at the options screen.
  
  Your class has to extend the GoogleSitemapGeneratorPrioProviderBase class
  which has a default constructor and a method called GetPostPriority
  which you can override.
  
  Look at the GoogleSitemapGeneratorPrioByPopularityContestProvider class
  for an example.
  
  To register your provider to the sitemap generator, use the following filter:
  
  add_filter("sm_add_prio_provider","AddMyProvider");
  
  Your function could look like this:
  
  function AddMyProvider($providers) {
	array_push($providers,"MyProviderClass");
	return $providers;
  }
  
  Note that you have to return the modified list!  
   

*/

//Enable for dev! Good code doesn't generate any notices...
//error_reporting(E_ALL);
//ini_set("display_errors",1);


#region PHP5 compat functions
if(!function_exists('file_get_contents')) {
	/**
	 * Replace file_get_contents()
	 *
	 * @category    PHP
	 * @package     PHP_Compat
	 * @link        http://php.net/function.file_get_contents
	 * @author      Aidan Lister <aidan - php - net>
	 * @version     $Revision: 1.21 $
	 * @internal    resource_context is not supported
	 * @since       PHP 5
	 */
	if (!function_exists('file_get_contents')) {
		function file_get_contents($filename, $incpath = false, $resource_context = null) {
			if (false === $fh = fopen($filename, 'rb', $incpath)) {
				user_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
				return false;
			}
			
			clearstatcache();
			if ($fsize = @filesize($filename)) {
				$data = fread($fh, $fsize);
			} else {
				$data = '';
				while (!feof($fh)) {
					$data .= fread($fh, 8192);
				}
			}
			
			fclose($fh);
			return $data;
		}	
	}
}

if(!function_exists('file_put_contents')) {
	
	if (!defined('FILE_USE_INCLUDE_PATH')) {
		define('FILE_USE_INCLUDE_PATH', 1);
	}
	
	if (!defined('LOCK_EX')) {
		define('LOCK_EX', 2);
	}
	
	if (!defined('FILE_APPEND')) {
		define('FILE_APPEND', 8);
	}
	
	
	/**
	 * Replace file_put_contents()
	 *
	 * @category    PHP
	 * @package     PHP_Compat
	 * @link        http://php.net/function.file_put_contents
	 * @author      Aidan Lister <aidan - php - net>
	 * @version     $Revision: 1.25 $
	 * @internal    resource_context is not supported
	 * @since       PHP 5
	 * @require     PHP 4.0.0 (user_error)
	 */
	function file_put_contents($filename, $content, $flags = null, $resource_context = null) {
		// If $content is an array, convert it to a string
		if (is_array($content)) {
			$content = implode('', $content);
		}
		
		// If we don't have a string, throw an error
		if (!is_scalar($content)) {
			user_error('file_put_contents() The 2nd parameter should be either a string or an array',E_USER_WARNING);
			return false;
		}
		
		// Get the length of data to write
		$length = strlen($content);
		
		// Check what mode we are using
		$mode = ($flags & FILE_APPEND)?'a':'wb';
		
		// Check if we're using the include path
		$use_inc_path = ($flags & FILE_USE_INCLUDE_PATH)?true:false;
		
		// Open the file for writing
		if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
			user_error('file_put_contents() failed to open stream: Permission denied',E_USER_WARNING);
			return false;
		}
		
		// Attempt to get an exclusive lock
		$use_lock = ($flags & LOCK_EX) ? true : false ;
		if ($use_lock === true) {
			if (!flock($fh, LOCK_EX)) {
				return false;
			}
		}
		
		// Write to the file
		$bytes = 0;
		if (($bytes = @fwrite($fh, $content)) === false) {
			$errormsg = sprintf('file_put_contents() Failed to write %d bytes to %s',$length,$filename);
			user_error($errormsg, E_USER_WARNING);
			return false;
		}
		
		// Close the handle
		@fclose($fh);
		
		// Check all the data was written
		if ($bytes != $length) {
			$errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',$bytes,$length);
			user_error($errormsg, E_USER_WARNING);
			return false;
		}
		
		// Return length
		return $bytes;
	}
	
}

if (!function_exists('stripos')) {
	/**
	 * Replace stripos()
	 *
	 * @category    PHP
	 * @package     PHP_Compat
	 * @link        http://php.net/function.stripos
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     $Revision: 1.1 $
	 * @since       PHP 5
	 * @require     PHP 4.0.1 (trigger_error)
	 */
	function stripos($haystack, $needle, $offset = null) {
		if (!is_scalar($haystack)) {
			trigger_error('stripos() expects parameter 1 to be string, ' . gettype($haystack) . ' given', E_USER_WARNING);
			return false;
		}
		
		if (!is_scalar($needle)) {
			trigger_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
			return false;
		}
		
		if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
			trigger_error('stripos() expects parameter 3 to be long, ' . gettype($offset) . ' given', E_USER_WARNING);
			return false;
		}
		
		// Manipulate the string if there is an offset
		$fix = 0;
		if (!is_null($offset)) {
			if ($offset > 0) {
				$haystack = substr($haystack, $offset, strlen($haystack) - $offset);
				$fix = $offset;
			}
		}
		
		$segments = explode(strtolower($needle), strtolower($haystack), 2);
		
		// Check there was a match
		if (count($segments) == 1) {
			return false;
		}
		
		$position = strlen($segments[0]) + $fix;
		return $position;
	}
}
#endregion

/**
 * Represents the status (success and failures) of a building process
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @package sitemap
 * @since 3.0b5
 */
class GoogleSitemapGeneratorStatus {

	function GoogleSitemapGeneratorStatus() {
		$this->_startTime = $this->GetMicrotimeFloat();
		
		$exists = get_option("sm_status");
		
		if($exists === false) add_option("sm_status","","Status","no");
		
		$this->Save();
	}
	
	function Save() {
		update_option("sm_status",$this);		
	}
	
	function Load() {
		$status = @get_option("sm_status");
		if(is_a($status,"GoogleSitemapGeneratorStatus")) return $status;
		else return null;	
	}
	
	/**
	 * @var float $_startTime The start time of the building process
	 * @access private
	 */
	var $_startTime = 0;
	
	/**
	 * @var float $_endTime The end time of the building process
	 * @access private
	 */
	var $_endTime = 0;
	
	/**
	 * @var int $_memoryUsage The amount of memory used in bytes
	 * @access private
	 */
	var $_memoryUsage = 0;	
	
	/**
	 * @var int $_lastPost The number of posts processed. This value is updated every 50 posts.
	 * @access private
	 */
	var $_lastPost = 0;
	
	/**
	 * @var int $_lastTime The time when the last step-update occured. This value is updated every 50 posts.
	 * @access private
	 */
	var $_lastTime = 0;
	
	function End() {
		$this->_endTime = $this->GetMicrotimeFloat();
		
		$this->SetMemoryUsage();
		
		$this->Save();
	}
	
	function SetMemoryUsage() {
		if(function_exists("memory_get_peak_usage")) {
			$this->_memoryUsage = memory_get_peak_usage(true);
		} else if(function_exists("memory_get_usage")) {
			$this->_memoryUsage =  memory_get_usage(true);
		}	
	}
	
	function GetMemoryUsage() {
		return round($this->_memoryUsage / 1024 / 1024,2);	
	}
	
	function SaveStep($postCount) {
		$this->SetMemoryUsage();
		$this->_lastPost = $postCount;
		$this->_lastTime = $this->GetMicrotimeFloat();
		
		$this->Save();	
	}
	
	function GetTime() {
		return round($this->_endTime - $this->_startTime,2);	
	}
	
	function GetLastTime() {
		return round($this->_lastTime - $this->_startTime,2);			
	}
	
	function GetLastPost() {
		return $this->_lastPost;	
	}
	
	var $_usedXml = false;
	var $_xmlSuccess = false;
	var $_xmlPath = '';
	var $_xmlUrl = '';
	
	function StartXml($path,$url) {
		$this->_usedXml = true;
		$this->_xmlPath = $path;
		$this->_xmlUrl = $url;
		
		$this->Save();	
	}
	
	function EndXml($success) {
		$this->_xmlSuccess = $success;	
		
		$this->Save();
	}
	
	
	var $_usedZip = false;
	var $_zipSuccess = false;
	var $_zipPath = '';
	var $_zipUrl = '';
	
	function StartZip($path,$url) {
		$this->_usedZip = true;
		$this->_zipPath = $path;
		$this->_zipUrl = $url;	
		
		$this->Save();
	}
	
	function EndZip($success) {
		$this->_zipSuccess = $success;
		
		$this->Save();	
	}
	
	var $_usedGoogle = false;
	var $_gooogleSuccess = false;
	var $_googleStartTime = 0;
	var $_googleEndTime = 0;
	
	function StartGooglePing() {
		$this->_usedGoogle = true;
		$this->_googleStartTime = $this->GetMicrotimeFloat();	
		
		$this->Save();
	}
	
	function EndGooglePing($success) {
		$this->_googleEndTime = $this->GetMicrotimeFloat();
		$this->_gooogleSuccess = $success;	
		
		$this->Save();	
	}
	
	function GetGoogleTime() {
		return round($this->_googleEndTime - $this->_googleStartTime,2);	
	}
	
	var $_usedYahoo = false;
	var $_yahooSuccess = false;
	var $_yahooStartTime = 0;
	var $_yahooEndTime = 0;
	
	function StartYahooPing() {
		$this->_usedYahoo = true;
		$this->_yahooStartTime = $this->GetMicrotimeFloat();
		
		$this->Save();	
	}
	
	function EndYahooPing($success) {
		$this->_yahooEndTime = $this->GetMicrotimeFloat();
		$this->_yahooSuccess = $success;	
		
		$this->Save();	
	}
	
	function GetYahooTime() {
		return round($this->_yahooEndTime - $this->_yahooStartTime,2);	
	}
	
	function GetMicrotimeFloat() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

/**
 * Represents an item in the page list
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPage {
	
	/**
	 * @var string $_url Sets the URL or the relative path to the blog dir of the page
	 * @access private
	 */
	var $_url;
	
	/**
	 * @var float $_priority Sets the priority of this page
	 * @access private
	 */
	var $_priority;
	
	/**
	 * @var string $_changeFreq Sets the chanfe frequency of the page. I want Enums!
	 * @access private
	 */
	var $_changeFreq;
	
	/**
	 * @var int $_lastMod Sets the lastMod date as a UNIX timestamp. 
	 * @access private
	 */
	var $_lastMod;	
	
	/**
	 * Initialize a new page object
	 * 
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param bool $enabled Should this page be included in thesitemap
	 * @param string $url The URL or path of the file
	 * @param float $priority The Priority of the page 0.0 to 1.0
	 * @param string $changeFreq The change frequency like daily, hourly, weekly
	 * @param int $lastMod The last mod date as a unix timestamp
	 */
	function GoogleSitemapGeneratorPage($url="",$priority=0.0,$changeFreq="never",$lastMod=0) {
		$this->SetUrl($url);
		$this->SetProprity($priority);
		$this->SetChangeFreq($changeFreq);
		$this->SetLastMod($lastMod);
	}
	
	/**
	 * Returns the URL of the page
	 *
	 * @return string The URL
	 */
	function GetUrl() {
		return $this->_url;	
	}
	
	/**
	 * Sets the URL of the page
	 *
	 * @param string $url The new URL
	 */
	function SetUrl($url) {
		$this->_url=(string) $url;				
	}
	
	/**
	 * Returns the priority of this page
	 *
	 * @return float the priority, from 0.0 to 1.0
	 */
	function GetPriority() {
		return $this->_priority;		
	}
	
	/**
	 * Sets the priority of the page
	 *
	 * @param float $priority The new priority from 0.1 to 1.0
	 */
	function SetProprity($priority) {
		$this->_priority=floatval($priority);	
	}
	
	/**
	 * Returns the change frequency of the page
	 *
	 * @return string The change frequncy like hourly, weekly, monthly etc.
	 */
	function GetChangeFreq() {
		return $this->_changeFreq;		
	}
	
	/**
	 * Sets the change frequency of the page
	 *
	 * @param string $changeFreq The new change frequency
	 */
	function SetChangeFreq($changeFreq) {
		$this->_changeFreq=(string) $changeFreq;	
	}
	
	/**
	 * Returns the last mod of the page
	 *
	 * @return int The lastmod value in seconds
	 */
	function GetLastMod() {
		return $this->_lastMod;	
	}
	
	/**
	 * Sets the last mod of the page
	 *
	 * @param int $lastMod The lastmod of the page
	 */
	function SetLastMod($lastMod) {
		$this->_lastMod=intval($lastMod);
	}	
	
	function Render() {
		
		$r="";
		$r.= "\t<url>\n";
		$r.= "\t\t<loc>" . $this->EscapeXML($this->_url) . "</loc>\n";
		if($this->_lastMod>0) $r.= "\t\t<lastmod>" . date('Y-m-d\TH:i:s+00:00',$this->_lastMod) . "</lastmod>\n";
		if(!empty($this->_changeFreq)) $r.= "\t\t<changefreq>" . $this->_changeFreq . "</changefreq>\n";	
		if($this->_priority!==false && $this->_priority!=="") $r.= "\t\t<priority>" . $this->_priority . "</priority>\n";
		$r.= "\t</url>\n";	
		return $r;
	}	
	
	function EscapeXML($string) {
		return str_replace ( array ( '&', '"', "'", '<', '>'), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;'), $string);
	}				
}

class GoogleSitemapGeneratorXmlEntry {
	
	var $_xml;
	
	function GoogleSitemapGeneratorXmlEntry($xml) {
		$this->_xml = $xml;	
	}
	
	function Render() {
		return $this->_xml;	
	}			
}

class GoogleSitemapGeneratorDebugEntry extends GoogleSitemapGeneratorXmlEntry {
	
	function Render() {
		return "<!-- " . $this->_xml . " -->";	
	}			
}

/**
 * Base class for all priority providers
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @package sitemap
 * @since 3.0
 */		
class GoogleSitemapGeneratorPrioProviderBase {
	
	/**
	 * @var int $_totalComments The total number of comments of all posts
	 * @access protected
	 */
	var $_totalComments=0;
	
	/**
	 * @var int $_totalComments The total number of posts
	 * @access protected
	 */
	var $_totalPosts=0;
	
	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated name
	*/
	function GetName() {
		return "";
	}
	
	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated description
	*/
	function GetDescription() {
		return "";	
	}
	
	/**
	 * Initializes a new priority provider
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts 
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts) {
		$this->_totalComments=$totalComments;
		$this->_totalPosts=$totalPosts;
		
	}	
	
	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		return 0;
	}	
}

/**
 * Priority Provider which calculates the priority based on the number of comments
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @package sitemap
 * @since 3.0
 */		
class GoogleSitemapGeneratorPrioByCountProvider extends GoogleSitemapGeneratorPrioProviderBase {
	
	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated name
	*/
	function GetName() {
		return __("Comment Count",'sitemap');
	}
	
	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated description
	*/
	function GetDescription() {
		return __("Uses the number of comments of the post to calculate the priority",'sitemap');	
	}
	
	/**
	 * Initializes a new priority provider which calculates the post priority based on the number of comments
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts 
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function GoogleSitemapGeneratorPrioByCountProvider($totalComments,$totalPosts) {
		parent::GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts);	
	}
	
	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		$prio=0;
		if($this->_totalComments>0 && $commentCount>0) {
			$prio = round(($commentCount*100/$this->_totalComments)/100,1);				
		} else {
			$prio = 0;	
		}
		return $prio;
	}			
}

/**
 * Priority Provider which calculates the priority based on the average number of comments
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @package sitemap
 * @since 3.0
 */	
class GoogleSitemapGeneratorPrioByAverageProvider extends GoogleSitemapGeneratorPrioProviderBase {
	
	/**
	 * @var int $_average The average number of comments per post
	 * @access protected
	 */
	var $_average=0.0;
	
	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated name
	*/
	function GetName() {
		return __("Comment Average",'sitemap');
	}
	
	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated description
	*/
	function GetDescription() {
		return __("Uses the average comment count to calculate the priority",'sitemap');	
	}
	
	/**
	 * Initializes a new priority provider which calculates the post priority based on the average number of comments
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts 
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function GoogleSitemapGeneratorPrioByAverageProvider($totalComments,$totalPosts) {
		parent::GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts);
		
		if($this->_totalComments>0 && $this->_totalPosts>0) {
			$this->_average= (double) $this->_totalComments / $this->_totalPosts;
		}
	}
	
	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		$prio = 0;
		//Do not divide by zero!
		if($this->_average==0) {
			if($commentCount>0)	$prio = 1;		
			else $prio = 0;
		} else {
			$prio = $commentCount/$this->_average;	
			if($prio>1) $prio = 1;
			else if($prio<0) $prio = 0;
		}
		
		return round($prio,1);
	}
} 

/**
 * Priority Provider which calculates the priority based on the popularity by the PopularityContest Plugin
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @package sitemap
 * @since 3.0
 */	
class GoogleSitemapGeneratorPrioByPopularityContestProvider extends GoogleSitemapGeneratorPrioProviderBase {
	
	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated name
	*/
	function GetName() {
		return __("Popularity Contest",'sitemap');	
	}
	
	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The translated description
	*/
	function GetDescription() {
		return str_replace("%4","index.php?page=popularity-contest.php",str_replace("%3","options-general.php?page=popularity-contest.php",str_replace("%2","http://www.alexking.org/",str_replace("%1","http://www.alexking.org/index.php?content=software/wordpress/content.php",__("Uses the activated <a href=\"%1\">Popularity Contest Plugin</a> from <a href=\"%2\">Alex King</a>. See <a href=\"%3\">Settings</a> and <a href=\"%4\">Most Popular Posts</a>",'sitemap')))));
	}
	
	/**
	 * Initializes a new priority provider which calculates the post priority based on the popularity by the PopularityContest Plugin
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts 
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function GoogleSitemapGeneratorPrioByPopularityContestProvider($totalComments,$totalPosts) {
		parent::GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts);
	}
	
	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		//$akpc is the global instance of the Popularity Contest Plugin
		global $akpc,$posts;
		
		$res=0;
		//Better check if its there
		if(!empty($akpc) && is_object($akpc)) {
			//Is the method we rely on available?
		if(method_exists($akpc,"get_post_rank")) {
			if(!is_array($posts) || !$posts) $posts = array();
				if(!isset($posts[$postID])) $posts[$postID] = get_post($postID);
				//popresult comes as a percent value
				$popresult=$akpc->get_post_rank($postID);
				if(!empty($popresult) && strpos($popresult,"%")!==false) {
					//We need to parse it to get the priority as an int (percent)
					$matches=null;
					preg_match("/([0-9]{1,3})\%/si",$popresult,$matches);
					if(!empty($matches) && is_array($matches) && count($matches)==2) {
						//Divide it so 100% = 1, 10% = 0.1
						$res=round(intval($matches[1])/100,1);							
					}
				}
			}
		}
		return $res;
	}	
}

/**
 * Class to generate a sitemaps.org Sitemaps compliant sitemap of a WordPress blog.
 * 
 * @package sitemap
 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
 * @since 3.0
*/
class GoogleSitemapGenerator {	
	/**
	 * @var Version of the generator
	*/
	var $_version = "3.0b6";
	
	/**
	 * @var string The full path to the blog directory
	 */
	var $_homePath = "";
	
	/**
	 * @var array The unserialized array with the stored options
	 */
	var $_options = array();
	
	/**
	 * @var array The saved additional pages
	 */
	var $_pages = array();
	
	/**
	 * @var array A list of available freuency names
	 */
	var $_freqNames = array();
	
	/**
	 * @var array A list of class names which my be called for priority calculation
	 */
	var $_prioProviders = array();
	
	/**
	 * @var bool True if init complete (options loaded etc)
	 */
	var $_initiated = false;
	
	/**
	 * @var string Holds the last error if one occurs when writing the files
	 */	
	var $_lastError=null;
	
	/**
	 * @var array Contains the elements of the sitemap
	 */	
	var $_content = array();

	/**
	 * @var int The last handled post ID
	 */		
	var $_lastPostID = 0;
	
	/**
	 * @var bool Defines if the sitemap building process is active at the moment
	 */		
	var $_isActive = false;
	
	/**
	 * Returns the path to the blog directory
	 * 
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The full path to the blog directory
	*/
	function GetHomePath() {
		
		$res="";
		//Check if we are in the admin area -> get_home_path() is avaiable
		if(function_exists("get_home_path")) {
			$res = get_home_path();	
		} else {
			//get_home_path() is not available, but we can't include the admin
			//libraries because many plugins check for the "check_admin_referer"
			//function to detect if you are on an admin page. So we have to copy
			//the get_home_path function in our own...
			$home = get_settings('home');
			$home_path="";
			if ( $home != '' && $home != get_settings('siteurl') ) {
				$home_path = parse_url($home);
				$home_path = $home_path['path'];
				$root = str_replace($_SERVER["PHP_SELF"], '', str_replace("\\","/",$_SERVER["SCRIPT_FILENAME"]));
				$home_path = trailingslashit($root . $home_path);
			} else {
				$home_path = ABSPATH;
			}
			$res = $home_path;
		}
		return $res;
	}
	
	/**
	 * Returns the path to the directory where the plugin file is located
	 * @since 3.0b5
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The path to the plugin directory
	 */
	function GetPluginPath() {
		$path = dirname(__FILE__);
		return trailingslashit(str_replace("\\","/",$path));
	}
	
	/**
	 * Returns the URL to the directory where the plugin file is located
	 * @since 3.0b5
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The URL to the plugin directory
	 */
	function GetPluginUrl() {
		$path = dirname(__FILE__);
		$path = str_replace("\\","/",$path);
		$path = trailingslashit(get_bloginfo('home')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
		return $path;
	}	
	
	/**
	 * Returns the URL to default XSLT style if it exists
	 * @since 3.0b5
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return string The URL to the default stylesheet, empry string if not available.
	 */
	function GetDefaultStyle() {
		$p = $this->GetPluginPath();
		if(file_exists($p . "sitemap.xsl")) {
			return $this->GetPluginUrl() . "sitemap.xsl";	
		}
		return "";
	}
	
	/**
	 * Sets up the default configuration
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function InitOptions() {
		
		$this->_options=array();
		$this->_options["sm_b_prio_provider"]="GoogleSitemapGeneratorPrioByCountProvider";			//Provider for automatic priority calculation
		$this->_options["sm_b_filename"]="sitemap.xml";		//Name of the Sitemap file
		$this->_options["sm_b_debug"]=false;				//Write debug messages in the xml file
		$this->_options["sm_b_xml"]=true;					//Create a .xml file
		$this->_options["sm_b_gzip"]=true;					//Create a gzipped .xml file(.gz) file
		$this->_options["sm_b_ping"]=true;					//Auto ping Google
		$this->_options["sm_b_pingyahoo"]=false;			//Auto ping YAHOO
		$this->_options["sm_b_yahookey"]='';				//YAHOO Application KEy
		$this->_options["sm_b_manual_enabled"]=false;		//Allow manual creation of the sitemap via GET request
		$this->_options["sm_b_auto_enabled"]=true;			//Rebuild sitemap when content is changed
		$this->_options["sm_b_manual_key"]=md5(microtime());//The secret key to build the sitemap via GET request
		$this->_options["sm_b_hide_donors"]=false;			//Hide the list of donations
		$this->_options["sm_b_donated"]=false;				//Did you donate? Thank you! :)
		$this->_options["sm_b_hide_donated"]=false;			//And hide the thank you..
		$this->_options["sm_b_memory"] = '';				//Set Memory Limit (e.g. 16M)
		$this->_options["sm_b_time"] = -1;					//Set time limit in seconds, 0 for unlimited, -1 for disabled
		$this->_options["sm_b_style"] = $this->GetDefaultStyle(); //Include a stylesheet in the XML
		

		$this->_options["sm_b_location_mode"]="auto";		//Mode of location, auto or manual
		$this->_options["sm_b_filename_manual"]="";			//Manuel filename
		$this->_options["sm_b_fileurl_manual"]="";			//Manuel fileurl

		$this->_options["sm_in_home"]=true;					//Include homepage
		$this->_options["sm_in_posts"]=true;				//Include posts
		$this->_options["sm_in_pages"]=true;				//Include static pages
		$this->_options["sm_in_cats"]=true;					//Include categories
		$this->_options["sm_in_arch"]=true;					//Include archives

		$this->_options["sm_cf_home"]="daily";				//Change frequency of the homepage
		$this->_options["sm_cf_posts"]="monthly";			//Change frequency of posts
		$this->_options["sm_cf_pages"]="weekly";			//Change frequency of static pages
		$this->_options["sm_cf_cats"]="weekly";				//Change frequency of categories
		$this->_options["sm_cf_arch_curr"]="daily";			//Change frequency of the current archive (this month)
		$this->_options["sm_cf_arch_old"]="yearly";			//Change frequency of older archives

		$this->_options["sm_pr_home"]=1.0;					//Priority of the homepage
		$this->_options["sm_pr_posts"]=0.6;					//Priority of posts (if auto prio is disabled)
		$this->_options["sm_pr_posts_min"]=0.2;				//Minimum Priority of posts, even if autocalc is enabled
		$this->_options["sm_pr_pages"]=0.6;					//Priority of static pages
		$this->_options["sm_pr_cats"]=0.3;					//Priority of categories
		$this->_options["sm_pr_arch"]=0.3;					//Priority of archives	
	}
	
	/**
	 * Loads the configuration from the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function LoadOptions() {
		
		$this->InitOptions();
		
		//First init default values, then overwrite it with stored values so we can add default
		//values with an update which get stored by the next edit.
		$storedoptions=get_option("sm_options");
		if($storedoptions && is_array($storedoptions)) {
			foreach($storedoptions AS $k=>$v) {
				$this->_options[$k]=$v;	
			}
		} else update_option("sm_options",$this->_options); //First time use, store default values
	}
	
	/**
	 * Initializes a new Google Sitemap Generator
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function GoogleSitemapGenerator() {
		
		$this->_freqNames = array("always", "hourly", "daily", "weekly", "monthly", "yearly","never");
		$this->_prioProviders = array();
		$this->_homePath = $this->GetHomePath();
	}
	
	/**
	 * Returns the version of the generator
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return int The version
	*/
	function GetVersion() {
		return $this->_version;
	}
	
	/**
	 * Returns all parent classes of a class
	 *
	 * @param $className string The name of the class
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return array An array which contains the names of the parent classes
	*/
	function GetParentClasses($classname) {
		$parent = get_parent_class($classname);
		$parents = array();
		if (!empty($parent)) {
			$parents = $this->GetParentClasses($parent);
			$parents[] = strtolower($parent);
		}
		return $parents;
	}
	
	/**
	 * Returns if a class is a subclass of another class
	 *
	 * @param $className string The name of the class
	 * @param $$parentName string The name of the parent class
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return bool true if the given class is a subclass of the other one
	*/
	function IsSubclassOf($className, $parentName) {
		
		$className = strtolower($className);
		$parentName = strtolower($parentName);
		
		if(empty($className) || empty($parentName) || !class_exists($className) || !class_exists($parentName)) return false;
		
		$parents=$this->GetParentClasses($className);
		
		return in_array($parentName,$parents);	
	}
		
	/**
	 * Loads up the configuration and validates the prioity providers
	 *
	 * This method is only called if the sitemaps needs to be build or the admin page is displayed.
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function Initate() {
		if(!$this->_initiated) {
			
			//Loading language file...
			//load_plugin_textdomain('sitemap');
			//Hmm, doesn't work if the plugin file has its own directory.
			//Let's make it our way... load_plugin_textdomain() searches only in the wp-content/plugins dir.
			$currentLocale = get_locale();
			if(!empty($currentLocale)) {
				$moFile = dirname(__FILE__) . "/sitemap-" . $currentLocale . ".mo";
				if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('sitemap', $moFile);
			}
			
			$this->LoadOptions();
			$this->LoadPages();
				
			add_filter("sm_add_prio_provider",array(&$this, 'AddDefaultPrioProviders'));
				
			$r = apply_filters("sm_add_prio_provider",$this->_prioProviders);
			
			if($r != null) $this->_prioProviders = $r;		
				
			$this->ValidatePrioProviders();
			
			$this->_initiated = true;
		}
	}
	
	/**
	 * Returns the instance of the Sitemap Generator
	 *
	 * @since 3.0
	 * @access public
	 * @return GoogleSitemapGenerator The instance or null if not available. 
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function &GetInstance() {
		if(isset($GLOBALS["sm_instance"])) {
			return $GLOBALS["sm_instance"];
		} else return null;
	}
	
	/**
	 * Returns if the sitemap building process is currently active
	 *
	 * @since 3.0
	 * @access public
	 * @return bool true if active
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function IsActive() {
		$inst = &GoogleSitemapGenerator::GetInstance();
		return ($inst != null && $inst->_isActive);
	}
	
	/**
	 * Enables the Google Sitemap Generator and registers the WordPress hooks
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function Enable() {
		
		if(!isset($GLOBALS["sm_instance"])) {			

			$GLOBALS["sm_instance"]=new GoogleSitemapGenerator();

			//Register the sitemap creator to wordpress...
			add_action('admin_menu', array(&$GLOBALS["sm_instance"], 'RegisterAdminPage'));
			
			//Manual Hook via GET
			add_action('init', array(&$GLOBALS["sm_instance"], 'CheckForManualBuild'));

			//Register to various events... @WordPress Dev Team: I wish me a 'public_content_changed' action :)
			
			//If a new post gets saved
			add_action('save_post', array(&$GLOBALS["sm_instance"], 'CheckForAutoBuild'),99,1);

			//Existing post gets edited
			add_action('edit_post', array(&$GLOBALS["sm_instance"], 'CheckForAutoBuild'),99,1); 

			//Existing posts gets deleted
			add_action('delete_post', array(&$GLOBALS["sm_instance"], 'CheckForAutoBuild'),99,1);
			
			//Existing post gets published
			add_action('publish_post', array(&$GLOBALS["sm_instance"], 'CheckForAutoBuild'),99,1); 
		}
	}
	
	/**
	 * Checks if sitemap building after content changed is enabled and rebuild the sitemap
	 *
	 * @param int $postID The ID of the post to handle. Used to avoid double rebuilding if more than one hook was fired.
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function CheckForAutoBuild($postID) {
		$this->Initate();
		if($this->GetOption("b_auto_enabled")===true && $this->_lastPostID != $postID) {
			$this->_lastPostID = $postID;
			$this->BuildSitemap();	
		}
	}
	
	/**
	 * Checks if the rebuild request was send and starts to rebuilt the sitemap
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function CheckForManualBuild() {
		if(!empty($_GET["sm_command"]) && !empty($_GET["sm_key"])) {
			$this->Initate();
			if($this->GetOption("b_manual_enabled")===true && $_GET["sm_command"]=="build" && $_GET["sm_key"]==$this->GetOption("b_manual_key")) {
				$this->BuildSitemap();	
				exit;
			}	
		}
	}
	
	/**
	 * Validates all given Priority Providers by checking them for required methods and existence
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function ValidatePrioProviders() {
		$validProviders=array();
		
		for($i=0; $i<count($this->_prioProviders); $i++) {
			if(class_exists($this->_prioProviders[$i])) {
				if($this->IsSubclassOf($this->_prioProviders[$i],"GoogleSitemapGeneratorPrioProviderBase")) {
					array_push($validProviders,$this->_prioProviders[$i]);
				}
			}
		}
		$this->_prioProviders=$validProviders;
		
		if(!$this->GetOption("b_prio_provider")) {
			if(!in_array($this->GetOption("b_prio_provider"),$this->_prioProviders,true)) {
				$this->SetOption("b_prio_provider","");	
			}
		}
	}

	/**
	 * Adds the default Priority Providers to the provider list
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function AddDefaultPrioProviders($providers) {
		array_push($providers,"GoogleSitemapGeneratorPrioByCountProvider");
		array_push($providers,"GoogleSitemapGeneratorPrioByAverageProvider");
		if(class_exists("ak_popularity_contest")) {
			array_push($providers,"GoogleSitemapGeneratorPrioByPopularityContestProvider");	
		}
		return $providers;	
	}
	
	/**
	 * Loads the stored pages from the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	*/
	function LoadPages() {
		global $wpdb;
		
		$needsUpdate=false;
		
		$pagesString=$wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'sm_cpages'");
		
		//Class sm_page was renamed with 3.0 -> rename it in serialized value for compatibility
		if(!empty($pagesString) && strpos($pagesString,"sm_page")!==false) {
			$pagesString = str_replace("O:7:\"sm_page\"","O:26:\"GoogleSitemapGeneratorPage\"",$pagesString);
			$needsUpdate=true;
		}
		
		if(!empty($pagesString)) {
			$storedpages=unserialize($pagesString);
			$this->_pages=$storedpages;
		} else {
			$this->_pages=array();
			//Add the option, Note the autoload=false because when the autoload happens, our class GoogleSitemapGeneratorPage doesn't exist
			add_option("sm_cpages",$this->_pages,"Storage for custom pages of the sitemap plugin",'no');	
		}	
		
		if($needsUpdate) {
			$this->SavePages();
		}
	}
	
	/**
	 * Saved the additional pages back to the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return true on success
	*/
	function SavePages() {
		$oldvalue = get_option("sm_cpages");
		if($oldvalue == $this->_pages) {
			return true;
		} else 	return update_option("sm_cpages",$this->_pages);
	}
	
	
	/**
	 * Returns the URL for the sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The URL to the Sitemap file
	*/
	function GetXmlUrl($forceAuto=false) {
		
		if(!$forceAuto && $this->GetOption("b_location_mode")=="manual") {
			return $this->GetOption("b_fileurl_manual");
		} else {
			return trailingslashit(get_bloginfo('siteurl')). $this->GetOption("b_filename");
		}
	}

	/**
	 * Returns the URL for the gzipped sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The URL to the gzipped Sitemap file
	*/
	function GetZipUrl($forceAuto=false) {
		return $this->GetXmlUrl($forceAuto) . ".gz";	
	}
	
	/**
	 * Returns the file system path to the sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The file system path;
	*/
	function GetXmlPath($forceAuto=false) {		
		if(!$forceAuto && $this->GetOption("b_location_mode")=="manual") {
			return $this->GetOption("b_filename_manual");		
		} else {
			return $this->GetHomePath()  . $this->GetOption("b_filename");
		}
	}
	
	/**
	 * Returns the file system path to the gzipped sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The file system path;
	*/
	function GetZipPath($forceAuto=false) {
		return $this->GetXmlPath($forceAuto) . ".gz";	
	}
	
	/**
	 * Returns the option value for the given key
	 * Alias for getOption
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $key string The Configuration Key
	 * @return mixed The value
	*/
	function Go($key) {
		return $this->getOption($key);
	}

	/**
	 * Returns the option value for the given key
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $key string The Configuration Key
	 * @return mixed The value
	 */
	function GetOption($key) {
		if(strpos($key,"sm_")!==0) $key="sm_" . $key;
		if(array_key_exists($key,$this->_options)) {
			return $this->_options[$key];	
		} else return null;
	}
	
	/**
	 * Sets an option to a new value
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $key string The configuration key
	 * @param $value mixed The new object
	 */
	function SetOption($key,$value) {
		if(strstr($key,"sm_")!==0) $key="sm_" . $key;
		
		$this->_options[$key]=$value;	
	}
	
	/**
	 * Saves the options back to the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return bool true on success
	 */
	function SaveOptions() {
		$oldvalue = get_option("sm_options");
		if($oldvalue == $this->_options) {
			return true;	
		} else return update_option("sm_options",$this->_options);		
	}
	
	/**
	 * Retrieves the number of comments of a post in a asso. array
	 * The key is the postID, the value the number of comments
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return array An array with postIDs and their comment count
	 */
	function GetComments() {
		global $wpdb;
		$comments=array();

		//Query comments and add them into the array
		$commentRes=$wpdb->get_results("SELECT `comment_post_ID` as `post_id`, COUNT(comment_ID) as `comment_count` FROM `" . $wpdb->comments . "` WHERE `comment_approved`='1' GROUP BY `comment_post_ID`");
		if($commentRes) {
			foreach($commentRes as $comment) {
				$comments[$comment->post_id]=$comment->comment_count;
			}	
		}
		return $comments;
	}
	
	/**
	 * Calculates the full number of comments from an sm_getComments() generated array
	 * 
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>	
	 * @param $comments array The Array with posts and c0mment count
	 * @see sm_getComments
	 * @return The full number of comments
	 */ 
	function GetCommentCount($comments) {
		$commentCount=0;
		foreach($comments AS $k=>$v) {
			$commentCount+=$v;	
		}	
		return $commentCount;
	}	
	
	/**
	 * Removes an element of an array and reorders the indexes
	 * 
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param array $array The array with the values
	 * @param object $indice The key which vallue should be removed
	 * @return array The modified array
	 */
	function ArrayRemove ($array, $indice) {
		if (array_key_exists($indice, $array)) {
			$temp = $array[0];
			$array[0] = $array[$indice];
			$array[$indice] = $temp;
			array_shift($array);

			for ($i = 0 ; $i < $indice ; $i++)
			{
				$dummy = $array[$i];
				$array[$i] = $temp;
				$temp = $dummy;
			}
		}
		return $array;
	} 
	
	/**
	 * Adds a url to the sitemap. You can use this method or call AddElement directly.
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>  
	 * @param $loc string The location (url) of the page
	 * @param $lastMod int The last Modification time as a UNIX timestamp
	 * @param $changeFreq string The change frequenty of the page, Valid values are "always", "hourly", "daily", "weekly", "monthly", "yearly" and "never".
	 * @param $priorty float The priority of the page, between 0.0 and 1.0
	 * @see AddElement
	 * @return string The URL node
	 */
	function AddUrl($loc,$lastMod=0,$changeFreq="monthly",$priority=0.5) {
		$page = new GoogleSitemapGeneratorPage($loc,$priority,$changeFreq,$lastMod);
		
		$this->AddElement($page);
	}
	
	/**
	 * Adds an element to the sitemap
	 * 
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $page The element
	 */
	function AddElement(&$page) {
		if(empty($page)) return;
		
		$this->_content[] = $page;
	}
	
	/**
	 * Checks if a file is writable and tries to make it if not.
	 * 
	 * @since 3.05b
	 * @access private
	 * @author  VJTD3 <http://www.VJTD3.com>
	 * @return bool true if writable
	 */
	function IsFileWritable($filename) {	
		//can we write?
		if(!is_writable($filename)) {
			//no we can't.
			if(!@chmod($filename, 0666)) {
				$pathtofilename = dirname($filename);
				//Lets check if parent directory is writable.
				if(!is_writable($pathtofilename)) {
					//it's not writeable too.
					if(!@chmod($pathtoffilename, 0666)) {
						//darn couldn't fix up parrent directory this hosting is foobar.
						//be a good programmer and cleanup your mess.
						unset($pathtofilename);
						//Lets error because of the permissions problems.
						return false;
					} else {
						//Delete the file so the script can create it
						if(!@unlink($filename)) return false;
					}
				}
			}
		}
		//we can write, return 1/true/happy dance.
		return true;
	}
	
	/**
	 * Builds the sitemap and writes it into a xml file.
	 * 
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return array An array with messages such as failed writes etc.
	 */
	function BuildSitemap() {
		
		global $wpdb, $posts, $wp_version;	
		$this->Initate();
		
		if($this->GetOption("b_memory")!='') {
			@ini_set("memory_limit",$this->GetOption("b_memory"));	
		}
		
		if($this->GetOption("sm_b_time")!=-1) {
			@set_time_limit($this->GetOption("sm_b_time"));				
		}		
		
		//This object saves the status information of the script directly to the database
		$status = new GoogleSitemapGeneratorStatus();
		
		//Other plugins can detect if the building process is active
		$this->_isActive = true;
		
		//$this->AddElement(new GoogleSitemapGeneratorXmlEntry());
		
		//Return messages to the user in frontend
		$messages=array();
		
		//Debug mode?
		$debug=$this->GetOption("b_debug");
		
		//Content of the XML file
		$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<?xml version="1.0" encoding="UTF-8"' . '?' . '>'));
		
		if($this->GetOption("b_style")!='') {
			$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<' . '?xml-stylesheet type="text/xsl" href="' . $this->GetOption("b_style") . '"?' . '>'));	
		}
		
		//WordPress powered... and me! :D
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("generator=\"wordpress/" . get_bloginfo('version') . "\""));
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("sitemap-generator-url=\"http://www.arnebrachhold.de\" sitemap-generator-version=\"" . $this->GetVersion() . "\""));
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("generated-on=\"" . date(get_option("date_format") . " " . get_option("time_format")) . "\""));
		
		//All comments as an asso. Array (postID=>commentCount)
		$comments=($this->GetOption("b_prio_provider")!=""?$this->GetComments():array());
		
		//Full number of comments
		$commentCount=(count($comments)>0?$this->GetCommentCount($comments):0);
		
		if($debug && $this->GetOption("b_prio_provider")!="") {
			$this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Total comment count: " . $commentCount));	
		}
		
		//Go XML!
		$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/09/sitemap.xsd"	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'));
		
		//Add the home page (WITH a slash!)
		if($this->GetOption("in_home")) {
			$this->AddUrl(trailingslashit(get_bloginfo('url')),$this->GetTimestampFromMySql(get_lastpostmodified('GMT')),$this->GetOption("cf_home"),$this->GetOption("pr_home"));
		}
		
		//Add the posts
		if($this->GetOption("in_posts") || $this->GetOption("in_pages")) {
			
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Postings"));
		
			//Pre 2.1 compatibility. 2.1 introduced 'future' as post_status so we don't need to check post_date
			$wpCompat = (floatval($wp_version) < 2.1);
			
			$sql="SELECT `ID`, `post_author`, `post_date`, `post_status`, `post_name`, `post_modified`, `post_parent`, `post_type` FROM `" . $wpdb->posts . "` WHERE (";
			
			if($this->GetOption('in_posts')) {
				if($wpCompat) $sql.="(post_status = 'publish' AND post_date_gmt <= '" . gmdate('Y-m-d H:i:59') . "')";
				else $sql.=" post_status = 'publish' ";
			}
			
			if($this->GetOption('in_pages')) {
				if($this->GetOption('in_posts')) {
					$sql.=" OR ";	
				}
				$sql.=" post_status='static' ";
			}
			
			$sql.=") ";
			
			$sql.=" AND post_password='' ORDER BY post_modified DESC";
			
			//Retrieve all posts and static pages (if enabled)
			$postRes=$wpdb->get_results($sql);
			
			if($postRes) {
				
				//Count of all posts
				$postCount=count($postRes);
				
				//#type $prioProvider GoogleSitemapGeneratorPrioProviderBase
				$prioProvider=NULL;
				
				if($this->GetOption("b_prio_provider") != '') {
					$providerClass=$this->GetOption('b_prio_provider');
					$prioProvider = new $providerClass($commentCount,$postCount);
				}
				
				//$posts is used by Alex King's Popularity Contest plugin
				if($posts == null || !is_array($posts)) {
					$posts = &$postRes;	
				}
				
				$z = 1;
				$zz = 1;
				
				//Default priorities
				$default_prio_posts = $this->GetOption('pr_posts');
				$default_prio_pages = $this->GetOption('pr_pages');
				
				//Change frequencies
				$cf_pages = $this->GetOption('sm_cf_pages');
				$cf_posts = $this->GetOption('sm_cf_posts');
				
				$minPrio=$this->GetOption('pr_posts_min');
				
				//Fill the cache with our DB result. Since it's incomplete (no text-content for example), we will clean it later.
				update_post_cache($postRes);

				//Cycle through all posts and add them
				foreach($postRes as $post) {
				
					//Set the current working post
					$GLOBALS['post'] = &$post;
				
					//Default Priority if auto calc is disabled
					$prio = 0;
					
					if($post->post_status=='static') {
						//Priority for static pages
						$prio = $default_prio_pages;
					} else {
						//Priority for normal posts
						$prio = $default_prio_posts;
					}
					
					//If priority calc. is enabled, calculate (but only for posts, not pages)!
					if($prioProvider !== null && $post->post_status != 'static') {

						//Comment count for this post
						$cmtcnt = (isset($comments[$post->ID])?$comments[$post->ID]:0);
						$prio = $prioProvider->GetPostPriority($post->ID,$cmtcnt);

						if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry('Debug: Priority report of postID ' . $post->ID . ': Comments: ' . $cmtcnt . ' of ' . $commentCount . ' = ' . $prio . ' points'));
					}	
					
					if($post->post_status != 'static' && $minPrio>0 && $prio<$minPrio) {
						$prio = $minPrio;
					}
					
					//Add it
					$this->AddUrl(get_permalink($post->ID),$this->GetTimestampFromMySql(($post->post_modified && $post->post_modified!='0000-00-00 00:00:00'?$post->post_modified:$post->post_date)),($post->post_status=='static'?$cf_pages:$cf_posts),$prio);
					
					//Update the status every 100 posts and at the end. 
					//If the script breaks because of memory or time limit, 
					//we have a "last reponded" value which can be compared to the server settings
					if($zz==100 || $z == $postCount) {
						$status->SaveStep($z);
						$zz=0;						
					} else $zz++;
					
					$z++;
					
					//Clean cache because it's incomplete
					clean_post_cache($post->ID);
				}
				unset($postRes);
				unset($prioProvider);
				
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Postings"));
		}
		
		//Add the cats
		if($this->GetOption("in_cats")) {
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Cats"));
			
			$catsRes=$wpdb->get_results("SELECT cat_ID AS ID, MAX(post_modified) AS last_mod FROM `" . $wpdb->posts . "` p LEFT JOIN `" . $wpdb->post2cat . "` pc ON p.ID = pc.post_id LEFT JOIN `" . $wpdb->categories . "` c ON pc.category_id = c.cat_ID WHERE post_status = 'publish' GROUP BY cat_ID");
			if($catsRes) {
				foreach($catsRes as $cat) {
					if($cat && $cat->ID && $cat->ID>0) {
						if($debug) if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Cat-ID:" . $cat->ID)); 	
						$this->AddUrl(get_category_link($cat->ID),$this->GetTimestampFromMySql($cat->last_mod),$this->GetOption("cf_cats"),$this->GetOption("pr_cats"));
					}
				}	
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Cats"));	
		}
		
		//Add the archives
		if($this->GetOption("in_arch")) {
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Archive"));
			$now = current_time('mysql');

			$arcresults = $wpdb->get_results("SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, MAX(post_date) as last_mod, count(ID) as posts FROM $wpdb->posts WHERE post_date < '$now' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC");
			if ($arcresults) {
				foreach ($arcresults as $arcresult) {
					
					$url  = get_month_link($arcresult->year,   $arcresult->month);
					$changeFreq="";
					
					//Archive is the current one
					if($arcresult->month==date("n") && $arcresult->year==date("Y")) {
						$changeFreq=$this->GetOption("cf_arch_curr");	
					} else { // Archive is older
						$changeFreq=$this->GetOption("cf_arch_old");	
					}
					
					$this->AddUrl($url,$this->GetTimestampFromMySql($arcresult->last_mod),$changeFreq,$this->GetOption("pr_arch"));				
				}
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Archive")); 	
		}
		
		//Add the custom pages
		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Custom Pages"));
		if($this->_pages && is_array($this->_pages) && count($this->_pages)>0) {
			//#type $page GoogleSitemapGeneratorPage
			foreach($this->_pages AS $page) {
				$this->AddUrl($page->GetUrl(),$page->getLastMod(),$page->getChangeFreq(),$page->getPriority());
			}	
		}
		
		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Custom Pages"));
		
		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start additional URLs"));
		
		do_action("sm_buildmap");
		
		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End additional URLs"));
		
		$this->AddElement(new GoogleSitemapGeneratorXmlEntry("</urlset>"));
		
		$s='';
		$c = count($this->_content);
		for($i =0; $i<$c; $i++) {
			$s.=$this->_content[$i]->Render() . "\n";	
		}
		
		$pingUrl='';
		
		$oldHandler = set_error_handler(array(&$this,"TrackError"));
		
		//Write normal sitemap file
		if($this->GetOption("b_xml")) {
			$fileName = $this->GetXmlPath();
			$status->StartXml($this->GetXmlPath(),$this->GetXmlUrl());
			if($this->IsFileWritable($fileName)) {
				if(file_put_contents($fileName,$s)) {
					$pingUrl=$this->GetXmlUrl();
					$status->EndXml(true);
				}  else {
					$status->EndXml(false,$this->_lastError);	
				}			
			} else $status->EndXml(false,"nt writable");
		}
		
		//Write gzipped sitemap file
		if($this->GetOption("b_gzip")===true && function_exists("gzencode")) {
			$fileName = $this->GetZipPath();
			$status->StartZip($this->GetZipPath(),$this->GetZipUrl());
			if($this->IsFileWritable($fileName)) {
				if(file_put_contents($fileName,gzencode($s))) {
					$pingUrl=$this->GetZipUrl();
					$status->EndZip(true);
				}  else {
					$status->EndZip(false,$this->_lastError);	
				}			
			} else $status->EndZip(false,"nt writable");	
		}
		
		//Ping Google
		if($this->GetOption("b_ping") && !empty($pingUrl)) {
			$status->StartGooglePing();
			$pingUrl="http://www.google.com/webmasters/sitemaps/ping?sitemap=" . urlencode($pingUrl);
			$pingres=@wp_remote_fopen($pingUrl);
									  
			if($pingres==NULL || $pingres===false) {
				$status->EndGooglePing(false,$this->_lastError);
			} else {
				$status->EndGooglePing(true);
			}
		}
		
		//Ping YAHOO
		if($this->GetOption("sm_b_pingyahoo")===true && $this->GetOption("sm_b_yahookey")!="" && !empty($pingUrl)) {
			$status->StartYahooPing();
			$pingUrl="http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=" . $this->GetOption("sm_b_yahookey") . "&url=" . urlencode($pingUrl);
			$pingres=@wp_remote_fopen($pingUrl);

			if($pingres==NULL || $pingres===false || stripos($pingres,"success")===false) {
				$status->EndYahooPing(false,$this->_lastError);
			} else {
				$status->EndYahooPing(true);
			}	
		}
		
		if($oldHandler!==null) restore_error_handler();	
		
		$status->End();	
		$this->_isActive = false;	

		//done...
		return $messages;
	}
	
	/**
	 * Tracks the last error (gets called by PHP)
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 */
	function TrackError($log_level, $log_text, $error_file, $error_line) {
		$this->_lastError = $log_text;		
	}
	
	/**
	 * Adds the options page in the admin menu
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 */
	function RegisterAdminPage() {
		if (function_exists('add_options_page')) {
			add_options_page(__('Sitemap Generator','sitemap'), __('Sitemap','sitemap'), 8, basename(__FILE__), array(&$this,'HtmlShowOptionsPage'));	
		}
	}
	
	/**
	 * Echos option fields for an select field containing the valid change frequencies
	 * 
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $currentVal The value which should be selected
	 * @return all valid change frequencies as html option fields 
	 */
	function HtmlGetFreqNames($currentVal) {
		foreach($this->_freqNames AS $v) {
			echo "<option value=\"$v\" " . $this->HtmlGetSelected($v,$currentVal) .">";
			echo ucfirst(__($v,'sitemap'));
			echo "</option>";	
		}
	}
	
	/**
	 * Echos option fields for an select field containing the valid priorities (0- 1.0)
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $currentVal string The value which should be selected
	 * @return 0.0 - 1.0 as html option fields 
	 */
	function HtmlGetPriorityValues($currentVal) {
		$currentVal=(float) $currentVal;
		for($i=0.0; $i<=1.0; $i+=0.1) {
			echo "<option value=\"$i\" " . $this->HtmlGetSelected("$i","$currentVal") .">";
			_e(strval($i));
			echo "</option>";	
		}	
	}
	
	/**
	 * Returns the checked attribute if the given values match
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $val string The current value
	 * @param $equals string The value to match
	 * @return The checked attribute if the given values match, an empty string if not
	 */
	function HtmlGetChecked($val,$equals) {
		if($val==$equals) return $this->HtmlGetAttribute("checked");
		else return "";		
	}
	
	/**
	 * Returns the selected attribute if the given values match
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $val string The current value
	 * @param $equals string The value to match
	 * @return The selected attribute if the given values match, an empty string if not
	 */
	function HtmlGetSelected($val,$equals) {
		if($val==$equals) return $this->HtmlGetAttribute("selected");
		else return "";		
	}
	
	/**
	 * Returns an formatted attribute. If the value is NULL, the name will be used.
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @param $attr string The attribute name
	 * @param $value string The attribute value
	 * @return The formatted attribute
	 */
	function HtmlGetAttribute($attr,$value=NULL) {
		if($value==NULL) $value=$attr;
		return " " . $attr . "=\"" . $value . "\" ";	
	}
	
	/**
	 * Returns an array with GoogleSitemapGeneratorPage objects which is generated from POST values
	 *
	 * @since 3.0
	 * @see GoogleSitemapGeneratorPage
	 * @access private
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return array An array with GoogleSitemapGeneratorPage objects
	 */
	function HtmlApplyPages() {
		// Array with all page URLs
		$pages_ur=(!isset($_POST["sm_pages_ur"]) || !is_array($_POST["sm_pages_ur"])?array():$_POST["sm_pages_ur"]);
		
		//Array with all priorities
		$pages_pr=(!isset($_POST["sm_pages_pr"]) || !is_array($_POST["sm_pages_pr"])?array():$_POST["sm_pages_pr"]);
		
		//Array with all change frequencies
		$pages_cf=(!isset($_POST["sm_pages_cf"]) || !is_array($_POST["sm_pages_cf"])?array():$_POST["sm_pages_cf"]);
		
		//Array with all lastmods
		$pages_lm=(!isset($_POST["sm_pages_lm"]) || !is_array($_POST["sm_pages_lm"])?array():$_POST["sm_pages_lm"]);

		//Array where the new pages are stored
		$pages=array();
		
		//Loop through all defined pages and set their properties into an object
		if(isset($_POST["sm_pages_mark"]) && is_array($_POST["sm_pages_mark"])) {
			for($i=0; $i<count($_POST["sm_pages_mark"]); $i++) {
				//Create new object
				$p=new GoogleSitemapGeneratorPage();
				if(substr($pages_ur[$i],0,4)=="www.") $pages_ur[$i]="http://" . $pages_ur[$i];
				$p->SetUrl($pages_ur[$i]);
				$p->SetProprity($pages_pr[$i]);
				$p->SetChangeFreq($pages_cf[$i]);
				//Try to parse last modified, if -1 (note ===) automatic will be used (0)
				$lm=(!empty($pages_lm[$i])?strtotime($pages_lm[$i],time()):-1);
				if($lm===-1) $p->setLastMod(0);
				else $p->setLastMod($lm);
				
				//Add it to the array
				array_push($pages,$p);
			}					
		}	
		return $pages;
	}
	
	function GetTimestampFromMySql($mysqlDateTime) {
		list($date, $hours) = split(' ', $mysqlDateTime);
		list($year,$month,$day) = split('-',$date);
		list($hour,$min,$sec) = split(':',$hours);
		return mktime($hour, $min, $sec, $month, $day, $year);
	}

	
	function GetResourceLink($resourceID) {
		return trailingslashit(get_bloginfo('siteurl')) . '?res=' . $resourceID;
	}
	
	function GetRedirectLink($redir) {
		return trailingslashit("http://www.arnebrachhold.de/redir/" . $redir);
	}
	
	function GetBackLink() {
		$page = basename(__FILE__);
		if(isset($_GET['page']) && !empty($_GET['page'])) {
			$page = preg_replace('[^a-zA-Z0-9\.\_\-]','',$_GET['page']);
		}
		return $_SERVER['PHP_SELF'] . "?page=" .  $page;	
	}
	
	/**
	 * Displays the option page
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 */
	function HtmlShowOptionsPage() {
		
		$this->Initate();
			
		//All output should go in this var which get printed at the end
		$message="";
		
		if(isset($_GET['sm_donated'])) {
			$this->SetOption('b_donated',true);
			$this->SaveOptions();	
		}
		if(isset($_GET['sm_hidedonate'])) {
			$this->SetOption('b_hide_donated',true);
			$this->SaveOptions();	
		}
		
		if(isset($_GET['sm_hidedonors'])) {
			$this->SetOption('b_hide_donors',true);
			$this->SaveOptions();	
		}
		
		if(isset($_GET['sm_donated']) || ($this->GetOption('b_donated')===true && $this->GetOption('b_hide_donated')!==true)) {
			?>
			<div class="updated">
				<strong><p><?php _e('Thank you very much for your donation. You help me to continue support and development of this plugin and other free software!','sitemap'); ?> <a href="<?php echo $this->GetBackLink() . "&amp;sm_hidedonate=true"; ?>"><small style="font-weight:normal;"><?php _e('Hide this notice', 'sitemap'); ?></small></a></p></strong>
			</div>
			<?php	
		}
		
		if(!empty($_REQUEST["sm_rebuild"])) { //Pressed Button: Rebuild Sitemap
			$msg = $this->BuildSitemap();
			header("location: " . $this->GetBackLink());
			exit;
		} else if (!empty($_POST['sm_update'])) { //Pressed Button: Update Config	
		
			foreach($this->_options as $k=>$v) {
				//Check vor values and convert them into their types, based on the category they are in
				if(!isset($_POST[$k])) $_POST[$k]=""; // Empty string will get false on 2bool and 0 on 2float
				//Options of the category "Basic Settings" are boolean, except the filename and the autoprio provider
				if(substr($k,0,5)=="sm_b_") {					
					if($k=="sm_b_filename" || $k=="sm_b_fileurl_manual" || $k=="sm_b_filename_manual" || $k=="sm_b_prio_provider" || $k=="sm_b_manual_key" || $k == "sm_b_yahookey" || $k == "sm_b_style" || $k == "sm_b_memory") {
						if($k=="sm_b_filename_manual" && strpos($_POST[$k],"\\")!==false){
							$_POST[$k]=stripslashes($_POST[$k]);
						}
						$this->_options[$k]=(string) $_POST[$k];
					} else if($k=="sm_b_location_mode") {
						$tmp=(string) $_POST[$k];
						$tmp=strtolower($tmp);
						if($tmp=="auto" || $tmp="manual") $this->_options[$k]=$tmp;
						else $this->_options[$k]="auto";								
					} else if($k == "sm_b_time") {
						if($_POST[$k]=='') $_POST[$k] = -1;
						$this->_options[$k] = intval($_POST[$k]);			
					} else {
						$this->_options[$k]=(bool) $_POST[$k];	
					}
				//Options of the category "Includes" are boolean
				} else if(substr($k,0,6)=="sm_in_") {
					$this->_options[$k]=(bool) $_POST[$k];		
				//Options of the category "Change frequencies" are string
				} else if(substr($k,0,6)=="sm_cf_") {
					$this->_options[$k]=(string) $_POST[$k];		
				//Options of the category "Priorities" are float
				} else if(substr($k,0,6)=="sm_pr_") {
						$this->_options[$k]=(float) $_POST[$k];		
				}
			}
			
			//Apply page changes from POST
			$this->_pages=$this->HtmlApplyPages();
			
			if($this->SaveOptions()) $message.=__('Configuration updated', 'sitemap') . "<br />";
			else $message.=__('Error while saving options', 'sitemap') . "<br />";
			
			if($this->SavePages()) $message.=__("Pages saved",'sitemap') . "<br />";
			else $message.=__('Error while saving pages', 'sitemap'). "<br />";
			
		} else if(!empty($_POST["sm_reset_config"])) { //Pressed Button: Reset Config
			$this->InitOptions();
			$this->SaveOptions();
			
			$message.=__('The default configuration was restored.','sitemap');
		}
		
		//Print out the message to the user, if any
		if($message!="") {
			?>
			<div class="updated"><strong><p><?php
			echo $message;
			?></p></strong></div><?php
		}
		?>
				
		<style type="text/css">
		
		li.sm_hint {
			color:green;
		}
		
		li.sm_optimize {
			color:orange;
		}
		
		li.sm_error {
			color:red;
		}
		
		input.sm_warning:hover {
			background: #ce0000;
			color: #fff;
		}
		
		a.sm_button {
			padding:4px;
			display:block;
			padding-left:25px;
			background-repeat:no-repeat;
			background-position:5px 50%;	
			text-decoration:none;
			border:none;
		}
		
		a.sm_button:hover {
			border-bottom-width:1px;
		}

		

		a.sm_donatePayPal {
			background-image:url(<?php echo $this->GetResourceLink("{8C0BAD8C-77FA-4842-956E-CDEF7635F2C7}"); ?>);
		}
		
		a.sm_donateAmazon {
			background-image:url(<?php echo $this->GetResourceLink("{9866EAFC-3F85-44df-8A72-4CD1566E2D4F}"); ?>);
		}
		
		a.sm_pluginHome {
			background-image:url(<?php echo $this->GetResourceLink("{AD59B831-BF3D-49b1-A649-9DD8EDA1798A}"); ?>);
		}
		
		a.sm_pluginList {
			background-image:url(<?php echo $this->GetResourceLink("{FFA3E2B1-D2B1-4c66-B8A4-5F6E7D8781F2}"); ?>);
		}
		
		a.sm_pluginSupport {
			background-image:url(<?php echo $this->GetResourceLink("{234C74C9-3DF4-4ae2-A12E-C157C67059D8}"); ?>);	
		}
		
		a.sm_resGoogle {
			background-image:url(<?php echo $this->GetResourceLink("{7E5622AF-0DE3-4e43-99F9-33EC61308376}"); ?>);	
		}
		
		a.sm_resYahoo {
			background-image:url(<?php echo $this->GetResourceLink("{BC853F21-410E-47ff-BB6D-2B89C9D7E76B}"); ?>);	
		}

		</style>
		
		<div class="wrap" id="sm_div">
			<form method="post" action="<?php echo $this->GetBackLink() ?>">
				<h2><?php _e('XML Sitemap Generator for WordPress', 'sitemap'); echo " " . $this->GetVersion() ?> </h2>
				
				<script type="text/javascript" src="../wp-includes/js/tw-sack.js"></script>
				<script type="text/javascript" src="list-manipulation.js"></script>
				<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
				<script type="text/javascript">
				//<![CDATA[
				addLoadEvent( function() {
					var manager = new dbxManager('sm_sitemap_meta_33');
					
					//create new docking boxes group
					var meta = new dbxGroup(
						'grabit', 		// container ID [/-_a-zA-Z0-9/]
						'vertical', 	// orientation ['vertical'|'horizontal']
						'10', 			// drag threshold ['n' pixels]
						'no',			// restrict drag movement to container axis ['yes'|'no']
						'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
						'yes', 			// include open/close toggle buttons ['yes'|'no']
						'open', 		// default state ['open'|'closed']
						<?php echo "'" . js_escape(__('open')); ?>', 		// word for "open", as in "open this box"
						<?php echo "'" . js_escape(__('close')); ?>', 		// word for "close", as in "close this box"
						<?php echo "'" . js_escape(__('click-down and drag to move this box')); ?>', // sentence for "move this box" by mouse
						<?php echo "'" . js_escape(__('click to %toggle% this box')); ?>', // pattern-match sentence for "(open|close) this box" by mouse
						<?php echo "'" . js_escape(__('use the arrow keys to move this box')); ?>', // sentence for "move this box" by keyboard
						<?php echo "'" . js_escape(__(', or press the enter key to %toggle% it')); ?>',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
						'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
						);

					var advanced = new dbxGroup(
						'advancedstuff', 		// container ID [/-_a-zA-Z0-9/]
						'vertical', 		// orientation ['vertical'|'horizontal']
						'10', 			// drag threshold ['n' pixels]
						'yes',			// restrict drag movement to container axis ['yes'|'no']
						'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
						'yes', 			// include open/close toggle buttons ['yes'|'no']
						'open', 		// default state ['open'|'closed']
						<?php echo "'" . js_escape(__('open')); ?>', 		// word for "open", as in "open this box"
						<?php echo "'" . js_escape(__('close')); ?>', 		// word for "close", as in "close this box"
						<?php echo "'" . js_escape(__('click-down and drag to move this box')); ?>', // sentence for "move this box" by mouse
						<?php echo "'" . js_escape(__('click to %toggle% this box')); ?>', // pattern-match sentence for "(open|close) this box" by mouse
						<?php echo "'" . js_escape(__('use the arrow keys to move this box')); ?>', // sentence for "move this box" by keyboard
						<?php echo "'" . js_escape(__(', or press the enter key to %toggle% it')); ?>',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
						'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
						);
				});
				//]]>
				</script>

				<div id="poststuff">
					<div id="moremeta">
						<div id="grabit" class="dbx-group">
							<fieldset id="sm_pnres" class="dbx-box">
								<h3 class="dbx-handle"><?php _e('About this Plugin:','sitemap'); ?></h3>
								<div class="dbx-content">
									<a class="sm_button sm_pluginHome"    href="<?php echo $this->GetRedirectLink('sitemap-home'); ?>">Plugin Homepage</a>
									<a class="sm_button sm_pluginList"    href="<?php echo $this->GetRedirectLink('sitemap-list'); ?>">Notify List</a>
									<a class="sm_button sm_pluginSupport" href="<?php echo $this->GetRedirectLink('sitemap-support'); ?>">Support Forum</a>
									<a class="sm_button sm_donatePayPal"  href="<?php echo $this->GetRedirectLink('sitemap-paypal'); ?>">Donate with PayPal</a>
									<a class="sm_button sm_donateAmazon"  href="<?php echo $this->GetRedirectLink('sitemap-amazon'); ?>">My Amazon Wish List</a>
									<?php if(__('translator_name','sitemap')!='translator_name') {?><a class="sm_button sm_pluginSupport" href="<?php _e('translator_url'); ?>"><?php _e('translator_name'); ?></a><?php } ?>
								</div>
							</fieldset>
							<fieldset id="sm_smres" class="dbx-box">
								<h3 class="dbx-handle"><?php _e('Sitemap Resources:','sitemap'); ?></h3>
								<div class="dbx-content">
									<a class="sm_button sm_resGoogle"    href="<?php echo $this->GetRedirectLink('sitemap-gwt'); ?>"><?php _e('Webmaster Tools','sitemap'); ?></a>
									<a class="sm_button sm_resGoogle"    href="<?php echo $this->GetRedirectLink('sitemap-gwb'); ?>"><?php _e('Webmaster Blog','sitemap'); ?></a>
									
									<a class="sm_button sm_resYahoo"     href="<?php echo $this->GetRedirectLink('sitemap-yse'); ?>"><?php _e('Site Explorer','sitemap'); ?></a>
									<a class="sm_button sm_resYahoo"     href="<?php echo $this->GetRedirectLink('sitemap-ywb'); ?>"><?php _e('Search Blog','sitemap'); ?></a>
									<br />
									<a class="sm_button sm_resGoogle"    href="<?php echo $this->GetRedirectLink('sitemap-prot'); ?>"><?php _e('Sitemaps Protocol','sitemap'); ?></a>
									<a class="sm_button sm_resGoogle"    href="<?php echo $this->GetRedirectLink('sitemap-ofaq'); ?>"><?php _e('Official Sitemaps FAQ','sitemap'); ?></a>
									<a class="sm_button sm_pluginHome"   href="<?php echo $this->GetRedirectLink('sitemap-afaq'); ?>"><?php _e('My Sitemaps FAQ','sitemap'); ?></a>
								</div>
							</fieldset>
									
							<fieldset id="dm_donations" class="dbx-box">
								<h3 class="dbx-handle"><?php _e('Recent Donations:','siteinfo'); ?></h3>
								<div class="dbx-content">
									<?php if($this->GetOption('b_hide_donors')!==true) { ?>
										<iframe border="0" frameborder="0" scrolling="no" allowtransparency="yes" style="width:100%; height:60px;" src="<?php echo $this->GetRedirectLink('sitemap-donorlist'); ?>">
										List of the donors
										</iframe><br />
										<a href="<?php echo $this->GetBackLink() . "&amp;sm_hidedonors=true"; ?>"><small><?php _e('Hide this list','sitemap'); ?></small></a><br /><br />
									<?php } ?>
									<a style="float:left; margin-right:5px; border:none;" href="javascript:document.getElementById('sm_donate_form').submit();"><img style="vertical-align:middle; border:none; margin-top:2px;" src="<?php echo $this->GetResourceLink("{6E89EFD4-A853-4321-B5CF-3E36C60B268D}"); ?>" border="0" alt="PayPal" title="Help me to continue support of this plugin :)" /></a>
									<span><small>Thanks for your support!</small></span>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div id="advancedstuff" class="dbx-group" >
					
						<!-- Rebuild Area -->
						<div class="dbx-b-ox-wrapper">
							<fieldset id="sm_rebuild" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Status', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<ul>
											<?php
					
				//#type $status GoogleSitemapGeneratorStatus							
				$status = GoogleSitemapGeneratorStatus::Load();
				if($status == null) {
					echo "<li>" . str_replace("%s",$this->GetBackLink() . "&sm_rebuild=true",__('The sitemap wasn\'t built yet. <a href="%s">Click here</a> to build it the first time.','sitemap')) . "</li>";	
				}  else {
					if($status->_endTime !== 0) {
						if($status->_usedXml) {
							if($status->_xmlSuccess) {
								$ft = filemtime($status->_xmlPath);
								echo "<li>" . str_replace("%url%",$status->_xmlUrl,str_replace("%date%",date(get_option('date_format'),$ft) . " " . date(get_option('time_format'),$ft),__("Your <a href=\"%url%\">sitemap</a> was last built on <b>%date%</b>.",'sitemap'))) . "</li>"; 		
							} else {
								echo "<li class=\"sm_error\">" . str_replace("%url%",$this->GetRedirectLink('sitemap-help-files'),__("There was a problem writing your sitemap file. Make sure the file exists and is writable. <a href=\"%url%\">Learn more</a",'sitemap')) . "</li>";	
							}	
						}
						
						if($status->_usedZip) {
							if($status->_zipSuccess) {
									$ft = filemtime($status->_zipPath);
									echo "<li>" . str_replace("%url%",$status->_zipUrl,str_replace("%date%",date(get_option('date_format'),$ft) . " " . date(get_option('time_format'),$ft),__("Your sitemap (<a href=\"%url%\">zipped</a>) was last built on <b>%date%</b>.",'sitemap'))) . "</li>"; 		
							} else {
								echo "<li class=\"sm_error\">" . str_replace("%url%",$this->GetRedirectLink('sitemap-help-files'),__("There was a problem writing your zipped sitemap file. Make sure the file exists and is writable. <a href=\"%url%\">Learn more</a",'sitemap')) . "</li>";	
							}	
						}
						
						if($status->_usedGoogle) {
							if($status->_gooogleSuccess) {
								echo "<li>" .__("Google was <b>successfully notified</b> about changes.",'sitemap'). "</li>";
								$gt = $status->GetGoogleTime();
								if($gt>4) {
									echo "<li class=\sm_optimize\">" . str_replace("%time%",$gt,__("It took %time% seconds to notify Google, maybe you want to disable this feature to reduce the building time.",'sitemap')) . "</li>";		
								}						
							} else {
								echo "<li class=\"sm_error\">" . __("There was a problem while notifying Google.",'sitemap') . "</li>";	
							}	
						} 
						
						if($status->_usedYahoo) {
							if($status->_yahooSuccess) {
								echo "<li>" .__("YAHOO was <b>successfully notified</b> about changes.",'sitemap'). "</li>";
								$yt = $status->GetYahooTime();
								if($yt>4) {
									echo "<li class=\sm_optimize\">" . str_replace("%time%",$yt,__("It took %time% seconds to notify YAHOO, maybe you want to disable this feature to reduce the building time.",'sitemap')) . "</li>";		
								}	
							} else {
								echo "<li class=\"sm_error\">" . __("There was a problem while notifying YAHOO",'sitemap') . "</li>";	
							}	
						} 
						
						$et = $status->GetTime();
						$mem = $status->GetMemoryUsage();
						
						if($mem > 0) {
							echo "<li>" .str_replace(array("%time%","%memory%"),array($et,$mem),__("The building process took about <b>%time% seconds</b> to complete and used %memory% MB of memory.",'sitemap')). "</li>";	
						} else {
							echo "<li>" .str_replace("%time%",$et,__("The building process took about <b>%time% seconds</b> to complete.",'sitemap')). "</li>";		
						} 				
					} else {
						echo '<li class="sm_error">'. str_replace("%url%",$this->GetRedirectLink('sitemap-help-memtime'),__("The last run didn't finish! Maybe you can raise the memory or time limit for PHP scripts. <a href=\"%url%\">Learn more</a>",'sitemap')) . '</li>';	
						if($status->_memoryUsage > 0) {
							echo '<li class="sm_error">'. str_replace(array("%memused%","%memlimit%"),array($status->GetMemoryUsage(),ini_get('memory_limit')),__("The last known memory usage of the script was %memused%MB, the limit of your server is %memlimit%.",'sitemap')) . '</li>';		
						}	
						
						if($status->_lastTime > 0) {
							echo '<li class="sm_error">'. str_replace(array("%timeused%","%timelimit%"),array($status->GetLastTime(),ini_get('max_execution_time')),__("The last known execution time of the script was %timeused% seconds, the limit of your server is %timelimit% seconds.",'sitemap')) . '</li>';		
						}	
						
						if($status->GetLastPost() > 0) {
							echo '<li class="sm_optimize">'. str_replace("%lastpost%",$status->GetLastPost(),__("The script stopped around post number %lastpost% (+/- 100)",'sitemap')) . '</li>';		
						}	
					}
					echo "<li>" . str_replace("%s",$this->GetBackLink() . "&amp;sm_rebuild=true",__('If you changed something on your server or blog, you should <a href="%s">rebuild the sitemap</a> manually.','sitemap')) . "</li>";
				}
				?>
			
										</ul>
									</div>
								</div>
							</fieldset>
						</div>
													
						<!-- Basic Options -->
						<div class="dbx-b-ox-wrapper">
							<fieldset id="sm_basic_options" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Basic Options', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<b><?php _e('Sitemap files:','sitemap'); ?></b> <a href="<?php echo $this->GetRedirectLink('sitemap-help-options-files'); ?>"><?php _e('Learn more','sitemap'); ?></a>
										<ul>
											<li>
												<label for="sm_b_xml">
													<input type="checkbox" id="sm_b_xml" name="sm_b_xml" <?php echo ($this->GetOption("b_xml")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Write a normal XML file (your filename)', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_b_gzip">
													<input type="checkbox" id="sm_b_gzip" name="sm_b_gzip" <?php if(function_exists("gzencode")) { echo ($this->GetOption("b_gzip")==true?"checked=\"checked\"":""); } else echo "disabled=\"disabled\"";  ?> />
													<?php _e('Write a gzipped file (your filename + .gz)', 'sitemap') ?>
												</label>
											</li>
										</ul>
										<b><?php _e('Building mode:','sitemap'); ?></b> <a href="<?php echo $this->GetRedirectLink('sitemap-help-options-process'); ?>"><?php _e('Learn more','sitemap'); ?></a>
										<ul>
											<li>
												<label for="sm_b_auto_enabled">
													<input type="checkbox" id="sm_b_auto_enabled" name="sm_b_auto_enabled" <?php echo ($this->GetOption("sm_b_auto_enabled")==true?"checked=\"checked\"":""); ?> />
													<?php _e('Rebuild sitemap if you change the content of your blog', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_b_manual_enabled">
													<input type="hidden" name="sm_b_manual_key" value="<?php echo $this->GetOption("b_manual_key"); ?>" />
													<input type="checkbox" id="sm_b_manual_enabled" name="sm_b_manual_enabled" <?php echo ($this->GetOption("b_manual_enabled")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Enable manual sitemap building via GET Request', 'sitemap') ?>
												</label>
												<a href="javascript:void(document.getElementById('sm_manual_help').style.display='');">[?]</a>
												<span id="sm_manual_help" style="display:none;"><br />
												<?php echo str_replace("%1",trailingslashit(get_bloginfo('siteurl')) . "?sm_command=build&amp;sm_key=" . $this->GetOption("b_manual_key"),__('This will allow you to refresh your sitemap if an external tool wrote into the WordPress database without using the WordPress API. Use the following URL to start the process: <a href="%1">%1</a> Please check the logfile above to see if sitemap was successfully built.', 'sitemap')); ?>
												</span>
											</li>
										</ul>
										<b><?php _e('Update Notification:','sitemap'); ?></b> <a href="<?php echo $this->GetRedirectLink('sitemap-help-options-ping'); ?>"><?php _e('Learn more','sitemap'); ?></a>
										<ul>
											<li>
													<input style="float:left; margin-bottom:10px; margin-right:3px;" type="checkbox" id="sm_b_ping" name="sm_b_ping" <?php echo ($this->GetOption("b_ping")==true?"checked=\"checked\"":"") ?> />
													<label for="sm_b_ping"><?php _e('Notify Google about Updates of your Blog', 'sitemap') ?></label><br />
													<small>No registration required, but you can join the <a href="<?php echo $this->GetRedirectLink('sitemap-gwt'); ?>">Google Webmaster Tools</a> to check crawling statistics.</small>
											</li>
											<li style="clear:left;">
													<input style="float:left; margin-bottom:30px; margin-right:3px;" type="checkbox" id="sm_b_pingyahoo" name="sm_b_pingyahoo" <?php echo ($this->GetOption("sm_b_pingyahoo")==true?"checked=\"checked\"":"") ?> />
													<label for="sm_b_pingyahoo"><?php _e('Notify YAHOO about Updates of your Blog', 'sitemap') ?></label><br />
													<label for="sm_b_yahookey"><?php _e('Your Application ID:', 'sitemap') ?> <input type="text" name="sm_b_yahookey" id="sm_b_yahookey" value="<?php echo $this->GetOption("sm_b_yahookey"); ?>" /></label><br />
													<small>Don't have such a key? <a href="<?php echo $this->GetRedirectLink('sitemap-ykr'); ?>">Request it here</a>! (<a href="http://developer.yahoo.net/about/">Web Services by Yahoo!</a>)</small>
											</li>
										</ul>
										<b><?php _e('Advanced Options:','sitemap'); ?></b> <a href="<?php echo $this->GetRedirectLink('sitemap-help-options-adv'); ?>"><?php _e('Learn more','sitemap'); ?></a>
										<ul>
											<li>
												<label for="sm_b_memory"><?php _e('Try to increase the memory limit to:', 'sitemap') ?> <input type="text" name="sm_b_memory" id="sm_b_memory" style="width:40px;" value="<?php echo $this->GetOption("sm_b_memory"); ?>" /></label> (<?php echo htmlspecialchars(__('e.g. "4M", "16M"', 'sitemap')); ?>)
											</li>
											<li>
												<label for="sm_b_time"><?php _e('Try to increase the execution time limit to:', 'sitemap') ?> <input type="text" name="sm_b_time" id="sm_b_time" style="width:40px;" value="<?php echo ($this->GetOption("sm_b_time")===-1?'':$this->GetOption("sm_b_time")); ?>" /></label> (<?php echo htmlspecialchars(__('in seconds, e.g. "60" or "0" for unlimited', 'sitemap')) ?>)
											</li>	
											<li>
												<label for="sm_b_style"><?php _e('Include a XSLT stylesheet:', 'sitemap') ?> <input type="text" name="sm_b_style" id="sm_b_style"  value="<?php echo $this->GetOption("sm_b_style"); ?>" /></label> <?php if($this->GetDefaultStyle()!='') { echo ' <a href="javascript:void(0);" onclick="document.getElementById(\'sm_b_style\').value=\'' . $this->GetDefaultStyle() . '\';">' . __('Use Default','sitemap') . '</a>'; } ?> (<?php _e('Full or relative URL to your .xsl file', 'sitemap') ?>)
											</li>									
										</ul>
									</div>
								</div>
							</fieldset>
						</div>
						
						<div class="dbx-b-ox-wrapper">
							<fieldset id="sm_pages" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Additional pages', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<?php 
										_e('Here you can specify files or URLs which should be included in the sitemap, but do not belong to your Blog/WordPress.<br />For example, if your domain is www.foo.com and your blog is located on www.foo.com/blog you might want to include your homepage at www.foo.com','sitemap');
										echo "<ul><li>";
										echo "<strong>" . __('Note','sitemap'). "</strong>: ";
										_e("If your blog is in a subdirectory and you want to add pages which are NOT in the blog directory or beneath, you MUST place your sitemap file in the root directory (Look at the &quot;Location of your sitemap file&quot; section on this page)!",'sitemap');
										echo "</li><li>";
										echo "<strong>" . __('URL to the page','sitemap'). "</strong>: ";
										_e("Enter the URL to the page. Examples: http://www.foo.com/index.html or www.foo.com/home ",'sitemap');
										echo "</li><li>";
										echo "<strong>" . __('Priority','sitemap') . "</strong>: ";
										_e("Choose the priority of the page relative to the other pages. For example, your homepage might have a higher priority than your imprint.",'sitemap');
										echo "</li><li>";
										echo "<strong>" . __('Last Changed','sitemap'). "</strong>: ";
										_e("Enter the date of the last change as YYYY-MM-DD (2005-12-31 for example) (optional).",'sitemap');
										
										echo "</li></ul>";
										
										
										?>
										<script type="text/javascript">
											//<![CDATA[
											<?php
											$freqVals = "'" . implode("','",$this->_freqNames). "'";
											$transUpper = create_function('&$s',' return ucfirst(__($s,"sitemap"));');
											
											$freqNamesArr = array_map($transUpper,$this->_freqNames);  
											$freqNames = "'" . implode("','",$freqNamesArr). "'";
											?>
			
											var changeFreqVals = new Array( <?php echo $freqVals; ?> );
											var changeFreqNames= new Array( <?php echo $freqNames; ?> );
											
											var priorities= new Array(0 <?php for($i=0.1; $i<1; $i+=0.1) { echo "," .  $i; } ?>);
											
											var pages = [ <?php
												if(count($this->_pages)>0) {
													for($i=0; $i<count($this->_pages); $i++) {
														$v=&$this->_pages[$i];
														if($i>0) echo ",";
														echo '{url:"' . $v->getUrl() . '", priority:"' . $v->getPriority() . '", changeFreq:"' . $v->getChangeFreq() . '", lastChanged:"' . ($v!=null && $v->getLastMod()>0?date("Y-m-d",$v->getLastMod()):"") . '"}';											
													}
												}
											?> ];
											
											function sm_addPage(url,priority,changeFreq,lastChanged) {
											
												var table = document.getElementById('sm_pageTable').getElementsByTagName('TBODY')[0];
												var ce = function(ele) { return document.createElement(ele) };
												var tr = ce('TR');
																							
												var td = ce('TD');
												var iUrl = ce('INPUT');
												iUrl.type="text";
												iUrl.style.width='95%';
												iUrl.name="sm_pages_ur[]";
												if(url) iUrl.value=url;
												td.appendChild(iUrl);
												tr.appendChild(td);
												
												td = ce('TD');
												td.style.width='150px';
												var iPrio = ce('SELECT');
												iPrio.style.width='95%';
												iPrio.name="sm_pages_pr[]";
												for(var i=0; i <priorities.length; i++) {
													var op = ce('OPTION');
													op.text = priorities[i];		
													op.value = priorities[i];
													try {
														iPrio.add(op, null); // standards compliant; doesn't work in IE
													} catch(ex) {
														iPrio.add(op); // IE only
													}
													if(priority && priority == op.value) {
														iPrio.selectedIndex = i;
													}
												}
												td.appendChild(iPrio);
												tr.appendChild(td);
												
												td = ce('TD');
												td.style.width='150px';
												var iFreq = ce('SELECT');
												iFreq.name="sm_pages_cf[]";
												iFreq.style.width='95%';
												for(var i=0; i<changeFreqVals.length; i++) {
													var op = ce('OPTION');
													op.text = changeFreqNames[i];		
													op.value = changeFreqVals[i];
													try {
														iFreq.add(op, null); // standards compliant; doesn't work in IE
													} catch(ex) {
														iFreq.add(op); // IE only
													}
													
													if(changeFreq && changeFreq == op.value) {
														iFreq.selectedIndex = i;
													}
												}
												td.appendChild(iFreq);
												tr.appendChild(td);
												
												var td = ce('TD');
												td.style.width='150px';
												var iChanged = ce('INPUT');
												iChanged.type="text";
												iChanged.name="sm_pages_lm[]";
												iChanged.style.width='95%';
												if(lastChanged) iChanged.value=lastChanged;
												td.appendChild(iChanged);
												tr.appendChild(td);
												
												var td = ce('TD');
												td.style.textAlign="center";
												td.style.width='5px';
												var iAction = ce('A');
												iAction.innerHTML = 'X';
												iAction.href="javascript:void(0);"
												iAction.onclick = function() { table.removeChild(tr); };
												td.appendChild(iAction);
												tr.appendChild(td);
												
												var mark = ce('INPUT');
												mark.type="hidden";
												mark.name="sm_pages_mark[]";
												mark.value="true";
												tr.appendChild(mark);
												
												
												var firstRow = table.getElementsByTagName('TR')[1];
												if(firstRow) {
													var firstCol = firstRow.childNodes[1];
													if(firstCol.colSpan>1) {
														firstRow.parentNode.removeChild(firstRow);
													}
												}
												var cnt = table.getElementsByTagName('TR').length;
												if(cnt%2) tr.className="alternate";
												
												table.appendChild(tr);										
											}
											
											function sm_loadPages() {
												for(var i=0; i<pages.length; i++) {
													sm_addPage(pages[i].url,pages[i].priority,pages[i].changeFreq,pages[i].lastChanged);
												}
											}
											
											//]]>										
										</script>
										<table width="100%" cellpadding="3" cellspacing="3" id="sm_pageTable"> 
											<tr>
												<th scope="col"><?php _e('URL to the page','sitemap'); ?></th>
												<th scope="col"><?php _e('Priority','sitemap'); ?></th>
												<th scope="col"><?php _e('Change Frequency','sitemap'); ?></th>
												<th scope="col"><?php _e('Last Changed','sitemap'); ?></th>
												<th scope="col"><?php _e('#','sitemap'); ?></th>
											</tr>			
											<?php
												if(count($this->_pages)<=0) { ?>
													<tr> 
														<td colspan="5" align="center"><?php _e('No pages defined.','sitemap') ?></td> 
													</tr><?php
												}
											?>
										</table>
										<a href="javascript:void(0);" onclick="sm_addPage();"><?php _e("Add new page",'sitemap'); ?></a>
									</div>
								</div>
							</fieldset>
						</div>

						
						<!-- AutoPrio Options -->
						<div class="dbx-b-ox-wrapper">
							<fieldset id="sm_postprio" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Post Priority', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<p><?php _e('Please select how the priority of each post should be calculated:', 'sitemap') ?></p>
										<ul>
											<li><p><input type="radio" name="sm_b_prio_provider" id="sm_b_prio_provider__0" value="" <?php echo $this->HtmlGetChecked($this->GetOption("b_prio_provider"),"") ?> /> <label for="sm_b_prio_provider__0"><?php _e('Do not use automatic priority calculation', 'sitemap') ?></label><br /><?php _e('All posts will have the same priority which is defined in &quot;Priorities&quot;', 'sitemap') ?></p></li>
											<?php
											for($i=0; $i<count($this->_prioProviders); $i++) {
												echo "<li><p><input type=\"radio\" id=\"sm_b_prio_provider_$i\" name=\"sm_b_prio_provider\" value=\"" . $this->_prioProviders[$i] . "\" " .  $this->HtmlGetChecked($this->GetOption("b_prio_provider"),$this->_prioProviders[$i]) . " /> <label for=\"sm_b_prio_provider_$i\">" . call_user_func(array(&$this->_prioProviders[$i], 'getName'))  . "</label><br />" .  call_user_func(array(&$this->_prioProviders[$i], 'getDescription')) . "</p></li>";
											}
											?>
										</ul>
									</div>
								</div>
							</fieldset>
						</div>
						
						
						<!-- Location Options -->
						<div class="dbx-b-ox-wrapper">
							<fieldset id="sm_location" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Location of your sitemap file', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<div>
											<b><label for="sm_location_useauto"><input type="radio" id="sm_location_useauto" name="sm_b_location_mode" value="auto" <?php echo ($this->GetOption("b_location_mode")=="auto"?"checked=\"checked\"":"") ?> /> <?php _e('Automatic location','sitemap') ?></label></b>
											<ul>
												<li>
													<label for="sm_b_filename">
														<?php _e('Filename of the sitemap file', 'sitemap') ?>
														<input type="text" id="sm_b_filename" name="sm_b_filename" value="<?php echo $this->GetOption("b_filename"); ?>" />
													</label><br />
													<?php _e('Detected Path', 'sitemap') ?>: <?php echo $this->getXmlPath(true); ?><br /><?php _e('Detected URL', 'sitemap') ?>: <a href="<?php echo $this->getXmlUrl(true); ?>"><?php echo $this->getXmlUrl(true); ?></a>
												</li>
											</ul>
										</div>
										<div>
											<b><label for="sm_location_usemanual"><input type="radio" id="sm_location_usemanual" name="sm_b_location_mode" value="manual" <?php echo ($this->GetOption("b_location_mode")=="manual"?"checked=\"checked\"":"") ?>  /> <?php _e('Custom location','sitemap') ?></label></b>
											<ul>
												<li>
													<label for="sm_b_filename_manual">
														<?php _e('Absolute or relative path to the sitemap file, including name.','sitemap');
														echo "<br />";
														_e('Example','sitemap');
														echo ": /var/www/htdocs/wordpress/sitemap.xml"; ?><br />
														<input style="width:70%" type="text" id="sm_b_filename_manual" name="sm_b_filename_manual" value="<?php echo (!$this->GetOption("b_filename_manual")?$this->getXmlPath():$this->GetOption("b_filename_manual")); ?>" />
													</label>
												</li>
												<li>
													<label for="sm_b_fileurl_manual">
														<?php _e('Complete URL to the sitemap file, including name.','sitemap');
														echo "<br />";
														_e('Example','sitemap');
														echo ": http://www.yourdomain.com/sitemap.xml"; ?><br />
														<input style="width:70%" type="text" id="sm_b_fileurl_manual" name="sm_b_fileurl_manual" value="<?php echo (!$this->GetOption("b_fileurl_manual")?$this->getXmlUrl():$this->GetOption("b_fileurl_manual")); ?>" />
													</label>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
						
						
						<!-- Includes -->
						<div class="dbx-b-ox-wrapper">	
							<fieldset id="sm_includes" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Sitemap Content', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<ul>
											<li>
												<label for="sm_in_home">
													<input type="checkbox" id="sm_in_home" name="sm_in_home"  <?php echo ($this->GetOption("in_home")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Include homepage', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_in_posts">
													<input type="checkbox" id="sm_in_posts" name="sm_in_posts"  <?php echo ($this->GetOption("in_posts")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Include posts', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_in_pages">
													<input type="checkbox" id="sm_in_pages" name="sm_in_pages"  <?php echo ($this->GetOption("in_pages")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Include static pages', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_in_cats">
													<input type="checkbox" id="sm_in_cats" name="sm_in_cats"  <?php echo ($this->GetOption("in_cats")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Include categories', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_in_arch">
													<input type="checkbox" id="sm_in_arch" name="sm_in_arch"  <?php echo ($this->GetOption("in_arch")==true?"checked=\"checked\"":"") ?> />
													<?php _e('Include archives', 'sitemap') ?>
												</label>
											</li>
										</ul>
									</div>
								</div>
							</fieldset>
						</div>
						
						
						<!-- Change frequencies -->
						<div class="dbx-b-ox-wrapper">	
							<fieldset id="sm_change_frequencies" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Change frequencies', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">
										<p>
											<b><?php _e('Note', 'sitemap') ?>:</b> 
											<?php _e('Please note that the value of this tag is considered a hint and not a command. Even though search engine crawlers consider this information when making decisions, they may crawl pages marked "hourly" less frequently than that, and they may crawl pages marked "yearly" more frequently than that. It is also likely that crawlers will periodically crawl pages marked "never" so that they can handle unexpected changes to those pages.', 'sitemap') ?>
										</p>
										<ul>
											<li>
												<label for="sm_cf_home">
													<select id="sm_cf_home" name="sm_cf_home"><?php $this->HtmlGetFreqNames($this->GetOption("cf_home")); ?></select> 
													<?php _e('Homepage', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_cf_posts">
													<select id="sm_cf_posts" name="sm_cf_posts"><?php $this->HtmlGetFreqNames($this->GetOption("cf_posts")); ?></select> 
													<?php _e('Posts', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_cf_pages">
													<select id="sm_cf_pages" name="sm_cf_pages"><?php $this->HtmlGetFreqNames($this->GetOption("cf_pages")); ?></select> 
													<?php _e('Static pages', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_cf_cats">
													<select id="sm_cf_cats" name="sm_cf_cats"><?php $this->HtmlGetFreqNames($this->GetOption("cf_cats")); ?></select> 
													<?php _e('Categories', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_cf_arch_curr">
													<select id="sm_cf_arch_curr" name="sm_cf_arch_curr"><?php $this->HtmlGetFreqNames($this->GetOption("cf_arch_curr")); ?></select> 
													<?php _e('The current archive of this month (Should be the same like your homepage)', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_cf_arch_old">
													<select id="sm_cf_arch_old" name="sm_cf_arch_old"><?php $this->HtmlGetFreqNames($this->GetOption("cf_arch_old")); ?></select> 
													<?php _e('Older archives (Changes only if you edit an old post)', 'sitemap') ?>
												</label>
											</li>
										</ul>
									</div>
								</div>
							</fieldset>
						</div>
						
						
						<!-- Priorities -->	
						<div class="dbx-b-ox-wrapper">	
							<fieldset id="sm_priorities" class="dbx-box">
								<div class="dbx-h-andle-wrapper">
									<h3 class="dbx-handle"><?php _e('Priorities', 'sitemap') ?></h3>
								</div>
								<div class="dbx-c-ontent-wrapper">
									<div class="dbx-content">			
										<ul>
											<li>
												<label for="sm_pr_home">
													<select id="sm_pr_home" name="sm_pr_home"><?php $this->HtmlGetPriorityValues($this->GetOption("pr_home")); ?></select> 
													<?php _e('Homepage', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_pr_posts">
													<select id="sm_pr_posts" name="sm_pr_posts"><?php $this->HtmlGetPriorityValues($this->GetOption("pr_posts")); ?></select> 
													<?php _e('Posts (If auto calculation is disabled)', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_pr_posts_min">
													<select id="sm_pr_posts_min" name="sm_pr_posts_min"><?php $this->HtmlGetPriorityValues($this->GetOption("pr_posts_min")); ?></select> 
													<?php _e('Minimum post priority (Even if auto calculation is enabled)', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_pr_pages">
													<select id="sm_pr_pages" name="sm_pr_pages"><?php $this->HtmlGetPriorityValues($this->GetOption("pr_pages")); ?></select> 
													<?php _e('Static pages', 'sitemap'); ?>
												</label>
											</li>
											<li>
												<label for="sm_pr_cats">
													<select id="sm_pr_cats" name="sm_pr_cats"><?php $this->HtmlGetPriorityValues($this->GetOption("pr_cats")); ?></select> 
													<?php _e('Categories', 'sitemap') ?>
												</label>
											</li>
											<li>
												<label for="sm_pr_arch">
													<select id="sm_pr_arch" name="sm_pr_arch"><?php $this->HtmlGetPriorityValues($this->GetOption("pr_arch")); ?></select> 
													<?php _e('Archives', 'sitemap') ?>
												</label>
											</li>
										</ul>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					<div>
						<p class="submit">
							<input type="submit" name="sm_update" value="<?php _e('Update options', 'sitemap'); ?>" />
							<input type="submit" onclick='return confirm("Do you really want to reset your configuration?");' class="sm_warning" name="sm_reset_config" value="<?php _e('Reset options', 'sitemap'); ?>" />
						</p>
					</div>
				</div>
				<script type="text/javascript">if(typeof(sm_loadPages)=='function') addLoadEvent(sm_loadPages); </script>
			</form>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="sm_donate_form">
				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="business" value="donate@arnebrachhold.de" />
				<input type="hidden" name="item_name" value="Sitemap Generator for WordPress. Please tell me if if you don't want to be listed on the donator list." />
				<input type="hidden" name="no_shipping" value="1" />
				<input type="hidden" name="return" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $this->GetBackLink(); ?>&amp;sm_donated=true" />
				<input type="hidden" name="item_number" value="0001" />
				<input type="hidden" name="currency_code" value="USD" />
				<input type="hidden" name="bn" value="PP-BuyNowBF" />
				<input type="hidden" name="rm" value="2" />
				<input type="hidden" name="on0" value="Your Website" />
				<input type="hidden" name="os0" value="<?php echo get_bloginfo("home"); ?>"/>
			</form>
		</div>
		<?php
	}
}


//Check if ABSPATH and WPINC is defined, this is done in wp-settings.php
//If not defined, we can't guarante that all required functions are available.
if(defined('ABSPATH') && defined('WPINC')) {
	GoogleSitemapGenerator::Enable();	
}

#region Embedded resources
if(isset($_GET["res"]) && !empty($_GET["res"])) {
	$resources = array(
			//PayPal
			"{8C0BAD8C-77FA-4842-956E-CDEF7635F2C7}"
			=>"R0lGODlhEAAQAMQQANbe5sjT3gAzZpGmvOTp7v///629zYSctK28zbvI1ZKnvfH094SbtHaQrHaRrJ+xxf///wAAAAAAAAAAAAAAAAAAAAAAA"
			. "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAHoAxAALAAAAAAQABAAQAVZICSOZDkGQqGuBTqUSMqqiTACwqIKgGkKwCDwVRIodkDVwjYSMFSNoCNQIgSugZ6"
			. "vBMBetSYCj0UwCA6lgyy4YoqWASRAZSYNZNaA+VxM7B5bEA9CboGGIyEAOw==",
			
			//Amazon
			"{9866EAFC-3F85-44df-8A72-4CD1566E2D4F}"
			=>"R0lGODlhEAAQAMQGADc1NVBOTmloaERCQl1bW9rZ2f///7Szs8rKyubm5nZ0dJuamvPy8qinp31gOLeDOuWgPPGnPM6SO05DNoKBgY+NjXFZN8"
			. "OLOpRuOWZSN0M8NdqZO1pKNqB1OYhnOOrBhSwAAAAAEAAQAEAFeSAgBsIgnqiYGOxaAM4DRZE0AcOCMOyS/hxLR0KLPCaciEOEMCQKLMVPRIgCetPsaYLRXCKaIm3Tu"
			. "Z08mhMByjpoAQhE4GrwZaGMBnYq6OkNL1kBCwsUABUDFhcXGDIRGVMTDl8QExsSABcbYjQXkABDIh8XHQ5mWiEAOw==",
			
			//Homepage
			"{AD59B831-BF3D-49b1-A649-9DD8EDA1798A}"
			=>"R0lGODlhEAAQAPc6AKG82qK72aG62KC52KK52Z+32aG526C315631aK52KG71qC516O62aO82qC42qO63KW83KK72p+41p+415+62KG42J+51K"
			. "S72qG62aK526S726O62qW616O92KW52qC41KK61qG51Z230p611aS31ae826O71aa516a72KO416i816K61KO41aC40p+306G21aW41qe62KK30qS51qG20aq+2am61"
			. "qi51aa61aW51LHD2aq52Ka306690qi20aa606O402R7nae40rW70bK817C606u507C70a651a6406m506+71a2916KwzVVwm1Fokqa30ai40au51Ka20Ke1z6m50qy4"
			. "0qa11Ke30ae41F9xlVlym1Zsk0tsl6O30qS1z6W20qK2z6W1z6O3z6O0zqq60am1z5+uy110ll1zmlNvl09rk1h0nKO00KW20KC0zV52kFV1m1l0n150nVRwl150m1F"
			. "zmU9slmp+n560zJ+zzKGyzmF0kk5rlVJymV11mVtzmVhvm0xpkWh4nFJulmR0mGqTwW2Sv2ySv2uOuFtym1pxmldzm2V3nV1xllBvm1hxmlhwkmR4m1x0mGRzmmCHsm"
			. "CKtF+FtGSGs1p/rFeBq1d8qVZ7qFt9q1h/qlN8qll5qlV3pFJ0oU90oFFzoVWAq1V9rlWArVZ/rVeCr1Z/q1h+rVR+rleArlN+q1d9rFB6rFN7rFF8qVR6q1F6qFN5q"
			. "FJ9qlB7qFJ7p1J7qVR6qVJ4pwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
			. "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
			. "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACwAAAAAEAAQAAAI/wABBBAwgECAAgYEHBCAIMEABQsYBGjg4AGECAskTABAoYIECwYYXMAgIIMGAQwWCFC5ge"
			. "GCBBwYdKjgAcMHECBCiBiRgACJEgxMMDiBIgUHFSZWsGjhQsELGByiwoghI8aJGTBQ0ADxoYaNGzhsqMiBQ8eOGzx6+PgBJAgPIUOIFDFyZAiSJEqWMGni5AmUKFKmU"
			. "KliJcqUK1iyaNnCpYuXL2DCiBlDpowQM2fQpFGzhk0bN2DefMkCJ84WOXPo1LFzB0+bPHra7OHTx88fQH0CCRpEqJChQ4gSKVrEqJGjR5DSRJI0iVIlS5cwZdK0iVMn"
			. "T59AhRI1ilQpU6dQpSBCpWoVK1anWrlileoVrFiyZtGqRcuVrVu0cNHKpWtXQAA7",
			
			//List
			"{FFA3E2B1-D2B1-4c66-B8A4-5F6E7D8781F2}"
			=>"R0lGODlhEAAQAJECAAAAAP///////wAAACH5BAHoAwIALAAAAAAQABAAAAIrlI+py50Ao4QIhosvPSD07HmVln3ASIYaqn0rB4InHGOz4da3MP"
			. "W7AwwKCwA7",
			
			//Support
			"{234C74C9-3DF4-4ae2-A12E-C157C67059D8}"
			=>"R0lGODlhEAANALMPAFV3uxFBoCJOpzNcrd3k8Yigz3eSyczW62aFwgAzmURptJmt1qq73bvJ5O7x+P///yH5BAHoAw8ALAAAAAAQAA0AAARaEA"
			. "QxSBHUvHcGfkhybEeCbBuREFyibUqAPguAeuiSMPe46Y1NSEEazBwJmyMASDhAi9lD4igYGonC8jnDLgQsyuIkfQQCxEchEfBJDbtUwljmCGYKXn3P7z8iADs=",
			
			//XML Sitemap
			"{7428F989-4DE9-4a97-AFF8-9E7E4B2E5BA9}"
			=>"R0lGODlhUAAPAJEBAGZmZv////9mAImOeSwAAAAAUAAPAAACn4SPqcvtD0+YtNqLs968myCE4kiWYTCk6sq27gu7wWfWNRrn+s7OAGgLinC8or"
			. "FHOw2VTOBwRUnhogMq8Yol5nxOAcg58QK7XpW2ak6bUROpGw3fJpfLL/O5Vset6Km1vcM1Ync3RnIGd6W39zZlJFgYKQa2pvhWBWjpGBc4J2SDeCQq9/MpFDqa2gJpe"
			. "qP6CsPVMUtba4sRkau7y6tQAAA7",
			
			//Google
			"{7E5622AF-0DE3-4e43-99F9-33EC61308376}"
			=>"R0lGODlhEAAQAOYAAERx0ypnuRcvx9waAP/9+dEnKf/6/llutvb9//b/8hc5uik3s0i3T4iP1SYuku3//hll5NocEMrS1CtX1//5+lO2OxpZ9/"
			. "/2/+n//8smABVn0Rpf1vr/4dUjG//19dHm/x1Gskq1OhQrpfb7/iNcy02yQ/r7/+X2/vj///v///X/6//9///9/fn9//P///D//+7/9v/z/+f5//3/6f7/9Pr+7ykwd"
			. "C23QBxascrq+YGZ38rT8mWF2Hqk1hgiiKq2wv7///z99/fx//L8/eT29vf/7ff/+0+tVd3r/3ec6fj79P/58/z/8wAlvSFY6AQ2sf/x+Bw4jP/1+ur5/9fy/yJg2a/S"
			. "+s4SC84UGeHf7MnX/w0Uov/42f/45m+s783c83yPx/D/9/j/5uDm/O/v/AAilv/07Rc7k9IoD7zX/7/e8KOpzQdj6Pv191a2Suj/9mKEqe//8zdb1vz/9v7/9/z//8L"
			. "O/vb668Tb4wATVtPZ+cH1//7/4//8/zy1RP///yH5BAAAAAAALAAAAAAQABAAAAf/gGxuIUclDBWGfhUMITd+fhN3f218DyknWS5kLRcUUAUWKCgPVEkHCwJPAV5qCV"
			. "0ZGnQwewAKSBh1GFpRNmQxVyR8KDwOU3w1LC19XxIJS2gQIzpneihiYRwJFyZ9CTMZTi5JChcENAYvLx4pNEUGVxMPATgzBAYXfX1SK3MqFBEQHpAA4YnGkAt0CKRYM"
			. "YNLgQ0uAJRRwkLIHxdv8OT4sONbFRU9moCBgaIPHTsLRCxoEKSDhT8yBGxJYwSImT4mPvhYwwfLBhocxjjY0kAGkzgPrOT5wSeChhcIEJw44EOEAAUCQMAh8qeDnBh8"
			. "/ogdK3ZEnwseOjxay7atnwGBAQAAOw==",
			
			//YAHOO
			"{BC853F21-410E-47ff-BB6D-2B89C9D7E76B}"
			=>"R0lGODlhEAAQAJEAAAAAAP8AAP///wAAACH5BAAAAAAALAAAAAAQABAAAAIplI+py+0dogwOSADEnEf2kHkfFWUamEzmpZSfmaIHPHrRguUm/f"
			. "T+UwAAOw==",
			
			//PayPal
			"{6E89EFD4-A853-4321-B5CF-3E36C60B268D}"
			=>"R0lGODlhPgAfAMQAADJXgRVBcMHN2k5uktHV2oiguLTE08jT3tfh6QAhWPb4+kRli+fs8KS3yp2wxOPx93CMqeHn7VZ2mHyWsef1+u7x9GiDoi"
			. "RIc7u7u+np6QY0ZiI2TZSnvQAAAOr3/P///yH5BAAAAAAALAAAAAA+AB8AAAX/INdwZGmeaKquLFlYQ+HNdG3feK7vNlB8wKBwSCwaj8jhxfK5PZ5Qng4a3T0K2AJn9"
			. "sksiYKAZqwJLBxJIuNCHgMmFaNAAwAEAMAK4WubXAyABg4WGhYzFIg8BgEOgQ0QdxQeiJIzEwAVCgMSM3pfQwtMQw2FC3UAMQOqAxAMEqp1RAgBm6cLHJpMCgAceXtM"
			. "NQ8BDR8MCwMIHxUXARZZEmOsA4UBEBAXEkAWCwZA085YFmYXvQgaAp2/QwcBBB8GCQloFQE/QgXyHwjxxBEBvQw0JIDgbcAQA2MOfHAQIIgnYDQ4XFCwcAnFA4UkXAD"
			. "g7sDADxE0LPCWIJkAVBE+/ygwY2FjNwYBAlCEQePhEBiyACTwYeAAgw8cEhhcJuBDgwQX4gipYAHphJ4/MRr84MOhuhoXfigQIOBRgAT2DnT7IGGnAgXE1ggFckCAgQ"
			. "IXEkigGKFBHAdgQQYwUFNdkJBFMZY5NvHDhLW7FgT4qS3AhQnK2NyBluxogDhNuy1K6YvPjAaXgV44gMBDAzwfDjiIM4uhwg9zCgQoas4AAgpzUkbgwJnZz0s1bAaBs"
			. "ICBq6mpNaxWcMBng9EaiO2yMAcBA7gOq2WKYIABRgDGFxC06tnDgjYTakDQcIENmQUSAFCcEADBejKcaJBy3KZ/g+B+AUEAV1wpFQQBDjggQNZbB0yggTvmlEOgAJwF"
			. "gUCCPU1IIEXkQSTFDbNkVQAAC1Ty4YnCpVEEB6eQmIyKMHbm4Yk01qhDijHmqGMRNtno4499fYJEB0R2EESRQBiZ5JFEJlmkkU8m0eMOHdBQZZUzXInlllZ2mSWKAR6"
			. "h5AdQHknmmUo+OeaaTUp5FZVdYunBlVly+eWdc4IppJhIOpmmmWcuKSiZfR4xJZCIngjEBUNl4OijkEYq6aSUVmppBggYsFGmGHTq6aeghirqqKSW2ikHG7S3waqstu"
			. "rqq7DGKuusroYAADs="
			
			);
				
	if(array_key_exists($_GET["res"],$resources)) {
		$key = $_GET["res"];
		$content = base64_decode($resources[$key]);
			
		$lastMod = filemtime(__FILE__);
		$client = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?$_SERVER['HTTP_IF_MODIFIED_SINCE']:false);
		// Checking if the client is validating his cache and if it is current.
		if (isset($client) && (strtotime($client) == $lastMod)) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 304);
			exit;
		} else {
			// Image not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 200);
			header('Content-Length: '.strlen($content));
			header('Content-Type: image/gif');
			echo $content;
			exit;
		}	
	}
}
#endregion
?>
<?php
/**
 * AJAX call handler for tagindex admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gina Häußge, Michael Klier <dokuwiki@chimeric.de>
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Arthur Lobert <arthur.lobert@gmail.com>
 */

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../../') . '/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if (!defined('DOKU_REG')) define ('DOKU_REG', DOKU_PLUGIN.'autolink3/register/');
if (!defined('DOKU_PAGE')) define ('DOKU_PAGE', DOKU_INC.'data\pages');
if (!defined('DOKU_PAGES')) define ('DOKU_PAGES', realpath(DOKU_PAGE));
if (!defined('NL')) define('NL', "\n");

//fix for Opera XMLHttpRequests
if(!count($_POST) && $HTTP_RAW_POST_DATA)
{
	parse_str($HTTP_RAW_POST_DATA, $_POST);
}

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/indexer.php');
	
//close session
session_write_close();
header('Content-Type: text/plain; charset=utf-8');
if (!auth_isadmin())
{
	die('for admins only');
}

//clear all index files
if (@file_exists($conf['indexdir'].'/page.idx'))// new word length based index
{
	$tag_idx = $conf['indexdir'].'/topic.idx';
}
else
{                                          // old index
$tag_idx = $conf['cachedir'].'/topic.idx';
}
$tag_helper =& plugin_load('helper', 'tag');

//call the requested function
$call = 'ajax_'.$_POST['call'];
if(function_exists($call))
{
	$call();
}
else
{
	print "The called function '".htmlspecialchars($call)."' does not exist!";
}

/**
 * Searches for pages
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_pagelist()
{
	global $conf;

	$pages = array();
	search($pages, $conf['datadir'], 'search_allpages', array());
	foreach($pages as $page)
	{
		print $page['id']."\n";
	}
}

/**
 * Clear all index files
 */
function ajax_clearindex() {
	global $conf;
	global $tag_idx;

	
	// keep running
	@ignore_user_abort(true);

	// try to aquire a lock
	$lock = $conf['lockdir'].'/_tagindexer.lock';
	while(!@mkdir($lock))
	{
		if(time()-@filemtime($lock) > 60*5)
		{
			// looks like a stale lock - remove it
			@rmdir($lock);
		}
		else
		{
			print 'tagindexer is locked.';
			exit;
		}
	}
	io_saveFile($tag_idx,'');
	// we're finished
	print 1;
}

/**
 * check if the current page is concerned by the link processing (Ajax).
 * @param $page : name of the current page
 * @param $text : content of $page
 * @return $text
 */

function is_link_application($page, $text)
{

	$link = sort_tab(read_file(), compare_len);
	$link = sort_tab_space($link);
	foreach ($link as $elem):{
		$locate = trim($elem[2]);
		$page = realpath($page); 
		$locate = realpath($locate); 
		if (!strncmp($page, $locate, strlen($locate)))
		{
			$text = link_replace($text, $elem[0], $elem[1], DOKU_PAGE.str_replace(':', '/', $page));
		}
	}
	endforeach;
	return ($text);
}

function ajax_indexpage($page = NULL, $text = NULL) {
	//check end
		global $conf;
		
	if(!$_POST['page'] && $page == NULL)
	{
		print 1;
		exit;
	}
	// keep running
	@ignore_user_abort(true);

	$flag = 0;
	// choose $page source
	!$page ? $page = $_REQUEST['page'] : $flag = 1;

	//$page format
	$page = ':'.trim($page).'.txt';
	$text = is_link_application($page, $text);
	if ($text != '')
	{
		$rd_page = fopen(DOKU_PAGE.str_replace(':', '/', $page), 'w+');
		fwrite($rd_page, $text);
	}
	
	$lock = $conf['lockdir'].'/_tagindexer.lock';
	// we're finished
	
	@rmdir($lock);
	print 1;
}
?>
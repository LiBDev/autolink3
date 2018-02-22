<?php
/**
 * Autolink Action Plugin:
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Vincent Fleury <fleury.vincent@gmail.com>
 * @author     Arthur Lobert <arthur.lobert@gmail.com>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if (!defined('DOKU_REG')) define ('DOKU_REG', DOKU_PLUGIN.'autolink3/register/');
if (!defined('DOKU_PAGES')) define ('DOKU_PAGES', realpath('./data/pages'));
require_once (DOKU_PLUGIN.'action.php');
require_once ('sys.php');

class action_plugin_autolink3 extends DokuWiki_Action_Plugin
{
	/**
	 * return some info
	 */
	function getInfo()
	{
		return array(
                'author' => 'Arthur Lobert',
                'email'  => 'arthur.lobert@gmail.com',
                'date'   => @file_get_contents(DOKU_PLUGIN.'autolink3/VERSION'),
                'name'   => 'autolink3',
                'desc'   => 'Automatic link Manager',
                'url'    => 'http://www.dokuwiki.org/plugin:autolink3',
		);
	}

	/**
	 * Register its handlers with the DokuWiki's event controller
	 */

	function register(&$controller)
	{
		$controller->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this,
                                      '_autolink', array());
	}

/**
 * check if the current page is concerned by the by the link processing.
 * @param $page : name of the current page
 * @param $text : content of $page
 * @return $text
 */
	private function _is_link_application($page, $text)
	{
		$link = sort_tab(read_file(), compare_len);
		$link = sort_tab_space($link);
		foreach ($link as $elem):{
			$locate = trim($elem[2]);
			$locate = str_replace('\\', '/', DOKU_PAGES.str_replace(':', '/', $locate));
			$page = realpath($page); 
			$locate = realpath($locate); 
			if (!strncmp($page, $locate, strlen($locate)))
			{
				$text = link_replace($text, $elem[0], $elem[1], str_replace(':', '/', $page));
			}
		}
		endforeach;
		return ($text);
	}

	/**
	 * Apply autolink to page.
	 *
	 * @author Vincent Fleury <fleury.vincent@gmail.com>
	 */
	
	//TODO : private or not
	
	function _autolink(&$event, $param)
	{
		$event->data[0][1] = $this->_is_link_application($event->data[0][0], $event->data[0][1]);   
	}
}
?>
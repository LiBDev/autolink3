<?php
/**
 * Plugin : autolink3
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Arthur Lobert <arthur.lobert@gmail.com>
 */

if(!defined('DOKU_TINC')) define('DOKU_TINC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PAGE')) define('DOKU_PAGE', DOKU_TINC.'data/pages/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_TINC.'lib/plugins/');
if (!defined('DOKU_REG')) define ('DOKU_REG', DOKU_PLUGIN.'autolink3/register/');
require_once(DOKU_PLUGIN.'admin.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_autolink3 extends DokuWiki_Admin_Plugin
{
	function getInfo()
	{
		return array(
            'author' => 'Arthur Lobert',
            'email'  => 'arthur.lobert@gmail.com',
            'date'   => @file_get_contents(DOKU_PLUGIN.'autolink3/VERSION'),
            'name'   => 'autolink3',
            'desc'   => 'Replace key words by appropriates links',
            'url'    => 'http://www.dokuwiki.org/plugin:autolink3',
		);
	}

	function handle(){
	$this->error = 0;
		if (isset($_REQUEST['word'])) $word = $_REQUEST['word'];
		if (isset($_REQUEST['link'])) $page = $_REQUEST['link'];
		if (isset($_REQUEST['local'])) $local = $_REQUEST['local'];
		if (isset($_REQUEST['supr'])) mod_link($_REQUEST['supr']);
		if (isset($_REQUEST['complete']) && isset($_REQUEST['new_link']) && $_REQUEST['new_link'] != '')
		{
			$new_ligne = sprintf("%s	%s	%s\r\n", $_REQUEST['new_link'],$_REQUEST['new_page'],$_REQUEST['new_locate']);
			mod_link($_REQUEST['ligne'], $new_ligne);
		}
		$rd = fopen (DOKU_REG."register.txt", "a");
		$global = sprintf("%s	%s	%s\r\n", $word, $page, $local);
		if ($local && $word && $local && !is_link_exist($page, $local, $word)) fwrite ($rd, $global);
			elseif($_REQUEST['add']) $this->error = 1;
		fclose($rd);
	}


/**
 * create the link tab of the plugin interface
 * @return $ret which is the appropriate html code
 */
	
	private function _get_link_tab()
	{
		if (isset($_REQUEST['mod'])){
			$ligne = str_replace(';', '	',$_REQUEST['mod']);
		}
		$reg = read_file();
		isset ($_REQUEST['arrow']) ? $reg = sort_tab($reg, compare_alpha,$_REQUEST['arrow']) : $reg = sort_tab($reg, compare_alpha,$_REQUEST['up']);
		$pages = get_dokupage_tree(DOKU_PAGE, $pages, '', 0);		
			$local[] = ':';
		$local = get_dokupage_tree(DOKU_PAGE, $local, '', 1);	

		foreach($pages as $file):
		{
			$page_tree .= "<option value='".$file."'>".$file."</option>";
		}
		endforeach;
		foreach($local as $file):
		{
			$data_tree .= "<option value='".$file."'>".$file."</option>";
		}
		endforeach ;
		if (isset($reg))
		foreach ($reg as $lign):
		{
			if ($lign != '\r\n')
			{
				$ret .= '<tr onmouseover="this.bgColor=\'#CEF6CE\'" onmouseout="this.bgColor =\'#FFFFFF\'">';
				if (strcmp($ligne,rtrim($lign[0].'	'.$lign[1].'	'.$lign[2]))){
					$global = $lign[0].';'.$lign[1].';'.$lign[2];
					$ret .="<td>".$lign[0]."</td><td>".$lign[1]."</td><td>".$lign[2]."</td>
								<td>
								
			 						<input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/delete.gif' name='supr' value='".$global."' alt='del' />
			 						<input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/edit.gif' name='mod' value='".$global."' alt ='edit' />
			 					</td>
			 				</tr>";
				}
				else {print_r($lign);
						
							$ret .="<td><input type = 'text' name='new_link' value='".$lign[0]."'/></td>
							<td><select name = 'new_page'>".'<option value='.$lign[1].'>'.$lign[1].'</option>'.$page_tree."</select></td>
							<td><select name = 'new_locate'>".'<option value='.$lign[2].'>'.$lign[2].'</option>'.$data_tree."</select></td>
							<td>
			 					<input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/add.gif' name='complete' value='sd".$global."' alt='add' />
			 				</td></tr>";
				}
			}
		}
		endforeach;
		return ($ret);
	}

	/**
	 * output appropriate html
	 */

	function html()
	{
		$word = $this->_get_link_tab();
		ptln('<!-- Pagemove Plugin start -->');
		$tab = get_dokupage_tree(DOKU_PAGE, $tab, '', 0);	
			$local[] = ':';
		$local = get_dokupage_tree(DOKU_PAGE, $local, '', 1);
		foreach($tab as $file):
		{
			$page_tree .= "<option value='".$file."'>".$file."</option>";
		}
		endforeach;
		foreach($local as $file):
		{
			$data_tree .= "<option value='".$file."'>".$file."</option>";
		}
		endforeach ;
		//initialisation des fleches de tri
		$tab = array(0,0,0);
		isset ($_REQUEST['arrow']) ? $mem = $_REQUEST['arrow'] : $mem = $_REQUEST['up'];
		$tab[$mem] = 1;
		if ($this->error == 1)
			ptln ("<div class='error'>".$this->getLang('error')."</div>");
		echo "
		<div id='autolink3'>
			<h1 align='center'>
				Auto Link
			</h1>
			".$this->getLang('description')."
			<form action='".wl()."' method='post' accept-chartset='utf-8'>
				<input type='hidden' name='do' value='admin' />
				<input type='hidden' name='add' value=''/>
				<input type='hidden' name='page' value='autolink3' />
				<input type='hidden' name='ligne' value='".$_REQUEST['mod']."' />
				<input type='hidden' name='up' value='".$mem."' />
				<table>
					<tr>
						<th><div style='width:80%;float:left;'>".$this->getLang('link')."</div><div align='right' style='width:16px;float:left;'><input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/arrow_".($tab[0] ? "up" : "down").".gif' alt='sort' name='arrow' value ='0'/></div></th>
						<th><div style='width:80%;float:left;'>".$this->getLang('to')."</div><div align='right' style='width:16px;float:left;'><input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/arrow_".($tab[1] ? "up" : "down").".gif' alt='sort' name='arrow' value ='1'/></div></th>
						<th><div style='width:80%;float:left;'>".$this->getLang('in')."</div><div align='right' style='width:16px;float:left;'><input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/arrow_".($tab[2] ? "up" : "down").".gif' alt='sort' name='arrow' value ='2'/></div></th>
						<th>".$this->getLang('action')." </th>
					</tr>
					".$word."
					<tr>
	        			<td>
	        				<input type = 'text' name='word'/>
	        			</td>
	        			<td>
	        				<select name = 'link'>
							".$page_tree."	
							</select>
						</td>
						<td>
							<select name = 'local'>
							".$data_tree."
							</select>
						</td>
						<td>
							<input type='image' src='".DOKU_BASE."/lib/plugins/autolink3/ressources/add.gif' alt='add' name='add' value = 1/>
						</td>
					</tr>
				</table>
			</form>
		<script type='text/javascript' src='".DOKU_BASE."/lib/plugins/autolink3/script.js'>
		</script>
		<fieldset class='pl_si_out'>
			<button class='button' id='pl_si_gobtn' onclick='plugin_tagindex_go()'>
				Rebuild Linkindex
			</button>
			<div id='pl_si_out'>
        		<img src='".DOKU_BASE."/lib/images/loading.gif' id='pl_si_throbber' style='visibility: hidden' alt='load'/>		
			</div>
		</fieldset>
	</div>";
	}
}

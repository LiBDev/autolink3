<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if (!defined('DOKU_REG')) define ('DOKU_REG', DOKU_PLUGIN.'autolink3/register/');


/**
 * pars $ text to find potential links
 * @param $text content of $page
 * @param $word processing word(link)
 * @param $link associate page
 * @param $filename name of the treaty file
 * @return $ret (modified $text)
 */

function link_replace($text = NULL, $word, $link, $filename)
{
	if ($text == NULL)
	{
		$rd_page = fopen($filename, 'r');
		while ($ligne = fgets($rd_page)){
			$text .= $ligne;
		}
	}
	$tit = preg_split("/(==*)/",$text, -1 ,PREG_SPLIT_DELIM_CAPTURE);
	$ign = false;
	$old = NULL;
	foreach ($tit as $em):
	{
		if (preg_match("/==*/", $em)==1)$ign == false ? $ign = true : $ign = false;
	
			$ignore = 0;
			$tab = preg_split("/(\[\[|\]\]|\}\}|\{\{)/",$em, -1 ,PREG_SPLIT_DELIM_CAPTURE);
			foreach($tab as $element):
			{
				if ($ign == false){
					if ($element == "[[" || $element == "{{") $ignore = 1;
					elseif ($element == "]]" || $element == "}}") $ignore = 0;
					elseif ($ignore == 0)
					{
						if (preg_match('/(\W|^)('.$word.')($|[^a-z0-9_\-])/i', $element) == 1)
						{
							$element = preg_replace('/(\W|^)('.$word.')($|[^a-z0-9_\-])/i','\1'."[[".substr($link,0, strlen($link)-4)."|".'\2'."]]".'\3', $element);
							$element = link_replace($element, $word, $link, $filename);
						}
					}
				}
				$ret .= $element;
			}
			endforeach;
		}
	endforeach;
	return ($ret);
}

/**
 * addon of sort_tab : size comparison
 * @param $s1 : first string
 * @param $s2 :second string
 * @return returns the result of the size comparison of two strings
 */

function compare_len($s1, $s2)
{
	return (strlen ($s1) < strlen($s2));
}

/**
 * addon of sort_tab : alphabetic comparison
 * @param $s1 : first string
 * @param $s2 :second string
 * @return returns the result of the comparison of the two strings Alphabetically
 */

function compare_alpha($s1, $s2)
{
	return (strcmp($s1, $s2) < 0);
}

function compare_unalpha($s1, $s2)
{
	return (strcmp($s1, $s2) > 0);
}

/**
 * 
 * sort tab function (size and alphabetic sort)
 * @param $tab : link tab
 * @param $fcmp : function pointer for the comparison type
 * @param $col : for alphabetical comparison, determines the column to sort
 * @return $tab
 */

function sort_tab($tab ,$fcmp, $col = 0)
{
	if (isset ($tab)){
	foreach ($tab as $ligne):{
		$i = 0;
		while($tab[$i + 1])
		{
			if ($fcmp($tab[$i+1][$col], $tab[$i][$col]))
				list($tab[$i], $tab[$i + 1]) = array($tab[$i + 1], $tab[$i]);
			$i += 1;
		}
	}
	endforeach;
	return($tab);
	}
}

function sort_tab_space($tab)
{
	if (isset ($tab)){
	foreach ($tab as $ligne):{
		$i = 0;
		while($tab[$i + 1])
		{
			if (($tab[$i][0] == $tab[$î + 1][0]) && (strlen($tab[$i][2]) < strlen($tab[$i + 1][2])))
				list($tab[$i], $tab[$i + 1]) = array($tab[$i + 1], $tab[$i]);
			$i++;
		}
	}
	endforeach;
	return($tab);
	}
}

/**
 * check if the link already exist
 * @param $page : link associate page
 * @param $local : location of the link application
 * @param $word : link 
 * @return true or false if the link is already registered or not
 */

function is_link_exist($page, $local, $word)
{
	$global = sprintf("%s	%s	%s\r\n", $word, $page, $local);
	$rd = fopen(DOKU_INC.'lib/plugins/autolink3/register/register.txt', r);
	while ($check = fgets($rd))
	{
		$nword = explode('	', $check);
		if (!strcmp($check, $global) || (!strcmp($word, $nword[0]) && !strcmp($local, $nword[2])))
			return(1);
	}
	if (preg_match('/:/', $word))
		return(1);
	return (0);
}

/**
 * addon of get_dokuwiki_tree, check if the processing element is a page 
 * @param $page : name of the page checked
 * @return true or false if the page exist or not
 */

function is_txt($page)
{
	return substr_count($page, '.txt');
}

/**
 * read the data/pages folder and get the pages tree
 * @param $adress : path of the pages repository
 * @param $tab : final table with all the page 
 * @param $old : used for the recursivity in the folders
 * @param $flag : option witch determines the try content (folders or not)
 * @return $tab: the pages tree
 */

function get_dokupage_tree($adress, $tab, $old, $flag){
	{
		$rd = opendir($adress.str_replace(':','/',$old));
		while ($element = readdir($rd))
		{
			if ($element != '.' && $element != '..')
			{
				
				if ($old != '')
				$element = $old.':'.$element;
				if (is_txt($element) > 0) $tab[] = ':'. $element;
				elseif (is_dir($adress.str_replace(':','/',$element)))
				{
					if ($flag == 1) 
					{$tab[] = ':'.$element;
					}
					$tab = get_dokupage_tree($adress, $tab, $element, $flag);
				}
				
			}
		}
		closedir($rd);
		return ($tab);
	}
}

/**
 * read all the registered links
 * @return : $tab witch content the links
 */

function read_file()
{
	$rd = fopen (DOKU_REG."register.txt", "r");
	while ($str = fgets($rd))
	{
		$ret[] = explode("	",$str);
	}
	fclose($rd);
	return ($ret);
}

/**
 * modification function to register.txt (delete and modification)
 * @param $ligne : ligne to modifie
 * @param  $new : the modification
 */

function mod_link($ligne, $new = '')
{
	$rd = fopen (DOKU_REG.'register.txt', 'r + b');
	while ($str = fgets($rd))
	{
		$tab[] = $str;
	}
	if (isset($tab))
	foreach ($tab as $elm):
	{
		if(strcmp($elm, str_replace(";", "	",$ligne."\r\n")))
			$tab2[] = $elm;
		else 
			$tab2[] = $new;
	}
	endforeach;
	fclose($rd);
	$rd = fopen(DOKU_REG.'register.txt', 'w');
	if (isset($tab2))
	foreach ($tab2 as $eee):
	{
		fwrite($rd, $eee);
	}
	endforeach;
	fclose($rd);
}
?>
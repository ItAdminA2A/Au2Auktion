<?php
	//
	// Laver liste med elementer der kan indsættes
	//
	
	$tbl = new table;
	$tbl->th("{LANG|Navn}");
	$tbl->th("{LANG|Element}");
	$tbl->th("{LANG|Link}");
	$tbl->endrow();
	
	$tbl->td("{LANG|Menu - Komplet}");
	$tbl->td("{<!>PAGE|menu}");
	$tbl->td("-");
	$tbl->endrow();
	
	$tbl->td("{LANG|Menu - Top-menu}");
	$tbl->td("{<!>PAGE|menu_top}");
	$tbl->td("-");
	$tbl->endrow();
	
	$tbl->td("{LANG|Menu - Sub-menu}");
	$tbl->td("{<!>PAGE|menu_sub}");
	$tbl->td("-");
	$tbl->endrow();
	
	$tbl->td("{LANG|Menu - Linkoversigt}");
	$tbl->td("{<!>PAGE|menu_link}");
	$tbl->td("-");
	$tbl->endrow();
	
	$tbl->td("{LANG|Forside}");
	$tbl->td("{<!>PAGE|default}");
	$tbl->td($_site_url . "/");
	$tbl->endrow();
	
	// Henter sprog
	$array_langs = array();
	$select_langs = array(array("", "-"));
	$db->execute("
		SELECT
			*
		FROM
			" . $_table_prefix . "_languages_
		ORDER BY
			`default` DESC,
			title
		");
	while ($db->fetch_array())
	{
		$array_langs[$db->array["id"]] = stripslashes($db->array["title"]);
	}
	
	// Henter sider
	$pages = pages_array(true);

	// Gennemløber sider	
	for ($i = 0; $i < count($pages); $i++)
	{
		$tbl->td("{LANG|Sider} - " .
			($array_langs[$pages[$i]["lang_id"]] <> "" ? $array_langs[$pages[$i]["lang_id"]] : $pages[$i]["lang_id"])
			. " - " . $pages[$i]["title"]);
		$tbl->td("{<!>PAGE|default||" . $pages[$i]["id"] . "}");
		$tbl->td($_site_url . "/site/////" . $pages[$i]["id"]);
		$tbl->endrow();
	}	
	
	// Henter elementer
	$elements = module_elements();
	
	// Laver liste med elementer
	reset($elements);
	while (list($tmp_module, $tmp_array) = each($elements))
	{
		reset($tmp_array);
		while (list($key, $value) = each($tmp_array))
		{
			list($tmp_page, $tmp_do, $tmp_id) = split("[\|]", $value);
			$tbl->td(module2title($tmp_module) . " - " . $key);
			$tbl->td("{<!>MODULE|$tmp_module|$tmp_page|$tmp_do|$tmp_id}");
			$tbl->td($_site_url . "/site//$tmp_module/$tmp_page/$tmp_do/$tmp_id");
			$tbl->endrow();
		}
	}
	
	// Viser tabel
	$html .= $tbl->html();
?>
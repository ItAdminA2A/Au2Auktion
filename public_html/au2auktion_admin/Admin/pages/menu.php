<?php
	/*
		Visning af admin menu
	*/
	
	// Global
	global $_lang_, $_table_prefix, $db;
	
	// Henter rettigheder
	$user_rights = " " . $usr->extra_get("rights");
	$user_administrator = $usr->extra_get("administrator");
	
	// Array med alle menu grupper
	$array_groups = array();
	
	// Generelle menupunkter
	$elements = "";
	
	// Admin forside
	$tmp = new tpl("admin_menu_element");
	$tmp->set("title", "{LANG|Oversigt}");
	$tmp->set("module", "");
	$tmp->set("page", "default");
	$tmp->set("do", "");
	$elements .= $tmp->html();
	
	// Vis siden
	$tmp = new tpl("admin_menu_element_popup_url");
	$tmp->set("width", "1024");
	$tmp->set("height", "768");
	$tmp->set("url", "../");
	$tmp->set("title", "{LANG|Vis siden}");
	$elements .= $tmp->html();
	
	// Array med yderlige menupunkter
	$array = array(
		"{LANG|Menu}" => array("pages", "", true),
		"{LANG|Skift password}" => array("change_password", "", false),
		"{LANG|Log ud}" => array("logout", "", false)
		);
	reset($array);
	while (list($title, list($pagex, $dox, $right)) = each($array))
	{
		if ($user_administrator == 1 or
			strpos($user_rights, "|$pagex|") > 0 or
			!$right)
		{
			$tmp = new tpl("admin_menu_element");
			$tmp->set("title", $title);
			$tmp->set("module", "");
			$tmp->set("page", $pagex);
			$tmp->set("do", $dox);
			$elements .= $tmp->html();
		}
	}
	if ($elements <> "")
	{
		$tmp = new tpl("admin_menu_headline_visible");
		$tmp->set("icon", "../img/icon_home.gif");
		$tmp->set("id", "General");
		$tmp->set("headline", $usr->data["username"]);
		$tmp->set("elements", $elements);
		$array_groups["General"] = $tmp->html();
	}

	// Avanceret menupunkter
	$array = array(
		"{LANG|Indstillinger}" => array("settings", "", true),
		"{LANG|Farver / CSS}" => array("css", "", true),
		"{LANG|Layouts}" => array("layouts", "", true),
		"{LANG|Moduler}" => array("modules", "", true),
		"{LANG|CMS grundsystem}" => array("cms", "", true),
		"{LANG|Admin-brugere}" => array("admin_users", "", true),
		"{LANG|Sprog}" => array("languages", "", true),
		"{LANG|Domæner}" => array("domains", "", true),
		"{LANG|System-meddelelser}" => array("log_messages", "", true),
		"{LANG|Backup / Genskab}" => array("backup", "", true)
		);
	reset($array);
	$elements = "";
	$show = false;
	while (list($title, list($pagex, $dox, $right)) = each($array))
	{
		if ($user_administrator == 1 or
			strpos($user_rights, "|$pagex|") > 0 or
			!$right)
		{
			//if ($vars["module"] == "" and $vars["page"] == $pagex) $show = true;
			$tmp = new tpl("admin_menu_element");
			$tmp->set("title", $title);
			$tmp->set("module", "");
			$tmp->set("page", $pagex);
			$tmp->set("do", $dox);
			$elements .= $tmp->html();
		}
	}
	if ($elements <> "")
	{
		$tmp = new tpl("admin_menu_headline");
		$tmp->set("icon", "../img/icon_advanced.gif");
		$tmp->set("id", "Advanced");
		$tmp->set("headline", "Avanceret");
		$tmp->set("elements", $elements);
		$tmp->set("show", $show ? "1" : "");
		$array_groups["Advanced"] = $tmp->html();
	}

	// Henter menuer
	$array1 = admin_module_menu();
	reset($array1);
	while (list($folder, $menu) = each($array1))
	{
		if (strpos($user_rights, "|module_$folder|") > 0 or $user_administrator == 1)
		{
			$elements = "";
			reset($menu);
			while (list($key, $value) = each($menu))
			{
				$key2 = str_replace("{LANG|", "", $key);
				$key2 = str_replace("}", "", $key2);
				$title = $_lang_[$folder][$key2];
				if ($title == "") $title = $key;
				$array = split("[|]", $value);
				$tmp = new tpl("admin_menu_element");
				$tmp->set("title", $title);
				$tmp->set("module", $folder);
				$tmp->set("page", $array[0]);
				$tmp->set("do", $array[1]);
				$elements .= $tmp->html();
			}
			if ($elements <> "")
			{
				$tmp = new tpl("admin_menu_headline");
				$tmp->set("icon", is_file($_document_root . "/modules/$folder/icon_small.gif") ? 
					"../modules/$folder/icon_small.gif"
					:
					"../img/icon_default.gif"
					);
				$tmp->set("id", "Module" . $folder);
				$tmp->set("headline", module2title($folder));
				$tmp->set("elements", $elements);
				$tmp->set("show", $vars["module"] == $folder ? "1" : "");
				$array_groups["Module" . $folder] = $tmp->html();
			}
		}
	}
	
	if ($do == "clear_order")
	{
		// Ryd sortering for bruger
		
		$usr->extra_set("admin_menu_order", "");
		
		// Reload menu
		$html .= "<script> parent.parent.frames['menu_frame'].document.location.reload(); document.location.href = './?page=$page&do=order'; </script>";
		
	}
	elseif ($do == "order")
	{
		// Sorter menupunkter
		
		$msg = new message;
		$msg->title("{LANG|Sorter admin menu}");
		$html .= $msg->html();
		
		$links = new links;
		$links->link("{LANG|Nulstil sortering}", "clear_order", "", "{LANG|Er du sikker}?");
		$html .= $links->html();

		$tbl = new table("order");
		$tbl->row_dnd(true); // Drag'n'drop
		
		$tbl->th("{LANG|Menugruppe}");
		$tbl->endrow();
		
		// Finder aktuel sortering
		$array_current_order = array();
		$array_menu_order = split("[\n]", str_replace("\r", "", stripslashes($usr->extra_get("admin_menu_order"))));
		for ($i = 0; $i < count($array_menu_order); $i++)
		{
			if (isset($array_groups[$array_menu_order[$i]]))
			{
				$array_current_order[] = $array_menu_order[$i];
				unset($array_groups[$array_menu_order[$i]]);
			}
		}
		reset($array_groups);
		while (list($key, $value) = each($array_groups)) $array_current_order[] = $key;
		
		// Flyt rækker?
		if (list($move_id, $after_id) = $tbl->row_move())
		{
			$move_key = array_search($move_id, $array_current_order);
			if ($after_id != "")
			{
				// Skal placeres efter et andet element
				$after_key = array_search($after_id, $array_current_order);
				
				// Lægger en til alle andre nøgler
				if ($move_key < $after_key)
				{
					for ($i = $move_key; $i <= $after_key; $i++)
					{
						$array_current_order[$i] = $array_current_order[$i + 1];
					}
					$array_current_order[$after_key] = $move_id;
				}
				else
				{
					for ($i = $move_key - 1; $i > $after_key; $i--)
					{
						$array_current_order[$i + 1] = $array_current_order[$i];
					}
					$array_current_order[$after_key + 1] = $move_id;
				}
			}
			else
			{
				// Skal placeres i begyndelse af array
				for ($i = $move_key; $i > 0; $i--)
				{
					$array_current_order[$i] = $array_current_order[$i - 1];
				}
				$array_current_order[0] = $move_id;
			}
		}

		// Viser aktuel sortering
		for ($i = 0; $i < count($array_current_order); $i++)
		{
			$key = $array_current_order[$i];
			if ($key)
			{
				if (ereg("^Module(.+)$", $key, $tmparray))
				{
					$title = module2title($tmparray[1]);
				}
				elseif ($key == "General")
				{
					$title = $usr->data["username"];
				}
				elseif ($key == "Advanced")
				{
					$title = "{LANG|Avanceret}";
				}
				else
				{
					$title = $key;
				}
				
				$tbl->row_id($key);
				$tbl->td($title);
				$tbl->endrow();
			}
		}
		
		// Gemmer evt
		if ($move_id)
		{
			$tmp = "";
			for ($i = 0; $i < count($array_current_order); $i++)
			{
				if ($array_current_order[$i])
				{
					if ($tmp != "") $tmp .= "\r\n";
					$tmp .= $array_current_order[$i];
				}
			}
			$usr->extra_set("admin_menu_order", $tmp);
		}
		
		$html .= $tbl->html();
		
		// Reload evt. menu
		if ($move_id) $html .= "<script> parent.parent.frames['menu_frame'].document.location.reload(); </script>";
		
	}
	else
	{
		// Vis menu
	
		// Ajax
		$a = new ajax;
		if ($a->do == "time")
		{
			include($_document_root . "/Admin/pages/bot.php");
			
			$a->response(array(
				"state" => "ok",
				"time" => date("d-m-Y H:i")
				));
		}
		$html .= $a->html();
		
		// Viser menu baseret på brugerens sortering
		$array_menu_order = split("[\n]", str_replace("\r", "", stripslashes($usr->extra_get("admin_menu_order"))));
		for ($i = 0; $i < count($array_menu_order); $i++)
		{
			if (isset($array_groups[$array_menu_order[$i]]))
			{
				$html .= $array_groups[$array_menu_order[$i]];
				unset($array_groups[$array_menu_order[$i]]);
			}
		}
		reset($array_groups);
		while (list($key, $value) = each($array_groups)) $html .= $value;
	
		$tpl = "menu";
	}
?>
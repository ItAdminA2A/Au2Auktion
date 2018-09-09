<?php
	$id = $vars["id"];
	$data = trim($vars["data"]);

	if ($do == "delete")
	{
		// Slet element
		$rules = split("[\n]", str_replace("\r", "", $data));
		$data = "";
		for ($i = 0; $i < count($rules); $i++)
		{
			if ($vars["i"] != $i) $data .= $rules[$i] . "\r\n";
		}
		$do = "edit";
		$data = trim($data);
	}
	elseif ($do == "move_up" or $do == "move_down")
	{
		// Flyt element
		$rules = split("[\n]", str_replace("\r", "", $data));
		$data = "";
		$tmp = "";
		for ($i = 0; $i < count($rules); $i++)
		{
			if ($do == "move_up" && $vars["i"] == $i + 1)
			{
				$tmp = $rules[$i];
			}
			elseif ($do == "move_down" && $vars["i"] == $i)
			{
				$tmp = $rules[$i];
			}
			else
			{
				$data .= $rules[$i] . "\r\n";
				if ($tmp != "")
				{
					$data .= $tmp . "\r\n";
					$tmp = "";
				}
			}
		}
		if ($tmp != "") $data .= $tmp;
		$do = "edit";
		$data = trim($data);
	}
	
	if ($do == "")
	{
		// Hent regler fra parent vindue
	
		$html = "
			<form action=\"./\" method=\"post\" id=\"MyForm\">
			<input type=\"hidden\" name=\"page\" value=\"$page\">
			<input type=\"hidden\" name=\"id\" value=\"$id\">
			<input type=\"hidden\" name=\"do\" value=\"edit\">
			<input type=\"hidden\" name=\"data\" id=\"MyData\" value=\"\">
			<input type=\"hidden\" name=\"rule_type\" value=\"" . $vars["rule_type"] . "\">
			</form>
			<script type=\"text/javascript\">
			try
			{
				document.getElementById('MyData').value = window.opener.document.getElementById('$id').value;
				document.getElementById('MyForm').submit();
			}
			catch(e)
			{
				// dummy
				document.write('Fejl opstået - kontroller at du ikke har lukket administrationen');
			}
			</script>
			";
		
	}
	elseif ($do == "edit")
	{
		// Vis regel editor

		$msg = new message;
		$msg->title("{LANG|Regel editor}");
		$html .= $msg->html();
		
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

		// Opbygger array med regler		
		$elements = array();
		
		if ($vars["rule_type"] == "admin")
		{
			// Admin regler
			
			// Henter sider
			$pages = pages_array(true);
		
			// Gennemløber sider
			$elements["|pages||"] = "{LANG|Menu oversigt}";
			for ($i = 0; $i < count($pages); $i++)
			{
				$elements["|pages|edit|" . $pages[$i]["id"]] = " &nbsp; &nbsp; &nbsp; " . $pages[$i]["title"];
			}
			
			// Henter admin menuer
			$array1 = admin_module_menu();
			reset($array1);
			while (list($folder, $menu) = each($array1))
			{
				if (count($menu) > 0)
				{
					$elements[$folder . "|*|*|*"] = module2title($folder) . " ({LANG|Alt})";
					reset($menu);
					while (list($key, $value) = each($menu))
					{
						$key2 = str_replace("{LANG|", "", $key);
						$key2 = str_replace("}", "", $key2);
						$title = $_lang_[$folder][$key2];
						if ($title == "") $title = $key;
						list($tmp_page, $tmp_do) = split("[|]", $value);
						if ($tmp_do == "") $tmp_do = "*";
						$elements[$folder . "|$tmp_page|$tmp_do|*"] = " &nbsp; &nbsp; &nbsp; " . $title;
					}
				}
			}
			
		}
		else
		{
			// Side regler
				
			// Henter sider
			$pages = pages_array(true);
		
			// Gennemløber sider
			for ($i = 0; $i < count($pages); $i++)
			{
				$elements["|default|*|" . $pages[$i]["id"]] = 
					"{LANG|Sider} - " . ($array_langs[$pages[$i]["lang_id"]] <> "" ? $array_langs[$pages[$i]["lang_id"]] : $pages[$i]["lang_id"]) . " - " . $pages[$i]["title"];
			}
			
			// Henter elementer
			$modules = module_elements();
			
			// Laver liste med elementer
			reset($modules);
			while (list($tmp_module, $tmp_array) = each($modules))
			{
				$elements[$tmp_module . "|*|*|*"] = $tmp_module . " ({LANG|Alt})";
				reset($tmp_array);
				while (list($key, $value) = each($tmp_array))
				{
					list($tmp_page, $tmp_do, $tmp_id) = split("[\|]", $value);
					if ($tmp_do == "") $tmp_do = "*";
					if ($tmp_id == "") $tmp_id = "*";
					$elements[$tmp_module . "|" . $tmp_page . "|" . $tmp_do . "|" . $tmp_id] = module2title($tmp_module) . " - " . $key;
				}
			}
		}
		
		// Laver select-liste
		$select_elements = array(array("ALL", "{LANG|Alt}"));
		reset($elements);
		while (list($rule, $element) = each($elements))
		{
			if ($rule != "" && $element != "") $select_elements[count($select_elements)] = array($rule, $element);
		}
		
		$frm = new form;
		$frm->tpl("th", "{LANG|Tilføj regel}");
		$frm->select(
			"{LANG|Regel}",
			"add_rule",
			"",
			"^.+$",
			"",
			"",
			array(
				array("ALLOW", "{LANG|Tillad}"),
				array("DENY", "{LANG|Forbyd}")
				)
			);
		$frm->select(
			"{LANG|Element}",
			"add_element",
			"",
			"",
			"",
			"",
			$select_elements
			);
				
		if ($frm->done())
		{
			// Tilføj regel
			$data .= "\r\n" . $frm->values["add_rule"] . " " . $frm->values["add_element"];
			$data = trim($data);
		}
		
		$frm->hidden("data", $data);
		$frm->hidden("rule_type", $vars["rule_type"]);
		$html .= $frm->html();
		
		
		// Eksisterende regler
		$rules = split("[\n]", str_replace("\r", "", $data));
		
		$tbl = new table;
		$tbl->th("{LANG|Regel}");
		$tbl->th("{LANG|Element}");
		$tbl->th("{LANG|Sortering}", 2);
		$tbl->th("{LANG|Slet}");
		$tbl->endrow();
		
		for ($i = 0; $i < count($rules); $i++)
		{
			if (ereg("^(ALLOW|DENY) (.+)$", $rules[$i], $array))
			{
				$tbl->td($array[1] == "ALLOW" ? "{LANG|Tillad}" : "{LANG|Forbyd}");
				if (isset($elements[$array[2]]))
				{
					$tbl->td($elements[$array[2]]);
				}
				else
				{
					$tbl->td($array[2]);
				}
				$tbl->choise("{LANG|Flyt op}", "move_up", $id . "&i=" . $i . "&data=" . urlencode($data) . "&rule_type=" . $vars["rule_type"]);
				$tbl->choise("{LANG|Flyt ned}", "move_down", $id . "&i=" . $i . "&data=" . urlencode($data) . "&rule_type=" . $vars["rule_type"]);
				$tbl->choise("{LANG|Slet}", "delete", $id . "&i=" . $i . "&data=" . urlencode($data) . "&rule_type=" . $vars["rule_type"]);
				$tbl->endrow();
			}
		}
		
		$html .= $tbl->html();
		
		$html .= "
			<script type=\"text/javascript\">
			try
			{
				window.opener.document.getElementById('$id').value = document.getElementById('data').value;
			}
			catch(e)
			{
				alert('Fejl opstået - kontroller at du ikke har lukket administrationen');
				close();
			}
			</script>
			";
		
	}	

	$tpl = "popup";
?>
<?php
	exit;

	/*COPYRIGHT*\
		COPYRIGHT STADEL.DK 2006
		
		AL KODE I DENNE FIL TILHØRER STADEL.DK, THOMAS@STADEL.DK.
		KODEN MÅ UNDER INGEN  OMSTÆNDIGHEDER  BENYTTES  TIL ANDET
		FORMÅL END  DET DEN ER KØB TIL.  KODEN MÅ IKKE  ÆNDRES AF
		ANDRE   END   STADEL.DK.   KODEN  MÅ  IKKE  SÆLGES  ELLER
		VIDEREDISTRIBUERES  HELT, DELVIS ELLER SOM EN KOPI AF DET
		SYSTEM   DET  OPRINDELIGT  ER  KØBT  SAMMEN  MED.  ENHVER
		OVERTRÆDELSE  AF EN ELLER FLERE AF DE NÆVNTE  BETINGELSER
		VIL RESULTERE I RETSFORFØLGELSE OG ERSTATNING FOR BRUD PÅ
		OPHAVSRETTEN AF KODEN, IFLG.  DANSK  OPHAVSRETSLOV. DENNE
		COPYRIGHT    MEDDELELSE    MÅ    DESUDEN    UNDER   INGEN
		OMSTÆNDIGHEDER FJERNES FRA DENNE FIL.
	
		ALL   CODE  IN  THIS  FILE  ARE  COPYRIGHTED   STADEL.DK,
		THOMAS@STADEL.DK.  IT'S NOT  ALLOWED TO USE THIS CODE FOR 
		ANY OTHER PURPOSE  THAN TOGEHTER  WITH THE ORGINAL SCRIPT 
		AS IT HAS BEEN  BOUGHT  AS A PART OF. IT'S NOT ALLOWED TO 
		SELL OR REDISTRIBUTE  THE CODE IN IT'S COMPLETE SENTENCE,
		ANY  PART OF THE  CODE OR AS A PART OF ANOTHER  SYSTEM OR 
		SCRIPT.  ANY  VIOLATION  OF  THESE  RULES  WILL RESULT IN 
		PROSECUTION   AND   COMPENSATION  FOR  VIOLATION  OF  THE 
		COPYRIGHT OF THIS SYSTEM,  SCRIPT AND CODE,  ACCORDING TO 
		DANISH  COPYRIGHT LAW. THIS  COPYRIGHT  MAY  NOT,  IN ANY 
		CIRCUMSTANCE, BE REMOVED FROM THIS FILE.
	\*COPYRIGHT*/

	/*
		Version:		29-11-2006
		Beskrivelse:	Editor til at redigere CSS stylesheets
	*/
	
	// Arrays med fortolkninger af CSS-navne
	include($_document_root . "/Admin/pages/css_editor_types.php");
	
	// Tjekker at filen ikke kaldes direkte
	if ($page <> "layouts" and $page <> "modules" and $page <> "cms" and $page <> "css") die("Denne fil må ikke kaldes direkte !");
	$filegroup = $page;
	if ($filegroup == "css")
	{
		$vars["file"] = "default.css";
		if ($vars["mod"] != "")
		{
			// Modul
			$id = $vars["mod"];
			$filegroup = "modules";
		}
		else
		{
			// CMS grundsystem - standard layout
			$id = $_settings_["SITE_LAYOUT"];
			$filegroup = "layouts";
		}
	}

	// Tjekker filnavn	
	$file = $vars["file"];
	if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}css$", $file) or !is_file($_document_root . "/$filegroup/$id/css/$file"))
	{
		header("Location: ?page=$page&do=edit&id=$id");
		exit;
	}
	
	// Åbner stylesheet
	$data = "";
	if ($fp = fopen($_document_root . "/$filegroup/$id/css/$file", "r"))
	{
		while (!feof($fp)) $data .= fread($fp, 1024);
		fclose($fp);
	}
	else
	{
		errorhandler("Kunne ikke indlæse Stylesheet");
	}
	
	// Gemmer kopi af original CSS
	$orig_data = $data;
	
	// Erstatter {MODULE}
	$data = str_replace("{MODULE}", "__MODULE__", $data);
	
	// Aktuel gruppe
	$active_group = $vars["next_group"] <> "" ? $vars["next_group"] : $vars["group"];
	$array_group_titles = array();
	
	// Indlæser CSS-data
	$css = array();
	while (eregi("([^\r^\n^{^}]+)[\r\n\t]*{([^}]+)}", $data, $array))
	{
		// Fjerner fra CSS
		$data = str_replace($array[0], "", $data);

		// Elementer
		$elements = split("[;]", $array[2]);
		$tmpelements = array();
		for ($i = 0; $i < count($elements); $i++)
		{
			// Splitter i tag og værdi
			if (eregi("^([^:]+):(.+)$", $elements[$i], $tmparray))
			{
				$key = trim($tmparray[1]);
				$value = trim($tmparray[2]);
				if ($key <> "")
				{
					$tmpelements[$key] = $value . " ";
				}
				// Findes denne i array_elements allerede ?
				if (!array_key_exists($key, $array_elements))
				{
					$array_elements[$key] = $key . " (ukendt)";
					$array_values[$key] = array("text");
				}
			}
		}
		$elements = $tmpelements;
		
		// Grupper
		$groups = array($array[1]);
		$group_title = "";
		for ($i = 0; $i < count($groups); $i++)
		{
			$tmpgroup = trim($groups[$i]);
			if ($tmpgroup <> "")
			{
				// Titel
				$tmptitle = $tmpgroup;
				if (eregi("^(.+)/\*(.+)\*/$", $tmptitle, $tmparray))
				{
					$tmpgroup = trim($tmparray[1]);
					$tmptitle = trim($tmparray[2]);
					$array_group_titles[$tmpgroup] = trim($tmparray[0]);
				}
				else
				{
					$array_group_titles[$tmpgroup] = $tmpgroup;
				}
				
				// Erstatter __MODULE__
				$array_group_titles[$tmpgroup] = str_replace("__MODULE__", "{MODULE}", $array_group_titles[$tmpgroup]);
				
				$css[$tmpgroup] = $elements;
				
				// Findes denne i array_groups allerede ?
				if (!array_key_exists($tmpgroup, $array_groups))
				{
					$array_groups[$tmpgroup] = $tmptitle;
				}
			}
		}
	}
	
	// Gennemløber alle grupper
	reset($array_groups);
	$tabs = "";
	$active_title = "";
	$css_new = "";
	while (list($group, $group_title) = each($array_groups))
	{
		// Laver ny CSS, hvis POST
		if ($vars["do2"] == "save") // and $vars["group"] == $group)
		{
			reset($array_elements);
			$elements_new = "";
			while (list($element, $element_title) = each($array_elements))
			{
				if ($vars["group"] == $group)
				{
					$value = stripslashes(trim($vars[$element . "_0"] . $vars[$element . "_0_"] . " " . $vars[$element . "_1"] . $vars[$element . "_1_"] . " " . $vars[$element . "_2"] . $vars[$element . "_2_"]));
					$value = str_replace("{", "", $value);
					$value = str_replace("}", "", $value);
					$value = str_replace(";", "", $value);
				}
				else
				{
					$value = $css[$group][$element];
				}
				if ($css[$group][$element] <> "")
				{
					if ($value == "px" or $value == "pt" or $value == "%") $value = "";
					$elements_new .= "\t" . $element . ": " . trim($value) . ";\r\n";
				}
			}
			if (trim($group) != "" and trim($elements_new) != "")
			{
				if ($css_new != "") $css_new .= "\r\n";
				$css_new .= $array_group_titles[$group] . "\r\n{\r\n" . $elements_new . "}";
			}
		}
		
		// Viser tab
		if (count($css[$group]) > 0)
		{
			if ($active_group == "") $active_group = $group;
			$tmp = new tpl("admin_css_editor_tab");
			$tmp->set("group", $group);
			$tmp->set("title", $group_title);
			if ($group == $active_group)
			{
				$tmp->set("active", "1");
				$active_title = $group_title;
			}
			$tabs .= $tmp->html();
		}
	}
	
	if ($vars["do2"] == "save")
	{
		// Erstatter ændringer i oprindelig CSS
		
		/*
		Buggy:
		$pos1 = strpos($orig_data, $array_group_titles[$vars["group"]]);
		$pos2 = strpos($orig_data, "}", $pos1 + strlen($array_group_titles[$vars["group"]]));
		$css_new = substr($orig_data, 0, $pos1) . $css_new . substr($orig_data, $pos2 + 1);

		Med preg:		
		$css_new = trim(preg_replace("/([^a-z]+)" . $array_group_titles[$vars["group"]] . "[\r\n\t]*\{[^\}]*\}/", "\\1" . $css_new, " " . $orig_data));
		*/
		
		// Gemmer evt. backup hvis ikke det allerede er gjort
		if (substr($file, 0, 10) != "Backup_af_" and !is_file($_document_root . "/$filegroup/$id/css/Backup_af_" . $file))
		{
			copy($_document_root . "/$filegroup/$id/css/$file", $_document_root . "/$filegroup/$id/css/Backup_af_" . $file);
		}
		
		// Gemmer CSS
		if ($fp = fopen($_document_root . "/$filegroup/$id/css/$file", "w"))
		{
			fwrite($fp, $css_new);
			fclose($fp);
			// Videre
			header("Location: ?module=$module&page=$page&do=$do&id=$id&file=$file&mod=" . urlencode($vars["mod"]) . "&group=" . urlencode($active_group));
			exit;
		}
		else
		{
			die("Kunne ikke gemme Stylesheet");
		}
	}
	
	// Viser aktuel gruppe
	reset($array_elements);
	$elements = "";
	$elements_inactive = "";
	$i = 0;
	while (list($element, $element_title) = each($array_elements))
	{
		// Værdier
		$values = "";
		$array = $array_values[$element];
		$val = trim($css[$active_group][$element]);
		if (count($array) > 1)
		{
			$val = split("[ ]", $val);
		}
		else
		{
			$val = array($val);
		}
		$has_value = ($css[$active_group][$element] <> "");
		for ($i = 0; $i < count($array); $i++)
		{
			if (is_array($array[$i]))
			{
				// Bestemte værdier
				$tmp = new tpl("admin_css_editor_select_header");
				$tmp->set("element", $element);
				$tmp->set("i", $i);
				$values .= $tmp->html();
				// Gennemløber værdier
				$array2 = $array[$i];
				for ($i2 = 0; $i2 < count($array2); $i2++)
				{
					$tmp = new tpl("admin_css_editor_select_option");
					$tmp->set("value", $array2[$i2]);
					$tmp->set("option", $array_texts[$array2[$i2]]);
					if ($val[$i] == $array2[$i2]) $tmp->set("selected", "selected");
					$values .= $tmp->html();
				}
				// Footer
				$tmp = new tpl("admin_css_editor_select_footer");
				$values .= $tmp->html();
			}
			elseif ($array[$i] == "text")
			{
				// Tekst - skal indeholde alle elementer i teksten
				$tmp = new tpl("admin_css_editor_" . $array[$i]);
				$tmp->set("element", $element);
				$tmp->set("i", $i);
				$tmp->set("value", trim($css[$active_group][$element]));
				$values .= $tmp->html();
				$i = count($array);
			}
			else
			{
				// Bestemt type
				$tmp = new tpl("admin_css_editor_" . $array[$i]);
				$tmp->set("element", $element);
				$tmp->set("i", $i);
				$tmp->set("value", eregi_replace("(px|pt|%)$", "", $val[$i]));
				$tmp->set("selected_" . eregi_replace("^.+(px|pt|%)$", "\\1", $val[$i]), "selected");
				$values .= $tmp->html();
			}
		}
		// Viser element
		$tmp = new tpl("admin_css_editor_element");
		$tmp->set("element", $element);
		$tmp->set("title", $element_title);
		$tmp->set("values", $values);
		if ($has_value)
		{
			$elements .= $tmp->html();
		}
		else
		{
			$elements_inactive .= $tmp->html();
		}
	}
	
	// Viser siden
	$tmp = new tpl("admin_css_editor");
	$tmp->set("mod", $vars["mod"]);
	$tmp->set("id", $id);
	$tmp->set("file", $file);
	$tmp->set("tabs", $tabs);
	$tmp->set("group", $active_group);
	$tmp->set("elements", $elements);
	$tmp->set("title", $active_title);
	$html .= $tmp->html();
?>
<?php
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
		Beskrivelse:	Oversigt over menuer
	*/
	
	// Henter elementer
	$elements = module_elements();
	
	// Henter sprog
	$menu_lang = "";
	$menu_title = "";
	$array_langs = array();
	$select_langs = array();
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
		if ($menu_lang == "" or $vars["menu_lang"] == $db->array["id"])
		{
			$menu_lang = $db->array["id"];
			$menu_title = stripslashes($db->array["title"]);
		}
		$array_langs[$db->array["id"]] = stripslashes($db->array["title"]);
		$select_langs[count($select_langs)] = array($db->array["id"], stripslashes($db->array["title"]));
	}
	
	// Liste med bruger-grupper
	$array_user_groups = array(array("", "-"));
	$array = $usr->admin_get_groups();
	for ($i = 0; $i < count($array); $i++)
	{
		$array_user_groups[count($array_user_groups)] = array($array[$i] . "|logged_in", module2title($array[$i]) . " ({LANG|logget ind})");
		$array_user_groups[count($array_user_groups)] = array($array[$i] . "|logged_out", module2title($array[$i]) . " ({LANG|logget ud})");
		
		// Grupper under denne bruger-gruppe
		if (preg_match("/^Brugere/", $array[$i]))
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $array[$i] . "_groups
				ORDER BY
					title
				");
			while ($db->fetch_array())
			{
				$array_user_groups[count($array_user_groups)] = array($array[$i] . "|logged_in|" . $db->array["id"], module2title($array[$i]) . " (" . stripslashes($db->array["title"]) . ") ({LANG|logget ind})");
			}
		}
	}
	
	// Liste med modul-menuer
	$sitemenu_array = sitemenu_array();
	$array_sitemenu = array("" => "-");
	$select_sitemenu = array(array("", "-"));
	reset($sitemenu_array);
	while (list($tmpmodule, $tmparray) = each($sitemenu_array))
	{
		reset($tmparray);
		while (list($tmpdo, $tmptitle) = each($tmparray))
		{
			$array_sitemenu[$tmpmodule . "|" . $tmpdo] = module2title($tmpmodule) . " - " . $tmptitle;
			$select_sitemenu[] = array($tmpmodule . "|" . $tmpdo, module2title($tmpmodule) . " - " . $tmptitle);
		}
	}
	
	// Henter liste med layout-filer i aktuelle layout
	$file = new file;
	$files = $file->find_files($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/");
	$select_layouts = array(array("default", "default"));
	for ($i = 0; $i < count($files); $i++)
	{
		if (!ereg("^menu_", $files[$i]) and $files[$i] <> "default.html" and ereg("\.html$", $files[$i]))
		{
			$filename = ereg_replace("\.html$", "", $files[$i]);
			$select_layouts[count($select_layouts)] = array	($filename, $filename);
		}
	}
	
	// Overskrift
	$msg = new message;
	$msg->title("{LANG|Menu}" . ($menu_title != "" ? (" - " . $menu_title) : ""));
	$html .= $msg->html();
	
	if ($do == "add")
	{
		//
		// Opret side
		//
		
		// Laver array med sider
		$pages_array = array(array(0, "-"));
		$tmp_array = pages_array(true, 0, "", $menu_lang);
		for ($i = 0; $i < count($tmp_array); $i++)
		{
			$res = $tmp_array[$i];
			$pages_array[count($pages_array)] = array(
				$res["id"],
				stripslashes($res["title"])
				);
		}
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "&menu_lang=" . $menu_lang);
		$html .= $links->html();

		// Formular
		$frm = new form;
		$frm->hidden("lang_id", $menu_lang);
		$frm->submit_text = "{LANG|Opret menupunkt}";
		$frm->tpl("th", "{LANG|Opret menupunkt}");
		$frm->tpl("td2", "{LANG|Sprog}:", $array_langs[$menu_lang]);
		$frm->select(
			"{LANG|Placer under}",
			"sub_id",
			0,
			"",
			"",
			"",
			$pages_array
			);
		$frm->input(
			"{LANG|Titel}",
			"title",
			"",
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->checkbox(
			"{LANG|Vis i menu}",
			"active",
			false
			);
		$frm->checkbox(
			"{LANG|Vis i linkoversigt}",
			"link",
			false
			);
		if ($frm->done())
		{
			// Opret side
			$db->execute("
				INSERT INTO
					" . $_table_prefix . "_pages_
				(
					title,
					active,
					`link`,
					public,
					sub_id,
					lang_id,
					edit_mode,
					`layout`
				)
				VALUES
				(
					'" . $db->escape($frm->values["title"]) . "',
					'" . ($frm->values["active"] != "" ? 1 : 0) . "',
					'" . ($frm->values["link"] != "" ? 1 : 0) . "',
					'1',
					'" . intval($frm->values["sub_id"]) . "',
					'" . $db->escape($frm->values["lang_id"]) . "',
					'advanced',
					''
				)
				");
			$id = $db->insert_id();
			$db->execute("
				UPDATE
					" . $_table_prefix . "_pages_
				SET
					" . $_table_prefix . "_pages_.order = '$id'
				WHERE
					id = '$id'
				");
				
			// Henter parent titel
			$parent_title = "";
			$sub_id = intval($frm->values["sub_id"]);
			while ($sub_id > 0)
			{
				$db->execute("
					SELECT
						sub_id,
						title
					FROM
						" . $_table_prefix . "_pages_
					WHERE
						id = '$sub_id'
					");
				if ($res = $db->fetch_array())
				{
					$parent_title = stripslashes($res["title"]) . "/" . $parent_title;
					$sub_id = $res["sub_id"];
				}
				else
				{
					$sub_id = 0;
				}
			}
			
			// Opretter smart url
			create_smart_url($parent_title . $frm->values["title"], "/site/" . $frm->values["lang_id"] . "////" . $id);
			
			header("Location: ?page=$page&do=edit&id=$id&menu_lang=" . $frm->values["lang_id"]);
			exit;
		}
		
		$html .= $frm->html();
				
		
	}
	elseif ($do == "edit")
	{
		//
		// Rediger side
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "&menu_lang=" . $menu_lang);
		$html .= $links->html();
		
		// Henter side
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_pages_
			WHERE
				id = '$id'
			");
		if (!$res = $db->fetch_array())
		{
			// Tilbage
			header("Location: ?page=$page&menu_lang=" . $menu_lang);
			exit;
		}
		
		// Sætter menu-lang
		$menu_lang = $res["lang_id"];
		
		// Skift edit mode
		if (ereg("^(simple|advanced)$", $vars["edit_mode"]))
		{
			// Opdater
			$db->execute("
				UPDATE
					" . $_table_prefix . "_pages_
				SET
					edit_mode = '" . $db->escape($vars["edit_mode"]) . "'
				WHERE
					id = '$id'
				");
			// Videre
			header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
			exit;
		}
		
		if ($res["edit_mode"] == "simple")
		{
			// Simpel
				
			// Fjern element
			if ($vars["do2"] == "remove")
			{
				// Fjerner element
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						content = REPLACE(content, '{" . $db->escape($vars["element"]) . "}', '')
					WHERE
						id = '$id'
					");
				// Videre
				header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
				exit;
			}
			
			// Flyt op
			if ($vars["do2"] == "move_up")
			{
				$new_elements = "";
				$parent_element = "";
				$content = $res["content"];
				while (ereg("\{MODULE\|([^\|]+)\|([^\}]+)\}", $content, $array))
				{
					// Fjerner fra content
					$content = str_replace($array[0], "", $content);
					// Henter parametre
					$tmp_module = $array[1];
					$value = $array[2];
					if ($vars["element"] == "MODULE|" . $tmp_module . "|" . $value)
					{
						// Flyt element op
						$new_elements = $new_elements . "{MODULE|" . $tmp_module . "|" . $value . "}";
					}
					if ($parent_element <> "")
					{
						$new_elements .= "{" . $parent_element . "}";
					}
					if ($vars["element"] == "MODULE|" . $tmp_module . "|" . $value)
					{
						$parent_element = "";
					}
					else
					{
						$parent_element = "MODULE|" . $tmp_module . "|" . $value;
					}
				}
				if ($parent_element <> "") $new_elements .= "{" . $parent_element . "}";
				
				// Gemmer
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						content = '" . $db->escape($new_elements . $content) . "'
					WHERE
						id = '$id'
					");
					
				// Videre
				header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
				exit;
			}
			
			// Flyt new
			if ($vars["do2"] == "move_down")
			{
				$new_elements = "";
				$after_next_element = "";
				$content = $res["content"];
				while (ereg("\{MODULE\|([^\|]+)\|([^\}]+)\}", $content, $array))
				{
					// Fjerner fra content
					$content = str_replace($array[0], "", $content);
					// Henter parametre
					$tmp_module = $array[1];
					$value = $array[2];
					if ($vars["element"] == "MODULE|" . $tmp_module . "|" . $value)
					{
						// Flyt element ned
						$after_next_element = "{MODULE|" . $tmp_module . "|" . $value . "}";
					}
					else
					{
						$new_elements .= "{MODULE|" . $tmp_module . "|" . $value . "}";
						if ($after_next_element <> "")
						{
							$new_elements .= "{" . $after_next_element . "}";
							$after_next_element = "";
						}
					}
				}
				if ($after_next_element <> "") $new_elements .= "{" . $after_next_element . "}";
				// Gemmer
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						content = '" . $db->escape($new_elements . $content) . "'
					WHERE
						id = '$id'
					");
				// Videre
				header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
				exit;
			}
		}
		
		// Laver select-array med elementer
		$select_elements = array();
		reset($elements);
		while (list($tmp_module, $tmp_array) = each($elements))
		{
			reset($tmp_array);
			while (list($key, $value) = each($tmp_array))
			{
				if (strpos(" " . $res["content"], "{MODULE|" . $tmp_module . "|" . $value . "}") === false)
				{
					$select_elements[count($select_elements)] = array(
						"MODULE|" . $tmp_module . "|" . $value, module2title($tmp_module) . " - " . $key
						);
				}
			}
		}
		
		// Laver array med sider
		$sub_id = $res["sub_id"];
		$pages_array = array(array(0, "-"));
		$tmp_array = pages_array(true, 0, "", $menu_lang, $id);
		for ($i = 0; $i < count($tmp_array); $i++)
		{
			$res2 = $tmp_array[$i];
			if ($vars["sub_id"] == $res2["id"]) $sub_id = $res2["id"];
			$pages_array[count($pages_array)] = array(
				$res2["id"],
				stripslashes($res2["title"])
				);
		}
		
		// Formular
		$frm = new form;
		$frm->submit_text = "{LANG|Gem ændringer}";
		$frm->tab("{LANG|Rediger side}");
		$frm->tpl("td2", "{LANG|Sprog}:", $array_langs[$res["lang_id"]] <> "" ? $array_langs[$res["lang_id"]] : $res["lang_id"]);
		
		$smart_url = $_site_url . get_smart_url("/site/" . $res["lang_id"] . "////" . $id);
		$frm->tpl("td2", "{LANG|Smart URL}:", "<a href=\"$smart_url\" onclick=\"return false;\">$smart_url</a>");
		$frm->checkbox("{LANG|Opdater smart URL}", "update_smart_url");
		
		$frm->select(
			"{LANG|Placer under}",
			"sub_id",
			$res["sub_id"],
			"",
			"",
			"",
			$pages_array
			);
		$frm->input(
			"{LANG|Titel}",
			"title",
			stripslashes($res["title"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->checkbox(
			"{LANG|Vis i menu}",
			"active",
			$res["active"] == 1
			);
		$frm->checkbox(
			"{LANG|Vis i linkoversigt}",
			"link",
			$res["link"] == 1
			);
		$frm->checkbox(
			"{LANG|Brug som forside}",
			"frontpage",
			$res["frontpage"] == 1
			);
			
		$frm->tab("{LANG|Udvidede indstillinger}");
		
		$frm->input(
			"{LANG|Viderestil til} (URL)",
			"alt_url",
			$res["alt_url"]
			);
		$frm->checkbox(
			"{LANG|Ikke klikbart}",
			"no_link",
			$res["no_link"] == 1
			);
		
		$frm->input(
			"{LANG|Under-titel}",
			"sub_title",
			stripslashes($res["sub_title"])
			);
		$frm->checkbox(
			"{LANG|Publiceret}",
			"public",
			$res["public"] == 1
			);
		$frm->input(
			"{LANG|Start-dato og tid} ({LANG|kan undlades})",
			"time_from",
			$res["time_from"] <> "" ? date("d-m-Y H:i", strtotime($res["time_from"])) : "",
			"^([0-9]{1,2}[/-]{1}[0-9]{1,2}[/-]{1}[0-9]{2,4} [0-9]{1,2}[:\./-]{1}[0-9]{1,2}){0,1}$",
			"{LANG|Ugyldigt format}. {LANG|Formatet skal være dd-mm-åååå} tt:mm, {LANG|feks}. 31-12-2005 15:45"
			);
		$frm->input(
			"{LANG|Slut-dato og tid} ({LANG|kan undlades})",
			"time_to",
			$res["time_to"] <> "" ? date("d-m-Y H:i", strtotime($res["time_to"])) : "",
			"^([0-9]{1,2}[/-]{1}[0-9]{1,2}[/-]{1}[0-9]{2,4} [0-9]{1,2}[:\./-]{1}[0-9]{1,2}){0,1}$",
			"{LANG|Ugyldigt format}. {LANG|Formatet skal være dd-mm-åååå} tt:mm, {LANG|feks}. 31-12-2005 15:45"
			);
		$frm->select(
			"{LANG|Brugergruppe}",
			"user_group",
			$res["user_group"],
			"",
			"",
			"",
			$array_user_groups
			);
		$frm->combo(
			"{LANG|Anvend dette layout}",
			"layout",
			$res["layout"],
			"",
			"",
			"",
			$select_layouts
			);
		$frm->select(
			"{LANG|Undermenu}",
			"sub_menu",
			$res["sub_menu"],
			"",
			"",
			"",
			$select_sitemenu
			);
			
		$frm->tab("{LANG|Søgeoptimering}");
		$frm->tpl("td", "{LANG|Felterne herunder kan undlades}, " .
			"{LANG|hvorved titel og beskrivelse fra Indstillinger benyttes}.");
		$frm->input(
			"{LANG|Titel}",
			"meta_title",
			stripslashes($res["meta_title"])
			);
		$frm->textarea(
			"{LANG|Beskrivelse}",
			"meta_description",
			stripslashes($res["meta_description"]),
			"",
			"",
			"",
			35,
			3
			);
		$frm->textarea(
			"{LANG|Søgeord - et pr. linie}",
			"meta_keywords",
			str_replace(",", "\r\n", stripslashes($res["meta_keywords"])),
			"",
			"",
			"",
			35,
			3
			);
			
		if ($frm->done())
		{
			if (intval($frm->values["sub_id"]) == 0) $sub_id = 0;
			
			// Konverterer datoer
			$cnv = new convert;
			$time_from = $frm->values["time_from"];
			if ($time_from <> "")
			{
				$time_from = "'" . $cnv->date_dk2uk($time_from) . "'";
			}
			else
			{
				$time_from = "NULL";
			}
			$time_to = $frm->values["time_to"];
			if ($time_to <> "")
			{
				$time_to = "'" . $cnv->date_dk2uk($time_to) . "'";
			}
			else
			{
				$time_to = "NULL";
			}
			// Gemmer ændringer
			if ($frm->values["frontpage"] <> "")
			{
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						frontpage = 0
					WHERE
						lang_id = '" . $res["lang_id"] . "'
					");
			}
			$db->execute("
				UPDATE
					" . $_table_prefix . "_pages_
				SET
					title = '" . $db->escape($frm->values["title"]) . "',
					sub_title = '" . $db->escape($frm->values["sub_title"]) . "',
					time_from = $time_from,
					time_to = $time_to,
					active = '" . ($frm->values["active"] <> "" ? 1 : 0) . "',
					link = '" . ($frm->values["link"] <> "" ? 1 : 0) . "',
					public = '" . ($frm->values["public"] <> "" ? 1 : 0) . "',
					frontpage = '" . ($frm->values["frontpage"] <> "" ? 1 : 0) . "',
					layout = '" . $db->escape($frm->values["layout"]) . "',
					user_group = '" . $db->escape($frm->values["user_group"]) . "',
					meta_title = '" . $db->escape($frm->values["meta_title"]) . "',
					meta_description = '" . $db->escape($frm->values["meta_description"]) . "',
					meta_keywords = '" . $db->escape(str_replace("\n", ",", str_replace("\r", "", trim($frm->values["meta_keywords"])))) . "',
					sub_id = '$sub_id',
					sub_menu = '" . $db->escape($frm->values["sub_menu"]) . "',
					alt_url = '" . $db->escape($frm->values["alt_url"]) . "',
					no_link = '" . ($frm->values["no_link"] != "" ? 1 : 0) . "'
				WHERE
					id = '$id'
				");
			
			if ($frm->values["update_smart_url"] != "")
			{
				// Henter parent titel
				$parent_title = "";
				while ($sub_id > 0)
				{
					$db->execute("
						SELECT
							sub_id,
							title
						FROM
							" . $_table_prefix . "_pages_
						WHERE
							id = '$sub_id'
						");
					if ($res2 = $db->fetch_array())
					{
						$parent_title = stripslashes($res2["title"]) . "/" . $parent_title;
						$sub_id = $res2["sub_id"];
					}
					else
					{
						$sub_id = 0;
					}
				}
				
				// Sletter tidligere smart url
				delete_smart_url("/site/" . $res["lang_id"] . "////" . $id);
				
				// Opretter smart url
				create_smart_url($parent_title . $frm->values["title"], "/site/" . $res["lang_id"] . "////" . $id);
			}
			
			// Videre
			header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
			exit;
					
		}
			
		$html .= $frm->html();

		
		/*
			Elementer på denne side - simpel, avanceret
		*/
		
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Indhold}");
		$html .= $msg->html();
		
		$links = new links;
		if ($res["edit_mode"] != "simple") $links->link("{LANG|Vis elementer}", $do, $id . "&edit_mode=simple");
		if ($res["edit_mode"] != "advanced") $links->link("{LANG|Vis WYSIWYG-editor}", $do, $id . "&edit_mode=advanced");
		$html .= $links->html();
				
		if ($res["edit_mode"] == "advanced")
		{
			// WYSIWYG / avanceret
			
			$frm = new form("content");
			$frm->submit_text = "{LANG|Gem indhold}";
			$frm->wysiwyg_dir = "/modules/$module/uplwysiwyg/";
			$frm->wysiwyg_upload(true);
			$frm->wysiwyg(true);
			$frm->textarea(
				"{LANG|Indhold}",
				"content",
				stripslashes($res["content"]),
				"",
				"",
				"",
				75,
				15
				);
				
			if ($frm->done())
			{
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						content = '" . $db->escape($frm->values["content"]) . "'
					WHERE
						id = '$id'
					");
				// Videre
				header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
				exit;
			}
			
			$html .= $frm->html();
			
		}
		else
		{
			// Simpel
			
			// Elementer på denne side
			$tbl = new table;
			$tbl->th("{LANG|Element}");
			$tbl->th("{LANG|Valg}", 2);
			$tbl->th("{LANG|Sortering}", 2);
			$tbl->endrow();
			$content = $res["content"];
			$count = 0;
			while (ereg("\{([\.\|A-Za-z0-9_-]+)\}", $content, $array))
			{
				// Fjerner fra content
				$content = str_replace($array[0], "", $content);
				// Laver element-navn uden {}
				$element = str_replace("{", "", str_replace("}", "", $array[0]));
				// Deler op i parametre
				$array = split("[|]", $array[1]);
				// Finder element-type
				if ($array[0] == "MODULE")
				{
					// Finder modul-beskrivelse
					if ($key = array_search($array[2], $elements[$array[1]]))
					{
						// Så har vi fundet beskrivelsen
						$tbl->td(str_replace("_", " ", $array[1]) . " - " . $key);
					}
					elseif ($key = array_search($array[2] . "|" . $array[3], $elements[$array[1]]))
					{
						// Så er det med do-parameter
						$tbl->td(str_replace("_", " ", $array[1]) . " - " . $key);
					}
					elseif ($key = array_search($array[2] . "|" . $array[3] . "|" . $array[4], $elements[$array[1]]))
					{
						// Så er det med ID-parameter
						$tbl->td(str_replace("_", " ", $array[1]) . " - " . $key);
					}
					else
					{
						// Så er det bare modul-navnet vi oplyser
						$tbl->td($array[1]);
					}
				}
				else
				{
					// Ukendt
					$tbl->td("{LANG|Ukendt element}: " . str_replace("_", " ", $array[1]));
				}
				if ($array[4] <> "")
				{
					$tbl->choise("{LANG|Ret}", "edit", $array[4] . "&return_url=" . urlencode("?page=$page&do=$do&id=$id"), "", "default", $array[1]);
				}
				else
				{
					$tbl->td("&nbsp;");
				}
				$tbl->choise("{LANG|Fjern}", "$do&do2=remove", $id . "&element=" . urlencode($element), "Fjern dette element fra denne side? Elementet bevares.");
				$tbl->choise("{LANG|Op}", "$do&do2=move_up", $id . "&element=" . urlencode($element));
				$tbl->choise("{LANG|Ned}", "$do&do2=move_down", $id . "&element=" . urlencode($element));
				$tbl->endrow();
				$count++;
			}
			if ($count == 0)
			{
				$tbl->td("{LANG|Ingen elementer}...", 4);
				$tbl->endrow();
			}
			$html .= $tbl->html();
			
			// Formular til tilføjelse af element
			$frm = new form("add");
			$frm->submit_text = "{LANG|Tilføj}";
			$frm->tpl("th", "{LANG|Tilføj element til denne side}");
			$frm->select(
				"{LANG|Vælg element}",
				"element",
				"",
				"^.+$",
				"{LANG|Vælg et element}",
				"",
				array_merge(
					array(array("", "")), 
					$select_elements
					)
				);
			if ($frm->done())
			{
				// Tilføjer element til siden
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						content = CONCAT(content, '{" . $db->escape($frm->values["element"]) . "}')
					WHERE
						id = '$id'
					");
				// Videre
				header("Location: ?page=$page&do=$do&id=$id&menu_lang=" . $menu_lang);
				exit;
			}
			$html .= $frm->html();
			
		}
		
		
		
	}
	elseif ($do == "move_up" or $do == "move_down")
	{
		//
		// Flyt op / ned
		//
	
		if ($do == "move_up")
		{
			$sql_where = " < ";
			$sql_order = " DESC ";
		}
		else
		{
			$sql_where = " > ";
			$sql_order = " ASC ";
		}
		
		$db->execute("
			SELECT
				id,
				`order`
			FROM
				" . $_table_prefix . "_pages_
			WHERE
				id = '$id'
			");
		$res1 = $db->fetch_array();
		
		$db->execute("
			SELECT
				id,
				`order`
			FROM
				" . $_table_prefix . "_pages_
			WHERE
				lang_id = '" . $db->escape($vars["menu_lang"]) . "' AND
				`order` $sql_where '" . $res1["order"] . "'
			ORDER BY
				`order` $sql_order
			LIMIT
				1
			");
		$res2 = $db->fetch_array();
		
		if ($res1 and $res2)
		{
			$db->execute("
				UPDATE
					" . $_table_prefix . "_pages_
				SET
					`order` = '" . $res1["order"] . "' 
				WHERE
					id = '" . $res2["id"] . "'
				");
			$db->execute("
				UPDATE
					" . $_table_prefix . "_pages_
				SET
					`order` = '" . $res2["order"] . "' 
				WHERE
					id = '" . $res1["id"] . "'
				");
		}
		
		header("Location: ./?page=$page&menu_lang=" . $vars["menu_lang"]);
		exit;
		
	}
	elseif ($do == "copy")
	{
		//
		// Kopier menu
		//
		
		$links = new links;
		$links->link("Tilbage", "&menu_lang=" . $vars["menu_lang"]);
		$html .= $links->html();
		
		$frm = new form;
		$frm->hidden("menu_lang", $vars["menu_lang"]);
		$frm->tpl("th", "{LANG|Kopier menu mellem sprog}");
		$frm->select(
			"{LANG|Kopier fra}",
			"lang_from",
			$menu_lang,
			"^[a-z]+$",
			"{LANG|Påkrævet}",
			"",
			array_merge(array(array("", "")), $select_langs)
			);
		$frm->select(
			"{LANG|Kopier til}",
			"lang_to",
			"",
			"^[a-z]+$",
			"{LANG|Påkrævet}",
			'
				if ($this->values["lang_from"] == $this->values["lang_to"])
				{
					$error = "{LANG|Kan ikke være samme som ´Kopier fra´}";
				}
			',
			array_merge(array(array("", "")), $select_langs)
			);
			
		if ($frm->done())
		{
			$funccopy = create_function('$lang_from, $lang_to, $sub_from = 0, $sub_to = 0', '
				if ($lang_from == $lang_to) return;
			
				global $funccopy, $_table_prefix;
				$db = new db;
				
				// Henter menu
				$ress = $db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_pages_
					WHERE
						lang_id = \'" . $db->escape($lang_from) . "\' AND
						sub_id = \'" . intval($sub_from) . "\'
					ORDER BY
						`order`
					");
				$order = intval($db->execute_field("
					SELECT
						MAX(`order`)
					FROM
						" . $_table_prefix . "_pages_
					WHERE
						lang_id = \'" . $db->escape($lang_to) . "\' AND
						sub_id = \'" . intval($sub_to) . "\'
					"));
				while ($res = $db->fetch_array($ress))
				{
					$order++;
					if ($res["frontpage"] == 1)
					{
						$db->execute("
							UPDATE
								" . $_table_prefix . "_pages_
							SET
								frontpage = 0
							WHERE
								lang_id = \'" . $db->escape($lang_to) . "\'
							");
					}
					// Opretter menu
					$db->execute("
						INSERT INTO
							" . $_table_prefix . "_pages_
						(
							sub_id,
							title,
							time_from,
							time_to,
							`order`,
							content,
							active,
							frontpage,
							layout,
							user_group,
							lang_id,
							meta_title,
							meta_description,
							edit_mode,
							meta_keywords,
							public,
							link,
							sub_title,
							sub_menu,
							alt_url,
							no_link
						)
						VALUES
						(
							\'" . intval($sub_to) . "\',
							\'" . $db->escape($res["title"]) . "\',
							" . ($res["time_from"] != "" ? ("\'" . $res["time_from"] . "\'") : "NULL") . ",
							" . ($res["time_to"] != "" ? ("\'" . $res["time_to"] . "\'") : "NULL") . ",
							\'$order\',
							\'" . $db->escape($res["content"]) . "\',
							\'" . $db->escape($res["active"]) . "\',
							\'" . $db->escape($res["frontpage"]) . "\',
							\'" . $db->escape($res["layout"]) . "\',
							\'" . $db->escape($res["user_group"]) . "\',
							\'" . $db->escape($lang_to) . "\',
							\'" . $db->escape($res["meta_title"]) . "\',
							\'" . $db->escape($res["meta_description"]) . "\',
							\'" . $db->escape($res["edit_mode"]) . "\',
							\'" . $db->escape($res["meta_keywords"]) . "\',
							\'" . $db->escape($res["public"]) . "\',
							\'" . $db->escape($res["link"]) . "\',
							\'" . $db->escape($res["sub_title"]) . "\',
							\'" . $db->escape($res["sub_menu"]) . "\',
							\'" . $db->escape($res["alt_url"]) . "\',
							\'" . $db->escape($res["no_link"]) . "\'
						)
						");
					$id = $db->insert_id();
					$funccopy($lang_from, $lang_to, $res["id"], $id);
				}
				');
			$funccopy($frm->values["lang_from"], $frm->values["lang_to"]);
			
			header("Location: ./?page=$page&menu_lang=" . $frm->values["lang_to"]);
			exit;
		}
		
		$html .= $frm->html();
		
		
	}
	else
	{
		//
		// Oversigt
		//
		
		// Slet
		if ($do == "delete")
		{
			$lang_id = $db->execute_field("
				SELECT
					lang_id
				FROM
					" . $_table_prefix . "_pages_
				WHERE
					id = '$id'
				");
				
			// Sletter smart url
			delete_smart_url("/site/$lang_id////" . $id);
			
			$sub_id = $db->execute_field("
				SELECT
					sub_id
				FROM
					" . $_table_prefix . "_pages_
				WHERE
					id = '$id'
				");
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_pages_
				WHERE
					id = '$id'
				");
			$db->execute("
				UPDATE
					" . $_table_prefix . "_pages_
				SET
					sub_id = '$sub_id'
				WHERE
					sub_id = '$id'
				");
		}

		// Tabel		
		$tbl = new table("overview");
		
		// Drag'n'drop
		$tbl->row_dnd(true); 
		
		// Flyt
		if (list($move_id, $before_id) = $tbl->row_move())
		{
			if ($before_id == "")
			{
				// Skal placeres øverst
				
				// Lægger 1 til alle menu-order
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						`order` = `order` + 1
					");
					
				// Sætter sortering til 0 på det flyttede element
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						`order` = 1
					WHERE
						id = '" . intval($move_id) . "'
					");
			}
			else
			{
				// Skal placeres efter andet menupunkt
				
				// Henter sortering på menupunkt den skal placeres efter
				$order = $db->execute_field("
					SELECT
						`order`
					FROM
						" . $_table_prefix . "_pages_
					WHERE
						id = '" . intval($before_id) . "'
					");
					
				// Lægger 1 til alle menu-order herefter
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						`order` = `order` + 1
					WHERE
						`order` > '$order'
					");
					
				// Sætter sortering på andet menupunkt
				$db->execute("
					UPDATE
						" . $_table_prefix . "_pages_
					SET
						`order` = '" . ($order + 1) . "'
					WHERE
						id = '" . intval($move_id) . "'
					");
			}
		}		
		
		// Links
		$links = new links;
		$links->link("{LANG|Opret side}", "add&menu_lang=" . $menu_lang);
		if (count($select_langs) > 1)
		{
			for ($i = 0; $i < count($select_langs); $i++)
			{
				$links->link($select_langs[$i][1], "&menu_lang=" . $select_langs[$i][0]);
			}
			$links->link("{LANG|Kopier mellem sprog}", "copy&menu_lang=" . $menu_lang);
		}
		$html .= $links->html();
		
		// Henter sider
		$pages = pages_array(true, 0, "", $menu_lang);
		
		// Viser oversigt
		$tbl->th("{LANG|Sprog}");
		$tbl->th("{LANG|Titel}");
		$tbl->th("{LANG|Start-dato}");
		$tbl->th("{LANG|Slut-dato}");
		$tbl->th("{LANG|Layout}");
		$tbl->th("{LANG|Vis i menu}&nbsp;");
		$tbl->th("{LANG|Publiceret}&nbsp;");
		$tbl->th("{LANG|Brugergruppe}");
		$tbl->th("{LANG|Undermenu}");
		$tbl->th("{LANG|Forside}&nbsp;");
		$tbl->th("{LANG|Status}&nbsp;");
		$tbl->th("{LANG|Valg}", 2);
		/*
		$tbl->th("{LANG|Sortering}", 2);
		*/
		$tbl->endrow();
		
		$tmp = new tpl("admin_icon_active");
		$icon_active = $tmp->html();
				
		$tmp = new tpl("admin_icon_inactive");
		$icon_inactive = $tmp->html();
		
		for ($i = 0; $i < count($pages); $i++)
		{
			// Finder status
			if ($pages[$i]["active"] == 1 and
				($pages[$i]["time_from"] == "" or strtotime($pages[$i]["time_from"]) <= time())
				and
				($pages[$i]["time_to"] == "" or strtotime($pages[$i]["time_to"]) >= time())
				)
			{
				$status = $icon_active;
			}
			else
			{
				$status = $icon_inactive;
			}
			
			list($group, $inout, $ugroup) = explode("|", $pages[$i]["user_group"]);
			if ($group != "")
			{
				if ($ugroup > 0)
				{
					$group .= " (" . $db->execute_field("
						SELECT
							title
						FROM
							" . $_table_prefix . "_module_" . $group . "_groups
						WHERE
							id = '" . intval($ugroup) . "'
						") . ")";
				}
				$group .= " (" . ($inout == "logged_in" ? "Logget ind" : "Logget ud") . ")";
			}
			else
			{
				$group = "-";
			}
			
			$tbl->row_id($pages[$i]["id"]);
			$tbl->td($array_langs[$pages[$i]["lang_id"]] <> "" ? $array_langs[$pages[$i]["lang_id"]] : $pages[$i]["lang_id"]);
			$tbl->td($pages[$i]["title"]);
			$tbl->td($pages[$i]["time_from"] <> "" ? date("d-m-Y H:i", strtotime($pages[$i]["time_from"])) : "-");
			$tbl->td($pages[$i]["time_to"] <> "" ? date("d-m-Y H:i", strtotime($pages[$i]["time_to"])) : "-");
			$tbl->td($pages[$i]["layout"]);
			$tbl->td($pages[$i]["active"] ? $icon_active : $icon_inactive, 1, 1, "center");
			$tbl->td($pages[$i]["public"] ? $icon_active : $icon_inactive, 1, 1, "center");
			$tbl->td($group);
			$tbl->td($array_sitemenu[$pages[$i]["sub_menu"]]);
			$tbl->td($pages[$i]["frontpage"] ? $icon_active : $icon_inactive, 1, 1, "center");
			$tbl->td($status, 1, 1, "center");
			$tbl->choise("{LANG|Ret}", "edit", $pages[$i]["id"] . "&menu_lang=" . $menu_lang);
			$tbl->choise("{LANG|Slet}", "delete", $pages[$i]["id"] . "&menu_lang=" . $menu_lang, "Slet denne side? Elementer fra siden bevares.");
			/*
			$tbl->choise("{LANG|Flyt op}", "move_up", $pages[$i]["id"] . "&menu_lang=" . $menu_lang);
			$tbl->choise("{LANG|Flyt ned}", "move_down", $pages[$i]["id"] . "&menu_lang=" . $menu_lang);
			*/
			$tbl->endrow();
		}
		
		if (count($pages) == 0)
		{
			$tbl->td("{LANG|Ingen}...", 11);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
	}
?>
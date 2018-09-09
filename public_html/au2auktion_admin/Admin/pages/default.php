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
		Beskrivelse:	Start-side med genveje osv.
		31-10-2007:		Sletter gamle tmp-filer
		04-02-2008:		Rydder op i log meddelelser så der max er 1000
	*/
	
	// Sletter gamle filer i tmp-mappe
	$file = new file;
	$files = $file->find_files($_tmp_dir);
	for ($i = 0; $i < count($files); $i++)
	{
		if (filemtime($_tmp_dir . $files[$i]) < $_tmp_expire)
		{
			@unlink($_tmp_dir . $files[$i]);
		}
	}
	
	// Rydder op i gamle log meddelelser
	$max_id = $db->execute_field("
		SELECT
			MAX(id)
		FROM
			" . $_table_prefix . "_log_messages
		");
	$db->execute("
		DELETE FROM
			" . $_table_prefix . "_log_messages
		WHERE
			id < " . ($max_id - 1000) . "
		");
		
	// Overskrift
	$msg = new message;
	$msg->title("{LANG|Administration af} " . $_settings_["SITE_TITLE"]);
	$html .= $msg->html();
	
	if ($do == "edit_links")
	{
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$links->link("{LANG|Opret genvej}", "add_link");
		$html .= $links->html();
		
		// Tabel
		$tbl = new table;
		$tbl->th("{LANG|Titel}");
		$tbl->th("{LANG|URL}");
		$tbl->th("{LANG|Valg}", 2);
		$tbl->endrow();
		
		// Henter links
		$elements = "";
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_admin_links
			ORDER BY
				title
			");
		while ($db->fetch_array())
		{
			$tbl->td(stripslashes($db->array["title"]));
			$tbl->td(stripslashes($db->array["url"]));
			$tbl->choise("{LANG|Ret}", "edit_link", $db->array["id"]);
			$tbl->choise("{LANG|Slet}", "delete_link", $db->array["id"], "{LANG|Vil du slette denne genvej}?");
			$tbl->endrow();
		}
		
		if ($db->num_rows() == 0)
		{
			$tbl->td("{LANG|Ingen}...", 4);
		}
		
		// Viser tabel
		$html .= $tbl->html();
		
	}
	elseif ($do == "delete_link")
	{
		// Slet link
		$db->execute("
			DELETE FROM
				" . $_table_prefix . "_admin_links
			WHERE
				id = '$id'
			");
			
		// Tilbage
		header("Location: ?page=$page&do=edit_links");
		exit;
		
	}
	elseif ($do == "edit_link" or $do == "add_link")
	{
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Henter link
		if ($do == "edit_link")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_admin_links
				WHERE
					id = '$id'
				");
			if (!$res = $db->fetch_array())
			{
				header("Location: ?page=$page&do=edit_links");
				exit;
			}
		}
		else
		{
			$res = false;
		}
		
		// Henter rettigheder
		$user_rights = " " . $usr->extra_get("rights");
		$user_administrator = $usr->extra_get("administrator");
		
		// Henter links til moduler ect.
		$elements = "";
		$array1 = admin_module_menu();
		reset($array1);
		while (list($folder, $menu) = each($array1))
		{
			if (strpos($user_rights, $folder) > 0 or $user_administrator == 1)
			{
				reset($menu);
				while (list($key, $value) = each($menu))
				{
					$array = split("[|]", $value);
					$url = "?module=" . $folder . "&page=" . $array[0] . "&do=" . $array[1];
					$tmp = new tpl("admin_default_links_select_element");
					$tmp->set("title", module2title($folder) . " - " . $key);
					$tmp->set("url", $url);
					if ($url == stripslashes($res["url"])) $tmp->set("selected", "selected");
					$elements .= $tmp->html();
				}
			}
		}
		$tmp = new tpl("admin_default_links_select");
		$tmp->set("elements", $elements);
		$urls = $tmp->html();
		
		// Henter ikoner
		$file = new file;
		
		// /img/icon_large_*.*
		$files = $file->find_files($_document_root . "/img/");
		$elements = "";
		for ($i = 0; $i < count($files); $i++)
		{
			if (eregi("^icon_large_", $files[$i]))
			{
				$tmp = new tpl("admin_default_links_icons_element");
				$tmp->set("icon", "/img/" . $files[$i]);
				if ($res["icon"] == $files[$i]) $tmp->set("checked", "checked");
				$elements .= $tmp->html();
			}
		}
		// /upl/admin_icon_*.jpg
		$files = $file->find_files($_document_root . "/upl/");
		for ($i = 0; $i < count($files); $i++)
		{
			if (eregi("^admin_icon_", $files[$i]))
			{
				$tmp = new tpl("admin_default_links_icons_element");
				$tmp->set("icon", "/upl/" . $files[$i]);
				if ($res["icon"] == $files[$i]) $tmp->set("checked", "checked");
				$elements .= $tmp->html();
			}
		}
		$tmp = new tpl("admin_default_links_icons");
		$tmp->set("elements", $elements);
		$icons .= $tmp->html();
		
		// Formular
		$frm = new form;
		$frm->tpl("th", $do == "add_link" ? "{LANG|Opret genvej}" : "{LANG|Ret genvej}");
		$frm->input(
			"{LANG|Titel}",
			"title",
			stripslashes($res["title"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->input(
			"{LANG|Indtast destinations URL}",
			"url",
			stripslashes($res["url"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->tpl(
			"td2",
			"...{LANG|eller vælg destination her}:",
			$urls
			);
		$frm->tpl(
			"td2",
			"{LANG|Vælg ikon}",
			$icons
			);
		$frm->image(
			"{LANG|Eller upload ikon} (32x32 pixels)",
			"upload_icon"
			);
			
		if ($frm->done())
		{
			if ($frm->values["upload_icon"] != "")
			{
				if ($img = imagecreatefromjpeg($_document_root . $frm->values["upload_icon"]))
				{
					// Skalerer ikon til 32 x 32
					$image = new image;
					$img = $image->imagesize($img, 32, 32);
					
					// Finder filnavn
					$filename_prefix = "/upl/admin_icon_" . eregi_replace("[^a-z]", "", eregi_replace("\.[a-z]+$", "", $_FILES["upload_icon"]["name"]));
					$filename = $filename_prefix . ".jpg";
					$count = 0;
					while (is_file($_document_root . $filename))
					{
						$count++;
						$filename = $filename_prefix . $count . ".jpg";
					}
					
					// Gemmer fil
					imagejpeg($img, $_document_root . $filename);
					
					// Sætter som ikon
					$vars["icon"] = $filename;
				}
			}
			
			if ($do == "add_link")
			{
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_admin_links
					(
						title,
						url,
						icon
					)
					VALUES
					(
						'" . $db->escape($vars["title"]) . "',
						'" . $db->escape($vars["url"]) . "',
						'" . $db->escape($vars["icon"] <> "" ? $vars["icon"] : "icon_large_default.gif") . "'
					)
					");
			}
			else
			{
				$db->execute("
					UPDATE
						" . $_table_prefix . "_admin_links
					SET
						title = '" . $db->escape($vars["title"]) . "',
						url = '" . $db->escape($vars["url"]) . "',
						icon = '" . $db->escape($vars["icon"] <> "" ? $vars["icon"] : "icon_large_default.png") . "'
					WHERE
						id = '$id'
					");
			}
			
			$frm->cleanup();
			
			header("Location: ?page=$page");
			exit;
		}
		
		$html .= $frm->html();
		
	}
	else
	{
		// Henter links
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_admin_links
			ORDER BY
				title
			");
		
		// Links
		$links = new links;
		if ($db->num_rows() > 0)
		{
			$links->link("{LANG|Rediger genveje}", "edit_links");
		}
		$links->link("{LANG|Opret genvej}", "add_link");
		$html .= $links->html();
		
		// Ikoner med links
		$elements = "";
		while ($db->fetch_array())
		{
			$tmp = new tpl("admin_default_element");
			$tmp->set("title", stripslashes($db->array["title"]));
			$tmp->set("url", stripslashes($db->array["url"]));
			$tmp->set("icon", strpos($db->array["icon"], "/") === false ? ("/img/" . stripslashes($db->array["icon"])) : stripslashes($db->array["icon"]));
			$elements .= $tmp->html();
		}
		$tmp = new tpl("admin_default");
		$tmp->set("elements", $elements);
		$html .= $tmp->html();
	
		// Andre brugere der er logget ind
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_user_admin
			WHERE
				login_time > '" . date("Y-m-d H:i:s", strtotime("-2 minute")) . "' AND
				id <> '" . $usr->user_id . "'
			");
		if ($db->num_rows() > 0)
		{
			$tbl = new table;
			$tbl->th("{LANG|Andre brugere der er logget ind}");
			$tbl->endrow();
			
			while ($db->fetch_array())
			{
				$tbl->td($db->array["username"], 1, 1, "center");
				$tbl->endrow();
			}
			
			$html .= $tbl->html();
		}
	}
?>
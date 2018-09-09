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
		Beskrivelse:	Sprog
		26-04-2007:		Mulighed for oversættelse af grundsystem
						Bedre oversættelses system
	*/
	
	// ISO 639 - 2 bogstavs koder - hvad skal vi dog bruge alle de sprog til :-)
	$array_iso = array(
		"aa" => "Afar", "ab" => "Abkhazian", "af" => "Afrikaans", "am" => "Amharic", "ar" => "Arabic",
		"as" => "Assamese", "ay" => "Aymara", "az" => "Azerbaijani", "ba" => "Bashkir", "be" => "Byelorussian",
		"bg" => "Bulgarian", "bh" => "Bihari", "bi" => "Bislama", "bn" => "Bengali, Bangla", "bo" => "Tibetan",
		"br" => "Breton", "ca" => "Catalan", "co" => "Corsican", "cs" => "Czech", "cy" => "Welsh",
		"da" => "Dansk", "de" => "German", "dz" => "Bhutani", "el" => "Greek", "en" => "English, American",
		"eo" => "Esperanto", "es" => "Spanish", "et" => "Estonian", "eu" => "Basque", "fa" => "Persian",
		"fi" => "Finnish", "fj" => "Fiji", "fo" => "Faeroese", "fr" => "French", "fy" => "Frisian",
		"ga" => "Irish", "gd" => "Gaelic, Scots Gaelic", "gl" => "Galician", "gn" => "Guarani", "gu" => "Gujarati",
		"ha" => "Hausa", "hi" => "Hindi", "hr" => "Croatian", "hu" => "Hungarian", "hy" => "Armenian",
		"ia" => "Interlingua", "ie" => "Interlingue", "ik" => "Inupiak", "in" => "Indonesian", "is" => "Icelandic",
		"it" => "Italian", "iw" => "Hebrew", "ja" => "Japanese", "ji" => "Yiddish", "jw" => "Javanese",
		"ka" => "Georgian", "kk" => "Kazakh", "kl" => "Greenlandic", "km" => "Cambodian", "kn" => "Kannada",
		"ko" => "Korean", "ks" => "Kashmiri", "ku" => "Kurdish", "ky" => "Kirghiz", "la" => "Latin",
		"ln" => "Lingala", "lo" => "Laothian", "lt" => "Lithuanian", "lv" => "Latvian, Lettish", "mg" => "Malagasy",
		"mi" => "Maori", "mk" => "Macedonian", "ml" => "Malayalam", "mn" => "Mongolian", "mo" => "Moldavian",
		"mr" => "Marathi", "ms" => "Malay", "mt" => "Maltese", "my" => "Burmese", "na" => "Nauru",
		"ne" => "Nepali", "nl" => "Dutch", "no" => "Norwegian", "oc" => "Occitan", "om" => "Oromo, Afan",
		"or" => "Oriya", "pa" => "Punjabi", "pl" => "Polish", "ps" => "Pashto, Pushto", "pt" => "Portuguese",
		"qu" => "Quechua", "rm" => "Rhaeto-Romance", "rn" => "Kirundi", "ro" => "Romanian", "ru" => "Russian",
		"rw" => "Kinyarwanda", "sa" => "Sanskrit", "sd" => "Sindhi", "sg" => "Sangro", "sh" => "Serbo-Croatian",
		"si" => "Singhalese", "sk" => "Slovak", "sl" => "Slovenian", "sm" => "Samoan", "sn" => "Shona",
		"so" => "Somali", "sq" => "Albanian", "sr" => "Serbian", "ss" => "Siswati", "st" => "Sesotho",
		"su" => "Sudanese", "sv" => "Swedish", "sw" => "Swahili", "ta" => "Tamil", "te" => "Tegulu",
		"tg" => "Tajik", "th" => "Thai", "ti" => "Tigrinya", "tk" => "Turkmen", "tl" => "Tagalog",
		"tn" => "Setswana", "to" => "Tonga", "tr" => "Turkish", "ts" => "Tsonga", "tt" => "Tatar",
		"tw" => "Twi", "uk" => "Ukrainian", "ur" => "Urdu", "uz" => "Uzbek", "vi" => "Vietnamese",
		"vo" => "Volapuk", "wo" => "Wolof", "xh" => "Xhosa", "yo" => "Yoruba", "zh" => "Chinese",
		"zu" => "Zulu"
		);
		
	// Må tilføje sprog?
	$disable_edit = (strpos(" " . $usr->extra_get("rights"), "|languages_noedit|") > 0 or $usr->extra_get("administrator") != 1);
		
	// Henter ID fra vars
	$id = $vars["id"];
	if (!ereg("^[a-z]{2}$", $id)) $id = false;

	if (($do == "add" or $do == "edit") and !$disable_edit)
	{
		//
		// Tilføj eller ret sprog
		//
		
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Sprog} - " . ($do == "add" ? "{LANG|Tilføj}" : "{LANG|Ret}"));
		$html .= $msg->html();
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		if ($do == "edit")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_languages_
				WHERE
					id = '$id'
				");
			if (!$lang = $db->fetch_array())
			{
				header("Location: ?page=$page");
				exit;
			}
		}
		else
		{
			$lang = false;
		}
		
		// Formular
		$frm = new form;
		$frm->tpl("th", $do == "add" ? "{LANG|Tilføj sprog}" : "{LANG|Ret sprog}");
		if ($do == "add")
		{
			// Laver select-liste med ISO-koder
			$select_iso = array();
			reset($array_iso);
			while (list($key, $value) = each($array_iso))
			{
				$select_iso[count($select_iso)] = array($key, $key . ", " . $value);
			}
			$frm->select(
				"{LANG|ISO-kode}",
				"id",
				"",
				"^.+$",
				"{LANG|Skal vælges}",
				'
					global $_table_prefix;
					$db = new db;
					if ($db->execute_field("
						SELECT
							id
						FROM
							" . $_table_prefix . "_languages_
						WHERE
							id = \'" . $db->escape($this->values["id"]) . "\'
						"))
					{
						$error = "{LANG|Der findes allerede et sprog med denne ISO-kode}";
					}
				',
				$select_iso
				);
		}
		else
		{
			$frm->tpl("td2", "{LANG|ISO-kode}:", $lang["id"] . ", " . $array_iso[$lang["id"]]);
		}
		$frm->input(
			"{LANG|Sprog}",
			"title",
			stripslashes($lang["title"]),
			"^.+$",
			"{LANG|Skal angives}"
			);
			
		if ($frm->done())
		{
			if ($do == "add")
			{
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_languages_
					(
						id,
						title
					)
					VALUES
					(
						'" . $db->escape($frm->values["id"]) . "',
						'" . $db->escape($frm->values["title"]) . "'
					)
					");
			}
			else
			{
				$db->execute("
					UPDATE
						" . $_table_prefix . "_languages_
					SET
						title = '" . $db->escape($frm->values["title"]) . "'
					WHERE
						id = '$id'
					");
			}
			header("Location: ?page=$page");
			exit;
		}

		// Viser formular
		$html .= $frm->html();			
		
	}
	elseif ($do == "delete" and !$disable_edit)
	{
		//
		// Slet sprog
		//
	
		$db->execute("
			DELETE FROM
				" . $_table_prefix . "_languages_
			WHERE
				id = '$id' AND
				`default` <> '1'
			");
			
		header("Location: ?page=$page");
		exit;
			
	}
	elseif ($do == "make_default" and !$disable_edit)
	{
		//
		// Vælg standard sprog
		//
		
		$id = $db->execute_field("
			SELECT
				id
			FROM
				" . $_table_prefix . "_languages_
			WHERE
				id = '$id'
			");
			
		if ($id <> "")
		{
	
			$db->execute("
				UPDATE
					" . $_table_prefix . "_languages_
				SET
					`default` = '0'
				");
				
			$db->execute("
				UPDATE
					" . $_table_prefix . "_languages_
				SET
					`default` = '1'
				WHERE
					id = '$id'
				");
				
		}
		
		header("Location: ?page=$page");
		exit;				
			
	}
	elseif ($do == "translate")
	{
		//
		// Oversæt
		//
	
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_languages_
			WHERE
				id = '$id'
			");
		if (!$lang = $db->fetch_array())
		{
			header("Location: ?page=$page");
			exit;
		}
		
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Sprog} - " . stripslashes($lang["title"]));
		$html .= $msg->html();
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Tabel
		$tbl = new table;
		$tbl->th("{LANG|Type}");
		$tbl->th("{LANG|Title}");
		$tbl->th("{LANG|Valg}");
		$tbl->endrow();
		$tbl->td("{LANG|CMS}");
		$tbl->td("{LANG|Grundsystem}");
		$tbl->choise("{LANG|Oversæt}", "translater", $id);
		$tbl->endrow();

		// Viser moduler der kan oversættes
		$array = admin_module_installed();
		
		for ($i = 0; $i < count($array); $i++)
		{
			$tbl->td("{LANG|Modul}");
			$tbl->td($array[$i]);
			$tbl->choise("{LANG|Oversæt}", "translater", $id . "&mod=" . $array[$i]);
			$tbl->endrow();
		}
		
		if (count($array) == 0)
		{
			$tbl->td("{LANG|Ingen moduler med sprog-understøttelse}...", 2);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
	}
	elseif ($do == "translater_prepare")
	{
		//
		// Opdaterer sprog-filer
		//
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_languages_
			WHERE
				id = '$id'
			");
		if (!$lang = $db->fetch_array())
		{
			header("Location: ./?page=$page");
			exit;
		}		
		
		$mod = $vars["mod"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $mod))
		{
			$mod = "";
			$title = "{LANG|CMS grundsystem}";
		}
		else
		{
			$title = module2title($mod);
		}
		
		if ($vars["do2"] == "prepare")
		{
			if (!update_lang_file($mod))
			{
				die("Sprog-filen kunne ikke opdateres - se venligst System-meddelser");
			}
			else
			{
				header("Location: ./?page=$page&do=translater&id=$id&mod=$mod");
				exit;
			}
		}
		
		$msg = new message;
		$msg->title("{LANG|Sprog} - " . stripslashes($lang["title"]) . " - " . $title . " - {LANG|Opdaterer sprog-fil}");
		$msg->message("Vent et øjeblik...");
		$html .= $msg->html();
		
		$links = new links;
		$links->link("Annuller", "translate", $id);
		$html .= $links->html();
		
		$tmp = new tpl("redirect");
		$tmp->set("url", "./?page=$page&do=$do&id=$id&mod=$mod&do2=prepare");
		$tmp->set("timeout", "1000");
		$html .= $tmp->html();
		
		
	}
	elseif ($do == "translater")
	{
		//
		// Oversæt modul / grundsystem
		//
	
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_languages_
			WHERE
				id = '$id'
			");
		if (!$lang = $db->fetch_array())
		{
			header("Location: ./?page=$page");
			exit;
		}
		
		$mod = $vars["mod"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $mod))
		{
			$mod = "";
			$title = "{LANG|CMS grundsystem}";
			$lang_src = $_document_root . "/lang/lang.php";
			$lang_dst = "lang/" . $id . ".php";
		}
		else
		{
			$title = module2title($mod);
			$lang_src = $_document_root . "/modules/$mod/lang/lang.php";
			$lang_dst = "modules/$mod/lang/" . $id . ".php";
		}
		
		if (!is_file($lang_src))
		{
			header("Location: ?page=$page");
			exit;
		}
		
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Sprog} - " . stripslashes($lang["title"]) . " - " . $title);
		$html .= $msg->html();
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "translate", $id);
		$links->link("{LANG|Opdater sprogfil}", "translater_prepare", $id . "&mod=" . $mod);
		$html .= $links->html();
		
		// Henter sprog-linier
		$lang = array();
		include($lang_src);
		$lines = $lang;
		$lang = array();
		if (is_file($_document_root . "/" . $lang_dst))
		{
			include($_document_root . "/" . $lang_dst);
		}
		$i_total = count($lines);
		
		// Side-tal
		$paging = new paging;
		$paging->total($i_total);
		$limit = $paging->limit(25);
		$start = ($paging->current_page() - 1) * $limit;
		$end = $start + $limit;
		$html .= $paging->html();
		
		// Formular
		$frm = new form;
		$frm->hidden("mod", $mod);
		$frm->hidden("i_total", $i_total);
		$frm->hidden("_paging_page", $vars["_paging_page"]);
		$frm->tpl("th", "{LANG|Oversæt}");
		
		// Viser sprog-linier
		for ($i = $start; $i < $i_total and $i < $end; $i++)
		{
			$frm->textarea(
				$lines[$i],
				"line_" . $i,
				htmlentities(stripslashes($lang[$lines[$i]])),
				"",
				"",
				"",
				30,
				3
				);
			$lang[$lines[$i]] = $db->escape($frm->values["line_" . $i]);
		}
		
		if ($frm->done() and $frm->values["i_total"] == $i_total)
		{
			// Laver PHP kode
			$php = '';
			reset($lang);
			while (list($key, $value) = each($lang))
			{
				$value = stripslashes($value);
				$value = str_replace("\\", "/", $value);
				$value = str_replace("\"", "\\\"", $value);
				$key = str_replace("\\\"", "\"", $key);
				$key = str_replace("\"", "\\\"", $key);
				
				if ($php != "") $php .= ",";
				$php .= '"' . $key . '"=>"' . $value . '"';
			}
			$php = '<?php $lang = array(' . $php . '); ?>';
			
			// Gemmer via FTP
			if (file_put_contents($_document_root . "/" . $lang_dst, $php))
			{
				// OK
				header("Location: ?page=$page&do=translater&id=$id&mod=$mod&_paging_page=" . $vars["_paging_page"]);
				exit;
			}
			else
			{
				// Kunne ikke gemme
				$msg = new message;
				$msg->type("error");
				$msg->title("{LANG|Sprog-filen kunne ikke gemmes}. {LANG|Se fejl under system meddelelser}");
				$html .= $msg->html();
			}
			
		}
		
		// Viser formular
		$html .= $frm->html();
		
	}
	else
	{
		//
		// Oversigt
		//
	
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Sprog}");
		$html .= $msg->html();
		
		// Links
		$links = new links;
		if (!$disable_edit) $links->link("{LANG|Tilføj sprog}", "add");
		$html .= $links->html();
		
		// Tabel
		$tbl = new table;
		$tbl->th("{LANG|ISO-kode}");
		$tbl->th("{LANG|Sprog}");
		$tbl->th("{LANG|Standard}");
		$tbl->th("{LANG|Valg}", 4);
		$tbl->endrow();
		
		// Henter sprog
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_languages_
			ORDER BY
				`default` DESC,
				title
			");
			
		// OK ikon
		$tmp = new tpl("admin_icon_active");
		$icon_ok = $tmp->html();
	
		// Gennemløber sprog
		while ($db->fetch_array())
		{
			$tbl->td($db->array["id"], 1, 1, "center");
			$tbl->td(stripslashes($db->array["title"]));
			$tbl->td($db->array["default"] == 1 ? $icon_ok : "-", 1, 1, "center");
			$tbl->choise("{LANG|Oversæt}", "translate", $db->array["id"]);
			if (!$disable_edit)
			{
				$tbl->choise("{LANG|Ret}", "edit", $db->array["id"]);
				if ($db->array["default"] <> 1)
				{
					$tbl->choise("{LANG|Standard}", "make_default", $db->array["id"], "{LANG|Gør dette sprog til standard-sprog}?");
					$tbl->choise("{LANG|Slet}", "delete", $db->array["id"], "{LANG|Slet sprog}? {LANG|Evt}. {LANG|sprog-filer bevares}.");
				}
			}
			$tbl->endrow();
		}
		
		// Viser tabel
		$html .= $tbl->html();
			
	}
?>
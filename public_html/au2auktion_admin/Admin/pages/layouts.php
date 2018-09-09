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
		Beskrivelse:	Ændring af layouts
	*/
	
	$enable_updates = (strpos(" " . $usr->data["extra_rights"], "|updates|") > 0 or $usr->data["extra_administrator"] == 1);
	
	// Mode til filer der skal have php upload aktiveret
	$writable_file_mode = cms_setting("writable_file_mode");
	
	$msg = new message;
	$msg->title("Layouts");
	$html .= $msg->html();
	
	$id = $vars["id"];
	if ($id <> "" and !ereg("^[a-zA-Z0-9_-]+$", $id) and !is_dir($_document_root . "/layouts/$id/"))
	{
		header("Location: ?page=$page");
		exit;
	}
	
	// Inkluderer file_editor
	include("pages/file_editor.php");	
	
	if ($do == "example")
	{
		//
		// Eksempel
		//
		
		// Åbner filen manuelt, så vi kan fjerne menuen
		if ($fp = fopen($_document_root . "/layouts/" . $id . "/html/default.html", "r"))
		{
			$tmp = "";
			while (!feof($fp)) $tmp .= fread($fp, 1024);
			fclose($fp);
			// Fjerner alle {PAGE|} og {MODULE|} tags
			$tmp = str_replace("{PAGE|", "{", $tmp);
			$tmp = str_replace("{MODULE|", "{", $tmp);
			$_settings_["SITE_LAYOUT"] = $id;
			$tmp = new tpl($tmp);
			$html = $tmp->html();
		}
		
		$tpl = "empty";
		
	}
	elseif ($do == "activate" and $enable_updates)
	{
		//
		// Brug layout
		//
		
		$set->set("SITE_LAYOUT", $id);
		header("Location: ?page=$page");
		exit;
		
	}
	elseif ($do == "layouts_avaiable" and $enable_updates)
	{
		//
		// Henter liste med flere layouts
		//
	
		// Henter tilgængelig liste
		$layouts_avaiable = admin_layouts_avaiable();
		
		// Finder ud af hvilke layouts, der ikke er installeret
		$layouts = array();
		for ($i = 0; $i < count($layouts_avaiable); $i++)
		{
			$layouts[count($layouts)] = $layouts_avaiable[$i];
		}
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		$tbl = new table;
		
		$tbl->th("{LANG|Tilgængelige layouts}", 3);
		$tbl->endrow();
		
		// Gennemløber alle layouts
		for ($i = 0; $i < count($layouts); $i++)
		{
			if (round($i / 3) == $i / 3) $tbl->endrow();
			$tmp = new tpl("admin_layouts_layout_avaiable");
			$tmp->set("title", str_replace("_", " ", $layouts[$i]));
			$tmp->set("cms_update", $_cms_update);
			$tmp->set("cms_domain", $_cms_domain);
			$tmp->set("cms_check", $_cms_check);
			$tmp->set("id", $layouts[$i]);
			$tbl->td($tmp->html(), 1, 1, round($i / 2) == $i / 2 ? "a" : "b");
		}
		
		if (count($layouts) == 0)
		{
			$tbl->td("{LANG|Ingen tilgængelige layouts}...", 3);
		}
		$tbl->endrow();
		$html .= $tbl->html();
			
	}
	elseif ($do == "install_iframe" and $enable_updates)
	{
		//
		// Installerer layout - iframe
		//
		
		// Layout
		$tmp_layout = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_layout)) die("Ugyldigt layout");
		
		// Tjekker om layout allerede er installeret
		$install_as = $vars["install_as"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $install_as)) $install_as = $tmp_layout;
		$count = 0;
		$tmp_install_as = $install_as;
		while (is_dir($_document_root . "/layouts/$install_as/"))
		{
			$count++;
			$install_as = $tmp_install_as . "_" . $count . "_";
		}
		
		// Javascript realtime klasse
		$js = new js_realtime();
		$js->pause_after_update(.1);

		//
		// Starter installation
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 0);
		$tmp->set("text", "{LANG|Starter installation}");
		$js->update($tmp->html());
		
		//
		// Henter filliste
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 20);
		$tmp->set("text", "{LANG|Henter filliste}");
		$js->update($tmp->html());
		
		// Henter fil-liste for layout
		$array_file_list = admin_layouts_file_list($tmp_layout);
			
		//
		// Opretter mapper
		//		
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 40);
		$tmp->set("text", "{LANG|Opretter mapper}");
		$js->update($tmp->html());

		// FTP
		$ftp = new ftp;
		
		// Forbinder
		$ftp->connect($_cms_ftp_server) or die("Kunne ikke forbinde til FTP-server");
		
		// Logger ind
		$ftp->login($_cms_ftp_username, $_cms_ftp_password) or die("Kunne ikke logge på FTP-server");
		
		// Skifter til rod-mappe
		if ($_cms_ftp_root <> "")
		{
			$ftp->chdir($_cms_ftp_root) or die("Kunne ikke skifte til rod-mappe");
		}
		
		// Opretter layout-mappe
		$ftp->mkdir("layouts/$install_as") or die("Kunne ikke oprette layout-mappe");
		
		// Gennemløber og finder mapper, der skal oprettes
		for ($i = 0; $i < count($array_file_list); $i++)
		{
			$array = $array_file_list[$i];
			if ($array[0] == "FOLDER")
			{
				// Opretter mappe
				$ftp->mkdir("layouts/$install_as/" . $array[1], $writable_file_mode) or die("Kunne ikke oprette mappe");
			}
		}

		//
		// Kopierer filer
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 60);
		$tmp->set("text", "{LANG|Kopierer filer}");
		$js->update($tmp->html());
			
		// Gennemløber og finder mapper, der skal kopieres
		$tmp_file = $_tmp_dir . uniqid("");
		for ($i = 0; $i < count($array_file_list); $i++)
		{
			$array = $array_file_list[$i];
			if ($array[0] == "FILE")
			{
				// Henter fil
				$data = admin_layouts_file_get($tmp_layout, $array[1]);
				// Gemmer i tmp-mappe
				if ($fp = fopen($tmp_file, "w"))
				{
					fwrite($fp, $data);
					fclose($fp);
					// Tjekker om filen findes i forvejen
					if (is_file($_document_root . "/layouts/$install_as/" . $array[1]))
					{
						// Sletter fil via FTP
						$ftp->delete("layouts/$install_as/" . $array[1]) or die("Kunne ikke slette fil");
					}
					// Uploader fil
					$tmp_mode = ((eregi("/(html|js|css|img)/", $array[1]) and substr($array[1], -4) != ".php") ? $writable_file_mode : false);
					$ftp->upload($tmp_file, "layouts/$install_as/" . $array[1], $tmp_mode) or die("Kunne ikke uploade fil");
					// Sletter fil
					@unlink($tmp_file);
				}
			}
		}
		
		// Lukker FTP-forbindelse
		$ftp->close();

		//
		// Afslutter installationen
		//			
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 80);
		$tmp->set("text", "{LANG|Afslutter installation}");
		$js->update($tmp->html());
		
		//
		// Færdig
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 100);
		$tmp->set("text", "{LANG|Installation færdig}");
		$js->update($tmp->html());
		
		// Afslutter JavaScript realtime klasse
		$js->end();
			
		$tpl = "empty";
		$html = "";
		
	}
	elseif ($do == "install" and $enable_updates)
	{
		//
		// Installer layout
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();

		// Layout
		$tmp_layout = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_layout))
		{
			header("Location: ?page=$page");
			exit;
		}
				
		// Besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Installation af layoutet} " . str_replace("_", " ", $tmp_layout));
		$msg->message("{LANG|Vent venligst mens layoutet installeres}. {LANG|Forlad ikke denne side mens layoutet installeres}.");
		$html .= $msg->html();
		
		// Viser iframe med installation af layout
		$tmp = new tpl("iframe");
		$tmp->set("src", "?page=$page&do=install_iframe&id=$tmp_layout&install_as=" . $vars["install_as"]);
		$tmp->set("width", "400");
		$tmp->set("height", "50");
		$html .= $tmp->html();
		
	}
	elseif ($do == "remove_iframe" and $enable_updates)
	{
		//
		// Fjern layout - iframe
		//
		
		// Modul
		$tmp_layout = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_layout)) die("Ugyldigt layout");
		
		// Tjekker at layoutet ikke er det aktuelle layout
		if ($tmp_layout == $_settings_["SITE_LAYOUT"]) die("Aktuelt layout kan ikke af-installeres");
		
		// Javascript realtime klasse
		$js = new js_realtime();
		$js->pause_after_update(.1);

		//
		// Starter af-installation
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 0);
		$tmp->set("text", "{LANG|Starter Af-Installation}");
		$js->update($tmp->html());
		
		//
		// Fjerner data
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 25);
		$tmp->set("text", "{LANG|Fjerner data}");
		$js->update($tmp->html());
		
		//
		// Fjerner filer
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 50);
		$tmp->set("text", "{LANG|Fjerner filer}");
		$js->update($tmp->html());
		
		// FTP
		$ftp = new ftp;
		
		// Forbinder
		$ftp->connect($_cms_ftp_server) or die("Kunne ikke forbinde til FTP-server");
		
		// Logger ind
		$ftp->login($_cms_ftp_username, $_cms_ftp_password) or die("Kunne ikke logge på FTP-server");
		
		// Skifter til rod-mappe
		if ($_cms_ftp_root <> "")
		{
			$ftp->chdir($_cms_ftp_root) or die("Kunne ikke skifte til rod-mappe");
		}
		
		// Finder alle filer
		$file = new file;
		$files = $file->find_files($_document_root . "/layouts/$tmp_layout/", true);
		
		// Gennemløber og sletter filer
		for ($i = 0; $i < count($files); $i++)
		{
			// Sletter fil via FTP
			$ftp->delete("layouts/$tmp_layout/" . $files[$i]) or die("Kunne ikke slette fil");
		}
		
		//
		// Fjerner mapper
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 75);
		$tmp->set("text", "{LANG|Fjerner mapper}");
		$js->update($tmp->html());
		
		// Finder alle mapper
		$file = new file;
		$folders = $file->find_folders($_document_root . "/layouts/$tmp_layout/", true);
		
		// Gennemløber og sletter mapper
		for ($i = 0; $i < count($folders); $i++)
		{
			// Sletter mappe via FTP
			$ftp->rmdir("layouts/$tmp_layout/" . $folders[$i]) or die("Kunne ikke slette mappe");
		}
		
		// Sletter modul-mappe
		$ftp->rmdir("layouts/$tmp_layout") or die("Kunne ikke slette layout-mappe");
		
		// Lukker FTP-forbindelse
		$ftp->close();
		
		//
		// Færdig
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 100);
		$tmp->set("text", "{LANG|Af-Installation færdig}");
		$js->update($tmp->html());
		
		// Afslutter JavaScript realtime klasse
		$js->end();
			
		$tpl = "empty";
		$html = "";
		
	}
	elseif ($do == "remove" and $enable_updates)
	{
		//
		// Fjerner layout
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Modul
		$tmp_layout = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_layout))
		{
			header("Location: ?page=$page");
			exit;
		}
		
		// Tjekker at layoutet ikke er det aktuelle layout
		if ($tmp_layout == $_settings_["SITE_LAYOUT"])
		{
			header("Location: ?page=$page");
			exit;
		}
				
		// Tjekker om layout er installeret
		if (!is_dir($_document_root . "/layouts/$tmp_layout/"))
		{
			header("Location: ?page=$page");
			exit;
		}
		
		// Besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Af-Installation af layoutet} " . str_replace("_", " ", $tmp_layout));
		$msg->message("{LANG|Vent venligst mens layoutet afinstalleres}. {LANG|Forlad ikke denne side mens layoutet afinstalleres}.");
		$html .= $msg->html();
		
		// Viser iframe med installation af layout
		$tmp = new tpl("iframe");
		$tmp->set("src", "?page=$page&do=remove_iframe&id=$tmp_layout");
		$tmp->set("width", "400");
		$tmp->set("height", "50");
		$html .= $tmp->html();
		
	}
	elseif ($do == "install_intro" and $enable_updates)
	{
		//
		// Installation - Valg af navn
		//
	
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "show_avaiable");
		$html .= $links->html();
		
		// Finder titel
		if ($vars["install_as"] == "")
		{
			$install_as = $id;
			$count = 0;
			while (is_dir($_document_root . "/layouts/" . $id . "__" . $count . "__") or
				(is_dir($_document_root . "/layouts/" . $id) and $count == 0))
			{
				$count++;
				$install_as = $id . " " . $count;
			}
		}
		else
		{
			$install_as = $vars["install_as"];
		}
	
		// Formular
		$frm = new form;
		$frm->tpl("th", "{LANG|Installation af layout}");
		$frm->tpl("td2", "{LANG|Layout}:", $id);
		$frm->input(
			"{LANG|Installer som}",
			"install_as",
			$install_as,
			"^[a-zA-Z0-9 _-]+$",
			"{LANG|Må kun bestå af} a-z, 0-9, _, - {LANG|samt mellemrum}",
			'
				if (is_dir("' . $_document_root . '/layouts/" . $this->values["title"]))
				{
					$error = "{LANG|Der findes allerede et layout med det angivne navn}";
			'
			);
			
		if ($frm->done())
		{
			header("Location: ?module=$module&page=$page&do=install&id=$id&install_as=" .
				urlencode($install_as));
			exit;
		}
		
		$html .= $frm->html();
		
	}
	elseif ($do == "")
	{
		//
		// Oversigt
		//
	
		// Links
		$links = new links;
		if ($enable_updates) $links->link("{LANG|Hent flere layouts}", "layouts_avaiable");
		$html .= $links->html();
		
		$tbl = new table;
		
		$tbl->th("{LANG|Layout}");
		$tbl->th("{LANG|Standard}");
		$tbl->th("{LANG|Valg}", 4);
		$tbl->endrow();
		$tbl->endrow();

		// OK ikon
		$tmp = new tpl("admin_icon_active");
		$icon_ok = $tmp->html();
		
		// Finder alle layouts
		$file = new file;
		$layouts = $file->find_folders($_document_root . "/layouts/");
		$i2 = 0;
		for ($i = 0; $i < count($layouts); $i++)
		{
			$standard = ($_settings_["SITE_LAYOUT"] == $layouts[$i]);
			$tbl->td($layouts[$i]);
			$tbl->td($standard ? $icon_ok : "-", 1, 1, "center");
			$tbl->td("<a href=\"javascript:Popup('?module=$module&page=$page&do=example&id=" .
				$layouts[$i] . 
				"', 1024, 768);void(0);\" onclick=\"this.blur();\"><img src=\"../img/icon_show.gif\" alt=\"{LANG|Vis}\"></a>");
			$tbl->choise("{LANG|Ret}", "edit", $layouts[$i]);
			if ($enable_updates and !$standard)
			{
				$tbl->choise("{LANG|Standard}", "activate", $layouts[$i], "{LANG|Gør dette layout til standard layout}?");
				$tbl->choise("{LANG|Slet}", "remove", $layouts[$i], "{LANG|Slet dette layout}?");
			}
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
	}
?>
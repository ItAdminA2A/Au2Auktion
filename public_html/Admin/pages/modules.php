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
		Beskrivelse:	Styring af moduler
		30-04-2007:		Versionering af filer ved opdatering, f.eks.:
						minfil.html.2007043000
						minfil.html.2007042901
						minfil.html.2007042900
	*/
	
	$enable_updates = (strpos(" " . $usr->data["extra_rights"], "|updates|") > 0 or $usr->data["extra_administrator"] == 1);
	$enabled_auto_update = ($usr->extra_get("auto_update") == 1);
	
	// Mode til filer der skal have php upload aktiveret
	$writable_file_mode = cms_setting("writable_file_mode");
	
	// Overskrift
	if (substr($do, -7) <> "_iframe")
	{
		$msg = new message;
		$msg->title("{LANG|Moduler}");
		$html .= $msg->html();
	}
	
	$id = $vars["id"];
	if ($id <> "" and !ereg("^[a-zA-Z0-9_-]+$", $id) and !is_dir($_document_root . "/modules/$id/"))
	{
		header("Location: ?page=$page");
		exit;
	}
	
	// Inkluderer file_editor
	include("pages/file_editor.php");	
	
	if ($do == "edit")
	{
		//
		// Rediger modul
		//
		
		// Henter liste med layout-filer i aktuelle layout
		$file = new file;
		$files = $file->find_files($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/");
		$select_layouts = array(array("", "-"));
		for ($i = 0; $i < count($files); $i++)
		{
			if (!ereg("^menu_", $files[$i]) and $files[$i] <> "default.html" and ereg("\.html$", $files[$i]))
			{
				$filename = ereg_replace("\.html$", "", $files[$i]);
				$select_layouts[count($select_layouts)] = array	($filename, $filename);
			}
		}
		
		// Henter aktuelt layout for dette modul
		$module_layout = unserialize($_settings_["MODULE_LAYOUT"]);
		
		// Mulighed for at vælge hvilket layout der skal benyttes til dette modul
		$frm = new form("module_layout");
		$frm->tpl("th", "{LANG|Modul indstillinger}");
		
		// Sprog		
		$lang = languages_array();
		reset($lang);
		while (list($lang_id, $lang_title) = each($lang))
		{
			$frm->input(
				"{LANG|Titel} " . $lang_title,
				"title_" . $lang_id,
				module2title($id, $lang_id),
				"^[a-zA-Z0-9 _-]+$",
				"{LANG|Må kun bestå af} a-z, 0-9, _, - {LANG|samt mellemrum}"
				);
		}
		
		$frm->select(
			"{LANG|Layout til dette modul}",
			"layout",
			$module_layout[$id],
			"",
			"",
			"",
			$select_layouts
			);
		$frm->tpl("td", nl2br("{LANG|Bemærk}, {LANG|at layoutet vil kun have effekt},\r\n" .
			"{LANG|når modulet kaldes direkte}, {LANG|dvs}. {LANG|ikke hvis}\r\n" .
			"{LANG|et element indsættes på en side}."));
			
		if ($frm->done())
		{
			// Gemmer ny titel
			$file = $_document_root . "/modules/$id/admin/version.php";
			$version = "";
			if (is_file($file)) @include($file);
			if ($fp = fopen($file, "w"))
			{
				fwrite($fp, '<?php' . "\r\n");
				fwrite($fp, '$version = "' . $version . '";' . "\r\n");
				fwrite($fp, '$title = array();' . "\r\n");
				$lang = languages_array();
				reset($lang);
				while (list($lang_id, $lang_title) = each($lang))
				{
					fwrite($fp, '$title["' . $lang_id . '"] = "' . $frm->values["title_" . $lang_id] . '";' . "\r\n");
				}
				fwrite($fp, '?>');
				fclose($fp);
			}
			
			// Gemmer nyt layout
			$module_layout[$id] = $frm->values["layout"];
			$set->set("MODULE_LAYOUT", serialize($module_layout));
			
			// Videre
			header("Location: ?module=$module&page=$page&do=$do&id=$id");
			exit;
		}
			
		$html .= $frm->html();
		
	}
	elseif (($do == "check_updates" or $do == "check_updates_now") and $enable_updates)
	{
		//
		// Søger efter opdateringer til installerede moduler
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		if ($do == "check_updates")
		{
			// Viser infoboks og stiller videre til opdatering
			$msg = new message;
			$msg->type("section");
			$msg->title("{LANG|Tjekker for opdateringer}");
			$msg->message("{LANG|Vent venligst mens systemet tjekker for nye opdateringer}...");
			$html .= $msg->html();
			// Redirect
			$tmp = new tpl("redirect");
			$tmp->set("url", "?page=$page&do=check_updates_now");
			$tmp->set("timeout", "100");
			$html .= $tmp->html();
		}
		else
		{
			// Henter liste med installerede moduler
			$array_installed = admin_module_installed();
			
			// Gennemløber installerede moduler
			$array_updates = array();
			for ($i = 0; $i < count($array_installed); $i++)
			{
				// Henter opdateringsliste til dette modul
				$tmp_updates = admin_module_updates($array_installed[$i]);
				if (count($tmp_updates) > 0)
				{
					$array_updates[$array_installed[$i]] = $tmp_updates;
				}
			}
			
			if (count($array_updates) > 0)
			{
				// Så er der tilgængelige opdateringer
				$msg = new message;
				$msg->type("section");
				$msg->title("{LANG|Der er nye opdateringer tilgængelig}");
				$msg->message("{LANG|Herunder kan du se de nye opdateringer}, {LANG|der er tilgængelig}.");
				$html .= $msg->html();
				// Knap til installation af alle opdateringer
				$links = new links;
				$links->link("{LANG|Installer alle opdateringer}", "update_all", "", "{LANG|Er du sikker på du vil installere alle tilgængelige opdateringer nu}?");
				$html .= $links->html();
				// Viser liste med opdateringer
				$tbl = new table;
				// Viser liste
				reset($array_updates);
				while (list($tmp_module, $updates) = each($array_updates))
				{
					$tbl->th(module2title($tmp_module), 1);
					$tbl->th("{LANG|Version}");
					$tbl->th("{LANG|Valg}");
					$tbl->endrow();
					for ($i = 0; $i < count($updates); $i++)
					{
						$tbl->td($updates[$i][1]);
						$tbl->td(version2date($updates[$i][0]));
						$tbl->td("&nbsp;");
						$tbl->endrow();
					}
					$tbl->td("", 2);
					$tbl->choise("{LANG|Installer}", "update", $tmp_module, "{LANG|Installer opdateringer til modulet} " . module2title($tmp_module) . " {LANG|nu}?\\n\\n{LANG|BEMÆRK}\\n{LANG|Det anbefales på det kraftigste at installere alle opdateringer},\\n{LANG|da denne opdatering kan være afhængig af en tidligere}.");
					$tbl->endrow();
				}
				$html .= $tbl->html();
			}
			else
			{
				// Ingen opdateringern
				$msg = new message;
				$msg->type("section");
				$msg->title("{LANG|Opdateringer}");
				$msg->message("{LANG|Der er ingen tilgængelige opdateringer}...");
				$html .= $msg->html();
			}
		}
		
	}
	elseif (($do == "update_iframe" or $do == "update_dummy_iframe") and ($enable_updates or $enabled_auto_update))
	{
		//
		// Opdater modul - iframe
		//
		
		$dummy = ($do == "update_dummy_iframe");
		
		// Modul
		$tmp_module = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_module)) die("Ugyldigt modul");
		
		if (!$dummy)
		{
			// Javascript realtime klasse
			$js = new js_realtime();
			$js->pause_after_update(.1);
		}

		//
		// Starter installation
		//
		if (!$dummy)
		{
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 0);
			$tmp->set("text", "{LANG|Starter opdatering}");
			$js->update($tmp->html());
		}
		
		// FTP
		$ftp = new ftp;
		
		// Forbinder
		$ftp->connect($_cms_ftp_server) or die("Kunne ikke forbinde til FTP-server (" . $_cms_ftp_server . ")");
		
		// Logger ind
		$ftp->login($_cms_ftp_username, $_cms_ftp_password) or die("Kunne ikke logge på FTP-server (" . $_cms_ftp_username . ")");
		
		// Skifter til rod-mappe
		if ($_cms_ftp_root <> "")
		{
			$ftp->chdir($_cms_ftp_root) or die("Kunne ikke skifte til rod-mappe (" . $_cms_ftp_root . ")");
		}
		
		// Henter opdateringsliste til dette modul
		$tmp_updates = admin_module_updates($tmp_module);
		
		// Gennemløber opdateringer til dette modul
		for ($i1 = 0; $i1 < count($tmp_updates); $i1++)
		{
			
			//
			// Installerer opdatering
			//
			if (!$dummy)
			{
				$tmp = new tpl("progressbar");
				$tmp->set("percent", 0);
				$tmp->set("text", "{LANG|Installerer opdatering} " . version2date($tmp_updates[$i1][0]));
				$js->update($tmp->html());
			}
				
			//
			// Henter filliste til opdatering
			//
			if (!$dummy)
			{
				$tmp = new tpl("progressbar");
				$tmp->set("percent", 20);
				$tmp->set("text", "{LANG|Henter filliste}");
				$js->update($tmp->html());
			}
			
			// Henter fil-liste for modul
			$array_file_list = admin_module_update_file_list($tmp_module, admin_module_version_installed($tmp_module));
			
			//
			// Opretter mapper
			//		
			if (!$dummy)
			{
				$tmp = new tpl("progressbar");
				$tmp->set("percent", 40);
				$tmp->set("text", "{LANG|Opdaterer mapper}");
				$js->update($tmp->html());
			}
	
			// Gennemløber og finder mapper, der skal oprettes
			for ($i = 0; $i < count($array_file_list); $i++)
			{
				$array = $array_file_list[$i];
				if ($array[0] == "FOLDER")
				{
					// Opretter mappe hvis den ikke findes i forvejen
					if (!is_dir($_document_root . "/modules/$tmp_module/" . $array[1]))
					{
						$tmp_mode = (eregi("/(upl|html|img|js|css|tmp)(/){0,1}$", "/" . $array[1]) ? $writable_file_mode : false);
						$ftp->mkdir("modules/$tmp_module/" . $array[1], $tmp_mode) or die("Kunne ikke opdatere mappe (modules/$tmp_module/" . $array[1] . ")");
					}
				}
			}
	
			//
			// Kopierer filer
			//
			if (!$dummy)
			{
				$tmp = new tpl("progressbar");
				$tmp->set("percent", 60);
				$tmp->set("text", "{LANG|Opdaterer filer}");
				$js->update($tmp->html());
			}
				
			// Gennemløber og finder mapper, der skal opdateres
			$tmp_file = $_tmp_dir . uniqid("");
			for ($i = 0; $i < count($array_file_list); $i++)
			{
				$array = $array_file_list[$i];
				if ($array[0] == "FILE")
				{
					// Henter fil
					$data = admin_module_file_get($tmp_module, $array[1]);
					// Gemmer i tmp-mappe
					if ($fp = fopen($tmp_file, "w"))
					{
						fwrite($fp, $data);
						fclose($fp);
						// Er der en lock-fil?
						if (is_file($_document_root . "/modules/$tmp_module/" . $array[1] . ".lock"))
						{
							// Findes .new allerede?
							if (is_file($_document_root . "/modules/$tmp_module/" . $array[1] . ".new"))
							{
								// Sletter fil via FTP
								$ftp->delete("modules/$tmp_module/" . $array[1] . ".new");
							}
							// Uploader fil som .new
							$ftp->upload($tmp_file, "modules/$tmp_module/" . $array[1] . ".new", $writable_file_mode) or die("Kunne ikke opdatere fil (modules/$tmp_module/" . $array[1] . ".new)");
						}
						else
						{						
							// Tjekker om filen findes i forvejen
							if (is_file($_document_root . "/modules/$tmp_module/" . $array[1]))
							{
								if (eregi("\.(html|js|css)$", $array[1]))
								{
									// Laver ny filversion af denne fil
									$version_file = $array[1] . "." . date("Ymd");
									if (!is_file($_document_root . "/modules/$tmp_module/" . $version_file))
									{
										// Omdøber eksisterende fil
										rename($_document_root . "/modules/$tmp_module/" . $array[1],
											$_document_root . "/modules/$tmp_module/" . $version_file);
									}
									else
									{
										// Sletter fil via FTP
										$ftp->delete("modules/$tmp_module/" . $array[1]) or die("Kunne ikke slette fil (modules/$tmp_module/" . $version_file . ")");
									}
								}
								else
								{
									// Sletter fil via FTP
									$ftp->delete("modules/$tmp_module/" . $array[1]) or die("Kunne ikke slette fil (modules/$tmp_module/" . $version_file . ")");
								}
							}
							// Uploader fil
							$tmp_mode = ((eregi("/(html|js|css|img)/", $array[1]) and substr($array[1], -4) != ".php") ? $writable_file_mode : false);
							$ftp->upload($tmp_file, "modules/$tmp_module/" . $array[1], $tmp_mode) or die("Kunne ikke opdatere fil (modules/$tmp_module/" . $array[1] . ")");
						}
						// Sletter fil
						@unlink($tmp_file);
					}
				}
			}
			
			//
			// Afslutter installationen
			//			
			if (!$dummy)
			{
				$tmp = new tpl("progressbar");
				$tmp->set("percent", 80);
				$tmp->set("text", "{LANG|Afslutter opdatering}");
				$js->update($tmp->html());
			}
			
			// Inkluderer installationsfil
			if (is_file($_document_root . "/modules/$tmp_module/admin/install.php"))
			{
				// Gemmer aktuelt modulnavn
				$tmp_install_module = $module;
				$module = $tmp_module;
				// Inkluderer installations-fil
				include($_document_root . "/modules/$tmp_module/admin/install.php");
				// Henter gammelt modulnavn
				$module = $tmp_intall_module;
			}
					
			// Gennemløber og finder PHP-kode, der skal udføres
			for ($i = 0; $i < count($array_file_list); $i++)
			{
				$array = $array_file_list[$i];
				if ($array[0] == "EVAL")
				{
					// Gemmer aktuelt modulnavn
					$tmp_install_module = $module;
					$module = $tmp_module;
					// Udfører kode
					eval(base64_decode($array[1]));
					// Henter gammelt modulnavn
					$module = $tmp_install_module;
				}
			}
			
			// Ændrer versions-nummer for modul
			$tmp_file = $_tmp_dir . uniqid("");
			if ($fp = fopen($tmp_file, "w"))
			{
				fwrite($fp, '<?php' . "\r\n");
				fwrite($fp, '$version = "' . $tmp_updates[$i1][0] . '";' . "\r\n");
				fwrite($fp, '$title = array();' . "\r\n");
				$lang = languages_array();
				reset($lang);
				while (list($lang_id, $lang_title) = each($lang))
				{
					fwrite($fp, '$title["' . $lang_id . '"] = "' . module2title($tmp_module, $lang_id) . '";' . "\r\n");
				}
				fwrite($fp, '?>');
				fclose($fp);
				
				// Opdaterer fil via FTP
				$ftp->delete("modules/$tmp_module/admin/version.php") or die("Kunne ikke slette versionsfil");
				$ftp->upload($tmp_file, "modules/$tmp_module/admin/version.php") or die("Kunne ikke opdatere versionsfil");
				
				// Sletter fil
				@unlink($tmp_file);
			}
			
			// Tilføjet til log
			add_log_message("Update complete\r\n" .
				"Module: $tmp_module\r\n" .
				"Version: " . $tmp_updates[$i1][0]);
			
		}
		
		// Lukker FTP-forbindelse
		$ftp->close();
		
		//
		// Færdig
		//
		if (!$dummy)
		{
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 100);
			$tmp->set("text", "{LANG|Opdatering færdig}");
			$js->update($tmp->html());
			
			// Afslutter JavaScript realtime klasse
			echo("<script> parent.parent.frames['menu_frame'].document.location.reload(); </script>");
			$js->end();
		}
			
		$tpl = "empty";
		$html = "";
		
	}
	elseif ($do == "update" and $enable_updates)
	{
		//
		// Opdater modul
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Modul
		$tmp_module = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_module))
		{
			header("Location: ?page=$page");
			exit;
		}
				
		// Tjekker om modul allerede er installeret
		if (!is_dir($_document_root . "/modules/$tmp_module/"))
		{
			header("Location: ?page=$page");
			exit;
		}
		
		// Besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Opdatering af modulet} " . module2title($tmp_module));
		$msg->message("{LANG|Vent venligst mens modulet opdateres}. {LANG|Forlad ikke denne side mens modulet opdateres}.");
		$html .= $msg->html();
		
		// Viser iframe med opdatering af modul
		$tmp = new tpl("iframe");
		$tmp->set("src", "?page=modules&do=update_iframe&id=$tmp_module");
		$tmp->set("width", "400");
		$tmp->set("height", "100");
		$html .= $tmp->html();
		
	}
	elseif ($do == "update_all" and $enable_updates)
	{
		//
		// Opdater alle moduler
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Opdatering af moduler}");
		$msg->message("{LANG|Vent venligst mens moduler opdateres}. {LANG|Forlad ikke denne side mens modulerne opdateres}.");
		$html .= $msg->html();
		
		// Viser iframe med opdatering af moduler
		$tmp = new tpl("iframe");
		$tmp->set("src", "?page=modules&do=update_all_iframe");
		$tmp->set("width", "400");
		$tmp->set("height", "100");
		$html .= $tmp->html();
		
	}
	elseif ($do == "update_all_iframe" and ($enable_updates or $enabled_auto_update))
	{
		//
		// Opdater alle moduler - iframe
		//
		
		// Henter liste med installerede moduler
		$array_installed = admin_module_installed();
		
		// Gennemløber installerede moduler
		$array_update_all = array();
		for ($i = 0; $i < count($array_installed); $i++)
		{
			// Henter opdateringsliste til dette modul
			$tmp_updates = admin_module_updates($array_installed[$i]);
			if (count($tmp_updates) > 0)
			{
				$array_update_all[$array_installed[$i]] = $tmp_updates;
			}
		}
		
		$js = new js_realtime;
		$js->pause_after_update(.1);
		
		$do = "update_dummy_iframe";
			
		// Gennemløber liste
		reset($array_update_all);
		$count_update_all = 0;
		$total_update_all = count($array_update_all);
		while (list($vars["id"], $updates) = each($array_update_all))
		{
			// Viser procesbar
			$tmp = new tpl("progressbar");
			$tmp->set("percent", round($count_update_all / $total_update_all * 100));
			$tmp->set("text", "{LANG|Opdaterer modulet} '" . module2title($vars["id"]) . "'");
			$js->update($tmp->html());
			// Installerer opdatering
			require($_document_root . "/Admin/pages/modules.php");
			$count_update_all++;
		}
		
		// Færdig
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 100);
		$tmp->set("text", "{LANG|Opdatering gennemført}");
		$js->update($tmp->html());
		
		echo("
			<script>
			try
			{
				parent.parent.frames['menu_frame'].document.location.reload();
			}
			catch(e)
			{
				try
				{
					parent.updateModulesDone();
				}
				catch(e)
				{
					// dummy
				}
			}
			</script>
			");
		$js->end();
		
		$tpl = "empty";
		$html = "";
		
	}
	elseif ($do == "install_iframe" and $enable_updates)
	{
		//
		// Installerer modul - iframe
		//
		
		// Modul
		$tmp_module = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_module)) die("Ugyldigt modul");
		
		// Finder navn til installation
		$tmp_module_as = $tmp_module;
		$tmp_count = 0;
		while (is_dir($_document_root . "/modules/" . $tmp_module_as))
		{
			$tmp_count++;
			$tmp_module_as = $tmp_module . "__" . $tmp_count . "__";
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
		
		// Henter fil-liste for modul
		$array_file_list = admin_module_file_list($tmp_module);
			
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
		
		// Opretter modul-mappe
		$ftp->mkdir("modules/$tmp_module_as") or die("Kunne ikke oprette modul-mappe");
		
		// Gennemløber og finder mapper, der skal oprettes
		for ($i = 0; $i < count($array_file_list); $i++)
		{
			$array = $array_file_list[$i];
			if ($array[0] == "FOLDER")
			{
				// Opretter mappe
				$tmp_mode = (eregi("/(upl|html|img|js|css|tmp)(/){0,1}$", "/" . $array[1]) ? $writable_file_mode : false);
				$ftp->mkdir("modules/$tmp_module_as/" . $array[1], $tmp_mode) or die("Kunne ikke oprette mappe");
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
				$data = admin_module_file_get($tmp_module, $array[1]);
				// Gemmer i tmp-mappe
				if ($fp = fopen($tmp_file, "w"))
				{
					fwrite($fp, $data);
					fclose($fp);
					// Tjekker om filen findes i forvejen
					if (is_file($_document_root . "/modules/$tmp_module_as/" . $array[1]))
					{
						// Sletter fil via FTP
						$ftp->delete("modules/$tmp_module_as/" . $array[1]) or die("Kunne ikke slette fil");
					}
					// Uploader fil
					$tmp_mode = ((eregi("/(html|js|css|img)/", $array[1]) and substr($array[1], -4) != ".php") ? $writable_file_mode : false);
					$ftp->upload($tmp_file, "modules/$tmp_module_as/" . $array[1], $tmp_mode) or die("Kunne ikke uploade fil");
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
		
		// Gennemløber og finder PHP-kode, der skal udføres
		for ($i = 0; $i < count($array_file_list); $i++)
		{
			$array = $array_file_list[$i];
			if ($array[0] == "EVAL")
			{
				// Gemmer aktuelt modulnavn
				$tmp_install_module = $module;
				$module = $tmp_module;
				// Udfører kode
				eval(base64_decode($array[1]));
				// Henter gammelt modulnavn
				$module = $tmp_install_module;
			}
		}
		
		// Inkluderer installationsfil
		if (is_file($_document_root . "/modules/$tmp_module_as/admin/install.php"))
		{
			// Gemmer aktuelt modulnavn
			$tmp_install_module = $module;
			$module = $tmp_module_as;
			// Inkluderer installations-fil
			include($_document_root . "/modules/$tmp_module_as/admin/install.php");
			// Henter gammelt modulnavn
			$module = $tmp_intall_module;
		}
				
		// Opdaterer version.php med titel på modulet
		$file = $_document_root . "/modules/$tmp_module_as/admin/version.php";
		$version = "";
		if (is_file($file)) @include($file);
		if (eregi("^[a-z0-9 _-]+$", $vars["title"]))
		{
			// Gemmer ny titel
			if ($fp = fopen($file, "w"))
			{
				fwrite($fp, '<? $version = "' . $version . '"; ' .
					'$title = "' . $vars["title"] . '"; ?>');
				fclose($fp);
			}
		}
		
		// Tilføjer til log
		add_log_message("Install complete\r\n" .
			"Module: $tmp_module_as\r\n" .
			"Version: " . $version);
		
		//
		// Færdig
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 100);
		$tmp->set("text", "{LANG|Installation færdig}");
		$js->update($tmp->html());
		
		// Afslutter JavaScript realtime klasse
		echo("<script> parent.parent.frames['menu_frame'].document.location.reload(); </script>");
		$js->end();
			
		$tpl = "empty";
		$html = "";
		
	}
	elseif ($do == "install" and $enable_updates)
	{
		//
		// Installer modul
		//

		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "show_avaiable");
		$html .= $links->html();
		
		// Modul
		$tmp_module = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_module))
		{
			header("Location: ?page=$page");
			exit;
		}
				
		/*
		// Tjekker om modul allerede er installeret
		if (is_dir($_document_root . "/modules/$tmp_module/"))
		{
			header("Location: ?page=$page");
			exit;
		}
		*/
		
		// Besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Installation af modulet} " . str_replace("_", " ", $tmp_module));
		$msg->message("{LANG|Vent venligst mens modulet installeres}. {LANG|Forlad ikke denne side mens modulet installeres}.");
		$html .= $msg->html();
		
		// Viser iframe med installation af modul
		$tmp = new tpl("iframe");
		$tmp->set("src", "?page=modules&do=install_iframe&id=$tmp_module&title=" . urlencode($vars["title"]));
		$tmp->set("width", "400");
		$tmp->set("height", "100");
		$html .= $tmp->html();
		
	}
	elseif ($do == "remove_iframe" and $enable_updates)
	{
		//
		// Installerer modul - iframe
		//
		
		// Modul
		$tmp_module = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_module)) die("Ugyldigt modul");
		
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
		
		// Inkluderer af-installationsfil
		if (is_file($_document_root . "/modules/$tmp_module/admin/uninstall.php"))
		{
			// Gemmer aktuelt modulnavn
			$tmp_install_module = $module;
			$module = $tmp_module;
			// Inkluderer afinstallations-fil
			include($_document_root . "/modules/$tmp_module/admin/uninstall.php");
			// Henter gammelt modul-navn
			$module = $old_module;
		}
		
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
		$files = $file->find_files($_document_root . "/modules/$tmp_module/", true);
		
		// Gennemløber og sletter filer
		for ($i = 0; $i < count($files); $i++)
		{
			// Sletter fil via FTP
			$ftp->delete("modules/$tmp_module/" . $files[$i]) or die("Kunne ikke slette fil");
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
		$folders = $file->find_folders($_document_root . "/modules/$tmp_module/", true);
		
		// Gennemløber og sletter mapper
		for ($i = 0; $i < count($folders); $i++)
		{
			// Sletter mappe via FTP
			$ftp->rmdir("modules/$tmp_module/" . $folders[$i]); // or die("Kunne ikke slette mappe");
		}
		
		// Sletter modul-mappe
		$ftp->rmdir("modules/$tmp_module"); // or die("Kunne ikke slette modul-mappe");
		
		// Lukker FTP-forbindelse
		$ftp->close();
		
		// Sletter modul indstillinger
		$db->execute("
			DELETE FROM
				" . $_table_prefix . "_settings_module
			WHERE
				module = '" . $db->escape($tmp_module) . "'
			");
		
		//
		// Færdig
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 100);
		$tmp->set("text", "{LANG|Af-Installation færdig}");
		$js->update($tmp->html());
		
		// Afslutter JavaScript realtime klasse
		echo("<script> parent.parent.frames['menu_frame'].document.location.reload(); </script>");
		$js->end();
			
		$tpl = "empty";
		$html = "";
		
	}
	elseif ($do == "remove" and $enable_updates)
	{
		//
		// Fjerner modul
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Modul
		$tmp_module = $vars["id"];
		if (!ereg("^[a-zA-Z0-9_-]+$", $tmp_module))
		{
			header("Location: ?page=$page");
			exit;
		}
				
		// Tjekker om modul er installeret
		if (!is_dir($_document_root . "/modules/$tmp_module/"))
		{
			header("Location: ?page=$page");
			exit;
		}
		
		if ($_cms_ftp_server <> "")
		{
			
			// Besked
			$msg = new message;
			$msg->type("section");
			$msg->title("{LANG|Af-Installation af modulet} " . str_replace("_", " ", $tmp_module));
			$msg->message("{LANG|Vent venligst mens modulet afinstalleres}. {LANG|Forlad ikke denne side mens modulet afinstalleres}.");
			$html .= $msg->html();
			
			// Viser iframe med installation af modul
			$tmp = new tpl("iframe");
			$tmp->set("src", "?page=modules&do=remove_iframe&id=$tmp_module");
			$tmp->set("width", "400");
			$tmp->set("height", "100");
			$html .= $tmp->html();
			
		}
		else
		{
		
			// Fejl-besked
			$msg = new message;
			$msg->type("section");
			$msg->title("{LANG|Af-Installation af modulet} " . str_replace("_", " ", $tmp_module));
			$msg->message("{LANG|Modulet kan ikke af-installeres}.");
			$html .= $msg->html();
			
		}
		
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
		if ($vars["title"] == "")
		{
			$title = $id;
			$count = 0;
			while (is_dir($_document_root . "/modules/" . $id . "__" . $count . "__") or
				(is_dir($_document_root . "/modules/" . $id) and $count == 0))
			{
				$count++;
				$title = $id . " " . $count;
			}
		}
		else
		{
			$title = $vars["title"];
		}
	
		// Formular
		$frm = new form;
		$frm->tpl("th", "{LANG|Installation af modul}");
		$frm->tpl("td2", "{LANG|Modul}:", $id);
		$frm->input(
			"{LANG|Titel}",
			"title",
			$title,
			"^[a-zA-Z0-9 _-]+$",
			"{LANG|Må kun bestå af} a-z, 0-9, _, - {LANG|samt mellemrum}"
			);
			
		if ($frm->done())
		{
			header("Location: ?module=$module&page=$page&do=install&id=$id&title=" .
				urlencode($title));
			exit;
		}
		
		$html .= $frm->html();
		
	}
	elseif ($do == "show_avaiable" and $enable_updates)
	{
		//
		// Viser liste med tilgængelig moduler
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
	
		// Henter liste med tilgængelige moduler
		$array_avaiable = admin_module_avaiable();
		
		// Tilgængelige moduler
		$tbl = new table;
		$tbl->th("{LANG|Tilgængelige moduler}", 3);
		$tbl->endrow();
		$tbl->th("{LANG|Modul}");
		$tbl->th("{LANG|Version}");
		$tbl->th("{LANG|Valg}");
		$tbl->endrow();
		
		$count = 0;
		for ($i = 0; $i < count($array_avaiable); $i++)
		{
			$count++;
			$tbl->td($array_avaiable[$i][0]);
			$tbl->td(version2date($array_avaiable[$i][1]));
			$tbl->choise("{LANG|Installer}", "install_intro", $array_avaiable[$i][0]);
			$tbl->endrow();
		}
		
		if ($count == 0)
		{
			$tbl->td("{LANG|Ingen tilgængelige moduler}...", 3);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();	
		
			
	}
	elseif ($do == "")
	{
		//
		// Oversigt
		//

		// Links
		$links = new links;
		if ($enable_updates)
		{
			$links->link("{LANG|Hent flere moduler}", "show_avaiable");
			$links->link("{LANG|Tjek for opdateringer}", "check_updates");
		}
		$html .= $links->html();
	
		// Henter liste med installerede moduler
		$array_installed = admin_module_installed();
		
		// Installerede moduler
		$tbl = new table;
		$tbl->th("{LANG|Installerede moduler}", 5);
		$tbl->endrow();
		$tbl->th("{LANG|Modul}");
		$tbl->th("{LANG|Titel}");
		$tbl->th("{LANG|Version}");
		$tbl->th("{LANG|Valg}", 2);
		$tbl->endrow();
		
		for ($i = 0; $i < count($array_installed); $i++)
		{
			$tbl->td($array_installed[$i]);
			$tbl->td(module2title($array_installed[$i]));
			$tbl->td(version2date(admin_module_version_installed($array_installed[$i])));
			$tbl->choise("{LANG|Rediger}", "edit", $array_installed[$i]);
			if ($enable_updates) $tbl->choise("{LANG|Afinstaller}", "remove", $array_installed[$i], "{LANG|Er du sikker på du vil afinstallere dette modul}? {LANG|Evt}. {LANG|data vil gå tabt}!");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();	
		
	}
?>
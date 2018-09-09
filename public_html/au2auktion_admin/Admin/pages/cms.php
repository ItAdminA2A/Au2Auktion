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
		Version:		06-04-2006
		Beskrivelse:	CMS-system
	*/
	
	// Mode til filer der skal have php upload aktiveret
	$writable_file_mode = cms_setting("writable_file_mode");
	
	// Overskrift
	if (substr($do, -7) <> "_iframe")
	{
		$msg = new message;
		$msg->title("{LANG|CMS grundsystem}");
		$html .= $msg->html();
	}
	
	if ($do == "check_updates" or $do == "check_updates_now")
	{
		//
		// Søger efter opdateringer til CMS-systemet
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
			$tmp->set("timeout", "1000");
			$html .= $tmp->html();
		}
		else
		{
			// Henter opdateringsliste til CMS-systemet
			$array_updates = admin_cms_updates();
			
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
				$tbl->th("{LANG|Tilgængelige opdateringer}", 2);
				$tbl->endrow();
				// Viser liste
				for ($i = 0; $i < count($array_updates); $i++)
				{
					$tbl->td($array_updates[$i][1]);
					$tbl->td(version2date($array_updates[$i][0]));
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
	elseif ($do == "update_all")
	{
		//
		// Installer alle opdateringer
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		// Besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Opdatering af CMS grundsystem}");
		$msg->message("{LANG|Vent venligst mens CMS grundsystemet opdateres}. {LANG|Forlad ikke denne side under opdateringen}.");
		$html .= $msg->html();
		
		// Viser iframe med opdatering af CMS grundsystemet
		$tmp = new tpl("iframe");
		$tmp->set("src", "?page=$page&do=update_all_iframe");
		$tmp->set("width", "400");
		$tmp->set("height", "100");
		$html .= $tmp->html();
		
	}
	elseif ($do == "update_all_iframe")
	{
		//
		// Installer alle opdateringer - iframe
		//
		
		// Javascript realtime klasse
		$js = new js_realtime();
		$js->pause_after_update(.1);

		//
		// Starter installation
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 0);
		$tmp->set("text", "{LANG|Starter opdatering}");
		$js->update($tmp->html());
		
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
		
		// Henter opdateringsliste til CMS grundsystem
		$tmp_updates = admin_cms_updates();
		
		// Gennemløber opdateringer til dette modul
		for ($i1 = 0; $i1 < count($tmp_updates); $i1++)
		{
			
			//
			// Installerer opdatering
			//
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 0);
			$tmp->set("text", "{LANG|Installerer opdatering} " . version2date($tmp_updates[$i1][0]));
			$js->update($tmp->html());
				
			//
			// Henter filliste til opdatering
			//
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 20);
			$tmp->set("text", "{LANG|Henter filliste}");
			$js->update($tmp->html());
			
			// Henter fil-liste for modul
			$array_file_list = admin_cms_update_file_list(admin_cms_version());
			
			//
			// Opretter mapper
			//		
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 40);
			$tmp->set("text", "{LANG|Opdaterer mapper}");
			$js->update($tmp->html());
	
			// Gennemløber og finder mapper, der skal oprettes
			for ($i = 0; $i < count($array_file_list); $i++)
			{
				$array = $array_file_list[$i];
				if ($array[0] == "FOLDER")
				{
					// Opretter mappe
					if (!is_dir($_document_root . "/" . $array[1]))
					{
						$tmp_mode = (eregi("/(upl|html|img|js|css|tmp)(/){0,1}$", $array[1]) ? $writable_file_mode : false);
						$ftp->mkdir($array[1], $tmp_mode) or die("Kunne ikke opdatere mappe (" . $array[1] . ")");
					}
				}
			}
	
			//
			// Kopierer filer
			//
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 60);
			$tmp->set("text", "{LANG|Opdaterer filer}");
			$js->update($tmp->html());
				
			// Gennemløber og finder filer, der skal opdateres
			$tmp_file = $_tmp_dir . uniqid("");
			for ($i = 0; $i < count($array_file_list); $i++)
			{
				$array = $array_file_list[$i];
				if ($array[0] == "FILE")
				{
					// Henter fil
					$data = admin_cms_file_get($array[1]);
					// Gemmer i tmp-mappe
					if ($fp = fopen($tmp_file, "w"))
					{
						fwrite($fp, $data);
						fclose($fp);
						// Tjekker om filen findes i forvejen
						if (is_file($_document_root . "/" . $array[1]))
						{
							// Sletter fil via FTP
							$ftp->delete($array[1]) or die("Kunne ikke slette fil (" . $array[1] . ")");
						}
						// Uploader fil
						$ftp->upload($tmp_file, $array[1]) or die("Kunne ikke opdatere fil (" . $array[1] . ")");
						// Sletter fil
						@unlink($tmp_file);
					}
				}
			}
			
			//
			// Afslutter installationen
			//			
			$tmp = new tpl("progressbar");
			$tmp->set("percent", 80);
			$tmp->set("text", "{LANG|Afslutter opdatering}");
			$js->update($tmp->html());
			
			// Gennemløber og finder PHP-kode, der skal udføres
			for ($i = 0; $i < count($array_file_list); $i++)
			{
				$array = $array_file_list[$i];
				if ($array[0] == "EVAL")
				{
					// Udfører kode
					eval(base64_decode($array[1]));
				}
			}
			
			// Ændrer versions-nummer denne opdatering
			$tmp_file = $_tmp_dir . uniqid("");
			if ($fp = fopen($tmp_file, "w"))
			{
				fwrite($fp, '<? $version = "' . $tmp_updates[$i1][0] . '"; ?>');
				fclose($fp);
				// Opdaterer fil via FTP
				$ftp->delete("version.php");
				$ftp->upload($tmp_file, "version.php") or die("Kunne ikke opdatere versionsfil");
				// Sletter fil
				@unlink($tmp_file);
			}
			
			// Tilføjer til log
			add_log_message("Update complete\r\n" .
				"CMS core system\r\n" .
				"Version: " . $tmp_updates[$i1][0]);
			
		}
		
		// Lukker FTP-forbindelse
		$ftp->close();
		
		//
		// Færdig
		//
		$tmp = new tpl("progressbar");
		$tmp->set("percent", 100);
		$tmp->set("text", "{LANG|Opdatering færdig}");
		$js->update($tmp->html());
			
		// Afslutter JavaScript realtime klasse
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
					parent.updateCmsDone();
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
	elseif ($do == "")
	{
		//
		// Oversigt
		//
	
		// Links
		$links = new links;
		$links->link("{LANG|Tjek for opdateringer}", "check_updates");
		$html .= $links->html();
	
		// Installeret version
		$tbl = new table;
		$tbl->th("{LANG|CMS grundsystem}", 2);
		$tbl->endrow();
		
		// Version
		$tbl->td("{LANG|Version}:");
		$tbl->td(version2date(admin_cms_version()));
		$tbl->endrow();
		
		$html .= $tbl->html();	
		
	}
?>
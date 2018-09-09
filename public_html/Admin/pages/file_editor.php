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
		Beskrivelse:	Generel editor til redigering af filer i forbindelse med moduler og layouts
		30-04-2007:		Styring af versionering af HTML-filer
		24-01-2008:		Mulighed for at låse filer og se hvis der er kommet nye udgaver ved
						opdatering (kun moduler)
	*/
	
	if ($page <> "layouts" and $page <> "modules" and $page <> "cms") die("Denne fil må ikke kaldes direkte !");
	
	// Type
	$type_file = "";
	$type_title = "";
	$type_data = "";
	if (ereg("_css$", $do))
	{
		$type_file = "css";
		$type_title = "{LANG|Stylesheet}";
		$type_data = "CSS";
	}
	elseif (ereg("_html$", $do))
	{
		$type_file = "html";
		$type_title = "{LANG|HTML-fil}";
		$type_data = "HTML";
	}
	elseif (ereg("_js$", $do))
	{
		$type_file = "js";
		$type_title = "{LANG|JavaScript-fil}";
		$type_data = "JavaScript";
	}
	elseif (ereg("_img$", $do))
	{
		$type_file = "img";
		$type_title = "{LANG|Billede}";
		$type_data = "{LANG|Billede}";
	}
	
	if ($do == "add_css" or $do == "add_html" or $do == "add_js")
	{
		//
		// Tilføj fil
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "edit", $id);
		$html .= $links->html();
		
		// Laver formular
		$frm = new form;
		$frm->submit_text = "{LANG|Gem}";
		$frm->tpl("th", "{LANG|Tilføj} $type_title (" . str_replace("_", " ", $id) . ")");
		$frm->textarea(
			$type_data,
			"data",
			$data,
			"",
			"",
			"",
			100,
			20
			);
		$frm->input(
			"{LANG|Gem som}",
			"new_filename",
			"",
			"^[a-zA-Z0-9_-]+$",
			"{LANG|Skal angives og må bestå af} a-z, A-Z, 0-9 _ {LANG|samt} -, {LANG|og skal angives uden fil-endelse}",
			'
				if (is_file("' . $_document_root . '//' . $id . '/' . $type_file . '/" . $this->values["new_filename"] . ".' . $type_file . '"))
				{
					$error = "{LANG|Der findes allerede en fil med det angivne navn}";
				}
			'
			);
			
		if ($frm->done())
		{
			// Gemmer data
			$data = stripslashes($frm->values["data"]);
			if ($fp = fopen($_document_root . "/$page/$id/" . $type_file . "/" . $frm->values["new_filename"] . "." . $type_file, "w"))
			{
				fwrite($fp, $data);
				fclose($fp);
				// Tilbage
				header("Location: ?page=$page&do=edit&id=$id");
				exit;
			}
			else
			{
				errorhandler("Kunne ikke gemme " . $type_title);
			}
		}
			
		$html .= $frm->html();
				
	}
	elseif ($do == "add_img")
	{
		//
		// Tilføj billede
		//
		
		// Links
		$links = new links;
		$links->link("Tilbage", "edit", $id);
		$html .= $links->html();
		
		// Formular til upload af billede
		
		$frm = new form;
		$frm->tpl("th", "{LANG|Tilføj billede} (" . str_replace("_", " ", $id) . ")");
		$frm->file(
			"{LANG|Billede}",
			"new_image",
			true,
			"{LANG|Skal vælges}"
			);
		$frm->input(
			"{LANG|Gem som}",
			"new_filename",
			"",
			"^[a-zA-Z0-9_-]+$",
			"{LANG|Filnavnet må bestå af} a-z, 0-9 _ {LANG|samt} - {LANG|angives uden fil-endelse}",
			'
				if (is_file("' . $_document_root . '/$page/' . $id . '/img/" . $this->values["new_filename"] . "." . $this->values["new_image_realext"]))
				{
					$error = "{LANG|Der findes allerede en fil med samme navn} !";
				}
			'
			);
			
		if ($frm->done())
		{
			// Gemmer billede
			if (!rename($_document_root . $frm->values["new_image"],
				$_document_root . "/$page/$id/img/" . $frm->values["new_filename"] . "." . $frm->values["new_image_realext"]))
			{
				// Kunne ikke omdøbe billede
				errorhandler("Kunne ikke flytte uploadede fil");
			}
			else
			{
				// Sletter formular
				$frm->cleanup();
				// Videre
				header("Location: ?page=$page&do=edit&id=$id");
			}
		}
		
		$html .= $frm->html();
		
	}
	elseif ($do == "delete_img" or $do == "delete_html" or $do == "delete_js" or $do == "delete_css")
	{
		//
		// Slet fil
		//
		
		$file = $vars["file"];
		
		if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}[a-z]+$", $file) or !is_file($_document_root . "/$page/$id/$type_file/$file"))
		{
			header("Location: ?page=$page&do=edit&id=$id");
			exit;
		}
	
		@unlink($_document_root . "/$page/$id/$type_file/$file");
		header("Location: ?page=$page&do=edit&id=$id");
		exit;
		
	}
	elseif ($do == "edit_img")
	{
		//
		// Rediger billede
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "edit", $id);
		$html .= $links->html();
		
		$img = $vars["file"];
		
		if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}[a-z]+$", $img) or !is_file($_document_root . "/$page/$id/img/$img"))
		{
			header("Location: ?page=$page&do=edit&id=$id");
			exit;
		}
		
		$image = new image;

		// Størrelse på aktuelt billede
		$width = $image->width($_document_root . "/$page/$id/img/$img");
		$height = $image->height($_document_root . "/$page/$id/img/$img");
		
		// Filtype
		$ext = strtolower(substr($img, strrpos($img, ".") + 1));
		
		// Formular til erstatning af billede
		$frm = new form;
		$frm->hidden("file", $img);
		$frm->tpl("th", "{LANG|Erstat billede}");
		$frm->file(
			"{LANG|Billede}",
			"new_image",
			true,
			"{LANG|Skal vælges}",
			array($ext)
			);
			
		if (is_file($_document_root . $frm->values["new_image"]))
		{
			if ($frm->values["confirm_error"] == "" and !$tmp_img = $image->imagecreatefromfile($_document_root . $frm->values["new_image"]))
			{
				// Billede kan ikke læses - advarer om dette
				$frm->tpl("td", nl2br("{LANG|Billedet kunne ikke læses}, {LANG|og systemet kan derfor ikke afgøre hvorvidt billedet har}\r\n" .
					"{LANG|samme størrelse som det oprindelige billede}. " . 
					"{LANG|Derfor kan billedet ikke anvendes}."));
				$frm->cleanup();
				$frm->submit_text = "{LANG|Upload nyt billede}";
			}
			elseif ($frm->values["confirm_error"] == "" and (imagesx($tmp_img) <> $width or imagesy($tmp_img) <> $height))
			{
				// Størrelse stemmer ikke overens
				$frm->tpl("td", nl2br("{LANG|Billedets dimensioner stemmer ikke overens med det oprindelige billede}.\r\n " .
					"{LANG|Det uploadede billede er} " . 
					$image->size_formatted($_document_root . $frm->values["new_image"]) .
					" {LANG|og det oprindelige billede er} " .
					$image->size_formatted($_document_root . "/$page/$id/img/$img") . ".\r\n" .
					"{LANG|Er du sikker på du vil erstatte det oprindelige billede med det du netop har uploadet}?"));
				$frm->checkbox(
					"{LANG|Ja}, {LANG|benyt det uploadede billede}",
					"confirm_error",
					false,
					true,
					nl2br("{LANG|Bekræft her hvis du vil}\r\n{LANG|overskrive nuværende billede}")
					);
			}
			else
			{
				// Overskriver oprindelige billede
				if (!rename($_document_root . $frm->values["new_image"],
					$_document_root . "/$page/$id/img/$img"))
				{
					// Kunne ikke omdøbe billede
					errorhandler("Kunne ikke flytte uploadede fil");
				}
				else
				{
					// Henter størrelse på aktuelt billede igen
					$image->clear_buffer();
					$width = $image->width($_document_root . "/$page/$id/img/$img");
					$height = $image->height($_document_root . "/$page/$id/img/$img");
					// Sletter formular
					$frm->cleanup();
					// Viser OK-besked
					$msg = new message;
					$msg->type("section");
					$msg->title("{LANG|Billede erstattet OK}");
					$msg->message("{LANG|Det uploadede billede er nu erstattet med det tidligere billede}.");
					$html .= $msg->html();
				}
			}
			
		}
		else
		{
			
			$frm->submit_text = "{LANG|Upload nyt billede}";
			$frm->tpl("td", "{LANG|Billedet skal have endelsen} $ext {LANG|og en dimension på} " .
				$image->size_formatted($_document_root . "/$page/$id/img/$img") . ".");
				
		}
				
		$html_frm .= $frm->html();	

		// Er der fejl i billedet
		if ($width == 0 or $height == 0)
		{
			
			// Kunne ikke læse info om billede
			$msg = new message;
			$msg->type("section");
			$msg->title("{LANG|Kunne ikke læse information om billede}");
			$msg->message("{LANG|Da systemet ikke kunne læse information om billedet}, " .
				"{LANG|er det ikke muligt at fortage ændringer af dette}.");
			$html .= $msg->html();
			
		}
		
		// Info om billede
		$tbl = new table;
		$tbl->th("{LANG|Rediger billede} (" . str_replace("_", " ", $id) . " - $img)", 2);
		$tbl->endrow();
		$tbl->td("{LANG|URL}:");
		$tbl->td($_site_url . "/$page/$id/img/$img");
		$tbl->endrow();
		$tbl->td("{LANG|Dimension}:");
		$tbl->td($image->size_formatted($_document_root . "/$page/$id/img/$img", "{LANG|Ukendt}"));
		$tbl->endrow();
		$tbl->td("{LANG|Billede}:");
		
		$tmp = new tpl("image_overflow");
		$tmp->set("src", $_site_url . "/$page/$id/img/$img?" . time());
		$tmp->set("width", ($width == 0 or $width > 400) ? 400 : ($width < 50 ? 50 : $width + 25));
		$tmp->set("height", ($height == 0 or $height > 300) ? 300 : ($height < 50 ? 50 : $height + 25));
		
		$tbl->td($tmp->html());
		$tbl->endrow();
		
		$html .= $tbl->html();
		
		// Er billedet OK ?
		if ($width > 0 and $height > 0)
		{
			
			// Info om billede OK, så vi giver mulighed for at foretage ændringer
			$html .= $html_frm;
			
		}
				
	}
	elseif ($do == "edit_css_simple")
	{
		//
		// Rediger CSS via Guide
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "edit", $id);
		$links->link("{LANG|Skift til avanceret redigering}", "edit_css", $id . "&file=" . $vars["file"]);
		$html .= $links->html();
		
		include($_document_root . "/Admin/pages/css_editor.php");
		
	}
	elseif ($do == "show_version_html" or $do == "show_version_js" or $do == "show_version_css")
	{
		//
		// Vis tidligere version af fil
		//
		
		$html = "";
		
		$file = $vars["file"] . "." . $vars["version"];
		
		if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}[a-z]+\.([0-9]{8}|new)$", $file) or !is_file($_document_root . "/$page/$id/$type_file/$file"))
		{
			die("Ugyldig fil ($file)");
		}
		
		// Åbner fil
		$data = "";
		if ($fp = fopen($_document_root . "/$page/$id/$type_file/$file", "r"))
		{
			while (!feof($fp)) $data .= fread($fp, 1024);
			fclose($fp);
		}
		else
		{
			errorhandler("Kunne ikke indlæse $type_title");
		}
		
		$cnv = new convert;
		
		$tmp = new tpl("textarea");
		$tmp->set("tags", "readonly");
		$tmp->set("cols", 75);
		$tmp->set("rows", 23);
		$tmp->set("width", "550px");
		$tmp->set("height", "275px");
		$tmp->set("value", $cnv->tagentities(htmlentities($data, ENT_SUBSTITUTE, "ISO-8859-1")));
		$html .= $tmp->html();
		
		$tpl = "popup";
		
	}
	elseif ($do == "edit_html" or $do == "edit_js" or $do == "edit_css")
	{
		//
		// Rediger fil
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}", "edit", $id);
		if ($do == "edit_css") $links->link("{LANG|Skift til simpel redigering}", "edit_css_simple", $id . "&file=" . $vars["file"]);
		$html .= $links->html();
		
		$file = $vars["file"];
		
		if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}[a-z]+$", $file) or !is_file($_document_root . "/$page/$id/$type_file/$file"))
		{
			header("Location: ?page=$page&do=edit&id=$id");
			exit;
		}
		
		// Åbner fil
		if ($vars["data"] == "")
		{
			$data = "";
			if ($fp = fopen($_document_root . "/$page/$id/$type_file/$file", "r"))
			{
				while (!feof($fp)) $data .= fread($fp, 1024);
				fclose($fp);
			}
			else
			{
				errorhandler("Kunne ikke indlæse $type_title");
			}
		}

		// Laver formular
		$frm = new form;
		$frm->iframe_save();
		$frm->hidden("file", $file);
		$frm->submit_text = "Gem";
		$frm->tab("{LANG|Aktuel version}");
		$frm->tpl("th", "{LANG|Rediger} $type_title (" . str_replace("_", " ", $id) . " - $file)");
		$frm->tpl("td2", "{LANG|URL}:", $_site_url . "/$page/$id/$type_file/$file");
		$frm->textarea(
			$type_data,
			"data",
			$data,
			"",
			"",
			"",
			100,
			20
			);
			
		// Lock-fil
		$lockfile = $_document_root . "/$page/$id/$type_file/$file.lock";
		if ($page == "modules")
		{
			$frm->checkbox(
				"{LANG|Beskyt mod opdateringer}",
				"lockfile",
				is_file($lockfile)
				);
		}
			
		if ($frm->done())
		{
			// Rydder html
			$html = "";
			// Gemmer data
			$data = ($frm->values["data"]);
			if ($fp = fopen($_document_root . "/$page/$id/$type_file/$file", "w"))
			{
				fwrite($fp, $data);
				fclose($fp);
				$message = "$type_title {LANG|er gemt} - " . date("d-m-Y H:i:s");
			}
			else
			{
				$message = "{LANG|Kunne ikke gemme} $type_title";
			}
			
			// Opret lock-fil?
			if ($frm->values["lockfile"] <> "")
			{
				@touch($lockfile);
			}
			elseif (is_file($lockfile))
			{
				@unlink($lockfile);
			}
							
			// Færdig
			$tmp = new tpl("form_ready");
			$tmp->set("message", $message);
			$tmp->set("form_id", $frm->form_id);
			$html .= $tmp->html();
			// Tpl
			$tpl = "iframe";
		}
		else
		{
			// Viser versioner af fil
			$fileobj = new file;
			$files = $fileobj->find_files($_document_root . "/$page/$id/$type_file/");
			rsort($files);
			
			// Er der en "ny" fil?
			if (is_file($_document_root . "/$page/$id/$type_file/$file.new"))
			{
				$frm->tab("{LANG|Opdatering}");
				$tmp = new tpl("iframe");
				$tmp->set("width", 575);
				$tmp->set("height", 300);
				$tmp->set("src", "?page=$page&do=show_version_" . $type_file . "&file=$file&id=$id&version=new");
				$frm->tpl("td", $tmp->html());
			}
			
			$count = 0;
			for ($i = 0; $i < count($files); $i++)
			{
				if (eregi("^" . str_replace(".", "\.", $file) . "\.([0-9]{8})$", $files[$i], $array))
				{
					if ($count < 5)
					{
						$version = $array[1];
						$frm->tab(substr($version, 6, 2) . "-" . substr($version, 4, 2) . "-" . substr($version, 0, 4));
						$tmp = new tpl("iframe");
						$tmp->set("width", 575);
						$tmp->set("height", 300);
						$tmp->set("src", "?page=$page&do=show_version_" . $type_file . "&file=$file&id=$id&version=$version");
						$frm->tpl("td", $tmp->html());
						$count++;
					}
					else
					{
						// Sletter versions-fil
						@unlink($_document_root . "/$page/$id/$type_file/" . $files[$i]);
					}
				}
			}
			
			// Viser formular
			$html .= $frm->html();			
		}
				
	}
	elseif ($do == "edit")
	{
		//
		// Rediger oversigt
		//
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$links->link("{LANG|Tilføj Stylesheet}", "add_css", $id);
		$links->link("{LANG|Tilføj HTML-fil}", "add_html", $id);
		$links->link("{LANG|Tilføj JavaScript-fil}", "add_js", $id);
		$links->link("{LANG|Tilføj Billede}", "add_img", $id);
		$html .= $links->html();
		
		// Laver oversigt over CSS, HTML og billeder
		
		$file = new file;
		
		$tbl = new table;
		$tbl->th("{LANG|Rediger} " . ereg_replace("s$", "", $page) . " (" . str_replace("_", " ", $id) . ")", 6);
		$tbl->endrow();
		$tbl->td("{LANG|Herunder finder du de tilknyttede filer til det aktuelle} " . ereg_replace("s$", "", $page) . ".", 6);
		$tbl->endrow();
		
		// Typer
		$array_types = array(
			"css" => "{LANG|Stylesheet}",
			"html" => "{LANG|HTML}",
			"js" => "{LANG|JavaScript}"
			);
		reset($array_types);
		while (list($ext, $title) = each($array_types))
		{
			$tbl->th($title);
			$tbl->th("{LANG|Størrelse}");
			$tbl->th("{LANG|Ændret}");
			$tbl->th("{LANG|Status}", 2);
			$tbl->th("{LANG|Valg}", 2);
			$tbl->endrow();
			$files = $file->find_files($_document_root . "/$page/$id/$ext/");
			for ($i = 0; $i < count($files); $i++)
			{
				if (ereg("\.$ext$", $files[$i]))
				{
					$tbl->td($files[$i]);
					$tbl->td($file->size_formatted($_document_root . "/$page/$id/$ext/" . $files[$i]), 1, 1, "right");
					$tbl->td(date("d-m-Y H:i", filemtime($_document_root . "/$page/$id/$ext/" . $files[$i])), 1, 1, "center");
					if (is_file($_document_root . "/$page/$id/$ext/" . $files[$i] . ".lock"))
					{
						$tbl->choise("{LANG|Filen er beskyttet mod opdateringer}", "locked_$ext&file=" . $files[$i], $id);
					}
					else
					{
						$tbl->choise("{LANG|Klik for at beskytte filen mod opdateringer}", "unlocked_$ext&file=" . $files[$i], $id);
					}
					if (is_file($_document_root . "/$page/$id/$ext/" . $files[$i] . ".new"))
					{
						$tbl->td("<img src=\"/img/icon_new.gif\" alt=\"Opdatering tilgængelig\">", 1, 1, "center");
					}
					else
					{
						$tbl->td("&nbsp;");
					}
					$tbl->choise("{LANG|Ret}", "edit_$ext&file=" . $files[$i], $id);
					$tbl->choise("{LANG|Slet}", "delete_$ext&file=" . $files[$i], $id, "{LANG|Er du sikker på du vil slette denne fil}?");
					$tbl->endrow();
				}
			}
			if (count($files) == 0)
			{
				$tbl->td("{LANG|Ingen}...", 8);
				$tbl->endrow();
			}
		}
		
		// Billeder
		$image = new image;
		$tbl->th("{LANG|Billeder}");
		$tbl->th("{LANG|Størrelse}");
		$tbl->th("{LANG|Ændret}");
		$tbl->th("{LANG|Valg}", 2);
		$tbl->endrow();
		$files = $file->find_files($_document_root . "/$page/$id/img/");
		for ($i = 0; $i < count($files); $i++)
		{
			$tbl->td($files[$i]);
			$tbl->td($file->size_formatted($_document_root . "/$page/$id/img/" . $files[$i]), 1, 1, "right");
			$tbl->td(date("d-m-Y H:i", filemtime($_document_root . "/$page/$id/img/" . $files[$i])), 1, 1, "center");
			$tbl->choise("{LANG|Ret}", "edit_img&file=" . $files[$i], $id);
			$tbl->choise("{LANG|Slet}", "delete_img&file=" . $files[$i], $id, "{LANG|Er du sikker på du vil slette dette billede}?");
			$tbl->endrow();
		}
		if (count($files) == 0)
		{
			$tbl->td("{LANG|Ingen}...", 6);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
	}
	elseif (ereg("^unlocked_", $do))
	{
		// Beskyt fil mod opdateringer
		$file = $vars["file"];
		if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}[a-z]+$", $file) or !is_file($_document_root . "/$page/$id/$type_file/$file"))
		{
			header("Location: ?page=$page&do=edit&id=$id");
			exit;
		}
		if ($fp = fopen($_document_root . "/$page/$id/$type_file/$file.lock", "w")) fclose($fp);
		header("Location: ./?page=$page&do=edit&id=$id");
		exit;
	}
	elseif (ereg("^locked_", $do))
	{
		// Fjern beskyttelse af fil
		$file = $vars["file"];
		if (!ereg("^[a-zA-Z0-9_-]+[\.]{1}[a-z]+$", $file) or !is_file($_document_root . "/$page/$id/$type_file/$file"))
		{
			header("Location: ?page=$page&do=edit&id=$id");
			exit;
		}
		@unlink($_document_root . "/$page/$id/$type_file/$file.lock");
		header("Location: ./?page=$page&do=edit&id=$id");
		exit;
	}
	
	if (($do == "add_html" or $do == "edit_html") and $tpl == "default")
	{
		include($_document_root . "/Admin/pages/elements.php");
		/*
		// Viser iframe med elementer
		$tmp = new tpl("iframe");
		$tmp->set("width", "100%");
		$tmp->set("height", "500");
		$tmp->set("src", "./?page=elements");
		$html .= $tmp->html();
		*/
	}
?>
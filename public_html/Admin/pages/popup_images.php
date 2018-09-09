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
		Version:		04-12-2006
		Beskrivelse:	Viser popup med billeder
	*/

	$frm = new form;
	$frm->hidden("return", $vars["return"]);
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
			if (is_file("' . $_document_root . '/layouts/' . $_settings_["SITE_LAYOUT"] . '/img/" . $this->values["new_filename"] . "." . $this->values["new_image_realext"]))
			{
				$error = "{LANG|Der findes allerede en fil med samme navn} !";
			}
		'
		);
		
	if ($frm->done())
	{
		// Gemmer billede
		if (!rename($_document_root . $frm->values["new_image"],
			$_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/img/" . $frm->values["new_filename"] . "." . $frm->values["new_image_realext"]))
		{
			// Kunne ikke omdøbe billede
			errorhandler("Kunne ikke flytte uploadede fil");
		}
		else
		{
			// Sletter formular
			$frm->cleanup();
			// Videre
		}
	}
	
	$form = $frm->html();
	
	// Henter billeder
	$file = new file;
	$files = $file->find_files($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/img/");
	$elements = "";
	for ($i = 0; $i < count($files); $i++)
	{
		$tmp = new tpl("admin_popup_images_element");
		$tmp->set("image", "/layouts/" . $_settings_["SITE_LAYOUT"] . "/img/" . $files[$i]);
		$elements .= $tmp->html();
	}
	
	$tmp = new tpl("admin_popup_images");
	$tmp->set("elements", $elements);
	$tmp->set("return", stripslashes($vars["return"]));
	$tmp->set("return_entities", htmlentities(stripslashes($vars["return"])));
	$tmp->set("value", $vars["value"]);
	$tmp->set("form", $form);
	$html .= $tmp->html();
	
	// Template
	$tpl = "iframe";
?>
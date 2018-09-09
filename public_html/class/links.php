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
		Beskrivelse:	Laver linkknapper baseret på template
		01-04-2007:		Understøtter nu ny link stil, f.eks. /site/da/WYSIWYG/show/1
	*/
	
	class links
	{
		// Array med links
		var $array_links = array();
		// Template, der skal anvendes
		var $tpl = "";
		
		// Init
		function links($tpl = "")
		{
			$this->tpl = $tpl;
		}
		
		// Tilføjer link
		function link($text, $do = "", $id = "", $confirm = "")
		{
			$i = count($this->array_links);
			$this->array_links[$i]["text"] = $text;
			$this->array_links[$i]["do"] = $do;
			$this->array_links[$i]["id"] = $id;
			$this->array_links[$i]["confirm"] = $confirm;
		}
		
		// Viser links
		function html()
		{
			global $module, $page, $do, $id, $_lang_id, $_SERVER;
			
			// Admin?
			$is_admin = ereg("/Admin/", $_SERVER["REQUEST_URI"]);
				
			$html = "";
			$tmp = new tpl("_links_" . $this->tpl . "_header");
			$html .= $tmp->html();
			for ($i = 0; $i < count($this->array_links); $i++)
			{
				$link = $this->array_links[$i];
				if ($do == $link["do"] and ($id == $link["id"] or $link["id"] == ""))
				{
					$tmp = new tpl("_links_" . $this->tpl . "_active");
				}
				else
				{
					$tmp = new tpl("_links_" . $this->tpl . "_inactive");
				}
				if ((substr($link["do"], 0, 1) == "/" or substr($link["do"], 0, 1) == "?") and $link["id"] == "")
				{
					$tmp->set("link", $link["do"]);
				}
				elseif ($is_admin)
				{
					$tmp->set("link", "?module=$module&page=$page&do=" . $link["do"] . "&id=" . $link["id"]);
				}
				else
				{
					$tmp->set("link", "/site/$_lang_id/$module/$page/" . $link["do"] . "/" . $link["id"]);
				}
				$tmp->set("text", $link["text"]);
				$tmp->set("confirm", $link["confirm"]);
				$html .= $tmp->html();
			}
			$tmp = new tpl("_links_" . $this->tpl . "_footer");
			$html .= $tmp->html();
			return $html;
		}
	}
?>
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
		Version:		14-10-2005
		Beskrivelse:	Realtime status via JavaScript, opdatering af body
	*/
	
	class js_realtime
	{
		var $pause_after_update = 0;
		
		function js_realtime()
		{
			// Tømmer og stopper output-buffer
			ob_end_clean();
			// Henter start-HTML
			$tmp = new tpl("_js_realtime_header");
			$tmp->view();
			flush();
		}
		
		function update($html)
		{
			// Behandler HTML, så det kan vises via JavaScript
			$html = str_replace("\r", "", $html);
			$html = str_replace("\n", "\\n", $html);
			$html = str_replace("'", "\'", $html);
			$html = str_replace("script", "scr'+'ipt", $html);
			// Henter opdaterings-HTML
			$tmp = new tpl("_js_realtime_update");
			$tmp->set("html", $html);
			$tmp->view();
			flush();
			// Pause
			$this->pause($this->pause_after_update);
		}
		
		function pause($secs = 0)
		{
			// Pauser script
			if ($secs <= 0) return;
			usleep($secs * 1000000);
		}
		
		function end()
		{
			// Henter slut-HTML
			$tmp = new tpl("_js_realtime_footer");
			$tmp->view();
			flush();
		}
		
		function pause_after_update($pause_after_update)
		{
			$this->pause_after_update = $pause_after_update;
		}
	}
?>
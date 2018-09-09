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
		Beskrivelse:	Konverterings-funktioner
	*/
	
	class convert
	{
		// Beregner alder ud fra fødselsdato
		function calc_age($birthday)
		{
			$ts = strtotime($birthday);
			$age = date("Y") - date("Y", $ts);
			if (date("m-d") < date("m-d", $ts)) $age--;
			return $age;
		}
		
		// Laver UK-datoformat YYYY-MM-DD ud fra DK-datoformat DD-MM-YYYY
		function date_dk2uk($date)
		{
			if (ereg("^([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})( [0-9]{1,2}.[0-9]{1,2}){0,1}$", $date, $array))
			{
				if ($array[4] <> "")
				{
					return $array[3] . "-" . $array[2] . "-" . $array[1] . str_replace(".", ":", $array[4]);
				}
				else
				{
					return $array[3] . "-" . $array[2] . "-" . $array[1];
				}
			}
			else
			{
				return $date;
			}
		}

		// Laver DK-datoformat DD-MM-YYYY ud fra UK-datoformat YYYY-MM-DD
		function date_uk2dk($date)
		{
			if (ereg("^([0-9]{2,4}).([0-9]{1,2}).([0-9]{1,2})( [0-9]{1,2}.[0-9]{1,2}){0,1}$", $date, $array))
			{
				if ($array[4] <> "")
				{
					return $array[3] . "-" . $array[2] . "-" . $array[1] . str_replace(".", ":", $array[4]);
				}
				else
				{
					return $array[3] . "-" . $array[2] . "-" . $array[1];
				}
			}
			else
			{
				return $date;
			}
		}

		// Formattering af tekst, så det kan bruges i XML-tags		
		function xmlentities($string, $quote_style=ENT_QUOTES)
		{
			static $trans;
			if (!isset($trans)) {
				$trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
				foreach ($trans as $key => $value)
					$trans[$key] = '&#'.ord($key).';';
				// dont translate the '&' in case it is part of &xxx;
				$trans[chr(38)] = '&';
			}
			$string = str_replace("&", "&#38;", $string);
			// after the initial translation, _do_ map standalone '&' into '&#38;'
			return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/","&#38;" , strtr($string, $trans));
		}
		
		// Erstatter { og } med &#000; istedet
		function tagentities($string)
		{
			return str_replace("{", "&#123;",
				str_replace("}", "&#125;",
					$string
					));
		}
		
		// Formaterer pris
		function price($price, $digits = 2)
		{
			return number_format($price, $digits, ",", ".");
		}
		
		// Behandler indtastet tal fra bruger
		function number($number, $digits = 2)
		{
			return number_format(str_replace(",", ".", $number), $digits, ".", "");
		}
		
		// Formaterer dato
		function formatdate($ts)
		{
			if ($ts <> "")
			{
				// Finder format
				if (date("d-m-Y") == date("d-m-Y", $ts))
				{
					// I dag
					$format = cms_setting("convert_date_today");
					if ($format == "" or !$format) $format = "%H:%M";
				}
				elseif (date("Y") == date("Y", $ts))
				{
					// I år
					$format = cms_setting("convert_date_year");
					if ($format == "" or !$format) $format = "%e. %b. %H:%M";
				}
				else
				{
					// Standard
					$format = cms_setting("convert_date_default");
					if ($format == "" or !$format) $format = "%d-%m-%Y %H:%M";
				}
				
				// Beregner timestamp
				if (!is_numeric($ts)) $ts = strtotime($ts);
				
				// Formatterer
				$date = strftime($format, $ts);
			}
			else
			{
				$date = "-";
			}
			return $date;
		}
	}
?>
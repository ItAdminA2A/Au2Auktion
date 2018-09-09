<?php
	/*COPYRIGHT*\
		COPYRIGHT STADEL.DK 2006
		
		AL KODE I DENNE FIL TILH�RER STADEL.DK, THOMAS@STADEL.DK.
		KODEN M� UNDER INGEN  OMST�NDIGHEDER  BENYTTES  TIL ANDET
		FORM�L END  DET DEN ER K�B TIL.  KODEN M� IKKE  �NDRES AF
		ANDRE   END   STADEL.DK.   KODEN  M�  IKKE  S�LGES  ELLER
		VIDEREDISTRIBUERES  HELT, DELVIS ELLER SOM EN KOPI AF DET
		SYSTEM   DET  OPRINDELIGT  ER  K�BT  SAMMEN  MED.  ENHVER
		OVERTR�DELSE  AF EN ELLER FLERE AF DE N�VNTE  BETINGELSER
		VIL RESULTERE I RETSFORF�LGELSE OG ERSTATNING FOR BRUD P�
		OPHAVSRETTEN AF KODEN, IFLG.  DANSK  OPHAVSRETSLOV. DENNE
		COPYRIGHT    MEDDELELSE    M�    DESUDEN    UNDER   INGEN
		OMST�NDIGHEDER FJERNES FRA DENNE FIL.
	
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
		Version:		17-03-2005
		Beskrivelse:	Indstillinger
	*/
	
	$_settings_ = array();
	
	class settings
	{
		function settings()
		{
			global $_settings_, $_table_prefix;
			
			if (!isset($_settings_)) $_settings_ = array();
				
			// Database
			$db = new db;
			
			// Henter indstillinger
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_settings_
				");
			while ($db->fetch_array())
			{
				if (!isset($_settings_[$db->array["id"]])) $_settings_[$db->array["id"]] = stripslashes($db->array["value"]);
			}
		}
		
		function get($key)
		{
			global $_settings_;
			
			return $_settings_[$key];
		}
		
		function delete($key)
		{
			global $_table_prefix;
			
			// Database
			$db = new db;
			$db->disable_log(true);
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_settings_
				WHERE
					id = '" . $db->escape($key) . "'
				");
		}
		
		function set($key, $value)
		{
			global $_settings_, $_table_prefix;
			
			// Database
			$db = new db;
			$db->disable_log(true);
			
			if (!$db->execute_field("
				SELECT
					id
				FROM
					" . $_table_prefix . "_settings_
				WHERE
					id = '" . $db->escape($key) . "'
				"))
			{
				// Tilf�jer indstilling
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_settings_
					(
						id
					)
					VALUES
					(
						'" . $db->escape($key) . "'
					)
					");
			}
			
			// Gemmer indstilling
			$db->execute("
				UPDATE
					" . $_table_prefix . "_settings_
				SET
					value = '" . $db->escape($value) . "'
				WHERE
					id = '" . $db->escape($key) . "'
				");
			
			$_settings_[$key] = $value;
		}
	}
?>
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
		Version:		17-05-2006
		Beskrivelse:	Laver links til sider etc.
	*/
	
	$tmp_elements = "";
	
	// Henter sprog
	$array_langs = array();
	$select_langs = array(array("", "-"));
	$db->execute("
		SELECT
			*
		FROM
			" . $_table_prefix . "_languages_
		ORDER BY
			`default` DESC,
			title
		");
	while ($db->fetch_array())
	{
		$array_langs[$db->array["id"]] = stripslashes($db->array["title"]);
		$select_langs[count($select_langs)] = array($db->array["id"], stripslashes($db->array["title"]));
	}
	
	// Henter sider
	$pages = pages_array(true);

	// Gennemløber sider	
	for ($i = 0; $i < count($pages); $i++)
	{
		$tmp = new tpl("admin_js_links_element");
		$tmp->set("title", 
			str_replace("'", "",
			str_replace("\"", "",
				"Sider - " .
				($array_langs[$pages[$i]["lang_id"]] <> "" ? $array_langs[$pages[$i]["lang_id"]] : $pages[$i]["lang_id"])
				. " - " . $pages[$i]["title"]
				))
			);
		$tmp->set("link", "id=" . $pages[$i]["id"]);
		$tmp_elements .= $tmp->html();
	}	
	
	// Henter elementer
	$elements = module_elements();
	
	// Laver select-array med elementer
	$select_elements = array();
	reset($elements);
	while (list($tmp_module, $tmp_array) = each($elements))
	{
		reset($tmp_array);
		while (list($key, $value) = each($tmp_array))
		{
			$tmp = new tpl("admin_js_links_element");
			$tmp->set("title", str_replace("'", "", str_replace("\"", "", $tmp_module . " - " . $key)));
			list($tmp_page, $tmp_do, $tmp_id) = split("[\|]", $value);
			$tmp->set("link", "module=$tmp_module&page=$tmp_page&do=$tmp_do&id=$tmp_id");
			$tmp_elements .= $tmp->html();
		}
	}
	
	// Viser links
	$tmp = new tpl("admin_js_links");
	$tmp->set("elements", $tmp_elements);
	$html .= $tmp->html();
	
	// Template
	$tpl = "empty";
?>
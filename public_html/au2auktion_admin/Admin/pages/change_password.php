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
		Version:		10-11-2005
		Beskrivelse:	Skift password
	*/

	// Overskrift
	$msg = new message;
	$msg->title("{LANG|Skift password}");
	$msg->message("{LANG|Herunder kan du �ndre dit password til administrationen}.");
	$html .= $msg->html();
	
	// Formular
	$frm = new form;
	$frm->tpl("th", "{LANG|Skift password}");
	$frm->password(
		"{LANG|V�lg nyt password}",
		"password",
		"",
		"^.+$",
		"{LANG|Skal udfyldes}"
		);
	$frm->password(
		"{LANG|Gentag password}",
		"password_confirm",
		"",
		"",
		"",
		'
			if ($this->values["password"] <> $this->values["password_confirm"])
			{
				$error = "{LANG|De to passwords stemmer ikke overens} !";
			}
		'
		);
		
	if ($frm->done())
	{
		// Opdaterer brugerens password
		$usr->change_password($frm->values["password"]);
		// OK-besked
		$msg = new message;
		$msg->type("section");
		$msg->title("{LANG|Dit password er nu �ndret}");
		$msg->message("{LANG|Dit password er nu �ndret og er g�ldende fra n�ste gang du logger ind}.");
		$html .= $msg->html();
	}
	else
	{
		// Viser formular
		$html .= $frm->html();
	}
?>
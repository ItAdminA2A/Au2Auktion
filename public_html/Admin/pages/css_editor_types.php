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
		Version:		30-11-2006
		Beskrivelse:	Fortolkninger af CSS elementer
	*/

	$array_groups = array(
		"body" 		=> "{LANG|Dokument}",
		"p" 		=> "{LANG|Paragraf}",
		"span" 		=> "{LANG|Afsnit} ({LANG|span})",
		"div"		=> "{LANG|Lag} ({LANG|div})",
		"ul"		=> "{LANG|Punktopstilling} (ul)",
		"li"		=> "{LANG|Punktopstilling} (li)",
		"il"		=> "{LANG|Punktopstilling tal} (il)",
		"table"		=> "{LANG|Tabel}",
		"tr"		=> "{LANG|Tabel række}",
		"th"		=> "{LANG|Tabel overskrift}",
		"td"		=> "{LANG|Tabel celle}",
		"img"		=> "{LANG|Billede}",
		"a"			=> "{LANG|Link}",
		"a:hover"	=> "{LANG|Link mus over}",
		"form"		=> "{LANG|Formular}",
		"input"		=> "{LANG|Formular tekst felt}",
		"textarea"	=> "{LANG|Formular notat felt}",
		"select"	=> "{LANG|Formular liste}",
		"h1"		=> "{LANG|Overskrift} 1",
		"h2"		=> "{LANG|Overskrift} 2",
		"h3"		=> "{LANG|Overskrift} 3",
		"h4"		=> "{LANG|Overskrift} 4",
		"h5"		=> "{LANG|Overskrift} 5",
		"h6"		=> "{LANG|Overskrift} 6",
		"b"			=> "{LANG|Tekst fed}",
		"i"			=> "{LANG|Tekst kursiv}",
		"u"			=> "{LANG|Tekst understreget}",
		"small"		=> "{LANG|Tekst lille}",
		"center"	=> "{LANG|Centrering}"
		);
		
	$array_elements = array(
		"background-color"		=> "{LANG|Baggrund farve}", 
		"background-image"		=> "{LANG|Baggrund billede}", 
		"background-position"	=> "{LANG|Baggrund billede position}",
		"background-repeat"		=> "{LANG|Baggrund billede gentagelse}",
		"background-attachment" => "{LANG|Baggrund billede vedhæftning}",
		"color"					=> "{LANG|Font farve}",
		"font-family"			=> "{LANG|Font type}",
		"font-size"				=> "{LANG|Font størrelse}",
		"font-style"			=> "{LANG|Font stil}",
		"font-weight"			=> "{LANG|Font fed}",
		"font-variant"			=> "{LANG|Font variant}",
		"text-decoration"		=> "{LANG|Tekst dekoration}",
		"text-align"			=> "{LANG|Tekst justering}",
		"letter-spacing"		=> "{LANG|Tekst bogstav mellemrum}",
		"text-transform"		=> "{LANG|Tekst transformering}",
		"text-indent"			=> "{LANG|Tekst indrykning}",
		"white-space"			=> "{LANG|Font blanktegn}",
		"margin-top"			=> "{LANG|Margen top}",
		"margin-left"			=> "{LANG|Margen venstre}",
		"margin-right"			=> "{LANG|Margen højre}",
		"margin-bottom"			=> "{LANG|Margen bund}",
		"padding-top"			=> "{LANG|Indre margen top}",
		"padding-left"			=> "{LANG|Indre margen venstre}",
		"padding-right"			=> "{LANG|Indre margen højre}",
		"padding-bottom"		=> "{LANG|Indre margen bund}",
		"border-top"			=> "{LANG|Ramme top}",
		"border-left"			=> "{LANG|Ramme venstre}",
		"border-right"			=> "{LANG|Ramme højre}",
		"border-bottom"			=> "{LANG|Ramme bund}",
		"width"					=> "{LANG|Bredde}",
		"height"				=> "{LANG|Højde}",
		"overflow-x"			=> "{LANG|Overflod vandret}",
		"overflow-y"			=> "{LANG|Overflod lodret}",
		"float"					=> "{LANG|Position}",
		"display"				=> "{LANG|Visning}"
		);
		
	$array_values = array(
		"background-color"		=> array("color"),
		"background-image"		=> array("image"),
		"background-position"	=> array(array("top", "center", "bottom"), array("left", "center", "right")),
		"background-repeat"		=> array(array("repeat", "repeat-x", "repeat-y", "no-repeat")),
		"background-attachment" => array(array("scroll", "fixed")),
		"color"					=> array("color"),
		"font-family"			=> array(array("Arial", "Courier New", "Tahoma", "Times New Roman", "Verdana")),
		"font-size"				=> array("pixel_point"),
		"font-style"			=> array(array("italic")),
		"font-weight"			=> array(array("normal", "bold")),
		"font-variant"			=> array(array("normal", "small-caps")),
		"text-decoration"		=> array(array("underline", "overline", "line-through", "none")),
		"text-align"			=> array(array("left", "center", "right", "justify")),
		"letter-spacing"		=> array("pixel"),
		"text-transform"		=> array(array("capitalize", "uppercase", "lowercase")),
		"text-indent"			=> array("pixel"),
		"white-space"			=> array(array("nowrap")),
		"margin-top"			=> array("pixel"),
		"margin-left"			=> array("pixel"),
		"margin-right"			=> array("pixel"),
		"margin-bottom"			=> array("pixel"),
		"padding-top"			=> array("pixel"),
		"padding-left"			=> array("pixel"),
		"padding-right"			=> array("pixel"),
		"padding-bottom"		=> array("pixel"),
		"border-top"			=> array("pixel", array("dotted", "dashed", "solid", "double", "groove", "ridge", "inset", "outset"), "color"),
		"border-left"			=> array("pixel", array("dotted", "dashed", "solid", "double", "groove", "ridge", "inset", "outset"), "color"),
		"border-right"			=> array("pixel", array("dotted", "dashed", "solid", "double", "groove", "ridge", "inset", "outset"), "color"),
		"border-bottom"			=> array("pixel", array("dotted", "dashed", "solid", "double", "groove", "ridge", "inset", "outset"), "color"),
		"width"					=> array("pixel_procent"),
		"height"				=> array("pixel_procent"),
		"overflow-x"			=> array(array("hidden", "auto", "scroll")),
		"overflow-y"			=> array(array("hidden", "auto", "scroll")),
		"float"					=> array(array("left", "right", "none")),
		"display"				=> array(array("none", "block", "inline"))
		);
		
	$array_texts = array(
		"block"			=> "{LANG|Blok}",
		"inline"		=> "{LANG|Normal}",
		"top"			=> "{LANG|Top}",
		"center"		=> "{LANG|Center}",
		"bottom"		=> "{LANG|Bund}",
		"left"			=> "{LANG|Venstre}",
		"right"			=> "{LANG|Højre}",
		"repeat"		=> "{LANG|Gentag}",
		"repeat-x"		=> "{LANG|Gentag vandret}",
		"repeat-y"		=> "{LANG|Gentag lodret}",
		"no-repeat" 	=> "{LANG|Ingen gentagelse}",
		"scroll"		=> "{LANG|Scroll}",
		"fixed"			=> "{LANG|Fastlåst}",
		"italic"		=> "{LANG|Kursiv}",
		"normal"		=> "{LANG|Normal}",
		"bold"			=> "{LANG|Fed}",
		"small-caps"	=> "{LANG|Vis små bogstaver som store}",
		"underline"		=> "{LANG|Understreg}",
		"overline"		=> "{LANG|Streg over}",
		"line-through"	=> "{LANG|Gennemstreget}",
		"justify"		=> "{LANG|Lige margener}",
		"capitalize"	=> "{LANG|Første bogstav i ord med stort}",
		"uppercase"		=> "{LANG|Alle bogstaver med stort}",
		"lowercase"		=> "{LANG|Alle bogstaver med småt}",
		"nowrap"		=> "{LANG|Ingen tekst-ombrydning}",
		"dotted"		=> "{LANG|Prikket}",
		"dashed"		=> "{LANG|Streget}",
		"solid"			=> "{LANG|Fuld streg}",
		"double"		=> "{LANG|Dobbelt}",
		"groove"		=> "{LANG|Fordybning}",
		"ridge"			=> "{LANG|Forhøjning}",
		"inset"			=> "{LANG|Inset}",
		"outset"		=> "{LANG|Outset}",
		"hidden"		=> "{LANG|Skjul}",
		"auto"			=> "{LANG|Automatisk}",
		"Arial"			=> "Arial",
		"Courier New"	=> "Courier New",
		"Tahoma"		=> "Tahoma",
		"Times New Roman" => "Times New Roman",
		"Verdana"		=> "Verdana",
		"none"			=> "{LANG|Ingen / skjul}"
		);
?>
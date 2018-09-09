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
		Beskrivelse:	Domæner
	*/
	
	if ($do == "add" or $do == "edit")
	{
		//
		// Tilføj eller ret domæne
		//
		
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Domæner} - " . ($do == "add" ? "{LANG|Tilføj}" : "{LANG|Ret}"));
		$html .= $msg->html();
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		if ($do == "edit")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_domains_
				WHERE
					id = '$id'
				");
			if (!$res = $db->fetch_array())
			{
				header("Location: ?page=$page");
				exit;
			}
		}
		else
		{
			$lang = false;
		}
		
		// Select med layouts
		$select_layouts = array(array("", "-"));
		$file = new file;
		$layouts = $file->find_folders($_document_root . "/layouts/");
		for ($i = 0; $i < count($layouts); $i++)
		{
			$select_layouts[count($select_layouts)] = array($layouts[$i], $layouts[$i]);
		}
		
		// Select med sprog
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
			$select_langs[count($select_langs)] = array($db->array["id"], stripslashes($db->array["title"]));
		}
		
		
		// Formular
		$frm = new form;
		$frm->tab($do == "add" ? "{LANG|Tilføj domæne}" : "{LANG|Ret domæne}");
		if ($do == "add")
		{
			$frm->input(
				"{LANG|Domæne}",
				"domain",
				"",
				"^([a-z0-9*-]+\.){1,}[a-z*]+$",
				"{LANG|Ugyldigt domænenavn}, {LANG|må kun bestå af} a-z, 0-9, - {LANG|samt} *",
				'
					$db = new db;
					if ($db->execute_field("
						SELECT
							id
						FROM
							' . $_table_prefix . '_domains_
						WHERE
							domain = \'" . $this->values["domain"] . "\'
						") > 0)
					{
						$error = "{LANG|Det angivne domæne er allerede oprettet}";
					}
				'
				);
		}
		else
		{
			$frm->tpl("td2", "{LANG|Domæne}:", str_replace("%", "*", $res["domain"]));
		}
		$frm->input(
			"{LANG|Viderestil til} ({<!>URI} = {LANG|Forespurgt URL uden} http://domain.dk)",
			"redirect",
			$res["redirect"],
			"^((http|https|ftp):\/\/.*){0,1}$",
			"Skal starte med http, https eller ftp"
			);
		$frm->select(
			"{LANG|Layout for domæne}",
			"layout",
			$res["layout"],
			"",
			"",
			"",
			$select_layouts
			);
		$frm->select(
			"{LANG|Sprog for domæne}",
			"lang_id",
			$res["lang_id"],
			"",
			"",
			"",
			$select_langs
			);
			
		$frm->tab("{LANG|Side}");
		$frm->input(
			"{LANG|Titel på siden}",
			"site_title",
			stripslashes($res["site_title"])
			);
		$frm->textarea(
			"{LANG|Beskrivelse af siden}",
			"site_description",
			stripslashes($res["site_description"])
			);
		$frm->textarea(
			"{LANG|Søgeord - et per linie}",
			"site_keywords",
			str_replace(",", "\r\n", stripslashes($res["site_keywords"]))
			);
			
		$frm->tab("{LANG|E-mail}");
		$frm->input(
			"{LANG|Standard afsender navn}",
			"site_email_name",
			stripslashes($res["site_email_name"])
			);
		$frm->input(
			"{LANG|Standard afsender e-mail}",
			"site_email",
			stripslashes($res["site_email"])
			);
		$frm->select(
			"{LANG|Vedhæft billeder}",
			"email_attach_images",
			$res["email_attach_images"],
			"",
			"",
			"",
			array(
				array("", ""),
				array("0", "{LANG|Nej}"),
				array("1", "{LANG|Ja}")
				)
			);
			
		$frm->select(
			"{LANG|Send e-mail via}",
			"email_method",
			$res["email_method"],
			"",
			"",
			"",
			array(
				array("", ""),
				array("php",	"PHP mail()"),
				array("smtp",	"SMTP server")
				)
			);
		$frm->input(
			"{LANG|SMTP server}",
			"email_smtp_host",
			stripslashes($res["email_smtp_host"]),
			"",
			"",
			'
				if ($this->values["email_method"] == "smtp" and $this->values["email_smtp_host"] == "")
				{
					$error = "{LANG|Skal udfyldes}";
				}
			'
			);
		$frm->input(
			"{LANG|SMTP port}",
			"email_smtp_port",
			stripslashes($res["email_smtp_port"]),
			"^[0-9]*",
			"{LANG|Må kun bestå af tal}",
			'
				if ($this->values["email_method"] == "smtp" and $this->values["email_smtp_port"] == "")
				{
					$error = "{LANG|Skal udfyldes}";
				}
			'
			);
		$frm->input(
			"{LANG|SMTP brugernavn}",
			"email_smtp_user",
			$res["email_smtp_user"]
			);
		$frm->input(
			"{LANG|SMTP password}",
			"email_smtp_pass",
			"*****"
			);
			
		$frm->tpl("th", "{LANG|Retur-mail}");
		$frm->tpl("td", "{LANG|Disse indstillinger kræver at der er oprettet en POP3-konto til retur-mail},<br>{LANG|samt at der benyttes SMTP-server til afsendelse af mails}");
		$frm->input(
			"{LANG|Retur e-mail}",
			"return_email",
			stripslashes($res["return_email"])
			);
		$frm->input(
			"{LANG|POP3-server}",
			"return_email_server",
			stripslashes($res["return_email_server"])
			);
		$frm->input(
			"{LANG|POP3-brugernavn}",
			"return_email_user",
			stripslashes($res["return_email_user"])
			);
		$frm->input(
			"{LANG|POP3-password}",
			"return_email_pass",
			"*****"
			);
			
		$frm->tab("{LANG|Bruger-specifikke variabler}");
		$frm->tpl("td", "{LANG|Angives f.eks.}<br>" .
			"&nbsp;&nbsp;&nbsp;{LANG|TEKST1=Min første variable tekst}<br>" .
			"&nbsp;&nbsp;&nbsp;{LANG|TEKST2=Min anden variable tekst}<br>" .
			"{LANG|Der angives således en variabel pr. linie}<br>" .
			"{LANG|Alle variabler prefixes med USER, dvs.}<br>" .
			"&nbsp;&nbsp;&nbsp;TEKST1 {LANG|indsættes i HTML som} {<!>USER_TEKST1}<br>" .
			"&nbsp;&nbsp;&nbsp;TEKST2 {LANG|indsættes i HTML som} {<!>USER_TEKST2}");
		$frm->textarea(
			"{LANG|Bruger-variabler}",
			"user_settings",
			stripslashes($res["user_settings"])
			);
			
		if ($frm->done())
		{
			if ($frm->values["return_email_pass"] == "*****") $frm->values["return_email_pass"] = $res["return_email_pass"];
			if ($frm->values["email_smtp_pass"] == "*****") $frm->values["email_smtp_pass"] = $res["email_smtp_pass"];
			
			if ($do == "add")
			{
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_domains_
					(
						domain,
						layout,
						lang_id,
						site_title,
						site_description,
						site_keywords,
						site_email_name,
						site_email,
						user_settings,
						redirect,
						email_attach_images,
						email_method,
						email_smtp_host,
						email_smtp_port,
						email_smtp_user,
						email_smtp_pass,
						return_email,
						return_email_server,
						return_email_user,
						return_email_pass
					)
					VALUES
					(
						'" . $db->escape(str_replace("*", "%", $frm->values["domain"])) . "',
						'" . $db->escape($frm->values["layout"]) . "',
						'" . $db->escape($frm->values["lang_id"]) . "',
						'" . $db->escape($frm->values["site_title"]) . "',
						'" . $db->escape($frm->values["site_description"]) . "',
						'" . $db->escape($frm->values["site_keywords"]) . "',
						'" . $db->escape($frm->values["site_email_name"]) . "',
						'" . $db->escape($frm->values["site_email"]) . "',
						'" . $db->escape($frm->values["user_settings"]) . "',
						'" . $db->escape($frm->values["redirect"]) . "',
						'" . $db->escape($frm->values["email_attach_images"]) . "',
						'" . $db->escape($frm->values["email_method"]) . "',
						'" . $db->escape($frm->values["email_smtp_host"]) . "',
						'" . $db->escape($frm->values["email_smtp_port"]) . "',
						'" . $db->escape($frm->values["email_smtp_user"]) . "',
						'" . $db->escape($frm->values["email_smtp_pass"]) . "',
						'" . $db->escape($frm->values["return_email"]) . "',
						'" . $db->escape($frm->values["return_email_server"]) . "',
						'" . $db->escape($frm->values["return_email_user"]) . "',
						'" . $db->escape($frm->values["return_email_pass"]) . "'
					)
					");
			}
			else
			{
				$db->execute("
					UPDATE
						" . $_table_prefix . "_domains_
					SET
						layout = '" . $db->escape($frm->values["layout"]) . "',
						lang_id = '" . $db->escape($frm->values["lang_id"]) . "',
						site_title = '" . $db->escape($frm->values["site_title"]) . "',
						site_description = '" . $db->escape($frm->values["site_description"]) . "',
						site_keywords = '" . $db->escape($frm->values["site_keywords"]) . "',
						site_email_name = '" . $db->escape($frm->values["site_email_name"]) . "',
						site_email = '" . $db->escape($frm->values["site_email"]) . "',
						user_settings = '" . $db->escape($frm->values["user_settings"]) . "',
						redirect = '" . $db->escape($frm->values["redirect"]) . "',
						email_attach_images = '" . $db->escape($frm->values["email_attach_images"]) . "',
						email_method = '" . $db->escape($frm->values["email_method"]) . "',
						email_smtp_host = '" . $db->escape($frm->values["email_smtp_host"]) . "',
						email_smtp_port = '" . $db->escape($frm->values["email_smtp_port"]) . "',
						email_smtp_user = '" . $db->escape($frm->values["email_smtp_user"]) . "',
						email_smtp_pass = '" . $db->escape($frm->values["email_smtp_pass"]) . "',
						return_email = '" . $db->escape($frm->values["return_email"]) . "',
						return_email_server = '" . $db->escape($frm->values["return_email_server"]) . "',
						return_email_user = '" . $db->escape($frm->values["return_email_user"]) . "',
						return_email_pass = '" . $db->escape($frm->values["return_email_pass"]) . "'
					WHERE
						id = '$id'
					");
			}
			header("Location: ?page=$page");
			exit;
		}

		// Viser formular
		$html .= $frm->html();			
		
	}
	elseif ($do == "delete")
	{
		//
		// Slet sprog
		//
	
		$db->execute("
			DELETE FROM
				" . $_table_prefix . "_domains_
			WHERE
				id = '$id'
			");
			
		header("Location: ?page=$page");
		exit;
			
	}
	elseif ($do == "default")
	{
		//
		// Standard domæne
		//
	
		$db->execute("
			UPDATE
				" . $_table_prefix . "_domains_
			SET
				`default` = '0'
			");
			
		$db->execute("
			UPDATE
				" . $_table_prefix . "_domains_
			SET
				`default` = '1'
			WHERE
				id = '$id'
			");
			
		header("Location: ?page=$page");
		exit;
			
	}
	else
	{
		//
		// Oversigt
		//
	
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Domæner}");
		$html .= $msg->html();
		
		// Links
		$links = new links;
		$links->link("{LANG|Tilføj domæne}", "add");
		$html .= $links->html();
		
		// Tabel
		$tbl = new table;
		$tbl->th("{LANG|Domæne}");
		$tbl->th("{LANG|Layout}");
		$tbl->th("{LANG|Sprog}");
		$tbl->th("{LANG|Viderestil}");
		$tbl->th("{LANG|Valg}", 4);
		$tbl->endrow();
		
		// Henter 
		$db->execute("
			SELECT
				d.*,
				l.title AS language
			FROM
				" . $_table_prefix . "_domains_ AS d
			LEFT JOIN
				" . $_table_prefix . "_languages_ AS l
			ON
				l.id = d.lang_id
			ORDER BY
				domain
			");
			
		// Gennemløber
		while ($db->fetch_array())
		{
			$tbl->td(str_replace("%", "*", $db->array["domain"]));
			$tbl->td($db->array["layout"] <> "" ? $db->array["layout"] : "-");
			$tbl->td($db->array["language"] <> "" ? $db->array["language"] : "-");
			$tbl->td($db->array["redirect"] != "" ? $db->array["redirect"] : "-");
			$tbl->choise("{LANG|Ret}", "edit", $db->array["id"]);
			$tbl->choise("{LANG|Slet}", "delete", $db->array["id"], "{LANG|Slet dette domæne}?");
			$tbl->endrow();
		}
		
		if ($db->num_rows() == 0)
		{
			$tbl->td("{LANG|Ingen}...", 5);
		}
		
		// Viser tabel
		$html .= $tbl->html();
			
	}
?>
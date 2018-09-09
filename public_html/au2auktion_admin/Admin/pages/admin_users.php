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
		Beskrivelse:	Admin-brugere
	*/

	// Overskrift
	$msg = new message;
	$msg->title("{LANG|Admin-brugere}");
	$msg->message("{LANG|Herunder kan du styre brugere af administrationen}.");
	$html .= $msg->html();
	
	if ($do == "edit" or $do == "add")
	{
		//
		// Ret eller tilføj bruger
		//
		
		// Faste regler
		$default_rules = "ALLOW |login|*|*\r\n" .
			"ALLOW |logout|*|*\r\n" .
			"ALLOW |menu|*|*\r\n" .
			"ALLOW |frameset|*|*\r\n" .
			"ALLOW |change_password|*|*\r\n" .
			"ALLOW |default\r\n";
		
		// Sætter reg exp for brugernavn og password
		$usr->ereg_username = ".+";
		$usr->ereg_password = ".+";
		
		if ($do == "edit")
		{
			$user = $usr->get_user($id);
		}
		else
		{
			$user = false;
		}	
		
		$rights = " " . $user["extra_rights"];
		
		// Formular
		$frm = new form;
		$frm->tpl("th", $do == "edit" ? "{LANG|Ret bruger}" : "{LANG|Tilføj bruger}");
		if ($do == "add")
		{
			$frm->input(
				"{LANG|Brugernavn}",
				"usernamex",
				stripslashes($user["username"]),
				"^[\.@a-zA-Z0-9_-]+$",
				"{LANG|Ugyldigt brugernavn} - {LANG|må kun bestå af} a-z, 0-9 _ @ . {LANG|og} -",
				'
					// Tjekker om brugernavnet allerede er i brug
					global $usr;
					if (!$usr->check_username($this->values["usernamex"]))
					{
						$error = "{LANG|Brugernavnet er allerede i brug} !";
					}
				'
				);
		}
		else
		{
			$frm->tpl("td2", "{LANG|Brugernavn}:", $user["username"]);
		}
		$frm->password(
			"{LANG|Password}",
			"password",
			"",
			$do == "add" ? "^.+$" : "^.*$",
			"{LANG|Skal udfyldes}"
			);
		$frm->password(
			"{LANG|Gentag password}",
			"password_confirm",
			"",
			"",
			"",
			'
				// Tjekker at de to password stemmer overens
				if ($this->values["password"] <> $this->values["password_confirm"])
				{
					$error = "{LANG|De to passwords stemmer ikke overens} !";
				}
			'
			);
		$frm->input(
			"{LANG|Evt. e-mail} ({LANG|Bruges ved glemt password})",
			"email",
			$user["email"]
			);
			
		$frm->checkbox(
			"{LANG|Automatisk installation af opdateringer ved login}",
			"auto_update",
			$user["extra_auto_update"] == 1
			);
			
		$frm->tpl("th", "{LANG|Rettigheder}");
		if ($user["username"] == "Administrator")
		{
			$frm->tpl("td", "{LANG|Du kan ikke ændre rettigheder for administratoren}");
		}
		elseif ($user["id"] == $usr->user_id)
		{
			$frm->tpl("td", "{LANG|Du kan ikke ændre dine egne rettigheder}");
		}
		else
		{
			if ($usr->data["username"] == "Administrator" || $usr->data["administrator"] == 1)
			{
				$frm->checkbox(
					"{LANG|Administrator}",
					"administrator",
					$user["administrator"] == 1
					);
				$frm->tpl("td", "{LANG|Bemærk}, {LANG|at en administrator automatisk har adgang til alle funktioner og moduler}");
			}
			$frm->checkbox(
				"{LANG|Adgang til Menu}",
				"pages",
				strpos($rights, "|pages|") > 0
				);
			$frm->checkbox(
				"{LANG|Adgang til Indstillinger}",
				"settings",
				strpos($rights, "|settings|") > 0
				);
			$frm->checkbox(
				"{LANG|Adgang til Backup/Genskab}",
				"backup",
				strpos($rights, "|backup|") > 0
				);
			if ($usr->data["username"] == "Administrator" || $usr->data["administrator"] == 1)
			{
				$frm->checkbox(
					"{LANG|Adgang til Layouts}",
					"layouts",
					strpos($rights, "|layouts|") > 0
					);
				$frm->checkbox(
					"{LANG|Adgang til Moduler}",
					"modules",
					strpos($rights, "|modules|") > 0
					);
				$frm->checkbox(
					"{LANG|Adgang til Sprog}",
					"languages",
					strpos($rights, "|languages|") > 0
					);
				$frm->checkbox(
					"{LANG|Deaktiver adgang til at oprette / slette / ændre sprog}",
					"languages_noedit",
					strpos($rights, "|languages_noedit|") > 0
					);
				$frm->checkbox(
					"{LANG|Adgang til Domæner}",
					"domains",
					strpos($rights, "|domains|") > 0
					);
				$frm->checkbox(
					"{LANG|Adgang til System-meddelelser}",
					"log_messages",
					strpos($rights, "|log_messages|") > 0
					);
				$frm->checkbox(
					"{LANG|Adgang til Installation og Opdateringer}",
					"updates",
					strpos($rights, "|updates|") > 0
					);
				$frm->checkbox(
					"{LANG|Adgang til Admin-brugere - Modul-adgang}",
					"admin_users",
					strpos($rights, "|admin_users|") > 0
					);
			}
				
			$frm->tpl("th", "{LANG|Adgang til moduler}");
				
			// Finder moduler, så der kan tildeles rettigheder
			$array_installed = admin_module_installed();
			for ($i = 0; $i < count($array_installed); $i++)
			{
				$frm->checkbox(
					module2title($array_installed[$i]),
					"module_" . $array_installed[$i],
					strpos($rights, "|module_" . $array_installed[$i] . "|") > 0
					);
			}

			$frm->tpl("th", "{LANG|Specifikke regler}");
			$frm->textarea(
				"{LANG|Regler}",
				"rules",
				str_replace($default_rules, "", stripslashes($user["rules"])),
				"",
				"",
				"",
				40,
				7,
				"adminrules"
				);
			
		}
		
		if ($frm->done())
		{
			if ($do == "add")
			{
				// Tilføjer
				$id = $usr->create($frm->values["usernamex"], $frm->values["password"]);
			}
			else
			{
				// Skifter evt. password, hvis nyt er angivet
				if ($frm->values["password"] <> "")
				{
					$usr->change_password($frm->values["password"], $id);
				}
			}
			
			// Regler
			if ($frm->values["administrator"] == "" and $user["username"] != "Administrator" and $frm->values["rules"] != "")
			{
				$usr->update($id, array("rules" => $default_rules . $frm->values["rules"]));
			}
			else
			{
				$usr->update($id, array("rules" => ""));
			}
			
			// Gemmer e-mail
			$usr->update($id, array("email" => $frm->values["email"]));
			
			// Gemmer automatisk opdatering
			$usr->extra_set("auto_update", $frm->values["auto_update"] != "" ? 1 : 0, $id);
			
			// Opdaterer rettigheder
			if ($user["username"] != "Administrator" and $user["id"] != $usr->user_id)
			{
				// Rettigheder
				$rights = "";
				
				if ($vars["pages"] <> "") $rights .= "|pages|";
				if ($vars["settings"] <> "") $rights .= "|settings|";
				if ($vars["backup"] <> "") $rights .= "|backup|";
				if ($usr->data["administrator"] == 1 || $usr->data["username"] == "Administrator")
				{
					if ($vars["layouts"] <> "") $rights .= "|layouts|";
					if ($vars["modules"] <> "") $rights .= "|modules|";
					if ($vars["languages"] <> "") $rights .= "|languages|";
					if ($vars["languages_noedit"] <> "") $rights .= "|languages_noedit|";
					if ($vars["domains"] <> "") $rights .= "|domains|";
					if ($vars["log_messages"] <> "") $rights .= "|log_messages|";
					if ($vars["updates"] <> "") $rights .= "|updates||cms|";
					if ($vars["admin_users"] <> "") $rights .= "|admin_users|";
				}
				else
				{
					// Ikke administrator - kan kun tildele rettigheder til moduler
					$rights = $user["extra_rights"];
					$tmp = "";
					while ($tmp != $rights)
					{
						$tmp = $rights;
						$rights = eregi_replace("\|module_[^\|]+\|", "", $rights);
					}
				}
					
				for ($i = 0; $i < count($array_installed); $i++)
				{
					if ($vars["module_" . $array_installed[$i]] <> "")
					{
						$rights .= "|module_" . $array_installed[$i] . "|";
					}
				}
				$usr->extra_set("administrator", $frm->values["administrator"] <> "" ? 1 : 0, $id);
				$usr->extra_set("rights", $rights, $id);
			}
			
			// Tilbage til oversigten
			header("Location: ?page=$page");
			exit;
		}
		
		$html .= $frm->html();
		
	}
	elseif ($do == "delete")
	{
		//
		// Slet bruger
		//
		
		$user = $usr->get_user($id);
		
		if ($user["username"] <> "Administrator") $usr->admin_delete($id);
		
		header("Location: ?page=$page");
		exit;
		
	}
	elseif ($do == "log")
	{
		//
		// Log
		//
	
		// Links
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		$total = $db->execute_field("
			SELECT
				COUNT(*)
			FROM
				" . $_table_prefix . "_user_admin_log
			WHERE
				user_id = '$id'
			");
			
		$paging = new paging;
		$paging->total($total);
		$limit = $paging->limit(50);
		$start = ($paging->current_page() - 1) * $limit;
		$html .= $paging->html();
		
		$tbl = new table;
		$tbl->th("{LANG|Tid}");
		$tbl->th("{LANG|IP-adresse}");
		$tbl->th("{LANG|Handling}");
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_user_admin_log
			WHERE
				user_id = '$id'
			ORDER BY
				id DESC
			LIMIT
				$start, $limit
			");
		while ($db->fetch_array())
		{
			$tbl->td(date("d-m-Y H:i:s", strtotime($db->array["time"])));
			$tbl->td($db->array["ip"]);
			$tbl->td(nl2br(stripslashes($db->array["action"])));
			$tbl->endrow();
		}		
		
		if ($db->num_rows() == 0)
		{
			$tbl->td(date("d-m-Y H:i:s"));
			$tbl->td("{LANG|Ingen meddelelser}...");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
		
	}
	else
	{
		//
		// Oversigt
		//
	
		// Links
		$links = new links;
		$links->link("{LANG|Tilføj bruger}", "add");
		$html .= $links->html();
		
		// Oversigt
		$tbl = new table;
		$tbl->th("{LANG|Brugernavn}");
		$tbl->th("{LANG|Seneste logind}");
		$tbl->th("{LANG|Administrator}");
		$tbl->th("{LANG|Auto opdatering}");
		$tbl->th("{LANG|Valg}", 3);
		$tbl->endrow();
		
		$users = $usr->admin_search("", 0, 9999);
		
		while ($users->fetch_array())
		{
			$tbl->td($users->array["username"]);
			$tbl->td($users->array["login_time"] <> "" ? date("d-m-y H:i", strtotime($users->array["login_time"])) : "-", 1, 1, "center");
			if ($users->array["extra_administrator"] == 1)
			{
				$tmp = new tpl("admin_icon_active");
			}
			else
			{
				$tmp = new tpl("admin_icon_inactive");
			}
			$tbl->td($tmp->html(), 1, 1, "center");
			if ($users->array["extra_auto_update"] == 1)
			{
				$tmp = new tpl("admin_icon_active");
			}
			else
			{
				$tmp = new tpl("admin_icon_inactive");
			}
			$tbl->td($tmp->html(), 1, 1, "center");
			$tbl->choise("{LANG|Ret}", "edit", $users->array["id"]);
			if ($users->array["username"] <> "Administrator" and $users->array["id"] <> $usr->user_id)
			{
				$tbl->choise("{LANG|Slet}", "delete", $users->array["id"], "{LANG|Er du sikker på du vil slette denne bruger}?");
			}
			else
			{
				$tbl->td("");
			}
			$tbl->choise("{LANG|Log}", "log", $users->array["id"]);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
	}
?>
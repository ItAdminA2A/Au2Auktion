<?php
	$msg = new message;
	$msg->title("{LANG|Indstillinger}");
	$html .= $msg->html();
	
	if ($do == "check_writable_file_mode")
	{
		// Tjekker rettigheder på alle filer
		
		$links = new links;
		$links->link("{LANG|Tilbage}");
		$html .= $links->html();
		
		$frm = new form;
		$frm->tpl("th", "{LANG|Angiv mode til mapper/filer}");
		$frm->input(
			"{LANG|Ikke skrivbar mappe mode}",
			"default_folder_mode",
			cms_setting("default_folder_mode"),
			"^([0-9]{4}){0,1}$",
			"{LANG|Skal bestå af 4 tal - eller undlades}"
			);
		$frm->input(
			"{LANG|Ikke skrivbar fil mode}",
			"default_file_mode",
			cms_setting("default_file_mode"),
			"^([0-9]{4}){0,1}$",
			"{LANG|Skal bestå af 4 tal - eller undlades}"
			);
		$frm->input(
			"{LANG|Skrivbar mappe/fil mode}",
			"writable_file_mode",
			cms_setting("writable_file_mode"),
			"^([0-9]{4}){0,1}$",
			"{LANG|Skal bestå af 4 tal - eller undlades}"
			);

		if ($frm->done())
		{
			cms_setting("default_folder_mode", $frm->values["default_folder_mode"]);
			cms_setting("default_file_mode", $frm->values["default_file_mode"]);
			cms_setting("writable_file_mode", $frm->values["writable_file_mode"]);
			
			$html .= "{LANG|Kontrollerer mappe/fil rettigheder - vent venligst...}<br>";	
		
			$tmp = new tpl("iframe");
			$tmp->set("width", "500");
			$tmp->set("height", "500");
			$tmp->set("src", "./?page=$page&do=check_writable_file_mode_iframe");
			$html .= $tmp->html();
		}
		else
		{
			$html .= $frm->html();
		}
		
	}
	elseif ($do == "check_writable_file_mode_iframe")
	{
		// Tjekker fil rettigheder på alle filer

		// Mode til filer der skal have php upload aktiveret
		$writable_file_mode = cms_setting("writable_file_mode");
		$default_file_mode = cms_setting("default_file_mode");
		$default_folder_mode = cms_setting("default_folder_mode");
		
		// Slet buffer
		ob_end_clean();
		
		echo("
			<html>
			<head><style>
			body { font-family: Arial; font-size: 8pt; }
			th, td { text-align: center; font-size: 8pt; }
			</style></head>
			<body>
			<script>
			function autoScroll()
			{
				window.scrollBy(0, 10000);
			}
			var intervalAutoScroll = setInterval('autoScroll();', 1);
			</script>
			");
			
		// FTP objekt
		$ftp = new ftp;
		
		// Forbinder
		$ftp->connect($_cms_ftp_server) or die("Kunne ikke forbinde til FTP-server (" . $_cms_ftp_server . ")");
		
		// Logger ind
		$ftp->login($_cms_ftp_username, $_cms_ftp_password) or die("Kunne ikke logge på FTP-server (" . $_cms_ftp_username . ")");
		
		// Skifter til rod-mappe
		if ($_cms_ftp_root <> "")
		{
			$ftp->chdir($_cms_ftp_root) or die("Kunne ikke skifte til rod-mappe (" . $_cms_ftp_root . ")");
		}
		
		// Fil objekt
		$file = new file;
		
		// Finder alle mapper på web-hotel
		$folders = $file->find_folders($_document_root, true);
		$folders_ok = 0;
		$folders_changed = 0;
		$folders_error = 0;
		$folders_unknown = 0;
		for ($i = 0; $i < count($folders); $i++)
		{
			// Tjekker mappe
			$correct_mode = (eregi("/(upl|html|img|js|css|tmp)$", "/" . $folders[$i]) ? $writable_file_mode : $default_folder_mode);
			if ($correct_mode != false)
			{
				$actual_mode = substr(decoct(fileperms($_document_root . "/" . $folders[$i])), 1);
				if ($correct_mode == $actual_mode)
				{
					$folders_ok++;
				}
				else
				{
					// Ændrer rettigheder
					echo("Chmod $correct_mode " . $folders[$i]);
					if ($ftp->chmod($folders[$i], $correct_mode))
					{
						echo(" OK");
						$folders_changed++;
					}
					else
					{
						echo(" <font color=red>FEJL</font>");
						$folders_error++;
					}
					echo("<br>");
				}
				flush();
			}
			else
			{
				$folders_unknown++;
			}
		}
		
		// Finder alle filer på web-hotel
		$files = $file->find_files($_document_root, true);
		$files_ok = 0;
		$files_changed = 0;
		$files_error = 0;
		$files_unknown = 0;
		for ($i = 0; $i < count($files); $i++)
		{
			// Tjekker fil
			$correct_mode = ((eregi("/(modules|layouts)/[^/]+/(upl|html|img|js|css|tmp)/", "/" . $files[$i]) and substr($files[$i], -4) != ".php") ? $writable_file_mode : $default_file_mode);
			if ($correct_mode != false)
			{
				$actual_mode = substr(decoct(fileperms($_document_root . "/" . $files[$i])), 1);
				if ($correct_mode == $actual_mode)
				{
					$files_ok++;
				}
				else
				{
					// Ændrer rettigheder
					echo("Chmod $correct_mode " . $files[$i]);
					if ($ftp->chmod($files[$i], $correct_mode))
					{
						echo(" OK");
						$files_changed++;
					}
					else
					{
						echo(" <font color=red>FEJL</font>");
						$files_error++;
					}
					echo("<br>");
				}
				flush();
			}
			else
			{
				$files_unknown++;
			}
		}
		
		echo("
			<br>
			Færdig<br>
			<br>
			
			<table border=1>
				<tr>
					<th>
						Type
					</th>
					<th>
						OK
					</th>
					<th>
						Chmod OK
					</th>
					<th>
						Chmod FEJL
					</th>
					<th>
						Ukendt
					</th>
				</tr>
				<tr>
					<td>
						Mapper
					</td>
					<td>
						$folders_ok
					</td>
					<td>
						$folders_changed
					</td>
					<td>
						$folders_error
					</td>
					<td>
						$folders_unknown
					</td>
				</tr>
				<tr>
					<td>
						Filer
					</td>
					<td>
						$files_ok
					</td>
					<td>
						$files_changed
					</td>
					<td>
						$files_error
					</td>
					<td>
						$files_unknown
					</td>
				</tr>
			</table>
			<br>
			");
			
		if ($folders_changed > 0 or $folders_error > 0 or $files_changed > 0 or $files_error > 0)
		{
			echo("
				<font color=red>En yderlig kontrol bør gennemføres for at kontrollere at alle mapper/filer nu har korrekte rettigheder</font><br>
				<br>
				");
		}
			
		echo("
			<a href=\"./?page=$page&do=check_writable_file_mode_iframe\">» Kør kontrol igen</a>
			<script>
			clearInterval(intervalAutoScroll);
			window.scrollBy(0, 100000);
			</script>
			</body>
			</html>
			");
		
		exit;
			
	}
	else
	{
		// Viser indstillinger	
			
		$frm = new form;
		$frm->default_tab = $vars["tab"];
		$frm->submit_text = "{LANG|Gem ændringer}";
		
		// Side
		$frm->tab("{LANG|Side}");
		$frm->input(
			"{LANG|Titel på siden}",
			"SITE_TITLE",
			stripslashes($_settings_["SITE_TITLE"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->textarea(
			"{LANG|Beskrivelse af siden}",
			"SITE_DESCRIPTION",
			stripslashes($_settings_["SITE_DESCRIPTION"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->textarea(
			"{LANG|Søgeord - et per linie}",
			"SITE_KEYWORDS",
			str_replace(",", "\r\n", stripslashes($_settings_["SITE_KEYWORDS"])),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
			
		$frm->tab("{LANG|Cookies}");
		$frm->checkbox(
			"{LANG|Bruger skal give sammentykke til cookies}",
			"site_user_accept_cookies",
			cms_setting("site_user_accept_cookies") == 1
			);
		$frm->tpl("td", "{LANG|Hvis brugeren ikke giver sin sammentykke vil det ikke være<br>muligt at logge ind, handle i webshoppen etc}");
		$frm->input(
			"{LANG|URL til cookiepolitik}",
			"site_url_policy",
			cms_setting("site_url_policy")
			);
			
		$frm->tab("{LANG|E-mail}");
		$frm->input(
			"{LANG|Standard afsender navn}",
			"SITE_EMAIL_NAME",
			stripslashes($_settings_["SITE_EMAIL_NAME"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->input(
			"{LANG|Standard afsender e-mail}",
			"SITE_EMAIL",
			stripslashes($_settings_["SITE_EMAIL"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
		$frm->checkbox(
			"{LANG|Vedhæft billeder}",
			"EMAIL_ATTACH_IMAGES",
			$_settings_["EMAIL_ATTACH_IMAGES"] == 1
			);
			
		$frm->select(
			"{LANG|Send e-mail via}",
			"EMAIL_METHOD",
			$_settings_["EMAIL_METHOD"],
			"",
			"",
			"",
			array(
				array("",		"PHP mail()"),
				array("smtp",	"SMTP server")
				)
			);
		$frm->input(
			"{LANG|SMTP server}",
			"EMAIL_SMTP_HOST",
			stripslashes($_settings_["EMAIL_SMTP_HOST"]),
			"",
			"",
			'
				if ($this->values["EMAIL_METHOD"] == "smtp" and $this->values["EMAIL_SMTP_HOST"] == "")
				{
					$error = "{LANG|Skal udfyldes}";
				}
			'
			);
		$frm->input(
			"{LANG|SMTP port}",
			"EMAIL_SMTP_PORT",
			stripslashes($_settings_["EMAIL_SMTP_PORT"]),
			"^[0-9]*",
			"{LANG|Må kun bestå af tal}",
			'
				if ($this->values["EMAIL_METHOD"] == "smtp" and $this->values["EMAIL_SMTP_PORT"] == "")
				{
					$error = "{LANG|Skal udfyldes}";
				}
			'
			);
		$frm->input(
			"{LANG|SMTP brugernavn}",
			"EMAIL_SMTP_USER",
			$_settings_["EMAIL_SMTP_USER"]
			);
		$frm->input(
			"{LANG|SMTP password}",
			"EMAIL_SMTP_PASS",
			"*****"
			);
			
		$frm->tpl("th", "{LANG|Retur-mail}");
		$frm->tpl("td", "{LANG|Disse indstillinger kræver at der er oprettet en POP3-konto til retur-mail},<br>{LANG|samt at der benyttes SMTP-server til afsendelse af mails}");
		$frm->input(
			"{LANG|Retur e-mail}",
			"RETURN_EMAIL",
			stripslashes($_settings_["RETURN_EMAIL"])
			);
		$frm->input(
			"{LANG|POP3-server}",
			"RETURN_EMAIL_SERVER",
			stripslashes($_settings_["RETURN_EMAIL_SERVER"])
			);
		$frm->input(
			"{LANG|POP3-brugernavn}",
			"RETURN_EMAIL_USER",
			stripslashes($_settings_["RETURN_EMAIL_USER"])
			);
		$frm->input(
			"{LANG|POP3-password}",
			"RETURN_EMAIL_PASS",
			"*****"
			);
			
		$frm->tab("{LANG|SMS-gateway}");
		$frm->input(
			"{LANG|HTTP GET URL}<br>{LANG|F.eks.}<br>http://sms.gw/?user=me&pass=secret<br>&mobile={<!>MOBILE}&message={<!>MESSAGE}<br>&sender={<!>SENDER}",
			"sms_url",
			$_settings_["sms_url"]
			);
		$frm->checkbox(
			"{LANG|Tillad flere numre pr. HTTP kald}",
			"sms_multiple",
			$_settings_["sms_multiple"] == 1
			);
		$frm->input(
			"{LANG|Adskil flere numre med}",
			"sms_separator",
			$_settings_["sms_separator"]
			);
		$frm->input(
			"{LANG|Svar ved OK}",
			"sms_response_ok",
			$_settings_["sms_response_ok"]
			);
		$frm->checkbox(
			"{LANG|Svar ved OK er regular expression}",
			"sms_response_regexp",
			$_settings_["sms_response_regexp"] == 1
			);
			
		$frm->tab("{LANG|Printer}");
		$frm->select(
			"{LANG|Print metode}",
			"printer_method",
			$_settings_["printer_method"],
			"",
			"",
			"",
			array(
				array("email", "Send til e-mail (vedhæftet PDF/TXT)"),
				array("lpr", "Linux LPR (benytter standard printer)")
				)
			);
		$frm->input(
			"{LANG|E-mail (hvis `Send til e-mail` er valgt)}",
			"printer_email",
			$_settings_["printer_email"]
			);
			
		$frm->tab("{LANG|Viderestil / Omskriv}");
		$frm->select(
			"{LANG|Viderestil altid til}",
			"smart_url_redir",
			$_settings_["smart_url_redir"],
			"",
			"",
			"",
			array(
				array("", "{LANG|Ingen viderestilling}"),
				array("smart_url", "{LANG|Til smart URL}"),
				array("param", "/?module=&page=&do=&id="),
				array("real_url", "/site/da/MODULE/PAGE/DO/ID")
				)
			);
		$frm->checkbox(
			"{LANG|Viderestil altid forside til} /",
			"frontpage_redir",
			$_settings_["frontpage_redir"] == 1
			);
		$frm->select(
			"{LANG|Omskriv links til}",
			"url_rewrite",
			$_settings_["url_rewrite"],
			"",
			"",
			"",
			array(
				array("", "{LANG|Ingen omskrivning}"),
				array("smart_url", "{LANG|Til smart URL}"),
				array("param", "/?module=&page=&do=&id="),
				array("site", "/site/da/MODULE/PAGE/DO/ID")
				)
			);
			
		$frm->tab("{LANG|Sitemap} / {LANG|Indeksering}");
		$frm->checkbox(
			"{LANG|Lad inaktive menu-punkter indgå i sitemap}",
			"inactive_menu_sitemap",
			$_settings_["inactive_menu_sitemap"] == 1
			);
		$frm->checkbox(
			"{LANG|Lad ikke-publicerede menu-punkter indgå i sitemap}",
			"nonpublic_menu_sitemap",
			$_settings_["nonpublic_menu_sitemap"] == 1
			);
		$frm->tpl("td", "&nbsp;");
		
		$frm->tpl("td", "{LANG|Afkrydses `Indekser kun smart-urls` vil afkrydsning af moduler være uden betydning}<br>" .
			"{LANG|Hvis der ikke er afkrydsninger, vil alt kunne indekseres}<br>" .
			"{LANG|CMS-systemet tilføjer META-tag for at fortælle søgemaskiner at en given side ikke må indekseres}");
		$frm->checkbox(
			"{LANG|Indekser kun smart-urls}",
			"index_only_smart_urls",
			$_settings_["index_only_smart_urls"] == 1
			);
		$index_modules = explode("|", $_settings_["index_modules"]);
		$inst_modules = admin_module_installed();
		$vars_inst_modules = array();
		for ($i = 0; $i < count($inst_modules); $i++)
		{
			$frm->checkbox(
				"{LANG|Modul}: " . module2title($inst_modules[$i]),
				"index_module_" . $inst_modules[$i],
				in_array($inst_modules[$i], $index_modules)
				);
			if ($vars["index_module_" . $inst_modules[$i]] != "") $vars_inst_modules[] = $inst_modules[$i];
		}
			
		$frm->tab("{LANG|Info}");
		$check = $_settings_["bot_check"];
		if ($check == "" or !$check)
		{
			for ($i = 0; $i < 25; $i++)
			{
				$check .= chr(rand(97, 122));
			}
			$set->set("bot_check", $check);
		}
		$bot_url = $_site_url . "/Admin/?page=bot&check=$check";
		$frm->tpl("td2", "{LANG|Cron job URL}:", "<a href=\"$bot_url\" onclick=\"return false;\">$bot_url</a>");
		$tmp_url = $_site_url . "/google_sitemap.php";
		$frm->tpl("td2", "{LANG|Google sitemap URL}:", "<a href=\"$tmp_url\" onclick=\"return false;\">$tmp_url</a>");
		
		$frm->tab("{LANG|CMS klasser}");
		
		$frm->tpl("th", "{LANG|Sidetal} (paging)");
		$frm->input(
			"{LANG|Antal sidetal der vises af gangen}",
			"paging_pages",
			$_settings_["paging_pages"] > 3 ? $_settings_["paging_pages"] : 15,
			"^[1-9]+[0-9]*$",
			"{LANG|Kun tal}",
			'
				if (intval($this->values["paging_pages"]) < 3)
				{
					$error = "{LANG|Min. 3 sider}";
				}
			'
			);
		
		// Mulige dato formater
		$array_date_format = array(
			"%d-%m-%Y",
			"%d/%m/%Y",
			"%Y-%m-%d",
			"%Y/%m/%d",
			"%d-%m-%Y %H:%M",
			"%d/%m/%Y %H:%M",
			"%Y-%m-%d %H:%M",
			"%Y/%m/%d %H:%M",
			"%d-%m-%y",
			"%d/%m/%y",
			"%y-%m-%d",
			"%y/%m/%d",
			"%d-%m-%y %H:%M",
			"%d/%m/%y %H:%M",
			"%y-%m-%d %H:%M",
			"%y/%m/%d %H:%M",
			"%d/%m",
			"%d/%m %H:%M",
			"%d. %b.",
			"%d. %b. %H:%M",
			"%d. %B",
			"%d. %B %H:%M",
			"%d. %b. %Y",
			"%d. %b. %Y %H:%M",
			"%d. %B %Y",
			"%d. %B %Y %H:%M",
			"%d. %b. %y",
			"%d. %b. %y %H:%M",
			"%d. %B %y",
			"%d. %B %y %H:%M",
			"%H:%M",
			"%H:%M:%S"
			);
		$select_date_format = array();
		for ($i = 0; $i < count($array_date_format); $i++)
		{
			$select_date_format[] = array(
				$array_date_format[$i],
				strftime($array_date_format[$i])
				);
		}
			
		$frm->tpl("th", "{LANG|Konvertering} (convert)");
		$frm->select(
			"{LANG|Datoformat (i dag)}",
			"convert_date_today",
			$_settings_["convert_date_today"],
			"",
			"",
			"",
			$select_date_format
			);
		$frm->select(
			"{LANG|Datoformat (i år)}",
			"convert_date_year",
			$_settings_["convert_date_year"],
			"",
			"",
			"",
			$select_date_format
			);
		$frm->select(
			"{LANG|Datoformat (andre)}",
			"convert_date_default",
			$_settings_["convert_date_default"],
			"",
			"",
			"",
			$select_date_format
			);
			
		$frm->tpl("th", "{LANG|Formular} (form)");
		$frm->select(
			"{LANG|Editor}",
			"form_editor",
			$_settings_["form_editor"],
			"^(spaw|tinymce)$",
			"",
			"",
			array(
				array("spaw", "SPAW"),
				array("tinymce", "Tiny MCE")
				)
			);			
			
		$frm->tab("{LANG|HEAD (Analytics etc.)}");
		$frm->tpl("td", "{LANG|Koden i feltet herunder tilføjes før &lt;/head&gt; i din HTML-kode}");
		$frm->textarea(
			"{LANG|HTML}",
			"site_head",
			$_settings_["SITE_HEAD"],
			"",
			"",
			"",
			75,
			25
			);
			
		$frm->tab("{LANG|Variabler}");
		$frm->tpl("td", "{LANG|Angiv variabelnavn, f.eks. MINKODE, som så automatisk prefikses med} USER_<br />" .
			"{LANG|og derfor kan indsættes via flg. tag}:<br />" .
			"{<!>USER_MINKODE}");
		
		$frm->tpl("th", "{LANG|Tilføj variabel}");
		$frm->input(
			"{LANG|Variabelnavn}",
			"adduser_name",
			"",
			"^[a-zA-Z_]*$",
			"{Må kun bestå af a-z samt _}"
			);
		$frm->textarea(
			"{LANG|Variabelværdi}",
			"adduser_value"
			);
			
		reset($_settings_);
		while (list($key, $value) = each($_settings_))
		{
			if (preg_match("/^USER_([a-zA-Z_]+)$/", $key, $array))
			{
				$frm->tpl("th", "{LANG|Variabelnavn}: " . $key);
				$frm->textarea(
					"{LANG|Variabelværdi}",
					$key,
					$value
					);
				$frm->checkbox(
					"{LANG|Slet variabel}",
					"delete" . $key
					);
			}
		}
		
		$frm->tab("{LANG|Admin}");
		$frm->input(
			"{LANG|Max loginfejl pr. time}",
			"admin_max_login_errors",
			is_numeric($_settings_["admin_max_login_errors"]) ? $_settings_["admin_max_login_errors"] : 3,
			"^[1-9]+[0-9]*$",
			"{LANG|Positivt tal påkrævet}"
			);
			
		// Forside ved login
		$select_pages = array();
		$array1 = admin_module_menu();
		reset($array1);
		while (list($folder, $menu) = each($array1))
		{
			$elements = "";
			reset($menu);
			while (list($key, $value) = each($menu))
			{
				$key2 = str_replace("{LANG|", "", $key);
				$key2 = str_replace("}", "", $key2);
				$title = $_lang_[$folder][$key2];
				if ($title == "") $title = $key;
				$array = split("[|]", $value);
				
				$select_pages[] = array("./?module=$folder&page=" . $array[0] . "&do=" . $array[1], module2title($folder) . " - " . $title);
			}
		}
		
		$frm->combo(
			"{LANG|Forside ved login i admin}",
			"admin_frontpage",
			$_settings_["admin_frontpage"],
			"",
			"",
			"",
			$select_pages
			);
		
		if ($frm->done())
		{
			// Side
			$set->set("SITE_TITLE", $frm->values["SITE_TITLE"]);
			$set->set("SITE_DESCRIPTION", $frm->values["SITE_DESCRIPTION"]);
			$set->set("SITE_KEYWORDS", str_replace("\r\n", ",", $frm->values["SITE_KEYWORDS"]));
			
			// Cookies
			$set->set("site_user_accept_cookies", $frm->values["site_user_accept_cookies"] != "" ? 1 : 0);
			$set->set("site_url_policy", $frm->values["site_url_policy"]);
			
			// E-mail
			$set->set("SITE_EMAIL", $frm->values["SITE_EMAIL"]);
			$set->set("SITE_EMAIL_NAME", $frm->values["SITE_EMAIL_NAME"]);
			$set->set("EMAIL_METHOD", $frm->values["EMAIL_METHOD"]);
			$set->set("EMAIL_SMTP_HOST", $frm->values["EMAIL_SMTP_HOST"]);
			$set->set("EMAIL_SMTP_PORT", $frm->values["EMAIL_SMTP_PORT"]);
			$set->set("EMAIL_SMTP_USER", $frm->values["EMAIL_SMTP_USER"]);
			if ($frm->values["EMAIL_SMTP_PASS"] != "" && $frm->values["EMAIL_SMTP_PASS"] != "*****") $set->set("EMAIL_SMTP_PASS", $frm->values["EMAIL_SMTP_PASS"]);
			$set->set("EMAIL_ATTACH_IMAGES", $frm->values["EMAIL_ATTACH_IMAGES"] != "" ? 1 : 0);
			
			// Retur e-mail
			$set->set("RETURN_EMAIL", $frm->values["RETURN_EMAIL"]);
			$set->set("RETURN_EMAIL_SERVER", $frm->values["RETURN_EMAIL_SERVER"]);
			$set->set("RETURN_EMAIL_USER", $frm->values["RETURN_EMAIL_USER"]);
			if ($frm->values["RETURN_EMAIL_PASS"] != "" && $frm->values["RETURN_EMAIL_PASS"] != "*****") $set->set("RETURN_EMAIL_PASS", $frm->values["RETURN_EMAIL_PASS"]);
			
			// SMS gateway
			$set->set("sms_url", $frm->values["sms_url"]);
			$set->set("sms_multiple", $frm->values["sms_multiple"] != "" ? 1 : 0);
			$set->set("sms_separator", $frm->values["sms_separator"]);
			$set->set("sms_response_ok", $frm->values["sms_response_ok"]);
			$set->set("sms_response_regexp", $frm->values["sms_response_regexp"] != "" ? 1 : 0);
			
			// Printer
			$set->set("printer_method", $frm->values["printer_method"]);
			$set->set("printer_email", $frm->values["printer_email"]);
			
			// Smart URL
			$set->set("smart_url_redir", $frm->values["smart_url_redir"]);
			$set->set("frontpage_redir", $frm->values["frontpage_redir"] != "" ? 1 : 0);
			$set->set("url_rewrite", $frm->values["url_rewrite"]);
			
			// Sitemap / indeksering
			$set->set("inactive_menu_sitemap", $frm->values["inactive_menu_sitemap"] != "" ? 1 : 0); 
			$set->set("nonpublic_menu_sitemap", $frm->values["nonpublic_menu_sitemap"] != "" ? 1 : 0);
			$set->set("index_only_smart_urls", $frm->values["index_only_smart_urls"] != "" ? 1 : 0);
			$set->set("index_modules", $frm->values["index_only_smart_urls"] != "" ? "" : implode("|", $vars_inst_modules));
			
			// CMS klasser
			$set->set("paging_pages", intval($frm->values["paging_pages"]));
			$set->set("convert_date_today", $frm->values["convert_date_today"]);
			$set->set("convert_date_year", $frm->values["convert_date_year"]);
			$set->set("convert_date_default", $frm->values["convert_date_default"]);
			$set->set("form_editor", $frm->values["form_editor"]);
			
			// HTML HEAD
			$set->set("SITE_HEAD", $frm->values["site_head"]);
			
			// Variabler
			reset($_settings_);
			while (list($key, $value) = each($_settings_))
			{
				if (preg_match("/^USER_([a-zA-Z_]+)$/", $key, $array))
				{
					if ($frm->values["delete" . $key] != "")
					{
						$set->delete($key);
					}
					else
					{
						$set->set($key, $frm->values[$key]);
					}
				}
			}
			if ($frm->values["adduser_name"] != "")
			{
				$set->set("USER_" . strtoupper($frm->values["adduser_name"]), $frm->values["adduser_value"]);
			}
			
			// Admin
			$set->set("admin_max_login_errors", intval($frm->values["admin_max_login_errors"]));
			$set->set("admin_frontpage", $frm->values["admin_frontpage"]);
			
			header("Location: ./?page=$page&do=saved&tab=" . $frm->default_tab);
			exit;
		}
		
		if ($do == "saved")
		{
			// OK
			$msg = new message;
			$msg->type("section");
			$msg->title("{LANG|Ændringerne er gemt}");
			$html .= $msg->html();
		}
			
		$html .= $frm->html();
		
		// Send test e-mail
		
		$frm2 = new form("email");
		$frm2->tpl("th", "{LANG|Send test e-mail}");
		$frm2->input(
			"{LANG|Modtager e-mail} ({LANG|Adskil flere med komma})",
			"email",
			stripslashes($_settings_["SITE_EMAIL"]),
			"^.+$",
			"{LANG|Skal udfyldes}"
			);
			
		if ($frm2->done())
		{
			$mail = new email;
			$array = split("[,]", $frm2->values["email"]);
			for ($i = 0; $i < count($array); $i++)
			{
				$mail->to($usr->data["username"], $array[$i], $array[$i]);
			}
			$mail->subject("{LANG|Test e-mail}");
			$mail->body("{LANG|Hej} [NAME]<br />{LANG|Denne e-mail er sendt via} " .
				($_settings_["EMAIL_METHOD"] == "smtp" ? "SMTP-server" : "PHP mail()") . " {LANG|til} [EMAIL]");
			if ($mail->send())
			{
				$msg = new message;
				$msg->type("section");
				$msg->title("{LANG|E-mailen er afsendt korrekt - kontroller din indbakke}");
				$html .= $msg->html();
			}
			else
			{
				$msg = new message;
				$msg->type("error");
				$msg->title("{LANG|E-mailen kunne ikke sendes - kontroller dine indstillinger}");
				$msg->message($mail->error);
				$html .= $msg->html();
			}
		}
		
		$html .= $frm2->html();
		
		
		// Send test sms
		
		$frm2 = new form("sms");
		$frm2->tpl("th", "{LANG|Send test SMS}");
		$frm2->input(
			"{LANG|Modtager numre} ({LANG|Adskil flere med komma})",
			"mobile",
			"",
			"^[0-9,]+$",
			"{LANG|Skal udfyldes}"
			);
			
		if ($frm2->done())
		{
			$sms = new sms;
			$array = split("[,]", $frm2->values["mobile"]);
			for ($i = 0; $i < count($array); $i++)
			{
				$sms->mobile($array[$i]);
			}
			
			$tmp = new tpl("{LANG|Dette er en test SMS}");
			$message = $tmp->html();
			
			$sms->sender("Test SMS");
			$sms->message($message);
			
			if ($sms->send())
			{
				$msg = new message;
				$msg->type("section");
				$msg->title("{LANG|SMSen er afsendt korrekt - kontroller din indbakke}");
				$html .= $msg->html();
			}
			else
			{
				$msg = new message;
				$msg->type("error");
				$msg->title("{LANG|SMSen kunne ikke sendes - kontroller dine indstillinger}");
				$msg->message($sms->error);
				$html .= $msg->html();
			}
		}
		
		$html .= $frm2->html();
		
		
		// Send test print
		$frm3 = new form("print");
		$frm3->tpl("th", "{LANG|Send test Print}");
			
		if ($frm3->done())
		{
			$tmpfile = $_document_root . "/tmp/" . uniqid(time()) . ".txt";
			file_put_contents($tmpfile, "Dette er et test-print printet via " . 
				($_settings_["printer_method"] == "email" ? ("e-mail (" . $_settings_["printer_email"] . ")") : "Linux LPR"));
				
			if (print_file($tmpfile))
			{
				$msg = new message;
				$msg->type("section");
				$msg->title("{LANG|Test print er afsendt - kontroller din printer}");
				$html .= $msg->html();
			}
			else
			{
				$msg = new message;
				$msg->type("error");
				$msg->title("{LANG|Test print kunne ikke afsendes - kontroller dine indstillinger}");
				$html .= $msg->html();
			}
			
			unlink($tmpfile);
		}
		
		$html .= $frm3->html();
		
		
		
		$links = new links;
		$links->link("{LANG|Kontroller fil rettigheder på web-hotel}", "check_writable_file_mode");
		$html .= $links->html();
	}
?>
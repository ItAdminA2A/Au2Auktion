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
	
	// Start output buffer og session
	ob_start();
	session_set_cookie_params(0, "/Admin/");
	session_start();

	$_cms_allow_cookies = true;
	
	// Nødvendige filer
	require("../inc/config.php");
	require("../inc/functions.php");
	
	// Inkluderer alle class'es
	$mappe = dir(realpath($_document_root . "/class/"));
	while ($fil = $mappe->read())
	{
		if (is_file($_document_root . "/class/" . $fil))
		{
			if (ereg("\.php$", $fil))
			{
				include($_document_root . "/class/" . $fil);
			}
		}
	}
	
	// Input-variabler
	$vars = $_SERVER["REQUEST_METHOD"] == "POST" ? $_POST : $_GET;
	
	// Tjekker referer
	if ($_SERVER["HTTP_REFERER"] != "" and substr($_SERVER["HTTP_REFERER"], 0, strlen($_site_url . "/")) != $_site_url . "/")
	{
		// Referer ikke ok
		add_log_message("Referer fejl<br>" .
			"HTTP referer: " . $_SERVER["HTTP_REFERER"] . "<br>" .
			"Site URL: " . $_site_url);
		$vars = array();
	}
	
	// Standard variabler
	$module = $vars["module"];
	$page = $vars["page"];
	$do = $vars["do"];
	$id = $vars["id"];
	
	// Database objekt
	$db = new db;
	
	// Tjekker forbindelse
	if (!$db->is_connected)
	{
		// Fejl i forbindelse til MySQL
		die("Ingen forbindelse til MySQL-server !");
	}
	
	// Tjekker tabeller
	$db->execute("SHOW TABLES LIKE '" . $_table_prefix . "%'");
	if (!$db->fetch_array())
	{
		// Opretter tabeller
		include("install.php");
	}
	
	// Loader indstillinger
	$set = new settings;
	
	// Skifter sprog
	change_language($_COOKIE["admin_lang_id"] <> "" ? $_COOKIE["admin_lang_id"] : $_SESSION["admin_lang_id"]);
	if (!isset($_SESSION["admin_lang_id"])) $_SESSION["admin_lang_id"] = $_lang_id;
	if (!isset($_COOKIE["admin_lang_id"])) setcookie("admin_lang_id", $_lang_id, time() + 365*86400, "/Admin/");
	
	// Tjekker modul og side
	if (!ereg("^[A-Za-z_0-9-]+$", $module))		$module = "";
	if (!ereg("^[A-Za-z_0-9-]+$", $page))		$page = "frameset";
	if (!ereg("^[A-Za-z_0-9-]+$", $do))			$do = "";
	if (!ereg("^[0-9]+$", $id))					$id = "";
	if ($module <> "" and !is_file($_document_root . "/modules/" . $module . "/admin/" . $page . ".php"))
												$module = "";
	// Aktuel admin-bruger
	$usr = new user("admin");
	if (intval(cms_setting("admin_max_login_errors")) > 0) $usr->max_login_errors = intval(cms_setting("admin_max_login_errors"));
	if ($usr->logged_in)
	{
		// Tjekker om det er administrator eller om der er rettigheder til dette modul
		$rights = " " . $usr->extra_get("rights");
		if ($usr->extra_get("administrator") <> 1 and 
			(
				$module <> "" and
				strpos($rights, "|module_$module|") === false
				or
				$module == "" and
				$page <> "logout" and
				$page <> "change_password" and
				$page <> "keep_alive" and
				$page <> "bot" and
				$page <> "js_links" and
				$page <> "frameset" and
				$page <> "menu" and
				$page <> "rule_editor" and
				strpos($rights, "|$page|") === false				
			))
		{
			// Det er der ikke rettigheder til - kontroller om auto update er ok
			if ($usr->extra_get("auto_update") == 1 and $module == "" and
				(
					$page == "auto_update" or
					$page == "cms" and $do == "update_all_iframe" or
					$page == "modules" and $do == "update_all_iframe" or
					$page == "modules" and $do == "update_dummy_iframe"
				))
			{
				// Auto update - det må brugeren godt
			}
			else
			{
				// Ingen rettigheder
				$module = "";
				$page = "default";
				$do = "";
				$id = "";
			}
		}
	}
	elseif ($page == "js_links")
	{
		// Kald til js_links, så stopper vi scriptet for ikke at få javascript fejl
		$tmp = new tpl("admin_js_links");
		$tmp->set("elements", $tmp_elements);
		$tmp->view();
		exit;
	}
	else
	{
		// Ikke logget ind, så vi viser logind-siden
		$module = "";
		if ($page <> "bot") $page = "login";
	}

	if ($module <> "")
	{
		$include_file = $_document_root . "/modules/" . $module . "/admin/" . $page . ".php";
	}
	else
	{
		if (!is_file("pages/" . $page . ".php"))
		{
			$page = "default";
		}
		$include_file = "pages/" . $page . ".php";
	}
	
	// Henter indstillinger for domæne
	$db->execute("
		SELECT
			*
		FROM
			" . $_table_prefix . "_domains_
		WHERE
			domain = '" . $db->escape($_SERVER["HTTP_HOST"]) . "' OR
			'" . $db->escape($_SERVER["HTTP_HOST"]) . "' LIKE domain
		ORDER BY
			IF(domain = '" . $db->escape($_SERVER["HTTP_HOST"]) . "', 0, 1)
		LIMIT
			0, 1
		");
	if ($db->fetch_array())
	{
		/*
		// Viderestil?
		if ($db->array["redirect"] != "")
		{
			header($_SERVER["SERVER_PROTOCOL"] . " 301 Moved Permanently");
			header("Location: " . str_replace("{URI}", $_SERVER["REQUEST_URI"], $db->array["redirect"]));
			exit;
		}
		*/

		reset($db->array);
		while (list($key, $value) = each($db->array))
		{
			if ($key == "lang_id")
			{
				if ($vars["lang_id"] == "") $vars["lang_id"] = $value;
			}
			elseif ($key == "layout")
			{
				if ($value != "") $_settings_["SITE_LAYOUT"] = $value;
			}
			elseif (preg_match("/^(site|email|return)_.+$/", $key))
			{
				if ($value != "") $_settings_[strtoupper($key)] = $value;
			}
		}

		// Bruger variabler
		$user_settings = split("[\n]", str_replace("\r", "", stripslashes($db->array["user_settings"])));
		for ($i = 0; $i < count($user_settings); $i++)
		{
			list($key, $value) = split("[=]", $user_settings[$i]);
			$_settings_["USER_" . strtoupper($key)] = $value;
		}
	}
	
	// HTML og template til denne side
	$html = "";
	$tpl = "default";
	
	if ($usr->logged_in)
	{
		// Henter alle installerede moduler
		$array_module_installed = admin_module_installed();
		
		// Inkluderer header-filer fra moduler
		for ($int_module_installed = 0; $int_module_installed < count($array_module_installed); $int_module_installed++)
		{
			if (is_file($_document_root . "/modules/" . $array_module_installed[$int_module_installed] . "/admin/header.php"))
			{
				$tmp_old_module = $module;
				$tmp_old_page = $page;
				$tmp_old_do = $do;
				$tmp_old_id = $id;
				$module = $array_module_installed[$int_module_installed];
				$page = "";
				$do = "";
				$id = 0;
				include($_document_root . "/modules/" . $array_module_installed[$int_module_installed] . "/admin/header.php");
				$module = $tmp_old_module;
				$page = $tmp_old_page;
				$do = $tmp_old_do;
				$id = $tmp_old_id;
			}
		}
	}
		
	// Inkluderer fil
	require($include_file);
	
	if ($usr->logged_in)
	{
		// Inkluderer footer-filer fra moduler
		for ($int_module_installed = 0; $int_module_installed < count($array_module_installed); $int_module_installed++)
		{
			if (is_file($_document_root . "/modules/" . $array_module_installed[$int_module_installed] . "/admin/footer.php"))
			{
				$tmp_old_module = $module;
				$tmp_old_page = $page;
				$tmp_old_do = $do;
				$tmp_old_id = $id;
				$module = $array_module_installed[$int_module_installed];
				$page = "";
				$do = "";
				$id = 0;
				include($_document_root . "/modules/" . $array_module_installed[$int_module_installed] . "/admin/footer.php");
				$module = $tmp_old_module;
				$page = $tmp_old_page;
				$do = $tmp_old_do;
				$id = $tmp_old_id;
			}
		}
	}
	
	// Template til denne side
	$tpl = new tpl("admin_layout_" . $tpl);
	$tpl->set("html", $html);
	$tpl->view();
?>
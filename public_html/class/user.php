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
		Klasse:			user
		Beskrivelse:	Bruger-klasse, der kan håndtere oprettelse, validering,
						ændring, logind af brugere
		26-04-2007:		Mulighed for flere sprog
		03-05-2007:		Mulighed for aktiv/ikke aktiv bruger
		22-01-2008:		Gemmer fejl login i databasen
		04-05-2009:		Tilføjet felt 'rules' set_rules() og get_rules() og kontrol af rettigheder, f.eks.:
							DENY ALL
							ALLOW WYSIWYG|default|show
							ALLOW Nyheder|default|overview
						Er brugeren ikke logget ind, kan rettighederne sættes for bruger-gruppen med no_user_rules(), f.eks.
							no_user_rules("DENY ALL\r\n" .
								"ALLOW Brugere|login\r\n" .
								"ALLOW Brugere|signup\r\n" .
								"ALLOW Brugere|forgot_password\r\n" .
								"ALLOW Brugere|restore_password");
						Er brugeren logget ind, men ikke har tildelt rettigheder kan disse sættes med default_rules()
						Er der ingen regler angivet gives rettigheder til alt
						Med deny_url() stilles brugeren videre til angivne url, ellers vises blot Access Denied
						Angives f.eks.
							ALLOW |default|*|1
								tillades kald af filen /pages/default.php, hvor id = 1
	*/
	
	class user
	{
		// Aktuelt bruger-id
		var $user_id = false;
		
		// Angiver om brugeren er logget ind
		var $logged_in = false;
		
		// Angiver tabel-ID
		var $group_id = false;
		
		// Brugerens data
		var $data = false;
		
		// RegExp der anvendes til tjek af brugernavn
		var $ereg_username = "^[a-zA-ZæøåÆØÅ0-9_-]{3,50}\$";
		
		// RegExp der anvendes til tjek af password
		var $ereg_password = "^.{3,}\$";
		
		// Max login forsøg pr. time
		var $max_login_errors = 3;
		
		// Antal login fejl seneste time
		var $login_errors = 0;
		
		// Array med alle felter i tabellen brugere - dog ikke id, username, password, create*, login*
		var $array_fields = array(
			"vat" 			=> "VARCHAR( 25 ) NOT NULL ",
			"company" 		=> "VARCHAR( 50 ) NOT NULL ",
			"name" 			=> "VARCHAR( 50 ) NOT NULL ",
			"address" 		=> "VARCHAR( 255 ) NOT NULL ",
			"zipcode" 		=> "VARCHAR( 10 ) NOT NULL ",
			"city" 			=> "VARCHAR( 50 ) NOT NULL ",
			"country" 		=> "VARCHAR( 50 ) NOT NULL ",
			"phone" 		=> "VARCHAR( 15 ) NOT NULL ",
			"mobile" 		=> "VARCHAR( 15 ) NOT NULL ",
			"email" 		=> "VARCHAR( 50 ) NOT NULL ",
			"birthday" 		=> "DATE NOT NULL ",
			"active" 		=> "TINYINT(1) NOT NULL DEFAULT '1'",
			"rules" 		=> "TEXT",
			"lang_id" 		=> "VARCHAR(2) NOT NULL DEFAULT ''",
			"sex"			=> "varchar(6) not null default ''",
			"referer"		=> "varchar(50) not null default ''",
			"comment"		=> "TEXT ",
			);
		
		// Sætter deny url
		function deny_url($url = -1)
		{
			return cms_setting("class_user_" . $this->group_id . "_deny_url", $url);
		}
		
		// Sætter deny url for bruger
		function deny_url_user($url = -1)
		{
			return cms_setting("class_user_" . $this->group_id . "_deny_url_user", $url);
		}
		
		// Sætter default rules, der gælder hvis en bruger er logget ind og ikke har tildelt rettigheder
		function default_rules($rules = -1)
		{
			if ($rules == -1) return cms_setting("class_user_" . $this->group_id . "_default_rules");
			$new_rules = "";
			$lines = split("[\n]", $rules);
			for ($i = 0; $i < count($lines); $i++)
			{
				$line = trim($lines[$i]);
				if ($line != "") $new_rules .= $line . "\r\n";
			}
			cms_setting("class_user_" . $this->group_id . "_default_rules", trim($new_rules));
		}
		
		// Sætter anonym rules, der gælder hvis en bruger ikke er logget ind
		function no_user_rules($rules = -1)
		{
			if ($rules == -1) return cms_setting("class_user_" . $this->group_id . "_no_user_rules");
			$new_rules = "";
			$lines = split("[\n]", $rules);
			for ($i = 0; $i < count($lines); $i++)
			{
				$line = trim($lines[$i]);
				if ($line != "") $new_rules .= $line . "\r\n";
			}
			cms_setting("class_user_" . $this->group_id . "_no_user_rules", trim($new_rules));
		}
		
		// Tjekker regler og returnerer true / false
		function check_rules()
		{
			$rules = "";
			if ($this->logged_in)
			{
				$rules = $this->data["rules"];
				if ($rules == "")
				{
					// Henter regler for gruppe, hvis bruger er tilmeldt
					if (preg_match("/^Brugere/i", $this->group_id) and $this->data["extra_groups"] != "")
					{
						global $_table_prefix;
						$db = new db;
						$rules = $db->execute_field("
							SELECT
								rules
							FROM
								" . $_table_prefix . "_module_" . $this->group_id . "_groups
							WHERE
								'" . $db->escape($this->data["extra_groups"]) . "' LIKE CONCAT('%|', id, '|%') AND
								rules <> ''
							");
					}
				}
				if ($rules == "")
				{					
					// Henter standard regler for bruger
					$rules = cms_setting("class_user_" . $this->group_id . "_default_rules");
				}
			}
			else
			{
				// Henter regler for bruger, der ikke er logget ind
				$rules = cms_setting("class_user_" . $this->group_id . "_no_user_rules");
			}
			if ($rules == "")
			{
				// Ingen regler = tillad alt
				return true;
			}
			
			// Tjekker om kaldt URL = deny url
			if ($this->logged_in)
			{
				$deny_url = $this->deny_url_user();
			}
			else
			{
				$deny_url = $this->deny_url();
			}
			if ($deny_url != "")
			{
				$deny_url = eregi_replace("^" . $_site_url, "", $deny_url);
				
				list($prefix_url, $suffix_url) = split("{URL}", $deny_url);
				if ($prefix_url == substr($_SERVER["REQUEST_URI"], 0, strlen($prefix_url)) &&
					($suffix_url == substr($_SERVER["REQUEST_URI"], -strlen($suffix_url)) || $suffix_url == "")) return true;
			}
			
			// Kaldt URL
			global $vars;
			$module = $vars["module"];
			$page = $vars["page"];
			$do = $vars["do"];
			$id = $vars["id"];
			
			// Tillad boolean
			$allow = false;
			
			// Gennemløber regler
			$lines = explode("\n", $rules);
			for ($i = 0; $i < count($lines); $i++)
			{
				$line = trim($lines[$i]);
				if ($line <> "")
				{
					list($line_allow, $line_element) = explode(" ", $line);
					
					// Tillad?
					$line_allow = (strtoupper($line_allow) == "ALLOW");
					
					// Element
					if (strtoupper($line_element) == "ALL")
					{
						// Alt
						$allow = $line_allow;
					}
					else
					{
						// Bestemt element
						list($line_module, $line_page, $line_do, $line_id) = explode("|", $line_element);
						$ok = false;
						if (($line_module == "*" || $line_module == $module) && ($line_page == "*" || $line_page == $page || ($line_page == "default" && $page == "")) && ($line_do == "*" || $line_do == $do) && ($line_id == "*" || $line_id == $id))
						{
							$allow = $line_allow;
						}
					}
				}
			}
			
			// Tillad?
			return $allow;
		}
		
		// Init-funktion
		function user($group_id = "users", $perform_autologin = true)
		{
			// Tjekker gruppe-ID
			if (!ereg("^[a-zA-Z0-9_-]+$", $group_id)) $group_id = "users";
			
			// Sætter gruppe-ID for brugere
			$this->group_id = $group_id;
			
			// Tjekker tabeller
			$this->check_tables();
			
			// Tjekker om brugeren allerede er logget ind
			$this->check_login();
			
			// Tjekker om der skal foretages autologin
			if ($perform_autologin and !$this->logged_in) $this->check_autologin();
			
			// Tjekker regler
			if ((!ereg("/Admin/", $_SERVER["REQUEST_URI"]) or $group_id == "admin") && !$this->check_rules())
			{
				// Er der angivet en deny url
				if ($this->logged_in)
				{
					$deny_url = $this->deny_url_user();
				}
				else
				{
					$deny_url = $this->deny_url();
				}
				if ($deny_url != "")
				{
					// Erstatter {URL} med aktuel URL
					$deny_url = str_replace("{URL}", urlencode($_SERVER["REQUEST_URI"]), $deny_url);
					
					header("Location: $deny_url");
					exit;
				}
				else
				{
					header("403 Access Denied");
					echo("<html><head><title>403 Access Denied</title></head><body><h1>403 Access Denied</h1>You are not allowed to see this page<br><br><a href=\"javascript:history.back();\">Go back</a></body></html>");
					exit;
				}
			}
			
			// Login forsøg
			global $_table_prefix;
			$db = new db;
			
			// Sletter gamle login-forsøg
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_ip_ban
				WHERE
					time < '" . date("Y-m-d H:i:s", strtotime("-1 hour")) . "' AND
					reason = '_user_login_error_" . $this->group_id . "'
				");
				
			// Antal login fejl
			$this->login_errors = $db->execute_field("
				SELECT			
					COUNT(*)
				FROM
					" . $_table_prefix . "_ip_ban
				WHERE
					ip = '" . $_SERVER["REMOTE_ADDR"] . "' AND
					reason = '_user_login_error_" . $this->group_id . "'					
				");
		}
		
		// Tjekker om bruger-tabellen eksisterer
		function check_tables()
		{
			global $_table_prefix;
			
			// Tjekker tabel
			$db = new db;
			$fields = "";
			reset($this->array_fields);
			while (list($field) = each($this->array_fields)) $fields .= ",`$field`";
			if (!$db->execute("
				SELECT
					id
					$fields
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				LIMIT 1
				"))
			{
				// Opretter tabellen
				$db->execute("
					CREATE TABLE " . $_table_prefix . "_user_" . $this->group_id . "
					(
						id INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
						username VARCHAR( 50 ) NOT NULL ,
						password VARCHAR( 32 ) NOT NULL ,
						create_ip VARCHAR( 15 ) NOT NULL ,
						create_time DATETIME ,
						login_ip VARCHAR( 15 ) NOT NULL ,
						login_time DATETIME ,
						PRIMARY KEY ( id ) ,
						UNIQUE ( username )
					)
					");
					
				// Tjekker felter
				reset($this->array_fields);
				while (list($field, $type) = each($this->array_fields))
				{
					if (!$db->execute("
						SELECT
							$field
						FROM
							" . $_table_prefix . "_user_" . $this->group_id . "
						LIMIT
							1
						"))
					{
						$db->execute("
							ALTER TABLE
								" . $_table_prefix . "_user_" . $this->group_id . "
							ADD
								$field $type
							");
					}
				}
			}
		}
		
		// Tjekker om brugeren allerede er logget ind
		function check_login()
		{
			// Globale variabler
			global $_table_prefix, $_lang_id;
			
			// Tjekker om der er sat en SESSION-værdi med bruger-ID
			if ($_SESSION["_user_id_" . $this->group_id] <> "")
			{
				// Database
				$db = new db;
				
				// Tjekker bruger-ID
				$db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_user_" . $this->group_id . "
					WHERE
						id = '" . intval($_SESSION["_user_id_" . $this->group_id]) . "' AND
						active = '1'
					");
				if ($db->fetch_array())
				{
					// Logget ind
					$this->logged_in = true;
					$this->user_id = $db->array["id"];
					$this->data = $db->array;
					$db->execute("
						UPDATE
							" . $_table_prefix . "_user_" . $this->group_id . "
						SET
							login_ip = '" . $_SERVER["REMOTE_ADDR"] . "',
							login_time = '" . date("Y-m-d H:i:s") . "',
							lang_id = '" . $db->escape($_lang_id) . "'
						WHERE
							id = '" . $this->user_id . "'
						");
					$_SESSION["_user_id_" . $this->group_id] = $this->user_id;
					return true;
				}
				else
				{
					// Ikke logget ind
					$this->logged_in = false;
					$this->user_id = false;
					$this->data = false;
					return false;
				}
			} else {
				// Ikke logget ind
				return false;
			}
		}
		
		// Tjekker for autologin-cookie
		function check_autologin()
		{
			global $_table_prefix;
			
			// Splitter i id og kode
			list($uid, $ucookie) = split("[-]", $_COOKIE["_user_autologin_" . $this->group_id]);
			$uid = intval($uid);
			$ucookie = trim($ucookie);

			// Tjekker id og kode
			if ($uid == 0 and $ucookie == "") return false;
			
			// Forsøger at hente password
			$db = new db;
			$md5_password = $db->execute_field("
				SELECT
					`password`
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					id = '" . intval($uid) . "' AND
					extra_autologincookie = '" . $db->escape($ucookie) . "' AND
					extra_autologincookie <> ''
				");
			
			if ($md5_password == false or $md5_password == "") return false;
			
			// Forsøger at logge ind
			$this->login(false, false, $uid, $md5_password);
		}

		// Sætter eller fjerner autologin-cookie
		function autologin($enabled)
		{
			// Tjekker lige om brugeren er logget ind
			global $_cms_allow_cookies;
			if ($this->logged_in)
			{
				if ($enabled)
				{
					// Laver ny kode til login
					$cookie = "";
					for ($i = 0; $i < 32; $i++)
					{
						$cookie .= chr(rand(48, 122));
					}
					
					// Gemmer kode
					$this->extra_set("autologincookie", $cookie, $this->user_id);
					
					// Sætter autologin-cookie
					if ($_cms_allow_cookies) setcookie("_user_autologin_" . $this->group_id, $this->user_id . "-" . $cookie, time() + 365 * 86400, preg_match("/^\/Admin\//", $_SERVER["REQUEST_URI"]) ? "/Admin/" : "/");
					return true;
				}
				else
				{
					// Fjerner autologin-cookie
					if ($_cms_allow_cookies) setcookie("_user_autologin_" . $this->group_id, "", time(), preg_match("/^\/Admin\//", $_SERVER["REQUEST_URI"]) ? "/Admin/" : "/");
					return true;
				}
			}
			else
			{
				// Så er brugeren ikke logget ind, så kan vi ikke sætte en cookie
				return false;
			}
		}
		
		// Logger bruger ind
		function login($username = false, $password = false, $user_id = false, $user_md5 = false)
		{
			// Globale variabler
			global $_table_prefix;
			
			// Database
			$db = new db;
			
			if ($username <> "" and $password <> "")
			{
				// Log ind med brugernavn og password
				$sql_where = "
					username = '" . $db->escape(trim($username)) . "' AND
					password = '" . md5(trim($password)) . "'
					";
			}
			elseif ($user_id > 0 and $user_md5 <> "")
			{
				// Log ind med auto-cookie
				$sql_where = "
					id = '" . intval($user_id) . "' AND
					password = '" . $db->escape($user_md5) . "'
					";
			}
			else
			{
				return false;
			}
			

			// Antal login fejl
			$this->login_errors = $db->execute_field("
				SELECT			
					COUNT(*)
				FROM
					" . $_table_prefix . "_ip_ban
				WHERE
					ip = '" . $_SERVER["REMOTE_ADDR"] . "' AND
					reason = '_user_login_error_" . $this->group_id . "'					
				");
			if ($this->login_errors >= $this->max_login_errors)
			{
				// Har logget ind for mange gange
				
				add_log_message("Class: user\r\n" .
					"Request: $module | $page | $do | $id\r\n" .
					"To many login errors IP: " . $_SERVER["REMOTE_ADDR"]);
				if ($add_log) $this->add_log($user_id, "FEJL: For mange login forsøg");
				
				return false;
			}
			
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					$sql_where
					AND
					active = '1'
				");
			if ($db->fetch_array())
			{
				// Logget ind
				$this->logged_in = true;
				$this->user_id = $db->array["id"];
				$this->data = $db->array;
				$db->execute("
					UPDATE
						" . $_table_prefix . "_user_" . $this->group_id . "
					SET
						login_ip = '" . $_SERVER["REMOTE_ADDR"] . "',
						login_time = '" . date("Y-m-d H:i:s") . "',
						lang_id = '" . $db->escape($_lang_id) . "'
					WHERE
						id = '" . $this->user_id . "'
					");
				$_SESSION["_user_id_" . $this->group_id] = $this->user_id;
				$this->add_log($this->user_id, "OK: Logget ind");
				
				// Sletter gamle login fejl
				$db->execute("
					DELETE FROM
						" . $_table_prefix . "_ip_ban
					WHERE
						reason = '_user_login_error_" . $this->group_id . "' AND
						ip = '" . $_SERVER["REMOTE_ADDR"] . "'
					");
				
				return true;
			}
			else
			{
				// Ikke logget ind
				global $module, $page, $do, $id;
				add_log_message("Class: user\r\n" .
					"Request: $module | $page | $do | $id\r\n" .
					"Login error user / IP: " . ($username <> "" ? $username : $user_id) . " / " . $_SERVER["REMOTE_ADDR"]);
				if ($add_log) $this->add_log($user_id, "FEJL: Autologin fejlet");
				$this->logged_in = false;
				$this->user_id = false;
				$this->data = false;
				
				// Gemmer login fejl
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_ip_ban
					(
						ip,
						reason,
						time
					)
					VALUES
					(
						'" . $_SERVER["REMOTE_ADDR"] . "',
						'_user_login_error_" . $this->group_id . "',
						'" . date("Y-m-d H:i:s") . "'
					)
					");
				$this->login_errors++;
				
				return false;
			}
		}
		
		// Aktiverer bruger
		function activate($user_id = false)
		{
			global $_table_prefix;
			if (!$user_id)
			{
				$user_id = $this->user_id;
			}
			$db = new db;
			$db->execute("
				UPDATE
					" . $_table_prefix . "_user_" . $this->group_id . "
				SET
					active = '1'
				WHERE
					id = '" . intval($user_id) . "'
				");
		}
		
		// Deaktiverer bruger
		function deactivate($user_id = false)
		{
			global $_table_prefix;
			if (!$user_id)
			{
				$user_id = $this->user_id;
			}
			$db = new db;
			$db->execute("
				UPDATE
					" . $_table_prefix . "_user_" . $this->group_id . "
				SET
					active = '0'
				WHERE
					id = '" . intval($user_id) . "'
				");
		}
		
		// Opdaterer brugerens data
		function update($user_id = false, $data = false)
		{
			global $_table_prefix;
			if (!$user_id)
			{
				$user_id = $this->user_id;
			}
			if (!$data)
			{
				$data = $this->data;
			}
			$db = new db;
			$count = 0;
			reset($data);
			while (list($key, $value) = each($data))
			{
				if ($this->array_fields[$key])
				{
					if ($key == "birthday" and $value == "") $value = "0000-00-00";
					if ($key == "active") $value = (($value == "" or $value == "0") ? "0" : "1");
					if ($key == "rules") $value = trim($value);
					$db->execute("
						UPDATE
							" . $_table_prefix . "_user_" . $this->group_id . "
						SET
							$key = '" . $db->escape($value) . "'
						WHERE
							id = '" . $user_id . "'
						");
					$count += $db->affected_rows();
				}
			}
			if ($count == 0)
			{
				// Ikke opdateret
				return false;
			}
			else
			{
				// Opdateret
				$this->add_log($user_id, "OK: Profil opdateret");
				return true;
			}
		}
		
		// Ændrer brugerens password
		function change_password($new_password, $user_id = false)
		{
			global $_table_prefix;
			// Tjekker $password
			if (!ereg($this->ereg_password, $new_password))
			{
				return "{LANG|Ugyldigt password}";
			}
			// Tjekker bruger-ID
			if (!$user_id) 
			{
				$user_id = $this->user_id;
			}
			// Database objekt
			$db = new db;
			// Opdaterer
			$db->execute("
				UPDATE
					" . $_table_prefix . "_user_" . $this->group_id . "
				SET
					password = '" . md5($new_password) . "'
				WHERE
					id = '" . $user_id . "'
				");
			return true;
		}
		
		// Logger brugeren ud, og sletter evt. autologin-cookies
		function logout()
		{
			global $_cms_allow_cookies;
			if ($this->logged_in)
			{
				// Logger ud
				$this->add_log($this->user_id, "OK: Logget ud");
				if ($_cms_allow_cookies) setcookie("_user_autologin_" . $this->group_id, "", time(), preg_match("/^\/Admin\//", $_SERVER["REQUEST_URI"]) ? "/Admin/" : "/");
				unset($_SESSION["_user_id_" . $this->group_id]);
				$this->logged_in = false;
				$this->user_id = false;
				$this->data = false;
				return true;
			}
			else
			{
				// Brugeren var ikke logget ind
				return false;
			}
		}
		
		// Opretter en bruger, melder fejl ved duplikerede brugernavne
		function create($username, $password)
		{
			// Globale variabler
			global $_table_prefix, $_lang_id;
			// Tjekker $username
			if (!ereg($this->ereg_username, $username))
			{
				return "{LANG|Ugyldigt brugernavn}";
			}
			// Tjekker $password
			if (!ereg($this->ereg_password, $password))
			{
				return "{LANG|Ugyldigt password}";
			}
			// Logger brugeren ud, hvis han er logget ind
			//$this->logout();
			// Forsøger at oprette brugeren
			$db = new db;
			if (!$db->execute("
				INSERT INTO
					" . $_table_prefix . "_user_" . $this->group_id . "
				(username, password, create_ip, create_time, lang_id) VALUES (
					'" . $db->escape($username) . "',
					'" . md5($password) . "',
					'" . $_SERVER["REMOTE_ADDR"] . "',
					'" . date("Y-m-d H:i:s") . "',
					'" . $db->escape($_lang_id) . "'
					)
				"))
			{
				return "{LANG|Brugernavnet er allerede i brug} !";
			}
			else
			{
				// Returnerer brugerens ID
				return $db->insert_id();
			}
		}
		
		// Tjekker om et givent brugernavn er ledigt
		function check_username($username)
		{
			global $_table_prefix;
			$db = new db;
			$db->execute("
				SELECT
					id
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					username = '" . $db->escape($username) . "'
				");
			return ($db->num_rows() == 0);
		}
		
		// Laver liste med ledige brugernavne, der minder om $like
		function find_usernames($like, $count = 5)
		{
			global $_table_prefix;
			$found = 0;
			$usernames = "";
			$id = "";
			$db = new db;
			while ($found < $count)
			{
				// Genererer tilfældigt brugernavn
				$username = $like . $id;
				// Tjekker om brugernavnet er ledigt
				$db->execute("
					SELECT
						id
					FROM
						" . $_table_prefix . "_user_" . $this->group_id . "
					WHERE
						username = '" . $db->escape($username) . "'
					");
				// Tilføjer til liste, hvis den ikke er i brug
				if ($db->num_rows() == 0)
				{
					$usernames .= $username . "\r\n";
					$found++;
				}
				// Finder nyt ID
				$id = intval($id) + 1;
			}
			return $usernames;
		}
		
		// Henter bruger-info og returnerer det som et array
		function get_user($user_id)
		{
			global $_table_prefix;
			// Database-objekt
			$db = new db;
			// Henter bruger
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					id = '" . intval($user_id) . "'
				");
			// Returnerer resultat
			return $db->fetch_array();
		}
		
		// Henter bruger-info ud fra e-mail og returnerer det som et array
		function get_user_from_email($email)
		{
			global $_table_prefix;
			// Database-objekt
			$db = new db;
			// Henter bruger
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					email = '" . $db->escape($email) . "'
				");
			// Returnerer resultat
			return $db->fetch_array();
		}
		
		// Henter bruger-info ud fra brugernavn og returnerer det som et array
		function get_user_from_username($username)
		{
			global $_table_prefix;
			// Database-objekt
			$db = new db;
			// Henter bruger
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					username = '" . $db->escape($username) . "'
				");
			// Returnerer resultat
			return $db->fetch_array();
		}
		
		// Giver mulighed for ekstra felter, der automatisk oprettes
		function extra_set($name, $value, $user_id = false)
		{
			global $_table_prefix;
			if (!$user_id)
			{
				$user_id = $this->user_id;
			}
			// Behandler $name-tag så det kan bruges til tabeller
			if (!ereg("^[a-z_-]+$", $name))
			{
				$tmp = "";
				for ($i = 0; $i < strlen($name); $i++)
				{
					$c = substr($name, $i, 1);
					if (!ereg("^[a-z_-]{1}$", $c))
					{
						$tmp .= "_";
					}
					else
					{
						$tmp .= $c;
					}
				}
				$name = $tmp;
			}
			// Database objekt
			$db = new db;
			// Opdaterer
			if (!$db->execute("
				UPDATE
					" . $_table_prefix . "_user_" . $this->group_id . "
				SET
					extra_" . $name . " = '" . $db->escape($value) . "'
				WHERE
					id = '" . $user_id . "'
				"))
			{
				// Så findes feltet ikke og vi opretter det
				$db->execute("
					ALTER TABLE
						" . $_table_prefix . "_user_" . $this->group_id . "
					ADD
						extra_" . $name . " VARCHAR( 255 ) NOT NULL
					");
				$db->execute("
					UPDATE
						" . $_table_prefix . "_user_" . $this->group_id . "
					SET
						extra_" . $name . " = '" . $db->escape($value) . "'
					WHERE
						id = '" . $user_id . "'
					");
			}
			$this->data["extra_" . $name] = $value;
		}
		
		// Henter et ekstra felt
		function extra_get($name, $user_id = false)
		{
			global $_table_prefix;
			// Behandler $name-tag så det kan bruges til tabeller
			if (!ereg("^[a-z_-]+$", $name))
			{
				$tmp = "";
				for ($i = 0; $i < strlen($name); $i++)
				{
					$c = substr($name, $i, 1);
					if (!ereg("^[a-z_-]{1}$", $c))
					{
						$tmp .= "_";
					}
					else
					{
						$tmp .= $c;
					}
				}
				$name = $tmp;
			}
			if (!$user_id)
			{
				return stripslashes($this->data["extra_" . $name]);
			}
			else
			{
				// Henter for givent bruger-ID
				$db = new db;
				$db->execute("
					SELECT
						extra_" . $name . " AS x
					FROM
						" . $_table_prefix . "_user_" . $this->group_id . "
					WHERE
						id = '" . intval($user_id) . "'
					");
				$db->fetch_array();
				return stripslashes($db->array["x"]);
			}
		}
		
		// Gemmer log over bruger
		function add_log($user_id, $action)
		{
			// Globale variabler
			global $_table_prefix;
			// Tjekker bruger-ID
			if (!$this->get_user($user_id))
			{
				return "{LANG|Ugyldigt bruger-ID} !";
			}
			// Database objekt
			$db = new db;
			// Gemmer i log
			$db->execute("
				INSERT INTO
					" . $_table_prefix . "_user_" . $this->group_id . "_log
				(user_id, time, ip, action) VALUES (
					'" . intval($user_id) . "',
					'" . date("Y-m-d H:i:s") . "',
					'" . $_SERVER["REMOTE_ADDR"] . "',
					'" . $db->escape($action) . "'
					)
				");
			return true;
		}
		
		// Henter log over bruger
		function get_log($user_id)
		{
			global $_table_prefix;
			// Database objekt
			$db = new db;
			// Henter log
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "_log
				WHERE
					user_id = '" . intval($user_id) . "'
				ORDER BY
					id DESC
				");
			return $db;
		}
		
		// Søger efter bruger ud fra søgeord
		function admin_search($searchstring, $start = 0, $limit = 50, $order_by = "username")
		{
			global $_table_prefix;
			// Database objekt
			$db = new db;
			$searchstring = $db->escape($searchstring);
			// Søger efter brugere
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					username LIKE '%$searchstring%' OR
					company LIKE '%$searchstring%' OR
					name LIKE '%$searchstring%' OR
					address LIKE '%$searchstring%' OR
					zipcode LIKE '%$searchstring%' OR
					city LIKE '%$searchstring%' OR
					phone LIKE '%$searchstring%' OR
					mobile LIKE '%$searchstring%' OR
					email LIKE '%$searchstring%' OR
					create_ip LIKE '%$searchstring%' OR
					login_ip LIKE '%$searchstring%'
				ORDER BY
					$order_by
				LIMIT
					$start, $limit
				");
			return $db;
		}
		
		// Returnerer totalt antal brugere
		function admin_get_total($searchstring = "")
		{
			global $_table_prefix;
			// Database objekt
			$db = new db;
			$searchstring = $db->escape($searchstring);
			$db->execute("
				SELECT
					COUNT(*) AS x
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					username LIKE '%$searchstring%' OR
					company LIKE '%$searchstring%' OR
					name LIKE '%$searchstring%' OR
					address LIKE '%$searchstring%' OR
					zipcode LIKE '%$searchstring%' OR
					city LIKE '%$searchstring%' OR
					phone LIKE '%$searchstring%' OR
					mobile LIKE '%$searchstring%' OR
					email LIKE '%$searchstring%' OR
					create_ip LIKE '%$searchstring%' OR
					login_ip LIKE '%$searchstring%'
				");
			$db->fetch_array();
			return $db->array["x"];
		}
		
		// Søger i bruger-log ud fra søgeord
		function admin_search_log($user_id = false, $searchstring, $start = 0, $limit = 50)
		{
			global $_table_prefix;
			// Database objekt
			$db = new db;
			$searchstring = $db->escape($searchstring);
			// Søger efter brugere
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "_log
				WHERE
					user_id = '" . intval($user_id) . "' AND
					(
						time LIKE '%$searchstring%' OR
						ip LIKE '%$searchstring%' OR
						action LIKE '%$searchstring%'
					)
				ORDER BY
					id DESC
				LIMIT
					$start, $limit
				");
			return $db;
		}
		
		// Returnerer totalt antal log for bruger
		function admin_get_total_log($user_id = false, $searchstring = "")
		{
			global $_table_prefix;
			// Database objekt
			$db = new db;
			$searchstring = $db->escape($searchstring);
			$db->execute("
				SELECT
					COUNT(*) AS x
				FROM
					" . $_table_prefix . "_user_" . $this->group_id . "_log
				WHERE
					user_id = '" . intval($user_id) . "' AND
					(
						time LIKE '%$searchstring%' OR
						ip LIKE '%$searchstring%' OR
						action LIKE '%$searchstring%'
					)
				");
			$db->fetch_array();
			return $db->array["x"];
		}
		
		// Sletter bruger
		function admin_delete($user_id)
		{
			global $_table_prefix;
			// Database objekt
			$db = new db;
			// Sletter brugeren
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					id = '" . intval($user_id) . "'
				");
			if ($db->affected_rows() > 0)
			{
				// Sletter log
				$db->execute("
					DELETE FROM
						" . $_table_prefix . "_user_" . $this->group_id . "_log
					WHERE
						user_id = '" . intval($user_id) . "'
					");
				return true;
			}
			else
			{
				return false;
			}
		}
		
		// Ændrer brugerens brugernavn
		function change_username($new_username, $user_id = false)
		{
			global $_table_prefix;
			if (!$user_id)
			{
				$user_id = $this->user_id;
			}
			// Henter gammelt brugernavn
			if (!$user = $this->get_user($user_id)) return false;
			$db = new db;
			// Tjekker $username
			if (!ereg($this->ereg_username, $new_username))
			{
				return "{LANG|Ugyldigt brugernavn}";
			}
			// Tjekker om der allerede findes en bruger med angivne brugernavn
			$db->execute("
				SELECT
					*
				FROM	
					" . $_table_prefix . "_user_" . $this->group_id . "
				WHERE
					id <> '" . intval($user_id) . "' AND
					username = '" . $db->escape($new_username) . "'
				");
			if ($db->fetch_array())
			{
				return "{LANG|Brugernavn allerede i brug}";
			}
			// Gemmer nyt brugernavn
			$db->execute("
				UPDATE
					" . $_table_prefix . "_user_" . $this->group_id . "
				SET
					username = '" . $db->escape($new_username) . "'
				WHERE
					id = '" . intval($user_id) . "'
				");
			// Gemmer gammelt brugernavn i log
			$this->add_log($user_id, "PROFIL: Brugernavn ændret fra '" . $user["username"] . "' til '" . $new_username . "'");
			return true;
		}
		
		// Returnerer array med grupper - undtaget admin
		function admin_get_groups($show_admin = false)
		{
			global $_table_prefix;
			$db = new db;
			$array = array();
			$db->execute("SHOW TABLES LIKE '" . $_table_prefix . "_user_%'");
			while (list($table) = $db->fetch_row())
			{
				if (!ereg("_log$", $table) and (!ereg("_admin$", $table) or $show_admin))
				{
					$array[count($array)] = ereg_replace("^" . $_table_prefix . "_user_", "", $table);
				}
			}
			return $array;
		}
		
		// Fjerner tabeller for bestemt gruppe
		function admin_remove_tables()
		{
			global $_table_prefix;
			$db->execute("DROP TABLE " . $_table_prefix . "_user_" . $this->group_id);
			$db->execute("DROP TABLE " . $_table_prefix . "_user_" . $this->group_id . "_log");
		}
	}
?>
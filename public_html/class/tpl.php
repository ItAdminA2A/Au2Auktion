<?php
	/*
		Template klasse til håndtering af HTML-templates
	*/

	// Array med brugte elementer
	global $_tpl_used_elements, $_tpl_microtime;
	$_tpl_used_elements = array();
	$_tpl_microtime = 0;
	$_tpl_loaded = array();
	$_tpl_no_cache = array();
	$_tpl_preset_settings = array();
	$_tpl_settings_count = 0;
	
	class tpl {
		var $tpl = "";
		var $vars = array();
		
		function tpl($tpl) {
			global $_settings_, $_site_url, $meta, $module, $page, $do, $id, $_cms_js_menu, $_lang_id, $_tpl_microtime, $_tpl_preset_settings, $_tpl_settings_count;
			if ($_tpl_settings_count == count($_settings_))
			{
				$this->vars = $_tpl_preset_settings;
			}
			else
			{
				reset($_settings_);
				while (list($key, $value) = each($_settings_))
				{
					if (preg_match("/^(SITE_|USER_|MENU_)/i", $key)) $this->vars[strtoupper($key)] = $value;
				}
				$_tpl_preset_settings = $this->vars;
				$_tpl_settings_count = count($_settings_);
			}
			$this->vars["SITE_URL"] = $_site_url;
			$this->vars["META"] = $meta;
			$this->vars["MODULE"] = $module;
			$this->vars["PAGE"] = $page;
			$this->vars["DO"] = $do;
			$this->vars["ID"] = $id;
			$this->vars["CMS_JS_MENU"] = $_cms_js_menu;
			$this->vars["LANG_ID"] = $_lang_id;
			$this->tpl = $tpl;
		}
		
		function set($var, $vaerdi) {
			$var = strtoupper($var);
			$var = str_replace("æ", "Æ", $var);
			$var = str_replace("ø", "Ø", $var);
			$var = str_replace("å", "Å", $var);
			$this->vars[$var] = $vaerdi;
		}
		
		function add($var, $vaerdi) {
			$var = strtoupper($var);
			$var = str_replace("æ", "Æ", $var);
			$var = str_replace("ø", "Ø", $var);
			$var = str_replace("å", "Å", $var);
			$this->vars[$var] .= $vaerdi;
		}
		
		// Genererer HTML-kode
		function html() {
			global
				$_tmp_dir,
				$_tpl_dir,
				$_tpl_ext,
				$usr,
				$_document_root,
				$vars,
				$_tpl_time,
				$_tpl_use_cache,
				$_tpl_cache_ttl,
				$db,
				$_table_prefix,
				$module,
				$page,
				$do,
				$id,
				$_settings_,
				$_lang_,
				$_lang_id,
				$_tpl_used_elements,
				$messages,
				$_tpl_microtime,
				$_tpl_loaded,
				$_tpl_no_cache;
				
			// Forbrugt tid
			$_tpl_microtime -= microtime(true);
			
			// Tjekker at element ikke er i brug allerede
			if (!isset($_tpl_used_elements[$this->tpl])) $_tpl_used_elements[$this->tpl] = 0;
			$_tpl_used_elements[$this->tpl]++;
			if ($_tpl_used_elements[$this->tpl] > 10)
			{
				trigger_error("Element løkke registreret (" . $this->tpl . ")");
				return $this->tpl;
			}
			
			// Tjekker template-navn
			$is_tpl = preg_match("/^[\|a-zA-Z0-9_-]+$/", $this->tpl);
			
			// Cache
			$use_cache = false;
			$cache_file = "";
			if ($_tpl_use_cache)
			{
				if ($is_tpl and !isset($_tpl_no_cache[$this->tpl]))
				{				
					// Filnavn på cache-fil
					$cache_file_get = $_tmp_dir . "cache.tpl." . str_replace("|", "_-_", $this->tpl) . "." . md5($_SERVER["QUERY_STRING"]) . ".html";
					$cache_file = $_tmp_dir . "cache.tpl." . str_replace("|", "_-_", $this->tpl) . ".html";
					
					// Tjekker om den findes
					if (is_file($cache_file))
					{
						if (filemtime($cache_file) < time() - $_tpl_cache_ttl)
						{
							// Sletter cache-fil
							@unlink($cache_file);
						}
						else
						{
							return file_get_contents($cache_file);
						}
					}
					elseif (is_file($cache_file_get))
					{
						if (filemtime($cache_file_get) < time() - $_tpl_cache_ttl)
						{
							// Sletter cache-fil
							@unlink($cache_file_get);
						}
						else
						{
							return file_get_contents($cache_file_get);
						}
					}
				}
			}
			
			// Loader template
			if (isset($_tpl_loaded[$this->tpl]))
			{
				$html = $_tpl_loaded[$this->tpl];
			}
			else
			{
				$html = "";
				$tmp_type = "";
				$tmp_dir = "";
				$tmp_tpl = "";
				$tmp_arr = explode("|", $this->tpl);
				$tmp_type = $tmp_arr[0];
				if (isset($tmp_arr[1])) $tmp_dir = $tmp_arr[1];
				if (isset($tmp_arr[2])) $tmp_tpl = $tmp_arr[2];
				if (!$is_tpl)
				{
					// Anvender $this->tpl som template
					$html = $this->tpl;
				}
				elseif ($tmp_type == "LAYOUT" and is_file($_document_root . "/layouts/$tmp_dir/html/" . $tmp_tpl . "." . $_tpl_ext))
				{
					// Bruger template fra layout
					$html = file_get_contents($_document_root . "/layouts/$tmp_dir/html/" . $tmp_tpl . "." . $_tpl_ext);
				}
				elseif ($tmp_type == "MODULE" and is_file($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/module_" . $tmp_dir . "_" . $tmp_tpl . "." . $_tpl_ext))
				{
					// Bruger modul template fra layout
					$html = file_get_contents($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/module_" . $tmp_dir . "_" . $tmp_tpl . "." . $_tpl_ext);
				}
				elseif ($tmp_type == "MODULE" and is_file($_document_root . "/modules/$tmp_dir/html/" . $tmp_tpl . "." . $_tpl_ext))
				{
					// Bruger template fra modul
					$html = file_get_contents($_document_root . "/modules/$tmp_dir/html/" . $tmp_tpl . "." . $_tpl_ext);
				}
				elseif ($module != "" and $tmp_tpl == "" and is_file($_document_root . "/modules/$module/html/cms_" . $this->tpl . "_" . $page . "." . $_tpl_ext))
				{
					// Bruger grund template fra modul
					$html = file_get_contents($_document_root . "/modules/$module/html/cms_" . $this->tpl . "_" . $page . "." . $_tpl_ext);
				}
				elseif ($module != "" and $tmp_tpl == "" and is_file($_document_root . "/modules/$module/html/cms_" . $this->tpl . "." . $_tpl_ext))
				{
					// Bruger grund template fra modul
					$html = file_get_contents($_document_root . "/modules/$module/html/cms_" . $this->tpl . "." . $_tpl_ext);
				}
				elseif ($tmp_tpl == "" and is_file($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/cms_" . $this->tpl . "." . $_tpl_ext))
				{
					// Bruger template fra layout
					$html = file_get_contents($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/cms_" . $this->tpl . "." . $_tpl_ext);
				}
				elseif ($fp = is_file($_tpl_dir . $this->tpl . "." . $_tpl_ext))
				{
					// Bruger almindelig template
					$html = file_get_contents($_tpl_dir . $this->tpl . "." . $_tpl_ext);
				}
				else
				{
					// Anvender $this->tpl som template
					$html = $this->tpl;
				}
				$_tpl_loaded[$this->tpl] = $html;
			}
			
			if ($html != "" and $_tpl_use_cache and !isset($_tpl_no_cache[$this->tpl]))
			{
				// Tjekker om vi skal bruge cache-fil - kun ved GET
				if ($_SERVER["REQUEST_METHOD"] == "GET" and substr($html, 0, 16) == "<!--CACHE_GET-->")
				{
					// Så bruger vi cache
					$html = substr($html, 16);
					$cache_file = $cache_file_get;
					$use_cache = true;
				}
				elseif (substr($html, 0, 12) == "<!--CACHE-->")
				{
					// Så bruger vi cache
					$html = substr($html, 12);
					$use_cache = true;
				}
			}
			
			// Finder andre tags			
			$replace = array();
			while (preg_match("/{[^{^}^\r^\n^\t^'^\"]*}/", $html, $tpl_array))
			{
				$tpl_tag = $tpl_array[0];
				
				// Find ud af hvad vi skal
				$tpl_array = explode("|", substr($tpl_tag, 1, strlen($tpl_tag) - 2));
				
				// Erstat med
				$replace_with = "";
				
				// Finder type
				if (count($tpl_array) == 1)
				{
					// Alm. tag
					if (isset($this->vars[$tpl_array[0]]))
					{
						$replace_with = $this->vars[$tpl_array[0]];
					}
					elseif (preg_match("/^[0-9]+$/", $tpl_array[0]))
					{
						// Få fjerner vi ikke tag
						$replace_with = uniqid("--REPLACE--");
						$replace[$replace_with] = $tpl_tag;
					}
					else
					{
						$replace_with = "";
					}
				}
				elseif ($tpl_array[0] == "TPL")
				{
					// Template
					$tmptpl = "";
					for ($i = 1; $i < count($tpl_array); $i++)
					{
						if ($i > 1) $tmptpl .= "|";
						$tmptpl .= $tpl_array[$i];
					}
					if ($tpl_array[1] == "MODULE")
					{
						$tpl_old_module = $module;
						if (isset($tpl_array[2])) $module = $tpl_array[2];
					}
					$tmp = new tpl($tmptpl);
					$replace_with = $tmp->html();
					if ($tpl_array[1] == "MODULE")
					{
						$module = $tpl_old_module;
					}
				}
				elseif ($tpl_array[0] == "PAGE")
				{
					// Modul
					$tpl_old_html = $html;
					$tpl_old_module = $module;
					$tpl_old_page = $page;
					$tpl_old_do = $do;
					$tpl_old_id = $id;
					$html = "";
					$module = "";
					$page = $tpl_array[1];
					$do = "";
					$id = 0;
					if (isset($tpl_array[2])) $do = $tpl_array[2];
					if (isset($tpl_array[3])) $id = intval($tpl_array[3]);
					include("pages/" . $page . ".php");
					$replace_with = $html;
					$html = $tpl_old_html;
					$module = $tpl_old_module;
					$page = $tpl_old_page;
					$do = $tpl_old_do;
					$id = $tpl_old_id;
				}
				elseif ($tpl_array[0] == "MODULE")
				{
					// Plugin
					$tpl_old_html = $html;
					$tpl_old_module = $module;
					$tpl_old_page = $page;
					$tpl_old_do = $do;
					$tpl_old_id = $id;
					$html = "";
					$module = $tpl_array[1];
					$page = "";
					$do = "";
					$id = 0;
					if (isset($tpl_array[2])) $page = $tpl_array[2];
					if (isset($tpl_array[3])) $do = $tpl_array[3];
					if (isset($tpl_array[4])) $id = intval($tpl_array[4]);
					if ($page == "") $page = "default";
					include($_document_root . "/modules/" . $module . "/pages/" . $page . ".php");
					$replace_with = $html;
					$html = $tpl_old_html;
					$module = $tpl_old_module;
					$page = $tpl_old_page;
					$do = $tpl_old_do;
					$id = $tpl_old_id;
				}
				elseif ($tpl_array[0] == "LANG")
				{
					// Sproglinie
					if (isset($_lang_[$module]) and isset($_lang_[$module][$tpl_array[1]])) $replace_with = $_lang_[$module][$tpl_array[1]];
					if ($replace_with == "" and isset($_lang_[""][$tpl_array[1]])) $replace_with = $_lang_[""][$tpl_array[1]];
					if ($replace_with == "") $replace_with = str_replace("_", " ", $tpl_array[1]);
				}
				elseif ($tpl_array[0] == "VAR")
				{
					// Request variabel (GET / POST)
					if (isset($vars[$tpl_array[1]])) $replace_with = htmlentities($vars[$tpl_array[1]]);
				}
				elseif ($tpl_array[0] == "MESSAGE")
				{
					// Besked variabel - f.eks. fejlmelding fra formular-klassen {MESSAGE|ERROR_FORM_GROUP_FIELD}
					if (isset($messages[$tpl_array[1]])) $replace_with = htmlentities($messages[$tpl_array[1]]);
				}
				elseif ($tpl_array[0] == "DATETIME")
				{
					// Dato og evt. tid
					// {DATETIME|%d-%m-%Y %H:%M:%S}
					$replace_with = htmlentities(strftime($tpl_array[1], time()));
				}
				elseif ($tpl_array[0] == "IF")
				{
					// IF sætning
					// {IF|værdi1|værdi2|sand|falsk}
					if (isset($tpl_array[2]) and isset($tpl_array[3]) and isset($tpl_array[4]))
					{
						$replace_with = ($tpl_array[1] == $tpl_array[2] ? $tpl_array[3] : $tpl_array[4]);
					}
				}
				else
				{
					// Få fjerner vi ikke tag
					$replace_with = uniqid("--REPLACE--");
					$replace[$replace_with] = $tpl_tag;
				}
				
				// Erstatter i HTML
				$html = str_replace($tpl_tag, $replace_with, $html);
			}
			
			// Genskaber ugyldige tags
			reset($replace);
			while (list($from, $to) = each($replace))
			{
				$html = str_replace($from, $to, $html);
			}
			
			// Tjekker om vi bruger cache-filer
			if ($use_cache)
			{
				// Gemmer cache-fil
				file_put_contents($cache_file, "<!--" . date("d-m-Y H:i:s") . "-->" . $html);
			}
			else
			{
				// Denne template bruges ikke til cache
				$_tpl_no_cache[$this->tpl] = true;
			}
			
			// Fjerner fra element liste
			$_tpl_used_elements[$this->tpl]--;
			
			// Forbrugt tid
			$_tpl_microtime += microtime(true);
			
			// Returnerer
			return $html;
		}
		
		// Viser siden
		function view() {
			echo($this->html());
		}
	}
?>
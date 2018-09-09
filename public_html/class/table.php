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
		Klasse:			table
		Version:		26-04-2012
		Beskrivelse:	Tabel-klasse, der kan lave et tabel-layout
	*/
	
	class table
	{
		// Indeholder al HTML-koden
		var $html = "";
		
		// Indeholder array med alle data, undtagen knapper o.l.
		var $data = array();
		
		// Aktuel position
		var $data_col = -1;
		var $data_row = -1;
		
		// Array med [række,kolonne] der skal springes over
		var $data_skip = array();
		
		// Angiver om det er en ny række
		var $newrow = true;
		var $row_id = "";
		var $row_dnd = false;
		// Angiver aktuelt link
		var $link = "";
		// TD-klasser
		var $td_classes = array("td1", "td2");
		var $td_classes_id = 0;
		// Multi-valg
		var $multi_choises = array();
		var $multi_choise_id = false;
		// Tabellen ID
		var $table_id = false;
		
		// Angiver om drag'n'drop af rækker er tilladt
		function row_dnd($enabled = true)
		{
			$this->row_dnd = $enabled;
		}
		
		// Returnerer om nogle rækker skal ændre rækkefølge
		function row_move()
		{
			global $vars;
			if ($vars["_table_submit_" . $this->table_id] != "" &&
				$vars["_table_move_id_" . $this->table_id] != "")
			{
				return array($vars["_table_move_id_" . $this->table_id], $vars["_table_after_id_" . $this->table_id]);
			}
			else
			{
				return false;
			}
		}
			
		// Angiver multi-valg
		function multi_choise($text, $do = "", $confirm = "")
		{
			$i = count($this->multi_choises);
			$this->multi_choises[$i]["text"] = $text;
			$this->multi_choises[$i]["do"] = $do;
			$this->multi_choises[$i]["confirm"] = $confirm;
		}
		
		// Angiver aktuel multi-valg ID
		function multi_choise_id($multi_choise_id = false)
		{
			$this->multi_choise_id = $multi_choise_id;
		}
		
		// Angiver aktuel række ID
		function row_id($row_id = false)
		{
			$this->row_id = $row_id;
		}
		
		// Init-funktion
		function table($table_id = "", $class = "")
		{
			// Laver start af tabel
			global $_site_url;
			$this->table_id = $table_id;
			$tmp = new tpl("_table_header");
			$tmp->set("action", eregi("^" . eregi_replace("^(http|https)://", "", $_site_url) . "/Admin/", $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]) ? ($_site_url . "/Admin/") : $_site_url);
			$tmp->set("table_id", $this->table_id);
			$tmp->set("class", $class);
			$this->html = $tmp->html();
		}
		
		// Angiver link, der anvendes ved felter
		function link($link)
		{
			$this->link = $link;
		}
		
		// Overskrift-felt
		function th($content, $colspan = 1, $rowspan = 1, $class = "")
		{
			$this->startrow(false);
			if ($this->link <> "")
			{
				$tmp = new tpl("_table_th_link");
				$tmp->set("link", $this->link);
			}
			else
			{
				$tmp = new tpl("_table_th");
			}
			$tmp->set("colspan", $colspan);
			$tmp->set("rowspan", $rowspan);
			$tmp->set("class", $class);
			$tmp->set("html", $content);
			$this->html .= $tmp->html();
			
			// Tilføjet til data array
			$this->data_add($content, $colspan, $rowspan);
		}
		
		// Alm. felt
		function tpl($tpl, $content, $colspan = 1, $rowspan = 1, $class = "")
		{
			$this->startrow(false);
			if (count($this->td_classes) > 0)
			{
				$class = $this->td_classes[$this->td_classes_id] . $class;
			}
			if ($this->link <> "")
			{
				$tmp = new tpl("_table_" . $tpl . "_link");
				$tmp->set("link", $this->link);
			}
			else
			{
				$tmp = new tpl("_table_" . $tpl);
			}
			$tmp->set("colspan", $colspan);
			$tmp->set("rowspan", $rowspan);
			$tmp->set("class", $class);
			$tmp->set("html", $content);
			$this->html .= $tmp->html();
			
			// Tilføjet til data array
			$this->data_add($content, $colspan, $rowspan);
		}
		
		// Alm. felt
		function td($content, $colspan = 1, $rowspan = 1, $class = "")
		{
			$this->startrow();
			if (count($this->td_classes) > 0)
			{
				$class = $this->td_classes[$this->td_classes_id] . $class;
			}
			if ($this->link <> "")
			{
				$tmp = new tpl("_table_td_link");
				$tmp->set("link", $this->link);
			}
			else
			{
				$tmp = new tpl("_table_td");
			}
			$tmp->set("colspan", $colspan);
			$tmp->set("rowspan", $rowspan);
			$tmp->set("class", $class);
			$tmp->set("html", $content);
			$this->html .= $tmp->html();
			
			// Tilføjet til data array
			$this->data_add($content, $colspan, $rowspan);
		}
		
		// Knap med valg
		function choise($text, $do = "", $id = "", $confirm = "", $tmp_page = "", $tmp_module = "")
		{
			global $module, $page, $_document_root;
			if ($tmp_module == "") $tmp_module = $module;
			if ($tmp_page == "") $tmp_page = $page;
			$this->startrow();
			// Tjekker for ikon
			ereg("do2=([a-z_]+)", $do, $array) or ereg("([a-z_]+)", $do, $array);
			$icon = $array[1];
			$image = is_file($_document_root . "/img/icon_" . $icon . ".gif") ? "_image" : "";
			if (!$image)
			{
				$array = split("[_]", $icon);
				$icon = $array[0];
				$image = is_file($_document_root . "/img/icon_" . $icon . ".gif") ? "_image" : "";
			}
			if ($confirm <> "")
			{
				$tmp = new tpl("_table_choise_confirm" . $image);
			}
			else
			{
				$tmp = new tpl("_table_choise" . $image);
			}
			$tmp->set("icon", $icon);
			$tmp->set("class", $this->td_classes[$this->td_classes_id]);
			$tmp->set("text", $text);
			$tmp->set("do", $do);
			$tmp->set("id", $id);
			$tmp->set("confirm", $confirm);
			$tmp->set("module", $tmp_module);
			$tmp->set("page", $tmp_page);
			$tmp->set("action", $_SERVER["PHP_SELF"]);
			$this->html .= $tmp->html();
			
			// Tilføjet til data array
			$this->data_add();
		}
		
		// Indsætter start række, hvis den ikke er indsat
		function startrow($allow_multi_choise = true)
		{
			if ($this->newrow)
			{
				// Laver ny række
				$tmp = new tpl("_table_row_header");
				if (!$allow_multi_choise) $tmp->set("class", "nodrag nodrop");
				$tmp->set("table_id", $this->table_id);
				$tmp->set("row_id", $this->row_id);
				$this->html .= $tmp->html();
				$this->newrow = false;
				// Tjekker for multi-valg
				$multi_choise = "";
				if (count($this->multi_choises) > 0)
				{
					// Tjekker om det er en overskrift-række
					if ($allow_multi_choise)
					{
						$tmp = new tpl("_table_multi_choise_row");
						if (!$this->multi_choise_id)
						{
							$tmp->set("disabled", "disabled");
						}
						else
						{
							$tmp->set("id", $this->multi_choise_id);
						}
						$tmp->set("class", $this->td_classes[$this->td_classes_id]);
						$this->html .= $tmp->html();
					}
					else
					{
						$this->th("&nbsp;");
					}
				}
				
				// Tilføjer til data array
				$this->data_row++;
				$this->data[$this->data_row] = array();
				$this->data_col = 0;
				while (isset($this->data_skip[$this->data_row . "," . $this->data_col])) $this->data_col++;
			}
		}
		
		// Afslutter aktuel række
		function endrow()
		{
			$tmp = new tpl("_table_row_footer");
			$tmp->set("table_id", $this->table_id);
			$tmp->set("row_id", $this->row_id);
			$this->html .= $tmp->html();
			$this->newrow = true;
			$this->td_classes_id++;
			if ($this->td_classes_id >= count($this->td_classes))
			{
				$this->td_classes_id = 0;
			}
		}
		
		// Viser tabel
		function html()
		{
			// Tjekker for multi-valg
			if (count($this->multi_choises) > 0)
			{
				// Laver slutning af multi-valg
				$choises = "";
				for ($i = 0; $i < count($this->multi_choises); $i++)
				{
					$tmp = new tpl("_table_multi_choise_choise");
					$tmp->set("text", $this->multi_choises[$i]["text"]);
					$tmp->set("do", $this->multi_choises[$i]["do"]);
					$tmp->set("confirm", $this->multi_choises[$i]["confirm"]);
					$tmp->set("table_id", $this->table_id);
					$choises .= $tmp->html();
				}
				$tmp = new tpl("_table_multi_choise");
				$tmp->set("table_id", $this->table_id);
				$tmp->set("choises", $choises);
				$multi_choises = $tmp->html();
			}
			else
			{
				$multi_choises = "";
			}
			// Henter slutning af tabel
			$tmp = new tpl("_table_footer");
			$tmp->set("table_id", $this->table_id);
			if ($this->row_dnd)
			{
				$tmp1 = new tpl("_table_dnd");
				$tmp1->set("table_id", $this->table_id);
				$tmp->set("dnd", $tmp1->html());
			}
			return $this->html . $multi_choises . $tmp->html();
		}
		
		// Tilføjer data til $this->data array
		function data_add($value = "", $colspan = 1, $rowspan = 1)
		{
			if ($this->data_row < 0) $this->data_row = 0;
			if (!is_array($this->data[$this->data_row])) $this->data[$this->data_row] = array();
			
			$this->data[$this->data_row][$this->data_col] = $value;
			
			for ($c = 1; $c < $colspan; $c++) $this->data_skip[$this->data_row . "," . ($this->data_col + $c)] = true;
			for ($r = 1; $r < $rowspan; $r++) $this->data_skip[($this->data_row + $r) . "," . $this->data_col] = true;
			
			$this->data_col++;
			while (isset($this->data_skip[$this->data_row . "," . $this->data_col]))
			{
				$this->data[$this->data_row][$this->data_col] = "";
				$this->data_col++;
			}
		}
		
		// Returnerer CSV
		function csv()
		{
			$separator = ";";
			$linefeed = "\r\n";
			$csv = "";
			for ($r = 0; $r < count($this->data); $r++)
			{
				for ($c = 0; $c < count($this->data[$r]); $c++)
				{
					$value = $this->data[$r][$c];
					if (strpos(" " . $value, "{") > 0)
					{
						$tmp = new tpl($value);
						$value = $tmp->html();
					}
					$value = str_replace($separator, " ", $value);
					$value = str_replace($linefeed, " ", $value);
					$value = strip_tags($value);
					$csv .= $value;
					if ($c + 1 < count($this->data[$r])) $csv .= $separator;
				}
				
				if ($r + 1 < count($this->data))
				{
					$csv .= $linefeed;
				}
			}
			return $csv;
		}
		
		// Sender header så Excel åbnes med CSV, scriptet stoppes herefter
		function excel($filename = "excel.xls")
		{
			ob_end_clean();
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: attachment; filename=\"" . $filename . "\"");
			echo($this->csv());
			exit;
		}
	}
?>
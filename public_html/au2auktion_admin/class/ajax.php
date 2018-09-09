<?php
	/*
		AJAX klasse, der gør client<->server kommunikation nemmere
	*/

	class ajax
	{
		// Handling ved AJAX-kald
		var $do = false;
		
		// Værdierne returneret ved AJAX-kald
		var $values = array();
		
		// Gruppe
		var $group = false;
		
		// Init
		function ajax($group = false)
		{
			global $module, $page, $do, $id, $vars;
			if (!$group or $group == "") $group = $module . $page . $do . $id;
			$this->group = $group;
			
			// Er der et AJAX-kald?
			if ($vars["_ajax_" . $group . "_submit"] != "")
			{
				// Handling
				$this->do = $vars["_ajax_" . $group . "_do"];
				
				// Gennemløber værdier
				reset($vars);
				while (list($key, $value) = each($vars))
				{
					if (eregi("^_ajax_" . $group . "_([a-z0-9_-]+)$", $key, $array))
					{
						$this->values[$array[1]] = $value;
					}
				}			
			}
		}
		
		// Viser svar til AJAX-kald
		function response($values = array())
		{
			// Tjekker parametre
			if (!is_array($values)) $values = array("response" => $values);
			
			// Bygger XML med værdier
			$cnv = new convert;
			$xml = "";
			reset($values);
			while (list($key, $value) = each($values))
			{
				if (eregi("^[a-z0-9_-]+$", $key) and strlen($value) > 0)
				{
					if (strlen($value) > 1024)
					{
						// Laver til CDATA
						$value = str_replace("]]>", "]]&gt;", $value);
						$value = "<![CDATA[" . $value . "]]>";
					}
					else
					{
						// XML encode						
						$value = $cnv->xmlentities($value);
					}
					
					$tmp = new tpl("_ajax_response_value");
					$tmp->set("key", $key);
					$tmp->set("value", $value);
					$xml .= $tmp->html();
				}
			}
			
			// Bygger XML
			$tmp = new tpl("_ajax_response");
			$tmp->set("values", $xml);
			$xml = $tmp->html();
			
			// Sletter output buffer
			ob_end_clean();
			
			// Sender headers
			header("Content-type: text/xml");
			
			// Sender XML
			echo($xml);
			
			// Stopper script
			exit;
		}
		
		// Viser HTML med javascript etc
		function html()
		{
			// Er det er AJAX-kald?
			if ($this->do != "") $this->response();
			
			global $module, $page, $do, $id, $_site_url;
			
			$tmp = new tpl("_ajax_script");
			$tmp->set("url", eregi("^" . eregi_replace("^(http|https)://", "", $_site_url) . "/Admin/", $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]) ? ($_site_url . "/Admin/") : $_site_url);
			$tmp->set("module", $module);
			$tmp->set("page", $page);
			$tmp->set("do", $do);
			$tmp->set("id", $id);
			$tmp->set("group", $this->group);
			return $tmp->html();
		}
	}
?>
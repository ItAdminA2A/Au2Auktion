<?php
	exit;

	/*
		Direkte links til CSS for grundsystem og moduler
	*/

	if ($do == "css")
	{
		// Ret CSS
		
		if ($vars["mod"] == "")
		{
			// CMS grundsystem
			$title = "{LANG|CMS grundsystem}";
		}
		else
		{
			// Modul
			$title = "{LANG|Modul} " . module2title($vars["mod"]);
		}
		
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Farver / CSS} - $title");
		$html .= $msg->html();

		$links = new links;
		$links->link("Tilbage");
		$html .= $links->html();
				
		include($_document_root . "/Admin/pages/css_editor.php");
		
	}
	else
	{
		// Oversigt over CMS og moduler
			
		// Overskrift
		$msg = new message;
		$msg->title("{LANG|Farver / CSS}");
		$html .= $msg->html();
		
		// Tabel
		$tbl = new table;
		$tbl->th("{LANG|Type}");
		$tbl->th("{LANG|Title}");
		$tbl->th("{LANG|Valg}");
		$tbl->endrow();
		$tbl->td("{LANG|CMS}");
		$tbl->td("{LANG|Grundsystem}");
		$tbl->choise("{LANG|Rediger}", "css", $id);
		$tbl->endrow();

		// Viser moduler der kan oversættes
		$array = admin_module_installed();
		
		for ($i = 0; $i < count($array); $i++)
		{
			if (is_file($_document_root . "/modules/" . $array[$i] . "/css/default.css"))
			{
				$tbl->td("{LANG|Modul}");
				$tbl->td($array[$i]);
				$tbl->choise("{LANG|Rediger}", "css", $id . "&mod=" . $array[$i]);
				$tbl->endrow();
			}
		}
		
		if (count($array) == 0)
		{
			$tbl->td("{LANG|Ingen moduler med CSS-understøttelse}...", 2);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
	}
?>	
<?php
	// Installer opdateringer ?
	if ($usr->extra_get("auto_update") != 1)
	{
		header("Location: ./?page=frameset");
		exit;
	}
	
	$tmp = new tpl("admin_auto_update");
	$html .= $tmp->html();
	
	$tpl = "iframe";
?>
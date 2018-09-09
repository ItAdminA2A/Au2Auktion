<?php
	/*
		Viser samlet CSS for alle moduler
	*/
	
	// Headers
	header("Content-type: text/css");
	header("Expires: " . date("D, j M Y H:i:s", time() + (86400 * 30)) . " CEST");
	header("Cache-Control: Public");
	header("Pragma: Public");
	
	// Inkluderer alle CSS
	$dir = dir(realpath("modules/"));
	while ($m = $dir->read())
	{
		if (is_file("modules/$m/css/default.css"))
		{
			echo(str_replace("{MODULE}", $m, "\r\n" . file_get_contents("modules/$m/css/default.css")));
		}
	}
?>
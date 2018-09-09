<?php
	/*
		Lav backup af hele CMS, modul eller layout - med undtagelse af php-filer
	*/
	
	// Checksum prefix
	$checksum_prefix = "l1os2wrao0ohctfbtyeotrfmf2pd";
	
	// Tillad PHP filer
	$allow_php = ($_SERVER["REMOTE_ADDR"] == gethostbyname("kontor.stadel.dk"));

	$msg = new message;
	$msg->title("{LANG|Backup / Genskab}");
	$html .= $msg->html();
	
	if ($do == "restore")
	{
		// Genskab backup
		
		$links = new links;
		$links->link("Tilbage");
		$html .= $links->html();
		
		$frm = new form;
		$frm->tpl("th", "{LANG|Genskab backup}");
		$frm->file(
			"{LANG|Vælg backup fil}",
			"file",
			true,
			"{LANG|Påkrævet}",
			array("cms")
			);
			
		if ($frm->done())
		{
			$html .= "Genindlæser backup..<br><textarea cols=\"100\" rows=\"25\" style=\"width: 100%;\">";
			
			// Tmp fil
			$tmp_file = $_document_root . "/tmp/admin_backup_restore_" . uniqid("") . ".tmp";
			
			// FTP
			$ftp = new ftp;
			
			// Forbinder
			if (!$ftp->connect($_cms_ftp_server)) die("Kunne ikke forbinde til FTP-server (" . $_cms_ftp_server . ")");
			
			// Logger ind
			if ($ftp->fp and !$ftp->login($_cms_ftp_username, $_cms_ftp_password)) die("Kunne ikke logge på FTP-server (" . $_cms_ftp_username . ")");
			
			// Skifter til rod-mappe
			if ($ftp->fp and $_cms_ftp_root <> "")
			{
				if (!$ftp->chdir($_cms_ftp_root)) die("Kunne ikke skifte til rod-mappe (" . $_cms_ftp_root . ")");
			}
			
			// Åbner backup fil
			if ($fp = fopen($_document_root . $frm->values["file"], "r"))
			{
				$type = "";
				$name = "";
				$mode = "";
				$data = "";
				$checksum = "";
				$datatype = "";
				$readdata = false;
				
				// Læser en linie af gangen
				while ($line = fgets($fp))
				{
					// Trimmer linie
					$line = trim($line);
					
					if (strpos($line, ":") === false) $readdata = true;
					
					if (!$readdata and $line != "--:--")
					{
						// Splitter linie ved :
						list($key, $value) = split("[:]", $line);
						
						if ($key == "TYPE")
						{
							$type = $value;
						}
						elseif ($key == "NAME")
						{
							$name = $value;
						}
						elseif ($key == "MODE")
						{
							$mode = $value;
						}
						elseif ($key == "CHECKSUM")
						{
							$checksum = $value;
						}
						elseif ($key == "DATA")
						{
							$datatype = $value;
						}
					}
					else
					{
						// Læser data indtil enden
						if ($line != "--:--")
						{
							$data .= $line;
						}
						else
						{
							// Slut på data
							$data = trim($data);
							
							// Dekoder evt. data
							if ($datatype != "" and $data != "")
							{
								if ($datatype == "BASE64")
								{
									$data = base64_decode($data);
									if (!$data) die("Fejl i indlæsning af backup !");
								}
							}
							
							if ($type == "FOLDER")
							{
								if ($checksum != "" && strtolower(md5($checksum_prefix . $name)) != $checksum) die("Checksum fejl i backup-fil !");
								$html .= "Opretter mappe $name\r\n";
								$ftp->mkdir($name, $mode);
							}
							elseif ($type == "FILE")
							{
								if ($checksum != "" && strtolower(md5($checksum_prefix . $data)) != $checksum) die("Checksum fejl i backup-fil !");
								$html .= "Opretter fil $name\r\n";
								$ftp->delete($name);
								file_put_contents($tmp_file, $data);
								$ftp->upload($tmp_file, $name, $mode);
								@unlink($tmp_file);
							}
							elseif ($type == "SQL")
							{
								// SQL-kald
								if ($checksum != "" && strtolower(md5($checksum_prefix . $data)) != $checksum) die("Checksum fejl i backup-fil !");
								$html .= "Kører SQL $data\r\n";
								$db->execute($data);
							}
							
							// Nulstiller variabler
							$type = "";
							$name = "";
							$mode = "";
							$data = "";
							$checksum = "";
							$datatype = "";
							$readdata = false;
						}
					}
				}
				
				// Lukker fil
				fclose($fp);
				
				// Lukker ftp
				$ftp->close();
			}
			
			@unlink($tmp_file);
			
			$html .= "</textarea><br><br>Import færdig !<br><br>";
			
			$frm->cleanup();
		}
		
		$html .= $frm->html();
		
	}
	else
	{
		// Backup
		
		$select_backup = array(array("", ""), array("all", "{LANG|Hele siden}"));
		$array = admin_layouts_installed();
		for ($i = 0; $i < count($array); $i++)
		{
			$select_backup[count($select_backup)] = array("layout|" . $array[$i], "{LANG|Layout}: " . $array[$i]);
		}
		$array = admin_module_installed();
		for ($i = 0; $i < count($array); $i++)
		{
			$select_backup[count($select_backup)] = array("module|" . $array[$i], "{LANG|Modul}: " . $array[$i]);
		}
	
		$links = new links;
		$links->link("{LANG|Genskab backup}", "restore");
		$html .= $links->html();
		
		$frm = new form;
		$frm->tpl("th", "{LANG|Lav backup}");
		$frm->select(
			"{LANG|Lav backup af}",
			"backup",
			"",
			"^.+$",
			"{LANG|Påkrævet}",
			"",
			$select_backup
			);
			
		if ($allow_php)
		{
			$frm->checkbox(
				"{LANG|Medtag PHP-filer}",
				"include_php",
				true
				);
		}

		if ($frm->done())
		{
			if ($frm->values["backup"] == "all")
			{
				// Backup af hele siden
				$title = "CMS system";
				$dir = "";
				$mysql = "%";
			}
			elseif (eregi("^layout\|([a-zA-Z_-]+)$", $frm->values["backup"], $array))
			{
				// Backup af layout
				$title = "Layout " . $array[1];
				$dir = "layouts/" . $array[1];
				$mysql = "";
			}
			elseif (eregi("^module\|([a-zA-Z_-]+)$", $frm->values["backup"], $array))
			{
				// Backup af modul
				$title = "Module " . $array[1];
				$dir = "modules/" . $array[1];
				$mysql = $_table_prefix . "_module_" . $array[1] . "_%";
			}
			else
			{
				// Fejl !
				die("ERROR");
			}

			// Sender headers			
			header("Content-type: application/x");
			header("Content-disposition: attachment; filename=\"Backup " . $title . " " . date("d-m-Y") . ".cms\"");
			
			// Fil objekt
			$file = new file;
			
			// Først alle mapper
			$folders = $file->find_folders($_document_root . "/" . $dir, true);
			if ($dir != "") $folders = array_merge(array(""), $folders);
			for ($i = 0; $i < count($folders); $i++)
			{
				$folder = $dir;
				if ($folders[$i] != "") $folder .= "/" . $folders[$i];
				
				echo("TYPE:FOLDER\r\n" .
					"NAME:" . $folder . "\r\n" . 
					"MODE:" . substr(sprintf("%o", fileperms($_document_root . "/" . $folder)), -4) . "\r\n" .
					"CHECKSUM:" . strtolower(md5($checksum_prefix . $folder)) . "\r\n" .
					"--:--\r\n");
			}
			
			// Finder filer
			$files = $file->find_files($_document_root . "/" . $dir, true);
			for ($i = 0; $i < count($files); $i++)
			{
				$file = $dir . "/" . $files[$i];

				if (!eregi("\.php$", $file) or ($allow_php and $frm->values["include_php"] != ""))
				{
					$content = file_get_contents($_document_root . "/" . $file);
					echo("TYPE:FILE\r\n" .
						"NAME:" . $file . "\r\n" . 
						"MODE:" . substr(sprintf("%o", fileperms($_document_root . "/" . $file)), -4) . "\r\n" .
						"DATA:BASE64\r\n" . 
						"CHECKSUM:" . strtolower(md5($checksum_prefix . $content)) . "\r\n" .
						chunk_split(base64_encode($content)) . "\r\n" .
						"--:--\r\n");
				}
			}
			
			// Henter SQL dump
			if ($mysql != "")
			{
				$sqldump = new mysqldump;
				$sqldump->like($mysql);
				$sqls = $sqldump->dump();
				for ($i = 0; $i < count($sqls); $i++)
				{
					echo("TYPE:SQL\r\n" .
						"DATA:BASE64\r\n" .
						"CHECKSUM:" . strtolower(md5($checksum_prefix . $sqls[$i])) . "\r\n" .
						chunk_split(base64_encode($sqls[$i])) . "\r\n" .
						"--:--\r\n");
				}
			}

			// Stop script
			exit;
		}
		
		$html .= $frm->html();
		
		$html .= "
			<script type=\"text/javascript\">
			document.getElementById('_form__form').onsubmit = '';
			</script>
			";
		
	}
?>
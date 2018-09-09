<?php
	/*COPYRIGHT*\
		COPYRIGHT STADEL.DK 2006
		
		AL KODE I DENNE FIL TILH�RER STADEL.DK, THOMAS@STADEL.DK.
		KODEN M� UNDER INGEN  OMST�NDIGHEDER  BENYTTES  TIL ANDET
		FORM�L END  DET DEN ER K�B TIL.  KODEN M� IKKE  �NDRES AF
		ANDRE   END   STADEL.DK.   KODEN  M�  IKKE  S�LGES  ELLER
		VIDEREDISTRIBUERES  HELT, DELVIS ELLER SOM EN KOPI AF DET
		SYSTEM   DET  OPRINDELIGT  ER  K�BT  SAMMEN  MED.  ENHVER
		OVERTR�DELSE  AF EN ELLER FLERE AF DE N�VNTE  BETINGELSER
		VIL RESULTERE I RETSFORF�LGELSE OG ERSTATNING FOR BRUD P�
		OPHAVSRETTEN AF KODEN, IFLG.  DANSK  OPHAVSRETSLOV. DENNE
		COPYRIGHT    MEDDELELSE    M�    DESUDEN    UNDER   INGEN
		OMST�NDIGHEDER FJERNES FRA DENNE FIL.
	
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
		Version:		20-05-2005
		Beskrivelse:	FTP-klasse
	*/
	
	class ftp
	{
		var $passive = true;
		var $server;
		var $user;
		var $pass;
		var $fp;
		
		function ftp() {
			$this->fp = false;
		}
		
		function passive($passive)
		{
			$this->passive = $passive;
		}
		
		function connect($server, $port = 21) {
			$this->server = $server;
			// Fors�ger at forbindelse til serveren
			$this->fp = ftp_connect($server, $port);
			if ($this->fp) {
				return true;
			} else {
				return false;
			}
		}
		
		function login($user, $pass) {
			$this->user = $user;
			$this->pass = $pass;
			
			// Tjekker forbindelsen
			if (!$this->fp) {
				return false;
			}

			// Fors�ger at logge ind
			$res = ftp_login($this->fp, $user, $pass);
			
			// Tjekker om det gik godt
			if ($res)
			{
				if ($this->passive)
				{
					// Skifter til passive
					ftp_pasv($this->fp, true);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function close() {
			// Lukker forbindelsen
			ftp_close($this->fp);
			$this->fp = false;
			
			return true;
		}
		
		function chdir($dir)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Skifter mappe
			return ftp_chdir($this->fp, $dir);
		}
		
		function current_dir()
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Henter aktuel mappe
			return ftp_pwd($this->fp);
		}
		
		function chmod($file, $mode)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;

			// Skifter rettigheder
			return ftp_site($this->fp, "chmod $mode $file");
		} 
		
		function mkdir($dir, $mode = false)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Laver ny mappe
			if (ftp_mkdir($this->fp, $dir))
			{
				if ($mode <> false)
				{
					// Fors�ger at �ndre mode p� mappe
					$this->chmod($dir, $mode);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function curdir()
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;

			// Henter aktuel mappe
			return ftp_pwd($this->fp);			
		}
				
		function delete($file)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Sletter fil
			return ftp_delete($this->fp, $file);
		}
		
		function rmdir($folder)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Sletter mappe
			return ftp_rmdir($this->fp, $folder);
		}
		
		function download($remote, $local)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Laver lokal fil
			if (!$fp = fopen($local, "w")) return false;
			
			// Downloader fil
			$res = ftp_fget($this->fp, $fp, $remote, FTP_BINARY);
			
			// Lukker lokal fil
			fclose($fp);
			
			// Retur
			return $res;
		}
		
		function upload($local, $remote, $mode = false)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// �bner lokal fil
			if (!$fp = fopen($local, "r")) return false;
			
			// Uploader fil
			$res = ftp_fput($this->fp, $remote, $fp, FTP_BINARY);
			
			// Lukker lokal fil
			fclose($fp);
			
			// Tjekker om der skal s�ttes bestemt mode p�
			if ($mode <> false)
			{
				$this->chmod($remote, $mode);
			}
			
			// Retur
			return $res;
		}
		
		function files($folder = ".", $include_folders = false)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;

			// Henter liste
			$tmplist = ftp_nlist($this->fp, $folder);
			$list = array();
			for ($i = 0; $i < count($tmplist); $i++)
			{
				// Tjekker st�rrelse p� filer
				if ($this->filesize($tmplist[$i]) > -1)
				{
					$list[count($list)] = $tmplist[$i];
				}
				elseif ($include_folders)
				{
					// S� er det en mappe, hvor vi ogs� skal s�ge efter filer
					
					// Skifter til mappe
					if ($this->chdir($tmplist[$i]))
					{
						// Henter filer i mappe
						$list2 = $this->files(".", true);
						// Tilf�jer mappe-struktur til filer
						for ($i2 = 0; $i2 < count($list2); $i2++)
						{
							$list[count($list)] = $tmplist[$i] . "/" . $list2[$i2];
						}
						// Skifter tilbage til aktuel mappe
						$this->chdir("../");
					}
				}
			}
			
			return $list;
		}
		
		function dirs($folder = ".")
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Henter liste
			$tmplist = ftp_nlist($this->fp, $folder);
			$list = array();
			for ($i = 0; $i < count($tmplist); $i++)
			{
				// Tjekker st�rrelse p� filer
				if ($this->filesize($tmplist[$i]) == -1)
				{
					$list[count($list)] = $tmplist[$i];
				}
			}
			
			return $list;
		}
		
		function filesize($file)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Henter filst�rrelse
			return ftp_size($this->fp, $file);
		}
		
		function download_dir($remote = ".", $local = ".")
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Tjekker lokal mappe
			if (!is_dir($local)) return false;
			
			// Aktuel mappe
			$curdir = $this->curdir();
			
			// Skifter til mappe
			if (!$this->chdir($remote)) return false;
			
			// Henter filer herunder
			$dirs = $this->dirs(".", true);
			$files = $this->files(".", true);
			
			// Skifter til gammel mappe
			$this->chdir($curdir);
			
			// Genneml�ber mapper og opretter disse lokalt
			for ($i = 0; $i < count($dirs); $i++)
			{
				mkdir($local . "/" . $dirs[$i]);
			}
			
			// Genneml�ber filer
			for ($i = 0; $i < count($files); $i++)
			{
				// Downloader fil
				$this->download($curdir . "/" . $files[$i], $local . "/" . $files[$i]);
			}
			
			return true;
		}
		
		function upload_dir($local = ".", $remote = ".", $mode = false)
		{
			// Tjekker forbindelse
			if (!$this->fp) return false;
			
			// Tjekker lokal mappe
			if (!is_dir($local)) return false;
			
			// Henter filer i lokal mappe
			$file = new file;
			$dirs = $file->find_folders($local, true);
			$files = $file->find_files($local, true);

			// Genneml�ber mapper og opretter disse p� server
			for ($i = 0; $i < count($dirs); $i++)
			{
				$this->mkdir($remote . "/" . $dirs[$i], $mode);
			}
			
			// Genneml�ber filer
			for ($i = 0; $i < count($files); $i++)
			{
				// Uploader fil
				$this->upload($local . "/" . $files[$i], $remote . "/" . $files[$i], $mode);
			}
			
			return true;
		}
		
	}
?>
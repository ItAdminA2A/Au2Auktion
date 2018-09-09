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
		Klasse:				file
		Version:			14-01-2005
		Beskrivelse:		Klasse til behandling af filer
	*/

	//
	// Funktioner til at kopiere og slette mapper
	//

	class file
	{	
		function find_folders($xpath, $sub_folders = false) {
			$fuldpath = realpath($xpath);
			
			if (!is_readable($fuldpath)) return array();
	
			//
			// Finder alle mapper i aktuel mappe
			//
			$mapper = array();
			$antal_mapper = 0;
			$mappe = dir($fuldpath);
			while ($fil = $mappe->read()) {
				if (is_dir($fuldpath . "/" . $fil) and $fil <> "." and $fil <> "..") {
					$mapper[count($mapper)] = $fil;
					if ($sub_folders)
					{
						$filer2 = $this->find_folders($fuldpath . "/" . $fil, true);
						for ($i = 0; $i < count($filer2); $i++)
						{
							$mapper[count($mapper)] = $fil . "/" . $filer2[$i];
						}
					}
				}
			}
			sort($mapper);
			return $mapper;
		}
	
		function find_files($xpath, $sub_folders = false) {
			$fuldpath = realpath($xpath);
			
			if (!is_readable($fuldpath)) return array();
	
			//
			// Finder alle php- og asp-filer i aktuel mappe
			//
			$filer = array();
			if ($mappe = dir($fuldpath)) {
				while ($fil = $mappe->read()) {
					if (is_file($fuldpath . "/" . $fil)) {
						$filext = split("[.]", $fil);
						$filext = $filext[sizeof($filext) - 1];
						$filext = strtolower($filext);
						if ($filext <> ".." and $filext <> ".") {
							$filer[count($filer)] = $fil;
						}
					}
					elseif ($sub_folders and $fil <> "." and $fil <> "..")
					{
						$filer2 = $this->find_files($fuldpath . "/" . $fil, true);
						for ($i = 0; $i < count($filer2); $i++)
						{
							$filer[count($filer)] = $fil . "/" . $filer2[$i];
						}
					}
				}
				sort($filer);
			}
			return $filer;
		}
	
		function copy_folder($path_fra, $path_til) {
			// Tjekker om mappen findes, som der skal kopieres til
			if ($path_til <> "") {
				if (!is_dir($path_til)) {
					// Forsøger at lave mappen
					if (substr($path_til, -1) == "/") {
						$path_til = substr($path_til, 0, strlen($path_til) - 1);
					}
					$pos = strrpos($path_til, "/");
					$fuldpath_til = realpath(substr($path_til, 0, $pos)) . substr($path_til, $pos);
					mkdir($fuldpath_til, 0777);
				}
			}
			// Finder filer, der skal kopieres
			$fuldpath_fra = realpath($path_fra);
			$fuldpath_til = realpath($path_til);
			$filer = $this->find_files($fuldpath_fra);
			for ($x = 0; $x < count($filer); $x++) {
				// Kopierer filen
				copy($fuldpath_fra . "/" . $filer[$x], $fuldpath_til . "/" . $filer[$x]);
			}
			$mapper = $this->find_folders($fuldpath_fra);
			for ($x = 0; $x < count($mapper); $x++) {
				$this->copy_folder($fuldpath_fra . "/" . $mapper[$x], $fuldpath_til . "/" . $mapper[$x]);
			}
		}
		
		function delete_folder($path) {
			$fuldpath = realpath($path);
			$filer = $this->find_files($fuldpath);
			for ($x = 0; $x < count($filer); $x++) {
				// Sletter filen
				unlink($fuldpath . "/" . $filer[$x]);
			}
			$mapper = $this->find_folders($fuldpath);
			for ($x = 0; $x < count($mapper); $x++) {
				// Sletter mappen
				$this->delete_folder($fuldpath . "/" . $mapper[$x]);
			}
			// Sletter start-mappen
			rmdir($fuldpath);
		}
		
		function get_folder_size($path) {
			$bytes = 0;
			$fuldpath = realpath($path);
			$filer = $this->find_files($fuldpath);
			for ($x = 0; $x < count($filer); $x++) {
				$bytes += filesize($fuldpath . "/" . $filer[$x]);
			}
			$mapper = $this->find_folders($fuldpath);
			for ($x = 0; $x < count($mapper); $x++) {
				$bytes += $this->get_folder_size($fuldpath . "/" . $mapper[$x]);
			}
			return $bytes;
		}
		
		function size_formatted($file)
		{
			$size = filesize($file);
			if ($size >= 1024*1024*1024)
			{
				// Gb
				return number_format($size / (1024*1024*1024), 2, ",", ".") . " GB";
			}
			elseif ($size >= 1024*1024)
			{
				// Mb
				return number_format($size / (1024*1024), 2, ",", ".") . " MB";
			}
			elseif ($size >= 1024)
			{
				// Kb
				return number_format($size / (1024), 2, ",", ".") . " KB";
			}
			else
			{
				// B
				return number_format($size, 0, ",", ".") . " byte";
			}
		}
	}
?>
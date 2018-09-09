<?php
	/*
		Version:		04-04-2005
		Beskrivelse:	Kan åbne og læse CSV og konvertere til array, f.eks.:
						$a[0][0] = række 1, kolonne 1
						$a[0][1] = række 1, kolonne 2
						$a[1][0] = række 2, kolonne 1
						...osv...
	*/
	
	class csv
	{
		// Separator
		var $separator = ";";
		
		// Linieskift
		var $linefeed = "\r\n";
		
		// Tekst-afgrænser
		var $text_separator = "\"";
		
		// Indeholder data
		var $data = "";

		// Gemmer linieskift
		function linefeed($linefeed)
		{
			$this->linefeed = $linefeed;
		}
				
		// Gemmer tekst-separator
		function text_separator($text_separator)
		{
			$this->text_separator = $text_separator;
		}
				
		// Gemmer separator
		function separator($separator)
		{
			$this->separator = $separator;
		}
				
		// Gemmer data
		function data($data)
		{
			$this->data = $data;
		}
		
		// Laver array med indhold fra fil
		function get_array()
		{
			// Tjekker separatorer
			if (strlen($this->separator) <= 0) return;
			if (strlen($this->linefeed) <= 0) return;
			
			// Array
			$array = array();
			
			// Henter data
			$data = $this->data;
			
			// Linier og kolonne
			$line_i = 0;
			$col_i = 0;
			
			// Bool
			$tag_open = false;
			
			// Gennemløber tegn for tegn
			for ($i = 0; $i < strlen($data); $i++)
			{
				if (!is_array($array[$line_i])) $array[$line_i] = array();
				if (!isset($array[$line_i][$col_i])) $array[$line_i][$col_i] = "";
				
				// Åbn / luk tag
				if (strlen($this->text_separator) > 0 and substr($data, $i, strlen($this->text_separator)) == $this->text_separator)
				{
					$tag_open = !$tag_open;
					$i += strlen($this->text_separator) - 1;
				}
				
				// Værdi i tag
				elseif ($tag_open)
				{
					$array[$line_i][$col_i] .= substr($data, $i, 1);
				}
				
				// Linieskift
				elseif (substr($data, $i, strlen($this->linefeed)) == $this->linefeed)
				{
					$line_i++;
					$col_i = 0;
				}
				
				// Kolonneskift
				elseif (substr($data, $i, strlen($this->separator)) == $this->separator)
				{
					$col_i++;
				}
				
				// Værdi i tag
				else
				{
					$array[$line_i][$col_i] .= substr($data, $i, 1);
				}
			}
			
			// Returnerer array
			return $array;
		}
	}
?>
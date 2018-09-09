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
		Beskrivelse:	Klasse til billedebehandling
		19-12-2006:		Tilføjet allokering ud fra HTML-farve kode, f.eks. "#ff0000"
	*/

	class image
	{
		//
		// Funktioner til billed-behandling
		//
		
		// Buffer af billede-info
		var $buffer = array();
		
		// Allokerer farve ud fra HTML-farve kode
		function imagehtmlcolorallocate($img, $html_color)
		{
			if (substr($html_color, 0, 1) <> "#") $html_color = "#" . $html_color;
			$html_color = hexdec(str_replace("#", "0x", $html_color));
			$html_color = array(
				"r" => 0xFF & ($html_color >> 0x10),
				"g" => 0xFF & ($html_color >> 0x8),
				"b" => 0xFF & $html_color
				);
			return imagecolorallocate($img, $html_color["r"], $html_color["g"], $html_color["b"]);
		}
		
		// Allokerer farven mellem to andre farver
		function imagecolorallocatemiddle($img, $color1, $color2)
		{
			// Finder r, g, b for color1
			$a1 = imagecolorsforindex($img, $color1);
			$r1 = $a1["red"];
			$g1 = $a1["green"];
			$b1 = $a1["blue"];
			// Finder r, g, b for color2
			$a2 = imagecolorsforindex($img, $color2);
			$r2 = $a2["red"];
			$g2 = $a2["green"];
			$b2 = $a2["blue"];
			// Finder mellem-farve mellem color1 og color2
			$r = $r1 + round(($r2 - $r1) / 2);
			$g = $g1 + round(($g2 - $g1) / 2);
			$b = $b1 + round(($b2 - $b1) / 2);
			// Allokerer farve og returnerer index for denne
			return imagecolorallocate($img, $r, $g, $b);			
		}
	
		// Afrunder hjørnerne på et billede
		function imagecornerround($img, $size, $r = -1, $g = -1, $b = -1)
		{
			// Finder bredde og højde på billedet
			$w = imagesx($img);
			$h = imagesy($img);
			// Tjekker om der er angivet en farve eller et farveindex
			if ($r == -1)
			{
				$color = imagecolorallocate($img, 255, 255, 255);
			}
			elseif ($g == -1)
			{
				$color = $r;
			}
			else
			{
				$color = imagecolorallocate($img, $r, $g, $b);
			}
			// Laver array med koordinater
			$cirkel_x = array();
			$cirkel_y = array();
			$pixel_x = array();
			$pixel_y = array();
			for ($y = 0; $y < $size; $y++)
			{
				$max_x = intval($size * (1 - sin(acos(1 - $y / $size))));
				$i = count($cirkel_x);
				$cirkel_x[$i] = $max_x;
				$cirkel_y[$i] = $y;
				$i = count($pixel_x);
				for ($x = 0; $x < $max_x; $x++) {
					$pixel_x[$i] = $x;
					$pixel_y[$i] = $y;
					$i++;
				}
			}
			// Laver bløde rundinger
			for ($i = 0; $i < count($cirkel_x); $i++)
			{
				// Top, venstre
				$mid_color = imagecolorallocatemiddle($img, imagecolorat($img, $cirkel_x[$i], $cirkel_y[$i]), $color);
				imagesetpixel($img, $cirkel_x[$i], $cirkel_y[$i], $mid_color);
				// Top, højre
				$mid_color = imagecolorallocatemiddle($img, imagecolorat($img, $w - $cirkel_x[$i] - 1, $cirkel_y[$i]), $color);
				imagesetpixel($img, $w - $cirkel_x[$i] - 1, $cirkel_y[$i], $mid_color);
				// Bund, venstre
				$mid_color = imagecolorallocatemiddle($img, imagecolorat($img, $cirkel_x[$i], $h - $cirkel_y[$i] - 1), $color);
				imagesetpixel($img, $cirkel_x[$i], $h - $cirkel_y[$i] - 1, $mid_color);
				// Bund, højre
				$mid_color = imagecolorallocatemiddle($img, imagecolorat($img, $w - $cirkel_x[$i] - 1, $h - $cirkel_y[$i] - 1), $color);
				imagesetpixel($img, $w - $cirkel_x[$i] - 1, $h - $cirkel_y[$i] - 1, $mid_color);
			}
			// Farve andre pixels
			for ($i = 0; $i < count($pixel_x); $i++)
			{
				// Top, venstre
				imagesetpixel($img, $pixel_x[$i], $pixel_y[$i], $color);
				// Top, højre
				imagesetpixel($img, $w - $pixel_x[$i] - 1, $pixel_y[$i], $color);
				// Bund, venstre
				imagesetpixel($img, $pixel_x[$i], $h - $pixel_y[$i] - 1, $color);
				// Bund, højre
				imagesetpixel($img, $w - $pixel_x[$i] - 1, $h - $pixel_y[$i] - 1, $color);
			}
			return $img;
		}
		
		// Laver et postet billede om til en image-variabel, tjekker desuden om det er et kendt
		// format, herunder JPG, PNG eller GIF
		function imagecreatefrompost($name)
		{
			global $_tmp_dir;

			// Tjekker om det er et uploadet billede
			if (!is_uploaded_file($_FILES[$name]["tmp_name"])) return false;
			
			// Finder filnavn og endelse
			$filnavn = $_FILES[$name]["name"];
			$arr = false;
			eregi("\.[a-z]+$", $filnavn, $arr);
			$filext = strtolower($arr[0]);
			
			// Tjekker om det er et JPG-, GIF- eller PNG-billede
			if ($filext <> ".jpg" and $filext <> ".jpeg" and $filext <> ".png" and $filext <> ".gif") return false;
			
			// Flytter til tmp-mappe
			$tmpfilename = $_tmp_dir . uniqid("") . $filext;
			if (!move_uploaded_file($_FILES[$name]["tmp_name"], $tmpfilename)) return false;
			
			// Tjekker om vi kan åbne billedet
			if ($filext == ".png")
			{
				$img = imagecreatefrompng($tmpfilename);
			}
			elseif ($filext == ".gif" and function_exists("imagecreatefromgif"))
			{
				$img = imagecreatefromgif($tmpfilename);
			}
			else
			{
				$img = imagecreatefromjpeg($tmpfilename);
			}
			
			// Sletter tmp-fil
			unlink($tmpfilename);
			
			// Retur
			return $img;
		}
		
		// Laver miniature ud fra billede, med maks-mål x og y
		function imagemaxsize($img, $x, $y)
		{
			$img_x = imagesx($img);
			$img_y = imagesy($img);
			$ny_x = $img_x;
			$ny_y = $img_y;
			if ($ny_x > $x)
			{
				$ny_x = $x;
				$ny_y = round($ny_x / $img_x * $img_y);
			}
			if ($ny_y > $y)
			{
				$ny_y = $y;
				$ny_x = round($ny_y / $img_y * $img_x);
			}
			if ($ny_x < $img_x or $ny_y < $img_y)
			{
				// GD Lib 2.x
				if ($img2 = @imagecreatetruecolor($ny_x, $ny_y))
				{
					// Gennemsigtig
					$color_trans = imagecolortransparent($img);
					if ($color_trans >= 0)
					{
						$color_trans = imagecolorsforindex($img, $color_trans);
						$color_trans2 = imagecolorallocatealpha($img2, $color_trans['red'], $color_trans['green'], $color_trans['blue'], 127);
					}
					else
					{
						$color_trans2 = imagecolorallocatealpha($img2, 127, 127, 127, 127);
					}
					imagecolortransparent($img2, $color_trans2);
					imagefill($img2, 0, 0, $color_trans2);
					imagesavealpha($img2, true);
					
					imagecopyresampled($img2, $img, 0, 0, 0, 0, $ny_x, $ny_y, $img_x, $img_y);
				}
				else
				{
					// GD Lib 1.x
					$img2 = imagecreate($ny_x, $ny_y);
					imagecopyresized($img2, $img, 0, 0, 0, 0, $ny_x, $ny_y, $img_x, $img_y);
				}
				$img = $img2;
				$img_x = $ny_x;
				$img_y = $ny_y;
			}
			return $img;
		}
	
		// Laver miniature ud fra billede, med målene x og y
		function imagesize($img, $x, $y, $r = -1, $g = -1, $b = -1)
		{
			$img = $this->imagemaxsize($img, $x, $y);
			$img_x = imagesx($img);
			$img_y = imagesy($img);
			if ($img_x < $x or $img_y < $y)
			{
				if ($img2 = @imagecreatetruecolor($x, $y))
				{
					// GD Lib 2.x
					$gdlib = 2;
					
					// Gennemsigtig
					$color_trans = imagecolortransparent($img);
					if ($color_trans >= 0 and $r == -1 and $g == -1 and $b == -1)
					{
						$color_trans = imagecolorsforindex($img, $color_trans);
						$color_trans2 = imagecolorallocatealpha($img2, $color_trans['red'], $color_trans['green'], $color_trans['blue'], 127);
					}
					else
					{
						if ($r == -1) $r = 127;
						if ($g == -1) $g = 127;
						if ($b == -1) $b = 127;
						$color_trans2 = imagecolorallocatealpha($img2, $r, $g, $b, 127);
					}
					imagecolortransparent($img2, $color_trans2);
					imagefill($img2, 0, 0, $color_trans2);
					imagesavealpha($img2, true);
				}
				else
				{
					// GD Lib 1.x
					$gdlib = 1;
					$img2 = imagecreate($x, $y);
					
					// Tjekker om der er angivet en farve eller et farveindex
					if ($r == -1)
					{
						$color = imagecolorallocate($img2, 255, 255, 255);
					}
					elseif ($g == -1)
					{
						$color = $r;
					}
					else
					{
						$color = imagecolorallocate($img2, $r, $g, $b);
					}
					
					// Fylder billedets baggrund med den angivne farve
					imagefilledrectangle($img2, 0, 0, $x, $y, $color);
				}
				imagecopy($img2, $img, round(($x - $img_x) / 2), round(($y - $img_y) / 2), 0, 0, $img_x, $img_y);
				$img = $img2;
				$img_x = $x;
				$img_y = $y;
			}
			return $img;
		}
		
		// Laver miniature ud fra billede, med målene x og y, men "klipper" det ud i stedet for
		// at lave en fyld-farve omkring
		function imagesizecut($img, $x, $y)
		{
			$img_x = imagesx($img);
			$img_y = imagesy($img);
			if ($img_x / $x < $img_y / $y)
			{
				// x > y
				$faktor = $img_x / $x;
				$src_x = 0;
				$src_y = round(($img_y - $faktor * $y) / 2);
			}
			else
			{
				// y >= x
				$faktor = $img_y / $y;
				$src_x = round(($img_x - $faktor * $x) / 2);
				$src_y = 0;
			}
			$src_w = round($faktor * $x);
			$src_h = round($faktor * $y);
			// GD Lib 2.x
			if ($img2 = @imagecreatetruecolor($x, $y))
			{
				// Gennemsigtig
				$color_trans = imagecolortransparent($img);
				if ($color_trans >= 0)
				{
					$color_trans = imagecolorsforindex($img, $color_trans);
					$color_trans2 = imagecolorallocatealpha($img2, $color_trans['red'], $color_trans['green'], $color_trans['blue'], 127);
				}
				else
				{
					$color_trans2 = imagecolorallocatealpha($img2, 127, 127, 127, 127);
				}
				imagecolortransparent($img2, $color_trans2);
				imagefill($img2, 0, 0, $color_trans2);
				imagesavealpha($img2, true);
				
				imagecopyresampled($img2, $img, 0, 0, $src_x, $src_y, $x, $y, $src_w, $src_h);
			}
			else
			{
				// GD Lib 1.x
				$img2 = imagecreate($x, $y);
				imagecopyresized($img2, $img, 0, 0, $src_x, $src_y, $x, $y, $src_w, $src_h);
			}
			$img = $img2;
			return $img;
		}
		
		// Åbner billede fra fil - forsøger alle filtyper
		function imagecreatefromfile($filename)
		{
			if ($img = imagecreatefromjpeg($filename)) return $img;
			if ($img = imagecreatefrompng($filename)) return $img;
			if ($img = imagecreatefromgif($filename)) return $img;
			return false;
		}
		
		// Returnerer pixel bredde på et billede
		function width($filename)
		{
			if (!$this->buffer[$filename]["width"])
			{
				if ($img = $this->imagecreatefromfile($filename))
				{
					$this->buffer[$filename]["width"] = imagesx($img);
					$this->buffer[$filename]["height"] = imagesy($img);
				}
				else
				{
					$this->buffer[$filename]["width"] = 0;
					$this->buffer[$filename]["height"] = 0;
				}
			}
			return $this->buffer[$filename]["width"];
		}
		
		// Returnerer pixel højde på et billede
		function height($filename)
		{
			if (!$this->buffer[$filename]["height"])
			{
				if ($img = $this->imagecreatefromfile($filename))
				{
					$this->buffer[$filename]["width"] = imagesx($img);
					$this->buffer[$filename]["height"] = imagesy($img);
				}
				else
				{
					$this->buffer[$filename]["width"] = 0;
					$this->buffer[$filename]["height"] = 0;
				}
			}
			return $this->buffer[$filename]["height"];
		}
		
		// Returnerer pixel størrelse på et billede formatteret
		function size_formatted($filename, $error_text = "?x? pixel")
		{
			$width = $this->width($filename);
			$height = $this->height($filename);
			if ($width > 0 and $height > 0)
			{
				return $width . "x" . $height . " pixel";
			}
			else
			{
				return $error_text;
			}
		}
		
		// Returnerer pixel bredde på et billede
		function width_formatted($filename, $error_text = "? pixel")
		{
			$width = $this->width($filename);
			if ($width > 0)
			{
				return $width . " {LANG|pixel}";
			}
			else
			{
				return $error_text;
			}
		}
		
		// Returnerer pixel højde på et billede
		function height_formatted($filename, $error_text = "? pixel")
		{
			$height = $this->height($filename);
			if ($height > 0)
			{
				return $height . " {LANG|pixel}";
			}
			else
			{
				return $error_text;
			}
		}
		
		// Sletter buffer med billede-størrelser
		function clear_buffer()
		{
			$this->buffer = array();
		}		
	}
?>
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
		Beskrivelse:	Kan vise en graf ud fra givne data
		19-12-2006:		Først udgave, kan vise x-y graf, kun kurver
		20-12-2006:		Overfører data via SESSION variabler
		14-11-2008:		Mulighed for visning som søjler
	*/

	class graph
	{
		// Type af graf: curve, column
		var $type = "column";
		
		// Bredde og højde
		var $width = 500;
		var $height = 400;
		
		// Baggrund - kan også være et billede
		var $background = "#ffffff";
		
		// Margin til kant og mellem tekst og linier
		var $margin = 10;
		
		// Størrelse på pil
		var $arrow_size = 4;
		
		// Font ID
		var $font = 2;
		
		// Data og titler
		var $data = array();
		var $titles = array();
		
		// Resizeable graf ?
		var $resizeable = false;
		
		// Farver til kurver
		var $colors = array(
			"#FF4040",
			"#C0C000",
			"#40FF40",
			"#40FFFF",
			"#4040FF",
			"#FF40FF"
			);
			
		// Forgrundsfarve - bruges til tekst og optegning af x- og y-akser
		var $color = "#000000";
		
		function color($color)
		{
			$this->color = $color;
		}
		
		function colors($colors)
		{
			if (!is_array($colors)) return false;
			$this->colors = $colors;
		}
		
		function type($type)
		{
			if (!in_array($type, array("curve", "column"))) return false;
			$this->type = $type;
		}
		
		function width($width)
		{
			$width = intval($width);
			if ($width <= 0) return false;
			$this->width = $width;
		}
		
		function height($height)
		{
			$height = intval($height);
			if ($height <= 0) return false;
			$this->height = $height;
		}
		
		function background($background)
		{
			$this->background = $background;
		}
		
		function data($title, $data)
		{
			if (!is_array($data)) return false;
			$i = count($this->data);
			$this->titles[$i] = $title;
			$this->data[$i] = $data;
		}
		
		function text_width($text)
		{
			return imagefontwidth($this->font) * strlen($text);
		}
		
		function text_height()
		{
			return imagefontheight($this->font);
		}
		
		function resizeable($resizeable)
		{
			$this->resizeable = $resizeable;
		}
		
		function html()
		{
			global $class_graph_id;
			$class_graph_id++;
			$_SESSION["class_graph_data"][$class_graph_id] = array(
				"type" => $this->type,
				"background" => $this->background,
				"data" => $this->data,
				"titles" => $this->titles,
				"color" => $this->color,
				"colors" => $this->colors
				);
			$tmp = new tpl($this->resizeable ? "graph_resizeable" : "graph");
			$tmp->set("id", $class_graph_id);
			$tmp->set("width", $this->width);
			$tmp->set("height", $this->height);
			return $tmp->html();
		}
		
		function img()
		{
			// Billede objekt
			$image = new image;
			
			// Laver billede
			$img = imagecreatetruecolor($this->width, $this->height);
			
			// Baggrund
			if (ereg("^(#){0,1}[0-9a-f]{6}$", $this->background))
			{
				// Farve
				$color = $image->imagehtmlcolorallocate($img, $this->background);
				// Fylder farve
				imagefilledrectangle($img, 0, 0, $this->width, $this->height, $color);
			}
			elseif ($this->background <> "")
			{
				// Billede
				if (!eregi("^http://", $this->background))
				{
					$this->background = "http://" . $_SERVER["SERVER_NAME"] . "/" . $this->background;
				}
				$img2 = false;
				if (eregi("\.png$", $this->background))
				{
					$img2 = @imagecreatefrompng($this->background);
				}
				elseif (eregi("\.gif$", $this->background) and function_exists("imagecreatefromgif"))
				{
					$img2 = @imagecreatefromgif($this->background);
				}
				else
				{
					$img2 = @imagecreatefromjpeg($this->background);
				}
				// Er billede OK?
				if ($img2)
				{
					// Indsætter
					imagecopyresampled($img, $img2, 0, 0, 0, 0, $this->width, $this->height, imagesx($img2), imagesy($img2));
				}
			}
			
			// Finder min og max værdier
			unset($min_value);
			unset($max_value);
			$max_value_len = 0;
			$max_key_len = 0;
			$max_title_len = 0;
			$dec_count = 0;
			$array_keys = array();
			for ($i = 0; $i < count($this->data); $i++)
			{
				$tmp = $this->text_width($this->titles[$i]);
				if ($tmp > $max_title_len) $max_title_len = $tmp;
				$data = $this->data[$i];
				reset($data);
				while (list($key, $value) = each($data))
				{
					if ($value < $min_value or !isset($min_value)) $min_value = $value;
					if ($value > $max_value or !isset($max_value)) $max_value = $value;
					$tmp = $this->text_width($value);
					if ($tmp > $max_value_len) $max_value_len = $tmp;
					$tmp = $this->text_width($key);
					if ($tmp > $max_key_len) $max_key_len = $tmp;
					$value = abs($value);
					$value = $value - floor($value);
					$value = substr($value, 2);
					if (strlen($value) > $dec_count) $dec_count = strlen($dec_count);
					if (!in_array($key, $array_keys)) $array_keys[count($array_keys)] = $key;
				}
			}
			if ($max_value <= $min_value) $max_value++;
			
			// Beregner bredden af værdier - pixel pr tegn + margin
			$width_value = $max_value_len + $this->margin;
			$width_key = $max_key_len + $this->margin;
			$text_offset = -round($this->text_height() / 2);
			
			// Beregner bredden af forklaringer
			if ($max_title_len > 0)
			{
				$width_title = $this->arrow_size + $max_title_len + 3 * round($this->margin / 2);
				$height_title = round($this->margin / 2) + count($this->data) * ($this->text_height() + round($this->margin / 2));
				$innerwidth_title = $width_title - $this->margin;
				$innerheight_title = $height_title - $this->margin;
				$offset_x_title = $this->width - $this->margin - $width_title;
				$offset_y_title = $this->margin;
				$inneroffset_x_title = $offset_x_title + round($this->margin / 2);
				$inneroffset_y_title = $offset_y_title + round($this->margin / 2);
			}
			else
			{
				$width_title = 0;
				$height_title = 0;
			}
			
			// Beregner højde og bredde af diagram
			$width_graph = $this->width - $width_value - 2 * $this->margin - $width_title;
			$height_graph = $this->height - $width_key - 2 * $this->margin;
			$innerwidth_graph = $width_graph - 2 * $this->margin;
			$innerheight_graph = $height_graph - 2 * $this->margin;
			$offset_x_graph = $width_value + $this->margin;
			$offset_y_graph = $this->margin;
			$inneroffset_x_graph = $offset_x_graph + $this->margin;
			$inneroffset_y_graph = $offset_y_graph + $this->margin;
			
			// Allokerer farve
			$color = $image->imagehtmlcolorallocate($img, $this->color);
			
			// Laver forklaringer
			if ($max_title_len > 0)
			{
				imagerectangle($img, $offset_x_title, $offset_y_title, $offset_x_title + $width_title, $offset_y_title + $height_title, $color);
				for ($i = 0; $i < count($this->titles); $i++)
				{
					// Farve
					$color2 = $this->colors[$i];
					$color2 = $image->imagehtmlcolorallocate($img, $color2);
					$x1 = $inneroffset_x_title;
					$x2 = $inneroffset_x_title + $this->arrow_size;
					$y = $inneroffset_y_title + ($this->text_height() + round($this->margin / 2)) * $i - $text_offset;
					imagefilledrectangle($img, $x1, $y + $text_offset, $x2, $y - $text_offset, $color2);
					imagestring($img, $this->font, $x2 + round($this->margin / 2), $y + $text_offset, $this->titles[$i], $color);
				}
			}
			
			// Laver x og y linier
			// X
			imageline($img, 
				$offset_x_graph, $offset_y_graph + $height_graph, 
				$offset_x_graph + $width_graph, $offset_y_graph + $height_graph, 
				$color);
			// Pil X
			imageline($img, 
				$offset_x_graph + $width_graph, $offset_y_graph + $height_graph, 
				$offset_x_graph + $width_graph - $this->arrow_size, $offset_y_graph + $height_graph - $this->arrow_size, 
				$color);
			imageline($img, 
				$offset_x_graph + $width_graph, $offset_y_graph + $height_graph, 
				$offset_x_graph + $width_graph - $this->arrow_size, $offset_y_graph + $height_graph + $this->arrow_size, 
				$color);
			// Y
			imageline($img, 
				$offset_x_graph, $offset_y_graph, $offset_x_graph, 
				$offset_y_graph + $height_graph, 
				$color);
			// Pil X
			imageline($img, 
				$offset_x_graph, $offset_y_graph, 
				$offset_x_graph - $this->arrow_size, $offset_y_graph + $this->arrow_size, 
				$color);
			imageline($img, 
				$offset_x_graph, $offset_y_graph, 
				$offset_x_graph + $this->arrow_size, $offset_y_graph + $this->arrow_size, 
				$color);
				
			// Beregner step mellem hver værdi - max 1 pr 25 pixel
			$step = $max_value - $min_value;
			if ($step > ceil($innerheight_graph / 25)) $step = ceil($innerheight_graph / 25);
			if ($step < 1) $step = 1;
			
			// Indsætter værdier
			for ($i = 0; $i <= $step; $i++)
			{
				if ($i == 0)
				{
					$value = $min_value;
				}
				elseif ($i == $step)
				{
					$value = $max_value;
				}
				else
				{
					$value = $min_value + $i * ($max_value - $min_value) / $step;
				}
				$value = number_format($value, $dec_count, ",", "");
				$x = $this->margin;
				$y = $inneroffset_y_graph + $innerheight_graph - round($innerheight_graph / $step * $i);
				imagestring($img, $this->font, $x, $y + $text_offset, $value, $color);
				imageline($img, $offset_x_graph - $this->arrow_size, $y, $offset_x_graph + $this->arrow_size, $y, $color);
			}
			
			// Indsætter nøgler
			$count = count($array_keys);
			for ($i = 0; $i < $count; $i++)
			{
				$value = $array_keys[$i];
				if ($this->type == "column")
				{
					$x = $inneroffset_x_graph + round($innerwidth_graph / $count * $i) + round($innerwidth_graph / $count / 2);
				}
				else
				{
					$x = $inneroffset_x_graph + round($innerwidth_graph / ($count - ($count > 1 ? 1 : 0)) * $i);
				}
				$y = $offset_y_graph + $height_graph;
				imagestringup($img, $this->font, $x + $text_offset, $y + $width_key, $value, $color);
				imageline($img, $x, $y - $this->arrow_size, $x, $y + $this->arrow_size, $color);
			}
			
			// Bredde af søjler
			$width_column = floor($innerwidth_graph / (count($this->data) * $count));
			
			// Gennemløber data
			for ($i = 0; $i < count($this->data); $i++)
			{
				$data = $this->data[$i];
				$prev_x = 0;
				$prev_y = 0;
				// Farve
				$color = $this->colors[$i];
				$color = $image->imagehtmlcolorallocate($img, $color);
				for ($i2 = 0; $i2 < $count; $i2++)
				{
					if (isset($data[$array_keys[$i2]]))
					{
						// Værdi
						$value = $data[$array_keys[$i2]];
						// Beregner x og y
						if ($this->type == "column")
						{
							$x = $inneroffset_x_graph + round($innerwidth_graph / ($count + 1 - ($count > 1 ? 1 : 0)) * $i2);
						}
						else
						{
							$x = $inneroffset_x_graph + round($innerwidth_graph / ($count - ($count > 1 ? 1 : 0)) * $i2);
						}
						$y = $inneroffset_y_graph + $innerheight_graph - round($innerheight_graph * ($value - $min_value) / ($max_value - $min_value));
						// Indtegner
						if ($this->type == "curve")
						{
							// Kurve
							if ($prev_x > 0 and $prev_y > 0)
							{
								imageline($img, $prev_x, $prev_y, $x, $y, $color);
							}
						}
						elseif ($this->type == "column")
						{
							// Kolonne
							imagefilledrectangle($img, $x + $i * $width_column, $offset_y_graph + $height_graph - 1, $x + $i * $width_column + $width_column - 1, $y, $color);
						}
						// Gemmer tidligere
						$prev_x = $x;
						$prev_y = $y;
					}
				}
			}
			
			return $img;
		}
	}
	
	// Public variabel, der tæller op for hver graph objekt
	global $class_graph_id;
	$class_graph_id = 0;
	
	// Er filen kaldt direkte ?
	if (ereg("/class/graph\.php$", $_SERVER["PHP_SELF"]))
	{
		// Starter session
		session_start();
		
		// Henter data fra session
		$tmp = $_SESSION["class_graph_data"][intval($_GET["id"])];

		// Er graf kaldt korrekt ?
		if (!is_array($tmp)) exit;
		
		// Inkluderer image.php
		include("image.php");
		
		// Opretter graf klasse
		$graph = new graph;
		$graph->background($tmp["background"]);
		$graph->type($tmp["type"]);
		$graph->width($_GET["width"] > 200 ? $_GET["width"] : 200);
		$graph->height($_GET["height"] > 150 ? $_GET["height"] : 150);
		$graph->data = $tmp["data"];
		$graph->titles = $tmp["titles"];
		$graph->colors = $tmp["colors"];
		$graph->color = $tmp["color"];
		
		// Sender headers
		header("Content-type: image/png");
		
		// Viser graf
		imagepng($graph->img());
	}
?>
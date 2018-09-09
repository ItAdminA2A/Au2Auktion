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
		Beskrivelse:	Bruges til paging
		31-10-2007:		Opdateret så den understøtter nye URL'er
		28-01-2008:		Understøtter nu at ligge i en under-mappe, f.eks. /test/Admin/
	*/
	
	class paging
	{
		// Antal poster per side
		var $limit = 15;
		// Poster i alt
		var $total = 0;
		// Sidetal der vises på én gang
		var $pages = 15;
		
		// Init
		function paging()
		{
			// Antal sidetal der skal vises af gangen
			$this->pages = intval(cms_setting("paging_pages"));
			if ($this->pages < 3) $this->pages = 15;
		}
		
		// Sætter antal sidetal, der vises på én gang
		function pages($pages)
		{
			$this->pages = $pages;
		}
		
		// Returnerer aktuel side
		function current_page()
		{
			global $vars;
			$current_page = intval($vars["_paging_page"]);
			if ($current_page <= 0)
			{
				$current_page = 1;
			}
			return $current_page;
		}
		
		// Returnerer aktuel startpost
		function start()
		{
			return ($this->current_page() - 1) * $this->limit;
		}
		
		// Sætter antal poster i alt
		function total($total)
		{
			$this->total = $total;
		}
		
		// Returnerer eller sætter poster per side
		function limit($limit = false)
		{
			if ($limit > 0)
			{
				$this->limit = $limit;
			}
			return $this->limit;
		}
		
		// Returnerer antal sider i alt
		function total_pages()
		{
			return ceil($this->total / $this->limit);
		}
		
		// Laver paging-oversigt
		function html()
		{
			// Globale variabler
			global $vars;
			
			// Finder aktuel URL
			$url = $_SERVER["PHP_SELF"] . "?";
			$values = $vars;
			reset($values);
			while (list($key, $value) = each($values))
			{
				if ($key <> "_paging_page")
				{
					$url .= "&" . $key . "=" . urlencode($value);
				}
			}
			
			// Header
			$tmp = new tpl("_paging_header");
			$tmp->set("total", $this->total_pages());
			$tmp->set("current", $this->current_page());
			$html = $tmp->html();
			
			// Forrige side
			if ($this->current_page() > 1)
			{
				$tmp = new tpl("_paging_previous");
				$tmp->set("url", $url);
				$tmp->set("page",  $this->current_page() - 1);
				$html .= $tmp->html();
			}
			else
			{
				$tmp = new tpl("_paging_previous_none");
				$html .= $tmp->html();
			}
			
			// Finder første side, der skal vises
			$page_from = $this->current_page() - round($this->pages / 2) + 1;
			
			// Tjekker om først side er under 1
			if ($page_from < 1)
			{
				$page_to -= $page_from;
				$page_from = 1;
			}
			
			// Finder sidste side, der skal vises
			$page_to = $page_from + $this->pages - 1;
			
			// Tjekker om sidste side overskrider antal sider i alt
			if ($page_to > $this->total_pages())
			{
				$page_to = $this->total_pages();
				$page_from = $page_to - $this->pages + 1;
				if ($page_from < 1) $page_from = 1;
			}
			
			// Gennemløber sidetal
			$current_page = $this->current_page();
			for ($i = $page_from; $i <= $page_to; $i++)
			{
				if ($current_page == $i)
				{
					$tmp = new tpl("_paging_page_current");
				}
				else
				{
					$tmp = new tpl("_paging_page");
				}
				$tmp->set("url", $url);
				$tmp->set("page", $i);
				$html .= $tmp->html();
			}
			
			// Næste side
			if ($this->current_page() < $this->total_pages())
			{
				$tmp = new tpl("_paging_next");
				$tmp->set("url", $url);
				$tmp->set("page", $this->current_page() + 1);
				$html .= $tmp->html();
			}
			else
			{
				$tmp = new tpl("_paging_next_none");
				$html .= $tmp->html();
			}
			
			// Footer
			$tmp = new tpl("_paging_footer");
			$tmp->set("total", $this->total_pages());
			$tmp->set("current", $this->current_page());
			$html .= $tmp->html();
			
			// Returnerer resultat
			return $html;
		}
	}
?>
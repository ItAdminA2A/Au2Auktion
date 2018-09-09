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
		Klasse:			db
		Beskrivelse:	Database-klasse, der anvendes til alle kald af databaser.
	*/
	
	// Global variabel, der indeholder aktuel connection-ID
	$_db_conn_id = false;
	$_db_time = 0;
	
	// Global variabel, der indeholder tidligere SQL-kald, bruges ved buffering
	$_db_buffer = array();
	
	class db
	{
		// Angiver om der er oprettet forbindelse til serveren
		var $is_connected = false;
		// Aktuelt resultat-sæt
		var $actual_resultset = false;
		// Aktuelt resultat-række som numerisk array
		var $row = false;
		// Aktuelt resultat-række som alphanumerisk array
		var $array = false;
		// Gemmer ikke log beskeder ?
		var $disable_log = false;
		
		// Escaper
		function escape($string = "")
		{
			return mysql_real_escape_string($string);
		}
		
		// Init-funktion, der automatisk kan forbinde til MySQL-serveren
		function db($auto_connect = true)
		{
			// Globale variabler
			global $_db_conn_id;
			// Tjekker om der er oprettet en forbindelse til serveren
			$this->is_connected = ($_db_conn_id > 0);
			// Forbinder, hvis $auto_connect = true
			if ($auto_connect) 
			{
				$this->connect();
				$this->select_database();
			}
			if (function_exists("cms_init")) cms_init();
		}
		
		// Forbinder til MySQL-server
		function connect($server = false, $username = false, $password = false)
		{
			global $_db_conn_id, $_db_server, $_db_database, $_db_username, $_db_password;
			// Tjekker om der allerede er forbundet
			if ($_db_conn_id > 0)
			{
				$this->is_connected = true;
				return true;
			}
			// Henter server-info
			if (!$server) $server = $_db_server;
			if (!$username) $username = $_db_username;
			if (!$password) $password = $_db_password;
			// Forbinder
			$_db_conn_id = @mysql_connect($server, $username, $password) or trigger_error("Kunne ikke forbinde til MySQL-server (" . mysql_error() . ")");
			$this->is_connected = ($_db_conn_id > 0);
			return $this->is_connected;
		}
		
		// Afbryder forbindelse til MySQL-server
		function disconnect()
		{
			mysql_close();
			$this->is_connected = false;
		}
		
		// Vælger database
		function select_database($database = false)
		{
			// Server-info
			global $_db_database;
			if (!$database) $database = $_db_database;
			return @mysql_select_db($database) or trigger_error("Kunne ikke vælge MySQL-database (" . mysql_error() . ")");
		}
		
		// Udfører SQL
		function query($sql)
		{
			return $this->execute($sql);
		}
		
		// Udfører SQL
		function execute($sql)
		{
			// Globale variabler
			global $_db_buffer, $_db_buffer_active, $_db_time;
			// Tjekker om buffering er aktiveret
			if ($_db_buffer_active and strtoupper(substr(trim($sql), 0, 6)) == "SELECT" and isset($_db_buffer[$sql]))
			{
				// Henter fra buffer
				$this->actual_resultset = $_db_buffer[$sql];
			}
			else
			{
				// Udfører SQL-sætning
				$this->actual_resultset = @mysql_query($sql); // or trigger_error("Kunne ikke udføre SQL-sætning ($sql)");
				
				// Fejl?
				if (!$this->actual_resultset) $this->add_log($sql);
				
				// Tjekker om vi skal gemme i buffer
				if ($_db_buffer_active)
				{
					// Gemmer resultat i buffer
					$_db_buffer[$sql] = $this->actual_resultset;
				}
			}
			return $this->actual_resultset;
		}
		
		// Laver SQL-kald og returnerer resultat af første felt i første række
		function execute_field($sql)
		{
			$ress = @mysql_query($sql);
			if (!$ress) $this->add_log($sql);
			list($res) = @mysql_fetch_row($ress);
			return stripslashes($res);
		}
		
		// Returnerer næste række af resultat-sættet som et numerisk array
		function fetch_row($resultset = false)
		{
			$this->array = false;
			if (!$resultset) $resultset = $this->actual_resultset;
			$this->row = @mysql_fetch_row($resultset);
			return $this->row;
		}
		
		// Returnerer næste række af resultat-sættet som et alphanumerisk array
		function fetch_array($resultset = false)
		{
			$this->row = false;
			if (!$resultset) $resultset = $this->actual_resultset;
			$this->array = @mysql_fetch_array($resultset);
			return $this->array;
		}
		
		// Returnerer antal berørte rækker
		function affected_rows()
		{
			return mysql_affected_rows();
		}
		
		// Returnerer antal rækker i resultatset
		function num_rows()
		{
			if ($this->actual_resultset)
			{
				return mysql_num_rows($this->actual_resultset);
			}
			else
			{
				return 0;
			}
		}
		
		// Returnerer sidste indsatte ID
		function insert_id()
		{
			return mysql_insert_id();
		}
		
		// Returnerer antal bytes som alle tabeller optager tilsammen
		function get_db_size()
		{
			$bytes = 0;
			// Laver lige et ekstra db for ikke at ødelægge tidligere kald
			$db = new db;
			// Finder alle tabeller
			$db->execute("SHOW TABLE STATUS");
			// Gennemløber
			while ($db->fetch_array())
			{
				$bytes += $db->array["Data_length"] + $db->array["Index_length"];
			}
			// Returnerer
			return $bytes;
		}
		
		// Slår log til eller fra
		function disable_log($disable_log = false)
		{
			$this->disable_log = $disable_log;
		}
		
		// Gemmer i log hvis slået til
		function add_log($sql_query = "")
		{
			if ($this->disable_log) return false;
			global $module, $page, $do, $id;
			$error = mysql_error();
			if (eregi("duplicate entry", $error)) return false;
			add_log_message("Class: DB\r\n" . 
				"Request: $module | $page | $do | $id\r\n" .
				"MySQL error: $error\r\n" . 
				"SQL-query: $sql_query");
		}
	}
?>
<?php
	//
	// Denne klasse kan hente e-mails fra en POP3-server
	//

	class pop3 {
		
		var $server;
		var $user;
		var $pass;
		var $fp;
		var $last_response;
		
		function pop3() {
			$this->fp = false;
		}
		
		function connect($server) {
			$this->server = $server;
			// Forsøger at forbindelse til serveren
			$this->fp = fsockopen($server, 110, $errno, $errstr, 3);
			if ($this->fp) {
				stream_set_timeout($this->fp, 10);
				return true;
			} else {
				return false;
			}
		}
		
		function get_by_size($size = 0) {
			if (!$this->fp) {
				return false;
			}
			// Timeout
			$ts = time() + 3;
			$res = "";
			while (!feof($this->fp) and 
					strlen($res) < $size and 
					trim($tmp) != "-ERR" and
					$ts > time()) {
				$tmp = fgets($this->fp, 1024);
				$res .= $tmp;
				if ($tmp != "") $ts = time() + 3;
			}
			$this->last_response = $res;
			return $res;
		}
		
		function get($flere_linier = false) {
			if (!$this->fp) {
				return false;
			}
			// Tjekker om det er flere linier, der skal hentes
			if ($flere_linier) {
				$slut_tag = "\r\n.\r\n";
			} else {
				$slut_tag = "\r\n";
			}			
			// 3 sekunder til at hente svaret
			$ts = time() + 3;
			$res = "";
			while (!feof($this->fp) and 
					substr($res, -strlen($slut_tag)) != $slut_tag and 
					trim($tmp) != "-ERR" and
					$ts > time()) {
				$tmp = fgets($this->fp, 1024);
				$res .= $tmp;
				if ($tmp != "") $ts = time() + 3;
			}
			$this->last_response = $res;
			return $res;
		}
		
		function send($kommando, $flere_linier = false) {
			if (!$this->fp) {
				return false;
			}
			fputs($this->fp, $kommando . "\r\n");
			return $this->get($flere_linier);
		}
		
		function ok($res) {
			if (substr($res, 0, 3) == "+OK") {
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
			
			// Henter resultat fra server
			$res = $this->get();
			if (!$this->ok($res)) {
				return false;
			}
			
			// Sender brugernavn
			$res = $this->send("USER " . $this->user);
			if (!$this->ok($res)) {
				return false;
			}
			
			// Sender password
			$res = $this->send("PASS " . $this->pass);
			if (!$this->ok($res)) {
				return false;
			}
			
			return true;
		}
		
		function get_list($id = "") {
			// Forespørger efter liste
			$res = $this->send("LIST" . ($id != "" ? (" " . $id) : ""), $id == "");
			
			if (!$this->ok($res)) {
				return false;
			}
			
			// Splitter mails i array
			$array = array();
			while (ereg("([0-9]+) ([0-9]+)\r\n", $res, $tmp_array)) {
				$res = str_replace($tmp_array[0], "", $res);
				$array[$tmp_array[1]] = $tmp_array[2];
			}
			
			return $array;
		}
		
		function get_mail($id) {
			// Forespørger på mail-ID
			$res = $this->send("RETR " . $id);
			
			if (!$this->ok($res)) {
				return false;
			}
			
			// Undersøger om serveren fortæller hvor mange bytes der er
			if (eregi("\+OK ([1-9]+[0-9]+) ", $res, $array))
			{
				// Henter mail efter størrelse
				$res .= $this->get_by_size(intval($array[1]) + 3);
			}
			else
			{
				// Henter mail indtil vi modtager sluttegn
				$res .= $this->get(true);
				
				// Fjerner sidste linieskift
				$res = substr($res, 0, strlen($res) - 5);
			}
			
			// Fjerner første og sidste linie
			$res = substr($res, strpos($res, "\r\n"));
			
			return $res;
		}
		
		function delete_mail($id) {
			// Sletter mail
			$res = $this->send("DELE " . $id);
			
			if (!$this->ok($res)) {
				return false;
			} else {
				return true;
			}
		}
		
		function stat()
		{
			// Henter antal mails i boksen
			$res = $this->send("STAT");
			
			if (!$this->ok($res))
			{
				return false;
			}
			else
			{
				list($ok, $count, $bytes) = split("[ ]", $res);
				return array(intval($count), intval($bytes));
			}
		}
		
		function close() {
			// Lukker forbindelsen
			$this->send("QUIT");
			$this->fp = false;
			
			return true;
		}
	}
?>
#!/usr/bin/php
<?php
	die("DEAKTIVERET DA DATABASERNE ER LAGT SAMMEN\n");

	//
	// CLI script - sørger for at sync'e med hjemmeside
	//
	
	function OBA_server_sync($action, $data)
	{
		global $sleep_on_error, $server, $module, $_document_root;
		
		$boundary = uniqid("----------" . $module);
		
		// Bygger POST
		$content = 
			"--" . $boundary . "\r\n" .
			"Content-Disposition: form-data; name=\"secret\"\r\n" .
			"\r\n" .
			"ntmh6e4Z4T7ZK4uqJb2qkQ7NeDeRSSu2Z67RuZN3748QsWQ3Hsdi5sJQFFwZ9w99\r\n" .
			"--" . $boundary . "\r\n" .
			"Content-Disposition: form-data; name=\"module\"\r\n" .
			"\r\n" .
			$module . "\r\n" .
			"--" . $boundary . "\r\n" .
			"Content-Disposition: form-data; name=\"page\"\r\n" .
			"\r\n" .
			"sync\r\n" .
			"--" . $boundary . "\r\n" .
			"Content-Disposition: form-data; name=\"action\"\r\n" .
			"\r\n" .
			$action . "\r\n" .
			"--" . $boundary . "\r\n" .
			"Content-Disposition: form-data; name=\"data\"\r\n" .
			"\r\n" .
			$data . "\r\n";
		
		if ($action == "SAVE_FILE")
		{
			// Indlæser fil
			$content .=
				"--" . $boundary . "\r\n" .
				"Content-Disposition: form-data; name=\"file\"; filename=\"image.jpg\"\r\n" .
				"Content-Type: image/jpeg\r\n" .
				"\r\n" .
				file_get_contents($_document_root . "/modules/$module/upl/" . $data) . "\r\n";
		}
		
		$content .= "--" . $boundary . "--";

		if ($fs = fsockopen($server, 80, $a, $b, 5))
		{
			fputs($fs,
				"POST / HTTP/1.0\r\n" .
				"Host: $server\r\n" .
				"Content-Type: multipart/form-data; boundary=" . $boundary . "\r\n" .
				"Content-Length: " . strlen($content) . "\r\n" .
				"Connection: Close\r\n" .
				"\r\n" .
				$content);
				
			// Venter på svar - max 5 sek
			$timeout = microtime(true) + 5;
			
			// Headers
			$headers = "";
			while ($timeout > microtime(true) and !feof($fs) and strpos($headers, "\r\n\r\n") === false)
			{
				$headers .= fgets($fs);
			}

			// Content
			$response = substr($headers, strpos($headers, "\r\n\r\n") + 4);
			$headers = substr($headers, 0, strpos($headers, "\r\n\r\n"));
			if (preg_match("/\r\ncontent-length\: ([0-9]+)\r\n/i", $headers, $array))
			{
				$contentlength = intval($array[1]);
			}
			else
			{
				$contentlength = 0;
			}
			while ($timeout > microtime(true) and !feof($fs) and (strlen($response) < $contentlength or $contentlength == 0))
			{
				$response .= fgets($fs);
			}				
			fclose($fs);
			
			if ($timeout > microtime(true))
			{
				// Svar ok
				list($response, $responsedata) = explode("|", $response, 2);
				if ($response == "OK")
				{
					// OK
					return array("OK", $responsedata);
				}
				else
				{
					// Sync fejl
					add_log_message("$module CLI\r\nJeg spurgte: $action|$data\r\n$server svarede: " . htmlentities($response . "|" . $responsedata) . " - går i dvale $sleep_on_error sekunder");
					sleep($sleep_on_error);
					return array($response, $responsedata);
				}
			}
			else
			{
				// Intet svar
				add_log_message("$module CLI: $server svarede ikke inden for 5 sekunder ($timeout < " . microtime(true) . ") - går i dvale $sleep_on_error sekunder");
				sleep($sleep_on_error);
				return array("ERROR", "No response");
			}
			
		}
		else
		{
			add_log_message("$module CLI: Kan ikke forbinde til $server - går i dvale $sleep_on_error sekunder");
			sleep($sleep_on_error);
			return array("ERROR", "Connection error");
		}
	}
	
	if (isset($_SERVER["HTTP_HOST"])) die("Må kun køres fra CLI");
	
	// Time limit = uendelig
	set_time_limit(0);
	
	// Fejl-håndtering
	error_reporting("E_NONE");

	// Skifter til aktuel mappe
	chdir(dirname(__FILE__));
	
	// Modul-navn
	$module = preg_replace("/^(.*\/modules\/)([^\/]+)(\/.*)$/", "\\2", __FILE__);
	
	// Include filer
	require("../../../inc/config.php");
	require("../../../inc/functions.php");
	require("../../../class/db.php");
	require("../inc/functions.php");
	
	// Server, der skal tjekkes op imod
	$server = "au2auktion.dk";
	
	// Database
	$db = new db;
	
	// Ændringstid for __FILE__
	$filemtime = filemtime(__FILE__);
	
	// Log at vi er startet
	add_log_message("$module CLI: Starter $server");
	
	// Hvor lang tid skal vi gå i dvale, hvis der sker noget uventet
	$sleep_on_error = 5;
	
	// Næste cronjob
	$cronjob_time = time();
	
	while (true)
	{
		// Ryd fil cache
		clearstatcache();
		
		// Er bot.php ændret, så stopper vi script
		if ($filemtime != filemtime(__FILE__))
		{
			add_log_message("$module CLI: Genstarter, da script er ændret");
			exit;
		}		

		// Tjekker for sync-kommandoer
		$ress = $db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_sync
			ORDER BY
				id
			");
		while ($res = $db->fetch_array($ress))
		{
			list($result, $data) = OBA_server_sync($res["action"], $res["data"]);
			if ($result == "OK")
			{
				// OK
				$db->execute("
					DELETE FROM
						" . $_table_prefix . "_module_" . $module . "_sync
					WHERE
						id = '" . $res["id"] . "'
					");
			}
			else
			{
				// Fejl - stop
				$ress = false;
			}
		}
		
		// Spørger server om den har noget til os
		list($action, $data) = OBA_server_sync("SYNC", "");
		list($id, $action, $data) = explode("|", $data, 3);
		if ($action == "SQL")
		{
			// SQL
			
			// Tilladte SQL
			if (preg_match("/^[\s\t\r\n`]*(INSERT INTO|DELETE FROM|UPDATE)[\s\t\r\n`]+(" . $_table_prefix . "_module_" . $module . "_auctions|" . $_table_prefix . "_module_" . $module . "_images|" . $_table_prefix . "_module_" . $module . "_bids|" . $_table_prefix . "_module_" . $module . "_images|" . $_table_prefix . "_user_" . $module . "_cust)[\s\t\r\n`]+/m", $data))
			{			
				if (!$db->execute($data))
				{
					$err = mysql_error();
					if (preg_match("/^Duplicate entry/", $err))
					{
						// OK
						OBA_server_sync("SYNC_OK", $id);
					}
					else
					{
						add_log_message("Server SQL kunne ikke udføres: $err");
						OBA_server_sync("SYNC_ERROR", $id);
					}
				}
				else
				{
					// OK
					OBA_server_sync("SYNC_OK", $id);
				}
			}
			else
			{
				add_log_message("Server SQL kunne ikke udføres: $data\r\n$err");
			}
			
			
		}
		elseif ($action == "SAVE_FILE")
		{
			// Fil, der skal gemmes
			
			// Fildata
			list($filename, $content) = explode("|", $data, 2);
			
			if (preg_match("/^[a-zA-Z0-9_\-]+\.jpg$/", $filename))
			{
				add_log_message($_document_root . "/modules/$module/upl/" . $filename);
				if (file_put_contents($_document_root . "/modules/$module/upl/" . $filename, base64_decode($content)))
				{
					// OK
					OBA_server_sync("SYNC_OK", $id);
				}
			}
			
		}
		elseif ($action == "DELETE_FILE")
		{
			// Fil, der skal slettes
			
			if (preg_match("/^[a-zA-Z0-9_\-]+\.jpg$/", $data))
			{
				if (!is_file($_document_root . "/modules/$module/upl/" . $data) or 
					unlink($_document_root . "/modules/$module/upl/" . $data))
				{
					// OK
					OBA_server_sync("SYNC_OK", $id);
				}
			}
		}
		elseif ($action == "ONLINE")
		{
			// Antal online
			module_setting("online_count", intval($data));
			module_setting("online_count_time", time());
			OBA_server_sync("SYNC_OK", $id);
		}
		elseif ($action == "BID")
		{
			// Online bud
			
			add_log_message("Online bud: $data");
			
			// Bud data
			list($auction_id, $user_id, $bid) = explode("|", $data);
			
			// Tjekker om auktionen er aktiv
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					auction_date = '" . date("Y-m-d") . "' AND
					NOT ISNULL(start_time) AND
					ISNULL(end_time) AND
					id = '" . $db->escape(module_setting("cur_auction_id")) . "' AND
					id = '" . $db->escape($auction_id) . "' AND
					cur_price + 500 <= '" . intval($bid) . "'
				");
			$auc = $db->fetch_array();
			
			// Henter bruger
			$db->execute("
				SELECT
					id
				FROM
					" . $_table_prefix . "_user_" . $module . "_cust
				WHERE
					id = '" . intval($user_id) . "'
				");
			$cust = $db->fetch_array();
			
			if ($auc and $cust)
			{
				// Gemmer bud
				$bid_id = OBA_id();
				$sql = "
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_bids
					(
						id,
						time,
						auction_id,
						bidder_id,
						bid,
						`type`
					)
					VALUES
					(
						'$bid_id',
						'" . date("Y-m-d H:i:s") . "',
						'" . $auc["id"] . "',
						'" . $cust["id"] . "',
						'" . intval($bid) . "',
						'Online'
					)
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
								
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						cur_price = '" . intval($bid) . "',
						bidder_id = '" . $cust["id"] . "'
					WHERE
						id = '" . $auc["id"] . "' AND
						cur_price < '" . intval($bid) . "' AND
						NOT ISNULL(start_time) AND
						ISNULL(end_time)
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
			}
			
			OBA_server_sync("SYNC_OK", $id);
		}
		
		// Cronjob
		if ($action == "" and $cronjob_time <= time())
		{
			// Igen om 1 minut
			$cronjob_time = time() + 60;
			
			// Cronjob-fil
			include("cli_cronjob.php");
		}

		// Er der admin online
		if ($action != "OK" or $db->execute_field("
			SELECT
				id
			FROM
				" . $_table_prefix . "_user_" . $module . "_admin
			WHERE
				login_time > '" . date("Y-m-d H:i:s", strtotime("-5 minute")) . "'
			"))
		{
			// Pause 1 sek
			sleep(1);
		}
		else
		{
			// Pause 15 sek
			sleep(15);
		}
	}

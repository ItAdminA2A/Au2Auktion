<?php
	/*
		Opretter faktura for afsluttede online auktioner
	*/
	
	exit; // Deaktiveret
	
	add_log_message("$module: CLI cronjob");
	
	$ress = $db->execute("
		SELECT
			*
		FROM
			" . $_table_prefix . "_module_" . $module . "_auctions
		WHERE
			auction_type = 'online' AND
			NOT ISNULL(end_time) AND
			end_time < '" . date("Y-m-d H:i:00") . "' AND
			ISNULL(end_email_time) AND
			cur_price >= min_price AND
			cur_price > 0 AND
			seller_account_invoice_id = 0 AND
			invoice_id = 0 AND
			NOT ISNULL(`end_web_time`)
		");
	while ($res = $db->fetch_array($ress))
	{
		// Salær inkl. moms
		$salery = $db->execute_field("
			SELECT
				salery
			FROM
				" . $_table_prefix . "_module_" . $module . "_online_salery
			WHERE
				bid <= '" . $res["cur_price"] . "'
			ORDER BY
				bid DESC
			LIMIT
				0, 1
			");
			
		// Forhandler salær inkl. moms
		$salery_dealer = $db->execute_field("
			SELECT
				salery_dealer
			FROM
				" . $_table_prefix . "_module_" . $module . "_online_salery
			WHERE
				bid <= '" . $res["cur_price"] . "'
			ORDER BY
				bid DESC
			LIMIT
				0, 1
			") / 100 * (100 + intval(module_setting("vat_pct")));
			
		// Henter køber
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_user_" . $module . "_cust
			WHERE
				id = '" . $res["bidder_id"] . "'
			");
		if ($buyer = $db->fetch_array())
		{
			// Opretter faktura for køber
			$no = OBA_invoice_no();
			if ($db->execute("
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_invoices
				(
					name,
					address,
					zipcode,
					city,
					phone,
					email,
					invoice_no,
					invoice_date
				)
				VALUES
				(
					'" . $db->escape($buyer["name"]) . "',
					'" . $db->escape($buyer["address"]) . "',
					'" . $db->escape($buyer["zipcode"]) . "',
					'" . $db->escape($buyer["city"]) . "',
					'" . $db->escape($buyer["phone"]) . "',
					'" . $db->escape($buyer["email"]) . "',
					'$no',
					'" . date("Y-m-d") . "'
				)
				"))
			{
				$b_id = $db->insert_id();
				OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_invoices", $b_id);
				
				// Opretter varelinie
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_invoices_lines
					(
						invoice_id,
						title,
						quantity,
						price,
						no_vat
					)
					VALUES
					(
						'$b_id',
						'Salær katalognummer " . $db->escape($res["auction_no"]) . ", købspris DKK " . $res["cur_price"] . "',
						'1',
						'" . number_format(($buyer["extra_type"] == "private" ? $salery : $salery_dealer) / (100 + intval(module_setting("vat_pct"))) * 100, 2, ".", "") . "',
						'0'
					)
					");
				$b_lid = $db->insert_id();
				OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_invoices_lines", $b_lid);
			}
		}
		else
		{
			$b_id = 0;
		}
		
		// Opretter faktura for sælger
		$no = OBA_invoice_no();
		if ($db->execute("
			INSERT INTO
				" . $_table_prefix . "_module_" . $module . "_invoices
			(
				name,
				address,
				zipcode,
				city,
				phone,
				email,
				invoice_no,
				invoice_date
			)
			VALUES
			(
				'" . $db->escape($res["seller_name"]) . "',
				'" . $db->escape($res["seller_address"]) . "',
				'" . $db->escape($res["seller_zipcode"]) . "',
				'" . $db->escape($res["seller_city"]) . "',
				'" . $db->escape($res["seller_phone"]) . "',
				'" . $db->escape($res["seller_email"]) . "',
				'$no',
				'" . date("Y-m-d") . "'
			)
			"))
		{
			$s_id = $db->insert_id();
			OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_invoices", $s_id);
			
			// Opretter varelinie
			$db->execute("
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_invoices_lines
				(
					invoice_id,
					title,
					quantity,
					price,
					no_vat
				)
				VALUES
				(
					'$s_id',
					'Salær katalognummer " . $db->escape($res["auction_no"]) . ", salgspris DKK " . $res["cur_price"] . "',
					'1',
					'" . number_format(($res["seller_type"] == "private" ? $salery : $salery_dealer) / (100 + intval(module_setting("vat_pct"))) * 100, 2, ".", "") . "',
					'0'
				)
				");
			$s_lid = $db->insert_id();
			OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_invoices_lines", $s_lid);
		}
		else
		{
			$s_li = 0;
		}
		
		// Opdaterer auktion
		$sql = "
			UPDATE
				" . $_table_prefix . "_module_" . $module . "_auctions
			SET
				invoice_id = '$b_id',
				seller_account_invoice_id = '$s_id',
				invoice_time = '" . date("Y-m-d H:i:s") . "',
				seller_account_time = '" . date("Y-m-d H:i:s") . "'
			WHERE
				id = '" . $res["id"] . "'
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);
	}

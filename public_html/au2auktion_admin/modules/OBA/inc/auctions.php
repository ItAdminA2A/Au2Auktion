<?php
	// ID må gerne indehold et bogstav
	if (!preg_match("/^[A-Z]{0,1}[0-9]+$/", $vars["id"]))
	{
		$id = "";
	}
	else
	{
		$id = $vars["id"];
	}

	if ($do == "auctions_online_buyer")
	{
		// Afregn køber
		
		if ($id != "")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					id = '$id' AND
					ISNULL(invoice_time) AND
					NOT ISNULL(end_time) AND
					end_time < '" . date("Y-m-d H:i:s") . "' AND
					`cancel` = 0 AND
					auction_type = 'online' AND
					cur_price >= min_price
				");
			$auc = $db->fetch_array();
			
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $module . "_cust
				WHERE
					id = '" . $auc["bidder_id"] . "'
				");
			$cust = $db->fetch_array();
			
			$salery = $db->execute_field("
				SELECT
					salery
				FROM
					" . $_table_prefix . "_module_" . $module . "_online_salery
				WHERE
					bid <= '" . $auc["cur_price"] . "'
				ORDER BY
					bid DESC
				LIMIT
					0, 1
				");
			// Trækker moms fra
			$salery = $salery / (100 + intval(module_setting("vat_pct"))) * 100;
				
			if ($auc and $cust and $salery)
			{
				// Alt ok
				
				// Opretter faktura-kladde
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_invoices
					(
						invoice_date,
						name,
						address,
						zipcode,
						city,
						phone,
						email
					)
					VALUES
					(
						'" . date("Y-m-d") . "',
						'" . $db->escape($cust["name"]) . "',
						'" . $db->escape($cust["address"]) . "',
						'" . $db->escape($cust["zipcode"]) . "',
						'" . $db->escape($cust["city"]) . "',
						'" . $db->escape($cust["phone"]) . "',
						'" . $db->escape($cust["email"]) . "'
					)
					");
				$iid = $db->insert_id();
				
				// Opretter linier
				$lines = array(
					"Køber-Salær for online-auktion nr. " . $auc["auction_no"],
					"Bil: " . $auc["brand"] . " " . $auc["model"],
					"Reg.nr.: " . $auc["regno"],
					"Stelnr.: " . $auc["chasno"],
					"Budt: DKK " . $auc["cur_price"] . ",-",
					"Sælger: " . $auc["seller_name"] . ", " . $auc["seller_address"] . ", " . $auc["seller_zipcode"] . " " . $auc["seller_city"] . ", tlf. " . $auc["seller_phone"] . ", email " . $auc["seller_email"]
					);
				for ($i = 0; $i < count($lines); $i++)
				{
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
							'$iid',
							'" . $db->escape($lines[$i]) . "',
							'1',
							'" . ($i == 0 ? $salery : 0) . "',
							'0'
						)
						");
				}
				
				// Gemmer sammen med auktion
				$db->execute("
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						invoice_id = '$iid',
						invoice_time = '" . date("Y-m-d H:i:s") . "'
					WHERE
						id = '" . $auc["id"] . "'
					");
					
				// Videre til faktura
				header("Location: /?module=$module&page=$page&do=invoices_add&id=$iid");
				exit;
			}			
		}
		
		$msg = new message;
		$msg->title("Online auktioner - Afregn køber");
		$html .= $msg->html();
		
		$ress = $db->execute("
			SELECT
				auc.*,
				bidno.number,
				cust.name,
				bids.type
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions AS auc
			INNER JOIN
				" . $_table_prefix . "_user_" . $module . "_cust AS cust
			ON
				cust.id = auc.bidder_id
			LEFT JOIN
				" . $_table_prefix . "_module_" . $module . "_bidno AS bidno
			ON
				bidno.cust_id = cust.id
			LEFT JOIN
				" . $_table_prefix . "_module_" . $module . "_bids AS bids
			ON
				bids.bidder_id = cust.id AND
				bids.auction_id = auc.id AND
				bids.bid = auc.cur_price
			WHERE
				ISNULL(invoice_time) AND
				NOT ISNULL(end_time) AND
				end_time < '" . date("Y-m-d H:i:s") . "' AND
				`cancel` = 0 AND
				auction_type = 'online' AND
				cur_price >= min_price
			ORDER BY
				auction_date,
				auction_no
			");
		if ($db->num_rows($ress) > 0)
		{
			$tbl = new table;
			$tbl->th("Auktionsdato");
			$tbl->th("Auktionsnr");
			$tbl->th("Mærke");
			$tbl->th("Model");
			$tbl->th("Type");
			$tbl->th("Byder");
			$tbl->th("Bud");
			$tbl->th("Valg");
			$tbl->endrow();
			
			while ($res = $db->fetch_array($ress))
			{
				$tbl->td(date("d-m-Y", strtotime($res["auction_date"])));
				$tbl->td($res["auction_no"]);
				$tbl->td($res["brand"]);
				$tbl->td($res["model"]);
				$tbl->td($res["type"]);
				$tbl->td($res["number"] . " " . $res["name"]);
				$tbl->td($res["cur_price"]);
				$tbl->choise("Afregn", $do, $res["id"]);
				$tbl->endrow();
			}
			
			$html .= $tbl->html();
		}
		else
		{
			$html .= "Intet at afregne";
		}
		
	}	
	elseif ($do == "auctions_online_seller")
	{
		// Afregn sælger
		
		if ($id != "")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					id = '$id' AND
					ISNULL(seller_account_time) AND
					NOT ISNULL(end_time) AND
					end_time < '" . date("Y-m-d H:i:s") . "' AND
					`cancel` = 0 AND
					auction_type = 'online' AND
					cur_price >= min_price
				");
			$auc = $db->fetch_array();
			
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $module . "_cust
				WHERE
					id = '" . $auc["bidder_id"] . "'
				");
			$cust = $db->fetch_array();
			
			$salery = $db->execute_field("
				SELECT
					salery
				FROM
					" . $_table_prefix . "_module_" . $module . "_online_salery
				WHERE
					bid <= '" . $auc["cur_price"] . "'
				ORDER BY
					bid DESC
				LIMIT
					0, 1
				");
			// Trækker moms fra
			$salery = $salery / (100 + intval(module_setting("vat_pct"))) * 100;
				
			if ($auc and $cust and $salery)
			{
				// Alt ok
				
				// Opretter faktura-kladde
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_invoices
					(
						invoice_date,
						name,
						address,
						zipcode,
						city,
						phone,
						email
					)
					VALUES
					(
						'" . date("Y-m-d") . "',
						'" . $db->escape($auc["seller_name"]) . "',
						'" . $db->escape($auc["seller_address"]) . "',
						'" . $db->escape($auc["seller_zipcode"]) . "',
						'" . $db->escape($auc["seller_city"]) . "',
						'" . $db->escape($auc["seller_phone"]) . "',
						'" . $db->escape($auc["seller_email"]) . "'
					)
					");
				$iid = $db->insert_id();
				
				// Opretter linier
				$lines = array(
					"Sælger-Salær for online-auktion nr. " . $auc["auction_no"],
					"Bil: " . $auc["brand"] . " " . $auc["model"],
					"Reg.nr.: " . $auc["regno"],
					"Stelnr.: " . $auc["chasno"],
					"Solgt for: DKK " . $auc["cur_price"] . ",-",
					"Køber: " . $cust["name"] . ", " . $cust["address"] . ", " . $cust["zipcode"] . " " . $cust["city"] . ", tlf. " . $cust["phone"] . ", email " . $cust["email"]
					);
				for ($i = 0; $i < count($lines); $i++)
				{
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
							'$iid',
							'" . $db->escape($lines[$i]) . "',
							'1',
							'" . ($i == 0 ? $salery : 0) . "',
							'0'
						)
						");
				}
				
				// Gemmer sammen med auktion
				$db->execute("
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						seller_account_invoice_id = '$iid',
						seller_account_time = '" . date("Y-m-d H:i:s") . "'
					WHERE
						id = '" . $auc["id"] . "'
					");
					
				// Videre til faktura
				header("Location: /?module=$module&page=$page&do=invoices_add&id=$iid");
				exit;
			}			
		}
		
		$msg = new message;
		$msg->title("Online auktioner - Afregn sælger");
		$html .= $msg->html();
		
		$ress = $db->execute("
			SELECT
				auc.*,
				bidno.number,
				cust.name,
				bids.type
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions AS auc
			INNER JOIN
				" . $_table_prefix . "_user_" . $module . "_cust AS cust
			ON
				cust.id = auc.bidder_id
			LEFT JOIN
				" . $_table_prefix . "_module_" . $module . "_bidno AS bidno
			ON
				bidno.cust_id = cust.id
			LEFT JOIN
				" . $_table_prefix . "_module_" . $module . "_bids AS bids
			ON
				bids.bidder_id = cust.id AND
				bids.auction_id = auc.id AND
				bids.bid = auc.cur_price
			WHERE
				ISNULL(seller_account_time) AND
				NOT ISNULL(end_time) AND
				end_time < '" . date("Y-m-d H:i:s") . "' AND
				`cancel` = 0 AND
				auction_type = 'online' AND
				cur_price >= min_price
			ORDER BY
				auction_date,
				auction_no
			");
		if ($db->num_rows($ress) > 0)
		{
			$tbl = new table;
			$tbl->th("Auktionsdato");
			$tbl->th("Auktionsnr");
			$tbl->th("Mærke");
			$tbl->th("Model");
			$tbl->th("Type");
			$tbl->th("Byder");
			$tbl->th("Bud");
			$tbl->th("Valg");
			$tbl->endrow();
			
			while ($res = $db->fetch_array($ress))
			{
				$tbl->td(date("d-m-Y", strtotime($res["auction_date"])));
				$tbl->td($res["auction_no"]);
				$tbl->td($res["brand"]);
				$tbl->td($res["model"]);
				$tbl->td($res["type"]);
				$tbl->td($res["number"] . " " . $res["name"]);
				$tbl->td($res["cur_price"]);
				$tbl->choise("Afregn", $do, $res["id"]);
				$tbl->endrow();
			}
			
			$html .= $tbl->html();
		}
		else
		{
			$html .= "Intet at afregne";
		}
		
	}	
	elseif ($do == "auctions_online_salery")
	{
		// Online salær
		
		if ($vars["do2"] == "delete")
		{
			$sql = "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_online_salery
				WHERE
					id = '$id'
				";
			if ($db->execute($sql))
			{
				OBA_sync("SQL", $sql);
			}
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}
		
		$msg = new message;
		$msg->title("Online salær");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->tpl("th", "Opret");
		$frm->input(
			"Bud fra",
			"bid",
			"0",
			"^[0-9]+$",
			"Påkrævet tal"
			);
		$frm->input(
			"Privat salær inkl. moms",
			"salery",
			"",
			"^[1-9]+[0-9]*$",
			"Påkrævet positivt tal"
			);
		$frm->input(
			"Forhandler salær ex. moms",
			"salery_dealer",
			"",
			"^[1-9]+[0-9]*$",
			"Påkrævet positivt tal"
			);
		if ($frm->done())
		{
			$sql = "
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_online_salery
				(
					`bid`,
					`salery`,
					salery_dealer
				)
				VALUES
				(
					'" . intval($frm->values["bid"]) . "',
					'" . intval($frm->values["salery"]) . "',
					'" . intval($frm->values["salery_dealer"]) . "'
				)
				";
			if ($db->execute($sql))
			{
				OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_online_salery", $db->insert_id());
			}
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}
		$html .= $frm->html();

		$tbl = new table;
		$tbl->th("Bud fra");
		$tbl->th("Privat salær inkl. moms");
		$tbl->th("Forhandler salær ex. moms");
		$tbl->th("Slet");
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_online_salery
			ORDER BY
				bid
			");
		while ($db->fetch_array())
		{
			$tbl->td("DKK " . $db->array["bid"] . ",-");
			$tbl->td("DKK " . $db->array["salery"] . ",-");
			$tbl->td("DKK " . ($db->array["salery_dealer"]) . ",-");
			$tbl->choise("Slet", $do, $db->array["id"] . "&do2=delete", "Slet?");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();		
		
	}
	elseif ($do == "auctions_online_days")
	{
		// Online auktionsdage
		
		if ($vars["do2"] == "delete")
		{
			$sql = "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_online_days
				WHERE
					id = '$id'
				";
			if ($db->execute($sql))
			{
				OBA_sync("SQL", $sql);
			}
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}
		
		$arr_weekdays = array(
			"0" => "Søndag",
			"1" => "Mandag",
			"2" => "Tirsdag",
			"3" => "Onsdag",
			"4" => "Torsdag",
			"5" => "Fredag",
			"6" => "Lørdag"
			);
	
		$msg = new message;
		$msg->title("Online auktionsdage");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->tpl("th", "Opret");
		$frm->select(
			"Ugedag",
			"weekday",
			"",
			"^[0-6]{1}$",
			"Påkrævet",
			"",
			array(
				array("", ""),
				array("1", "Mandag"),
				array("2", "Tirsdag"),
				array("3", "Onsdag"),
				array("4", "Torsdag"),
				array("5", "Fredag"),
				array("6", "Lørdag"),
				array("0", "Søndag")
				)
			);
		$frm->input(
			"Start-tidspunkt",
			"time",
			"11:00",
			"^[0-9]{2}:[0-9]{2}$",
			"Påkrævet format: tt:mm, f.eks. 11:00"
			);
		$frm->input(
			"Varighed i timer",
			"duration",
			24,
			"^[1-9]+[0-9]*$",
			"Påkrævet positivt tal"
			);
		if ($frm->done())
		{
			$sql = "
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_online_days
				(
					`weekday`,
					`time`,
					`duration`
				)
				VALUES
				(
					'" . intval($frm->values["weekday"]) . "',
					'" . $db->escape($frm->values["time"]) . "',
					'" . intval($frm->values["duration"]) . "'
				)
				";
			if ($db->execute($sql))
			{
				OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_online_days", $db->insert_id());
			}
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}
		$html .= $frm->html();

		$tbl = new table;
		$tbl->th("Ugedag");
		$tbl->th("Tidspunkt");
		$tbl->th("Varighed");
		$tbl->th("Slet");
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_online_days
			ORDER BY
				IF(`weekday` = 0, 7, `weekday`),
				`time`
			");
		while ($db->fetch_array())
		{
			$tbl->td($arr_weekdays[$db->array["weekday"]]);
			$tbl->td(substr($db->array["time"], 0, 5));
			$tbl->td($db->array["duration"] . " timer");
			$tbl->choise("Slet", $do, $db->array["id"] . "&do2=delete", "Slet?");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();		
		
	}
	elseif ($do == "auctions_bidno")
	{
		// Opret bydernumre
		
		if ($vars["do2"] == "delete")
		{
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_bidno
				WHERE
					`number` = '$id'
				");
		}
		elseif ($vars["do2"] == "delete_all")
		{
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_bidno
				");
		}

		$ajax = new ajax;
		if ($ajax->do == "search_cust")
		{
			$response = array("state" => "ok");
			$count = 0;
			$searchstring = trim($ajax->values["searchstring"]);
			if ($searchstring != "")
			{
				$db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_user_" . $module . "_cust
					WHERE
						name LIKE '%" . $db->escape($searchstring) . "%' OR
						phone LIKE '%" . $db->escape($searchstring) . "%' OR
						email LIKE '%" . $db->escape($searchstring) . "%'
					ORDER BY
						IF(name LIKE '" . $db->escape($searchstring) . "%', 0, 1),
						name
					LIMIT
						0, 25
					");
				while ($db->fetch_array())
				{
					$response["id" . $count] = $db->array["id"];
					$response["type" . $count] = $db->array["extra_type"];
					$response["name" . $count] = $db->array["name"];
					$response["address" . $count] = $db->array["address"];
					$response["zipcode" . $count] = $db->array["zipcode"];
					$response["city" . $count] = $db->array["city"];
					$response["phone" . $count] = $db->array["phone"];
					$response["email" . $count] = $db->array["email"];
					$response["bank_regno" . $count] = $db->array["extra_bank_regno"];
					$response["bank_account" . $count] = $db->array["extra_bank_account"];
					$response["vat" . $count] = $db->array["vat"];
					$count++;
				}
			}
			$response["count"] = $count;
			$ajax->response($response);
		}
		
		$msg = new message;
		$msg->title("Opret bydernummer");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->submit_text = "Gem";
		$frm->tpl("th", "Opret bydernummer");
		$frm->input(
			"Bydernummer",
			"number",
			$db->execute_field("
				SELECT
					MAX(number)
				FROM
					" . $_table_prefix . "_module_" . $module . "_bidno
				") + 1
			);
		$frm->tpl("th", "Kundeinfo");
		$frm->tpl("td2", "Kunde-ID:", "<input type=\"text\" readonly name=\"cust_id\" id=\"cust_id\" value=\"0\" />");
		$frm->select(	
			"Type",
			"type",
			"",
			"",
			"",
			"",
			array(
				array("private", "Privat"),
				array("dealer", "Forhandler")
				)
			);
		$frm->input(
			"Navn / Søg",
			"name",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Adresse",
			"address",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Adresse 2",
			"address2",
			""
			);
		$frm->input(
			"Postnr",
			"zipcode",
			"",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"By",
			"city",
			"",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Telefon",
			"phone",
			"",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"E-mail",
			"email",
			"",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"CVR-nr. (hvis forhandler)", 
			"vat",
			"",
			"",
			($frm->values["type"] == "dealer" ? "^.+$" : ""),
			"Påkrævet"
			);

		if ($frm->done())
		{
			// Henter kunde
			$usrobject = new user($module . "_cust");
			
			if (!$id = $db->execute_field("
				SELECT
					id
				FROM
					" . $_table_prefix . "_user_" . $module . "_cust
				WHERE
					id = '" . intval($vars["cust_id"]) . "'
				"))
			{
				// Opret
				$username = OBA_id();
				while ($usrobject->get_user_from_username($username))
				{
					$username = OBA_id();
				}
				$password = create_password(6);
				$usrobject->ereg_username = ".";
				$usrobject->ereg_password = ".";
				$id = $usrobject->create($username, $password);
				if (!is_numeric($id)) die("Uventet fejl: $id");
			}
			
			$usrobject->update($id, array(
				"name" => $frm->values["name"],
				"address" => trim($frm->values["address"] . "\n" . $frm->values["address2"]),
				"zipcode" => $frm->values["zipcode"],
				"city" => $frm->values["city"],
				"phone" => $frm->values["phone"],
				"email" => $frm->values["email"],
				"vat" => $frm->values["vat"]
				));
		
			$usrobject->extra_set("type", $frm->values["type"], $id);
			
			if ($id) OBA_sync_sql_row($_table_prefix . "_user_" . $module . "_cust", $id);
			
			// Opretter bydernummer
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_bidno
				WHERE
					`number` = '" . intval($frm->values["number"]) . "'
				");
			$db->execute("
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_bidno
				(
					cust_id,
					number
				)
				VALUES
				(
					'$id',
					'" . intval($frm->values["number"]) . "'
				)
				");
				
			header("Location: ./?module=$module&page=$page&do=auctions_bidno");
			exit;
		}
		
		$html .= $frm->html();
		$html .= $ajax->html();
		$tmp = new tpl("MODULE|$module|admin_auctions_bidno");
		$tmp->set("ajax", $ajax->group);
		$html .= $tmp->html();
		
		// Oversigt over bydernumre
		$msg = new message;
		$msg->title("Oversigt");
		$html .= $msg->html();
		
		$links = new links;
		$links->link("Slet alle bydernumre", "auctions_bidno", "?do2=delete_all", "Slet alle bydernumre nu?");
		$html .= $links->html();
		
		$tbl = new table;
		$tbl->th("Bydernummer");
		$tbl->th("Navn");
		$tbl->th("Slet");
		$tbl->endrow();
		
		$ress = $db->execute("
			SELECT
				bidno.number,
				cust.name
			FROM
				" . $_table_prefix . "_module_" . $module . "_bidno AS bidno
			INNER JOIN
				" . $_table_prefix . "_user_" . $module . "_cust AS cust
			ON
				cust.id = bidno.cust_id
			ORDER BY
				number
			");
		while ($res = $db->fetch_array($ress))
		{
			$tbl->td($res["number"]);
			$tbl->td($res["name"]);
			$tbl->choise("Slet", "auctions_bidno", $res["number"] . "&do2=delete");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
		
	}
	elseif ($do == "auctions_add" or $do == "auctions_edit")
	{
		// Opret auktion
		
		$ajax = new ajax;
		if ($ajax->do == "lookup_regno")
		{
			$response = OBA_regno_lookup($ajax->values["regno"]);
			$response["state"] = "ok";
			$ajax->response($response);
		}
		elseif ($ajax->do == "search_cust")
		{
			$response = array("state" => "ok");
			$count = 0;
			$searchstring = trim($ajax->values["searchstring"]);
			if ($searchstring != "")
			{
				$db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_user_" . $module . "_cust
					WHERE
						name LIKE '%" . $db->escape($searchstring) . "%' OR
						phone LIKE '%" . $db->escape($searchstring) . "%' OR
						email LIKE '%" . $db->escape($searchstring) . "%'
					ORDER BY
						IF(name LIKE '" . $db->escape($searchstring) . "%', 0, 1),
						name
					LIMIT
						0, 25
					");
				while ($db->fetch_array())
				{
					$response["id" . $count] = $db->array["id"];
					$response["type" . $count] = $db->array["extra_type"];
					$response["name" . $count] = $db->array["name"];
					$response["address" . $count] = $db->array["address"];
					$response["zipcode" . $count] = $db->array["zipcode"];
					$response["city" . $count] = $db->array["city"];
					$response["phone" . $count] = $db->array["phone"];
					$response["email" . $count] = $db->array["email"];
					$response["bank_regno" . $count] = $db->array["extra_bank_regno"];
					$response["bank_account" . $count] = $db->array["extra_bank_account"];
					$response["vat" . $count] = $db->array["vat"];
					$count++;
				}
			}
			$response["count"] = $count;
			$ajax->response($response);
		}
		
		if ($do == "auctions_edit")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					id = '$id'
				");
			if (!$res = $db->fetch_array())
			{
				header("Location: /site/$_lang_id/$module/$page/auctions_overview");
				exit;
			}
		}
		else
		{
			$res = false;
		}
		
		// Kategorier
		$select_categories = array();
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_categories
			ORDER BY
				`type`
			");
		while ($db->fetch_array())
		{
			$select_categories[] = array($db->array["id"], $db->array["type"] . " - " . $db->array["title"]);
		}
		
		// Grupper
		$select_groups = array();
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_groups
			ORDER BY
				title
			");
		while ($db->fetch_array())
		{
			$select_groups[] = array($db->array["id"], $db->array["title"]);
		}
		
		// Typer
		$select_types = array();
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_types
			ORDER BY
				`title`
			");
		while ($db->fetch_array())
		{
			$select_types[] = array($db->array["id"], $db->array["title"]);
		}
		
		$msg = new message;
		$msg->title($do == "auctions_add" ? "Opret auktion" : "Rediger auktion");
		$html .= $msg->html();
		
		if ($do == "auctions_edit" and $res["end_time"] != "" and false)
		{
			// Viser info om auktion
			$html .= "Afsluttet";
		}
		else
		{
			// Rediger
			
			$frm = new form;
			$frm->hidden("tpl", $vars["tpl"]);
			if (!preg_match("/^(online|live)$/", $vars["add_type"])) $vars["add_type"] = "live";
			$frm->hidden("add_type", $vars["add_type"]);
			$frm->tpl("th", $do == "auctions_add" ? "Opret auktion" : "Rediger auktion");
			
			if ($do == "auctions_add")
			{
				if ($vars["add_type"] == "online")
				{
					// Online
					$frm->tpl("td2", "Auktionstype:", "Online auktion");
					$frm->tpl("td2", "Auktionsnr:", "<i>Endnu ikke tildelt</i>");
					// Henter mulige auktionsdatoer for online
					$sel_online_days = array();
					for ($i = 0; $i <= 7; $i++)
					{
						$ts = strtotime("+" . $i . " day");
						$db->execute("
							SELECT
								time,
								duration
							FROM
								" . $_table_prefix . "_module_" . $module . "_online_days
							WHERE
								weekday = '" . date("w", $ts) . "'
							ORDER BY
								time
							");
						while ($db->fetch_array())
						{
							$ts_from = strtotime(date("Y-m-d", $ts) . " " . substr($db->array["time"], 0, 5));
							$ts_to = strtotime(date("Y-m-d H:i", $ts_from) . " +" . $db->array["duration"] . " hour");
							$sel_online_days[] = array($ts_from . "-" . $ts_to, date("d-m-Y H:i", $ts_from) . " til " . date("d-m-Y H:i", $ts_to));
						}
					}
					$frm->select(
						"Auktionsdato",
						"auction_date",
						$date,
						$vars["auction_type"] == "online" ? "^[0-9]+-[0-9]+$" : "",
						"Påkrævet",
						$vars["auction_type"] == "online" ? '
							list($ts_from, $ts_to) = explode("-", $this->values["auction_date"]);
							$ts_from = intval($ts_from);
							$ts_to = intval($ts_to);
							if ($ts_from >= $ts_to) $error = "Påkrævet";
							if (date("Y-m-d", $ts_to) <= date("Y-m-d H:i")) $error = "Påkrævet";
						' : "",
						$sel_online_days
						);
				}
				else
				{
					// Fysisk
					$frm->tpl("td2", "Auktionstype:", "Fysisk auktion");
					$frm->tpl("td2", "Auktionsnr:", "<i>Endnu ikke tildelt</i>");
					$frm->input(
						"Auktionsdato",
						"auction_date",
						date("d-m-Y", strtotime($db->execute_field("
							SELECT
								MIN(`date`)
							FROM
								" . $_table_prefix . "_module_" . $module . "_dates
							WHERE
								`date` >= '" . date("Y-m-d") . "'
							ORDER BY
								`date`
							LIMIT 
								1
							"))),
						"^[0-9]{2}-[0-9]{2}-[0-9]{4}$",
						"Ugyldig dato - format: dd-mm-åååå",
						'
							$db = new db;
							$cnv = new convert;
							if (!$db->execute_field("
								SELECT
									`date`
								FROM
									' . $_table_prefix . '_module_' . $module . '_dates
								WHERE
									`date` = \'" . $db->escape($cnv->date_dk2uk($this->values["auction_date"])) . "\'
								"))
							{
								$error = "Auktionsdato findes ikke";
							}
						'
						);
				}
			}
			else
			{
				if ($res["auction_type"] == "online")
				{
					// Online
					$frm->tpl("td2", "Auktionstype:", "Online auktion");
					$frm->tpl("td2", "Auktionsnr:", $res["auction_no"]);
					$frm->tpl("td2", "Auktion-start:", date("d-m-Y H:i", strtotime($res["start_time"])));
					$frm->tpl("td2", "Auktion-slut:", date("d-m-Y H:i", strtotime($res["end_time"])));
				}
				else
				{
					// Fysisk
					$frm->tpl("td2", "Auktionstype:", "Fysisk auktion");
					$frm->tpl("td2", "Auktionsnr:", $res["auction_no"]);
					$frm->tpl("td2", "Auktionsdato:", date("d-m-Y", strtotime($res["auction_date"])));
				}
			}
			
			if ($res["auction_type"] != "online" and $vars["add_type"] != "online")
			{
				$frm->input(
					"Nøglenr",
					"keyno",
					$res["keyno"] > 0 ? $res["keyno"] : ""
					);
				$frm->select(
					"Kategori",
					"category_id",
					$res["category_id"],
					"",
					"",
					"",
					$select_categories
					);
				$frm->select(
					"Auktionstype",
					"type_id",
					$res["type_id"],
					"",
					"",
					"",
					$select_types
					);
			}
			else
			{
				$frm->select(
					"Gruppe",
					"group_id",
					$res["group_id"],
					"",
					"",
					"",
					$select_groups
					);
			}
			
			$frm->input(
				"Regnr.",
				"regno",
				stripslashes($res["regno"]),
				"",
				"",
				"",
				"<input type=\"button\" value=\"Slå op\" onclick=\"LookupRegno();\" id=\"buttonLookupRegno\" disabled />"
				);
			$frm->input(
				"Stelnr.",
				"chasno",
				stripslashes($res["chasno"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"Mærke",
				"brand",
				stripslashes($res["brand"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"Model",
				"model",
				stripslashes($res["model"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"Variant",
				"variant",
				stripslashes($res["variant"])
				);
			$frm->select(
				"Motorstørrelse",
				"motorsize",
				$res["motorsize"],
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_motorsize"), $res["motorsize"])
				);
			$frm->input(
				"Hestekræfter (HK)",
				"hp",
				$res["hp"],
				"^[0-9]*$",
				"Skal være et tal"
				);
			$frm->select(
				"Type",
				"type",
				stripslashes($res["type"]),
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_type"), $res["type"])
				);
			$frm->select(
				"Brændstof",
				"fuel",
				stripslashes($res["fuel"]),
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_fuel"), $res["fuel"])
				);
			$frm->input(
				"Døre",
				"doors",
				stripslashes($res["doors"])
				);
			$frm->select(
				"Geartype",
				"geartype",
				$res["geartype"],
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_geartype"), $res["geartype"])
				);
			$frm->select(
				"Gear",
				"gearcount",
				$res["gearcount"],
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_gearcount"), $res["gearcount"])
				);
			$frm->input(
				"Nypris",
				"new_price",
				$res["new_price"],
				"^[0-9]*$",
				"Skal være et tal"
				);
			$frm->select(
				"Hjultræk",
				"wheel_drive",
				$res["wheel_drive"],
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_wheel_drive"), $res["wheel_drive"])
				);
			$frm->input(
				"Årgang",
				"year",
				stripslashes($res["year"])
				);
			$frm->input(
				"Km-stand",
				"km",
				stripslashes($res["km"])
				);
			$frm->checkbox(
				"Dokumentation for kilometerstand haves",
				"km_doc",
				$res["km_doc"] == 1
				);
			$frm->input(
				"Farve",
				"color",
				stripslashes($res["color"])
				);
			$frm->select(
				"Moms",
				"no_vat",
				$res["no_vat"],
				"",
				"",
				"",
				array(
					array("1", "Ekskl. Moms"),
					array("0", "Momsfri")
					)
				);
			$frm->select(
				"Er bilen med afgift",
				"no_tax",
				$res["no_tax"],
				"",
				"",
				"",
				array(
					array("0", "Ja"),
					array("1", "Nej")
					)
				);
			$frm->checkbox(
				"Gul-plade",
				"yellow_plate",
				$res["yellow_plate"] == 1
				);
			$frm->checkbox(
				"Nysynet",
				"newly_tested",
				$res["newly_tested"] == 1
				);
			$frm->input(
				"Sidste syn",
				"newly_tested_date",
				$res["newly_tested_date"] != "" ? date("d-m-Y", strtotime($res["newly_tested_date"])) : "",
				"^([0-9]{2}-[0-9]{2}-[0-9]{4}){0,1}$",
				"Ugyldig dato - format: dd-mm-åååå",
				'
					if ($this->values["newly_tested"] != "" and $this->values["newly_tested_date"] == "")
					{
						$error = "Påkrævet ved nysynet";
					}
				'
				);
			$frm->select(
				"Status",
				"is_regged",
				$res["is_regged"],
				"",
				"",
				"",
				array(
					array("", "Vælg"),
					array("1", "Indregistreret"),
					array("0", "Afmeldt")
					)
				);
			$frm->input(
				"1. indreg. dato",
				"first_reg_date",
				$res["first_reg_date"] != "" ? date("d-m-Y", strtotime($res["first_reg_date"])) : "",
				"^([0-9]{2}-[0-9]{2}-[0-9]{4}){0,1}$",
				"Ugyldig dato - format: dd-mm-åååå"
				);
			$frm->checkbox(
				"Servicebog",
				"service",
				$res["service"] == 1
				);
			$frm->textarea(
				"Beskrivelse",
				"description",
				stripslashes($res["description"])
				);
			$frm->select(
				"Er der restgæld",
				"unpaid_debt",
				$res["unpaid_debt"],
				"",
				"",
				"",
				array(
					array("", "Vælg"),
					array("1", "Ja"),
					array("0", "Nej")
					)
				);
			$frm->input(
				"Mindstepris",
				"min_price",
				$res["min_price"]
				);
				
				
				
				
			/*
				UDSTYR
			*/
			$frm->tpl("th", "Udstyr standard/ekstra");
			$html_equipment = "<div class=\"equipmentWrapper\"><div class=\"equipmentColumn\">";
			$lines = explode("\n", module_setting("vars_equipment"));
			$count = 0;
			$res_equipment = unserialize($res["equipment"]);
			$vars_equipment = array(
				"equipment" => array(),
				"airbags" => $vars["equipment_airbags"],
				"comment" => $vars["equipment_comment"]
				);
			if ($frm->submitted) $res_equipment = $vars_equipment;
			for ($i = 0; $i < count($lines); $i++)
			{
				$line = trim($lines[$i]);
				if ($line != "")
				{
					if ($count >= count($lines) / 3)
					{
						$count = 0;
						$html_equipment .= "</div><div class=\"equipmentColumn\">";
					}
					
					$html_equipment .= "<div class=\"equipmentCell\"><label>$line <input type=\"checkbox\" name=\"equipment_" . md5($line) . "\" " .
						((in_array($line, $res_equipment["equipment"]) or $vars["equipment_" . md5($line)] != "") ? "checked" : "") . " /></label></div>";
						
					if ($vars["equipment_" . md5($line)] != "") $vars_equipment["equipment"][] = $line;
					
					$count++;
				}
			}
			$html_equipment .= "</div></div>";
			$frm->tpl("td", $html_equipment);
			$frm->select(
				"Antal airbags",
				"equipment_airbags",
				$res_equipment["airbags"],
				"",
				"",
				"",
				OBA_list2select(module_setting("vars_equipment_airbags"), $res_equipment["airbags"])
				);
			$frm->textarea(
				"Evt. bemærkninger",
				"equipment_comment",
				$res_equipment["comment"]
				);
				
				
				
			/*
				DÆK
			*/
			$frm->tpl("th", "Monteret dæk og fælge");
			$res_tires = unserialize($res["tires"]);
			$vars_tires = array(
				"type" => $vars["tires_type"],
				"rim" => $vars["tires_rim"],
				"depth_front" => $vars["tires_depth_front"],
				"depth_back" => $vars["tires_depth_back"],
				"type_extra" => $vars["tires_type_extra"],
				"rim_extra" => $vars["tires_rim_extra"],
				"depth_front_extra" => $vars["tires_depth_front_extra"],
				"depth_back_extra" => $vars["tires_depth_back_extra"]
				);
			if ($frm->submitted) $res_tires = $vars_tires;
			$frm->tpl("td2", "
				<div class=\"SmallSelect\">Monteret dæk <select name=\"tires_type\">" . OBA_list2select_html(module_setting("vars_tires_type"), $res_tires["type"]) . "</select></div>
				<div class=\"SmallSelect\">Monteret fælge <select name=\"tires_rim\">" . OBA_list2select_html(module_setting("vars_tires_rim"), $res_tires["rim"]) . "</select></div>
				<div class=\"SmallSelect\">Mønster fordæk <select name=\"tires_depth_front\">" . OBA_list2select_html(module_setting("vars_tires_depth"), $res_tires["depth_front"]) . "</select></div>
				<div class=\"SmallSelect\">Mønster bagdæk <select name=\"tires_depth_back\">" . OBA_list2select_html(module_setting("vars_tires_depth"), $res_tires["depth_back"]) . "</select></div>
				", "
				<div class=\"SmallSelect\">Ekstra dæk <select name=\"tires_type_extra\">" . OBA_list2select_html(module_setting("vars_tires_type"), $res_tires["type_extra"]) . "</select></div>
				<div class=\"SmallSelect\">Ekstra fælge <select name=\"tires_rim_extra\">" . OBA_list2select_html(module_setting("vars_tires_rim"), $res_tires["rim_extra"]) . "</select></div>
				<div class=\"SmallSelect\">Mønster fordæk <select name=\"tires_depth_front_extra\">" . OBA_list2select_html(module_setting("vars_tires_depth"), $res_tires["depth_front_extra"]) . "</select></div>
				<div class=\"SmallSelect\">Mønster bagdæk <select name=\"tires_depth_back_extra\">" . OBA_list2select_html(module_setting("vars_tires_depth"), $res_tires["depth_back_extra"]) . "</select></div>
				");
			
			
			
			
			/*
				STAND
			*/
			$frm->tpl("th", "Stand");
			$res_condition = unserialize($res["condition"]);
			$vars_condition = array(
				"inside" => $vars["condition_inside"],
				"mecanical" => $vars["condition_mecanical"],
				"lacquer" => $vars["condition_lacquer"],
				"light_front" => $vars["condition_light_front"],
				"light_back" => $vars["condition_light_back"],
				"light_fog" => $vars["condition_light_fog"],
				"damage" => $vars["condition_damage"],
				"electric" => $vars["condition_electric"]
				);
			if ($frm->submitted) $res_condition = $vars_condition;
			$frm->tpl("td2", "
				<div class=\"SmallSelect\">Indvendig stand <select name=\"condition_inside\">" . OBA_list2select_html(module_setting("vars_condition_inside"), $res_condition["inside"]) . "</select></div>
				<div class=\"SmallSelect\">Mekanisk stand <select name=\"condition_mecanical\">" . OBA_list2select_html(module_setting("vars_condition_mecanical"), $res_condition["mecanical"]) . "</select></div>
				<div class=\"SmallSelect\">Lak stand <select name=\"condition_lacquer\">" . OBA_list2select_html(module_setting("vars_condition_lacquer"), $res_condition["lacquer"]) . "</select></div>
				<div class=\"SmallSelect\">Forlygter <select name=\"condition_light_front\">" . OBA_list2select_html(module_setting("vars_condition_light_front"), $res_condition["light_front"]) . "</select></div>
				<div class=\"SmallSelect\">Baglygter <select name=\"condition_light_back\">" . OBA_list2select_html(module_setting("vars_condition_light_back"), $res_condition["light_back"]) . "</select></div>
				<div class=\"SmallSelect\">Tågelygter <select name=\"condition_light_fog\">" . OBA_list2select_html(module_setting("vars_condition_light_fog"), $res_condition["light_fog"]) . "</select></div>
				", "
				<div class=\"SmallSelect\">Tidligere skadet <select name=\"condition_damage\">" . OBA_list2select_html(module_setting("vars_condition_damage"), $res_condition["damage"]) . "</select></div>
				<div class=\"SmallSelect\">Elektrisk stand <select name=\"condition_electric\">" . OBA_list2select_html(module_setting("vars_condition_electric"), $res_condition["electric"]) . "</select></div>
				");
			
			
			/*
				VEDLIGEHOLDELSE
			*/
			$frm->tpl("th", "Vedligeholdelse");
			$res_maintain = unserialize($res["maintain"]);
			$vars_maintain = array(
				"book" => $vars["maintain_book"],
				"service_ok" => $vars["maintain_service_ok"],
				"rust_treat" => $vars["maintain_rust_treat"],
				"brake" => $vars["maintain_brake"],
				"last_service" => $vars["maintain_last_service"],
				"next_service" => $vars["maintain_next_service"],
				"timing_belt" => $vars["maintain_timing_belt"],
				"oil" => $vars["maintain_oil"],
				"comment" => $vars["maintain_comment"]
				);
			if ($frm->submitted) $res_maintain = $vars_maintain;
			$frm->tpl("td2", "
				<div class=\"SmallSelect\">Medfølger servicebog <select name=\"maintain_book\">" . OBA_list2select_html(module_setting("vars_maintain_book"), $res_maintain["book"]) . "</select></div>
				<div class=\"SmallSelect\">Service overholdt <select name=\"maintain_service_ok\">" . OBA_list2select_html(module_setting("vars_maintain_service_ok"), $res_maintain["service_ok"]) . "</select></div>
				<div class=\"SmallSelect\">Undervognsbehandlet <select name=\"maintain_rust_treat\">" . OBA_list2select_html(module_setting("vars_maintain_rust_treat"), $res_maintain["rust_treat"]) . "</select></div>
				<div class=\"SmallSelect\">Bremseklodser skiftet <select name=\"maintain_brake\">" . OBA_numberselect_html(1000, 1000000, $res_maintain["brake"], 1000) . "</select></div>
				", "
				<div class=\"SmallSelect\">Sidste service <select name=\"maintain_last_service\">" . OBA_numberselect_html(1000, 1000000, $res_maintain["last_service"], 1000) . "</select></div>
				<div class=\"SmallSelect\">Næste service <select name=\"maintain_next_service\">" . OBA_numberselect_html(1000, 1000000, $res_maintain["next_service"], 1000) . "</select></div>
				<div class=\"SmallSelect\">Tandrem skiftet <select name=\"maintain_timing_belt\">" . OBA_numberselect_html(1000, 1000000, $res_maintain["timing_belt"], 1000) . "</select></div>
				<div class=\"SmallSelect\">Olie skiftet <select name=\"maintain_oil\">" . OBA_numberselect_html(1000, 1000000, $res_maintain["oil"], 1000) . "</select></div>
				");
			$frm->textarea(
				"Evt. bemærkninger",
				"maintain_comment",
				$res_maintain["comment"]
				);
			
			
			
			
			/*
				UDVENDIG STAND
			*/
			$frm->tpl("th", "Udvendig stand");
			$res_exterior = unserialize($res["exterior"]);
			$arr_fields = array(
			
				/* FORAN */
			
				array(
					"windshield",
					"Forrude",
					array(
						array("windshield", "Stand", "windshield")
						)
					),
					
				array(
					"hood",
					"Motorhjelm",
					array(
						array("hood_dent", "Buler", "dent"),
						array("hood_scratch", "Ridser", "scratch"),
						array("hood_rust", "Rust", "rust"),
						array("hood_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"bumper_front",
					"Forkofanger",
					array(
						array("bumper_front", "Stand", "condition")
						)
					),
					
				/* HØJRE */
						
				array(
					"fender_front_right",
					"Højre forskærm",
					array(
						array("fender_front_right_dent", "Buler", "dent"),
						array("fender_front_right_scratch", "Ridser", "scratch"),
						array("fender_front_right_rust", "Rust", "rust"),
						array("fender_front_right_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"door_front_right",
					"Højre fordør",
					array(
						array("door_front_right_dent", "Buler", "dent"),
						array("door_front_right_scratch", "Ridser", "scratch"),
						array("door_front_right_rust", "Rust", "rust"),
						array("door_front_right_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"door_back_right",
					"Højre bagdør",
					array(
						array("door_back_right_dent", "Buler", "dent"),
						array("door_back_right_scratch", "Ridser", "scratch"),
						array("door_back_right_rust", "Rust", "rust"),
						array("door_back_right_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"fender_back_right",
					"Højre bagdør",
					array(
						array("fender_back_right_dent", "Buler", "dent"),
						array("fender_back_right_scratch", "Ridser", "scratch"),
						array("fender_back_right_rust", "Rust", "rust"),
						array("fender_back_right_stone", "Stenslag", "stone")
						)
					),
					
				array(
					"mirror_right",
					"Højre sidespejl",
					array(
						array("mirror_right_scratch", "Ridser", "mirror_scratch"),
						array("mirror_right_stone", "Stenslag", "mirror_stone"),
						array("mirror_right_glass", "Spejlglas", "mirror_glass")
						)
					),
				
				array(
					"panel_right",
					"Højre dørpanel",
					array(
						array("panel_right_dent", "Buler", "dent"),
						array("panel_right_rust", "Rust", "rust"),
						array("panel_right_stone", "Stenslag", "stone")
						)
					),
					
				/* VENSTRE */
						
				array(
					"fender_front_left",
					"Venstre forskærm",
					array(
						array("fender_front_left_dent", "Buler", "dent"),
						array("fender_front_left_scratch", "Ridser", "scratch"),
						array("fender_front_left_rust", "Rust", "rust"),
						array("fender_front_left_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"door_front_left",
					"Venstre fordør",
					array(
						array("door_front_left_dent", "Buler", "dent"),
						array("door_front_left_scratch", "Ridser", "scratch"),
						array("door_front_left_rust", "Rust", "rust"),
						array("door_front_left_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"door_back_left",
					"Venstre bagdør",
					array(
						array("door_back_left_dent", "Buler", "dent"),
						array("door_back_left_scratch", "Ridser", "scratch"),
						array("door_back_left_rust", "Rust", "rust"),
						array("door_back_left_stone", "Stenslag", "stone")
						)
					),
						
				array(
					"fender_back_left",
					"Venstre bagdør",
					array(
						array("fender_back_left_dent", "Buler", "dent"),
						array("fender_back_left_scratch", "Ridser", "scratch"),
						array("fender_back_left_rust", "Rust", "rust"),
						array("fender_back_left_stone", "Stenslag", "stone")
						)
					),
					
				array(
					"mirror_left",
					"Venstre sidespejl",
					array(
						array("mirror_left_scratch", "Ridser", "mirror_scratch"),
						array("mirror_left_stone", "Stenslag", "mirror_stone"),
						array("mirror_left_glass", "Spejlglas", "mirror_glass")
						)
					),
				
				array(
					"panel_left",
					"Venstre dørpanel",
					array(
						array("panel_left_dent", "Buler", "dent"),
						array("panel_left_rust", "Rust", "rust"),
						array("panel_left_stone", "Stenslag", "stone")
						)
					),
					
				/* BAG */
				
				array(
					"bumper_back",
					"Bagkofanger",
					array(
						array("bumper_back", "Stand", "condition")
						)
					),
					
				array(
					"door_back",
					"Bagklap",
					array(
						array("door_back_dent", "Buler", "dent"),
						array("door_back_rust", "Rust", "rust"),
						array("door_back_stone", "Stenslag", "stone")
						)
					),
					
				/* TAG */
				
				array(
					"roof",
					"Tag",
					array(
						array("roof_dent", "Buler", "dent"),
						array("roof_rust", "Rust", "rust"),
						array("roof_stone", "Stenslag", "stone")
						)
					),
				
				/* UNDERVOGN */
						
				array(
					"under",
					"Undervogn",
					array(
						array("under", "Rust", "rust"),
						)
					)
					
				);
			$vars_exterior = array();
			$html_exterior = "";
			for ($i = 0; $i < count($arr_fields); $i++)
			{
				list($name, $title, $fields) = $arr_fields[$i];
				
				$html_exterior .= "<div class=\"exteriorRow\"><div class=\"exteriorTitle\">$title<br>" .
					"<img src=\"/modules/$module/img/" . $name . ".png\" /></div><div class=\"exteriorColumn\">";
				
				$count = 0;
				for ($j = 0; $j < count($fields); $j++)
				{
					list($fieldname, $fieldtitle, $fieldsource) = $fields[$j];
					
					if ($count >= count($fields) / 2 and $count > 0)
					{
						$html_exterior .= "</div><div class=\"exteriorColumn\">";
						$count = 0;
					}
					
					$vars_exterior[$fieldname] = $vars["exterior_" . $fieldname];
					if ($frm->submitted) $res_exterior[$fieldname] = $vars["exterior_" . $fieldname];
					
					$html_exterior .= "<div class=\"exteriorCell\">$fieldtitle <select name=\"exterior_" . $fieldname . "\">" .
						OBA_list2select_html(module_setting("vars_exterior_" . $fieldsource), $res_exterior[$fieldname]) . "</select></div>";
					
					$count++;
				}
				
				$html_exterior .= "</div><div class=\"clear\"></div></div>";
			}
			$frm->tpl("td", $html_exterior);
				
				
			

				
				
				
			
			$frm->tpl("th", "Sælger");
			$frm->tpl("td2", "Kunde-ID:", "<input type=\"text\" readonly name=\"seller_id\" id=\"seller_id\" value=\"" . (isset($vars["seller_id"]) ? $vars["seller_id"] : $res["seller_id"]) . "\" />");
			$frm->select(	
				"Type",
				"seller_type",
				$res["seller_type"],
				"",
				"",
				"",
				array(
					array("private", "Privat"),
					array("dealer", "Forhandler")
					)
				);
			$frm->input(
				"Navn / Søg",
				"seller_name",
				stripslashes($res["seller_name"]),
				"^.+$",
				"Påkrævet"
				);
			list($a1, $a2) = explode("\n", stripslashes($res["seller_address"]));
			$frm->input(
				"Adresse",
				"seller_address",
				$a1,
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"Adresse 2",
				"seller_address2",
				$a2
				);
			$frm->input(
				"Postnr.",
				"seller_zipcode",
				stripslashes($res["seller_zipcode"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"By",
				"seller_city",
				stripslashes($res["seller_city"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"Telefon",
				"seller_phone",
				stripslashes($res["seller_phone"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"E-mail",
				"seller_email",
				stripslashes($res["seller_email"]),
				"^.+$",
				"Påkrævet"
				);
			$frm->input(
				"Bank - Reg.nr.",
				"seller_bank_regno",
				stripslashes($res["seller_bank_regno"])
				);
			$frm->input(
				"Bank - Kontonr.",
				"seller_bank_account",
				stripslashes($res["seller_bank_account"])
				);
			$frm->input(
				"CVR-nr. (hvis forhandler)",
				"seller_vat",
				stripslashes($res["seller_vat"])
				);
				
			$frm->tpl("th", "Billeder");
			$tmphtml = "";
			$ressimages = $db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_images
				WHERE
					auction_id = '$id'
				");
			while ($resimage = $db->fetch_array($ressimages))
			{
				$tmphtml .= "<div style=\"float: left; width: 100px; height: 120px; margin: 10px; text-align: center; border: 1px solid #a0a0a0;\">" .
					"<div style=\"width: 100px; height: 100px; background-image: url(/modules/$module/upl/image_" . $resimage["id"] . "_thumb.jpg); " .
					"background-size: contain; background-position: 50% 50%; background-repeat: no-repeat;\"></div>" .
					"<input type=\"checkbox\" name=\"delete_image_" . $resimage["id"] . "\"> Slet" .
					"</div>";
			}
			$frm->tpl("td", $tmphtml);
			
			$frm->enctype = "multipart/form-data";
			$frm->tpl("td", "Upload JPG-billeder, max " . ini_get("max_file_uploads") . " billeder á max " . ini_get("upload_max_filesize") . "b: " .
				"<input type=\"file\" name=\"upload_image[]\" multiple>");
			
			if ($res["end_time"] != "")
			{
				$frm->tpl("th", "Bud-historik");
				if ($res["bidder_id"] != 0)
				{
					$db->execute("
						SELECT
							*
						FROM
							" . $_table_prefix . "_user_" . $module . "_cust
						WHERE
							id = '" . $res["bidder_id"] . "'
						");
					$cust = $db->fetch_array();
					$frm->tpl("td2", "Højeste bydende:", $cust ? $cust["name"] : "-");
					$frm->input(
						"Højeste bud",
						"cur_price",
						$res["cur_price"],
						"^[0-9]+$",
						"Skal være et tal"
						);

					$tbl = new table;
					$tbl->th("Byder");
					$tbl->th("Type");
					$tbl->th("Bud");
					$tbl->endrow();
					$ress3 = $db->execute("
						SELECT
							b.*,
							c.name
						FROM
							" . $_table_prefix . "_module_" . $module . "_bids AS b
						LEFT JOIN
							" . $_table_prefix . "_user_" . $module . "_cust AS c
						ON
							c.id = b.bidder_id
						WHERE
							b.auction_id = '" . $res["id"] . "'
						ORDER BY
							b.counter DESC
						");
					while ($res3 = $db->fetch_array($ress3))
					{
						$tbl->td($res3["name"]);
						$tbl->td($res3["type"]);
						$tbl->td($res3["bid"]);
						$tbl->endrow();
					}
					$frm->tpl("td", $tbl->html());
				}
				else
				{
					$frm->tpl("td", "<i>Der er ingen bud på auktionen</i>");
				}
			}
			
				
			if ($frm->done())
			{
				$cnv = new convert;
				if ($do == "auctions_add")
				{
					// Opretter auktion
					$id = OBA_id();
					
					// Dato
					if ($vars["add_type"] == "online")
					{
						// Online
						list($ts_from, $ts_to) = explode("-", $frm->values["auction_date"]);
						$ts_from = intval($ts_from);
						$ts_to = intval($ts_to);
						$auction_date = date("Y-m-d", $ts_from);
						$start_time = "'" . date("Y-m-d H:i", $ts_from) . "'";
						$end_time = "'" . date("Y-m-d H:i", $ts_to) . "'";
					}
					else
					{
						// Fysisk
						$auction_date = $db->escape($cnv->date_dk2uk($frm->values["auction_date"]));
						$start_time = "NULL";
						$end_time = "NULL";
					}
					
					$sql = "
						INSERT INTO
							" . $_table_prefix . "_module_" . $module . "_auctions
						(
							`id`, 
							`auction_date`,
							`regno`,
							`chasno`,
							`brand`,
							`model`,
							`variant`,
							`type`,
							`fuel`,
							`doors`,
							`year`,
							`km`,
							`color`,
							no_vat,
							no_tax,
							yellow_plate,
							`newly_tested`,
							`newly_tested_date`,
							`is_regged`,
							`description`,
							`min_price`,
							seller_id,
							seller_type,
							seller_name,
							seller_address,
							seller_zipcode,
							seller_city,
							seller_phone,
							seller_email,
							seller_vat,
							seller_bank_regno,
							seller_bank_account,
							category_id,
							type_id,
							keyno,
							first_reg_date,
							`service`,
							
							`start_time`,
							`end_time`,
							`auction_type`,
							`unpaid_debt`,
							`equipment`,
							`tires`,
							`condition`,
							`maintain`,
							`exterior`,
							`motorsize`,
							`hp`,
							`geartype`,
							`gearcount`,
							`new_price`,
							`wheel_drive`,
							`km_doc`,
							group_id
						)
						VALUES
						(
							'$id',
							'" . $db->escape($cnv->date_dk2uk($auction_date)) . "',
							'" . $db->escape($frm->values["regno"]) . "',
							'" . $db->escape($frm->values["chasno"]) . "',
							'" . $db->escape($frm->values["brand"]) . "',
							'" . $db->escape($frm->values["model"]) . "',
							'" . $db->escape($frm->values["variant"]) . "',
							'" . $db->escape($frm->values["type"]) . "',
							'" . $db->escape($frm->values["fuel"]) . "',					
							'" . $db->escape($frm->values["doors"]) . "',
							'" . $db->escape($frm->values["year"]) . "',
							'" . $db->escape($frm->values["km"]) . "',
							'" . $db->escape($frm->values["color"]) . "',
							'" . $db->escape($frm->values["no_vat"]) . "',
							'" . $db->escape($frm->values["no_tax"]) . "',
							'" . $db->escape($frm->values["yellow_plate"] != "" ? 1 : 0) . "',
							'" . $db->escape($frm->values["newly_tested"] != "" ? 1 : 0) . "',
							" . ($frm->values["newly_tested_date"] != "" ? date("'Y-m-d'", strtotime($cnv->date_dk2uk($frm->values["newly_tested_date"]))) : "NULL") . ",
							'" . $db->escape($frm->values["is_regged"] != "" ? 1 : 0) . "',
							'" . $db->escape($frm->values["description"]) . "',
							'" . $db->escape($frm->values["min_price"]) . "',
							'" . $db->escape($frm->values["seller_id"]) . "',
							'" . $db->escape($frm->values["seller_type"]) . "',
							'" . $db->escape($frm->values["seller_name"]) . "',
							'" . $db->escape(trim($frm->values["seller_address"] . "\n" . $frm->values["seller_address2"])) . "',
							'" . $db->escape($frm->values["seller_zipcode"]) . "',
							'" . $db->escape($frm->values["seller_city"]) . "',
							'" . $db->escape($frm->values["seller_phone"]) . "',
							'" . $db->escape($frm->values["seller_email"]) . "',
							'" . $db->escape($frm->values["seller_vat"]) . "',
							'" . $db->escape($frm->values["seller_bank_regno"]) . "',
							'" . $db->escape($frm->values["seller_bank_account"]) . "',
							'" . $db->escape($frm->values["category_id"]) . "',
							'" . $db->escape($frm->values["type_id"]) . "',
							'" . intval($frm->values["keyno"]) . "',
							" . ($frm->values["first_reg_date"] != "" ? date("'Y-m-d'", strtotime($cnv->date_dk2uk($frm->values["first_reg_date"]))) : "NULL") . ",
							'" . ($frm->values["service"] != "" ? 1 : 0) . "',
							
							$start_time,
							$end_time,
							'" . $db->escape($vars["add_type"]) . "',
							'" . ($frm->values["unpaid_debt"] != "" ? 1 : 0) . "',
							'" . $db->escape(serialize($vars_equipment)) . "',
							'" . $db->escape(serialize($vars_tires)) . "',
							'" . $db->escape(serialize($vars_condition)) . "',
							'" . $db->escape(serialize($vars_maintain)) . "',
							'" . $db->escape(serialize($vars_exterior)) . "',
							'" . $db->escape($frm->values["motorsize"]) . "',
							'" . $db->escape($frm->values["hp"]) . "',
							'" . $db->escape($frm->values["geartype"]) . "',
							'" . $db->escape($frm->values["gearcount"]) . "',
							" . (is_numeric($frm->values["new_price"]) ? intval($frm->values["new_price"]) : "NULL") . ",
							'" . $db->escape($frm->values["wheel_drive"]) . "',
							'" . $db->escape($frm->values["km_doc"] != "" ? 1 : 0) . "',
							'" . intval($frm->values["group_id"]) . "'
						)
						";
					$db->execute($sql);
					OBA_sync("SQL", $sql);
					if ($res["auction_no"] == "") OBA_auction_no($id);
				}
				else
				{
					$sql_update = "";
					if ($res["end_time"] != "" and $res["bidder_id"] != 0) $sql_update .= ", cur_price = '" . intval($frm->values["cur_price"]) . "' ";

					$sql = "
						UPDATE
							" . $_table_prefix . "_module_" . $module . "_auctions
						SET
							regno = '" . $db->escape($frm->values["regno"]) . "',
							chasno = '" . $db->escape($frm->values["chasno"]) . "',
							brand = '" . $db->escape($frm->values["brand"]) . "',
							model = '" . $db->escape($frm->values["model"]) . "',
							`variant` = '" . $db->escape($frm->values["variant"]) . "',
							`type` = '" . $db->escape($frm->values["type"]) . "',
							fuel = '" . $db->escape($frm->values["fuel"]) . "',					
							doors = '" . $db->escape($frm->values["doors"]) . "',
							`year` = '" . $db->escape($frm->values["year"]) . "',
							km = '" . $db->escape($frm->values["km"]) . "',
							color = '" . $db->escape($frm->values["color"]) . "',
							no_vat = '" . $db->escape($frm->values["no_vat"]) . "',
							no_tax = '" . $db->escape($frm->values["no_tax"]) . "',
							yellow_plate = '" . $db->escape($frm->values["yellow_plate"] != "" ? 1 : 0) . "',
							newly_tested = '" . $db->escape($frm->values["newly_tested"] != "" ? 1 : 0) . "',
							newly_tested_date = " . ($frm->values["newly_tested_date"] != "" ? date("'Y-m-d'", strtotime($cnv->date_dk2uk($frm->values["newly_tested_date"]))) : "NULL") . ",
							is_regged = '" . $db->escape($frm->values["is_regged"] != "" ? 1 : 0) . "',
							description = '" . $db->escape($frm->values["description"]) . "',
							min_price = '" . $db->escape($frm->values["min_price"]) . "',
							seller_id = '" . $db->escape($frm->values["seller_id"]) . "',
							seller_type = '" . $db->escape($frm->values["seller_type"]) . "',
							seller_name = '" . $db->escape($frm->values["seller_name"]) . "',
							seller_address = '" . $db->escape(trim($frm->values["seller_address"] . "\n" . $frm->values["seller_address2"])) . "',
							seller_zipcode = '" . $db->escape($frm->values["seller_zipcode"]) . "',
							seller_city = '" . $db->escape($frm->values["seller_city"]) . "',
							seller_phone = '" . $db->escape($frm->values["seller_phone"]) . "',
							seller_email = '" . $db->escape($frm->values["seller_email"]) . "',
							seller_vat = '" . $db->escape($frm->values["seller_vat"]) . "',
							seller_bank_regno = '" . $db->escape($frm->values["seller_bank_regno"]) . "',
							seller_bank_account = '" . $db->escape($frm->values["seller_bank_account"]) . "',
							category_id = '" . $db->escape($frm->values["category_id"]) . "',
							type_id = '" . $db->escape($frm->values["type_id"]) . "',
							keyno = '" . intval($frm->values["keyno"]) . "',
							first_reg_date = " . ($frm->values["first_reg_date"] != "" ? date("'Y-m-d'", strtotime($cnv->date_dk2uk($frm->values["first_reg_date"]))) : "NULL") . ",
						 	`service` = '" . ($frm->values["service"] != "" ? 1 : 0) . "',
							
							`unpaid_debt` = '" . ($frm->values["unpaid_debt"] != "" ? 1 : 0) . "',
							`equipment` = '" . $db->escape(serialize($vars_equipment)) . "',
							`tires` = '" . $db->escape(serialize($vars_tires)) . "',
							`condition` = '" . $db->escape(serialize($vars_condition)) . "',
							`maintain` = '" . $db->escape(serialize($vars_maintain)) . "',
							`exterior` = '" . $db->escape(serialize($vars_exterior)) . "',
							`motorsize` = '" . $db->escape($frm->values["motorsize"]) . "',
							`hp` = '" . $db->escape($frm->values["hp"]) . "',
							`geartype` = '" . $db->escape($frm->values["geartype"]) . "',
							`gearcount` = '" . $db->escape($frm->values["gearcount"]) . "',
							`new_price` = " . (is_numeric($frm->values["new_price"]) ? intval($frm->values["new_price"]) : "NULL") . ",
							`wheel_drive` = '" . $db->escape($frm->values["wheel_drive"]) . "',
							`km_doc` = '" . $db->escape($frm->values["km_doc"] != "" ? 1 : 0) . "',
							group_id = '" . intval($frm->values["group_id"]) . "'
							
							$sql_update
						WHERE
							id = '$id'
						";
					$db->execute($sql);
					OBA_sync("SQL", $sql);
				}
				
				$ressimages = $db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_module_" . $module . "_images
					WHERE
						auction_id = '$id'
					");
				while ($resimage = $db->fetch_array($ressimages))
				{
					if ($frm->values["delete_image_" . $resimage["id"]] != "")
					{
						unlink($_document_root . "/modules/$module/upl/image_" . $resimage["id"] . ".jpg");
						unlink($_document_root . "/modules/$module/upl/image_" . $resimage["id"] . "_thumb.jpg");
						OBA_sync("DELETE_FILE", "image_" . $resimage["id"] . ".jpg");
						OBA_sync("DELETE_FILE", "image_" . $resimage["id"] . "_thumb.jpg");
						$sql = "
							DELETE FROM
								" . $_table_prefix . "_module_" . $module . "_images
							WHERE
								id = '" . $resimage["id"] . "'
							";
						$db->execute($sql);
						OBA_sync("SQL", $sql);
					}
				}
				
				$image = new image;
				$tmpimg = $_document_root . "/tmp/" . uniqid(time()) . ".jpg";
				$order = $db->execute_field("
					SELECT
						MAX(`order`)
					FROM
						" . $_table_prefix . "_module_" . $module . "_images
					WHERE
						auction_id = '$id'
					") + 1;
				for ($i = 0; $i < 20; $i++)
				{
					if (is_uploaded_file($_FILES["upload_image"]["tmp_name"][$i]))
					{
						if (is_file($tmpimg)) unlink($tmpimg);
						move_uploaded_file($_FILES["upload_image"]["tmp_name"][$i], $tmpimg);
						if (
								$img = imagecreatefromjpeg($tmpimg)
								or
								$img = imagecreatefrompng($tmpimg)
								or
								$img = imagecreatefromgif($tmpimg)
							)
						{
							$imgid = OBA_id();
							$sql = "
								INSERT INTO
									" . $_table_prefix . "_module_" . $module . "_images
								(
									id,
									auction_id,
									`order`
								)
								VALUES
								(
									'$imgid',
									'$id',
									'$order'
								)
								";
							$db->execute($sql);
							OBA_sync("SQL", $sql);
							
							imagejpeg($image->imagemaxsize($img, 1000, 1000), $_document_root . "/modules/$module/upl/image_" . $imgid . ".jpg");
							imagejpeg($image->imagemaxsize($img, 200, 200), $_document_root . "/modules/$module/upl/image_" . $imgid . "_thumb.jpg");
							imagedestroy($img);
							
							OBA_sync("SAVE_FILE", "image_" . $imgid . ".jpg");
							OBA_sync("SAVE_FILE", "image_" . $imgid . "_thumb.jpg");
							
							$order++;
						}
						unlink($tmpimg);
					}
				}
				
				$frm->cleanup();
				
				if ($vars["tpl"] == "popup")
				{
					echo("<script> parent.document.location.href = parent.document.location.href; </script>");
				}
				else
				{
					if ($res["auction_type"] == "online" or $vars["add_type"] == "online")
					{
						header("Location: /site/$_lang_id/$module/$page/auctions_online");
					}
					else
					{
						header("Location: /site/$_lang_id/$module/$page/auctions_overview");
					}
				}
				exit;
			}
			
			$html .= $frm->html();
			$html .= $ajax->html();
			$tmp = new tpl("MODULE|$module|admin_auctions_edit");
			$tmp->set("ajax", $ajax->group);
			$html .= $tmp->html();
		}
		
	}
	elseif ($do == "auctions_dates")
	{
		// Auktionsdatoer
	
		$msg = new message;
		$msg->title("Auktionsdatoer");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->submit_text = "Tilføj";
		$frm->tpl("th", "Tilføj auktionsdato");
		$frm->input(
			"Dato",
			"date",
			"",
			"^[0-9]{2}-[0-9]{2}-[0-9]{4}$",
			"Ugyldig dato - format: dd-mm-åååå",
			'
				$cnv = new convert;
				if ($cnv->date_dk2uk($this->values["date"]) < date("Y-m-d"))
				{
					$error = "Dato må ikke være før i tiden";
				}
			'
			);
		if ($frm->done())
		{
			$cnv = new convert;
			$sql = "
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_dates
				(
					`date`
				)
				VALUES
				(
					'" . $db->escape($cnv->date_dk2uk($frm->values["date"])) . "'
				)
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}
		$html .= $frm->html();
			
		$html .= "
<script>
$(document).ready(function() {
	$('#date').datepicker({
		dateFormat: 'dd-mm-yy',
		minDate: '+0',
		maxDate: '+180',
		dayNamesMin: ['Sø', 'Ma', 'Ti', 'On', 'To', 'Fr', 'Lø', 'Sø'],
		monthNames: ['Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December'],
		firstDay: 1
	});
});		
</script>
			";	
			
		// Datoer
		
		if ($vars["do2"] == "delete")
		{
			$sql = "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_dates
				WHERE
					`date` = '" . $db->escape($vars["id"]) . "'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
			
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}		
		
		$tbl = new table;
		$tbl->th("Dato");
		$tbl->th("Auktioner");
		$tbl->th("Slet");
		$tbl->endrow();
		
		$ress = $db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_dates
			WHERE
				`date` >= '" . date("Y-m-d") . "'
			ORDER BY
				`date`
			");
		while ($res = $db->fetch_array($ress))
		{
			$count = $db->execute_field("
				SELECT
					COUNT(*)
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					auction_date = '" . $res["date"] . "' AND
					auction_type = 'live'
				");
			$tbl->td(date("d-m-Y", strtotime($res["date"])));
			$tbl->td($count);
			$tbl->choise("Slet", "auctions_dates", $res["date"] . "&do2=delete", "Slet auktionsdato " . date("d-m-Y", strtotime($res["date"])) . "?");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
	}
	elseif ($do == "auctions_delete")
	{
		$auction_type = $db->execute_field("
			SELECT
				auction_type
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				id = '$id'
			");
		$sql = "
			DELETE FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				id = '$id' AND
				(
					ISNULL(end_time) OR
					`cancel` = 1 OR
					auction_type = 'online' AND
					(
						start_time > '" . date("Y-m-d H:i:s") . "'
						OR
						end_time < '" . date("Y-m-d H:i:s") . "'
					)
				)
			";
		$db->execute($sql);
		if ($db->affected_rows() == 1)
		{
			OBA_sync("SQL", "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					id = '$id'
				");
			
			$ress = $db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_images
				WHERE
					auction_id = '$id'
				");
			while ($res = $db->fetch_array($ress))
			{
				unlink($_document_root . "/modules/$module/upl/image_" . $res["id"] . ".jpg");
				unlink($_document_root . "/modules/$module/upl/image_" . $res["id"] . "_thumb.jpg");
				
				OBA_sync("DELETE_FILE", "image_" . $res["id"] . ".jpg");
				OBA_sync("DELETE_FILE", "image_" . $res["id"] . "_thumb.jpg");
			}
			
			$sql = "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_images
				WHERE
					auction_id = '$id'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
			
			$sql = "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_bids
				WHERE
					auction_id = '$id'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
		}
		
		if ($auction_type == "online")
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_online");
		}
		else
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_overview");
		}
		exit;
	}
	elseif ($do == "auctions_approve_now")
	{
		// Godkend auktion
		
		OBA_auction_no($id);
		
		if ($vars["return_url"] != "")
		{
			header("Location: " . $vars["return_url"]);
		}
		else
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_approve");
		}
		exit;
		
	}
	elseif ($do == "auctions_disapprove")
	{
		// Fjern godkendelse af auktion
		
		$sql = "
			UPDATE
				" . $_table_prefix . "_module_" . $module . "_auctions
			SET
				auction_no = NULL
			WHERE
				id = '$id' AND
				auction_type = 'live'
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);
		
		header("Location: /site/$_lang_id/$module/$page/auctions_overview");
		exit;
		
	}
	elseif ($do == "auctions_overview" or $do == "auctions_approve" or $do == "auctions_online")
	{
		// Kategorier og typer
		$array_categories = array();
		$array_types = array();
		$array_groups = array();
		$select_groups = array(array("", "Alle"));
		if ($do == "auctions_overview")
		{
			// Fysisk
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_categories
				ORDER BY
					`type`
				");
			while ($db->fetch_array())
			{
				$array_categories[$db->array["id"]] = $db->array["type"];
			}
			
			// Typer
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_types
				ORDER BY
					`title`
				");
			while ($db->fetch_array())
			{
				$array_types[$db->array["id"]] = $db->array["title"];
			}
		}
		
		if ($do == "auctions_online")
		{
			// Grupper
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_groups
				ORDER BY
					`title`
				");
			while ($db->fetch_array())
			{
				$array_groups[$db->array["id"]] = $db->array["title"];
				$select_groups[] = array($db->array["id"], $db->array["title"]);
			}
		}
		
		$msg = new message;
		if ($do == "auctions_online")
		{
			$msg->title("Online-auktioner");
		}
		elseif ($do == "auctions_approve")
		{
			$msg->title("Godkend auktioner");
		}
		else
		{
			$msg->title("Fysiske auktioner");
		}
		$html .= $msg->html();
		
		if ($do == "auctions_approve")
		{
			// Kun auktioner der ikke er godkendt
			$sql_where = " WHERE ISNULL(auction_no) AND cancel = 0 ";
		}
		else
		{
			if ($do == "auctions_online")
			{
				$sql_where = " auction_type = 'online' ";
			}
			else
			{
				$sql_where = " auction_type = 'live' ";
			}
			
			if ($vars["submitform"] == "true")
			{
				$_SESSION[$module . $do . "_date"] = $vars["date"];
				$_SESSION[$module . $do . "_searchstring"] = trim($vars["searchstring"]);
				$_SESSION[$module . $do . "_group_id"] = $vars["group_id"];
			}
			
			$date = $_SESSION[$module . $do . "_date"];
			$searchstring = $_SESSION[$module . $do . "_searchstring"];
			$group_id = $_SESSION[$module . $do . "_group_id"];
			
			// Auktionsdatoer
			$db->execute("
				SELECT
					DISTINCT(auction_date) AS auction_date
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					NOT ISNULL(auction_date) AND
					auction_type = 'live'
				ORDER BY
					auction_date
				");
			$select_auction_date = array(array("all", "Vis alle"));
			while ($db->fetch_array())
			{
				if ($date == "" and $db->array["auction_date"] >= date("Y-m-d")) $date = $db->array["auction_date"];
				$select_auction_date[] = array($db->array["auction_date"], date("d-m-Y", strtotime($db->array["auction_date"])));
			}
			if ($date != "all" and $do != "auctions_online")
			{
				if ($sql_where != "") $sql_where .= " AND ";				
				$sql_where .= " auction_date = '" . $db->escape($date) . "' ";
			}
			
			if ($group_id > 0)
			{
				$sql_where .= " AND group_id = '" . intval($group_id) . "' ";
			}
			
			// Bygger søge SQL
			if ($searchstring != "")
			{
				if ($sql_where != "") $sql_where .= " AND ";
				$sql_where .= "
					(
						auction_no = '" . intval($searchstring) . "' OR
						regno LIKE '%" . $db->escape($searchstring) . "%' OR
						brand LIKE '%" . $db->escape($searchstring) . "%' OR
						model LIKE '%" . $db->escape($searchstring) . "%'
					)
					";
			}
			if ($sql_where != "") $sql_where = " WHERE $sql_where ";
			
			$frm = new form;
			$frm->method("get");
			$frm->submit_text = "{LANG|Søg}";
			$frm->hidden("submitform", "true");
			$frm->tpl("th", "{LANG|Søg}");
			if ($do != "auctions_online")
			{
				$frm->select(
					"Auktionsdato",
					"date",
					$date,
					"",
					"",
					"",
					$select_auction_date
					);
			}
			else
			{
				$frm->select(
					"Gruppe",
					"group_id",
					$group_id,
					"",
					"",
					"",
					$select_groups
					);
			}
			$frm->input(
				"{LANG|Søgeord}",
				"searchstring",
				$searchstring
				);
			$html .= $frm->html();
		}
		
		$total = $db->execute_field("
			SELECT
				COUNT(*)
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			$sql_where
			");
			
		$paging = new paging;
		$limit = $paging->limit(25);
		$paging->total($total);
		$start = ($paging->current_page() - 1) * $limit;
		$html .= $paging->html();
		
		$tbl = new table;
		
		if ($do == "auctions_approve")
		{
			$tbl->th("Type");
		}
		
		$tbl->th("Auktionsdato");
		$tbl->th("Auktionsnr");
		
		if ($do == "auctions_online" or $do == "auctions_approve")
		{
			// Online
			$tbl->th("Gruppe");
		}
		if ($do != "auctions_online")
		{
			// Fysisk
			$tbl->th("Nøglenr");
			$tbl->th("Kategori");
			$tbl->th("Type");
		}
			
		$tbl->th("Reg./stelnr.");
		$tbl->th("Mærke");
		$tbl->th("Model");
		$tbl->th("Status");
		$tbl->th("{LANG|Valg}", 3);
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			$sql_where
			ORDER BY
				IF(auction_date < '" . date("Y-m-d") . "', 1, 0),
				auction_date,
				auction_no
			LIMIT
				$start, $limit
			");
		
		while ($res = $db->fetch_array())
		{
			if ($do == "auctions_approve")
			{
				$tbl->td($res["auction_type"] == "online" ? "Online" : "Fysisk");
			}
			
			$tbl->td(date("d-m-Y", strtotime($res["auction_date"])));
			$tbl->td($res["auction_no"]);
			
			if ($do == "auctions_online" or $do == "auctions_approve")
			{
				// Online
				$tbl->td($array_groups[$res["group_id"]]);
			}
			if ($do != "auctions_online")
			{
				// Fysisk
				$tbl->td($res["keyno"] > 0 ? $res["keyno"] : "-");
				$tbl->td($array_categories[$res["category_id"]]);
				$tbl->td($array_types[$res["type_id"]]);
			}
				
			$tbl->td(htmlentities(stripslashes($res["regno"])));
			$tbl->td(htmlentities(stripslashes($res["brand"])));
			$tbl->td(htmlentities(stripslashes($res["model"])));
			
			// Status
			if ($res["auction_type"] == "online")
			{
				// Online
				if ($res["auction_no"] == "")
				{
					$tbl->td("Mangler godkendelse");
					$tbl->choise("Godkend", "auctions_approve_now", $res["id"]);
				}
				elseif ($res["cancel"] == 1)
				{
					$tbl->td("Annulleret");
					$tbl->choise("Gentag", "auctions_reset", $res["id"]);
				}
				elseif ($res["start_time"] > date("Y-m-d H:i"))
				{
					$tbl->td("Ny");
					$tbl->choise("Annuller", "auctions_cancel", $res["id"], "Vil du annullere denne auktion? (bagefter kan du gentage den)");
				}
				elseif ($res["start_time"] <= date("Y-m-d H:i") and $res["end_time"] > date("Y-m-d H:i"))
				{
					$tbl->td("Igangværende");
					$tbl->choise("Annuller", "auctions_cancel", $res["id"], "DENNE AUKTION ER AKTIV I ØJEBLIKKET!!! Vil du annullere denne auktion? (bagefter kan du gentage den)");
				}
				elseif ($res["cur_price"] == 0)
				{
					$tbl->td("Ingen bud");
					$tbl->choise("Gentag", "auctions_reset", $res["id"]);
				}
				elseif ($res["cur_price"] < $res["min_price"])
				{
					$tbl->td("Mindstepris ikke opnået");
					$tbl->choise("Gentag", "auctions_reset", $res["id"]);
				}
				elseif ($res["invoice_time"] != "" and $res["seller_account_time"] != "")
				{
					// Afregning ok
					$tbl->td("Solgt og afregnet");
				}
				else
				{
					$tbl->td("Venter på afregning");
				}
			}
			else
			{
				// Fysisk
				if ($res["cancel"] == 1)
				{
					$tbl->td("Annulleret");
				}
				elseif ($res["start_time"] == "")
				{
					$tbl->td("Ny");
				}
				elseif ($res["end_time"] == "")
				{
					$tbl->td("På auktion");
				}
				elseif ($res["cur_price"] == 0)
				{
					$tbl->td("Ingen bud");
				}
				elseif ($res["cur_price"] < $res["min_price"])
				{
					$tbl->td("Mindstepris ikke opnået");
				}
				elseif ($res["invoice_time"] == "")
				{
					$tbl->td("Køber afregning");
				}
				elseif ($res["mortgage_time"] == "")
				{
					$tbl->td("Kontroller pant");
				}
				elseif ($res["seller_account_time"] == "")
				{
					$tbl->td("Sælger afregning");
				}
				else
				{
					$tbl->td("Gennemført");
				}
			}
			
			if ($do == "auctions_approve")
			{
				// Godkend live auktion
				$tbl->td("<input type=\"button\" value=\"Vis\" onclick=\"url_popup('/site/$_lang_id/$module/$page/auctions_edit/" . $res["id"] . "?tpl=popup');\">");
				$tbl->choise("{LANG|Godkend}", "auctions_approve_now", $res["id"] . "&return_url=" . urlencode($_SERVER["REQUEST_URI"]));
				if ($res["end_time"] == "" or $res["cancel"] == 1)
				{
					$tbl->choise("{LANG|Slet}", "auctions_delete", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring), "{LANG|Slet auctionsnr} " . $res["auction_no"] . "?");
				}
			}
			elseif ($do == "auctions_overview")
			{
				// Live auktioner
				
				// Gentag
				if (
					$res["cancel"] == 1
					or
					$res["end_time"] != "" and $res["cur_price"] < $res["min_price"]
					)
				{
					$tbl->choise("Gentag", "auctions_reset", $res["id"]);
				}
				
				$tbl->choise("{LANG|Ret}", "auctions_edit", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring));
				if ($res["start_time"] == "" and $res["auction_no"] != "") {
					$tbl->choise("{LANG|Skift dato/nr}", "auctions_change_no", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring));
				}
				
				if ($res["auction_no"] == "")
				{
					$tbl->choise("{LANG|Godkend}", "auctions_approve_now", $res["id"] . "&return_url=" . urlencode($_SERVER["REQUEST_URI"]));
				}
							
				if ($res["start_time"] == "" and $res["auction_no"] != "")
				{
					$tbl->choise("Annuller", "auctions_disapprove", $res["id"], "Er du sikker på at du vil annullere auktionsnr. " . $res["auction_no"] . "?");
				}
	
				if ($res["end_time"] == "" or $res["cancel"] == 1)
				{
					$tbl->choise("{LANG|Slet}", "auctions_delete", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring), "{LANG|Slet auctionsnr} " . $res["auction_no"] . "?");
				}
				else
				{
					if ($res["cur_price"] < $res["min_price"] and $res["cancel"] != 1)
					{
						// Mulighed for at gentage
						$tbl->choise("Gentag", "auctions_reset", $res["id"]);
						$tbl->choise("Ikke solgt", "auctions_cancel", $res["id"], "Er du sikker på du vil markere auktionsnr. " . $res["auction_no"] . " som `Ikke solgt`?");
					}
				}
				
			}
			elseif ($do == "auctions_online")
			{
				// Online auktioner
				$tbl->choise("{LANG|Ret}", "auctions_edit", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring));
				if ($res["start_time"] > date("Y-m-d H:i") or $res["end_time"] < date("Y-m-d H:i:s") or $res["cancel"] == 1)
				{
					$tbl->choise("{LANG|Slet}", "auctions_delete", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring), "{LANG|Slet auctionsnr} " . $res["auction_no"] . "?");
				}
			}

			$tbl->endrow();
		}
		
		if ($db->num_rows() == 0)
		{
			$tbl->td("{LANG|Ingen}...", 10);
		} 
		
		$html .= $tbl->html();
		
		
	}
	elseif ($do == "auctions_change_no")
	{
		// Skift auktionsnr
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				ISNULL(start_time) AND
				id = '$id' AND
				NOT ISNULL(auction_no) AND
				auction_type = 'live'
			");
		if (!$res = $db->fetch_array())
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_overview");
			exit;
		}
		
		$msg = new message;
		$msg->title("Skift auktionsdato/-nummer");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->tpl("th", "Auktionsdata");
		$frm->tpl("td2", "Nuværende auktionsdato:", date("d-m-Y", strtotime($res["auction_date"])));
		$frm->tpl("td2", "Nuværende auktionsnr:", $res["auction_no"]);
		$frm->tpl("td2", "Regnr:", htmlentities(stripslashes($res["regno"])));
		$frm->tpl("td2", "Mærke:", htmlentities(stripslashes($res["brand"])));
		$frm->tpl("td2", "Model:", htmlentities(stripslashes($res["model"])));
		
		$frm->input(
			"Ny auktionsdato",
			"auction_date",
			date("d-m-Y", strtotime($res["auction_date"])),
			"^[0-9]{2}-[0-9]{2}-[0-9]{4}$",
			"Ugyldig dato - format: dd-mm-åååå",
			'
				$db = new db;
				$cnv = new convert;
				if (!$db->execute_field("
					SELECT
						`date`
					FROM
						' . $_table_prefix . '_module_' . $module . '_dates
					WHERE
						`date` = \'" . $db->escape($cnv->date_dk2uk($this->values["auction_date"])) . "\'
					"))
				{
					$error = "Auktionsdato findes ikke";
				}
			'
			);
		$frm->input(
			"Nyt auktionsnr",
			"auction_new_no",
			$res["auction_no"],
			"^[1-9]+[0-9]*$",
			"Skal være et tal"
			);
			
		if ($frm->done())
		{
			// Skifter auktionsnummer
			
			// Skifter auktionsdato
			$cnv = new convert;
			$res["auction_date"] = $cnv->date_dk2uk($frm->values["auction_date"]);
			
			// Finder nuværende auktion med dette nummer
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					auction_date = '" . $res["auction_date"] . "' AND
					auction_no = '" . intval($frm->values["auction_new_no"]) . "' AND
					auction_type = 'live'
				");
			if ($res2 = $db->fetch_array())
			{
				// Skifter auktionsnummer på den anden auktion
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						auction_no = '" . $res["auction_no"] . "'
					WHERE
						id = '" . $res2["id"] . "'
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
			}
			
			// Skifter auktionsnummer på den denne auktion
			$sql = "
				UPDATE
					" . $_table_prefix . "_module_" . $module . "_auctions
				SET
					auction_date = '" . $db->escape($res["auction_date"]) . "',
					auction_no = '" . intval($frm->values["auction_new_no"]) . "'
				WHERE
					id = '" . $res["id"] . "'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);

			header("Location: /site/$_lang_id/$module/$page/auctions_overview");
			exit;
		}

		$html .= $frm->html();
		
		$html .= "
<script>
$(document).ready(function() {
	$('#auction_date').datepicker({
		dateFormat: 'dd-mm-yy',
		minDate: '+0',
		maxDate: '+180',
		dayNamesMin: ['Sø', 'Ma', 'Ti', 'On', 'To', 'Fr', 'Lø', 'Sø'],
		monthNames: ['Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December'],
		firstDay: 1
	});
});
</script>		
		";
		
	}
	elseif ($do == "auctions_reset")
	{
		// Gentag auktion
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				(
					NOT ISNULL(start_time) AND
					NOT ISNULL(end_time) AND
					(
						cur_price < min_price
						OR
						cur_price = 0
					)
					OR
					cancel = 1
				)
				AND
				id = '$id'
			");
		if (!$res = $db->fetch_array())
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_overview");
			exit;
		}
		
		$msg = new message;
		$msg->title("Gentag auktion");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->tpl("th", "Auktionsdata");
		$frm->tpl("td2", "Auktionstype:" , $res["auction_type"] == "online" ? "Online" : "Fysisk");
		$frm->tpl("td2", "Auktionsnr:", $res["auction_no"]);
		$frm->tpl("td2", "Regnr:", htmlentities(stripslashes($res["regno"])));
		$frm->tpl("td2", "Mærke:", htmlentities(stripslashes($res["brand"])));
		$frm->tpl("td2", "Model:", htmlentities(stripslashes($res["model"])));
		
		if ($res["auction_type"] == "online")
		{
			// Online
			
			// Henter mulige auktionsdatoer for online
			$sel_online_days = array();
			for ($i = 0; $i <= 7; $i++)
			{
				$ts = strtotime("+" . $i . " day");
				$db->execute("
					SELECT
						time,
						duration
					FROM
						" . $_table_prefix . "_module_" . $module . "_online_days
					WHERE
						weekday = '" . date("w", $ts) . "'
					ORDER BY
						time
					");
				while ($db->fetch_array())
				{
					$ts_from = strtotime(date("Y-m-d", $ts) . " " . substr($db->array["time"], 0, 5));
					$ts_to = strtotime(date("Y-m-d H:i", $ts_from) . " +" . $db->array["duration"] . " hour");
					$sel_online_days[] = array($ts_from . "-" . $ts_to, date("d-m-Y H:i", $ts_from) . " til " . date("d-m-Y H:i", $ts_to));
				}
			}
			$frm->select(
				"Auktionsdato",
				"auction_date",
				"",
				"^[0-9]+-[0-9]+$",
				"Påkrævet",
				'
					list($ts_from, $ts_to) = explode("-", $this->values["auction_date"]);
					$ts_from = intval($ts_from);
					$ts_to = intval($ts_to);
					if ($ts_from >= $ts_to) $error = "Påkrævet";
					//if (date("Y-m-d", $ts_to) <= date("Y-m-d H:i")) $error = "";
				',
				$sel_online_days
				);
		}
		else
		{
			// Fysisk
			$frm->input(
				"Auktionsdato",
				"auction_date",
				date("d-m-Y", strtotime($res["auction_date"])),
				"^[0-9]{2}-[0-9]{2}-[0-9]{4}$",
				"Ugyldig dato - format: dd-mm-åååå",
				'
					$db = new db;
					$cnv = new convert;
					if (!$db->execute_field("
						SELECT
							`date`
						FROM
							' . $_table_prefix . '_module_' . $module . '_dates
						WHERE
							`date` = \'" . $db->escape($cnv->date_dk2uk($this->values["auction_date"])) . "\' AND
							`date` >= \'" . date("Y-m-d") . "\'
						"))
					{
						$error = "Auktionsdato findes ikke";
					}
				'
				);
		}
		
		$frm->input(
			"Mindstepris",
			"min_price",
			$res["min_price"],
			"^[0-9]+$",
			"Skal være et tal"
			);
			
		if ($frm->done())
		{
			// Nulstiller auktion
			$cnv = new convert;
			
			if ($res["auction_type"] == "online")
			{
				// Online
				list($ts_from, $ts_to) = explode("-", $frm->values["auction_date"]);
				$ts_from = intval($ts_from);
				$ts_to = intval($ts_to);
				$auction_date = date("Y-m-d", $ts_from);
				$start_time = "'" . date("Y-m-d H:i", $ts_from) . "'";
				$end_time = "'" . date("Y-m-d H:i", $ts_to) . "'";
			}
			else
			{
				// Fysisk
				$auction_date = $db->escape($cnv->date_dk2uk($frm->values["auction_date"]));
				$start_time = "NULL";
				$end_time = "NULL";
			}
				
			$sql = "
				UPDATE
					" . $_table_prefix . "_module_" . $module . "_auctions
				SET
					auction_date = '$auction_date',
					auction_no = NULL,
					min_price = '" . intval($frm->values["min_price"]) . "',
					cur_price = 0,
					bidder_id = 0,
					start_time = $start_time,
					end_time = $end_time,
					cancel = 0
				WHERE
					id = '$id'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
			
			// Sletter bud
			$sql = "
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_bids
				WHERE
					auction_id = '$id'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
			
			// Tilføjer nyt auktionsnr.
			OBA_auction_no($id);

			if ($res["auction_type"] == "online")
			{
				header("Location: /site/$_lang_id/$module/$page/auctions_online");
			}
			else
			{
				header("Location: /site/$_lang_id/$module/$page/auctions_overview");
			}
			exit;
		}

		$html .= $frm->html();
		
		$html .= "
<script>
$(document).ready(function() {
	$('#auction_date').datepicker({
		dateFormat: 'dd-mm-yy',
		minDate: '+0',
		maxDate: '+180',
		dayNamesMin: ['Sø', 'Ma', 'Ti', 'On', 'To', 'Fr', 'Lø', 'Sø'],
		monthNames: ['Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December'],
		firstDay: 1
	});
});
</script>			
			";
		
	}
	elseif ($do == "auctions_cancel")
	{
		// Afslut auktion
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				id = '$id' AND
				NOT ISNULL(start_time) AND
				NOT ISNULL(end_time) AND
				(
					auction_type = 'online' OR
					cur_price < min_price
				)
			");
		if ($res = $db->fetch_array())
		{
			// Afslutter auktion
			$sql = "
				UPDATE
					" . $_table_prefix . "_module_" . $module . "_auctions
				SET
					cancel = 1
				WHERE
					id = '$id'
				";
			$db->execute($sql);
			OBA_sync("SQL", $sql);
		}
			
		if ($db->execute_field("
			SELECT
				auction_type
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				id = '$id'
			") == "online")
		{
			// Online
			header("Location: /site/$_lang_id/$module/$page/auctions_online");
		}
		else
		{
			// Fysisk
			header("Location: /site/$_lang_id/$module/$page/auctions_overview");
		}
		
		exit;
		
	}
	elseif ($do == "auctions_print")
	{
		// Print PDF
		
		$msg = new message;
		$msg->title("Print katalog");
		$html .= $msg->html();
		
		$select_dates = array();
		$db->execute("
			SELECT
				`date`
			FROM
				" . $_table_prefix . "_module_" . $module . "_dates
			WHERE
				`date` >= '" . date("Y-m-d") . "'
			ORDER BY
				`date`
			");
		while ($db->fetch_array()) $select_dates[] = array($db->array["date"], date("d-m-Y", strtotime($db->array["date"])));
	
		$frm = new form;
		$frm->method("get");
		$frm->submit_text = "{LANG|Åbn som PDF}";
		$frm->tpl("th", "{LANG|Vælg auktionsdato}");
		$frm->select(
			"{LANG|Vælg auktionsdato}",
			"date",
			"",
			"",
			"",
			"",
			$select_dates
			);
		$frm->checkbox(
			"Vis mindstepris+nøglenr",
			"show_min_price"
			);
		$html .= $frm->html();
		
		if ($vars["date"] != "")
		{
			if ($vars["pdf"] != "")
			{
				// Lav PDF
				
				// Felter der skal vises
				$array_fields = array(
					"regno" => "Regnr.",
					"chasno" => "Stelnr.",
					"brand" => "Mærke",
					"model" => "Model",
					"variant" => "Variant",
					"type" => "Type",
					"fuel" => "Brændstof",
					"doors" => "Døre",
					"year" => "Årgang",
					"km" => "Km-stand",
					"color" => "Farve",
					"newly_tested" => "Nysynet",
					"newly_tested_date" => "Sidste syn",
					"is_regged" => "Indregistreret",
					"first_reg_date" => "1. indreg.",
					"no_vat" => "Moms",
					"no_tax" => "Afgift",
					"service" => "Servicebog",
					"category_type" => "Kategori"
					);
				if ($vars["show_min_price"] != "")
				{
					$array_fields["min_price"] = "Mindstepris";
					$array_fields["keyno"] = "Nøglenr";
				}
				
				// FPDF + FPDI
				define("RELATIVE_PATH", $_document_root . "/modules/$module/fpdf/");
				define("FPDF_FONTPATH", $_document_root . "/modules/$module/fpdf/font/");
				require_once($_document_root . "/modules/$module/fpdf/fpdf.php");
				require_once($_document_root . "/modules/$module/fpdf/fpdi.php");
				
				// Instanciation of inherited class
				$fpdf = new FPDI('P', 'mm', 'A4');
				$fpdf->SetAutoPageBreak(false);
				
				// Forside
				$fpdf->AddPage();
				$fpdf->Image($_document_root . "/modules/$module/pdf/frontpage.jpg", 0, 0, 210, 297);
				$fpdf->SetTextColor(0, 0, 0);
				$fpdf->SetXY(0, 185);
				$fpdf->AddFont("Eurostile-Normal", "", "Eurostile-Normal.php");
				$fpdf->SetFont("Eurostile-Normal", "", 30);
				$fpdf->MultiCell(210, 0, strftime("%A d. %e. %B %Y", strtotime($vars["date"])), "0", "C");
				$fpdf->SetFont("Arial", "", 30);
			
				// Standard farve
				$fpdf->SetTextColor(0, 0, 0);
				
				$ress = $db->execute("
					SELECT
						auc.*,
						cat.type AS category_type
					FROM
						" . $_table_prefix . "_module_" . $module . "_auctions AS auc
					LEFT JOIN
						" . $_table_prefix . "_module_" . $module . "_categories AS cat
					ON
						cat.id = auc.category_id
					WHERE
						auc.auction_date = '" . $db->escape($vars["date"]) . "' AND
						NOT ISNULL(auc.auction_no) AND
						auc.auction_type = 'live'
					ORDER BY
						auc.auction_no
					");
				$total = $db->num_rows($ress);
				$count = 0;
				while ($res = $db->fetch_array($ress))
				{
					if ($count == 0 or $count >= 3)
					{
						$fpdf->AddPage();
						$offset_y = 0;
						$count = 0;
					}
					elseif ($count == 1)
					{
						$offset_y = 99;
					}
					else
					{
						$offset_y = 198;
					}
					
					// Overskrift
					$fpdf->SetFont("Arial", "", 16);
					$fpdf->Text(10, $offset_y + 15, "AUKTIONSNR: " . $res["auction_no"]);
					
					// Felter
					$fpdf->SetFont("Arial", "", 9);
					$y = 20;
					$fcount = 0;
					$finset = 0;
					foreach ($array_fields as $key => $val)
					{
						if (
							$res[$key] != ""
							or
							in_array($key, array("newly_tested", "is_regged", "no_vat", "no_tax")) and $res[$key] == 1
							)
						{
							if ($finset == 0 and $fcount >= count($array_fields) / 2)
							{
								$finset = 70;
								$y1 = $fpdf->GetY();
								$y = 20;
							}
							if ($key == "keyno" and intval($res[$key]) == 0) $res[$key] = "-";
							if ($key == "newly_tested" or $key == "is_regged" or $key == "no_vat" or $key == "no_tax" or $key == "service")
							{
								if ($res[$key] == 1)
								{
									if ($key == "no_vat")
									{
										$fpdf->Text($finset + 33, $offset_y + $y, "ekskl. moms");
									}
									elseif ($key == "no_tax")
									{
										$fpdf->Text($finset + 33, $offset_y + $y, "uden afgift (ikke reg. i dk)");
									}
									else
									{
										$fpdf->Text($finset + 33, $offset_y + $y, "Ja");
									}
										
									$fpdf->Text($finset + 10, $offset_y + $y, $val . ":");
									$y += 4.3;
									$fcount++;
								}
							}
							else
							{
								if (preg_match("/_date$/", $key))
								{
									$fpdf->Text($finset + 33, $offset_y + $y, date("d-m-Y", strtotime($res[$key])));
								}
								else
								{
									$fpdf->Text($finset + 33, $offset_y + $y, stripslashes($res[$key]));
								}
								$fpdf->Text($finset + 10, $offset_y + $y, $val . ":");
								$y += 4.3;
								$fcount++;
							}
						}
					}
					$y1 = max($fpdf->GetY(), $y1);
					
					// Billede
					$y = $offset_y + 10;
					$imgid = $db->execute_field("
						SELECT
							id
						FROM
							" . $_table_prefix . "_module_" . $module . "_images
						WHERE
							auction_id = '" . $res["id"] . "'
						");
					if ($imgid)
					{
						// Tilføjer billede
						$maxw = 60;
						$maxh = 45;
						
						$imgfile = $_document_root . "/modules/$module/upl/image_" . $imgid . ".jpg";
						if ($img = imagecreatefromjpeg($imgfile))
						{
							$w = imagesx($img);
							$h = imagesy($img);
							imagedestroy($img);
							
							if ($w / $h > $maxw / $maxh)
							{
								$h = $maxw / $w * $h;
								$w = $maxw;
							}
							else
							{
								$w = $maxh / $h * $w;
								$h = $maxh;
							}
						}
						else
						{
							$w = $maxw;
							$h = $maxh;
						}
						$l = 200 - $w;
						$t = $y;
						$fpdf->Image($imgfile, $l, $t, $w, $h, "JPG");
						$y2 = $t + $h + 5;
					}
					else
					{
						$fpdf->Text(140, $y, "Intet billede");
						$y2 = $y1;
					}
					
					// Beskrivelse
					$y = 65;
					$arr = explode("\n", wordwrap($res["description"], 125, "\n", true));
					for ($i = 0; $i < count($arr) and $i < 9; $i++)
					{
						$fpdf->Text(10, $offset_y + $y, $arr[$i]);
						$y += 3.5;
					}
					
					// Tegner streg
					if ($count > 0)
					{
						$fpdf->SetDrawColor(0, 0, 0);
						$fpdf->SetLineWidth(0.1);
						$fpdf->Line(0, 99 * $count, 210, 99 * $count);
					}
					
					$count++;
				}
				
				// Betingelser
				$pagecount = $fpdf->setSourceFile($_document_root . "/modules/$module/pdf/terms.pdf"); 
				for ($i = 1; $i <= $pagecount; $i++)
				{
					$tplidx = $fpdf->ImportPage($i); 
					$s = $fpdf->getTemplatesize($tplidx); 
					$fpdf->AddPage('P', array($s['w'], $s['h'])); 
					$fpdf->useTemplate($tplidx); 
				}

				// Vis PDF				
				$fpdf->Output();
				exit;
				
			}
			
			// Vis PDF
			$html .= "
				<script>
				$(document).ready(function() {
					url_popup('/?module=$module&page=$page&do=$do&date=" . $vars["date"] . "&pdf=true&show_min_price=" . $vars["show_min_price"] . "');
				});
				</script>
				";
		}	
	}
	elseif ($do == "auctions_print_window")
	{
		// Print vindues PDF
		
		$msg = new message;
		$msg->title("Print rudeark");
		$html .= $msg->html();
		
		$select_dates = array();
		$db->execute("
			SELECT
				`date`
			FROM
				" . $_table_prefix . "_module_" . $module . "_dates
			WHERE
				`date` >= '" . date("Y-m-d") . "'
			ORDER BY
				`date`
			");
		while ($db->fetch_array()) $select_dates[] = array($db->array["date"], date("d-m-Y", strtotime($db->array["date"])));
	
		$frm = new form;
		$frm->method("get");
		$frm->submit_text = "{LANG|Åbn som PDF}";
		$frm->tpl("th", "{LANG|Vælg auktionsdato}");
		$frm->select(
			"{LANG|Vælg auktionsdato}",
			"date",
			"",
			"",
			"",
			"",
			$select_dates
			);
		$html .= $frm->html();
		
		if ($vars["date"] != "")
		{
			if ($vars["pdf"] != "")
			{
				// Lav PDF
				
				// Felter
				$array_fields = array(
					"auction_no" => "Auktionsnr",
					"type" => "Katalogtype",
					"is_regged" => "Indregistreret",
					"brand" => "Mærke",
					"model" => "Model",
					"variant" => "Variant",
					"km" => "Kilometer",
					
					"category_type" => "Klasse",
					"first_reg_date" => "1. indreg",
					"newly_tested_date" => "Sidste syn",
					"year" => "Årgang",
					"" => "",
					"doors" => "Døre",
					"color" => "Farve"
					);
				
				// FPDF + FPDI
				define("RELATIVE_PATH", $_document_root . "/modules/$module/fpdf/");
				define("FPDF_FONTPATH", $_document_root . "/modules/$module/fpdf/font/");
				require_once($_document_root . "/modules/$module/fpdf/fpdf.php");
				require_once($_document_root . "/modules/$module/fpdf/fpdi.php");
				
				// Instanciation of inherited class
				$fpdf = new FPDI('P', 'mm', 'A4');
				$fpdf->SetAutoPageBreak(false);
				$fpdf->AddFont("Eurostile-Normal", "", "Eurostile-Normal.php");
				$fpdf->AddFont("Eurostile-Bold", "", "Eurostile-Bold.php");
				$fpdf->SetTextColor(0, 0, 0);
				
				$ress = $db->execute("
					SELECT
						auc.*,
						cat.type AS category_type
					FROM
						" . $_table_prefix . "_module_" . $module . "_auctions AS auc
					LEFT JOIN
						" . $_table_prefix . "_module_" . $module . "_categories AS cat
					ON
						cat.id = auc.category_id
					WHERE
						auc.auction_date = '" . $db->escape($vars["date"]) . "' AND
						NOT ISNULL(auc.auction_no) AND
						auc.auction_type = 'live'
					ORDER BY
						auc.auction_no
					");
				while ($res = $db->fetch_array($ress))
				{
					$fpdf->AddPage();
					
					// Logo
					$fpdf->Image($_document_root . "/modules/$module/pdf/logo.png", 40, 6, 130);
					
					// Felter
					$count = 0;
					foreach ($array_fields as $key => $title)
					{
						if ($key != "")
						{
							$val = $res[$key];
							
							if ($key == "is_regged") $val = ($val == 1 ? "Ja" : "Nej");
							if (preg_match("/date/", $key) and $val != "") $val = date("d-m-Y", strtotime($val));
							
							if ($count < count($array_fields) / 2)
							{
								// Venstre kolonne
								$x = 8;
								$y = 60 + $count * 20;
								$max_width = 100;
								
								// Linie
								$fpdf->Line(8, $y + 7, 202, $y + 7);
							}
							else
							{
								// Højre kolonne
								$x = 110;
								$y = 60 + ($count - count($array_fields) / 2) * 20;
								$max_width = 95;
							}

							$str = $title . ": " . $val;
							$width = -1;
							$size = 26;
							while ($width == -1 or $width > $max_width and $size > 8)
							{
								$fpdf->SetFont("Eurostile-Normal", "", $size);
								$width = $fpdf->GetStringWidth($str);
								$size--;
							}
							$fpdf->Text($x, $y, $str);
						}
						
						$count++;
					}
					
					// Bemærkninger
					$fpdf->Text(8, 200, "Bemærkninger:");
					$fpdf->SetFont("Eurostile-Normal", "", 18);
					$y = 208;
					$arr = explode("\n", wordwrap($res["description"], 70, "\n", true));
					for ($i = 0; $i < count($arr) and $i < 9; $i++)
					{
						$fpdf->Text(8, $y, $arr[$i]);
						$y += 7;
					}
					
					// Bund
					$fpdf->SetFont("Eurostile-Normal", "", 14);
					$fpdf->Text(8, 280, "Auktion: Hver Torsdag kl. 17:00");
					$fpdf->Text(8, 289, "Åbningstider: Dagligt 9.00 - 16.00");
					$fpdf->Text(170, 289, "Internt bilnr: " . $res["keyno"]);
				}
				
				// Vis PDF				
				$fpdf->Output();
				exit;
				
			}
			
			// Vis PDF
			$html .= "
				<script>
				$(document).ready(function() {
					url_popup('/?module=$module&page=$page&do=$do&date=" . $vars["date"] . "&pdf=true', 420, 600);
				});
				</script>
				";
		}	
	}
	elseif ($do == "auctions_secretary")
	{
		// Sekretær - start auktioner
	
		$msg = new message;
		$msg->title("Auktioner - Sekretær");
		$html .= $msg->html();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				auction_date = '" . date("Y-m-d") . "' AND
				ISNULL(end_time) AND
				NOT ISNULL(auction_no) AND
				auction_type = 'live'
			ORDER BY
				IF(NOT ISNULL(start_time), 0, 1),
				auction_date,
				auction_no
			");
		if ($res = $db->fetch_array())
		{
			if ($vars["do2"] == "stop")
			{
				// Stop auktionsrunde
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						start_time = NULL
					WHERE
						id = '" . $res["id"] . "'
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);

				module_setting("prev_auction_id", "");
				module_setting("cur_auction_id", "");
				module_setting("next_auction_id", "");
				
				OBA_sync("ACTIVE_IDS", "||");
				
				header("Location: /site/$_lang_id/$module/$page/$do");
				exit;
			}
			elseif ($vars["do2"] == "next")
			{
				// Næste auktion
				
				// Afslut aktuel auktion
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						end_time = '" . date("Y-m-d H:i:s") . "'
					WHERE
						id = '" . $res["id"] . "'
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
				
				// Find næste
				$db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_module_" . $module . "_auctions
					WHERE
						auction_date = '" . $res["auction_date"] . "' AND
						ISNULL(start_time) AND
						NOT ISNULL(auction_no) AND
						auction_type = 'live'
					ORDER BY
						auction_no
					");
				if ($rescur = $db->fetch_array())
				{
					$sql = "
						UPDATE
							" . $_table_prefix . "_module_" . $module . "_auctions
						SET
							start_time = '" . date("Y-m-d H:i:s") . "'
						WHERE
							id = '" . $rescur["id"] . "'
						";
					$db->execute($sql);
					OBA_sync("SQL", $sql);
	
					$next_id = $db->execute_field("
						SELECT
							id
						FROM
							" . $_table_prefix . "_module_" . $module . "_auctions
						WHERE
							auction_date = '" . $res["auction_date"] . "' AND
							ISNULL(start_time) AND
							NOT ISNULL(auction_no) AND
							auction_type = 'live'
						ORDER BY
							auction_no
						LIMIT
							1
						");
					$cur_id = $rescur["id"];
				}
				else
				{
					$cur_id = "";
					$next_id = "";
				}
				
				module_setting("prev_auction_id", $res["id"]);
				module_setting("cur_auction_id", $cur_id);
				module_setting("next_auction_id", $next_id);
				
				OBA_sync("ACTIVE_IDS", $res["id"] . "|" . $cur_id . "|" . $next_id);
					
				header("Location: /site/$_lang_id/$module/$page/$do");
				exit;
			}
			
			if ($res["start_time"] == "")
			{
				// Endnu ikke startet
				
				$html .= "Auktion ikke startet.<br><br>";
				
				$frm = new form;
				$frm->method("get");
				$frm->submit_text = "Start auktion";
				$html .= $frm->html();
				
				if ($frm->done())
				{
					$sql = "
						UPDATE
							" . $_table_prefix . "_module_" . $module . "_auctions
						SET
							start_time = '" . date("Y-m-d H:i:s") . "'
						WHERE
							id = '" . $res["id"] . "'
						";
					$db->execute($sql);
					OBA_sync("SQL", $sql);

					$prev_id = $db->execute_field("
						SELECT
							id
						FROM
							" . $_table_prefix . "_module_" . $module . "_auctions
						WHERE
							auction_date = '" . $res["auction_date"] . "' AND
							NOT ISNULL(end_time) AND
							NOT ISNULL(auction_no) AND
							auction_type = 'live'
						ORDER BY
							auction_no DESC
						LIMIT
							1
						");
						
					$next_id = $db->execute_field("
						SELECT
							id
						FROM
							" . $_table_prefix . "_module_" . $module . "_auctions
						WHERE
							auction_date = '" . $res["auction_date"] . "' AND
							ISNULL(start_time) AND
							NOT ISNULL(auction_no) AND
							auction_type = 'live'
						ORDER BY
							auction_no
						LIMIT
							1
						");
					
					module_setting("prev_auction_id", $prev_id);
					module_setting("cur_auction_id", $res["id"]);
					module_setting("next_auction_id", $next_id);
					
					OBA_sync("ACTIVE_IDS", $prev_id . "|" . $res["id"] . "|" . $next_id);
					
					header("Location: /site/$_lang_id/$module/$page/$do");
					exit;
				}
			}
			else
			{
				// Startet
				
				$a = new ajax;
				if ($a->do == "save_bid")
				{
					// Gem bud
					
					// Henter bruger ud fra bydernr
					$db->execute("
						SELECT
							cust.*
						FROM
							" . $_table_prefix . "_user_" . $module . "_cust AS cust
						INNER JOIN
							" . $_table_prefix . "_module_" . $module . "_bidno AS bidno
						ON
							bidno.cust_id = cust.id
						WHERE
							bidno.number = '" . intval($a->values["bidder"]) . "'
						");
					if ($cust = $db->fetch_array())
					{
						$sql = "
							INSERT INTO
								" . $_table_prefix . "_module_" . $module . "_bids
							(
								id,
								auction_id,
								`time`,
								bidder_id,
								bid,
								`type`
							)
							VALUES
							(
								'" . OBA_id() . "',
								'" . $res["id"] . "',
								'" . date("Y-m-d H:i:s") . "',
								'" . $cust["id"] . "',
								'" . $db->escape($a->values["bid"]) . "',
								'Sal'
							)
							";
						$db->execute($sql);
						OBA_sync("SQL", $sql);
						
						$sql = "
							UPDATE
								" . $_table_prefix . "_module_" . $module . "_auctions
							SET
								cur_price = '" . $db->escape($a->values["bid"]) . "',
								bidder_id = '" . $cust["id"] . "'
							WHERE
								id = '" . $res["id"] . "' AND
								cur_price <= '" . $db->escape($a->values["bid"]) . "' AND
								NOT ISNULL(start_time) AND
								ISNULL(end_time)
							";
						$db->execute($sql);
						OBA_sync("SQL", $sql);
					}
					
					$a->do = "sync";
					
				}
				if ($a->do == "sync")
				{
					// Refresh
					
					// Er der nye bud
					$counter = intval($a->values["counter"]);
					$bids = "";
					$db->execute("
						SELECT
							bids.*,
							cust.name
						FROM
							" . $_table_prefix . "_module_" . $module . "_bids AS bids
						LEFT JOIN
							" . $_table_prefix . "_user_" . $module . "_cust AS cust
						ON
							cust.id = bids.bidder_id
						WHERE
							bids.auction_id = '" . $res["id"] . "' AND
							bids.counter > '$counter'
						ORDER BY
							bids.counter
						");
					while ($db->fetch_array())
					{
						if ($bids != "") $bids .= "\n";
						$bids .= $db->array["counter"] . "|" . $db->array["time"] . "|" . $db->array["type"] . ": " . $db->array["name"] . "|" . $db->array["bid"];
						$counter = $db->array["counter"];
					}
					
					$a->response(array(
						"state" => "ok",
						"counter" => $counter,
						"bids" => $bids
						));
				}
				$html .= $a->html();
				
				$imgid = $db->execute_field("
					SELECT
						id
					FROM
						" . $_table_prefix . "_module_" . $module . "_images
					WHERE
						auction_id = '" . $res["id"] . "'
					");
				
				$tmp = new tpl("MODULE|$module|admin_auctions_secretary");
				$tmp->set("image_id", $imgid);
				
				$array_fields = array(
					"regno" => "Reg./stelnr.",
					"chasno" => "Stelnr.",
					"brand" => "Mærke",
					"model" => "Model",
					"variant" => "Variant",
					"type" => "Type",
					"fuel" => "Brændstof",
					"doors" => "Døre",
					"year" => "Årgang",
					"km" => "Km-stand",
					"color" => "Farve",
					"newly_tested" => "Nysynet",
					"is_regged" => "Indregistreret",
					"no_vat" => "Uden moms",
					"no_tax" => "Uden afgift",
					"service" => "Servicebog",
					"min_price" => "Mindstepris",
					"auction_no" => "Auktionsnr."
					);
				foreach ($array_fields as $key => $val)
				{
					$tmp->set($key, $res[$key]);
				}				
				
				$html .= $tmp->html();
			}
		}
		else
		{
			$html .= "Ikke flere auktioner i dag";
		}
	}
	elseif ($do == "auctions_cashregister")
	{
		// Kasse
		
		$msg = new message;
		$msg->title("Auktioner - Kasse");
		$html .= $msg->html();
	
		if ($id == "")
		{
			// Angiv byder-nr
			
			$frm = new form;
			$frm->submit_text = "Find auktioner";
			$frm->method("get");
			$frm->tpl("th", "Afregn auktion");
			$frm->select(
				"Type",
				"type",
				"",
				"",
				"",
				"",
				array(
					array("Sal", "Sal"),
					array("Online", "Online")
					)
				);
			$frm->input(
				"Indtast bydernr/navn",
				"bidder"
				);
			$html .= $frm->html();
			$html .= "<script> $('#bidder').focus(); </script>";
			
			if ($frm->done())
			{
				// Finder auktioner
				$sql_where = " AND bids.type = '" . $db->escape($frm->values["type"]) . "' ";
				if ($frm->values["bidder"] != "")
				{
					if (is_numeric($frm->values["bidder"]))
					{
						// Søg på bydernummer
						$sql_where .= " AND bidno.number = '" . intval($frm->values["bidder"]) . "' ";
					}
					else
					{
						// Søg på navn
						$sql_where .= " AND cust.name LIKE '%" . $db->escape($frm->values["bidder"]) . "%' ";
					}
				}
				$ress = $db->execute("
					SELECT
						auc.*,
						bidno.number,
						cust.name,
						bids.type
					FROM
						" . $_table_prefix . "_module_" . $module . "_auctions AS auc
					INNER JOIN
						" . $_table_prefix . "_user_" . $module . "_cust AS cust
					ON
						cust.id = auc.bidder_id
					LEFT JOIN
						" . $_table_prefix . "_module_" . $module . "_bidno AS bidno
					ON
						bidno.cust_id = cust.id
					LEFT JOIN
						" . $_table_prefix . "_module_" . $module . "_bids AS bids
					ON
						bids.bidder_id = cust.id AND
						bids.auction_id = auc.id AND
						bids.bid = auc.cur_price
					WHERE
						ISNULL(invoice_time) AND
						NOT ISNULL(end_time) AND
						`cancel` = 0 AND
						auction_type = 'live'
						$sql_where
					ORDER BY
						auction_date,
						auction_no
					");
				if ($db->num_rows($ress) > 0)
				{
					$tbl = new table;
					$tbl->th("Auktionsdato");
					$tbl->th("Auktionsnr");
					$tbl->th("Mærke");
					$tbl->th("Model");
					$tbl->th("Type");
					$tbl->th("Byder");
					$tbl->th("Bud");
					$tbl->th("Valg");
					$tbl->endrow();
					
					while ($res = $db->fetch_array($ress))
					{
						$tbl->td(date("d-m-Y", strtotime($res["auction_date"])));
						$tbl->td($res["auction_no"]);
						$tbl->td($res["brand"]);
						$tbl->td($res["model"]);
						$tbl->td($res["type"]);
						$tbl->td($res["number"] . " " . $res["name"]);
						$tbl->td($res["cur_price"]);
						if ($res["cur_price"] >= $res["min_price"])
						{
							$tbl->choise("Afregn", $do, $res["id"]);
						}
						else
						{
							$tbl->choise("Fjern", $do, $res["id"]);
							$tbl->td("<font color=red>Mindstepris ikke opnået</font>");
						}
						$tbl->endrow();
					}
					
					$html .= $tbl->html();
					
				}
				else
				{
					$html .= "Ingen resultater fundet";
				}
			}
			
			if ($vars["invoice_popup"] != "")
			{
				$html .= "
					<script>
					$(document).ready(function() {
						url_popup('/?module=$module&page=$page&do=invoices_pay&id=" . $vars["invoice_popup"] . "&tpl=popup');
					});
					</script>
					";
			}
			
		}	
		else
		{
			// Kasse
			
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					id = '$id' AND
					NOT ISNULL(end_time) AND
					ISNULL(invoice_time) AND
					auction_type = 'live'
				");
			if (!$res = $db->fetch_array())
			{
				header("Location: /site/$_lang_id/$module/$page/$do");
				exit;
			}
			
			if ($res["cur_price"] < $res["min_price"] or $vars["do2"] == "cancel")
			{
				// Mindstepris ikke opnået
				$db->execute("
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						invoice_time = '" . date("Y-m-d H:i:s") . "'
					WHERE
						id = '$id'
					");
				header("Location: /site/$_lang_id/$module/$page/$do");
				exit;
			}
			
			if ($vars["do2"] == "create_invoice")
			{
				// Opretter faktura
				$invoice_no = OBA_invoice_no();
				$db->execute("
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_invoices
					(
						invoice_no,
						invoice_date,
						name,
						address,
						zipcode,
						city,
						phone,
						email
					)
					VALUES
					(
						'$invoice_no',
						'" . date("Y-m-d") . "',
						'" . $db->escape($vars["name"]) . "',
						'" . $db->escape($vars["address"]) . "',
						'" . $db->escape($vars["zipcode"]) . "',
						'" . $db->escape($vars["city"]) . "',
						'" . $db->escape($vars["phone"]) . "',
						'" . $db->escape($vars["email"]) . "'
					)
					");
				$invoice_id = $db->insert_id();
					
				// Opretter linier
				$i = 0;
				while ($vars["title" . $i] != "")
				{
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
							'$invoice_id',
							'" . $db->escape($vars["title" . $i]) . "',
							'" . intval($vars["quantity" . $i]) . "',
							'" . number_format($vars["price" . $i], 2, ".", "") . "',
							'" . ($vars["no_vat" . $i] != "" ? 1 : 0) . "'
						)
						");
					$i++;
				}
				
				$db->execute("
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_auctions
					SET
						invoice_id = '$invoice_id',
						invoice_time = '" . date("Y-m-d H:i:s") . "'
					WHERE
						id = '" . $res["id"] . "'
					");
				
				header("Location: /site/$_lang_id/$module/$page/$do?invoice_popup=$invoice_id");
				exit;
			}
			
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_types
				WHERE
					id = '" . $res["type_id"] . "'
				");
			$type = $db->fetch_array();
			
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_user_" . $module . "_cust
				WHERE
					id = '" . $res["bidder_id"] . "'
				");
			$cust = $db->fetch_array();
			if ($cust["extra_type"] == "") $cust["extra_type"] = "private";
			
			$tmp = new tpl("MODULE|$module|admin_auctions_cashregister");
			$tmp->set("image_id", $db->execute_field("
				SELECT
					id
				FROM
					" . $_table_prefix . "_module_" . $module . "_images
				WHERE
					auction_id = '" . $res["id"] . "'
				"));
				
			// Auktion
			$tmp->set("id", $res["id"]);
			$tmp->set("auction_date", date("d-m-Y", strtotime($res["auction_date"])));
			$tmp->set("auction_no", $res["auction_no"]);
			
			// Bud
			$tmp->set("cur_price", number_format($res["cur_price"], 2, ".", ""));
			
			// Kunde
			$tmp->set("name", $cust["name"]);
			list($a1, $a2) = explode("\n", $cust["address"]);
			$tmp->set("address", $a1);
			$tmp->set("address2", $a2);
			$tmp->set("zipcode", $cust["zipcode"]);
			$tmp->set("city", $cust["city"]);
			$tmp->set("phone", $cust["phone"]);
			$tmp->set("email", $cust["email"]);
			
			// Bil
			$tmp->set("brand", stripslashes($res["brand"]));
			$tmp->set("model", stripslashes($res["model"]));
			$tmp->set("no_vat", $res["no_vat"] == 1 ? 0 : 1); // Det er omvendt, hvis "Uden moms" er afkrydset, så lægger vi moms til
			$tmp->set("no_tax", $res["no_tax"]);
			
			// Salær
			$tmp->set("salery_procent", $type["procent_" . $cust["extra_type"]]);
			$tmp->set("salery_min", $type["min_" . $cust["extra_type"]]);
			$tmp->set("salery_max", $type["max_" . $cust["extra_type"]]);

			// Faste beløb			
			$tmp->set("trans_start", module_setting("trans_start"));
			$tmp->set("trans_pr_km", module_setting("trans_pr_km"));
			$tmp->set("re_registration", module_setting("re_registration"));
			$tmp->set("storage_pr_day", module_setting("storage_pr_day"));
			
			// Moms og firmaadresse
			$tmp->set("vat_pct", module_setting("vat_pct"));
			$tmp->set("company_address", module_setting("company_address"));
			$tmp->set("company_zipcity", module_setting("company_zipcity"));
			
			$html .= $tmp->html();
			
		}
		
	}
	elseif ($do == "auctions_mortgage")
	{
		// Pant
	
		$msg = new message;
		$msg->title("Pant");
		$html .= $msg->html();
		
		$tbl = new table;
		$tbl->th("Auktionsdato");
		$tbl->th("Auktionsnr");
		$tbl->th("Reg./stelnr.");
		$tbl->th("Mærke");
		$tbl->th("Model");
		$tbl->th("{LANG|Valg}", 2);
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				NOT ISNULL(invoice_time) AND 
				invoice_id > 0 AND
				ISNULL(mortgage_time) AND
				`cancel` = 0 AND
				auction_type = 'live'
			ORDER BY
				auction_date,
				auction_no
			");
		
		while ($res = $db->fetch_array())
		{
			$tbl->td(date("d-m-Y", strtotime($res["auction_date"])));
			$tbl->td($res["auction_no"]);
			$tbl->td(htmlentities(stripslashes($res["regno"])));
			$tbl->td(htmlentities(stripslashes($res["brand"])));
			$tbl->td(htmlentities(stripslashes($res["model"])));
			$tbl->choise("{LANG|Tjek for pant}", "auctions_mortgage_show", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring));
			$tbl->endrow();
		}
		
		if ($db->num_rows() == 0)
		{
			$tbl->td("{LANG|Ingen}...", 10);
		} 
		
		$html .= $tbl->html();
	}
	elseif ($do == "auctions_mortgage_show")
	{
		// Tjek for Pant
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				id = '$id' AND
				NOT ISNULL(invoice_time) AND 
				invoice_id > 0 AND
				ISNULL(mortgage_time) AND
				`cancel` = 0 AND
				auction_type = 'live'
			");
		if (!$res = $db->fetch_array())
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_mortgage");
			exit;
		}
	
		$msg = new message;
		$msg->title("Tjek for pant");
		$html .= $msg->html();

		$frm = new form;
		$frm->submit_text = "Gem";
		$frm->tpl("th", "Auktionsinfo");
		$frm->tpl("td2", "Auktionsdato:", date("d-m-Y", strtotime($res["auction_date"])));
		$frm->tpl("td2", "Auktionsnr:", $res["id"]);
		$frm->tpl("td2", "Regnr.:", $res["regno"]);
		$frm->tpl("td2", "Stelnr.:", $res["chasno"]);
		$frm->tpl("td2", "Mærke:", $res["brand"]);
		$frm->tpl("td2", "Model:", $res["model"]);
		$frm->tpl("td2", "Variant:", $res["variant"]);
		$frm->tpl("td2", "Type:", $res["type"]);
		$frm->tpl("td2", "Brændstof:", $res["fuel"]);
		$frm->tpl("td2", "Døre:", $res["doors"]);
		$frm->tpl("td2", "Årgang:", $res["year"]);
		$frm->tpl("td2", "Km-stand:", $res["km"]);
		$frm->tpl("td2", "Farve:", $res["color"]);
		$frm->tpl("td2", "Nysynet:", $res["newly_tested"] == 1 ? "Ja" : "Nej");
		$frm->tpl("td2", "Indregistreret:", $res["is_regged"] == 1 ? "Ja" : "Nej");
		$frm->tpl("td2", "Servicebog:", $res["service"] == 1 ? "Ja" : "Nej");
		$frm->tpl("th", "Pant");
		$frm->tpl("td", "<input type=\"button\" value=\"Slå op i nummerplade.net\" onclick=\"url_popup('http://www.nummerplade.net/soeg/?" .
			((strlen($res["regno"]) <= 7) ? "regnr" : "stelnr") . "=" . $res["regno"] . "');\" />");
		$frm->tpl("td", "<input type=\"button\" value=\"Åbn tinglysning.dk (stelnr skal kopieres ind manuelt)\" onclick=\"html_popup($('#ContainerTinglysningDk').val());\" />");
		$frm->input(
			"Angiv restgæld",
			"mortgage_price",
			"0",
			"^[0-9]+$",
			"Tal påkrævet"
			);
			
		if ($frm->done())
		{
			$db->execute("
				UPDATE
					" . $_table_prefix . "_module_" . $module . "_auctions
				SET
					mortgage_time = '" . date("Y-m-d H:i:s") . "',
					mortgage_price = '" . intval($frm->values["mortgage_price"]) . "'
				WHERE
					id = '" . $res["id"] . "'
				");
			header("Location: /site/$_lang_id/$module/$page/auctions_mortgage");
			exit;
		}
			
		$html .= "<textarea id=\"ContainerTinglysningDk\" style=\"display: none;\">Stelnr: <input type=text readonly value=\"" . $res["chasno"] . "\" size=\"20\" onclick=\"this.select();\" /><br>" .
			"<iframe src=\"https://www.tinglysning.dk/tinglysning/forespoerg/bilbogen/bilbogen.xhtml\" width=\"900\" height=\"600\"></iframe></textarea>";
		
		$html .= $frm->html();
				
	}
	elseif ($do == "auctions_seller_accounting")
	{
		// Afregn sælger
	
		$msg = new message;
		$msg->title("Afregn sælger");
		$html .= $msg->html();
		
		$tbl = new table;
		$tbl->th("Auktionsdato");
		$tbl->th("Auktionsnr");
		$tbl->th("Reg./stelnr.");
		$tbl->th("Mærke");
		$tbl->th("Model");
		$tbl->th("{LANG|Valg}", 2);
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				NOT ISNULL(mortgage_time) AND 
				ISNULL(seller_account_time) AND
				auction_type = 'live'
			ORDER BY
				auction_date,
				auction_no
			");
		
		while ($res = $db->fetch_array())
		{
			$tbl->td(date("d-m-Y", strtotime($res["auction_date"])));
			$tbl->td($res["auction_no"]);
			$tbl->td(htmlentities(stripslashes($res["regno"])));
			$tbl->td(htmlentities(stripslashes($res["brand"])));
			$tbl->td(htmlentities(stripslashes($res["model"])));
			$tbl->choise("{LANG|Afregn sælger}", "auctions_seller_accounting_show", $res["id"] . "&_paging_page=" . $vars["_paging_page"] . "&searchstring=" . urlencode($searchstring));
			$tbl->endrow();
		}
		
		if ($db->num_rows() == 0)
		{
			$tbl->td("{LANG|Ingen}...", 10);
		} 
		
		$html .= $tbl->html();
		
		if ($vars["showinvoiceid"] != "")
		{
			$html .= "<script> $(document).ready(function() {
				url_popup('/?module=$module&page=$page&do=invoices_pay&id=" . intval($vars["showinvoiceid"]) . "&tpl=popup');
			}); </script>";
		}
	}
	elseif ($do == "auctions_seller_accounting_show")
	{
		// Tjek afregn sælger
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				id = '$id' AND
				NOT ISNULL(mortgage_time) AND 
				ISNULL(seller_account_time) AND
				auction_type = 'live'
			");
		if (!$res = $db->fetch_array())
		{
			header("Location: /site/$_lang_id/$module/$page/auctions_seller_accounting");
			exit;
		}
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_types
			WHERE
				id = '" . $res["type_id"] . "'
			");
		$type = $db->fetch_array();
	
		$msg = new message;
		$msg->title("Afregn sælger");
		$html .= $msg->html();

		$frm = new form;
		$frm->submit_text = "Afregn og opret faktura";
		$frm->tpl("th", "Auktionsinfo");
		$frm->tpl("td2", "Auktionsdato:", date("d-m-Y", strtotime($res["auction_date"])));
		$frm->tpl("td2", "Auktionsnr:", $res["id"]);
		$frm->tpl("td2", "Reg./stelnr.:", $res["regno"]);
		$frm->tpl("td2", "Mærke:", $res["brand"]);
		$frm->tpl("td2", "Model:", $res["model"]);
		$frm->tpl("td2", "Variant:", $res["variant"]);
		$frm->tpl("td2", "Type:", $res["type"]);
		$frm->tpl("td2", "Brændstof:", $res["fuel"]);
		$frm->tpl("td2", "Døre:", $res["doors"]);
		$frm->tpl("td2", "Årgang:", $res["year"]);
		$frm->tpl("td2", "Km-stand:", $res["km"]);
		$frm->tpl("td2", "Farve:", $res["color"]);
		$frm->tpl("td2", "Nysynet:", $res["newly_tested"] == 1 ? "Ja" : "Nej");
		$frm->tpl("td2", "Indregistreret:", $res["is_regged"] == 1 ? "Ja" : "Nej");
		$frm->tpl("td2", "Servicebog:", $res["service"] == 1 ? "Ja" : "Nej");
		
		$frm->tpl("th", "Sælger");
		$frm->input(
			"Navn",
			"seller_name",
			stripslashes($res["seller_name"]),
			"^.+$",
			"Påkrævet"
			);
		list($a1, $a2) = explode("\n", $res["seller_address"]);
		$frm->input(
			"Adresse",
			"seller_address",
			$a1,
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Adresse 2",
			"seller_address2",
			$a2
			);
		$frm->input(
			"Postnr",
			"seller_zipcode",
			stripslashes($res["seller_zipcode"]),
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"By",
			"seller_city",
			stripslashes($res["seller_city"]),
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Telefon",
			"seller_phone",
			stripslashes($res["seller_phone"]),
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"E-mail",
			"seller_email",
			stripslashes($res["seller_email"]),
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Bank - Reg.nr.",
			"seller_bank_regno",
			stripslashes($res["seller_bank_regno"]),
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Bank - Kontonr.",
			"seller_bank_account",
			stripslashes($res["seller_bank_account"]),
			"^.+$",
			"Påkrævet"
			);
		
		$frm->tpl("th", "Afregning til sælger");
		$frm->tpl("td2", "Salgspris:", OBA_price($res["cur_price"]));
		$frm->tpl("td2", "Moms salgspris:", "<span id=\"cur_price_vat\">" . ($res["no_vat"] == 0 ? "* momsfri" : "") . "</span>");
		
		$salery = $res["cur_price"] / 100 * intval($type["procent_" . $res["seller_type"]]);
		if ($salery < $type["min_" . $res["seller_type"]]) $salery = $type["min_" . $res["seller_type"]];
		if ($salery > $type["max_" . $res["seller_type"]]) $salery = $type["max_" . $res["seller_type"]];
		$frm->input(
			"Sælger salær (" . $type["procent_" . $res["seller_type"]] . "%)",
			"seller_salery",
			number_format($salery, 2, ".", ""),
			"^[0-9,\.]+$",
			"Beløb påkrævet"
			);
		$frm->tpl("td2", "Moms salær:", "<span id=\"seller_salery_vat\"></span>");
		$frm->input(
			"Tilbageholdt pant",
			"mortgage_price",
			number_format($res["mortgage_price"], 2, ".", ""),
			"^[0-9,\.]+$",
			"Beløb påkrævet"
			);
		$frm->tpl("td2", "Overføres til sælger:", "<span id=\"seller_account_price\"></span>");
		
		if ($frm->done())
		{
			$seller_salery = floatval(str_replace(",", ".", $frm->values["seller_salery"]));
			$mortgage_price = floatval(str_replace(",", ".", $frm->values["mortgage_price"]));
			
			// Opdaterer auktion
			$db->execute("
				UPDATE
					" . $_table_prefix . "_module_" . $module . "_auctions
				SET
					seller_name = '" . $db->escape($vars["seller_name"]) . "',
					seller_address = '" . $db->escape(trim($vars["seller_address1"] . "\n" . $vars["seller_address2"])) . "',
					seller_zipcity = '" . $db->escape($vars["seller_zipcity"]) . "',
					seller_phone = '" . $db->escape($vars["seller_phone"]) . "',
					seller_email = '" . $db->escape($vars["seller_email"]) . "',
					seller_bank_regno = '" . $db->escape($vars["seller_bank_regno"]) . "',
					seller_bank_account = '" . $db->escape($vars["seller_bank_account"]) . "',
					seller_salery = '" . number_format($seller_salery, 2, ".", "") . "',
					mortgage_price = '" . number_format($mortgage_price, 2, ".", "") . "'
				WHERE
					id = '$id'
				");			
			
			// Opretter faktura
			$invoice_no = OBA_invoice_no();
			$db->execute("
				INSERT INTO
					" . $_table_prefix . "_module_" . $module . "_invoices
				(
					invoice_no,
					invoice_date,
					name,
					address,
					zipcode,
					city,
					phone,
					email,
					paid_date,
					paid_method
				)
				VALUES
				(
					'$invoice_no',
					'" . date("Y-m-d") . "',
					'" . $db->escape($vars["seller_name"]) . "',
					'" . $db->escape($vars["seller_address"]) . "',
					'" . $db->escape($vars["seller_zipcode"]) . "',
					'" . $db->escape($vars["seller_city"]) . "',
					'" . $db->escape($vars["seller_phone"]) . "',
					'" . $db->escape($vars["seller_email"]) . "',
					'" . date("Y-m-d") . "',
					'bank'
				)
				");
			$invoice_id = $db->insert_id();
			
			// Opretter linier
			$lines = array(
				array(
					"title" => "Auktionsdato: " . date("d-m-Y", strtotime($res["auction_date"])) . "\r\n" .
						"Auktionsnr.: " . $res["auction_no"] . "\r\n" .
						"Regnr./stelnr.: " . $res["regno"] . "\r\n" .
						"Mærke: " . $res["brand"] . "\r\n" . 
						"Model: " . $res["model"],
					"price" => -$res["cur_price"],
					"no_vat" => ($res["no_vat"] == 0)
					),
				array(
					"title" => "Salær",
					"price" => $seller_salery
					),
				array(
					"title" => "Tilbageholdt pant\r\n\r\n" .
						"Beløbet overføres til konto " . $vars["seller_bank_regno"] . "-" . $vars["seller_bank_account"],
					"price" => $mortgage_price,
					"no_vat" => true
					)
				);
					
			for ($i = 0; $i < count($lines); $i++)
			{	
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
						'$invoice_id',
						'" . $db->escape($lines[$i]["title"]) . "',
						'1',
						'" . number_format($lines[$i]["price"], 2, ".", "") . "',
						'" . ($lines[$i]["no_vat"] ? 1 : 0) . "'
					)
					");
			}

			$db->execute("
				UPDATE
					" . $_table_prefix . "_module_" . $module . "_auctions
				SET
					seller_account_time = '" . date("Y-m-d H:i:s") . "',
					seller_account_price = '" . number_format($res["cur_price"] - ($seller_salery * (1 + intval(module_setting("vat_pct")) / 100)) - $mortgage_price, 2, ".", "") . "',
					seller_account_invoice_id = '$invoice_id'
				WHERE
					id = '" . $res["id"] . "'
				");
				
			header("Location: /site/$_lang_id/$module/$page/auctions_seller_accounting?showinvoiceid=$invoice_id");
			exit;
		}
			
		$html .= $frm->html();

		// Lidt hard-code - ups		
		$tmp = new tpl("MODULE|$module|admin_auctions_seller_accounting_show");
		$tmp->set("cur_price", number_format($res["cur_price"], 2, ".", ""));
		$tmp->set("vat_pct", module_setting("vat_pct"));
		$tmp->set("no_vat", $res["no_vat"] == 1 ? 0 : 1);
		$html .= $tmp->html();
				
	}
	elseif ($do == "auctions_sold" or $do == "auctions_not_sold" or $do == "auctions_almost_sold")
	{
		// Afsluttede auktioner
		
		$sql_where = "";
		
		$msg = new message;
		if ($do == "auctions_sold")
		{
			$msg->title("Solgte biler");
			$sql_where = " AND a.cur_price > 0 AND a.cur_price >= a.min_price ";
		}
		elseif ($do == "auctions_not_sold")
		{
			$msg->title("Ikke solgte biler");
			$sql_where = " AND (a.cur_price = 0 OR a.min_price - a.cur_price > 10000 AND a.cur_price > 0 AND a.min_price > a.cur_price) ";
		}
		elseif ($do == "auctions_almost_sold")
		{
			$msg->title("Næsten solgte biler");
			$sql_where = " AND a.cur_price > 0 AND a.cur_price < a.min_price AND a.min_price - a.cur_price <= 10000 ";
		}
		$html .= $msg->html();

		$html .= "
			<a href=\"/site/$_lang_id/$module/$page/auctions_sold\" class=\"Button" . ($do == "auctions_sold" ? "Active" : "") . "\">Solgte biler</a>
			<a href=\"/site/$_lang_id/$module/$page/auctions_almost_sold\" class=\"Button" . ($do == "auctions_almost_sold" ? "Active" : "") . "\">Næsten solgte biler</a>
			<a href=\"/site/$_lang_id/$module/$page/auctions_not_sold\" class=\"Button" . ($do == "auctions_not_sold" ? "Active" : "") . "\">Ikke solgte biler</a>
			<div class=\"Clear\"></div>
			<br />
			";
			
		$tbl = new table;
		$tbl->th("Katalog nummer");
		$tbl->th("Mærke");
		$tbl->th("Model");
		$tbl->th("Mindstepris");
		$tbl->th("Højeste bud");
		$tbl->th("Byder");
		$tbl->th("Valg", 5);
		$tbl->endrow();
			
		$ress = $db->execute("
			SELECT	
				a.*,
				c.name AS bidder_name,
				c.email AS bidder_email
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions AS a
			LEFT JOIN
				" . $_table_prefix . "_user_" . $module . "_cust AS c
			ON
				c.id = a.bidder_id
			WHERE
				a.auction_type = 'online' AND
				NOT ISNULL(a.end_time) AND
				a.end_time < '" . date("Y-m-d H:i:00") . "'
				$sql_where
			");
		while ($res = $db->fetch_array($ress))
		{
			$tbl->td($res["auction_no"]);
			$tbl->td($res["brand"]);
			$tbl->td($res["model"]);
			$tbl->td(number_format($res["min_price"], 0, ",", "."));
			$tbl->td(number_format($res["cur_price"], 0, ",", "."));
			$tbl->td("<a href=\"mailto:" . $res["bidder_email"] . "\">" . $res["bidder_name"] . "</a>");
			if ($do == "auctions_not_sold")
			{
				$tbl->choise("Gentag", "auctions_reset", $res["id"]);
			}
			else
			{
				$tbl->choise("Annuller", "auctions_cancel_bid", $res["id"], "Er du sikker? - bud sættes til 0,-");
			}
			$tbl->choise("Ret", "auctions_edit", $res["id"]);
			$tbl->choise("Slet", "auctions_delete", $res["id"], "Er du sikker? - auktionen slettes permanent");
			if ($do != "auctions_sold" and $res["cur_price"] > 0)
			{
				$tbl->choise("Accepter bud", "auctions_accept_current_bid", $res["id"], "Er du sikker? - mindste-pris sættes = aktuelt bud");
			}
			$tbl->endrow();
		}
		
		if ($db->num_rows($ress) == 0)
		{
			$tbl->td("Ingen resultater...", 6);
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
		
	}
	elseif ($do == "auctions_cancel_bid")
	{
		// Annuller bud
		$db->execute("
			UPDATE
				" . $_table_prefix . "_module_" . $module . "_auctions
			SET
				cur_price = 0,
				bidder_id = 0
			WHERE
				id = '$id' AND
				NOT ISNULL(end_time) AND
				end_time < '" . date("Y-m-d H:i:00") . "'
			");
		die("<script> history.back(); </script>");
	}
	elseif ($do == "auctions_accept_current_bid")
	{
		// Accepter aktuelt bud
		$sql = "
			UPDATE
				" . $_table_prefix . "_module_" . $module . "_auctions
			SET
				min_price = cur_price,
				end_email_time = NULL
			WHERE
				id = '$id' AND
				NOT ISNULL(end_time) AND
				end_time < '" . date("Y-m-d H:i:00") . "' AND
				cur_price > 0 AND
				bidder_id > 0
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);
		die("<script> history.back(); </script>");
	}	
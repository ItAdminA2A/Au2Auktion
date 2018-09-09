<?php
	/*
		Indstillinger
	*/

	if ($do == "settings_variables")
	{
		// Variabler
		
		$array_vars = array(
			"fuel" => "Brændstof",
			"motorsize" => "Motorstørrelse",
			"doors" => "Døre",
			"geartype" => "Geartype",
			"gearcount" => "Gear",
			"color" => "Farve",
			"wheel_drive" => "Hjultræk",
			"type" => "Type",
			"equipment" => "Udstyr",
			"equipment_airbags" => "Udstyr - Antal airbags",
			"tires_type" => "Dæk - Type",
			"tires_rim" => "Dæk - Fælg",
			"tires_depth" => "Dæk - Mønster",
			"condition_inside" => "Stand - Indvendig",
			"condition_mecanical" => "Stand - Mekanisk",
			"condition_lacquer" => "Stand - Lak",
			"condition_light_front" => "Stand - Forlygter",
			"condition_light_back" => "Stand - Baglygter",
			"condition_light_fog" => "Stand - Tågelygter",
			"condition_damage" => "Stand - Tidligere skadet",
			"condition_electric" => "Stand - Elektrisk",
			"maintain_book" => "Vedligehold - Medfølger servicebog",
			"maintain_service_ok" => "Vedligehold - Service OK",
			"maintain_rust_treat" => "Vedligehold - Undervognsbehandlet",
			"maintain_brake" => "Vedligehold - Bremseklodser skiftet",
			"maintain_last_service" => "Vedligehold -> Sidste service",
			"maintain_next_service" => "Vedligehold -> Næste service",
			"maintain_timing_belt" => "Vedligehold -> Tandrem skiftet",
			"maintain_oil" => "Vedligehold -> Olie",
			"exterior_windshield" => "Udvendig - Forrude",
			"exterior_dent" => "Udvendig - Buler",
			"exterior_scratch" => "Udvendig - Ridser",
			"exterior_rust" => "Udvendig - Rust",
			"exterior_stone" => "Udvendig - Stenslag",
			"exterior_condition" => "Udvendig - Stand",
			"exterior_mirror_scratch" => "Udvendig - Spejle - Ridser",
			"exterior_mirror_stone" => "Udvendig - Spejle - Stenslag",
			"exterior_mirror_glass" => "Udvendig - Spejle - Spejlglas"
			);
			
		$var = $vars["var"];
			
		$msg = new message;
		$msg->title("Variabler");
		$html .= $msg->html();
		
		$select_var = "<option></option>";
		foreach ($array_vars as $key => $val) $select_var .= "<option value=\"$key\" " . ($var == $key ? "selected" : "") . ">$val</option>";
		
		$html .= "Vælg variabel: <select onchange=\"document.location.href = '/site/$_lang_id/$module/$page/$do?var=' + this.options[this.selectedIndex].value;\">$select_var</select><br /><br />";
		
		if (isset($array_vars[$var]))
		{
			$frm = new form;
			$frm->hidden("var", $var);
			$frm->submit_text = "Gem";
				
			$frm->tpl("th", $array_vars[$var]);
			$frm->textarea(
				"En værdi pr. linie",
				"data",
				module_setting("vars_" . $var),
				"",
				"",
				"",
				30,
				30
				);
				
			if ($frm->done())
			{
				$data = "";
				$lines = explode("\n", $frm->values["data"]);
				for ($i = 0; $i < count($lines); $i++)
				{
					$line = trim($lines[$i]);
					if ($line != "")
					{
						if ($data != "") $data .= "\n";
						$data .= $line;
					}
				}
				
				module_setting("vars_" . $var, $data);
				OBA_sync("SETTING", "vars_" . $var . "|" . $data);
				
				header("Location: /site/$_lang_id/$module/$page/$do?var=$var");
				exit;
			}
			
			$html .= $frm->html();
		}
		
	}	
	elseif ($do == "settings_general")
	{
		// Indstillinger
		
		$msg = new message;
		$msg->title("Generelle indstillinger");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->submit_text = "Gem";
			
		$frm->tpl("th", "Beløb");
		$frm->input(
			"Transport startgebyr i DKK",
			"trans_start",
			module_setting("trans_start"),
			"^[0-9,\.]+$",
			"Tal påkrævet"
			);
		$frm->input(
			"Transport pr. km i DKK",
			"trans_pr_km",
			module_setting("trans_pr_km"),
			"^[0-9,\.]+$",
			"Tal påkrævet"
			);
		$frm->input(
			"Omregistrering i DKK",
			"re_registration",
			module_setting("re_registration"),
			"^[0-9,\.]+$",
			"Tal påkrævet"
			);
		$frm->input(
			"Opbevaring pr. dag i DKK",
			"storage_pr_day",
			module_setting("storage_pr_day"),
			"^[0-9,\.]+$",
			"Tal påkrævet"
			);
			
		$frm->tpl("th", "Satser");
		$frm->input(
			"Moms-%",
			"vat_pct",
			module_setting("vat_pct"),
			"^[0-9]+$",
			"Tal påkrævet"
			);
			
		$frm->tpl("th", "Firmainfo");
		$frm->input(
			"CVR",
			"company_vat",
			module_setting("company_vat")
			);
		$frm->input(
			"Firma",
			"company_name",
			module_setting("company_name")
			);
		$frm->input(
			"Addresse",
			"company_address",
			module_setting("company_address")
			);
		$frm->input(
			"Postnr. & by",
			"company_zipcity",
			module_setting("company_zipcity")
			);
			
		$frm->tpl("th", "Faktura");
		$frm->textarea(
			"Fakturabund",
			"invoice_bottom",
			module_setting("invoice_bottom")
			);
			
		if ($frm->done())
		{
			module_setting("seller_salery", number_format(str_replace(",", ".", $frm->values["seller_salery"]), 2, ".", ""));
			module_setting("bidder_salery", number_format(str_replace(",", ".", $frm->values["bidder_salery"]), 2, ".", ""));
			module_setting("trans_start", number_format(str_replace(",", ".", $frm->values["trans_start"]), 2, ".", ""));
			module_setting("trans_pr_km", number_format(str_replace(",", ".", $frm->values["trans_pr_km"]), 2, ".", ""));
			module_setting("re_registration", number_format(str_replace(",", ".", $frm->values["re_registration"]), 2, ".", ""));
			module_setting("storage_pr_day", number_format(str_replace(",", ".", $frm->values["storage_pr_day"]), 2, ".", ""));
			module_setting("vat_pct", number_format(str_replace(",", ".", $frm->values["vat_pct"]), 0, ".", ""));
			module_setting("company_vat", $frm->values["company_vat"]);
			module_setting("company_name", $frm->values["company_name"]);
			module_setting("company_address", $frm->values["company_address"]);
			module_setting("company_zipcity", $frm->values["company_zipcity"]);
			module_setting("invoice_bottom", $frm->values["invoice_bottom"]);
			OBA_sync("SETTING", "seller_salery|" . number_format(str_replace(",", ".", $frm->values["seller_salery"]), 2, ".", ""));
			OBA_sync("SETTING", "bidder_salery|" . number_format(str_replace(",", ".", $frm->values["bidder_salery"]), 2, ".", ""));
			OBA_sync("SETTING", "trans_start|" . number_format(str_replace(",", ".", $frm->values["trans_start"]), 2, ".", ""));
			OBA_sync("SETTING", "trans_pr_km|" . number_format(str_replace(",", ".", $frm->values["trans_pr_km"]), 2, ".", ""));
			OBA_sync("SETTING", "re_registration|" . number_format(str_replace(",", ".", $frm->values["re_registration"]), 2, ".", ""));
			OBA_sync("SETTING", "storage_pr_day|" . number_format(str_replace(",", ".", $frm->values["storage_pr_day"]), 2, ".", ""));
			OBA_sync("SETTING", "vat_pct|" . number_format(str_replace(",", ".", $frm->values["vat_pct"]), 0, ".", ""));
			OBA_sync("SETTING", "company_vat|" . $frm->values["company_vat"]);
			OBA_sync("SETTING", "company_name|" . $frm->values["company_name"]);
			OBA_sync("SETTING", "company_address|" . $frm->values["company_address"]);
			OBA_sync("SETTING", "company_zipcity|" . $frm->values["company_zipcity"]);
			OBA_sync("SETTING", "invoice_bottom|" . $frm->values["invoice_bottom"]);
			
			header("Location: /site/$_lang_id/$module/$page/$do");
			exit;
		}
		
		$html .= $frm->html();
		
	}	
	elseif ($do == "settings_types_add" or $do == "settings_types_edit")
	{
		// Tilføj type
		
		if ($do == "settings_types_edit")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_types
				WHERE
					id = '$id'
				");
			if (!$res = $db->fetch_array())
			{
				header("Location: /site/$_lang_id/$module/$page/settings_types");
				exit;
			}
		}
		else
		{
			$res = false;
		}
		
		$msg = new message;
		$msg->title($do == "settings_types_edit" ? "Rediger type" : "Tilføj type");
		$html .= $msg->html();
		
		$frm = new form;
		$frm->submit_text = "Gem";
		
		$frm->tpl("th", $do == "settings_types_edit" ? "Rediger type" : "Tilføj type");
		$frm->input(
			"Type",
			"title",
			stripslashes($res["title"]),
			"^.+$",
			"Påkrævet"
			);
			
		$frm->tpl("th", "Salærer");
		$frm->tpl("td", "
			<table>
				<tr>
					<td>
						
					</td>
					<th>
						Forhandler
					</th>
					<th>
						Privat
					</th>
				</tr>
				<tr>
					<th>
						Procent
					</th>
					<td>
						<input type=\"text\" name=\"procent_dealer\" value=\"" . $res["procent_dealer"] . "\" />
					</td>
					<td>
						<input type=\"text\" name=\"procent_private\" value=\"" . $res["procent_private"] . "\" />
					</td>
				</tr>
				<tr>
					<th>
						Min
					</th>
					<td>
						<input type=\"text\" name=\"min_dealer\" value=\"" . $res["min_dealer"] . "\" />
					</td>
					<td>
						<input type=\"text\" name=\"min_private\" value=\"" . $res["min_private"] . "\" />
					</td>
				</tr>
				<tr>
					<th>
						Max
					</th>
					<td>
						<input type=\"text\" name=\"max_dealer\" value=\"" . $res["max_dealer"] . "\" />
					</td>
					<td>
						<input type=\"text\" name=\"max_private\" value=\"" . $res["max_private"] . "\" />
					</td>
				</tr>
			</table>
			");
			
		if ($frm->done())
		{
			if ($do == "settings_types_edit")
			{
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_types
					SET
						title = '" . $db->escape($vars["title"]) . "',
						procent_private = '" . intval($vars["procent_private"]) . "',
						min_private = '" . intval($vars["min_private"]) . "',
						max_private = '" . intval($vars["max_private"]) . "',
						procent_dealer = '" . intval($vars["procent_dealer"]) . "',
						min_dealer = '" . intval($vars["min_dealer"]) . "',
						max_dealer = '" . intval($vars["max_dealer"]) . "'
					WHERE
						id = '$id'
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
			}
			else
			{
				$sql = "
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_types
					(
						`title`,
						procent_private,
						min_private,
						max_private,
						procent_dealer,
						min_dealer,
						max_dealer
					)
					VALUES
					(
						'" . $db->escape($vars["title"]) . "',
						'" . intval($vars["procent_private"]) . "',
						'" . intval($vars["min_private"]) . "',
						'" . intval($vars["max_private"]) . "',
						'" . intval($vars["procent_dealer"]) . "',
						'" . intval($vars["min_dealer"]) . "',
						'" . intval($vars["max_dealer"]) . "'
					)
					";
				$db->execute($sql);
				$id = $db->insert_id();
			}
			OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_types", $id);
			
			header("Location: /site/$_lang_id/$module/$page/settings_types");
			exit;
		}

		$html .= $frm->html();
		
	}
	elseif ($do == "settings_types_delete")
	{
		// Slet type
		
		$sql = "
			DELETE FROM
				" . $_table_prefix . "_module_" . $module . "_types
			WHERE
				id = '$id'
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);
		
		header("Location: /site/$_lang_id/$module/$page/settings_types");
		exit;
		
	}
	elseif ($do == "settings_types")
	{
		// Typer
		
		$msg = new message;
		$msg->title("Typer");
		$html .= $msg->html();
		
		$links = new links;
		$links->link("Tilføj", "settings_types_add");
		$html .= $links->html();
		
		$tbl = new table;
		$tbl->th("Tekst");
		$tbl->th("Valg", 2);
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_types
			ORDER BY
				title
			");
			
		while ($db->fetch_array())
		{
			$tbl->td(stripslashes($db->array["title"]));
			$tbl->choise("Ret", "settings_types_edit", $db->array["id"]);
			$tbl->choise("Slet", "settings_types_delete", $db->array["id"], "Er du sikker?");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
	}
	elseif ($do == "settings_categories_delete")
	{
		// Slet kategori
		
		$sql = "
			DELETE FROM
				" . $_table_prefix . "_module_" . $module . "_categories
			WHERE
				id = '$id'
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);
		
		header("Location: /site/$_lang_id/$module/$page/settings_categories");
		exit;
		
	}
	elseif ($do == "settings_categories" or $do == "settings_categories_edit")
	{
		// Kategorier
		
		$msg = new message;
		$msg->title("Kategorier");
		$html .= $msg->html();
		
		if ($do == "settings_categories_edit")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_categories
				WHERE
					id = '$id'
				");
			if (!$res = $db->fetch_array())
			{
				$do == "settings_categories";
			}
		}
		else
		{
			$res = false;
		}
		
		$frm = new form;
		$frm->submit_text = "Gem";
		
		$frm->tpl("th", $do == "settings_categories_edit" ? "Rediger kategori" : "Tilføj kategori");
		$frm->input(
			"Forkortelse",
			"type",
			$res["type"],
			"^.+$",
			"Påkrævet"
			);
		$frm->input(
			"Tekst",
			"title",
			stripslashes($res["title"]),
			"^.+$",
			"Påkrævet"
			);
			
		if ($frm->done())
		{
			if ($do == "settings_categories_edit")
			{
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_categories
					SET
						`type` = '" . $db->escape($vars["type"]) . "',
						title = '" . $db->escape($vars["title"]) . "'
					WHERE
						id = '$id'
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
			}
			else
			{
				$sql = "
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_categories
					(
						`type`,
						title
					)
					VALUES
					(
						'" . $db->escape($vars["type"]) . "',
						'" . $db->escape($vars["title"]) . "'
					)
					";
				$db->execute($sql);
				$id = $db->insert_id();
			}
			OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_categories", $id);
				
			header("Location: /site/$_lang_id/$module/$page/settings_categories");
			exit;
		}

		$html .= $frm->html();
		
		$tbl = new table;
		$tbl->th("Forkortelse");
		$tbl->th("Tekst");
		$tbl->th("Valg", 2);
		$tbl->endrow();
		
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
			$tbl->td($db->array["type"]);
			$tbl->td(stripslashes($db->array["title"]));
			$tbl->choise("Ret", "settings_categories_edit", $db->array["id"]);
			$tbl->choise("Slet", "settings_categories_delete", $db->array["id"], "Er du sikker?");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
	}
	elseif ($do == "settings_groups_delete")
	{
		// Slet gruppe
		
		$sql = "
			DELETE FROM
				" . $_table_prefix . "_module_" . $module . "_groups
			WHERE
				id = '$id'
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);
		
		header("Location: /site/$_lang_id/$module/$page/settings_groups");
		exit;
		
	}
	elseif ($do == "settings_groups" or $do == "settings_groups_edit")
	{
		// Grupper
		
		$msg = new message;
		$msg->title("Grupper");
		$html .= $msg->html();
		
		if ($do == "settings_groups_edit")
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_groups
				WHERE
					id = '$id'
				");
			if (!$res = $db->fetch_array())
			{
				$do == "settings_groups";
			}
		}
		else
		{
			$res = false;
		}
		
		$types = explode(",", $res["types"]);
		
		$frm = new form;
		$frm->submit_text = "Gem";
		
		$frm->tpl("th", $do == "settings_groups_edit" ? "Rediger gruppe" : "Tilføj gruppe");
		$frm->input(
			"Tekst",
			"title",
			stripslashes($res["title"]),
			"^.+$",
			"Påkrævet"
			);
		$frm->checkbox(
			"Privat",
			"types_private",
			in_array("private", $types)
			);
		$frm->checkbox(
			"Forhandler",
			"types_dealer",
			in_array("dealer", $types)
			);
		
		// Logo
		$logo = "/modules/$module/upl/group_" . $res["id"] . ".jpg";
		if ($res and is_file($_document_root . $logo))
		{
			$frm->tpl("td2", "Logo:", "<img src=\"$logo?" . filemtime($_document_root . $logo) . "\" height=\"100\" />");
			$frm->checkbox("Slet logo", "delete_logo");
		}
		$frm->image("Upload logo", "upload_logo");
			
		if ($frm->done())
		{
			$types = array();
			if ($vars["types_private"] != "") $types[] = "private";
			if ($vars["types_dealer"] != "") $types[] = "dealer";
			
			if ($do == "settings_groups_edit")
			{
				$sql = "
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_groups
					SET
						title = '" . $db->escape($vars["title"]) . "',
						types = '" . $db->escape(implode(",", $types)) . "'
					WHERE
						id = '$id'
					";
				$db->execute($sql);
				OBA_sync("SQL", $sql);
			}
			else
			{
				$sql = "
					INSERT INTO
						" . $_table_prefix . "_module_" . $module . "_groups
					(
						title,
						types
					)
					VALUES
					(
						'" . $db->escape($vars["title"]) . "',
						'" . $db->escape(implode(",", $types)) . "'
					)
					";
				$db->execute($sql);
				$id = $db->insert_id();
			}
			OBA_sync_sql_row($_table_prefix . "_module_" . $module . "_groups", $id);
			
			// Logo
			if ($res and $frm->values["delete_logo"] != "")
			{
				if (unlink($_document_root . $logo)) OBA_sync("DELETE_FILE", "group_" . $res["id"] . ".jpg");
			}
			if ($frm->values["upload_logo"] != "" and $img = imagecreatefromjpeg($_document_root . $frm->values["upload_logo"]))
			{
				if (imagejpeg($img, $_document_root . "/modules/$module/upl/group_" . $id . ".jpg")) OBA_sync("SAVE_FILE", "group_" . $id . ".jpg"); 
			}
			$frm->cleanup();
				
			header("Location: /site/$_lang_id/$module/$page/settings_groups");
			exit;
		}

		$html .= $frm->html();
		
		$tbl = new table;
		$tbl->th("Tekst");
		$tbl->th("Typer");
		$tbl->th("Valg", 2);
		$tbl->endrow();
		
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
			$tbl->td(stripslashes($db->array["title"]));
			$tbl->td(str_replace("private", "Privat", str_replace("dealer", "Forhandler", $db->array["types"])));
			$tbl->choise("Ret", "settings_groups_edit", $db->array["id"]);
			$tbl->choise("Slet", "settings_groups_delete", $db->array["id"], "Er du sikker?");
			$tbl->endrow();
		}
		
		$html .= $tbl->html();
		
	}

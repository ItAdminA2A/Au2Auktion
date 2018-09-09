<?php
	$usrobject = new user($module . "_cust");
	$usrobject->ereg_username = ".";
	$usrobject->ereg_password = ".";

	if ($do == "cust_add" or $do == "cust_edit")
	{
		if ($do == "cust_edit")
		{
			if (!$user = $usrobject->get_user($id))
			{
				header("Location: ./?module=$module&page=$page");
				exit;
			}
		}
		else
		{
			$user = false;
		}
		
		$msg = new message;
		$msg->title("Kunder");
		$html .= $msg->html();
		
		$links = new links;
		$links->link("Tilbage", "cust_overview");
		$html .= $links->html();
		
		$frm = new form;
		$frm->submit_text = "Gem";
		$frm->tpl("th", $do == "cust_add" ? "Tilføj kunde" : "Ret kunde");
		$frm->select(	
			"Type",
			"extra_type",
			$user["extra_type"],
			"",
			"",
			"",
			array(
				array("private", "Privat"),
				array("dealer", "Forhandler")
				)
			);
		$frm->input(
			"Brugernavn",
			"username",
			$user["username"],
			"^[a-z_\-\.\@]+$",
			"Må kun bestå af: a-z - _ . @",
			'
				$usrobject = new user("' . $module . '_cust");
				if ($user = $usrobject->get_user_from_username($this->values["username"]))
				{
					if ($user["id"] != "' . $user["id"] . '")
					{
						$error = "Brugernavn allerede i brug";
					}
				}
			'
			);
		$frm->input(
			$do == "cust_add" ? "Password" : "Skift password",
			"password",
			"",
			$do == "cust_add" ? "^.+$" : "",
			"Påkrævet"
			);
		$frm->checkbox(
			"Godkendt",
			"active",
			$do == "cust_add" or $user["active"] == 1
			);
			
		$frm->tpl("th", "Info");
		$frm->tpl("td2", "Kunde-ID:", "<input type=\"text\" disabled value=\"$id\" />");
		$frm->input(
			"Navn",
			"name",
			stripslashes($user["name"]),
			"^.+$",
			"Påkrævet"
			);
		list($a1, $a2) = explode("\n", $user["address"]);
		$frm->input(
			"Adresse",
			"address",
			$a1
			);
		$frm->input(
			"Adresse 2",
			"address2",
			$a2
			);
		$frm->input(
			"Postnr",
			"zipcode",
			stripslashes($user["zipcode"])
			);
		$frm->input(
			"By",
			"city",
			stripslashes($user["city"])
			);
		$frm->input(
			"Telefon",
			"phone",
			stripslashes($user["phone"])
			);
		$frm->input(
			"E-mail",
			"email",
			stripslashes($user["email"])
			);
		$frm->input(
			"Bank - Reg.nr.",
			"bank_regno",
			stripslashes($user["extra_bank_regno"])
			);
		$frm->input(
			"Bank - Kontonr.",
			"bank_account",
			stripslashes($user["extra_bank_account"])
			);
		$frm->input(
			"CVR-nr. (hvis forhandler)", 
			"vat",
			$user["vat"]
			);

		if ($frm->done())
		{
			if ($do == "cust_add")
			{
				$id = $usrobject->create($frm->values["username"], $frm->values["password"]);
			}
			else
			{
				if ($frm->values["username"] != $user["username"])
				{
					$usrobject->change_username($frm->values["username"], $id);
				}
				if ($frm->values["password"] != "")
				{
					$usrobject->change_password($frm->values["password"], $id);
				}
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
			
			$usrobject->extra_set("type", $frm->values["extra_type"], $id);	
			$usrobject->extra_set("bank_regno", $frm->values["bank_regno"], $id);
			$usrobject->extra_set("bank_account", $frm->values["bank_account"], $id);
			
			if ($frm->values["active"] != "")
			{
				if ($user["active"] == 0 and $frm->values["extra_type"] == "dealer")
				{
					// Send mail til forhandler
					$e = new email;
					$e->to($user["email"]);
					$e->subject("Du er nu godkendt");
					$e->body("Hej " . $frm->values["name"] . "<br><br>Du er nu godkendt som forhandler på " . $_settings_["SITE_TITLE"] . "<br><br>" .
						"<a href=\"http://odensebilauktion.dk/site/da/OBA/user/login\">Log ind her</a>");
					$e->send();
				}
				$usrobject->activate($id);
			}
			else
			{
				$usrobject->deactivate($id);
			}
				
			if ($id) OBA_sync_sql_row($_table_prefix . "_user_" . $module . "_cust", $id);
				
			header("Location: ./?module=$module&page=$page&do=cust_overview");
			exit;
		}
		
		$html .= $frm->html();
	}
	elseif ($do == "cust_delete")
	{
		$usrobject->admin_delete($id);
		OBA_sync("SQL", "DELETE FROM " . $_table_prefix . "_user_" . $module . "_cust WHERE id = '$id'");
		header("Location: ./?module=$module&page=$page&do=cust_overview");
		exit;
	}
	elseif ($do == "cust_overview" or $do == "cust_inactive")
	{
		$msg = new message;
		$msg->title($do == "cust_inactive" ? "Nye kunder" : "Kunder");
		$html .= $msg->html();
		
		if ($do != "cust_inactive")
		{
			$searchstring = $_SESSION[$module . $page . $do . "searchstring"];
			
			$frm = new form;
			$frm->method("get");
			$frm->submit_text = "Søg";
			$frm->input(
				"Søg efter",
				"searchstring",
				$searchstring
				);
			$html .= $frm->html();
			
			if ($frm->done())
			{
				$searchstring = $frm->values["searchstring"];
				$_SESSION[$module . $page . $do . "searchstring"] = $frm->values["searchstring"];
			}
		}
		else
		{
			$searchstring = "";
		}
		
		$sql_where = "";
		if ($do == "cust_inactive")
		{
			$sql_where = " WHERE (active = 0 OR extra_check_done = 0) ";
		}
		else
		{
			$sql_where = " WHERE 1 ";
		}
		if ($searchstring != "")
		{
			$sql_where .= " AND (" .
				"name LIKE '%" . $db->escape($searchstring) . "%' OR " .
				"phone LIKE '%" . $db->escape($searchstring) . "%' OR " .
				"email LIKE '%" . $db->escape($searchstring) . "%') ";
		}
		
		$total = $db->execute_field("
			SELECT
				COUNT(*)
			FROM
				" . $_table_prefix . "_user_" . $module . "_cust
			$sql_where
			");
		$paging = new paging;
		$paging->total($total);
		$limit = $paging->limit(25);
		$start = ($paging->current_page() - 1) * $limit;
		$html .= $paging->html();
		
		$t = new table;
		$t->th("ID");
		$t->th("Type");
		$t->th("Brugernavn");
		$t->th("Navn");
		$t->th("Godkendt");
		$t->th("Valg", 3);
		$t->endrow();
		
		$ress = $db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_user_" . $module . "_cust
			$sql_where
			ORDER BY
				name
			LIMIT
				$start, $limit
			");
			
		$tmp = new tpl("admin_icon_active");
		$icon_active = $tmp->html();
		$tmp = new tpl("admin_icon_inactive");
		$icon_inactive = $tmp->html();
		
		while ($res = $db->fetch_array($ress))
		{
			$t->td($res["id"]);
			$t->td($res["extra_type"] == "dealer" ? "Forhandler" : "Privat");
			$t->td($res["username"]);
			$t->td(stripslashes($res["name"]));
			$t->td($res["active"] == 1 ? $icon_active : $icon_inactive);
			if ($do == "cust_inactive")
			{
				$t->choise($res["extra_type"] == "dealer" ? "Godkend" : "Marker som tjekket", "cust_accept", $res["id"]);
			}
			$t->choise("Ret", "cust_edit", $res["id"]);
			$t->choise("Slet", "cust_delete", $res["id"], "Slet?");
			$t->endrow();
		}
		
		$html .= $t->html();
	}
	elseif ($do == "cust_accept")
	{
		$user = $usrobject->get_user($id);
		if ($user and $user["extra_type"] == "dealer" and $user["active"] == 0)
		{
			// Send mail til forhandler
			$e = new email;
			$e->to($user["email"]);
			$e->subject("Du er nu godkendt");
			$e->body("Hej " . $user["name"] . "<br><br>Du er nu godkendt som forhandler på " . $_settings_["SITE_TITLE"] . "<br><br>" .
				"<a href=\"http://odensebilauktion.dk/site/da/OBA/user/login\">Log ind her</a>");
			$e->send();
		}
		
		$sql = "
			UPDATE
				" . $_table_prefix . "_user_" . $module . "_cust
			SET
				active = 1,
				extra_check_done = 1
			WHERE
				id = '$id'
			";
		$db->execute($sql);
		OBA_sync("SQL", $sql);

		die("<script> history.back(); </script>");
	}
	
<?php
	if ($_db_username == "web14") $html .= "<div style=\"background: green; color: white; margin: 5px; padding: 5px; text-align: center;\">DEMO-SYSTEM</div>";

	$usr = new user($module . "_admin");
	if (!$usr->logged_in and $do != "menu") $do = "login";
	
	require_once($_document_root . "/modules/$module/inc/functions.php");
	
	if ($do == "menu")
	{
		if ($usr->logged_in)
		{
			$array_menu = array(
			/*
				"Fysiske auktioner" => array(
					"Oversigt" => "auctions_overview",
					"Opret" => "auctions_add",
					"Bydernumre" => "auctions_bidno",
					"Print katalog" => "auctions_print",
					"Print rudeark" => "auctions_print_window",
					"Sekretær" => "auctions_secretary",
					"Kasse" => "auctions_cashregister",
					"Pant" => "auctions_mortgage",
					"Afregn sælger" => "auctions_seller_accounting",
					"Auktionsdatoer" => "auctions_dates"
					),
			*/
				"Online auktioner" => array(
					"Oversigt" => "auctions_online",
					"Opret" => "auctions_add?add_type=online",
					"Auktionsdage" => "auctions_online_days",
					"Afregn køber" => "auctions_online_buyer",
					"Afregn sælger" => "auctions_online_seller",
					"Solgte biler" => "auctions_sold",
					"Næsten solgte biler" => "auctions_almost_sold",
					"Ikke solgte biler" => "auctions_not_sold"
					),
			/*
				"Faktura" => array(
					"Oversigt" => "invoices_overview",
					"Opret" => "invoices_add",
					"Daglig" => "invoices_daily"
					),
			*/
				"Kunder" => array(
					"Oversigt" => "cust_overview",
					"Nye kunder" => "cust_inactive",
					"Opret" => "cust_add"
					),
				"Admin-Brugere" => array(
					"Oversigt" => "users_overview",
					"Opret" => "users_add"
					),
				"Indstillinger" => array(
					"Generelle" => "settings_general",
					"Kategorier" => "settings_categories",
					"Auktionstyper" => "settings_types",
					"Variabler" => "settings_variables",
					"Online-salær" => "auctions_online_salery",
					"Grupper" => "settings_groups"
					),
				"Log af" => "logout"
				);
		}
		else
		{
			$array_menu = array(
				"Log ind" => "login"
				);
		}
		$menu = "";
		foreach ($array_menu as $title => $value)
		{
			if (is_array($value))
			{
				// Under-menu
				$submenu = "";
				foreach ($value as $subtitle => $subvalue)
				{
					$tmp = new tpl("MODULE|$module|admin_menu_sub");
					$tmp->set("title", $subtitle);
					$tmp->set("link", $subvalue);
					$submenu .= $tmp->html();
				}
				
				$tmp = new tpl("MODULE|$module|admin_menu_top_with_sub");
				$tmp->set("menu", $submenu);
				$tmp->set("title", $title);
				$menu .= $tmp->html();
			}
			else
			{
				// Top-menu
				$tmp = new tpl("MODULE|$module|admin_menu_top");
				$tmp->set("title", $title);
				$tmp->set("link", $value);
				$menu .= $tmp->html();
			}
		}
		$tmp = new tpl("MODULE|$module|admin_menu");
		$tmp->set("menu", $menu);
		$html .= $tmp->html();
	}
	elseif ($do == "login")
	{
		$frm = new form;
		$frm->submit_text = "Log ind";
		$frm->tpl("th", "Log ind");
		$frm->input(
			"Brugernavn",
			"username",
			"",
			"^.+$",
			"Påkrævet"
			);
		$frm->password(
			"Password",
			"password",
			"",
			"^.+$",
			"Påkrævet"
			);
		if ($frm->done())
		{
			if ($usr->login($frm->values["username"], $frm->values["password"]))
			{
				header("Location: /site/$_lang_id/$module/$page");
				exit;
			}
			$msg = new message;
			$msg->type("error");
			$msg->title("Forkert brugernavn eller password");
			$html .= $msg->html();
		}
		$html .= $frm->html();
	}
	elseif ($do == "logout")
	{
		$usr->logout();
		header("Location: /");
		exit;
	}
	elseif (substr($do, 0, 9) == "settings_")
	{
		require($_document_root . "/modules/$module/inc/settings.php");
	}
	elseif (substr($do, 0, 6) == "users_")
	{
		require($_document_root . "/modules/$module/inc/users.php");
	}
	elseif (substr($do, 0, 9) == "invoices_")
	{
		require($_document_root . "/modules/$module/inc/invoices.php");
	}
	elseif (substr($do, 0, 9) == "auctions_")
	{
		require($_document_root . "/modules/$module/inc/auctions.php");
	}
	elseif (substr($do, 0, 5) == "cust_")
	{
		require($_document_root . "/modules/$module/inc/cust.php");
	}

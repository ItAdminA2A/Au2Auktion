<?php
	$usr = new user($module . "_admin");
	$usr->ereg_username = ".";
	$usr->ereg_password = ".";

	$msg = new message;
	$msg->title("Admin-Brugere");
	$html .= $msg->html();
	
	if ($do == "users_add" or $do == "users_edit")
	{
		if ($do == "users_edit")
		{
			if (!$user = $usr->get_user($id))
			{
				header("Location: ./?module=$module&page=$page");
				exit;
			}
		}
		else
		{
			$user = false;
		}
		
		$links = new links;
		$links->link("Tilbage", "users_overview");
		$html .= $links->html();
		
		$frm = new form;
		$frm->submit_text = "Gem";
		$frm->tpl("th", $do == "users_add" ? "Tilføj bruger" : "Ret bruger");
		$frm->input(
			"Brugernavn",
			"username",
			$user["username"],
			"^[a-z_\-\.\@]+$",
			"Må kun bestå af: a-z - _ . @",
			'
				$usr = new user("' . $module . '");
				if ($user = $usr->get_user_from_username($this->values["username"]))
				{
					if ($user["id"] != "' . $user["id"] . '")
					{
						$error = "Brugernavn allerede i brug";
					}
				}
			'
			);
		$frm->input(
			$do == "add" ? "Password" : "Skift password",
			"password"
			);
			
		if ($frm->done())
		{
			if ($do == "users_add")
			{
				$id = $usr->create($frm->values["username"], $frm->values["password"]);
			}
			else
			{
				if ($frm->values["username"] != $user["username"])
				{
					$usr->change_username($frm->values["username"], $id);
				}
				if ($frm->values["password"] != "")
				{
					$usr->change_password($frm->values["password"], $id);
				}
			}
			
			header("Location: ./?module=$module&page=$page&do=users_overview");
			exit;
		}
		
		$html .= $frm->html();
	}
	elseif ($do == "users_delete")
	{
		if ($id > 1) $usr->admin_delete($id);
		header("Location: ./?module=$module&page=$page&do=users_overview");
		exit;
	}
	elseif ($do == "users_overview")
	{
		$links = new links;
		$links->link("Tilføj bruger", "users_add");
		$html .= $links->html();
		
		$t = new table;
		$t->th("Brugernavn");
		$t->th("Valg", 2);
		$t->endrow();
		
		$ress = $usr->admin_search("", 0, 9999);
		
		while ($res = $ress->fetch_array())
		{
			$t->td($res["username"]);
			$t->choise("Ret", "users_edit", $res["id"]);
			if ($res["id"] > 1 and $res["id"] != $usr->user_id) $t->choise("Slet", "users_delete", $res["id"], "Slet?");
			$t->endrow();
		}
		
		$html .= $t->html();
	}

<?php
	if ($do == "invoices_pdf" or $do == "invoices_pdf_print" or $do == "invoices_pdf_email")
	{
		// Faktura som PDF + print + e-mail

		$fpdf = OBA_invoice_fpdf($id, $vars["copy"] == "true");		
		
		if ($do == "invoices_pdf_print")
		{
			// Print PDF
			$tmpfile = $_document_root . "/tmp/" . uniqid(time()) . ".pdf";
			$fpdf->Output($tmpfile, "F");
			if (print_file($tmpfile))
			{
				if ($vars["copy"] != "true")
				{
					// Print kopi
					header("Location: /site/$_lang_id/$module/$page/$do/$id?copy=true&tpl=popup");
					exit;
				}
				$html .= "Print OK";
			}
			else
			{
				$html .= "Kunne ikke printe";
			}
			unlink($tmpfile);
		}
		elseif ($do == "invoices_pdf_email")
		{
			// E-mail PDF
			$tmpfile = $_document_root . "/tmp/" . uniqid(time()) . ".pdf";
			$fpdf->Output($tmpfile, "F");
			
			$email = $db->execute_field("
				SELECT
					email
				FROM
					" . $_table_prefix . "_module_" . $module . "_invoices
				WHERE
					id = '$id'
				");
			
			if ($email != "")
			{
				$e = new email;
				$e->to($email);
				$e->subject("Faktura " . ($res["invoice_no"] > 0 ? "Fakturanr.:" : ""));
				$e->body("Se vedhæftede fil");
				$e->attach($tmpfile);
				$e->send();
				
				$html .= "E-mail OK: $email {TPL|LAYOUT|{SITE_LAYOUT}|jquery_iframe_close}";
			}
			else
			{
				$html .= "E-mail fejl: Ingen e-mail tilknyttet";
			}
			unlink($tmpfile);
		}
		else
		{
			// Vis PDF
			$fpdf->Output();
			exit;		
		}
	}
	elseif ($do == "invoices_html")
	{
		// Faktura som HTML
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_invoices
			WHERE
				id = '$id'
			");
		if ($res = $db->fetch_array())
		{
			// Linier
			$ress2 = $db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_invoices_lines
				WHERE
					invoice_id = '$id'
				ORDER BY
					id
				");
			$lines = "";
			$total = 0;
			$vat_base = 0;
			while ($res2 = $db->fetch_array($ress2))
			{
				$tmp = new tpl("MODULE|$module|admin_invoices_html_line");
				$tmp->set("title", nl2br(stripslashes($res2["title"])));
				$tmp->set("quantity", $res2["quantity"]);
				$tmp->set("price", OBA_price($res2["price"]));
				$tmp->set("total", OBA_price($res2["price"] * $res2["quantity"]));
				$lines .= $tmp->html();
				
				$total += ($res2["price"] * $res2["quantity"]);
				if ($res2["no_vat"] != 1) $vat_base += ($res2["price"] * $res2["quantity"]);
			}
			
			$tmp = new tpl("MODULE|$module|admin_invoices_html");
			$tmp->set("invoice_date", date("d-m-Y", strtotime($res["invoice_date"])));
			$tmp->set("invoice_no", $res["invoice_no"]);
			$tmp->set("name", stripslashes($res["name"]));
			$tmp->set("address", stripslashes($res["address"]));
			$tmp->set("zipcode", stripslashes($res["zipcode"]));
			$tmp->set("city", stripslashes($res["city"]));
			$tmp->set("phone", stripslashes($res["phone"]));
			$tmp->set("email", stripslashes($res["email"]));
			$tmp->set("lines", $lines);
			$tmp->set("vat_pct", module_setting("vat_pct"));
			
			$tmp->set("total", OBA_price($total));
			$tmp->set("vat", OBA_price($vat_base / 100 * intval(module_setting("vat_pct"))));
			$tmp->set("total_vat", OBA_price($total + $vat_base / 100 * intval(module_setting("vat_pct"))));
			
			$html .= $tmp->html();
		}
	}
	elseif ($do == "invoices_pay")
	{
		// Marker faktura som betalt
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_invoices
			WHERE
				id = '$id'
			");
		if (!$res = $db->fetch_array())
		{
			header("Location: /site/$_lang_id/$module/$page/invoices_overview");
			exit;
		}

		$msg = new message;
		$msg->title("Faktura - Betaling");
		$html .= $msg->html();

		if ($res["invoice_no"] == 0)
		{	
			$html .= "Dette er en faktura kladde og kan derfor ikke markeres som betalt.";
		}	
		elseif ($res["paid_date"] == "")
		{
			$frm = new form;
			$frm->hidden("tpl", $vars["tpl"]);
			$frm->submit_text = "Marker som betalt";
			$frm->tpl("th", "Betaling");
			$frm->select(
				"Betalingsmetode",
				"paid_method",
				"",
				"^.+$",
				"Påkrævet",
				"",
				$select_paid_method
				);
				
			if ($frm->done())
			{
				$db->execute("
					UPDATE
						" . $_table_prefix . "_module_" . $module . "_invoices
					SET
						paid_date = '" . date("Y-m-d") . "',
						paid_method = '" . $db->escape($frm->values["paid_method"]) . "'
					WHERE
						id = '$id'
					");
				header("Location: /site/$_lang_id/$module/$page/$do/$id?tpl=" . $vars["tpl"]);
				exit;
			}
				
			$html .= $frm->html();
		}
		else
		{
			$html .= "Betaling modtaget " . date("d-m-Y", strtotime($res["paid_date"])) . " (" . $array_paid_method[$res["paid_method"]] . ")";
		}

		$html .= " &nbsp; &nbsp; &nbsp; <a href=\"/?module=$module&page=$page&do=invoices_pdf_print&id=" . $res["id"] . "&tpl=popup\">Print faktura og luk vindue</a><br><br>" .
			"<iframe src=\"/?module=$module&page=$page&do=invoices_pdf&id=$id\" width=\"98%\" height=\"500\"></iframe>";		
		
	}
	elseif ($do == "invoices_overview")
	{
		$msg = new message;
		$msg->title("Faktura - Oversigt");
		$html .= $msg->html();
		
		// Bygger søge SQL
		$searchstring = trim($vars["searchstring"]);
		$sql_where = "";
		if ($searchstring != "")
		{
			$sql_where = "
				WHERE
					invoice_no = '" . intval($searchstring) . "' OR
					name LIKE '%" . $db->escape($searchstring) . "%' OR
					phone LIKE '%" . $db->escape($searchstring) . "%' OR
					email LIKE '%" . $db->escape($searchstring) . "%'
				";
		}
		
		$frm = new form;
		$frm->method("get");
		$frm->submit_text = "{LANG|Søg}";
		$frm->tpl("th", "{LANG|Søg}");
		$frm->input(
			"{LANG|Søgeord}",
			"searchstring",
			$searchstring
			);
		$html .= $frm->html();
		
		$total = $db->execute_field("
			SELECT
				COUNT(*)
			FROM
				" . $_table_prefix . "_module_" . $module . "_invoices
			$sql_where
			");
			
		$paging = new paging;
		$limit = $paging->limit(25);
		$paging->total($total);
		$start = ($paging->current_page() - 1) * $limit;
		$html .= $paging->html();
		
		$tbl = new table;
		$tbl->th("Fakturadato");
		$tbl->th("Fakturanr");
		$tbl->th("Navn");
		$tbl->th("E-mail");
		$tbl->th("Telefon");
		$tbl->th("Beløb");
		$tbl->th("Betaling");
		$tbl->th("{LANG|Valg}", 3);
		$tbl->endrow();
		
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_invoices
			$sql_where
			ORDER BY
				IF(invoice_no = 0, 0, 1),
				invoice_no DESC
			LIMIT
				$start, $limit
			");
		
		while ($res = $db->fetch_array())
		{
			$tbl->td(date("d-m-Y", strtotime($res["invoice_date"])));
			$tbl->td($res["invoice_no"]);
			$tbl->td(htmlentities(stripslashes($res["name"])));
			$tbl->td(htmlentities(stripslashes($res["email"])));
			$tbl->td(htmlentities(stripslashes($res["phone"])));
			$tbl->td(OBA_price($db->execute_field("
				SELECT
					SUM(quantity * price)
				FROM
					" . $_table_prefix . "_module_" . $module . "_invoices_lines
				WHERE
					invoice_id = '" . $res["id"] . "'
				")));
			$tbl->td($res["paid_date"] != "" ? (date("d-m-Y", strtotime($res["paid_date"])) . " (" . $array_paid_method[$res["paid_method"]] . ")") : "-");
			$tbl->td("<input type=\"button\" value=\"Vis\" onclick=\"url_popup('/?module=$module&page=$page&do=invoices_pdf&id=" . $res["id"] . "&tpl=popup');\" />");
			$tbl->td("<input type=\"button\" value=\"Print\" onclick=\"
				$('#iframeHidden').attr('src', '/?module=$module&page=$page&do=invoices_pdf_print&id=" . $res["id"] . "&tpl=popup');
				this.disabled = true;
				var elm = this;
				setTimeout(function () {
					elm.disabled = false;
				}, 2000);
				\" />");
			$tbl->td("<input type=\"button\" value=\"E-mail\" onclick=\"url_popup('/?module=$module&page=$page&do=invoices_pdf_email&id=" . $res["id"] . "&tpl=popup');\" />");
			if ($res["invoice_no"] == 0)
			{
				$tbl->choise("Ret", "invoices_add", $res["id"]);
			}
			if ($res["invoice_no"] > 0 and $res["paid_date"] == "") $tbl->td("<input type=\"button\" value=\"Betaling\" onclick=\"url_popup('/?module=$module&page=$page&do=invoices_pay&id=" . $res["id"] . "&tpl=popup');\" />");
			$tbl->choise("Slet", "invoices_delete", $res["id"], "Slet denne faktura?");
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
	elseif ($do == "invoices_delete")
	{
		// Slet faktura kladde
	
		$db->execute("
			DELETE FROM
				" . $_table_prefix . "_module_" . $module . "_invoices
			WHERE
				invoice_no = 0 AND
				id = '$id'
			");
		if ($db->affected_rows() == 1)
		{
			$db->execute("
				DELETE FROM
					" . $_table_prefix . "_module_" . $module . "_invoices_lines
				WHERE
					invoice_id = '$id'
				");
		}
		
		header("Location: /site/$_lang_id/$module/$page/invoices_overview");
		exit;
	}
	elseif ($do == "invoices_daily")
	{
		// Daglig
	
		$msg = new message;
		$msg->title("Faktura - Daglig");
		$html .= $msg->html();
		
		$tmp = new tpl("MODULE|$module|admin_invoices_daily");
		$tmp->set("date", date("d-m-Y"));
		$html .= $tmp->html();
		
	}
	elseif ($do == "invoices_daily_show")
	{
		// Vis daglig
		
		$cnv = new convert;
		$date = date("Y-m-d", strtotime($cnv->date_dk2uk($vars["date"])));
	
		// FPDF
		require_once($_document_root . "/modules/$module/fpdf/fpdf.php");

		// Instanciation of inherited class
		$fpdf = new FPDF('P', 'mm', 'A4');
		$fpdf->SetAutoPageBreak(false);
		$fpdf->SetTextColor(0, 0, 0);
		
		$pagecount = 1;
		$fpdf->AddPage();
		OBA_fpdf_layout($fpdf, "DAGLIG " . date("d-m-Y", strtotime($date)), "Side $pagecount");
		$fpdf->SetFont("Arial", "B", 10);
		$fpdf->Text(15, 75, "Fakturanr");
		$fpdf->Text(60, 75, "Fakturadato");
		$fpdf->Text(110, 75, "Afstemningskonto");
		$fpdf->Text(185, 75, "Beløb");
		$fpdf->SetFont("Arial", "", 10);
		$y = 80;
		
		$total = 0;
		$array_totals = array();		
		$ress = $db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_invoices
			WHERE
				paid_date = '$date'
			ORDER BY
				id
			");
		while ($res = $db->fetch_array($ress))
		{
			if ($y > 260)
			{
				$pagecount++;
				$fpdf->AddPage();
				OBA_fpdf_layout($fpdf, "DAGLIG " . date("d-m-Y", strtotime($date)), "Side $pagecount");
				$fpdf->SetFont("Arial", "B", 10);
				$fpdf->Text(15, 75, "Fakturanr");
				$fpdf->Text(60, 75, "Fakturadato");
				$fpdf->Text(110, 75, "Afstemningskonto");
				$fpdf->Text(185, 75, "Beløb");
				$fpdf->SetFont("Arial", "", 10);
				$y = 80;
			}
			
			$amount = $db->execute_field("
				SELECT
					SUM(price * quantity * IF(no_vat = 1, 1, " . number_format(1 + intval(module_setting("vat_pct")) / 100, 2, ".", "") . "))
				FROM
					" . $_table_prefix . "_module_" . $module . "_invoices_lines
				WHERE
					invoice_id = '" . $res["id"] . "'
				");
			
			$fpdf->Text(15, $y, $res["invoice_no"]);
			$fpdf->Text(60, $y, date("d-m-Y", strtotime($res["invoice_date"])));
			$fpdf->Text(110, $y, $array_paid_method[$res["paid_method"]]);
			
			$fpdf->SetXY(150, $y - 0.7);
			$fpdf->MultiCell(45, 0, number_format($amount, 2, ",", "."), "0", "R");
			
			$y += 5;
			
			if (!isset($array_totals[$res["paid_method"]])) $array_totals[$res["paid_method"]] = 0;
			$array_totals[$res["paid_method"]] += $amount;
			$total += $amount;
		}
		
		// Totaler
		$y += 10;
		if ($y > 270 - (count($array_totals) + 1) * 5)
		{
			$pagecount++;
			$fpdf->AddPage();
			OBA_fpdf_layout($fpdf, "DAGLIG " . date("d-m-Y", strtotime($date)), "Side $pagecount");
			$fpdf->SetFont("Arial", "B", 10);
			$fpdf->Text(15, 75, "Fakturanr");
			$fpdf->Text(60, 75, "Fakturadato");
			$fpdf->Text(110, 75, "Afstemningskonto");
			$fpdf->Text(185, 75, "Beløb");
			$fpdf->SetFont("Arial", "", 10);
			$y = 80;
		}
		$fpdf->SetFont("Arial", "B", 10);
		foreach ($array_totals as $key => $val)
		{
			$fpdf->Text(110, $y, $array_paid_method[$key] . ":");
			
			$fpdf->SetXY(150, $y - 0.7);
			$fpdf->MultiCell(45, 0, number_format($val, 2, ",", "."), "0", "R");
			
			$y += 5;
		}
		$fpdf->Text(110, $y, "Total for dagen:");
		
		$fpdf->SetXY(150, $y - 0.7);
		$fpdf->MultiCell(45, 0, number_format($total, 2, ",", "."), "0", "R");
		
		
		$fpdf->Output();
		exit;
		
	}
	elseif ($do == "invoices_add")
	{
		// Opret faktura
		
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
				$db->execute("
					SELECT
						*
					FROM
						" . $_table_prefix . "_module_" . $module . "_invoices
					WHERE
						invoice_no > 0 AND
						(
							name LIKE '%" . $db->escape($searchstring) . "%' OR
							phone LIKE '%" . $db->escape($searchstring) . "%' OR
							email LIKE '%" . $db->escape($searchstring) . "%'
						)
					GROUP BY
						phone
					ORDER BY
						IF(name LIKE '" . $db->escape($searchstring) . "%', 0, 1),
						name
					LIMIT
						0, 25
					");
				while ($db->fetch_array())
				{
					$response["id" . $count] = 0;
					$response["type" . $count] = "private";
					$response["name" . $count] = $db->array["name"];
					$response["address" . $count] = $db->array["address"];
					$response["zipcode" . $count] = $db->array["zipcode"];
					$response["city" . $count] = $db->array["city"];
					$response["phone" . $count] = $db->array["phone"];
					$response["email" . $count] = $db->array["email"];
					$response["bank_regno" . $count] = "";
					$response["bank_account" . $count] = "";
					$response["vat" . $count] = "";
					$count++;
				}
			}
			$response["count"] = $count;
			$ajax->response($response);
		}
		
		if ($id > 0)
		{
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_invoices
				WHERE
					invoice_no = 0 AND
					id = '$id'
				");
			$res = $db->fetch_array();
		}
		else
		{
			$res = false;
		}
		
		if ($vars["do2"] == "save_invoice" or $vars["do2"] == "create_invoice")
		{
			if ($res)
			{
				$db->execute("
					DELETE FROM
						" . $_table_prefix . "_module_" . $module . "_invoices
					WHERE
						id = '" . $res["id"] . "'
					");
				$db->execute("
					DELETE FROM
						" . $_table_prefix . "_module_" . $module . "_invoices_lines
					WHERE
						invoice_id = '" . $res["id"] . "'
					");
			}
			
			// Opretter faktura
			if ($vars["do2"] == "create_invoice")
			{
				$invoice_no = OBA_invoice_no();
			}
			else
			{
				$invoice_no = 0;
			}
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
					'" . $db->escape(trim($vars["address"] . "\n" . $vars["address2"])) . "',
					'" . $db->escape($vars["zipcode"]) . "',
					'" . $db->escape($vars["city"]) . "',
					'" . $db->escape($vars["phone"]) . "',
					'" . $db->escape($vars["email"]) . "'
				)
				");
			$invoice_id = $db->insert_id();
				
			// Opretter linier
			$i = 0;
			while ($i <= intval($vars["lines_count"]))
			{
				if ($vars["title" . $i] != "")
				{
					$factor = ($vars["no_vat" . $i] != "" ? 1 : (100 / (100 + intval(module_setting("vat_pct")))));
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
							'" . number_format(floatval(str_replace(",", ".", $vars["price" . $i])) * $factor, 2, ".", "") . "',
							'" . ($vars["no_vat" . $i] != "" ? 1 : 0) . "'
						)
						");
				}
				$i++;
			}
			
			header("Location: /site/$_lang_id/$module/$page/invoices_overview?showinvoiceid=$invoice_id");
			exit;
		}
		
		if ($res)
		{
			$lines = "";
			$ress2 = $db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_invoices_lines
				WHERE
					invoice_id = '" . $res["id"] . "'
				ORDER BY
					id
				");
			while ($res2 = $db->fetch_array($ress2))
			{
				$factor = ($res2["no_vat"] == 1 ? 1 : ((100 + intval(module_setting("vat_pct"))) / 100));
				if ($lines != "") $lines .= ",";
				$tmp = new tpl("MODULE|$module|admin_invoices_add_line");
				$tmp->set("title", str_replace("\r", "", str_replace("\n", " ", str_replace("'", "", stripslashes($res2["title"])))));
				$tmp->set("quantity", number_format($res2["quantity"], 2, ".", ""));
				$tmp->set("price", number_format($res2["price"] * $factor, 2, ".", ""));
				$tmp->set("no_vat", $res2["no_vat"]);
				$lines .= $tmp->html();
			}
		}
		else
		{
			$lines = "";
		}
		
		$msg = new message;
		$msg->title("Opret faktura");
		$html .= $msg->html();
	
		$tmp = new tpl("MODULE|$module|admin_invoices_add");
		$tmp->set("ajax", $ajax->group);
		$tmp->set("lines", $lines);
		$tmp->set("id", $res["id"]);
		$tmp->set("name", stripslashes($res["name"]));
		list($a1, $a2) = explode("\n", stripslashes($res["address"]));
		$tmp->set("address", $a1);
		$tmp->set("address2", $a2);
		$tmp->set("zipcode", stripslashes($res["zipcode"]));
		$tmp->set("city", stripslashes($res["city"]));
		$tmp->set("phone", stripslashes($res["phone"]));
		$tmp->set("email", stripslashes($res["email"]));
		$tmp->set("vat_pct", module_setting("vat_pct"));
		$html .= $tmp->html();	
		
		$html .= $ajax->html();
		
	}
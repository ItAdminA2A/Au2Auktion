<?php
	$test = false;

	$a = new ajax;
	if ($a->do == "get_prev_and_next")
	{
		$response = array("state" => "ok");
		
		// Forrige auktioner
		$prev_count = 0;
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				auction_date = '" . date("Y-m-d") . "' AND
				NOT ISNULL(start_time) AND
				NOT ISNULL(end_time) AND
				NOT ISNULL(auction_no)
			ORDER BY
				auction_no DESC
			LIMIT
				0, 6
			");
		while ($db->fetch_array())
		{
			$response["prev_id" . $prev_count] = $db->array["id"];
			$response["prev_auction_no" . $prev_count] = $db->array["auction_no"];
			$response["prev_brand" . $prev_count] = $db->array["brand"];
			$response["prev_model" . $prev_count] = $db->array["model"];
			$response["prev_variant" . $prev_count] = $db->array["variant"];
			$response["prev_fuel" . $prev_count] = $db->array["fuel"];
			$response["prev_year" . $prev_count] = $db->array["year"];
			$response["prev_cur_price" . $prev_count] = $db->array["cur_price"];
			$response["prev_min_price" . $prev_count] = $db->array["min_price"];
			$response["prev_bidder_id" . $prev_count] = $db->array["bidder_id"];
			$response["prev_cancel" . $prev_count] = $db->array["cancel"];
			$prev_count++;
		}
		$response["prev_count"] = $prev_count;

		// Næste auktioner
		$next_count = 0;
		$db->execute("
			SELECT
				*
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions
			WHERE
				auction_date = '" . date("Y-m-d") . "' AND
				ISNULL(start_time) AND
				ISNULL(end_time) AND
				NOT ISNULL(auction_no)
			ORDER BY
				auction_no
			LIMIT
				0, 6
			");
		while ($db->fetch_array())
		{
			$response["next_id" . $next_count] = $db->array["id"];
			$response["next_auction_no" . $prev_count] = $db->array["auction_no"];
			$response["next_brand" . $next_count] = $db->array["brand"];
			$response["next_model" . $next_count] = $db->array["model"];
			$response["next_variant" . $next_count] = $db->array["variant"];
			$response["next_fuel" . $next_count] = $db->array["fuel"];
			$response["next_year" . $next_count] = $db->array["year"];
			$response["next_cur_price" . $next_count] = $db->array["cur_price"];
			$response["next_min_price" . $next_count] = $db->array["min_price"];
			$next_count++;
		}
		$response["next_count"] = $next_count;
		
		$a->response($response);
	}
	if ($a->do == "get_auction")
	{
		$db->execute("
			SELECT
				auc.*,
				cat.type AS category_type,
				cat.title AS category_title
			FROM
				" . $_table_prefix . "_module_" . $module . "_auctions AS auc
			LEFT JOIN
				" . $_table_prefix . "_module_" . $module . "_categories AS cat
			ON
				cat.id = auc.category_id
			WHERE
				auc.id = '" . $db->escape($a->values["id"]) . "'
			");
		if ($res = $db->fetch_array())
		{
			$response = array(
				"state" => "ok"
				);
				
			foreach ($res as $key => $val)
			{
				$val = htmlentities($val);
				if ($key == "description") $val = nl2br($val);
				if (!preg_match("/^[0-9]+$/", $key)) $response[$key] = $val;
			}
			
			$images = "";
			$db->execute("
				SELECT
					*
				FROM
					" . $_table_prefix . "_module_" . $module . "_images
				WHERE
					auction_id = '" . $db->escape($a->values["id"]) . "'
				");
			while ($db->fetch_array())
			{
				if ($images != "") $images .= "|";
				$images .= $db->array["id"];
			}
			
			$response["images"] = $images;
		}
		else
		{
			$response = array(
				"state" => "none"
				);
		}
		
		$a->response($response);
	}
	if ($a->do == "refresh")
	{
		// Test
		if ($test)
		{
			// Finder tilfældig auktion til test
			if (!isset($_SESSION[$module . "_test_timeout"]) or $_SESSION[$module . "_test_timeout"] < time())
			{
				$cur_auction_id = $db->execute_field("
					SELECT
						id
					FROM
						" . $_table_prefix . "_module_" . $module . "_auctions
					WHERE
						auction_date >= '" . date("Y-m-d") . "'
					ORDER BY
						RAND()
					LIMIT
						0, 1
					");
				$_SESSION[$module . "_test_timeout"] = time() + 10;
				$_SESSION[$module . "_test_id"] = $cur_auction_id;
			}
			else
			{
				$cur_auction_id = $_SESSION[$module . "_test_id"];
			}
		}
		else
		{
			$cur_auction_id = module_setting("cur_auction_id");
		}
		
		$a->response(array(
			"state" => "ok",
			"time" => date("d-m-Y H:i:s"),
			"prev_auction_id" => module_setting("prev_auction_id"),
			"current_auction_id" => $cur_auction_id,
			"next_auction_id" => module_setting("next_auction_id"),
			"cur_price" => $db->execute_field("
				SELECT
					cur_price
				FROM
					" . $_table_prefix . "_module_" . $module . "_auctions
				WHERE
					id = '" . $db->escape($cur_auction_id) . "'
				")
			));
	}
	
	$tmp = new tpl("MODULE|$module|screen");
	$tmp->set("ajax", $a->html());
	echo($tmp->html());
	exit;

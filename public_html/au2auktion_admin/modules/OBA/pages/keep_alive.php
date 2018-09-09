<?php
	$a = new ajax;
	if ($a->do == "time")
	{
		$a->response(array(
			"state" => "ok",
			"time" => date("Y-m-d H:i:s"),
			"online_count" => (intval(module_setting("online_count_time")) > time() - 15) ? module_setting("online_count") : "0"
			));
	}
	$html .= $a->html();
	
	$tmp = new tpl("MODULE|$module|keep_alive");
	$tmp->set("ajax", $a->group);
	$tmp->set("online_count", (intval(module_setting("online_count_time")) > time() - 15) ? module_setting("online_count") : "0");
	$tmp->set("approve_count", $db->execute_field("
		SELECT
			COUNT(*)
		FROM
			" . $_table_prefix . "_module_" . $module . "_auctions
		WHERE
			ISNULL(auction_no) AND cancel = 0
		"));
	$tmp->set("new_user_count", $db->execute_field("
		SELECT
			COUNT(*)
		FROM
			" . $_table_prefix . "_user_" . $module . "_cust
		WHERE
			extra_check_done = 0
		"));
	$html .= $tmp->html();